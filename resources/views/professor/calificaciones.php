<?php
declare(strict_types=1);

// ARCHIVO: resources/views/professor/calificaciones.php
// Vista de calificaciones: tabla cruzada Estudiantes × Casos

$pageTitle = 'Calificaciones — Simulador SENIAT';
$activePage = 'calificaciones';
$extraCss = '<link rel="stylesheet" href="' . asset('css/professor/calificaciones.css') . '">';

// ── Datos Placeholder ──────────────────────────────────────
$secciones = ['4to A', '4to B'];
$seccionSeleccionada = '4to A'; // Simulates pre-selected

$casos = [
    ['id' => 1, 'titulo' => 'Sucesión González Méndez'],
    ['id' => 2, 'titulo' => 'Sucesión Pérez Alvarado'],
    ['id' => 3, 'titulo' => 'Sucesión Ramírez Torres'],
];

// Students with grades per caso (best attempt)
$calificaciones = [
    [
        'id' => 1,
        'nombres' => 'Ana María',
        'apellidos' => 'Martínez López',
        'cedula' => '28456789',
        'nacionalidad' => 'V',
        'notas' => [1 => 14.5, 2 => 18.0, 3 => null],  // caso_id => nota
    ],
    [
        'id' => 2,
        'nombres' => 'Pedro José',
        'apellidos' => 'López Ramírez',
        'cedula' => '27123456',
        'nacionalidad' => 'V',
        'notas' => [1 => 17.0, 2 => 15.5, 3 => 12.0],
    ],
    [
        'id' => 5,
        'nombres' => 'Valentina',
        'apellidos' => 'Rodríguez Salas',
        'cedula' => '28654321',
        'nacionalidad' => 'V',
        'notas' => [1 => null, 2 => null, 3 => null],
    ],
    [
        'id' => 6,
        'nombres' => 'Luis Enrique',
        'apellidos' => 'Morales Quintero',
        'cedula' => '27998877',
        'nacionalidad' => 'V',
        'notas' => [1 => 12.0, 2 => 8.5, 3 => 'pendiente'],
    ],
];

// Helpers
$avatarColors = ['avatar--blue', 'avatar--green', 'avatar--amber', 'avatar--purple', 'avatar--red'];

ob_start();
?>

<!-- Page Header -->
<div class="page-header">
    <div class="page-header-left">
        <h1>Calificaciones</h1>
        <p>Sábana de notas por sección y caso</p>
    </div>
</div>

<!-- Toolbar -->
<div class="toolbar">
    <div class="toolbar-left">
        <select class="toolbar-select" id="select-seccion">
            <option value="">— Seleccionar sección —</option>
            <?php foreach ($secciones as $sec): ?>
                <option value="<?= htmlspecialchars($sec) ?>" <?= $sec === $seccionSeleccionada ? 'selected' : '' ?>>
                    <?= htmlspecialchars($sec) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select class="toolbar-select" id="select-caso">
            <option value="">Todos los casos</option>
            <?php foreach ($casos as $caso): ?>
                <option value="<?= $caso['id'] ?>">
                    <?= htmlspecialchars($caso['titulo']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="toolbar-right">
        <button class="export-btn" disabled title="Próximamente">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                <polyline points="7 10 12 15 17 10" />
                <line x1="12" y1="15" x2="12" y2="3" />
            </svg>
            Exportar
        </button>
    </div>
</div>

<!-- Grades Table -->
<div class="grades-wrapper animate-in" id="grades-container">
    <?php if (!$seccionSeleccionada): ?>
        <!-- Initial State: No section selected -->
        <div class="grades-initial-state">
            <div class="empty-state-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                    <polyline points="22 4 12 14.01 9 11.01" />
                </svg>
            </div>
            <h3>Selecciona una sección</h3>
            <p>Elige una sección del selector para ver las calificaciones de tus estudiantes.</p>
        </div>
    <?php else: ?>
        <table class="grades-table">
            <thead>
                <tr>
                    <th>Estudiante</th>
                    <?php foreach ($casos as $caso): ?>
                        <th>
                            <?= htmlspecialchars($caso['titulo']) ?>
                        </th>
                    <?php endforeach; ?>
                    <th>Promedio</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($calificaciones as $est):
                    $fullName = trim($est['nombres'] . ' ' . $est['apellidos']);
                    preg_match_all('/\b\w/u', $fullName, $m);
                    $iniciales = mb_strtoupper(implode('', array_slice($m[0], 0, 2)));
                    $avatarClass = $avatarColors[abs(crc32($iniciales)) % count($avatarColors)];
                    $cedula = ($est['nacionalidad'] ?? 'V') . '-' . number_format((float) $est['cedula'], 0, ',', '.');

                    // Calculate average from numeric grades only
                    $numericNotes = array_filter($est['notas'], fn($n) => is_numeric($n));
                    $promedio = count($numericNotes) > 0
                        ? array_sum($numericNotes) / count($numericNotes)
                        : null;
                    ?>
                    <tr>
                        <!-- Estudiante (sticky) -->
                        <td>
                            <div class="estudiante-cell">
                                <div class="estudiante-avatar <?= $avatarClass ?>">
                                    <?= htmlspecialchars($iniciales) ?>
                                </div>
                                <div class="estudiante-info">
                                    <div class="estudiante-name">
                                        <?= htmlspecialchars($fullName) ?>
                                    </div>
                                    <div class="estudiante-ci">
                                        <?= htmlspecialchars($cedula) ?>
                                    </div>
                                </div>
                            </div>
                        </td>

                        <!-- Grades per case -->
                        <?php foreach ($casos as $caso):
                            $nota = $est['notas'][$caso['id']] ?? null;
                            if ($nota === 'pendiente') {
                                $cellClass = 'grade-pending';
                                $cellText = 'Pendiente';
                            } elseif ($nota === null) {
                                $cellClass = 'grade-na';
                                $cellText = '—';
                            } elseif ($nota >= 10) {
                                $cellClass = 'grade-pass';
                                $cellText = number_format($nota, 1);
                            } else {
                                $cellClass = 'grade-fail';
                                $cellText = number_format($nota, 1);
                            }
                            ?>
                            <td>
                                <span class="grade-cell <?= $cellClass ?>">
                                    <?= $cellText ?>
                                </span>
                            </td>
                        <?php endforeach; ?>

                        <!-- Promedio (sticky right) -->
                        <td>
                            <?php if ($promedio !== null): ?>
                                <span class="promedio-final <?= $promedio >= 10 ? 'grade-pass' : 'grade-fail' ?>">
                                    <?= number_format($promedio, 1) ?>
                                </span>
                            <?php else: ?>
                                <span class="promedio-final grade-na">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/logged_layout.php';
?>