<?php
declare(strict_types=1);

// ARCHIVO: resources/views/admin/usuarios/gestionar_profesores.php

$pageTitle = 'Gestión de Estudiantes';
$activePage = 'estudiantes';
$breadcrumbs = [
    'Inicio' => base_url('/admin'),
    'Gestión de Usuarios' => '#',
    'Estudiantes' => '#'
];

$extraCss = '<link rel="stylesheet" href="' . asset('css/professor/casos_sucesorales.css') . '">';

ob_start();
?>
<div class="page-header">
    <div class="page-header-left">
        <h1>Estudiantes Registrados</h1>
        <p>Visualice y gestione las cuentas de los estudiantes inscritos en el Simulador SENIAT.</p>
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
            <input type="text" id="searchInput" placeholder="Buscar por nombre, cédula o correo...">
        </div>

        <button class="filter-chip active" data-filter="Todos">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3" />
            </svg>
            Todos
        </button>
        <button class="filter-chip" data-filter="Activo">Activos</button>
        <button class="filter-chip" data-filter="Inactivo">Inactivos</button>
    </div>
</div>

<!-- Data Table -->
<div class="table-container">
    <table class="data-table">
        <thead>
            <tr>
                <th class="sortable" data-sort="estudiante">Estudiante</th>
                <th class="sortable" data-sort="cedula">Cédula</th>
                <th>Sede</th>
                <th>Período Ingreso</th>
                <th class="sortable" data-sort="fecha">Fecha de Registro</th>
                <th class="sortable" data-sort="estado">Estado</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <!-- Mock Row 1 -->
            <tr>
                <td>
                    <div class="causante-cell">
                        <div class="causante-avatar m">JP</div>
                        <div class="causante-info">
                            <span class="causante-name">Juan Pérez</span>
                            <span class="causante-ci">juan.perez@est.ucab.edu.ve</span>
                        </div>
                    </div>
                </td>
                <td class="case-id" style="font-size: 14px;">V-29123456</td>
                <td>Montalbán</td>
                <td>2024-II</td>
                <td class="date-cell">10 Mar 2026<br><span class="date-relative">Hace 2 días</span></td>
                <td><span class="status-badge status-published">Activo</span></td>
                <td>
                    <div class="row-actions">
                        <!-- Reset Password Button -->
                        <button class="row-action-btn" title="Restablecer Contraseña"
                            onclick="alert('Se enviará un correo para restablecer.')">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                                <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                            </svg>
                        </button>
                        <button class="row-action-btn btn-inactivar-caso" title="Desactivar"
                            onclick="window.modalManager.open('modal-eliminar')">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round">
                                <path d="M18.36 6.64a9 9 0 1 1-12.73 0" />
                                <line x1="12" y1="2" x2="12" y2="12" />
                            </svg>
                        </button>
                    </div>
                </td>
            </tr>

            <!-- Mock Row 2 -->
            <tr>
                <td>
                    <div class="causante-cell">
                        <div class="causante-avatar f">CV</div>
                        <div class="causante-info">
                            <span class="causante-name">Camila Vargas</span>
                            <span class="causante-ci">cvargas@est.ucab.edu.ve</span>
                        </div>
                    </div>
                </td>
                <td class="case-id" style="font-size: 14px;">V-30987654</td>
                <td>Guayana</td>
                <td>2025-I</td>
                <td class="date-cell">1 Mar 2026<br><span class="date-relative">Hace 1 semana</span></td>
                <td><span class="status-badge status-draft">Inactivo</span></td>
                <td>
                    <div class="row-actions">
                        <!-- Reset Password Button -->
                        <button class="row-action-btn" title="Restablecer Contraseña"
                            onclick="alert('Se enviará un correo para restablecer.')">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                                <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                            </svg>
                        </button>
                        <button class="row-action-btn btn-reactivar-caso" title="Reactivar">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round">
                                <polyline points="23 4 23 10 17 10" />
                                <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10" />
                            </svg>
                        </button>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Pagination Mock (Matched to global pagination style) -->
    <div class="pagination-wrapper"
        style="padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--gray-200); background: #fafafa; border-radius: 0 0 var(--radius-lg) var(--radius-lg);">
        <div style="font-size: 12px; color: var(--gray-500);">
            Mostrando <span style="font-weight: 600; color: var(--gray-700);">1</span> a <span
                style="font-weight: 600; color: var(--gray-700);">2</span> de <span
                style="font-weight: 600; color: var(--gray-700);">240</span> registros
        </div>
        <div class="pagination" style="display: flex; gap: 5px;">
            <button class="btn btn-secondary btn-sm" disabled
                style="padding: 4px 10px; font-size: 13px; border-radius: var(--radius-sm); border-color: var(--gray-300);">Anterior</button>
            <button class="btn btn-primary btn-sm"
                style="padding: 4px 12px; font-size: 13px; border-radius: var(--radius-sm);">1</button>
            <button class="btn btn-secondary btn-sm"
                style="padding: 4px 12px; font-size: 13px; border-radius: var(--radius-sm); border-color: var(--gray-300);">2</button>
            <button class="btn btn-secondary btn-sm"
                style="padding: 4px 10px; font-size: 13px; border-radius: var(--radius-sm); border-color: var(--gray-300);">Siguiente</button>
        </div>
    </div>
