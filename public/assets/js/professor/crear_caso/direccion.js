import { caseData, UIState } from './state.js';
import { $, show, hide, showToast } from './utils.js';

// Control de peticiones para evitar Race Conditions
const abortControllers = {
    estados: null,
    municipios: null,
    parroquias: null,
    ciudades: null,
    zonas: null,
    // Acta de defunción
    estados_acta: null,
    municipios_acta: null,
    parroquias_acta: null
};

function fetchGeneric(url, type, selectSelector, defaultText, stateObj, fieldName) {
    const select = document.querySelector(selectSelector);
    if (!select) return;

    if (abortControllers[type]) {
        abortControllers[type].abort();
    }
    abortControllers[type] = new AbortController();
    const signal = abortControllers[type].signal;

    select.innerHTML = '<option value="">Cargando...</option>';
    select.disabled = true;

    // Limpiar el campo en el state
    if (stateObj && fieldName && stateObj[fieldName] !== undefined) {
        stateObj[fieldName] = '';
    }

    fetch(url, { signal })
        .then(async response => {
            const text = await response.text();
            if (!response.ok) throw new Error(`Server Error: ${response.status} - ${text}`);
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error("Respuesta inválida del servidor (No es JSON):", text);
                console.error("Detalle del error JSON.parse:", e.message);
                throw new Error("Formato de respuesta inválido: " + e.message);
            }
        })
        .then(result => {
            if (result.success && result.data) {
                const isZona = type === 'zonas';
                const options = result.data.map(item =>
                    `<option value="${item.id}">${isZona ? item.codigo : item.nombre}</option>`
                ).join('');
                select.innerHTML = `<option value="">${defaultText}</option>` + options;
            } else {
                select.innerHTML = '<option value="">No hay datos</option>';
            }
        })
        .catch(err => {
            if (err.name === 'AbortError') return;
            console.error(`Error en ${type}:`, err);
            select.innerHTML = '<option value="">Error al cargar</option>';
        })
        .finally(() => {
            if (!signal.aborted) {
                select.disabled = false;
                select.value = "";
            }
        });
}

function getBaseUrl() {
    let url = window.BASE_URL || '/tesis_francisco/public';
    return url.replace(/\/+$/, ''); // Remueve slash final si existe
}

// ═══════════════════════════════════════════════════════
//  Domicilio fiscal del causante
//  Nombres de data-bind corregidos para coincidir con BD
// ═══════════════════════════════════════════════════════

export function fetchEstados() {
    const baseUrl = getBaseUrl();
    fetchGeneric(`${baseUrl}/api/estados`, 'estados',
        '[data-bind="domicilio_causante.estado"]', 'Seleccionar Estado',
        caseData.domicilio_causante, 'estado');
}

export function fetchMunicipios(estadoId) {
    if (!estadoId) {
        resetSelect('[data-bind="domicilio_causante.municipio"]', 'Seleccionar Municipio');
        return;
    }
    const baseUrl = getBaseUrl();
    fetchGeneric(`${baseUrl}/api/municipios?estado_id=${estadoId}`, 'municipios',
        '[data-bind="domicilio_causante.municipio"]', 'Seleccionar Municipio',
        caseData.domicilio_causante, 'municipio');
}

export function fetchParroquias(municipioId) {
    if (!municipioId) {
        resetSelect('[data-bind="domicilio_causante.parroquia"]', 'Seleccionar Parroquia');
        return;
    }
    const baseUrl = getBaseUrl();
    fetchGeneric(`${baseUrl}/api/parroquias?municipio_id=${municipioId}`, 'parroquias',
        '[data-bind="domicilio_causante.parroquia"]', 'Seleccionar Parroquia',
        caseData.domicilio_causante, 'parroquia');
}

export function fetchCiudades(municipioId) {
    if (!municipioId) {
        resetSelect('[data-bind="domicilio_causante.ciudad"]', 'Seleccionar Ciudad');
        return;
    }
    const baseUrl = getBaseUrl();
    fetchGeneric(`${baseUrl}/api/ciudades?municipio_id=${municipioId}`, 'ciudades',
        '[data-bind="domicilio_causante.ciudad"]', 'Seleccionar Ciudad',
        caseData.domicilio_causante, 'ciudad');
}

// Sección 6: zona_postal → codigo_postal_id
export function fetchZonasPostales(estadoId) {
    if (!estadoId) {
        resetSelect('[data-bind="domicilio_causante.codigo_postal_id"]', 'SELECCIONAR');
        return;
    }
    const baseUrl = getBaseUrl();
    fetchGeneric(`${baseUrl}/api/zonas-postales?estado_id=${estadoId}`, 'zonas',
        '[data-bind="domicilio_causante.codigo_postal_id"]', 'SELECCIONAR',
        caseData.domicilio_causante, 'codigo_postal_id');
}

// ═══════════════════════════════════════════════════════
// (La sección de Acta de Defunción fue eliminada por simplificación a campo de texto)
// ═══════════════════════════════════════════════════════

// ═══════════════════════════════════════════════════════
//  Utilidades
// ═══════════════════════════════════════════════════════

function resetSelect(selector, defaultText) {
    const el = document.querySelector(selector);
    if (el) {
        el.innerHTML = `<option value="">${defaultText}</option>`;
        el.value = "";
        el.disabled = false;
    }
}

