<?php
declare(strict_types=1);

// ARCHIVO: resources/views/admin/configuracion/catalogos.php

$pageTitle = 'Catálogos del Sistema';
$activePage = 'catalogos';
$breadcrumbs = [
    'Inicio' => base_url('/admin'),
    'Configuración' => '#',
    'Catálogos' => '#'
];

$extraCss = '
    <link rel="stylesheet" href="' . asset('css/professor/casos_sucesorales.css') . '">
    <link rel="stylesheet" href="' . asset('css/admin/configuracion.css') . '">
';

ob_start();
?>

<div class="page-header">
    <div class="page-header-left">
        <h1>Catálogos Maestros</h1>
        <p>Gestione las tablas maestras que alimentan los selectores del simulador.</p>
    </div>
</div>

<!-- Tabs Navigation -->
<div class="config-tabs-nav">
    <button class="config-tab-btn active" onclick="switchTab('tab-ut')">Unidad Tributaria</button>
    <button class="config-tab-btn" onclick="switchTab('tab-parentescos')">Parentescos</button>
    <button class="config-tab-btn" onclick="switchTab('tab-inmuebles')">Bienes Inmuebles</button>
    <button class="config-tab-btn" onclick="switchTab('tab-muebles')">Bienes Muebles</button>
    <button class="config-tab-btn" onclick="switchTab('tab-pasivos')">Pasivos y Otros</button>
</div>

<!-- Contenido: Unidad Tributaria -->
<div id="tab-ut" class="config-tab-pane active">
    <!-- Toolbar -->
    <div class="toolbar" style="margin-bottom: 16px;">
        <div class="toolbar-left">
            <div class="search-box">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <circle cx="11" cy="11" r="8" />
                    <path d="m21 21-4.35-4.35" />
                </svg>
                <input type="text" placeholder="Buscar histórico UTM...">
            </div>
        </div>
        <div class="filters">
            <button class="btn btn-primary" onclick="window.modalManager.open('modal-nuevo-ut')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                    stroke-linecap="round">
                    <line x1="12" y1="5" x2="12" y2="19" />
                    <line x1="5" y1="12" x2="19" y2="12" />
                </svg>
                Actualizar Valor UT
            </button>
        </div>
    </div>

    <!-- Table -->
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Fecha Vigencia</th>
                    <th>Valor (Bs.)</th>
                    <th>Resolución Oficial</th>
                    <th>Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>01/01/2026</strong></td>
                    <td>Bs. 9.00</td>
                    <td>Gaceta Nº 42.123</td>
                    <td><span class="status-badge status-published">Vigente</span></td>
                    <td>
                        <div class="row-actions">
                            <button class="row-action-btn" title="Editar"><svg viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                                </svg></button>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td><strong>15/05/2024</strong></td>
                    <td>Bs. 0.40</td>
                    <td>Gaceta Nº 42.100</td>
                    <td><span class="status-badge status-draft">Histórico</span></td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Contenidos Placeholder: Otros Tabs -->
<div id="tab-parentescos" class="config-tab-pane">
    <div class="table-container" style="padding: 40px; text-align: center; color: var(--color-text-light);">
        <p>Gestión de grados de consanguinidad para herederos.</p>
    </div>
</div>

<!-- Tab: Bienes Inmuebles -->
<div id="tab-inmuebles" class="config-tab-pane">
    <div class="table-container" style="padding: 40px; text-align: center; color: var(--color-text-light);">
        <h3 style="color:var(--color-text-dark); margin-bottom: 8px;">Tipos de Bien Inmueble</h3>
        <p>Gestión de los 21 tipos de inmuebles (sim_cat_tipos_bien_inmueble).</p>
    </div>
</div>

<!-- Tab: Bienes Muebles -->
<div id="tab-muebles" class="config-tab-pane">
    <div style="display: flex; flex-direction: column; gap: 24px;">
        <div class="table-container" style="padding: 30px; text-align: center; color: var(--color-text-light);">
            <h3 style="color:var(--color-text-dark); margin-bottom: 8px;">Categorías de Bien Mueble</h3>
            <p>12 categorías principales (sim_cat_categorias_bien_mueble).</p>
        </div>
        <div class="table-container" style="padding: 30px; text-align: center; color: var(--color-text-light);">
            <h3 style="color:var(--color-text-dark); margin-bottom: 8px;">Tipos de Bien Mueble</h3>
            <p>22 subtipos vinculados a categorías (sim_cat_tipos_bien_mueble).</p>
        </div>
        <div class="table-container" style="padding: 30px; text-align: center; color: var(--color-text-light);">
            <h3 style="color:var(--color-text-dark); margin-bottom: 8px;">Tipos de Semovientes</h3>
            <p>11 tipos de animales ganaderos (sim_cat_tipos_semoviente).</p>
        </div>
    </div>
