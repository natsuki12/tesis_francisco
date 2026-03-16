<?php
declare(strict_types=1);

// ARCHIVO: resources/views/professor/gestionar_caso.php

// 1. Configuración de la Vista
$pageTitle = 'Gestionar Caso — Simulador SENIAT';
$activePage = 'casos-sucesorales';

// 2. CSS específico
$extraCss = '<link rel="stylesheet" href="' . asset('css/professor/gestionar_caso.css') . '">';

// 3. JS específico
$extraJs = '<script type="module" src="' . asset('js/professor/gestionar_caso/gestionar_caso.js') . '"></script>';

ob_start();

// Extraer datos del caso
$caso = $casoData['caso'];
$source = $casoData['source']; // 'borrador' o 'publicado'

$titulo = htmlspecialchars($caso['titulo'] ?? 'Sin título');
$estado = $caso['estado'] ?? 'Borrador';
$estadoClase = $estado === 'Publicado' ? 'status-completed has-dot' : 'status-review has-dot';
$fechaAlta = isset($caso['created_at']) ? date('d/m/Y', strtotime($caso['created_at'])) : '—';
$casoId = (int) $caso['id'];

// Helper para formatear moneda
function formatBs(float $val): string
{
    return 'Bs. ' . number_format($val, 2, ',', '.');
}

// Helper para mostrar valor o dash
function showVal($val, string $default = '—'): string
{
    return !empty($val) ? htmlspecialchars((string) $val) : $default;
}

if ($source === 'borrador') {
    // ============================
    // DATOS DESDE BORRADOR JSON
    // ============================
    $b = $casoData['borrador'];
    $causante = $b['causante'] ?? [];
    $rep = $b['representante'] ?? [];
    $herederos = $b['herederos'] ?? [];
    $herederos_premuertos = $b['herederos_premuertos'] ?? [];
    $allHerederos = array_merge($herederos, $herederos_premuertos);
    $bienesInmuebles = $b['bienes_inmuebles'] ?? [];
    // bienes_muebles viene como {"1": [...], "2": [...], "length": 0} — aplanar
    $bienesMuebles = [];
    $catNames = [
        '1' => 'Banco',
        '2' => 'Seguro',
        '3' => 'Transporte',
        '4' => 'Opciones de Compra',
        '5' => 'Cuentas y Efectos por Cobrar',
        '6' => 'Semovientes',
        '7' => 'Bonos',
        '8' => 'Acciones',
        '9' => 'Prestaciones Sociales',
        '10' => 'Caja de Ahorro',
        '11' => 'Plantaciones',
        '12' => 'Otros'
    ];
    foreach (($b['bienes_muebles'] ?? []) as $catId => $items) {
        if ($catId === 'length' || !is_array($items))
            continue;
        foreach ($items as $item) {
            $item['categoria'] = $catNames[$catId] ?? "Cat. $catId";
            $bienesMuebles[] = $item;
        }
    }
    // Also load bienes_muebles_banco (new MVC format)
    foreach (($b['bienes_muebles_banco'] ?? []) as $bancoItem) {
        $bancoItem['categoria'] = 'Banco';
        $bancoItem['tipo_nombre'] = $bancoItem['tipo_bien_nombre'] ?? '';
        $bancoItem['es_bien_litigioso'] = ($bancoItem['bien_litigioso'] ?? 'false') === 'true' ? 1 : 0;
        $bienesMuebles[] = $bancoItem;
    }
    $pasivosDeuda = $b['pasivos_deuda'] ?? [];
    $pasivosGastos = $b['pasivos_gastos'] ?? [];
    $exenciones = $b['exenciones'] ?? [];
    $exoneraciones = $b['exoneraciones'] ?? [];
    $prorrogas = $b['prorrogas'] ?? [];
    $herenciaTipos = $b['herencia']['tipos'] ?? [];
    $direcciones = $b['direcciones_causante'] ?? [];
    $config = $b['config'] ?? [];

    $causanteNombre = trim(($causante['nombres'] ?? '') . ' ' . ($causante['apellidos'] ?? ''));
    $causanteCedula = $causante['cedula'] ?? '';
    $causanteStr = !empty($causanteNombre)
        ? $causanteNombre . (!empty($causanteCedula) ? " ({$causante['tipo_cedula']}-{$causanteCedula})" : '')
        : 'No definido';
    $repNombre = trim(($rep['nombres'] ?? '') . ' ' . ($rep['apellidos'] ?? ''));
    $repStr = !empty($repNombre) ? $repNombre . (!empty($rep['cedula']) ? " ({$rep['letra_cedula']}-{$rep['cedula']})" : '') : 'No definido';

    $totalActivos = 0;
    foreach ($bienesInmuebles as $bi)
        $totalActivos += (float) ($bi['valor_declarado'] ?? 0);
    foreach ($bienesMuebles as $bm)
        $totalActivos += (float) ($bm['valor_declarado'] ?? 0);
    $totalPasivos = 0;
    foreach ($pasivosDeuda as $pd)
        $totalPasivos += (float) ($pd['valor_declarado'] ?? 0);
    foreach ($pasivosGastos as $pg)
        $totalPasivos += (float) ($pg['valor_declarado'] ?? 0);
    $patrimonioNeto = $totalActivos - $totalPasivos;
    $totalHerederos = count($allHerederos);
    $totalItems = count($bienesInmuebles) + count($bienesMuebles) + count($pasivosDeuda) + count($pasivosGastos);

} else {
    // ============================
    // DATOS DESDE TABLAS NORMALIZADAS
    // ============================
    $causanteNombre = trim(($caso['causante_nombres'] ?? '') . ' ' . ($caso['causante_apellidos'] ?? ''));
    $causanteCedula = $caso['causante_cedula'] ?? '';
    $causanteStr = !empty($causanteNombre)
        ? $causanteNombre . (!empty($causanteCedula) ? " ({$caso['causante_tipo_cedula']}-{$causanteCedula})" : '')
        : 'No definido';

    $rep = $caso;
    $repNombre = trim(($caso['rep_nombres'] ?? '') . ' ' . ($caso['rep_apellidos'] ?? ''));
    $repStr = !empty($repNombre) ? $repNombre . (!empty($caso['rep_cedula']) ? " ({$caso['rep_tipo_cedula']}-{$caso['rep_cedula']})" : '') : 'No definido';

    $causante = [
        'nombres' => $caso['causante_nombres'] ?? '',
        'apellidos' => $caso['causante_apellidos'] ?? '',
        'cedula' => $caso['causante_cedula'] ?? '',
        'tipo_cedula' => $caso['causante_tipo_cedula'] ?? '',
        'sexo' => $caso['causante_sexo'] ?? '',
        'estado_civil' => $caso['causante_estado_civil'] ?? '',
        'fecha_nacimiento' => $caso['causante_fecha_nacimiento'] ?? '',
    ];

    $acta = $casoData['acta_defuncion'] ?? [];
    $datosFiscales = $casoData['datos_fiscales'] ?? [];
    $allHerederos = $casoData['herederos'] ?? [];
    // Split herederos into regular and premuerto groups
    $herederos = [];
    $herederos_premuertos = [];
    foreach ($allHerederos as $h) {
        $isPre = ($h['es_premuerto'] ?? 0);
        $isPre = ($isPre === 1 || $isPre === '1' || $isPre === 'SI');
        if (isset($h['premuerto_padre_id']) && !empty($h['premuerto_padre_id'])) {
            // This is a heredero OF a premuerto (represents a deceased heir)
            $herederos_premuertos[] = $h;
        } else {
            $herederos[] = $h;
        }
    }
    $bienesInmuebles = $casoData['bienes_inmuebles'] ?? [];
    $bienesMuebles = $casoData['bienes_muebles'] ?? [];
    $pasivosDeuda = $casoData['pasivos_deuda'] ?? [];
    $pasivosGastos = $casoData['pasivos_gastos'] ?? [];
    $exenciones = $casoData['exenciones'] ?? [];
    $exoneraciones = $casoData['exoneraciones'] ?? [];
    $prorrogas = $casoData['prorrogas'] ?? [];
    $herenciaTipos = $casoData['herencia'] ?? [];
    $direcciones = $casoData['direcciones'] ?? [];
    $config = $casoData['config'] ?? [];
    $configs = $casoData['configs'] ?? [];
    $asignaciones = $casoData['asignaciones'] ?? [];

    $resumen = $casoData['resumen'];
    $totalHerederos = $resumen['total_herederos'];
    $totalActivos = $resumen['total_activos'];
    $totalPasivos = $resumen['total_pasivos'];
    $patrimonioNeto = $resumen['patrimonio_neto'];
    $totalItems = $resumen['total_items'];
}
?>

