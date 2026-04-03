<?php
declare(strict_types=1);

$pageTitle = 'Gestión de Períodos';
$activePage = 'periodos';
$breadcrumbs = [
    'Inicio' => base_url('/admin'),
    'Gestión Académica' => '#',
    'Períodos' => '#'
];

$extraCss = '<link rel="stylesheet" href="' . asset('css/shared/data-table.css') . '">';

// Datos inyectados por el controlador
$periodos = $periodos ?? [];

// Meses en español
$meses = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];

ob_start();
?>
<div class="page-header">
    <div class="page-header-left">
        <h1>Períodos Académicos</h1>
        <p>Gestione los períodos académicos en los que se dictará el uso del Simulador.</p>
    </div>
    <button class="btn btn-primary" onclick="openCrearPeriodo()">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
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
            <input type="text" data-search-for="tbl-periodos" placeholder="Buscar período...">
        </div>
    </div>
    <div class="toolbar-right">
        <label style="font-size:var(--text-xs); color:var(--gray-500); display:flex; align-items:center; gap:6px;">
            Mostrar <select data-perpage-for="tbl-periodos" class="per-page-select"><option value="10" selected>10</option><option value="15">15</option><option value="25">25</option></select> filas
        </label>
    </div>
</div>

<!-- Data Table -->
<div class="table-container">
    <table class="data-table" id="tbl-periodos">
        <thead>
            <tr>
                <th class="sortable" data-col="0" style="width:50px">ID</th>
                <th class="sortable" data-col="1">Período</th>
                <th class="sortable" data-col="2">Fecha Inicio</th>
                <th class="sortable" data-col="3">Fecha Fin</th>
                <th class="sortable" data-col="4" style="width:100px">Secciones</th>
                <th class="sortable" data-col="5" style="width:120px">Inscritos</th>
                <th class="sortable" data-col="6" style="width:100px">Estado</th>
                <th style="width:90px">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($periodos)): ?>
                <tr class="empty-row"><td colspan="8" style="text-align:center; padding:40px; color:var(--gray-400);">No se encontraron períodos registrados.</td></tr>
            <?php else: ?>
                <?php foreach ($periodos as $per):
                    $nombre   = $per['nombre'] ?? '—';
                    $activo   = (int)($per['activo'] ?? 0);
                    $secciones = (int)($per['total_secciones'] ?? 0);
                    $inscritos = (int)($per['total_inscritos'] ?? 0);

                    // Format dates
                    $fmtDate = function(?string $d) use ($meses): string {
                        if (!$d) return '—';
                        try {
                            $dt = new \DateTime($d);
                            return $dt->format('d') . ' ' . $meses[(int)$dt->format('n') - 1] . ' ' . $dt->format('Y');
                        } catch (\Throwable $e) { return $d; }
                    };

                    $fechaInicio = $fmtDate($per['fecha_inicio'] ?? null);
                    $fechaFin    = $fmtDate($per['fecha_fin'] ?? null);
                    $estado      = $activo ? 'Activo' : 'Inactivo';

                    $searchText = mb_strtolower($nombre . ' ' . $estado);
                ?>
                    <tr data-search="<?= e($searchText) ?>">
                        <td style="color:var(--gray-400); font-size:12px;"><?= (int)$per['id'] ?></td>
                        <td><strong><?= e($nombre) ?></strong></td>
                        <td style="font-size:13px;"><?= $fechaInicio ?></td>
                        <td style="font-size:13px;"><?= $fechaFin ?></td>
                        <td style="text-align:center;">
                            <strong><?= $secciones ?></strong>
                        </td>
                        <td>
                            <strong><?= $inscritos ?></strong>
                        </td>
                        <td>
                            <?php if ($activo): ?>
                                <span class="status-badge status-published">Activo</span>
                            <?php else: ?>
                                <span class="status-badge status-draft">Inactivo</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="row-actions">
                                <button class="row-action-btn" title="Editar Período"
                                    onclick="openEditarPeriodo(this)"
                                    data-id="<?= (int)$per['id'] ?>"
                                    data-codigo="<?= e($nombre) ?>"
                                    data-fecha-inicio="<?= e($per['fecha_inicio'] ?? '') ?>"
                                    data-fecha-fin="<?= e($per['fecha_fin'] ?? '') ?>">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                                    </svg>
                                </button>
                                <?php if ($activo): ?>
                                <button class="row-action-btn" title="Cerrar Período"
                                    onclick="openTogglePeriodo(<?= (int)$per['id'] ?>, 'cerrar', '<?= e(addslashes($nombre)) ?>')" style="color:var(--red-500);">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M18.36 6.64a9 9 0 1 1-12.73 0" />
                                        <line x1="12" y1="2" x2="12" y2="12" />
                                    </svg>
                                </button>
                                <?php else: ?>
                                <button class="row-action-btn" title="Reactivar Período"
                                    onclick="openTogglePeriodo(<?= (int)$per['id'] ?>, 'reactivar', '<?= e(addslashes($nombre)) ?>')" style="color:var(--green-500);">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M23 4v6h-6" /><path d="M1 20v-6h6" />
                                        <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15" />
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
    <div class="table-footer" data-footer-for="tbl-periodos">
        <div class="table-footer-info"></div>
        <div class="pagination"></div>
    </div>
