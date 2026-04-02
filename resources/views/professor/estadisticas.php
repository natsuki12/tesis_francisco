<?php
declare(strict_types=1);

// ARCHIVO: resources/views/professor/estadisticas.php

$pageTitle  = 'Estadísticas — Simulador SENIAT';
$activePage = 'estadisticas';

$extraCss  = '<link rel="stylesheet" href="' . asset('css/shared/data-table.css') . '">';
$extraCss .= '<link rel="stylesheet" href="' . asset('css/professor/estadisticas.css') . '">';
$extraJs   = '<script src="' . asset('js/global/data_table_core.js') . '"></script>';

// Defaults anti-crash
$resumen      = $resumen      ?? ['estudiantes' => 0, 'secciones' => 0, 'casos' => 0, 'intentos' => 0, 'tasa_exito' => 0, 'asignaciones' => 0, 'promedio' => null];
$estados      = $estados      ?? [];
$rendimiento  = $rendimiento  ?? [];
$notas        = $notas        ?? ['numericas' => ['0 – 5' => 0, '6 – 10' => 0, '11 – 15' => 0, '16 – 20' => 0, 'total' => 0], 'cualitativas' => []];
$sinActividad = $sinActividad ?? [];

$tieneNumericas   = ($notas['numericas']['total'] ?? 0) > 0;
$tieneCualitativas = !empty($notas['cualitativas']);

ob_start();
?>

<!-- ═══════════════════════════════════════════════ -->
<!-- HEADER                                         -->
<!-- ═══════════════════════════════════════════════ -->
<div class="est-header">
    <div>
        <h1>Estadísticas</h1>
        <p>Resumen del rendimiento académico de sus estudiantes en el simulador.</p>
    </div>
    <button class="est-btn-export" title="Próximamente" disabled style="display:none;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
            <polyline points="7 10 12 15 17 10"/>
            <line x1="12" y1="15" x2="12" y2="3"/>
        </svg>
        Exportar PDF
    </button>
</div>

<!-- ═══════════════════════════════════════════════ -->
<!-- STAT CARDS                                     -->
<!-- ═══════════════════════════════════════════════ -->
<div class="est-stats">
    <div class="est-stat">
        <div class="est-stat__icon blue">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24">
                <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/>
            </svg>
        </div>
        <div class="est-stat__value"><?= $resumen['estudiantes'] ?></div>
        <div class="est-stat__label">Estudiantes inscritos</div>
    </div>
    <div class="est-stat">
        <div class="est-stat__icon green">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24">
                <path d="M2 3h6a4 4 0 014 4v14a3 3 0 00-3-3H2z"/><path d="M22 3h-6a4 4 0 00-4 4v14a3 3 0 013-3h7z"/>
            </svg>
        </div>
        <div class="est-stat__value"><?= $resumen['secciones'] ?></div>
        <div class="est-stat__label">Secciones</div>
    </div>
    <div class="est-stat">
        <div class="est-stat__icon blue">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24">
                <path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/>
            </svg>
        </div>
        <div class="est-stat__value"><?= $resumen['casos'] ?></div>
        <div class="est-stat__label">Casos publicados</div>
    </div>
    <div class="est-stat">
        <div class="est-stat__icon purple">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24">
                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
            </svg>
        </div>
        <div class="est-stat__value"><?= number_format($resumen['intentos']) ?></div>
        <div class="est-stat__label">Intentos totales</div>
    </div>
    <div class="est-stat">
        <div class="est-stat__icon amber">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24">
                <rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/>
            </svg>
        </div>
        <div class="est-stat__value"><?= number_format($resumen['asignaciones']) ?></div>
        <div class="est-stat__label">Asignaciones activas</div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════ -->
