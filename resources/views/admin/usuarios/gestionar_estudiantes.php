<?php
declare(strict_types=1);

$pageTitle = 'Gestión de Estudiantes';
$activePage = 'estudiantes';
$breadcrumbs = [
    'Inicio' => base_url('/admin'),
    'Gestión de Usuarios' => '#',
    'Estudiantes' => '#'
];

$extraCss = '<link rel="stylesheet" href="' . asset('css/shared/data-table.css') . '">';

// Datos inyectados por el controlador
$conteo = $conteo ?? ['total' => 0, 'activos' => 0, 'inactivos' => 0];

ob_start();
?>
<div class="page-header">
    <div class="page-header-left">
        <h1>Estudiantes Registrados</h1>
        <p>Visualice y gestione las cuentas de los estudiantes inscritos en el Simulador SENIAT.</p>
    </div>
    <div style="display:flex; gap:8px; flex-wrap:wrap;">
        <button class="btn btn-primary" onclick="openCrearEstudiante()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                <line x1="12" y1="5" x2="12" y2="19" />
                <line x1="5" y1="12" x2="19" y2="12" />
            </svg>
            Registrar Estudiante
        </button>
        <button class="btn" style="background:#fff; color:var(--gray-700); padding:10px 16px; font-size:13px; border:1.5px solid var(--gray-300); border-radius:8px; cursor:pointer; display:inline-flex; align-items:center; gap:6px; font-weight:600; box-shadow:0 1px 2px rgba(0,0,0,.06);" onmouseover="this.style.background='var(--gray-50)';this.style.borderColor='var(--gray-400)'" onmouseout="this.style.background='#fff';this.style.borderColor='var(--gray-300)'" onclick="openImportarCSVEst()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
                <line x1="12" y1="18" x2="12" y2="12"/>
                <polyline points="9 15 12 12 15 15"/>
            </svg>
            Importar CSV
        </button>
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
            <input type="text" data-search-for="tbl-estudiantes" placeholder="Buscar por nombre, cédula o correo...">
        </div>
        <button class="btn btn-secondary" data-reload-for="tbl-estudiantes" onclick="window.DataTableManager.reloadTableData('tbl-estudiantes');" title="Recargar tabla" style="padding: 10px; border-radius: 8px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="transform-origin: center;">
                <polyline points="23 4 23 10 17 10"></polyline>
                <polyline points="1 20 1 14 7 14"></polyline>
                <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
            </svg>
        </button>

        <button class="filter-chip active" data-filter="" data-filter-key="status">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3" />
            </svg>
            Todos <span class="filter-count" id="count-total"><?= $conteo['total'] ?></span>
        </button>
        <button class="filter-chip" data-filter="active" data-filter-key="status">Activos <span class="filter-count" id="count-activos"><?= $conteo['activos'] ?></span></button>
        <button class="filter-chip" data-filter="inactive" data-filter-key="status">Inactivos <span class="filter-count" id="count-inactivos"><?= $conteo['inactivos'] ?></span></button>
    </div>

    <div class="toolbar-right">
        <label style="font-size:var(--text-xs); color:var(--gray-500); display:flex; align-items:center; gap:6px;">
            Mostrar <select data-perpage-for="tbl-estudiantes" class="per-page-select"><option value="10">10</option><option value="15" selected>15</option><option value="25">25</option><option value="50">50</option></select> filas
        </label>
    </div>
</div>

<!-- Data Table (Server-Side) -->
<div class="table-container">
    <table class="data-table" id="tbl-estudiantes"
           data-server-url="<?= base_url('/admin/estudiantes/api') ?>"
           data-render="renderEstudianteRow">
        <thead>
            <tr>
                <th class="sortable" data-sort-key="nombres">Nombre</th>
                <th class="sortable" data-sort-key="apellidos">Apellido</th>
                <th>Correo</th>
                <th class="sortable" data-sort-key="cedula">Cédula</th>
                <th>Carrera</th>
                <th>Período</th>
                <th class="sortable" data-sort-key="created_at">Fecha de Registro</th>
                <th class="sortable" data-sort-key="status">Estado</th>
                <th style="width:90px">Acciones</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>

    <!-- Table Footer -->
    <div class="table-footer" data-footer-for="tbl-estudiantes">
        <div class="table-footer-info">
            Mostrando <strong>0</strong> estudiantes
        </div>
        <div class="pagination"></div>
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

