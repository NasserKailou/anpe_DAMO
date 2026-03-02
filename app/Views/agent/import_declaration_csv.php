<?php
/**
 * Vue : Import CSV d'une déclaration RAMO
 * Permet d'importer les données de chaque section depuis un fichier CSV
 */
if (!defined('EDAMO')) exit;

$declaration = $declaration ?? [];
$decId       = $declaration['id'] ?? 0;
$code        = $declaration['code_questionnaire'] ?? '';

// Templates CSV téléchargeables par section
$templates = [
    'effectifs_mensuels' => [
        'label'   => 'II — Effectifs mensuels',
        'icon'    => 'bi-calendar3',
        'color'   => 'primary',
        'header'  => 'mois,effectif',
        'exemple' => "1,245\n2,230\n3,260\n...\n12,280",
        'note'    => 'mois = 1 à 12, effectif = nombre d\'employés au dernier jour du mois',
    ],
    'categories' => [
        'label'   => 'III.1 — Catégories / Origines / Sexe',
        'icon'    => 'bi-people',
        'color'   => 'success',
        'header'  => 'categorie,nigeriens_h,nigeriens_f,africains_h,africains_f,autres_nat_h,autres_nat_f',
        'exemple' => "cadres_superieurs,5,2,1,0,0,0\nagents_maitrise,12,3,0,0,0,0\nemployes_bureau,8,15,0,1,0,0\nouvriers_qualifies,20,2,2,0,1,0\nouvriers_specialises,30,5,0,0,0,0\nmanoeuvres,15,1,0,0,0,0\napprentis_stagiaires,4,2,0,0,0,0",
        'note'    => 'Valeurs de catégorie : cadres_superieurs | agents_maitrise | employes_bureau | ouvriers_qualifies | ouvriers_specialises | manoeuvres | apprentis_stagiaires',
    ],
    'niveaux' => [
        'label'   => 'III.2 — Niveaux d\'instruction',
        'icon'    => 'bi-mortarboard',
        'color'   => 'info',
        'header'  => 'categorie,niveau,effectif_h,effectif_f',
        'exemple' => "cadres_superieurs,superieur_3,3,1\ncadres_superieurs,superieur_2,2,1\nagents_maitrise,secondaire_2,8,2",
        'note'    => 'niveaux : non_scolarise | primaire | secondaire_1 | secondaire_2 | moyen_pro | superieur_pro | superieur_1 | superieur_2 | superieur_3',
    ],
    'formations' => [
        'label'   => 'III.3 — Formation professionnelle',
        'icon'    => 'bi-award',
        'color'   => 'warning',
        'header'  => 'qualification,nature_formation,duree_formation,effectif_h,effectif_f',
        'exemple' => "Informatique,Recyclage,3 mois,5,2\nSécurité,Initiation,1 semaine,10,3",
        'note'    => 'Une ligne par session de formation. Colonnes texte libres.',
    ],
    'pertes' => [
        'label'   => 'IV — Pertes d\'emploi',
        'icon'    => 'bi-person-dash',
        'color'   => 'danger',
        'header'  => 'motif,effectif_h,effectif_f,motif_autre',
        'exemple' => "licenciement,3,1,\ndemission,2,0,\nfin_contrat,5,2,\nretraite,1,0,\ndeces,0,0,\nautres,1,0,Rupture conventionnelle",
        'note'    => 'motifs : licenciement | demission | fin_contrat | retraite | deces | autres. Renseignez motif_autre uniquement pour "autres".',
    ],
    'etrangers' => [
        'label'   => 'VI — Effectifs étrangers',
        'icon'    => 'bi-globe',
        'color'   => 'secondary',
        'header'  => 'pays,qualification,fonction,sexe,nombre',
        'exemple' => "France,Ingénieur,Directeur technique,H,2\nChine,Technicien,Chef de chantier,H,5\nFrance,Comptable,Directrice financière,F,1",
        'note'    => 'sexe = H ou F. Une ligne par nationalité/fonction.',
    ],
];
?>

