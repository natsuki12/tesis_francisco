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
                                    onclick="openCerrarPeriodo(<?= (int)$per['id'] ?>)" style="color:var(--red-500);">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M18.36 6.64a9 9 0 1 1-12.73 0" />
                                        <line x1="12" y1="2" x2="12" y2="12" />
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
     MODALES
     ============================================== -->

<!-- Modal: Crear/Editar Período -->
<div id="modal-periodo" class="modal-overlay">
    <div class="modal" style="max-width:500px;">
        <div class="modal-header">
            <div>
                <h2 id="modal-periodo-title">Crear Período Académico</h2>
                <p>Defina el código del período y su rango de fechas.</p>
            </div>
            <button class="modal-close" onclick="document.getElementById('modal-periodo').classList.remove('show')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="form-grid">
                <input type="hidden" id="periodo_id" value="">

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
        <div class="modal-footer">
            <button class="btn" style="background:var(--gray-100); color:var(--gray-600); padding:10px 20px;"
                    onclick="document.getElementById('modal-periodo').classList.remove('show')">
                Cancelar
            </button>
            <button class="btn btn-primary" style="padding:10px 24px;"
                    onclick="alert('Guardado diferido — solo lectura por ahora.'); document.getElementById('modal-periodo').classList.remove('show');">
                Guardar Período
            </button>
        </div>
    </div>
</div>

<!-- Modal: Confirmar Cierre -->
<div id="modal-cerrar-periodo" class="modal-overlay">
    <div class="modal" style="max-width:480px;">
        <div class="modal-header">
            <div>
                <h2>¿Cerrar Período?</h2>
                <p>Las secciones asociadas pasarán a estado inactivo.</p>
            </div>
            <button class="modal-close" onclick="document.getElementById('modal-cerrar-periodo').classList.remove('show')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <p style="font-size:14px; color:var(--gray-600); line-height:1.5;">
                Al cerrar este período, <strong>todas las secciones asociadas pasarán a estado inactivo</strong>
                y los estudiantes no podrán continuar trabajando en sus casos asignados. Esta acción
                puede ser revertida posteriormente reactivando el período.
            </p>
        </div>
        <div class="modal-footer">
            <button class="btn" style="background:var(--gray-100); color:var(--gray-600); padding:10px 20px;"
                    onclick="document.getElementById('modal-cerrar-periodo').classList.remove('show')">
                Cancelar
            </button>
            <button class="btn" style="background:var(--red-500); color:white; padding:10px 24px;"
                    onclick="alert('Cierre diferido — solo lectura por ahora.'); document.getElementById('modal-cerrar-periodo').classList.remove('show');">
                Cerrar Período
            </button>
        </div>
    </div>
</div>

<script>
// ── Modal helpers ──
function openCrearPeriodo() {
    document.getElementById('periodo_id').value = '';
    document.getElementById('periodo_codigo').value = '';
    document.getElementById('periodo_fecha_inicio').value = '';
    document.getElementById('periodo_fecha_fin').value = '';
    document.getElementById('modal-periodo-title').textContent = 'Crear Período Académico';
    document.getElementById('modal-periodo').classList.add('show');
}

function openEditarPeriodo(btn) {
    document.getElementById('periodo_id').value = btn.dataset.id || '';
    document.getElementById('periodo_codigo').value = btn.dataset.codigo || '';
    document.getElementById('periodo_fecha_inicio').value = btn.dataset.fechaInicio || '';
    document.getElementById('periodo_fecha_fin').value = btn.dataset.fechaFin || '';
    document.getElementById('modal-periodo-title').textContent = 'Editar Período';
    document.getElementById('modal-periodo').classList.add('show');
}

function openCerrarPeriodo(id) {
    document.getElementById('modal-cerrar-periodo').classList.add('show');
}

// ── Close modals on click outside / Escape ──
['modal-periodo', 'modal-cerrar-periodo'].forEach(id => {
    document.getElementById(id)?.addEventListener('click', function(e) {
        if (e.target === this) this.classList.remove('show');
    });
});
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        document.getElementById('modal-periodo')?.classList.remove('show');
        document.getElementById('modal-cerrar-periodo')?.classList.remove('show');
    }
});

// ── DataTable Engine ──
(function() {
    const table = document.getElementById('tbl-periodos');
    if (!table) return;
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr[data-search]'));
    if (rows.length === 0) return;

    const searchInput = document.querySelector('[data-search-for="tbl-periodos"]');
    const perPageSel = document.querySelector('[data-perpage-for="tbl-periodos"]');
    const footer = document.querySelector('[data-footer-for="tbl-periodos"]');
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