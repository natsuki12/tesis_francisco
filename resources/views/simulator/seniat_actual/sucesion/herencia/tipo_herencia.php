<?php
/**
 * Tipo Herencia — Herencia > Tipo Herencia
 * Uses: sim_sucesiones_layout.php
 *
 * Receives from route: $intento (array with id, borrador_json, etc.)
 */
$activeMenu = 'herencia';
$activeItem = 'Tipo Herencia';
$extraCss = [
    '/assets/css/simulator/seniat_actual/sucesion/herencia/tipo_herencia.css',
];
$breadcrumbs = [
    ['label' => 'Inicio', 'url' => '/simulador/servicios_declaracion/dashboard'],
    ['label' => 'Tipo Herencia'],
];

// ─── Extract intento data for JS ───
$intentoId = $intento['id'] ?? null;
$borradorRaw = $intento['borrador_json'] ?? '{}';
$borrador = json_decode($borradorRaw, true) ?: [];
$tiposHerenciaGuardados = $borrador['tipos_herencia']['items'] ?? [];

// Catalog names for rendering table
$catalogoNombres = [
    1 => 'Testamento',
    2 => 'Ab-Intestato',
    3 => 'Pura y Simple',
    4 => 'Presunción de Ausencia',
    5 => 'Presunción de Muerte por Accidente',
    6 => 'Beneficio de Inventario',
];

ob_start();
?>

