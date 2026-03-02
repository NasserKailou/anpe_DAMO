<?php /* Vue : Page d'accueil publique e-DAMO */ ?>

<!-- ===== HERO ===== -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-7">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <span class="badge text-white fw-normal px-3 py-2" style="background:rgba(255,255,255,.15);border-radius:20px;font-size:.8rem">
                        <i class="bi bi-circle-fill me-1" style="font-size:.5rem;color:#4ade80"></i>
                        Plateforme officielle ANPE Niger
                    </span>
                </div>
                <h1 class="display-5 fw-black text-white mb-3 lh-sm" style="font-weight:900!important">
                    <?= APP_FULL_NAME ?>
                </h1>
                <p class="lead text-white mb-4" style="opacity:.85;font-size:1.05rem">
                    Déclarez, suivez et analysez les données annuelles sur la main d'œuvre au Niger, 
                    en toute simplicité et en toute sécurité.
                </p>
                <div class="d-flex gap-3 flex-wrap">
                    <a href="<?= url('statistiques') ?>" class="btn-hero-light">
                        <i class="bi bi-bar-chart-fill"></i>Voir les statistiques
                    </a>
                    <a href="<?= url('donnees') ?>" class="btn-hero-outline">
                        <i class="bi bi-database-fill"></i>Données ouvertes
                    </a>
                    <?php if ($campagne): ?>
                    <a href="<?= url('login') ?>" class="btn-hero-accent">
                        <i class="bi bi-pencil-fill"></i>Soumettre une déclaration
                    </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="row g-3">
                    <?php
                    $heroKpis = [
                        ['val' => formatNumber($stats['total_entreprises']),  'label' => 'Entreprises enregistrées', 'icon' => 'bi-building'],
                        ['val' => formatNumber($stats['total_declarations']), 'label' => 'Déclarations validées',    'icon' => 'bi-check2-circle'],
                        ['val' => $stats['regions_couvertes'].'/'.count(REGIONS_NIGER), 'label' => 'Régions couvertes', 'icon' => 'bi-geo-alt-fill'],
                        ['val' => $stats['derniere_annee'] ?: date('Y'), 'label' => 'Dernière campagne', 'icon' => 'bi-calendar2-check-fill'],
                    ];
                    foreach ($heroKpis as $k): ?>
                    <div class="col-6">
                        <div class="hero-kpi-card">
                            <i class="bi <?= $k['icon'] ?>" style="font-size:1.6rem;color:rgba(255,255,255,.8);margin-bottom:8px;display:block"></i>
                            <div class="hero-kpi-val"><?= $k['val'] ?></div>
                            <div class="hero-kpi-label"><?= $k['label'] ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if ($campagne): ?>
<!-- ===== BANDEAU CAMPAGNE ACTIVE ===== -->
<div class="campagne-banner">
    <i class="bi bi-calendar-check-fill me-2"></i>
    <strong>Campagne <?= e($campagne['libelle']) ?></strong> en cours —
    Date limite&nbsp;: <strong><?= formatDate($campagne['date_fin']) ?></strong>
    <a href="<?= url('login') ?>"><i class="bi bi-arrow-right me-1"></i>Déclarer maintenant</a>
</div>
<?php endif; ?>

<?php if ($effectifsGlobaux && ($effectifsGlobaux['total_hommes'] || $effectifsGlobaux['total_femmes'])): ?>
<!-- ===== KPI EMPLOIS ===== -->
<section class="pub-section bg-white">
    <div class="container">
        <h2 class="pub-section-title">Emplois déclarés — Vue d'ensemble</h2>
        <p class="pub-section-subtitle">Données issues des déclarations validées de la dernière campagne</p>
        <div class="row g-4 justify-content-center">
            <?php
            $total = ($effectifsGlobaux['total_hommes'] ?? 0) + ($effectifsGlobaux['total_femmes'] ?? 0);
            $kpis = [
                ['val'=>$total, 'label'=>'Total emplois', 'icon'=>'bi-people-fill', 'bg'=>'#eff6ff', 'color'=>'#1d4ed8'],
                ['val'=>$effectifsGlobaux['total_hommes']??0, 'label'=>'Hommes', 'icon'=>'bi-person-fill', 'bg'=>'#e0f2fe', 'color'=>'#0369a1'],
                ['val'=>$effectifsGlobaux['total_femmes']??0, 'label'=>'Femmes', 'icon'=>'bi-person-fill', 'bg'=>'#fdf2f8', 'color'=>'#9d174d'],
                ['val'=>$effectifsGlobaux['total_nigeriens']??0, 'label'=>'Nigériens', 'icon'=>'bi-flag-fill', 'bg'=>'#f0fdf4', 'color'=>'#15803d'],
            ];
            foreach ($kpis as $k): ?>
            <div class="col-6 col-md-3">
                <div class="kpi-card">
                    <div class="kpi-icon" style="background:<?= $k['bg'] ?>;color:<?= $k['color'] ?>">
                        <i class="bi <?= $k['icon'] ?>"></i>
                    </div>
                    <div class="kpi-val" style="color:<?= $k['color'] ?>"><?= formatNumber($k['val']) ?></div>
                    <div class="kpi-label"><?= $k['label'] ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($parBranche)): ?>
