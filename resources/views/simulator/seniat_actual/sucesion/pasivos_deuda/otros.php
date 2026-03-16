<?php
$activeMenu = 'pasivosDeuda';
$activeItem = 'Otros';
$breadcrumbs = [
    ['label' => 'Inicio', 'url' => '/simulador/servicios_declaracion/dashboard'],
    ['label' => 'Pasivos'],
    ['label' => 'Otros'],
];

/* ── Data from controller ── */
$intento = $intento ?? null;
$intentoId = $intento['id'] ?? 0;

$borrador = [];
if ($intento && !empty($intento['borrador_json'])) {
    $borrador = json_decode($intento['borrador_json'], true) ?: [];
}
$otrosGuardados = $borrador['pasivos_deuda_otros'] ?? [];

ob_start();
?>

<div _ngcontent-pgi-c92 class="shadow-lg p-3 mb-5 bg-body rounded lenletra">
    <div _ngcontent-pgi-c92 class=card>
        <div _ngcontent-pgi-c92 class=card-header>Otros</div>
        <div _ngcontent-pgi-c92 class=card-body>
            <form _ngcontent-pgi-c92 novalidate>

                <!-- ═══ Row 1: Tipo de Pasivo · Tipo de Deuda ═══ -->
                <div _ngcontent-pgi-c92 class=row>
                    <div _ngcontent-pgi-c92 class=col-sm-6>
                        <div _ngcontent-pgi-c92 class=form-group>
                            <div _ngcontent-pgi-c92 class=form-floating>
                                <select _ngcontent-pgi-c92 id=codTipoPasivo
                                    placeholder="Seleccione el Tipo de Pasivo"
                                    formcontrolname=codTipoPasivo required
                                    class="form-select form-select-sm">
                                    <option _ngcontent-pgi-c92 value=1 selected>Deudas</option>
                                </select>
                                <label _ngcontent-pgi-c92 for=tb>Tipo de Pasivo</label>
                            </div>
                        </div>
                    </div>
                    <div _ngcontent-pgi-c92 class=col-sm-6>
                        <div _ngcontent-pgi-c92 class=form-group>
                            <div _ngcontent-pgi-c92 class=form-floating>
                                <select _ngcontent-pgi-c92 id=codTipoDeuda
                                    placeholder="Seleccione el Tipo de Deuda"
                                    formcontrolname=codTipoDeuda required
                                    class="form-select form-select-sm">
                                    <option _ngcontent-pgi-c92 value=4 selected>Otro Especifique</option>
                                </select>
                                <label _ngcontent-pgi-c92 for=tb>Tipo de Deuda</label>
                            </div>
                        </div>
                    </div>
                </div>

                <br _ngcontent-pgi-c92>

                <!-- ═══ Row 2: Porcentaje · Descripción · Valor Declarado ═══ -->
                <div _ngcontent-pgi-c92 class=row>
                    <div _ngcontent-pgi-c92 class=col-sm-2>
                        <div _ngcontent-pgi-c92 class=form-group>
                            <div _ngcontent-pgi-c92 class="form-floating sm-4">
                                <input _ngcontent-pgi-c92 id=sporcentaje placeholder=# type=text
                                    formcontrolname=porcentaje currencymask maxlength=6 required
                                    class="form-control form-control-sm text-end"
                                    style=text-align:right value="0,01">
                                <label _ngcontent-pgi-c92 for=ssc>Porcentaje %</label>
                            </div>
                        </div>
                    </div>
                    <div _ngcontent-pgi-c92 class=col-sm-6>
                        <div _ngcontent-pgi-c92 class=form-group>
                            <div _ngcontent-pgi-c92 class=form-floating>
                                <textarea _ngcontent-pgi-c92 id=sc placeholder=#
                                    formcontrolname=descripcion maxlength=4999 required
                                    class="form-control form-control-sm"></textarea>
                                <label _ngcontent-pgi-c92 for=sc>Descripción</label>
                            </div>
                        </div>
                    </div>
                    <div _ngcontent-pgi-c92 class=col-sm-4>
                        <div _ngcontent-pgi-c92 class=form-group>
                            <div _ngcontent-pgi-c92 class=form-floating>
                                <input _ngcontent-pgi-c92 id=ssc placeholder=# type=text
                                    formcontrolname=valorDeclarado currencymask required
                                    class="form-control form-control-sm text-end"
                                    style=text-align:right value="0,00">
                                <label _ngcontent-pgi-c92 for=ssc>Valor Declarado (Bs.)</label>
                            </div>
                        </div>
                    </div>
                </div>

                <br _ngcontent-pgi-c92>
                <button _ngcontent-pgi-c92 type=submit class="btn btn-sm btn-danger" disabled>Guardar <i _ngcontent-pgi-c92 class=bi-save></i></button>
            </form>
        </div>
    </div>
    <br _ngcontent-pgi-c92>

    <!-- ═══ Table ═══ -->
    <div id="tablaContainerOtros" style="display:none">
        <table _ngcontent-pgi-c92 class="table table-bordered table-striped table-sm">
            <thead _ngcontent-pgi-c92>
                <tr _ngcontent-pgi-c92>
                    <th _ngcontent-pgi-c92 scope=col>Tipo de Pasivo</th>
                    <th _ngcontent-pgi-c92 scope=col>Tipo Deuda</th>
                    <th _ngcontent-pgi-c92 scope=col>Descripción</th>
                    <th _ngcontent-pgi-c92 scope=col>Valor Declarado (Bs.)</th>
                    <th _ngcontent-pgi-c92 scope=col>Acción</th>
                </tr>
            </thead>
            <tbody _ngcontent-pgi-c92 id="tbodyOtros"></tbody>
        </table>
    </div>
