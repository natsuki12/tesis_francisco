import { $, $$, showToast } from './utils.js';
import { caseData, UIState, loadCaseData, saveCaseData, clearSavedCaseData, hydrateCaseData } from './state.js';

/**
 * Envía el caseData completo al backend vía POST /api/casos.
 * @param {'Borrador'|'Publicado'} modo
 */
let _submitted = false;
async function submitCase(modo) {
    // Fijar el estado antes de enviar
    caseData.caso.estado = modo;

    // Preparar payload con caso_id si existe (para re-saves de borradores)
    const payload = JSON.parse(JSON.stringify(caseData));
    if (caseData.caso_id) {
        payload.caso_id = caseData.caso_id;
    }

    try {
        const baseUrl = (window.BASE_URL || '/tesis_francisco/public').replace(/\/+$/, '');
        const res = await fetch(baseUrl + '/api/casos', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });
        const json = await res.json();

        if (!res.ok || !json.success) {
            const serverErrors = json.errors || ['Error desconocido al guardar.'];
            if (modo === 'Publicado') {
                showValidationPopup(serverErrors, 'Error del servidor');
            } else {
                serverErrors.forEach(err => showToast(err));
            }
            return;
        }

        // Éxito
        showToast(json.message || 'Guardado exitosamente.', 'success');

        if (modo === 'Borrador') {
            // Guardar caso_id y redirigir a la lista
            clearSavedCaseData();
            _submitted = true;
            setTimeout(() => { window.location.href = baseUrl + '/casos-sucesorales?borrador=ok'; }, 500);
        } else {
            // Publicar → redirigir
            clearSavedCaseData();
            _submitted = true;
            setTimeout(() => { window.location.href = baseUrl + '/casos-sucesorales'; }, 1500);
        }
    } catch (err) {
        showToast('Error de red al guardar el caso: ' + err.message);
    }
}
import { renderHerenciaCheckboxes, initRepresentanteLogic, renderHerederos, renderHerederosPremuertos } from './herederos.js';
import { fetchEstados, initAddressListeners, saveDireccion, renderDirecciones, editDireccion, deleteDireccion, restoreAddressCascade } from './direccion.js';
import { initStepperClicks, setStep, nextStep, prevStep } from './navigation.js';
import { initStudentSearch } from './summary.js';
import { initCatalogos, getCatalogs, loadSeccionesSelect } from './catalogos.js';
import { openModal, closeModal, saveModal, removeItem, removeMueble } from './modal.js';
import { saveProrroga, renderProrrogas, deleteProrroga, editProrroga } from './prorroga.js';
import { renderInventario } from './inventario.js';

/**
 * Validación frontend antes de publicar.
 * Genera un array de mensajes de error legibles.
 */
// ====================================================================
// Restricciones de entrada en tiempo real
// ====================================================================

/**
 * Aplica restricciones de tipo en inputs dentro de un contenedor.
 * Se reutiliza tanto para el DOM principal como para modales dinámicos.
 */
