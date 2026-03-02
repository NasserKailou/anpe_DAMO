<?php
/**
 * Formulaire entreprise (création / modification) - agent
 */
$entreprise   = $entreprise   ?? [];
$branches     = $branches     ?? [];
$departements = $departements ?? [];
$communes     = $communes     ?? [];
$mode         = $mode         ?? 'create';
$region       = $region       ?? null;

$isEdit  = $mode === 'edit';
$action  = $isEdit
    ? url("agent/entreprise/{$entreprise['id']}/update")
    : url('agent/entreprise/creer');
$pageTitle = $isEdit ? 'Modifier l\'entreprise' : 'Nouvelle entreprise';

$nationalites = ['Nigérienne','Française','Américaine','Britannique','Chinoise','Libanaise','Togolaise',
                 'Béninoise','Burkinabè','Malienne','Sénégalaise','Ivoirienne','Autre'];
?>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-building text-primary fs-5"></i>
                    <h6 class="mb-0 fw-semibold"><?= $pageTitle ?></h6>
                    <?php if ($region): ?>
                        <span class="badge bg-secondary ms-auto"><i class="bi bi-geo-alt me-1"></i><?= e($region['nom'] ?? '') ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= $action ?>">
                    <?= csrfField() ?>

                    <!-- Informations générales -->
                    <h6 class="text-muted text-uppercase small letter-spacing mb-3 mt-1">
                        <i class="bi bi-info-circle me-1"></i>Informations générales
                    </h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Raison sociale <span class="text-danger">*</span></label>
                            <input type="text" name="raison_sociale" class="form-control" required
                                   value="<?= e($entreprise['raison_sociale'] ?? '') ?>"
                                   placeholder="Dénomination officielle de l'entreprise">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">N° CNSS</label>
                            <input type="text" name="numero_cnss" class="form-control font-monospace"
                                   value="<?= e($entreprise['numero_cnss'] ?? '') ?>"
                                   placeholder="Ex: NE-123456">
                            <div class="form-text">Doit être unique dans la région</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Nationalité</label>
                            <select name="nationalite" class="form-select">
                                <?php foreach ($nationalites as $nat): ?>
                                    <option <?= ($entreprise['nationalite']??'Nigérienne') === $nat ? 'selected' : '' ?>><?= $nat ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Branche d'activité</label>
                            <select name="branche_id" class="form-select">
                                <option value="">-- Sélectionner une branche --</option>
                                <?php foreach ($branches as $b): ?>
                                    <option value="<?= $b['id'] ?>"
                                            <?= ($entreprise['branche_id']??'') == $b['id'] ? 'selected' : '' ?>>
                                        <?= e($b['code']) ?> – <?= e($b['libelle']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Activité principale</label>
                            <input type="text" name="activite_principale" class="form-control"
                                   value="<?= e($entreprise['activite_principale'] ?? '') ?>"
                                   placeholder="Description de l'activité principale">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Activités secondaires</label>
                            <input type="text" name="activites_secondaires" class="form-control"
                                   value="<?= e($entreprise['activites_secondaires'] ?? '') ?>"
                                   placeholder="Activités secondaires (optionnel)">
                        </div>
                    </div>

                    <hr>

                    <!-- Localisation -->
                    <h6 class="text-muted text-uppercase small letter-spacing mb-3">
                        <i class="bi bi-geo-alt me-1"></i>Localisation
                    </h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Département</label>
                            <select name="departement_id" class="form-select" id="sel-dept">
                                <option value="">-- Département --</option>
                                <?php foreach ($departements as $d): ?>
                                    <option value="<?= $d['id'] ?>"
                                            <?= ($entreprise['departement_id']??'') == $d['id'] ? 'selected' : '' ?>>
                                        <?= e($d['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Commune</label>
                            <select name="commune_id" class="form-select" id="sel-commune">
                                <option value="">-- Commune --</option>
                                <?php foreach ($communes as $c): ?>
                                    <option value="<?= $c['id'] ?>"
                                            <?= ($entreprise['commune_id']??'') == $c['id'] ? 'selected' : '' ?>>
                                        <?= e($c['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Localité / Ville</label>
                            <input type="text" name="localite" class="form-control"
                                   value="<?= e($entreprise['localite'] ?? '') ?>"
                                   placeholder="Ville ou localité">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Quartier</label>
                            <input type="text" name="quartier" class="form-control"
                                   value="<?= e($entreprise['quartier'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Boîte postale</label>
                            <input type="text" name="boite_postale" class="form-control"
                                   value="<?= e($entreprise['boite_postale'] ?? '') ?>"
                                   placeholder="BP 1234">
                        </div>
                    </div>

                    <hr>

                    <!-- Coordonnées -->
                    <h6 class="text-muted text-uppercase small letter-spacing mb-3">
                        <i class="bi bi-telephone me-1"></i>Coordonnées
                    </h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Téléphone</label>
                            <input type="text" name="telephone" class="form-control"
                                   value="<?= e($entreprise['telephone'] ?? '') ?>"
                                   placeholder="+227 20 XX XX XX">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Fax</label>
                            <input type="text" name="fax" class="form-control"
                                   value="<?= e($entreprise['fax'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" name="email" class="form-control"
                                   value="<?= e($entreprise['email'] ?? '') ?>"
                                   placeholder="contact@entreprise.ne">
                        </div>
                    </div>

                    <!-- Boutons -->
                    <div class="d-flex justify-content-end gap-2 border-top pt-3">
                        <a href="<?= url('agent/entreprises') ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-x-lg me-1"></i>Annuler
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-<?= $isEdit ? 'floppy' : 'plus-lg' ?> me-1"></i>
                            <?= $isEdit ? 'Enregistrer les modifications' : 'Créer l\'entreprise' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Chargement dynamique des communes selon le département sélectionné
const BASE_URL = '<?= BASE_PATH ?>';
document.getElementById('sel-dept')?.addEventListener('change', function() {
    const deptId = this.value;
    const selCommune = document.getElementById('sel-commune');
    if (!selCommune) return;
    selCommune.innerHTML = '<option value="">Chargement…</option>';
    if (!deptId) {
        selCommune.innerHTML = '<option value="">-- Commune --</option>';
        return;
    }
    fetch(BASE_URL + '/api/communes?departement_id=' + deptId)
        .then(r => r.json())
        .then(data => {
            selCommune.innerHTML = '<option value="">-- Commune --</option>';
            (data.communes || data || []).forEach(c => {
                selCommune.innerHTML += `<option value="${c.id}">${c.nom}</option>`;
            });
        })
        .catch(() => {
            selCommune.innerHTML = '<option value="">-- Commune --</option>';
        });
});
</script>
