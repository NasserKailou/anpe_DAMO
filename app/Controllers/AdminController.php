<?php
/**
 * Contrôleur Dashboard Administrateur
 */
namespace App\Controllers;

class AdminController extends BaseController
{
    /**
     * Tableau de bord principal
     */
    public function dashboard(): void
    {
        $campagne = $this->db->fetchOne(
            "SELECT * FROM campagnes_damo WHERE actif = TRUE ORDER BY annee DESC LIMIT 1"
        );

        $stats = [
            'total_declarations'    => (int) $this->db->fetchScalar("SELECT COUNT(*) FROM declarations"),
            'declarations_soumises' => (int) $this->db->fetchScalar("SELECT COUNT(*) FROM declarations WHERE statut = 'soumise'"),
            'declarations_validees' => (int) $this->db->fetchScalar("SELECT COUNT(*) FROM declarations WHERE statut = 'validee'"),
            'declarations_rejetees' => (int) $this->db->fetchScalar("SELECT COUNT(*) FROM declarations WHERE statut = 'rejetee'"),
            'total_entreprises'     => (int) $this->db->fetchScalar("SELECT COUNT(*) FROM entreprises"),
            'total_agents'          => (int) $this->db->fetchScalar("SELECT COUNT(*) FROM utilisateurs WHERE role = 'agent' AND actif = TRUE"),
            'total_regions_actives' => (int) $this->db->fetchScalar(
                "SELECT COUNT(DISTINCT region_id) FROM declarations WHERE campagne_id = $1",
                [$campagne['id'] ?? 0]
            ),
        ];

        // Déclarations par région (campagne courante)
        $parRegion = $this->db->fetchAll(
            "SELECT r.nom, COUNT(d.id) AS total,
                    SUM(CASE WHEN d.statut = 'validee' THEN 1 ELSE 0 END) AS validees,
                    SUM(CASE WHEN d.statut = 'soumise' THEN 1 ELSE 0 END) AS soumises,
                    SUM(CASE WHEN d.statut = 'brouillon' THEN 1 ELSE 0 END) AS brouillons
             FROM regions r
             LEFT JOIN declarations d ON d.region_id = r.id AND d.campagne_id = $1
             GROUP BY r.id, r.nom
             ORDER BY total DESC",
            [$campagne['id'] ?? 0]
        );

        // Déclarations récentes
        $recentesDeclarations = $this->db->fetchAll(
            "SELECT d.*, e.raison_sociale, r.nom AS region_nom,
                    u.nom AS agent_nom, u.prenom AS agent_prenom,
                    c.annee
             FROM declarations d
             JOIN entreprises e ON e.id = d.entreprise_id
             JOIN regions r ON r.id = d.region_id
             JOIN utilisateurs u ON u.id = d.agent_id
             JOIN campagnes_damo c ON c.id = d.campagne_id
             ORDER BY d.updated_at DESC
             LIMIT 10"
        );

        // Activité récente (logs)
        $activiteRecente = $this->db->fetchAll(
            "SELECT l.*, u.nom, u.prenom, u.email
             FROM logs_activite l
             LEFT JOIN utilisateurs u ON u.id = l.utilisateur_id
             ORDER BY l.created_at DESC
             LIMIT 8"
        );

        $this->render('admin.dashboard', [
            'pageTitle'           => 'Tableau de bord - ' . APP_NAME,
            'campagne'            => $campagne,
            'stats'               => $stats,
            'parRegion'           => $parRegion,
            'recentesDeclarations'=> $recentesDeclarations,
            'activiteRecente'     => $activiteRecente,
            'breadcrumbs'         => [
                ['label' => 'Tableau de bord', 'url' => false],
            ],
        ]);
    }

