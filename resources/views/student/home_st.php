<?php
declare(strict_types=1);

// ARCHIVO: resources/views/student/home_st.php
// Dashboard del estudiante — panorama general.

$pageTitle = 'Inicio Estudiante — Simulador SENIAT';
$activePage = 'inicio';
$extraCss = '<link rel="stylesheet" href="' . asset('css/student/home_st.css') . '">';

// ── Datos Placeholder ──────────────────────────────────────
$userName = $_SESSION['user_name'] ?? 'Estudiante';

$stats = [
  'pendientes' => 3,
  'en_progreso' => 1,
  'calificados' => 2,
  'promedio' => 15.7,
];

// Draft: borrador activo (null si no hay)
$draft = [
  'caso_titulo' => 'Sucesión González Méndez',
  'paso_actual' => 2,
  'paso_total' => 5,
  'paso_nombre' => 'Herederos',
  'ultima_edicion' => '2026-03-09 12:30:00',
  'deadline' => '2026-03-15',
  'asignacion_id' => 1,
];

// Activity feed
$actividad = [
  [
    'tipo' => 'envio',
    'dot' => 'dot-blue',
    'texto' => 'Enviaste el intento <strong>#2</strong> del caso <strong>Sucesión González Méndez</strong>',
    'tiempo' => 'Hace 1 día',
  ],
  [
    'tipo' => 'calificacion',
    'dot' => 'dot-green',
    'texto' => 'Tu intento <strong>#1</strong> del caso <strong>Sucesión Pérez Alvarado</strong> fue calificado: <strong>16/20</strong>',
    'tiempo' => 'Hace 3 días',
  ],
  [
    'tipo' => 'asignacion',
    'dot' => 'dot-purple',
    'texto' => 'El <strong>Prof. César Rodríguez</strong> te asignó el caso <strong>Sucesión Ramírez Torres</strong>',
    'tiempo' => 'Hace 5 días',
  ],
  [
    'tipo' => 'calificacion',
    'dot' => 'dot-green',
    'texto' => 'Tu intento <strong>#1</strong> del caso <strong>Sucesión González Méndez</strong> fue calificado: <strong>14.5/20</strong>',
    'tiempo' => 'Hace 6 días',
  ],
  [
    'tipo' => 'envio',
    'dot' => 'dot-blue',
    'texto' => 'Enviaste el intento <strong>#1</strong> del caso <strong>Sucesión González Méndez</strong>',
    'tiempo' => 'Hace 8 días',
  ],
];

ob_start();
?>

<!-- Header -->
<div class="page-header">
  <h1>Bienvenido, <?= htmlspecialchars($userName) ?></h1>
</div>

<!-- Stats Row -->
<div class="stats-row">
  <div class="stat-card stat-card--vertical animate-in">
    <div class="stat-card-top">
      <span class="stat-label">Asignaciones Pendientes</span>
      <div class="stat-icon blue">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
          <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2" />
          <rect x="8" y="2" width="8" height="4" rx="1" ry="1" />
        </svg>
      </div>
    </div>
    <div class="stat-value"><?= $stats['pendientes'] ?></div>
  </div>

  <div class="stat-card stat-card--vertical animate-in">
    <div class="stat-card-top">
      <span class="stat-label">En Progreso</span>
      <div class="stat-icon amber">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
          <circle cx="12" cy="12" r="10" />
          <polyline points="12 6 12 12 16 14" />
        </svg>
      </div>
    </div>
    <div class="stat-value"><?= $stats['en_progreso'] ?></div>
  </div>

  <div class="stat-card stat-card--vertical animate-in">
    <div class="stat-card-top">
      <span class="stat-label">Calificados</span>
      <div class="stat-icon green">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
          <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
          <polyline points="22 4 12 14.01 9 11.01" />
        </svg>
      </div>
    </div>
    <div class="stat-value"><?= $stats['calificados'] ?></div>
  </div>

  <div class="stat-card stat-card--vertical stat-dark animate-in">
    <div class="stat-card-top">
      <span class="stat-label">Promedio General</span>
      <div class="stat-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
          <polygon
            points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
        </svg>
      </div>
    </div>
    <div class="stat-value">
      <?= $stats['promedio'] !== null ? number_format($stats['promedio'], 1) : '—' ?>
    </div>
  </div>
