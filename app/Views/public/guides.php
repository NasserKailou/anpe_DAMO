<?php // Vue : Guides publics ?>
<div class="container py-5">
    <h1 class="mb-4">Guides de remplissage</h1>
    <?php if (empty($guides)): ?>
    <div class="alert alert-info"><i class="bi bi-info-circle me-2"></i>Aucun guide disponible pour le moment.</div>
    <?php else: ?>
    <div class="row g-3">
        <?php foreach ($guides as $g): ?>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-start gap-3">
                        <i class="bi bi-file-earmark-pdf text-danger" style="font-size:2.5rem;flex-shrink:0"></i>
                        <div>
                            <h5 class="mb-1"><?= e($g['titre']) ?></h5>
                            <?php if ($g['description']): ?>
                            <p class="text-muted" style="font-size:.84rem"><?= e($g['description']) ?></p>
                            <?php endif; ?>
                            <small class="text-muted">Année <?= e($g['annee']) ?> — <?= formatFileSize($g['fichier_taille']) ?></small>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent">
                    <a href="/guide/<?= $g['id'] ?>/telecharger" class="btn btn-primary btn-sm w-100">
                        <i class="bi bi-download me-2"></i>Télécharger
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