    /**
     * Liste des déclarations
     */
    public function declarations(): void
    {
        $statut   = get('statut', '');
        $region   = get('region', '');
        $campagne = get('campagne', '');
        $search   = get('q', '');

        // Construction dynamique de la clause WHERE (placeholders ? pour PDO)
        $where  = ['1=1'];
        $params = [];

        if ($statut) {
            $where[] = "d.statut = ?";
            $params[] = $statut;
        }
        if ($region) {
            $where[] = "d.region_id = ?";
            $params[] = (int) $region;
        }
        if ($campagne) {
            $where[] = "d.campagne_id = ?";
            $params[] = (int) $campagne;
        }
        if ($search) {
            // Chaque ILIKE a son propre ? et son propre paramètre
            $where[] = "(e.raison_sociale ILIKE ? OR e.numero_cnss ILIKE ? OR d.code_questionnaire ILIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $whereClause = implode(' AND ', $where);

        $total = (int) $this->db->fetchScalarRaw(
            "SELECT COUNT(*) FROM declarations d JOIN entreprises e ON e.id = d.entreprise_id WHERE $whereClause",
            $params
        );

        $pagination = $this->paginate($total);

        $paramsPage = array_merge($params, [$pagination['per_page'], $pagination['offset']]);
        $declarations = $this->db->fetchAllRaw(
            "SELECT d.*, e.raison_sociale, e.numero_cnss, r.nom AS region_nom,
                    u.nom AS agent_nom, u.prenom AS agent_prenom,
                    c.annee
             FROM declarations d
             JOIN entreprises e ON e.id = d.entreprise_id
             JOIN regions r ON r.id = d.region_id
             JOIN utilisateurs u ON u.id = d.agent_id
             JOIN campagnes_damo c ON c.id = d.campagne_id
             WHERE $whereClause
             ORDER BY d.updated_at DESC
             LIMIT ? OFFSET ?",
            $paramsPage
        );

        $regions    = $this->db->fetchAll("SELECT id, nom FROM regions ORDER BY nom");
        $campagnes  = $this->db->fetchAll("SELECT id, annee, libelle FROM campagnes_damo ORDER BY annee DESC");

        $this->render('admin.declarations', [
            'pageTitle'    => 'Déclarations - ' . APP_NAME,
            'declarations' => $declarations,
            'pagination'   => $pagination,
            'regions'      => $regions,
            'campagnes'    => $campagnes,
            'filters'      => compact('statut', 'region', 'campagne', 'search'),
            'total'        => $total,
            'breadcrumbs'  => [
                ['label' => 'Tableau de bord', 'url' => '/admin/dashboard'],
                ['label' => 'Déclarations', 'url' => false],
            ],
        ]);
    }

    /**
     * Voir une déclaration en détail
     */
    public function voirDeclaration(string $id): void
    {
        $declaration = $this->db->fetchOne(
            "SELECT d.*, e.raison_sociale, e.numero_cnss, e.activite_principale,
                    e.nationalite, e.localite, e.telephone AS ent_tel, e.email AS ent_email,
                    e.boite_postale, e.quartier, e.adresse,
                    r.nom AS region_nom, b.libelle AS branche_nom,
                    u.nom AS agent_nom, u.prenom AS agent_prenom,
                    c.annee, c.libelle AS campagne_libelle,
                    v.nom AS validateur_nom, v.prenom AS validateur_prenom
             FROM declarations d
             JOIN entreprises e ON e.id = d.entreprise_id
             JOIN regions r ON r.id = d.region_id
             JOIN utilisateurs u ON u.id = d.agent_id
             JOIN campagnes_damo c ON c.id = d.campagne_id
             LEFT JOIN branches_activite b ON b.id = e.branche_id
             LEFT JOIN utilisateurs v ON v.id = d.validateur_id
             WHERE d.id = $1",
            [(int) $id]
        );

        if (!$declaration) {
            redirectWith('admin/declarations', 'error', 'Déclaration introuvable.');
        }

        // Charger toutes les sections
        $effectifsMensuels   = $this->db->fetchAll(
            "SELECT * FROM declaration_effectifs_mensuels WHERE declaration_id = $1 ORDER BY mois",
            [(int) $id]
        );
        $categoriesEffectifs = $this->db->fetchAll(
            "SELECT * FROM declaration_categories_effectifs WHERE declaration_id = $1",
            [(int) $id]
        );
        $niveauxInstruction  = $this->db->fetchAll(
            "SELECT * FROM declaration_niveaux_instruction WHERE declaration_id = $1",
            [(int) $id]
        );
        $formations          = $this->db->fetchAll(
            "SELECT * FROM declaration_formations WHERE declaration_id = $1",
            [(int) $id]
        );
        $pertesEmploi        = $this->db->fetchAll(
            "SELECT * FROM declaration_pertes_emploi WHERE declaration_id = $1",
            [(int) $id]
        );
        $perspective         = $this->db->fetchOne(
            "SELECT * FROM declaration_perspectives WHERE declaration_id = $1",
            [(int) $id]
        );
        $effectifsEtrangers  = $this->db->fetchAll(
            "SELECT * FROM declaration_effectifs_etrangers WHERE declaration_id = $1",
            [(int) $id]
        );
        $historique          = $this->db->fetchAll(
            "SELECT h.*, u.nom, u.prenom FROM historique_declarations h
             LEFT JOIN utilisateurs u ON u.id = h.utilisateur_id
             WHERE h.declaration_id = $1 ORDER BY h.created_at DESC",
            [(int) $id]
        );

        $this->render('admin.declaration_detail', [
            'pageTitle'           => 'Déclaration #' . $declaration['code_questionnaire'] . ' - ' . APP_NAME,
            'declaration'         => $declaration,
            'effectifsMensuels'   => $effectifsMensuels,
            'categoriesEffectifs' => $categoriesEffectifs,
            'niveauxInstruction'  => $niveauxInstruction,
            'formations'          => $formations,
            'pertesEmploi'        => $pertesEmploi,
            'perspective'         => $perspective,
            'effectifsEtrangers'  => $effectifsEtrangers,
            'historique'          => $historique,
            'breadcrumbs'         => [
                ['label' => 'Tableau de bord', 'url' => '/admin/dashboard'],
                ['label' => 'Déclarations', 'url' => '/admin/declarations'],
                ['label' => '#' . $declaration['code_questionnaire'], 'url' => false],
            ],
        ]);
    }

    /**
     * Valider une déclaration
     */
    public function validerDeclaration(string $id): void
    {
        $this->requireCsrf();
        
        $declaration = $this->db->fetchOne(
            "SELECT * FROM declarations WHERE id = $1 AND statut = 'soumise'",
            [(int) $id]
        );

        if (!$declaration) {
            $this->json(['success' => false, 'message' => 'Déclaration introuvable ou déjà traitée.']);
        }

        $observations = sanitize(post('observations', ''));

        $this->db->beginTransaction();
        try {
            $this->db->execute(
                "UPDATE declarations SET statut = 'validee', date_validation = NOW(),
                 validateur_id = $1, observations = $2, updated_at = NOW()
                 WHERE id = $3",
                [$_SESSION['user_id'], $observations, (int) $id]
            );

            // Historique
            $this->db->execute(
                "INSERT INTO historique_declarations (declaration_id, utilisateur_id, action, ancien_statut, nouveau_statut, details, ip_address)
                 VALUES ($1, $2, 'validation', 'soumise', 'validee', $3, $4)",
                [(int) $id, $_SESSION['user_id'], json_encode(['observations' => $observations]), getClientIp()]
            );

            $this->db->commit();
            logActivity('declaration_validee', 'declarations', (int) $id);

            if (isAjax()) {
                $this->json(['success' => true, 'message' => 'Déclaration validée avec succès.']);
            }
            redirectWith('admin/declarations', 'success', 'Déclaration validée avec succès.');
        } catch (\Exception $e) {
            $this->db->rollback();
            $this->json(['success' => false, 'message' => 'Erreur lors de la validation.']);
        }
    }

    /**
     * Rejeter une déclaration
     */
    public function rejeterDeclaration(string $id): void
    {
        $this->requireCsrf();

        $motif = sanitize(post('motif_rejet', ''));
        if (!$motif) {
            $this->json(['success' => false, 'message' => 'Le motif de rejet est obligatoire.']);
        }

        $declaration = $this->db->fetchOne(
            "SELECT * FROM declarations WHERE id = $1 AND statut = 'soumise'",
            [(int) $id]
        );

        if (!$declaration) {
            $this->json(['success' => false, 'message' => 'Déclaration introuvable ou déjà traitée.']);
        }

        $this->db->beginTransaction();
        try {
            $this->db->execute(
                "UPDATE declarations SET statut = 'rejetee', date_rejet = NOW(),
                 motif_rejet = $1, validateur_id = $2, updated_at = NOW()
                 WHERE id = $3",
                [$motif, $_SESSION['user_id'], (int) $id]
            );

            $this->db->execute(
                "INSERT INTO historique_declarations (declaration_id, utilisateur_id, action, ancien_statut, nouveau_statut, details, ip_address)
                 VALUES ($1, $2, 'rejet', 'soumise', 'rejetee', $3, $4)",
                [(int) $id, $_SESSION['user_id'], json_encode(['motif' => $motif]), getClientIp()]
            );

            $this->db->commit();
            logActivity('declaration_rejetee', 'declarations', (int) $id, ['motif' => $motif]);

            if (isAjax()) {
                $this->json(['success' => true, 'message' => 'Déclaration rejetée.']);
            }
            redirectWith('admin/declarations', 'warning', 'Déclaration rejetée.');
        } catch (\Exception $e) {
            $this->db->rollback();
            $this->json(['success' => false, 'message' => 'Erreur lors du rejet.']);
        }
    }

    /**
     * Gestion des utilisateurs
     */
    public function utilisateurs(): void
    {
        $search = get('q', '');
        $role   = get('role', '');
        $region = get('region', '');

        $where  = ['1=1'];
        $params = [];
        $i = 1;

        if ($search) {
            $where[] = "(u.nom ILIKE \$$i OR u.prenom ILIKE \$$i OR u.email ILIKE \$$i)";
            $params[] = "%$search%";
            $i++;
        }
        if ($role) {
            $where[] = "u.role = \$$i";
            $params[] = $role;
            $i++;
        }
        if ($region) {
            $where[] = "u.region_id = \$$i";
            $params[] = (int) $region;
            $i++;
        }

        $whereClause = implode(' AND ', $where);
        $total = (int) $this->db->fetchScalar(
            "SELECT COUNT(*) FROM utilisateurs u WHERE $whereClause",
            $params
        );

        $pagination = $this->paginate($total);

        $utilisateurs = $this->db->fetchAll(
            "SELECT u.*, r.nom AS region_nom,
                    (SELECT COUNT(*) FROM declarations WHERE agent_id = u.id) AS nb_declarations
             FROM utilisateurs u
             LEFT JOIN regions r ON r.id = u.region_id
             WHERE $whereClause
             ORDER BY u.created_at DESC
             LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}",
            $params
        );

        $regions = $this->db->fetchAll("SELECT id, nom FROM regions ORDER BY nom");

        $this->render('admin.utilisateurs', [
            'pageTitle'    => 'Utilisateurs - ' . APP_NAME,
            'utilisateurs' => $utilisateurs,
            'pagination'   => $pagination,
            'regions'      => $regions,
            'filters'      => compact('search', 'role', 'region'),
            'total'        => $total,
            'breadcrumbs'  => [
                ['label' => 'Tableau de bord', 'url' => '/admin/dashboard'],
                ['label' => 'Utilisateurs', 'url' => false],
            ],
        ]);
    }

