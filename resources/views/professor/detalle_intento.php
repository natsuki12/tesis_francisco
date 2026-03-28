<?php
declare(strict_types=1);

// ARCHIVO: resources/views/professor/detalle_intento.php

$pageTitle = 'Detalle de Intento — Simulador SENIAT';
$activePage = 'entregas';
$extraCss = '<link rel="stylesheet" href="' . asset('css/professor/detalle_intento.css') . '">';

// ── $intento y $comparacion vienen del route ──

$nombres = $intento['estudiante_nombres'] ?? 'Sin nombre';
$apellidos = $intento['estudiante_apellidos'] ?? '';
$fullName = trim("{$nombres} {$apellidos}");

$cedula = $intento['estudiante_cedula']
    ? ($intento['estudiante_nacionalidad'] ?? 'V') . '-' . number_format((float) $intento['estudiante_cedula'], 0, ',', '.')
    : 'S/C';

$fechaEnvio = $intento['fecha_envio'] ? date('d/m/Y H:i', strtotime($intento['fecha_envio'])) : '—';

$estadoLabel = match ($intento['estado']) {
    'Enviado' => 'Enviado',
    'Aprobado' => 'Aprobado',
    'Rechazado' => 'No Aprobado',
    'En_Progreso' => 'En Progreso',
    default => ucfirst(str_replace('_', ' ', $intento['estado'])),
};

$statusClass = match ($estadoLabel) {
    'Enviado' => 'status-enviado',
    'Aprobado' => 'status-calificado',
    'No Aprobado' => 'status-danger',
    'En Progreso' => 'status-progreso',
    default => 'status-enviado',
};

$tipoCalificacion = $intento['tipo_calificacion'] ?? 'aprobado_reprobado';
$notaNum = $intento['nota_numerica'];
$notaCual = $intento['nota_cualitativa'];
$observacion = $intento['observacion'] ?? '';
$isCalificado = $intento['estado'] === 'Aprobado' || $intento['estado'] === 'Rechazado';

// Comparación — normalizar claves para evitar crashes
$score = $comparacion['score'] ?? [];
$score += ['correctas' => 0, 'con_errores' => 0, 'omitidas' => 0, 'de_mas' => 0, 'total_esperado' => 0, 'porcentaje' => 0];
$resumenSecciones = $comparacion['resumen_secciones'] ?? [];
$secciones = $comparacion['secciones'] ?? [];
$autoItems = $comparacion['autoliquidacion'] ?? [];
$herederosCalc = $comparacion['herederos_calculo'] ?? [];
$hayComparacion = $score['total_esperado'] > 0;

ob_start();
?>

<!-- Breadcrumb -->
<div class="breadcrumb">
    <a href="<?= base_url('/entregas') ?>">Entregas</a>
    <span class="breadcrumb-sep">›</span>
    <span class="breadcrumb-current">Intento #<?= $intento['numero_intento'] ?> — <?= htmlspecialchars($fullName) ?></span>
</div>

<!-- Intento Header -->
<div class="intento-header animate-in">
    <div class="intento-header-left">
        <h2><?= htmlspecialchars($intento['caso_titulo'] ?? 'Sin caso') ?></h2>
        <p><?= htmlspecialchars($fullName) ?> · <?= htmlspecialchars($cedula) ?></p>
        <div class="intento-meta">
            <span class="intento-meta-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" /><circle cx="9" cy="7" r="4" />
                </svg>
                Sección: <strong><?= htmlspecialchars($intento['seccion'] ?? '—') ?></strong>
            </span>
            <span class="intento-meta-item">
                Intento <strong>#<?= $intento['numero_intento'] ?> de <?= $intento['max_intentos'] ?></strong>
            </span>
            <span class="intento-meta-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                Enviado: <strong><?= $fechaEnvio ?></strong>
            </span>
        </div>
    </div>
    <span class="intento-status-big <?= $statusClass ?>"><?= htmlspecialchars($estadoLabel) ?></span>
</div>

