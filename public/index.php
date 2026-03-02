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

// Charger le routeur et dispatcher
require_once dirname(__DIR__) . '/routes/web.php';