    /**
     * Formulaire nouvel utilisateur
     */
    public function nouvelUtilisateur(): void
    {
        $regions = $this->db->fetchAll("SELECT id, nom FROM regions ORDER BY nom");
        $this->render('admin.utilisateur_form', [
            'pageTitle' => 'Nouvel utilisateur - ' . APP_NAME,
            'regions'   => $regions,
            'mode'      => 'create',
        ]);
    }

    /**
     * Créer un utilisateur
     */
    public function creerUtilisateur(): void
    {
        $this->requireCsrf();

        $data = [
            'nom'       => sanitize(post('nom', '')),
            'prenom'    => sanitize(post('prenom', '')),
            'email'     => strtolower(trim(post('email', ''))),
            'telephone' => sanitize(post('telephone', '')),
            'role'      => sanitize(post('role', '')),
            'region_id' => post('region_id') ?: null,
            'password'  => post('password', ''),
        ];

        $errors = $this->validate($data, [
            'nom'    => 'required|max:100',
            'email'  => 'required|email',
            'role'   => 'required',
            'password' => 'required|min:8',
        ]);

        $pwdErrors = \App\Helpers\Security::checkPasswordStrength($data['password']);
        if ($pwdErrors) $errors['password'] = implode(', ', $pwdErrors);

        // Email unique
        $exists = $this->db->fetchScalar("SELECT id FROM utilisateurs WHERE email = $1", [$data['email']]);
        if ($exists) $errors['email'] = 'Cet email est déjà utilisé.';

        // Rôle valide
        if (!in_array($data['role'], [ROLE_ADMIN, ROLE_AGENT])) {
            $errors['role'] = 'Rôle invalide.';
        }

        $regions = $this->db->fetchAll("SELECT id, nom FROM regions ORDER BY nom");

        if (!empty($errors)) {
            $this->render('admin.utilisateur_form', [
                'pageTitle' => 'Nouvel utilisateur - ' . APP_NAME,
                'regions'   => $regions,
                'errors'    => $errors,
                'old'       => $data,
                'mode'      => 'create',
            ]);
            return;
        }

        $hash = \App\Helpers\Security::hashPassword($data['password']);

        $this->db->insert(
            "INSERT INTO utilisateurs (nom, prenom, email, telephone, mot_de_passe, role, region_id, actif, email_verifie, created_by)
             VALUES ($1, $2, $3, $4, $5, $6, $7, TRUE, TRUE, $8)",
            [$data['nom'], $data['prenom'], $data['email'], $data['telephone'],
             $hash, $data['role'], $data['region_id'], $_SESSION['user_id']]
        );

        logActivity('user_created', 'utilisateurs', 0, ['email' => $data['email']]);
        redirectWith('admin/utilisateurs', 'success', 'Utilisateur créé avec succès.');
    }

