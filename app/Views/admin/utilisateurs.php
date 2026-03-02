<?php defined('EDAMO') or die('Accès direct interdit'); ?>

<!-- Barre d'outils -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div></div>
    <div class="d-flex gap-2">
        <a href="<?= url('admin/utilisateur/nouveau') ?>" class="btn btn-primary">
            <i class="bi bi-person-plus me-1"></i>Nouvel utilisateur
        </a>
        <a href="<?= url('admin/import/entreprises') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-upload me-1"></i>Import CSV
        </a>
    </div>
</div>

<!-- Filtres -->
<div class="card shadow-sm mb-4">
    <div class="card-body py-3">
        <form method="GET" action="<?= url('admin/utilisateurs') ?>" class="row g-2 align-items-end">
            <div class="col-md-4">
                <input type="text" name="q" class="form-control form-control-sm"
                       placeholder="Nom, prénom, email…" value="<?= e($filters['search']) ?>">
            </div>
            <div class="col-md-2">
                <select name="role" class="form-select form-select-sm">
                    <option value="">Tous les rôles</option>
                    <option value="admin"       <?= $filters['role'] === 'admin'       ? 'selected' : '' ?>>Admin</option>
                    <option value="agent"       <?= $filters['role'] === 'agent'       ? 'selected' : '' ?>>Agent</option>
                    <option value="super_admin" <?= $filters['role'] === 'super_admin' ? 'selected' : '' ?>>Super Admin</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="region" class="form-select form-select-sm">
                    <option value="">Toutes les régions</option>
                    <?php foreach ($regions as $r): ?>
                    <option value="<?= $r['id'] ?>" <?= (int)$filters['region'] === (int)$r['id'] ? 'selected' : '' ?>>
                        <?= e($r['nom']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="bi bi-search me-1"></i>Filtrer
                </button>
                <a href="<?= url('admin/utilisateurs') ?>" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x-circle me-1"></i>Réinitialiser
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Liste -->
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="bi bi-people me-2 text-primary"></i>
            Utilisateurs
            <span class="badge bg-primary ms-1"><?= number_format($total) ?></span>
        </h6>
    </div>
    <div class="card-body p-0">
        <?php if (empty($utilisateurs)): ?>
        <div class="text-center text-muted py-5">
            <i class="bi bi-person-x fs-1 d-block mb-2"></i>
            Aucun utilisateur trouvé.
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Utilisateur</th>
                        <th>Rôle</th>
                        <th>Région</th>
                        <th class="text-center">Déclarations</th>
                        <th>Dernière connexion</th>
                        <th class="text-center">Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($utilisateurs as $u): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar-circle avatar-sm bg-primary text-white">
                                    <?= strtoupper(substr($u['prenom'] ?? 'U', 0, 1) . substr($u['nom'] ?? '', 0, 1)) ?>
                                </div>
                                <div>
                                    <div class="fw-500"><?= e(($u['prenom'] ?? '') . ' ' . ($u['nom'] ?? '')) ?></div>
                                    <small class="text-muted"><?= e($u['email']) ?></small>
                                    <?php if ($u['telephone']): ?>
                                    <br><small class="text-muted"><i class="bi bi-phone me-1"></i><?= e($u['telephone']) ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td>
                            <?= match($u['role']) {
                                'super_admin' => '<span class="badge bg-danger"><i class="bi bi-shield-fill me-1"></i>Super Admin</span>',
                                'admin'       => '<span class="badge bg-primary"><i class="bi bi-person-gear me-1"></i>Admin</span>',
                                'agent'       => '<span class="badge bg-info"><i class="bi bi-person-badge me-1"></i>Agent</span>',
                                default       => '<span class="badge bg-secondary">' . e($u['role']) . '</span>',
                            } ?>
                        </td>
                        <td><?= e($u['region_nom'] ?? '—') ?></td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark border"><?= $u['nb_declarations'] ?></span>
                        </td>
                        <td class="text-muted small">
                            <?= $u['derniere_connexion'] ? formatDateTime($u['derniere_connexion']) : 'Jamais' ?>
                        </td>
                        <td class="text-center">
                            <?php if ($u['actif']): ?>
                            <span class="badge bg-success">Actif</span>
                            <?php else: ?>
                            <span class="badge bg-secondary">Inactif</span>
                            <?php endif; ?>
                            <?php if (!$u['email_verifie']): ?>
                            <br><span class="badge bg-warning text-dark small">Email non vérifié</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="<?= url('admin/utilisateur/' . $u['id'] . '/modifier') ?>"
                                   class="btn btn-outline-primary" title="Modifier">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <?php if ($u['role'] !== ROLE_SUPER_ADMIN): ?>
                                <button type="button" class="btn btn-outline-<?= $u['actif'] ? 'warning' : 'success' ?> btn-toggle-user"
                                        data-id="<?= $u['id'] ?>"
                                        data-actif="<?= $u['actif'] ? '1' : '0' ?>"
                                        title="<?= $u['actif'] ? 'Désactiver' : 'Activer' ?>">
                                    <i class="bi bi-<?= $u['actif'] ? 'pause-circle' : 'play-circle' ?>"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($pagination['total_pages'] > 1): ?>
    <div class="card-footer d-flex justify-content-between align-items-center">
        <small class="text-muted">Page <?= $pagination['page'] ?> / <?= $pagination['total_pages'] ?></small>
        <nav><ul class="pagination pagination-sm mb-0">
            <?php if ($pagination['page'] > 1): ?>
            <li class="page-item"><a class="page-link" href="?page=<?= $pagination['page'] - 1 ?>&<?= http_build_query(array_filter($filters)) ?>"><i class="bi bi-chevron-left"></i></a></li>
            <?php endif; ?>
            <?php for ($p = max(1, $pagination['page'] - 2); $p <= min($pagination['total_pages'], $pagination['page'] + 2); $p++): ?>
            <li class="page-item <?= $p === $pagination['page'] ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $p ?>&<?= http_build_query(array_filter($filters)) ?>"><?= $p ?></a>
            </li>
            <?php endfor; ?>
            <?php if ($pagination['page'] < $pagination['total_pages']): ?>
            <li class="page-item"><a class="page-link" href="?page=<?= $pagination['page'] + 1 ?>&<?= http_build_query(array_filter($filters)) ?>"><i class="bi bi-chevron-right"></i></a></li>
            <?php endif; ?>
        </ul></nav>
    </div>
    <?php endif; ?>
</div>

<script>
document.querySelectorAll('.btn-toggle-user').forEach(btn => {
    btn.addEventListener('click', function () {
        const id    = this.dataset.id;
        const actif = this.dataset.actif === '1';
        const msg   = actif ? 'Désactiver cet utilisateur ?' : 'Activer cet utilisateur ?';
        if (!confirm(msg)) return;
        fetch(window.APP_BASE + '/admin/utilisateur/' + id + '/toggle', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': window.CSRF_TOKEN },
            body: JSON.stringify({ _csrf: window.CSRF_TOKEN })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) location.reload();
            else alert(data.message || 'Erreur');
        });
    });
});
</script>
