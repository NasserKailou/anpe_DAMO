<?php // Vue : Guides et Documents ?>
<div class="page-header">
    <div>
        <h1><i class="bi bi-book me-2 text-primary"></i>Guides & Documents</h1>
    </div>
    <a href="/admin/guide/nouveau" class="btn btn-primary btn-sm"><i class="bi bi-upload me-1"></i>Uploader un guide</a>
</div>
<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead><tr><th>Titre</th><th>Année</th><th>Taille</th><th>Créé par</th><th>Date</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($guides as $g): ?>
            <tr>
                <td>
                    <strong><?= e($g['titre']) ?></strong>
                    <?php if ($g['description']): ?><br><small class="text-muted"><?= e(truncate($g['description'], 60)) ?></small><?php endif; ?>
                </td>
                <td><?= e($g['annee']) ?></td>
                <td><small><?= formatFileSize($g['fichier_taille']) ?></small></td>
                <td><?= e($g['createur'] ?? '-') ?></td>
                <td><small><?= formatDate($g['created_at']) ?></small></td>
                <td>
                    <a href="<?= e($g['fichier_path']) ?>" target="_blank" class="btn btn-sm btn-icon btn-outline-primary" title="Télécharger">
                        <i class="bi bi-download"></i>
                    </a>
                    <form method="POST" action="/admin/guide/<?= $g['id'] ?>/supprimer" class="d-inline" data-ajax>
                        <?= csrfField() ?>
                        <button type="submit" class="btn btn-sm btn-icon btn-outline-danger" title="Supprimer" data-confirm="Supprimer ce guide ?">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </td>
            </tr>
            <?php endforeach; if (empty($guides)): ?>
            <tr><td colspan="6" class="text-center py-4 text-muted">Aucun guide disponible</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
