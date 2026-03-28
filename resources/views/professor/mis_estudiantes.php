<?php
declare(strict_types=1);

// ARCHIVO: resources/views/professor/mis_estudiantes.php

$pageTitle = 'Mis Estudiantes — Simulador SENIAT';
$activePage = 'mis-estudiantes';
$extraCss  = '<link rel="stylesheet" href="' . asset('css/shared/data-table.css') . '">';
$extraCss .= '<link rel="stylesheet" href="' . asset('css/professor/mis_estudiantes.css') . '">';
$extraJs   = '<script src="' . asset('js/global/data_table_core.js') . '"></script>';

// Datos del controller
$estudiantes = $estudiantes ?? [];
$stats       = $stats ?? ['total' => 0, 'pendientes' => 0, 'sin_actividad' => 0];

$avatarColors = ['avatar--blue', 'avatar--green', 'avatar--amber', 'avatar--purple', 'avatar--red'];

function getInitialsEst(string $name): string
{
    preg_match_all('/\b\w/u', $name, $m);
    return mb_strtoupper(implode('', array_slice($m[0], 0, 2)));
}

function timeAgo(?string $datetime): string
{
    if (!$datetime) return 'Sin registro';
    $diff = time() - strtotime($datetime);
    if ($diff < 60)     return 'Hace un momento';
    if ($diff < 3600)   return 'Hace ' . floor($diff / 60) . ' min';
    if ($diff < 86400)  return 'Hace ' . floor($diff / 3600) . 'h';
    if ($diff < 172800) return 'Ayer';
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
        <div class="stat-value"><?= $stats['total'] ?></div>
    </div>

    <div class="stat-card stat-card--vertical animate-in">
        <div class="stat-card-top">
            <span class="stat-label">Con Pendientes</span>
            <div class="stat-icon amber">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <circle cx="12" cy="12" r="10" />
                    <polyline points="12 6 12 12 16 14" />
                </svg>
            </div>
        </div>
        <div class="stat-value"><?= $stats['pendientes'] ?></div>
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
        <div class="stat-value"><?= $stats['sin_actividad'] ?></div>
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
            <input type="text" data-search-for="estudiantes-table" placeholder="Buscar estudiante...">
        </div>
    </div>
    <div class="toolbar-right">
        <select data-perpage-for="estudiantes-table" class="per-page-select">
            <option value="10">10</option>
            <option value="15" selected>15</option>
            <option value="25">25</option>
            <option value="50">50</option>
        </select>
    </div>
</div>

<!-- Data Table -->
<div class="table-container animate-in">
    <table class="data-table" id="estudiantes-table">
        <thead>
            <tr>
                <th class="sortable" data-col="0">Estudiante</th>
                <th class="sortable" data-col="1">Sección</th>
                <th class="sortable" data-col="2" data-type="number">N° Asignaciones</th>
                <th class="sortable" data-col="3" data-type="number">Completadas</th>
                <th class="sortable" data-col="4" data-type="number">Pendientes</th>
                <th class="sortable" data-col="5">Último Acceso</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($estudiantes)): ?>
                <?php foreach ($estudiantes as $est):
                    $fullName = trim(($est['nombres'] ?? '') . ' ' . ($est['apellidos'] ?? ''));
                    $iniciales = getInitialsEst($fullName);
                    $avatarClass = $avatarColors[abs(crc32($iniciales)) % count($avatarColors)];
                    $cedula = ($est['nacionalidad'] ?? 'V') . '-' . number_format((float) ($est['cedula'] ?? 0), 0, ',', '.');

                    $asignaciones = (int) ($est['asignaciones'] ?? 0);
                    $completadas  = (int) ($est['asignaciones_completadas'] ?? 0);
                    $pendientes   = (int) ($est['asignaciones_pendientes'] ?? 0);

                    $pendientesClass = $pendientes > 0 ? 'has-pending' : '';
                    $accesoText = timeAgo($est['ultimo_acceso'] ?? null);
                    $searchText = mb_strtolower($fullName . ' ' . $cedula . ' ' . ($est['seccion'] ?? ''));
                ?>
                    <tr data-search="<?= htmlspecialchars($searchText) ?>">
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

                        <td><?= htmlspecialchars($est['seccion'] ?? '—') ?></td>
                        <td><?= $asignaciones ?></td>
                        <td>
                            <span class="completadas-count"><?= $completadas ?></span>
                        </td>
                        <td>
                            <span class="pendientes-count <?= $pendientesClass ?>">
                                <?= $pendientes ?>
                            </span>
                        </td>
                        <td>
                            <span class="last-activity"><?= $accesoText ?></span>
                        </td>
                        <td>
                            <a href="<?= base_url('/mis-estudiantes/' . $est['id']) ?>" class="ver-link">
                                Ver perfil
                                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
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

    <!-- Table Footer (managed by DataTableManager) -->
    <div class="table-footer" data-footer-for="estudiantes-table">
        <span class="table-footer-info"></span>
        <div class="pagination"></div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/logged_layout.php';
?>