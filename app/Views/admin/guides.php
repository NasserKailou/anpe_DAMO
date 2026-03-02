<?php defined('EDAMO') or die('Accès direct interdit'); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div></div>
    <a href="<?= url('admin/guide/nouveau') ?>" class="btn btn-primary">
        <i class="bi bi-upload me-1"></i>Uploader un guide
    </a>
</div>

<div class="row g-3">
    <?php if (empty($guides)): ?>
    <div class="col-12 text-center text-muted py-5">
        <i class="bi bi-book fs-1 d-block mb-2"></i>
        Aucun guide disponible. Uploadez le premier !
    </div>
    <?php else: ?>
    <?php foreach ($guides as $g): ?>
    <div class="col-md-6 col-xl-4">
        <div class="card shadow-sm h-100 border-<?= $g['actif'] ? 'primary' : 'secondary' ?>" style="border-top-width:3px">
            <div class="card-body">
                <div class="d-flex align-items-start gap-3">
                    <div class="text-danger fs-2"><i class="bi bi-file-earmark-pdf-fill"></i></div>
                    <div class="flex-1">
                        <h6 class="mb-1 fw-bold"><?= e($g['titre']) ?></h6>
                        <?php if ($g['description']): ?>
                        <p class="text-muted small mb-1"><?= e($g['description']) ?></p>
                        <?php endif; ?>
                        <div class="d-flex flex-wrap gap-2 mt-2">
                            <span class="badge bg-light text-dark border">
                                <i class="bi bi-calendar me-1"></i><?= e($g['annee']) ?>
                            </span>
                            <span class="badge bg-light text-dark border">
                                <i class="bi bi-download me-1"></i><?= number_format($g['telechargements'] ?? 0) ?> téléch.
                            </span>
                            <?php if ($g['fichier_taille']): ?>
                            <span class="badge bg-light text-dark border">
                                <?= round($g['fichier_taille'] / 1024) ?> Ko
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between align-items-center">
                <small class="text-muted">Par <?= e($g['createur'] ?? 'Admin') ?></small>
                <div class="btn-group btn-group-sm">
                    <a href="<?= url('guide/' . $g['id'] . '/telecharger') ?>"
                       class="btn btn-outline-danger" title="Télécharger">
                        <i class="bi bi-download"></i>
                    </a>
                    <button type="button" class="btn btn-outline-danger btn-supprimer-guide"
                            data-id="<?= $g['id'] ?>" data-titre="<?= e($g['titre']) ?>"
                            title="Supprimer">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Modal suppression -->
<div class="modal fade" id="modalSupprimerGuide" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header"><h6 class="modal-title text-danger">Supprimer le guide</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="formSupprimerGuide" method="POST">
                <?= csrfField() ?>
                <div class="modal-body">
                    Supprimer <strong id="titreGuide"></strong> ? Cette action est irréversible.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger btn-sm"><i class="bi bi-trash me-1"></i>Supprimer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.btn-supprimer-guide').forEach(btn => {
    btn.addEventListener('click', function () {
        document.getElementById('titreGuide').textContent = this.dataset.titre;
        document.getElementById('formSupprimerGuide').action = window.APP_BASE + '/admin/guide/' + this.dataset.id + '/supprimer';
        new bootstrap.Modal(document.getElementById('modalSupprimerGuide')).show();
    });
});
</script>
