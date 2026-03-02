-- ============================================================
-- e-DAMO - Données initiales (seeds)
-- ANPE Niger
-- ============================================================

-- Régions du Niger
INSERT INTO regions (code, nom) VALUES
    ('1',  'Agadez'),
    ('2',  'Diffa'),
    ('3',  'Dosso'),
    ('4',  'Maradi'),
    ('5',  'Tahoua'),
    ('6',  'Tillabéri'),
    ('7',  'Zinder'),
    ('8',  'Niamey'),
    ('11', 'Arlit'),
    ('51', 'Konni')
ON CONFLICT (code) DO NOTHING;

-- Départements principaux
INSERT INTO departements (region_id, nom) VALUES
    ((SELECT id FROM regions WHERE code='1'), 'Agadez'),
    ((SELECT id FROM regions WHERE code='1'), 'Arlit'),
    ((SELECT id FROM regions WHERE code='1'), 'Bilma'),
    ((SELECT id FROM regions WHERE code='1'), 'Tchirozerine'),
    ((SELECT id FROM regions WHERE code='2'), 'Diffa'),
    ((SELECT id FROM regions WHERE code='2'), 'Maine-Soroa'),
    ((SELECT id FROM regions WHERE code='2'), 'Nguigmi'),
    ((SELECT id FROM regions WHERE code='3'), 'Boboye'),
    ((SELECT id FROM regions WHERE code='3'), 'Dosso'),
    ((SELECT id FROM regions WHERE code='3'), 'Gaya'),
    ((SELECT id FROM regions WHERE code='3'), 'Loga'),
    ((SELECT id FROM regions WHERE code='3'), 'Doutchi'),
    ((SELECT id FROM regions WHERE code='4'), 'Aguié'),
    ((SELECT id FROM regions WHERE code='4'), 'Dakoro'),
    ((SELECT id FROM regions WHERE code='4'), 'Guidan-Roumdji'),
    ((SELECT id FROM regions WHERE code='4'), 'Madarounfa'),
    ((SELECT id FROM regions WHERE code='4'), 'Maradi'),
    ((SELECT id FROM regions WHERE code='4'), 'Mayahi'),
    ((SELECT id FROM regions WHERE code='4'), 'Tessaoua'),
    ((SELECT id FROM regions WHERE code='5'), 'Abalak'),
    ((SELECT id FROM regions WHERE code='5'), 'Birni-N''Konni'),
    ((SELECT id FROM regions WHERE code='5'), 'Bouza'),
    ((SELECT id FROM regions WHERE code='5'), 'Illéla'),
    ((SELECT id FROM regions WHERE code='5'), 'Keïta'),
    ((SELECT id FROM regions WHERE code='5'), 'Madaoua'),
    ((SELECT id FROM regions WHERE code='5'), 'Tahoua'),
    ((SELECT id FROM regions WHERE code='5'), 'Tchintabaraden'),
    ((SELECT id FROM regions WHERE code='6'), 'Filingué'),
    ((SELECT id FROM regions WHERE code='6'), 'Kollo'),
    ((SELECT id FROM regions WHERE code='6'), 'Ouallam'),
    ((SELECT id FROM regions WHERE code='6'), 'Say'),
    ((SELECT id FROM regions WHERE code='6'), 'Tera'),
    ((SELECT id FROM regions WHERE code='6'), 'Tillabéri'),
    ((SELECT id FROM regions WHERE code='6'), 'Torodi'),
    ((SELECT id FROM regions WHERE code='7'), 'Gouré'),
    ((SELECT id FROM regions WHERE code='7'), 'Magaria'),
    ((SELECT id FROM regions WHERE code='7'), 'Matameye'),
    ((SELECT id FROM regions WHERE code='7'), 'Mirriah'),
    ((SELECT id FROM regions WHERE code='7'), 'Tanout'),
    ((SELECT id FROM regions WHERE code='7'), 'Zinder'),
    ((SELECT id FROM regions WHERE code='8'), 'Niamey I'),
    ((SELECT id FROM regions WHERE code='8'), 'Niamey II'),
    ((SELECT id FROM regions WHERE code='8'), 'Niamey III'),
    ((SELECT id FROM regions WHERE code='8'), 'Niamey IV'),
    ((SELECT id FROM regions WHERE code='8'), 'Niamey V')
ON CONFLICT DO NOTHING;

-- Branches d'activité (9 branches ANPE)
INSERT INTO branches_activite (code, libelle, description) VALUES
    ('B1', 'Agriculture, Élevage, Chasse et Sylviculture', 'Activités agricoles et d''élevage'),
    ('B2', 'Pêche', 'Activités de pêche et pisciculture'),
    ('B3', 'Industries extractives et mines', 'Extraction minière, pétrole, gaz'),
    ('B4', 'Industries manufacturières', 'Transformation industrielle'),
    ('B5', 'Electricité, Gaz et Eau', 'Production et distribution d''énergie et d''eau'),
    ('B6', 'Construction et BTP', 'Travaux de construction et bâtiment'),
    ('B7', 'Commerce, Restauration et Hôtellerie', 'Commerce et tourisme'),
    ('B8', 'Transport, Entreposage et Communications', 'Logistique et télécommunications'),
    ('B9', 'Services', 'Services financiers, éducation, santé, administration')
ON CONFLICT (code) DO NOTHING;

-- Campagne DAMO 2025
INSERT INTO campagnes_damo (annee, libelle, date_debut, date_fin, actif, description) VALUES
    (2025, 'Déclaration Annuelle 2025', '2025-01-01', '2025-03-31', TRUE, 
     'Campagne de collecte de la Déclaration Annuelle de la Main d''Œuvre pour l''année 2025')
ON CONFLICT (annee) DO NOTHING;

-- Compte Super Administrateur par défaut
-- Mot de passe: Admin@ANPE2025! (bcrypt hash - à changer impérativement)
INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role, actif, email_verifie) VALUES
    ('ANPE', 'Administrateur', 'admin@anpe-niger.ne', 
     '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
     'super_admin', TRUE, TRUE)
ON CONFLICT (email) DO NOTHING;

-- Paramètres système
INSERT INTO parametres (cle, valeur, description, type) VALUES
    ('app_nom', 'e-DAMO', 'Nom de l''application', 'string'),
    ('app_slogan', 'Déclaration Annuelle de la Main d''Œuvre en ligne', 'Slogan de l''application', 'string'),
    ('annee_courante', '2025', 'Année de déclaration courante', 'integer'),
    ('deadline_declaration', '2025-03-31', 'Date limite de déclaration', 'string'),
    ('email_contact', 'anpe-niger16@gmail.com', 'Email de contact', 'string'),
    ('tel_contact', '20 73 33 84', 'Téléphone de contact', 'string'),
    ('site_web', 'https://www.anpe-niger.ne', 'Site web officiel', 'string'),
    ('adresse', 'BP 13 222 NIAMEY – NIGER', 'Adresse postale', 'string'),
    ('maintenance_mode', 'false', 'Mode maintenance activé', 'boolean'),
    ('inscriptions_ouvertes', 'true', 'Inscriptions ouvertes', 'boolean'),
    ('items_par_page', '20', 'Nombre d''éléments par page', 'integer'),
    ('logo_path', '/assets/img/logo-anpe.png', 'Chemin du logo', 'string')
ON CONFLICT (cle) DO NOTHING;