<!-- Page Header con Breadcrumbs -->
<div class="gc-header">
    <div class="gc-breadcrumbs">
        <a href="<?= base_url('/casos-sucesorales') ?>">Casos Sucesorales</a>
        <span class="gc-separator">/</span>
        <span class="gc-current"><?= $titulo ?></span>
    </div>

    <div class="gc-header-content">
        <div class="gc-header-left">
            <h1 class="gc-title"><?= $titulo ?></h1>
            <p class="gc-subtitle">Causante: <strong><?= htmlspecialchars($causanteStr) ?></strong></p>
        </div>
        <div class="gc-header-right">
            <div class="status-badge <?= $estadoClase ?>"><?= htmlspecialchars($estado) ?></div>
            <?php if ($estado === 'Borrador'): ?>
                <a href="<?= base_url('/crear-caso?edit=' . $casoId) ?>" class="btn btn-secondary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                    </svg>
                    Editar Datos
                </a>
            <?php elseif ($estado === 'Inactivo'): ?>
                <button id="btn-reactivar-caso" class="btn btn-primary" data-caso-id="<?= $casoId ?>">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round">
                        <polyline points="23 4 23 10 17 10"></polyline>
                        <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
                    </svg>
                    Reactivar Caso
                </button>
            <?php else: ?>
                <span class="btn btn-secondary" style="opacity:0.6;cursor:not-allowed;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                    Caso Publicado
                </span>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Tabs de Navegación del Caso -->
<div class="gc-tabs">
    <button class="gc-tab is-active" data-tab="resumen">Resumen General</button>
    <button class="gc-tab" data-tab="patrimonio">Inventario Patrimonial</button>
    <button class="gc-tab" data-tab="asignaciones">Asignaciones</button>
</div>

