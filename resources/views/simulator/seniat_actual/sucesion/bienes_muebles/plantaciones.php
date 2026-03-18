<?php
$activeMenu = 'muebles';
$activeItem = 'Plantaciones';
$breadcrumbs = [
    ['label' => 'Inicio', 'url' => '/simulador/servicios_declaracion/dashboard'],
    ['label' => 'Bienes Muebles'],
    ['label' => 'Plantaciones'],
];

/* ── Data from controller ── */
$intento = $intento ?? null;
$intentoId = $intento['id'] ?? 0;

$borrador = [];
if ($intento && !empty($intento['borrador_json'])) {
    $borrador = json_decode($intento['borrador_json'], true) ?: [];
}
$plantacionesGuardadas = $borrador['bienes_muebles_plantaciones'] ?? [];

ob_start();
?>

<div _ngcontent-sdd-c97 class="shadow-lg p-3 mb-5 bg-body rounded lenletra">
    <div _ngcontent-sdd-c97 class=card>
        <div _ngcontent-sdd-c97 class=card-header>Plantaciones</div>
        <div _ngcontent-sdd-c97 class=card-body>
            <form _ngcontent-sdd-c97 novalidate>

                <!-- ═══ Row 1: Porcentaje · Bien Litigioso · Descripción ═══ -->
                <div _ngcontent-sdd-c97 class=row>
                    <div _ngcontent-sdd-c97 class=col-sm-2>
                        <div _ngcontent-sdd-c97 class=form-group>
                            <div _ngcontent-sdd-c97 class="form-floating sm-4">
                                <input _ngcontent-sdd-c97 id=sporcentaje placeholder=# type=text
                                    formcontrolname=porcentaje currencymask maxlength=6 required
                                    class="decimal-input form-control form-control-sm text-end"
                                    style=text-align:right value=0,01>
                                <label _ngcontent-sdd-c97 for=ssc>Porcentaje %</label>
                            </div>
                        </div>
                    </div>
                    <div _ngcontent-sdd-c97 class=col-sm-2>
                        <div _ngcontent-sdd-c97 class=form-group>
                            <div _ngcontent-sdd-c97 class="form-floating form-floating-sm">
                                <select _ngcontent-sdd-c97 id=bl formcontrolname=indicadorBienLigitioso required
                                    class="form-select form-select-sm">
                                    <option _ngcontent-sdd-c97 value=true>Si</option>
                                    <option _ngcontent-sdd-c97 value=false selected>No</option>
                                </select>
                                <label _ngcontent-sdd-c97 for=bl>Bien Litigioso</label>
                            </div>
                        </div>
                    </div>
                    <div _ngcontent-sdd-c97 class=col-sm-8>
                        <div _ngcontent-sdd-c97 class=form-group>
                            <div _ngcontent-sdd-c97 class="form-floating sm-4">
                                <textarea _ngcontent-sdd-c97 id=sc placeholder=# formcontrolname=descripcion
                                    maxlength=4999 required
                                    class="form-control form-control-sm"></textarea>
                                <label _ngcontent-sdd-c97 for=sc>Descripción</label>
                            </div>
                            <div _ngcontent-sdd-c97 class="col-sm-6 text-danger"></div>
                        </div>
                    </div>
                </div>

                <!-- ═══ Datos del Tribunal (partial) ═══ -->
                <?php include __DIR__ . '/_datos_tribunal.php'; ?>

                <!-- ═══ Row 2: Valor Declarado ═══ -->
                <div _ngcontent-sdd-c97 class="row py-3">
                    <div _ngcontent-sdd-c97 class=col-sm-6>&nbsp;</div>
                    <div _ngcontent-sdd-c97 class=col-sm-6>
                        <div _ngcontent-sdd-c97 class=form-group>
                            <div _ngcontent-sdd-c97 class="form-floating sm-4">
                                <input _ngcontent-sdd-c97 id=ssc placeholder=# type=text
                                    formcontrolname=valorDeclarado currencymask required
                                    class="decimal-input form-control form-control-sm text-end"
                                    style=text-align:right value=0,00>
                                <label _ngcontent-sdd-c97 for=ssc>Valor Declarado (Bs.)</label>
                            </div>
                        </div>
                    </div>
                </div>

                <button _ngcontent-sdd-c97 type=submit class="btn btn-sm btn-danger" disabled>Guardar <i _ngcontent-sdd-c97 class=bi-save></i></button>
            </form>
        </div>
    </div>
    <br _ngcontent-sdd-c97>

    <!-- ═══ Table ═══ -->
    <div id="tablaContainerPlantaciones" style="display:none">
        <table _ngcontent-sdd-c97 class="table table-bordered table-striped table-sm">
            <thead _ngcontent-sdd-c97>
                <tr _ngcontent-sdd-c97>
                    <th _ngcontent-sdd-c97 scope=col>Tipo de Bien</th>
                    <th _ngcontent-sdd-c97 scope=col>Bien Litigioso</th>
                    <th _ngcontent-sdd-c97 scope=col>Descripción</th>
                    <th _ngcontent-sdd-c97 scope=col>Valor Declarado (Bs.)</th>
                    <th _ngcontent-sdd-c97 scope=col>Acción</th>
                </tr>
            </thead>
            <tbody _ngcontent-sdd-c97 id="tbodyPlantaciones"></tbody>
        </table>
    </div>
