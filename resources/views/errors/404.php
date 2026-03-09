<?php
$pageTitle = 'Página no encontrada';
$activePage = '';

// Detectar rol para redirigir correctamente
$role = $_SESSION['role_id'] ?? null;
if ($role == 1) {
    $homeUrl = base_url('/admin');
    $homeLabel = 'Volver al Panel';
} elseif ($role == 2) {
    $homeUrl = base_url('/casos-sucesorales');
    $homeLabel = 'Ir a Mis Casos';
} elseif ($role == 3) {
    $homeUrl = base_url('/home');
    $homeLabel = 'Ir al Inicio';
} else {
    $homeUrl = base_url('/');
    $homeLabel = 'Ir al Inicio';
}

// Si el usuario está logueado, usar el layout loggeado; si no, renderizar standalone
$isLogged = isset($_SESSION['user_id']);
ob_start();
?>

<div class="error-page">
    <div class="error-page__content">
        <!-- Ilustración SVG -->
        <div class="error-page__illustration">
            <svg viewBox="0 0 200 160" fill="none" xmlns="http://www.w3.org/2000/svg">
                <!-- Fondo circular -->
                <circle cx="100" cy="80" r="70" fill="var(--blue-50, #eff6ff)" />
                <!-- Documento roto -->
                <rect x="65" y="35" width="50" height="65" rx="4" fill="var(--white, #fff)"
                    stroke="var(--gray-300, #cbd5e1)" stroke-width="2" />
                <line x1="75" y1="50" x2="105" y2="50" stroke="var(--gray-200, #e2e8f0)" stroke-width="3"
                    stroke-linecap="round" />
                <line x1="75" y1="58" x2="100" y2="58" stroke="var(--gray-200, #e2e8f0)" stroke-width="3"
                    stroke-linecap="round" />
                <line x1="75" y1="66" x2="95" y2="66" stroke="var(--gray-200, #e2e8f0)" stroke-width="3"
                    stroke-linecap="round" />
                <!-- Signo de interrogación -->
                <circle cx="135" cy="45" r="18" fill="var(--blue-600, #1a4a8a)" opacity="0.1" />
                <text x="135" y="52" text-anchor="middle" font-size="22" font-weight="700"
                    fill="var(--blue-600, #1a4a8a)">?</text>
                <!-- Lupa -->
                <circle cx="72" cy="110" r="14" fill="none" stroke="var(--gray-400, #94a3b8)" stroke-width="3" />
                <line x1="82" y1="120" x2="92" y2="130" stroke="var(--gray-400, #94a3b8)" stroke-width="3"
                    stroke-linecap="round" />
            </svg>
        </div>

        <!-- Código de error -->
        <h1 class="error-page__code">404</h1>

        <!-- Mensaje principal -->
        <h2 class="error-page__title">Página no encontrada</h2>

        <!-- Descripción -->
        <p class="error-page__description">
            El recurso que buscas no existe, fue movido o no tienes permiso para acceder a él.
            Verifica la URL e inténtalo de nuevo.
        </p>

        <!-- Botones de acción -->
        <div class="error-page__actions">
            <a href="<?= $homeUrl ?>" class="modal-btn modal-btn-primary" style="padding: 12px 28px; font-size: 15px;">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"
                    stroke-linecap="round" style="margin-right:6px;">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                    <polyline points="9 22 9 12 15 12 15 22" />
                </svg>
                <?= $homeLabel ?>
            </a>
            <button onclick="history.back()" class="modal-btn modal-btn-cancel"
                style="padding: 12px 28px; font-size: 15px;">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"
                    stroke-linecap="round" style="margin-right:6px;">
                    <line x1="19" y1="12" x2="5" y2="12" />
                    <polyline points="12 19 5 12 12 5" />
                </svg>
                Volver atrás
            </button>
        </div>
    </div>
</div>

<style>
    .error-page {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 70vh;
        padding: 2rem;
        animation: fadeInUp .4s ease;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .error-page__content {
        text-align: center;
        max-width: 460px;
    }

    .error-page__illustration {
        margin-bottom: 1.5rem;
    }

    .error-page__illustration svg {
        width: 180px;
        height: auto;
    }

    .error-page__code {
        font-size: 5rem;
        font-weight: 800;
        color: var(--gray-200, #e2e8f0);
        margin: 0;
        line-height: 1;
        letter-spacing: -2px;
    }

    .error-page__title {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-primary, #1a1a1a);
        margin: 0.75rem 0 0.5rem;
    }

    .error-page__description {
        font-size: 0.9375rem;
        color: var(--text-light, #718096);
        line-height: 1.6;
        margin-bottom: 2rem;
    }

    .error-page__actions {
        display: flex;
        gap: 12px;
        justify-content: center;
        flex-wrap: wrap;
    }

    .error-page__actions .modal-btn {
        display: inline-flex;
        align-items: center;
    }
</style>

<?php
$content = ob_get_clean();

if ($isLogged) {
    require __DIR__ . '/../layouts/logged_layout.php';
} else {
    // Standalone: página completa sin sidebar/navbar
    ?>
    <!DOCTYPE html>
    <html lang="es">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= $pageTitle ?> — UNIMAR</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
            rel="stylesheet">
        <link rel="stylesheet" href="<?= asset('css/base.css') ?>">
        <link rel="stylesheet" href="<?= asset('css/global/components.css') ?>">
        <style>
            body {
                margin: 0;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                background: var(--gray-50, #f8fafc);
            }
        </style>
    </head>

    <body>
        <?= $content ?>
    </body>

    </html>
    <?php
}
?>