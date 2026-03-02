# e-DAMO — Plateforme Digitale de Déclaration Annuelle de la Main d'Œuvre

**ANPE Niger** — Version 1.0.0

## Vue d'ensemble

Plateforme web pour la gestion des Déclarations Annuelles de la Main d'Œuvre (DAMO) au Niger. Développée en PHP 8.2 avec PostgreSQL 15 et Apache.

## 🟢 Fonctionnalités complétées

### Authentification & Sécurité
- ✅ Login / Logout avec session sécurisée (CSRF, HttpOnly, SameSite)
- ✅ Blocage de compte après 5 tentatives (900s)
- ✅ Hachage bcrypt (cost=12)
- ✅ Réinitialisation de mot de passe par email (token 1h)
- ✅ Gestion des rôles : `super_admin`, `admin`, `agent`

### Espace Administration (`/admin/`)
- ✅ Tableau de bord avec statistiques en temps réel
- ✅ Liste + détail + validation/rejet des déclarations
- ✅ Gestion des utilisateurs (CRUD + toggle actif)
- ✅ Gestion des campagnes DAMO
- ✅ Upload et gestion des guides de remplissage (PDF)
- ✅ Paramètres système
- ✅ Journaux d'audit / logs d'activité
- ✅ Liste des branches d'activité
- ✅ Export CSV des déclarations
- ✅ Statistiques avancées par région/campagne

### Espace Agent (`/agent/`)
- ✅ Tableau de bord personnel avec statistiques régionales
- ✅ Gestion des entreprises (CRUD dans la région)
- ✅ Déclaration multi-étapes (7 étapes avec auto-save)
  - Étape 1: Identification entreprise + masse salariale
  - Étape 2: Effectifs mensuels (12 mois)
  - Étape 3: Catégories professionnelles × origines × sexes
  - Étape 4: Niveaux d'instruction
  - Étape 5: Formation professionnelle
  - Étape 6: Pertes d'emploi (motifs)
  - Étape 7: Effectifs étrangers
- ✅ Soumission et aperçu de déclaration
- ✅ Correction de déclarations rejetées

### Espace Public (`/`)
- ✅ Page d'accueil avec statistiques globales
- ✅ Statistiques par année/région/catégorie
- ✅ Données ouvertes avec filtres
- ✅ Téléchargement de guides (PDF)

### Profil utilisateur (`/profil`)
- ✅ Modification des informations personnelles
- ✅ Changement de mot de passe

## 🔗 URLs principales

| URL | Description |
|-----|-------------|
| `http://localhost/` | Page d'accueil publique |
| `http://localhost/login` | Connexion |
| `http://localhost/admin/dashboard` | Tableau de bord admin |
| `http://localhost/agent/dashboard` | Tableau de bord agent |
| `http://localhost/admin/declarations` | Liste des déclarations |
| `http://localhost/agent/declaration/nouvelle` | Créer une déclaration |

## 👥 Comptes de test

| Email | Mot de passe | Rôle | Région |
|-------|-------------|------|--------|
| `admin@edamo.ne` | `Admin2024!` | super_admin | — |
| `admin2@edamo.ne` | `Admin2024!` | admin | — |
| `agent@edamo.ne` | `Admin2024!` | agent | Agadez |
| `agent.niamey@edamo.ne` | `Admin2024!` | agent | Tillabéry |
| `agent.zinder@edamo.ne` | `Admin2024!` | agent | Zinder |

## 🏗️ Architecture technique

```
webapp/
├── app/
│   ├── Controllers/     # 9 contrôleurs (Admin, Agent, Auth, Declaration, ...)
│   ├── Helpers/         # Router, Autoloader, Security, functions
│   ├── Middleware/       # Auth, Admin, Agent
│   ├── Models/          # Database (singleton PDO PostgreSQL)
│   └── Views/           # Vues PHP (admin/, agent/, auth/, public/, profil/)
├── config/config.php    # Configuration centralisée
├── database/
│   ├── migrations/      # Schéma SQL initial
│   └── seeds/          # Données de test
├── public/
│   ├── assets/          # CSS (main, admin, saisie, auth, public) + JS
│   └── index.php        # Point d'entrée unique
└── routes/web.php       # Définition de toutes les routes
```

## 🗄️ Modèle de données

**Tables principales :**
- `utilisateurs` — Comptes admin/agents
- `regions` — 8 régions du Niger
- `entreprises` — Entreprises par région
- `campagnes_damo` — Campagnes annuelles
- `declarations` — Déclarations DAMO (7 étapes)
- `declaration_effectifs_mensuels` — Effectifs par mois
- `declaration_categories_effectifs` — Par catégorie pro × origine × sexe
- `declaration_niveaux_instruction` — Niveaux d'instruction
- `declaration_formations` — Formation professionnelle
- `declaration_pertes_emploi` — Pertes d'emploi par motif
- `declaration_perspectives` — Perspectives emploi
- `declaration_effectifs_etrangers` — Personnel étranger
- `branches_activite` — 19 branches CITI
- `guides_documents` — Guides téléchargeables
- `parametres` — Configuration système
- `logs_activite` — Journal d'audit
- `sessions_utilisateurs` — Suivi des sessions

## ⚙️ Configuration requise

- PHP 8.2+ (ext pdo, pdo_pgsql, mbstring, json)
- PostgreSQL 15+
- Apache 2.4+ (mod_rewrite)

**Connexion DB :**
```
Host: 127.0.0.1:5432
Base: edamo_db
User: edamo_user
Pass: Edamo@ANPE2025!
```

## 🔧 Corrections techniques (v1.0.1)

### Bug critique résolu : Placeholders PDO
`Database.php` corrigé — `convertPlaceholders()` retourne maintenant `[$sql_converti, $params_réorganisés]` et toutes les méthodes utilisent les params convertis. Nouvelles méthodes `*Raw()` pour SQL avec `?` directs (ILIKE multi-colonne).

**Controllers corrigés :**
- `AdminController::declarations()` — ILIKE avec 3 colonnes
- `AgentController::entreprises()` — ILIKE avec 2 colonnes
- `DeclarationController::index()` — ILIKE avec 3 colonnes

## 🚧 À faire (prochaines étapes)

1. **Sécurité** : 2FA, durcissement CORS, headers sécurité
2. **Email** : Notifications SMTP (soumission, validation, rappels)
3. **Tests** : Tests unitaires contrôleurs + PHPUnit
4. **API REST** : Endpoints publics `/api/v1/` documentés
5. **Rapports** : Export PDF des déclarations individuelles
6. **Import** : Import CSV des entreprises en masse
7. **i18n** : Internationalisation (français/anglais)

## 📅 Historique

- **2026-03-02** : Version initiale — Structure complète, corrections DB, vues admin/agent
