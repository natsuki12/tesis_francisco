<?php
declare(strict_types=1);

// ARCHIVO: resources/views/shared/perfil.php
// Vista compartida de perfil para Estudiantes (role_id=3) y Profesores (role_id=2).

$pageTitle = 'Mi Perfil — Simulador SENIAT';
$activePage = 'perfil';
$extraCss = '<link rel="stylesheet" href="' . asset('css/shared/perfil.css') . '">';

// ── Datos reales desde el controlador/ruta ──────────────────
$roleId = (int) ($_SESSION['role_id'] ?? 3);

$persona = [
    'nombres'          => $personaRow['nombres'] ?? ($_SESSION['user_name'] ?? 'Nombre'),
    'apellidos'        => $personaRow['apellidos'] ?? '—',
    'nacionalidad'     => $personaRow['nacionalidad'] ?? 'V',
    'cedula'           => $personaRow['cedula'] ?? '—',
    'fecha_nacimiento' => $personaRow['fecha_nacimiento'] ?? null,
    'genero'           => $personaRow['genero'] ?? null,
    'email'            => $_SESSION['email'] ?? 'correo@ejemplo.com',
];

// Formatear cédula con puntos: 31120479 → 31.120.479
$cedulaRaw = preg_replace('/[^0-9]/', '', $persona['cedula']);
$persona['cedula'] = $cedulaRaw ? number_format((float) $cedulaRaw, 0, '', '.') : '—';

// Solo estudiante
$estudiante = [
    'carrera' => $estudianteRow['carrera'] ?? '—',
    'seccion' => $estudianteRow['seccion'] ?? '—',
];

// Solo profesor
$profesor = [
    'titulo'        => $profesorRow['titulo'] ?? '—',
    'firma_digital' => $profesorRow['firma_digital'] ?? null,
];

// Fecha de registro
$memberSince = !empty($userRow['created_at']) ? $userRow['created_at'] : null;

$initials = mb_strtoupper(
    mb_substr($persona['nombres'], 0, 1) . mb_substr($persona['apellidos'], 0, 1)
);

ob_start();
?>

<!-- Page Header -->
<div class="page-header">
    <div class="page-header-left">
        <h1>Mi Perfil</h1>
        <p>Información de tu cuenta</p>
    </div>
</div>

<!-- Profile Layout -->
<div class="perfil-layout">

    <!-- Left: Avatar Card -->
    <div class="perfil-avatar-card animate-in">
        <div class="perfil-avatar">
            <span class="perfil-avatar-initials">
                <?= htmlspecialchars($initials) ?>
            </span>
        </div>
        <h2 class="perfil-name">
            <?= htmlspecialchars($persona['nombres'] . ' ' . $persona['apellidos']) ?>
        </h2>
        <span class="perfil-role-badge <?= $roleId === 2 ? 'role-profesor' : 'role-estudiante' ?>">
            <?= $roleId === 2 ? 'Profesor' : 'Estudiante' ?>
        </span>
        <div class="perfil-meta">
            <div class="perfil-meta-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                    <polyline points="22,6 12,13 2,6" />
                </svg>
                <?= htmlspecialchars($persona['email']) ?>
            </div>
            <div class="perfil-meta-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                    <line x1="16" y1="2" x2="16" y2="6" />
                    <line x1="8" y1="2" x2="8" y2="6" />
                    <line x1="3" y1="10" x2="21" y2="10" />
                </svg>
                Miembro desde
                <?= $memberSince ? date('d/m/Y', strtotime($memberSince)) : '—' ?>
            </div>
        </div>
    </div>

    <!-- Right: Data Panels -->
    <div class="perfil-panels">

        <!-- Datos Personales -->
        <div class="panel animate-in">
            <div class="panel-header">
                <h3>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        width="20" height="20">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                        <circle cx="12" cy="7" r="4" />
                    </svg>
                    Datos Personales
                </h3>
            </div>
            <div class="panel-body">
                <div class="perfil-grid">
                    <div class="perfil-field">
                        <label>Nombres</label>
                        <span>
                            <?= htmlspecialchars($persona['nombres']) ?>
                        </span>
                    </div>
                    <div class="perfil-field">
                        <label>Apellidos</label>
                        <span>
                            <?= htmlspecialchars($persona['apellidos']) ?>
                        </span>
                    </div>
                    <div class="perfil-field">
                        <label>Cédula</label>
                        <span>
                            <?= htmlspecialchars($persona['nacionalidad'] . '-' . $persona['cedula']) ?>
                        </span>
                    </div>
                    <div class="perfil-field">
                        <label>Fecha de nacimiento</label>
                        <span>
                            <?= $persona['fecha_nacimiento'] ? date('d/m/Y', strtotime($persona['fecha_nacimiento'])) : '—' ?>
                        </span>
                    </div>
                    <div class="perfil-field">
                        <label>Género</label>
                        <span>
                            <?= htmlspecialchars($persona['genero'] ?: '—') ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Datos Académicos -->
        <div class="panel animate-in">
            <div class="panel-header">
                <h3>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        width="20" height="20">
                        <path d="M22 10v6M2 10l10-5 10 5-10 5z" />
                        <path d="M6 12v5c0 2 3 3 6 3s6-1 6-3v-5" />
                    </svg>
                    Datos Académicos
                </h3>
            </div>
            <div class="panel-body">
                <div class="perfil-grid">
                    <?php if ($roleId === 3): ?>
                        <!-- Estudiante -->
                        <div class="perfil-field">
                            <label>Carrera</label>
                            <span>
                                <?= htmlspecialchars($estudiante['carrera']) ?>
                            </span>
                        </div>
                        <div class="perfil-field">
                            <label>Sección actual</label>
                            <span>
                                <?= htmlspecialchars($estudiante['seccion']) ?>
                            </span>
                        </div>
                    <?php elseif ($roleId === 2): ?>
                        <!-- Profesor -->
                        <div class="perfil-field">
                            <label>Título</label>
                            <span>
                                <?= htmlspecialchars(ucfirst($profesor['titulo'])) ?>
                            </span>
                        </div>
                        <div class="perfil-field">
                            <label>Firma digital</label>
                            <?php if ($profesor['firma_digital']): ?>
                                <span class="perfil-firma-status perfil-firma-ok">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14"
                                        height="14">
                                        <polyline points="20 6 9 17 4 12" />
                                    </svg>
                                    Cargada
                                </span>
                            <?php else: ?>
                                <span class="perfil-firma-status perfil-firma-pending">Sin firma cargada</span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Seguridad -->
        <div class="panel animate-in">
            <div class="panel-header">
                <h3>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        width="20" height="20">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                        <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                    </svg>
                    Seguridad
                </h3>
            </div>
            <div class="panel-body">
                <div class="perfil-grid">
                    <div class="perfil-field">
                        <label>Correo electrónico</label>
                        <span>
                            <?= htmlspecialchars($persona['email']) ?>
                        </span>
                    </div>
                    <div class="perfil-field">
                        <label>Contraseña</label>
                        <span>••••••••</span>
                    </div>
                </div>
                <div class="perfil-actions">
                    <button class="btn btn-outline" onclick="alert('Cambiar contraseña (placeholder)')">
                        Cambiar contraseña
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/logged_layout.php';
?>