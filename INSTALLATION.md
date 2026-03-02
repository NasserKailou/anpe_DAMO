# 📦 Guide d'installation et de déploiement — e-DAMO

> **ANPE Niger** — Plateforme Digitale de Déclaration Annuelle de la Main d'Œuvre  
> Version 1.1.0

---

## 🖥️ Prérequis

| Composant | Version minimale |
|-----------|-----------------|
| PHP | 8.1+ |
| Apache | 2.4+ avec `mod_rewrite` et `mod_headers` activés |
| PostgreSQL | 13+ |
| XAMPP / WAMP / Laragon (Windows) | dernière version |

---

## ⚙️ CAS 1 — Déploiement en sous-dossier (XAMPP / WAMP standard)

> **URL finale :** `http://localhost:8085/anpe_DAMO/`  
> Cette méthode ne nécessite **aucune modification** de la configuration Apache.

### Étape 1 — Copier le projet

```
C:\xampp\htdocs\
└── anpe_DAMO\          ← copier TOUT le projet ici
    ├── app\
    ├── config\
    ├── database\
    ├── public\
    │   ├── .htaccess   ← RewriteBase /anpe_DAMO/  (déjà configuré)
    │   ├── index.php
    │   └── assets\
    ├── routes\
    ├── storage\
    ├── .htaccess       ← redirige vers public\ (déjà présent)
    ├── .env
    └── ...
```

### Étape 2 — Vérifier le `.htaccess` dans `public/`

Ouvrir `public/.htaccess` et s'assurer que cette ligne est **décommentée** :

```apache
RewriteBase /anpe_DAMO/
```

Et que celle-ci est **commentée** :

```apache
#RewriteBase /
```

### Étape 3 — Créer le fichier `.env`

Copier `.env.example` en `.env` et remplir :

```env
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost:8085/anpe_DAMO

DB_HOST=127.0.0.1
DB_PORT=5432
DB_NAME=edamo_db
DB_USER=edamo_user
DB_PASS=VotreMotDePasseDB

MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=votre@email.com
MAIL_PASSWORD=votre_mot_de_passe_app
MAIL_FROM=noreply@anpe-niger.ne
MAIL_FROM_NAME="e-DAMO ANPE Niger"
```

### Étape 4 — Créer la base de données PostgreSQL

```sql
-- Dans psql ou pgAdmin
CREATE USER edamo_user WITH PASSWORD 'VotreMotDePasseDB';
CREATE DATABASE edamo_db OWNER edamo_user;
GRANT ALL PRIVILEGES ON DATABASE edamo_db TO edamo_user;
```

Puis exécuter les migrations :

```bash
psql -U edamo_user -d edamo_db -f database/migrations/schema.sql
psql -U edamo_user -d edamo_db -f database/seeders/seed_data.sql
```

### Étape 5 — Créer le dossier storage/sessions

```bash
mkdir -p storage\sessions
mkdir -p storage\logs
# Windows :
md storage\sessions
md storage\logs
```

Donner les droits en écriture à Apache (XAMPP) :

```bash
# Windows : clic droit → Propriétés → Sécurité → Ajouter "Everyone" → Écriture
```

### Étape 6 — Activer mod_rewrite et mod_headers dans XAMPP

Ouvrir `C:\xampp\apache\conf\httpd.conf` et vérifier que ces lignes sont **décommentées** :

```apache
LoadModule rewrite_module modules/mod_rewrite.so
LoadModule headers_module modules/mod_headers.so
```

Et dans la section `<Directory "C:/xampp/htdocs">` :

```apache
AllowOverride All
```

### Étape 7 — Redémarrer Apache et tester

Redémarrer Apache via le panneau de contrôle XAMPP, puis :

```
http://localhost:8085/anpe_DAMO/          → Page d'accueil grand public ✓
http://localhost:8085/anpe_DAMO/login     → Connexion ✓
http://localhost:8085/anpe_DAMO/statistiques → Statistiques publiques ✓
```

