<?php
/**
 * Contrôleur Agent
 */
namespace App\Controllers;

class AgentController extends BaseController
{
    /**
     * Tableau de bord de l'agent
     */
    public function dashboard(): void
    {
        $user     = currentUser();
        $campagne = $this->db->fetchOne(
            "SELECT * FROM campagnes_damo WHERE actif = TRUE ORDER BY annee DESC LIMIT 1"
        );

        $stats = [
            'total_declarations'    => (int) $this->db->fetchScalar(
                "SELECT COUNT(*) FROM declarations WHERE agent_id = $1", [$user['id']]
            ),
            'declarations_brouillon'=> (int) $this->db->fetchScalar(
                "SELECT COUNT(*) FROM declarations WHERE agent_id = $1 AND statut = 'brouillon'", [$user['id']]
            ),
            'declarations_soumises' => (int) $this->db->fetchScalar(
                "SELECT COUNT(*) FROM declarations WHERE agent_id = $1 AND statut = 'soumise'", [$user['id']]
            ),
            'declarations_validees' => (int) $this->db->fetchScalar(
                "SELECT COUNT(*) FROM declarations WHERE agent_id = $1 AND statut = 'validee'", [$user['id']]
            ),
            'declarations_rejetees' => (int) $this->db->fetchScalar(
                "SELECT COUNT(*) FROM declarations WHERE agent_id = $1 AND statut = 'rejetee'", [$user['id']]
            ),
            'total_entreprises'     => (int) $this->db->fetchScalar(
                "SELECT COUNT(*) FROM entreprises WHERE region_id = $1 AND actif = TRUE", [$user['region_id']]
            ),
        ];

        // Déclarations récentes
        $dernieresDeclarations = $this->db->fetchAll(
            "SELECT d.*, e.raison_sociale, e.numero_cnss, c.annee
             FROM declarations d
             JOIN entreprises e ON e.id = d.entreprise_id
             JOIN campagnes_damo c ON c.id = d.campagne_id
             WHERE d.agent_id = $1
             ORDER BY d.updated_at DESC
             LIMIT 8",
            [$user['id']]
        );

        // Déclarations rejetées à corriger
        $declarationsRejetees = $this->db->fetchAll(
            "SELECT d.*, e.raison_sociale, d.motif_rejet
             FROM declarations d
             JOIN entreprises e ON e.id = d.entreprise_id
             WHERE d.agent_id = $1 AND d.statut = 'rejetee'
             ORDER BY d.date_rejet DESC",
            [$user['id']]
        );

        // Entreprises sans déclaration pour la campagne courante
        $entreprisesSansDeclaration = 0;
        if ($campagne) {
            $entreprisesSansDeclaration = (int) $this->db->fetchScalar(
                "SELECT COUNT(*) FROM entreprises e 
                 WHERE e.region_id = $1 AND e.actif = TRUE
                 AND e.id NOT IN (SELECT entreprise_id FROM declarations WHERE campagne_id = $2)",
                [$user['region_id'], $campagne['id']]
            );
        }

        $this->render('agent.dashboard', [
            'pageTitle'                  => 'Mon tableau de bord - ' . APP_NAME,
            'campagne'                   => $campagne,
            'stats'                      => $stats,
            'dernieresDeclarations'      => $dernieresDeclarations,
            'declarationsRejetees'       => $declarationsRejetees,
            'entreprisesSansDeclaration' => $entreprisesSansDeclaration,
            'region'                     => $user['region_nom'],
            'breadcrumbs'                => [
                ['label' => 'Tableau de bord', 'url' => false],
            ],
        ]);
    }

    /**
     * Gestion des entreprises de la région
     */
    public function entreprises(): void
    {
        $user   = currentUser();
        $search = get('q', '');
        $branche = get('branche', '');

        // Construction dynamique avec placeholders ? directs
        $where  = ['e.region_id = ?', 'e.actif = TRUE'];
        $params = [$user['region_id']];

        if ($search) {
            $where[] = "(e.raison_sociale ILIKE ? OR e.numero_cnss ILIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        if ($branche) {
            $where[] = "e.branche_id = ?";
            $params[] = (int) $branche;
        }

        $whereClause = implode(' AND ', $where);
        $total = (int) $this->db->fetchScalarRaw(
            "SELECT COUNT(*) FROM entreprises e WHERE $whereClause", $params
        );

        $pagination = $this->paginate($total);

        $paramsPage = array_merge($params, [$pagination['per_page'], $pagination['offset']]);
        $entreprises = $this->db->fetchAllRaw(
            "SELECT e.*, b.libelle AS branche_libelle,
                    (SELECT COUNT(*) FROM declarations WHERE entreprise_id = e.id) AS nb_declarations
             FROM entreprises e
             LEFT JOIN branches_activite b ON b.id = e.branche_id
             WHERE $whereClause
             ORDER BY e.raison_sociale
             LIMIT ? OFFSET ?",
            $paramsPage
        );

        $branches = $this->db->fetchAll("SELECT id, libelle FROM branches_activite WHERE actif = TRUE ORDER BY code");

        $this->render('agent.entreprises', [
            'pageTitle'   => 'Entreprises - ' . APP_NAME,
            'entreprises' => $entreprises,
            'pagination'  => $pagination,
            'branches'    => $branches,
            'filters'     => compact('search', 'branche'),
            'total'       => $total,
            'breadcrumbs' => [
                ['label' => 'Tableau de bord', 'url' => '/agent/dashboard'],
                ['label' => 'Entreprises', 'url' => false],
            ],
        ]);
    }

