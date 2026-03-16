<?php
/**
 * Prórrogas — Solicitud de Prórrogas
 * Uses: sim_sucesiones_layout.php
 */
$activeMenu = 'prorrogas';
$activeItem = 'Prórrogas';
$breadcrumbs = [
    ['label' => 'Inicio', 'url' => '/simulador/servicios_declaracion/dashboard'],
    ['label' => 'Prórrogas'],
];

/* ── Data from controller ── */
$intento = $intento ?? null;
$intentoId = $intento['id'] ?? 0;

$borrador = [];
if ($intento && !empty($intento['borrador_json'])) {
    $borrador = json_decode($intento['borrador_json'], true) ?: [];
}
$prorrogasGuardadas = $borrador['prorrogas'] ?? [];

ob_start();
?>

<div _ngcontent-sdd-c77 class="shadow-lg p-3 mb-5 bg-body rounded lenletra">
    <div _ngcontent-sdd-c77>
        <div _ngcontent-sdd-c77 class=card>
            <div _ngcontent-sdd-c77 class=card-header>Prórrogas</div>
            <div _ngcontent-sdd-c77 class=card-body>
                <form _ngcontent-sdd-c77 novalidate>

                    <!-- ═══ Row 1: Fecha Solicitud · Fecha Resolución · Fecha Vencimiento ═══ -->
                    <div _ngcontent-sdd-c77 class=row>
                        <div _ngcontent-sdd-c77 class=col-sm-4>
                            <div _ngcontent-sdd-c77 class=form-group>
                                <label _ngcontent-sdd-c77 for=fechaSolicitud>Fecha Solicitud</label>
                                <div _ngcontent-sdd-c77 class=input-group>
                                    <input _ngcontent-sdd-c77 id=fechaSolicitud placeholder="Seleccione Fecha"
                                        type=text ngbdatepicker formcontrolname=fecha_solicitud required
                                        class="form-control form-control-sm">
                                    <i _ngcontent-sdd-c77 class="bi bi-calendar3 btn btn-outline-secondary"></i>
                                </div>
                            </div>
                        </div>
                        <div _ngcontent-sdd-c77 class=col-sm-4>
                            <div _ngcontent-sdd-c77 class=form-group>
                                <label _ngcontent-sdd-c77 for=fechaResolucion>Fecha Resolución</label>
                                <div _ngcontent-sdd-c77 class=input-group>
                                    <input _ngcontent-sdd-c77 id=fechaResolucion placeholder="Seleccione Fecha"
                                        type=text ngbdatepicker formcontrolname=fecha_resolucion required
                                        class="form-control form-control-sm">
                                    <i _ngcontent-sdd-c77 class="bi bi-calendar3 btn btn-outline-secondary"></i>
                                </div>
                            </div>
                        </div>
                        <div _ngcontent-sdd-c77 class=col-sm-4>
                            <div _ngcontent-sdd-c77 class=form-group>
                                <label _ngcontent-sdd-c77 for=fechaVencimiento>Fecha Vencimiento</label>
                                <div _ngcontent-sdd-c77 class=input-group>
                                    <input _ngcontent-sdd-c77 id=fechaVencimiento placeholder="Seleccione Fecha"
                                        type=text ngbdatepicker formcontrolname=fecha_vencimiento required
                                        class="form-control form-control-sm">
                                    <i _ngcontent-sdd-c77 class="bi bi-calendar3 btn btn-outline-secondary"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ═══ Row 2: N° Resolución · Plazo Otorgado ═══ -->
                    <div _ngcontent-sdd-c77 class="row py-3">
                        <div _ngcontent-sdd-c77 class=col-sm-4>
                            <div _ngcontent-sdd-c77 class=form-group>
                                <div _ngcontent-sdd-c77 class="form-floating sm-4">
                                    <input _ngcontent-sdd-c77 id=nroResolucion placeholder=#
                                        type=text formcontrolname=nro_resolucion required
                                        class="form-control form-control-sm">
                                    <label _ngcontent-sdd-c77 for=nroResolucion>N° Resolución</label>
                                </div>
                            </div>
                        </div>
                        <div _ngcontent-sdd-c77 class=col-sm-4>
                            <div _ngcontent-sdd-c77 class=form-group>
                                <div _ngcontent-sdd-c77 class="form-floating sm-4">
                                    <input _ngcontent-sdd-c77 id=plazoDias placeholder=#
                                        type=number formcontrolname=plazo_dias min=1 required
                                        class="form-control form-control-sm">
                                    <label _ngcontent-sdd-c77 for=plazoDias>Plazo Otorgado (Días)</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button _ngcontent-sdd-c77 type=submit class="btn btn-sm btn-danger" disabled>Guardar <i _ngcontent-sdd-c77 class=bi-save></i></button>
                </form>
            </div>
        </div>
        <br _ngcontent-sdd-c77>

        <!-- ═══ Table ═══ -->
        <div id="tablaContainerProrrogas" style="display:none">
            <table _ngcontent-sdd-c77 class="table table-bordered table-striped table-sm">
                <thead _ngcontent-sdd-c77>
                    <tr _ngcontent-sdd-c77>
                        <th _ngcontent-sdd-c77 scope=col>Fecha Solicitud</th>
                        <th _ngcontent-sdd-c77 scope=col>N° Resolución</th>
                        <th _ngcontent-sdd-c77 scope=col>Fecha Resolución</th>
                        <th _ngcontent-sdd-c77 scope=col>Plazo (Días)</th>
                        <th _ngcontent-sdd-c77 scope=col>Fecha Vencimiento</th>
                        <th _ngcontent-sdd-c77 scope=col>Acción</th>
                    </tr>
                </thead>
                <tbody _ngcontent-sdd-c77 id="tbodyProrrogas"></tbody>
            </table>
        </div>
    </div>
