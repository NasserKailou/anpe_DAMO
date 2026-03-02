<?php /** @var array $declaration, $effectifsMensuels, $categoriesEffectifs, $niveauxInstruction, $formations, $pertesEmploi, $perspective, $effectifsEtrangers, $historique */ ?>

<div class="page-header">
    <div class="page-header-left">
        <h1><i class="bi bi-file-earmark-text me-2 text-primary"></i>
            Déclaration <?= e($declaration['code_questionnaire']) ?>
        </h1>
        <p class="text-muted mb-0">
            <?= e($declaration['raison_sociale']) ?> &mdash; Campagne <?= e($declaration['annee']) ?>
        </p>
    </div>
    <div class="page-header-right">
        <?php if ($declaration['statut'] === 'soumise'): ?>
            <button class="btn btn-success me-2" onclick="validerDeclaration(<?= (int)$declaration['id'] ?>)">
                <i class="bi bi-check-circle me-1"></i> Valider
            </button>
            <button class="btn btn-danger" onclick="rejeterDeclaration(<?= (int)$declaration['id'] ?>)">
                <i class="bi bi-x-circle me-1"></i> Rejeter
            </button>
        <?php endif; ?>
        <a href="/admin/declarations" class="btn btn-outline-secondary ms-2">
            <i class="bi bi-arrow-left me-1"></i> Retour
        </a>
    </div>
</div>

<!-- Statut Badge -->
<div class="mb-3">
    <span class="badge badge-lg fs-6 bg-<?= statutBadgeClass($declaration['statut']) ?>">
        <?= statutLabel($declaration['statut']) ?>
    </span>
    <?php if ($declaration['statut'] === 'rejetee' && !empty($declaration['motif_rejet'])): ?>
        <div class="alert alert-danger mt-2">
            <strong>Motif de rejet :</strong> <?= e($declaration['motif_rejet']) ?>
        </div>
    <?php endif; ?>
</div>

<!-- Infos générales -->
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-building me-2"></i>Entreprise</div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr><th width="40%">Raison sociale</th><td><?= e($declaration['raison_sociale']) ?></td></tr>
                    <tr><th>N° CNSS</th><td><?= e($declaration['numero_cnss']) ?></td></tr>
                    <tr><th>Activité principale</th><td><?= e($declaration['activite_principale'] ?? '—') ?></td></tr>
                    <tr><th>Nationalité</th><td><?= e($declaration['nationalite'] ?? '—') ?></td></tr>
                    <tr><th>Adresse</th><td><?= e($declaration['adresse'] ?? '—') ?>, <?= e($declaration['quartier'] ?? '') ?></td></tr>
                    <tr><th>Téléphone</th><td><?= e($declaration['ent_tel'] ?? '—') ?></td></tr>
                    <tr><th>Email</th><td><?= e($declaration['ent_email'] ?? '—') ?></td></tr>
                    <tr><th>Boîte postale</th><td><?= e($declaration['boite_postale'] ?? '—') ?></td></tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-info-circle me-2"></i>Déclaration</div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr><th width="40%">Code questionnaire</th><td><?= e($declaration['code_questionnaire']) ?></td></tr>
                    <tr><th>Région</th><td><?= e($declaration['region_nom']) ?></td></tr>
                    <tr><th>Branche</th><td><?= e($declaration['branche_nom'] ?? '—') ?></td></tr>
                    <tr><th>Campagne</th><td><?= e($declaration['campagne_libelle'] ?? $declaration['annee']) ?></td></tr>
                    <tr><th>Agent saisie</th><td><?= e($declaration['agent_prenom'] . ' ' . $declaration['agent_nom']) ?></td></tr>
                    <tr><th>Date soumission</th><td><?= formatDateTime($declaration['date_soumission'] ?? '') ?></td></tr>
                    <tr><th>Validateur</th><td><?= !empty($declaration['validateur_nom']) ? e($declaration['validateur_prenom'].' '.$declaration['validateur_nom']) : '—' ?></td></tr>
                    <tr><th>Date validation</th><td><?= formatDateTime($declaration['date_validation'] ?? '') ?></td></tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Effectifs mensuels -->
<?php if (!empty($effectifsMensuels)): ?>
<div class="card mb-4">
    <div class="card-header"><i class="bi bi-calendar3 me-2"></i>Effectifs mensuels</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Mois</th><th>Total Hommes</th><th>Total Femmes</th><th>Total Général</th>
                        <th>Permanents H</th><th>Permanents F</th>
                        <th>Temporaires H</th><th>Temporaires F</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($effectifsMensuels as $em): ?>
                    <tr>
                        <td><?= nomMois($em['mois']) ?></td>
                        <td><?= formatNumber($em['total_hommes'] ?? 0) ?></td>
                        <td><?= formatNumber($em['total_femmes'] ?? 0) ?></td>
                        <td><strong><?= formatNumber(($em['total_hommes'] ?? 0) + ($em['total_femmes'] ?? 0)) ?></strong></td>
                        <td><?= formatNumber($em['permanents_hommes'] ?? 0) ?></td>
                        <td><?= formatNumber($em['permanents_femmes'] ?? 0) ?></td>
                        <td><?= formatNumber($em['temporaires_hommes'] ?? 0) ?></td>
                        <td><?= formatNumber($em['temporaires_femmes'] ?? 0) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Catégories d'effectifs -->