function applyConstraints(container) {
    const today = new Date().toISOString().slice(0, 10);
    // Query both data-bind (static form) and data-modal (modal fields)
    const sel = (name) =>
        container.querySelector(`[data-bind="${name}"]`)
        || container.querySelector(`[data-modal="${name}"]`);

    // Helper: apply constraint to an element, skip if already done
    const constrain = (el, flag = '_constrained') => {
        if (!el || el[flag]) return null;
        el[flag] = true;
        return el;
    };

    // — Solo texto (bloquear dígitos) —
    [
        'causante.nombres', 'causante.apellidos',
        'representante.nombres', 'representante.apellidos',
        'nombres', 'apellidos', // modal heredero / heredero_premuerto
        'apellidos_nombres', 'nombre_oferente', // mueble: cobrar, compra
    ].forEach(name => {
        const el = constrain(sel(name));
        if (!el) return;
        el.addEventListener('input', () => {
            el.value = el.value.replace(/[0-9]/g, '');
        });
    });

    // — Solo numérico (bloquear letras, maxlength) —
    [
        { name: 'causante.cedula', maxLen: 10 },
        { name: 'representante.cedula', maxLen: 10 },
        { name: 'acta_defuncion.year_acta', maxLen: 4 },
        { name: 'prorroga.plazo_dias', maxLen: 4 },
        { name: 'cedula', maxLen: 10 },           // modal heredero
        { name: 'numero_cuenta', maxLen: 20 },     // banco
        { name: 'numero_tdc', maxLen: 20 },        // TDC deuda
        { name: 'numero_prima', maxLen: 20 },      // seguro
        { name: 'numero_serie', maxLen: 20 },      // bonos
        { name: 'plazo_otorgado_dias', maxLen: 4 }, // prórroga modal
    ].forEach(({ name, maxLen }) => {
        const el = constrain(sel(name));
        if (!el) return;
        if (maxLen) el.setAttribute('maxlength', maxLen);
        el.addEventListener('input', () => {
            el.value = el.value.replace(/\D/g, '');
        });
    });

    // — Enteros sin decimales (bloquear . , e) —
    ['config.max_intentos', 'anio', 'numero_bonos', 'cantidad'].forEach(name => {
        const el = constrain(sel(name));
        if (!el) return;
        if (name === 'config.max_intentos') {
            el.setAttribute('step', '1');
            el.setAttribute('min', '0');
            el.setAttribute('max', '99');
        }
        if (name === 'anio') {
            el.setAttribute('min', '1900');
            el.setAttribute('max', new Date().getFullYear().toString());
        }
        if (name === 'cantidad' || name === 'numero_bonos') {
            el.setAttribute('min', '1');
        }
        el.addEventListener('keydown', (e) => {
            if (e.key === '.' || e.key === ',' || e.key === 'e' || e.key === 'E') {
                e.preventDefault();
            }
        });
    });

    // — Fechas con max=hoy (no futuras) —
    [
        'causante.fecha_nacimiento', 'causante.fecha_fallecimiento',
        'datos_fiscales_causante.fecha_cierre_fiscal',
        'representante.fecha_nacimiento',
        'prorroga.fecha_solicitud', 'prorroga.fecha_resolucion',
        'fecha_nacimiento', 'fecha_fallecimiento',  // modal heredero
        'fecha_registro',                            // inmueble
        'fecha_solicitud', 'fecha_resolucion',       // prórroga modal
    ].forEach(name => {
        const el = constrain(sel(name), '_dateConstrained');
        if (!el) return;
        el.setAttribute('max', today);
    });

    // — Teléfonos (solo dígitos y guión, max 12) —
    [
        'domicilio_causante.telefono_fijo',
        'domicilio_causante.telefono_celular',
        'domicilio_causante.fax',
    ].forEach(name => {
        const el = constrain(sel(name));
        if (!el) return;
        el.setAttribute('maxlength', '12');
        el.setAttribute('placeholder', '0XXX-XXXXXXX');
        el.addEventListener('input', () => {
            el.value = el.value.replace(/[^\d-]/g, '');
        });
    });

    // — Longitud máxima en campos de texto libre —
    [
        { name: 'caso.titulo', max: 255 },
        { name: 'caso.descripcion', max: 1000 },
        { name: 'descripcion', max: 500 },    // modal descriptions
        { name: 'linderos', max: 500 },        // inmueble linderos
    ].forEach(({ name, max }) => {
        const el = constrain(sel(name));
        if (!el) return;
        el.setAttribute('maxlength', max);
    });

    // — Valores monetarios: solo números y punto decimal —
    [
        'valor_declarado', 'valor_original',
        'superficie_construida', 'superficie_no_construida', 'area_superficie',
    ].forEach(name => {
        const el = constrain(sel(name));
        if (!el) return;
        el.addEventListener('input', () => {
            el.value = el.value.replace(/[^\d.]/g, '');
            const parts = el.value.split('.');
            if (parts.length > 2) el.value = parts[0] + '.' + parts.slice(1).join('');
        });
    });

    // — Porcentaje: 0-100 —
    ['porcentaje'].forEach(name => {
        const el = constrain(sel(name));
        if (!el) return;
        el.setAttribute('min', '0');
        el.setAttribute('max', '100');
        el.setAttribute('step', '0.01');
    });

    // — RIF Empresa: J + 9 dígitos —
    const rifEl = constrain(sel('rif_empresa'));
    if (rifEl) {
        rifEl.setAttribute('maxlength', '10');
    }

    // ================================================================
    // Relaciones cruzadas entre fechas (min/max dinámicos)
    // ================================================================

    /**
     * Vincula dos campos de fecha: cuando cambia `fromEl`, ajusta `min` en `toEl`.
     * Opcionalmente limpia toEl si su valor queda por debajo del nuevo min.
     */
    function linkDates(fromEl, toEl) {
        if (!fromEl || !toEl || fromEl._linkedTo === toEl) return;
        fromEl._linkedTo = toEl;
        const sync = () => {
            if (fromEl.value) {
                toEl.setAttribute('min', fromEl.value);
                if (toEl.value && toEl.value < fromEl.value) {
                    toEl.value = '';
                }
            } else {
                toEl.removeAttribute('min');
            }
        };
        fromEl.addEventListener('change', sync);
        sync(); // apply immediately with current value
    }

    // — Causante: fallecimiento ≥ nacimiento —
    linkDates(sel('causante.fecha_nacimiento'), sel('causante.fecha_fallecimiento'));

    // — Modal heredero / premuerto: fallecimiento ≥ nacimiento —
    linkDates(sel('fecha_nacimiento'), sel('fecha_fallecimiento'));

    // — Prórroga (static): resolución ≥ solicitud, vencimiento ≥ resolución —
    linkDates(sel('prorroga.fecha_solicitud'), sel('prorroga.fecha_resolucion'));
    linkDates(sel('prorroga.fecha_resolucion'), sel('prorroga.fecha_vencimiento'));

    // — Prórroga (modal): resolución ≥ solicitud, vencimiento ≥ resolución —
    linkDates(sel('fecha_solicitud'), sel('fecha_resolucion'));
    linkDates(sel('fecha_resolucion'), sel('fecha_vencimiento'));

    // — Representante: debe ser mayor de 18 años —
    const repNacEl = sel('representante.fecha_nacimiento');
    if (repNacEl && !repNacEl._age18) {
        repNacEl._age18 = true;
        const d18 = new Date();
        d18.setFullYear(d18.getFullYear() - 18);
        repNacEl.setAttribute('max', d18.toISOString().slice(0, 10));
    }

    // — Acta de defunción: año del acta ≥ año de fallecimiento —
    const fechaFall = sel('causante.fecha_fallecimiento');
    const yearActa = sel('acta_defuncion.year_acta');
    if (fechaFall && yearActa && !fechaFall._linkedActa) {
        fechaFall._linkedActa = true;
        const syncActaMin = () => {
            if (fechaFall.value) {
                const minYear = new Date(fechaFall.value).getFullYear();
                yearActa.setAttribute('min', minYear);
                if (yearActa.value && parseInt(yearActa.value) < minYear) {
                    yearActa.value = '';
                }
            }
        };
        fechaFall.addEventListener('change', syncActaMin);
        syncActaMin();
    }

    // ================================================================
    // Avisos de cédula duplicada (representante — sin botón de guardar)
    // ================================================================
    const repCedEl = sel('representante.cedula');
    if (repCedEl && !repCedEl._cedWarn) {
        repCedEl._cedWarn = true;
        const checkRepCed = () => {
            const val = repCedEl.value.trim();
            if (!val) return;
            const repLetraEl = sel('representante.letra_cedula');
            const repLetra = repLetraEl ? repLetraEl.value : '';
            const fullRepCed = repLetra + val;
            // No puede ser igual al causante
            if (caseData.causante.cedula) {
                const causanteCed = (caseData.causante.tipo_cedula || '') + caseData.causante.cedula;
                if (fullRepCed === causanteCed) {
                    showToast('La cédula del representante no puede ser igual a la del causante.');
                    repCedEl.value = '';
                    return;
                }
            }
            // Aviso si coincide con heredero
            const dupH = caseData.herederos.some(h => (h.letra_cedula || '') + (h.cedula || '') === fullRepCed);
            const dupHP = caseData.herederos_premuertos.some(h => (h.letra_cedula || '') + (h.cedula || '') === fullRepCed);
            if (dupH || dupHP) {
                showToast('La cédula del representante coincide con la de un heredero registrado.');
            }
        };
        repCedEl.addEventListener('change', checkRepCed);
        // También escuchar cambios en la letra del representante
        const repLetraEl = sel('representante.letra_cedula');
        if (repLetraEl) repLetraEl.addEventListener('change', checkRepCed);
    }
}

