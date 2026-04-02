<?php
declare(strict_types=1);

// ARCHIVO: resources/views/professor/generacion_rs_detalle.php
// Vista de revisión campo a campo para RIF Sucesoral.
// Usa tabs para separar secciones: Causante, Relaciones, Direcciones.

$pageTitle = 'Revisión de R.S. — Simulador SENIAT';
$activePage = 'generacion-rs';
$extraCss = '<link rel="stylesheet" href="' . asset('css/professor/detalle_intento.css') . '">
<link rel="stylesheet" href="' . asset('css/professor/generacion_rs_detalle.css') . '">';

// Variables del controller
$intento     = $intento ?? [];
$causante    = $causante ?? ['campos' => [], 'vacio' => true];
$relaciones  = $relaciones ?? ['representante' => [], 'herederos' => []];
$direcciones = $direcciones ?? [];

$estNombre   = trim(($intento['est_nombres'] ?? '') . ' ' . ($intento['est_apellidos'] ?? ''));
$estCedula   = ($intento['est_nacionalidad'] ?? 'V') . '-' . number_format((float) ($intento['est_cedula'] ?? 0), 0, ',', '.');
$esPendiente = ($intento['estado'] ?? '') === 'Pendiente_RIF';
$esAprobado  = ($intento['estado'] ?? '') === 'Aprobado';
$esRechazado = ($intento['estado'] ?? '') === 'Rechazado';
$tipoCalif   = $intento['tipo_calificacion'] ?? 'aprobado_reprobado';

// ── Totales por sección ──
$causanteTotal = 0; $causanteOk = 0;
if (!($causante['vacio'] ?? true)) {
    foreach ($causante['campos'] as $c) { $causanteTotal++; if ($c['coincide']) $causanteOk++; }
}

$relacionesTotal = 0; $relacionesOk = 0;
foreach ($relaciones['representante'] ?? [] as $c) { $relacionesTotal++; if ($c['coincide']) $relacionesOk++; }
foreach ($relaciones['herederos'] ?? [] as $h) {
    $hCampos = $h['campos'] ?? $h;
    foreach ($hCampos as $c) { if (is_array($c)) { $relacionesTotal++; if ($c['coincide']) $relacionesOk++; } }
}

$direccionesTotal = 0; $direccionesOk = 0;
foreach ($direcciones ?? [] as $campos) {
    foreach ($campos as $c) { $direccionesTotal++; if ($c['coincide']) $direccionesOk++; }
}

$totalCampos = $causanteTotal + $relacionesTotal + $direccionesTotal;
$totalCorrectos = $causanteOk + $relacionesOk + $direccionesOk;

ob_start();
?>

<!-- Breadcrumb -->
<div class="breadcrumb">
    <a href="<?= base_url('/generacion-rs') ?>">Generación de R.S.</a>
    <span class="breadcrumb-sep">›</span>
    <span class="breadcrumb-current">Revisión — <?= htmlspecialchars($estNombre) ?></span>
</div>

<!-- Info Card -->
<div class="intento-header">
    <div class="intento-header-left">
        <h2>Revisión de RIF Sucesoral</h2>
        <p><?= htmlspecialchars($estNombre) ?> (<?= htmlspecialchars($estCedula) ?>)</p>
        <div class="intento-meta">
            <span class="intento-meta-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>
                Caso: <strong><?= htmlspecialchars($intento['caso_titulo'] ?? '—') ?></strong>
            </span>
            <span class="intento-meta-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                Intento: <strong>#<?= (int) ($intento['numero_intento'] ?? 1) ?> de <?= (int) ($intento['max_intentos'] ?? 1) ?></strong>
            </span>
            <?php if ($intento['submitted_at'] ?? ''): ?>
            <span class="intento-meta-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                Enviado: <strong><?= date('d/m/Y H:i', strtotime($intento['submitted_at'])) ?></strong>
            </span>
            <?php endif; ?>
        </div>
    </div>
    <?php if ($totalCampos > 0): ?>
    <div class="rs-match-badge <?= $totalCorrectos === $totalCampos ? 'badge-all-ok' : '' ?>">
        <span class="rs-match-num"><?= $totalCorrectos ?></span>
        <span class="rs-match-sep">/</span>
        <span class="rs-match-total"><?= $totalCampos ?></span>
        <span class="rs-match-label">correctos</span>
    </div>
    <?php endif; ?>
