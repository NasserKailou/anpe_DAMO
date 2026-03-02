<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= e(APP_FULL_NAME) ?> — ANPE Niger">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($pageTitle ?? APP_NAME) ?> — e-DAMO</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= asset('img/favicon.ico') ?>">
    <!-- Bootstrap 5 (local) -->
    <link rel="stylesheet" href="<?= asset('css/bootstrap.min.css') ?>">
    <!-- Bootstrap Icons (local) -->
    <link rel="stylesheet" href="<?= asset('css/bootstrap-icons.min.css') ?>">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- CSS -->
    <link rel="stylesheet" href="<?= asset('css/main.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/admin.css') ?>">

    <?php if (isset($extraCss)): foreach ($extraCss as $css): ?>
    <link rel="stylesheet" href="<?= e($css) ?>">
    <?php endforeach; endif; ?>
</head>
<body class="admin-layout sidebar-mini">

<?php
// Chemin courant sans BASE_PATH pour les comparaisons actif/inactif
$_cp = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$_bp = defined('BASE_PATH') ? BASE_PATH : '';
if ($_bp && str_starts_with($_cp, $_bp)) $_cp = substr($_cp, strlen($_bp));
$_cp = '/' . ltrim($_cp, '/');
function _nav_active(string $prefix): string {
    global $_cp;
    return str_starts_with($_cp, $prefix) ? 'active' : '';
}
?>

<!-- ===== SIDEBAR ===== -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <img src="<?= asset('img/logo-anpe.png') ?>" alt="ANPE Niger" class="sidebar-logo">
        <div class="sidebar-brand-text">
            <span class="brand-name">e-DAMO</span>
            <small class="brand-sub">ANPE Niger</small>
        </div>
        <button class="sidebar-toggle-btn d-lg-none" id="sidebarClose">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <div class="sidebar-user">
        <div class="user-avatar">
            <?= strtoupper(substr(currentUser()['prenom'] ?? 'U', 0, 1) . substr(currentUser()['nom'] ?? '', 0, 1)) ?>
        </div>
        <div class="user-info">
            <span class="user-name"><?= e((currentUser()['prenom'] ?? '') . ' ' . (currentUser()['nom'] ?? '')) ?></span>
            <span class="user-role badge-role-<?= currentUser()['role'] ?? '' ?>">
                <?= match(currentUser()['role'] ?? '') {
                    'super_admin' => 'Super Admin',
                    'admin'       => 'Administrateur',
                    'agent'       => 'Agent',
                    default       => 'Utilisateur'
                } ?>
            </span>
        </div>
    </div>

    <nav class="sidebar-nav">
        <?php if (isAdmin()): ?>
        <div class="nav-section"><span class="nav-section-title">Administration</span></div>

        <a href="<?= url('admin/dashboard') ?>" class="nav-item <?= _nav_active('/admin/dashboard') ?>">
            <i class="bi bi-speedometer2"></i><span>Tableau de bord</span>
        </a>
        <a href="<?= url('admin/declarations') ?>" class="nav-item <?= _nav_active('/admin/declarations') ?>">
            <i class="bi bi-file-earmark-text"></i><span>Déclarations</span>
            <?php
            try {
                $pendingCount = \App\Models\Database::getInstance()->fetchScalar(
                    "SELECT COUNT(*) FROM declarations WHERE statut = 'soumise'"
                );
                if ($pendingCount > 0): ?>
                <span class="nav-badge"><?= $pendingCount ?></span>
            <?php endif; } catch(\Exception $e) {} ?>
        </a>
        <a href="<?= url('admin/statistiques') ?>" class="nav-item <?= _nav_active('/admin/statistiques') ?>">
            <i class="bi bi-bar-chart-line"></i><span>Statistiques</span>
        </a>

        <div class="nav-section"><span class="nav-section-title">Gestion</span></div>

        <a href="<?= url('admin/utilisateurs') ?>" class="nav-item <?= _nav_active('/admin/utilisateurs') ?>">
            <i class="bi bi-people"></i><span>Utilisateurs</span>
        </a>
        <a href="<?= url('admin/campagnes') ?>" class="nav-item <?= _nav_active('/admin/campagnes') ?>">
            <i class="bi bi-calendar3"></i><span>Campagnes DAMO</span>
        </a>
        <a href="<?= url('admin/branches') ?>" class="nav-item <?= _nav_active('/admin/branches') ?>">
            <i class="bi bi-diagram-3"></i><span>Branches d'activité</span>
        </a>
        <a href="<?= url('admin/guides') ?>" class="nav-item <?= _nav_active('/admin/guides') ?>">
            <i class="bi bi-book"></i><span>Guides & Documents</span>
        </a>
        <a href="<?= url('admin/parametres') ?>" class="nav-item <?= _nav_active('/admin/parametres') ?>">
            <i class="bi bi-gear"></i><span>Paramètres</span>
        </a>
        <a href="<?= url('admin/logs') ?>" class="nav-item <?= _nav_active('/admin/logs') ?>">
            <i class="bi bi-journal-text"></i><span>Journaux d'audit</span>
        </a>
        <?php endif; ?>

        <?php if (isAgent()): ?>
        <div class="nav-section"><span class="nav-section-title">Saisie DAMO</span></div>

        <a href="<?= url('agent/dashboard') ?>" class="nav-item <?= _nav_active('/agent/dashboard') ?>">
            <i class="bi bi-speedometer2"></i><span>Mon tableau de bord</span>
        </a>
        <a href="<?= url('agent/declarations') ?>" class="nav-item <?= _nav_active('/agent/declarations') ?>">
            <i class="bi bi-file-earmark-text"></i><span>Mes déclarations</span>
        </a>
        <a href="<?= url('agent/entreprises') ?>" class="nav-item <?= _nav_active('/agent/entreprises') ?>">
            <i class="bi bi-building"></i><span>Entreprises</span>
        </a>
        <a href="<?= url('guides') ?>" class="nav-item">
            <i class="bi bi-book"></i><span>Guides de saisie</span>
        </a>
        <?php endif; ?>

        <div class="nav-section"><span class="nav-section-title">Compte</span></div>
        <a href="<?= url('profil') ?>" class="nav-item <?= _nav_active('/profil') ?>">
            <i class="bi bi-person-circle"></i><span>Mon profil</span>
        </a>
        <a href="<?= url('accueil') ?>" class="nav-item" target="_blank" rel="noopener">
            <i class="bi bi-globe"></i><span>Site public</span>
        </a>
        <a href="<?= url('logout') ?>" class="nav-item text-danger">
            <i class="bi bi-box-arrow-right"></i><span>Déconnexion</span>
        </a>
    </nav>
