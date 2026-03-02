<?php // Vue : Saisie Déclaration Multi-étapes ?>
<?php $ent = $data['entreprise']; ?>

<!-- En-tête fixe -->
<div class="page-header">
    <div>
        <h1 style="font-size:1.1rem">
            <i class="bi bi-pencil-square me-2 text-primary"></i>
            Saisie : <?= e($declaration['code_questionnaire']) ?>
        </h1>
        <p><?= e($declaration['raison_sociale']) ?> — Campagne <?= e($declaration['campagne_libelle']) ?></p>
    </div>
    <div class="page-header-right">
        <span id="autosave-status" class="autosave-indicator"></span>
        <a href="/agent/declarations" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Mes déclarations
        </a>
    </div>
</div>

<!-- Barre de progression -->
<div class="progress-bar-declaration mb-1">
    <div class="fill" style="width:<?= round(($etape / 7) * 100) ?>%"></div>
</div>
<div class="d-flex justify-content-between mb-3">
    <small id="progress-label" class="text-muted">Étape <?= $etape ?>/7 — <?= round(($etape / 7) * 100) ?>%</small>
    <span class="badge-statut badge-<?= e($declaration['statut']) ?>"><?= statutLabel($declaration['statut']) ?></span>
</div>

<!-- Étapes Wizard -->
<div class="wizard-steps">
    <?php
    $etapes = [
        1 => 'Identification',
        2 => 'Effectifs mensuels',
        3 => 'Catégories',
        4 => 'Niveaux instruction',
        5 => 'Formation',
        6 => 'Pertes d\'emploi',
        7 => 'Étrangers',
    ];
    foreach ($etapes as $n => $label):
    ?>
        <?php if ($n > 1): ?><div class="wizard-sep <?= $n <= $etape ? 'done' : '' ?>"></div><?php endif; ?>
        <div class="wizard-step <?= $n === $etape ? 'active' : ($n < $etape ? 'done' : '') ?>" data-step="<?= $n ?>">
            <div class="wizard-step-num">
                <?= $n < $etape ? '<i class="bi bi-check-lg"></i>' : $n ?>
            </div>
            <span class="wizard-step-label d-none d-md-inline"><?= e($label) ?></span>
        </div>
    <?php endforeach; ?>
</div>

