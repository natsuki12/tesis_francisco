<?php
declare(strict_types=1);

$pageTitle = 'Gestión de Profesores';
$activePage = 'profesores';
$breadcrumbs = [
    'Inicio' => base_url('/admin'),
    'Gestión de Usuarios' => '#',
    'Profesores' => '#'
];

$extraCss = '<link rel="stylesheet" href="' . asset('css/shared/data-table.css') . '">';

// Datos inyectados por el controlador
$profesores = $profesores ?? [];

ob_start();
?>
<div class="page-header">
    <div class="page-header-left">
        <h1>Gestión de Profesores</h1>
        <p>Registre y administre los profesores habilitados para supervisar secciones del simulador.</p>
    </div>
    <div style="display:flex; gap:8px; flex-wrap:wrap;">
        <button class="btn btn-primary" onclick="openCrearProfesor()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                <line x1="12" y1="5" x2="12" y2="19" />
                <line x1="5" y1="12" x2="19" y2="12" />
            </svg>
            Registrar Profesor
        </button>
        <button class="btn" style="background:#fff; color:var(--gray-700); padding:10px 16px; font-size:13px; border:1.5px solid var(--gray-300); border-radius:8px; cursor:pointer; display:inline-flex; align-items:center; gap:6px; font-weight:600; box-shadow:0 1px 2px rgba(0,0,0,.06);" onmouseover="this.style.background='var(--gray-50)';this.style.borderColor='var(--gray-400)'" onmouseout="this.style.background='#fff';this.style.borderColor='var(--gray-300)'" onclick="openImportarCSV()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
            <input type="text" data-search-for="tbl-profesores" placeholder="Buscar por nombre, cédula o correo...">
        </div>
        <button class="btn btn-secondary" id="btn-reload-tbl-profesores" data-reload-for="tbl-profesores" onclick="window.DataTableManager.reloadTableData('tbl-profesores');" title="Recargar tabla" style="padding: 10px; border-radius: 8px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="transform-origin: center;">
                <polyline points="23 4 23 10 17 10"></polyline>
                <polyline points="1 20 1 14 7 14"></polyline>
                <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
            </svg>
        </button>
    </div>
    <div class="toolbar-right">
        <label style="font-size:var(--text-xs); color:var(--gray-500); display:flex; align-items:center; gap:6px;">
            Mostrar <select data-perpage-for="tbl-profesores" class="per-page-select"><option value="10" selected>10</option><option value="15">15</option><option value="25">25</option></select> filas
        </label>
    </div>
</div>

