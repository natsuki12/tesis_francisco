<?php
declare(strict_types=1);

// ARCHIVO: resources/views/professor/home_professor.php

// 1. Configuración de la Vista
$pageTitle = 'Panel del Profesor — Simulador SENIAT';
$activePage = 'inicio';

// 2. Cargamos el CSS específico (home_professor.css)
$extraCss = '<link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700&display=swap" rel="stylesheet">';
$extraCss .= '<link rel="stylesheet" href="' . asset('css/professor/home_professor.css') . '">';

// 3. Datos (Placeholder: Luego vendrán del Controlador)
$userName = $_SESSION['user_name'] ?? 'Profesor';

ob_start();
?>

<!-- Header -->
<div class="page-header">
  <h1>Bienvenido, Prof. <?= htmlspecialchars($userName) ?></h1>
  <p>Panel de control del profesor. Gestione sus estudiantes y revise sus declaraciones.</p>
</div>

<?php $stats = $stats ?? ['estudiantes' => 0, 'casos' => 0, 'rif_pendientes' => 0, 'por_calificar' => 0]; ?>

<!-- Stats -->
<div class="stats-row">
  <div class="stat-card stat-card--vertical animate-in">
    <div class="stat-card-top">
      <div class="stat-icon blue">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
          stroke-linejoin="round" viewBox="0 0 24 24">
          <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" />
          <circle cx="9" cy="7" r="4" />
          <path d="M23 21v-2a4 4 0 00-3-3.87" />
          <path d="M16 3.13a4 4 0 010 7.75" />
        </svg>
      </div>
    </div>
    <div class="stat-value"><?= $stats['estudiantes'] ?></div>
    <div class="stat-label">Estudiantes activos</div>
  </div>
  <div class="stat-card stat-card--vertical animate-in">
    <div class="stat-card-top">
      <div class="stat-icon green">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
          stroke-linejoin="round" viewBox="0 0 24 24">
          <path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z" />
        </svg>
      </div>
    </div>
    <div class="stat-value"><?= $stats['casos'] ?></div>
    <div class="stat-label">Casos asignados</div>
  </div>
  <div class="stat-card stat-card--vertical animate-in">
    <div class="stat-card-top">
      <div class="stat-icon amber">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
          stroke-linejoin="round" viewBox="0 0 24 24">
          <circle cx="12" cy="12" r="10" />
          <polyline points="12 6 12 12 16 14" />
        </svg>
      </div>
    </div>
    <div class="stat-value"><?= $stats['rif_pendientes'] ?></div>
    <div class="stat-label">Solicitudes de RIF pendientes</div>
  </div>
  <div class="stat-card stat-card--vertical animate-in">
    <div class="stat-card-top">
      <div class="stat-icon red">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
          stroke-linejoin="round" viewBox="0 0 24 24">
          <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" />
          <polyline points="14 2 14 8 20 8" />
          <line x1="16" y1="13" x2="8" y2="13" />
          <line x1="16" y1="17" x2="8" y2="17" />
        </svg>
      </div>
    </div>
    <div class="stat-value"><?= $stats['por_calificar'] ?></div>
    <div class="stat-label">Declaraciones por calificar</div>
  </div>
</div>

<!-- Action Cards -->
<div class="action-cards">
  <a class="action-card animate-in" href="<?= base_url('/entregas') ?>">
    <div class="action-card-icon">
      <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
        stroke-linejoin="round" viewBox="0 0 24 24">
        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" />
        <polyline points="14 2 14 8 20 8" />
        <line x1="16" y1="13" x2="8" y2="13" />
        <line x1="16" y1="17" x2="8" y2="17" />
        <polyline points="10 9 9 9 8 9" />
      </svg>
    </div>
    <h3>Revisar Declaraciones</h3>
    <p>Acceda a las declaraciones sucesorales realizadas por sus estudiantes para evaluarlas.</p>
    <span class="action-card-link">
      Ver declaraciones
      <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
        stroke-linejoin="round" viewBox="0 0 24 24">
        <line x1="5" y1="12" x2="19" y2="12" />
        <polyline points="12 5 19 12 12 19" />
      </svg>
    </span>
  </a>
  <a class="action-card animate-in" href="<?= base_url('/mis-estudiantes') ?>">
    <div class="action-card-icon">
      <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
        stroke-linejoin="round" viewBox="0 0 24 24">
        <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" />
        <circle cx="9" cy="7" r="4" />
        <path d="M23 21v-2a4 4 0 00-3-3.87" />
        <path d="M16 3.13a4 4 0 010 7.75" />
      </svg>
    </div>
    <h3>Mis Estudiantes</h3>
    <p>Consulte la lista de estudiantes asignados, su progreso individual y el estado de sus prácticas.</p>
    <span class="action-card-link">
      Ver estudiantes
      <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
        stroke-linejoin="round" viewBox="0 0 24 24">
        <line x1="5" y1="12" x2="19" y2="12" />
        <polyline points="12 5 19 12 12 19" />
      </svg>
    </span>
  </a>
  <a class="action-card animate-in" href="<?= base_url('/estadisticas') ?>">
    <div class="action-card-icon">
      <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
        stroke-linejoin="round" viewBox="0 0 24 24">
        <line x1="18" y1="20" x2="18" y2="10" />
        <line x1="12" y1="20" x2="12" y2="4" />
        <line x1="6" y1="20" x2="6" y2="14" />
      </svg>
    </div>
    <h3>Estadísticas</h3>
    <p>Consulte métricas de rendimiento, distribución de notas y actividad de sus estudiantes.</p>
    <span class="action-card-link">
      Ver estadísticas
      <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
        stroke-linejoin="round" viewBox="0 0 24 24">
        <line x1="5" y1="12" x2="19" y2="12" />
        <polyline points="12 5 19 12 12 19" />
      </svg>
    </span>
  </a>
