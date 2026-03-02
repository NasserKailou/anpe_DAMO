<?php defined('EDAMO') or die('Accès direct interdit'); ?>

<!-- En-tête déclaration -->
<div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-1">
            Déclaration <code><?= e($declaration['code_questionnaire']) ?></code>
            <?= match($declaration['statut']) {
                'brouillon' => '<span class="badge bg-secondary ms-2">Brouillon</span>',
                'soumise'   => '<span class="badge bg-warning text-dark ms-2">Soumise</span>',
                'validee'   => '<span class="badge bg-success ms-2">Validée</span>',
                'rejetee'   => '<span class="badge bg-danger ms-2">Rejetée</span>',
                default     => ''
            } ?>
        </h4>
        <div class="text-muted small">
            Campagne <?= e($declaration['annee']) ?> — <?= e($declaration['campagne_libelle']) ?>
        </div>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <?php if ($declaration['statut'] === 'soumise'): ?>
        <button class="btn btn-success btn-valider-detail" data-id="<?= $declaration['id'] ?>">
            <i class="bi bi-check-circle me-1"></i>Valider
        </button>
        <button class="btn btn-danger btn-rejeter-detail" data-id="<?= $declaration['id'] ?>">
            <i class="bi bi-x-circle me-1"></i>Rejeter
        </button>
        <?php endif; ?>
        <?php if (in_array($declaration['statut'], ['soumise', 'rejetee', 'corrigee'])): ?>
        <button class="btn btn-outline-warning btn-retour-brouillon" data-id="<?= $declaration['id'] ?>" title="Remettre en brouillon pour modification par l'agent">
            <i class="bi bi-arrow-counterclockwise me-1"></i>Retour brouillon
        </button>
        <?php endif; ?>
        <a href="<?= url('admin/declaration/' . $declaration['id'] . '/exporter') ?>"
           class="btn btn-outline-secondary">
            <i class="bi bi-file-earmark-arrow-down me-1"></i>Exporter
        </a>
        <a href="<?= url('admin/declarations') ?>" class="btn btn-outline-dark">
            <i class="bi bi-arrow-left me-1"></i>Retour
        </a>
    </div>
</div>

<?php if ($declaration['statut'] === 'rejetee' && $declaration['motif_rejet']): ?>
<div class="alert alert-danger">
    <i class="bi bi-x-octagon me-2"></i>
    <strong>Motif de rejet :</strong> <?= e($declaration['motif_rejet']) ?>
</div>
<?php endif; ?>

<?php if ($declaration['statut'] === 'validee'): ?>
<div class="alert alert-success">
    <i class="bi bi-check-circle me-2"></i>
    <strong>Validée le <?= formatDate($declaration['date_validation']) ?></strong>
    par <?= e(($declaration['validateur_prenom'] ?? '') . ' ' . ($declaration['validateur_nom'] ?? '')) ?>
    <?php if ($declaration['observations']): ?>
    — <em><?= e($declaration['observations']) ?></em>
    <?php endif; ?>
</div>
<?php endif; ?>

