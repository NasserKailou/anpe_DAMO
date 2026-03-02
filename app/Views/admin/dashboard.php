<?php defined('EDAMO') or die('Accès direct interdit'); ?>

<!-- KPI Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="stat-card stat-card-primary">
            <div class="stat-icon"><i class="bi bi-file-earmark-text"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= number_format($stats['total_declarations']) ?></div>
                <div class="stat-label">Total déclarations</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card stat-card-warning">
            <div class="stat-icon"><i class="bi bi-hourglass-split"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= number_format($stats['declarations_soumises']) ?></div>
                <div class="stat-label">À traiter</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card stat-card-success">
            <div class="stat-icon"><i class="bi bi-check-circle"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= number_format($stats['declarations_validees']) ?></div>
                <div class="stat-label">Validées</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card stat-card-info">
            <div class="stat-icon"><i class="bi bi-building"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= number_format($stats['total_entreprises']) ?></div>
                <div class="stat-label">Entreprises</div>
            </div>
        </div>
    </div>
</div>

<!-- Campagne active -->
<?php if ($campagne): ?>
<div class="alert alert-info d-flex align-items-center gap-3 mb-4">
    <i class="bi bi-calendar-check fs-4"></i>
    <div>
        <strong>Campagne active :</strong> <?= e($campagne['libelle']) ?>
        &nbsp;|&nbsp; Clôture : <strong><?= formatDate($campagne['date_fin']) ?></strong>
        &nbsp;|&nbsp; <?= $stats['declarations_soumises'] ?> déclaration(s) en attente de traitement.
    </div>
    <a href="<?= url('admin/declarations?statut=soumise') ?>" class="btn btn-sm btn-primary ms-auto">
        <i class="bi bi-eye me-1"></i>Traiter
    </a>
</div>
<?php endif; ?>

<div class="row g-4">
    <!-- Déclarations par région -->
    <div class="col-lg-7">
        <div class="card shadow-sm h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bi bi-map me-2 text-primary"></i>Déclarations par région</h6>
                <span class="badge bg-primary"><?= $campagne['annee'] ?? date('Y') ?></span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Région</th>
                                <th class="text-center">Total</th>
                                <th class="text-center text-success">Validées</th>
                                <th class="text-center text-warning">Soumises</th>
                                <th class="text-center text-secondary">Brouillons</th>
                                <th>Progression</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($parRegion as $region): 
                                $total = (int)$region['total'];
                                $validees = (int)$region['validees'];
                                $pct = $total > 0 ? round($validees / $total * 100) : 0;
                            ?>
                            <tr>
                                <td class="fw-500"><?= e($region['nom']) ?></td>
                                <td class="text-center"><span class="badge bg-secondary"><?= $total ?></span></td>
                                <td class="text-center"><span class="badge bg-success"><?= $validees ?></span></td>
                                <td class="text-center"><span class="badge bg-warning text-dark"><?= $region['soumises'] ?></span></td>
                                <td class="text-center"><span class="badge bg-light text-dark"><?= $region['brouillons'] ?></span></td>
                                <td style="min-width:100px">
                                    <div class="progress" style="height:6px">
                                        <div class="progress-bar bg-success" style="width:<?= $pct ?>%"></div>
                                    </div>
                                    <small class="text-muted"><?= $pct ?>%</small>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Activité récente -->
    <div class="col-lg-5">
        <div class="card shadow-sm h-100">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-activity me-2 text-success"></i>Activité récente</h6>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <?php foreach ($activiteRecente as $log): ?>
                    <li class="list-group-item px-3 py-2">
                        <div class="d-flex align-items-start gap-2">
                            <span class="text-<?= match(true) {
                                str_contains($log['action'], 'login')     => 'success',
                                str_contains($log['action'], 'valid')     => 'primary',
                                str_contains($log['action'], 'rejet')     => 'danger',
                                str_contains($log['action'], 'creat')     => 'info',
                                default                                   => 'secondary'
                            } ?>"><i class="bi bi-circle-fill" style="font-size:.5rem"></i></span>
                            <div class="flex-1">
                                <span class="fw-500 small"><?= e(($log['prenom'] ?? '') . ' ' . ($log['nom'] ?? 'Système')) ?></span>
                                <span class="text-muted small"> — <?= e($log['action']) ?></span>
                                <div class="text-muted" style="font-size:.75rem"><?= formatDateTime($log['created_at']) ?></div>
                            </div>
                        </div>
                    </li>
                    <?php endforeach; ?>
                    <?php if (empty($activiteRecente)): ?>
                    <li class="list-group-item text-center text-muted py-4">Aucune activité récente</li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="card-footer text-end">
                <a href="<?= url('admin/logs') ?>" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-journal-text me-1"></i>Voir tous les logs
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Déclarations récentes -->
<div class="card shadow-sm mt-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="bi bi-clock-history me-2 text-warning"></i>Dernières déclarations</h6>
        <a href="<?= url('admin/declarations') ?>" class="btn btn-sm btn-outline-primary">Voir tout</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Code</th>
                        <th>Entreprise</th>
                        <th>Région</th>
                        <th>Agent</th>
                        <th>Statut</th>
                        <th>Mis à jour</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentesDeclarations as $d): ?>
                    <tr>
                        <td><code><?= e($d['code_questionnaire']) ?></code></td>
                        <td class="fw-500"><?= e($d['raison_sociale']) ?></td>
                        <td><?= e($d['region_nom']) ?></td>
                        <td><?= e(($d['agent_prenom'] ?? '') . ' ' . ($d['agent_nom'] ?? '')) ?></td>
                        <td><?= statutBadge($d['statut']) ?></td>
                        <td class="text-muted small"><?= formatDateTime($d['updated_at']) ?></td>
                        <td>
                            <a href="<?= url('admin/declaration/' . $d['id']) ?>" class="btn btn-xs btn-outline-primary">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
// Helper local pour les badges statut
function statutBadge(string $statut): string {
    return match($statut) {
        'brouillon' => '<span class="badge bg-secondary">Brouillon</span>',
        'soumise'   => '<span class="badge bg-warning text-dark">Soumise</span>',
        'validee'   => '<span class="badge bg-success">Validée</span>',
        'rejetee'   => '<span class="badge bg-danger">Rejetée</span>',
        'corrigee'  => '<span class="badge bg-info">Corrigée</span>',
        default     => '<span class="badge bg-light text-dark">' . htmlspecialchars($statut) . '</span>',
    };
}
?>
