/**
 * Modal Información — Global para Sucesiones
 * Replica el modal del SENIAT con soporte para distintos tipos de alerta.
 *
 * Uso:
 *   showModalInfo('La información ha sido registrada satisfactoriamente');
 *   showModalInfo('Ocurrió un error', 'danger');
 *   showModalInfo('Advertencia importante', 'warning');
 *   showModalInfo('Nota informativa', 'info');
 *
 * Tipos válidos: 'success' | 'danger' | 'warning' | 'info'
 */
(function () {
    'use strict';

    // ─── Mapa de tipos → clase de alerta + icono Bootstrap Icons ──────
    var TYPE_MAP = {
        success: { alertClass: 'alert-success', icon: 'bi-check-circle-fill'  },
        danger:  { alertClass: 'alert-danger',  icon: 'bi-x-circle-fill' },
        warning: { alertClass: 'alert-warning', icon: 'bi-exclamation-triangle-fill' },
        info:    { alertClass: 'alert-info',    icon: 'bi-info-circle-fill' }
    };

    /**
     * Muestra el modal de información con el mensaje y tipo dado.
     * @param {string} message     — Texto a mostrar dentro de la alerta
     * @param {string} [type]      — Tipo de alerta: success | danger | warning | info (default: success)
     * @param {boolean} [showFooter] — Si true, muestra el botón "Cerrar" en el footer (default: false)
     */
    function showModalInfo(message, type, showFooter) {
        type = type || 'success';
        var config = TYPE_MAP[type] || TYPE_MAP.success;

        var modal    = document.getElementById('modalInfoSucesiones');
        var backdrop = document.getElementById('modalInfoBackdrop');
        var alert    = document.getElementById('modalInfoAlert');
        var icon     = document.getElementById('modalInfoIcon');
        var msg      = document.getElementById('modalInfoMessage');
        var footer   = document.getElementById('modalInfoFooter');

        if (!modal || !alert || !icon || !msg) return;

        // Limpiar clases previas de alerta
        alert.className = 'alert ' + config.alertClass;

        // Limpiar clases previas de icono y aplicar la nueva
        icon.className = 'bi ' + config.icon;

        // Texto del mensaje
        msg.textContent = message;

        // Mostrar/ocultar footer
        if (footer) footer.style.display = showFooter ? '' : 'none';

        // Mostrar backdrop + modal
        if (backdrop) {
            backdrop.style.display = 'block';
            // Forzar reflow para la transición
            void backdrop.offsetWidth;
            backdrop.classList.add('show');
        }
        modal.style.display = 'block';
        void modal.offsetWidth;
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    /**
     * Oculta el modal de información.
     */
    function hideModalInfo() {
        var modal    = document.getElementById('modalInfoSucesiones');
        var backdrop = document.getElementById('modalInfoBackdrop');

        if (modal) {
            modal.classList.remove('show');
            setTimeout(function () { modal.style.display = 'none'; }, 150);
        }
        if (backdrop) {
            backdrop.classList.remove('show');
            setTimeout(function () { backdrop.style.display = 'none'; }, 150);
        }
        document.body.style.overflow = '';
    }

    // ─── Event listeners (con guard para evitar doble binding) ────────
    var _bound = false;

    function bindEvents() {
        if (_bound) return;
        _bound = true;

        // Botón × del header
        var closeBtn = document.getElementById('modalInfoClose');
        if (closeBtn) closeBtn.addEventListener('click', hideModalInfo);

        // Botón "Cerrar" del footer
        var closeFooter = document.getElementById('modalInfoCloseFooter');
        if (closeFooter) closeFooter.addEventListener('click', hideModalInfo);

        // Click en el backdrop cierra el modal
        var backdrop = document.getElementById('modalInfoBackdrop');
        if (backdrop) backdrop.addEventListener('click', hideModalInfo);

        // Tecla Escape cierra el modal
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                var modal = document.getElementById('modalInfoSucesiones');
                if (modal && modal.classList.contains('show')) {
                    hideModalInfo();
                }
            }
        });
    }

    // Bind al cargar el DOM normalmente
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bindEvents);
    } else {
        // DOM ya cargó (script defer o al final del body)
        bindEvents();
    }

    // ─── Wrapper seguro que garantiza binding antes de mostrar ────────
    function safeShowModalInfo(message, type) {
        bindEvents();            // idempotente — no duplica listeners
        showModalInfo(message, type);
    }

    // ─── Exponer como función global ──────────────────────────────────
    window.showModalInfo = safeShowModalInfo;
    window.hideModalInfo = hideModalInfo;
})();
