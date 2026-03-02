<?php defined('EDAMO') or die('Accès direct interdit');
$isEdit = ($mode ?? 'create') === 'edit';
$c = $campagne ?? [];
?>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-calendar-plus me-2 text-primary"></i>
                    <?= $isEdit ? 'Modifier la campagne' : 'Nouvelle campagne DAMO' ?>
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= $isEdit ? url('admin/campagne/' . $c['id'] . '/update') : url('admin/campagne/creer') ?>">
                    <?= csrfField() ?>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-500">Année <span class="text-danger">*</span></label>
                            <input type="number" name="annee" class="form-control"
                                   value="<?= e($c['annee'] ?? date('Y')) ?>"
                                   min="2000" max="2100" required <?= $isEdit ? 'readonly' : '' ?>>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-500">Libellé</label>
                            <input type="text" name="libelle" class="form-control"
                                   value="<?= e($c['libelle'] ?? '') ?>"
                                   placeholder="Déclaration Annuelle 2025">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-500">Date de début</label>
                            <input type="date" name="date_debut" class="form-control"
                                   value="<?= e($c['date_debut'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-500">Date de clôture</label>
                            <input type="date" name="date_fin" class="form-control"
                                   value="<?= e($c['date_fin'] ?? '') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-500">Description</label>
                            <textarea name="description" class="form-control" rows="3"
                                      placeholder="Description de la campagne…"><?= e($c['description'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <div class="alert alert-info mt-3 small">
                        <i class="bi bi-info-circle me-2"></i>
                        La nouvelle campagne sera automatiquement définie comme <strong>active</strong>.
                        Les autres campagnes seront marquées comme clôturées.
                    </div>

                    <div class="d-flex gap-2 mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i><?= $isEdit ? 'Enregistrer' : 'Créer la campagne' ?>
                        </button>
                        <a href="<?= url('admin/campagnes') ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i>Annuler
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
