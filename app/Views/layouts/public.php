<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="e-DAMO — Déclaration Annuelle de la Main d'Œuvre — ANPE Niger">
    <title><?= e($pageTitle ?? APP_NAME) ?> — e-DAMO</title>
    <link rel="icon" type="image/png" href="<?= asset('img/favicon.ico') ?>">

    <!-- Bootstrap 5 (local) -->
    <link rel="stylesheet" href="<?= asset('css/bootstrap.min.css') ?>">
    <!-- Bootstrap Icons (local) -->
    <link rel="stylesheet" href="<?= asset('css/bootstrap-icons.min.css') ?>">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- CSS -->
    <link rel="stylesheet" href="<?= asset('css/main.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/public.css') ?>">
</head>
<body class="public-layout">

<?php
// Déterminer la page courante (sans BASE_PATH) pour le menu actif
$_currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$_bp = defined('BASE_PATH') ? BASE_PATH : '';
if ($_bp && str_starts_with($_currentPath, $_bp)) {
    $_currentPath = substr($_currentPath, strlen($_bp));
}
$_currentPath = '/' . ltrim($_currentPath, '/');
?>

<!-- ===== NAVBAR ===== -->
<nav class="pub-navbar navbar navbar-expand-lg sticky-top">
    <div class="container">

        <!-- Logo + Titre -->
        <a href="<?= url('accueil') ?>" class="navbar-brand d-flex align-items-center gap-2 text-decoration-none">
            <div class="pub-logo-wrap">
                <img src="<?= asset('img/logo-anpe.png') ?>" alt="ANPE Niger">
            </div>
            <div class="lh-1">
                <span class="pub-brand-title">e-DAMO</span>
                <span class="pub-brand-sub d-none d-md-block">ANPE Niger</span>
            </div>
        </a>

        <!-- Toggler mobile -->
        <button class="navbar-toggler pub-toggler" type="button"
                data-bs-toggle="collapse" data-bs-target="#pubNavMenu"
                aria-expanded="false" aria-label="Menu">
            <i class="bi bi-list"></i>
        </button>

        <!-- Menu -->
        <div class="collapse navbar-collapse" id="pubNavMenu">
            <ul class="navbar-nav mx-auto gap-1">
                <li class="nav-item">
                    <a href="<?= url('accueil') ?>"
                       class="pub-nav-link <?= in_array($_currentPath, ['/', '/accueil']) ? 'active' : '' ?>">
                        <i class="bi bi-house-fill"></i><span>Accueil</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= url('statistiques') ?>"
                       class="pub-nav-link <?= str_starts_with($_currentPath, '/statistiques') ? 'active' : '' ?>">
                        <i class="bi bi-bar-chart-fill"></i><span>Statistiques</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= url('donnees') ?>"
                       class="pub-nav-link <?= str_starts_with($_currentPath, '/donnees') ? 'active' : '' ?>">
                        <i class="bi bi-database-fill"></i><span>Données</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= url('guides') ?>"
                       class="pub-nav-link <?= str_starts_with($_currentPath, '/guides') ? 'active' : '' ?>">
                        <i class="bi bi-book-fill"></i><span>Guides</span>
                    </a>
                </li>
            </ul>

            <div class="d-flex align-items-center gap-2">
                <?php if (isAuthenticated()): ?>
                <a href="<?= url(isAdmin() ? 'admin/dashboard' : 'agent/dashboard') ?>"
                   class="btn btn-warning btn-sm fw-bold px-3">
                    <i class="bi bi-grid-fill me-1"></i>Mon espace
                </a>
                <?php else: ?>
                <a href="<?= url('login') ?>" class="btn pub-btn-login btn-sm">
                    <i class="bi bi-box-arrow-in-right me-1"></i>Connexion
                </a>
                <?php endif; ?>
            </div>
        </div>

    </div>
</nav>

<!-- ===== CONTENU ===== -->
<main>
    <?= $content ?>
</main>

