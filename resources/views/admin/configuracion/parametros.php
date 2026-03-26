<?php
declare(strict_types=1);

// ARCHIVO: resources/views/admin/configuracion/parametros.php

$pageTitle = 'Parámetros del Sistema';
$activePage = 'parametros';
$breadcrumbs = [
    'Inicio' => base_url('/admin'),
    'Configuración' => '#',
    'Parámetros Globales' => '#'
];

$extraCss = '<link rel="stylesheet" href="' . asset('css/shared/data-table.css') . '">
<style>
.param-switch{position:relative;display:inline-block;width:44px;height:24px;flex-shrink:0}
.param-switch input{opacity:0;width:0;height:0}
.param-switch .track{position:absolute;cursor:pointer;inset:0;background:var(--gray-300);transition:.3s;border-radius:34px}
.param-switch .track::before{content:"";position:absolute;height:18px;width:18px;left:3px;bottom:3px;background:var(--white);transition:.3s;border-radius:50%;box-shadow:0 1px 3px rgba(0,0,0,.15)}
.param-switch input:checked+.track{background:var(--green-600)}
.param-switch input:checked+.track::before{transform:translateX(20px)}
.param-row{display:flex;align-items:center;justify-content:space-between;padding:14px 0;border-bottom:1px solid var(--gray-100)}
.param-row:last-child{border-bottom:none;padding-bottom:0}
.param-row>label{margin:0;font-weight:500;font-size:var(--text-md);color:var(--gray-700)}
.param-unit{font-size:var(--text-xs);color:var(--gray-400);font-weight:500}
</style>';

// Datos inyectados por el controlador
$config  = $config ?? [];
$backups = $backups ?? [];

// Valores de configuración (con defaults)
$autoEnabled  = ($config['backup_auto_enabled'] ?? '0') === '1';
$frecuencia   = $config['backup_frecuencia'] ?? 'diario';
$hora         = $config['backup_hora'] ?? '03:00';
$dia          = (int)($config['backup_dia'] ?? 0);
$retencion    = (int)($config['backup_retencion'] ?? 5);
$ultimoTs     = $config['backup_ultimo_timestamp'] ?? null;

// Meses en español
$meses = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];

ob_start();
?>

<div class="page-header" style="margin-bottom: 32px;">
    <div class="page-header-left">
        <h1>Parámetros Globales</h1>
        <p>Ajustes generales de respaldo y mantenimiento del sistema.</p>
    </div>
    <div style="display:flex; gap:12px;">
        <button class="btn btn-primary" onclick="guardarConfiguracion()" id="btn-guardar">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                <polyline points="17 21 17 13 7 13 7 21"/>
                <polyline points="7 3 7 8 15 8"/>
            </svg>
            Guardar Cambios
        </button>
    </div>
</div>

