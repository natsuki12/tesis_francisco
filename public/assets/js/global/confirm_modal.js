/**
 * confirm_modal.js
 * Global reusable confirm modal — available as window.showConfirm()
 * Loaded by logged_layout.php for all authenticated views.
 *
 * Usage:
 *   const ok = await window.showConfirm({
 *       title: 'Eliminar Respaldo',
 *       message: '¿Está seguro?',
 *       icon: 'danger',              // 'danger' | 'warning' | 'info'
 *       confirmText: 'Eliminar',     // default: 'Aceptar'
 *       confirmStyle: 'danger',      // 'danger' | 'primary' | 'warning'
 *       cancelText: 'Cancelar'       // default: 'Cancelar'
 *   });
 *   if (ok) { ... }
 */
(function () {
    'use strict';

    var ICONS = {
        danger: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>',
        warning: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
        info: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>'
    };

    window.showConfirm = function (opts) {
        if (typeof opts === 'string') opts = { message: opts };

        var title = opts.title || 'Confirmación';
        var message = opts.message || '';
        var iconType = opts.icon || 'warning';
        var confirmText = opts.confirmText || 'Aceptar';
        var confirmStyle = opts.confirmStyle || (iconType === 'danger' ? 'danger' : 'primary');
        var cancelText = opts.cancelText || 'Cancelar';

        return new Promise(function (resolve) {
            var overlay = document.createElement('div');
            overlay.className = 'confirm-overlay';
            overlay.innerHTML =
                '<div class="confirm-card">' +
                '  <div class="confirm-header">' +
                '    <div class="confirm-icon confirm-icon--' + iconType + '">' + (ICONS[iconType] || ICONS.warning) + '</div>' +
                '    <h3 class="confirm-title">' + title + '</h3>' +
                '  </div>' +
                '  <div class="confirm-body">' + message + '</div>' +
                '  <div class="confirm-footer">' +
                '    <button class="confirm-btn confirm-btn--cancel" id="gcmNo">' + cancelText + '</button>' +
                '    <button class="confirm-btn confirm-btn--' + confirmStyle + '" id="gcmYes">' + confirmText + '</button>' +
                '  </div>' +
                '</div>';

            document.body.appendChild(overlay);

            requestAnimationFrame(function () {
                overlay.classList.add('is-visible');
            });

            function close(val) {
                overlay.classList.remove('is-visible');
                setTimeout(function () {
                    overlay.remove();
                    resolve(val);
                }, 200);
            }

            overlay.querySelector('#gcmNo').addEventListener('click', function () { close(false); });
            overlay.querySelector('#gcmYes').addEventListener('click', function () { close(true); });
            overlay.addEventListener('click', function (e) {
                if (e.target === overlay) close(false);
            });
        });
    };
})();
