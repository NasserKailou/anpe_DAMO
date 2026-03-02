<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= e(APP_FULL_NAME) ?> - ANPE Niger">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($pageTitle ?? APP_NAME) ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/assets/img/favicon.ico">
    
    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- CSS principal -->
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="/assets/css/admin.css">
    
    <?php if (isset($extraCss)): foreach ($extraCss as $css): ?>
    <link rel="stylesheet" href="<?= e($css) ?>">
    <?php endforeach; endif; ?>
</head>
<body class="admin-layout sidebar-mini">

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <img src="/assets/img/logo-anpe.png" alt="ANPE Niger" class="sidebar-logo">
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
        <!-- Menu Administration -->
        <div class="nav-section">
            <span class="nav-section-title">Administration</span>
        </div>
        <a href="/admin/dashboard" class="nav-item <?= strpos($_SERVER['REQUEST_URI'], '/admin/dashboard') !== false ? 'active' : '' ?>">
            <i class="bi bi-speedometer2"></i>
            <span>Tableau de bord</span>
        </a>
        <a href="/admin/declarations" class="nav-item <?= strpos($_SERVER['REQUEST_URI'], '/admin/declarations') !== false ? 'active' : '' ?>">
            <i class="bi bi-file-earmark-text"></i>
            <span>Déclarations</span>
            <?php 
            $pendingCount = \App\Models\Database::getInstance()->fetchScalar(
                "SELECT COUNT(*) FROM declarations WHERE statut = 'soumise'"
            );
            if ($pendingCount > 0): ?>
            <span class="nav-badge"><?= $pendingCount ?></span>
            <?php endif; ?>
        </a>
        <a href="/admin/statistiques" class="nav-item <?= strpos($_SERVER['REQUEST_URI'], '/admin/statistiques') !== false ? 'active' : '' ?>">
            <i class="bi bi-bar-chart-line"></i>
            <span>Statistiques</span>
        </a>

        <div class="nav-section">
            <span class="nav-section-title">Gestion</span>
        </div>
        <a href="/admin/utilisateurs" class="nav-item <?= strpos($_SERVER['REQUEST_URI'], '/admin/utilisateurs') !== false ? 'active' : '' ?>">
            <i class="bi bi-people"></i>
            <span>Utilisateurs</span>
        </a>
        <a href="/admin/campagnes" class="nav-item <?= strpos($_SERVER['REQUEST_URI'], '/admin/campagnes') !== false ? 'active' : '' ?>">
            <i class="bi bi-calendar3"></i>
            <span>Campagnes DAMO</span>
        </a>
        <a href="/admin/branches" class="nav-item <?= strpos($_SERVER['REQUEST_URI'], '/admin/branches') !== false ? 'active' : '' ?>">
            <i class="bi bi-diagram-3"></i>
            <span>Branches d'activité</span>
        </a>
        <a href="/admin/guides" class="nav-item <?= strpos($_SERVER['REQUEST_URI'], '/admin/guides') !== false ? 'active' : '' ?>">
            <i class="bi bi-book"></i>
            <span>Guides & Documents</span>
        </a>
        <a href="/admin/parametres" class="nav-item <?= strpos($_SERVER['REQUEST_URI'], '/admin/parametres') !== false ? 'active' : '' ?>">
            <i class="bi bi-gear"></i>
            <span>Paramètres</span>
        </a>
        <a href="/admin/logs" class="nav-item <?= strpos($_SERVER['REQUEST_URI'], '/admin/logs') !== false ? 'active' : '' ?>">
            <i class="bi bi-journal-text"></i>
            <span>Journaux d'audit</span>
        </a>
        <?php endif; ?>

        <?php if (isAgent()): ?>
        <!-- Menu Agent -->
        <div class="nav-section">
            <span class="nav-section-title">Saisie DAMO</span>
        </div>
        <a href="/agent/dashboard" class="nav-item <?= strpos($_SERVER['REQUEST_URI'], '/agent/dashboard') !== false ? 'active' : '' ?>">
            <i class="bi bi-speedometer2"></i>
            <span>Mon tableau de bord</span>
        </a>
        <a href="/agent/declarations" class="nav-item <?= strpos($_SERVER['REQUEST_URI'], '/agent/declarations') !== false ? 'active' : '' ?>">
            <i class="bi bi-file-earmark-text"></i>
            <span>Mes déclarations</span>
        </a>
        <a href="/agent/entreprises" class="nav-item <?= strpos($_SERVER['REQUEST_URI'], '/agent/entreprises') !== false ? 'active' : '' ?>">
            <i class="bi bi-building"></i>
            <span>Entreprises</span>
        </a>
        <a href="/guides" class="nav-item">
            <i class="bi bi-book"></i>
            <span>Guides de saisie</span>
        </a>
        <?php endif; ?>

        <div class="nav-section">
            <span class="nav-section-title">Compte</span>
        </div>
        <a href="/profil" class="nav-item <?= strpos($_SERVER['REQUEST_URI'], '/profil') !== false ? 'active' : '' ?>">
            <i class="bi bi-person-circle"></i>
            <span>Mon profil</span>
        </a>
        <a href="/accueil" class="nav-item" target="_blank">
            <i class="bi bi-globe"></i>
            <span>Site public</span>
        </a>
        <a href="/logout" class="nav-item text-danger">
            <i class="bi bi-box-arrow-right"></i>
            <span>Déconnexion</span>
        </a>
    </nav>
