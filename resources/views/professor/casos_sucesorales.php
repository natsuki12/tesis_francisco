<?php
declare(strict_types=1);

// ARCHIVO: resources/views/professor/casos_sucesorales.php

// 1. Configuración de la Vista
$pageTitle = 'Casos Sucesorales — Simulador SENIAT';
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
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
      stroke-linecap="round">
      <line x1="12" y1="5" x2="12" y2="19" />
      <line x1="5" y1="12" x2="19" y2="12" />
    </svg>
    Nuevo Caso
  </a>
</div>

<!-- Stats Row -->
<div class="stats-row">
  <div class="stat-card">
    <div class="stat-icon blue">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
        <polyline points="14 2 14 8 20 8" />
      </svg>
    </div>
    <div class="stat-info">
      <div class="stat-value"><?= $stats['total_casos'] ?? 0 ?></div>
      <div class="stat-label">Total de Casos</div>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon green">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
        <polyline points="22 4 12 14.01 9 11.01" />
      </svg>
    </div>
    <div class="stat-info">
      <div class="stat-value"><?= $stats['casos_publicados'] ?? 0 ?></div>
      <div class="stat-label">Completados</div>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon amber">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
        <circle cx="12" cy="12" r="10" />
        <polyline points="12 6 12 12 16 14" />
      </svg>
    </div>
    <div class="stat-info">
      <div class="stat-value"><?= $stats['casos_borrador'] ?? 0 ?></div>
      <div class="stat-label">En Borrador</div>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon purple">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
        <circle cx="9" cy="7" r="4" />
        <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
        <path d="M16 3.13a4 4 0 0 1 0 7.75" />
      </svg>
    </div>
    <div class="stat-info">
      <div class="stat-value"><?= $stats['estudiantes_asignados'] ?? 0 ?></div>
      <div class="stat-label">Estudiantes Asignados</div>
    </div>
  </div>
</div>

