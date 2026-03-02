-- ============================================================
-- e-DAMO - Base de données PostgreSQL 15
-- ANPE Niger - Déclaration Annuelle de la Main d'Œuvre
-- Migration 001 : Schéma complet
-- ============================================================

-- Extension pour UUID
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pgcrypto";

-- ============================================================
-- TABLE : regions
-- ============================================================
CREATE TABLE IF NOT EXISTS regions (
    id          SERIAL PRIMARY KEY,
    code        VARCHAR(5) NOT NULL UNIQUE,
    nom         VARCHAR(100) NOT NULL,
    actif       BOOLEAN DEFAULT TRUE,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- TABLE : departements
-- ============================================================
CREATE TABLE IF NOT EXISTS departements (
    id          SERIAL PRIMARY KEY,
    region_id   INTEGER NOT NULL REFERENCES regions(id) ON DELETE CASCADE,
    nom         VARCHAR(100) NOT NULL,
    actif       BOOLEAN DEFAULT TRUE,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- TABLE : communes
-- ============================================================
CREATE TABLE IF NOT EXISTS communes (
    id              SERIAL PRIMARY KEY,
    departement_id  INTEGER NOT NULL REFERENCES departements(id) ON DELETE CASCADE,
    nom             VARCHAR(100) NOT NULL,
    actif           BOOLEAN DEFAULT TRUE,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- TABLE : branches_activite
-- ============================================================
CREATE TABLE IF NOT EXISTS branches_activite (
    id          SERIAL PRIMARY KEY,
    code        VARCHAR(10) NOT NULL UNIQUE,
    libelle     VARCHAR(200) NOT NULL,
    description TEXT,
    actif       BOOLEAN DEFAULT TRUE,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- TABLE : utilisateurs
-- ============================================================
CREATE TABLE IF NOT EXISTS utilisateurs (
    id                  SERIAL PRIMARY KEY,
    uuid                UUID DEFAULT uuid_generate_v4() UNIQUE NOT NULL,
    nom                 VARCHAR(100) NOT NULL,
    prenom              VARCHAR(100),
    email               VARCHAR(255) NOT NULL UNIQUE,
    telephone           VARCHAR(20),
    mot_de_passe        VARCHAR(255) NOT NULL,
    role                VARCHAR(20) NOT NULL CHECK (role IN ('super_admin','admin','agent')),
    region_id           INTEGER REFERENCES regions(id) ON DELETE SET NULL,
    actif               BOOLEAN DEFAULT TRUE,
    email_verifie       BOOLEAN DEFAULT FALSE,
    token_verification  VARCHAR(100),
    token_reset         VARCHAR(100),
    token_reset_expiry  TIMESTAMP,
    derniere_connexion  TIMESTAMP,
    tentatives_connexion INTEGER DEFAULT 0,
    bloque_jusqu_a      TIMESTAMP,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by          INTEGER REFERENCES utilisateurs(id) ON DELETE SET NULL
);

-- ============================================================
-- TABLE : sessions_utilisateurs (audit des connexions)
-- ============================================================
CREATE TABLE IF NOT EXISTS sessions_utilisateurs (
    id              SERIAL PRIMARY KEY,
    utilisateur_id  INTEGER NOT NULL REFERENCES utilisateurs(id) ON DELETE CASCADE,
    session_id      VARCHAR(255) NOT NULL,
    ip_address      VARCHAR(45),
    user_agent      TEXT,
    debut           TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fin             TIMESTAMP,
    actif           BOOLEAN DEFAULT TRUE
);

-- ============================================================
-- TABLE : entreprises
-- ============================================================
CREATE TABLE IF NOT EXISTS entreprises (
    id                      SERIAL PRIMARY KEY,
    uuid                    UUID DEFAULT uuid_generate_v4() UNIQUE NOT NULL,
    raison_sociale          VARCHAR(255) NOT NULL,
    nationalite             VARCHAR(100),
    activite_principale     TEXT,
    activites_secondaires   TEXT,
    branche_id              INTEGER REFERENCES branches_activite(id) ON DELETE SET NULL,
    region_id               INTEGER NOT NULL REFERENCES regions(id),
    departement_id          INTEGER REFERENCES departements(id) ON DELETE SET NULL,
    commune_id              INTEGER REFERENCES communes(id) ON DELETE SET NULL,
    localite                VARCHAR(200),
    quartier                VARCHAR(200),
    adresse                 TEXT,
    boite_postale           VARCHAR(50),
    telephone               VARCHAR(20),
    fax                     VARCHAR(20),
    email                   VARCHAR(255),
    numero_cnss             VARCHAR(50),
    agent_id                INTEGER REFERENCES utilisateurs(id) ON DELETE SET NULL,
    actif                   BOOLEAN DEFAULT TRUE,
    created_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by              INTEGER REFERENCES utilisateurs(id) ON DELETE SET NULL
);

-- ============================================================
-- TABLE : campagnes_damo (années de déclaration)
-- ============================================================
CREATE TABLE IF NOT EXISTS campagnes_damo (
    id              SERIAL PRIMARY KEY,
    annee           INTEGER NOT NULL UNIQUE,
    libelle         VARCHAR(100) NOT NULL,
    date_debut      DATE NOT NULL,
    date_fin        DATE NOT NULL,
    actif           BOOLEAN DEFAULT TRUE,
    description     TEXT,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by      INTEGER REFERENCES utilisateurs(id) ON DELETE SET NULL
);

-- ============================================================
-- TABLE : declarations (entête principale)
-- ============================================================
CREATE TABLE IF NOT EXISTS declarations (
    id                      SERIAL PRIMARY KEY,
    uuid                    UUID DEFAULT uuid_generate_v4() UNIQUE NOT NULL,
    code_questionnaire      VARCHAR(50),
    campagne_id             INTEGER NOT NULL REFERENCES campagnes_damo(id) ON DELETE RESTRICT,
    entreprise_id           INTEGER NOT NULL REFERENCES entreprises(id) ON DELETE RESTRICT,
    agent_id                INTEGER NOT NULL REFERENCES utilisateurs(id) ON DELETE RESTRICT,
    region_id               INTEGER NOT NULL REFERENCES regions(id),
    nom_enqueteur           VARCHAR(200),
    
    -- Statut
    statut                  VARCHAR(20) DEFAULT 'brouillon' 
                            CHECK (statut IN ('brouillon','soumise','validee','rejetee','corrigee')),
    
    -- Dates importantes
    date_soumission         TIMESTAMP,
    date_validation         TIMESTAMP,
    date_rejet              TIMESTAMP,
    motif_rejet             TEXT,
    observations            TEXT,
    
    -- Section I données complémentaires
    masse_salariale         NUMERIC(20,2),
    
    -- Validation
    validateur_id           INTEGER REFERENCES utilisateurs(id) ON DELETE SET NULL,
    
    -- Métadonnées
    ip_saisie               VARCHAR(45),
    etape_courante          INTEGER DEFAULT 1,
    pourcentage_completion  INTEGER DEFAULT 0,
    
    created_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    submitted_at            TIMESTAMP,
    
    UNIQUE(campagne_id, entreprise_id)
);

-- ============================================================
-- TABLE : declaration_effectifs_mensuels (Section II)
-- Effectif global en service au dernier jour de chaque mois
-- ============================================================
CREATE TABLE IF NOT EXISTS declaration_effectifs_mensuels (
    id              SERIAL PRIMARY KEY,
    declaration_id  INTEGER NOT NULL REFERENCES declarations(id) ON DELETE CASCADE,
    mois            INTEGER NOT NULL CHECK (mois BETWEEN 1 AND 12),
    effectif        INTEGER DEFAULT 0 CHECK (effectif >= 0),
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE(declaration_id, mois)
);

-- ============================================================
-- TABLE : declaration_categories_effectifs (Section III.1)
-- Répartition par catégories professionnelles, sexes et origines
-- ============================================================
CREATE TABLE IF NOT EXISTS declaration_categories_effectifs (
    id              SERIAL PRIMARY KEY,
    declaration_id  INTEGER NOT NULL REFERENCES declarations(id) ON DELETE CASCADE,
    categorie       VARCHAR(50) NOT NULL CHECK (categorie IN (
                        'cadres_superieurs','agents_maitrise','employes_bureau',
                        'ouvriers_qualifies','ouvriers_specialises','manœuvres','apprentis_stagiaires'
                    )),
    -- Nigériens
    nigeriens_h     INTEGER DEFAULT 0 CHECK (nigeriens_h >= 0),
    nigeriens_f     INTEGER DEFAULT 0 CHECK (nigeriens_f >= 0),
    -- Africains (autres pays africains)
    africains_h     INTEGER DEFAULT 0 CHECK (africains_h >= 0),
    africains_f     INTEGER DEFAULT 0 CHECK (africains_f >= 0),
    -- Autres nationalités
    autres_nat_h    INTEGER DEFAULT 0 CHECK (autres_nat_h >= 0),
    autres_nat_f    INTEGER DEFAULT 0 CHECK (autres_nat_f >= 0),
    
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE(declaration_id, categorie)
);

-- ============================================================
-- TABLE : declaration_niveaux_instruction (Section III.2)
-- Répartition par niveaux d'instruction et catégories
-- ============================================================
CREATE TABLE IF NOT EXISTS declaration_niveaux_instruction (
    id              SERIAL PRIMARY KEY,
    declaration_id  INTEGER NOT NULL REFERENCES declarations(id) ON DELETE CASCADE,
    categorie       VARCHAR(50) NOT NULL CHECK (categorie IN (
                        'cadres_superieurs','agents_maitrise','employes_bureau',
                        'ouvriers_qualifies','ouvriers_specialises','manœuvres','apprentis_stagiaires'
                    )),
    niveau          VARCHAR(50) NOT NULL CHECK (niveau IN (
                        'non_scolarise','primaire','secondaire_1er','secondaire_2eme',
                        'moyen_prof','superieur_prof','superieur_1','superieur_2','superieur_3'
                    )),
    effectif_h      INTEGER DEFAULT 0 CHECK (effectif_h >= 0),
    effectif_f      INTEGER DEFAULT 0 CHECK (effectif_f >= 0),
    
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE(declaration_id, categorie, niveau)
);

-- ============================================================
-- TABLE : declaration_formations (Section III.3)
-- Formation Professionnelle Continue
-- ============================================================
CREATE TABLE IF NOT EXISTS declaration_formations (
    id                  SERIAL PRIMARY KEY,
    declaration_id      INTEGER NOT NULL REFERENCES declarations(id) ON DELETE CASCADE,
    a_eu_formation      BOOLEAN DEFAULT FALSE,
    qualification       VARCHAR(100),
    nature_formation    TEXT,
    duree_formation     VARCHAR(100),
    effectif_h          INTEGER DEFAULT 0 CHECK (effectif_h >= 0),
    effectif_f          INTEGER DEFAULT 0 CHECK (effectif_f >= 0),
    observations        TEXT,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- TABLE : declaration_pertes_emploi (Section IV)
-- Perte d'emploi par motif et sexe
-- ============================================================
CREATE TABLE IF NOT EXISTS declaration_pertes_emploi (
    id              SERIAL PRIMARY KEY,
    declaration_id  INTEGER NOT NULL REFERENCES declarations(id) ON DELETE CASCADE,
    motif           VARCHAR(50) NOT NULL CHECK (motif IN (
                        'licenciement','demission','fin_contrat','retraite','deces','autres'
                    )),
    motif_autre     VARCHAR(200),
    effectif_h      INTEGER DEFAULT 0 CHECK (effectif_h >= 0),
    effectif_f      INTEGER DEFAULT 0 CHECK (effectif_f >= 0),
    
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE(declaration_id, motif)
);

-- ============================================================
-- TABLE : declaration_perspectives (Section V)
-- Perspectives d'emploi
-- ============================================================
CREATE TABLE IF NOT EXISTS declaration_perspectives (
    id              SERIAL PRIMARY KEY,
    declaration_id  INTEGER NOT NULL REFERENCES declarations(id) ON DELETE CASCADE UNIQUE,
    perspective     VARCHAR(20) CHECK (perspective IN ('hausse','stabilite','baisse')),
    justification   TEXT,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- TABLE : declaration_effectifs_etrangers (Section VI)
-- Effectifs par nationalité étrangère
-- ============================================================
CREATE TABLE IF NOT EXISTS declaration_effectifs_etrangers (
    id              SERIAL PRIMARY KEY,
    declaration_id  INTEGER NOT NULL REFERENCES declarations(id) ON DELETE CASCADE,
    pays            VARCHAR(100) NOT NULL,
    qualification   VARCHAR(100),
    fonction        VARCHAR(200),
    sexe            CHAR(1) CHECK (sexe IN ('H','F','M')),
    nombre          INTEGER DEFAULT 0 CHECK (nombre >= 0),
    
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- TABLE : historique_declarations (audit des changements)
-- ============================================================
CREATE TABLE IF NOT EXISTS historique_declarations (
    id              SERIAL PRIMARY KEY,
    declaration_id  INTEGER NOT NULL REFERENCES declarations(id) ON DELETE CASCADE,
    utilisateur_id  INTEGER REFERENCES utilisateurs(id) ON DELETE SET NULL,
    action          VARCHAR(50) NOT NULL,
    ancien_statut   VARCHAR(20),
    nouveau_statut  VARCHAR(20),
    details         JSONB,
    ip_address      VARCHAR(45),
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- TABLE : notifications
-- ============================================================
CREATE TABLE IF NOT EXISTS notifications (
    id              SERIAL PRIMARY KEY,
    utilisateur_id  INTEGER NOT NULL REFERENCES utilisateurs(id) ON DELETE CASCADE,
    titre           VARCHAR(255) NOT NULL,
    message         TEXT NOT NULL,
    type            VARCHAR(50) DEFAULT 'info' CHECK (type IN ('info','success','warning','error')),
    lu              BOOLEAN DEFAULT FALSE,
    lu_at           TIMESTAMP,
    lien            VARCHAR(500),
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- TABLE : parametres (configuration dynamique)
-- ============================================================
CREATE TABLE IF NOT EXISTS parametres (
    id          SERIAL PRIMARY KEY,
    cle         VARCHAR(100) NOT NULL UNIQUE,
    valeur      TEXT,
    description TEXT,
    type        VARCHAR(20) DEFAULT 'string' CHECK (type IN ('string','integer','boolean','json')),
    modifiable  BOOLEAN DEFAULT TRUE,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_by  INTEGER REFERENCES utilisateurs(id) ON DELETE SET NULL
);

-- ============================================================
-- TABLE : guides_documents
-- ============================================================
CREATE TABLE IF NOT EXISTS guides_documents (
    id              SERIAL PRIMARY KEY,
    titre           VARCHAR(255) NOT NULL,
    description     TEXT,
    fichier_nom     VARCHAR(255) NOT NULL,
    fichier_path    VARCHAR(500) NOT NULL,
    fichier_taille  INTEGER,
    fichier_type    VARCHAR(100),
    annee           INTEGER,
    actif           BOOLEAN DEFAULT TRUE,
    ordre           INTEGER DEFAULT 0,
    telechargements INTEGER DEFAULT 0,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by      INTEGER REFERENCES utilisateurs(id) ON DELETE SET NULL
);

-- ============================================================
-- TABLE : logs_activite (journal d'audit complet)
-- ============================================================
CREATE TABLE IF NOT EXISTS logs_activite (
    id              BIGSERIAL PRIMARY KEY,
    utilisateur_id  INTEGER REFERENCES utilisateurs(id) ON DELETE SET NULL,
    action          VARCHAR(100) NOT NULL,
    ressource       VARCHAR(100),
    ressource_id    INTEGER,
    details         JSONB,
    ip_address      VARCHAR(45),
    user_agent      TEXT,
    statut          VARCHAR(20) DEFAULT 'success' CHECK (statut IN ('success','failure','warning')),
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- INDEX POUR LES PERFORMANCES
-- ============================================================
CREATE INDEX IF NOT EXISTS idx_utilisateurs_email ON utilisateurs(email);
CREATE INDEX IF NOT EXISTS idx_utilisateurs_role ON utilisateurs(role);
CREATE INDEX IF NOT EXISTS idx_utilisateurs_region ON utilisateurs(region_id);
CREATE INDEX IF NOT EXISTS idx_entreprises_cnss ON entreprises(numero_cnss);
CREATE INDEX IF NOT EXISTS idx_entreprises_region ON entreprises(region_id);
CREATE INDEX IF NOT EXISTS idx_declarations_campagne ON declarations(campagne_id);
CREATE INDEX IF NOT EXISTS idx_declarations_entreprise ON declarations(entreprise_id);
CREATE INDEX IF NOT EXISTS idx_declarations_agent ON declarations(agent_id);
CREATE INDEX IF NOT EXISTS idx_declarations_statut ON declarations(statut);
CREATE INDEX IF NOT EXISTS idx_declarations_region ON declarations(region_id);
CREATE INDEX IF NOT EXISTS idx_historique_declaration ON historique_declarations(declaration_id);
CREATE INDEX IF NOT EXISTS idx_logs_utilisateur ON logs_activite(utilisateur_id);
CREATE INDEX IF NOT EXISTS idx_logs_created ON logs_activite(created_at);
CREATE INDEX IF NOT EXISTS idx_notifications_user ON notifications(utilisateur_id, lu);

-- ============================================================
-- FONCTION : mise à jour automatique updated_at
-- ============================================================
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ language 'plpgsql';

-- Triggers pour updated_at
CREATE TRIGGER update_regions_updated_at BEFORE UPDATE ON regions 
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_entreprises_updated_at BEFORE UPDATE ON entreprises 
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_declarations_updated_at BEFORE UPDATE ON declarations 
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_utilisateurs_updated_at BEFORE UPDATE ON utilisateurs 
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_campagnes_updated_at BEFORE UPDATE ON campagnes_damo 
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
