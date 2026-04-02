<?php
/**
 * Herederos Premuerto — Manages sub-heirs of premuerto heirs
 * Uses: sim_sucesiones_layout.php
 *
 * Receives from route: $intento (array with id, borrador_json, etc.)
 * JSON structure:
 *   relaciones[] — complete list of heirs (premuertos have premuerto === "Si")
 *   herederos_premuertos[] — sub-heirs, each with premuerto_padre_id referencing parent's cedula
 */
$activeMenu = 'herederos_premuerto';
$activeItem = 'Herederos Premuerto';
$extraCss = [
    '/assets/css/simulator/seniat_actual/sucesion/herencia/tipo_herencia.css',
];
$breadcrumbs = [
    ['label' => 'Inicio', 'url' => '/simulador/servicios_declaracion/dashboard'],
    ['label' => 'Identificación Herederos'],
    ['label' => 'Heredero Premuerto en Representación'],
];

// ─── Extract data from borrador_json ───
$borradorRaw  = $intento['borrador_json'] ?? '{}';
$borrador     = json_decode($borradorRaw, true) ?: [];
$relaciones   = $borrador['relaciones'] ?? [];
$herederosPremuertos = $borrador['herederos_premuertos'] ?? [];
$intentoId    = $intento['id'] ?? null;

// Filter: only relaciones with premuerto === "Si"
$premuertos = [];
foreach ($relaciones as $idx => $r) {
    if (($r['premuerto'] ?? '') === 'Si') {
        $nombre = strtoupper(trim(($r['apellido'] ?? '') . ' ' . ($r['nombre'] ?? '')));
        $cedula = $r['idDocumento'] ?? (($r['tipodocumento'] ?? '') . ($r['cedula'] ?? ''));
        $premuertos[] = [
            'idx'     => $idx,
            'nombre'  => $nombre,
            'cedula'  => $r['cedula'] ?? '',
            'idDoc'   => $cedula,
        ];
    }
}

// Parentesco catalog
$catalogoParentescos = [
    1 => 'Hija/Hijo', 2 => 'Nieta/Nieto', 3 => 'Bisnieta/Bisnieto',
    4 => 'Madre', 5 => 'Padre', 6 => 'Abuela/Abuelo',
    7 => 'Hija/Hijo Adoptiva', 8 => 'Cónyuge', 9 => 'Concubina',
    10 => 'Hermana(o) Simple', 11 => 'Hermana(o) Doble',
    12 => 'Tia/Tio', 13 => 'Sobrina/Sobrino',
    14 => 'Prima/Primo Segundo', 15 => 'Otro pariente',
    16 => 'Extraño', 17 => 'Prima/Primo', 18 => 'Otro', 19 => 'Sin definir',
];

ob_start();
?>

<div _ngcontent-pgi-c76 class="shadow p-3 mb-5 bg-white rounded lenletra" data-sub-herederos-count="<?= count($herederosPremuertos) ?>">
    <div _ngcontent-pgi-c76 class=card>
        <div _ngcontent-pgi-c76 class=card-header>IDENTIFICACIÓN DE HEREDEROS PREMUERTO EN REPRESENTACIÓN</div>
        <div _ngcontent-pgi-c76 class=card-body>
            <table _ngcontent-pgi-c76 class="table table-bordered">
                <thead _ngcontent-pgi-c76>
                    <tr _ngcontent-pgi-c76 class=table-secondary>
                        <th _ngcontent-pgi-c76 scope=col class=text-start>PREMUERTO
                        <th _ngcontent-pgi-c76 scope=col class=text-start>VER HEREDEROS
                    </tr>
                </thead>
                <tbody _ngcontent-pgi-c76 id="tbodyPremuertos">
                    <!-- Rendered by JS -->
                </tbody>
            </table>

            <!-- Expandable section per premuerto -->
            <div id="detalleHerederos" style="display:none">
                <p _ngcontent-pgi-c76><strong> Herederos del Premuerto:&nbsp;</strong><span id="nombrePremuerto"></span> &nbsp;<i class=bi-check-lg></i></p>
                <div><button type=button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAgregarHeredero">Agregar Heredero</button></div><br>
                <table _ngcontent-pgi-c76 class="table table-bordered table-sm">
                    <thead _ngcontent-pgi-c76>
                        <tr class=table-secondary>
                            <th scope=col class=text-start>APELLIDOS Y NOMBRES
                            <th scope=col class=text-start>CEDULA DE IDENTIDAD
                            <th scope=col class=text-start>CARACTER
                            <th scope=col class=text-start>FECHA DE NACIMIENTO
                            <th scope=col class=text-start>PARENTESCO
                            <th scope=col class=text-start>ACCIÓN
                        </tr>
                    </thead>
                    <tbody id="tbodySubHerederos">
                        <!-- Rendered by JS -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Agregar Heredero -->
