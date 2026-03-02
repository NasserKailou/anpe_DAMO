<?php // Vue : Paramètres système ?>
<div class="page-header">
    <h1><i class="bi bi-gear me-2 text-primary"></i>Paramètres système</h1>
</div>
<div class="row justify-content-center"><div class="col-lg-8">
    <div class="card"><div class="card-body">
        <form method="POST" action="/admin/parametres">
            <?= csrfField() ?>
            <div class="table-responsive">
                <table class="table">
                    <thead><tr><th>Paramètre</th><th>Valeur</th><th>Description</th></tr></thead>
                    <tbody>
                    <?php foreach ($parametres as $p): ?>
                    <tr>
                        <td><strong><?= e($p['cle']) ?></strong></td>
                        <td>
                            <?php if (!$p['modifiable']): ?>
                            <span class="text-muted"><?= e($p['valeur']) ?></span>
                            <?php elseif ($p['type'] === 'boolean'): ?>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="<?= e($p['cle']) ?>" value="1"
                                    <?= $p['valeur'] === 'true' ? 'checked' : '' ?>>
                            </div>
                            <?php else: ?>
                            <input type="<?= $p['type'] === 'integer' ? 'number' : 'text' ?>"
                                name="<?= e($p['cle']) ?>" class="form-control form-control-sm"
                                value="<?= e($p['valeur']) ?>">
                            <?php endif; ?>
                        </td>
                        <td><small class="text-muted"><?= e($p['description'] ?? '') ?></small></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Sauvegarder</button>
            </div>
        </form>
    </div></div>
</div></div>