<!-- Contenedor Principal -->
<div class="gc-content">

    <!-- ========================================= -->
    <!-- Tab: Resumen General                      -->
    <!-- ========================================= -->
    <div class="gc-panel is-active" id="tab-resumen">

        <!-- Stats Cards -->
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
                    <span class="gc-stat-value"><?= $totalHerederos ?></span>
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
                    <span class="gc-stat-value"><?= formatBs($patrimonioNeto) ?></span>
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
                    <span class="gc-stat-value"><?= $fechaAlta ?></span>
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
                    <span class="gc-stat-value"><?= $totalItems ?> ítems</span>
                </div>
            </div>
        </div>

        <!-- Causante -->
        <div class="gc-card">
            <div class="gc-card-header">
                <h3>Datos del Causante</h3>
            </div>
            <div class="gc-card-body">
                <div class="gc-info-list">
                    <div class="gc-info-item">
                        <span class="gc-info-label">Nombres</span>
                        <span class="gc-info-value"><?= showVal($causante['nombres'] ?? null) ?></span>
                    </div>
                    <div class="gc-info-item">
                        <span class="gc-info-label">Apellidos</span>
                        <span class="gc-info-value"><?= showVal($causante['apellidos'] ?? null) ?></span>
                    </div>
                    <div class="gc-info-item">
                        <span class="gc-info-label">Cédula</span>
                        <span class="gc-info-value"><?= showVal($causante['cedula'] ?? null) ?></span>
                    </div>
                    <div class="gc-info-item">
                        <span class="gc-info-label">Sexo</span>
                        <span class="gc-info-value"><?= showVal($causante['sexo'] ?? null) ?></span>
                    </div>
                    <div class="gc-info-item">
                        <span class="gc-info-label">Estado Civil</span>
                        <span class="gc-info-value"><?= showVal($causante['estado_civil'] ?? null) ?></span>
                    </div>
                    <div class="gc-info-item">
                        <span class="gc-info-label">Fecha Nacimiento</span>
                        <span
                            class="gc-info-value"><?= !empty($causante['fecha_nacimiento']) ? date('d/m/Y', strtotime($causante['fecha_nacimiento'])) : '—' ?></span>
                    </div>
                    <?php if ($source === 'borrador' && !empty($causante['fecha_fallecimiento'])): ?>
                        <div class="gc-info-item">
                            <span class="gc-info-label">Fecha Fallecimiento</span>
                            <span
                                class="gc-info-value"><?= date('d/m/Y', strtotime($causante['fecha_fallecimiento'])) ?></span>
                        </div>
                    <?php elseif ($source === 'publicado' && !empty($acta['fecha_fallecimiento'])): ?>
                        <div class="gc-info-item">
                            <span class="gc-info-label">Fecha Fallecimiento</span>
                            <span class="gc-info-value"><?= date('d/m/Y', strtotime($acta['fecha_fallecimiento'])) ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="gc-info-item">
                        <span class="gc-info-label">Tipo de Sucesión</span>
                        <span class="gc-info-value"><?= showVal($caso['tipo_sucesion'] ?? null) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Representante -->
        <div class="gc-card">
            <div class="gc-card-header">
                <h3>Representante Legal</h3>
            </div>
            <div class="gc-card-body">
                <div class="gc-info-list">
                    <div class="gc-info-item">
                        <span class="gc-info-label">Nombre Completo</span>
                        <span class="gc-info-value"><?= htmlspecialchars($repStr) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Herencia -->
        <?php if (!empty($herenciaTipos)): ?>
            <div class="gc-card">
                <div class="gc-card-header">
                    <h3>Tipos de Herencia</h3>
                </div>
                <div class="gc-card-body">
                    <div class="gc-tags">
                        <?php foreach ($herenciaTipos as $h): ?>
                            <span class="gc-tag">
                                <?= htmlspecialchars($h['tipo_nombre'] ?? $h['nombre'] ?? "Tipo #{$h['tipo_herencia_id']}") ?>
                                <?php if (!empty($h['subtipo_testamento'])): ?>
                                    <small>(<?= htmlspecialchars($h['subtipo_testamento']) ?>)</small>
                                <?php endif; ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Herederos -->
        <?php if (!empty($herederos)): ?>
            <div class="gc-card">
                <div class="gc-card-header">
                    <h3>Herederos (<?= count($herederos) ?>)</h3>
                </div>
                <div class="gc-card-body gc-table-wrapper">
                    <table class="gc-table">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Cédula</th>
                                <th>Parentesco</th>
                                <th>Premuerto</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($herederos as $h): ?>
                                <tr>
                                    <td><?= htmlspecialchars(($h['nombres'] ?? '') . ' ' . ($h['apellidos'] ?? '')) ?></td>
                                    <td><?= showVal($h['cedula'] ?? null) ?></td>
                                    <td><?= htmlspecialchars($h['parentesco_nombre'] ?? $h['parentesco_id'] ?? '—') ?></td>
                                    <td>
                                        <?php
                                        $isPremuerto = ($h['es_premuerto'] ?? $h['premuerto'] ?? 'NO');
                                        $isPremuerto = ($isPremuerto === 1 || $isPremuerto === '1' || $isPremuerto === 'SI');
                                        ?>
                                        <?php if ($isPremuerto): ?>
                                            <span class="gc-badge-small badge-red">Sí</span>
                                        <?php else: ?>
                                            <span class="gc-badge-small badge-gray">No</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <!-- Herederos de Premuertos -->
        <?php if (!empty($herederos_premuertos)): ?>
            <div class="gc-card">
                <div class="gc-card-header">
                    <h3>Herederos de Premuertos (<?= count($herederos_premuertos) ?>)</h3>
                </div>
                <div class="gc-card-body gc-table-wrapper">
                    <table class="gc-table">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Cédula</th>
                                <th>Parentesco</th>
                                <th>Representa a</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($herederos_premuertos as $hp): ?>
                                <tr>
                                    <td><?= htmlspecialchars(($hp['nombres'] ?? '') . ' ' . ($hp['apellidos'] ?? '')) ?></td>
                                    <td><?= showVal($hp['cedula'] ?? null) ?></td>
                                    <td><?= htmlspecialchars($hp['parentesco_nombre'] ?? $hp['parentesco_id'] ?? '—') ?></td>
                                    <td><?= showVal($hp['premuerto_padre_nombre'] ?? $hp['premuerto_padre_id'] ?? null) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <!-- Direcciones -->
        <?php if (!empty($direcciones)): ?>
            <div class="gc-card">
                <div class="gc-card-header">
                    <h3>Direcciones (<?= count($direcciones) ?>)</h3>
                </div>
                <div class="gc-card-body" style="padding: 0;">
                    <?php foreach ($direcciones as $i => $dir):
                        $tipoDir = str_replace('_', ' ', $dir['tipo_direccion'] ?? 'Sin tipo');
                        $ubicacion = trim(($dir['estado_nombre'] ?? $dir['estado'] ?? '') . ', ' . ($dir['municipio_nombre'] ?? $dir['municipio'] ?? '') . ', ' . ($dir['parroquia_nombre'] ?? $dir['parroquia'] ?? ''), ', ');
                        $vialidad = trim(($dir['tipo_vialidad'] ?? '') . ' ' . ($dir['nombre_vialidad'] ?? ''));
                    ?>
                        <div class="gc-dir-item" data-dir-index="<?= $i ?>">
                            <div class="gc-dir-summary">
                                <div class="gc-dir-summary-left">
                                    <div class="gc-dir-icon">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" width="18" height="18">
                                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                            <circle cx="12" cy="10" r="3"></circle>
                                        </svg>
                                    </div>
                                    <div>
                                        <span class="gc-dir-type"><?= htmlspecialchars($tipoDir) ?></span>
                                        <span class="gc-dir-location"><?= !empty($ubicacion) ? htmlspecialchars($ubicacion) : 'Sin ubicación' ?></span>
                                    </div>
                                </div>
                                <svg class="gc-dir-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" width="16" height="16">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg>
                            </div>
                            <div class="gc-dir-details">
                                <div class="gc-info-list">
                                    <div class="gc-info-item">
                                        <span class="gc-info-label">Tipo Dirección</span>
                                        <span class="gc-info-value"><?= htmlspecialchars($tipoDir) ?></span>
                                    </div>
                                    <div class="gc-info-item">
                                        <span class="gc-info-label">Vialidad</span>
                                        <span class="gc-info-value"><?= showVal($vialidad) ?></span>
                                    </div>
                                    <div class="gc-info-item">
                                        <span class="gc-info-label">Inmueble</span>
                                        <span class="gc-info-value"><?= showVal(($dir['tipo_inmueble'] ?? '') . (!empty($dir['nro_inmueble']) ? ' #' . $dir['nro_inmueble'] : '')) ?></span>
                                    </div>
                                    <div class="gc-info-item">
                                        <span class="gc-info-label">Nivel</span>
                                        <span class="gc-info-value"><?= showVal(($dir['tipo_nivel'] ?? '') . (!empty($dir['nro_nivel']) ? ' ' . $dir['nro_nivel'] : '')) ?></span>
                                    </div>
                                    <div class="gc-info-item">
                                        <span class="gc-info-label">Sector</span>
                                        <span class="gc-info-value"><?= showVal(($dir['tipo_sector'] ?? '') . (!empty($dir['nombre_sector']) ? ' ' . $dir['nombre_sector'] : '')) ?></span>
                                    </div>
                                    <div class="gc-info-item">
                                        <span class="gc-info-label">Estado</span>
                                        <span class="gc-info-value"><?= showVal($dir['estado_nombre'] ?? $dir['estado'] ?? null) ?></span>
                                    </div>
                                    <div class="gc-info-item">
                                        <span class="gc-info-label">Municipio</span>
                                        <span class="gc-info-value"><?= showVal($dir['municipio_nombre'] ?? $dir['municipio'] ?? null) ?></span>
                                    </div>
                                    <div class="gc-info-item">
                                        <span class="gc-info-label">Parroquia</span>
                                        <span class="gc-info-value"><?= showVal($dir['parroquia_nombre'] ?? $dir['parroquia'] ?? null) ?></span>
                                    </div>
                                    <div class="gc-info-item">
                                        <span class="gc-info-label">Ciudad</span>
                                        <span class="gc-info-value"><?= showVal($dir['ciudad_nombre'] ?? $dir['ciudad'] ?? null) ?></span>
                                    </div>
                                    <div class="gc-info-item">
                                        <span class="gc-info-label">Zona Postal</span>
                                        <span class="gc-info-value"><?= showVal($dir['codigo_postal_codigo'] ?? $dir['codigo_postal_id'] ?? null) ?></span>
                                    </div>
                                    <?php if (!empty($dir['telefono_fijo'])): ?>
                                    <div class="gc-info-item">
                                        <span class="gc-info-label">Teléfono Fijo</span>
                                        <span class="gc-info-value"><?= htmlspecialchars($dir['telefono_fijo']) ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (!empty($dir['telefono_celular'])): ?>
                                    <div class="gc-info-item">
                                        <span class="gc-info-label">Celular</span>
                                        <span class="gc-info-value"><?= htmlspecialchars($dir['telefono_celular']) ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (!empty($dir['fax'])): ?>
                                    <div class="gc-info-item">
                                        <span class="gc-info-label">Fax</span>
                                        <span class="gc-info-value"><?= htmlspecialchars($dir['fax']) ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (!empty($dir['punto_referencia'])): ?>
                                    <div class="gc-info-item">
                                        <span class="gc-info-label">Punto de Referencia</span>
                                        <span class="gc-info-value"><?= htmlspecialchars($dir['punto_referencia']) ?></span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Prórrogas -->
        <?php if (!empty($prorrogas)): ?>
            <div class="gc-card">
                <div class="gc-card-header">
                    <h3>Prórrogas (<?= count($prorrogas) ?>)</h3>
                </div>
                <div class="gc-card-body gc-table-wrapper">
                    <table class="gc-table">
                        <thead>
                            <tr>
                                <th>Fecha Solicitud</th>
                                <th>Nro. Resolución</th>
                                <th>Plazo (días)</th>
                                <th>Vencimiento</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($prorrogas as $pr): ?>
                                <tr>
                                    <td><?= !empty($pr['fecha_solicitud']) ? date('d/m/Y', strtotime($pr['fecha_solicitud'])) : '—' ?>
                                    </td>
                                    <td><?= showVal($pr['nro_resolucion'] ?? null) ?></td>
                                    <td><?= showVal($pr['plazo_otorgado_dias'] ?? $pr['plazo_dias'] ?? null) ?></td>
                                    <td><?= !empty($pr['fecha_vencimiento']) ? date('d/m/Y', strtotime($pr['fecha_vencimiento'])) : '—' ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- ========================================= -->
    <!-- Tab: Inventario Patrimonial               -->
    <!-- ========================================= -->
    <div class="gc-panel" id="tab-patrimonio">

        <!-- Resumen financiero -->
        <div class="gc-stats-grid" style="margin-bottom: 2rem;">
            <div class="gc-stat-card">
                <div class="gc-stat-icon green">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
                    </svg>
                </div>
                <div class="gc-stat-info">
                    <span class="gc-stat-label">Total Activos</span>
                    <span class="gc-stat-value"><?= formatBs($totalActivos) ?></span>
                </div>
            </div>
            <div class="gc-stat-card">
                <div class="gc-stat-icon" style="background:#fee2e2;color:#dc2626;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="23 18 13.5 8.5 8.5 13.5 1 6"></polyline>
                    </svg>
                </div>
                <div class="gc-stat-info">
                    <span class="gc-stat-label">Total Pasivos</span>
                    <span class="gc-stat-value"><?= formatBs($totalPasivos) ?></span>
                </div>
            </div>
            <div class="gc-stat-card">
                <div class="gc-stat-icon blue">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="1" x2="12" y2="23"></line>
                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                    </svg>
                </div>
                <div class="gc-stat-info">
                    <span class="gc-stat-label">Patrimonio Neto</span>
                    <span class="gc-stat-value"><?= formatBs($patrimonioNeto) ?></span>
                </div>
            </div>
        </div>

        <!-- Bienes Inmuebles -->
        <div class="gc-card">
            <div class="gc-card-header">
                <h3>Bienes Inmuebles (<?= count($bienesInmuebles) ?>)</h3>
            </div>
            <div class="gc-card-body" style="padding: 0;">
                <?php if (empty($bienesInmuebles)): ?>
                    <p class="gc-empty-text" style="padding: 20px;">No hay bienes inmuebles registrados.</p>
                <?php else: ?>
                    <?php foreach ($bienesInmuebles as $i => $bi):
                        $tipoBien = $bi['tipo_bien_nombres'] ?? $bi['tipo_bien_nombre'] ?? '';
                        $desc = $bi['descripcion'] ?? 'Inmueble #' . ($i + 1);
                        $headerLabel = !empty($tipoBien) ? $tipoBien : $desc;
                        $valor = formatBs((float)($bi['valor_declarado'] ?? 0));
                        $esVivienda = ($bi['vivienda_principal'] ?? $bi['es_vivienda_principal'] ?? 0);
                        $esVivienda = ($esVivienda === 'true' || $esVivienda == 1);
                        $esLitigioso = ($bi['bien_litigioso'] ?? $bi['es_bien_litigioso'] ?? 0);
                        $esLitigioso = ($esLitigioso === 'true' || $esLitigioso == 1);
                    ?>
                        <div class="gc-dir-item" data-dir-index="bi_<?= $i ?>">
                            <div class="gc-dir-summary">
                                <div class="gc-dir-summary-left">
                                    <div class="gc-dir-icon" style="background: var(--green-50); color: var(--green-600);">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" width="18" height="18">
                                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                            <polyline points="9 22 9 12 15 12 15 22"></polyline>
                                        </svg>
                                    </div>
                                    <div>
                                        <span class="gc-dir-type"><?= htmlspecialchars($headerLabel) ?></span>
                                        <span class="gc-dir-location">
                                            <?= $valor ?>
                                            <?php if ($esVivienda): ?> · <span style="color: var(--green-600);">Vivienda Principal</span><?php endif; ?>
                                            <?php if ($esLitigioso): ?> · <span style="color: var(--red-500);">Litigioso</span><?php endif; ?>
                                        </span>
                                    </div>
                                </div>
                                <svg class="gc-dir-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" width="16" height="16">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg>
                            </div>
                            <div class="gc-dir-details">
                                <div class="gc-info-list">
                                    <?php if (!empty($tipoBien)): ?>
                                    <div class="gc-info-item">
                                        <span class="gc-info-label">Tipo de Bien</span>
                                        <span class="gc-info-value"><?= htmlspecialchars($tipoBien) ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <div class="gc-info-item">
                                        <span class="gc-info-label">Porcentaje</span>
                                        <span class="gc-info-value"><?= number_format((float)($bi['porcentaje'] ?? 0), 2) ?>%</span>
                                    </div>
                                    <div class="gc-info-item">
                                        <span class="gc-info-label">Vivienda Principal</span>
                                        <span class="gc-info-value"><?= $esVivienda ? 'Sí' : 'No' ?></span>
                                    </div>
                                    <div class="gc-info-item">
                                        <span class="gc-info-label">Bien Litigioso</span>
                                        <span class="gc-info-value"><?= $esLitigioso ? 'Sí' : 'No' ?></span>
                                    </div>
                                    <div class="gc-info-item">
                                        <span class="gc-info-label">Valor Original</span>
                                        <span class="gc-info-value"><?= formatBs((float)($bi['valor_original'] ?? 0)) ?></span>
                                    </div>
                                    <div class="gc-info-item">
                                        <span class="gc-info-label">Valor Declarado</span>
                                        <span class="gc-info-value" style="font-weight:700;color:var(--green-600);"><?= $valor ?></span>
                                    </div>
                                    <div class="gc-info-item">
                                        <span class="gc-info-label">Sup. Construida</span>
                                        <span class="gc-info-value"><?= showVal($bi['superficie_construida'] ?? null) ?> m²</span>
                                    </div>
                                    <div class="gc-info-item">
                                        <span class="gc-info-label">Sup. No Construida</span>
                                        <span class="gc-info-value"><?= showVal($bi['superficie_no_construida'] ?? null) ?> m²</span>
                                    </div>
                                    <div class="gc-info-item">
                                        <span class="gc-info-label">Área Superficie</span>
                                        <span class="gc-info-value"><?= showVal($bi['area_superficie'] ?? null) ?> m²</span>
                                    </div>
                                    <div class="gc-info-item">
                                        <span class="gc-info-label">Oficina Registro</span>
                                        <span class="gc-info-value"><?= showVal($bi['oficina_registro'] ?? $bi['oficina_subalterna'] ?? null) ?></span>
                                    </div>
                                    <div class="gc-info-item">
                                        <span class="gc-info-label">Nro. Registro</span>
                                        <span class="gc-info-value"><?= showVal($bi['nro_registro'] ?? $bi['numero_registro'] ?? null) ?></span>
                                    </div>
                                    <div class="gc-info-item">
                                        <span class="gc-info-label">Libro</span>
                                        <span class="gc-info-value"><?= showVal($bi['libro'] ?? null) ?></span>
                                    </div>
                                    <div class="gc-info-item">
                                        <span class="gc-info-label">Protocolo</span>
                                        <span class="gc-info-value"><?= showVal($bi['protocolo'] ?? null) ?></span>
                                    </div>
                                    <div class="gc-info-item">
                                        <span class="gc-info-label">Fecha Registro</span>
                                        <span class="gc-info-value"><?= !empty($bi['fecha_registro']) ? date('d/m/Y', strtotime($bi['fecha_registro'])) : '—' ?></span>
                                    </div>
                                    <div class="gc-info-item">
                                        <span class="gc-info-label">Trimestre</span>
                                        <span class="gc-info-value"><?= showVal($bi['trimestre'] ?? null) ?></span>
                                    </div>
                                    <div class="gc-info-item">
                                        <span class="gc-info-label">Asiento Registral</span>
                                        <span class="gc-info-value"><?= showVal($bi['asiento_registral'] ?? null) ?></span>
                                    </div>
                                    <div class="gc-info-item">
                                        <span class="gc-info-label">Matrícula</span>
                                        <span class="gc-info-value"><?= showVal($bi['matricula'] ?? null) ?></span>
                                    </div>
                                    <div class="gc-info-item">
                                        <span class="gc-info-label">Libro Folio Real</span>
                                        <span class="gc-info-value"><?= showVal($bi['folio_real_anio'] ?? $bi['libro_folio_real'] ?? null) ?></span>
                                    </div>
                                    <?php if (!empty($bi['descripcion'])): ?>
                                    <div class="gc-info-item" style="grid-column: 1 / -1;">
                                        <span class="gc-info-label">Descripción</span>
                                        <span class="gc-info-value"><?= htmlspecialchars($bi['descripcion']) ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (!empty($bi['linderos'])): ?>
                                    <div class="gc-info-item" style="grid-column: 1 / -1;">
                                        <span class="gc-info-label">Linderos</span>
                                        <span class="gc-info-value"><?= htmlspecialchars($bi['linderos']) ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (!empty($bi['direccion'])): ?>
                                    <div class="gc-info-item" style="grid-column: 1 / -1;">
                                        <span class="gc-info-label">Dirección</span>
                                        <span class="gc-info-value"><?= htmlspecialchars($bi['direccion']) ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php
                                    $litData = $bi['litigioso_data'] ?? null;
                                    if (!$litData && $esLitigioso) {
                                        // Borrador: datos litigiosos pueden estar inline
                                        $litData = [
                                            'tribunal_causa' => $bi['tribunal_causa'] ?? null,
                                            'numero_expediente' => $bi['numero_expediente'] ?? null,
                                            'partes_juicio' => $bi['partes_juicio'] ?? null,
                                            'estado_juicio' => $bi['estado_juicio'] ?? null,
                                        ];
                                    }
                                    if ($esLitigioso && $litData): ?>
                                    <div style="grid-column: 1 / -1; margin-top: 8px; padding: 12px 16px; background: var(--red-50, #fef2f2); border-left: 3px solid var(--red-400, #f87171); border-radius: 6px;">
                                        <div style="font-weight: 600; color: var(--red-600, #dc2626); margin-bottom: 8px; font-size: 0.85rem;">⚠️ Datos Litigiosos</div>
                                        <div class="gc-info-list" style="gap: 6px 24px;">
                                            <div class="gc-info-item">
                                                <span class="gc-info-label">Tribunal</span>
                                                <span class="gc-info-value"><?= showVal($litData['tribunal_causa'] ?? null) ?></span>
                                            </div>
                                            <div class="gc-info-item">
                                                <span class="gc-info-label">Nro. Expediente</span>
                                                <span class="gc-info-value"><?= showVal($litData['numero_expediente'] ?? null) ?></span>
                                            </div>
                                            <div class="gc-info-item">
                                                <span class="gc-info-label">Partes del Juicio</span>
                                                <span class="gc-info-value"><?= showVal($litData['partes_juicio'] ?? null) ?></span>
                                            </div>
                                            <div class="gc-info-item">
                                                <span class="gc-info-label">Estado del Juicio</span>
                                                <span class="gc-info-value"><?= showVal($litData['estado_juicio'] ?? null) ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="gc-card" id="bmCard">
            <div class="gc-card-header">
                <h3>Bienes Muebles (<?= count($bienesMuebles) ?>)</h3>
            </div>
            <div class="gc-card-body" style="padding: 0;">
                <?php if (empty($bienesMuebles)): ?>
                    <p class="gc-empty-text" style="padding: 20px;">No hay bienes muebles registrados.</p>
                <?php else: ?>
                    <?php
                    // Collect unique categories for filter pills
                    $bmCats = [];
                    foreach ($bienesMuebles as $bm) {
                        $c = $bm['categoria_nombre'] ?? $bm['categoria'] ?? 'Otros';
                        if (!in_array($c, $bmCats)) $bmCats[] = $c;
                    }
                    ?>
                    <div class="gc-filter-bar" id="bmFilterBar">
                        <button class="gc-filter-pill is-active" data-filter="all">Todos (<?= count($bienesMuebles) ?>)</button>
                        <?php foreach ($bmCats as $catName):
                            $catCount = count(array_filter($bienesMuebles, fn($b) => ($b['categoria_nombre'] ?? $b['categoria'] ?? 'Otros') === $catName));
                        ?>
                            <button class="gc-filter-pill" data-filter="<?= htmlspecialchars($catName) ?>"><?= htmlspecialchars($catName) ?> (<?= $catCount ?>)</button>
                        <?php endforeach; ?>
                    </div>
                    <div id="bmItems">
                    <?php foreach ($bienesMuebles as $i => $bm):
                        $cat = $bm['categoria_nombre'] ?? $bm['categoria'] ?? 'Sin categoría';
                        $tipo = $bm['tipo_nombre'] ?? '';
                        $descBm = $bm['descripcion'] ?? 'Bien mueble #' . ($i + 1);
                        $valorBm = formatBs((float)($bm['valor_declarado'] ?? 0));
                        $esLitigiosoBm = ($bm['es_bien_litigioso'] ?? 0) == 1;
                    ?>
                        <div class="gc-dir-item" data-dir-index="bm_<?= $i ?>" data-bm-cat="<?= htmlspecialchars($cat) ?>">
                            <div class="gc-dir-summary">
                                <div class="gc-dir-summary-left">
                                    <div class="gc-dir-icon" style="background: var(--purple-50, #f5f3ff); color: var(--purple-600, #7c3aed);">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" width="18" height="18">
                                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <span class="gc-dir-type"><?= htmlspecialchars($cat) ?><?= !empty($tipo) ? ' — ' . htmlspecialchars($tipo) : '' ?></span>
                                        <span class="gc-dir-location">
                                            <?= $valorBm ?>
                                            <?php if ($esLitigiosoBm): ?> · <span style="color: var(--red-500);">Litigioso</span><?php endif; ?>
                                        </span>
                                    </div>
                                </div>
                                <svg class="gc-dir-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" width="16" height="16">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg>
                            </div>
                            <div class="gc-dir-details">
                                <div class="gc-info-list">
                                    <div class="gc-info-item">
                                        <span class="gc-info-label">Categoría</span>
                                        <span class="gc-info-value"><?= htmlspecialchars($cat) ?></span>
                                    </div>
                                    <?php if (!empty($tipo)): ?>
                                    <div class="gc-info-item">
                                        <span class="gc-info-label">Tipo</span>
                                        <span class="gc-info-value"><?= htmlspecialchars($tipo) ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <div class="gc-info-item">
                                        <span class="gc-info-label">Porcentaje</span>
                                        <span class="gc-info-value"><?= number_format((float)($bm['porcentaje'] ?? 0), 2) ?>%</span>
                                    </div>
                                    <div class="gc-info-item">
                                        <span class="gc-info-label">Bien Litigioso</span>
                                        <span class="gc-info-value"><?= $esLitigiosoBm ? 'Sí' : 'No' ?></span>
                                    </div>
                                    <div class="gc-info-item">
                                        <span class="gc-info-label">Valor Declarado</span>
                                        <span class="gc-info-value" style="font-weight:700;color:var(--green-600);"><?= $valorBm ?></span>
                                    </div>
                                    <?php
                                    $catCheck = strtolower($bm['categoria_nombre'] ?? $bm['categoria'] ?? '');
                                    if (strpos($catCheck, 'banco') !== false):
                                        $bancoNombre = $bm['banco_nombre'] ?? $bm['nombre_banco'] ?? $bm['banco'] ?? '—';
                                        $numCuenta = $bm['numero_cuenta'] ?? $bm['numeroCuenta'] ?? '—';
                                    ?>
                                    <div class="gc-info-item">
                                        <span class="gc-info-label">Banco</span>
                                        <span class="gc-info-value"><?= htmlspecialchars($bancoNombre) ?></span>
                                    </div>
                                    <div class="gc-info-item">
                                        <span class="gc-info-label">Número de Cuenta</span>
                                        <span class="gc-info-value"><?= htmlspecialchars($numCuenta) ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (!empty($bm['descripcion'])): ?>
                                    <div class="gc-info-item" style="grid-column: 1 / -1;">
                                        <span class="gc-info-label">Descripción</span>
                                        <span class="gc-info-value"><?= htmlspecialchars($bm['descripcion']) ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php
                                    $litDataBm = $bm['litigioso_data'] ?? null;
                                    if (!$litDataBm && $esLitigiosoBm) {
                                        $litDataBm = [
                                            'tribunal_causa' => $bm['tribunal_causa'] ?? null,
                                            'numero_expediente' => $bm['numero_expediente'] ?? null,
                                            'partes_juicio' => $bm['partes_juicio'] ?? null,
                                            'estado_juicio' => $bm['estado_juicio'] ?? null,
                                        ];
                                    }
                                    if ($esLitigiosoBm && $litDataBm): ?>
                                    <div style="grid-column: 1 / -1; margin-top: 8px; padding: 12px 16px; background: var(--red-50, #fef2f2); border-left: 3px solid var(--red-400, #f87171); border-radius: 6px;">
                                        <div style="font-weight: 600; color: var(--red-600, #dc2626); margin-bottom: 8px; font-size: 0.85rem;">⚠️ Datos Litigiosos</div>
                                        <div class="gc-info-list" style="gap: 6px 24px;">
                                            <div class="gc-info-item">
                                                <span class="gc-info-label">Tribunal</span>
                                                <span class="gc-info-value"><?= showVal($litDataBm['tribunal_causa'] ?? null) ?></span>
                                            </div>
                                            <div class="gc-info-item">
                                                <span class="gc-info-label">Nro. Expediente</span>
                                                <span class="gc-info-value"><?= showVal($litDataBm['numero_expediente'] ?? null) ?></span>
                                            </div>
                                            <div class="gc-info-item">
                                                <span class="gc-info-label">Partes del Juicio</span>
                                                <span class="gc-info-value"><?= showVal($litDataBm['partes_juicio'] ?? null) ?></span>
                                            </div>
                                            <div class="gc-info-item">
                                                <span class="gc-info-label">Estado del Juicio</span>
                                                <span class="gc-info-value"><?= showVal($litDataBm['estado_juicio'] ?? null) ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Pasivos Deuda -->
        <div class="gc-card">
            <div class="gc-card-header">
                <h3>Pasivos — Deudas (<?= count($pasivosDeuda) ?>)</h3>
            </div>
            <div class="gc-card-body gc-table-wrapper">
                <?php if (empty($pasivosDeuda)): ?>
                    <p class="gc-empty-text">No hay deudas registradas.</p>
                <?php else: ?>
                    <table class="gc-table">
                        <thead>
                            <tr>
                                <th>Tipo</th>
                                <th>Descripción</th>
                                <th>Porcentaje</th>
                                <th>Valor Declarado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pasivosDeuda as $pd): ?>
                                <tr>
                                    <td><?= showVal($pd['tipo_nombre'] ?? $pd['tipo'] ?? null) ?></td>
                                    <td><?= showVal($pd['descripcion'] ?? null, 'Sin descripción') ?></td>
                                    <td><?= number_format((float) ($pd['porcentaje'] ?? 0), 2) ?>%</td>
                                    <td class="gc-money"><?= formatBs((float) ($pd['valor_declarado'] ?? 0)) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Pasivos Gastos -->
        <div class="gc-card">
            <div class="gc-card-header">
                <h3>Pasivos — Gastos (<?= count($pasivosGastos) ?>)</h3>
            </div>
            <div class="gc-card-body gc-table-wrapper">
                <?php if (empty($pasivosGastos)): ?>
                    <p class="gc-empty-text">No hay gastos registrados.</p>
                <?php else: ?>
                    <table class="gc-table">
                        <thead>
                            <tr>
                                <th>Tipo</th>
                                <th>Descripción</th>
                                <th>Porcentaje</th>
                                <th>Valor Declarado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pasivosGastos as $pg): ?>
                                <tr>
                                    <td><?= showVal($pg['tipo_nombre'] ?? $pg['tipo'] ?? null) ?></td>
                                    <td><?= showVal($pg['descripcion'] ?? null, 'Sin descripción') ?></td>
                                    <td><?= number_format((float) ($pg['porcentaje'] ?? 0), 2) ?>%</td>
                                    <td class="gc-money"><?= formatBs((float) ($pg['valor_declarado'] ?? 0)) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Exenciones -->
        <?php if (!empty($exenciones)): ?>
            <div class="gc-card">
                <div class="gc-card-header">
                    <h3>Exenciones (<?= count($exenciones) ?>)</h3>
                </div>
                <div class="gc-card-body gc-table-wrapper">
                    <table class="gc-table">
                        <thead>
                            <tr>
                                <th>Tipo</th>
                                <th>Descripción</th>
                                <th>Valor Declarado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($exenciones as $ex): ?>
                                <tr>
                                    <td><?= showVal($ex['tipo'] ?? null) ?></td>
                                    <td><?= showVal($ex['descripcion'] ?? null) ?></td>
                                    <td class="gc-money"><?= formatBs((float) ($ex['valor_declarado'] ?? 0)) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <!-- Exoneraciones -->
        <?php if (!empty($exoneraciones)): ?>
            <div class="gc-card">
                <div class="gc-card-header">
                    <h3>Exoneraciones (<?= count($exoneraciones) ?>)</h3>
                </div>
                <div class="gc-card-body gc-table-wrapper">
                    <table class="gc-table">
                        <thead>
                            <tr>
                                <th>Tipo</th>
                                <th>Descripción</th>
                                <th>Valor Declarado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($exoneraciones as $exo): ?>
                                <tr>
                                    <td><?= showVal($exo['tipo'] ?? null) ?></td>
                                    <td><?= showVal($exo['descripcion'] ?? null) ?></td>
                                    <td class="gc-money"><?= formatBs((float) ($exo['valor_declarado'] ?? 0)) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- ========================================= -->
    <!-- Tab: Estudiantes Asignados                -->
    <!-- ========================================= -->
    <div class="gc-panel" id="tab-asignaciones">
        <div class="gc-asig-toolbar">
            <button class="btn btn-primary" id="btnNuevaAsignacion" data-caso-id="<?= $caso['id'] ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Nueva Asignación
            </button>
        </div>

        <?php if (empty($configs)): ?>
            <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
                <h3>Sin Asignaciones</h3>
                <p>Aún no se han creado asignaciones para este caso.</p>
            </div>
        <?php else: ?>
            <?php foreach ($configs as $idx => $cfg): ?>
                <div
                    class="gc-card gc-assignment-card<?= ($cfg['status'] ?? 'Activo') === 'Inactivo' ? ' gc-card-inactive' : '' ?>">
                    <div class="gc-card-header gc-assignment-header">
                        <div>
                            <h3>Asignación
                                #<?= $idx + 1 ?><?= ($cfg['status'] ?? 'Activo') === 'Inactivo' ? ' <span class="gc-badge-small badge-gray">Inactiva</span>' : '' ?>
                            </h3>
                            <div class="gc-assignment-badges">
                                <span
                                    class="gc-badge-pill <?= ($cfg['modalidad'] ?? '') === 'Evaluacion' ? 'pill-red' : 'pill-blue' ?>">
                                    <?= htmlspecialchars(str_replace('_', ' ', $cfg['modalidad'] ?? 'Sin modalidad')) ?>
                                </span>
                                <span class="gc-badge-pill pill-gray">
                                    <?= ($cfg['max_intentos'] ?? 0) == 0 ? 'Ilimitados' : ($cfg['max_intentos'] . ' intento' . ((int) $cfg['max_intentos'] !== 1 ? 's' : '')) ?>
                                </span>
                                <?php if (!empty($cfg['fecha_apertura'])): ?>
                                    <span class="gc-badge-pill pill-gray">Desde:
                                        <?= date('d/m/Y H:i', strtotime($cfg['fecha_apertura'])) ?></span>
                                <?php endif; ?>
                                <span class="gc-badge-pill pill-gray">
                                    <?= !empty($cfg['fecha_limite']) ? 'Hasta: ' . date('d/m/Y H:i', strtotime($cfg['fecha_limite'])) : 'Sin límite' ?>
                                </span>
                            </div>
                        </div>
                        <?php if (($cfg['status'] ?? 'Activo') === 'Activo'): ?>
                            <div class="gc-card-actions">
                                <button class="btn btn-sm btn-outline btnEditConfig" data-config-id="<?= $cfg['id'] ?>"
                                    title="Editar">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16"
                                        height="16">
                                        <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"></path>
                                        <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                    </svg>
                                    Editar
                                </button>
                                <button class="btn btn-sm btn-outline-danger btnDeleteConfig" data-config-id="<?= $cfg['id'] ?>"
                                    title="Eliminar">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16"
                                        height="16">
                                        <polyline points="3 6 5 6 21 6"></polyline>
                                        <path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"></path>
                                    </svg>
                                    Eliminar
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="gc-card-body gc-table-wrapper">
                        <?php if (empty($cfg['estudiantes'])): ?>
                            <p class="gc-empty-text">No hay estudiantes en esta asignación.</p>
                        <?php else: ?>
                            <table class="gc-table">
                                <thead>
                                    <tr>
                                        <th>Estudiante</th>
                                        <th>Cédula</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cfg['estudiantes'] as $a): ?>
                                        <tr>
                                            <td><?= htmlspecialchars(($a['nombres'] ?? '') . ' ' . ($a['apellidos'] ?? '')) ?></td>
                                            <td><?= showVal($a['cedula'] ?? null) ?></td>
                                            <td>
                                                <span
                                                    class="gc-badge-small <?= ($a['estado'] ?? '') === 'Completado' ? 'badge-green' : 'badge-amber' ?>">
                                                    <?= showVal($a['estado'] ?? null, 'Pendiente') ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>