---

## ⚙️ CAS 2 — VirtualHost dédié (accès sans sous-dossier)

> **URL finale :** `http://localhost:8085/`  
> Nécessite la création d'un VirtualHost Apache.

### Étape 1 — Copier le projet

Placer le projet n'importe où, par exemple :

```
C:\projets\anpe_DAMO\
├── public\    ← ce sera le DocumentRoot
├── app\
├── config\
└── ...
```

### Étape 2 — Créer le VirtualHost Apache

Ouvrir `C:\xampp\apache\conf\extra\httpd-vhosts.conf` et ajouter :

```apache
<VirtualHost *:8085>
    ServerName localhost
    DocumentRoot "C:/projets/anpe_DAMO/public"
    
    <Directory "C:/projets/anpe_DAMO/public">
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog "C:/projets/anpe_DAMO/storage/logs/apache_error.log"
    CustomLog "C:/projets/anpe_DAMO/storage/logs/apache_access.log" combined
</VirtualHost>
```

### Étape 3 — Activer les VirtualHosts dans `httpd.conf`

Vérifier que cette ligne est **décommentée** :

```apache
Include conf/extra/httpd-vhosts.conf
```

### Étape 4 — Ajuster `.htaccess` dans `public/`

Ouvrir `public/.htaccess` et s'assurer que cette ligne est **décommentée** :

```apache
RewriteBase /
```

Et que celle-ci est **commentée** :

```apache
#RewriteBase /anpe_DAMO/
```

### Étape 5 — Créer le `.env`

```env
APP_ENV=development
APP_URL=http://localhost:8085
```

Le reste identique au CAS 1.

### Étape 6 — Redémarrer Apache et tester

```
http://localhost:8085/          → Page d'accueil grand public ✓
http://localhost:8085/login     → Connexion ✓
http://localhost:8085/statistiques → Statistiques publiques ✓
```

---

## ⚙️ CAS 3 — Serveur Linux de production (Ubuntu/Debian)

### Étape 1 — Installer les dépendances

```bash
sudo apt update
sudo apt install apache2 php8.2 php8.2-pgsql php8.2-mbstring php8.2-xml php8.2-curl postgresql libapache2-mod-php8.2
sudo a2enmod rewrite headers
sudo systemctl restart apache2
```

### Étape 2 — Déployer le projet

```bash
cd /var/www/html
sudo git clone https://github.com/NasserKailou/anpe_DAMO.git
sudo chown -R www-data:www-data anpe_DAMO/
sudo chmod -R 755 anpe_DAMO/
sudo chmod -R 775 anpe_DAMO/storage/
```

### Étape 3 — Créer un VirtualHost Apache

```bash
sudo nano /etc/apache2/sites-available/edamo.conf
```

Contenu :

```apache
<VirtualHost *:80>
    ServerName edamo.anpe-niger.ne
    DocumentRoot /var/www/html/anpe_DAMO/public

    <Directory /var/www/html/anpe_DAMO/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/edamo_error.log
    CustomLog ${APACHE_LOG_DIR}/edamo_access.log combined
</VirtualHost>
```

```bash
sudo a2ensite edamo.conf
sudo a2dissite 000-default.conf
sudo systemctl reload apache2
```

### Étape 4 — Configurer PostgreSQL

```bash
sudo -u postgres psql
```

```sql
CREATE USER edamo_user WITH PASSWORD 'MotDePasseSecurise2025!';
CREATE DATABASE edamo_db OWNER edamo_user;
GRANT ALL PRIVILEGES ON DATABASE edamo_db TO edamo_user;
\q
```

```bash
cd /var/www/html/anpe_DAMO
psql -U edamo_user -h 127.0.0.1 -d edamo_db -f database/migrations/schema.sql
psql -U edamo_user -h 127.0.0.1 -d edamo_db -f database/seeders/seed_data.sql
```

