<?php
/**
 * Vue de saisie multi-étapes d'une déclaration
 * Étape 1: Identification | 2: Effectifs Mensuels | 3: Catégories | 4: Niveaux instruction
 * Étape 5: Formation | 6: Pertes d'emploi | 7: Effectifs étrangers
 */
$declaration = $declaration ?? [];
$entreprise  = $data['entreprise'] ?? [];
$etape       = $etape ?? 1;
$data        = $data ?? [];

$etapes = [
    1 => ['titre' => 'Identification',       'icon' => 'bi-building'],
    2 => ['titre' => 'Effectifs mensuels',   'icon' => 'bi-calendar3'],
    3 => ['titre' => 'Catégories',           'icon' => 'bi-people'],
    4 => ['titre' => 'Niveaux instruction',  'icon' => 'bi-mortarboard'],
    5 => ['titre' => 'Formation prof.',      'icon' => 'bi-award'],
    6 => ['titre' => 'Pertes d\'emploi',     'icon' => 'bi-person-dash'],
    7 => ['titre' => 'Effectifs étrangers',  'icon' => 'bi-globe'],
];

$moisLabels = ['','Jan','Fév','Mar','Avr','Mai','Jun','Jul','Aoû','Sep','Oct','Nov','Déc'];

$statut = $declaration['statut'] ?? 'brouillon';
$canEdit = in_array($statut, ['brouillon', 'corrigee']);
$decId   = $declaration['id'] ?? 0;
$pct     = $declaration['pourcentage_completion'] ?? 0;
?>