<script>
    (function () {
        const btn = document.getElementById('btn-reactivar-caso');
        if (!btn) return;

        const baseUrl = '<?= base_url('') ?>'.replace(/\/+$/, '');

        btn.addEventListener('click', async () => {
            btn.disabled = true;
            btn.textContent = 'Reactivando...';
            try {
                const res = await fetch(baseUrl + '/api/casos/' + btn.dataset.casoId + '/estado', {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ estado: 'Publicado' })
                });
                const data = await res.json();
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Error al reactivar.');
                    btn.disabled = false;
                    btn.textContent = 'Reactivar Caso';
                }
            } catch (err) {
                alert('Error de conexión.');
                btn.disabled = false;
                btn.textContent = 'Reactivar Caso';
            }
        });
    })();
</script>

<!-- Modal: Crear/Editar Asignación -->
<div class="gc-modal-overlay" id="asignacionModal" style="display:none">
    <div class="gc-modal">
        <div class="gc-modal-header">
            <h3 id="asignacionModalTitle">Nueva Asignación</h3>
            <button class="gc-modal-close" id="btnCloseModal">&times;</button>
        </div>
        <div class="gc-modal-body">
            <form id="formAsignacion">
                <input type="hidden" id="editConfigId" value="">

                <div class="gc-form-row">
                    <div class="gc-form-group">
                        <label for="cfgModalidad">Modalidad *</label>
                        <select id="cfgModalidad" class="gc-input" required>
                            <option value="Practica_Libre">Práctica Libre</option>
                            <option value="Practica_guiada">Práctica Guiada</option>
                            <option value="Evaluacion">Evaluación</option>
                        </select>
                    </div>
                    <div class="gc-form-group">
                        <label for="cfgMaxIntentos">Máx. Intentos <small>(0 = ilimitados)</small></label>
                        <input type="number" id="cfgMaxIntentos" class="gc-input" value="0" min="0">
                    </div>
                </div>

                <div class="gc-form-row">
                    <div class="gc-form-group">
                        <label for="cfgFechaApertura">Fecha Apertura</label>
                        <input type="datetime-local" id="cfgFechaApertura" class="gc-input">
                    </div>
                    <div class="gc-form-group">
                        <label for="cfgFechaLimite">Fecha Cierre</label>
                        <input type="datetime-local" id="cfgFechaLimite" class="gc-input">
                    </div>
                </div>

                <hr class="gc-divider">

                <div class="gc-form-group">
                    <label>Estudiantes</label>
                    <div class="gc-student-search">
                        <input type="text" id="studentSearch" class="gc-input"
                            placeholder="Buscar por nombre o cédula...">
                        <div id="studentResults" class="gc-student-results" style="display:none"></div>
                    </div>
                    <div id="selectedStudents" class="gc-selected-students"></div>
                </div>
            </form>
        </div>
        <div class="gc-modal-footer">
            <button class="btn btn-secondary" id="btnCancelModal">Cancelar</button>
            <button class="btn btn-primary" id="btnSaveAsignacion">Guardar</button>
        </div>
    </div>
