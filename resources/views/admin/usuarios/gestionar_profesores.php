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
    <button class="btn btn-primary" onclick="openCrearProfesor()">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
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
            <input type="text" data-search-for="tbl-profesores" placeholder="Buscar por nombre, cédula o correo...">
        </div>
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
            <?php if (empty($profesores)): ?>
                <tr class="empty-row"><td colspan="7" style="text-align:center; padding:40px; color:var(--gray-400);">No se encontraron profesores registrados.</td></tr>
            <?php else: ?>
                <?php foreach ($profesores as $prof):
                    $nombres   = $prof['nombres'] ?? '';
                    $apellidos = $prof['apellidos'] ?? '';
                    $fullName  = trim($nombres . ' ' . $apellidos);
                    $cedula    = $prof['cedula'] ?? '';
                    $nac       = $prof['nacionalidad'] ?? 'V';
                    $email     = $prof['email'] ?? '';
                    $titulo    = ucfirst($prof['titulo'] ?? '');
                    $genero    = $prof['genero'] ?? '';
                    $status    = $prof['status'] ?? 'active';
                    $secciones = (int)($prof['total_secciones'] ?? 0);

                    // Initials
                    $initials = mb_strtoupper(mb_substr($nombres, 0, 1) . mb_substr($apellidos, 0, 1));
                    $avatarClass = ($genero === 'F') ? 'f' : 'm';

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
                                    onclick="openToggleProfesor(<?= (int)($prof['user_id'] ?? 0) ?>, '<?= e($fullName) ?>', 'deactivate')" style="color:var(--red-500);">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"/>
                                        <line x1="15" y1="9" x2="9" y2="15"/>
                                        <line x1="9" y1="9" x2="15" y2="15"/>
                                    </svg>
                                </button>
                                <?php else: ?>
                                <button class="row-action-btn" title="Reactivar"
                                    onclick="openToggleProfesor(<?= (int)($prof['user_id'] ?? 0) ?>, '<?= e($fullName) ?>', 'activate')" style="color:var(--green-600);">
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
<div id="modal-profesor" class="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <div>
                <h2 id="modal-profesor-title">Registrar Profesor</h2>
                <p>Complete los datos del profesor para habilitarlo en el sistema.</p>
            </div>
            <button class="modal-close" onclick="document.getElementById('modal-profesor').classList.remove('show')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="modal-body">
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
        <div class="modal-footer">
            <button class="btn" style="background:var(--gray-100); color:var(--gray-600); padding:10px 20px;"
                    onclick="document.getElementById('modal-profesor').classList.remove('show')">
                Cancelar
            </button>
            <button class="btn btn-primary" style="padding:10px 24px;" id="btn-guardar-profesor"
                    onclick="guardarProfesor()">
                Guardar Profesor
            </button>
        </div>
    </div>
</div>

<!-- Modal: Confirmar Activar/Desactivar -->
<div id="modal-toggle" class="modal-overlay">
    <div class="modal" style="max-width:480px;">
        <div class="modal-header">
            <div>
                <h2 id="toggle-title">¿Desactivar profesor?</h2>
                <p id="toggle-subtitle">El profesor no podrá acceder al sistema.</p>
            </div>
            <button class="modal-close" onclick="document.getElementById('modal-toggle').classList.remove('show')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <p style="font-size:14px; color:var(--gray-600); line-height:1.5;" id="toggle-body-text">
                Al desactivar a <strong id="toggle-nombre"></strong>, perderá acceso al sistema.
                Las secciones que tenga asignadas permanecerán intactas pero requerirán reasignación.
                Esta acción puede revertirse.
            </p>
        </div>
        <div class="modal-footer">
            <button class="btn" style="background:var(--gray-100); color:var(--gray-600); padding:10px 20px;"
                    onclick="document.getElementById('modal-toggle').classList.remove('show')">
                Cancelar
            </button>
            <button class="btn" id="btn-toggle-confirm" style="background:var(--red-500); color:white; padding:10px 24px;"
                    onclick="confirmarToggle()">
                Desactivar
            </button>
        </div>
    </div>
</div>

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
    // Habilitar campos para creación
    document.getElementById('profesor_nac').disabled = false;
    document.getElementById('profesor_cedula').disabled = false;
    document.getElementById('btn-guardar-profesor').style.display = '';
    document.getElementById('modal-profesor').classList.add('show');
}

