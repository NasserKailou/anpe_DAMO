<?php
/**
 * Contrôleur de Déclaration (saisie par étapes)
 */
namespace App\Controllers;

class DeclarationController extends BaseController
{
    /**
     * Liste des déclarations de l'agent
     */
    public function index(): void
    {
        $user       = currentUser();
        $search     = get('q', '');
        $statut     = get('statut', '');
        $campagneId = get('campagne', '');

        // Placeholders ? directs pour éviter les répétitions $n
        $where  = ['d.agent_id = ?'];
        $params = [$user['id']];

        if ($statut)    { $where[] = "d.statut = ?";     $params[] = $statut; }
        if ($campagneId){ $where[] = "d.campagne_id = ?"; $params[] = (int)$campagneId; }
        if ($search) {
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

        $pagination  = $this->paginate($total);
        $paramsPage  = array_merge($params, [$pagination['per_page'], $pagination['offset']]);

        $declarations = $this->db->fetchAllRaw(
            "SELECT d.*, e.raison_sociale, e.numero_cnss, r.nom AS region_nom, c.annee
             FROM declarations d
             JOIN entreprises e ON e.id = d.entreprise_id
             JOIN regions r ON r.id = d.region_id
             JOIN campagnes_damo c ON c.id = d.campagne_id
             WHERE $whereClause
             ORDER BY d.updated_at DESC
             LIMIT ? OFFSET ?",
            $paramsPage
        );

        $campagnes = $this->db->fetchAll("SELECT id, annee, libelle FROM campagnes_damo ORDER BY annee DESC");

        $this->render('agent.declarations', [
            'pageTitle'    => 'Mes déclarations - ' . APP_NAME,
            'declarations' => $declarations,
            'pagination'   => $pagination,
            'campagnes'    => $campagnes,
            'filters'      => compact('search', 'statut', 'campagneId'),
            'total'        => $total,
            'breadcrumbs'  => [
                ['label' => 'Mon tableau de bord', 'url' => '/agent/dashboard'],
                ['label' => 'Mes déclarations', 'url' => false],
            ],
        ]);
    }

    /**
     * Formulaire : Nouvelle déclaration (choisir entreprise + campagne)
     */
    public function nouvelle(): void
    {
        $user       = currentUser();
        $campagne   = $this->db->fetchOne(
            "SELECT * FROM campagnes_damo WHERE actif = TRUE ORDER BY annee DESC LIMIT 1"
        );

        if (!$campagne) {
            redirectWith('agent/declarations', 'error', 'Aucune campagne active. Contactez l\'administrateur.');
        }

        // Entreprises de la région de l'agent
        $entreprises = $this->db->fetchAll(
            "SELECT e.* FROM entreprises e WHERE e.region_id = $1 AND e.actif = TRUE
             AND e.id NOT IN (
                SELECT entreprise_id FROM declarations WHERE campagne_id = $2 AND agent_id = $3
             )
             ORDER BY e.raison_sociale",
            [$user['region_id'], $campagne['id'], $user['id']]
        );

        $this->render('agent.declaration_nouvelle', [
            'pageTitle'   => 'Nouvelle déclaration - ' . APP_NAME,
            'campagne'    => $campagne,
            'entreprises' => $entreprises,
            'breadcrumbs' => [
                ['label' => 'Mes déclarations', 'url' => '/agent/declarations'],
                ['label' => 'Nouvelle', 'url' => false],
            ],
        ]);
    }

    /**
     * Créer la déclaration initiale
     */
    public function creer(): void
    {
        $this->requireCsrf();
        $user       = currentUser();
        $campagne   = $this->db->fetchOne(
            "SELECT * FROM campagnes_damo WHERE actif = TRUE ORDER BY annee DESC LIMIT 1"
        );

        if (!$campagne) {
            $this->json(['success' => false, 'message' => 'Aucune campagne active.']);
        }

        $entrepriseId = (int) post('entreprise_id', 0);
        if (!$entrepriseId) {
            redirectWith('agent/declaration/nouvelle', 'error', 'Veuillez sélectionner une entreprise.');
        }

        // Vérifier que l'entreprise existe et appartient à la région de l'agent
        $entreprise = $this->db->fetchOne(
            "SELECT * FROM entreprises WHERE id = $1 AND region_id = $2 AND actif = TRUE",
            [$entrepriseId, $user['region_id']]
        );

        if (!$entreprise) {
            redirectWith('agent/declaration/nouvelle', 'error', 'Entreprise introuvable ou non autorisée.');
        }

        // Vérifier qu'une déclaration n'existe pas déjà
        $exists = $this->db->fetchScalar(
            "SELECT id FROM declarations WHERE campagne_id = $1 AND entreprise_id = $2",
            [$campagne['id'], $entrepriseId]
        );
        if ($exists) {
            redirect("agent/declaration/$exists/saisie");
        }

        // Générer le code questionnaire
        $codeRegion = $this->db->fetchScalar("SELECT code FROM regions WHERE id = $1", [$user['region_id']]);
        $nextNum    = $this->db->fetchScalar(
            "SELECT COUNT(*) + 1 FROM declarations WHERE region_id = $1 AND campagne_id = $2",
            [$user['region_id'], $campagne['id']]
        );
        $codeQuestion = sprintf('%s/%02d/%03d', $codeRegion, 1, $nextNum);

        $decId = $this->db->insert(
            "INSERT INTO declarations (code_questionnaire, campagne_id, entreprise_id, agent_id, region_id,
             nom_enqueteur, statut, etape_courante, ip_saisie)
             VALUES ($1, $2, $3, $4, $5, $6, 'brouillon', 1, $7)",
            [
                $codeQuestion, $campagne['id'], $entrepriseId, $user['id'],
                $user['region_id'],
                ($user['prenom'] ?? '') . ' ' . ($user['nom'] ?? ''),
                getClientIp()
            ]
        );

        // Pré-remplir les tables de saisie (12 mois, catégories, etc.)
        $this->preRemplirDeclaration((int) $decId);

        logActivity('declaration_created', 'declarations', (int) $decId, ['code' => $codeQuestion]);
        redirect("agent/declaration/$decId/saisie");
    }

