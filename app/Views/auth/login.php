<?php /* Vue Login — e-DAMO */ ?>

<?php if (isset($error)): ?>
<div class="alert alert-danger d-flex align-items-center gap-2 mb-4">
    <i class="bi bi-exclamation-octagon-fill"></i>
    <div><?= e($error) ?></div>
</div>
<?php endif; ?>

<form method="POST" action="<?= url('login') ?>" class="needs-validation" id="loginForm" novalidate>
    <?= csrfField() ?>

    <!-- Email -->
    <div class="mb-3">
        <label for="email" class="form-label">
            <i class="bi bi-person-fill me-1"></i>Adresse email
        </label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-envelope-fill"></i></span>
            <input type="email" class="form-control" id="email" name="email"
                   value="<?= isset($email) ? e($email) : '' ?>"
                   placeholder="votre@email.ne"
                   required autofocus autocomplete="email">
        </div>
    </div>

    <!-- Mot de passe -->
    <div class="mb-3">
        <label for="password" class="form-label">
            <i class="bi bi-lock-fill me-1"></i>Mot de passe
        </label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-key-fill"></i></span>
            <input type="password" class="form-control" id="password" name="password"
                   placeholder="••••••••"
                   required autocomplete="current-password">
            <button type="button" class="toggle-password btn" data-target="password" tabindex="-1"
                    title="Afficher / masquer">
                <i class="bi bi-eye"></i>
            </button>
        </div>
    </div>

    <!-- Se souvenir / Mot de passe oublié -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="form-check">
            <input type="checkbox" class="form-check-input" id="remember" name="remember">
            <label class="form-check-label" for="remember">Se souvenir de moi</label>
        </div>
        <a href="<?= url('mot-de-passe-oublie') ?>" class="forgot-link">
            Mot de passe oublié ?
        </a>
    </div>

    <!-- Bouton connexion -->
    <button type="submit" class="btn-login" id="loginBtn">
        <span><i class="bi bi-box-arrow-in-right me-2"></i>Se connecter</span>
    </button>
</form>

<!-- Lien retour site public -->
<div class="auth-divider"><span>ou</span></div>

<a href="<?= url('accueil') ?>" class="btn-public-link">
    <i class="bi bi-globe2"></i>
    <span>Retour au site public</span>
    <i class="bi bi-arrow-right" style="margin-left:auto;font-size:.8rem;opacity:.5"></i>
</a>
