<?php
/**
 * Seguro — Bienes Muebles > Seguro
 * Uses: sim_sucesiones_layout.php
 */
$activeMenu = 'muebles';
$activeItem = 'Seguro';
$extraCss = ['/assets/css/simulator/seniat_actual/sucesion/bienes_muebles/seguro.css'];
$breadcrumbs = [
    ['label' => 'Inicio', 'url' => '/simulador/servicios_declaracion/dashboard'],
    ['label' => 'Bienes Muebles'],
    ['label' => 'Seguro'],
];

ob_start();

$intentoId = $intento['id'] ?? null;
$borradorJson = $intento['borrador_json'] ?? '{}';
$borradorData = json_decode($borradorJson ?: '{}', true) ?: [];
$segurosGuardados = $borradorData['bienes_muebles_seguro'] ?? [];
?>

<div _ngcontent-sdd-c88 class="shadow-lg p-3 mb-5 bg-body rounded lenletra">
    <div _ngcontent-sdd-c88 class=card>
        <div _ngcontent-sdd-c88 class=card-header>Seguro</div>
        <div _ngcontent-sdd-c88 class=card-body>
            <form _ngcontent-sdd-c88 novalidate class="ng-pristine ng-invalid ng-touched">
                <div _ngcontent-sdd-c88 class=row>
                    <div _ngcontent-sdd-c88 class=col-sm-3>
                        <div _ngcontent-sdd-c88 class=form-group>
                            <div _ngcontent-sdd-c88 class=form-floating><select _ngcontent-sdd-c88
                                    placeholder="Seleccione el Tipo de Bien" formcontrolname=codTipoBien required
                                    class="form-select form-select-sm ng-pristine ng-invalid ng-touched">
                                    <?php foreach ($tiposBien as $tb): ?>
                                        <option _ngcontent-sdd-c88 value="<?= $tb['tipo_bien_mueble_id'] ?>"><?= htmlspecialchars($tb['nombre']) ?>
                                    <?php endforeach; ?>
                                </select><label _ngcontent-sdd-c88 for=tb>Tipo de Bien</label></div>
                        </div>
                    </div>
                    <div _ngcontent-sdd-c88 class=col-sm-3>
                        <div _ngcontent-sdd-c88 class=form-group>
                            <div _ngcontent-sdd-c88 class="form-floating sm-4"><input _ngcontent-sdd-c88 id=rifEmpresa
                                    placeholder=# type=text formcontrolname=rifEmpresa maxlength=10 required
                                    class="form-control form-control-sm ng-pristine ng-invalid ng-touched" value><label
                                    _ngcontent-sdd-c88 for=rifEmpresa>Rif Empresa</label></div>
                            <div _ngcontent-sdd-c88 id="errorRifEmpresa" class="col-sm-4 text-danger"
                                style="display:none">Favor ingrese el formato válido Ej: J012345678</div>
                        </div>
                    </div>
                    <div _ngcontent-sdd-c88 class=col-sm-6>
                        <div _ngcontent-sdd-c88 class=form-group>
                            <div _ngcontent-sdd-c88 class=form-floating><input _ngcontent-sdd-c88 id=razonSocial
                                    placeholder=# type=text formcontrolname=razonSocial required
                                    class="form-control form-control-sm ng-untouched ng-pristine" disabled value><label
                                    _ngcontent-sdd-c88 for=razonSocial>Razón Social</label></div>
                        </div>
                    </div>
                </div><br _ngcontent-sdd-c88>
                <div _ngcontent-sdd-c88 class=row>
                    <div _ngcontent-sdd-c88 class=col-sm-6>
                        <div _ngcontent-sdd-c88 class=form-group>
                            <div _ngcontent-sdd-c88 class="form-floating sm-4"><input _ngcontent-sdd-c88 id=numeroPrima
                                    placeholder=# type=text formcontrolname=numeroPrima maxlength=15 required
                                    class="form-control form-control-sm ng-pristine ng-invalid ng-touched" value><label
                                    _ngcontent-sdd-c88 for=numeroPrima>Número de Prima</label></div>
                            <div _ngcontent-sdd-c88 class="col-sm-6 text-danger"></div>
                        </div>
                    </div>
                    <div _ngcontent-sdd-c88 class=col-sm-6>
                        <div _ngcontent-sdd-c88 class=form-group>
                            <div _ngcontent-sdd-c88 class=form-floating><select _ngcontent-sdd-c88 id=bl
                                    formcontrolname=indicadorBienLigitioso required
                                    class="form-select form-select-sm ng-untouched ng-pristine ng-valid">
                                    <option _ngcontent-sdd-c88 value=true>Si
                                    <option _ngcontent-sdd-c88 value=false selected>No
                                </select><label _ngcontent-sdd-c88 for=bl>Bien Litigioso</label></div>
                        </div>
                    </div>
                </div>
                <?php include __DIR__ . '/_datos_tribunal.php'; ?>
                <div _ngcontent-sdd-c88 class="row py-3">
                    <div _ngcontent-sdd-c88 class=col-sm-2>
                        <div _ngcontent-sdd-c88 class=form-group>
                            <div _ngcontent-sdd-c88 class="form-floating sm-4"><input _ngcontent-sdd-c88 id=sporcentaje
                                    placeholder=# type=text formcontrolname=porcentaje currencymask maxlength=6 required
                                    class="decimal-input form-control form-control-sm text-end ng-untouched ng-pristine ng-valid"
                                    style=text-align:right value=0,01><label _ngcontent-sdd-c88
                                    for=sporcentaje>Porcentaje
                                    %</label></div>
                        </div>
                    </div>
                    <div _ngcontent-sdd-c88 class=col-sm-10>
                        <div _ngcontent-sdd-c88 class=form-group>
                            <div _ngcontent-sdd-c88 class=form-floating><textarea _ngcontent-sdd-c88 id=sc placeholder=#
                                    formcontrolname=descripcion maxlength=4999 required
                                    class="form-control form-control-sm ng-untouched ng-pristine ng-invalid"></textarea><label
                                    _ngcontent-sdd-c88 for=sc>Descripción</label></div>
                            <div _ngcontent-sdd-c88 class="col-sm-6 text-danger"></div>
                        </div>
                    </div>
                </div><br _ngcontent-sdd-c88>
                <div _ngcontent-sdd-c88 class=row>
                    <div _ngcontent-sdd-c88 class=col-sm-6> &nbsp; </div>
                    <div _ngcontent-sdd-c88 class=col-sm-6>
                        <div _ngcontent-sdd-c88 class=form-group>
                            <div _ngcontent-sdd-c88 class="form-floating sm-4"><input _ngcontent-sdd-c88 id=ssc
                                    placeholder=# type=text formcontrolname=valorDeclarado currencymask required
                                    class="decimal-input form-control form-control-sm text-end ng-untouched ng-pristine ng-invalid"
                                    style=text-align:right value=0,00><label _ngcontent-sdd-c88 for=ssc>Valor Declarado
                                    (Bs.)</label></div>
                        </div>
                    </div>
                </div><button _ngcontent-sdd-c88 type=submit class="btn btn-sm btn-danger" disabled>Guardar <i
                        _ngcontent-sdd-c88 class=bi-save></i></button>
            </form>
        </div>
    </div><br _ngcontent-sdd-c88>

    <!-- ═══ Tabla de Bienes Muebles (Seguro) Registrados ═══ -->
    <div _ngcontent-sdd-c88 id="tablaContainerSeguro">
        <table _ngcontent-sdd-c88 id="tablaSeguro" class="table table-bordered table-striped table-sm">
            <thead _ngcontent-sdd-c88>
                <tr _ngcontent-sdd-c88>
                    <th _ngcontent-sdd-c88 scope=col>Tipo de Mueble
                    <th _ngcontent-sdd-c88 scope=col>Bien Litigioso
                    <th _ngcontent-sdd-c88 scope=col>Descripción
                    <th _ngcontent-sdd-c88 scope=col>Valor Declarado (Bs.)
                    <th _ngcontent-sdd-c88 scope=col>Acción
            <tbody _ngcontent-sdd-c88 id="tbodySeguro">
            </tbody>
        </table>
    </div>
