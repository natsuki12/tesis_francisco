import { caseData, UIState } from './state.js';
import { $, show, hide, showToast } from '../../global/utils.js';

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

function fetchGeneric(url, type, selectSelector, defaultText, stateObj, fieldName, preserveState = false) {
    const select = document.querySelector(selectSelector);
    if (!select) return Promise.resolve();

    if (abortControllers[type]) {
        abortControllers[type].abort();
    }
    abortControllers[type] = new AbortController();
    const signal = abortControllers[type].signal;

    // Guardar valor actual antes de limpiar
    const savedValue = (preserveState && stateObj && fieldName) ? stateObj[fieldName] : null;

    select.innerHTML = '<option value="">Cargando...</option>';
    select.disabled = true;

    // Solo limpiar el campo si NO estamos preservando
    if (!preserveState && stateObj && fieldName && stateObj[fieldName] !== undefined) {
        stateObj[fieldName] = '';
    }

    return fetch(url, { signal })
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
                if (preserveState && savedValue) {
                    // Restaurar valor guardado del state
                    select.value = savedValue;
                    if (stateObj && fieldName) stateObj[fieldName] = savedValue;
                } else if (!preserveState) {
                    select.value = "";
                }
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

    // Listener para tipo de inmueble
    const tipoInmuebleRadios = document.querySelectorAll('input[name="tipo_inmueble"]');
    tipoInmuebleRadios.forEach(r => {
        r.addEventListener('change', (e) => {
            const val = e.target.value;
            const radiosNivel = document.querySelectorAll('input[name="tipo_nivel"]');
            const nroNivelInput = document.querySelector('[data-bind="domicilio_causante.nro_nivel"]');
            const lblPiso = document.getElementById('lbl_piso_nivel');

            if (val === 'Edificio' || val === 'Centro_Comercial') {
                radiosNivel.forEach(rn => rn.disabled = false);
                if (nroNivelInput) {
                    nroNivelInput.disabled = false;
                    if (nroNivelInput.value === 'NO APLICA') {
                        nroNivelInput.value = '';
                        caseData.domicilio_causante.nro_nivel = '';
                    }
                }
                if (lblPiso) lblPiso.innerText = val === 'Edificio' ? 'PISO' : 'NIVEL';
            } else {
                // Quinta, Casa, Local
                radiosNivel.forEach(rn => {
                    rn.disabled = true;
                    rn.checked = false;
                });
                caseData.domicilio_causante.tipo_nivel = ''; // clear state

                if (nroNivelInput) {
                    nroNivelInput.value = 'NO APLICA';
                    nroNivelInput.disabled = true;
                    caseData.domicilio_causante.nro_nivel = 'NO APLICA';
                }
                if (lblPiso) lblPiso.innerText = 'NRO';
            }
        });
    });

    // Auto-formato teléfonos: 0XXX-XXXXXXX
    const phoneFields = [
        '[data-bind="domicilio_causante.telefono_fijo"]',
        '[data-bind="domicilio_causante.telefono_celular"]',
        '[data-bind="domicilio_causante.fax"]'
    ];
    phoneFields.forEach(selector => {
        const el = document.querySelector(selector);
        if (!el) return;
        el.maxLength = 12; // 0XXX-XXXXXXX = 12 chars
        el.addEventListener('input', () => {
            // Solo dígitos
            let digits = el.value.replace(/\D/g, '');
            if (digits.length > 11) digits = digits.slice(0, 11);
            // Insertar guión después del 4to dígito
            if (digits.length > 4) {
                el.value = digits.slice(0, 4) + '-' + digits.slice(4);
            } else {
                el.value = digits;
            }
            // Sync con caseData
            const bind = el.dataset.bind;
            if (bind) {
                const [section, key] = bind.split('.');
                if (caseData[section]) caseData[section][key] = el.value;
            }
        });
    });
}