    /**
     * Formulaire modification utilisateur
     */
    public function modifierUtilisateur(string $id): void
    {
        $user = $this->db->fetchOne("SELECT * FROM utilisateurs WHERE id = $1", [(int) $id]);
        if (!$user) redirectWith('admin/utilisateurs', 'error', 'Utilisateur introuvable.');

        $regions = $this->db->fetchAll("SELECT id, nom FROM regions ORDER BY nom");
        $this->render('admin.utilisateur_form', [
            'pageTitle' => 'Modifier utilisateur - ' . APP_NAME,
            'regions'   => $regions,
            'user'      => $user,
            'mode'      => 'edit',
        ]);
    }

    /**
     * Mettre à jour un utilisateur
     */
    public function updateUtilisateur(string $id): void
    {
        $this->requireCsrf();

        $user = $this->db->fetchOne("SELECT * FROM utilisateurs WHERE id = $1", [(int) $id]);
        if (!$user) redirectWith('admin/utilisateurs', 'error', 'Utilisateur introuvable.');

        // Empêcher la modification du super admin via l'interface
        if ($user['role'] === ROLE_SUPER_ADMIN && !isSuperAdmin()) {
            redirectWith('admin/utilisateurs', 'error', 'Opération non autorisée.');
        }

        $nom       = sanitize(post('nom', ''));
        $prenom    = sanitize(post('prenom', ''));
        $telephone = sanitize(post('telephone', ''));
        $role      = sanitize(post('role', ''));
        $regionId  = post('region_id') ?: null;
        $password  = post('password', '');

        $this->db->execute(
            "UPDATE utilisateurs SET nom=$1, prenom=$2, telephone=$3, role=$4, region_id=$5, updated_at=NOW() WHERE id=$6",
            [$nom, $prenom, $telephone, $role, $regionId, (int) $id]
        );

        // Changer le mot de passe si fourni
        if (!empty($password)) {
            $hash = \App\Helpers\Security::hashPassword($password);
            $this->db->execute(
                "UPDATE utilisateurs SET mot_de_passe = $1 WHERE id = $2",
                [$hash, (int) $id]
            );
        }

        logActivity('user_updated', 'utilisateurs', (int) $id);
        redirectWith('admin/utilisateurs', 'success', 'Utilisateur mis à jour avec succès.');
    }

