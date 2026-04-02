<?php
/**
 * Declaración Anverso — Secciones A–I con datos dinámicos del borrador_json.
 *
 * Variables recibidas desde SucesionController::declaracionAnverso():
 *   $datos['nombre_sucesion']     — "SUCESION APELLIDOS, NOMBRES"
 *   $datos['rif']                 — RIF sucesoral (J...)
 *   $datos['fecha_declaracion']   — DD/MM/YYYY
 *   $datos['fecha_vencimiento']   — DD/MM/YYYY
 *   $datos['nombre_causante']     — "APELLIDOS, NOMBRES"
 *   $datos['rif_causante']        — RIF personal del causante
 *   $datos['cedula_causante']     — Cédula del causante
 *   $datos['domicilio_fiscal']    — Dirección completa formateada
 *   $datos['fecha_fallecimiento'] — DD/MM/YYYY
 *   $datos['representante_nombre']— "APELLIDOS, NOMBRES"
 *   $datos['representante_rif']   — RIF del representante
 *   $datos['herederos']           — Array de herederos con datos calculados
 *   $datos['linea_1']..['linea_14'] — Autoliquidación formateada
 *   $datos['fmt']                 — Función formatDecimal($float)
 */
ob_start();
$activeMenu = 'verDeclaracion';
$activeItem = 'Ver Declaración';
$breadcrumbs = [
    ['label' => 'Inicio', 'url' => '/simulador/servicios_declaracion/dashboard'],
    ['label' => 'Ver Declaración'],
];

// ── Safe extraction ──
$d   = $datos ?? [];
$fmt = $d['fmt'] ?? function (float $v) { return number_format($v, 2, ',', '.'); };
$herederos = $d['herederos'] ?? [];
?>