<!-- Toolbar -->
<div class="toolbar">
  <div class="toolbar-left">
    <div class="search-box">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
        <circle cx="11" cy="11" r="8" />
        <path d="m21 21-4.35-4.35" />
      </svg>
      <input type="text" placeholder="Buscar por causante, cédula o N° de caso...">
    </div>

    <button class="filter-chip active">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
        <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3" />
      </svg>
      Todos
    </button>
    <button class="filter-chip">Borrador</button>
    <button class="filter-chip">Activos</button>
    <button class="filter-chip">Completados</button>
  </div>

  <div class="toolbar-right">
    <button class="btn btn-secondary btn-sm">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
        stroke-linecap="round">
        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
        <polyline points="7 10 12 15 17 10" />
        <line x1="12" y1="15" x2="12" y2="3" />
      </svg>
      Exportar
    </button>
    <div class="view-toggle">
      <button class="active" aria-label="Vista tabla">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
          <line x1="8" y1="6" x2="21" y2="6" />
          <line x1="8" y1="12" x2="21" y2="12" />
          <line x1="8" y1="18" x2="21" y2="18" />
          <line x1="3" y1="6" x2="3.01" y2="6" />
          <line x1="3" y1="12" x2="3.01" y2="12" />
          <line x1="3" y1="18" x2="3.01" y2="18" />
        </svg>
      </button>
      <button aria-label="Vista tarjetas">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
          <rect x="3" y="3" width="7" height="7" />
          <rect x="14" y="3" width="7" height="7" />
          <rect x="14" y="14" width="7" height="7" />
          <rect x="3" y="14" width="7" height="7" />
        </svg>
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
      <?php if (empty($casos)): ?>
        <tr>
          <td colspan="9" style="text-align: center; padding: 2rem;">No tienes casos creados aún. <a
              href="<?= base_url('/crear-caso') ?>">Crear el primero</a></td>
        </tr>
      <?php else: ?>
        <?php foreach ($casos as $caso):
          // 1. Número de caso formato CS-YYYY-001
          $year = date('Y', strtotime($caso['created_at']));
          $caseNum = str_pad((string) $caso['id'], 3, "0", STR_PAD_LEFT);
          $caseId = "CS-{$year}-{$caseNum}";

          // 2. Nombre del causante e iniciales
          $nombres = $caso['causante_nombres'] ?? 'Sin Causante';
          $apellidos = $caso['causante_apellidos'] ?? '';
          $fullName = trim("{$nombres} {$apellidos}");

          $iniciales = '';
          if (empty($nombres) && empty($apellidos)) {
            $iniciales = 'SC'; // Sin Causante
          } else {
            preg_match_all('/\b\w/u', $fullName, $matches);
            $iniciales = mb_strtoupper(implode('', array_slice($matches[0], 0, 2)));
          }

          $cedula = $caso['causante_cedula'] ? ($caso['causante_nacionalidad'] ?? 'V') . '-' . number_format((float) $caso['causante_cedula'], 0, ',', '.') : 'S/C';

          // 3. Patrimonio Neto (Monto base, en el futuro se calculará real desde BD)
          // Por ahora pondremos un monto base a 0 si es borrador
          $patrimonio = 'Bs. 0,00';

          // 4. Modalidad del caso
          $modalidadClase = ($caso['modalidad'] === 'Practica_Libre') ? 'practice-free' : 'practice-guided';
          $modalidadTexto = ($caso['modalidad'] === 'Practica_Libre') ? 'Libre' : 'Evaluación';
          if (empty($caso['modalidad'])) {
            $modalidadClase = 'practice-guided';
            $modalidadTexto = 'Borrador';
          }

          // 5. Estado badge
          $statusClass = match ($caso['estado']) {
            'Publicado' => 'status-active', // O completed si es el diseño
            'Borrador' => 'status-draft',
            'Inactivo' => 'status-review',
            default => 'status-draft'
          };

          // 6. Fecha y Tiempo Relativo
          $timestamp = strtotime($caso['created_at']);
          $dateFormatted = date('d/m/Y', $timestamp);
          $daysDiff = floor((time() - $timestamp) / (60 * 60 * 24));

          if ($daysDiff == 0)
            $dateRelative = "hoy";
          elseif ($daysDiff == 1)
            $dateRelative = "ayer";
          else
            $dateRelative = "hace {$daysDiff} días";
          ?>
          <tr>
            <td class="checkbox-cell"><input type="checkbox" class="custom-check"></td>
            <td><span class="case-id"><?= htmlspecialchars($caseId) ?></span></td>
            <td>
              <div class="causante-cell">
                <div class="causante-avatar m"><?= htmlspecialchars($iniciales) ?></div>
                <div class="causante-info">
                  <div class="causante-name" title="<?= htmlspecialchars($caso['titulo']) ?>">
                    <?= htmlspecialchars($fullName) ?></div>
                  <div class="causante-ci"><?= htmlspecialchars($cedula) ?></div>
                </div>
              </div>
            </td>
            <td>
              <div class="herederos-count">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                  <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                  <circle cx="9" cy="7" r="4" />
                </svg>
                <?= (int) $caso['herederos_count'] ?>
              </div>
            </td>
            <td><span class="patrimonio-value"><?= $patrimonio ?></span></td>
            <td><span class="practice-type <?= $modalidadClase ?>"><?= $modalidadTexto ?></span></td>
            <td><span class="status-badge <?= $statusClass ?>"><?= htmlspecialchars($caso['estado']) ?></span></td>
            <td>
              <div class="date-cell">
                <?= $dateFormatted ?>
                <div class="date-relative"><?= $dateRelative ?></div>
              </div>
            </td>
            <td>
              <div class="row-actions">
                <button class="row-action-btn" title="Ver detalle">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                    <circle cx="12" cy="12" r="3" />
                  </svg>
                </button>
                <button class="row-action-btn" title="Editar">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                  </svg>
                </button>
                <button class="row-action-btn" title="Duplicar">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <rect x="9" y="9" width="13" height="13" rx="2" ry="2" />
                    <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" />
                  </svg>
                </button>
                <button class="row-action-btn danger" title="Eliminar">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <polyline points="3 6 5 6 21 6" />
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                  </svg>
                </button>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>

    </tbody>
  </table>

  <!-- Table Footer -->
  <div class="table-footer">
    <div class="table-footer-info">
      Mostrando <strong><?= count($casos) ?></strong> casos
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
require __DIR__ . '/../layouts/logged_layout.php';
?>