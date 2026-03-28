<?php
declare(strict_types=1);

// ARCHIVO: resources/views/student/detalle_asignacion.php

$pageTitle = 'Detalle de Asignación — Simulador SENIAT';
$activePage = 'mis-asignaciones';
$extraCss = '<link rel="stylesheet" href="' . asset('css/student/mis_asignaciones.css') . '">';

// $asignacion y $intentos vienen del route (StudentAssignmentModel)

// ── Helpers ────────────────────────────────────────────────
function getModeLabel_d(string $dbMode): string
{
    return match ($dbMode) {
        'Practica_Libre' => 'Práctica Libre',
        'Practica_guiada' => 'Práctica Guiada',
        'Evaluacion' => 'Evaluación',
        default => $dbMode,
    };
}

function getModeClass_d(string $dbMode): string
{
    return match ($dbMode) {
        'Practica_Libre' => 'mode-libre',
        'Practica_guiada' => 'mode-guiado',
        'Evaluacion' => 'mode-evaluacion',
        default => 'mode-guiado',
    };
}

/**
 * Mapea estado de intento (DB sim_intentos.estado) a label de display.
 * DB values: En_Progreso, Enviado, Aprobado, Rechazado, Cancelado
 */
function mapIntentoEstado(string $dbEstado): string
{
    return match ($dbEstado) {
        'En_Progreso' => 'Borrador',
        'Enviado' => 'Enviado',
        'Aprobado' => 'Aprobado',
        'Rechazado' => 'Rechazado',
        'Cancelado' => 'Cancelado',
        default => ucfirst(str_replace('_', ' ', $dbEstado)),
    };
}

function getIntentoStatusClass(string $label): string
{
    return match ($label) {
        'Borrador' => 'status-warning',
        'Enviado' => 'status-info',
        'Aprobado' => 'status-active',
        'Rechazado' => 'status-danger',
        'Cancelado' => 'status-draft',
        default => 'status-draft',
    };
}

// ── Derived values ──────────────────────────────────────────
$modeLabel = getModeLabel_d($asignacion['modalidad']);
$modeClass = getModeClass_d($asignacion['modalidad']);
$maxIntentos = (int) $asignacion['max_intentos'];
$fechaLimite = $asignacion['fecha_limite'] ?? null;
$profesorNombre = $asignacion['profesor_nombre'] ?? 'Profesor';
$seccionNombre = $asignacion['seccion_nombre'] ?? '';
$maxDisplay = $maxIntentos === 0 ? '∞' : (string) $maxIntentos;

$intentosUsados = count($intentos);
$tieneBorrador = !empty(array_filter($intentos, fn($i) => ($i['estado'] ?? '') === 'En_Proceso'));

// Intentos disponibles
if ($maxIntentos === 0) {
    $intentosDisponibles = 999; // ilimitados
} else {
    $intentosDisponibles = max(0, $maxIntentos - $intentosUsados);
}

// Dot count for progress visualization
$dotCount = $maxIntentos === 0 ? max($intentosUsados + 1, 3) : $maxIntentos;

ob_start();
?>

<!-- Breadcrumb -->
<div class="breadcrumb">
    <a href="<?= base_url('/mis-asignaciones') ?>">Mis Asignaciones</a>
    <span class="breadcrumb-sep">›</span>
    <span class="breadcrumb-current">
        <?= htmlspecialchars($asignacion['caso_titulo']) ?>
    </span>
</div>

<!-- Assignment Summary Card -->
<div class="assignment-card animate-in" style="cursor: default; margin-bottom: 20px;">
    <div class="assignment-card-header">
        <div>
            <h3 class="assignment-card-title" style="font-size: var(--text-lg);">
                <?= htmlspecialchars($asignacion['caso_titulo']) ?>
            </h3>
            <div class="assignment-card-sub">
                <?= htmlspecialchars($profesorNombre) ?>
                <?php if ($seccionNombre): ?>
                    | <?= htmlspecialchars($seccionNombre) ?>
                <?php endif; ?>
            </div>
        </div>
        <span class="mode-badge <?= $modeClass ?>">
            <?= htmlspecialchars($modeLabel) ?>
        </span>
    </div>
    <div class="assignment-card-body">
        <div class="assignment-info-row">
            <div class="progress-dots">
                <?php for ($i = 0; $i < $dotCount; $i++): ?>
                    <span class="progress-dot <?= $i < $intentosUsados ? 'dot-used' : 'dot-available' ?>"></span>
                <?php endfor; ?>
                <span class="progress-dots-label">
                    <?= $intentosUsados ?> de
                    <?= $maxDisplay ?> intentos usados
                </span>
            </div>
            <?php if ($fechaLimite): ?>
                <?php
                $deadlineTs = strtotime($fechaLimite);
                $daysLeft = (int) (($deadlineTs - time()) / 86400);
                $dlClass = $daysLeft < 0 ? 'deadline-expired' : ($daysLeft <= 2 ? 'deadline-urgent' : ($daysLeft <= 5 ? 'deadline-soon' : 'deadline-ok'));
                ?>
                <span class="deadline-badge <?= $dlClass ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                        <line x1="16" y1="2" x2="16" y2="6" />
                        <line x1="8" y1="2" x2="8" y2="6" />
                        <line x1="3" y1="10" x2="21" y2="10" />
                    </svg>
                    <?= $daysLeft < 0 ? 'Fecha límite vencida' : 'Fecha límite: ' . date('d/m/Y', $deadlineTs) ?>
                </span>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Instrucciones del profesor -->
