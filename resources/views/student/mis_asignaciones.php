<?php
declare(strict_types=1);

// ARCHIVO: resources/views/student/mis_asignaciones.php

$pageTitle = 'Mis Asignaciones — Simulador SENIAT';
$activePage = 'mis-asignaciones';
$extraCss = '<link rel="stylesheet" href="' . asset('css/shared/data-table.css') . '">
<link rel="stylesheet" href="' . asset('css/student/mis_asignaciones.css') . '">';

// $asignaciones viene del controller (StudentAssignmentModel::getAsignaciones)

// ── Helpers ────────────────────────────────────────────────
function getModeLabel(string $dbMode): string
{
    return match ($dbMode) {
        'Practica_Libre' => 'Práctica Libre',
        'Practica_guiada' => 'Práctica Guiada',
        'Evaluacion' => 'Evaluación',
        default => $dbMode,
    };
}

function getModeClassSt(string $dbMode): string
{
    return match ($dbMode) {
        'Practica_Libre' => 'mode-libre',
        'Practica_guiada' => 'mode-guiado',
        'Evaluacion' => 'mode-evaluacion',
        default => 'mode-guiado',
    };
}

function mapEstadoLabel(array $asig): string
{
    $estado = $asig['asignacion_estado'] ?? 'Pendiente';
    $intentos = (int) ($asig['intentos_usados'] ?? 0);
    $borrador = (int) ($asig['tiene_borrador'] ?? 0);
    $fechaLimite = $asig['fecha_limite'] ?? null;

    // Vencida = fecha pasó + nunca envió ningún intento
    if ($fechaLimite && strtotime($fechaLimite) < time() && $estado !== 'Completado' && $intentos === 0) {
        return 'Vencida';
    }

    return match ($estado) {
        'Pendiente' => $intentos === 0
            ? ($borrador ? 'En progreso' : 'Sin iniciar')
            : ($borrador ? 'En progreso' : 'Enviado'),
        'En_Progreso' => $borrador ? 'En progreso' : 'Enviado',
        'Completado' => 'Calificada',
        default => ucfirst(str_replace('_', ' ', $estado)),
    };
}

function getStatusClassSt(string $label): string
{
    return match ($label) {
        'Sin iniciar' => 'status-draft',
        'En progreso' => 'status-warning',
        'Enviado' => 'status-info',
        'Calificada' => 'status-active',
        'Vencida' => 'status-danger',
        default => 'status-draft',
    };
}

ob_start();
?>

<!-- Page Header -->
<div class="page-header">
    <div class="page-header-left">
        <h1>Mis Asignaciones</h1>
        <p>Casos asignados por tu profesor para practicar</p>
    </div>
</div>

<!-- Toolbar -->
<div class="toolbar">
    <div class="toolbar-left">
        <div class="search-box">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                <circle cx="11" cy="11" r="8" />
                <path d="m21 21-4.35-4.35" />
            </svg>
            <input type="text" data-search-for="tbl-asignaciones" placeholder="Buscar caso, profesor o sección...">
        </div>

        <select class="filter-select" id="filter-modalidad">
            <option value="">Todas las modalidades</option>
            <option value="Práctica Libre">Práctica Libre</option>
            <option value="Práctica Guiada">Práctica Guiada</option>
            <option value="Evaluación">Evaluación</option>
        </select>

        <select class="filter-select" id="filter-estado">
            <option value="">Todos los estados</option>
            <option value="Sin iniciar">Sin iniciar</option>
            <option value="En progreso">En progreso</option>
            <option value="Enviado">Enviado</option>
            <option value="Calificada">Calificada</option>
            <option value="Vencida">Vencida</option>
        </select>
    </div>
    <div class="toolbar-right">
        <label style="font-size:var(--text-xs); color:var(--gray-500); display:flex; align-items:center; gap:6px;">
            Mostrar <select data-perpage-for="tbl-asignaciones" class="per-page-select"><option value="10" selected>10</option><option value="15">15</option><option value="25">25</option></select> filas
        </label>
    </div>
</div>