<!-- Data Table -->
<div class="table-container">
    <table class="data-table" id="tbl-profesores">
        <thead>
            <tr>
                <th class="sortable" data-col="0">Profesor</th>
                <th class="sortable" data-col="1" style="width:110px">Cédula</th>
                <th class="sortable" data-col="2">Correo</th>
                <th class="sortable" data-col="3" style="width:100px">Título</th>
                <th class="sortable" data-col="4" style="width:90px">Secciones</th>
                <th class="sortable" data-col="5" style="width:90px">Estado</th>
                <th style="width:90px">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($profesores)): ?>
                <?php foreach ($profesores as $prof):
                    $nombres   = $prof['nombres'] ?? '';
                    $apellidos = $prof['apellidos'] ?? '';
                    $fullName  = trim($nombres . ' ' . $apellidos);
                    $cedula    = $prof['cedula'] ?? '';
                    $nac       = $prof['nacionalidad'] ?? 'V';
                    $email     = $prof['email'] ?? '';
                    $titulo    = ucfirst($prof['titulo'] ?? '');
                    $status    = $prof['status'] ?? 'active';
                    $secciones = (int)($prof['total_secciones'] ?? 0);

                    // Initials
                    $initials = mb_strtoupper(mb_substr($nombres, 0, 1) . mb_substr($apellidos, 0, 1));
                    $avatarClass = 'm';

                    // Status label
                    $statusLabel = $status === 'active' ? 'Activo' : ($status === 'inactive' ? 'Inactivo' : 'Bloqueado');
                    $statusClass = $status === 'active' ? 'status-published' : 'status-draft';

                    $searchText = mb_strtolower($fullName . ' ' . $cedula . ' ' . $email . ' ' . $titulo . ' ' . $statusLabel);
                ?>
                    <tr data-search="<?= e($searchText) ?>">
                        <td>
                            <div style="display:flex; align-items:center; gap:10px;">
                                <div class="causante-avatar <?= $avatarClass ?>" style="width:32px;height:32px;font-size:11px;"><?= e($initials) ?></div>
                                <div>
                                    <strong style="display:block;"><?= e($fullName) ?></strong>
                                </div>
                            </div>
                        </td>
                        <td style="font-size:13px;"><?= e($nac) ?>-<?= e($cedula) ?></td>
                        <td style="font-size:13px; color:var(--gray-500);"><?= e($email) ?></td>
                        <td style="font-size:13px;"><?= e($titulo) ?></td>
                        <td style="text-align:center;"><strong><?= $secciones ?></strong></td>
                        <td>
                            <span class="status-badge <?= $statusClass ?>"><?= $statusLabel ?></span>
                        </td>
                        <td>
                            <div class="row-actions">
                                <button class="row-action-btn" title="Ver detalles"
                                    onclick="openEditarProfesor(this)"
                                    data-id="<?= (int)$prof['id'] ?>"
                                    data-user-id="<?= (int)($prof['user_id'] ?? 0) ?>"
                                    data-tipo-cedula="<?= e($nac) ?>"
                                    data-cedula="<?= e($cedula) ?>"
                                    data-nombres="<?= e($nombres) ?>"
                                    data-apellidos="<?= e($apellidos) ?>"
                                    data-email="<?= e($email) ?>"
                                    data-titulo="<?= e($prof['titulo'] ?? '') ?>"
                                    data-estado="<?= $status === 'active' ? '1' : '0' ?>">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                                    </svg>
                                </button>
                                <?php if ($status === 'active'): ?>
                                <button class="row-action-btn" title="Desactivar"
                                    onclick="openToggleProfesor(<?= (int)($prof['user_id'] ?? 0) ?>, '<?= e(addslashes($fullName)) ?>', 'deactivate')" style="color:var(--red-500);">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"/>
                                        <line x1="15" y1="9" x2="9" y2="15"/>
                                        <line x1="9" y1="9" x2="15" y2="15"/>
                                    </svg>
                                </button>
                                <?php else: ?>
                                <button class="row-action-btn" title="Reactivar"
                                    onclick="openToggleProfesor(<?= (int)($prof['user_id'] ?? 0) ?>, '<?= e(addslashes($fullName)) ?>', 'activate')" style="color:var(--green-600);">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"/>
                                        <path d="m9 12 2 2 4-4"/>
                                    </svg>
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    <div class="table-footer" data-footer-for="tbl-profesores">
        <div class="table-footer-info"></div>
        <div class="pagination"></div>
    </div>
</div>

<!-- ==============================================
     MODALES
     ============================================== -->

