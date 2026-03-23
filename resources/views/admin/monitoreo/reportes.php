<?php
declare(strict_types=1);

$pageTitle = 'Reportes y Estadísticas';
$activePage = 'reportes';
$breadcrumbs = [
    'Inicio' => base_url('/admin'),
    'Monitoreo' => '#',
    'Estadísticas' => '#'
];

$extraCss = '<link rel="stylesheet" href="' . asset('css/admin/dashboard.css') . '">
<link rel="stylesheet" href="' . asset('css/shared/data-table.css') . '">';

// ── DATOS DEMO (se reemplazarán por queries reales) ──
$kpi = [
    'estudiantes'  => 124,
    'profesores'   => 8,
    'casos'        => 23,
    'intentos'     => 1847,
    'aprobacion'   => 78,
];

$actividadSemanal = [45, 62, 58, 71, 83, 67, 92, 78];
$actividadLabels  = ['Sem 1', 'Sem 2', 'Sem 3', 'Sem 4', 'Sem 5', 'Sem 6', 'Sem 7', 'Sem 8'];

$casosPorEstado = [
    ['label' => 'Casos activos', 'count' => 15, 'color' => 'var(--green-600)', 'bg' => '#f0fdf4'],
    ['label' => 'Casos en borrador', 'count' => 5,  'color' => 'var(--amber-600)', 'bg' => '#fffbeb'],
    ['label' => 'Casos cerrados',   'count' => 3,  'color' => 'var(--gray-600)',   'bg' => '#eef1f6'],
];

$rendimiento = [
    ['seccion' => 'SEC-01', 'profesor' => 'Prof. María García',   'estudiantes' => 28, 'intentos' => 156, 'promedio' => 15.2, 'aprobacion' => 85],
    ['seccion' => 'SEC-02', 'profesor' => 'Prof. Carlos López',   'estudiantes' => 32, 'intentos' => 203, 'promedio' => 12.8, 'aprobacion' => 72],
    ['seccion' => 'SEC-03', 'profesor' => 'Prof. Ana Rodríguez',  'estudiantes' => 25, 'intentos' => 178, 'promedio' => 16.1, 'aprobacion' => 92],
    ['seccion' => 'SEC-04', 'profesor' => 'Prof. José Martínez',  'estudiantes' => 30, 'intentos' => 189, 'promedio' => 13.5, 'aprobacion' => 76],
    ['seccion' => 'SEC-05', 'profesor' => 'Prof. Laura Sánchez',  'estudiantes' => 22, 'intentos' => 142, 'promedio' => 14.9, 'aprobacion' => 81],
];

$distribucionNotas = [
    ['rango' => '16 – 20', 'pct' => 28, 'cant' => 52,  'color' => 'var(--green-600)',  'bg' => 'var(--green-50)'],
    ['rango' => '11 – 15', 'pct' => 42, 'cant' => 78,  'color' => 'var(--blue-600)',   'bg' => 'var(--blue-50)'],
    ['rango' => '06 – 10', 'pct' => 21, 'cant' => 39,  'color' => 'var(--amber-600)',  'bg' => 'var(--amber-50)'],
    ['rango' => '00 – 05', 'pct' =>  9, 'cant' => 17,  'color' => 'var(--red-600)',    'bg' => 'var(--red-50)'],
];

$topMejorPromedio = [
    ['nombre' => 'Valentina Herrera',  'intentos' => 12, 'promedio' => 18.5],
    ['nombre' => 'Andrés Mendoza',     'intentos' =>  9, 'promedio' => 17.8],
    ['nombre' => 'Gabriela Torres',    'intentos' => 14, 'promedio' => 17.1],
    ['nombre' => 'Diego Ramírez',      'intentos' =>  8, 'promedio' => 16.9],
    ['nombre' => 'Camila Flores',      'intentos' => 11, 'promedio' => 16.5],
];

$topMasActivos = [
    ['nombre' => 'Luis Paredes',       'intentos' => 24, 'promedio' => 13.2],
    ['nombre' => 'Valentina Herrera',  'intentos' => 21, 'promedio' => 18.5],
    ['nombre' => 'Carlos Mendez',      'intentos' => 19, 'promedio' => 11.8],
    ['nombre' => 'Sofía Rivas',        'intentos' => 18, 'promedio' => 14.5],
    ['nombre' => 'Andrés Mendoza',     'intentos' => 17, 'promedio' => 17.8],
];

