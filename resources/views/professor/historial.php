<?php
declare(strict_types=1);

// ARCHIVO: resources/views/professor/historial.php
// Timeline/feed de actividad reciente del profesor.

$pageTitle = 'Historial — Simulador SENIAT';
$activePage = 'historial';
$extraCss = '<link rel="stylesheet" href="' . asset('css/professor/historial.css') . '">';

// ── Datos Placeholder ──────────────────────────────────────
// Events grouped by date (most recent first)
$eventos = [
    '2026-03-09' => [
        [
            'tipo' => 'intento_calificado',
            'hora' => '10:45',
            'texto' => '<strong>Ana María Martínez</strong> — Intento #2 calificado con <strong>14.5 pts</strong> en Sucesión González Méndez.',
            'enlace' => '#',
        ],
        [
            'tipo' => 'rs_aprobada',
            'hora' => '09:20',
            'texto' => 'Solicitud de R.S. de <strong>Pedro José López</strong> aprobada para Sucesión González Méndez.',
            'enlace' => '#',
        ],
    ],
    '2026-03-08' => [
        [
            'tipo' => 'caso_publicado',
            'hora' => '16:30',
            'texto' => 'Caso <strong>Sucesión Pérez Alvarado</strong> publicado y asignado a secciones 4to A y 4to B.',
            'enlace' => '#',
        ],
        [
            'tipo' => 'rs_rechazada',
            'hora' => '14:15',
            'texto' => 'Solicitud de R.S. de <strong>María José García</strong> rechazada — datos del causante inconsistentes.',
            'enlace' => '#',
        ],
        [
            'tipo' => 'intento_calificado',
            'hora' => '11:00',
            'texto' => '<strong>Luis Enrique Morales</strong> — Intento #1 calificado con <strong>12.0 pts</strong> en Sucesión González Méndez.',
            'enlace' => '#',
        ],
    ],
    '2026-03-07' => [
        [
            'tipo' => 'asignacion_creada',
            'hora' => '18:00',
            'texto' => 'Asignación <strong>"Evaluación parcial 1"</strong> creada para Sucesión González Méndez — Secciones: 4to A, 4to B.',
            'enlace' => '#',
        ],
        [
            'tipo' => 'caso_creado',
            'hora' => '15:20',
            'texto' => 'Caso <strong>Sucesión Ramírez Torres</strong> guardado como borrador.',
            'enlace' => '#',
        ],
    ],
    '2026-03-05' => [
        [
            'tipo' => 'intento_calificado',
            'hora' => '10:30',
            'texto' => '<strong>Valentina Rodríguez</strong> — Intento #1 calificado con <strong>19.0 pts</strong> en Sucesión González Méndez.',
            'enlace' => '#',
        ],
        [
            'tipo' => 'rs_aprobada',
            'hora' => '08:45',
            'texto' => 'Solicitud de R.S. de <strong>Valentina Rodríguez</strong> aprobada para Sucesión González Méndez.',
            'enlace' => '#',
        ],
    ],
];

// Event type configuration
$tipoConfig = [
    'caso_creado' => [
        'dot' => 'timeline-dot--blue',
        'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>',
    ],
    'caso_publicado' => [
        'dot' => 'timeline-dot--green',
        'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>',
    ],
    'intento_calificado' => [
        'dot' => 'timeline-dot--purple',
        'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>',
    ],
    'rs_aprobada' => [
        'dot' => 'timeline-dot--green',
        'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>',
    ],
    'rs_rechazada' => [
        'dot' => 'timeline-dot--red',
        'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>',
    ],
    'asignacion_creada' => [
        'dot' => 'timeline-dot--amber',
        'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>',
    ],
];

function formatDayHeader(string $date): string
{
    $today = date('Y-m-d');
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    if ($date === $today)
        return 'Hoy';
    if ($date === $yesterday)
        return 'Ayer';
    $months = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
    $d = (int) date('d', strtotime($date));
    $m = $months[(int) date('m', strtotime($date)) - 1];
    $y = date('Y', strtotime($date));
    return "$d de $m de $y";
}

ob_start();
?>

<!-- Page Header -->
<div class="page-header">
    <div class="page-header-left">
        <h1>Historial</h1>
        <p>Registro de actividad reciente</p>
    </div>
</div>

<!-- Toolbar -->
<div class="toolbar">
    <div class="toolbar-left">
        <select class="toolbar-select" id="filter-tipo">
            <option value="">Todos los tipos</option>
            <option value="caso_creado">Caso creado</option>
            <option value="caso_publicado">Caso publicado</option>
            <option value="intento_calificado">Intento calificado</option>
            <option value="rs_aprobada">R.S. aprobada</option>
            <option value="rs_rechazada">R.S. rechazada</option>
            <option value="asignacion_creada">Asignación creada</option>
        </select>

        <span class="date-label">Desde</span>
        <input type="date" class="date-input" id="filter-desde" value="2026-03-01">

        <span class="date-label">Hasta</span>
        <input type="date" class="date-input" id="filter-hasta" value="2026-03-09">
    </div>
</div>

<!-- Timeline Container -->
<div class="timeline-container animate-in">
    <?php if (empty($eventos)): ?>
        <div class="empty-state empty-state--blue">
            <div class="empty-state-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <circle cx="12" cy="12" r="10" />
                    <polyline points="12 6 12 12 16 14" />
                </svg>
            </div>
            <h3>Sin actividad registrada</h3>
            <p>Tu historial de acciones aparecerá aquí a medida que uses el sistema.</p>
        </div>
    <?php else: ?>
        <div class="timeline">
            <?php foreach ($eventos as $fecha => $eventosDelDia): ?>
                <div class="timeline-day-header">
                    <?= formatDayHeader($fecha) ?>
                </div>

                <?php foreach ($eventosDelDia as $evt):
                    $config = $tipoConfig[$evt['tipo']] ?? $tipoConfig['caso_creado'];
                    ?>
                    <div class="timeline-item" data-tipo="<?= htmlspecialchars($evt['tipo']) ?>">
                        <div class="timeline-dot <?= $config['dot'] ?>">
                            <?= $config['icon'] ?>
                        </div>

                        <div class="timeline-content">
                            <div class="timeline-text">
                                <?= $evt['texto'] ?>
                            </div>
                            <div class="timeline-meta">
                                <span class="timeline-time">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round">
                                        <circle cx="12" cy="12" r="10" />
                                        <polyline points="12 6 12 12 16 14" />
                                    </svg>
                                    <?= $evt['hora'] ?>
                                </span>
                            </div>
                        </div>

                        <div class="timeline-link">
                            <a href="<?= $evt['enlace'] ?>" class="ver-link">
                                Ver
                                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"
                                    stroke-linecap="round">
                                    <path d="M5 12h14" />
                                    <polyline points="12 5 19 12 12 19" />
                                </svg>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>

        <!-- Load More -->
        <div class="load-more-wrapper">
            <button class="load-more-btn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <polyline points="7 13 12 18 17 13" />
                    <polyline points="7 6 12 11 17 6" />
                </svg>
                Cargar más
            </button>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/logged_layout.php';
?>