</div>

<style>
  .gc-card-header { cursor: pointer; user-select: none; }
  .gc-card-header .gc-chevron { width: 20px; height: 20px; transition: transform 0.25s ease; flex-shrink: 0; margin-left: 12px; color: var(--cc-slate-400, #94a3b8); }
  .gc-card.is-collapsed .gc-card-header .gc-chevron { transform: rotate(-90deg); }
  .gc-card.is-collapsed .gc-card-body { display: none; }

  /* Direcciones Accordion */
  .gc-dir-item { border-bottom: 1px solid var(--gray-100); }
  .gc-dir-item:last-child { border-bottom: none; }
  .gc-dir-summary {
    display: flex; align-items: center; justify-content: space-between;
    padding: 14px 20px; cursor: pointer; user-select: none;
    transition: background 0.15s ease;
  }
  .gc-dir-summary:hover { background: var(--gray-50); }
  .gc-dir-summary-left { display: flex; align-items: center; gap: 12px; }
  .gc-dir-icon {
    width: 36px; height: 36px; border-radius: 10px;
    background: var(--blue-50); color: var(--blue-500);
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
  }
  .gc-dir-type {
    display: block; font-size: var(--text-sm); font-weight: 600;
    color: var(--gray-800); line-height: 1.3;
  }
  .gc-dir-location {
    display: block; font-size: var(--text-xs); color: var(--gray-500);
    margin-top: 1px;
  }
  .gc-dir-chevron {
    width: 16px; height: 16px; color: var(--gray-400);
    transition: transform 0.25s ease; flex-shrink: 0;
  }
  .gc-dir-item.is-open .gc-dir-chevron { transform: rotate(180deg); }
  .gc-dir-item.is-open .gc-dir-summary { background: var(--gray-50); }
  .gc-dir-details {
    display: none; padding: 12px 20px 16px 68px;
  }
  .gc-dir-item.is-open .gc-dir-details { display: block; }
  .gc-dir-details .gc-info-list {
    grid-template-columns: repeat(3, 1fr);
    gap: 14px 24px;
  }
  .gc-dir-details .gc-info-value { font-size: var(--text-sm); }

  /* Filter pill bar */
  .gc-filter-bar {
    display: flex; gap: 6px; padding: 12px 20px;
    border-bottom: 1px solid var(--gray-100);
    overflow-x: auto; flex-wrap: wrap;
  }
  .gc-filter-pill {
    padding: 5px 14px; border-radius: 20px;
    font-size: var(--text-xs); font-weight: 500;
    border: 1px solid var(--gray-200); background: var(--white, #fff);
    color: var(--gray-600); cursor: pointer;
    transition: all 0.15s ease; white-space: nowrap;
  }
  .gc-filter-pill:hover { border-color: var(--blue-300); color: var(--blue-600); }
  .gc-filter-pill.is-active {
    background: var(--blue-500); color: #fff;
    border-color: var(--blue-500);
  }
</style>
<script>
    window.__casoId = <?= (int) $caso['id'] ?>;
    window.__baseUrl = '<?= base_url('') ?>'.replace(/\/+$/, '');
</script>
<script>
(function() {
  const STORAGE_KEY = 'gc_cards_state_' + window.__casoId;

  function loadState() {
    try { return JSON.parse(localStorage.getItem(STORAGE_KEY)) || {}; } catch { return {}; }
  }
  function saveState(state) {
    try { localStorage.setItem(STORAGE_KEY, JSON.stringify(state)); } catch {}
  }

  const cards = document.querySelectorAll('.gc-card');
  const state = loadState();
  const isFirstVisit = Object.keys(state).length === 0;

  cards.forEach((card, idx) => {
    const header = card.querySelector('.gc-card-header');
    const body = card.querySelector('.gc-card-body');
    if (!header || !body) return;

    // Add chevron icon
    const chevron = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    chevron.setAttribute('class', 'gc-chevron');
    chevron.setAttribute('viewBox', '0 0 24 24');
    chevron.setAttribute('fill', 'none');
    chevron.setAttribute('stroke', 'currentColor');
    chevron.setAttribute('stroke-width', '2');
    chevron.setAttribute('stroke-linecap', 'round');
    chevron.setAttribute('stroke-linejoin', 'round');
    chevron.innerHTML = '<polyline points="6 9 12 15 18 9"></polyline>';
    header.appendChild(chevron);

    // Apply state: collapsed by default on first visit, restore on revisit
    const key = 'card_' + idx;
    const isCollapsed = isFirstVisit ? true : (state[key] !== false);
    if (isCollapsed) {
      card.classList.add('is-collapsed');
    }

    header.addEventListener('click', function(e) {
      // Don't toggle if clicking buttons inside the header
      if (e.target.closest('button, a')) return;

      const collapsed = card.classList.toggle('is-collapsed');
      const st = loadState();
      st[key] = collapsed;
      saveState(st);
    });
  });

  // Direcciones / Bienes accordion
  document.querySelectorAll('.gc-dir-summary').forEach(function(summary) {
    summary.addEventListener('click', function() {
      summary.closest('.gc-dir-item').classList.toggle('is-open');
    });
  });

  // Bienes Muebles category filter
  var bmFilterBar = document.getElementById('bmFilterBar');
  if (bmFilterBar) {
    bmFilterBar.querySelectorAll('.gc-filter-pill').forEach(function(pill) {
      pill.addEventListener('click', function() {
        bmFilterBar.querySelectorAll('.gc-filter-pill').forEach(function(p) { p.classList.remove('is-active'); });
        pill.classList.add('is-active');
        var filter = pill.getAttribute('data-filter');
        document.querySelectorAll('#bmItems .gc-dir-item').forEach(function(item) {
          item.classList.remove('is-open'); // collapse on filter change
          if (filter === 'all' || item.getAttribute('data-bm-cat') === filter) {
            item.style.display = '';
          } else {
            item.style.display = 'none';
          }
        });
      });
    });
  }
})();
</script>
<script src="<?= base_url('assets/js/professor/gestionar_caso/asignaciones.js') ?>"></script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/logged_layout.php';
?>