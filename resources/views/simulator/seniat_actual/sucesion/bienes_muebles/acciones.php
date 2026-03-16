<?php
/**
 * Acciones — Bienes Muebles > Acciones
 * Uses: sim_sucesiones_layout.php
 */
$activeMenu = 'muebles';
$activeItem = 'Acciones';
$breadcrumbs = [
    ['label' => 'Inicio', 'url' => '/simulador/servicios_declaracion/dashboard'],
    ['label' => 'Bienes Muebles'],
    ['label' => 'Acciones'],
];

ob_start();

$intentoId = $intento['id'] ?? null;
$borradorJson = $intento['borrador_json'] ?? '{}';
$borradorData = json_decode($borradorJson ?: '{}', true) ?: [];
$accionesGuardadas = $borradorData['bienes_muebles_acciones'] ?? [];
?>

<div _ngcontent-div-c62 class="shadow-lg p-3 mb-5 bg-body rounded lenletra">
    <div _ngcontent-div-c62 class=card>
        <div _ngcontent-div-c62 class=card-header>Acciones</div>
        <div _ngcontent-div-c62 class=card-body>
            <form _ngcontent-div-c62 novalidate class="ng-untouched ng-pristine ng-invalid">
                <div _ngcontent-div-c62 class=row>
                    <div _ngcontent-div-c62 class=col-sm-3>
                        <div _ngcontent-div-c62 class=form-group>
                            <div _ngcontent-div-c62 class=form-floating><select _ngcontent-div-c62
                                    placeholder="Seleccione el Tipo de Bien" formcontrolname=codTipoBien required
                                    class="form-select form-select-sm ng-untouched ng-pristine ng-invalid">
                                    <?php foreach ($tiposBien as $tb): ?>
                                        <option _ngcontent-div-c62 value="<?= $tb['tipo_bien_mueble_id'] ?>"><?= htmlspecialchars($tb['nombre']) ?>
                                    <?php endforeach; ?>
                                </select><label _ngcontent-div-c62 for=tb>Tipo de Bien</label></div>
                        </div>
                    </div>
                    <div _ngcontent-div-c62 class=col-sm-2>
                        <div _ngcontent-div-c62 class=form-group>
                            <div _ngcontent-div-c62 class="form-floating sm-3"><input _ngcontent-div-c62 id=rifEmpresa
                                    placeholder=# type=text formcontrolname=rifEmpresa maxlength=10 required
                                    class="form-control form-control-sm ng-untouched ng-pristine ng-invalid"
                                    value><label _ngcontent-div-c62 for=rifEmpresa>Rif Empresa</label></div>
                            <div _ngcontent-div-c62 id="errorRifEmpresa" class="col-sm-4 text-danger"
                                style="display:none">Favor ingrese el formato válido Ej: J012345678</div>
                        </div>
                    </div>
                    <div _ngcontent-div-c62 class=col-sm-5>
                        <div _ngcontent-div-c62 class=form-group>
                            <div _ngcontent-div-c62 class=form-floating><input _ngcontent-div-c62 id=razonSocial
                                    placeholder=# type=text formcontrolname=razonSocial required
                                    class="form-control form-control-sm ng-untouched ng-pristine" disabled
                                    value><label _ngcontent-div-c62 for=razonSocial>Razón Social</label></div>
                        </div>
                    </div>
                    <div _ngcontent-div-c62 class=col-sm-2>
                        <div _ngcontent-div-c62 class=form-group>
                            <div _ngcontent-div-c62 class="form-floating form-floating-sm"><select _ngcontent-div-c62
                                    id=bl formcontrolname=indicadorBienLigitioso required
                                    class="form-select form-select-sm ng-untouched ng-pristine ng-valid">
                                    <option _ngcontent-div-c62 value=true>Si
                                    <option _ngcontent-div-c62 value=false selected>No
                                </select><label _ngcontent-div-c62 for=bl>Bien Litigioso</label></div>
                        </div>
                    </div>
                </div>
                <?php include __DIR__ . '/_datos_tribunal.php'; ?>
                <div _ngcontent-div-c62 class="row py-3">
                    <div _ngcontent-div-c62 class=col-sm-2>
                        <div _ngcontent-div-c62 class=form-group>
                            <div _ngcontent-div-c62 class="form-floating sm-4"><input _ngcontent-div-c62 id=sporcentaje
                                    placeholder=# type=text formcontrolname=porcentaje currencymask maxlength=6 required
                                    class="form-control form-control-sm text-end ng-untouched ng-pristine ng-valid"
                                    style=text-align:right value=0,01><label _ngcontent-div-c62
                                    for=sporcentaje>Porcentaje
                                    %</label></div>
                        </div>
                    </div>
                    <div _ngcontent-div-c62 class=col-sm-10>
                        <div _ngcontent-div-c62 class=form-group>
                            <div _ngcontent-div-c62 class="form-floating sm-4"><textarea _ngcontent-div-c62 id=sc
                                    placeholder=# formcontrolname=descripcion maxlength=4999 required
                                    class="form-control form-control-sm ng-untouched ng-pristine ng-invalid"></textarea><label
                                    _ngcontent-div-c62 for=sc>Descripción</label></div>
                        </div>
                    </div>
                </div>
                <div _ngcontent-div-c62 class=row>
                    <div _ngcontent-div-c62 class=col-sm-6> &nbsp; </div>
                    <div _ngcontent-div-c62 class=col-sm-6>
                        <div _ngcontent-div-c62 class=form-group>
                            <div _ngcontent-div-c62 class="form-floating sm-4"><input _ngcontent-div-c62 id=ssc
                                    placeholder=# type=text formcontrolname=valorDeclarado currencymask required
                                    class="form-control form-control-sm text-end ng-untouched ng-pristine ng-invalid"
                                    style=text-align:right value=0,00><label _ngcontent-div-c62 for=ssc>Valor Declarado
                                    (Bs.)</label></div>
                        </div>
                    </div>
                </div><button _ngcontent-div-c62 type=submit class="btn btn-sm btn-danger" disabled>Guardar <i
                        _ngcontent-div-c62 class=bi-save></i></button>
            </form>
        </div>
    </div><br _ngcontent-div-c62>

    <!-- ═══ Tabla de Acciones Registradas ═══ -->
    <div _ngcontent-div-c62 id="tablaContainerAcciones">
        <table _ngcontent-div-c62 id=tableim class="table table-bordered table-sm">
            <thead _ngcontent-div-c62>
                <tr _ngcontent-div-c62>
                    <th _ngcontent-div-c62 scope=col class=lth>Tipo de Mueble
                    <th _ngcontent-div-c62 scope=col class=lth>Bien Litigioso
                    <th _ngcontent-div-c62 scope=col class=lthg>Descripción
                    <th _ngcontent-div-c62 scope=col class=lth>Valor Declarado (Bs.)
                    <th _ngcontent-div-c62 scope=col class=lth>Acción
            <tbody _ngcontent-div-c62 id="tbodyAcciones">
            </tbody>
        </table>
    </div>