export function initAddressListeners() {
    // ── Domicilio fiscal ──
    const estadoSelect = document.querySelector('[data-bind="domicilio_causante.estado"]');
    const municipioSelect = document.querySelector('[data-bind="domicilio_causante.municipio"]');

    if (estadoSelect) {
        estadoSelect.addEventListener('change', (e) => {
            const estadoId = e.target.value;
            caseData.domicilio_causante.estado = estadoId;
            caseData.domicilio_causante.municipio = '';
            caseData.domicilio_causante.parroquia = '';
            caseData.domicilio_causante.ciudad = '';
            caseData.domicilio_causante.codigo_postal_id = '';  // antes: zona_postal

            fetchMunicipios(estadoId);
            fetchZonasPostales(estadoId);
            fetchParroquias('');
            fetchCiudades('');
        });
    }

    if (municipioSelect) {
        municipioSelect.addEventListener('change', (e) => {
            const municipioId = e.target.value;
            caseData.domicilio_causante.municipio = municipioId;
            caseData.domicilio_causante.parroquia = '';
            caseData.domicilio_causante.ciudad = '';

            fetchParroquias(municipioId);
            fetchCiudades(municipioId);
        });
    }

    // (Eventos de Acta de Defunción eliminados)
}

export function saveDireccion() {
    const d = caseData.domicilio_causante;

    if (!d.tipo_direccion || !d.tipo_vialidad || !d.tipo_inmueble || !d.nombre_vialidad ||
        !d.nro_inmueble || !d.tipo_nivel || !d.tipo_sector || !d.nro_nivel || !d.nombre_sector ||
        !d.estado || !d.municipio || !d.parroquia || !d.ciudad) {
        showToast('Complete todos los campos de ubicación requeridos para la dirección.');
        return;
    }

    const nuevaDir = { ...d };

    if (UIState.editDireccionIndex !== null) {
        caseData.direcciones_causante[UIState.editDireccionIndex] = nuevaDir;
        UIState.editDireccionIndex = null;
        $('#btnSaveDireccion').innerText = "+ Agregar Dirección";
    } else {
        caseData.direcciones_causante.push(nuevaDir);
    }

    clearDireccionForm();
    renderDirecciones();
}

export function renderDirecciones() {
    const container = $('#direccionesTableContainer');
    const tbody = $('#direccionesTableBody');
    if (!container || !tbody) return;

    if (caseData.direcciones_causante.length === 0) {
        hide(container);
        return;
    }

    show(container);
    tbody.innerHTML = caseData.direcciones_causante.map((dir, i) => `
        <tr>
            <td>${dir.tipo_direccion.replace(/_/g, ' ')}</td>
            <td>
                <strong>${dir.tipo_vialidad}</strong>: ${dir.nombre_vialidad}<br>
                <strong>${dir.tipo_inmueble}</strong>: ${dir.nro_inmueble} (${dir.tipo_nivel} ${dir.nro_nivel})<br>
                <strong>${dir.tipo_sector}</strong>: ${dir.nombre_sector}
            </td>
            <td>Edo. ${getOptionText('estado', dir.estado)}, Mun. ${getOptionText('municipio', dir.municipio)}, Pq. ${getOptionText('parroquia', dir.parroquia)}, ${getOptionText('ciudad', dir.ciudad)}</td>
            <td>Fijo: ${dir.telefono_fijo || 'N/A'}<br>Cel: ${dir.telefono_celular || 'N/A'}</td>
            <td>
                <div class="cc-td-actions">
                    <button type="button" class="cc-btn--icon-edit" onclick="CC.editDireccion(${i})"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>
                    <button type="button" class="cc-btn--icon-danger" onclick="CC.deleteDireccion(${i})"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></button>
                </div>
            </td>
        </tr>
    `).join('');
}

function getOptionText(selectName, value) {
    const sel = document.querySelector(`select[data-bind="domicilio_causante.${selectName}"]`);
    if (!sel || !value) return value;
    const opt = sel.querySelector(`option[value="${value}"]`);
    return opt ? opt.innerText : value;
}

export function editDireccion(index) {
    const dir = caseData.direcciones_causante[index];
    Object.assign(caseData.domicilio_causante, dir);

    document.querySelectorAll('[data-bind^="domicilio_causante."]').forEach(el => {
        const key = el.dataset.bind.split('.')[1];
        if (dir[key] !== undefined) {
            if (el.type === 'radio' || el.type === 'checkbox') {
                el.checked = (el.value === String(dir[key]));
            } else {
                el.value = dir[key];
            }
        }
    });

    UIState.editDireccionIndex = index;
    $('#btnSaveDireccion').innerText = "Guardar Cambios";
}

export function deleteDireccion(index) {
    if (confirm('¿Eliminar esta dirección?')) {
        caseData.direcciones_causante.splice(index, 1);
        if (UIState.editDireccionIndex === index) {
            UIState.editDireccionIndex = null;
            clearDireccionForm();
            $('#btnSaveDireccion').innerText = "+ Agregar Dirección";
        }
        renderDirecciones();
    }
}

function clearDireccionForm() {
    const d = caseData.domicilio_causante;
    for (let k in d) { d[k] = ''; }

    document.querySelectorAll('[data-bind^="domicilio_causante."]').forEach(el => {
        if (el.tagName === 'SELECT') el.value = '';
        if (el.type === 'radio' || el.type === 'checkbox') el.checked = false;
        if (el.type === 'text') el.value = '';
    });
}
