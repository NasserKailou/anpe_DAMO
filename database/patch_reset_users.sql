-- ============================================================
-- PATCH : Réinitialisation des 3 comptes de test e-DAMO
-- Compatible MySQL / MariaDB
-- ============================================================
-- Exécution :
--   mysql -u root -p edamo < patch_reset_users.sql
-- OU via Plesk phpMyAdmin : copier-coller et exécuter
-- ============================================================
--
-- COMPTES CRÉÉS / RÉINITIALISÉS :
-- ┌────────────────────────────┬──────────────────────┬──────────────────┐
-- │ Email                      │ Mot de passe         │ Rôle             │
-- ├────────────────────────────┼──────────────────────┼──────────────────┤
-- │ super@anpe-niger.ne        │ SuperAdmin@2025      │ super_admin      │
-- │ admin@anpe-niger.ne        │ Admin@2025           │ admin            │
-- │ agent@anpe-niger.ne        │ Agent@2025           │ agent            │
-- └────────────────────────────┴──────────────────────┴──────────────────┘
--
-- Les hash sont générés avec bcrypt cost=12 (compatibles PHP password_verify)
-- ============================================================

SET NAMES utf8mb4;

-- ── 1. Supprimer les sessions bloquantes ───────────────────────────────
UPDATE `utilisateurs`
SET
    `tentatives_connexion` = 0,
    `bloque_jusqu_a`       = NULL
WHERE `email` IN (
    'super@anpe-niger.ne',
    'admin@anpe-niger.ne',
    'agent@anpe-niger.ne'
);

-- ── 2. SUPER ADMIN ─────────────────────────────────────────────────────
-- Email : super@anpe-niger.ne | Mot de passe : SuperAdmin@2025
INSERT INTO `utilisateurs`
    (`id`, `uuid`, `nom`, `prenom`, `email`, `telephone`,
     `mot_de_passe`, `role`, `region_id`, `actif`, `email_verifie`,
     `tentatives_connexion`, `bloque_jusqu_a`,
     `created_at`, `updated_at`)
VALUES
    (3,
     COALESCE((SELECT uuid FROM (SELECT uuid FROM utilisateurs WHERE id=3) AS t), UUID()),
     'TRAORE', 'Moussa',
     'super@anpe-niger.ne',
     NULL,
     '$2b$12$kMkLGC/QazfzYL7afFwfbejxr6b6YRmKwtuS4bMetUA/4ET1eaqiq',
     'super_admin',
     NULL,
     1, 1, 0, NULL,
     NOW(), NOW()
    )
ON DUPLICATE KEY UPDATE
    `nom`                  = 'TRAORE',
    `prenom`               = 'Moussa',
    `email`                = 'super@anpe-niger.ne',
    `mot_de_passe`         = '$2b$12$kMkLGC/QazfzYL7afFwfbejxr6b6YRmKwtuS4bMetUA/4ET1eaqiq',
    `role`                 = 'super_admin',
    `region_id`            = NULL,
    `actif`                = 1,
    `email_verifie`        = 1,
    `tentatives_connexion` = 0,
    `bloque_jusqu_a`       = NULL,
    `updated_at`           = NOW();

-- ── 3. ADMIN ───────────────────────────────────────────────────────────
-- Email : admin@anpe-niger.ne | Mot de passe : Admin@2025
INSERT INTO `utilisateurs`
    (`id`, `uuid`, `nom`, `prenom`, `email`, `telephone`,
     `mot_de_passe`, `role`, `region_id`, `actif`, `email_verifie`,
     `tentatives_connexion`, `bloque_jusqu_a`,
     `created_at`, `updated_at`)
VALUES
    (4,
     COALESCE((SELECT uuid FROM (SELECT uuid FROM utilisateurs WHERE id=4) AS t), UUID()),
     'MAHAMADOU', 'Ibrahim',
     'admin@anpe-niger.ne',
     NULL,
     '$2b$12$ukNY9lCITjPBi1MUPEQc2.ZliLmLYCZWk0mXWJLNrkWNX8NSvfTuK',
     'admin',
     NULL,
     1, 1, 0, NULL,
     NOW(), NOW()
    )
ON DUPLICATE KEY UPDATE
    `nom`                  = 'MAHAMADOU',
    `prenom`               = 'Ibrahim',
    `email`                = 'admin@anpe-niger.ne',
    `mot_de_passe`         = '$2b$12$ukNY9lCITjPBi1MUPEQc2.ZliLmLYCZWk0mXWJLNrkWNX8NSvfTuK',
    `role`                 = 'admin',
    `region_id`            = NULL,
    `actif`                = 1,
    `email_verifie`        = 1,
    `tentatives_connexion` = 0,
    `bloque_jusqu_a`       = NULL,
    `updated_at`           = NOW();

-- ── 4. AGENT ───────────────────────────────────────────────────────────
-- Email : agent@anpe-niger.ne | Mot de passe : Agent@2025
-- region_id = 4 (Maradi - modifiable selon votre config)
INSERT INTO `utilisateurs`
    (`id`, `uuid`, `nom`, `prenom`, `email`, `telephone`,
     `mot_de_passe`, `role`, `region_id`, `actif`, `email_verifie`,
     `tentatives_connexion`, `bloque_jusqu_a`,
     `created_at`, `updated_at`)
VALUES
    (5,
     COALESCE((SELECT uuid FROM (SELECT uuid FROM utilisateurs WHERE id=5) AS t), UUID()),
     'ISSAKA', 'Fati',
     'agent@anpe-niger.ne',
     NULL,
     '$2b$12$ykN5ZhnaI5x60huWjHFWreIz5eaCENiE2zunXD8Eax7gUxZEKJkY.',
     'agent',
     (SELECT id FROM regions WHERE id = 4 LIMIT 1),
     1, 1, 0, NULL,
     NOW(), NOW()
    )
ON DUPLICATE KEY UPDATE
    `nom`                  = 'ISSAKA',
    `prenom`               = 'Fati',
    `email`                = 'agent@anpe-niger.ne',
    `mot_de_passe`         = '$2b$12$ykN5ZhnaI5x60huWjHFWreIz5eaCENiE2zunXD8Eax7gUxZEKJkY.',
    `role`                 = 'agent',
    `region_id`            = (SELECT id FROM regions WHERE id = 4 LIMIT 1),
    `actif`                = 1,
    `email_verifie`        = 1,
    `tentatives_connexion` = 0,
    `bloque_jusqu_a`       = NULL,
    `updated_at`           = NOW();

-- ── 5. Vérification ────────────────────────────────────────────────────
SELECT
    id,
    nom,
    prenom,
    email,
    role,
    actif,
    email_verifie,
    tentatives_connexion,
    bloque_jusqu_a,
    region_id
FROM `utilisateurs`
WHERE email IN (
    'super@anpe-niger.ne',
    'admin@anpe-niger.ne',
    'agent@anpe-niger.ne'
)
ORDER BY id;
