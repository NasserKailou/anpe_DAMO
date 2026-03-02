/**
 * e-DAMO — JavaScript Formulaire de Saisie Multi-étapes (RAMO 2025)
 * Compatible avec declaration_saisie.php (classes .ramo-step / .ramo-sep, 8 étapes)
 */

const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
const DECL_ID    = document.getElementById('wizard-form')?.dataset.declId ?? 0;
const TOTAL_ETAPES = parseInt(document.getElementById('wizard-form')?.dataset.totalEtapes ?? '8');
const BASE       = (window.APP_BASE ?? '').replace(/\/+$/, '');

let currentEtape  = parseInt(document.getElementById('wizard-form')?.dataset.etape ?? '1');
let autoSaveTimer = null;

/* ═══════════════════════════════════════════════════════════════
   INITIALISATION
═══════════════════════════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', () => {
    showEtape(currentEtape);
    bindNavButtons();
    bindAutoSave();
    // Les calculs sont déjà initialisés dans la vue inline
});

/* ═══════════════════════════════════════════════════════════════
   NAVIGATION WIZARD (utilisée aussi par goToStep inline dans la vue)
═══════════════════════════════════════════════════════════════ */
function showEtape(n) {
    n = Math.max(1, Math.min(TOTAL_ETAPES, n));

    // Masquer toutes les sections
    document.querySelectorAll('.form-section').forEach(s => s.classList.remove('active'));

    // Afficher la section cible
    const section = document.getElementById('etape-' + n);
    if (section) section.classList.add('active');

    // Mettre à jour les indicateurs du wizard (.ramo-step)
    document.querySelectorAll('.ramo-step').forEach(step => {
        const stepNum = parseInt(step.dataset.step);
        step.classList.remove('active', 'done');
        if (stepNum === n) step.classList.add('active');
        else if (stepNum < n) step.classList.add('done');
    });

    // Mettre à jour les séparateurs (.ramo-sep)
    document.querySelectorAll('.ramo-sep').forEach((sep, i) => {
        sep.classList.toggle('done', (i + 1) < n);
    });

    // Mettre à jour l'icône checkmark dans les étapes passées
    document.querySelectorAll('.ramo-step').forEach(step => {
        const stepNum = parseInt(step.dataset.step);
        const numEl   = step.querySelector('.ramo-step-num');
        if (!numEl) return;
        if (stepNum < n) {
            numEl.innerHTML = '<i class="bi bi-check-lg"></i>';
        } else {
            numEl.textContent = stepNum;
        }
    });

    // Mettre à jour le champ caché
    const inputEtape = document.getElementById('input-etape');
    if (inputEtape) inputEtape.value = n;

    currentEtape = n;
    updateProgress(n);
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Exposition globale pour l'attribut onclick="goToStep(n)" de la vue
window.goToStep = function(n) {
    showEtape(n);
};

/* ═══════════════════════════════════════════════════════════════
   BARRE DE PROGRESSION
═══════════════════════════════════════════════════════════════ */
function updateProgress(n) {
    const pct = Math.round((n / TOTAL_ETAPES) * 100);
    const bar = document.querySelector('.progress-ramo .fill');
    if (bar) bar.style.width = pct + '%';
    const label = document.getElementById('progress-label');
    if (label) label.textContent = `Avancement : ${pct}% — Étape ${n}/${TOTAL_ETAPES}`;
}

/* ═══════════════════════════════════════════════════════════════
   BOUTONS NAVIGATION
═══════════════════════════════════════════════════════════════ */
function bindNavButtons() {
    // Bouton "Suivant" → sauvegarder puis avancer
    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('.btn-next');
        if (!btn) return;
        e.preventDefault();
        const ok = await saveCurrentEtape(false); // sauvegarde silencieuse
        if (ok && currentEtape < TOTAL_ETAPES) showEtape(currentEtape + 1);
    });

    // Bouton "Précédent" → juste naviguer (sans sauvegarder)
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.btn-prev');
        if (!btn) return;
        e.preventDefault();
        if (currentEtape > 1) showEtape(currentEtape - 1);
    });

    // Bouton "Sauvegarder" (submit)
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.btn-save');
        if (!btn) return;
        // Laisser le formulaire se soumettre normalement (POST standard)
        // L'input#input-etape contient l'étape courante
    });
}