</div>

<!-- ==============================================
     MODAL: Crear / Editar Período (HTML5 dialog)
     ============================================== -->
<dialog class="modal-base" id="modal-periodo">
    <div class="modal-base__container" style="max-width: 500px;">
        <div class="modal-base__header">
            <h2 class="modal-base__title" id="modal-periodo-title">Crear Período Académico</h2>
            <button class="modal-base__close" onclick="window.modalManager.close('modal-periodo')" aria-label="Cerrar modal">&times;</button>
        </div>
        <div class="modal-base__body">
            <p id="modal-periodo-desc" style="font-size: 15px; color: var(--text-body); margin-bottom: 20px;">
                Defina el código del período y su rango de fechas.
            </p>

            <input type="hidden" id="periodo_id" value="">

            <div class="form-grid">
                <div class="form-group form-full">
                    <label>Código del Período <span class="required">*</span></label>
                    <input type="text" id="periodo_codigo" placeholder="Ej: 2026-I" maxlength="20">
                </div>

                <div class="form-group">
                    <label>Fecha de Inicio <span class="required">*</span></label>
                    <input type="date" id="periodo_fecha_inicio">
                </div>

                <div class="form-group">
                    <label>Fecha de Fin <span class="required">*</span></label>
                    <input type="date" id="periodo_fecha_fin">
                </div>
            </div>
        </div>
        <div class="modal-base__footer">
            <button class="modal-btn modal-btn-cancel" onclick="window.modalManager.close('modal-periodo')">Cancelar</button>
            <button class="modal-btn modal-btn-primary" id="btn-guardar-periodo" onclick="guardarPeriodo()">Crear Período</button>
        </div>
    </div>
</dialog>

<!-- ==============================================
     MODAL: Confirmar Cerrar / Reactivar
     ============================================== -->
<dialog class="modal-base" id="modal-toggle-periodo">
    <div class="modal-base__container" style="max-width: 480px;">
        <div class="modal-base__header">
            <h2 class="modal-base__title" id="modal-toggle-title">¿Cerrar Período?</h2>
            <button class="modal-base__close" onclick="window.modalManager.close('modal-toggle-periodo')" aria-label="Cerrar modal">&times;</button>
        </div>
        <div class="modal-base__body">
            <p id="modal-toggle-desc" style="font-size:14px; color:var(--gray-600); line-height:1.5;"></p>
        </div>
        <div class="modal-base__footer">
            <button class="modal-btn modal-btn-cancel" onclick="window.modalManager.close('modal-toggle-periodo')">Cancelar</button>
            <button class="modal-btn" id="btn-confirmar-toggle" onclick="confirmarToggle()">Confirmar</button>
        </div>
    </div>
</dialog>

<script>
const CSRF_TOKEN = '<?= \App\Core\Csrf::getToken() ?>';
const BASE_URL = '<?= base_url('') ?>';

let editingId = null;
let toggleId = null;
let toggleAccion = null;

// ── Abrir modal crear ──
function openCrearPeriodo() {
    editingId = null;
    document.getElementById('periodo_id').value = '';
    document.getElementById('periodo_codigo').value = '';
    document.getElementById('periodo_fecha_inicio').value = '';
    document.getElementById('periodo_fecha_fin').value = '';
    document.getElementById('modal-periodo-title').textContent = 'Crear Período Académico';
    document.getElementById('modal-periodo-desc').textContent = 'Defina el código del período y su rango de fechas. Se creará como activo.';
    document.getElementById('btn-guardar-periodo').textContent = 'Crear Período';
    window.modalManager.clearError('modal-periodo');
    window.modalManager.open('modal-periodo');
}

// ── Abrir modal editar ──
function openEditarPeriodo(btn) {
    editingId = btn.dataset.id;
    document.getElementById('periodo_id').value = editingId;
    document.getElementById('periodo_codigo').value = btn.dataset.codigo || '';
    document.getElementById('periodo_fecha_inicio').value = btn.dataset.fechaInicio || '';
    document.getElementById('periodo_fecha_fin').value = btn.dataset.fechaFin || '';
    document.getElementById('modal-periodo-title').textContent = 'Editar Período';
    document.getElementById('modal-periodo-desc').textContent = 'Modifique los datos del período y guarde los cambios.';
    document.getElementById('btn-guardar-periodo').textContent = 'Guardar Cambios';
    window.modalManager.clearError('modal-periodo');
    window.modalManager.open('modal-periodo');
}

