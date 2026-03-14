<?php
/**
 * Banco — Bienes Muebles > Banco
 * Uses: sim_sucesiones_layout.php
 */
$activeMenu = 'muebles';
$activeItem = 'Banco';
$extraCss = ['/assets/css/simulator/seniat_actual/sucesion/banco/banco.css'];
$breadcrumbs = [
    ['label' => 'Inicio', 'url' => '/simulador/servicios_declaracion'],
    ['label' => 'Bienes Muebles'],
    ['label' => 'Banco'],
];

ob_start();
?>

<div _ngcontent-pgi-c72 class="shadow-lg p-3 mb-5 bg-body rounded lenletra">
    <div _ngcontent-pgi-c72 class=card>
        <div _ngcontent-pgi-c72 class=card-header>Banco</div>
        <div _ngcontent-pgi-c72 class=card-body>
            <form _ngcontent-pgi-c72 novalidate class="ng-pristine ng-invalid ng-touched">
                <div _ngcontent-pgi-c72 class=row>
                    <div _ngcontent-pgi-c72 class=col-sm-6>
                        <div _ngcontent-pgi-c72 class=form-group>
                            <div _ngcontent-pgi-c72 class=form-floating><select _ngcontent-pgi-c72
                                    placeholder="Seleccione el Tipo de Bien" formcontrolname=codTipoBien required
                                    class="form-select form-select-sm ng-pristine ng-invalid ng-touched">
                                    <option _ngcontent-pgi-c72 value=37>Acciones
                                    <option _ngcontent-pgi-c72 value=39>Cajas de Ahorro
                                    <option _ngcontent-pgi-c72 value=04>Cuentas Bancarias
                                    <option _ngcontent-pgi-c72 value=05>Fideicomiso
                                    <option _ngcontent-pgi-c72 value=06>Inventario Caja de Seguridad
                                    <option _ngcontent-pgi-c72 value=38>Prestaciones Sociales
                                </select><label _ngcontent-pgi-c72 for=tb>Tipo de Bien</label></div>
                        </div>
                    </div>
                    <div _ngcontent-pgi-c72 class=col-sm-6>
                        <div _ngcontent-pgi-c72 class=form-group>
                            <div _ngcontent-pgi-c72 class=form-floating><select _ngcontent-pgi-c72 id=vp
                                    formcontrolname=codBanco required
                                    class="form-select form-select-sm ng-untouched ng-pristine ng-invalid">
                                    <option _ngcontent-pgi-c72 value=7>BANCO PROVINCIAL
                                    <option _ngcontent-pgi-c72 value=6>BANCO CENTRAL DE VENEZUELA
                                    <option _ngcontent-pgi-c72 value=5>BANCO DE VENEZUELA
                                    <option _ngcontent-pgi-c72 value=4>BANCO MERCANTIL
                                    <option _ngcontent-pgi-c72 value=3>BANCO DEL TESORO
                                    <option _ngcontent-pgi-c72 value=-1>NO APLICA
                                    <option _ngcontent-pgi-c72 value=8>BANCO EXTERIOR
                                    <option _ngcontent-pgi-c72 value=9>BANCO CARONI
                                    <option _ngcontent-pgi-c72 value=11>BANCO SOFITASA
                                    <option _ngcontent-pgi-c72 value=13>100% BANCO
                                    <option _ngcontent-pgi-c72 value=14>BANCO ACTIVO
                                    <option _ngcontent-pgi-c72 value=17>BANCO VENEZOLANO DE CREDITO
                                    <option _ngcontent-pgi-c72 value=20>BANPLUS
                                    <option _ngcontent-pgi-c72 value=21>BANCAMIGA
                                    <option _ngcontent-pgi-c72 value=22>BANCRECER
                                    <option _ngcontent-pgi-c72 value=23>BANCO AGRICOLA DE VENEZUELA
                                    <option _ngcontent-pgi-c72 value=16>BANCO NACIONAL DE CREDITO, C.A. BANCO UNIVERSAL
                                    <option _ngcontent-pgi-c72 value=24>BANCO DE LA FUERZA ARMADA NACIONAL BOLIVARIANA,
                                        BANCO UNIVERSAL
                                    <option _ngcontent-pgi-c72 value=19>BANCO SOFITASA BANCO UNIVERSAL, C. A.
                                    <option _ngcontent-pgi-c72 value=10>BANESCO BANCO UNIVERSAL, C.A.
                                    <option _ngcontent-pgi-c72 value=18>BANCO PLAZA, C.A., BANCO UNIVERSAL
                                    <option _ngcontent-pgi-c72 value=25>BANCO DE COMERCIO EXTERIOR, C.A., BANCOEX
                                    <option _ngcontent-pgi-c72 value=26>BANCO DE LA GENTE EMPRENDEDORA (BANGENTE). C.A.
                                    <option _ngcontent-pgi-c72 value=12>BFC BANCO FONDO COMÚN C.A., BANCO UNIVERSAL
                                    <option _ngcontent-pgi-c72 value=27>DEL SUR BANCO UNIVERSAL, C.A.
                                    <option _ngcontent-pgi-c72 value=29>MI BANCO, BANCO MICROFINANCIERO, C.A.
                                    <option _ngcontent-pgi-c72 value=999>INSTITUTO MUNICIPAL DE CREDITO POPULAR
                                    <option _ngcontent-pgi-c72 value=30>BANCO DEL CARIBE
                                    <option _ngcontent-pgi-c72 value=31>BANCO INTERNACIONAL DE DESARROLLO
                                    <option _ngcontent-pgi-c72 value=998>OTROS BANCOS
                                    <option _ngcontent-pgi-c72 value=2>BANCO DIGITAL DE LOS TRABAJADORES
                                </select><label _ngcontent-pgi-c72 for=vp>Nombre Banco</label></div>
                        </div>
                    </div>
                </div><br _ngcontent-pgi-c72>
                <div _ngcontent-pgi-c72 class="row py-3">
                    <div _ngcontent-pgi-c72 class=col-sm-6>
                        <div _ngcontent-pgi-c72 class=form-group>
                            <div _ngcontent-pgi-c72 class="form-floating sm-4"><input _ngcontent-pgi-c72 id=lind
                                    placeholder=# type=text formcontrolname=numeroCuenta maxlength=20 required
                                    class="form-control form-control-sm ng-untouched ng-pristine ng-invalid"
                                    value><label _ngcontent-pgi-c72 for=lind>Número de Cuenta</label></div>
                        </div>
                    </div>
                    <div _ngcontent-pgi-c72 class=col-sm-6>
                        <div _ngcontent-pgi-c72 class=form-group>
                            <div _ngcontent-pgi-c72 class="form-floating form-floating-sm"><select _ngcontent-pgi-c72
                                    id=bl formcontrolname=indicadorBienLigitioso required
                                    class="form-select form-select-sm ng-untouched ng-pristine ng-valid">
                                    <option _ngcontent-pgi-c72 value=true>Si
                                    <option _ngcontent-pgi-c72 value=false selected>No
                                </select><label _ngcontent-pgi-c72 for=bl>Bien Litigioso</label></div>
                        </div>
                    </div>
                </div>
                <div _ngcontent-pgi-c72 class=row>
                    <div _ngcontent-pgi-c72 class=col-sm-2>
                        <div _ngcontent-pgi-c72 class=form-group>
                            <div _ngcontent-pgi-c72 class="form-floating sm-4"><input _ngcontent-pgi-c72 id=sporcentaje
                                    placeholder=# type=text formcontrolname=porcentaje currencymask maxlength=6 required
                                    class="form-control form-control-sm text-end ng-untouched ng-pristine ng-valid"
                                    style=text-align:right value=0,01><label _ngcontent-pgi-c72 for=ssc>Porcentaje
                                    %</label></div>
                        </div>
                    </div>
                    <div _ngcontent-pgi-c72 class=col-sm-10>
                        <div _ngcontent-pgi-c72 class=form-group>
                            <div _ngcontent-pgi-c72 class="form-floating sm-4"><textarea _ngcontent-pgi-c72 id=sc
                                    placeholder=# formcontrolname=descripcion maxlength=4999 required
                                    class="form-control form-control-sm ng-untouched ng-pristine ng-invalid"></textarea><label
                                    _ngcontent-pgi-c72 for=sc>Descripción</label></div>
                        </div>
                    </div>
                </div><br _ngcontent-pgi-c72>
                <div _ngcontent-pgi-c72 class=row>
                    <div _ngcontent-pgi-c72 class=col-sm-6> &nbsp; </div>
                    <div _ngcontent-pgi-c72 class=col-sm-6>
                        <div _ngcontent-pgi-c72 class=form-group>
                            <div _ngcontent-pgi-c72 class="form-floating sm-4"><input _ngcontent-pgi-c72 id=ssc
                                    placeholder=# type=text formcontrolname=valorDeclarado currencymask required
                                    class="form-control form-control-sm text-end ng-untouched ng-pristine ng-invalid"
                                    style=text-align:right value=0,00><label _ngcontent-pgi-c72 for=ssc>Valor Declarado
                                    (Bs.)</label></div>
                        </div>
                    </div>
                </div><button _ngcontent-pgi-c72 type=submit class="btn btn-sm btn-danger" disabled>Guardar <i
                        _ngcontent-pgi-c72 class=bi-save></i></button>
            </form>
        </div>
    </div><br _ngcontent-pgi-c72>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../../../layouts/sim_sucesiones_layout.php';
?>