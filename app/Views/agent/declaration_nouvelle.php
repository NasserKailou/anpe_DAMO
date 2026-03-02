<?php // Vue : Nouvelle déclaration - sélection entreprise ?>
<div class="page-header">
    <div>
        <h1><i class="bi bi-file-earmark-plus me-2 text-primary"></i>Nouvelle déclaration</h1>
        <p>Campagne : <strong><?= e($campagne['libelle']) ?></strong> (<?= e($campagne['annee']) ?>)</p>
    </div>
    <a href="/agent/declarations" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Retour</a>
</div>
<div class="row justify-content-center"><div class="col-lg-7">
    <div class="card"><div class="card-body">
        <p class="text-muted mb-4">Sélectionnez l'entreprise pour laquelle vous souhaitez créer une déclaration.</p>
        <?php if (empty($entreprises)): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            Toutes les entreprises de votre région ont déjà une déclaration pour cette campagne.
            Vous pouvez <a href="/agent/entreprise/nouvelle">ajouter une nouvelle entreprise</a>.
        </div>
        <?php else: ?>
        <form method="POST" action="/agent/declaration/nouvelle">
            <?= csrfField() ?>
            <div class="mb-3">
                <label class="form-label">Entreprise <span class="text-danger">*</span></label>
                <select name="entreprise_id" class="form-select" required>
                    <option value="">— Choisir une entreprise —</option>
                    <?php foreach ($entreprises as $e): ?>
                    <option value="<?= $e['id'] ?>">
                        <?= e($e['raison_sociale']) ?>
                        <?php if ($e['numero_cnss']): ?> — CNSS: <?= e($e['numero_cnss']) ?><?php endif; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <div class="form-text">
                    Vous ne trouvez pas l'entreprise ?
                    <a href="/agent/entreprise/nouvelle">Créer une nouvelle entreprise</a>
                </div>
            </div>
            <div class="d-flex justify-content-end gap-2">
                <a href="/agent/declarations" class="btn btn-outline-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-arrow-right me-1"></i>Commencer la saisie
                </button>
            </div>
        </form>
        <?php endif; ?>
    </div></div>
</div></div>