// ── AJAX: Guardar período (crear o editar) ──
async function guardarPeriodo() {
    const btn = document.getElementById('btn-guardar-periodo');
    window.modalManager.clearError('modal-periodo');
    window.modalManager.setButtonLoading(btn);

    const nombre = document.getElementById('periodo_codigo').value.trim();
    const fechaInicio = document.getElementById('periodo_fecha_inicio').value;
    const fechaFin = document.getElementById('periodo_fecha_fin').value;

    // Validación client-side
    if (!nombre) {
        window.modalManager.showError('modal-periodo', 'El código del período es obligatorio.');
        window.modalManager.resetButtonLoading(btn);
        return;
    }
    if (!fechaInicio || !fechaFin) {
        window.modalManager.showError('modal-periodo', 'Ambas fechas son obligatorias.');
        window.modalManager.resetButtonLoading(btn);
        return;
    }
    if (fechaInicio >= fechaFin) {
        window.modalManager.showError('modal-periodo', 'La fecha de inicio debe ser anterior a la fecha de fin.');
        window.modalManager.resetButtonLoading(btn);
        return;
    }

    try {
        const params = {
            csrf_token:    CSRF_TOKEN,
            nombre:        nombre,
            fecha_inicio:  fechaInicio,
            fecha_fin:     fechaFin
        };

        let url = BASE_URL + '/admin/periodos/crear';

        if (editingId) {
            params.id = editingId;
            url = BASE_URL + '/admin/periodos/actualizar';
        }

        const res = await fetch(url, {
            method: 'POST',
            body: new URLSearchParams(params)
        });

        if (res.redirected || !res.ok) {
            window.modalManager.showError('modal-periodo', 'Sesión expirada. Recargue la página.');
            window.modalManager.resetButtonLoading(btn);
            return;
        }

        const data = await res.json();

        if (data.success) {
            window.modalManager.close('modal-periodo');
            if (window.showToast) window.showToast(data.message, 'success');
            setTimeout(() => location.reload(), 800);
        } else {
            window.modalManager.showError('modal-periodo', data.message || 'Error al guardar el período.');
        }
    } catch (err) {
        console.error(err);
        window.modalManager.showError('modal-periodo', 'No se pudo conectar con el servidor.');
    } finally {
        window.modalManager.resetButtonLoading(btn);
    }
}

// ── Abrir modal toggle (cerrar/reactivar) ──
function openTogglePeriodo(id, accion, nombre) {
    toggleId = id;
    toggleAccion = accion;

    const btn = document.getElementById('btn-confirmar-toggle');

    if (accion === 'cerrar') {
        document.getElementById('modal-toggle-title').textContent = '¿Cerrar Período?';
        document.getElementById('modal-toggle-desc').innerHTML =
            'Al cerrar el período <strong>' + nombre + '</strong>, no se podrán crear nuevas secciones ni asignaciones. ' +
            'Los estudiantes con trabajo en curso <strong>no serán afectados</strong>. Esta acción es reversible.';
        btn.textContent = 'Cerrar Período';
        btn.style.background = 'var(--red-500)';
        btn.style.color = 'white';
    } else {
        document.getElementById('modal-toggle-title').textContent = '¿Reactivar Período?';
        document.getElementById('modal-toggle-desc').innerHTML =
            'Al reactivar <strong>' + nombre + '</strong>, se convertirá en el período activo del sistema. ' +
            'Si existe otro período activo, será cerrado automáticamente.';
        btn.textContent = 'Reactivar Período';
        btn.style.background = 'var(--green-500)';
        btn.style.color = 'white';
    }

    window.modalManager.clearError('modal-toggle-periodo');
    window.modalManager.open('modal-toggle-periodo');
}

// ── AJAX: Confirmar toggle ──
async function confirmarToggle() {
    const btn = document.getElementById('btn-confirmar-toggle');
    window.modalManager.setButtonLoading(btn);

    try {
        const res = await fetch(BASE_URL + '/admin/periodos/toggle', {
            method: 'POST',
            body: new URLSearchParams({
                csrf_token: CSRF_TOKEN,
                id: toggleId,
                accion: toggleAccion
            })
        });

        const data = await res.json();

        if (data.success) {
            window.modalManager.close('modal-toggle-periodo');
            if (window.showToast) window.showToast(data.message, 'success');
            setTimeout(() => location.reload(), 800);
        } else {
            window.modalManager.showError('modal-toggle-periodo', data.message || 'Error al cambiar el estado.');
            window.modalManager.resetButtonLoading(btn);
        }
    } catch (err) {
        console.error(err);
        window.modalManager.showError('modal-toggle-periodo', 'Error de conexión con el servidor.');
        window.modalManager.resetButtonLoading(btn);
    }
}
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/logged_layout.php';
?>