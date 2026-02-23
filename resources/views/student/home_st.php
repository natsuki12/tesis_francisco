<?php
declare(strict_types=1);

// ARCHIVO: resources/views/student/home_st.php

// 1. Configuración de la Vista
$pageTitle = 'Inicio Estudiante — Simulador SENIAT';
$activePage = 'inicio';

// 2. Cargamos el CSS específico
$extraCss = '<link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700&display=swap" rel="stylesheet">';
$extraCss .= '<link rel="stylesheet" href="'.asset('css/student/home_st.css').'">';

// 3. Datos (Placeholder: Luego vendrán del Controlador)
$userName = $_SESSION['user_name'] ?? 'Estudiante'; 

ob_start();
?>

    <!-- Header -->
    <div class="page-header">
      <h1>Bienvenido, <?= htmlspecialchars($userName) ?></h1>
      <p>Panel de control del estudiante. Seleccione una acción para continuar.</p>
    </div>

    <!-- Stats -->
    <div class="stats-row">
      <div class="stat-card animate-in">
        <div class="stat-card-top">
          <div class="stat-icon blue">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
          </div>
        </div>
        <div class="stat-number">0</div>
        <div class="stat-label">Declaraciones realizadas</div>
      </div>
      <div class="stat-card animate-in">
        <div class="stat-card-top">
          <div class="stat-icon green">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
          </div>
        </div>
        <div class="stat-number">0</div>
        <div class="stat-label">Completadas con éxito</div>
      </div>
      <div class="stat-card animate-in">
        <div class="stat-card-top">
          <div class="stat-icon amber">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
          </div>
        </div>
        <div class="stat-number">0</div>
        <div class="stat-label">En progreso</div>
      </div>
    </div>

    <!-- Action Cards -->
    <div class="action-cards">
      <a class="action-card animate-in" href="<?= base_url('/simulador_inicio') ?>">
        <div class="action-card-icon">
          <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
        </div>
        <h3>Nueva Declaración</h3>
        <p>Acceda al simulador interactivo para realizar el proceso de declaración sucesoral (Forma 32), cálculos y desglose de herederos.</p>
        <span class="action-card-link">
          Iniciar Simulación
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </span>
      </a>
      <a class="action-card animate-in" href="<?= base_url('/perfil') ?>">
        <div class="action-card-icon">
          <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        </div>
        <h3>Mi Perfil</h3>
        <p>Revise su información personal registrada, datos académicos y gestione la seguridad de su cuenta.</p>
        <span class="action-card-link">
          Ver Datos Personales
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </span>
      </a>
      <div class="action-card disabled animate-in">
        <div class="action-card-icon">
          <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
        <h3>Historial</h3>
        <p>Consulte sus declaraciones anteriores y reportes de evaluación generados por el sistema.</p>
        <span class="badge-wip">(Módulo en construcción)</span>
        <span class="btn-disabled">No disponible</span>
      </div>
    </div>

    <!-- Bottom Panel -->
    <div class="bottom-grid">

      <!-- Quick Guide -->
      <div class="panel">
        <div class="panel-header">
          <h3>Guía rápida</h3>
        </div>
        <div class="guide-item">
          <div class="guide-step">1</div>
          <div>
            <div class="guide-title">Iniciar nueva declaración</div>
            <div class="guide-desc">Acceda al simulador y complete los datos del causante para comenzar.</div>
          </div>
        </div>
        <div class="guide-item">
          <div class="guide-step">2</div>
          <div>
            <div class="guide-title">Completar los formularios</div>
            <div class="guide-desc">Registre los datos de herederos, bienes y cálculos tributarios paso a paso.</div>
          </div>
        </div>
        <div class="guide-item">
          <div class="guide-step">3</div>
          <div>
            <div class="guide-title">Generar la planilla</div>
            <div class="guide-desc">Revise los resultados y genere la Forma 32 para su evaluación.</div>
          </div>
        </div>
        <div class="guide-item">
          <div class="guide-step">4</div>
          <div>
            <div class="guide-title">Esperar calificación</div>
            <div class="guide-desc">Su profesor revisará la declaración y le asignará una calificación.</div>
          </div>
        </div>
      </div>

      <!-- Marco Legal -->
      <div class="panel">
        <div class="panel-header">
          <h3>Marco Legal Relevante</h3>
          <a href="#">Ver todo</a>
        </div>
        <div class="legal-item">
          <div class="legal-icon">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/></svg>
          </div>
          <div>
            <div class="legal-title">Ley de Impuesto sobre Sucesiones</div>
            <div class="legal-desc">Gaceta Oficial N° 5.391 — Base legal para la Forma 32.</div>
          </div>
        </div>
        <div class="legal-item">
          <div class="legal-icon">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/></svg>
          </div>
          <div>
            <div class="legal-title">Código Orgánico Tributario</div>
            <div class="legal-desc">Normativa complementaria sobre deberes formales del contribuyente.</div>
          </div>
        </div>
        <div class="legal-item">
          <div class="legal-icon">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/></svg>
          </div>
          <div>
            <div class="legal-title">Providencia SENIAT</div>
            <div class="legal-desc">Procedimiento para la autoliquidación del impuesto sucesoral.</div>
          </div>
        </div>
      </div>

    </div>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layouts/logged_layout.php'; 
?>