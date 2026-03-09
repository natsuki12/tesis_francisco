<?php
declare(strict_types=1);

// ARCHIVO: resources/views/professor/mis_estudiantes.php

$pageTitle = 'Mis Estudiantes — Simulador SENIAT';
$activePage = 'mis-estudiantes';
$extraCss = '<link rel="stylesheet" href="' . asset('css/professor/mis_estudiantes.css') . '">';

// ── Datos Placeholder ──────────────────────────────────────
$estudiantes = [
    [
        'id' => 1,
        'nombres' => 'Ana María',
        'apellidos' => 'Martínez López',
        'cedula' => '28456789',
        'nacionalidad' => 'V',
        'seccion' => '4to A',
        'intentos' => 4,
        'pendientes' => 2,
        'promedio' => 14.5,
        'ultima_actividad' => '2026-03-09 10:30:00',
    ],
    [
        'id' => 2,
        'nombres' => 'Pedro José',
        'apellidos' => 'López Ramírez',
        'cedula' => '27123456',
        'nacionalidad' => 'V',
        'seccion' => '4to A',
        'intentos' => 3,
        'pendientes' => 0,
        'promedio' => 17.0,
        'ultima_actividad' => '2026-03-08 15:45:00',
    ],
    [
        'id' => 3,
        'nombres' => 'María José',
        'apellidos' => 'García Herrera',
        'cedula' => '29876543',
        'nacionalidad' => 'V',
        'seccion' => '4to B',
        'intentos' => 1,
        'pendientes' => 1,
        'promedio' => null,
        'ultima_actividad' => '2026-03-08 09:15:00',
    ],
    [
        'id' => 4,
        'nombres' => 'Carlos Andrés',
        'apellidos' => 'Fernández Díaz',
        'cedula' => '30112233',
        'nacionalidad' => 'V',
        'seccion' => '4to B',
        'intentos' => 6,
        'pendientes' => 0,
        'promedio' => 8.5,
        'ultima_actividad' => '2026-03-07 18:00:00',
    ],
    [
        'id' => 5,
        'nombres' => 'Valentina',
        'apellidos' => 'Rodríguez Salas',
        'cedula' => '28654321',
        'nacionalidad' => 'V',
        'seccion' => '4to A',
        'intentos' => 0,
        'pendientes' => 0,
        'promedio' => null,
        'ultima_actividad' => null,
    ],
    [
        'id' => 6,
        'nombres' => 'Luis Enrique',
        'apellidos' => 'Morales Quintero',
        'cedula' => '27998877',
        'nacionalidad' => 'V',
        'seccion' => '4to A',
        'intentos' => 5,
        'pendientes' => 1,
        'promedio' => 12.0,
        'ultima_actividad' => '2026-03-06 14:50:00',
    ],
    [
        'id' => 7,
        'nombres' => 'Isabella',
        'apellidos' => 'Mendoza Torres',
        'cedula' => '29111222',
        'nacionalidad' => 'V',
        'seccion' => '4to B',
        'intentos' => 2,
        'pendientes' => 0,
        'promedio' => 19.0,
        'ultima_actividad' => '2026-03-05 10:00:00',
    ],
];

$stats = [
    'total' => count($estudiantes),
    'pendientes' => count(array_filter($estudiantes, fn($e) => $e['pendientes'] > 0)),
    'sin_actividad' => count(array_filter($estudiantes, fn($e) => $e['intentos'] === 0)),
];

$avatarColors = ['avatar--blue', 'avatar--green', 'avatar--amber', 'avatar--purple', 'avatar--red'];

function getInitialsEst(string $name): string
{
    preg_match_all('/\b\w/u', $name, $m);
    return mb_strtoupper(implode('', array_slice($m[0], 0, 2)));
}

function timeAgo(string $datetime): string
{
    $diff = time() - strtotime($datetime);
    if ($diff < 60)
        return 'Hace un momento';
    if ($diff < 3600)
        return 'Hace ' . floor($diff / 60) . ' min';
    if ($diff < 86400)
        return 'Hace ' . floor($diff / 3600) . 'h';
    if ($diff < 172800)
        return 'Ayer';
    return 'Hace ' . floor($diff / 86400) . ' días';
}

ob_start();
?>

<!-- Page Header -->
<div class="page-header">
    <div class="page-header-left">
        <h1>Mis Estudiantes</h1>
        <p>Listado de estudiantes en tus secciones</p>
    </div>
</div>

<!-- Stats Row -->
<div class="stats-row">
    <div class="stat-card stat-card--vertical animate-in">
        <div class="stat-card-top">
            <span class="stat-label">Total Estudiantes</span>
            <div class="stat-icon blue">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                    <circle cx="9" cy="7" r="4" />
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                    <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                </svg>
            </div>
        </div>
        <div class="stat-value">
            <?= $stats['total'] ?>
        </div>
    </div>

    <div class="stat-card stat-card--vertical animate-in">
        <div class="stat-card-top">
            <span class="stat-label">Con Entregas Pendientes</span>
            <div class="stat-icon amber">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <circle cx="12" cy="12" r="10" />
                    <polyline points="12 6 12 12 16 14" />
                </svg>
            </div>
        </div>
        <div class="stat-value">
            <?= $stats['pendientes'] ?>
        </div>
    </div>

    <div class="stat-card stat-card--vertical animate-in">
        <div class="stat-card-top">
            <span class="stat-label">Sin Actividad</span>
            <div class="stat-icon red">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <circle cx="12" cy="12" r="10" />
                    <line x1="15" y1="9" x2="9" y2="15" />
                    <line x1="9" y1="9" x2="15" y2="15" />
                </svg>
            </div>
        </div>
        <div class="stat-value">
            <?= $stats['sin_actividad'] ?>
        </div>
    </div>
