<?php
$activeMenu = 'exoneraciones';
$activeItem = 'Exoneraciones';
$breadcrumbs = [
    ['label' => 'Inicio', 'url' => '/simulador/servicios_declaracion/dashboard'],
    ['label' => 'Exoneraciones'],
];

/* ── Data from controller ── */
$intento = $intento ?? null;
$intentoId = $intento['id'] ?? 0;

$borrador = [];
if ($intento && !empty($intento['borrador_json'])) {
    $borrador = json_decode($intento['borrador_json'], true) ?: [];
}
$exoneracionesGuardadas = $borrador['exoneraciones'] ?? [];

ob_start();
?>

<div _ngcontent-sdd-c76 class="shadow-lg p-3 mb-5 bg-body rounded lenletra">
    <div _ngcontent-sdd-c76 class=card>
        <div _ngcontent-sdd-c76 class=card-header>Exoneraciones</div>
        <div _ngcontent-sdd-c76 class=card-body>
            <form _ngcontent-sdd-c76 novalidate>

                <!-- ═══ Row 1: Tipo · Descripción ═══ -->
                <div _ngcontent-sdd-c76 class=row>
                    <div _ngcontent-sdd-c76 class=col-sm-6>
                        <div _ngcontent-sdd-c76 class=form-group>
                            <div _ngcontent-sdd-c76 class=form-floating>
                                <input _ngcontent-sdd-c76 id=exoTipo placeholder=# type=text formcontrolname=tipo
                                    maxlength=50 required class="form-control form-control-sm" value>
                                <label _ngcontent-sdd-c76 for=exoTipo>Tipo</label>
                            </div>
                            <div _ngcontent-sdd-c76 class="col-sm-6 text-danger"></div>
                        </div>
                    </div>
                    <div _ngcontent-sdd-c76 class=col-sm-6>
                        <div _ngcontent-sdd-c76 class=form-group>
                            <div _ngcontent-sdd-c76 class=form-floating>
                                <textarea _ngcontent-sdd-c76 id=exoDescripcion placeholder=# formcontrolname=descripcion
                                    maxlength=4999 required class="form-control form-control-sm"></textarea>
                                <label _ngcontent-sdd-c76 for=exoDescripcion>Descripción</label>
                            </div>
                            <div _ngcontent-sdd-c76 class="col-sm-6 text-danger"></div>
                        </div>
                    </div>
                </div>

                <br _ngcontent-sdd-c76>

                <!-- ═══ Row 2: Valor Declarado ═══ -->
                <div _ngcontent-sdd-c76 class=row>
                    <div _ngcontent-sdd-c76 class=col-sm-6>
                        <div _ngcontent-sdd-c76 class=form-group>
                            <div _ngcontent-sdd-c76 class=form-floating>
                                <input _ngcontent-sdd-c76 id=exoValorDeclarado placeholder=# type=text
                                    formcontrolname=valorDeclarado currencymask required
                                    class="form-control form-control-sm text-end" style=text-align:right value="0,00">
                                <label _ngcontent-sdd-c76 for=exoValorDeclarado>Valor Declarado (Bs.)</label>
                            </div>
                        </div>
                    </div>
                </div>

                <br _ngcontent-sdd-c76>
                <button _ngcontent-sdd-c76 type=submit class="btn btn-sm btn-danger" disabled>Guardar <i
                        _ngcontent-sdd-c76 class=bi-save></i></button>
            </form>
        </div>
    </div>
    <br _ngcontent-sdd-c76>

    <!-- ═══ Table ═══ -->
    <div id="tablaContainerExoneraciones" style="display:none">
        <table _ngcontent-sdd-c76 class="table table-bordered table-striped table-sm">
            <thead _ngcontent-sdd-c76>
                <tr _ngcontent-sdd-c76>
                    <th _ngcontent-sdd-c76 scope=col>Tipo</th>
                    <th _ngcontent-sdd-c76 scope=col>Descripción</th>
                    <th _ngcontent-sdd-c76 scope=col>Valor Declarado (Bs.)</th>
                    <th _ngcontent-sdd-c76 scope=col>Acción</th>
                </tr>
            </thead>
            <tbody _ngcontent-sdd-c76 id="tbodyExoneraciones"></tbody>
        </table>
    </div>
</div>

