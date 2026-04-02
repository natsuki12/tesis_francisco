<?php
declare(strict_types=1);

$pageTitle = 'Gestión de Secciones';
$activePage = 'secciones';
$breadcrumbs = [
    'Inicio' => base_url('/admin'),
    'Gestión Académica' => '#',
    'Secciones' => '#'
];

$extraCss = '<link rel="stylesheet" href="' . asset('css/shared/data-table.css') . '">
             <link rel="stylesheet" href="' . asset('css/global/autocomplete_dropdown.css') . '">';
$extraJs  = '<script src="' . asset('js/global/autocomplete_dropdown.js') . '"></script>';

// Datos inyectados por el controlador
$secciones      = $secciones      ?? [];
$periodos       = $periodos       ?? [];
$profesores     = $profesores     ?? [];
$materias       = $materias       ?? [];
$periodoActivo  = $periodoActivo  ?? null;

ob_start();
?>
<div class="page-header">
    <div class="page-header-left">
        <h1>Secciones Activas e Históricas</h1>
        <p>Administre las secciones disponibles, asigne a los profesores responsables y visualice la matrícula.</p>
    </div>
    <button class="btn btn-primary" onclick="openCrearSeccion()">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
            stroke-linecap="round">
            <line x1="12" y1="5" x2="12" y2="19" />
            <line x1="5" y1="12" x2="19" y2="12" />
        </svg>
        Crear Sección
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
            <input type="text" data-search-for="tbl-secciones" placeholder="Buscar sección, profesor o período...">
        </div>
    </div>
    <div class="toolbar-right">
        <label style="font-size:var(--text-xs); color:var(--gray-500); display:flex; align-items:center; gap:6px;">
            Mostrar <select data-perpage-for="tbl-secciones" class="per-page-select"><option value="10" selected>10</option><option value="15">15</option><option value="25">25</option></select> filas
        </label>
    </div>
</div>

<!-- Data Table -->
<div class="table-container">
    <table class="data-table" id="tbl-secciones">
        <thead>
            <tr>
                <th class="sortable" data-col="0" style="width:4%">ID</th>
                <th class="sortable" data-col="1" style="width:11%">Sección</th>
                <th class="sortable" data-col="2" style="width:18%">Materia</th>
                <th class="sortable" data-col="3" style="width:13%">Período</th>
                <th class="sortable" data-col="4" style="width:20%">Profesor Asignado</th>
                <th class="sortable" data-col="5" style="width:13%">Inscritos / Cupo</th>
                <th class="sortable" data-col="6" style="width:9%">Estado</th>
                <th style="width:90px">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($secciones)): ?>
                <tr class="empty-row"><td colspan="8" style="text-align:center; padding:40px; color:var(--gray-400);">No se encontraron secciones registradas.</td></tr>
            <?php else: ?>
                <?php foreach ($secciones as $sec):
                    $nombre     = $sec['nombre'] ?? '';
                    $materia    = $sec['materia'] ?? '—';
                    $periodo    = $sec['periodo'] ?? '—';
                    $periodoAct = (int)($sec['periodo_activo'] ?? 0);
                    $profesor   = $sec['profesor_nombre'] ?? null;
                    $inscritos  = (int)($sec['inscritos'] ?? 0);
                    $cupo       = (int)($sec['cupo_maximo'] ?? 40);
                    $pct        = $cupo > 0 ? round($inscritos / $cupo * 100) : 0;

                    $initials = '—';
                    if ($profesor) {
                        $parts = explode(' ', $profesor);
                        $initials = '';
                        foreach ($parts as $p) { if ($p !== '') $initials .= mb_strtoupper(mb_substr($p, 0, 1)); }
                        $initials = mb_substr($initials, 0, 2);
                    }

                    $estado = $periodoAct ? 'Abierta' : 'Cerrada';
                    $searchText = mb_strtolower($nombre . ' ' . $materia . ' ' . $periodo . ' ' . $profesor);
                ?>
                    <tr data-search="<?= e($searchText) ?>">
                        <td style="color:var(--gray-400); font-size:12px;"><?= (int)$sec['id'] ?></td>
                        <td><strong><?= e($nombre) ?></strong></td>
                        <td style="font-size:13px;"><?= e($materia) ?></td>
                        <td>
                            <?= e($periodo) ?>
                            <?php if ($periodoAct): ?>
                                <span style="display:inline-block; width:6px; height:6px; background:var(--green-500); border-radius:50%; margin-left:6px; vertical-align:middle;" title="Período activo"></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($profesor): ?>
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <div class="causante-avatar m" style="width:28px;height:28px;font-size:10px;"><?= e($initials) ?></div>
                                    <span><?= e($profesor) ?></span>
                                </div>
                            <?php else: ?>
                                <span style="color:var(--gray-400); font-style:italic; font-size:13px;">Sin asignar</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?= $inscritos ?></strong> / <?= $cupo ?>
                            <?php if ($pct >= 90): ?>
                                <span style="font-size:10px; color:var(--red-500); margin-left:4px;">●</span>
                            <?php elseif ($pct >= 70): ?>
                                <span style="font-size:10px; color:var(--yellow-500); margin-left:4px;">●</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($estado === 'Abierta'): ?>
                                <span class="status-badge status-published">Abierta</span>
                            <?php else: ?>
                                <span class="status-badge status-draft">Cerrada</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="row-actions">
                                <button class="row-action-btn" title="Gestionar Estudiantes"
                                    onclick="abrirEstudiantes(<?= (int)$sec['id'] ?>, '<?= e(addslashes($nombre)) ?>', <?= (int)$cupo ?>)">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                    </svg>
                                </button>
                                <button class="row-action-btn" title="Editar Sección"
                                    onclick="openEditarSeccion(this)"
                                    data-id="<?= (int)$sec['id'] ?>"
                                    data-nombre="<?= e($nombre) ?>"
                                    data-cupo="<?= (int)$cupo ?>"
                                    data-materia="<?= (int)($sec['materia_id'] ?? 0) ?>"
                                    data-profesor="<?= !empty($sec['profesor_id']) ? (int)$sec['profesor_id'] : '' ?>">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    <div class="table-footer" data-footer-for="tbl-secciones">
        <div class="table-footer-info"></div>
        <div class="pagination"></div>
    </div>
