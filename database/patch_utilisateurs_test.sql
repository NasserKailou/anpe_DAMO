-- =============================================================
-- PATCH UTILISATEURS TEST — e-DAMO ANPE Niger
-- Genere le : 2026-03-02 16:55:07
--
-- UTILISATION :
--   psql -U edamo_user -d edamo_db -f patch_utilisateurs_test.sql
--   Ou via phpPgAdmin : copier-coller dans l'onglet SQL
--
-- COMPTES CREES :
--   Email                   | Mot de passe     | Role
--   -----------------------------------------------------------------
--   super@anpe-niger.ne     | Admin@ANPE2025!  | super_admin
--   admin@anpe-niger.ne     | Admin@ANPE2025!  | admin
--   agent@anpe-niger.ne     | Agent@ANPE2025!  | agent
-- =============================================================

-- 1. Super Administrateur
INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role, actif, email_verifie, tentatives_connexion)
VALUES (
    'TRAORE', 'Moussa', 'super@anpe-niger.ne',
    '$2y$12$1VIsVrvXwxtX4wcoy04ITuXWO1aIofWEJ3BNOqR4SGEaF1HIqw5fe',
    'super_admin', TRUE, TRUE, 0
)
ON CONFLICT (email) DO UPDATE SET
    mot_de_passe = EXCLUDED.mot_de_passe,
    actif = TRUE, email_verifie = TRUE,
    tentatives_connexion = 0, bloque_jusqu_a = NULL;

-- 2. Administrateur
INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role, actif, email_verifie, tentatives_connexion)
VALUES (
    'MAHAMADOU', 'Ibrahim', 'admin@anpe-niger.ne',
    '$2y$12$.hHy/6URdxjLix5OIe9nYO/jA2InGO6OsXIbWDwnGa2DoBMD0Tg2C',
    'admin', TRUE, TRUE, 0
)
ON CONFLICT (email) DO UPDATE SET
    mot_de_passe = EXCLUDED.mot_de_passe,
    actif = TRUE, email_verifie = TRUE,
    tentatives_connexion = 0, bloque_jusqu_a = NULL;

-- 3. Agent de saisie
INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role, actif, email_verifie, tentatives_connexion)
VALUES (
    'ISSAKA', 'Fati', 'agent@anpe-niger.ne',
    '$2y$12$vZlPYhObqmpS.EiSRzAkLu4xN37j.u4DQIAuzlt1rIzs4NYLOOtXS',
    'agent', TRUE, TRUE, 0
)
ON CONFLICT (email) DO UPDATE SET
    mot_de_passe = EXCLUDED.mot_de_passe,
    actif = TRUE, email_verifie = TRUE,
    tentatives_connexion = 0, bloque_jusqu_a = NULL;

-- REINITIALISATION du compte admin par defaut (admin@anpe-niger.ne)
-- (au cas ou il existe deja avec un mauvais hash)
UPDATE utilisateurs
SET mot_de_passe = '$2y$12$1VIsVrvXwxtX4wcoy04ITuXWO1aIofWEJ3BNOqR4SGEaF1HIqw5fe',
    actif = TRUE, email_verifie = TRUE,
    tentatives_connexion = 0, bloque_jusqu_a = NULL
WHERE email = 'admin@anpe-niger.ne' AND role = 'super_admin';

-- VERIFICATION : liste de tous les utilisateurs
SELECT id,
       CONCAT(prenom, ' ', nom) AS nom_complet,
       email, role, actif, email_verifie
FROM utilisateurs
ORDER BY CASE role
    WHEN 'super_admin' THEN 1
    WHEN 'admin'       THEN 2
    WHEN 'agent'       THEN 3
    ELSE 4 END;