ob_start();
?>

<style>
.rpt-filter-bar { display:flex; align-items:center; justify-content:space-between; margin-bottom:24px; flex-wrap:wrap; gap:12px; }
.rpt-filter-bar h1 { margin:0; font-size:24px; font-weight:700; color:var(--gray-800); letter-spacing:-0.02em; }
.rpt-filter-bar p { margin:4px 0 0; font-size:14px; color:var(--gray-500); }
.rpt-filter-right { display:flex; align-items:center; gap:12px; }
.rpt-stats-row { display:grid; grid-template-columns:repeat(5,1fr); gap:16px; margin-bottom:24px; }
.rpt-charts-row { display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:24px; }
.rpt-bottom-row { display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:0; }
.rpt-panel { background:var(--white); border-radius:12px; border:1px solid var(--gray-200); box-shadow:0 1px 3px rgba(0,0,0,.05); }
.rpt-panel__header { padding:16px 20px; border-bottom:1px solid var(--gray-100); display:flex; align-items:center; justify-content:space-between; }
.rpt-panel__header h3 { margin:0; font-size:15px; font-weight:700; color:var(--gray-800); }
.rpt-panel__body { padding:20px; }
.rpt-bar-row { display:flex; align-items:center; gap:12px; padding:10px 0; border-bottom:1px solid var(--gray-50); }
.rpt-bar-row:last-child { border-bottom:none; }
.rpt-bar-label { width:60px; font-size:13px; font-weight:600; color:var(--gray-600); flex-shrink:0; }
.rpt-bar-track { flex:1; height:24px; background:var(--gray-100); border-radius:6px; overflow:hidden; position:relative; }
.rpt-bar-fill { height:100%; border-radius:6px; display:flex; align-items:center; justify-content:flex-end; padding-right:8px; font-size:11px; font-weight:700; color:white; transition:width .6s ease; }
.rpt-top-rank { width:24px; height:24px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:700; flex-shrink:0; }
.rpt-aprobacion-bar { height:6px; border-radius:3px; background:var(--gray-100); overflow:hidden; }
.rpt-aprobacion-fill { height:100%; border-radius:3px; }
.rpt-caso-badge { display:flex; align-items:center; gap:8px; padding:10px 14px; border-radius:8px; flex:1; }
.rpt-caso-badge__count { font-size:20px; font-weight:800; }
.rpt-caso-badge__label { font-size:12px; font-weight:500; }
.rpt-tab { padding:6px 14px; border:1px solid var(--gray-200); background:var(--white); border-radius:6px; font-size:12px; font-weight:600; color:var(--gray-500); cursor:pointer; transition:all .15s; font-family:var(--font-ui); }
.rpt-tab.active { background:var(--blue-600); color:white; border-color:var(--blue-600); }
.rpt-tab:hover:not(.active) { background:var(--gray-50); color:var(--gray-700); }
@media (max-width:1200px) { .rpt-stats-row { grid-template-columns:repeat(3,1fr); } }
@media (max-width:900px) { .rpt-stats-row { grid-template-columns:repeat(2,1fr); } .rpt-charts-row,.rpt-bottom-row { grid-template-columns:1fr; } }
</style>

<!-- Encabezado + Filtro -->
<div class="rpt-filter-bar">
    <div>
        <h1>Centro de Reportes</h1>
        <p>Métricas de uso y rendimiento académico del sistema.</p>
    </div>
    <div class="rpt-filter-right">
        <select class="per-page-select" style="min-width:170px;" id="filtro-periodo">
            <option value="mes">Este mes</option>
            <option value="trimestre">Último trimestre</option>
            <option value="semestre" selected>Este semestre</option>
            <option value="todo">Todo el tiempo</option>
        </select>
        <button class="btn btn-outline" onclick="exportarReportePDF()" style="display:inline-flex; align-items:center; gap:6px;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <polyline points="7 10 12 15 17 10"/>
                <line x1="12" y1="15" x2="12" y2="3"/>
            </svg>
            Exportar PDF
        </button>
    </div>