/**
 * Inicializa todas las restricciones de campo al cargar la página,
 * y observa el modal para aplicarlas cuando se inyecta contenido.
 */
function initFieldConstraints() {
    // Aplicar a todos los campos estáticos del formulario
    applyConstraints(document);

    // Observar el modal para aplicar restricciones a campos dinámicos
    const modalBody = document.getElementById('modalBody');
    if (modalBody) {
        const observer = new MutationObserver(() => {
            applyConstraints(modalBody);
        });
        observer.observe(modalBody, { childList: true, subtree: true });
    }
}

// ====================================================================
// Validación pre-publicación
// ====================================================================

function validateBeforePublish() {
    const errors = [];
    const c = caseData;

    // Helpers
    const calcAge = (birth, ref) => {
        const b = new Date(birth), r = ref ? new Date(ref) : new Date();
        let age = r.getFullYear() - b.getFullYear();
        const m = r.getMonth() - b.getMonth();
        if (m < 0 || (m === 0 && r.getDate() < b.getDate())) age--;
        return age;
    };
    const isValidName = (v) => /[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ]/.test(v);

    // Sección 1: Caso
    if (!c.caso.titulo?.trim()) errors.push('Título del caso');
    if ((c.caso.titulo || '').length > 255) errors.push('El título no puede exceder 255 caracteres');
    if (!c.caso.descripcion?.trim()) errors.push('Descripción del caso');
    if ((c.caso.descripcion || '').length > 1000) errors.push('La descripción no puede exceder 1000 caracteres');
    if (!c.caso.tipo_sucesion) errors.push('Tipo de sucesión');

    // Causante
    if (!c.causante.nombres?.trim()) errors.push('Causante: Nombres');
    else if (!isValidName(c.causante.nombres)) errors.push('Causante: Nombres no puede contener solo espacios o números');
    if (!c.causante.apellidos?.trim()) errors.push('Causante: Apellidos');
    else if (!isValidName(c.causante.apellidos)) errors.push('Causante: Apellidos no puede contener solo espacios o números');
    if (!c.causante.sexo) errors.push('Causante: Sexo');
    if (!c.causante.estado_civil) errors.push('Causante: Estado civil');
    if (!c.causante.fecha_nacimiento) errors.push('Causante: Fecha de nacimiento');
    if (!c.causante.fecha_fallecimiento) errors.push('Causante: Fecha de fallecimiento');
    // #13 — Fallecimiento > nacimiento
    if (c.causante.fecha_nacimiento && c.causante.fecha_fallecimiento
        && c.causante.fecha_fallecimiento <= c.causante.fecha_nacimiento)
        errors.push('Causante: La fecha de fallecimiento debe ser posterior a la de nacimiento');
    // #14 — Edad razonable (0–130)
    if (c.causante.fecha_nacimiento && c.causante.fecha_fallecimiento
        && c.causante.fecha_fallecimiento > c.causante.fecha_nacimiento) {
        const edad = calcAge(c.causante.fecha_nacimiento, c.causante.fecha_fallecimiento);
        if (edad < 0 || edad > 130) errors.push('Causante: La edad resultante no es razonable (0–130 años)');
    }
    // Cédula según tipo sucesión
    if (c.caso.tipo_sucesion === 'Con Cédula' && !c.causante.cedula?.trim())
        errors.push('Causante: Cédula (requerida para sucesión Con Cédula)');
    if (c.caso.tipo_sucesion === 'Sin Cédula' && c.causante.cedula?.trim())
        errors.push('Causante: En sucesión Sin Cédula, el campo de cédula debe estar vacío');

    // Datos fiscales
    if (!c.datos_fiscales_causante.fecha_cierre_fiscal) errors.push('Datos fiscales: Fecha de cierre fiscal');
    // #21 — Cierre fiscal ≥ fallecimiento
    if (c.datos_fiscales_causante.fecha_cierre_fiscal && c.causante.fecha_fallecimiento
        && c.datos_fiscales_causante.fecha_cierre_fiscal < c.causante.fecha_fallecimiento)
        errors.push('Datos fiscales: La fecha de cierre fiscal debe ser posterior o igual a la de fallecimiento');

    // Herencia
    if (!c.herencia.tipos.length) {
        errors.push('Al menos un tipo de herencia');
    } else {
        const cats = getCatalogs();
        c.herencia.tipos.forEach(t => {
            const tipo = (cats.tiposHerencia || []).find(th => th.id == t.tipo_herencia_id);
            const nombre = tipo?.nombre?.toLowerCase() || '';
            if (nombre.includes('testamento')) {
                if (!t.subtipo_testamento) errors.push('Herencia Testamentaria: Subtipo');
                if (!t.fecha_testamento) errors.push('Herencia Testamentaria: Fecha');
            }
            if (nombre.includes('inventario')) {
                if (!t.fecha_conclusion_inventario) errors.push('Beneficio de Inventario: Fecha de conclusión');
            }
        });
    }

    // Representante
    if (!c.representante.nombres?.trim()) errors.push('Representante: Nombres');
    else if (!isValidName(c.representante.nombres)) errors.push('Representante: Nombres no puede contener solo espacios o números');
    if (!c.representante.apellidos?.trim()) errors.push('Representante: Apellidos');
    else if (!isValidName(c.representante.apellidos)) errors.push('Representante: Apellidos no puede contener solo espacios o números');
    if (!c.representante.cedula?.trim() && !c.representante.pasaporte?.trim()) errors.push('Representante: Cédula o Pasaporte');
    if (!c.representante.sexo) errors.push('Representante: Sexo');
    if (!c.representante.estado_civil) errors.push('Representante: Estado civil');
    if (!c.representante.fecha_nacimiento) errors.push('Representante: Fecha de nacimiento');
    // #33 — Representante ≥ 18
    if (c.representante.fecha_nacimiento) {
        const edad = calcAge(c.representante.fecha_nacimiento);
        if (edad < 18) errors.push('Representante: Debe ser mayor de 18 años');
    }

    // #18/#19 — Cédulas cruzadas
    const cedCausante = (c.causante.cedula || '').trim();
    const cedRep = (c.representante.cedula || '').trim();
    if (cedCausante && cedRep && cedCausante === cedRep)
        errors.push('La cédula del representante no puede ser igual a la del causante');

    // Domicilio fiscal
    if (!c.domicilio_causante.estado) errors.push('Domicilio fiscal del causante (dirección completa)');

    // Herederos + Premuertos — cédulas cruzadas
    if (!c.herederos.length) errors.push('Al menos un heredero');
    const allCedulas = [];
    c.herederos.forEach((h, i) => {
        const ced = (h.cedula || '').trim();
        if (ced && allCedulas.includes(ced)) errors.push(`Heredero #${i + 1}: Cédula duplicada`);
        if (ced) allCedulas.push(ced);
        if (cedCausante && ced && ced === cedCausante) errors.push(`Heredero #${i + 1}: Cédula igual a la del causante`);
        if (h.fecha_nacimiento) {
            const edad = calcAge(h.fecha_nacimiento);
            if (edad < 0 || edad > 150) errors.push(`Heredero #${i + 1}: Fecha de nacimiento no resulta en una edad razonable`);
        }
    });
    (c.herederos_premuertos || []).forEach((hp, i) => {
        const ced = (hp.cedula || '').trim();
        if (cedCausante && ced && ced === cedCausante) errors.push(`Heredero premuerto #${i + 1}: Cédula igual a la del causante`);
        if (ced && allCedulas.includes(ced)) errors.push(`Heredero premuerto #${i + 1}: Cédula duplicada con un heredero`);
        if (ced) allCedulas.push(ced);
    });

    // Bienes
    const tieneInmuebles = c.bienes_inmuebles.length > 0;
    const tieneMuebles = Object.values(c.bienes_muebles).some(arr => Array.isArray(arr) && arr.length > 0);
    if (!tieneInmuebles && !tieneMuebles) errors.push('Al menos un bien (inmueble o mueble)');

    // Config
    if (!c.config.modalidad) errors.push('Modalidad de asignación');
    const maxInt = c.config.max_intentos;
    if (maxInt === '' || maxInt === null || parseInt(maxInt) < 0)
        errors.push('Número máximo de intentos (no puede ser negativo)');
    else if (String(maxInt).includes('.') || !Number.isInteger(Number(maxInt)))
        errors.push('Número máximo de intentos debe ser un número entero');
    else if (parseInt(maxInt) > 99)
        errors.push('Número máximo de intentos no puede exceder 99');
    if (!c.config.tipo_asignacion) errors.push('Tipo de asignación');
    if (c.config.tipo_asignacion === 'Seccion' && !c.config.seccion_id) errors.push('Sección asignada');
    if (c.config.tipo_asignacion === 'Estudiantes' && !c.estudiantes_asignados.length) errors.push('Al menos un estudiante asignado');
    // #56 — Fecha límite futura
    if (c.config.modalidad === 'Evaluacion' && c.config.fecha_limite && c.config.fecha_limite < new Date().toISOString().slice(0, 10))
        errors.push('Configuración: La fecha límite debe ser una fecha futura');

    return errors;
}

