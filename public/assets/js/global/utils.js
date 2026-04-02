/**
 * utils.js
 * Funcionalidades de ayuda compartidas.
 */

export const $ = (sel) => document.querySelector(sel);
export const $$ = (sel) => document.querySelectorAll(sel);
export const show = (el) => { if (el) el.style.display = ''; };
export const hide = (el) => { if (el) el.style.display = 'none'; };

export const formatBs = (v) => {
    // Handle Venezuelan format: "1.500,00" → strip dots, swap comma → "1500.00"
    let n = 0;
    if (typeof v === 'number') {
        n = v;
    } else if (typeof v === 'string' && v !== '') {
        const clean = v.replace(/\./g, '').replace(',', '.');
        n = parseFloat(clean) || 0;
    }
    return 'Bs. ' + n.toLocaleString('es-VE', { minimumFractionDigits: 2 });
};

// ── Toast notifications (reemplazo de alert()) ──

const TOAST_ICONS = {
    error: `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>`,
    success: `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>`,
    warning: `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>`,
    info: `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>`,
};

/**
 * Muestra un toast inline en vez de un alert() nativo.
 * @param {string} message - Texto a mostrar
 * @param {'error'|'success'|'info'} type - Tipo de notificación
 * @param {number} duration - Duración en ms (default 4000)
 */
export function showToast(message, type = 'error', duration = 4000) {
    // Crear contenedor si no existe
    let container = document.getElementById('cc-toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'cc-toast-container';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = `cc-toast cc-toast--${type}`;
    toast.innerHTML = `
        <span class="cc-toast__icon">${TOAST_ICONS[type] || TOAST_ICONS.info}</span>
        <span class="cc-toast__msg">${message}</span>
        <button class="cc-toast__close" aria-label="Cerrar">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
    `;

    const dismiss = () => {
        toast.classList.add('cc-toast--exit');
        toast.addEventListener('animationend', () => toast.remove());
    };

    toast.querySelector('.cc-toast__close').addEventListener('click', dismiss);
    // Desplazar agresivamente a cualquier toast viejo limpiando el contenedor
    container.innerHTML = '';
    container.appendChild(toast);
    console.log('[Sistema Global] Toast Inyectado:', message);

    // Auto-dismiss
    if (duration > 0) {
        setTimeout(dismiss, duration);
    }
}

// Expose globally for inline scripts
window.showToast = showToast;

/**
 * Muestra un error in-line estandarizado y autolimpiante.
 * @param {string} containerId - ID del div contenedor del error (ej. 'causanteErrors')
 * @param {string} listId - ID de la lista UL dentro del contenedor (ej. 'causanteErrorsList')
 * @param {string} message - Texto del error a mostrar
 * @param {HTMLElement|null} inputToClear - (Opcional) Elemento input para limpiar su valor simultáneamente
 */
export function showInlineError(containerId, listId, message, inputToClear = null) {
    const container = document.getElementById(containerId);
    const list = document.getElementById(listId);
    if (!container || !list) {
        showToast(message, 'warning');
        if (inputToClear) {
            inputToClear.value = '';
            inputToClear.dispatchEvent(new Event('change', { bubbles: true }));
        }
        return;
    }

    list.innerHTML = `<li>${message}</li>`;
    container.classList.add('is-visible');
    container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    if (inputToClear) {
        inputToClear.value = '';
        inputToClear.dispatchEvent(new Event('change', { bubbles: true }));
    }

    const clearHandler = () => {
        container.classList.remove('is-visible');
        document.body.removeEventListener('input', clearHandler);
        document.body.removeEventListener('change', clearHandler);
    };
    
    // Slight delay to avoid immediate clear if triggered by a generic event
    setTimeout(() => {
        document.body.addEventListener('input', clearHandler, { once: true });
        document.body.addEventListener('change', clearHandler, { once: true });
    }, 100);
}

window.showInlineError = showInlineError;

/**
 * ── Mantiene Viva la Sesión de PHP (Evita la Expiración por Inactividad) ──
 * Se lanza un "ping" cada 5 minutos (300,000 ms) para asegurar que la
 * sesión del usuario (Profesor/Estudiante) no caduque mientras llena formularios.
 */
export function initSessionPing(intervalMs = 300000) {
    const baseUrl = (window.BASE_URL || '/tesis_francisco/public').replace(/\/+$/, '');
    setInterval(() => {
        fetch(baseUrl + '/api/ping', { method: 'GET', headers: { 'Cache-Control': 'no-cache' } })
            .then(res => res.json())
            .catch(e => console.log('[PING] Fallo de latido', e));
    }, intervalMs);
}

// Iniciar automáticamente en todas las páginas que incluyan utils.js
initSessionPing();
