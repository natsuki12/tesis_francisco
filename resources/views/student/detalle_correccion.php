<?php
declare(strict_types=1);

// ARCHIVO: resources/views/student/detalle_correccion.php

$pageTitle  = 'Mi Corrección — Simulador SENIAT';
$activePage = 'mis-calificaciones';

$esRechazadoSinRif = $esRechazadoSinRif ?? false;
$extraCss = '<link rel="stylesheet" href="' . asset('css/professor/detalle_intento.css') . '">'
          . '<link rel="stylesheet" href="' . asset('css/student/detalle_correccion.css') . '">'
          . ($esRechazadoSinRif ? '<link rel="stylesheet" href="' . asset('css/professor/generacion_rs_detalle.css') . '">' : '');

// ── Datos del intento ─────────────────────────────────────────────
$casoTitulo       = htmlspecialchars($intento['caso_titulo'] ?? 'Sin caso');
$numeroIntento    = (int) ($intento['numero_intento'] ?? 1);
$maxIntentos      = (int) ($intento['max_intentos'] ?? 1);
$asignacionId     = (int) ($asignacionId ?? $intento['asignacion_id'] ?? 0);
$fechaEnvio       = $intento['fecha_envio']    ? date('d/m/Y H:i', strtotime($intento['fecha_envio']))    : '—';
$fechaRevision    = $intento['fecha_revision'] ? date('d/m/Y',     strtotime($intento['fecha_revision'])) : '—';
$tipoCalificacion = $intento['tipo_calificacion'] ?? 'aprobado_reprobado';
$notaNum          = $intento['nota_numerica']   ?? null;
$notaCual         = $intento['nota_cualitativa'] ?? null;
$observacion      = $intento['observacion']     ?? '';
$modalidad        = $intento['modalidad']       ?? '—';
$seccion          = $intento['seccion']         ?? '—';

$profesorNombre = trim(
    htmlspecialchars($intento['profesor_nombres'] ?? '') . ' ' .
    htmlspecialchars($intento['profesor_apellidos'] ?? '')
);

$estadoLabel = match ($intento['estado'] ?? '') {
    'Aprobado'  => 'Aprobado',
    'Rechazado' => 'No Aprobado',
    default     => ucfirst(str_replace('_', ' ', $intento['estado'] ?? '')),
};

$statusClass = match ($estadoLabel) {
    'Aprobado'    => 'status-calificado',
    'No Aprobado' => 'status-danger',
    default       => 'status-enviado',
};

// ── Nota para mostrar ─────────────────────────────────────────────
if ($tipoCalificacion === 'numerica' && $notaNum !== null) {
    $notaDisplay  = number_format((float) $notaNum, 1) . ' / 20';
    $notaClass    = (float) $notaNum >= 10 ? 'pass' : 'fail';
} elseif ($notaCual !== null) {
    $notaDisplay = $notaCual;
    $notaClass   = $notaCual === 'Aprobado' ? 'pass' : 'fail';
} else {
    $notaDisplay = '—';
    $notaClass   = '';
}

// ── Datos de comparación (solo para caso NO-RIF) ──────────────────
$score = $comparacion['score'] ?? [];
$score += ['correctas' => 0, 'con_errores' => 0, 'omitidas' => 0, 'de_mas' => 0, 'total_esperado' => 0, 'porcentaje' => 0];
$resumenSecciones = $comparacion['resumen_secciones'] ?? [];
$secciones        = $comparacion['secciones']         ?? [];
$autoItems        = $comparacion['autoliquidacion']   ?? [];
$herederosCalc    = $comparacion['herederos_calculo'] ?? [];
$hayComparacion   = $score['total_esperado'] > 0;

ob_start();
?>

