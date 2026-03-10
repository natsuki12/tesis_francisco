<?php
declare(strict_types=1);

// ARCHIVO: resources/views/student/historial_st.php

$pageTitle = 'Historial / Planillas — Simulador SENIAT';
$activePage = 'historial-planillas';
$extraCss = '<link rel="stylesheet" href="' . asset('css/student/historial_st.css') . '">';

// ── Datos Placeholder ──────────────────────────────────────
$entregas = [
    [
        'caso' => 'Sucesión González Méndez',
        'intento' => 2,
        'fecha_envio' => '2026-03-07 14:20:00',
        'estado' => 'Enviado',
        'nota' => null,
    ],
    [
        'caso' => 'Sucesión Pérez Alvarado',
        'intento' => 1,
        'fecha_envio' => '2026-03-03 11:45:00',
        'estado' => 'Calificado',
        'nota' => 16.0,
    ],
    [
        'caso' => 'Sucesión González Méndez',
        'intento' => 1,
        'fecha_envio' => '2026-03-01 14:30:00',
        'estado' => 'Calificado',
        'nota' => 14.5,
    ],
];

$solicitudesRS = [
    [
        'caso' => 'Sucesión González Méndez',
        'intento' => 2,
        'fecha_solicitud' => '2026-03-07 14:25:00',
        'estado' => 'Pendiente',
        'motivo_rechazo' => null,
    ],
    [
        'caso' => 'Sucesión Pérez Alvarado',
        'intento' => 1,
        'fecha_solicitud' => '2026-03-03 11:50:00',
        'estado' => 'Aprobada',
        'motivo_rechazo' => null,
    ],
    [
        'caso' => 'Sucesión González Méndez',
        'intento' => 1,
        'fecha_solicitud' => '2026-03-01 14:35:00',
        'estado' => 'Rechazada',
        'motivo_rechazo' => 'La cédula del causante no coincide con el documento registrado. Verifica los datos y vuelve a solicitar.',
    ],
];

ob_start();
?>

<!-- Page Header -->
<div class="page-header">
    <div class="page-header-left">
        <h1>Historial / Planillas</h1>
        <p>Registro de tus declaraciones enviadas y planillas generadas</p>
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
            <input type="text" id="search-historial" placeholder="Buscar por caso...">
        </div>

        <select class="toolbar-select" id="filter-caso">
            <option value="">Todos los casos</option>
            <option>Sucesión González Méndez</option>
            <option>Sucesión Pérez Alvarado</option>
        </select>

        <select class="toolbar-select" id="filter-estado">
            <option value="">Todos los estados</option>
            <option value="Enviado">Enviado</option>
            <option value="Calificado">Calificado</option>
        </select>
    </div>
</div>

<!-- Tabla Principal -->
<div class="table-container animate-in">
    <table class="data-table">
        <thead>
            <tr>
                <th>Caso</th>
                <th>Intento</th>
                <th>Fecha de Envío</th>
                <th>Estado</th>
                <th>Nota</th>
                <th>Planilla</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($entregas)): ?>
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
                            <h3>Sin declaraciones enviadas</h3>
                            <p>Cuando envíes tus intentos desde el simulador, aparecerán aquí con sus planillas.</p>
                        </div>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($entregas as $entrega):
                    $statusClass = match ($entrega['estado']) {
                        'Enviado' => 'status-info',
                        'En revisión' => 'status-warning',
                        'Calificado' => 'status-active',
                        default => 'status-draft',
                    };
                    ?>
                    <tr>
                        <td><strong>
                                <?= htmlspecialchars($entrega['caso']) ?>
                            </strong></td>
                        <td>#
                            <?= $entrega['intento'] ?>
                        </td>
                        <td>
                            <?= date('d/m/Y H:i', strtotime($entrega['fecha_envio'])) ?>
                        </td>
                        <td><span class="status-badge <?= $statusClass ?>">
                                <?= htmlspecialchars($entrega['estado']) ?>
                            </span></td>
                        <td>
                            <?php if ($entrega['nota'] !== null): ?>
                                <strong style="color: <?= $entrega['nota'] >= 10 ? 'var(--green-600)' : 'var(--red-600)' ?>">
                                    <?= number_format($entrega['nota'], 1) ?>/20
                                </strong>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="planilla-actions">
                                <a href="#" class="planilla-btn"
                                    onclick="event.preventDefault(); alert('Ver planilla (placeholder)');">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                        <circle cx="12" cy="12" r="3" />
                                    </svg>
                                    Ver
                                </a>
                                <a href="#" class="planilla-btn planilla-download"
                                    onclick="event.preventDefault(); alert('Descargar PDF (placeholder)');">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                        <polyline points="7 10 12 15 17 10" />
                                        <line x1="12" y1="15" x2="12" y2="3" />
                                    </svg>
                                    PDF
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Solicitudes de R.S. -->
<div class="rs-section animate-in">
    <div class="rs-section-header">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
            <polyline points="14 2 14 8 20 8" />
            <line x1="16" y1="13" x2="8" y2="13" />
            <line x1="16" y1="17" x2="8" y2="17" />
            <polyline points="10 9 9 9 8 9" />
        </svg>
        <h3>Solicitudes de R.S.</h3>
    </div>

    <?php if (empty($solicitudesRS)): ?>
        <p class="rs-empty">No has generado solicitudes de R.S. aún.</p>
    <?php else: ?>
        <div class="table-container">
            <table class="data-table data-table--sm">
                <thead>
                    <tr>
                        <th>Caso</th>
                        <th>Intento</th>
                        <th>Fecha Solicitud</th>
                        <th>Estado</th>
                        <th>Motivo de Rechazo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($solicitudesRS as $rs):
                        $rsStatusClass = match ($rs['estado']) {
                            'Pendiente' => 'status-warning',
                            'Aprobada' => 'status-active',
                            'Rechazada' => 'status-danger',
                            default => 'status-draft',
                        };
                        ?>
                        <tr>
                            <td>
                                <?= htmlspecialchars($rs['caso']) ?>
                            </td>
                            <td>#
                                <?= $rs['intento'] ?>
                            </td>
                            <td>
                                <?= date('d/m/Y H:i', strtotime($rs['fecha_solicitud'])) ?>
                            </td>
                            <td><span class="status-badge <?= $rsStatusClass ?>">
                                    <?= htmlspecialchars($rs['estado']) ?>
                                </span></td>
                            <td>
                                <?php if ($rs['motivo_rechazo']): ?>
                                    <span class="rs-motivo" title="<?= htmlspecialchars($rs['motivo_rechazo']) ?>">
                                        <?= htmlspecialchars(mb_substr($rs['motivo_rechazo'], 0, 80)) ?>
                                        <?= mb_strlen($rs['motivo_rechazo']) > 80 ? '...' : '' ?>
                                    </span>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/logged_layout.php';
?>