<!-- Modal: Registrar Estudiante -->
<dialog class="modal-base" id="modal-estudiante">
    <div class="modal-base__container" style="max-width: 600px;">
        <div class="modal-base__header">
            <h2 class="modal-base__title">Registrar Estudiante</h2>
            <button class="modal-base__close" onclick="window.modalManager.close('modal-estudiante')" aria-label="Cerrar modal">&times;</button>
        </div>
        <div class="modal-base__body">
            <p style="font-size: 15px; color: var(--text-body); margin-bottom: 20px;">Complete los datos del estudiante para habilitarlo en el sistema.</p>

            <div class="form-grid">
                <div class="form-group">
                    <label>Nombres <span class="required">*</span></label>
                    <input type="text" id="est_nombres" placeholder="Ej: María" maxlength="100">
                </div>

                <div class="form-group">
                    <label>Apellidos <span class="required">*</span></label>
                    <input type="text" id="est_apellidos" placeholder="Ej: González" maxlength="100">
                </div>

                <div class="form-group">
                    <label>Nacionalidad <span class="required">*</span></label>
                    <select id="est_nac">
                        <option value="V">Venezolano (V)</option>
                        <option value="E">Extranjero (E)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Cédula <span class="required">*</span></label>
                    <input type="text" id="est_cedula" placeholder="Ej: 31860250" maxlength="20">
                </div>

                <div class="form-group form-full">
                    <label>Correo Electrónico <span class="required">*</span></label>
                    <input type="email" id="est_email" placeholder="usuario@correo.com" maxlength="150">
                </div>

                <!-- Toggle: Asignar sección -->
                <div class="form-group form-full" style="margin-top: 6px;">
                    <label>Asignación de Sección</label>
                    <div style="display:flex; gap:12px; margin-top:4px;">
                        <label style="display:flex; align-items:center; gap:6px; font-size:14px; font-weight:500; color:var(--gray-600); cursor:pointer;">
                            <input type="radio" name="seccion_mode" value="sin" checked onchange="toggleSeccionDropdown(false)"> Sin sección
                        </label>
                        <label style="display:flex; align-items:center; gap:6px; font-size:14px; font-weight:500; color:var(--gray-600); cursor:pointer;">
                            <input type="radio" name="seccion_mode" value="con" onchange="toggleSeccionDropdown(true)"> Con sección
                        </label>
                    </div>
                </div>

                <div class="form-group form-full" id="seccion-dropdown-wrapper" style="display:none; opacity:0; transition: opacity 0.2s ease;">
                    <label>Sección <span class="required">*</span></label>
                    <select id="est_seccion">
                        <option value="">Seleccione una sección...</option>
                        <?php foreach (($secciones ?? []) as $sec): ?>
                        <option value="<?= (int)$sec['id'] ?>">
                            <?= htmlspecialchars($sec['nombre']) ?> · <?= htmlspecialchars($sec['periodo']) ?> · Prof. <?= htmlspecialchars(explode(' ', $sec['profesor'])[0]) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="modal-base__footer">
            <button class="modal-btn modal-btn-cancel" onclick="window.modalManager.close('modal-estudiante')">Cancelar</button>
            <button class="modal-btn modal-btn-primary" id="btn-guardar-estudiante" onclick="guardarEstudiante()">Guardar Estudiante</button>
        </div>
    </div>
</dialog>

