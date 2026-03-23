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
$secciones  = $secciones  ?? [];
$periodos   = $periodos   ?? [];
$profesores = $profesores ?? [];

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
                <th class="sortable" data-col="0" style="width:50px">ID</th>
                <th class="sortable" data-col="1">Sección</th>
                <th class="sortable" data-col="2">Período</th>
                <th class="sortable" data-col="3">Profesor Asignado</th>
                <th class="sortable" data-col="4" style="width:120px">Inscritos / Cupo</th>
                <th class="sortable" data-col="5" style="width:100px">Estado</th>
                <th style="width:90px">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($secciones)): ?>
                <tr class="empty-row"><td colspan="7" style="text-align:center; padding:40px; color:var(--gray-400);">No se encontraron secciones registradas.</td></tr>
            <?php else: ?>
                <?php foreach ($secciones as $sec):
                    $nombre     = $sec['nombre'] ?? '';
                    $periodo    = $sec['periodo'] ?? '—';
                    $periodoAct = (int)($sec['periodo_activo'] ?? 0);
                    $profesor   = $sec['profesor_nombre'] ?? '—';
                    $genero     = $sec['profesor_genero'] ?? '';
                    $inscritos  = (int)($sec['inscritos'] ?? 0);
                    $cupo       = (int)($sec['cupo_maximo'] ?? 40);
                    $pct        = $cupo > 0 ? round($inscritos / $cupo * 100) : 0;

                    // Initials for avatar
                    $parts = explode(' ', $profesor);
                    $initials = '';
                    foreach ($parts as $p) { if ($p !== '') $initials .= mb_strtoupper(mb_substr($p, 0, 1)); }
                    $initials = mb_substr($initials, 0, 2);
                    $avatarClass = ($genero === 'F') ? 'f' : 'm';

                    // Estado
                    $estado = $periodoAct ? 'Abierta' : 'Cerrada';

                    $searchText = mb_strtolower($nombre . ' ' . $periodo . ' ' . $profesor);
                ?>
                    <tr data-search="<?= e($searchText) ?>">
                        <td style="color:var(--gray-400); font-size:12px;"><?= (int)$sec['id'] ?></td>
                        <td><strong><?= e($nombre) ?></strong></td>
                        <td>
                            <?= e($periodo) ?>
                            <?php if ($periodoAct): ?>
                                <span style="display:inline-block; width:6px; height:6px; background:var(--green-500); border-radius:50%; margin-left:6px; vertical-align:middle;" title="Período activo"></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div style="display:flex; align-items:center; gap:8px;">
                                <div class="causante-avatar <?= $avatarClass ?>" style="width:28px;height:28px;font-size:10px;"><?= e($initials) ?></div>
                                <span><?= e($profesor) ?></span>
                            </div>
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
                                    data-cupo="<?= $cupo ?>"
                                    data-periodo-id="<?= (int)($sec['periodo_id'] ?? 0) ?>"
                                    data-profesor-id="<?= (int)($sec['profesor_id'] ?? 0) ?>">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                                    </svg>
                                </button>
                                <button class="row-action-btn" title="Cerrar Sección"
                                    onclick="openCerrarSeccion(<?= (int)$sec['id'] ?>)" style="color:var(--red-500);">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"/>
                                        <line x1="15" y1="9" x2="9" y2="15"/>
                                        <line x1="9" y1="9" x2="15" y2="15"/>
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
     MODALES 
     ============================================== -->

