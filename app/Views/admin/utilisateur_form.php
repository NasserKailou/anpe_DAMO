<?php // Vue : Formulaire Utilisateur (Admin) - Création et Modification ?>

<div class="page-header">
    <div>
        <h1>
            <i class="bi bi-person-<?= $mode === 'create' ? 'plus' : 'gear' ?> me-2 text-primary"></i>
            <?= $mode === 'create' ? 'Nouvel utilisateur' : 'Modifier l\'utilisateur' ?>
        </h1>
    </div>
    <a href="/admin/utilisateurs" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Retour
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header">
                <h5><?= $mode === 'create' ? 'Informations du nouvel utilisateur' : 'Modifier les informations' ?></h5>
            </div>
            <div class="card-body">
                <form method="POST" action="/admin/utilisateur/<?= $mode === 'edit' ? (e($user['id']) . '/modifier') : 'nouveau' ?>">
                    <?= csrfField() ?>

                    <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $field => $err): ?>
                            <li><?= e($err) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Prénom</label>
                            <input type="text" name="prenom" class="form-control"
                                value="<?= e($old['prenom'] ?? $user['prenom'] ?? '') ?>"
                                placeholder="Prénom">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nom <span class="text-danger">*</span></label>
                            <input type="text" name="nom" class="form-control <?= !empty($errors['nom']) ? 'is-invalid' : '' ?>"
                                value="<?= e($old['nom'] ?? $user['nom'] ?? '') ?>"
                                placeholder="Nom de famille" required>
                            <?php if (!empty($errors['nom'])): ?>
                            <div class="invalid-feedback"><?= e($errors['nom']) ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control <?= !empty($errors['email']) ? 'is-invalid' : '' ?>"
                                value="<?= e($old['email'] ?? $user['email'] ?? '') ?>"
                                placeholder="email@exemple.com" required
                                <?= $mode === 'edit' ? 'readonly' : '' ?>>
                            <?php if (!empty($errors['email'])): ?>
                            <div class="invalid-feedback"><?= e($errors['email']) ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Téléphone</label>
                            <input type="text" name="telephone" class="form-control"
                                value="<?= e($old['telephone'] ?? $user['telephone'] ?? '') ?>"
                                placeholder="+227...">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Rôle <span class="text-danger">*</span></label>
                            <select name="role" class="form-select" required>
                                <option value="">Choisir un rôle</option>
                                <?php if (isSuperAdmin()): ?>
                                <option value="admin" <?= ($old['role'] ?? $user['role'] ?? '') === 'admin' ? 'selected' : '' ?>>
                                    Administrateur
                                </option>
                                <?php endif; ?>
                                <option value="agent" <?= ($old['role'] ?? $user['role'] ?? '') === 'agent' ? 'selected' : '' ?>>
                                    Agent (saisie régionale)
                                </option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Région assignée</label>
                            <select name="region_id" class="form-select">
                                <option value="">— Aucune région —</option>
                                <?php foreach ($regions as $r): ?>
                                <option value="<?= $r['id'] ?>"
                                    <?= ($old['region_id'] ?? $user['region_id'] ?? '') == $r['id'] ? 'selected' : '' ?>>
                                    <?= e($r['nom']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">
                                Mot de passe <?= $mode === 'create' ? '<span class="text-danger">*</span>' : '<small class="text-muted">(laisser vide pour ne pas changer)</small>' ?>
                            </label>
                            <input type="password" name="password" class="form-control <?= !empty($errors['password']) ? 'is-invalid' : '' ?>"
                                placeholder="Minimum 8 caractères, inclure majuscules et chiffres"
                                <?= $mode === 'create' ? 'required' : '' ?> autocomplete="new-password">
                            <?php if (!empty($errors['password'])): ?>
                            <div class="invalid-feedback"><?= e($errors['password']) ?></div>
                            <?php endif; ?>
                            <div class="form-text">
                                Exigences : 8+ caractères, 1 majuscule, 1 chiffre, 1 caractère spécial
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="/admin/utilisateurs" class="btn btn-outline-secondary">Annuler</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>
                            <?= $mode === 'create' ? 'Créer l\'utilisateur' : 'Enregistrer les modifications' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
