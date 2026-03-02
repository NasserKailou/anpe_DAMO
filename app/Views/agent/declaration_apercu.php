<?php
/**
 * Vue aperçu / récapitulatif de la déclaration avant soumission
 */
$declaration = $declaration ?? [];
$data        = $data ?? [];
$entreprise  = $data['entreprise'] ?? [];
$statut      = $declaration['statut'] ?? 'brouillon';
$canEdit     = in_array($statut, ['brouillon', 'corrigee']);
$canSubmit   = $canEdit;
$decId       = $declaration['id'] ?? 0;

$moisLabels = ['','Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
$badgeClass = ['brouillon'=>'secondary','soumise'=>'warning','validee'=>'success','rejetee'=>'danger','corrigee'=>'info'][$statut] ?? 'secondary';
$badgeLabel = ['brouillon'=>'Brouillon','soumise'=>'Soumise','validee'=>'Validée','rejetee'=>'Rejetée','corrigee'=>'En correction'][$statut] ?? $statut;

// Calcul totaux catégories
$totalH = 0; $totalF = 0;
foreach ($data['categories'] ?? [] as $cat => $row) {
    $totalH += ($row['nigeriens_h']??0) + ($row['africains_h']??0) + ($row['autres_nat_h']??0);
    $totalF += ($row['nigeriens_f']??0) + ($row['africains_f']??0) + ($row['autres_nat_f']??0);
}
$totalPertes = 0;
foreach ($data['pertes'] ?? [] as $row) {
    $totalPertes += ($row['effectif_h']??0) + ($row['effectif_f']??0);
}
?>

<style>
.section-apercu { margin-bottom: 1.5rem; }
.section-apercu .section-title { background: #f0f4ff; border-left: 4px solid #0d6efd; padding: 10px 14px; font-weight: 600; border-radius: 0 6px 6px 0; margin-bottom: 1rem; }
.table-apercu th { font-size: .8rem; background: #f8f9fa; }
.table-apercu td { font-size: .85rem; }
@media print { .no-print { display: none !important; } body { font-size: 12px; } }
</style>

<!-- En-tête & Statut -->
<div class="row mb-3">
    <div class="col-lg-9">
        <div class="card border-0 shadow-sm">
            <div class="card-body py-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-2 bg-primary bg-opacity-10 rounded">
                        <i class="bi bi-file-earmark-check text-primary fs-4"></i>
                    </div>
                    <div>
                        <h5 class="mb-0"><?= e($entreprise['raison_sociale'] ?? '') ?></h5>
                        <small class="text-muted">
                            Code: <strong><?= e($declaration['code_questionnaire'] ?? '') ?></strong>
                            &bull; Campagne <strong><?= e($entreprise['annee'] ?? '') ?></strong>
                            &bull; Région: <?= e($entreprise['region_nom'] ?? '') ?>
                        </small>
                    </div>
                    <div class="ms-auto">
                        <span class="badge bg-<?= $badgeClass ?> fs-6"><?= $badgeLabel ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="card border-0 shadow-sm h-100 text-center">
            <div class="card-body d-flex flex-column justify-content-center">
                <div class="fs-3 fw-bold text-primary"><?= $totalH + $totalF ?></div>
                <div class="text-muted small">Effectif total déclaré</div>
                <div class="text-muted small"><?= $totalH ?>H / <?= $totalF ?>F</div>
            </div>
        </div>
    </div>
</div>

<!-- Motif rejet -->
<?php if ($declaration['motif_rejet'] ?? ''): ?>
    <div class="alert alert-danger d-flex gap-2 mb-3">
        <i class="bi bi-exclamation-triangle-fill fs-5 flex-shrink-0"></i>
        <div>
            <strong>Déclaration rejetée :</strong> <?= e($declaration['motif_rejet']) ?>
            <?php if ($canEdit): ?>
                <div class="mt-2">
                    <a href="<?= url("agent/declaration/$decId/saisie") ?>" class="btn btn-sm btn-warning">
                        <i class="bi bi-pencil me-1"></i>Corriger la déclaration
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<!-- Boutons d'action -->
<div class="d-flex gap-2 mb-3 no-print">
    <?php if ($canEdit): ?>
        <a href="<?= url("agent/declaration/$decId/saisie") ?>" class="btn btn-outline-primary">
            <i class="bi bi-pencil me-1"></i>Modifier
        </a>
    <?php endif; ?>
    <?php if ($canSubmit): ?>
        <button type="button" class="btn btn-success" id="btn-soumettre">
            <i class="bi bi-send me-1"></i>Soumettre à l'ANPE
        </button>
    <?php endif; ?>
    <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
        <i class="bi bi-printer me-1"></i>Imprimer
    </button>
    <a href="<?= url('agent/declarations') ?>" class="btn btn-outline-secondary ms-auto">
        <i class="bi bi-arrow-left me-1"></i>Retour à la liste
    </a>
</div>

<!-- ===================== SECTION 1 : IDENTIFICATION ===================== -->
<div class="section-apercu">
    <div class="section-title"><i class="bi bi-building me-2"></i>1. Identification de l'entreprise</div>
    <div class="row g-2">
        <?php
        $fields = [
            ['Raison sociale', $entreprise['raison_sociale'] ?? ''],
            ['N° CNSS', $entreprise['numero_cnss'] ?? 'Non renseigné'],
            ['Nationalité', $entreprise['nationalite'] ?? ''],
            ['Branche d\'activité', $entreprise['branche_libelle'] ?? ''],
            ['Activité principale', $entreprise['activite_principale'] ?? ''],
            ['Masse salariale (FCFA)', $declaration['masse_salariale'] ? number_format((float)$declaration['masse_salariale'],0,',',' ') : ''],
            ['Département', $entreprise['dept_nom'] ?? ''],
            ['Commune', $entreprise['commune_nom'] ?? ''],
            ['Localité', $entreprise['localite'] ?? ''],
            ['Quartier', $entreprise['quartier'] ?? ''],
            ['Boîte postale', $entreprise['boite_postale'] ?? ''],
            ['Téléphone', $entreprise['telephone'] ?? ''],
            ['Email', $entreprise['email'] ?? ''],
            ['Enquêteur', $declaration['nom_enqueteur'] ?? ''],
        ];
        foreach ($fields as [$label, $value]):
        ?>
            <div class="col-md-3 col-sm-6">
                <div class="p-2 bg-light rounded">
                    <div class="text-muted" style="font-size:.75rem"><?= $label ?></div>
                    <div class="fw-semibold" style="font-size:.9rem"><?= $value ? e($value) : '<span class="text-muted fst-italic">—</span>' ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- ===================== SECTION 2 : EFFECTIFS MENSUELS ===================== -->
<div class="section-apercu">
    <div class="section-title"><i class="bi bi-calendar3 me-2"></i>2. Effectifs mensuels</div>
    <div class="table-responsive">
        <table class="table table-bordered table-apercu text-center">
            <thead><tr>
                <?php for ($m=1;$m<=12;$m++): ?>
                    <th><?= ['','Jan','Fév','Mar','Avr','Mai','Jun','Jul','Aoû','Sep','Oct','Nov','Déc'][$m] ?></th>
                <?php endfor; ?>
                <th class="table-primary">Moy.</th>
            </tr></thead>
            <tbody><tr>
                <?php $sum=0; $cnt=0; for ($m=1;$m<=12;$m++): $v=(int)($data['effectifs_mensuels'][$m]??0); $sum+=$v; $cnt++; ?>
                    <td><?= $v ?: '<span class="text-muted">0</span>' ?></td>
                <?php endfor; ?>
                <td class="fw-bold table-primary"><?= $cnt > 0 ? round($sum/$cnt,1) : 0 ?></td>
            </tr></tbody>
        </table>
    </div>
</div>

<!-- ===================== SECTION 3 : CATÉGORIES ===================== -->
<div class="section-apercu">
    <div class="section-title"><i class="bi bi-people me-2"></i>3. Effectifs par catégorie professionnelle</div>
    <div class="table-responsive">
        <table class="table table-bordered table-apercu">
            <thead class="table-light">
                <tr>
                    <th rowspan="2" class="align-middle">Catégorie</th>
                    <th colspan="2" class="text-center">Nigériens</th>
                    <th colspan="2" class="text-center">Africains</th>
                    <th colspan="2" class="text-center">Autres nat.</th>
                    <th colspan="2" class="text-center table-primary">Total</th>
                </tr>
                <tr>
                    <th class="text-center">H</th><th class="text-center">F</th>
                    <th class="text-center">H</th><th class="text-center">F</th>
                    <th class="text-center">H</th><th class="text-center">F</th>
                    <th class="text-center table-primary">H</th><th class="text-center table-primary">F</th>
                </tr>
            </thead>
            <tbody>
                <?php $gtH=0;$gtF=0; foreach (CATEGORIES_PROFESSIONNELLES as $key => $label): ?>
                    <?php $row=$data['categories'][$key]??[];
                    $tH=($row['nigeriens_h']??0)+($row['africains_h']??0)+($row['autres_nat_h']??0);
                    $tF=($row['nigeriens_f']??0)+($row['africains_f']??0)+($row['autres_nat_f']??0);
                    $gtH+=$tH; $gtF+=$tF; ?>
                    <tr>
                        <td class="fw-semibold"><?= $label ?></td>
                        <td class="text-center"><?= $row['nigeriens_h']??0 ?></td>
                        <td class="text-center"><?= $row['nigeriens_f']??0 ?></td>
                        <td class="text-center"><?= $row['africains_h']??0 ?></td>
                        <td class="text-center"><?= $row['africains_f']??0 ?></td>
                        <td class="text-center"><?= $row['autres_nat_h']??0 ?></td>
                        <td class="text-center"><?= $row['autres_nat_f']??0 ?></td>
                        <td class="text-center fw-bold table-primary"><?= $tH ?></td>
                        <td class="text-center fw-bold table-primary"><?= $tF ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot class="table-secondary fw-bold">
                <tr>
                    <td>TOTAL GÉNÉRAL</td>
                    <td colspan="6" class="text-center text-muted small">—</td>
                    <td class="text-center table-primary"><?= $gtH ?></td>
                    <td class="text-center table-primary"><?= $gtF ?></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<!-- ===================== SECTION 4 : NIVEAUX D'INSTRUCTION ===================== -->
<div class="section-apercu">
    <div class="section-title"><i class="bi bi-mortarboard me-2"></i>4. Niveaux d'instruction</div>
    <div class="table-responsive">
        <table class="table table-bordered table-apercu">
            <thead class="table-light">
                <tr>
                    <th>Niveau</th>
                    <?php foreach (CATEGORIES_PROFESSIONNELLES as $label): ?>
                        <th colspan="2" class="text-center" style="font-size:.72rem"><?= $label ?></th>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <th></th>
                    <?php foreach (CATEGORIES_PROFESSIONNELLES as $key => $label): ?>
                        <th class="text-center" style="font-size:.7rem">H</th>
                        <th class="text-center" style="font-size:.7rem">F</th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach (NIVEAUX_INSTRUCTION as $nivKey => $nivLabel): ?>
                    <tr>
                        <td class="small"><?= $nivLabel ?></td>
                        <?php foreach (CATEGORIES_PROFESSIONNELLES as $catKey => $catLabel): ?>
                            <?php $row=$data['niveaux'][$catKey][$nivKey]??[]; ?>
                            <td class="text-center"><?= $row['effectif_h']??0 ?></td>
                            <td class="text-center"><?= $row['effectif_f']??0 ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ===================== SECTION 5 : FORMATION ===================== -->
<div class="section-apercu">
    <div class="section-title"><i class="bi bi-award me-2"></i>5. Formation professionnelle</div>
    <?php $f = ($data['formations']??[[]])[0]??[]; ?>
    <?php if ($f['a_eu_formation'] ?? false): ?>
        <div class="row g-2">
            <div class="col-md-3"><div class="p-2 bg-light rounded"><div class="text-muted" style="font-size:.75rem">Qualification visée</div><div class="fw-semibold"><?= e($f['qualification']??'') ?: '—' ?></div></div></div>
            <div class="col-md-3"><div class="p-2 bg-light rounded"><div class="text-muted" style="font-size:.75rem">Nature</div><div class="fw-semibold"><?= e($f['nature_formation']??'') ?: '—' ?></div></div></div>
            <div class="col-md-2"><div class="p-2 bg-light rounded"><div class="text-muted" style="font-size:.75rem">Durée</div><div class="fw-semibold"><?= e($f['duree_formation']??'') ?: '—' ?></div></div></div>
            <div class="col-md-2"><div class="p-2 bg-light rounded"><div class="text-muted" style="font-size:.75rem">Effectif formé (H/F)</div><div class="fw-semibold"><?= ($f['effectif_h']??0) ?> H / <?= ($f['effectif_f']??0) ?> F</div></div></div>
            <?php if ($f['observations']??''): ?>
                <div class="col-12"><div class="p-2 bg-light rounded"><div class="text-muted" style="font-size:.75rem">Observations</div><div><?= e($f['observations']) ?></div></div></div>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <p class="text-muted fst-italic"><i class="bi bi-dash-circle me-1"></i>Aucune formation professionnelle déclarée.</p>
    <?php endif; ?>
</div>

<!-- ===================== SECTION 6 : PERTES D'EMPLOI ===================== -->
<div class="section-apercu">
    <div class="section-title"><i class="bi bi-person-dash me-2"></i>6. Pertes d'emploi</div>
    <?php if ($totalPertes > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-apercu">
                <thead class="table-light">
                    <tr><th>Motif</th><th class="text-center">Hommes</th><th class="text-center">Femmes</th><th class="text-center table-primary">Total</th></tr>
                </thead>
                <tbody>
                    <?php foreach (MOTIFS_PERTE_EMPLOI as $key => $label): ?>
                        <?php $row=$data['pertes'][$key]??[]; $tot=($row['effectif_h']??0)+($row['effectif_f']??0); ?>
                        <?php if ($tot > 0): ?>
                            <tr>
                                <td><?= $label ?> <?= ($key==='autres' && ($row['motif_autre']??'')) ? '<small class="text-muted">('.e($row['motif_autre']).')</small>' : '' ?></td>
                                <td class="text-center"><?= $row['effectif_h']??0 ?></td>
                                <td class="text-center"><?= $row['effectif_f']??0 ?></td>
                                <td class="text-center fw-bold table-primary"><?= $tot ?></td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="table-secondary fw-bold">
                    <tr><td>TOTAL</td><td class="text-center"><?= array_sum(array_column(array_values($data['pertes']??[]),'effectif_h')) ?></td><td class="text-center"><?= array_sum(array_column(array_values($data['pertes']??[]),'effectif_f')) ?></td><td class="text-center table-primary"><?= $totalPertes ?></td></tr>
                </tfoot>
            </table>
        </div>
        <?php $persp = $data['perspective']??[]; if ($persp['perspective']??''): ?>
            <div class="alert alert-info py-2 px-3 mt-2">
                <strong>Perspectives :</strong>
                <?= ['stable'=>'Stable','hausse'=>'En hausse','baisse'=>'En baisse','inconnue'=>'Inconnue'][$persp['perspective']] ?? $persp['perspective'] ?>
                <?= ($persp['justification']??'') ? '— '.$persp['justification'] : '' ?>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <p class="text-muted fst-italic"><i class="bi bi-dash-circle me-1"></i>Aucune perte d'emploi déclarée.</p>
    <?php endif; ?>
</div>

<!-- ===================== SECTION 7 : EFFECTIFS ÉTRANGERS ===================== -->
<div class="section-apercu">
    <div class="section-title"><i class="bi bi-globe me-2"></i>7. Effectifs étrangers</div>
    <?php if (!empty($data['etrangers'])): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-apercu">
                <thead class="table-light">
                    <tr><th>Pays</th><th>Qualification</th><th>Fonction</th><th class="text-center">Sexe</th><th class="text-center">Nombre</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($data['etrangers'] as $et): ?>
                        <tr>
                            <td><?= e($et['pays']) ?></td>
                            <td><?= e($et['qualification']??'') ?></td>
                            <td><?= e($et['fonction']??'') ?></td>
                            <td class="text-center"><?= e($et['sexe']??'H') ?></td>
                            <td class="text-center fw-bold"><?= (int)($et['nombre']??0) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="table-secondary fw-bold">
                    <tr><td colspan="4">Total étrangers</td><td class="text-center"><?= array_sum(array_column($data['etrangers'],'nombre')) ?></td></tr>
                </tfoot>
            </table>
        </div>
    <?php else: ?>
        <p class="text-muted fst-italic"><i class="bi bi-dash-circle me-1"></i>Aucun effectif étranger déclaré.</p>
    <?php endif; ?>
</div>

<!-- Historique soumission/validation -->
<?php if ($declaration['date_soumission'] ?? ''): ?>
    <div class="alert alert-success d-flex gap-2 no-print">
        <i class="bi bi-check-circle-fill fs-5"></i>
        <div>
            <strong>Soumise le</strong> <?= formatDateTime($declaration['date_soumission']) ?>
            <?php if ($declaration['date_validation'] ?? ''): ?>
                &bull; <strong>Validée le</strong> <?= formatDateTime($declaration['date_validation']) ?>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<!-- Modal soumission -->
<?php if ($canSubmit): ?>
<div class="modal fade" id="modal-soumettre" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-send me-2"></i>Confirmer la soumission</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Une fois soumise, la déclaration sera envoyée à l'ANPE pour validation et ne pourra plus être modifiée.
                </div>
                <p>Confirmez-vous la soumission de la déclaration <strong><?= e($declaration['code_questionnaire']??'') ?></strong> ?</p>
                <p><strong>Effectif total déclaré :</strong> <?= $totalH + $totalF ?> employés (<?= $totalH ?> H / <?= $totalF ?> F)</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <form method="POST" action="<?= url("agent/declaration/$decId/soumettre") ?>">
                    <?= csrfField() ?>
                    <button type="submit" class="btn btn-success"><i class="bi bi-send me-1"></i>Soumettre définitivement</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
document.getElementById('btn-soumettre')?.addEventListener('click', () => {
    new bootstrap.Modal(document.getElementById('modal-soumettre')).show();
});
</script>
<?php endif; ?>