<?php if (!empty($asignacion['instrucciones'])): ?>
    <div class="panel animate-in" style="margin-bottom: 20px;">
        <div class="panel-header">
            <h3>Instrucciones del Profesor</h3>
        </div>
        <div class="panel-body" style="padding: 18px 22px;">
            <p style="font-size: var(--text-sm); color: var(--gray-600); line-height: 1.6; margin: 0;">
                <?= htmlspecialchars($asignacion['instrucciones']) ?>
            </p>
        </div>
    </div>
<?php endif; ?>

<!-- Tabla de Intentos -->
<div class="table-container animate-in">
    <table class="data-table">
        <thead>
            <tr>
                <th>Intento</th>
                <th>Estado</th>
                <th>Fecha Inicio</th>
                <th>Fecha Envío</th>
                <th>Observaciones</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($intentos)): ?>
                <tr>
                    <td colspan="6" class="empty-cell">
                        <div class="empty-state empty-state--blue">
                            <div class="empty-state-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                    <polyline points="14 2 14 8 20 8" />
                                </svg>
                            </div>
                            <h3>No has realizado ningún intento aún</h3>
                            <p>¡Comienza tu primer intento!</p>
                        </div>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($intentos as $intento):
                    $estadoLabel = mapIntentoEstado($intento['estado']);
                    $statusClass = getIntentoStatusClass($estadoLabel);
                    ?>
                    <tr>
                        <td><strong>#<?= $intento['numero'] ?></strong></td>
                        <td><span class="status-badge <?= $statusClass ?>">
                                <?= htmlspecialchars($estadoLabel) ?>
                            </span></td>
                        <td>
                            <?= $intento['fecha_inicio'] ? date('d/m/Y', strtotime($intento['fecha_inicio'])) : '—' ?>
                        </td>
                        <td>
                            <?= $intento['fecha_envio'] ? date('d/m/Y H:i', strtotime($intento['fecha_envio'])) : '—' ?>
                        </td>
                        <td style="max-width: 200px;">
                            <?php if (!empty($intento['observacion'])): ?>
                                <span style="font-size: var(--text-xs); color: var(--gray-500);"
                                    title="<?= htmlspecialchars($intento['observacion']) ?>">
                                    <?= htmlspecialchars(mb_substr($intento['observacion'], 0, 60)) ?>
                                    <?= mb_strlen($intento['observacion']) > 60 ? '...' : '' ?>
                                </span>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($estadoLabel === 'Borrador'): ?>
                                <form method="POST" action="<?= base_url('/api/intentos/iniciar') ?>" style="display:inline;">
                                    <input type="hidden" name="asignacion_id" value="<?= $asignacion['asignacion_id'] ?>">
                                    <button type="submit" class="ver-link" style="border:none;background:none;cursor:pointer;">
                                        Continuar
                                        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor"
                                            stroke-width="2.5" stroke-linecap="round">
                                            <path d="M5 12h14" />
                                            <polyline points="12 5 19 12 12 19" />
                                        </svg>
                                    </button>
                                </form>
                            <?php elseif ($estadoLabel === 'Aprobado'): ?>
                                <a href="<?= base_url('/mis-calificaciones/' . $asignacion['asignacion_id']) ?>" class="ver-link">
                                    Ver corrección
                                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor"
                                        stroke-width="2.5" stroke-linecap="round">
                                        <path d="M5 12h14" />
                                        <polyline points="12 5 19 12 12 19" />
                                    </svg>
                                </a>
                            <?php elseif ($estadoLabel === 'Enviado'): ?>
                                <div style="display: flex; gap: 8px; align-items: center;">
                                    <a href="<?= base_url('/simulador/sucesion/planilla_pdf?intento_id=' . $intento['intento_id']) ?>"
                                       target="_blank" class="ver-link" title="Planilla FORMA DS-99032"
                                       style="font-size: var(--text-xs);">
                                        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="#dc3545" stroke-width="2" stroke-linecap="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                        Planilla
                                    </a>
                                    <a href="<?= base_url('/simulador/sucesion/declaracion_pdf?intento_id=' . $intento['intento_id']) ?>"
                                       target="_blank" class="ver-link" title="Resumen de la Asignación"
                                       style="font-size: var(--text-xs);">
                                        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                        Resumen
                                    </a>
                                </div>
                            <?php else: ?>
                                <span style="font-size: var(--text-xs); color: var(--gray-400);">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Action: Iniciar nuevo intento -->
<?php if ($intentosDisponibles > 0 && !$tieneBorrador): ?>
    <div style="margin-top: 16px; text-align: center;">
        <form method="POST" action="<?= base_url('/api/intentos/iniciar') ?>" style="display:inline;">
            <input type="hidden" name="asignacion_id" value="<?= $asignacion['asignacion_id'] ?>">
            <button type="submit" class="btn btn-primary">
                Iniciar nuevo intento
            </button>
        </form>
    </div>
<?php elseif ($maxIntentos > 0 && $intentosDisponibles === 0): ?>
    <div
        style="margin-top: 16px; text-align: center; padding: 12px; background: var(--gray-50); border-radius: var(--radius-sm); color: var(--gray-500); font-size: var(--text-sm);">
        Has alcanzado el máximo de intentos para esta asignación.
    </div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/logged_layout.php';
?>