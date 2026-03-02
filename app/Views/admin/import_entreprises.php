<?php /** @var array $regions */ ?>

<div class="container-fluid py-4">
  <div class="row justify-content-center">
    <div class="col-lg-8">

      <!-- En-tête -->
      <div class="d-flex align-items-center mb-4">
        <div>
          <h2 class="mb-0"><i class="fas fa-file-csv text-success me-2"></i>Import CSV — Entreprises</h2>
          <p class="text-muted mb-0">Importer une liste d'entreprises depuis un fichier CSV</p>
        </div>
        <div class="ms-auto">
          <a href="/admin/utilisateurs" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Retour
          </a>
        </div>
      </div>

      <!-- Instructions -->
      <div class="alert alert-info">
        <h6 class="alert-heading"><i class="fas fa-info-circle me-1"></i>Format attendu du fichier CSV</h6>
        <p class="mb-2">Le fichier CSV doit contenir les colonnes suivantes (dans l'ordre) :</p>
        <ol class="mb-2">
          <li><strong>raison_sociale</strong> (obligatoire)</li>
          <li><strong>numero_cnss</strong></li>
          <li><strong>telephone</strong></li>
          <li><strong>email</strong></li>
          <li><strong>activite_principale</strong></li>
          <li><strong>nationalite</strong> (défaut : Nigérienne)</li>
          <li><strong>localite</strong></li>
        </ol>
        <small class="text-muted">Les entreprises dont le N° CNSS existe déjà seront ignorées (anti-doublon).</small>
      </div>

      <!-- Modèle CSV à télécharger -->
      <div class="mb-4">
        <a href="data:text/csv;charset=UTF-8,%EF%BB%BF<?= urlencode("raison_sociale;numero_cnss;telephone;email;activite_principale;nationalite;localite\nSOPAMIN;CNSS-2025-001;+227 20 73 00 01;contact@sopamin.ne;Mines;Nigérienne;Agadez\n") ?>"
           download="modele_import_entreprises.csv"
           class="btn btn-outline-success btn-sm">
          <i class="fas fa-download me-1"></i>Télécharger le modèle CSV
        </a>
      </div>

      <!-- Formulaire d'import -->
      <div class="card shadow-sm">
        <div class="card-header bg-white">
          <h5 class="mb-0"><i class="fas fa-upload me-2 text-primary"></i>Uploader le fichier</h5>
        </div>
        <div class="card-body">
          <form method="POST" action="/admin/import/entreprises" enctype="multipart/form-data" id="importForm">
            <?= csrfField() ?>

            <div class="mb-3">
              <label for="region_id" class="form-label fw-semibold">
                Région <span class="text-danger">*</span>
              </label>
              <select name="region_id" id="region_id" class="form-select" required>
                <option value="">-- Sélectionner une région --</option>
                <?php foreach ($regions as $r): ?>
                  <option value="<?= $r['id'] ?>"><?= e($r['nom']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="mb-3">
              <label for="csv_file" class="form-label fw-semibold">
                Fichier CSV <span class="text-danger">*</span>
              </label>
              <input type="file" name="csv_file" id="csv_file" class="form-control"
                     accept=".csv,text/csv" required>
              <div class="form-text">Taille maximale : 5 Mo — Encodage UTF-8 recommandé</div>
            </div>

            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <label for="delimiter" class="form-label fw-semibold">Séparateur</label>
                <select name="delimiter" id="delimiter" class="form-select">
                  <option value=";">Point-virgule (;) — défaut Excel FR</option>
                  <option value=",">, Virgule (,)</option>
                  <option value="&#9;">Tabulation (TAB)</option>
                </select>
              </div>
              <div class="col-md-6 d-flex align-items-end">
                <div class="form-check mb-2">
                  <input type="checkbox" name="skip_header" id="skip_header" class="form-check-input" value="1" checked>
                  <label for="skip_header" class="form-check-label">
                    Ignorer la première ligne (en-tête)
                  </label>
                </div>
              </div>
            </div>

            <!-- Aperçu fichier -->
            <div id="filePreview" class="d-none mb-3">
              <div class="alert alert-light border">
                <strong><i class="fas fa-eye me-1"></i>Aperçu :</strong>
                <span id="fileName" class="ms-2 text-primary"></span>
                <span id="fileSize" class="ms-2 text-muted"></span>
              </div>
            </div>

            <div class="d-flex gap-2">
              <button type="submit" class="btn btn-success">
                <i class="fas fa-file-import me-2"></i>Lancer l'import
              </button>
              <a href="/admin/utilisateurs" class="btn btn-outline-secondary">Annuler</a>
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

document.getElementById('importForm').addEventListener('submit', function(e) {
  const btn = this.querySelector('button[type=submit]');
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Import en cours...';
});
</script>
