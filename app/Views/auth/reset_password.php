<?php /* Vue Réinitialisation mot de passe — e-DAMO */ ?>

<div class="auth-page-title">
    <i class="bi bi-shield-lock"></i> Nouveau mot de passe
</div>

<?php if (isset($error)): ?>
<div class="alert alert-danger">
    <i class="bi bi-x-octagon-fill"></i>
    <span><?= e($error) ?></span>
</div>
<?php endif; ?>

<form method="POST" action="<?= url('reinitialiser-mot-de-passe') ?>" id="resetForm" novalidate>
    <?= csrfField() ?>
    <input type="hidden" name="token" value="<?= e($token ?? '') ?>">

    <div class="mb-3">
        <label for="password" class="form-label">
            <i class="bi bi-lock-fill"></i> Nouveau mot de passe
        </label>
        <div class="input-group password-group">
            <span class="input-group-text"><i class="bi bi-key-fill"></i></span>
            <input type="password" class="form-control" id="password" name="password"
                   placeholder="Au moins 8 caractères"
                   required minlength="8" autocomplete="new-password">
            <button type="button" class="btn toggle-password" data-target="password" tabindex="-1" title="Afficher">
                <i class="bi bi-eye"></i>
            </button>
        </div>
    </div>

    <div class="mb-4">
        <label for="password_confirm" class="form-label">
            <i class="bi bi-lock-fill"></i> Confirmer le mot de passe
        </label>
        <div class="input-group password-group">
            <span class="input-group-text"><i class="bi bi-key-fill"></i></span>
            <input type="password" class="form-control" id="password_confirm" name="password_confirm"
                   placeholder="Répéter le mot de passe"
                   required minlength="8" autocomplete="new-password">
            <button type="button" class="btn toggle-password" data-target="password_confirm" tabindex="-1" title="Afficher">
                <i class="bi bi-eye"></i>
            </button>
        </div>
    </div>

    <button type="submit" class="btn-connexion mb-3" id="resetBtn">
        <i class="bi bi-check2-circle"></i>
        <span>Enregistrer le nouveau mot de passe</span>
    </button>
</form>

<div class="auth-divider"><span>ou</span></div>

<a href="<?= url('login') ?>" class="btn-back-site">
    <i class="bi bi-arrow-left"></i>
    <span>Retour à la connexion</span>
    <i class="bi bi-chevron-right ms-auto" style="font-size:.78rem;opacity:.5"></i>
</a>
