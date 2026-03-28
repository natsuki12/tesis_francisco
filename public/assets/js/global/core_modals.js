/**
 * core_modals.js
 * Generic modal manager for the native <dialog> HTML5 element.
 * Provides a global window.modalManager object.
 */

window.modalManager = {
    /**
     * Abre un modal por su ID.
     * @param {string} id - El ID del elemento <dialog>
     */
    open: function (id) {
        const dialog = document.getElementById(id);
        if (dialog) {
            // Prevent scrolling on body when modal is open
            document.body.style.overflow = 'hidden';
            dialog.showModal();
        } else {
            console.error(`ModalManager: No se encontró el modal con ID '${id}'`);
        }
    },

    /**
     * Muestra un error dinámico dentro del modal especificado.
     * @param {string} id - El ID del elemento <dialog>
     * @param {string} message - El mensaje de error a mostrar
     */
    showError: function (id, message) {
        const dialog = document.getElementById(id);
        if (!dialog) return;
        
        // Find existing error box or create one dynamically
        let errorBox = dialog.querySelector('.modal-error-box');
        if (!errorBox) {
            errorBox = document.createElement('div');
            errorBox.className = 'modal-error-box';
            
            const body = dialog.querySelector('.modal-base__body');
            if (body) {
                // Insert above the form grid if possible
                const firstGrid = body.querySelector('.form-grid') || body.firstChild;
                body.insertBefore(errorBox, firstGrid);
            }
        }
        
        errorBox.innerHTML = `<strong>Error:</strong> ${message}`;
        
        if (!errorBox.classList.contains('show')) {
            errorBox.classList.add('show');
            const body = dialog.querySelector('.modal-base__body');
            if (body) body.scrollTo({ top: 0, behavior: 'smooth' });
        } else {
            errorBox.classList.remove('shake');
            void errorBox.offsetWidth; // Trigger CSS reflow
            errorBox.classList.add('shake');
        }
    },

    /**
     * Oculta suavemente el mensaje de error de un modal.
     * @param {string} id - El ID del elemento <dialog>
     */
    clearError: function (id) {
        const dialog = document.getElementById(id);
        if (!dialog) return;
        const errorBox = dialog.querySelector('.modal-error-box');
        if (errorBox) {
            errorBox.classList.remove('show', 'shake');
        }
    },

    /**
     * Pone un botón modal en estado de carga (Native Spinner UI).
     * @param {HTMLButtonElement} btn - El botón a modificar
     */
    setButtonLoading: function (btn) {
        if (!btn) return;
        btn.disabled = true;
        btn.classList.add('btn-loading-state');
    },

    /**
     * Devuelve el botón a su estado normal (oculta el spinner y restaura el texto).
     * @param {HTMLButtonElement} btn - El botón a restaurar
     */
    resetButtonLoading: function (btn) {
        if (!btn) return;
        btn.disabled = false;
        btn.classList.remove('btn-loading-state');
    },

    /**
     * Cierra un modal por su ID.
     * @param {string} id - El ID del elemento <dialog>
     */
    close: function (id) {
        const dialog = document.getElementById(id);
        if (dialog) {
            dialog.close();
            // Restore scrolling
            document.body.style.overflow = '';
        }
    }
};

// Cerrar el modal al hacer clic en el backdrop (el espacio oscuro fuera de la tarjeta)
// Dialogs con data-no-backdrop-close se saltan esta lógica
document.addEventListener('click', function (event) {
    if (event.target.tagName === 'DIALOG' && event.target.classList.contains('modal-base')) {
        if (event.target.hasAttribute('data-no-backdrop-close')) return;
        const rect = event.target.getBoundingClientRect();
        const isInDialog = (
            rect.top <= event.clientY &&
            event.clientY <= rect.top + rect.height &&
            rect.left <= event.clientX &&
            event.clientX <= rect.left + rect.width
        );
        if (!isInDialog) {
            window.modalManager.close(event.target.id);
        }
    }
});
