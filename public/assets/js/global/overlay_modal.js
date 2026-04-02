/**
 * overlay_modal.js
 * ─────────────────────────────────────────────────────────────────
 * Modal global basado en <div> overlay (NO en <dialog>).
 *
 * ¿POR QUÉ EXISTE ESTE ARCHIVO?
 * ─────────────────────────────────────────────────────────────────
 * El sistema principal de modales (core_modals.js) usa el elemento
 * nativo <dialog> con el método showModal(). Esto crea un "top-layer"
 * del navegador que se renderiza POR ENCIMA de todo el DOM, incluyendo
 * elementos con z-index: 9999.
 *
 * El componente AutocompleteDropdown crea su dropdown en document.body
 * con position: fixed y z-index: 9999. Cuando el autocomplete se usa
 * DENTRO de un <dialog showModal()>, el dropdown queda invisible detrás
 * del top-layer del navegador.
 *
 * Este módulo resuelve el problema usando un <div> overlay con z-index
 * manual, permitiendo que el AutocompleteDropdown se renderice encima.
 *
 * CUÁNDO USAR CADA UNO:
 * - core_modals.js (modalManager)     → Modales simples sin autocomplete
 * - overlay_modal.js (overlayModal)   → Modales que contienen AutocompleteDropdown
 * ─────────────────────────────────────────────────────────────────
 *
 * HTML esperado:
 *   <div class="overlay-modal" id="mi-modal">
 *     <div class="overlay-modal__backdrop"></div>
 *     <div class="overlay-modal__container">
 *       <div class="overlay-modal__header">
 *         <h2 class="overlay-modal__title">Título</h2>
 *         <button class="overlay-modal__close" onclick="overlayModal.close('mi-modal')">&times;</button>
 *       </div>
 *       <div class="overlay-modal__body">...</div>
 *       <div class="overlay-modal__footer">...</div>
 *     </div>
 *   </div>
 *
 * JS:
 *   overlayModal.open('mi-modal');
 *   overlayModal.close('mi-modal');
 */

window.overlayModal = {
    /**
     * Abre un overlay modal por su ID.
     */
    open: function (id) {
        const modal = document.getElementById(id);
        if (!modal) {
            console.error(`[overlayModal] No se encontró el modal con ID '${id}'`);
            return;
        }
        document.body.style.overflow = 'hidden';
        modal.classList.add('is-open');
        // Focus trap: enfocar el primer input si existe
        requestAnimationFrame(() => {
            const firstInput = modal.querySelector('input:not([type=hidden]), select, textarea');
            if (firstInput) firstInput.focus();
        });
    },

    /**
     * Cierra un overlay modal por su ID.
     */
    close: function (id) {
        const modal = document.getElementById(id);
        if (!modal) return;
        modal.classList.remove('is-open');
        document.body.style.overflow = '';
    },

    /**
     * Muestra un error dentro del modal.
     */
    showError: function (id, message) {
        const modal = document.getElementById(id);
        if (!modal) return;

        let errorBox = modal.querySelector('.modal-error-box');
        if (!errorBox) {
            errorBox = document.createElement('div');
            errorBox.className = 'modal-error-box';
            const body = modal.querySelector('.overlay-modal__body');
            if (body) body.insertBefore(errorBox, body.firstChild);
        }

        errorBox.innerHTML = `<strong>Error:</strong> ${message}`;
        if (!errorBox.classList.contains('show')) {
            errorBox.classList.add('show');
        } else {
            errorBox.classList.remove('shake');
            void errorBox.offsetWidth;
            errorBox.classList.add('shake');
        }
    },

    /**
     * Oculta el error de un modal.
     */
    clearError: function (id) {
        const modal = document.getElementById(id);
        if (!modal) return;
        const errorBox = modal.querySelector('.modal-error-box');
        if (errorBox) errorBox.classList.remove('show', 'shake');
    }
};

// Cerrar al hacer click en el backdrop
document.addEventListener('click', function (e) {
    if (e.target.classList.contains('overlay-modal__backdrop')) {
        const modal = e.target.closest('.overlay-modal');
        if (modal && !modal.hasAttribute('data-no-backdrop-close')) {
            window.overlayModal.close(modal.id);
        }
    }
});

// Cerrar con Escape
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        const openModal = document.querySelector('.overlay-modal.is-open');
        if (openModal && !openModal.hasAttribute('data-no-backdrop-close')) {
            window.overlayModal.close(openModal.id);
        }
    }
});
