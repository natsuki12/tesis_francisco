<?php
/**
 * Tipo Herencia — Herencia > Tipo Herencia
 * Uses: sim_sucesiones_layout.php
 */
$activeMenu = 'herencia';
$activeItem = 'Tipo Herencia';
$extraCss = [
    '/assets/css/simulator/seniat_actual/servicios_declaracion/bootstrap.min.css',
    '/assets/css/simulator/seniat_actual/servicios_declaracion/bootstrap-icons.css',
    '/assets/css/simulator/seniat_actual/sucesion/herencia/tipo_herencia.css',
];
$breadcrumbs = [
    ['label' => 'Inicio', 'url' => '/simulador/sucesion/principal'],
    ['label' => 'Tipo Herencia'],
];

ob_start();
?>

<div _ngcontent-pgi-c66 class="shadow-lg p-3 mb-5 bg-body rounded lenletra">
    <div _ngcontent-pgi-c66>
        <div _ngcontent-pgi-c66 class=card>
            <div _ngcontent-pgi-c66 class=card-header>Tipo Herencia</div>
            <div _ngcontent-pgi-c66 class=card-body>
                <form _ngcontent-pgi-c66 novalidate class="lenletra">
                    <div _ngcontent-pgi-c66 class=row>
                        <div _ngcontent-pgi-c66 class=col-sm-4>
                            <div _ngcontent-pgi-c66 class=form-check>
                                <input _ngcontent-pgi-c66 type=checkbox id=chkPuraSimple class="form-check-input" value=03>
                                <label _ngcontent-pgi-c66 for=chkPuraSimple class=form-check-label> Pura y Simple </label>
                            </div>
                        </div>
                    </div>
                    <div _ngcontent-pgi-c66 class=row>
                        <div _ngcontent-pgi-c66 class=col-sm-4>
                            <div _ngcontent-pgi-c66 class=form-check>
                                <input _ngcontent-pgi-c66 type=checkbox id=chkPresuncionAusencia class="form-check-input" value=04>
                                <label _ngcontent-pgi-c66 for=chkPresuncionAusencia class=form-check-label> Presunción de Ausencia </label>
                            </div>
                        </div>
                    </div>
                    <div _ngcontent-pgi-c66 class=row>
                        <div _ngcontent-pgi-c66 class=col-sm-4>
                            <div _ngcontent-pgi-c66 class=form-check>
                                <input _ngcontent-pgi-c66 type=checkbox id=chkTestamento class="form-check-input" value=01>
                                <label _ngcontent-pgi-c66 for=chkTestamento class=form-check-label> Testamento </label>
                            </div>
                        </div>
                    </div>
                    <div _ngcontent-pgi-c66 class=row>
                        <div _ngcontent-pgi-c66 class=col-sm-4>
                            <div _ngcontent-pgi-c66 class=form-check>
                                <input _ngcontent-pgi-c66 type=checkbox id=chkAbIntestato class="form-check-input" value=02>
                                <label _ngcontent-pgi-c66 for=chkAbIntestato class=form-check-label> Ab-Intestato </label>
                            </div>
                        </div>
                    </div>
                    <div _ngcontent-pgi-c66 class=row>
                        <div _ngcontent-pgi-c66 class=col-sm-4>
                            <div _ngcontent-pgi-c66 class=form-check>
                                <input _ngcontent-pgi-c66 type=checkbox id=chkPresuncionMuerte class="form-check-input" value=05>
                                <label _ngcontent-pgi-c66 for=chkPresuncionMuerte class=form-check-label> Presunción de Muerte por Accidente </label>
                            </div>
                        </div>
                    </div>
                    <div _ngcontent-pgi-c66 class=row>
                        <div _ngcontent-pgi-c66 class=col-sm-4>
                            <div _ngcontent-pgi-c66 class=form-check>
                                <input _ngcontent-pgi-c66 type=checkbox id=chkBeneficioInventario class="form-check-input" value=06>
                                <label _ngcontent-pgi-c66 for=chkBeneficioInventario class=form-check-label> Beneficio de Inventario </label>
                            </div>
                        </div>
                    </div>
                    <div _ngcontent-pgi-c66 class="row py-3">
                        <div _ngcontent-pgi-c66 class=col-sm-12>
                            <button _ngcontent-pgi-c66 type=submit class="btn btn-danger btn-sm">Guardar&nbsp;<i _ngcontent-pgi-c66 class=bi-save></i></button>
                        </div>
                    </div>
                </form>
                <br _ngcontent-pgi-c66>
                <table _ngcontent-pgi-c66 class="table table-bordered table-striped table-sm lenletra">
                    <thead _ngcontent-pgi-c66>
                        <tr _ngcontent-pgi-c66>
                            <th _ngcontent-pgi-c66 scope=col>Tipo de Herencia</th>
                            <th _ngcontent-pgi-c66 scope=col>Tipo</th>
                            <th _ngcontent-pgi-c66 scope=col>Fecha</th>
                            <th _ngcontent-pgi-c66 scope=col>Acción</th>
                        </tr>
                    </thead>
                    <tbody _ngcontent-pgi-c66>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../../../layouts/sim_sucesiones_layout.php';
?>
