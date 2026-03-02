<?php defined('EDAMO') or die('Accès direct interdit');
$isEdit = ($mode === 'edit');
$u      = $user ?? [];
$old    = $old ?? $u;
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-person-<?= $isEdit ? 'gear' : 'plus' ?> me-2 text-primary"></i>
                    <?= $isEdit ? 'Modifier l\'utilisateur' : 'Créer un utilisateur' ?>
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $field => $msg): ?>
                        <li><?= e($msg) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <form method="POST" action="<?= $isEdit ? url('admin/utilisateur/' . $u['id'] . '/modifier') : url('admin/utilisateur/nouveau') ?>">
                    <?= csrfField() ?>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-500">Prénom <span class="text-danger">*</span></label>
                            <input type="text" name="prenom" class="form-control <?= isset($errors['prenom']) ? 'is-invalid' : '' ?>"
                                   value="<?= e($old['prenom'] ?? '') ?>" required>
                            <?php if (isset($errors['prenom'])): ?>
                            <div class="invalid-feedback"><?= e($errors['prenom']) ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-500">Nom <span class="text-danger">*</span></label>
                            <input type="text" name="nom" class="form-control <?= isset($errors['nom']) ? 'is-invalid' : '' ?>"
                                   value="<?= e($old['nom'] ?? '') ?>" required>
                            <?php if (isset($errors['nom'])): ?>
                            <div class="invalid-feedback"><?= e($errors['nom']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-7">
                            <label class="form-label fw-500">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                                   value="<?= e($old['email'] ?? '') ?>"
                                   <?= $isEdit ? 'readonly' : 'required' ?>>
                            <?php if (isset($errors['email'])): ?>
                            <div class="invalid-feedback"><?= e($errors['email']) ?></div>
                            <?php endif; ?>
                            <?php if ($isEdit): ?>
                            <small class="text-muted">L'email ne peut pas être modifié.</small>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-5">
                            <label class="form-label fw-500">Téléphone</label>
                            <input type="text" name="telephone" class="form-control"
                                   value="<?= e($old['telephone'] ?? '') ?>"
                                   placeholder="+227 XX XX XX XX">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-500">Rôle <span class="text-danger">*</span></label>
                            <select name="role" class="form-select <?= isset($errors['role']) ? 'is-invalid' : '' ?>" required id="selectRole">
                                <option value="">-- Sélectionner --</option>
                                <option value="agent"       <?= ($old['role'] ?? '') === 'agent'       ? 'selected' : '' ?>>Agent de saisie</option>
                                <option value="admin"       <?= ($old['role'] ?? '') === 'admin'       ? 'selected' : '' ?>>Administrateur</option>
                                <?php if (isSuperAdmin()): ?>
                                <option value="super_admin" <?= ($old['role'] ?? '') === 'super_admin' ? 'selected' : '' ?>>Super Administrateur</option>
                                <?php endif; ?>
                            </select>
                            <?php if (isset($errors['role'])): ?>
                            <div class="invalid-feedback"><?= e($errors['role']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-6" id="wrapperRegion">
                            <label class="form-label fw-500">Région</label>
                            <select name="region_id" class="form-select">
                                <option value="">-- Aucune --</option>
                                <?php foreach ($regions as $r): ?>
                                <option value="<?= $r['id'] ?>" <?= ((int)($old['region_id'] ?? 0)) === (int)$r['id'] ? 'selected' : '' ?>>
                                    <?= e($r['nom']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Obligatoire pour les agents.</small>
                        </div>

                        <!-- Mot de passe -->
                        <div class="col-12">
                            <hr class="my-2">
                            <h6 class="text-muted small text-uppercase mb-3">
                                <?= $isEdit ? 'Changer le mot de passe (laisser vide pour conserver)' : 'Mot de passe' ?>
                            </h6>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-500">
                                Mot de passe <?= !$isEdit ? '<span class="text-danger">*</span>' : '' ?>
                            </label>
                            <div class="input-group">
                                <input type="password" name="password" id="pwdInput"
                                       class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                                       <?= !$isEdit ? 'required' : '' ?>
                                       minlength="8" placeholder="Min. 8 caractères">
                                <button type="button" class="btn btn-outline-secondary" id="togglePwd">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <?php if (isset($errors['password'])): ?>
                                <div class="invalid-feedback"><?= e($errors['password']) ?></div>
                                <?php endif; ?>
                            </div>
                            <div id="pwdStrength" class="mt-1"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-500">Confirmer le mot de passe</label>
                            <input type="password" name="password_confirm" id="pwdConfirmInput"
                                   class="form-control"
                                   minlength="8">
                            <div id="pwdMatch" class="mt-1 small"></div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-<?= $isEdit ? 'save' : 'person-plus' ?> me-1"></i>
                            <?= $isEdit ? 'Enregistrer les modifications' : 'Créer l\'utilisateur' ?>
                        </button>
                        <a href="<?= url('admin/utilisateurs') ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i>Annuler
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle mot de passe
document.getElementById('togglePwd')?.addEventListener('click', function () {
    const inp = document.getElementById('pwdInput');
    inp.type  = inp.type === 'password' ? 'text' : 'password';
    this.querySelector('i').className = inp.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
});

// Indicateur de force
document.getElementById('pwdInput')?.addEventListener('input', function () {
    const v = this.value;
    let score = 0;
    if (v.length >= 8) score++;
    if (/[A-Z]/.test(v)) score++;
    if (/[0-9]/.test(v)) score++;
    if (/[^A-Za-z0-9]/.test(v)) score++;
    const labels = ['', 'Faible', 'Moyen', 'Bon', 'Fort'];
    const colors = ['', 'danger', 'warning', 'info', 'success'];
    const el = document.getElementById('pwdStrength');
    el.innerHTML = v ? `<span class="text-${colors[score] || 'danger'}"><i class="bi bi-shield-${score >= 3 ? 'check' : 'x'} me-1"></i>${labels[score] || 'Très faible'}</span>` : '';
});

// Match confirmation
document.getElementById('pwdConfirmInput')?.addEventListener('input', function () {
    const pwd = document.getElementById('pwdInput').value;
    const el  = document.getElementById('pwdMatch');
    if (!this.value) { el.innerHTML = ''; return; }
    el.innerHTML = this.value === pwd
        ? '<span class="text-success"><i class="bi bi-check-circle me-1"></i>Correspondent</span>'
        : '<span class="text-danger"><i class="bi bi-x-circle me-1"></i>Ne correspondent pas</span>';
});

// Afficher/masquer la région selon le rôle
document.getElementById('selectRole')?.addEventListener('change', function () {
    const wrap = document.getElementById('wrapperRegion');
    wrap.style.display = (this.value === 'agent') ? '' : 'none';
});
// Init
const initRole = document.getElementById('selectRole')?.value;
if (initRole && initRole !== 'agent') {
    document.getElementById('wrapperRegion').style.display = 'none';
}
</script>
