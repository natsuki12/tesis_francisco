<?php
declare(strict_types=1);

// ARCHIVO: resources/views/professor/casos_sucesorales.php

// 1. Configuración de la Vista
$pageTitle  = 'Casos Sucesorales — Simulador SENIAT';
$activePage = 'casos-sucesorales';

// 2. CSS específico
$extraCss = '<link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700&display=swap" rel="stylesheet">';
$extraCss .= '<link rel="stylesheet" href="' . asset('css/professor/casos_sucesorales.css') . '">';

ob_start();
?>

  <!-- Page Header -->
  <div class="page-header">
    <div class="page-header-left">
      <h1>Casos Sucesorales</h1>
      <p>Gestiona los casos de estudio para prácticas guiadas y libres del proceso de declaración sucesoral.</p>
    </div>
    <a href="<?= base_url('/crear-caso') ?>" class="btn btn-primary">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Nuevo Caso
    </a>
  </div>

  <!-- Stats Row -->
  <div class="stats-row">
    <div class="stat-card">
      <div class="stat-icon blue">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
      </div>
      <div class="stat-info">
        <div class="stat-value">12</div>
        <div class="stat-label">Total de Casos</div>
      </div>
    </div>

    <div class="stat-card">
      <div class="stat-icon green">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
      </div>
      <div class="stat-info">
        <div class="stat-value">7</div>
        <div class="stat-label">Completados</div>
      </div>
    </div>

    <div class="stat-card">
      <div class="stat-icon amber">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
      </div>
      <div class="stat-info">
        <div class="stat-value">3</div>
        <div class="stat-label">En Progreso</div>
      </div>
    </div>

    <div class="stat-card">
      <div class="stat-icon purple">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      </div>
      <div class="stat-info">
        <div class="stat-value">18</div>
        <div class="stat-label">Estudiantes Asignados</div>
      </div>
    </div>
  </div>

  <!-- Toolbar -->
  <div class="toolbar">
    <div class="toolbar-left">
      <div class="search-box">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
        <input type="text" placeholder="Buscar por causante, cédula o N° de caso...">
      </div>

      <button class="filter-chip active">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
        Todos
      </button>
      <button class="filter-chip">Borrador</button>
      <button class="filter-chip">Activos</button>
      <button class="filter-chip">Completados</button>
    </div>

    <div class="toolbar-right">
      <button class="btn btn-secondary btn-sm">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
        Exportar
      </button>
      <div class="view-toggle">
        <button class="active" aria-label="Vista tabla">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
        </button>
        <button aria-label="Vista tarjetas">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
        </button>
      </div>
    </div>
  </div>

  <!-- Data Table -->
  <div class="table-container">
    <table class="data-table">
      <thead>
        <tr>
          <th class="checkbox-cell"><input type="checkbox" class="custom-check"></th>
          <th class="sortable">N° Caso</th>
          <th class="sortable">Causante</th>
          <th>Herederos</th>
          <th class="sortable">Patrimonio Neto</th>
          <th>Tipo</th>
          <th class="sortable">Estado</th>
          <th class="sortable">Fecha</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <!-- Row 1 -->
        <tr>
          <td class="checkbox-cell"><input type="checkbox" class="custom-check"></td>
          <td><span class="case-id">CS-2025-001</span></td>
          <td>
            <div class="causante-cell">
              <div class="causante-avatar m">JR</div>
              <div class="causante-info">
                <div class="causante-name">José Rafael Mendoza</div>
                <div class="causante-ci">V-8.456.321</div>
              </div>
            </div>
          </td>
          <td>
            <div class="herederos-count">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
              4
            </div>
          </td>
          <td><span class="patrimonio-value">Bs. 2.450.000,00</span></td>
          <td><span class="practice-type practice-guided">Guiada</span></td>
          <td><span class="status-badge status-completed">Completado</span></td>
          <td>
            <div class="date-cell">
              15/01/2025
              <div class="date-relative">hace 45 días</div>
            </div>
          </td>
          <td>
            <div class="row-actions">
              <button class="row-action-btn" title="Ver detalle">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              </button>
              <button class="row-action-btn" title="Editar">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
              </button>
              <button class="row-action-btn" title="Duplicar">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
              </button>
              <button class="row-action-btn danger" title="Eliminar">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
              </button>
            </div>
          </td>
        </tr>

        <!-- Row 2 -->
        <tr>
          <td class="checkbox-cell"><input type="checkbox" class="custom-check"></td>
          <td><span class="case-id">CS-2025-002</span></td>
          <td>
            <div class="causante-cell">
              <div class="causante-avatar f">MC</div>
              <div class="causante-info">
                <div class="causante-name">María Carmen López</div>
                <div class="causante-ci">V-6.789.012</div>
              </div>
            </div>
          </td>
          <td>
            <div class="herederos-count">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
              6
            </div>
          </td>
          <td><span class="patrimonio-value">Bs. 8.120.500,00</span></td>
          <td><span class="practice-type practice-guided">Guiada</span></td>
          <td><span class="status-badge status-active">Activo</span></td>
          <td>
            <div class="date-cell">
              28/01/2025
              <div class="date-relative">hace 32 días</div>
            </div>
          </td>
          <td>
            <div class="row-actions">
              <button class="row-action-btn" title="Ver detalle">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              </button>
              <button class="row-action-btn" title="Editar">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
              </button>
              <button class="row-action-btn" title="Duplicar">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
              </button>
              <button class="row-action-btn danger" title="Eliminar">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
              </button>
            </div>
          </td>
        </tr>

        <!-- Row 3 -->
        <tr>
          <td class="checkbox-cell"><input type="checkbox" class="custom-check"></td>
          <td><span class="case-id">CS-2025-003</span></td>
          <td>
            <div class="causante-cell">
              <div class="causante-avatar m">LP</div>
              <div class="causante-info">
                <div class="causante-name">Luis Pérez Guzmán</div>
                <div class="causante-ci">V-10.234.567</div>
              </div>
            </div>
          </td>
          <td>
            <div class="herederos-count">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
              2
            </div>
          </td>
          <td><span class="patrimonio-value">Bs. 1.875.000,00</span></td>
          <td><span class="practice-type practice-free">Libre</span></td>
          <td><span class="status-badge status-review">En Revisión</span></td>
          <td>
            <div class="date-cell">
              05/02/2025
              <div class="date-relative">hace 24 días</div>
            </div>
          </td>
          <td>
            <div class="row-actions">
              <button class="row-action-btn" title="Ver detalle">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              </button>
              <button class="row-action-btn" title="Editar">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
              </button>
              <button class="row-action-btn" title="Duplicar">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
              </button>
              <button class="row-action-btn danger" title="Eliminar">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
              </button>
            </div>
          </td>
        </tr>

        <!-- Row 4 -->
        <tr>
          <td class="checkbox-cell"><input type="checkbox" class="custom-check"></td>
          <td><span class="case-id">CS-2025-004</span></td>
          <td>
            <div class="causante-cell">
              <div class="causante-avatar f">AR</div>
              <div class="causante-info">
                <div class="causante-name">Ana Rosa Bermúdez</div>
                <div class="causante-ci">V-5.678.901</div>
              </div>
            </div>
          </td>
          <td>
            <div class="herederos-count">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
              3
            </div>
          </td>
          <td><span class="patrimonio-value">Bs. 15.340.000,00</span></td>
          <td><span class="practice-type practice-guided">Guiada</span></td>
          <td><span class="status-badge status-active">Activo</span></td>
          <td>
            <div class="date-cell">
              10/02/2025
              <div class="date-relative">hace 19 días</div>
            </div>
          </td>
          <td>
            <div class="row-actions">
              <button class="row-action-btn" title="Ver detalle">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              </button>
              <button class="row-action-btn" title="Editar">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
              </button>
              <button class="row-action-btn" title="Duplicar">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
              </button>
              <button class="row-action-btn danger" title="Eliminar">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
              </button>
            </div>
          </td>
        </tr>

        <!-- Row 5 -->
        <tr>
          <td class="checkbox-cell"><input type="checkbox" class="custom-check"></td>
          <td><span class="case-id">CS-2025-005</span></td>
          <td>
            <div class="causante-cell">
              <div class="causante-avatar m">CR</div>
              <div class="causante-info">
                <div class="causante-name">Carlos Rivero Torres</div>
                <div class="causante-ci">V-12.345.678</div>
              </div>
            </div>
          </td>
          <td>
            <div class="herederos-count">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
              5
            </div>
          </td>
          <td><span class="patrimonio-value">Bs. 4.560.250,00</span></td>
          <td><span class="practice-type practice-free">Libre</span></td>
          <td><span class="status-badge status-draft">Borrador</span></td>
          <td>
            <div class="date-cell">
              18/02/2025
              <div class="date-relative">hace 11 días</div>
            </div>
          </td>
          <td>
            <div class="row-actions">
              <button class="row-action-btn" title="Ver detalle">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              </button>
              <button class="row-action-btn" title="Editar">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
              </button>
              <button class="row-action-btn" title="Duplicar">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
              </button>
              <button class="row-action-btn danger" title="Eliminar">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
              </button>
            </div>
          </td>
        </tr>

        <!-- Row 6 -->
        <tr>
          <td class="checkbox-cell"><input type="checkbox" class="custom-check"></td>
          <td><span class="case-id">CS-2025-006</span></td>
          <td>
            <div class="causante-cell">
              <div class="causante-avatar f">EG</div>
              <div class="causante-info">
                <div class="causante-name">Elena Gómez Salazar</div>
                <div class="causante-ci">V-7.890.123</div>
              </div>
            </div>
          </td>
          <td>
            <div class="herederos-count">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
              7
            </div>
          </td>
          <td><span class="patrimonio-value">Bs. 22.100.000,00</span></td>
          <td><span class="practice-type practice-guided">Guiada</span></td>
          <td><span class="status-badge status-completed">Completado</span></td>
          <td>
            <div class="date-cell">
              22/02/2025
              <div class="date-relative">hace 7 días</div>
            </div>
          </td>
          <td>
            <div class="row-actions">
              <button class="row-action-btn" title="Ver detalle">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              </button>
              <button class="row-action-btn" title="Editar">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
              </button>
              <button class="row-action-btn" title="Duplicar">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
              </button>
              <button class="row-action-btn danger" title="Eliminar">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
              </button>
            </div>
          </td>
        </tr>

      </tbody>
    </table>

    <!-- Table Footer -->
    <div class="table-footer">
      <div class="table-footer-info">
        Mostrando <strong>1-6</strong> de <strong>12</strong> casos
      </div>
      <div class="pagination">
        <button disabled>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="15 18 9 12 15 6"/></svg>
        </button>
        <button class="active">1</button>
        <button>2</button>
        <button>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="9 18 15 12 9 6"/></svg>
        </button>
      </div>
    </div>
  </div>



<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/logged_layout.php';
?>
