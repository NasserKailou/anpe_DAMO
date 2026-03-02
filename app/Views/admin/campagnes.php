<?php // Vue : Campagnes DAMO ?>
<div class="page-header">
    <div>
        <h1><i class="bi bi-calendar3 me-2 text-primary"></i>Campagnes DAMO</h1>
    </div>
    <a href="/admin/campagne/nouvelle" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i>Nouvelle campagne
    </a>
</div>
<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead><tr><th>Année</th><th>Libellé</th><th>Période</th><th>Déclarations</th><th>Statut</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($campagnes as $c): ?>
            <tr>
                <td><strong><?= e($c['annee']) ?></strong></td>
                <td><?= e($c['libelle']) ?></td>
                <td><small><?= formatDate($c['date_debut']) ?> → <?= formatDate($c['date_fin']) ?></small></td>
                <td class="text-center"><?= formatNumber($c['nb_declarations']) ?></td>
                <td><?= $c['actif'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' ?></td>
                <td><a href="/admin/campagne/<?= $c['id'] ?>/modifier" class="btn btn-sm btn-icon btn-outline-primary"><i class="bi bi-pencil"></i></a></td>
            </tr>
            <?php endforeach; if (empty($campagnes)): ?>
            <tr><td colspan="6" class="text-center py-4 text-muted">Aucune campagne</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