<div class="shadow-lg p-3 mb-5 bg-body rounded lenletratablaResumen">
    <div>
        <div class="row">
            <!-- Botones de navegación -->
            <div id="navAnverso" class="col-sm-12" style="text-align:center">
                <button type="button" class="btn btn-sm btn-danger" disabled>
                    <i class="bi bi-arrow-bar-left"></i> Anverso
                </button>
                &nbsp;
                <button type="button" class="btn btn-sm btn-danger" id="btnDeclararAnverso" onclick="window.modalManager.open('modal-aviso-seniat')">
                    <i class="bi-check-circle"></i> Declarar
                </button>
                &nbsp;&nbsp;
                <a href="<?= base_url('/simulador/sucesion/declaracion_reverso') ?>" class="btn btn-sm btn-danger">
                    Reverso <i class="bi bi-arrow-bar-right"></i>
                </a>
            </div>

            <div style="height:30px"></div>

            <div class="row">
                <div class="col-sm-12">
                    <div>
                        <!-- ═══ Tabla principal: Secciones A-E ═══ -->
                        <table id="seccionAE" class="table table-bordered table-sm lenletratablaResumen">
                            <!-- A - DATOS DEL CONTRIBUYENTE -->
                            <tbody>
                                <tr>
                                    <th class="table-light">A - DATOS DEL CONTRIBUYENTE</th>
                                    <th class="table-light">Nº RIF</th>
                                </tr>
                            </tbody>
                            <tbody>
                                <tr>
                                    <td class="bordeIzq bordeAbajo bordeDer"><?= htmlspecialchars($d['nombre_sucesion'] ?? '') ?></td>
                                    <td class="bordeAbajo bordeDer text-end"><?= htmlspecialchars($d['rif'] ?? '') ?></td>
                                </tr>
                            </tbody>

                            <!-- FECHA DE DECLARACIÓN / FECHA DE VENCIMIENTO -->
                            <tbody>
                                <tr>
                                    <th class="table-light">FECHA DE DECLARACIÓN</th>
                                    <th class="table-light">FECHA DE VENCIMIENTO</th>
                                </tr>
                            </tbody>
                            <tbody>
                                <tr>
                                    <td class="bordeIzq bordeAbajo bordeDer"><?= htmlspecialchars($d['fecha_declaracion'] ?? '') ?></td>
                                    <td class="bordeAbajo bordeDer text-end"><?= htmlspecialchars($d['fecha_vencimiento'] ?? '') ?></td>
                                </tr>
                            </tbody>

                            <!-- B - DATOS DEL CAUSANTE O DONANTE -->
                            <tbody>
                                <tr>
                                    <th class="table-light">B - DATOS DEL CAUSANTE O DONANTE</th>
                                    <th class="table-light">RIF Ó CEDULA DE IDENTIDAD</th>
                                </tr>
                            </tbody>
                            <tbody>
                                <tr>
                                    <td class="bordeIzq bordeAbajo bordeDer"><?= htmlspecialchars($d['nombre_causante'] ?? '') ?></td>
                                    <td class="bordeAbajo bordeDer text-end"><?= htmlspecialchars(($d['rif_causante'] ?? '') . ' / ' . ($d['cedula_causante'] ?? '')) ?></td>
                                </tr>
                            </tbody>

                            <!-- C - DIRECCIÓN DEL CAUSANTE O DONANTE -->
                            <tbody>
                                <tr>
                                    <th class="table-light">C - DIRECCIÓN DEL CAUSANTE O DONANTE</th>
                                    <th class="table-light">FECHA DE FALLECIMIENTO</th>
                                </tr>
                            </tbody>
                            <tbody>
                                <tr>
                                    <td class="bordeIzq bordeAbajo bordeDer"><?= htmlspecialchars($d['domicilio_fiscal'] ?? '') ?></td>
                                    <td class="bordeAbajo bordeDer text-end"><?= htmlspecialchars($d['fecha_fallecimiento'] ?? '') ?></td>
                                </tr>
                            </tbody>

                            <!-- D - DATOS DEL REPRESENTANTE LEGAL O RESPONSABLE -->
                            <tbody>
                                <tr>
                                    <th class="table-light">D - DATOS DEL REPRESENTANTE LEGAL O RESPONSABLE</th>
                                    <th class="bordeAbajo bordeDer">N°- RIF</th>
                                </tr>
                            </tbody>
                            <tbody>
                                <tr>
                                    <td><?= htmlspecialchars($d['representante_nombre'] ?? '') ?></td>
                                    <td class="text-end"><?= htmlspecialchars($d['representante_rif'] ?? '') ?></td>
                                </tr>
                            </tbody>

                            <!-- E - TIPO DE HERENCIA -->
                            <tbody>
                                <tr>
                                    <td colspan="2" class="table-light">E - TIPO DE HERENCIA</td>
                                </tr>
                            </tbody>
                            <tbody>
                                <tr>
                                    <td colspan="2"><?= htmlspecialchars($d['tipos_herencia'] ?? '') ?></td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- ═══ F - DATOS DE LA PRORROGA ═══ -->
                        <table id="seccionF" class="table table-bordered table-sm lenletratablaResumen">
                            <thead>
                                <tr>
                                    <td colspan="10" class="table-light">F- DATOS DE LA PRORROGA</td>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <table class="table table-bordered table-sm lenletratablaResumen">
                                            <thead class="table-light">
                                                <tr>
                                                    <td colspan="2">Fecha Solicitud</td>
                                                    <td colspan="2">Nro. Resolución&nbsp;</td>
                                                    <td colspan="2">Fecha Resolución&nbsp;</td>
                                                    <td colspan="2">Plazo Otorgado(días)</td>
                                                    <td colspan="2">Fecha Vencimiento</td>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $prorrogas = $d['prorrogas'] ?? [];
                                                if (empty($prorrogas)): ?>
                                                    <tr>
                                                        <td colspan="10" class="text-center">Sin prórrogas registradas</td>
                                                    </tr>
                                                <?php else:
                                                    foreach ($prorrogas as $pro): ?>
                                                        <tr>
                                                            <td colspan="2"><?= htmlspecialchars($pro['fecha_solicitud'] ?? '') ?></td>
                                                            <td colspan="2"><?= htmlspecialchars($pro['nro_resolucion'] ?? '') ?></td>
                                                            <td colspan="2"><?= htmlspecialchars($pro['fecha_resolucion'] ?? '') ?></td>
                                                            <td colspan="2" class="text-center"><?= htmlspecialchars($pro['plazo_dias'] ?? '') ?></td>
                                                            <td colspan="2"><?= htmlspecialchars($pro['fecha_vencimiento'] ?? '') ?></td>
                                                        </tr>
                                                    <?php endforeach;
                                                endif; ?>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- ═══ G - HEREDEROS ═══ -->
                        <table id="seccionG" class="table table-bordered table-sm lenletratablaResumen">
                            <thead class="table-light">
                                <tr>
                                    <td colspan="11" class="table-light"> G - HEREDEROS</td>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <table colspan="11" class="table table-bordered table-sm lenletratablaResumen">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Apellido(s) y Nombre(s)</th>
                                                    <th>C.I./Pasaporte</th>
                                                    <th>Parentesco</th>
                                                    <th>Grado</th>
                                                    <th>Premuerto</th>
                                                    <th>Cuota Parte Hereditaria(Bs)</th>
                                                    <th>Porcentaje o Tarifa (%)</th>
                                                    <th>Sustraendo(Bs)</th>
                                                    <th>Impuesto Determinado(Bs)</th>
                                                    <th>Reducción(Bs)</th>
                                                    <th>Impuesto a Pagar (Impuesto Determinado - Reduccion)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($herederos as $h): ?>
                                                <tr>
                                                    <td style="text-align:left!important"><?= htmlspecialchars($h['nombre'] ?? '') ?></td>
                                                    <td style="text-align:center"><?= htmlspecialchars($h['cedula'] ?? '') ?></td>
                                                    <td style="text-align:center"><?= htmlspecialchars($h['parentesco'] ?? '') ?></td>
                                                    <td style="text-align:center"><?= htmlspecialchars($h['grado'] ?? '1') ?></td>
                                                    <td style="text-align:center"><?= ($h['premuerto'] ?? 'NO') ?></td>
                                                    <td class="text-end"><?= $fmt((float)($h['cuota_parte_bs'] ?? 0)) ?></td>
                                                    <td class="text-center"><?= $fmt((float)($h['porcentaje'] ?? 0)) ?></td>
                                                    <td class="text-end"><?= $fmt((float)($h['sustraendo_bs'] ?? 0)) ?></td>
                                                    <td class="text-end"><?= $fmt((float)($h['impuesto_determinado'] ?? 0)) ?></td>
                                                    <td class="text-end"><?= $fmt((float)($h['reduccion'] ?? 0)) ?></td>
                                                    <td class="text-end"><?= $fmt((float)($h['impuesto_a_pagar'] ?? 0)) ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                                <?php if (empty($herederos)): ?>
                                                <tr><td colspan="11" class="text-center text-muted">No hay herederos registrados</td></tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- ═══ H - IDENTIFICACION DE HEREDERO PREMUERTO ═══ -->
                        <table id="seccionH" class="table table-bordered table-sm lenletratablaResumen">
                            <thead class="table-light">
                                <tr>
                                    <td colspan="11" class="table-light"> H - IDENTIFICACION DE HEREDERO PREMUERTO</td>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <table colspan="11" class="table table-bordered table-sm lenletratablaResumen">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Apellidos y Nombre del Heredero Premuerto</th>
                                                    <th>Apellidos y Nombre de lo(s) Heredero(s) del Premuerto</th>
                                                    <th>C.I./Pasaporte</th>
                                                    <th>Parentesco</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                // Catalog parentescos for premuerto sub-herederos
                                                $catParPremuerto = [
                                                    1 => 'Hija/Hijo', 2 => 'Nieta/Nieto', 3 => 'Bisnieta/Bisnieto',
                                                    4 => 'Madre', 5 => 'Padre', 6 => 'Abuela/Abuelo',
                                                    7 => 'Hija/Hijo Adoptiva', 8 => 'Cónyuge', 9 => 'Concubina',
                                                    10 => 'Hermana(o) Simple', 11 => 'Hermana(o) Doble',
                                                    12 => 'Tia/Tio', 13 => 'Sobrina/Sobrino',
                                                    14 => 'Prima/Primo Segundo', 15 => 'Otro pariente',
                                                    16 => 'Extraño', 17 => 'Prima/Primo', 18 => 'Otro', 19 => 'Sin definir',
                                                ];

                                                $relaciones = $d['relaciones'] ?? [];
                                                $herPremuertos = $d['herederos_premuertos'] ?? [];

                                                // Filtrar relaciones con premuerto = "Si"
                                                $premuertosFilter = [];
                                                foreach ($relaciones as $r) {
                                                    if (($r['premuerto'] ?? '') === 'Si') {
                                                        $premuertosFilter[] = $r;
                                                    }
                                                }

                                                if (empty($premuertosFilter)): ?>
                                                    <tr><td colspan="4" class="text-center text-muted">No hay herederos premuertos</td></tr>
                                                <?php else:
                                                    foreach ($premuertosFilter as $pm):
                                                        $pmCedula = $pm['cedula'] ?? '';
                                                        $pmNombre = strtoupper(trim(($pm['apellido'] ?? '') . ' ' . ($pm['nombre'] ?? '')));

                                                        // Buscar sub-herederos de este premuerto
                                                        $subHerederos = [];
                                                        foreach ($herPremuertos as $sh) {
                                                            if (($sh['premuerto_padre_id'] ?? '') === $pmCedula) {
                                                                $subHerederos[] = $sh;
                                                            }
                                                        }

                                                        if (empty($subHerederos)): ?>
                                                            <tr>
                                                                <td><?= htmlspecialchars($pmNombre) ?></td>
                                                                <td colspan="3" class="text-center text-muted">Sin herederos registrados</td>
                                                            </tr>
                                                        <?php else:
                                                            foreach ($subHerederos as $si => $sh):
                                                                $shNombre = strtoupper(trim(($sh['apellido'] ?? '') . ' ' . ($sh['nombre'] ?? '')));
                                                                $shCedula = $sh['cedula'] ?? '';
                                                                $shParId  = (int)($sh['parentesco_id'] ?? 19);
                                                                $shPar    = strtoupper($catParPremuerto[$shParId] ?? 'SIN DEFINIR');
                                                        ?>
                                                            <tr>
                                                                <?php if ($si === 0): ?>
                                                                    <td rowspan="<?= count($subHerederos) ?>"><?= htmlspecialchars($pmNombre) ?></td>
                                                                <?php endif; ?>
                                                                <td><?= htmlspecialchars($shNombre) ?></td>
                                                                <td><?= htmlspecialchars($shCedula) ?></td>
                                                                <td><?= $shPar ?></td>
                                                            </tr>
                                                        <?php endforeach;
                                                        endif;
                                                    endforeach;
                                                endif; ?>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- ═══ I - AUTOLIQUIDACIÓN DEL IMPUESTO ═══ -->
                        <table id="seccionI" class="table table-bordered table-sm lenletratablaResumen">
                            <thead class="table-light">
                                <tr>
                                    <td colspan="11" class="table-light"> I – AUTOLIQUIDACIÓN DEL IMPUESTO</td>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="row">
                                            <div class="col-sm-12 py-2">
                                                <div class="text-info text-center">
                                                    <h6>Los bienes litigiosos y vivienda principal no se muestra en esta
                                                        sección. Puede visualizarlo en el Reverso sección Anexos</h6>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <table class="table table-bordered">
                                            <tbody id="grupoPatrimonioBruto">
                                                <tr>
                                                    <td colspan="2" class="table-light text-center">
                                                        <strong>Concepto</strong></td>
                                                    <td class="table-light text-center"><strong>Gravamen</strong></td>
                                                </tr>
                                                <tr>
                                                    <td>1</td>
                                                    <td>Total Bienes Inmuebles</td>
                                                    <td class="text-end"><?= $d['linea_1'] ?? '0,00' ?></td>
                                                </tr>
                                                <tr>
                                                    <td>2</td>
                                                    <td>Total Bienes Muebles</td>
                                                    <td class="text-end"><?= $d['linea_2'] ?? '0,00' ?></td>
                                                </tr>
                                                <tr class="table-light">
                                                    <td><strong>3</strong></td>
                                                    <td><strong>Patrimonio Hereditario Bruto (1 + 2)</strong></td>
                                                    <td class="text-end"><strong><?= $d['linea_3'] ?? '0,00' ?></strong></td>
                                                </tr>
                                                <tr class="table-light">
                                                    <td><strong>4</strong></td>
                                                    <td><strong>Activo Hereditario Bruto (Patrimonio Hereditario
                                                            Bruto)</strong></td>
                                                    <td class="text-end"><strong><?= $d['linea_4'] ?? '0,00' ?></strong></td>
                                                </tr>
                                            </tbody>
                                            <tbody id="grupoExclusiones">
                                                <tr>
                                                    <td>5</td>
                                                    <td>Desgravámenes</td>
                                                    <td class="text-end"><?= $d['linea_5'] ?? '0,00' ?></td>
                                                </tr>
                                                <tr>
                                                    <td>6</td>
                                                    <td>Exenciones</td>
                                                    <td class="text-end"><?= $d['linea_6'] ?? '0,00' ?></td>
                                                </tr>
                                                <tr>
                                                    <td>7</td>
                                                    <td>Exoneraciones</td>
                                                    <td class="text-end"><?= $d['linea_7'] ?? '0,00' ?></td>
                                                </tr>
                                                <tr class="table-light">
                                                    <td><strong>8</strong></td>
                                                    <td><strong>Total de Exclusiones (Desgravámenes - Exenciones -
                                                            Exoneraciones)</strong></td>
                                                    <td class="text-end"><strong><?= $d['linea_8'] ?? '0,00' ?></strong></td>
                                                </tr>
                                            </tbody>
                                            <tbody id="grupoPatrimonioNeto">
                                                <tr>
                                                    <td colspan="3" class="text-center border-white"><strong>Patrimonio
                                                            Neto Hereditario</strong></td>
                                                </tr>
                                                <tr>
                                                    <td>9</td>
                                                    <td>Activo Hereditario Neto (Activo Hereditario Bruto - Total de
                                                        Exclusiones)</td>
                                                    <td class="text-end"><?= $d['linea_9'] ?? '0,00' ?></td>
                                                </tr>
                                                <tr>
                                                    <td>10</td>
                                                    <td>Total Pasivo</td>
                                                    <td class="text-end"><?= $d['linea_10'] ?? '0,00' ?></td>
                                                </tr>
                                                <tr class="table-light">
                                                    <td><strong>11</strong></td>
                                                    <td><strong>Patrimonio Neto Hereditario o Líquido Hereditario
                                                            Gravable (Activo Hereditario Neto - Total Pasivo)</strong>
                                                    </td>
                                                    <td class="text-end"><strong><?= $d['linea_11'] ?? '0,00' ?></strong></td>
                                                </tr>
                                            </tbody>
                                            <tbody id="grupoTributo">
                                                <tr>
                                                    <td colspan="3" class="text-center border-white">
                                                        <strong>Determinación de Tributo</strong></td>
                                                </tr>
                                                <tr>
                                                    <td>12</td>
                                                    <td>Impuesto Determinado por Según Tarifa</td>
                                                    <td class="text-end"><?= $d['linea_12'] ?? '0,00' ?></td>
                                                </tr>
                                                <tr>
                                                    <td>13</td>
                                                    <td>Reducciones</td>
                                                    <td class="text-end"><?= $d['linea_13'] ?? '0,00' ?></td>
                                                </tr>
                                                <tr class="table-light">
                                                    <td><strong>14</strong></td>
                                                    <td><strong>Total Impuesto a Pagar</strong></td>
                                                    <td class="text-end"><strong><?= $d['linea_14'] ?? '0,00' ?></strong></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ═══ Modal Aviso SENIAT (paso 1) ═══ -->
