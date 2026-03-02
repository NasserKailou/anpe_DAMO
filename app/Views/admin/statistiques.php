<?php
/**
 * Vue : Statistiques Admin — Tableau de bord avancé avec Chart.js
 * @var array $campagne
 * @var array $effectifsParCategorie
 * @var array $parStatut
 * @var array $effectifsParRegion
 */
$campagneId   = $campagne['id'] ?? 0;
$campagneYear = $campagne['annee'] ?? date('Y');
$campagneLib  = $campagne ? e($campagne['libelle']) . ' (' . $campagne['annee'] . ')' : 'Aucune';

// Totaux rapides
$totalEmplois = 0;
$totalHommes  = 0;
$totalFemmes  = 0;
foreach ($effectifsParCategorie as $cat) {
    $totalEmplois += (int)$cat['total'];
    $totalHommes  += (int)$cat['hommes'];
    $totalFemmes  += (int)$cat['femmes'];
}
$totalDecl = array_sum(array_column($parStatut, 'total'));
$validees  = 0; $soumises = 0; $brouillons = 0; $rejetees = 0;
foreach ($parStatut as $s) {
    $$s['statut'] = (int)$s['total'];
}
?>

<!-- Sélecteur d'année -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-0"><i class="fas fa-chart-bar text-primary me-2"></i>Statistiques DAMO</h2>
        <p class="text-muted mb-0">Campagne active : <strong><?= $campagneLib ?></strong></p>
    </div>
    <div class="d-flex gap-2">
        <select id="anneeSelector" class="form-select form-select-sm" style="width:120px">
            <?php
            $years = PGPASSWORD = '';
            $yearsData = \App\Models\Database::getInstance()->fetchAll("SELECT annee FROM campagnes_damo ORDER BY annee DESC");
            foreach ($yearsData as $y):
            ?>
              <option value="<?= $y['annee'] ?>" <?= $y['annee'] == $campagneYear ? 'selected' : '' ?>>
                <?= $y['annee'] ?>
              </option>
            <?php endforeach; ?>
        </select>
        <a href="/admin/export/declarations?campagne=<?= $campagneId ?>" class="btn btn-outline-success btn-sm">
            <i class="fas fa-file-csv me-1"></i>CSV
        </a>
        <button onclick="window.print()" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-print me-1"></i>Imprimer
        </button>
    </div>
</div>

<!-- KPIs principaux -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                        <i class="fas fa-file-alt fa-lg text-primary"></i>
                    </div>
                    <div>
                        <div class="fs-2 fw-bold text-primary"><?= number_format($totalDecl) ?></div>
                        <div class="text-muted small">Déclarations totales</div>
                    </div>
                </div>
                <div class="mt-2 d-flex gap-2 flex-wrap">
                    <span class="badge bg-success"><?= $validees ?> validées</span>
                    <span class="badge bg-warning text-dark"><?= $soumises ?> soumises</span>
                    <span class="badge bg-secondary"><?= $brouillons ?> brouillons</span>
                    <?php if ($rejetees): ?><span class="badge bg-danger"><?= $rejetees ?> rejetées</span><?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 rounded-circle bg-success bg-opacity-10 p-3 me-3">
                        <i class="fas fa-users fa-lg text-success"></i>
                    </div>
                    <div>
                        <div class="fs-2 fw-bold text-success"><?= number_format($totalEmplois) ?></div>
                        <div class="text-muted small">Total emplois déclarés</div>
                    </div>
                </div>
                <div class="mt-2 progress" style="height:6px">
                    <?php $pctF = $totalEmplois > 0 ? round($totalFemmes / $totalEmplois * 100) : 0; ?>
                    <div class="progress-bar bg-info" style="width:<?= $pctF ?>%"></div>
                </div>
                <small class="text-muted">Femmes : <?= $pctF ?>%</small>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 rounded-circle bg-info bg-opacity-10 p-3 me-3">
                        <i class="fas fa-male fa-lg text-info"></i>
                    </div>
                    <div>
                        <div class="fs-2 fw-bold text-info"><?= number_format($totalHommes) ?></div>
                        <div class="text-muted small">Emplois masculins</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 rounded-circle bg-warning bg-opacity-10 p-3 me-3">
                        <i class="fas fa-female fa-lg text-warning"></i>
                    </div>
                    <div>
                        <div class="fs-2 fw-bold text-warning"><?= number_format($totalFemmes) ?></div>
                        <div class="text-muted small">Emplois féminins</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Graphiques -->
