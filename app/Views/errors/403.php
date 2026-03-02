<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accès refusé — e-DAMO</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            color: #333;
        }
        .error-box {
            background: #fff;
            border-radius: 16px;
            padding: 3rem 2.5rem;
            text-align: center;
            max-width: 480px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
        }
        .error-icon {
            font-size: 5rem;
            margin-bottom: 1.5rem;
            display: block;
        }
        .error-code {
            font-size: 5rem;
            font-weight: 800;
            color: #dc3545;
            line-height: 1;
            margin-bottom: 0.5rem;
        }
        .error-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 0.75rem;
        }
        .error-desc {
            color: #6c757d;
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        .btn-back {
            display: inline-block;
            background: #0d6efd;
            color: #fff;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: background 0.2s;
        }
        .btn-back:hover { background: #0b5ed7; color: #fff; }
        .btn-home {
            display: inline-block;
            background: transparent;
            color: #6c757d;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            margin-left: 0.5rem;
            border: 1px solid #dee2e6;
        }
        .btn-home:hover { background: #f8f9fa; color: #333; }
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
        <span class="error-icon">🔒</span>
        <div class="error-code">403</div>
        <div class="error-title">Accès refusé</div>
        <p class="error-desc">
            Vous n'avez pas les permissions nécessaires pour accéder à cette page.
            Veuillez contacter votre administrateur si vous pensez qu'il s'agit d'une erreur.
        </p>
        <div>
            <a href="javascript:history.back()" class="btn-back">← Retour</a>
            <a href="/" class="btn-home">🏠 Accueil</a>
        </div>
        <div class="brand">
            <strong>e-DAMO</strong> — ANPE Niger
        </div>
    </div>
</body>
</html>