function openEditarProfesor(btn) {
    document.getElementById('profesor_id').value = btn.dataset.id || '';
    document.getElementById('profesor_nombres').value = btn.dataset.nombres || '';
    document.getElementById('profesor_apellidos').value = btn.dataset.apellidos || '';
    document.getElementById('profesor_nac').value = btn.dataset.tipoCedula || 'V';
    document.getElementById('profesor_cedula').value = btn.dataset.cedula || '';
    document.getElementById('profesor_email').value = btn.dataset.email || '';
    document.getElementById('profesor_titulo').value = btn.dataset.titulo || 'profesor';
    document.getElementById('modal-profesor-title').textContent = 'Detalles del Profesor';
    // Deshabilitar campos de identidad (no se editan)
    document.getElementById('profesor_nac').disabled = true;
    document.getElementById('profesor_cedula').disabled = true;
    document.getElementById('btn-guardar-profesor').style.display = 'none';
    document.getElementById('modal-profesor').classList.add('show');
}

function openToggleProfesor(userId, nombre, action) {
    toggleState = { userId, action };
    const isDeactivate = action === 'deactivate';
    
    document.getElementById('toggle-title').textContent = isDeactivate ? '¿Desactivar profesor?' : '¿Reactivar profesor?';
    document.getElementById('toggle-subtitle').textContent = isDeactivate 
        ? 'El profesor no podrá acceder al sistema.' 
        : 'El profesor recuperará el acceso al sistema.';
    document.getElementById('toggle-nombre').textContent = nombre;
    
    const bodyText = isDeactivate
        ? `Al desactivar a <strong>${nombre}</strong>, perderá acceso al sistema. Las secciones asignadas permanecerán intactas. Esta acción puede revertirse.`
        : `Al reactivar a <strong>${nombre}</strong>, podrá acceder nuevamente al sistema con su cuenta existente.`;
    document.getElementById('toggle-body-text').innerHTML = bodyText;
    
    const confirmBtn = document.getElementById('btn-toggle-confirm');
    confirmBtn.textContent = isDeactivate ? 'Desactivar' : 'Reactivar';
    confirmBtn.style.background = isDeactivate ? 'var(--red-500)' : 'var(--green-600)';
    
    document.getElementById('modal-toggle').classList.add('show');
}

// ── AJAX: Guardar Profesor ──
async function guardarProfesor() {
    const btn = document.getElementById('btn-guardar-profesor');
    btn.disabled = true;
    btn.textContent = 'Guardando...';

    const body = new URLSearchParams({
        csrf_token:    CSRF_TOKEN,
        nacionalidad:  document.getElementById('profesor_nac').value,
        cedula:        document.getElementById('profesor_cedula').value.trim(),
        nombres:       document.getElementById('profesor_nombres').value.trim(),
        apellidos:     document.getElementById('profesor_apellidos').value.trim(),
        email:         document.getElementById('profesor_email').value.trim(),
        titulo:        document.getElementById('profesor_titulo').value
    });

    try {
        const res = await fetch(BASE_URL + '/admin/profesores/guardar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body
        });
        const data = await res.json();

        if (data.success) {
            document.getElementById('modal-profesor').classList.remove('show');
            showToast(data.message, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(data.message || 'Error al guardar.', 'error');
        }
    } catch (err) {
        console.error(err);
        showToast('Error de conexión al guardar.', 'error');
    } finally {
        btn.disabled = false;
        btn.textContent = 'Guardar Profesor';
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

        document.getElementById('modal-toggle').classList.remove('show');
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => location.reload(), 1200);
        } else {
            showToast(data.message || 'Error al cambiar estado.', 'error');
        }
    } catch (err) {
        console.error(err);
        showToast('Error de conexión.', 'error');
    } finally {
        btn.disabled = false;
    }
}

// ── Toast notificación (sistema global — reutiliza toast.css) ──
function showToast(message, type = 'error', duration = 4000) {
    let container = document.getElementById('cc-toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'cc-toast-container';
        document.body.appendChild(container);
    }
    const icons = {
        error:   '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>',
        success: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>',
        warning: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
        info:    '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>'
    };
    const toast = document.createElement('div');
    toast.className = 'cc-toast cc-toast--' + type;
    toast.innerHTML = '<span class="cc-toast__icon">' + (icons[type] || icons.info) + '</span>' +
        '<span class="cc-toast__msg">' + message + '</span>' +
        '<button class="cc-toast__close" aria-label="Cerrar"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>';
    const dismiss = () => { toast.classList.add('cc-toast--exit'); toast.addEventListener('animationend', () => toast.remove()); };
    toast.querySelector('.cc-toast__close').addEventListener('click', dismiss);
    container.appendChild(toast);
    if (duration > 0) setTimeout(dismiss, duration);
}

