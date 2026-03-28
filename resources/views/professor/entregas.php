<?php
declare(strict_types=1);

// ARCHIVO: resources/views/professor/entregas.php

// 1. Configuración de la Vista
$pageTitle = 'Entregas — Simulador SENIAT';
$activePage = 'entregas';

// 2. CSS específico
$extraCss = '<link rel="stylesheet" href="' . asset('css/professor/entregas.css') . '">';

// $entregas y $stats vienen del route (EntregasModel)

// ── Helpers ────────────────────────────────────────────────
function mapEstadoEntrega(string $dbEstado): string
{
    return match ($dbEstado) {
        'Enviado' => 'Enviado',
        'Aprobado' => 'Aprobado',
        'Rechazado' => 'No Aprobado',
        'En_Progreso' => 'En Progreso',
        default => ucfirst(str_replace('_', ' ', $dbEstado)),
    };
}

function getStatusClassEntrega(string $label): string
{
    return match ($label) {
        'Enviado' => 'status-info',
        'Aprobado' => 'status-active',
        'No Aprobado' => 'status-danger',
        'En Progreso' => 'status-warning',
        default => 'status-draft',
    };
}

ob_start();
?>

<!-- Page Header -->
<div class="page-header">
    <div class="page-header-left">
        <h1>Entregas</h1>
        <p>Revisión de intentos de tus estudiantes</p>
    </div>
</div>

<!-- Stats Row -->
<div class="stats-row">
    <div class="stat-card stat-card--vertical animate-in">
        <div class="stat-card-top">
            <span class="stat-label">Pendientes de Revisión</span>
            <div class="stat-icon amber">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <circle cx="12" cy="12" r="10" />
                    <polyline points="12 6 12 12 16 14" />
                </svg>
            </div>
        </div>
        <div class="stat-value" id="stat-pendientes">
            <?= $stats['pendientes'] ?? 0 ?>
        </div>
    </div>

    <div class="stat-card stat-card--vertical animate-in">
        <div class="stat-card-top">
            <span class="stat-label">Calificadas</span>
            <div class="stat-icon green">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                    <polyline points="22 4 12 14.01 9 11.01" />
                </svg>
            </div>
        </div>
        <div class="stat-value" id="stat-calificadas">
            <?= $stats['calificadas'] ?? 0 ?>
        </div>
    </div>

    <div class="stat-card stat-card--vertical animate-in">
        <div class="stat-card-top">
            <span class="stat-label">Total Entregas</span>
            <div class="stat-icon purple">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2" />
                    <rect x="8" y="2" width="8" height="4" rx="1" ry="1" />
                    <path d="M9 14l2 2 4-4" />
                </svg>
            </div>
        </div>
        <div class="stat-value" id="stat-total">
            <?= $stats['total'] ?? 0 ?>
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
            <input type="text" data-search-for="tbl-entregas-prof" placeholder="Buscar estudiante o caso...">
        </div>

        <select class="toolbar-select" id="filter-estado-prof">
            <option value="">Todos los estados</option>
            <option value="Enviado">Enviado</option>
            <option value="Aprobado">Aprobado</option>
            <option value="No Aprobado">No Aprobado</option>
        </select>

        <select class="toolbar-select" id="filter-caso-prof">
            <option value="">Todos los casos</option>
            <?php
            $casosUnicos = array_unique(array_column($entregas, 'caso_titulo'));
            foreach ($casosUnicos as $caso): ?>
                <option value="<?= htmlspecialchars($caso) ?>"><?= htmlspecialchars($caso) ?></option>
            <?php endforeach; ?>
        </select>

        <select class="toolbar-select" id="filter-seccion-prof">
            <option value="">Todas las secciones</option>
            <?php
            $seccionesUnicas = array_unique(array_filter(array_column($entregas, 'seccion')));
            sort($seccionesUnicas);
            foreach ($seccionesUnicas as $sec): ?>
                <option value="<?= htmlspecialchars($sec) ?>"><?= htmlspecialchars($sec) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="toolbar-right">
        Mostrar <select data-perpage-for="tbl-entregas-prof" class="per-page-select"><option value="10" selected>10</option><option value="25">25</option><option value="50">50</option></select> filas
    </div>
</div>

