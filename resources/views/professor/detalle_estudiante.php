<?php
declare(strict_types=1);

// ARCHIVO: resources/views/professor/detalle_estudiante.php
// Sub-vista: perfil y entregas de un estudiante específico.

$pageTitle = 'Detalle Estudiante — Simulador SENIAT';
$activePage = 'mis-estudiantes';
$extraCss  = '<link rel="stylesheet" href="' . asset('css/shared/data-table.css') . '">';
$extraCss .= '<link rel="stylesheet" href="' . asset('css/professor/mis_estudiantes.css') . '">';
$extraCss .= '<link rel="stylesheet" href="' . asset('css/professor/entregas.css') . '">';
$extraJs   = '<script src="' . asset('js/global/data_table_core.js') . '"></script>';

// Datos del controller
$estudiante = $estudiante ?? [];
$entregas   = $entregas ?? [];

// Helpers
$fullName = trim(($estudiante['nombres'] ?? '') . ' ' . ($estudiante['apellidos'] ?? ''));
preg_match_all('/\b\w/u', $fullName, $m);
$iniciales = mb_strtoupper(implode('', array_slice($m[0], 0, 2)));
$avatarColors = ['avatar--blue', 'avatar--green', 'avatar--amber', 'avatar--purple'];
$avatarClass = $avatarColors[abs(crc32($iniciales)) % count($avatarColors)];
$cedula = ($estudiante['nacionalidad'] ?? 'V') . '-' . number_format((float) ($estudiante['cedula'] ?? 0), 0, ',', '.');

function mapEstadoDetalle(string $estado): array
{
    return match ($estado) {
        'Enviado'      => ['label' => 'Enviado',      'class' => 'status-info'],
        'Aprobado'     => ['label' => 'Aprobado',     'class' => 'status-success'],
        'Rechazado'    => ['label' => 'No Aprobado',  'class' => 'status-danger'],
        'En_Progreso'  => ['label' => 'En Progreso',  'class' => 'status-warning'],
        default        => ['label' => ucfirst(str_replace('_', ' ', $estado)), 'class' => 'status-info'],
    };
}

ob_start();
?>

<!-- Breadcrumb -->
<div class="breadcrumb">
    <a href="<?= base_url('/mis-estudiantes') ?>">Mis Estudiantes</a>
    <span class="breadcrumb-sep">›</span>
    <span class="breadcrumb-current">
        <?= htmlspecialchars($fullName) ?>
    </span>
</div>

<!-- Page Header -->
<div class="page-header">
    <div class="page-header-left">
        <h1>Detalle del Estudiante</h1>
    </div>
</div>

<!-- Profile Card -->
<div class="profile-card animate-in">
    <div class="profile-avatar <?= $avatarClass ?>">
        <?= htmlspecialchars($iniciales) ?>
    </div>
    <div class="profile-info">
        <h2 class="profile-name">
            <?= htmlspecialchars($fullName) ?>
        </h2>
        <div class="profile-meta">
            <div class="profile-meta-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <rect x="2" y="3" width="20" height="14" rx="2" />
                    <line x1="8" y1="21" x2="16" y2="21" />
                    <line x1="12" y1="17" x2="12" y2="21" />
                </svg>
                <span>CI: <strong><?= htmlspecialchars($cedula) ?></strong></span>
            </div>
            <div class="profile-meta-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                    <polyline points="22,6 12,13 2,6" />
                </svg>
                <span><?= htmlspecialchars($estudiante['email'] ?? '—') ?></span>
            </div>
            <div class="profile-meta-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                    <circle cx="9" cy="7" r="4" />
                </svg>
                <span>Sección: <strong><?= htmlspecialchars($estudiante['seccion'] ?? '—') ?></strong></span>
            </div>
            <?php if (!empty($estudiante['fecha_inscripcion'])): ?>
            <div class="profile-meta-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                    <line x1="16" y1="2" x2="16" y2="6" />
                    <line x1="8" y1="2" x2="8" y2="6" />
                    <line x1="3" y1="10" x2="21" y2="10" />
                </svg>
                <span>Inscrito: <strong><?= date('d/m/Y', strtotime($estudiante['fecha_inscripcion'])) ?></strong></span>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Entregas del Estudiante -->