/* ═══════════════════════════════════════════════════════════════
   SAUVEGARDE AJAX
═══════════════════════════════════════════════════════════════ */
async function saveCurrentEtape(showFeedback = true) {
    setAutoSaveStatus('saving');
    const form = document.getElementById('wizard-form');
    if (!form) return false;

    const fd = new FormData(form);
    fd.set('etape', currentEtape);
    // Assurer que le CSRF est bien présent
    if (!fd.has('_csrf_token') || !fd.get('_csrf_token')) {
        fd.set('_csrf_token', CSRF_TOKEN);
    }

    try {
        const resp = await fetch(`${BASE}/agent/declaration/${DECL_ID}/sauvegarder`, {
            method: 'POST',
            body: fd,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });

        if (!resp.ok) {
            setAutoSaveStatus('error');
            if (showFeedback) showToast('Erreur HTTP ' + resp.status, 'danger');
            return false;
        }

        const json = await resp.json();
        setAutoSaveStatus(json.success ? 'saved' : 'error');
        if (!json.success && showFeedback) {
            showToast(json.message ?? 'Erreur de sauvegarde', 'danger');
        }
        return json.success === true;

    } catch (err) {
        setAutoSaveStatus('error');
        if (showFeedback) showToast('Erreur réseau. Vérifiez votre connexion.', 'danger');
        return false;
    }
}

/* ═══════════════════════════════════════════════════════════════
   INDICATEUR AUTOSAVE
═══════════════════════════════════════════════════════════════ */
function setAutoSaveStatus(status) {
    const el = document.getElementById('autosave-status');
    if (!el) return;
    const cfg = {
        saving: { text: 'Sauvegarde…',       icon: 'bi-cloud-arrow-up', cls: 'saving' },
        saved:  { text: 'Sauvegardé',         icon: 'bi-cloud-check',    cls: 'saved'  },
        error:  { text: 'Erreur sauvegarde',  icon: 'bi-cloud-slash',    cls: 'error'  },
    };
    const c = cfg[status] ?? cfg.saved;
    el.innerHTML = `<i class="bi ${c.icon}"></i><span>${c.text}</span>`;
    el.className = `autosave-indicator ${c.cls}`;
}

/* ═══════════════════════════════════════════════════════════════
   AUTOSAVE (déclenché 3 s après la dernière modification)
═══════════════════════════════════════════════════════════════ */
function bindAutoSave() {
    document.getElementById('wizard-form')?.addEventListener('input', () => {
        clearTimeout(autoSaveTimer);
        autoSaveTimer = setTimeout(() => saveCurrentEtape(false), 3000);
    });
    document.getElementById('wizard-form')?.addEventListener('change', () => {
        clearTimeout(autoSaveTimer);
        autoSaveTimer = setTimeout(() => saveCurrentEtape(false), 3000);
    });
}

/* ═══════════════════════════════════════════════════════════════
   NOTIFICATIONS TOAST
═══════════════════════════════════════════════════════════════ */
function showToast(msg, type = 'info') {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.style.cssText =
            'position:fixed;top:20px;right:20px;z-index:9999;' +
            'display:flex;flex-direction:column;gap:8px;max-width:340px;';
        document.body.appendChild(container);
    }
    const colors = {
        success: '#2e7d32',
        danger:  '#c62828',
        warning: '#e65100',
        info:    '#01579b',
    };
    const icons = {
        success: 'bi-check-circle-fill',
        danger:  'bi-exclamation-triangle-fill',
        warning: 'bi-exclamation-circle-fill',
        info:    'bi-info-circle-fill',
    };
    const t = document.createElement('div');
    t.style.cssText =
        `background:${colors[type] ?? '#333'};color:#fff;` +
        'padding:12px 16px;border-radius:8px;font-size:.85rem;' +
        'box-shadow:0 4px 14px rgba(0,0,0,.25);display:flex;align-items:center;gap:10px;';
    t.innerHTML = `<i class="bi ${icons[type] ?? 'bi-bell'}" style="flex-shrink:0"></i><span>${msg}</span>`;
    container.appendChild(t);
    setTimeout(() => {
        t.style.transition = 'opacity .4s';
        t.style.opacity    = '0';
        setTimeout(() => t.remove(), 400);
    }, 4000);
}

/* ═══════════════════════════════════════════════════════════════
   EXPOSITION GLOBALE (utilisée dans declaration_saisie.php)
═══════════════════════════════════════════════════════════════ */
window.showEtapeRamo    = showEtape;
window.showToastRamo    = showToast;
window.saveCurrentEtape = saveCurrentEtape;