</div>

<?php if ($esAprobado && ($intento['rif_sucesoral'] ?? '')): ?>
<div class="rif-banner rif-banner--aprobado">
    <div class="rif-banner-label">RIF Sucesoral Generado</div>
    <div class="rif-banner-value"><?= htmlspecialchars($intento['rif_sucesoral']) ?></div>
    <div class="rif-banner-date">Aprobado el <?= $intento['approved_at'] ? date('d/m/Y H:i', strtotime($intento['approved_at'])) : '—' ?></div>
</div>
<?php elseif ($esRechazado): ?>
<div class="rif-banner rif-banner--rechazado">
    <div class="rif-banner-label">Solicitud Rechazada</div>
    <?php $nota = $intento['nota_cualitativa'] ?? ($intento['nota_numerica'] !== null ? (string) $intento['nota_numerica'] : '—'); ?>
    <div class="rif-banner-meta">Nota: <?= htmlspecialchars($nota) ?> — Revisado el <?= $intento['reviewed_at'] ? date('d/m/Y H:i', strtotime($intento['reviewed_at'])) : '—' ?></div>
    <?php if ($intento['observacion'] ?? ''): ?>
        <div class="rif-banner-obs"><?= htmlspecialchars($intento['observacion']) ?></div>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- ═══ TABS ═══ -->
<div class="rs-tabs-bar">
    <button class="rs-tab rs-tab--active" data-target="panel-causante">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        Causante
        <span class="rs-tab-badge <?= $causanteTotal > 0 && $causanteOk === $causanteTotal ? 'badge-ok' : 'badge-err' ?>"><?= $causanteOk ?>/<?= $causanteTotal ?></span>
    </button>
    <button class="rs-tab" data-target="panel-relaciones">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        Relaciones
        <span class="rs-tab-badge <?= $relacionesTotal > 0 && $relacionesOk === $relacionesTotal ? 'badge-ok' : 'badge-err' ?>"><?= $relacionesOk ?>/<?= $relacionesTotal ?></span>
    </button>
    <button class="rs-tab" data-target="panel-direcciones">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
        Direcciones
        <span class="rs-tab-badge <?= $direccionesTotal > 0 && $direccionesOk === $direccionesTotal ? 'badge-ok' : 'badge-err' ?>"><?= $direccionesOk ?>/<?= $direccionesTotal ?></span>
    </button>
</div>

<!-- ═══ PANEL: CAUSANTE ═══ -->
<div class="rs-panel-content" id="panel-causante">
    <?php if ($causante['vacio'] ?? true): ?>
        <div class="rs-panel-empty">El estudiante no completó los datos del causante.</div>
    <?php else: ?>
        <table class="comparison-table">
            <thead>
                <tr>
                    <th>Campo</th>
                    <th>Esperado (Caso)</th>
                    <th>Ingresado (Estudiante)</th>
                    <th style="width:32px;"></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($causante['campos'] as $campo): ?>
                <tr>
                    <td class="field-label"><?= htmlspecialchars($campo['label']) ?></td>
                    <td><?= htmlspecialchars($campo['esperado'] ?: '—') ?></td>
                    <td class="<?= $campo['coincide'] ? 'cell-correct' : 'cell-error' ?>"><?= htmlspecialchars($campo['ingresado'] ?: '—') ?></td>
                    <td class="cell-match-icon <?= $campo['coincide'] ? 'match-ok' : 'match-fail' ?>">
                        <?php if ($campo['coincide']): ?>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
                        <?php else: ?>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- ═══ PANEL: RELACIONES ═══ -->
