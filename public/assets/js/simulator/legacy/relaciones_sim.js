/**
 * relaciones_sim.js
 * Lógica de tabla de relaciones para el simulador SENIAT.
 * Replica el patrón de direcciones_sim.js.
 */
(function () {
    'use strict';

    // ── Estado en memoria ──
    const relaciones = [];

    // ── Utilidades ──
    function getBaseUrl() {
        return (window.BASE_URL || '/tesis_francisco/public').replace(/\/+$/, '');
    }

    function $(selector) {
        return document.querySelector(selector);
    }

    // ── Persistencia con backend ──
    function guardarBorradorBackend() {
        if (!window.simIntentoId) return;

        var borrador = window.simBorrador || {};
        borrador.relaciones = relaciones;

        var apiUrl = (window.simBaseUrl || '') + '/api/intentos/' + window.simIntentoId + '/guardar';

        fetch(apiUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                borrador: borrador,
                paso_actual: 1
            })
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.ok) {
                window.simBorrador = borrador;
            } else {
                console.error('Error al guardar relaciones en el servidor.');
            }
        })
        .catch(function (error) {
            console.error('Error de red al guardar relaciones:', error);
        });
    }

    function loadSavedRelaciones() {
        var borrador = window.simBorrador;
        if (borrador && Array.isArray(borrador.relaciones)) {
            borrador.relaciones.forEach(function (rel) {
                relaciones.push(rel);
            });
        }
    }

    // ── Elementos del formulario ──
    const el = {
        get apellido() { return $('[name="apellido"]'); },
        get nombre() { return $('[name="nombre"]'); },
        get tipodocumentoRadios() { return document.querySelectorAll('[name="tipodocumento"]'); },
        get cedula() { return $('[name="cedula"]'); },
        get parentesco() { return $('[name="parentesco\\.codigo"]'); },
        get pasaporte() { return $('[name="pasaporte"]'); },
    };

    function getRadioValue(name) {
        const checked = document.querySelector(`input[name="${name}"]:checked`);
        return checked ? checked.value : '';
    }

    function getSelectText(selectEl) {
        if (!selectEl || !selectEl.value) return '';
        const opt = selectEl.querySelector(`option[value="${selectEl.value}"]`);
        return opt ? opt.textContent.trim() : selectEl.value;
    }

    // ══════════════════════════════════════════════════════
    //  Validación onblur — cédula / RIF
    //  Se dispara al salir del campo, igual que el SENIAT
    // ══════════════════════════════════════════════════════
    function validarDocumento() {
        var cedulaEl = el.cedula;
        if (!cedulaEl) return true;
        var val = cedulaEl.value.trim();
        if (!val) return true; // vacío se valida en submit

        var tipodocumento = getRadioValue('tipodocumento');

        if (tipodocumento === 'C') {
            // Cédula: V o E seguido de hasta 8 dígitos
            if (!/^[vVeE]\d{1,8}$/.test(val)) {
                alert('Cedula Inválida. El formato de cédula debe ser vV, eE seguido de ocho dígitos numéricos, ej.: V12345678');
                return false;
            }
        } else {
            // RIF: J, V, G, E, P, C seguido de 9 dígitos
            if (!/^[jJvVgGeEpPcC]\d{9}$/.test(val)) {
                alert('el formato del RIF debe ser vV, eE, pP, jJ, gG, cC, seguido de nueve dígitos numéricos');
                return false;
            }
        }

        // Normalizar a mayúsculas
        cedulaEl.value = val.toUpperCase();
        return true;
    }

    // ══════════════════════════════════════════════════════
    //  Guardar relación (al hacer click en Guardar)
    // ══════════════════════════════════════════════════════
    function saveRelacion() {
        // Prevent double-click silently
        if (window._savingRelacion) return;

        const apellidoForm = el.apellido ? el.apellido.value.trim().toUpperCase() : '';
        const nombreForm = el.nombre ? el.nombre.value.trim().toUpperCase() : '';
        const tipodocumento = getRadioValue('tipodocumento');
        var cedula = el.cedula ? el.cedula.value.trim().toUpperCase() : '';
        const parentescoVal = el.parentesco ? el.parentesco.value : '';
        const parentescoText = getSelectText(el.parentesco);
        const pasaporte = el.pasaporte ? el.pasaporte.value.trim().toUpperCase() : '';

        // ── Validar formato cédula/RIF si tiene valor ──
        if (cedula && !validarDocumento()) return;

        // ── Validaciones de campos obligatorios (solo al Guardar) ──
        var errors = [];
        if (!apellidoForm) errors.push('Debe ingresar información en el campo Apellidos.');
        if (!nombreForm) errors.push('Debe ingresar información en el campo Nombres.');
        if (!cedula && !pasaporte) errors.push('Debe ingresar Cédula/RIF o Pasaporte.');
        if (!parentescoVal) errors.push('Debe ingresar información en el campo Parentesco.');

        if (errors.length > 0) {
            alert(errors.join('\n'));
            return;
        }

        // ── Determinar parámetros de búsqueda ──
        var searchParams = new URLSearchParams();

        if (cedula) {
            if (tipodocumento === 'C') {
                // Extraer solo dígitos para la búsqueda API
                var cedulaDigits = cedula.replace(/^[VE]/i, '');
                searchParams.set('tipo', cedula.charAt(0).toUpperCase());
                searchParams.set('cedula', cedulaDigits);
            } else {
                searchParams.set('rif', cedula);
            }
            searchParams.set('pasaporte', '');
        } else {
            searchParams.set('cedula', '');
            searchParams.set('rif', '');
            searchParams.set('pasaporte', pasaporte);
        }

        window._savingRelacion = true;
        var apiUrl = (window.simBaseUrl || '') + '/api/buscar-persona?' + searchParams.toString();

        fetch(apiUrl, {
            method: 'GET',
            headers: { 'Accept': 'application/json' }
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            var apellido = apellidoForm;
            var nombre = nombreForm;
            var rifPersonal = '';

            if (data.success && data.data) {
                apellido = (data.data.apellidos || '').toUpperCase();
                nombre = (data.data.nombres || '').toUpperCase();
                rifPersonal = (data.data.rif_personal || '').toUpperCase();
            }

            // ── REPRESENTANTE debe tener RIF ──
            var esRepresentante = parentescoVal === '50';
            if (esRepresentante) {
                var rifFinal = rifPersonal || (tipodocumento === 'R' ? cedula : '');
                if (!rifFinal) {
                    alert('El Representante de la Sucesión debe tener RIF.');
                    return;
                }
            }

            // Construir idDocumento — la cédula/RIF ya incluye la letra
            var idDocumentoText = '';
            if (rifPersonal) {
                idDocumentoText = rifPersonal;
            } else if (cedula) {
                idDocumentoText = cedula; // ya tiene V27836650 o J123456789
            } else {
                idDocumentoText = pasaporte;
            }
            var pasaporteFinal = (!cedula && !esRepresentante) ? pasaporte : '';

            var rel = {
                apellido: apellido,
                nombre: nombre,
                tipodocumento: esRepresentante ? 'R' : tipodocumento,
                cedula: cedula,
                parentesco: parentescoVal,
                parentescoText: parentescoText,
                pasaporte: pasaporteFinal,
                idDocumento: idDocumentoText
            };

            // ── Verificar duplicados (mismo documento Y mismo parentesco) ──
            // Una misma persona puede ser Representante (50) y Heredero (51)
            var duplicado = relaciones.some(function (r) {
                return r.idDocumento && r.idDocumento === idDocumentoText
                    && r.parentesco === parentescoVal;
            });
            if (duplicado) {
                alert('Ya existe una relación con ese documento y parentesco.');
                return;
            }

            relaciones.push(rel);

            clearForm();
            renderTable();
            syncParentescoOptions();
            guardarBorradorBackend();
        })
        .catch(function (error) {
            console.error('Error al buscar persona:', error);
            alert('Error al buscar persona en la base de datos.');
        })
        .finally(function () {
            window._savingRelacion = false;
        });
    }

    // ── Renderizar tabla ──
    function renderTable() {
        const existingTable = document.querySelector('table[cellspacing="2"][cellpadding="1"]');
        if (!existingTable) return;

        const tbody = existingTable.querySelector('tbody') || existingTable;

        const oldRows = tbody.querySelectorAll('tr.letrasLista, tr.rel-empty');
        oldRows.forEach(row => row.remove());

        const headerRow = tbody.querySelector('tr:first-child');

        const btnRemover = $('#remover');
        if (btnRemover) btnRemover.style.display = relaciones.length > 0 ? '' : 'none';

        if (relaciones.length === 0) {
            const emptyRow = document.createElement('tr');
            emptyRow.className = 'rel-empty';
            emptyRow.innerHTML = '<td colspan="6" style="FONT-SIZE:7pt;HEIGHT:20px;BACKGROUND-COLOR:#D7D7D7;text-align:center;font-family:Verdana,Arial;">No existen relaciones cargadas</td>';
            if (headerRow) {
                headerRow.after(emptyRow);
            } else {
                tbody.appendChild(emptyRow);
            }
            return;
        }

        const fragment = document.createDocumentFragment();
        relaciones.forEach((rel, i) => {
            const row = document.createElement('tr');
            row.className = 'letrasLista';
            row.setAttribute('align', 'center');
            row.style.cursor = 'default';
            row.innerHTML = `
                <td width="5%" style="FONT-SIZE:7pt;HEIGHT:20px;BACKGROUND-COLOR:#D7D7D7">
                    <div align="center">
                        <input type="checkbox" class="rel-check" data-index="${i}">
                    </div>
                </td>
                <td width="25%" style="FONT-SIZE:7pt;HEIGHT:20px;BACKGROUND-COLOR:#D7D7D7">${rel.parentescoText}</td>
                <td width="25%" style="FONT-SIZE:7pt;HEIGHT:20px;BACKGROUND-COLOR:#D7D7D7">${rel.nombre}</td>
                <td width="25%" style="FONT-SIZE:7pt;HEIGHT:20px;BACKGROUND-COLOR:#D7D7D7">${rel.apellido}</td>
                <td width="10%" style="FONT-SIZE:7pt;HEIGHT:20px;BACKGROUND-COLOR:#D7D7D7">${!rel.pasaporte ? rel.idDocumento : ''}</td>
                <td width="10%" style="FONT-SIZE:7pt;HEIGHT:20px;BACKGROUND-COLOR:#D7D7D7">${rel.pasaporte || ''}</td>
            `;
            fragment.appendChild(row);
        });

        if (headerRow) {
            headerRow.after(fragment);
        } else {
            tbody.appendChild(fragment);
        }
    }

    // ── Sincronizar opciones de parentesco ──
    function syncParentescoOptions() {
        const selectEl = el.parentesco;
        if (!selectEl) return;

        const tieneRepresentante = relaciones.some(r => r.parentesco === '50');
        const opt50 = selectEl.querySelector('option[value="50"]');
        if (!opt50) return;

        if (tieneRepresentante) {
            opt50.disabled = true;
            opt50.style.display = 'none';
            if (selectEl.value === '50') selectEl.value = '';
        } else {
            opt50.disabled = false;
            opt50.style.display = '';
        }
    }

    // ── Remover relaciones seleccionadas ──
    function removeRelaciones() {
        const checks = document.querySelectorAll('.rel-check:checked');
        if (checks.length === 0) return;

        const indices = Array.from(checks).map(c => parseInt(c.dataset.index)).sort((a, b) => b - a);
        indices.forEach(i => relaciones.splice(i, 1));

        guardarBorradorBackend();
        renderTable();
        syncParentescoOptions();
    }

    // ── Limpiar formulario ──
    function clearForm() {
        if (el.apellido) el.apellido.value = '';
        if (el.nombre) el.nombre.value = '';
        if (el.cedula) el.cedula.value = '';
        if (el.parentesco) el.parentesco.value = '';
        if (el.pasaporte) el.pasaporte.value = '';

        el.tipodocumentoRadios.forEach(r => {
            r.checked = (r.value === 'C');
        });
    }

    // ── Interceptar submit nativo y botones ──
    function initFormInterception() {
        const form = document.querySelector('form[name="CargaFamiliarForm"]');
        if (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                saveRelacion();
            });
        }

        const btnRemover = $('#remover');
        if (btnRemover) {
            btnRemover.type = 'button';
            btnRemover.addEventListener('click', function (e) {
                e.preventDefault();
                removeRelaciones();
            });
        }

        const btnGuardar = $('#guardar');
        if (btnGuardar) {
            btnGuardar.addEventListener('click', function (e) {
                e.preventDefault();
                saveRelacion();
            });
        }

        const btnReset = $('#reestablecer');
        if (btnReset) {
            btnReset.type = 'button';
            btnReset.addEventListener('click', function (e) {
                e.preventDefault();
                clearForm();
            });
        }

        // ── Validación onblur en campo cédula/RIF (estilo SENIAT original) ──
        var cedulaField = el.cedula;
        if (cedulaField) {
            cedulaField.addEventListener('blur', function () {
                validarDocumento();
            });
        }

        // Re-validar al cambiar tipo doc (Cédula ↔ RIF) si ya tiene valor
        el.tipodocumentoRadios.forEach(function (radio) {
            radio.addEventListener('change', function () {
                if (el.cedula && el.cedula.value.trim()) {
                    validarDocumento();
                }
            });
        });
    }

    // ── Inicialización ──
    function init() {
        initFormInterception();
        loadSavedRelaciones();
        renderTable();
        syncParentescoOptions();
    }

    window.SimRel = {
        remove: removeRelaciones,
        save: saveRelacion
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
