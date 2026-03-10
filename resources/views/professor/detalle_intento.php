<?php
declare(strict_types=1);

// ARCHIVO: resources/views/professor/detalle_intento.php
// Vista de corrección / revisión de un intento de estudiante.

$pageTitle = 'Detalle de Intento — Simulador SENIAT';
$activePage = 'entregas';
$extraCss = '<link rel="stylesheet" href="' . asset('css/professor/detalle_intento.css') . '">';

// ── Datos Placeholder ──────────────────────────────────────
$intento = [
    'id' => 102,
    'caso_titulo' => 'Sucesión González Méndez',
    'estudiante' => 'Ana María Martínez López',
    'seccion' => '4to A',
    'asignacion' => 'Evaluación parcial 1',
    'intento_num' => 2,
    'intento_max' => 3,
    'fecha_envio' => '2026-03-07 14:20:00',
    'estado' => 'Enviado',  // Enviado | Calificado | En Progreso
    'nota' => null,       // null si no calificado
    'observaciones' => null,
    'fecha_evaluacion' => null,
];

// Comparison data: field → [declarado, esperado, match]
$causante = [
    ['campo' => 'Nombre del causante', 'declarado' => 'Juan Carlos González', 'esperado' => 'Juan Carlos González', 'match' => true],
    ['campo' => 'Cédula del causante', 'declarado' => 'V-8.456.789', 'esperado' => 'V-8.456.789', 'match' => true],
    ['campo' => 'Fecha de fallecimiento', 'declarado' => '15/01/2026', 'esperado' => '15/01/2026', 'match' => true],
    ['campo' => 'Último domicilio', 'declarado' => 'Caracas, Dtto. Capital', 'esperado' => 'Valencia, Edo. Carabobo', 'match' => false],
    ['campo' => 'Estado civil', 'declarado' => 'Casado', 'esperado' => 'Casado', 'match' => true],
    ['campo' => 'Tipo de sucesión', 'declarado' => 'Ab intestato', 'esperado' => 'Testamentaria', 'match' => false],
];

$herederos = [
    ['campo' => 'Heredero 1 — María González', 'declarado' => 'Cónyuge — 50%', 'esperado' => 'Cónyuge — 50%', 'match' => true],
    ['campo' => 'Heredero 2 — Pedro González', 'declarado' => 'Hijo — 25%', 'esperado' => 'Hijo — 25%', 'match' => true],
    ['campo' => 'Heredero 3 — Ana González', 'declarado' => 'Hija — 25%', 'esperado' => 'Hija — 25%', 'match' => true],
    ['campo' => 'Heredero 4 — Luis González', 'declarado' => '—', 'esperado' => 'Hijo — Premuerto', 'match' => false],
];

$inventario = [
    ['campo' => 'Inmueble — Apartamento Los Palos Grandes', 'declarado' => 'Bs. 250.000,00', 'esperado' => 'Bs. 250.000,00', 'match' => true],
    ['campo' => 'Inmueble — Casa Valencia', 'declarado' => 'Bs. 180.000,00', 'esperado' => 'Bs. 180.000,00', 'match' => true],
    ['campo' => 'Vehículo — Toyota Corolla 2020', 'declarado' => 'Bs. 35.000,00', 'esperado' => 'Bs. 45.000,00', 'match' => false],
    ['campo' => 'Cuenta bancaria — Banesco', 'declarado' => 'Bs. 12.500,00', 'esperado' => 'Bs. 12.500,00', 'match' => true],
];

$calculo = [
    ['campo' => 'Patrimonio bruto', 'declarado' => 'Bs. 477.500,00', 'esperado' => 'Bs. 487.500,00', 'match' => false],
    ['campo' => 'Pasivos deducibles', 'declarado' => 'Bs. 15.000,00', 'esperado' => 'Bs. 15.000,00', 'match' => true],
    ['campo' => 'Patrimonio neto', 'declarado' => 'Bs. 462.500,00', 'esperado' => 'Bs. 472.500,00', 'match' => false],
    ['campo' => 'Base imponible', 'declarado' => 'Bs. 462.500,00', 'esperado' => 'Bs. 472.500,00', 'match' => false],
    ['campo' => 'Alícuota aplicada', 'declarado' => '25%', 'esperado' => '25%', 'match' => true],
    ['campo' => 'Impuesto determinado', 'declarado' => 'Bs. 115.625,00', 'esperado' => 'Bs. 118.125,00', 'match' => false],
];

