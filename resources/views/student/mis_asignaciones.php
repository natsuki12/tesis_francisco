<?php
declare(strict_types=1);

// ARCHIVO: resources/views/student/mis_asignaciones.php

$pageTitle = 'Mis Asignaciones — Simulador SENIAT';
$activePage = 'mis-asignaciones';
$extraCss = '<link rel="stylesheet" href="' . asset('css/student/mis_asignaciones.css') . '">';

// $asignaciones viene del route (StudentAssignmentModel::getAsignaciones)
// Cada fila: asignacion_id, asignacion_estado, caso_titulo, profesor_nombre,
//            modalidad (Practica_Libre|Evaluacion), max_intentos, fecha_limite,
//            intentos_usados, mejor_nota, tiene_borrador, seccion_nombre, ...

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

    return match ($estado) {
        'Pendiente' => $intentos === 0 ? 'Sin iniciar' : ($borrador ? 'En progreso' : 'Enviado'),
        'En_Progreso' => $borrador ? 'En progreso' : 'Enviado',
        'Completado' => 'Calificada',
        'Vencido' => 'Vencida',
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
            <input type="text" id="search-asignaciones" placeholder="Buscar caso o profesor...">
        </div>

        <select class="toolbar-select" id="filter-estado">
            <option value="">Todas</option>
            <option value="Sin iniciar">Sin iniciar</option>
            <option value="En progreso">En progreso</option>
            <option value="Enviado">Enviado</option>
            <option value="Calificada">Calificada</option>
        </select>

        <select class="toolbar-select" id="filter-modalidad">
            <option value="">Todas las modalidades</option>
            <option value="Práctica Libre">Práctica Libre</option>
            <option value="Práctica Guiada">Práctica Guiada</option>
            <option value="Evaluación">Evaluación</option>
        </select>
    </div>
</div>

<!-- Assignment Cards Grid -->
<?php if (empty($asignaciones)): ?>
    <div class="table-container">
        <div class="empty-state empty-state--blue" style="padding: 60px 24px;">
            <div class="empty-state-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2" />
                    <rect x="8" y="2" width="8" height="4" rx="1" ry="1" />
                </svg>
            </div>
            <h3>Sin asignaciones</h3>
            <p>Cuando tu profesor te asigne casos, aparecerán aquí.</p>
        </div>
    </div>
<?php else: ?>
    <div class="assignment-grid">
        <?php foreach ($asignaciones as $asig):
            // ── Derived values ──
            $modeLabel = getModeLabel($asig['modalidad']);
            $modeClass = getModeClassSt($asig['modalidad']);
            $estadoLabel = mapEstadoLabel($asig);
            $statusClass = getStatusClassSt($estadoLabel);
            $maxIntentos = (int) $asig['max_intentos'];
            $intentosUsados = (int) $asig['intentos_usados'];
            $mejorNota = $asig['mejor_nota'] !== null ? (float) $asig['mejor_nota'] : null;
            $tieneBorrador = (bool) $asig['tiene_borrador'];
            $fechaLimite = $asig['fecha_limite'] ?? null;
            $profesorNombre = $asig['profesor_nombre'] ?? 'Profesor';
            $seccionNombre = $asig['seccion_nombre'] ?? '';
            $maxDisplay = $maxIntentos === 0 ? '∞' : (string) $maxIntentos;
            $dotCount = $maxIntentos === 0 ? max($intentosUsados + 1, 3) : $maxIntentos;

            // Deadline urgency
            $dlBadge = '';
            $dlText = '';
            if ($fechaLimite) {
                $deadlineTs = strtotime($fechaLimite);
                $daysLeft = (int) (($deadlineTs - time()) / 86400);
                if ($estadoLabel === 'Vencida' || $daysLeft < 0) {
                    $dlBadge = 'deadline-expired';
                    $dlText = 'Fecha límite vencida';
                } elseif ($daysLeft <= 2) {
                    $dlBadge = 'deadline-urgent';
                    $dlText = 'Fecha límite: ' . date('d/m/Y', $deadlineTs);
                } elseif ($daysLeft <= 5) {
                    $dlBadge = 'deadline-soon';
                    $dlText = 'Fecha límite: ' . date('d/m/Y', $deadlineTs);
                } else {
                    $dlBadge = 'deadline-ok';
                    $dlText = 'Fecha límite: ' . date('d/m/Y', $deadlineTs);
                }
            }

            // Action Button
            if ($estadoLabel === 'Sin iniciar') {
                $btnText = 'Comenzar';
                $btnClass = 'btn-comenzar';
            } elseif ($tieneBorrador) {
                $btnText = 'Continuar';
                $btnClass = 'btn-continuar';
            } elseif (in_array($estadoLabel, ['Calificada', 'Vencida'])) {
                $btnText = 'Ver resultados';
                $btnClass = 'btn-resultados';
            } elseif ($maxIntentos > 0 && $intentosUsados < $maxIntentos && $intentosUsados > 0) {
                $btnText = 'Nuevo intento';
                $btnClass = 'btn-nuevo-intento';
            } else {
                $btnText = 'Ver detalles';
                $btnClass = 'btn-resultados';
            }
            ?>
            <a href="<?= base_url('/mis-asignaciones/' . $asig['asignacion_id']) ?>" class="assignment-card animate-in">
                <!-- Header -->
                <div class="assignment-card-header">
                    <div>
                        <h3 class="assignment-card-title">
                            <?= htmlspecialchars($asig['caso_titulo']) ?>
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

                <!-- Body -->
                <div class="assignment-card-body">
                    <div class="assignment-info-row">
                        <!-- Progress Dots -->
                        <div class="progress-dots">
                            <?php for ($i = 0; $i < $dotCount; $i++): ?>
                                <span class="progress-dot <?= $i < $intentosUsados ? 'dot-used' : 'dot-available' ?>"></span>
                            <?php endfor; ?>
                            <span class="progress-dots-label">
                                <?= $intentosUsados ?> de
                                <?= $maxDisplay ?>
                            </span>
                        </div>
                    </div>

                    <?php if ($mejorNota !== null): ?>
                        <div class="assignment-nota">
                            Mejor nota:
                            <strong class="<?= $mejorNota >= 10 ? 'nota-pass' : 'nota-fail' ?>">
                                <?= number_format($mejorNota, 1) ?>/20
                            </strong>
                        </div>
                    <?php elseif ($intentosUsados > 0): ?>
                        <div class="assignment-nota">Sin calificar aún</div>
                    <?php endif; ?>

                    <?php if ($dlBadge): ?>
                        <span class="deadline-badge <?= $dlBadge ?>">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                                <line x1="16" y1="2" x2="16" y2="6" />
                                <line x1="8" y1="2" x2="8" y2="6" />
                                <line x1="3" y1="10" x2="21" y2="10" />
                            </svg>
                            <?= $dlText ?>
                        </span>
                    <?php endif; ?>
                </div>

                <!-- Footer -->
                <div class="assignment-card-footer">
                    <span class="status-badge <?= $statusClass ?>">
                        <?= htmlspecialchars($estadoLabel) ?>
                    </span>
                    <?php if (in_array($btnText, ['Comenzar', 'Continuar', 'Nuevo intento'])): ?>
                        <form method="POST" action="<?= base_url('/api/intentos/iniciar') ?>" style="display:inline;"
                            onclick="event.stopPropagation();">
                            <input type="hidden" name="asignacion_id" value="<?= $asig['asignacion_id'] ?>">
                            <button type="submit" class="btn btn-sm <?= $btnClass ?>">
                                <?= $btnText ?>
                            </button>
                        </form>
                    <?php else: ?>
                        <span class="btn btn-sm <?= $btnClass ?>">
                            <?= $btnText ?>
                        </span>
                    <?php endif; ?>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/logged_layout.php';
?>