</div>

<!-- Bottom Panels -->
<div class="bottom-grid">

  <!-- Recent Students -->
  <div class="panel">
    <div class="panel-header">
      <h3>Estudiantes recientes</h3>
      <a href="<?= base_url('/mis-estudiantes') ?>">Ver todos</a>
    </div>
    <?php if (empty($recentStudents)): ?>
      <div style="text-align:center; padding: 2rem; color: var(--gray-400); font-size: var(--text-md);">
        Ningún estudiante ha ingresado a la plataforma aún.
      </div>
    <?php else: ?>
      <?php foreach ($recentStudents as $st):
        $fullName = trim(($st['nombres'] ?? '') . ' ' . ($st['apellidos'] ?? ''));
        preg_match_all('/\b\w/u', $fullName, $m);
        $initials = mb_strtoupper(implode('', array_slice($m[0], 0, 2)));
        $ced = $st['cedula']
          ? ($st['nacionalidad'] ?? 'V') . '-' . number_format((float) $st['cedula'], 0, ',', '.')
          : 'S/C';

        // Relative time — use DB-computed UNIX timestamp to avoid timezone issues
        $ts = (int) $st['last_login_ts'];
        $diff = time() - $ts;
        if ($diff < 60) {
          $rel = 'Hace unos segundos';
        } elseif ($diff < 3600) {
          $mins = (int) floor($diff / 60);
          $rel = "Hace {$mins} min";
        } elseif ($diff < 86400) {
          $hrs = (int) floor($diff / 3600);
          $rel = "Hace {$hrs}h";
        } elseif ($diff < 172800) {
          $rel = 'Ayer, ' . date('g:i A', $ts);
        } else {
          $rel = date('d/m/Y, g:i A', $ts);
        }
        ?>
        <div class="student-row">
          <div class="student-avatar"><?= htmlspecialchars($initials) ?></div>
          <div class="student-info">
            <div class="student-name"><?= htmlspecialchars($fullName) ?></div>
            <div class="student-detail"><?= htmlspecialchars($ced) ?></div>
          </div>
          <span class="student-status last-seen"><?= $rel ?></span>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <!-- Activity Feed -->
  <div class="panel">
    <div class="panel-header">
      <h3>Actividad reciente</h3>
      <a href="<?= base_url('/historial') ?>">Ver todo</a>
    </div>
    <?php
    $recentActivity = $recentActivity ?? [];
    if (empty($recentActivity)):
      ?>
      <div style="text-align:center; padding: 2rem; color: var(--gray-400); font-size: var(--text-md);">
        No hay actividad registrada aún.
      </div>
    <?php else: ?>
      <?php foreach ($recentActivity as $act):
        // Dot color by event type
        $dotColor = match ($act['tipo'] ?? '') {
          'intento_calificado' => 'green',
          'intento_enviado' => 'blue',
          'asignacion_creada' => 'amber',
          'caso_creado' => 'purple',
          'intento_iniciado' => 'blue',
          default => 'blue',
        };

        // Relative time
        $ts = strtotime($act['fecha'] ?? 'now');
        $diff = time() - $ts;
        if ($diff < 60) {
          $rel = 'Hace unos segundos';
        } elseif ($diff < 3600) {
          $mins = (int) floor($diff / 60);
          $rel = "Hace {$mins} min";
        } elseif ($diff < 86400) {
          $hrs = (int) floor($diff / 3600);
          $rel = "Hace {$hrs}h";
        } elseif ($diff < 172800) {
          $rel = 'Ayer, ' . date('g:i A', $ts);
        } else {
          $rel = date('d/m/Y, g:i A', $ts);
        }

        // Activity text
        $nombre = htmlspecialchars($act['estudiante'] ?? '');
        $detalle = htmlspecialchars($act['detalle'] ?? '');
        ?>
        <div class="activity-item">
          <div class="activity-dot <?= $dotColor ?>"></div>
          <div>
            <div class="activity-text">
              <?php if ($nombre): ?>
                <strong><?= $nombre ?></strong> — <?= $detalle ?>
              <?php else: ?>
                <?= $detalle ?>
              <?php endif; ?>
            </div>
            <div class="activity-time"><?= $rel ?></div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/logged_layout.php';
?>