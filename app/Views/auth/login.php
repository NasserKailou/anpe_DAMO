<?php /* Vue Login — e-DAMO */ ?>

<?php if (isset($error)): ?>
<div class="alert alert-danger">
    <i class="bi bi-x-octagon-fill"></i>
    <span><?= e($error) ?></span>
</div>
<?php endif; ?>

<form method="POST" action="<?= url('login') ?>" id="loginForm" novalidate>
    <?= csrfField() ?>

    <!-- Email -->
    <div class="mb-3">
        <label for="email" class="form-label">
            <i class="bi bi-envelope-fill"></i> Adresse e-mail
        </label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
            <input type="email" class="form-control" id="email" name="email"
                   value="<?= isset($email) ? e($email) : '' ?>"
                   placeholder="exemple@anpe-niger.ne"
                   required autofocus autocomplete="email">
        </div>
    </div>

    <!-- Mot de passe -->
    <div class="mb-2">
        <label for="password" class="form-label">
            <i class="bi bi-lock-fill"></i> Mot de passe
        </label>
        <div class="input-group password-group">
            <span class="input-group-text"><i class="bi bi-key-fill"></i></span>
            <input type="password" class="form-control" id="password" name="password"
                   placeholder="••••••••"
                   required autocomplete="current-password">
            <button type="button" class="btn toggle-password" data-target="password" tabindex="-1" title="Afficher">
                <i class="bi bi-eye"></i>
            </button>
        </div>
    </div>

    <!-- Se souvenir / Mot de passe oublié -->
    <div class="form-options">
        <div class="form-check mb-0">
            <input type="checkbox" class="form-check-input" id="remember" name="remember" value="1">
            <label class="form-check-label" for="remember">Se souvenir de moi</label>
        </div>
        <a href="<?= url('mot-de-passe-oublie') ?>" class="forgot-link">
            Mot de passe oublié ?
        </a>
    </div>

    <!-- Bouton connexion -->
    <button type="submit" class="btn-connexion" id="loginBtn">
        <i class="bi bi-box-arrow-in-right"></i>
        <span>Se connecter</span>
    </button>
</form>

<!-- Retour site public -->
<div class="auth-divider"><span>ou</span></div>

<a href="<?= url('accueil') ?>" class="btn-back-site">
    <i class="bi bi-globe2"></i>
    <span>Retour au site public</span>
    <i class="bi bi-chevron-right ms-auto" style="font-size:.78rem;opacity:.5"></i>
</a>
