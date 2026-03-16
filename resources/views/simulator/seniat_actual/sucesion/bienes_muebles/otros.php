<?php
$activeMenu = 'muebles';
$activeItem = 'Otros';
$breadcrumbs = [
    ['label' => 'Inicio', 'url' => '/simulador/servicios_declaracion/dashboard'],
    ['label' => 'Bienes Muebles'],
    ['label' => 'Otros'],
];

/* ── Data from controller ── */
$intento = $intento ?? null;
$intentoId = $intento['id'] ?? 0;

$borrador = [];
if ($intento && !empty($intento['borrador_json'])) {
    $borrador = json_decode($intento['borrador_json'], true) ?: [];
}
$otrosGuardados = $borrador['bienes_muebles_otros'] ?? [];

ob_start();
?>

<div _ngcontent-sdd-c98 class="shadow-lg p-3 mb-5 bg-body rounded lenletra">
    <div _ngcontent-sdd-c98 class=card>
        <div _ngcontent-sdd-c98 class=card-header>Otros</div>
        <div _ngcontent-sdd-c98 class=card-body>
            <form _ngcontent-sdd-c98 novalidate>

                <!-- ═══ Row 1: Tipo de Bien · Bien Litigioso ═══ -->
                <div _ngcontent-sdd-c98 class=row>
                    <div _ngcontent-sdd-c98 class=col-sm-6>
                        <div _ngcontent-sdd-c98 class=form-group>
                            <div _ngcontent-sdd-c98 class=form-floating>
                                <select _ngcontent-sdd-c98 id=codTipoBien
                                    placeholder="Seleccione el Tipo de Bien"
                                    formcontrolname=codTipoBien required
                                    class="form-select form-select-sm">
                                    <option _ngcontent-sdd-c98 value=19 selected>Otros Especifique</option>
                                </select>
                                <label _ngcontent-sdd-c98 for=tb>Tipo de Bien</label>
                            </div>
                        </div>
                    </div>
                    <div _ngcontent-sdd-c98 class=col-sm-6>
                        <div _ngcontent-sdd-c98 class=form-group>
                            <div _ngcontent-sdd-c98 class=form-floating>
                                <select _ngcontent-sdd-c98 id=bl formcontrolname=indicadorBienLigitioso required
                                    class="form-select">
                                    <option _ngcontent-sdd-c98 value=true>Si</option>
                                    <option _ngcontent-sdd-c98 value=false selected>No</option>
                                </select>
                                <label _ngcontent-sdd-c98 for=bl>Bien Litigioso</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ═══ Datos del Tribunal (partial) ═══ -->
                <?php include __DIR__ . '/_datos_tribunal.php'; ?>

                <!-- ═══ Row 2: Porcentaje · Descripción ═══ -->
                <div _ngcontent-sdd-c98 class="row py-3">
                    <div _ngcontent-sdd-c98 class=col-sm-2>
                        <div _ngcontent-sdd-c98 class=form-group>
                            <div _ngcontent-sdd-c98 class="form-floating sm-4">
                                <input _ngcontent-sdd-c98 id=sporcentaje placeholder=# type=text
                                    formcontrolname=porcentaje currencymask maxlength=6 required
                                    class="form-control form-control-sm text-end"
                                    style=text-align:right value=0,01>
                                <label _ngcontent-sdd-c98 for=ssc>Porcentaje %</label>
                            </div>
                        </div>
                    </div>
                    <div _ngcontent-sdd-c98 class=col-sm-10>
                        <div _ngcontent-sdd-c98 class=form-group>
                            <div _ngcontent-sdd-c98 class=form-floating>
                                <textarea _ngcontent-sdd-c98 id=sc placeholder=# formcontrolname=descripcion
                                    maxlength=4999 required
                                    class="form-control form-control-sm"></textarea>
                                <label _ngcontent-sdd-c98 for=sc>Descripción</label>
                            </div>
                            <div _ngcontent-sdd-c98 class="col-sm-6 text-danger"></div>
                        </div>
                    </div>
                </div>

                <br _ngcontent-sdd-c98>

                <!-- ═══ Row 3: Valor Declarado ═══ -->
                <div _ngcontent-sdd-c98 class=row>
                    <div _ngcontent-sdd-c98 class=col-sm-6>&nbsp;</div>
                    <div _ngcontent-sdd-c98 class=col-sm-6>
                        <div _ngcontent-sdd-c98 class=form-group>
                            <div _ngcontent-sdd-c98 class=form-floating>
                                <input _ngcontent-sdd-c98 id=ssc placeholder=# type=text
                                    formcontrolname=valorDeclarado currencymask required
                                    class="form-control form-control-sm text-end"
                                    style=text-align:right value=0,00>
                                <label _ngcontent-sdd-c98 for=ssc>Valor Declarado (Bs.)</label>
                            </div>
                        </div>
                    </div>
                </div>

                <br _ngcontent-sdd-c98>
                <button _ngcontent-sdd-c98 type=submit class="btn btn-sm btn-danger" disabled>Guardar <i _ngcontent-sdd-c98 class=bi-save></i></button>
            </form>
        </div>
    </div>
    <br _ngcontent-sdd-c98>

    <!-- ═══ Table ═══ -->
    <div id="tablaContainerOtros" style="display:none">
        <table _ngcontent-sdd-c98 class="table table-bordered table-striped table-sm">
            <thead _ngcontent-sdd-c98>
                <tr _ngcontent-sdd-c98>
                    <th _ngcontent-sdd-c98 scope=col>Tipo de Mueble</th>
                    <th _ngcontent-sdd-c98 scope=col>Bien Litigioso</th>
                    <th _ngcontent-sdd-c98 scope=col>Descripción</th>
                    <th _ngcontent-sdd-c98 scope=col>Valor Declarado (Bs.)</th>
                    <th _ngcontent-sdd-c98 scope=col>Acción</th>
                </tr>
            </thead>
            <tbody _ngcontent-sdd-c98 id="tbodyOtros"></tbody>
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
                tr.setAttribute('_ngcontent-sdd-c98', '');
                tr.innerHTML = `
                <td _ngcontent-sdd-c98>${item.nombre_tipo_bien || 'Otros Especifique'}</td>
                <td _ngcontent-sdd-c98>${item.bien_litigioso === 'true' ? 'Si' : 'No'}</td>
                <td _ngcontent-sdd-c98 class=lthgf><div _ngcontent-sdd-c98 style=width:auto> ${desc}</div></td>
                <td _ngcontent-sdd-c98 align=right>${item.valor_declarado || '0,00'}</td>
                <td _ngcontent-sdd-c98>
                    <div _ngcontent-sdd-c98 class=accionesicono>
                        <i _ngcontent-sdd-c98 class="bi bi-pencil-fill" onclick="editarOtro(${idx})" title="Modificar"></i>&nbsp;
                        <i _ngcontent-sdd-c98 class="bi-trash-fill" onclick="eliminarOtro(${idx})" title="Eliminar"></i>
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
            var tipoBienSel = document.getElementById('codTipoBien');
            var data = {
                cod_tipo_bien: tipoBienSel.value,
                nombre_tipo_bien: tipoBienSel.options[tipoBienSel.selectedIndex]?.text?.trim() || 'Otros Especifique',
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
            document.getElementById('codTipoBien').value = '19';
            document.getElementById('bl').value = 'false';
            resetTribunal();
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

            document.getElementById('codTipoBien').value = item.cod_tipo_bien || '19';
            document.getElementById('bl').value = item.bien_litigioso || 'false';
            setTribunalData(item);
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

            fetch(BASE + '/api/otros/' + INTENTO_ID + '/eliminar', {
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
                ? BASE + '/api/otros/' + INTENTO_ID + '/editar'
                : BASE + '/api/otros/' + INTENTO_ID + '/agregar';

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
