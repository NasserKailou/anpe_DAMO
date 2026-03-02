<?php defined('EDAMO') or die('Accès direct interdit'); ?>
<?php $u = $userDetails ?? currentUser(); ?>

<div class="row g-4">
    <!-- Colonne profil -->
    <div class="col-lg-4">
        <!-- Carte identité -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body text-center py-4">
                <div class="avatar-xl mx-auto mb-3"
                     style="width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg,#0d6efd,#6610f2);
                            display:flex;align-items:center;justify-content:center;font-size:2rem;color:white;font-weight:700;">
                    <?= strtoupper(substr($u['prenom'] ?? 'U', 0, 1) . substr($u['nom'] ?? '', 0, 1)) ?>
                </div>
                <h5 class="fw-bold mb-1"><?= e(($u['prenom'] ?? '') . ' ' . ($u['nom'] ?? '')) ?></h5>
                <div class="mb-2">
                    <span class="badge bg-<?= match($u['role'] ?? '') {
                        'super_admin' => 'danger',
                        'admin'       => 'warning text-dark',
                        'agent'       => 'primary',
                        default       => 'secondary'
                    } ?> px-3 py-1">
                        <?= match($u['role'] ?? '') {
                            'super_admin' => 'Super Administrateur',
                            'admin'       => 'Administrateur',
                            'agent'       => 'Agent ANPE',
                            default       => 'Utilisateur'
                        } ?>
                    </span>
                </div>
                <p class="text-muted small mb-0">
                    <i class="bi bi-envelope me-1"></i><?= e($u['email'] ?? '') ?>
                </p>
                <?php if (!empty($u['region_nom'])): ?>
                <p class="text-muted small mt-1 mb-0">
                    <i class="bi bi-geo-alt me-1"></i>Région : <?= e($u['region_nom']) ?>
                </p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Informations compte -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-info-circle text-primary me-2"></i>Informations du compte</h6>
            </div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex justify-content-between align-items-center small py-2">
                    <span class="text-muted">Statut</span>
                    <span class="badge bg-<?= ($u['actif'] ?? false) ? 'success' : 'danger' ?>">
                        <?= ($u['actif'] ?? false) ? 'Actif' : 'Inactif' ?>
                    </span>
                </li>
                <?php if (!empty($u['created_at'])): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center small py-2">
                    <span class="text-muted">Membre depuis</span>
                    <span><?= formatDate($u['created_at']) ?></span>
                </li>
                <?php endif; ?>
                <?php if (!empty($u['derniere_connexion'])): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center small py-2">
                    <span class="text-muted">Dernière connexion</span>
                    <span><?= formatDateTime($u['derniere_connexion']) ?></span>
                </li>
                <?php endif; ?>
                <li class="list-group-item d-flex justify-content-between align-items-center small py-2">
                    <span class="text-muted">Téléphone</span>
                    <span><?= e($u['telephone'] ?? '—') ?></span>
                </li>
            </ul>
        </div>
    </div>

    <!-- Colonne formulaires -->
    <div class="col-lg-8">
        <!-- Flash messages -->
        <?php if ($flash = flash('success')): ?>
        <div class="alert alert-success border-0 shadow-sm"><i class="bi bi-check-circle me-2"></i><?= $flash ?></div>
        <?php endif; ?>
        <?php if ($flash = flash('error')): ?>
        <div class="alert alert-danger border-0 shadow-sm"><i class="bi bi-x-circle me-2"></i><?= $flash ?></div>
        <?php endif; ?>

        <!-- Formulaire infos personnelles -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-person-gear text-primary me-2"></i>Informations personnelles
                </h6>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= url('profil/update') ?>">
                    <?= csrfField() ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Prénom <span class="text-danger">*</span></label>
                            <input type="text" name="prenom" class="form-control"
                                   value="<?= e($u['prenom'] ?? '') ?>" required
                                   placeholder="Votre prénom">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Nom <span class="text-danger">*</span></label>
                            <input type="text" name="nom" class="form-control"
                                   value="<?= e($u['nom'] ?? '') ?>" required
                                   placeholder="Votre nom">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Adresse email</label>
                            <input type="email" class="form-control bg-light"
                                   value="<?= e($u['email'] ?? '') ?>" readonly>
                            <div class="form-text">L'adresse email ne peut pas être modifiée ici.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Téléphone</label>
                            <input type="text" name="telephone" class="form-control"
                                   value="<?= e($u['telephone'] ?? '') ?>"
                                   placeholder="+227 XX XX XX XX">
                        </div>
                    </div>
                    <div class="mt-4 text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-1"></i>Enregistrer les modifications
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Formulaire changement de mot de passe -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-shield-lock text-warning me-2"></i>Changer le mot de passe
                </h6>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= url('profil/password') ?>" id="formPassword">
                    <?= csrfField() ?>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-medium">Mot de passe actuel <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" name="current_password" class="form-control"
                                       id="currentPwd" required placeholder="Votre mot de passe actuel">
                                <button class="btn btn-outline-secondary" type="button"
                                        onclick="togglePwd('currentPwd')">
                                    <i class="bi bi-eye" id="eye_currentPwd"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Nouveau mot de passe <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" name="password" class="form-control"
                                       id="newPwd" required minlength="8" placeholder="Min. 8 caractères"
                                       oninput="checkStrength(this.value)">
                                <button class="btn btn-outline-secondary" type="button"
                                        onclick="togglePwd('newPwd')">
                                    <i class="bi bi-eye" id="eye_newPwd"></i>
                                </button>
                            </div>
                            <!-- Indicateur de force -->
                            <div class="progress mt-2" style="height:5px;" id="pwdStrengthBar">
                                <div class="progress-bar" id="strengthBar" style="width:0%;"></div>
                            </div>
                            <small id="strengthText" class="text-muted"></small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Confirmer le nouveau mot de passe <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" name="password_confirm" class="form-control"
                                       id="confirmPwd" required placeholder="Répéter le mot de passe"
                                       oninput="checkMatch()">
                                <button class="btn btn-outline-secondary" type="button"
                                        onclick="togglePwd('confirmPwd')">
                                    <i class="bi bi-eye" id="eye_confirmPwd"></i>
                                </button>
                            </div>
                            <small id="matchText"></small>
                        </div>
                    </div>

                    <div class="alert alert-light border mt-3 small">
                        <strong>Règles de sécurité :</strong>
                        <ul class="mb-0 ps-3 mt-1">
                            <li id="rule_len" class="text-muted">Au moins 8 caractères</li>
                            <li id="rule_upper" class="text-muted">Au moins une majuscule</li>
                            <li id="rule_lower" class="text-muted">Au moins une minuscule</li>
                            <li id="rule_num" class="text-muted">Au moins un chiffre</li>
                        </ul>
                    </div>

                    <div class="mt-3 text-end">
                        <button type="submit" class="btn btn-warning text-dark fw-semibold">
                            <i class="bi bi-shield-check me-1"></i>Changer le mot de passe
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function togglePwd(id) {
    const input = document.getElementById(id);
    const eye   = document.getElementById('eye_' + id);
    if (input.type === 'password') {
        input.type = 'text';
        eye.className = 'bi bi-eye-slash';
    } else {
        input.type = 'password';
        eye.className = 'bi bi-eye';
    }
}