    /**
     * Activer/Désactiver un utilisateur
     */
    public function toggleUtilisateur(string $id): void
    {
        $this->requireCsrf();
        $user = $this->db->fetchOne("SELECT * FROM utilisateurs WHERE id = $1", [(int) $id]);
        if (!$user || $user['role'] === ROLE_SUPER_ADMIN) {
            $this->json(['success' => false, 'message' => 'Opération non autorisée.']);
        }

        $nouvelEtat = !$user['actif'];
        $this->db->execute("UPDATE utilisateurs SET actif = $1 WHERE id = $2", [$nouvelEtat, (int) $id]);
        logActivity($nouvelEtat ? 'user_activated' : 'user_deactivated', 'utilisateurs', (int) $id);

        $this->json([
            'success' => true,
            'actif'   => $nouvelEtat,
            'message' => 'Compte ' . ($nouvelEtat ? 'activé' : 'désactivé') . ' avec succès.',
        ]);
    }

    /**
     * Statistiques
     */
    public function statistiques(): void
    {
        $campagne = $this->db->fetchOne(
            "SELECT * FROM campagnes_damo WHERE actif = TRUE ORDER BY annee DESC LIMIT 1"
        );
        $campagneId = $campagne['id'] ?? 0;

        // Effectifs par catégorie (total global)
        $effectifsParCategorie = $this->db->fetchAll(
            "SELECT categorie,
                    SUM(nigeriens_h + nigeriens_f + africains_h + africains_f + autres_nat_h + autres_nat_f) AS total,
                    SUM(nigeriens_h + africains_h + autres_nat_h) AS hommes,
                    SUM(nigeriens_f + africains_f + autres_nat_f) AS femmes
             FROM declaration_categories_effectifs dc
             JOIN declarations d ON d.id = dc.declaration_id
             WHERE d.campagne_id = $1 AND d.statut = 'validee'
             GROUP BY categorie",
            [$campagneId]
        );

        // Déclarations par statut
        $parStatut = $this->db->fetchAll(
            "SELECT statut, COUNT(*) AS total FROM declarations WHERE campagne_id = $1 GROUP BY statut",
            [$campagneId]
        );

        // Effectifs par région
        $effectifsParRegion = $this->db->fetchAll(
            "SELECT r.nom AS region,
                    COUNT(DISTINCT d.id) AS nb_declarations,
                    SUM(dc.nigeriens_h + dc.nigeriens_f + dc.africains_h + dc.africains_f + dc.autres_nat_h + dc.autres_nat_f) AS total_emplois
             FROM regions r
             LEFT JOIN declarations d ON d.region_id = r.id AND d.campagne_id = $1 AND d.statut = 'validee'
             LEFT JOIN declaration_categories_effectifs dc ON dc.declaration_id = d.id
             GROUP BY r.id, r.nom
             ORDER BY r.nom",
            [$campagneId]
        );

        $this->render('admin.statistiques', [
            'pageTitle'             => 'Statistiques - ' . APP_NAME,
            'campagne'              => $campagne,
            'effectifsParCategorie' => $effectifsParCategorie,
            'parStatut'             => $parStatut,
            'effectifsParRegion'    => $effectifsParRegion,
            'breadcrumbs'           => [
                ['label' => 'Tableau de bord', 'url' => '/admin/dashboard'],
                ['label' => 'Statistiques', 'url' => false],
            ],
        ]);
    }

