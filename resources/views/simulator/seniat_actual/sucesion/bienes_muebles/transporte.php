<?php
/**
 * Transporte — Bienes Muebles > Transporte
 * Uses: sim_sucesiones_layout.php
 */
$activeMenu = 'muebles';
$activeItem = 'Transporte';
$extraCss = [];
$breadcrumbs = [
    ['label' => 'Inicio', 'url' => '/simulador/servicios_declaracion/dashboard'],
    ['label' => 'Bienes Muebles'],
    ['label' => 'Transporte'],
];

$intentoId = $intento['id'] ?? null;
$borradorJson = $intento['borrador_json'] ?? '{}';
$borradorData = json_decode($borradorJson ?: '{}', true) ?: [];
$transportesGuardados = $borradorData['bienes_muebles_transporte'] ?? [];

ob_start();
?>

<div _ngcontent-sdd-c89 class="shadow-lg p-3 mb-5 bg-body rounded lenletra">
    <div _ngcontent-sdd-c89 class=card>
        <div _ngcontent-sdd-c89 class=card-header>Transporte</div>
        <div _ngcontent-sdd-c89 class=card-body>
            <form _ngcontent-sdd-c89 novalidate class="ng-untouched ng-pristine ng-invalid" id="formTransporte">
                <div _ngcontent-sdd-c89 class=row>
                    <div _ngcontent-sdd-c89 class=col-sm-6>
                        <div _ngcontent-sdd-c89 class=form-group>
                            <div _ngcontent-sdd-c89 class=form-floating><select _ngcontent-sdd-c89
                                    placeholder="Seleccione el Tipo de Bien" formcontrolname=codTipoBien required
                                    class="form-select form-select-sm ng-untouched ng-pristine ng-invalid"
                                    id="tipoBienTransporte">
                                    <option _ngcontent-sdd-c89 value="" selected>Seleccione</option>
                                    <?php foreach ($tiposBien as $tb): ?>
                                        <option _ngcontent-sdd-c89 value="<?= $tb['tipo_bien_mueble_id'] ?>"><?= htmlspecialchars($tb['nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select><label _ngcontent-sdd-c89 for=tb>Tipo de Bien</label></div>
                        </div>
                    </div>
                    <div _ngcontent-sdd-c89 class=col-sm-6>
                        <div _ngcontent-sdd-c89 class=form-group>
                            <div _ngcontent-sdd-c89 class=form-floating><input _ngcontent-sdd-c89 id=anio
                                    placeholder=# type=text formcontrolname=anio maxlength=4 required
                                    class="form-control form-control-sm ng-untouched ng-pristine ng-invalid"
                                    value><label _ngcontent-sdd-c89 for=anio>Año</label></div>
                            <div _ngcontent-sdd-c89 class="col-sm-6 text-danger"></div>
                        </div>
                    </div>
                </div><br _ngcontent-sdd-c89>
                <div _ngcontent-sdd-c89 class=row>
                    <div _ngcontent-sdd-c89 class=col-sm-6>
                        <div _ngcontent-sdd-c89 class=form-group>
                            <div _ngcontent-sdd-c89 class=form-floating><input _ngcontent-sdd-c89 id=marca
                                    placeholder=# type=text formcontrolname=marca maxlength=15 required
                                    class="form-control form-control-sm ng-untouched ng-pristine ng-invalid"
                                    value><label _ngcontent-sdd-c89 for=marca>Marca</label></div>
                            <div _ngcontent-sdd-c89 class="col-sm-6 text-danger"></div>
                        </div>
                    </div>
                    <div _ngcontent-sdd-c89 class=col-sm-6>
                        <div _ngcontent-sdd-c89 class=form-group>
                            <div _ngcontent-sdd-c89 class=form-floating><input _ngcontent-sdd-c89 id=modelo
                                    placeholder=# type=text formcontrolname=modelo maxlength=15 required
                                    class="form-control form-control-sm ng-untouched ng-pristine ng-invalid"
                                    value><label _ngcontent-sdd-c89 for=modelo>Modelo</label></div>
                            <div _ngcontent-sdd-c89 class="col-sm-6 text-danger"></div>
                        </div>
                    </div>
                </div><br _ngcontent-sdd-c89>
                <div _ngcontent-sdd-c89 class=row>
                    <div _ngcontent-sdd-c89 class=col-sm-6>
                        <div _ngcontent-sdd-c89 class=form-group>
                            <div _ngcontent-sdd-c89 class=form-floating><input _ngcontent-sdd-c89 id=serial
                                    placeholder=# type=text formcontrolname=serial maxlength=30 required
                                    class="form-control form-control-sm ng-untouched ng-pristine ng-invalid"
                                    value><label _ngcontent-sdd-c89
                                    for=serial>Serial/Número Identificador/Placas</label></div>
                            <div _ngcontent-sdd-c89 class="col-sm-6 text-danger"></div>
                        </div>
                    </div>
                    <div _ngcontent-sdd-c89 class=col-sm-6>
                        <div _ngcontent-sdd-c89 class=form-group>
                            <div _ngcontent-sdd-c89 class=form-floating><select _ngcontent-sdd-c89 id=bl
                                    formcontrolname=indicadorBienLigitioso required
                                    class="form-select form-select-sm ng-untouched ng-pristine ng-valid">
                                    <option _ngcontent-sdd-c89 value=true>Si</option>
                                    <option _ngcontent-sdd-c89 value=false selected>No</option>
                                </select><label _ngcontent-sdd-c89 for=bl>Bien Litigioso</label></div>
                        </div>
                    </div>
                </div>
                <?php include __DIR__ . '/_datos_tribunal.php'; ?>
                <div _ngcontent-sdd-c89 class="row py-3">
                    <div _ngcontent-sdd-c89 class=col-sm-2>
                        <div _ngcontent-sdd-c89 class=form-group>
                            <div _ngcontent-sdd-c89 class="form-floating sm-4"><input _ngcontent-sdd-c89 id=sporcentaje
                                    placeholder=# type=text formcontrolname=porcentaje currencymask maxlength=6 required
                                    class="decimal-input form-control form-control-sm text-end ng-untouched ng-pristine ng-valid"
                                    style=text-align:right value=0,01><label _ngcontent-sdd-c89
                                    for=sporcentaje>Porcentaje %</label></div>
                        </div>
                    </div>
                    <div _ngcontent-sdd-c89 class=col-sm-10>
                        <div _ngcontent-sdd-c89 class=form-group>
                            <div _ngcontent-sdd-c89 class=form-floating><textarea _ngcontent-sdd-c89 id=sc placeholder=#
                                    formcontrolname=descripcion maxlength=4999 required
                                    class="form-control form-control-sm ng-untouched ng-pristine ng-invalid"></textarea><label
                                    _ngcontent-sdd-c89 for=sc>Descripción</label></div>
                            <div _ngcontent-sdd-c89 class="col-sm-6 text-danger"></div>
                        </div>
                    </div>
                </div><br _ngcontent-sdd-c89>
                <div _ngcontent-sdd-c89 class=row>
                    <div _ngcontent-sdd-c89 class=col-sm-6> &nbsp; </div>
                    <div _ngcontent-sdd-c89 class=col-sm-6>
                        <div _ngcontent-sdd-c89 class=form-group>
                            <div _ngcontent-sdd-c89 class=form-floating><input _ngcontent-sdd-c89 id=ssc placeholder=#
                                    type=text formcontrolname=valorDeclarado currencymask required
                                    class="decimal-input form-control form-control-sm text-end ng-untouched ng-pristine ng-invalid"
                                    style=text-align:right value=0,00><label _ngcontent-sdd-c89 for=ssc>Valor Declarado
                                    (Bs.)</label></div>
                        </div>
                    </div>
                </div><button _ngcontent-sdd-c89 type=submit class="btn btn-sm btn-danger" disabled
                    id="btnGuardarTransporte">Guardar <i _ngcontent-sdd-c89 class=bi-save></i></button>
            </form>
        </div>
    </div><br _ngcontent-sdd-c89>

    <!-- ═══ Tabla de Transportes Registrados ═══ -->
    <div id="tablaContainerTransporte" style="display:none">
        <table _ngcontent-sdd-c89 class="table table-bordered table-striped table-sm lenletra">
            <thead _ngcontent-sdd-c89>
                <tr _ngcontent-sdd-c89>
                    <th _ngcontent-sdd-c89 scope=col>Tipo de Mueble</th>
                    <th _ngcontent-sdd-c89 scope=col>Bien Litigioso</th>
                    <th _ngcontent-sdd-c89 scope=col>Descripción</th>
                    <th _ngcontent-sdd-c89 scope=col>Valor Declarado (Bs.)</th>
                    <th _ngcontent-sdd-c89 scope=col>Acción</th>
                </tr>
            </thead>
            <tbody _ngcontent-sdd-c89 id="tbodyTransporte"></tbody>
        </table>
    </div>
</div>

<script>
    var transportes = <?= json_encode($transportesGuardados, JSON_UNESCAPED_UNICODE) ?>;

    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('formTransporte');
        const btn = document.getElementById('btnGuardarTransporte');
        const tbody = document.getElementById('tbodyTransporte');
        if (!form || !btn) return;

        const requiredFields = form.querySelectorAll(
            'input[required], textarea[required], select[required]'
        );

        // ═══ Validate form ═══
        function validateForm() {
            try {
                let valid = true;
                requiredFields.forEach(function (f) {
                    if (!f.value || f.value.trim() === '') valid = false;
                });

                // If bien litigioso = Si, tribunal fields are also required
                var bl = document.getElementById('bl');
                if (bl && bl.value === 'true') {
                    var tribunalIds = ['litigioNroExpediente', 'litigioTribunalCausa', 'litigioPartesJuicio', 'litigioEstadoJuicio'];
                    tribunalIds.forEach(function (id) {
                        var el = document.getElementById(id);
                        if (!el || !el.value || el.value.trim() === '') valid = false;
                    });
                }

                btn.disabled = !valid;
            } catch (err) {
                console.error('[Transporte::validateForm]', err);
            }
        }

        requiredFields.forEach(function (f) {
            f.addEventListener('input', validateForm);
            f.addEventListener('change', validateForm);
        });

        // Also listen on tribunal fields + bien litigioso select
        ['bl', 'litigioNroExpediente', 'litigioTribunalCausa', 'litigioPartesJuicio', 'litigioEstadoJuicio'].forEach(function (id) {
            var el = document.getElementById(id);
            if (el) {
                el.addEventListener('input', validateForm);
                el.addEventListener('change', validateForm);
            }
        });

        // ═══ Toggle Datos del Tribunal (global) ═══
        initTribunalToggle();

        // ═══ Render table ═══
        function renderTable() {
            try {
                const container = document.getElementById('tablaContainerTransporte');
                tbody.innerHTML = '';
                let totalDeclarado = 0;

                if (transportes.length === 0) {
                    container.style.display = 'none';
                } else {
                    container.style.display = '';
                }

                transportes.forEach(function (item, idx) {
                    const vd = parseFloat((item.valor_declarado || '0').replace(/\./g, '').replace(',', '.'));
                    totalDeclarado += vd;

                    const desc = ' ' + (item.porcentaje || '0,01') + '% de ' + (item.descripcion || '') +
                        '. Año: ' + (item.anio || '') +
                        ', Marca: ' + (item.marca || '') +
                        ', Modelo: ' + (item.modelo || '') +
                        ', Serial/Número Identificador/Placas: ' + (item.serial || '') + '. ';

                    const tr = document.createElement('tr');
                    tr.innerHTML =
                        '<td>' + (item.tipo_bien_nombre || '') + '</td>' +
                        '<td>' + (item.bien_litigioso === 'true' ? 'Si' : 'No') + '</td>' +
                        '<td class=lthgf><div style="width:auto">' + desc + '</div></td>' +
                        '<td align=right>' + (item.valor_declarado || '0,00') + '</td>' +
                        '<td><div class=accionesicono>' +
                        '<i class="bi bi-pencil-fill" onclick="editarTransporte(' + idx + ')" title="Modificar"></i>&nbsp; ' +
                        '<i class="bi-trash-fill" onclick="eliminarTransporte(' + idx + ')" title="Eliminar"></i>' +
                        '</div></td>';
                    tbody.appendChild(tr);
                });

                // Total row as last tr in tbody
                var trTotal = document.createElement('tr');
                trTotal.innerHTML =
                    '<td></td>' +
                    '<td></td>' +
                    '<td align=right>Total:</td>' +
                    '<td align=right> ' + totalDeclarado.toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '</td>' +
                    '<td></td>';
                tbody.appendChild(trTotal);
            } catch (err) {
                console.error('[Transporte::renderTable]', err);
            }
        }

        // ═══ Collect form data ═══
        function getFormData() {
            const tipoBienSel = document.getElementById('tipoBienTransporte');
            var data = {
                tipo_bien: tipoBienSel.value,
                tipo_bien_nombre: tipoBienSel.options[tipoBienSel.selectedIndex]?.text?.trim() || '',
                anio: document.getElementById('anio').value,
                marca: document.getElementById('marca').value,
                modelo: document.getElementById('modelo').value,
                serial: document.getElementById('serial').value,
                bien_litigioso: document.getElementById('bl').value,
                porcentaje: document.getElementById('sporcentaje').value,
                descripcion: document.getElementById('sc').value,
                valor_declarado: document.getElementById('ssc').value,
            };
            Object.assign(data, getTribunalData());
            return data;
        }

        // ═══ Reset form ═══
        function resetForm() {
            try {
                document.getElementById('tipoBienTransporte').selectedIndex = 0;
                document.getElementById('anio').value = '';
                document.getElementById('marca').value = '';
                document.getElementById('modelo').value = '';
                document.getElementById('serial').value = '';
                document.getElementById('bl').value = 'false';
                resetTribunal();
                document.getElementById('sporcentaje').value = '0,01';
                document.getElementById('sc').value = '';
                document.getElementById('ssc').value = '0,00';
            } catch (err) {
                console.error('[Transporte::resetForm]', err);
            }
        }

        // ═══ Fill form for editing ═══
        function fillForm(item) {
            try {
                const tipoBienSel = document.getElementById('tipoBienTransporte');
                for (let i = 0; i < tipoBienSel.options.length; i++) {
                    if (tipoBienSel.options[i].value === item.tipo_bien) {
                        tipoBienSel.selectedIndex = i;
                        break;
                    }
                }

                document.getElementById('anio').value = item.anio || '';
                document.getElementById('marca').value = item.marca || '';
                document.getElementById('modelo').value = item.modelo || '';
                document.getElementById('serial').value = item.serial || '';
                document.getElementById('bl').value = item.bien_litigioso || 'false';
                setTribunalData(item);
                document.getElementById('sporcentaje').value = item.porcentaje || '0,01';
                document.getElementById('sc').value = item.descripcion || '';
                document.getElementById('ssc').value = item.valor_declarado || '0,00';
            } catch (err) {
                console.error('[Transporte::fillForm]', err);
            }
        }

        // ═══ CRUD Manager (global) ═══
        initCrudManager({
            intentoId:    INTENTO_ID,
            baseUrl:      BASE,
            apiSlug:      'transporte',
            items:        transportes,
            formSel:      '#formTransporte',
            getFormData:  getFormData,
            resetForm:    resetForm,
            renderTable:  renderTable,
            fillForm:     fillForm,
            validateForm: validateForm,
            editName:     'editarTransporte',
            deleteName:   'eliminarTransporte'
        });

        // Initial render
        renderTable();
        validateForm();
    });
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../../../layouts/sim_sucesiones_layout.php';
?>
