<?php
declare(strict_types=1);

// ARCHIVO: resources/views/professor/generacion_rs.php
// Bandeja de solicitudes de RIF Sucesoral generadas por estudiantes.

$pageTitle = 'Generación de R.S. — Simulador SENIAT';
$activePage = 'generacion-rs';
$extraCss = '<link rel="stylesheet" href="' . asset('css/professor/generacion_rs.css') . '">';

// ── Datos Placeholder ──────────────────────────────────────
$solicitudes = [
    [
        'id' => 1,
        'estudiante_nombres' => 'Ana María',
        'estudiante_apellidos' => 'Martínez López',
        'estudiante_cedula' => '28456789',
        'estudiante_nacionalidad' => 'V',
        'seccion' => '4to A',
        'caso_titulo' => 'Sucesión González Méndez',
        'intento_actual' => 1,
        'intento_max' => 3,
        'fecha_solicitud' => '2026-03-09 10:30:00',
        'estado' => 'Pendiente',
        'motivo_rechazo' => null,
    ],
    [
        'id' => 2,
        'estudiante_nombres' => 'Pedro José',
        'estudiante_apellidos' => 'López Ramírez',
        'estudiante_cedula' => '27123456',
        'estudiante_nacionalidad' => 'V',
        'seccion' => '4to A',
        'caso_titulo' => 'Sucesión González Méndez',
        'intento_actual' => 2,
        'intento_max' => 3,
        'fecha_solicitud' => '2026-03-08 15:45:00',
        'estado' => 'Aprobada',
        'motivo_rechazo' => null,
    ],
    [
        'id' => 3,
        'estudiante_nombres' => 'María José',
        'estudiante_apellidos' => 'García Herrera',
        'estudiante_cedula' => '29876543',
        'estudiante_nacionalidad' => 'V',
        'seccion' => '4to B',
        'caso_titulo' => 'Sucesión Pérez Alvarado',
        'intento_actual' => 1,
        'intento_max' => 3,
        'fecha_solicitud' => '2026-03-08 09:15:00',
        'estado' => 'Rechazada',
        'motivo_rechazo' => 'Los datos del causante presentan inconsistencias: la cédula no corresponde con el nombre registrado en el caso de estudio. Revise la sección 1 del formulario.',
    ],
    [
        'id' => 4,
        'estudiante_nombres' => 'Carlos Andrés',
        'estudiante_apellidos' => 'Fernández Díaz',
        'estudiante_cedula' => '30112233',
        'estudiante_nacionalidad' => 'V',
        'seccion' => '4to B',
        'caso_titulo' => 'Sucesión González Méndez',
        'intento_actual' => 3,
        'intento_max' => 3,
        'fecha_solicitud' => '2026-03-07 18:00:00',
        'estado' => 'Pendiente',
        'motivo_rechazo' => null,
    ],
    [
        'id' => 5,
        'estudiante_nombres' => 'Valentina',
        'estudiante_apellidos' => 'Rodríguez Salas',
        'estudiante_cedula' => '28654321',
        'estudiante_nacionalidad' => 'V',
        'seccion' => '4to A',
        'caso_titulo' => 'Sucesión Pérez Alvarado',
        'intento_actual' => 1,
        'intento_max' => 3,
        'fecha_solicitud' => '2026-03-07 11:20:00',
        'estado' => 'Aprobada',
        'motivo_rechazo' => null,
    ],
    [
        'id' => 6,
        'estudiante_nombres' => 'Luis Enrique',
        'estudiante_apellidos' => 'Morales Quintero',
        'estudiante_cedula' => '27998877',
        'estudiante_nacionalidad' => 'V',
        'seccion' => '4to A',
        'caso_titulo' => 'Sucesión González Méndez',
        'intento_actual' => 2,
        'intento_max' => 3,
        'fecha_solicitud' => '2026-03-06 14:50:00',
        'estado' => 'Pendiente',
        'motivo_rechazo' => null,
    ],
];

// Stats
$stats = [
    'pendientes' => count(array_filter($solicitudes, fn($s) => $s['estado'] === 'Pendiente')),
    'aprobadas' => count(array_filter($solicitudes, fn($s) => $s['estado'] === 'Aprobada')),
    'rechazadas' => count(array_filter($solicitudes, fn($s) => $s['estado'] === 'Rechazada')),
    'total' => count($solicitudes),
];

// Helpers
$avatarColors = ['avatar--blue', 'avatar--green', 'avatar--amber', 'avatar--purple', 'avatar--red'];

