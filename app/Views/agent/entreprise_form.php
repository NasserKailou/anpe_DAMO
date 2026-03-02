<?php
/**
 * Formulaire entreprise (création / modification) - agent
 */
defined('EDAMO') or exit;

$entreprise   = $entreprise   ?? [];
$branches     = $branches     ?? [];
$departements = $departements ?? [];
$communes     = $communes     ?? [];
$mode         = $mode         ?? 'create';
$region       = $region       ?? null;

$isEdit = $mode === 'edit';

// ── URLS correctes (correspondent aux routes définies dans web.php) ──────────
// POST /agent/entreprise/nouvelle       → creerEntreprise()
// POST /agent/entreprise/:id/modifier   → updateEntreprise()
$action    = $isEdit
    ? url("agent/entreprise/{$entreprise['id']}/modifier")
    : url('agent/entreprise/nouvelle');
$pageTitle = $isEdit ? 'Modifier l\'entreprise' : 'Nouvelle entreprise';

$nationalites = [
    'Nigérienne','Française','Américaine','Britannique','Chinoise',
    'Libanaise','Togolaise','Béninoise','Burkinabè','Malienne',
    'Sénégalaise','Ivoirienne','Autre',
];

// ID département courant (pour recharger les communes au chargement)
$currentDeptId = (int)($entreprise['departement_id'] ?? 0);
?>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-building text-primary fs-5"></i>
                    <h6 class="mb-0 fw-semibold"><?= $pageTitle ?></h6>
                    <?php if ($region): ?>
                        <span class="badge bg-secondary ms-auto">
                            <i class="bi bi-geo-alt me-1"></i><?= e($region['nom'] ?? '') ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card-body">
                <form method="POST" action="<?= $action ?>" id="form-entreprise">
                    <?= csrfField() ?>

                    <!-- ── Informations générales ── -->
                    <h6 class="text-muted text-uppercase small mb-3 mt-1">
                        <i class="bi bi-info-circle me-1"></i>Informations générales
                    </h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">
                                Raison sociale <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="raison_sociale" class="form-control" required
                                   value="<?= e($entreprise['raison_sociale'] ?? '') ?>"
                                   placeholder="Dénomination officielle de l'entreprise">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">N° CNSS</label>
                            <input type="text" name="numero_cnss" class="form-control font-monospace"
                                   value="<?= e($entreprise['numero_cnss'] ?? '') ?>"
                                   placeholder="Ex : NE-123456">
                            <div class="form-text">Doit être unique dans la région</div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Nationalité</label>
                            <select name="nationalite" class="form-select">
                                <?php foreach ($nationalites as $nat): ?>
                                    <option value="<?= e($nat) ?>"
                                        <?= ($entreprise['nationalite'] ?? 'Nigérienne') === $nat ? 'selected' : '' ?>>
                                        <?= e($nat) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Branche d'activité</label>
                            <select name="branche_id" class="form-select">
                                <option value="">-- Sélectionner une branche --</option>
                                <?php foreach ($branches as $b): ?>
                                    <option value="<?= $b['id'] ?>"
                                        <?= ($entreprise['branche_id'] ?? '') == $b['id'] ? 'selected' : '' ?>>
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
                                   placeholder="Optionnel">
                        </div>
                    </div>

                    <hr>

                    <!-- ── Localisation ── -->
                    <h6 class="text-muted text-uppercase small mb-3">
                        <i class="bi bi-geo-alt me-1"></i>Localisation
                    </h6>
                    <div class="row g-3 mb-4">
                        <?php if ($region): ?>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Région</label>
                            <input type="text" class="form-control" value="<?= e($region['nom'] ?? '') ?>" readonly>
                        </div>
                        <?php endif; ?>

                        <!-- Département — peuplé côté serveur selon la région de l'agent -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Département</label>
                            <select name="departement_id" class="form-select" id="sel-dept">
                                <option value="">-- Département --</option>
                                <?php foreach ($departements as $d): ?>
                                    <option value="<?= (int)$d['id'] ?>"
                                        <?= $currentDeptId === (int)$d['id'] ? 'selected' : '' ?>>
                                        <?= e($d['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Commune — rechargée via AJAX quand le département change -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Commune</label>
                            <select name="commune_id" class="form-select" id="sel-commune">
                                <option value="">-- Commune --</option>
                                <?php foreach ($communes as $c): ?>
                                    <option value="<?= (int)$c['id'] ?>"
                                        <?= ($entreprise['commune_id'] ?? '') == $c['id'] ? 'selected' : '' ?>>
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

                    <!-- ── Coordonnées ── -->
                    <h6 class="text-muted text-uppercase small mb-3">
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

                    <!-- ── Boutons ── -->
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
/**
 * Chargement dynamique des communes selon le département sélectionné
 * Route API : GET /api/communes/:dept_id  → { success: true, communes: [{id, nom}, …] }
 */
(function () {
    const BASE       = (window.APP_BASE ?? '').replace(/\/+$/, '');
    const selDept    = document.getElementById('sel-dept');
    const selCommune = document.getElementById('sel-commune');

    if (!selDept || !selCommune) return;

    // Valeur pré-sélectionnée en mode édition
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
            console.error('Erreur chargement communes :', err);
            selCommune.innerHTML = '<option value="">-- Erreur de chargement --</option>';
        } finally {
            selCommune.disabled = false;
        }
    }

    // Changement de département → recharger communes
    selDept.addEventListener('change', function () {
        loadCommunes(this.value, 0);
    });

    // En mode édition : si un département est déjà sélectionné, charger ses communes
    // et pré-sélectionner la commune enregistrée
    if (selDept.value && savedCommuneId && <?= count($communes) === 0 ? 'true' : 'false' ?>) {
        loadCommunes(selDept.value, savedCommuneId);
    }
})();
</script>
