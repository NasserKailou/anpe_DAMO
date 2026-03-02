<?php defined('EDAMO') or die('Accès direct interdit');
$isEdit = ($mode ?? 'create') === 'edit';
$c = $campagne ?? [];
?>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card shadow-sm">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="bi bi-calendar-<?= $isEdit ? 'check' : 'plus' ?> text-primary fs-5"></i>
                <h5 class="mb-0">
                    <?= $isEdit ? 'Modifier la campagne' : 'Nouvelle campagne DAMO' ?>
                </h5>
                <?php if ($isEdit): ?>
                    <span class="badge ms-auto <?= ($c['actif'] ?? false) ? 'bg-success' : 'bg-secondary' ?>">
                        <?= ($c['actif'] ?? false) ? 'Active' : 'Clôturée' ?>
                    </span>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= $isEdit ? url('admin/campagne/' . $c['id'] . '/modifier') : url('admin/campagne/nouvelle') ?>">
                    <?= csrfField() ?>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Année <span class="text-danger">*</span></label>
                            <input type="number" name="annee" class="form-control"
                                   value="<?= e($c['annee'] ?? date('Y')) ?>"
                                   min="2000" max="2100" required <?= $isEdit ? 'readonly' : '' ?>>
                            <?php if ($isEdit): ?><small class="text-muted">L'année ne peut pas être modifiée.</small><?php endif; ?>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Libellé <span class="text-danger">*</span></label>
                            <input type="text" name="libelle" class="form-control"
                                   value="<?= e($c['libelle'] ?? 'Déclaration Annuelle ' . date('Y')) ?>"
                                   placeholder="Ex : Déclaration Annuelle 2025" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Date d'ouverture <span class="text-danger">*</span></label>
                            <input type="date" name="date_debut" class="form-control"
                                   value="<?= e($c['date_debut'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Date de clôture <span class="text-danger">*</span></label>
                            <input type="date" name="date_fin" class="form-control"
                                   value="<?= e($c['date_fin'] ?? '') ?>" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea name="description" class="form-control" rows="3"
                                      placeholder="Décrivez les objectifs ou instructions de cette campagne…"><?= e($c['description'] ?? '') ?></textarea>
                        </div>

                        <?php if ($isEdit): ?>
                        <!-- Statut actif/inactif en mode édition -->
                        <div class="col-12">
                            <hr class="my-1">
                            <label class="form-label fw-semibold">Statut de la campagne</label>
                            <div class="d-flex gap-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="actif" id="actif-oui" value="1"
                                           <?= ($c['actif'] ?? false) ? 'checked' : '' ?>>
                                    <label class="form-check-label text-success fw-semibold" for="actif-oui">
                                        <i class="bi bi-lightning-fill me-1"></i>Active (les agents peuvent déclarer)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="actif" id="actif-non" value="0"
                                           <?= !($c['actif'] ?? true) ? 'checked' : '' ?>>
                                    <label class="form-check-label text-secondary fw-semibold" for="actif-non">
                                        <i class="bi bi-lock me-1"></i>Clôturée (saisie désactivée)
                                    </label>
                                </div>
                            </div>
                            <small class="text-muted">
                                <i class="bi bi-info-circle me-1"></i>
                                Activer cette campagne clôturera automatiquement toutes les autres.
                            </small>
                        </div>
                        <?php endif; ?>
                    </div>

                    <?php if (!$isEdit): ?>
                    <div class="alert alert-info mt-3 small mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        La nouvelle campagne sera automatiquement définie comme <strong>active</strong>.
                        Les autres campagnes seront marquées comme clôturées.
                    </div>
                    <?php endif; ?>

                    <div class="d-flex gap-2 mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i><?= $isEdit ? 'Enregistrer les modifications' : 'Créer la campagne' ?>
                        </button>
                        <a href="<?= url('admin/campagnes') ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i>Annuler
                        </a>
                        <?php if ($isEdit): ?>
                        <a href="<?= url('admin/campagne/' . $c['id'] . '/rappels') ?>"
                           class="btn btn-outline-warning ms-auto"
                           onclick="return confirm('Envoyer des rappels aux agents avec brouillons en cours ?')">
                            <i class="bi bi-bell me-1"></i>Envoyer rappels
                        </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