</aside>

<!-- Overlay mobile -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Contenu principal -->
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
                <button class="topbar-btn" data-bs-toggle="dropdown" id="notifBtn">
                    <i class="bi bi-bell"></i>
                    <span class="topbar-badge" id="notifCount" style="display:none">0</span>
                </button>
                <div class="dropdown-menu dropdown-menu-end notif-dropdown" id="notifDropdown">
                    <div class="notif-header">
                        <strong>Notifications</strong>
                        <a href="#" class="mark-all-read">Tout lire</a>
                    </div>
                    <div class="notif-list" id="notifList">
                        <p class="text-center text-muted p-3">Aucune notification</p>
                    </div>
                </div>
            </div>
            
            <!-- Utilisateur -->
            <div class="dropdown">
                <button class="topbar-user" data-bs-toggle="dropdown">
                    <div class="user-avatar-sm">
                        <?= strtoupper(substr(currentUser()['prenom'] ?? 'U', 0, 1) . substr(currentUser()['nom'] ?? '', 0, 1)) ?>
                    </div>
                    <span class="d-none d-md-inline"><?= e(currentUser()['prenom'] ?? '') ?></span>
                    <i class="bi bi-chevron-down"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="/profil"><i class="bi bi-person me-2"></i>Mon profil</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="/logout"><i class="bi bi-box-arrow-right me-2"></i>Déconnexion</a></li>
                </ul>
            </div>
        </div>
    </header>

    <!-- Contenu de la page -->
    <main class="page-content">
        <!-- Messages Flash -->
        <?php if ($flash): ?>
        <div class="flash-container">
            <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?> alert-dismissible fade show" role="alert">
                <i class="bi bi-<?= match($flash['type']) {
                    'success' => 'check-circle',
                    'error', 'danger' => 'exclamation-triangle',
                    'warning' => 'exclamation-circle',
                    default => 'info-circle'
                } ?> me-2"></i>
                <?= e($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
        <?php endif; ?>

        <?= $content ?>
    </main>

    <footer class="page-footer">
        <span>© <?= date('Y') ?> <?= APP_NAME ?> - ANPE Niger | <?= APP_FULL_NAME ?></span>
        <span>Version <?= APP_VERSION ?></span>
    </footer>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script src="/assets/js/main.js"></script>

<?php if (isset($extraJs)): foreach ($extraJs as $js): ?>
<script src="<?= e($js) ?>"></script>
<?php endforeach; endif; ?>

<script>
// Token CSRF pour les requêtes AJAX
const CSRF_TOKEN = '<?= csrfToken() ?>';
const APP_URL    = '<?= APP_URL ?>';
</script>
</body>
</html>
