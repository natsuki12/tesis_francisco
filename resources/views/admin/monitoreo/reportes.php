<?php
declare(strict_types=1);

// ARCHIVO: resources/views/admin/monitoreo/reportes.php

$pageTitle  = 'Centro de Reportes';
$activePage = 'reportes';
$breadcrumbs = [
    'Inicio'       => base_url('/admin'),
    'Monitoreo'    => '#',
    'Reportes'     => '#'
];

$extraCss  = '<link rel="stylesheet" href="' . asset('css/shared/data-table.css') . '">';
$extraCss .= '<link rel="stylesheet" href="' . asset('css/professor/estadisticas.css') . '">';
$extraCss .= '<link rel="stylesheet" href="' . asset('css/admin/reportes.css') . '">';
$extraJs   = '<script src="' . asset('js/global/data_table_core.js') . '"></script>';

// ── Defaults anti-crash ──
$kpi          = $kpi          ?? ['estudiantes' => 0, 'profesores' => 0, 'secciones' => 0, 'casos' => 0, 'intentos' => 0, 'asignaciones' => 0, 'asignaciones_con_intento' => 0];
$estados      = $estados      ?? [];
$tasaExito    = $tasaExito    ?? 0;
$rendimiento  = $rendimiento  ?? [];
$notas        = $notas        ?? ['numericas' => ['0 – 5' => 0, '6 – 10' => 0, '11 – 15' => 0, '16 – 20' => 0, 'total' => 0], 'cualitativas' => [], 'promedio' => null];
$topPromedio  = $topPromedio  ?? [];
$topActivos   = $topActivos   ?? [];
$promSeccion  = $promSeccion  ?? [];

$tieneNumericas    = ($notas['numericas']['total'] ?? 0) > 0;
$tieneCualitativas = !empty($notas['cualitativas']);
$tasaActividad     = $kpi['asignaciones'] > 0 ? (int) round(($kpi['asignaciones_con_intento'] / $kpi['asignaciones']) * 100) : 0;
$totalEstados      = array_sum($estados) ?: 1;

ob_start();
?>

<!-- ═══════════════ HEADER ═══════════════ -->
<div class="est-header">
    <div>
        <h1>Centro de Reportes</h1>
        <p>Métricas globales de uso y rendimiento académico del sistema.</p>
    </div>
    <button class="est-btn-export" style="display:none;" id="btn-export-pdf">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>
        </svg>
        Exportar PDF
    </button>
</div>

<!-- ═══════════════ 6 STAT CARDS (3×2) ═══════════════ -->
<div class="est-stats rpt-stats-3col">
    <!-- Estudiantes -->
    <div class="est-stat">
        <div class="est-stat__icon blue">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
        </div>
        <div class="est-stat__value"><?= number_format($kpi['estudiantes']) ?></div>
        <div class="est-stat__label">Estudiantes</div>
    </div>
    <!-- Profesores -->
    <div class="est-stat">
        <div class="est-stat__icon green">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
            </svg>
        </div>
        <div class="est-stat__value"><?= $kpi['profesores'] ?></div>
        <div class="est-stat__label">Profesores</div>
    </div>
    <!-- Secciones -->
    <div class="est-stat">
        <div class="est-stat__icon amber">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                <rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/>
            </svg>
        </div>
        <div class="est-stat__value"><?= $kpi['secciones'] ?></div>
        <div class="est-stat__label">Secciones</div>
    </div>
    <!-- Casos Publicados -->
    <div class="est-stat">
        <div class="est-stat__icon purple">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>
                <line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>
            </svg>
        </div>
        <div class="est-stat__value"><?= $kpi['casos'] ?></div>
        <div class="est-stat__label">Casos publicados</div>
    </div>
    <!-- Asignaciones -->
    <div class="est-stat">
        <div class="est-stat__icon" style="background:var(--blue-500);">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
                <rect x="8" y="2" width="8" height="4" rx="1"/>
            </svg>
        </div>
        <div class="est-stat__value"><?= number_format($kpi['asignaciones']) ?></div>
        <div class="est-stat__label">Asignaciones</div>
    </div>
    <!-- Intentos -->
    <div class="est-stat">
        <div class="est-stat__icon red">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
            </svg>
        </div>
        <div class="est-stat__value"><?= number_format($kpi['intentos']) ?></div>
        <div class="est-stat__label">Intentos totales</div>
    </div>
