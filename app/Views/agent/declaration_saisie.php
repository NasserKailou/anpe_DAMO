<?php
/**
 * FORMULAIRE DE DECLARATION ANNUELLE DE LA MAIN D'ŒUVRE — RAMO 2025
 * Fidèle à l'imprimé officiel ANPE Niger (mis à jour février 2025)
 *
 * Sections :
 *  I.    Identification de l'entreprise
 *  II.   Renseignements statistiques généraux (Effectifs mensuels)
 *  III.1 Répartition par catégories professionnelles, sexes et origines
 *  III.2 Répartition par niveaux d'instruction et catégories
 *  III.3 Formation professionnelle continue
 *  IV.   Pertes d'emploi
 *  V.    Perspectives d'emploi
 *  VI.   Effectifs du personnel par nationalité (étrangers)
 */
if (!defined('EDAMO')) exit;

$declaration = $declaration ?? [];
$entreprise  = $data['entreprise'] ?? [];
$etape       = (int)($etape ?? 1);
$data        = $data ?? [];

$statut  = $declaration['statut'] ?? 'brouillon';
$canEdit = in_array($statut, ['brouillon', 'corrigee']);
$decId   = $declaration['id'] ?? 0;
$pct     = $declaration['pourcentage_completion'] ?? 0;

// Étapes du wizard (8 étapes pour respecter toutes les sections)
$etapeTitres = [
    1 => ['titre' => 'Identification',        'icon' => 'bi-building',      'section' => 'I'],
    2 => ['titre' => 'Effectifs mensuels',    'icon' => 'bi-calendar3',     'section' => 'II'],
    3 => ['titre' => 'Catégories/Origines',   'icon' => 'bi-people',        'section' => 'III.1'],
    4 => ['titre' => 'Niveaux d\'instruction','icon' => 'bi-mortarboard',   'section' => 'III.2'],
    5 => ['titre' => 'Formation prof.',       'icon' => 'bi-award',         'section' => 'III.3'],
    6 => ['titre' => 'Pertes d\'emploi',      'icon' => 'bi-person-dash',   'section' => 'IV'],
    7 => ['titre' => 'Perspectives',          'icon' => 'bi-graph-up',      'section' => 'V'],
    8 => ['titre' => 'Eff. étrangers',        'icon' => 'bi-globe',         'section' => 'VI'],
];
$totalEtapes = count($etapeTitres);

$moisLabels = ['','Jan','Fév','Mars','Avr','Mai','Juin','Juil','Août','Sept','Oct','Nov','Déc'];

// Données du formulaire
$catMap      = $data['categories'] ?? [];
$niveauMap   = $data['niveaux'] ?? [];
$formations  = $data['formations'] ?? [];
$pertesMap   = $data['pertes'] ?? [];
$persp       = $data['perspective'] ?? [];
$etrangers   = $data['etrangers'] ?? [];
$mensuels    = $data['effectifs_mensuels'] ?? [];

// Catégories dans l'ordre exact du formulaire RAMO
// ⚠ Les clés DOIVENT correspondre aux valeurs de la base de données (ENUM SQL)
$categories = defined('CATEGORIES_PROFESSIONNELLES')
    ? CATEGORIES_PROFESSIONNELLES
    : [
        'cadres_superieurs'    => 'Cadres supérieurs',
        'agents_maitrise'      => 'Agents de maîtrise',
        'employes_bureau'      => 'Employés de bureau',
        'ouvriers_qualifies'   => 'Ouvriers qualifiés',
        'ouvriers_specialises' => 'Ouvriers spécialisés',
        'manœuvres'            => 'Manœuvres',
        'apprentis_stagiaires' => 'Apprentis / Stagiaires',
    ];

// Niveaux dans l'ordre exact du formulaire RAMO
// ⚠ Les clés DOIVENT correspondre aux valeurs de la base de données (ENUM SQL)
$niveaux = defined('NIVEAUX_INSTRUCTION')
    ? NIVEAUX_INSTRUCTION
    : [
        'non_scolarise'  => 'Non scolarisé',
        'primaire'       => 'Primaire',
        'secondaire_1er' => 'Secondaire 1er cycle',
        'secondaire_2eme'=> 'Secondaire 2ème cycle',
        'moyen_prof'     => 'Moyen (Ens. professionnel et technique)',
        'superieur_prof' => 'Supérieur (Ens. professionnel et technique)',
        'superieur_1'    => 'Supérieur 1 (Bac + 2)',
        'superieur_2'    => 'Supérieur 2 (Bac + 3 ou 4)',
        'superieur_3'    => 'Supérieur 3 (Bac + 5 et plus)',
    ];

$motifs = [
    'licenciement' => '1- Licenciement',
    'demission'    => '2- Démission',
    'fin_contrat'  => '3- Fin de Contrat',
    'retraite'     => '4- Retraite',
    'deces'        => '5- Décès',
    'autres'       => '6- Autres motifs (à préciser)',
];
?>

<style>
/* ── Wizard steps ── */
.ramo-steps{display:flex;align-items:flex-start;gap:0;overflow-x:auto;padding:4px 0}
.ramo-step{display:flex;flex-direction:column;align-items:center;flex:1;min-width:70px;cursor:pointer;position:relative}
.ramo-step-num{width:34px;height:34px;border-radius:50%;border:2px solid #dee2e6;background:#fff;color:#6c757d;
    display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.82rem;transition:all .25s}