<dialog class="modal-base" id="modal-aviso-seniat">
    <div class="modal-base__container" style="max-width: 460px;">
        <div class="modal-base__header" style="background: #f8f9fa; border-bottom: 1px solid #dee2e6; padding: 12px 16px;">
            <h2 class="modal-base__title" style="font-size: 16px; font-weight: 600; color: #333;">Aviso</h2>
            <button class="modal-base__close" onclick="window.modalManager.close('modal-aviso-seniat')" style="font-size: 18px; color: #666;">&times;</button>
        </div>
        <div class="modal-base__body" style="padding: 20px;">
            <p style="font-size: 14px; color: #333; margin: 0 0 16px;">
                Su monto a pagar es <strong><?= $d['linea_14'] ?? '0,00' ?></strong>
            </p>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 20px;">
                <label style="font-size: 13px; color: #555; white-space: nowrap;">Seleccione la cantidad de porciones</label>
                <select id="selectPorciones" style="padding: 6px 10px; border: 1px solid #ced4da; border-radius: 4px; font-size: 13px; min-width: 80px; background: #fff;">
                    <option value="1">1</option>
                </select>
            </div>
            <p style="font-size: 14px; color: #333; margin: 0;">
                Si está seguro presione Declarar?
            </p>
        </div>
        <div class="modal-base__footer" style="padding: 12px 16px; border-top: 1px solid #dee2e6; text-align: right;">
            <button class="modal-btn modal-btn-cancel" onclick="window.modalManager.close('modal-aviso-seniat')" style="margin-right: 8px;">Cancelar</button>
            <button class="modal-btn modal-btn-primary" id="btnAvisoDeclararAnverso" style="background-color: #2c3e6b; border-color: #2c3e6b;">Declarar</button>
        </div>
    </div>
