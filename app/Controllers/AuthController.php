<?php
/**
 * Contrôleur d'Authentification
 */
namespace App\Controllers;

use App\Helpers\Security;

class AuthController extends BaseController
{
    /**
     * Login - GET et POST
     */
    public function login(): void
    {
        // Déjà connecté → rediriger
        if (isAuthenticated()) {
            $this->redirectByRole();
        }

        if (isPost()) {
            $this->requireCsrf();
            $this->processLogin();
            return;
        }

        $this->render('auth.login', [
            'pageTitle' => 'Connexion - ' . APP_NAME,
        ]);
    }

    /**
     * Traiter la tentative de connexion
     */
    private function processLogin(): void
    {
        $email    = strtolower(trim(post('email', '')));
        $password = post('password', '');
        $ip       = getClientIp();

        // Validation basique
        if (!$email || !$password) {
            $this->render('auth.login', [
                'pageTitle' => 'Connexion - ' . APP_NAME,
                'error'     => 'Veuillez renseigner votre email et mot de passe.',
            ]);
            return;
        }

        if (!Security::validateEmail($email)) {
            $this->render('auth.login', [
                'pageTitle' => 'Connexion - ' . APP_NAME,
                'error'     => 'Format d\'email invalide.',
                'email'     => e($email),
            ]);
            return;
        }

        // Récupérer l'utilisateur
        $user = $this->db->fetchOne(
            "SELECT u.*, r.nom AS region_nom 
             FROM utilisateurs u
             LEFT JOIN regions r ON r.id = u.region_id
             WHERE u.email = $1",
            [$email]
        );

        if (!$user) {
            logActivity('login_failed', 'auth', 0, ['email' => $email, 'reason' => 'user_not_found'], 'failure');
            $this->renderLoginError('Email ou mot de passe incorrect.', $email);
            return;
        }

        // Vérifier si le compte est bloqué
        if (Security::isAccountLocked($user)) {
            $minutes = ceil((strtotime($user['bloque_jusqu_a']) - time()) / 60);
            $this->renderLoginError(
                "Compte temporairement bloqué. Réessayez dans $minutes minute(s).",
                $email
            );
            return;
        }

        // Vérifier si le compte est actif
        if (!$user['actif']) {
            $this->renderLoginError('Votre compte est désactivé. Contactez l\'administrateur.', $email);
            return;
        }

        // Vérifier le mot de passe
        if (!Security::verifyPassword($password, $user['mot_de_passe'])) {
            $this->handleFailedLogin($user);
            logActivity('login_failed', 'auth', $user['id'], ['email' => $email, 'reason' => 'wrong_password'], 'failure');
            $this->renderLoginError('Email ou mot de passe incorrect.', $email);
            return;
        }

        // Connexion réussie : réinitialiser les tentatives
        $this->db->execute(
            "UPDATE utilisateurs SET tentatives_connexion = 0, bloque_jusqu_a = NULL, 
             derniere_connexion = CURRENT_TIMESTAMP WHERE id = $1",
            [$user['id']]
        );

        // Créer la session sécurisée
        // Sauvegarder les données avant régénération
        $sessionData = $_SESSION;
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user']      = [
            'id'         => $user['id'],
            'nom'        => $user['nom'],
            'prenom'     => $user['prenom'],
            'email'      => $user['email'],
            'role'       => $user['role'],
            'region_id'  => $user['region_id'],
            'region_nom' => $user['region_nom'],
            'actif'      => $user['actif'],
        ];
        $_SESSION['_created'] = time();
        $_SESSION['ip']       = $ip;

        // Logger la session
        $this->db->execute(
            "INSERT INTO sessions_utilisateurs (utilisateur_id, session_id, ip_address, user_agent) 
             VALUES ($1, $2, $3, $4)",
            [$user['id'], session_id(), $ip, $_SERVER['HTTP_USER_AGENT'] ?? '']
        );

        logActivity('login_success', 'auth', $user['id'], ['email' => $email]);

        // Rediriger vers l'URL demandée ou selon le rôle
        $intended = $_SESSION['intended_url'] ?? null;
        unset($_SESSION['intended_url']);

        if ($intended && strpos($intended, '/login') === false) {
            header('Location: ' . APP_URL . $intended);
            exit;
        }

