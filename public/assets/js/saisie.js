/**
 * e-DAMO - JavaScript Formulaire de Saisie Multi-étapes
 */
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
const DECL_ID    = document.getElementById('wizard-form')?.dataset.declId ?? 0;

let currentEtape = parseInt(document.getElementById('wizard-form')?.dataset.etape ?? '1');
let autoSaveTimer = null;

document.addEventListener('DOMContentLoaded', () => {
    showEtape(currentEtape);
    bindNavButtons();
    bindTableCalculations();
    bindAutoSave();
    updateProgress();
});

function showEtape(n) {
    document.querySelectorAll('.form-section').forEach(s => s.classList.remove('active'));
    document.querySelectorAll('.wizard-step').forEach((s, i) => {
        s.classList.remove('active');
        if (i + 1 < n) s.classList.add('done'); else s.classList.remove('done');
    });
    document.querySelectorAll('.wizard-sep').forEach((s, i) => {
        if (i + 1 < n) s.classList.add('done'); else s.classList.remove('done');
    });
    const section = document.getElementById(`etape-${n}`);
    if (section) section.classList.add('active');
    const step = document.querySelector(`.wizard-step[data-step="${n}"]`);
    if (step) step.classList.add('active');
    currentEtape = n;
    updateProgress();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function updateProgress() {
    const pct = Math.round((currentEtape / 7) * 100);
    const bar = document.querySelector('.progress-bar-declaration .fill');
    if (bar) bar.style.width = pct + '%';
    const label = document.getElementById('progress-label');
    if (label) label.textContent = `Étape ${currentEtape}/7 — ${pct}%`;
}

function bindNavButtons() {
    document.querySelectorAll('.btn-next').forEach(btn => {
        btn.addEventListener('click', async () => {
            if (await saveCurrentEtape()) {
                if (currentEtape < 7) showEtape(currentEtape + 1);
            }
        });
    });
    document.querySelectorAll('.btn-prev').forEach(btn => {
        btn.addEventListener('click', () => {
            if (currentEtape > 1) showEtape(currentEtape - 1);
        });
    });
    document.querySelectorAll('.btn-save').forEach(btn => {
        btn.addEventListener('click', () => saveCurrentEtape());
    });
    document.querySelectorAll('.btn-submit').forEach(btn => {
        btn.addEventListener('click', () => soumettreDeclaration());
    });
    document.querySelectorAll('.wizard-step').forEach(step => {
        step.addEventListener('click', () => {
            const n = parseInt(step.dataset.step);
            if (!isNaN(n)) showEtape(n);
        });
    });
}

async function saveCurrentEtape() {
    setAutoSaveStatus('saving');
    const form = document.getElementById('wizard-form');
    if (!form) return false;
    const fd = new FormData(form);
    fd.set('etape', currentEtape);
    fd.append('_csrf_token', CSRF_TOKEN);

    try {
        const resp = await fetch(`/agent/declaration/${DECL_ID}/sauvegarder`, {
            method: 'POST', body: fd,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const json = await resp.json();
        setAutoSaveStatus(json.success ? 'saved' : 'error');
        if (!json.success) showToast('Erreur de sauvegarde', 'danger');
        return json.success;
    } catch (e) {
        setAutoSaveStatus('error');
        showToast('Erreur réseau', 'danger');
        return false;
    }
}

function setAutoSaveStatus(status) {
    const el = document.getElementById('autosave-status');
    if (!el) return;
    const msgs = { saving: '⏳ Sauvegarde...', saved: '✓ Sauvegardé', error: '✗ Erreur sauvegarde' };
    const classes = { saving: 'saving', saved: '', error: 'error' };
    el.textContent = msgs[status] ?? '';
    el.className = 'autosave-indicator ' + (classes[status] ?? '');
}

function bindAutoSave() {
    document.getElementById('wizard-form')?.addEventListener('change', () => {
        clearTimeout(autoSaveTimer);
        autoSaveTimer = setTimeout(saveCurrentEtape, 3000);
    });
}

function bindTableCalculations() {
    // Calculs automatiques dans les tableaux de saisie
    document.querySelectorAll('.table-saisie input[type=number]').forEach(input => {
        input.addEventListener('input', () => recalculateRow(input));
    });

    // Formation: afficher/cacher le formulaire selon oui/non
    const radioFormation = document.querySelectorAll('input[name="a_eu_formation"]');
    const detailFormation = document.getElementById('formation-details');
    if (radioFormation.length && detailFormation) {
        radioFormation.forEach(r => {
            r.addEventListener('change', () => {
                detailFormation.style.display = r.value === '1' ? 'block' : 'none';
            });
        });
    }

    // Effectifs étrangers: bouton ajouter ligne
    document.getElementById('btn-add-etranger')?.addEventListener('click', addEtrangerRow);
}

function recalculateRow(input) {
    const row = input.closest('tr');
    if (!row) return;
    const inputs = row.querySelectorAll('input[type=number]');
    let total = 0;
    inputs.forEach(i => { if (!i.dataset.nototal) total += parseInt(i.value || 0); });
    const totalCell = row.querySelector('.total-cell');
    if (totalCell) totalCell.textContent = total;
    recalculateColumn(input);
}

function recalculateColumn(input) {
    const table = input.closest('table');
    if (!table) return;
    const colIndex = Array.from(input.closest('tr').cells).indexOf(input.closest('td'));
    let total = 0;
    table.querySelectorAll(`tbody tr:not(.total-row) td:nth-child(${colIndex + 1}) input`).forEach(i => {
        total += parseInt(i.value || 0);
    });
    const totalRow = table.querySelector(`.total-row td:nth-child(${colIndex + 1})`);
    if (totalRow) totalRow.textContent = total;
}

let etrangerIndex = 0;
function addEtrangerRow() {
    const tbody = document.getElementById('etrangers-tbody');
    if (!tbody) return;
    etrangerIndex++;
    const tr = document.createElement('tr');
    tr.className = 'etrangers-row';
    tr.innerHTML = `
        <td><input type="text" class="form-control form-control-sm" name="etrangers[${etrangerIndex}][pays]" placeholder="Pays"></td>
        <td><input type="text" class="form-control form-control-sm" name="etrangers[${etrangerIndex}][qualification]" placeholder="Qualification"></td>
        <td><input type="text" class="form-control form-control-sm" name="etrangers[${etrangerIndex}][fonction]" placeholder="Fonction"></td>
        <td><select class="form-select form-select-sm" name="etrangers[${etrangerIndex}][sexe]">
            <option value="H">Homme</option><option value="F">Femme</option>
        </select></td>
        <td><input type="number" class="form-control form-control-sm" name="etrangers[${etrangerIndex}][nombre]" min="0" value="0"></td>
        <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove()">×</button></td>
    `;
    tbody.appendChild(tr);
}

async function soumettreDeclaration() {
    if (!confirm('Confirmez-vous la soumission définitive de cette déclaration à l\'ANPE Niger ?')) return;

    // Sauvegarder d'abord l'étape courante
    await saveCurrentEtape();

    const fd = new FormData();
    fd.append('_csrf_token', CSRF_TOKEN);

    try {
        const resp = await fetch(`/agent/declaration/${DECL_ID}/soumettre`, {
            method: 'POST', body: fd,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const json = await resp.json();
        if (json.success) {
            showToast('Déclaration soumise avec succès !', 'success');
            setTimeout(() => location.href = json.redirect ?? `/agent/declaration/${DECL_ID}/apercu`, 1500);
        } else {
            showToast(json.message ?? 'Erreur lors de la soumission', 'danger');
        }
    } catch {
        showToast('Erreur réseau', 'danger');
    }
}

function showToast(msg, type = 'info') {
    let c = document.getElementById('toast-container');
    if (!c) {
        c = document.createElement('div');
        c.id = 'toast-container';
        c.style.cssText = 'position:fixed;top:20px;right:20px;z-index:9999;display:flex;flex-direction:column;gap:8px;';
        document.body.appendChild(c);
    }
    const colors = { success: '#2e7d32', danger: '#c62828', warning: '#f57f17', info: '#01579b' };
    const t = document.createElement('div');
    t.style.cssText = `background:${colors[type]??'#333'};color:#fff;padding:12px 18px;border-radius:8px;font-size:.85rem;max-width:320px;box-shadow:0 4px 12px rgba(0,0,0,.2)`;
    t.textContent = msg;
    c.appendChild(t);
    setTimeout(() => t.remove(), 4000);
}