</div>

<script>
    const INTENTO_ID = <?= json_encode($intentoId) ?>;
    const BASE = <?= json_encode(rtrim(($_ENV['APP_BASE'] ?? getenv('APP_BASE')) ?: '', '/')) ?>;
    let acciones = <?= json_encode($accionesGuardadas, JSON_UNESCAPED_UNICODE) ?>;
    let editIndex = null;

    document.addEventListener('DOMContentLoaded', function () {
        const form = document.querySelector('form');
        const btn = form.querySelector('button[type=submit]');
        const tbody = document.getElementById('tbodyAcciones');
        if (!form || !btn) return;

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
            const container = document.getElementById('tablaContainerAcciones');
            tbody.innerHTML = '';
            let totalDeclarado = 0;

            if (acciones.length === 0) {
                container.style.display = 'none';
            } else {
                container.style.display = '';
            }

            acciones.forEach((item, idx) => {
                const vd = parseFloat((item.valor_declarado || '0').replace(/\./g, '').replace(',', '.'));
                totalDeclarado += vd;

                const desc = ` ${item.porcentaje || '0,01'}% de ${item.descripcion || ''}. Nombre de la Empresa: ${item.razon_social || ''}, RIF Empresa: ${item.rif_empresa || ''}. `;

                const tr = document.createElement('tr');
                tr.setAttribute('_ngcontent-div-c62', '');
                tr.innerHTML = `
                <td _ngcontent-div-c62 class=lth>${item.tipo_bien_nombre || ''}</td>
                <td _ngcontent-div-c62 class=lth>${item.bien_litigioso === 'true' ? 'Si' : 'No'}</td>
                <td _ngcontent-div-c62 class=lthgf><div _ngcontent-div-c62 style=width:auto> ${desc}</div></td>
                <td _ngcontent-div-c62 align=right>${item.valor_declarado || '0,00'}</td>
                <td _ngcontent-div-c62>
                    <div _ngcontent-div-c62 class=accionesicono>
                        <i _ngcontent-div-c62 class="bi bi-pencil-fill" onclick="editarAccion(${idx})" title="Modificar"></i>&nbsp;
                        <i _ngcontent-div-c62 class="bi-trash-fill" onclick="eliminarAccion(${idx})" title="Eliminar"></i>
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
            const tipoBienSel = form.querySelector('[formcontrolname=codTipoBien]');
            var data = {
            tipo_bien: tipoBienSel.value,
            tipo_bien_nombre: tipoBienSel.options[tipoBienSel.selectedIndex]?.text?.trim() || '',
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
            form.querySelector('[formcontrolname=codTipoBien]').selectedIndex = 0;
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
        window.editarAccion = function (idx) {
            const item = acciones[idx];
            if (!item) return;
            editIndex = idx;

            const tipoBienSel = form.querySelector('[formcontrolname=codTipoBien]');
            for (let i = 0; i < tipoBienSel.options.length; i++) {
                if (tipoBienSel.options[i].value === item.tipo_bien) {
                    tipoBienSel.selectedIndex = i;
                    break;
                }
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
        window.eliminarAccion = function (idx) {
            if (!confirm('¿Está seguro de eliminar este registro?')) return;
            if (!INTENTO_ID) { alert('No hay intento activo'); return; }

            fetch(BASE + '/api/acciones/' + INTENTO_ID + '/eliminar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ index: idx })
            })
                .then(r => r.json())
                .then(data => {
                    if (data.ok) {
                        acciones.splice(idx, 1);
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
                ? BASE + '/api/acciones/' + INTENTO_ID + '/editar'
                : BASE + '/api/acciones/' + INTENTO_ID + '/agregar';

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
                            acciones[editIndex] = formData;
                        } else {
                            acciones.push(formData);
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
