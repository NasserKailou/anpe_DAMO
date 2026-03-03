<?php
/**
 * e-DAMO - Configuration principale
 * ANPE Niger - Plateforme Digitale de Déclaration Annuelle de la Main d'Œuvre
 */

// Charger les variables d'environnement
$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value, " \t\n\r\0\x0B\"'");
        if (!array_key_exists($key, $_SERVER) && !array_key_exists($key, $_ENV)) {
            putenv("$key=$value");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}

// ===== CONFIGURATION APPLICATION =====
define('APP_NAME',        'e-DAMO');
define('APP_FULL_NAME',   'Plateforme Digitale de Déclaration Annuelle de la Main d\'Œuvre');
define('APP_VERSION',     '1.0.0');
define('APP_ENV',         getenv('APP_ENV') ?: 'production');
define('APP_DEBUG',       filter_var(getenv('APP_DEBUG') ?: false, FILTER_VALIDATE_BOOLEAN));
define('APP_URL',         getenv('APP_URL') ?: 'http://localhost');

// ─────────────────────────────────────────────────────────────────────────────
// BASE_PATH — détection automatique, universelle (Windows XAMPP + Linux Apache)
// ─────────────────────────────────────────────────────────────────────────────
//
// Algorithme : on compare SCRIPT_NAME et REQUEST_URI pour extraire le préfixe
// commun.  C'est la méthode la plus robuste quelle que soit la configuration
// Apache (sous-dossier, VirtualHost, Alias, RewriteBase, etc.).
//
// ┌─────────────────────────────────────────────────────────────────────────┐
// │ CAS 1 — XAMPP sous-dossier (htdocs/anpe_DAMO/)                         │
// │  URL         : http://localhost:8085/anpe_DAMO/login                   │
// │  SCRIPT_NAME : /anpe_DAMO/public/index.php                             │
// │  REQUEST_URI : /anpe_DAMO/public/login   (après réécriture .htaccess)  │
// │  → BASE_PATH : /anpe_DAMO                                              │
// ├─────────────────────────────────────────────────────────────────────────┤
// │ CAS 2 — VirtualHost dédié (DocumentRoot = .../anpe_DAMO/public)        │
// │  URL         : https://edamo.anpe-niger.ne/login                       │
// │  SCRIPT_NAME : /index.php                                              │
// │  REQUEST_URI : /login                                                  │
// │  → BASE_PATH : ''                                                      │
// └─────────────────────────────────────────────────────────────────────────┘
(function () {
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
    $requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';

    // ── Étape 1 : répertoire de index.php
    //    ex:  /anpe_DAMO/public/index.php  →  /anpe_DAMO/public
    //         /index.php                  →  ''  (dirname renvoie '/')
    $scriptDir = rtrim(dirname($scriptName), '/\\');
    if ($scriptDir === '.') $scriptDir = '';

    // ── Étape 2 : enlever le segment '/public' si présent
    //    ex:  /anpe_DAMO/public  →  /anpe_DAMO
    //         /public            →  ''
    if ($scriptDir === '/public' || str_ends_with($scriptDir, '/public')) {
        $scriptDir = rtrim(substr($scriptDir, 0, -strlen('/public')), '/\\');
    }

    // ── Étape 3 : si SCRIPT_NAME commence par REQUEST_URI c'est un VirtualHost
    //    (les deux ont le même préfixe nul) → BASE_PATH = ''
    //    Sinon BASE_PATH = $scriptDir (qui peut être '/anpe_DAMO')
    $base = $scriptDir;

    // Normalisation finale : jamais de slash terminal
    $base = rtrim($base, '/');

    // Sanity check : doit être '' ou commencer par '/'
    if ($base !== '' && !str_starts_with($base, '/')) {
        $base = '';
    }

    define('BASE_PATH', $base);
})();
define('APP_KEY',         getenv('APP_KEY') ?: 'changeme_32_chars_secret_key_here');
define('APP_TIMEZONE',    'Africa/Niamey');
define('APP_LOCALE',      'fr_FR');