</div>

<!-- ═══════════════ GRÁFICOS (dona + barras) ═══════════════ -->
<div class="est-charts-row">
    <!-- DONA: Estado de Intentos -->
    <div class="est-panel">
        <div class="est-panel__header">
            <h3>Estado de Intentos</h3>
            <span class="est-subtitle"><?= number_format($kpi['intentos']) ?> totales</span>
        </div>
        <div class="est-panel__body">
            <?php if (!empty($estados)): ?>
                <div class="est-chart-wrap" style="height:220px;"><canvas id="chart-estados"></canvas></div>
                <!-- Leyenda -->
                <div class="est-legend" id="legend-estados"></div>
                <!-- Tasa de éxito -->
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                    <div class="rpt-tasa-box">
                        <div class="rpt-tasa-box__value est-tasa <?= $tasaExito >= 70 ? 'high' : ($tasaExito >= 40 ? 'mid' : 'low') ?>"><?= $tasaExito ?>%</div>
                        <div class="rpt-tasa-box__label">Tasa de éxito</div>
                    </div>
                    <div class="rpt-tasa-box">
                        <div class="rpt-tasa-box__value est-tasa <?= $tasaActividad >= 70 ? 'high' : ($tasaActividad >= 40 ? 'mid' : 'low') ?>"><?= $tasaActividad ?>%</div>
                        <div class="rpt-tasa-box__label">Tasa de actividad</div>
                    </div>
                </div>
            <?php else: ?>
                <div class="est-empty">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M8 15h8"/><circle cx="9" cy="9" r="1"/><circle cx="15" cy="9" r="1"/></svg>
                    No hay intentos registrados aún.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- BARRAS: Promedio por Sección -->
    <div class="est-panel">
        <div class="est-panel__header">
            <h3>Promedio por Sección</h3>
            <span class="est-subtitle">Calificación promedio</span>
        </div>
        <div class="est-panel__body">
            <?php if (!empty($promSeccion)): ?>
                <div class="est-chart-wrap" style="height:<?= max(180, count($promSeccion) * 45) ?>px;">
                    <canvas id="chart-secciones"></canvas>
                </div>
            <?php else: ?>
                <div class="est-empty">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/></svg>
                    No hay datos de calificaciones aún.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ═══════════════ TABLA: Rendimiento por Sección ═══════════════ -->