<!-- Modal: Importar CSV Estudiantes -->
<dialog class="modal-base" id="modal-importar-est">
    <div class="modal-base__container" style="max-width: 600px;">
        <div class="modal-base__header">
            <h2 class="modal-base__title">Importar Estudiantes desde CSV</h2>
            <button class="modal-base__close" onclick="closeImportarCSVEst()" aria-label="Cerrar modal">&times;</button>
        </div>
        <div class="modal-base__body">
            <p style="font-size: 15px; color: var(--text-body); margin-bottom: 16px;">Cargue un archivo .csv con los datos de los estudiantes a registrar.</p>

            <!-- Toggle: Asignar sección (global para todos los del CSV) -->
            <div style="background:var(--gray-50); border:1px solid var(--gray-200); border-radius:8px; padding:14px 16px; margin-bottom:16px;">
                <label style="font-size:13px; font-weight:600; color:var(--gray-600);">Asignación de Sección</label>
                <div style="display:flex; gap:12px; margin-top:6px;">
                    <label style="display:flex; align-items:center; gap:6px; font-size:14px; font-weight:500; color:var(--gray-600); cursor:pointer;">
                        <input type="radio" name="csv_seccion_mode" value="sin" checked onchange="toggleCSVSeccion(false)"> Sin sección
                    </label>
                    <label style="display:flex; align-items:center; gap:6px; font-size:14px; font-weight:500; color:var(--gray-600); cursor:pointer;">
                        <input type="radio" name="csv_seccion_mode" value="con" onchange="toggleCSVSeccion(true)"> Con sección
                    </label>
                </div>
                <div id="csv-seccion-dropdown-wrapper" style="display:none; opacity:0; transition:opacity 0.2s ease; margin-top:10px;">
                    <div class="form-group" style="margin-bottom:0;">
                        <select id="csv_est_seccion" style="width:100%;">
                            <option value="">Seleccione una sección...</option>
                            <?php foreach (($secciones ?? []) as $sec): ?>
                            <option value="<?= (int)$sec['id'] ?>">
                                <?= htmlspecialchars($sec['nombre']) ?> · <?= htmlspecialchars($sec['periodo']) ?> · Prof. <?= htmlspecialchars(explode(' ', $sec['profesor'])[0]) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <p style="font-size:11px; color:var(--gray-400); margin:6px 0 0;">Todos los estudiantes importados se inscribirán en esta sección.</p>
                </div>
            </div>

            <div style="background:var(--gray-50); border:1px solid var(--gray-200); border-radius:8px; padding:14px 16px; margin-bottom:16px;">
                <p style="font-size:12px; font-weight:600; color:var(--gray-600); margin:0 0 6px; text-transform:uppercase; letter-spacing:.5px;">Formato esperado (sin cabecera)</p>
                <code style="display:block; font-size:12px; color:var(--gray-700); background:var(--gray-100); padding:8px 10px; border-radius:4px; line-height:1.6;">
email,cédula,nombres,apellidos<br>
estudiante@correo.com,V31860250,María,González<br>
otro@correo.com,E87654321,Carlos,López
                </code>
                <p style="font-size:11px; color:var(--gray-400); margin:8px 0 0;">La cédula debe iniciar con V o E seguido de 6-10 dígitos. Máximo 2MB.</p>
            </div>

            <div id="csv-dropzone-est" style="border:2px dashed var(--gray-300); border-radius:8px; padding:30px; text-align:center; cursor:pointer; transition:all .2s;" onclick="document.getElementById('csv-file-input-est').click()">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="var(--gray-400)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom:8px;">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <line x1="12" y1="18" x2="12" y2="12"/>
                    <polyline points="9 15 12 12 15 15"/>
                </svg>
                <p id="csv-dropzone-text-est" style="font-size:13px; color:var(--gray-500); margin:0;">Arrastra un archivo .csv aquí o haz click para seleccionar</p>
                <input type="file" id="csv-file-input-est" accept=".csv" style="display:none;" onchange="handleCSVFileEst(this.files[0])">
            </div>

            <div id="csv-preview-est" style="display:none; margin-top:16px; max-height:220px; overflow-y:auto;">
                <table class="data-table" style="font-size:12px;">
                    <thead>
                        <tr>
                            <th style="width:30px">#</th>
                            <th>Email</th>
                            <th>Cédula</th>
                            <th>Nombres</th>
                            <th>Apellidos</th>
                            <th style="width:50px">Estado</th>
                        </tr>
                    </thead>
                    <tbody id="csv-preview-body-est"></tbody>
                </table>
                <p id="csv-preview-summary-est" style="font-size:12px; color:var(--gray-500); margin:8px 0 0; text-align:right;"></p>
            </div>

            <div id="csv-results-est" style="display:none; margin-top:16px;"></div>
        </div>
        <div class="modal-base__footer">
            <button class="modal-btn modal-btn-cancel" onclick="closeImportarCSVEst()">Cancelar</button>
            <button class="modal-btn modal-btn-primary" id="btn-importar-csv-est" onclick="ejecutarImportacionEst()" disabled>Importar</button>
        </div>
    </div>
