<?php
ob_start();
$activeMenu = 'resumen';
$activeItem = 'Ver Resumen';
$breadcrumbs = [
    ['label' => 'Inicio', 'url' => '/simulador/servicios_declaracion/dashboard'],
    ['label' => 'Resumen Declaración'],
];

// Fallback if no data
$d = $datos ?? null;
$f = function ($key, $default = '0,00') use ($d) {
    return $d ? ($d[$key] ?? $default) : $default;
};
$herederos     = $d['herederos'] ?? [];
$totalHerederos = $d['total_herederos'] ?? 0;
$ut             = $d['ut'] ?? '0,4000000000';

/**
 * Formatea un float a string con coma decimal y punto de miles.
 * Ejemplo: 18641.67 → "18.641,67"
 */
$fmtBs = function ($v): string {
    return number_format((float)$v, 2, ',', '.');
};
?>

<div class="shadow-lg p-3 mb-5 bg-body rounded lenletratablaResumen">
    <div>
        <!-- ═══ Tabla 1: Unidad Tributaria / Total Herederos ═══ -->
        <table class="table table-bordered table-sm lenletratablaResumen">
            <tbody>
                <tr>
                    <td class="table-light"><strong>Unidad Tributaria</strong></td>
                    <td class="text-end"><?= htmlspecialchars($ut) ?></td>
                </tr>
                <tr>
                    <td class="table-light"><strong>Total de Heredero</strong></td>
                    <td class="text-end"><?= $totalHerederos ?></td>
                </tr>
            </tbody>
        </table>
        <br>

        <!-- ═══ Tabla 2: Resumen Patrimonio / Tributo ═══ -->
        <table class="table table-bordered">
            <tbody>
                <!-- Header -->
                <tr>
                    <td colspan="2" class="table-light text-center"><strong>Concepto</strong></td>
                    <td class="table-light text-center"><strong>Gravamen</strong></td>
                </tr>
                <!-- Rows 1-2 -->
                <tr>
                    <td>1</td>
                    <td>Total Bienes Inmuebles</td>
                    <td class="text-end"><?= $f('total_inmuebles') ?></td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>Total Bienes Muebles</td>
                    <td class="text-end"><?= $f('total_muebles') ?></td>
                </tr>
                <!-- Row 3 - highlighted -->
                <tr class="table-light">
                    <td><strong>3</strong></td>
                    <td><strong>Patrimonio Hereditario Bruto (1 + 2)</strong></td>
                    <td class="text-end"><strong><?= $f('patrimonio_bruto') ?></strong></td>
                </tr>
                <!-- Row 4 - highlighted -->
                <tr class="table-light">
                    <td><strong>4</strong></td>
                    <td><strong>Activo Hereditario Bruto (Patrimonio Hereditario Bruto)</strong></td>
                    <td class="text-end"><strong><?= $f('activo_bruto') ?></strong></td>
                </tr>
                <!-- Rows 5-7 -->
                <tr>
                    <td>5</td>
                    <td>Desgravámenes</td>
                    <td class="text-end"><?= $f('desgravamenes') ?></td>
                </tr>
                <tr>
                    <td>6</td>
                    <td>Exenciones</td>
                    <td class="text-end"><?= $f('exenciones') ?></td>
                </tr>
                <tr>
                    <td>7</td>
                    <td>Exoneraciones</td>
                    <td class="text-end"><?= $f('exoneraciones') ?></td>
                </tr>
                <!-- Row 8 - highlighted -->
                <tr class="table-light">
                    <td><strong>8</strong></td>
                    <td><strong>Total de Exclusiones (Desgravámenes - Exenciones - Exoneraciones)</strong></td>
                    <td class="text-end"><strong><?= $f('total_exclusiones') ?></strong></td>
                </tr>
                <!-- Section separator -->
                <tr>
                    <td colspan="3" class="text-center border-white"><strong>Patrimonio Neto Hereditario</strong></td>
                </tr>
                <!-- Rows 9-10 -->
                <tr>
                    <td>9</td>
                    <td>Activo Hereditario Neto (Activo Hereditario Bruto - Total de Exclusiones)</td>
                    <td class="text-end"><?= $f('activo_neto') ?></td>
                </tr>
                <tr>
                    <td>10</td>
                    <td>Total Pasivo</td>
                    <td class="text-end"><?= $f('total_pasivos') ?></td>
                </tr>
                <!-- Row 11 - highlighted -->
                <tr class="table-light">
                    <td><strong>11</strong></td>
                    <td><strong>Patrimonio Neto Hereditario o Líquido Hereditario Gravable (Activo Hereditario Neto - Total Pasivo)</strong></td>
                    <td class="text-end"><strong><?= $f('patrimonio_neto') ?></strong></td>
                </tr>
                <!-- Section separator -->
                <tr>
                    <td colspan="3" class="text-center border-white"><strong>Determinación de Tributo</strong></td>
                </tr>
                <!-- Rows 12-15 -->
                <tr>
                    <td>12</td>
                    <td>Impuesto Determinado por Según Tarifa</td>
                    <td class="text-end"><?= $f('impuesto_tarifa') ?></td>
                </tr>
                <tr>
                    <td>13</td>
                    <td>Reducciones</td>
                    <td class="text-end"><?= $f('reducciones') ?></td>
                </tr>
                <!-- Row 14 - highlighted -->
                <tr class="table-light">
                    <td><strong>14</strong></td>
                    <td><strong>Total Impuesto a Pagar</strong></td>
                    <td class="text-end"><strong><?= $f('total_impuesto') ?></strong></td>
                </tr>
            </tbody>
        </table>
        <br>

        <!-- ═══ Tabla 3: Cuota Parte Hereditaria ═══ -->
        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <td colspan="11" class="text-center">
                        <strong>Cuota Parte Hereditaria (Bs.) = Patrimonio Hereditario / Totales Herederos</strong><br>
                        Impuesto Determinado = (Cuota Parte/Porcentaje) - Sustraendo<br>
                        Impuesto Determinado = 0 (Si la Cuota Parte es menor o igual a 75 UT y el parentesco es de 1er grado)
                    </td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <div class="row">
                            <div class="col-sm-12 py-2">
                                <div class="text-info text-center">
                                    <h6>Si desea ajustar los cálculos de forma manual presione</h6>
                                    <a class="btn btn-sm btn-danger" href="<?= base_url('/simulador/sucesion/resumen_calculo_manual') ?>">Modificar Cálculo</a>
                                    &nbsp;
                                    <button type="button" id="btnRestaurar" class="btn btn-sm btn-danger">Restaurar Cálculo Automático</button>
                                </div>
                            </div>
                        </div>
                        <!-- Sub-tabla de herederos -->
                        <table colspan="11" class="table table-bordered table-sm lenletratablaResumen">
                            <thead class="table-light">
                                <tr>
                                    <th>Apellido(s) y Nombre(s)</th>
                                    <th>C.I./Pasaporte</th>
                                    <th>Parentesco</th>
                                    <th>Grado</th>
                                    <th>Premuerto</th>
                                    <th>Cuota Parte Hereditaria(UT)</th>
                                    <th>Porcentaje o Tarifa (%)</th>
                                    <th>Sustraendo (UT)</th>
                                    <th>Impuesto Determinado (Bs.)</th>
                                    <th>Reducción (Bs.)</th>
                                    <th>Impuesto a Pagar (Impuesto Determinado - Reducción)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($herederos)): ?>
                                    <tr>
                                        <td colspan="11" class="text-center">No hay herederos registrados</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($herederos as $h): ?>
                                        <tr>
                                            <td style="text-align:left!important"><?= htmlspecialchars($h['nombre']) ?></td>
                                            <td style="text-align:center"><?= htmlspecialchars($h['cedula']) ?></td>
                                            <td style="text-align:center"><?= htmlspecialchars($h['parentesco']) ?></td>
                                            <td style="text-align:center"><?= htmlspecialchars($h['grado']) ?></td>
                                            <td style="text-align:center"><?= htmlspecialchars($h['premuerto']) ?></td>
                                            <td><input readonly class="input-group text-end" value="<?= $fmtBs($h['cuota_parte_ut'] ?? 0) ?>"></td>
                                            <td><input readonly class="input-group text-end" value="<?= $fmtBs($h['porcentaje'] ?? 0) ?>"></td>
                                            <td><input readonly class="input-group text-end" value="<?= $fmtBs($h['sustraendo_ut'] ?? 0) ?>"></td>
                                            <td><input readonly class="input-group text-end" value="<?= $fmtBs($h['impuesto_determinado'] ?? 0) ?>"></td>
                                            <td><input readonly class="input-group text-end" value="<?= $fmtBs($h['reduccion'] ?? 0) ?>"></td>
                                            <td><input readonly class="input-group text-end" value="<?= $fmtBs($h['impuesto_a_pagar'] ?? 0) ?>"></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>
        <br>

        <!-- ═══ Tabla 4: Tarifa de Referencia ═══ -->
        <table class="table table-bordered table-sm lenletratablaResumen">
            <thead class="table-light">
                <tr>
                    <th>Indicación del Parentesco</th>
                    <th></th>
                    <th>Hasta 15,00 UT</th>
                    <th>Desde 15,01 UT Hasta 50,00 UT</th>
                    <th>Desde 50,01 UT Hasta 100,00 UT</th>
                    <th>Desde 100,01 UT Hasta 250,00 UT</th>
                    <th>Desde 250,01 UT Hasta 500,00 UT</th>
                    <th>Desde 500,01 UT Hasta 1000,00 UT</th>
                    <th>Desde 1000,01 UT Hasta 4000,00 UT</th>
                    <th>A partir de 4000,01 UT</th>
                </tr>
            </thead>
            <tbody>
                <!-- 1° Grado -->
                <tr>
                    <td rowspan="2">1° ASCENDIENTES DESCENDENTES CONYUGES E HIJOS ADOPTIVOS</td>
                    <td>Porcentaje</td>
                    <td>1%</td><td>2,5%</td><td>5%</td><td>7,50%</td><td>10%</td><td>15%</td><td>20%</td><td>25%</td>
                </tr>
                <tr>
                    <td>Sustraendo</td>
                    <td></td><td>0,23 UT</td><td>1,48 UT</td><td>3,98 UT</td><td>10,23 UT</td><td>35,23 UT</td><td>85,23 UT</td><td>285,23 UT</td>
                </tr>
                <!-- 2° Grado -->
                <tr>
                    <td rowspan="2">2° HERMANOS SOBRINOS POR DERECHO DE REPRESENTACIÓN</td>
                    <td>Porcentaje</td>
                    <td>2,5%</td><td>5%</td><td>10%</td><td>15%</td><td>20%</td><td>25%</td><td>30%</td><td>40%</td>
                </tr>
                <tr>
                    <td>Sustraendo</td>
                    <td></td><td>0,38 UT</td><td>2,88 UT</td><td>7,88 UT</td><td>20,38 UT</td><td>45,38 UT</td><td>95,38 UT</td><td>495,38 UT</td>
                </tr>
                <!-- 3° Grado -->
                <tr>
                    <td rowspan="2">3° OTROS COLATERALES DE 3° GRADO Y LOS DE 4° GRADO</td>
                    <td>Porcentaje</td>
                    <td>6%</td><td>12,5%</td><td>20%</td><td>25%</td><td>30%</td><td>35%</td><td>40%</td><td>50%</td>
                </tr>
                <tr>
                    <td>Sustraendo</td>
                    <td></td><td>0,98 UT</td><td>4,73 UT</td><td>9,73 UT</td><td>22,23 UT</td><td>47,23 UT</td><td>97,23 UT</td><td>497,23 UT</td>
                </tr>
                <!-- 4° Grado -->
                <tr>
                    <td rowspan="2">4° AFINES OTROS PARIENTES Y EXTRAÑOS</td>
                    <td>Porcentaje</td>
                    <td>10%</td><td>15%</td><td>25%</td><td>30%</td><td>35%</td><td>40%</td><td>45%</td><td>55%</td>
                </tr>
                <tr>
                    <td>Sustraendo</td>
                    <td></td><td>0,75 UT</td><td>5,75 UT</td><td>10,75 UT</td><td>23,25 UT</td><td>48,25 UT</td><td>98,25 UT</td><td>498,25 UT</td>
                </tr>
            </tbody>
        </table>
    </div>