<!-- Breadcrumb -->
<div class="breadcrumb">
    <a href="<?= base_url('/mis-asignaciones') ?>">Mis Asignaciones</a>
    <span class="breadcrumb-sep">›</span>
    <a href="<?= base_url('/mis-asignaciones/' . $asignacionId) ?>"><?= $casoTitulo ?></a>
    <span class="breadcrumb-sep">›</span>
    <span class="breadcrumb-current">Corrección — Intento #<?= $numeroIntento ?></span>
</div>

<!-- Header -->
<div class="intento-header animate-in">
    <div class="intento-header-left">
        <h2><?= $casoTitulo ?></h2>
        <div class="intento-meta">
            <span class="intento-meta-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                </svg>
                Sección: <strong><?= htmlspecialchars($seccion) ?></strong>
            </span>
            <span class="intento-meta-item">
                Intento <strong>#<?= $numeroIntento ?> de <?= $maxIntentos ?></strong>
            </span>
            <span class="intento-meta-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                Enviado: <strong><?= $fechaEnvio ?></strong>
            </span>
            <?php if ($fechaRevision !== '—'): ?>
            <span class="intento-meta-item">
                Revisado: <strong><?= $fechaRevision ?></strong>
            </span>
            <?php endif; ?>
        </div>
    </div>
    <span class="intento-status-big <?= $statusClass ?>"><?= htmlspecialchars($estadoLabel) ?></span>
</div>

