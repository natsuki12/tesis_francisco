<?php
$activeMenu = 'muebles';
$activeItem = 'Prestaciones Sociales';
$breadcrumbs = [
    ['label' => 'Inicio', 'url' => '/simulador/servicios_declaracion/dashboard'],
    ['label' => 'Bienes Muebles'],
    ['label' => 'Prestaciones Sociales'],
];

/* ── Data from controller ── */
$intento = $intento ?? null;
$intentoId = $intento['id'] ?? 0;

$borrador = [];
if ($intento && !empty($intento['borrador_json'])) {
    $borrador = json_decode($intento['borrador_json'], true) ?: [];
}
$prestacionesGuardadas = $borrador['bienes_muebles_prestaciones_sociales'] ?? [];

ob_start();
?>

<div _ngcontent-vpb-c64 class="shadow-lg p-3 mb-5 bg-body rounded lenletra">
    <div _ngcontent-vpb-c64 class=card>
        <div _ngcontent-vpb-c64 class=card-header>Prestaciones Sociales</div>
        <div _ngcontent-vpb-c64 class=card-body>
            <form _ngcontent-vpb-c64 novalidate>

                <!-- ═══ Row 1: Posee Banco · Nombre Banco · Número de Cuenta ═══ -->
                <div _ngcontent-vpb-c64 class=row>
                    <div _ngcontent-vpb-c64 class=col-sm-2>
                        <div _ngcontent-vpb-c64 class=form-group>
                            <div _ngcontent-vpb-c64 class=form-floating>
                                <select _ngcontent-vpb-c64 id=poseeBanco formcontrolname=indicadorBanco required
                                    class="form-select form-select-sm">
                                    <option _ngcontent-vpb-c64 value=false selected>No</option>
                                    <option _ngcontent-vpb-c64 value=true>Si</option>
                                </select>
                                <label _ngcontent-vpb-c64 for=poseeBanco>Posee Banco</label>
                            </div>
                        </div>
                    </div>
                    <div _ngcontent-vpb-c64 class=col-sm-4>
                        <div _ngcontent-vpb-c64 class=form-group>
                            <div _ngcontent-vpb-c64 class=form-floating>
                                <select _ngcontent-vpb-c64 id=codBanco formcontrolname=codBanco required
                                    class="form-select form-select-sm" disabled>
                                    <option _ngcontent-vpb-c64 value="">-- Seleccione --</option>
                                    <?php foreach ($bancos as $b): ?>
                                        <option _ngcontent-vpb-c64 value="<?= $b['banco_id'] ?>"><?= htmlspecialchars($b['nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <label _ngcontent-vpb-c64 for=codBanco>Nombre Banco</label>
                            </div>
                        </div>
                    </div>
                    <div _ngcontent-vpb-c64 class=col-sm-6>
                        <div _ngcontent-vpb-c64 class=form-group>
                            <div _ngcontent-vpb-c64 class="form-floating sm-4">
                                <input _ngcontent-vpb-c64 id=numeroCuenta placeholder=# type=text
                                    formcontrolname=numeroCuenta maxlength=20 required
                                    class="form-control form-control-sm" disabled value="NO APLICA">
                                <label _ngcontent-vpb-c64 for=numeroCuenta>Número de Cuenta</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ═══ Row 2: Rif Empresa · Razón Social · Bien Litigioso ═══ -->
                <div _ngcontent-vpb-c64 class="row py-3">
                    <div _ngcontent-vpb-c64 class=col-sm-2>
                        <div _ngcontent-vpb-c64 class=form-group>
                            <div _ngcontent-vpb-c64 class="form-floating sm-3">
                                <input _ngcontent-vpb-c64 id=rifEmpresa placeholder=# type=text
                                    formcontrolname=rifEmpresa maxlength=10 required
                                    class="form-control form-control-sm" value="">
                                <label _ngcontent-vpb-c64 for=rifEmpresa>Rif Empresa</label>
                            </div>
                            <div _ngcontent-vpb-c64 id=errorRifEmpresa class="col-sm-4 text-danger" style="display:none"></div>
                        </div>
                    </div>
                    <div _ngcontent-vpb-c64 class=col-sm-6>
                        <div _ngcontent-vpb-c64 class=form-group>
                            <div _ngcontent-vpb-c64 class=form-floating>
                                <input _ngcontent-vpb-c64 id=razonSocial placeholder=# type=text
                                    formcontrolname=razonSocial required
                                    class="form-control form-control-sm" disabled value="">
                                <label _ngcontent-vpb-c64 for=razonSocial>Razón Social</label>
                            </div>
                        </div>
                    </div>
                    <div _ngcontent-vpb-c64 class=col-sm-4>
                        <div _ngcontent-vpb-c64 class=form-group>
                            <div _ngcontent-vpb-c64 class="form-floating form-floating-sm">
                                <select _ngcontent-vpb-c64 id=bl formcontrolname=indicadorBienLigitioso required
                                    class="form-select form-select-sm">
                                    <option _ngcontent-vpb-c64 value=true>Si</option>
                                    <option _ngcontent-vpb-c64 value=false selected>No</option>
                                </select>
                                <label _ngcontent-vpb-c64 for=bl>Bien Litigioso</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ═══ Datos del Tribunal (partial) ═══ -->
                <?php include __DIR__ . '/_datos_tribunal.php'; ?>

                <!-- ═══ Row 3: Porcentaje · Descripción ═══ -->
                <div _ngcontent-vpb-c64 class="row py-3">
                    <div _ngcontent-vpb-c64 class=col-sm-2>
                        <div _ngcontent-vpb-c64 class=form-group>
                            <div _ngcontent-vpb-c64 class="form-floating sm-4">
                                <input _ngcontent-vpb-c64 id=sporcentaje placeholder=# type=text
                                    formcontrolname=porcentaje currencymask maxlength=6 required
                                    class="form-control form-control-sm text-end"
                                    style=text-align:right value=0,01>
                                <label _ngcontent-vpb-c64 for=sporcentaje>Porcentaje %</label>
                            </div>
                        </div>
                    </div>
                    <div _ngcontent-vpb-c64 class=col-sm-10>
                        <div _ngcontent-vpb-c64 class=form-group>
                            <div _ngcontent-vpb-c64 class="form-floating sm-4">
                                <textarea _ngcontent-vpb-c64 id=sc placeholder=# formcontrolname=descripcion
                                    maxlength=4999 required
                                    class="form-control form-control-sm"></textarea>
                                <label _ngcontent-vpb-c64 for=sc>Descripción</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ═══ Row 4: Valor Declarado ═══ -->
                <div _ngcontent-vpb-c64 class=row>
                    <div _ngcontent-vpb-c64 class=col-sm-6>&nbsp;</div>
                    <div _ngcontent-vpb-c64 class=col-sm-6>
                        <div _ngcontent-vpb-c64 class=form-group>
                            <div _ngcontent-vpb-c64 class="form-floating sm-4">
                                <input _ngcontent-vpb-c64 id=ssc placeholder=# type=text
                                    formcontrolname=valorDeclarado currencymask required
                                    class="form-control form-control-sm text-end"
                                    style=text-align:right value=0,00>
                                <label _ngcontent-vpb-c64 for=ssc>Valor Declarado (Bs.)</label>
                            </div>
                        </div>
                    </div>
                </div>

                <button _ngcontent-vpb-c64 type=submit class="btn btn-sm btn-danger" disabled>Guardar <i _ngcontent-vpb-c64 class=bi-save></i></button>
            </form>
        </div>
    </div>
    <br _ngcontent-vpb-c64>

    <!-- ═══ Table ═══ -->
    <div id="tablaContainerPrestaciones" style="display:none">
        <table _ngcontent-vpb-c64 class="table table-bordered table-striped table-sm">
            <thead _ngcontent-vpb-c64>
                <tr _ngcontent-vpb-c64>
                    <th _ngcontent-vpb-c64 scope=col>Tipo Bien</th>
                    <th _ngcontent-vpb-c64 scope=col>Bien Litigioso</th>
                    <th _ngcontent-vpb-c64 scope=col>Descripción</th>
                    <th _ngcontent-vpb-c64 scope=col>Valor Declarado (Bs.)</th>
                    <th _ngcontent-vpb-c64 scope=col>Acción</th>
                </tr>
            </thead>
            <tbody _ngcontent-vpb-c64 id="tbodyPrestaciones"></tbody>
        </table>
    </div>
</div>

<script>
    const INTENTO_ID = <?= json_encode($intentoId) ?>;
    const BASE = <?= json_encode(rtrim(($_ENV['APP_BASE'] ?? getenv('APP_BASE')) ?: '', '/')) ?>;
    let prestaciones = <?= json_encode($prestacionesGuardadas, JSON_UNESCAPED_UNICODE) ?>;
    let editIndex = null;

    document.addEventListener('DOMContentLoaded', function () {
        const form = document.querySelector('form');
        const btn = form.querySelector('button[type=submit]');
        const tbody = document.getElementById('tbodyPrestaciones');
        if (!form || !btn) return;

        const requiredFields = form.querySelectorAll(
            'input[required]:not([disabled]), textarea[required], select[required]'
        );

        // ═══ Banco toggle ═══
        const poseeBancoSel = document.getElementById('poseeBanco');
        const codBancoSel   = document.getElementById('codBanco');
        const numeroCuentaIn = document.getElementById('numeroCuenta');

        function toggleBancoFields() {
            const posee = poseeBancoSel.value === 'true';
            codBancoSel.disabled = !posee;
            numeroCuentaIn.disabled = !posee;
            if (!posee) {
                codBancoSel.value = '';
                numeroCuentaIn.value = 'NO APLICA';
            } else {
                if (numeroCuentaIn.value === 'NO APLICA') numeroCuentaIn.value = '';
            }
            validateForm();
        }

        poseeBancoSel.addEventListener('change', toggleBancoFields);

        // ═══ Validate form ═══
        function validateForm() {
            let valid = true;
            requiredFields.forEach(function (f) {
                if (f.disabled) return;
                if (!f.value || f.value.trim() === '') valid = false;
            });

            // RIF Empresa format validation
            const rifInput = document.getElementById('rifEmpresa');
            const errorRif = document.getElementById('errorRifEmpresa');
            const rifVal = rifInput.value;
            const rifFormatOk = /^[JGVEP]\d{9}$/i.test(rifVal);

            if (rifVal.length === 0) {
                errorRif.style.display = 'none';
                valid = false;
            } else if (!rifFormatOk) {
                errorRif.textContent = 'Favor ingrese el formato válido Ej: J012345678';
                errorRif.style.display = '';
                valid = false;
            } else if (rifVal.charAt(0).toUpperCase() !== 'J') {
                errorRif.textContent = 'El RIF no comienza por J. Verifique que sea correcto.';
                errorRif.style.display = '';
            } else {
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

        // ═══ RIF lookup — auto-fill Razón Social ═══
        (function () {
            const rifInput = document.getElementById('rifEmpresa');
            const razonInput = document.getElementById('razonSocial');
            let debounceTimer = null;

            rifInput.addEventListener('input', function () {
                try {
                    clearTimeout(debounceTimer);
                    const rif = rifInput.value.trim().toUpperCase();
                    rifInput.value = rif;

                    razonInput.value = '';

                    if (!/^[JGVEP]\d{9}$/i.test(rif)) return;

                    debounceTimer = setTimeout(function () {
                        try {
                            fetch(BASE + '/api/buscar-rif', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ rif: rif })
                            })
                                .then(function (r) { return r.json(); })
                                .then(function (data) {
                                    try {
                                        if (data.ok && data.found) {
                                            razonInput.value = data.razon_social || '';
                                        } else {
                                            razonInput.value = '';
                                        }
                                        validateForm();
                                    } catch (err) {
                                        console.error('[RIF lookup response]', err);
                                        razonInput.value = '';
                                    }
                                })
                                .catch(function (err) {
                                    console.error('[RIF lookup fetch]', err);
                                    razonInput.value = '';
                                });
                        } catch (err) {
                            console.error('[RIF lookup debounce]', err);
                        }
                    }, 300);
                } catch (err) {
                    console.error('[RIF lookup input]', err);
                }
            });
        })();

        // ═══ Render table ═══
        function renderTable() {
            const container = document.getElementById('tablaContainerPrestaciones');
            tbody.innerHTML = '';
            let totalDeclarado = 0;

            if (prestaciones.length === 0) {
                container.style.display = 'none';
            } else {
                container.style.display = '';
            }

            prestaciones.forEach(function (item, idx) {
                const vd = parseFloat((item.valor_declarado || '0').replace(/\./g, '').replace(',', '.'));
                totalDeclarado += vd;

                const desc = ` ${item.porcentaje || '0,01'}% de ${item.descripcion || ''}. Nombre de la Empresa: ${item.razon_social || ''}, RIF Empresa: ${item.rif_empresa || ''}. `;

                const tr = document.createElement('tr');
                tr.setAttribute('_ngcontent-vpb-c64', '');
                tr.innerHTML = `
                <td _ngcontent-vpb-c64>Prestaciones Sociales</td>
                <td _ngcontent-vpb-c64>${item.bien_litigioso === 'true' ? 'Si' : 'No'}</td>
                <td _ngcontent-vpb-c64 class=lthgf><div _ngcontent-vpb-c64 style=width:auto> ${desc}</div></td>
                <td _ngcontent-vpb-c64 align=right>${item.valor_declarado || '0,00'}</td>
                <td _ngcontent-vpb-c64>
                    <div _ngcontent-vpb-c64 class=accionesicono>
                        <i _ngcontent-vpb-c64 class="bi bi-pencil-fill" onclick="editarPrestacion(${idx})" title="Modificar"></i>&nbsp;
                        <i _ngcontent-vpb-c64 class="bi-trash-fill" onclick="eliminarPrestacion(${idx})" title="Eliminar"></i>
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
            var bancoSel = document.getElementById('codBanco');
            var data = {
                posee_banco: document.getElementById('poseeBanco').value,
                cod_banco: bancoSel.value,
                nombre_banco: bancoSel.options[bancoSel.selectedIndex]?.text?.trim() || '',
                numero_cuenta: document.getElementById('numeroCuenta').value,
                rif_empresa: document.getElementById('rifEmpresa').value,
                razon_social: document.getElementById('razonSocial').value,
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
            document.getElementById('poseeBanco').value = 'false';
            document.getElementById('codBanco').value = '';
            document.getElementById('codBanco').disabled = true;
            document.getElementById('numeroCuenta').value = 'NO APLICA';
            document.getElementById('numeroCuenta').disabled = true;
            document.getElementById('rifEmpresa').value = '';
            document.getElementById('razonSocial').value = '';
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
        window.editarPrestacion = function (idx) {
            const item = prestaciones[idx];
            if (!item) return;
            editIndex = idx;

            document.getElementById('poseeBanco').value = item.posee_banco || 'false';
            toggleBancoFields();
            if (item.posee_banco === 'true') {
                document.getElementById('codBanco').value = item.cod_banco || '';
                document.getElementById('numeroCuenta').value = item.numero_cuenta || '';
            }
            document.getElementById('rifEmpresa').value = item.rif_empresa || '';
            document.getElementById('razonSocial').value = item.razon_social || '';
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
        window.eliminarPrestacion = function (idx) {
            if (!confirm('¿Está seguro de eliminar este registro?')) return;
            if (!INTENTO_ID) { alert('No hay intento activo'); return; }

            fetch(BASE + '/api/prestaciones-sociales/' + INTENTO_ID + '/eliminar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ index: idx })
            })
                .then(r => r.json())
                .then(data => {
                    if (data.ok) {
                        prestaciones.splice(idx, 1);
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
                ? BASE + '/api/prestaciones-sociales/' + INTENTO_ID + '/editar'
                : BASE + '/api/prestaciones-sociales/' + INTENTO_ID + '/agregar';

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
                            prestaciones[editIndex] = formData;
                        } else {
                            prestaciones.push(formData);
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
