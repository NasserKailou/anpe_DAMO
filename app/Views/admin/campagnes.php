<?php defined('EDAMO') or die('Accès direct interdit'); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="mb-0 fw-bold"><i class="bi bi-calendar3 text-primary me-2"></i>Campagnes DAMO</h5>
        <small class="text-muted">Gérez les campagnes de déclaration annuelle de la main d'œuvre</small>
    </div>
    <a href="<?= url('admin/campagne/nouvelle') ?>" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i>Nouvelle campagne
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <?php if (empty($campagnes)): ?>
        <div class="text-center text-muted py-5">
            <i class="bi bi-calendar-x fs-1 d-block mb-2"></i>
            Aucune campagne créée.
            <div class="mt-2">
                <a href="<?= url('admin/campagne/nouvelle') ?>" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-circle me-1"></i>Créer la première campagne
                </a>
            </div>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Année</th>
                        <th>Libellé</th>
                        <th>Période</th>
                        <th class="text-center">Déclarations</th>
                        <th class="text-center">Statut</th>
                        <th>Créée par</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($campagnes as $c): ?>
                    <tr class="<?= $c['actif'] ? 'table-success bg-opacity-10' : '' ?>">
                        <td>
                            <span class="fw-bold fs-5"><?= e($c['annee']) ?></span>
                            <?php if ($c['actif']): ?>
                            <span class="badge bg-success ms-1" style="font-size:.6rem">EN COURS</span>
                            <?php endif; ?>
                        </td>
                        <td><?= e($c['libelle']) ?></td>
                        <td class="text-muted small">
                            <i class="bi bi-calendar-event me-1"></i><?= formatDate($c['date_debut']) ?>
                            <i class="bi bi-arrow-right mx-1"></i><?= formatDate($c['date_fin']) ?>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-secondary rounded-pill"><?= number_format($c['nb_declarations'] ?? 0) ?></span>
                        </td>
                        <td class="text-center">
                            <?php if ($c['actif']): ?>
                            <span class="badge bg-success px-3">
                                <i class="bi bi-lightning-fill me-1"></i>Active
                            </span>
                            <?php else: ?>
                            <span class="badge bg-secondary px-3">
                                <i class="bi bi-lock me-1"></i>Clôturée
                            </span>
                            <?php endif; ?>
                        </td>
                        <td class="text-muted small"><?= e($c['createur_nom'] ?? '—') ?></td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <!-- Modifier -->
                                <a href="<?= url('admin/campagne/' . $c['id'] . '/modifier') ?>"
                                   class="btn btn-outline-primary" title="Modifier">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                <?php if ($c['actif']): ?>
                                <!-- Clôturer -->
                                <button type="button" class="btn btn-outline-warning btn-cloturer"
                                        data-id="<?= $c['id'] ?>"
                                        data-libelle="<?= e($c['libelle']) ?>"
                                        title="Clôturer la campagne">
                                    <i class="bi bi-lock"></i>
                                </button>
                                <!-- Rappels -->
                                <?php if (($c['nb_declarations'] ?? 0) > 0): ?>
                                <a href="<?= url('admin/campagne/' . $c['id'] . '/rappels') ?>"
                                   class="btn btn-outline-info" title="Envoyer rappels aux agents"
                                   onclick="return confirm('Envoyer des rappels aux agents avec brouillons en cours ?')">
                                    <i class="bi bi-bell"></i>
                                </a>
                                <?php endif; ?>
                                <?php else: ?>
                                <!-- Réouvrir -->
                                <button type="button" class="btn btn-outline-success btn-ouvrir"
                                        data-id="<?= $c['id'] ?>"
                                        data-libelle="<?= e($c['libelle']) ?>"
                                        title="Réouvrir la campagne">
                                    <i class="bi bi-unlock"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Clôturer -->
<div class="modal fade" id="modalCloturer" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title"><i class="bi bi-lock me-2"></i>Clôturer la campagne</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="formCloturer">
                <?= csrfField() ?>
                <div class="modal-body">
                    <div class="alert alert-warning py-2 mb-3">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        <strong>Attention !</strong> Une fois clôturée, les agents ne pourront plus créer de nouvelles déclarations pour cette campagne.
                    </div>
                    <p>Confirmer la clôture de la campagne <strong id="cloturerLibelle"></strong> ?</p>
                    <p class="text-muted small mb-0">Les déclarations en cours peuvent toujours être soumises.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-lock me-1"></i>Oui, clôturer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Réouvrir -->
<div class="modal fade" id="modalOuvrir" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-unlock me-2"></i>Réouvrir la campagne</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="formOuvrir">
                <?= csrfField() ?>
                <div class="modal-body">
                    <div class="alert alert-info py-2 mb-3">
                        <i class="bi bi-info-circle me-1"></i>
                        Les autres campagnes actives seront automatiquement clôturées. Une seule campagne peut être active à la fois.
                    </div>
                    <p>Réouvrir et activer la campagne <strong id="ouvrirLibelle"></strong> ?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-unlock me-1"></i>Oui, réouvrir
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const BASE = (window.APP_BASE ?? '').replace(/\/+$/, '');

// Clôturer
document.querySelectorAll('.btn-cloturer').forEach(btn => {
    btn.addEventListener('click', () => {
        const id      = btn.dataset.id;
        const libelle = btn.dataset.libelle;
        document.getElementById('cloturerLibelle').textContent = libelle;
        document.getElementById('formCloturer').action = `${BASE}/admin/campagne/${id}/cloturer`;
        new bootstrap.Modal(document.getElementById('modalCloturer')).show();
    });
});

// Réouvrir
document.querySelectorAll('.btn-ouvrir').forEach(btn => {
    btn.addEventListener('click', () => {
        const id      = btn.dataset.id;
        const libelle = btn.dataset.libelle;
        document.getElementById('ouvrirLibelle').textContent = libelle;
        document.getElementById('formOuvrir').action = `${BASE}/admin/campagne/${id}/ouvrir`;
        new bootstrap.Modal(document.getElementById('modalOuvrir')).show();
    });
});
</script>
