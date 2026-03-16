<?php
/**
 * Cuentas y Efectos por Cobrar — Bienes Muebles > Cuenta y Efectos por cobrar
 * Uses: sim_sucesiones_layout.php
 */
$activeMenu = 'muebles';
$activeItem = 'Cuenta y Efectos por cobrar';
$breadcrumbs = [
    ['label' => 'Inicio', 'url' => '/simulador/servicios_declaracion/dashboard'],
    ['label' => 'Bienes Muebles'],
    ['label' => 'Cuentas y Efectos por Cobrar'],
];

ob_start();

$intentoId = $intento['id'] ?? null;
$borradorJson = $intento['borrador_json'] ?? '{}';
$borradorData = json_decode($borradorJson ?: '{}', true) ?: [];
$cuentasGuardadas = $borradorData['bienes_muebles_cuentas_efectos'] ?? [];
?>

<div _ngcontent-pgi-c88 class="shadow-lg p-3 mb-5 bg-body rounded lenletra">
    <div _ngcontent-pgi-c88 class=card>
        <div _ngcontent-pgi-c88 class=card-header>Cuenta y Efectos por cobrar</div>
        <div _ngcontent-pgi-c88 class=card-body>
            <form _ngcontent-pgi-c88 novalidate class="ng-pristine ng-invalid ng-touched">
                <div _ngcontent-pgi-c88 class=row>
                    <div _ngcontent-pgi-c88 class=col-sm-6>
                        <div _ngcontent-pgi-c88 class=form-group>
                            <div _ngcontent-pgi-c88 class=form-floating><select _ngcontent-pgi-c88
                                    placeholder="Seleccione el Tipo de Bien" formcontrolname=codTipoBien required
                                    class="form-select ng-pristine ng-invalid ng-touched">
                                    <?php foreach ($tiposBien as $tb): ?>
                                        <option _ngcontent-pgi-c88 value="<?= $tb['tipo_bien_mueble_id'] ?>"><?= htmlspecialchars($tb['nombre']) ?>
                                    <?php endforeach; ?>
                                </select><label _ngcontent-pgi-c88 for=tb>Tipo de Bien</label></div>
                        </div>
                    </div>
                    <div _ngcontent-pgi-c88 class=col-sm-6>
                        <div _ngcontent-pgi-c88 class=form-group>
                            <div _ngcontent-pgi-c88 class=form-floating><input _ngcontent-pgi-c88 id=lind
                                    placeholder=# type=text formcontrolname=rifCedula maxlength=10 required
                                    class="form-control form-control-sm ng-pristine ng-invalid ng-touched"
                                    value><label _ngcontent-pgi-c88 for=lind>Rif o Cédula</label></div>
                        </div>
                    </div>
                </div><br _ngcontent-pgi-c88>
                <div _ngcontent-pgi-c88 class=row>
                    <div _ngcontent-pgi-c88 class=col-sm-6>
                        <div _ngcontent-pgi-c88 class=form-group>
                            <div _ngcontent-pgi-c88 class=form-floating><input _ngcontent-pgi-c88 id=nombreApellido
                                    placeholder=# type=text formcontrolname=nombreApellido required
                                    class="form-control form-control-sm ng-pristine ng-invalid ng-touched"
                                    value><label _ngcontent-pgi-c88 for=nombreApellido>Apellidos y Nombres</label></div>
                        </div>
                    </div>
                    <div _ngcontent-pgi-c88 class=col-sm-6>
                        <div _ngcontent-pgi-c88 class=form-group>
                            <div _ngcontent-pgi-c88 class=form-floating><select _ngcontent-pgi-c88 id=bl
                                    formcontrolname=indicadorBienLigitioso required
                                    class="form-select ng-pristine ng-valid ng-touched">
                                    <option _ngcontent-pgi-c88 value=true>Si
                                    <option _ngcontent-pgi-c88 value=false selected>No
                                </select><label _ngcontent-pgi-c88 for=bl>Bien Litigioso</label></div>
                        </div>
                    </div>
                </div>
                <?php include __DIR__ . '/_datos_tribunal.php'; ?>
                <div _ngcontent-pgi-c88 class="row py-3">
                    <div _ngcontent-pgi-c88 class=col-sm-2>
                        <div _ngcontent-pgi-c88 class=form-group>
                            <div _ngcontent-pgi-c88 class="form-floating sm-4"><input _ngcontent-pgi-c88 id=sporcentaje
                                    placeholder=# type=text formcontrolname=porcentaje currencymask maxlength=6 required
                                    class="form-control form-control-sm text-end ng-untouched ng-pristine ng-valid"
                                    style=text-align:right value=0,01><label _ngcontent-pgi-c88
                                    for=ssc>Porcentaje %</label></div>
                        </div>
                    </div>
                    <div _ngcontent-pgi-c88 class=col-sm-10>
                        <div _ngcontent-pgi-c88 class=form-group>
                            <div _ngcontent-pgi-c88 class=form-floating><textarea _ngcontent-pgi-c88 id=descripcion
                                    placeholder=# formcontrolname=descripcion maxlength=4999 required
                                    class="form-control form-control-sm ng-untouched ng-pristine ng-invalid"></textarea><label
                                    _ngcontent-pgi-c88 for=descripcion>Descripción</label></div>
                        </div>
                    </div>
                </div><br _ngcontent-pgi-c88>
                <div _ngcontent-pgi-c88 class=row>
                    <div _ngcontent-pgi-c88 class=col-sm-6> &nbsp; </div>
                    <div _ngcontent-pgi-c88 class=col-sm-6>
                        <div _ngcontent-pgi-c88 class=form-group>
                            <div _ngcontent-pgi-c88 class=form-floating><input _ngcontent-pgi-c88 id=ssc
                                    placeholder=# type=text formcontrolname=valorDeclarado currencymask required
                                    class="form-control form-control-sm text-end ng-untouched ng-pristine ng-invalid"
                                    style=text-align:right value=0,00><label _ngcontent-pgi-c88 for=ssc>Valor Declarado
                                    (Bs.)</label></div>
                        </div>
                    </div>
                </div><br _ngcontent-pgi-c88>
                <button _ngcontent-pgi-c88 type=submit class="btn btn-sm btn-danger" disabled>Guardar <i
                        _ngcontent-pgi-c88 class=bi-save></i></button>
            </form>
        </div>
    </div><br _ngcontent-pgi-c88>

    <!-- ═══ Tabla de Cuentas y Efectos por Cobrar Registrados ═══ -->
    <div _ngcontent-pgi-c88 id="tablaContainerCuentasEfectos">
        <table _ngcontent-pgi-c88 id="tablaCuentasEfectos" class="table table-bordered table-striped table-sm">
            <thead _ngcontent-pgi-c88>
                <tr _ngcontent-pgi-c88>
                    <th _ngcontent-pgi-c88 scope=col>Tipo de Mueble
                    <th _ngcontent-pgi-c88 scope=col>Bien Litigioso
                    <th _ngcontent-pgi-c88 scope=col>Descripción
                    <th _ngcontent-pgi-c88 scope=col>Valor Declarado (Bs.)
                    <th _ngcontent-pgi-c88 scope=col>Acción
            <tbody _ngcontent-pgi-c88 id="tbodyCuentasEfectos">
            </tbody>
        </table>
    </div>
