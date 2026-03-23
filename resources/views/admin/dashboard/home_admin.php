<?php
declare(strict_types=1);

// ARCHIVO: resources/views/admin/dashboard/home_admin.php

$pageTitle = 'Inicio';
$activePage = 'inicio';
$breadcrumbs = ['Inicio' => '#'];

$extraCss = '<link rel="stylesheet" href="' . asset('css/admin/dashboard.css') . '">';

ob_start();
?>
<div class="admin-dashboard">
    <!-- Encabezado -->
    <div class="admin-page-header">
        <div>
            <h1 class="admin-page-title">Resumen del Sistema</h1>
            <p class="admin-page-subtitle">Bienvenido al panel de administración. Aquí tienes una visión general de la
                plataforma.</p>
        </div>
    </div>

    <!-- Fila Supeior: Tarjetas de Resumen (4) -->
    <div class="admin-stats-row">
        <!-- Usuarios -->
        <div class="admin-stat-card">
            <div class="admin-stat-card__icon bg-blue">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                    <circle cx="9" cy="7" r="4" />
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                    <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                </svg>
            </div>
            <div class="admin-stat-card__info">
                <span class="admin-stat-card__value"><?= (int)($stats['totalUsuarios'] ?? 0) ?></span>
                <span class="admin-stat-card__label">Usuarios registrados</span>
            </div>
        </div>
        <!-- Profesores -->
        <div class="admin-stat-card">
            <div class="admin-stat-card__icon bg-green">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                    <circle cx="12" cy="7" r="4" />
                </svg>
            </div>
            <div class="admin-stat-card__info">
                <span class="admin-stat-card__value"><?= (int)($stats['profesoresActivos'] ?? 0) ?></span>
                <span class="admin-stat-card__label">Profesores activos</span>
            </div>
        </div>
        <!-- Estudiantes -->
        <div class="admin-stat-card">
            <div class="admin-stat-card__icon bg-yellow">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round">
                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" />
                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" />
                </svg>
            </div>
            <div class="admin-stat-card__info">
                <span class="admin-stat-card__value"><?= (int)($stats['estudiantesInscritos'] ?? 0) ?></span>
                <span class="admin-stat-card__label">Estudiantes inscritos</span>
            </div>
        </div>
        <!-- Secciones -->
        <div class="admin-stat-card">
            <div class="admin-stat-card__icon bg-purple">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                    <line x1="16" y1="2" x2="16" y2="6" />
                    <line x1="8" y1="2" x2="8" y2="6" />
                    <line x1="3" y1="10" x2="21" y2="10" />
                </svg>
            </div>
            <div class="admin-stat-card__info">
                <span class="admin-stat-card__value"><?= (int)($stats['seccionesAbiertas'] ?? 0) ?></span>
                <span class="admin-stat-card__label">Secciones abiertas</span>
            </div>
        </div>
    </div>

    <!-- Fila Media: Acceso Rápido (3) -->
    <h3 class="admin-section-title">Accesos Rápidos</h3>
    <div class="admin-quick-links-row">
        <a href="<?= base_url('/admin/profesores') ?>" class="admin-quick-card">
            <div class="admin-quick-card__icon text-blue">
                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                    stroke-linecap="round">
                    <path d="M16 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" />
                    <circle cx="8.5" cy="7" r="4" />
                    <line x1="20" y1="8" x2="20" y2="14" />
                    <line x1="23" y1="11" x2="17" y2="11" />
                </svg>
            </div>
            <div class="admin-quick-card__content">
                <h4>Gestión de Profesores</h4>
                <p>Cree cuentas de profesores y gestione sus credenciales</p>
            </div>
            <div class="admin-quick-card__arrow">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <line x1="5" y1="12" x2="19" y2="12" />
                    <polyline points="12 5 19 12 12 19" />
                </svg>
            </div>
        </a>
        <a href="<?= base_url('/admin/secciones') ?>" class="admin-quick-card">
            <div class="admin-quick-card__icon text-purple">
                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                    stroke-linecap="round">
                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" />
                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" />
                </svg>
            </div>
            <div class="admin-quick-card__content">
                <h4>Secciones</h4>
                <p>Organice las secciones del período actual y asigne profesores</p>
            </div>
            <div class="admin-quick-card__arrow">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <line x1="5" y1="12" x2="19" y2="12" />
                    <polyline points="12 5 19 12 12 19" />
                </svg>
            </div>
        </a>
        <a href="<?= base_url('/admin/configuracion/catalogos') ?>" class="admin-quick-card">
            <div class="admin-quick-card__icon text-green">
                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                    stroke-linecap="round">
                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12" />
                </svg>
            </div>
            <div class="admin-quick-card__content">
                <h4>Catálogos Maestros</h4>
                <p>Actualice unidades tributarias, parentescos y otros catálogos</p>
            </div>
            <div class="admin-quick-card__arrow">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <line x1="5" y1="12" x2="19" y2="12" />
                    <polyline points="12 5 19 12 12 19" />
                </svg>
            </div>
        </a>
    </div>

    <!-- Fila Inferior: 2 Columnas -->
    <div class="admin-bottom-row">
        <!-- Columna Izquierda: Actividad Reciente -->
        <div class="admin-panel">
            <div class="admin-panel__header">
                <h3>Actividad Reciente</h3>
            </div>
            <div class="admin-panel__body">
                <div class="admin-activity-feed">
                    <?php if (empty($actividad)): ?>
                        <p style="color:var(--text-muted, #94a3b8); padding:1rem; text-align:center;">No hay actividad reciente.</p>
                    <?php else: ?>
                        <?php
                        // Mapa de color según nivel de riesgo del evento
                        $dotColorMap = [
                            'info'     => 'bg-blue',
                            'warning'  => 'bg-yellow',
                            'critical' => 'bg-purple',
                        ];
                        foreach ($actividad as $ev):
                            $dotClass = $dotColorMap[$ev['nivel_riesgo'] ?? 'info'] ?? 'bg-blue';

                            // Nombre completo o email intentado
                            $quien = '';
                            if (!empty($ev['nombres'])) {
                                $quien = e($ev['nombres'] . ' ' . $ev['apellidos']);
                            } elseif (!empty($ev['attempted_email'])) {
                                $quien = e($ev['attempted_email']);
                            } else {
                                $quien = 'Usuario desconocido';
                            }

                            // Timestamp relativo
                            $ts    = (int)($ev['created_ts'] ?? 0);
                            $ahora = time();
                            $diff  = $ahora - $ts;
                            if ($diff < 60) {
                                $tiempo = 'Hace un momento';
                            } elseif ($diff < 3600) {
                                $mins = (int)floor($diff / 60);
                                $tiempo = "Hace {$mins} min";
                            } elseif ($diff < 86400) {
                                $hrs = (int)floor($diff / 3600);
                                $tiempo = $hrs === 1 ? 'Hace 1 hora' : "Hace {$hrs} horas";
                            } else {
                                $tiempo = date('d/m/Y, h:i A', $ts);
                            }
                        ?>
                        <div class="admin-activity-item">
                            <div class="admin-activity-item__dot <?= $dotClass ?>"></div>
                            <div class="admin-activity-item__content">
                                <p class="admin-activity-item__text"><strong><?= $quien ?></strong> — <?= e($ev['tipo_descripcion'] ?? '') ?></p>
                                <span class="admin-activity-item__time"><?= $tiempo ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="admin-panel__footer">
                <a href="<?= base_url('/admin/monitoreo/bitacora') ?>" class="admin-link">Ver bitácora completa
                    &rarr;</a>
            </div>
        </div>

        <!-- Columna Derecha: Estado del Sistema -->
        <div class="admin-panel">
            <div class="admin-panel__header">
                <h3>Estado del Sistema</h3>
            </div>
            <div class="admin-panel__body">
                <ul class="admin-status-list">
                    <!-- Base de datos (dinámica) -->
                    <li class="admin-status-item">
                        <div class="admin-status-item__icon <?= $dbStatus ? 'text-green' : 'text-yellow' ?>">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round">
                                <?php if ($dbStatus): ?>
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                                    <polyline points="22 4 12 14.01 9 11.01" />
                                <?php else: ?>
                                    <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                                    <line x1="12" y1="9" x2="12" y2="13" />
                                    <line x1="12" y1="17" x2="12.01" y2="17" />
                                <?php endif; ?>
                            </svg>
                        </div>
                        <div class="admin-status-item__content">
                            <h4 class="admin-status-item__title">Base de datos</h4>
                            <?php if ($dbStatus): ?>
                                <p class="admin-status-item__desc">Conectada — <?= e($dbStatus['db_name']) ?> (<?= e($dbStatus['version']) ?>)</p>
                            <?php else: ?>
                                <p class="admin-status-item__desc">Sin conexión</p>
                            <?php endif; ?>
                        </div>
                    </li>
                    <!-- Período activo (dinámico) -->
                    <li class="admin-status-item">
                        <div class="admin-status-item__icon <?= $periodoActivo ? 'text-green' : 'text-yellow' ?>">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round">
                                <?php if ($periodoActivo): ?>
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                                    <polyline points="22 4 12 14.01 9 11.01" />
                                <?php else: ?>
                                    <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                                    <line x1="12" y1="9" x2="12" y2="13" />
                                    <line x1="12" y1="17" x2="12.01" y2="17" />
                                <?php endif; ?>
                            </svg>
                        </div>
                        <div class="admin-status-item__content">
                            <h4 class="admin-status-item__title">Período activo</h4>
                            <?php if ($periodoActivo): ?>
                                <p class="admin-status-item__desc"><?= e($periodoActivo['nombre']) ?> (<?= date('M d', strtotime($periodoActivo['fecha_inicio'])) ?> – <?= date('M d', strtotime($periodoActivo['fecha_fin'])) ?>)</p>
                            <?php else: ?>
                                <p class="admin-status-item__desc">Sin período activo</p>
                            <?php endif; ?>
                        </div>
                    </li>
                    <!-- Bitácora (dinámica) -->
                    <li class="admin-status-item">
                        <div class="admin-status-item__icon text-green">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                                <polyline points="22 4 12 14.01 9 11.01" />
                            </svg>
                        </div>
                        <div class="admin-status-item__content">
                            <h4 class="admin-status-item__title">Bitácora de accesos</h4>
                            <p class="admin-status-item__desc">Activa — <?= (int)$tiposEventos ?> tipos de eventos configurados</p>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/logged_layout.php';
?>