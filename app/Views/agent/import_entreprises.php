<?php
/**
 * Formulaire d'import CSV des entreprises (agent)
 */
?>

<div class="row justify-content-center">
    <div class="col-lg-8">

        <!-- Aide CSV -->
        <div class="card border-0 shadow-sm border-start border-4 border-info mb-3">
            <div class="card-body">
                <h6 class="fw-semibold text-info"><i class="bi bi-info-circle me-2"></i>Format du fichier CSV attendu</h6>
                <p class="text-muted small mb-2">Le fichier doit contenir les colonnes dans l'ordre suivant (séparateur : <code>;</code> ou <code>,</code>) :</p>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-2">
                        <thead class="table-light">
                            <tr>
                                <th>Col. 1</th><th>Col. 2</th><th>Col. 3</th>
                                <th>Col. 4</th><th>Col. 5</th><th>Col. 6</th><th>Col. 7</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="table-info">
                                <td><strong>Raison sociale</strong> <span class="text-danger">*</span></td>
                                <td>N° CNSS</td>
                                <td>Téléphone</td>
                                <td>Email</td>
                                <td>Activité principale</td>
                                <td>Nationalité</td>
                                <td>Localité</td>
                            </tr>
                            <tr class="text-muted small">
                                <td>SONITRAV SA</td>
                                <td>NE-001234</td>
                                <td>+227 20 XX XX</td>
                                <td>contact@ent.ne</td>
                                <td>BTP</td>
                                <td>Nigérienne</td>
                                <td>Niamey</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="d-flex gap-3 flex-wrap">
                    <div class="text-muted small"><i class="bi bi-check-circle text-success me-1"></i>La colonne "Raison sociale" est obligatoire</div>
                    <div class="text-muted small"><i class="bi bi-check-circle text-success me-1"></i>Les doublons CNSS seront ignorés</div>
                    <div class="text-muted small"><i class="bi bi-check-circle text-success me-1"></i>La 1ère ligne (en-tête) est ignorée par défaut</div>
                </div>
            </div>
        </div>

        <!-- Formulaire d'upload -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-upload me-2 text-primary"></i>Importer un fichier CSV</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= url('agent/import/entreprises') ?>"
                      enctype="multipart/form-data" id="form-import">
                    <?= csrfField() ?>

                    <!-- Zone de dépôt -->
                    <div class="border-2 border-dashed rounded p-4 text-center mb-3"
                         id="drop-zone"
                         style="border: 2px dashed #dee2e6; cursor: pointer;"
                         onclick="document.getElementById('csv_file').click()">
                        <i class="bi bi-cloud-upload text-primary" style="font-size: 2.5rem"></i>
                        <div class="mt-2 fw-semibold">Glissez votre fichier CSV ici</div>
                        <div class="text-muted small">ou cliquez pour parcourir</div>
                        <div id="file-name" class="mt-2 text-primary fw-semibold d-none"></div>
                        <input type="file" id="csv_file" name="csv_file"
                               accept=".csv,.txt" class="d-none" required>
                    </div>

                    <!-- Options -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Séparateur de colonnes</label>
                            <select name="delimiter" class="form-select">
                                <option value=";">Point-virgule ( ; ) — recommandé</option>
                                <option value=",">Virgule ( , )</option>
                                <option value="&#9;">Tabulation (TAB)</option>
                                <option value="|">Pipe ( | )</option>
                            </select>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox"
                                       name="skip_header" id="skip_header" value="1" checked>
                                <label class="form-check-label" for="skip_header">
                                    Ignorer la première ligne (en-tête)
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Aperçu (optionnel JS) -->
                    <div id="preview-section" class="d-none mb-3">
                        <h6 class="fw-semibold text-muted small text-uppercase">Aperçu des premières lignes</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered" id="preview-table"></table>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="d-flex justify-content-end gap-2 border-top pt-3">
                        <a href="<?= url('agent/entreprises') ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-x-lg me-1"></i>Annuler
                        </a>
                        <button type="submit" class="btn btn-primary" id="btn-import">
                            <i class="bi bi-upload me-1"></i>Lancer l'import
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Instructions supplémentaires -->
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-body">
                <h6 class="fw-semibold"><i class="bi bi-download me-2 text-secondary"></i>Télécharger un modèle</h6>
                <p class="text-muted small mb-2">Vous pouvez télécharger un fichier CSV modèle avec le bon format :</p>
                <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-download-tpl">
                    <i class="bi bi-file-earmark-arrow-down me-1"></i>Télécharger le modèle CSV
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Drag & drop
const dropZone = document.getElementById('drop-zone');
const fileInput = document.getElementById('csv_file');
const fileNameEl = document.getElementById('file-name');

fileInput.addEventListener('change', function() {
    if (this.files[0]) {
        fileNameEl.textContent = this.files[0].name;
        fileNameEl.classList.remove('d-none');
        dropZone.style.borderColor = '#0d6efd';
        dropZone.style.background = '#f0f4ff';
        previewCsv(this.files[0]);
    }
});

dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.style.borderColor = '#0d6efd'; });
dropZone.addEventListener('dragleave', () => { dropZone.style.borderColor = '#dee2e6'; });
dropZone.addEventListener('drop', e => {
    e.preventDefault();
    const f = e.dataTransfer.files[0];
    if (f) {
        fileInput.files = e.dataTransfer.files;
        fileNameEl.textContent = f.name;
        fileNameEl.classList.remove('d-none');
        previewCsv(f);
    }
});

function previewCsv(file) {
    const reader = new FileReader();
    reader.onload = function(e) {
        const lines = e.target.result.split('\n').slice(0, 6);
        const delim = document.querySelector('[name=delimiter]').value || ';';
        const table = document.getElementById('preview-table');
        table.innerHTML = '';
        lines.forEach((line, i) => {
            if (!line.trim()) return;
            const cells = line.split(delim);
            const tr = document.createElement('tr');
            tr.className = i === 0 ? 'table-light fw-semibold' : '';
            cells.forEach(c => {
                const td = document.createElement(i === 0 ? 'th' : 'td');
                td.textContent = c.replace(/"/g,'').trim();
                td.style.fontSize = '.8rem';
                tr.appendChild(td);
            });
            table.appendChild(tr);
        });
        document.getElementById('preview-section').classList.remove('d-none');
    };
    reader.readAsText(file);
}

// Soumission avec loader
document.getElementById('form-import').addEventListener('submit', function() {
    const btn = document.getElementById('btn-import');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Import en cours…';
});

// Télécharger le modèle
document.getElementById('btn-download-tpl').addEventListener('click', function() {
    const content = 'Raison sociale;N° CNSS;Téléphone;Email;Activité principale;Nationalité;Localité\n'
        + 'SONITRAV SA;NE-001234;+227 20 00 00 00;contact@sonitrav.ne;Bâtiment et Travaux Publics;Nigérienne;Niamey\n'
        + 'NIGER IMPORT;NE-005678;+227 20 11 22 33;;Commerce;Nigérienne;Agadez\n';
    const blob = new Blob(['\ufeff' + content], {type: 'text/csv;charset=utf-8;'});
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'modele_import_entreprises.csv';
    a.click();
    URL.revokeObjectURL(url);
});
</script>
