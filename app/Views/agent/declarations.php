<?php defined('EDAMO') or die('Accès direct interdit'); ?>

<!-- Barre d'outils -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div></div>
    <div class="d-flex gap-2">
        <?php if ($campagne ?? false): ?>
        <a href="<?= url('agent/declaration/nouvelle') ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i>Nouvelle déclaration
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- Filtres -->
<div class="card shadow-sm mb-4">
    <div class="card-body py-3">
        <form method="GET" action="<?= url('agent/declarations') ?>" class="row g-2 align-items-end">
            <div class="col-md-4">
                <input type="text" name="q" class="form-control form-control-sm"
                       placeholder="Rechercher (entreprise, CNSS…)" value="<?= e($filters['search'] ?? '') ?>">
            </div>
            <div class="col-md-3">
                <select name="statut" class="form-select form-select-sm">
                    <option value="">Tous les statuts</option>
                    <option value="brouillon" <?= ($filters['statut'] ?? '') === 'brouillon' ? 'selected' : '' ?>>Brouillon</option>
                    <option value="soumise"   <?= ($filters['statut'] ?? '') === 'soumise'   ? 'selected' : '' ?>>Soumise</option>
                    <option value="validee"   <?= ($filters['statut'] ?? '') === 'validee'   ? 'selected' : '' ?>>Validée</option>
                    <option value="rejetee"   <?= ($filters['statut'] ?? '') === 'rejetee'   ? 'selected' : '' ?>>Rejetée</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-search me-1"></i>Filtrer</button>
                <a href="<?= url('agent/declarations') ?>" class="btn btn-sm btn-outline-secondary">Réinitialiser</a>
            </div>
        </form>
    </div>
</div>

<!-- Liste -->
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="bi bi-file-earmark-text me-2 text-primary"></i>
            Mes déclarations
            <span class="badge bg-primary ms-1"><?= number_format($total ?? 0) ?></span>
        </h6>
    </div>
    <div class="card-body p-0">
        <?php if (empty($declarations)): ?>
        <div class="text-center text-muted py-5">
            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
            Aucune déclaration.
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Code</th>
                        <th>Entreprise</th>
                        <th>Campagne</th>
                        <th>Statut</th>
                        <th>Mis à jour</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($declarations as $d): ?>
                    <tr <?= $d['statut'] === 'rejetee' ? 'class="table-danger"' : '' ?>>
                        <td><code><?= e($d['code_questionnaire'] ?? '—') ?></code></td>
                        <td>
                            <div class="fw-500"><?= e($d['raison_sociale']) ?></div>
                            <small class="text-muted"><?= e($d['numero_cnss'] ?? '') ?></small>
                        </td>
                        <td><?= e($d['annee']) ?></td>
                        <td><?= match($d['statut']) {
                            'brouillon' => '<span class="badge bg-secondary">Brouillon</span>',
                            'soumise'   => '<span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i>En attente</span>',
                            'validee'   => '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Validée</span>',
                            'rejetee'   => '<span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Rejetée</span>',
                            'corrigee'  => '<span class="badge bg-info">Corrigée</span>',
                            default     => '<span class="badge bg-light text-dark">' . e($d['statut']) . '</span>',
                        } ?></td>
                        <td class="text-muted small"><?= formatDateTime($d['updated_at']) ?></td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="<?= url('agent/declaration/' . $d['id'] . '/apercu') ?>"
                                   class="btn btn-outline-secondary" title="Aperçu"><i class="bi bi-eye"></i></a>
                                <?php if (in_array($d['statut'], ['brouillon', 'corrigee'])): ?>
                                <a href="<?= url('agent/declaration/' . $d['id'] . '/saisie') ?>"
                                   class="btn btn-outline-primary" title="Saisir / Modifier"><i class="bi bi-pencil"></i></a>
                                <a href="<?= url('agent/declaration/' . $d['id'] . '/import-csv') ?>"
                                   class="btn btn-outline-warning" title="Import CSV"><i class="bi bi-file-earmark-spreadsheet"></i></a>
                                <button type="button" class="btn btn-outline-success btn-soumettre"
                                        data-id="<?= $d['id'] ?>" title="Soumettre">
                                    <i class="bi bi-send"></i>
                                </button>
                                <?php elseif ($d['statut'] === 'rejetee'): ?>
                                <a href="<?= url('agent/declaration/' . $d['id'] . '/saisie') ?>"
                                   class="btn btn-outline-primary" title="Corriger"><i class="bi bi-pencil-square"></i></a>
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

<!-- Modal soumettre -->
<div class="modal fade" id="modalSoumettre" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header"><h6 class="modal-title text-success"><i class="bi bi-send me-2"></i>Soumettre</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="formSoumettre" method="POST">
                <?= csrfField() ?>
                <div class="modal-body small">Soumettre cette déclaration pour validation ? Elle ne pourra plus être modifiée.</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-send me-1"></i>Soumettre</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
document.querySelectorAll('.btn-soumettre').forEach(btn => {
    btn.addEventListener('click', function () {
        document.getElementById('formSoumettre').action = window.APP_BASE + '/agent/declaration/' + this.dataset.id + '/soumettre';
        new bootstrap.Modal(document.getElementById('modalSoumettre')).show();
    });
});
</script>