<div class="row g-4">
    <!-- Informations entreprise -->
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-header"><h6 class="mb-0"><i class="bi bi-building me-2 text-primary"></i>Identification de l'entreprise</h6></div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr><th style="width:45%">Raison sociale</th><td class="fw-bold"><?= e($declaration['raison_sociale']) ?></td></tr>
                    <tr><th>N° CNSS</th><td><?= e($declaration['numero_cnss'] ?? '—') ?></td></tr>
                    <tr><th>Branche d'activité</th><td><?= e($declaration['branche_nom'] ?? '—') ?></td></tr>
                    <tr><th>Activité principale</th><td><?= e($declaration['activite_principale'] ?? '—') ?></td></tr>
                    <tr><th>Nationalité</th><td><?= e($declaration['nationalite'] ?? '—') ?></td></tr>
                    <tr><th>Localité</th><td><?= e($declaration['localite'] ?? '—') ?></td></tr>
                    <tr><th>Région</th><td><?= e($declaration['region_nom'] ?? '—') ?></td></tr>
                    <tr><th>Masse salariale</th><td class="fw-bold text-success"><?= number_format((float)($declaration['masse_salariale'] ?? 0), 2, ',', ' ') ?> FCFA</td></tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Infos déclaration -->
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-header"><h6 class="mb-0"><i class="bi bi-info-circle me-2 text-info"></i>Informations déclaration</h6></div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr><th style="width:45%">Agent saisie</th><td><?= e(($declaration['agent_prenom'] ?? '') . ' ' . ($declaration['agent_nom'] ?? '')) ?></td></tr>
                    <tr><th>Date soumission</th><td><?= formatDate($declaration['date_soumission']) ?></td></tr>
                    <tr><th>Créé le</th><td><?= formatDateTime($declaration['created_at']) ?></td></tr>
                    <tr><th>Modifié le</th><td><?= formatDateTime($declaration['updated_at']) ?></td></tr>
                    <tr><th>Observations</th><td><?= e($declaration['observations'] ?? '—') ?></td></tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Effectifs mensuels -->
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header"><h6 class="mb-0"><i class="bi bi-people me-2 text-success"></i>Effectifs mensuels</h6></div>
            <div class="card-body p-0">
                <?php if (empty($effectifsMensuels)): ?>
                <p class="text-muted text-center py-3 mb-0">Aucune donnée mensuelle.</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm table-striped mb-0">
                        <thead class="table-light"><tr>
                            <?php $moisLabels = ['Jan','Fév','Mar','Avr','Mai','Jun','Jul','Aoû','Sep','Oct','Nov','Déc']; ?>
                            <?php foreach ($effectifsMensuels as $em): ?>
                            <th class="text-center"><?= $moisLabels[$em['mois'] - 1] ?></th>
                            <?php endforeach; ?>
                            <th class="text-center text-primary">Moyen</th>
                        </tr></thead>
                        <tbody><tr>
                            <?php $somme = 0; foreach ($effectifsMensuels as $em): $somme += $em['effectif']; ?>
                            <td class="text-center"><?= number_format($em['effectif']) ?></td>
                            <?php endforeach; ?>
                            <td class="text-center fw-bold text-primary">
                                <?= count($effectifsMensuels) > 0 ? number_format($somme / count($effectifsMensuels), 1) : 0 ?>
                            </td>
                        </tr></tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Effectifs par catégorie -->
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header"><h6 class="mb-0"><i class="bi bi-bar-chart me-2 text-warning"></i>Effectifs par catégorie professionnelle</h6></div>
            <div class="card-body p-0">
                <?php if (empty($categoriesEffectifs)): ?>
                <p class="text-muted text-center py-3 mb-0">Aucune donnée.</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Catégorie</th>
                                <th class="text-center" colspan="2">Nigériens</th>
                                <th class="text-center" colspan="2">Africains</th>
                                <th class="text-center" colspan="2">Autres</th>
                                <th class="text-center text-primary">Total</th>
                            </tr>
                            <tr class="table-light">
                                <th></th>
                                <th class="text-center">H</th><th class="text-center">F</th>
                                <th class="text-center">H</th><th class="text-center">F</th>
                                <th class="text-center">H</th><th class="text-center">F</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categoriesEffectifs as $cat):
                                $total = ($cat['nigeriens_h']  ?? 0) + ($cat['nigeriens_f']  ?? 0)
                                       + ($cat['africains_h']  ?? 0) + ($cat['africains_f']  ?? 0)
                                       + ($cat['autres_nat_h'] ?? 0) + ($cat['autres_nat_f'] ?? 0);
                                $catLabel = CATEGORIES_PROFESSIONNELLES[$cat['categorie']] ?? $cat['categorie'];
                            ?>
                            <tr>
                                <td><?= e($catLabel) ?></td>
                                <td class="text-center"><?= $cat['nigeriens_h']  ?? 0 ?></td>
                                <td class="text-center"><?= $cat['nigeriens_f']  ?? 0 ?></td>
                                <td class="text-center"><?= $cat['africains_h']  ?? 0 ?></td>
                                <td class="text-center"><?= $cat['africains_f']  ?? 0 ?></td>
                                <td class="text-center"><?= $cat['autres_nat_h'] ?? 0 ?></td>
                                <td class="text-center"><?= $cat['autres_nat_f'] ?? 0 ?></td>
                                <td class="text-center fw-bold text-primary"><?= $total ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Niveaux instruction -->
    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header"><h6 class="mb-0"><i class="bi bi-mortarboard me-2"></i>Niveaux d'instruction</h6></div>
            <div class="card-body p-0">
                <?php if (empty($niveauxInstruction)): ?>
                <p class="text-muted text-center py-3 mb-0">Aucune donnée.</p>
                <?php else: ?>
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>Niveau</th><th class="text-center">H</th><th class="text-center">F</th></tr></thead>
                    <tbody>
                        <?php foreach ($niveauxInstruction as $niv):
                            $label = NIVEAUX_INSTRUCTION[$niv['niveau']] ?? $niv['niveau'];
                        ?>
                        <tr>
                            <td class="small"><?= e($label) ?></td>
                            <td class="text-center"><?= $niv['effectif_h'] ?? 0 ?></td>
                            <td class="text-center"><?= $niv['effectif_f'] ?? 0 ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Pertes d'emploi -->
    <?php if (!empty($pertesEmploi)): ?>
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-header"><h6 class="mb-0"><i class="bi bi-person-dash me-2 text-danger"></i>Pertes d'emploi</h6></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>Motif</th><th class="text-center">Hommes</th><th class="text-center">Femmes</th><th class="text-center">Total</th></tr></thead>
                    <tbody>
                        <?php foreach ($pertesEmploi as $p):
                            $label = MOTIFS_PERTE_EMPLOI[$p['motif']] ?? $p['motif'];
                        ?>
                        <tr>
                            <td><?= e($label) ?></td>
                            <td class="text-center"><?= $p['effectif_h'] ?? 0 ?></td>
                            <td class="text-center"><?= $p['effectif_f'] ?? 0 ?></td>
                            <td class="text-center fw-bold"><?= ($p['effectif_h'] ?? 0) + ($p['effectif_f'] ?? 0) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Perspectives -->
    <?php if ($perspective): ?>
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-header"><h6 class="mb-0"><i class="bi bi-graph-up-arrow me-2 text-info"></i>Perspectives d'emploi</h6></div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr><th>Perspective</th><td class="fw-bold"><?= e($perspective['perspective'] ?? '—') ?></td></tr>
                    <tr><th>Recrutements prévus (H)</th><td><?= $perspective['recrutements_prevus_h'] ?? 0 ?></td></tr>
                    <tr><th>Recrutements prévus (F)</th><td><?= $perspective['recrutements_prevus_f'] ?? 0 ?></td></tr>
                    <tr><th>Départs prévus (H)</th><td><?= $perspective['departs_prevus_h'] ?? 0 ?></td></tr>
                    <tr><th>Départs prévus (F)</th><td><?= $perspective['departs_prevus_f'] ?? 0 ?></td></tr>
                    <tr><th>Commentaire</th><td><?= e($perspective['commentaire'] ?? '—') ?></td></tr>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Historique -->
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header"><h6 class="mb-0"><i class="bi bi-clock-history me-2"></i>Historique</h6></div>
            <div class="card-body p-0">
                <?php if (empty($historique)): ?>
                <p class="text-muted text-center py-3 mb-0">Aucun historique disponible.</p>
                <?php else: ?>
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>Date</th><th>Action</th><th>Par</th><th>Ancien statut</th><th>Nouveau statut</th></tr></thead>
                    <tbody>
                        <?php foreach ($historique as $h): ?>
                        <tr>
                            <td class="small text-muted"><?= formatDateTime($h['created_at']) ?></td>
                            <td><code><?= e($h['action']) ?></code></td>
                            <td><?= e(($h['prenom'] ?? '') . ' ' . ($h['nom'] ?? 'Système')) ?></td>
                            <td><?= e($h['ancien_statut'] ?? '—') ?></td>
                            <td><?= e($h['nouveau_statut'] ?? '—') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modals Valider/Rejeter -->
