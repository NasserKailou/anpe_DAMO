<?php
/**
 * Contrôleur API (AJAX)
 */
namespace App\Controllers;

class ApiController extends BaseController
{
    /**
     * Statistiques publiques pour les graphiques
     */
    public function statsPubliques(): void
    {
        $campagne = $this->db->fetchOne(
            "SELECT * FROM campagnes_damo WHERE actif = TRUE ORDER BY annee DESC LIMIT 1"
        );
        $campagneId = $campagne['id'] ?? 0;

        $parRegion = $this->db->fetchAll(
            "SELECT r.nom, COUNT(d.id) AS total
             FROM regions r
             LEFT JOIN declarations d ON d.region_id = r.id AND d.campagne_id = $1 AND d.statut = 'validee'
             GROUP BY r.id, r.nom ORDER BY r.nom",
            [$campagneId]
        );

        $parCategorie = $this->db->fetchAll(
            "SELECT categorie,
                    SUM(nigeriens_h+africains_h+autres_nat_h) AS hommes,
                    SUM(nigeriens_f+africains_f+autres_nat_f) AS femmes
             FROM declaration_categories_effectifs dc
             JOIN declarations d ON d.id = dc.declaration_id
             WHERE d.campagne_id = $1 AND d.statut = 'validee'
             GROUP BY categorie",
            [$campagneId]
        );

        $this->json([
            'success'      => true,
            'par_region'   => $parRegion,
            'par_categorie'=> $parCategorie,
        ]);
    }

    /**
     * Statistiques admin
     */
    public function statsAdmin(): void
    {
        $campagne   = $this->db->fetchOne("SELECT * FROM campagnes_damo WHERE actif=TRUE ORDER BY annee DESC LIMIT 1");
        $campagneId = $campagne['id'] ?? 0;

        $evolution = $this->db->fetchAll(
            "SELECT DATE_TRUNC('week', d.created_at) AS semaine,
                    COUNT(*) AS total,
                    SUM(CASE WHEN d.statut = 'validee' THEN 1 ELSE 0 END) AS validees
             FROM declarations d
             WHERE d.campagne_id = $1
             GROUP BY DATE_TRUNC('week', d.created_at)
             ORDER BY semaine",
            [$campagneId]
        );

        $parStatut = $this->db->fetchAll(
            "SELECT statut, COUNT(*) AS total FROM declarations WHERE campagne_id = $1 GROUP BY statut",
            [$campagneId]
        );

        $this->json([
            'success'   => true,
            'evolution' => $evolution,
            'par_statut'=> $parStatut,
        ]);
    }

    /**
     * Données d'un graphique spécifique
     */
    public function chartData(string $type): void
    {
        $annee    = (int) (get('annee') ?? date('Y'));
        $campagne = $this->db->fetchOne("SELECT * FROM campagnes_damo WHERE annee = $1", [$annee]);
        $campagneId = $campagne['id'] ?? 0;

        $data = match($type) {
            'categories' => $this->getChartCategories($campagneId),
            'regions'    => $this->getChartRegions($campagneId),
            'niveaux'    => $this->getChartNiveaux($campagneId),
            'pertes'     => $this->getChartPertes($campagneId),
            'genre'      => $this->getChartGenre($campagneId),
            default      => ['error' => 'Type inconnu']
        };

        $this->json(['success' => true, 'data' => $data, 'annee' => $annee]);
    }

    private function getChartCategories(int $campagneId): array
    {
        $rows = $this->db->fetchAll(
            "SELECT categorie,
                    SUM(nigeriens_h+africains_h+autres_nat_h+nigeriens_f+africains_f+autres_nat_f) AS total
             FROM declaration_categories_effectifs dc
             JOIN declarations d ON d.id = dc.declaration_id
             WHERE d.campagne_id = $1 AND d.statut = 'validee'
             GROUP BY categorie",
            [$campagneId]
        );

        $labels = [];
        $values = [];
        foreach ($rows as $row) {
            $labels[] = CATEGORIES_PROFESSIONNELLES[$row['categorie']] ?? $row['categorie'];
            $values[] = (int) $row['total'];
        }
        return compact('labels', 'values');
    }

    private function getChartRegions(int $campagneId): array
    {
        $rows = $this->db->fetchAll(
            "SELECT r.nom AS region, COUNT(DISTINCT d.id) AS nb_entreprises,
                    COALESCE(SUM(dc.nigeriens_h+dc.nigeriens_f+dc.africains_h+dc.africains_f+dc.autres_nat_h+dc.autres_nat_f), 0) AS emplois
             FROM regions r
             LEFT JOIN declarations d ON d.region_id = r.id AND d.campagne_id = $1 AND d.statut = 'validee'
             LEFT JOIN declaration_categories_effectifs dc ON dc.declaration_id = d.id
             GROUP BY r.id, r.nom ORDER BY r.nom",
            [$campagneId]
        );

        $labels    = array_column($rows, 'region');
        $emplois   = array_map(fn($r) => (int)$r['emplois'], $rows);
        $entreprises = array_map(fn($r) => (int)$r['nb_entreprises'], $rows);
        return compact('labels', 'emplois', 'entreprises');
    }

