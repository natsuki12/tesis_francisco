<?php
declare(strict_types=1);

// ARCHIVO: resources/views/student/mis_calificaciones.php

$pageTitle = 'Mis Calificaciones — Simulador SENIAT';
$activePage = 'mis-calificaciones';
$extraCss = '<link rel="stylesheet" href="' . asset('css/student/mis_calificaciones.css') . '">';

// ── Datos Placeholder ──────────────────────────────────────
$promedio = 15.7;
$casosCalificados = 2;

$calificaciones = [
    [
        'caso'        => 'Sucesión Pérez Alvarado',
        'modalidad'   => 'Evaluación',
        'mejor_nota'  => 16.0,
        'intentos'    => '1 de 3',
        'fecha'       => '2026-03-06',
        'estado'      => 'Calificada',
        'parciales'   => [
            'Datos del Causante' => ['nota' => 4.5, 'max' => 5],
            'Herederos'          => ['nota' => 5.0, 'max' => 5],
            'Inventario'         => ['nota' => 3.5, 'max' => 5],
            'Cálculo'            => ['nota' => 3.0, 'max' => 5],
        ],
        'observaciones' => 'Excelente trabajo en la identificación de herederos. En el inventario, faltó incluir una cuenta bancaria. El cálculo del impuesto tiene un error en la alícuota del tramo 2.',
    ],
    [
        'caso'        => 'Sucesión González Méndez',
        'modalidad'   => 'Guiado',
        'mejor_nota'  => 14.5,
        'intentos'    => '2 de 3',
        'fecha'       => '2026-03-03',
        'estado'      => 'Calificada',
        'parciales'   => [
            'Datos del Causante' => ['nota' => 4.0, 'max' => 5],
            'Herederos'          => ['nota' => 4.5, 'max' => 5],
            'Inventario'         => ['nota' => 3.0, 'max' => 5],
            'Cálculo'            => ['nota' => 3.0, 'max' => 5],
        ],
        'observaciones' => 'Buen trabajo en herederos. Revisar el cálculo del impuesto: la alícuota asignada no corresponde al tramo correcto. En el inventario, revisar la valoración del vehículo.',
    ],
    [
        'caso'        => 'Sucesión Ramírez Torres',
        'modalidad'   => 'Libre',
        'mejor_nota'  => null,
        'intentos'    => '0 de 3',
        'fecha'       => null,
        'estado'      => 'Sin iniciar',
        'parciales'   => [],
        'observaciones' => null,
    ],
    [
        'caso'        => 'Sucesión López Fernández',
        'modalidad'   => 'Evaluación',
        'mejor_nota'  => null,
        'intentos'    => '1 de 2',
        'fecha'       => null,
        'estado'      => 'Pendiente de revisión',
        'parciales'   => [],
        'observaciones' => null,
    ],
];

// Scorecard colors
$scoreClass = $promedio >= 15 ? 'score-pass' : ($promedio >= 10 ? 'score-warn' : 'score-fail');
$barClass = $promedio >= 15 ? 'bar-pass' : ($promedio >= 10 ? 'bar-warn' : 'bar-fail');
$barWidth = ($promedio / 20) * 100;

ob_start();
?>

<!-- Page Header -->
<div class="page-header">
    <div class="page-header-left">
        <h1>Mis Calificaciones</h1>
        <p>Resumen de tu rendimiento en las prácticas</p>
    </div>
</div>

<!-- Scorecard -->
<div class="scorecard animate-in">
    <?php if ($promedio !== null): ?>
        <span class="scorecard-value <?= $scoreClass ?>"><?= number_format($promedio, 1) ?></span>
        <span class="scorecard-max"> / 20</span>
        <span class="scorecard-label">Promedio de <?= $casosCalificados ?> caso<?= $casosCalificados !== 1 ? 's' : '' ?> calificado<?= $casosCalificados !== 1 ? 's' : '' ?></span>
        <div class="scorecard-bar">
            <div class="scorecard-bar-fill <?= $barClass ?>" style="width: <?= $barWidth ?>%"></div>
        </div>
    <?php else: ?>
        <span class="scorecard-value score-na">—</span>
        <span class="scorecard-max"> / 20</span>
        <span class="scorecard-label">Sin calificaciones aún</span>
    <?php endif; ?>
