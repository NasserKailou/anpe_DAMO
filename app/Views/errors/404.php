<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page introuvable — e-DAMO</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            color: #333;
        }
        .error-box {
            background: #fff;
            border-radius: 16px;
            padding: 3rem 2.5rem;
            text-align: center;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .error-animation {
            font-size: 4rem;
            margin-bottom: 1rem;
            display: block;
            animation: float 3s ease-in-out infinite;
        }
        @keyframes float {
            0%,100% { transform: translateY(0); }
            50%      { transform: translateY(-10px); }
        }
        .error-code {
            font-size: 6rem;
            font-weight: 900;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1;
            margin-bottom: 0.25rem;
        }
        .error-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: #212529;
            margin-bottom: 0.75rem;
        }
        .error-desc {
            color: #6c757d;
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        .links { display: flex; gap: 0.75rem; justify-content: center; flex-wrap: wrap; }
        .btn-primary-custom {
            display: inline-block;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: #fff;
            padding: 0.75rem 1.75rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: opacity 0.2s;
        }
        .btn-primary-custom:hover { opacity: 0.9; color: #fff; }
        .btn-secondary-custom {
            display: inline-block;
            border: 1px solid #dee2e6;
            color: #6c757d;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            background: transparent;
        }
        .btn-secondary-custom:hover { background: #f8f9fa; color: #333; }
        .search-box {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }
        .search-box p {
            font-size: 0.85rem;
            color: #6c757d;
            margin-bottom: 0.5rem;
        }
        .quick-links {
            display: flex; flex-wrap: wrap; gap: 0.4rem; justify-content: center;
        }
        .quick-link {
            background: #e9ecef;
            color: #495057;
            padding: 0.3rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            text-decoration: none;
        }
        .quick-link:hover { background: #dee2e6; color: #212529; }
        .brand {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #f0f0f0;
            font-size: 0.85rem;
            color: #adb5bd;
        }
        .brand strong { color: #495057; }
    </style>
</head>
<body>
    <div class="error-box">
        <span class="error-animation">🗺️</span>
        <div class="error-code">404</div>
        <div class="error-title">Page introuvable</div>
        <p class="error-desc">
            La page que vous recherchez n'existe pas ou a été déplacée.
            Voici quelques liens utiles pour vous orienter.
        </p>

        <div class="search-box">
            <p>Accès rapide :</p>
            <div class="quick-links">
                <a href="/" class="quick-link">🏠 Accueil</a>
                <a href="/statistiques" class="quick-link">📊 Statistiques</a>
                <a href="/donnees" class="quick-link">💾 Données</a>
                <a href="/guides" class="quick-link">📋 Guides</a>
                <a href="/connexion" class="quick-link">🔑 Connexion</a>
            </div>
        </div>

        <div class="links">
            <a href="javascript:history.back()" class="btn-primary-custom">← Retour</a>
            <a href="/" class="btn-secondary-custom">🏠 Accueil</a>
        </div>
        <div class="brand">
            <strong>e-DAMO</strong> — ANPE Niger
        </div>
    </div>
</body>
</html>