<div class="page-header" style="margin-top: 8px;">
    <div class="page-header-left">
        <h1 style="font-size: var(--text-lg);">Entregas</h1>
        <p>Historial de intentos de este estudiante</p>
    </div>
</div>

<?php if (empty($entregas)): ?>
<div class="table-container animate-in">
    <div class="empty-state empty-state--blue">
        <div class="empty-state-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2" />
                <rect x="8" y="2" width="8" height="4" rx="1" ry="1" />
            </svg>
        </div>
        <h3>Sin entregas</h3>
        <p>Este estudiante aún no ha realizado ningún intento.</p>
    </div>
</div>
<?php else: ?>
<div class="table-container animate-in">
    <table class="data-table" id="entregas-est-table">
        <thead>
            <tr>
                <th class="sortable" data-col="0">Caso</th>
                <th class="sortable" data-col="1" data-type="number">Intento</th>
                <th class="sortable" data-col="2">Fecha</th>
                <th class="sortable" data-col="3">Estado</th>
                <th class="sortable" data-col="4">Nota</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody>
                <?php foreach ($entregas as $entrega):
                    $estado = mapEstadoDetalle($entrega['estado'] ?? 'Enviado');
                    $tipo = $entrega['tipo_calificacion'] ?? 'numerica';

                    // Nota display
                    $notaText = '—';
                    $notaClass = 'promedio-na';
                    if ($entrega['estado'] === 'Aprobado' || $entrega['estado'] === 'Rechazado') {
                        if ($tipo === 'numerica' && $entrega['nota_numerica'] !== null) {
                            $n = (float) $entrega['nota_numerica'];
                            $notaText = number_format($n, 1);
                            $notaClass = $n >= 10 ? 'promedio-pass' : 'promedio-fail';
                        } elseif ($entrega['nota_cualitativa']) {
                            $notaText = $entrega['nota_cualitativa'] === 'Aprobado' ? 'A' : 'R';
                            $notaClass = $entrega['nota_cualitativa'] === 'Aprobado' ? 'promedio-pass' : 'promedio-fail';
                        }
                    }

                    $fecha = $entrega['submitted_at'] ?? $entrega['created_at'] ?? null;
                    $fechaText = $fecha ? date('d/m/Y H:i', strtotime($fecha)) : '—';
                    $maxInt = (int) ($entrega['max_intentos'] ?? 0);
                    $maxLabel = $maxInt > 0 ? "de {$maxInt}" : '(ilimitado)';

                    $canView = in_array($entrega['estado'], ['Enviado', 'Aprobado', 'Rechazado']);
                ?>
                    <?php $searchStr = mb_strtolower(($entrega['caso_titulo'] ?? '') . ' ' . ($entrega['estado'] ?? '')); ?>
                    <tr data-search="<?= htmlspecialchars($searchStr) ?>">
                        <td><span class="caso-tag"><?= htmlspecialchars($entrega['caso_titulo'] ?? '—') ?></span></td>
                        <td><span class="intento-display">#<?= (int) ($entrega['numero_intento'] ?? 0) ?> <?= $maxLabel ?></span></td>
                        <td><?= $fechaText ?></td>
                        <td><span class="status-badge <?= $estado['class'] ?>"><?= $estado['label'] ?></span></td>
                        <td><span class="promedio-cell <?= $notaClass ?>"><?= $notaText ?></span></td>
                        <td>
                            <?php if ($canView): ?>
                                <a href="<?= base_url('/entregas/' . $entrega['intento_id']) ?>" class="ver-link">
                                    Ver
                                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor"
                                        stroke-width="2.5" stroke-linecap="round">
                                        <path d="M5 12h14" />
                                        <polyline points="12 5 19 12 12 19" />
                                    </svg>
                                </a>
                            <?php else: ?>
                                <span class="ver-link" style="opacity:0.4; pointer-events:none;">En curso</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
        </tbody>
    </table>

    <div class="table-footer" data-footer-for="entregas-est-table">
        <span class="table-footer-info"></span>
        <div class="pagination"></div>
    </div>
</div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/logged_layout.php';
?>