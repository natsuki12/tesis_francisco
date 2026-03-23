<?php
declare(strict_types=1);

$pageTitle = 'Bitácora de Auditoría';
$activePage = 'bitacora';
$breadcrumbs = [
    'Inicio' => base_url('/admin'),
    'Monitoreo' => '#',
    'Bitácora' => '#'
];

$extraCss = '<link rel="stylesheet" href="' . asset('css/shared/data-table.css') . '">';

// Datos inyectados por el controlador (fallback a arrays vacíos)
$eventos = $eventos ?? [];
$emails  = $emails  ?? [];
$tipos   = $tipos   ?? [];
$modulos = $modulos ?? [];

ob_start();
?>

<div class="page-header">
    <div class="page-header-left">
        <h1>Bitácora de Eventos</h1>
        <p>Registro inmutable de todas las acciones de acceso dentro de la plataforma.</p>
    </div>
    <div style="position:relative; display:inline-block;" id="export-dropdown-wrap">
        <button class="btn btn-outline" id="btn-export-toggle" style="display:inline-flex; align-items:center; gap:6px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <polyline points="7 10 12 15 17 10"/>
                <line x1="12" y1="15" x2="12" y2="3"/>
            </svg>
            Exportar
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="6 9 12 15 18 9"/></svg>
        </button>
        <div id="export-dropdown" style="display:none; position:absolute; right:0; top:calc(100% + 6px); background:var(--white); border:1px solid var(--gray-200); border-radius:var(--radius-sm); box-shadow:var(--shadow-dropdown); z-index:50; min-width:160px; overflow:hidden;">
            <button onclick="exportarCSV()" style="width:100%; padding:10px 16px; border:none; background:none; cursor:pointer; display:flex; align-items:center; gap:10px; font-size:13px; color:var(--gray-700); font-family:var(--font-ui);" onmouseover="this.style.background='var(--gray-50)'" onmouseout="this.style.background='none'">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--green-600)" stroke-width="2" stroke-linecap="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                Exportar CSV
            </button>
            <div style="border-top:1px solid var(--gray-100);"></div>
            <button onclick="exportarPDF()" style="width:100%; padding:10px 16px; border:none; background:none; cursor:pointer; display:flex; align-items:center; gap:10px; font-size:13px; color:var(--gray-700); font-family:var(--font-ui);" onmouseover="this.style.background='var(--gray-50)'" onmouseout="this.style.background='none'">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--red-500)" stroke-width="2" stroke-linecap="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                Exportar PDF
            </button>
        </div>
    </div>
</div>

<!-- Toolbar: Búsqueda + Filtros -->
<div class="toolbar">
    <div class="toolbar-left">
        <div class="search-box">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                <circle cx="11" cy="11" r="8" />
                <path d="m21 21-4.35-4.35" />
            </svg>
            <input type="text" id="search-bitacora" placeholder="Buscar por usuario, IP o descripción...">
        </div>

        <select id="filter-modulo" class="filter-select">
            <option value="Todos">Todos los módulos</option>
            <?php foreach ($modulos as $key => $label): ?>
                <option value="<?= e($key) ?>"><?= e($label) ?></option>
            <?php endforeach; ?>
        </select>

        <select id="filter-evento" class="filter-select">
            <option value="Todos">Todos los eventos</option>
            <?php foreach ($tipos as $t): ?>
                <option value="<?= e($t['descripcion']) ?>"><?= e($t['descripcion']) ?></option>
            <?php endforeach; ?>
        </select>

        <select id="filter-usuario" class="filter-select">
            <option value="Todos">Todos los usuarios</option>
            <?php foreach ($emails as $email): ?>
                <option value="<?= e($email) ?>"><?= e($email) ?></option>
            <?php endforeach; ?>
        </select>

        <div class="date-range-group">
            <div class="date-range-group__segment">
                <span class="date-range-group__label">Desde</span>
                <input type="date" id="date-from" class="date-range-group__input" title="Fecha inicio" onkeydown="return false">
            </div>
            <div class="date-range-group__segment">
                <span class="date-range-group__label">Hasta</span>
                <input type="date" id="date-to" class="date-range-group__input" title="Fecha fin" onkeydown="return false">
            </div>
            <button type="button" id="date-clear" class="date-range-group__clear" title="Limpiar fechas" style="display:none">&times;</button>
        </div>
    </div>

    <div class="toolbar-right">
        <label style="font-size:var(--text-xs, 13px); color:var(--gray-500, #64748b); display:flex; align-items:center; gap:6px;">
            Mostrar
            <select id="per-page" class="per-page-select">
                <option value="10">10</option>
                <option value="15" selected>15</option>
                <option value="25">25</option>
                <option value="50">50</option>
            </select>
            filas
        </label>
    </div>