function checkStrength(val) {
    const bar  = document.getElementById('strengthBar');
    const text = document.getElementById('strengthText');
    const rules = {
        len:   val.length >= 8,
        upper: /[A-Z]/.test(val),
        lower: /[a-z]/.test(val),
        num:   /[0-9]/.test(val),
    };
    ['len','upper','lower','num'].forEach(r => {
        const li = document.getElementById('rule_' + r);
        if (li) {
            li.className = rules[r] ? 'text-success' : 'text-muted';
            li.style.textDecoration = rules[r] ? 'line-through' : '';
        }
    });
    const score = Object.values(rules).filter(Boolean).length;
    const pct   = (score / 4) * 100;
    bar.style.width = pct + '%';
    bar.className = 'progress-bar ' + (['', 'bg-danger', 'bg-warning', 'bg-info', 'bg-success'][score] || '');
    const labels  = ['', 'Très faible', 'Faible', 'Moyen', 'Fort'];
    text.textContent = labels[score] || '';
    text.className = score >= 3 ? 'text-success small' : 'text-warning small';
}

function checkMatch() {
    const p1 = document.getElementById('newPwd').value;
    const p2 = document.getElementById('confirmPwd').value;
    const el = document.getElementById('matchText');
    if (p2.length === 0) { el.textContent = ''; return; }
    if (p1 === p2) {
        el.textContent = '✓ Les mots de passe correspondent';
        el.className = 'text-success small';
    } else {
        el.textContent = '✗ Les mots de passe ne correspondent pas';
        el.className = 'text-danger small';
    }
}
</script>
