<?php
/**
 * Herederos — Identificación Herederos
 * Uses: sim_sucesiones_layout.php
 *
 * Receives from route: $intento (array with id, borrador_json, etc.)
 * JSON structure: relaciones[] — array of objects with:
 *   apellido, nombre, tipodocumento (R/C), cedula, parentesco (code), parentescoText, pasaporte, idDocumento
 *   parentescoText = "HEREDERO" for herederos, "REPRESENTANTE DE LA SUCESION" for rep
 *
 * ── Editable fields (via modal): premuerto, fecha_nacimiento, fecha_fallecimiento, parentesco_id
 */
$activeMenu = 'herederos';
$activeItem = 'Herederos';
$extraCss = [
    '/assets/css/simulator/seniat_actual/sucesion/herencia/tipo_herencia.css',
];
$breadcrumbs = [
    ['label' => 'Inicio', 'url' => '/simulador/servicios_declaracion/dashboard'],
    ['label' => 'Identificación Herederos'],
    ['label' => 'Heredero'],
];

// ─── Extract data from borrador_json ───
$borradorRaw = $intento['borrador_json'] ?? '{}';
$borrador = json_decode($borradorRaw, true) ?: [];
$relaciones = $borrador['relaciones'] ?? [];
$intentoId = $intento['id'] ?? null;

// Parentesco catalog — loaded from sim_cat_parentescos via route
$catalogoParentescos = $catalogoParentescos ?? [];

ob_start();
?>

<div _ngcontent-pgi-c74 class="shadow p-3 mb-5 bg-white rounded lenletra">
    <div _ngcontent-pgi-c74>
        <div _ngcontent-pgi-c74 class=card>
            <div _ngcontent-pgi-c74 class=card-header>Identificación Herederos</div>
            <div _ngcontent-pgi-c74 class=card-body>
                <table _ngcontent-pgi-c74 class="table table-bordered table-striped table-sm">
                    <thead _ngcontent-pgi-c74>
                        <tr _ngcontent-pgi-c74>
                            <th _ngcontent-pgi-c74></th>
                            <th _ngcontent-pgi-c74 scope=col>Apellidos y Nombres</th>
                            <th _ngcontent-pgi-c74 scope=col>Cédula de Identidad</th>
                            <th _ngcontent-pgi-c74 scope=col>Caracter</th>
                            <th _ngcontent-pgi-c74 scope=col>Fecha de Nacimiento</th>
                            <th _ngcontent-pgi-c74 scope=col>Premuerto</th>
                            <th _ngcontent-pgi-c74 scope=col>Fecha de Fallecimiento</th>
                            <th _ngcontent-pgi-c74 scope=col>Parentesco</th>
                            <th _ngcontent-pgi-c74 scope=col>Acción</th>
                        </tr>
                    </thead>
                    <tbody _ngcontent-pgi-c74 id="tbodyHerederos">
                        <!-- Rendered by JS -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════ -->