</div>

<!-- Data Table -->
<div class="table-container">
    <table class="data-table">
        <thead>
            <tr>
                <th class="sortable" data-sort="timestamp" style="width: 14%">Timestamp</th>
                <th class="sortable" data-sort="usuario" style="width: 17%">Usuario</th>
                <th class="sortable" data-sort="modulo" style="width: 12%">Módulo</th>
                <th class="sortable" data-sort="evento" style="width: 22%">Evento</th>
                <th class="sortable" data-sort="descripcion" style="width: 22%">Detalle</th>
                <th style="width: 13%">IP</th>
            </tr>
        </thead>
        <tbody id="bitacora-tbody" style="font-size: 13px; font-family: var(--font-ui);">
            <?php if (empty($eventos)): ?>
                <tr>
                    <td colspan="6" style="text-align:center; padding:40px; color:var(--gray-400);">
                        No se encontraron eventos en la bitácora.
                    </td>
                </tr>
            <?php else: ?>
                <?php
                // Mapa de colores por nivel_riesgo
                $riskColors = [
                    'info'     => 'background:#eff6ff; color:#1d4ed8;',
                    'warning'  => 'background:#fffbeb; color:#b45309;',
                    'critical' => 'background:#fef2f2; color:#b91c1c;',
                ];
                // Mapa legible de módulos
                $moduloLabels = $modulos;
                // Mapa legible de entidades
                $entidadLabels = [
                    'users'                => 'Usuario',
                    'sim_casos_estudios'   => 'Caso',
                    'sim_intentos'         => 'Intento',
                    'sim_asignaciones'     => 'Asignación',
                    'sim_config_caso'      => 'Config',
                ];
                ?>
                <?php foreach ($eventos as $ev): ?>
                    <?php
                    $ts          = $ev['created_at'] ?? '';
                    $email       = $ev['email'] ?? 'Desconocido';
                    $evento      = $ev['evento'] ?? 'Sin tipo';
                    $riesgo      = $ev['nivel_riesgo'] ?? 'info';
                    $modulo      = $ev['modulo'] ?? 'autenticacion';
                    $entTipo     = $ev['entidad_tipo'] ?? '';
                    $entId       = $ev['entidad_id'] ?? null;
                    $detalle     = $ev['detalle'] ?? '';
                    $ip          = $ev['ip_address'] ?? '—';
                    $badgeStyle  = $riskColors[$riesgo] ?? $riskColors['info'];
                    $moduloLabel = $moduloLabels[$modulo] ?? ucfirst($modulo);

                    // Formato de timestamp legible
                    $tsFormatted = '';
                    if ($ts) {
                        try {
                            $date = new \DateTime($ts);
                            $tsFormatted = $date->format('d M Y, H:i:s');
                        } catch (\Throwable $e) {
                            $tsFormatted = e($ts);
                        }
                    }

                    // Armar texto de detalle: entidad + detalle
                    $detalleTexto = '';
                    if ($entTipo && $entId) {
                        $entLabel = $entidadLabels[$entTipo] ?? $entTipo;
                        $detalleTexto = $entLabel . ' #' . $entId;
                        if ($detalle) {
                            $detalleTexto .= ' — ' . $detalle;
                        }
                    } else {
                        $detalleTexto = $detalle ?: '—';
                    }
                    ?>
                    <tr data-timestamp="<?= e($ts) ?>"
                        data-usuario="<?= e(mb_strtolower($email)) ?>"
                        data-modulo="<?= e($modulo) ?>"
                        data-evento="<?= e(mb_strtolower($evento)) ?>"
                        data-descripcion="<?= e(mb_strtolower($detalleTexto)) ?>"
                        data-ip="<?= e(mb_strtolower($ip)) ?>">
                        <td style="color: var(--color-text-light);"><?= e($tsFormatted) ?></td>
                        <td><strong><?= e($email) ?></strong></td>
                        <td><span style="font-size:12px; color:var(--gray-500);"><?= e($moduloLabel) ?></span></td>
                        <td><span class="status-badge" style="<?= $badgeStyle ?>"><?= e($evento) ?></span></td>
                        <td><?= e($detalleTexto) ?></td>
                        <td style="color: var(--color-text-light); font-size: 12px;"><?= e($ip) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Table Footer -->
    <div class="table-footer">
        <div class="table-footer-info">
            Mostrando <strong>0</strong> de <strong>0</strong> eventos
        </div>
        <div class="pagination"></div>
    </div>
