<?php
/**
 * AssetController — Sert les fichiers CSS/JS locaux via PHP
 *
 * Contourne le blocage nginx/Plesk (HTTP 428) sur les fichiers statiques.
 * Route : GET /a/{type}/{file}
 *   ex: /a/css/main.css     → public/assets/css/main.css
 *       /a/js/main.js       → public/assets/js/main.js
 *       /a/img/logo-anpe.png → public/assets/img/logo-anpe.png
 *       /a/fonts/bootstrap-icons.woff2 → public/assets/fonts/bootstrap-icons.woff2
 */
namespace App\Controllers;

defined('EDAMO') or die('Accès direct interdit');

class AssetController extends BaseController
{
    private static array $MIME = [
        'css'   => 'text/css; charset=UTF-8',
        'js'    => 'application/javascript; charset=UTF-8',
        'png'   => 'image/png',
        'jpg'   => 'image/jpeg',
        'jpeg'  => 'image/jpeg',
        'gif'   => 'image/gif',
        'svg'   => 'image/svg+xml',
        'ico'   => 'image/x-icon',
        'woff'  => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf'   => 'font/ttf',
        'eot'   => 'application/vnd.ms-fontobject',
        'json'  => 'application/json',
        'map'   => 'application/json',
    ];

    /**
     * Sert un asset : GET /a/:type/:file
     */
    public function serve(string $type, string $file): void
    {
        // Sécurité : interdire les traversées de répertoire
        $type = basename($type);
        $file = basename($file);

        if (!$type || !$file) {
            http_response_code(404);
            exit('Asset not found');
        }

        // Construire le chemin physique
        $assetPath = PUBLIC_PATH . '/assets/' . $type . '/' . $file;

        // Vérifier que le fichier existe
        if (!file_exists($assetPath) || !is_file($assetPath)) {
            http_response_code(404);
            exit('Asset not found: ' . htmlspecialchars($type . '/' . $file));
        }

        // Déterminer le type MIME
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $mime = self::$MIME[$ext] ?? 'application/octet-stream';

        // Cache 30 jours (sauf en dev)
        $maxAge = (APP_ENV === 'production') ? 2592000 : 0;
        $etag   = md5_file($assetPath);
        $lastMod = filemtime($assetPath);

        // Vérifier If-None-Match pour cache 304
        $ifNoneMatch = $_SERVER['HTTP_IF_NONE_MATCH'] ?? '';
        if ($ifNoneMatch === '"' . $etag . '"') {
            http_response_code(304);
            exit;
        }

        // Vider tout output buffer en cours
        while (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($assetPath));
        header('ETag: "' . $etag . '"');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $lastMod) . ' GMT');
        header('Cache-Control: public, max-age=' . $maxAge);
        header('X-Asset-Served: PHP');

        readfile($assetPath);
        exit;
    }
}
