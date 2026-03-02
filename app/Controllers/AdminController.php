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

            // ── Notification email agent ──
            try {
                $notif = new \App\Helpers\NotificationService();
                $agent = $this->db->fetchOne(
                    "SELECT u.nom, u.prenom, u.email
                     FROM declarations d JOIN utilisateurs u ON u.id = d.agent_id
                     WHERE d.id = $1",
                    [(int) $id]
                );
                if ($agent) {
                    $notif->declarationValidee($declaration, $agent, $observations);
                }
            } catch (\Exception $e) {
                error_log('[Notif] Erreur validation: ' . $e->getMessage());
            }

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

            // ── Notification email agent ──
            try {
                $notif = new \App\Helpers\NotificationService();
                $agent = $this->db->fetchOne(
                    "SELECT u.nom, u.prenom, u.email
                     FROM declarations d JOIN utilisateurs u ON u.id = d.agent_id
                     WHERE d.id = $1",
                    [(int) $id]
                );
                if ($agent) {
                    $notif->declarationRejetee($declaration, $agent, $motif);
                }
            } catch (\Exception $e) {
                error_log('[Notif] Erreur rejet: ' . $e->getMessage());
            }

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

        if ($search) {
            // Chaque ILIKE a son propre ? (fetchAllRaw nécessite des ? purs)
            $where[] = "(u.nom ILIKE ? OR u.prenom ILIKE ? OR u.email ILIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        if ($role) {
            $where[] = "u.role = ?";
            $params[] = $role;
        }
        if ($region) {
            $where[] = "u.region_id = ?";
            $params[] = (int) $region;
        }

        $whereClause = implode(' AND ', $where);
        $total = (int) $this->db->fetchScalarRaw(
            "SELECT COUNT(*) FROM utilisateurs u WHERE $whereClause",
            $params
        );

        $pagination = $this->paginate($total);
        $paramsPage = array_merge($params, [$pagination['per_page'], $pagination['offset']]);

        $utilisateurs = $this->db->fetchAllRaw(
            "SELECT u.*, r.nom AS region_nom,
                    (SELECT COUNT(*) FROM declarations WHERE agent_id = u.id) AS nb_declarations
             FROM utilisateurs u
             LEFT JOIN regions r ON r.id = u.region_id
             WHERE $whereClause
             ORDER BY u.created_at DESC
             LIMIT ? OFFSET ?",
            $paramsPage
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

        // ── Notification de bienvenue ──
        try {
            $notif    = new \App\Helpers\NotificationService();
            $regionNom = '';
            if ($data['region_id']) {
                $reg = $this->db->fetchOne("SELECT nom FROM regions WHERE id = $1", [(int)$data['region_id']]);
                $regionNom = $reg['nom'] ?? '';
            }
            $notif->welcomeAgent(
                array_merge($data, ['region_nom' => $regionNom]),
                $data['password']
            );
        } catch (\Exception $e) {
            error_log('[Notif] Erreur welcome: ' . $e->getMessage());
        }

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
        $annee    = (int) get('annee', 0);
        $campagne = $annee
            ? $this->db->fetchOne("SELECT * FROM campagnes_damo WHERE annee = $1", [$annee])
            : $this->db->fetchOne("SELECT * FROM campagnes_damo WHERE actif = TRUE ORDER BY annee DESC LIMIT 1");

        $campagneId = $campagne['id'] ?? 0;

        // Effectifs par catégorie (total global)
        $effectifsParCategorie = $this->db->fetchAll(
            "SELECT categorie,
                    SUM(nigeriens_h + nigeriens_f + africains_h + africains_f + autres_nat_h + autres_nat_f) AS total,
                    SUM(nigeriens_h + africains_h + autres_nat_h) AS hommes,
                    SUM(nigeriens_f + africains_f + autres_nat_f) AS femmes
             FROM declaration_categories_effectifs dc
             JOIN declarations d ON d.id = dc.declaration_id
             WHERE d.campagne_id = \$1 AND d.statut = 'validee'
             GROUP BY categorie
             ORDER BY total DESC",
            [$campagneId]
        );

        // Déclarations par statut
        $parStatut = $this->db->fetchAll(
            "SELECT statut, COUNT(*) AS total FROM declarations WHERE campagne_id = \$1 GROUP BY statut",
            [$campagneId]
        );

        // Effectifs par région
        $effectifsParRegion = $this->db->fetchAll(
            "SELECT r.nom AS region,
                    COUNT(DISTINCT d.id) AS nb_declarations,
                    COALESCE(SUM(dc.nigeriens_h + dc.nigeriens_f + dc.africains_h + dc.africains_f + dc.autres_nat_h + dc.autres_nat_f), 0) AS total_emplois
             FROM regions r
             LEFT JOIN declarations d ON d.region_id = r.id AND d.campagne_id = \$1 AND d.statut = 'validee'
             LEFT JOIN declaration_categories_effectifs dc ON dc.declaration_id = d.id
             GROUP BY r.id, r.nom
             ORDER BY r.nom",
            [$campagneId]
        );

        // Top entreprises par effectifs
        $topEntreprises = $this->db->fetchAll(
            "SELECT e.raison_sociale, r.nom AS region,
                    COALESCE(SUM(dc.nigeriens_h+dc.nigeriens_f+dc.africains_h+dc.africains_f+dc.autres_nat_h+dc.autres_nat_f), 0) AS emplois
             FROM declarations d
             JOIN entreprises e ON e.id = d.entreprise_id
             JOIN regions r ON r.id = d.region_id
             LEFT JOIN declaration_categories_effectifs dc ON dc.declaration_id = d.id
             WHERE d.campagne_id = \$1 AND d.statut = 'validee'
             GROUP BY e.id, e.raison_sociale, r.nom
             ORDER BY emplois DESC LIMIT 10",
            [$campagneId]
        );

        $this->render('admin.statistiques', [
            'pageTitle'             => 'Statistiques - ' . APP_NAME,
            'campagne'              => $campagne,
            'effectifsParCategorie' => $effectifsParCategorie,
            'parStatut'             => $parStatut,
            'effectifsParRegion'    => $effectifsParRegion,
            'topEntreprises'        => $topEntreprises,
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

        if ($search) {
            $where[] = "(u.email ILIKE ? OR u.nom ILIKE ? OR l.action ILIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        if ($action) {
            $where[] = "l.action = ?";
            $params[] = $action;
        }
        if ($dateFrom) {
            $where[] = "l.created_at >= ?";
            $params[] = $dateFrom . ' 00:00:00';
        }
        if ($dateTo) {
            $where[] = "l.created_at <= ?";
            $params[] = $dateTo . ' 23:59:59';
        }

        $whereClause = implode(' AND ', $where);
        $total = (int) $this->db->fetchScalarRaw(
            "SELECT COUNT(*) FROM logs_activite l LEFT JOIN utilisateurs u ON u.id = l.utilisateur_id WHERE $whereClause",
            $params
        );

        $pagination = $this->paginate($total, 30);
        $paramsPage = array_merge($params, [$pagination['per_page'], $pagination['offset']]);

        $logs = $this->db->fetchAllRaw(
            "SELECT l.*, u.nom, u.prenom, u.email
             FROM logs_activite l
             LEFT JOIN utilisateurs u ON u.id = l.utilisateur_id
             WHERE $whereClause
             ORDER BY l.created_at DESC
             LIMIT ? OFFSET ?",
            $paramsPage
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

        if ($campagneId) {
            $where[] = "d.campagne_id = ?"; $params[] = $campagneId;
        }
        if ($statut) {
            $where[] = "d.statut = ?"; $params[] = $statut;
        }

        $whereClause = implode(' AND ', $where);
        $declarations = $this->db->fetchAllRaw(
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

    /**
     * Export CSV des entreprises
     */
    public function exportEntreprises(): void
    {
        $regionId = (int) get('region', 0);
        $where    = ['1=1'];
        $params   = [];
        if ($regionId) {
            $where[]  = 'd.region_id = ?';
            $params[] = $regionId;
        }
        $whereClause = implode(' AND ', $where);

        $entreprises = $this->db->fetchAllRaw(
            "SELECT e.raison_sociale, e.numero_cnss, e.telephone, e.email,
                    e.activite_principale, e.nationalite, e.localite,
                    r.nom AS region, b.libelle AS branche
             FROM entreprises e
             LEFT JOIN regions r ON r.id = e.region_id
             LEFT JOIN branches_activite b ON b.id = e.branche_id
             WHERE e.actif = TRUE
             ORDER BY r.nom, e.raison_sociale",
            []
        );

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="entreprises_' . date('Y-m-d') . '.csv"');
        $out = fopen('php://output', 'w');
        fputs($out, "\xEF\xBB\xBF");
        fputcsv($out, ['Raison Sociale', 'N° CNSS', 'Téléphone', 'Email', 'Activité', 'Nationalité', 'Localité', 'Région', 'Branche'], ';');
        foreach ($entreprises as $e) {
            fputcsv($out, array_values($e), ';');
        }
        fclose($out);
        exit;
    }

    /**
     * Export PDF d'une déclaration (HTML → PDF via wkhtmltopdf ou HTML pur)
     */
    public function exportPdf(string $id): void
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

        $effectifsMensuels   = $this->db->fetchAll("SELECT * FROM declaration_effectifs_mensuels WHERE declaration_id = $1 ORDER BY mois", [(int)$id]);
        $categoriesEffectifs = $this->db->fetchAll("SELECT * FROM declaration_categories_effectifs WHERE declaration_id = $1", [(int)$id]);
        $niveauxInstruction  = $this->db->fetchAll("SELECT * FROM declaration_niveaux_instruction WHERE declaration_id = $1", [(int)$id]);
        $formations          = $this->db->fetchAll("SELECT * FROM declaration_formations WHERE declaration_id = $1", [(int)$id]);
        $pertesEmploi        = $this->db->fetchAll("SELECT * FROM declaration_pertes_emploi WHERE declaration_id = $1", [(int)$id]);
        $perspective         = $this->db->fetchOne("SELECT * FROM declaration_perspectives WHERE declaration_id = $1", [(int)$id]);
        $effectifsEtrangers  = $this->db->fetchAll("SELECT * FROM declaration_effectifs_etrangers WHERE declaration_id = $1", [(int)$id]);

        // Générer le HTML du PDF
        $html = $this->generatePdfHtml(
            $declaration, $effectifsMensuels, $categoriesEffectifs,
            $niveauxInstruction, $formations, $pertesEmploi,
            $perspective, $effectifsEtrangers
        );

        // En-têtes pour le téléchargement HTML (impression navigateur)
        $filename = 'declaration_' . $declaration['code_questionnaire'] . '_' . date('Ymd') . '.html';
        $filename = str_replace('/', '-', $filename);
        header('Content-Type: text/html; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo $html;
        exit;
    }

    /**
     * Générer le HTML du rapport PDF
     */
    private function generatePdfHtml(
        array $d, array $effectifsMensuels, array $categories,
        array $niveaux, array $formations, array $pertes,
        ?array $perspective, array $etrangers
    ): string {
        $code    = htmlspecialchars($d['code_questionnaire']);
        $raison  = htmlspecialchars($d['raison_sociale'] ?? '');
        $cnss    = htmlspecialchars($d['numero_cnss'] ?? '');
        $region  = htmlspecialchars($d['region_nom'] ?? '');
        $annee   = htmlspecialchars((string)($d['annee'] ?? ''));
        $statut  = statutLabel($d['statut'] ?? '');
        $agent   = htmlspecialchars(($d['agent_prenom'] ?? '') . ' ' . ($d['agent_nom'] ?? ''));
        $campagne = htmlspecialchars($d['campagne_libelle'] ?? '');
        $moisLabels = ['Jan','Fév','Mar','Avr','Mai','Jun','Jul','Aoû','Sep','Oct','Nov','Déc'];

        // Tableau effectifs mensuels
        $rowsMensuels = '';
        foreach ($effectifsMensuels as $em) {
            $moisLabel = $moisLabels[($em['mois'] - 1)] ?? $em['mois'];
            $rowsMensuels .= "<tr><td>$moisLabel</td><td style='text-align:right'>{$em['effectif']}</td></tr>";
        }

        // Tableau catégories
        $rowsCats = '';
        foreach ($categories as $cat) {
            $total = ($cat['nigeriens_h'] ?? 0) + ($cat['nigeriens_f'] ?? 0)
                   + ($cat['africains_h'] ?? 0) + ($cat['africains_f'] ?? 0)
                   + ($cat['autres_nat_h'] ?? 0) + ($cat['autres_nat_f'] ?? 0);
            $catLabel = CATEGORIES_PROFESSIONNELLES[$cat['categorie']] ?? $cat['categorie'];
            $rowsCats .= "<tr>
                <td>" . htmlspecialchars($catLabel) . "</td>
                <td>{$cat['nigeriens_h']}</td><td>{$cat['nigeriens_f']}</td>
                <td>{$cat['africains_h']}</td><td>{$cat['africains_f']}</td>
                <td>{$cat['autres_nat_h']}</td><td>{$cat['autres_nat_f']}</td>
                <td><strong>$total</strong></td>
            </tr>";
        }

        // Section formation
        $formationHtml = '';
        if (!empty($formations)) {
            $f = $formations[0];
            $ouiNon = $f['a_eu_formation'] ? 'Oui' : 'Non';
            $formationHtml = "
            <h3 style='color:#1d4ed8'>5. Formation professionnelle</h3>
            <table class='data'>
                <tr><th>A eu une formation</th><td>$ouiNon</td></tr>
                <tr><th>Qualification</th><td>" . htmlspecialchars($f['qualification'] ?? '') . "</td></tr>
                <tr><th>Nature formation</th><td>" . htmlspecialchars($f['nature_formation'] ?? '') . "</td></tr>
                <tr><th>Durée</th><td>" . htmlspecialchars($f['duree_formation'] ?? '') . "</td></tr>
                <tr><th>Effectif H/F</th><td>{$f['effectif_h']} H / {$f['effectif_f']} F</td></tr>
            </table>";
        }

        // Section pertes d'emploi
        $pertesHtml = '';
        if (!empty($pertes)) {
            $rows = '';
            foreach ($pertes as $p) {
                $label = MOTIFS_PERTE_EMPLOI[$p['motif']] ?? $p['motif'];
                $rows .= "<tr><td>" . htmlspecialchars($label) . "</td><td>{$p['effectif_h']}</td><td>{$p['effectif_f']}</td></tr>";
            }
            $pertesHtml = "
            <h3 style='color:#1d4ed8'>6. Pertes d'emploi</h3>
            <table class='data'>
                <thead><tr><th>Motif</th><th>Hommes</th><th>Femmes</th></tr></thead>
                <tbody>$rows</tbody>
            </table>";
        }

        $now = date('d/m/Y à H:i');
        $dateSubmission = formatDate($d['date_soumission'] ?? '');
        $brancheNom   = htmlspecialchars($d['branche_nom'] ?? '');
        $nationalite  = htmlspecialchars($d['nationalite'] ?? '');
        $localite     = htmlspecialchars($d['localite'] ?? '');
        $masseSalFmt  = number_format((float)($d['masse_salariale'] ?? 0), 2, ',', ' ');
        $decId        = (int)$d['id'];
        $statut_class = htmlspecialchars($d['statut'] ?? 'brouillon');

        return <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Déclaration $code — $appName</title>
<style>
  * { box-sizing: border-box; }
  body { font-family: Arial, sans-serif; font-size: 12px; color: #111; margin: 0; padding: 20px; }
  .header { background: #1d4ed8; color: white; padding: 20px; margin-bottom: 24px; }
  .header h1 { margin: 0; font-size: 18px; }
  .header p  { margin: 4px 0 0; font-size: 12px; opacity: .85; }
  .badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: bold; }
  .badge-validee { background: #d1fae5; color: #065f46; }
  .badge-soumise { background: #fef3c7; color: #92400e; }
  .badge-rejetee { background: #fee2e2; color: #7f1d1d; }
  .badge-brouillon { background: #f3f4f6; color: #374151; }
  h2 { color: #1e40af; border-bottom: 2px solid #1d4ed8; padding-bottom: 4px; font-size: 14px; margin-top: 24px; }
  h3 { color: #1d4ed8; font-size: 13px; margin-top: 20px; }
  table.data { width: 100%; border-collapse: collapse; margin-bottom: 16px; font-size: 11px; }
  table.data th, table.data td { border: 1px solid #d1d5db; padding: 6px 8px; }
  table.data th { background: #eff6ff; font-weight: bold; }
  table.data tr:nth-child(even) td { background: #f9fafb; }
  .footer { margin-top: 40px; text-align: center; font-size: 10px; color: #6b7280; border-top: 1px solid #e5e7eb; padding-top: 12px; }
  @media print { .no-print { display: none; } body { padding: 10px; } }
</style>
</head>
<body>
<div class="no-print" style="background:#1d4ed8;color:white;padding:12px;margin-bottom:16px;border-radius:8px">
  <strong>Mode impression</strong> : Appuyez sur <kbd>Ctrl+P</kbd> pour imprimer ou enregistrer en PDF.
  <a href="$appUrl/admin/declaration/$decId" style="float:right;color:white;text-decoration:underline">&larr; Retour</a>
</div>
<div class="header">
  <h1>e-DAMO &mdash; Déclaration de la Main d’&OElig;uvre</h1>
  <p>ANPE Niger &mdash; Exercice $annee &mdash; Campagne : $campagne</p>
</div>
<table class="data" style="margin-bottom:20px">
  <tr><th style="width:30%">Code questionnaire</th><td><strong>$code</strong></td>
      <th style="width:30%">Statut</th><td><span class="badge badge-$statut_class">$statut</span></td></tr>
  <tr><th>Raison sociale</th><td><strong>$raison</strong></td><th>N&deg; CNSS</th><td>$cnss</td></tr>
  <tr><th>R&eacute;gion</th><td>$region</td><th>Campagne</th><td>$campagne</td></tr>
  <tr><th>Agent</th><td>$agent</td><th>Date soumission</th><td>$dateSubmission</td></tr>
</table>
<h2>1. Identification de l&apos;entreprise</h2>
<table class="data">
  <tr><th style="width:30%">Raison sociale</th><td>$raison</td><th style="width:30%">N&deg; CNSS</th><td>$cnss</td></tr>
  <tr><th>Branche d&apos;activit&eacute;</th><td>$brancheNom</td><th>Nationalit&eacute;</th><td>$nationalite</td></tr>
  <tr><th>Localit&eacute;</th><td>$localite</td><th>Masse salariale</th><td>$masseSalFmt FCFA</td></tr>
</table>
<h2>2. Effectifs mensuels</h2>
<table class="data">
  <thead><tr><th>Mois</th><th>Effectif</th></tr></thead>
  <tbody>$rowsMensuels</tbody>
</table>
<h2>3. Effectifs par cat&eacute;gorie</h2>
<table class="data">
  <thead>
    <tr>
      <th rowspan="2">Cat&eacute;gorie</th>
      <th colspan="2">Nig&eacute;riens</th>
      <th colspan="2">Africains</th>
      <th colspan="2">Autres</th>
      <th rowspan="2">Total</th>
    </tr>
    <tr><th>H</th><th>F</th><th>H</th><th>F</th><th>H</th><th>F</th></tr>
  </thead>
  <tbody>$rowsCats</tbody>
</table>
$formationHtml
$pertesHtml
<div class="footer">
  <p>Document g&eacute;n&eacute;r&eacute; le $now par $appName &mdash; ANPE Niger</p>
  <p>D&eacute;claration r&eacute;f&eacute;rence : $code</p>
</div>
</body></html>
HTML;
    }

    /**
     * Formulaire d'import CSV des entreprises
     */
    public function importEntreprisesForm(): void
    {
        $regions = $this->db->fetchAll("SELECT id, nom FROM regions ORDER BY nom");
        $this->render('admin.import_entreprises', [
            'pageTitle' => 'Import CSV Entreprises - ' . APP_NAME,
            'regions'   => $regions,
        ]);
    }

    /**
     * Traitement de l'import CSV des entreprises
     */
    public function importEntreprises(): void
    {
        $this->requireCsrf();

        if (empty($_FILES['csv_file']['tmp_name'])) {
            redirectWith('admin/import/entreprises', 'error', 'Veuillez sélectionner un fichier CSV.');
        }

        $regionId  = (int) post('region_id', 0);
        $delimiter = post('delimiter', ';');
        $skipFirst = post('skip_header', '1') === '1';

        if (!$regionId) {
            redirectWith('admin/import/entreprises', 'error', 'Veuillez sélectionner une région.');
        }

        $tmpFile = $_FILES['csv_file']['tmp_name'];
        $handle  = fopen($tmpFile, 'r');
        if (!$handle) {
            redirectWith('admin/import/entreprises', 'error', 'Impossible de lire le fichier.');
        }

        $imported = 0; $errors = 0; $skipped = 0;
        $lineNum  = 0;
        $errorLog = [];

        while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) {
            $lineNum++;
            if ($lineNum === 1 && $skipFirst) continue; // En-tête

            // Format attendu : raison_sociale, numero_cnss, telephone, email, activite_principale, nationalite, localite
            if (count($row) < 2) {
                $errors++;
                $errorLog[] = "Ligne $lineNum : données insuffisantes";
                continue;
            }

            $raisonSociale = sanitize(trim($row[0] ?? ''));
            $numeroCnss    = sanitize(trim($row[1] ?? ''));
            $telephone     = sanitize(trim($row[2] ?? ''));
            $email         = strtolower(trim($row[3] ?? ''));
            $activite      = sanitize(trim($row[4] ?? ''));
            $nationalite   = sanitize(trim($row[5] ?? 'Nigérienne'));
            $localite      = sanitize(trim($row[6] ?? ''));

            if (!$raisonSociale) {
                $errors++;
                $errorLog[] = "Ligne $lineNum : raison sociale manquante";
                continue;
            }

            // Vérifier si l'entreprise existe déjà (par CNSS)
            if ($numeroCnss) {
                $existingId = $this->db->fetchScalar(
                    "SELECT id FROM entreprises WHERE numero_cnss = $1",
                    [$numeroCnss]
                );
                if ($existingId) {
                    $skipped++;
                    continue;
                }
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
                        $_SESSION['user_id']
                    ]
                );
                $imported++;
            } catch (\Exception $e) {
                $errors++;
                $errorLog[] = "Ligne $lineNum : " . $e->getMessage();
            }
        }
        fclose($handle);

        logActivity('csv_import_entreprises', 'entreprises', 0, [
            'imported' => $imported, 'errors' => $errors, 'skipped' => $skipped,
        ]);

        $msg = "Import terminé : <strong>$imported entreprise(s) importée(s)</strong>";
        if ($skipped) $msg .= ", $skipped ignorée(s) (doublon CNSS)";
        if ($errors)  $msg .= ", $errors erreur(s)";
        if (!empty($errorLog)) {
            error_log('[CSV Import] ' . implode(' | ', $errorLog));
        }

        redirectWith('admin/utilisateurs', 'success', $msg);
    }

    /**
     * Envoyer des rappels de campagne aux agents avec déclarations en brouillon
     */
    public function envoyerRappels(string $id): void
    {
        $campagne = $this->db->fetchOne("SELECT * FROM campagnes_damo WHERE id = $1", [(int)$id]);
        if (!$campagne) {
            redirectWith('admin/campagnes', 'error', 'Campagne introuvable.');
        }

        $joursRestants = (int) ceil((strtotime($campagne['date_fin']) - time()) / 86400);
        if ($joursRestants < 0) {
            redirectWith('admin/campagnes', 'warning', 'La campagne est déjà clôturée.');
        }

        // Agents avec des déclarations non soumises
        $agents = $this->db->fetchAll(
            "SELECT DISTINCT u.email, u.nom, u.prenom
             FROM utilisateurs u
             JOIN declarations d ON d.agent_id = u.id
             WHERE d.campagne_id = $1 AND d.statut = 'brouillon' AND u.actif = TRUE",
            [(int)$id]
        );

        $notif = new \App\Helpers\NotificationService();
        $sent  = 0;
        foreach ($agents as $agent) {
            if ($notif->rappelClotureCampagne($agent, $campagne, $joursRestants)) {
                $sent++;
            }
        }

        logActivity('rappels_envoyes', 'campagnes', (int)$id, ['nb_agents' => $sent, 'jours' => $joursRestants]);
        redirectWith('admin/campagnes', 'success', "$sent rappel(s) envoyé(s) aux agents.");
    }
}