</div>

<!-- 5 Tarjetas KPI -->
<div class="rpt-stats-row">
    <div class="admin-stat-card">
        <div class="admin-stat-card__icon bg-blue">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
        </div>
        <div class="admin-stat-card__info">
            <span class="admin-stat-card__value"><?= number_format($kpi['estudiantes']) ?></span>
            <span class="admin-stat-card__label">Estudiantes activos</span>
        </div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-card__icon bg-green">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
            </svg>
        </div>
        <div class="admin-stat-card__info">
            <span class="admin-stat-card__value"><?= $kpi['profesores'] ?></span>
            <span class="admin-stat-card__label">Profesores</span>
        </div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-card__icon bg-yellow">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>
                <line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>
            </svg>
        </div>
        <div class="admin-stat-card__info">
            <span class="admin-stat-card__value"><?= $kpi['casos'] ?></span>
            <span class="admin-stat-card__label">Casos publicados</span>
        </div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-card__icon bg-purple">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
            </svg>
        </div>
        <div class="admin-stat-card__info">
            <span class="admin-stat-card__value"><?= number_format($kpi['intentos']) ?></span>
            <span class="admin-stat-card__label">Intentos totales</span>
        </div>
    </div>
    <!-- KPI: Tasa de aprobación -->
    <div class="admin-stat-card">
        <div class="admin-stat-card__icon" style="background:<?= $kpi['aprobacion'] >= 70 ? 'var(--green-500)' : 'var(--amber-500)' ?>;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
        </div>
        <div class="admin-stat-card__info">
            <span class="admin-stat-card__value"><?= $kpi['aprobacion'] ?>%</span>
            <span class="admin-stat-card__label">Tasa de aprobación</span>
        </div>
    </div>
</div>

<!-- Fila de estados de casos -->
<div style="display:flex; gap:12px; margin-bottom:24px;">
    <?php foreach ($casosPorEstado as $c): ?>
        <div class="rpt-caso-badge" style="background:<?= $c['bg'] ?>; flex:1; padding:14px 18px; border-radius:10px; border:1px solid <?= $c['color'] ?>22;">
            <span class="rpt-caso-badge__count" style="color:<?= $c['color'] ?>;"><?= $c['count'] ?></span>
            <span class="rpt-caso-badge__label" style="color:<?= $c['color'] ?>;"><?= $c['label'] ?></span>
        </div>
    <?php endforeach; ?>
</div>

<!-- Fila 2: Gráfico actividad + Barras comparativas -->
<div class="rpt-charts-row">
    <!-- Gráfico de Líneas -->
    <div class="rpt-panel">
        <div class="rpt-panel__header">
            <h3>Actividad del Sistema</h3>
            <span style="font-size:13px; color:var(--gray-500); font-weight:500;">Intentos por semana</span>
        </div>
        <div class="rpt-panel__body">
            <div style="position:relative; height:220px;"><canvas id="chart-actividad"></canvas></div>
        </div>
    </div>

    <!-- Barras comparativas de rendimiento por sección -->
    <div class="rpt-panel">
        <div class="rpt-panel__header">
            <h3>Comparativa por Sección</h3>
            <span style="font-size:13px; color:var(--gray-500); font-weight:500;">Promedio de calificación</span>
        </div>
        <div class="rpt-panel__body">
            <div style="position:relative; height:240px;"><canvas id="chart-secciones"></canvas></div>
        </div>
    </div>
</div>