</div>

<?php
$intentoIdResumen = $d ? ($d['intento_id'] ?? null) : null;
$borradorRawResumen = $d ? ($d['borrador_raw'] ?? []) : [];
$content = ob_get_clean();
$content .= '
<script>
(function() {
    var BASE_URL   = "' . rtrim(base_url(), '/') . '";
    var INTENTO_ID = ' . json_encode($intentoIdResumen) . ';
    var BORRADOR   = ' . json_encode($borradorRawResumen, JSON_UNESCAPED_UNICODE) . ';
    var btn = document.getElementById("btnRestaurar");
    if (btn) {
        btn.addEventListener("click", function() {
            if (!INTENTO_ID) { alert("No hay intento activo"); return; }
            if (!confirm("¿Desea restaurar el cálculo automático? Se eliminarán las modificaciones manuales.")) return;

            btn.disabled = true;
            btn.textContent = "Restaurando...";

            // Eliminar calculo_manual del borrador
            delete BORRADOR.calculo_manual;

            fetch(BASE_URL + "/api/intentos/" + INTENTO_ID + "/guardar", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ borrador: BORRADOR })
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.ok) {
                    window.location.reload();
                } else {
                    alert("Error: " + (data.error || "Error desconocido"));
                    btn.disabled = false;
                    btn.textContent = "Restaurar Cálculo Automático";
                }
            })
            .catch(function(err) {
                alert("Error de conexión: " + err.message);
                btn.disabled = false;
                btn.textContent = "Restaurar Cálculo Automático";
            });
        });
    }
})();
</script>';
include __DIR__ . '/../../../../layouts/sim_sucesiones_layout.php';
?>
