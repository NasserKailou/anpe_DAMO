<?php defined('EDAMO') or die('Accès direct interdit');
// Indexer par clé
$params = [];
foreach ($parametres as $p) { $params[$p['cle']] = $p; }
?>

<div class="row g-4">
    <!-- Paramètres généraux -->
    <div class="col-lg-8">
        <form method="POST" action="<?= url('admin/parametres/update') ?>">
            <?= csrfField() ?>

            <!-- App -->
            <div class="card shadow-sm mb-4">
                <div class="card-header"><h6 class="mb-0"><i class="bi bi-gear me-2 text-primary"></i>Application</h6></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nom de l'application</label>
                            <input type="text" name="app_nom" class="form-control"
                                   value="<?= e($params['app_nom']['valeur'] ?? 'e-DAMO') ?>"
                                   <?= ($params['app_nom']['modifiable'] ?? false) ? '' : 'readonly' ?>>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Slogan</label>
                            <input type="text" name="app_slogan" class="form-control"
                                   value="<?= e($params['app_slogan']['valeur'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Année courante</label>
                            <input type="number" name="annee_courante" class="form-control"
                                   value="<?= e($params['annee_courante']['valeur'] ?? date('Y')) ?>"
                                   min="2000" max="2100">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Date limite de dépôt</label>
                            <input type="date" name="date_limite_depot" class="form-control"
                                   value="<?= e($params['date_limite_depot']['valeur'] ?? '') ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact -->
            <div class="card shadow-sm mb-4">
                <div class="card-header"><h6 class="mb-0"><i class="bi bi-telephone me-2 text-info"></i>Contact & Coordonnées</h6></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Email de contact</label>
                            <input type="email" name="contact_email" class="form-control"
                                   value="<?= e($params['contact_email']['valeur'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Téléphone</label>
                            <input type="text" name="contact_telephone" class="form-control"
                                   value="<?= e($params['contact_telephone']['valeur'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Site web</label>
                            <input type="url" name="contact_website" class="form-control"
                                   value="<?= e($params['contact_website']['valeur'] ?? '') ?>"
                                   placeholder="https://www.anpe-niger.ne">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Adresse</label>
                            <input type="text" name="contact_adresse" class="form-control"
                                   value="<?= e($params['contact_adresse']['valeur'] ?? '') ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Configuration -->
            <div class="card shadow-sm mb-4">
                <div class="card-header"><h6 class="mb-0"><i class="bi bi-toggles me-2 text-warning"></i>Configuration</h6></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Éléments par page</label>
                            <input type="number" name="items_par_page" class="form-control"
                                   value="<?= e($params['items_par_page']['valeur'] ?? '20') ?>"
                                   min="5" max="100">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="inscriptions_ouvertes"
                                       id="inscriptionsOuvertes"
                                       <?= ($params['inscriptions_ouvertes']['valeur'] ?? 'true') === 'true' ? 'checked' : '' ?>>
                                <label class="form-check-label" for="inscriptionsOuvertes">
                                    Inscriptions ouvertes
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="maintenance_mode"
                                       id="maintenanceMode"
                                       <?= ($params['maintenance_mode']['valeur'] ?? 'false') === 'true' ? 'checked' : '' ?>>
                                <label class="form-check-label text-danger" for="maintenanceMode">
                                    <i class="bi bi-exclamation-triangle me-1"></i>Mode maintenance
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-lg w-100">
                <i class="bi bi-save me-1"></i>Enregistrer les paramètres
            </button>
        </form>
    </div>

    <!-- Infos système -->
    <div class="col-lg-4">
        <div class="card shadow-sm mb-3">
            <div class="card-header"><h6 class="mb-0"><i class="bi bi-server me-2 text-secondary"></i>Informations système</h6></div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr><th>Version app</th><td><?= APP_VERSION ?></td></tr>
                    <tr><th>PHP</th><td><?= PHP_VERSION ?></td></tr>
                    <tr><th>Serveur</th><td><?= $_SERVER['SERVER_SOFTWARE'] ?? 'N/A' ?></td></tr>
                    <tr><th>Environnement</th><td>
                        <span class="badge bg-<?= APP_ENV === 'production' ? 'danger' : 'success' ?>">
                            <?= APP_ENV ?>
                        </span>
                    </td></tr>
                    <tr><th>Date serveur</th><td><?= date('d/m/Y H:i') ?></td></tr>
                </table>
            </div>
        </div>

        <div class="card shadow-sm border-warning">
            <div class="card-header bg-warning text-dark"><h6 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Zone dangereuse</h6></div>
            <div class="card-body">
                <p class="small text-muted mb-3">
                    Ces actions sont irréversibles. Procéder avec précaution.
                </p>
                <div class="d-grid gap-2">
                    <a href="<?= url('admin/logs') ?>" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-journal-text me-1"></i>Voir les logs d'audit
                    </a>
                    <a href="<?= url('admin/export/declarations') ?>" class="btn btn-sm btn-outline-success">
                        <i class="bi bi-file-earmark-excel me-1"></i>Export CSV déclarations
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
