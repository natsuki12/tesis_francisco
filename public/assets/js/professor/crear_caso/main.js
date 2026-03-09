import { $, $$, showToast } from '../../global/utils.js';
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

    // Limpiar campos auxiliares del frontend que no van al backend
    delete payload.domicilio_causante; // Helper form → datos finales en direcciones_causante
    delete payload.prorroga;           // Helper form → datos finales en prorrogas
    // Solo quitar _locked_fields al publicar, los borradores los necesitan para restaurar estado
    if (modo === 'Publicado') {
        if (payload.causante) delete payload.causante._locked_fields;
        if (payload.representante) delete payload.representante._locked_fields;
        (payload.herederos || []).forEach(h => delete h._locked_fields);
        (payload.herederos_premuertos || []).forEach(h => delete h._locked_fields);
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

import { initCatalogos, getCatalogs } from '../../global/catalogos.js';
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
    ['anio', 'numero_bonos', 'cantidad'].forEach(name => {
        const el = constrain(sel(name));
        if (!el) return;

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

    // — Valores monetarios y porcentaje: solo números y punto decimal (acepta coma como separador) —
    [
        'valor_declarado', 'valor_original',
        'superficie_construida', 'superficie_no_construida', 'area_superficie',
        'porcentaje',
    ].forEach(name => {
        const el = constrain(sel(name));
        if (!el) return;
        el.addEventListener('input', (e) => {
            // Guardar posición inicial del cursor
            const start = el.selectionStart;
            const end = el.selectionEnd;
            const lengthBefore = el.value.length;

            // 1. Reemplazar comas por puntos
            let val = el.value.replace(/,/g, '.');

            // 2. Eliminar cualquier caracter que no sea dígito o punto
            val = val.replace(/[^0-9.]/g, '');

            // 3. Prevenir múltiples puntos
            const parts = val.split('.');
            if (parts.length > 2) {
                val = parts[0] + '.' + parts.slice(1).join('');
            }

            el.value = val;

            // Restaurar cursor intentando compensar cambios de longitud
            const lengthAfter = el.value.length;
            const diff = lengthAfter - lengthBefore;

            // Si el valor original tenía coma y lo cambiamos a punto, el cursor avanza al punto
            if (e.inputType === 'insertText' && e.data === ',') {
                el.setSelectionRange(start, end);
            } else {
                el.setSelectionRange(start + diff, end + diff);
            }
        });
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
                    setTimeout(() => {
                        showToast('La segunda fecha fue limpiada porque no puede ser anterior a la primera fecha introducida.', 'warning');
                    }, 300);
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

    // — Fecha de cierre fiscal: min = fecha_fallecimiento, max = 31/12 del año de fallecimiento —
    const cierreInput = $('#input_fecha_cierre_fiscal');
    const fallecInput = $('[data-bind="causante.fecha_fallecimiento"]');
    if (cierreInput && fallecInput) {
        const updateCierreConstraints = () => {
            const fallec = fallecInput.value;
            if (fallec) {
                const year = new Date(fallec).getFullYear();
                cierreInput.min = fallec;
                cierreInput.max = `${year}-12-31`;
            } else {
                cierreInput.min = '';
                cierreInput.max = '';
            }
            // Validar valor actual contra nuevos límites
            if (cierreInput.value) {
                if (cierreInput.min && cierreInput.value < cierreInput.min) {
                    showToast('La fecha de cierre fiscal no puede ser anterior a la fecha de fallecimiento.');
                    cierreInput.value = '';
                    caseData.datos_fiscales_causante.fecha_cierre_fiscal = '';
                } else if (cierreInput.max && cierreInput.value > cierreInput.max) {
                    showToast('La fecha de cierre fiscal no puede ser posterior al 31/12 del año de fallecimiento.');
                    cierreInput.value = '';
                    caseData.datos_fiscales_causante.fecha_cierre_fiscal = '';
                }
            }
        };
        // Actualizar cuando cambia fecha de fallecimiento
        fallecInput.addEventListener('change', updateCierreConstraints);
        // Validar al seleccionar fecha de cierre fiscal
        cierreInput.addEventListener('change', () => {
            const fallec = fallecInput.value || caseData.causante.fecha_fallecimiento;
            if (fallec) {
                const year = new Date(fallec).getFullYear();
                if (cierreInput.value < fallec) {
                    showToast('La fecha de cierre fiscal no puede ser anterior a la fecha de fallecimiento.');
                    cierreInput.value = '';
                    caseData.datos_fiscales_causante.fecha_cierre_fiscal = '';
                } else if (cierreInput.value > `${year}-12-31`) {
                    showToast('La fecha de cierre fiscal no puede ser posterior al 31/12 del año de fallecimiento.');
                    cierreInput.value = '';
                    caseData.datos_fiscales_causante.fecha_cierre_fiscal = '';
                }
            }
        });
        // Aplicar restricciones iniciales (datos restaurados de sessionStorage)
        updateCierreConstraints();
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

    // Acta de defunción (obligatoria solo para sucesión Sin Cédula)
    if (c.caso.tipo_sucesion === 'Sin Cédula') {
        if (!c.acta_defuncion.numero_acta?.trim()) errors.push('Acta de Defunción: Número de acta');
        if (!c.acta_defuncion.year_acta) errors.push('Acta de Defunción: Año del acta');
        else if (parseInt(c.acta_defuncion.year_acta) < 1900) errors.push('Acta de Defunción: El año debe ser ≥ 1900');
        if (!c.acta_defuncion.parroquia_registro_id) errors.push('Acta de Defunción: Parroquia de registro');
    }

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
                if (!t.fecha_testamento) errors.push('Herencia Testamentaria: Fecha del testamento');
                // Fecha del testamento no puede ser posterior al fallecimiento
                if (t.fecha_testamento && c.causante.fecha_fallecimiento
                    && t.fecha_testamento > c.causante.fecha_fallecimiento)
                    errors.push('Herencia Testamentaria: La fecha del testamento no puede ser posterior a la fecha de fallecimiento del causante');
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

    // Domicilio fiscal — debe haber al menos una dirección guardada
    if (!c.direcciones_causante || c.direcciones_causante.length === 0)
        errors.push('Debe agregar al menos una dirección de domicilio fiscal del causante');

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

    // Validar que cada heredero premuerto tenga al menos un heredero del premuerto
    c.herederos.forEach((h, i) => {
        if (h.premuerto === 'SI') {
            const ced = (h.cedula || '').trim();
            const tieneSubHerederos = (c.herederos_premuertos || []).some(hp => hp.premuerto_padre_id === ced);
            if (!tieneSubHerederos) {
                errors.push(`Heredero #${i + 1} (${h.nombres || ''} ${h.apellidos || ''}): Está marcado como premuerto pero no tiene herederos del premuerto asignados`);
            }
        }
    });

    // Bienes
    const tieneInmuebles = c.bienes_inmuebles.length > 0;
    const tieneMuebles = Object.values(c.bienes_muebles).some(arr => Array.isArray(arr) && arr.length > 0);
    if (!tieneInmuebles && !tieneMuebles) errors.push('Al menos un bien (inmueble o mueble)');



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
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--red-600)" stroke-width="2.5" stroke-linecap="round">
                <circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
            </svg>
            <span>${e}</span>
        </li>`
    ).join('');

    overlay.innerHTML = `
        <div class="cc-modal" style="width:520px;">
            <div class="cc-modal__header" style="border-bottom:1px solid var(--gray-200);padding:20px 24px;">
                <div style="display:flex;align-items:center;gap:10px;">
                    <div style="width:40px;height:40px;border-radius:10px;background:var(--red-50);display:flex;align-items:center;justify-content:center;">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--red-600)" stroke-width="2" stroke-linecap="round">
                            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                            <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                        </svg>
                    </div>
                    <div>
                        <h3 style="margin:0;font-size:16px;font-weight:700;color:var(--gray-800);">${title}</h3>
                        <p style="margin:2px 0 0;font-size:13px;color:var(--gray-500);">Completa los siguientes campos antes de publicar:</p>
                    </div>
                </div>
            </div>
            <div class="cc-modal__body" style="padding:16px 24px;max-height:50vh;overflow-y:auto;">
                <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:8px;">
                    ${errorItems}
                </ul>
            </div>
            <div class="cc-modal__footer" style="padding:16px 24px;border-top:1px solid var(--gray-200);display:flex;justify-content:flex-end;">
                <button class="btn btn-primary" id="ccValidationClose" style="min-width:120px;">Entendido</button>
            </div>
        </div>
    `;

    document.body.appendChild(overlay);

    // Style the list items
    overlay.querySelectorAll('li').forEach(li => {
        li.style.cssText = 'display:flex;align-items:center;gap:10px;padding:8px 12px;border-radius:8px;background:var(--red-50);font-size:13.5px;color:var(--gray-700);';
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

function initCausanteAutocomplete() {
    const inputCedulaCausante = $('[data-bind="causante.cedula"]');
    const selectTipoCedulaCausante = $('[data-bind="causante.tipo_cedula"]');

    if (inputCedulaCausante && selectTipoCedulaCausante) {
        // Función auxiliar para vaciar y habilitar solo los campos que fueron auto-rellenados desde la BD
        const clearAndEnableAll = () => {
            const lockedFields = caseData.causante._locked_fields || [];
            lockedFields.forEach(f => {
                if (caseData.causante[f] !== undefined) {
                    caseData.causante[f] = '';
                }
                const el = $(`[data-bind="causante.${f}"]`);
                if (el) {
                    el.value = '';
                    el.disabled = false;
                    el.style.backgroundColor = '';
                }
            });
            // Also clear persona_id since the person was unlinked
            caseData.causante.persona_id = '';
            const elPid = $('[data-bind="causante.persona_id"]');
            if (elPid) { elPid.value = ''; }
            caseData.causante._locked_fields = [];
        };

        const fetchCausante = async () => {
            const cedula = inputCedulaCausante.value.trim();
            const tipo = selectTipoCedulaCausante.value;

            if (!cedula || cedula.length < 6 || caseData.caso.tipo_sucesion !== 'Con Cédula') {
                if (cedula.length === 0 && caseData.causante.nombres) {
                    clearAndEnableAll();
                }
                return;
            }

            if (!tipo) {
                if (caseData.causante.nombres) {
                    clearAndEnableAll();
                }
                return;
            }

            try {
                const baseUrl = (window.BASE_URL || '/tesis_francisco/public').replace(/\/+$/, '');
                const resp = await fetch(`${baseUrl}/api/buscar-persona?tipo=${tipo}&cedula=${cedula}`);
                const json = await resp.json();

                if (json.success && json.data) {
                    const data = json.data;
                    // Update state
                    if (!tipo && data.tipo_cedula) {
                        caseData.causante.tipo_cedula = data.tipo_cedula;
                        if (selectTipoCedulaCausante) selectTipoCedulaCausante.value = data.tipo_cedula;
                    }

                    caseData.causante.persona_id = data.persona_id; // Store ID for cross-validation
                    caseData.causante.nombres = data.nombres;
                    caseData.causante.apellidos = data.apellidos;
                    caseData.causante.fecha_nacimiento = data.fecha_nacimiento;
                    caseData.causante.sexo = data.sexo;

                    const normalizedEstadoCivil = data.estado_civil ? data.estado_civil.toLowerCase().replace('_', ' ') : '';
                    if (normalizedEstadoCivil === 'no aplica') {
                        caseData.causante.estado_civil = '';
                    } else {
                        caseData.causante.estado_civil = data.estado_civil;
                    }

                    caseData.causante.nacionalidad = data.nacionalidad;
                    if (data.fecha_fallecimiento) {
                        caseData.causante.fecha_fallecimiento = data.fecha_fallecimiento;
                    }

                    // Función auxiliar para asignar valor y deshabilitar
                    const fillAndDisable = (selector, value, forceEnable = false) => {
                        const el = $(selector);
                        if (el && value !== undefined && value !== null) {
                            if (forceEnable) {
                                el.value = '';
                                el.disabled = false;
                                el.style.backgroundColor = '';
                            } else {
                                el.value = value;
                                el.disabled = true;
                                // Add a subtle style to indicate it's auto-filled and locked
                                el.style.backgroundColor = 'var(--cc-slate-50, #f8fafc)';
                            }
                        }
                    };

                    // Update DOM directly since bindInputs runs once
                    fillAndDisable('[data-bind="causante.nombres"]', data.nombres);
                    fillAndDisable('[data-bind="causante.apellidos"]', data.apellidos);
                    fillAndDisable('[data-bind="causante.fecha_nacimiento"]', data.fecha_nacimiento);
                    fillAndDisable('[data-bind="causante.sexo"]', data.sexo);

                    const isEstadoCivilNoAplica = (normalizedEstadoCivil === 'no aplica');
                    fillAndDisable('[data-bind="causante.estado_civil"]', data.estado_civil, isEstadoCivilNoAplica);

                    fillAndDisable('[data-bind="causante.nacionalidad"]', data.nacionalidad);
                    if (data.fecha_fallecimiento) {
                        fillAndDisable('[data-bind="causante.fecha_fallecimiento"]', data.fecha_fallecimiento);
                    }

                    showToast('Datos del causante autocompletados', 'success');
                    // Guardar cuáles campos específicos se deshabilitaron
                    const lockedFields = ['nombres', 'apellidos', 'fecha_nacimiento', 'sexo', 'estado_civil', 'nacionalidad', 'fecha_fallecimiento']
                        .filter(f => data[f] && !(f === 'estado_civil' && isEstadoCivilNoAplica));
                    caseData.causante._locked_fields = lockedFields;
                    // Re-renderizar herencia para actualizar max de fecha_testamento
                    renderHerenciaCheckboxes();
                } else {
                    // Si consultamos y no existe, limpiamos en caso de que hubiese otra antes
                    clearAndEnableAll();
                }
            } catch (err) {
                console.error("Error buscando persona", err);
            }
        };

        inputCedulaCausante.addEventListener('input', fetchCausante);
        selectTipoCedulaCausante.addEventListener('change', fetchCausante);
    }
}

function initRepresentanteAutocomplete() {
    const inputCedulaRep = $('[data-bind="representante.cedula"]');
    const selectLetraRep = $('[data-bind="representante.letra_cedula"]');
    const inputPasaporteRep = $('[data-bind="representante.pasaporte"]');
    const radiosTipoDoc = $$('[name="rep_tipo_doc"]');

    const clearAndEnableAll = () => {
        radiosTipoDoc.forEach(r => r.disabled = false);
        const lockedFields = caseData.representante._locked_fields || [];
        lockedFields.forEach(f => {
            if (caseData.representante[f] !== undefined) {
                caseData.representante[f] = '';
            }
            const el = $(`[data-bind="representante.${f}"]`);
            if (el) {
                el.value = '';
                el.disabled = false;
                el.style.backgroundColor = '';
            }
        });
        // Also clear persona_id since the person was unlinked
        caseData.representante.persona_id = '';
        caseData.representante._locked_fields = [];
    };

    const fetchRepresentante = async (isPasaporte = false) => {
        let url = '';
        const baseUrl = (window.BASE_URL || '/tesis_francisco/public').replace(/\/+$/, '');

        const isPasaporteRadio = Array.from(radiosTipoDoc).find(r => r.checked)?.value === 'Pasaporte';

        if (isPasaporteRadio || isPasaporte) {
            const pasaporte = inputPasaporteRep ? inputPasaporteRep.value.trim() : '';
            if (!pasaporte || pasaporte.length < 5) {
                if (pasaporte.length === 0 && caseData.representante.nombres) clearAndEnableAll();
                return;
            }
            url = `${baseUrl}/api/buscar-persona?pasaporte=${pasaporte}`;
        } else {
            const cedula = inputCedulaRep ? inputCedulaRep.value.trim() : '';
            const tipo = selectLetraRep ? selectLetraRep.value : '';
            const isRif = Array.from(radiosTipoDoc).find(r => r.checked)?.value === 'Rif';

            if (!cedula || cedula.length < 6) {
                if (cedula.length === 0 && caseData.representante.nombres) clearAndEnableAll();
                return;
            }

            if (!tipo) {
                if (caseData.representante.nombres) clearAndEnableAll();
                return;
            }

            if (isRif) {
                url = `${baseUrl}/api/buscar-persona?rif=${tipo}${cedula}`;
            } else {
                url = `${baseUrl}/api/buscar-persona?tipo=${tipo}&cedula=${cedula}`;
            }
        }

        try {
            const resp = await fetch(url);
            const json = await resp.json();

            if (json.success && json.data) {
                const data = json.data;

                // Validar que el representante no sea el mismo causante
                let isSamePerson = false;
                if (caseData.causante) {
                    const isRepRif = Array.from(radiosTipoDoc).find(r => r.checked)?.value === 'Rif';

                    if (isRepRif) {
                        // Representative is RIF (data.rif_personal will be populated like "V12345678")
                        // Does it match Causante's explicitly loaded RIF?
                        if (data.rif_personal && caseData.causante.rif_personal && data.rif_personal === caseData.causante.rif_personal) {
                            isSamePerson = true;
                        }
                        // Did Causante enter their RIF into the Cédula field instead?
                        if (caseData.causante.cedula && data.rif_personal && (caseData.causante.tipo_cedula + caseData.causante.cedula) === data.rif_personal) {
                            isSamePerson = true;
                        }
                    } else if (isPasaporteRadio) {
                        // Check by pasaporte
                        if (data.pasaporte && caseData.causante.pasaporte && data.pasaporte === caseData.causante.pasaporte) {
                            isSamePerson = true;
                        }
                    } else {
                        // Representative is Cédula
                        if (caseData.causante.cedula && data.cedula && data.cedula === caseData.causante.cedula && data.tipo_cedula === caseData.causante.tipo_cedula) {
                            isSamePerson = true;
                        }
                    }

                    // ID match, using `persona_id` returned by CatalogModel
                    // Since causante doesn't always store persona_id, we only check if present
                    if (data.persona_id && caseData.causante.persona_id && data.persona_id === caseData.causante.persona_id) {
                        isSamePerson = true;
                    }
                }

                if (isSamePerson) {
                    showToast('El representante no puede ser el mismo causante.', 'error');
                    clearAndEnableAll();
                    return;
                }

                caseData.representante.persona_id = data.persona_id;
                caseData.representante.nombres = data.nombres;
                caseData.representante.apellidos = data.apellidos;
                caseData.representante.fecha_nacimiento = data.fecha_nacimiento;
                caseData.representante.sexo = data.sexo;

                const normalizedEstadoCivil = data.estado_civil ? data.estado_civil.toLowerCase().replace('_', ' ') : '';
                if (normalizedEstadoCivil === 'no aplica') {
                    caseData.representante.estado_civil = '';
                } else {
                    caseData.representante.estado_civil = data.estado_civil;
                }

                caseData.representante.nacionalidad = data.nacionalidad;

                const fillAndDisable = (selector, value, forceEnable = false) => {
                    const el = $(selector);
                    if (el && value !== undefined && value !== null) {
                        if (forceEnable) {
                            el.value = '';
                            el.disabled = false;
                            el.style.backgroundColor = '';
                        } else {
                            el.value = value;
                            el.disabled = true;
                            el.style.backgroundColor = 'var(--cc-slate-50, #f8fafc)';
                        }
                    }
                };

                fillAndDisable('[data-bind="representante.nombres"]', data.nombres);
                fillAndDisable('[data-bind="representante.apellidos"]', data.apellidos);
                fillAndDisable('[data-bind="representante.fecha_nacimiento"]', data.fecha_nacimiento);
                fillAndDisable('[data-bind="representante.sexo"]', data.sexo);

                const isEstadoCivilNoAplica = (normalizedEstadoCivil === 'no aplica');
                fillAndDisable('[data-bind="representante.estado_civil"]', data.estado_civil, isEstadoCivilNoAplica);

                fillAndDisable('[data-bind="representante.nacionalidad"]', data.nacionalidad);

                showToast('Datos del representante autocompletados', 'success');
                const lockedFieldsRep = ['nombres', 'apellidos', 'fecha_nacimiento', 'sexo', 'estado_civil', 'nacionalidad']
                    .filter(f => data[f] && !(f === 'estado_civil' && isEstadoCivilNoAplica));
                caseData.representante._locked_fields = lockedFieldsRep;
            } else {
                clearAndEnableAll();
            }
        } catch (err) {
            console.error("Error buscando persona", err);
        }
    };

    if (inputCedulaRep) {
        inputCedulaRep.addEventListener('input', () => fetchRepresentante(false));
    }
    if (selectLetraRep) {
        selectLetraRep.addEventListener('change', () => fetchRepresentante(false));
    }
    if (inputPasaporteRep) {
        inputPasaporteRep.addEventListener('input', () => fetchRepresentante(true));
    }
    if (radiosTipoDoc) {
        radiosTipoDoc.forEach(r => r.addEventListener('change', () => {
            fetchRepresentante(r.value === 'Pasaporte');
        }));
    }
}

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

function restoreLockedFields() {
    const lockStyle = 'var(--cc-slate-50, #f8fafc)';

    // Helper: inferir campos bloqueados a partir de los que tienen valor
    const inferLockedFields = (personData, fieldsList) => {
        return fieldsList.filter(f => {
            const val = personData[f];
            return val !== undefined && val !== null && val !== '';
        });
    };

    // Causante
    const causanteFieldsList = ['nombres', 'apellidos', 'fecha_nacimiento', 'sexo', 'estado_civil', 'nacionalidad', 'fecha_fallecimiento'];
    let causanteLocked = caseData.causante._locked_fields || [];

    // Fallback para borradores viejos: solo si _locked_from_db fue marcado por autocomplete
    if (causanteLocked.length === 0 && caseData.causante._locked_from_db) {
        causanteLocked = inferLockedFields(caseData.causante, causanteFieldsList);
        caseData.causante._locked_fields = causanteLocked; // Guardar para futuros refreshes
    }

    causanteLocked.forEach(f => {
        const el = $(`[data-bind="causante.${f}"]`);
        if (el) {
            el.disabled = true;
            el.style.backgroundColor = lockStyle;
        }
    });

    // Representante
    const repFieldsList = ['nombres', 'apellidos', 'fecha_nacimiento', 'sexo', 'estado_civil', 'nacionalidad'];
    let repLocked = caseData.representante._locked_fields || [];

    // Fallback para borradores viejos
    if (repLocked.length === 0 && caseData.representante._locked_from_db) {
        repLocked = inferLockedFields(caseData.representante, repFieldsList);
        caseData.representante._locked_fields = repLocked;
    }

    repLocked.forEach(f => {
        const el = $(`[data-bind="representante.${f}"]`);
        if (el) {
            el.disabled = true;
            el.style.backgroundColor = lockStyle;
        }
    });
}

function initCollapsibles() {
    const CARDS_STATE_KEY = 'crearCaso_cardsState';

    // Detect: is this a page refresh (F5) or a fresh navigation (link click)?
    const navEntries = performance.getEntriesByType('navigation');
    const isReload = navEntries.length > 0 && navEntries[0].type === 'reload';

    // Load saved state only on refresh; on fresh navigation, clear it
    let savedState = null;
    if (isReload) {
        try {
            const stored = sessionStorage.getItem(CARDS_STATE_KEY);
            if (stored) savedState = JSON.parse(stored);
        } catch (e) { /* ignore */ }
    } else {
        sessionStorage.removeItem(CARDS_STATE_KEY);
    }

    // Helper: persist current state of all collapsible cards
    const saveCardsState = () => {
        const state = {};
        $$('.cc-card--collapsible').forEach((card, i) => {
            const key = card.id || `card_${i}`;
            state[key] = card.classList.contains('is-open');
        });
        sessionStorage.setItem(CARDS_STATE_KEY, JSON.stringify(state));
    };

    $$('.cc-card--collapsible').forEach((card, index) => {
        const header = card.querySelector('.cc-card__toggle');
        const body = card.querySelector('.cc-card__collapse');

        if (!header) return;

        const key = card.id || `card_${index}`;

        // Determine initial open/closed state
        if (savedState && savedState[key] !== undefined) {
            // Refresh → restore saved state
            if (savedState[key]) {
                card.classList.add('is-open');
                if (body) body.style.display = '';
            } else {
                card.classList.remove('is-open');
                if (body) body.style.display = 'none';
            }
        } else {
            // First entry (crear/editar) → all closed by default
            card.classList.remove('is-open');
            if (body) body.style.display = 'none';
        }

        // Clone node to drop previously attached listeners (prevent multiple triggers)
        const newHeader = header.cloneNode(true);
        header.parentNode.replaceChild(newHeader, header);

        newHeader.addEventListener('click', () => {
            card.classList.toggle('is-open');
            if (body) {
                body.style.display = card.classList.contains('is-open') ? '' : 'none';
            }
            saveCardsState();
        });
    });

    // Save initial state so a refresh knows we've been here
    saveCardsState();
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
    let hadSavedData = false;

    if (editId) {
        const navEntry = performance.getEntriesByType('navigation')[0];
        const isPageReload = navEntry && navEntry.type === 'reload';

        // En reload: intentar restaurar desde sessionStorage (preserva ediciones no guardadas)
        if (isPageReload) {
            const restored = loadCaseData();
            if (restored) {
                isEditMode = true;
                hadSavedData = true;
            }
        }

        // Si no se restauró de sessionStorage (navegación fresca o no había datos), cargar del servidor
        if (!isEditMode) {
            try {
                const baseUrl = (window.BASE_URL || '/tesis_francisco/public').replace(/\/+$/, '');
                const res = await fetch(baseUrl + '/api/casos/' + editId);
                const json = await res.json();

                if (res.ok && json.success && json.data) {
                    clearSavedCaseData();
                    hydrateCaseData(json.data);
                    isEditMode = true;
                    hadSavedData = true;
                } else {
                    showToast(json.message || 'No se pudo cargar el caso para editar.');
                }
            } catch (err) {
                showToast('Error de red al cargar el caso: ' + err.message);
            }
        }

        // Actualizar UI para modo edición
        if (isEditMode) {
            const titleEl = $('.cc-topbar__title');
            if (titleEl) titleEl.textContent = 'Editar Caso';
            const badgeEl = $('.status-badge');
            if (badgeEl) {
                badgeEl.textContent = 'Editando';
                badgeEl.className = 'status-badge status-active';
            }
        }
    }

    // Show toast if coming from saving a draft (only in non-edit mode)
    if (!editId && params.get('borrador') === 'ok') {
        showToast('Borrador guardado exitosamente.', 'success');
        history.replaceState(null, '', window.location.pathname);
    }

    // Restore saved data only on page RELOAD (F5), not on fresh navigation (create mode only)
    if (!isEditMode) {
        const navEntry = performance.getEntriesByType('navigation')[0];
        const isPageReload = navEntry && navEntry.type === 'reload';
        if (isPageReload) {
            hadSavedData = loadCaseData();
        } else {
            clearSavedCaseData();
        }
    }

    // 1. Cargar catálogos PRIMERO (antes de bindInputs para que los selects tengan opciones)
    await initCatalogos();
    renderSelects();


    // 2. Ahora bindInputs puede poner valores en selects que ya tienen opciones
    bindInputs();
    restoreLockedFields(); // Re-aplicar disabled en campos cargados de BD
    initFieldConstraints();
    initCollapsibles();
    initTabs();
    initStepperClicks();

    renderHerenciaCheckboxes();
    initRepresentanteLogic();
    initAddressListeners();
    initCausanteAutocomplete();
    initRepresentanteAutocomplete();

    // 3. Global text sanitization is now handled by global/sanitize.js (loaded in layout)

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

    // Auto-save before page unload (must be immediate, not debounced)
    window.addEventListener('beforeunload', () => saveCaseData(true));

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