</div>

<script>
    const INTENTO_ID = <?= json_encode($intentoId) ?>;
    const BASE = <?= json_encode(rtrim(($_ENV['APP_BASE'] ?? getenv('APP_BASE')) ?: '', '/')) ?>;
    let otrosItems = <?= json_encode($otrosGuardados, JSON_UNESCAPED_UNICODE) ?>;
    let editIndex = null;

    document.addEventListener('DOMContentLoaded', function () {
        const form = document.querySelector('form');
        const btn = form.querySelector('button[type=submit]');
        const tbody = document.getElementById('tbodyOtros');
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
            const container = document.getElementById('tablaContainerOtros');
            tbody.innerHTML = '';
            let totalDeclarado = 0;

            if (otrosItems.length === 0) {
                container.style.display = 'none';
            } else {
                container.style.display = '';
            }

            otrosItems.forEach(function (item, idx) {
                const vd = parseFloat((item.valor_declarado || '0').replace(/\./g, '').replace(',', '.'));
                totalDeclarado += vd;

                const desc = ` ${item.porcentaje || '0,01'}% de ${item.descripcion || ''}.`;

                const tr = document.createElement('tr');
                tr.setAttribute('_ngcontent-pgi-c92', '');
                tr.innerHTML = `
                <td _ngcontent-pgi-c92>${item.nombre_tipo_pasivo || 'Deudas'}</td>
                <td _ngcontent-pgi-c92>${item.nombre_tipo_deuda || 'Otro Especifique'}</td>
                <td _ngcontent-pgi-c92 class=lthgf><div _ngcontent-pgi-c92 style=width:auto>${desc}</div></td>
                <td _ngcontent-pgi-c92 align=right>${item.valor_declarado || '0,00'}</td>
                <td _ngcontent-pgi-c92>
                    <div _ngcontent-pgi-c92 class=accionesicono>
                        <i _ngcontent-pgi-c92 class="bi bi-pencil-fill" onclick="editarOtro(${idx})" title="Modificar"></i>&nbsp;
                        <i _ngcontent-pgi-c92 class="bi-trash-fill" onclick="eliminarOtro(${idx})" title="Eliminar"></i>
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
                porcentaje: document.getElementById('sporcentaje').value,
                descripcion: document.getElementById('sc').value,
                valor_declarado: document.getElementById('ssc').value,
            };
        }

        // ═══ Reset form ═══
        function resetForm() {
            document.getElementById('codTipoPasivo').value = '1';
            document.getElementById('codTipoDeuda').value = '4';
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
        window.editarOtro = function (idx) {
            const item = otrosItems[idx];
            if (!item) return;
            editIndex = idx;

            document.getElementById('codTipoPasivo').value = item.cod_tipo_pasivo || '1';
            document.getElementById('codTipoDeuda').value = item.cod_tipo_deuda || '4';
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
        window.eliminarOtro = function (idx) {
            if (!confirm('¿Está seguro de eliminar este registro?')) return;
            if (!INTENTO_ID) { alert('No hay intento activo'); return; }

            fetch(BASE + '/api/pasivos_otros/' + INTENTO_ID + '/eliminar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ index: idx })
            })
                .then(r => r.json())
                .then(data => {
                    if (data.ok) {
                        otrosItems.splice(idx, 1);
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
                ? BASE + '/api/pasivos_otros/' + INTENTO_ID + '/editar'
                : BASE + '/api/pasivos_otros/' + INTENTO_ID + '/agregar';

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
                            otrosItems[editIndex] = formData;
                        } else {
                            otrosItems.push(formData);
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
