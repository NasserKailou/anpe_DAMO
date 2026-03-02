<?php
/**
 * Vue publique des statistiques de l'emploi formel - e-DAMO
 */
$parCategorie      = $parCategorie      ?? [];
$parRegion         = $parRegion         ?? [];
$pertesEmploi      = $pertesEmploi      ?? [];
$parNiveau         = $parNiveau         ?? [];
$perspectives      = $perspectives      ?? [];
$anneesDisponibles = $anneesDisponibles ?? [];
$annee             = $anneeSelectionnee ?? date('Y');
$campagne          = $campagne          ?? null;

// Calcul totaux globaux
$totalEmplois = 0; $totalH = 0; $totalF = 0;
foreach ($parCategorie as $row) {
    $totalEmplois += ($row['hommes']??0) + ($row['femmes']??0);
    $totalH       += $row['hommes']??0;
    $totalF       += $row['femmes']??0;
}
$totalPertes = 0;
foreach ($pertesEmploi as $row) { $totalPertes += $row['total']??0; }

$motifLabels = MOTIFS_PERTE_EMPLOI;
$catLabels   = CATEGORIES_PROFESSIONNELLES;
$nivLabels   = NIVEAUX_INSTRUCTION;
?>

<!-- Sélecteur d'année -->
<div class="bg-white border-bottom py-3 mb-4" style="margin:-1.5rem -1.5rem 1.5rem">
    <div class="container-xl px-4">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h4 class="mb-0 text-dark fw-bold">
                    <i class="bi bi-bar-chart-fill text-primary me-2"></i>
                    Statistiques de l'emploi formel
                    <?php if ($campagne): ?>
                        <span class="badge bg-primary ms-2"><?= e($campagne['annee'] ?? $annee) ?></span>
                    <?php endif; ?>
                </h4>
                <?php if ($campagne && ($campagne['libelle']??'')): ?>
                    <small class="text-muted"><?= e($campagne['libelle']) ?></small>
                <?php endif; ?>
            </div>
            <div class="col-md-6">
                <form method="GET" class="d-flex justify-content-md-end gap-2 mt-2 mt-md-0">
                    <select name="annee" class="form-select form-select-sm" style="max-width:160px" onchange="this.form.submit()">
                        <?php foreach ($anneesDisponibles as $a): ?>
                            <option value="<?= $a['annee'] ?>" <?= $a['annee'] == $annee ? 'selected' : '' ?>>
                                Année <?= $a['annee'] ?>
                            </option>
                        <?php endforeach; ?>
                        <?php if (empty($anneesDisponibles)): ?>
                            <option value="<?= $annee ?>" selected>Année <?= $annee ?></option>
                        <?php endif; ?>
                    </select>
                    <a href="<?= url('donnees') ?>" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-table me-1"></i>Données brutes
                    </a>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- KPIs principaux -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center h-100">
            <div class="card-body">
                <div class="fs-2 fw-bold text-primary"><?= number_format($totalEmplois) ?></div>
                <div class="text-muted small">Emplois formels déclarés</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center h-100">
            <div class="card-body">
                <div class="fs-2 fw-bold text-info"><?= number_format($totalH) ?></div>
                <div class="text-muted small">Hommes</div>
                <?php if ($totalEmplois > 0): ?>
                    <div class="progress mt-2" style="height:4px">
                        <div class="progress-bar bg-info" style="width:<?= round($totalH/$totalEmplois*100) ?>%"></div>
                    </div>
                    <div style="font-size:.7rem" class="text-muted mt-1"><?= round($totalH/$totalEmplois*100) ?>%</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center h-100">
            <div class="card-body">
                <div class="fs-2 fw-bold text-danger"><?= number_format($totalF) ?></div>
                <div class="text-muted small">Femmes</div>
                <?php if ($totalEmplois > 0): ?>
                    <div class="progress mt-2" style="height:4px">
                        <div class="progress-bar bg-danger" style="width:<?= round($totalF/$totalEmplois*100) ?>%"></div>
                    </div>
                    <div style="font-size:.7rem" class="text-muted mt-1"><?= round($totalF/$totalEmplois*100) ?>%</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center h-100">
            <div class="card-body">
                <div class="fs-2 fw-bold text-warning"><?= number_format($totalPertes) ?></div>
                <div class="text-muted small">Pertes d'emploi</div>
            </div>
        </div>
    </div>
</div>

<?php if ($totalEmplois === 0): ?>
    <div class="alert alert-info text-center">
        <i class="bi bi-info-circle fs-3 d-block mb-2"></i>
        <strong>Aucune donnée publiée</strong> pour l'année <?= $annee ?>.<br>
        <small class="text-muted">Les statistiques apparaissent dès que des déclarations sont validées par l'ANPE.</small>
    </div>
<?php else: ?>

