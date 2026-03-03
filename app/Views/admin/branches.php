<?php defined('EDAMO') or die('Accès direct interdit'); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="mb-0 fw-bold"><i class="bi bi-diagram-3 text-primary me-2"></i>Branches d'activité</h5>
        <small class="text-muted">Nomenclature officielle ANPE Niger — Gestion CRUD complète</small>
    </div>
    <a href="<?= url('admin/branche/nouvelle') ?>" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i>Nouvelle branche
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span class="fw-semibold">
            <i class="bi bi-list-ul me-1"></i>Liste des branches
        </span>
        <span class="badge bg-primary rounded-pill"><?= count($branches) ?></span>
    </div>
    <div class="card-body p-0">
        <?php if (empty($branches)): ?>
        <div class="text-center text-muted py-5">
            <i class="bi bi-diagram-3 fs-1 d-block mb-2 opacity-25"></i>
            <p class="mb-3">Aucune branche définie.</p>
            <a href="<?= url('admin/branche/nouvelle') ?>" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle me-1"></i>Créer la première branche
            </a>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:90px">Code</th>
                        <th>Libellé</th>
                        <th class="d-none d-md-table-cell">Description</th>
                        <th class="text-center" style="width:100px">Entreprises</th>
                        <th class="text-center" style="width:90px">Statut</th>
                        <th class="text-end" style="width:160px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($branches as $b): ?>
                    <tr class="<?= !$b['actif'] ? 'table-secondary opacity-75' : '' ?>">
                        <td>
                            <span class="badge bg-primary fs-6 fw-bold"><?= e($b['code']) ?></span>
                        </td>
                        <td class="fw-semibold"><?= e($b['libelle']) ?></td>
                        <td class="text-muted small d-none d-md-table-cell">
                            <?= e($b['description'] ?? '—') ?>
                        </td>
                        <td class="text-center">
                            <?php
                            $nbEntreprises = (int)($b['nb_entreprises'] ?? 0);
                            ?>
                            <span class="badge bg-<?= $nbEntreprises > 0 ? 'info' : 'secondary' ?> rounded-pill">
                                <?= $nbEntreprises ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <?php if ($b['actif']): ?>
                            <span class="badge bg-success">Actif</span>
                            <?php else: ?>
                            <span class="badge bg-secondary">Inactif</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <!-- Modifier -->
                                <a href="<?= url('admin/branche/' . $b['id'] . '/modifier') ?>"
                                   class="btn btn-outline-primary" title="Modifier">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                <!-- Toggle actif/inactif -->
                                <form method="POST" action="<?= url('admin/branche/' . $b['id'] . '/toggle') ?>"
                                      class="d-inline">
                                    <?= csrfField() ?>
                                    <button type="submit" class="btn btn-outline-<?= $b['actif'] ? 'warning' : 'success' ?>"
                                            title="<?= $b['actif'] ? 'Désactiver' : 'Activer' ?>"
                                            onclick="return confirm('<?= $b['actif'] ? 'Désactiver' : 'Activer' ?> cette branche ?')">
                                        <i class="bi bi-toggle-<?= $b['actif'] ? 'on' : 'off' ?>"></i>
                                    </button>
                                </form>

                                <!-- Supprimer -->
                                <button type="button"
                                        class="btn btn-outline-danger btn-supprimer-branche"
                                        data-id="<?= $b['id'] ?>"
                                        data-libelle="<?= e($b['libelle']) ?>"
                                        data-usage="<?= (int)($b['nb_entreprises'] ?? 0) ?>"
                                        title="Supprimer">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
    <div class="card-footer text-muted small d-flex justify-content-between align-items-center">
        <span>
            <i class="bi bi-info-circle me-1"></i>
            Les branches actives sont utilisées lors de la création d'entreprises.
        </span>
        <span>
            Total : <strong><?= count($branches) ?></strong> branche(s) —
            Actives : <strong><?= count(array_filter($branches, fn($b) => $b['actif'])) ?></strong>
        </span>
    </div>
</div>

<!-- Modal Suppression -->
<div class="modal fade" id="modalSupprimerBranche" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-trash me-2"></i>Supprimer la branche</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="formSupprimerBranche">
                <?= csrfField() ?>
                <div class="modal-body">
                    <div class="alert alert-danger py-2 mb-3" id="alertUsage" style="display:none">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        <strong>Suppression impossible !</strong> Cette branche est utilisée par des entreprises.
                    </div>
                    <p>Supprimer définitivement la branche <strong id="supprimerLibelle"></strong> ?</p>
                    <p class="text-muted small mb-0">Cette action est irréversible.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger" id="btnConfirmerSupprimer">
                        <i class="bi bi-trash me-1"></i>Supprimer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const BASE = (window.APP_BASE ?? '').replace(/\/+$/, '');

document.querySelectorAll('.btn-supprimer-branche').forEach(btn => {
    btn.addEventListener('click', () => {
        const id      = btn.dataset.id;
        const libelle = btn.dataset.libelle;
        const usage   = parseInt(btn.dataset.usage) || 0;

        document.getElementById('supprimerLibelle').textContent = libelle;
        document.getElementById('formSupprimerBranche').action = `${BASE}/admin/branche/${id}/supprimer`;

        const alertUsage = document.getElementById('alertUsage');
        const btnConfirm = document.getElementById('btnConfirmerSupprimer');

        if (usage > 0) {
            alertUsage.style.display = '';
            alertUsage.innerHTML = `<i class="bi bi-exclamation-triangle me-1"></i>
                <strong>Suppression impossible !</strong> Cette branche est utilisée par ${usage} entreprise(s).`;
            btnConfirm.disabled = true;
        } else {
            alertUsage.style.display = 'none';
            btnConfirm.disabled = false;
        }

        new bootstrap.Modal(document.getElementById('modalSupprimerBranche')).show();
    });
});
</script>