function getInitials(string $fullName): string
{
    preg_match_all('/\b\w/u', $fullName, $m);
    return mb_strtoupper(implode('', array_slice($m[0], 0, 2)));
}

function getAvatarColor(string $initials, array $colors): string
{
    return $colors[abs(crc32($initials)) % count($colors)];
}

ob_start();
?>

<!-- Page Header -->
<div class="page-header">
    <div class="page-header-left">
        <h1>Generación de R.S.</h1>
        <p>Solicitudes de RIF Sucesoral de tus estudiantes</p>
    </div>
</div>

<!-- Stats Row -->
<div class="stats-row">
    <div class="stat-card stat-card--vertical animate-in">
        <div class="stat-card-top">
            <span class="stat-label">Pendientes</span>
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
            <span class="stat-label">Aprobadas</span>
            <div class="stat-icon green">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                    <polyline points="22 4 12 14.01 9 11.01" />
                </svg>
            </div>
        </div>
        <div class="stat-value">
            <?= $stats['aprobadas'] ?>
        </div>
    </div>

    <div class="stat-card stat-card--vertical animate-in">
        <div class="stat-card-top">
            <span class="stat-label">Rechazadas</span>
            <div class="stat-icon red">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <circle cx="12" cy="12" r="10" />
                    <line x1="15" y1="9" x2="9" y2="15" />
                    <line x1="9" y1="9" x2="15" y2="15" />
                </svg>
            </div>
        </div>
        <div class="stat-value">
            <?= $stats['rechazadas'] ?>
        </div>
    </div>

    <div class="stat-card stat-card--vertical animate-in">
        <div class="stat-card-top">
            <span class="stat-label">Total Solicitudes</span>
            <div class="stat-icon blue">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2" />
                    <rect x="8" y="2" width="8" height="4" rx="1" ry="1" />
                    <path d="M9 14l2 2 4-4" />
                </svg>
            </div>
        </div>
        <div class="stat-value">
            <?= $stats['total'] ?>
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
            <input type="text" id="search-rs" placeholder="Buscar estudiante...">
        </div>

        <div class="filter-dropdown" id="filter-seccion">
            <button class="filter-btn">
                Sección
                <svg viewBox="0 0 24 24" width="14" height="14">
                    <path d="M7 10l5 5 5-5z" fill="currentColor" />
                </svg>
            </button>
        </div>

        <div class="filter-dropdown" id="filter-caso">
            <button class="filter-btn">
                Caso
                <svg viewBox="0 0 24 24" width="14" height="14">
                    <path d="M7 10l5 5 5-5z" fill="currentColor" />
                </svg>
            </button>
        </div>

        <div class="filter-dropdown" id="filter-estado">
            <button class="filter-btn">
                Estado
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
                <th class="sortable" data-sort="caso">Caso</th>
                <th class="sortable" data-sort="intento">Intento</th>
                <th class="sortable" data-sort="fecha">Fecha Solicitud</th>
                <th class="sortable" data-sort="estado">Estado</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody id="rs-tbody">
            <?php if (empty($solicitudes)): ?>
                <tr>
                    <td colspan="7" class="empty-cell">
                        <div class="empty-state empty-state--blue">
                            <div class="empty-state-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round">
                                    <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2" />
                                    <rect x="8" y="2" width="8" height="4" rx="1" ry="1" />
                                    <path d="M9 14l2 2 4-4" />
                                </svg>
                            </div>
                            <h3>Sin solicitudes aún</h3>
                            <p>Cuando tus estudiantes generen solicitudes de R.S. desde sus intentos, aparecerán aquí.</p>
                        </div>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($solicitudes as $sol):
                    $fullName = trim($sol['estudiante_nombres'] . ' ' . $sol['estudiante_apellidos']);
                    $iniciales = getInitials($fullName);
                    $avatarClass = getAvatarColor($iniciales, $avatarColors);
                    $cedula = ($sol['estudiante_nacionalidad'] ?? 'V') . '-' . number_format((float) $sol['estudiante_cedula'], 0, ',', '.');

                    $statusClass = match ($sol['estado']) {
                        'Pendiente' => 'status-pendiente',
                        'Aprobada' => 'status-aprobada',
                        'Rechazada' => 'status-rechazada',
                        default => 'status-pendiente'
                    };

                    $fechaFormatted = date('d/m/Y H:i', strtotime($sol['fecha_solicitud']));
                    ?>
                    <tr data-estado="<?= htmlspecialchars($sol['estado']) ?>"
                        data-estudiante="<?= htmlspecialchars(mb_strtolower($fullName)) ?>"
                        data-seccion="<?= htmlspecialchars($sol['seccion']) ?>"
                        data-caso="<?= htmlspecialchars($sol['caso_titulo']) ?>">

                        <!-- Estudiante -->
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

                        <!-- Sección -->
                        <td>
                            <?= htmlspecialchars($sol['seccion']) ?>
                        </td>

                        <!-- Caso -->
                        <td><span class="caso-tag">
                                <?= htmlspecialchars($sol['caso_titulo']) ?>
                            </span></td>

                        <!-- Intento -->
                        <td>
                            <span class="intento-display">
                                #
                                <?= $sol['intento_actual'] ?> de
                                <?= $sol['intento_max'] ?>
                            </span>
                        </td>

                        <!-- Fecha Solicitud -->
                        <td>
                            <?= $fechaFormatted ?>
                        </td>

                        <!-- Estado -->
                        <td>
                            <span class="status-badge <?= $statusClass ?>">
                                <?= htmlspecialchars($sol['estado']) ?>
                            </span>
                        </td>

                        <!-- Acción -->
                        <td>
                            <?php if ($sol['estado'] === 'Pendiente'): ?>
                                <div class="action-btns">
                                    <button class="action-btn action-btn--approve" onclick="confirmarAprobacion(<?= $sol['id'] ?>)"
                                        title="Aprobar solicitud">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                                            stroke-linecap="round">
                                            <polyline points="20 6 9 17 4 12" />
                                        </svg>
                                        Aprobar
                                    </button>
                                    <button class="action-btn action-btn--reject" onclick="abrirRechazo(<?= $sol['id'] ?>)"
                                        title="Rechazar solicitud">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                                            stroke-linecap="round">
                                            <line x1="18" y1="6" x2="6" y2="18" />
                                            <line x1="6" y1="6" x2="18" y2="18" />
                                        </svg>
                                        Rechazar
                                    </button>
                                </div>
                            <?php else: ?>
                                <a href="javascript:void(0)" class="ver-link" onclick="verDetalle(<?= $sol['id'] ?>)">
                                    Ver detalle
                                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor"
                                        stroke-width="2.5" stroke-linecap="round">
                                        <path d="M5 12h14" />
                                        <polyline points="12 5 19 12 12 19" />
                                    </svg>
                                </a>
                            <?php endif; ?>
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
                <?= count($solicitudes) ?>
            </strong> de <strong>
                <?= $stats['total'] ?>
            </strong> solicitudes
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