<!-- GRÁFICOS: Dona (estados) + Barras (por caso)  -->
<!-- ═══════════════════════════════════════════════ -->
<div class="est-charts-row">
    <!-- Dona: Distribución de estados -->
    <div class="est-panel">
        <div class="est-panel__header">
            <h3>Estado de los Intentos</h3>
            <span class="est-subtitle"><?= array_sum($estados) ?> total</span>
        </div>
        <div class="est-panel__body">
            <?php if (empty($estados)): ?>
                <div class="est-empty">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M8 15h8"/><circle cx="9" cy="9" r="1"/><circle cx="15" cy="9" r="1"/></svg>
                    No hay intentos registrados aún.
                </div>
            <?php else: ?>
                <div class="est-chart-wrap" style="height:220px;">
                    <canvas id="chart-estados"></canvas>
                </div>
                <div class="est-legend" id="legend-estados"></div>
                <div style="text-align:center; margin-top:10px; padding-top:10px; border-top:1px solid var(--gray-100);">
                    <span style="font-size:var(--text-xs); color:var(--gray-400);">Tasa de éxito:</span>
                    <strong class="est-tasa <?= $resumen['tasa_exito'] >= 70 ? 'high' : ($resumen['tasa_exito'] >= 40 ? 'mid' : 'low') ?>" style="font-size:var(--text-lg);"><?= $resumen['tasa_exito'] ?>%</strong>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Barras: Intentos por caso -->
    <div class="est-panel">
        <div class="est-panel__header">
            <h3>Intentos por Caso</h3>
            <span class="est-subtitle">Aprobados vs No Aprobados</span>
        </div>
        <div class="est-panel__body">
            <?php if (empty($rendimiento)): ?>
                <div class="est-empty">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                    Aún no hay casos con intentos.
                </div>
            <?php else: ?>
                <div class="est-chart-wrap" style="height:<?= max(180, count($rendimiento) * 42) ?>px;">
                    <canvas id="chart-casos"></canvas>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════ -->
<!-- TABLA: Rendimiento por Caso                    -->
<!-- ═══════════════════════════════════════════════ -->
<div class="est-panel">
    <div class="est-panel__header">
        <h3>Rendimiento por Caso</h3>
        <span class="est-subtitle"><?= count($rendimiento) ?> configuraciones</span>
    </div>
    <div class="est-panel__body" style="padding:0; overflow-x:auto;">
        <?php if (empty($rendimiento)): ?>
            <div class="est-empty">No hay casos configurados.</div>
        <?php else: ?>
            <table class="est-table">
                <thead>
                    <tr>
                        <th>Caso</th>
                        <th>Modalidad</th>
                        <th>Tipo Calif.</th>
                        <th style="text-align:center;">Asignados</th>
                        <th style="text-align:center;">Intentos</th>
                        <th style="text-align:center;">Aprobados</th>
                        <th style="text-align:center;">No Aprob.</th>
                        <th style="text-align:center;">% Éxito</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rendimiento as $r):
                        $aprobados = (int) ($r['aprobados'] ?? 0);
                        $rechazados = (int) ($r['rechazados'] ?? 0);
                        $totalEval = $aprobados + $rechazados;
                        $tasa = $totalEval > 0 ? round(($aprobados / $totalEval) * 100) : 0;
                        $tasaClass = $tasa >= 70 ? 'high' : ($tasa >= 40 ? 'mid' : 'low');

                        // Badge modalidad
                        $mod = $r['modalidad'] ?? 'Libre';
                        $modLower = strtolower(str_replace(['Practica_', 'practica_'], '', $mod));
                        if (strpos(strtolower($mod), 'libre') !== false) $modClass = 'libre';
                        elseif (strpos(strtolower($mod), 'guiada') !== false) $modClass = 'guiada';
                        else $modClass = 'evaluacion';

                        $modLabel = match (true) {
                            str_contains(strtolower($mod), 'libre')  => 'Libre',
                            str_contains(strtolower($mod), 'guiada') => 'Guiada',
                            default                                   => 'Evaluación',
                        };

                        // Badge tipo calificación
                        $tipoCalif = $r['tipo_calificacion'] ?? 'aprobado_reprobado';
                        $tipoCalifLabel = $tipoCalif === 'numerica' ? 'Numérica' : 'Apr./Rep.';
                        $tipoCalifClass = $tipoCalif === 'numerica' ? 'numerica' : 'cualitativa';

                        $titulo = mb_strlen($r['titulo'] ?? '') > 35
                            ? mb_substr($r['titulo'], 0, 35) . '…'
                            : ($r['titulo'] ?? 'Sin título');
                    ?>
                        <tr>
                            <td style="font-weight:600; color:var(--gray-800); max-width:220px;" title="<?= htmlspecialchars($r['titulo'] ?? '') ?>"><?= htmlspecialchars($titulo) ?></td>
                            <td><span class="est-badge est-badge--<?= $modClass ?>"><?= $modLabel ?></span></td>
                            <td><span class="est-badge est-badge--<?= $tipoCalifClass ?>"><?= $tipoCalifLabel ?></span></td>
                            <td style="text-align:center;"><?= $r['asignados'] ?? 0 ?></td>
                            <td style="text-align:center; font-weight:600;"><?= $r['intentos'] ?? 0 ?></td>
                            <td style="text-align:center; color:var(--green-600);"><?= $aprobados ?></td>
                            <td style="text-align:center; color:var(--red-600);"><?= $rechazados ?></td> <!-- No Aprobados -->
                            <td style="text-align:center;">
                                <?php if ($totalEval > 0): ?>
                                    <span class="est-tasa <?= $tasaClass ?>"><?= $tasa ?>%</span>
                                <?php else: ?>
                                    <span style="color:var(--gray-300);">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<!-- ═══════════════════════════════════════════════ -->
