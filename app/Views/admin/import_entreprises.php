<?php defined('EDAMO') or die('Accès direct interdit'); ?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-upload me-2 text-primary"></i>Import CSV des entreprises</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h6 class="fw-bold"><i class="bi bi-info-circle me-2"></i>Format attendu</h6>
                    <p class="mb-2">Le fichier CSV doit contenir les colonnes dans l'ordre suivant :</p>
                    <code>raison_sociale ; numero_cnss ; telephone ; email ; activite_principale ; nationalite ; localite</code>
                    <p class="mt-2 mb-0 small">Les entreprises avec un numéro CNSS déjà existant seront ignorées (pas de doublons).</p>
                </div>

                <form method="POST" action="<?= url('admin/import/entreprises') ?>" enctype="multipart/form-data">
                    <?= csrfField() ?>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-500">Fichier CSV <span class="text-danger">*</span></label>
                            <input type="file" name="csv_file" class="form-control" accept=".csv,.txt" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-500">Région <span class="text-danger">*</span></label>
                            <select name="region_id" class="form-select" required>
                                <option value="">-- Sélectionner --</option>
                                <?php foreach ($regions as $r): ?>
                                <option value="<?= $r['id'] ?>"><?= e($r['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-500">Séparateur</label>
                            <select name="delimiter" class="form-select">
                                <option value=";">Point-virgule ( ; )</option>
                                <option value=",">Virgule ( , )</option>
                                <option value="|">Pipe ( | )</option>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="skip_header" value="1" id="skipHeader" checked>
                                <label class="form-check-label" for="skipHeader">
                                    Ignorer la 1ère ligne (en-tête)
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-upload me-1"></i>Lancer l'import
                        </button>
                        <a href="<?= url('admin/utilisateurs') ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i>Retour
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
