<?php // Vue : Aperçu / Récapitulatif déclaration ?>
<?php $ent = $data['entreprise']; ?>

<div class="page-header">
    <div>
        <h1><i class="bi bi-file-earmark-check me-2 text-primary"></i>Aperçu déclaration</h1>
        <p><?= e($declaration['raison_sociale']) ?> — <?= e($declaration['campagne_libelle']) ?></p>
    </div>
    <div class="page-header-right">
        <span class="badge-statut badge-<?= e($declaration['statut']) ?> me-2"><?= statutLabel($declaration['statut']) ?></span>
        <?php if (in_array($declaration['statut'], ['brouillon', 'corrigee'])): ?>
        <a href="/agent/declaration/<?= $declaration['id'] ?>/saisie" class="btn btn-warning btn-sm">
            <i class="bi bi-pencil me-1"></i>Modifier
        </a>
        <?php endif; ?>
        <a href="/agent/declarations" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Mes déclarations
        </a>
    </div>
</div>

<?php if ($declaration['statut'] === 'rejetee'): ?>
<div class="alert alert-danger">
    <strong><i class="bi bi-x-circle me-2"></i>Déclaration rejetée</strong>
    <?php if ($declaration['motif_rejet']): ?>
    <br>Motif : <?= e($declaration['motif_rejet']) ?>
    <?php endif; ?>
    <br><a href="/agent/declaration/<?= $declaration['id'] ?>/modifier" class="btn btn-sm btn-warning mt-2">
        <i class="bi bi-arrow-repeat me-1"></i>Corriger et resoumettre
    </a>
</div>
<?php elseif ($declaration['statut'] === 'validee'): ?>
<div class="alert alert-success">
    <i class="bi bi-check-circle me-2"></i>
    Déclaration validée le <?= formatDateTime($declaration['date_validation']) ?>.
</div>
<?php elseif ($declaration['statut'] === 'soumise'): ?>
<div class="alert alert-info">
    <i class="bi bi-hourglass-split me-2"></i>
    Déclaration soumise le <?= formatDateTime($declaration['date_soumission']) ?> — en attente de validation par l'ANPE.
</div>
<?php elseif (in_array($declaration['statut'], ['brouillon', 'corrigee'])): ?>
<div class="alert alert-warning">
    <i class="bi bi-pencil me-2"></i>
    Déclaration en cours de saisie. Complétez tous les champs avant de soumettre.
    <div class="mt-2">
        <form method="POST" action="/agent/declaration/<?= $declaration['id'] ?>/soumettre" class="d-inline">
            <?= csrfField() ?>
            <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Confirmer la soumission définitive ?')">
                <i class="bi bi-send me-1"></i>Soumettre à l'ANPE
            </button>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- Identification -->
<div class="card mb-3">
    <div class="card-header"><h5>I. Identification de l'entreprise</h5></div>
    <div class="card-body">
        <div class="row g-2">
            <div class="col-md-6"><strong>Raison sociale :</strong> <?= e($ent['raison_sociale'] ?? '-') ?></div>
            <div class="col-md-6"><strong>N° CNSS :</strong> <?= e($ent['numero_cnss'] ?? '-') ?></div>
            <div class="col-md-6"><strong>Nationalité du capital :</strong> <?= e($ent['nationalite'] ?? '-') ?></div>
            <div class="col-md-6"><strong>Activité principale :</strong> <?= e($ent['activite_principale'] ?? '-') ?></div>
            <div class="col-md-6"><strong>Région :</strong> <?= e($declaration['region_nom']) ?></div>
            <div class="col-md-6"><strong>Masse salariale :</strong> <?= formatNumber($declaration['masse_salariale'] ?? 0) ?> FCFA</div>
        </div>
    </div>
</div>

<!-- Effectifs mensuels -->
<?php if (!empty($data['effectifs_mensuels'])): ?>
<div class="card mb-3">
    <div class="card-header"><h5>II. Effectifs actifs mensuels</h5></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm table-bordered text-center">
                <thead><tr>
                    <?php foreach (range(1, 12) as $m): ?>
                    <th><?= substr(nomMois($m), 0, 3) ?></th>
                    <?php endforeach; ?>
                    <th>Moy.</th>
                </tr></thead>
                <tbody><tr>
                    <?php $total = 0; foreach (range(1, 12) as $m): $total += (int)($data['effectifs_mensuels'][$m] ?? 0); ?>
                    <td><?= $data['effectifs_mensuels'][$m] ?? 0 ?></td>
                    <?php endforeach; ?>
                    <td class="fw-bold"><?= round($total / 12) ?></td>
                </tr></tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Résumé des sections restantes -->
<div class="row g-3">
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header"><h6>III.3 Formation continue</h6></div>
            <div class="card-body">
                <?php $f = $data['formations'][0] ?? []; ?>
                <p class="mb-1"><strong><?= ($f['a_eu_formation'] ?? false) ? 'Oui' : 'Non' ?></strong></p>
                <?php if ($f['a_eu_formation'] ?? false): ?>
                <small class="text-muted"><?= e($f['nature_formation'] ?? '') ?> — <?= e($f['duree_formation'] ?? '') ?></small>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header"><h6>IV. Pertes d'emploi</h6></div>
            <div class="card-body">
                <?php
                $totalPertes = 0;
                foreach ($data['pertes'] as $p) $totalPertes += (int)($p['effectif_h'] ?? 0) + (int)($p['effectif_f'] ?? 0);
                ?>
                <p class="mb-0"><strong><?= $totalPertes ?></strong> départ(s) enregistré(s)</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header"><h6>V. Perspectives</h6></div>
            <div class="card-body">
                <?php $persp = $data['perspective'] ?? []; ?>
                <?php $perspLabels = ['hausse' => '↑ Hausse', 'stabilite' => '→ Stabilité', 'baisse' => '↓ Baisse']; ?>
                <p class="mb-0"><strong><?= $perspLabels[$persp['perspective'] ?? ''] ?? 'Non renseigné' ?></strong></p>
            </div>
        </div>
    </div>
</div>

<!-- Code questionnaire + infos -->
<div class="card mt-3">
    <div class="card-body text-center">
        <p class="mb-1">Code questionnaire</p>
        <h3><code><?= e($declaration['code_questionnaire']) ?></code></h3>
        <small class="text-muted">Conservez ce code comme référence</small>
    </div>
</div>
