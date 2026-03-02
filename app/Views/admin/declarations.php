<?php defined('EDAMO') or die('Accès direct interdit'); ?>

<!-- Filtres -->
<div class="card shadow-sm mb-4">
    <div class="card-body py-3">
        <form method="GET" action="<?= url('admin/declarations') ?>" class="row g-2 align-items-end">
            <div class="col-md-3">
                <input type="text" name="q" class="form-control form-control-sm"
                       placeholder="Rechercher (entreprise, CNSS…)" value="<?= e($filters['search']) ?>">
            </div>
            <div class="col-md-2">
                <select name="statut" class="form-select form-select-sm">
                    <option value="">Tous les statuts</option>
                    <option value="brouillon" <?= $filters['statut'] === 'brouillon' ? 'selected' : '' ?>>Brouillon</option>
                    <option value="soumise"   <?= $filters['statut'] === 'soumise'   ? 'selected' : '' ?>>Soumise</option>
                    <option value="validee"   <?= $filters['statut'] === 'validee'   ? 'selected' : '' ?>>Validée</option>
                    <option value="rejetee"   <?= $filters['statut'] === 'rejetee'   ? 'selected' : '' ?>>Rejetée</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="region" class="form-select form-select-sm">
                    <option value="">Toutes les régions</option>
                    <?php foreach ($regions as $r): ?>
                    <option value="<?= $r['id'] ?>" <?= (int)$filters['region'] === (int)$r['id'] ? 'selected' : '' ?>>
                        <?= e($r['nom']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="campagne" class="form-select form-select-sm">
                    <option value="">Toutes les campagnes</option>
                    <?php foreach ($campagnes as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= (int)$filters['campagne'] === (int)$c['id'] ? 'selected' : '' ?>>
                        <?= e($c['annee'] . ' — ' . $c['libelle']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="bi bi-search me-1"></i>Filtrer
                </button>
                <a href="<?= url('admin/declarations') ?>" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x-circle me-1"></i>Réinitialiser
                </a>
                <a href="<?= url('admin/export/declarations') ?>" class="btn btn-sm btn-outline-success ms-auto">
                    <i class="bi bi-file-earmark-excel me-1"></i>CSV
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Résultats -->
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="bi bi-file-earmark-text me-2 text-primary"></i>
            Déclarations
            <span class="badge bg-primary ms-1"><?= number_format($total) ?></span>
        </h6>
    </div>
    <div class="card-body p-0">
        <?php if (empty($declarations)): ?>
        <div class="text-center text-muted py-5">
            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
            Aucune déclaration trouvée.
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Code questionnaire</th>
                        <th>Entreprise</th>
                        <th>Région</th>
                        <th>Agent</th>
                        <th>Campagne</th>
                        <th>Statut</th>
                        <th>Dernière modif.</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($declarations as $d): ?>
                    <tr>
                        <td><code><?= e($d['code_questionnaire']) ?></code></td>
                        <td>
                            <div class="fw-500"><?= e($d['raison_sociale']) ?></div>
                            <?php if ($d['numero_cnss']): ?>
                            <small class="text-muted"><?= e($d['numero_cnss']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?= e($d['region_nom']) ?></td>
                        <td><?= e(($d['agent_prenom'] ?? '') . ' ' . ($d['agent_nom'] ?? '')) ?></td>
                        <td><?= e($d['annee']) ?></td>
                        <td>
                            <?= match($d['statut']) {
                                'brouillon' => '<span class="badge bg-secondary">Brouillon</span>',
                                'soumise'   => '<span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i>Soumise</span>',
                                'validee'   => '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Validée</span>',
                                'rejetee'   => '<span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Rejetée</span>',
                                'corrigee'  => '<span class="badge bg-info">Corrigée</span>',
                                default     => '<span class="badge bg-light text-dark">' . e($d['statut']) . '</span>',
                            } ?>
                        </td>
                        <td class="text-muted small"><?= formatDateTime($d['updated_at']) ?></td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="<?= url('admin/declaration/' . $d['id']) ?>"
                                   class="btn btn-outline-primary" title="Voir">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <?php if ($d['statut'] === 'soumise'): ?>
                                <button type="button" class="btn btn-outline-success btn-valider"
                                        data-id="<?= $d['id'] ?>"
                                        data-code="<?= e($d['code_questionnaire']) ?>"
                                        title="Valider">
                                    <i class="bi bi-check-lg"></i>
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-rejeter"
                                        data-id="<?= $d['id'] ?>"
                                        data-code="<?= e($d['code_questionnaire']) ?>"
                                        title="Rejeter">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                                <?php endif; ?>
                                <a href="<?= url('admin/declaration/' . $d['id'] . '/export-pdf') ?>"
                                   class="btn btn-outline-secondary" title="Exporter HTML/PDF">
                                    <i class="bi bi-file-earmark-arrow-down"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($pagination['total_pages'] > 1): ?>
    <div class="card-footer d-flex justify-content-between align-items-center">
        <small class="text-muted">
            Page <?= $pagination['page'] ?> / <?= $pagination['total_pages'] ?>
            — <?= number_format($total) ?> résultat(s)
        </small>
        <nav>
            <ul class="pagination pagination-sm mb-0">
                <?php if ($pagination['page'] > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $pagination['page'] - 1 ?>&<?= http_build_query(array_filter($filters)) ?>">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                </li>
                <?php endif; ?>
                <?php for ($p = max(1, $pagination['page'] - 2); $p <= min($pagination['total_pages'], $pagination['page'] + 2); $p++): ?>
                <li class="page-item <?= $p === $pagination['page'] ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $p ?>&<?= http_build_query(array_filter($filters)) ?>">
                        <?= $p ?>
                    </a>
                </li>
                <?php endfor; ?>
                <?php if ($pagination['page'] < $pagination['total_pages']): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $pagination['page'] + 1 ?>&<?= http_build_query(array_filter($filters)) ?>">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>

<!-- Modal Valider -->
<div class="modal fade" id="modalValider" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-check-circle me-2 text-success"></i>Valider la déclaration</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formValider" method="POST">
                <?= csrfField() ?>
                <div class="modal-body">
                    <p>Confirmer la validation de la déclaration <strong id="codeValider"></strong> ?</p>
                    <div class="mb-3">
                        <label class="form-label">Observations (optionnel)</label>
                        <textarea name="observations" class="form-control" rows="3"
                                  placeholder="Commentaire de validation…"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-lg me-1"></i>Valider
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Rejeter -->
<div class="modal fade" id="modalRejeter" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-x-circle me-2 text-danger"></i>Rejeter la déclaration</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formRejeter" method="POST">
                <?= csrfField() ?>
                <div class="modal-body">
                    <p>Rejeter la déclaration <strong id="codeRejeter"></strong> ?</p>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Motif de rejet <span class="text-danger">*</span></label>
                        <textarea name="motif_rejet" class="form-control" rows="3"
                                  placeholder="Expliquer le motif de rejet…" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-x-lg me-1"></i>Rejeter
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const base = window.APP_BASE || '';

    // Boutons Valider
    document.querySelectorAll('.btn-valider').forEach(btn => {
        btn.addEventListener('click', function () {
            const id   = this.dataset.id;
            const code = this.dataset.code;
            document.getElementById('codeValider').textContent = code;
            document.getElementById('formValider').action = base + '/admin/declaration/' + id + '/valider';
            new bootstrap.Modal(document.getElementById('modalValider')).show();
        });
    });

    // Boutons Rejeter
    document.querySelectorAll('.btn-rejeter').forEach(btn => {
        btn.addEventListener('click', function () {
            const id   = this.dataset.id;
            const code = this.dataset.code;
            document.getElementById('codeRejeter').textContent = code;
            document.getElementById('formRejeter').action = base + '/admin/declaration/' + id + '/rejeter';
            new bootstrap.Modal(document.getElementById('modalRejeter')).show();
        });
    });
});
</script>
