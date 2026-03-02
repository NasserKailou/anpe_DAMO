<?php defined('EDAMO') or die('Accès direct interdit'); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div></div>
    <a href="<?= url('admin/campagne/nouvelle') ?>" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i>Nouvelle campagne
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-header">
        <h6 class="mb-0"><i class="bi bi-calendar3 me-2 text-primary"></i>Campagnes DAMO</h6>
    </div>
    <div class="card-body p-0">
        <?php if (empty($campagnes)): ?>
        <div class="text-center text-muted py-5">
            <i class="bi bi-calendar-x fs-1 d-block mb-2"></i>
            Aucune campagne créée.
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Année</th>
                        <th>Libellé</th>
                        <th>Période</th>
                        <th class="text-center">Déclarations</th>
                        <th class="text-center">Statut</th>
                        <th>Créée par</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($campagnes as $c): ?>
                    <tr class="<?= $c['actif'] ? 'table-primary' : '' ?>">
                        <td class="fw-bold fs-5"><?= e($c['annee']) ?></td>
                        <td><?= e($c['libelle']) ?></td>
                        <td class="text-muted small">
                            <?= formatDate($c['date_debut']) ?> → <?= formatDate($c['date_fin']) ?>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-secondary"><?= number_format($c['nb_declarations']) ?></span>
                        </td>
                        <td class="text-center">
                            <?php if ($c['actif']): ?>
                            <span class="badge bg-success"><i class="bi bi-lightning-fill me-1"></i>Active</span>
                            <?php else: ?>
                            <span class="badge bg-light text-dark">Clôturée</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-muted small"><?= e($c['createur_nom'] ?? '—') ?></td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="<?= url('admin/campagne/' . $c['id'] . '/modifier') ?>"
                                   class="btn btn-outline-primary" title="Modifier"><i class="bi bi-pencil"></i></a>
                                <?php if ($c['actif'] && $c['nb_declarations'] > 0): ?>
                                <a href="<?= url('admin/campagne/' . $c['id'] . '/rappels') ?>"
                                   class="btn btn-outline-warning" title="Envoyer rappels"
                                   onclick="return confirm('Envoyer des rappels aux agents avec brouillons ?')">
                                    <i class="bi bi-bell"></i>
                                </a>
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
</div>
