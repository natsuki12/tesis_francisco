<?php
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
            --header-height: 56px;
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
            grid-template-areas: "header header" "sidebar main";
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

    <!-- CSS de los parciales -->
    <link rel="stylesheet" href="<?= asset('css/partials/logged/header_logged.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/partials/logged/sidebar_logged.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/global/toast.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/global/components.css') ?>">

    <!-- CSS extra de la página (si existe) -->
    <?php if (isset($extraCss))
        echo $extraCss; ?>
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

    <!-- JS extra de la página (si existe) -->
    <?php if (isset($extraJs))
        echo $extraJs; ?>
</body>

</html>