<div class="est-panel">
    <div class="est-panel__header">
        <h3>Rendimiento por Sección</h3>
        <span class="est-subtitle"><?= count($rendimiento) ?> secciones</span>
    </div>
    <div class="est-panel__body">
        <?php if (!empty($rendimiento)): ?>
            <!-- Toolbar -->
            <?php
                $profesoresUnicos = array_unique(array_column($rendimiento, 'profesor'));
                sort($profesoresUnicos);
            ?>
            <div class="toolbar">
                <div class="toolbar-left">
                    <div class="search-box">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                            <circle cx="11" cy="11" r="8" /><path d="m21 21-4.35-4.35" />
                        </svg>
                        <input type="text" data-search-for="tbl-rendimiento" placeholder="Buscar sección o profesor…">
                    </div>
                    <select class="filter-select" id="filter-profesor" onchange="filtrarPorProfesor()">
                        <option value="">Todos los profesores</option>
                        <?php foreach ($profesoresUnicos as $prof): ?>
                            <option value="<?= e(mb_strtolower($prof)) ?>"><?= e($prof) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="toolbar-right">
                    <label style="font-size:var(--text-xs); color:var(--gray-500); display:flex; align-items:center; gap:6px;">
                        Mostrar <select data-perpage-for="tbl-rendimiento" class="per-page-select"><option value="10" selected>10</option><option value="15">15</option><option value="25">25</option></select> filas
                    </label>
                </div>
            </div>
            <!-- Table -->
            <div class="table-container">
                <table class="data-table" id="tbl-rendimiento">
                    <thead>
                        <tr>
                            <th class="sortable" data-col="0">Sección</th>
                            <th class="sortable" data-col="1">Profesor</th>
                            <th class="sortable" data-col="2" style="text-align:center;">Estudiantes</th>
                            <th class="sortable" data-col="3" style="text-align:center;">Asignaciones</th>
                            <th class="sortable" data-col="4" style="text-align:center;">Intentos</th>
                            <th class="sortable" data-col="5" style="text-align:center;">Promedio</th>
                            <th class="sortable" data-col="6" style="text-align:center;">Aprobación</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rendimiento as $r):
                            $searchText = mb_strtolower(($r['seccion'] ?? '') . ' ' . ($r['profesor'] ?? ''));
                        ?>
                            <tr data-search="<?= e($searchText) ?>">
                                <td style="font-weight:var(--weight-semibold); color:var(--gray-800);"><?= e($r['seccion']) ?></td>
                                <td><?= e($r['profesor']) ?></td>
                                <td style="text-align:center;"><?= (int)$r['estudiantes'] ?></td>
                                <td style="text-align:center;"><?= (int)$r['asignaciones'] ?></td>
                                <td style="text-align:center;"><?= (int)$r['intentos'] ?></td>
                                <td style="text-align:center;">
                                    <?php if ($r['promedio'] !== null): ?>
                                        <span class="est-tasa <?= $r['promedio'] >= 15 ? 'high' : ($r['promedio'] >= 10 ? 'mid' : 'low') ?>">
                                            <?= $r['promedio'] ?>
                                        </span>
                                    <?php else: ?>
                                        <span style="color:var(--gray-400);">—</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align:center;">
                                    <span class="rpt-mini-bar">
                                        <span class="rpt-mini-bar__fill" style="width:<?= $r['tasa'] ?>%; background:<?= $r['tasa'] >= 70 ? 'var(--green-500)' : ($r['tasa'] >= 40 ? 'var(--amber-500)' : 'var(--red-500)') ?>;"></span>
                                    </span>
                                    <span class="est-tasa <?= $r['tasa'] >= 70 ? 'high' : ($r['tasa'] >= 40 ? 'mid' : 'low') ?>"><?= $r['tasa'] ?>%</span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="table-footer" data-footer-for="tbl-rendimiento">
                    <div class="table-footer-info"></div>
                    <div class="pagination"></div>
                </div>
            </div>
        <?php else: ?>
            <div class="est-empty">No hay secciones registradas.</div>
        <?php endif; ?>
    </div>
</div>