<div class="modal fade" id="modalValider" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title text-success"><i class="bi bi-check-circle me-2"></i>Valider la déclaration</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form method="POST" action="<?= url('admin/declaration/' . $declaration['id'] . '/valider') ?>">
                <?= csrfField() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Observations</label>
                        <textarea name="observations" class="form-control" rows="3" placeholder="Commentaire facultatif…"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Valider</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalRejeter" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title text-danger"><i class="bi bi-x-circle me-2"></i>Rejeter la déclaration</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form method="POST" action="<?= url('admin/declaration/' . $declaration['id'] . '/rejeter') ?>">
                <?= csrfField() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Motif de rejet <span class="text-danger">*</span></label>
                        <textarea name="motif_rejet" class="form-control" rows="3" required placeholder="Expliquer le motif…"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger"><i class="bi bi-x-lg me-1"></i>Rejeter</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Retour Brouillon -->
<div class="modal fade" id="modalRetourBrouillon" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title"><i class="bi bi-arrow-counterclockwise me-2"></i>Retour en brouillon</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= url('admin/declaration/' . $declaration['id'] . '/retour-brouillon') ?>">
                <?= csrfField() ?>
                <div class="modal-body">
                    <div class="alert alert-warning py-2 mb-3">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        L'agent pourra à nouveau modifier et re-soumettre cette déclaration.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Motif / Commentaire</label>
                        <textarea name="motif_retour" class="form-control" rows="3"
                                  placeholder="Précisez la raison du retour en brouillon (optionnel)…"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>Confirmer le retour
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.querySelector('.btn-valider-detail')?.addEventListener('click', function () {
    new bootstrap.Modal(document.getElementById('modalValider')).show();
});
document.querySelector('.btn-rejeter-detail')?.addEventListener('click', function () {
    new bootstrap.Modal(document.getElementById('modalRejeter')).show();
});
document.querySelector('.btn-retour-brouillon')?.addEventListener('click', function () {
    new bootstrap.Modal(document.getElementById('modalRetourBrouillon')).show();
});
</script>
