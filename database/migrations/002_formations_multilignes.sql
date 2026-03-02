-- ============================================================
-- Migration 002 : Formation professionnelle multi-lignes (RAMO 2025)
-- Le formulaire RAMO autorise plusieurs lignes par formation
-- On ajoute un champ "ligne" pour identifier chaque ligne du tableau
-- ============================================================

-- Ajouter colonne ligne_ordre si elle n'existe pas déjà
ALTER TABLE declaration_formations
    ADD COLUMN IF NOT EXISTS ligne_ordre INTEGER DEFAULT 1;

-- Ajouter colonne activites_secondaires dans entreprises
ALTER TABLE entreprises
    ADD COLUMN IF NOT EXISTS activites_secondaires TEXT;

-- Ajouter total_nigeriens et total_etrangers dans declarations pour Section VI
ALTER TABLE declarations
    ADD COLUMN IF NOT EXISTS total_nigeriens INTEGER DEFAULT 0,
    ADD COLUMN IF NOT EXISTS total_etrangers INTEGER DEFAULT 0;

-- Ajouter motif_precis (colonne "Autres motifs à préciser") dans pertes_emploi
-- (déjà nommé motif_autre dans la table - pas de changement nécessaire)

-- Vue Section VI : total main d'œuvre nigérienne et étrangère
-- Ces données sont dérivées de declaration_categories_effectifs
-- mais on conserve aussi la saisie manuelle du formulaire RAMO