<!-- Modal: Crear/Editar Profesor -->
<dialog class="modal-base" id="modal-profesor">
    <div class="modal-base__container" style="max-width: 600px;">
        <div class="modal-base__header">
            <h2 class="modal-base__title" id="modal-profesor-title">Registrar Profesor</h2>
            <button class="modal-base__close" onclick="window.modalManager.close('modal-profesor')" aria-label="Cerrar modal">&times;</button>
        </div>
        <div class="modal-base__body">
            <p style="font-size: 15px; color: var(--text-body); margin-bottom: 20px;">Complete los datos del profesor para habilitarlo en el sistema.</p>
            
            <div class="form-grid">
                <input type="hidden" id="profesor_id" value="">

                <div class="form-group">
                    <label>Nombres <span class="required">*</span></label>
                    <input type="text" id="profesor_nombres" placeholder="Ej: César" maxlength="100">
                </div>

                <div class="form-group">
                    <label>Apellidos <span class="required">*</span></label>
                    <input type="text" id="profesor_apellidos" placeholder="Ej: Requena" maxlength="100">
                </div>

                <div class="form-group">
                    <label>Nacionalidad <span class="required">*</span></label>
                    <select id="profesor_nac">
                        <option value="V">Venezolano (V)</option>
                        <option value="E">Extranjero (E)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Cédula <span class="required">*</span></label>
                    <input type="text" id="profesor_cedula" placeholder="Ej: 12345678" maxlength="20">
                </div>

                <div class="form-group">
                    <label>Correo Electrónico <span class="required">*</span></label>
                    <input type="email" id="profesor_email" placeholder="usuario@unimar.edu.ve" maxlength="150">
                </div>

                <div class="form-group">
                    <label>Título <span class="required">*</span></label>
                    <select id="profesor_titulo">
                        <option value="profesor">Profesor</option>
                        <option value="licenciado">Licenciado</option>
                        <option value="ingeniero">Ingeniero</option>
                        <option value="abogado">Abogado</option>
                        <option value="especialista">Especialista</option>
                        <option value="magíster">Magíster</option>
                        <option value="doctor">Doctor</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="modal-base__footer">
            <button class="modal-btn modal-btn-cancel" onclick="window.modalManager.close('modal-profesor')">Cancelar</button>
            <button class="modal-btn modal-btn-primary" id="btn-guardar-profesor" onclick="guardarProfesor()">Guardar Profesor</button>
        </div>
    </div>
</dialog>

<!-- Modal: Confirmar Activar/Desactivar -->
<dialog class="modal-base" id="modal-toggle">
    <div class="modal-base__container" style="max-width: 480px;">
        <div class="modal-base__header">
            <h2 class="modal-base__title" id="toggle-title">¿Desactivar profesor?</h2>
            <button class="modal-base__close" onclick="window.modalManager.close('modal-toggle')" aria-label="Cerrar modal">&times;</button>
        </div>
        <div class="modal-base__body">
            <p style="font-size: 15px; color: var(--text-body); line-height: 1.5; margin-bottom: 0;" id="toggle-body-text">
                Al desactivar a <strong>[Profesor]</strong>, perderá acceso al sistema.
                Las secciones que tenga asignadas permanecerán intactas pero requerirán reasignación.
                Esta acción puede revertirse.
            </p>
        </div>
        <div class="modal-base__footer" style="padding-top: 24px;">
            <button class="modal-btn modal-btn-cancel" style="min-width: 120px;" onclick="window.modalManager.close('modal-toggle')">Cancelar</button>
            <button class="modal-btn modal-btn-danger" id="btn-toggle-confirm" style="min-width: 120px;" onclick="confirmarToggle()">Desactivar</button>
        </div>
    </div>
</dialog>

<!-- Modal: Importar CSV -->
<dialog class="modal-base" id="modal-importar">
    <div class="modal-base__container" style="max-width: 600px;">
        <div class="modal-base__header">
            <h2 class="modal-base__title">Importar Profesores desde CSV</h2>
            <button class="modal-base__close" onclick="closeImportarCSV()" aria-label="Cerrar modal">&times;</button>
        </div>
        <div class="modal-base__body">
            <p style="font-size: 15px; color: var(--text-body); margin-bottom: 20px;">Cargue un archivo .csv con los datos de los profesores a registrar.</p>
            
            <!-- Formato esperado -->
            <div style="background:var(--gray-50); border:1px solid var(--gray-200); border-radius:8px; padding:14px 16px; margin-bottom:16px;">
                <p style="font-size:12px; font-weight:600; color:var(--gray-600); margin:0 0 6px; text-transform:uppercase; letter-spacing:.5px;">Formato esperado (sin cabecera)</p>
                <code style="display:block; font-size:12px; color:var(--gray-700); background:var(--gray-100); padding:8px 10px; border-radius:4px; line-height:1.6;">
