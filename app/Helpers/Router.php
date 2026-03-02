<?php
/**
 * Routeur HTTP e-DAMO
 */

namespace App\Helpers;

class Router
{
    private static array $routes = [];
    private static array $middlewares = [];

    /**
     * Ajouter une route GET
     */
    public static function get(string $path, array|callable $handler, array $middlewares = []): void
    {
        self::addRoute('GET', $path, $handler, $middlewares);
    }

    /**
     * Ajouter une route POST
     */
    public static function post(string $path, array|callable $handler, array $middlewares = []): void
    {
        self::addRoute('POST', $path, $handler, $middlewares);
    }

    /**
     * Ajouter une route (GET et POST)
     */
    public static function any(string $path, array|callable $handler, array $middlewares = []): void
    {
        self::addRoute('GET', $path, $handler, $middlewares);
        self::addRoute('POST', $path, $handler, $middlewares);
    }

    /**
     * Enregistrer une route
     */
    private static function addRoute(string $method, string $path, array|callable $handler, array $middlewares): void
    {
        self::$routes[] = [
            'method'      => $method,
            'path'        => $path,
            'pattern'     => self::pathToRegex($path),
            'handler'     => $handler,
            'middlewares' => $middlewares,
        ];
    }

    /**
     * Convertir un chemin en regex (support des paramètres :id)
     */
    private static function pathToRegex(string $path): string
    {
        $pattern = preg_replace('/\/:([a-z_]+)/', '/(?P<$1>[^/]+)', $path);
        $pattern = str_replace('/', '\/', $pattern);
        return '/^' . $pattern . '$/i';
    }

    /**
     * Dispatcher la requête courante
     */
    public static function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        // Support du spoofing de méthode (PUT, DELETE via POST + _method)
        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }

        // Obtenir l'URI sans query string et sans le préfixe du sous-dossier
        // Cas 1 : /anpe_DAMO/profil           → /profil
        // Cas 2 : /anpe_DAMO/public/profil    → /profil  (après redirection interne Apache)
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        $basePath = defined('BASE_PATH') ? BASE_PATH : '';

        // Supprimer le préfixe BASE_PATH (ex: /anpe_DAMO)
        if ($basePath !== '' && str_starts_with($uri, $basePath)) {
            $uri = substr($uri, strlen($basePath));
        }

        // Supprimer le segment /public/ résiduel qu'Apache peut injecter
        // quand le .htaccess racine redirige en interne vers public/
        if (str_starts_with($uri, '/public/') || $uri === '/public') {
            $uri = substr($uri, strlen('/public'));
        }

        $uri = '/' . ltrim($uri, '/');
        if ($uri === '') $uri = '/';

        foreach (self::$routes as $route) {
            if ($route['method'] !== $method && $route['method'] !== 'ANY') continue;

            if (preg_match($route['pattern'], $uri, $matches)) {
                // Extraire les paramètres nommés
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // Exécuter les middlewares
                foreach ($route['middlewares'] as $middleware) {
                    $middlewareClass = "App\\Middleware\\$middleware";
                    if (class_exists($middlewareClass)) {
                        (new $middlewareClass())->handle();
                    }
                }

                // Appeler le handler
                self::callHandler($route['handler'], $params);
                return;
            }
        }

        // 404
        self::handle404();
    }

    /**
     * Appeler un handler (tableau [Controller, method] ou callable)
     */
    private static function callHandler(array|callable $handler, array $params): void
    {
        if (is_callable($handler)) {
            call_user_func_array($handler, $params);
            return;
        }

        if (is_array($handler)) {
            [$controllerClass, $method] = $handler;
            $fullClass = "App\\Controllers\\$controllerClass";
            if (class_exists($fullClass)) {
                $controller = new $fullClass();
                if (method_exists($controller, $method)) {
                    call_user_func_array([$controller, $method], $params);
                    return;
                }
            }
        }

        self::handle404();
    }

    /**
     * Gérer les erreurs 404
     */
    private static function handle404(): void
    {
        http_response_code(404);
        if (isAjax()) {
            jsonResponse(['success' => false, 'message' => 'Page non trouvée'], 404);
        }
        // Afficher page 404
        $viewFile = VIEW_PATH . '/errors/404.php';
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            echo '<h1>404 - Page non trouvée</h1>';
        }
        exit;
    }
}
