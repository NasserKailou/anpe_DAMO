<?php defined('EDAMO') or die('Accès direct interdit'); ?>

<!-- Sélecteur d'année -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div class="d-flex gap-2 align-items-center">
        <span class="text-muted">Campagne :</span>
        <strong><?= e($campagne['libelle'] ?? 'Aucune') ?></strong>
    </div>
    <form method="GET" class="d-flex gap-2 align-items-center">
        <select name="annee" class="form-select form-select-sm" onchange="this.form.submit()">
            <?php foreach ($annees as $a): ?>
            <option value="<?= $a['annee'] ?>" <?= ($a['annee'] == ($campagne['annee'] ?? 0)) ? 'selected' : '' ?>>
                <?= $a['annee'] ?>
            </option>
            <?php endforeach; ?>
        </select>
        <a href="<?= url('admin/export/declarations?campagne=' . ($campagne['id'] ?? '')) ?>"
           class="btn btn-sm btn-outline-success">
            <i class="bi bi-file-earmark-excel me-1"></i>Export
        </a>
    </form>
</div>

<!-- KPI -->
<div class="row g-3 mb-4">
    <?php
    $totalDecl  = array_sum(array_column($parStatut, 'total'));
    $validees   = 0; $soumises = 0; $rejetees = 0; $brouillons = 0;
    foreach ($parStatut as $s) {
        match($s['statut']) {
            'validee'   => $validees   = (int)$s['total'],
            'soumise'   => $soumises   = (int)$s['total'],
            'rejetee'   => $rejetees   = (int)$s['total'],
            'brouillon' => $brouillons = (int)$s['total'],
            default     => null
        };
    }
    $totalEmplois = 0;
    foreach ($effectifsParCategorie as $e) { $totalEmplois += (int)$e['total']; }
    ?>
    <div class="col-6 col-xl-3">
        <div class="stat-card stat-card-primary">
            <div class="stat-icon"><i class="bi bi-file-earmark-text"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= number_format($totalDecl) ?></div>
                <div class="stat-label">Total déclarations</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card stat-card-success">
            <div class="stat-icon"><i class="bi bi-check-circle"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= number_format($validees) ?></div>
                <div class="stat-label">Validées</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card stat-card-warning">
            <div class="stat-icon"><i class="bi bi-hourglass-split"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= number_format($soumises) ?></div>
                <div class="stat-label">En attente</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card stat-card-info">
            <div class="stat-icon"><i class="bi bi-people"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= number_format($totalEmplois) ?></div>
                <div class="stat-label">Emplois déclarés</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Graphique statuts -->
    <div class="col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-header"><h6 class="mb-0"><i class="bi bi-pie-chart me-2 text-primary"></i>Répartition par statut</h6></div>
            <div class="card-body d-flex align-items-center justify-content-center">
                <div style="max-width:260px; width:100%">
                    <canvas id="chartStatuts"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphique catégories -->
    <div class="col-lg-8">
        <div class="card shadow-sm h-100">
            <div class="card-header"><h6 class="mb-0"><i class="bi bi-bar-chart me-2 text-success"></i>Effectifs par catégorie professionnelle</h6></div>
            <div class="card-body">
                <canvas id="chartCategories" height="120"></canvas>
            </div>
        </div>
    </div>

    <!-- Effectifs par région -->
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header"><h6 class="mb-0"><i class="bi bi-map me-2 text-info"></i>Déclarations et emplois par région</h6></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-striped mb-0">
                        <thead class="table-light"><tr><th>Région</th><th class="text-center">Décl.</th><th class="text-center">Emplois</th></tr></thead>
                        <tbody>
                            <?php foreach ($effectifsParRegion as $r): ?>
                            <tr>
                                <td><?= e($r['region']) ?></td>
                                <td class="text-center"><?= number_format($r['nb_declarations']) ?></td>
                                <td class="text-center fw-bold"><?= number_format($r['total_emplois']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Top entreprises -->
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header"><h6 class="mb-0"><i class="bi bi-trophy me-2 text-warning"></i>Top 10 entreprises (emplois)</h6></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-striped mb-0">
                        <thead class="table-light"><tr><th>#</th><th>Entreprise</th><th>Région</th><th class="text-end">Emplois</th></tr></thead>
                        <tbody>
                            <?php foreach ($topEntreprises as $i => $ent): ?>
                            <tr>
                                <td><span class="badge bg-<?= $i < 3 ? 'warning text-dark' : 'light text-dark' ?>"><?= $i+1 ?></span></td>
                                <td class="fw-500 small"><?= e($ent['raison_sociale']) ?></td>
                                <td class="small text-muted"><?= e($ent['region']) ?></td>
                                <td class="text-end fw-bold"><?= number_format($ent['emplois']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Tableau catégories détaillé -->
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header"><h6 class="mb-0"><i class="bi bi-table me-2 text-secondary"></i>Effectifs détaillés par catégorie</h6></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Catégorie</th>
                                <th class="text-center">Hommes</th>
                                <th class="text-center">Femmes</th>
                                <th class="text-center fw-bold text-primary">Total</th>
                                <th>Répartition</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($effectifsParCategorie as $cat):
                                $catLabel = CATEGORIES_PROFESSIONNELLES[$cat['categorie']] ?? $cat['categorie'];
                                $pct = $totalEmplois > 0 ? round((int)$cat['total'] / $totalEmplois * 100) : 0;
                            ?>
                            <tr>
                                <td><?= e($catLabel) ?></td>
                                <td class="text-center"><?= number_format($cat['hommes']) ?></td>
                                <td class="text-center"><?= number_format($cat['femmes']) ?></td>
                                <td class="text-center fw-bold text-primary"><?= number_format($cat['total']) ?></td>
                                <td style="min-width:120px">
                                    <div class="progress" style="height:8px">
                                        <div class="progress-bar bg-primary" style="width:<?= $pct ?>%"></div>
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
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Graphique statuts
    new Chart(document.getElementById('chartStatuts'), {
        type: 'doughnut',
        data: {
            labels: ['Validées', 'Soumises', 'Rejetées', 'Brouillons'],
            datasets: [{
                data: [<?= $validees ?>, <?= $soumises ?>, <?= $rejetees ?>, <?= $brouillons ?>],
                backgroundColor: ['#198754', '#ffc107', '#dc3545', '#6c757d'],
                borderWidth: 2
            }]
        },
        options: {
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12 } }
            }
        }
    });

    // Graphique catégories
    const cats   = <?= json_encode(array_map(fn($c) => CATEGORIES_PROFESSIONNELLES[$c['categorie']] ?? $c['categorie'], $effectifsParCategorie)) ?>;
    const hommes = <?= json_encode(array_column($effectifsParCategorie, 'hommes')) ?>;
    const femmes = <?= json_encode(array_column($effectifsParCategorie, 'femmes')) ?>;

    new Chart(document.getElementById('chartCategories'), {
        type: 'bar',
        data: {
            labels: cats,
            datasets: [
                { label: 'Hommes', data: hommes, backgroundColor: 'rgba(13,110,253,0.7)', borderRadius: 4 },
                { label: 'Femmes', data: femmes, backgroundColor: 'rgba(214,51,132,0.7)', borderRadius: 4 }
            ]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'top' } },
            scales: {
                x: { ticks: { maxRotation: 30, font: { size: 11 } } },
                y: { beginAtZero: true }
            }
        }
    });
});
</script>

<?php
// Préparer les années disponibles
$annees = $annees ?? [];
?>
