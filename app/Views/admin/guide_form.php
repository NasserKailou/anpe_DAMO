<?php defined('EDAMO') or die('Accès direct interdit'); ?>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-upload me-2 text-danger"></i>Uploader un guide PDF</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= url('admin/guide/upload') ?>" enctype="multipart/form-data">
                    <?= csrfField() ?>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-500">Titre <span class="text-danger">*</span></label>
                            <input type="text" name="titre" class="form-control" required
                                   placeholder="Guide de remplissage DAMO 2025">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-500">Année</label>
                            <input type="number" name="annee" class="form-control"
                                   value="<?= date('Y') ?>" min="2000" max="2100">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-500">Ordre d'affichage</label>
                            <input type="number" name="ordre" class="form-control" value="1" min="1">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-500">Description</label>
                            <textarea name="description" class="form-control" rows="2"
                                      placeholder="Brève description du document…"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-500">Fichier PDF <span class="text-danger">*</span></label>
                            <input type="file" name="fichier" class="form-control" accept=".pdf" required>
                            <small class="text-muted">Format PDF uniquement — taille max 10 Mo</small>
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-upload me-1"></i>Uploader
                        </button>
                        <a href="<?= url('admin/guides') ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i>Annuler
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