</div>

<!-- Tab: Pasivos y Otros -->
<div id="tab-pasivos" class="config-tab-pane">
    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 24px;">
        <div class="table-container" style="padding: 30px; text-align: center; color: var(--color-text-light);">
            <h3 style="color:var(--color-text-dark); margin-bottom: 8px;">Tipos de Deuda</h3>
            <p>4 tipos (sim_cat_tipos_pasivo_deuda).</p>
        </div>
        <div class="table-container" style="padding: 30px; text-align: center; color: var(--color-text-light);">
            <h3 style="color:var(--color-text-dark); margin-bottom: 8px;">Tipos de Gasto</h3>
            <p>7 tipos (sim_cat_tipos_pasivo_gasto).</p>
        </div>
        <div class="table-container" style="padding: 30px; text-align: center; color: var(--color-text-light);">
            <h3 style="color:var(--color-text-dark); margin-bottom: 8px;">Bancos</h3>
            <p>31 instituciones financieras (sim_cat_bancos).</p>
        </div>
        <div class="table-container" style="padding: 30px; text-align: center; color: var(--color-text-light);">
            <h3 style="color:var(--color-text-dark); margin-bottom: 8px;">Tipos de Herencia</h3>
            <p>6 clasificaciones jurídicas (sim_cat_tipoherencias).</p>
        </div>
    </div>
</div>

<!-- JS Simple para las pestañas -->
<script>
    function switchTab(tabId) {
        // Renombrar botón activo
        document.querySelectorAll('.config-tab-btn').forEach(btn => btn.classList.remove('active'));
        event.currentTarget.classList.add('active');

        // Mostrar panel activo
        document.querySelectorAll('.config-tab-pane').forEach(pane => pane.classList.remove('active'));
        document.getElementById(tabId).classList.add('active');
    }
</script>

<!-- ==============================================
     MODALES 
     ============================================== -->

<!-- Modal: Actualizar Valor UT -->
<dialog class="modal-base" id="modal-nuevo-ut">
    <div class="modal-base__container" style="max-width: 480px;">
        <div class="modal-base__header">
            <h2 class="modal-base__title">Actualizar Unidad Tributaria</h2>
            <button class="modal-base__close" onclick="window.modalManager.close('modal-nuevo-ut')"
                aria-label="Cerrar modal">&times;</button>
        </div>
        <div class="modal-base__body">
            <p style="font-size: 14px; color: var(--text-light); margin-bottom: 20px;">
                Registre el nuevo valor de la Unidad Tributaria según Gaceta Oficial. Al guardar,
                el valor anterior pasará automáticamente a estado "Histórico".
            </p>
            <form id="formActualizarUT" action="<?= base_url('/admin/catalogos/ut/guardar') ?>" method="POST"
                style="display: flex; flex-direction: column; gap: 16px;">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="ut_id" value="">

                <div class="form-group">
                    <label class="form-label">Valor en Bolívares (Bs.)</label>
                    <input type="text" name="valor" id="ut_valor" class="form-input" placeholder="Ej: 9.00"
                        maxlength="15" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Fecha de Vigencia</label>
                    <input type="date" name="fecha_vigencia" id="ut_fecha" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Resolución / Gaceta Oficial</label>
                    <input type="text" name="resolucion" id="ut_resolucion" class="form-input"
                        placeholder="Ej: Gaceta Nº 42.123" maxlength="100">
                </div>
            </form>
        </div>
        <div class="modal-base__footer">
            <button type="button" class="modal-btn modal-btn-cancel"
                onclick="window.modalManager.close('modal-nuevo-ut')">Cancelar</button>
            <button type="submit" form="formActualizarUT" class="modal-btn modal-btn-primary">Guardar Valor UT</button>
        </div>
    </div>
</dialog>

<!-- Modal: Genérico para Crear/Editar Items de Catálogo -->
<dialog class="modal-base" id="modal-catalogo">
    <div class="modal-base__container" style="max-width: 460px;">
        <div class="modal-base__header">
            <h2 class="modal-base__title" id="catalogo-modal-title">Agregar Registro</h2>
            <button class="modal-base__close" onclick="window.modalManager.close('modal-catalogo')"
                aria-label="Cerrar modal">&times;</button>
        </div>
        <div class="modal-base__body">
            <form id="formCatalogo" action="<?= base_url('/admin/catalogos/guardar') ?>" method="POST"
                style="display: flex; flex-direction: column; gap: 16px;">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="catalogo_item_id" value="">
                <input type="hidden" name="tabla" id="catalogo_tabla" value="">

                <div class="form-group">
                    <label class="form-label" id="catalogo-label-nombre">Nombre</label>
                    <input type="text" name="nombre" id="catalogo_nombre" class="form-input"
                        placeholder="Nombre del registro" maxlength="100" required>
                </div>
                <div class="form-group" id="catalogo-grupo-descripcion">
                    <label class="form-label">Descripción (opcional)</label>
                    <textarea name="descripcion" id="catalogo_descripcion" class="form-input"
                        placeholder="Breve descripción..." rows="3" maxlength="255"></textarea>
                </div>
            </form>
        </div>
        <div class="modal-base__footer">
            <button type="button" class="modal-btn modal-btn-cancel"
                onclick="window.modalManager.close('modal-catalogo')">Cancelar</button>
            <button type="submit" form="formCatalogo" class="modal-btn modal-btn-primary">Guardar</button>
        </div>
    </div>
