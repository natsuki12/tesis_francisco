/**
 * modals_ajax.js
 * Generic handler for Modal Form Submissions via AJAX and specific Input formatting.
 */

import { showToast } from './utils.js';

document.addEventListener('DOMContentLoaded', () => {

    /* =========================================================================
       1. NUMERIC & CURRENCY SANITIZATION (Max 2 Decimals)
       ========================================================================= */
    const formatCurrencyInput = (el) => {
        let val = el.value.trim();
        if (!val) return;

        // Remove everything except digits and first dot/comma
        val = val.replace(/[^0-9.,]/g, '');
        val = val.replace(/,/g, '.'); // Normalize comma to dot

        const parts = val.split('.');
        if (parts.length > 2) {
            val = parts[0] + '.' + parts.slice(1).join('');
        }

        const num = parseFloat(val);
        if (isNaN(num)) {
            el.value = '';
        } else {
            // Format to exactly 2 decimals on blur
            el.value = num.toFixed(2);
        }
    };

    // Restrict typing live
    document.addEventListener('input', (e) => {
        if (e.target.matches('input[data-type="currency"]')) {
            let val = e.target.value;
            // Allow digits and only one separator during typing
            val = val.replace(/[^0-9.,]/g, '');
            e.target.value = val;
        }
    });

    // Format strict 2 decimals on blur
    document.addEventListener('focusout', (e) => {
        if (e.target.matches('input[data-type="currency"]')) {
            formatCurrencyInput(e.target);
        }
    });

    /* =========================================================================
       2. GENERIC AJAX FORM SUBMISSION FOR MODALS
       ========================================================================= */
    document.addEventListener('submit', async (e) => {
        const form = e.target;
        // Only intercept forms that are inside a .modal-base OR explicitly opt-in
        if (!form.closest('.modal-base') && !form.hasAttribute('data-ajax-form')) return;

        // Give forms a way to opt-out if they need native submission
        if (form.hasAttribute('data-no-ajax')) return;

        e.preventDefault();

        const submitBtn = form.querySelector('[type="submit"]');
        const originalBtnText = submitBtn ? submitBtn.innerHTML : '';
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Procesando...';
        }

        const url = form.getAttribute('action');
        const method = (form.getAttribute('method') || 'POST').toUpperCase();

        try {
            const formData = new FormData(form);

            // If the modal has a specific CSRF token appended outside, make sure it's caught
            // Usually it's inside the form thanks to helpers.php

            const response = await fetch(url, {
                method: method,
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            // Attempt to parse JSON
            let result;
            try {
                result = await response.json();
            } catch (jsonErr) {
                // If not JSON, it might be a fatal error or dump
                throw new Error("El servidor devolvió una respuesta no válida (no JSON).");
            }

            if (response.ok && result.success) {
                showToast(result.message || 'Operación exitosa', 'success');

                // Close modal
                const modalId = form.closest('.modal-base')?.id;
                if (modalId && window.modalManager) {
                    window.modalManager.close(modalId);
                }

                // If defined, reset form
                if (!form.hasAttribute('data-no-reset')) {
                    form.reset();
                }

                // Reload or update table/DOM
                // For now, reload the page after a short delay for simplicity in CRUD
                // If it has 'data-no-reload', we don't reload.
                if (!form.hasAttribute('data-no-reload')) {
                    setTimeout(() => window.location.reload(), 800);
                }

            } else {
                // Server returned success:false or 4xx/5xx status
                showToast(result.message || result.error || 'Ocurrió un error en la solicitud.', 'error');
            }

        } catch (error) {
            console.error(error);
            showToast(error.message || 'Error de conexión con el servidor.', 'error');
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            }
        }
    });

});
