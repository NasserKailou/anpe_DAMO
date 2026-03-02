<?php // Vue : Statistiques Admin ?>

<div class="page-header">
    <div>
        <h1><i class="bi bi-bar-chart me-2 text-primary"></i>Statistiques DAMO</h1>
        <p>Campagne : <strong><?= $campagne ? e($campagne['libelle']) . ' (' . $campagne['annee'] . ')' : 'Aucune' ?></strong></p>
    </div>
</div>

<!-- Effectifs par catégorie -->
<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header">
                <h5><i class="bi bi-people me-2"></i>Effectifs par catégorie professionnelle</h5>
            </div>
            <div class="card-body">
                <div style="height:320px"><canvas id="chartCategories"></canvas></div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">
                <h5><i class="bi bi-pie-chart me-2"></i>Statuts déclarations</h5>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center flex-column">
                <div style="height:220px;width:220px"><canvas id="chartStatuts"></canvas></div>
            </div>
        </div>
    </div>
</div>

<!-- Tableau effectifs par région -->
<div class="card mb-4">
    <div class="card-header">
        <h5><i class="bi bi-geo-alt me-2"></i>Effectifs par région</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Région</th>
                        <th class="text-center">Déclarations validées</th>
                        <th class="text-center">Total emplois</th>
                        <th>% du total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $totalEmplois = array_sum(array_column($effectifsParRegion, 'total_emplois'));
                    foreach ($effectifsParRegion as $r):
                        $pct = $totalEmplois > 0 ? round(($r['total_emplois'] / $totalEmplois) * 100, 1) : 0;
                    ?>
                    <tr>
                        <td><strong><?= e($r['region']) ?></strong></td>
                        <td class="text-center"><?= formatNumber($r['nb_declarations']) ?></td>
                        <td class="text-center"><?= formatNumber($r['total_emplois'] ?? 0) ?></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div style="width:120px;background:#e0e0e0;border-radius:4px;height:8px">
                                    <div style="width:<?= $pct ?>%;background:#0d47a1;border-radius:4px;height:8px"></div>
                                </div>
                                <small><?= $pct ?>%</small>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Graphique catégories
    const catData = <?= json_encode(array_map(fn($c) => [
        'label' => CATEGORIES_PROFESSIONNELLES[$c['categorie']] ?? $c['categorie'],
        'hommes' => (int)$c['hommes'],
        'femmes' => (int)$c['femmes'],
    ], $effectifsParCategorie)) ?>;
    
    if (catData.length) {
        initChart('chartCategories', 'bar', {
            labels: catData.map(c => c.label),
            datasets: [
                { label: 'Hommes', data: catData.map(c => c.hommes), backgroundColor: '#1565c0' },
                { label: 'Femmes', data: catData.map(c => c.femmes), backgroundColor: '#e91e63' }
            ]
        }, { scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true } } });
    }
    
    // Graphique statuts
    const statutData = <?= json_encode(array_map(fn($s) => ['statut' => $s['statut'], 'total' => (int)$s['total']], $parStatut)) ?>;
    const colors = { brouillon: '#ffc107', soumise: '#17a2b8', validee: '#28a745', rejetee: '#dc3545', corrigee: '#6c757d' };
    const labels = { brouillon: 'Brouillon', soumise: 'Soumises', validee: 'Validées', rejetee: 'Rejetées', corrigee: 'Corrigées' };
    
    if (statutData.length) {
        initChart('chartStatuts', 'doughnut', {
            labels: statutData.map(s => labels[s.statut] ?? s.statut),
            datasets: [{ data: statutData.map(s => s.total), backgroundColor: statutData.map(s => colors[s.statut] ?? '#999') }]
        });
    }
});
</script>
