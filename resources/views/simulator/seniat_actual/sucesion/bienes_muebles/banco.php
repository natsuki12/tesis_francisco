<?php
/**
 * Banco — Bienes Muebles > Banco
 * Uses: sim_sucesiones_layout.php
 */
$activeMenu = 'muebles';
$activeItem = 'Banco';
$extraCss = ['/assets/css/simulator/seniat_actual/sucesion/bienes_muebles/banco.css'];
$breadcrumbs = [
    ['label' => 'Inicio', 'url' => '/simulador/servicios_declaracion/dashboard'],
    ['label' => 'Bienes Muebles'],
    ['label' => 'Banco'],
];

ob_start();

$intentoId = $intento['id'] ?? null;
$borradorJson = $intento['borrador_json'] ?? '{}';
$borradorData = json_decode($borradorJson ?: '{}', true) ?: [];
$bancosGuardados = $borradorData['bienes_muebles_banco'] ?? [];
?>

<div _ngcontent-pgi-c72 class="shadow-lg p-3 mb-5 bg-body rounded lenletra">
    <div _ngcontent-pgi-c72 class=card>
        <div _ngcontent-pgi-c72 class=card-header>Banco</div>
        <div _ngcontent-pgi-c72 class=card-body>
            <form _ngcontent-pgi-c72 novalidate class="ng-pristine ng-invalid ng-touched">
                <div _ngcontent-pgi-c72 class=row>
                    <div _ngcontent-pgi-c72 class=col-sm-6>
                        <div _ngcontent-pgi-c72 class=form-group>
                            <div _ngcontent-pgi-c72 class=form-floating><select _ngcontent-pgi-c72
                                    placeholder="Seleccione el Tipo de Bien" formcontrolname=codTipoBien required
                                    class="form-select form-select-sm ng-pristine ng-invalid ng-touched">
                                    <?php foreach ($tiposBien as $tb): ?>
                                        <option _ngcontent-pgi-c72 value="<?= $tb['tipo_bien_mueble_id'] ?>"><?= htmlspecialchars($tb['nombre']) ?>
                                    <?php endforeach; ?>
                                </select><label _ngcontent-pgi-c72 for=tb>Tipo de Bien</label></div>
                        </div>
                    </div>
                    <div _ngcontent-pgi-c72 class=col-sm-6>
                        <div _ngcontent-pgi-c72 class=form-group>
                            <div _ngcontent-pgi-c72 class=form-floating><select _ngcontent-pgi-c72 id=vp
                                    formcontrolname=codBanco required
                                    class="form-select form-select-sm ng-untouched ng-pristine ng-invalid">
                                    <?php foreach ($bancos as $b): ?>
                                        <option _ngcontent-pgi-c72 value="<?= $b['banco_id'] ?>"><?= htmlspecialchars($b['nombre']) ?>
                                    <?php endforeach; ?>
                                </select><label _ngcontent-pgi-c72 for=vp>Nombre Banco</label></div>
                        </div>
                    </div>
                </div><br _ngcontent-pgi-c72>
                <div _ngcontent-pgi-c72 class="row py-3">
                    <div _ngcontent-pgi-c72 class=col-sm-6>
                        <div _ngcontent-pgi-c72 class=form-group>
                            <div _ngcontent-pgi-c72 class="form-floating sm-4"><input _ngcontent-pgi-c72 id=lind
                                    placeholder=# type=text formcontrolname=numeroCuenta maxlength=20 required
                                    class="form-control form-control-sm ng-untouched ng-pristine ng-invalid"
                                    value><label _ngcontent-pgi-c72 for=lind>Número de Cuenta</label></div>
                            <div _ngcontent-pgi-c72 id="errorNumeroCuenta" class="col-sm-6 text-danger" style="display:none">Favor ingrese el formato válido de 20 Dígitos</div>
                        </div>
                    </div>
                    <div _ngcontent-pgi-c72 class=col-sm-6>
                        <div _ngcontent-pgi-c72 class=form-group>
                            <div _ngcontent-pgi-c72 class="form-floating form-floating-sm"><select _ngcontent-pgi-c72
                                    id=bl formcontrolname=indicadorBienLigitioso required
                                    class="form-select form-select-sm ng-untouched ng-pristine ng-valid">
                                    <option _ngcontent-pgi-c72 value=true>Si
                                    <option _ngcontent-pgi-c72 value=false selected>No
                                </select><label _ngcontent-pgi-c72 for=bl>Bien Litigioso</label></div>
                        </div>
                    </div>
                </div>
                <?php include __DIR__ . '/_datos_tribunal.php'; ?>
                <div _ngcontent-pgi-c72 class="row py-3">
                    <div _ngcontent-pgi-c72 class=col-sm-2>
                        <div _ngcontent-pgi-c72 class=form-group>
                            <div _ngcontent-pgi-c72 class="form-floating sm-4"><input _ngcontent-pgi-c72 id=sporcentaje
                                    placeholder=# type=text formcontrolname=porcentaje currencymask maxlength=6 required
                                    class="decimal-input form-control form-control-sm text-end ng-untouched ng-pristine ng-valid"
                                    style=text-align:right value=0,01><label _ngcontent-pgi-c72 for=ssc>Porcentaje
                                    %</label></div>
                        </div>
                    </div>
                    <div _ngcontent-pgi-c72 class=col-sm-10>
                        <div _ngcontent-pgi-c72 class=form-group>
                            <div _ngcontent-pgi-c72 class="form-floating sm-4"><textarea _ngcontent-pgi-c72 id=sc
                                    placeholder=# formcontrolname=descripcion maxlength=4999 required
                                    class="form-control form-control-sm ng-untouched ng-pristine ng-invalid"></textarea><label
                                    _ngcontent-pgi-c72 for=sc>Descripción</label></div>
                        </div>
                    </div>
                </div><br _ngcontent-pgi-c72>
                <div _ngcontent-pgi-c72 class=row>
                    <div _ngcontent-pgi-c72 class=col-sm-6> &nbsp; </div>
                    <div _ngcontent-pgi-c72 class=col-sm-6>
                        <div _ngcontent-pgi-c72 class=form-group>
                            <div _ngcontent-pgi-c72 class="form-floating sm-4"><input _ngcontent-pgi-c72 id=ssc
                                    placeholder=# type=text formcontrolname=valorDeclarado currencymask required
                                    class="decimal-input form-control form-control-sm text-end ng-untouched ng-pristine ng-invalid"
                                    style=text-align:right value=0,00><label _ngcontent-pgi-c72 for=ssc>Valor Declarado
                                    (Bs.)</label></div>
                        </div>
                    </div>
                </div><button _ngcontent-pgi-c72 type=submit class="btn btn-sm btn-danger" disabled>Guardar <i
                        _ngcontent-pgi-c72 class=bi-save></i></button>
            </form>
        </div>
    </div><br _ngcontent-pgi-c72>

    <!-- ═══ Tabla de Bienes Muebles (Banco) Registrados ═══ -->
    <div _ngcontent-pgi-c72 id="tablaContainerBanco">
        <table _ngcontent-pgi-c72 id="tablaBanco" class="table table-bordered table-striped table-sm">
            <thead _ngcontent-pgi-c72>
                <tr _ngcontent-pgi-c72>
                    <th _ngcontent-pgi-c72 scope=col>Tipo de Mueble
                    <th _ngcontent-pgi-c72 scope=col>Bien Litigioso
                    <th _ngcontent-pgi-c72 scope=col>Descripción
                    <th _ngcontent-pgi-c72 scope=col>Valor Declarado (Bs.)
                    <th _ngcontent-pgi-c72 scope=col>Acción
            <tbody _ngcontent-pgi-c72 id="tbodyBanco">
            </tbody>
        </table>
    </div>