<!-- ===== BRANCHES ===== -->
<section class="pub-section pub-section-alt">
    <div class="container">
        <h2 class="pub-section-title">Emplois par branche d'activité</h2>
        <p class="pub-section-subtitle">Répartition des effectifs déclarés par secteur économique</p>
        <div class="row g-3">
            <?php $max = max(array_column($parBranche, 'total_emplois') ?: [1]); ?>
            <?php foreach ($parBranche as $b): ?>
            <div class="col-md-4">
                <div class="branche-card">
                    <div class="fw-600" style="font-size:.88rem;font-weight:600;color:#111827"><?= e(truncate($b['branche'], 45)) ?></div>
                    <div class="text-muted" style="font-size:.78rem"><?= formatNumber($b['nb_entreprises']) ?> entreprise(s)</div>
                    <div class="branche-progress">
                        <div class="branche-progress-bar" style="width:<?= $max > 0 ? round($b['total_emplois'] / $max * 100) : 0 ?>%"></div>
                    </div>
                    <div style="font-size:1.05rem;font-weight:800;color:var(--pub-primary)">
                        <?= formatNumber($b['total_emplois'] ?? 0) ?> emplois
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-5">
            <a href="<?= url('statistiques') ?>" class="btn btn-primary btn-lg px-5 fw-600">
                <i class="bi bi-bar-chart-fill me-2"></i>Toutes les statistiques
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($guides)): ?>
<!-- ===== GUIDES ===== -->
<section class="pub-section bg-white">
    <div class="container">
        <h2 class="pub-section-title">Guides de remplissage</h2>
        <p class="pub-section-subtitle">Téléchargez les guides officiels pour vous aider dans vos déclarations</p>
        <div class="row g-3 justify-content-center">
            <?php foreach ($guides as $g): ?>
            <div class="col-md-4">
                <div class="guide-card">
                    <div class="d-flex align-items-start gap-3 mb-3">
                        <div style="width:44px;height:44px;background:#fef2f2;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                            <i class="bi bi-file-earmark-pdf-fill text-danger" style="font-size:1.3rem"></i>
                        </div>
                        <div class="flex-1">
                            <div class="fw-bold" style="font-size:.9rem;color:#111827"><?= e($g['titre']) ?></div>
                            <div class="text-muted" style="font-size:.78rem">Année <?= e($g['annee']) ?> — <?= formatFileSize($g['fichier_taille']) ?></div>
                        </div>
                    </div>
                    <?php if ($g['description']): ?>
                    <p class="text-muted flex-1" style="font-size:.82rem;flex:1"><?= e(truncate($g['description'], 90)) ?></p>
                    <?php endif; ?>
                    <a href="<?= url('guides') ?>" class="btn btn-outline-primary btn-sm w-100 mt-auto">
                        <i class="bi bi-download me-1"></i>Télécharger
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4">
            <a href="<?= url('guides') ?>" class="btn btn-outline-primary px-4">
                <i class="bi bi-books me-1"></i>Tous les guides
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ===== CTA FINAL ===== -->
<section class="pub-cta-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <i class="bi bi-building-fill-gear" style="font-size:2.5rem;opacity:.7;margin-bottom:16px;display:block"></i>
                <h2 class="fw-800 mb-3" style="font-weight:800">Espace Agent / Déclarant</h2>
                <p class="lead mb-4" style="opacity:.85;font-size:.95rem">
                    Connectez-vous à votre espace personnel pour accéder aux formulaires de déclaration, 
                    suivre l'avancement de vos dossiers et consulter vos historiques.
                </p>
                <a href="<?= url('login') ?>" class="btn-hero-light" style="display:inline-flex">
                    <i class="bi bi-box-arrow-in-right"></i>
                    Se connecter à mon espace
                </a>
            </div>
        </div>
    </div>
</section>
