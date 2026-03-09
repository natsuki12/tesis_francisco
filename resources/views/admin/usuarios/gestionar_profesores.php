<?php
declare(strict_types=1);

// ARCHIVO: resources/views/admin/usuarios/gestionar_profesores.php

$pageTitle = 'Gestión de Profesores';
$activePage = 'profesores';
$breadcrumbs = [
    'Inicio' => base_url('/admin'),
    'Gestión de Usuarios' => '#',
    'Profesores' => '#'
];

$extraCss = '<link rel="stylesheet" href="' . asset('css/professor/casos_sucesorales.css') . '">';

ob_start();
?>
<div class="page-header">
    <div class="page-header-left">
        <h1>Profesores Autorizados</h1>
        <p>Gestione el acceso y credenciales del personal docente interactuando con el sistema.</p>
    </div>
    <button class="btn btn-primary" onclick="openCrearProfesor()">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
            stroke-linecap="round">
            <line x1="12" y1="5" x2="12" y2="19" />
            <line x1="5" y1="12" x2="19" y2="12" />
        </svg>
        Registrar Profesor
    </button>
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
                <th class="sortable" data-sort="profesor">Profesor</th>
                <th class="sortable" data-sort="cedula">Cédula</th>
                <th>Secciones Asignadas</th>
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
                        <div class="causante-avatar m">CR</div>
                        <div class="causante-info">
                            <span class="causante-name">César Requena</span>
                            <span class="causante-ci">cesar.requena@ucab.edu.ve</span>
                        </div>
                    </div>
                </td>
                <td class="case-id" style="font-size: 14px;">V-12345678</td>
                <td><strong>2</strong> activas (2026-I)</td>
                <td class="date-cell">15 Ene 2026<br><span class="date-relative">Hace 2 meses</span></td>
                <td><span class="status-badge status-published">Activo</span></td>
                <td>
                    <div class="row-actions">
                        <button class="row-action-btn" title="Editar" data-id="1" data-tipo-cedula="V"
                            data-cedula="12345678" data-nombres="César" data-apellidos="Requena"
                            data-email="cesar.requena@ucab.edu.ve" data-estado="1" onclick="openEditarProfesor(this)">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                            </svg>
                        </button>
                        <button class="row-action-btn btn-inactivar-caso" title="Desactivar"
                            onclick="openEliminarProfesor(1)">
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
                        <div class="causante-avatar f">MR</div>
                        <div class="causante-info">
                            <span class="causante-name">María Rodríguez</span>
                            <span class="causante-ci">mrodriguez@ucab.edu.ve</span>
                        </div>
                    </div>
                </td>
                <td class="case-id" style="font-size: 14px;">V-16789012</td>
                <td><strong>1</strong> activa (2026-I)</td>
                <td class="date-cell">12 Feb 2026<br><span class="date-relative">Hace 1 mes</span></td>
                <td><span class="status-badge status-published">Activo</span></td>
                <td>
                    <div class="row-actions">
                        <button class="row-action-btn" title="Editar" data-id="2" data-tipo-cedula="V"
                            data-cedula="16789012" data-nombres="María" data-apellidos="Rodríguez"
                            data-email="mrodriguez@ucab.edu.ve" data-estado="1" onclick="openEditarProfesor(this)">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                            </svg>
                        </button>
                        <button class="row-action-btn btn-inactivar-caso" title="Desactivar"
                            onclick="openEliminarProfesor(2)">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round">
                                <path d="M18.36 6.64a9 9 0 1 1-12.73 0" />
                                <line x1="12" y1="2" x2="12" y2="12" />
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
                style="font-weight: 600; color: var(--gray-700);">15</span> registros
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