// ── Close modals on click outside / Escape ──
['modal-profesor', 'modal-toggle'].forEach(id => {
    document.getElementById(id)?.addEventListener('click', function(e) {
        if (e.target === this) this.classList.remove('show');
    });
});
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        document.getElementById('modal-profesor')?.classList.remove('show');
        document.getElementById('modal-toggle')?.classList.remove('show');
    }
});

// ── DataTable Engine ──
(function() {
    const table = document.getElementById('tbl-profesores');
    if (!table) return;
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr[data-search]'));
    if (rows.length === 0) return;

    const searchInput = document.querySelector('[data-search-for="tbl-profesores"]');
    const perPageSel = document.querySelector('[data-perpage-for="tbl-profesores"]');
    const footer = document.querySelector('[data-footer-for="tbl-profesores"]');
    const footerInfo = footer?.querySelector('.table-footer-info');
    const paginationEl = footer?.querySelector('.pagination');

    let searchTerm = '', currentPage = 1, sortCol = null, sortDir = 1;

    function getPerPage() { return parseInt(perPageSel?.value || '10', 10); }

    function getVisible() {
        return rows.filter(r => !searchTerm || (r.dataset.search || '').includes(searchTerm));
    }

    function sortRows(arr) {
        if (sortCol === null) return arr;
        return arr.slice().sort((a, b) => {
            const va = (a.children[sortCol]?.textContent || '').trim().toLowerCase();
            const vb = (b.children[sortCol]?.textContent || '').trim().toLowerCase();
            const na = parseFloat(va.replace(/[^\d.-]/g, ''));
            const nb = parseFloat(vb.replace(/[^\d.-]/g, ''));
            if (!isNaN(na) && !isNaN(nb)) return sortDir * (na - nb);
            return sortDir * va.localeCompare(vb);
        });
    }

    function render() {
        const PER_PAGE = getPerPage();
        const visible = sortRows(getVisible());
        const totalPages = Math.max(1, Math.ceil(visible.length / PER_PAGE));
        if (currentPage > totalPages) currentPage = totalPages;
        const start = (currentPage - 1) * PER_PAGE;
        const pageRows = visible.slice(start, start + PER_PAGE);

        rows.forEach(r => r.style.display = 'none');
        pageRows.forEach(r => r.style.display = '');

        if (footerInfo) {
            const from = visible.length > 0 ? start + 1 : 0;
            const to = Math.min(start + PER_PAGE, visible.length);
            footerInfo.innerHTML = `Mostrando <strong>${from}</strong> a <strong>${to}</strong> de <strong>${visible.length}</strong> registros`;
        }

        if (paginationEl) {
            paginationEl.innerHTML = '';
            if (totalPages > 1) {
                const prev = document.createElement('button');
                prev.innerHTML = '‹'; prev.disabled = currentPage === 1;
                prev.addEventListener('click', () => { currentPage--; render(); });
                paginationEl.appendChild(prev);
                for (let p = 1; p <= totalPages; p++) {
                    const b = document.createElement('button');
                    b.textContent = p;
                    if (p === currentPage) b.classList.add('active');
                    b.addEventListener('click', () => { currentPage = p; render(); });
                    paginationEl.appendChild(b);
                }
                const next = document.createElement('button');
                next.innerHTML = '›'; next.disabled = currentPage === totalPages;
                next.addEventListener('click', () => { currentPage++; render(); });
                paginationEl.appendChild(next);
            }
        }
    }

    searchInput?.addEventListener('input', e => {
        searchTerm = e.target.value.toLowerCase().trim();
        currentPage = 1;
        render();
    });

    perPageSel?.addEventListener('change', () => { currentPage = 1; render(); });

    table.querySelectorAll('th.sortable[data-col]').forEach(th => {
        th.style.cursor = 'pointer';
        th.addEventListener('click', () => {
            const col = parseInt(th.dataset.col, 10);
            if (sortCol === col) sortDir *= -1;
            else { sortCol = col; sortDir = 1; }
            table.querySelectorAll('th.sortable').forEach(h => h.classList.remove('sort-asc', 'sort-desc'));
            th.classList.add(sortDir === 1 ? 'sort-asc' : 'sort-desc');
            render();
        });
    });

    render();
})();
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/logged_layout.php';
?>