<!-- DISTRIBUCIÓN DE CALIFICACIONES                 -->
<!-- ═══════════════════════════════════════════════ -->
<?php if ($tieneNumericas || $tieneCualitativas): ?>
<div class="est-notas-grid <?= ($tieneNumericas && $tieneCualitativas) ? '' : 'single' ?>">
    <?php if ($tieneNumericas):
        $totalNum = $notas['numericas']['total'];
    ?>
    <div class="est-panel">
        <div class="est-panel__header">
            <h3>Notas Numéricas</h3>
            <span class="est-subtitle"><?= $totalNum ?> calificaciones</span>
        </div>
        <div class="est-panel__body">
            <?php
            $rangos = [
                '16 – 20' => ['val' => $notas['numericas']['16 – 20'], 'color' => 'var(--green-500)'],
                '11 – 15' => ['val' => $notas['numericas']['11 – 15'], 'color' => 'var(--blue-400)'],
                '6 – 10'  => ['val' => $notas['numericas']['6 – 10'],  'color' => 'var(--amber-500)'],
                '0 – 5'   => ['val' => $notas['numericas']['0 – 5'],   'color' => 'var(--red-500)'],
            ];
            foreach ($rangos as $label => $info):
                $pct = $totalNum > 0 ? round(($info['val'] / $totalNum) * 100) : 0;
            ?>
                <div class="est-bar-row">
                    <span class="est-bar-label"><?= $label ?></span>
                    <div class="est-bar-track">
                        <div class="est-bar-fill" style="width:<?= $pct ?>%; background:<?= $info['color'] ?>;">
                            <?= $pct >= 10 ? $pct . '%' : '' ?>
                        </div>
                    </div>
                    <span class="est-bar-count"><?= $info['val'] ?> est.</span>
                </div>
            <?php endforeach; ?>
            <?php if ($resumen['promedio'] !== null): ?>
                <div style="text-align:center; margin-top:14px; padding-top:12px; border-top:1px solid var(--gray-100);">
                    <span style="font-size:var(--text-xs); color:var(--gray-400); text-transform:uppercase; letter-spacing:0.5px;">Promedio General</span>
                    <div style="font-size:24px; font-weight:var(--weight-bold); color:var(--gray-800); margin-top:2px;">
                        <?= $resumen['promedio'] ?> <span style="font-size:var(--text-sm); color:var(--gray-400); font-weight:var(--weight-medium);">/ 20</span>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($tieneCualitativas):
        $totalCual = array_sum($notas['cualitativas']);
        $aprobadosCual = $notas['cualitativas']['Aprobado'] ?? 0;
        $reprobadosCual = $notas['cualitativas']['Reprobado'] ?? 0;
        $pctApr = $totalCual > 0 ? round(($aprobadosCual / $totalCual) * 100) : 0;
        $pctRep = $totalCual > 0 ? round(($reprobadosCual / $totalCual) * 100) : 0;
    ?>
    <div class="est-panel">
        <div class="est-panel__header">
            <h3>Notas Cualitativas</h3>
            <span class="est-subtitle"><?= $totalCual ?> calificaciones</span>
        </div>
        <div class="est-panel__body">
            <div class="est-bar-row">
                <span class="est-bar-label">Aprob.</span>
                <div class="est-bar-track">
                    <div class="est-bar-fill" style="width:<?= $pctApr ?>%; background:var(--green-500);">
                        <?= $pctApr >= 10 ? $pctApr . '%' : '' ?>
                    </div>
                </div>
                <span class="est-bar-count"><?= $aprobadosCual ?> est.</span>
            </div>
            <div class="est-bar-row">
                <span class="est-bar-label">No Apr.</span>
                <div class="est-bar-track">
                    <div class="est-bar-fill" style="width:<?= $pctRep ?>%; background:var(--red-500);">
                        <?= $pctRep >= 10 ? $pctRep . '%' : '' ?>
                    </div>
                </div>
                <span class="est-bar-count"><?= $reprobadosCual ?> est.</span>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- ═══════════════════════════════════════════════ -->
