<?php
ob_start();
$activeMenu = 'verDeclaracion';
$activeItem = 'Ver Declaración';
$breadcrumbs = [
    ['label' => 'Inicio', 'url' => '/simulador/servicios_declaracion/dashboard'],
    ['label' => 'Ver Declaración'],
];
?>

<!-- Alerta tipo declaración -->
<div class="row">
    <div class="col-sm-12">
        <div role="alert" class="row alert alert-sm alert-info">
            <div class="text-center fw-bold"> SU DECLARACIÓN ES TIPO SUSTITUTIVA</div>
        </div>
    </div>
</div>

<div class="shadow-lg p-3 mb-5 bg-body rounded lenletratablaResumen">
    <div>
        <div class="row">
            <!-- Botones de navegación -->
            <div class="col-sm-12" style="text-align:center">
                <button type="button" class="btn btn-sm btn-danger" disabled>
                    <i class="bi bi-arrow-bar-left"></i> Anverso
                </button>
                &nbsp;
                <button type="button" class="btn btn-sm btn-danger" id="btnDeclararAnverso" onclick="window.modalManager.open('modal-declarar')">
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
                        <table class="table table-bordered table-sm lenletratablaResumen">
                            <!-- A - DATOS DEL CONTRIBUYENTE -->
                            <tbody>
                                <tr>
                                    <th class="table-light">A - DATOS DEL CONTRIBUYENTE</th>
                                    <th class="table-light">Nº RIF</th>
                                </tr>
                            </tbody>
                            <tbody>
                                <tr>
                                    <td class="bordeIzq bordeAbajo bordeDer">SUCESION BAUZA MARIN RAMON ERNESTO</td>
                                    <td class="bordeAbajo bordeDer text-end">J504705900</td>
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
                                    <td class="bordeIzq bordeAbajo bordeDer">14/03/2026</td>
                                    <td class="bordeAbajo bordeDer text-end">17/01/2023</td>
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
                                    <td class="bordeIzq bordeAbajo bordeDer">BAUZA MARIN, RAMON ERNESTO</td>
                                    <td class="bordeAbajo bordeDer text-end">J504705900 / 6145727</td>
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
                                    <td class="bordeIzq bordeAbajo bordeDer">CALLE OESTE CASA CONJUNTO RESIDENCIAL LAS
                                        TUNAS NRO 04-04 NO APLICA URBANIZACION MANEIRO CIUDAD PAMPATAR PARROQUIA:
                                        CAPITAL MANEIRO MUNICIPIO: MANEIRO ESTADO: NUEVA ESPARTA</td>
                                    <td class="bordeAbajo bordeDer text-end">21/04/2022</td>
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
                                    <td>PEDRONI LEPERVANCHE, PAOLA MARIA</td>
                                    <td class="text-end">V069727138</td>
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
                                    <td colspan="2"></td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- ═══ F - DATOS DE LA PRORROGA ═══ -->
                        <table class="table table-bordered table-sm lenletratablaResumen">
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
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- ═══ G - HEREDEROS ═══ -->
                        <table class="table table-bordered table-sm lenletratablaResumen">
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
                                                <tr>
                                                    <td style="text-align:left!important">BAUZA PEDRONI RAMON ERNESTO
                                                    </td>
                                                    <td style="text-align:center">V213264954</td>
                                                    <td style="text-align:center">HIJA/HIJO</td>
                                                    <td style="text-align:center">1</td>
                                                    <td style="text-align:center">NO</td>
                                                    <td class="text-end">0,00</td>
                                                    <td class="text-center">0,00</td>
                                                    <td class="text-end">0,00</td>
                                                    <td class="text-end">0,00</td>
                                                    <td class="text-end">0,00</td>
                                                    <td class="text-end">0,00</td>
                                                </tr>
                                                <tr>
                                                    <td style="text-align:left!important">BAUZA PEDRONI ANDRES ALEJANDRO
                                                    </td>
                                                    <td style="text-align:center">V213264962</td>
                                                    <td style="text-align:center">HIJA/HIJO</td>
                                                    <td style="text-align:center">1</td>
                                                    <td style="text-align:center">NO</td>
                                                    <td class="text-end">0,00</td>
                                                    <td class="text-center">0,00</td>
                                                    <td class="text-end">0,00</td>
                                                    <td class="text-end">0,00</td>
                                                    <td class="text-end">0,00</td>
                                                    <td class="text-end">0,00</td>
                                                </tr>
                                                <tr>
                                                    <td style="text-align:left!important">PEDRONI LEPERVANCHE PAOLA
                                                        MARIA</td>
                                                    <td style="text-align:center">V069727138</td>
                                                    <td style="text-align:center">HIJA/HIJO</td>
                                                    <td style="text-align:center">1</td>
                                                    <td style="text-align:center">NO</td>
                                                    <td class="text-end">0,00</td>
                                                    <td class="text-center">0,00</td>
                                                    <td class="text-end">0,00</td>
                                                    <td class="text-end">0,00</td>
                                                    <td class="text-end">0,00</td>
                                                    <td class="text-end">0,00</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- ═══ H - IDENTIFICACION DE HEREDERO PREMUERTO ═══ -->
                        <table class="table table-bordered table-sm lenletratablaResumen">
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
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- ═══ I - AUTOLIQUIDACIÓN DEL IMPUESTO ═══ -->
                        <table class="table table-bordered table-sm lenletratablaResumen">
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
                                            <tbody>
                                                <tr>
                                                    <td colspan="2" class="table-light text-center">
                                                        <strong>Concepto</strong></td>
                                                    <td class="table-light text-center"><strong>Gravamen</strong></td>
                                                </tr>
                                                <tr>
                                                    <td>1</td>
                                                    <td>Total Bienes Inmuebles</td>
                                                    <td class="text-end">3.700.000,00</td>
                                                </tr>
                                                <tr>
                                                    <td>2</td>
                                                    <td>Total Bienes Muebles</td>
                                                    <td class="text-end">0,00</td>
                                                </tr>
                                                <tr class="table-light">
                                                    <td><strong>3</strong></td>
                                                    <td><strong>Patrimonio Hereditario Bruto (1 + 2)</strong></td>
                                                    <td class="text-end"><strong>3.700.000,00</strong></td>
                                                </tr>
                                                <tr class="table-light">
                                                    <td><strong>4</strong></td>
                                                    <td><strong>Activo Hereditario Bruto (Patrimonio Hereditario
                                                            Bruto)</strong></td>
                                                    <td class="text-end"><strong>3.700.000,00</strong></td>
                                                </tr>
                                                <tr>
                                                    <td>5</td>
                                                    <td>Desgravámenes</td>
                                                    <td class="text-end">3.700.000,00</td>
                                                </tr>
                                                <tr>
                                                    <td>6</td>
                                                    <td>Exenciones</td>
                                                    <td class="text-end">0,00</td>
                                                </tr>
                                                <tr>
                                                    <td>7</td>
                                                    <td>Exoneraciones</td>
                                                    <td class="text-end">0,00</td>
                                                </tr>
                                                <tr class="table-light">
                                                    <td><strong>8</strong></td>
                                                    <td><strong>Total de Exclusiones (Desgravámenes - Exenciones -
                                                            Exoneraciones)</strong></td>
                                                    <td class="text-end"><strong>3.700.000,00</strong></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="3" class="text-center border-white"><strong>Patrimonio
                                                            Neto Hereditario</strong></td>
                                                </tr>
                                                <tr>
                                                    <td>9</td>
                                                    <td>Activo Hereditario Neto (Activo Hereditario Bruto - Total de
                                                        Exclusiones)</td>
                                                    <td class="text-end">0,00</td>
                                                </tr>
                                                <tr>
                                                    <td>10</td>
                                                    <td>Total Pasivo</td>
                                                    <td class="text-end">0,00</td>
                                                </tr>
                                                <tr class="table-light">
                                                    <td><strong>11</strong></td>
                                                    <td><strong>Patrimonio Neto Hereditario o Líquido Hereditario
                                                            Gravable (Activo Hereditario Neto - Total Pasivo)</strong>
                                                    </td>
                                                    <td class="text-end"><strong>0,00</strong></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="3" class="text-center border-white">
                                                        <strong>Determinación de Tributo</strong></td>
                                                </tr>
                                                <tr>
                                                    <td>12</td>
                                                    <td>Impuesto Determinado por Según Tarifa</td>
                                                    <td class="text-end">0,00</td>
                                                </tr>
                                                <tr>
                                                    <td>13</td>
                                                    <td>Reducciones</td>
                                                    <td class="text-end">0,00</td>
                                                </tr>
                                                <tr class="table-light">
                                                    <td><strong>14</strong></td>
                                                    <td><strong>Total Impuesto a Pagar</strong></td>
                                                    <td class="text-end"><strong>0,00</strong></td>
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

<!-- ═══ Modal Confirmación Declarar (SPDSS) ═══ -->
<dialog class="modal-base" id="modal-declarar">
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

<?php
$content = ob_get_clean();
include __DIR__ . '/../../../../layouts/sim_sucesiones_layout.php';
?>