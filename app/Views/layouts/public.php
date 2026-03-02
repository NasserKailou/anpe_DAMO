<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="e-DAMO - Déclaration Annuelle de la Main d'Œuvre en ligne - ANPE Niger">
    <title><?= e($pageTitle ?? APP_NAME) ?></title>
    <link rel="icon" type="image/png" href="/assets/img/favicon.ico">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="/assets/css/public.css">
</head>
<body class="public-layout">

<!-- Navigation -->
<nav class="public-navbar sticky-top">
    <div class="container">
        <div class="d-flex align-items-center justify-content-between">
            <a href="/accueil" class="navbar-brand-pub d-flex align-items-center gap-2 text-decoration-none">
                <img src="/assets/img/logo-anpe.png" alt="ANPE" style="width:44px;height:44px;border-radius:50%;background:#fff;padding:3px">
                <div>
                    <span class="d-block text-white fw-bold fs-5">e-DAMO</span>
                    <small class="text-white-50 d-none d-md-block">ANPE Niger</small>
                </div>
            </a>
            
            <div class="d-flex align-items-center gap-3">
                <nav class="d-none d-lg-flex gap-1">
                    <a href="/accueil" class="nav-pub-link <?= $_SERVER['REQUEST_URI'] === '/' || $_SERVER['REQUEST_URI'] === '/accueil' ? 'active' : '' ?>">
                        <i class="bi bi-house me-1"></i>Accueil
                    </a>
                    <a href="/statistiques" class="nav-pub-link <?= strpos($_SERVER['REQUEST_URI'], '/statistiques') !== false ? 'active' : '' ?>">
                        <i class="bi bi-bar-chart me-1"></i>Statistiques
                    </a>
                    <a href="/donnees" class="nav-pub-link <?= strpos($_SERVER['REQUEST_URI'], '/donnees') !== false ? 'active' : '' ?>">
                        <i class="bi bi-database me-1"></i>Données
                    </a>
                    <a href="/guides" class="nav-pub-link <?= strpos($_SERVER['REQUEST_URI'], '/guides') !== false ? 'active' : '' ?>">
                        <i class="bi bi-book me-1"></i>Guides
                    </a>
                </nav>

                <?php if (isAuthenticated()): ?>
                <a href="<?= isAdmin() ? '/admin/dashboard' : '/agent/dashboard' ?>" class="btn btn-warning btn-sm fw-bold">
                    <i class="bi bi-grid me-1"></i>Mon espace
                </a>
                <?php else: ?>
                <a href="/login" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-box-arrow-in-right me-1"></i>Connexion
                </a>
                <?php endif; ?>

                <!-- Menu mobile -->
                <button class="d-lg-none btn btn-outline-light btn-sm" data-bs-toggle="collapse" data-bs-target="#mobileMenu">
                    <i class="bi bi-list"></i>
                </button>
            </div>
        </div>

        <!-- Menu mobile -->
        <div class="collapse d-lg-none" id="mobileMenu">
            <div class="py-2 border-top border-white-50 mt-2 d-flex flex-column gap-1">
                <a href="/accueil" class="nav-pub-link">Accueil</a>
                <a href="/statistiques" class="nav-pub-link">Statistiques</a>
                <a href="/donnees" class="nav-pub-link">Données</a>
                <a href="/guides" class="nav-pub-link">Guides</a>
            </div>
        </div>
    </div>
</nav>

<!-- Contenu -->
<main>
    <?= $content ?>
</main>

<!-- Pied de page -->
<footer class="pub-footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <img src="/assets/img/logo-anpe.png" alt="ANPE Niger" style="width:50px;height:50px;border-radius:50%;background:#fff;padding:4px">
                    <div>
                        <div class="text-white fw-bold">ANPE Niger</div>
                        <small class="text-white-50">Agence Nationale pour la Promotion de l'Emploi</small>
                    </div>
                </div>
                <p class="text-white-50 small">
                    Plateforme officielle de Déclaration Annuelle de la Main d'Œuvre (DAMO) du Niger.
                </p>
            </div>
            <div class="col-md-4">
                <h6 class="text-white fw-bold mb-3">Liens rapides</h6>
                <ul class="list-unstyled">
                    <li><a href="/statistiques" class="footer-link"><i class="bi bi-bar-chart me-1"></i>Statistiques</a></li>
                    <li><a href="/donnees" class="footer-link"><i class="bi bi-database me-1"></i>Données ouvertes</a></li>
                    <li><a href="/guides" class="footer-link"><i class="bi bi-book me-1"></i>Guides de remplissage</a></li>
                    <li><a href="/login" class="footer-link"><i class="bi bi-lock me-1"></i>Espace agents / admin</a></li>
                </ul>
            </div>
            <div class="col-md-4">
                <h6 class="text-white fw-bold mb-3">Contact</h6>
                <ul class="list-unstyled text-white-50 small">
                    <li><i class="bi bi-geo-alt me-2"></i>BP 13 222 NIAMEY – NIGER</li>
                    <li><i class="bi bi-telephone me-2"></i>Tél: 20 73 33 84</li>
                    <li><i class="bi bi-telephone-forward me-2"></i>Tél/Fax: 20 73 70 31</li>
                    <li><i class="bi bi-envelope me-2"></i>anpe-niger16@gmail.com</li>
                    <li><i class="bi bi-globe me-2"></i><a href="https://www.anpe-niger.ne" class="text-white-50">www.anpe-niger.ne</a></li>
                </ul>
            </div>
        </div>
        <hr class="border-white-50">
        <div class="d-flex flex-wrap justify-content-between text-white-50 small">
            <span>© <?= date('Y') ?> e-DAMO - ANPE Niger. Tous droits réservés.</span>
            <span>Version <?= APP_VERSION ?> | <?= APP_FULL_NAME ?></span>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="/assets/js/main.js"></script>
<script>
window.APP_URL = '<?= APP_URL ?>';
</script>
<?php if (isset($extraJs)): foreach ($extraJs as $js): ?>
<script src="<?= e($js) ?>"></script>
<?php endforeach; endif; ?>
</body>
</html>
