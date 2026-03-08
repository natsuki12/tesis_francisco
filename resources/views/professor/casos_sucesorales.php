<?php
declare(strict_types=1);

// ARCHIVO: resources/views/professor/casos_sucesorales.php

// 1. Configuración de la Vista
$pageTitle = 'Casos Sucesorales — Simulador SENIAT';
$activePage = 'casos-sucesorales';

// 2. CSS específico
$extraCss = '<link rel="stylesheet" href="' . asset('css/professor/casos_sucesorales.css') . '">';

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
      <input type="text" id="search-casos" placeholder="Buscar por causante, cédula o N° de caso...">
    </div>

    <button class="filter-chip active" data-filter="Todos">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
        <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3" />
      </svg>
      Todos
    </button>
    <button class="filter-chip" data-filter="Borrador">Borrador</button>
    <button class="filter-chip" data-filter="Publicado">Activos</button>
    <button class="filter-chip" data-filter="Inactivo">Inactivos</button>
  </div>

  <div class="toolbar-right">
    <button class="btn btn-secondary btn-sm" disabled style="opacity:0.5;cursor:not-allowed;">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
        stroke-linecap="round">
        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
        <polyline points="7 10 12 15 17 10" />
        <line x1="12" y1="15" x2="12" y2="3" />
      </svg>
      Exportar
    </button>
  </div>
</div>

<!-- Data Table -->
<div class="table-container">
  <table class="data-table">
    <thead>
      <tr>
        <th class="checkbox-cell"><input type="checkbox" class="custom-check" id="check-all"></th>
        <th class="sortable" data-sort="id">N° Caso</th>
        <th class="sortable" data-sort="titulo">Nombre Caso</th>
        <th class="sortable" data-sort="causante">Causante</th>
        <th>Herederos</th>
        <th class="sortable" data-sort="patrimonio">Patrimonio Neto</th>
        <th>Tipo</th>
        <th class="sortable" data-sort="estado">Estado</th>
        <th class="sortable" data-sort="fecha">Fecha</th>
        <th></th>
      </tr>
    </thead>
    <tbody id="casos-tbody">
      <?php if (empty($casos)): ?>
        <tr>
          <td colspan="10" style="text-align: center; padding: 2rem;">No tienes casos creados aún. <a
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
          <tr data-estado="<?= htmlspecialchars($caso['estado']) ?>" data-id="<?= $caso['id'] ?>"
            data-titulo="<?= htmlspecialchars(strtolower($caso['titulo'] ?? '')) ?>"
            data-causante="<?= htmlspecialchars(strtolower($fullName)) ?>"
            data-cedula="<?= htmlspecialchars($caso['causante_cedula'] ?? '') ?>" data-fecha="<?= $caso['created_at'] ?>">
            <td class="checkbox-cell"><input type="checkbox" class="custom-check row-check"></td>
            <td><a href="<?= base_url('/casos-sucesorales/' . $caso['id']) ?>" class="case-id"
                style="text-decoration: none; color: inherit;"><?= htmlspecialchars($caseId) ?></a></td>
            <td><?= htmlspecialchars($caso['titulo'] ?? 'Sin título') ?></td>
            <td>
              <div class="causante-cell">
                <div class="causante-avatar m"><?= htmlspecialchars($iniciales) ?></div>
                <div class="causante-info">
                  <div class="causante-name" title="<?= htmlspecialchars($caso['titulo']) ?>">
                    <?= htmlspecialchars($fullName) ?>
                  </div>
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
                <a href="<?= base_url('/casos-sucesorales/' . $caso['id']) ?>" class="row-action-btn"
                  title="Gestionar Caso">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                    <circle cx="12" cy="12" r="3" />
                  </svg>
                </a>
                <?php if ($caso['estado'] === 'Borrador'): ?>
                  <a href="<?= base_url('/crear-caso?edit=' . $caso['id']) ?>" class="row-action-btn" title="Editar">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                      <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                      <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                    </svg>
                  </a>
                <?php elseif ($caso['estado'] === 'Inactivo'): ?>
                  <button class="row-action-btn btn-reactivar-caso" data-caso-id="<?= $caso['id'] ?>" title="Reactivar">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                      <polyline points="23 4 23 10 17 10" />
                      <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10" />
                    </svg>
                  </button>
                <?php elseif ($caso['estado'] === 'Publicado'): ?>
                  <button class="row-action-btn btn-desactivar-caso" data-caso-id="<?= $caso['id'] ?>" title="Desactivar">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                      <circle cx="12" cy="12" r="10" />
                      <line x1="4.93" y1="4.93" x2="19.07" y2="19.07" />
                    </svg>
                  </button>
                <?php endif; ?>
                <button class="row-action-btn danger btn-delete-caso" data-caso-id="<?= $caso['id'] ?>"
                  data-caso-titulo="<?= htmlspecialchars($caso['titulo'] ?? 'Sin título') ?>"
                  data-caso-estado="<?= htmlspecialchars($caso['estado']) ?>" title="Eliminar">
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



