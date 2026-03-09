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
document.addEventListener('click', function (event) {
    if (event.target.tagName === 'DIALOG' && event.target.classList.contains('modal-base')) {
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