.ramo-step.done   .ramo-step-num{background:#198754;border-color:#198754;color:#fff}
.ramo-step.active .ramo-step-num{background:#0d6efd;border-color:#0d6efd;color:#fff}
.ramo-step-label{font-size:.65rem;text-align:center;margin-top:3px;color:#6c757d;line-height:1.2}
.ramo-step.active .ramo-step-label{color:#0d6efd;font-weight:600}
.ramo-step.done   .ramo-step-label{color:#198754}
.ramo-sep{flex:1;height:2px;background:#dee2e6;margin-top:17px;min-width:10px;flex-shrink:0}
.ramo-sep.done{background:#198754}

/* ── Sections ── */
.form-section{display:none}
.form-section.active{display:block}
.section-badge{display:inline-flex;align-items:center;gap:6px;background:#e9f0ff;color:#0d47a1;
    border-radius:20px;padding:3px 12px;font-size:.78rem;font-weight:700;margin-bottom:4px}

/* ── Tables ── */
.table-ramo th{background:#e8f0fe;font-size:.75rem;font-weight:600;text-align:center;vertical-align:middle}
.table-ramo td{font-size:.8rem;vertical-align:middle}
.table-ramo input[type=number]{width:68px;text-align:center;padding:3px 4px;font-size:.8rem}
.table-ramo .total-col{background:#f1f8ff;font-weight:700;text-align:center}
.table-ramo .total-row td{background:#e8f5e9;font-weight:700}
.table-ramo .row-label{font-weight:600;font-size:.78rem;min-width:140px}

/* ── Formation rows ── */
.formation-row td{padding:4px 6px}
.formation-row input,.formation-row select{font-size:.8rem}

/* ── Progress ── */
.progress-ramo{height:6px;border-radius:10px;background:#e0e0e0;overflow:hidden;margin-bottom:2px}
.progress-ramo .fill{height:100%;background:linear-gradient(90deg,#0d47a1,#1565c0);border-radius:10px;transition:width .4s}

/* ── Autosave ── */
.autosave-indicator{font-size:.75rem;display:flex;align-items:center;gap:5px}
.autosave-indicator.saving{color:#ff9800}
.autosave-indicator.saved{color:#4caf50}
.autosave-indicator.error{color:#f44336}
</style>

<!-- Entête déclaration -->
<div class="row mb-3 g-3">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body py-2">
                <div class="d-flex align-items-center gap-3">
                    <div class="flex-grow-1">
                        <div class="fw-bold text-primary small">
                            <i class="bi bi-file-earmark-text me-1"></i><?= e($declaration['code_questionnaire'] ?? '') ?>
                        </div>
                        <div class="fw-semibold"><?= e($entreprise['raison_sociale'] ?? '') ?></div>
                        <small class="text-muted">Campagne : <?= e($entreprise['campagne_libelle'] ?? '') ?></small>
                    </div>
                    <?php if ($canEdit): ?>
                    <a href="<?= url("agent/declaration/$decId/import-csv") ?>" class="btn btn-sm btn-outline-warning" title="Importer des données depuis un fichier CSV">
                        <i class="bi bi-file-earmark-spreadsheet me-1"></i>Import CSV
                    </a>
                    <?php endif; ?>
                    <span class="badge bg-<?= $statut === 'brouillon' ? 'secondary' : ($statut === 'corrigee' ? 'warning text-dark' : ($statut === 'soumise' ? 'info' : 'success')) ?> fs-6">
                        <?= ucfirst($statut) ?>
                    </span>
                </div>
                <div class="progress-ramo mt-2"><div class="fill" style="width:<?= $pct ?>%"></div></div>
                <small class="text-muted" id="progress-label">Avancement : <?= $pct ?>% — Étape <?= $etape ?>/<?= $totalEtapes ?></small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body py-2 d-flex flex-column justify-content-center">
                <div id="autosave-status" class="autosave-indicator saved">
                    <i class="bi bi-cloud-check"></i><span>Prêt</span>
                </div>
                <?php if (!$canEdit): ?>
                    <div class="alert alert-warning py-1 mb-0 mt-2 small">
                        <i class="bi bi-lock me-1"></i>Déclaration en lecture seule (statut : <?= $statut ?>)
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Navigation wizard -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <div class="ramo-steps">
            <?php foreach ($etapeTitres as $num => $info): ?>
                <?php $cls = $num < $etape ? 'done' : ($num === $etape ? 'active' : ''); ?>
                <div class="ramo-step <?= $cls ?>" data-step="<?= $num ?>" onclick="goToStep(<?= $num ?>)">
                    <div class="ramo-step-num">
                        <?= $num < $etape ? '<i class="bi bi-check-lg"></i>' : $num ?>
                    </div>
                    <div class="ramo-step-label">
                        <div style="font-size:.6rem;color:#aaa"><?= $info['section'] ?></div>
                        <?= $info['titre'] ?>
                    </div>
                </div>
                <?php if ($num < $totalEtapes): ?>
                    <div class="ramo-sep <?= $num < $etape ? 'done' : '' ?>"></div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- ════════════════════════════════════════════════════════════
     FORMULAIRE PRINCIPAL
════════════════════════════════════════════════════════════ -->
<form id="wizard-form"
      method="POST"
      action="<?= url("agent/declaration/$decId/sauvegarder") ?>"
      data-decl-id="<?= $decId ?>"
      data-etape="<?= $etape ?>"
      data-total-etapes="<?= $totalEtapes ?>">

    <?= csrfField() ?>
    <input type="hidden" name="etape" id="input-etape" value="<?= $etape ?>">

    <!-- ╔══════════════════════════════════════════════════════╗
         ║  ÉTAPE 1 — Section I : Identification               ║
         ╚══════════════════════════════════════════════════════╝ -->
    <div class="form-section <?= $etape === 1 ? 'active' : '' ?>" id="etape-1">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-bottom py-3">
                <div class="section-badge"><span>I</span> Identification de l'entreprise</div>
                <small class="text-muted">Renseignez l'identité complète de l'établissement</small>
            </div>
            <div class="card-body">
                <div class="row g-3">

                    <div class="col-12">
                        <label class="form-label fw-semibold">Nom ou raison sociale <span class="text-danger">*</span></label>
                        <input type="text" name="raison_sociale" class="form-control form-control-sm"
                               value="<?= e($entreprise['raison_sociale'] ?? '') ?>"
                               placeholder="Nom complet de l'entreprise ou de l'établissement"
                               <?= !$canEdit ? 'readonly' : '' ?> required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nationalité de l'entreprise</label>
                        <input type="text" name="nationalite" class="form-control form-control-sm"
                               value="<?= e($entreprise['nationalite'] ?? '') ?>"
                               placeholder="Ex: Nigérienne, Française…"
                               <?= !$canEdit ? 'readonly' : '' ?>>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Branche d'activité</label>
                        <select name="branche_id" class="form-select form-select-sm" <?= !$canEdit ? 'disabled' : '' ?>>
                            <option value="">-- Sélectionner --</option>
                            <?php foreach ($data['branches'] ?? [] as $b): ?>
                                <option value="<?= $b['id'] ?>" <?= ($entreprise['branche_id'] ?? '') == $b['id'] ? 'selected' : '' ?>>
                                    <?= e($b['code']) ?> – <?= e($b['libelle']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Activité principale de l'entreprise</label>
                        <textarea name="activite_principale" rows="2" class="form-control form-control-sm"
                                  placeholder="Décrivez l'activité principale"
                                  <?= !$canEdit ? 'readonly' : '' ?>><?= e($entreprise['activite_principale'] ?? '') ?></textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Activités secondaires</label>
                        <textarea name="activites_secondaires" rows="2" class="form-control form-control-sm"
                                  placeholder="Activités secondaires éventuelles"
                                  <?= !$canEdit ? 'readonly' : '' ?>><?= e($entreprise['activites_secondaires'] ?? '') ?></textarea>
                    </div>

                    <!-- Localisation -->
                    <div class="col-12"><hr class="my-1"><small class="text-muted fw-semibold">Localisation</small></div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Région</label>
                        <input type="text" class="form-control form-control-sm" value="<?= e($entreprise['region_nom'] ?? '') ?>" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Département</label>
                        <select name="departement_id" class="form-select form-select-sm" id="sel-dept" <?= !$canEdit ? 'disabled' : '' ?>>
                            <option value="">-- Département --</option>
                            <?php foreach ($data['departements'] ?? [] as $d): ?>
                                <option value="<?= $d['id'] ?>" <?= ($entreprise['departement_id'] ?? '') == $d['id'] ? 'selected' : '' ?>><?= e($d['nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Commune</label>
                        <select name="commune_id" class="form-select form-select-sm" id="sel-commune" <?= !$canEdit ? 'disabled' : '' ?>>
                            <option value="">-- Commune --</option>
                            <?php foreach ($data['communes'] ?? [] as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= ($entreprise['commune_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= e($c['nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Localité / Ville</label>
                        <input type="text" name="localite" class="form-control form-control-sm"
                               value="<?= e($entreprise['localite'] ?? '') ?>" <?= !$canEdit ? 'readonly' : '' ?>>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Boîte postale</label>
                        <input type="text" name="boite_postale" class="form-control form-control-sm"
                               placeholder="BP …" value="<?= e($entreprise['boite_postale'] ?? '') ?>" <?= !$canEdit ? 'readonly' : '' ?>>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Téléphone</label>
                        <input type="text" name="telephone" class="form-control form-control-sm"
                               value="<?= e($entreprise['telephone'] ?? '') ?>" <?= !$canEdit ? 'readonly' : '' ?>>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Fax</label>
                        <input type="text" name="fax" class="form-control form-control-sm"
                               value="<?= e($entreprise['fax'] ?? '') ?>" <?= !$canEdit ? 'readonly' : '' ?>>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Quartier</label>
                        <input type="text" name="quartier" class="form-control form-control-sm"
                               value="<?= e($entreprise['quartier'] ?? '') ?>" <?= !$canEdit ? 'readonly' : '' ?>>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">E-mail</label>
                        <input type="email" name="email" class="form-control form-control-sm"
                               value="<?= e($entreprise['email'] ?? '') ?>" <?= !$canEdit ? 'readonly' : '' ?>>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">N° CNSS</label>
                        <input type="text" name="numero_cnss" class="form-control form-control-sm"
                               value="<?= e($entreprise['numero_cnss'] ?? '') ?>" <?= !$canEdit ? 'readonly' : '' ?>>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Masse salariale (FCFA) <small class="text-muted">— montant global annuel au 31/12</small></label>
                        <input type="number" name="masse_salariale" class="form-control form-control-sm"
                               value="<?= e($declaration['masse_salariale'] ?? '') ?>"
                               min="0" placeholder="0" <?= !$canEdit ? 'readonly' : '' ?>>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nom de l'enquêteur</label>
                        <input type="text" name="nom_enqueteur" class="form-control form-control-sm"
                               value="<?= e($declaration['nom_enqueteur'] ?? '') ?>" <?= !$canEdit ? 'readonly' : '' ?>>
                    </div>

                </div><!-- /row -->
            </div>
        </div>
        <?= _navBtns(1, $totalEtapes, $canEdit) ?>
    </div>

    <!-- ╔══════════════════════════════════════════════════════╗
         ║  ÉTAPE 2 — Section II : Effectifs mensuels          ║
         ╚══════════════════════════════════════════════════════╝ -->
    <div class="form-section <?= $etape === 2 ? 'active' : '' ?>" id="etape-2">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-bottom py-3">
                <div class="section-badge"><span>II</span> Renseignements statistiques généraux</div>
                <small class="text-muted">Effectif global en service au dernier jour du mois</small>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-ramo text-center align-middle mb-2">
                        <thead>
                            <tr>
                                <?php for ($m = 1; $m <= 12; $m++): ?>
                                    <th style="min-width:68px"><?= $moisLabels[$m] ?></th>
                                <?php endfor; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <?php for ($m = 1; $m <= 12; $m++): ?>
                                    <td>
                                        <input type="number" name="effectifs[<?= $m ?>]"
                                               class="form-control form-control-sm text-center eff-mensuel"
                                               value="<?= (int)($mensuels[$m] ?? 0) ?>"
                                               min="0" <?= !$canEdit ? 'readonly' : '' ?>>
                                    </td>
                                <?php endfor; ?>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="alert alert-info py-2 small mb-0">
                    <i class="bi bi-info-circle me-1"></i>
                    <strong>NB :</strong> Veuillez répartir les effectifs du mois de Décembre dans les deux tableaux qui suivent (Sections III.1 et III.2).
                </div>

                <!-- Statistiques auto -->
                <div class="row g-3 mt-1">
                    <div class="col-4"><div class="p-2 bg-primary bg-opacity-10 rounded text-center">
                        <div class="fw-bold text-primary fs-5" id="eff-max">0</div><small class="text-muted">Effectif max</small>
                    </div></div>
                    <div class="col-4"><div class="p-2 bg-success bg-opacity-10 rounded text-center">
                        <div class="fw-bold text-success fs-5" id="eff-moy">0</div><small class="text-muted">Effectif moyen</small>
                    </div></div>
                    <div class="col-4"><div class="p-2 bg-warning bg-opacity-10 rounded text-center">
                        <div class="fw-bold text-warning fs-5" id="eff-dec">0</div><small class="text-muted">Effectif déc.</small>
                    </div></div>
                </div>
            </div>
        </div>
        <?= _navBtns(2, $totalEtapes, $canEdit) ?>
    </div>

    <!-- ╔══════════════════════════════════════════════════════╗
         ║  ÉTAPE 3 — Section III.1 : Catégories/Origines/Sexe ║
         ╚══════════════════════════════════════════════════════╝ -->
    <div class="form-section <?= $etape === 3 ? 'active' : '' ?>" id="etape-3">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-bottom py-3">
                <div class="section-badge"><span>III.1</span> Répartition par catégories, sexes et origines</div>
                <small class="text-muted">Effectifs du mois de Décembre — répartis par catégorie professionnelle, nationalité et sexe</small>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-ramo table-sm mb-0">
                        <thead>
                            <tr>
                                <th rowspan="2" class="align-middle text-start ps-2" style="min-width:160px">Catégorie professionnelle</th>
                                <th colspan="2" class="text-center bg-primary bg-opacity-10">NIGÉRIENS</th>
                                <th colspan="2" class="text-center bg-success bg-opacity-10">AFRICAINS</th>
                                <th colspan="2" class="text-center bg-warning bg-opacity-10">AUTRES NAT.</th>
                                <th colspan="2" class="text-center bg-info bg-opacity-10">SOUS-TOTAL</th>
                                <th class="text-center bg-secondary bg-opacity-10">TOTAL<br>H&F</th>
                            </tr>
                            <tr>
                                <th class="text-center bg-primary bg-opacity-10">H</th>
                                <th class="text-center bg-primary bg-opacity-10">F</th>
                                <th class="text-center bg-success bg-opacity-10">H</th>
                                <th class="text-center bg-success bg-opacity-10">F</th>
                                <th class="text-center bg-warning bg-opacity-10">H</th>
                                <th class="text-center bg-warning bg-opacity-10">F</th>
                                <th class="text-center bg-info bg-opacity-10">H</th>
                                <th class="text-center bg-info bg-opacity-10">F</th>
                                <th class="text-center bg-secondary bg-opacity-10">T</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $key => $label): ?>
                                <?php $row = $catMap[$key] ?? []; ?>
                                <tr class="cat-row" data-cat="<?= $key ?>">
                                    <td class="row-label ps-2"><?= $label ?></td>
                                    <?php foreach (['nigeriens_h','nigeriens_f','africains_h','africains_f','autres_nat_h','autres_nat_f'] as $field): ?>
                                        <td>
                                            <input type="number"
                                                   name="categories[<?= $key ?>][<?= $field ?>]"
                                                   class="form-control form-control-sm text-center cat-input"
                                                   value="<?= (int)($row[$field] ?? 0) ?>"
                                                   min="0" data-cat="<?= $key ?>"
                                                   <?= !$canEdit ? 'readonly' : '' ?>>
                                        </td>
                                    <?php endforeach; ?>
                                    <td class="total-col" id="cat-sh-<?= $key ?>">0</td>
                                    <td class="total-col" id="cat-sf-<?= $key ?>">0</td>
                                    <td class="total-col fw-bold" id="cat-tot-<?= $key ?>">0</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="total-row">
                                <td class="ps-2 fw-bold">Sous-total</td>
                                <td class="text-center" id="gtot-nih">0</td>
                                <td class="text-center" id="gtot-nif">0</td>
                                <td class="text-center" id="gtot-afh">0</td>
                                <td class="text-center" id="gtot-aff">0</td>
                                <td class="text-center" id="gtot-auh">0</td>
                                <td class="text-center" id="gtot-auf">0</td>
                                <td class="text-center" id="gtot-sh">0</td>
                                <td class="text-center" id="gtot-sf">0</td>
                                <td class="text-center" id="gtot-tot">0</td>
                            </tr>
                            <tr class="total-row" style="background:#c8e6c9">
                                <td class="ps-2 fw-bold">Total général</td>
                                <td colspan="6"></td>
                                <td class="text-center fw-bold" id="gtot-gen-h">0</td>
                                <td class="text-center fw-bold" id="gtot-gen-f">0</td>
                                <td class="text-center fw-bold" id="gtot-gen">0</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        <?= _navBtns(3, $totalEtapes, $canEdit) ?>
    </div>

    <!-- ╔══════════════════════════════════════════════════════╗
         ║  ÉTAPE 4 — Section III.2 : Niveaux d'instruction    ║
         ╚══════════════════════════════════════════════════════╝ -->
    <div class="form-section <?= $etape === 4 ? 'active' : '' ?>" id="etape-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-bottom py-3">
                <div class="section-badge"><span>III.2</span> Répartition par niveaux d'instruction et catégories</div>
                <small class="text-muted">NB : considérez SVP le dernier niveau atteint par le salarié.</small>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-ramo table-sm mb-0">
                        <thead>
                            <tr>
                                <th rowspan="2" class="align-middle text-start ps-2" style="min-width:180px">Niveaux d'instructions</th>
                                <?php foreach ($categories as $catKey => $catLabel): ?>
                                    <th colspan="2" class="text-center" style="font-size:.7rem"><?= $catLabel ?></th>
                                <?php endforeach; ?>
                                <th class="text-center bg-secondary bg-opacity-10">TOTAL</th>
                            </tr>
                            <tr>
                                <?php foreach ($categories as $catKey => $catLabel): ?>
                                    <th class="text-center" style="width:48px">H</th>
                                    <th class="text-center" style="width:48px">F</th>
                                <?php endforeach; ?>
                                <th class="text-center" style="width:60px">T</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($niveaux as $nivKey => $nivLabel): ?>
                                <tr class="niv-row" data-niv="<?= $nivKey ?>">
                                    <td class="row-label ps-2 small"><?= $nivLabel ?></td>
                                    <?php foreach ($categories as $catKey => $catLabel): ?>
                                        <?php $rowN = $niveauMap[$catKey][$nivKey] ?? []; ?>
                                        <td>
                                            <input type="number"
                                                   name="niveaux[<?= $catKey ?>][<?= $nivKey ?>][h]"
                                                   class="form-control form-control-sm text-center niv-input"
                                                   value="<?= (int)($rowN['effectif_h'] ?? 0) ?>"
                                                   min="0" data-niv-cat="<?= $catKey ?>" data-niv-niv="<?= $nivKey ?>"
                                                   <?= !$canEdit ? 'readonly' : '' ?>>
                                        </td>
                                        <td>
                                            <input type="number"
                                                   name="niveaux[<?= $catKey ?>][<?= $nivKey ?>][f]"
                                                   class="form-control form-control-sm text-center niv-input"
                                                   value="<?= (int)($rowN['effectif_f'] ?? 0) ?>"
                                                   min="0" data-niv-cat="<?= $catKey ?>" data-niv-niv="<?= $nivKey ?>"
                                                   <?= !$canEdit ? 'readonly' : '' ?>>
                                        </td>
                                    <?php endforeach; ?>
                                    <td class="total-col" id="niv-row-tot-<?= $nivKey ?>">0</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="total-row">
                                <td class="ps-2 fw-bold">Sous-total</td>
                                <?php foreach ($categories as $catKey => $catLabel): ?>
                                    <td class="text-center" id="niv-col-h-<?= $catKey ?>">0</td>
                                    <td class="text-center" id="niv-col-f-<?= $catKey ?>">0</td>
                                <?php endforeach; ?>
                                <td class="text-center fw-bold" id="niv-grand-tot">0</td>
                            </tr>
                            <tr class="total-row" style="background:#c8e6c9">
                                <td class="ps-2 fw-bold">TOTAL</td>
                                <?php foreach ($categories as $catKey => $catLabel): ?>
                                    <td colspan="2" class="text-center" id="niv-cat-tot-<?= $catKey ?>">0</td>
                                <?php endforeach; ?>
                                <td class="text-center fw-bold" id="niv-general">0</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        <?= _navBtns(4, $totalEtapes, $canEdit) ?>
    </div>

    <!-- ╔══════════════════════════════════════════════════════╗
         ║  ÉTAPE 5 — Section III.3 : Formation professionnelle║
         ╚══════════════════════════════════════════════════════╝ -->
    <div class="form-section <?= $etape === 5 ? 'active' : '' ?>" id="etape-5">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-bottom py-3">
                <div class="section-badge"><span>III.3</span> Formation professionnelle continue</div>
                <small class="text-muted">Le personnel a-t-il bénéficié d'une formation de mise à niveau, d'un recyclage ou d'un perfectionnement au cours de l'année écoulée ?</small>
            </div>
            <div class="card-body">
                <?php $aFormation = !empty($formations) && ($formations[0]['a_eu_formation'] ?? false); ?>
                <div class="mb-3 d-flex align-items-center gap-4">
                    <label class="fw-semibold me-2">Formation(s) réalisée(s) ?</label>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="a_eu_formation" id="form-oui" value="1"
                               <?= $aFormation ? 'checked' : '' ?> <?= !$canEdit ? 'disabled' : '' ?>>
                        <label class="form-check-label fw-semibold text-success" for="form-oui">
                            <i class="bi bi-check-circle me-1"></i>Oui
                        </label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="a_eu_formation" id="form-non" value="0"
                               <?= !$aFormation ? 'checked' : '' ?> <?= !$canEdit ? 'disabled' : '' ?>>
                        <label class="form-check-label fw-semibold text-secondary" for="form-non">
                            <i class="bi bi-x-circle me-1"></i>Non
                        </label>
                    </div>
                </div>

                <!-- Tableau formations multi-lignes -->
                <div id="formation-details" <?= !$aFormation ? 'style="display:none"' : '' ?>>
                    <p class="text-muted small mb-2">
                        <i class="bi bi-info-circle me-1"></i>
                        Si oui, précisez le niveau de qualification, la nature de la formation, la durée, le sexe et le nombre.
                        Vous pouvez saisir plusieurs lignes.
                    </p>
                    <div class="table-responsive">
                        <table class="table table-bordered table-ramo table-sm" id="table-formations">
                            <thead>
                                <tr>
                                    <th style="min-width:140px">Niveaux de qualifications</th>
                                    <th style="min-width:180px">Nature de la formation</th>
                                    <th style="min-width:100px">Durée</th>
                                    <th class="text-center" style="width:80px">Hommes</th>
                                    <th class="text-center" style="width:80px">Femmes</th>
                                    <?php if ($canEdit): ?><th class="text-center" style="width:40px">×</th><?php endif; ?>
                                </tr>
                            </thead>
                            <tbody id="tbody-formations">
                                <?php
                                $formRows = !empty($formations) ? $formations : [[]];
                                foreach ($formRows as $fi => $frow):
                                ?>
                                <tr class="formation-row">
                                    <td>
                                        <input type="text" name="formations[<?= $fi ?>][qualification]"
                                               class="form-control form-control-sm"
                                               value="<?= e($frow['qualification'] ?? '') ?>"
                                               placeholder="Qualification visée"
                                               <?= !$canEdit ? 'readonly' : '' ?>>
                                    </td>
                                    <td>
                                        <input type="text" name="formations[<?= $fi ?>][nature_formation]"
                                               class="form-control form-control-sm"
                                               value="<?= e($frow['nature_formation'] ?? '') ?>"
                                               placeholder="Nature de la formation"
                                               <?= !$canEdit ? 'readonly' : '' ?>>
                                    </td>
                                    <td>
                                        <input type="text" name="formations[<?= $fi ?>][duree_formation]"
                                               class="form-control form-control-sm"
                                               value="<?= e($frow['duree_formation'] ?? '') ?>"
                                               placeholder="Ex: 3 mois"
                                               <?= !$canEdit ? 'readonly' : '' ?>>
                                    </td>
                                    <td>
                                        <input type="number" name="formations[<?= $fi ?>][effectif_h]"
                                               class="form-control form-control-sm text-center form-tot-input"
                                               value="<?= (int)($frow['effectif_h'] ?? 0) ?>"
                                               min="0" <?= !$canEdit ? 'readonly' : '' ?>>
                                    </td>
                                    <td>
                                        <input type="number" name="formations[<?= $fi ?>][effectif_f]"
                                               class="form-control form-control-sm text-center form-tot-input"
                                               value="<?= (int)($frow['effectif_f'] ?? 0) ?>"
                                               min="0" <?= !$canEdit ? 'readonly' : '' ?>>
                                    </td>
                                    <?php if ($canEdit): ?>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-rm-form" onclick="rmFormRow(this)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                    <?php endif; ?>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr class="table-secondary fw-bold">
                                    <td colspan="3" class="text-end pe-2">Total</td>
                                    <td class="text-center" id="form-tot-h">0</td>
                                    <td class="text-center" id="form-tot-f">0</td>
                                    <?php if ($canEdit): ?><td></td><?php endif; ?>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <?php if ($canEdit): ?>
                    <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="btn-add-formation">
                        <i class="bi bi-plus-lg me-1"></i>Ajouter une ligne de formation
                    </button>
                    <?php endif; ?>
                    <div class="mt-3">
                        <label class="form-label fw-semibold small text-muted">Observations</label>
                        <textarea name="formation_observations" rows="2" class="form-control form-control-sm"
                                  placeholder="Vous pouvez joindre des intercalaires en ce qui concerne la formation professionnelle continue du personnel."
                                  <?= !$canEdit ? 'readonly' : '' ?>><?= e($formations[0]['observations'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
        </div>
        <?= _navBtns(5, $totalEtapes, $canEdit) ?>
    </div>

    <!-- ╔══════════════════════════════════════════════════════╗
         ║  ÉTAPE 6 — Section IV : Pertes d'emploi             ║
         ╚══════════════════════════════════════════════════════╝ -->
    <div class="form-section <?= $etape === 6 ? 'active' : '' ?>" id="etape-6">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-bottom py-3">
                <div class="section-badge"><span>IV</span> Pertes d'emploi</div>
                <small class="text-muted">Avez-vous enregistré des pertes d'emploi au cours de l'année écoulée ?</small>
            </div>
            <div class="card-body">
                <?php $aPertes = !empty(array_filter(array_map(fn($r) => ($r['effectif_h'] ?? 0) + ($r['effectif_f'] ?? 0), $pertesMap))); ?>
                <div class="mb-3 d-flex align-items-center gap-4">
                    <label class="fw-semibold me-2">Pertes d'emploi enregistrées ?</label>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="a_perte_emploi" id="perte-oui" value="1"
                               <?= $aPertes ? 'checked' : '' ?> <?= !$canEdit ? 'disabled' : '' ?>>
                        <label class="form-check-label fw-semibold text-danger" for="perte-oui"><i class="bi bi-check-circle me-1"></i>Oui</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="a_perte_emploi" id="perte-non" value="0"
                               <?= !$aPertes ? 'checked' : '' ?> <?= !$canEdit ? 'disabled' : '' ?>>
                        <label class="form-check-label fw-semibold text-secondary" for="perte-non"><i class="bi bi-x-circle me-1"></i>Non</label>
                    </div>
                </div>

                <div id="pertes-details">
                    <p class="text-muted small mb-2"><i class="bi bi-info-circle me-1"></i>Si oui, pour quels motifs et leur nombre par sexe ?</p>
                    <div class="table-responsive">
                        <table class="table table-bordered table-ramo align-middle">
                            <thead>
                                <tr>
                                    <th class="text-start ps-2" style="min-width:200px">Motifs</th>
                                    <th class="text-center" style="width:110px">Homme</th>
                                    <th class="text-center" style="width:110px">Femme</th>
                                    <th class="text-center bg-secondary bg-opacity-10" style="width:110px">Total</th>
                                    <th style="min-width:160px">Précision</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($motifs as $motifKey => $motifLabel): ?>
                                    <?php $rowP = $pertesMap[$motifKey] ?? []; ?>
                                    <tr>
                                        <td class="fw-semibold small ps-2"><?= $motifLabel ?></td>
                                        <td>
                                            <input type="number" name="pertes[<?= $motifKey ?>][h]"
                                                   class="form-control form-control-sm text-center perte-input"
                                                   value="<?= (int)($rowP['effectif_h'] ?? 0) ?>"
                                                   min="0" data-perte="<?= $motifKey ?>"
                                                   <?= !$canEdit ? 'readonly' : '' ?>>
                                        </td>
                                        <td>
                                            <input type="number" name="pertes[<?= $motifKey ?>][f]"
                                                   class="form-control form-control-sm text-center perte-input"
                                                   value="<?= (int)($rowP['effectif_f'] ?? 0) ?>"
                                                   min="0" data-perte="<?= $motifKey ?>"
                                                   <?= !$canEdit ? 'readonly' : '' ?>>
                                        </td>
                                        <td class="total-col" id="perte-tot-<?= $motifKey ?>">0</td>
                                        <td>
                                            <?php if ($motifKey === 'autres'): ?>
                                                <input type="text" name="pertes[autres][motif_autre]"
                                                       class="form-control form-control-sm"
                                                       placeholder="À préciser…"
                                                       value="<?= e($rowP['motif_autre'] ?? '') ?>"
                                                       <?= !$canEdit ? 'readonly' : '' ?>>
                                            <?php else: ?>
                                                <span class="text-muted small">—</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr class="total-row">
                                    <td class="fw-bold ps-2">Total</td>
                                    <td class="text-center" id="ptot-h">0</td>
                                    <td class="text-center" id="ptot-f">0</td>
                                    <td class="text-center fw-bold bg-secondary bg-opacity-10" id="ptot-tot">0</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?= _navBtns(6, $totalEtapes, $canEdit) ?>
    </div>

    <!-- ╔══════════════════════════════════════════════════════╗
         ║  ÉTAPE 7 — Section V : Perspectives d'emploi        ║
         ╚══════════════════════════════════════════════════════╝ -->
    <div class="form-section <?= $etape === 7 ? 'active' : '' ?>" id="etape-7">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-bottom py-3">
                <div class="section-badge"><span>V</span> Perspectives d'emploi</div>
                <small class="text-muted">Quelles sont vos perspectives d'emploi en cours ?</small>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-12">
                        <label class="form-label fw-semibold mb-3">Tendance prévue :</label>
                        <div class="d-flex flex-wrap gap-4">
                            <?php foreach (['hausse' => ['Hausse','bi-graph-up-arrow','success'],
                                            'stabilite' => ['Stabilité','bi-dash-lg','primary'],
                                            'baisse' => ['Baisse','bi-graph-down-arrow','danger']] as $val => [$lbl,$ico,$col]): ?>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="perspective"
                                           id="persp-<?= $val ?>" value="<?= $val ?>"
                                           <?= ($persp['perspective'] ?? '') === $val ? 'checked' : '' ?>
                                           <?= !$canEdit ? 'disabled' : '' ?>>
                                    <label class="form-check-label fw-semibold text-<?= $col ?>" for="persp-<?= $val ?>">
                                        <i class="bi <?= $ico ?> me-1"></i><?= $lbl ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Donnez une justification :</label>
                        <textarea name="justification" rows="4" class="form-control"
                                  placeholder="Expliquez les raisons de cette perspective d'emploi…"
                                  <?= !$canEdit ? 'readonly' : '' ?>><?= e($persp['justification'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
        </div>
        <?= _navBtns(7, $totalEtapes, $canEdit) ?>
    </div>

    <!-- ╔══════════════════════════════════════════════════════╗
         ║  ÉTAPE 8 — Section VI : Effectifs étrangers         ║
         ╚══════════════════════════════════════════════════════╝ -->
    <div class="form-section <?= $etape === 8 ? 'active' : '' ?>" id="etape-8">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <div>
                    <div class="section-badge"><span>VI</span> Effectifs du personnel par nationalité</div>
                    <small class="text-muted">Répartition de la main d'œuvre nigérienne et étrangère</small>
                </div>
                <?php if ($canEdit): ?>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="btn-add-etranger">
                        <i class="bi bi-plus-lg me-1"></i>Ajouter
                    </button>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <!-- Totaux MON / MOE -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">A — Total main d'œuvre nigérienne</label>
                        <input type="number" name="total_nigeriens" class="form-control form-control-sm"
                               value="<?= (int)($declaration['total_nigeriens'] ?? 0) ?>"
                               id="total-nig-input"
                               min="0" placeholder="0" <?= !$canEdit ? 'readonly' : '' ?>>
                        <small class="text-muted">Calculé automatiquement depuis III.1 ou saisie manuelle</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">B — Total main d'œuvre étrangère</label>
                        <input type="number" name="total_etrangers" class="form-control form-control-sm"
                               value="<?= (int)($declaration['total_etrangers'] ?? 0) ?>"
                               id="total-etr-input"
                               min="0" placeholder="0" <?= !$canEdit ? 'readonly' : '' ?>>
                        <small class="text-muted">Calculé depuis le tableau ci-dessous</small>
                    </div>
                </div>

                <p class="fw-semibold small text-muted mb-2">Répartition de la main d'œuvre étrangère :</p>
                <div class="table-responsive">
                    <table class="table table-bordered table-ramo table-sm">
                        <thead>
                            <tr>
                                <th style="min-width:130px">Pays</th>
                                <th style="min-width:130px">Qualification</th>
                                <th style="min-width:150px">Fonction</th>
                                <th class="text-center" style="width:70px">Sexe</th>
                                <th class="text-center" style="width:80px">Nombre</th>
                                <?php if ($canEdit): ?><th class="text-center" style="width:40px">×</th><?php endif; ?>
                            </tr>
                        </thead>
                        <tbody id="etrangers-tbody">
                            <?php if (!empty($etrangers)): ?>
                                <?php foreach ($etrangers as $i => $et): ?>
                                    <tr class="etr-row">
                                        <td><input type="text" name="etrangers[<?= $i ?>][pays]" class="form-control form-control-sm" value="<?= e($et['pays']) ?>" placeholder="Pays" <?= !$canEdit ? 'readonly' : '' ?>></td>
                                        <td><input type="text" name="etrangers[<?= $i ?>][qualification]" class="form-control form-control-sm" value="<?= e($et['qualification'] ?? '') ?>" placeholder="Qualification" <?= !$canEdit ? 'readonly' : '' ?>></td>
                                        <td><input type="text" name="etrangers[<?= $i ?>][fonction]" class="form-control form-control-sm" value="<?= e($et['fonction'] ?? '') ?>" placeholder="Fonction" <?= !$canEdit ? 'readonly' : '' ?>></td>
                                        <td>
                                            <select name="etrangers[<?= $i ?>][sexe]" class="form-select form-select-sm etr-nombre" <?= !$canEdit ? 'disabled' : '' ?>>
                                                <option value="H" <?= ($et['sexe'] ?? 'H') === 'H' ? 'selected' : '' ?>>H</option>
                                                <option value="F" <?= ($et['sexe'] ?? '') === 'F' ? 'selected' : '' ?>>F</option>
                                            </select>
                                        </td>
                                        <td><input type="number" name="etrangers[<?= $i ?>][nombre]" class="form-control form-control-sm text-center etr-nombre" value="<?= (int)($et['nombre'] ?? 0) ?>" min="0" <?= !$canEdit ? 'readonly' : '' ?>></td>
                                        <?php if ($canEdit): ?><td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger" onclick="rmEtr(this)"><i class="bi bi-trash"></i></button></td><?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr id="etr-empty"><td colspan="6" class="text-center text-muted py-3 small"><i class="bi bi-info-circle me-1"></i>Aucun effectif étranger enregistré. <?= $canEdit ? 'Cliquez sur "Ajouter".' : '' ?></td></tr>
                            <?php endif; ?>
                        </tbody>
                        <tfoot>
                            <tr class="total-row">
                                <td colspan="4" class="text-end pe-2 fw-bold">Total étrangers :</td>
                                <td class="text-center fw-bold" id="etr-tot">0</td>
                                <?php if ($canEdit): ?><td></td><?php endif; ?>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Boutons finaux -->
        <div class="d-flex justify-content-between align-items-center mt-3 mb-4">
            <button type="button" class="btn btn-outline-secondary btn-prev">
                <i class="bi bi-arrow-left me-1"></i>Précédent
            </button>
            <div class="d-flex gap-2">
                <?php if ($canEdit): ?>
                    <button type="submit" class="btn btn-success btn-save">
                        <i class="bi bi-floppy me-1"></i>Sauvegarder
                    </button>
                    <button type="button" class="btn btn-primary"
                            data-bs-toggle="modal" data-bs-target="#modal-soumettre">
                        <i class="bi bi-send me-1"></i>Soumettre à l'ANPE
                    </button>
                <?php else: ?>
                    <a href="<?= url("agent/declaration/$decId/apercu") ?>" class="btn btn-outline-info">
                        <i class="bi bi-eye me-1"></i>Voir l'aperçu complet
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

</form>

<!-- ════ Modal confirmation soumission ════ -->
<div class="modal fade" id="modal-soumettre" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-send me-2"></i>Confirmer la soumission</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning py-2">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    <strong>Attention !</strong> Une fois soumise, la déclaration ne pourra plus être modifiée sans avis de l'administration.
                </div>
                <p>Confirmer la soumission définitive de la déclaration
                   <strong><?= e($declaration['code_questionnaire'] ?? '') ?></strong> —
                   <strong><?= e($entreprise['raison_sociale'] ?? '') ?></strong> ?
                </p>
                <p class="text-muted small">Complétude : <strong><?= $pct ?>%</strong> — <?= $totalEtapes ?> sections renseignées</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" id="btn-confirm-soumettre">
                    <i class="bi bi-send me-1"></i>Oui, soumettre
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ════ Calculs JavaScript ════ -->
<script>
const BASE = (window.APP_BASE ?? '').replace(/\/+$/, '');
const DECL_ID_RAMO = '<?= $decId ?>';

// ─── Filtre dynamique Département → Commune (étape 1) ───────────────────────
(function () {
    const selDept    = document.getElementById('sel-dept');
    const selCommune = document.getElementById('sel-commune');
    if (!selDept || !selCommune) return;

    const savedCommuneId = <?= (int)($entreprise['commune_id'] ?? 0) ?>;

    async function loadCommunes(deptId, preselectId = 0) {
        if (!deptId) {
            selCommune.innerHTML = '<option value="">-- Commune --</option>';
            return;
        }
        selCommune.innerHTML = '<option value="">Chargement…</option>';
        selCommune.disabled  = true;
        try {
            const resp = await fetch(`${BASE}/api/communes/${deptId}`);
            if (!resp.ok) throw new Error('HTTP ' + resp.status);
            const data = await resp.json();
            const list = data.communes ?? data ?? [];
            selCommune.innerHTML = '<option value="">-- Commune --</option>';
            list.forEach(c => {
                const opt = document.createElement('option');
                opt.value       = c.id;
                opt.textContent = c.nom;
                if (preselectId && parseInt(c.id) === preselectId) opt.selected = true;
                selCommune.appendChild(opt);
            });
        } catch (err) {
            console.error('Communes API error:', err);
            selCommune.innerHTML = '<option value="">-- Erreur --</option>';
        } finally {
            selCommune.disabled = false;
        }
    }

    selDept.addEventListener('change', function () {
        loadCommunes(this.value, 0);
    });
})();


function calcMensuels() {
    const inputs = document.querySelectorAll('.eff-mensuel');
    let sum = 0, max = 0, dec = 0, cnt = 0;
    inputs.forEach((i, idx) => {
        const v = parseInt(i.value) || 0;
        sum += v; cnt++;
        if (v > max) max = v;
        if (idx === 11) dec = v; // décembre
    });
    const set = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
    set('eff-max', max);
    set('eff-moy', Math.round(sum / 12));
    set('eff-dec', dec);
}
document.querySelectorAll('.eff-mensuel').forEach(i => i.addEventListener('input', calcMensuels));
calcMensuels();

// ─── Catégories III.1 ──────────────────────────────────────
function calcCategories() {
    const cats = <?= json_encode(array_keys($categories)) ?>;
    let totNiH=0,totNiF=0,totAfH=0,totAfF=0,totAuH=0,totAuF=0;
    cats.forEach(cat => {
        const g = f => parseInt(document.querySelector(`input[name="categories[${cat}][${f}]"]`)?.value)||0;
        const niH=g('nigeriens_h'),niF=g('nigeriens_f'),
              afH=g('africains_h'),afF=g('africains_f'),
              auH=g('autres_nat_h'),auF=g('autres_nat_f');
        totNiH+=niH;totNiF+=niF;totAfH+=afH;totAfF+=afF;totAuH+=auH;totAuF+=auF;
        const sH=niH+afH+auH, sF=niF+afF+auF;
        const set=(id,v)=>{const e=document.getElementById(id);if(e)e.textContent=v};
        set(`cat-sh-${cat}`,sH);
        set(`cat-sf-${cat}`,sF);
        set(`cat-tot-${cat}`,sH+sF);
    });
    const set=(id,v)=>{const e=document.getElementById(id);if(e)e.textContent=v};
    set('gtot-nih',totNiH);set('gtot-nif',totNiF);
    set('gtot-afh',totAfH);set('gtot-aff',totAfF);
    set('gtot-auh',totAuH);set('gtot-auf',totAuF);
    const sH=totNiH+totAfH+totAuH, sF=totNiF+totAfF+totAuF;
    set('gtot-sh',sH);set('gtot-sf',sF);set('gtot-tot',sH+sF);
    set('gtot-gen-h',sH);set('gtot-gen-f',sF);set('gtot-gen',sH+sF);
    // Proposer total nigériens pour section VI
    const tnEl = document.getElementById('total-nig-input');
    if (tnEl && !tnEl.dataset.manual) tnEl.value = totNiH+totNiF;
}
document.querySelectorAll('.cat-input').forEach(i => i.addEventListener('input', calcCategories));
document.getElementById('total-nig-input')?.addEventListener('change', e => { e.target.dataset.manual = '1'; });
calcCategories();

// ─── Niveaux d'instruction III.2 ──────────────────────────
function calcNiveaux() {
    const cats = <?= json_encode(array_keys($categories)) ?>;
    const nivs = <?= json_encode(array_keys($niveaux)) ?>;
    let grandTot = 0;
    nivs.forEach(niv => {
        let rowTot = 0;
        cats.forEach(cat => {
            const h=parseInt(document.querySelector(`input[name="niveaux[${cat}][${niv}][h]"]`)?.value)||0;
            const f=parseInt(document.querySelector(`input[name="niveaux[${cat}][${niv}][f]"]`)?.value)||0;
            rowTot+=h+f;
        });
        const rt=document.getElementById(`niv-row-tot-${niv}`);
        if(rt)rt.textContent=rowTot;
        grandTot+=rowTot;
    });
    cats.forEach(cat => {
        let colH=0,colF=0;
        nivs.forEach(niv => {
            const h=parseInt(document.querySelector(`input[name="niveaux[${cat}][${niv}][h]"]`)?.value)||0;
            const f=parseInt(document.querySelector(`input[name="niveaux[${cat}][${niv}][f]"]`)?.value)||0;
            colH+=h;colF+=f;
        });
        const ch=document.getElementById(`niv-col-h-${cat}`);
        const cf=document.getElementById(`niv-col-f-${cat}`);
        const ct=document.getElementById(`niv-cat-tot-${cat}`);
        if(ch)ch.textContent=colH;
        if(cf)cf.textContent=colF;
        if(ct)ct.textContent=colH+colF;
    });
    const gt=document.getElementById('niv-grand-tot');
    const gn=document.getElementById('niv-general');
    if(gt)gt.textContent=grandTot;
    if(gn)gn.textContent=grandTot;
}
document.querySelectorAll('.niv-input').forEach(i => i.addEventListener('input', calcNiveaux));
calcNiveaux();

// ─── Formation III.3 : total & show/hide ──────────────────
function calcFormation() {
    let tH=0,tF=0;
    document.querySelectorAll('#tbody-formations .form-tot-input').forEach((el,idx) => {
        const v=parseInt(el.value)||0;
        if(el.name.includes('[effectif_h]')) tH+=v;
        else tF+=v;
    });
    // Recalcul par paire
    tH=0;tF=0;
    document.querySelectorAll('#tbody-formations tr').forEach(tr => {
        const nh=tr.querySelector('input[name*="[effectif_h]"]');
        const nf=tr.querySelector('input[name*="[effectif_f]"]');
        if(nh) tH+=parseInt(nh.value)||0;
        if(nf) tF+=parseInt(nf.value)||0;
    });
    const fH=document.getElementById('form-tot-h');
    const fF=document.getElementById('form-tot-f');
    if(fH)fH.textContent=tH;
    if(fF)fF.textContent=tF;
}
document.querySelectorAll('input[name=a_eu_formation]').forEach(r => {
    r.addEventListener('change', () => {
        const bloc = document.getElementById('formation-details');
        if(bloc) bloc.style.display = r.value==='1' ? '' : 'none';
    });
});
document.getElementById('tbody-formations')?.addEventListener('input', calcFormation);
calcFormation();

// Ajouter ligne formation
let formIdx = <?= count($formRows ?? [[]]) ?>;
document.getElementById('btn-add-formation')?.addEventListener('click', () => {
    const tbody = document.getElementById('tbody-formations');
    const tr = document.createElement('tr');
    tr.className = 'formation-row';
    tr.innerHTML = `
        <td><input type="text" name="formations[${formIdx}][qualification]" class="form-control form-control-sm" placeholder="Qualification visée"></td>
        <td><input type="text" name="formations[${formIdx}][nature_formation]" class="form-control form-control-sm" placeholder="Nature de la formation"></td>
        <td><input type="text" name="formations[${formIdx}][duree_formation]" class="form-control form-control-sm" placeholder="Ex: 3 mois"></td>
        <td><input type="number" name="formations[${formIdx}][effectif_h]" class="form-control form-control-sm text-center form-tot-input" value="0" min="0"></td>
        <td><input type="number" name="formations[${formIdx}][effectif_f]" class="form-control form-control-sm text-center form-tot-input" value="0" min="0"></td>
        <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger" onclick="rmFormRow(this)"><i class="bi bi-trash"></i></button></td>
    `;
    tbody.appendChild(tr);
    tr.addEventListener('input', calcFormation);
    formIdx++;
});
function rmFormRow(btn) {
    const tr = btn.closest('tr');
    if (document.querySelectorAll('#tbody-formations tr').length > 1) {
        tr.remove();
    } else {
        tr.querySelectorAll('input').forEach(i => { i.value = i.type==='number'?'0':'' });
    }
    calcFormation();
}

// ─── Pertes d'emploi IV ────────────────────────────────────
function calcPertes() {
    const motifs = <?= json_encode(array_keys($motifs)) ?>;
    let tH=0,tF=0;
    motifs.forEach(m => {
        const h=parseInt(document.querySelector(`input[name="pertes[${m}][h]"]`)?.value)||0;
        const f=parseInt(document.querySelector(`input[name="pertes[${m}][f]"]`)?.value)||0;
        tH+=h;tF+=f;
        const tot=document.getElementById(`perte-tot-${m}`);
        if(tot)tot.textContent=h+f;
    });
    const ph=document.getElementById('ptot-h');
    const pf=document.getElementById('ptot-f');
    const pt=document.getElementById('ptot-tot');
    if(ph)ph.textContent=tH;
    if(pf)pf.textContent=tF;
    if(pt)pt.textContent=tH+tF;
}
document.querySelectorAll('.perte-input').forEach(i => i.addEventListener('input', calcPertes));
calcPertes();

// ─── Exposer goToStep globalement pour onclick dans le DOM ──
window.goToStep = function(n) {
    if (typeof window.showEtapeRamo === 'function') {
        window.showEtapeRamo(n);
    } else {
        // Fallback si saisie.js n'est pas encore chargé
        document.querySelectorAll('.form-section').forEach(s => s.classList.remove('active'));
        const t = document.getElementById('etape-' + n);
        if (t) t.classList.add('active');
        const inp = document.getElementById('input-etape');
        if (inp) inp.value = n;
    }
};

// ─── Effectifs étrangers VI ───────────────────────────────
function calcEtrangers() {
    let tot=0;
    document.querySelectorAll('#etrangers-tbody .etr-nombre[name*="[nombre]"]').forEach(i => { tot+=parseInt(i.value)||0; });
    const el=document.getElementById('etr-tot');
    if(el)el.textContent=tot;
    const etrInput=document.getElementById('total-etr-input');
    if(etrInput && !etrInput.dataset.manual) etrInput.value=tot;
}
document.getElementById('total-etr-input')?.addEventListener('change', e => { e.target.dataset.manual='1'; });

let etrIdx = <?= count($etrangers) ?>;
document.getElementById('btn-add-etranger')?.addEventListener('click', () => {
    const tbody=document.getElementById('etrangers-tbody');
    const empty=document.getElementById('etr-empty');
    if(empty)empty.remove();
    const tr=document.createElement('tr');
    tr.className='etr-row';
    tr.innerHTML=`
        <td><input type="text" name="etrangers[${etrIdx}][pays]" class="form-control form-control-sm" placeholder="Pays"></td>
        <td><input type="text" name="etrangers[${etrIdx}][qualification]" class="form-control form-control-sm" placeholder="Qualification"></td>
        <td><input type="text" name="etrangers[${etrIdx}][fonction]" class="form-control form-control-sm" placeholder="Fonction"></td>
        <td><select name="etrangers[${etrIdx}][sexe]" class="form-select form-select-sm etr-nombre"><option value="H">H</option><option value="F">F</option></select></td>
        <td><input type="number" name="etrangers[${etrIdx}][nombre]" class="form-control form-control-sm text-center etr-nombre" value="0" min="0"></td>
        <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger" onclick="rmEtr(this)"><i class="bi bi-trash"></i></button></td>
    `;
    tbody.appendChild(tr);
    tr.querySelectorAll('.etr-nombre').forEach(i => i.addEventListener('input', calcEtrangers));
    etrIdx++;
    calcEtrangers();
});
function rmEtr(btn) { btn.closest('tr').remove(); calcEtrangers(); }
document.querySelectorAll('#etrangers-tbody .etr-nombre').forEach(i => i.addEventListener('input', calcEtrangers));
calcEtrangers();

// ─── Soumission ───────────────────────────────────────────
document.getElementById('btn-confirm-soumettre')?.addEventListener('click', async () => {
    const modal=bootstrap.Modal.getInstance(document.getElementById('modal-soumettre'));
    if(modal)modal.hide();
    const csrf=document.querySelector('meta[name="csrf-token"]')?.content??'';
    const fd=new FormData();
    fd.append('_csrf_token',csrf);
    try{
        const resp=await fetch(`${BASE}/agent/declaration/${DECL_ID_RAMO}/soumettre`,{
            method:'POST',body:fd,headers:{'X-Requested-With':'XMLHttpRequest'}
        });
        const json=await resp.json();
        if(json.success){
            window.location.href=json.redirect??`${BASE}/agent/declaration/${DECL_ID_RAMO}/apercu`;
        }else{
            alert(json.message??'Erreur lors de la soumission.');
        }
    }catch(e){alert('Erreur réseau. Veuillez réessayer.');}
});
</script>

<?php
/**
 * Génère les boutons Précédent / Suivant / Sauvegarder pour une étape
 */
function _navBtns(int $n, int $total, bool $canEdit): string {
    $html = '<div class="d-flex justify-content-between align-items-center mt-3 mb-4">';
    $html .= '<button type="button" class="btn btn-outline-secondary btn-prev"'.($n<=1?' disabled':'').'>';
    $html .= '<i class="bi bi-arrow-left me-1"></i>Précédent</button>';
    $html .= '<div class="d-flex gap-2">';
    if ($canEdit) {
        $html .= '<button type="submit" class="btn btn-outline-success btn-save"><i class="bi bi-floppy me-1"></i>Sauvegarder</button>';
    }
    if ($n < $total) {
        $html .= '<button type="button" class="btn btn-primary btn-next"><i class="bi bi-arrow-right me-1"></i>Suivant</button>';
    }
    $html .= '</div></div>';
    return $html;
}
?>
