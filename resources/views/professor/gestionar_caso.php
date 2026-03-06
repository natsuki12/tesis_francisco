<?php
declare(strict_types=1);

// ARCHIVO: resources/views/professor/gestionar_caso.php

// 1. Configuración de la Vista
$pageTitle = 'Gestionar Caso — Simulador SENIAT';
$activePage = 'casos-sucesorales'; // Mantiene activa la pestaña en el sidebar

// 2. CSS específico
$extraCss = '<link rel="stylesheet" href="' . asset('css/professor/gestionar_caso.css') . '">';

// 3. JS específico
$extraJs = '<script type="module" src="' . asset('js/professor/gestionar_caso/gestionar_caso.js') . '"></script>';

ob_start();

// Datos "mockeados" temporales (se reemplazarán con DB después)
$titulo = "Familia González Díaz";
$nroCaso = "CS-2024-005";
$estado = "Publicado";
$estadoClase = "status-published"; // status-draft, status-published
$fechaAlta = "15/10/2024";
$causanteStr = "Juan Pablo González Díaz (V-12.345.678)";
?>

<!-- Page Header con Breadcrumbs -->
<div class="gc-header">
    <div class="gc-breadcrumbs">
        <a href="<?= base_url('/casos-sucesorales') ?>">Casos Sucesorales</a>
        <span class="gc-separator">/</span>
        <span class="gc-current">Gestionar Caso
            <?= htmlspecialchars($nroCaso) ?>
        </span>
    </div>

    <div class="gc-header-content">
        <div class="gc-header-left">
            <h1 class="gc-title">
                <?= htmlspecialchars($titulo) ?>
            </h1>
            <p class="gc-subtitle">Causante: <strong>
                    <?= htmlspecialchars($causanteStr) ?>
                </strong></p>
        </div>
        <div class="gc-header-right">
            <div class="gc-badge <?= $estadoClase ?>">
                <?= htmlspecialchars($estado) ?>
            </div>
            <button class="btn btn-secondary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                </svg>
                Editar Datos
            </button>
        </div>
    </div>
</div>

<!-- Tabs de Navegación del Caso -->
<div class="gc-tabs">
    <button class="gc-tab is-active" data-tab="resumen">Resumen General</button>
    <button class="gc-tab" data-tab="patrimonio">Inventario Patrimonial</button>
    <button class="gc-tab" data-tab="asignaciones">Estudiantes Asignados</button>
    <button class="gc-tab" data-tab="configuracion">Configuración</button>
</div>

<!-- Contenedor Principal -->
<div class="gc-content">

    <!-- Tab: Resumen General -->
    <div class="gc-panel is-active" id="tab-resumen">

        <div class="gc-stats-grid">
            <div class="gc-stat-card">
                <div class="gc-stat-icon purple">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                    </svg>
                </div>
                <div class="gc-stat-info">
                    <span class="gc-stat-label">Herederos Totales</span>
                    <span class="gc-stat-value">5</span>
                </div>
            </div>

            <div class="gc-stat-card">
                <div class="gc-stat-icon green">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                        <line x1="12" y1="1" x2="12" y2="23"></line>
                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                    </svg>
                </div>
                <div class="gc-stat-info">
                    <span class="gc-stat-label">Patrimonio Neto</span>
                    <span class="gc-stat-value">Bs. 345.000,00</span>
                </div>
            </div>

            <div class="gc-stat-card">
                <div class="gc-stat-icon blue">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                </div>
                <div class="gc-stat-info">
                    <span class="gc-stat-label">Fecha de Alta</span>
                    <span class="gc-stat-value">
                        <?= htmlspecialchars($fechaAlta) ?>
                    </span>
                </div>
            </div>

            <div class="gc-stat-card">
                <div class="gc-stat-icon amber">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                    </svg>
                </div>
                <div class="gc-stat-info">
                    <span class="gc-stat-label">Bienes y Pasivos</span>
                    <span class="gc-stat-value">12 ítems</span>
                </div>
            </div>
        </div>

        <div class="gc-card">
            <div class="gc-card-header">
                <h3>Detalles del Causante y Representante</h3>
            </div>
            <div class="gc-card-body">
                <div class="gc-info-list">
                    <div class="gc-info-item">
                        <span class="gc-info-label">Tipo de Sucesión</span>
                        <span class="gc-info-value">Con Cédula</span>
                    </div>
                    <div class="gc-info-item">
                        <span class="gc-info-label">Fecha de Fallecimiento</span>
                        <span class="gc-info-value">12/05/2023</span>
                    </div>
                    <div class="gc-info-item">
                        <span class="gc-info-label">Representante Legal</span>
                        <span class="gc-info-value">Pedro González Díaz (V-15.678.901)</span>
                    </div>
                    <div class="gc-info-item">
                        <span class="gc-info-label">Vínculo del Rep.</span>
                        <span class="gc-info-value">Hijo</span>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Tab Placeholder 1 -->
    <div class="gc-panel" id="tab-patrimonio">
        <div class="gc-empty-state">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
                <polyline points="2 17 12 22 22 17"></polyline>
                <polyline points="2 12 12 17 22 12"></polyline>
            </svg>
            <h3>Inventario Patrimonial</h3>
            <p>Aquí se listarán los bienes inmuebles, muebles y pasivos asociados al caso.</p>
            <button class="btn btn-primary btn-sm mt-3">Ver Detalle Completo</button>
        </div>
    </div>

</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/logged_layout.php';
?>