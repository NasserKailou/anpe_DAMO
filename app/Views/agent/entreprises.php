<?php // Vue : Liste Entreprises Agent ?>
<div class="page-header">
    <div><h1><i class="bi bi-building me-2 text-primary"></i>Entreprises de ma région</h1>
        <p>Total : <strong><?= formatNumber($total) ?></strong></p></div>
    <a href="/agent/entreprise/nouvelle" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i>Nouvelle entreprise
    </a>
</div>
<div class="filters-bar">
    <form method="GET" action="/agent/entreprises">
        <input type="text" name="q" value="<?= e($filters['search']) ?>" class="form-control form-control-sm" placeholder="Raison sociale, CNSS...">
        <select name="branche" class="form-select form-select-sm">
            <option value="">Toutes branches</option>
            <?php foreach ($branches as $b): ?>
            <option value="<?= $b['id'] ?>" <?= $filters['branche'] == $b['id'] ? 'selected' : '' ?>><?= e($b['libelle']) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search me-1"></i>Filtrer</button>
        <a href="/agent/entreprises" class="btn btn-outline-secondary btn-sm">Reset</a>
    </form>
</div>
<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead><tr><th>Raison sociale</th><th>N° CNSS</th><th>Branche</th><th>Téléphone</th><th>Déclarations</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($entreprises as $e): ?>
            <tr>
                <td><strong><?= e($e['raison_sociale']) ?></strong>
                    <?php if ($e['localite']): ?><br><small class="text-muted"><?= e($e['localite']) ?></small><?php endif; ?>
                </td>
                <td><?= e($e['numero_cnss'] ?? '-') ?></td>
                <td><small><?= e($e['branche_libelle'] ?? '-') ?></small></td>
                <td><?= e($e['telephone'] ?? '-') ?></td>
                <td class="text-center"><?= $e['nb_declarations'] ?></td>
                <td>
                    <a href="/agent/entreprise/<?= $e['id'] ?>/modifier" class="btn btn-sm btn-icon btn-outline-primary">
                        <i class="bi bi-pencil"></i>
                    </a>
                </td>
            </tr>
            <?php endforeach; if (empty($entreprises)): ?>
            <tr><td colspan="6" class="text-center py-4 text-muted">Aucune entreprise</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