<script>
  (function () {
    const params = new URLSearchParams(window.location.search);
    if (params.get('borrador') !== 'ok') return;
    history.replaceState(null, '', window.location.pathname);

    const toast = document.createElement('div');
    toast.textContent = '✅ Borrador guardado exitosamente.';
    Object.assign(toast.style, {
      position: 'fixed', top: '24px', right: '24px', zIndex: '9999',
      padding: '14px 28px', borderRadius: '10px',
      background: '#059669', color: '#fff',
      fontSize: '14px', fontWeight: '500',
      boxShadow: '0 8px 24px rgba(5,150,105,.35)',
      opacity: '0', transform: 'translateY(-12px)',
      transition: 'opacity .3s, transform .3s'
    });
    document.body.appendChild(toast);
    requestAnimationFrame(() => { toast.style.opacity = '1'; toast.style.transform = 'translateY(0)'; });
    setTimeout(() => {
      toast.style.opacity = '0'; toast.style.transform = 'translateY(-12px)';
      setTimeout(() => toast.remove(), 300);
    }, 3000);
  })();
</script>

<!-- Barra de acciones masivas -->
<div id="bulk-bar" class="bulk-bar" style="display:none;">
  <div class="bulk-bar-inner">
    <span class="bulk-bar-count"><strong id="bulk-count">0</strong> casos seleccionados</span>
    <div class="bulk-bar-actions">
      <button id="bulk-activar" class="del-btn del-btn-activar" style="display:none;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          stroke-linecap="round">
          <polyline points="23 4 23 10 17 10" />
          <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10" />
        </svg>
        Activar
      </button>
      <button id="bulk-inactivar" class="del-btn del-btn-inactivar" style="display:none;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          stroke-linecap="round">
          <circle cx="12" cy="12" r="10" />
          <line x1="4.93" y1="4.93" x2="19.07" y2="19.07" />
        </svg>
        Desactivar
      </button>
      <button id="bulk-eliminar" class="del-btn del-btn-delete">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          stroke-linecap="round">
          <polyline points="3 6 5 6 21 6" />
          <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
        </svg>
        Eliminar
      </button>
    </div>
  </div>
</div>

<!-- Modal de confirmación genérico -->
<div id="modal-confirm" class="del-modal-overlay" style="display:none;">
  <div class="del-modal">
    <div class="del-modal-icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
        <circle cx="12" cy="12" r="10" />
        <line x1="12" y1="8" x2="12" y2="12" />
        <line x1="12" y1="16" x2="12.01" y2="16" />
      </svg>
    </div>
    <h3 class="del-modal-title" id="confirm-modal-title"></h3>
    <p class="del-modal-case-name" id="confirm-modal-desc" style="margin-bottom:1rem;"></p>
    <div class="del-modal-actions">
      <button id="confirm-modal-cancel" class="del-btn del-btn-cancel">Cancelar</button>
      <button id="confirm-modal-ok" class="del-btn del-btn-delete">Confirmar</button>
    </div>
  </div>
</div>

