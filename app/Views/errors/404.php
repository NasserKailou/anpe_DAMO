<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 – Page introuvable | <?= defined('APP_NAME') ? APP_NAME : 'e-DAMO' ?></title>
    <link rel="stylesheet" href="<?= defined('BASE_PATH') ? BASE_PATH : '' ?>/assets/css/bootstrap.min.css">
    <style>
        body { min-height: 100vh; display: flex; align-items: center; justify-content: center;
               background: linear-gradient(135deg,#f8f9fa 0%,#e9ecef 100%); font-family:'Inter',sans-serif; }
        .error-code { font-size: 7rem; font-weight: 900; color: #6c757d; line-height: 1; }
        .error-icon { font-size: 5rem; }
    </style>
</head>
<body>
    <div class="text-center px-4" style="max-width:520px">
        <div class="error-icon mb-3">🔍</div>
        <div class="error-code">404</div>
        <h2 class="fw-bold mt-2 mb-3 text-dark">Page introuvable</h2>
        <p class="text-muted mb-4">
            La page que vous recherchez n'existe pas ou a été déplacée.<br>
            Vérifiez l'URL ou retournez à la page d'accueil.
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
        <?php if (defined('APP_DEBUG') && APP_DEBUG && isset($_SERVER['REQUEST_URI'])): ?>
            <div class="mt-4 p-3 bg-white rounded border text-start">
                <small class="text-muted d-block mb-1">URL demandée :</small>
                <code class="text-danger small"><?= htmlspecialchars($_SERVER['REQUEST_URI']) ?></code>
            </div>
        <?php endif; ?>
    </div>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</body>
</html>
