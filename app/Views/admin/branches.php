<?php defined('EDAMO') or die('Accès direct interdit'); ?>

<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="bi bi-diagram-3 me-2 text-primary"></i>Branches d'activité</h6>
        <span class="badge bg-primary"><?= count($branches) ?></span>
    </div>
    <div class="card-body p-0">
        <?php if (empty($branches)): ?>
        <div class="text-center text-muted py-5">
            <i class="bi bi-diagram-3 fs-1 d-block mb-2"></i>Aucune branche définie.
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Code</th>
                        <th>Libellé</th>
                        <th>Description</th>
                        <th class="text-center">Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($branches as $b): ?>
                    <tr>
                        <td><span class="badge bg-primary fs-6"><?= e($b['code']) ?></span></td>
                        <td class="fw-500"><?= e($b['libelle']) ?></td>
                        <td class="text-muted small"><?= e($b['description'] ?? '—') ?></td>
                        <td class="text-center">
                            <?php if ($b['actif']): ?>
                            <span class="badge bg-success">Actif</span>
                            <?php else: ?>
                            <span class="badge bg-secondary">Inactif</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
    <div class="card-footer text-muted small">
        <i class="bi bi-info-circle me-1"></i>
        Les branches d'activité sont issues de la nomenclature officielle ANPE Niger.
        Contactez l'administrateur système pour les modifier.
    </div>
</div>