</dialog>

<script>
// ── Helpers ──
function capitalize(s) { return s.charAt(0).toUpperCase() + s.slice(1); }
function escHtml(s) { const d = document.createElement('div'); d.textContent = s; return d.innerHTML; }

const CSRF_TOKEN = '<?= \App\Core\Csrf::getToken() ?>';
const BASE_URL   = '<?= base_url() ?>';

// ── Modal: Crear Estudiante ──
function openCrearEstudiante() {
    document.getElementById('est_nombres').value = '';
    document.getElementById('est_apellidos').value = '';
    document.getElementById('est_nac').value = 'V';
    document.getElementById('est_cedula').value = '';
    document.getElementById('est_email').value = '';
    // Reset sección toggle
    document.querySelector('input[name="seccion_mode"][value="sin"]').checked = true;
    toggleSeccionDropdown(false);
    window.modalManager.clearError('modal-estudiante');
    window.modalManager.open('modal-estudiante');
}

function toggleSeccionDropdown(show) {
    const wrapper = document.getElementById('seccion-dropdown-wrapper');
    if (show) {
        wrapper.style.display = 'block';
        requestAnimationFrame(() => { wrapper.style.opacity = '1'; });
    } else {
        wrapper.style.opacity = '0';
        setTimeout(() => { wrapper.style.display = 'none'; }, 200);
        document.getElementById('est_seccion').value = '';
    }
}

async function guardarEstudiante() {
    const btn = document.getElementById('btn-guardar-estudiante');

    // Validar sección si "Con sección" está activo
    const conSeccion = document.querySelector('input[name="seccion_mode"][value="con"]').checked;
    if (conSeccion && !document.getElementById('est_seccion').value) {
        window.modalManager.showError('modal-estudiante', 'Debe seleccionar una sección o cambiar a "Sin sección".');
        return;
    }

    window.modalManager.setButtonLoading(btn);

    const body = new URLSearchParams({
        csrf_token:    CSRF_TOKEN,
        nacionalidad:  document.getElementById('est_nac').value,
        cedula:        document.getElementById('est_cedula').value.trim(),
        nombres:       document.getElementById('est_nombres').value.trim(),
        apellidos:     document.getElementById('est_apellidos').value.trim(),
        email:         document.getElementById('est_email').value.trim(),
        seccion_id:    document.getElementById('est_seccion').value || '0',
    });

    try {
        const res = await fetch(BASE_URL + '/admin/estudiantes/guardar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body
        });
        const data = await res.json();

        if (data.success) {
            window.modalManager.close('modal-estudiante');
            showToast(data.message, 'success');
            window.DataTableManager.reloadTableData('tbl-estudiantes');
        } else {
            window.modalManager.showError('modal-estudiante', data.message || 'Error al procesar la solicitud.');
        }
    } catch (err) {
        console.error(err);
        window.modalManager.showError('modal-estudiante', 'No se pudo conectar con el servidor.');
    } finally {
        window.modalManager.resetButtonLoading(btn);
    }
}

