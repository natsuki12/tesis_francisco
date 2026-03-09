<?php
declare(strict_types=1);

// ARCHIVO: resources/views/admin/configuracion/parametros.php

$pageTitle = 'Parámetros del Sistema';
$activePage = 'parametros';
$breadcrumbs = [
    'Inicio' => base_url('/admin'),
    'Configuración' => '#',
    'Parámetros Globales' => '#'
];

$extraCss = '<link rel="stylesheet" href="' . asset('css/admin/configuracion.css') . '">';

ob_start();
?>

<div class="page-header" style="margin-bottom: 32px;">
    <div class="page-header-left">
        <h1>Parámetros Globales</h1>
        <p>Ajustes generales del comportamiento del sistema y seguridad.</p>
    </div>
    <div style="display:flex; gap:12px;">
        <button class="btn btn-outline">Restablecer Valores</button>
        <button class="btn btn-primary" onclick="alert('Configuración Guardada (Mock)')">Guardar Cambios</button>
    </div>
</div>

<div class="config-card-grid">
    <!-- Bloque Seguridad -->
    <div class="config-block">
        <h3>Políticas de Seguridad</h3>
        <p>Restricciones para el acceso y contraseñas de los usuarios.</p>

        <div class="form-group inline">
            <label>Bloqueo tras intentos fallidos</label>
            <div style="display:flex; align-items:center; gap:8px;">
                <input type="number" class="form-input" value="5" style="width: 70px; text-align:center;">
                <span class="param-unit">intentos</span>
            </div>
        </div>

        <div class="form-group inline">
            <label>Duración del bloqueo temporal</label>
            <div style="display:flex; align-items:center; gap:8px;">
                <input type="number" class="form-input" value="15" style="width: 70px; text-align:center;">
                <span class="param-unit">minutos</span>
            </div>
        </div>

        <div class="form-group inline">
            <label>Exigir contraseña alfanumérica fuerte</label>
            <label class="switch">
                <input type="checkbox" checked>
                <span class="slider"></span>
            </label>
        </div>

        <div class="form-group inline">
            <label>Expiración de sesión por inactividad</label>
            <div style="display:flex; align-items:center; gap:8px;">
                <input type="number" class="form-input" value="60" style="width: 70px; text-align:center;">
                <span class="param-unit">minutos</span>
            </div>
        </div>
    </div>

    <!-- Bloque Comportamiento -->
    <div class="config-block">
        <h3>Comportamiento del Aplicativo</h3>
        <p>Ajustes del ciclo de vida de los casos y notificaciones por correo.</p>

        <div class="form-group inline">
            <label>Cierre automático de casos inactivos</label>
            <div style="display:flex; align-items:center; gap:8px;">
                <input type="number" class="form-input" value="30" style="width: 70px; text-align:center;">
                <span class="param-unit">días</span>
            </div>
        </div>

        <div class="form-group inline">
            <label>Notificar al profesor al registrar alumno</label>
            <label class="switch">
                <input type="checkbox" checked>
                <span class="slider"></span>
            </label>
        </div>

        <div class="form-group inline">
            <label>Enviar recordatorio antes de cerrar caso</label>
            <label class="switch">
                <input type="checkbox" checked>
                <span class="slider"></span>
            </label>
        </div>

        <div class="form-group inline">
            <label>Modo de mantenimiento programado</label>
            <label class="switch">
                <input type="checkbox">
                <span class="slider"></span>
            </label>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/logged_layout.php';
?>