</div>

<script>
var bancos = <?= json_encode($bancosGuardados, JSON_UNESCAPED_UNICODE) ?>;

document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const btn  = form.querySelector('button[type=submit]');
    const tbody = document.getElementById('tbodyBanco');
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
        // Número de Cuenta must be exactly 20 numeric digits
        const numCuenta = document.getElementById('lind').value;
        const errorDiv = document.getElementById('errorNumeroCuenta');
        const soloNumeros = /^\d*$/.test(numCuenta);
        if (numCuenta.length !== 20 || !soloNumeros) {
            valid = false;
            // Show error if user has started typing
            errorDiv.style.display = numCuenta.length > 0 ? '' : 'none';
        } else {
            errorDiv.style.display = 'none';
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

    // ═══ Render table ═══
    function renderTable() {
        const container = document.getElementById('tablaContainerBanco');
        tbody.innerHTML = '';
        let totalDeclarado = 0;

        if (bancos.length === 0) {
            container.style.display = 'none';
        } else {
            container.style.display = '';
        }

        bancos.forEach((item, idx) => {
            const vd = parseFloat((item.valor_declarado || '0').replace(/\./g, '').replace(',', '.'));
            totalDeclarado += vd;

            const desc = `${item.porcentaje || '0,01'}% de ${item.descripcion || ''}. Banco: ${item.banco_nombre || ''}, Número de Cuenta: ${item.numero_cuenta || ''}.`;

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${item.tipo_bien_nombre || ''}</td>
                <td>${item.bien_litigioso === 'true' ? 'Si' : 'No'}</td>
                <td class=lthgf><div style="width:auto">${desc}</div></td>
                <td align=right>${item.valor_declarado || '0,00'}</td>
                <td>
                    <div class=accionesicono>
                        <i class="bi bi-pencil-fill" onclick="editarBanco(${idx})" title="Modificar"></i>&nbsp;
                        <i class="bi-trash-fill" onclick="eliminarBanco(${idx})" title="Eliminar"></i>
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
        const bancoSel = form.querySelector('[formcontrolname=codBanco]');
        var data = {
            tipo_bien: tipoBienSel.value,
            tipo_bien_nombre: tipoBienSel.options[tipoBienSel.selectedIndex]?.text?.trim() || '',
            banco: bancoSel.value,
            banco_nombre: bancoSel.options[bancoSel.selectedIndex]?.text?.trim() || '',
            numero_cuenta: document.getElementById('lind').value,
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
        form.querySelector('[formcontrolname=codBanco]').selectedIndex = 0;
        document.getElementById('lind').value = '';
        document.getElementById('bl').value = 'false';
        resetTribunal();
        document.getElementById('sporcentaje').value = '0,01';
        document.getElementById('sc').value = '';
        document.getElementById('ssc').value = '0,00';
    }

    // ═══ Fill form for editing ═══
    function fillForm(item) {
        // Select tipo bien
        const tipoBienSel = form.querySelector('[formcontrolname=codTipoBien]');
        for (let i = 0; i < tipoBienSel.options.length; i++) {
            if (tipoBienSel.options[i].value === item.tipo_bien) {
                tipoBienSel.selectedIndex = i;
                break;
            }
        }
        // Select banco
        const bancoSel = form.querySelector('[formcontrolname=codBanco]');
        for (let i = 0; i < bancoSel.options.length; i++) {
            if (bancoSel.options[i].value === item.banco) {
                bancoSel.selectedIndex = i;
                break;
            }
        }

        document.getElementById('lind').value = item.numero_cuenta || '';
        document.getElementById('bl').value = item.bien_litigioso || 'false';
        setTribunalData(item);
        document.getElementById('sporcentaje').value = item.porcentaje || '0,01';
        document.getElementById('sc').value = item.descripcion || '';
        document.getElementById('ssc').value = item.valor_declarado || '0,00';
    }

    // ═══ CRUD Manager (global) ═══
    initCrudManager({
        intentoId:    INTENTO_ID,
        baseUrl:      BASE,
        apiSlug:      'banco',
        items:        bancos,
        getFormData:  getFormData,
        resetForm:    resetForm,
        renderTable:  renderTable,
        fillForm:     fillForm,
        validateForm: validateForm,
        editName:     'editarBanco',
        deleteName:   'eliminarBanco'
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