</div>

<script>
const INTENTO_ID = <?= json_encode($intentoId) ?>;
const BASE = <?= json_encode(rtrim(($_ENV['APP_BASE'] ?? getenv('APP_BASE')) ?: '', '/')) ?>;
let cuentas = <?= json_encode($cuentasGuardadas, JSON_UNESCAPED_UNICODE) ?>;
let editIndex = null;

document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const btn  = form.querySelector('button[type=submit]');
    const tbody = document.getElementById('tbodyCuentasEfectos');
    if (!form || !btn) return;

    const requiredFields = form.querySelectorAll(
        'input[required], textarea[required], select[required]'
    );

    // ═══ Validate form ═══
    function validateForm() {
        let valid = true;
        requiredFields.forEach(f => {
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
    if (typeof initTribunalToggle === 'function') initTribunalToggle();

    // ═══ Render table ═══
    function renderTable() {
        const container = document.getElementById('tablaContainerCuentasEfectos');
        tbody.innerHTML = '';
        let totalDeclarado = 0;

        if (cuentas.length === 0) {
            container.style.display = 'none';
        } else {
            container.style.display = '';
        }

        cuentas.forEach((item, idx) => {
            const vd = parseFloat((item.valor_declarado || '0').replace(/\./g, '').replace(',', '.'));
            totalDeclarado += vd;

            const desc = `${item.porcentaje || '0,01'}% de ${item.descripcion || ''}. Nombre del Deudor: ${item.nombre_apellido || ''}, RIF Deudor: ${item.rif_cedula || ''}.`;

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${item.tipo_bien_nombre || ''}</td>
                <td>${item.bien_litigioso === 'true' ? 'Si' : 'No'}</td>
                <td class=lthgf><div style="width:auto">${desc}</div></td>
                <td align=right>${item.valor_declarado || '0,00'}</td>
                <td>
                    <div class=accionesicono>
                        <i class="bi bi-pencil-fill" onclick="editarCuenta(${idx})" title="Modificar"></i>&nbsp;
                        <i class="bi-trash-fill" onclick="eliminarCuenta(${idx})" title="Eliminar"></i>
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
            '<td align=right> ' + totalDeclarado.toLocaleString('es-VE', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</td>' +
            '<td></td>';
        tbody.appendChild(trTotal);
    }

    // ═══ Collect form data ═══
    function getFormData() {
        const tipoBienSel = form.querySelector('[formcontrolname=codTipoBien]');
        var data = {
            tipo_bien: tipoBienSel.value,
            tipo_bien_nombre: tipoBienSel.options[tipoBienSel.selectedIndex]?.text?.trim() || '',
            rif_cedula: document.getElementById('lind').value,
            nombre_apellido: document.getElementById('nombreApellido').value,
            bien_litigioso: document.getElementById('bl').value,
            porcentaje: document.getElementById('sporcentaje').value,
            descripcion: document.getElementById('descripcion').value,
            valor_declarado: document.getElementById('ssc').value,
        };
        if (typeof getTribunalData === 'function') Object.assign(data, getTribunalData());
        return data;
    }

    // ═══ Reset form ═══
    function resetForm() {
        form.querySelector('[formcontrolname=codTipoBien]').selectedIndex = 0;
        document.getElementById('lind').value = '';
        document.getElementById('nombreApellido').value = '';
        document.getElementById('bl').value = 'false';
        if (typeof resetTribunal === 'function') resetTribunal();
        document.getElementById('sporcentaje').value = '0,01';
        document.getElementById('descripcion').value = '';
        document.getElementById('ssc').value = '0,00';
        editIndex = null;
        btn.textContent = 'Guardar ';
        const icon = document.createElement('i');
        icon.className = 'bi-save';
        btn.appendChild(icon);
        btn.disabled = true;
    }

    // ═══ Fill form for editing ═══
    window.editarCuenta = function(idx) {
        const item = cuentas[idx];
        if (!item) return;
        editIndex = idx;

        // Select tipo bien
        const tipoBienSel = form.querySelector('[formcontrolname=codTipoBien]');
        for (let i = 0; i < tipoBienSel.options.length; i++) {
            if (tipoBienSel.options[i].value === item.tipo_bien) {
                tipoBienSel.selectedIndex = i;
                break;
            }
        }

        document.getElementById('lind').value = item.rif_cedula || '';
        document.getElementById('nombreApellido').value = item.nombre_apellido || '';
        document.getElementById('bl').value = item.bien_litigioso || 'false';
        if (typeof setTribunalData === 'function') setTribunalData(item);
        document.getElementById('sporcentaje').value = item.porcentaje || '0,01';
        document.getElementById('descripcion').value = item.descripcion || '';
        document.getElementById('ssc').value = item.valor_declarado || '0,00';

        btn.textContent = 'Actualizar ';
        const icon = document.createElement('i');
        icon.className = 'bi-save';
        btn.appendChild(icon);

        validateForm();
        window.scrollTo({top: 0, behavior: 'smooth'});
    };

    // ═══ Delete ═══
    window.eliminarCuenta = function(idx) {
        if (!confirm('¿Está seguro de eliminar este registro?')) return;
        if (!INTENTO_ID) { alert('No hay intento activo'); return; }

        fetch(BASE + '/api/cuentas-efectos/' + INTENTO_ID + '/eliminar', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({index: idx})
        })
        .then(r => r.json())
        .then(data => {
            if (data.ok) {
                cuentas.splice(idx, 1);
                renderTable();
            } else {
                alert(data.error || 'Error al eliminar');
            }
        })
        .catch(() => alert('Error de conexión'));
    };

    // ═══ Submit (add/edit) ═══
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        if (!INTENTO_ID) { alert('No hay intento activo'); return; }

        const formData = getFormData();
        const isEdit = editIndex !== null;
        const url = isEdit
            ? BASE + '/api/cuentas-efectos/' + INTENTO_ID + '/editar'
            : BASE + '/api/cuentas-efectos/' + INTENTO_ID + '/agregar';

        if (isEdit) formData.index = editIndex;

        fetch(url, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(formData)
        })
        .then(r => r.json())
        .then(data => {
            if (data.ok) {
                if (isEdit) {
                    cuentas[editIndex] = formData;
                } else {
                    cuentas.push(formData);
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