<!-- Card de Respaldo -->
<div class="panel" style="max-width:560px;">
    <div class="panel-header">
        <h3>Respaldo de Base de Datos</h3>
    </div>
    <div class="panel-body">
        <p style="font-size:var(--text-md); color:var(--gray-500); margin:0 0 20px; line-height:1.5;">
            Genere un respaldo manual o configure respaldos automáticos programados.
        </p>

        <!-- Último respaldo -->
        <div class="param-row">
            <label>Último respaldo</label>
            <?php
                $lastParsed = false;
                if ($ultimoTs) {
                    try {
                        $dt = new \DateTime($ultimoTs);
                        $lastDate = $dt->format('d') . ' ' . $meses[(int)$dt->format('n') - 1] . ' ' . $dt->format('Y');
                        $lastTime = $dt->format('H:i');
                        $lastParsed = true;
                    } catch (\Throwable $e) {
                        $lastParsed = false;
                    }
                }
            ?>
            <?php if ($lastParsed): ?>
                <div style="display:flex; align-items:center; gap:12px; padding:8px 14px; background:var(--green-50); border-radius:var(--radius-sm); border:1px solid var(--green-100);">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--green-500)" stroke-width="2" stroke-linecap="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                        <polyline points="22 4 12 14.01 9 11.01"/>
                    </svg>
                    <div style="font-size:12px; color:var(--gray-600);">
                        <strong><?= $lastDate ?></strong> a las <?= $lastTime ?>
                    </div>
                </div>
            <?php else: ?>
                <div style="display:flex; align-items:center; gap:10px; padding:10px 14px; background:var(--gray-50); border-radius:var(--radius-sm); border:1px dashed var(--gray-200);">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--gray-300)" stroke-width="2" stroke-linecap="round">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12 6 12 12 16 14"/>
                    </svg>
                    <span style="font-size:12px; color:var(--gray-400); font-style:italic;">Sin respaldos registrados aún</span>
                </div>
            <?php endif; ?>
        </div>

        <!-- Generar respaldo manual -->
        <div class="param-row">
            <label>Generar respaldo manual</label>
            <button id="btn-backup" class="btn btn-outline" style="padding:7px 16px; font-size:12px; display:inline-flex; align-items:center; gap:6px;"
                    onclick="generarRespaldo()">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="7 10 12 15 17 10"/>
                    <line x1="12" y1="15" x2="12" y2="3"/>
                </svg>
                <span id="btn-backup-text">Generar Respaldo (.sql)</span>
            </button>
        </div>

        <!-- Respaldo automático -->
        <div class="param-row">
            <label>Respaldo automático</label>
            <label class="param-switch">
                <input type="checkbox" id="cfg-auto-enabled" <?= $autoEnabled ? 'checked' : '' ?>
                       onchange="document.getElementById('autobackup-options').style.display = this.checked ? 'flex' : 'none'">
                <span class="track"></span>
            </label>
        </div>
        <div id="autobackup-options" style="display:<?= $autoEnabled ? 'flex' : 'none' ?>; flex-direction:column; gap:14px; padding:14px 18px; margin:-4px 0 8px; background:var(--blue-50); border-radius:var(--radius-sm); border:1px solid var(--blue-100);">
            <div style="display:flex; align-items:center; justify-content:space-between;">
                <label style="font-size:13px; font-weight:500; color:var(--gray-600);">Frecuencia</label>
                <select id="cfg-frecuencia" class="per-page-select" style="width:auto; min-width:140px;"
                        onchange="document.getElementById('backup-dia-row').style.display = this.value === 'semanal' ? 'flex' : 'none'">
                    <option value="diario" <?= $frecuencia === 'diario' ? 'selected' : '' ?>>Diario</option>
                    <option value="semanal" <?= $frecuencia === 'semanal' ? 'selected' : '' ?>>Semanal</option>
                </select>
            </div>
            <div id="backup-dia-row" style="display:<?= $frecuencia === 'semanal' ? 'flex' : 'none' ?>; align-items:center; justify-content:space-between;">
                <label style="font-size:13px; font-weight:500; color:var(--gray-600);">Día de la semana</label>
                <select id="cfg-dia" class="per-page-select" style="width:auto; min-width:140px;">
                    <option value="1" <?= $dia === 1 ? 'selected' : '' ?>>Lunes</option>
                    <option value="2" <?= $dia === 2 ? 'selected' : '' ?>>Martes</option>
                    <option value="3" <?= $dia === 3 ? 'selected' : '' ?>>Miércoles</option>
                    <option value="4" <?= $dia === 4 ? 'selected' : '' ?>>Jueves</option>
                    <option value="5" <?= $dia === 5 ? 'selected' : '' ?>>Viernes</option>
                    <option value="6" <?= $dia === 6 ? 'selected' : '' ?>>Sábado</option>
                    <option value="0" <?= $dia === 0 ? 'selected' : '' ?>>Domingo</option>
                </select>
            </div>
            <div style="display:flex; align-items:center; justify-content:space-between;">
                <label style="font-size:13px; font-weight:500; color:var(--gray-600);">Hora de ejecución</label>
                <input type="time" id="cfg-hora" value="<?= e($hora) ?>" style="width:110px; text-align:center; font-size:13px; padding:6px 10px; border:1px solid var(--gray-300); border-radius:var(--radius-sm); color:var(--gray-700);">
            </div>
        </div>

        <!-- Retención -->
        <div class="param-row">
            <label>Retención de respaldos</label>
            <div style="display:flex; align-items:center; gap:8px;">
                <input type="number" id="cfg-retencion" value="<?= $retencion ?>" min="1" max="50" style="width:70px; text-align:center; padding:6px 10px; border:1px solid var(--gray-300); border-radius:var(--radius-sm); font-size:var(--text-md); color:var(--gray-700);">
                <span class="param-unit">respaldos</span>
            </div>
        </div>
        <p style="margin:-8px 0 0; font-size:11px; color:var(--gray-400); padding-left:2px;">
            Los respaldos automáticos más antiguos que excedan esta cantidad serán eliminados.
        </p>
    </div>