</dialog>

<!-- ═══ Modal Confirmación Declarar (SPDSS) ═══ -->
<dialog class="modal-base" id="modal-declarar" data-no-backdrop-close>
    <div class="modal-base__container" style="max-width: 480px;">
        <div class="modal-base__header">
            <h2 class="modal-base__title">Confirmar Declaración</h2>
            <button class="modal-base__close" onclick="window.modalManager.close('modal-declarar')">✕</button>
        </div>
        <div class="modal-base__body">
            <p style="color: var(--gray-600); font-size: var(--text-md); margin: 0 0 12px;">
                Está a punto de <strong>enviar su declaración sucesoral</strong>. Esta acción es <strong>definitiva</strong> y no podrá ser revertida.
            </p>
            <p style="color: var(--gray-600); font-size: var(--text-md); margin: 0 0 8px;">Al confirmar:</p>
            <ul style="color: var(--gray-600); font-size: var(--text-md); margin: 0 0 12px; padding-left: 20px; line-height: 1.8;">
                <li>Se finalizará la simulación del proceso</li>
                <li>No podrá modificar los datos ingresados después de declarar</li>
                <li>Recibirá un correo electrónico con el resumen y los resultados obtenidos</li>
            </ul>
            <p style="color: var(--gray-500); font-size: var(--text-xs); margin: 0; font-style: italic;">
                Asegúrese de haber revisado toda la información antes de continuar.
            </p>
        </div>
        <div class="modal-base__footer">
            <button class="modal-btn modal-btn-cancel" onclick="window.modalManager.close('modal-declarar')">Cancelar</button>
            <button class="modal-btn modal-btn-primary" id="btnConfirmarDeclaracion">Sí, Declarar</button>
        </div>
    </div>
