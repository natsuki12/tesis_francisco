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
    <link rel="stylesheet" href="' . asset('css/admin/dashboard.css') . '">
';

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
    'Ley' => 'background:#eff6ff; color:#1d4ed8;',
    'Codigo' => 'background:#f0fdf4; color:#15803d;',
    'Providencia' => 'background:#fefce8; color:#a16207;',
    'Gaceta_Oficial' => 'background:#faf5ff; color:#7e22ce;',
    'Reglamento' => 'background:#fff1f2; color:#be123c;',
];

ob_start();
?>

<div class="page-header">
    <div class="page-header-left">
        <h1>Marco Legal Vigente</h1>
        <p>Artículos y estatutos técnicos aplicables al proceso sucesoral SENIAT (solo lectura).</p>
    </div>
    <button class="btn btn-primary" onclick="document.getElementById('modal-articulo').classList.add('show')">
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
            <?php if (empty($articulosMarcoLegal)): ?>
                <tr class="empty-row"><td colspan="7" style="text-align:center; padding:40px; color:var(--gray-400);">No se encontraron registros en el marco legal.</td></tr>
            <?php else: ?>
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
                            <p style="margin:0; font-size:13px; color:var(--color-text-light); line-height:1.4; max-width:400px; overflow:hidden; text-overflow:ellipsis; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical;">
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
                                <button class="row-action-btn" title="Editar" onclick="alert('Edición diferida — solo lectura.')">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                                    </svg>
                                </button>
                                <button class="row-action-btn" title="Eliminar" onclick="alert('Eliminación diferida — solo lectura.')" style="color:var(--red-500);">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="3 6 5 6 21 6" />
                                        <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" />
                                        <path d="M10 11v6" />
                                        <path d="M14 11v6" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    <div class="table-footer" data-footer-for="tbl-marco">
        <div class="table-footer-info"></div>
        <div class="pagination"></div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════
     MODAL: Registrar Artículo
     ═══════════════════════════════════════════════════════════ -->
<div id="modal-articulo" class="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <div>
                <h2>Registrar Artículo</h2>
                <p>Agregue un nuevo instrumento al marco legal del sistema.</p>
            </div>
            <button class="modal-close" onclick="document.getElementById('modal-articulo').classList.remove('show')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="modal-body">
            <div class="form-grid">
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

        <div class="modal-footer">
            <button class="btn" style="background:var(--gray-100); color:var(--gray-600); padding:10px 20px;"
                    onclick="document.getElementById('modal-articulo').classList.remove('show')">
                Cancelar
            </button>
            <button class="btn btn-primary" style="padding:10px 24px;"
                    onclick="alert('Guardado diferido — solo lectura por ahora.'); document.getElementById('modal-articulo').classList.remove('show');">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                    <polyline points="17 21 17 13 7 13 7 21"/>
                    <polyline points="7 3 7 8 15 8"/>
                </svg>
                Guardar Artículo
            </button>
        </div>
    </div>
</div>

<script>
// ── Modal: cerrar con click fuera ──
document.getElementById('modal-articulo')?.addEventListener('click', function(e) {
    if (e.target === this) this.classList.remove('show');
});
// ── Modal: cerrar con Escape ──
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') document.getElementById('modal-articulo')?.classList.remove('show');
});
</script>

<script>
(function() {
    const table = document.getElementById('tbl-marco');
    if (!table) return;
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr[data-search]'));
    if (rows.length === 0) return;

    const searchInput = document.querySelector('[data-search-for="tbl-marco"]');
    const perPageSel = document.querySelector('[data-perpage-for="tbl-marco"]');
    const footer = document.querySelector('[data-footer-for="tbl-marco"]');
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