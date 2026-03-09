<?php
declare(strict_types=1);

// ARCHIVO: resources/views/professor/detalle_estudiante.php
// Sub-vista: perfil y entregas de un estudiante específico.

$pageTitle = 'Detalle Estudiante — Simulador SENIAT';
$activePage = 'mis-estudiantes';
$extraCss = '<link rel="stylesheet" href="' . asset('css/professor/mis_estudiantes.css') . '">';
$extraCss .= '<link rel="stylesheet" href="' . asset('css/professor/entregas.css') . '">';

// ── Datos Placeholder ──────────────────────────────────────
$estudiante = [
    'id' => 1,
    'nombres' => 'Ana María',
    'apellidos' => 'Martínez López',
    'cedula' => '28456789',
    'nacionalidad' => 'V',
    'email' => 'ana.martinez@est.example.com',
    'seccion' => '4to A',
    'fecha_inscripcion' => '2025-09-15',
];

$entregas = [
    [
        'id' => 101,
        'caso_titulo' => 'Sucesión González Méndez',
        'asignacion_nombre' => 'Asignación 1',
        'intento_actual' => 1,
        'intento_max' => 3,
        'created_at' => '2026-03-09 10:30:00',
        'estado' => 'Enviado',
        'nota' => null,
    ],
    [
        'id' => 102,
        'caso_titulo' => 'Sucesión González Méndez',
        'asignacion_nombre' => 'Asignación 1',
        'intento_actual' => 2,
        'intento_max' => 3,
        'created_at' => '2026-03-07 14:20:00',
        'estado' => 'Calificado',
        'nota' => 14.5,
    ],
    [
        'id' => 103,
        'caso_titulo' => 'Sucesión Pérez Alvarado',
        'asignacion_nombre' => 'Asignación 2',
        'intento_actual' => 1,
        'intento_max' => 3,
        'created_at' => '2026-03-05 09:10:00',
        'estado' => 'En Progreso',
        'nota' => null,
    ],
    [
        'id' => 104,
        'caso_titulo' => 'Sucesión González Méndez',
        'asignacion_nombre' => 'Asignación 1',
        'intento_actual' => 3,
        'intento_max' => 3,
        'created_at' => '2026-03-03 16:45:00',
        'estado' => 'Calificado',
        'nota' => 18.0,
    ],
];

// Helpers
$fullName = trim($estudiante['nombres'] . ' ' . $estudiante['apellidos']);
preg_match_all('/\b\w/u', $fullName, $m);
$iniciales = mb_strtoupper(implode('', array_slice($m[0], 0, 2)));
$avatarColors = ['avatar--blue', 'avatar--green', 'avatar--amber', 'avatar--purple'];
$avatarClass = $avatarColors[abs(crc32($iniciales)) % count($avatarColors)];
$cedula = ($estudiante['nacionalidad'] ?? 'V') . '-' . number_format((float) $estudiante['cedula'], 0, ',', '.');

ob_start();
?>

<!-- Breadcrumb -->
<div class="breadcrumb">
    <a href="<?= base_url('/mis-estudiantes') ?>">Mis Estudiantes</a>
    <span class="breadcrumb-sep">›</span>
    <span class="breadcrumb-current">
        <?= htmlspecialchars($fullName) ?>
    </span>
</div>

<!-- Page Header -->
<div class="page-header">
    <div class="page-header-left">
        <h1>Detalle del Estudiante</h1>
    </div>
</div>

