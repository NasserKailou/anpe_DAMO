-- ============================================================
-- e-DAMO — Migration MySQL complète
-- Converti depuis PostgreSQL backup_damo20260303.sql
-- Encodage : utf8mb4 / utf8mb4_unicode_ci
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';

-- ============================================================
-- TABLE : regions
-- ============================================================
CREATE TABLE IF NOT EXISTS `regions` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(5) NOT NULL,
  `nom` VARCHAR(100) NOT NULL,
  `actif` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE : departements
-- ============================================================
CREATE TABLE IF NOT EXISTS `departements` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `region_id` INT NOT NULL,
  `nom` VARCHAR(100) NOT NULL,
  `actif` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_departements_region_id` (`region_id`),
  CONSTRAINT `fk_departements_region` FOREIGN KEY (`region_id`) REFERENCES `regions` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE : communes
-- ============================================================
CREATE TABLE IF NOT EXISTS `communes` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `departement_id` INT NOT NULL,
  `nom` VARCHAR(100) NOT NULL,
  `actif` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_communes_departement_id` (`departement_id`),
  CONSTRAINT `fk_communes_departement` FOREIGN KEY (`departement_id`) REFERENCES `departements` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE : branches_activite
-- ============================================================
CREATE TABLE IF NOT EXISTS `branches_activite` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(10) NOT NULL,
  `libelle` VARCHAR(200) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `actif` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_branches_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE : utilisateurs
-- ============================================================
CREATE TABLE IF NOT EXISTS `utilisateurs` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `uuid` VARCHAR(36) NOT NULL,
  `nom` VARCHAR(100) NOT NULL,
  `prenom` VARCHAR(100) DEFAULT NULL,
  `email` VARCHAR(255) NOT NULL,
  `telephone` VARCHAR(20) DEFAULT NULL,
  `mot_de_passe` VARCHAR(255) NOT NULL,
  `role` ENUM('super_admin','admin','agent') NOT NULL,
  `region_id` INT DEFAULT NULL,
  `actif` TINYINT(1) NOT NULL DEFAULT 1,
  `email_verifie` TINYINT(1) NOT NULL DEFAULT 0,
  `token_verification` VARCHAR(100) DEFAULT NULL,
  `token_reset` VARCHAR(100) DEFAULT NULL,
  `token_reset_expiry` DATETIME DEFAULT NULL,
  `derniere_connexion` DATETIME DEFAULT NULL,
  `tentatives_connexion` INT NOT NULL DEFAULT 0,
  `bloque_jusqu_a` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_by` INT DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_utilisateurs_uuid` (`uuid`),
  UNIQUE KEY `uq_utilisateurs_email` (`email`),
  KEY `idx_utilisateurs_region_id` (`region_id`),
  CONSTRAINT `fk_utilisateurs_region` FOREIGN KEY (`region_id`) REFERENCES `regions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE : sessions_utilisateurs
-- ============================================================
CREATE TABLE IF NOT EXISTS `sessions_utilisateurs` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `utilisateur_id` INT NOT NULL,
  `session_id` VARCHAR(255) NOT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `user_agent` TEXT DEFAULT NULL,
  `debut` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `fin` DATETIME DEFAULT NULL,
  `actif` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_sessions_utilisateur_id` (`utilisateur_id`),
  CONSTRAINT `fk_sessions_utilisateur` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE : campagnes_damo