<!-- Data Table -->
<div class="table-container animate-in">
    <table class="data-table" id="tbl-entregas-prof" data-per-page="10">
        <thead>
            <tr>
                <th class="sortable" data-col="0">Estudiante</th>
                <th class="sortable" data-col="1">Sección</th>
                <th class="sortable" data-col="2">Caso</th>
                <th class="sortable" data-col="3">Intento</th>
                <th class="sortable" data-col="4">Fecha Envío</th>
                <th>Estado</th>
                <th>Nota</th>
                <th>Observación</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($entregas as $entrega):
                $nombres = $entrega['estudiante_nombres'] ?? 'Sin nombre';
                $apellidos = $entrega['estudiante_apellidos'] ?? '';
                $fullName = trim("{$nombres} {$apellidos}");

                // Iniciales para avatar
                preg_match_all('/\b\w/u', $fullName, $matches);
                $iniciales = mb_strtoupper(implode('', array_slice($matches[0], 0, 2)));

                $cedula = $entrega['estudiante_cedula']
                    ? ($entrega['estudiante_nacionalidad'] ?? 'V') . '-' . number_format((float) $entrega['estudiante_cedula'], 0, ',', '.')
                    : 'S/C';

                // Color avatar
                $avatarColors = ['avatar--blue', 'avatar--green', 'avatar--amber', 'avatar--purple', 'avatar--red'];
                $colorIdx = crc32($iniciales) % count($avatarColors);
                $avatarClass = $avatarColors[abs($colorIdx)];

                $estadoLabel = mapEstadoEntrega($entrega['estado']);
                $statusClass = getStatusClassEntrega($estadoLabel);

                $fechaEnvio = $entrega['fecha_envio'] ? date('d/m/Y H:i', strtotime($entrega['fecha_envio'])) : '—';

                $intentoActual = $entrega['intento_actual'] ?? 1;
                $intentoMax = $entrega['intento_max'] ?? 0;

                $searchStr = strtolower(
                    $fullName . ' ' . $cedula . ' ' .
                    ($entrega['caso_titulo'] ?? '') . ' ' .
                    ($entrega['seccion'] ?? '') . ' ' .
                    $estadoLabel
                );
            ?>
                <tr data-search="<?= htmlspecialchars($searchStr) ?>"
                    data-estado="<?= htmlspecialchars($estadoLabel) ?>"
                    data-caso="<?= htmlspecialchars($entrega['caso_titulo'] ?? '') ?>"
                    data-seccion="<?= htmlspecialchars($entrega['seccion'] ?? '') ?>">
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
                    <td><?= htmlspecialchars($entrega['seccion'] ?? '—') ?></td>
                    <td><span class="caso-tag"><?= htmlspecialchars($entrega['caso_titulo'] ?? '—') ?></span></td>
                    <td>
                        <span class="intento-display">
                            <?= $intentoActual ?><?= $intentoMax > 0 ? " de {$intentoMax}" : '' ?>
                        </span>
                    </td>
                    <td><?= $fechaEnvio ?></td>
                    <td>
                        <span class="status-badge <?= $statusClass ?>">
                            <?= htmlspecialchars($estadoLabel) ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($estadoLabel === 'Enviado'): ?>
                            <span style="color: var(--amber-500); font-size: var(--text-xs); font-weight: 500;">Por calificar</span>
                        <?php elseif ($estadoLabel === 'En Progreso'): ?>
                            <span style="color: var(--gray-400); font-size: var(--text-xs);">En progreso</span>
                        <?php else: ?>
                            <span style="color: var(--gray-400);">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span style="color: var(--gray-400);">—</span>
                    </td>
                    <td>
                        <a href="<?= base_url('/entregas/' . $entrega['intento_id']) ?>" class="ver-link">
                            Ver
                            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor"
                                stroke-width="2.5" stroke-linecap="round">
                                <path d="M5 12h14" />
                                <polyline points="12 5 19 12 12 19" />
                            </svg>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Table Footer -->
<div class="table-footer" data-footer-for="tbl-entregas-prof">
    <div class="table-footer-info"></div>
    <div class="pagination"></div>
</div>

<!-- Filter JS -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    var filterEstado = document.getElementById('filter-estado-prof');
    var filterCaso = document.getElementById('filter-caso-prof');
    var filterSeccion = document.getElementById('filter-seccion-prof');

    function applyFilters() {
        var estado = filterEstado ? filterEstado.value : '';
        var caso = filterCaso ? filterCaso.value : '';
        var seccion = filterSeccion ? filterSeccion.value : '';

        window.DataTableManager.setClientFilter('tbl-entregas-prof',
            (estado || caso || seccion)
                ? function(row) {
                    if (estado && row.dataset.estado !== estado) return false;
                    if (caso && row.dataset.caso !== caso) return false;
                    if (seccion && row.dataset.seccion !== seccion) return false;
                    return true;
                }
                : null
        );
    }

    if (filterEstado) filterEstado.addEventListener('change', applyFilters);
    if (filterCaso) filterCaso.addEventListener('change', applyFilters);
    if (filterSeccion) filterSeccion.addEventListener('change', applyFilters);
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/logged_layout.php';
?>