<?php
$activeMenu = 'inmuebles';
$activeItem = 'Bienes Inmuebles';
$extraCss = [
    '/assets/css/simulator/seniat_actual/sucesion/bienes_inmuebles/bienes_inmuebles.css',
];
$breadcrumbs = [
    ['label' => 'Inicio', 'url' => '/simulador/servicios_declaracion/dashboard'],
    ['label' => 'Bienes Inmuebles'],
];

ob_start();

$intentoId = $intento['id'] ?? null;
$borradorJson = $intento['borrador_json'] ?? '{}';
$borradorData = json_decode($borradorJson ?: '{}', true) ?: [];
$inmueblesGuardados = $borradorData['bienes_inmuebles'] ?? [];
?>

<div _ngcontent-pgi-c71 class="shadow-lg p-3 mb-5 bg-body rounded lenletra">
    <div _ngcontent-pgi-c71 class=card>
        <div _ngcontent-pgi-c71 class=card-header> Bienes Inmuebles </div>
        <div _ngcontent-pgi-c71 class=card-body>
            <div _ngcontent-pgi-c71 class="text-primary py-3 text-center">Debe indicar todos los campos del formulario. En caso de que la información no sea requerida colocar "NO APLICA" </div>
            <form _ngcontent-pgi-c71 id="formInmueble" novalidate class="ng-untouched ng-pristine ng-invalid">
                <!-- Tipo de Bien checkboxes -->
                <div _ngcontent-pgi-c71 class="row py-2">
                    <h6 _ngcontent-pgi-c71 class=py-1>Tipo de Bien</h6>
                    <div _ngcontent-pgi-c71 formarrayname=tipoBienes class="checkbox-group ng-untouched ng-pristine ng-valid">
                        <?php foreach ($tiposBienInmueble as $tipo): ?>
                        <div _ngcontent-pgi-c71 class=checkbox-item><div _ngcontent-pgi-c71 class=form-check><input _ngcontent-pgi-c71 type=checkbox class="form-check-input ng-untouched ng-pristine ng-valid" id="tipo_<?= e($tipo['tipo_bien_inmueble_id']) ?>" value="<?= e($tipo['tipo_bien_inmueble_id']) ?>" data-nombre="<?= e($tipo['nombre']) ?>"><label _ngcontent-pgi-c71 class=form-check-label for="tipo_<?= e($tipo['tipo_bien_inmueble_id']) ?>"> <?= e($tipo['nombre']) ?> </label></div></div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Vivienda Principal / Bien Litigioso / Porcentaje -->
                <div _ngcontent-pgi-c71 class=row>
                    <div _ngcontent-pgi-c71 class=col-sm-4>
                        <div _ngcontent-pgi-c71 class=form-group>
                            <div _ngcontent-pgi-c71 class=form-floating>
                                <select _ngcontent-pgi-c71 id=vp formcontrolname=indicadorViviendaPrincipalA required class="form-select form-select-sm ng-untouched ng-pristine" disabled>
                                    <option _ngcontent-pgi-c71 value=true>Si
                                    <option _ngcontent-pgi-c71 value=false selected>No
                                </select><label _ngcontent-pgi-c71 for=vp>Vivienda Principal</label>
                            </div>
                        </div>
                    </div>
                    <div _ngcontent-pgi-c71 class=col-sm-4>
                        <div _ngcontent-pgi-c71 class=form-group>
                            <div _ngcontent-pgi-c71 class=form-floating>
                                <select _ngcontent-pgi-c71 id=bl formcontrolname=indicadorBienLigitiosoAS required class="form-select form-select-sm ng-untouched ng-pristine ng-valid">
                                    <option _ngcontent-pgi-c71 value=true>Si
                                    <option _ngcontent-pgi-c71 value=false selected>No
                                </select><label _ngcontent-pgi-c71 for=bl>Bien Litigioso</label>
                            </div>
                        </div>
                    </div>
                    <div _ngcontent-pgi-c71 class=col-sm-4>
                        <div _ngcontent-pgi-c71 class=form-group>
                            <div _ngcontent-pgi-c71 class=form-floating>
                                <input _ngcontent-pgi-c71 id=des placeholder=# type=text formcontrolname=porcentaje currencymask maxlength=6 required class="decimal-input form-control form-control-sm ng-untouched ng-pristine ng-valid" style=text-align:right value=0,01>
                                <label _ngcontent-pgi-c71 for=des>Porcentaje %</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Datos del Tribunal (shown when Bien Litigioso = Si) -->
                <?php include __DIR__ . '/../bienes_muebles/_datos_tribunal.php'; ?>
                <!-- Descripción -->
                <div _ngcontent-pgi-c71 class="row py-3">
                    <div _ngcontent-pgi-c71 class=col-sm-12>
                        <div _ngcontent-pgi-c71 class=form-group>
                            <div _ngcontent-pgi-c71 class=form-floating>
                                <textarea _ngcontent-pgi-c71 id=desc_inp placeholder=# formcontrolname=descripcion maxlength=4999 required class="form-control form-control-sm ng-untouched ng-pristine ng-invalid"></textarea>
                                <label _ngcontent-pgi-c71 for=desc_inp>Descripción</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Linderos -->
                <div _ngcontent-pgi-c71 class="row py-3">
                    <div _ngcontent-pgi-c71 class=col-sm-12>
                        <div _ngcontent-pgi-c71 class=form-group>
                            <div _ngcontent-pgi-c71 class=form-floating>
                                <textarea _ngcontent-pgi-c71 id=lind placeholder=# formcontrolname=linderos maxlength=4999 required class="form-control form-control-sm ng-untouched ng-pristine ng-invalid"></textarea>
                                <label _ngcontent-pgi-c71 for=lind>Linderos</label>
                            </div>
                            <div _ngcontent-pgi-c71 class="col-sm-6 text-danger"></div>
                        </div>
                    </div>
                </div>

                <!-- Superficie Construida / Sin Construir / Área -->
                <div _ngcontent-pgi-c71 class="row py-3">
                    <div _ngcontent-pgi-c71 class=col-sm-4>
                        <div _ngcontent-pgi-c71 class=form-group>
                            <div _ngcontent-pgi-c71 class=form-floating>
                                <input _ngcontent-pgi-c71 id=sc placeholder=# type=text formcontrolname=superficieConstruida maxlength=20 required class="form-control form-control-sm ng-untouched ng-pristine ng-invalid" value>
                                <label _ngcontent-pgi-c71 for=sc>Superficie Construida</label>
                            </div>
                            <div _ngcontent-pgi-c71 class="col-sm-6 text-danger"></div>
                        </div>
                    </div>
                    <div _ngcontent-pgi-c71 class=col-sm-4>
                        <div _ngcontent-pgi-c71 class=form-group>
                            <div _ngcontent-pgi-c71 class=form-floating>
                                <input _ngcontent-pgi-c71 id=ssc placeholder=# type=text formcontrolname=superficieSinConstruir maxlength=20 required class="form-control form-control-sm ng-untouched ng-pristine ng-invalid" value>
                                <label _ngcontent-pgi-c71 for=ssc>Superficie sin Construir</label>
                            </div>
                        </div>
                    </div>
                    <div _ngcontent-pgi-c71 class=col-sm-4>
                        <div _ngcontent-pgi-c71 class=form-group>
                            <div _ngcontent-pgi-c71 class=form-floating>
                                <input _ngcontent-pgi-c71 id=sup placeholder=# type=text formcontrolname=superficie maxlength=20 required class="form-control form-control-sm ng-untouched ng-pristine ng-invalid" value>
                                <label _ngcontent-pgi-c71 for=sup>Área o Superficie</label>
                            </div>
                            <div _ngcontent-pgi-c71 class="col-sm-6 text-danger"></div>
                        </div>
                    </div>
                </div>

                <!-- Dirección -->
                <div _ngcontent-pgi-c71 class="row py-3">
                    <div _ngcontent-pgi-c71 class=col-sm-12>
                        <div _ngcontent-pgi-c71 class=form-group>
                            <div _ngcontent-pgi-c71 class=form-floating>
                                <textarea _ngcontent-pgi-c71 id=dir placeholder=# type=text formcontrolname=direccion maxlength=4999 required class="form-control form-control-sm ng-untouched ng-pristine ng-invalid"></textarea>
                                <label _ngcontent-pgi-c71 for=dir>Dirección</label>
                            </div>
                            <div _ngcontent-pgi-c71 class="col-sm-6 text-danger"></div>
                        </div>
                    </div>
                </div>

                <!-- Oficina Subalterna -->
                <div _ngcontent-pgi-c71 class="row py-3">
                    <div _ngcontent-pgi-c71 class=col-sm-12>
                        <div _ngcontent-pgi-c71 class=form-group>
                            <div _ngcontent-pgi-c71 class=form-floating>
                                <textarea _ngcontent-pgi-c71 id=os placeholder=# type=text formcontrolname=oficinaSubalterna maxlength=300 required class="form-control form-control-sm ng-untouched ng-pristine ng-invalid"></textarea>
                                <label _ngcontent-pgi-c71 for=os>Oficina Subalterna/ Juzgado/ Notaría/ Misión Vivienda</label>
                            </div>
                            <div _ngcontent-pgi-c71 class="col-sm-6 text-danger"></div>
                        </div>
                    </div>
                </div>

                <!-- Nro de Registro / Libro -->
                <div _ngcontent-pgi-c71 class="row py-3">
                    <div _ngcontent-pgi-c71 class=col-sm-6>
                        <div _ngcontent-pgi-c71 class=form-group>
                            <div _ngcontent-pgi-c71 class=form-floating>
                                <input _ngcontent-pgi-c71 id=nr placeholder=# type=text formcontrolname=nroRegistro maxlength=20 required class="form-control form-control-sm ng-untouched ng-pristine ng-invalid" value>
                                <label _ngcontent-pgi-c71 for=nr>Nro de Registro</label>
                            </div>
                            <div _ngcontent-pgi-c71 class="col-sm-6 text-danger"></div>
                        </div>
                    </div>
                    <div _ngcontent-pgi-c71 class=col-sm-6>
                        <div _ngcontent-pgi-c71 class=form-group>
                            <div _ngcontent-pgi-c71 class=form-floating>
                                <input _ngcontent-pgi-c71 id=lib placeholder=# type=text formcontrolname=libro maxlength=20 required class="form-control form-control-sm ng-untouched ng-pristine ng-invalid" value>
                                <label _ngcontent-pgi-c71 for=lib>Libro</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Protocolo / Fecha -->
                <div _ngcontent-pgi-c71 class="row py-3">
                    <div _ngcontent-pgi-c71 class=col-sm-6>
                        <div _ngcontent-pgi-c71 class=form-group>
                            <div _ngcontent-pgi-c71 class=form-floating>
                                <input _ngcontent-pgi-c71 id=pr placeholder=# type=text formcontrolname=protocolo maxlength=20 required class="form-control form-control-sm ng-untouched ng-pristine ng-invalid" value>
                                <label _ngcontent-pgi-c71 for=pr>Protocolo</label>
                            </div>
                        </div>
                    </div>
                    <div _ngcontent-pgi-c71 class=col-sm-6>
                        <div _ngcontent-pgi-c71 class=form-group>
                            <label _ngcontent-pgi-c71 for=lfr>Fecha</label>
                            <div _ngcontent-pgi-c71 class=input-group>
                                <input _ngcontent-pgi-c71 id=lfr placeholder="Seleccione Fecha" type=text formcontrolname=fecha ngbdatepicker required class="form-control form-control-sm ng-untouched ng-pristine ng-invalid" value>
                                <i _ngcontent-pgi-c71 placement=top ngbtooltip=Calendario class="bi bi-calendar3 btn btn-outline-secondary"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Trimestre / Asiento Registral -->
                <div _ngcontent-pgi-c71 class="row py-3">
                    <div _ngcontent-pgi-c71 class=col-sm-6>
                        <div _ngcontent-pgi-c71 class=form-group>
                            <div _ngcontent-pgi-c71 class=form-floating>
                                <input _ngcontent-pgi-c71 id=tr placeholder=# type=text formcontrolname=trimestre maxlength=15 required class="form-control form-control-sm ng-untouched ng-pristine ng-invalid" value>
                                <label _ngcontent-pgi-c71 for=tr>Trimestre</label>
                            </div>
                        </div>
                    </div>
                    <div _ngcontent-pgi-c71 class=col-sm-6>
                        <div _ngcontent-pgi-c71 class=form-group>
                            <div _ngcontent-pgi-c71 class=form-floating>
                                <input _ngcontent-pgi-c71 id=ar placeholder=# type=text formcontrolname=asientoRegistral maxlength=15 required class="form-control form-control-sm ng-untouched ng-pristine ng-invalid" value>
                                <label _ngcontent-pgi-c71 for=ar>Asiento Registral</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Matrícula / Libro de Folio Real -->
                <div _ngcontent-pgi-c71 class="row py-3">
                    <div _ngcontent-pgi-c71 class=col-sm-6>
                        <div _ngcontent-pgi-c71 class=form-group>
                            <div _ngcontent-pgi-c71 class=form-floating>
                                <input _ngcontent-pgi-c71 id=mtr placeholder=# type=text formcontrolname=matricula maxlength=20 required class="form-control form-control-sm ng-untouched ng-pristine ng-invalid" value>
                                <label _ngcontent-pgi-c71 for=mtr>Matricula</label>
                            </div>
                        </div>
                    </div>
                    <div _ngcontent-pgi-c71 class=col-sm-6>
                        <div _ngcontent-pgi-c71 class=form-group>
                            <div _ngcontent-pgi-c71 class=form-floating>
                                <input _ngcontent-pgi-c71 id=lfol placeholder=# type=text formcontrolname=libroFolio maxlength=15 required class="form-control form-control-sm ng-untouched ng-pristine ng-invalid" value>
                                <label _ngcontent-pgi-c71 for=lfol>Libro de Folio Real del Año</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Valor Original / Valor Declarado -->
                <div _ngcontent-pgi-c71 class="row py-3">
                    <div _ngcontent-pgi-c71 class=col-sm-6>
                        <div _ngcontent-pgi-c71 class=form-group>
                            <div _ngcontent-pgi-c71 class=form-floating>
                                <input _ngcontent-pgi-c71 id=vor placeholder=# type=text formcontrolname=valorOriginalBienASBs currencymask class="decimal-input form-control form-control-sm text-end ng-untouched ng-pristine ng-invalid" style=text-align:right value=0,00>
                                <label _ngcontent-pgi-c71 for=vor>Valor Original (Bs.)</label>
                            </div>
                        </div>
                    </div>
                    <div _ngcontent-pgi-c71 class=col-sm-6>
                        <div _ngcontent-pgi-c71 class=form-group>
                            <div _ngcontent-pgi-c71 class="form-floating sm-4">
                                <input _ngcontent-pgi-c71 id=vdec placeholder=# type=text formcontrolname=valorDeclaradoASBs currencymask required class="decimal-input form-control form-control-sm text-end ng-untouched ng-pristine ng-invalid" style=text-align:right value=0,00>
                                <label _ngcontent-pgi-c71 for=vdec>Valor Declarado (Bs.)</label>
                            </div>
                        </div>
                    </div>
                </div>

                <button _ngcontent-pgi-c71 type=submit id="btnGuardar" class="btn btn-sm btn-danger" disabled>Guardar <i _ngcontent-pgi-c71 class=bi-save></i></button>
            </form>
        </div>

        <!-- ═══ Tabla de Inmuebles Registrados ═══ -->
        <div _ngcontent-pgi-c71 class=card-body id="tablaContainer" style="display:none">
            <div _ngcontent-pgi-c71 class=table-responsive>
                <table _ngcontent-pgi-c71 id=tableim class="table table-bordered table-striped table-sm lenletra">
                    <thead _ngcontent-pgi-c71>
                        <tr _ngcontent-pgi-c71>
                            <th _ngcontent-pgi-c71 scope=col class=lth>Tipo de Bien
                            <th _ngcontent-pgi-c71 scope=col class=lth>Vivienda Principal
                            <th _ngcontent-pgi-c71 scope=col class=lth>Bien Litigioso
                            <th _ngcontent-pgi-c71 scope=col class=lthg>Dirección
                            <th _ngcontent-pgi-c71 scope=col class=lthg>Linderos
                            <th _ngcontent-pgi-c71 scope=col class=lthg>Datos del Registro
                            <th _ngcontent-pgi-c71 scope=col class=lthg>Descripción
                            <th _ngcontent-pgi-c71 scope=col class=lth>Valor Según Documento (Bs.)
                            <th _ngcontent-pgi-c71 scope=col class=lth>Valor Declarado (Bs.)
                            <th _ngcontent-pgi-c71 scope=col class=lth>Acción
                    <tbody _ngcontent-pgi-c71 id="tbodyInmuebles">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
