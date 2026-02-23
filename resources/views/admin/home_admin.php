<?php
declare(strict_types=1);

// ARCHIVO: resources/views/admin/home_admin.php

// 1. Configuración de la Vista
$pageTitle = 'Panel del Administrador — Simulador SENIAT';
$activePage = 'inicio';

// 2. Cargamos el CSS específico
$extraCss = '<link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700&display=swap" rel="stylesheet">';
$extraCss .= '<link rel="stylesheet" href="'.asset('css/admin/home_admin.css').'">';

// 3. Datos (Placeholder: Luego vendrán del Controlador)
$userName = $_SESSION['user_name'] ?? 'Administrador';

ob_start();
?>

    <!-- Header -->
    <div class="page-header">
      <h1>Bienvenido, <?= htmlspecialchars($userName) ?></h1>
      <p>Panel de administración del sistema. Gestione usuarios, profesores y configuración general.</p>
    </div>

    <!-- Stats -->
    <div class="stats-row">
      <div class="stat-card animate-in">
        <div class="stat-card-top">
          <div class="stat-icon blue">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
          </div>
        </div>
        <div class="stat-number">5</div>
        <div class="stat-label">Usuarios registrados</div>
      </div>
      <div class="stat-card animate-in">
        <div class="stat-card-top">
          <div class="stat-icon green">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
          </div>
        </div>
        <div class="stat-number">1</div>
        <div class="stat-label">Profesores activos</div>
      </div>
      <div class="stat-card animate-in">
        <div class="stat-card-top">
          <div class="stat-icon amber">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
          </div>
        </div>
        <div class="stat-number">4</div>
        <div class="stat-label">Estudiantes inscritos</div>
      </div>
      <div class="stat-card animate-in">
        <div class="stat-card-top">
          <div class="stat-icon purple">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
          </div>
        </div>
        <div class="stat-number">1</div>
        <div class="stat-label">Secciones abiertas</div>
      </div>
    </div>

    <!-- Action Cards -->
    <div class="action-cards">
      <a class="action-card animate-in" href="#">
        <div class="action-card-icon">
          <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
        </div>
        <h3>Gestión de Usuarios</h3>
        <p>Administre las cuentas de profesores y estudiantes. Active, desactive o modifique usuarios del sistema.</p>
        <span class="action-card-link">
          Administrar
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </span>
      </a>
      <a class="action-card animate-in" href="#">
        <div class="action-card-icon">
          <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M20 17a2 2 0 002-2V4a2 2 0 00-2-2H9.46c.35.61.54 1.3.54 2h10v11h-9v2h9zM15 7v2H9V7h6zm0 4v2H9v-2h6z"/><path d="M1 11v10l5-3 5 3V11H1zm8 6.12l-3-1.8-3 1.8V13h6v4.12z"/></svg>
        </div>
        <h3>Profesores Autorizados</h3>
        <p>Gestione la lista de profesores autorizados para registrarse en la plataforma y sus permisos.</p>
        <span class="action-card-link">
          Ver lista
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </span>
      </a>
      <div class="action-card disabled animate-in">
        <div class="action-card-icon">
          <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
        </div>
        <h3>Reportes Generales</h3>
        <p>Genere reportes del sistema, estadísticas de uso y actividad de la plataforma.</p>
        <span class="badge-wip">(Módulo en construcción)</span>
        <span class="btn-disabled">No disponible</span>
      </div>
    </div>

    <!-- Bottom Panels -->
    <div class="bottom-grid">

      <!-- Bitácora de Accesos -->
      <div class="panel">
        <div class="panel-header">
          <h3>Actividad del sistema</h3>
          <a href="#">Ver bitácora</a>
        </div>
        <div class="activity-item">
          <div class="activity-dot green"></div>
          <div>
            <div class="activity-text"><strong>Admin</strong> inició sesión en el panel.</div>
            <div class="activity-time">Ahora mismo</div>
          </div>
        </div>
        <div class="activity-item">
          <div class="activity-dot blue"></div>
          <div>
            <div class="activity-text">Se registró un nuevo <strong>estudiante</strong> en la plataforma.</div>
            <div class="activity-time">Hace 2 horas</div>
          </div>
        </div>
        <div class="activity-item">
          <div class="activity-dot amber"></div>
          <div>
            <div class="activity-text">Intento de acceso fallido desde IP <strong>192.168.1.50</strong>.</div>
            <div class="activity-time">Hace 5 horas</div>
          </div>
        </div>
        <div class="activity-item">
          <div class="activity-dot green"></div>
          <div>
            <div class="activity-text">Profesor <strong>César Requena</strong> cerró sesión.</div>
            <div class="activity-time">Ayer, 6:15 PM</div>
          </div>
        </div>
      </div>

      <!-- Configuración Rápida -->
      <div class="panel">
        <div class="panel-header">
          <h3>Estado del sistema</h3>
        </div>
        <div class="config-item">
          <div class="config-icon green">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M8 12l2.5 2.5L16 9"/></svg>
          </div>
          <div>
            <div class="config-title">Base de datos</div>
            <div class="config-status">Conectada — spdss (MariaDB 10.4)</div>
          </div>
        </div>
        <div class="config-item">
          <div class="config-icon green">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M8 12l2.5 2.5L16 9"/></svg>
          </div>
          <div>
            <div class="config-title">Periodo activo</div>
            <div class="config-status">2026-I (Ene 19 – Abr 17)</div>
          </div>
        </div>
        <div class="config-item">
          <div class="config-icon blue">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l2 2"/></svg>
          </div>
          <div>
            <div class="config-title">Bitácora de accesos</div>
            <div class="config-status">Activa — 7 tipos de eventos configurados</div>
          </div>
        </div>
        <div class="config-item">
          <div class="config-icon amber">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
          </div>
          <div>
            <div class="config-title">Módulos pendientes</div>
            <div class="config-status">Reportes Generales — en construcción</div>
          </div>
        </div>
      </div>

    </div>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layouts/logged_layout.php'; 
?>