<div class="rs-panel-content" id="panel-relaciones" style="display:none;">
    <?php if (!empty($relaciones['representante']) || !empty($relaciones['herederos'])): ?>
        <table class="comparison-table">
            <thead>
                <tr>
                    <th>Campo</th>
                    <th>Esperado (Caso)</th>
                    <th>Ingresado (Estudiante)</th>
                    <th style="width:32px;"></th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($relaciones['representante'])): ?>
                    <tr class="rs-group-row"><td colspan="4">Representante de la Sucesión</td></tr>
                    <?php foreach ($relaciones['representante'] as $campo): ?>
                    <tr>
                        <td class="field-label"><?= htmlspecialchars($campo['label']) ?></td>
                        <td><?= htmlspecialchars($campo['esperado'] ?: '—') ?></td>
                        <td class="<?= $campo['coincide'] ? 'cell-correct' : 'cell-error' ?>"><?= htmlspecialchars($campo['ingresado'] ?: '—') ?></td>
                        <td class="cell-match-icon <?= $campo['coincide'] ? 'match-ok' : 'match-fail' ?>">
                            <?php if ($campo['coincide']): ?>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
                            <?php else: ?>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>

                <?php foreach ($relaciones['herederos'] as $idx => $heredero): ?>
                    <?php
                        $tipo = $heredero['tipo'] ?? 'match';
                        $campos = $heredero['campos'] ?? $heredero; // Backwards compat
                        if ($tipo === 'faltante') {
                            $label = 'Heredero — No ingresado por el estudiante';
                        } elseif ($tipo === 'extra') {
                            $label = 'Heredero Extra — No existe en el caso';
                        } else {
                            $label = 'Heredero #' . ($idx + 1);
                        }
                    ?>
                    <tr class="rs-group-row"><td colspan="4"><?= $label ?></td></tr>
                    <?php foreach ($campos as $campo): ?>
                    <tr>
                        <td class="field-label"><?= htmlspecialchars($campo['label']) ?></td>
                        <td><?= htmlspecialchars($campo['esperado'] ?: '—') ?></td>
                        <td class="<?= $campo['coincide'] ? 'cell-correct' : 'cell-error' ?>"><?= htmlspecialchars($campo['ingresado'] ?: '—') ?></td>
                        <td class="cell-match-icon <?= $campo['coincide'] ? 'match-ok' : 'match-fail' ?>">
                            <?php if ($campo['coincide']): ?>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
                            <?php else: ?>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="rs-panel-empty">No se encontraron relaciones en el borrador.</div>
    <?php endif; ?>
</div>

<!-- ═══ PANEL: DIRECCIONES ═══ -->
<div class="rs-panel-content" id="panel-direcciones" style="display:none;">
    <?php if (empty($direcciones)): ?>
        <div class="rs-panel-empty">No se encontraron direcciones en el borrador.</div>
    <?php else: ?>
        <?php foreach ($direcciones as $idx => $campos): ?>
            <?php
                $dirOk = 0; $dirTotal = count($campos);
                foreach ($campos as $c) { if ($c['coincide']) $dirOk++; }
                $dirTieneErrores = ($dirOk < $dirTotal);
            ?>
            <div class="rs-dir-card <?= $dirTieneErrores ? 'rs-dir-card--error' : 'rs-dir-card--ok' ?>">
                <button class="rs-dir-toggle" type="button" onclick="this.parentElement.classList.toggle('rs-dir-card--open')">
                    <div class="rs-dir-toggle-left">
                        <svg class="rs-dir-chevron" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="9 18 15 12 9 6"/></svg>
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        Dirección #<?= $idx + 1 ?>
                    </div>
                    <span class="rs-tab-badge <?= $dirTieneErrores ? 'badge-err' : 'badge-ok' ?>"><?= $dirOk ?>/<?= $dirTotal ?></span>
                </button>
                <div class="rs-dir-body">
                    <table class="comparison-table">
                        <thead>
                            <tr>
                                <th>Campo</th>
                                <th>Esperado (Caso)</th>
                                <th>Ingresado (Estudiante)</th>
                                <th style="width:32px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($campos as $campo): ?>
                            <tr>
                                <td class="field-label"><?= htmlspecialchars($campo['label']) ?></td>
                                <td><?= htmlspecialchars($campo['esperado'] ?: '—') ?></td>
                                <td class="<?= $campo['coincide'] ? 'cell-correct' : 'cell-error' ?>"><?= htmlspecialchars($campo['ingresado'] ?: '—') ?></td>
                                <td class="cell-match-icon <?= $campo['coincide'] ? 'match-ok' : 'match-fail' ?>">
                                    <?php if ($campo['coincide']): ?>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
                                    <?php else: ?>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php if ($esPendiente): ?>
