<?php
declare(strict_types=1);

// ARCHIVO: resources/views/professor/home_professor.php

// 1. Configuración de la Vista
$pageTitle = 'Panel del Profesor — Simulador SENIAT';
$activePage = 'inicio';

// 2. Cargamos el CSS específico (home_professor.css)
$extraCss = '<link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700&display=swap" rel="stylesheet">';
$extraCss .= '<link rel="stylesheet" href="'.asset('css/professor/home_professor.css').'">';

// 3. Datos (Placeholder: Luego vendrán del Controlador)
$userName = $_SESSION['user_name'] ?? 'Profesor';

ob_start();
?>

    <!-- Header -->
    <div class="page-header">
      <h1>Bienvenido, Prof. <?= htmlspecialchars($userName) ?></h1>
      <p>Panel de control del profesor. Gestione sus estudiantes y revise sus declaraciones.</p>
    </div>

    <!-- Stats -->
    <div class="stats-row">
      <div class="stat-card animate-in">
        <div class="stat-card-top">
          <div class="stat-icon blue">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
          </div>
          <span class="stat-trend up">+3</span>
        </div>
        <div class="stat-number">24</div>
        <div class="stat-label">Estudiantes activos</div>
      </div>
      <div class="stat-card animate-in">
        <div class="stat-card-top">
          <div class="stat-icon green">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
          </div>
        </div>
        <div class="stat-number">12</div>
        <div class="stat-label">Declaraciones completadas</div>
      </div>
      <div class="stat-card animate-in">
        <div class="stat-card-top">
          <div class="stat-icon amber">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
          </div>
        </div>
        <div class="stat-number">8</div>
        <div class="stat-label">Pendientes por revisar</div>
      </div>
      <div class="stat-card animate-in">
        <div class="stat-card-top">
          <div class="stat-icon red">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
          </div>
        </div>
        <div class="stat-number">4</div>
        <div class="stat-label">Declaraciones con errores</div>
      </div>
    </div>

    <!-- Action Cards -->
    <div class="action-cards">
      <a class="action-card animate-in" href="#">
        <div class="action-card-icon">
          <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
        </div>
        <h3>Revisar Declaraciones</h3>
        <p>Acceda a las declaraciones sucesorales (Forma 32) realizadas por sus estudiantes para evaluarlas.</p>
        <span class="action-card-link">
          Ver declaraciones
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </span>
      </a>
      <a class="action-card animate-in" href="#">
        <div class="action-card-icon">
          <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
        </div>
        <h3>Mis Estudiantes</h3>
        <p>Consulte la lista de estudiantes asignados, su progreso individual y el estado de sus prácticas.</p>
        <span class="action-card-link">
          Ver estudiantes
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </span>
      </a>
      <div class="action-card disabled animate-in">
        <div class="action-card-icon">
          <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
        </div>
        <h3>Reportes</h3>
        <p>Genere reportes de rendimiento y estadísticas de progreso de sus estudiantes.</p>
        <span class="badge-wip">(Módulo en construcción)</span>
        <span class="btn-disabled">No disponible</span>
      </div>
    </div>

    <!-- Bottom Panels -->
    <div class="bottom-grid">

      <!-- Recent Students -->
      <div class="panel">
        <div class="panel-header">
          <h3>Estudiantes recientes</h3>
          <a href="#">Ver todos</a>
        </div>
        <div class="student-row">
          <div class="student-avatar">CG</div>
          <div class="student-info">
            <div class="student-name">César González</div>
            <div class="student-detail">V-28.456.789 · Derecho</div>
          </div>
          <span class="student-status completed">Completado</span>
        </div>
        <div class="student-row">
          <div class="student-avatar">ML</div>
          <div class="student-info">
            <div class="student-name">María López</div>
            <div class="student-detail">V-29.123.456 · Derecho</div>
          </div>
          <span class="student-status in-progress">En progreso</span>
        </div>
        <div class="student-row">
          <div class="student-avatar">AR</div>
          <div class="student-info">
            <div class="student-name">Andrés Rodríguez</div>
            <div class="student-detail">V-27.890.123 · Derecho</div>
          </div>
          <span class="student-status pending">Pendiente</span>
        </div>
        <div class="student-row">
          <div class="student-avatar">LP</div>
          <div class="student-info">
            <div class="student-name">Laura Pérez</div>
            <div class="student-detail">V-30.567.890 · Derecho</div>
          </div>
          <span class="student-status completed">Completado</span>
        </div>
        <div class="student-row">
          <div class="student-avatar">JM</div>
          <div class="student-info">
            <div class="student-name">José Mendoza</div>
            <div class="student-detail">V-28.234.567 · Derecho</div>
          </div>
          <span class="student-status in-progress">En progreso</span>
        </div>
      </div>

      <!-- Activity Feed -->
      <div class="panel">
        <div class="panel-header">
          <h3>Actividad reciente</h3>
          <a href="#">Ver todo</a>
        </div>
        <div class="activity-item">
          <div class="activity-dot green"></div>
          <div>
            <div class="activity-text"><strong>César González</strong> completó su declaración sucesoral.</div>
            <div class="activity-time">Hace 25 minutos</div>
          </div>
        </div>
        <div class="activity-item">
          <div class="activity-dot blue"></div>
          <div>
            <div class="activity-text"><strong>María López</strong> inició una nueva declaración (Forma 32).</div>
            <div class="activity-time">Hace 1 hora</div>
          </div>
        </div>
        <div class="activity-item">
          <div class="activity-dot amber"></div>
          <div>
            <div class="activity-text"><strong>Andrés Rodríguez</strong> tiene 3 errores en el cálculo de cuota parte.</div>
            <div class="activity-time">Hace 2 horas</div>
          </div>
        </div>
        <div class="activity-item">
          <div class="activity-dot green"></div>
          <div>
            <div class="activity-text"><strong>Laura Pérez</strong> completó el módulo de datos del causante.</div>
            <div class="activity-time">Hace 3 horas</div>
          </div>
        </div>
        <div class="activity-item">
          <div class="activity-dot blue"></div>
          <div>
            <div class="activity-text"><strong>José Mendoza</strong> se registró en la plataforma.</div>
            <div class="activity-time">Ayer, 4:30 PM</div>
          </div>
        </div>
      </div>

    </div>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layouts/logged_layout.php'; 
?>
