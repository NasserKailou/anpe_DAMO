<?php
/**
 * Vue publique des données ouvertes - e-DAMO
 * Accès aux déclarations validées avec filtres avancés
 */
$resultats  = $resultats  ?? [];
$pagination = $pagination ?? [];
$total      = $total      ?? 0;
$campagnes  = $campagnes  ?? [];
$regions    = $regions    ?? [];
$branches   = $branches   ?? [];
$filters    = $filters    ?? [];

$campagneId = $filters['campagneId'] ?? 0;
$regionId   = $filters['regionId']   ?? 0;
$brancheId  = $filters['brancheId']  ?? 0;
?>

<!-- En-tête -->
<div class="row mb-4">
    <div class="col-12">
        <div class="p-4 bg-white border-0 shadow-sm rounded">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <h4 class="fw-bold mb-1">
                        <i class="bi bi-database me-2 text-primary"></i>Données ouvertes DAMO
                    </h4>
                    <p class="text-muted mb-0">
                        Accédez aux données publiées sur l'emploi formel au Niger. Filtrez par campagne, région ou branche d'activité.
                        <strong><?= number_format($total) ?> déclaration<?= $total > 1 ? 's' : '' ?> publiée<?= $total > 1 ? 's' : '' ?></strong>.
                    </p>
                </div>
                <div class="col-md-5 text-md-end mt-3 mt-md-0">
                    <button type="button" class="btn btn-success" id="btn-export-csv"
                            <?= $total === 0 ? 'disabled' : '' ?>>
                        <i class="bi bi-download me-1"></i>Exporter CSV
                    </button>
                    <a href="<?= url('statistiques') ?>" class="btn btn-outline-primary ms-2">
                        <i class="bi bi-bar-chart me-1"></i>Voir les graphiques
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filtres -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white border-bottom py-2">
        <span class="fw-semibold small"><i class="bi bi-funnel me-1 text-primary"></i>Filtres de recherche</span>
    </div>
    <div class="card-body py-3">
        <form method="GET" id="form-filters">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Campagne / Année</label>
                    <select name="campagne" class="form-select form-select-sm">
                        <option value="">Toutes les campagnes</option>
                        <?php foreach ($campagnes as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= $campagneId == $c['id'] ? 'selected' : '' ?>>
                                Campagne <?= $c['annee'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Région</label>
                    <select name="region" class="form-select form-select-sm">
                        <option value="">Toutes les régions</option>
                        <?php foreach ($regions as $r): ?>
                            <option value="<?= $r['id'] ?>" <?= $regionId == $r['id'] ? 'selected' : '' ?>>
                                <?= e($r['nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Branche d'activité</label>
                    <select name="branche" class="form-select form-select-sm">
                        <option value="">Toutes les branches</option>
                        <?php foreach ($branches as $b): ?>
                            <option value="<?= $b['id'] ?>" <?= $brancheId == $b['id'] ? 'selected' : '' ?>>
                                <?= e($b['libelle']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm flex-fill">
                            <i class="bi bi-search me-1"></i>Filtrer
                        </button>
                        <a href="<?= url('donnees') ?>" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Résultats actifs -->
<?php if ($campagneId || $regionId || $brancheId): ?>
    <div class="d-flex flex-wrap gap-2 mb-3">
        <small class="text-muted align-self-center">Filtres actifs :</small>
        <?php foreach ($campagnes as $c): if ($campagneId == $c['id']): ?>
            <span class="badge bg-primary d-flex align-items-center gap-1">
                Campagne <?= $c['annee'] ?>
                <a href="?<?= http_build_query(array_merge($filters, ['campagneId'=>0, 'campagne'=>''])) ?>" class="text-white text-decoration-none ms-1">×</a>
            </span>
        <?php endif; endforeach; ?>
        <?php foreach ($regions as $r): if ($regionId == $r['id']): ?>
            <span class="badge bg-success d-flex align-items-center gap-1">
                <?= e($r['nom']) ?>
                <a href="?<?= http_build_query(array_merge($filters, ['regionId'=>0, 'region'=>''])) ?>" class="text-white text-decoration-none ms-1">×</a>
            </span>
        <?php endif; endforeach; ?>
        <?php foreach ($branches as $b): if ($brancheId == $b['id']): ?>
            <span class="badge bg-info text-dark d-flex align-items-center gap-1">
                <?= e($b['libelle']) ?>
                <a href="?<?= http_build_query(array_merge($filters, ['brancheId'=>0, 'branche'=>''])) ?>" class="text-dark text-decoration-none ms-1">×</a>
            </span>
        <?php endif; endforeach; ?>
    </div>
<?php endif; ?>

<!-- Tableau des données -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-2">
        <span class="fw-semibold small text-muted">
            <?= number_format($total) ?> résultat<?= $total > 1 ? 's' : '' ?>
        </span>
        <small class="text-muted">
            <i class="bi bi-lock me-1"></i>Données anonymisées — déclarations validées uniquement
        </small>
    </div>
    <div class="card-body p-0">
        <?php if (empty($resultats)): ?>
            <div class="text-center py-5">
                <i class="bi bi-database text-muted" style="font-size:2.5rem"></i>
                <p class="text-muted mt-2 mb-0">Aucune donnée publiée pour les critères sélectionnés.</p>
                <?php if ($campagneId || $regionId || $brancheId): ?>
                    <a href="<?= url('donnees') ?>" class="btn btn-outline-primary btn-sm mt-2">
                        <i class="bi bi-x-circle me-1"></i>Retirer tous les filtres
                    </a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="table-donnees">
                    <thead class="table-light">
                        <tr>
                            <th>Entreprise</th>
                            <th>Branche</th>
                            <th>Région</th>
                            <th class="text-center">Année</th>
                            <th class="text-center">Effectif moyen</th>
                            <th class="text-center">Masse salariale</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($resultats as $row): ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?= e($row['raison_sociale']) ?></div>
                                    <?php if ($row['activite_principale'] ?? ''): ?>
                                        <small class="text-muted"><?= e(mb_substr($row['activite_principale'],0,60)) ?><?= mb_strlen($row['activite_principale'])>60 ? '…':'' ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($row['branche'] ?? ''): ?>
                                        <span class="badge bg-info text-dark" style="font-size:.72rem"><?= e($row['branche']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td><small class="fw-semibold"><?= e($row['region']) ?></small></td>
                                <td class="text-center"><span class="badge bg-secondary"><?= $row['annee'] ?></span></td>
                                <td class="text-center fw-semibold">
                                    <?php if ($row['effectif_moyen'] ?? ''): ?>
                                        <?= number_format((float)$row['effectif_moyen'],1,',','&nbsp;') ?>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($row['masse_salariale'] ?? ''): ?>
                                        <?= number_format((float)$row['masse_salariale'],0,',',' ') ?>&nbsp;FCFA
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if (($pagination['total_pages'] ?? 1) > 1): ?>
                <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top">
                    <small class="text-muted">
                        Affichage <?= ($pagination['offset']??0)+1 ?>–<?= min(($pagination['offset']??0)+($pagination['per_page']??20), $total) ?>
                        sur <?= number_format($total) ?>
                    </small>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <?php
                            $curPage = $pagination['current_page'] ?? 1;
                            $totPages = $pagination['total_pages'] ?? 1;
                            $range = range(max(1, $curPage-2), min($totPages, $curPage+2));
                            if (!in_array(1,$range)) { ?>
                                <li class="page-item"><a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page'=>1])) ?>">1</a></li>
                                <?php if (!in_array(2,$range)): ?><li class="page-item disabled"><span class="page-link">…</span></li><?php endif; ?>
                            <?php } ?>
                            <?php foreach ($range as $p): ?>
                                <li class="page-item <?= $p === $curPage ? 'active' : '' ?>">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page'=>$p])) ?>"><?= $p ?></a>
                                </li>
                            <?php endforeach; ?>
                            <?php if (!in_array($totPages,$range) && $totPages > 1): ?>
                                <?php if (!in_array($totPages-1,$range)): ?><li class="page-item disabled"><span class="page-link">…</span></li><?php endif; ?>
                                <li class="page-item"><a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page'=>$totPages])) ?>"><?= $totPages ?></a></li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Export CSV côté client -->
<script>
document.getElementById('btn-export-csv')?.addEventListener('click', function() {
    const rows = document.querySelectorAll('#table-donnees tr');
    if (!rows.length) return;
    let csv = [];
    rows.forEach(r => {
        const cells = r.querySelectorAll('th, td');
        const row = [];
        cells.forEach(c => {
            // Récupérer le texte brut (pas HTML)
            let t = c.innerText.replace(/\s+/g,' ').trim();
            // Échapper guillemets
            t = '"' + t.replace(/"/g,'""') + '"';
            row.push(t);
        });
        csv.push(row.join(';'));
    });
    const blob = new Blob(['\ufeff' + csv.join('\n')], {type:'text/csv;charset=utf-8;'});
    const url  = URL.createObjectURL(blob);
    const a    = document.createElement('a');
    a.href     = url;
    a.download = 'donnees_damo_export.csv';
    a.click();
    URL.revokeObjectURL(url);
});
</script>