    /**
     * Gestion des campagnes
     */
    public function campagnes(): void
    {
        $campagnes = $this->db->fetchAll(
            "SELECT c.*, 
                    (SELECT COUNT(*) FROM declarations WHERE campagne_id = c.id) AS nb_declarations,
                    u.nom AS createur_nom
             FROM campagnes_damo c
             LEFT JOIN utilisateurs u ON u.id = c.created_by
             ORDER BY c.annee DESC"
        );

        $this->render('admin.campagnes', [
            'pageTitle' => 'Campagnes DAMO - ' . APP_NAME,
            'campagnes' => $campagnes,
        ]);
    }

    /**
     * Nouvelle campagne
     */
    public function nouvelleCampagne(): void
    {
        $this->render('admin.campagne_form', [
            'pageTitle' => 'Nouvelle campagne - ' . APP_NAME,
            'mode'      => 'create',
        ]);
    }

    /**
     * Créer une campagne
     */
    public function creerCampagne(): void
    {
        $this->requireCsrf();
        $annee      = (int) post('annee', date('Y'));
        $libelle    = sanitize(post('libelle', ''));
        $dateDebut  = post('date_debut', '');
        $dateFin    = post('date_fin', '');
        $description = sanitize(post('description', ''));

        if ($annee < 2000 || $annee > 2100) {
            redirectWith('admin/campagnes', 'error', 'Année invalide.');
        }

        $exists = $this->db->fetchScalar("SELECT id FROM campagnes_damo WHERE annee = $1", [$annee]);
        if ($exists) {
            redirectWith('admin/campagnes', 'error', "Une campagne pour l'année $annee existe déjà.");
        }

        // Désactiver les autres campagnes
        $this->db->execute("UPDATE campagnes_damo SET actif = FALSE");

        $this->db->insert(
            "INSERT INTO campagnes_damo (annee, libelle, date_debut, date_fin, actif, description, created_by)
             VALUES ($1, $2, $3, $4, TRUE, $5, $6)",
            [$annee, $libelle ?: "Déclaration Annuelle $annee", $dateDebut, $dateFin, $description, $_SESSION['user_id']]
        );

        logActivity('campagne_created', 'campagnes', 0, ['annee' => $annee]);
        redirectWith('admin/campagnes', 'success', "Campagne $annee créée et activée avec succès.");
    }

