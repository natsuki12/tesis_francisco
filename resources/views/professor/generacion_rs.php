<?php
declare(strict_types=1);

// ARCHIVO: resources/views/professor/generacion_rs.php
// Bandeja de solicitudes de RIF Sucesoral — datos reales desde GeneracionRsController.

$pageTitle = 'Generación de R.S. — Simulador SENIAT';
$activePage = 'generacion-rs';
$extraCss = '<link rel="stylesheet" href="' . asset('css/professor/generacion_rs.css') . '">';

// ── $solicitudes y $stats vienen del controller ──
$solicitudes = $solicitudes ?? [];
$stats = $stats ?? ['pendientes' => 0, 'aprobadas' => 0, 'rechazadas' => 0, 'total' => 0];

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
        <p>Solicitudes de RIF Sucesoral de tus estudiantes (Modo Evaluación)</p>
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
            <input type="text" id="search-rs" placeholder="Buscar estudiante, caso, sección...">
        </div>

        <select id="filter-estado" class="filter-select">
            <option value="">Todos los estados</option>
            <option value="Pendiente_RIF">Pendientes</option>
            <option value="Aprobado">Aprobadas</option>
            <option value="Rechazado">Rechazadas</option>
        </select>
    </div>
</div>

<!-- Data Table -->
<div class="table-container animate-in">
    <table class="data-table" id="rs-table" data-no-auto-init>
        <thead>
            <tr>
                <th>Estudiante</th>
                <th>Sección</th>
                <th>Caso</th>
                <th>Intento</th>
                <th>Fecha Solicitud</th>
                <th>Estado</th>
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
                            <p>Cuando tus estudiantes envíen inscripciones de RIF en modo Evaluación, aparecerán aquí.</p>
                        </div>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($solicitudes as $idx => $sol):
                    $fullName = trim(($sol['est_nombres'] ?? '') . ' ' . ($sol['est_apellidos'] ?? ''));
                    $iniciales = getInitials($fullName);
                    $avatarClass = getAvatarColor($iniciales, $avatarColors);
                    $cedula = ($sol['est_nacionalidad'] ?? 'V') . '-' . number_format((float) ($sol['est_cedula'] ?? 0), 0, ',', '.');

                    $statusClass = match ($sol['estado']) {
                        'Pendiente_RIF' => 'status-pendiente',
                        'Aprobado' => 'status-aprobada',
                        'Rechazado' => 'status-rechazada',
                        default => 'status-pendiente'
                    };

                    $statusLabel = match ($sol['estado']) {
                        'Pendiente_RIF' => 'Pendiente',
                        'Aprobado' => 'Aprobada',
                        'Rechazado' => 'Rechazada',
                        default => $sol['estado']
                    };

                    $fechaFormatted = $sol['submitted_at']
                        ? date('d/m/Y H:i', strtotime($sol['submitted_at']))
                        : '—';

                    // Datos para el modal de rechazo
                    $tipoCalif = $sol['tipo_calificacion'] ?? 'aprobado_reprobado';
                    ?>
                    <tr data-estado="<?= htmlspecialchars($sol['estado']) ?>"
                        data-estudiante="<?= htmlspecialchars(mb_strtolower($fullName)) ?>"
                        data-seccion="<?= htmlspecialchars($sol['seccion'] ?? '') ?>"
                        data-caso="<?= htmlspecialchars($sol['caso_titulo'] ?? '') ?>"
                        data-id="<?= (int) $sol['id'] ?>"
                        data-tipo-calificacion="<?= htmlspecialchars($tipoCalif) ?>">

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
                            <?= htmlspecialchars($sol['seccion'] ?? '—') ?>
                        </td>

                        <!-- Caso -->
                        <td><span class="caso-tag">
                                <?= htmlspecialchars($sol['caso_titulo'] ?? '—') ?>
                            </span></td>

                        <!-- Intento -->
                        <td>
                            <span class="intento-display">
                                #<?= (int) ($sol['numero_intento'] ?? 1) ?> de <?= (int) ($sol['max_intentos'] ?? 1) ?>
                            </span>
                        </td>

                        <!-- Fecha Solicitud -->
                        <td>
                            <?= $fechaFormatted ?>
                        </td>

                        <!-- Estado -->
                        <td>
                            <span class="status-badge <?= $statusClass ?>">
                                <?= htmlspecialchars($statusLabel) ?>
                            </span>
                        </td>

                        <!-- Acción -->
                        <td>
                            <?php if ($sol['estado'] === 'Pendiente_RIF'): ?>
                                <a href="<?= base_url('generacion-rs/' . (int) $sol['id']) ?>" class="ver-link">
                                    Revisar
                                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor"
                                        stroke-width="2.5" stroke-linecap="round">
                                        <path d="M5 12h14" />
                                        <polyline points="12 5 19 12 12 19" />
                                    </svg>
                                </a>
                            <?php elseif ($sol['estado'] === 'Aprobado'): ?>
                                <span class="resultado-texto">Aprobado</span>
                            <?php else: ?>
                                <span class="resultado-texto">No aprobado</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Table Footer -->
    <div class="table-footer">
        <div class="table-footer-info" id="rs-footer-info">
            Mostrando <strong><?= count($solicitudes) ?></strong> de <strong><?= $stats['total'] ?></strong> solicitudes
        </div>
    </div>
</div>

<!-- ═══ JavaScript ═══ -->
<script>
    (function () {
        // ══════════════════════════════════════════════
        //  CLIENT-SIDE FILTERING
        // ══════════════════════════════════════════════
        var searchInput = document.getElementById('search-rs');
        var filterEstado = document.getElementById('filter-estado');
        var tbody = document.getElementById('rs-tbody');

        function filterTable() {
            var search = (searchInput.value || '').toLowerCase();
            var estado = filterEstado.value;
            var rows = tbody.querySelectorAll('tr[data-id]');
            var visible = 0;

            rows.forEach(function (row) {
                var matchSearch = !search ||
                    row.getAttribute('data-estudiante').indexOf(search) !== -1 ||
                    (row.getAttribute('data-seccion') || '').toLowerCase().indexOf(search) !== -1 ||
                    (row.getAttribute('data-caso') || '').toLowerCase().indexOf(search) !== -1;

                var matchEstado = !estado || row.getAttribute('data-estado') === estado;

                if (matchSearch && matchEstado) {
                    row.style.display = '';
                    visible++;
                } else {
                    row.style.display = 'none';
                }
            });

            var info = document.getElementById('rs-footer-info');
            if (info) {
                info.innerHTML = 'Mostrando <strong>' + visible + '</strong> de <strong><?= $stats['total'] ?></strong> solicitudes';
            }
        }

        searchInput.addEventListener('input', filterTable);
        filterEstado.addEventListener('change', filterTable);
    })();
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/logged_layout.php';
?>