</div>

<script>
    var plantacionesItems = <?= json_encode($plantacionesGuardadas, JSON_UNESCAPED_UNICODE) ?>;

    document.addEventListener('DOMContentLoaded', function () {
        const form = document.querySelector('form');
        const btn = form.querySelector('button[type=submit]');
        const tbody = document.getElementById('tbodyPlantaciones');
        if (!form || !btn) return;

        const requiredFields = form.querySelectorAll(
            'input[required]:not([disabled]), textarea[required], select[required]'
        );

        // ═══ Validate form ═══
        function validateForm() {
            let valid = true;
            requiredFields.forEach(function (f) {
                if (f.disabled) return;
                if (!f.value || f.value.trim() === '') valid = false;
            });
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
            const container = document.getElementById('tablaContainerPlantaciones');
            tbody.innerHTML = '';
            let totalDeclarado = 0;

            if (plantacionesItems.length === 0) {
                container.style.display = 'none';
            } else {
                container.style.display = '';
            }

            plantacionesItems.forEach(function (item, idx) {
                const vd = parseFloat((item.valor_declarado || '0').replace(/\./g, '').replace(',', '.'));
                totalDeclarado += vd;

                const desc = ` ${item.porcentaje || '0,01'}% de ${item.descripcion || ''}.`;

                const tr = document.createElement('tr');
                tr.setAttribute('_ngcontent-sdd-c97', '');
                tr.innerHTML = `
                <td _ngcontent-sdd-c97>Plantaciones</td>
                <td _ngcontent-sdd-c97>${item.bien_litigioso === 'true' ? 'Si' : 'No'}</td>
                <td _ngcontent-sdd-c97 class=lthgf><div _ngcontent-sdd-c97 style=width:auto> ${desc}</div></td>
                <td _ngcontent-sdd-c97 align=right>${item.valor_declarado || '0,00'}</td>
                <td _ngcontent-sdd-c97>
                    <div _ngcontent-sdd-c97 class=accionesicono>
                        <i _ngcontent-sdd-c97 class="bi bi-pencil-fill" onclick="editarPlantacion(${idx})" title="Modificar"></i>&nbsp;
                        <i _ngcontent-sdd-c97 class="bi-trash-fill" onclick="eliminarPlantacion(${idx})" title="Eliminar"></i>
                    </div>
                </td>`;
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
        }

        // ═══ Collect form data ═══
        function getFormData() {
            var data = {
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
            document.getElementById('bl').value = 'false';
            resetTribunal();
            document.getElementById('sporcentaje').value = '0,01';
            document.getElementById('sc').value = '';
            document.getElementById('ssc').value = '0,00';
        }

        // ═══ Fill form for editing ═══
        function fillForm(item) {
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
            apiSlug:      'plantaciones',
            items:        plantacionesItems,
            getFormData:  getFormData,
            resetForm:    resetForm,
            renderTable:  renderTable,
            fillForm:     fillForm,
            validateForm: validateForm,
            editName:     'editarPlantacion',
            deleteName:   'eliminarPlantacion'
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
