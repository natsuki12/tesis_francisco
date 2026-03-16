<?php
$activeMenu = 'pasivosGastos';
$activeItem = 'Pasivos Gastos';
$breadcrumbs = [
    ['label' => 'Inicio', 'url' => '/simulador/servicios_declaracion/dashboard'],
    ['label' => 'Pasivos'],
    ['label' => 'Gastos'],
];

/* ── Data from controller ── */
$intento = $intento ?? null;
$intentoId = $intento['id'] ?? 0;

$borrador = [];
if ($intento && !empty($intento['borrador_json'])) {
    $borrador = json_decode($intento['borrador_json'], true) ?: [];
}
$gastosGuardados = $borrador['pasivos_gastos'] ?? [];

ob_start();
?>

<div _ngcontent-sdd-c79 class="shadow-lg p-3 mb-5 bg-body rounded lenletra">
    <div _ngcontent-sdd-c79 class=card>
        <div _ngcontent-sdd-c79 class=card-header>Gastos</div>
        <div _ngcontent-sdd-c79 class=card-body>
            <form _ngcontent-sdd-c79 novalidate>

                <!-- ═══ Row 1: Tipo de Pasivo · Tipo de Gastos ═══ -->
                <div _ngcontent-sdd-c79 class=row>
                    <div _ngcontent-sdd-c79 class=col-sm-6>
                        <div _ngcontent-sdd-c79 class=form-group>
                            <div _ngcontent-sdd-c79 class=form-floating>
                                <select _ngcontent-sdd-c79 id=codTipoPasivo
                                    placeholder="Seleccione el Tipo de Pasivo"
                                    formcontrolname=codTipoPasivo required
                                    class="form-select form-select-sm">
                                    <option _ngcontent-sdd-c79 value=2 selected>Gastos</option>
                                </select>
                                <label _ngcontent-sdd-c79 for=tb>Tipo de Pasivo</label>
                            </div>
                        </div>
                    </div>
                    <div _ngcontent-sdd-c79 class=col-sm-6>
                        <div _ngcontent-sdd-c79 class=form-group>
                            <div _ngcontent-sdd-c79 class=form-floating>
                                <select _ngcontent-sdd-c79 id=codTipoGasto
                                    placeholder="Seleccione el Tipo de Deuda"
                                    formcontrolname=codTipoDeuda required
                                    class="form-select form-control-sm">
                                    <?php foreach ($tiposGasto as $tg): ?>
                                        <option _ngcontent-sdd-c79 value="<?= $tg['tipo_pasivo_gasto_id'] ?>"><?= htmlspecialchars($tg['nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <label _ngcontent-sdd-c79 for=tb>Tipo de Gastos</label>
                            </div>
                        </div>
                    </div>
                </div>

                <br _ngcontent-sdd-c79>

                <!-- ═══ Row 2: Porcentaje · Descripción · Valor Declarado ═══ -->
                <div _ngcontent-sdd-c79 class=row>
                    <div _ngcontent-sdd-c79 class=col-sm-2>
                        <div _ngcontent-sdd-c79 class=form-group>
                            <div _ngcontent-sdd-c79 class="form-floating sm-4">
                                <input _ngcontent-sdd-c79 id=sporcentaje placeholder=# type=text
                                    formcontrolname=porcentaje currencymask maxlength=6 required
                                    class="form-control form-control-sm text-end"
                                    style=text-align:right value="0,01">
                                <label _ngcontent-sdd-c79 for=ssc>Porcentaje %</label>
                            </div>
                        </div>
                    </div>
                    <div _ngcontent-sdd-c79 class=col-sm-6>
                        <div _ngcontent-sdd-c79 class=form-group>
                            <div _ngcontent-sdd-c79 class=form-floating>
                                <textarea _ngcontent-sdd-c79 id=sc placeholder=#
                                    formcontrolname=descripcion maxlength=4999 required
                                    class="form-control form-control-sm"></textarea>
                                <label _ngcontent-sdd-c79 for=sc>Descripción</label>
                            </div>
                            <div _ngcontent-sdd-c79 class="col-sm-6 text-danger"></div>
                        </div>
                    </div>
                    <div _ngcontent-sdd-c79 class=col-sm-4>
                        <div _ngcontent-sdd-c79 class=form-group>
                            <div _ngcontent-sdd-c79 class=form-floating>
                                <input _ngcontent-sdd-c79 id=ssc placeholder=# type=text
                                    formcontrolname=valorDeclarado currencymask required
                                    class="form-control form-control-sm text-end"
                                    style=text-align:right value="0,00">
                                <label _ngcontent-sdd-c79 for=ssc>Valor Declarado (Bs.)</label>
                            </div>
                        </div>
                    </div>
                </div>

                <br _ngcontent-sdd-c79>
                <button _ngcontent-sdd-c79 type=submit class="btn btn-sm btn-danger" disabled>Guardar <i _ngcontent-sdd-c79 class=bi-save></i></button>
            </form>
        </div>
    </div>
    <br _ngcontent-sdd-c79>

    <!-- ═══ Table ═══ -->
    <div id="tablaContainerGastos" style="display:none">
        <table _ngcontent-sdd-c79 class="table table-bordered table-striped table-sm">
            <thead _ngcontent-sdd-c79>
                <tr _ngcontent-sdd-c79>
                    <th _ngcontent-sdd-c79 scope=col>Tipo de Pasivo</th>
                    <th _ngcontent-sdd-c79 scope=col>Tipo de Gasto</th>
                    <th _ngcontent-sdd-c79 scope=col>Descripción</th>
                    <th _ngcontent-sdd-c79 scope=col>Valor Declarado (Bs.)</th>
                    <th _ngcontent-sdd-c79 scope=col>Acción</th>
                </tr>
            </thead>
            <tbody _ngcontent-sdd-c79 id="tbodyGastos"></tbody>
        </table>
    </div>
</div>

<script>
    const INTENTO_ID = <?= json_encode($intentoId) ?>;
    const BASE = <?= json_encode(rtrim(($_ENV['APP_BASE'] ?? getenv('APP_BASE')) ?: '', '/')) ?>;
    let gastosItems = <?= json_encode($gastosGuardados, JSON_UNESCAPED_UNICODE) ?>;
    let editIndex = null;

    document.addEventListener('DOMContentLoaded', function () {
        const form = document.querySelector('form');
        const btn = form.querySelector('button[type=submit]');
        const tbody = document.getElementById('tbodyGastos');
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
            const container = document.getElementById('tablaContainerGastos');
            tbody.innerHTML = '';
            let totalDeclarado = 0;

            if (gastosItems.length === 0) {
                container.style.display = 'none';
            } else {
                container.style.display = '';
            }

            gastosItems.forEach(function (item, idx) {
                const vd = parseFloat((item.valor_declarado || '0').replace(/\./g, '').replace(',', '.'));
                totalDeclarado += vd;

                const desc = ` ${item.porcentaje || '0,01'}% de ${item.descripcion || ''}.`;

                const tr = document.createElement('tr');
                tr.setAttribute('_ngcontent-sdd-c79', '');
                tr.innerHTML = `
                <td _ngcontent-sdd-c79>${item.nombre_tipo_pasivo || 'Gastos'}</td>
                <td _ngcontent-sdd-c79>${item.nombre_tipo_gasto || 'Apertura de Testamento'}</td>
                <td _ngcontent-sdd-c79 class=lthgf><div _ngcontent-sdd-c79 style=width:auto>${desc}</div></td>
                <td _ngcontent-sdd-c79 align=right>${item.valor_declarado || '0,00'}</td>
                <td _ngcontent-sdd-c79>
                    <div _ngcontent-sdd-c79 class=accionesicono>
                        <i _ngcontent-sdd-c79 class="bi bi-pencil-fill" onclick="editarGasto(${idx})" title="Modificar"></i>&nbsp;
                        <i _ngcontent-sdd-c79 class="bi-trash-fill" onclick="eliminarGasto(${idx})" title="Eliminar"></i>
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
                cod_tipo_gasto: document.getElementById('codTipoGasto').value,
                nombre_tipo_gasto: getSelectText('codTipoGasto'),
                porcentaje: document.getElementById('sporcentaje').value,
                descripcion: document.getElementById('sc').value,
                valor_declarado: document.getElementById('ssc').value,
            };
        }

        // ═══ Reset form ═══
        function resetForm() {
            document.getElementById('codTipoPasivo').value = '2';
            document.getElementById('codTipoGasto').value = '7';
            document.getElementById('sporcentaje').value = '0,01';
            document.getElementById('sc').value = '';
            document.getElementById('ssc').value = '0,00';
            editIndex = null;
            btn.textContent = 'Guardar ';
            const icon = document.createElement('i');
            icon.className = 'bi-save';
            btn.appendChild(icon);
            btn.disabled = true;
        }

        // ═══ Fill form for editing ═══
        window.editarGasto = function (idx) {
            const item = gastosItems[idx];
            if (!item) return;
            editIndex = idx;

            document.getElementById('codTipoPasivo').value = item.cod_tipo_pasivo || '2';
            document.getElementById('codTipoGasto').value = item.cod_tipo_gasto || '7';
            document.getElementById('sporcentaje').value = item.porcentaje || '0,01';
            document.getElementById('sc').value = item.descripcion || '';
            document.getElementById('ssc').value = item.valor_declarado || '0,00';

            btn.textContent = 'Actualizar ';
            const icon = document.createElement('i');
            icon.className = 'bi-save';
            btn.appendChild(icon);

            validateForm();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        };

        // ═══ Delete ═══
        window.eliminarGasto = function (idx) {
            if (!confirm('¿Está seguro de eliminar este registro?')) return;
            if (!INTENTO_ID) { alert('No hay intento activo'); return; }

            fetch(BASE + '/api/pasivos_gastos/' + INTENTO_ID + '/eliminar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ index: idx })
            })
                .then(r => r.json())
                .then(data => {
                    if (data.ok) {
                        gastosItems.splice(idx, 1);
                        renderTable();
                    } else {
                        alert(data.error || 'Error al eliminar');
                    }
                })
                .catch(() => alert('Error de conexión'));
        };

        // ═══ Submit (add/edit) ═══
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            if (!INTENTO_ID) { alert('No hay intento activo'); return; }

            const formData = getFormData();
            const isEdit = editIndex !== null;
            const url = isEdit
                ? BASE + '/api/pasivos_gastos/' + INTENTO_ID + '/editar'
                : BASE + '/api/pasivos_gastos/' + INTENTO_ID + '/agregar';

            if (isEdit) formData.index = editIndex;

            fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            })
                .then(r => r.json())
                .then(data => {
                    if (data.ok) {
                        if (isEdit) {
                            gastosItems[editIndex] = formData;
                        } else {
                            gastosItems.push(formData);
                        }
                        renderTable();
                        resetForm();
                    } else {
                        alert(data.error || 'Error al guardar');
                    }
                })
                .catch(() => alert('Error de conexión'));
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
