<?php
declare(strict_types=1);

$pageTitle = 'Gestión de Correos';
$activePage = 'correos';
$breadcrumbs = [
    'Inicio' => base_url('/admin'),
    'Monitoreo' => '#',
    'Correos' => '#'
];

$extraCss = '<link rel="stylesheet" href="' . asset('css/shared/data-table.css') . '">';

ob_start();
?>
<div class="page-header">
    <div class="page-header-left">
        <h1>Gestión de Correos</h1>
        <p>Monitoree el estado del servidor de correo, la cola de envíos y el historial de notificaciones del sistema.</p>
    </div>
</div>

<!-- Tarjetas de Resumen -->
<div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px; align-items:stretch;">
    <!-- SMTP Status (carga async) -->
    <div id="smtp-card" style="background: var(--sim-white, #fff); border: 1.5px solid var(--gray-200, #e5e7eb); border-radius: 12px; padding: 20px; display:flex; align-items:center; gap:14px;">
        <div id="smtp-icon" style="width:44px; height:44px; border-radius:10px; display:flex; align-items:center; justify-content:center; background:rgba(156,163,175,0.12); color:#9ca3af; flex-shrink:0;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                <circle cx="12" cy="12" r="10" /><polyline points="12 6 12 12 16 14" />
            </svg>
        </div>
        <div>
            <div style="font-size:13px; color:var(--gray-500); font-weight:500;">Servidor SMTP</div>
            <div id="smtp-status" style="font-size:15px; font-weight:700; color:#9ca3af;">Verificando...</div>
            <div id="smtp-detail" style="font-size:12px; color:var(--gray-400); margin-top:2px;"></div>
        </div>
    </div>

    <!-- Enviados -->
    <div style="background: var(--sim-white, #fff); border: 1.5px solid var(--gray-200, #e5e7eb); border-radius: 12px; padding: 20px; display:flex; align-items:center; gap:14px;">
        <div style="width:44px; height:44px; border-radius:10px; display:flex; align-items:center; justify-content:center; background:rgba(59,130,246,0.12); color:#3b82f6; flex-shrink:0;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                <line x1="22" y1="2" x2="11" y2="13" /><polygon points="22 2 15 22 11 13 2 9 22 2" />
            </svg>
        </div>
        <div>
            <div style="font-size:13px; color:var(--gray-500); font-weight:500;">Enviados</div>
            <div style="font-size:22px; font-weight:700; color:var(--text-dark, #1e293b);"><?= $stats['enviados'] ?></div>
            <div style="font-size:12px; color:var(--gray-400); margin-top:2px;">Entregados exitosamente</div>
        </div>
    </div>

    <!-- Pendientes -->
    <div style="background: var(--sim-white, #fff); border: 1.5px solid var(--gray-200, #e5e7eb); border-radius: 12px; padding: 20px; display:flex; align-items:center; gap:14px;">
        <div style="width:44px; height:44px; border-radius:10px; display:flex; align-items:center; justify-content:center; background:rgba(245,158,11,0.12); color:#f59e0b; flex-shrink:0;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                <circle cx="12" cy="12" r="10" /><polyline points="12 6 12 12 16 14" />
            </svg>
        </div>
        <div>
            <div style="font-size:13px; color:var(--gray-500); font-weight:500;">Pendientes</div>
            <div style="font-size:22px; font-weight:700; color:var(--text-dark, #1e293b);"><?= $stats['pendientes'] ?></div>
            <div style="font-size:12px; color:var(--gray-400); margin-top:2px;">En cola de reintento</div>
        </div>
    </div>

    <!-- Fallidos -->
    <div style="background: var(--sim-white, #fff); border: 1.5px solid var(--gray-200, #e5e7eb); border-radius: 12px; padding: 20px; display:flex; align-items:center; gap:14px;">
        <div style="width:44px; height:44px; border-radius:10px; display:flex; align-items:center; justify-content:center; background:rgba(239,68,68,0.12); color:#ef4444; flex-shrink:0;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                <circle cx="12" cy="12" r="10" /><line x1="15" y1="9" x2="9" y2="15" /><line x1="9" y1="9" x2="15" y2="15" />
            </svg>
        </div>
        <div>
            <div style="font-size:13px; color:var(--gray-500); font-weight:500;">Fallidos</div>
            <div style="font-size:22px; font-weight:700; color:var(--text-dark, #1e293b);"><?= $stats['fallidos'] ?></div>
            <div style="font-size:12px; color:var(--gray-400); margin-top:2px;">Máximo de intentos alcanzado</div>
        </div>
    </div>
</div>

<!-- Toolbar de la tabla -->
<div class="toolbar">
    <div class="toolbar-left">
        <div class="search-box">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                <circle cx="11" cy="11" r="8" />
                <path d="m21 21-4.35-4.35" />
            </svg>
            <input type="text" data-search-for="tbl-correos" placeholder="Buscar por tipo, destinatario o asunto...">
        </div>
        <button class="btn btn-secondary" data-reload-for="tbl-correos" onclick="window.DataTableManager.reloadTableData('tbl-correos');" title="Recargar tabla" style="padding: 10px; border-radius: 8px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="transform-origin: center;">
                <polyline points="23 4 23 10 17 10"></polyline>
                <polyline points="1 20 1 14 7 14"></polyline>
                <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
            </svg>
        </button>
    </div>
    <div class="toolbar-right" style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
        <div style="display:flex; align-items:center; gap:8px;">
            <button class="btn btn-primary" id="btn-procesar-cola" style="display:flex; align-items:center; gap:6px; padding:8px 16px; white-space:nowrap;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="23 4 23 10 17 10"></polyline><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path></svg>
                Procesar Cola
            </button>
            <span style="font-size:11px; color:var(--gray-400); max-width:180px; line-height:1.3;">
                Procesa hasta 10 correos por clic. Si hay más, pulse varias veces.
            </span>
        </div>
        <label style="font-size:var(--text-xs); color:var(--gray-500); display:flex; align-items:center; gap:6px;">
            Mostrar <select data-perpage-for="tbl-correos" class="per-page-select"><option value="10" selected>10</option><option value="25">25</option><option value="50">50</option></select> filas
        </label>
    </div>
