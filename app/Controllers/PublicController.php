<?php
/**
 * Contrôleur Public (Espace Grand Public)
 */
namespace App\Controllers;

class PublicController extends BaseController
{
    /**
     * Page d'accueil publique
     */
    public function index(): void
    {
        $campagne = $this->db->fetchOne(
            "SELECT * FROM campagnes_damo WHERE actif = TRUE ORDER BY annee DESC LIMIT 1"
        );

        // Statistiques générales publiées (déclarations validées)
        $stats = [
            'total_entreprises'     => (int) $this->db->fetchScalar("SELECT COUNT(*) FROM entreprises WHERE actif = TRUE"),
            'total_declarations'    => (int) $this->db->fetchScalar("SELECT COUNT(*) FROM declarations WHERE statut = 'validee'"),
            'regions_couvertes'     => (int) $this->db->fetchScalar("SELECT COUNT(DISTINCT region_id) FROM declarations WHERE statut = 'validee'"),
            'derniere_annee'        => $campagne['annee'] ?? date('Y'),
        ];

        // Effectifs globaux validés
        $effectifsGlobaux = $this->db->fetchOne(
            "SELECT 
                SUM(dc.nigeriens_h + dc.africains_h + dc.autres_nat_h) AS total_hommes,
                SUM(dc.nigeriens_f + dc.africains_f + dc.autres_nat_f) AS total_femmes,
                SUM(dc.nigeriens_h + dc.nigeriens_f) AS total_nigeriens,
                SUM(dc.africains_h + dc.africains_f + dc.autres_nat_h + dc.autres_nat_f) AS total_etrangers
             FROM declaration_categories_effectifs dc
             JOIN declarations d ON d.id = dc.declaration_id
             WHERE d.statut = 'validee'"
        );

        // Répartition par branche
        // MySQL : alias SUM interdit dans ORDER BY → sous-requête
        $parBranche = $this->db->fetchAll(
            "SELECT * FROM (
                SELECT b.libelle AS branche, COUNT(DISTINCT d.id) AS nb_entreprises,
                       SUM(dc.nigeriens_h + dc.nigeriens_f + dc.africains_h + dc.africains_f + dc.autres_nat_h + dc.autres_nat_f) AS total_emplois
                FROM declarations d
                JOIN declaration_categories_effectifs dc ON dc.declaration_id = d.id
                JOIN entreprises e ON e.id = d.entreprise_id
                JOIN branches_activite b ON b.id = e.branche_id
                WHERE d.statut = 'validee'
                GROUP BY b.id, b.libelle
             ) AS sub
             ORDER BY total_emplois DESC
             LIMIT 9"
        );

        // Guides disponibles
        $guides = $this->db->fetchAll(
            "SELECT id, titre, description, fichier_taille, annee 
             FROM guides_documents WHERE actif = TRUE ORDER BY ordre, annee DESC LIMIT 6"
        );

        $parametres = $this->getParametres();