<!-- ═══ Modal: Confirmar Aprobación ═══ -->
<dialog class="modal-base" id="modal-aprobar">
    <div class="modal-base__container" style="max-width: 420px;">
        <div class="modal-base__header">
            <h2 class="modal-base__title">Aprobar Solicitud</h2>
            <button class="modal-base__close" onclick="window.modalManager.close('modal-aprobar')">✕</button>
        </div>
        <div class="modal-base__body">
            <p style="color: var(--gray-600); font-size: var(--text-md); margin: 0;">
                ¿Está seguro que desea aprobar esta solicitud de RIF Sucesoral?
                El estudiante será notificado y podrá continuar con su declaración.
            </p>
        </div>
        <div class="modal-base__footer">
            <button class="modal-btn modal-btn-cancel"
                onclick="window.modalManager.close('modal-aprobar')">Cancelar</button>
            <button class="modal-btn modal-btn-primary" id="btn-confirmar-aprobacion">Sí, aprobar</button>
        </div>
    </div>
</dialog>

<!-- ═══ Modal: Rechazar con Motivo ═══ -->
<dialog class="modal-base" id="modal-rechazar">
    <div class="modal-base__container" style="max-width: 500px;">
        <div class="modal-base__header">
            <h2 class="modal-base__title">Rechazar Solicitud</h2>
            <button class="modal-base__close" onclick="window.modalManager.close('modal-rechazar')">✕</button>
        </div>
        <div class="modal-base__body">
            <div class="reject-field">
                <label for="motivo-rechazo">Motivo del rechazo</label>
                <textarea id="motivo-rechazo" placeholder="Explique al estudiante por qué se rechaza esta solicitud..."
                    required></textarea>
                <p class="field-hint">Este mensaje será visible para el estudiante.</p>
            </div>
        </div>
        <div class="modal-base__footer">
            <button class="modal-btn modal-btn-cancel"
                onclick="window.modalManager.close('modal-rechazar')">Cancelar</button>
            <button class="modal-btn modal-btn-danger" id="btn-confirmar-rechazo">Rechazar solicitud</button>
        </div>
    </div>