<!-- Modal: Crear/Editar Profesor -->
<dialog class="modal-base" id="modal-profesor">
    <div class="modal-base__container" style="max-width: 500px;">
        <div class="modal-base__header">
            <h2 class="modal-base__title">Registrar Nuevo Profesor</h2>
            <button class="modal-base__close" onclick="window.modalManager.close('modal-profesor')"
                aria-label="Cerrar modal">&times;</button>
        </div>
        <div class="modal-base__body">
            <p style="font-size: 14px; color: var(--color-text-light); margin-bottom: 20px;">
                Ingrese los datos del docente. Se le enviará automáticamente un correo con las instrucciones de acceso.
            </p>
            <form id="formProfesor" action="<?= base_url('/admin/profesores/guardar') ?>" method="POST"
                style="display: flex; flex-direction: column; gap: 16px;">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="profesor_id" value="">

                <div class="form-group">
                    <label class="form-label">Cédula de Identidad</label>
                    <div style="display:flex; gap: 8px;">
                        <select name="tipo_cedula" id="profesor_tipo_cedula" class="form-select" style="width: 80px;"
                            required>
                            <option value="V">V-</option>
                            <option value="E">E-</option>
                        </select>
                        <input type="text" name="cedula" id="profesor_cedula" class="form-input" style="flex:1;"
                            placeholder="Ej: 12345678" required>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Nombres</label>
                        <input type="text" name="nombres" id="profesor_nombres" class="form-input"
                            placeholder="Ej: Juan Antonio" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Apellidos</label>
                        <input type="text" name="apellidos" id="profesor_apellidos" class="form-input"
                            placeholder="Ej: Pérez Gómez" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Correo Electrónico</label>
                    <input type="email" name="email" id="profesor_email" class="form-input"
                        placeholder="usuario@ucab.edu.ve" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Estado Inicial</label>
                    <select name="estado" id="profesor_estado" class="form-select" required>
                        <option value="1">Activo (Habilitado para Login)</option>
                        <option value="0">Inactivo (Suspendido)</option>
                    </select>
                </div>
            </form>
        </div>
        <div class="modal-base__footer">
            <button type="button" class="modal-btn modal-btn-cancel"
                onclick="window.modalManager.close('modal-profesor')">Cancelar</button>
            <button type="submit" form="formProfesor" class="modal-btn modal-btn-primary">Guardar Profesor</button>
        </div>
    </div>
</dialog>

<!-- Modal: Confirmar Desactivación -->
<dialog class="modal-base" id="modal-eliminar">
    <div class="modal-base__container" style="max-width: 480px;">
        <!-- Se usa el mismo overlay oscuro y desenfocado requerido por el cliente (UI Consistency) -->
        <div class="modal-base__header">
            <h2 class="modal-base__title">¿Desactivar profesor?</h2>
            <button class="modal-base__close" onclick="window.modalManager.close('modal-eliminar')"
                aria-label="Cerrar modal">&times;</button>
        </div>
        <div class="modal-base__body">
            <p style="font-size: 15px; color: var(--color-text-body); line-height: 1.5; margin-bottom: 0;">
                Si desactiva este profesor, <strong>no podrá iniciar sesión en el simulador</strong> ni revisar casos
                hasta que sea reactivado. Sus secciones asignadas quedarán huérfanas o pasarán a estar inactivas si es
                el único docente asignado.
            </p>
            <form id="formEliminarProfesor" action="<?= base_url('/admin/profesores/eliminar') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="eliminar_profesor_id" value="">
            </form>
        </div>
        <div class="modal-base__footer" style="padding-top: 24px;">
            <button class="modal-btn modal-btn-cancel" style="min-width: 120px;"
                onclick="window.modalManager.close('modal-eliminar')">Cancelar</button>
            <button type="submit" form="formEliminarProfesor" class="modal-btn modal-btn-danger"
                style="min-width: 120px;">
                Sí, desactivar
            </button>
        </div>
    </div>
</dialog>

<script>
    // Funciones Helper para poblar modales desde los botones de la tabla
    function openCrearProfesor() {
        document.getElementById('formProfesor').reset();
        document.getElementById('profesor_id').value = '';
        document.querySelector('#modal-profesor .modal-base__title').textContent = 'Registrar Nuevo Profesor';
        window.modalManager.open('modal-profesor');
    }

    function openEditarProfesor(btn) {
        document.getElementById('formProfesor').reset();
        document.getElementById('profesor_id').value = btn.dataset.id || '';
        document.getElementById('profesor_tipo_cedula').value = btn.dataset.tipoCedula || 'V';
        document.getElementById('profesor_cedula').value = btn.dataset.cedula || '';
        document.getElementById('profesor_nombres').value = btn.dataset.nombres || '';
        document.getElementById('profesor_apellidos').value = btn.dataset.apellidos || '';
        document.getElementById('profesor_email').value = btn.dataset.email || '';
        document.getElementById('profesor_estado').value = btn.dataset.estado || '1';

        document.querySelector('#modal-profesor .modal-base__title').textContent = 'Editar Profesor';
        window.modalManager.open('modal-profesor');
    }

    function openEliminarProfesor(id) {
        document.getElementById('eliminar_profesor_id').value = id;
        window.modalManager.open('modal-eliminar');
    }
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/logged_layout.php';
?>