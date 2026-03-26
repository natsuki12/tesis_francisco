<?php
declare(strict_types=1);

$pageTitle = 'Gestión de Secciones';
$activePage = 'secciones';
$breadcrumbs = [
    'Inicio' => base_url('/admin'),
    'Gestión Académica' => '#',
    'Secciones' => '#'
];

$extraCss = '<link rel="stylesheet" href="' . asset('css/shared/data-table.css') . '">';

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
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/logged_layout.php';
?>