import { caseData } from './state.js';

// Control de peticiones para evitar Race Conditions
const abortControllers = {
    estados: null,
    municipios: null,
    parroquias: null,
    ciudades: null,
    zonas: null
};

function fetchGeneric(url, type, selectSelector, defaultText) {
    const select = document.querySelector(selectSelector);
    if (!select) return;

    if (abortControllers[type]) {
        abortControllers[type].abort();
    }
    abortControllers[type] = new AbortController();
    const signal = abortControllers[type].signal;

    select.innerHTML = '<option value="">Cargando...</option>';
    select.disabled = true;

    const fieldMap = {
        'estados': 'estado',
        'municipios': 'municipio',
        'parroquias': 'parroquia',
        'ciudades': 'ciudad',
        'zonas': 'zona_postal'
    };
    if (caseData.domicilio_causante[fieldMap[type]] !== undefined) {
        caseData.domicilio_causante[fieldMap[type]] = '';
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

export function fetchEstados() {
    const baseUrl = getBaseUrl();
    fetchGeneric(`${baseUrl}/api/estados`, 'estados',
        '[data-bind="domicilio_causante.estado"]', 'Seleccionar Estado');
}

export function fetchMunicipios(estadoId) {
    if (!estadoId) {
        resetSelect('[data-bind="domicilio_causante.municipio"]', 'Seleccionar Municipio');
        return;
    }
    const baseUrl = getBaseUrl();
    fetchGeneric(`${baseUrl}/api/municipios?estado_id=${estadoId}`, 'municipios',
        '[data-bind="domicilio_causante.municipio"]', 'Seleccionar Municipio');
}

export function fetchParroquias(municipioId) {
    if (!municipioId) {
        resetSelect('[data-bind="domicilio_causante.parroquia"]', 'Seleccionar Parroquia');
        return;
    }
    const baseUrl = getBaseUrl();
    fetchGeneric(`${baseUrl}/api/parroquias?municipio_id=${municipioId}`, 'parroquias',
        '[data-bind="domicilio_causante.parroquia"]', 'Seleccionar Parroquia');
}

export function fetchCiudades(municipioId) {
    if (!municipioId) {
        resetSelect('[data-bind="domicilio_causante.ciudad"]', 'Seleccionar Ciudad');
        return;
    }
    const baseUrl = getBaseUrl();
    fetchGeneric(`${baseUrl}/api/ciudades?municipio_id=${municipioId}`, 'ciudades',
        '[data-bind="domicilio_causante.ciudad"]', 'Seleccionar Ciudad');
}

export function fetchZonasPostales(estadoId) {
    if (!estadoId) {
        resetSelect('[data-bind="domicilio_causante.zona_postal"]', 'SELECCIONAR');
        return;
    }
    const baseUrl = getBaseUrl();
    fetchGeneric(`${baseUrl}/api/zonas-postales?estado_id=${estadoId}`, 'zonas',
        '[data-bind="domicilio_causante.zona_postal"]', 'SELECCIONAR');
}

function resetSelect(selector, defaultText) {
    const el = document.querySelector(selector);
    if (el) {
        el.innerHTML = `<option value="">${defaultText}</option>`;
        el.value = "";
        el.disabled = false;
    }
}

export function initAddressListeners() {
    const estadoSelect = document.querySelector('[data-bind="domicilio_causante.estado"]');
    const municipioSelect = document.querySelector('[data-bind="domicilio_causante.municipio"]');

    if (estadoSelect) {
        estadoSelect.addEventListener('change', (e) => {
            const estadoId = e.target.value;
            caseData.domicilio_causante.estado = estadoId;
            caseData.domicilio_causante.municipio = '';
            caseData.domicilio_causante.parroquia = '';
            caseData.domicilio_causante.ciudad = '';
            caseData.domicilio_causante.zona_postal = '';

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
}
