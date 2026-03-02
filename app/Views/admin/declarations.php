<?php // Vue : Liste des déclarations (Admin) ?>

<div class="page-header">
    <div>
        <h1><i class="bi bi-file-earmark-text me-2 text-primary"></i>Déclarations</h1>
        <p>Total : <strong><?= formatNumber($total) ?></strong> déclaration(s)</p>
    </div>
    <div class="page-header-right">
        <a href="/admin/export/declarations" class="btn btn-success btn-sm">
            <i class="bi bi-download me-1"></i>Exporter CSV
        </a>
    </div>
</div>

<!-- Filtres -->
<div class="filters-bar">
    <form method="GET" action="/admin/declarations">
        <div>
            <input type="text" name="q" value="<?= e($filters['search']) ?>"
                class="form-control form-control-sm" placeholder="Rechercher...">
        </div>
        <div>
            <select name="statut" class="form-select form-select-sm">
                <option value="">Tous statuts</option>
                <?php foreach (['brouillon','soumise','validee','rejetee','corrigee'] as $s): ?>
                <option value="<?= $s ?>" <?= $filters['statut'] === $s ? 'selected' : '' ?>><?= statutLabel($s) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <select name="region" class="form-select form-select-sm">
                <option value="">Toutes régions</option>
                <?php foreach ($regions as $r): ?>
                <option value="<?= $r['id'] ?>" <?= $filters['region'] == $r['id'] ? 'selected' : '' ?>><?= e($r['nom']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <select name="campagne" class="form-select form-select-sm">
                <option value="">Toutes campagnes</option>
                <?php foreach ($campagnes as $c): ?>
                <option value="<?= $c['id'] ?>" <?= $filters['campagne'] == $c['id'] ? 'selected' : '' ?>><?= e($c['annee']) ?> - <?= e($c['libelle']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search me-1"></i>Filtrer</button>
        <a href="/admin/declarations" class="btn btn-outline-secondary btn-sm">Réinitialiser</a>
    </form>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Entreprise</th>
                        <th>N° CNSS</th>
                        <th>Région</th>
                        <th>Année</th>
                        <th>Agent</th>
                        <th>Statut</th>
                        <th>Date soumission</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($declarations as $d): ?>
                    <tr>
                        <td><code><?= e($d['code_questionnaire']) ?></code></td>
                        <td>
                            <strong><?= e(truncate($d['raison_sociale'], 35)) ?></strong>
                        </td>
                        <td><?= e($d['numero_cnss'] ?? '-') ?></td>
                        <td><?= e($d['region_nom']) ?></td>
                        <td><?= e($d['annee']) ?></td>
                        <td><?= e($d['agent_prenom'] . ' ' . $d['agent_nom']) ?></td>
                        <td>
                            <span class="badge-statut badge-<?= e($d['statut']) ?>">
                                <?= statutLabel($d['statut']) ?>
                            </span>
                        </td>
                        <td><small><?= $d['date_soumission'] ? formatDate($d['date_soumission']) : '-' ?></small></td>
                        <td>
                            <a href="/admin/declaration/<?= $d['id'] ?>" class="btn btn-sm btn-icon btn-outline-primary" title="Voir">
                                <i class="bi bi-eye"></i>
                            </a>
                            <?php if ($d['statut'] === 'soumise'): ?>
                            <button class="btn btn-sm btn-icon btn-success" title="Valider" onclick="validerDeclaration(<?= $d['id'] ?>)">
                                <i class="bi bi-check-lg"></i>
                            </button>
                            <button class="btn btn-sm btn-icon btn-danger" title="Rejeter" onclick="rejeterDeclaration(<?= $d['id'] ?>)">
                                <i class="bi bi-x-lg"></i>
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($declarations)): ?>
                    <tr><td colspan="9" class="text-center text-muted py-5">Aucune déclaration trouvée</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Pagination -->
<?php if ($pagination['total_pages'] > 1): ?>
<nav class="mt-3">
    <ul class="pagination justify-content-center">
        <?php if ($pagination['has_prev']): ?>
        <li class="page-item">
            <a class="page-link" href="?page=<?= $pagination['prev_page'] ?>&q=<?= e($filters['search']) ?>&statut=<?= e($filters['statut']) ?>&region=<?= e($filters['region']) ?>">
                &laquo; Précédent
            </a>
        </li>
        <?php endif; ?>
        <li class="page-item disabled">
            <span class="page-link">Page <?= $pagination['current_page'] ?> / <?= $pagination['total_pages'] ?></span>
        </li>
        <?php if ($pagination['has_next']): ?>
        <li class="page-item">
            <a class="page-link" href="?page=<?= $pagination['next_page'] ?>&q=<?= e($filters['search']) ?>&statut=<?= e($filters['statut']) ?>&region=<?= e($filters['region']) ?>">
                Suivant &raquo;
            </a>
        </li>
        <?php endif; ?>
    </ul>
</nav>
<?php endif; ?>