/**
 * Muestra un popup centrado con la lista de errores de validación.
 */
function showValidationPopup(errors, title = 'No se puede publicar') {
    // Remove any existing popup
    const existing = document.getElementById('ccValidationPopup');
    if (existing) existing.remove();

    const overlay = document.createElement('div');
    overlay.id = 'ccValidationPopup';
    overlay.className = 'cc-modal-overlay is-open';
    overlay.style.zIndex = '500';

    const errorItems = errors.map(e =>
        `<li>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--cc-red)" stroke-width="2.5" stroke-linecap="round">
                <circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
            </svg>
            <span>${e}</span>
        </li>`
    ).join('');

    overlay.innerHTML = `
        <div class="cc-modal" style="width:520px;">
            <div class="cc-modal__header" style="border-bottom:1px solid var(--cc-slate-200);padding:20px 24px;">
                <div style="display:flex;align-items:center;gap:10px;">
                    <div style="width:40px;height:40px;border-radius:10px;background:var(--cc-red-light);display:flex;align-items:center;justify-content:center;">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--cc-red)" stroke-width="2" stroke-linecap="round">
                            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                            <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                        </svg>
                    </div>
                    <div>
                        <h3 style="margin:0;font-size:16px;font-weight:700;color:var(--cc-slate-800);">${title}</h3>
                        <p style="margin:2px 0 0;font-size:13px;color:var(--cc-slate-500);">Completa los siguientes campos antes de publicar:</p>
                    </div>
                </div>
            </div>
            <div class="cc-modal__body" style="padding:16px 24px;max-height:50vh;overflow-y:auto;">
                <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:8px;">
                    ${errorItems}
                </ul>
            </div>
            <div class="cc-modal__footer" style="padding:16px 24px;border-top:1px solid var(--cc-slate-200);display:flex;justify-content:flex-end;">
                <button class="cc-btn cc-btn--primary" id="ccValidationClose" style="min-width:120px;">Entendido</button>
            </div>
        </div>
    `;

    document.body.appendChild(overlay);

    // Style the list items
    overlay.querySelectorAll('li').forEach(li => {
        li.style.cssText = 'display:flex;align-items:center;gap:10px;padding:8px 12px;border-radius:8px;background:var(--cc-red-light);font-size:13.5px;color:var(--cc-slate-700);';
    });

    // Close handlers
    const closePopup = () => overlay.remove();
    overlay.querySelector('#ccValidationClose').addEventListener('click', closePopup);
    overlay.addEventListener('click', (e) => { if (e.target === overlay) closePopup(); });
}