<!-- ═══════════════ NOTAS + TOP 5 ═══════════════ -->
<div class="est-notas-grid">
    <!-- Distribución de Notas -->
    <div class="est-panel" style="margin-bottom:0;">
        <div class="est-panel__header">
            <h3>Distribución de Calificaciones</h3>
        </div>
        <div class="est-panel__body">
            <?php if ($tieneNumericas): ?>
                <div style="margin-bottom:8px; font-size:var(--text-xs); font-weight:var(--weight-semibold); color:var(--gray-500); text-transform:uppercase; letter-spacing:0.5px;">Notas Numéricas</div>
                <?php
                    $totalNum = max((int)($notas['numericas']['total'] ?? 0), 1);
                    $rangos = [
                        ['rango' => '16 – 20', 'key' => '16 – 20', 'color' => 'var(--green-500)'],
                        ['rango' => '11 – 15', 'key' => '11 – 15', 'color' => 'var(--blue-500)'],
                        ['rango' => '6 – 10',  'key' => '6 – 10',  'color' => 'var(--amber-500)'],
                        ['rango' => '0 – 5',   'key' => '0 – 5',   'color' => 'var(--red-500)'],
                    ];
                    foreach ($rangos as $d):
                        $cant = (int)($notas['numericas'][$d['key']] ?? 0);
                        $pct  = round(($cant / $totalNum) * 100);
                ?>
                    <div class="est-bar-row">
                        <span class="est-bar-label"><?= $d['rango'] ?></span>
                        <div class="est-bar-track">
                            <div class="est-bar-fill" style="width:<?= $pct ?>%; background:<?= $d['color'] ?>;"><?= $pct > 8 ? $pct . '%' : '' ?></div>
                        </div>
                        <span class="est-bar-count"><?= $cant ?> est.</span>
                    </div>
                <?php endforeach; ?>

                <?php if ($notas['promedio'] !== null): ?>
                    <div class="rpt-promedio-general">
                        <div class="rpt-promedio-general__value" style="display: flex; align-items: baseline; justify-content: center; gap: 4px;">
                            <?= $notas['promedio'] ?> 
                            <span style="font-size: 16px; font-weight: 500; color: var(--gray-400);">/20</span>
                        </div>
                        <div class="rpt-promedio-general__label">Promedio general</div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($tieneCualitativas): ?>
                <div style="margin-top:16px; margin-bottom:8px; font-size:var(--text-xs); font-weight:var(--weight-semibold); color:var(--gray-500); text-transform:uppercase; letter-spacing:0.5px;">Notas Cualitativas</div>
                <?php
                    $totalCual = max(array_sum($notas['cualitativas']), 1);
                    $cualColors = ['Aprobado' => 'var(--green-500)', 'Reprobado' => 'var(--red-500)'];
                    foreach ($notas['cualitativas'] as $label => $cant):
                        $pct = round(($cant / $totalCual) * 100);
                ?>
                    <div class="est-bar-row">
                        <span class="est-bar-label"><?= e($label) ?></span>
                        <div class="est-bar-track">
                            <div class="est-bar-fill" style="width:<?= $pct ?>%; background:<?= $cualColors[$label] ?? 'var(--gray-500)' ?>;"><?= $pct > 8 ? $pct . '%' : '' ?></div>
                        </div>
                        <span class="est-bar-count"><?= $cant ?></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <?php if (!$tieneNumericas && !$tieneCualitativas): ?>
                <div class="est-empty">No hay calificaciones registradas.</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Top 5 Estudiantes -->
    <div class="est-panel" style="margin-bottom:0;">
        <div class="est-panel__header">
            <h3>Top 5 Estudiantes</h3>
            <div class="rpt-tabs">
                <button class="rpt-tab active" data-tab="promedio" onclick="toggleTop5('promedio', this)">Mejor Promedio</button>
                <button class="rpt-tab" data-tab="activos" onclick="toggleTop5('activos', this)">Más Activos</button>
            </div>
        </div>
        <div class="est-panel__body" style="padding:12px 20px;">
            <!-- Lista: Mejor Promedio -->
            <div id="top5-promedio">
                <?php if (!empty($topPromedio)): ?>
                    <?php foreach ($topPromedio as $i => $est): ?>
                        <div class="rpt-rank-item">
                            <span class="rpt-rank-badge n<?= $i + 1 ?>"><?= $i + 1 ?></span>
                            <div class="rpt-rank-info">
                                <div class="rpt-rank-name"><?= e($est['nombre'] ?? '') ?></div>
                                <div class="rpt-rank-detail"><?= (int)($est['intentos'] ?? 0) ?> intentos</div>
                            </div>
                            <div class="rpt-rank-value">
                                <span class="rpt-rank-value__num" style="color:var(--green-600);"><?= $est['promedio'] ?? 0 ?></span>
                                <span class="rpt-rank-value__unit">/20</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="est-empty">No hay datos suficientes.</div>
                <?php endif; ?>
            </div>

            <!-- Lista: Más Activos -->
            <div id="top5-activos" style="display:none;">
                <?php if (!empty($topActivos)): ?>
                    <?php foreach ($topActivos as $i => $est): ?>
                        <div class="rpt-rank-item">
                            <span class="rpt-rank-badge n<?= $i + 1 ?>"><?= $i + 1 ?></span>
                            <div class="rpt-rank-info">
                                <div class="rpt-rank-name"><?= e($est['nombre'] ?? '') ?></div>
                                <div class="rpt-rank-detail">Promedio: <?= $est['promedio'] ?? '—' ?></div>
                            </div>
                            <div class="rpt-rank-value">
                                <span class="rpt-rank-value__num" style="color:var(--blue-600);"><?= (int)($est['intentos'] ?? 0) ?></span>
                                <span class="rpt-rank-value__unit">int.</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="est-empty">No hay datos suficientes.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════ CHART.JS ═══════════════ -->
