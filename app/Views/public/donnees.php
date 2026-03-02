<?php // Vue : Données ouvertes ?>
<div class="container py-5">
    <h1 class="mb-1">Données ouvertes</h1>
    <p class="text-muted mb-4">Explorez les déclarations validées par l'ANPE Niger</p>

    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="/donnees" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Année / Campagne</label>
                    <select name="campagne" class="form-select form-select-sm">
                        <option value="">Toutes les années</option>
                        <?php foreach ($campagnes as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $filters['campagneId'] == $c['id'] ? 'selected' : '' ?>><?= e($c['annee']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Région</label>
                    <select name="region" class="form-select form-select-sm">
                        <option value="">Toutes les régions</option>
                        <?php foreach ($regions as $r): ?>
                        <option value="<?= $r['id'] ?>" <?= $filters['regionId'] == $r['id'] ? 'selected' : '' ?>><?= e($r['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Branche</label>
                    <select name="branche" class="form-select form-select-sm">
                        <option value="">Toutes branches</option>
                        <?php foreach ($branches as $b): ?>
                        <option value="<?= $b['id'] ?>" <?= $filters['brancheId'] == $b['id'] ? 'selected' : '' ?>><?= e($b['libelle']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-search me-1"></i>Filtrer</button>
                </div>
            </form>
        </div>
    </div>

    <p class="text-muted mb-3"><strong><?= formatNumber($total) ?></strong> résultat(s) trouvé(s)</p>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead><tr><th>Entreprise</th><th>Région</th><th>Branche</th><th>Année</th><th class="text-center">Effectif moyen</th></tr></thead>
                    <tbody>
                    <?php foreach ($resultats as $r): ?>
                    <tr>
                        <td>
                            <strong><?= e($r['raison_sociale']) ?></strong>
                            <?php if ($r['numero_cnss']): ?><br><small class="text-muted">CNSS: <?= e($r['numero_cnss']) ?></small><?php endif; ?>
                        </td>
                        <td><?= e($r['region']) ?></td>
                        <td><small><?= e($r['branche'] ?? '-') ?></small></td>
                        <td><?= e($r['annee']) ?></td>
                        <td class="text-center"><?= $r['effectif_moyen'] ? formatNumber(round($r['effectif_moyen'])) : '-' ?></td>
                    </tr>
                    <?php endforeach; if (empty($resultats)): ?>
                    <tr><td colspan="5" class="text-center py-4 text-muted">Aucune donnée disponible</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php if ($pagination['total_pages'] > 1): ?>
    <nav class="mt-3"><ul class="pagination justify-content-center">
        <?php if ($pagination['has_prev']): ?><li class="page-item"><a class="page-link" href="?page=<?= $pagination['prev_page'] ?>">&laquo;</a></li><?php endif; ?>
        <li class="page-item disabled"><span class="page-link">Page <?= $pagination['current_page'] ?>/<?= $pagination['total_pages'] ?></span></li>
        <?php if ($pagination['has_next']): ?><li class="page-item"><a class="page-link" href="?page=<?= $pagination['next_page'] ?>">&raquo;</a></li><?php endif; ?>
    </ul></nav>
    <?php endif; ?>
</div>