<!-- Profile Card -->
<div class="profile-card animate-in">
    <div class="profile-avatar <?= $avatarClass ?>">
        <?= htmlspecialchars($iniciales) ?>
    </div>
    <div class="profile-info">
        <h2 class="profile-name">
            <?= htmlspecialchars($fullName) ?>
        </h2>
        <div class="profile-meta">
            <div class="profile-meta-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <rect x="2" y="3" width="20" height="14" rx="2" />
                    <line x1="8" y1="21" x2="16" y2="21" />
                    <line x1="12" y1="17" x2="12" y2="21" />
                </svg>
                <span>CI: <strong>
                        <?= htmlspecialchars($cedula) ?>
                    </strong></span>
            </div>
            <div class="profile-meta-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                    <polyline points="22,6 12,13 2,6" />
                </svg>
                <span>
                    <?= htmlspecialchars($estudiante['email']) ?>
                </span>
            </div>
            <div class="profile-meta-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                    <circle cx="9" cy="7" r="4" />
                </svg>
                <span>Sección: <strong>
                        <?= htmlspecialchars($estudiante['seccion']) ?>
                    </strong></span>
            </div>
            <div class="profile-meta-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                    <line x1="16" y1="2" x2="16" y2="6" />
                    <line x1="8" y1="2" x2="8" y2="6" />
                    <line x1="3" y1="10" x2="21" y2="10" />
                </svg>
                <span>Inscrito: <strong>
                        <?= date('d/m/Y', strtotime($estudiante['fecha_inscripcion'])) ?>
                    </strong></span>
            </div>
        </div>
    </div>
</div>

<!-- Entregas del Estudiante -->
<div class="page-header" style="margin-top: 8px;">
    <div class="page-header-left">
        <h1 style="font-size: var(--text-lg);">Entregas</h1>
        <p>Historial de intentos de este estudiante</p>
    </div>
</div>

<div class="table-container animate-in">
    <table class="data-table">
        <thead>
            <tr>
                <th class="sortable" data-sort="caso">Caso</th>
                <th class="sortable" data-sort="asignacion">Asignación</th>
                <th class="sortable" data-sort="intento">Intento</th>
                <th class="sortable" data-sort="fecha">Fecha</th>
                <th class="sortable" data-sort="estado">Estado</th>
                <th class="sortable" data-sort="nota">Nota</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($entregas)): ?>
                <tr>
                    <td colspan="7" class="empty-cell">
                        <div class="empty-state empty-state--blue">
                            <div class="empty-state-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round">
                                    <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2" />
                                    <rect x="8" y="2" width="8" height="4" rx="1" ry="1" />
                                </svg>
                            </div>
                            <h3>Sin entregas</h3>
                            <p>Este estudiante aún no ha realizado ningún intento.</p>
                        </div>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($entregas as $entrega):
                    $statusClass = match ($entrega['estado']) {
                        'Enviado' => 'status-enviado',
                        'En Progreso' => 'status-progreso',
                        'Calificado' => 'status-calificado',
                        default => 'status-enviado'
                    };
                    $notaText = $entrega['nota'] !== null ? number_format($entrega['nota'], 1) : '—';
                    $notaClass = '';
                    if ($entrega['nota'] !== null) {
                        $notaClass = $entrega['nota'] >= 10 ? 'promedio-pass' : 'promedio-fail';
                    } else {
                        $notaClass = 'promedio-na';
                    }
                    ?>
                    <tr>
                        <td><span class="caso-tag">
                                <?= htmlspecialchars($entrega['caso_titulo']) ?>
                            </span></td>
                        <td><span class="asignacion-tag">
                                <?= htmlspecialchars($entrega['asignacion_nombre']) ?>
                            </span></td>
                        <td><span class="intento-display">#
                                <?= $entrega['intento_actual'] ?> de
                                <?= $entrega['intento_max'] ?>
                            </span></td>
                        <td>
                            <?= date('d/m/Y', strtotime($entrega['created_at'])) ?>
                        </td>
                        <td><span class="status-badge <?= $statusClass ?>">
                                <?= htmlspecialchars($entrega['estado']) ?>
                            </span></td>
                        <td><span class="promedio-cell <?= $notaClass ?>">
                                <?= $notaText ?>
                            </span></td>
                        <td>
                            <a href="#" class="ver-link">
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
            <?php endif; ?>
        </tbody>
    </table>

    <div class="table-footer">
        <div class="table-footer-info">
            Mostrando <strong>
                <?= count($entregas) ?>
            </strong> entregas
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/logged_layout.php';
?>