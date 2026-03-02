<?php /* Vue Guides de remplissage — e-DAMO */ ?>

<!-- ===== HERO GUIDES ===== -->
<section class="pub-hero pub-hero-sm">
    <div class="container">
        <div class="text-center text-white">
            <div class="pub-hero-icon mb-3">
                <i class="bi bi-book-fill"></i>
            </div>
            <h1 class="pub-hero-title">Guides de remplissage</h1>
            <p class="pub-hero-desc">
                Téléchargez les guides officiels pour compléter correctement votre Déclaration Annuelle de la Main d'Œuvre.
            </p>
        </div>
    </div>
</section>

<!-- ===== CONTENU GUIDES ===== -->
<section class="py-5">
    <div class="container">

        <?php if (empty($guides)): ?>
        <!-- Aucun guide disponible -->
        <div class="text-center py-5">
            <div style="width:80px;height:80px;background:#f3f4f6;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
                <i class="bi bi-folder-x" style="font-size:2rem;color:#9ca3af;"></i>
            </div>
            <h4 style="color:#374151;font-weight:600;">Aucun guide disponible</h4>
            <p style="color:#6b7280;max-width:400px;margin:8px auto 0;">
                Les guides de remplissage seront mis en ligne prochainement. Contactez-nous pour plus d'informations.
            </p>
        </div>

        <?php else: ?>
        <!-- Liste des guides -->
        <div class="row g-4">

            <?php foreach ($guides as $guide): ?>
            <div class="col-md-6 col-lg-4">
                <div class="guide-card h-100">

                    <!-- Icône PDF -->
                    <div class="guide-card-icon">
                        <i class="bi bi-file-earmark-pdf-fill"></i>
                        <span class="guide-year-badge"><?= e($guide['annee'] ?? date('Y')) ?></span>
                    </div>

                    <!-- Contenu -->
                    <div class="guide-card-body">
                        <h5 class="guide-title"><?= e($guide['titre']) ?></h5>

                        <?php if (!empty($guide['description'])): ?>
                        <p class="guide-desc"><?= e($guide['description']) ?></p>
                        <?php endif; ?>

                        <!-- Méta-infos -->
                        <div class="guide-meta">
                            <?php if (!empty($guide['fichier_taille'])): ?>
                            <span class="guide-meta-item">
                                <i class="bi bi-hdd"></i>
                                <?= round($guide['fichier_taille'] / 1024) ?> Ko
                            </span>
                            <?php endif; ?>
                            <span class="guide-meta-item">
                                <i class="bi bi-download"></i>
                                <?= number_format($guide['telechargements'] ?? 0) ?> téléch.
                            </span>
                            <span class="guide-meta-item">
                                <i class="bi bi-file-earmark-pdf"></i>
                                PDF
                            </span>
                        </div>
                    </div>

                    <!-- Bouton télécharger -->
                    <div class="guide-card-footer">
                        <a href="<?= url('guide/' . $guide['id'] . '/telecharger') ?>"
                           class="btn-guide-dl" target="_blank" rel="noopener">
                            <i class="bi bi-cloud-arrow-down-fill"></i>
                            <span>Télécharger le guide</span>
                        </a>
                    </div>

                </div>
            </div>
            <?php endforeach; ?>

        </div>
        <?php endif; ?>

        <!-- Encart contact -->
        <div class="guide-contact-box mt-5">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h5 class="mb-1" style="color:#1a4f8a;font-weight:700;">
                        <i class="bi bi-question-circle-fill me-2" style="color:#f59e0b;"></i>
                        Besoin d'aide pour remplir votre déclaration ?
                    </h5>
                    <p class="mb-0 text-muted" style="font-size:.9rem;">
                        Nos agents ANPE sont disponibles pour vous accompagner. Contactez-nous ou rendez-vous dans votre agence régionale.
                    </p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <a href="tel:+22720733384" class="btn btn-outline-primary me-2">
                        <i class="bi bi-telephone-fill me-1"></i>+227 20 73 33 84
                    </a>
                    <a href="<?= url('login') ?>" class="btn btn-primary">
                        <i class="bi bi-person-fill me-1"></i>Espace agents
                    </a>
                </div>
            </div>
        </div>

    </div>
</section>

<style>
/* ===== HERO GUIDES ===== */
.pub-hero-sm {
    padding: 60px 0 50px;
    background: linear-gradient(150deg, #0d3261 0%, #1a4f8a 50%, #1e6fba 100%);
    position: relative;
    overflow: hidden;
}
.pub-hero-sm::before {
    content:'';
    position:absolute;
    inset:0;
    background: radial-gradient(circle at 80% 20%, rgba(245,158,11,.1) 0%, transparent 50%);
}
.pub-hero-icon {
    width: 64px; height: 64px;
    background: rgba(255,255,255,.15);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto;
    backdrop-filter: blur(10px);
}
.pub-hero-icon i { font-size: 1.8rem; color: #f59e0b; }
.pub-hero-title { font-size: 2rem; font-weight: 800; margin-bottom: 12px; }
.pub-hero-desc  { font-size: 1rem; opacity: .8; max-width: 520px; margin: 0 auto; }

/* ===== GUIDE CARD ===== */
.guide-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,.08);
    border: 1px solid #f3f4f6;
    overflow: hidden;
    transition: transform .25s ease, box-shadow .25s ease;
    display: flex;
    flex-direction: column;
}
.guide-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 36px rgba(26,79,138,.14);
}
.guide-card-icon {
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    padding: 28px 24px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: relative;
}
.guide-card-icon i {
    font-size: 2.8rem;
    color: rgba(255,255,255,.9);
    filter: drop-shadow(0 2px 4px rgba(0,0,0,.2));
}
.guide-year-badge {
    background: rgba(255,255,255,.2);
    color: #fff;
    font-size: .78rem;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 20px;
    border: 1px solid rgba(255,255,255,.3);
}
.guide-card-body {
    padding: 20px 22px;
    flex: 1;
}
.guide-title {
    font-size: .95rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 8px;
    line-height: 1.4;
}
.guide-desc {
    font-size: .83rem;
    color: #6b7280;
    line-height: 1.5;
    margin-bottom: 14px;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.guide-meta {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}
.guide-meta-item {
    font-size: .76rem;
    color: #9ca3af;
    display: flex;
    align-items: center;
    gap: 4px;
}
.guide-meta-item i { font-size: .82rem; }
.guide-card-footer {
    padding: 14px 22px 20px;
    border-top: 1px solid #f3f4f6;
}
.btn-guide-dl {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    padding: 11px 16px;
    background: linear-gradient(135deg, #1a4f8a, #1e6fba);
    color: #fff;
    border-radius: 10px;
    text-decoration: none;
    font-size: .88rem;
    font-weight: 600;
    transition: all .25s ease;
}
.btn-guide-dl:hover {
    background: linear-gradient(135deg, #0d3261, #1a4f8a);
    color: #fff;
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(26,79,138,.3);
}
.btn-guide-dl i { font-size: 1.1rem; }

/* ===== ENCART CONTACT ===== */
.guide-contact-box {
    background: #f8fafc;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    padding: 28px 32px;
}
</style>
