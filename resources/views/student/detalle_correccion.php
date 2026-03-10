<?php
declare(strict_types=1);

// ARCHIVO: resources/views/student/detalle_correccion.php
// Read-only view of professor's correction.

$pageTitle = 'Corrección — Simulador SENIAT';
$activePage = 'mis-calificaciones';
$extraCss = '<link rel="stylesheet" href="' . asset('css/professor/detalle_intento.css') . '">'
    . '<link rel="stylesheet" href="' . asset('css/student/detalle_correccion.css') . '">';

// ── Datos Placeholder ──────────────────────────────────────
$intento = [
    'caso_titulo' => 'Sucesión Pérez Alvarado',
    'estudiante' => $_SESSION['user_name'] ?? 'Ana María Martínez',
    'seccion' => '4to A',
    'modalidad' => 'Evaluación',
    'intento_num' => 1,
    'intento_max' => 3,
    'fecha_envio' => '2026-03-03 11:45:00',
    'fecha_evaluacion' => '2026-03-06 09:30:00',
    'nota' => 16.0,
    'observaciones' => 'Excelente trabajo en la identificación de herederos. En el inventario, faltó incluir una cuenta bancaria. El cálculo del impuesto tiene un error en la alícuota del tramo 2.',
    'profesor' => 'Prof. César Rodríguez',
];

// Comparison data (same structure as professor)
$causante = [
    ['campo' => 'Nombre del causante', 'declarado' => 'Carlos Alberto Pérez', 'esperado' => 'Carlos Alberto Pérez', 'match' => true],
    ['campo' => 'Cédula del causante', 'declarado' => 'V-10.234.567', 'esperado' => 'V-10.234.567', 'match' => true],
    ['campo' => 'Fecha de fallecimiento', 'declarado' => '20/02/2026', 'esperado' => '20/02/2026', 'match' => true],
    ['campo' => 'Último domicilio', 'declarado' => 'Valencia, Edo. Carabobo', 'esperado' => 'Valencia, Edo. Carabobo', 'match' => true],
    ['campo' => 'Estado civil', 'declarado' => 'Casado', 'esperado' => 'Casado', 'match' => true],
    ['campo' => 'Tipo de sucesión', 'declarado' => 'Ab intestato', 'esperado' => 'Ab intestato', 'match' => true],
];

$herederos = [
    ['campo' => 'Heredero 1 — Rosa Pérez', 'declarado' => 'Cónyuge — 50%', 'esperado' => 'Cónyuge — 50%', 'match' => true],
    ['campo' => 'Heredero 2 — Carlos Jr.', 'declarado' => 'Hijo — 25%', 'esperado' => 'Hijo — 25%', 'match' => true],
    ['campo' => 'Heredero 3 — Laura Pérez', 'declarado' => 'Hija — 25%', 'esperado' => 'Hija — 25%', 'match' => true],
];

$inventario = [
    ['campo' => 'Inmueble — Casa Valencia', 'declarado' => 'Bs. 200.000,00', 'esperado' => 'Bs. 200.000,00', 'match' => true],
    ['campo' => 'Vehículo — Honda Civic 2021', 'declarado' => 'Bs. 40.000,00', 'esperado' => 'Bs. 40.000,00', 'match' => true],
    ['campo' => 'Cuenta — Banco Provincial', 'declarado' => '—', 'esperado' => 'Bs. 8.500,00', 'match' => false],
];

$calculo = [
    ['campo' => 'Patrimonio bruto', 'declarado' => 'Bs. 240.000,00', 'esperado' => 'Bs. 248.500,00', 'match' => false],
    ['campo' => 'Pasivos deducibles', 'declarado' => 'Bs. 10.000,00', 'esperado' => 'Bs. 10.000,00', 'match' => true],
    ['campo' => 'Patrimonio neto', 'declarado' => 'Bs. 230.000,00', 'esperado' => 'Bs. 238.500,00', 'match' => false],
    ['campo' => 'Alícuota aplicada', 'declarado' => '20%', 'esperado' => '25%', 'match' => false],
    ['campo' => 'Impuesto determinado', 'declarado' => 'Bs. 46.000,00', 'esperado' => 'Bs. 59.625,00', 'match' => false],
];