<!-- Content Layout (70 / 30) -->
<div class="correction-layout">

    <!-- ═══ MAIN AREA ═══ -->
    <div class="correction-main">

        <!-- PDF Links -->
        <div class="sidebar-panel animate-in">
            <div class="sidebar-panel-header">
                <h3>Documentos del Intento</h3>
            </div>
            <div class="sidebar-panel-body">
                <div style="display:flex; gap:12px; flex-wrap:wrap;">
                    <a href="<?= base_url('/planilla-sucesoral?intento_id=' . $intento['intento_id']) ?>"
                       target="_blank" class="btn btn-outline" style="text-decoration:none;">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                        </svg>
                        Planilla DS-99032
                    </a>
                    <a href="<?= base_url('/resumen-declaracion?intento_id=' . $intento['intento_id']) ?>"
                       target="_blank" class="btn btn-outline" style="text-decoration:none;">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                            <line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/>
                        </svg>
                        Resumen de Declaración
                    </a>
                </div>
            </div>
        </div>

        <?php if ($hayComparacion): ?>
        <!-- Score Summary -->
        <div class="sidebar-panel animate-in" style="margin-top:16px;">
            <div class="sidebar-panel-header">
                <h3>Resumen de Comparación</h3>
                <span class="score-badge <?= $score['porcentaje'] >= 70 ? 'score-pass' : 'score-fail' ?>">
                    <?= $score['porcentaje'] ?>% correcto
                </span>
            </div>
            <div class="sidebar-panel-body" style="padding:0;">
                <table class="resumen-table">
                    <thead>
                        <tr>
                            <th>Sección</th>
                            <th class="col-num">✓</th>
                            <th class="col-num">✗</th>
                            <th class="col-num">Omitidas</th>
                            <th class="col-num">De más</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($resumenSecciones as $rs): ?>
                        <tr>
                            <td><?= htmlspecialchars($rs['nombre']) ?></td>
                            <td class="col-num text-green"><?= $rs['correctas'] ?></td>
                            <td class="col-num text-red"><?= $rs['con_errores'] ?></td>
                            <td class="col-num text-amber"><?= $rs['omitidas'] ?></td>
                            <td class="col-num text-gray"><?= $rs['de_mas'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td><strong>Total</strong></td>
                            <td class="col-num text-green"><strong><?= $score['correctas'] ?? 0 ?></strong></td>
                            <td class="col-num text-red"><strong><?= $score['con_errores'] ?? 0 ?></strong></td>
                            <td class="col-num text-amber"><strong><?= $score['omitidas'] ?? 0 ?></strong></td>
                            <td class="col-num text-gray"><strong><?= $score['de_mas'] ?? 0 ?></strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Detailed Comparison Accordions -->
        <div class="sidebar-panel animate-in" style="margin-top:16px;">
            <div class="sidebar-panel-header">
                <h3>Detalle por Sección</h3>
            </div>
            <div class="sidebar-panel-body" style="padding:8px 0;">
                <?php foreach ($secciones as $i => $seccion):
                    $totalCampos = 0; $errores = 0;
                    foreach ($seccion['grupos'] as $g) {
                        if ($g['label'] === 'Cantidad') continue;
                        foreach ($g['campos'] as $c) {
                            $totalCampos++;
                            if (!$c['correcto']) $errores++;
                        }
                    }
                ?>
                <div class="accordion" id="accordion-<?= $i ?>">
                    <div class="accordion-header" onclick="toggleAccordion(<?= $i ?>)">
                        <span class="accordion-title">
                            <?= htmlspecialchars($seccion['titulo']) ?>
                            <?php if ($errores === 0): ?>
                                <span class="accordion-badge badge-correct">Todo correcto ✓</span>
                            <?php else: ?>
                                <span class="accordion-badge badge-errors"><?= $errores ?> error<?= $errores > 1 ? 'es' : '' ?></span>
                            <?php endif; ?>
                        </span>
                        <svg class="accordion-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                            <polyline points="6 9 12 15 18 9"/>
                        </svg>
                    </div>
                    <div class="accordion-body">
                        <div class="accordion-content">
                            <?php foreach ($seccion['grupos'] as $g): ?>
                                <div class="grupo-header"><?= htmlspecialchars($g['label']) ?></div>
                                <table class="comparison-table">
                                    <thead>
                                        <tr>
                                            <th>Campo</th>
                                            <th>Declarado</th>
                                            <th>Esperado</th>
                                            <th style="width:32px;"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($g['campos'] as $c): ?>
                                        <tr>
                                            <td class="field-label"><?= htmlspecialchars($c['campo']) ?></td>
                                            <td class="<?= $c['correcto'] ? 'cell-correct' : 'cell-error' ?>">
                                                <?= htmlspecialchars($c['borrador'] ?? '—') ?>
                                            </td>
                                            <td><?= htmlspecialchars($c['esperado'] ?? '—') ?></td>
                                            <td class="cell-match-icon <?= $c['correcto'] ? 'match-ok' : 'match-fail' ?>">
                                                <?php if ($c['correcto']): ?>
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
                                                <?php else: ?>
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>

                <?php if (!empty($autoItems)): ?>
                <!-- Autoliquidación -->
                <div class="accordion" id="accordion-auto">
                    <div class="accordion-header" onclick="document.getElementById('accordion-auto').classList.toggle('accordion--open')">
                        <span class="accordion-title">
                            Autoliquidación
                            <?php
                                $autoErrors = count(array_filter($autoItems, fn($i) => !$i['correcto']));
                            ?>
                            <?php if ($autoErrors === 0): ?>
                                <span class="accordion-badge badge-correct">Todo correcto ✓</span>
                            <?php else: ?>
                                <span class="accordion-badge badge-errors"><?= $autoErrors ?> error<?= $autoErrors > 1 ? 'es' : '' ?></span>
                            <?php endif; ?>
                        </span>
                        <svg class="accordion-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                            <polyline points="6 9 12 15 18 9"/>
                        </svg>
                    </div>
                    <div class="accordion-body">
                        <div class="accordion-content">
                            <table class="comparison-table">
                                <thead>
                                    <tr>
                                        <th>Concepto</th>
                                        <th>Declarado</th>
                                        <th>Esperado</th>
                                        <th style="width:32px;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($autoItems as $ai): ?>
                                    <tr>
                                        <td class="field-label"><?= htmlspecialchars($ai['campo']) ?></td>
                                        <td class="<?= $ai['correcto'] ? 'cell-correct' : 'cell-error' ?>">
                                            <?= htmlspecialchars($ai['borrador'] ?? '—') ?>
                                        </td>
                                        <td><?= htmlspecialchars($ai['esperado'] ?? '—') ?></td>
                                        <td class="cell-match-icon <?= $ai['correcto'] ? 'match-ok' : 'match-fail' ?>">
                                            <?php if ($ai['correcto']): ?>
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
                </div>
                <?php endif; ?>

                <?php if (!empty($herederosCalc)): ?>
                <!-- Cálculo por Heredero -->
                <div class="accordion" id="accordion-hcalc">
                    <div class="accordion-header" onclick="document.getElementById('accordion-hcalc').classList.toggle('accordion--open')">
                        <span class="accordion-title">
                            Impuesto por Heredero
                            <?php
                                $hcErrors = 0;
                                foreach ($herederosCalc as $h) {
                                    foreach ($h['campos'] as $c) { if (!$c['correcto']) { $hcErrors++; } }
                                }
                            ?>
                            <?php if ($hcErrors === 0): ?>
                                <span class="accordion-badge badge-correct">Todo correcto ✓</span>
                            <?php else: ?>
                                <span class="accordion-badge badge-errors"><?= $hcErrors ?> error<?= $hcErrors > 1 ? 'es' : '' ?></span>
                            <?php endif; ?>
                        </span>
                        <svg class="accordion-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                            <polyline points="6 9 12 15 18 9"/>
                        </svg>
                    </div>
                    <div class="accordion-body">
                        <div class="accordion-content">
                            <?php foreach ($herederosCalc as $h): ?>
                                <div class="grupo-header"><?= htmlspecialchars($h['label'] ?? 'Heredero') ?></div>
                                <table class="comparison-table">
                                    <thead>
                                        <tr>
                                            <th>Campo</th>
                                            <th>Declarado</th>
                                            <th>Esperado</th>
                                            <th style="width:32px;"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($h['campos'] as $c): ?>
                                        <tr>
                                            <td class="field-label"><?= htmlspecialchars($c['campo']) ?></td>
                                            <td class="<?= $c['correcto'] ? 'cell-correct' : 'cell-error' ?>">
                                                <?= htmlspecialchars($c['borrador'] ?? '—') ?>
                                            </td>
                                            <td><?= htmlspecialchars($c['esperado'] ?? '—') ?></td>
                                            <td class="cell-match-icon <?= $c['correcto'] ? 'match-ok' : 'match-fail' ?>">
                                                <?php if ($c['correcto']): ?>
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
                                                <?php else: ?>
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <?php else: ?>
        <!-- No comparison data -->
        <div class="sidebar-panel animate-in" style="margin-top:16px;">
            <div class="sidebar-panel-header"><h3>Comparación de Declaración</h3></div>
            <div class="sidebar-panel-body">
                <div style="text-align:center; padding:2rem 1rem; color:var(--gray-400);">
                    <svg viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" style="margin-bottom:12px; opacity:0.5;">
                        <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
                        <rect x="8" y="2" width="8" height="4" rx="1" ry="1"/><path d="M9 14l2 2 4-4"/>
                    </svg>
                    <p style="font-size:var(--text-sm); font-weight:500; margin-bottom:4px;">Sin datos de comparación</p>
                    <p style="font-size:var(--text-xs); max-width:360px; margin:0 auto; line-height:1.5;">
                        Este intento no tiene datos de declaración para comparar. Puede que aún no haya sido enviado o que el borrador esté vacío.
                    </p>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div>

    <!-- ═══ SIDEBAR (Grading Panel) ═══ -->
    <div class="correction-sidebar">
        <!-- Score -->
        <?php if ($hayComparacion): ?>
        <div class="sidebar-panel">
            <div class="sidebar-panel-body" style="text-align:center;">
                <div class="score-circle <?= $score['porcentaje'] >= 70 ? 'score-pass' : 'score-fail' ?>">
                    <span class="score-circle-value"><?= $score['porcentaje'] ?>%</span>
                </div>
                <div style="margin-top:8px; font-size:var(--text-xs); color:var(--gray-500);">
                    <?= $score['correctas'] ?> de <?= $score['total_esperado'] ?> correctas
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Flash Messages -->
        <?php if (!empty($_SESSION['flash_success'])): ?>
            <div style="padding:12px 16px; background:var(--green-50); border:1px solid var(--green-200); border-radius:var(--radius-sm); font-size:var(--text-sm); color:var(--green-700); margin-bottom:12px;">
                ✓ <?= htmlspecialchars($_SESSION['flash_success']) ?>
            </div>
            <?php unset($_SESSION['flash_success']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['flash_error'])): ?>
            <div style="padding:12px 16px; background:var(--red-50); border:1px solid var(--red-200); border-radius:var(--radius-sm); font-size:var(--text-sm); color:var(--red-700); margin-bottom:12px;">
                ✗ <?= htmlspecialchars($_SESSION['flash_error']) ?>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>

        <form id="form-calificar" method="POST" action="<?= base_url('/entregas/' . $intento['intento_id'] . '/calificar') ?>">

        <!-- Grade Input (always editable) -->
        <div class="sidebar-panel">
            <div class="sidebar-panel-header">
                <h3>Calificación</h3>
                <span class="badge-tipo-cal">
                    <?= $tipoCalificacion === 'numerica' ? 'Numérica (0-20)' : 'Aprobado / Reprobado' ?>
                </span>
            </div>
            <div class="sidebar-panel-body">
                <?php if ($tipoCalificacion === 'numerica'): ?>
                    <div class="grade-input-group">
                        <label for="nota_numerica">Nota (0 - 20)</label>
                        <input type="number" name="nota_numerica" id="nota_numerica"
                               class="grade-input-big" min="0" max="20" step="0.5"
                               placeholder="0.0" required
                               value="<?= $notaNum !== null ? number_format((float) $notaNum, 1) : '' ?>">
                    </div>
                <?php else: ?>
                    <div class="grade-input-group">
                        <label for="nota_cualitativa">Resultado</label>
                        <select name="nota_cualitativa" id="nota_cualitativa" class="grade-select" required>
                            <option value="" disabled <?= $notaCual === null ? 'selected' : '' ?>>Seleccionar…</option>
                            <option value="Aprobado" <?= $notaCual === 'Aprobado' ? 'selected' : '' ?>>Aprobado</option>
                            <option value="Reprobado" <?= $notaCual === 'Reprobado' ? 'selected' : '' ?>>Reprobado</option>
                        </select>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Observations (always editable) -->
        <div class="sidebar-panel">
            <div class="sidebar-panel-header"><h3>Observaciones</h3></div>
            <div class="sidebar-panel-body">
                <textarea name="observacion" class="observations-textarea"
                          placeholder="Comentarios para el estudiante…"><?= htmlspecialchars($observacion) ?></textarea>
            </div>
        </div>

        <!-- Actions -->
        <div class="sidebar-panel">
            <div class="sidebar-actions">
                <button type="button" id="btn-calificar" class="btn btn-primary" style="width:100%; justify-content:center;">
                    <?= $isCalificado ? 'Actualizar Calificación' : 'Calificar y Enviar' ?>
                </button>
                <a href="<?= base_url('/entregas') ?>" class="btn btn-secondary" style="text-decoration:none; text-align:center;">
                    ← Volver a Entregas
                </a>
            </div>
        </div>
        </form>
    </div>
