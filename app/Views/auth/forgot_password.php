<?php // Vue : Mot de passe oublié ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/auth.css">
</head>
<body class="auth-page">
<div class="auth-container">
    <div class="auth-card">
        <div class="auth-logo">
            <img src="/assets/img/logo-anpe.svg" alt="ANPE Niger" height="60">
            <h1>e-DAMO</h1>
            <p>Mot de passe oublié</p>
        </div>
        <?php if (isset($success)): ?>
        <div class="alert alert-success"><?= e($success) ?></div>
        <?php else: ?>
        <form method="POST" action="/mot-de-passe-oublie">
            <?= csrfField() ?>
            <div class="mb-3">
                <label class="form-label">Adresse email</label>
                <input type="email" name="email" class="form-control" required placeholder="votre@email.com">
            </div>
            <button type="submit" class="btn btn-primary w-100">Envoyer le lien de réinitialisation</button>
        </form>
        <?php endif; ?>
        <div class="text-center mt-3">
            <a href="/login" class="text-decoration-none"><i class="bi bi-arrow-left me-1"></i>Retour à la connexion</a>
        </div>
    </div>
</div>
</body>
</html>