<!-- Data Table -->
<div class="table-container">
    <table class="data-table" id="tbl-asignaciones">
        <thead>
            <tr>
                <th class="sortable" data-col="0" style="width:22%">Caso</th>
                <th class="sortable" data-col="1" style="width:16%">Profesor</th>
                <th class="sortable" data-col="2" style="width:10%">Sección</th>
                <th class="sortable" data-col="3" style="width:12%">Modalidad</th>
                <th class="sortable" data-col="4" style="width:9%">Intentos</th>
                <th class="sortable" data-col="5" style="width:13%">Fecha Límite</th>
                <th class="sortable" data-col="6" style="width:10%">Estado</th>
                <th style="width:8%">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($asignaciones)): ?>
                <tr class="empty-row"><td colspan="8" style="text-align:center; padding:40px; color:var(--gray-400);">No tienes asignaciones pendientes. Cuando tu profesor te asigne casos, aparecerán aquí.</td></tr>
            <?php else: ?>
                <?php foreach ($asignaciones as $asig):
                    $modeLabel = getModeLabel($asig['modalidad']);
                    $modeClass = getModeClassSt($asig['modalidad']);
                    $estadoLabel = mapEstadoLabel($asig);
                    $statusClass = getStatusClassSt($estadoLabel);
                    $maxIntentos = (int) $asig['max_intentos'];
                    $intentosUsados = (int) $asig['intentos_usados'];
                    $tieneBorrador = (bool) $asig['tiene_borrador'];
                    $fechaLimite = $asig['fecha_limite'] ?? null;
                    $profesorNombre = $asig['profesor_nombre'] ?? 'Profesor';
                    $seccionNombre = $asig['seccion_nombre'] ?? '—';
                    $maxDisplay = $maxIntentos === 0 ? '∞' : (string) $maxIntentos;

                    // Deadline urgency
                    $dlClass = '';
                    $dlText = '—';
                    if ($fechaLimite) {
                        $deadlineTs = strtotime($fechaLimite);
                        // Comparar contra inicio del día actual para evitar inconsistencias por hora
                        $todayTs = strtotime('today');
                        $daysLeft = (int) (($deadlineTs - $todayTs) / 86400);
                        $dlText = date('d/m/Y', $deadlineTs);
                        if ($estadoLabel === 'Vencida' || $daysLeft < 0) {
                            $dlClass = 'dl-expired';
                        } elseif ($daysLeft <= 3) {
                            $dlClass = 'dl-urgent';
                        } elseif ($daysLeft <= 7) {
                            $dlClass = 'dl-soon';
                        }
                    }

                    // Fecha expirada (para botones y color)
                    $fechaExpirada = $fechaLimite && strtotime($fechaLimite) < time();

                    // Action Button — si fecha pasó, siempre "Ver"
                    if ($fechaExpirada && $estadoLabel !== 'Calificada') {
                        $btnText = 'Ver ›';
                        $isAction = false;
                    } elseif ($estadoLabel === 'Sin iniciar') {
                        $btnText = 'Comenzar ›';
                        $isAction = true;
                    } elseif ($tieneBorrador) {
                        $btnText = 'Continuar ›';
                        $isAction = true;
                    } elseif ($estadoLabel === 'Calificada') {
                        $btnText = 'Ver ›';
                        $isAction = false;
                    } elseif (($maxIntentos === 0 || $intentosUsados < $maxIntentos) && $intentosUsados > 0) {
                        $btnText = 'Reintentar ›';
                        $isAction = true;
                    } else {
                        $btnText = 'Ver ›';
                        $isAction = false;
                    }

                    $searchText = mb_strtolower($asig['caso_titulo'] . ' ' . $profesorNombre . ' ' . $seccionNombre . ' ' . $modeLabel . ' ' . $estadoLabel);
                ?>
                    <tr data-search="<?= htmlspecialchars($searchText) ?>" data-href="<?= base_url('/mis-asignaciones/' . $asig['asignacion_id']) ?>" data-modalidad="<?= htmlspecialchars($modeLabel) ?>" data-estado="<?= htmlspecialchars($estadoLabel) ?>" class="row-clickable">
                        <td>
                            <strong class="td-caso-titulo"><?= htmlspecialchars($asig['caso_titulo']) ?></strong>
                        </td>
                        <td><?= htmlspecialchars($profesorNombre) ?></td>
                        <td><?= htmlspecialchars($seccionNombre) ?></td>
                        <td><span class="mode-badge <?= $modeClass ?>"><?= htmlspecialchars($modeLabel) ?></span></td>
                        <td>
                            <strong><?= $intentosUsados ?></strong>
                            <span style="color:var(--gray-400)">de <?= $maxDisplay ?></span>
                        </td>
                        <td>
                            <span class="<?= $dlClass ?>"><?= $dlText ?></span>
                        </td>
                        <td><span class="status-badge <?= $statusClass ?>"><?= htmlspecialchars($estadoLabel) ?></span></td>
                        <td>
                            <?php if ($isAction): ?>
                                <form method="POST" action="<?= base_url('/api/intentos/iniciar') ?>" style="display:inline;"
                                    onclick="event.stopPropagation();">
                                    <input type="hidden" name="asignacion_id" value="<?= $asig['asignacion_id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-action"><?= $btnText ?></button>
                                </form>
                            <?php else: ?>
                                <a href="<?= base_url('/mis-asignaciones/' . $asig['asignacion_id']) ?>"
                                   class="btn btn-sm btn-action"
                                   onclick="event.stopPropagation();"><?= $btnText ?></a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    <div class="table-footer" data-footer-for="tbl-asignaciones">
        <div class="table-footer-info"></div>
        <div class="pagination"></div>
    </div>
</div>

<script>
// Clickable rows → navigate to assignment detail
document.querySelectorAll('.row-clickable').forEach(row => {
    row.addEventListener('click', () => {
        const href = row.dataset.href;
        if (href) window.location.href = href;
    });
});

// Custom dropdown filters via DataTable API
(function() {
    const filterMod = document.getElementById('filter-modalidad');
    const filterEst = document.getElementById('filter-estado');

    function applyFilters() {
        const mod = filterMod.value;
        const est = filterEst.value;

        if (!mod && !est) {
            DataTableManager.setClientFilter('tbl-asignaciones', null);
        } else {
            DataTableManager.setClientFilter('tbl-asignaciones', row => {
                if (mod && row.dataset.modalidad !== mod) return false;
                if (est && row.dataset.estado !== est) return false;
                return true;
            });
        }
    }

    filterMod.addEventListener('change', applyFilters);
    filterEst.addEventListener('change', applyFilters);
})();
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/logged_layout.php';
?>