</aside>

<!-- Overlay mobile -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- ===== CONTENU PRINCIPAL ===== -->
<div class="main-wrapper">

    <!-- Topbar -->
    <header class="topbar">
        <div class="topbar-left">
            <button class="sidebar-toggle" id="sidebarToggle">
                <i class="bi bi-list"></i>
            </button>
            <nav aria-label="breadcrumb" class="d-none d-md-flex">
                <ol class="breadcrumb mb-0">
                    <?php if (isset($breadcrumbs)): foreach ($breadcrumbs as $bc): ?>
                    <?php if ($bc['url']): ?>
                    <li class="breadcrumb-item"><a href="<?= e($bc['url']) ?>"><?= e($bc['label']) ?></a></li>
                    <?php else: ?>
                    <li class="breadcrumb-item active"><?= e($bc['label']) ?></li>
                    <?php endif; ?>
                    <?php endforeach; endif; ?>
                </ol>
            </nav>
        </div>
        <div class="topbar-right">
            <!-- Notifications -->
            <div class="dropdown">
                <button class="topbar-btn" data-bs-toggle="dropdown" id="notifBtn" title="Notifications">
                    <i class="bi bi-bell"></i>
                    <span class="topbar-badge" id="notifCount" style="display:none">0</span>
                </button>
                <div class="dropdown-menu dropdown-menu-end notif-dropdown" id="notifDropdown">
                    <div class="notif-header">
                        <strong>Notifications</strong>
                        <a href="#" class="mark-all-read small">Tout lire</a>
                    </div>
                    <div class="notif-list" id="notifList">
                        <p class="text-center text-muted p-3 mb-0">Aucune notification</p>
                    </div>
                </div>
            </div>

            <!-- Utilisateur dropdown -->
            <div class="dropdown">
                <button class="topbar-user" data-bs-toggle="dropdown">
                    <div class="user-avatar-sm">
                        <?= strtoupper(substr(currentUser()['prenom'] ?? 'U', 0, 1) . substr(currentUser()['nom'] ?? '', 0, 1)) ?>
                    </div>
                    <span class="d-none d-md-inline fw-500"><?= e(currentUser()['prenom'] ?? '') ?></span>
                    <i class="bi bi-chevron-down" style="font-size:.7rem"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow">
                    <li>
                        <a class="dropdown-item" href="<?= url('profil') ?>">
                            <i class="bi bi-person-circle me-2 text-primary"></i>Mon profil
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="<?= url('accueil') ?>" target="_blank">
                            <i class="bi bi-globe me-2 text-info"></i>Site public
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item text-danger" href="<?= url('logout') ?>">
                            <i class="bi bi-box-arrow-right me-2"></i>Déconnexion
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <!-- Page content -->
    <main class="page-content">
        <?php if ($flash): ?>
        <div class="flash-container">
            <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : e($flash['type']) ?> alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
                <i class="bi bi-<?= match($flash['type']) {
                    'success'         => 'check-circle-fill',
                    'error','danger'  => 'exclamation-triangle-fill',
                    'warning'         => 'exclamation-circle-fill',
                    default           => 'info-circle-fill'
                } ?>"></i>
                <div class="flex-1"><?= e($flash['message']) ?></div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
        <?php endif; ?>

        <?= $content ?>
    </main>

    <footer class="page-footer">
        <span>© <?= date('Y') ?> <?= APP_NAME ?> — ANPE Niger</span>
        <span>Version <?= APP_VERSION ?></span>
    </footer>
</div>

<!-- Scripts (locaux) -->
<script src="<?= asset('js/bootstrap.bundle.min.js') ?>"></script>
<script src="<?= asset('js/chart.umd.min.js') ?>"></script>
<script src="<?= asset('js/sweetalert2.all.min.js') ?>"></script>
<script>
window.CSRF_TOKEN = '<?= csrfToken() ?>';
window.APP_URL    = '<?= APP_URL ?>';
window.APP_BASE   = '<?= defined('BASE_PATH') ? BASE_PATH : '' ?>';
</script>
<script src="<?= asset('js/main.js') ?>"></script>
<?php if (isset($extraJs)): foreach ($extraJs as $js): ?>
<script src="<?= e($js) ?>"></script>
<?php endforeach; endif; ?>
</body>
</html>
