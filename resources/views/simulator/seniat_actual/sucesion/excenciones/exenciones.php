<?php
$activeMenu = 'exenciones';
$activeItem = 'Exenciones';
$breadcrumbs = [
    ['label' => 'Inicio', 'url' => '/simulador/servicios_declaracion/dashboard'],
    ['label' => 'Exenciones'],
];

/* ── Data from controller ── */
$intento = $intento ?? null;
$intentoId = $intento['id'] ?? 0;

$borrador = [];
if ($intento && !empty($intento['borrador_json'])) {
    $borrador = json_decode($intento['borrador_json'], true) ?: [];
}
$exencionesGuardadas = $borrador['exenciones'] ?? [];

ob_start();
?>

<div _ngcontent-sdd-c77 class="shadow-lg p-3 mb-5 bg-body rounded lenletra">
    <div _ngcontent-sdd-c77 class=card>
        <div _ngcontent-sdd-c77 class=card-header>Exenciones</div>
        <div _ngcontent-sdd-c77 class=card-body>
            <form _ngcontent-sdd-c77 novalidate>

                <!-- ═══ Row 1: Tipo · Descripción ═══ -->
                <div _ngcontent-sdd-c77 class=row>
                    <div _ngcontent-sdd-c77 class=col-sm-6>
                        <div _ngcontent-sdd-c77 class=form-group>
                            <div _ngcontent-sdd-c77 class=form-floating>
                                <input _ngcontent-sdd-c77 id=exTipo placeholder=# type=text formcontrolname=tipo
                                    maxlength=50 required class="form-control form-control-sm" value>
                                <label _ngcontent-sdd-c77 for=exTipo>Tipo</label>
                            </div>
                            <div _ngcontent-sdd-c77 class="col-sm-6 text-danger"></div>
                        </div>
                    </div>
                    <div _ngcontent-sdd-c77 class=col-sm-6>
                        <div _ngcontent-sdd-c77 class=form-group>
                            <div _ngcontent-sdd-c77 class=form-floating>
                                <textarea _ngcontent-sdd-c77 id=exDescripcion placeholder=# formcontrolname=descripcion
                                    maxlength=4999 required class="form-control form-control-sm"></textarea>
                                <label _ngcontent-sdd-c77 for=exDescripcion>Descripción</label>
                            </div>
                            <div _ngcontent-sdd-c77 class="col-sm-6 text-danger"></div>
                        </div>
                    </div>
                </div>

                <br _ngcontent-sdd-c77>

                <!-- ═══ Row 2: Valor Declarado ═══ -->
                <div _ngcontent-sdd-c77 class=row>
                    <div _ngcontent-sdd-c77 class=col-sm-6>
                        <div _ngcontent-sdd-c77 class=form-group>
                            <div _ngcontent-sdd-c77 class=form-floating>
                                <input _ngcontent-sdd-c77 id=exValorDeclarado placeholder=# type=text
                                    formcontrolname=valorDeclarado currencymask required
                                    class="decimal-input form-control form-control-sm text-end" style=text-align:right value="0,00">
                                <label _ngcontent-sdd-c77 for=exValorDeclarado>Valor Declarado (Bs.)</label>
                            </div>
                        </div>
                    </div>
                </div>

                <br _ngcontent-sdd-c77>
                <button _ngcontent-sdd-c77 type=submit class="btn btn-sm btn-danger" disabled>Guardar <i
                        _ngcontent-sdd-c77 class=bi-save></i></button>
            </form>
        </div>
    </div>
    <br _ngcontent-sdd-c77>

    <!-- ═══ Table ═══ -->
    <div id="tablaContainerExenciones" style="display:none">
        <table _ngcontent-sdd-c77 class="table table-bordered table-striped table-sm">
            <thead _ngcontent-sdd-c77>
                <tr _ngcontent-sdd-c77>
                    <th _ngcontent-sdd-c77 scope=col>Tipo</th>
                    <th _ngcontent-sdd-c77 scope=col>Descripción</th>
                    <th _ngcontent-sdd-c77 scope=col>Valor Declarado (Bs.)</th>
                    <th _ngcontent-sdd-c77 scope=col>Acción</th>
                </tr>
            </thead>
            <tbody _ngcontent-sdd-c77 id="tbodyExenciones"></tbody>
        </table>
    </div>
</div>

<script>
    var exencionesItems = <?= json_encode($exencionesGuardadas, JSON_UNESCAPED_UNICODE) ?>;

    document.addEventListener('DOMContentLoaded', function () {
        const form = document.querySelector('form');
        const btn = form.querySelector('button[type=submit]');
        const tbody = document.getElementById('tbodyExenciones');
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
            var container = document.getElementById('tablaContainerExenciones');
            tbody.innerHTML = '';
            var totalDeclarado = 0;

            if (exencionesItems.length === 0) {
                container.style.display = 'none';
            } else {
                container.style.display = '';
            }

            exencionesItems.forEach(function (item, idx) {
                var vd = parseFloat((item.valor_declarado || '0').replace(/\./g, '').replace(',', '.'));
                totalDeclarado += vd;

                var tr = document.createElement('tr');
                tr.setAttribute('_ngcontent-sdd-c77', '');
                tr.innerHTML =
                    '<td _ngcontent-sdd-c77>' + (item.tipo || '') + '</td>' +
                    '<td _ngcontent-sdd-c77>' + (item.descripcion || '') + '</td>' +
                    '<td _ngcontent-sdd-c77 align=right>' + (item.valor_declarado || '0,00') + '</td>' +
                    '<td _ngcontent-sdd-c77>' +
                    '<div _ngcontent-sdd-c77 class=accionesicono>' +
                    '<i _ngcontent-sdd-c77 class="bi bi-pencil-fill" onclick="editarExencion(' + idx + ')" title="Modificar"></i>&nbsp;' +
                    '<i _ngcontent-sdd-c77 class="bi-trash-fill" onclick="eliminarExencion(' + idx + ')" title="Eliminar"></i>' +
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
                tipo: document.getElementById('exTipo').value,
                descripcion: document.getElementById('exDescripcion').value,
                valor_declarado: document.getElementById('exValorDeclarado').value,
            };
        }

        // ═══ Reset form ═══
        function resetForm() {
            document.getElementById('exTipo').value = '';
            document.getElementById('exDescripcion').value = '';
            document.getElementById('exValorDeclarado').value = '0,00';
        }

        // ═══ Fill form for editing ═══
        function fillForm(item) {
            document.getElementById('exTipo').value = item.tipo || '';
            document.getElementById('exDescripcion').value = item.descripcion || '';
            document.getElementById('exValorDeclarado').value = item.valor_declarado || '0,00';
        }

        // ═══ CRUD Manager (global) ═══
        initCrudManager({
            intentoId:    INTENTO_ID,
            baseUrl:      BASE,
            apiSlug:      'exenciones',
            items:        exencionesItems,
            getFormData:  getFormData,
            resetForm:    resetForm,
            renderTable:  renderTable,
            fillForm:     fillForm,
            validateForm: validateForm,
            editName:     'editarExencion',
            deleteName:   'eliminarExencion'
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