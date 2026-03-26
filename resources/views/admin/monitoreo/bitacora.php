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

// Datos inyectados por el controlador (dropdown data only — rows load via API)
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
            <input type="text" data-search-for="tbl-bitacora" placeholder="Buscar por usuario, IP o descripción...">
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
            <select data-perpage-for="tbl-bitacora" class="per-page-select">
                <option value="10">10</option>
                <option value="15" selected>15</option>
                <option value="25">25</option>
                <option value="50">50</option>
            </select>
            filas
        </label>
    </div>
</div>

<!-- Data Table (Server-Side) -->
<div class="table-container">
    <table class="data-table" id="tbl-bitacora"
           data-server-url="<?= base_url('/admin/monitoreo/bitacora/api') ?>"
           data-render="renderBitacoraRow"
           data-columns='["email","modulo","evento","detalle","ip_address","created_at"]'>
        <thead>
            <tr>
                <th class="sortable" data-sort-key="created_at" style="width: 14%">Timestamp</th>
                <th class="sortable" data-sort-key="email" style="width: 17%">Usuario</th>
                <th class="sortable" data-sort-key="modulo" style="width: 12%">Módulo</th>
                <th class="sortable" data-sort-key="evento" style="width: 22%">Evento</th>
                <th class="sortable" data-sort-key="detalle" style="width: 22%">Detalle</th>
                <th style="width: 13%">IP</th>
            </tr>
        </thead>
        <tbody style="font-size: 13px; font-family: var(--font-ui);"></tbody>
    </table>

    <div class="table-footer" data-footer-for="tbl-bitacora">
        <div class="table-footer-info"></div>
        <div class="pagination"></div>
    </div>
</div>

