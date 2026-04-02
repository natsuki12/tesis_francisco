<?php
declare(strict_types=1);

$pageTitle = 'Marco Legal';
$activePage = 'marco-legal';
$breadcrumbs = [
    'Inicio' => base_url('/admin'),
    'Configuración' => '#',
    'Marco Legal' => '#'
];

$extraCss = '
    <link rel="stylesheet" href="' . asset('css/shared/data-table.css') . '">
';
$extraJs = '<script src="' . asset('js/global/data_table_core.js') . '"></script>';

// Datos inyectados por el controlador
$articulosMarcoLegal = $articulosMarcoLegal ?? [];

// Mapa de tipos legibles
$tipoLabels = [
    'Ley' => 'Ley',
    'Codigo' => 'Código',
    'Providencia' => 'Providencia',
    'Gaceta_Oficial' => 'Gaceta Oficial',
    'Reglamento' => 'Reglamento',
];

// Colores por tipo
$tipoColors = [
    'Ley' => 'background:var(--blue-50); color:var(--blue-600);',
    'Codigo' => 'background:var(--green-50); color:var(--green-600);',
    'Providencia' => 'background:var(--amber-50); color:var(--amber-600);',
    'Gaceta_Oficial' => 'background:var(--purple-50); color:var(--purple-600);',
    'Reglamento' => 'background:var(--red-50); color:var(--red-600);',
];

ob_start();
?>

<div class="page-header">
    <div class="page-header-left">
        <h1>Marco Legal Vigente</h1>
        <p>Gestione los artículos y estatutos técnicos aplicables al proceso sucesoral SENIAT.</p>
    </div>
    <button class="btn btn-primary" onclick="abrirCrear()">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
            <line x1="12" y1="5" x2="12" y2="19" />
            <line x1="5" y1="12" x2="19" y2="12" />
        </svg>
        Registrar Artículo
    </button>
</div>

<!-- Toolbar -->
<div class="toolbar">
    <div class="toolbar-left">
        <div class="search-box">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            <input type="text" data-search-for="tbl-marco" placeholder="Buscar artículo, tipo o descripción...">
        </div>
        <button class="btn btn-secondary" data-reload-for="tbl-marco" onclick="window.DataTableManager.reloadTableData('tbl-marco');" title="Recargar tabla" style="padding: 10px; border-radius: 8px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="transform-origin: center;">
                <polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline>
                <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
            </svg>
        </button>
    </div>
    <div class="toolbar-right">
        <label style="font-size:var(--text-xs); color:var(--gray-500); display:flex; align-items:center; gap:6px;">
            Mostrar <select data-perpage-for="tbl-marco" class="per-page-select"><option value="10" selected>10</option><option value="15">15</option><option value="25">25</option><option value="50">50</option></select> filas
        </label>
    </div>
</div>

