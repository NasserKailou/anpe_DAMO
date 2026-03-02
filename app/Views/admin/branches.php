<?php // Vue : Branches d'activité ?>
<div class="page-header">
    <h1><i class="bi bi-diagram-3 me-2 text-primary"></i>Branches d'activité</h1>
</div>
<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead><tr><th>Code</th><th>Libellé</th><th>Statut</th></tr></thead>
            <tbody>
            <?php foreach ($branches as $b): ?>
            <tr>
                <td><code><?= e($b['code']) ?></code></td>
                <td><?= e($b['libelle']) ?></td>
                <td><?= $b['actif'] ? '<span class="badge bg-success">Actif</span>' : '<span class="badge bg-secondary">Inactif</span>' ?></td>
            </tr>
            <?php endforeach; if (empty($branches)): ?>
            <tr><td colspan="3" class="text-center py-4 text-muted">Aucune branche</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
