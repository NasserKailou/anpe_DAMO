-- ============================================================
-- Patch : Ajout du guide DAMO 2026
-- À exécuter sur la base PostgreSQL de production
-- Usage : psql -U edamo_user -d edamo_db -f patch_guide_2026.sql
-- ============================================================

-- S'assurer que le fichier PDF est bien dans uploads/guides/
-- Chemin relatif : public/uploads/guides/guide_damo_2026.pdf

INSERT INTO guides_documents (
    titre, description, fichier_nom, fichier_path,
    fichier_taille, fichier_type, annee, actif, ordre
) VALUES (
    'Guide de remplissage du formulaire DAMO 2026',
    'Guide officiel de remplissage du Formulaire de Déclaration Annuelle de la Main d''Œuvre (DAMO). Ce document explique étape par étape comment compléter correctement chaque section du formulaire RAMO.',
    'guide_damo_2026.pdf',
    'uploads/guides/guide_damo_2026.pdf',
    864167,
    'application/pdf',
    2026,
    TRUE,
    1
) ON CONFLICT DO NOTHING;

-- Vérification
SELECT id, titre, annee, fichier_nom, actif FROM guides_documents ORDER BY ordre;
