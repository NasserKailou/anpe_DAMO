<?php
/**
 * Fonctions utilitaires globales e-DAMO
 */
defined('EDAMO') or die('Accès direct interdit');

/**
 * Escape HTML pour prévenir XSS
 */
function e(mixed $value): string
{
    if (is_array($value) || is_object($value)) {
        return '';
    }
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Obtenir la valeur d'une requête GET nettoyée
 */
function get(string $key, mixed $default = null): mixed
{
    return isset($_GET[$key]) ? sanitize($_GET[$key]) : $default;
}

/**
 * Obtenir la valeur d'une requête POST nettoyée
 */
function post(string $key, mixed $default = null): mixed
{
    return isset($_POST[$key]) ? sanitize($_POST[$key]) : $default;
}

/**
 * Nettoyer une valeur d'entrée
 */
function sanitize(mixed $value): mixed
{
    if (is_array($value)) {
        return array_map('sanitize', $value);
    }
    if (is_string($value)) {
        return trim(strip_tags($value));
    }
    return $value;
}

/**
 * Vérifier si la requête est AJAX
 */
function isAjax(): bool
{
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Vérifier si la méthode est POST
 */
function isPost(): bool
{
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

/**
 * Rediriger vers une URL
 */
function redirect(string $url): never
{
    header('Location: ' . APP_URL . '/' . ltrim($url, '/'));
    exit;
}

/**
 * Rediriger avec message flash
 */
function redirectWith(string $url, string $type, string $message): never
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    redirect($url);
}

/**
 * Obtenir et supprimer le message flash
 */
function flash(): ?array
{
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Vérifier si un utilisateur est connecté
 */
function isAuthenticated(): bool
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Obtenir l'utilisateur courant
 */
function currentUser(): ?array
{
    return $_SESSION['user'] ?? null;
}

/**
 * Vérifier le rôle de l'utilisateur
 */
function hasRole(string ...$roles): bool
{
    $user = currentUser();
    if (!$user) return false;
    return in_array($user['role'], $roles);
}

/**
 * Vérifier si l'utilisateur est admin (super_admin ou admin)
 */
function isAdmin(): bool
{
    return hasRole(ROLE_SUPER_ADMIN, ROLE_ADMIN);
}

/**
 * Vérifier si l'utilisateur est super admin
 */
function isSuperAdmin(): bool
{
    return hasRole(ROLE_SUPER_ADMIN);
}

/**
 * Vérifier si l'utilisateur est agent
 */
function isAgent(): bool
{
    return hasRole(ROLE_AGENT);
}

/**
 * Obtenir l'URL courante
 */
function currentUrl(): string
{
    return $_SERVER['REQUEST_URI'] ?? '/';
}

/**
 * Formater un nombre avec séparateurs
 */
function formatNumber(mixed $number, int $decimals = 0): string
{
    if ($number === null || $number === '') return '0';
    return number_format((float)$number, $decimals, ',', ' ');
}

/**
 * Formater une date
 */
function formatDate(?string $date, string $format = 'd/m/Y'): string
{
    if (!$date) return '-';
    try {
        return (new DateTime($date))->format($format);
    } catch (Exception $e) {
        return '-';
    }
}

/**
 * Formater une date et heure
 */
function formatDateTime(?string $datetime): string
{
    return formatDate($datetime, 'd/m/Y à H:i');
}

/**
 * Générer un token CSRF
 */
function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(CSRF_TOKEN_LENGTH));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Champ input CSRF caché
 */
function csrfField(): string
{
    return '<input type="hidden" name="_csrf_token" value="' . e(csrfToken()) . '">';
}

/**
 * Vérifier le token CSRF
 */
function verifyCsrf(): bool
{
    $token = $_POST['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Réponse JSON
 */
function jsonResponse(array $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Logger une activité
 */
function logActivity(string $action, string $ressource = '', int $ressourceId = 0, array $details = [], string $statut = 'success'): void
{
    try {
        $db = \App\Models\Database::getInstance();
        $userId = $_SESSION['user_id'] ?? null;
        $ip = getClientIp();
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        $db->execute(
            "INSERT INTO logs_activite (utilisateur_id, action, ressource, ressource_id, details, ip_address, user_agent, statut) 
             VALUES ($1, $2, $3, $4, $5, $6, $7, $8)",
            [$userId, $action, $ressource, $ressourceId ?: null, json_encode($details), $ip, $ua, $statut]
        );
    } catch (Exception $e) {
        // Ne pas faire planter l'application si le log échoue
    }
}

/**
 * Obtenir l'IP réelle du client
 */
function getClientIp(): string
{
    $headers = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
    foreach ($headers as $header) {
        if (!empty($_SERVER[$header])) {
            $ips = explode(',', $_SERVER[$header]);
            $ip = trim($ips[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
        }
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/**
 * Obtenir le label d'un statut de déclaration
 */
function statutLabel(string $statut): string
{
    return match($statut) {
        'brouillon'  => 'Brouillon',
        'soumise'    => 'Soumise',
        'validee'    => 'Validée',
        'rejetee'    => 'Rejetée',
        'corrigee'   => 'Corrigée',
        default      => ucfirst($statut)
    };
}

/**
 * Obtenir la classe CSS d'un badge de statut
 */
function statutBadgeClass(string $statut): string
{
    return match($statut) {
        'brouillon'  => 'badge-warning',
        'soumise'    => 'badge-info',
        'validee'    => 'badge-success',
        'rejetee'    => 'badge-danger',
        'corrigee'   => 'badge-secondary',
        default      => 'badge-light'
    };
}

/**
 * Obtenir le nom d'un mois
 */
function nomMois(int $mois): string
{
    $mois_noms = [
        1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
        5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
        9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
    ];
    return $mois_noms[$mois] ?? '';
}

/**
 * Paginer des résultats
 */
function paginate(int $total, int $perPage = ITEMS_PER_PAGE): array
{
    $currentPage = max(1, (int)(get('page') ?? 1));
    $totalPages = max(1, (int)ceil($total / $perPage));
    $currentPage = min($currentPage, $totalPages);
    $offset = ($currentPage - 1) * $perPage;

    return [
        'total'        => $total,
        'per_page'     => $perPage,
        'current_page' => $currentPage,
        'total_pages'  => $totalPages,
        'offset'       => $offset,
        'has_prev'     => $currentPage > 1,
        'has_next'     => $currentPage < $totalPages,
        'prev_page'    => $currentPage - 1,
        'next_page'    => $currentPage + 1,
    ];
}

/**
 * Valider et nettoyer un entier positif
 */
function positiveInt(mixed $value, int $default = 0): int
{
    $int = (int) filter_var($value, FILTER_VALIDATE_INT);
    return $int >= 0 ? $int : $default;
}

/**
 * Tronquer un texte
 */
function truncate(string $text, int $length = 100): string
{
    if (mb_strlen($text) <= $length) return $text;
    return mb_substr($text, 0, $length) . '...';
}

/**
 * Formater la taille d'un fichier
 */
function formatFileSize(int $bytes): string
{
    if ($bytes < 1024) return $bytes . ' o';
    if ($bytes < 1048576) return round($bytes / 1024, 1) . ' Ko';
    return round($bytes / 1048576, 1) . ' Mo';
}