</dialog>

<!-- Modal: Confirmar Eliminación de Item de Catálogo -->
<dialog class="modal-base" id="modal-eliminar-catalogo">
    <div class="modal-base__container" style="max-width: 440px;">
        <div class="modal-base__header">
            <h2 class="modal-base__title">¿Eliminar este registro?</h2>
            <button class="modal-base__close" onclick="window.modalManager.close('modal-eliminar-catalogo')"
                aria-label="Cerrar modal">&times;</button>
        </div>
        <div class="modal-base__body">
            <p style="font-size: 15px; color: var(--text-body); line-height: 1.5; margin-bottom: 0;">
                Se eliminará el registro <strong id="eliminar-catalogo-nombre"></strong> de forma permanente.
                Si está siendo utilizado por algún caso en curso, <strong>no podrá eliminarse</strong>.
            </p>
            <form id="formEliminarCatalogo" action="<?= base_url('/admin/catalogos/eliminar') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="eliminar_catalogo_id" value="">
                <input type="hidden" name="tabla" id="eliminar_catalogo_tabla" value="">
            </form>
        </div>
        <div class="modal-base__footer" style="padding-top: 24px;">
            <button class="modal-btn modal-btn-cancel" style="min-width: 120px;"
                onclick="window.modalManager.close('modal-eliminar-catalogo')">Cancelar</button>
            <button type="submit" form="formEliminarCatalogo" class="modal-btn modal-btn-danger"
                style="min-width: 120px;">Eliminar</button>
        </div>
    </div>
</dialog>

<script>
    // --- UT Modal ---
    function openCrearUT() {
        document.getElementById('formActualizarUT').reset();
        document.getElementById('ut_id').value = '';
        document.querySelector('#modal-nuevo-ut .modal-base__title').textContent = 'Actualizar Unidad Tributaria';
        window.modalManager.open('modal-nuevo-ut');
    }

    function openEditarUT(btn) {
        document.getElementById('formActualizarUT').reset();
        document.getElementById('ut_id').value = btn.dataset.id || '';
        document.getElementById('ut_valor').value = btn.dataset.valor || '';
        document.getElementById('ut_fecha').value = btn.dataset.fecha || '';
        document.getElementById('ut_resolucion').value = btn.dataset.resolucion || '';
        document.querySelector('#modal-nuevo-ut .modal-base__title').textContent = 'Editar Valor UT';
        window.modalManager.open('modal-nuevo-ut');
    }

    // --- Catálogo Genérico Modal ---
    function openCrearCatalogo(tabla, titulo, labelNombre) {
        document.getElementById('formCatalogo').reset();
        document.getElementById('catalogo_item_id').value = '';
        document.getElementById('catalogo_tabla').value = tabla;
        document.getElementById('catalogo-modal-title').textContent = 'Agregar ' + titulo;
        document.getElementById('catalogo-label-nombre').textContent = labelNombre || 'Nombre';
        window.modalManager.open('modal-catalogo');
    }

    function openEditarCatalogo(btn, titulo, labelNombre) {
        document.getElementById('formCatalogo').reset();
        document.getElementById('catalogo_item_id').value = btn.dataset.id || '';
        document.getElementById('catalogo_tabla').value = btn.dataset.tabla || '';
        document.getElementById('catalogo_nombre').value = btn.dataset.nombre || '';
        document.getElementById('catalogo_descripcion').value = btn.dataset.descripcion || '';
        document.getElementById('catalogo-modal-title').textContent = 'Editar ' + titulo;
        document.getElementById('catalogo-label-nombre').textContent = labelNombre || 'Nombre';
        window.modalManager.open('modal-catalogo');
    }

    function openEliminarCatalogo(id, tabla, nombre) {
        document.getElementById('eliminar_catalogo_id').value = id;
        document.getElementById('eliminar_catalogo_tabla').value = tabla;
        document.getElementById('eliminar-catalogo-nombre').textContent = '"' + nombre + '"';
        window.modalManager.open('modal-eliminar-catalogo');
    }
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/logged_layout.php';
?>