</div>

<!-- Historial de Respaldos -->
<div style="margin-top:24px;">
    <div class="table-toolbar" style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px; flex-wrap:wrap; gap:10px;">
        <h3 style="font-size:14px; font-weight:600; color:var(--gray-700); margin:0;">Historial de Respaldos</h3>
        <div style="display:flex; align-items:center; gap:12px; font-size:13px; color:var(--gray-500);">
            Mostrar <select data-perpage-for="tbl-backups" class="per-page-select"><option value="10" selected>10</option><option value="25">25</option><option value="50">50</option></select> filas
            <input type="text" data-search-for="tbl-backups" class="table-search-input" placeholder="Buscar respaldo..." style="padding:6px 12px; border:1px solid var(--gray-200); border-radius:8px; font-size:13px; width:180px;">
        </div>
    </div>
    <div class="table-container">
        <table class="data-table data-table--sm" id="tbl-backups">
            <thead>
                <tr>
                    <th class="sortable" data-col="0">Fecha y Hora</th>
                    <th class="sortable" data-col="1">Tamaño</th>
                    <th class="sortable" data-col="2">Tipo</th>
                    <th style="width:90px">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($backups as $bk):
                    $dt = new \DateTime();
                    $dt->setTimestamp($bk['date']);
                    $bkDate = $dt->format('d') . ' ' . $meses[(int)$dt->format('n') - 1] . ' ' . $dt->format('Y');
                    $bkTime = $dt->format('H:i');
                    $bkSize = round($bk['size'] / 1024 / 1024, 1);
                    $bkTipo = $bk['tipo'];
                    $tipoBg    = $bkTipo === 'Automático' ? 'var(--blue-50)'  : 'var(--green-50)';
                    $tipoColor = $bkTipo === 'Automático' ? 'var(--blue-600)' : 'var(--green-600)';
                    $searchText = strtolower("$bkDate $bkTime $bkTipo {$bk['filename']}");
                ?>
                <tr data-search="<?= $searchText ?>">
                            <td style="font-size:13px;">
                                <strong><?= $bkDate ?></strong>
                                <span style="color:var(--gray-400); margin-left:6px;"><?= $bkTime ?></span>
                            </td>
                            <td style="font-size:13px; color:var(--gray-500);"><?= $bkSize ?> MB</td>
                            <td><span class="status-badge" style="background:<?= $tipoBg ?>; color:<?= $tipoColor ?>; font-size:11px;"><?= e($bkTipo) ?></span></td>
                            <td>
                                <div class="row-actions">
                                    <a class="row-action-btn" title="Descargar" href="<?= base_url('/admin/configuracion/backup/descargar?file=' . urlencode($bk['filename'])) ?>">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                            <polyline points="7 10 12 15 17 10"/>
                                            <line x1="12" y1="15" x2="12" y2="3"/>
                                        </svg>
                                    </a>
                                    <button class="row-action-btn" title="Restaurar base de datos" style="color:var(--blue-600);"
                                        onclick="restaurarRespaldo('<?= e($bk['filename']) ?>')">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="1 4 1 10 7 10"/>
                                            <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/>
                                        </svg>
                                    </button>
                                    <button class="row-action-btn" title="Eliminar" style="color:var(--red-500);"
                                        onclick="eliminarRespaldo('<?= e($bk['filename']) ?>', this)">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="3 6 5 6 21 6"/>
                                            <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                                            <path d="M10 11v6"/><path d="M14 11v6"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="table-footer" data-footer-for="tbl-backups">
        <span class="table-footer-info"></span>
        <div class="pagination"></div>
    </div>
</div>

<script>