<!-- Data Table -->
<div class="table-container">
    <table class="data-table" id="tbl-marco">
        <thead>
            <tr>
                <th class="sortable" data-col="0" style="width:60px">Orden</th>
                <th class="sortable" data-col="1" style="width:22%">Título</th>
                <th class="sortable" data-col="2" style="width:10%">Tipo</th>
                <th style="width:35%">Descripción</th>
                <th class="sortable" data-col="4" style="width:10%">Estado</th>
                <th class="sortable" data-col="5" style="width:10%">Fecha Pub.</th>
                <th style="width:90px">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($articulosMarcoLegal as $art):
                $tipo = $art['tipo'] ?? 'Ley';
                $tipoLabel = $tipoLabels[$tipo] ?? ucfirst($tipo);
                $tipoStyle = $tipoColors[$tipo] ?? $tipoColors['Ley'];
                $estado = $art['estado'] ?? 'Vigente';

                $fechaFmt = '—';
                if (!empty($art['fecha_publicacion'])) {
                    try {
                        $fechaFmt = (new \DateTime($art['fecha_publicacion']))->format('d/m/Y');
                    } catch (\Throwable $e) {
                        $fechaFmt = e($art['fecha_publicacion']);
                    }
                }

                $searchText = mb_strtolower(
                    ($art['titulo'] ?? '') . ' ' .
                    $tipoLabel . ' ' .
                    ($art['descripcion'] ?? '') . ' ' .
                    $estado . ' ' .
                    ($art['numero_gaceta'] ?? '')
                );
            ?>
                <tr data-search="<?= e($searchText) ?>">
                    <td style="font-weight:600; color:var(--gray-500); text-align:center;"><?= (int)($art['orden'] ?? 0) ?></td>
                    <td>
                        <strong><?= e($art['titulo'] ?? '') ?></strong>
                        <?php if (!empty($art['numero_gaceta'])): ?>
                            <br><span style="font-size:11px; color:var(--gray-400);">G.O. <?= e($art['numero_gaceta']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td><span class="status-badge" style="<?= $tipoStyle ?>"><?= e($tipoLabel) ?></span></td>
                    <td>
                        <p style="margin:0; font-size:13px; color:var(--text-light); line-height:1.4; max-width:400px; overflow:hidden; text-overflow:ellipsis; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical;">
                            <?= e($art['descripcion'] ?? '') ?>
                        </p>
                    </td>
                    <td>
                        <?php if ($estado === 'Vigente'): ?>
                            <span class="status-badge status-published">Vigente</span>
                        <?php else: ?>
                            <span class="status-badge status-draft">Derogado</span>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:12px; color:var(--gray-500);"><?= $fechaFmt ?></td>
                    <td>
                        <div class="row-actions">
                            <button class="row-action-btn" title="Editar"
                                onclick="abrirEditar(this)"
                                data-id="<?= (int)$art['id'] ?>"
                                data-titulo="<?= e($art['titulo'] ?? '') ?>"
                                data-tipo="<?= e($tipo) ?>"
                                data-descripcion="<?= e($art['descripcion'] ?? '') ?>"
                                data-url="<?= e($art['url'] ?? '') ?>"
                                data-estado="<?= e($estado) ?>"
                                data-orden="<?= (int)($art['orden'] ?? 0) ?>"
                                data-fecha="<?= e($art['fecha_publicacion'] ?? '') ?>"
                                data-gaceta="<?= e($art['numero_gaceta'] ?? '') ?>">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                                </svg>
                            </button>
                            <button class="row-action-btn" title="Eliminar"
                                onclick="abrirEliminar(<?= (int)$art['id'] ?>, '<?= e(addslashes($art['titulo'] ?? '')) ?>')"
                                style="color:var(--red-500);">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="3 6 5 6 21 6" />
                                    <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" />
                                    <path d="M10 11v6" /><path d="M14 11v6" />
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div class="table-footer" data-footer-for="tbl-marco">
        <div class="table-footer-info"></div>
        <div class="pagination"></div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════
     MODAL: Crear / Editar Artículo
     ═══════════════════════════════════════════════════════════ -->
<dialog class="modal-base" id="modal-articulo" data-no-backdrop-close>
    <div class="modal-base__container" style="max-width: 640px;">
        <div class="modal-base__header">
            <h2 class="modal-base__title" id="modal-articulo-title">Registrar Artículo</h2>
            <button class="modal-base__close" onclick="window.modalManager.close('modal-articulo')" aria-label="Cerrar modal">&times;</button>
        </div>
        <div class="modal-base__body">
            <p style="font-size: 15px; color: var(--text-body); margin-bottom: 20px;">Complete los datos del instrumento legal.</p>

            <div class="form-grid">
                <input type="hidden" id="art-id" value="">

                <!-- Título -->
                <div class="form-group form-full">
                    <label>Título <span class="required">*</span></label>
                    <input type="text" id="art-titulo" placeholder="Ej: L.I.S.S.D Art. 52" maxlength="255">
                </div>

                <!-- Tipo -->
                <div class="form-group">
                    <label>Tipo <span class="required">*</span></label>
                    <select id="art-tipo">
                        <option value="Ley">Ley</option>
                        <option value="Codigo">Código</option>
                        <option value="Providencia">Providencia</option>
                        <option value="Gaceta_Oficial">Gaceta Oficial</option>
                        <option value="Reglamento">Reglamento</option>
                    </select>
                </div>

                <!-- Estado -->
                <div class="form-group">
                    <label>Estado <span class="required">*</span></label>
                    <select id="art-estado">
                        <option value="Vigente">Vigente</option>
                        <option value="Derogado">Derogado</option>
                    </select>
                </div>

                <!-- Descripción -->
                <div class="form-group form-full">
                    <label>Descripción / Extracto <span class="required">*</span></label>
                    <textarea id="art-descripcion" rows="4" placeholder="Texto del artículo o extracto relevante..."></textarea>
                </div>

                <!-- Fecha Publicación -->
                <div class="form-group">
                    <label>Fecha de Publicación</label>
                    <input type="date" id="art-fecha" max="<?= date('Y-m-d') ?>" onkeydown="return false">
                </div>

                <!-- Número de Gaceta -->
                <div class="form-group">
                    <label>Número de Gaceta</label>
                    <input type="text" id="art-gaceta" placeholder="Ej: 6.507 Extraordinario" maxlength="100">
                </div>

                <!-- URL -->
                <div class="form-group form-full">
                    <label>URL de Referencia</label>
                    <input type="url" id="art-url" placeholder="https://..." maxlength="500">
                </div>

                <!-- Orden -->
                <div class="form-group">
                    <label>Orden de Visualización</label>
                    <input type="number" id="art-orden" min="0" max="999" value="0">
                </div>
            </div>
        </div>

        <div class="modal-base__footer">
            <button class="modal-btn modal-btn-cancel" onclick="window.modalManager.close('modal-articulo')">Cancelar</button>
            <button class="modal-btn modal-btn-primary" id="btn-guardar-articulo" onclick="guardarArticulo()">Guardar Artículo</button>
        </div>
    </div>
</dialog>

<!-- ═══════════════════════════════════════════════════════════
     MODAL: Confirmar Eliminación
     ═══════════════════════════════════════════════════════════ -->
<dialog class="modal-base" id="modal-eliminar">
    <div class="modal-base__container" style="max-width: 480px;">
        <div class="modal-base__header">
            <h2 class="modal-base__title">¿Eliminar artículo?</h2>
            <button class="modal-base__close" onclick="window.modalManager.close('modal-eliminar')" aria-label="Cerrar modal">&times;</button>
        </div>
        <div class="modal-base__body">
            <p style="font-size: 15px; color: var(--text-body); line-height: 1.5; margin-bottom: 0;" id="eliminar-body-text">
                Se eliminará permanentemente el artículo <strong id="eliminar-nombre"></strong> del marco legal. Esta acción no puede deshacerse.
            </p>
        </div>
        <div class="modal-base__footer" style="padding-top: 24px;">
            <button class="modal-btn modal-btn-cancel" style="min-width: 120px;" onclick="window.modalManager.close('modal-eliminar')">Cancelar</button>
            <button class="modal-btn modal-btn-danger" id="btn-confirmar-eliminar" style="min-width: 120px;" onclick="confirmarEliminar()">Eliminar</button>
        </div>
    </div>
</dialog>

<script>
const CSRF_TOKEN = '<?= \App\Core\Csrf::getToken() ?>';
const BASE_URL   = '<?= base_url() ?>';

let eliminarId = 0;

// ── Abrir modal CREAR ──
function abrirCrear() {
    document.getElementById('art-id').value = '';
    document.getElementById('art-titulo').value = '';
    document.getElementById('art-tipo').value = 'Ley';
    document.getElementById('art-estado').value = 'Vigente';
    document.getElementById('art-descripcion').value = '';
    document.getElementById('art-fecha').value = '';
    document.getElementById('art-gaceta').value = '';
    document.getElementById('art-url').value = '';
    document.getElementById('art-orden').value = '0';
    document.getElementById('modal-articulo-title').textContent = 'Registrar Artículo';

    window.modalManager.clearError('modal-articulo');

    const btn = document.getElementById('btn-guardar-articulo');
    btn.textContent = 'Guardar Artículo';
    btn.style.display = '';

    window.modalManager.open('modal-articulo');
}

// ── Abrir modal EDITAR ──
function abrirEditar(btnEl) {
    document.getElementById('art-id').value = btnEl.dataset.id || '';
    document.getElementById('art-titulo').value = btnEl.dataset.titulo || '';
    document.getElementById('art-tipo').value = btnEl.dataset.tipo || 'Ley';
    document.getElementById('art-estado').value = btnEl.dataset.estado || 'Vigente';
    document.getElementById('art-descripcion').value = btnEl.dataset.descripcion || '';
    document.getElementById('art-fecha').value = btnEl.dataset.fecha || '';
    document.getElementById('art-gaceta').value = btnEl.dataset.gaceta || '';
    document.getElementById('art-url').value = btnEl.dataset.url || '';
    document.getElementById('art-orden').value = btnEl.dataset.orden || '0';
    document.getElementById('modal-articulo-title').textContent = 'Editar Artículo';

    window.modalManager.clearError('modal-articulo');

    const btn = document.getElementById('btn-guardar-articulo');
    btn.textContent = 'Actualizar Artículo';
    btn.style.display = '';

    window.modalManager.open('modal-articulo');
}

// ── AJAX: Guardar (crear/editar) ──
async function guardarArticulo() {
    const btn = document.getElementById('btn-guardar-articulo');
    const artId = document.getElementById('art-id').value;
    const isUpdate = artId !== '';

    window.modalManager.setButtonLoading(btn);

    const body = new URLSearchParams({
        csrf_token:        CSRF_TOKEN,
        titulo:            document.getElementById('art-titulo').value.trim(),
        tipo:              document.getElementById('art-tipo').value,
        estado:            document.getElementById('art-estado').value,
        descripcion:       document.getElementById('art-descripcion').value.trim(),
        fecha_publicacion: document.getElementById('art-fecha').value,
        numero_gaceta:     document.getElementById('art-gaceta').value.trim(),
        url:               document.getElementById('art-url').value.trim(),
        orden:             document.getElementById('art-orden').value,
    });

    if (isUpdate) body.append('id', artId);

    try {
        const res = await fetch(BASE_URL + '/admin/configuracion/marco-legal/guardar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body
        });
        const data = await res.json();

        if (data.success) {
            window.modalManager.close('modal-articulo');
            showToast(data.message, 'success');
            window.DataTableManager.reloadTableData('tbl-marco');
        } else {
            window.modalManager.showError('modal-articulo', data.message || 'Ocurrió un error.');
        }
    } catch (err) {
        console.error(err);
        window.modalManager.showError('modal-articulo', 'No se pudo conectar con el servidor.');
    } finally {
        window.modalManager.resetButtonLoading(btn);
    }
}

// ── Abrir modal ELIMINAR ──
function abrirEliminar(id, titulo) {
    eliminarId = id;
    document.getElementById('eliminar-nombre').textContent = titulo;
    window.modalManager.open('modal-eliminar');
}

// ── AJAX: Confirmar eliminación ──
async function confirmarEliminar() {
    const btn = document.getElementById('btn-confirmar-eliminar');
    btn.disabled = true;

    try {
        const res = await fetch(BASE_URL + '/admin/configuracion/marco-legal/eliminar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ csrf_token: CSRF_TOKEN, id: eliminarId })
        });
        const data = await res.json();

        window.modalManager.close('modal-eliminar');
        if (data.success) {
            showToast(data.message, 'success');
            window.DataTableManager.reloadTableData('tbl-marco');
        } else {
            showToast(data.message || 'Error al eliminar.', 'error');
        }
    } catch (err) {
        console.error(err);
        showToast('Error de conexión.', 'error');
    } finally {
        btn.disabled = false;
    }
}
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/logged_layout.php';
?>