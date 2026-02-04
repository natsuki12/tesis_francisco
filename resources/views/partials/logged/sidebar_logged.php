<?php
// Placeholders para imágenes
$logoMain = $logoMain ?? asset('img/brand/logo-small.png');
$phLogo = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI0OCIgaGVpZ2h0PSI0OCIgdmlld0JveD0iMCAwIDQ4IDQ4Ij4KICA8cmVjdCB3aWR0aD0iNDgiIGhlaWdodD0iNDgiIGZpbGw9IiMwYjVhYTYiIHJ4PSI0Ii8+CiAgPHRleHQgeD0iMjQiIHk9IjI4IiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTYiIGZvbnQtd2VpZ2h0PSJib2xkIiBmaWxsPSIjZmZmIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIj5VTTwvdGV4dD4KPC9zdmc+';
$activePage = $activePage ?? '';
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
        <!-- SECCIÓN: PRINCIPAL -->
        <div class="sim-nav__section">
            <span class="sim-nav__section-title">PRINCIPAL</span>
            <ul class="sim-nav__list">
                <li class="sim-nav__item <?= $activePage === 'inicio' ? 'sim-nav__item--active' : '' ?>">
                    <a href="<?= base_url('/simulador_inicio') ?>" class="sim-nav__link">
                        <span class="sim-nav__icon">
                            <svg viewBox="0 0 24 24" width="20" height="20"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z" fill="currentColor"/></svg>
                        </span>
                        <span class="sim-nav__text">Inicio</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- SECCIÓN: SIMULADOR SENIAT -->
        <div class="sim-nav__section">
            <span class="sim-nav__section-title">SIMULADOR SENIAT</span>
            <ul class="sim-nav__list">
                <!-- Nueva Declaración con submenú -->
                <li class="sim-nav__item sim-nav__item--parent <?= in_array($activePage, ['nueva-declaracion', 'modo-guiado', 'practica-libre', 'evaluacion']) ? 'sim-nav__item--expanded' : '' ?>">
                    <button class="sim-nav__link" data-toggle="submenu">
                        <span class="sim-nav__icon">
                            <svg viewBox="0 0 24 24" width="20" height="20"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z" fill="currentColor"/></svg>
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
                            <a href="#" class="sim-nav__sublink">
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

                <!-- Historial / Planillas -->
                <li class="sim-nav__item <?= $activePage === 'historial' ? 'sim-nav__item--active' : '' ?>">
                    <a href="#" class="sim-nav__link">
                        <span class="sim-nav__icon">
                            <svg viewBox="0 0 24 24" width="20" height="20"><path d="M13 3c-4.97 0-9 4.03-9 9H1l3.89 3.89.07.14L9 12H6c0-3.87 3.13-7 7-7s7 3.13 7 7-3.13 7-7 7c-1.93 0-3.68-.79-4.94-2.06l-1.42 1.42C8.27 19.99 10.51 21 13 21c4.97 0 9-4.03 9-9s-4.03-9-9-9zm-1 5v5l4.28 2.54.72-1.21-3.5-2.08V8H12z" fill="currentColor"/></svg>
                        </span>
                        <span class="sim-nav__text">Historial / Planillas</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- SECCIÓN: ACADÉMICO -->
        <div class="sim-nav__section">
            <span class="sim-nav__section-title">ACADÉMICO</span>
            <ul class="sim-nav__list">
                <li class="sim-nav__item <?= $activePage === 'marco-legal' ? 'sim-nav__item--active' : '' ?>">
                    <a href="#" class="sim-nav__link">
                        <span class="sim-nav__icon">
                            <svg viewBox="0 0 24 24" width="20" height="20"><path d="M18 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zM6 4h5v8l-2.5-1.5L6 12V4z" fill="currentColor"/></svg>
                        </span>
                        <span class="sim-nav__text">Marco Legal</span>
                    </a>
                </li>
                <li class="sim-nav__item <?= $activePage === 'calificaciones' ? 'sim-nav__item--active' : '' ?>">
                    <a href="#" class="sim-nav__link">
                        <span class="sim-nav__icon">
                            <svg viewBox="0 0 24 24" width="20" height="20"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z" fill="currentColor"/></svg>
                        </span>
                        <span class="sim-nav__text">Mis Calificaciones</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- SECCIÓN: CUENTA -->
        <div class="sim-nav__section">
            <span class="sim-nav__section-title">CUENTA</span>
            <ul class="sim-nav__list">
                <li class="sim-nav__item <?= $activePage === 'perfil' ? 'sim-nav__item--active' : '' ?>">
                    <a href="<?= base_url('/perfil') ?>" class="sim-nav__link">
                        <span class="sim-nav__icon">
                            <svg viewBox="0 0 24 24" width="20" height="20"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" fill="currentColor"/></svg>
                        </span>
                        <span class="sim-nav__text">Perfil</span>
                    </a>
                </li>
                <li class="sim-nav__item">
                    <a href="#" class="sim-nav__link">
                        <span class="sim-nav__icon">
                            <svg viewBox="0 0 24 24" width="20" height="20"><path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z" fill="currentColor"/></svg>
                        </span>
                        <span class="sim-nav__text">Cerrar Sesión</span>
                    </a>
                </li>
            </ul>
        </div>
    </nav>
</aside>