// ── Custom row renderer for estudiantes DataTable (server-side) ──
window.renderEstudianteRow = function(row, index, data) {
    const nombres = escHtml(row.nombres || '');
    const apellidos = escHtml(row.apellidos || '');
    const email = escHtml(row.email || '');
    const nac = escHtml(row.nacionalidad || 'V');
    const cedula = escHtml(row.cedula || '');
    const carrera = escHtml(row.carrera || '—');
    const periodo = escHtml(row.periodo || '—');
    const status = row.status || 'active';
    const userId = parseInt(row.user_id) || 0;

    const esActivo = status === 'active';
    const estadoLabel = esActivo ? 'Activo' : 'Inactivo';
    const estadoBadge = esActivo ? 'status-published' : 'status-draft';

    // Fecha formateada
    let fechaHtml = '';
    if (row.created_at) {
        try {
            const d = new Date(row.created_at);
            const meses = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
            fechaHtml = d.getDate() + ' ' + meses[d.getMonth()] + ' ' + d.getFullYear();
            const now = new Date();
            const diffDays = Math.floor((now - d) / 86400000);
            let rel = '';
            if (diffDays === 0) rel = 'Hoy';
            else if (diffDays === 1) rel = 'Ayer';
            else if (diffDays < 7) rel = 'Hace ' + diffDays + ' días';
            else if (diffDays < 30) { const s = Math.floor(diffDays / 7); rel = 'Hace ' + s + ' semana' + (s > 1 ? 's' : ''); }
            else { const m = Math.floor(diffDays / 30); rel = 'Hace ' + m + ' mes' + (m > 1 ? 'es' : ''); }
            if (rel) fechaHtml += '<br><span class="date-relative">' + rel + '</span>';
        } catch (e) { fechaHtml = escHtml(row.created_at); }
    }

    // Botón de acción toggle
    const fullName = nombres + ' ' + apellidos;
    const escapedName = fullName.replace(/'/g, "\\'");
    let toggleBtn = '';
    if (esActivo) {
        toggleBtn = `<button class="row-action-btn btn-inactivar-caso" title="Desactivar"
            onclick="openDesactivarEstudiante(${userId})">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                <path d="M18.36 6.64a9 9 0 1 1-12.73 0" /><line x1="12" y1="2" x2="12" y2="12" />
            </svg></button>`;
    } else {
        toggleBtn = `<button class="row-action-btn btn-reactivar-caso" title="Reactivar"
            onclick="openReactivarEstudiante(${userId})">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                <polyline points="23 4 23 10 17 10" /><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10" />
            </svg></button>`;
    }

    return `<tr>
        <td>${nombres}</td>
        <td>${apellidos}</td>
        <td style="font-size: 13px; color: var(--gray-500);">${email}</td>
        <td class="case-id" style="font-size: 14px;">${nac}-${cedula}</td>
        <td>${carrera}</td>
        <td>${periodo}</td>
        <td class="date-cell">${fechaHtml}</td>
        <td><span class="status-badge ${estadoBadge}">${estadoLabel}</span></td>
        <td>
            <div class="row-actions">
                <button class="row-action-btn" title="Restablecer Contraseña"
                    onclick="openResetPassword(${userId}, '${escapedName}')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                        <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                    </svg>
                </button>
                ${toggleBtn}
            </div>
        </td>
    </tr>`;
};

// ── Filter Chips (server-side — envía parámetro status al API) ──
(function() {
    const filterChips = document.querySelectorAll('.filter-chip[data-filter-key="status"]');
    filterChips.forEach(chip => {
        chip.addEventListener('click', () => {
            filterChips.forEach(c => c.classList.remove('active'));
            chip.classList.add('active');
            // El DataTableManager lee data-filter-key y data-filter para enviar como query param
            if (window.DataTableManager) {
                window.DataTableManager.setFilter('tbl-estudiantes', 'status', chip.dataset.filter);
            }
        });
    });
})();

// ── Actualizar conteos después de cada carga de datos ──
document.getElementById('tbl-estudiantes')?.addEventListener('datatable:loaded', function(e) {
    const data = e.detail;
    if (data && data.conteo) {
        document.getElementById('count-total').textContent = data.conteo.total ?? 0;
        document.getElementById('count-activos').textContent = data.conteo.activos ?? 0;
        document.getElementById('count-inactivos').textContent = data.conteo.inactivos ?? 0;
    }
});

function openDesactivarEstudiante(id) {
    document.getElementById('desactivar_estudiante_id').value = id;
    window.modalManager.open('modal-eliminar');
}

function openReactivarEstudiante(id) {
    if (!confirm('¿Desea reactivar a este estudiante?')) return;
    fetch('<?= base_url('/api/admin/estudiantes/') ?>' + id + '/reactivar', {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            window.DataTableManager.reloadTableData('tbl-estudiantes');
        } else {
            if (window.showToast) window.showToast(data.message || 'Error al reactivar.', 'error');
        }
    })
    .catch(() => {
        if (window.showToast) window.showToast('Error de conexión.', 'error');
    });
}