email,cédula,nombres,apellidos<br>
profesor@correo.com,V12345678,Juan,Pérez<br>
otro@correo.com,E87654321,María,López
                </code>
                <p style="font-size:11px; color:var(--gray-400); margin:8px 0 0;">La cédula debe iniciar con V o E seguido de 6-10 dígitos. Máximo 2MB.</p>
            </div>

            <!-- Drop zone -->
            <div id="csv-dropzone" style="border:2px dashed var(--gray-300); border-radius:8px; padding:30px; text-align:center; cursor:pointer; transition:all .2s;" onclick="document.getElementById('csv-file-input').click()">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="var(--gray-400)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom:8px;">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <line x1="12" y1="18" x2="12" y2="12"/>
                    <polyline points="9 15 12 12 15 15"/>
                </svg>
                <p id="csv-dropzone-text" style="font-size:13px; color:var(--gray-500); margin:0;">Arrastra un archivo .csv aquí o haz click para seleccionar</p>
                <input type="file" id="csv-file-input" accept=".csv" style="display:none;" onchange="handleCSVFile(this.files[0])">
            </div>

            <!-- Preview -->
            <div id="csv-preview" style="display:none; margin-top:16px; max-height:220px; overflow-y:auto;">
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
                    <tbody id="csv-preview-body"></tbody>
                </table>
                <p id="csv-preview-summary" style="font-size:12px; color:var(--gray-500); margin:8px 0 0; text-align:right;"></p>
            </div>

            <!-- Resultados post-importación -->
            <div id="csv-results" style="display:none; margin-top:16px;"></div>
        </div>
        <div class="modal-base__footer">
            <button class="modal-btn modal-btn-cancel" onclick="closeImportarCSV()">Cancelar</button>
            <button class="modal-btn modal-btn-primary" id="btn-importar-csv" onclick="ejecutarImportacion()" disabled>Importar</button>
        </div>
    </div>
</dialog>

<script>
const CSRF_TOKEN = '<?= \App\Core\Csrf::getToken() ?>';
const BASE_URL   = '<?= base_url() ?>';

// ── Estado global para toggle ──
let toggleState = { userId: 0, action: '' };

// ── Modal helpers ──
function openCrearProfesor() {
    document.getElementById('profesor_id').value = '';
    document.getElementById('profesor_nombres').value = '';
    document.getElementById('profesor_apellidos').value = '';
    document.getElementById('profesor_nac').value = 'V';
    document.getElementById('profesor_cedula').value = '';
    document.getElementById('profesor_email').value = '';
    document.getElementById('profesor_titulo').value = 'profesor';
    document.getElementById('modal-profesor-title').textContent = 'Registrar Profesor';
    
    // Ocultar error suavemente sin resetear de golpe usando el manager global
    window.modalManager.clearError('modal-profesor');
    
    // Habilitar campos para creación
    document.getElementById('profesor_nac').disabled = false;
    document.getElementById('profesor_cedula').disabled = false;
    
    const btnGuardar = document.getElementById('btn-guardar-profesor');
    btnGuardar.style.display = '';
    btnGuardar.textContent = 'Guardar Profesor';
    
    window.modalManager.open('modal-profesor');
}

function openEditarProfesor(btn) {
    document.getElementById('profesor_id').value = btn.dataset.userId || '';
    document.getElementById('profesor_nombres').value = btn.dataset.nombres || '';
    document.getElementById('profesor_apellidos').value = btn.dataset.apellidos || '';
    document.getElementById('profesor_nac').value = btn.dataset.nacionalidad || 'V';
    document.getElementById('profesor_cedula').value = btn.dataset.cedula || '';
    document.getElementById('profesor_email').value = btn.dataset.email || '';
    document.getElementById('profesor_titulo').value = btn.dataset.titulo || 'profesor';
    document.getElementById('modal-profesor-title').textContent = 'Actualizar Profesor';
    
    // Ocultar error suavemente usando el manager global
    window.modalManager.clearError('modal-profesor');
    
    // Habilitar campos de identidad para correcciones (Opción B)
    document.getElementById('profesor_nac').disabled = false;
    document.getElementById('profesor_cedula').disabled = false;
    
    const btnGuardar = document.getElementById('btn-guardar-profesor');
    btnGuardar.style.display = '';
    btnGuardar.textContent = 'Actualizar Profesor';
    
    window.modalManager.open('modal-profesor');
}