export function saveDireccion() {
    const d = caseData.domicilio_causante;

    // Forzar mayúsculas en campos de texto
    const uppercaseFields = ['nombre_vialidad', 'nro_nivel', 'nombre_sector', 'telefono_fijo', 'telefono_celular', 'fax', 'punto_referencia'];
    uppercaseFields.forEach(f => {
        const el = document.querySelector(`[data-bind="domicilio_causante.${f}"]`);
        if (el && el.value) {
            el.value = el.value.toUpperCase();
            d[f] = el.value;
        }
    });

    // Leer inputs manuales de nro_inmueble
    const elDesc = document.getElementById('input_desc_inmueble');
    const elNum = document.getElementById('input_piso_nivel');
    let desc = elDesc ? elDesc.value.trim().toUpperCase() : '';
    let num = elNum ? elNum.value.trim().toUpperCase() : '';

    if (elDesc) elDesc.value = desc;
    if (elNum) elNum.value = num;

    // Asegurar que tipo de inmueble está actualizado
    const tipoInmChecked = document.querySelector('input[name="tipo_inmueble"]:checked');
    d.tipo_inmueble = tipoInmChecked ? tipoInmChecked.value : d.tipo_inmueble;

    if (!desc && !num && d.tipo_inmueble) {
        showToast('Debe ingresar el nombre/descripción o el piso/nro/nivel del inmueble.');
        return;
    }

    // Construir nro_inmueble lógicamente
    let finalDesc = desc || 'NO APLICA';
    let finalInmueble = '';

    if (d.tipo_inmueble === 'Edificio') {
        if (!num) { showToast('El piso es obligatorio para Edificios.'); return; }
        finalInmueble = `${finalDesc} - PISO ${num}`;
    } else if (d.tipo_inmueble === 'Centro_Comercial') {
        if (!num) { showToast('El nivel es obligatorio para Centros Comerciales.'); return; }
        finalInmueble = `${finalDesc} - NIVEL ${num}`;
    } else {
        if (desc && num) finalInmueble = `${finalDesc} - NRO ${num}`;
        else if (desc && !num) finalInmueble = finalDesc;
        else if (!desc && num) finalInmueble = `NO APLICA - NRO ${num}`;
    }

    d.nro_inmueble = finalInmueble;

    if (!d.tipo_direccion || !d.tipo_vialidad || !d.tipo_inmueble || !d.nombre_vialidad ||
        !d.nro_inmueble || !d.tipo_sector || !d.nombre_sector ||
        !d.estado || !d.municipio || !d.parroquia || !d.ciudad || !d.codigo_postal_id) {
        showToast('Complete todos los campos base requeridos (incluyendo Código Postal).');
        return;
    }

    if (d.tipo_inmueble === 'Edificio' || d.tipo_inmueble === 'Centro_Comercial') {
        if (!d.tipo_nivel || !d.nro_nivel) {
            showToast(`Complete el tipo de nivel y el número de local/apto/oficina para ${d.tipo_inmueble.replace('_', ' ')}.`);
            return;
        }
    }

    if (!d.telefono_fijo && !d.telefono_celular) {
        showToast('Debe ingresar al menos un teléfono (fijo o celular).');
        return;
    }

    const phoneRegex = /^0\d{3}-\d{7}$/;
    if (d.telefono_fijo && !phoneRegex.test(d.telefono_fijo)) {
        showToast('El teléfono fijo debe tener el formato 0XXX-XXXXXXX');
        return;
    }
    if (d.telefono_celular && !phoneRegex.test(d.telefono_celular)) {
        showToast('El teléfono celular debe tener el formato 0XXX-XXXXXXX');
        return;
    }
    if (d.fax && !phoneRegex.test(d.fax)) {
        showToast('El fax debe tener el formato 0XXX-XXXXXXX');
        return;
    }

    const nuevaDir = { ...d };

    if (UIState.editDireccionIndex !== null) {
        caseData.direcciones_causante[UIState.editDireccionIndex] = nuevaDir;
        UIState.editDireccionIndex = null;
        $('#btnSaveDireccion').innerText = "+ Agregar Dirección";
    } else {
        if (caseData.direcciones_causante.length >= 1) {
            showToast('Solo se permite un domicilio fiscal. Edite o elimine el existente.');
            return;
        }
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
                    <button type="button" class="btn-icon" onclick="CC.editDireccion(${i})"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>
                    <button type="button" class="btn-danger-ghost" onclick="CC.deleteDireccion(${i})"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></button>
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

export async function editDireccion(index) {
    const dir = caseData.direcciones_causante[index];

    // Copiar la dirección al domicilio_causante en estado
    Object.assign(caseData.domicilio_causante, dir);

    const baseUrl = getBaseUrl();

    // 1. Cargar estados y restaurar el valor guardado
    await fetchGeneric(`${baseUrl}/api/estados`, 'estados',
        '[data-bind="domicilio_causante.estado"]', 'Seleccionar Estado',
        caseData.domicilio_causante, 'estado', true);

    // 2. Si hay estado, cargar municipios y zonas postales
    if (dir.estado) {
        await Promise.all([
            fetchGeneric(`${baseUrl}/api/municipios?estado_id=${dir.estado}`, 'municipios',
                '[data-bind="domicilio_causante.municipio"]', 'Seleccionar Municipio',
                caseData.domicilio_causante, 'municipio', true),
            fetchGeneric(`${baseUrl}/api/zonas-postales?estado_id=${dir.estado}`, 'zonas',
                '[data-bind="domicilio_causante.codigo_postal_id"]', 'SELECCIONAR',
                caseData.domicilio_causante, 'codigo_postal_id', true),
        ]);
    }

    // 3. Si hay municipio, cargar parroquias y ciudades
    if (dir.municipio) {
        await Promise.all([
            fetchGeneric(`${baseUrl}/api/parroquias?municipio_id=${dir.municipio}`, 'parroquias',
                '[data-bind="domicilio_causante.parroquia"]', 'Seleccionar Parroquia',
                caseData.domicilio_causante, 'parroquia', true),
            fetchGeneric(`${baseUrl}/api/ciudades?municipio_id=${dir.municipio}`, 'ciudades',
                '[data-bind="domicilio_causante.ciudad"]', 'Seleccionar Ciudad',
                caseData.domicilio_causante, 'ciudad', true),
        ]);
    }

    // 4. Rellenar el resto de campos del formulario con los valores guardados
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

    // 5. Restaurar campos manuales desc_inmueble y piso_nivel a partir de nro_inmueble
    let desc = '', num = '';
    if (dir.nro_inmueble) {
        if (dir.nro_inmueble.includes(' - PISO ')) {
            [desc, num] = dir.nro_inmueble.split(' - PISO ');
        } else if (dir.nro_inmueble.includes(' - NIVEL ')) {
            [desc, num] = dir.nro_inmueble.split(' - NIVEL ');
        } else if (dir.nro_inmueble.includes(' - NRO ')) {
            [desc, num] = dir.nro_inmueble.split(' - NRO ');
        } else {
            desc = dir.nro_inmueble;
        }
    }
    if (desc === 'NO APLICA') desc = '';

    const elDesc = document.getElementById('input_desc_inmueble');
    const elNum = document.getElementById('input_piso_nivel');
    if (elDesc) elDesc.value = desc;
    if (elNum) elNum.value = num;

    // 6. Forzar trigger de tipo_inmueble para actualizar labels y disables
    const selectedInmueble = document.querySelector(`input[name="tipo_inmueble"][value="${dir.tipo_inmueble}"]`);
    if (selectedInmueble) {
        selectedInmueble.checked = true;
        selectedInmueble.dispatchEvent(new Event('change'));
    }

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

/**
 * Restaura la cascada completa de dirección usando los valores persistidos en caseData.
 * Se usa al recargar la página para reconstruir los selects sin perder los valores guardados.
 */
export async function restoreAddressCascade() {
    if (!caseData.domicilio_causante) {
        caseData.domicilio_causante = {};
    }
    const d = caseData.domicilio_causante;
    const baseUrl = getBaseUrl();

    // 1. Cargar estados y restaurar valor
    await fetchGeneric(`${baseUrl}/api/estados`, 'estados',
        '[data-bind="domicilio_causante.estado"]', 'Seleccionar Estado',
        d, 'estado', true);

    // 2. Si había un estado seleccionado, cargar municipios y zonas
    if (d.estado) {
        await Promise.all([
            fetchGeneric(`${baseUrl}/api/municipios?estado_id=${d.estado}`, 'municipios',
                '[data-bind="domicilio_causante.municipio"]', 'Seleccionar Municipio',
                d, 'municipio', true),
            fetchGeneric(`${baseUrl}/api/zonas-postales?estado_id=${d.estado}`, 'zonas',
                '[data-bind="domicilio_causante.codigo_postal_id"]', 'SELECCIONAR',
                d, 'codigo_postal_id', true),
        ]);
    }

    // 3. Si había un municipio seleccionado, cargar parroquias y ciudades
    if (d.municipio) {
        await Promise.all([
            fetchGeneric(`${baseUrl}/api/parroquias?municipio_id=${d.municipio}`, 'parroquias',
                '[data-bind="domicilio_causante.parroquia"]', 'Seleccionar Parroquia',
                d, 'parroquia', true),
            fetchGeneric(`${baseUrl}/api/ciudades?municipio_id=${d.municipio}`, 'ciudades',
                '[data-bind="domicilio_causante.ciudad"]', 'Seleccionar Ciudad',
                d, 'ciudad', true),
        ]);
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

    // Limpiar campos visuales que no tienen data-bind
    const elDesc = document.getElementById('input_desc_inmueble');
    const elNum = document.getElementById('input_piso_nivel');
    if (elDesc) elDesc.value = '';
    if (elNum) elNum.value = '';

    // Habilitar controles ocultos/deshabilitados condicionalmente
    document.querySelectorAll('input[name="tipo_nivel"]').forEach(rn => {
        rn.disabled = false;
        rn.checked = false;
    });

    const nroNivelInput = document.querySelector('[data-bind="domicilio_causante.nro_nivel"]');
    if (nroNivelInput) {
        nroNivelInput.disabled = false;
        nroNivelInput.value = '';
    }

    const lbl = document.getElementById('lbl_piso_nivel');
    if (lbl) lbl.innerText = 'PISO/NRO';
}