</div>

<!-- Confirmation Modal -->
<div id="modal-confirmar" class="modal-overlay" style="display:none;">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Confirmar Calificación</h3>
        </div>
        <div class="modal-body">
            <p>¿Está seguro de que desea <?= $isCalificado ? 'actualizar' : 'enviar' ?> esta calificación?</p>
            <div class="modal-summary">
                <div class="modal-summary-row">
                    <span class="modal-summary-label">Nota:</span>
                    <span class="modal-summary-value" id="modal-nota-display">—</span>
                </div>
                <div class="modal-summary-row">
                    <span class="modal-summary-label">Observación:</span>
                    <span class="modal-summary-value" id="modal-obs-display" style="white-space:pre-wrap;">—</span>
                </div>
            </div>
            <p style="font-size:var(--text-xs); color:var(--gray-400); margin-top:12px;">
                El estudiante podrá ver esta calificación y observación.
            </p>
        </div>
        <div class="modal-footer">
            <button type="button" id="modal-cancelar" class="btn btn-secondary">Cancelar</button>
            <button type="button" id="modal-confirmar-btn" class="btn btn-primary">Confirmar</button>
        </div>
    </div>
</div>

<script>
(function() {
    window.toggleAccordion = function(index) {
        var acc = document.getElementById('accordion-' + index);
        if (acc) acc.classList.toggle('accordion--open');
    };

    // ── Confirmation Modal ──
    var form = document.getElementById('form-calificar');
    var btnCalificar = document.getElementById('btn-calificar');
    var modal = document.getElementById('modal-confirmar');
    var btnCancelar = document.getElementById('modal-cancelar');
    var btnConfirmar = document.getElementById('modal-confirmar-btn');
    var notaDisplay = document.getElementById('modal-nota-display');
    var obsDisplay = document.getElementById('modal-obs-display');

    btnCalificar.addEventListener('click', function() {
        // Validate form first
        if (!form.reportValidity()) return;

        // Get nota display value
        var notaInput = document.getElementById('nota_numerica');
        var notaSelect = document.getElementById('nota_cualitativa');
        if (notaInput) {
            notaDisplay.textContent = notaInput.value + ' / 20';
        } else if (notaSelect) {
            notaDisplay.textContent = notaSelect.options[notaSelect.selectedIndex].text;
        }

        // Get observation
        var obs = form.querySelector('textarea[name="observacion"]').value.trim();
        obsDisplay.textContent = obs || '(Sin observaciones)';

        modal.style.display = 'flex';
    });

    btnCancelar.addEventListener('click', function() {
        modal.style.display = 'none';
    });

    btnConfirmar.addEventListener('click', function() {
        form.submit();
    });

    // Close on overlay click
    modal.addEventListener('click', function(e) {
        if (e.target === modal) modal.style.display = 'none';
    });
})();
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/logged_layout.php';
?>