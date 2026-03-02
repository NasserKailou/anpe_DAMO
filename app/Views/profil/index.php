<?php // Vue : Profil utilisateur ?>
<?php $user = currentUser(); ?>
<div class="page-header">
    <h1><i class="bi bi-person-gear me-2 text-primary"></i>Mon profil</h1>
</div>
<div class="row g-4">
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header"><h5>Informations personnelles</h5></div>
            <div class="card-body">
                <form method="POST" action="/profil/modifier">
                    <?= csrfField() ?>
                    <?php if ($flash && $flash['type'] === 'success' && strpos($flash['message'], 'profil') !== false): ?>
                    <div class="alert alert-success"><?= e($flash['message']) ?></div>
                    <?php endif; ?>
                    <div class="mb-3">
                        <label class="form-label">Prénom</label>
                        <input type="text" name="prenom" class="form-control" value="<?= e($user['prenom'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nom</label>
                        <input type="text" name="nom" class="form-control" value="<?= e($user['nom'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" value="<?= e($user['email'] ?? '') ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Téléphone</label>
                        <input type="text" name="telephone" class="form-control" value="<?= e($userDetails['telephone'] ?? '') ?>">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-check-lg me-1"></i>Mettre à jour
                    </button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <!-- Changer mot de passe -->
        <div class="card mb-3">
            <div class="card-header"><h5>Changer le mot de passe</h5></div>
            <div class="card-body">
                <form method="POST" action="/profil/mot-de-passe">
                    <?= csrfField() ?>
                    <?php if (!empty($pwdErrors)): ?>
                    <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($pwdErrors as $e_): ?><li><?= e($e_) ?></li><?php endforeach; ?></ul></div>
                    <?php endif; ?>
                    <div class="mb-3">
                        <label class="form-label">Mot de passe actuel</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nouveau mot de passe</label>
                        <input type="password" name="password" class="form-control" required minlength="8">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirmer le nouveau mot de passe</label>
                        <input type="password" name="password_confirm" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-shield-lock me-1"></i>Changer le mot de passe
                    </button>
                </form>
            </div>
        </div>

        <!-- Informations compte -->
        <div class="card">
            <div class="card-header"><h5>Informations du compte</h5></div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr><th>Rôle</th><td><span class="badge-role-<?= e($user['role'] ?? '') ?>"><?= ucfirst(str_replace('_', ' ', $user['role'] ?? '')) ?></span></td></tr>
                    <tr><th>Région</th><td><?= e($user['region_nom'] ?? 'Non assignée') ?></td></tr>
                    <tr><th>Dernière connexion</th><td><?= formatDateTime($userDetails['derniere_connexion'] ?? '') ?></td></tr>
                    <tr><th>Compte créé le</th><td><?= formatDate($userDetails['created_at'] ?? '') ?></td></tr>
                </table>
            </div>
        </div>
    </div>
</div>
