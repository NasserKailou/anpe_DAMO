<?php // Vue : Tableau de bord Admin ?>

<div class="page-header">
    <div class="page-header-left">
        <h1><i class="bi bi-speedometer2 me-2 text-primary"></i>Tableau de bord</h1>
        <p>Campagne active : <strong><?= $campagne ? e($campagne['libelle']) . ' (' . $campagne['annee'] . ')' : 'Aucune campagne active' ?></strong></p>
    </div>
    <div class="page-header-right">
        <?php if ($campagne): ?>
        <span class="badge bg-success">
            <i class="bi bi-calendar-check me-1"></i>
            Jusqu'au <?= formatDate($campagne['date_fin']) ?>
        </span>
        <?php endif; ?>
        <a href="/admin/declarations?statut=soumise" class="btn btn-sm btn-primary">
            <i class="bi bi-eye me-1"></i>
            Voir les soumissions (<?= $stats['declarations_soumises'] ?>)
        </a>
    </div>
</div>

<!-- Statistiques principales -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-card-icon" style="background:#d6eaf8;color:#1a5276">
                <i class="bi bi-file-earmark-text"></i>
            </div>
            <div class="stat-card-value"><?= formatNumber($stats['total_declarations']) ?></div>
            <div class="stat-card-label">Total déclarations</div>
            <div class="stat-card-change text-muted">
                <i class="bi bi-arrow-right"></i>
                <a href="/admin/declarations" class="text-decoration-none text-muted">Voir tout</a>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-card-icon" style="background:#d1ecf1;color:#0c5460">
                <i class="bi bi-hourglass-split"></i>
            </div>
            <div class="stat-card-value text-info"><?= formatNumber($stats['declarations_soumises']) ?></div>
            <div class="stat-card-label">En attente validation</div>
            <?php if ($stats['declarations_soumises'] > 0): ?>
            <div class="stat-card-change text-info">
                <i class="bi bi-exclamation-circle"></i> Action requise
            </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-card-icon" style="background:#d4edda;color:#155724">
                <i class="bi bi-check-circle"></i>
            </div>
            <div class="stat-card-value text-success"><?= formatNumber($stats['declarations_validees']) ?></div>
            <div class="stat-card-label">Validées</div>
            <div class="stat-card-change text-success">
                <i class="bi bi-check2-all"></i>
                <?= $stats['total_declarations'] > 0 ? round($stats['declarations_validees'] / $stats['total_declarations'] * 100) : 0 ?>% du total
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-card-icon" style="background:#fff3cd;color:#856404">
                <i class="bi bi-building"></i>
            </div>
            <div class="stat-card-value text-warning"><?= formatNumber($stats['total_entreprises']) ?></div>
            <div class="stat-card-label">Entreprises enregistrées</div>
            <div class="stat-card-change text-muted">
                <i class="bi bi-geo-alt"></i> <?= $stats['total_regions_actives'] ?> régions couvertes
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <!-- Graphique déclarations par région -->
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header">
                <h5><i class="bi bi-bar-chart me-2"></i>Déclarations par région</h5>
                <select class="form-select form-select-sm w-auto" id="chartPeriod">
                    <option>Campagne courante</option>
                </select>
            </div>
            <div class="card-body">
                <div style="height:280px">
                    <canvas id="chartRegions"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Statut des déclarations (donut) -->
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">
                <h5><i class="bi bi-pie-chart me-2"></i>Répartition par statut</h5>
            </div>
            <div class="card-body d-flex flex-column align-items-center justify-content-center">
                <div style="height:200px;width:200px">
                    <canvas id="chartStatuts"></canvas>
                </div>
                <div class="mt-3 w-100">
                    <?php
                    $statutColors = [
                        'brouillon' => '#ffc107',
                        'soumise'   => '#17a2b8',
                        'validee'   => '#28a745',
                        'rejetee'   => '#dc3545',
                        'corrigee'  => '#6c757d',
                    ];
                    ?>
                    <div class="d-flex flex-wrap gap-2 justify-content-center">
                        <span class="badge-statut badge-brouillon">Brouillon: <?= $stats['total_declarations'] - $stats['declarations_soumises'] - $stats['declarations_validees'] - $stats['declarations_rejetees'] ?></span>
                        <span class="badge-statut badge-soumise">Soumises: <?= $stats['declarations_soumises'] ?></span>
                        <span class="badge-statut badge-validee">Validées: <?= $stats['declarations_validees'] ?></span>
                        <span class="badge-statut badge-rejetee">Rejetées: <?= $stats['declarations_rejetees'] ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- Déclarations récentes -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5><i class="bi bi-clock-history me-2"></i>Activités récentes</h5>
                <a href="/admin/declarations" class="btn btn-sm btn-outline-primary">Tout voir</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Entreprise</th>
                                <th>Région</th>
                                <th>Statut</th>
                                <th>Date</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentesDeclarations as $decl): ?>
                            <tr>
                                <td><code><?= e($decl['code_questionnaire']) ?></code></td>
                                <td>
                                    <strong><?= e(truncate($decl['raison_sociale'], 30)) ?></strong>
                                    <?php if ($decl['numero_cnss']): ?>
                                    <br><small class="text-muted">CNSS: <?= e($decl['numero_cnss']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><span class="text-muted"><?= e($decl['region_nom']) ?></span></td>
                                <td><span class="badge-statut badge-<?= e($decl['statut']) ?>"><?= statutLabel($decl['statut']) ?></span></td>
                                <td><small><?= formatDateTime($decl['updated_at']) ?></small></td>
                                <td>
                                    <a href="/admin/declaration/<?= $decl['id'] ?>" class="btn btn-sm btn-icon btn-outline-secondary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($recentesDeclarations)): ?>
                            <tr><td colspan="6" class="text-center text-muted py-4">Aucune déclaration</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Activité récente + Quick Stats par région -->
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header">
                <h5><i class="bi bi-geo-alt me-2"></i>Avancement par région</h5>
            </div>
            <div class="card-body p-0">
                <?php foreach ($parRegion as $r): ?>
                <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom">
                    <div>
                        <strong class="d-block" style="font-size:.85rem"><?= e($r['nom']) ?></strong>
                        <small class="text-muted"><?= $r['total'] ?> déclarations</small>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-success"><?= $r['validees'] ?> ✓</span>
                        <?php if ($r['soumises'] > 0): ?>
                        <span class="badge bg-info"><?= $r['soumises'] ?> ⏳</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($parRegion)): ?>
                <p class="text-center text-muted p-3">Aucune donnée</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Actions rapides -->
        <div class="card">
            <div class="card-header"><h5><i class="bi bi-lightning me-2"></i>Actions rapides</h5></div>
            <div class="card-body d-grid gap-2">
                <a href="/admin/utilisateur/nouveau" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-person-plus me-2"></i>Ajouter un agent
                </a>
                <a href="/admin/declarations?statut=soumise" class="btn btn-outline-info btn-sm">
                    <i class="bi bi-check2-square me-2"></i>Valider les soumissions
                </a>
                <a href="/admin/export/declarations" class="btn btn-outline-success btn-sm">
                    <i class="bi bi-download me-2"></i>Exporter les données
                </a>
                <a href="/admin/guide/nouveau" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-upload me-2"></i>Uploader un guide
                </a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', async () => {
    // Charger les données
    const res = await fetch('/api/admin/stats', {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-Token': CSRF_TOKEN }
    });
    const data = await res.json();
    
    // Graphique régions
    const regionsData = <?= json_encode(array_map(fn($r) => ['label' => $r['nom'], 'validees' => (int)$r['validees'], 'soumises' => (int)$r['soumises'], 'brouillons' => (int)$r['brouillons']], $parRegion)) ?>;
    
    if (regionsData.length > 0) {
        initChart('chartRegions', 'bar', {
            labels: regionsData.map(r => r.label),
            datasets: [
                { label: 'Validées', data: regionsData.map(r => r.validees), backgroundColor: '#28a745' },
                { label: 'Soumises', data: regionsData.map(r => r.soumises), backgroundColor: '#17a2b8' },
                { label: 'Brouillons', data: regionsData.map(r => r.brouillons), backgroundColor: '#ffc107' },
            ]
        }, { scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true } } });
    }
    
    // Graphique statuts
    const statutData = {
        brouillon: <?= $stats['total_declarations'] - $stats['declarations_soumises'] - $stats['declarations_validees'] - $stats['declarations_rejetees'] ?>,
        soumise: <?= $stats['declarations_soumises'] ?>,
        validee: <?= $stats['declarations_validees'] ?>,
        rejetee: <?= $stats['declarations_rejetees'] ?>
    };
    
    initChart('chartStatuts', 'doughnut', {
        labels: ['Brouillon', 'Soumises', 'Validées', 'Rejetées'],
        datasets: [{
            data: [statutData.brouillon, statutData.soumise, statutData.validee, statutData.rejetee],
            backgroundColor: ['#ffc107', '#17a2b8', '#28a745', '#dc3545'],
        }]
    });
});
</script>
