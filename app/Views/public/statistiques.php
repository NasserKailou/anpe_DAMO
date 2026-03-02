<?php // Vue : Statistiques publiques ?>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h1 class="mb-1">Statistiques DAMO</h1>
            <p class="text-muted">Données consolidées des déclarations validées</p>
        </div>
        <form method="GET" action="/statistiques" class="d-flex gap-2">
            <select name="annee" class="form-select form-select-sm" style="width:auto">
                <?php foreach ($anneesDisponibles as $a): ?>
                <option value="<?= $a['annee'] ?>" <?= $a['annee'] == $anneeSelectionnee ? 'selected' : '' ?>><?= $a['annee'] ?></option>
                <?php endforeach; ?>
                <option value="<?= date('Y') ?>" <?= date('Y') == $anneeSelectionnee ? 'selected' : '' ?>><?= date('Y') ?></option>
            </select>
            <button type="submit" class="btn btn-primary btn-sm">OK</button>
        </form>
    </div>

    <!-- Charts -->
    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card"><div class="card-header"><h5>Effectifs par région</h5></div>
                <div class="card-body"><div style="height:320px"><canvas id="chartRegion"></canvas></div></div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card"><div class="card-header"><h5>Perspectives d'emploi</h5></div>
                <div class="card-body"><div style="height:280px"><canvas id="chartPersp"></canvas></div></div>
            </div>
        </div>
    </div>

    <!-- Tableau effectifs par catégorie -->
    <?php if (!empty($parCategorie)): ?>
    <div class="card mb-4">
        <div class="card-header"><h5>Effectifs par catégorie professionnelle</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead><tr><th>Catégorie</th><th class="text-center">Hommes</th><th class="text-center">Femmes</th><th class="text-center">Nigériens</th><th class="text-center">Étrangers</th><th class="text-center">Total</th></tr></thead>
                    <tbody>
                        <?php foreach ($parCategorie as $c):
                            $total = ($c['hommes'] ?? 0) + ($c['femmes'] ?? 0);
                        ?>
                        <tr>
                            <td><?= e(CATEGORIES_PROFESSIONNELLES[$c['categorie']] ?? $c['categorie']) ?></td>
                            <td class="text-center"><?= formatNumber($c['hommes'] ?? 0) ?></td>
                            <td class="text-center"><?= formatNumber($c['femmes'] ?? 0) ?></td>
                            <td class="text-center"><?= formatNumber($c['nigeriens'] ?? 0) ?></td>
                            <td class="text-center"><?= formatNumber($c['etrangers'] ?? 0) ?></td>
                            <td class="text-center fw-bold"><?= formatNumber($total) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Pertes d'emploi -->
    <?php if (!empty($pertesEmploi)): ?>
    <div class="card mb-4">
        <div class="card-header"><h5>Pertes d'emploi par motif</h5></div>
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead><tr><th>Motif</th><th class="text-center">Hommes</th><th class="text-center">Femmes</th><th class="text-center">Total</th></tr></thead>
                <tbody>
                <?php foreach ($pertesEmploi as $p): ?>
                <tr>
                    <td><?= e(MOTIFS_PERTE_EMPLOI[$p['motif']] ?? $p['motif']) ?></td>
                    <td class="text-center"><?= formatNumber($p['hommes'] ?? 0) ?></td>
                    <td class="text-center"><?= formatNumber($p['femmes'] ?? 0) ?></td>
                    <td class="text-center fw-bold"><?= formatNumber($p['total'] ?? 0) ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const regionData = <?= json_encode(array_map(fn($r) => ['region' => $r['region'], 'total' => (int)($r['total_emplois'] ?? 0)], $parRegion)) ?>;
    if (regionData.length) {
        initChart('chartRegion', 'bar', {
            labels: regionData.map(r => r.region),
            datasets: [{ label: 'Emplois', data: regionData.map(r => r.total), backgroundColor: '#0d47a1' }]
        }, { scales: { y: { beginAtZero: true } }, plugins: { legend: { display: false } } });
    }

    const perspData = <?= json_encode(array_map(fn($p) => ['label' => $p['perspective'], 'val' => (int)$p['total']], $perspectives)) ?>;
    const perspLabels = { hausse: '↑ Hausse', stabilite: '→ Stabilité', baisse: '↓ Baisse' };
    if (perspData.length) {
        initChart('chartPersp', 'doughnut', {
            labels: perspData.map(p => perspLabels[p.label] ?? p.label),
            datasets: [{ data: perspData.map(p => p.val), backgroundColor: ['#28a745','#ffc107','#dc3545'] }]
        });
    }
});
</script>