</div>

<!-- Toolbar / Filters -->
<div class="toolbar">
    <div class="toolbar-left">
        <div class="search-box">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                <circle cx="11" cy="11" r="8" />
                <path d="m21 21-4.35-4.35" />
            </svg>
            <input type="text" id="search-estudiantes" placeholder="Buscar estudiante...">
        </div>

        <div class="filter-dropdown" id="filter-seccion">
            <button class="filter-btn">
                Sección
                <svg viewBox="0 0 24 24" width="14" height="14">
                    <path d="M7 10l5 5 5-5z" fill="currentColor" />
                </svg>
            </button>
        </div>

        <div class="filter-dropdown" id="filter-actividad">
            <button class="filter-btn">
                Actividad
                <svg viewBox="0 0 24 24" width="14" height="14">
                    <path d="M7 10l5 5 5-5z" fill="currentColor" />
                </svg>
            </button>
        </div>
    </div>
</div>

<!-- Data Table -->
<div class="table-container animate-in">
    <table class="data-table">
        <thead>
            <tr>
                <th class="sortable" data-sort="estudiante">Estudiante</th>
                <th class="sortable" data-sort="seccion">Sección</th>
                <th class="sortable" data-sort="intentos">Intentos</th>
                <th class="sortable" data-sort="pendientes">Pendientes</th>
                <th class="sortable" data-sort="promedio">Promedio</th>
                <th class="sortable" data-sort="actividad">Última Actividad</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody id="estudiantes-tbody">
            <?php if (empty($estudiantes)): ?>
                <tr>
                    <td colspan="7" class="empty-cell">
                        <div class="empty-state empty-state--blue">
                            <div class="empty-state-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                                    <circle cx="9" cy="7" r="4" />
                                </svg>
                            </div>
                            <h3>Sin estudiantes asignados</h3>
                            <p>Cuando se asignen estudiantes a tus secciones, aparecerán aquí.</p>
                        </div>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($estudiantes as $est):
                    $fullName = trim($est['nombres'] . ' ' . $est['apellidos']);
                    $iniciales = getInitialsEst($fullName);
                    $avatarClass = $avatarColors[abs(crc32($iniciales)) % count($avatarColors)];
                    $cedula = ($est['nacionalidad'] ?? 'V') . '-' . number_format((float) $est['cedula'], 0, ',', '.');

                    $promedioClass = 'promedio-na';
                    $promedioText = '—';
                    if ($est['promedio'] !== null) {
                        $promedioText = number_format($est['promedio'], 1);
                        $promedioClass = $est['promedio'] >= 10 ? 'promedio-pass' : 'promedio-fail';
                    }

                    $pendientesClass = $est['pendientes'] > 0 ? 'has-pending' : '';
                    $actividadText = $est['ultima_actividad'] ? timeAgo($est['ultima_actividad']) : 'Sin actividad';
                    ?>
                    <tr data-estudiante="<?= htmlspecialchars(mb_strtolower($fullName)) ?>"
                        data-seccion="<?= htmlspecialchars($est['seccion']) ?>">

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

                        <td>
                            <?= htmlspecialchars($est['seccion']) ?>
                        </td>

                        <td>
                            <?= $est['intentos'] ?>
                        </td>

                        <td>
                            <span class="pendientes-count <?= $pendientesClass ?>">
                                <?= $est['pendientes'] ?>
                            </span>
                        </td>

                        <td>
                            <span class="promedio-cell <?= $promedioClass ?>">
                                <?= $promedioText ?>
                            </span>
                        </td>

                        <td>
                            <span class="last-activity">
                                <?= $actividadText ?>
                            </span>
                        </td>

                        <td>
                            <a href="<?= base_url('/mis-estudiantes/' . $est['id']) ?>" class="ver-link">
                                Ver perfil
                                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor"
                                    stroke-width="2.5" stroke-linecap="round">
                                    <path d="M5 12h14" />
                                    <polyline points="12 5 19 12 12 19" />
                                </svg>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Table Footer -->
    <div class="table-footer">
        <div class="table-footer-info">
            Mostrando <strong>
                <?= count($estudiantes) ?>
            </strong> de <strong>
                <?= $stats['total'] ?>
            </strong> estudiantes
        </div>
        <div class="pagination">
            <button disabled>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                    <polyline points="15 18 9 12 15 6" />
                </svg>
            </button>
            <button class="active">1</button>
            <button>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                    <polyline points="9 18 15 12 9 6" />
                </svg>
            </button>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/logged_layout.php';
?>