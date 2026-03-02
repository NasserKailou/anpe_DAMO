<?php
/**
 * Contrôleur de base - Toutes les vues passent par render()
 */
namespace App\Controllers;

use App\Models\Database;

abstract class BaseController
{
    protected Database $db;
    protected array $data = [];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Afficher une vue
     */
    protected function render(string $view, array $data = [], string $layout = 'default'): void
    {
        // Fusionner les données
        $this->data = array_merge($this->data, $data);

        // Variables disponibles dans la vue
        extract($this->data);

        // Charger les paramètres globaux
        $pageTitle = $data['pageTitle'] ?? APP_NAME;
        $flash = flash();

        // Chemin de la vue
        $viewFile = VIEW_PATH . '/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewFile)) {
            throw new \RuntimeException("Vue introuvable: $view");
        }

        // Charger le layout
        $layoutFile = VIEW_PATH . '/layouts/' . $layout . '.php';
        if (file_exists($layoutFile)) {
            // Capturer le contenu de la vue
            ob_start();
            include $viewFile;
            $content = ob_get_clean();

            include $layoutFile;
        } else {
            include $viewFile;
        }
    }

    /**
     * Réponse JSON (API)
     */
    protected function json(array $data, int $status = 200): never
    {
        jsonResponse($data, $status);
    }

    /**
     * Vérifier CSRF et retourner erreur JSON si invalide
     */
    protected function requireCsrf(): void
    {
        if (!verifyCsrf()) {
            if (isAjax()) {
                $this->json(['success' => false, 'message' => 'Token de sécurité invalide.'], 403);
            }
            redirectWith('', 'error', 'Token de sécurité invalide. Veuillez réessayer.');
        }
    }

    /**
     * Valider et retourner les erreurs
     */
    protected function validate(array $data, array $rules): array
    {
        $errors = [];
        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? null;
            $ruleList = explode('|', $rule);

            foreach ($ruleList as $r) {
                if ($r === 'required' && (empty($value) && $value !== '0')) {
                    $errors[$field] = "Ce champ est obligatoire";
                    break;
                }
                if ($r === 'email' && $value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field] = "Email invalide";
                    break;
                }
                if (str_starts_with($r, 'min:') && $value !== null) {
                    $min = (int) substr($r, 4);
                    if (strlen((string)$value) < $min) {
                        $errors[$field] = "Minimum $min caractères requis";
                        break;
                    }
                }
                if (str_starts_with($r, 'max:') && $value !== null) {
                    $max = (int) substr($r, 4);
                    if (strlen((string)$value) > $max) {
                        $errors[$field] = "Maximum $max caractères autorisés";
                        break;
                    }
                }
                if ($r === 'integer' && $value !== null && $value !== '') {
                    if (!filter_var($value, FILTER_VALIDATE_INT)) {
                        $errors[$field] = "Valeur entière requise";
                        break;
                    }
                }
                if ($r === 'positive' && $value !== null) {
                    if ((int)$value < 0) {
                        $errors[$field] = "La valeur doit être positive";
                        break;
                    }
                }
            }
        }
        return $errors;
    }

    /**
     * Pagination helper
     */
    protected function paginate(int $total, int $perPage = ITEMS_PER_PAGE): array
    {
        return paginate($total, $perPage);
    }
}
