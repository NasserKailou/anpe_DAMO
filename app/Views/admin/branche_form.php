<?php defined('EDAMO') or die('Accès direct interdit'); ?>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card shadow-sm">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="bi bi-diagram-3 me-2 text-primary"></i>
                    <?= $mode === 'create' ? "Nouvelle branche d'activité" : "Modifier la branche" ?>
                </h6>
            </div>
            <div class="card-body">
                <?php
                $action = $mode === 'create'
                    ? url('admin/branche/nouvelle')
                    : url('admin/branche/' . $branche['id'] . '/modifier');
                $old    = $old ?? $branche ?? [];
                ?>
                <form method="POST" action="<?= $action ?>" novalidate>
                    <?= csrfField() ?>

                    <!-- Code -->
                    <div class="mb-3">
                        <label for="code" class="form-label fw-semibold">
                            Code <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control text-uppercase <?= isset($errors['code']) ? 'is-invalid' : '' ?>"
                               id="code" name="code"
                               value="<?= e($old['code'] ?? '') ?>"
                               maxlength="10" placeholder="ex: B10" required
                               oninput="this.value = this.value.toUpperCase()">
                        <?php if (isset($errors['code'])): ?>
                        <div class="invalid-feedback"><?= e($errors['code']) ?></div>
                        <?php endif; ?>
                        <div class="form-text">Code unique, max 10 caractères. Ex : B1, B10</div>
                    </div>

                    <!-- Libellé -->
                    <div class="mb-3">
                        <label for="libelle" class="form-label fw-semibold">
                            Libellé <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control <?= isset($errors['libelle']) ? 'is-invalid' : '' ?>"
                               id="libelle" name="libelle"
                               value="<?= e($old['libelle'] ?? '') ?>"
                               maxlength="200" placeholder="ex: Industries manufacturières" required>
                        <?php if (isset($errors['libelle'])): ?>
                        <div class="invalid-feedback"><?= e($errors['libelle']) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Description -->
                    <div class="mb-3">
                        <label for="description" class="form-label fw-semibold">Description</label>
                        <textarea class="form-control" id="description" name="description"
                                  rows="3" placeholder="Description optionnelle..."><?= e($old['description'] ?? '') ?></textarea>
                    </div>

                    <!-- Statut -->
                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="actif" name="actif" value="1"
                                   <?= (!isset($old['actif']) || $old['actif']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="actif">Branche active</label>
                        </div>
                        <div class="form-text">Les branches inactives ne s'affichent pas dans les formulaires.</div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>
                            <?= $mode === 'create' ? 'Créer la branche' : 'Mettre à jour' ?>
                        </button>
                        <a href="<?= url('admin/branches') ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i>Retour
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
