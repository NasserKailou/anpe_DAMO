<!-- Vue Login -->
<?php if (isset($error)): ?>
<div class="alert alert-danger">
    <i class="bi bi-exclamation-triangle me-2"></i><?= e($error) ?>
</div>
<?php endif; ?>

<form method="POST" action="/login" class="auth-form" id="loginForm" novalidate>
    <?= csrfField() ?>
    
    <div class="mb-3">
        <label for="email" class="form-label">Adresse email</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
            <input type="email" class="form-control" id="email" name="email" 
                   value="<?= isset($email) ? e($email) : '' ?>"
                   placeholder="votre@email.ne" required autofocus autocomplete="email">
        </div>
    </div>
    
    <div class="mb-3">
        <label for="password" class="form-label">Mot de passe</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input type="password" class="form-control" id="password" name="password" 
                   placeholder="Votre mot de passe" required autocomplete="current-password">
            <button type="button" class="btn btn-outline-secondary toggle-password" data-target="password">
                <i class="bi bi-eye"></i>
            </button>
        </div>
    </div>
    
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="form-check">
            <input type="checkbox" class="form-check-input" id="remember" name="remember">
            <label class="form-check-label" for="remember">Se souvenir de moi</label>
        </div>
        <a href="/mot-de-passe-oublie" class="auth-link">Mot de passe oublié?</a>
    </div>
    
    <button type="submit" class="btn btn-primary btn-auth w-100" id="loginBtn">
        <i class="bi bi-box-arrow-in-right me-2"></i>
        Se connecter
    </button>
</form>

<div class="auth-links">
    <a href="/accueil" class="btn btn-link">
        <i class="bi bi-globe me-1"></i>Retour au site public
    </a>
</div>