function openResetPassword(id, nombre) {
    document.getElementById('reset_estudiante_id').value = id;
    document.getElementById('reset-student-name').textContent = nombre;
    window.modalManager.open('modal-reset-password');
}

// ── CSV Import Estudiantes ──
let csvSelectedFileEst = null;

function openImportarCSVEst() {
    csvSelectedFileEst = null;
    document.getElementById('csv-file-input-est').value = '';
    document.getElementById('csv-dropzone-text-est').textContent = 'Arrastra un archivo .csv aquí o haz click para seleccionar';
    document.getElementById('csv-dropzone-est').style.borderColor = 'var(--gray-300)';
    document.getElementById('csv-preview-est').style.display = 'none';
    document.getElementById('csv-results-est').style.display = 'none';
    document.getElementById('btn-importar-csv-est').disabled = true;
    // Reset sección toggle
    document.querySelector('input[name="csv_seccion_mode"][value="sin"]').checked = true;
    toggleCSVSeccion(false);
    window.modalManager.open('modal-importar-est');
}

function closeImportarCSVEst() {
    window.modalManager.close('modal-importar-est');
}

function toggleCSVSeccion(show) {
    const wrapper = document.getElementById('csv-seccion-dropdown-wrapper');
    if (show) {
        wrapper.style.display = 'block';
        requestAnimationFrame(() => { wrapper.style.opacity = '1'; });
    } else {
        wrapper.style.opacity = '0';
        setTimeout(() => { wrapper.style.display = 'none'; }, 200);
        document.getElementById('csv_est_seccion').value = '';
    }
}

// Drag & drop
(function() {
    const dz = document.getElementById('csv-dropzone-est');
    if (!dz) return;
    ['dragenter','dragover'].forEach(ev => dz.addEventListener(ev, e => { e.preventDefault(); dz.style.borderColor = 'var(--primary)'; }));
    ['dragleave','drop'].forEach(ev => dz.addEventListener(ev, e => { e.preventDefault(); dz.style.borderColor = 'var(--gray-300)'; }));
    dz.addEventListener('drop', e => {
        const file = e.dataTransfer.files[0];
        if (file) handleCSVFileEst(file);
    });
})();