</div>

<!-- Tabla de Calificaciones -->
<div class="table-container animate-in">
    <table class="data-table" id="calificaciones-table">
        <thead>
            <tr>
                <th>Caso</th>
                <th>Modalidad</th>
                <th>Mejor Nota</th>
                <th>Intentos</th>
                <th>Fecha</th>
                <th>Estado</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($calificaciones)): ?>
                <tr>
                    <td colspan="7" class="empty-cell">
                        <div class="empty-state empty-state--blue">
                            <div class="empty-state-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                                </svg>
                            </div>
                            <h3>Sin calificaciones aún</h3>
                            <p>Cuando tus intentos sean calificados por tu profesor, las notas aparecerán aquí.</p>
                        </div>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($calificaciones as $i => $cal):
                    $modeClass = match($cal['modalidad']) {
                        'Guiado'     => 'mode-guiado',
                        'Libre'      => 'mode-libre',
                        'Evaluación' => 'mode-evaluacion',
                        default      => 'mode-guiado',
                    };
                    $statusClass = match($cal['estado']) {
                        'Calificada'           => 'status-active',
                        'Pendiente de revisión' => 'status-warning',
                        'En progreso'          => 'status-info',
                        'Sin iniciar'          => 'status-draft',
                        default                => 'status-draft',
                    };
                    $hasDesglose = !empty($cal['parciales']);
                ?>
                    <!-- Data row -->
                    <tr>
                        <td><strong><?= htmlspecialchars($cal['caso']) ?></strong></td>
                        <td><span class="mode-badge <?= $modeClass ?>"><?= htmlspecialchars($cal['modalidad']) ?></span></td>
                        <td>
                            <?php if ($cal['mejor_nota'] !== null): ?>
                                <span class="nota-cell <?= $cal['mejor_nota'] >= 10 ? 'nota-pass' : 'nota-fail' ?>">
                                    <?= number_format($cal['mejor_nota'], 1) ?>/20
                                </span>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($cal['intentos']) ?></td>
                        <td><?= $cal['fecha'] ? date('d/m/Y', strtotime($cal['fecha'])) : '—' ?></td>
                        <td><span class="status-badge <?= $statusClass ?>"><?= htmlspecialchars($cal['estado']) ?></span></td>
                        <td>
                            <?php if ($hasDesglose): ?>
                                <button class="grade-toggle" onclick="toggleGradeRow(<?= $i ?>)">
                                    Ver desglose
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                                        <polyline points="6 9 12 15 18 9" />
                                    </svg>
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <!-- Expandable row -->
                    <?php if ($hasDesglose): ?>
                        <tr class="grade-expand-row" id="grade-row-<?= $i ?>">
                            <td colspan="7">
                                <div class="grade-expand-content">
                                    <div class="grade-expand-inner">
                                        <!-- Partial grades -->
                                        <div class="partial-grades">
                                            <h4>Notas parciales</h4>
                                            <?php foreach ($cal['parciales'] as $seccion => $notas): ?>
                                                <div class="partial-row">
                                                    <span><?= htmlspecialchars($seccion) ?></span>
                                                    <span class="partial-value"><?= number_format($notas['nota'], 1) ?> / <?= $notas['max'] ?></span>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <!-- Observations -->
                                        <div class="grade-observations">
                                            <h4>Observaciones del profesor</h4>
                                            <?php if ($cal['observaciones']): ?>
                                                <p><?= htmlspecialchars($cal['observaciones']) ?></p>
                                            <?php else: ?>
                                                <p style="color: var(--gray-400); font-style: italic;">Sin observaciones.</p>
                                            <?php endif; ?>
                                            <a href="<?= base_url('/mis-calificaciones/' . ($i + 1)) ?>" class="ver-link" style="font-size: var(--text-xs);">
                                                Ver corrección completa
                                                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                                                    <path d="M5 12h14" /><polyline points="12 5 19 12 12 19" />
                                                </svg>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- JS: Accordion toggle -->
<script>
(function() {
    window.toggleGradeRow = function(index) {
        const row = document.getElementById('grade-row-' + index);
        if (row) row.classList.toggle('expanded');
    };
})();
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/logged_layout.php';
?>