</dialog>

<!-- ═══ Modal: Ver Detalle ═══ -->
<dialog class="modal-base" id="modal-detalle">
    <div class="modal-base__container" style="max-width: 520px;">
        <div class="modal-base__header">
            <h2 class="modal-base__title">Detalle de Solicitud</h2>
            <button class="modal-base__close" onclick="window.modalManager.close('modal-detalle')">✕</button>
        </div>
        <div class="modal-base__body">
            <dl class="solicitud-detail-grid" id="detalle-content">
                <dt>Estudiante</dt>
                <dd id="det-estudiante">—</dd>
                <dt>Sección</dt>
                <dd id="det-seccion">—</dd>
                <dt>Caso</dt>
                <dd id="det-caso">—</dd>
                <dt>Intento</dt>
                <dd id="det-intento">—</dd>
                <dt>Fecha solicitud</dt>
                <dd id="det-fecha">—</dd>
                <dt>Estado</dt>
                <dd id="det-estado">—</dd>
            </dl>
            <div class="motivo-rechazo-box" id="det-motivo-box" style="display:none;">
                <strong>Motivo del rechazo:</strong>
                <p id="det-motivo" style="margin: 6px 0 0;"></p>
            </div>
        </div>
        <div class="modal-base__footer">
            <button class="modal-btn modal-btn-cancel"
                onclick="window.modalManager.close('modal-detalle')">Cerrar</button>
        </div>
    </div>
</dialog>


<!-- ═══ JavaScript ═══ -->
<script>
    (function () {
        // ── Placeholder data map for detail modal ──
        const solicitudesData = <?= json_encode(array_map(function ($s) {
            $fullName = trim($s['estudiante_nombres'] . ' ' . $s['estudiante_apellidos']);
            return [
                'id' => $s['id'],
                'estudiante' => $fullName,
                'seccion' => $s['seccion'],
                'caso' => $s['caso_titulo'],
                'intento' => '#' . $s['intento_actual'] . ' de ' . $s['intento_max'],
                'fecha' => date('d/m/Y H:i', strtotime($s['fecha_solicitud'])),
                'estado' => $s['estado'],
                'motivo_rechazo' => $s['motivo_rechazo'],
            ];
        }, $solicitudes), JSON_UNESCAPED_UNICODE) ?>;

        let targetId = null;

        // ── Approve flow ──
        window.confirmarAprobacion = function (id) {
            targetId = id;
            window.modalManager.open('modal-aprobar');
        };

        document.getElementById('btn-confirmar-aprobacion').addEventListener('click', function () {
            // TODO: AJAX call to approve
            window.modalManager.close('modal-aprobar');
            alert('Solicitud #' + targetId + ' aprobada (placeholder).');
        });

        // ── Reject flow ──
        window.abrirRechazo = function (id) {
            targetId = id;
            document.getElementById('motivo-rechazo').value = '';
            window.modalManager.open('modal-rechazar');
        };

        document.getElementById('btn-confirmar-rechazo').addEventListener('click', function () {
            const motivo = document.getElementById('motivo-rechazo').value.trim();
            if (!motivo) {
                document.getElementById('motivo-rechazo').focus();
                return;
            }
            // TODO: AJAX call to reject
            window.modalManager.close('modal-rechazar');
            alert('Solicitud #' + targetId + ' rechazada (placeholder).');
        });

        // ── Detail modal ──
        window.verDetalle = function (id) {
            const sol = solicitudesData.find(s => s.id === id);
            if (!sol) return;
            document.getElementById('det-estudiante').textContent = sol.estudiante;
            document.getElementById('det-seccion').textContent = sol.seccion;
            document.getElementById('det-caso').textContent = sol.caso;
            document.getElementById('det-intento').textContent = sol.intento;
            document.getElementById('det-fecha').textContent = sol.fecha;
            document.getElementById('det-estado').textContent = sol.estado;

            const motivoBox = document.getElementById('det-motivo-box');
            if (sol.motivo_rechazo) {
                motivoBox.style.display = '';
                document.getElementById('det-motivo').textContent = sol.motivo_rechazo;
            } else {
                motivoBox.style.display = 'none';
            }
            window.modalManager.open('modal-detalle');
        };

        // Backdrop click handled globally by core_modals.js
    })();
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/logged_layout.php';
?>