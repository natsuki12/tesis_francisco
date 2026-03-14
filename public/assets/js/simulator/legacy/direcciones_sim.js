/**
 * direcciones_sim.js
 * Lógica de cascadas y tabla de direcciones para el simulador SENIAT.
 * Replica el patrón de crear_caso.php → direccion.js adaptado al form legacy.
 */
(function () {
    'use strict';

    // ── Estado en memoria ──
    const direcciones = [];
    let editIndex = null;

    // ── Utilidades ──
    function getBaseUrl() {
        return (window.BASE_URL || '/tesis_francisco/public').replace(/\/+$/, '');
    }

    function $(selector) {
        return document.querySelector(selector);
    }

    function showToast(msg) {
        alert(msg); // sencillo para el contexto legacy
    }

    // ── Persistencia con backend ──
    function guardarBorradorBackend() {
        if (!window.simIntentoId) return;

        var borrador = window.simBorrador || {};
        borrador.direcciones = direcciones;

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
                console.error('Error al guardar direcciones en el servidor.');
            }
        })
        .catch(function (error) {
            console.error('Error de red al guardar direcciones:', error);
        });
    }

    function loadSavedDirecciones() {
        var borrador = window.simBorrador;
        if (borrador && Array.isArray(borrador.direcciones)) {
            borrador.direcciones.forEach(function (dir) {
                direcciones.push(dir);
            });
        }
    }

    // ── Carga genérica de selects ──
    const abortControllers = {};

    function fetchSelect(url, type, selectEl, defaultText) {
        if (!selectEl) return Promise.resolve();

        if (abortControllers[type]) abortControllers[type].abort();
        abortControllers[type] = new AbortController();
        const signal = abortControllers[type].signal;

        selectEl.innerHTML = '<option value="">Cargando...</option>';
        selectEl.disabled = true;

        return fetch(url, { signal })
            .then(r => r.json())
            .then(result => {
                if (result.success && result.data) {
                    const isZona = type === 'zonas';
                    const opts = result.data.map(item =>
                        `<option value="${item.id}">${isZona ? item.codigo : item.nombre}</option>`
                    ).join('');
                    selectEl.innerHTML = `<option value="">${defaultText}</option>` + opts;
                } else {
                    selectEl.innerHTML = `<option value="">${defaultText}</option>`;
                }
            })
            .catch(err => {
                if (err.name === 'AbortError') return;
                console.error(`Error cargando ${type}:`, err);
                selectEl.innerHTML = '<option value="">Error al cargar</option>';
            })
            .finally(() => {
                if (!signal.aborted) selectEl.disabled = false;
            });
    }

    function resetSelect(selectEl, defaultText) {
        if (!selectEl) return;
        selectEl.innerHTML = `<option value="">${defaultText}</option>`;
        selectEl.value = '';
        selectEl.disabled = false;
    }

    // ── Elementos del formulario ──
    const el = {
        get tipoDireccion() { return $('[name="tipoDireccion.codigo"]'); },
        get estado() { return $('[name="estado.codigo"]'); },
        get municipio() { return $('[name="municipio.codigo"]'); },
        get parroquia() { return $('[name="parroquia.codigo"]'); },
        get ciudad() { return $('[name="ciudad.codigo"]'); },
        get zonaPostal() { return $('[name="zonaPostal"]'); },
        get vialidadDesc() { return $('[name="vialidad.descripcion"]'); },
        get edificacionDesc() { return $('[name="edificacion.descripcion"]'); },
        get piso() { return $('[name="piso"]'); },
        get localDesc() { return $('[name="local.descripcion"]'); },
        get sectorDesc() { return $('[name="sector.descripcion"]'); },
        get telefono() { return $('[name="telefono"]'); },
        get celular() { return $('[name="telefonoCelular"]'); },
        get fax() { return $('[name="fax"]'); },
        get referencia() { return $('[name="referencia"]'); },
    };

    // ── Funciones de cascada ──
    function loadEstados() {
        const base = getBaseUrl();
        return fetchSelect(`${base}/api/estados`, 'estados', el.estado, 'SELECCIONAR');
    }

    function loadMunicipios(estadoId) {
        if (!estadoId) { resetSelect(el.municipio, 'SELECCIONAR'); return; }
        const base = getBaseUrl();
        fetchSelect(`${base}/api/municipios?estado_id=${estadoId}`, 'municipios', el.municipio, 'SELECCIONAR');
    }

    function loadParroquias(municipioId) {
        if (!municipioId) { resetSelect(el.parroquia, 'SELECCIONAR'); return; }
        const base = getBaseUrl();
        fetchSelect(`${base}/api/parroquias?municipio_id=${municipioId}`, 'parroquias', el.parroquia, 'SELECCIONAR');
    }

    function loadCiudades(municipioId) {
        if (!municipioId) { resetSelect(el.ciudad, 'SELECCIONAR'); return; }
        const base = getBaseUrl();
        fetchSelect(`${base}/api/ciudades?municipio_id=${municipioId}`, 'ciudades', el.ciudad, 'SELECCIONAR');
    }

    function loadZonasPostales(estadoId) {
        if (!estadoId) { resetSelect(el.zonaPostal, 'SELECCIONAR'); return; }
        const base = getBaseUrl();
        fetchSelect(`${base}/api/zonas-postales?estado_id=${estadoId}`, 'zonas', el.zonaPostal, 'SELECCIONAR');
    }

    // ── Listeners de cascada ──
    function initCascadeListeners() {
        if (el.estado) {
            el.estado.addEventListener('change', function () {
                const id = this.value;
                resetSelect(el.municipio, 'SELECCIONAR');
                resetSelect(el.parroquia, 'SELECCIONAR');
                resetSelect(el.ciudad, 'SELECCIONAR');
                resetSelect(el.zonaPostal, 'SELECCIONAR');
                if (id) {
                    loadMunicipios(id);
                    loadZonasPostales(id);
                }
            });
        }

        if (el.municipio) {
            el.municipio.addEventListener('change', function () {
                const id = this.value;
                resetSelect(el.parroquia, 'SELECCIONAR');
                resetSelect(el.ciudad, 'SELECCIONAR');
                if (id) {
                    loadParroquias(id);
                    loadCiudades(id);
                }
            });
        }
    }

    // ── Lógica condicional de tipo inmueble ──
    function initEdificacionLogic() {
        const radios = document.querySelectorAll('input[name="radioedificacion"]');
        radios.forEach(r => {
            r.addEventListener('change', function () {
                const val = this.value;
                const radiosLocal = document.querySelectorAll('input[name="radiolocal"]');
                const localInput = el.localDesc;
                const pisoInput = el.piso;
                const labelInput = $('[name="label"]');

                // edificio (01) o centro comercial (02) → habilitar local/apto/oficina
                if (val === '01' || val === '02') {
                    radiosLocal.forEach(rl => rl.disabled = false);
                    if (localInput) {
                        localInput.disabled = false;
                        if (localInput.value === 'NO APLICA') localInput.value = '';
                    }
                    if (labelInput) labelInput.value = val === '01' ? 'Piso' : 'Nivel';
                } else {
                    // quinta (03), casa (04), local (05) → deshabilitar
                    radiosLocal.forEach(rl => {
                        rl.disabled = true;
                        rl.checked = false;
                    });
                    if (localInput) {
                        localInput.value = 'NO APLICA';
                        localInput.disabled = true;
                    }
                    if (labelInput) labelInput.value = 'Nro';
                }
            });
        });
    }

    // ── Formato de teléfono ──
    function initPhoneFormat() {
        [el.telefono, el.celular, el.fax].forEach(input => {
            if (!input) return;
            input.maxLength = 12;
            input.addEventListener('input', function () {
                let digits = this.value.replace(/\D/g, '');
                if (digits.length > 11) digits = digits.slice(0, 11);
                this.value = digits.length > 4
                    ? digits.slice(0, 4) + '-' + digits.slice(4)
                    : digits;
            });
        });
    }

    // ── Leer radio seleccionado ──
    function getRadioValue(name) {
        const checked = document.querySelector(`input[name="${name}"]:checked`);
        return checked ? checked.value : '';
    }

    function getRadioLabel(name) {
        const checked = document.querySelector(`input[name="${name}"]:checked`);
        if (!checked) return '';
        // El texto está justo después del input como nodo de texto
        const next = checked.nextSibling;
        return next ? next.textContent.trim() : checked.value;
    }

    function getSelectText(selectEl) {
        if (!selectEl || !selectEl.value) return '';
        const opt = selectEl.querySelector(`option[value="${selectEl.value}"]`);
        return opt ? opt.textContent.trim() : selectEl.value;
    }

    // ── Guardar dirección ──
    function saveDireccion() {
        const tipoDir = el.tipoDireccion ? el.tipoDireccion.value : '';
        const tipoDirText = el.tipoDireccion ? getSelectText(el.tipoDireccion) : '';
        const tipoVialidad = getRadioValue('radiovialidad');
        const tipoVialidadLabel = getRadioLabel('radiovialidad');
        const tipoEdificacion = getRadioValue('radioedificacion');
        const tipoEdificacionLabel = getRadioLabel('radioedificacion');
        const tipoLocal = getRadioValue('radiolocal');
        const tipoLocalLabel = getRadioLabel('radiolocal');
        const tipoSector = getRadioValue('radiosector');
        const tipoSectorLabel = getRadioLabel('radiosector');

        const vialidad = el.vialidadDesc ? el.vialidadDesc.value.trim().toUpperCase() : '';
        const edificacion = el.edificacionDesc ? el.edificacionDesc.value.trim().toUpperCase() : '';
        const piso = el.piso ? el.piso.value.trim().toUpperCase() : '';
        const local = el.localDesc ? el.localDesc.value.trim().toUpperCase() : '';
        const sector = el.sectorDesc ? el.sectorDesc.value.trim().toUpperCase() : '';

        const estadoVal = el.estado ? el.estado.value : '';
        const estadoText = getSelectText(el.estado);
        const municipioVal = el.municipio ? el.municipio.value : '';
        const municipioText = getSelectText(el.municipio);
        const parroquiaVal = el.parroquia ? el.parroquia.value : '';
        const ciudadVal = el.ciudad ? el.ciudad.value : '';
        const ciudadText = getSelectText(el.ciudad);
        const zonaPostalVal = el.zonaPostal ? el.zonaPostal.value : '';

        const telefono = el.telefono ? el.telefono.value.trim() : '';
        const celular = el.celular ? el.celular.value.trim() : '';
        const fax = el.fax ? el.fax.value.trim() : '';
        const referencia = el.referencia ? el.referencia.value.trim().toUpperCase() : '';

        // ── Validaciones (orden SENIAT real) ──

        // 1. Teléfonos (primera prioridad)
        if (!telefono && !celular) {
            showToast('Debe ingresar al menos un telefono');
            return;
        }

        const phoneRegex = /^0\d{3}-\d{7}$/;
        if (telefono && !phoneRegex.test(telefono)) {
            showToast('Formato de telefono invalido. Ej: 0212-1234567');
            return;
        }
        if (celular && !phoneRegex.test(celular)) {
            showToast('Formato de telefono invalido. Ej: 0212-1234567');
            return;
        }

        // 2. Validaciones condicionales (dependientes de edificación)
        if (tipoEdificacion === '01' && !piso) {
            showToast('El campo Piso es obligatorio');
            return;
        }
        if (tipoEdificacion === '02' && !piso) {
            showToast('El campo Nivel es obligatorio');
            return;
        }
        if ((tipoEdificacion === '03' || tipoEdificacion === '04' || tipoEdificacion === '05') && !edificacion && !piso) {
            showToast('Debe registrar la Descripcion o el Nro de la Quinta/Casa');
            return;
        }

        // 3. Campos obligatorios generales
        if (!tipoDir) {
            showToast('Debe ingresar información en el campo Tipo de Dirección.');
            return;
        }
        if (!tipoVialidad) {
            showToast('Debe ingresar información en el campo Tipo Vialidad (Calle, Avenida, Vereda, Carretera, Esquina o Carrera).');
            return;
        }
        if (!vialidad) {
            showToast('Debe ingresar información en el campo que describe el Tipo de Vialidad (Calle, Avenida, Vereda, Carretera, Esquina o Carrera) seleccionado.');
            return;
        }
        if (!tipoEdificacion) {
            showToast('Debe ingresar información en el campo Tipo Edificación (Edificio, Centro Comercial, Quinta o Casa).');
            return;
        }
        if (!edificacion) {
            showToast('Debe ingresar información en el campo que describe el Tipo Edificación (Edificio, Centro Comercial, Quinta o Casa) seleccionado.');
            return;
        }
        if (!tipoLocal && (tipoEdificacion === '01' || tipoEdificacion === '02')) {
            showToast('Debe ingresar información en el campo Tipo Local (Apartamento, Local u Oficina).');
            return;
        }
        if (!local && (tipoEdificacion === '01' || tipoEdificacion === '02')) {
            showToast('Debe ingresar información en el campo que describe el Tipo Local (Apartamento, Local u Oficina) seleccionado.');
            return;
        }
        if (!tipoSector) {
            showToast('Debe ingresar información en el campo Tipo Sector (Urbanización, Zona o Sector).');
            return;
        }
        if (!sector) {
            showToast('Debe ingresar información en el campo que describe el Tipo Sector (Urbanización, Zona o Sector) seleccionado.');
            return;
        }
        if (!estadoVal) {
            showToast('Debe ingresar información en el campo Estado.');
            return;
        }
        if (!municipioVal) {
            showToast('Debe ingresar información en el campo Municipio.');
            return;
        }
        if (!parroquiaVal) {
            showToast('Debe ingresar información en el campo Parroquia.');
            return;
        }
        if (!ciudadVal) {
            showToast('Debe ingresar información en el campo Ciudad.');
            return;
        }
        if (!zonaPostalVal) {
            showToast('Debe ingresar información en el campo Zona Postal.');
            return;
        }

        const dir = {
            tipoDireccion: tipoDir,
            tipoDireccionText: tipoDirText,
            tipoVialidad: tipoVialidad,
            tipoVialidadLabel: tipoVialidadLabel,
            vialidad: vialidad,
            tipoEdificacion: tipoEdificacion,
            tipoEdificacionLabel: tipoEdificacionLabel,
            edificacion: edificacion,
            piso: piso,
            tipoLocal: tipoLocal,
            tipoLocalLabel: tipoLocalLabel,
            local: local,
            tipoSector: tipoSector,
            tipoSectorLabel: tipoSectorLabel,
            sector: sector,
            estado: estadoVal,
            estadoText: estadoText,
            municipio: municipioVal,
            municipioText: municipioText,
            parroquia: parroquiaVal,
            ciudad: ciudadVal,
            ciudadText: ciudadText,
            zonaPostal: zonaPostalVal,
            telefono: telefono,
            celular: celular,
            fax: fax,
            referencia: referencia
        };

        if (editIndex !== null) {
            direcciones[editIndex] = dir;
            editIndex = null;
            const btn = $('#guardar');
            if (btn) btn.value = 'Guardar';
        } else {
            direcciones.push(dir);
        }

        clearForm();
        renderTable();
        guardarBorradorBackend();
    }

    // ── Renderizar tabla ──
    function renderTable() {
        const existingTable = document.querySelector('table[cellspacing="2"][cellpadding="1"]');
        if (!existingTable) return;

        // Usar el tbody existente (los navegadores lo auto-crean)
        const tbody = existingTable.querySelector('tbody') || existingTable;

        // Eliminar filas de datos previas (estáticas y dinámicas)
        const oldRows = tbody.querySelectorAll('tr.letrasLista, tr.dir-empty');
        oldRows.forEach(row => row.remove());

        // Fila del header (primera tr con tablaSubTitulo)
        const headerRow = tbody.querySelector('tr:first-child');

        // Mostrar/ocultar botón Remover según haya direcciones
        const btnRemover = $('#remover');
        if (btnRemover) btnRemover.style.display = direcciones.length > 0 ? '' : 'none';

        // Ocultar DOMICILIO FISCAL si ya existe (salvo que se esté editando esa misma)
        const selectTipoDir = el.tipoDireccion;
        if (selectTipoDir) {
            const optFiscal = selectTipoDir.querySelector('option[value="06"]');
            if (optFiscal) {
                const fiscalUsado = direcciones.some((d, i) => d.tipoDireccion === '06' && i !== editIndex);
                optFiscal.disabled = fiscalUsado;
                optFiscal.style.display = fiscalUsado ? 'none' : '';
            }
        }

        if (direcciones.length === 0) {
            const emptyRow = document.createElement('tr');
            emptyRow.className = 'dir-empty';
            emptyRow.innerHTML = '<td colspan="6" style="FONT-SIZE:7pt;HEIGHT:20px;BACKGROUND-COLOR:#D7D7D7;text-align:center;font-family:Verdana,Arial;">No existen direcciones cargadas</td>';
            if (headerRow) {
                headerRow.after(emptyRow);
            } else {
                tbody.appendChild(emptyRow);
            }
            return;
        }

        // Crear fragmento con todas las filas
        const fragment = document.createDocumentFragment();
        direcciones.forEach((dir, i) => {
            const row = document.createElement('tr');
            row.className = 'letrasLista';
            row.setAttribute('align', 'center');
            row.style.cursor = 'default';
            row.innerHTML = `
                <td width="5%" style="FONT-SIZE:7pt;HEIGHT:20px;BACKGROUND-COLOR:#D7D7D7">
                    <div align="center">
                        <input type="checkbox" class="dir-check" data-index="${i}">
                    </div>
                </td>
                <td width="25%" style="FONT-SIZE:7pt;HEIGHT:20px;BACKGROUND-COLOR:#D7D7D7">
                    <a href="javascript:void(0)" onclick="SimDir.edit(${i})">${dir.tipoDireccionText}</a>
                </td>
                <td width="20%" style="FONT-SIZE:7pt;HEIGHT:20px;BACKGROUND-COLOR:#D7D7D7">${dir.tipoVialidadLabel.toUpperCase()} ${dir.vialidad}</td>
                <td width="20%" style="FONT-SIZE:7pt;HEIGHT:20px;BACKGROUND-COLOR:#D7D7D7">${dir.tipoSectorLabel.toUpperCase()} ${dir.sector}</td>
                <td width="20%" style="FONT-SIZE:7pt;HEIGHT:20px;BACKGROUND-COLOR:#D7D7D7">${dir.ciudadText || 'N/A'}</td>
                <td width="10%" style="FONT-SIZE:7pt;HEIGHT:20px;BACKGROUND-COLOR:#D7D7D7">${dir.estadoText}</td>
            `;
            fragment.appendChild(row);
        });

        if (headerRow) {
            headerRow.after(fragment);
        } else {
            tbody.appendChild(fragment);
        }
    }

    // ── Editar dirección ──
    async function editDireccion(index) {
        const dir = direcciones[index];
        editIndex = index;

        // Tipo dirección
        if (el.tipoDireccion) el.tipoDireccion.value = dir.tipoDireccion;

        // Radios
        setRadio('radiovialidad', dir.tipoVialidad);
        setRadio('radioedificacion', dir.tipoEdificacion);
        setRadio('radiolocal', dir.tipoLocal);
        setRadio('radiosector', dir.tipoSector);

        // Textos
        if (el.vialidadDesc) el.vialidadDesc.value = dir.vialidad;
        if (el.edificacionDesc) el.edificacionDesc.value = dir.edificacion;
        if (el.piso) el.piso.value = dir.piso;
        if (el.localDesc) el.localDesc.value = dir.local;
        if (el.sectorDesc) el.sectorDesc.value = dir.sector;
        if (el.telefono) el.telefono.value = dir.telefono;
        if (el.celular) el.celular.value = dir.celular;
        if (el.fax) el.fax.value = dir.fax;
        if (el.referencia) el.referencia.value = dir.referencia;

        // Cascada: cargar estado y restaurar
        const base = getBaseUrl();
        await fetchSelect(`${base}/api/estados`, 'estados', el.estado, 'SELECCIONAR');
        if (el.estado) el.estado.value = dir.estado;

        if (dir.estado) {
            await Promise.all([
                fetchSelect(`${base}/api/municipios?estado_id=${dir.estado}`, 'municipios', el.municipio, 'SELECCIONAR'),
                fetchSelect(`${base}/api/zonas-postales?estado_id=${dir.estado}`, 'zonas', el.zonaPostal, 'SELECCIONAR'),
            ]);
            if (el.municipio) el.municipio.value = dir.municipio;
            if (el.zonaPostal) el.zonaPostal.value = dir.zonaPostal;
        }

        if (dir.municipio) {
            await Promise.all([
                fetchSelect(`${base}/api/parroquias?municipio_id=${dir.municipio}`, 'parroquias', el.parroquia, 'SELECCIONAR'),
                fetchSelect(`${base}/api/ciudades?municipio_id=${dir.municipio}`, 'ciudades', el.ciudad, 'SELECCIONAR'),
            ]);
            if (el.parroquia) el.parroquia.value = dir.parroquia;
            if (el.ciudad) el.ciudad.value = dir.ciudad;
        }

        const btn = $('#guardar');
        if (btn) btn.value = 'Actualizar';
    }

    function setRadio(name, value) {
        const radios = document.querySelectorAll(`input[name="${name}"]`);
        radios.forEach(r => r.checked = r.value === value);
    }

    // ── Remover direcciones seleccionadas ──
    function removeDirecciones() {
        const checks = document.querySelectorAll('.dir-check:checked');
        if (checks.length === 0) return;

        // Obtener índices en orden descendente para no alterar los índices al eliminar
        const indices = Array.from(checks).map(c => parseInt(c.dataset.index)).sort((a, b) => b - a);
        indices.forEach(i => direcciones.splice(i, 1));

        if (editIndex !== null) {
            editIndex = null;
            clearForm();
            const btn = $('#guardar');
            if (btn) btn.value = 'Guardar';
        }

        guardarBorradorBackend();

        renderTable();
    }

    // ── Limpiar formulario ──
    function clearForm() {
        if (el.tipoDireccion) el.tipoDireccion.value = '';
        if (el.vialidadDesc) el.vialidadDesc.value = '';
        if (el.edificacionDesc) el.edificacionDesc.value = '';
        if (el.piso) el.piso.value = '';
        if (el.localDesc) el.localDesc.value = '';
        if (el.sectorDesc) el.sectorDesc.value = '';
        if (el.telefono) el.telefono.value = '';
        if (el.celular) el.celular.value = '';
        if (el.fax) el.fax.value = '';
        if (el.referencia) el.referencia.value = '';

        // Resetear radios
        ['radiovialidad', 'radioedificacion', 'radiolocal', 'radiosector'].forEach(name => {
            document.querySelectorAll(`input[name="${name}"]`).forEach(r => r.checked = false);
        });

        // Resetear selects de cascada
        resetSelect(el.municipio, 'SELECCIONAR');
        resetSelect(el.parroquia, 'SELECCIONAR');
        resetSelect(el.ciudad, 'SELECCIONAR');
        resetSelect(el.zonaPostal, 'SELECCIONAR');
    }

    // ── Interceptar submit nativo y botones ──
    function initFormInterception() {
        // Interceptar el formulario para que "Guardar" use nuestra lógica
        const form = document.querySelector('form[name="DireccionForm"]');
        if (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                saveDireccion();
            });
        }

        // Interceptar el botón "Remover"
        const btnRemover = $('#remover');
        if (btnRemover) {
            btnRemover.type = 'button'; // cambiar de submit a button
            btnRemover.addEventListener('click', function (e) {
                e.preventDefault();
                removeDirecciones();
            });
        }

        // Interceptar el botón "Reestablecer"
        const btnReset = $('#reestablecer');
        if (btnReset) {
            btnReset.type = 'button';
            btnReset.addEventListener('click', function (e) {
                e.preventDefault();
                clearForm();
                editIndex = null;
                const btn = $('#guardar');
                if (btn) btn.value = 'Guardar';
            });
        }
    }

    // ── Inicialización ──
    function init() {
        // Reemplazar los estados estáticos por datos de API
        loadEstados();

        // Configurar cascadas
        initCascadeListeners();

        // Formato de teléfono
        initPhoneFormat();

        // Lógica condicional edificio/quinta/casa
        initEdificacionLogic();

        // Interceptar formulario
        initFormInterception();

        // Cargar direcciones guardadas del JSON y renderizar
        loadSavedDirecciones();
        renderTable();
    }

    // Exponer API global para onclick inline
    window.SimDir = {
        edit: editDireccion,
        remove: removeDirecciones,
        save: saveDireccion
    };

    // Ejecutar al cargar DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
