/**
 * e-DAMO - JavaScript Principal
 */

// Token CSRF global
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

// Initialiser les charts Chart.js
function initChart(canvasId, type, data, extraOptions = {}) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return null;
    return new Chart(canvas.getContext('2d'), {
        type,
        data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } } },
            ...extraOptions
        }
    });
}

// Sidebar toggle
document.addEventListener('DOMContentLoaded', () => {
    const sidebar   = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('sidebarToggle');
    const closeBtn  = document.getElementById('sidebarClose');
    let overlay = document.querySelector('.overlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.className = 'overlay';
        document.body.appendChild(overlay);
    }

    function openSidebar() {
        sidebar?.classList.add('open');
        overlay.classList.add('show');
    }
    function closeSidebar() {
        sidebar?.classList.remove('open');
        overlay.classList.remove('show');
    }

    toggleBtn?.addEventListener('click', openSidebar);
    closeBtn?.addEventListener('click', closeSidebar);
    overlay.addEventListener('click', closeSidebar);

    // Marquer le lien actif dans la sidebar
    const currentPath = window.location.pathname;
    document.querySelectorAll('.sidebar-link').forEach(link => {
        const href = link.getAttribute('href');
        if (href && currentPath.startsWith(href) && href !== '/') {
            link.classList.add('active');
        } else if (href === '/' && currentPath === '/') {
            link.classList.add('active');
        }
    });

    // Fermeture auto des alertes
    document.querySelectorAll('.alert-dismissible').forEach(alert => {
        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            bsAlert?.close();
        }, 5000);
    });

    // Confirmation suppression
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', function(e) {
            const msg = this.dataset.confirm || 'Confirmer cette action ?';
            if (!confirm(msg)) e.preventDefault();
        });
    });

    // Soumission AJAX pour les formulaires marqués data-ajax
    document.querySelectorAll('form[data-ajax]').forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            const btn = this.querySelector('[type=submit]');
            if (btn) btn.disabled = true;

            try {
                const resp = await fetch(this.action, {
                    method: 'POST',
                    body: new FormData(this),
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-Token': CSRF_TOKEN }
                });
                const json = await resp.json();
                if (json.success) {
                    showToast(json.message ?? 'Succès', 'success');
                    if (json.redirect) setTimeout(() => location.href = json.redirect, 1000);
                } else {
                    showToast(json.message ?? 'Erreur', 'danger');
                }
            } catch {
                showToast('Erreur de communication', 'danger');
            } finally {
                if (btn) btn.disabled = false;
            }
        });
    });
});

// Toast notification
function showToast(message, type = 'info') {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.style.cssText = 'position:fixed;top:20px;right:20px;z-index:9999;display:flex;flex-direction:column;gap:8px;';
        document.body.appendChild(container);
    }
    const colors = { success: '#2e7d32', danger: '#c62828', warning: '#f57f17', info: '#01579b' };
    const toast = document.createElement('div');
    toast.style.cssText = `background:${colors[type]??'#333'};color:#fff;padding:12px 18px;border-radius:8px;font-size:.85rem;max-width:320px;box-shadow:0 4px 12px rgba(0,0,0,.2);animation:fadeInRight .3s ease`;
    toast.textContent = message;
    container.appendChild(toast);
    setTimeout(() => toast.remove(), 4000);
}

// AJAX POST helper
async function ajaxPost(url, data) {
    const fd = data instanceof FormData ? data : (() => {
        const f = new FormData();
        Object.entries(data).forEach(([k, v]) => f.append(k, v));
        f.append('_csrf_token', CSRF_TOKEN);
        return f;
    })();
    const resp = await fetch(url, {
        method: 'POST',
        body: fd,
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-Token': CSRF_TOKEN }
    });
    return resp.json();
}

// Toggle statut utilisateur
function toggleUser(id) {
    ajaxPost(`/admin/utilisateur/${id}/toggle`, { _csrf_token: CSRF_TOKEN })
        .then(json => {
            showToast(json.message, json.success ? 'success' : 'danger');
            if (json.success) setTimeout(() => location.reload(), 800);
        });
}

// Valider/rejeter déclaration
function validerDeclaration(id) {
    const obs = prompt('Observations (optionnel) :') ?? '';
    ajaxPost(`/admin/declaration/${id}/valider`, { observations: obs, _csrf_token: CSRF_TOKEN })
        .then(json => {
            showToast(json.message, json.success ? 'success' : 'danger');
            if (json.success) setTimeout(() => location.reload(), 1000);
        });
}
function rejeterDeclaration(id) {
    const motif = prompt('Motif de rejet (obligatoire) :');
    if (!motif) return showToast('Le motif est obligatoire', 'warning');
    ajaxPost(`/admin/declaration/${id}/rejeter`, { motif_rejet: motif, _csrf_token: CSRF_TOKEN })
        .then(json => {
            showToast(json.message, json.success ? 'success' : 'danger');
            if (json.success) setTimeout(() => location.reload(), 1000);
        });
}
