<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? APP_NAME) ?> — e-DAMO</title>
    <meta name="description" content="Plateforme de Déclaration Annuelle de la Main d'Œuvre — ANPE Niger">

    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
    /* ===== RESET & BASE ===== */
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
        --primary:      #1a4f8a;
        --primary-dark: #0d3261;
        --primary-mid:  #1e6fba;
        --accent:       #f59e0b;
        --accent-dark:  #d97706;
        --light-bg:     #f0f4f8;
        --card-shadow:  0 24px 64px rgba(0,0,0,.22);
        --radius:       18px;
        --transition:   .3s cubic-bezier(.4,0,.2,1);
    }

    body {
        font-family: 'Inter', sans-serif;
        min-height: 100vh;
        background: linear-gradient(135deg, #0b2141 0%, #1a4f8a 45%, #1e6fba 100%);
        display: flex;
        flex-direction: column;
        position: relative;
        overflow-x: hidden;
    }

    /* ===== CERCLES DÉCORATIFS ===== */
    body::before, body::after {
        content: '';
        position: fixed;
        border-radius: 50%;
        opacity: .07;
        pointer-events: none;
    }
    body::before {
        width: 600px; height: 600px;
        background: #fff;
        top: -200px; right: -200px;
    }
    body::after {
        width: 400px; height: 400px;
        background: var(--accent);
        bottom: -150px; left: -150px;
    }

    /* ===== WRAPPER ===== */
    .auth-wrapper {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px 16px;
        min-height: 100vh;
        position: relative;
        z-index: 2;
    }

    /* ===== CARD PRINCIPALE ===== */
    .auth-card {
        width: 100%;
        max-width: 440px;
        background: #fff;
        border-radius: var(--radius);
        box-shadow: var(--card-shadow);
        overflow: hidden;
        animation: slideUp .45s cubic-bezier(.4,0,.2,1);
    }

    @keyframes slideUp {
        from { opacity: 0; transform: translateY(40px) scale(.97); }
        to   { opacity: 1; transform: translateY(0) scale(1); }
    }

    /* ===== EN-TÊTE CARD ===== */
    .auth-card-header {
        background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 50%, var(--primary-mid) 100%);
        padding: 36px 40px 28px;
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    .auth-card-header::before {
        content: '';
        position: absolute;
        width: 200px; height: 200px;
        background: rgba(255,255,255,.06);
        border-radius: 50%;
        top: -80px; right: -60px;
    }

    .auth-logo-wrap {
        width: 76px; height: 76px;
        background: #fff;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 16px;
        box-shadow: 0 8px 24px rgba(0,0,0,.25);
        padding: 10px;
        position: relative; z-index: 1;
    }

    .auth-logo-wrap img {
        width: 100%; height: 100%;
        object-fit: contain;
    }

    .auth-card-header h1 {
        font-size: 2rem;
        font-weight: 800;
        color: #fff;
        letter-spacing: 3px;
        line-height: 1;
        position: relative; z-index: 1;
    }

    .auth-card-header h1 span {
        display: block;
        font-size: .72rem;
        font-weight: 400;
        letter-spacing: 1.5px;
        color: rgba(255,255,255,.72);
        margin-top: 4px;
    }

    /* ===== BODY CARD ===== */
    .auth-card-body {
        padding: 32px 40px 28px;
    }

    .auth-page-title {
        font-size: 1.15rem;
        font-weight: 700;
        color: var(--primary);
        text-align: center;
        margin-bottom: 24px;
    }

    /* ===== FORM ELEMENTS ===== */
    .form-label {
        font-size: .83rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 6px;
    }

    .input-group-text {
        background: var(--light-bg);
        border: 1.5px solid #d1d5db;
        border-right: none;
        color: var(--primary);
        font-size: 1rem;
        padding: 0 14px;
    }

    .form-control {
        border: 1.5px solid #d1d5db;
        border-left: none;
        border-radius: 0 10px 10px 0;
        padding: 11px 14px;
        font-size: .9rem;
        color: #111827;
        transition: border-color var(--transition), box-shadow var(--transition);
        background: #fff;
    }

    .form-control:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(26,79,138,.12);
        outline: none;
    }

    .input-group .input-group-text:first-child {
        border-radius: 10px 0 0 10px;
    }

    .toggle-password {
        border: 1.5px solid #d1d5db;
        border-left: none;
        background: var(--light-bg);
        color: #6b7280;
        border-radius: 0 10px 10px 0 !important;
        padding: 0 12px;
        transition: background var(--transition);
    }

    .toggle-password:hover { background: #e5e7eb; color: var(--primary); }

    .form-check-input:checked {
        background-color: var(--primary);
        border-color: var(--primary);
    }

    .form-check-label { font-size: .83rem; color: #6b7280; }

    .forgot-link {
        font-size: .83rem;
        font-weight: 500;
        color: var(--primary);
        text-decoration: none;
        transition: color var(--transition);
    }
    .forgot-link:hover { color: var(--accent-dark); text-decoration: underline; }

    /* ===== BOUTON CONNEXION ===== */
    .btn-login {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-mid) 100%);
        color: #fff;
        border: none;
        border-radius: 10px;
        padding: 13px;
        font-size: .95rem;
        font-weight: 700;
        letter-spacing: .3px;
        width: 100%;
        transition: all var(--transition);
        position: relative;
        overflow: hidden;
    }

    .btn-login::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, var(--accent) 0%, var(--accent-dark) 100%);
        opacity: 0;
        transition: opacity var(--transition);
    }

    .btn-login:hover::after { opacity: 1; }
    .btn-login span { position: relative; z-index: 1; }

    .btn-login:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(26,79,138,.35);
    }

    .btn-login:active { transform: translateY(0); }

    /* ===== DIVIDER ===== */
    .auth-divider {
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 20px 0;
    }
    .auth-divider::before, .auth-divider::after {
        content: '';
        flex: 1;
        height: 1px;
        background: #e5e7eb;
    }
    .auth-divider span { font-size: .78rem; color: #9ca3af; white-space: nowrap; }

    /* ===== LIEN RETOUR SITE PUBLIC ===== */
    .btn-public-link {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%;
        padding: 11px;
        border: 1.5px solid #d1d5db;
        border-radius: 10px;
        background: #fff;
        color: var(--primary);
        font-size: .88rem;
        font-weight: 500;
        text-decoration: none;
        transition: all var(--transition);
    }
    .btn-public-link:hover {
        background: var(--light-bg);
        border-color: var(--primary);
        color: var(--primary-dark);
        transform: translateY(-1px);
    }

    /* ===== FOOTER ===== */
    .auth-footer-bar {
        text-align: center;
        color: rgba(255,255,255,.55);
        font-size: .75rem;
        padding: 16px;
        position: relative; z-index: 2;
    }

    /* ===== ALERTS ===== */
    .alert { border-radius: 10px; font-size: .87rem; border: none; }
    .alert-danger  { background: #fef2f2; color: #b91c1c; }
    .alert-success { background: #f0fdf4; color: #15803d; }
    .alert-warning { background: #fffbeb; color: #92400e; }
    .alert-info    { background: #eff6ff; color: #1d4ed8; }

    /* ===== BADGES SÉCURITÉ ===== */
    .security-badges {
        display: flex;
        justify-content: center;
        gap: 16px;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid #f3f4f6;
    }

    .sec-badge {
        display: flex;
        align-items: center;
        gap: 5px;
        font-size: .73rem;
        color: #9ca3af;
    }

    .sec-badge i { font-size: .9rem; color: #6b7280; }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 480px) {
        .auth-card-header { padding: 28px 24px 22px; }
        .auth-card-body   { padding: 24px 24px 20px; }
        .auth-card-header h1 { font-size: 1.7rem; }
    }
    </style>
</head>
<body>

<div class="auth-wrapper">
    <div class="auth-card">

        <!-- En-tête avec logo -->
        <div class="auth-card-header">
            <div class="auth-logo-wrap">
                <img src="<?= asset('img/logo-anpe.png') ?>" alt="ANPE Niger">
            </div>
            <h1><?= APP_NAME ?>
                <span>Agence Nationale pour la Promotion de l'Emploi</span>
            </h1>
        </div>

        <!-- Corps -->
        <div class="auth-card-body">

            <?php if (isset($pageTitle) && $pageTitle !== APP_NAME): ?>
            <div class="auth-page-title"><?= e($pageTitle) ?></div>
            <?php endif; ?>

            <?php if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : e($flash['type']) ?> mb-4 d-flex align-items-center gap-2">
                <i class="bi bi-<?= $flash['type'] === 'success' ? 'check-circle' : ($flash['type'] === 'error' ? 'exclamation-circle' : 'info-circle') ?>"></i>
                <?= e($flash['message']) ?>
            </div>
            <?php endif; ?>

            <?= $content ?>

            <!-- Badges sécurité -->
            <div class="security-badges">
                <span class="sec-badge"><i class="bi bi-shield-lock"></i> Connexion sécurisée</span>
                <span class="sec-badge"><i class="bi bi-lock"></i> Données chiffrées</span>
                <span class="sec-badge"><i class="bi bi-patch-check"></i> Conforme ANPE</span>
            </div>
        </div>

    </div>
</div>

<div class="auth-footer-bar">
    © <?= date('Y') ?> e-DAMO — ANPE Niger &nbsp;|&nbsp; Plateforme Digitale de Déclaration Annuelle de la Main d'Œuvre
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Toggle affichage mot de passe
document.querySelectorAll('.toggle-password').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var targetId = this.dataset.target;
        var input = document.getElementById(targetId);
        if (!input) return;
        var isHidden = input.type === 'password';
        input.type = isHidden ? 'text' : 'password';
        var icon = this.querySelector('i');
        if (icon) {
            icon.className = isHidden ? 'bi bi-eye-slash' : 'bi bi-eye';
        }
    });
});

// Spinner sur submit
var loginForm = document.getElementById('loginForm');
if (loginForm) {
    loginForm.addEventListener('submit', function() {
        var btn = document.getElementById('loginBtn');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Connexion en cours…';
        }
    });
}
</script>
</body>
</html>