</div>

<!-- Dashboard Grid (60/40) -->
<div class="dashboard-grid">

  <!-- LEFT: Continuar donde lo dejaste -->
  <div class="continue-card animate-in">
    <div class="continue-card-header">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
        <polygon points="5 3 19 12 5 21 5 3" />
      </svg>
      <h3>Continuar donde lo dejaste</h3>
    </div>

    <?php if ($draft): ?>
      <div class="draft-card">
        <div class="draft-card-title"><?= htmlspecialchars($draft['caso_titulo']) ?></div>
        <div class="draft-card-meta">
          <span class="draft-meta-item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
              <circle cx="12" cy="12" r="10" />
              <polyline points="12 6 12 12 16 14" />
            </svg>
            Última edición: <strong><?php
            $diff = time() - strtotime($draft['ultima_edicion']);
            if ($diff < 3600)
              echo 'Hace ' . max(1, floor($diff / 60)) . ' min';
            elseif ($diff < 86400)
              echo 'Hace ' . floor($diff / 3600) . ' horas';
            else
              echo 'Hace ' . floor($diff / 86400) . ' días';
            ?></strong>
          </span>
          <?php if ($draft['deadline']): ?>
            <?php
            $daysLeft = (int) ((strtotime($draft['deadline']) - time()) / 86400);
            $dlClass = $daysLeft <= 2 ? 'deadline-urgent' : ($daysLeft <= 5 ? 'deadline-soon' : 'deadline-ok');
            ?>
            <span class="deadline-badge <?= $dlClass ?>">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                <line x1="16" y1="2" x2="16" y2="6" />
                <line x1="8" y1="2" x2="8" y2="6" />
                <line x1="3" y1="10" x2="21" y2="10" />
              </svg>
              Fecha límite: <?= date('d/m/Y', strtotime($draft['deadline'])) ?>
            </span>
          <?php endif; ?>
        </div>
        <div class="draft-stepper">
          <?php for ($i = 1; $i <= $draft['paso_total']; $i++): ?>
            <span class="stepper-dot <?php
            if ($i < $draft['paso_actual'])
              echo 'stepper-done';
            elseif ($i === $draft['paso_actual'])
              echo 'stepper-current';
            ?>"></span>
          <?php endfor; ?>
          <span class="stepper-label">Paso <?= $draft['paso_actual'] ?> de <?= $draft['paso_total'] ?> —
            <?= htmlspecialchars($draft['paso_nombre']) ?></span>
        </div>
        <a href="<?= base_url('/mis-asignaciones/' . $draft['asignacion_id']) ?>" class="btn btn-primary">
          Continuar →
        </a>
      </div>
      <div class="continue-footer">
        <a href="<?= base_url('/mis-asignaciones') ?>">Ver todas en Mis Asignaciones</a>
      </div>
    <?php else: ?>
      <div class="no-draft">
        <div class="no-draft-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
            <polyline points="20 6 9 17 4 12" />
          </svg>
        </div>
        <h4>No tienes declaraciones en progreso</h4>
        <p>¡Revisa tus asignaciones para comenzar!</p>
        <a href="<?= base_url('/mis-asignaciones') ?>" class="btn btn-primary">
          Ir a Mis Asignaciones
        </a>
      </div>
    <?php endif; ?>
  </div>

  <!-- RIGHT: Actividad reciente -->
  <div class="activity-panel animate-in">
    <div class="activity-panel-header">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
        <circle cx="12" cy="12" r="10" />
        <polyline points="12 6 12 12 16 14" />
      </svg>
      <h3>Actividad reciente</h3>
    </div>

    <?php if (empty($actividad)): ?>
      <div class="activity-empty">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
          <circle cx="12" cy="12" r="10" />
          <polyline points="12 6 12 12 16 14" />
        </svg>
        <p>Tu actividad aparecerá aquí a medida que uses el simulador.</p>
      </div>
    <?php else: ?>
      <ul class="activity-list">
        <?php foreach ($actividad as $act): ?>
          <li class="activity-item">
            <span class="activity-dot <?= $act['dot'] ?>"></span>
            <div class="activity-text"><?= $act['texto'] ?></div>
            <span class="activity-time"><?= $act['tiempo'] ?></span>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/logged_layout.php';
?>