<?php
ob_start();
$activeMenu = 'resumen';
$activeItem = 'Ver Resumen';
$breadcrumbs = [
    ['label' => 'Inicio', 'url' => '/simulador/servicios_declaracion/dashboard'],
    ['label' => 'Resumen Declaración'],
];
?>

<div class="shadow-lg p-3 mb-5 bg-body rounded">
    <div class="card">
        <!-- ═══ Card Header ═══ -->
        <div class="card-header">
            <div class="bg-light clearfix">
                <div class="float-start">
                    <span><strong>Cálculo Manual Cuota Parte Hereditaria</strong></span>
                </div>
                <div class="float-end">
                    <a href="<?= base_url('/simulador/sucesion/resumen_declaracion') ?>"
                       class="btn btn-light" title="Regresar">
                        <i class="bi bi-arrow-clockwise"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- ═══ Card Body ═══ -->
        <div class="card-body">
            <div>
                <form novalidate>
                    <!-- Floating inputs row -->
                    <div class="row py-3">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <div class="form-floating">
                                    <input id="ut" placeholder="#" type="text" readonly
                                           class="form-control form-control-sm"
                                           value="0,4000000000">
                                    <label for="ut">Unidad Tributaria Aplicada para cálculo</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <div class="form-floating">
                                    <input id="ip" type="text" placeholder="#" readonly
                                           class="form-control form-control-sm"
                                           style="text-align:right"
                                           value=" 0,00">
                                    <label for="ip">Total Impuesto a Pagar</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Herederos table -->
                    <table class="table table-bordered table-sm lenletratablaResumen">
                        <thead class="table-light">
                            <tr>
                                <th>Apellido(s) y Nombre(s)</th>
                                <th>C.I./Pasaporte</th>
                                <th>Parentesco</th>
                                <th>Grado</th>
                                <th>Premuerto</th>
                                <th>Cuota Parte Hereditaria(UT)</th>
                                <th>Reducción (Bs.)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="text-align:left!important">BAUZA PEDRONI RAMON ERNESTO</td>
                                <td style="text-align:center">V213264954</td>
                                <td style="text-align:center">HIJA/HIJO</td>
                                <td style="text-align:center">1</td>
                                <td style="text-align:center">NO</td>
                                <td><input type="text" class="form-control form-control-sm text-end" style="text-align:right" value="0,00"></td>
                                <td><input type="text" class="form-control form-control-sm text-end" style="text-align:right" value="0,00"></td>
                            </tr>
                            <tr>
                                <td style="text-align:left!important">PEDRONI LEPERVANCHE PAOLA MARIA</td>
                                <td style="text-align:center">V069727138</td>
                                <td style="text-align:center">HIJA/HIJO</td>
                                <td style="text-align:center">1</td>
                                <td style="text-align:center">NO</td>
                                <td><input type="text" class="form-control form-control-sm text-end" style="text-align:right" value="0,00"></td>
                                <td><input type="text" class="form-control form-control-sm text-end" style="text-align:right" value="0,00"></td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="7" class="text-center">
                                    <button type="submit" class="btn btn-sm btn-danger">Calcular</button>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../../../layouts/sim_sucesiones_layout.php';
?>