<!-- Modal de confirmación de eliminación -->
<div id="modal-delete-caso" class="del-modal-overlay" style="display:none;">
  <div class="del-modal">
    <div class="del-modal-icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
        <line x1="12" y1="9" x2="12" y2="13" />
        <line x1="12" y1="17" x2="12.01" y2="17" />
      </svg>
    </div>
    <h3 class="del-modal-title">¿Eliminar este caso?</h3>
    <p class="del-modal-case-name" id="del-modal-case-name"></p>
    <div class="del-modal-warning" id="del-modal-warning">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" width="18"
        height="18">
        <circle cx="12" cy="12" r="10" />
        <line x1="12" y1="8" x2="12" y2="12" />
        <line x1="12" y1="16" x2="12.01" y2="16" />
      </svg>
      <span id="del-modal-warning-text">Al eliminar este caso, toda la información asociada —incluyendo el progreso de
        los estudiantes asignados— se
        perderá de forma <strong>permanente</strong>.</span>
    </div>
    <p class="del-modal-hint" id="del-modal-hint">Si desea que el caso deje de estar disponible sin perder datos, puede
      <strong>desactivarlo</strong> en su lugar.
    </p>
    <div class="del-modal-actions">
      <button id="btn-del-cancel" class="del-btn del-btn-cancel">Cancelar</button>
      <button id="btn-del-inactivar" class="del-btn del-btn-inactivar">Desactivar</button>
      <button id="btn-del-confirm" class="del-btn del-btn-delete">Eliminar caso</button>
    </div>
  </div>
</div>

<style>
  .del-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, .45);
    backdrop-filter: blur(4px);
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
    animation: delFadeIn .2s ease
  }

  @keyframes delFadeIn {
    from {
      opacity: 0
    }

    to {
      opacity: 1
    }
  }

  .del-modal {
    background: #fff;
    border-radius: 16px;
    padding: 2rem 2.25rem;
    max-width: 480px;
    width: 90%;
    box-shadow: 0 20px 60px rgba(0, 0, 0, .15);
    text-align: center;
    animation: delSlideUp .25s ease
  }

  @keyframes delSlideUp {
    from {
      opacity: 0;
      transform: translateY(16px)
    }

    to {
      opacity: 1;
      transform: translateY(0)
    }
  }

  .del-modal-icon {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: #fef2f2;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem
  }

  .del-modal-icon svg {
    width: 28px;
    height: 28px;
    color: #dc2626
  }

  .del-modal-title {
    margin: 0 0 .5rem;
    font-size: 1.25rem;
    font-weight: 700;
    color: #111827
  }

  .del-modal-case-name {
    margin: 0 0 1rem;
    font-size: .9375rem;
    color: #6b7280;
    font-weight: 500
  }

  .del-modal-warning {
    display: flex;
    align-items: flex-start;
    gap: .75rem;
    background: #fef3c7;
    border: 1px solid #fde68a;
    border-radius: 10px;
    padding: .875rem 1rem;
    text-align: left;
    margin-bottom: 1rem;
    font-size: .875rem;
    color: #92400e;
    line-height: 1.5
  }

  .del-modal-warning svg {
    flex-shrink: 0;
    color: #d97706;
    margin-top: 2px
  }

  .del-modal-hint {
    font-size: .875rem;
    color: #6b7280;
    margin: 0 0 1.5rem;
    line-height: 1.5
  }

  .del-modal-actions {
    display: flex;
    gap: .75rem;
    justify-content: center
  }

  .del-btn {
    padding: .625rem 1.25rem;
    border-radius: 8px;
    font-size: .9375rem;
    font-weight: 600;
    cursor: pointer;
    border: 1px solid transparent;
    transition: all .2s
  }

  .del-btn-cancel {
    background: #fff;
    color: #374151;
    border-color: #d1d5db
  }

  .del-btn-cancel:hover {
    background: #f9fafb;
    border-color: #9ca3af
  }

  .del-btn-inactivar {
    background: #fef3c7;
    color: #92400e;
    border-color: #fde68a
  }

  .del-btn-inactivar:hover {
    background: #fde68a
  }

  .del-btn-delete {
    background: #dc2626;
    color: #fff
  }

  .del-btn-delete:hover {
    background: #b91c1c
  }

  /* Bulk action bar */
  .bulk-bar {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    z-index: 9000;
    display: flex;
    justify-content: center;
    padding: 0 1rem 1.25rem;
    pointer-events: none;
    animation: bulkSlideUp .25s ease
  }

  @keyframes bulkSlideUp {
    from {
      opacity: 0;
      transform: translateY(20px)
    }

    to {
      opacity: 1;
      transform: translateY(0)
    }
  }

  .bulk-bar-inner {
    pointer-events: all;
    background: #1e293b;
    color: #fff;
    border-radius: 12px;
    padding: .75rem 1.25rem;
    display: flex;
    align-items: center;
    gap: 1.25rem;
    box-shadow: 0 12px 40px rgba(0, 0, 0, .25)
  }

  .bulk-bar-count {
    font-size: .875rem;
    white-space: nowrap
  }

  .bulk-bar-count strong {
    font-weight: 700
  }

  .bulk-bar-actions {
    display: flex;
    gap: .5rem
  }

  .bulk-bar-actions .del-btn {
    padding: .5rem 1rem;
    font-size: .8125rem;
    display: inline-flex;
    align-items: center;
    gap: 6px
  }

  .del-btn-activar {
    background: #dcfce7;
    color: #166534;
    border-color: #bbf7d0
  }

  .del-btn-activar:hover {
    background: #bbf7d0
  }
