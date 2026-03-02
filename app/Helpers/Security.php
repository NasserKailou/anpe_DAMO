<?php
/**
 * Classe de sécurité centralisée
 */
namespace App\Helpers;

class Security
{
    /**
     * Hacher un mot de passe
     */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]);
    }

    /**
     * Vérifier un mot de passe
     */
    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Vérifier la force du mot de passe
     */
    public static function checkPasswordStrength(string $password): array
    {
        $errors = [];
        if (strlen($password) < PASSWORD_MIN_LENGTH) {
            $errors[] = "Le mot de passe doit contenir au moins " . PASSWORD_MIN_LENGTH . " caractères";
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = "Le mot de passe doit contenir au moins une majuscule";
        }
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = "Le mot de passe doit contenir au moins une minuscule";
        }
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = "Le mot de passe doit contenir au moins un chiffre";
        }
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = "Le mot de passe doit contenir au moins un caractère spécial";
        }
        return $errors;
    }

    /**
     * Générer un token sécurisé
     */
    public static function generateToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }

    /**
     * Vérifier le token CSRF
     */
    public static function verifyCsrfToken(string $token): bool
    {
        return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Sanitiser pour une requête SQL (utiliser les requêtes préparées !)
     */
    public static function sanitizeString(string $value): string
    {
        return trim(htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    /**
     * Valider un email
     */
    public static function validateEmail(string $email): bool
    {
        return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Valider un entier
     */
    public static function validateInt(mixed $value, int $min = 0, ?int $max = null): bool
    {
        $options = ['options' => ['min_range' => $min]];
        if ($max !== null) {
            $options['options']['max_range'] = $max;
        }
        return filter_var($value, FILTER_VALIDATE_INT, $options) !== false;
    }

    /**
     * Vérifier si le compte est bloqué
     */
    public static function isAccountLocked(array $user): bool
    {
        if ($user['bloque_jusqu_a'] === null) return false;
        return strtotime($user['bloque_jusqu_a']) > time();
    }

    /**
     * Vérifier si un fichier uploadé est sûr
     */
    public static function validateUploadedFile(array $file, array $allowedTypes = null): array
    {
        $errors = [];
        $allowedTypes = $allowedTypes ?? ALLOWED_TYPES;

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = self::uploadErrorMessage($file['error']);
            return $errors;
        }

        if ($file['size'] > MAX_FILE_SIZE) {
            $errors[] = "Le fichier est trop volumineux (max " . formatFileSize(MAX_FILE_SIZE) . ")";
        }

        // Vérification du type MIME réel
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $realMime = $finfo->file($file['tmp_name']);
        if (!in_array($realMime, $allowedTypes)) {
            $errors[] = "Type de fichier non autorisé ($realMime)";
        }

        return $errors;
    }

    /**
     * Message d'erreur upload
     */
    private static function uploadErrorMessage(int $error): string
    {
        return match($error) {
            UPLOAD_ERR_INI_SIZE  => "Le fichier dépasse la taille maximale autorisée par le serveur",
            UPLOAD_ERR_FORM_SIZE => "Le fichier dépasse la taille maximale autorisée",
            UPLOAD_ERR_PARTIAL   => "Le fichier n'a été que partiellement téléchargé",
            UPLOAD_ERR_NO_FILE   => "Aucun fichier n'a été téléchargé",
            UPLOAD_ERR_NO_TMP_DIR => "Dossier temporaire manquant",
            UPLOAD_ERR_CANT_WRITE => "Impossible d'écrire le fichier sur le disque",
            default              => "Erreur lors de l'upload du fichier"
        };
    }

    /**
     * Déplacer un fichier uploadé de façon sécurisée
     */
    public static function moveUploadedFile(array $file, string $destination, string $prefix = ''): string|false
    {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = $prefix . bin2hex(random_bytes(8)) . '_' . time() . '.' . $ext;
        $path = rtrim($destination, '/') . '/' . $filename;

        if (!is_dir($destination)) {
            mkdir($destination, 0750, true);
        }

        if (move_uploaded_file($file['tmp_name'], $path)) {
            return $filename;
        }
        return false;
    }

    /**
     * Nettoyer les en-têtes de sortie pour la prévention d'injection
     */
    public static function setSecurityHeaders(): void
    {
        if (!headers_sent()) {
            header('X-Content-Type-Options: nosniff');
            header('X-Frame-Options: SAMEORIGIN');
            header('X-XSS-Protection: 1; mode=block');
        }
    }
}
