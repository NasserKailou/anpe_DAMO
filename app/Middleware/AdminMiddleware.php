<?php
/**
 * Middleware Admin
 */
namespace App\Middleware;

class AdminMiddleware
{
    public function handle(): void
    {
        if (!isAdmin()) {
            if (isAjax()) {
                jsonResponse(['success' => false, 'message' => 'Accès non autorisé'], 403);
            }
            http_response_code(403);
            include VIEW_PATH . '/errors/403.php';
            exit;
        }
    }
}