// Section stats: correct / total
$sections = [
    ['title' => 'Datos del Causante', 'data' => $causante, 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>', 'max_pts' => 5],
    ['title' => 'Herederos', 'data' => $herederos, 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>', 'max_pts' => 5],
    ['title' => 'Inventario Patrimonial', 'data' => $inventario, 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>', 'max_pts' => 5],
    ['title' => 'Cálculo del Impuesto', 'data' => $calculo, 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="4" y="2" width="16" height="20" rx="2"/><line x1="8" y1="6" x2="16" y2="6"/><line x1="8" y1="10" x2="16" y2="10"/><line x1="8" y1="14" x2="12" y2="14"/></svg>', 'max_pts' => 5],
];

$statusClass = match ($intento['estado']) {
    'Enviado' => 'status-enviado',
    'Calificado' => 'status-calificado',
    'En Progreso' => 'status-progreso',
    default => 'status-enviado',
};

$isCalificado = $intento['estado'] === 'Calificado';

ob_start();
?>

<!-- Breadcrumb -->
<div class="breadcrumb">
    <a href="<?= base_url('/entregas') ?>">Entregas</a>
    <span class="breadcrumb-sep">›</span>
    <span class="breadcrumb-current">Intento #
        <?= $intento['intento_num'] ?> —
        <?= htmlspecialchars($intento['estudiante']) ?>
    </span>
</div>

<!-- Intento Header -->
<div class="intento-header animate-in">
    <div class="intento-header-left">
        <h2>
            <?= htmlspecialchars($intento['caso_titulo']) ?>
        </h2>
        <p>
            <?= htmlspecialchars($intento['estudiante']) ?>
        </p>
        <div class="intento-meta">
            <span class="intento-meta-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                    <circle cx="9" cy="7" r="4" />
                </svg>
                Sección: <strong>
                    <?= htmlspecialchars($intento['seccion']) ?>
                </strong>
            </span>
            <span class="intento-meta-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2" />
                    <rect x="8" y="2" width="8" height="4" rx="1" ry="1" />
                </svg>
                <?= htmlspecialchars($intento['asignacion']) ?>
            </span>
            <span class="intento-meta-item">
                Intento <strong>#
                    <?= $intento['intento_num'] ?> de
                    <?= $intento['intento_max'] ?>
                </strong>
            </span>
            <span class="intento-meta-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                    <line x1="16" y1="2" x2="16" y2="6" />
                    <line x1="8" y1="2" x2="8" y2="6" />
                    <line x1="3" y1="10" x2="21" y2="10" />
                </svg>
                Enviado: <strong>
                    <?= date('d/m/Y H:i', strtotime($intento['fecha_envio'])) ?>
                </strong>
            </span>
        </div>
    </div>
    <span class="intento-status-big <?= $statusClass ?>">
        <?= htmlspecialchars($intento['estado']) ?>
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
            $isOpen = $i === 0; // first accordion starts open
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
                                    <th>Declarado por el estudiante</th>
                                    <th>Valor esperado</th>
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

    <!-- ═══ SIDEBAR (Grading Panel) ═══ -->
    <div class="correction-sidebar">
        <!-- Grade Total -->
        <div class="sidebar-panel">
            <div class="sidebar-panel-body">
                <div class="grade-total">
                    <?php if ($isCalificado): ?>
                        <span class="grade-total-value <?= $intento['nota'] >= 10 ? 'pass' : 'fail' ?>">
                            <?= number_format($intento['nota'], 1) ?>
                        </span>
                    <?php else: ?>
                        <span class="grade-total-value pending">—</span>
                    <?php endif; ?>
                    <span class="grade-total-max"> / 20</span>
                    <span class="grade-total-label">Nota Total</span>
                </div>

                <!-- Partial Grades -->
                <div class="grade-partials">
                    <?php foreach ($sections as $i => $section): ?>
                        <div class="grade-partial-row">
                            <span class="grade-partial-label">
                                <?= htmlspecialchars($section['title']) ?>
                            </span>
                            <input type="number" class="grade-input" min="0" max="<?= $section['max_pts'] ?>" step="0.5"
                                placeholder="—" value="" id="grade-<?= $i ?>" <?= $isCalificado ? 'readonly' : '' ?>>
                            <span class="grade-partial-max">/
                                <?= $section['max_pts'] ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Observations -->
        <div class="sidebar-panel">
            <div class="sidebar-panel-header">
                <h3>Observaciones</h3>
            </div>
            <div class="sidebar-panel-body">
                <div class="observations-field">
                    <textarea class="observations-textarea" id="observaciones"
                        placeholder="Comentarios para el estudiante..." <?= $isCalificado ? 'readonly' : '' ?>><?= $isCalificado ? htmlspecialchars($intento['observaciones'] ?? '') : '' ?></textarea>
                </div>

                <?php if ($isCalificado && $intento['fecha_evaluacion']): ?>
                    <div class="calificado-info" style="margin-top: 12px;">
                        Calificado el <strong>
                            <?= date('d/m/Y H:i', strtotime($intento['fecha_evaluacion'])) ?>
                        </strong>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="sidebar-panel">
            <div class="sidebar-actions">
                <?php if (!$isCalificado): ?>
                    <button class="btn btn-outline" onclick="alert('Borrador guardado (placeholder)')">
                        Guardar borrador
                    </button>
                    <button class="btn btn-primary" onclick="alert('Intento calificado (placeholder)')">
                        Calificar y enviar
                    </button>
                <?php else: ?>
                    <button class="btn btn-outline" onclick="alert('Modo edición activado (placeholder)')">
                        Editar calificación
                    </button>
                <?php endif; ?>
                <a href="<?= base_url('/entregas') ?>" class="btn btn-secondary"
                    style="text-decoration: none; text-align: center;">
                    Volver a Entregas
                </a>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript: Accordion toggle -->
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