        $this->redirectByRole($user['role']);
    }

    /**
     * Gérer les échecs de connexion
     */
    private function handleFailedLogin(array $user): void
    {
        $attempts = $user['tentatives_connexion'] + 1;
        if ($attempts >= MAX_LOGIN_ATTEMPTS) {
            $blockedUntil = date('Y-m-d H:i:s', time() + LOCKOUT_TIME);
            $this->db->execute(
                "UPDATE utilisateurs SET tentatives_connexion = $1, bloque_jusqu_a = $2 WHERE id = $3",
                [$attempts, $blockedUntil, $user['id']]
            );
        } else {
            $this->db->execute(
                "UPDATE utilisateurs SET tentatives_connexion = $1 WHERE id = $2",
                [$attempts, $user['id']]
            );
        }
    }

    /**
     * Rediriger selon le rôle
     */
    private function redirectByRole(string $role = null): never
    {
        $role = $role ?? (currentUser()['role'] ?? '');
        match($role) {
            ROLE_SUPER_ADMIN, ROLE_ADMIN => redirect('admin/dashboard'),
            ROLE_AGENT                   => redirect('agent/dashboard'),
            default                      => redirect('dashboard'),
        };
    }

    /**
     * Afficher le formulaire de login avec erreur
     */
    private function renderLoginError(string $error, string $email = ''): void
    {
        $this->render('auth.login', [
            'pageTitle' => 'Connexion - ' . APP_NAME,
            'error'     => $error,
            'email'     => e($email),
        ]);
    }

    /**
     * Déconnexion
     */
    public function logout(): void
    {
        if (isAuthenticated()) {
            $userId = $_SESSION['user_id'];
            logActivity('logout', 'auth', $userId);

            // Fermer la session dans la BD
            $this->db->execute(
                "UPDATE sessions_utilisateurs SET actif = FALSE, fin = CURRENT_TIMESTAMP 
                 WHERE session_id = $1",
                [session_id()]
            );
        }

        // Détruire la session
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(SESSION_NAME, '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        session_destroy();

        redirect('login');
    }

    /**
     * Mot de passe oublié
     */
    public function forgotPassword(): void
    {
        if (isPost()) {
            $this->requireCsrf();
            $email = strtolower(trim(post('email', '')));

            // On ne révèle pas si l'email existe (sécurité)
            if (Security::validateEmail($email)) {
                $user = $this->db->fetchOne(
                    "SELECT id, nom FROM utilisateurs WHERE email = $1 AND actif = TRUE",
                    [$email]
                );
                if ($user) {
                    $token = Security::generateToken();
                    $expiry = date('Y-m-d H:i:s', time() + 3600);
                    $this->db->execute(
                        "UPDATE utilisateurs SET token_reset = $1, token_reset_expiry = $2 WHERE id = $3",
                        [$token, $expiry, $user['id']]
                    );
                    // TODO: Envoyer l'email
                    logActivity('password_reset_request', 'auth', $user['id']);
                }
            }

            $this->render('auth.forgot_password', [
                'pageTitle' => 'Mot de passe oublié - ' . APP_NAME,
                'success'   => 'Si cet email existe, un lien de réinitialisation a été envoyé.',
            ]);
            return;
        }

        $this->render('auth.forgot_password', [
            'pageTitle' => 'Mot de passe oublié - ' . APP_NAME,
        ]);
    }

    /**
     * Réinitialisation du mot de passe
     */
    public function resetPassword(string $token): void
    {
        $user = $this->db->fetchOne(
            "SELECT id FROM utilisateurs WHERE token_reset = $1 AND token_reset_expiry > NOW()",
            [$token]
        );

        if (!$user) {
            redirect('mot-de-passe-oublie');
        }

        if (isPost()) {
            $this->requireCsrf();
            $password = post('password', '');
            $confirm  = post('password_confirm', '');

            $errors = Security::checkPasswordStrength($password);
            if ($password !== $confirm) {
                $errors[] = 'Les mots de passe ne correspondent pas.';
            }

            if (empty($errors)) {
                $hash = Security::hashPassword($password);
                $this->db->execute(
                    "UPDATE utilisateurs SET mot_de_passe = $1, token_reset = NULL, token_reset_expiry = NULL WHERE id = $2",
                    [$hash, $user['id']]
                );
                logActivity('password_reset', 'auth', $user['id']);
                redirectWith('login', 'success', 'Mot de passe réinitialisé avec succès. Connectez-vous.');
            }

            $this->render('auth.reset_password', [
                'pageTitle' => 'Nouveau mot de passe - ' . APP_NAME,
                'errors'    => $errors,
                'token'     => e($token),
            ]);
            return;
        }

        $this->render('auth.reset_password', [
            'pageTitle' => 'Nouveau mot de passe - ' . APP_NAME,
            'token'     => e($token),
        ]);
    }
}
