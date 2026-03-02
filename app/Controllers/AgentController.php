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

    /**
     * Désactiver (soft-delete) une entreprise
     * Route : POST /agent/entreprise/:id/supprimer
     */
    public function supprimerEntreprise(string $id): void
    {
        $this->requireCsrf();
        $user       = currentUser();
        $entreprise = $this->db->fetchOne(
            "SELECT * FROM entreprises WHERE id = $1 AND region_id = $2",
            [(int) $id, $user['region_id']]
        );

        if (!$entreprise) {
            redirectWith('agent/entreprises', 'error', 'Entreprise introuvable.');
        }

        // Vérifier qu'aucune déclaration n'est attachée
        $nbDecl = (int) $this->db->fetchScalar(
            "SELECT COUNT(*) FROM declarations WHERE entreprise_id = $1",
            [(int) $id]
        );
        if ($nbDecl > 0) {
            redirectWith(
                'agent/entreprises',
                'error',
                "Impossible de supprimer \"{$entreprise['raison_sociale']}\" : elle possède $nbDecl déclaration(s)."
            );
        }

        // Soft-delete : mettre actif = FALSE
        $this->db->execute(
            "UPDATE entreprises SET actif = FALSE, updated_at = NOW() WHERE id = $1",
            [(int) $id]
        );

        logActivity('enterprise_deleted', 'entreprises', (int) $id, [
            'raison_sociale' => $entreprise['raison_sociale'],
        ]);
        redirectWith('agent/entreprises', 'success', "Entreprise \"{$entreprise['raison_sociale']}\" supprimée.");
    }

    /**
     * Formulaire d'import CSV des entreprises (agent)
     */
    public function importEntreprisesForm(): void
    {
        $user = currentUser();
        $this->render('agent.import_entreprises', [
            'pageTitle'  => 'Import CSV Entreprises - ' . APP_NAME,
            'region_id'  => $user['region_id'],
            'breadcrumbs' => [
                ['label' => 'Entreprises', 'url' => '/agent/entreprises'],
                ['label' => 'Import CSV', 'url' => false],
            ],
        ]);
    }

    /**
     * Traitement de l'import CSV entreprises (agent)
     */
    public function importEntreprises(): void
    {
        $this->requireCsrf();
        $user = currentUser();

        if (empty($_FILES['csv_file']['tmp_name'])) {
            redirectWith('agent/import/entreprises', 'error', 'Veuillez sélectionner un fichier CSV.');
        }

        $delimiter = post('delimiter', ';');
        $skipFirst = post('skip_header', '1') === '1';
        $regionId  = $user['region_id'];

        $tmpFile = $_FILES['csv_file']['tmp_name'];
        $handle  = fopen($tmpFile, 'r');
        if (!$handle) {
            redirectWith('agent/import/entreprises', 'error', 'Impossible de lire le fichier.');
        }

        $imported = 0; $errors = 0; $skipped = 0;
        $lineNum  = 0;

        while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) {
            $lineNum++;
            if ($lineNum === 1 && $skipFirst) continue;

            if (count($row) < 2) { $errors++; continue; }

            $raisonSociale = sanitize(trim($row[0] ?? ''));
            $numeroCnss    = sanitize(trim($row[1] ?? ''));
            $telephone     = sanitize(trim($row[2] ?? ''));
            $email         = strtolower(trim($row[3] ?? ''));
            $activite      = sanitize(trim($row[4] ?? ''));
            $nationalite   = sanitize(trim($row[5] ?? 'Nigérienne'));
            $localite      = sanitize(trim($row[6] ?? ''));

            if (!$raisonSociale) { $errors++; continue; }

            if ($numeroCnss) {
                $exists = $this->db->fetchScalar("SELECT id FROM entreprises WHERE numero_cnss = $1", [$numeroCnss]);
                if ($exists) { $skipped++; continue; }
            }

            try {
                $this->db->insert(
                    "INSERT INTO entreprises (raison_sociale, numero_cnss, telephone, email,
                     activite_principale, nationalite, localite, region_id, actif, created_by)
                     VALUES ($1, $2, $3, $4, $5, $6, $7, $8, TRUE, $9)",
                    [
                        $raisonSociale,
                        $numeroCnss ?: null,
                        $telephone ?: null,
                        ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) ? $email : null,
                        $activite ?: null,
                        $nationalite,
                        $localite ?: null,
                        $regionId,
                        $user['id'],
                    ]
                );
                $imported++;
            } catch (\Exception $e) {
                $errors++;
            }
        }
        fclose($handle);

        logActivity('csv_import_entreprises', 'entreprises', 0, [
            'imported' => $imported, 'errors' => $errors, 'skipped' => $skipped,
        ]);

        $msg = "Import terminé : <strong>$imported entreprise(s) importée(s)</strong>";
        if ($skipped) $msg .= ", $skipped ignorée(s) (doublon CNSS)";
        if ($errors)  $msg .= ", $errors erreur(s)";

        redirectWith('agent/entreprises', 'success', $msg);
    }
}
