<?php defined('EDAMO') or die('Accès direct interdit'); ?>

<!-- Filtres logs -->
<div class="card shadow-sm mb-4">
    <div class="card-body py-3">
        <form method="GET" action="<?= url('admin/logs') ?>" class="row g-2 align-items-end">
            <div class="col-md-3">
                <input type="text" name="q" class="form-control form-control-sm"
                       placeholder="Rechercher (email, action…)" value="<?= e($filters['search'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <select name="action" class="form-select form-select-sm">
                    <option value="">Toutes les actions</option>
                    <?php foreach ($actions as $a): ?>
                    <option value="<?= e($a) ?>" <?= ($filters['action'] ?? '') === $a ? 'selected' : '' ?>>
                        <?= e($a) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="date_from" class="form-control form-control-sm"
                       value="<?= e($filters['date_from'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <input type="date" name="date_to" class="form-control form-control-sm"
                       value="<?= e($filters['date_to'] ?? '') ?>">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="bi bi-search me-1"></i>Filtrer
                </button>
                <a href="<?= url('admin/logs') ?>" class="btn btn-sm btn-outline-secondary">Réinitialiser</a>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="bi bi-journal-text me-2 text-primary"></i>
            Journaux d'audit
            <span class="badge bg-primary ms-1"><?= number_format($total) ?></span>
        </h6>
    </div>
    <div class="card-body p-0">
        <?php if (empty($logs)): ?>
        <div class="text-center text-muted py-5">
            <i class="bi bi-journal fs-1 d-block mb-2"></i>Aucun log trouvé.
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 table-sm">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Utilisateur</th>
                        <th>Action</th>
                        <th>Ressource</th>
                        <th>Statut</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                    <tr>
                        <td class="text-muted small"><?= formatDateTime($log['created_at']) ?></td>
                        <td>
                            <?php if ($log['email']): ?>
                            <div class="fw-500 small"><?= e(($log['prenom'] ?? '') . ' ' . ($log['nom'] ?? '')) ?></div>
                            <small class="text-muted"><?= e($log['email']) ?></small>
                            <?php else: ?>
                            <span class="text-muted small">Système</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <code class="small text-<?= match(true) {
                                str_contains($log['action'], 'login')    => 'success',
                                str_contains($log['action'], 'delete')   => 'danger',
                                str_contains($log['action'], 'creat')    => 'primary',
                                str_contains($log['action'], 'update')   => 'warning',
                                default                                  => 'secondary'
                            } ?>"><?= e($log['action']) ?></code>
                        </td>
                        <td class="small">
                            <?= e($log['ressource'] ?? '') ?>
                            <?php if ($log['ressource_id']): ?>
                            <span class="text-muted"> #<?= $log['ressource_id'] ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (($log['statut'] ?? 'success') === 'success'): ?>
                            <span class="badge bg-success-subtle text-success border">OK</span>
                            <?php else: ?>
                            <span class="badge bg-danger-subtle text-danger border">Échec</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-muted small"><?= e($log['ip_address'] ?? '—') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <?php if (($pagination['total_pages'] ?? 1) > 1): ?>
    <div class="card-footer d-flex justify-content-between align-items-center">
        <small class="text-muted">Page <?= $pagination['page'] ?> / <?= $pagination['total_pages'] ?></small>
        <nav><ul class="pagination pagination-sm mb-0">
            <?php if ($pagination['page'] > 1): ?>
            <li class="page-item"><a class="page-link" href="?page=<?= $pagination['page'] - 1 ?>&<?= http_build_query(array_filter($filters ?? [])) ?>"><i class="bi bi-chevron-left"></i></a></li>
            <?php endif; ?>
            <?php for ($p = max(1, $pagination['page'] - 2); $p <= min($pagination['total_pages'], $pagination['page'] + 2); $p++): ?>
            <li class="page-item <?= $p === $pagination['page'] ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $p ?>&<?= http_build_query(array_filter($filters ?? [])) ?>"><?= $p ?></a></li>
            <?php endfor; ?>
            <?php if ($pagination['page'] < $pagination['total_pages']): ?>
            <li class="page-item"><a class="page-link" href="?page=<?= $pagination['page'] + 1 ?>&<?= http_build_query(array_filter($filters ?? [])) ?>"><i class="bi bi-chevron-right"></i></a></li>
            <?php endif; ?>
        </ul></nav>
    </div>
    <?php endif; ?>
</div>
