<?php
$activeMenu = 'pasivosDeuda';
$activeItem = 'Tarjetas de Crédito';
$breadcrumbs = [
    ['label' => 'Inicio', 'url' => '/simulador/servicios_declaracion/dashboard'],
    ['label' => 'Pasivos'],
    ['label' => 'Tarjetas de Crédito'],
];

/* ── Data from controller ── */
$intento = $intento ?? null;
$intentoId = $intento['id'] ?? 0;

$borrador = [];
if ($intento && !empty($intento['borrador_json'])) {
    $borrador = json_decode($intento['borrador_json'], true) ?: [];
}
$tdcGuardados = $borrador['pasivos_deuda_tdc'] ?? [];

ob_start();
?>

<div _ngcontent-sdd-c80 class="shadow-lg p-3 mb-5 bg-body rounded lenletra">
    <div _ngcontent-sdd-c80 class=card>
        <div _ngcontent-sdd-c80 class=card-header>Tarjetas de Crédito</div>
        <div _ngcontent-sdd-c80 class=card-body>
            <form _ngcontent-sdd-c80 novalidate>

                <!-- ═══ Row 1: Tipo de Pasivo · Tipo de Deuda ═══ -->
                <div _ngcontent-sdd-c80 class=row>
                    <div _ngcontent-sdd-c80 class=col-sm-6>
                        <div _ngcontent-sdd-c80 class=form-group>
                            <div _ngcontent-sdd-c80 class=form-floating>
                                <select _ngcontent-sdd-c80 id=codTipoPasivo
                                    placeholder="Seleccione el Tipo de Pasivo"
                                    formcontrolname=codTipoPasivo required
                                    class="form-select form-select-sm">
                                    <option _ngcontent-sdd-c80 value=1 selected>Deudas</option>
                                </select>
                                <label _ngcontent-sdd-c80 for=tb>Tipo de Pasivo</label>
                            </div>
                        </div>
                    </div>
                    <div _ngcontent-sdd-c80 class=col-sm-6>
                        <div _ngcontent-sdd-c80 class=form-group>
                            <div _ngcontent-sdd-c80 class=form-floating>
                                <select _ngcontent-sdd-c80 id=codTipoDeuda
                                    placeholder="Seleccione el Tipo de Deuda"
                                    formcontrolname=codTipoDeuda required
                                    class="form-select form-select-sm">
                                    <option _ngcontent-sdd-c80 value=1 selected>Tarjeta de Crédito</option>
                                </select>
                                <label _ngcontent-sdd-c80 for=tb>Tipo de Deuda</label>
                            </div>
                        </div>
                    </div>
                </div>

                <br _ngcontent-sdd-c80>

                <!-- ═══ Row 2: Nombre Banco · Número de TDC ═══ -->
                <div _ngcontent-sdd-c80 class=row>
                    <div _ngcontent-sdd-c80 class=col-sm-6>
                        <div _ngcontent-sdd-c80 class=form-group>
                            <div _ngcontent-sdd-c80 class=form-floating>
                                <select _ngcontent-sdd-c80 id=vp formcontrolname=codBanco required
                                    class="form-select form-select-sm">
                                    <?php foreach ($bancos as $b): ?>
                                        <option _ngcontent-sdd-c80 value="<?= $b['banco_id'] ?>"><?= htmlspecialchars($b['nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <label _ngcontent-sdd-c80 for=vp>Nombre Banco</label>
                            </div>
                        </div>
                    </div>
                    <div _ngcontent-sdd-c80 class=col-sm-6>
                        <div _ngcontent-sdd-c80 class=form-group>
                            <div _ngcontent-sdd-c80 class=form-floating>
                                <input _ngcontent-sdd-c80 id=lind placeholder=# type=text
                                    formcontrolname=numeroTdc maxlength=30 required
                                    class="form-control form-control-sm">
                                <label _ngcontent-sdd-c80 for=lind>Número de TDC</label>
                            </div>
                            <div _ngcontent-sdd-c80 class="col-sm-6 text-danger"></div>
                        </div>
                    </div>
                </div>

                <br _ngcontent-sdd-c80>

                <!-- ═══ Row 3: Porcentaje · Descripción · Valor Declarado ═══ -->
                <div _ngcontent-sdd-c80 class=row>
                    <div _ngcontent-sdd-c80 class=col-sm-2>
                        <div _ngcontent-sdd-c80 class=form-group>
                            <div _ngcontent-sdd-c80 class="form-floating sm-4">
                                <input _ngcontent-sdd-c80 id=sporcentaje placeholder=# type=text
                                    formcontrolname=porcentaje currencymask maxlength=6 required
                                    class="decimal-input form-control form-control-sm text-end"
                                    style=text-align:right value=0,01>
                                <label _ngcontent-sdd-c80 for=ssc>Porcentaje %</label>
                            </div>
                        </div>
                    </div>
                    <div _ngcontent-sdd-c80 class=col-sm-6>
                        <div _ngcontent-sdd-c80 class=form-group>
                            <div _ngcontent-sdd-c80 class=form-floating>
                                <textarea _ngcontent-sdd-c80 id=sc placeholder=# formcontrolname=descripcion
                                    maxlength=4999 required
                                    class="form-control form-control-sm"></textarea>
                                <label _ngcontent-sdd-c80 for=sc>Descripción</label>
                            </div>
                            <div _ngcontent-sdd-c80 class="col-sm-6 text-danger"></div>
                        </div>
                    </div>
                    <div _ngcontent-sdd-c80 class=col-sm-4>
                        <div _ngcontent-sdd-c80 class=form-group>
                            <div _ngcontent-sdd-c80 class=form-floating>
                                <input _ngcontent-sdd-c80 id=ssc placeholder=# type=text
                                    formcontrolname=valorDeclarado currencymask required
                                    class="decimal-input form-control form-control-sm text-end"
                                    style=text-align:right value=0,00>
                                <label _ngcontent-sdd-c80 for=ssc>Valor Declarado (Bs.)</label>
                            </div>
                        </div>
                    </div>
                </div>

                <br _ngcontent-sdd-c80>
                <button _ngcontent-sdd-c80 type=submit class="btn btn-sm btn-danger" disabled>Guardar <i _ngcontent-sdd-c80 class=bi-save></i></button>
            </form>
        </div>
    </div>
    <br _ngcontent-sdd-c80>

    <!-- ═══ Table ═══ -->
    <div id="tablaContainerTdc" style="display:none">
        <table _ngcontent-sdd-c80 class="table table-bordered table-striped table-sm">
            <thead _ngcontent-sdd-c80>
                <tr _ngcontent-sdd-c80>
                    <th _ngcontent-sdd-c80 scope=col>Tipo de Pasivo</th>
                    <th _ngcontent-sdd-c80 scope=col>Tipo Deuda</th>
                    <th _ngcontent-sdd-c80 scope=col>Descripción</th>
                    <th _ngcontent-sdd-c80 scope=col>Valor Declarado (Bs.)</th>
                    <th _ngcontent-sdd-c80 scope=col>Acción</th>
                </tr>
            </thead>
            <tbody _ngcontent-sdd-c80 id="tbodyTdc"></tbody>
        </table>
    </div>
</div>

<script>
    var tdcItems = <?= json_encode($tdcGuardados, JSON_UNESCAPED_UNICODE) ?>;

    document.addEventListener('DOMContentLoaded', function () {
        const form = document.querySelector('form');
        const btn = form.querySelector('button[type=submit]');
        const tbody = document.getElementById('tbodyTdc');
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
            btn.disabled = !valid;
        }

        requiredFields.forEach(function (f) {
            f.addEventListener('input', validateForm);
            f.addEventListener('change', validateForm);
        });

        // ═══ Render table ═══
        function renderTable() {
            const container = document.getElementById('tablaContainerTdc');
            tbody.innerHTML = '';
            let totalDeclarado = 0;

            if (tdcItems.length === 0) {
                container.style.display = 'none';
            } else {
                container.style.display = '';
            }

            tdcItems.forEach(function (item, idx) {
                const vd = parseFloat((item.valor_declarado || '0').replace(/\./g, '').replace(',', '.'));
                totalDeclarado += vd;

                const desc = ` ${item.porcentaje || '0,01'}% de ${item.descripcion || ''}. Banco: ${item.nombre_banco || ''}, Número de Cuenta/Tarjeta: ${item.numero_tdc || ''}.`;

                const tr = document.createElement('tr');
                tr.setAttribute('_ngcontent-sdd-c80', '');
                tr.innerHTML = `
                <td _ngcontent-sdd-c80>${item.nombre_tipo_pasivo || 'Deudas'}</td>
                <td _ngcontent-sdd-c80>${item.nombre_tipo_deuda || 'Tarjeta de Crédito'}</td>
                <td _ngcontent-sdd-c80 class=lthgf><div _ngcontent-sdd-c80><textarea _ngcontent-sdd-c80 class=lthgtextarea readonly>${desc}</textarea></div></td>
                <td _ngcontent-sdd-c80 align=right>${item.valor_declarado || '0,00'}</td>
                <td _ngcontent-sdd-c80>
                    <div _ngcontent-sdd-c80 class=accionesicono>
                        <i _ngcontent-sdd-c80 class="bi bi-pencil-fill" onclick="editarTdc(${idx})" title="Modificar"></i>&nbsp;
                        <i _ngcontent-sdd-c80 class="bi-trash-fill" onclick="eliminarTdc(${idx})" title="Eliminar"></i>
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

        // ═══ Helper: get selected text from select ═══
        function getSelectText(id) {
            var sel = document.getElementById(id);
            return sel && sel.options[sel.selectedIndex] ? sel.options[sel.selectedIndex].text.trim() : '';
        }

        // ═══ Collect form data ═══
        function getFormData() {
            return {
                cod_tipo_pasivo: document.getElementById('codTipoPasivo').value,
                nombre_tipo_pasivo: getSelectText('codTipoPasivo'),
                cod_tipo_deuda: document.getElementById('codTipoDeuda').value,
                nombre_tipo_deuda: getSelectText('codTipoDeuda'),
                cod_banco: document.getElementById('vp').value,
                nombre_banco: getSelectText('vp'),
                numero_tdc: document.getElementById('lind').value,
                porcentaje: document.getElementById('sporcentaje').value,
                descripcion: document.getElementById('sc').value,
                valor_declarado: document.getElementById('ssc').value,
            };
        }

        // ═══ Reset form ═══
        function resetForm() {
            document.getElementById('codTipoPasivo').value = '1';
            document.getElementById('codTipoDeuda').value = '1';
            document.getElementById('vp').selectedIndex = 0;
            document.getElementById('lind').value = '';
            document.getElementById('sporcentaje').value = '0,01';
            document.getElementById('sc').value = '';
            document.getElementById('ssc').value = '0,00';
        }

        // ═══ Fill form for editing ═══
        function fillForm(item) {
            document.getElementById('codTipoPasivo').value = item.cod_tipo_pasivo || '1';
            document.getElementById('codTipoDeuda').value = item.cod_tipo_deuda || '1';
            document.getElementById('vp').value = item.cod_banco || '';
            document.getElementById('lind').value = item.numero_tdc || '';
            document.getElementById('sporcentaje').value = item.porcentaje || '0,01';
            document.getElementById('sc').value = item.descripcion || '';
            document.getElementById('ssc').value = item.valor_declarado || '0,00';
        }

        // ═══ CRUD Manager (global) ═══
        initCrudManager({
            intentoId:    INTENTO_ID,
            baseUrl:      BASE,
            apiSlug:      'tarjetas_credito',
            items:        tdcItems,
            getFormData:  getFormData,
            resetForm:    resetForm,
            renderTable:  renderTable,
            fillForm:     fillForm,
            validateForm: validateForm,
            editName:     'editarTdc',
            deleteName:   'eliminarTdc'
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