function openToggleProfesor(userId, nombre, action) {
    toggleState = { userId, action };
    const isDeactivate = action === 'deactivate';
    
    document.getElementById('toggle-title').textContent = isDeactivate ? '¿Desactivar profesor?' : '¿Reactivar profesor?';
    
    // Almacenamos el nombre en el botón para usarlo fácil luego
    const confirmBtn = document.getElementById('btn-toggle-confirm');
    confirmBtn.dataset.nombre = nombre;
    
    const bodyText = isDeactivate
        ? `Al desactivar a <strong>${nombre}</strong>, perderá acceso al sistema. Las secciones asignadas permanecerán intactas. Esta acción puede revertirse.`
        : `Al reactivar a <strong>${nombre}</strong>, podrá acceder nuevamente al sistema con su cuenta existente.`;
    document.getElementById('toggle-body-text').innerHTML = bodyText;
    
    confirmBtn.textContent = isDeactivate ? 'Desactivar' : 'Reactivar';
    confirmBtn.style.background = isDeactivate ? 'var(--red-500)' : 'var(--green-600)';
    
    window.modalManager.open('modal-toggle');
}

// ── AJAX: Guardar Profesor ──
async function guardarProfesor() {
    const btn = document.getElementById('btn-guardar-profesor');
    const profId = document.getElementById('profesor_id').value;
    const isUpdate = profId !== '';

    // Delegate exact width locking and loading state to the global manager
    window.modalManager.setButtonLoading(btn);

    const body = new URLSearchParams({
        csrf_token:    CSRF_TOKEN,
        nacionalidad:  document.getElementById('profesor_nac').value,
        cedula:        document.getElementById('profesor_cedula').value.trim(),
        nombres:       document.getElementById('profesor_nombres').value.trim(),
        apellidos:     document.getElementById('profesor_apellidos').value.trim(),
        email:         document.getElementById('profesor_email').value.trim(),
        titulo:        document.getElementById('profesor_titulo').value
    });

    if (isUpdate) {
        body.append('user_id', profId);
    }
    
    const url = isUpdate ? BASE_URL + '/admin/profesores/actualizar' : BASE_URL + '/admin/profesores/guardar';

    try {
        const res = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body
        });
        const data = await res.json();

        if (data.success) {
            window.modalManager.close('modal-profesor');
            showToast(data.message, 'success');
            
            if (isUpdate) {
                // Actualización interactiva en caliente (True AJAX sin recargar página)
                const editBtn = document.querySelector(`button[onclick="openEditarProfesor(this)"][data-user-id="${profId}"]`);
                if (editBtn) {
                    const row = editBtn.closest('tr');
                    
                    const newNac = document.getElementById('profesor_nac').value;
                    const newCedula = document.getElementById('profesor_cedula').value.trim();
                    const newNombres = document.getElementById('profesor_nombres').value.trim();
                    const newApellidos = document.getElementById('profesor_apellidos').value.trim();
                    const newEmail = document.getElementById('profesor_email').value.trim();
                    const newTitulo = document.getElementById('profesor_titulo').value;
                    
                    // 1. Actualizar el Dataset del botón para futuras ediciones
                    editBtn.dataset.nombres = newNombres;
                    editBtn.dataset.apellidos = newApellidos;
                    editBtn.dataset.nacionalidad = newNac;
                    editBtn.dataset.cedula = newCedula;
                    editBtn.dataset.email = newEmail;
                    editBtn.dataset.titulo = newTitulo;

                    // 2. Transmutar el DOM visual a la nueva información
                    if (row) {
                        const avatar = row.querySelector('.causante-avatar');
                        if (avatar) avatar.textContent = (newNombres.charAt(0) + newApellidos.charAt(0)).toUpperCase();
                        
                        const nameEl = row.querySelector('strong');
                        if (nameEl) nameEl.textContent = newNombres + ' ' + newApellidos;
                        
                        const tds = row.querySelectorAll('td');
                        if (tds.length >= 4) {
                            tds[1].textContent = newNac + '-' + newCedula;
                            tds[2].textContent = newEmail;
                            tds[3].textContent = newTitulo.charAt(0).toUpperCase() + newTitulo.slice(1);
                        }
                    }
                }
            } else {
                // Si es un profesor nuevo, recargamos silenciosamente usando DOM Morphing Global
                window.DataTableManager.reloadTableData('tbl-profesores');
            }
        } else {
            // Mostrar error usando el manager global (se encarga del shake y el scroll)
            window.modalManager.showError('modal-profesor', data.message || 'Ocurrió un error al procesar la solicitud.');
        }
    } catch (err) {
        console.error(err);
        window.modalManager.showError('modal-profesor', 'No se pudo conectar con el servidor.');
    } finally {
        window.modalManager.resetButtonLoading(btn);
    }
}

