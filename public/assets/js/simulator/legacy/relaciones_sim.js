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

    function showToast(msg) {
        alert(msg);
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

    // ── Guardar relación ──
    function saveRelacion() {
        const apellidoForm = el.apellido ? el.apellido.value.trim().toUpperCase() : '';
        const nombreForm = el.nombre ? el.nombre.value.trim().toUpperCase() : '';
        const tipodocumento = getRadioValue('tipodocumento');
        const cedula = el.cedula ? el.cedula.value.trim().toUpperCase() : '';
        const parentescoVal = el.parentesco ? el.parentesco.value : '';
        const parentescoText = getSelectText(el.parentesco);
        const pasaporte = el.pasaporte ? el.pasaporte.value.trim().toUpperCase() : '';

        // ── Validaciones (todos obligatorios, sin avisos) ──
        if (!apellidoForm || !nombreForm) return;
        if (!cedula && !pasaporte) return;
        if (!parentescoVal) return;

        // ── Determinar parámetros de búsqueda ──
        // Prioridad: cédula/rif sobre pasaporte
        var searchParams = new URLSearchParams();

        if (cedula) {
            // Si hay cédula o RIF, priorizar sobre pasaporte
            if (tipodocumento === 'C') {
                searchParams.set('tipo', 'V');
                searchParams.set('cedula', cedula);
            } else {
                // RIF
                searchParams.set('rif', cedula);
            }
            searchParams.set('pasaporte', '');
        } else {
            // Solo pasaporte
            searchParams.set('cedula', '');
            searchParams.set('rif', '');
            searchParams.set('pasaporte', pasaporte);
        }

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
                // Match en la DB: usar nombres de la base de datos
                apellido = (data.data.apellidos || '').toUpperCase();
                nombre = (data.data.nombres || '').toUpperCase();
                rifPersonal = (data.data.rif_personal || '').toUpperCase();
            } else {
                // Sin match: usar los nombres del formulario (ya validados arriba)
            }

            // ── Validación especial: REPRESENTANTE DE LA SUCESION debe tener RIF ──
            var esRepresentante = parentescoVal === '50';
            if (esRepresentante) {
                // El RIF puede venir de la DB o del formulario (si tipodocumento es RIF)
                var rifFinal = rifPersonal || (tipodocumento === 'R' ? cedula : '');
                if (!rifFinal) {
                    showToast('El Representante de la Sucesión debe tener RIF.');
                    return;
                }
            }

            // Construir idDocumento — siempre priorizar RIF de la DB
            var idDocumentoText = '';
            if (rifPersonal) {
                // La DB tiene RIF: usarlo siempre
                idDocumentoText = rifPersonal;
            } else if (cedula) {
                if (tipodocumento === 'C') {
                    idDocumentoText = 'V' + cedula;
                } else {
                    idDocumentoText = cedula; // RIF del formulario
                }
            } else {
                idDocumentoText = pasaporte;
            }
            // Pasaporte solo se guarda si es el único documento ingresado
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
            // ── Verificar duplicados por documento ──
            var duplicado = relaciones.some(function (r) {
                return r.idDocumento && r.idDocumento === idDocumentoText;
            });
            if (duplicado) {
                showToast('Ya existe una relación con ese documento.');
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
            showToast('Error al buscar persona en la base de datos.');
        });
    }

    // ── Renderizar tabla ──
    function renderTable() {
        const existingTable = document.querySelector('table[cellspacing="2"][cellpadding="1"]');
        if (!existingTable) return;

        const tbody = existingTable.querySelector('tbody') || existingTable;

        // Eliminar filas de datos previas (estáticas y dinámicas)
        const oldRows = tbody.querySelectorAll('tr.letrasLista, tr.rel-empty');
        oldRows.forEach(row => row.remove());

        // Fila del header
        const headerRow = tbody.querySelector('tr:first-child');

        // Mostrar/ocultar botón Remover según haya relaciones
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

        // Crear fragmento con todas las filas
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
    // Si ya existe un REPRESENTANTE DE LA SUCESION en la lista,
    // ocultar esa opción del select para que no se pueda agregar otro.
    function syncParentescoOptions() {
        const selectEl = el.parentesco;
        if (!selectEl) return;

        const tieneRepresentante = relaciones.some(r => r.parentesco === '50');
        const opt50 = selectEl.querySelector('option[value="50"]');
        if (!opt50) return;

        if (tieneRepresentante) {
            opt50.disabled = true;
            opt50.style.display = 'none';
            // Si estaba seleccionado, resetear
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

        // Obtener índices en orden descendente para no alterar los índices al eliminar
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
            if (r.value === 'C') {
                r.checked = true;
            } else {
                r.checked = false;
            }
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