    /**
     * Gestion des guides
     */
    public function guides(): void
    {
        $guides = $this->db->fetchAll(
            "SELECT g.*, u.nom AS createur FROM guides_documents g
             LEFT JOIN utilisateurs u ON u.id = g.created_by
             ORDER BY g.ordre, g.created_at DESC"
        );

        $this->render('admin.guides', [
            'pageTitle' => 'Guides & Documents - ' . APP_NAME,
            'guides'    => $guides,
        ]);
    }

    /**
     * Formulaire nouveau guide
     */
    public function nouveauGuide(): void
    {
        $this->render('admin.guide_form', [
            'pageTitle' => 'Nouveau guide - ' . APP_NAME,
        ]);
    }

    /**
     * Upload guide
     */
    public function uploadGuide(): void
    {
        $this->requireCsrf();

        $titre       = sanitize(post('titre', ''));
        $description = sanitize(post('description', ''));
        $annee       = (int) post('annee', date('Y'));

        if (!$titre) {
            redirectWith('admin/guide/nouveau', 'error', 'Le titre est obligatoire.');
        }

        if (empty($_FILES['fichier']['name'])) {
            redirectWith('admin/guide/nouveau', 'error', 'Veuillez sélectionner un fichier.');
        }

        $errors = \App\Helpers\Security::validateUploadedFile($_FILES['fichier'], ['application/pdf']);
        if ($errors) {
            redirectWith('admin/guide/nouveau', 'error', implode(', ', $errors));
        }

        $filename = \App\Helpers\Security::moveUploadedFile($_FILES['fichier'], GUIDES_UPLOAD_DIR, 'guide_');
        if (!$filename) {
            redirectWith('admin/guide/nouveau', 'error', 'Erreur lors de l\'upload du fichier.');
        }

        $this->db->insert(
            "INSERT INTO guides_documents (titre, description, fichier_nom, fichier_path, fichier_taille, fichier_type, annee, created_by)
             VALUES ($1, $2, $3, $4, $5, $6, $7, $8)",
            [$titre, $description, $_FILES['fichier']['name'],
             '/uploads/guides/' . $filename, $_FILES['fichier']['size'],
             $_FILES['fichier']['type'], $annee, $_SESSION['user_id']]
        );

        logActivity('guide_uploaded', 'guides', 0, ['titre' => $titre]);
        redirectWith('admin/guides', 'success', 'Guide uploadé avec succès.');
    }

    /**
     * Supprimer un guide
     */
    public function supprimerGuide(string $id): void
    {
        $this->requireCsrf();
        $guide = $this->db->fetchOne("SELECT * FROM guides_documents WHERE id = $1", [(int) $id]);
        if (!$guide) redirectWith('admin/guides', 'error', 'Guide introuvable.');

        // Supprimer le fichier physique
        $filePath = PUBLIC_PATH . $guide['fichier_path'];
        if (file_exists($filePath)) unlink($filePath);

        $this->db->execute("DELETE FROM guides_documents WHERE id = $1", [(int) $id]);
        logActivity('guide_deleted', 'guides', (int) $id);

        if (isAjax()) $this->json(['success' => true]);
        redirectWith('admin/guides', 'success', 'Guide supprimé.');
    }

    /**
     * Paramètres système
     */
    public function parametres(): void
    {
        $parametres = $this->db->fetchAll("SELECT * FROM parametres ORDER BY cle");
        $this->render('admin.parametres', [
            'pageTitle' => 'Paramètres - ' . APP_NAME,
            'parametres' => $parametres,
        ]);
    }

    /**
     * Mettre à jour les paramètres
     */
    public function updateParametres(): void
    {
        $this->requireCsrf();
        $parametres = $this->db->fetchAll("SELECT * FROM parametres WHERE modifiable = TRUE");

        foreach ($parametres as $param) {
            $value = post($param['cle'], '');
            if ($param['type'] === 'boolean') {
                $value = isset($_POST[$param['cle']]) ? 'true' : 'false';
            }
            $this->db->execute(
                "UPDATE parametres SET valeur = $1, updated_by = $2, updated_at = NOW() WHERE cle = $3",
                [sanitize($value), $_SESSION['user_id'], $param['cle']]
            );
        }

        logActivity('parametres_updated', 'parametres');
        redirectWith('admin/parametres', 'success', 'Paramètres mis à jour avec succès.');
    }

