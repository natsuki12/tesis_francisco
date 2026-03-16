<?php
/**
 * PDF Comparison Template — Rendered by mPDF
 *
 * Variables available:
 *   $datos              — array with caso metadata
 *   $secciones          — array of sections with grupos/campos comparisons
 *   $resumenSecciones   — array of per-section score summaries
 *   $autoItems          — array of autoliquidación comparison rows
 *   $herederosCalc      — array of per-heredero tax calculation
 *   $score              — array{correctos, total, porcentaje}
 *   $patrimonioNeto     — float: correct patrimonio neto value
 */

$scoreColor = $score['porcentaje'] >= 70 ? '#38a169' : ($score['porcentaje'] >= 40 ? '#d69e2e' : '#e53e3e');
?>
<style>
    /* ============================================ */
    /* BASE                                         */
    /* ============================================ */
    body {
        font-family: 'dejavusans', sans-serif;
        font-size: 9pt;
        color: #1a1a1a;
        line-height: 1.4;
    }

    /* ============================================ */
    /* ENCABEZADO                                   */
    /* ============================================ */
    .encabezado {
        text-align: center;
        margin-bottom: 10px;
    }
    .encabezado h1 {
        font-size: 14pt;
        color: #1a365d;
        margin: 0 0 2px 0;
    }
    .encabezado p {
        font-size: 8pt;
        color: #555;
        margin: 0;
    }

    .meta-table { width: 100%; border-collapse: collapse; margin: 8px 0; }
    .meta-table td { border: none; padding: 2px 8px; font-size: 9pt; }
    .meta-label { font-weight: bold; color: #4a5568; width: 170px; }

    /* ============================================ */
    /* SCORE GLOBAL                                 */
    /* ============================================ */
    .score-global {
        text-align: center;
        margin: 12px 0;
        padding: 10px;
        background-color: #f8f9fa;
        border: 2px solid <?= $scoreColor ?>;
        border-radius: 6px;
    }
    .score-numero {
        font-size: 22pt;
        font-weight: bold;
        color: <?= $scoreColor ?>;
    }
    .score-label {
        font-size: 10pt;
        color: #666;
        margin-bottom: 6px;
    }
    .barra-contenedor {
        background-color: #e9ecef;
        height: 10px;
        border-radius: 5px;
        margin-top: 6px;
    }
    .barra-relleno {
        background-color: <?= $scoreColor ?>;
        height: 10px;
        border-radius: 5px;
    }

    /* ============================================ */
    /* TABLA RESUMEN POR SECCIÓN                    */
    /* ============================================ */
    .tabla-resumen {
        width: 100%;
        border-collapse: collapse;
        margin: 12px 0;
        font-size: 9pt;
    }
    .tabla-resumen th {
        background-color: #1a365d;
        color: #ffffff;
        padding: 5px 8px;
        font-weight: bold;
        font-size: 8pt;
        text-transform: uppercase;
    }
    .tabla-resumen td {
        padding: 4px 8px;
        border-bottom: 1px solid #e2e8f0;
    }
    .barra-mini-contenedor {
        background-color: #e2e8f0;
        height: 8px;
        border-radius: 4px;
    }
    .barra-mini-relleno {
        height: 8px;
        border-radius: 4px;
    }
    .barra-verde   { background-color: #38a169; }
    .barra-amarilla { background-color: #d69e2e; }
    .barra-roja    { background-color: #e53e3e; }

    /* ============================================ */
    /* SECCIONES DE DETALLE                         */
    /* ============================================ */
    .seccion {
        margin-bottom: 12px;
    }
    .seccion-header {
        background-color: #1a365d;
        color: #ffffff;
        padding: 5px 10px;
        margin-bottom: 0;
    }
    .seccion-titulo {
        font-size: 11pt;
        font-weight: bold;
    }
    .seccion-conteo {
        float: right;
        font-size: 9pt;
        font-weight: normal;
    }

    .item-header {
        background-color: #edf2f7;
        padding: 3px 10px;
        font-size: 9pt;
        font-weight: bold;
        color: #2d3748;
        border-left: 3px solid #3182ce;
        margin: 6px 0 2px 0;
    }

    /* ============================================ */
    /* TABLA DE CAMPOS                              */
    /* ============================================ */
    .tabla-campos {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 4px;
        font-size: 8.5pt;
    }
    .tabla-campos th {
        background-color: #e2e8f0;
        padding: 3px 6px;
        text-align: left;
        font-size: 7.5pt;
        text-transform: uppercase;
        color: #4a5568;
        border-bottom: 2px solid #cbd5e0;
    }
    .tabla-campos td {
        padding: 2px 6px;
        border-bottom: 1px solid #edf2f7;
        vertical-align: top;
    }

    /* ============================================ */
    /* FILAS POR TIPO DE RESULTADO                  */
    /* ============================================ */
    .fila-ok td { background-color: #f0fff4; }
    .fila-ok .icono { color: #38a169; font-weight: bold; }

    .fila-fail td { background-color: #fff5f5; }
    .fila-fail .icono { color: #e53e3e; font-weight: bold; }

    .fila-omitido td { background-color: #fffaf0; }
    .fila-omitido .icono { color: #dd6b20; font-weight: bold; }

    .fila-sobrante td { background-color: #fffff0; }
    .fila-sobrante .icono { color: #d69e2e; font-weight: bold; }

    /* ============================================ */
    /* BADGES                                       */
    /* ============================================ */
    .badge {
        display: inline-block;
        padding: 1px 5px;
        border-radius: 3px;
        font-size: 7pt;
        font-weight: bold;
        text-transform: uppercase;
    }
    .badge-ok {
        background-color: #c6f6d5;
        color: #276749;
    }
    .badge-fail {
        background-color: #fed7d7;
        color: #9b2c2c;
    }
    .badge-omitido {
        background-color: #feebc8;
        color: #9c4221;
    }
    .badge-sobrante {
        background-color: #fefcbf;
        color: #975a16;
    }
    .valor-vacio {
        color: #a0aec0;
        font-style: italic;
    }
    .sobrante-nota {
        background-color: #fffff0;
        border: 1px solid #ecc94b;
        border-left: 3px solid #d69e2e;
        padding: 5px 10px;
        font-size: 8pt;
        color: #744210;
        margin: 2px 0 4px 0;
    }

    /* ============================================ */
    /* AUTOLIQUIDACIÓN ESPECIALES                   */
    /* ============================================ */
    .nota-informativa {
        background-color: #ebf8ff;
        border: 1px solid #90cdf4;
        border-left: 3px solid #3182ce;
        padding: 6px 10px;
        font-size: 8pt;
        color: #2c5282;
        margin-top: 6px;
    }

    .pie-reporte {
        text-align: center;
        font-size: 7pt;
        color: #a0aec0;
        margin-top: 15px;
        border-top: 1px solid #e2e8f0;
        padding-top: 4px;
    }
</style>

<!-- ═══ ENCABEZADO ═══ -->
<div class="encabezado">
    <h1>Reporte de Comparación de Declaración</h1>
    <p>Sistema de Práctica de Declaración Sucesoral SENIAT (SPDSS)</p>
</div>

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

<!-- ═══ SCORE GLOBAL ═══ -->
<div class="score-global">
    <div class="score-numero"><?= $score['correctos'] ?> / <?= $score['total'] ?></div>
    <div class="score-label">Campos correctos (<?= $score['porcentaje'] ?>%)</div>
    <div class="barra-contenedor">
        <div class="barra-relleno" style="width: <?= min($score['porcentaje'], 100) ?>%;"></div>
    </div>
</div>

<!-- ═══ RESUMEN POR SECCIÓN ═══ -->
<table class="tabla-resumen">
    <thead>
        <tr>
            <th style="text-align: left;">Sección</th>
            <th style="text-align: center;">Correctos</th>
            <th style="text-align: center;">Total</th>
            <th style="text-align: center;">%</th>
            <th style="text-align: left; width: 35%;">Progreso</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($resumenSecciones as $sec): ?>
        <?php $pct = $sec['total'] > 0 ? round($sec['correctos'] / $sec['total'] * 100) : 0; ?>
        <tr>
            <td><?= htmlspecialchars($sec['nombre']) ?></td>
            <td style="text-align: center;"><?= $sec['correctos'] ?></td>
            <td style="text-align: center;"><?= $sec['total'] ?></td>
            <td style="text-align: center;"><?= $pct ?>%</td>
            <td>
                <div class="barra-mini-contenedor">
                    <div class="barra-mini-relleno <?php if ($pct >= 70): ?>barra-verde<?php elseif ($pct >= 40): ?>barra-amarilla<?php else: ?>barra-roja<?php endif; ?>" style="width: <?= $pct ?>%;"></div>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<pagebreak />

<!-- ═══ SECCIONES DETALLADAS ═══ -->
<?php
// Helper to count section score
function contarSeccion(array $grupos): array {
    $ok = 0; $t = 0;
    foreach ($grupos as $g) {
        foreach ($g['campos'] as $c) { $t++; if ($c['correcto']) $ok++; }
    }
    return [$ok, $t];
}
?>

<?php foreach ($secciones as $seccion): ?>
<?php [$secOk, $secTotal] = contarSeccion($seccion['grupos']); ?>
<div class="seccion">
    <div class="seccion-header">
        <span class="seccion-titulo"><?= htmlspecialchars($seccion['titulo']) ?></span>
        <span class="seccion-conteo"><?= $secOk ?> / <?= $secTotal ?> correctos</span>
    </div>

    <?php foreach ($seccion['grupos'] as $grupo): ?>
        <div class="item-header"><?= htmlspecialchars($grupo['label']) ?></div>
        <table class="tabla-campos">
            <thead>
                <tr>
                    <th style="width:5%"></th>
                    <th style="width:27%">Campo</th>
                    <th style="width:28%">Su Valor</th>
                    <th style="width:28%">Valor Correcto</th>
                    <th style="width:12%">Resultado</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($grupo['campos'] as $c):
                    $tipo = $c['tipo'] ?? ($c['correcto'] ? 'ok' : 'fail');
                    if ($tipo === 'omitido') { $filaClass = 'fila-omitido'; $badgeClass = 'badge-omitido'; $badgeText = 'Omitido'; $iconChar = '○'; }
                    elseif ($tipo === 'sobrante') { $filaClass = 'fila-sobrante'; $badgeClass = 'badge-sobrante'; $badgeText = 'De Más'; $iconChar = '△'; }
                    elseif ($c['correcto']) { $filaClass = 'fila-ok'; $badgeClass = 'badge-ok'; $badgeText = 'Correcto'; $iconChar = '✓'; }
                    else { $filaClass = 'fila-fail'; $badgeClass = 'badge-fail'; $badgeText = 'Incorrecto'; $iconChar = '✗'; }
                ?>
                <tr class="<?= $filaClass ?>">
                    <td class="icono" style="text-align:center;"><?= $iconChar ?></td>
                    <td><?= htmlspecialchars($c['campo']) ?></td>
                    <td<?= ($c['borrador'] === '—') ? ' class="valor-vacio"' : '' ?>><?= htmlspecialchars($c['borrador']) ?></td>
                    <td><?= htmlspecialchars($c['esperado']) ?></td>
                    <td style="text-align:center;">
                        <span class="badge <?= $badgeClass ?>"><?= $badgeText ?></span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endforeach; ?>
</div>
<?php endforeach; ?>

<!-- ═══ AUTOLIQUIDACIÓN ═══ -->
<?php
$autoOk = 0; $autoTotal = count($autoItems);
foreach ($autoItems as $item) { if ($item['correcto']) $autoOk++; }
?>
<div class="seccion">
    <div class="seccion-header">
        <span class="seccion-titulo">Autoliquidación del Impuesto</span>
        <span class="seccion-conteo"><?= $autoOk ?> / <?= $autoTotal ?> correctos</span>
    </div>

    <table class="tabla-campos">
        <thead>
            <tr>
                <th style="width:5%"></th>
                <th style="width:35%">Concepto</th>
                <th style="width:22%">Su Valor (Bs)</th>
                <th style="width:22%">Valor Correcto (Bs)</th>
                <th style="width:16%">Resultado</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($autoItems as $item): ?>
            <tr class="<?= $item['correcto'] ? 'fila-ok' : 'fila-fail' ?>">
                <td class="icono" style="text-align:center;"><?= $item['correcto'] ? '✓' : '✗' ?></td>
                <td><?= htmlspecialchars($item['campo']) ?></td>
                <td style="text-align:right;"><?= htmlspecialchars($item['borrador']) ?></td>
                <td style="text-align:right;"><?= htmlspecialchars($item['esperado']) ?></td>
                <td style="text-align:center;">
                    <span class="badge <?= $item['correcto'] ? 'badge-ok' : 'badge-fail' ?>">
                        <?= $item['correcto'] ? 'Correcto' : 'Incorrecto' ?>
                    </span>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php if ($patrimonioNeto <= 0): ?>
    <div class="nota-informativa">
        <strong>Nota:</strong> El Patrimonio Neto Hereditario correcto es ≤ 0 (los pasivos superan los activos),
        por lo tanto no hay impuesto a determinar. Todos los valores de impuesto correctos son 0,00 Bs.
    </div>
    <?php endif; ?>
</div>

<!-- ═══ CÁLCULO POR HEREDERO ═══ -->
<?php if (!empty($herederosCalc)): ?>
<?php
$hcOk = 0; $hcTotal = 0;
foreach ($herederosCalc as $h) {
    foreach ($h['campos'] as $c) { $hcTotal++; if ($c['correcto']) $hcOk++; }
}
?>
<div class="seccion">
    <div class="seccion-header">
        <span class="seccion-titulo">Determinación del Impuesto por Heredero</span>
        <span class="seccion-conteo"><?= $hcOk ?> / <?= $hcTotal ?> correctos</span>
    </div>

    <?php foreach ($herederosCalc as $h): ?>
    <div class="item-header">
        <?= htmlspecialchars($h['nombre']) ?> — <?= htmlspecialchars($h['cedula']) ?> (<?= htmlspecialchars($h['parentesco']) ?>)
    </div>
    <table class="tabla-campos">
        <thead>
            <tr>
                <th style="width:5%"></th>
                <th style="width:27%">Campo</th>
                <th style="width:28%">Su Valor</th>
                <th style="width:28%">Valor Correcto</th>
                <th style="width:12%">Resultado</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($h['campos'] as $c): ?>
            <tr class="<?= $c['correcto'] ? 'fila-ok' : 'fila-fail' ?>">
                <td class="icono" style="text-align:center;"><?= $c['correcto'] ? '✓' : '✗' ?></td>
                <td><?= htmlspecialchars($c['campo']) ?></td>
                <td><?= htmlspecialchars($c['borrador']) ?></td>
                <td><?= htmlspecialchars($c['esperado']) ?></td>
                <td style="text-align:center;">
                    <span class="badge <?= $c['correcto'] ? 'badge-ok' : 'badge-fail' ?>">
                        <?= $c['correcto'] ? 'Correcto' : 'Incorrecto' ?>
                    </span>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="pie-reporte">
    Generado por SPDSS — <?= date('d/m/Y H:i:s') ?>
</div>
