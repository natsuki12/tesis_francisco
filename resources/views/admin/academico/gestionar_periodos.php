<?php
declare(strict_types=1);

// ARCHIVO: resources/views/admin/academico/gestionar_periodos.php

$pageTitle = 'Gestión de Períodos';
$activePage = 'periodos';
$breadcrumbs = [
    'Inicio' => base_url('/admin'),
    'Gestión Académica' => '#',
    'Períodos' => '#'
];

$extraCss = '<link rel="stylesheet" href="' . asset('css/professor/casos_sucesorales.css') . '">';

ob_start();
?>
<div class="page-header">
    <div class="page-header-left">
        <h1>Períodos Académicos</h1>
        <p>Gestione los períodos académicos en los que se dictará el uso del Simulador.</p>
    </div>
    <button class="btn btn-primary" onclick="window.modalManager.open('modal-periodo')">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
            stroke-linecap="round">
            <line x1="12" y1="5" x2="12" y2="19" />
            <line x1="5" y1="12" x2="19" y2="12" />
        </svg>
        Crear Período
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
                <th class="sortable" data-sort="periodo">Período</th>
                <th class="sortable" data-sort="inicio">Fecha Inicio</th>
                <th class="sortable" data-sort="fin">Fecha Fin</th>
                <th>Estudiantes Inscritos</th>
                <th class="sortable" data-sort="estado">Estado</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <!-- Mock Row 1 -->
            <tr>
                <td><strong>2026-I</strong></td>
                <td>01 Mar 2026</td>
                <td>15 Jul 2026</td>
                <td><strong>120</strong> matriculados</td>
                <td><span class="status-badge status-published">Activo</span></td>
                <td>
                    <div class="row-actions">
                        <button class="row-action-btn" title="Editar"
                            onclick="window.modalManager.open('modal-periodo')">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                            </svg>
                        </button>
                        <button class="row-action-btn btn-inactivar-caso" title="Cerrar Período"
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
                <td><strong>2025-II</strong></td>
                <td>15 Sep 2025</td>
                <td>30 Ene 2026</td>
                <td><strong>105</strong> matriculados</td>
                <td><span class="status-badge status-draft">Inactivo</span></td>
                <td>
                    <div class="row-actions">
                        <button class="row-action-btn" title="Editar"
                            onclick="window.modalManager.open('modal-periodo')">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
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

<!-- Modal: Crear/Editar Período -->
<dialog class="modal-base" id="modal-periodo">
    <div class="modal-base__container" style="max-width: 500px;">
        <div class="modal-base__header">
            <h2 class="modal-base__title">Crear Período Académico</h2>
            <button class="modal-base__close" onclick="window.modalManager.close('modal-periodo')"
                aria-label="Cerrar modal">&times;</button>
        </div>
        <div class="modal-base__body">
            <p style="font-size: 14px; color: var(--text-light); margin-bottom: 20px;">
                Defina el código del período y su rango de fechas. Los estudiantes solo podrán
                inscribirse y trabajar durante períodos activos.
            </p>
            <form id="formPeriodo" action="<?= base_url('/admin/periodos/guardar') ?>" method="POST"
                style="display: flex; flex-direction: column; gap: 16px;">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="periodo_id" value="">

                <div class="form-group">
                    <label class="form-label">Código del Período</label>
                    <input type="text" name="codigo" id="periodo_codigo" class="form-input" placeholder="Ej: 2026-I"
                        maxlength="20" required>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Fecha de Inicio</label>
                        <input type="date" name="fecha_inicio" id="periodo_fecha_inicio" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Fecha de Fin</label>
                        <input type="date" name="fecha_fin" id="periodo_fecha_fin" class="form-input" required>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-base__footer">
            <button type="button" class="modal-btn modal-btn-cancel"
                onclick="window.modalManager.close('modal-periodo')">Cancelar</button>
            <button type="submit" form="formPeriodo" class="modal-btn modal-btn-primary">Guardar
                Período</button>
        </div>
    </div>
</dialog>

<!-- Modal: Confirmar Cierre de Período -->
<dialog class="modal-base" id="modal-eliminar">
    <div class="modal-base__container" style="max-width: 480px;">
        <div class="modal-base__header">
            <h2 class="modal-base__title">¿Cerrar Período?</h2>
            <button class="modal-base__close" onclick="window.modalManager.close('modal-eliminar')"
                aria-label="Cerrar modal">&times;</button>
        </div>
        <div class="modal-base__body">
            <p style="font-size: 15px; color: var(--text-body); line-height: 1.5; margin-bottom: 0;">
                Al cerrar este período, <strong>todas las secciones asociadas pasarán a estado inactivo</strong>
                y los estudiantes no podrán continuar trabajando en sus casos asignados. Esta acción
                puede ser revertida posteriormente reactivando el período.
            </p>
            <form id="formCerrarPeriodo" action="<?= base_url('/admin/periodos/cerrar') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="cerrar_periodo_id" value="">
            </form>
        </div>
        <div class="modal-base__footer" style="padding-top: 24px;">
            <button class="modal-btn modal-btn-cancel" style="min-width: 120px;"
                onclick="window.modalManager.close('modal-eliminar')">Cancelar</button>
            <button type="submit" form="formCerrarPeriodo" class="modal-btn modal-btn-danger"
                style="min-width: 120px;">Cerrar Período</button>
        </div>
    </div>
</dialog>

<script>
    function openCrearPeriodo() {
        document.getElementById('formPeriodo').reset();
        document.getElementById('periodo_id').value = '';
        document.querySelector('#modal-periodo .modal-base__title').textContent = 'Crear Período Académico';
        window.modalManager.open('modal-periodo');
    }

    function openEditarPeriodo(btn) {
        document.getElementById('formPeriodo').reset();
        document.getElementById('periodo_id').value = btn.dataset.id || '';
        document.getElementById('periodo_codigo').value = btn.dataset.codigo || '';
        document.getElementById('periodo_fecha_inicio').value = btn.dataset.fechaInicio || '';
        document.getElementById('periodo_fecha_fin').value = btn.dataset.fechaFin || '';
        document.querySelector('#modal-periodo .modal-base__title').textContent = 'Editar Período';
        window.modalManager.open('modal-periodo');
    }

    function openCerrarPeriodo(id) {
        document.getElementById('cerrar_periodo_id').value = id;
        window.modalManager.open('modal-eliminar');
    }
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/logged_layout.php';
?>