<!-- Fila inferior: Distribución + Top 5 con toggle -->
<div class="rpt-bottom-row">
    <!-- Distribución de Notas -->
    <div class="rpt-panel">
        <div class="rpt-panel__header">
            <h3>Distribución de Calificaciones</h3>
        </div>
        <div class="rpt-panel__body">
            <?php foreach ($distribucionNotas as $d): ?>
                <div class="rpt-bar-row">
                    <span class="rpt-bar-label"><?= $d['rango'] ?></span>
                    <div class="rpt-bar-track">
                        <div class="rpt-bar-fill" style="width:<?= $d['pct'] ?>%; background:<?= $d['color'] ?>;"><?= $d['pct'] ?>%</div>
                    </div>
                    <span style="font-size:12px; color:var(--gray-500); min-width:70px; text-align:right;"><?= $d['cant'] ?> est.</span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Top 5 con Toggle -->
    <div class="rpt-panel">
        <div class="rpt-panel__header">
            <h3>Top 5 Estudiantes</h3>
            <div style="display:flex; gap:6px;">
                <button class="rpt-tab active" data-tab="promedio" onclick="toggleTop5('promedio', this)">Mejor Promedio</button>
                <button class="rpt-tab" data-tab="activos" onclick="toggleTop5('activos', this)">Más Activos</button>
            </div>
        </div>
        <div class="rpt-panel__body" style="padding:12px 20px;">
            <!-- Lista: Mejor Promedio -->
            <div id="top5-promedio">
                <?php foreach ($topMejorPromedio as $i => $est):
                    $rankColors = ['background:var(--blue-600); color:white;', 'background:var(--blue-100); color:var(--blue-600);', 'background:var(--gray-200); color:var(--gray-600);', 'background:var(--gray-100); color:var(--gray-500);', 'background:var(--gray-100); color:var(--gray-500);'];
                ?>
                    <div style="display:flex; align-items:center; gap:12px; padding:10px 0; <?= $i < 4 ? 'border-bottom:1px solid var(--gray-100);' : '' ?>">
                        <span class="rpt-top-rank" style="<?= $rankColors[$i] ?>"><?= $i + 1 ?></span>
                        <div style="flex:1;">
                            <div style="font-size:13px; font-weight:600; color:var(--gray-700);"><?= e($est['nombre']) ?></div>
                            <div style="font-size:11px; color:var(--gray-400);"><?= $est['intentos'] ?> intentos</div>
                        </div>
                        <div style="text-align:right;">
                            <span style="font-size:14px; font-weight:700; color:var(--green-600);"><?= $est['promedio'] ?></span>
                            <span style="font-size:11px; color:var(--gray-400);">/20</span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Lista: Más Activos -->
            <div id="top5-activos" style="display:none;">
                <?php foreach ($topMasActivos as $i => $est):
                    $rankColors = ['background:var(--purple-600,#8b5cf6); color:white;', 'background:var(--purple-100,#ede9fe); color:var(--purple-600,#8b5cf6);', 'background:var(--gray-200); color:var(--gray-600);', 'background:var(--gray-100); color:var(--gray-500);', 'background:var(--gray-100); color:var(--gray-500);'];
                ?>
                    <div style="display:flex; align-items:center; gap:12px; padding:10px 0; <?= $i < 4 ? 'border-bottom:1px solid var(--gray-100);' : '' ?>">
                        <span class="rpt-top-rank" style="<?= $rankColors[$i] ?>"><?= $i + 1 ?></span>
                        <div style="flex:1;">
                            <div style="font-size:13px; font-weight:600; color:var(--gray-700);"><?= e($est['nombre']) ?></div>
                            <div style="font-size:11px; color:var(--gray-400);">Promedio: <?= $est['promedio'] ?></div>
                        </div>
                        <div style="text-align:right;">
                            <span style="font-size:14px; font-weight:700; color:var(--blue-600);"><?= $est['intentos'] ?></span>
                            <span style="font-size:11px; color:var(--gray-400);"> int.</span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="<?= asset('js/lib/chart.umd.min.js') ?>"></script>
<script>
// ── Gráfico de Actividad (Líneas) ──
new Chart(document.getElementById('chart-actividad'), {
    type: 'line',
    data: {
        labels: <?= json_encode($actividadLabels) ?>,
        datasets: [{
            label: 'Intentos',
            data: <?= json_encode($actividadSemanal) ?>,
            borderColor: '#1a4a8a',
            backgroundColor: 'rgba(26, 74, 138, 0.08)',
            fill: true,
            tension: 0.4,
            borderWidth: 2.5,
            pointRadius: 4,
            pointBackgroundColor: '#1a4a8a',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointHoverRadius: 6,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,.04)' }, ticks: { font: { size: 11 } } },
            x: { grid: { display: false }, ticks: { font: { size: 11 } } }
        }
    }
});

