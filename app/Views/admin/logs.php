<?php // Vue : Journaux d'audit ?>
<div class="page-header">
    <div>
        <h1><i class="bi bi-journal-text me-2 text-primary"></i>Journaux d'audit</h1>
        <p>Total : <strong><?= formatNumber($total) ?></strong> entrée(s)</p>
    </div>
</div>
<div class="filters-bar">
    <form method="GET" action="/admin/logs">
        <input type="text" name="q" value="<?= e($filters['search']) ?>" class="form-control form-control-sm" placeholder="Rechercher...">
        <input type="text" name="action" value="<?= e($filters['action']) ?>" class="form-control form-control-sm" placeholder="Action...">
        <input type="date" name="date_from" value="<?= e($filters['dateFrom']) ?>" class="form-control form-control-sm">
        <input type="date" name="date_to" value="<?= e($filters['dateTo']) ?>" class="form-control form-control-sm">
        <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search me-1"></i>Filtrer</button>
        <a href="/admin/logs" class="btn btn-outline-secondary btn-sm">Reset</a>
    </form>
</div>
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead><tr><th>Date/Heure</th><th>Utilisateur</th><th>Action</th><th>Ressource</th><th>IP</th><th>Statut</th></tr></thead>
                <tbody>
                <?php foreach ($logs as $l): ?>
                <tr>
                    <td><small><?= formatDateTime($l['created_at']) ?></small></td>
                    <td>
                        <?php if ($l['nom']): ?>
                        <small><?= e($l['prenom'] . ' ' . $l['nom']) ?><br><span class="text-muted"><?= e($l['email'] ?? '') ?></span></small>
                        <?php else: ?><small class="text-muted">Système</small><?php endif; ?>
                    </td>
                    <td><code style="font-size:.78rem"><?= e($l['action']) ?></code></td>
                    <td><small><?= e($l['ressource']) ?> <?= $l['ressource_id'] ? '#'.$l['ressource_id'] : '' ?></small></td>
                    <td><small class="text-muted"><?= e($l['ip_address']) ?></small></td>
                    <td>
                        <?php if ($l['statut'] === 'success'): ?>
                        <span class="badge bg-success" style="font-size:.65rem">✓</span>
                        <?php else: ?>
                        <span class="badge bg-danger" style="font-size:.65rem">✗</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; if (empty($logs)): ?>
                <tr><td colspan="6" class="text-center py-4 text-muted">Aucune entrée</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php if ($pagination['total_pages'] > 1): ?>
<nav class="mt-3"><ul class="pagination justify-content-center">
    <?php if ($pagination['has_prev']): ?><li class="page-item"><a class="page-link" href="?page=<?= $pagination['prev_page'] ?>">&laquo;</a></li><?php endif; ?>
    <li class="page-item disabled"><span class="page-link"><?= $pagination['current_page'] ?>/<?= $pagination['total_pages'] ?></span></li>
    <?php if ($pagination['has_next']): ?><li class="page-item"><a class="page-link" href="?page=<?= $pagination['next_page'] ?>">&raquo;</a></li><?php endif; ?>
</ul></nav>
<?php endif; ?>
