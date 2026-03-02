<?php
/**
 * Autoloader PSR-4 simplifié
 */
defined('EDAMO') or die('Accès direct interdit');

class Autoloader
{
    private static array $namespaces = [];

    public static function register(): void
    {
        // Enregistrer les namespaces
        self::$namespaces = [
            'App\\Controllers\\'  => APP_PATH . '/Controllers/',
            'App\\Models\\'       => APP_PATH . '/Models/',
            'App\\Middleware\\'   => APP_PATH . '/Middleware/',
            'App\\Helpers\\'      => APP_PATH . '/Helpers/',
        ];

        spl_autoload_register([self::class, 'load']);

        // Charger les helpers globaux
        self::loadHelpers();
    }

    public static function load(string $class): void
    {
        foreach (self::$namespaces as $namespace => $path) {
            if (strpos($class, $namespace) === 0) {
                $relative = substr($class, strlen($namespace));
                $file = $path . str_replace('\\', '/', $relative) . '.php';
                if (file_exists($file)) {
                    require_once $file;
                    return;
                }
            }
        }
    }

    private static function loadHelpers(): void
    {
        $helpers = [
            APP_PATH . '/Helpers/functions.php',
            APP_PATH . '/Helpers/Security.php',
        ];
        foreach ($helpers as $helper) {
            if (file_exists($helper)) {
                require_once $helper;
            }
        }
    }
}
