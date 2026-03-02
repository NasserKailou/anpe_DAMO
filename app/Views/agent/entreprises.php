<?php
/**
 * Vue liste des entreprises de la région (agent)
 */
$entreprises = $entreprises ?? [];
$pagination  = $pagination  ?? [];
$branches    = $branches    ?? [];
$filters     = $filters     ?? [];
$total       = $total       ?? 0;
?>

<!-- En-tête avec stats -->
<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="p-3 bg-primary bg-opacity-10 rounded">
                    <i class="bi bi-building text-primary fs-4"></i>
                </div>
                <div>
                    <div class="fs-4 fw-bold text-primary"><?= number_format($total) ?></div>
                    <div class="text-muted small">Entreprises dans votre région</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div class="text-muted small">
                    <i class="bi bi-info-circle me-1"></i>
                    Gérez les entreprises de votre région. Seules les entreprises actives peuvent faire l'objet d'une déclaration DAMO.
                </div>
                <div class="d-flex gap-2">
                    <a href="<?= url('agent/entreprise/nouvelle') ?>" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-lg me-1"></i>Nouvelle entreprise
                    </a>
                    <a href="<?= url('agent/import/entreprises') ?>" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-upload me-1"></i>Import CSV
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filtres -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-5">
                <input type="text" name="q" class="form-control form-control-sm"
                       placeholder="Rechercher raison sociale, N° CNSS…"
                       value="<?= e($filters['search'] ?? '') ?>">
            </div>
            <div class="col-md-4">
                <select name="branche" class="form-select form-select-sm">
                    <option value="">Toutes les branches</option>
                    <?php foreach ($branches as $b): ?>
                        <option value="<?= $b['id'] ?>" <?= ($filters['branche']??'') == $b['id'] ? 'selected' : '' ?>>
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
                    <a href="<?= url('agent/entreprises') ?>" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Tableau des entreprises -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between py-2">
        <span class="fw-semibold small text-muted">
            <i class="bi bi-list me-1"></i>
            <?= number_format($total) ?> entreprise<?= $total > 1 ? 's' : '' ?>
            <?php if ($filters['search'] ?? ''): ?>
                pour «&nbsp;<strong><?= e($filters['search']) ?></strong>&nbsp;»
            <?php endif; ?>
        </span>
    </div>
    <div class="card-body p-0">
        <?php if (empty($entreprises)): ?>
            <div class="text-center py-5">
                <i class="bi bi-building text-muted" style="font-size: 2.5rem"></i>
                <p class="text-muted mt-2">Aucune entreprise trouvée.</p>
                <a href="<?= url('agent/entreprise/nouvelle') ?>" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg me-1"></i>Ajouter une entreprise
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Raison sociale</th>
                            <th>N° CNSS</th>
                            <th>Branche</th>
                            <th>Localité</th>
                            <th class="text-center">Déclarations</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($entreprises as $ent): ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?= e($ent['raison_sociale']) ?></div>
                                    <?php if ($ent['activite_principale'] ?? ''): ?>
                                        <small class="text-muted"><?= e(mb_substr($ent['activite_principale'],0,50)) ?><?= strlen($ent['activite_principale'])>50 ? '…' : '' ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($ent['numero_cnss'] ?? ''): ?>
                                        <span class="font-monospace small"><?= e($ent['numero_cnss']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted fst-italic small">Non renseigné</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($ent['branche_libelle'] ?? ''): ?>
                                        <span class="badge bg-info text-dark" style="font-size:.72rem"><?= e($ent['branche_libelle']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td><small><?= e($ent['localite'] ?? '—') ?></small></td>
                                <td class="text-center">
                                    <span class="badge bg-<?= ($ent['nb_declarations']??0)>0 ? 'primary' : 'secondary' ?>">
                                        <?= (int)($ent['nb_declarations']??0) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="<?= url("agent/entreprise/{$ent['id']}/modifier") ?>"
                                       class="btn btn-sm btn-outline-primary" title="Modifier">
                                        <i class="bi bi-pencil"></i>
                                    </a>
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
                        Affichage <?= $pagination['offset']+1 ?>–<?= min($pagination['offset']+$pagination['per_page'], $total) ?> sur <?= number_format($total) ?>
                    </small>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <?php for ($p = 1; $p <= $pagination['total_pages']; $p++): ?>
                                <li class="page-item <?= $p === $pagination['current_page'] ? 'active' : '' ?>">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page'=>$p])) ?>"><?= $p ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
