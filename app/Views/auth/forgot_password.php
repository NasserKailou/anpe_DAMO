<?php /* Vue Mot de passe oublié — e-DAMO */ ?>

<div class="auth-page-title">
    <i class="bi bi-key"></i> Réinitialiser le mot de passe
</div>

<?php if (isset($error)): ?>
<div class="alert alert-danger">
    <i class="bi bi-x-octagon-fill"></i>
    <span><?= e($error) ?></span>
</div>
<?php endif; ?>

<p style="font-size:.87rem;color:#6b7280;text-align:center;margin-bottom:20px;line-height:1.5;">
    Entrez votre adresse e-mail. Vous recevrez un lien pour créer un nouveau mot de passe.
</p>

<form method="POST" action="<?= url('mot-de-passe-oublie') ?>" id="forgotForm" novalidate>
    <?= csrfField() ?>

    <div class="mb-4">
        <label for="email" class="form-label">
            <i class="bi bi-envelope-fill"></i> Adresse e-mail
        </label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
            <input type="email" class="form-control" id="email" name="email"
                   placeholder="exemple@anpe-niger.ne"
                   required autofocus autocomplete="email">
        </div>
    </div>

    <button type="submit" class="btn-connexion mb-3" id="forgotBtn">
        <i class="bi bi-send-fill"></i>
        <span>Envoyer le lien de réinitialisation</span>
    </button>
</form>

<div class="auth-divider"><span>ou</span></div>

<a href="<?= url('login') ?>" class="btn-back-site">
    <i class="bi bi-arrow-left"></i>
    <span>Retour à la connexion</span>
    <i class="bi bi-chevron-right ms-auto" style="font-size:.78rem;opacity:.5"></i>
</a>