<!-- Formulaire principal -->
<form id="wizard-form" data-decl-id="<?= $declaration['id'] ?>" data-etape="<?= $etape ?>">
    <?= csrfField() ?>

    <!-- ======= ÉTAPE 1 : Identification ======= -->
    <div class="form-section <?= $etape == 1 ? 'active' : '' ?>" id="etape-1">
        <div class="card"><div class="card-body">
            <h5 class="section-title">Section I — Identification de l'entreprise</h5>
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">Raison sociale <span class="text-danger">*</span></label>
                    <input type="text" name="raison_sociale" class="form-control" value="<?= e($ent['raison_sociale'] ?? '') ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">N° CNSS</label>
                    <input type="text" name="numero_cnss" class="form-control" value="<?= e($ent['numero_cnss'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nationalité du capital</label>
                    <input type="text" name="nationalite" class="form-control" value="<?= e($ent['nationalite'] ?? 'Nigérienne') ?>" placeholder="Ex: Nigérienne, Française...">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Branche d'activité</label>
                    <select name="branche_id" class="form-select">
                        <option value="">— Choisir —</option>
                        <?php foreach ($data['branches'] as $b): ?>
                        <option value="<?= $b['id'] ?>" <?= $ent['branche_id'] == $b['id'] ? 'selected' : '' ?>>
                            <?= e($b['code']) ?> - <?= e($b['libelle']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Activité principale</label>
                    <input type="text" name="activite_principale" class="form-control" value="<?= e($ent['activite_principale'] ?? '') ?>">
                </div>
                <div class="col-12">
                    <label class="form-label">Activités secondaires</label>
                    <input type="text" name="activites_secondaires" class="form-control" value="<?= e($ent['activites_secondaires'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Localité / Ville</label>
                    <input type="text" name="localite" class="form-control" value="<?= e($ent['localite'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Quartier</label>
                    <input type="text" name="quartier" class="form-control" value="<?= e($ent['quartier'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Boîte postale</label>
                    <input type="text" name="boite_postale" class="form-control" value="<?= e($ent['boite_postale'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Téléphone</label>
                    <input type="text" name="telephone" class="form-control" value="<?= e($ent['ent_tel'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Fax</label>
                    <input type="text" name="fax" class="form-control" value="<?= e($ent['fax'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= e($ent['ent_email'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Département</label>
                    <select name="departement_id" class="form-select" id="select-dept">
                        <option value="">— Choisir —</option>
                        <?php foreach ($data['departements'] as $dept): ?>
                        <option value="<?= $dept['id'] ?>" <?= $ent['departement_id'] == $dept['id'] ? 'selected' : '' ?>>
                            <?= e($dept['nom']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Commune</label>
                    <select name="commune_id" class="form-select" id="select-commune">
                        <option value="">— Choisir —</option>
                        <?php foreach ($data['communes'] as $com): ?>
                        <option value="<?= $com['id'] ?>" <?= $ent['commune_id'] == $com['id'] ? 'selected' : '' ?>>
                            <?= e($com['nom']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Masse salariale <?= e($declaration['annee'] ?? '') ?> (FCFA)</label>
                    <input type="number" name="masse_salariale" class="form-control"
                        value="<?= e($declaration['masse_salariale'] ?? '') ?>"
                        placeholder="Montant en FCFA" min="0" step="1">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nom de l'enquêteur</label>
                    <input type="text" name="nom_enqueteur" class="form-control" value="<?= e($declaration['nom_enqueteur'] ?? '') ?>">
                </div>
            </div>
        </div></div>
        <div class="nav-buttons">
            <span></span>
            <button type="button" class="btn btn-primary btn-next">
                Étape suivante <i class="bi bi-arrow-right ms-1"></i>
            </button>
        </div>
    </div>

    <!-- ======= ÉTAPE 2 : Effectifs mensuels ======= -->
    <div class="form-section <?= $etape == 2 ? 'active' : '' ?>" id="etape-2">
        <div class="card"><div class="card-body">
            <h5 class="section-title">Section II — Renseignements statistiques généraux</h5>
            <p class="text-muted">Indiquez l'effectif actif (présent et payé) pour chaque mois de l'année.</p>
            <div class="table-responsive">
                <table class="table table-bordered table-saisie">
                    <thead><tr>
                        <?php foreach (range(1, 12) as $m): ?>
                        <th class="text-center"><?= nomMois($m) ?></th>
                        <?php endforeach; ?>
                    </tr></thead>
                    <tbody><tr>
                        <?php foreach (range(1, 12) as $m): ?>
                        <td class="text-center">
                            <input type="number" name="effectifs[<?= $m ?>]"
                                class="form-control form-control-sm text-center"
                                value="<?= (int)($data['effectifs_mensuels'][$m] ?? 0) ?>"
                                min="0" style="width:70px">
                        </td>
                        <?php endforeach; ?>
                    </tr></tbody>
                </table>
            </div>
        </div></div>
        <div class="nav-buttons">
            <button type="button" class="btn btn-outline-secondary btn-prev"><i class="bi bi-arrow-left me-1"></i>Précédent</button>
            <button type="button" class="btn btn-primary btn-next">Étape suivante <i class="bi bi-arrow-right ms-1"></i></button>
        </div>
    </div>

    <!-- ======= ÉTAPE 3 : Catégories × Origines ======= -->
    <div class="form-section <?= $etape == 3 ? 'active' : '' ?>" id="etape-3">
        <div class="card"><div class="card-body">
            <h5 class="section-title">Section III.1 — Répartition par catégories (sexe et nationalité)</h5>
            <p class="text-muted">Pour chaque catégorie, indiquez les effectifs par nationalité et sexe.</p>
            <div class="table-responsive">
                <table class="table table-bordered table-saisie">
                    <thead>
                        <tr>
                            <th rowspan="2">Catégorie</th>
                            <th colspan="2" class="text-center">Nigériens</th>
                            <th colspan="2" class="text-center">Africains (hors Niger)</th>
                            <th colspan="2" class="text-center">Autres nationalités</th>
                            <th rowspan="2" class="text-center">Total</th>
                        </tr>
                        <tr>
                            <th class="text-center">H</th><th class="text-center">F</th>
                            <th class="text-center">H</th><th class="text-center">F</th>
                            <th class="text-center">H</th><th class="text-center">F</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (CATEGORIES_PROFESSIONNELLES as $key => $lib):
                            $row = $data['categories'][$key] ?? [];
                        ?>
                        <tr>
                            <td><?= e($lib) ?></td>
                            <td><input type="number" name="categories[<?= $key ?>][nigeriens_h]" class="form-control form-control-sm text-center" value="<?= (int)($row['nigeriens_h'] ?? 0) ?>" min="0" style="width:60px"></td>
                            <td><input type="number" name="categories[<?= $key ?>][nigeriens_f]" class="form-control form-control-sm text-center" value="<?= (int)($row['nigeriens_f'] ?? 0) ?>" min="0" style="width:60px"></td>
                            <td><input type="number" name="categories[<?= $key ?>][africains_h]" class="form-control form-control-sm text-center" value="<?= (int)($row['africains_h'] ?? 0) ?>" min="0" style="width:60px"></td>
                            <td><input type="number" name="categories[<?= $key ?>][africains_f]" class="form-control form-control-sm text-center" value="<?= (int)($row['africains_f'] ?? 0) ?>" min="0" style="width:60px"></td>
                            <td><input type="number" name="categories[<?= $key ?>][autres_nat_h]" class="form-control form-control-sm text-center" value="<?= (int)($row['autres_nat_h'] ?? 0) ?>" min="0" style="width:60px"></td>
                            <td><input type="number" name="categories[<?= $key ?>][autres_nat_f]" class="form-control form-control-sm text-center" value="<?= (int)($row['autres_nat_f'] ?? 0) ?>" min="0" style="width:60px"></td>
                            <td class="text-center total-cell fw-bold">
                                <?= array_sum(array_map(fn($k) => (int)($row[$k] ?? 0), ['nigeriens_h','nigeriens_f','africains_h','africains_f','autres_nat_h','autres_nat_f'])) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div></div>
        <div class="nav-buttons">
            <button type="button" class="btn btn-outline-secondary btn-prev"><i class="bi bi-arrow-left me-1"></i>Précédent</button>
            <button type="button" class="btn btn-primary btn-next">Étape suivante <i class="bi bi-arrow-right ms-1"></i></button>
        </div>
    </div>

    <!-- ======= ÉTAPE 4 : Niveaux d'instruction ======= -->
    <div class="form-section <?= $etape == 4 ? 'active' : '' ?>" id="etape-4">
        <div class="card"><div class="card-body">
            <h5 class="section-title">Section III.2 — Répartition par niveau d'instruction</h5>
            <p class="text-muted">Pour chaque niveau d'instruction, indiquez les effectifs (H/F) par catégorie professionnelle.</p>
            <div class="table-responsive">
                <table class="table table-bordered table-saisie" style="font-size:.78rem">
                    <thead>
                        <tr>
                            <th>Niveau d'instruction</th>
                            <?php foreach (CATEGORIES_PROFESSIONNELLES as $key => $lib): ?>
                            <th colspan="2" class="text-center"><?= e($lib) ?></th>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <th></th>
                            <?php foreach (CATEGORIES_PROFESSIONNELLES as $key => $lib): ?>
                            <th class="text-center">H</th><th class="text-center">F</th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (NIVEAUX_INSTRUCTION as $nivKey => $nivLib): ?>
                        <tr>
                            <td><?= e($nivLib) ?></td>
                            <?php foreach (array_keys(CATEGORIES_PROFESSIONNELLES) as $catKey):
                                $row = $data['niveaux'][$catKey][$nivKey] ?? [];
                            ?>
                            <td><input type="number" name="niveaux[<?= $catKey ?>][<?= $nivKey ?>][h]"
                                class="form-control form-control-sm text-center" value="<?= (int)($row['effectif_h'] ?? 0) ?>" min="0" style="width:50px"></td>
                            <td><input type="number" name="niveaux[<?= $catKey ?>][<?= $nivKey ?>][f]"
                                class="form-control form-control-sm text-center" value="<?= (int)($row['effectif_f'] ?? 0) ?>" min="0" style="width:50px"></td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div></div>
        <div class="nav-buttons">
            <button type="button" class="btn btn-outline-secondary btn-prev"><i class="bi bi-arrow-left me-1"></i>Précédent</button>
            <button type="button" class="btn btn-primary btn-next">Étape suivante <i class="bi bi-arrow-right ms-1"></i></button>
        </div>
    </div>

    <!-- ======= ÉTAPE 5 : Formation professionnelle ======= -->
    <div class="form-section <?= $etape == 5 ? 'active' : '' ?>" id="etape-5">
        <?php $formation = $data['formations'][0] ?? []; ?>
        <div class="card"><div class="card-body">
            <h5 class="section-title">Section III.3 — Formation professionnelle continue</h5>
            <div class="mb-3">
                <label class="form-label">L'entreprise a-t-elle organisé ou financé des formations en <?= e($declaration['annee'] ?? '') ?> ?</label>
                <div class="d-flex gap-4">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="a_eu_formation" id="formation_oui" value="1"
                            <?= ($formation['a_eu_formation'] ?? false) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="formation_oui">Oui</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="a_eu_formation" id="formation_non" value="0"
                            <?= !($formation['a_eu_formation'] ?? false) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="formation_non">Non</label>
                    </div>
                </div>
            </div>
            <div id="formation-details" style="display:<?= ($formation['a_eu_formation'] ?? false) ? 'block' : 'none' ?>">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Qualification visée</label>
                        <input type="text" name="qualification" class="form-control" value="<?= e($formation['qualification'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nature de la formation</label>
                        <input type="text" name="nature_formation" class="form-control" value="<?= e($formation['nature_formation'] ?? '') ?>" placeholder="Ex: Technique, Management...">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Durée</label>
                        <input type="text" name="duree_formation" class="form-control" value="<?= e($formation['duree_formation'] ?? '') ?>" placeholder="Ex: 2 semaines, 40h...">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Effectif Hommes</label>
                        <input type="number" name="formation_h" class="form-control" value="<?= (int)($formation['effectif_h'] ?? 0) ?>" min="0">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Effectif Femmes</label>
                        <input type="number" name="formation_f" class="form-control" value="<?= (int)($formation['effectif_f'] ?? 0) ?>" min="0">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Observations</label>
                        <textarea name="observations" class="form-control" rows="2"><?= e($formation['observations'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
        </div></div>
        <div class="nav-buttons">
            <button type="button" class="btn btn-outline-secondary btn-prev"><i class="bi bi-arrow-left me-1"></i>Précédent</button>
            <button type="button" class="btn btn-primary btn-next">Étape suivante <i class="bi bi-arrow-right ms-1"></i></button>
        </div>
    </div>

    <!-- ======= ÉTAPE 6 : Pertes d'emploi + Perspectives ======= -->
    <div class="form-section <?= $etape == 6 ? 'active' : '' ?>" id="etape-6">
        <div class="card mb-3"><div class="card-body">
            <h5 class="section-title">Section IV — Perte d'emploi</h5>
            <div class="table-responsive">
                <table class="table table-bordered table-saisie">
                    <thead><tr>
                        <th>Motif</th>
                        <th class="text-center">Hommes</th>
                        <th class="text-center">Femmes</th>
                        <th class="text-center">Total</th>
                    </tr></thead>
                    <tbody>
                        <?php foreach (MOTIFS_PERTE_EMPLOI as $motifKey => $motifLib):
                            $perte = $data['pertes'][$motifKey] ?? [];
                        ?>
                        <tr>
                            <td>
                                <?= e($motifLib) ?>
                                <?php if ($motifKey === 'autres'): ?>
                                <br><input type="text" name="pertes[autres][autre_precision]" class="form-control form-control-sm mt-1"
                                    value="<?= e($perte['motif_autre'] ?? '') ?>" placeholder="Préciser...">
                                <?php endif; ?>
                            </td>
                            <td><input type="number" name="pertes[<?= $motifKey ?>][h]" class="form-control form-control-sm text-center"
                                value="<?= (int)($perte['effectif_h'] ?? 0) ?>" min="0" style="width:70px"></td>
                            <td><input type="number" name="pertes[<?= $motifKey ?>][f]" class="form-control form-control-sm text-center"
                                value="<?= (int)($perte['effectif_f'] ?? 0) ?>" min="0" style="width:70px"></td>
                            <td class="text-center total-cell fw-bold">
                                <?= ((int)($perte['effectif_h'] ?? 0)) + ((int)($perte['effectif_f'] ?? 0)) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div></div>

        <div class="card"><div class="card-body">
            <h5 class="section-title">Section V — Perspectives d'emploi</h5>
            <?php $persp = $data['perspective'] ?? []; ?>
            <div class="mb-3">
                <label class="form-label">Perspectives pour l'année prochaine</label>
                <div class="d-flex gap-4">
                    <?php foreach (['hausse' => '↑ Hausse', 'stabilite' => '→ Stabilité', 'baisse' => '↓ Baisse'] as $val => $label): ?>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="perspective" value="<?= $val ?>"
                            id="persp_<?= $val ?>"
                            <?= ($persp['perspective'] ?? '') === $val ? 'checked' : '' ?>>
                        <label class="form-check-label" for="persp_<?= $val ?>"><?= $label ?></label>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Justification</label>
                <textarea name="justification" class="form-control" rows="3" placeholder="Expliquez vos perspectives..."><?= e($persp['justification'] ?? '') ?></textarea>
            </div>
        </div></div>
        <div class="nav-buttons">
            <button type="button" class="btn btn-outline-secondary btn-prev"><i class="bi bi-arrow-left me-1"></i>Précédent</button>
            <button type="button" class="btn btn-primary btn-next">Étape suivante <i class="bi bi-arrow-right ms-1"></i></button>
        </div>
    </div>

    <!-- ======= ÉTAPE 7 : Effectifs étrangers ======= -->
    <div class="form-section <?= $etape == 7 ? 'active' : '' ?>" id="etape-7">
        <div class="card"><div class="card-body">
            <h5 class="section-title">Section VI — Effectifs étrangers par nationalité</h5>
            <p class="text-muted">Listez les travailleurs étrangers par pays d'origine, qualification, fonction et sexe.</p>
            <div class="table-responsive">
                <table class="table table-bordered table-saisie" id="table-etrangers">
                    <thead><tr>
                        <th>Pays d'origine</th>
                        <th>Qualification</th>
                        <th>Fonction</th>
                        <th>Sexe</th>
                        <th>Nombre</th>
                        <th></th>
                    </tr></thead>
                    <tbody id="etrangers-tbody">
                        <?php foreach ($data['etrangers'] as $i => $et): ?>
                        <tr class="etrangers-row">
                            <td><input type="text" class="form-control form-control-sm" name="etrangers[<?= $i ?>][pays]" value="<?= e($et['pays']) ?>"></td>
                            <td><input type="text" class="form-control form-control-sm" name="etrangers[<?= $i ?>][qualification]" value="<?= e($et['qualification'] ?? '') ?>"></td>
                            <td><input type="text" class="form-control form-control-sm" name="etrangers[<?= $i ?>][fonction]" value="<?= e($et['fonction'] ?? '') ?>"></td>
                            <td>
                                <select class="form-select form-select-sm" name="etrangers[<?= $i ?>][sexe]">
                                    <option value="H" <?= ($et['sexe'] ?? 'H') === 'H' ? 'selected' : '' ?>>Homme</option>
                                    <option value="F" <?= ($et['sexe'] ?? '') === 'F' ? 'selected' : '' ?>>Femme</option>
                                </select>
                            </td>
                            <td><input type="number" class="form-control form-control-sm" name="etrangers[<?= $i ?>][nombre]" value="<?= (int)($et['nombre'] ?? 0) ?>" min="0"></td>
                            <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove()">×</button></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($data['etrangers'])): ?>
                        <tr id="etrangers-empty"><td colspan="6" class="text-center text-muted py-3">
                            Aucun travailleur étranger (cliquez "Ajouter" si applicable)
                        </td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <button type="button" class="btn btn-outline-primary btn-sm" id="btn-add-etranger">
                <i class="bi bi-plus-lg me-1"></i>Ajouter un travailleur étranger
            </button>
        </div></div>

        <!-- Récapitulatif avant soumission -->
        <div class="card mt-3 border-success">
            <div class="card-header" style="background:#e8f5e9">
                <h5 class="text-success"><i class="bi bi-check-circle me-2"></i>Récapitulatif et soumission</h5>
            </div>
            <div class="card-body">
                <p>Vous avez complété toutes les sections du formulaire.</p>
                <ul class="text-muted" style="font-size:.84rem">
                    <li>Vérifiez bien toutes les informations saisies avant de soumettre.</li>
                    <li>Une fois soumise, la déclaration sera transmise à l'ANPE pour validation.</li>
                    <li>Vous recevrez une notification de la décision.</li>
                </ul>
                <div class="d-flex gap-2 mt-3">
                    <button type="button" class="btn btn-outline-secondary btn-save">
                        <i class="bi bi-floppy me-1"></i>Sauvegarder
                    </button>
                    <button type="button" class="btn btn-success btn-submit">
                        <i class="bi bi-send me-1"></i>Soumettre à l'ANPE
                    </button>
                </div>
            </div>
        </div>

        <div class="nav-buttons">
            <button type="button" class="btn btn-outline-secondary btn-prev"><i class="bi bi-arrow-left me-1"></i>Précédent</button>
            <span></span>
        </div>
    </div>
</form>

<script>
// Chargement dynamique des communes par département
document.getElementById('select-dept')?.addEventListener('change', function() {
    const deptId = this.value;
    const select = document.getElementById('select-commune');
    if (!deptId) { select.innerHTML = '<option value="">— Choisir —</option>'; return; }
    fetch(`/api/communes/${deptId}`)
        .then(r => r.json())
        .then(data => {
            select.innerHTML = '<option value="">— Choisir —</option>';
            (data.communes ?? []).forEach(c => {
                select.innerHTML += `<option value="${c.id}">${c.nom}</option>`;
            });
        });
});
</script>