var inmuebles = <?= json_encode($inmueblesGuardados, JSON_UNESCAPED_UNICODE) ?>;
var editIndex = null;

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formInmueble');
    const btn  = document.getElementById('btnGuardar');
    const vpSelect = document.getElementById('vp');
    const tbody = document.getElementById('tbodyInmuebles');
    if (!form || !btn) return;

    const checkboxes = form.querySelectorAll('input[type=checkbox]');
    const requiredFields = form.querySelectorAll(
        'input[required], textarea[required], select[required]'
    );

    // Nombres que desbloquean Vivienda Principal
    const nombresVP = ['casa', 'apartamento', 'townhouse', 'quinta'];

    function toggleViviendaPrincipal() {
        const alguno = Array.from(checkboxes).some(cb => {
            const nombre = (cb.dataset.nombre || '').toLowerCase().trim();
            return cb.checked && nombresVP.includes(nombre);
        });

        // Ya existe un inmueble con vivienda principal (excluir el que se edita)
        const yaExisteVP = inmuebles.some((item, idx) =>
            item.vivienda_principal === 'true' && idx !== editIndex
        );

        vpSelect.disabled = !alguno || yaExisteVP;
        if (!alguno || yaExisteVP) vpSelect.value = 'false';
    }

    function validateForm() {
        const anyChecked = Array.from(checkboxes).some(cb => cb.checked);
        if (!anyChecked) { btn.disabled = true; return; }

        const allFilled = Array.from(requiredFields).every(field => {
            if (field.disabled) return true;
            return field.value.trim() !== '';
        });

        const porcVal = parseFloat((document.getElementById('des')?.value || '0').replace(/\./g, '').replace(',', '.'));
        const porcOk = porcVal >= 0.01;

        const vorVal = parseFloat((document.getElementById('vor')?.value || '0').replace(/\./g, '').replace(',', '.'));
        const vdecVal = parseFloat((document.getElementById('vdec')?.value || '0').replace(/\./g, '').replace(',', '.'));
        const valoresOk = vorVal >= 0 && vdecVal >= 0;

        let valid = true;
        // If bien litigioso = Si, tribunal fields are also required
        var bl = document.getElementById('bl');
        if (bl && bl.value === 'true') {
            ['litigioNroExpediente', 'litigioTribunalCausa', 'litigioPartesJuicio', 'litigioEstadoJuicio'].forEach(function (id) {
                var el = document.getElementById(id);
                if (!el || !el.value || el.value.trim() === '') valid = false;
            });
        }

        btn.disabled = !(allFilled && porcOk && valoresOk && valid);
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            toggleViviendaPrincipal();
            validateForm();
        });
    });
    requiredFields.forEach(field => {
        field.addEventListener('input', validateForm);
        field.addEventListener('change', validateForm);
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
        const container = document.getElementById('tablaContainer');
        tbody.innerHTML = '';
        let totalDeclarado = 0;

        if (inmuebles.length === 0) {
            container.style.display = 'none';
        } else {
            container.style.display = '';
        }

        inmuebles.forEach((item, idx) => {
            const vd = parseFloat((item.valor_declarado || '0').replace(/\./g, '').replace(',', '.'));
            totalDeclarado += vd;

            const datosReg = `Oficina Subalterna: ${item.oficina_registro || ''}, Nro Registro: ${item.nro_registro || ''}, Libro: ${item.libro || ''}, Protocolo: ${item.protocolo || ''}, Fecha: ${item.fecha_registro || ''}, Trimestre: ${item.trimestre || ''}, Asiento Registral: ${item.asiento_registral || ''}, Matrícula: ${item.matricula || ''}, Libro Folio Real: ${item.folio_real_anio || ''}`;

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class=lth>${item.tipo_bien_nombres || ''}</td>
                <td class=lth>${item.vivienda_principal === 'true' ? 'Si' : 'No'}</td>
                <td class=lth>${item.bien_litigioso === 'true' ? 'Si' : 'No'}</td>
                <td class=lthg><div style="word-wrap:break-word;max-width:150px">${item.direccion || ''}</div></td>
                <td class=lthg><div><textarea class=lthgtextarea2 readonly>${item.linderos || ''}</textarea></div></td>
                <td class=lthg><div><textarea class=lthgtextarea2 readonly>${datosReg}</textarea></div></td>
                <td class=lthg><div style="word-wrap:break-word;max-width:150px">${item.descripcion || ''}</div></td>
                <td align=right class=lth>${item.valor_original || '0,00'}</td>
                <td align=right class=lth>${item.valor_declarado || '0,00'}</td>
                <td><div class="accionesicono lth">
                    <i class="bi bi-pencil-fill" style="cursor:pointer" title="Modificar" onclick="editarInmueble(${idx})"></i>
                    <i class="bi-trash-fill" style="cursor:pointer" title="Eliminar" onclick="eliminarInmueble(${idx})"></i>
                </div></td>`;
            tbody.appendChild(tr);
        });

        // Total row as last tr in tbody
        var trTotal = document.createElement('tr');
        trTotal.innerHTML =
            '<td></td>' +
            '<td></td>' +
            '<td></td>' +
            '<td></td>' +
            '<td></td>' +
            '<td></td>' +
            '<td></td>' +
            '<td align=right>Total:</td>' +
            '<td align=right> ' + totalDeclarado.toLocaleString('es-VE', {minimumFractionDigits:2, maximumFractionDigits:2}) + '</td>' +
            '<td></td>';
        tbody.appendChild(trTotal);
    }

    // ═══ Collect form data ═══
    function getFormData() {
        const checkedIds = [];
        const checkedNames = [];
        checkboxes.forEach(cb => {
            if (cb.checked) {
                checkedIds.push(cb.value);
                checkedNames.push(cb.dataset.nombre || '');
            }
        });

        var data = {
            tipo_bien_inmueble_id: checkedIds,
            tipo_bien_nombres: checkedNames.join(', '),
            vivienda_principal: document.getElementById('vp').value,
            bien_litigioso: document.getElementById('bl').value,
            porcentaje: document.getElementById('des').value,
            descripcion: document.getElementById('desc_inp').value,
            linderos: document.getElementById('lind').value,
            superficie_construida: document.getElementById('sc').value,
            superficie_no_construida: document.getElementById('ssc').value,
            area_superficie: document.getElementById('sup').value,
            direccion: document.getElementById('dir').value,
            oficina_registro: document.getElementById('os').value,
            nro_registro: document.getElementById('nr').value,
            libro: document.getElementById('lib').value,
            protocolo: document.getElementById('pr').value,
            fecha_registro: document.getElementById('lfr').value,
            trimestre: document.getElementById('tr').value,
            asiento_registral: document.getElementById('ar').value,
            matricula: document.getElementById('mtr').value,
            folio_real_anio: document.getElementById('lfol').value,
            valor_original: document.getElementById('vor').value,
            valor_declarado: document.getElementById('vdec').value,
        };
        Object.assign(data, getTribunalData());
        return data;
    }

    // ═══ Reset form ═══
    function resetForm() {
        checkboxes.forEach(cb => cb.checked = false);
        form.querySelectorAll('input[type=text], textarea').forEach(el => {
            if (el.hasAttribute('currencymask')) { el.value = '0,00'; }
            else { el.value = ''; }
        });
        document.getElementById('vp').value = 'false';
        document.getElementById('bl').value = 'false';
        document.getElementById('des').value = '0,01';
        resetTribunal();
        editIndex = null;
        btn.textContent = 'Guardar ';
        const icon = document.createElement('i');
        icon.className = 'bi-save';
        btn.appendChild(icon);
        toggleViviendaPrincipal();
        validateForm();
    }

    // ═══ Fill form for editing ═══
    window.editarInmueble = function(idx) {
        const item = inmuebles[idx];
        if (!item) return;
        editIndex = idx;

        // Checkboxes
        const ids = item.tipo_bien_inmueble_id || [];
        checkboxes.forEach(cb => cb.checked = ids.includes(cb.value) || ids.includes(parseInt(cb.value)));

        document.getElementById('vp').value = item.vivienda_principal || 'false';
        document.getElementById('bl').value = item.bien_litigioso || 'false';
        document.getElementById('des').value = item.porcentaje || '0,01';
        document.getElementById('desc_inp').value = item.descripcion || '';
        document.getElementById('lind').value = item.linderos || '';
        document.getElementById('sc').value = item.superficie_construida || '';
        document.getElementById('ssc').value = item.superficie_no_construida || '';
        document.getElementById('sup').value = item.area_superficie || '';
        document.getElementById('dir').value = item.direccion || '';
        document.getElementById('os').value = item.oficina_registro || '';
        document.getElementById('nr').value = item.nro_registro || '';
        document.getElementById('lib').value = item.libro || '';
        document.getElementById('pr').value = item.protocolo || '';
        document.getElementById('lfr').value = item.fecha_registro || '';
        document.getElementById('tr').value = item.trimestre || '';
        document.getElementById('ar').value = item.asiento_registral || '';
        document.getElementById('mtr').value = item.matricula || '';
        document.getElementById('lfol').value = item.folio_real_anio || '';
        document.getElementById('vor').value = item.valor_original || '0,00';
        document.getElementById('vdec').value = item.valor_declarado || '0,00';

        setTribunalData(item);

        btn.textContent = 'Actualizar ';
        const icon = document.createElement('i');
        icon.className = 'bi-save';
        btn.appendChild(icon);

        toggleViviendaPrincipal();
        validateForm();
        window.scrollTo({top: 0, behavior: 'smooth'});
    };

    // ═══ Delete ═══
    window.eliminarInmueble = function(idx) {
        if (!confirm('¿Está seguro de eliminar este inmueble?')) return;
        if (!INTENTO_ID) { alert('No hay intento activo'); return; }

        fetch(BASE + '/api/bienes-inmuebles/' + INTENTO_ID + '/eliminar', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({index: idx})
        })
        .then(r => r.json())
        .then(data => {
            if (data.ok) {
                inmuebles.splice(idx, 1);
                renderTable();
            } else {
                alert(data.error || 'Error al eliminar');
            }
        })
        .catch(() => alert('Error de conexión'));
    };

    // ═══ Form submit ═══
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        if (!INTENTO_ID) { alert('No hay intento activo'); return; }

        const payload = getFormData();
        const isEdit = editIndex !== null;
        const url = isEdit
            ? BASE + '/api/bienes-inmuebles/' + INTENTO_ID + '/editar'
            : BASE + '/api/bienes-inmuebles/' + INTENTO_ID + '/agregar';

        if (isEdit) payload.index = editIndex;

        btn.disabled = true;
        fetch(url, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(payload)
        })
        .then(r => r.json())
        .then(data => {
            if (data.ok) {
                if (isEdit) {
                    inmuebles[editIndex] = payload;
                } else {
                    inmuebles.push(payload);
                }
                renderTable();
                resetForm();
            } else {
                alert(data.error || 'Error al guardar');
                validateForm();
            }
        })
        .catch(() => {
            alert('Error de conexión');
            validateForm();
        });
    });

    // Initial render
    toggleViviendaPrincipal();
    validateForm();
    renderTable();
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../../../layouts/sim_sucesiones_layout.php';
?>
