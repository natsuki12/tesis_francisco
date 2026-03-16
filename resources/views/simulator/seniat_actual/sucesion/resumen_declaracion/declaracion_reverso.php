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
                <a href="<?= base_url('/simulador/sucesion/declaracion_anverso') ?>" class="btn btn-sm btn-danger">
                    <i class="bi bi-arrow-bar-left"></i> Anverso
                </a>
                &nbsp;
                <button type="button" class="btn btn-sm btn-danger" id="btnDeclararReverso" onclick="window.modalManager.open('modal-declarar')">
                    <i class="bi-check-circle"></i> Declarar
                </button>
                &nbsp;&nbsp;
                <button type="button" class="btn btn-sm btn-danger" disabled>
                    Reverso <i class="bi bi-arrow-bar-right"></i>
                </button>
            </div>

            <div style="height:30px"></div>

            <div class="row">
                <div class="col-sm-12">
                    <div>
                        <!-- ═══ Tabla A–D (misma que Anverso) ═══ -->
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
                        </table>

                        <!-- ═══ J - ANEXOS ═══ -->
                        <table class="table table-bordered table-sm lenletratablaResumen">
                            <tbody>
                                <tr>
                                    <td colspan="10" class="table-light">J- ANEXOS</td>
                                </tr>
                            </tbody>

                            <!-- ── Bienes Inmuebles ── -->
                            <tbody>
                                <tr>
                                    <td colspan="10" class="table-light">Bienes Inmuebles</td>
                                </tr>
                            </tbody>
                            <tbody>
                                <tr>
                                    <td colspan="10">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-sm lenletratablaResumen">
                                                <thead>
                                                    <tr>
                                                        <td colspan="2" class="table-light">Tipo</td>
                                                        <td colspan="2" class="table-light">Descripción&nbsp;</td>
                                                        <td colspan="2" class="table-light">Registro&nbsp;</td>
                                                        <td colspan="1" class="table-light">Vivienda Principal</td>
                                                        <td colspan="1" class="table-light">Bien Litigioso</td>
                                                        <td colspan="2" class="table-light">Monto Declarado</td>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td colspan="2">Townhouse</td>
                                                        <td colspan="2">
                                                            <div>
                                                                <textarea class="lthgtextarea22"> 100% de casa quinta tipo townhouse identificada con el numero 04-04. Tipo de Bien Inmueble: Townhouse. Linderos: norte con townhouse numero 04-03; sur con twnhouse 04-05; este con zona de jardin de uso exclusivo y area comun del conjunto; oeste con avenida oeste de la urbanizacion , Superficie Construida: 184 metros cuadrados, Superficie Sin Construir: no aplica, Área o Superficie: no aplica. , Dirección: Cunjunto residencial "Las tunas", urbanizacion Maneiro, municipio Maneiro, Estado Nueva Esparta </textarea>
                                                            </div>
                                                        </td>
                                                        <td colspan="2">
                                                            <div>
                                                                <textarea class="lthgtextarea22"> Oficina Subalterna/Juzgado/Notaría/Misión Vivienda: Oficina Subalterna de Registro Publico del Distrito Maneiro del Estado Nueva Esparta, Número de Registro: 33, Libro: tomo quinto, Protocolo: primero, Fecha: 11/11/1992, Trimestre: tercero, Asiento Registral: 33, Matrícula: no aplica, Libro de Folio Real del Año: 1992</textarea>
                                                            </div>
                                                        </td>
                                                        <td colspan="1" class="text-center">
                                                            <span class="badge rounded-pill bg-success">&nbsp;SI&nbsp;</span>
                                                        </td>
                                                        <td colspan="1" class="text-center">
                                                            <span class="badge rounded-pill bg-danger">&nbsp;NO&nbsp;</span>
                                                        </td>
                                                        <td colspan="2" class="text-end">3.700.000,00</td>
                                                    </tr>
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <td colspan="8" class="text-end">Monto Total</td>
                                                        <td colspan="2" class="text-end">3.700.000,00</td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>

                            <!-- ── Bienes Muebles ── -->
                            <tbody>
                                <tr>
                                    <td colspan="10" class="table-light">Bienes Muebles</td>
                                </tr>
                            </tbody>
                            <tbody>
                                <tr>
                                    <td colspan="10">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-sm lenletratablaResumen">
                                                <thead>
                                                    <tr>
                                                        <td colspan="2" class="table-light">Tipo</td>
                                                        <td colspan="5" class="table-light">Descripción&nbsp;</td>
                                                        <td colspan="1" class="table-light">Bien Litigioso</td>
                                                        <td colspan="2" class="table-light text-end">Monto Declarado</td>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <td colspan="8" class="text-end">Monto Total</td>
                                                        <td colspan="2" class="text-end">0,00</td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>

                            <!-- ── Pasivos ── -->
                            <tbody>
                                <tr>
                                    <td colspan="10" class="table-light">Pasivos</td>
                                </tr>
                            </tbody>
                            <tbody>
                                <tr>
                                    <td colspan="10">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-sm lenletratablaResumen">
                                                <thead>
                                                    <tr>
                                                        <td colspan="2" class="table-light">Tipo</td>
                                                        <td colspan="6" class="table-light">Descripción&nbsp;</td>
                                                        <td colspan="2" class="table-light">Monto Declarado</td>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <td colspan="8" class="text-end">Monto Total</td>
                                                        <td colspan="2" class="text-end">0,00</td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>

                            <!-- ── Desgravamenes ── -->
                            <tbody>
                                <tr>
                                    <td colspan="10" class="table-light">Desgravamenes</td>
                                </tr>
                            </tbody>
                            <tbody>
                                <tr>
                                    <td colspan="10">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-sm lenletratablaResumen">
                                                <thead>
                                                    <tr>
                                                        <td colspan="2" class="table-light">Tipo</td>
                                                        <td colspan="6" class="table-light">Descripción&nbsp;</td>
                                                        <td colspan="2" class="table-light text-end">Monto Declarado</td>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td colspan="2" class="lth">Townhouse</td>
                                                        <td colspan="6">
                                                            <div>
                                                                <textarea class="lthgtextarea23"> 100% de casa quinta tipo townhouse identificada con el numero 04-04. Tipo de Bien Inmueble: Townhouse. Linderos: norte con townhouse numero 04-03; sur con twnhouse 04-05; este con zona de jardin de uso exclusivo y area comun del conjunto; oeste con avenida oeste de la urbanizacion , Superficie Construida: 184 metros cuadrados, Superficie Sin Construir: no aplica, Área o Superficie: no aplica. </textarea>
                                                            </div>
                                                        </td>
                                                        <td colspan="2" class="text-end">3.700.000,00</td>
                                                    </tr>
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <td colspan="8" class="text-end">Monto Total</td>
                                                        <td colspan="2" class="text-end">3.700.000,00</td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>

                            <!-- ── Exenciones ── -->
                            <tbody>
                                <tr>
                                    <td colspan="10" class="table-light">Exenciones</td>
                                </tr>
                            </tbody>
                            <tbody>
                                <tr>
                                    <td colspan="10">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-sm lenletratablaResumen">
                                                <thead>
                                                    <tr>
                                                        <td colspan="2">Tipo</td>
                                                        <td colspan="6">Descripción&nbsp;</td>
                                                        <td colspan="2">Monto Declarado</td>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <td colspan="8" class="text-end">Monto Total</td>
                                                        <td colspan="2" class="text-end">0,00</td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>

                            <!-- ── Exoneraciones ── -->
                            <tbody>
                                <tr>
                                    <td colspan="10" class="table-light">Exoneraciones</td>
                                </tr>
                            </tbody>
                            <tbody>
                                <tr>
                                    <td colspan="10">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-sm lenletratablaResumen">
                                                <thead>
                                                    <tr>
                                                        <td colspan="2" class="table-light">Tipo</td>
                                                        <td colspan="6" class="table-light">Descripción&nbsp;</td>
                                                        <td colspan="2" class="table-light">Monto Declarado</td>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <td colspan="8" class="text-end">Monto Total</td>
                                                        <td colspan="2" class="text-end">0,00</td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>

                            <!-- ── Bienes Litigiosos ── -->
                            <tbody>
                                <tr>
                                    <td colspan="10" class="table-light">Bienes Litigiosos</td>
                                </tr>
                            </tbody>
                            <tbody>
                                <tr>
                                    <td colspan="10">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-sm lenletratablaResumen">
                                                <thead>
                                                    <tr>
                                                        <td colspan="2" class="table-light">Tipo</td>
                                                        <td colspan="6" class="table-light">Descripción&nbsp;</td>
                                                        <td colspan="2" class="table-light">Monto Declarado</td>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <td colspan="8" class="text-end">Monto Total</td>
                                                        <td colspan="2" class="text-end">0,00</td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
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
