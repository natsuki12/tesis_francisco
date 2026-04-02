<?php
declare(strict_types=1);

// ARCHIVO: resources/views/student/home_st.php
// Dashboard del estudiante — panorama general.

$pageTitle = 'Inicio Estudiante — Simulador SENIAT';
$activePage = 'inicio';
$extraCss = '<link rel="stylesheet" href="' . asset('css/student/home_st.css') . '">';

// ── Datos ────────────────────────────────────────────────────
$userName = $_SESSION['user_name'] ?? 'Estudiante';

// $draft, $stats, $actividad y $proximoVencimiento vienen del controller
$stats     = $stats ?? ['pendientes' => 0, 'en_progreso' => 0, 'calificados' => 0];
$actividad = $actividad ?? [];

// Calcular urgencia del vencimiento
$vencFecha = null;
$vencColor = '';
$vencLabel = '—';
$vencCaso  = 'Sin fechas límite';
$vencIconColor = 'blue';

if (!empty($proximoVencimiento['fecha_limite'])) {
    $vencFecha = $proximoVencimiento['fecha_limite'];
    $diasRestantes = (int) ceil((strtotime($vencFecha) - time()) / 86400);
    $vencLabel = date('d/m/Y', strtotime($vencFecha));
    $vencCaso = $proximoVencimiento['caso_titulo'] ?? '';

    if ($diasRestantes <= 0) {
        $vencColor = 'color: var(--red-500);';
        $vencIconColor = 'red';
    } elseif ($diasRestantes <= 2) {
        $vencColor = 'color: var(--red-500);';
        $vencIconColor = 'red';
    } elseif ($diasRestantes <= 5) {
        $vencColor = 'color: var(--amber-600, #d97706);';
        $vencIconColor = 'amber';
    } else {
        $vencColor = 'color: var(--green-500);';
        $vencIconColor = 'green';
    }
}

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

  <div class="stat-card stat-card--vertical animate-in">
    <div class="stat-card-top">
      <span class="stat-label">Próximo Vencimiento</span>
      <div class="stat-icon <?= $vencIconColor ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
          <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
          <line x1="16" y1="2" x2="16" y2="6" />
          <line x1="8" y1="2" x2="8" y2="6" />
          <line x1="3" y1="10" x2="21" y2="10" />
        </svg>
      </div>
    </div>
    <div class="stat-value" style="<?= $vencColor ?>"><?= $vencLabel ?></div>
    <div class="stat-sub" title="<?= htmlspecialchars($vencCaso) ?>"><?= htmlspecialchars($vencCaso) ?></div>
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
          <?php if (!empty($draft['fecha_limite'])): ?>
            <?php
            $daysLeft = (int) ((strtotime($draft['fecha_limite']) - time()) / 86400);
            $dlClass = $daysLeft <= 2 ? 'deadline-urgent' : ($daysLeft <= 5 ? 'deadline-soon' : 'deadline-ok');
            ?>
            <span class="deadline-badge <?= $dlClass ?>">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                <line x1="16" y1="2" x2="16" y2="6" />
                <line x1="8" y1="2" x2="8" y2="6" />
                <line x1="3" y1="10" x2="21" y2="10" />
              </svg>
              Fecha límite: <?= date('d/m/Y', strtotime($draft['fecha_limite'])) ?>
            </span>
          <?php endif; ?>
        </div>
        <form method="POST" action="<?= base_url('/api/intentos/iniciar') ?>" style="display:inline;">
          <input type="hidden" name="asignacion_id" value="<?= $draft['asignacion_id'] ?>">
          <button type="submit" class="btn btn-primary">Continuar →</button>
        </form>
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