// ── Guardar configuración ──
function guardarConfiguracion() {
    const btn = document.getElementById('btn-guardar');
    btn.disabled = true;

    const body = new URLSearchParams({
        csrf_token: '<?= \App\Core\Csrf::getToken() ?>',
        backup_auto_enabled: document.getElementById('cfg-auto-enabled').checked ? '1' : '0',
        backup_frecuencia:   document.getElementById('cfg-frecuencia').value,
        backup_hora:         document.getElementById('cfg-hora').value,
        backup_dia:          document.getElementById('cfg-dia').value,
        backup_retencion:    document.getElementById('cfg-retencion').value
    });

    fetch('<?= base_url('/admin/configuracion/parametros/guardar') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body.toString()
    })
    .then(r => r.json())
    .then(data => {
        showToast(data.message, data.success ? 'success' : 'error');
        btn.disabled = false;
    })
    .catch(() => {
        showToast('Error de conexión con el servidor.', 'error');
        btn.disabled = false;
    });
}

// ── Generar respaldo manual ──
function generarRespaldo() {
    const btn = document.getElementById('btn-backup');
    const txt = document.getElementById('btn-backup-text');
    btn.disabled = true;
    txt.textContent = 'Generando...';

    fetch('<?= base_url('/admin/configuracion/backup') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'csrf_token=<?= \App\Core\Csrf::getToken() ?>'
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success', 5000);
            setTimeout(() => location.reload(), 2000);
        } else {
            showToast(data.message, 'error');
            btn.disabled = false;
            txt.textContent = 'Generar Respaldo (.sql)';
        }
    })
    .catch(() => {
        showToast('Error de conexión con el servidor.', 'error');
        btn.disabled = false;
        txt.textContent = 'Generar Respaldo (.sql)';
    });
}

// ── Eliminar respaldo ──
async function eliminarRespaldo(filename, btn) {
    const confirmed = await window.showConfirm({
        title: 'Eliminar Respaldo',
        message: '¿Eliminar este respaldo? Esta acción no se puede deshacer.',
        icon: 'danger',
        confirmText: 'Eliminar',
        confirmStyle: 'danger'
    });
    if (!confirmed) return;

    const row = btn.closest('tr');

    fetch('<?= base_url('/admin/configuracion/backup/eliminar') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'file=' + encodeURIComponent(filename) + '&csrf_token=<?= \App\Core\Csrf::getToken() ?>'
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            row.remove();
            showToast('Respaldo eliminado correctamente.', 'success');
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(() => showToast('Error de conexión con el servidor.', 'error'));
}

// ── Restaurar respaldo (doble confirmación) ──
async function restaurarRespaldo(filename) {
    // Paso 1: Advertencia inicial
    const paso1 = await window.showConfirm({
        title: 'Restaurar Base de Datos',
        message: '<strong>¿Desea restaurar la base de datos desde este respaldo?</strong><br><br>' +
                 '<code style="background:var(--gray-100); padding:4px 8px; border-radius:4px; font-size:12px;">' + filename + '</code><br><br>' +
                 '<span style="color:var(--red-500); font-weight:600;">⚠ ADVERTENCIA: esto sobrescribirá TODOS los datos actuales de la base de datos.</span>',
        icon: 'warning',
        confirmText: 'Continuar',
        confirmStyle: 'warning'
    });
    if (!paso1) return;

    // Paso 2: Confirmación final con peligro
    const paso2 = await window.showConfirm({
        title: '¿Está completamente seguro?',
        message: '<span style="font-size:14px;">Esta acción es <strong style="color:var(--red-500);">IRREVERSIBLE</strong>. ' +
                 'Todos los datos actuales serán reemplazados por los del respaldo.</span><br><br>' +
                 '<span style="font-size:13px; color:var(--gray-500);">Se recomienda descargar un respaldo actual antes de continuar.</span>',
        icon: 'danger',
        confirmText: 'Sí, restaurar ahora',
        confirmStyle: 'danger',
        cancelText: 'No, cancelar'
    });
    if (!paso2) return;

    // Ejecutar restauración
    try {
        const res = await fetch('<?= base_url('/admin/configuracion/backup/restaurar') ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'file=' + encodeURIComponent(filename) + '&csrf_token=<?= \App\Core\Csrf::getToken() ?>'
        });
        const data = await res.json();

        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => location.reload(), 2000);
        } else {
            showToast(data.message || 'Error al restaurar.', 'error');
        }
    } catch (e) {
        showToast('Error de conexión con el servidor.', 'error');
    }
}
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/logged_layout.php';
?>