        $this->render('public.index', [
            'pageTitle'       => APP_NAME . ' - ' . APP_FULL_NAME,
            'campagne'        => $campagne,
            'stats'           => $stats,
            'effectifsGlobaux'=> $effectifsGlobaux,
            'parBranche'      => $parBranche,
            'guides'          => $guides,
            'parametres'      => $parametres,
        ], 'public');
    }

    /**
     * Page statistiques/données publiques
     */
    public function statistiques(): void
    {
        $annee    = (int) (get('annee') ?? date('Y'));
        $campagne = $this->db->fetchOne(
            "SELECT * FROM campagnes_damo WHERE annee = $1",
            [$annee]
        );

        if (!$campagne) {
            $campagne = $this->db->fetchOne("SELECT * FROM campagnes_damo ORDER BY annee DESC LIMIT 1");
        }

        $campagneId = $campagne['id'] ?? 0;

        // Effectifs par catégorie professionnelle
        $parCategorie = $this->db->fetchAll(
            "SELECT categorie,
                    SUM(nigeriens_h + africains_h + autres_nat_h) AS hommes,
                    SUM(nigeriens_f + africains_f + autres_nat_f) AS femmes,
                    SUM(nigeriens_h + nigeriens_f) AS nigeriens,
                    SUM(africains_h + africains_f + autres_nat_h + autres_nat_f) AS etrangers
             FROM declaration_categories_effectifs dc
             JOIN declarations d ON d.id = dc.declaration_id
             WHERE d.campagne_id = $1 AND d.statut = 'validee'
             GROUP BY categorie",
            [$campagneId]
        );

        // Effectifs par région
        // Note MySQL : impossible de référencer un alias SUM() dans ORDER BY directement
        // → on wrappe dans une sous-requête pour pouvoir trier sur l'alias
        $parRegion = $this->db->fetchAll(
            "SELECT * FROM (
                SELECT r.nom AS region,
                       COUNT(DISTINCT d.id) AS nb_entreprises,
                       SUM(dc.nigeriens_h + dc.nigeriens_f + dc.africains_h + dc.africains_f + dc.autres_nat_h + dc.autres_nat_f) AS total_emplois,
                       SUM(dc.nigeriens_h + dc.africains_h + dc.autres_nat_h) AS hommes,
                       SUM(dc.nigeriens_f + dc.africains_f + dc.autres_nat_f) AS femmes
                FROM regions r
                LEFT JOIN declarations d ON d.region_id = r.id AND d.campagne_id = $1 AND d.statut = 'validee'
                LEFT JOIN declaration_categories_effectifs dc ON dc.declaration_id = d.id
                GROUP BY r.id, r.nom
             ) AS sub
             ORDER BY (total_emplois IS NULL) ASC, total_emplois DESC",
            [$campagneId]
        );

        // Pertes d'emploi par motif
        // MySQL : alias SUM interdit dans ORDER BY → sous-requête
        $pertesEmploi = $this->db->fetchAll(
            "SELECT * FROM (
                SELECT motif,
                       SUM(effectif_h) AS hommes,
                       SUM(effectif_f) AS femmes,
                       SUM(effectif_h + effectif_f) AS total
                FROM declaration_pertes_emploi dp
                JOIN declarations d ON d.id = dp.declaration_id
                WHERE d.campagne_id = $1 AND d.statut = 'validee'
                GROUP BY motif
             ) AS sub ORDER BY total DESC",
            [$campagneId]
        );

        // Niveaux d'instruction
        $parNiveau = $this->db->fetchAll(
            "SELECT niveau,
                    SUM(effectif_h) AS hommes, SUM(effectif_f) AS femmes,
                    SUM(effectif_h + effectif_f) AS total
             FROM declaration_niveaux_instruction dn
             JOIN declarations d ON d.id = dn.declaration_id
             WHERE d.campagne_id = $1 AND d.statut = 'validee'
             GROUP BY niveau",
            [$campagneId]
        );

        // Perspectives d'emploi
        $perspectives = $this->db->fetchAll(
            "SELECT perspective, COUNT(*) AS total
             FROM declaration_perspectives dp
             JOIN declarations d ON d.id = dp.declaration_id
             WHERE d.campagne_id = $1 AND d.statut = 'validee' AND dp.perspective IS NOT NULL
             GROUP BY perspective",
            [$campagneId]
        );

        // Années disponibles
        $anneesDisponibles = $this->db->fetchAll(
            "SELECT DISTINCT c.annee FROM campagnes_damo c
             JOIN declarations d ON d.campagne_id = c.id WHERE d.statut = 'validee'
             ORDER BY c.annee DESC"
        );

        $this->render('public.statistiques', [
            'pageTitle'         => "Statistiques {$annee} - " . APP_NAME,
            'campagne'          => $campagne,
            'parCategorie'      => $parCategorie,
            'parRegion'         => $parRegion,
            'pertesEmploi'      => $pertesEmploi,
            'parNiveau'         => $parNiveau,
            'perspectives'      => $perspectives,
            'anneesDisponibles' => $anneesDisponibles,
            'anneeSelectionnee' => $annee,
        ], 'public');
    }

    /**
     * Page des données (filtres avancés)
     */
    public function donnees(): void
    {
        $campagneId = (int) (get('campagne') ?? 0);
        $regionId   = (int) (get('region') ?? 0);
        $brancheId  = (int) (get('branche') ?? 0);

        $where  = ["d.statut = 'validee'"];
        $params = [];
        $i = 1;

        if ($campagneId) { $where[] = "d.campagne_id = \$$i"; $params[] = $campagneId; $i++; }
        if ($regionId)   { $where[] = "d.region_id = \$$i"; $params[] = $regionId; $i++; }
        if ($brancheId)  { $where[] = "e.branche_id = \$$i"; $params[] = $brancheId; $i++; }

        $whereClause = implode(' AND ', $where);
        $total = (int) $this->db->fetchScalar(
            "SELECT COUNT(*) FROM declarations d JOIN entreprises e ON e.id = d.entreprise_id WHERE $whereClause",
            $params
        );

        $pagination = $this->paginate($total);

        $resultats = $this->db->fetchAll(
            "SELECT e.raison_sociale, e.numero_cnss, e.activite_principale, b.libelle AS branche,
                    r.nom AS region, c.annee,
                    (SELECT SUM(em.effectif) / 12.0 FROM declaration_effectifs_mensuels em 
                     WHERE em.declaration_id = d.id) AS effectif_moyen,
                    d.masse_salariale, d.date_soumission
             FROM declarations d
             JOIN entreprises e ON e.id = d.entreprise_id
             JOIN regions r ON r.id = d.region_id
             JOIN campagnes_damo c ON c.id = d.campagne_id
             LEFT JOIN branches_activite b ON b.id = e.branche_id
             WHERE $whereClause
             ORDER BY r.nom, e.raison_sociale
             LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}",
            $params
        );

        $campagnes = $this->db->fetchAll("SELECT id, annee FROM campagnes_damo ORDER BY annee DESC");
        $regions   = $this->db->fetchAll("SELECT id, nom FROM regions ORDER BY nom");
        $branches  = $this->db->fetchAll("SELECT id, libelle FROM branches_activite WHERE actif=TRUE ORDER BY code");

        $this->render('public.donnees', [
            'pageTitle'  => "Données ouvertes - " . APP_NAME,
            'resultats'  => $resultats,
            'pagination' => $pagination,
            'total'      => $total,
            'campagnes'  => $campagnes,
            'regions'    => $regions,
            'branches'   => $branches,
            'filters'    => compact('campagneId', 'regionId', 'brancheId'),
        ], 'public');
    }

    /**
     * Page des guides
     */
    public function guides(): void
    {
        $guides = $this->db->fetchAll(
            "SELECT * FROM guides_documents WHERE actif = TRUE ORDER BY annee DESC, ordre"
        );

        $this->render('public.guides', [
            'pageTitle' => "Guides de remplissage - " . APP_NAME,
            'guides'    => $guides,
        ], 'public');
    }

    /**
     * Télécharger un guide
     */
    public function telechargerGuide(string $id): void
    {
        $guide = $this->db->fetchOne(
            "SELECT * FROM guides_documents WHERE id = $1 AND actif = TRUE",
            [(int) $id]
        );

        if (!$guide) {
            http_response_code(404);
            exit;
        }

        // Incrémenter le compteur de téléchargements
        $this->db->execute(
            "UPDATE guides_documents SET telechargements = telechargements + 1 WHERE id = $1",
            [(int) $id]
        );

        $filePath = rtrim(PUBLIC_PATH, '/') . '/' . ltrim($guide['fichier_path'], '/');
        if (!file_exists($filePath)) {
            // Fichier PDF non disponible : afficher une page d'erreur propre
            http_response_code(404);
            echo '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><title>Fichier non disponible</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"></head>
<body class="bg-light d-flex align-items-center justify-content-center" style="min-height:100vh">
<div class="text-center p-5"><h1 class="display-1 text-warning">404</h1>
<h2>Fichier non disponible</h2>
<p class="text-muted">Le guide "<strong>' . htmlspecialchars($guide['titre']) . '</strong>" n\'est pas encore disponible en téléchargement.</p>
<a href="/guides" class="btn btn-primary">Retour aux guides</a></div></body></html>';
            exit;
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $guide['fichier_nom'] . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: no-store, no-cache, must-revalidate');
        readfile($filePath);
        exit;
    }

    /**
     * Charger les paramètres publics
     */
    private function getParametres(): array
    {
        $rows = $this->db->fetchAll("SELECT cle, valeur FROM parametres");
        $params = [];
        foreach ($rows as $row) {
            $params[$row['cle']] = $row['valeur'];
        }
        return $params;
    }
}
