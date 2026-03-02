<?php // Vue : Upload guide ?>
<div class="page-header">
    <h1><i class="bi bi-upload me-2 text-primary"></i>Uploader un guide</h1>
    <a href="/admin/guides" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Retour</a>
</div>
<div class="row justify-content-center"><div class="col-lg-6">
    <div class="card"><div class="card-body">
        <form method="POST" action="/admin/guide/nouveau" enctype="multipart/form-data">
            <?= csrfField() ?>
            <div class="mb-3">
                <label class="form-label">Titre <span class="text-danger">*</span></label>
                <input type="text" name="titre" class="form-control" required placeholder="Ex: Guide de remplissage DAMO 2025">
            </div>
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="2"></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Année</label>
                <input type="number" name="annee" class="form-control" value="<?= date('Y') ?>" min="2000" max="2100">
            </div>
            <div class="mb-3">
                <label class="form-label">Fichier PDF <span class="text-danger">*</span></label>
                <input type="file" name="fichier" class="form-control" accept=".pdf" required>
                <div class="form-text">Format PDF uniquement, max 10 MB</div>
            </div>
            <div class="d-flex justify-content-end gap-2">
                <a href="/admin/guides" class="btn btn-outline-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary"><i class="bi bi-upload me-1"></i>Uploader</button>
            </div>
        </form>
    </div></div>
</div></div>