    /**
     * Branches d'activité
     */
    public function branches(): void
    {
        $branches = $this->db->fetchAll("SELECT * FROM branches_activite ORDER BY code");
        $this->render('admin.branches', [
            'pageTitle' => "Branches d'activité - " . APP_NAME,
            'branches'  => $branches,
        ]);
    }

    /**
     * Journaux d'audit
     */
    public function logs(): void
    {
        $search = get('q', '');
        $action = get('action', '');
        $dateFrom = get('date_from', '');
        $dateTo   = get('date_to', '');

        $where  = ['1=1'];
        $params = [];
        $i = 1;

        if ($search) {
            $where[] = "(u.email ILIKE \$$i OR u.nom ILIKE \$$i OR l.action ILIKE \$$i)";
            $params[] = "%$search%";
            $i++;
        }
        if ($action) {
            $where[] = "l.action = \$$i";
            $params[] = $action;
            $i++;
        }
        if ($dateFrom) {
            $where[] = "l.created_at >= \$$i";
            $params[] = $dateFrom . ' 00:00:00';
            $i++;
        }
        if ($dateTo) {
            $where[] = "l.created_at <= \$$i";
            $params[] = $dateTo . ' 23:59:59';
            $i++;
        }

        $whereClause = implode(' AND ', $where);
        $total = (int) $this->db->fetchScalar(
            "SELECT COUNT(*) FROM logs_activite l LEFT JOIN utilisateurs u ON u.id = l.utilisateur_id WHERE $whereClause",
            $params
        );

        $pagination = $this->paginate($total, 30);

        $logs = $this->db->fetchAll(
            "SELECT l.*, u.nom, u.prenom, u.email
             FROM logs_activite l
             LEFT JOIN utilisateurs u ON u.id = l.utilisateur_id
             WHERE $whereClause
             ORDER BY l.created_at DESC
             LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}",
            $params
        );

        $this->render('admin.logs', [
            'pageTitle'  => "Journaux d'audit - " . APP_NAME,
            'logs'       => $logs,
            'pagination' => $pagination,
            'total'      => $total,
            'filters'    => compact('search', 'action', 'dateFrom', 'dateTo'),
        ]);
    }

    /**
     * Export des déclarations (CSV)
     */
    public function exportDeclarations(): void
    {
        $campagneId = (int) get('campagne', 0);
        $statut     = get('statut', '');

        $where  = ['1=1'];
        $params = [];
        $i = 1;

        if ($campagneId) {
            $where[] = "d.campagne_id = \$$i"; $params[] = $campagneId; $i++;
        }
        if ($statut) {
            $where[] = "d.statut = \$$i"; $params[] = $statut; $i++;
        }

        $whereClause = implode(' AND ', $where);
        $declarations = $this->db->fetchAll(
            "SELECT d.code_questionnaire, d.statut, d.masse_salariale, d.date_soumission,
                    e.raison_sociale, e.numero_cnss, e.activite_principale,
                    r.nom AS region, c.annee,
                    u.nom AS agent, u.prenom AS agent_prenom
             FROM declarations d
             JOIN entreprises e ON e.id = d.entreprise_id
             JOIN regions r ON r.id = d.region_id
             JOIN campagnes_damo c ON c.id = d.campagne_id
             JOIN utilisateurs u ON u.id = d.agent_id
             WHERE $whereClause
             ORDER BY r.nom, e.raison_sociale",
            $params
        );

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="declarations_' . date('Y-m-d') . '.csv"');
        header('Pragma: no-cache');

        $out = fopen('php://output', 'w');
        // BOM UTF-8
        fputs($out, "\xEF\xBB\xBF");

        fputcsv($out, ['Code', 'Raison Sociale', 'N° CNSS', 'Région', 'Année', 'Statut',
                       'Masse Salariale', 'Date Soumission', 'Agent'], ';');

        foreach ($declarations as $d) {
            fputcsv($out, [
                $d['code_questionnaire'], $d['raison_sociale'], $d['numero_cnss'],
                $d['region'], $d['annee'], statutLabel($d['statut']),
                $d['masse_salariale'] ?? '', formatDate($d['date_soumission']),
                $d['agent_prenom'] . ' ' . $d['agent'],
            ], ';');
        }

        fclose($out);
        exit;
    }
}