<div class="modal fade" id="modalAgregarHeredero" tabindex="-1" aria-labelledby="modalAgregarHerederoLabel" aria-modal="true" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAgregarHerederoLabel">Agregar Heredero</h5>
                <button type="button" class="btn btn-light close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div role="alert" class="alert alert-info text-justify">
                    <div style="text-align:justify;font-size:14px"><strong>Tome en cuenta que al agregar un heredero sin documento de identificación, debe colocar cero(0) en el campo cédula .</strong></div>
                </div>
                <form novalidate id="formAgregar">
                    <div class="row"><div class="col-sm-12"><div class="form-group">
                        <div class="form-floating"><input id="add_nom" type="text" maxlength="80" required class="form-control form-control-sm"><label for="add_nom">Nombre</label></div>
                    </div></div></div><br>
                    <div class="row"><div class="col-sm-12"><div class="form-group">
                        <div class="form-floating"><input id="add_ap" type="text" maxlength="80" required class="form-control form-control-sm"><label for="add_ap">Apellido</label></div>
                    </div></div></div><br>
                    <div class="row"><div class="col-sm-12"><div class="form-group">
                        <div class="form-floating"><input id="add_ced" type="text" required class="form-control form-control-sm"><label for="add_ced">Cédula</label></div>
                    </div></div></div><br>
                    <div class="row"><div class="col-sm-12"><div class="form-group">
                        <label for="add_fn">Fecha de Nacimiento</label>
                        <div class="input-group"><input id="add_fn" placeholder="Seleccione Fecha" type="text" ngbdatepicker required class="form-control form-control-sm"><i class="bi bi-calendar3 btn btn-outline-secondary"></i></div>
                    </div></div></div><br>
                    <div class="row"><div class="col-sm-12"><div class="form-group">
                        <div class="form-floating"><select id="add_par" required class="form-select">
                            <?php foreach ($catalogoParentescos as $id => $label): ?>
                                <option value="<?= $id ?>"><?= htmlspecialchars($label) ?></option>
                            <?php endforeach; ?>
                        </select><label for="add_par">Parentesco</label></div>
                    </div></div></div><br>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" id="btnAgregarHeredero" class="btn btn-sm btn-primary" disabled>Agregar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Heredero -->
