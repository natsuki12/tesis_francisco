<?php
declare(strict_types=1);

// ARCHIVO: resources/views/admin/monitoreo/bitacora.php

$pageTitle = 'Bitácora de Auditoría';
$activePage = 'bitacora';
$breadcrumbs = [
    'Inicio' => base_url('/admin'),
    'Monitoreo' => '#',
    'Bitácora' => '#'
];

$extraCss = '<link rel="stylesheet" href="' . asset('css/professor/casos_sucesorales.css') . '">';

ob_start();
?>

<div class="page-header">
    <div class="page-header-left">
        <h1>Bitácora de Eventos</h1>
        <p>Registro inmutable de todas las acciones sensibles dentro de la plataforma.</p>
    </div>
    <button class="btn btn-outline" onclick="alert('Funcionalidad de exportación CSV diferida.')">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
            stroke-linecap="round">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
            <polyline points="7 10 12 15 17 10" />
            <line x1="12" y1="15" x2="12" y2="3" />
        </svg>
        Exportar Log
    </button>
</div>

<div class="table-container">
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 15%">Timestamp</th>
                <th style="width: 25%">Usuario (Actor)</th>
                <th style="width: 15%">Módulo</th>
                <th style="width: 45%">Descripción del Evento</th>
            </tr>
        </thead>
        <tbody style="font-size: 13px; font-family: 'Inter', sans-serif;">
            <tr>
                <td style="color: var(--color-text-light);">08 Mar 2026, 14:32:01</td>
                <td><strong>admin@ucab.edu.ve</strong><br><span
                        style="color: var(--color-text-light); font-size:11px;">IP: 192.168.1.45</span></td>
                <td><span class="status-badge" style="background:#f1f5f9; color:#475569;">Configuración</span></td>
                <td>Actualización del valor de Unidad Tributaria de Bs. 0.40 a Bs. 9.00</td>
            </tr>
            <tr>
                <td style="color: var(--color-text-light);">08 Mar 2026, 11:15:44</td>
                <td><strong>cesar.requena@ucab.edu.ve</strong><br><span
                        style="color: var(--color-text-light); font-size:11px;">IP: 200.11.23.4</span></td>
                <td><span class="status-badge" style="background:#eff6ff; color:#1d4ed8;">Casos</span></td>
                <td>Caso V-12345678 promovido a estado PUBLICADO</td>
            </tr>
            <tr>
                <td style="color: var(--color-text-light);">07 Mar 2026, 09:00:12</td>
                <td><strong>System Auth</strong><br><span style="color: var(--color-text-light); font-size:11px;">IP:
                        Internal</span></td>
                <td><span class="status-badge" style="background:#fef2f2; color:#b91c1c;">Seguridad</span></td>
                <td>Bloqueo preventivo de estudiante por exceso de intentos (Sección 1A)</td>
            </tr>
        </tbody>
    </table>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/logged_layout.php';
?>