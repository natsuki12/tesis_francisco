<?php
/**
 * PDF Comparison Template — Rendered by mPDF
 *
 * Variables available:
 *   $datos              — array with caso metadata
 *   $secciones          — array of sections with grupos/campos comparisons
 *   $resumenSecciones   — array of per-section unit score summaries
 *   $autoItems          — array of autoliquidación comparison rows
 *   $herederosCalc      — array of per-heredero tax calculation
 *   $score              — array{correctas, con_errores, omitidas, de_mas, total_esperado, porcentaje}
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
    .score-detalle {
        font-size: 9pt;
        color: #4a5568;
        margin-top: 6px;
    }
    .score-detalle span {
        display: inline-block;
        margin: 0 6px;
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
        padding: 5px 6px;
        font-weight: bold;
        font-size: 7.5pt;
        text-transform: uppercase;
    }
    .tabla-resumen td {
        padding: 4px 6px;
        border-bottom: 1px solid #e2e8f0;
    }
    .tabla-resumen .col-num {
        text-align: center;
        width: 11%;
    }
    .tabla-resumen .fila-total td {
        font-weight: bold;
        border-top: 2px solid #1a365d;
        background-color: #f7fafc;
    }

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
        font-size: 8pt;
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

    .nota-heredero {
        background-color: #fffff0;
        border: 1px solid #ecc94b;
        border-left: 3px solid #d69e2e;
        padding: 5px 10px;
        font-size: 8pt;
        color: #744210;
        margin: 2px 0 4px 0;
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
    <div class="score-numero"><?= $score['correctas'] ?> de <?= $score['total_esperado'] ?></div>
    <div class="score-label">correctos (<?= $score['porcentaje'] ?>%)</div>
    <div class="barra-contenedor">
        <div class="barra-relleno" style="width: <?= min($score['porcentaje'], 100) ?>%;"></div>
    </div>
    <div class="score-detalle">
        <span style="color:#38a169;">✓ <?= $score['correctas'] ?> correctos</span>
        <span style="color:#e53e3e;">✗ <?= $score['con_errores'] ?> con errores</span>
        <span style="color:#dd6b20;">○ <?= $score['omitidas'] ?> omitidos</span>
        <span style="color:#d69e2e;">△ <?= $score['de_mas'] ?> de más</span>
    </div>
</div>

<!-- ═══ RESUMEN POR SECCIÓN ═══ -->
<table class="tabla-resumen">
    <thead>
        <tr>
            <th style="text-align: left;">Sección</th>
            <th class="col-num" style="color:#c6f6d5;">✓</th>
            <th class="col-num" style="color:#fed7d7;">✗</th>
            <th class="col-num" style="color:#feebc8;">○</th>
            <th class="col-num" style="color:#fefcbf;">△</th>
            <th class="col-num">Evaluados</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($resumenSecciones as $sec): ?>
        <tr>
            <td><?= htmlspecialchars($sec['nombre']) ?></td>
            <td class="col-num"><?= $sec['correctas'] ?></td>
            <td class="col-num"><?= $sec['con_errores'] ?></td>
            <td class="col-num"><?= $sec['omitidas'] ?></td>
            <td class="col-num"><?= $sec['de_mas'] ?></td>
            <td class="col-num"><?= $sec['total_esperado'] ?></td>
        </tr>
        <?php endforeach; ?>
        <tr class="fila-total">
            <td>TOTAL</td>
            <td class="col-num"><?= $score['correctas'] ?></td>
            <td class="col-num"><?= $score['con_errores'] ?></td>
            <td class="col-num"><?= $score['omitidas'] ?></td>
            <td class="col-num"><?= $score['de_mas'] ?></td>
            <td class="col-num"><?= $score['total_esperado'] ?></td>
        </tr>
    </tbody>
</table>

<pagebreak />

<!-- ═══ SECCIONES DETALLADAS ═══ -->
<?php
/**
 * Clasifica los grupos de una sección como unidades.
 * Retorna [correctas, con_errores, omitidas, de_mas]
 */
function contarUnidadesSeccion(array $grupos): array {
    $u = ['correctas' => 0, 'con_errores' => 0, 'omitidas' => 0, 'de_mas' => 0];
    foreach ($grupos as $g) {
        if ($g['label'] === 'Cantidad') continue;
        $label = $g['label'];
        if (str_starts_with($label, 'Omitido:')) {
            $u['omitidas']++;
        } elseif (str_starts_with($label, 'De más:')) {
            $u['de_mas']++;
        } else {
            $todosOk = true;
            foreach ($g['campos'] as $c) {
                if (!$c['correcto']) { $todosOk = false; break; }
            }
            if ($todosOk) { $u['correctas']++; } else { $u['con_errores']++; }
        }
    }
    return $u;
}
?>

<?php foreach ($secciones as $seccion): ?>
<?php
    $gruposSeccion = $seccion['grupos'] ?? [];
    if (!is_array($gruposSeccion)) { $gruposSeccion = []; }
    $u = contarUnidadesSeccion($gruposSeccion);
?>
<div class="seccion">
    <div class="seccion-header">
        <span class="seccion-titulo"><?= htmlspecialchars($seccion['titulo']) ?></span>
        <span class="seccion-conteo">✓ <?= $u['correctas'] ?>  ✗ <?= $u['con_errores'] ?>  ○ <?= $u['omitidas'] ?>  △ <?= $u['de_mas'] ?></span>
    </div>

    <?php foreach ($gruposSeccion as $grupo): ?>
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
$autoErr = $autoTotal - $autoOk;
?>
<div class="seccion">
    <div class="seccion-header">
        <span class="seccion-titulo">Autoliquidación del Impuesto</span>
        <span class="seccion-conteo">✓ <?= $autoOk ?>  ✗ <?= $autoErr ?></span>
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
$hcOkUnits = 0;
foreach ($herederosCalc as $h) {
    $allOk = true;
    foreach ($h['campos'] as $c) { if (!$c['correcto']) { $allOk = false; break; } }
    if ($allOk) $hcOkUnits++;
}
$hcErrUnits = count($herederosCalc) - $hcOkUnits;
?>
<div class="seccion">
    <div class="seccion-header">
        <span class="seccion-titulo">Determinación del Impuesto por Heredero</span>
        <span class="seccion-conteo">✓ <?= $hcOkUnits ?>  ✗ <?= $hcErrUnits ?></span>
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
    <?php if (!empty($h['notas'])): ?>
        <?php foreach ($h['notas'] as $nota): ?>
        <div class="nota-heredero">
            <strong>ℹ</strong> <?= htmlspecialchars($nota) ?>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="pie-reporte">
    Generado por SPDSS — <?= date('d/m/Y H:i:s') ?>
</div>
