<?php
/**
 * Vue de saisie multi-étapes d'une déclaration
 * Compatible avec public/assets/js/saisie.js et css/saisie.css
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

$etapeTitres = [
    1 => ['titre' => 'Identification',      'icon' => 'bi-building'],
    2 => ['titre' => 'Effectifs mensuels',  'icon' => 'bi-calendar3'],
    3 => ['titre' => 'Catégories',          'icon' => 'bi-people'],
    4 => ['titre' => 'Niveaux instruction', 'icon' => 'bi-mortarboard'],
    5 => ['titre' => 'Formation prof.',     'icon' => 'bi-award'],
    6 => ['titre' => 'Pertes d\'emploi',    'icon' => 'bi-person-dash'],
    7 => ['titre' => 'Eff. étrangers',      'icon' => 'bi-globe'],
];

$moisLabels = ['','Jan','Fév','Mar','Avr','Mai','Jun','Jul','Aoû','Sep','Oct','Nov','Déc'];
?>

<!-- Barre de progression et titre -->
<div class="row mb-3">
    <div class="col-lg-9">
        <div class="card border-0 shadow-sm">
            <div class="card-body py-3">
                <div class="d-flex align-items-center justify-content-between gap-3 mb-2">
                    <div>
                        <h6 class="mb-0 fw-bold">
                            <i class="bi bi-file-earmark-text text-primary me-2"></i>
                            <?= e($declaration['code_questionnaire'] ?? '') ?>
                        </h6>
                        <small class="text-muted"><?= e($entreprise['raison_sociale'] ?? '') ?></small>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-<?= $statut === 'brouillon' ? 'secondary' : ($statut === 'corrigee' ? 'warning' : 'success') ?> fs-6">
                            <?= ucfirst($statut) ?>
                        </span>
                    </div>
                </div>
                <div class="progress-bar-declaration">
                    <div class="fill" style="width:<?= $pct ?>%"></div>
                </div>
                <small class="text-muted" id="progress-label">Étape <?= $etape ?>/7 — <?= $pct ?>%</small>
            </div>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body py-3 d-flex align-items-center">
                <div id="autosave-status" class="autosave-indicator">
                    <i class="bi bi-cloud-check me-1"></i>Prêt
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Navigation wizard étapes -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <div class="wizard-steps d-flex align-items-center" style="overflow-x:auto; padding:8px 0; gap:0">
            <?php foreach ($etapeTitres as $num => $info): ?>
                <?php
                $cls = 'pending';
                if ($num < $etape) $cls = 'done';
                elseif ($num === $etape) $cls = 'active';
                ?>
                <div class="wizard-step <?= $cls ?>" data-step="<?= $num ?>" style="cursor:pointer; flex-shrink:0; text-align:center; min-width:80px">
                    <div class="wizard-step-num">
                        <?php if ($num < $etape): ?>
                            <i class="bi bi-check-lg"></i>
                        <?php else: ?>
                            <?= $num ?>
                        <?php endif; ?>
                    </div>
                    <div class="wizard-step-label"><?= $info['titre'] ?></div>
                </div>
                <?php if ($num < 7): ?>
                    <div class="wizard-sep <?= $num < $etape ? 'done' : '' ?>" style="flex:1; min-width:12px; height:2px; background:#e0e0e0; margin:0 2px; margin-top:-14px"></div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Formulaire principal wizard -->
<form id="wizard-form"
      method="POST"
      action="<?= url("agent/declaration/$decId/sauvegarder") ?>"
      data-decl-id="<?= $decId ?>"
      data-etape="<?= $etape ?>">

    <?= csrfField() ?>
    <input type="hidden" name="etape" id="input-etape" value="<?= $etape ?>">

    <!-- ══════════ ÉTAPE 1 : IDENTIFICATION ══════════ -->
    <div class="form-section <?= $etape === 1 ? 'active' : '' ?>" id="etape-1">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-bold"><i class="bi bi-building text-primary me-2"></i>Étape 1 – Identification de l'entreprise</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Raison sociale <span class="text-danger">*</span></label>
                        <input type="text" name="raison_sociale" class="form-control"
                               value="<?= e($entreprise['raison_sociale'] ?? '') ?>"
                               <?= !$canEdit ? 'readonly' : '' ?> required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Numéro CNSS</label>
                        <input type="text" name="numero_cnss" class="form-control"
                               value="<?= e($entreprise['numero_cnss'] ?? '') ?>"
                               <?= !$canEdit ? 'readonly' : '' ?>>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nationalité</label>
                        <select name="nationalite" class="form-select" <?= !$canEdit ? 'disabled' : '' ?>>
                            <?php foreach (['Nigérienne','Française','Américaine','Chinoise','Libanaise','Autre'] as $nat): ?>
                                <option <?= ($entreprise['nationalite'] ?? '') === $nat ? 'selected' : '' ?>><?= $nat ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Branche d'activité</label>
                        <select name="branche_id" class="form-select" <?= !$canEdit ? 'disabled' : '' ?>>
                            <option value="">-- Sélectionner --</option>
                            <?php foreach ($data['branches'] ?? [] as $b): ?>
                                <option value="<?= $b['id'] ?>" <?= ($entreprise['branche_id'] ?? '') == $b['id'] ? 'selected' : '' ?>>
                                    <?= e($b['code']) ?> – <?= e($b['libelle']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Activité principale</label>
                        <input type="text" name="activite_principale" class="form-control"
                               value="<?= e($entreprise['activite_principale'] ?? '') ?>"
                               <?= !$canEdit ? 'readonly' : '' ?>>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Masse salariale (FCFA)</label>
                        <input type="number" name="masse_salariale" class="form-control"
                               value="<?= e($declaration['masse_salariale'] ?? '') ?>"
                               min="0" <?= !$canEdit ? 'readonly' : '' ?>>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Département</label>
                        <select name="departement_id" class="form-select" id="sel-dept" <?= !$canEdit ? 'disabled' : '' ?>>
                            <option value="">-- Département --</option>
                            <?php foreach ($data['departements'] ?? [] as $d): ?>
                                <option value="<?= $d['id'] ?>" <?= ($entreprise['departement_id'] ?? '') == $d['id'] ? 'selected' : '' ?>>
                                    <?= e($d['nom']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Commune</label>
                        <select name="commune_id" class="form-select" id="sel-commune" <?= !$canEdit ? 'disabled' : '' ?>>
                            <option value="">-- Commune --</option>
                            <?php foreach ($data['communes'] ?? [] as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= ($entreprise['commune_id'] ?? '') == $c['id'] ? 'selected' : '' ?>>
                                    <?= e($c['nom']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Localité / Ville</label>
                        <input type="text" name="localite" class="form-control"
                               value="<?= e($entreprise['localite'] ?? '') ?>"
                               <?= !$canEdit ? 'readonly' : '' ?>>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Quartier</label>
                        <input type="text" name="quartier" class="form-control"
                               value="<?= e($entreprise['quartier'] ?? '') ?>"
                               <?= !$canEdit ? 'readonly' : '' ?>>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Boîte postale</label>
                        <input type="text" name="boite_postale" class="form-control"
                               value="<?= e($entreprise['boite_postale'] ?? '') ?>"
                               <?= !$canEdit ? 'readonly' : '' ?>>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Téléphone</label>
                        <input type="text" name="telephone" class="form-control"
                               value="<?= e($entreprise['telephone'] ?? '') ?>"
                               <?= !$canEdit ? 'readonly' : '' ?>>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Email</label>
                        <input type="email" name="email" class="form-control"
                               value="<?= e($entreprise['email'] ?? '') ?>"
                               <?= !$canEdit ? 'readonly' : '' ?>>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Nom de l'enquêteur</label>
                        <input type="text" name="nom_enqueteur" class="form-control"
                               value="<?= e($declaration['nom_enqueteur'] ?? '') ?>"
                               <?= !$canEdit ? 'readonly' : '' ?>>
                    </div>
                </div>
            </div>
        </div>
        <?= partialNavButtons(1, 7, $canEdit, $decId) ?>
    </div>

    <!-- ══════════ ÉTAPE 2 : EFFECTIFS MENSUELS ══════════ -->
    <div class="form-section <?= $etape === 2 ? 'active' : '' ?>" id="etape-2">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-bold"><i class="bi bi-calendar3 text-primary me-2"></i>Étape 2 – Effectifs mensuels</h6>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">Indiquez le nombre total d'employés pour chaque mois de l'année.</p>
                <div class="table-responsive">
                    <table class="table table-bordered table-saisie text-center align-middle">
                        <thead class="table-primary">
                            <tr>
                                <?php for ($m = 1; $m <= 12; $m++): ?>
                                    <th><?= $moisLabels[$m] ?></th>
                                <?php endfor; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <?php for ($m = 1; $m <= 12; $m++): ?>
                                    <td>
                                        <input type="number"
                                               name="effectifs[<?= $m ?>]"
                                               class="form-control form-control-sm text-center px-1"
                                               value="<?= (int)($data['effectifs_mensuels'][$m] ?? 0) ?>"
                                               min="0" style="width:70px"
                                               <?= !$canEdit ? 'readonly' : '' ?>>
                                    </td>
                                <?php endfor; ?>
                            </tr>
                        </tbody>
                        <tfoot class="table-secondary total-row fw-bold">
                            <tr>
                                <td colspan="6" class="text-end pe-3">Somme / Max / Moyenne :</td>
                                <td colspan="3" class="text-center total-cell" id="eff-sum">0</td>
                                <td colspan="3" class="text-center text-muted" id="eff-avg">0</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="row mt-3">
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded text-center">
                            <div class="fs-5 fw-bold text-primary" id="total-mensuel">0</div>
                            <small class="text-muted">Total effectifs annuel</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded text-center">
                            <div class="fs-5 fw-bold text-success" id="max-mensuel">0</div>
                            <small class="text-muted">Effectif maximal</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded text-center">
                            <div class="fs-5 fw-bold text-info" id="moy-mensuel">0</div>
                            <small class="text-muted">Effectif moyen / mois</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?= partialNavButtons(2, 7, $canEdit, $decId) ?>
    </div>

    <!-- ══════════ ÉTAPE 3 : CATÉGORIES ══════════ -->
    <div class="form-section <?= $etape === 3 ? 'active' : '' ?>" id="etape-3">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-bold"><i class="bi bi-people text-primary me-2"></i>Étape 3 – Effectifs par catégorie et origine</h6>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">Renseignez les effectifs par catégorie professionnelle, origine et sexe.</p>
                <div class="table-responsive">
                    <table class="table table-bordered table-saisie table-sm align-middle">
                        <thead class="table-primary">
                            <tr>
                                <th rowspan="2" class="align-middle">Catégorie</th>
                                <th colspan="2" class="text-center">Nigériens</th>
                                <th colspan="2" class="text-center">Africains</th>
                                <th colspan="2" class="text-center">Autres nat.</th>
                                <th colspan="2" class="text-center bg-light fw-bold">Total</th>
                            </tr>
                            <tr>
                                <th class="text-center">H</th><th class="text-center">F</th>
                                <th class="text-center">H</th><th class="text-center">F</th>
                                <th class="text-center">H</th><th class="text-center">F</th>
                                <th class="text-center bg-light">H</th><th class="text-center bg-light">F</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (CATEGORIES_PROFESSIONNELLES as $key => $label): ?>
                                <?php $row = $data['categories'][$key] ?? []; ?>
                                <tr>
                                    <td class="fw-semibold small"><?= $label ?></td>
                                    <?php foreach (['nigeriens_h','nigeriens_f','africains_h','africains_f','autres_nat_h','autres_nat_f'] as $field): ?>
                                        <td>
                                            <input type="number"
                                                   name="categories[<?= $key ?>][<?= $field ?>]"
                                                   class="form-control form-control-sm text-center px-1 cat-input"
                                                   value="<?= (int)($row[$field] ?? 0) ?>"
                                                   min="0" data-cat="<?= $key ?>"
                                                   <?= !$canEdit ? 'readonly' : '' ?>>
                                        </td>
                                    <?php endforeach; ?>
                                    <td class="bg-light fw-bold text-center total-cell" id="tot-h-<?= $key ?>">0</td>
                                    <td class="bg-light fw-bold text-center total-cell" id="tot-f-<?= $key ?>">0</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-secondary fw-bold total-row">
                            <tr>
                                <td>TOTAL</td>
                                <td class="text-center" id="gtot-nih">0</td>
                                <td class="text-center" id="gtot-nif">0</td>
                                <td class="text-center" id="gtot-afh">0</td>
                                <td class="text-center" id="gtot-aff">0</td>
                                <td class="text-center" id="gtot-auh">0</td>
                                <td class="text-center" id="gtot-auf">0</td>
                                <td class="text-center bg-light" id="gtot-h">0</td>
                                <td class="text-center bg-light" id="gtot-f">0</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        <?= partialNavButtons(3, 7, $canEdit, $decId) ?>
    </div>

    <!-- ══════════ ÉTAPE 4 : NIVEAUX D'INSTRUCTION ══════════ -->
    <div class="form-section <?= $etape === 4 ? 'active' : '' ?>" id="etape-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-bold"><i class="bi bi-mortarboard text-primary me-2"></i>Étape 4 – Niveaux d'instruction</h6>
            </div>
            <div class="card-body">
                <?php foreach (CATEGORIES_PROFESSIONNELLES as $catKey => $catLabel): ?>
                    <div class="mb-4 fieldset-section">
                        <h6 class="section-title"><i class="bi bi-person-workspace me-1"></i><?= $catLabel ?></h6>
                        <div class="table-responsive">
                            <table class="table table-bordered table-saisie table-sm align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Niveau d'instruction</th>
                                        <th class="text-center" style="width:90px">Hommes</th>
                                        <th class="text-center" style="width:90px">Femmes</th>
                                        <th class="text-center bg-light" style="width:90px">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (NIVEAUX_INSTRUCTION as $nivKey => $nivLabel): ?>
                                        <?php $rowN = $data['niveaux'][$catKey][$nivKey] ?? []; ?>
                                        <tr>
                                            <td class="small"><?= $nivLabel ?></td>
                                            <td>
                                                <input type="number"
                                                       name="niveaux[<?= $catKey ?>][<?= $nivKey ?>][h]"
                                                       class="form-control form-control-sm text-center niv-input"
                                                       value="<?= (int)($rowN['effectif_h'] ?? 0) ?>"
                                                       min="0" data-niv-row="<?= $catKey ?>-<?= $nivKey ?>"
                                                       <?= !$canEdit ? 'readonly' : '' ?>>
                                            </td>
                                            <td>
                                                <input type="number"
                                                       name="niveaux[<?= $catKey ?>][<?= $nivKey ?>][f]"
                                                       class="form-control form-control-sm text-center niv-input"
                                                       value="<?= (int)($rowN['effectif_f'] ?? 0) ?>"
                                                       min="0" data-niv-row="<?= $catKey ?>-<?= $nivKey ?>"
                                                       <?= !$canEdit ? 'readonly' : '' ?>>
                                            </td>
                                            <td class="bg-light text-center fw-bold total-cell" id="niv-tot-<?= $catKey ?>-<?= $nivKey ?>">0</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot class="table-secondary fw-bold total-row">
                                    <tr>
                                        <td>Total <?= $catLabel ?></td>
                                        <td class="text-center" id="niv-sh-<?= $catKey ?>">0</td>
                                        <td class="text-center" id="niv-sf-<?= $catKey ?>">0</td>
                                        <td class="text-center bg-light" id="niv-st-<?= $catKey ?>">0</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?= partialNavButtons(4, 7, $canEdit, $decId) ?>
    </div>

    <!-- ══════════ ÉTAPE 5 : FORMATION PROFESSIONNELLE ══════════ -->
    <div class="form-section <?= $etape === 5 ? 'active' : '' ?>" id="etape-5">
        <?php $formation = ($data['formations'] ?? [[]])[0] ?? []; ?>
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-bold"><i class="bi bi-award text-primary me-2"></i>Étape 5 – Formation professionnelle</h6>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <label class="form-label fw-semibold">L'entreprise a-t-elle effectué des formations cette année ?</label>
                    <div class="d-flex gap-4">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="a_eu_formation"
                                   id="form-oui" value="1"
                                   <?= ($formation['a_eu_formation'] ?? false) ? 'checked' : '' ?>
                                   <?= !$canEdit ? 'disabled' : '' ?>>
                            <label class="form-check-label fw-semibold text-success" for="form-oui">
                                <i class="bi bi-check-circle me-1"></i>Oui
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="a_eu_formation"
                                   id="form-non" value="0"
                                   <?= !($formation['a_eu_formation'] ?? false) ? 'checked' : '' ?>
                                   <?= !$canEdit ? 'disabled' : '' ?>>
                            <label class="form-check-label fw-semibold text-secondary" for="form-non">
                                <i class="bi bi-x-circle me-1"></i>Non
                            </label>
                        </div>
                    </div>
                </div>
                <!-- id="formation-details" requis par saisie.js -->
                <div id="formation-details" <?= !($formation['a_eu_formation'] ?? false) ? 'style="display:none"' : '' ?>>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Qualification visée</label>
                            <input type="text" name="qualification" class="form-control"
                                   value="<?= e($formation['qualification'] ?? '') ?>"
                                   <?= !$canEdit ? 'readonly' : '' ?>>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nature de la formation</label>
                            <input type="text" name="nature_formation" class="form-control"
                                   value="<?= e($formation['nature_formation'] ?? '') ?>"
                                   <?= !$canEdit ? 'readonly' : '' ?>>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Durée</label>
                            <input type="text" name="duree_formation" class="form-control"
                                   placeholder="ex: 3 mois"
                                   value="<?= e($formation['duree_formation'] ?? '') ?>"
                                   <?= !$canEdit ? 'readonly' : '' ?>>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Effectif formé (H)</label>
                            <input type="number" name="formation_h" class="form-control"
                                   value="<?= (int)($formation['effectif_h'] ?? 0) ?>"
                                   min="0" <?= !$canEdit ? 'readonly' : '' ?>>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Effectif formé (F)</label>
                            <input type="number" name="formation_f" class="form-control"
                                   value="<?= (int)($formation['effectif_f'] ?? 0) ?>"
                                   min="0" <?= !$canEdit ? 'readonly' : '' ?>>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Observations</label>
                            <textarea name="observations" rows="3" class="form-control"
                                      <?= !$canEdit ? 'readonly' : '' ?>><?= e($formation['observations'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?= partialNavButtons(5, 7, $canEdit, $decId) ?>
    </div>

    <!-- ══════════ ÉTAPE 6 : PERTES D'EMPLOI ══════════ -->
    <div class="form-section <?= $etape === 6 ? 'active' : '' ?>" id="etape-6">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-bold"><i class="bi bi-person-dash text-primary me-2"></i>Étape 6 – Pertes d'emploi et perspectives</h6>
            </div>
            <div class="card-body">
                <h6 class="section-title">Pertes d'emploi par motif</h6>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-saisie align-middle">
                        <thead class="table-primary">
                            <tr>
                                <th>Motif</th>
                                <th class="text-center" style="width:100px">Hommes</th>
                                <th class="text-center" style="width:100px">Femmes</th>
                                <th class="text-center bg-light" style="width:100px">Total</th>
                                <th>Précision (si Autres)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (MOTIFS_PERTE_EMPLOI as $motifKey => $motifLabel): ?>
                                <?php $rowP = $data['pertes'][$motifKey] ?? []; ?>
                                <tr>
                                    <td class="fw-semibold"><?= $motifLabel ?></td>
                                    <td>
                                        <input type="number"
                                               name="pertes[<?= $motifKey ?>][h]"
                                               class="form-control form-control-sm text-center perte-input"
                                               value="<?= (int)($rowP['effectif_h'] ?? 0) ?>"
                                               min="0" data-perte="<?= $motifKey ?>"
                                               <?= !$canEdit ? 'readonly' : '' ?>>
                                    </td>
                                    <td>
                                        <input type="number"
                                               name="pertes[<?= $motifKey ?>][f]"
                                               class="form-control form-control-sm text-center perte-input"
                                               value="<?= (int)($rowP['effectif_f'] ?? 0) ?>"
                                               min="0" data-perte="<?= $motifKey ?>"
                                               <?= !$canEdit ? 'readonly' : '' ?>>
                                    </td>
                                    <td class="bg-light text-center fw-bold total-cell" id="perte-tot-<?= $motifKey ?>">0</td>
                                    <td>
                                        <?php if ($motifKey === 'autres'): ?>
                                            <input type="text" name="pertes[autres][autre_precision]"
                                                   class="form-control form-control-sm"
                                                   placeholder="Préciser…"
                                                   value="<?= e($rowP['motif_autre'] ?? '') ?>"
                                                   <?= !$canEdit ? 'readonly' : '' ?>>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-secondary fw-bold total-row">
                            <tr>
                                <td>TOTAL</td>
                                <td class="text-center" id="ptot-h">0</td>
                                <td class="text-center" id="ptot-f">0</td>
                                <td class="text-center bg-light" id="ptot-t">0</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <h6 class="section-title">Perspectives d'emploi pour la prochaine période</h6>
                <?php $persp = $data['perspective'] ?? []; ?>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Perspective</label>
                        <select name="perspective" class="form-select" <?= !$canEdit ? 'disabled' : '' ?>>
                            <option value="">-- Sélectionner --</option>
                            <?php foreach (['stable'=>'Stable','hausse'=>'En hausse','baisse'=>'En baisse','inconnue'=>'Inconnue'] as $k => $v): ?>
                                <option value="<?= $k ?>" <?= ($persp['perspective'] ?? '') === $k ? 'selected' : '' ?>><?= $v ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Justification</label>
                        <input type="text" name="justification" class="form-control"
                               value="<?= e($persp['justification'] ?? '') ?>"
                               <?= !$canEdit ? 'readonly' : '' ?>>
                    </div>
                </div>
            </div>
        </div>
        <?= partialNavButtons(6, 7, $canEdit, $decId) ?>
    </div>

    <!-- ══════════ ÉTAPE 7 : EFFECTIFS ÉTRANGERS ══════════ -->
    <div class="form-section <?= $etape === 7 ? 'active' : '' ?>" id="etape-7">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold"><i class="bi bi-globe text-primary me-2"></i>Étape 7 – Effectifs étrangers</h6>
                <?php if ($canEdit): ?>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="btn-add-etranger">
                        <i class="bi bi-plus-lg me-1"></i>Ajouter une ligne
                    </button>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">Liste des travailleurs étrangers (hors Nigériens) employés dans l'entreprise.</p>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle table-etrangers">
                        <thead class="table-primary">
                            <tr>
                                <th>Pays</th>
                                <th>Qualification</th>
                                <th>Fonction</th>
                                <th class="text-center" style="width:70px">Sexe</th>
                                <th class="text-center" style="width:80px">Nombre</th>
                                <?php if ($canEdit): ?><th class="text-center" style="width:50px">×</th><?php endif; ?>
                            </tr>
                        </thead>
                        <!-- id="etrangers-tbody" requis par saisie.js -->
                        <tbody id="etrangers-tbody">
                            <?php if (!empty($data['etrangers'])): ?>
                                <?php foreach ($data['etrangers'] as $i => $et): ?>
                                    <tr class="etrangers-row">
                                        <td><input type="text" name="etrangers[<?= $i ?>][pays]" class="form-control form-control-sm" value="<?= e($et['pays']) ?>" <?= !$canEdit ? 'readonly' : '' ?>></td>
                                        <td><input type="text" name="etrangers[<?= $i ?>][qualification]" class="form-control form-control-sm" value="<?= e($et['qualification'] ?? '') ?>" <?= !$canEdit ? 'readonly' : '' ?>></td>
                                        <td><input type="text" name="etrangers[<?= $i ?>][fonction]" class="form-control form-control-sm" value="<?= e($et['fonction'] ?? '') ?>" <?= !$canEdit ? 'readonly' : '' ?>></td>
                                        <td>
                                            <select name="etrangers[<?= $i ?>][sexe]" class="form-select form-select-sm" <?= !$canEdit ? 'disabled' : '' ?>>
                                                <option value="H" <?= ($et['sexe'] ?? 'H') === 'H' ? 'selected' : '' ?>>H</option>
                                                <option value="F" <?= ($et['sexe'] ?? '') === 'F' ? 'selected' : '' ?>>F</option>
                                            </select>
                                        </td>
                                        <td><input type="number" name="etrangers[<?= $i ?>][nombre]" class="form-control form-control-sm text-center" value="<?= (int)($et['nombre'] ?? 0) ?>" min="0" <?= !$canEdit ? 'readonly' : '' ?>></td>
                                        <?php if ($canEdit): ?><td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove()"><i class="bi bi-trash"></i></button></td><?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr id="row-no-etranger"><td colspan="6" class="text-center text-muted py-3"><i class="bi bi-info-circle me-1"></i>Aucun effectif étranger. Cliquez sur "Ajouter" pour en saisir.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Boutons finaux étape 7 -->
        <div class="d-flex justify-content-between align-items-center mt-3 mb-4">
            <button type="button" class="btn btn-outline-secondary btn-prev">
                <i class="bi bi-arrow-left me-1"></i>Précédent
            </button>
            <div class="d-flex gap-2">
                <?php if ($canEdit): ?>
                    <button type="submit" class="btn btn-success btn-save">
                        <i class="bi bi-floppy me-1"></i>Sauvegarder
                    </button>
                    <button type="button" class="btn btn-primary btn-submit"
                            data-bs-toggle="modal" data-bs-target="#modal-soumettre">
                        <i class="bi bi-send me-1"></i>Soumettre à l'ANPE
                    </button>
                <?php else: ?>
                    <a href="<?= url("agent/declaration/$decId/apercu") ?>" class="btn btn-info">
                        <i class="bi bi-eye me-1"></i>Voir l'aperçu
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

</form>

<!-- Modal confirmation soumission -->
<div class="modal fade" id="modal-soumettre" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-send me-2"></i>Confirmer la soumission</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Attention !</strong> Une fois soumise, la déclaration ne pourra plus être modifiée sans validation de l'administration.
                </div>
                <p>Voulez-vous soumettre définitivement la déclaration
                   <strong><?= e($declaration['code_questionnaire'] ?? '') ?></strong> de
                   <strong><?= e($entreprise['raison_sociale'] ?? '') ?></strong> ?
                </p>
                <p class="text-muted small">Complétude actuelle : <strong><?= $pct ?>%</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary btn-submit" id="btn-confirm-soumettre">
                    <i class="bi bi-send me-1"></i>Oui, soumettre
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Script inline complémentaire (calculs spécifiques non couverts par saisie.js) -->
<script>
// Calcul effectifs mensuels
function calcMensuels() {
    const inputs = document.querySelectorAll('#etape-2 input[type=number]');
    let sum = 0, max = 0;
    inputs.forEach(i => {
        const v = parseInt(i.value) || 0;
        sum += v;
        if (v > max) max = v;
    });
    const t = document.getElementById('total-mensuel');
    const m = document.getElementById('max-mensuel');
    const a = document.getElementById('moy-mensuel');
    if (t) t.textContent = sum;
    if (m) m.textContent = max;
    if (a) a.textContent = Math.round(sum / 12);
}
document.querySelectorAll('#etape-2 input[type=number]').forEach(i => i.addEventListener('input', calcMensuels));
calcMensuels();

// Calcul catégories
function calcCategories() {
    const cats = <?= json_encode(array_keys(CATEGORIES_PROFESSIONNELLES)) ?>;
    let gNiH=0,gNiF=0,gAfH=0,gAfF=0,gAuH=0,gAuF=0;
    cats.forEach(cat => {
        const f = ['nigeriens_h','nigeriens_f','africains_h','africains_f','autres_nat_h','autres_nat_f'];
        const v = {};
        f.forEach(fld => {
            const el = document.querySelector(`input[name="categories[${cat}][${fld}]"]`);
            v[fld] = el ? (parseInt(el.value)||0) : 0;
        });
        gNiH+=v.nigeriens_h; gNiF+=v.nigeriens_f;
        gAfH+=v.africains_h; gAfF+=v.africains_f;
        gAuH+=v.autres_nat_h; gAuF+=v.autres_nat_f;
        const tH = document.getElementById(`tot-h-${cat}`);
        const tF = document.getElementById(`tot-f-${cat}`);
        if (tH) tH.textContent = v.nigeriens_h + v.africains_h + v.autres_nat_h;
        if (tF) tF.textContent = v.nigeriens_f + v.africains_f + v.autres_nat_f;
    });
    [['gtot-nih',gNiH],['gtot-nif',gNiF],['gtot-afh',gAfH],['gtot-aff',gAfF],
     ['gtot-auh',gAuH],['gtot-auf',gAuF],['gtot-h',gNiH+gAfH+gAuH],['gtot-f',gNiF+gAfF+gAuF]].forEach(([id,val]) => {
        const el = document.getElementById(id);
        if(el) el.textContent = val;
    });
}
document.querySelectorAll('.cat-input').forEach(i => i.addEventListener('input', calcCategories));
calcCategories();

// Calcul niveaux instruction
function calcNiveaux() {
    const cats = <?= json_encode(array_keys(CATEGORIES_PROFESSIONNELLES)) ?>;
    const nivs = <?= json_encode(array_keys(NIVEAUX_INSTRUCTION)) ?>;
    cats.forEach(cat => {
        let sH=0, sF=0;
        nivs.forEach(niv => {
            const h = parseInt(document.querySelector(`input[name="niveaux[${cat}][${niv}][h]"]`)?.value)||0;
            const f = parseInt(document.querySelector(`input[name="niveaux[${cat}][${niv}][f]"]`)?.value)||0;
            sH+=h; sF+=f;
            const tot = document.getElementById(`niv-tot-${cat}-${niv}`);
            if(tot) tot.textContent = h+f;
        });
        const sh = document.getElementById(`niv-sh-${cat}`);
        const sf = document.getElementById(`niv-sf-${cat}`);
        const st = document.getElementById(`niv-st-${cat}`);
        if(sh) sh.textContent = sH;
        if(sf) sf.textContent = sF;
        if(st) st.textContent = sH+sF;
    });
}
document.querySelectorAll('.niv-input').forEach(i => i.addEventListener('input', calcNiveaux));
calcNiveaux();

// Calcul pertes d'emploi
function calcPertes() {
    const motifs = <?= json_encode(array_keys(MOTIFS_PERTE_EMPLOI)) ?>;
    let tH=0, tF=0;
    motifs.forEach(m => {
        const h = parseInt(document.querySelector(`input[name="pertes[${m}][h]"]`)?.value)||0;
        const f = parseInt(document.querySelector(`input[name="pertes[${m}][f]"]`)?.value)||0;
        tH+=h; tF+=f;
        const tot = document.getElementById(`perte-tot-${m}`);
        if(tot) tot.textContent = h+f;
    });
    const ph = document.getElementById('ptot-h');
    const pf = document.getElementById('ptot-f');
    const pt = document.getElementById('ptot-t');
    if(ph) ph.textContent = tH;
    if(pf) pf.textContent = tF;
    if(pt) pt.textContent = tH+tF;
}
document.querySelectorAll('.perte-input').forEach(i => i.addEventListener('input', calcPertes));
calcPertes();

// Bouton "Confirmer soumission" dans le modal (déclenche la soumission AJAX)
document.getElementById('btn-confirm-soumettre')?.addEventListener('click', async () => {
    const modal = bootstrap.Modal.getInstance(document.getElementById('modal-soumettre'));
    if (modal) modal.hide();

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const decId = document.getElementById('wizard-form')?.dataset.declId ?? 0;
    const fd = new FormData();
    fd.append('_csrf_token', csrf);

    try {
        const resp = await fetch(`<?= url("agent/declaration/$decId/soumettre") ?>`, {
            method: 'POST', body: fd,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const json = await resp.json();
        if (json.success) {
            window.location.href = json.redirect ?? '<?= url("agent/declaration/$decId/apercu") ?>';
        } else {
            alert(json.message ?? 'Erreur lors de la soumission.');
        }
    } catch(e) {
        alert('Erreur réseau. Veuillez réessayer.');
    }
});
</script>

<?php
/**
 * Génère les boutons de navigation (Précédent / Suivant+Sauvegarder) pour une étape
 */
function partialNavButtons(int $current, int $total, bool $canEdit, int $decId): string
{
    $html  = '<div class="d-flex justify-content-between align-items-center mt-3 mb-4">';
    $html .= '<button type="button" class="btn btn-outline-secondary btn-prev"' . ($current <= 1 ? ' disabled' : '') . '>';
    $html .= '<i class="bi bi-arrow-left me-1"></i>Précédent</button>';
    $html .= '<div class="d-flex gap-2">';
    if ($canEdit) {
        $html .= '<button type="submit" class="btn btn-outline-success btn-save"><i class="bi bi-floppy me-1"></i>Sauvegarder</button>';
    }
    if ($current < $total) {
        $html .= '<button type="button" class="btn btn-primary btn-next"><i class="bi bi-arrow-right me-1"></i>Suivant</button>';
    }
    $html .= '</div></div>';
    return $html;
}
?>