<script>
    const INTENTO_ID = <?= json_encode($intentoId) ?>;
    const BASE = <?= json_encode(rtrim(($_ENV['APP_BASE'] ?? getenv('APP_BASE')) ?: '', '/')) ?>;
    let exoneracionesItems = <?= json_encode($exoneracionesGuardadas, JSON_UNESCAPED_UNICODE) ?>;
    let editIndex = null;

    document.addEventListener('DOMContentLoaded', function () {
        const form = document.querySelector('form');
        const btn = form.querySelector('button[type=submit]');
        const tbody = document.getElementById('tbodyExoneraciones');
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
            var container = document.getElementById('tablaContainerExoneraciones');
            tbody.innerHTML = '';
            var totalDeclarado = 0;

            if (exoneracionesItems.length === 0) {
                container.style.display = 'none';
            } else {
                container.style.display = '';
            }

            exoneracionesItems.forEach(function (item, idx) {
                var vd = parseFloat((item.valor_declarado || '0').replace(/\./g, '').replace(',', '.'));
                totalDeclarado += vd;

                var tr = document.createElement('tr');
                tr.setAttribute('_ngcontent-sdd-c76', '');
                tr.innerHTML =
                    '<td _ngcontent-sdd-c76>' + (item.tipo || '') + '</td>' +
                    '<td _ngcontent-sdd-c76>' + (item.descripcion || '') + '</td>' +
                    '<td _ngcontent-sdd-c76 align=right>' + (item.valor_declarado || '0,00') + '</td>' +
                    '<td _ngcontent-sdd-c76>' +
                    '<div _ngcontent-sdd-c76 class=accionesicono>' +
                    '<i _ngcontent-sdd-c76 class="bi bi-pencil-fill" onclick="editarExoneracion(' + idx + ')" title="Modificar"></i>&nbsp;' +
                    '<i _ngcontent-sdd-c76 class="bi-trash-fill" onclick="eliminarExoneracion(' + idx + ')" title="Eliminar"></i>' +
                    '</div>' +
                    '</td>';
                tbody.appendChild(tr);
            });

            // Total row as last tr in tbody
            var trTotal = document.createElement('tr');
            trTotal.innerHTML =
                '<td></td>' +
                '<td>Total:</td>' +
                '<td align=right> ' + totalDeclarado.toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '</td>' +
                '<td></td>';
            tbody.appendChild(trTotal);
        }

        // ═══ Collect form data ═══
        function getFormData() {
            return {
                tipo: document.getElementById('exoTipo').value,
                descripcion: document.getElementById('exoDescripcion').value,
                valor_declarado: document.getElementById('exoValorDeclarado').value,
            };
        }

        // ═══ Reset form ═══
        function resetForm() {
            document.getElementById('exoTipo').value = '';
            document.getElementById('exoDescripcion').value = '';
            document.getElementById('exoValorDeclarado').value = '0,00';
            editIndex = null;
            btn.textContent = 'Guardar ';
            var icon = document.createElement('i');
            icon.className = 'bi-save';
            btn.appendChild(icon);
            btn.disabled = true;
        }

        // ═══ Fill form for editing ═══
        window.editarExoneracion = function (idx) {
            var item = exoneracionesItems[idx];
            if (!item) return;
            editIndex = idx;

            document.getElementById('exoTipo').value = item.tipo || '';
            document.getElementById('exoDescripcion').value = item.descripcion || '';
            document.getElementById('exoValorDeclarado').value = item.valor_declarado || '0,00';

            btn.textContent = 'Actualizar ';
            var icon = document.createElement('i');
            icon.className = 'bi-save';
            btn.appendChild(icon);

            validateForm();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        };

        // ═══ Delete ═══
        window.eliminarExoneracion = function (idx) {
            if (!confirm('¿Está seguro de eliminar este registro?')) return;
            if (!INTENTO_ID) { alert('No hay intento activo'); return; }

            fetch(BASE + '/api/exoneraciones/' + INTENTO_ID + '/eliminar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ index: idx })
            })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.ok) {
                        exoneracionesItems.splice(idx, 1);
                        renderTable();
                    } else {
                        alert(data.error || 'Error al eliminar');
                    }
                })
                .catch(function () { alert('Error de conexión'); });
        };

        // ═══ Submit (add/edit) ═══
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            if (!INTENTO_ID) { alert('No hay intento activo'); return; }

            var formData = getFormData();
            var isEdit = editIndex !== null;
            var url = isEdit
                ? BASE + '/api/exoneraciones/' + INTENTO_ID + '/editar'
                : BASE + '/api/exoneraciones/' + INTENTO_ID + '/agregar';

            if (isEdit) formData.index = editIndex;

            fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.ok) {
                        if (isEdit) {
                            exoneracionesItems[editIndex] = formData;
                        } else {
                            exoneracionesItems.push(formData);
                        }
                        renderTable();
                        resetForm();
                    } else {
                        alert(data.error || 'Error al guardar');
                    }
                })
                .catch(function () { alert('Error de conexión'); });
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