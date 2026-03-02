<?php defined('EDAMO') or die('Accès direct interdit'); ?>

<!-- Hero filtres -->
<section class="bg-primary text-white py-4">
    <div class="container">
        <h2 class="h4 fw-bold mb-1">
            <i class="bi bi-database me-2"></i>Données ouvertes — Emploi formel
        </h2>
        <p class="mb-3 opacity-75 small">Accédez aux données validées des déclarations de main d'œuvre au Niger.</p>

        <form method="GET" action="<?= url('donnees') ?>" class="row g-2">
            <div class="col-md-3">
                <select name="campagne" class="form-select form-select-sm">
                    <option value="">Toutes les campagnes</option>
                    <?php foreach ($campagnes ?? [] as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= ($filters['campagneId'] ?? 0) == $c['id'] ? 'selected' : '' ?>>
                        Campagne <?= $c['annee'] ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <select name="region" class="form-select form-select-sm">
                    <option value="">Toutes les régions</option>
                    <?php foreach ($regions ?? [] as $r): ?>
                    <option value="<?= $r['id'] ?>" <?= ($filters['regionId'] ?? 0) == $r['id'] ? 'selected' : '' ?>>
                        <?= e($r['nom']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <select name="branche" class="form-select form-select-sm">
                    <option value="">Toutes les branches</option>
                    <?php foreach ($branches ?? [] as $b): ?>
                    <option value="<?= $b['id'] ?>" <?= ($filters['brancheId'] ?? 0) == $b['id'] ? 'selected' : '' ?>>
                        <?= e($b['libelle']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-light btn-sm fw-semibold">
                    <i class="bi bi-funnel me-1"></i>Filtrer
                </button>
                <a href="<?= url('donnees') ?>" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-x"></i> Réinitialiser
                </a>
                <?php if (!empty($resultats)): ?>
                <a href="<?= url('admin/export/entreprises') ?>" class="btn btn-warning btn-sm fw-semibold ms-auto">
                    <i class="bi bi-download me-1"></i>CSV
                </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <!-- Compteur résultats -->
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
            <div>
                <h5 class="mb-0 fw-semibold">
                    Résultats
                    <span class="badge bg-primary ms-2"><?= number_format($total ?? 0) ?></span>
                </h5>
                <small class="text-muted">
                    <?php
                    $activeFilters = array_filter([
                        isset($filters['campagneId']) && $filters['campagneId'] ? 'Campagne filtrée' : null,
                        isset($filters['regionId'])   && $filters['regionId']   ? 'Région filtrée' : null,
                        isset($filters['brancheId'])  && $filters['brancheId']  ? 'Branche filtrée' : null,
                    ]);
                    echo implode(' — ', $activeFilters) ?: 'Toutes les déclarations validées';
                    ?>
                </small>
            </div>
        </div>

        <?php if (empty($resultats)): ?>
        <div class="text-center py-5">
            <i class="bi bi-inbox display-3 text-muted opacity-30 d-block mb-3"></i>
            <h5 class="text-muted">Aucune donnée pour les critères sélectionnés</h5>
            <p class="text-muted small">Modifiez vos filtres ou consultez d'autres années.</p>
            <a href="<?= url('donnees') ?>" class="btn btn-outline-primary mt-2">
                <i class="bi bi-x-circle me-1"></i>Effacer les filtres
            </a>
        </div>
        <?php else: ?>

        <!-- Tableau de données -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Entreprise</th>
                                <th>N° CNSS</th>
                                <th>Branche</th>
                                <th>Région</th>
                                <th class="text-center">Année</th>
                                <th class="text-center">Effectif moy.</th>
                                <th class="text-center">Masse salariale</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($resultats as $row): ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?= e($row['raison_sociale']) ?></div>
                                    <?php if (!empty($row['activite_principale'])): ?>
                                    <small class="text-muted"><?= e(mb_strimwidth($row['activite_principale'], 0, 40, '…')) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($row['numero_cnss'])): ?>
                                    <code class="small text-primary"><?= e($row['numero_cnss']) ?></code>
                                    <?php else: ?>
                                    <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border small">
                                        <?= e($row['branche'] ?? '—') ?>
                                    </span>
                                </td>
                                <td class="text-muted"><?= e($row['region']) ?></td>
                                <td class="text-center">
                                    <span class="badge bg-secondary"><?= $row['annee'] ?></span>
                                </td>
                                <td class="text-center fw-semibold text-primary">
                                    <?= $row['effectif_moyen'] ? number_format(round($row['effectif_moyen'])) : '—' ?>
                                </td>
                                <td class="text-center text-muted small">
                                    <?= $row['masse_salariale'] ? number_format($row['masse_salariale']) . ' F' : '—' ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <?php if (($pagination['total_pages'] ?? 1) > 1): ?>
            <div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
                <small class="text-muted">
                    Page <?= $pagination['page'] ?> / <?= $pagination['total_pages'] ?>
                    (<?= number_format($total) ?> résultats)
                </small>
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <?php if ($pagination['page'] > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $pagination['page']-1])) ?>">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php for ($p = max(1, $pagination['page']-2); $p <= min($pagination['total_pages'], $pagination['page']+2); $p++): ?>
                        <li class="page-item <?= $p == $pagination['page'] ? 'active' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $p])) ?>"><?= $p ?></a>
                        </li>
                        <?php endfor; ?>
                        <?php if ($pagination['page'] < $pagination['total_pages']): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $pagination['page']+1])) ?>">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
        </div>

        <!-- Note légale -->
        <div class="alert alert-light border mt-4 small text-muted">
            <i class="bi bi-info-circle me-2"></i>
            Ces données sont issues des déclarations de main d'œuvre validées par l'ANPE Niger.
            Elles sont publiées dans le cadre de la politique de transparence et d'ouverture des données publiques.
            Toute réutilisation doit mentionner la source : <strong>ANPE Niger — e-DAMO</strong>.
        </div>

        <?php endif; ?>
    </div>
</section>