<div class="row g-4 mb-4">
    <!-- Effectifs par catégorie -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom d-flex justify-content-between">
                <h6 class="mb-0 fw-semibold"><i class="fas fa-briefcase me-2 text-primary"></i>Effectifs par catégorie professionnelle</h6>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-secondary active" onclick="toggleCatChart('bar')">Barres</button>
                    <button class="btn btn-outline-secondary" onclick="toggleCatChart('horizontalBar')">Horizontal</button>
                </div>
            </div>
            <div class="card-body">
                <div style="height:320px; position:relative">
                    <canvas id="chartCategories"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Genre (Donut) -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-semibold"><i class="fas fa-venus-mars me-2 text-info"></i>Répartition par genre</h6>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center flex-column">
                <div style="height:220px; width:220px; position:relative">
                    <canvas id="chartGenre"></canvas>
                </div>
                <div class="mt-3 d-flex gap-3 text-center">
                    <div>
                        <div class="fw-bold text-primary"><?= $totalHommes > 0 ? round($totalHommes / max(1,$totalEmplois) * 100) : 0 ?>%</div>
                        <small class="text-muted">Hommes</small>
                    </div>
                    <div>
                        <div class="fw-bold text-danger"><?= $pctF ?>%</div>
                        <small class="text-muted">Femmes</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Déclarations par région -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-semibold"><i class="fas fa-map-marker-alt me-2 text-success"></i>Emplois déclarés par région</h6>
            </div>
            <div class="card-body">
                <div style="height:300px; position:relative">
                    <canvas id="chartRegions"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Statuts des déclarations -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-semibold"><i class="fas fa-tasks me-2 text-warning"></i>Statuts des déclarations</h6>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center flex-column">
                <div style="height:250px; width:250px; position:relative">
                    <canvas id="chartStatuts"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tableau détaillé par région -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom">
        <h6 class="mb-0 fw-semibold"><i class="fas fa-table me-2 text-secondary"></i>Détail par région</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Région</th>
                        <th class="text-center">Déclarations</th>
                        <th class="text-center">Emplois totaux</th>
                        <th>Avancement</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $maxEmplois = max(1, max(array_column($effectifsParRegion, 'total_emplois') ?: [1]));
                    foreach ($effectifsParRegion as $reg):
                        $pct = $maxEmplois > 0 ? round(($reg['total_emplois'] ?? 0) / $maxEmplois * 100) : 0;
                    ?>
                    <tr>
                        <td class="fw-semibold"><?= e($reg['region']) ?></td>
                        <td class="text-center">
                            <span class="badge bg-primary"><?= $reg['nb_declarations'] ?></span>
                        </td>
                        <td class="text-center fw-bold"><?= number_format((int)($reg['total_emplois'] ?? 0)) ?></td>
                        <td style="min-width:150px">
                            <div class="progress" style="height:10px">
                                <div class="progress-bar bg-gradient" style="width:<?= $pct ?>%;
                                     background: linear-gradient(90deg, #1d4ed8, #7c3aed) !important;"></div>
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

<!-- Tableau effectifs par catégorie -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom">
        <h6 class="mb-0 fw-semibold"><i class="fas fa-list me-2 text-secondary"></i>Effectifs par catégorie (détail H/F)</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Catégorie</th>
                        <th class="text-center">Hommes</th>
                        <th class="text-center">Femmes</th>
                        <th class="text-center">Total</th>
                        <th>Part</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($effectifsParCategorie as $cat):
                        $part = $totalEmplois > 0 ? round($cat['total'] / $totalEmplois * 100, 1) : 0;
                        $label = CATEGORIES_PROFESSIONNELLES[$cat['categorie']] ?? $cat['categorie'];
                    ?>
                    <tr>
                        <td><?= e($label) ?></td>
                        <td class="text-center"><?= number_format((int)$cat['hommes']) ?></td>
                        <td class="text-center"><?= number_format((int)$cat['femmes']) ?></td>
                        <td class="text-center fw-bold"><?= number_format((int)$cat['total']) ?></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="height:8px">
                                    <div class="progress-bar bg-primary" style="width:<?= $part ?>%"></div>
                                </div>
                                <small class="text-muted" style="min-width:35px"><?= $part ?>%</small>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td>TOTAL</td>
                        <td class="text-center"><?= number_format($totalHommes) ?></td>
                        <td class="text-center"><?= number_format($totalFemmes) ?></td>
                        <td class="text-center"><?= number_format($totalEmplois) ?></td>
                        <td>100%</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function() {
    const COLORS = [
        '#1d4ed8','#7c3aed','#059669','#d97706','#dc2626',
        '#0891b2','#65a30d','#9333ea','#ea580c','#0284c7'
    ];

    // ── Catégories ──────────────────────────────────────────────
    const catData = <?= json_encode(array_map(fn($c) => [
        'label' => CATEGORIES_PROFESSIONNELLES[$c['categorie']] ?? $c['categorie'],
        'h' => (int)$c['hommes'], 'f' => (int)$c['femmes']
    ], $effectifsParCategorie)) ?>;

    let catChart = new Chart(document.getElementById('chartCategories'), {
        type: 'bar',
        data: {
            labels: catData.map(c => c.label),
            datasets: [
                { label: 'Hommes', data: catData.map(c => c.h), backgroundColor: '#1d4ed8' },
                { label: 'Femmes', data: catData.map(c => c.f), backgroundColor: '#ec4899' },
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { position: 'top' } },
            scales: {
                x: { stacked: true, ticks: { font: { size: 10 } } },
                y: { stacked: true, beginAtZero: true }
            }
        }
    });

    window.toggleCatChart = function(type) {
        catChart.destroy();
        const opts = { responsive: true, maintainAspectRatio: false,
            plugins: { legend: { position: 'top' } },
            indexAxis: type === 'horizontalBar' ? 'y' : 'x',
            scales: { x: { stacked: true, beginAtZero: true }, y: { stacked: true } }
        };
        catChart = new Chart(document.getElementById('chartCategories'), {
            type: 'bar',
            data: {
                labels: catData.map(c => c.label),
                datasets: [
                    { label: 'Hommes', data: catData.map(c => c.h), backgroundColor: '#1d4ed8' },
                    { label: 'Femmes', data: catData.map(c => c.f), backgroundColor: '#ec4899' },
                ]
            },
            options: opts
        });
        document.querySelectorAll('.btn-group button').forEach(b => b.classList.remove('active'));
        event.target.classList.add('active');
    };

    // ── Genre (Donut) ────────────────────────────────────────────
    new Chart(document.getElementById('chartGenre'), {
        type: 'doughnut',
        data: {
            labels: ['Hommes', 'Femmes'],
            datasets: [{ data: [<?= $totalHommes ?>, <?= $totalFemmes ?>],
                         backgroundColor: ['#1d4ed8', '#ec4899'],
                         borderWidth: 0, hoverOffset: 6 }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' },
                       tooltip: { callbacks: {
                           label: ctx => ` ${ctx.label} : ${ctx.parsed.toLocaleString('fr-FR')}`
                       }}}
        }
    });

    // ── Régions (bar) ────────────────────────────────────────────
    const regionData = <?= json_encode(array_map(fn($r) => [
        'region'  => $r['region'],
        'emplois' => (int)($r['total_emplois'] ?? 0),
        'decl'    => (int)$r['nb_declarations'],
    ], $effectifsParRegion)) ?>;

    new Chart(document.getElementById('chartRegions'), {
        type: 'bar',
        data: {
            labels: regionData.map(r => r.region),
            datasets: [{
                label: 'Emplois déclarés',
                data: regionData.map(r => r.emplois),
                backgroundColor: COLORS,
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } }
        }
    });

    // ── Statuts (Donut) ──────────────────────────────────────────
    const statutColors = { validee:'#059669', soumise:'#d97706', brouillon:'#6b7280', rejetee:'#dc2626', corrigee:'#7c3aed' };
    const statutLabels = { validee:'Validées', soumise:'Soumises', brouillon:'Brouillons', rejetee:'Rejetées', corrigee:'Corrigées' };
    const statutData   = <?= json_encode($parStatut) ?>;

    new Chart(document.getElementById('chartStatuts'), {
        type: 'doughnut',
        data: {
            labels: statutData.map(s => statutLabels[s.statut] ?? s.statut),
            datasets: [{
                data: statutData.map(s => s.total),
                backgroundColor: statutData.map(s => statutColors[s.statut] ?? '#6b7280'),
                borderWidth: 0, hoverOffset: 6
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } }
        }
    });

    // ── Rechargement sur changement d'année ──────────────────────
    document.getElementById('anneeSelector').addEventListener('change', function() {
        window.location.href = '/admin/statistiques?annee=' + this.value;
    });
})();
</script>