</div>

<script>
    const INTENTO_ID = <?= json_encode($intentoId) ?>;
    const BASE = <?= json_encode(rtrim(($_ENV['APP_BASE'] ?? getenv('APP_BASE')) ?: '', '/')) ?>;
    let prorrogasItems = <?= json_encode($prorrogasGuardadas, JSON_UNESCAPED_UNICODE) ?>;
    let editIndex = null;

    document.addEventListener('DOMContentLoaded', function () {
        const form = document.querySelector('form');
        const btn = form.querySelector('button[type=submit]');
        const tbody = document.getElementById('tbodyProrrogas');
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

        // ═══ Helper: format date YYYY-MM-DD → DD/MM/YYYY ═══
        function formatDate(dateStr) {
            if (!dateStr) return '';
            var parts = dateStr.split('-');
            if (parts.length !== 3) return dateStr;
            return parts[2] + '/' + parts[1] + '/' + parts[0];
        }

        // ═══ Render table ═══
        function renderTable() {
            const container = document.getElementById('tablaContainerProrrogas');
            tbody.innerHTML = '';

            if (prorrogasItems.length === 0) {
                container.style.display = 'none';
            } else {
                container.style.display = '';
            }

            prorrogasItems.forEach(function (item, idx) {
                const tr = document.createElement('tr');
                tr.setAttribute('_ngcontent-sdd-c77', '');
                tr.innerHTML = `
                <td _ngcontent-sdd-c77>${formatDate(item.fecha_solicitud)}</td>
                <td _ngcontent-sdd-c77>${item.nro_resolucion || ''}</td>
                <td _ngcontent-sdd-c77>${formatDate(item.fecha_resolucion)}</td>
                <td _ngcontent-sdd-c77 style="text-align:center">${item.plazo_dias || ''}</td>
                <td _ngcontent-sdd-c77>${formatDate(item.fecha_vencimiento)}</td>
                <td _ngcontent-sdd-c77>
                    <div _ngcontent-sdd-c77 class=accionesicono>
                        <i _ngcontent-sdd-c77 class="bi bi-pencil-fill" onclick="editarProrroga(${idx})" title="Modificar"></i>&nbsp;
                        <i _ngcontent-sdd-c77 class="bi-trash-fill" onclick="eliminarProrroga(${idx})" title="Eliminar"></i>
                    </div>
                </td>`;
                tbody.appendChild(tr);
            });
        }

        // ═══ Collect form data ═══
        function getFormData() {
            return {
                fecha_solicitud: document.getElementById('fechaSolicitud').value,
                nro_resolucion: document.getElementById('nroResolucion').value,
                fecha_resolucion: document.getElementById('fechaResolucion').value,
                plazo_dias: document.getElementById('plazoDias').value,
                fecha_vencimiento: document.getElementById('fechaVencimiento').value,
            };
        }

        // ═══ Reset form ═══
        function resetForm() {
            document.getElementById('fechaSolicitud').value = '';
            document.getElementById('nroResolucion').value = '';
            document.getElementById('fechaResolucion').value = '';
            document.getElementById('plazoDias').value = '';
            document.getElementById('fechaVencimiento').value = '';
            editIndex = null;
            btn.textContent = 'Guardar ';
            const icon = document.createElement('i');
            icon.className = 'bi-save';
            btn.appendChild(icon);
            btn.disabled = true;
        }

        // ═══ Fill form for editing ═══
        window.editarProrroga = function (idx) {
            const item = prorrogasItems[idx];
            if (!item) return;
            editIndex = idx;

            document.getElementById('fechaSolicitud').value = item.fecha_solicitud || '';
            document.getElementById('nroResolucion').value = item.nro_resolucion || '';
            document.getElementById('fechaResolucion').value = item.fecha_resolucion || '';
            document.getElementById('plazoDias').value = item.plazo_dias || '';
            document.getElementById('fechaVencimiento').value = item.fecha_vencimiento || '';

            btn.textContent = 'Actualizar ';
            const icon = document.createElement('i');
            icon.className = 'bi-save';
            btn.appendChild(icon);

            validateForm();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        };

        // ═══ Delete ═══
        window.eliminarProrroga = function (idx) {
            if (!confirm('¿Está seguro de eliminar esta prórroga?')) return;
            if (!INTENTO_ID) { alert('No hay intento activo'); return; }

            fetch(BASE + '/api/prorrogas/' + INTENTO_ID + '/eliminar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ index: idx })
            })
                .then(r => r.json())
                .then(data => {
                    if (data.ok) {
                        prorrogasItems.splice(idx, 1);
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
                ? BASE + '/api/prorrogas/' + INTENTO_ID + '/editar'
                : BASE + '/api/prorrogas/' + INTENTO_ID + '/agregar';

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
                            prorrogasItems[editIndex] = formData;
                        } else {
                            prorrogasItems.push(formData);
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
