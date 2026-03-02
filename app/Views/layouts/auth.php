<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? APP_NAME) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="/assets/css/auth.css">
</head>
<body class="auth-layout">
    <div class="auth-wrapper">
        <div class="auth-container">
            <div class="auth-header">
                <img src="/assets/img/logo-anpe.png" alt="ANPE Niger" class="auth-logo">
                <h1 class="auth-title"><?= APP_NAME ?></h1>
                <p class="auth-subtitle">Agence Nationale pour la Promotion de l'Emploi</p>
            </div>
            <?php if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?> mb-3">
                <?= e($flash['message']) ?>
            </div>
            <?php endif; ?>
            <?= $content ?>
        </div>
        <div class="auth-footer">
            <p>© <?= date('Y') ?> ANPE Niger | BP 13 222 NIAMEY | Tél: 20 73 33 84</p>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/main.js"></script>
</body>
</html>
