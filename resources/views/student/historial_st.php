<?php
declare(strict_types=1);

// ARCHIVO: resources/views/student/historial_st.php

$pageTitle = 'Mis Entregas — Simulador SENIAT';
$activePage = 'mis-entregas';
$extraCss = '<link rel="stylesheet" href="' . asset('css/student/historial_st.css') . '">';

// $entregas viene del route (StudentAssignmentModel::getHistorialPlanillas)

// ── Helpers ────────────────────────────────────────────────
function mapEstadoHistorial(string $dbEstado): string
{
    return match ($dbEstado) {
        'Enviado' => 'Enviado',
        'Aprobado' => 'Aprobado',
        'Rechazado' => 'No Aprobado',
        default => ucfirst(str_replace('_', ' ', $dbEstado)),
    };
}

function getStatusClassHistorial(string $label): string
{
    return match ($label) {
        'Enviado' => 'status-info',
        'Aprobado' => 'status-active',
        'Rechazado' => 'status-danger',
        default => 'status-draft',
    };
}

ob_start();
?>

<!-- Page Header -->
<div class="page-header">
    <div class="page-header-left">
        <h1>Mis Entregas</h1>
        <p>Registro de tus declaraciones enviadas, calificaciones y planillas generadas</p>
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
            <input type="text" data-search-for="tbl-entregas" placeholder="Buscar por caso...">
        </div>

        <select class="toolbar-select" id="filter-estado-entregas">
            <option value="">Todos los estados</option>
            <option value="Enviado">Enviado</option>
            <option value="Aprobado">Aprobado</option>
            <option value="No Aprobado">No Aprobado</option>
        </select>
    </div>
    <div class="toolbar-right">
        Mostrar <select data-perpage-for="tbl-entregas" class="per-page-select"><option value="10" selected>10</option><option value="25">25</option><option value="50">50</option></select> filas
    </div>
</div>

<!-- Tabla Principal -->
<div class="table-container animate-in">
    <table class="data-table" id="tbl-entregas" data-per-page="10">
        <thead>
            <tr>
                <th class="sortable" data-col="0">Caso</th>
                <th class="sortable" data-col="1">Intento</th>
                <th class="sortable" data-col="2">Fecha de Envío</th>
                <th>Estado</th>
                <th>Nota</th>
                <th>Documentos</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($entregas as $entrega):
                $estadoLabel = mapEstadoHistorial($entrega['estado']);
                $statusClass = getStatusClassHistorial($estadoLabel);
                $fechaEnvio  = $entrega['fecha_envio'] ? date('d/m/Y H:i', strtotime($entrega['fecha_envio'])) : '—';
                $searchStr   = strtolower(
                    ($entrega['caso_titulo'] ?? '') . ' ' .
                    $estadoLabel . ' ' .
                    $fechaEnvio
                );

                // Nota
                $tipoCalif = $entrega['tipo_calificacion'] ?? 'aprobado_reprobado';
                if ($tipoCalif === 'numerica' && $entrega['nota_numerica'] !== null) {
                    $notaDisplay = number_format((float) $entrega['nota_numerica'], 1) . ' / 20';
                    $notaClass   = (float) $entrega['nota_numerica'] >= 10 ? 'text-green' : 'text-red';
                } elseif ($entrega['nota_cualitativa'] !== null) {
                    $notaDisplay = $entrega['nota_cualitativa'];
                    $notaClass   = $entrega['nota_cualitativa'] === 'Aprobado' ? 'text-green' : 'text-red';
                } else {
                    $notaDisplay = null;
                    $notaClass   = '';
                }

                $esCalificado = in_array($entrega['estado'], ['Aprobado', 'Rechazado'], true);
            ?>
                <tr data-search="<?= htmlspecialchars($searchStr) ?>" data-estado="<?= htmlspecialchars($estadoLabel) ?>">
                    <td><strong><?= htmlspecialchars($entrega['caso_titulo']) ?></strong></td>
                    <td>#<?= $entrega['numero'] ?></td>
                    <td><?= $fechaEnvio ?></td>
                    <td>
                        <span class="status-badge <?= $statusClass ?>">
                            <?= htmlspecialchars($estadoLabel) ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($notaDisplay !== null): ?>
                            <strong class="<?= $notaClass ?>"><?= htmlspecialchars($notaDisplay) ?></strong>
                        <?php else: ?>
                            <span style="color: var(--gray-400);">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (in_array($entrega['estado'], ['Enviado', 'Aprobado', 'Rechazado'], true)): ?>
                        <div class="planilla-actions">
                            <a href="<?= base_url('/simulador/sucesion/planilla_pdf?intento_id=' . $entrega['intento_id']) ?>"
                               target="_blank" class="planilla-btn" title="Planilla FORMA DS-99032">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                    <polyline points="14 2 14 8 20 8" />
                                </svg>
                                Planilla
                            </a>
                            <a href="<?= base_url('/simulador/sucesion/declaracion_pdf?intento_id=' . $entrega['intento_id']) ?>"
                               target="_blank" class="planilla-btn planilla-download" title="Resumen de la Asignación">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                    <circle cx="12" cy="12" r="3" />
                                </svg>
                                Resumen
                            </a>
                        </div>
                        <?php else: ?>
                            <span style="color: var(--gray-400);">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($esCalificado): ?>
                            <a href="<?= base_url('/mis-calificaciones/' . $entrega['asignacion_id']) ?>" class="planilla-btn">
                                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                                Ver corrección
                            </a>
                        <?php else: ?>
                            <span style="color: var(--gray-400);">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Table Footer -->
<div class="table-footer" data-footer-for="tbl-entregas">
    <div class="table-footer-info"></div>
    <div class="pagination"></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var sel = document.getElementById('filter-estado-entregas');
    if (sel) {
        sel.addEventListener('change', function() {
            var val = this.value;
            window.DataTableManager.setClientFilter('tbl-entregas', val
                ? function(row) { return row.dataset.estado === val; }
                : null
            );
        });
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/logged_layout.php';
?>