-- ============================================================
CREATE TABLE IF NOT EXISTS `campagnes_damo` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `annee` INT NOT NULL,
  `libelle` VARCHAR(100) NOT NULL,
  `date_debut` DATE NOT NULL,
  `date_fin` DATE NOT NULL,
  `actif` TINYINT(1) NOT NULL DEFAULT 1,
  `description` TEXT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_by` INT DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE : entreprises
-- ============================================================
CREATE TABLE IF NOT EXISTS `entreprises` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `uuid` VARCHAR(36) NOT NULL,
  `raison_sociale` VARCHAR(255) NOT NULL,
  `nationalite` VARCHAR(100) DEFAULT NULL,
  `activite_principale` TEXT DEFAULT NULL,
  `activites_secondaires` TEXT DEFAULT NULL,
  `branche_id` INT DEFAULT NULL,
  `region_id` INT NOT NULL,
  `departement_id` INT DEFAULT NULL,
  `commune_id` INT DEFAULT NULL,
  `localite` VARCHAR(200) DEFAULT NULL,
  `quartier` VARCHAR(200) DEFAULT NULL,
  `adresse` TEXT DEFAULT NULL,
  `boite_postale` VARCHAR(50) DEFAULT NULL,
  `telephone` VARCHAR(20) DEFAULT NULL,
  `fax` VARCHAR(20) DEFAULT NULL,
  `email` VARCHAR(255) DEFAULT NULL,
  `numero_cnss` VARCHAR(50) DEFAULT NULL,
  `agent_id` INT DEFAULT NULL,
  `actif` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_by` INT DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_entreprises_uuid` (`uuid`),
  KEY `idx_entreprises_region_id` (`region_id`),
  KEY `idx_entreprises_branche_id` (`branche_id`),
  KEY `idx_entreprises_agent_id` (`agent_id`),
  CONSTRAINT `fk_entreprises_region` FOREIGN KEY (`region_id`) REFERENCES `regions` (`id`),
  CONSTRAINT `fk_entreprises_branche` FOREIGN KEY (`branche_id`) REFERENCES `branches_activite` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_entreprises_agent` FOREIGN KEY (`agent_id`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE : declarations
-- ============================================================
CREATE TABLE IF NOT EXISTS `declarations` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `uuid` VARCHAR(36) NOT NULL,
  `code_questionnaire` VARCHAR(50) DEFAULT NULL,
  `campagne_id` INT NOT NULL,
  `entreprise_id` INT NOT NULL,
  `agent_id` INT NOT NULL,
  `region_id` INT NOT NULL,
  `nom_enqueteur` VARCHAR(200) DEFAULT NULL,
  `statut` ENUM('brouillon','soumise','validee','rejetee','corrigee') NOT NULL DEFAULT 'brouillon',
  `date_soumission` DATETIME DEFAULT NULL,
  `date_validation` DATETIME DEFAULT NULL,
  `date_rejet` DATETIME DEFAULT NULL,
  `motif_rejet` TEXT DEFAULT NULL,
  `observations` TEXT DEFAULT NULL,
  `masse_salariale` DECIMAL(20,2) DEFAULT NULL,
  `validateur_id` INT DEFAULT NULL,
  `ip_saisie` VARCHAR(45) DEFAULT NULL,
  `etape_courante` INT NOT NULL DEFAULT 1,
  `pourcentage_completion` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `submitted_at` DATETIME DEFAULT NULL,
  `total_nigeriens` INT NOT NULL DEFAULT 0,
  `total_etrangers` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_declarations_uuid` (`uuid`),
  KEY `idx_declarations_campagne_id` (`campagne_id`),
  KEY `idx_declarations_entreprise_id` (`entreprise_id`),
  KEY `idx_declarations_agent_id` (`agent_id`),
  KEY `idx_declarations_region_id` (`region_id`),
  KEY `idx_declarations_statut` (`statut`),
  CONSTRAINT `fk_declarations_campagne` FOREIGN KEY (`campagne_id`) REFERENCES `campagnes_damo` (`id`),
  CONSTRAINT `fk_declarations_entreprise` FOREIGN KEY (`entreprise_id`) REFERENCES `entreprises` (`id`),
  CONSTRAINT `fk_declarations_agent` FOREIGN KEY (`agent_id`) REFERENCES `utilisateurs` (`id`),
  CONSTRAINT `fk_declarations_region` FOREIGN KEY (`region_id`) REFERENCES `regions` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE : declaration_effectifs_mensuels
-- ============================================================
CREATE TABLE IF NOT EXISTS `declaration_effectifs_mensuels` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `declaration_id` INT NOT NULL,
  `mois` INT NOT NULL,
  `effectif` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_effectifs_mensuels_declaration_id` (`declaration_id`),
  CONSTRAINT `fk_eff_mensuels_declaration` FOREIGN KEY (`declaration_id`) REFERENCES `declarations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chk_eff_mensuels_mois` CHECK (`mois` >= 1 AND `mois` <= 12),
  CONSTRAINT `chk_eff_mensuels_effectif` CHECK (`effectif` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE : declaration_categories_effectifs
-- ============================================================
CREATE TABLE IF NOT EXISTS `declaration_categories_effectifs` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `declaration_id` INT NOT NULL,
  `categorie` VARCHAR(50) NOT NULL,
  `nigeriens_h` INT NOT NULL DEFAULT 0,
  `nigeriens_f` INT NOT NULL DEFAULT 0,
  `africains_h` INT NOT NULL DEFAULT 0,
  `africains_f` INT NOT NULL DEFAULT 0,
  `autres_nat_h` INT NOT NULL DEFAULT 0,
  `autres_nat_f` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_cat_effectifs_declaration_id` (`declaration_id`),
  CONSTRAINT `fk_cat_effectifs_declaration` FOREIGN KEY (`declaration_id`) REFERENCES `declarations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE : declaration_niveaux_instruction
-- ============================================================
CREATE TABLE IF NOT EXISTS `declaration_niveaux_instruction` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `declaration_id` INT NOT NULL,
  `categorie` VARCHAR(50) NOT NULL,
  `niveau` VARCHAR(50) NOT NULL,
  `effectif_h` INT NOT NULL DEFAULT 0,
  `effectif_f` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_niveaux_instruction_declaration_id` (`declaration_id`),
  CONSTRAINT `fk_niveaux_instruction_declaration` FOREIGN KEY (`declaration_id`) REFERENCES `declarations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE : declaration_formations
-- ============================================================
CREATE TABLE IF NOT EXISTS `declaration_formations` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `declaration_id` INT NOT NULL,
  `a_eu_formation` TINYINT(1) NOT NULL DEFAULT 0,
  `qualification` VARCHAR(100) DEFAULT NULL,
  `nature_formation` TEXT DEFAULT NULL,
  `duree_formation` VARCHAR(100) DEFAULT NULL,
  `effectif_h` INT NOT NULL DEFAULT 0,
  `effectif_f` INT NOT NULL DEFAULT 0,
  `observations` TEXT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ligne_ordre` INT NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_formations_declaration_id` (`declaration_id`),
  CONSTRAINT `fk_formations_declaration` FOREIGN KEY (`declaration_id`) REFERENCES `declarations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE : declaration_pertes_emploi
-- ============================================================
CREATE TABLE IF NOT EXISTS `declaration_pertes_emploi` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `declaration_id` INT NOT NULL,
  `motif` ENUM('licenciement','demission','fin_contrat','retraite','deces','autres') NOT NULL,
  `motif_autre` VARCHAR(200) DEFAULT NULL,
  `effectif_h` INT NOT NULL DEFAULT 0,
  `effectif_f` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_pertes_emploi_declaration_id` (`declaration_id`),
  CONSTRAINT `fk_pertes_emploi_declaration` FOREIGN KEY (`declaration_id`) REFERENCES `declarations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE : declaration_perspectives
-- ============================================================
CREATE TABLE IF NOT EXISTS `declaration_perspectives` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `declaration_id` INT NOT NULL,
  `perspective` ENUM('hausse','stabilite','baisse') DEFAULT NULL,
  `justification` TEXT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_perspectives_declaration_id` (`declaration_id`),
  CONSTRAINT `fk_perspectives_declaration` FOREIGN KEY (`declaration_id`) REFERENCES `declarations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE : declaration_effectifs_etrangers
-- ============================================================
CREATE TABLE IF NOT EXISTS `declaration_effectifs_etrangers` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `declaration_id` INT NOT NULL,
  `pays` VARCHAR(100) NOT NULL,
  `qualification` VARCHAR(100) DEFAULT NULL,
  `fonction` VARCHAR(200) DEFAULT NULL,
  `sexe` CHAR(1) DEFAULT NULL,
  `nombre` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_eff_etrangers_declaration_id` (`declaration_id`),
  CONSTRAINT `fk_eff_etrangers_declaration` FOREIGN KEY (`declaration_id`) REFERENCES `declarations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE : historique_declarations
-- ============================================================
CREATE TABLE IF NOT EXISTS `historique_declarations` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `declaration_id` INT NOT NULL,
  `utilisateur_id` INT DEFAULT NULL,
  `action` VARCHAR(50) NOT NULL,
  `ancien_statut` VARCHAR(20) DEFAULT NULL,
  `nouveau_statut` VARCHAR(20) DEFAULT NULL,
  `details` JSON DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_historique_declaration_id` (`declaration_id`),
  CONSTRAINT `fk_historique_declaration` FOREIGN KEY (`declaration_id`) REFERENCES `declarations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE : notifications
-- ============================================================
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `utilisateur_id` INT NOT NULL,
  `titre` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `type` ENUM('info','success','warning','error') NOT NULL DEFAULT 'info',
  `lu` TINYINT(1) NOT NULL DEFAULT 0,
  `lu_at` DATETIME DEFAULT NULL,
  `lien` VARCHAR(500) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_notifications_utilisateur_id` (`utilisateur_id`),
  CONSTRAINT `fk_notifications_utilisateur` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE : parametres
-- ============================================================
CREATE TABLE IF NOT EXISTS `parametres` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `cle` VARCHAR(100) NOT NULL,
  `valeur` TEXT DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `type` ENUM('string','integer','boolean','json') NOT NULL DEFAULT 'string',
  `modifiable` TINYINT(1) NOT NULL DEFAULT 1,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` INT DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_parametres_cle` (`cle`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE : guides_documents
-- ============================================================
CREATE TABLE IF NOT EXISTS `guides_documents` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `titre` VARCHAR(255) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `fichier_nom` VARCHAR(255) NOT NULL,
  `fichier_path` VARCHAR(500) NOT NULL,
  `fichier_taille` INT DEFAULT NULL,
  `fichier_type` VARCHAR(100) DEFAULT NULL,
  `annee` INT DEFAULT NULL,
  `actif` TINYINT(1) NOT NULL DEFAULT 1,
  `ordre` INT NOT NULL DEFAULT 0,
  `telechargements` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_by` INT DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE : logs_activite
-- ============================================================
CREATE TABLE IF NOT EXISTS `logs_activite` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `utilisateur_id` INT DEFAULT NULL,
  `action` VARCHAR(100) NOT NULL,
  `ressource` VARCHAR(100) DEFAULT NULL,
  `ressource_id` INT DEFAULT NULL,
  `details` JSON DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `user_agent` TEXT DEFAULT NULL,
  `statut` ENUM('success','failure','warning') NOT NULL DEFAULT 'success',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_logs_utilisateur_id` (`utilisateur_id`),
  KEY `idx_logs_action` (`action`),
  KEY `idx_logs_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- DONNÉES INITIALES
-- ============================================================

-- Régions
INSERT IGNORE INTO `regions` (`id`, `code`, `nom`, `actif`, `created_at`, `updated_at`) VALUES
(1, '1', 'Agadez', 1, '2026-03-02 16:50:28', '2026-03-02 16:50:28'),
(2, '2', 'Diffa', 1, '2026-03-02 16:50:28', '2026-03-02 16:50:28'),
(3, '3', 'Dosso', 1, '2026-03-02 16:50:28', '2026-03-02 16:50:28'),
(4, '4', 'Maradi', 1, '2026-03-02 16:50:28', '2026-03-02 16:50:28'),
(5, '5', 'Tahoua', 1, '2026-03-02 16:50:28', '2026-03-02 16:50:28'),
(6, '6', 'Tillabéri', 1, '2026-03-02 16:50:28', '2026-03-02 16:50:28'),
(7, '7', 'Zinder', 1, '2026-03-02 16:50:28', '2026-03-02 16:50:28'),
(8, '8', 'Niamey', 1, '2026-03-02 16:50:28', '2026-03-02 16:50:28'),
(9, '11', 'Arlit', 1, '2026-03-02 16:50:28', '2026-03-02 16:50:28'),
(10, '51', 'Konni', 1, '2026-03-02 16:50:28', '2026-03-02 16:50:28');

-- Départements
INSERT IGNORE INTO `departements` (`id`, `region_id`, `nom`, `actif`, `created_at`, `updated_at`) VALUES
(1, 1, 'Agadez', 1, '2026-03-02 16:50:28', '2026-03-02 16:50:28'),
(2, 1, 'Arlit', 1, '2026-03-02 16:50:28', '2026-03-02 16:50:28'),
(3, 1, 'Bilma', 1, '2026-03-02 16:50:28', '2026-03-02 16:50:28'),
(4, 1, 'Tchirozerine', 1, '2026-03-02 16:50:28', '2026-03-02 16:50:28'),
(5, 2, 'Diffa', 1, '2026-03-02 16:50:28', '2026-03-02 16:50:28'),
(6, 2, 'Maine-Soroa', 1, '2026-03-02 16:50:28', '2026-03-02 16:50:28'),
(7, 2, 'Nguigmi', 1, '2026-03-02 16:50:28', '2026-03-02 16:50:28'),
(8, 3, 'Boboye', 1, '2026-03-02 16:50:28', '2026-03-02 16:50:28'),
(9, 3, 'Dosso', 1, '2026-03-02 16:50:28', '2026-03-02 16:50:28'),
(10, 3, 'Gaya', 1, '2026-03-02 16:50:28', '2026-03-02 16:50:28'),
(11, 3, 'Loga', 1, '2026-03-02 16:50:28', '2026-03-02 16:50:28'),
(12, 3, 'Doutchi', 1, '2026-03-02 16:50:28', '2026-03-02 16:50:28'),
(13, 4, 'Aguié', 1, '2026-03-02 16:50:28', '2026-03-02 16:50:28'),
(14, 4, 'Dakoro', 1, '2026-03-02 16:50:28', '2026-03-02 16:50:28'),
(15, 4, 'Guidan-Roumdji', 1, '2026-03-02 16:50:28', '2026-03-02 16:50:28'),
(16, 4, 'Madarounfa', 1, '2026-03-02 16:50:28', '2026-03-02 16:50:28'),
(17, 4, 'Maradi', 1, '2026-03-02 16:50:28', '2026-03-02 16:50:28'),
(18, 4, 'Mayahi', 1, '2026-03-02 16:50:28', '2026-03-02 16:50:28'),
(19, 4, 'Tessaoua', 1, '2026-03-02 16:50:28', '2026-03-02 16:50:28'),
(20, 5, 'Abalak', 1, '2026-03-02 16:50:28', '2026-03-02 16:50:28'),
(21, 5, 'Birni-N\'Konni', 1, '2026-03-02 16:50:28', '2026-03-02 16:50:28'),
(22, 5, 'Bouza', 1, '2026-03-02 16:50:28', '2026-03-02 16:50:28'),
(23, 5, 'Illéla', 1, '2026-03-02 16:50:28', '2026-03-02 16:50:28'),
(24, 5, 'Keïta', 1, '2026-03-02 16:50:28', '2026-03-02 16:50:28'),
(25, 5, 'Madaoua', 1, '2026-03-02 16:50:28', '2026-03-02 16:50:28'),
(26, 5, 'Tahoua', 1, '2026-03-02 16:50:28', '2026-03-02 16:50:28'),
(27, 5, 'Tchintabaraden', 1, '2026-03-02 16:50:28', '2026-03-02 16:50:28'),
(28, 6, 'Filingué', 1, '2026-03-02 16:50:28', '2026-03-02 16:50:28'),
(29, 6, 'Kollo', 1, '2026-03-02 16:50:28', '2026-03-02 16:50:28'),
(30, 6, 'Ouallam', 1, '2026-03-02 16:50:28', '2026-03-02 16:50:28'),
(31, 6, 'Say', 1, '2026-03-02 16:50:28', '2026-03-02 16:50:28'),
(32, 6, 'Tera', 1, '2026-03-02 16:50:28', '2026-03-02 16:50:28'),
(33, 6, 'Tillabéri', 1, '2026-03-02 16:50:28', '2026-03-02 16:50:28'),
(34, 6, 'Torodi', 1, '2026-03-02 16:50:28', '2026-03-02 16:50:28'),
(35, 7, 'Gouré', 1, '2026-03-02 16:50:28', '2026-03-02 16:50:28'),
(36, 7, 'Magaria', 1, '2026-03-02 16:50:28', '2026-03-02 16:50:28'),
(37, 7, 'Matameye', 1, '2026-03-02 16:50:28', '2026-03-02 16:50:28'),
(38, 7, 'Mirriah', 1, '2026-03-02 16:50:28', '2026-03-02 16:50:28'),
(39, 7, 'Tanout', 1, '2026-03-02 16:50:28', '2026-03-02 16:50:28'),
(40, 7, 'Zinder', 1, '2026-03-02 16:50:28', '2026-03-02 16:50:28'),
(41, 8, 'Niamey I', 1, '2026-03-02 16:50:28', '2026-03-02 16:50:28'),
(42, 8, 'Niamey II', 1, '2026-03-02 16:50:28', '2026-03-02 16:50:28'),
(43, 8, 'Niamey III', 1, '2026-03-02 16:50:28', '2026-03-02 16:50:28'),
(44, 8, 'Niamey IV', 1, '2026-03-02 16:50:28', '2026-03-02 16:50:28'),
(45, 8, 'Niamey V', 1, '2026-03-02 16:50:28', '2026-03-02 16:50:28');

-- Branches d'activité
INSERT IGNORE INTO `branches_activite` (`id`, `code`, `libelle`, `description`, `actif`, `created_at`) VALUES
(1, 'B1', 'Agriculture, Élevage, Chasse et Sylviculture', 'Activités agricoles et d\'élevage', 1, '2026-03-02 16:50:28'),
(2, 'B2', 'Pêche', 'Activités de pêche et pisciculture', 1, '2026-03-02 16:50:28'),
(3, 'B3', 'Industries extractives et mines', 'Extraction minière, pétrole, gaz', 1, '2026-03-02 16:50:28'),
(4, 'B4', 'Industries manufacturières', 'Transformation industrielle', 1, '2026-03-02 16:50:28'),
(5, 'B5', 'Electricité, Gaz et Eau', 'Production et distribution d\'énergie et d\'eau', 1, '2026-03-02 16:50:28'),
(6, 'B6', 'Construction et BTP', 'Travaux de construction et bâtiment', 1, '2026-03-02 16:50:28'),
(7, 'B7', 'Commerce, Restauration et Hôtellerie', 'Commerce et tourisme', 1, '2026-03-02 16:50:28'),
(8, 'B8', 'Transport, Entreposage et Communications', 'Logistique et télécommunications', 1, '2026-03-02 16:50:28'),
(9, 'B9', 'Services', 'Services financiers, éducation, santé, administration', 1, '2026-03-02 16:50:28');

-- Utilisateurs (mots de passe : Admin@2025 pour tous sauf super_admin)
INSERT IGNORE INTO `utilisateurs` (`id`, `uuid`, `nom`, `prenom`, `email`, `telephone`, `mot_de_passe`, `role`, `region_id`, `actif`, `email_verifie`, `created_at`, `updated_at`) VALUES
(3, '0a49400c-de08-4e5f-8c67-0438e4185cea', 'TRAORE', 'Moussa', 'super@anpe-niger.ne', NULL, '$2y$12$1VIsVrvXwxtX4wcoy04ITuXWO1aIofWEJ3BNOqR4SGEaF1HIqw5fe', 'super_admin', NULL, 1, 1, '2026-03-02 17:58:09', '2026-03-02 18:27:31'),
(4, '90d26e20-ff8d-4096-8e9f-73a347b0fd3a', 'MAHAMADOU', 'Ibrahim', 'admin@anpe-niger.ne', NULL, '$2y$12$.hHy/6URdxjLix5OIe9nYO/jA2InGO6OsXIbWDwnGa2DoBMD0Tg2C', 'admin', NULL, 1, 1, '2026-03-02 17:58:09', '2026-03-03 09:28:51'),
(5, '373ccfac-01ae-4b12-88cf-60d06df01a8c', 'ISSAKA', 'Fati', 'agent@anpe-niger.ne', '', '$2y$12$1VIsVrvXwxtX4wcoy04ITuXWO1aIofWEJ3BNOqR4SGEaF1HIqw5fe', 'agent', 4, 1, 1, '2026-03-02 17:58:09', '2026-03-02 19:04:45');

-- Campagne
INSERT IGNORE INTO `campagnes_damo` (`id`, `annee`, `libelle`, `date_debut`, `date_fin`, `actif`, `description`, `created_at`, `updated_at`) VALUES
(1, 2025, 'Déclaration Annuelle 2025', '2025-01-01', '2025-03-31', 1, 'Campagne de collecte de la Déclaration Annuelle de la Main d\'Œuvre pour l\'année 2025', '2026-03-02 16:50:28', '2026-03-02 16:50:28');

-- Entreprises
INSERT IGNORE INTO `entreprises` (`id`, `uuid`, `raison_sociale`, `nationalite`, `activite_principale`, `branche_id`, `region_id`, `departement_id`, `agent_id`, `actif`, `created_at`, `updated_at`, `created_by`) VALUES
(1, 'fe8738e8-0df7-4933-a3df-5f980fdbb83b', 'ZFSFSFSFS', 'Nigérienne', '', 1, 4, 13, 5, 1, '2026-03-02 19:22:57', '2026-03-02 19:23:33', 5);

-- Déclarations
INSERT IGNORE INTO `declarations` (`id`, `uuid`, `code_questionnaire`, `campagne_id`, `entreprise_id`, `agent_id`, `region_id`, `nom_enqueteur`, `statut`, `ip_saisie`, `etape_courante`, `pourcentage_completion`, `created_at`, `updated_at`, `total_nigeriens`, `total_etrangers`) VALUES
(1, '879000fa-5926-4ffd-bdd4-31d5b915ecc3', '4/01/001', 1, 1, 5, 4, 'Fati ISSAKA', 'brouillon', '127.0.0.1', 1, 0, '2026-03-02 19:24:02', '2026-03-02 19:24:02', 0, 0);

-- Déclaration : effectifs mensuels
INSERT IGNORE INTO `declaration_effectifs_mensuels` (`id`, `declaration_id`, `mois`, `effectif`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 0, '2026-03-02 19:24:02', '2026-03-02 19:24:02'),
(2, 1, 2, 0, '2026-03-02 19:24:02', '2026-03-02 19:24:02'),
(3, 1, 3, 0, '2026-03-02 19:24:02', '2026-03-02 19:24:02'),
(4, 1, 4, 0, '2026-03-02 19:24:02', '2026-03-02 19:24:02'),
(5, 1, 5, 0, '2026-03-02 19:24:02', '2026-03-02 19:24:02'),
(6, 1, 6, 0, '2026-03-02 19:24:02', '2026-03-02 19:24:02'),
(7, 1, 7, 0, '2026-03-02 19:24:02', '2026-03-02 19:24:02'),
(8, 1, 8, 0, '2026-03-02 19:24:02', '2026-03-02 19:24:02'),
(9, 1, 9, 0, '2026-03-02 19:24:02', '2026-03-02 19:24:02'),
(10, 1, 10, 0, '2026-03-02 19:24:02', '2026-03-02 19:24:02'),
(11, 1, 11, 0, '2026-03-02 19:24:02', '2026-03-02 19:24:02'),
(12, 1, 12, 0, '2026-03-02 19:24:02', '2026-03-02 19:24:02');

-- Déclaration : catégories effectifs
INSERT IGNORE INTO `declaration_categories_effectifs` (`id`, `declaration_id`, `categorie`, `nigeriens_h`, `nigeriens_f`, `africains_h`, `africains_f`, `autres_nat_h`, `autres_nat_f`, `created_at`, `updated_at`) VALUES
(1, 1, 'cadres_superieurs', 0, 0, 0, 0, 0, 0, '2026-03-02 19:24:02', '2026-03-02 19:24:02'),
(2, 1, 'agents_maitrise', 0, 0, 0, 0, 0, 0, '2026-03-02 19:24:02', '2026-03-02 19:24:02'),
(3, 1, 'employes_bureau', 0, 0, 0, 0, 0, 0, '2026-03-02 19:24:02', '2026-03-02 19:24:02'),
(4, 1, 'ouvriers_qualifies', 0, 0, 0, 0, 0, 0, '2026-03-02 19:24:02', '2026-03-02 19:24:02'),
(5, 1, 'ouvriers_specialises', 0, 0, 0, 0, 0, 0, '2026-03-02 19:24:02', '2026-03-02 19:24:02'),
(6, 1, 'manœuvres', 0, 0, 0, 0, 0, 0, '2026-03-02 19:24:02', '2026-03-02 19:24:02'),
(7, 1, 'apprentis_stagiaires', 0, 0, 0, 0, 0, 0, '2026-03-02 19:24:02', '2026-03-02 19:24:02');

-- Déclaration : formations
INSERT IGNORE INTO `declaration_formations` (`id`, `declaration_id`, `a_eu_formation`, `effectif_h`, `effectif_f`, `ligne_ordre`, `created_at`, `updated_at`) VALUES
(1, 1, 0, 0, 0, 1, '2026-03-02 19:24:02', '2026-03-02 19:24:02');

-- Déclaration : pertes emploi
INSERT IGNORE INTO `declaration_pertes_emploi` (`id`, `declaration_id`, `motif`, `effectif_h`, `effectif_f`, `created_at`, `updated_at`) VALUES
(1, 1, 'licenciement', 0, 0, '2026-03-02 19:24:02', '2026-03-02 19:24:02'),
(2, 1, 'demission', 0, 0, '2026-03-02 19:24:02', '2026-03-02 19:24:02'),
(3, 1, 'fin_contrat', 0, 0, '2026-03-02 19:24:02', '2026-03-02 19:24:02'),
(4, 1, 'retraite', 0, 0, '2026-03-02 19:24:02', '2026-03-02 19:24:02'),
(5, 1, 'deces', 0, 0, '2026-03-02 19:24:02', '2026-03-02 19:24:02'),
(6, 1, 'autres', 0, 0, '2026-03-02 19:24:02', '2026-03-02 19:24:02');

-- Déclaration : perspectives
INSERT IGNORE INTO `declaration_perspectives` (`id`, `declaration_id`, `created_at`, `updated_at`) VALUES
(1, 1, '2026-03-02 19:24:02', '2026-03-02 19:24:02');

-- Paramètres
INSERT IGNORE INTO `parametres` (`id`, `cle`, `valeur`, `description`, `type`, `modifiable`, `updated_at`) VALUES
(1, 'app_nom', 'e-DAMO', 'Nom de l\'application', 'string', 1, '2026-03-02 16:50:28'),
(2, 'app_slogan', 'Déclaration Annuelle de la Main d\'Œuvre en ligne', 'Slogan de l\'application', 'string', 1, '2026-03-02 16:50:28'),
(3, 'annee_courante', '2025', 'Année de déclaration courante', 'integer', 1, '2026-03-02 16:50:28'),
(4, 'deadline_declaration', '2025-03-31', 'Date limite de déclaration', 'string', 1, '2026-03-02 16:50:28'),
(5, 'email_contact', 'anpe-niger16@gmail.com', 'Email de contact', 'string', 1, '2026-03-02 16:50:28'),
(6, 'tel_contact', '20 73 33 84', 'Téléphone de contact', 'string', 1, '2026-03-02 16:50:28'),
(7, 'site_web', 'https://www.anpe-niger.ne', 'Site web officiel', 'string', 1, '2026-03-02 16:50:28'),
(8, 'adresse', 'BP 13 222 NIAMEY – NIGER', 'Adresse postale', 'string', 1, '2026-03-02 16:50:28'),
(9, 'maintenance_mode', 'false', 'Mode maintenance activé', 'boolean', 1, '2026-03-02 16:50:28'),
(10, 'inscriptions_ouvertes', 'true', 'Inscriptions ouvertes', 'boolean', 1, '2026-03-02 16:50:28'),
(11, 'items_par_page', '20', 'Nombre d\'éléments par page', 'integer', 1, '2026-03-02 16:50:28'),
(12, 'logo_path', '/assets/img/logo-anpe.png', 'Chemin du logo', 'string', 1, '2026-03-02 16:50:28');

-- Guide
INSERT IGNORE INTO `guides_documents` (`id`, `titre`, `description`, `fichier_nom`, `fichier_path`, `fichier_taille`, `fichier_type`, `annee`, `actif`, `ordre`, `telechargements`, `created_at`, `updated_at`) VALUES
(1, 'Guide de remplissage du formulaire DAMO 2026', 'Guide officiel de remplissage du Formulaire de Déclaration Annuelle de la Main d\'Œuvre (DAMO). Ce document explique étape par étape comment compléter correctement chaque section du formulaire RAMO.', 'guide_damo_2026.pdf', 'uploads/guides/guide_damo_2026.pdf', 864167, 'application/pdf', 2026, 1, 1, 2, '2026-03-02 17:49:47', '2026-03-02 17:49:47');