    /**
     * Pré-remplir les lignes vides pour la saisie
     */
    private function preRemplirDeclaration(int $decId): void
    {
        // 12 mois
        for ($m = 1; $m <= 12; $m++) {
            $this->db->execute(
                "INSERT INTO declaration_effectifs_mensuels (declaration_id, mois, effectif) VALUES ($1, $2, 0)",
                [$decId, $m]
            );
        }

        // Catégories professionnelles × origines
        $categories = array_keys(CATEGORIES_PROFESSIONNELLES);
        foreach ($categories as $cat) {
            $this->db->execute(
                "INSERT INTO declaration_categories_effectifs (declaration_id, categorie) VALUES ($1, $2)
                 ON CONFLICT DO NOTHING",
                [$decId, $cat]
            );

            // Niveaux d'instruction pour chaque catégorie
            foreach (array_keys(NIVEAUX_INSTRUCTION) as $niveau) {
                $this->db->execute(
                    "INSERT INTO declaration_niveaux_instruction (declaration_id, categorie, niveau)
                     VALUES ($1, $2, $3) ON CONFLICT DO NOTHING",
                    [$decId, $cat, $niveau]
                );
            }
        }

        // Motifs de perte d'emploi
        foreach (array_keys(MOTIFS_PERTE_EMPLOI) as $motif) {
            $this->db->execute(
                "INSERT INTO declaration_pertes_emploi (declaration_id, motif) VALUES ($1, $2)
                 ON CONFLICT DO NOTHING",
                [$decId, $motif]
            );
        }

        // Perspectives
        $this->db->execute(
            "INSERT INTO declaration_perspectives (declaration_id) VALUES ($1) ON CONFLICT DO NOTHING",
            [$decId]
        );

        // Formation (une ligne vide)
        $this->db->execute(
            "INSERT INTO declaration_formations (declaration_id, a_eu_formation) VALUES ($1, FALSE)",
            [$decId]
        );
    }

