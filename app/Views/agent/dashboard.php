<?php defined('EDAMO') or die('Accès direct interdit'); ?>

<!-- Campagne active -->
<?php if ($campagne): ?>
<div class="alert alert-info d-flex align-items-center gap-3 mb-4">
    <i class="bi bi-calendar-check fs-3 text-info"></i>
    <div class="flex-1">
        <strong>Campagne <?= e($campagne['annee']) ?></strong> — <?= e($campagne['libelle']) ?>
        <div class="small mt-1">
            Clôture : <strong><?= formatDate($campagne['date_fin']) ?></strong>
            <?php
            $joursRestants = (int) ceil((strtotime($campagne['date_fin']) - time()) / 86400);
            if ($joursRestants > 0): ?>
            — <span class="text-<?= $joursRestants <= 7 ? 'danger fw-bold' : 'muted' ?>">
                <?= $joursRestants ?> jour(s) restant(s)
            </span>
            <?php elseif ($joursRestants === 0): ?>
            — <span class="text-danger fw-bold">Aujourd'hui !</span>
            <?php else: ?>
            — <span class="text-danger">Campagne clôturée</span>
            <?php endif; ?>
        </div>
    </div>
    <?php if ($joursRestants >= 0): ?>
    <a href="<?= url('agent/declaration/nouvelle') ?>" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-circle me-1"></i>Nouvelle déclaration
    </a>
    <?php endif; ?>
</div>
<?php else: ?>
<div class="alert alert-warning mb-4">
    <i class="bi bi-exclamation-triangle me-2"></i>
    Aucune campagne active pour le moment.
</div>
<?php endif; ?>

<!-- Alertes déclarations rejetées -->
<?php if (!empty($declarationsRejetees)): ?>
<div class="alert alert-danger mb-4">
    <h6 class="fw-bold"><i class="bi bi-x-octagon me-2"></i><?= count($declarationsRejetees) ?> déclaration(s) rejetée(s) à corriger</h6>
    <?php foreach ($declarationsRejetees as $dr): ?>
    <div class="d-flex align-items-center justify-content-between py-1 border-bottom border-danger-subtle">
        <div>
            <strong><?= e($dr['raison_sociale']) ?></strong>
            <span class="text-muted small ms-2">— <?= e(substr($dr['motif_rejet'] ?? '', 0, 80)) ?>…</span>
        </div>
        <a href="<?= url('agent/declaration/' . $dr['id'] . '/saisie') ?>" class="btn btn-sm btn-danger">Corriger</a>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- KPI Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl-2">
        <div class="stat-card stat-card-primary">
            <div class="stat-icon"><i class="bi bi-file-earmark-text"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= $stats['total_declarations'] ?></div>
                <div class="stat-label">Mes déclarations</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="stat-card stat-card-secondary">
            <div class="stat-icon"><i class="bi bi-pencil-square"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= $stats['declarations_brouillon'] ?></div>
                <div class="stat-label">Brouillons</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="stat-card stat-card-warning">
            <div class="stat-icon"><i class="bi bi-hourglass-split"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= $stats['declarations_soumises'] ?></div>
                <div class="stat-label">Soumises</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="stat-card stat-card-success">
            <div class="stat-icon"><i class="bi bi-check-circle"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= $stats['declarations_validees'] ?></div>
                <div class="stat-label">Validées</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="stat-card stat-card-danger">
            <div class="stat-icon"><i class="bi bi-x-circle"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= $stats['declarations_rejetees'] ?></div>
                <div class="stat-label">Rejetées</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="stat-card stat-card-info">
            <div class="stat-icon"><i class="bi bi-building"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= $stats['total_entreprises'] ?></div>
                <div class="stat-label">Entreprises</div>
            </div>
        </div>
    </div>
</div>

<!-- Alerte entreprises sans déclaration -->
<?php if ($entreprisesSansDeclaration > 0 && $campagne): ?>
<div class="alert alert-warning">
    <i class="bi bi-building-exclamation me-2"></i>
    <strong><?= $entreprisesSansDeclaration ?> entreprise(s)</strong> de votre région n'ont pas encore de déclaration
    pour la campagne <?= e($campagne['annee']) ?>.
    <a href="<?= url('agent/entreprises') ?>" class="alert-link ms-2">Voir les entreprises</a>
</div>
<?php endif; ?>

<!-- Dernières déclarations -->
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="bi bi-clock-history me-2 text-warning"></i>Mes dernières déclarations</h6>
        <a href="<?= url('agent/declarations') ?>" class="btn btn-sm btn-outline-primary">Voir toutes</a>
    </div>
    <div class="card-body p-0">
        <?php if (empty($dernieresDeclarations)): ?>
        <div class="text-center text-muted py-5">
            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
            Aucune déclaration.
            <?php if ($campagne): ?>
            <div class="mt-2">
                <a href="<?= url('agent/declaration/nouvelle') ?>" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i>Commencer une déclaration
                </a>
            </div>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Entreprise</th>
                        <th>Campagne</th>
                        <th>Statut</th>
                        <th>Mis à jour</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dernieresDeclarations as $d): ?>
                    <tr>
                        <td>
                            <div class="fw-500"><?= e($d['raison_sociale']) ?></div>
                            <?php if ($d['numero_cnss']): ?>
                            <small class="text-muted"><?= e($d['numero_cnss']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?= e($d['annee']) ?></td>
                        <td><?= match($d['statut']) {
                            'brouillon' => '<span class="badge bg-secondary">Brouillon</span>',
                            'soumise'   => '<span class="badge bg-warning text-dark">En attente</span>',
                            'validee'   => '<span class="badge bg-success">Validée</span>',
                            'rejetee'   => '<span class="badge bg-danger">Rejetée</span>',
                            'corrigee'  => '<span class="badge bg-info">Corrigée</span>',
                            default     => '<span class="badge bg-light text-dark">' . e($d['statut']) . '</span>',
                        } ?></td>
                        <td class="text-muted small"><?= formatDateTime($d['updated_at']) ?></td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="<?= url('agent/declaration/' . $d['id'] . '/apercu') ?>"
                                   class="btn btn-outline-secondary" title="Aperçu">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <?php if (in_array($d['statut'], ['brouillon', 'rejetee'])): ?>
                                <a href="<?= url('agent/declaration/' . $d['id'] . '/saisie') ?>"
                                   class="btn btn-outline-primary" title="Continuer la saisie">
                                    <i class="bi bi-pencil"></i>
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