</div>

<!-- ==============================================
     MODAL: Crear / Editar Sección (HTML5 dialog)
     ============================================== -->
<dialog class="modal-base" id="modal-seccion">
    <div class="modal-base__container" style="max-width: 600px;">
        <div class="modal-base__header">
            <h2 class="modal-base__title" id="modal-seccion-title">Crear Sección</h2>
            <button class="modal-base__close" onclick="window.modalManager.close('modal-seccion')" aria-label="Cerrar modal">&times;</button>
        </div>
        <div class="modal-base__body">
            <p id="modal-seccion-desc" style="font-size: 15px; color: var(--text-body); margin-bottom: 20px;">
                Defina el nombre, materia, profesor y cupo. Se asignará al período activo automáticamente.
            </p>

            <?php if ($periodoActivo): ?>
                <div style="background: rgba(59,130,246,0.08); border: 1px solid rgba(59,130,246,0.2); border-radius: 8px; padding: 10px 14px; margin-bottom: 16px; font-size: 13px; color: var(--blue-600, #2563eb); display:flex; align-items:center; gap:8px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                    Período activo: <strong><?= e($periodoActivo['nombre']) ?></strong>
                </div>
            <?php else: ?>
                <div style="background: rgba(245,158,11,0.08); border: 1px solid rgba(245,158,11,0.2); border-radius: 8px; padding: 10px 14px; margin-bottom: 16px; font-size: 13px; color: var(--yellow-600, #d97706);">
                    ⚠ No hay un período académico activo. No se pueden crear secciones.
                </div>
            <?php endif; ?>

            <input type="hidden" id="seccion_id" value="">

            <div class="form-grid">
                <div class="form-group">
                    <label>Nombre de Sección <span class="required">*</span></label>
                    <input type="text" id="seccion_nombre" placeholder="Ej: 1A, Noche-B" maxlength="20">
                </div>

                <div class="form-group">
                    <label>Cupo Máximo <span class="required">*</span></label>
                    <input type="number" id="seccion_cupo" placeholder="Ej: 40" min="1" max="999" value="40">
                </div>

                <div class="form-group">
                    <label>Materia <span class="required">*</span></label>
                    <select id="seccion_materia">
                        <?php if (count($materias) === 1): ?>
                            <option value="<?= (int)$materias[0]['id'] ?>" selected><?= e($materias[0]['nombre']) ?></option>
                        <?php else: ?>
                            <option value="">— Seleccione una materia —</option>
                            <?php foreach ($materias as $mat): ?>
                                <option value="<?= (int)$mat['id'] ?>"><?= e($mat['nombre']) ?><?= $mat['codigo'] ? ' (' . e($mat['codigo']) . ')' : '' ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Profesor Asignado</label>
                    <select id="seccion_profesor">
                        <option value="">— Seleccione un profesor —</option>
                        <?php foreach ($profesores as $prof): ?>
                            <option value="<?= (int)$prof['id'] ?>">
                                <?= e(ucfirst($prof['titulo'] ?? '')) ?>. <?= e($prof['nombre_completo']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="modal-base__footer">
            <button class="modal-btn modal-btn-cancel" onclick="window.modalManager.close('modal-seccion')">Cancelar</button>
            <button class="modal-btn modal-btn-primary" id="btn-guardar-seccion" onclick="guardarSeccion()"
                    <?= !$periodoActivo ? 'disabled' : '' ?>>Crear Sección</button>
        </div>
    </div>
</dialog>

<!-- ==============================================================
     MODAL: Gestionar Estudiantes de una Sección
     Usa div overlay (mismo patrón que gc-modal-overlay del profesor)
     para compatibilidad con AutocompleteDropdown (z-index: 9999)
     ============================================================== -->
<div class="gc-modal-overlay" id="modal-estudiantes" style="display:none">
    <div class="gc-modal" style="max-width: 640px;">
        <div class="modal-base__header">
            <div style="display:flex;align-items:center;gap:10px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    width="20" height="20" style="color:var(--blue-500,#3b82f6);">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
                <div>
                    <h3 class="modal-base__title" id="modal-est-title">Estudiantes</h3>
                    <p style="font-size:12px; color:var(--gray-500); margin:2px 0 0; font-weight:500;" id="modal-est-cupo"></p>
                </div>
            </div>
            <button class="modal-base__close" onclick="cerrarModalEstudiantes()">&times;</button>
        </div>
        <div class="modal-base__body">
            <!-- Buscador para agregar -->
            <div class="gc-section-divider" style="margin-top:0;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" style="flex-shrink:0;">
                    <circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.35-4.35"></path>
                </svg>
                Inscribir estudiante
            </div>
            <div class="gc-form-group" style="margin-bottom:20px;">
                <input type="text" id="buscar-est-input" class="gc-input" placeholder="Buscar por nombre, cédula o correo..." autocomplete="off">
            </div>

            <!-- Inline errors -->
            <div id="est-error-box" style="display:none; margin-bottom:12px; padding:10px 14px; border-radius:var(--radius-sm,8px); background:var(--red-50,#fef2f2); border:1px solid var(--red-200,#fecaca); color:var(--red-700,#b91c1c); font-size:13px; font-weight:500;">
                <span id="est-error-msg"></span>
            </div>

            <!-- Lista de inscritos -->
            <div class="gc-section-divider">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" style="flex-shrink:0;">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                </svg>
                Inscritos <span class="gc-student-counter" id="est-counter">0</span>
            </div>
            <div id="lista-estudiantes" style="max-height:340px; overflow-y:auto;"></div>
            <!-- Empty state -->
            <div id="est-empty" class="gc-empty-students" style="display:none;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="32" height="32">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <line x1="19" y1="11" x2="23" y2="11"></line>
                </svg>
                <span>No hay estudiantes inscritos en esta sección.</span>
            </div>
            <!-- Loading -->
            <div id="est-loading" style="display:none; text-align:center; padding:30px 0; color:var(--gray-400);">
                Cargando...
            </div>
        </div>
        <div class="modal-base__footer">
            <button class="modal-btn modal-btn-cancel" onclick="cerrarModalEstudiantes()">Cancelar</button>
            <button class="modal-btn modal-btn-primary" id="btn-guardar-estudiantes" onclick="guardarEstudiantes()">Guardar</button>
        </div>
    </div>
</div>

<!-- Scoped styles for gc-modal-overlay (same as professor's gestionar_caso.css) -->
<style>
    .gc-modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(10, 30, 61, 0.5);
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }
    .gc-modal {
        background: var(--white, #ffffff);
        border-radius: var(--radius-lg, 12px);
        width: 100%;
        max-width: 640px;
        max-height: 90vh;
        display: flex;
        flex-direction: column;
        box-shadow: 0 24px 80px rgba(0, 0, 0, 0.25);
        position: relative;
        z-index: 1;
        overflow: hidden;
    }
    .gc-modal .modal-base__body {
        flex: 1;
        overflow-y: auto;
    }
    .gc-section-divider {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 8px 0 12px;
        padding: 10px 14px;
        font-size: 13px;
        font-weight: 600;
        color: var(--blue-700, #1d4ed8);
        background: var(--blue-50, #eff6ff);
        border-radius: var(--radius-sm, 8px);
        border: 1px solid var(--blue-100, #dbeafe);
    }
    .gc-student-counter {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 22px;
        height: 22px;
        padding: 0 7px;
        border-radius: 11px;
        font-size: 11px;
        font-weight: 700;
        background: var(--blue-600, #2563eb);
        color: #fff;
    }
    .gc-form-group { display: flex; flex-direction: column; gap: 0.35rem; }
    .gc-input {
        width: 100%;
        padding: 9px 12px;
        border: 1px solid var(--gray-200, #e5e7eb);
        border-radius: var(--radius-sm, 8px);
        font-size: var(--text-md, 14px);
        background: var(--white, #ffffff);
        color: var(--gray-700, #374151);
        transition: border-color 0.15s;
        font-family: var(--font-ui, inherit);
    }
    .gc-input:focus {
        outline: none;
        border-color: var(--blue-400, #60a5fa);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    .gc-empty-students {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 28px 16px;
        width: 100%;
        color: var(--gray-400, #9ca3af);
        text-align: center;
    }
    .gc-empty-students svg { opacity: 0.5; }
    .gc-empty-students span { font-size: 13px; font-style: italic; }
</style>

<script>
const CSRF_TOKEN = '<?= \App\Core\Csrf::getToken() ?>';
const BASE_URL = '<?= base_url('') ?>';

let editingId = null;

// ── Abrir modal crear ──
function openCrearSeccion() {
    editingId = null;
    document.getElementById('seccion_id').value = '';
    document.getElementById('seccion_nombre').value = '';
    document.getElementById('seccion_cupo').value = '40';
    document.getElementById('seccion_materia').value = <?= count($materias) === 1 ? (int)$materias[0]['id'] : "''" ?>;
    document.getElementById('seccion_profesor').value = '';
    document.getElementById('modal-seccion-title').textContent = 'Crear Sección';
    document.getElementById('modal-seccion-desc').textContent = 'Defina el nombre, materia, profesor y cupo. Se asignará al período activo automáticamente.';
    document.getElementById('btn-guardar-seccion').textContent = 'Crear Sección';
    window.modalManager.clearError('modal-seccion');
    window.modalManager.open('modal-seccion');
}

// ── Abrir modal editar ──
function openEditarSeccion(btn) {
    editingId = btn.dataset.id;
    document.getElementById('seccion_id').value = editingId;
    document.getElementById('seccion_nombre').value = btn.dataset.nombre || '';
    document.getElementById('seccion_cupo').value = btn.dataset.cupo || '40';
    document.getElementById('seccion_materia').value = btn.dataset.materia || '';
    document.getElementById('seccion_profesor').value = btn.dataset.profesor || '';
    document.getElementById('modal-seccion-title').textContent = 'Editar Sección';
    document.getElementById('modal-seccion-desc').textContent = 'Modifique los datos de la sección y guarde los cambios.';
    document.getElementById('btn-guardar-seccion').textContent = 'Guardar Cambios';
    document.getElementById('btn-guardar-seccion').disabled = false;
    window.modalManager.clearError('modal-seccion');
    window.modalManager.open('modal-seccion');
}

// ── AJAX: Guardar sección (crear o editar) ──
async function guardarSeccion() {
    const btn = document.getElementById('btn-guardar-seccion');
    window.modalManager.clearError('modal-seccion');
    window.modalManager.setButtonLoading(btn);

    const nombre   = document.getElementById('seccion_nombre').value.trim();
    const cupo     = document.getElementById('seccion_cupo').value;
    const materia  = document.getElementById('seccion_materia').value;
    const profesor = document.getElementById('seccion_profesor').value;

    // Validación client-side rápida
    if (!nombre) {
        window.modalManager.showError('modal-seccion', 'El nombre de la sección es obligatorio.');
        window.modalManager.resetButtonLoading(btn);
        return;
    }

    if (!materia) {
        window.modalManager.showError('modal-seccion', 'Debe seleccionar una materia.');
        window.modalManager.resetButtonLoading(btn);
        return;
    }

    try {
        const params = {
            csrf_token:  CSRF_TOKEN,
            nombre:      nombre,
            cupo_maximo: cupo,
            profesor_id: profesor,
            materia_id:  materia
        };

        let url = BASE_URL + '/admin/secciones/crear';

        if (editingId) {
            params.id = editingId;
            url = BASE_URL + '/admin/secciones/actualizar';
        }

        const res = await fetch(url, {
            method: 'POST',
            body: new URLSearchParams(params)
        });

        if (res.redirected || !res.ok) {
            window.modalManager.showError('modal-seccion', 'Sesión expirada. Recargue la página.');
            window.modalManager.resetButtonLoading(btn);
            return;
        }

        const data = await res.json();

        if (data.success) {
            window.modalManager.close('modal-seccion');
            if (window.showToast) window.showToast(data.message, 'success');
            setTimeout(() => location.reload(), 800);
        } else {
            window.modalManager.showError('modal-seccion', data.message || 'Error al guardar la sección.');
        }
    } catch (err) {
        console.error(err);
        window.modalManager.showError('modal-seccion', 'No se pudo conectar con el servidor.');
    } finally {
        window.modalManager.resetButtonLoading(btn);
    }
}
// ═══════════════════════════════════════════════════════════
//  GESTIÓN DE ESTUDIANTES POR SECCIÓN (BATCH SAVE)
// ═══════════════════════════════════════════════════════════

let currentSeccionId = null;
let currentSeccionCupo = 0;
let autocompleteEst = null;
let localEstudiantes = [];  // [{id, nombre_completo, cedula, nacionalidad, email}]
let isSaving = false;

function cerrarModalEstudiantes() {
    document.getElementById('modal-estudiantes').style.display = 'none';
    document.body.style.overflow = '';
    localEstudiantes = [];
    clearEstError();
}

// ── Inline error helpers ──
let estErrorTimer = null;
function showEstError(msg) {
    const box = document.getElementById('est-error-box');
    document.getElementById('est-error-msg').textContent = msg;
    box.style.display = '';
    if (estErrorTimer) clearTimeout(estErrorTimer);
    estErrorTimer = setTimeout(() => { box.style.display = 'none'; }, 5000);
}
function clearEstError() {
    document.getElementById('est-error-box').style.display = 'none';
    if (estErrorTimer) { clearTimeout(estErrorTimer); estErrorTimer = null; }
}

// ── Renderizar lista desde estado local ──
function renderListaEstudiantes() {
    const counter = document.getElementById('est-counter');
    const cupoLabel = document.getElementById('modal-est-cupo');
    counter.textContent = localEstudiantes.length;
    cupoLabel.textContent = `${localEstudiantes.length} / ${currentSeccionCupo} cupos ocupados`;

    const container = document.getElementById('lista-estudiantes');
    const empty = document.getElementById('est-empty');

    if (localEstudiantes.length === 0) {
        container.innerHTML = '';
        empty.style.display = '';
        return;
    }

    empty.style.display = 'none';
    container.innerHTML = localEstudiantes.map((est, i) => {
        const cedula = (est.nacionalidad || 'V') + '-' + (est.cedula || '');
        const parts = (est.nombre_completo || '').split(' ');
        let initials = '';
        parts.forEach(p => { if (p) initials += p.charAt(0).toUpperCase(); });
        initials = initials.substring(0, 2);

        return `<div class="est-row" style="display:flex; align-items:center; gap:12px; padding:10px 0;${i > 0 ? ' border-top:1px solid var(--gray-100);' : ''}">
            <div class="causante-avatar m" style="width:32px;height:32px;font-size:11px;flex-shrink:0;">${initials}</div>
            <div style="flex:1; min-width:0;">
                <div style="font-size:14px; font-weight:var(--weight-semibold); color:var(--gray-800);">${est.nombre_completo}</div>
                <div style="font-size:12px; color:var(--gray-500);">${cedula}${est.email ? ' · ' + est.email : ''}</div>
            </div>
            <button class="row-action-btn" title="Remover" style="color:var(--red-500); flex-shrink:0;"
                onclick="removerEstudianteLocal(${est.id})"
            >
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </div>`;
    }).join('');
}

// ── Agregar al estado local ──
function agregarEstudianteLocal(item) {
    const itemId = Number(item.id);
    if (localEstudiantes.some(e => e.id === itemId)) {
        showEstError('El estudiante ya está en la lista.');
        return;
    }
    if (localEstudiantes.length >= currentSeccionCupo) {
        showEstError('Se alcanzó el cupo máximo de la sección.');
        return;
    }
    localEstudiantes.push({
        id: itemId,
        nombre_completo: item.nombre_completo,
        cedula: item.cedula,
        nacionalidad: item.nacionalidad,
        email: item.email
    });
    renderListaEstudiantes();
}

// ── Remover del estado local ──
function removerEstudianteLocal(estudianteId) {
    localEstudiantes = localEstudiantes.filter(e => e.id !== estudianteId);
    renderListaEstudiantes();
    // Limpiar caché para que el removido vuelva a aparecer en búsquedas
    if (autocompleteEst) autocompleteEst._cache.clear();
}

// ── Abrir modal ──
async function abrirEstudiantes(seccionId, nombre, cupo) {
    currentSeccionId = seccionId;
    currentSeccionCupo = cupo;
    localEstudiantes = [];
    isSaving = false;

    // Limpiar caché del autocomplete para que busque con la nueva sección
    if (autocompleteEst) {
        autocompleteEst._cache.clear();
        autocompleteEst.close();
    }

    document.getElementById('modal-est-title').textContent = `Estudiantes — ${nombre}`;
    document.getElementById('modal-est-cupo').textContent = 'Cargando...';
    document.getElementById('est-counter').textContent = '0';
    document.getElementById('lista-estudiantes').innerHTML = '';
    document.getElementById('est-empty').style.display = 'none';
    document.getElementById('est-loading').style.display = '';
    document.getElementById('buscar-est-input').value = '';
    clearEstError();

    const btnGuardar = document.getElementById('btn-guardar-estudiantes');
    btnGuardar.disabled = false;
    btnGuardar.textContent = 'Guardar';

    document.getElementById('modal-estudiantes').style.display = '';
    document.body.style.overflow = 'hidden';

    // Inicializar autocomplete (una sola vez)
    if (!autocompleteEst) {
        autocompleteEst = new AutocompleteDropdown({
            input: document.getElementById('buscar-est-input'),
            fetchFn: async (query) => {
                const res = await fetch(BASE_URL + `/admin/secciones/buscar-estudiantes?seccion_id=${currentSeccionId}&q=${encodeURIComponent(query)}`);
                const results = await res.json();
                // Filtrar los que ya están en la lista local
                const localIds = new Set(localEstudiantes.map(e => e.id));
                return results.filter(r => !localIds.has(Number(r.id)));
            },
            renderItem: (item) => {
                const cedula = (item.nacionalidad || 'V') + '-' + (item.cedula || '');
                return `
                    <div style="line-height:1.4;">
                        <span class="ac-dropdown__cedula">${cedula}</span>
                        <span class="ac-dropdown__sep">—</span>
                        <span class="ac-dropdown__name">${item.nombre_completo}</span>
                        <br>
                        <small style="color:#64748b;">${item.email || 'Sin correo'}</small>
                    </div>`;
            },
            onSelect: (item) => {
                agregarEstudianteLocal(item);
                document.getElementById('buscar-est-input').value = '';
                // Cerrar dropdown tras selección (override del auto-reopen del componente)
                setTimeout(() => {
                    if (autocompleteEst) autocompleteEst.close();
                    document.getElementById('buscar-est-input').blur();
                }, 80);
                // Scroll al final para que se vea la adición
                const lista = document.getElementById('lista-estudiantes');
                setTimeout(() => { lista.scrollTop = lista.scrollHeight; }, 100);
            },
            minLength: 0
        });
    }

    // Cargar inscritos actuales al estado local
    try {
        const res = await fetch(BASE_URL + `/admin/secciones/estudiantes?seccion_id=${currentSeccionId}`);
        const json = await res.json();
        document.getElementById('est-loading').style.display = 'none';

        if (json.success && json.data) {
            localEstudiantes = json.data.map(est => ({
                id: Number(est.id),
                nombre_completo: est.nombre_completo,
                cedula: est.cedula,
                nacionalidad: est.nacionalidad,
                email: est.email
            }));
        }
        renderListaEstudiantes();
    } catch (err) {
        console.error(err);
        document.getElementById('est-loading').style.display = 'none';
        document.getElementById('est-empty').querySelector('span').textContent = 'Error al cargar estudiantes.';
        document.getElementById('est-empty').style.display = '';
    }
}

// ── Guardar (batch sync) con protección doble clic ──
async function guardarEstudiantes() {
    if (isSaving) return;
    isSaving = true;

    const btn = document.getElementById('btn-guardar-estudiantes');
    const originalText = btn.textContent;
    btn.disabled = true;
    btn.textContent = 'Guardando...';

    try {
        const res = await fetch(BASE_URL + '/admin/secciones/sync-estudiantes', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                csrf_token: CSRF_TOKEN,
                seccion_id: currentSeccionId,
                estudiante_ids: localEstudiantes.map(e => e.id)
            })
        });

        const data = await res.json();

        if (data.success) {
            if (window.showToast) showToast(data.message, 'success');
            actualizarContadorTabla(currentSeccionId, data.inscritos);
            cerrarModalEstudiantes();
        } else {
            showEstError(data.message || 'Error al guardar.');
            btn.disabled = false;
            btn.textContent = originalText;
            isSaving = false;
        }
    } catch (err) {
        console.error(err);
        showEstError('Error de conexión con el servidor.');
        btn.disabled = false;
        btn.textContent = originalText;
        isSaving = false;
    }
}

// Cerrar con Escape
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && document.getElementById('modal-estudiantes').style.display !== 'none') {
        cerrarModalEstudiantes();
    }
});

// ── Helper: actualizar tabla principal ──
function actualizarContadorTabla(seccionId, inscritos) {
    const table = document.getElementById('tbl-secciones');
    if (!table) return;
    const rows = table.querySelectorAll('tbody tr');
    for (const row of rows) {
        const idCell = row.children[0];
        if (idCell && idCell.textContent.trim() == seccionId) {
            const cupoCell = row.children[5];
            if (cupoCell) {
                const parts = cupoCell.innerHTML.split('/');
                if (parts.length >= 2) {
                    const cupoNum = parseInt(parts[1]) || currentSeccionCupo;
                    const pct = cupoNum > 0 ? Math.round(inscritos / cupoNum * 100) : 0;
                    let dot = '';
                    if (pct >= 90) dot = '<span style="font-size:10px; color:var(--red-500); margin-left:4px;">●</span>';
                    else if (pct >= 70) dot = '<span style="font-size:10px; color:var(--yellow-500); margin-left:4px;">●</span>';
                    cupoCell.innerHTML = `<strong>${inscritos}</strong> / ${cupoNum}${dot}`;
                }
            }
            break;
        }
    }
}
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/logged_layout.php';
?>