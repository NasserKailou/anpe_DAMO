<?php // Vue : Liste Déclarations Agent ?>
<div class="page-header">
    <div>
        <h1><i class="bi bi-file-earmark-text me-2 text-primary"></i>Mes déclarations</h1>
        <p>Total : <strong><?= formatNumber($total) ?></strong></p>
    </div>
    <a href="/agent/declaration/nouvelle" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i>Nouvelle déclaration
    </a>
</div>
<div class="filters-bar">
    <form method="GET" action="/agent/declarations">
        <input type="text" name="q" value="<?= e($filters['search']) ?>" class="form-control form-control-sm" placeholder="Rechercher...">
        <select name="statut" class="form-select form-select-sm">
            <option value="">Tous statuts</option>
            <?php foreach (['brouillon','soumise','validee','rejetee','corrigee'] as $s): ?>
            <option value="<?= $s ?>" <?= $filters['statut'] === $s ? 'selected' : '' ?>><?= statutLabel($s) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search me-1"></i>Filtrer</button>
        <a href="/agent/declarations" class="btn btn-outline-secondary btn-sm">Reset</a>
    </form>
</div>
<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead><tr>
                <th>Code</th><th>Entreprise</th><th>N° CNSS</th>
                <th>Année</th><th>Statut</th><th>Avancement</th><th>Mis à jour</th><th>Actions</th>
            </tr></thead>
            <tbody>
            <?php foreach ($declarations as $d): ?>
            <tr>
                <td><code><?= e($d['code_questionnaire']) ?></code></td>
                <td><strong><?= e(truncate($d['raison_sociale'], 35)) ?></strong></td>
                <td><?= e($d['numero_cnss'] ?? '-') ?></td>
                <td><?= e($d['annee']) ?></td>
                <td><span class="badge-statut badge-<?= e($d['statut']) ?>"><?= statutLabel($d['statut']) ?></span></td>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <div style="width:60px;background:#e0e0e0;border-radius:4px;height:6px">
                            <div style="width:<?= $d['pourcentage_completion'] ?? 0 ?>%;background:#0d47a1;border-radius:4px;height:6px"></div>
                        </div>
                        <small><?= $d['pourcentage_completion'] ?? 0 ?>%</small>
                    </div>
                </td>
                <td><small><?= formatDateTime($d['updated_at']) ?></small></td>
                <td>
                    <?php if (in_array($d['statut'], ['brouillon', 'corrigee'])): ?>
                    <a href="/agent/declaration/<?= $d['id'] ?>/saisie" class="btn btn-sm btn-primary">
                        <i class="bi bi-pencil me-1"></i>Continuer
                    </a>
                    <?php elseif ($d['statut'] === 'rejetee'): ?>
                    <a href="/agent/declaration/<?= $d['id'] ?>/modifier" class="btn btn-sm btn-warning">
                        <i class="bi bi-arrow-repeat me-1"></i>Corriger
                    </a>
                    <?php else: ?>
                    <a href="/agent/declaration/<?= $d['id'] ?>/apercu" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-eye me-1"></i>Voir
                    </a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; if (empty($declarations)): ?>
            <tr><td colspan="8" class="text-center text-muted py-5">Aucune déclaration</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php if ($pagination['total_pages'] > 1): ?>
<nav class="mt-3"><ul class="pagination justify-content-center">
    <?php if ($pagination['has_prev']): ?><li class="page-item"><a class="page-link" href="?page=<?= $pagination['prev_page'] ?>">&laquo;</a></li><?php endif; ?>
    <li class="page-item disabled"><span class="page-link">Page <?= $pagination['current_page'] ?>/<?= $pagination['total_pages'] ?></span></li>
    <?php if ($pagination['has_next']): ?><li class="page-item"><a class="page-link" href="?page=<?= $pagination['next_page'] ?>">&raquo;</a></li><?php endif; ?>
</ul></nav>
<?php endif; ?>
