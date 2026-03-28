<?php
declare(strict_types=1);

// ARCHIVO: resources/views/professor/historial.php
// Historial de actividad del profesor — DataTable server-side.

$pageTitle = 'Historial — Simulador SENIAT';
$activePage = 'historial';

$tiposEvento = $tiposEvento ?? [];

$extraCssInline = '
<style>
.status-badge { display:inline-block; padding:3px 10px; border-radius:20px; font-size:12px; font-weight:600; letter-spacing:0.02em; }
.badge-teal   { background:#e6fffa; color:#0d9488; }
.badge-purple { background:#f3e8ff; color:#7c3aed; }
.badge-blue   { background:#e0f2fe; color:#0369a1; }
.badge-green  { background:#dcfce7; color:#16a34a; }
.badge-amber  { background:#fef3c7; color:#d97706; }
.badge-gray   { background:#f1f5f9; color:#64748b; }
.ver-link { color:var(--blue-500); font-weight:600; text-decoration:none; }
.ver-link:hover { text-decoration:underline; }
</style>';
$extraCss = '<link rel="stylesheet" href="' . asset('css/shared/data-table.css') . '">' . $extraCssInline;

ob_start();
?>

<!-- Page Header -->
<div class="page-header">
    <div class="page-header-left">
        <h1>Historial</h1>
        <p>Registro de actividad reciente del simulador</p>
    </div>
</div>

<!-- Toolbar -->
<div class="toolbar">
    <div class="toolbar-left">
        <div class="search-box">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="hist-search" placeholder="Buscar por estudiante, caso…">
        </div>

        <select class="filter-select" id="hist-filter-tipo">
            <option value="">Todos los tipos</option>
            <?php foreach ($tiposEvento as $key => $label): ?>
                <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($label) ?></option>
            <?php endforeach; ?>
        </select>

        <div class="date-range-group">
            <div class="date-range-group__segment">
                <span class="date-range-group__label">Desde</span>
                <input type="date" class="date-range-group__input" id="hist-date-from">
            </div>
            <div class="date-range-group__segment">
                <span class="date-range-group__label">Hasta</span>
                <input type="date" class="date-range-group__input" id="hist-date-to">
            </div>
            <button class="date-range-group__clear" id="hist-date-clear" title="Limpiar fechas">✕</button>
        </div>
    </div>

    <div class="toolbar-right">
        <select class="per-page-select" id="hist-per-page">
            <option value="10">10</option>
            <option value="15" selected>15</option>
            <option value="25">25</option>
            <option value="50">50</option>
        </select>
    </div>
</div>

<!-- Table Container -->
<div class="table-container animate-in">
    <table class="data-table" id="historial-table">
        <thead>
            <tr>
                <th class="sortable" data-col="fecha" style="width:160px;">Fecha</th>
                <th data-col="tipo" style="width:150px;">Tipo</th>
                <th class="sortable" data-col="estudiante">Estudiante</th>
                <th class="sortable" data-col="caso">Caso</th>
                <th data-col="detalle">Detalle</th>
                <th style="width:60px;"></th>
            </tr>
        </thead>
        <tbody id="historial-tbody">
            <tr>
                <td colspan="6" style="text-align:center; padding:40px; color:var(--gray-400);">Cargando…</td>
            </tr>
        </tbody>
    </table>

    <div class="table-footer" id="historial-footer">
        <span class="table-footer-info" id="historial-info">—</span>
        <div class="pagination" id="historial-pagination"></div>
    </div>
</div>

<script>
(function() {
    var API_URL = '<?= base_url("/historial/api") ?>';
    var tbody   = document.getElementById('historial-tbody');
    var info    = document.getElementById('historial-info');
    var pagEl   = document.getElementById('historial-pagination');

    var searchInput = document.getElementById('hist-search');
    var filterTipo  = document.getElementById('hist-filter-tipo');
    var dateFrom    = document.getElementById('hist-date-from');
    var dateTo      = document.getElementById('hist-date-to');
    var dateClear   = document.getElementById('hist-date-clear');
    var perPage     = document.getElementById('hist-per-page');

    var state = { page: 1, sort: 'fecha', order: 'DESC' };
    var debounceTimer = null;

    // ── Tipo badges ──
    var tipoBadge = {
        intento_iniciado:   { label: 'Iniciado',     cls: 'badge-teal'   },
        intento_calificado: { label: 'Calificado',  cls: 'badge-purple' },
        intento_enviado:    { label: 'Enviado',      cls: 'badge-blue'   },
        caso_creado:        { label: 'Caso creado',  cls: 'badge-green'  },
        asignacion_creada:  { label: 'Asignación',   cls: 'badge-amber'  }
    };

    function fetchData() {
        var params = new URLSearchParams();
        params.set('page',  state.page);
        params.set('limit', perPage.value);
        params.set('sort',  state.sort);
        params.set('order', state.order);
        if (searchInput.value.trim()) params.set('search', searchInput.value.trim());
        if (filterTipo.value)         params.set('tipo',   filterTipo.value);
        if (dateFrom.value)           params.set('date_from', dateFrom.value);
        if (dateTo.value)             params.set('date_to',   dateTo.value);

        fetch(API_URL + '?' + params.toString())
            .then(function(r) { return r.json(); })
            .then(function(data) { renderData(data); })
            .catch(function() {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; padding:40px; color:var(--red-500);">Error al cargar datos</td></tr>';
            });
    }

    function renderData(data) {
        var rows = data.rows || [];
        if (!rows.length) {
            tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; padding:40px; color:var(--gray-400);">Sin resultados</td></tr>';
            info.textContent = '0 registros';
            pagEl.innerHTML = '';
            return;
        }

        var html = '';
        rows.forEach(function(r) {
            var badge = tipoBadge[r.tipo] || { label: r.tipo, cls: 'badge-gray' };
            var fecha = r.fecha ? formatDate(r.fecha) : '—';
            var estudiante = r.estudiante || '<span style="color:var(--gray-400)">—</span>';
            var linkHtml = '';
            if (r.tipo === 'intento_calificado' || r.tipo === 'intento_enviado' || r.tipo === 'intento_iniciado') {
                linkHtml = '<a href="<?= base_url("/entregas/") ?>' + r.ref_id + '" class="ver-link" style="font-size:var(--text-xs);">Ver</a>';
            }

            html += '<tr data-search="' + escHtml((r.estudiante||'') + ' ' + (r.caso||'') + ' ' + (r.detalle||'')) + '">';
            html += '<td>' + fecha + '</td>';
            html += '<td><span class="status-badge ' + badge.cls + '">' + badge.label + '</span></td>';
            html += '<td>' + estudiante + '</td>';
            html += '<td>' + escHtml(r.caso || '') + '</td>';
            html += '<td style="white-space:normal; line-height:1.4;">' + escHtml(r.detalle || '') + '</td>';
            html += '<td>' + linkHtml + '</td>';
            html += '</tr>';
        });
        tbody.innerHTML = html;

        // Info
        var start = (data.page - 1) * parseInt(perPage.value) + 1;
        var end = Math.min(start + rows.length - 1, data.total);
        info.textContent = start + '-' + end + ' de ' + data.total + ' registros';

        // Pagination
        renderPagination(data.page, data.pages);
    }

    function renderPagination(current, total) {
        if (total <= 1) { pagEl.innerHTML = ''; return; }
        var html = '';
        html += '<button class="page-btn" ' + (current <= 1 ? 'disabled' : '') + ' onclick="window.__histPage(' + (current-1) + ')">«</button>';
        for (var i = 1; i <= total; i++) {
            if (total > 7 && Math.abs(i - current) > 2 && i !== 1 && i !== total) {
                if (i === 2 || i === total - 1) html += '<span class="page-ellipsis">…</span>';
                continue;
            }
            html += '<button class="page-btn ' + (i === current ? 'active' : '') + '" onclick="window.__histPage(' + i + ')">' + i + '</button>';
        }
        html += '<button class="page-btn" ' + (current >= total ? 'disabled' : '') + ' onclick="window.__histPage(' + (current+1) + ')">»</button>';
        pagEl.innerHTML = html;
    }

    window.__histPage = function(p) { state.page = p; fetchData(); };

    function formatDate(str) {
        var d = new Date(str);
        if (isNaN(d)) return str;
        var dd = String(d.getDate()).padStart(2, '0');
        var mm = String(d.getMonth() + 1).padStart(2, '0');
        var yy = d.getFullYear();
        var hh = String(d.getHours()).padStart(2, '0');
        var mi = String(d.getMinutes()).padStart(2, '0');
        return dd + '/' + mm + '/' + yy + ' ' + hh + ':' + mi;
    }

    function escHtml(s) {
        var el = document.createElement('span');
        el.textContent = s;
        return el.innerHTML;
    }

    // ── Event listeners ──
    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function() { state.page = 1; fetchData(); }, 300);
    });

    filterTipo.addEventListener('change', function() { state.page = 1; fetchData(); });
    dateFrom.addEventListener('change', function() { state.page = 1; fetchData(); });
    dateTo.addEventListener('change', function() { state.page = 1; fetchData(); });
    perPage.addEventListener('change', function() { state.page = 1; fetchData(); });
    dateClear.addEventListener('click', function() {
        dateFrom.value = '';
        dateTo.value = '';
        state.page = 1;
        fetchData();
    });

    // Sortable headers
    document.querySelectorAll('#historial-table th.sortable').forEach(function(th) {
        th.addEventListener('click', function() {
            var col = this.dataset.col;
            if (state.sort === col) {
                state.order = state.order === 'DESC' ? 'ASC' : 'DESC';
            } else {
                state.sort = col;
                state.order = 'DESC';
            }
            // Update visual
            document.querySelectorAll('#historial-table th.sortable').forEach(function(h) {
                h.classList.remove('sort-asc', 'sort-desc');
            });
            this.classList.add(state.order === 'ASC' ? 'sort-asc' : 'sort-desc');
            state.page = 1;
            fetchData();
        });
    });

    // Initial load
    fetchData();
})();
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/logged_layout.php';
?>