### Étape 5 — Configurer `.env` pour la production

```bash
cp .env.example .env
nano .env
```

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://edamo.anpe-niger.ne
APP_KEY=GENERER_UNE_CLE_ALEATOIRE_32_CHARS

DB_HOST=127.0.0.1
DB_PORT=5432
DB_NAME=edamo_db
DB_USER=edamo_user
DB_PASS=MotDePasseSecurise2025!
```

### Étape 6 — HTTPS avec Let's Encrypt (optionnel)

```bash
sudo apt install certbot python3-certbot-apache
sudo certbot --apache -d edamo.anpe-niger.ne
sudo systemctl reload apache2
```

### Étape 7 — Ajuster `public/.htaccess` pour la racine

Dans `public/.htaccess`, décommenter :

```apache
RewriteBase /
```

Et commenter :

```apache
#RewriteBase /anpe_DAMO/
```

---

## 🔑 Comptes par défaut (après seeding)

| Rôle | Email | Mot de passe |
|------|-------|-------------|
| Super Admin | admin@edamo.ne | Admin2024! |
| Admin | admin.niamey@edamo.ne | Admin2024! |
| Agent | agent.niamey@edamo.ne | Agent2024! |

> ⚠️ **Changer ces mots de passe immédiatement** après la première connexion en production.

---

## 🔍 Dépannage

### Erreur "Not Found" sur toutes les pages

1. Vérifier que `mod_rewrite` est activé : `apache2ctl -M | grep rewrite`
2. Vérifier `AllowOverride All` dans la config Apache
3. Vérifier que `RewriteBase` dans `public/.htaccess` correspond à votre installation

### Page blanche (PHP)

Activer temporairement les erreurs dans `.env` :
```env
APP_DEBUG=true
```

### Erreur de connexion à la base de données

```bash
psql -U edamo_user -h 127.0.0.1 -d edamo_db -c "SELECT 1"
```

Vérifier que PostgreSQL écoute sur `127.0.0.1` dans `/etc/postgresql/*/main/postgresql.conf` :
```
listen_addresses = 'localhost'
```

### Erreur "Permission denied" sur storage/

```bash
sudo chown -R www-data:www-data storage/
sudo chmod -R 775 storage/
```

---

## 📁 Structure du projet

```
anpe_DAMO/
├── app/
│   ├── Controllers/        ← Contrôleurs MVC
│   ├── Helpers/            ← Utilitaires (Router, Mailer, Security…)
│   ├── Middleware/         ← Middlewares d'authentification
│   ├── Models/             ← Modèles (Database PDO)
│   └── Views/              ← Templates PHP
│       ├── admin/          ← Vues administration
│       ├── agent/          ← Vues agents
│       ├── auth/           ← Vues authentification
│       ├── errors/         ← Pages d'erreur
│       ├── layouts/        ← Gabarits partagés
│       └── public/         ← Vues grand public
├── config/
│   └── config.php          ← Configuration centrale
├── database/
│   ├── migrations/         ← Scripts SQL de création
│   └── seeders/            ← Données initiales
├── public/                 ← DOCUMENT ROOT (seul dossier accessible)
│   ├── .htaccess           ← ⚡ Front-controller Apache
│   ├── index.php           ← Point d'entrée unique
│   └── assets/             ← CSS, JS, images
├── routes/
│   └── web.php             ← Définition de toutes les routes
├── storage/
│   ├── logs/               ← Logs PHP et Apache
│   ├── sessions/           ← Sessions PHP
│   └── uploads/            ← Fichiers uploadés
├── .env                    ← Variables d'environnement (ne pas committer)
├── .env.example            ← Template .env
├── .htaccess               ← Redirige vers public/ (racine projet)
└── INSTALLATION.md         ← Ce guide
```

---

*Guide rédigé pour e-DAMO v1.1.0 — ANPE Niger — Mars 2026*
