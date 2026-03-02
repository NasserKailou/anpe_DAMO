<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? APP_NAME) ?> — e-DAMO</title>
    <meta name="description" content="Plateforme de Déclaration Annuelle de la Main d'Œuvre — ANPE Niger">
    <link rel="icon" href="<?= asset('img/logo-anpe.svg') ?>" type="image/svg+xml">

    <!-- Bootstrap 5 (local) -->
    <link rel="stylesheet" href="<?= asset('css/bootstrap.min.css') ?>">
    <!-- Bootstrap Icons (local) -->
    <link rel="stylesheet" href="<?= asset('css/bootstrap-icons.min.css') ?>">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
    /* ===== VARIABLES ===== */
    :root {
        --primary:       #1a4f8a;
        --primary-dark:  #0d3261;
        --primary-light: #2563b0;
        --accent:        #f59e0b;
        --accent-dark:   #d97706;
        --white:         #ffffff;
        --gray-50:       #f9fafb;
        --gray-100:      #f3f4f6;
        --gray-200:      #e5e7eb;
        --gray-400:      #9ca3af;
        --gray-600:      #4b5563;
        --gray-800:      #1f2937;
        --shadow-lg:     0 20px 60px rgba(0,0,0,.28);
        --shadow-md:     0 8px 24px rgba(0,0,0,.15);
        --radius-xl:     20px;
        --radius-lg:     12px;
        --radius-md:     8px;
        --transition:    .28s cubic-bezier(.4,0,.2,1);
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    /* ===== FOND ANIMÉ ===== */
    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        background: linear-gradient(145deg, #061529 0%, #0d3261 35%, #1a4f8a 65%, #1e6fba 100%);
        position: relative;
        overflow-x: hidden;
    }

    /* Particules décoratives */
    body::before {
        content: '';
        position: fixed;
        inset: 0;
        background:
            radial-gradient(circle at 15% 85%, rgba(245,158,11,.08) 0%, transparent 40%),
            radial-gradient(circle at 85% 10%, rgba(255,255,255,.05) 0%, transparent 40%),
            radial-gradient(circle at 50% 50%, rgba(30,111,186,.1) 0%, transparent 60%);
        pointer-events: none;
        z-index: 0;
    }

    /* Cercles flottants */
    .deco-circle {
        position: fixed;
        border-radius: 50%;
        pointer-events: none;
        z-index: 0;
    }
    .deco-circle-1 {
        width: 500px; height: 500px;
        border: 1px solid rgba(255,255,255,.04);
        top: -200px; right: -100px;
        animation: rotateSlow 40s linear infinite;
    }
    .deco-circle-2 {
        width: 350px; height: 350px;
        border: 1px solid rgba(245,158,11,.08);
        bottom: -150px; left: -100px;
        animation: rotateSlow 30s linear infinite reverse;
    }
    .deco-circle-3 {
        width: 200px; height: 200px;
        background: rgba(245,158,11,.04);
        top: 30%; left: 5%;
        animation: float 8s ease-in-out infinite;
    }

    @keyframes rotateSlow { to { transform: rotate(360deg); } }
    @keyframes float {
        0%, 100% { transform: translateY(0); }
        50%       { transform: translateY(-20px); }
    }

    /* ===== WRAPPER ===== */
    .auth-wrapper {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 28px 16px;
        position: relative;
        z-index: 2;
    }

    /* ===== CARD ===== */
    .auth-card {
        width: 100%;
        max-width: 460px;
        border-radius: var(--radius-xl);
        box-shadow: var(--shadow-lg);
        overflow: hidden;
        animation: slideUp .5s cubic-bezier(.4,0,.2,1) both;
        background: var(--white);
    }

    @keyframes slideUp {
        from { opacity: 0; transform: translateY(50px) scale(.96); }
        to   { opacity: 1; transform: translateY(0)   scale(1); }
    }

    /* ===== EN-TÊTE ===== */
    .auth-header {
        background: linear-gradient(150deg, var(--primary-dark) 0%, var(--primary) 55%, var(--primary-light) 100%);
        padding: 40px 40px 32px;
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    .auth-header::before {
        content: '';
        position: absolute;
        width: 280px; height: 280px;
        background: rgba(255,255,255,.05);
        border-radius: 50%;
        top: -120px; right: -80px;
    }
    .auth-header::after {
        content: '';
        position: absolute;
        width: 180px; height: 180px;
        background: rgba(245,158,11,.06);
        border-radius: 50%;
        bottom: -90px; left: -60px;
    }

    /* Logo cercle */
    .auth-logo-ring {
        width: 90px; height: 90px;
        background: var(--white);
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 18px;
        box-shadow: 0 0 0 4px rgba(255,255,255,.2), 0 10px 30px rgba(0,0,0,.3);
        position: relative; z-index: 1;
        animation: pulse 3s ease-in-out infinite;
    }

    @keyframes pulse {
        0%, 100% { box-shadow: 0 0 0 4px rgba(255,255,255,.2), 0 10px 30px rgba(0,0,0,.3); }
        50%       { box-shadow: 0 0 0 8px rgba(255,255,255,.12), 0 10px 30px rgba(0,0,0,.3); }
    }

    .auth-logo-ring img {
        width: 68px; height: 68px;
        object-fit: contain;
    }

    .auth-header-title {
        font-size: 2.1rem;
        font-weight: 800;
        color: var(--white);
        letter-spacing: 3px;
        line-height: 1;
        position: relative; z-index: 1;
        text-shadow: 0 2px 8px rgba(0,0,0,.25);
    }

    .auth-header-sub {
        font-size: .72rem;
        font-weight: 400;
        letter-spacing: 1.5px;
        color: rgba(255,255,255,.65);
        display: block;
        margin-top: 6px;
        position: relative; z-index: 1;
    }

    /* Bande dorée décorative -->
    .auth-header-stripe {
        height: 3px;
        background: linear-gradient(90deg, transparent, var(--accent), transparent);
        margin-top: 20px;
        position: relative; z-index: 1;
    }

    /* ===== CORPS ===== */
    .auth-body {
        padding: 32px 40px 28px;
        background: var(--white);
    }

    .auth-page-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--primary);
        text-align: center;
        margin-bottom: 22px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    /* ===== FORM ===== */
    .form-label {
        font-size: .82rem;
        font-weight: 600;
        color: var(--gray-800);
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .form-label i { color: var(--primary); font-size: .9rem; }

    .input-group-text {
        background: var(--gray-50);
        border: 1.5px solid var(--gray-200);
        border-right: none;
        color: var(--primary);
        font-size: 1rem;
        padding: 0 14px;
        border-radius: var(--radius-md) 0 0 var(--radius-md);
    }

    .form-control {
        border: 1.5px solid var(--gray-200);
        border-left: none;
        padding: 11px 14px;
        font-size: .9rem;
        color: var(--gray-800);
        background: var(--white);
        transition: border-color var(--transition), box-shadow var(--transition);
    }

    .form-control:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(26,79,138,.1);
        outline: none;
        background: var(--white);
    }

    .form-control.border-left-radius-0 {
        border-radius: 0 var(--radius-md) var(--radius-md) 0;
    }

    /* Champ mot de passe avec bouton toggle */
    .password-group .form-control {
        border-radius: 0;
        border-right: none;
    }

    .toggle-password {
        border: 1.5px solid var(--gray-200);
        border-left: none;
        background: var(--gray-50);
        color: var(--gray-400);
        border-radius: 0 var(--radius-md) var(--radius-md) 0 !important;
        padding: 0 13px;
        transition: all var(--transition);
        cursor: pointer;
    }

    .toggle-password:hover {
        background: var(--gray-100);
        color: var(--primary);
        border-color: var(--primary-light);
    }

    /* Remember + Forgot */
    .form-options {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin: 4px 0 20px;
    }

    .form-check-input:checked {
        background-color: var(--primary);
        border-color: var(--primary);
    }

    .form-check-label {
        font-size: .83rem;
        color: var(--gray-600);
    }

    .forgot-link {
        font-size: .83rem;
        font-weight: 500;
        color: var(--primary);
        text-decoration: none;
        transition: color var(--transition);
    }

    .forgot-link:hover {
        color: var(--accent-dark);
        text-decoration: underline;
    }

    /* ===== BOUTON CONNEXION ===== */
    .btn-connexion {
        width: 100%;
        padding: 13px 20px;
        border: none;
        border-radius: var(--radius-md);
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        color: var(--white);
        font-size: .95rem;
        font-weight: 700;
        letter-spacing: .4px;
        position: relative;
        overflow: hidden;
        transition: all var(--transition);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }

    .btn-connexion::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, var(--accent) 0%, var(--accent-dark) 100%);
        opacity: 0;
        transition: opacity var(--transition);
    }

    .btn-connexion:hover::before { opacity: 1; }
    .btn-connexion:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 28px rgba(26,79,138,.4);
    }
    .btn-connexion:active { transform: translateY(0); }

    .btn-connexion > * { position: relative; z-index: 1; }

    /* ===== DIVIDER ===== */
    .auth-divider {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 20px 0;
    }

    .auth-divider::before,
    .auth-divider::after {
        content: '';
        flex: 1;
        height: 1px;
        background: var(--gray-200);
    }

    .auth-divider span {
        font-size: .76rem;
        color: var(--gray-400);
        white-space: nowrap;
    }

    /* ===== BOUTON RETOUR SITE ===== */
    .btn-back-site {
        width: 100%;
        padding: 11px 20px;
        border: 1.5px solid var(--gray-200);
        border-radius: var(--radius-md);
        background: var(--white);
        color: var(--primary);
        font-size: .88rem;
        font-weight: 500;
        text-decoration: none;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all var(--transition);
    }

    .btn-back-site:hover {
        background: var(--gray-50);
        border-color: var(--primary);
        color: var(--primary-dark);
        transform: translateY(-1px);
        box-shadow: var(--shadow-md);
    }

    /* ===== ALERTES ===== */
    .alert {
        border-radius: var(--radius-md);
        font-size: .87rem;
        border: none;
        padding: 12px 16px;
        display: flex;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 18px;
    }

    .alert i { font-size: 1rem; margin-top: 1px; flex-shrink: 0; }
    .alert-danger  { background: #fef2f2; color: #b91c1c; }
    .alert-success { background: #f0fdf4; color: #15803d; }
    .alert-warning { background: #fffbeb; color: #92400e; }
    .alert-info    { background: #eff6ff; color: #1d4ed8; }

    /* ===== BADGES SÉCURITÉ ===== */
    .security-strip {
        display: flex;
        justify-content: center;
        gap: 18px;
        padding: 16px 0 0;
        margin-top: 20px;
        border-top: 1px solid var(--gray-100);
    }

    .sec-badge {
        display: flex;
        align-items: center;
        gap: 5px;
        font-size: .71rem;
        color: var(--gray-400);
        transition: color var(--transition);
    }

    .sec-badge:hover { color: var(--primary); }
    .sec-badge i { font-size: .88rem; }

    /* ===== FOOTER ===== */
    .auth-footer {
        text-align: center;
        color: rgba(255,255,255,.45);
        font-size: .73rem;
        padding: 14px 16px;
        position: relative;
        z-index: 2;
        line-height: 1.6;
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 520px) {
        .auth-card { border-radius: var(--radius-lg); }
        .auth-header { padding: 30px 24px 24px; }
        .auth-body { padding: 24px 24px 20px; }
        .auth-header-title { font-size: 1.75rem; }
        .auth-logo-ring { width: 76px; height: 76px; }
        .auth-logo-ring img { width: 56px; height: 56px; }
        .security-strip { gap: 10px; }
    }
    </style>
</head>
<body>

<!-- Éléments décoratifs -->
<div class="deco-circle deco-circle-1"></div>
<div class="deco-circle deco-circle-2"></div>
<div class="deco-circle deco-circle-3"></div>

<div class="auth-wrapper">
    <div class="auth-card">

        <!-- En-tête -->
        <div class="auth-header">
            <!-- Logo centré -->
            <div class="auth-logo-ring">
                <img src="<?= asset('img/logo-anpe.svg') ?>" alt="Logo ANPE Niger">
            </div>

            <div class="auth-header-title">
                e-DAMO
                <span class="auth-header-sub">Agence Nationale pour la Promotion de l'Emploi — Niger</span>
            </div>
            <div class="auth-header-stripe"></div>
        </div>

        <!-- Corps -->
        <div class="auth-body">

            <?php if (isset($pageTitle) && !in_array($pageTitle, [APP_NAME, 'e-DAMO'])): ?>
            <div class="auth-page-title">
                <i class="bi bi-person-lock"></i>
                <?= e($pageTitle) ?>
            </div>
            <?php endif; ?>

            <!-- Flash message -->
            <?php if (!empty($flash) && is_array($flash)): ?>
            <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : e($flash['type']) ?>">
                <i class="bi bi-<?= match($flash['type'] ?? '') {
                    'success' => 'check-circle-fill',
                    'warning' => 'exclamation-triangle-fill',
                    'error', 'danger' => 'x-octagon-fill',
                    default   => 'info-circle-fill'
                } ?>"></i>
                <span><?= e($flash['message'] ?? '') ?></span>
            </div>
            <?php endif; ?>

            <!-- Contenu de la vue injectée ici -->
            <?= $content ?>

            <!-- Badges sécurité -->
            <div class="security-strip">
                <span class="sec-badge" title="Connexion HTTPS sécurisée">
                    <i class="bi bi-shield-lock-fill" style="color:#10b981"></i> SSL sécurisé
                </span>
                <span class="sec-badge" title="Données chiffrées">
                    <i class="bi bi-lock-fill" style="color:#3b82f6"></i> Données chiffrées
                </span>
                <span class="sec-badge" title="Plateforme officielle ANPE Niger">
                    <i class="bi bi-patch-check-fill" style="color:#f59e0b"></i> ANPE officiel
                </span>
            </div>

        </div><!-- /.auth-body -->

    </div><!-- /.auth-card -->
</div><!-- /.auth-wrapper -->

<!-- Footer -->
<footer class="auth-footer">
    © <?= date('Y') ?> e-DAMO &nbsp;·&nbsp; ANPE Niger &nbsp;·&nbsp;
    Plateforme Digitale de Déclaration Annuelle de la Main d'Œuvre
</footer>

<!-- Bootstrap JS (local) -->
<script src="<?= asset('js/bootstrap.bundle.min.js') ?>"></script>

<script>
// ── Toggle affichage mot de passe ──
document.querySelectorAll('.toggle-password').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var inp = document.getElementById(this.dataset.target);
        if (!inp) return;
        var show = inp.type === 'password';
        inp.type = show ? 'text' : 'password';
        var ico = this.querySelector('i');
        if (ico) ico.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
        this.setAttribute('title', show ? 'Masquer' : 'Afficher');
    });
});

// ── Spinner sur soumission du formulaire de connexion ──
var loginForm = document.getElementById('loginForm');
if (loginForm) {
    loginForm.addEventListener('submit', function() {
        var btn = document.getElementById('loginBtn');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML =
                '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>' +
                ' <span>Connexion en cours…</span>';
        }
    });
}
</script>
</body>
</html>