</div>

<!-- ==============================================
     MODALES 
     ============================================== -->

<!-- Modal: Confirmar Desactivación -->
<dialog class="modal-base" id="modal-eliminar">
    <div class="modal-base__container" style="max-width: 480px;">
        <div class="modal-base__header">
            <h2 class="modal-base__title">¿Desactivar estudiante?</h2>
            <button class="modal-base__close" onclick="window.modalManager.close('modal-eliminar')"
                aria-label="Cerrar modal">&times;</button>
        </div>
        <div class="modal-base__body">
            <p style="font-size: 15px; color: var(--text-body); line-height: 1.5; margin-bottom: 0;">
                Si desactiva a este estudiante, <strong>no podrá iniciar sesión en el simulador</strong> ni continuar
                sus resoluciones de casos hasta que sea reactivado. Sus casos en curso no serán eliminados.
            </p>
            <form id="formDesactivarEstudiante" action="<?= base_url('/admin/estudiantes/desactivar') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="desactivar_estudiante_id" value="">
            </form>
        </div>
        <div class="modal-base__footer" style="padding-top: 24px;">
            <button class="modal-btn modal-btn-cancel" style="min-width: 120px;"
                onclick="window.modalManager.close('modal-eliminar')">Cancelar</button>
            <button type="submit" form="formDesactivarEstudiante" class="modal-btn modal-btn-danger"
                style="min-width: 120px;">
                Sí, desactivar
            </button>
        </div>
    </div>
</dialog>

<!-- Modal: Restablecer Contraseña -->
<dialog class="modal-base" id="modal-reset-password">
    <div class="modal-base__container" style="max-width: 460px;">
        <div class="modal-base__header">
            <h2 class="modal-base__title">¿Restablecer contraseña?</h2>
            <button class="modal-base__close" onclick="window.modalManager.close('modal-reset-password')"
                aria-label="Cerrar modal">&times;</button>
        </div>
        <div class="modal-base__body">
            <p style="font-size: 15px; color: var(--text-body); line-height: 1.5; margin-bottom: 0;">
                Se enviará un correo electrónico al estudiante <strong id="reset-student-name"></strong> con un
                enlace para restablecer su contraseña. El enlace expirará en 24 horas.
            </p>
            <form id="formResetPassword" action="<?= base_url('/admin/estudiantes/reset-password') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="reset_estudiante_id" value="">
            </form>
        </div>
        <div class="modal-base__footer">
            <button class="modal-btn modal-btn-cancel"
                onclick="window.modalManager.close('modal-reset-password')">Cancelar</button>
            <button type="submit" form="formResetPassword" class="modal-btn modal-btn-primary">
                Enviar correo
            </button>
        </div>
    </div>
</dialog>

<script>
    function openDesactivarEstudiante(id) {
        document.getElementById('desactivar_estudiante_id').value = id;
        window.modalManager.open('modal-eliminar');
    }

    function openResetPassword(id, nombre) {
        document.getElementById('reset_estudiante_id').value = id;
        document.getElementById('reset-student-name').textContent = nombre;
        window.modalManager.open('modal-reset-password');
    }
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/logged_layout.php';
?>