<div class="row g-4">
    <!-- ── Graphique effectifs par catégorie ── -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-people me-2 text-primary"></i>Effectifs par catégorie professionnelle</h6>
            </div>
            <div class="card-body">
                <canvas id="chart-categories" height="300"></canvas>
            </div>
        </div>
    </div>

    <!-- ── Tableau effectifs par catégorie ── -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-table me-2 text-primary"></i>Détail par catégorie</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Catégorie</th>
                                <th class="text-center">H</th>
                                <th class="text-center">F</th>
                                <th class="text-center">Total</th>
                                <th class="text-center" style="width:80px">Part</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($parCategorie as $row): ?>
                                <?php $tot = ($row['hommes']??0) + ($row['femmes']??0); ?>
                                <tr>
                                    <td class="small"><?= $catLabels[$row['categorie']] ?? $row['categorie'] ?></td>
                                    <td class="text-center small"><?= number_format($row['hommes']??0) ?></td>
                                    <td class="text-center small"><?= number_format($row['femmes']??0) ?></td>
                                    <td class="text-center fw-semibold"><?= number_format($tot) ?></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-1">
                                            <div class="progress flex-fill" style="height:6px">
                                                <div class="progress-bar bg-primary"
                                                     style="width:<?= $totalEmplois>0 ? round($tot/$totalEmplois*100) : 0 ?>%"></div>
                                            </div>
                                            <span style="font-size:.7rem;width:28px">
                                                <?= $totalEmplois>0 ? round($tot/$totalEmplois*100) : 0 ?>%
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-secondary fw-bold">
                            <tr>
                                <td>TOTAL</td>
                                <td class="text-center"><?= number_format($totalH) ?></td>
                                <td class="text-center"><?= number_format($totalF) ?></td>
                                <td class="text-center"><?= number_format($totalEmplois) ?></td>
                                <td class="text-center">100%</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Effectifs par région ── -->
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-geo-alt me-2 text-success"></i>Répartition par région</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-lg-7">
                        <canvas id="chart-regions" height="280"></canvas>
                    </div>
                    <div class="col-lg-5">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Région</th>
                                        <th class="text-center">Entrep.</th>
                                        <th class="text-center">Total</th>
                                        <th class="text-center" style="width:70px">% H/F</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($parRegion as $row): ?>
                                        <?php $t = (int)($row['total_emplois']??0); ?>
                                        <tr>
                                            <td class="small fw-semibold"><?= e($row['region']) ?></td>
                                            <td class="text-center small"><?= $row['nb_entreprises']??0 ?></td>
                                            <td class="text-center fw-semibold"><?= number_format($t) ?></td>
                                            <td>
                                                <?php if ($t > 0): ?>
                                                    <div class="d-flex" style="height:10px;border-radius:3px;overflow:hidden">
                                                        <div style="width:<?= round(($row['hommes']??0)/$t*100) ?>%;background:#0d6efd"></div>
                                                        <div style="width:<?= round(($row['femmes']??0)/$t*100) ?>%;background:#dc3545"></div>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-2 d-flex gap-3">
                            <div class="d-flex align-items-center gap-1">
                                <div style="width:12px;height:12px;background:#0d6efd;border-radius:2px"></div>
                                <small class="text-muted">Hommes</small>
                            </div>
                            <div class="d-flex align-items-center gap-1">
                                <div style="width:12px;height:12px;background:#dc3545;border-radius:2px"></div>
                                <small class="text-muted">Femmes</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Niveaux d'instruction ── -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-mortarboard me-2 text-info"></i>Niveaux d'instruction</h6>
            </div>
            <div class="card-body">
                <canvas id="chart-niveaux" height="300"></canvas>
            </div>
        </div>
    </div>

    <!-- ── Pertes d'emploi ── -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-person-dash me-2 text-warning"></i>Pertes d'emploi par motif</h6>
            </div>
            <div class="card-body">
                <?php if ($totalPertes > 0): ?>
                    <canvas id="chart-pertes" height="200"></canvas>
                    <div class="table-responsive mt-3">
                        <table class="table table-sm">
                            <thead class="table-light">
                                <tr><th>Motif</th><th class="text-center">H</th><th class="text-center">F</th><th class="text-center">Total</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pertesEmploi as $row): ?>
                                    <tr>
                                        <td class="small"><?= $motifLabels[$row['motif']] ?? $row['motif'] ?></td>
                                        <td class="text-center small"><?= $row['hommes']??0 ?></td>
                                        <td class="text-center small"><?= $row['femmes']??0 ?></td>
                                        <td class="text-center fw-semibold"><?= $row['total']??0 ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-check-circle text-success fs-3"></i>
                        <p class="mt-2">Aucune perte d'emploi enregistrée.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ── Perspectives ── -->
    <?php if (!empty($perspectives)): ?>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-graph-up-arrow me-2 text-success"></i>Perspectives d'emploi</h6>
            </div>
            <div class="card-body">
                <canvas id="chart-perspectives" height="220"></canvas>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div><!-- /row -->

<!-- Scripts Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const colors = ['#0d6efd','#198754','#ffc107','#dc3545','#0dcaf0','#6f42c1','#fd7e14','#20c997','#6c757d'];