    /**
     * Page de saisie (formulaire multi-étapes)
     */
    public function saisie(string $id): void
    {
        $user        = currentUser();
        $declaration = $this->getDeclarationForAgent((int) $id, $user);

        if (!$declaration) {
            redirectWith('agent/declarations', 'error', 'Déclaration introuvable.');
        }

        if (in_array($declaration['statut'], ['validee', 'soumise'])) {
            redirect("agent/declaration/$id/apercu");
        }

        $etape = max(1, min(7, (int) (get('etape') ?? $declaration['etape_courante'] ?? 1)));

        // Charger les données de chaque section
        $data = $this->chargerDonneesDeclaration((int) $id);

        $this->render('agent.declaration_saisie', [
            'pageTitle'   => 'Saisie déclaration - ' . APP_NAME,
            'declaration' => $declaration,
            'entreprise'  => $data['entreprise'],
            'etape'       => $etape,
            'data'        => $data,
            'extraCss'    => ['/assets/css/saisie.css'],
            'extraJs'     => ['/assets/js/saisie.js'],
            'breadcrumbs' => [
                ['label' => 'Mes déclarations', 'url' => '/agent/declarations'],
                ['label' => 'Saisie #' . $declaration['code_questionnaire'], 'url' => false],
            ],
        ]);
    }

    /**
     * Sauvegarder une étape (AJAX ou POST normal)
     */
    public function sauvegarder(string $id): void
    {
        if (!isAjax()) {
            $this->requireCsrf();
        }

        $user        = currentUser();
        $declaration = $this->getDeclarationForAgent((int) $id, $user);

        if (!$declaration) {
            $this->json(['success' => false, 'message' => 'Déclaration introuvable.']);
        }

        if ($declaration['statut'] === 'validee') {
            $this->json(['success' => false, 'message' => 'Cette déclaration est validée et ne peut plus être modifiée.']);
        }

        $etape = (int) (post('etape') ?? get('etape') ?? 1);
        $result = $this->sauvegarderEtapeData((int) $id, $etape);

        // Mettre à jour l'étape courante
        $etapeCourante = max($etape, (int) $declaration['etape_courante']);
        $completion    = (int) (($etapeCourante / 7) * 100);
        $this->db->execute(
            "UPDATE declarations SET etape_courante = $1, pourcentage_completion = $2, updated_at = NOW() WHERE id = $3",
            [$etapeCourante, $completion, (int) $id]
        );

        if (isAjax()) {
            $this->json(['success' => $result, 'message' => $result ? 'Sauvegardé' : 'Erreur de sauvegarde', 'etape' => $etape]);
        }

        $nextEtape = $etape < 7 ? $etape + 1 : $etape;
        redirect("agent/declaration/$id/saisie?etape=$nextEtape");
    }