</div>

<script>
    var seguros = <?= json_encode($segurosGuardados, JSON_UNESCAPED_UNICODE) ?>;

    document.addEventListener('DOMContentLoaded', function () {
        const form = document.querySelector('form');
        const btn = form.querySelector('button[type=submit]');
        const tbody = document.getElementById('tbodySeguro');
        if (!form || !btn) return;

        // Razón Social is disabled and not required for submission
        const requiredFields = form.querySelectorAll(
            'input[required]:not([disabled]), textarea[required], select[required]'
        );

        // ═══ Validate form ═══
        function validateForm() {
            let valid = true;
            requiredFields.forEach(f => {
                if (!f.value || f.value.trim() === '') valid = false;
            });

            // RIF Empresa format validation (letter + 9 digits = 10 chars)
            const rifInput = document.getElementById('rifEmpresa');
            const errorRif = document.getElementById('errorRifEmpresa');
            const rifVal = rifInput.value;
            const rifFormatOk = /^[JGVEP]\d{9}$/i.test(rifVal);

            if (rifVal.length === 0) {
                errorRif.style.display = 'none';
                valid = false;
            } else if (!rifFormatOk) {
                // Formato inválido → bloquear
                errorRif.textContent = 'Favor ingrese el formato válido Ej: J012345678';
                errorRif.style.display = '';
                valid = false;
            } else if (rifVal.charAt(0).toUpperCase() !== 'J') {
                // Formato válido pero no empieza por J → advertencia, NO bloquear
                errorRif.textContent = 'El RIF no comienza por J. Verifique que sea correcto.';
                errorRif.style.display = '';
            } else {
                // Formato válido y empieza por J → todo OK
                errorRif.style.display = 'none';
            }

            // If bien litigioso = Si, tribunal fields are also required
            var bl = document.getElementById('bl');
            if (bl && bl.value === 'true') {
                ['litigioNroExpediente', 'litigioTribunalCausa', 'litigioPartesJuicio', 'litigioEstadoJuicio'].forEach(function (id) {
                    var el = document.getElementById(id);
                    if (!el || !el.value || el.value.trim() === '') valid = false;
                });
            }

            btn.disabled = !valid;
        }

        requiredFields.forEach(f => {
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

    // ═══ RIF lookup — auto-fill Razón Social (global) ═══
        initRifLookup({
            rifInputId:   'rifEmpresa',
            razonInputId: 'razonSocial',
            baseUrl:      BASE,
            onResult:     validateForm
        });

        // ═══ Render table ═══
        function renderTable() {
            const container = document.getElementById('tablaContainerSeguro');
            tbody.innerHTML = '';
            let totalDeclarado = 0;

            if (seguros.length === 0) {
                container.style.display = 'none';
            } else {
                container.style.display = '';
            }

            seguros.forEach((item, idx) => {
                const vd = parseFloat((item.valor_declarado || '0').replace(/\./g, '').replace(',', '.'));
                totalDeclarado += vd;

                const desc = ` ${item.porcentaje || '0,01'}% de ${item.descripcion || ''}. RIF Aseguradora: ${item.rif_empresa || ''} ${item.razon_social || ''}, Número Prima: ${item.numero_prima || ''}. `;

                const tr = document.createElement('tr');
                tr.innerHTML = `
                <td>${item.tipo_bien_nombre || ''}</td>
                <td>${item.bien_litigioso === 'true' ? 'Si' : 'No'}</td>
                <td class=lthgf><div><textarea class=lthgtextarea readonly>${desc}</textarea></div></td>
                <td align=right>${item.valor_declarado || '0,00'}</td>
                <td>
                    <div class=accionesicono>
                        <i class="bi bi-pencil-fill" onclick="editarSeguro(${idx})" title="Modificar"></i>&nbsp;
                        <i class="bi-trash-fill" onclick="eliminarSeguro(${idx})" title="Eliminar"></i>
                    </div>
                </td>`;
                tbody.appendChild(tr);
            });

            // Total row as last tr in tbody
            var trTotal = document.createElement('tr');
            trTotal.innerHTML =
                '<td></td>' +
                '<td></td>' +
                '<td>Total:</td>' +
                '<td align=right> ' + totalDeclarado.toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '</td>' +
                '<td></td>';
            tbody.appendChild(trTotal);
        }

        // ═══ Collect form data ═══
        function getFormData() {
            const tipoBienSel = form.querySelector('[formcontrolname=codTipoBien]');
            var data = {
            tipo_bien: tipoBienSel.value,
            tipo_bien_nombre: tipoBienSel.options[tipoBienSel.selectedIndex]?.text?.trim() || '',
            rif_empresa: document.getElementById('rifEmpresa').value,
            razon_social: document.getElementById('razonSocial').value,
            numero_prima: document.getElementById('numeroPrima').value,
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
            form.querySelector('[formcontrolname=codTipoBien]').selectedIndex = 0;
            document.getElementById('rifEmpresa').value = '';
            document.getElementById('razonSocial').value = '';
            document.getElementById('numeroPrima').value = '';
            document.getElementById('bl').value = 'false';
            resetTribunal();
            document.getElementById('sporcentaje').value = '0,01';
            document.getElementById('sc').value = '';
            document.getElementById('ssc').value = '0,00';
        }

        // ═══ Fill form for editing ═══
        function fillForm(item) {
            // Select tipo bien
            const tipoBienSel = form.querySelector('[formcontrolname=codTipoBien]');
            for (let i = 0; i < tipoBienSel.options.length; i++) {
                if (tipoBienSel.options[i].value === item.tipo_bien) {
                    tipoBienSel.selectedIndex = i;
                    break;
                }
            }

            document.getElementById('rifEmpresa').value = item.rif_empresa || '';
            document.getElementById('razonSocial').value = item.razon_social || '';
            document.getElementById('numeroPrima').value = item.numero_prima || '';
            document.getElementById('bl').value = item.bien_litigioso || 'false';
            setTribunalData(item);
            document.getElementById('sporcentaje').value = item.porcentaje || '0,01';
            document.getElementById('sc').value = item.descripcion || '';
            document.getElementById('ssc').value = item.valor_declarado || '0,00';
        }

        // ═══ CRUD Manager (global) ═══
        initCrudManager({
            intentoId:    INTENTO_ID,
            baseUrl:      BASE,
            apiSlug:      'seguro',
            items:        seguros,
            getFormData:  getFormData,
            resetForm:    resetForm,
            renderTable:  renderTable,
            fillForm:     fillForm,
            validateForm: validateForm,
            editName:     'editarSeguro',
            deleteName:   'eliminarSeguro'
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