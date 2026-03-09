<?php
declare(strict_types=1);

// ARCHIVO: resources/views/admin/monitoreo/reportes.php

$pageTitle = 'Reportes y Estadísticas';
$activePage = 'reportes';
$breadcrumbs = [
    'Inicio' => base_url('/admin'),
    'Monitoreo' => '#',
    'Estadísticas' => '#'
];

ob_start();
?>

<div class="page-header" style="border-bottom:none;">
    <div class="page-header-left">
        <h1>Centro de Reportes</h1>
        <p>Métricas de uso y rendimiento académico transversal.</p>
    </div>
</div>

<div
    style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 100px 20px; text-align: center; background: white; border-radius: 12px; border: 1px dashed var(--border-color); margin-top: 24px;">

    <!-- Heroicon Outline : chart-pie -->
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
        style="width: 64px; height: 64px; color: var(--color-primary); margin-bottom: 24px; opacity: 0.8;">
        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 107.5 7.5h-7.5V6z" />
        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0013.5 3v7.5z" />
    </svg>

    <h2 style="font-size: 20px; color: var(--color-text-dark); margin-bottom: 8px;">Módulo en Construcción</h2>
    <p style="font-size: 15px; color: var(--color-text-light); max-width: 400px; line-height: 1.5;">
        El motor de gráficos avanzados y proyecciones semestrales estará disponible en la próxima actualización del
        sistema.
    </p>

</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/logged_layout.php';
?>