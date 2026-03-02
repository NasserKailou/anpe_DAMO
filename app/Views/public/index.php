<?php // Vue : Page d'accueil publique e-DAMO ?>

<!-- Hero section -->
<section class="hero-section py-5 text-white" style="background:linear-gradient(135deg,#0d47a1,#1565c0)">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <h1 class="display-5 fw-bold"><?= APP_FULL_NAME ?></h1>
                <p class="lead mt-3">
                    Plateforme digitale de l'ANPE Niger pour la collecte et l'analyse des données annuelles sur la main d'œuvre.
                </p>
                <div class="d-flex gap-3 mt-4 flex-wrap">
                    <a href="/statistiques" class="btn btn-light btn-lg">
                        <i class="bi bi-bar-chart me-2"></i>Voir les statistiques
                    </a>
                    <a href="/donnees" class="btn btn-outline-light btn-lg">
                        <i class="bi bi-table me-2"></i>Données ouvertes
                    </a>
                    <?php if ($campagne): ?>
                    <a href="/login" class="btn btn-warning btn-lg">
                        <i class="bi bi-pencil me-2"></i>Soumettre une déclaration
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-5">
                <!-- Stats rapides -->
                <div class="row g-3">
                    <div class="col-6">
                        <div class="bg-white bg-opacity-10 rounded-3 p-3 text-center">
                            <div style="font-size:2rem;font-weight:800"><?= formatNumber($stats['total_entreprises']) ?></div>
                            <div style="font-size:.85rem">Entreprises</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="bg-white bg-opacity-10 rounded-3 p-3 text-center">
                            <div style="font-size:2rem;font-weight:800"><?= formatNumber($stats['total_declarations']) ?></div>
                            <div style="font-size:.85rem">Déclarations validées</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="bg-white bg-opacity-10 rounded-3 p-3 text-center">
                            <div style="font-size:2rem;font-weight:800"><?= $stats['regions_couvertes'] ?>/<?= count(REGIONS_NIGER) ?></div>
                            <div style="font-size:.85rem">Régions couvertes</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="bg-white bg-opacity-10 rounded-3 p-3 text-center">
                            <div style="font-size:2rem;font-weight:800"><?= $stats['derniere_annee'] ?></div>
                            <div style="font-size:.85rem">Dernière campagne</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if ($campagne): ?>
<!-- Bandeau campagne active -->
<div class="alert alert-success m-0 rounded-0 text-center py-2">
    <i class="bi bi-calendar-check me-2"></i>
    <strong>Campagne <?= e($campagne['libelle']) ?></strong> en cours —
    Soumettez votre déclaration avant le <strong><?= formatDate($campagne['date_fin']) ?></strong>
    <a href="/login" class="btn btn-success btn-sm ms-3">Déclarer maintenant</a>
</div>
<?php endif; ?>

<!-- Effectifs globaux -->
<?php if ($effectifsGlobaux && ($effectifsGlobaux['total_hommes'] || $effectifsGlobaux['total_femmes'])): ?>
<section class="py-5 bg-white">
    <div class="container">
        <h2 class="text-center mb-4">Emplois déclarés — Vue d'ensemble</h2>
        <div class="row g-4 justify-content-center">
            <?php
            $totalGlobal = ($effectifsGlobaux['total_hommes'] ?? 0) + ($effectifsGlobaux['total_femmes'] ?? 0);
            $kpis = [
                ['val' => $totalGlobal, 'label' => 'Total emplois', 'icon' => 'people', 'color' => '#0d47a1'],
                ['val' => $effectifsGlobaux['total_hommes'] ?? 0, 'label' => 'Hommes', 'icon' => 'person', 'color' => '#1565c0'],
                ['val' => $effectifsGlobaux['total_femmes'] ?? 0, 'label' => 'Femmes', 'icon' => 'person', 'color' => '#e91e63'],
                ['val' => $effectifsGlobaux['total_nigeriens'] ?? 0, 'label' => 'Nigériens', 'icon' => 'flag', 'color' => '#2e7d32'],
            ];
            foreach ($kpis as $kpi): ?>
            <div class="col-6 col-md-3 text-center">
                <div class="p-3 rounded-3" style="background:#f5f7fa">
                    <i class="bi bi-<?= $kpi['icon'] ?>" style="font-size:2rem;color:<?= $kpi['color'] ?>"></i>
                    <div style="font-size:1.8rem;font-weight:800;color:<?= $kpi['color'] ?>"><?= formatNumber($kpi['val']) ?></div>
                    <div class="text-muted"><?= $kpi['label'] ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Répartition par branche -->
<?php if (!empty($parBranche)): ?>
<section class="py-5" style="background:#f8fafc">
    <div class="container">
        <h2 class="text-center mb-4">Emplois par branche d'activité</h2>
        <div class="row g-3">
            <?php $maxEmplois = max(array_column($parBranche, 'total_emplois') ?: [1]); ?>
            <?php foreach ($parBranche as $b): ?>
            <div class="col-md-4">
                <div class="bg-white rounded-3 p-3 shadow-sm h-100">
                    <strong style="font-size:.9rem"><?= e(truncate($b['branche'], 40)) ?></strong>
                    <div class="text-muted" style="font-size:.8rem"><?= formatNumber($b['nb_entreprises']) ?> entreprise(s)</div>
                    <div class="mt-2" style="background:#e0e0e0;border-radius:4px;height:6px">
                        <div style="width:<?= $maxEmplois > 0 ? round($b['total_emplois'] / $maxEmplois * 100) : 0 ?>%;background:#0d47a1;border-radius:4px;height:6px"></div>
                    </div>
                    <div style="font-size:1.1rem;font-weight:700;color:#0d47a1"><?= formatNumber($b['total_emplois'] ?? 0) ?> emplois</div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4">
            <a href="/statistiques" class="btn btn-primary">
                <i class="bi bi-bar-chart me-2"></i>Voir toutes les statistiques
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Guides -->
<?php if (!empty($guides)): ?>
<section class="py-5 bg-white">
    <div class="container">
        <h2 class="text-center mb-4">Guides de remplissage</h2>
        <div class="row g-3 justify-content-center">
            <?php foreach ($guides as $g): ?>
            <div class="col-md-4">
                <div class="border rounded-3 p-3 h-100 d-flex flex-column">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="bi bi-file-earmark-pdf text-danger" style="font-size:1.5rem"></i>
                        <strong><?= e($g['titre']) ?></strong>
                    </div>
                    <?php if ($g['description']): ?>
                    <p class="text-muted" style="font-size:.84rem;flex:1"><?= e(truncate($g['description'], 80)) ?></p>
                    <?php endif; ?>
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <small class="text-muted">Année <?= e($g['annee']) ?> — <?= formatFileSize($g['fichier_taille']) ?></small>
                        <a href="/guides" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-download me-1"></i>Télécharger
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4">
            <a href="/guides" class="btn btn-outline-primary">Tous les guides</a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Accès agent -->
<section class="py-5" style="background:linear-gradient(135deg,#0d47a1,#1565c0);color:#fff">
    <div class="container text-center">
        <h2 class="mb-3">Espace Agent / Déclarant</h2>
        <p class="lead mb-4">Connectez-vous pour accéder à votre espace de saisie et soumettre vos déclarations.</p>
        <a href="/login" class="btn btn-light btn-lg px-5">
            <i class="bi bi-box-arrow-in-right me-2"></i>Connexion
        </a>
    </div>
</section>
