<?php
/**
 * e-DAMO - Point d'entrée principal (index.php)
 * ANPE Niger - Toutes les requêtes passent par ici
 * Les URLs sans extension .php sont gérées par .htaccess
 */

// Démarrer le tampon de sortie
ob_start();

// Définir la constante de sécurité
define('EDAMO', true);

// Charger la configuration
require_once dirname(__DIR__) . '/config/config.php';

// Configurer la session sécurisée
ini_set('session.save_path', SESSION_PATH);
ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
ini_set('session.cookie_lifetime', 0); // Expire à fermeture navigateur
ini_set('session.cookie_httponly', SESSION_HTTPONLY ? '1' : '0');
ini_set('session.cookie_samesite', SESSION_SAMESITE);
if (APP_ENV === 'production') {
    ini_set('session.cookie_secure', '1');
}
session_name(SESSION_NAME);
session_start();

// Régénérer l'ID de session périodiquement (anti-fixation)
if (!isset($_SESSION['_created'])) {
    $_SESSION['_created'] = time();
} elseif (time() - $_SESSION['_created'] > 1800) {
    session_regenerate_id(true);
    $_SESSION['_created'] = time();
}

// Charger l'autoloader
require_once dirname(__DIR__) . '/app/Helpers/Autoloader.php';
Autoloader::register();

// ── Sécurité : En-têtes HTTP ──
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    if (APP_ENV === 'production') {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
    }
    // CSP - autoriser Bootstrap CDN + notre domaine
    $csp = implode('; ', [
        "default-src 'self'",
        "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com",
        "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.googleapis.com",
        "font-src 'self' https://cdn.jsdelivr.net https://fonts.gstatic.com data:",
        "img-src 'self' data: blob:",
        "connect-src 'self'",
        "frame-ancestors 'none'",
        "base-uri 'self'",
        "form-action 'self'",
    ]);
    header("Content-Security-Policy: $csp");
}

// ── Rate limiting basique (anti-brute-force) ──
// Enlever le préfixe de sous-dossier avant de comparer les chemins
$_rawPath    = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$_basePath   = defined('BASE_PATH') ? BASE_PATH : rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
$requestPath = ($_basePath !== '' && str_starts_with($_rawPath, $_basePath))
    ? substr($_rawPath, strlen($_basePath))
    : $_rawPath;
$requestPath = '/' . ltrim($requestPath, '/');
if (in_array($requestPath, ['/login', '/mot-de-passe-oublie'])
    && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $rateLimitKey = 'rl_' . md5(($_SERVER['REMOTE_ADDR'] ?? '') . $requestPath);
    $now          = time();
    $windowSec    = 300;   // 5 minutes
    $maxRequests  = 10;    // 10 tentatives max

    if (!isset($_SESSION[$rateLimitKey])) {
        $_SESSION[$rateLimitKey] = ['count' => 0, 'start' => $now];
    }
    $rl = &$_SESSION[$rateLimitKey];
    if ($now - $rl['start'] > $windowSec) {
        $rl = ['count' => 0, 'start' => $now];
    }
    $rl['count']++;
    if ($rl['count'] > $maxRequests) {
        http_response_code(429);
        header('Retry-After: ' . ($windowSec - ($now - $rl['start'])));
        die(json_encode(['error' => 'Trop de tentatives. Veuillez patienter.']));
    }
}

// Charger le routeur et dispatcher
require_once dirname(__DIR__) . '/routes/web.php';