</dialog>

<!-- ═══ Modal Finalización ═══ -->
<dialog class="modal-base" id="modal-finalizacion" data-no-backdrop-close>
    <div class="modal-base__container" style="max-width: 500px;">
        <div class="modal-base__header">
            <h2 class="modal-base__title">Simulación Finalizada</h2>
        </div>
        <div class="modal-base__body" style="text-align: center; padding: 24px;">
            <div style="margin-bottom: 16px;">
                <i class="bi bi-check-circle-fill" style="font-size: 48px; color: #28a745;"></i>
            </div>
            <p style="font-size: 16px; color: #333; margin: 0 0 20px; font-weight: 600;">
                Ha finalizado la simulación del proceso de declaración sucesoral.
            </p>
            <p style="font-size: 14px; color: #555; margin: 0 0 16px;">
                A continuación puede descargar los documentos generados:
            </p>
            <div style="display: flex; flex-direction: column; gap: 10px; align-items: center; margin-bottom: 8px;">
                <a href="<?= base_url('/simulador/sucesion/planilla_pdf') ?>" target="_blank"
                   style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 6px; color: #2c3e6b; text-decoration: none; font-size: 14px; font-weight: 500; width: 280px; justify-content: center;"
                   onmouseover="this.style.background='#e9ecef'" onmouseout="this.style.background='#f8f9fa'">
                    <i class="bi bi-file-earmark-pdf-fill" style="font-size: 18px; color: #dc3545;"></i>
                    Planilla FORMA DS-99032
                </a>
                <a href="<?= base_url('/simulador/sucesion/declaracion_pdf') ?>" target="_blank"
                   style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 6px; color: #2c3e6b; text-decoration: none; font-size: 14px; font-weight: 500; width: 280px; justify-content: center;"
                   onmouseover="this.style.background='#e9ecef'" onmouseout="this.style.background='#f8f9fa'">
                    <i class="bi bi-file-earmark-pdf-fill" style="font-size: 18px; color: #dc3545;"></i>
                    Resumen de la Asignación
                </a>
            </div>
        </div>
        <div class="modal-base__footer" style="justify-content: center;">
            <button class="modal-btn modal-btn-primary" onclick="window.modalManager.close('modal-finalizacion')">Continuar</button>
        </div>
    </div>
