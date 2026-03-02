<?php /** @var int $region_id */ ?>

<div class="container-fluid py-4">
  <div class="row justify-content-center">
    <div class="col-lg-8">

      <div class="d-flex align-items-center mb-4">
        <div>
          <h2 class="mb-0"><i class="fas fa-file-csv text-success me-2"></i>Import CSV — Entreprises</h2>
          <p class="text-muted mb-0">Importer vos entreprises depuis un fichier CSV</p>
        </div>
        <div class="ms-auto">
          <a href="/agent/entreprises" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Retour
          </a>
        </div>
      </div>

      <div class="alert alert-info">
        <h6 class="alert-heading"><i class="fas fa-info-circle me-1"></i>Format CSV attendu</h6>
        <p class="mb-2">Colonnes dans l'ordre :</p>
        <ol class="mb-1">
          <li><strong>raison_sociale</strong> (obligatoire)</li>
          <li>numero_cnss</li>
          <li>telephone</li>
          <li>email</li>
          <li>activite_principale</li>
          <li>nationalite (défaut : Nigérienne)</li>
          <li>localite</li>
        </ol>
        <small>Les doublons (même N° CNSS) seront automatiquement ignorés.</small>
      </div>

      <div class="mb-4">
        <a href="data:text/csv;charset=UTF-8,%EF%BB%BF<?= urlencode("raison_sociale;numero_cnss;telephone;email;activite_principale;nationalite;localite\nExemple SARL;CNSS-2025-XXX;+227 XX XX XX XX;contact@exemple.ne;Commerce;Nigérienne;Niamey\n") ?>"
           download="modele_entreprises.csv"
           class="btn btn-outline-success btn-sm">
          <i class="fas fa-download me-1"></i>Télécharger le modèle CSV
        </a>
      </div>

      <div class="card shadow-sm">
        <div class="card-header bg-white">
          <h5 class="mb-0"><i class="fas fa-upload me-2 text-primary"></i>Importer les entreprises</h5>
        </div>
        <div class="card-body">
          <form method="POST" action="/agent/import/entreprises" enctype="multipart/form-data" id="importForm">
            <?= csrfField() ?>

            <div class="alert alert-secondary py-2">
              <i class="fas fa-map-marker-alt me-1"></i>
              Les entreprises seront assignées à <strong>votre région</strong>.
            </div>

            <div class="mb-3">
              <label for="csv_file" class="form-label fw-semibold">
                Fichier CSV <span class="text-danger">*</span>
              </label>
              <input type="file" name="csv_file" id="csv_file" class="form-control"
                     accept=".csv,text/csv" required>
            </div>

            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <label for="delimiter" class="form-label fw-semibold">Séparateur</label>
                <select name="delimiter" id="delimiter" class="form-select">
                  <option value=";">Point-virgule (;)</option>
                  <option value=",">, Virgule (,)</option>
                </select>
              </div>
              <div class="col-md-6 d-flex align-items-end">
                <div class="form-check mb-2">
                  <input type="checkbox" name="skip_header" id="skip_header" class="form-check-input" value="1" checked>
                  <label for="skip_header" class="form-check-label">Ignorer la ligne d'en-tête</label>
                </div>
              </div>
            </div>

            <div id="filePreview" class="d-none mb-3">
              <div class="alert alert-light border">
                <i class="fas fa-file me-1"></i>
                <span id="fileName" class="text-primary"></span>
                <span id="fileSize" class="text-muted ms-2"></span>
              </div>
            </div>

            <div class="d-flex gap-2">
              <button type="submit" class="btn btn-success">
                <i class="fas fa-file-import me-2"></i>Importer
              </button>
              <a href="/agent/entreprises" class="btn btn-outline-secondary">Annuler</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.getElementById('csv_file').addEventListener('change', function() {
  const file = this.files[0];
  if (file) {
    document.getElementById('fileName').textContent = file.name;
    document.getElementById('fileSize').textContent = '(' + (file.size / 1024).toFixed(1) + ' Ko)';
    document.getElementById('filePreview').classList.remove('d-none');
  }
});
document.getElementById('importForm').addEventListener('submit', function() {
  const btn = this.querySelector('button[type=submit]');
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Import en cours...';
});
</script>