</style>

<script>
  (function () {
    const baseUrl = (window.BASE_URL || '<?= base_url('') ?>').replace(/\/+$/, '');
    const tbody = document.getElementById('casos-tbody');
    if (!tbody) return;

    // ========== TOAST ==========
    function showToast(msg, type) {
      const t = document.createElement('div');
      t.style.cssText = 'position:fixed;top:24px;right:24px;z-index:11000;padding:12px 20px;border-radius:10px;font-size:14px;font-weight:600;color:#fff;box-shadow:0 8px 24px rgba(0,0,0,.15);transform:translateY(-12px);opacity:0;transition:all .3s ease;';
      t.style.background = type === 'error' ? '#dc2626' : '#16a34a';
      t.textContent = msg;
      document.body.appendChild(t);
      requestAnimationFrame(() => { t.style.opacity = '1'; t.style.transform = 'translateY(0)'; });
      setTimeout(() => { t.style.opacity = '0'; t.style.transform = 'translateY(-12px)'; setTimeout(() => t.remove(), 300); }, 3000);
    }

    // ========== CONFIRM MODAL ==========
    const confirmOverlay = document.getElementById('modal-confirm');
    const confirmTitle = document.getElementById('confirm-modal-title');
    const confirmDesc = document.getElementById('confirm-modal-desc');
    const confirmOk = document.getElementById('confirm-modal-ok');
    const confirmCancel = document.getElementById('confirm-modal-cancel');

    function showConfirm(title, desc, btnText, btnClass) {
      return new Promise(resolve => {
        confirmTitle.textContent = title;
        confirmDesc.textContent = desc || '';
        confirmDesc.style.display = desc ? '' : 'none';
        confirmOk.textContent = btnText || 'Confirmar';
        confirmOk.className = 'del-btn ' + (btnClass || 'del-btn-delete');
        confirmOverlay.style.display = 'flex';
        const cleanup = val => { confirmOverlay.style.display = 'none'; resolve(val); };
        confirmOk.onclick = () => cleanup(true);
        confirmCancel.onclick = () => cleanup(false);
        confirmOverlay.onclick = e => { if (e.target === confirmOverlay) cleanup(false); };
      });
    }

    // ========== DOM HELPERS ==========
    const statusMap = { 'Publicado': 'status-active', 'Borrador': 'status-draft', 'Inactivo': 'status-review' };

    function updateRowEstado(id, newEstado) {
      const row = tbody.querySelector('tr[data-id="' + id + '"]');
      if (!row) return;
      row.dataset.estado = newEstado;

      // Badge
      const badge = row.querySelector('.status-badge');
      if (badge) {
        badge.className = 'status-badge ' + (statusMap[newEstado] || 'status-draft');
        badge.textContent = newEstado;
      }

      // Rebuild middle action button
      const actions = row.querySelector('.row-actions');
      if (!actions) return;
      const old = actions.querySelector('.btn-reactivar-caso, .btn-desactivar-caso, a[title="Editar"]');
      if (old) old.remove();
      const deleteBtn = actions.querySelector('.btn-delete-caso');

      let el;
      if (newEstado === 'Borrador') {
        el = document.createElement('a');
        el.href = baseUrl + '/crear-caso?edit=' + id;
        el.className = 'row-action-btn'; el.title = 'Editar';
        el.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>';
      } else if (newEstado === 'Inactivo') {
        el = document.createElement('button');
        el.className = 'row-action-btn btn-reactivar-caso'; el.dataset.casoId = id; el.title = 'Reactivar';
        el.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>';
      } else if (newEstado === 'Publicado') {
        el = document.createElement('button');
        el.className = 'row-action-btn btn-desactivar-caso'; el.dataset.casoId = id; el.title = 'Desactivar';
        el.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>';
      }
      if (el && deleteBtn) actions.insertBefore(el, deleteBtn);
      if (deleteBtn) deleteBtn.dataset.casoEstado = newEstado;
    }

    function removeRow(id) {
      const row = tbody.querySelector('tr[data-id="' + id + '"]');
      if (!row) return;
      row.style.transition = 'opacity .3s, transform .3s';
      row.style.opacity = '0'; row.style.transform = 'translateX(20px)';
      setTimeout(() => { row.remove(); render(); }, 300);
    }

    // ========== SINGLE ROW ACTIONS (event delegation) ==========
    tbody.addEventListener('click', async e => {
      const btn = e.target.closest('.btn-reactivar-caso, .btn-desactivar-caso');
      if (!btn) return;
      const id = btn.dataset.casoId;
      const newEstado = btn.classList.contains('btn-reactivar-caso') ? 'Publicado' : 'Inactivo';
      btn.disabled = true;
      try {
        const res = await fetch(baseUrl + '/api/casos/' + id + '/estado', {
          method: 'PATCH', headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ estado: newEstado })
        });
        const data = await res.json();
        if (data.success) {
          updateRowEstado(id, newEstado);
          showToast(newEstado === 'Publicado' ? 'Caso activado.' : 'Caso desactivado.');
          render();
        } else { showToast(data.message || 'Error.', 'error'); btn.disabled = false; }
      } catch (err) { showToast('Error de conexión.', 'error'); btn.disabled = false; }
    });

    // ========== DELETE MODAL ==========
    const modal = document.getElementById('modal-delete-caso');
    const nameEl = document.getElementById('del-modal-case-name');
    let currentCasoId = null;

    document.querySelectorAll('.btn-delete-caso').forEach(btn => {
      btn.addEventListener('click', () => {
        currentCasoId = btn.dataset.casoId;
        const estado = btn.dataset.casoEstado;
        nameEl.textContent = '"' + btn.dataset.casoTitulo + '"';
        const warningText = document.getElementById('del-modal-warning-text');
        const hint = document.getElementById('del-modal-hint');
        const btnInact = document.getElementById('btn-del-inactivar');
        if (estado === 'Borrador') {
          warningText.innerHTML = 'Se eliminará este borrador y todos sus datos de forma <strong>permanente</strong>. Esta acción no se puede deshacer.';
          hint.style.display = 'none'; btnInact.style.display = 'none';
        } else {
          warningText.innerHTML = 'Al eliminar este caso, toda la información asociada —incluyendo el progreso de los estudiantes asignados— se perderá de forma <strong>permanente</strong>.';
          hint.style.display = ''; btnInact.style.display = '';
        }
        modal.style.display = 'flex';
      });
    });

    document.getElementById('btn-del-cancel').addEventListener('click', () => { modal.style.display = 'none'; currentCasoId = null; });
    modal.addEventListener('click', e => { if (e.target === modal) { modal.style.display = 'none'; currentCasoId = null; } });

    document.getElementById('btn-del-inactivar').addEventListener('click', async () => {
      if (!currentCasoId) return;
      try {
        const res = await fetch(baseUrl + '/api/casos/' + currentCasoId + '/estado', {
          method: 'PATCH', headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ estado: 'Inactivo' })
        });
        const data = await res.json();
        if (data.success) { updateRowEstado(currentCasoId, 'Inactivo'); showToast('Caso desactivado.'); render(); }
        else showToast(data.message || 'Error.', 'error');
      } catch (err) { showToast('Error de conexión.', 'error'); }
      modal.style.display = 'none'; currentCasoId = null;
    });

    document.getElementById('btn-del-confirm').addEventListener('click', async () => {
      if (!currentCasoId) return;
      try {
        const res = await fetch(baseUrl + '/api/casos/' + currentCasoId, {
          method: 'DELETE', headers: { 'Content-Type': 'application/json' }
        });
        const data = await res.json();
        if (data.success) { removeRow(currentCasoId); showToast('Caso eliminado.'); }
        else showToast(data.message || 'Error.', 'error');
      } catch (err) { showToast('Error de conexión.', 'error'); }
      modal.style.display = 'none'; currentCasoId = null;
    });

    // ========== TABLE: SEARCH, FILTER, SORT, PAGINATION ==========
    const searchInput = document.getElementById('search-casos');
    const chips = document.querySelectorAll('.filter-chip[data-filter]');
    const checkAll = document.getElementById('check-all');
    const footerInfo = document.querySelector('.table-footer-info');
    const paginationEl = document.querySelector('.pagination');
    const PER_PAGE = 10;
    let activeFilter = 'Todos', searchTerm = '', sortKey = null, sortDir = 1, currentPage = 1;

    function getVisible() {
      return Array.from(tbody.querySelectorAll('tr[data-estado]')).filter(r => {
        const e = r.dataset.estado;
        if (e === 'Eliminado') return false;
        if (activeFilter !== 'Todos' && e !== activeFilter) return false;
        if (!searchTerm) return true;
        return r.dataset.causante.includes(searchTerm) || r.dataset.cedula.includes(searchTerm) || r.dataset.titulo.includes(searchTerm) || r.dataset.id.includes(searchTerm);
      });
    }

    function sortRows(rows) {
      if (!sortKey) return rows;
      return rows.slice().sort((a, b) => {
        let va, vb;
        switch (sortKey) {
          case 'id': va = +a.dataset.id; vb = +b.dataset.id; break;
          case 'titulo': va = a.dataset.titulo; vb = b.dataset.titulo; break;
          case 'causante': va = a.dataset.causante; vb = b.dataset.causante; break;
          case 'estado': va = a.dataset.estado; vb = b.dataset.estado; break;
          case 'fecha': va = a.dataset.fecha; vb = b.dataset.fecha; break;
          default: return 0;
        }
        return va < vb ? -sortDir : va > vb ? sortDir : 0;
      });
    }

    function render() {
      const visible = sortRows(getVisible());
      const totalPages = Math.max(1, Math.ceil(visible.length / PER_PAGE));
      if (currentPage > totalPages) currentPage = totalPages;
      const start = (currentPage - 1) * PER_PAGE;
      const pageRows = visible.slice(start, start + PER_PAGE);

      Array.from(tbody.querySelectorAll('tr[data-estado]')).forEach(r => r.style.display = 'none');
      pageRows.forEach(r => r.style.display = '');

      if (footerInfo) footerInfo.innerHTML = 'Mostrando <strong>' + pageRows.length + '</strong> de <strong>' + visible.length + '</strong> casos';

      if (paginationEl) {
        paginationEl.innerHTML = '';
        const prev = document.createElement('button');
        prev.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="15 18 9 12 15 6"/></svg>';
        prev.disabled = currentPage === 1;
        prev.addEventListener('click', () => { currentPage--; render(); });
        paginationEl.appendChild(prev);
        for (let i = 1; i <= totalPages; i++) {
          const b = document.createElement('button');
          b.textContent = i;
          if (i === currentPage) b.classList.add('active');
          b.addEventListener('click', () => { currentPage = i; render(); });
          paginationEl.appendChild(b);
        }
        const next = document.createElement('button');
        next.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="9 18 15 12 9 6"/></svg>';
        next.disabled = currentPage === totalPages;
        next.addEventListener('click', () => { currentPage++; render(); });
        paginationEl.appendChild(next);
      }
      if (checkAll) checkAll.checked = false;
      updateBulkBar();
    }

    searchInput.addEventListener('input', () => { searchTerm = searchInput.value.toLowerCase().trim(); currentPage = 1; render(); });
    chips.forEach(chip => {
      chip.addEventListener('click', () => {
        chips.forEach(c => c.classList.remove('active'));
        chip.classList.add('active');
        activeFilter = chip.dataset.filter; currentPage = 1; render();
      });
    });
    document.querySelectorAll('th.sortable[data-sort]').forEach(th => {
      th.style.cursor = 'pointer';
      th.addEventListener('click', () => {
        const key = th.dataset.sort;
        if (sortKey === key) sortDir *= -1; else { sortKey = key; sortDir = 1; }
        document.querySelectorAll('th.sortable[data-sort]').forEach(h => h.classList.remove('sort-asc', 'sort-desc'));
        th.classList.add(sortDir === 1 ? 'sort-asc' : 'sort-desc');
        render();
      });
    });

    // ========== BULK ACTIONS ==========
    const bulkBar = document.getElementById('bulk-bar');
    const bulkCount = document.getElementById('bulk-count');
    const btnBA = document.getElementById('bulk-activar');
    const btnBI = document.getElementById('bulk-inactivar');

    function getSelectedRows() {
      return Array.from(tbody.querySelectorAll('tr[data-estado]'))
        .filter(r => r.style.display !== 'none' && r.querySelector('.row-check')?.checked)
        .map(r => ({ id: r.dataset.id, estado: r.dataset.estado }));
    }

    function updateBulkBar() {
      const sel = getSelectedRows();
      if (sel.length > 0) {
        bulkCount.textContent = sel.length;
        bulkBar.style.display = 'flex';
        const estados = new Set(sel.map(s => s.estado));
        btnBA.style.display = estados.has('Inactivo') ? '' : 'none';
        btnBI.style.display = estados.has('Publicado') ? '' : 'none';
      } else { bulkBar.style.display = 'none'; }
    }

    tbody.addEventListener('change', e => { if (e.target.classList.contains('row-check')) updateBulkBar(); });
    if (checkAll) {
      checkAll.addEventListener('change', () => {
        Array.from(tbody.querySelectorAll('tr[data-estado]')).filter(r => r.style.display !== 'none')
          .map(r => r.querySelector('.row-check')).filter(Boolean).forEach(cb => cb.checked = checkAll.checked);
        updateBulkBar();
      });
    }

    document.getElementById('bulk-eliminar').addEventListener('click', async () => {
      const sel = getSelectedRows();
      if (!sel.length) return;
      if (!await showConfirm('¿Eliminar ' + sel.length + ' caso(s)?', 'Esta acción cambiará su estado a "Eliminado".', 'Eliminar', 'del-btn-delete')) return;
      try {
        await Promise.all(sel.map(s => fetch(baseUrl + '/api/casos/' + s.id, { method: 'DELETE', headers: { 'Content-Type': 'application/json' } })));
        sel.forEach(s => removeRow(s.id));
        showToast(sel.length + ' caso(s) eliminado(s).');
      } catch (err) { showToast('Error de conexión.', 'error'); }
    });

    document.getElementById('bulk-inactivar').addEventListener('click', async () => {
      const sel = getSelectedRows().filter(s => s.estado === 'Publicado');
      if (!sel.length) return;
      if (!await showConfirm('¿Desactivar ' + sel.length + ' caso(s)?', 'Los casos dejarán de estar disponibles para los estudiantes.', 'Desactivar', 'del-btn-inactivar')) return;
      try {
        await Promise.all(sel.map(s => fetch(baseUrl + '/api/casos/' + s.id + '/estado', { method: 'PATCH', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ estado: 'Inactivo' }) })));
        sel.forEach(s => updateRowEstado(s.id, 'Inactivo'));
        showToast(sel.length + ' caso(s) desactivado(s).'); render();
      } catch (err) { showToast('Error de conexión.', 'error'); }
    });

    document.getElementById('bulk-activar').addEventListener('click', async () => {
      const sel = getSelectedRows().filter(s => s.estado === 'Inactivo');
      if (!sel.length) return;
      if (!await showConfirm('¿Activar ' + sel.length + ' caso(s)?', 'Los casos volverán a estar disponibles para los estudiantes.', 'Activar', 'del-btn-activar')) return;
      try {
        await Promise.all(sel.map(s => fetch(baseUrl + '/api/casos/' + s.id + '/estado', { method: 'PATCH', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ estado: 'Publicado' }) })));
        sel.forEach(s => updateRowEstado(s.id, 'Publicado'));
        showToast(sel.length + ' caso(s) activado(s).'); render();
      } catch (err) { showToast('Error de conexión.', 'error'); }
    });

    render();
  })();
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/logged_layout.php';
?>