<!-- ESTUDIANTES SIN ACTIVIDAD (DataTable)          -->
<!-- ═══════════════════════════════════════════════ -->
<?php
// Extraer secciones y casos únicos para los filtros
$seccionesInactivos = array_unique(array_filter(array_column($sinActividad, 'seccion')));
sort($seccionesInactivos);
$casosInactivos = array_unique(array_filter(array_column($sinActividad, 'caso')));
sort($casosInactivos);
$asignacionesInactivos = array_unique(array_filter(array_column($sinActividad, 'asignacion')));
sort($asignacionesInactivos);
?>
<div class="est-panel">
    <div class="est-panel__header">
        <h3>Asignaciones sin Intentos</h3>
        <span class="est-subtitle"><?= count($sinActividad) ?> asignaciones pendientes de iniciar</span>
    </div>
    <div class="est-panel__body">
        <?php if (empty($sinActividad)): ?>
            <div class="est-empty" style="color:var(--green-600);">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                ¡Todos los estudiantes asignados han iniciado al menos un intento!
            </div>
        <?php else: ?>
            <!-- Toolbar -->
            <div class="toolbar" style="margin-bottom:12px;">
                <div class="toolbar-left">
                    <div class="search-box">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                            <circle cx="11" cy="11" r="8" />
                            <path d="m21 21-4.35-4.35" />
                        </svg>
                        <input type="text" data-search-for="inactivos-table" placeholder="Buscar por nombre, correo o sección...">
                    </div>
                    <select id="est-filter-seccion" class="per-page-select" style="min-width:170px;">
                        <option value="">Todas las secciones</option>
                        <?php foreach ($seccionesInactivos as $sec): ?>
                            <option value="<?= htmlspecialchars($sec) ?>"><?= htmlspecialchars($sec) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select id="est-filter-caso" class="per-page-select" style="min-width:170px;">
                        <option value="">Todos los casos</option>
                        <?php foreach ($casosInactivos as $caso): ?>
                            <option value="<?= htmlspecialchars($caso) ?>"><?= htmlspecialchars(mb_strlen($caso) > 30 ? mb_substr($caso, 0, 30) . '…' : $caso) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select id="est-filter-asignacion" class="per-page-select" style="min-width:170px;">
                        <option value="">Todas las asignaciones</option>
                        <?php foreach ($asignacionesInactivos as $asig): ?>
                            <option value="<?= htmlspecialchars($asig) ?>"><?= htmlspecialchars(mb_strlen($asig) > 30 ? mb_substr($asig, 0, 30) . '…' : $asig) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="toolbar-right">
                    <select data-perpage-for="inactivos-table" class="per-page-select">
                        <option value="5">5</option>
                        <option value="10" selected>10</option>
                        <option value="25">25</option>
                    </select>
                </div>
            </div>

            <!-- Table -->
            <div class="table-container" style="min-height:340px;">
                <table class="data-table" id="inactivos-table">
                    <thead>
                        <tr>
                            <th class="sortable" data-col="0">Estudiante</th>
                            <th class="sortable" data-col="1">Correo</th>
                            <th class="sortable" data-col="2">Sección</th>
                            <th class="sortable" data-col="3">Asignación</th>
                            <th class="sortable" data-col="4">Caso</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sinActividad as $st):
                            $fullName = trim(($st['nombres'] ?? '') . ' ' . ($st['apellidos'] ?? ''));
                            preg_match_all('/\b\w/u', $fullName, $m);
                            $initials = mb_strtoupper(implode('', array_slice($m[0], 0, 2)));
                            $ced = $st['cedula']
                                ? ($st['nacionalidad'] ?? 'V') . '-' . number_format((float) $st['cedula'], 0, ',', '.')
                                : 'S/C';
                            $email      = $st['email'] ?? '';
                            $seccion    = $st['seccion'] ?? '—';
                            $caso       = $st['caso'] ?? '';
                            $asignacion = $st['asignacion'] ?? '';
                            $searchText = mb_strtolower($fullName . ' ' . $ced . ' ' . $email . ' ' . $seccion . ' ' . $asignacion . ' ' . $caso);
                        ?>
                            <tr data-search="<?= htmlspecialchars($searchText) ?>" data-seccion="<?= htmlspecialchars($st['seccion'] ?? '') ?>" data-caso="<?= htmlspecialchars($st['caso'] ?? '') ?>" data-asignacion="<?= htmlspecialchars($st['asignacion'] ?? '') ?>">
                                <td>
                                    <div style="display:flex; align-items:center; gap:10px;">
                                        <div class="est-inactive-avatar"><?= htmlspecialchars($initials) ?></div>
                                        <div>
                                            <div style="font-weight:600; color:var(--gray-700); font-size:var(--text-sm);"><?= htmlspecialchars($fullName) ?></div>
                                            <div style="font-size:var(--text-xs); color:var(--gray-400);"><?= htmlspecialchars($ced) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td style="font-size:var(--text-sm); color:var(--gray-500);"><?= htmlspecialchars($email) ?></td>
                                <td><span class="est-badge est-badge--libre"><?= htmlspecialchars($seccion) ?></span></td>
                                <td style="font-size:var(--text-sm); font-weight:500; color:var(--gray-700);" title="<?= htmlspecialchars($asignacion) ?>">
                                    <?= htmlspecialchars(mb_strlen($asignacion) > 30 ? mb_substr($asignacion, 0, 30) . '…' : $asignacion) ?>
                                </td>
                                <td style="font-size:var(--text-sm); color:var(--gray-600);" title="<?= htmlspecialchars($caso) ?>">
                                    <?= htmlspecialchars(mb_strlen($caso) > 30 ? mb_substr($caso, 0, 30) . '…' : $caso) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="table-footer" data-footer-for="inactivos-table">
                    <span class="table-footer-info"></span>
                    <div class="pagination"></div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ═══════════════════════════════════════════════ -->