    /**
     * Sauvegarder les données d'une étape spécifique
     */
    private function sauvegarderEtapeData(int $decId, int $etape): bool
    {
        try {
            switch ($etape) {
                case 1: // Identification entreprise + masse salariale
                    $masseSalariale = post('masse_salariale', null);
                    $nomEnqueteur   = sanitize(post('nom_enqueteur', ''));
                    $this->db->execute(
                        "UPDATE declarations SET masse_salariale = $1, nom_enqueteur = $2, updated_at = NOW() WHERE id = $3",
                        [$masseSalariale ?: null, $nomEnqueteur, $decId]
                    );
                    // Mettre à jour les infos entreprise
                    $this->updateEntrepriseFromDeclaration($decId);
                    break;

                case 2: // Effectifs mensuels
                    $effectifs = post('effectifs', []);
                    for ($m = 1; $m <= 12; $m++) {
                        $val = positiveInt($effectifs[$m] ?? 0);
                        $this->db->execute(
                            "UPDATE declaration_effectifs_mensuels SET effectif = $1, updated_at = NOW()
                             WHERE declaration_id = $2 AND mois = $3",
                            [$val, $decId, $m]
                        );
                    }
                    break;

                case 3: // Catégories × origines × sexes
                    $categories = post('categories', []);
                    foreach (array_keys(CATEGORIES_PROFESSIONNELLES) as $cat) {
                        $row = $categories[$cat] ?? [];
                        $this->db->execute(
                            "UPDATE declaration_categories_effectifs
                             SET nigeriens_h=$1, nigeriens_f=$2, africains_h=$3, africains_f=$4,
                                 autres_nat_h=$5, autres_nat_f=$6, updated_at=NOW()
                             WHERE declaration_id=$7 AND categorie=$8",
                            [
                                positiveInt($row['nigeriens_h'] ?? 0),
                                positiveInt($row['nigeriens_f'] ?? 0),
                                positiveInt($row['africains_h'] ?? 0),
                                positiveInt($row['africains_f'] ?? 0),
                                positiveInt($row['autres_nat_h'] ?? 0),
                                positiveInt($row['autres_nat_f'] ?? 0),
                                $decId, $cat
                            ]
                        );
                    }
                    break;

                case 4: // Niveaux d'instruction
                    $niveaux = post('niveaux', []);
                    foreach (array_keys(CATEGORIES_PROFESSIONNELLES) as $cat) {
                        foreach (array_keys(NIVEAUX_INSTRUCTION) as $niv) {
                            $row = $niveaux[$cat][$niv] ?? [];
                            $this->db->execute(
                                "UPDATE declaration_niveaux_instruction
                                 SET effectif_h=$1, effectif_f=$2, updated_at=NOW()
                                 WHERE declaration_id=$3 AND categorie=$4 AND niveau=$5",
                                [positiveInt($row['h'] ?? 0), positiveInt($row['f'] ?? 0), $decId, $cat, $niv]
                            );
                        }
                    }
                    break;

                case 5: // Formation professionnelle
                    $aEuFormation = post('a_eu_formation', '0') === '1';
                    $qualification = sanitize(post('qualification', ''));
                    $nature        = sanitize(post('nature_formation', ''));
                    $duree         = sanitize(post('duree_formation', ''));
                    $effectifH     = positiveInt(post('formation_h', 0));
                    $effectifF     = positiveInt(post('formation_f', 0));
                    $observations  = sanitize(post('observations', ''));

                    $this->db->execute(
                        "UPDATE declaration_formations
                         SET a_eu_formation=$1, qualification=$2, nature_formation=$3, duree_formation=$4,
                             effectif_h=$5, effectif_f=$6, observations=$7, updated_at=NOW()
                         WHERE declaration_id=$8",
                        [$aEuFormation, $qualification, $nature, $duree, $effectifH, $effectifF, $observations, $decId]
                    );
                    break;

                case 6: // Pertes d'emploi
                    $pertes = post('pertes', []);
                    foreach (array_keys(MOTIFS_PERTE_EMPLOI) as $motif) {
                        $row         = $pertes[$motif] ?? [];
                        $motifAutre  = $motif === 'autres' ? sanitize($row['autre_precision'] ?? '') : null;
                        $this->db->execute(
                            "UPDATE declaration_pertes_emploi
                             SET effectif_h=$1, effectif_f=$2, motif_autre=$3, updated_at=NOW()
                             WHERE declaration_id=$4 AND motif=$5",
                            [positiveInt($row['h'] ?? 0), positiveInt($row['f'] ?? 0), $motifAutre, $decId, $motif]
                        );
                    }

                    // Perspectives
                    $perspective   = sanitize(post('perspective', ''));
                    $justification = sanitize(post('justification', ''));
                    $this->db->execute(
                        "UPDATE declaration_perspectives SET perspective=$1, justification=$2, updated_at=NOW()
                         WHERE declaration_id=$3",
                        [$perspective ?: null, $justification, $decId]
                    );
                    break;

                case 7: // Effectifs étrangers
                    // Supprimer et re-insérer
                    $this->db->execute("DELETE FROM declaration_effectifs_etrangers WHERE declaration_id = $1", [$decId]);
                    $etrangers = post('etrangers', []);
                    foreach ($etrangers as $row) {
                        if (empty($row['pays'])) continue;
                        $this->db->execute(
                            "INSERT INTO declaration_effectifs_etrangers (declaration_id, pays, qualification, fonction, sexe, nombre)
                             VALUES ($1, $2, $3, $4, $5, $6)",
                            [$decId, sanitize($row['pays']), sanitize($row['qualification'] ?? ''),
                             sanitize($row['fonction'] ?? ''), sanitize($row['sexe'] ?? 'H'),
                             positiveInt($row['nombre'] ?? 0)]
                        );
                    }
                    break;
            }
            return true;
        } catch (\Exception $e) {
            error_log('Erreur sauvegarde étape ' . $etape . ': ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Soumettre la déclaration
     */
    public function soumettre(string $id): void
    {
        $this->requireCsrf();
        $user        = currentUser();
        $declaration = $this->getDeclarationForAgent((int) $id, $user);

        if (!$declaration || $declaration['statut'] !== 'brouillon' && $declaration['statut'] !== 'corrigee') {
            $this->json(['success' => false, 'message' => 'Impossible de soumettre cette déclaration.']);
        }

        $this->db->beginTransaction();
        try {
            $this->db->execute(
                "UPDATE declarations SET statut = 'soumise', date_soumission = NOW(),
                 pourcentage_completion = 100, updated_at = NOW() WHERE id = $1",
                [(int) $id]
            );

            $this->db->execute(
                "INSERT INTO historique_declarations (declaration_id, utilisateur_id, action, ancien_statut, nouveau_statut, ip_address)
                 VALUES ($1, $2, 'soumission', $3, 'soumise', $4)",
                [(int) $id, $user['id'], $declaration['statut'], getClientIp()]
            );

            $this->db->commit();
            logActivity('declaration_soumise', 'declarations', (int) $id);

            // ── Notifications email ──
            try {
                $notif   = new \App\Helpers\NotificationService();
                $decFull = $this->db->fetchOne(
                    "SELECT d.*, e.raison_sociale, r.nom AS region_nom, c.libelle AS campagne_libelle, c.annee,
                            u.nom AS agent_nom, u.prenom AS agent_prenom, u.email AS agent_email
                     FROM declarations d
                     JOIN entreprises e ON e.id = d.entreprise_id
                     JOIN regions r ON r.id = d.region_id
                     JOIN campagnes_damo c ON c.id = d.campagne_id
                     JOIN utilisateurs u ON u.id = d.agent_id
                     WHERE d.id = $1",
                    [(int) $id]
                );
                if ($decFull) {
                    $agentInfo = ['nom' => $decFull['agent_nom'], 'prenom' => $decFull['agent_prenom'], 'email' => $decFull['agent_email']];
                    $notif->declarationSoumise($decFull, $agentInfo);
                    // Notifier les admins
                    $admins = $this->db->fetchAll(
                        "SELECT email, nom, prenom FROM utilisateurs WHERE role IN ('admin','super_admin') AND actif = TRUE"
                    );
                    $notif->notifierAdminsNouvelleDeclaration($admins, $decFull);
                }
            } catch (\Exception $e) {
                error_log('[Notif] Erreur soumission: ' . $e->getMessage());
            }

            if (isAjax()) {
                $this->json(['success' => true, 'message' => 'Déclaration soumise avec succès!', 'redirect' => "/agent/declaration/$id/apercu"]);
            }
            redirectWith("agent/declaration/$id/apercu", 'success', 'Déclaration soumise avec succès à l\'ANPE!');
        } catch (\Exception $e) {
            $this->db->rollback();
            $this->json(['success' => false, 'message' => 'Erreur lors de la soumission.']);
        }
    }

    /**
     * Aperçu / récapitulatif de la déclaration
     */
    public function apercu(string $id): void
    {
        $user        = currentUser();
        $declaration = $this->getDeclarationForAgent((int) $id, $user);

        if (!$declaration) {
            redirectWith('agent/declarations', 'error', 'Déclaration introuvable.');
        }

        $data = $this->chargerDonneesDeclaration((int) $id);

        $this->render('agent.declaration_apercu', [
            'pageTitle'   => 'Aperçu déclaration - ' . APP_NAME,
            'declaration' => $declaration,
            'data'        => $data,
            'extraCss'    => ['/assets/css/saisie.css'],
            'breadcrumbs' => [
                ['label' => 'Mes déclarations', 'url' => '/agent/declarations'],
                ['label' => 'Aperçu #' . $declaration['code_questionnaire'], 'url' => false],
            ],
        ]);
    }

    /**
     * Modifier une déclaration rejetée
     */
    public function modifier(string $id): void
    {
        $user        = currentUser();
        $declaration = $this->getDeclarationForAgent((int) $id, $user);

        if (!$declaration || $declaration['statut'] !== 'rejetee') {
            redirectWith('agent/declarations', 'error', 'Cette déclaration ne peut pas être modifiée.');
        }

        // Remettre en brouillon pour correction
        $this->db->execute(
            "UPDATE declarations SET statut = 'corrigee', etape_courante = 1, updated_at = NOW() WHERE id = $1",
            [(int) $id]
        );

        redirect("agent/declaration/$id/saisie");
    }

    /**
     * Corriger une déclaration rejetée (POST depuis la vue)
     * Route : POST /agent/declaration/:id/corriger
     */
    public function corriger(string $id): void
    {
        $this->requireCsrf();
        $user        = currentUser();
        $declaration = $this->getDeclarationForAgent((int) $id, $user);

        if (!$declaration) {
            redirectWith('agent/declarations', 'error', 'Déclaration introuvable.');
        }

        if ($declaration['statut'] !== 'rejetee') {
            redirectWith('agent/declarations', 'error', 'Seules les déclarations rejetées peuvent être corrigées.');
        }

        // Remettre en statut "corrigee" pour permettre la saisie
        $this->db->execute(
            "UPDATE declarations SET statut = 'corrigee', etape_courante = 1, updated_at = NOW() WHERE id = $1",
            [(int) $id]
        );

        // Journaliser l'action
        logActivity('declaration_correction_started', 'declarations', (int) $id, [
            'ancien_statut' => 'rejetee',
            'nouveau_statut' => 'corrigee',
        ]);

        redirectWith("agent/declaration/$id/saisie", 'success', 'Vous pouvez maintenant corriger votre déclaration.');
    }

    /**
     * Mettre à jour les infos entreprise depuis la déclaration
     */
    private function updateEntrepriseFromDeclaration(int $decId): void
    {
        $decl = $this->db->fetchOne("SELECT entreprise_id FROM declarations WHERE id = $1", [$decId]);
        if (!$decl) return;

        $data = [
            'raison_sociale'    => sanitize(post('raison_sociale', '')),
            'nationalite'       => sanitize(post('nationalite', '')),
            'activite_principale'=> sanitize(post('activite_principale', '')),
            'activites_secondaires'=> sanitize(post('activites_secondaires', '')),
            'branche_id'        => post('branche_id') ? (int)post('branche_id') : null,
            'localite'          => sanitize(post('localite', '')),
            'quartier'          => sanitize(post('quartier', '')),
            'boite_postale'     => sanitize(post('boite_postale', '')),
            'telephone'         => sanitize(post('telephone', '')),
            'fax'               => sanitize(post('fax', '')),
            'email'             => sanitize(post('email', '')),
            'numero_cnss'       => sanitize(post('numero_cnss', '')),
            'departement_id'    => post('departement_id') ? (int)post('departement_id') : null,
            'commune_id'        => post('commune_id') ? (int)post('commune_id') : null,
        ];

        $this->db->execute(
            "UPDATE entreprises SET
             raison_sociale=$1, nationalite=$2, activite_principale=$3, activites_secondaires=$4,
             branche_id=$5, localite=$6, quartier=$7, boite_postale=$8, telephone=$9,
             fax=$10, email=$11, numero_cnss=$12, departement_id=$13, commune_id=$14,
             updated_at=NOW()
             WHERE id=$15",
            array_merge(array_values($data), [$decl['entreprise_id']])
        );
    }

    /**
     * Récupérer et valider l'accès de l'agent à la déclaration
     */
    private function getDeclarationForAgent(int $id, array $user): ?array
    {
        $query = "SELECT d.*, e.raison_sociale, e.numero_cnss, e.activite_principale,
                         e.nationalite, e.localite, e.telephone AS ent_tel, e.email AS ent_email,
                         e.boite_postale, e.quartier, e.branche_id, e.departement_id, e.commune_id,
                         e.numero_cnss, e.activites_secondaires, e.adresse,
                         r.nom AS region_nom, c.annee, c.libelle AS campagne_libelle
                  FROM declarations d
                  JOIN entreprises e ON e.id = d.entreprise_id
                  JOIN regions r ON r.id = d.region_id
                  JOIN campagnes_damo c ON c.id = d.campagne_id
                  WHERE d.id = $1";

        $params = [$id];

        // Les agents ne voient que leurs propres déclarations
        if ($user['role'] === ROLE_AGENT) {
            $query  .= " AND d.agent_id = $2";
            $params[] = $user['id'];
        }

        return $this->db->fetchOne($query, $params);
    }

    /**
     * Charger toutes les données d'une déclaration
     */
    private function chargerDonneesDeclaration(int $id): array
    {
        $declaration  = $this->db->fetchOne(
            "SELECT d.*, e.*, r.nom AS region_nom, b.libelle AS branche_libelle,
                    c.annee, c.libelle AS campagne_libelle,
                    dept.nom AS dept_nom, com.nom AS commune_nom
             FROM declarations d
             JOIN entreprises e ON e.id = d.entreprise_id
             JOIN regions r ON r.id = d.region_id
             JOIN campagnes_damo c ON c.id = d.campagne_id
             LEFT JOIN branches_activite b ON b.id = e.branche_id
             LEFT JOIN departements dept ON dept.id = e.departement_id
             LEFT JOIN communes com ON com.id = e.commune_id
             WHERE d.id = $1",
            [$id]
        );

        $effectifsMensuels   = $this->db->fetchAll(
            "SELECT mois, effectif FROM declaration_effectifs_mensuels WHERE declaration_id = $1 ORDER BY mois",
            [$id]
        );
        $catEffectifs        = $this->db->fetchAll(
            "SELECT * FROM declaration_categories_effectifs WHERE declaration_id = $1",
            [$id]
        );
        $niveauxInstruction  = $this->db->fetchAll(
            "SELECT * FROM declaration_niveaux_instruction WHERE declaration_id = $1",
            [$id]
        );
        $formations          = $this->db->fetchAll(
            "SELECT * FROM declaration_formations WHERE declaration_id = $1",
            [$id]
        );
        $pertesEmploi        = $this->db->fetchAll(
            "SELECT * FROM declaration_pertes_emploi WHERE declaration_id = $1",
            [$id]
        );
        $perspective         = $this->db->fetchOne(
            "SELECT * FROM declaration_perspectives WHERE declaration_id = $1",
            [$id]
        );
        $effectifsEtrangers  = $this->db->fetchAll(
            "SELECT * FROM declaration_effectifs_etrangers WHERE declaration_id = $1",
            [$id]
        );
        $branches            = $this->db->fetchAll("SELECT id, code, libelle FROM branches_activite WHERE actif=TRUE ORDER BY code");
        $departements        = $this->db->fetchAll(
            "SELECT d.* FROM departements d JOIN regions r ON r.id = d.region_id WHERE d.region_id = (SELECT region_id FROM declarations WHERE id = $1)",
            [$id]
        );
        $communes            = $this->db->fetchAll(
            "SELECT c.* FROM communes c WHERE c.departement_id = COALESCE((SELECT departement_id FROM entreprises WHERE id = (SELECT entreprise_id FROM declarations WHERE id = $1)), 0)",
            [$id]
        );

        // Transformer les effectifs mensuels en tableau indexé par mois
        $mensuelMap = [];
        foreach ($effectifsMensuels as $row) {
            $mensuelMap[$row['mois']] = $row['effectif'];
        }

        // Transformer catégories en tableau indexé
        $catMap = [];
        foreach ($catEffectifs as $row) {
            $catMap[$row['categorie']] = $row;
        }

        // Transformer niveaux en tableau indexé [categorie][niveau]
        $niveauMap = [];
        foreach ($niveauxInstruction as $row) {
            $niveauMap[$row['categorie']][$row['niveau']] = $row;
        }

        // Pertes en tableau indexé
        $pertesMap = [];
        foreach ($pertesEmploi as $row) {
            $pertesMap[$row['motif']] = $row;
        }

        return [
            'entreprise'        => $declaration,
            'effectifs_mensuels'=> $mensuelMap,
            'categories'        => $catMap,
            'niveaux'           => $niveauMap,
            'formations'        => $formations,
            'pertes'            => $pertesMap,
            'perspective'       => $perspective,
            'etrangers'         => $effectifsEtrangers,
            'branches'          => $branches,
            'departements'      => $departements,
            'communes'          => $communes,
        ];
    }
}