// Asignamos callbacks globales para el HTML onClick
window.CC = {
    nextStep, prevStep, setStep,
    openModal, closeModal, saveModal,
    removeItem, removeMueble,
    saveDireccion, editDireccion, deleteDireccion,
    saveProrroga, deleteProrroga, editProrroga,
    publish: () => {
        const errors = validateBeforePublish();
        if (errors.length > 0) {
            showValidationPopup(errors);
            return;
        }
        submitCase('Publicado');
    }
};

// Global helper access for state changes
document.ccHelpers = { $, show: (el) => { if (el) el.style.display = ''; }, hide: (el) => { if (el) el.style.display = 'none'; } };

function bindInputs() {
    $$('[data-bind]').forEach(el => {
        const [section, key] = el.dataset.bind.split('.');

        // Set initial value
        if (caseData[section] && caseData[section][key] !== undefined) {
            if (el.type === 'radio') {
                el.checked = (el.value === String(caseData[section][key]));
            } else if (el.type === 'checkbox') {
                el.checked = Boolean(caseData[section][key]);
            } else {
                el.value = caseData[section][key];
            }
        }

        // Listen for changes
        const handler = () => {
            if (caseData[section]) {
                if (el.type === 'radio') {
                    if (el.checked) caseData[section][key] = el.value;
                } else if (el.type === 'checkbox') {
                    caseData[section][key] = el.checked;
                } else {
                    caseData[section][key] = el.value;
                }
            }
        };

        el.addEventListener('input', handler);
        el.addEventListener('change', handler);
    });
}