// ── Graphique catégories ──
(function(){
    const cats = <?= json_encode(array_values(CATEGORIES_PROFESSIONNELLES)) ?>;
    const dataH = <?= json_encode(array_map(fn($r) => (int)($r['hommes']??0), $parCategorie)) ?>;
    const dataF = <?= json_encode(array_map(fn($r) => (int)($r['femmes']??0), $parCategorie)) ?>;
    // Mapper dans l'ordre des constantes
    const catKeys = <?= json_encode(array_keys(CATEGORIES_PROFESSIONNELLES)) ?>;
    const catMap = {};
    <?php foreach ($parCategorie as $row): ?>
    catMap['<?= $row['categorie'] ?>'] = {h:<?= (int)($row['hommes']??0) ?>, f:<?= (int)($row['femmes']??0) ?>};
    <?php endforeach; ?>
    const labels = catKeys.map(k => cats[catKeys.indexOf(k)]);
    const hVals = catKeys.map(k => catMap[k]?.h || 0);
    const fVals = catKeys.map(k => catMap[k]?.f || 0);

    const ctx = document.getElementById('chart-categories');
    if (ctx) new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_values(CATEGORIES_PROFESSIONNELLES)) ?>,
            datasets: [
                { label:'Hommes', data: <?= json_encode(array_map(fn($r) => (int)($r['hommes']??0), $parCategorie)) ?>, backgroundColor:'#0d6efd' },
                { label:'Femmes', data: <?= json_encode(array_map(fn($r) => (int)($r['femmes']??0), $parCategorie)) ?>, backgroundColor:'#dc3545' },
            ]
        },
        options: {
            responsive:true, maintainAspectRatio:false,
            plugins: { legend:{position:'top'} },
            scales: { x:{ stacked:false }, y:{ beginAtZero:true, ticks:{stepSize:1} } }
        }
    });
})();

// ── Graphique régions ──
(function(){
    const ctx = document.getElementById('chart-regions');
    const labels = <?= json_encode(array_column($parRegion, 'region')) ?>;
    const vals   = <?= json_encode(array_map(fn($r) => (int)($r['total_emplois']??0), $parRegion)) ?>;
    if (!ctx || vals.every(v=>v===0)) return;
    new Chart(ctx, {
        type: 'horizontalBar' in Chart.registry ? 'horizontalBar' : 'bar',
        data: {
            labels,
            datasets:[{ label:'Emplois formels', data:vals, backgroundColor: colors.slice(0,labels.length) }]
        },
        options: {
            indexAxis:'y', responsive:true, maintainAspectRatio:false,
            plugins:{legend:{display:false}},
            scales:{x:{beginAtZero:true}}
        }
    });
})();

// ── Graphique niveaux ──
(function(){
    const ctx = document.getElementById('chart-niveaux');
    if (!ctx) return;
    const nivKeys  = <?= json_encode(array_keys(NIVEAUX_INSTRUCTION)) ?>;
    const nivLabels = <?= json_encode(array_values(NIVEAUX_INSTRUCTION)) ?>;
    const nivMap = {};
    <?php foreach ($parNiveau as $row): ?>
    nivMap['<?= $row['niveau'] ?>'] = <?= (int)($row['total']??0) ?>;
    <?php endforeach; ?>
    const vals = nivKeys.map(k => nivMap[k]||0);
    new Chart(ctx, {
        type:'doughnut',
        data: { labels: nivLabels, datasets:[{ data:vals, backgroundColor:colors }] },
        options: { responsive:true, maintainAspectRatio:false, plugins:{legend:{position:'right', labels:{font:{size:10}}}} }
    });
})();

// ── Graphique pertes ──
(function(){
    const ctx = document.getElementById('chart-pertes');
    if (!ctx) return;
    const labels = <?= json_encode(array_map(fn($r) => MOTIFS_PERTE_EMPLOI[$r['motif']] ?? $r['motif'], $pertesEmploi)) ?>;
    const vals   = <?= json_encode(array_column($pertesEmploi,'total')) ?>;
    new Chart(ctx, {
        type:'pie',
        data:{ labels, datasets:[{ data:vals, backgroundColor:colors }] },
        options:{ responsive:true, maintainAspectRatio:false, plugins:{legend:{position:'right', labels:{font:{size:10}}}} }
    });
})();

// ── Graphique perspectives ──
(function(){
    const ctx = document.getElementById('chart-perspectives');
    if (!ctx) return;
    const perspLabels = {stable:'Stable',hausse:'En hausse',baisse:'En baisse',inconnue:'Inconnue'};
    const rawLabels = <?= json_encode(array_column($perspectives,'perspective')) ?>;
    const labels = rawLabels.map(k => perspLabels[k]||k);
    const vals   = <?= json_encode(array_column($perspectives,'total')) ?>;
    new Chart(ctx, {
        type:'doughnut',
        data:{ labels, datasets:[{ data:vals, backgroundColor:['#198754','#0d6efd','#dc3545','#6c757d'] }] },
        options:{ responsive:true, maintainAspectRatio:false, plugins:{legend:{position:'bottom'}} }
    });
})();
</script>
<?php endif; ?>