<script src="<?= asset('js/lib/chart.umd.min.js') ?>"></script>
<script>
// ── Colores de estado (misma paleta que profesor) ──
const estadoColors = {
    'En_Progreso': '#3b82f6',
    'Enviado':     '#f59e0b',
    'Aprobado':    '#22c55e',
    'Rechazado':   '#ef4444',
    'Correccion':  '#8b5cf6',
};

// ── Dona: Estado de Intentos ──
<?php if (!empty($estados)): ?>
(function() {
    const data = <?= json_encode($estados) ?>;
    const labels = Object.keys(data).map(k => k.replace('_', ' '));
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
                hoverOffset: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '65%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => {
                            const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                            const pct = total ? Math.round((ctx.parsed / total) * 100) : 0;
                            return ` ${ctx.label}: ${ctx.parsed} (${pct}%)`;
                        }
                    }
                }
            }
        }
    });

    // Leyenda custom
    const legendEl = document.getElementById('legend-estados');
    labels.forEach((l, i) => {
        legendEl.innerHTML += `<span class="est-legend__item"><span class="est-legend__dot" style="background:${colors[i]}"></span>${l} (${values[i]})</span>`;
    });
})();
<?php endif; ?>

// ── Barras: Promedio por Sección ──
<?php if (!empty($promSeccion)): ?>
(function() {
    const labels = <?= json_encode(array_column($promSeccion, 'seccion')) ?>;
    const data   = <?= json_encode(array_map(fn($r) => (float)$r['promedio'], $promSeccion)) ?>;
    const colors = data.map(v => v >= 15 ? '#22c55e' : (v >= 10 ? '#3b82f6' : '#f59e0b'));

    new Chart(document.getElementById('chart-secciones'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Promedio',
                data: data,
                backgroundColor: colors,
                borderRadius: 6,
                barThickness: 28
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { min: 0, max: 20, grid: { color: 'rgba(0,0,0,.04)' }, ticks: { font: { size: 11 }, stepSize: 5 } },
                y: { grid: { display: false }, ticks: { font: { size: 12, weight: 'bold' } } }
            }
        }
    });
})();
<?php endif; ?>

// ── Toggle Top 5 ──
function toggleTop5(tab, btn) {
    document.getElementById('top5-promedio').style.display = tab === 'promedio' ? '' : 'none';
    document.getElementById('top5-activos').style.display  = tab === 'activos' ? '' : 'none';
    document.querySelectorAll('.rpt-tab').forEach(t => t.classList.remove('active'));
    btn.classList.add('active');
}

// ── Filtro por Profesor ──
function filtrarPorProfesor() {
    const val = document.getElementById('filter-profesor').value;
    if (!val) {
        window.DataTableManager.setClientFilter('tbl-rendimiento', null);
    } else {
        window.DataTableManager.setClientFilter('tbl-rendimiento', row => {
            const profCell = row.children[1];
            return profCell && profCell.textContent.trim().toLowerCase().includes(val);
        });
    }
}
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/logged_layout.php';
?>