// ── AJAX: Toggle estado ──
async function confirmarToggle() {
    const btn = document.getElementById('btn-toggle-confirm');
    btn.disabled = true;

    const body = new URLSearchParams({
        csrf_token: CSRF_TOKEN,
        user_id:    toggleState.userId,
        action:     toggleState.action
    });

    try {
        const res = await fetch(BASE_URL + '/admin/profesores/eliminar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body
        });
        const data = await res.json();

        window.modalManager.close('modal-toggle');
        if (data.success) {
            window.showToast(data.message, 'success');
            
            // Seamless AJAX DOM update instead of reloading
            const isDeactivate = toggleState.action === 'deactivate';
            const newAction = isDeactivate ? 'activate' : 'deactivate';
            const newStatus = isDeactivate ? '0' : '1';
            
            // Find the edit button to locate the row (it has data-user-id)
            const editBtn = document.querySelector(`button[onclick="openEditarProfesor(this)"][data-user-id="${toggleState.userId}"]`);
            if (editBtn) {
                editBtn.dataset.estado = newStatus;
                
                const row = editBtn.closest('tr');
                if (row) {
                    // 1. Update status badge
                    const badge = row.querySelector('.status-badge');
                    if (badge) {
                        badge.textContent = isDeactivate ? 'Inactivo' : 'Activo';
                        badge.className = 'status-badge ' + (isDeactivate ? 'status-draft' : 'status-published');
                    }
                    
                    // 2. Update the toggle button itself
                    const toggleBtn = Array.from(row.querySelectorAll('.row-action-btn')).find(b => 
                        b.getAttribute('onclick')?.includes('openToggleProfesor')
                    );
                    if (toggleBtn) {
                        const nombre = btn.dataset.nombre || '';
                        // Escape quotes for the JS call
                        const escapedNombre = nombre.replace(/'/g, "\\'");
                        
                        toggleBtn.setAttribute('onclick', `openToggleProfesor(${toggleState.userId}, '${escapedNombre}', '${newAction}')`);
                        toggleBtn.title = isDeactivate ? 'Reactivar' : 'Desactivar';
                        toggleBtn.style.color = isDeactivate ? 'var(--green-600)' : 'var(--red-500)';
                        
                        // Icon Update
                        toggleBtn.innerHTML = isDeactivate 
                            ? `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>`
                            : `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>`;
                    }
                    
                    // Update search payload for datatable
                    if (row.dataset.search) {
                        row.dataset.search = row.dataset.search.replace(isDeactivate ? 'activo' : 'inactivo', isDeactivate ? 'inactivo' : 'activo');
                    }
                }
            }
            // Trigger visual morphing map refresh
            window.DataTableManager.updateData('tbl-profesores', document.querySelector('#tbl-profesores tbody').innerHTML);
            
        } else {
            window.showToast(data.message || 'Error al cambiar estado.', 'error');
        }
    } catch (err) {
        console.error(err);
        window.showToast('Error de conexión.', 'error');
    } finally {
        btn.disabled = false;
    }
}