<!-- ═══ BARRA DE ACCIONES ═══ -->
<div class="rs-acciones-sticky">
    <button class="btn btn-primary" id="btn-aprobar-detalle">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12" /></svg>
        Aprobar RIF
    </button>
    <button class="btn btn-danger" id="btn-rechazar-detalle">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18" /><line x1="6" y1="6" x2="18" y2="18" /></svg>
        Rechazar
    </button>
</div>

<!-- Modal: Confirmar Aprobación -->
<dialog class="modal-base" id="modal-aprobar">
    <div class="modal-base__container" style="max-width: 420px;">
        <div class="modal-base__header">
            <h2 class="modal-base__title">Aprobar Solicitud</h2>
            <button class="modal-base__close" onclick="window.modalManager.close('modal-aprobar')">✕</button>
        </div>
        <div class="modal-base__body">
            <p style="color: var(--gray-600); font-size: var(--text-md); margin: 0;">
                ¿Está seguro que desea aprobar esta solicitud de RIF Sucesoral?
                Se generará un RIF único y el estudiante será notificado por correo electrónico.
            </p>
        </div>
        <div class="modal-base__footer">
            <button class="modal-btn modal-btn-cancel" onclick="window.modalManager.close('modal-aprobar')">Cancelar</button>
            <button class="modal-btn modal-btn-primary" id="btn-confirmar-aprobacion">Sí, aprobar</button>
        </div>
    </div>
</dialog>

<!-- Modal: Rechazar -->
<dialog class="modal-base" id="modal-rechazar">
    <div class="modal-base__container" style="max-width: 500px;">
        <div class="modal-base__header">
            <h2 class="modal-base__title">Rechazar Solicitud</h2>
            <button class="modal-base__close" onclick="window.modalManager.close('modal-rechazar')">✕</button>
        </div>
        <div class="modal-base__body">
            <?php if ($tipoCalif === 'numerica'): ?>
                <div class="reject-field">
                    <label for="nota-rechazo">Nota numérica (0–9)</label>
                    <input type="number" id="nota-rechazo" min="0" max="9" step="0.5" placeholder="0"
                        oninput="if(this.value!==''){var v=parseFloat(this.value);if(v>9)this.value=9;if(v<0)this.value=0;}"
                        style="width: 100%; padding: 10px 14px; border: 1px solid var(--gray-300); border-radius: 8px; font-size: var(--text-md); font-family: var(--font-ui); box-sizing: border-box;">
                    <p class="field-hint">Nota asignada al estudiante por este intento.</p>
                </div>
            <?php else: ?>
                <div class="reject-field">
                    <div style="background: var(--amber-50, #fffbeb); border: 1px solid var(--amber-200, #fde68a); border-radius: 8px; padding: 12px 16px;">
                        <p style="margin: 0; font-size: var(--text-sm); color: var(--amber-700, #92400e);">
                            <strong>📋 Nota:</strong> Al rechazar, se asignará automáticamente la nota <strong>Reprobado</strong>.
                        </p>
                    </div>
                </div>
            <?php endif; ?>
            <div class="reject-field">
                <label for="motivo-rechazo">Observación del rechazo <span style="color: var(--red-500);">*</span></label>
                <textarea id="motivo-rechazo" rows="4" placeholder="Explique al estudiante por qué se rechaza esta solicitud..." required
                    style="width: 100%; box-sizing: border-box;"></textarea>
                <p class="field-hint">Este mensaje será visible para el estudiante y enviado por correo electrónico.</p>
            </div>
        </div>
        <div class="modal-base__footer">
            <button class="modal-btn modal-btn-cancel" onclick="window.modalManager.close('modal-rechazar')">Cancelar</button>
            <button class="modal-btn modal-btn-danger" id="btn-confirmar-rechazo">Rechazar solicitud</button>
        </div>
    </div>
</dialog>
<?php endif; ?>