<div class="row justify-content-center">
    <div class="col-xl-10">

        <!-- En-tête -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                        <i class="bi bi-file-earmark-spreadsheet text-primary fs-3"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 fw-bold">Import CSV — Déclaration</h5>
                        <div class="text-muted small">
                            <i class="bi bi-file-earmark-text me-1"></i><?= e($code) ?>
                            &nbsp;|&nbsp; Importez les données de chaque section à partir d'un fichier CSV
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Instructions générales -->
        <div class="alert alert-info mb-4">
            <h6 class="fw-bold"><i class="bi bi-info-circle me-2"></i>Comment utiliser l'import CSV</h6>
            <ol class="mb-0 small">
                <li>Choisissez la section à importer dans la liste déroulante ci-dessous.</li>
                <li>Téléchargez le <strong>modèle CSV</strong> correspondant pour voir le format attendu.</li>
                <li>Remplissez le fichier CSV avec vos données (séparateur virgule <code>,</code> ou point-virgule <code>;</code>).</li>
                <li>Sélectionnez votre fichier et cliquez sur <strong>Importer</strong>.</li>
                <li>Les données importées <strong>remplaceront</strong> les données existantes pour la section choisie.</li>
            </ol>
        </div>

        <!-- Formulaire d'import -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-bold"><i class="bi bi-upload me-2 text-primary"></i>Importer une section</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= url("agent/declaration/$decId/import-csv") ?>" enctype="multipart/form-data" id="form-import-csv">
                    <?= csrfField() ?>

                    <div class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label fw-semibold">Section à importer <span class="text-danger">*</span></label>
                            <select name="section" class="form-select" id="sel-section" required>
                                <option value="">-- Sélectionner une section --</option>
                                <?php foreach ($templates as $key => $tpl): ?>
                                    <option value="<?= $key ?>"><?= $tpl['label'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-5">
                            <label class="form-label fw-semibold">Fichier CSV <span class="text-danger">*</span></label>
                            <input type="file" name="fichier_csv" class="form-control" id="input-csv"
                                   accept=".csv,.txt" required>
                            <div class="form-text">Formats acceptés : .csv, .txt — Encodage UTF-8 recommandé</div>
                        </div>

                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100" id="btn-import">
                                <i class="bi bi-upload me-1"></i>Importer
                            </button>
                        </div>
                    </div>

                    <!-- Prévisualisation dynamique du format -->
                    <div id="format-preview" class="mt-3" style="display:none">
                        <div class="p-3 bg-light rounded border">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <strong class="small text-primary" id="preview-title"></strong>
                                <button type="button" class="btn btn-sm btn-outline-success" id="btn-download-template">
                                    <i class="bi bi-download me-1"></i>Télécharger le modèle
                                </button>
                            </div>
                            <div class="mb-2">
                                <small class="text-muted fw-semibold">Format attendu (1ère ligne = en-tête) :</small>
                                <pre class="bg-white border rounded p-2 mb-0 small" id="preview-header" style="font-size:.78rem"></pre>
                            </div>
                            <div class="mb-2">
                                <small class="text-muted fw-semibold">Exemple de données :</small>
                                <pre class="bg-white border rounded p-2 mb-0 small" id="preview-exemple" style="font-size:.78rem"></pre>
                            </div>
                            <small class="text-muted fst-italic" id="preview-note"></small>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Référence des sections -->
        <div class="row g-3 mb-4">
            <?php foreach ($templates as $key => $tpl): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body py-3">
                            <div class="d-flex align-items-start gap-2 mb-2">
                                <span class="badge bg-<?= $tpl['color'] ?> bg-opacity-15 text-<?= $tpl['color'] ?> p-2">
                                    <i class="bi <?= $tpl['icon'] ?> fs-5"></i>
                                </span>
                                <div>
                                    <div class="fw-semibold small"><?= $tpl['label'] ?></div>
                                    <code class="small text-muted"><?= $tpl['header'] ?></code>
                                </div>
                            </div>
                            <div class="small text-muted"><?= $tpl['note'] ?></div>
                            <button type="button" class="btn btn-sm btn-outline-<?= $tpl['color'] ?> mt-2 btn-dl-tpl"
                                    data-section="<?= $key ?>"
                                    data-header="<?= e($tpl['header']) ?>"
                                    data-exemple="<?= e($tpl['exemple']) ?>">
                                <i class="bi bi-download me-1"></i>Télécharger modèle
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Bouton retour -->
        <div class="d-flex gap-2 mb-4">
            <a href="<?= url("agent/declaration/$decId/saisie") ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Retour à la saisie
            </a>
            <a href="<?= url("agent/declarations") ?>" class="btn btn-outline-primary">
                <i class="bi bi-list-ul me-1"></i>Mes déclarations
            </a>
        </div>

    </div>
</div>

<script>
// Données des templates
const TEMPLATES = <?= json_encode($templates, JSON_HEX_TAG) ?>;

// Sélection de section → afficher le format
document.getElementById('sel-section')?.addEventListener('change', function() {
    const key = this.value;
    const preview = document.getElementById('format-preview');
    if (!key || !TEMPLATES[key]) {
        preview.style.display = 'none';
        return;
    }
    const tpl = TEMPLATES[key];
    document.getElementById('preview-title').textContent = tpl.label;
    document.getElementById('preview-header').textContent = tpl.header;
    document.getElementById('preview-exemple').textContent = tpl.exemple;
    document.getElementById('preview-note').textContent = tpl.note;
    preview.style.display = '';

    // Bouton télécharger dans le preview
    document.getElementById('btn-download-template').onclick = () => downloadCSV(key, tpl.header, tpl.exemple);
});

// Boutons télécharger modèle
document.querySelectorAll('.btn-dl-tpl').forEach(btn => {
    btn.addEventListener('click', () => {
        const key     = btn.dataset.section;
        const header  = btn.dataset.header;
        const exemple = btn.dataset.exemple;
        downloadCSV(key, header, exemple);
    });
});

function downloadCSV(key, header, exemple) {
    const content = header + '\n' + exemple + '\n';
    const blob    = new Blob(['\ufeff' + content], {type: 'text/csv;charset=utf-8;'});
    const link    = document.createElement('a');
    link.href     = URL.createObjectURL(blob);
    link.download = `modele_ramo_${key}.csv`;
    link.click();
    URL.revokeObjectURL(link.href);
}

// Confirmation avant import
document.getElementById('form-import-csv')?.addEventListener('submit', function(e) {
    const section = document.getElementById('sel-section').value;
    const file    = document.getElementById('input-csv').files[0];
    if (!section) { e.preventDefault(); alert('Sélectionnez une section.'); return; }
    if (!file) { e.preventDefault(); alert('Sélectionnez un fichier CSV.'); return; }
    if (!confirm(`Importer "${file.name}" pour la section "${section}" ?\n\nAttention : les données existantes pour cette section seront remplacées.`)) {
        e.preventDefault();
    }
});

// Feedback visuel lors de la soumission
document.getElementById('form-import-csv')?.addEventListener('submit', () => {
    const btn = document.getElementById('btn-import');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Import en cours…';
    }
});
</script>
