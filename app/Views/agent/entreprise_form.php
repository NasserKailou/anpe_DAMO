<?php // Vue : Formulaire Entreprise ?>
<div class="page-header">
    <h1><i class="bi bi-building-add me-2 text-primary"></i><?= $mode === 'create' ? 'Nouvelle entreprise' : 'Modifier l\'entreprise' ?></h1>
    <a href="/agent/entreprises" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Retour</a>
</div>
<div class="card"><div class="card-body">
    <form method="POST" action="/agent/entreprise/<?= $mode === 'edit' ? (e($entreprise['id']) . '/modifier') : 'nouvelle' ?>">
        <?= csrfField() ?>
        <?php if (!empty($errors)): ?>
        <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e_): ?><li><?= e($e_) ?></li><?php endforeach; ?></ul></div>
        <?php endif; ?>
        <div class="row g-3">
            <div class="col-md-8">
                <label class="form-label">Raison sociale <span class="text-danger">*</span></label>
                <input type="text" name="raison_sociale" class="form-control" value="<?= e($entreprise['raison_sociale'] ?? '') ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">N° CNSS</label>
                <input type="text" name="numero_cnss" class="form-control" value="<?= e($entreprise['numero_cnss'] ?? '') ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Nationalité du capital</label>
                <input type="text" name="nationalite" class="form-control" value="<?= e($entreprise['nationalite'] ?? 'Nigérienne') ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Branche d'activité</label>
                <select name="branche_id" class="form-select">
                    <option value="">— Choisir —</option>
                    <?php foreach ($branches as $b): ?>
                    <option value="<?= $b['id'] ?>" <?= ($entreprise['branche_id'] ?? '') == $b['id'] ? 'selected' : '' ?>><?= e($b['code']) ?> - <?= e($b['libelle']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label">Activité principale</label>
                <input type="text" name="activite_principale" class="form-control" value="<?= e($entreprise['activite_principale'] ?? '') ?>">
            </div>
            <div class="col-12">
                <label class="form-label">Activités secondaires</label>
                <input type="text" name="activites_secondaires" class="form-control" value="<?= e($entreprise['activites_secondaires'] ?? '') ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Département</label>
                <select name="departement_id" class="form-select">
                    <option value="">— Choisir —</option>
                    <?php foreach ($departements as $d): ?>
                    <option value="<?= $d['id'] ?>" <?= ($entreprise['departement_id'] ?? '') == $d['id'] ? 'selected' : '' ?>><?= e($d['nom']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Commune</label>
                <select name="commune_id" class="form-select">
                    <option value="">— Choisir —</option>
                    <?php foreach ($communes ?? [] as $com): ?>
                    <option value="<?= $com['id'] ?>" <?= ($entreprise['commune_id'] ?? '') == $com['id'] ? 'selected' : '' ?>><?= e($com['nom']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4"><label class="form-label">Localité</label>
                <input type="text" name="localite" class="form-control" value="<?= e($entreprise['localite'] ?? '') ?>"></div>
            <div class="col-md-4"><label class="form-label">Quartier</label>
                <input type="text" name="quartier" class="form-control" value="<?= e($entreprise['quartier'] ?? '') ?>"></div>
            <div class="col-md-4"><label class="form-label">Boîte postale</label>
                <input type="text" name="boite_postale" class="form-control" value="<?= e($entreprise['boite_postale'] ?? '') ?>"></div>
            <div class="col-md-4"><label class="form-label">Téléphone</label>
                <input type="text" name="telephone" class="form-control" value="<?= e($entreprise['telephone'] ?? '') ?>"></div>
            <div class="col-md-4"><label class="form-label">Fax</label>
                <input type="text" name="fax" class="form-control" value="<?= e($entreprise['fax'] ?? '') ?>"></div>
            <div class="col-md-4"><label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="<?= e($entreprise['email'] ?? '') ?>"></div>
        </div>
        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="/agent/entreprises" class="btn btn-outline-secondary">Annuler</a>
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i><?= $mode === 'create' ? 'Créer l\'entreprise' : 'Enregistrer' ?></button>
        </div>
    </form>
</div></div>