</dialog>

<?php
$content = ob_get_clean();
$content .= '
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Aviso SENIAT → abre SPDSS modal
    var btnAviso = document.getElementById("btnAvisoDeclararAnverso");
    if (btnAviso) {
        btnAviso.addEventListener("click", function() {
            window.modalManager.close("modal-aviso-seniat");
            setTimeout(function() { window.modalManager.open("modal-declarar"); }, 300);
        });
    }

    // SPDSS confirmar → AJAX declarar → abrir modal finalización
    var btn = document.getElementById("btnConfirmarDeclaracion");
    if (btn) {
        btn.addEventListener("click", function() {
            var originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = \'<svg width="18" height="18" viewBox="0 0 24 24" style="animation:button-spin .8s linear infinite"><circle cx="12" cy="12" r="10" stroke="rgba(255,255,255,0.4)" stroke-width="3" fill="none"/><path d="M12 2a10 10 0 0 1 10 10" stroke="#fff" stroke-width="3" fill="none" stroke-linecap="round"/></svg> Procesando...\';

            fetch("' . base_url('/api/intentos/declarar') . '", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: "{}"
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.ok) {
                    // Guardar asignacion_id para el redirect de "Continuar"
                    window.__asignacionId = data.asignacion_id;
                    window.modalManager.close("modal-declarar");
                    setTimeout(function() { window.modalManager.open("modal-finalizacion"); }, 300);
                } else {
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                    alert(data.error || "Error al procesar la declaración.");
                }
            })
            .catch(function(err) {
                btn.disabled = false;
                btn.innerHTML = originalText;
                console.error(err);
                alert("Error de conexión al procesar la declaración.");
            });
        });
    }

    // Continuar → redirigir al detalle de la asignación
    var btnContinuar = document.querySelector("#modal-finalizacion .modal-btn-primary");
    if (btnContinuar) {
        btnContinuar.addEventListener("click", function(e) {
            e.preventDefault();
            var id = window.__asignacionId || "";
            window.location.href = "' . base_url('/mis-asignaciones/') . '" + id;
        });
    }
});
</script>';
include __DIR__ . '/../../../../layouts/sim_sucesiones_layout.php';
?>