// ===== CONFIGURATION BASE DE DONNÉES (MySQL) =====
define('DB_HOST',     getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT',     getenv('DB_PORT') ?: '3306');
define('DB_NAME',     getenv('DB_NAME') ?: 'edamo');
define('DB_USER',     getenv('DB_USER') ?: 'root');
define('DB_PASS',     getenv('DB_PASS') ?: '');
define('DB_SCHEMA',   ''); // Non utilisé avec MySQL

// ===== CONFIGURATION SESSION =====
define('SESSION_NAME',     'EDAMO_SESSION');
define('SESSION_LIFETIME', 7200); // 2 heures
define('SESSION_PATH',     dirname(__DIR__) . '/storage/sessions');
define('SESSION_SECURE',   APP_ENV === 'production');
define('SESSION_HTTPONLY',  true);
define('SESSION_SAMESITE',  'Strict');

// ===== CHEMINS =====
define('ROOT_PATH',    dirname(__DIR__));
define('APP_PATH',     ROOT_PATH . '/app');
define('PUBLIC_PATH',  ROOT_PATH . '/public');
define('VIEW_PATH',    APP_PATH . '/Views');
define('CONFIG_PATH',  ROOT_PATH . '/config');
define('STORAGE_PATH', ROOT_PATH . '/storage');
define('LOG_PATH',     STORAGE_PATH . '/logs');
define('UPLOAD_PATH',  PUBLIC_PATH . '/uploads');

// ===== SÉCURITÉ =====
define('BCRYPT_COST',         12);
define('CSRF_TOKEN_LENGTH',   32);
define('TOKEN_EXPIRY',        3600);
define('MAX_LOGIN_ATTEMPTS',  5);
define('LOCKOUT_TIME',        900); // 15 minutes
define('PASSWORD_MIN_LENGTH', 8);

// ===== PAGINATION =====
define('ITEMS_PER_PAGE', 20);

// ===== UPLOAD =====
define('MAX_FILE_SIZE',    10 * 1024 * 1024); // 10 MB
define('ALLOWED_TYPES',    ['application/pdf', 'image/jpeg', 'image/png']);
define('GUIDES_UPLOAD_DIR', UPLOAD_PATH . '/guides');

// ===== RÉGIONS NIGER =====
define('REGIONS_NIGER', [
    '1'  => 'Agadez',
    '2'  => 'Diffa',
    '3'  => 'Dosso',
    '4'  => 'Maradi',
    '5'  => 'Tahoua',
    '6'  => 'Tillabéri',
    '7'  => 'Zinder',
    '8'  => 'Niamey',
    '11' => 'Arlit',
    '51' => 'Konni',
]);

// ===== CATÉGORIES PROFESSIONNELLES =====
// ⚠️ Les clés doivent correspondre aux valeurs ENUM de la base de données
define('CATEGORIES_PROFESSIONNELLES', [
    'cadres_superieurs'    => 'Cadres supérieurs',
    'agents_maitrise'      => 'Agents de maîtrise',
    'employes_bureau'      => 'Employés de bureau',
    'ouvriers_qualifies'   => 'Ouvriers qualifiés',
    'ouvriers_specialises' => 'Ouvriers spécialisés',
    'manœuvres'            => 'Manœuvres',
    'apprentis_stagiaires' => 'Apprentis / Stagiaires',
]);

// ===== NIVEAUX D'INSTRUCTION =====
// ⚠️ Les clés doivent correspondre aux valeurs ENUM de la base de données
define('NIVEAUX_INSTRUCTION', [
    'non_scolarise'   => 'Non scolarisé',
    'primaire'        => 'Primaire',
    'secondaire_1er'  => 'Secondaire 1er cycle',
    'secondaire_2eme' => 'Secondaire 2ème cycle',
    'moyen_prof'      => 'Moyen (Ens. professionnel et technique)',
    'superieur_prof'  => 'Supérieur (Ens. professionnel et technique)',
    'superieur_1'     => 'Supérieur 1 (Bac + 2)',
    'superieur_2'     => 'Supérieur 2 (Bac + 3 ou 4)',
    'superieur_3'     => 'Supérieur 3 (Bac + 5 et plus)',
]);

// ===== MOTIFS PERTE D'EMPLOI =====
define('MOTIFS_PERTE_EMPLOI', [
    'licenciement'  => 'Licenciement',
    'demission'     => 'Démission',
    'fin_contrat'   => 'Fin de Contrat',
    'retraite'      => 'Retraite',
    'deces'         => 'Décès',
    'autres'        => 'Autres',
]);

// ===== STATUTS DÉCLARATION =====
define('STATUT_BROUILLON',  'brouillon');
define('STATUT_SOUMISE',    'soumise');
define('STATUT_VALIDEE',    'validee');
define('STATUT_REJETEE',    'rejetee');
define('STATUT_CORRIGEE',   'corrigee');

// ===== RÔLES UTILISATEURS =====
define('ROLE_SUPER_ADMIN', 'super_admin');
define('ROLE_ADMIN',       'admin');
define('ROLE_AGENT',       'agent');

// Configuration du fuseau horaire
date_default_timezone_set(APP_TIMEZONE);

// Configuration d'erreurs selon l'environnement
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', LOG_PATH . '/php_errors.log');
}

// ─── Configuration Email ──────────────────────────────────────────────────────
define('MAIL_HOST',      getenv('MAIL_HOST')      ?: 'smtp.gmail.com');
define('MAIL_PORT',      (int)(getenv('MAIL_PORT') ?: 587));
define('MAIL_USERNAME',  getenv('MAIL_USERNAME')   ?: '');
define('MAIL_PASSWORD',  getenv('MAIL_PASSWORD')   ?: '');
define('MAIL_FROM',      getenv('MAIL_FROM')       ?: 'noreply@anpe-niger.ne');
define('MAIL_FROM_NAME', getenv('MAIL_FROM_NAME')  ?: 'e-DAMO ANPE Niger');
define('MAIL_ENCRYPTION','tls');   // tls ou ssl
define('MAIL_ENABLED',   !empty(getenv('MAIL_PASSWORD')));