function initCollapsibles() {
    $$('.cc-card--collapsible').forEach(card => {
        const header = card.querySelector('.cc-card__toggle');
        const body = card.querySelector('.cc-card__collapse');

        if (!header) return;

        // Sync initial state (if body is visible, it should have 'is-open' class)
        if (body) {
            const isVisible = getComputedStyle(body).display !== 'none' && body.style.display !== 'none';
            if (isVisible) {
                card.classList.add('is-open');
            } else {
                card.classList.remove('is-open');
            }
        }

        // Clone node to drop previously attached listeners (prevent multiple triggers)
        const newHeader = header.cloneNode(true);
        header.parentNode.replaceChild(newHeader, header);

        newHeader.addEventListener('click', () => {
            card.classList.toggle('is-open');
            if (body) {
                body.style.display = card.classList.contains('is-open') ? '' : 'none';
            }
        });
    });
}

function initTabs() {
    $$('.cc-tab').forEach(tab => {
        tab.addEventListener('click', () => {
            $$('.cc-tab').forEach(t => t.classList.remove('is-active'));
            $$('.cc-tab-panel').forEach(p => { if (p) p.style.display = 'none'; });
            tab.classList.add('is-active');
            const panel = $(`#panel-${tab.dataset.tab}`);
            if (panel) panel.style.display = '';
        });
    });
}