</div>

<!-- Data Table (Server-Side) -->
<div class="table-container">
    <table class="data-table" id="tbl-correos"
           data-server-url="<?= base_url('/admin/monitoreo/correos/api') ?>"
           data-render="renderCorreoRow">
        <thead>
            <tr>
                <th style="width:50px">#</th>
                <th class="sortable" data-sort-key="tipo">Tipo</th>
                <th class="sortable" data-sort-key="destinatario">Destinatario</th>
                <th class="sortable" data-sort-key="asunto">Asunto</th>
                <th class="sortable" data-sort-key="estado">Estado</th>
                <th class="sortable" data-sort-key="intentos">Intentos</th>
                <th class="sortable" data-sort-key="created_at">Fecha</th>
                <th style="width:160px">Resolución</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>

    <div class="table-footer" data-footer-for="tbl-correos">
        <div class="table-footer-info">
            Mostrando <strong>0</strong> correos
        </div>
        <div class="pagination"></div>
    </div>
</div>

<script>
// Custom row renderer for correos DataTable
window.renderCorreoRow = function(row, index, data) {
    const estadoClasses = { pendiente: 'status-draft', enviado: 'status-published', fallido: 'status-archived' };
    const cls = estadoClasses[row.estado] || 'status-draft';
    const maxRetries = data.maxRetries || <?= $maxRetries ?>;
    const asunto = (row.asunto || '').length > 50 ? (row.asunto.substring(0, 50) + '…') : (row.asunto || '');

    // Columna dinámica: muestra info relevante según el estado
    let resolucion = '—';
    if (row.estado === 'enviado' && row.sent_at) {
        resolucion = `<span style="color:#10b981;">Enviado: ${escHtml(row.sent_at)}</span>`;
    } else if (row.estado === 'pendiente' && row.next_retry_at) {
        resolucion = `<span style="color:#f59e0b;">Reintento: ${escHtml(row.next_retry_at)}</span>`;
    }

    return `<tr>
        <td style="color:var(--gray-400); font-size:13px;">${index}</td>
        <td><span class="status-badge" style="background:rgba(99,102,241,0.1); color:#6366f1;">${capitalize(row.tipo || '')}</span></td>
        <td style="font-size:13px;">${escHtml(row.destinatario || '')}</td>
        <td style="font-size:13px; max-width:250px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="${escHtml(row.asunto || '')}">${escHtml(asunto)}</td>
        <td><span class="status-badge ${cls}">${capitalize(row.estado || '')}</span></td>
        <td style="text-align:center; font-size:13px;">${row.intentos || 0} / ${maxRetries}</td>
        <td style="font-size:13px;">${escHtml(row.created_at || '')}</td>
        <td style="font-size:13px;">${resolucion}</td>
    </tr>`;
};

function capitalize(s) { return s.charAt(0).toUpperCase() + s.slice(1); }
function escHtml(s) { const d = document.createElement('div'); d.textContent = s; return d.innerHTML; }
document.getElementById('btn-procesar-cola')?.addEventListener('click', async function() {
    const btn = this;
    const originalHTML = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = 'Procesando...';

    try {
        const fd = new FormData();
        fd.append('csrf_token', '<?= \App\Core\Csrf::getToken() ?>');

        const res = await fetch('<?= base_url('/admin/monitoreo/correos/procesar') ?>', {
            method: 'POST',
            body: fd
        });

        if (res.redirected || !res.ok) {
            location.reload();
            return;
        }

        const data = await res.json();

        if (data.success) {
            if (window.showToast) window.showToast(data.message, 'success');
            // Refrescar solo la tabla (sin recargar la página)
            window.DataTableManager.reloadTableData('tbl-correos');
        } else {
            if (window.showToast) window.showToast(data.message || 'Error al procesar la cola.', 'error');
        }
    } catch (e) {
        if (window.showToast) window.showToast('Error de conexión.', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalHTML;
    }
});
// SMTP Health Check async
(async function() {
    try {
        const res = await fetch('<?= base_url('/admin/monitoreo/correos/smtp-health') ?>');
        const data = await res.json();
        const icon = document.getElementById('smtp-icon');
        const status = document.getElementById('smtp-status');
        const detail = document.getElementById('smtp-detail');

        if (data.ok) {
            icon.style.background = 'rgba(16,185,129,0.12)';
            icon.style.color = '#10b981';
            icon.innerHTML = '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" /><polyline points="22 4 12 14.01 9 11.01" /></svg>';
            status.style.color = '#10b981';
            status.textContent = 'Conectado';
            detail.textContent = data.host + ' \u00b7 ' + data.latency_ms + 'ms';
        } else {
            icon.style.background = 'rgba(245,158,11,0.12)';
            icon.style.color = '#f59e0b';
            icon.innerHTML = '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" /><line x1="12" y1="9" x2="12" y2="13" /><line x1="12" y1="17" x2="12.01" y2="17" /></svg>';
            status.style.color = '#f59e0b';
            status.textContent = 'Sin conexi\u00f3n';
            detail.textContent = data.error || 'Desconocido';
        }
    } catch (e) {
        document.getElementById('smtp-status').textContent = 'Error de red';
    }
})();
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/logged_layout.php';
?>