<!-- JavaScript -->
<script>
(function () {
    var BASE = <?= json_encode(rtrim(base_url(''), '/')) ?>;
    var INTENTO_ID = <?= (int) ($intento['id'] ?? 0) ?>;
    var TIPO_CALIF = <?= json_encode($tipoCalif) ?>;
    var ES_PENDIENTE = <?= $esPendiente ? 'true' : 'false' ?>;

    // ── Tabs ──
    var tabs = document.querySelectorAll('.rs-tab');
    var panels = document.querySelectorAll('.rs-panel-content');

    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            tabs.forEach(function (t) { t.classList.remove('rs-tab--active'); });
            panels.forEach(function (p) { p.style.display = 'none'; });
            tab.classList.add('rs-tab--active');
            var target = document.getElementById(tab.getAttribute('data-target'));
            if (target) target.style.display = 'block';
        });
    });

    // Auto-open direction cards with errors
    document.querySelectorAll('.rs-dir-card--error').forEach(function (card) {
        card.classList.add('rs-dir-card--open');
    });

    if (!ES_PENDIENTE) return;

    // ── Aprobar ──
    document.getElementById('btn-aprobar-detalle').addEventListener('click', function () {
        window.modalManager.open('modal-aprobar');
    });

    var btnAprobar = document.getElementById('btn-confirmar-aprobacion');
    btnAprobar.addEventListener('click', function () {
        btnAprobar.disabled = true;
        btnAprobar.textContent = 'Procesando...';

        fetch(BASE + '/api/generacion-rs/' + INTENTO_ID + '/aprobar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            window.modalManager.close('modal-aprobar');
            btnAprobar.disabled = false;
            btnAprobar.textContent = 'Sí, aprobar';
            if (data.ok) {
                showToast('✅ RIF aprobado: ' + (data.rif || ''), 'success');
                setTimeout(function () { window.location.href = BASE + '/generacion-rs'; }, 1500);
            } else {
                showToast('❌ ' + (data.message || 'Error'), 'error');
            }
        })
        .catch(function () {
            btnAprobar.disabled = false;
            btnAprobar.textContent = 'Sí, aprobar';
            showToast('❌ Error de red.', 'error');
        });
    });

    // ── Rechazar ──
    document.getElementById('btn-rechazar-detalle').addEventListener('click', function () {
        document.getElementById('motivo-rechazo').value = '';
        window.modalManager.open('modal-rechazar');
    });

    var btnRechazar = document.getElementById('btn-confirmar-rechazo');
    btnRechazar.addEventListener('click', function () {
        var obs = document.getElementById('motivo-rechazo').value.trim();
        if (!obs) { document.getElementById('motivo-rechazo').focus(); return; }

        var payload = { observacion: obs };
        if (TIPO_CALIF === 'numerica') {
            var notaVal = document.getElementById('nota-rechazo').value;
            if (notaVal === '') { document.getElementById('nota-rechazo').focus(); showToast('⚠️ Ingrese nota.', 'error'); return; }
            var n = parseFloat(notaVal);
            if (isNaN(n) || n < 0 || n > 9) { showToast('⚠️ Nota entre 0-9.', 'error'); return; }
            payload.nota = n;
        }

        btnRechazar.disabled = true;
        btnRechazar.textContent = 'Procesando...';

        fetch(BASE + '/api/generacion-rs/' + INTENTO_ID + '/rechazar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            window.modalManager.close('modal-rechazar');
            btnRechazar.disabled = false;
            btnRechazar.textContent = 'Rechazar solicitud';
            if (data.ok) {
                showToast('✅ Solicitud rechazada.', 'success');
                setTimeout(function () { window.location.href = BASE + '/generacion-rs'; }, 1500);
            } else {
                showToast('❌ ' + (data.message || 'Error'), 'error');
            }
        })
        .catch(function () {
            btnRechazar.disabled = false;
            btnRechazar.textContent = 'Rechazar solicitud';
            showToast('❌ Error de red.', 'error');
        });
    });

    function showToast(msg, type) {
        if (window.showToast) { window.showToast(msg, type); return; }
        var t = document.createElement('div');
        t.textContent = msg;
        t.style.cssText = 'position:fixed;bottom:24px;right:24px;padding:14px 24px;border-radius:10px;color:#fff;font-size:14px;font-weight:600;z-index:99999;transition:opacity .3s;' + (type === 'success' ? 'background:#059669;' : 'background:#dc2626;');
        document.body.appendChild(t);
        setTimeout(function () { t.style.opacity = '0'; }, 2500);
        setTimeout(function () { t.remove(); }, 3000);
    }
})();
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/logged_layout.php';
?>