<script>
(function () {
    // ── Mapa de módulos (desde PHP) ──
    var MODULO_LABELS = <?= json_encode($modulos) ?>;
    // Mapa de entidades
    var ENTIDAD_LABELS = {
        'users': 'Usuario',
        'sim_casos_estudios': 'Caso',
        'sim_intentos': 'Intento',
        'sim_asignaciones': 'Asignación',
        'sim_config_caso': 'Config'
    };
    // Colores por nivel de riesgo
    var RISK_COLORS = {
        'info':     'background:#eff6ff; color:#1d4ed8;',
        'warning':  'background:#fffbeb; color:#b45309;',
        'critical': 'background:#fef2f2; color:#b91c1c;'
    };

    // ── Custom Row Renderer (llamado por DataTableManager) ──
    window.renderBitacoraRow = function(row) {
        // Timestamp formateado
        var ts = row.created_at || '';
        var tsFormatted = '';
        if (ts) {
            try {
                var d = new Date(ts.replace(' ', 'T'));
                var months = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
                tsFormatted = d.getDate().toString().padStart(2,'0') + ' ' + months[d.getMonth()] + ' ' + d.getFullYear() + ', ' +
                    d.getHours().toString().padStart(2,'0') + ':' + d.getMinutes().toString().padStart(2,'0') + ':' + d.getSeconds().toString().padStart(2,'0');
            } catch(e) { tsFormatted = ts; }
        }

        // Detalle
        var detalleTexto = '';
        if (row.entidad_tipo && row.entidad_id) {
            detalleTexto = (ENTIDAD_LABELS[row.entidad_tipo] || row.entidad_tipo) + ' #' + row.entidad_id;
            if (row.detalle) detalleTexto += ' — ' + row.detalle;
        } else {
            detalleTexto = row.detalle || '—';
        }

        var moduloLabel = MODULO_LABELS[row.modulo] || row.modulo || '';
        var badgeStyle = RISK_COLORS[row.nivel_riesgo] || RISK_COLORS['info'];
        var evento = row.evento || 'Sin tipo';

        return '<tr>' +
            '<td style="color:var(--color-text-light);">' + escHtml(tsFormatted) + '</td>' +
            '<td><strong>' + escHtml(row.email || '') + '</strong></td>' +
            '<td><span style="font-size:12px; color:var(--gray-500);">' + escHtml(moduloLabel) + '</span></td>' +
            '<td><span class="status-badge" style="' + badgeStyle + '">' + escHtml(evento) + '</span></td>' +
            '<td>' + escHtml(detalleTexto) + '</td>' +
            '<td style="color:var(--color-text-light); font-size:12px;">' + escHtml(row.ip_address || '—') + '</td>' +
        '</tr>';
    };

    function escHtml(s) {
        var d = document.createElement('div');
        d.textContent = String(s);
        return d.innerHTML;
    }

    // ── Filtros → DataTableManager.setFilter() ──
    var tableId = 'tbl-bitacora';

    document.getElementById('filter-modulo').addEventListener('change', function() {
        window.DataTableManager.setFilter(tableId, 'modulo', this.value === 'Todos' ? '' : this.value);
    });
    document.getElementById('filter-evento').addEventListener('change', function() {
        window.DataTableManager.setFilter(tableId, 'evento', this.value === 'Todos' ? '' : this.value);
    });
    document.getElementById('filter-usuario').addEventListener('change', function() {
        window.DataTableManager.setFilter(tableId, 'usuario', this.value === 'Todos' ? '' : this.value);
    });

    // ── Filtros de fecha ──
    var dateFrom = document.getElementById('date-from');
    var dateTo = document.getElementById('date-to');
    var dateClear = document.getElementById('date-clear');
    var today = new Date().toISOString().slice(0, 10);
    dateFrom.max = today;
    dateTo.max = today;

    function toggleClear() {
        dateClear.style.display = (dateFrom.value || dateTo.value) ? '' : 'none';
    }

    dateFrom.addEventListener('change', function() {
        if (dateFrom.value > today) dateFrom.value = today;
        dateTo.min = dateFrom.value || '';
        if (dateTo.value && dateFrom.value > dateTo.value) dateTo.value = dateFrom.value;
        toggleClear();
        window.DataTableManager.setFilter(tableId, 'date_from', dateFrom.value);
    });

    dateTo.addEventListener('change', function() {
        if (dateTo.value > today) dateTo.value = today;
        dateFrom.max = dateTo.value || today;
        if (dateFrom.value && dateTo.value < dateFrom.value) dateFrom.value = dateTo.value;
        toggleClear();
        window.DataTableManager.setFilter(tableId, 'date_to', dateTo.value);
    });

    dateClear.addEventListener('click', function() {
        dateFrom.value = '';
        dateTo.value = '';
        dateFrom.max = today;
        dateTo.min = '';
        toggleClear();
        window.DataTableManager.setFilter(tableId, 'date_from', '');
        window.DataTableManager.setFilter(tableId, 'date_to', '');
    });

    // ── Export Dropdown Toggle ──
    var exportBtn = document.getElementById('btn-export-toggle');
    var exportDrop = document.getElementById('export-dropdown');
    exportBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        exportDrop.style.display = exportDrop.style.display === 'none' ? 'block' : 'none';
    });
    document.addEventListener('click', function() { exportDrop.style.display = 'none'; });

    // ── Helper: obtener filtros actuales como query string ──
    function getFilterParams() {
        var params = new URLSearchParams({ limit: '9999', page: '1' });
        var search = document.querySelector('[data-search-for="tbl-bitacora"]');
        if (search && search.value.trim()) params.set('search', search.value.trim());
        var modulo = document.getElementById('filter-modulo').value;
        if (modulo !== 'Todos') params.set('modulo', modulo);
        var evento = document.getElementById('filter-evento').value;
        if (evento !== 'Todos') params.set('evento', evento);
        var usuario = document.getElementById('filter-usuario').value;
        if (usuario !== 'Todos') params.set('usuario', usuario);
        if (dateFrom.value) params.set('date_from', dateFrom.value);
        if (dateTo.value) params.set('date_to', dateTo.value);
        return params;
    }

    // ── Exportar CSV ──
    window.exportarCSV = async function() {
        exportDrop.style.display = 'none';
        try {
            var res = await fetch('<?= base_url('/admin/monitoreo/bitacora/api') ?>?' + getFilterParams());
            var data = await res.json();
            var rows = data.rows || [];
            if (!rows.length) { alert('No hay datos para exportar.'); return; }

            var headers = ['Timestamp', 'Usuario', 'Módulo', 'Evento', 'Detalle', 'IP'];
            var csvRows = [headers.join(',')];
            rows.forEach(function(r) {
                var detalleTexto = '';
                if (r.entidad_tipo && r.entidad_id) {
                    detalleTexto = (ENTIDAD_LABELS[r.entidad_tipo] || r.entidad_tipo) + ' #' + r.entidad_id;
                    if (r.detalle) detalleTexto += ' — ' + r.detalle;
                } else {
                    detalleTexto = r.detalle || '';
                }
                csvRows.push([
                    '"' + (r.created_at || '').replace(/"/g, '""') + '"',
                    '"' + (r.email || '').replace(/"/g, '""') + '"',
                    '"' + (MODULO_LABELS[r.modulo] || r.modulo || '').replace(/"/g, '""') + '"',
                    '"' + (r.evento || '').replace(/"/g, '""') + '"',
                    '"' + detalleTexto.replace(/"/g, '""') + '"',
                    '"' + (r.ip_address || '').replace(/"/g, '""') + '"'
                ].join(','));
            });

            var bom = '\uFEFF';
            var blob = new Blob([bom + csvRows.join('\n')], { type: 'text/csv;charset=utf-8;' });
            var url = URL.createObjectURL(blob);
            var a = document.createElement('a');
            a.href = url;
            a.download = 'bitacora_' + new Date().toISOString().slice(0,10) + '.csv';
            a.click();
            URL.revokeObjectURL(url);
        } catch(e) {
            alert('Error al exportar: ' + e.message);
        }
    };

    // ── Exportar PDF ──
    window.exportarPDF = async function() {
        exportDrop.style.display = 'none';
        try {
            var res = await fetch('<?= base_url('/admin/monitoreo/bitacora/api') ?>?' + getFilterParams());
            var data = await res.json();
            var rows = data.rows || [];
            if (!rows.length) { alert('No hay datos para exportar.'); return; }

            var headers = ['Timestamp', 'Usuario', 'Módulo', 'Evento', 'Detalle', 'IP'];
            var tableHTML = '<table style="width:100%; border-collapse:collapse; font-family:Arial,sans-serif; font-size:11px;"><thead><tr>';
            headers.forEach(function(h) { tableHTML += '<th style="border:1px solid #ccc; padding:6px 8px; background:#f1f5f9; text-align:left; font-size:11px;">' + h + '</th>'; });
            tableHTML += '</tr></thead><tbody>';

            rows.forEach(function(r) {
                var detalleTexto = '';
                if (r.entidad_tipo && r.entidad_id) {
                    detalleTexto = (ENTIDAD_LABELS[r.entidad_tipo] || r.entidad_tipo) + ' #' + r.entidad_id;
                    if (r.detalle) detalleTexto += ' — ' + r.detalle;
                } else {
                    detalleTexto = r.detalle || '';
                }
                tableHTML += '<tr>';
                tableHTML += '<td style="border:1px solid #e2e8f0; padding:5px 8px; font-size:10px;">' + escHtml(r.created_at || '') + '</td>';
                tableHTML += '<td style="border:1px solid #e2e8f0; padding:5px 8px; font-size:10px;">' + escHtml(r.email || '') + '</td>';
                tableHTML += '<td style="border:1px solid #e2e8f0; padding:5px 8px; font-size:10px;">' + escHtml(MODULO_LABELS[r.modulo] || r.modulo || '') + '</td>';
                tableHTML += '<td style="border:1px solid #e2e8f0; padding:5px 8px; font-size:10px;">' + escHtml(r.evento || '') + '</td>';
                tableHTML += '<td style="border:1px solid #e2e8f0; padding:5px 8px; font-size:10px;">' + escHtml(detalleTexto) + '</td>';
                tableHTML += '<td style="border:1px solid #e2e8f0; padding:5px 8px; font-size:10px;">' + escHtml(r.ip_address || '') + '</td>';
                tableHTML += '</tr>';
            });
            tableHTML += '</tbody></table>';

            var filtrosTexto = [];
            var modVal = document.getElementById('filter-modulo').value;
            var evtVal = document.getElementById('filter-evento').value;
            var usrVal = document.getElementById('filter-usuario').value;
            var srcVal = document.querySelector('[data-search-for="tbl-bitacora"]')?.value || '';
            if (modVal !== 'Todos') filtrosTexto.push('Módulo: ' + modVal);
            if (evtVal !== 'Todos') filtrosTexto.push('Evento: ' + evtVal);
            if (usrVal !== 'Todos') filtrosTexto.push('Usuario: ' + usrVal);
            if (dateFrom.value) filtrosTexto.push('Desde: ' + dateFrom.value);
            if (dateTo.value) filtrosTexto.push('Hasta: ' + dateTo.value);
            if (srcVal) filtrosTexto.push('Búsqueda: ' + srcVal);

            var fecha = new Date();
            var fechaStr = fecha.toLocaleDateString('es-VE', { day:'2-digit', month:'long', year:'numeric' });
            var horaStr = fecha.toLocaleTimeString('es-VE', { hour:'2-digit', minute:'2-digit' });

            var html = '<!DOCTYPE html><html><head><title>Bitácora de Auditoría</title>';
            html += '<style>@page{size:landscape;margin:15mm} body{font-family:Arial,sans-serif; margin:0; padding:20px; color:#333;} h1{font-size:18px; margin:0 0 4px;} .meta{font-size:12px; color:#666; margin-bottom:16px;} .filtros{font-size:11px; color:#888; margin-bottom:12px;} .total{font-size:12px; color:#666; margin-top:10px; text-align:right;}</style>';
            html += '</head><body>';
            html += '<h1>SPDSS — Bitácora de Auditoría</h1>';
            html += '<div class="meta">Generado el ' + fechaStr + ' a las ' + horaStr + '</div>';
            if (filtrosTexto.length) html += '<div class="filtros">Filtros aplicados: ' + filtrosTexto.join(' | ') + '</div>';
            html += tableHTML;
            html += '<div class="total">Total: ' + rows.length + ' registros</div>';
            html += '</body></html>';

            var iframe = document.getElementById('print-iframe');
            if (iframe) iframe.remove();
            iframe = document.createElement('iframe');
            iframe.id = 'print-iframe';
            iframe.style.cssText = 'position:fixed; top:-9999px; left:-9999px; width:1px; height:1px; border:none;';
            document.body.appendChild(iframe);

            var doc = iframe.contentDocument || iframe.contentWindow.document;
            doc.open();
            doc.write(html);
            doc.close();

            iframe.onload = function() {
                iframe.contentWindow.focus();
                iframe.contentWindow.print();
            };
        } catch(e) {
            alert('Error al exportar: ' + e.message);
        }
    };
})();
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/logged_layout.php';
?>