<?php
/**
 * e-DAMO — Point d'entrée racine
 * Inclut directement public/index.php (front-controller)
 *
 * Nécessaire pour éviter le "403 Forbidden" d'Apache quand on accède
 * à http://localhost:8085/anpe_DAMO/ sans index.php visible à la racine.
 *
 * La réécriture .htaccess achemine déjà tout vers public/index.php,
 * mais si Apache vérifie l'existence d'un index avant de lire .htaccess
 * (DirectoryIndex, AllowOverride restrictions), ce fichier sert de filet.
 */

// Calculer le chemin absolu vers public/index.php
$publicIndex = __DIR__ . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'index.php';

if (file_exists($publicIndex)) {
    // Inclure directement le front-controller sans redirection HTTP
    require $publicIndex;
} else {
    // Dernier recours : redirection HTTP vers public/
    $script   = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
    $basePath = rtrim(dirname($script), '/');
    $query    = !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '';
    header('Location: ' . $basePath . '/public/' . $query, true, 302);
    exit;
}
