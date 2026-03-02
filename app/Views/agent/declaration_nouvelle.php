<?php defined('EDAMO') or die('Accès direct interdit'); ?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-plus-circle me-2 text-primary"></i>
                    Nouvelle déclaration — Campagne <?= e($campagne['annee']) ?>
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info small">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Campagne :</strong> <?= e($campagne['libelle']) ?>
                    &nbsp;|&nbsp; Clôture : <strong><?= formatDate($campagne['date_fin']) ?></strong>
                </div>

                <?php if (empty($entreprises)): ?>
                <div class="text-center text-muted py-4">
                    <i class="bi bi-building-check fs-2 d-block mb-2 text-success"></i>
                    <p>Toutes les entreprises de votre région ont déjà une déclaration pour cette campagne.</p>
                    <a href="<?= url('agent/entreprise/nouvelle') ?>" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-building-add me-1"></i>Enregistrer une nouvelle entreprise
                    </a>
                </div>
                <?php else: ?>
                <form method="POST" action="<?= url('agent/declaration/creer') ?>">
                    <?= csrfField() ?>
                    <div class="mb-4">
                        <label class="form-label fw-bold fs-6">
                            Sélectionner l'entreprise <span class="text-danger">*</span>
                        </label>
                        <div class="mb-2">
                            <input type="text" id="searchEntreprise" class="form-control form-control-sm"
                                   placeholder="Rechercher par nom ou N° CNSS…">
                        </div>
                        <div style="max-height:350px;overflow-y:auto;border:1px solid #dee2e6;border-radius:8px" id="listeEntreprises">
                            <?php foreach ($entreprises as $e): ?>
                            <label class="d-flex align-items-start gap-3 p-3 cursor-pointer hover-bg enterprise-item"
                                   style="border-bottom:1px solid #f0f0f0">
                                <input type="radio" name="entreprise_id" value="<?= $e['id'] ?>" required class="mt-1 flex-shrink-0">
                                <div>
                                    <div class="fw-500"><?= e($e['raison_sociale']) ?></div>
                                    <div class="small text-muted">
                                        <?php if ($e['numero_cnss']): ?>
                                        <i class="bi bi-hash me-1"></i><?= e($e['numero_cnss']) ?> &nbsp;
                                        <?php endif; ?>
                                        <?php if ($e['activite_principale']): ?>
                                        <i class="bi bi-briefcase me-1"></i><?= e(substr($e['activite_principale'], 0, 50)) ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <small class="text-muted"><?= count($entreprises) ?> entreprise(s) disponible(s)</small>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-play-circle me-1"></i>Commencer la saisie
                        </button>
                        <a href="<?= url('agent/declarations') ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i>Annuler
                        </a>
                        <a href="<?= url('agent/entreprise/nouvelle') ?>" class="btn btn-outline-info ms-auto">
                            <i class="bi bi-building-add me-1"></i>Nouvelle entreprise
                        </a>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Filtre temps réel sur la liste des entreprises
document.getElementById('searchEntreprise')?.addEventListener('input', function () {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.enterprise-item').forEach(item => {
        item.style.display = item.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});
</script>