</div>

<script>
(function () {
    const tbody = document.getElementById('bitacora-tbody');
    if (!tbody) return;

    const searchInput   = document.getElementById('search-bitacora');
    const filterModulo  = document.getElementById('filter-modulo');
    const filterEvento  = document.getElementById('filter-evento');
    const filterUser    = document.getElementById('filter-usuario');
    const perPageSel    = document.getElementById('per-page');
    const dateFrom      = document.getElementById('date-from');
    const dateTo        = document.getElementById('date-to');
    const dateClear     = document.getElementById('date-clear');
    const footerInfo    = document.querySelector('.table-footer-info');
    const paginationEl  = document.querySelector('.pagination');

    let searchTerm = '', activeModulo = 'Todos', activeEvento = 'Todos', activeUser = 'Todos';
    let sortKey = null, sortDir = 1, currentPage = 1;

    function getPerPage() { return parseInt(perPageSel.value, 10) || 15; }

    // ── Filtro ──
    function getVisible() {
        const fromVal = dateFrom.value;
        const toVal   = dateTo.value;
        return Array.from(tbody.querySelectorAll('tr[data-usuario]')).filter(r => {
            // Filtro por módulo
            if (activeModulo !== 'Todos' && r.dataset.modulo !== activeModulo) return false;
            // Filtro por tipo de evento
            if (activeEvento !== 'Todos' && r.dataset.evento !== activeEvento.toLowerCase()) return false;
            // Filtro por usuario
            if (activeUser !== 'Todos' && r.dataset.usuario !== activeUser.toLowerCase()) return false;
            // Filtro de fechas
            if (fromVal || toVal) {
                const rowDate = r.dataset.timestamp.slice(0, 10);
                if (fromVal && rowDate < fromVal) return false;
                if (toVal && rowDate > toVal) return false;
            }
            if (!searchTerm) return true;
            return r.dataset.usuario.includes(searchTerm) ||
                   r.dataset.evento.includes(searchTerm) ||
                   r.dataset.descripcion.includes(searchTerm) ||
                   r.dataset.ip.includes(searchTerm);
        });
    }

    // ── Sort ──
    function sortRows(rows) {
        if (!sortKey) return rows;
        return rows.slice().sort((a, b) => {
            const va = a.dataset[sortKey] || '';
            const vb = b.dataset[sortKey] || '';
            return va < vb ? -sortDir : va > vb ? sortDir : 0;
        });
    }

    // ── Render ──
    function render() {
        const PER_PAGE = getPerPage();
        const visible = sortRows(getVisible());
        const totalPages = Math.max(1, Math.ceil(visible.length / PER_PAGE));
        if (currentPage > totalPages) currentPage = totalPages;
        const start = (currentPage - 1) * PER_PAGE;
        const pageRows = visible.slice(start, start + PER_PAGE);

        // Reorder DOM
        visible.forEach(r => tbody.appendChild(r));

        // Hide all, show page
        Array.from(tbody.querySelectorAll('tr[data-usuario]')).forEach(r => r.style.display = 'none');
        pageRows.forEach(r => r.style.display = '');

        // Footer info
        if (footerInfo) {
            footerInfo.innerHTML = 'Mostrando <strong>' + pageRows.length + '</strong> de <strong>' + visible.length + '</strong> eventos';
        }

        // Pagination
        if (paginationEl) {
            paginationEl.innerHTML = '';
            const prev = document.createElement('button');
            prev.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="15 18 9 12 15 6"/></svg>';
            prev.disabled = currentPage === 1;
            prev.addEventListener('click', () => { currentPage--; render(); });
            paginationEl.appendChild(prev);

            let pages = [];
            if (totalPages <= 7) {
                for (let i = 1; i <= totalPages; i++) pages.push(i);
            } else {
                pages = [1];
                if (currentPage > 3) pages.push('...');
                for (let i = Math.max(2, currentPage - 1); i <= Math.min(totalPages - 1, currentPage + 1); i++) pages.push(i);
                if (currentPage < totalPages - 2) pages.push('...');
                pages.push(totalPages);
            }
            pages.forEach(p => {
                if (p === '...') {
                    const span = document.createElement('span');
                    span.textContent = '…'; span.style.padding = '0 4px'; span.style.color = 'var(--gray-400)';
                    paginationEl.appendChild(span);
                } else {
                    const b = document.createElement('button');
                    b.textContent = p;
                    if (p === currentPage) b.classList.add('active');
                    b.addEventListener('click', () => { currentPage = p; render(); });
                    paginationEl.appendChild(b);
                }
            });

            const next = document.createElement('button');
            next.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="9 18 15 12 9 6"/></svg>';
            next.disabled = currentPage === totalPages;
            next.addEventListener('click', () => { currentPage++; render(); });
            paginationEl.appendChild(next);
        }
    }

    // ── Event Listeners ──
    searchInput.addEventListener('input', () => {
        searchTerm = searchInput.value.toLowerCase().trim();
        currentPage = 1;
        render();
    });

    filterModulo.addEventListener('change', () => {
        activeModulo = filterModulo.value;
        currentPage = 1;
        render();
    });

    filterEvento.addEventListener('change', () => {
        activeEvento = filterEvento.value;
        currentPage = 1;
        render();
    });

    filterUser.addEventListener('change', () => {
        activeUser = filterUser.value;
        currentPage = 1;
        render();
    });

    perPageSel.addEventListener('change', () => {
        currentPage = 1;
        render();
    });

    // ── Validaciones de fecha ──
    const today = new Date().toISOString().slice(0, 10);
    dateFrom.max = today;
    dateTo.max = today;

    function toggleClear() {
        dateClear.style.display = (dateFrom.value || dateTo.value) ? '' : 'none';
    }

    dateFrom.addEventListener('change', () => {
        if (dateFrom.value > today) dateFrom.value = today;
        dateTo.min = dateFrom.value || '';
        if (dateTo.value && dateFrom.value > dateTo.value) dateTo.value = dateFrom.value;
        toggleClear();
        currentPage = 1;
        render();
    });

    dateTo.addEventListener('change', () => {
        if (dateTo.value > today) dateTo.value = today;
        dateFrom.max = dateTo.value || today;
        if (dateFrom.value && dateTo.value < dateFrom.value) dateFrom.value = dateTo.value;
        toggleClear();
        currentPage = 1;
        render();
    });

    dateClear.addEventListener('click', () => {
        dateFrom.value = '';
        dateTo.value = '';
        dateFrom.max = today;
        dateTo.min = '';
        toggleClear();
        currentPage = 1;
        render();
    });

    // ── Sortable headers ──
    document.querySelectorAll('th.sortable[data-sort]').forEach(th => {
        th.style.cursor = 'pointer';
        th.addEventListener('click', () => {
            const key = th.dataset.sort;
            if (sortKey === key) sortDir *= -1;
            else { sortKey = key; sortDir = 1; }
            document.querySelectorAll('th.sortable[data-sort]').forEach(h => h.classList.remove('sort-asc', 'sort-desc'));
            th.classList.add(sortDir === 1 ? 'sort-asc' : 'sort-desc');
            render();
        });
    });

    // ── Initial render ──
    render();

    // ── Export Dropdown Toggle ──
    const exportBtn = document.getElementById('btn-export-toggle');
    const exportDrop = document.getElementById('export-dropdown');
    exportBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        exportDrop.style.display = exportDrop.style.display === 'none' ? 'block' : 'none';
    });
    document.addEventListener('click', () => { exportDrop.style.display = 'none'; });

    // ── Exportar CSV ──
    window.exportarCSV = function() {
        exportDrop.style.display = 'none';
        const rows = getVisible();
        if (!rows.length) { alert('No hay datos para exportar.'); return; }

        const headers = ['Timestamp', 'Usuario', 'Módulo', 'Evento', 'Detalle', 'IP'];
        const csvRows = [headers.join(',')];

        rows.forEach(r => {
            const cells = r.querySelectorAll('td');
            const line = Array.from(cells).map(c => {
                let txt = c.textContent.trim().replace(/"/g, '""');
                return '"' + txt + '"';
            });
            csvRows.push(line.join(','));
        });

        const bom = '\uFEFF';
        const blob = new Blob([bom + csvRows.join('\n')], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        const fecha = new Date().toISOString().slice(0,10);
        a.href = url;
        a.download = 'bitacora_' + fecha + '.csv';
        a.click();
        URL.revokeObjectURL(url);
    };

    // ── Exportar PDF (iframe oculto — sin pestañas extra) ──
    window.exportarPDF = function() {
        exportDrop.style.display = 'none';
        const rows = getVisible();
        if (!rows.length) { alert('No hay datos para exportar.'); return; }

        const headers = ['Timestamp', 'Usuario', 'Módulo', 'Evento', 'Detalle', 'IP'];
        let tableHTML = '<table style="width:100%; border-collapse:collapse; font-family:Arial,sans-serif; font-size:11px;">';
        tableHTML += '<thead><tr>';
        headers.forEach(h => { tableHTML += '<th style="border:1px solid #ccc; padding:6px 8px; background:#f1f5f9; text-align:left; font-size:11px;">' + h + '</th>'; });
        tableHTML += '</tr></thead><tbody>';

        rows.forEach(r => {
            const cells = r.querySelectorAll('td');
            tableHTML += '<tr>';
            cells.forEach(c => {
                tableHTML += '<td style="border:1px solid #e2e8f0; padding:5px 8px; font-size:10px;">' + c.textContent.trim() + '</td>';
            });
            tableHTML += '</tr>';
        });
        tableHTML += '</tbody></table>';

        const filtrosTexto = [];
        if (activeModulo !== 'Todos') filtrosTexto.push('Módulo: ' + activeModulo);
        if (activeEvento !== 'Todos') filtrosTexto.push('Evento: ' + activeEvento);
        if (activeUser !== 'Todos') filtrosTexto.push('Usuario: ' + activeUser);
        if (dateFrom.value) filtrosTexto.push('Desde: ' + dateFrom.value);
        if (dateTo.value) filtrosTexto.push('Hasta: ' + dateTo.value);
        if (searchTerm) filtrosTexto.push('Búsqueda: ' + searchTerm);

        const fecha = new Date();
        const fechaStr = fecha.toLocaleDateString('es-VE', { day:'2-digit', month:'long', year:'numeric' });
        const horaStr = fecha.toLocaleTimeString('es-VE', { hour:'2-digit', minute:'2-digit' });

        let html = '<!DOCTYPE html><html><head><title>Bitácora de Auditoría</title>';
        html += '<style>@page{size:landscape;margin:15mm} body{font-family:Arial,sans-serif; margin:0; padding:20px; color:#333;} h1{font-size:18px; margin:0 0 4px;} .meta{font-size:12px; color:#666; margin-bottom:16px;} .filtros{font-size:11px; color:#888; margin-bottom:12px;} .total{font-size:12px; color:#666; margin-top:10px; text-align:right;}</style>';
        html += '</head><body>';
        html += '<h1>SPDSS — Bitácora de Auditoría</h1>';
        html += '<div class="meta">Generado el ' + fechaStr + ' a las ' + horaStr + '</div>';
        if (filtrosTexto.length) {
            html += '<div class="filtros">Filtros aplicados: ' + filtrosTexto.join(' | ') + '</div>';
        }
        html += tableHTML;
        html += '<div class="total">Total: ' + rows.length + ' registros</div>';
        html += '</body></html>';

        // Usar iframe oculto para evitar abrir pestañas extra
        let iframe = document.getElementById('print-iframe');
        if (iframe) iframe.remove();
        iframe = document.createElement('iframe');
        iframe.id = 'print-iframe';
        iframe.style.cssText = 'position:fixed; top:-9999px; left:-9999px; width:1px; height:1px; border:none;';
        document.body.appendChild(iframe);

        const doc = iframe.contentDocument || iframe.contentWindow.document;
        doc.open();
        doc.write(html);
        doc.close();

        iframe.onload = function() {
            iframe.contentWindow.focus();
            iframe.contentWindow.print();
        };
    };
})();
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/logged_layout.php';
?>