<!-- CHART.JS                                       -->
<!-- ═══════════════════════════════════════════════ -->
<script src="<?= asset('js/lib/chart.umd.min.js') ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {

    // ── Paleta de colores por estado ──
    const estadoColors = {
        'En_Progreso':           '#3b82c4', // blue-400
        'Enviado':               '#64748b', // gray-500
        'Pendiente_RIF':         '#8b5cf6', // purple-500
        'Pendiente_Calificacion':'#f59e0b', // amber-500
        'Aprobado':              '#22c55e', // green-500
        'Rechazado':             '#ef4444', // red-500
    };

    const estadoLabels = {
        'En_Progreso':           'En Progreso',
        'Enviado':               'Enviado',
        'Pendiente_RIF':         'Pendiente RIF',
        'Pendiente_Calificacion':'Por Calificar',
        'Aprobado':              'Aprobado',
        'Rechazado':             'No Aprobado',
    };

    // ── Dona: Estados ──
    <?php if (!empty($estados)): ?>
    (function() {
        const data = <?= json_encode($estados) ?>;
        const labels = Object.keys(data).map(k => estadoLabels[k] || k);
        const values = Object.values(data);
        const colors = Object.keys(data).map(k => estadoColors[k] || '#94a3b8');

        new Chart(document.getElementById('chart-estados'), {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: colors,
                    borderWidth: 2,
                    borderColor: '#fff',
                    hoverOffset: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '62%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                const pct = total > 0 ? Math.round((ctx.parsed / total) * 100) : 0;
                                return ctx.label + ': ' + ctx.parsed + ' (' + pct + '%)';
                            }
                        }
                    }
                }
            }
        });

        // Leyenda custom
        const legendEl = document.getElementById('legend-estados');
        if (legendEl) {
            Object.keys(data).forEach(k => {
                const div = document.createElement('span');
                div.className = 'est-legend__item';
                div.innerHTML = '<span class="est-legend__dot" style="background:' + (estadoColors[k] || '#94a3b8') + ';"></span>' +
                    (estadoLabels[k] || k) + ' <strong>' + data[k] + '</strong>';
                legendEl.appendChild(div);
            });
        }
    })();
    <?php endif; ?>

    // ── Barras: Intentos por caso ──
    <?php if (!empty($rendimiento)): ?>
    (function() {
        const casos = <?= json_encode(array_map(function($r) {
            return mb_strlen($r['titulo'] ?? '') > 25 ? mb_substr($r['titulo'], 0, 25) . '…' : ($r['titulo'] ?? '');
        }, $rendimiento)) ?>;

        const aprobados  = <?= json_encode(array_map(fn($r) => (int)($r['aprobados'] ?? 0), $rendimiento)) ?>;
        const rechazados = <?= json_encode(array_map(fn($r) => (int)($r['rechazados'] ?? 0), $rendimiento)) ?>;
        const enProgreso = <?= json_encode(array_map(fn($r) => (int)($r['en_progreso'] ?? 0), $rendimiento)) ?>;

        new Chart(document.getElementById('chart-casos'), {
            type: 'bar',
            data: {
                labels: casos,
                datasets: [
                    {
                        label: 'Aprobados',
                        data: aprobados,
                        backgroundColor: '#22c55e',
                        borderRadius: 4,
                    },
                    {
                        label: 'No Aprobados',
                        data: rechazados,
                        backgroundColor: '#ef4444',
                        borderRadius: 4,
                    },
                    {
                        label: 'En Progreso',
                        data: enProgreso,
                        backgroundColor: '#e2e8f0',
                        borderRadius: 4,
                    }
                ]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 12, padding: 16, font: { size: 11 } }
                    }
                },
                scales: {
                    x: {
                        stacked: true,
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,.04)' },
                        ticks: { font: { size: 11 }, stepSize: 1 }
                    },
                    y: {
                        stacked: true,
                        grid: { display: false },
                        ticks: { font: { size: 11, weight: '600' } }
                    }
                }
            }
        });
    })();
    <?php endif; ?>

    // ── Filtros de sección, caso y asignación para tabla inactivos ──
    const filterSecEst = document.getElementById('est-filter-seccion');
    const filterCasoEst = document.getElementById('est-filter-caso');
    const filterAsigEst = document.getElementById('est-filter-asignacion');

    function applyInactivosFilters() {
        if (typeof DataTableManager === 'undefined') return;
        const secVal  = filterSecEst  ? filterSecEst.value  : '';
        const casoVal = filterCasoEst ? filterCasoEst.value : '';
        const asigVal = filterAsigEst ? filterAsigEst.value : '';

        DataTableManager.setClientFilter('inactivos-table', (secVal || casoVal || asigVal)
            ? row => {
                const matchSec  = !secVal  || (row.dataset.seccion || '') === secVal;
                const matchCaso = !casoVal || (row.dataset.caso || '') === casoVal;
                const matchAsig = !asigVal || (row.dataset.asignacion || '') === asigVal;
                return matchSec && matchCaso && matchAsig;
            }
            : null
        );
    }

    if (filterSecEst) filterSecEst.addEventListener('change', applyInactivosFilters);
    if (filterCasoEst) filterCasoEst.addEventListener('change', applyInactivosFilters);
    if (filterAsigEst) filterAsigEst.addEventListener('change', applyInactivosFilters);

});
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/logged_layout.php';
?>
