<?php // Vue : Formulaire Campagne ?>
<div class="page-header">
    <h1><i class="bi bi-calendar-plus me-2 text-primary"></i><?= $mode === 'create' ? 'Nouvelle campagne' : 'Modifier la campagne' ?></h1>
    <a href="/admin/campagnes" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Retour</a>
</div>
<div class="row justify-content-center"><div class="col-lg-6">
    <div class="card"><div class="card-body">
        <form method="POST" action="/admin/campagne/<?= $mode === 'edit' ? (e($campagne['id']) . '/modifier') : 'nouvelle' ?>">
            <?= csrfField() ?>
            <div class="mb-3">
                <label class="form-label">Année <span class="text-danger">*</span></label>
                <input type="number" name="annee" class="form-control" min="2000" max="2100" value="<?= e($campagne['annee'] ?? date('Y')) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Libellé</label>
                <input type="text" name="libelle" class="form-control" value="<?= e($campagne['libelle'] ?? '') ?>" placeholder="Ex: Déclaration Annuelle 2025">
            </div>
            <div class="row g-3">
                <div class="col-6">
                    <label class="form-label">Date de début</label>
                    <input type="date" name="date_debut" class="form-control" value="<?= e($campagne['date_debut'] ?? '') ?>">
                </div>
                <div class="col-6">
                    <label class="form-label">Date de fin</label>
                    <input type="date" name="date_fin" class="form-control" value="<?= e($campagne['date_fin'] ?? '') ?>">
                </div>
            </div>
            <div class="mb-3 mt-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3"><?= e($campagne['description'] ?? '') ?></textarea>
            </div>
            <div class="d-flex justify-content-end gap-2">
                <a href="/admin/campagnes" class="btn btn-outline-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Enregistrer</button>
            </div>
        </form>
    </div></div>
</div></div>