function handleCSVFileEst(file) {
    if (!file) return;
    const ext = file.name.split('.').pop().toLowerCase();
    if (ext !== 'csv') { window.showToast('Solo se permiten archivos .csv', 'error'); return; }
    if (file.size > 2 * 1024 * 1024) { window.showToast('El archivo excede 2MB.', 'error'); return; }

    csvSelectedFileEst = file;
    document.getElementById('csv-dropzone-text-est').textContent = '📄 ' + file.name;
    document.getElementById('csv-dropzone-est').style.borderColor = 'var(--green-600)';
    document.getElementById('csv-results-est').style.display = 'none';

    const reader = new FileReader();
    reader.onload = function(e) {
        const lines = e.target.result.split(/\r?\n/).filter(l => l.trim() !== '');
        const tbody = document.getElementById('csv-preview-body-est');
        tbody.innerHTML = '';
        let valid = 0, invalid = 0;
        const delimiter = (lines[0] && lines[0].split(';').length > lines[0].split(',').length) ? ';' : ',';

        lines.forEach((line, i) => {
            const cols = line.split(delimiter).map(c => c.trim());
            const tr = document.createElement('tr');
            const num = i + 1;

            const errors = [];
            if (cols.length !== 4) errors.push('debe tener 4 columnas');
            else {
                if (!/^[^@]+@[^@]+\.[^@]+$/.test(cols[0])) errors.push('email');
                if (!/^[VvEe]\d{6,10}$/.test(cols[1])) errors.push('cédula');
                if (!cols[2] || cols[2].length < 2) errors.push('nombres');
                if (!cols[3] || cols[3].length < 2) errors.push('apellidos');
            }

            const ok = errors.length === 0;
            if (ok) valid++; else invalid++;

            tr.innerHTML = `
                <td>${num}</td>
                <td>${cols[0] || ''}</td>
                <td>${cols[1] || ''}</td>
                <td>${cols[2] || ''}</td>
                <td>${cols[3] || ''}</td>
                <td><span style="color:${ok ? 'var(--green-600)' : 'var(--red-500)'}; font-size:14px;" title="${ok ? 'OK' : errors.join(', ')}">${ok ? '✔' : '✘'}</span></td>
            `;
            tbody.appendChild(tr);
        });

        document.getElementById('csv-preview-summary-est').textContent = `${valid} válida(s), ${invalid} con error(es) — Total: ${lines.length} fila(s)`;
        document.getElementById('csv-preview-est').style.display = 'block';
        document.getElementById('btn-importar-csv-est').disabled = valid === 0;
    };
    reader.readAsText(file, 'UTF-8');
}

async function ejecutarImportacionEst() {
    if (!csvSelectedFileEst) return;

    // Validar sección si "Con sección" está activo
    const conSeccion = document.querySelector('input[name="csv_seccion_mode"][value="con"]').checked;
    const seccionId = document.getElementById('csv_est_seccion').value;
    if (conSeccion && !seccionId) {
        window.modalManager.showError('modal-importar-est', 'Debe seleccionar una sección o cambiar a "Sin sección".');
        return;
    }

    const btn = document.getElementById('btn-importar-csv-est');
    btn.disabled = true;
    btn.textContent = 'Importando...';

    const formData = new FormData();
    formData.append('csrf_token', CSRF_TOKEN);
    formData.append('csv', csvSelectedFileEst);
    formData.append('seccion_id', conSeccion ? seccionId : '0');

    try {
        const res = await fetch(BASE_URL + '/admin/estudiantes/importar', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();

        const resultsDiv = document.getElementById('csv-results-est');
        resultsDiv.style.display = 'block';

        if (data.success) {
            let html = `<div style="background:#e8f5e9; border-left:4px solid #4caf50; padding:12px 16px; border-radius:0 6px 6px 0; margin-bottom:8px;">
                <strong>✔ ${data.message}</strong></div>`;

            if (data.errors && data.errors.length > 0) {
                html += `<div style="background:#fff3e0; border-left:4px solid #ff9800; padding:12px 16px; border-radius:0 6px 6px 0; font-size:12px; max-height:150px; overflow-y:auto;">
                    <strong>Detalles de filas omitidas:</strong><ul style="margin:6px 0 0; padding-left:18px;">`;
                data.errors.forEach(err => html += `<li>${err}</li>`);
                html += '</ul></div>';
            }

            resultsDiv.innerHTML = html;

            if (data.created > 0) {
                setTimeout(() => {
                    closeImportarCSVEst();
                    window.DataTableManager.reloadTableData('tbl-estudiantes');
                }, 2000);
            }
        } else {
            resultsDiv.innerHTML = `<div style="background:#ffebee; border-left:4px solid #f44336; padding:12px 16px; border-radius:0 6px 6px 0;">
                <strong>✘ ${data.message}</strong></div>`;
        }
    } catch (err) {
        console.error(err);
        window.showToast('Error de conexión al importar.', 'error');
    } finally {
        btn.disabled = false;
        btn.textContent = 'Importar';
    }
}
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/logged_layout.php';
?>