<!-- Modal: Crear/Editar Sección -->
<div id="modal-seccion" class="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <div>
                <h2 id="modal-seccion-title">Crear Sección</h2>
                <p>Defina el nombre, período, profesor y cupo de la sección.</p>
            </div>
            <button class="modal-close" onclick="document.getElementById('modal-seccion').classList.remove('show')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="form-grid">
                <input type="hidden" id="seccion_id" value="">

                <div class="form-group">
                    <label>Nombre de Sección <span class="required">*</span></label>
                    <input type="text" id="seccion_nombre" placeholder="Ej: 1A" maxlength="20">
                </div>

                <div class="form-group">
                    <label>Cupo Máximo <span class="required">*</span></label>
                    <input type="number" id="seccion_cupo" placeholder="Ej: 40" min="1" max="999" value="40">
                </div>

                <div class="form-group">
                    <label>Período Académico <span class="required">*</span></label>
                    <select id="seccion_periodo">
                        <option value="">— Seleccione un período —</option>
                        <?php foreach ($periodos as $per): ?>
                            <option value="<?= (int)$per['id'] ?>">
                                <?= e($per['nombre']) ?><?= (int)$per['activo'] ? ' (Activo)' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Profesor Asignado <span class="required">*</span></label>
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
        <div class="modal-footer">
            <button class="btn" style="background:var(--gray-100); color:var(--gray-600); padding:10px 20px;"
                    onclick="document.getElementById('modal-seccion').classList.remove('show')">
                Cancelar
            </button>
            <button class="btn btn-primary" style="padding:10px 24px;"
                    onclick="alert('Guardado diferido — solo lectura por ahora.'); document.getElementById('modal-seccion').classList.remove('show');">
                Guardar Sección
            </button>
        </div>
    </div>
</div>

<!-- Modal: Confirmar Cierre -->
<div id="modal-cerrar" class="modal-overlay">
    <div class="modal" style="max-width:480px;">
        <div class="modal-header">
            <div>
                <h2>¿Cerrar sección?</h2>
                <p>Los estudiantes inscritos no podrán continuar trabajando en sus casos.</p>
            </div>
            <button class="modal-close" onclick="document.getElementById('modal-cerrar').classList.remove('show')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <p style="font-size:14px; color:var(--gray-600); line-height:1.5;">
                Al cerrar esta sección, <strong>los estudiantes inscritos no podrán continuar
                trabajando en sus casos</strong> hasta que la sección sea reactivada. Los datos
                y el progreso no serán eliminados.
            </p>
        </div>
        <div class="modal-footer">
            <button class="btn" style="background:var(--gray-100); color:var(--gray-600); padding:10px 20px;"
                    onclick="document.getElementById('modal-cerrar').classList.remove('show')">
                Cancelar
            </button>
            <button class="btn" style="background:var(--red-500); color:white; padding:10px 24px;"
                    onclick="alert('Cierre diferido — solo lectura por ahora.'); document.getElementById('modal-cerrar').classList.remove('show');">
                Cerrar Sección
            </button>
        </div>
    </div>
</div>

<script>
// ── Modal helpers ──
function openCrearSeccion() {
    document.getElementById('seccion_id').value = '';
    document.getElementById('seccion_nombre').value = '';
    document.getElementById('seccion_cupo').value = '40';
    document.getElementById('seccion_periodo').value = '';
    document.getElementById('seccion_profesor').value = '';
    document.getElementById('modal-seccion-title').textContent = 'Crear Sección';
    document.getElementById('modal-seccion').classList.add('show');
}

function openEditarSeccion(btn) {
    document.getElementById('seccion_id').value = btn.dataset.id || '';
    document.getElementById('seccion_nombre').value = btn.dataset.nombre || '';
    document.getElementById('seccion_cupo').value = btn.dataset.cupo || '40';
    document.getElementById('seccion_periodo').value = btn.dataset.periodoId || '';
    document.getElementById('seccion_profesor').value = btn.dataset.profesorId || '';
    document.getElementById('modal-seccion-title').textContent = 'Editar Sección';
    document.getElementById('modal-seccion').classList.add('show');
}

function openCerrarSeccion(id) {
    document.getElementById('modal-cerrar').classList.add('show');
}

// ── Close modals on click outside / Escape ──
['modal-seccion', 'modal-cerrar'].forEach(id => {
    document.getElementById(id)?.addEventListener('click', function(e) {
        if (e.target === this) this.classList.remove('show');
    });
});
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        document.getElementById('modal-seccion')?.classList.remove('show');
        document.getElementById('modal-cerrar')?.classList.remove('show');
    }
});

// ── DataTable Engine ──
(function() {
    const table = document.getElementById('tbl-secciones');
    if (!table) return;
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr[data-search]'));
    if (rows.length === 0) return;

    const searchInput = document.querySelector('[data-search-for="tbl-secciones"]');
    const perPageSel = document.querySelector('[data-perpage-for="tbl-secciones"]');
    const footer = document.querySelector('[data-footer-for="tbl-secciones"]');
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