    private function getChartNiveaux(int $campagneId): array
    {
        $rows = $this->db->fetchAll(
            "SELECT * FROM (
                SELECT niveau, SUM(effectif_h+effectif_f) AS total
                FROM declaration_niveaux_instruction dn
                JOIN declarations d ON d.id = dn.declaration_id
                WHERE d.campagne_id = $1 AND d.statut = 'validee'
                GROUP BY niveau
             ) AS sub ORDER BY total DESC",
            [$campagneId]
        );

        $labels = array_map(fn($r) => NIVEAUX_INSTRUCTION[$r['niveau']] ?? $r['niveau'], $rows);
        $values = array_map(fn($r) => (int)$r['total'], $rows);
        return compact('labels', 'values');
    }

    private function getChartPertes(int $campagneId): array
    {
        $rows = $this->db->fetchAll(
            "SELECT motif, SUM(effectif_h+effectif_f) AS total
             FROM declaration_pertes_emploi dp
             JOIN declarations d ON d.id = dp.declaration_id
             WHERE d.campagne_id = $1 AND d.statut = 'validee'
             GROUP BY motif",
            [$campagneId]
        );

        $labels = array_map(fn($r) => MOTIFS_PERTE_EMPLOI[$r['motif']] ?? $r['motif'], $rows);
        $values = array_map(fn($r) => (int)$r['total'], $rows);
        return compact('labels', 'values');
    }

    private function getChartGenre(int $campagneId): array
    {
        $row = $this->db->fetchOne(
            "SELECT SUM(nigeriens_h+africains_h+autres_nat_h) AS hommes,
                    SUM(nigeriens_f+africains_f+autres_nat_f) AS femmes
             FROM declaration_categories_effectifs dc
             JOIN declarations d ON d.id = dc.declaration_id
             WHERE d.campagne_id = $1 AND d.statut = 'validee'",
            [$campagneId]
        );
        return [
            'labels' => ['Hommes', 'Femmes'],
            'values' => [(int)($row['hommes'] ?? 0), (int)($row['femmes'] ?? 0)],
        ];
    }

    /**
     * Obtenir les départements d'une région (AJAX)
     */
    public function getDepartements(string $regionId): void
    {
        $departements = $this->db->fetchAll(
            "SELECT id, nom FROM departements WHERE region_id = $1 AND actif = TRUE ORDER BY nom",
            [(int) $regionId]
        );
        $this->json(['success' => true, 'departements' => $departements]);
    }

    /**
     * Obtenir les communes d'un département (AJAX)
     */
    public function getCommunes(string $deptId): void
    {
        $communes = $this->db->fetchAll(
            "SELECT id, nom FROM communes WHERE departement_id = $1 AND actif = TRUE ORDER BY nom",
            [(int) $deptId]
        );
        $this->json(['success' => true, 'communes' => $communes]);
    }

    /**
     * Rechercher une entreprise (AJAX)
     */
    public function rechercheEntreprise(): void
    {
        $q      = get('q', '');
        $region = currentUser()['region_id'] ?? 0;

        if (strlen($q) < 2) {
            $this->json(['success' => true, 'entreprises' => []]);
        }

        $entreprises = $this->db->fetchAll(
            "SELECT id, raison_sociale, numero_cnss, activite_principale
             FROM entreprises
             WHERE region_id = $1 AND actif = TRUE
             AND (raison_sociale ILIKE $2 OR numero_cnss ILIKE $2)
             ORDER BY raison_sociale
             LIMIT 10",
            [$region, '%' . $q . '%']
        );

        $this->json(['success' => true, 'entreprises' => $entreprises]);
    }

    /**
     * Sauvegarder une étape de déclaration (AJAX)
     */
    public function sauvegarderEtape(string $id, string $etape): void
    {
        if (!verifyCsrf()) {
            $this->json(['success' => false, 'message' => 'Token invalide']);
        }

        $controller = new DeclarationController();
        // Déléguer au contrôleur de déclaration
        $_POST['etape'] = $etape;
        $controller->sauvegarder($id);
    }

    /**
     * Obtenir les données d'une étape (AJAX)
     */
    public function getEtape(string $id, string $etape): void
    {
        $user = currentUser();
        $decl = $this->db->fetchOne(
            "SELECT * FROM declarations WHERE id = $1 AND agent_id = $2",
            [(int) $id, $user['id']]
        );

        if (!$decl) {
            $this->json(['success' => false, 'message' => 'Déclaration introuvable']);
        }

        $this->json(['success' => true, 'etape' => (int) $etape, 'declaration_id' => $id]);
    }
}