<!-- MODAL: Modificar Registro                  -->
<!-- ═══════════════════════════════════════════ -->
<!-- ─── Modal CSS ─── -->
<style>
    .modal-backdrop-custom {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, .5);
        z-index: 1050;
        justify-content: center;
        align-items: flex-start;
        padding-top: 30px;
    }

    .modal-backdrop-custom.show {
        display: flex
    }

    .modal-dialog-custom {
        background: #fff;
        border-radius: .5rem;
        width: 100%;
        max-width: 520px;
        box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .15);
        overflow: hidden
    }

    .modal-dialog-custom .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #dee2e6
    }

    .modal-dialog-custom .modal-header h5 {
        margin: 0;
        font-weight: 700;
        font-size: 1.15rem
    }

    .modal-dialog-custom .modal-header .btn-close-custom {
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: #000;
        opacity: .5;
        line-height: 1
    }

    .modal-dialog-custom .modal-header .btn-close-custom:hover {
        opacity: .75
    }

    .modal-dialog-custom .modal-body {
        padding: 1.25rem;
        padding-bottom: 2.5rem
    }

    .modal-dialog-custom .modal-body p.heredero-label {
        margin: 0 0 1.25rem;
        font-size: .9rem
    }

    .modal-dialog-custom .modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: .5rem;
        padding: .75rem 1.25rem;
        border-top: 1px solid #dee2e6
    }

    /* Form fields inside modal */
    .modal-dialog-custom .campo {
        position: relative;
        margin-bottom: 1rem
    }

    .modal-dialog-custom .campo select,
    .modal-dialog-custom .campo input {
        display: block;
        width: 100%;
        padding: .6rem .75rem;
        font-size: .9rem;
        border: 1px solid #ced4da;
        border-radius: .375rem;
        outline: none;
        background: #fff;
        color: #212529;
        appearance: auto;
        -webkit-appearance: auto
    }

    .modal-dialog-custom .campo select:focus,
    .modal-dialog-custom .campo input:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 .2rem rgba(13, 110, 253, .25)
    }

    .modal-dialog-custom .campo label {
        position: absolute;
        top: -.55rem;
        left: .65rem;
        background: #fff;
        padding: 0 .3rem;
        font-size: .75rem;
        color: #6c757d;
        pointer-events: none
    }

    /* Buttons */
    .modal-dialog-custom .btn-cancelar {
        padding: .45rem 1.1rem;
        border: 1px solid #ced4da;
        border-radius: .375rem;
        background: #fff;
        color: #212529;
        cursor: pointer;
        font-size: .9rem
    }

    .modal-dialog-custom .btn-cancelar:hover {
        background: #e9ecef
    }

    .modal-dialog-custom .btn-modificar {
        padding: .45rem 1.1rem;
        border: none;
        border-radius: .375rem;
        background: var(--color-principal-btn, #245b98);
        color: #fff;
        cursor: pointer;
        font-size: .9rem;
        font-weight: 600
    }

    .modal-dialog-custom .btn-modificar:hover {
        background: var(--color-principal-fuerte, #164193)
    }

    /* Date hidden input */
    .modal-dialog-custom .fecha-hidden {
        position: absolute;
        opacity: 0;
        width: 0;
        height: 0;
        pointer-events: none
    }
</style>

<!-- ─── Modal HTML ─── -->
<div id="modalEditar" class="modal-backdrop-custom">
    <div class="modal-dialog-custom">
        <div class="modal-header">
            <h5>MODIFICAR REGISTRO!</h5>
            <button type="button" id="btnCerrarModal" class="btn-close-custom" aria-label="Cerrar">&times;</button>
        </div>
        <div class="modal-body">
            <p class="heredero-label">Heredero: <strong id="modalHerederoNombre"></strong></p>

            <div class="campo">
                <select id="modalPremuerto">
                    <option value="No">No</option>
                    <option value="Si">Si</option>
                </select>
                <label>Premuerto</label>
            </div>

            <div class="form-group" style="margin-bottom:1rem">
                <label>Fecha de Nacimiento</label>
                <div class="input-group">
                    <input type="text" id="modalFechaNacText" placeholder="Seleccione Fecha" class="form-control form-control-sm" readonly>
                    <input type="date" id="modalFechaNac" class="fecha-hidden">
                    <i class="bi bi-calendar3 btn btn-outline-secondary" data-target="modalFechaNac"></i>
                </div>
            </div>

            <div class="form-group" id="campoFechaFall" style="margin-bottom:1rem">
                <label>Fecha de Fallecimiento</label>
                <div class="input-group">
                    <input type="text" id="modalFechaFallText" placeholder="Seleccione Fecha" class="form-control form-control-sm" readonly>
                    <input type="date" id="modalFechaFallecimiento" class="fecha-hidden">
                    <i class="bi bi-calendar3 btn btn-outline-secondary" data-target="modalFechaFallecimiento"></i>
                </div>
            </div>

            <div class="campo">
                <select id="modalParentesco">
                    <?php foreach ($catalogoParentescos as $id => $label): ?>
                        <option value="<?= $id ?>"><?= htmlspecialchars($label) ?></option>
                    <?php endforeach; ?>
                </select>
                <label>Parentesco</label>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" id="btnCancelarModal" class="btn-cancelar">Cancelar</button>
            <button type="button" id="btnModificar" class="btn-modificar">Modificar</button>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════ -->
<!-- SCRIPT                                     -->
<!-- ═══════════════════════════════════════════ -->
<script>
    (function () {
        'use strict';

        var BASE_URL = '<?= rtrim(base_url(), "/") ?>';
        var INTENTO_ID = <?= json_encode($intentoId) ?>;

        // Full borrador + relaciones array
        var borradorCompleto = <?= json_encode($borrador) ?>;
        var relaciones = borradorCompleto.relaciones || [];

        // Parentesco catalog for display
        var PARENTESCOS = <?= json_encode($catalogoParentescos, JSON_UNESCAPED_UNICODE) ?>;

        var editandoIdx = null; // index in relaciones[] being edited

        // ─── Render Table ───
        function renderTabla() {
            var tbody = document.getElementById('tbodyHerederos');
            var html = '';
            var hayHerederos = false;

            relaciones.forEach(function (r, idx) {
                if (!r.parentescoText || r.parentescoText.toUpperCase() !== 'HEREDERO') return;
                hayHerederos = true;

                var nombre = ((r.apellido || '') + ' ' + (r.nombre || '')).toUpperCase();
                var cedula = r.idDocumento || ((r.tipodocumento || '') + (r.cedula || ''));
                var fechaNac = r.fecha_nacimiento || '';
                var premuerto = r.premuerto === 'Si' ? 'SI' : 'NO';
                var fechaFall = r.fecha_fallecimiento || '';
                var parentescoId = parseInt(r.parentesco_id || 0);
                var parentescoText = PARENTESCOS[parentescoId] || 'SIN DEFINIR';

                // Icon: green if has parentesco (not Sin definir) and fecha_nacimiento
                var actualizado = parentescoId && parentescoId !== 19 && fechaNac;
                var icono = actualizado
                    ? '<i placement="top" ngbtooltip="Heredero Actualizado" class="bi bi-check-circle-fill text-success"></i>'
                    : '<i placement="top" ngbtooltip="Heredero No Actualizado" class="bi bi-x-circle-fill text-danger"></i>';

                html += '<tr>';
                html += '<td>' + icono + '</td>';
                html += '<td>' + nombre + '</td>';
                html += '<td>' + cedula + '</td>';
                html += '<td>HEREDERO</td>';
                html += '<td>' + fechaNac + '</td>';
                html += '<td>' + premuerto + '</td>';
                html += '<td>' + fechaFall + '</td>';
                html += '<td>' + parentescoText.toUpperCase() + '</td>';
                html += '<td><div class="accionesicono text-center">';
                html += '<i class="bi bi-pencil-fill" style="cursor:pointer" data-edit-idx="' + idx + '"></i>&nbsp;';
                html += '</div></td>';
                html += '</tr>';
            });

            if (!hayHerederos) {
                html = '<tr><td colspan="9" class="text-center">No hay herederos registrados</td></tr>';
            }

            tbody.innerHTML = html;

            // Bind edit buttons
            tbody.querySelectorAll('[data-edit-idx]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    abrirModal(parseInt(this.getAttribute('data-edit-idx')));
                });
            });
        }

        // ─── Open Modal ───
        function abrirModal(idx) {
            try {
                var r = relaciones[idx];
                if (!r) return;
                editandoIdx = idx;

                var nombre = ((r.apellido || '') + ' ' + (r.nombre || '')).toUpperCase();
                var cedula = r.idDocumento || ((r.tipodocumento || '') + (r.cedula || ''));
                document.getElementById('modalHerederoNombre').textContent = nombre + ' ' + cedula;

                document.getElementById('modalPremuerto').value = r.premuerto === 'Si' ? 'Si' : 'No';
                setFecha('modalFechaNac', 'modalFechaNacText', r.fecha_nacimiento || '');
                setFecha('modalFechaFallecimiento', 'modalFechaFallText', r.fecha_fallecimiento || '');
                document.getElementById('modalParentesco').value = r.parentesco_id || '19';

                // Show/hide fecha fallecimiento based on premuerto
                toggleFechaFallecimiento();

                var modal = document.getElementById('modalEditar');
                modal.classList.add('show');
            } catch (err) {
                console.error('Error al abrir modal:', err);
                alert('Error al cargar los datos del heredero.');
            }
        }

        // ─── Close Modal ───
        function cerrarModal() {
            document.getElementById('modalEditar').classList.remove('show');
            editandoIdx = null;
        }

        // ─── Toggle fecha fallecimiento visibility ───
        function toggleFechaFallecimiento() {
            var premuerto = document.getElementById('modalPremuerto').value;
            var campoFechaFall = document.getElementById('campoFechaFall');
            if (premuerto === 'Si') {
                campoFechaFall.style.display = 'block';
            } else {
                campoFechaFall.style.display = 'none';
                setFecha('modalFechaFallecimiento', 'modalFechaFallText', '');
            }
        }

        // ─── Date helpers ───
        function formatDate(isoStr) {
            if (!isoStr) return '';
            var parts = isoStr.split('-');
            if (parts.length !== 3) return isoStr;
            return parts[2] + '/' + parts[1] + '/' + parts[0]; // dd/mm/yyyy
        }

        function setFecha(hiddenId, textId, value) {
            document.getElementById(hiddenId).value = value;
            document.getElementById(textId).value = value ? formatDate(value) : '';
        }

        // ─── Save (Modificar) ───
        function guardarModificacion() {
            try {
                if (editandoIdx === null) return;

                var premuerto = document.getElementById('modalPremuerto').value;
                var fechaNac = document.getElementById('modalFechaNac').value;
                var fechaFall = document.getElementById('modalFechaFallecimiento').value;
                var parentescoId = document.getElementById('modalParentesco').value;

                // Validation
                if (!fechaNac) {
                    alert('Debe ingresar la Fecha de Nacimiento.');
                    return;
                }
                if (parentescoId === '19' || !parentescoId) {
                    alert('Debe seleccionar un Parentesco.');
                    return;
                }
                if (premuerto === 'Si' && !fechaFall) {
                    alert('Si el heredero es premuerto, debe ingresar la Fecha de Fallecimiento.');
                    return;
                }

                // Prepare changes (don't apply to memory yet)
                var cambios = {
                    premuerto: premuerto,
                    fecha_nacimiento: fechaNac,
                    fecha_fallecimiento: (premuerto === 'Si') ? fechaFall : '',
                    parentesco_id: parseInt(parentescoId)
                };

                // Persist to backend first, then update memory
                guardarBorrador(editandoIdx, cambios);
                cerrarModal();
            } catch (err) {
                console.error('Error al guardar modificación:', err);
                alert('Ocurrió un error al modificar. Intente nuevamente.');
            }
        }

        // ─── Save borrador to backend ───
        function guardarBorrador(idx, cambios) {
            var btnModificar = document.getElementById('btnModificar');
            btnModificar.disabled = true;
            btnModificar.textContent = 'Guardando...';

            // Apply changes to a copy for the payload
            var relCopia = JSON.parse(JSON.stringify(relaciones));
            relCopia[idx].premuerto = cambios.premuerto;
            relCopia[idx].fecha_nacimiento = cambios.fecha_nacimiento;
            relCopia[idx].fecha_fallecimiento = cambios.fecha_fallecimiento;
            relCopia[idx].parentesco_id = cambios.parentesco_id;

            var borradorCopia = JSON.parse(JSON.stringify(borradorCompleto));
            borradorCopia.relaciones = relCopia;

            var payload = {
                borrador: borradorCopia,
                paso_actual: 1
            };

            fetch(BASE_URL + '/api/intentos/' + INTENTO_ID + '/guardar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.ok) {
                        // Solo actualizar memoria después de confirmación del servidor
                        relaciones[idx].premuerto = cambios.premuerto;
                        relaciones[idx].fecha_nacimiento = cambios.fecha_nacimiento;
                        relaciones[idx].fecha_fallecimiento = cambios.fecha_fallecimiento;
                        relaciones[idx].parentesco_id = cambios.parentesco_id;
                        borradorCompleto.relaciones = relaciones;
                        renderTabla();
                    } else {
                        alert('Error al guardar: ' + (data.error || 'Error desconocido'));
                    }
                })
                .catch(function (err) {
                    alert('Error de conexión: ' + err.message);
                })
                .finally(function () {
                    btnModificar.disabled = false;
                    btnModificar.textContent = 'Modificar';
                });
        }

        // ─── Event Listeners ───
        document.getElementById('btnCerrarModal').addEventListener('click', cerrarModal);
        document.getElementById('btnCancelarModal').addEventListener('click', cerrarModal);
        document.getElementById('btnModificar').addEventListener('click', guardarModificacion);
        document.getElementById('modalPremuerto').addEventListener('change', toggleFechaFallecimiento);

        // Close modal on background click
        document.getElementById('modalEditar').addEventListener('click', function (e) {
            if (e.target === this) cerrarModal();
        });

        // Calendar icon clicks → open native date picker
        document.querySelectorAll('.modal-dialog-custom [data-target]').forEach(function (icon) {
            icon.addEventListener('click', function () {
                var target = document.getElementById(this.getAttribute('data-target'));
                if (target && target.showPicker) {
                    target.showPicker();
                } else if (target) {
                    target.click();
                }
            });
        });

        // Also open picker when clicking the text input
        document.getElementById('modalFechaNacText').addEventListener('click', function () {
            var h = document.getElementById('modalFechaNac');
            if (h.showPicker) h.showPicker(); else h.click();
        });
        document.getElementById('modalFechaFallText').addEventListener('click', function () {
            var h = document.getElementById('modalFechaFallecimiento');
            if (h.showPicker) h.showPicker(); else h.click();
        });

        // Sync hidden date → text display on change
        document.getElementById('modalFechaNac').addEventListener('change', function () {
            document.getElementById('modalFechaNacText').value = formatDate(this.value);
        });
        document.getElementById('modalFechaFallecimiento').addEventListener('change', function () {
            document.getElementById('modalFechaFallText').value = formatDate(this.value);
        });

        // ─── Initial render ───
        renderTabla();
    })();
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../../../layouts/sim_sucesiones_layout.php';
?>