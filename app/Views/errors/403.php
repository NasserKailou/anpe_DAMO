<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 – Accès interdit | <?= defined('APP_NAME') ? APP_NAME : 'e-DAMO' ?></title>
    <!-- Bootstrap 5 CDN (évite blocage nginx Plesk) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" crossorigin="anonymous">
    <style>
        body { min-height: 100vh; display: flex; align-items: center; justify-content: center;
               background: linear-gradient(135deg,#f8f9fa 0%,#e9ecef 100%); font-family:'Inter',sans-serif; }
        .error-icon { font-size: 5rem; }
        .error-code { font-size: 7rem; font-weight: 900; color: #dc3545; line-height: 1; }
    </style>
</head>
<body>
    <div class="text-center px-4" style="max-width:500px">
        <div class="error-icon mb-3">🚫</div>
        <div class="error-code">403</div>
        <h2 class="fw-bold mt-2 mb-3 text-dark">Accès interdit</h2>
        <p class="text-muted mb-4">
            Vous n'avez pas les permissions nécessaires pour accéder à cette page.
            Contactez votre administrateur si vous pensez qu'il s'agit d'une erreur.
        </p>
        <div class="d-flex flex-column flex-sm-row gap-2 justify-content-center">
            <a href="<?= defined('BASE_PATH') ? BASE_PATH : '' ?>/"
               class="btn btn-primary">
                <i class="bi bi-house me-1"></i>Retour à l'accueil
            </a>
            <a href="javascript:history.back()"
               class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Page précédente
            </a>
        </div>
        <div class="mt-4 text-muted small">
            Si vous êtes connecté, vérifiez que votre session est toujours active.
            <a href="<?= defined('BASE_PATH') ? BASE_PATH : '' ?>/login">Se reconnecter</a>
        </div>
    </div>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</body>
</html>
