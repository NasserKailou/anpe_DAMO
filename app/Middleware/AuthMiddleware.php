<?php
/**
 * Middleware d'authentification
 */
namespace App\Middleware;

class AuthMiddleware
{
    public function handle(): void
    {
        if (!isAuthenticated()) {
            if (isAjax()) {
                jsonResponse(['success' => false, 'message' => 'Session expirée', 'redirect' => '/login'], 401);
            }
            $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'];
            redirectWith('login', 'warning', 'Veuillez vous connecter pour accéder à cette page.');
        }

        // Vérifier que l'utilisateur existe toujours et est actif
        $user = currentUser();
        if (!$user || !$user['actif']) {
            session_destroy();
            redirectWith('login', 'error', 'Votre compte a été désactivé. Contactez l\'administrateur.');
        }
    }
}
