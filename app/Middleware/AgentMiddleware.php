<?php
/**
 * Middleware Agent
 */
namespace App\Middleware;

class AgentMiddleware
{
    public function handle(): void
    {
        if (!isAgent() && !isAdmin()) {
            if (isAjax()) {
                jsonResponse(['success' => false, 'message' => 'Accès non autorisé'], 403);
            }
            http_response_code(403);
            include VIEW_PATH . '/errors/403.php';
            exit;
        }
    }
}
