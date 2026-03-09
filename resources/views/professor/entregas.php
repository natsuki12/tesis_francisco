<?php
declare(strict_types=1);

// ARCHIVO: resources/views/professor/entregas.php

// 1. Configuración de la Vista
$pageTitle = 'Entregas — Simulador SENIAT';
$activePage = 'entregas';

// 2. CSS específico
$extraCss = '<link rel="stylesheet" href="' . asset('css/professor/entregas.css') . '">';

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
        <div class="stat-value">
            <?= $stats['pendientes'] ?? 0 ?>
        </div>
    </div>

    <div class="stat-card stat-card--vertical animate-in">
        <div class="stat-card-top">
            <span class="stat-label">En Progreso</span>
            <div class="stat-icon blue">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <path
                        d="M12 2v4m0 12v4M4.93 4.93l2.83 2.83m8.49 8.49l2.83 2.83M2 12h4m12 0h4M4.93 19.07l2.83-2.83m8.49-8.49l2.83-2.83" />
                </svg>
            </div>
        </div>
        <div class="stat-value">
            <?= $stats['en_progreso'] ?? 0 ?>
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
        <div class="stat-value">
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
        <div class="stat-value">
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
            <input type="text" id="search-entregas" placeholder="Buscar estudiante...">
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

        <div class="filter-dropdown" id="filter-asignacion">
            <button class="filter-btn">
                Asignación
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

        <div class="filter-dropdown" id="filter-fecha">
            <button class="filter-btn">
                Fecha
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
                <th class="sortable" data-sort="asignacion">Asignación</th>
                <th class="sortable" data-sort="intento">Intento</th>
                <th class="sortable" data-sort="fecha">Fecha</th>
                <th class="sortable" data-sort="estado">Estado</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody id="entregas-tbody">
            <?php if (empty($entregas)): ?>
                <tr>
                    <td colspan="8" class="empty-cell">
                        <div class="empty-state empty-state--blue">
                            <div class="empty-state-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round">
                                    <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2" />
                                    <rect x="8" y="2" width="8" height="4" rx="1" ry="1" />
                                </svg>
                            </div>
                            <h3>Sin entregas aún</h3>
                            <p>Cuando tus estudiantes envíen sus intentos, aparecerán aquí para su revisión.</p>
                        </div>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($entregas as $entrega):
                    // Datos del estudiante
                    $nombres = $entrega['estudiante_nombres'] ?? 'Sin nombre';
                    $apellidos = $entrega['estudiante_apellidos'] ?? '';
                    $fullName = trim("{$nombres} {$apellidos}");

                    // Iniciales para avatar
                    preg_match_all('/\b\w/u', $fullName, $matches);
                    $iniciales = mb_strtoupper(implode('', array_slice($matches[0], 0, 2)));

                    $cedula = $entrega['estudiante_cedula']
                        ? ($entrega['estudiante_nacionalidad'] ?? 'V') . '-' . number_format((float) $entrega['estudiante_cedula'], 0, ',', '.')
                        : 'S/C';

                    // Color aleatorio basado en iniciales
                    $avatarColors = ['avatar--blue', 'avatar--green', 'avatar--amber', 'avatar--purple', 'avatar--red'];
                    $colorIdx = crc32($iniciales) % count($avatarColors);
                    $avatarClass = $avatarColors[abs($colorIdx)];

                    // Estado badge
                    $estadoEntrega = $entrega['estado'] ?? 'Enviado';
                    $statusClass = match ($estadoEntrega) {
                        'Enviado' => 'status-enviado',
                        'En Progreso' => 'status-progreso',
                        'Calificado' => 'status-calificado',
                        default => 'status-enviado'
                    };

                    // Fecha
                    $timestamp = strtotime($entrega['created_at'] ?? 'now');
                    $dateFormatted = date('d/m/Y', $timestamp);

                    // Intento
                    $intentoActual = $entrega['intento_actual'] ?? 1;
                    $intentoMax = $entrega['intento_max'] ?? 3;
                    ?>
                    <tr data-estado="<?= htmlspecialchars($estadoEntrega) ?>" data-id="<?= $entrega['id'] ?? '' ?>"
                        data-estudiante="<?= htmlspecialchars(strtolower($fullName)) ?>"
                        data-seccion="<?= htmlspecialchars($entrega['seccion'] ?? '') ?>"
                        data-caso="<?= htmlspecialchars($entrega['caso_titulo'] ?? '') ?>">
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
                        <td>
                            <?= htmlspecialchars($entrega['seccion'] ?? '—') ?>
                        </td>
                        <td><span class="caso-tag">
                                <?= htmlspecialchars($entrega['caso_titulo'] ?? '—') ?>
                            </span></td>
                        <td><span class="asignacion-tag">
                                <?= htmlspecialchars($entrega['asignacion_nombre'] ?? '—') ?>
                            </span></td>
                        <td><span class="intento-display">
                                <?= $intentoActual ?> de
                                <?= $intentoMax ?>
                            </span></td>
                        <td>
                            <?= $dateFormatted ?>
                        </td>
                        <td><span class="status-badge <?= $statusClass ?>">
                                <?= htmlspecialchars($estadoEntrega) ?>
                            </span></td>
                        <td>
                            <a href="<?= base_url('/entregas/' . ($entrega['id'] ?? '')) ?>" class="ver-link">
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

    <!-- Table Footer -->
    <div class="table-footer">
        <div class="table-footer-info">
            Mostrando <strong>
                <?= count($entregas ?? []) ?>
            </strong> de <strong>
                <?= $stats['total'] ?? 0 ?>
            </strong> entregas
        </div>
        <div class="pagination">
            <button disabled>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                    <polyline points="15 18 9 12 15 6" />
                </svg>
            </button>
            <button class="active">1</button>
            <button>2</button>
            <button>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                    <polyline points="9 18 15 12 9 6" />
                </svg>
            </button>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/logged_layout.php';
?>