<style>
.wizard-step { display: none; }
.wizard-step.active { display: block; }
.step-nav-item { cursor: pointer; transition: all 0.2s; }
.step-nav-item.completed .step-circle { background: #198754; color: #fff; border-color: #198754; }
.step-nav-item.active .step-circle   { background: #0d6efd; color: #fff; border-color: #0d6efd; }
.step-nav-item.pending .step-circle  { background: #fff; color: #6c757d; border-color: #dee2e6; }
.step-circle { width: 36px; height: 36px; border-radius: 50%; border: 2px solid; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.85rem; margin: 0 auto 4px; }
.step-connector { flex: 1; height: 2px; background: #dee2e6; margin: 0 4px; margin-top: -20px; }
.step-connector.done { background: #198754; }
.table-saisie input[type=number] { width: 80px; text-align: center; }
.table-saisie input[type=number]:focus { box-shadow: 0 0 0 0.2rem rgba(13,110,253,.25); }
.etrangers-row { background: #f8f9fa; }
</style>

<!-- En-tête de la déclaration -->
<div class="row mb-3">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body py-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-2 bg-primary bg-opacity-10 rounded">
                        <i class="bi bi-file-earmark-text text-primary fs-4"></i>
                    </div>
                    <div>
                        <h5 class="mb-0"><?= e($entreprise['raison_sociale'] ?? '') ?></h5>
                        <small class="text-muted">
                            Code: <strong><?= e($declaration['code_questionnaire'] ?? '') ?></strong>
                            &bull; Campagne: <strong><?= e($entreprise['annee'] ?? '') ?></strong>
                            &bull; Région: <?= e($entreprise['region_nom'] ?? '') ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <small class="fw-semibold">Progression</small>
                    <small class="text-primary fw-bold"><?= $pct ?>%</small>
                </div>
                <div class="progress" style="height:8px">
                    <div class="progress-bar bg-primary" style="width:<?= $pct ?>%"></div>
                </div>
                <div class="mt-2">
                    <?php
                    $badgeClass = ['brouillon'=>'secondary','soumise'=>'warning','validee'=>'success','rejetee'=>'danger','corrigee'=>'info'][$statut] ?? 'secondary';
                    $badgeLabel = ['brouillon'=>'Brouillon','soumise'=>'Soumise','validee'=>'Validée','rejetee'=>'Rejetée','corrigee'=>'En correction'][$statut] ?? $statut;
                    ?>
                    <span class="badge bg-<?= $badgeClass ?>"><?= $badgeLabel ?></span>
                    <?php if ($declaration['motif_rejet'] ?? ''): ?>
                        <div class="alert alert-danger py-1 px-2 mt-1 mb-0" style="font-size:.78rem">
                            <i class="bi bi-exclamation-triangle me-1"></i> <?= e($declaration['motif_rejet']) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Navigation des étapes -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <div class="d-flex align-items-start justify-content-between" id="steps-nav">
            <?php foreach ($etapes as $num => $info): ?>
                <?php
                $etapeCourante = (int)($declaration['etape_courante'] ?? 1);
                $stepClass = $num < $etape ? 'completed' : ($num === $etape ? 'active' : 'pending');
                ?>
                <div class="step-nav-item <?= $stepClass ?> text-center" style="flex:1"
                     onclick="goToStep(<?= $num ?>)" data-step="<?= $num ?>">
                    <div class="step-circle">
                        <?php if ($num < $etape): ?>
                            <i class="bi bi-check-lg"></i>
                        <?php else: ?>
                            <?= $num ?>
                        <?php endif; ?>
                    </div>
                    <div style="font-size:.7rem; line-height:1.2"><?= $info['titre'] ?></div>
                </div>
                <?php if ($num < 7): ?>
                    <div class="step-connector <?= $num < $etape ? 'done' : '' ?>"></div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Formulaire principal -->
<form id="form-saisie" method="POST" action="<?= url("agent/declaration/$decId/sauvegarder") ?>">
    <?= csrfField() ?>
    <input type="hidden" name="etape" id="input-etape" value="<?= $etape ?>">

    <!-- ==================== ÉTAPE 1 : IDENTIFICATION ==================== -->
    <div class="wizard-step <?= $etape === 1 ? 'active' : '' ?>" id="step-1">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0"><i class="bi bi-building me-2 text-primary"></i>Étape 1 – Identification de l'entreprise</h6>
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
    </div>

    <!-- ==================== ÉTAPE 2 : EFFECTIFS MENSUELS ==================== -->
    <div class="wizard-step <?= $etape === 2 ? 'active' : '' ?>" id="step-2">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0"><i class="bi bi-calendar3 me-2 text-primary"></i>Étape 2 – Effectifs mensuels</h6>
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
                                        <input type="number" name="effectifs[<?= $m ?>]"
                                               class="form-control form-control-sm text-center px-1"
                                               value="<?= (int)($data['effectifs_mensuels'][$m] ?? 0) ?>"
                                               min="0" style="width:70px"
                                               <?= !$canEdit ? 'readonly' : '' ?>>
                                    </td>
                                <?php endfor; ?>
                            </tr>
                        </tbody>
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
    </div>

    <!-- ==================== ÉTAPE 3 : CATÉGORIES ==================== -->
    <div class="wizard-step <?= $etape === 3 ? 'active' : '' ?>" id="step-3">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0"><i class="bi bi-people me-2 text-primary"></i>Étape 3 – Effectifs par catégorie et origine</h6>
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
                                            <input type="number" name="categories[<?= $key ?>][<?= $field ?>]"
                                                   class="form-control form-control-sm text-center px-1 cat-input"
                                                   value="<?= (int)($row[$field] ?? 0) ?>"
                                                   min="0" data-cat="<?= $key ?>"
                                                   <?= !$canEdit ? 'readonly' : '' ?>>
                                        </td>
                                    <?php endforeach; ?>
                                    <td class="bg-light fw-bold text-center" id="tot-h-<?= $key ?>">0</td>
                                    <td class="bg-light fw-bold text-center" id="tot-f-<?= $key ?>">0</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-secondary fw-bold">
                            <tr>
                                <td>TOTAL</td>
                                <td class="text-center" id="gtot-nih">0</td>
                                <td class="text-center" id="gtot-nif">0</td>
                                <td class="text-center" id="gtot-afh">0</td>
                                <td class="text-center" id="gtot-aff">0</td>
                                <td class="text-center" id="gtot-auh">0</td>
                                <td class="text-center" id="gtot-auf">0</td>
                                <td class="text-center" id="gtot-h">0</td>
                                <td class="text-center" id="gtot-f">0</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== ÉTAPE 4 : NIVEAUX D'INSTRUCTION ==================== -->
    <div class="wizard-step <?= $etape === 4 ? 'active' : '' ?>" id="step-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0"><i class="bi bi-mortarboard me-2 text-primary"></i>Étape 4 – Niveaux d'instruction</h6>
            </div>
            <div class="card-body">
                <?php foreach (CATEGORIES_PROFESSIONNELLES as $catKey => $catLabel): ?>
                    <div class="mb-4">
                        <h6 class="fw-semibold text-secondary border-bottom pb-2">
                            <i class="bi bi-person-workspace me-1"></i><?= $catLabel ?>
                        </h6>
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
                                        <?php $row = $data['niveaux'][$catKey][$nivKey] ?? []; ?>
                                        <tr>
                                            <td class="small"><?= $nivLabel ?></td>
                                            <td>
                                                <input type="number" name="niveaux[<?= $catKey ?>][<?= $nivKey ?>][h]"
                                                       class="form-control form-control-sm text-center niv-input"
                                                       value="<?= (int)($row['effectif_h'] ?? 0) ?>"
                                                       min="0" data-niv-row="<?= $catKey ?>-<?= $nivKey ?>"
                                                       <?= !$canEdit ? 'readonly' : '' ?>>
                                            </td>
                                            <td>
                                                <input type="number" name="niveaux[<?= $catKey ?>][<?= $nivKey ?>][f]"
                                                       class="form-control form-control-sm text-center niv-input"
                                                       value="<?= (int)($row['effectif_f'] ?? 0) ?>"
                                                       min="0" data-niv-row="<?= $catKey ?>-<?= $nivKey ?>"
                                                       <?= !$canEdit ? 'readonly' : '' ?>>
                                            </td>
                                            <td class="bg-light text-center fw-bold" id="niv-tot-<?= $catKey ?>-<?= $nivKey ?>">0</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot class="table-secondary fw-bold">
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
    </div>

    <!-- ==================== ÉTAPE 5 : FORMATION PROFESSIONNELLE ==================== -->
    <div class="wizard-step <?= $etape === 5 ? 'active' : '' ?>" id="step-5">
        <?php $formation = ($data['formations'] ?? [[]])[0] ?? []; ?>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0"><i class="bi bi-award me-2 text-primary"></i>Étape 5 – Formation professionnelle</h6>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <label class="form-label fw-semibold">L'entreprise a-t-elle effectué des formations cette année ?</label>
                    <div class="d-flex gap-4">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="a_eu_formation" id="form-oui" value="1"
                                   <?= ($formation['a_eu_formation'] ?? false) ? 'checked' : '' ?>
                                   <?= !$canEdit ? 'disabled' : '' ?>>
                            <label class="form-check-label fw-semibold text-success" for="form-oui">
                                <i class="bi bi-check-circle me-1"></i>Oui
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="a_eu_formation" id="form-non" value="0"
                                   <?= !($formation['a_eu_formation'] ?? false) ? 'checked' : '' ?>
                                   <?= !$canEdit ? 'disabled' : '' ?>>
                            <label class="form-check-label fw-semibold text-secondary" for="form-non">
                                <i class="bi bi-x-circle me-1"></i>Non
                            </label>
                        </div>
                    </div>
                </div>
                <div id="bloc-formation" <?= !($formation['a_eu_formation'] ?? false) ? 'style="display:none"' : '' ?>>
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
    </div>

    <!-- ==================== ÉTAPE 6 : PERTES D'EMPLOI ==================== -->
    <div class="wizard-step <?= $etape === 6 ? 'active' : '' ?>" id="step-6">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0"><i class="bi bi-person-dash me-2 text-primary"></i>Étape 6 – Pertes d'emploi et perspectives</h6>
            </div>
            <div class="card-body">
                <h6 class="fw-semibold text-secondary mb-3">Pertes d'emploi par motif</h6>
                <div class="table-responsive">
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
                                <?php $row = $data['pertes'][$motifKey] ?? []; ?>
                                <tr>
                                    <td class="fw-semibold"><?= $motifLabel ?></td>
                                    <td>
                                        <input type="number" name="pertes[<?= $motifKey ?>][h]"
                                               class="form-control form-control-sm text-center perte-input"
                                               value="<?= (int)($row['effectif_h'] ?? 0) ?>"
                                               min="0" data-perte="<?= $motifKey ?>"
                                               <?= !$canEdit ? 'readonly' : '' ?>>
                                    </td>
                                    <td>
                                        <input type="number" name="pertes[<?= $motifKey ?>][f]"
                                               class="form-control form-control-sm text-center perte-input"
                                               value="<?= (int)($row['effectif_f'] ?? 0) ?>"
                                               min="0" data-perte="<?= $motifKey ?>"
                                               <?= !$canEdit ? 'readonly' : '' ?>>
                                    </td>
                                    <td class="bg-light text-center fw-bold" id="perte-tot-<?= $motifKey ?>">0</td>
                                    <td>
                                        <?php if ($motifKey === 'autres'): ?>
                                            <input type="text" name="pertes[autres][autre_precision]"
                                                   class="form-control form-control-sm"
                                                   placeholder="Préciser…"
                                                   value="<?= e($row['motif_autre'] ?? '') ?>"
                                                   <?= !$canEdit ? 'readonly' : '' ?>>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-secondary fw-bold">
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

                <!-- Perspectives -->
                <h6 class="fw-semibold text-secondary mt-4 mb-3">Perspectives d'emploi pour la prochaine période</h6>
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
    </div>

    <!-- ==================== ÉTAPE 7 : EFFECTIFS ÉTRANGERS ==================== -->
    <div class="wizard-step <?= $etape === 7 ? 'active' : '' ?>" id="step-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bi bi-globe me-2 text-primary"></i>Étape 7 – Effectifs étrangers</h6>
                <?php if ($canEdit): ?>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="btn-add-etranger">
                        <i class="bi bi-plus-lg me-1"></i>Ajouter une ligne
                    </button>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">Liste des travailleurs étrangers (hors Nigériens) employés dans l'entreprise.</p>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle" id="table-etrangers">
                        <thead class="table-primary">
                            <tr>
                                <th>Pays</th>
                                <th>Qualification</th>
                                <th>Fonction</th>
                                <th class="text-center" style="width:80px">Sexe</th>
                                <th class="text-center" style="width:80px">Nombre</th>
                                <?php if ($canEdit): ?><th class="text-center" style="width:50px">—</th><?php endif; ?>
                            </tr>
                        </thead>
                        <tbody id="tbody-etrangers">
                            <?php if (!empty($data['etrangers'])): ?>
                                <?php foreach ($data['etrangers'] as $i => $e): ?>
                                    <tr class="etrangers-row">
                                        <td><input type="text" name="etrangers[<?= $i ?>][pays]" class="form-control form-control-sm" value="<?= e($e['pays']) ?>" <?= !$canEdit ? 'readonly' : '' ?>></td>
                                        <td><input type="text" name="etrangers[<?= $i ?>][qualification]" class="form-control form-control-sm" value="<?= e($e['qualification'] ?? '') ?>" <?= !$canEdit ? 'readonly' : '' ?>></td>
                                        <td><input type="text" name="etrangers[<?= $i ?>][fonction]" class="form-control form-control-sm" value="<?= e($e['fonction'] ?? '') ?>" <?= !$canEdit ? 'readonly' : '' ?>></td>
                                        <td>
                                            <select name="etrangers[<?= $i ?>][sexe]" class="form-select form-select-sm" <?= !$canEdit ? 'disabled' : '' ?>>
                                                <option value="H" <?= ($e['sexe'] ?? 'H') === 'H' ? 'selected' : '' ?>>H</option>
                                                <option value="F" <?= ($e['sexe'] ?? '') === 'F' ? 'selected' : '' ?>>F</option>
                                            </select>
                                        </td>
                                        <td><input type="number" name="etrangers[<?= $i ?>][nombre]" class="form-control form-control-sm text-center" value="<?= (int)($e['nombre'] ?? 0) ?>" min="0" <?= !$canEdit ? 'readonly' : '' ?>></td>
                                        <?php if ($canEdit): ?><td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger btn-rm-etranger"><i class="bi bi-trash"></i></button></td><?php endif; ?>
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
    </div>

    <!-- ==================== BOUTONS DE NAVIGATION ==================== -->
    <div class="d-flex justify-content-between align-items-center mt-3 mb-4">
        <div>
            <button type="button" class="btn btn-outline-secondary" id="btn-prev" <?= $etape <= 1 ? 'disabled' : '' ?>>
                <i class="bi bi-arrow-left me-1"></i>Précédent
            </button>
        </div>
        <div class="d-flex gap-2">
            <?php if ($canEdit): ?>
                <button type="button" class="btn btn-outline-success" id="btn-save-only">
                    <i class="bi bi-floppy me-1"></i>Sauvegarder
                </button>
            <?php endif; ?>
            <?php if ($etape < 7): ?>
                <button type="submit" class="btn btn-primary" id="btn-next">
                    Suivant <i class="bi bi-arrow-right ms-1"></i>
                </button>
            <?php else: ?>
                <?php if ($canEdit): ?>
                    <button type="submit" class="btn btn-success" id="btn-save-final">
                        <i class="bi bi-floppy me-1"></i>Sauvegarder
                    </button>
                    <button type="button" class="btn btn-primary" id="btn-soumettre"
                            data-url="<?= url("agent/declaration/$decId/soumettre") ?>">
                        <i class="bi bi-send me-1"></i>Soumettre à l'ANPE
                    </button>
                <?php endif; ?>
            <?php endif; ?>
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
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <form method="POST" id="form-soumettre" action="<?= url("agent/declaration/$decId/soumettre") ?>" style="display:inline">
                    <?= csrfField() ?>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send me-1"></i>Oui, soumettre
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const CURRENT_STEP = <?= $etape ?>;
const CAN_EDIT     = <?= $canEdit ? 'true' : 'false' ?>;
let currentStep    = CURRENT_STEP;
let etrangerIdx    = <?= count($data['etrangers'] ?? []) ?>;

// ── Navigation ──────────────────────────────────────────────────
function goToStep(n) {
    const url = new URL(window.location.href);
    url.searchParams.set('etape', n);
    window.location.href = url.toString();
}

document.getElementById('btn-prev')?.addEventListener('click', () => {
    if (currentStep > 1) goToStep(currentStep - 1);
});

document.getElementById('btn-save-only')?.addEventListener('click', () => {
    document.getElementById('form-saisie').submit();
});

document.getElementById('btn-soumettre')?.addEventListener('click', () => {
    new bootstrap.Modal(document.getElementById('modal-soumettre')).show();
});

// ── Étape 2 : Calcul automatique effectifs mensuels ──────────────
function calcMensuels() {
    const inputs = document.querySelectorAll('#step-2 input[type=number]');
    let sum = 0, max = 0;
    inputs.forEach(i => {
        const v = parseInt(i.value) || 0;
        sum += v;
        if (v > max) max = v;
    });
    const tot = document.getElementById('total-mensuel');
    const mx  = document.getElementById('max-mensuel');
    const mo  = document.getElementById('moy-mensuel');
    if (tot) tot.textContent = sum;
    if (mx)  mx.textContent  = max;
    if (mo)  mo.textContent  = Math.round(sum / 12);
}
document.querySelectorAll('#step-2 input[type=number]').forEach(i => {
    i.addEventListener('input', calcMensuels);
});
calcMensuels();

// ── Étape 3 : Calcul catégories ──────────────────────────────────
function calcCategories() {
    const cats = <?= json_encode(array_keys(CATEGORIES_PROFESSIONNELLES)) ?>;
    let gNiH=0,gNiF=0,gAfH=0,gAfF=0,gAuH=0,gAuF=0;
    cats.forEach(cat => {
        const fields = ['nigeriens_h','nigeriens_f','africains_h','africains_f','autres_nat_h','autres_nat_f'];
        const vals = {};
        fields.forEach(f => {
            const el = document.querySelector(`input[name="categories[${cat}][${f}]"]`);
            vals[f] = el ? (parseInt(el.value)||0) : 0;
        });
        gNiH+=vals.nigeriens_h; gNiF+=vals.nigeriens_f;
        gAfH+=vals.africains_h; gAfF+=vals.africains_f;
        gAuH+=vals.autres_nat_h; gAuF+=vals.autres_nat_f;
        const totH = vals.nigeriens_h + vals.africains_h + vals.autres_nat_h;
        const totF = vals.nigeriens_f + vals.africains_f + vals.autres_nat_f;
        const tH = document.getElementById(`tot-h-${cat}`);
        const tF = document.getElementById(`tot-f-${cat}`);
        if (tH) tH.textContent = totH;
        if (tF) tF.textContent = totF;
    });
    ['gtot-nih','gtot-nif','gtot-afh','gtot-aff','gtot-auh','gtot-auf','gtot-h','gtot-f'].forEach((id,i) => {
        const el = document.getElementById(id);
        if (el) el.textContent = [gNiH,gNiF,gAfH,gAfF,gAuH,gAuF,gNiH+gAfH+gAuH,gNiF+gAfF+gAuF][i];
    });
}
document.querySelectorAll('.cat-input').forEach(i => i.addEventListener('input', calcCategories));
calcCategories();

// ── Étape 4 : Calcul niveaux ─────────────────────────────────────
function calcNiveaux() {
    const cats = <?= json_encode(array_keys(CATEGORIES_PROFESSIONNELLES)) ?>;
    const nivs = <?= json_encode(array_keys(NIVEAUX_INSTRUCTION)) ?>;
    cats.forEach(cat => {
        let sH=0,sF=0;
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
        if(sh) sh.textContent=sH;
        if(sf) sf.textContent=sF;
        if(st) st.textContent=sH+sF;
    });
}
document.querySelectorAll('.niv-input').forEach(i => i.addEventListener('input', calcNiveaux));
calcNiveaux();

// ── Étape 5 : Afficher/Masquer bloc formation ────────────────────
document.querySelectorAll('input[name=a_eu_formation]').forEach(r => {
    r.addEventListener('change', () => {
        const bloc = document.getElementById('bloc-formation');
        if (bloc) bloc.style.display = r.value === '1' ? '' : 'none';
    });
});

// ── Étape 6 : Calcul pertes ──────────────────────────────────────
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
    if(ph) ph.textContent=tH;
    if(pf) pf.textContent=tF;
    if(pt) pt.textContent=tH+tF;
}
document.querySelectorAll('.perte-input').forEach(i => i.addEventListener('input', calcPertes));
calcPertes();

// ── Étape 7 : Ajout/Suppression lignes étrangers ─────────────────
function makeRow(idx) {
    return `<tr class="etrangers-row">
        <td><input type="text" name="etrangers[${idx}][pays]" class="form-control form-control-sm" placeholder="Pays"></td>
        <td><input type="text" name="etrangers[${idx}][qualification]" class="form-control form-control-sm" placeholder="Qualification"></td>
        <td><input type="text" name="etrangers[${idx}][fonction]" class="form-control form-control-sm" placeholder="Fonction"></td>
        <td><select name="etrangers[${idx}][sexe]" class="form-select form-select-sm"><option value="H">H</option><option value="F">F</option></select></td>
        <td><input type="number" name="etrangers[${idx}][nombre]" class="form-control form-control-sm text-center" value="0" min="0"></td>
        <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger btn-rm-etranger"><i class="bi bi-trash"></i></button></td>
    </tr>`;
}

document.getElementById('btn-add-etranger')?.addEventListener('click', () => {
    const tbody = document.getElementById('tbody-etrangers');
    const noRow = document.getElementById('row-no-etranger');
    if(noRow) noRow.remove();
    tbody.insertAdjacentHTML('beforeend', makeRow(etrangerIdx++));
    document.querySelectorAll('.btn-rm-etranger').forEach(b => b.addEventListener('click', e => e.target.closest('tr').remove()));
});

document.querySelectorAll('.btn-rm-etranger').forEach(b => {
    b.addEventListener('click', e => e.target.closest('tr').remove());
});
</script>
