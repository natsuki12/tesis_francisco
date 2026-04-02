<?php
/**
 * PDF Comparison Template — Rendered by mPDF
 *
 * Variables available:
 *   $datos           — array with caso metadata
 *   $secciones       — array of sections with grupos/campos comparisons
 *   $autoItems       — array of autoliquidación comparison rows
 *   $herederosCalc   — array of per-heredero tax calculation
 *   $score           — array{correctos, total, porcentaje}
 */

$scoreColor = $score['porcentaje'] >= 80 ? '#28a745' : ($score['porcentaje'] >= 50 ? '#fd7e14' : '#dc3545');
?>
<style>
    body { font-family: 'dejavusans', sans-serif; font-size: 10pt; color: #222; }
    h1 { font-size: 16pt; text-align: center; margin: 0 0 4px; color: #333; }
    h2 { font-size: 12pt; margin: 14px 0 6px; padding: 4px 8px; background: #e9ecef; border-left: 4px solid #d32f2f; }
    h3 { font-size: 10pt; margin: 8px 0 3px; color: #444; padding: 2px 4px; background: #f8f9fa; border-bottom: 1px solid #ddd; }
    .subtitle { text-align: center; font-size: 9pt; color: #666; margin: 0 0 12px; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
    th, td { border: 1px solid #ccc; padding: 3px 5px; font-size: 8.5pt; }
    th { background: #f5f5f5; text-align: left; font-weight: bold; }
    .text-end { text-align: right; }
    .text-center { text-align: center; }
    .ok { color: #28a745; font-weight: bold; font-size: 11pt; }
    .fail { color: #dc3545; font-weight: bold; font-size: 11pt; }
    .row-fail { background: #fff3f3; }
    .row-ok { background: #f0fff0; }
    .score-box {
        text-align: center; padding: 10px; margin: 8px 0;
        border: 2px solid <?= $scoreColor ?>;
        border-radius: 8px; background: #fafafa;
    }
    .score-num { font-size: 24pt; font-weight: bold; color: <?= $scoreColor ?>; }
    .score-label { font-size: 10pt; color: #555; }
    .meta-table td { border: none; padding: 2px 8px; font-size: 9pt; }
    .meta-label { font-weight: bold; color: #555; width: 180px; }
    .no-data { color: #999; font-style: italic; font-size: 9pt; padding: 4px; }
</style>

<h1>Reporte de Comparación de Declaración</h1>
<div class="subtitle">Sistema de Práctica de Declaración Sucesoral SENIAT (SUCELAB)</div>

<!-- ═══ Datos del Caso ═══ -->
<table class="meta-table">
    <tr>
        <td class="meta-label">Caso:</td>
        <td><?= htmlspecialchars($datos['nombre_caso'] ?? '') ?></td>
        <td class="meta-label">RIF:</td>
        <td><?= htmlspecialchars($datos['rif_sucesion'] ?? '') ?></td>
    </tr>
    <tr>
        <td class="meta-label">Causante:</td>
        <td><?= htmlspecialchars($datos['nombre_causante'] ?? '') ?></td>
        <td class="meta-label">Fecha Fallecimiento:</td>
        <td><?= htmlspecialchars($datos['fecha_fallecimiento'] ?? '') ?></td>
    </tr>
    <tr>
        <td class="meta-label">Fecha del Reporte:</td>
        <td><?= htmlspecialchars($datos['fecha_declaracion'] ?? '') ?></td>
        <td class="meta-label">UT Aplicable:</td>
        <td><?= htmlspecialchars($datos['ut_aplicable'] ?? '') ?></td>
    </tr>
</table>

<!-- ═══ Score ═══ -->
<div class="score-box">
    <div class="score-num"><?= $score['correctos'] ?> / <?= $score['total'] ?></div>
    <div class="score-label">Campos correctos (<?= $score['porcentaje'] ?>%)</div>
</div>

<!-- ═══ Secciones detalladas ═══ -->
<?php foreach ($secciones as $seccion): ?>
    <h2><?= htmlspecialchars($seccion['titulo']) ?></h2>

    <?php foreach ($seccion['grupos'] as $grupo): ?>
        <h3><?= htmlspecialchars($grupo['label']) ?></h3>
        <table>
            <thead>
                <tr>
                    <th style="width:32%">Campo</th>
                    <th class="text-end" style="width:26%">Su Valor</th>
                    <th class="text-end" style="width:26%">Valor Correcto</th>
                    <th class="text-center" style="width:16%">Resultado</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($grupo['campos'] as $c): ?>
                <tr class="<?= $c['correcto'] ? 'row-ok' : 'row-fail' ?>">
                    <td><?= htmlspecialchars($c['campo']) ?></td>
                    <td class="text-end"><?= htmlspecialchars($c['borrador']) ?></td>
                    <td class="text-end"><?= htmlspecialchars($c['esperado']) ?></td>
                    <td class="text-center"><?= $c['correcto'] ? '<span class="ok">✓</span>' : '<span class="fail">✗</span>' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endforeach; ?>
<?php endforeach; ?>

<!-- ═══ Autoliquidación ═══ -->
<h2>Autoliquidación del Impuesto</h2>
<table>
    <thead>
        <tr>
            <th style="width:40%">Concepto</th>
            <th class="text-end" style="width:22%">Su Valor (Bs)</th>
            <th class="text-end" style="width:22%">Valor Correcto (Bs)</th>
            <th class="text-center" style="width:16%">Resultado</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($autoItems as $item): ?>
        <tr class="<?= $item['correcto'] ? 'row-ok' : 'row-fail' ?>">
            <td><?= htmlspecialchars($item['campo']) ?></td>
            <td class="text-end"><?= htmlspecialchars($item['borrador']) ?></td>
            <td class="text-end"><?= htmlspecialchars($item['esperado']) ?></td>
            <td class="text-center"><?= $item['correcto'] ? '<span class="ok">✓</span>' : '<span class="fail">✗</span>' ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- ═══ Cálculo por Heredero ═══ -->
<h2>Determinación del Impuesto por Heredero</h2>

<?php if (empty($herederosCalc)): ?>
    <p class="no-data">No se encontraron herederos en el borrador.</p>
<?php else: ?>
    <?php foreach ($herederosCalc as $h): ?>
    <h3><?= htmlspecialchars($h['nombre']) ?> — <?= htmlspecialchars($h['cedula']) ?> (<?= htmlspecialchars($h['parentesco']) ?>)</h3>
    <table>
        <thead>
            <tr>
                <th style="width:40%">Campo</th>
                <th class="text-end" style="width:22%">Su Valor</th>
                <th class="text-end" style="width:22%">Valor Correcto</th>
                <th class="text-center" style="width:16%">Resultado</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($h['campos'] as $c): ?>
            <tr class="<?= $c['correcto'] ? 'row-ok' : 'row-fail' ?>">
                <td><?= htmlspecialchars($c['campo']) ?></td>
                <td class="text-end"><?= htmlspecialchars($c['borrador']) ?></td>
                <td class="text-end"><?= htmlspecialchars($c['esperado']) ?></td>
                <td class="text-center"><?= $c['correcto'] ? '<span class="ok">✓</span>' : '<span class="fail">✗</span>' ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endforeach; ?>
<?php endif; ?>

<div style="text-align:center; margin-top:14px; font-size:8pt; color:#999;">
    Generado por SUCELAB — <?= date('d/m/Y H:i:s') ?>
</div>
