<?php
/**
 * Bonos — Bienes Muebles > Bonos
 * Uses: sim_sucesiones_layout.php
 */
$activeMenu = 'muebles';
$activeItem = 'Bonos';
$breadcrumbs = [
    ['label' => 'Inicio', 'url' => '/simulador/servicios_declaracion/dashboard'],
    ['label' => 'Bienes Muebles'],
    ['label' => 'Bonos'],
];

ob_start();

$intentoId = $intento['id'] ?? null;
$borradorJson = $intento['borrador_json'] ?? '{}';
$borradorData = json_decode($borradorJson ?: '{}', true) ?: [];
$bonosGuardados = $borradorData['bienes_muebles_bonos'] ?? [];
?>

<div _ngcontent-sdd-c93 class="shadow-lg p-3 mb-5 bg-body rounded lenletra">
    <div _ngcontent-sdd-c93 class=card>
        <div _ngcontent-sdd-c93 class=card-header>Bonos</div>
        <div _ngcontent-sdd-c93 class=card-body>
            <form _ngcontent-sdd-c93 novalidate class="ng-pristine ng-invalid ng-touched">
                <div _ngcontent-sdd-c93 class=row>
                    <div _ngcontent-sdd-c93 class=col-sm-3>
                        <div _ngcontent-sdd-c93 class=form-group>
                            <div _ngcontent-sdd-c93 class=form-floating><select _ngcontent-sdd-c93
                                    placeholder="Seleccione el Tipo de Bien" formcontrolname=codTipoBien required
                                    class="form-select form-select-sm ng-pristine ng-valid ng-touched">
                                    <option _ngcontent-sdd-c93 value=18 selected>Bonos
                                </select><label _ngcontent-sdd-c93 for=tb>Tipo de Bien</label></div>
                        </div>
                    </div>
                    <div _ngcontent-sdd-c93 class=col-sm-6>
                        <div _ngcontent-sdd-c93 class=form-group>
                            <div _ngcontent-sdd-c93 class=form-floating><input _ngcontent-sdd-c93 id=tipoBonos
                                    placeholder=# type=text formcontrolname=tipoBonos maxlength=20 required
                                    class="form-control form-control-sm ng-pristine ng-invalid ng-touched"
                                    value><label _ngcontent-sdd-c93 for=tipoBonos>Tipo de Bonos</label></div>
                        </div>
                    </div>
                    <div _ngcontent-sdd-c93 class=col-sm-3>
                        <div _ngcontent-sdd-c93 class=form-group>
                            <div _ngcontent-sdd-c93 class=form-floating><select _ngcontent-sdd-c93 id=bl
                                    formcontrolname=indicadorBienLigitioso required
                                    class="form-select ng-pristine ng-valid ng-touched">
                                    <option _ngcontent-sdd-c93 value=true>Si
                                    <option _ngcontent-sdd-c93 value=false selected>No
                                </select><label _ngcontent-sdd-c93 for=bl>Bien Litigioso</label></div>
                        </div>
                    </div>
                </div>
                <?php include __DIR__ . '/_datos_tribunal.php'; ?>
                <div _ngcontent-sdd-c93 class="row py-3">
                    <div _ngcontent-sdd-c93 class=col-sm-6>
                        <div _ngcontent-sdd-c93 class=form-group>
                            <div _ngcontent-sdd-c93 class=form-floating><input _ngcontent-sdd-c93 id=numeroBonos
                                    placeholder=# type=text formcontrolname=numeroBonos maxlength=10 required
                                    class="form-control form-control-sm ng-pristine ng-invalid ng-touched"
                                    value><label _ngcontent-sdd-c93 for=numeroBonos>Número de Bonos</label></div>
                        </div>
                    </div>
                    <div _ngcontent-sdd-c93 class=col-sm-6>
                        <div _ngcontent-sdd-c93 class=form-group>
                            <div _ngcontent-sdd-c93 class=form-floating><input _ngcontent-sdd-c93 id=numeroSerie
                                    placeholder=# type=text formcontrolname=numeroSerie maxlength=10 required
                                    class="form-control form-control-sm ng-pristine ng-invalid ng-touched"
                                    value><label _ngcontent-sdd-c93 for=numeroSerie>Número de Serie</label></div>
                        </div>
                    </div>
                </div><br _ngcontent-sdd-c93>
                <div _ngcontent-sdd-c93 class=row>
                    <div _ngcontent-sdd-c93 class=col-sm-2>
                        <div _ngcontent-sdd-c93 class=form-group>
                            <div _ngcontent-sdd-c93 class="form-floating sm-4"><input _ngcontent-sdd-c93 id=sporcentaje
                                    placeholder=# type=text formcontrolname=porcentaje currencymask maxlength=6 required
                                    class="form-control form-control-sm text-end ng-pristine ng-valid ng-touched"
                                    style=text-align:right value=0,01><label _ngcontent-sdd-c93
                                    for=sporcentaje>Porcentaje %</label></div>
                        </div>
                    </div>
                    <div _ngcontent-sdd-c93 class=col-sm-10>
                        <div _ngcontent-sdd-c93 class=form-group>
                            <div _ngcontent-sdd-c93 class=form-floating><textarea _ngcontent-sdd-c93 id=descripcion
                                    placeholder=# formcontrolname=descripcion maxlength=4999 required
                                    class="form-control form-control-sm ng-pristine ng-invalid ng-touched"></textarea><label
                                    _ngcontent-sdd-c93 for=descripcion>Descripción</label></div>
                        </div>
                    </div>
                </div><br _ngcontent-sdd-c93>
                <div _ngcontent-sdd-c93 class=row>
                    <div _ngcontent-sdd-c93 class=col-sm-6> &nbsp; </div>
                    <div _ngcontent-sdd-c93 class=col-sm-6>
                        <div _ngcontent-sdd-c93 class=form-group>
                            <div _ngcontent-sdd-c93 class=form-floating><input _ngcontent-sdd-c93 id=ssc
                                    placeholder=# type=text formcontrolname=valorDeclarado currencymask required
                                    class="form-control form-control-sm text-end ng-pristine ng-invalid ng-touched"
                                    style=text-align:right value=0,00><label _ngcontent-sdd-c93 for=ssc>Valor Declarado
                                    (Bs.)</label></div>
                        </div>
                    </div>
                </div><br _ngcontent-sdd-c93>
                <button _ngcontent-sdd-c93 type=submit class="btn btn-sm btn-danger" disabled>Guardar <i
                        _ngcontent-sdd-c93 class=bi-save></i></button>
            </form>
        </div>
    </div><br _ngcontent-sdd-c93>

    <!-- ═══ Tabla de Bonos Registrados ═══ -->
    <div _ngcontent-sdd-c93 id="tablaContainerBonos">
        <table _ngcontent-sdd-c93 id="tablaBonos" class="table table-bordered table-striped table-sm">
            <thead _ngcontent-sdd-c93>
                <tr _ngcontent-sdd-c93>
                    <th _ngcontent-sdd-c93 scope=col>Tipo de Mueble
                    <th _ngcontent-sdd-c93 scope=col>Bien Litigioso
                    <th _ngcontent-sdd-c93 scope=col>Descripción
                    <th _ngcontent-sdd-c93 scope=col>Valor Declarado (Bs.)
                    <th _ngcontent-sdd-c93 scope=col>Acción
            <tbody _ngcontent-sdd-c93 id="tbodyBonos">
            </tbody>
        </table>
    </div>