<div _ngcontent-pgi-c66 class="shadow-lg p-3 mb-5 bg-body rounded lenletra">
    <div _ngcontent-pgi-c66>
        <div _ngcontent-pgi-c66 class=card>
            <div _ngcontent-pgi-c66 class=card-header>Tipo Herencia</div>
            <div _ngcontent-pgi-c66 class=card-body>
                <form _ngcontent-pgi-c66 novalidate class="lenletra" id="formTipoHerencia">
                    <!-- Pura y Simple -->
                    <div _ngcontent-pgi-c66 class=row>
                        <div _ngcontent-pgi-c66 class=col-sm-4>
                            <div _ngcontent-pgi-c66 class=form-check>
                                <input _ngcontent-pgi-c66 type=checkbox id=chkPuraSimple class="form-check-input"
                                    value=03>
                                <label _ngcontent-pgi-c66 for=chkPuraSimple class=form-check-label> Pura y Simple
                                </label>
                            </div>
                        </div>
                    </div>
                    <!-- Presunción de Ausencia -->
                    <div _ngcontent-pgi-c66 class=row>
                        <div _ngcontent-pgi-c66 class=col-sm-4>
                            <div _ngcontent-pgi-c66 class=form-check>
                                <input _ngcontent-pgi-c66 type=checkbox id=chkPresuncionAusencia
                                    class="form-check-input" value=04>
                                <label _ngcontent-pgi-c66 for=chkPresuncionAusencia class=form-check-label> Presunción
                                    de Ausencia </label>
                            </div>
                        </div>
                    </div>
                    <!-- Testamento + campos condicionales -->
                    <div _ngcontent-pgi-c66 class="row align-items-center">
                        <div _ngcontent-pgi-c66 class=col-sm-4>
                            <div _ngcontent-pgi-c66 class=form-check>
                                <input _ngcontent-pgi-c66 type=checkbox id=chkTestamento class="form-check-input"
                                    value=01
                                    onchange="document.getElementById('testamentoFields').style.display=this.checked?'flex':'none'">
                                <label _ngcontent-pgi-c66 for=chkTestamento class=form-check-label> Testamento </label>
                            </div>
                        </div>
                        <div _ngcontent-pgi-c66 class="col-sm-8" id="testamentoFields" style="display:none">
                            <div style="display:flex;gap:.75rem;align-items:flex-end">
                                <div style="flex:1">
                                    <div _ngcontent-pgi-c66 class=form-group>
                                        <div _ngcontent-pgi-c66 class=form-floating>
                                            <select _ngcontent-pgi-c66 id=tipoTestamento
                                                class="form-select form-select-sm">
                                                <option _ngcontent-pgi-c66 value="" selected>Seleccione</option>
                                                <option _ngcontent-pgi-c66 value="Abierto">Abierto</option>
                                                <option _ngcontent-pgi-c66 value="Cerrado">Cerrado</option>
                                            </select>
                                            <label _ngcontent-pgi-c66 for=tipoTestamento>Tipo Testamento</label>
                                        </div>
                                    </div>
                                </div>
                                <div style="flex:1">
                                    <div _ngcontent-pgi-c66 class=form-group>
                                        <label _ngcontent-pgi-c66 for=fechaTestamento>Fecha Testamento</label>
                                        <div _ngcontent-pgi-c66 class=input-group>
                                            <input _ngcontent-pgi-c66 id=fechaTestamento placeholder="Seleccione Fecha"
                                                type=text formcontrolname=fechaTestamento ngbdatepicker required
                                                class="form-control form-control-sm ng-untouched ng-pristine ng-invalid"
                                                value>
                                            <i _ngcontent-pgi-c66 placement=top ngbtooltip=Calendario
                                                class="bi bi-calendar3 btn btn-outline-secondary"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Ab-Intestato -->
                    <div _ngcontent-pgi-c66 class="row mt-1">
                        <div _ngcontent-pgi-c66 class=col-sm-4>
                            <div _ngcontent-pgi-c66 class=form-check>
                                <input _ngcontent-pgi-c66 type=checkbox id=chkAbIntestato class="form-check-input"
                                    value=02>
                                <label _ngcontent-pgi-c66 for=chkAbIntestato class=form-check-label> Ab-Intestato
                                </label>
                            </div>
                        </div>
                    </div>
                    <!-- Presunción de Muerte por Accidente -->
                    <div _ngcontent-pgi-c66 class=row>
                        <div _ngcontent-pgi-c66 class=col-sm-4>
                            <div _ngcontent-pgi-c66 class=form-check>
                                <input _ngcontent-pgi-c66 type=checkbox id=chkPresuncionMuerte class="form-check-input"
                                    value=05>
                                <label _ngcontent-pgi-c66 for=chkPresuncionMuerte class=form-check-label> Presunción de
                                    Muerte por Accidente </label>
                            </div>
                        </div>
                    </div>
                    <!-- Beneficio de Inventario + campo condicional -->
                    <div _ngcontent-pgi-c66 class="row align-items-center">
                        <div _ngcontent-pgi-c66 class=col-sm-4>
                            <div _ngcontent-pgi-c66 class=form-check>
                                <input _ngcontent-pgi-c66 type=checkbox id=chkBeneficioInventario
                                    class="form-check-input" value=06
                                    onchange="document.getElementById('beneficioFields').style.display=this.checked?'flex':'none'">
                                <label _ngcontent-pgi-c66 for=chkBeneficioInventario class=form-check-label> Beneficio
                                    de Inventario </label>
                            </div>
                        </div>
                        <div _ngcontent-pgi-c66 class="col-sm-4" id="beneficioFields" style="display:none">
                            <div _ngcontent-pgi-c66 class=form-group>
                                <div _ngcontent-pgi-c66 class=input-group>
                                    <input _ngcontent-pgi-c66 id=fechaBeneficio placeholder="Seleccione Fecha" type=text
                                        formcontrolname=fechaBeneficio ngbdatepicker required
                                        class="form-control form-control-sm ng-untouched ng-pristine ng-invalid" value>
                                    <i _ngcontent-pgi-c66 placement=top ngbtooltip=Calendario
                                        class="bi bi-calendar3 btn btn-outline-secondary"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div _ngcontent-pgi-c66 class="row py-3">
                        <div _ngcontent-pgi-c66 class=col-sm-12>
                            <button _ngcontent-pgi-c66 type=submit id="tourBtnGuardarHerencia"
                                class="btn btn-danger btn-sm">Guardar&nbsp;<i _ngcontent-pgi-c66
                                    class=bi-save></i></button>
                        </div>
                    </div>
                </form>
                <br _ngcontent-pgi-c66>
                <table _ngcontent-pgi-c66 id="tablaTiposHerencia"
                    class="table table-bordered table-striped table-sm lenletra">
                    <thead _ngcontent-pgi-c66>
                        <tr _ngcontent-pgi-c66>
                            <th _ngcontent-pgi-c66 scope=col>Tipo de Herencia</th>
                            <th _ngcontent-pgi-c66 scope=col>Tipo</th>
                            <th _ngcontent-pgi-c66 scope=col>Fecha</th>
                            <th _ngcontent-pgi-c66 scope=col>Acción</th>
                        </tr>
                    </thead>
                    <tbody _ngcontent-pgi-c66 id="tbodyTipos">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        // ─── Config ───
        var INTENTO_ID = <?= json_encode($intentoId) ?>;
        var BASE_URL = <?= json_encode(base_url('')) ?>;
        var CATALOGO = <?= json_encode($catalogoNombres, JSON_UNESCAPED_UNICODE) ?>;

        // ─── State: items currently saved ───
        var itemsGuardados = <?= json_encode($tiposHerenciaGuardados, JSON_UNESCAPED_UNICODE) ?>;

        // ─── Full borrador (to merge with on save) ───
        var borradorCompleto = <?= json_encode($borrador, JSON_UNESCAPED_UNICODE) ?>;

        // ─── Checkbox map ───
        var checkboxes = {
            1: document.getElementById('chkTestamento'),
            2: document.getElementById('chkAbIntestato'),
            3: document.getElementById('chkPuraSimple'),
            4: document.getElementById('chkPresuncionAusencia'),
            5: document.getElementById('chkPresuncionMuerte'),
            6: document.getElementById('chkBeneficioInventario')
        };

        // ─── Edit state ───
        var editandoIdx = null;

        // ─── Render table ───
        function renderTabla() {
            var tbody = document.getElementById('tbodyTipos');
            tbody.innerHTML = '';

            if (itemsGuardados.length === 0) {
                var tr = document.createElement('tr');
                tr.innerHTML = '<td colspan="4" class="text-center">No hay tipos de herencia registrados</td>';
                tbody.appendChild(tr);
                return;
            }

            itemsGuardados.forEach(function (item, idx) {
                var tr = document.createElement('tr');
                var nombre = CATALOGO[item.tipo_herencia_id] || 'Desconocido';
                var tipo = item.subtipo_testamento || '-';
                var fecha = item.fecha_testamento || item.fecha_conclusion_inventario || '-';

                // Edit icon only for Testamento (1) and Beneficio de Inventario (6)
                var editHtml = '';
                if (item.tipo_herencia_id === 1 || item.tipo_herencia_id === 6) {
                    editHtml = '<i class="bi bi-pencil-fill" style="cursor:pointer" data-edit-idx="' + idx + '" title="Editar"></i>&nbsp; ';
                }

                tr.innerHTML =
                    '<td>' + nombre + '</td>' +
                    '<td>' + tipo + '</td>' +
                    '<td>' + fecha + '</td>' +
                    '<td class="text-center"><div class="accionesicono">' + editHtml + '<i class="bi bi-trash-fill text-danger" style="cursor:pointer" data-del-idx="' + idx + '" title="Eliminar"></i></div></td>';
                tbody.appendChild(tr);
            });

            // Bind delete buttons
            tbody.querySelectorAll('[data-del-idx]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    eliminarItem(parseInt(this.getAttribute('data-del-idx')));
                });
            });

            // Bind edit buttons
            tbody.querySelectorAll('[data-edit-idx]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    editarItem(parseInt(this.getAttribute('data-edit-idx')));
                });
            });
        }

        // ─── Edit item (load into form without removing) ───
        function editarItem(idx) {
            try {
                var item = itemsGuardados[idx];
                if (!item) return;
                resetForm();
                editandoIdx = idx;

                if (item.tipo_herencia_id === 1) {
                    checkboxes[1].checked = true;
                    document.getElementById('testamentoFields').style.display = 'flex';
                    document.getElementById('tipoTestamento').value = item.subtipo_testamento || '';
                    document.getElementById('fechaTestamento').value = item.fecha_testamento || '';
                }

                if (item.tipo_herencia_id === 6) {
                    checkboxes[6].checked = true;
                    document.getElementById('beneficioFields').style.display = 'flex';
                    document.getElementById('fechaBeneficio').value = item.fecha_conclusion_inventario || '';
                }
            } catch (err) {
                console.error('Error al editar item:', err);
                alert('Error al cargar los datos para edición.');
            }
        }

        // ─── Reset form ───
        function resetForm() {
            editandoIdx = null;
            Object.values(checkboxes).forEach(function (cb) {
                if (cb) cb.checked = false;
            });
            document.getElementById('testamentoFields').style.display = 'none';
            document.getElementById('beneficioFields').style.display = 'none';
            document.getElementById('tipoTestamento').value = '';
            document.getElementById('fechaTestamento').value = '';
            document.getElementById('fechaBeneficio').value = '';
        }

        // ─── Check for duplicates (skip the item being edited) ───
        function yaExiste(tipoId) {
            return itemsGuardados.some(function (item, idx) {
                if (idx === editandoIdx) return false;
                return item.tipo_herencia_id === tipoId;
            });
        }

        // ─── Save to backend ───
        function guardar() {
            borradorCompleto.tipos_herencia = { items: itemsGuardados };

            var payload = {
                borrador: borradorCompleto,
                paso_actual: 2
            };

            fetch(BASE_URL + '/api/intentos/' + INTENTO_ID + '/guardar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.ok) {
                        renderTabla();
                        resetForm();
                    } else {
                        alert('Error al guardar: ' + (data.error || 'Error desconocido'));
                    }
                })
                .catch(function (err) {
                    alert('Error de conexión: ' + err.message);
                });
        }

        // ─── Delete item ───
        function eliminarItem(idx) {
            try {
                if (idx < 0 || idx >= itemsGuardados.length) return;
                if (editandoIdx === idx) {
                    resetForm();
                } else if (editandoIdx !== null && editandoIdx > idx) {
                    editandoIdx--;
                }
                itemsGuardados.splice(idx, 1);
                guardar();
            } catch (err) {
                console.error('Error al eliminar item:', err);
                alert('Error al eliminar el tipo de herencia.');
            }
        }

        // ─── Form submit ───
        document.getElementById('formTipoHerencia').addEventListener('submit', function (e) {
            e.preventDefault();

            try {
                if (!INTENTO_ID) {
                    alert('No hay un intento activo. Inicie una asignación primero.');
                    return;
                }

                // ── Editing mode: only process the edited type ──
                if (editandoIdx !== null) {
                    var editItem = itemsGuardados[editandoIdx];
                    if (!editItem) { resetForm(); return; }
                    var tipoId = editItem.tipo_herencia_id;

                    var updatedItem = {
                        tipo_herencia_id: tipoId,
                        subtipo_testamento: null,
                        fecha_testamento: null,
                        fecha_conclusion_inventario: null
                    };

                    if (tipoId === 1) {
                        var subtipo = document.getElementById('tipoTestamento').value;
                        var fecha = document.getElementById('fechaTestamento').value;
                        var camposFaltantes = [];
                        if (!subtipo) camposFaltantes.push('Tipo Testamento');
                        if (!fecha) camposFaltantes.push('Fecha Testamento');
                        if (camposFaltantes.length > 0) {
                            alert('Para guardar Testamento debe completar:\n\n• ' + camposFaltantes.join('\n• '));
                            return;
                        }
                        updatedItem.subtipo_testamento = subtipo;
                        updatedItem.fecha_testamento = fecha;
                    }

                    if (tipoId === 6) {
                        var fechaB = document.getElementById('fechaBeneficio').value;
                        if (!fechaB) {
                            alert('Para guardar Beneficio de Inventario debe completar la Fecha de Conclusión de Inventario.');
                            return;
                        }
                        updatedItem.fecha_conclusion_inventario = fechaB;
                    }

                    // Replace in-place
                    itemsGuardados[editandoIdx] = updatedItem;
                    guardar();
                    return;
                }

                // ── Normal mode: add new items ──
                var nuevos = [];
                var duplicados = [];
                var hayError = false;

                Object.keys(checkboxes).forEach(function (idStr) {
                    if (hayError) return;

                    var cb = checkboxes[idStr];
                    if (!cb || !cb.checked) return;

                    var tipoId = parseInt(idStr);

                    if (yaExiste(tipoId)) {
                        duplicados.push(CATALOGO[tipoId]);
                        return;
                    }

                    var item = {
                        tipo_herencia_id: tipoId,
                        subtipo_testamento: null,
                        fecha_testamento: null,
                        fecha_conclusion_inventario: null
                    };

                    if (tipoId === 1) {
                        var subtipo = document.getElementById('tipoTestamento').value;
                        var fecha = document.getElementById('fechaTestamento').value;
                        var camposFaltantes = [];
                        if (!subtipo) camposFaltantes.push('Tipo Testamento');
                        if (!fecha) camposFaltantes.push('Fecha Testamento');
                        if (camposFaltantes.length > 0) {
                            alert('Para guardar Testamento debe completar:\n\n• ' + camposFaltantes.join('\n• '));
                            hayError = true;
                            return;
                        }
                        item.subtipo_testamento = subtipo;
                        item.fecha_testamento = fecha;
                    }

                    if (tipoId === 6) {
                        var fechaB = document.getElementById('fechaBeneficio').value;
                        if (!fechaB) {
                            alert('Para guardar Beneficio de Inventario debe completar la Fecha de Conclusión de Inventario.');
                            hayError = true;
                            return;
                        }
                        item.fecha_conclusion_inventario = fechaB;
                    }

                    nuevos.push(item);
                });

                if (hayError) return;

                if (duplicados.length > 0) {
                    alert('Los siguientes tipos ya están guardados y no se pueden duplicar:\n\n• ' + duplicados.join('\n• '));
                    if (nuevos.length === 0) return;
                }

                if (nuevos.length === 0 && duplicados.length === 0) {
                    alert('Debe seleccionar al menos un tipo de herencia.');
                    return;
                }

                nuevos.forEach(function (item) {
                    itemsGuardados.push(item);
                });

                guardar();

            } catch (err) {
                console.error('Error en el formulario:', err);
                alert('Ocurrió un error inesperado. Intente nuevamente.');
            }
        });

        // ─── Initial render ───
        renderTabla();
    })();
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../../../layouts/sim_sucesiones_layout.php';
?>