<div class="modal fade" id="modalEditarHeredero" tabindex="-1" aria-labelledby="modalEditarHerederoLabel" aria-modal="true" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditarHerederoLabel">Modificar Heredero</h5>
                <button type="button" class="btn btn-light close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div role="alert" class="alert alert-info text-justify">
                    <div style="text-align:justify;font-size:14px"><strong>Tome en cuenta que al agregar un heredero sin documento de identificación, debe colocar cero(0) en el campo cédula .</strong></div>
                </div>
                <form novalidate id="formEditar">
                    <input type="hidden" id="edit_idx">
                    <div class="row"><div class="col-sm-12"><div class="form-group">
                        <div class="form-floating"><input id="edit_nom" type="text" maxlength="80" required class="form-control form-control-sm"><label for="edit_nom">Nombre</label></div>
                    </div></div></div><br>
                    <div class="row"><div class="col-sm-12"><div class="form-group">
                        <div class="form-floating"><input id="edit_ap" type="text" maxlength="80" required class="form-control form-control-sm"><label for="edit_ap">Apellido</label></div>
                    </div></div></div><br>
                    <div class="row"><div class="col-sm-12"><div class="form-group">
                        <div class="form-floating"><input id="edit_ced" type="text" required class="form-control form-control-sm"><label for="edit_ced">Cédula</label></div>
                    </div></div></div><br>
                    <div class="row"><div class="col-sm-12"><div class="form-group">
                        <label for="edit_fn">Fecha de Nacimiento</label>
                        <div class="input-group"><input id="edit_fn" placeholder="Seleccione Fecha" type="text" ngbdatepicker required class="form-control form-control-sm"><i class="bi bi-calendar3 btn btn-outline-secondary"></i></div>
                    </div></div></div><br>
                    <div class="row"><div class="col-sm-12"><div class="form-group">
                        <div class="form-floating"><select id="edit_par" required class="form-select">
                            <?php foreach ($catalogoParentescos as $id => $label): ?>
                                <option value="<?= $id ?>"><?= htmlspecialchars($label) ?></option>
                            <?php endforeach; ?>
                        </select><label for="edit_par">Parentesco</label></div>
                    </div></div></div><br>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" id="btnGuardarHeredero" class="btn btn-sm btn-primary" disabled>Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    'use strict';

    var BASE_URL   = '<?= rtrim(base_url(), "/") ?>';
    var INTENTO_ID = <?= json_encode($intentoId) ?>;
    var PREMUERTOS = <?= json_encode(array_values($premuertos), JSON_UNESCAPED_UNICODE) ?>;
    var PARENTESCOS = <?= json_encode($catalogoParentescos, JSON_UNESCAPED_UNICODE) ?>;
    var herederosPremuertos = <?= json_encode(array_values($herederosPremuertos), JSON_UNESCAPED_UNICODE) ?>;

    var selectedPadreId = null; // cedula of the currently viewed premuerto

    // ─── Render premuertos table ───
    function renderPremuertos() {
        var tbody = document.getElementById('tbodyPremuertos');
        if (!PREMUERTOS.length) {
            tbody.innerHTML = '<tr><td colspan="2" class="text-center">No hay herederos premuertos registrados en la sección de Herederos</td></tr>';
            return;
        }
        var html = '';
        PREMUERTOS.forEach(function(p) {
            html += '<tr>';
            html += '<td>' + p.nombre + '</td>';
            html += '<td><div class="accionesicono"><i class="bi-search" style="cursor:pointer" data-padre-cedula="' + p.cedula + '" data-padre-nombre="' + p.nombre + '"></i></div></td>';
            html += '</tr>';
        });
        tbody.innerHTML = html;

        // Bind lupa clicks
        tbody.querySelectorAll('[data-padre-cedula]').forEach(function(icon) {
            icon.addEventListener('click', function() {
                selectedPadreId = this.getAttribute('data-padre-cedula');
                document.getElementById('nombrePremuerto').textContent = this.getAttribute('data-padre-nombre');
                document.getElementById('detalleHerederos').style.display = 'block';
                renderSubHerederos();
            });
        });
    }

    // ─── Render sub-herederos for the selected premuerto ───
    function renderSubHerederos() {
        var tbody = document.getElementById('tbodySubHerederos');
        var filtered = [];
        herederosPremuertos.forEach(function(h, globalIdx) {
            if (h.premuerto_padre_id === selectedPadreId) {
                filtered.push({ data: h, globalIdx: globalIdx });
            }
        });

        if (!filtered.length) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center">No hay herederos registrados para este premuerto</td></tr>';
            return;
        }

        var html = '';
        filtered.forEach(function(item) {
            var h = item.data;
            var parentescoText = PARENTESCOS[h.parentesco_id] || 'Sin definir';
            var fechaNac = h.fecha_nacimiento || '';
            // Format dd/mm/yyyy if ISO
            if (fechaNac && fechaNac.indexOf('-') !== -1) {
                var parts = fechaNac.split('-');
                fechaNac = parts[2] + '/' + parts[1] + '/' + parts[0];
            }
            html += '<tr>';
            html += '<td>' + (h.apellido || '').toUpperCase() + ' ' + (h.nombre || '').toUpperCase() + '</td>';
            html += '<td>' + (h.cedula || '') + '</td>';
            html += '<td>Heredero</td>';
            html += '<td>' + fechaNac + '</td>';
            html += '<td>' + parentescoText.toUpperCase() + '</td>';
            html += '<td><div class="accionesicono">';
            html += '<i class="bi bi-pencil-fill" style="cursor:pointer" data-edit-global="' + item.globalIdx + '"></i>&nbsp; ';
            html += '<i class="bi-trash-fill" style="cursor:pointer" data-delete-global="' + item.globalIdx + '"></i>';
            html += '</div></td>';
            html += '</tr>';
        });
        tbody.innerHTML = html;

        // Bind edit
        tbody.querySelectorAll('[data-edit-global]').forEach(function(icon) {
            icon.addEventListener('click', function() {
                var idx = parseInt(this.getAttribute('data-edit-global'));
                abrirEditModal(idx);
            });
        });
        // Bind delete
        tbody.querySelectorAll('[data-delete-global]').forEach(function(icon) {
            icon.addEventListener('click', function() {
                var idx = parseInt(this.getAttribute('data-delete-global'));
                eliminarHeredero(idx);
            });
        });
    }

    // ─── Add modal validation ───
    var addFields = ['add_nom', 'add_ap', 'add_ced', 'add_fn'];
    var addSelect = document.getElementById('add_par');
    var btnAgregar = document.getElementById('btnAgregarHeredero');

    function checkAddFields() {
        var filled = addFields.every(function(id) { return document.getElementById(id).value.trim() !== ''; });
        btnAgregar.disabled = !(filled && addSelect.value);
    }
    addFields.forEach(function(id) { document.getElementById(id).addEventListener('input', checkAddFields); });
    addSelect.addEventListener('change', checkAddFields);

    // ─── Add form submit ───
    document.getElementById('formAgregar').addEventListener('submit', function(e) {
        e.preventDefault();
        if (btnAgregar.disabled || !INTENTO_ID) return;

        btnAgregar.disabled = true;
        btnAgregar.textContent = 'Guardando...';

        var payload = {
            nombre: document.getElementById('add_nom').value.trim(),
            apellido: document.getElementById('add_ap').value.trim(),
            cedula: document.getElementById('add_ced').value.trim() === '0' ? '' : document.getElementById('add_ced').value.trim(),
            fecha_nacimiento: document.getElementById('add_fn').value.trim(),
            parentesco_id: parseInt(addSelect.value),
            premuerto_padre_id: selectedPadreId
        };

        fetch(BASE_URL + '/api/herederos-premuertos/' + INTENTO_ID + '/agregar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.ok) {
                herederosPremuertos.push(payload);
                renderSubHerederos();
                // Reset form
                addFields.forEach(function(id) { document.getElementById(id).value = ''; });
                addSelect.value = '19';
                // Close modal
                var modal = bootstrap.Modal.getInstance(document.getElementById('modalAgregarHeredero'));
                if (modal) modal.hide();
            } else {
                alert('Error al agregar: ' + (data.error || 'Error desconocido'));
            }
        })
        .catch(function(err) { alert('Error de conexión: ' + err.message); })
        .finally(function() {
            btnAgregar.disabled = false;
            btnAgregar.textContent = 'Agregar';
            checkAddFields();
        });
    });

    // ─── Edit modal ───
    var editFields = ['edit_nom', 'edit_ap', 'edit_ced', 'edit_fn'];
    var editSelect = document.getElementById('edit_par');
    var btnGuardar = document.getElementById('btnGuardarHeredero');

    function checkEditFields() {
        var filled = editFields.every(function(id) { return document.getElementById(id).value.trim() !== ''; });
        btnGuardar.disabled = !(filled && editSelect.value);
    }
    editFields.forEach(function(id) { document.getElementById(id).addEventListener('input', checkEditFields); });
    editSelect.addEventListener('change', checkEditFields);

    function abrirEditModal(globalIdx) {
        var h = herederosPremuertos[globalIdx];
        if (!h) return;
        document.getElementById('edit_idx').value = globalIdx;
        document.getElementById('edit_nom').value = h.nombre || '';
        document.getElementById('edit_ap').value = h.apellido || '';
        document.getElementById('edit_ced').value = h.cedula || '';
        document.getElementById('edit_fn').value = h.fecha_nacimiento || '';
        editSelect.value = h.parentesco_id || '19';
        checkEditFields();
        var modal = new bootstrap.Modal(document.getElementById('modalEditarHeredero'));
        modal.show();
    }

    document.getElementById('formEditar').addEventListener('submit', function(e) {
        e.preventDefault();
        if (btnGuardar.disabled || !INTENTO_ID) return;

        var globalIdx = parseInt(document.getElementById('edit_idx').value);
        btnGuardar.disabled = true;
        btnGuardar.textContent = 'Guardando...';

        var payload = {
            index: globalIdx,
            nombre: document.getElementById('edit_nom').value.trim(),
            apellido: document.getElementById('edit_ap').value.trim(),
            cedula: document.getElementById('edit_ced').value.trim() === '0' ? '' : document.getElementById('edit_ced').value.trim(),
            fecha_nacimiento: document.getElementById('edit_fn').value.trim(),
            parentesco_id: parseInt(editSelect.value)
        };

        fetch(BASE_URL + '/api/herederos-premuertos/' + INTENTO_ID + '/editar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.ok) {
                // Update local data
                herederosPremuertos[globalIdx].nombre = payload.nombre;
                herederosPremuertos[globalIdx].apellido = payload.apellido;
                herederosPremuertos[globalIdx].cedula = payload.cedula;
                herederosPremuertos[globalIdx].fecha_nacimiento = payload.fecha_nacimiento;
                herederosPremuertos[globalIdx].parentesco_id = payload.parentesco_id;
                renderSubHerederos();
                var modal = bootstrap.Modal.getInstance(document.getElementById('modalEditarHeredero'));
                if (modal) modal.hide();
            } else {
                alert('Error al guardar: ' + (data.error || 'Error desconocido'));
            }
        })
        .catch(function(err) { alert('Error de conexión: ' + err.message); })
        .finally(function() {
            btnGuardar.disabled = false;
            btnGuardar.textContent = 'Guardar';
            checkEditFields();
        });
    });

    // ─── Delete ───
    function eliminarHeredero(globalIdx) {
        if (!confirm('¿Está seguro de eliminar este heredero?')) return;
        if (!INTENTO_ID) return;

        fetch(BASE_URL + '/api/herederos-premuertos/' + INTENTO_ID + '/eliminar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ index: globalIdx })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.ok) {
                herederosPremuertos.splice(globalIdx, 1);
                renderSubHerederos();
            } else {
                alert('Error al eliminar: ' + (data.error || 'Error desconocido'));
            }
        })
        .catch(function(err) { alert('Error de conexión: ' + err.message); });
    }

    // ─── Initial render ───
    renderPremuertos();
})();
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../../../layouts/sim_sucesiones_layout.php';
?>