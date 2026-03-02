<?php // Vue : Dashboard Agent ?>

<?php $user = currentUser(); ?>

<div class="page-header">
    <div>
        <h1><i class="bi bi-house me-2 text-primary"></i>Mon tableau de bord</h1>
        <p>Région : <strong><?= e($user['region_nom'] ?? 'Non assignée') ?></strong></p>
    </div>
    <div class="page-header-right">
        <?php if ($campagne): ?>
        <span class="badge bg-success px-3 py-2">
            <i class="bi bi-calendar-check me-1"></i>Campagne <?= e($campagne['annee']) ?> active
        </span>
        <a href="/agent/declaration/nouvelle" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Nouvelle déclaration
        </a>
        <?php else: ?>
        <span class="badge bg-warning text-dark">Aucune campagne active</span>
        <?php endif; ?>
    </div>
</div>

<!-- Stats rapides -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-card-icon" style="background:#e3f2fd;color:#0277bd"><i class="bi bi-file-earmark-text"></i></div>
            <div class="stat-card-value"><?= formatNumber($stats['total_declarations']) ?></div>
            <div class="stat-card-label">Total déclarations</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-card-icon" style="background:#fff8e1;color:#f57f17"><i class="bi bi-pencil-square"></i></div>
            <div class="stat-card-value text-warning"><?= formatNumber($stats['declarations_brouillon']) ?></div>
            <div class="stat-card-label">En cours</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-card-icon" style="background:#e8f5e9;color:#2e7d32"><i class="bi bi-check-circle"></i></div>
            <div class="stat-card-value text-success"><?= formatNumber($stats['declarations_validees']) ?></div>
            <div class="stat-card-label">Validées</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-card-icon" style="background:#ffebee;color:#c62828"><i class="bi bi-x-circle"></i></div>
            <div class="stat-card-value text-danger"><?= formatNumber($stats['declarations_rejetees']) ?></div>
            <div class="stat-card-label">Rejetées — à corriger</div>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- Déclarations récentes -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5><i class="bi bi-clock-history me-2"></i>Mes déclarations récentes</h5>
                <a href="/agent/declarations" class="btn btn-sm btn-outline-primary">Tout voir</a>
            </div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead><tr><th>Code</th><th>Entreprise</th><th>Statut</th><th>Avancement</th><th></th></tr></thead>
                    <tbody>
                        <?php foreach ($dernieresDeclarations as $d): ?>
                        <tr>
                            <td><code><?= e($d['code_questionnaire']) ?></code></td>
                            <td><?= e(truncate($d['raison_sociale'], 30)) ?></td>
                            <td><span class="badge-statut badge-<?= e($d['statut']) ?>"><?= statutLabel($d['statut']) ?></span></td>
                            <td>
                                <div style="background:#e0e0e0;border-radius:4px;height:6px;width:80px">
                                    <div style="width:<?= $d['pourcentage_completion'] ?? 0 ?>%;background:#0d47a1;border-radius:4px;height:6px"></div>
                                </div>
                                <small class="text-muted"><?= $d['pourcentage_completion'] ?? 0 ?>%</small>
                            </td>
                            <td>
                                <?php if (in_array($d['statut'], ['brouillon', 'corrigee'])): ?>
                                <a href="/agent/declaration/<?= $d['id'] ?>/saisie" class="btn btn-sm btn-primary btn-icon"><i class="bi bi-pencil"></i></a>
                                <?php elseif ($d['statut'] === 'rejetee'): ?>
                                <a href="/agent/declaration/<?= $d['id'] ?>/modifier" class="btn btn-sm btn-warning btn-icon"><i class="bi bi-arrow-repeat"></i></a>
                                <?php else: ?>
                                <a href="/agent/declaration/<?= $d['id'] ?>/apercu" class="btn btn-sm btn-outline-secondary btn-icon"><i class="bi bi-eye"></i></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($declarations)): ?>
                        <tr><td colspan="5" class="text-center text-muted py-4">Aucune déclaration</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Infos + guide -->
    <div class="col-lg-4">
        <!-- Campagne active -->
        <?php if ($campagne): ?>
        <div class="card mb-3">
            <div class="card-header"><h5><i class="bi bi-info-circle me-2"></i>Campagne en cours</h5></div>
            <div class="card-body">
                <p class="mb-1"><strong><?= e($campagne['libelle']) ?></strong></p>
                <p class="mb-1 text-muted">Du <?= formatDate($campagne['date_debut']) ?> au <?= formatDate($campagne['date_fin']) ?></p>
                <?php if ($campagne['description']): ?>
                <p class="mb-0 text-muted" style="font-size:.82rem"><?= e($campagne['description']) ?></p>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Guides disponibles -->
        <?php if (!empty($guides)): ?>
        <div class="card">
            <div class="card-header"><h5><i class="bi bi-book me-2"></i>Guides disponibles</h5></div>
            <div class="card-body">
                <?php foreach ($guides as $g): ?>
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div>
                        <div style="font-size:.84rem;font-weight:600"><?= e($g['titre']) ?></div>
                        <small class="text-muted"><?= formatFileSize($g['fichier_taille']) ?></small>
                    </div>
                    <a href="<?= e($g['fichier_path']) ?>" target="_blank" class="btn btn-sm btn-outline-primary btn-icon">
                        <i class="bi bi-download"></i>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Actions rapides -->
        <div class="card mt-3">
            <div class="card-header"><h5><i class="bi bi-lightning me-2"></i>Actions rapides</h5></div>
            <div class="card-body d-grid gap-2">
                <?php if ($campagne): ?>
                <a href="/agent/declaration/nouvelle" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg me-2"></i>Nouvelle déclaration
                </a>
                <?php endif; ?>
                <a href="/agent/declarations" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-list-ul me-2"></i>Voir toutes mes déclarations
                </a>
                <a href="/profil" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-person-gear me-2"></i>Mon profil
                </a>
            </div>
        </div>
    </div>
</div>