// ── CSV Import ──
let csvSelectedFile = null;

function openImportarCSV() {
    csvSelectedFile = null;
    document.getElementById('csv-file-input').value = '';
    document.getElementById('csv-dropzone-text').textContent = 'Arrastra un archivo .csv aquí o haz click para seleccionar';
    document.getElementById('csv-dropzone').style.borderColor = 'var(--gray-300)';
    document.getElementById('csv-preview').style.display = 'none';
    document.getElementById('csv-results').style.display = 'none';
    document.getElementById('btn-importar-csv').disabled = true;
    window.modalManager.open('modal-importar');
}

function closeImportarCSV() {
    window.modalManager.close('modal-importar');
}

// Drag & drop
(function() {
    const dz = document.getElementById('csv-dropzone');
    if (!dz) return;
    ['dragenter','dragover'].forEach(ev => dz.addEventListener(ev, e => { e.preventDefault(); dz.style.borderColor = 'var(--primary)'; }));
    ['dragleave','drop'].forEach(ev => dz.addEventListener(ev, e => { e.preventDefault(); dz.style.borderColor = 'var(--gray-300)'; }));
    dz.addEventListener('drop', e => {
        const file = e.dataTransfer.files[0];
        if (file) handleCSVFile(file);
    });
})();

function handleCSVFile(file) {
    if (!file) return;
    const ext = file.name.split('.').pop().toLowerCase();
    if (ext !== 'csv') { window.showToast('Solo se permiten archivos .csv', 'error'); return; }
    if (file.size > 2 * 1024 * 1024) { window.showToast('El archivo excede 2MB.', 'error'); return; }

    csvSelectedFile = file;
    document.getElementById('csv-dropzone-text').textContent = '📄 ' + file.name;
    document.getElementById('csv-dropzone').style.borderColor = 'var(--green-600)';
    document.getElementById('csv-results').style.display = 'none';

    // Parsear preview
    const reader = new FileReader();
    reader.onload = function(e) {
        const lines = e.target.result.split(/\r?\n/).filter(l => l.trim() !== '');
        const tbody = document.getElementById('csv-preview-body');
        tbody.innerHTML = '';
        let valid = 0, invalid = 0;
        const delimiter = (lines[0] && lines[0].split(';').length > lines[0].split(',').length) ? ';' : ',';

        lines.forEach((line, i) => {
            const cols = line.split(delimiter).map(c => c.trim());
            const tr = document.createElement('tr');
            const num = i + 1;

            // Client-side validation (MVC espejo)
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

        document.getElementById('csv-preview-summary').textContent = `${valid} válida(s), ${invalid} con error(es) — Total: ${lines.length} fila(s)`;
        document.getElementById('csv-preview').style.display = 'block';
        document.getElementById('btn-importar-csv').disabled = valid === 0;
    };
    reader.readAsText(file, 'UTF-8');
}

async function ejecutarImportacion() {
    if (!csvSelectedFile) return;

    const btn = document.getElementById('btn-importar-csv');
    btn.disabled = true;
    btn.textContent = 'Importando...';

    const formData = new FormData();
    formData.append('csrf_token', CSRF_TOKEN);
    formData.append('csv', csvSelectedFile);

    try {
        const res = await fetch(BASE_URL + '/admin/profesores/importar', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();

        const resultsDiv = document.getElementById('csv-results');
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
                setTimeout(() => location.reload(), 2500);
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

// ── Close modals on click outside / Escape ──
['modal-profesor', 'modal-toggle', 'modal-importar'].forEach(id => {
    document.getElementById(id)?.addEventListener('click', function(e) {
        if (e.target === this) this.classList.remove('show');
    });
});
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        document.getElementById('modal-profesor')?.classList.remove('show');
        document.getElementById('modal-toggle')?.classList.remove('show');
        document.getElementById('modal-importar')?.classList.remove('show');
    }
});

</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/logged_layout.php';
?>