$sections = [
    ['title' => 'Datos del Causante', 'data' => $causante, 'nota' => 4.5, 'max_pts' => 5, 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>'],
    ['title' => 'Herederos', 'data' => $herederos, 'nota' => 5.0, 'max_pts' => 5, 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>'],
    ['title' => 'Inventario Patrimonial', 'data' => $inventario, 'nota' => 3.5, 'max_pts' => 5, 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>'],
    ['title' => 'Cálculo del Impuesto', 'data' => $calculo, 'nota' => 3.0, 'max_pts' => 5, 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="4" y="2" width="16" height="20" rx="2"/><line x1="8" y1="6" x2="16" y2="6"/><line x1="8" y1="10" x2="16" y2="10"/><line x1="8" y1="14" x2="12" y2="14"/></svg>'],
];

$notaTotal = $intento['nota'];
$notaClass = $notaTotal >= 10 ? 'pass' : 'fail';

ob_start();
?>

<!-- Breadcrumb -->
<div class="breadcrumb">
    <a href="<?= base_url('/mis-asignaciones') ?>">Mis Asignaciones</a>
    <span class="breadcrumb-sep">›</span>
    <a href="<?= base_url('/mis-asignaciones/1') ?>">
        <?= htmlspecialchars($intento['caso_titulo']) ?>
    </a>
    <span class="breadcrumb-sep">›</span>
    <span class="breadcrumb-current">Intento #
        <?= $intento['intento_num'] ?>
    </span>
</div>

<!-- Intento Header -->
<div class="intento-header animate-in">
    <div class="intento-header-left">
        <h2>
            <?= htmlspecialchars($intento['caso_titulo']) ?>
        </h2>
        <p>Intento #
            <?= $intento['intento_num'] ?> | Enviado:
            <?= date('d/m/Y', strtotime($intento['fecha_envio'])) ?> | Calificado:
            <?= date('d/m/Y', strtotime($intento['fecha_evaluacion'])) ?>
        </p>
    </div>
    <span class="intento-status-big status-calificado">
        <?= number_format($notaTotal, 1) ?> / 20
    </span>
</div>

<!-- Correction Layout (70 / 30) -->
<div class="correction-layout">

    <!-- ═══ MAIN AREA (Comparison Accordions) ═══ -->
    <div class="correction-main">
        <?php foreach ($sections as $i => $section):
            $correct = count(array_filter($section['data'], fn($r) => $r['match']));
            $total = count($section['data']);
            $errors = $total - $correct;
            $isOpen = $i === 0;
            ?>
            <div class="accordion <?= $isOpen ? 'accordion--open' : '' ?>" id="accordion-<?= $i ?>">
                <div class="accordion-header" onclick="toggleAccordion(<?= $i ?>)">
                    <span class="accordion-title">
                        <?= $section['icon'] ?>
                        <?= htmlspecialchars($section['title']) ?>
                        <?php if ($errors === 0): ?>
                            <span class="accordion-badge badge-correct">
                                <?= $correct ?>/
                                <?= $total ?> ✓
                            </span>
                        <?php else: ?>
                            <span class="accordion-badge badge-errors">
                                <?= $errors ?> error
                                <?= $errors > 1 ? 'es' : '' ?>
                            </span>
                        <?php endif; ?>
                    </span>
                    <svg class="accordion-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                        stroke-linecap="round">
                        <polyline points="6 9 12 15 18 9" />
                    </svg>
                </div>
                <div class="accordion-body">
                    <div class="accordion-content">
                        <table class="comparison-table">
                            <thead>
                                <tr>
                                    <th>Campo</th>
                                    <th>Tu respuesta</th>
                                    <th>Valor correcto</th>
                                    <th style="width:40px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($section['data'] as $row): ?>
                                    <tr>
                                        <td class="field-label">
                                            <?= htmlspecialchars($row['campo']) ?>
                                        </td>
                                        <td class="<?= $row['match'] ? 'cell-correct' : 'cell-error' ?>">
                                            <?= htmlspecialchars($row['declarado']) ?>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($row['esperado']) ?>
                                        </td>
                                        <td class="cell-match-icon <?= $row['match'] ? 'match-ok' : 'match-fail' ?>">
                                            <?php if ($row['match']): ?>
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"
                                                    stroke-linecap="round">
                                                    <polyline points="20 6 9 17 4 12" />
                                                </svg>
                                            <?php else: ?>
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"
                                                    stroke-linecap="round">
                                                    <line x1="18" y1="6" x2="6" y2="18" />
                                                    <line x1="6" y1="6" x2="18" y2="18" />
                                                </svg>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- ═══ SIDEBAR (Read-only grades) ═══ -->
    <div class="correction-sidebar readonly">
        <!-- Grade Total -->
        <div class="sidebar-panel">
            <div class="sidebar-panel-body">
                <div class="student-grade-display">
                    <div class="student-grade-label">Calificación obtenida</div>
                    <span class="student-grade-value <?= $notaClass ?>">
                        <?= number_format($notaTotal, 1) ?>
                    </span>
                    <span class="student-grade-max"> / 20</span>
                </div>

                <!-- Partial Grades (read-only) -->
                <div class="grade-partials">
                    <?php foreach ($sections as $section): ?>
                        <div class="grade-partial-row">
                            <span class="grade-partial-label">
                                <?= htmlspecialchars($section['title']) ?>
                            </span>
                            <input type="text" class="grade-input" readonly
                                value="<?= number_format($section['nota'], 1) ?>">
                            <span class="grade-partial-max">/
                                <?= $section['max_pts'] ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Observations (read-only) -->
        <div class="sidebar-panel">
            <div class="sidebar-panel-header">
                <h3>Observaciones del Profesor</h3>
            </div>
            <div class="sidebar-panel-body">
                <textarea class="observations-textarea"
                    readonly><?= htmlspecialchars($intento['observaciones'] ?? '') ?></textarea>
                <div class="professor-info">
                    Calificado por <strong>
                        <?= htmlspecialchars($intento['profesor']) ?>
                    </strong>
                    el
                    <?= date('d/m/Y', strtotime($intento['fecha_evaluacion'])) ?>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="sidebar-panel">
            <div class="sidebar-actions">
                <button class="btn btn-outline" onclick="alert('Descargar planilla PDF (placeholder)')">
                    Descargar planilla PDF
                </button>
                <a href="<?= base_url('/mis-asignaciones') ?>" class="btn btn-secondary"
                    style="text-decoration: none; text-align: center;">
                    Volver a Mis Asignaciones
                </a>
            </div>
        </div>
    </div>
</div>

<!-- JS: Accordion toggle -->
<script>
    (function () {
        window.toggleAccordion = function (index) {
            const acc = document.getElementById('accordion-' + index);
            if (acc) acc.classList.toggle('accordion--open');
        };
    })();
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/logged_layout.php';
?>