<?php if (!empty($categoriesEffectifs)): ?>
<div class="card mb-4">
    <div class="card-header"><i class="bi bi-people me-2"></i>Effectifs par catégorie professionnelle</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Catégorie</th><th>Hommes</th><th>Femmes</th><th>Total</th>
                        <th>Masse salariale (FCFA)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categoriesEffectifs as $cat): ?>
                    <tr>
                        <td><?= e($cat['categorie']) ?></td>
                        <td><?= formatNumber($cat['hommes'] ?? 0) ?></td>
                        <td><?= formatNumber($cat['femmes'] ?? 0) ?></td>
                        <td><strong><?= formatNumber(($cat['hommes'] ?? 0) + ($cat['femmes'] ?? 0)) ?></strong></td>
                        <td><?= formatNumber($cat['masse_salariale'] ?? 0) ?> FCFA</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Niveaux d'instruction -->
<?php if (!empty($niveauxInstruction)): ?>
<div class="card mb-4">
    <div class="card-header"><i class="bi bi-mortarboard me-2"></i>Niveaux d'instruction</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-striped mb-0">
                <thead class="table-light">
                    <tr><th>Niveau</th><th>Hommes</th><th>Femmes</th><th>Total</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($niveauxInstruction as $ni): ?>
                    <tr>
                        <td><?= e($ni['niveau']) ?></td>
                        <td><?= formatNumber($ni['hommes'] ?? 0) ?></td>
                        <td><?= formatNumber($ni['femmes'] ?? 0) ?></td>
                        <td><?= formatNumber(($ni['hommes'] ?? 0) + ($ni['femmes'] ?? 0)) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Formations -->
<?php if (!empty($formations)): ?>
<div class="card mb-4">
    <div class="card-header"><i class="bi bi-journal-bookmark me-2"></i>Formations professionnelles</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-striped mb-0">
                <thead class="table-light">
                    <tr><th>Type formation</th><th>Spécialité/Domaine</th><th>Hommes</th><th>Femmes</th><th>Durée</th><th>Coût (FCFA)</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($formations as $f): ?>
                    <tr>
                        <td><?= e($f['type_formation'] ?? '—') ?></td>
                        <td><?= e($f['specialite'] ?? $f['domaine'] ?? '—') ?></td>
                        <td><?= formatNumber($f['hommes'] ?? 0) ?></td>
                        <td><?= formatNumber($f['femmes'] ?? 0) ?></td>
                        <td><?= e($f['duree'] ?? '—') ?></td>
                        <td><?= formatNumber($f['cout'] ?? 0) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Pertes d'emploi -->
<?php if (!empty($pertesEmploi)): ?>
<div class="card mb-4">
    <div class="card-header"><i class="bi bi-person-x me-2"></i>Pertes d'emploi</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-striped mb-0">
                <thead class="table-light">
                    <tr><th>Motif</th><th>Hommes</th><th>Femmes</th><th>Total</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($pertesEmploi as $pe): ?>
                    <tr>
                        <td><?= e($pe['motif'] ?? $pe['raison'] ?? '—') ?></td>
                        <td><?= formatNumber($pe['hommes'] ?? 0) ?></td>
                        <td><?= formatNumber($pe['femmes'] ?? 0) ?></td>
                        <td><?= formatNumber(($pe['hommes'] ?? 0) + ($pe['femmes'] ?? 0)) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Perspectives -->
<?php if (!empty($perspective)): ?>
<div class="card mb-4">
    <div class="card-header"><i class="bi bi-graph-up me-2"></i>Perspectives d'emploi</div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4 text-center">
                <h5>Recrutements prévus</h5>
                <p class="fs-4"><?= formatNumber($perspective['recrutements_prevus'] ?? 0) ?></p>
            </div>
            <div class="col-md-4 text-center">
                <h5>Départs prévus</h5>
                <p class="fs-4"><?= formatNumber($perspective['departs_prevus'] ?? 0) ?></p>
            </div>
            <div class="col-md-4 text-center">
                <h5>Observations</h5>
                <p><?= e($perspective['observations'] ?? '—') ?></p>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Effectifs étrangers -->
<?php if (!empty($effectifsEtrangers)): ?>
<div class="card mb-4">
    <div class="card-header"><i class="bi bi-globe me-2"></i>Effectifs étrangers</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-striped mb-0">
                <thead class="table-light">
                    <tr><th>Nationalité</th><th>Catégorie</th><th>Hommes</th><th>Femmes</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($effectifsEtrangers as $ee): ?>
                    <tr>
                        <td><?= e($ee['nationalite'] ?? '—') ?></td>
                        <td><?= e($ee['categorie'] ?? '—') ?></td>
                        <td><?= formatNumber($ee['hommes'] ?? 0) ?></td>
                        <td><?= formatNumber($ee['femmes'] ?? 0) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Historique -->
<?php if (!empty($historique)): ?>
<div class="card mb-4">
    <div class="card-header"><i class="bi bi-clock-history me-2"></i>Historique des actions</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-striped mb-0">
                <thead class="table-light">
                    <tr><th>Date</th><th>Action</th><th>Utilisateur</th><th>Commentaire</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($historique as $h): ?>
                    <tr>
                        <td><?= formatDateTime($h['created_at']) ?></td>
                        <td><span class="badge bg-secondary"><?= e($h['action']) ?></span></td>
                        <td><?= e(trim(($h['prenom'] ?? '') . ' ' . ($h['nom'] ?? '')) ?: '—') ?></td>
                        <td><?= e($h['commentaire'] ?? '—') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Export CSV -->
<div class="text-end mb-4">
    <a href="/admin/declarations/export?id=<?= (int)$declaration['id'] ?>" class="btn btn-outline-success">
        <i class="bi bi-download me-1"></i> Exporter CSV
    </a>
    <a href="/admin/declarations" class="btn btn-outline-secondary ms-2">
        <i class="bi bi-arrow-left me-1"></i> Retour à la liste
    </a>
</div>