<!-- ===== FOOTER ===== -->
<footer class="pub-footer">
    <div class="container">
        <div class="row g-4">

            <!-- Bloc ANPE -->
            <div class="col-md-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="pub-footer-logo">
                        <img src="<?= asset('img/logo-anpe.png') ?>" alt="ANPE Niger">
                    </div>
                    <div>
                        <div class="text-white fw-bold">ANPE Niger</div>
                        <small class="text-white-50">Agence Nationale pour la Promotion de l'Emploi</small>
                    </div>
                </div>
                <p class="text-white-50 small lh-base">
                    Plateforme officielle de Déclaration Annuelle<br>de la Main d'Œuvre (DAMO) du Niger.
                </p>
            </div>

            <!-- Liens rapides -->
            <div class="col-md-4">
                <h6 class="text-white fw-bold mb-3 text-uppercase" style="letter-spacing:.5px;font-size:.82rem">
                    <i class="bi bi-link-45deg me-1"></i>Liens rapides
                </h6>
                <ul class="list-unstyled">
                    <li><a href="<?= url('statistiques') ?>" class="pub-footer-link"><i class="bi bi-bar-chart me-2"></i>Statistiques nationales</a></li>
                    <li><a href="<?= url('donnees') ?>"      class="pub-footer-link"><i class="bi bi-database me-2"></i>Données ouvertes</a></li>
                    <li><a href="<?= url('guides') ?>"       class="pub-footer-link"><i class="bi bi-book me-2"></i>Guides de remplissage</a></li>
                    <li><a href="<?= url('login') ?>"        class="pub-footer-link"><i class="bi bi-lock me-2"></i>Espace agents / admin</a></li>
                </ul>
            </div>

            <!-- Contact -->
            <div class="col-md-4">
                <h6 class="text-white fw-bold mb-3 text-uppercase" style="letter-spacing:.5px;font-size:.82rem">
                    <i class="bi bi-geo-alt me-1"></i>Contact
                </h6>
                <ul class="list-unstyled text-white-50 small">
                    <li class="mb-1"><i class="bi bi-geo-alt-fill me-2 text-warning"></i>BP 13 222 NIAMEY — NIGER</li>
                    <li class="mb-1"><i class="bi bi-telephone-fill me-2 text-warning"></i>+227 20 73 33 84</li>
                    <li class="mb-1"><i class="bi bi-telephone-forward-fill me-2 text-warning"></i>Fax: +227 20 73 70 31</li>
                    <li class="mb-1"><i class="bi bi-envelope-fill me-2 text-warning"></i>anpe-niger16@gmail.com</li>
                    <li><i class="bi bi-globe me-2 text-warning"></i>
                        <a href="https://www.anpe-niger.ne" target="_blank" rel="noopener" class="text-white-50 text-decoration-none">www.anpe-niger.ne</a>
                    </li>
                </ul>
            </div>

        </div>
        <hr class="border-white-50 my-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center text-white-50 small gap-2">
            <span>© <?= date('Y') ?> e-DAMO — ANPE Niger. Tous droits réservés.</span>
            <span class="d-flex align-items-center gap-1">
                <i class="bi bi-shield-check text-success"></i>
                Version <?= APP_VERSION ?> — <?= APP_FULL_NAME ?>
            </span>
        </div>
    </div>
</footer>

<!-- Scripts (locaux) -->
<script src="<?= asset('js/bootstrap.bundle.min.js') ?>"></script>
<script src="<?= asset('js/chart.umd.min.js') ?>"></script>
<script>
window.APP_BASE = '<?= defined('BASE_PATH') ? BASE_PATH : '' ?>';
window.APP_URL  = '<?= APP_URL ?>';
</script>
<script src="<?= asset('js/main.js') ?>"></script>
<?php if (isset($extraJs)): foreach ($extraJs as $js): ?>
<script src="<?= e($js) ?>"></script>
<?php endforeach; endif; ?>
</body>
</html>