<!-- Content Layout (70 / 30) -->
<div class="correction-layout">

    <!-- ═══ MAIN AREA ═══ -->
    <div class="correction-main">

        <?php if ($esRechazadoSinRif): ?>

        <!-- Banner: rechazado en etapa de inscripción de RIF -->
        <div class="rif-banner rif-banner--rechazado animate-in" style="margin-bottom:16px;">
            <div class="rif-banner-label">Rechazado en Inscripción de RIF Sucesoral</div>
            <div class="rif-banner-meta">Este intento fue rechazado antes de completar la declaración. Solo se registraron los datos de causante, relaciones y direcciones.</div>
        </div>

        <?php
        // Calcular totales para badges de tabs
        $causanteTotal = 0; $causanteOk = 0;
        if (!($causante['vacio'] ?? true)) {
            foreach ($causante['campos'] as $c) { $causanteTotal++; if ($c['coincide']) $causanteOk++; }
        }
        $relacionesTotal = 0; $relacionesOk = 0;
        foreach ($relaciones['representante'] ?? [] as $c) { $relacionesTotal++; if ($c['coincide']) $relacionesOk++; }
        foreach ($relaciones['herederos'] ?? [] as $h) {
            $hCampos = $h['campos'] ?? $h;
            foreach ($hCampos as $c) { if (is_array($c)) { $relacionesTotal++; if ($c['coincide']) $relacionesOk++; } }
        }
        $direccionesTotal = 0; $direccionesOk = 0;
        foreach ($direcciones ?? [] as $campos) {
            foreach ($campos as $c) { $direccionesTotal++; if ($c['coincide']) $direccionesOk++; }
        }
        ?>

        <!-- Tabs RS -->
        <div class="rs-tabs-bar animate-in">
            <button class="rs-tab rs-tab--active" data-target="rif-panel-causante">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                Causante
                <span class="rs-tab-badge <?= $causanteTotal > 0 && $causanteOk === $causanteTotal ? 'badge-ok' : 'badge-err' ?>"><?= $causanteOk ?>/<?= $causanteTotal ?></span>
            </button>
            <button class="rs-tab" data-target="rif-panel-relaciones">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                Relaciones
                <span class="rs-tab-badge <?= $relacionesTotal > 0 && $relacionesOk === $relacionesTotal ? 'badge-ok' : 'badge-err' ?>"><?= $relacionesOk ?>/<?= $relacionesTotal ?></span>
            </button>
            <button class="rs-tab" data-target="rif-panel-direcciones">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                Direcciones
                <span class="rs-tab-badge <?= $direccionesTotal > 0 && $direccionesOk === $direccionesTotal ? 'badge-ok' : 'badge-err' ?>"><?= $direccionesOk ?>/<?= $direccionesTotal ?></span>
            </button>
        </div>

        <!-- Panel: Causante -->
        <div class="rs-panel-content animate-in" id="rif-panel-causante">
            <?php if ($causante['vacio'] ?? true): ?>
                <div class="rs-panel-empty">No se encontraron datos del causante en el intento.</div>
            <?php else: ?>
                <table class="comparison-table">
                    <thead>
                        <tr>
                            <th>Campo</th>
                            <th>Valor correcto</th>
                            <th>Tu respuesta</th>
                            <th style="width:32px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($causante['campos'] as $campo): ?>
                        <tr>
                            <td class="field-label"><?= htmlspecialchars($campo['label']) ?></td>
                            <td><?= htmlspecialchars($campo['esperado'] ?: '—') ?></td>
                            <td class="<?= $campo['coincide'] ? 'cell-correct' : 'cell-error' ?>"><?= htmlspecialchars($campo['ingresado'] ?: '—') ?></td>
                            <td class="cell-match-icon <?= $campo['coincide'] ? 'match-ok' : 'match-fail' ?>">
                                <?php if ($campo['coincide']): ?>
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
                                <?php else: ?>
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Panel: Relaciones -->
        <div class="rs-panel-content animate-in" id="rif-panel-relaciones" style="display:none;">
            <?php if (!empty($relaciones['representante']) || !empty($relaciones['herederos'])): ?>
                <table class="comparison-table">
                    <thead>
                        <tr>
                            <th>Campo</th>
                            <th>Valor correcto</th>
                            <th>Tu respuesta</th>
                            <th style="width:32px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($relaciones['representante'])): ?>
                            <tr class="rs-group-row"><td colspan="4">Representante de la Sucesión</td></tr>
                            <?php foreach ($relaciones['representante'] as $campo): ?>
                            <tr>
                                <td class="field-label"><?= htmlspecialchars($campo['label']) ?></td>
                                <td><?= htmlspecialchars($campo['esperado'] ?: '—') ?></td>
                                <td class="<?= $campo['coincide'] ? 'cell-correct' : 'cell-error' ?>"><?= htmlspecialchars($campo['ingresado'] ?: '—') ?></td>
                                <td class="cell-match-icon <?= $campo['coincide'] ? 'match-ok' : 'match-fail' ?>">
                                    <?php if ($campo['coincide']): ?>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
                                    <?php else: ?>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <?php foreach ($relaciones['herederos'] as $idx => $heredero): ?>
                            <?php
                                $tipo   = $heredero['tipo'] ?? 'match';
                                $campos = $heredero['campos'] ?? $heredero;
                                if ($tipo === 'faltante') {
                                    $hLabel = 'Heredero — No ingresado';
                                } elseif ($tipo === 'extra') {
                                    $hLabel = 'Heredero Extra — No existe en el caso';
                                } else {
                                    $hLabel = 'Heredero #' . ($idx + 1);
                                }
                            ?>
                            <tr class="rs-group-row"><td colspan="4"><?= $hLabel ?></td></tr>
                            <?php foreach ($campos as $campo): ?>
                            <tr>
                                <td class="field-label"><?= htmlspecialchars($campo['label']) ?></td>
                                <td><?= htmlspecialchars($campo['esperado'] ?: '—') ?></td>
                                <td class="<?= $campo['coincide'] ? 'cell-correct' : 'cell-error' ?>"><?= htmlspecialchars($campo['ingresado'] ?: '—') ?></td>
                                <td class="cell-match-icon <?= $campo['coincide'] ? 'match-ok' : 'match-fail' ?>">
                                    <?php if ($campo['coincide']): ?>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
                                    <?php else: ?>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="rs-panel-empty">No se encontraron relaciones en el intento.</div>
            <?php endif; ?>
        </div>

        <!-- Panel: Direcciones -->
        <div class="rs-panel-content animate-in" id="rif-panel-direcciones" style="display:none;">
            <?php if (empty($direcciones)): ?>
                <div class="rs-panel-empty">No se encontraron direcciones en el intento.</div>
            <?php else: ?>
                <?php foreach ($direcciones as $idx => $campos): ?>
                    <?php
                        $dirOk = 0; $dirTotal = count($campos);
                        foreach ($campos as $c) { if ($c['coincide']) $dirOk++; }
                        $dirTieneErrores = ($dirOk < $dirTotal);
                    ?>
                    <div class="rs-dir-card <?= $dirTieneErrores ? 'rs-dir-card--error' : 'rs-dir-card--ok' ?>">
                        <button class="rs-dir-toggle" type="button" onclick="this.parentElement.classList.toggle('rs-dir-card--open')">
                            <div class="rs-dir-toggle-left">
                                <svg class="rs-dir-chevron" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="9 18 15 12 9 6"/></svg>
                                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                Dirección #<?= $idx + 1 ?>
                            </div>
                            <span class="rs-tab-badge <?= $dirTieneErrores ? 'badge-err' : 'badge-ok' ?>"><?= $dirOk ?>/<?= $dirTotal ?></span>
                        </button>
                        <div class="rs-dir-body">
                            <table class="comparison-table">
                                <thead>
                                    <tr>
                                        <th>Campo</th>
                                        <th>Valor correcto</th>
                                        <th>Tu respuesta</th>
                                        <th style="width:32px;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($campos as $campo): ?>
                                    <tr>
                                        <td class="field-label"><?= htmlspecialchars($campo['label']) ?></td>
                                        <td><?= htmlspecialchars($campo['esperado'] ?: '—') ?></td>
                                        <td class="<?= $campo['coincide'] ? 'cell-correct' : 'cell-error' ?>"><?= htmlspecialchars($campo['ingresado'] ?: '—') ?></td>
                                        <td class="cell-match-icon <?= $campo['coincide'] ? 'match-ok' : 'match-fail' ?>">
                                            <?php if ($campo['coincide']): ?>
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
                                            <?php else: ?>
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php else: ?>

        <!-- PDF Links -->
        <div class="sidebar-panel animate-in">
            <div class="sidebar-panel-header">
                <h3>Documentos del Intento</h3>
            </div>
            <div class="sidebar-panel-body">
                <div style="display:flex; gap:12px; flex-wrap:wrap;">
                    <a href="<?= base_url('/simulador/sucesion/planilla_pdf?intento_id=' . $intento['intento_id']) ?>"
                       target="_blank" class="btn btn-outline" style="text-decoration:none;">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                        </svg>
                        Planilla DS-99032
                    </a>
                    <a href="<?= base_url('/simulador/sucesion/declaracion_pdf?intento_id=' . $intento['intento_id']) ?>"
                       target="_blank" class="btn btn-outline" style="text-decoration:none;">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                            <line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/>
                        </svg>
                        Resumen de Declaración
                    </a>
                </div>
            </div>
        </div>

        <?php if ($hayComparacion): ?>
        <!-- Score Summary -->
        <div class="sidebar-panel animate-in" style="margin-top:16px;">
            <div class="sidebar-panel-header">
                <h3>Resumen de Comparación</h3>
                <span class="score-badge <?= $score['porcentaje'] >= 70 ? 'score-pass' : 'score-fail' ?>">
                    <?= $score['porcentaje'] ?>% correcto
                </span>
            </div>
            <div class="sidebar-panel-body" style="padding:0;">
                <table class="resumen-table">
                    <thead>
                        <tr>
                            <th>Sección</th>
                            <th class="col-num">✓</th>
                            <th class="col-num">✗</th>
                            <th class="col-num">Omitidas</th>
                            <th class="col-num">De más</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($resumenSecciones as $rs): ?>
                        <tr>
                            <td><?= htmlspecialchars($rs['nombre']) ?></td>
                            <td class="col-num text-green"><?= $rs['correctas'] ?></td>
                            <td class="col-num text-red"><?= $rs['con_errores'] ?></td>
                            <td class="col-num text-amber"><?= $rs['omitidas'] ?></td>
                            <td class="col-num text-gray"><?= $rs['de_mas'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td><strong>Total</strong></td>
                            <td class="col-num text-green"><strong><?= $score['correctas'] ?? 0 ?></strong></td>
                            <td class="col-num text-red"><strong><?= $score['con_errores'] ?? 0 ?></strong></td>
                            <td class="col-num text-amber"><strong><?= $score['omitidas'] ?? 0 ?></strong></td>
                            <td class="col-num text-gray"><strong><?= $score['de_mas'] ?? 0 ?></strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Detailed Comparison Accordions -->
        <div class="sidebar-panel animate-in" style="margin-top:16px;">
            <div class="sidebar-panel-header">
                <h3>Detalle por Sección</h3>
            </div>
            <div class="sidebar-panel-body" style="padding:8px 0;">
                <?php foreach ($secciones as $i => $seccion):
                    $totalCampos = 0; $errores = 0;
                    foreach ($seccion['grupos'] as $g) {
                        if ($g['label'] === 'Cantidad') continue;
                        foreach ($g['campos'] as $c) {
                            $totalCampos++;
                            if (!$c['correcto']) $errores++;
                        }
                    }
                ?>
                <div class="accordion" id="accordion-<?= $i ?>">
                    <div class="accordion-header" onclick="toggleAccordion(<?= $i ?>)">
                        <span class="accordion-title">
                            <?= htmlspecialchars($seccion['titulo']) ?>
                            <?php if ($errores === 0): ?>
                                <span class="accordion-badge badge-correct">Todo correcto ✓</span>
                            <?php else: ?>
                                <span class="accordion-badge badge-errors"><?= $errores ?> error<?= $errores > 1 ? 'es' : '' ?></span>
                            <?php endif; ?>
                        </span>
                        <svg class="accordion-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                            <polyline points="6 9 12 15 18 9"/>
                        </svg>
                    </div>
                    <div class="accordion-body">
                        <div class="accordion-content">
                            <?php foreach ($seccion['grupos'] as $g): ?>
                                <div class="grupo-header"><?= htmlspecialchars($g['label']) ?></div>
                                <table class="comparison-table">
                                    <thead>
                                        <tr>
                                            <th>Campo</th>
                                            <th>Tu respuesta</th>
                                            <th>Valor correcto</th>
                                            <th style="width:32px;"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($g['campos'] as $c): ?>
                                        <tr>
                                            <td class="field-label"><?= htmlspecialchars($c['campo']) ?></td>
                                            <td class="<?= $c['correcto'] ? 'cell-correct' : 'cell-error' ?>">
                                                <?= htmlspecialchars($c['borrador'] ?? '—') ?>
                                            </td>
                                            <td><?= htmlspecialchars($c['esperado'] ?? '—') ?></td>
                                            <td class="cell-match-icon <?= $c['correcto'] ? 'match-ok' : 'match-fail' ?>">
                                                <?php if ($c['correcto']): ?>
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
                                                <?php else: ?>
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>

                <?php if (!empty($autoItems)): ?>
                <div class="accordion" id="accordion-auto">
                    <div class="accordion-header" onclick="document.getElementById('accordion-auto').classList.toggle('accordion--open')">
                        <span class="accordion-title">
                            Autoliquidación
                            <?php $autoErrors = count(array_filter($autoItems, fn($ai) => !$ai['correcto'])); ?>
                            <?php if ($autoErrors === 0): ?>
                                <span class="accordion-badge badge-correct">Todo correcto ✓</span>
                            <?php else: ?>
                                <span class="accordion-badge badge-errors"><?= $autoErrors ?> error<?= $autoErrors > 1 ? 'es' : '' ?></span>
                            <?php endif; ?>
                        </span>
                        <svg class="accordion-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                            <polyline points="6 9 12 15 18 9"/>
                        </svg>
                    </div>
                    <div class="accordion-body">
                        <div class="accordion-content">
                            <table class="comparison-table">
                                <thead>
                                    <tr>
                                        <th>Concepto</th>
                                        <th>Tu respuesta</th>
                                        <th>Valor correcto</th>
                                        <th style="width:32px;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($autoItems as $ai): ?>
                                    <tr>
                                        <td class="field-label"><?= htmlspecialchars($ai['campo']) ?></td>
                                        <td class="<?= $ai['correcto'] ? 'cell-correct' : 'cell-error' ?>">
                                            <?= htmlspecialchars($ai['borrador'] ?? '—') ?>
                                        </td>
                                        <td><?= htmlspecialchars($ai['esperado'] ?? '—') ?></td>
                                        <td class="cell-match-icon <?= $ai['correcto'] ? 'match-ok' : 'match-fail' ?>">
                                            <?php if ($ai['correcto']): ?>
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
                                            <?php else: ?>
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($herederosCalc)): ?>
                <div class="accordion" id="accordion-hcalc">
                    <div class="accordion-header" onclick="document.getElementById('accordion-hcalc').classList.toggle('accordion--open')">
                        <span class="accordion-title">
                            Impuesto por Heredero
                            <?php
                                $hcErrors = 0;
                                foreach ($herederosCalc as $h) {
                                    foreach ($h['campos'] as $c) { if (!$c['correcto']) { $hcErrors++; } }
                                }
                            ?>
                            <?php if ($hcErrors === 0): ?>
                                <span class="accordion-badge badge-correct">Todo correcto ✓</span>
                            <?php else: ?>
                                <span class="accordion-badge badge-errors"><?= $hcErrors ?> error<?= $hcErrors > 1 ? 'es' : '' ?></span>
                            <?php endif; ?>
                        </span>
                        <svg class="accordion-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                            <polyline points="6 9 12 15 18 9"/>
                        </svg>
                    </div>
                    <div class="accordion-body">
                        <div class="accordion-content">
                            <?php foreach ($herederosCalc as $h): ?>
                                <div class="grupo-header"><?= htmlspecialchars($h['label'] ?? 'Heredero') ?></div>
                                <table class="comparison-table">
                                    <thead>
                                        <tr>
                                            <th>Campo</th>
                                            <th>Tu respuesta</th>
                                            <th>Valor correcto</th>
                                            <th style="width:32px;"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($h['campos'] as $c): ?>
                                        <tr>
                                            <td class="field-label"><?= htmlspecialchars($c['campo']) ?></td>
                                            <td class="<?= $c['correcto'] ? 'cell-correct' : 'cell-error' ?>">
                                                <?= htmlspecialchars($c['borrador'] ?? '—') ?>
                                            </td>
                                            <td><?= htmlspecialchars($c['esperado'] ?? '—') ?></td>
                                            <td class="cell-match-icon <?= $c['correcto'] ? 'match-ok' : 'match-fail' ?>">
                                                <?php if ($c['correcto']): ?>
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
                                                <?php else: ?>
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

            </div>
        </div>

        <?php else: ?>
        <!-- Sin datos de comparación -->
        <div class="sidebar-panel animate-in" style="margin-top:16px;">
            <div class="sidebar-panel-header"><h3>Comparación de Declaración</h3></div>
            <div class="sidebar-panel-body">
                <div style="text-align:center; padding:2rem 1rem; color:var(--gray-400);">
                    <svg viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" style="margin-bottom:12px; opacity:0.5;">
                        <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
                        <rect x="8" y="2" width="8" height="4" rx="1" ry="1"/><path d="M9 14l2 2 4-4"/>
                    </svg>
                    <p style="font-size:var(--text-sm); font-weight:500; margin-bottom:4px;">Sin datos de comparación</p>
                    <p style="font-size:var(--text-xs); max-width:360px; margin:0 auto; line-height:1.5;">
                        No se encontraron datos de comparación para este intento.
                    </p>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php endif; ?>

    </div>

    <!-- ═══ SIDEBAR (Solo lectura) ═══ -->
    <div class="correction-sidebar readonly">

        <?php if ($hayComparacion && !$esRechazadoSinRif): ?>
        <!-- Score Circle -->
        <div class="sidebar-panel">
            <div class="sidebar-panel-body" style="text-align:center;">
                <div class="score-circle <?= $score['porcentaje'] >= 70 ? 'score-pass' : 'score-fail' ?>">
                    <span class="score-circle-value"><?= $score['porcentaje'] ?>%</span>
                </div>
                <div style="margin-top:8px; font-size:var(--text-xs); color:var(--gray-500);">
                    <?= $score['correctas'] ?> de <?= $score['total_esperado'] ?> correctas
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Calificación obtenida (solo lectura) -->
        <div class="sidebar-panel">
            <div class="sidebar-panel-header">
                <h3>Calificación obtenida</h3>
            </div>
            <div class="sidebar-panel-body" style="text-align:center;">
                <div class="student-grade-display">
                    <span class="student-grade-value <?= $notaClass ?>">
                        <?= htmlspecialchars($notaDisplay) ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Observaciones del profesor (solo lectura) -->
        <div class="sidebar-panel">
            <div class="sidebar-panel-header"><h3>Observaciones del Profesor</h3></div>
            <div class="sidebar-panel-body">
                <?php if ($observacion !== ''): ?>
                    <textarea class="observations-textarea" readonly><?= htmlspecialchars($observacion) ?></textarea>
                <?php else: ?>
                    <p style="font-size:var(--text-sm); color:var(--gray-400); text-align:center; padding:8px 0;">
                        Sin observaciones.
                    </p>
                <?php endif; ?>
                <?php if ($profesorNombre): ?>
                    <div class="professor-info" style="margin-top:8px; font-size:var(--text-xs); color:var(--gray-500);">
                        Calificado por <strong><?= $profesorNombre ?></strong>
                        <?= $fechaRevision !== '—' ? ' el ' . $fechaRevision : '' ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Acciones -->
        <div class="sidebar-panel">
            <div class="sidebar-actions">
                <a href="<?= base_url('/mis-asignaciones/' . $asignacionId) ?>"
                   class="btn btn-secondary"
                   style="text-decoration:none; text-align:center;">
                    ← Volver a la Asignación
                </a>
                <a href="<?= base_url('/mis-asignaciones') ?>"
                   class="btn btn-outline"
                   style="text-decoration:none; text-align:center;">
                    Mis Asignaciones
                </a>
            </div>
        </div>

    </div>
</div>

<script>
(function() {
    window.toggleAccordion = function(index) {
        var acc = document.getElementById('accordion-' + index);
        if (acc) acc.classList.toggle('accordion--open');
    };

    <?php if ($esRechazadoSinRif): ?>
    // ── Tabs de revisión RIF ──
    var rifTabs   = document.querySelectorAll('.rs-tab');
    var rifPanels = document.querySelectorAll('.rs-panel-content');
    rifTabs.forEach(function(tab) {
        tab.addEventListener('click', function() {
            rifTabs.forEach(function(t) { t.classList.remove('rs-tab--active'); });
            rifPanels.forEach(function(p) { p.style.display = 'none'; });
            tab.classList.add('rs-tab--active');
            var target = document.getElementById(tab.getAttribute('data-target'));
            if (target) target.style.display = 'block';
        });
    });
    // Auto-expandir direcciones con errores
    document.querySelectorAll('.rs-dir-card--error').forEach(function(card) {
        card.classList.add('rs-dir-card--open');
    });
    <?php endif; ?>
})();
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/logged_layout.php';
?>