// ── Barras Comparativas por Sección ──
new Chart(document.getElementById('chart-secciones'), {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($rendimiento, 'seccion')) ?>,
        datasets: [{
            label: 'Promedio',
            data: <?= json_encode(array_column($rendimiento, 'promedio')) ?>,
            backgroundColor: <?= json_encode(array_map(function($r) {
                return $r['promedio'] >= 15 ? '#22c55e' : ($r['promedio'] >= 12 ? '#3b82f6' : '#f59e0b');
            }, $rendimiento)) ?>,
            borderRadius: 6,
            barThickness: 32,
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

// ── Toggle Top 5 ──
function toggleTop5(tab, btn) {
    document.getElementById('top5-promedio').style.display = tab === 'promedio' ? '' : 'none';
    document.getElementById('top5-activos').style.display = tab === 'activos' ? '' : 'none';
    document.querySelectorAll('.rpt-tab').forEach(t => t.classList.remove('active'));
    btn.classList.add('active');
}

// ── Exportar PDF ──
function exportarReportePDF() {
    let iframe = document.getElementById('print-iframe-rpt');
    if (iframe) iframe.remove();
    iframe = document.createElement('iframe');
    iframe.id = 'print-iframe-rpt';
    iframe.style.cssText = 'position:fixed; top:-9999px; left:-9999px; width:1px; height:1px; border:none;';
    document.body.appendChild(iframe);

    const fecha = new Date();
    const fechaStr = fecha.toLocaleDateString('es-VE', { day:'2-digit', month:'long', year:'numeric' });
    const horaStr = fecha.toLocaleTimeString('es-VE', { hour:'2-digit', minute:'2-digit' });
    const periodo = document.getElementById('filtro-periodo').options[document.getElementById('filtro-periodo').selectedIndex].text;

    let html = `<!DOCTYPE html><html><head><title>Reporte SPDSS</title>
<style>
@page { size:landscape; margin:14mm; }
body { font-family:'Segoe UI',Arial,sans-serif; color:#1e293b; font-size:11px; padding:0; margin:0; }
.header { border-bottom:3px solid #1a4a8a; padding-bottom:10px; margin-bottom:16px; display:flex; justify-content:space-between; align-items:flex-end; }
.header h1 { font-size:18px; margin:0; color:#1a4a8a; }
.header .meta { font-size:10px; color:#64748b; text-align:right; margin:0; }
.section { margin-bottom:16px; page-break-inside:avoid; }
.section h2 { font-size:12px; font-weight:700; color:#1a4a8a; margin:0 0 8px; padding-bottom:4px; border-bottom:1px solid #e2e8f0; }
table { width:100%; border-collapse:collapse; margin-bottom:0; }
th, td { border:1px solid #e2e8f0; padding:5px 8px; text-align:left; font-size:10px; }
th { background:#f1f5f9; font-weight:700; color:#334155; }
tr:nth-child(even) td { background:#f8fafc; }
.kpi-row { display:flex; gap:12px; margin-bottom:14px; }
.kpi-box { flex:1; border:1px solid #e2e8f0; border-radius:6px; padding:10px 14px; text-align:center; }
.kpi-box .val { font-size:18px; font-weight:800; color:#1a4a8a; display:block; }
.kpi-box .lbl { font-size:9px; color:#64748b; text-transform:uppercase; letter-spacing:0.5px; }
.two-col { display:flex; gap:20px; }
.two-col > div { flex:1; }
.bar-row { display:flex; align-items:center; gap:8px; margin-bottom:6px; }
.bar-label { width:50px; font-size:10px; font-weight:600; color:#475569; }
.bar-track { flex:1; height:14px; background:#f1f5f9; border-radius:4px; overflow:hidden; }
.bar-fill { height:100%; border-radius:4px; }
.bar-count { font-size:9px; color:#64748b; min-width:50px; text-align:right; }
.caso-row { display:flex; gap:10px; margin-bottom:14px; }
.caso-badge { flex:1; padding:8px 12px; border-radius:6px; border:1px solid #e2e8f0; }
.caso-badge .num { font-size:16px; font-weight:800; }
.caso-badge .txt { font-size:9px; }
</style></head><body>`;

    // Header
    html += '<div class="header"><div><h1>SPDSS — Centro de Reportes</h1><span style="font-size:10px;color:#64748b;">Sistema Pedagógico de Determinación del SENIAT Simulado</span></div>';
    html += '<div class="meta">Generado: ' + fechaStr + ' — ' + horaStr + '<br>Período: ' + periodo + '</div></div>';

    // KPIs
    html += '<div class="kpi-row">';
    html += '<div class="kpi-box"><span class="val"><?= number_format($kpi['estudiantes']) ?></span><span class="lbl">Estudiantes</span></div>';
    html += '<div class="kpi-box"><span class="val"><?= $kpi['profesores'] ?></span><span class="lbl">Profesores</span></div>';
    html += '<div class="kpi-box"><span class="val"><?= $kpi['casos'] ?></span><span class="lbl">Casos</span></div>';
    html += '<div class="kpi-box"><span class="val"><?= number_format($kpi['intentos']) ?></span><span class="lbl">Intentos</span></div>';
    html += '<div class="kpi-box"><span class="val"><?= $kpi['aprobacion'] ?>%</span><span class="lbl">Aprobación</span></div>';
    html += '</div>';

    // Casos por estado
    html += '<div class="caso-row">';
    <?php foreach ($casosPorEstado as $c): ?>
    html += '<div class="caso-badge" style="background:<?= $c['bg'] ?>;"><span class="num" style="color:<?= $c['color'] ?>;"><?= $c['count'] ?></span> <span class="txt" style="color:<?= $c['color'] ?>;"><?= $c['label'] ?></span></div>';
    <?php endforeach; ?>
    html += '</div>';

    // Rendimiento por sección
    html += '<div class="section"><h2>Rendimiento por Sección</h2>';
    html += '<table><tr><th>Sección</th><th>Profesor</th><th>Estudiantes</th><th>Intentos</th><th>Promedio</th><th>Aprobación</th></tr>';
    <?php foreach ($rendimiento as $r): ?>
    html += '<tr><td style="font-weight:600;color:#1a4a8a;"><?= $r['seccion'] ?></td><td><?= $r['profesor'] ?></td><td style="text-align:center;"><?= $r['estudiantes'] ?></td><td style="text-align:center;"><?= $r['intentos'] ?></td><td style="text-align:center;font-weight:600;"><?= $r['promedio'] ?></td><td style="text-align:center;"><?= $r['aprobacion'] ?>%</td></tr>';
    <?php endforeach; ?>
    html += '</table></div>';

    // Dos columnas: Distribución + Top 5
    html += '<div class="two-col">';

    // Distribución
    html += '<div class="section"><h2>Distribución de Calificaciones</h2>';
    <?php foreach ($distribucionNotas as $d): ?>
    html += '<div class="bar-row"><span class="bar-label"><?= $d['rango'] ?></span><div class="bar-track"><div class="bar-fill" style="width:<?= $d['pct'] ?>%;background:<?= $d['color'] ?>;"></div></div><span class="bar-count"><?= $d['pct'] ?>% (<?= $d['cant'] ?>)</span></div>';
    <?php endforeach; ?>
    html += '</div>';

    // Top 5
    html += '<div class="section"><h2>Top 5 — Mejor Promedio</h2>';
    html += '<table><tr><th style="width:8%;">#</th><th>Nombre</th><th style="width:18%;text-align:center;">Intentos</th><th style="width:18%;text-align:center;">Promedio</th></tr>';
    <?php foreach ($topMejorPromedio as $i => $est): ?>
    html += '<tr><td style="text-align:center;font-weight:700;"><?= $i+1 ?></td><td><?= $est['nombre'] ?></td><td style="text-align:center;"><?= $est['intentos'] ?></td><td style="text-align:center;font-weight:700;"><?= $est['promedio'] ?></td></tr>';
    <?php endforeach; ?>
    html += '</table></div>';

    html += '</div>'; // close two-col
    html += '</body></html>';

    const doc = iframe.contentDocument || iframe.contentWindow.document;
    doc.open();
    doc.write(html);
    doc.close();
    iframe.onload = function() {
        iframe.contentWindow.focus();
        iframe.contentWindow.print();
    };
}
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/logged_layout.php';
?>