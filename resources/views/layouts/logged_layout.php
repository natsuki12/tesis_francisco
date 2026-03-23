<?php
// Pseudo-cron: verificar si toca respaldo automático (solo admin, ultra-ligero)
if (($_SESSION['role_id'] ?? 0) == 1) {
    \App\Core\BackupMiddleware::check();
}

// Datos por defecto del usuario (si no vienen del controlador)
if (!isset($user)) {
    $user = [
        'name' => 'ESTUDIANTE UNIMAR',
        'role' => 'Estudiante',
        'avatar' => asset('img/brand/avatar.png')
    ];
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Simulador' ?> - UNIMAR</title>

    <!-- Google Fonts (v2 Design System) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Outfit:wght@600;700;800&display=swap"
        rel="stylesheet">

    <!-- CSS Base Global (Variables y Tipografía) -->
    <link rel="stylesheet" href="<?= asset('css/base.css') ?>">

    <!-- CSS Base del Layout (estructura) -->
    <style>
        /* Variables globales */
        :root {
            --sim-blue: #0056AC;
            /* Azul actualizado según referencia */
            --sim-blue-dark: #003d7a;
            --sim-blue-light: #e6f0fa;
            --sim-text: #1f2937;
            --sim-text-light: #6b7280;
            --sim-border: #dfe5ee;
            --sim-bg: #f5f7fb;
            --sim-white: #ffffff;
            --sim-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            --sim-shadow-lg: 0 4px 6px rgba(0, 0, 0, 0.07);
            --header-height: 71px;
            --sidebar-width: 240px;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            height: 100%;
            margin: 0;
        }

        body {
            color: var(--sim-text);
            background: var(--sim-bg);
            line-height: 1.5;
        }

        /* Layout Grid */
        .sim-layout {
            display: grid;
            grid-template-areas: "sidebar header" "sidebar main";
            grid-template-rows: var(--header-height) 1fr;
            grid-template-columns: var(--sidebar-width) 1fr;
            height: 100vh;
            overflow: hidden;
        }

        /* Main Content */
        .sim-main {
            grid-area: main;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 32px 36px;
        }

        @media (max-width: 1024px) {
            .sim-layout {
                grid-template-areas: "header" "main";
                grid-template-columns: 1fr;
            }

            .sim-main {
                padding: 24px 16px;
            }
        }

        .sim-container {
            width: 100%;
            max-width: 100%;
            margin: 0;
            padding: 0;
        }
    </style>

    <!-- CSS extra de la página (Bootstrap, etc.) — carga ANTES de los parciales -->
    <?php if (isset($extraCss))
        echo $extraCss; ?>

    <!-- CSS de los parciales (cargan DESPUÉS para tener prioridad sobre Bootstrap) -->
    <link rel="stylesheet" href="<?= asset('css/partials/logged/header_logged.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/partials/logged/sidebar_logged.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/global/toast.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/global/components.css') ?>">
</head>

<body class="sim-layout">

    <?php include __DIR__ . '/../partials/logged/header_logged.php'; ?>

    <div class="sim-sidebar-backdrop" id="sidebarBackdrop"></div>

    <?php include __DIR__ . '/../partials/logged/sidebar_logged.php'; ?>

    <main class="sim-main">
        <div class="sim-container">
            <?= $content ?? '' ?>
        </div>
    </main>

    <script>
        const toggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        const backdrop = document.getElementById('sidebarBackdrop');
        const body = document.body;

        if (toggle && sidebar) {
            toggle.addEventListener('click', () => {
                body.classList.toggle('sim-layout--sidebar-open');
            });
        }

        if (backdrop) {
            backdrop.addEventListener('click', () => {
                body.classList.remove('sim-layout--sidebar-open');
            });
        }

        document.querySelectorAll('[data-toggle="submenu"]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const parent = btn.closest('.sim-nav__item--parent');
                if (parent) parent.classList.toggle('sim-nav__item--expanded');
            });
        });
    </script>

    <!-- Global text sanitization (removes dangerous characters from all text inputs) -->
    <script src="<?= asset('js/global/sanitize.js') ?>"></script>

    <!-- Global Flash Toast (session-based, one-shot) -->
    <?php if (!empty($_SESSION['flash_toast'])): 
        $flash = $_SESSION['flash_toast'];
        unset($_SESSION['flash_toast']);
        $fType = htmlspecialchars($flash['type'] ?? 'success');
        $fMsg  = htmlspecialchars($flash['msg'] ?? '');
    ?>
    <div id="cc-toast-container"></div>
    <script>
    (function(){
        var container = document.getElementById('cc-toast-container');
        var icons = {
            success: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>',
            error:   '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>',
            warning: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
            info:    '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>'
        };
        var type = <?= json_encode($fType) ?>;
        var msg  = <?= json_encode($fMsg) ?>;
        var toast = document.createElement('div');
        toast.className = 'cc-toast cc-toast--' + type;
        toast.innerHTML = '<span class="cc-toast__icon">' + (icons[type] || icons.info) + '</span>'
            + '<span class="cc-toast__msg">' + msg + '</span>'
            + '<button class="cc-toast__close" onclick="this.parentElement.classList.add(\'cc-toast--exit\');setTimeout(function(){toast.remove()},300)">✕</button>';
        container.appendChild(toast);
        setTimeout(function(){ toast.classList.add('cc-toast--exit'); setTimeout(function(){ toast.remove(); },300); }, 5000);
    })();
    </script>
    <?php endif; ?>

    <!-- Global Modal Manager (Native <dialog> API) -->
    <script src="<?= asset('js/global/core_modals.js') ?>"></script>

    <!-- Global AJAX Handlers & Formatters for Modals -->
    <script type="module" src="<?= asset('js/global/modals_ajax.js') ?>"></script>

    <?php
    // ── Exit-confirmation dialog (solo dentro del simulador con sesión activa) ──
    $isSimSession = !empty($_SESSION['sim_asignacion_id']);
    if ($isSimSession):
        ?>
        <!-- Dialog: confirmar salida del simulador -->
        <dialog id="exitSimDialog" class="modal-base">
            <div class="modal-base__container" style="max-width:420px;">
                <div class="modal-base__header" style="border-bottom:1px solid #e5e7eb;">
                    <h3 class="modal-base__title" style="color:#1f2937; margin:0; font-size:1.25rem; font-weight:700;">
                        ¿Salir de la asignación?</h3>
                    <button class="modal-base__close" onclick="window.modalManager.close('exitSimDialog')">✕</button>
                </div>
                <div class="modal-base__body" style="text-align:center; padding:28px 24px;">
                    <svg viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="#d97706" stroke-width="1.5"
                        stroke-linecap="round" stroke-linejoin="round" style="margin-bottom:12px;">
                        <path
                            d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                        <line x1="12" y1="9" x2="12" y2="13" />
                        <line x1="12" y1="17" x2="12.01" y2="17" />
                    </svg>
                    <p style="margin:0; font-size:.9375rem; color:#4b5563; line-height:1.6;">
                        Tienes un intento de asignación en progreso.<br>
                        Si sales, tu progreso no guardado podría perderse.
                    </p>
                </div>
                <div class="modal-base__footer">
                    <button class="modal-btn modal-btn-cancel" onclick="window.modalManager.close('exitSimDialog')">
                        Cancelar
                    </button>
                    <button class="modal-btn modal-btn-warning" id="exitSimConfirmBtn">
                        Salir de la asignación
                    </button>
                </div>
            </div>
        </dialog>

        <script>
            (function () {
                var basePath = <?= json_encode(rtrim((string) ($_ENV['APP_BASE'] ?? getenv('APP_BASE') ?: ''), '/')) ?>;
                var simPrefix = basePath + '/simulador';
                var pendingHref = null;

                document.addEventListener('click', function (e) {
                    var link = e.target.closest('a');
                    if (!link) return;

                    var href = link.getAttribute('href');
                    // Ignorar: sin href, anclas, javascript:
                    if (!href || href === '#' || href.charAt(0) === '#' || href.indexOf('javascript:') === 0) return;

                    // Resolver URL absoluta
                    try {
                        var url = new URL(href, window.location.origin);
                    } catch (_) { return; }

                    // Si es enlace externo (otro dominio) → interceptar
                    if (url.origin !== window.location.origin) {
                        e.preventDefault();
                        pendingHref = href;
                        window.modalManager.open('exitSimDialog');
                        return;
                    }

                    // Si es ruta del simulador → dejar pasar
                    if (url.pathname === simPrefix || url.pathname.indexOf(simPrefix + '/') === 0) return;

                    // Cualquier otra ruta interna → interceptar
                    e.preventDefault();
                    pendingHref = href;
                    window.modalManager.open('exitSimDialog');
                });

                document.getElementById('exitSimConfirmBtn').addEventListener('click', function () {
                    if (pendingHref) {
                        // Quitar basePath del destino (base_url() en el backend lo vuelve a agregar)
                        var cleanDest = pendingHref;
                        if (basePath && cleanDest.indexOf(basePath) === 0) {
                            cleanDest = cleanDest.substring(basePath.length);
                        }
                        window.location.href = basePath + '/api/simulador/salir?dest=' + encodeURIComponent(cleanDest);
                    }
                });
            })();
        </script>
    <?php endif; ?>

    <!-- JS extra de la página (si existe) -->
    <?php if (isset($extraJs))
        echo $extraJs; ?>
</body>

</html>