function renderSelects() {
    const cats = getCatalogs();

    // Nacionalidad
    const nacCausante = document.querySelector('select[data-bind="causante.nacionalidad"]');
    const nacRep = document.querySelector('select[data-bind="representante.nacionalidad"]');
    const paisesHtml = '<option value="">Seleccione...</option>' +
        cats.paises.map(p => `<option value="${p.id}">${p.nombre}</option>`).join('');

    if (nacCausante) {
        nacCausante.innerHTML = paisesHtml;
        // Restaurar valor del state después de reemplazar innerHTML
        if (caseData.causante.nacionalidad) nacCausante.value = caseData.causante.nacionalidad;
    }
    if (nacRep) {
        nacRep.innerHTML = paisesHtml;
        if (caseData.representante.nacionalidad) nacRep.value = caseData.representante.nacionalidad;
    }
}

async function init() {
    // Detect edit mode from URL
    const params = new URLSearchParams(window.location.search);
    const editId = params.get('edit');
    let isEditMode = false;

    if (editId) {
        // Edit mode: fetch case data from server
        try {
            const baseUrl = (window.BASE_URL || '/tesis_francisco/public').replace(/\/+$/, '');
            const res = await fetch(baseUrl + '/api/casos/' + editId);
            const json = await res.json();

            if (res.ok && json.success && json.data) {
                clearSavedCaseData(); // Clear any stale sessionStorage
                hydrateCaseData(json.data);
                isEditMode = true;

                // Update page title and breadcrumb
                const titleEl = $('.cc-topbar__title');
                if (titleEl) titleEl.textContent = 'Editar Caso';
                const badgeEl = $('.cc-badge');
                if (badgeEl) {
                    badgeEl.textContent = json.estado === 'Publicado' ? 'Publicado' : 'Editando';
                    badgeEl.className = 'cc-badge cc-badge--blue';
                }
            } else {
                showToast(json.message || 'No se pudo cargar el caso para editar.');
            }
        } catch (err) {
            showToast('Error de red al cargar el caso: ' + err.message);
        }
    }

    // Show toast if coming from saving a draft (only in non-edit mode)
    if (!editId && params.get('borrador') === 'ok') {
        showToast('Borrador guardado exitosamente.', 'success');
        history.replaceState(null, '', window.location.pathname);
    }

    // Restore saved data only on page RELOAD (F5), not on fresh navigation
    let hadSavedData = false;
    if (!isEditMode) {
        const navEntry = performance.getEntriesByType('navigation')[0];
        const isPageReload = navEntry && navEntry.type === 'reload';
        if (isPageReload) {
            hadSavedData = loadCaseData();
        } else {
            clearSavedCaseData();
        }
    } else {
        hadSavedData = true;
    }

    // 1. Cargar catálogos PRIMERO (antes de bindInputs para que los selects tengan opciones)
    await initCatalogos();
    renderSelects();
    loadSeccionesSelect();

    // 2. Ahora bindInputs puede poner valores en selects que ya tienen opciones
    bindInputs();
    initFieldConstraints();
    initCollapsibles();
    initTabs();
    initStepperClicks();
    initStudentSearch();
    renderHerenciaCheckboxes();
    initRepresentanteLogic();
    initAddressListeners();

    // 3. Restaurar datos de dirección en cascada o cargar estados nuevos
    if (hadSavedData) {
        await restoreAddressCascade();
        // Re-render tablas dinámicas con datos restaurados
        renderHerederos();
        renderHerederosPremuertos();
        renderInventario();
    } else {
        fetchEstados();
    }

    renderDirecciones();
    renderProrrogas();

    // Restore step or start at 0
    setStep(hadSavedData ? UIState.currentStep : 0);

    // Auto-save before page unload
    window.addEventListener('beforeunload', () => saveCaseData());

    const btnSaveDraft = $('#btnSaveDraft');
    if (btnSaveDraft) {
        btnSaveDraft.addEventListener('click', () => {
            if (!caseData.caso || !caseData.caso.titulo || caseData.caso.titulo.trim() === '') {
                showToast('Ingrese el Título del Caso antes de guardar el borrador.');
                return;
            }
            submitCase('Borrador');
        });
    }
}

document.addEventListener('DOMContentLoaded', init);
