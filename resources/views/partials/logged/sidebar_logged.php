<?php
// Placeholders para imágenes
$logoMain = $logoMain ?? asset('img/brand/logo-small.png');
$phLogo = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI0OCIgaGVpZ2h0PSI0OCIgdmlld0JveD0iMCAwIDQ4IDQ4Ij4KICA8cmVjdCB3aWR0aD0iNDgiIGhlaWdodD0iNDgiIGZpbGw9IiMwYjVhYTYiIHJ4PSI0Ii8+CiAgPHRleHQgeD0iMjQiIHk9IjI4IiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTYiIGZvbnQtd2VpZ2h0PSJib2xkIiBmaWxsPSIjZmZmIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIj5VTTwvdGV4dD4KPC9zdmc+';
$activePage = $activePage ?? '';
$role = $_SESSION['role_id'] ?? 3;
?>
<!-- SIDEBAR -->
<aside class="sim-sidebar" id="sidebar">
    <div class="sim-sidebar__header">
        <div class="sim-sidebar__brand">
            <img 
                class="sim-sidebar__logo" 
                src="<?= $logoMain ?>" 
                alt="Logo"
                onerror="this.onerror=null;this.src='<?= $phLogo ?>';"
            >
            <div class="sim-sidebar__brand-text">
                <div class="sim-sidebar__brand-name">Nombre_Sistema</div>
                <div class="sim-sidebar__brand-sub">Universidad de Margarita</div>
            </div>
        </div>
    </div>

    <nav class="sim-nav">
        <!-- ============================================================= -->
        <!-- SECCIÓN: PRINCIPAL (Todos los roles)                          -->
        <!-- ============================================================= -->
        <div class="sim-nav__section">
            <span class="sim-nav__section-title">PRINCIPAL</span>
            <a href="<?= base_url('/home') ?>" class="sim-nav__link <?= $activePage === 'inicio' ? 'sim-nav__link--active' : '' ?>">
                <span class="sim-nav__icon">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                </span>
                <span class="sim-nav__text">Inicio</span>
            </a>
        </div>

        <?php if ($role === 1): ?>
        <!-- ============================================================= -->
        <!-- SECCIÓN: ADMINISTRACIÓN (Solo Admin)                          -->
        <!-- ============================================================= -->
        <div class="sim-nav__section">
            <span class="sim-nav__section-title">ADMINISTRACIÓN</span>
            <a href="#" class="sim-nav__link <?= $activePage === 'gestion-usuarios' ? 'sim-nav__link--active' : '' ?>">
                <span class="sim-nav__icon">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </span>
                <span class="sim-nav__text">Gestión de Usuarios</span>
            </a>
            <a href="#" class="sim-nav__link <?= $activePage === 'profesores-autorizados' ? 'sim-nav__link--active' : '' ?>">
                <span class="sim-nav__icon">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M20 17a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H9.46c.35.61.54 1.3.54 2h10v11h-9v2h9z"/><path d="M1 11v10l5-3 5 3V11H1z"/></svg>
                </span>
                <span class="sim-nav__text">Profesores Autorizados</span>
            </a>
            <a href="#" class="sim-nav__link <?= $activePage === 'reportes' ? 'sim-nav__link--active' : '' ?>">
                <span class="sim-nav__icon">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                </span>
                <span class="sim-nav__text">Reportes Generales</span>
            </a>
        </div>
        <?php endif; ?>

        <?php if ($role === 2): ?>
        <!-- ============================================================= -->
        <!-- SECCIÓN: SIMULADOR (Profesor)                                 -->
        <!-- ============================================================= -->
        <div class="sim-nav__section">
            <span class="sim-nav__section-title">SIMULADOR</span>
            <a href="<?= base_url('/casos-sucesorales') ?>" class="sim-nav__link <?= $activePage === 'casos-sucesorales' ? 'sim-nav__link--active' : '' ?>">
                <span class="sim-nav__icon">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                </span>
                <span class="sim-nav__text">Casos Sucesorales</span>
            </a>
            <a href="#" class="sim-nav__link <?= $activePage === 'generacion-rif' ? 'sim-nav__link--active' : '' ?>">
                <span class="sim-nav__icon">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                </span>
                <span class="sim-nav__text">Generación de R.S</span>
            </a>
            <a href="#" class="sim-nav__link <?= $activePage === 'historial' ? 'sim-nav__link--active' : '' ?>">
                <span class="sim-nav__icon">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </span>
                <span class="sim-nav__text">Historial</span>
            </a>
        </div>

        <!-- SECCIÓN: ACADÉMICO (Profesor) -->
        <div class="sim-nav__section">
            <span class="sim-nav__section-title">ACADÉMICO</span>
            <a href="#" class="sim-nav__link <?= $activePage === 'mis-estudiantes' ? 'sim-nav__link--active' : '' ?>">
                <span class="sim-nav__icon">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </span>
                <span class="sim-nav__text">Mis Estudiantes</span>
            </a>
            <a href="#" class="sim-nav__link <?= $activePage === 'calificaciones' ? 'sim-nav__link--active' : '' ?>">
                <span class="sim-nav__icon">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                </span>
                <span class="sim-nav__text">Calificaciones</span>
            </a>
            <a href="#" class="sim-nav__link <?= $activePage === 'marco-legal' ? 'sim-nav__link--active' : '' ?>">
                <span class="sim-nav__icon">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                </span>
                <span class="sim-nav__text">Marco Legal</span>
            </a>
        </div>

        <!-- SECCIÓN: REPORTES (Profesor) -->
        <div class="sim-nav__section">
            <span class="sim-nav__section-title">REPORTES</span>
            <a href="#" class="sim-nav__link <?= $activePage === 'estadisticas' ? 'sim-nav__link--active' : '' ?>">
                <span class="sim-nav__icon">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                </span>
                <span class="sim-nav__text">Estadísticas</span>
                <span class="sim-nav__badge">Pronto</span>
            </a>
        </div>
        <?php endif; ?>

        <?php if ($role === 3): ?>
        <!-- ============================================================= -->
        <!-- SECCIÓN: SIMULADOR SENIAT (Estudiante)                        -->
        <!-- ============================================================= -->
        <div class="sim-nav__section">
            <span class="sim-nav__section-title">SIMULADOR SENIAT</span>
            <ul class="sim-nav__list">
                <!-- Nueva Declaración con submenú -->
                <li class="sim-nav__item sim-nav__item--parent <?= in_array($activePage, ['nueva-declaracion', 'modo-guiado', 'practica-libre', 'evaluacion']) ? 'sim-nav__item--expanded' : '' ?>">
                    <button class="sim-nav__link" data-toggle="submenu">
                        <span class="sim-nav__icon">
                            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                        </span>
                        <span class="sim-nav__text">Nueva Declaración</span>
                        <span class="sim-nav__arrow">
                            <svg viewBox="0 0 24 24" width="16" height="16"><path d="M7 10l5 5 5-5z" fill="currentColor"/></svg>
                        </span>
                    </button>
                    
                    <ul class="sim-nav__submenu">
                        <li class="sim-nav__subitem <?= $activePage === 'modo-guiado' ? 'sim-nav__subitem--active' : '' ?>">
                            <a href="#" class="sim-nav__sublink">
                                <span class="sim-nav__subtext">Modo Guiado (Aprender)</span>
                            </a>
                        </li>
                        <li class="sim-nav__subitem <?= $activePage === 'practica-libre' ? 'sim-nav__subitem--active' : '' ?>">
                            <a href="<?= base_url('/step_01_seniat_index') ?>" class="sim-nav__sublink">
                                <span class="sim-nav__subtext">Práctica Libre</span>
                            </a>
                        </li>
                        <li class="sim-nav__subitem <?= $activePage === 'evaluacion' ? 'sim-nav__subitem--active' : '' ?>">
                            <a href="#" class="sim-nav__sublink">
                                <span class="sim-nav__subtext">Evaluación Final</span>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>

            <a href="#" class="sim-nav__link <?= $activePage === 'historial' ? 'sim-nav__link--active' : '' ?>">
                <span class="sim-nav__icon">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </span>
                <span class="sim-nav__text">Historial / Planillas</span>
            </a>
        </div>

        <!-- SECCIÓN: ACADÉMICO (Estudiante) -->
        <div class="sim-nav__section">
            <span class="sim-nav__section-title">ACADÉMICO</span>
            <a href="#" class="sim-nav__link <?= $activePage === 'marco-legal' ? 'sim-nav__link--active' : '' ?>">
                <span class="sim-nav__icon">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                </span>
                <span class="sim-nav__text">Marco Legal</span>
            </a>
            <a href="#" class="sim-nav__link <?= $activePage === 'calificaciones' ? 'sim-nav__link--active' : '' ?>">
                <span class="sim-nav__icon">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                </span>
                <span class="sim-nav__text">Mis Calificaciones</span>
            </a>
        </div>
        <?php endif; ?>
    </nav>

    <!-- ============================================================= -->
    <!-- FOOTER: Perfil & Cerrar Sesión (push to bottom)               -->
    <!-- ============================================================= -->
    <div class="sim-sidebar__footer">
        <a href="<?= base_url('/perfil') ?>" class="sim-nav__link <?= $activePage === 'perfil' ? 'sim-nav__link--active' : '' ?>">
            <span class="sim-nav__icon">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
            </span>
            <span class="sim-nav__text">Perfil</span>
        </a>
        <a href="<?= base_url('/logout') ?>" class="sim-nav__link">
            <span class="sim-nav__icon">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            </span>
            <span class="sim-nav__text">Cerrar Sesión</span>
        </a>
    </div>
</aside>