    /**
     * Formulaire nouvelle entreprise
     */
    public function nouvelleEntreprise(): void
    {
        $user     = currentUser();
        $branches = $this->db->fetchAll("SELECT id, code, libelle FROM branches_activite WHERE actif=TRUE ORDER BY code");
        $departements = $this->db->fetchAll(
            "SELECT d.* FROM departements d WHERE d.region_id = $1 ORDER BY d.nom",
            [$user['region_id']]
        );
        $region = $this->db->fetchOne("SELECT * FROM regions WHERE id = $1", [$user['region_id']]);

        $this->render('agent.entreprise_form', [
            'pageTitle'    => 'Nouvelle entreprise - ' . APP_NAME,
            'branches'     => $branches,
            'departements' => $departements,
            'region'       => $region,
            'mode'         => 'create',
        ]);
    }

    /**
     * Créer une entreprise
     */
    public function creerEntreprise(): void
    {
        $this->requireCsrf();
        $user = currentUser();

        $raisonSociale = sanitize(post('raison_sociale', ''));
        if (!$raisonSociale) {
            redirectWith('agent/entreprise/nouvelle', 'error', 'La raison sociale est obligatoire.');
        }

        $numeroCnss = sanitize(post('numero_cnss', ''));
        if ($numeroCnss) {
            $exists = $this->db->fetchScalar(
                "SELECT id FROM entreprises WHERE numero_cnss = $1 AND region_id = $2",
                [$numeroCnss, $user['region_id']]
            );
            if ($exists) {
                redirectWith('agent/entreprise/nouvelle', 'error', 'Une entreprise avec ce numéro CNSS existe déjà dans votre région.');
            }
        }

        $this->db->insert(
            "INSERT INTO entreprises (raison_sociale, nationalite, activite_principale, activites_secondaires,
             branche_id, region_id, departement_id, commune_id, localite, quartier, boite_postale,
             telephone, fax, email, numero_cnss, agent_id, actif, created_by)
             VALUES ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11,$12,$13,$14,$15,$16,TRUE,$17)",
            [
                $raisonSociale,
                sanitize(post('nationalite', 'Nigérienne')),
                sanitize(post('activite_principale', '')),
                sanitize(post('activites_secondaires', '')),
                post('branche_id') ? (int)post('branche_id') : null,
                $user['region_id'],
                post('departement_id') ? (int)post('departement_id') : null,
                post('commune_id') ? (int)post('commune_id') : null,
                sanitize(post('localite', '')),
                sanitize(post('quartier', '')),
                sanitize(post('boite_postale', '')),
                sanitize(post('telephone', '')),
                sanitize(post('fax', '')),
                sanitize(post('email', '')),
                $numeroCnss,
                $user['id'],
                $user['id'],
            ]
        );

        logActivity('enterprise_created', 'entreprises', 0, ['raison_sociale' => $raisonSociale]);
        redirectWith('agent/entreprises', 'success', "Entreprise \"$raisonSociale\" créée avec succès.");
    }

    /**
     * Formulaire modification entreprise
     */
    public function modifierEntreprise(string $id): void
    {
        $user       = currentUser();
        $entreprise = $this->db->fetchOne(
            "SELECT * FROM entreprises WHERE id = $1 AND region_id = $2",
            [(int) $id, $user['region_id']]
        );

        if (!$entreprise) redirectWith('agent/entreprises', 'error', 'Entreprise introuvable.');

        $branches     = $this->db->fetchAll("SELECT id, code, libelle FROM branches_activite WHERE actif=TRUE ORDER BY code");
        $departements = $this->db->fetchAll("SELECT * FROM departements WHERE region_id = $1 ORDER BY nom", [$user['region_id']]);
        $communes     = $entreprise['departement_id'] ?
            $this->db->fetchAll("SELECT * FROM communes WHERE departement_id = $1 ORDER BY nom", [$entreprise['departement_id']]) : [];

        $this->render('agent.entreprise_form', [
            'pageTitle'    => 'Modifier entreprise - ' . APP_NAME,
            'entreprise'   => $entreprise,
            'branches'     => $branches,
            'departements' => $departements,
            'communes'     => $communes,
            'mode'         => 'edit',
        ]);
    }

    /**
     * Mettre à jour une entreprise
     */
    public function updateEntreprise(string $id): void
    {
        $this->requireCsrf();
        $user       = currentUser();
        $entreprise = $this->db->fetchOne(
            "SELECT * FROM entreprises WHERE id = $1 AND region_id = $2",
            [(int) $id, $user['region_id']]
        );
        if (!$entreprise) redirectWith('agent/entreprises', 'error', 'Entreprise introuvable.');

        $this->db->execute(
            "UPDATE entreprises SET
             raison_sociale=$1, nationalite=$2, activite_principale=$3, activites_secondaires=$4,
             branche_id=$5, departement_id=$6, commune_id=$7, localite=$8, quartier=$9,
             boite_postale=$10, telephone=$11, fax=$12, email=$13, numero_cnss=$14, updated_at=NOW()
             WHERE id=$15",
            [
                sanitize(post('raison_sociale', '')),
                sanitize(post('nationalite', '')),
                sanitize(post('activite_principale', '')),
                sanitize(post('activites_secondaires', '')),
                post('branche_id') ? (int)post('branche_id') : null,
                post('departement_id') ? (int)post('departement_id') : null,
                post('commune_id') ? (int)post('commune_id') : null,
                sanitize(post('localite', '')),
                sanitize(post('quartier', '')),
                sanitize(post('boite_postale', '')),
                sanitize(post('telephone', '')),
                sanitize(post('fax', '')),
                sanitize(post('email', '')),
                sanitize(post('numero_cnss', '')),
                (int) $id,
            ]
        );

        logActivity('enterprise_updated', 'entreprises', (int) $id);
        redirectWith('agent/entreprises', 'success', 'Entreprise mise à jour.');
    }
}