</div>

<script>
const INTENTO_ID = <?= json_encode($intentoId) ?>;
const BASE = <?= json_encode(rtrim(($_ENV['APP_BASE'] ?? getenv('APP_BASE')) ?: '', '/')) ?>;
let bonos = <?= json_encode($bonosGuardados, JSON_UNESCAPED_UNICODE) ?>;
let editIndex = null;

document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const btn  = form.querySelector('button[type=submit]');
    const tbody = document.getElementById('tbodyBonos');
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
        const container = document.getElementById('tablaContainerBonos');
        tbody.innerHTML = '';
        let totalDeclarado = 0;

        if (bonos.length === 0) {
            container.style.display = 'none';
        } else {
            container.style.display = '';
        }

        bonos.forEach((item, idx) => {
            const vd = parseFloat((item.valor_declarado || '0').replace(/\./g, '').replace(',', '.'));
            totalDeclarado += vd;

            const desc = `${item.porcentaje || '0,01'}% de ${item.descripcion || ''}. Tipo de Bono: ${item.tipo_bonos || ''}, Número de Bonos: ${item.numero_bonos || ''}, Número de Serie: ${item.numero_serie || ''}.`;

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${item.tipo_bien_nombre || 'Bonos'}</td>
                <td>${item.bien_litigioso === 'true' ? 'Si' : 'No'}</td>
                <td class=lthgf><div style="width:auto">${desc}</div></td>
                <td align=right>${item.valor_declarado || '0,00'}</td>
                <td>
                    <div class=accionesicono>
                        <i class="bi bi-pencil-fill" onclick="editarBono(${idx})" title="Modificar"></i>&nbsp;
                        <i class="bi-trash-fill" onclick="eliminarBono(${idx})" title="Eliminar"></i>
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
            tipo_bonos: document.getElementById('tipoBonos').value,
            numero_bonos: document.getElementById('numeroBonos').value,
            numero_serie: document.getElementById('numeroSerie').value,
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
        document.getElementById('tipoBonos').value = '';
        document.getElementById('bl').value = 'false';
        if (typeof resetTribunal === 'function') resetTribunal();
        document.getElementById('numeroBonos').value = '';
        document.getElementById('numeroSerie').value = '';
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
    window.editarBono = function(idx) {
        const item = bonos[idx];
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

        document.getElementById('tipoBonos').value = item.tipo_bonos || '';
        document.getElementById('bl').value = item.bien_litigioso || 'false';
        if (typeof setTribunalData === 'function') setTribunalData(item);
        document.getElementById('numeroBonos').value = item.numero_bonos || '';
        document.getElementById('numeroSerie').value = item.numero_serie || '';
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
    window.eliminarBono = function(idx) {
        if (!confirm('¿Está seguro de eliminar este registro?')) return;
        if (!INTENTO_ID) { alert('No hay intento activo'); return; }

        fetch(BASE + '/api/bonos/' + INTENTO_ID + '/eliminar', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({index: idx})
        })
        .then(r => r.json())
        .then(data => {
            if (data.ok) {
                bonos.splice(idx, 1);
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
            ? BASE + '/api/bonos/' + INTENTO_ID + '/editar'
            : BASE + '/api/bonos/' + INTENTO_ID + '/agregar';

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
                    bonos[editIndex] = formData;
                } else {
                    bonos.push(formData);
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
