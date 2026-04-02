import { $, $$, showToast, showInlineError } from '../../global/utils.js';
import { caseData, UIState, loadCaseData, saveCaseData, clearSavedCaseData, hydrateCaseData } from './state.js';
import { initChecklist } from './checklist.js';

/**
 * Envía el caseData completo al backend vía POST /api/casos.
 * @param {'Borrador'|'Publicado'} modo
 */
let _submitted = false;
let _saving = false;
async function submitCase(modo) {
    // Guard: prevent double-click / multiple submissions
    if (_saving || _submitted) return;
    _saving = true;

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
            _saving = false;
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
        _saving = false;
        showToast('Ocurrió un error inesperado al intentar guardar el caso. Revisa tu conexión a internet o contacta al administrador.');
    }
}
import { renderHerenciaCheckboxes, initRepresentanteLogic, renderHerederos, renderHerederosPremuertos } from './herederos.js';
import { fetchEstados, initAddressListeners, saveDireccion, renderDirecciones, editDireccion, deleteDireccion, restoreAddressCascade } from './direccion.js';
import { initStepperClicks, setStep, nextStep, prevStep } from './navigation.js';

import { initCatalogos, getCatalogs } from '../../global/catalogos.js';
import { openModal, closeModal, saveModal, clearModalFields, removeItem, removeMueble } from './modal.js';
import { saveProrroga, renderProrrogas, deleteProrroga, editProrroga } from './prorroga.js';
import { renderInventario, viewLitigioso } from './inventario.js';

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
        { name: 'representante.rif_personal', maxLen: 10 },
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

    // — Longitud máxima en campos de texto libre (DB max - 2 para texto, exacto para numéricos) —
    [
        { name: 'caso.titulo', max: 148 },            // DB: varchar(150)
        { name: 'caso.descripcion', max: 998 },        // DB: text
        { name: 'descripcion', max: 500 },              // modal descriptions (DB: text)
        { name: 'linderos', max: 500 },                 // inmueble linderos (DB: text)
        { name: 'causante.nombres', max: 98 },          // DB: varchar(100)
        { name: 'causante.apellidos', max: 98 },        // DB: varchar(100)
        { name: 'representante.nombres', max: 98 },     // DB: varchar(100)
        { name: 'representante.apellidos', max: 98 },   // DB: varchar(100)
        { name: 'acta_defuncion.numero_acta', max: 48 },// DB: varchar(50)
        { name: 'prorroga.nro_resolucion', max: 48 },   // DB: varchar(50)
        { name: 'domicilio_causante.nombre_vialidad', max: 98 },   // DB: varchar(100)
        { name: 'domicilio_causante.desc_inmueble', max: 58 },     // DB: varchar(60) → nombre_inmueble
        { name: 'domicilio_causante.piso_nivel', max: 18 },        // DB: varchar(20)
        { name: 'domicilio_causante.nro_nivel', max: 18 },         // DB: varchar(20)
        { name: 'domicilio_causante.nombre_sector', max: 98 },     // DB: varchar(100)
        { name: 'domicilio_causante.punto_referencia', max: 253 }, // DB: varchar(255)
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
                    setTimeout(() => {
                        const bind = toEl.dataset.bind || toEl.dataset.modal || '';
                        let container = 'causanteErrors';
                        let list = 'causanteErrorsList';
                        if (bind.includes('representante')) { container = 'representanteErrors'; list = 'representanteErrorsList'; }
                        else if (bind.includes('prorroga') || bind.includes('fecha_resolucion')) { container = 'prorrogaErrors'; list = 'prorrogaErrorsList'; }
                        else if (bind === 'fecha_nacimiento' || bind === 'fecha_fallecimiento') { 
                            container = document.getElementById('modalHerederoErrors') ? 'modalHerederoErrors' : 'causanteErrors';
                            list = document.getElementById('modalHerederoErrors') ? 'modalHerederoErrorsList' : 'causanteErrorsList';
                        }
                        showInlineError(container, list, 'La segunda fecha fue rechazada porque no puede ser anterior a la primera fecha.', toEl);
                    }, 100);
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
                    showInlineError('representanteErrors', 'representanteErrorsList', 'La cédula del representante no puede ser igual a la del causante.', repCedEl);
                    return;
                }
            }
            // Aviso si coincide con heredero
            const dupH = caseData.herederos.some(h => (h.letra_cedula || '') + (h.cedula || '') === fullRepCed);
            const dupHP = caseData.herederos_premuertos.some(h => (h.letra_cedula || '') + (h.cedula || '') === fullRepCed);
            if (dupH || dupHP) {
                showInlineError('representanteErrors', 'representanteErrorsList', 'La cédula del representante coincide con la de un heredero registrado.', repCedEl);
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
                    showInlineError('causanteErrors', 'causanteErrorsList', 'La fecha de cierre fiscal no puede ser anterior a la fecha de fallecimiento.', cierreInput);
                    caseData.datos_fiscales_causante.fecha_cierre_fiscal = '';
                } else if (cierreInput.max && cierreInput.value > cierreInput.max) {
                    showInlineError('causanteErrors', 'causanteErrorsList', 'La fecha de cierre fiscal no puede ser posterior al 31/12 del año de fallecimiento.', cierreInput);
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
                if (!cierreInput.value) return;
                const year = new Date(fallec).getFullYear();
                if (cierreInput.value < fallec) {
                    showInlineError('causanteErrors', 'causanteErrorsList', 'La fecha de cierre fiscal no puede ser anterior a la fecha de fallecimiento.', cierreInput);
                    caseData.datos_fiscales_causante.fecha_cierre_fiscal = '';
                } else if (cierreInput.value > `${year}-12-31`) {
                    showInlineError('causanteErrors', 'causanteErrorsList', 'La fecha de cierre fiscal no puede ser posterior al 31/12 del año de fallecimiento.', cierreInput);
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
    const errors = [];         // Global errors (popup)
    const causanteErrs = [];   // Inline in causante card
    const causanteFields = []; // data-bind selectors for red borders
    const repErrors = [];      // Inline in representante card
    const repFields = [];      // data-bind selectors for red borders
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

    // Sección 1: Caso (global)
    const casoErrs = [];
    if (!c.caso.titulo?.trim()) casoErrs.push('Título del caso');
    if ((c.caso.titulo || '').length > 148) casoErrs.push('El título no puede exceder 148 caracteres');
    if (!c.caso.descripcion?.trim()) casoErrs.push('Descripción del caso');
    if ((c.caso.descripcion || '').length > 998) casoErrs.push('La descripción no puede exceder 998 caracteres');
    if (!c.caso.tipo_sucesion) casoErrs.push('Tipo de sucesión');

    // Causante (inline)
    if (!c.causante.nombres?.trim()) { causanteErrs.push('Nombres'); causanteFields.push('[data-bind="causante.nombres"]'); }
    else if (!isValidName(c.causante.nombres)) causanteErrs.push('Nombres no puede contener solo espacios o números');
    if (!c.causante.apellidos?.trim()) { causanteErrs.push('Apellidos'); causanteFields.push('[data-bind="causante.apellidos"]'); }
    else if (!isValidName(c.causante.apellidos)) causanteErrs.push('Apellidos no puede contener solo espacios o números');
    if (!c.causante.sexo) { causanteErrs.push('Sexo'); causanteFields.push('[data-bind="causante.sexo"]'); }
    if (!c.causante.estado_civil) { causanteErrs.push('Estado civil'); causanteFields.push('[data-bind="causante.estado_civil"]'); }
    if (!c.causante.fecha_nacimiento) { causanteErrs.push('Fecha de nacimiento'); causanteFields.push('[data-bind="causante.fecha_nacimiento"]'); }
    if (!c.causante.fecha_fallecimiento) { causanteErrs.push('Fecha de fallecimiento'); causanteFields.push('[data-bind="causante.fecha_fallecimiento"]'); }
    if (c.causante.fecha_nacimiento && c.causante.fecha_fallecimiento
        && c.causante.fecha_fallecimiento <= c.causante.fecha_nacimiento)
        causanteErrs.push('La fecha de fallecimiento debe ser posterior a la de nacimiento');
    if (c.causante.fecha_nacimiento && c.causante.fecha_fallecimiento
        && c.causante.fecha_fallecimiento > c.causante.fecha_nacimiento) {
        const edad = calcAge(c.causante.fecha_nacimiento, c.causante.fecha_fallecimiento);
        if (edad < 0 || edad > 130) causanteErrs.push('La edad resultante no es razonable (0–130 años)');
    }
    if (!c.causante.nacionalidad) { causanteErrs.push('Nacionalidad'); causanteFields.push('[data-bind="causante.nacionalidad"]'); }
    if (c.caso.tipo_sucesion === 'Con Cédula' && !c.causante.cedula?.trim()) {
        causanteErrs.push('Cédula (requerida para sucesión Con Cédula)');
        causanteFields.push('[data-bind="causante.cedula"]');
    }
    if (c.caso.tipo_sucesion === 'Sin Cédula' && c.causante.cedula?.trim())
        causanteErrs.push('En sucesión Sin Cédula, el campo de cédula debe estar vacío');

    // Acta de defunción (inline in causante card)
    if (c.caso.tipo_sucesion === 'Sin Cédula') {
        if (!c.acta_defuncion.numero_acta?.trim()) { causanteErrs.push('Número de acta de defunción'); causanteFields.push('[data-bind="acta_defuncion.numero_acta"]'); }
        if (!c.acta_defuncion.year_acta) { causanteErrs.push('Año del acta de defunción'); causanteFields.push('[data-bind="acta_defuncion.year_acta"]'); }
        else if (parseInt(c.acta_defuncion.year_acta) < 1900) causanteErrs.push('El año del acta debe ser ≥ 1900');
        if (!c.acta_defuncion.parroquia_registro_id) { causanteErrs.push('Parroquia de registro'); causanteFields.push('[data-bind="acta_defuncion.parroquia_registro_id"]'); }
    }

    // Datos fiscales (inline in causante card)
    if (!c.datos_fiscales_causante.fecha_cierre_fiscal) { causanteErrs.push('Fecha de cierre fiscal'); causanteFields.push('#input_fecha_cierre_fiscal'); }
    if (c.datos_fiscales_causante.fecha_cierre_fiscal && c.causante.fecha_fallecimiento
        && c.datos_fiscales_causante.fecha_cierre_fiscal < c.causante.fecha_fallecimiento)
        causanteErrs.push('La fecha de cierre fiscal debe ser posterior o igual a la de fallecimiento');

    // Herencia (global)
    const herenciaErrs = [];
    if (!c.herencia.tipos.length) {
        herenciaErrs.push('Al menos un tipo de herencia');
    } else {
        const cats = getCatalogs();
        c.herencia.tipos.forEach(t => {
            const tipo = (cats.tiposHerencia || []).find(th => th.id == t.tipo_herencia_id);
            const nombre = tipo?.nombre?.toLowerCase() || '';
            if (nombre.includes('testamento')) {
                if (!t.subtipo_testamento) herenciaErrs.push('Herencia Testamentaria: Subtipo');
                if (!t.fecha_testamento) herenciaErrs.push('Herencia Testamentaria: Fecha del testamento');
                if (t.fecha_testamento && c.causante.fecha_fallecimiento
                    && t.fecha_testamento > c.causante.fecha_fallecimiento)
                    herenciaErrs.push('Herencia Testamentaria: La fecha del testamento no puede ser posterior a la fecha de fallecimiento del causante');
            }
            if (nombre.includes('inventario')) {
                if (!t.fecha_conclusion_inventario) herenciaErrs.push('Beneficio de Inventario: Fecha de conclusión');
            }
        });
    }

    // Representante (inline)
    if (!c.representante.nombres?.trim()) { repErrors.push('Nombres'); repFields.push('[data-bind="representante.nombres"]'); }
    else if (!isValidName(c.representante.nombres)) repErrors.push('Nombres no puede contener solo espacios o números');
    if (!c.representante.apellidos?.trim()) { repErrors.push('Apellidos'); repFields.push('[data-bind="representante.apellidos"]'); }
    else if (!isValidName(c.representante.apellidos)) repErrors.push('Apellidos no puede contener solo espacios o números');
    if (!c.representante.cedula?.trim()) { repErrors.push('Cédula'); repFields.push('[data-bind="representante.cedula"]'); }
    if (!c.representante.rif_personal?.trim()) { repErrors.push('RIF'); repFields.push('[data-bind="representante.rif_personal"]'); }
    if (!c.representante.sexo) { repErrors.push('Sexo'); repFields.push('[data-bind="representante.sexo"]'); }
    if (!c.representante.fecha_nacimiento) { repErrors.push('Fecha de nacimiento'); repFields.push('[data-bind="representante.fecha_nacimiento"]'); }
    if (c.representante.fecha_nacimiento) {
        const edad = calcAge(c.representante.fecha_nacimiento);
        if (edad < 18) repErrors.push('Debe ser mayor de 18 años');
    }

    // Cédulas cruzadas
    const crossErrs = [];
    const fullCedCausante = ((c.causante.tipo_cedula || '') + (c.causante.cedula || '')).trim();
    const fullCedRep = ((c.representante.letra_cedula || '') + (c.representante.cedula || '')).trim();
    if (fullCedCausante && fullCedRep && fullCedCausante === fullCedRep)
        crossErrs.push('La cédula del representante no puede ser igual a la del causante');

    // Domicilio fiscal
    const domicilioErrs = [];
    if (!c.direcciones_causante || c.direcciones_causante.length === 0)
        domicilioErrs.push('Debe agregar al menos una dirección de domicilio fiscal del causante');

    // Herederos + Premuertos
    const herederoErrs = [];
    if (!c.herederos.length) herederoErrs.push('Al menos un heredero');
    const allCedulas = [];
    c.herederos.forEach((h, i) => {
        const fullCed = ((h.letra_cedula || '') + (h.cedula || '')).trim();
        if (fullCed && allCedulas.includes(fullCed)) herederoErrs.push(`Heredero #${i + 1}: Cédula duplicada`);
        if (fullCed) allCedulas.push(fullCed);
        if (fullCedCausante && fullCed && fullCed === fullCedCausante) herederoErrs.push(`Heredero #${i + 1}: Cédula igual a la del causante`);
        if (h.fecha_nacimiento) {
            const edad = calcAge(h.fecha_nacimiento);
            if (edad < 0 || edad > 150) herederoErrs.push(`Heredero #${i + 1}: Fecha de nacimiento no resulta en una edad razonable`);
        }
    });
    (c.herederos_premuertos || []).forEach((hp, i) => {
        const fullCed = ((hp.letra_cedula || '') + (hp.cedula || '')).trim();
        if (fullCedCausante && fullCed && fullCed === fullCedCausante) herederoErrs.push(`Heredero premuerto #${i + 1}: Cédula igual a la del causante`);
        if (fullCed && allCedulas.includes(fullCed)) herederoErrs.push(`Heredero premuerto #${i + 1}: Cédula duplicada con un heredero`);
        if (fullCed) allCedulas.push(fullCed);
    });

    // Validar que cada heredero premuerto tenga al menos un heredero del premuerto
    c.herederos.forEach((h, i) => {
        if (h.premuerto === 'SI') {
            const uid = h._uid || '';
            const tieneSubHerederos = (c.herederos_premuertos || []).some(hp => hp.premuerto_padre_id === uid);
            if (!tieneSubHerederos) {
                herederoErrs.push(`Heredero #${i + 1} (${h.nombres || ''} ${h.apellidos || ''}): Está marcado como premuerto pero no tiene herederos del premuerto asignados`);
            }
        }
    });

    // Bienes
    const bienesErrs = [];
    const tieneInmuebles = c.bienes_inmuebles.length > 0;
    const tieneMuebles = Object.values(c.bienes_muebles).some(arr => Array.isArray(arr) && arr.length > 0);
    if (!tieneInmuebles && !tieneMuebles) bienesErrs.push('Al menos un bien (inmueble o mueble)');

    // ── Ensamblar errors[] en orden del formulario ──
    // 1. Caso
    errors.push(...casoErrs);
    // 2. Herencia
    errors.push(...herenciaErrs);
    // 3. Causante (resumen)
    if (causanteErrs.length > 0) errors.push('Falta completar los datos del causante');
    // 4. Domicilio fiscal
    errors.push(...domicilioErrs);
    // 5. Representante (resumen)
    if (repErrors.length > 0) errors.push('Falta completar los datos del representante');
    // 6. Herederos
    errors.push(...herederoErrs);
    // 7. Cédulas cruzadas
    errors.push(...crossErrs);
    // 8. Bienes
    errors.push(...bienesErrs);

    return { errors, causanteErrs, causanteFields, repErrors, repFields };
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
    openModal, closeModal, saveModal, clearModalFields,
    removeItem, removeMueble, viewLitigioso,
    saveDireccion, editDireccion, deleteDireccion,
    saveProrroga, deleteProrroga, editProrroga,
    publish: async () => {
        const baseUrl = (window.BASE_URL || '/tesis_francisco/public').replace(/\/+$/, '');

        const result = validateBeforePublish();

        // Pre-check: título duplicado contra BD (inject at top of errors)
        const titulo = (caseData.caso.titulo || '').trim();
        if (titulo) {
            try {
                const params = new URLSearchParams({ titulo });
                if (caseData.caso_id) params.append('caso_id', caseData.caso_id);
                const res = await fetch(baseUrl + '/api/casos/check-titulo?' + params);
                const json = await res.json();
                if (json.exists) {
                    result.errors.unshift('Ya existe un caso con este título. Elige otro.');
                }
            } catch (e) {
                // Si falla el check, continuamos — el backend validará de todas formas
            }
        }

        const hasErrors = result.errors.length > 0 || result.causanteErrs.length > 0 || result.repErrors.length > 0;

        // Clear previous inline errors
        if (CC.clearCausanteErrors) CC.clearCausanteErrors();
        if (CC.clearRepErrors) CC.clearRepErrors();

        if (!hasErrors) {
            submitCase('Publicado');
            return;
        }

        // Show inline errors for causante
        if (result.causanteErrs.length > 0 && CC.showCausanteError) {
            CC.showCausanteError(result.causanteErrs, result.causanteFields);
        }

        // Show inline errors for representante
        if (result.repErrors.length > 0 && CC.showRepError) {
            CC.showRepError(result.repErrors, result.repFields);
        }

        // Show popup for global errors (caso, herencia, bienes, etc.)
        if (result.errors.length > 0) {
            showValidationPopup(result.errors);
        }
    }
};

// Global helper access for state changes
document.ccHelpers = { $, show: (el) => { if (el) el.style.display = ''; }, hide: (el) => { if (el) el.style.display = 'none'; } };

function initCausanteAutocomplete() {
    const baseUrl = (window.BASE_URL || '/tesis_francisco/public').replace(/\/+$/, '');
    const lockStyle = 'var(--cc-slate-50, #f8fafc)';

    const inputBuscar = document.getElementById('inputBuscarCausante');
    const inputCedulaCausante = $('[data-bind="causante.cedula"]');
    const selectTipoCedulaCausante = $('[data-bind="causante.tipo_cedula"]');

    // All lockable fields for causante
    const allCausanteFields = ['nombres', 'apellidos', 'fecha_nacimiento', 'sexo', 'estado_civil', 'nacionalidad', 'fecha_fallecimiento'];

    // Fill & disable a field only if value is present, else enable it
    const fillIfPresent = (field, value) => {
        const sel = `[data-bind="causante.${field}"]`;
        const el = $(sel);
        if (!el) return false;
        if (value !== null && value !== undefined && value !== '') {
            caseData.causante[field] = value;
            el.value = value;
            el.disabled = true;
            el.style.backgroundColor = lockStyle;
            return true;
        } else {
            caseData.causante[field] = '';
            el.value = '';
            el.disabled = false;
            el.style.backgroundColor = '';
            return false;
        }
    };

    // Clear all causante data and unlock
    const clearCausante = () => {
        allCausanteFields.forEach(f => {
            caseData.causante[f] = '';
            const el = $(`[data-bind="causante.${f}"]`);
            if (el) { el.value = ''; el.disabled = false; el.style.backgroundColor = ''; }
        });
        caseData.causante.persona_id = '';
        caseData.causante._locked_fields = [];

        // Only clear cédula fields if in "Con Cédula" mode
        if (caseData.caso.tipo_sucesion !== 'Sin Cédula') {
            caseData.causante.cedula = '';
            caseData.causante.tipo_cedula = '';
            if (inputCedulaCausante) { inputCedulaCausante.value = ''; inputCedulaCausante.disabled = false; inputCedulaCausante.style.backgroundColor = ''; }
            if (selectTipoCedulaCausante) { selectTipoCedulaCausante.value = ''; selectTipoCedulaCausante.disabled = false; selectTipoCedulaCausante.style.backgroundColor = ''; }
        }

        // Clear acta de defunción fields
        caseData.acta_defuncion = caseData.acta_defuncion || {};
        caseData.acta_defuncion.numero_acta = '';
        caseData.acta_defuncion.year_acta = '';
        caseData.acta_defuncion.parroquia_registro_id = '';
        const actaFields = ['acta_defuncion.numero_acta', 'acta_defuncion.year_acta', 'acta_defuncion.parroquia_registro_id'];
        actaFields.forEach(f => {
            const el = $(`[data-bind="${f}"]`);
            if (el) { el.value = ''; el.disabled = false; el.style.backgroundColor = ''; }
        });

        // Clear datos fiscales
        caseData.datos_fiscales_causante.fecha_cierre_fiscal = '';
        const cierreEl = $('#input_fecha_cierre_fiscal');
        if (cierreEl) { cierreEl.value = ''; cierreEl.disabled = false; cierreEl.style.backgroundColor = ''; }

        caseData.datos_fiscales_causante.domiciliado_pais = '1';
        const domPaisEl = $('[data-bind="datos_fiscales_causante.domiciliado_pais"]');
        if (domPaisEl) { domPaisEl.value = '1'; domPaisEl.disabled = true; domPaisEl.style.backgroundColor = lockStyle; }

        // Clear search input
        if (inputBuscar) inputBuscar.value = '';

        // Clear inline errors
        clearCausanteErrors();
    };

    // Handle selected persona data
    const handleCausanteData = (data) => {
        // Clear previous state
        clearCausante();

        // Store persona_id
        caseData.causante.persona_id = data.persona_id;

        // Fill tipo_cedula and cedula (only in Con Cédula mode)
        if (caseData.caso.tipo_sucesion !== 'Sin Cédula') {
            if (data.tipo_cedula && data.tipo_cedula !== 'No_Aplica') {
                caseData.causante.tipo_cedula = data.tipo_cedula;
                if (selectTipoCedulaCausante) {
                    selectTipoCedulaCausante.value = data.tipo_cedula;
                    selectTipoCedulaCausante.disabled = true;
                    selectTipoCedulaCausante.style.backgroundColor = lockStyle;
                }
            }
            if (data.cedula) {
                caseData.causante.cedula = data.cedula;
                if (inputCedulaCausante) {
                    inputCedulaCausante.value = data.cedula;
                    inputCedulaCausante.disabled = true;
                    inputCedulaCausante.style.backgroundColor = lockStyle;
                }
            }
        }

        // Fill personal data — only lock non-empty fields
        const locked = [];
        allCausanteFields.forEach(f => {
            if (fillIfPresent(f, data[f])) locked.push(f);
        });

        // Datos fiscales
        if (data.fecha_cierre_fiscal) {
            caseData.datos_fiscales_causante.fecha_cierre_fiscal = data.fecha_cierre_fiscal;
            const el = $('#input_fecha_cierre_fiscal');
            if (el) { el.value = data.fecha_cierre_fiscal; el.disabled = true; el.style.backgroundColor = lockStyle; }
        }
        if (data.domiciliado_pais !== null && data.domiciliado_pais !== undefined) {
            caseData.datos_fiscales_causante.domiciliado_pais = data.domiciliado_pais;
            const el = $('[data-bind="datos_fiscales_causante.domiciliado_pais"]');
            if (el) { el.value = data.domiciliado_pais; el.disabled = true; el.style.backgroundColor = lockStyle; }
        }

        // Acta de defunción (only for Sin Cédula cases)
        if (caseData.caso.tipo_sucesion === 'Sin Cédula') {
            if (data.numero_acta) {
                caseData.acta_defuncion = caseData.acta_defuncion || {};
                caseData.acta_defuncion.numero_acta = data.numero_acta;
                const el = $('[data-bind="acta_defuncion.numero_acta"]');
                if (el) { el.value = data.numero_acta; el.disabled = true; el.style.backgroundColor = lockStyle; }
            }
            if (data.year_acta) {
                caseData.acta_defuncion = caseData.acta_defuncion || {};
                caseData.acta_defuncion.year_acta = data.year_acta;
                const el = $('[data-bind="acta_defuncion.year_acta"]');
                if (el) { el.value = data.year_acta; el.disabled = true; el.style.backgroundColor = lockStyle; }
            }
            if (data.parroquia_registro_id) {
                caseData.acta_defuncion = caseData.acta_defuncion || {};
                caseData.acta_defuncion.parroquia_registro_id = data.parroquia_registro_id;
                const el = $('[data-bind="acta_defuncion.parroquia_registro_id"]');
                if (el) { el.value = data.parroquia_registro_id; el.disabled = true; el.style.backgroundColor = lockStyle; }
            }
        }

        caseData.causante._locked_fields = locked;

        // Update search input to show who was selected
        if (inputBuscar) {
            inputBuscar.value = `${data.nombres || ''} ${data.apellidos || ''} — ${data.cedula || 'S/C'}`.trim();
        }

        showToast('Datos del causante autocompletados', 'success');
        renderHerenciaCheckboxes();
    };

    // Expose clear function
    window.CC.clearCausante = clearCausante;

    // ── AutocompleteDropdown on search bar ──
    let lastSinCedula = caseData.caso.tipo_sucesion === 'Sin Cédula';
    let causanteDropdown = null;

    if (inputBuscar && typeof AutocompleteDropdown !== 'undefined') {
        causanteDropdown = new AutocompleteDropdown({
            input: inputBuscar,
            debounceMs: 300,
            minLength: 0,

            fetchFn: async (query, signal) => {
                // Invalidate cache if tipo_sucesion changed
                const currentSinCedula = caseData.caso.tipo_sucesion === 'Sin Cédula';
                if (currentSinCedula !== lastSinCedula) {
                    causanteDropdown._cache.clear();
                    lastSinCedula = currentSinCedula;
                }

                const params = new URLSearchParams({ campo: 'cedula' });
                if (query) params.set('q', query);
                if (currentSinCedula) params.set('sin_cedula', '1');
                const resp = await fetch(`${baseUrl}/api/buscar-personas?${params}`, { signal });
                const json = await resp.json();
                return json.success ? json.data : [];
            },

            onSelect: async (item) => {
                try {
                    // Always fetch full details by persona_id
                    const resp = await fetch(`${baseUrl}/api/buscar-persona?persona_id=${item.persona_id}`);
                    const json = await resp.json();
                    if (json.success && json.data) {
                        handleCausanteData(json.data);
                    }
                } catch (err) {
                    console.error('Error fetching causante by ID:', err);
                }
            }
        });
    }

    // ── Clear causante when tipo_sucesion changes ──
    document.querySelectorAll('[data-bind="caso.tipo_sucesion"]').forEach(radio => {
        radio.addEventListener('change', () => {
            clearCausante();
            // Invalidate dropdown cache so results match the new mode
            if (causanteDropdown) {
                causanteDropdown._cache.clear();
                lastSinCedula = caseData.caso.tipo_sucesion === 'Sin Cédula';
            }
        });
    });

    // ── Inline error helpers for causante card ──
    const showCausanteError = (msgs, fieldSelectors = []) => {
        const container = document.getElementById('causanteErrors');
        const list = document.getElementById('causanteErrorsList');
        if (!container || !list) return;
        const arr = Array.isArray(msgs) ? msgs : [msgs];
        list.innerHTML = arr.map(m => `<li>${m}</li>`).join('');
        container.classList.add('is-visible');
        container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

        fieldSelectors.forEach(sel => {
            const el = document.querySelector(sel);
            if (el) {
                const field = el.closest('.cc-field');
                if (field) field.classList.add('cc-field--error');
            }
        });

        // Auto-clear on input within causante card only
        const card = container.closest('.cc-card');
        if (card) {
            const clearHandler = () => {
                clearCausanteErrors();
                card.removeEventListener('input', clearHandler);
                card.removeEventListener('change', clearHandler);
            };
            card.addEventListener('input', clearHandler, { once: true });
            card.addEventListener('change', clearHandler, { once: true });
        }
    };

    const clearCausanteErrors = () => {
        const container = document.getElementById('causanteErrors');
        if (container) container.classList.remove('is-visible');
        const card = document.querySelector('[data-bind="causante.nombres"]')?.closest('.cc-card');
        if (card) card.querySelectorAll('.cc-field--error').forEach(el => el.classList.remove('cc-field--error'));
    };

    // Expose error functions for publish-time validation
    window.CC.showCausanteError = showCausanteError;
    window.CC.clearCausanteErrors = clearCausanteErrors;

    // ── Cédula uniqueness validation on blur + tipo change ──
    const checkCedulaUniqueness = async () => {
        // Skip if persona was loaded via search bar (field is disabled)
        if (!inputCedulaCausante || inputCedulaCausante.disabled) return;

        const cedula = inputCedulaCausante.value.trim();
        const tipo = selectTipoCedulaCausante ? selectTipoCedulaCausante.value : '';
        if (!cedula || cedula.length < 6 || !tipo) return;

        try {
            const resp = await fetch(`${baseUrl}/api/buscar-persona?tipo=${tipo}&cedula=${cedula}`);
            const json = await resp.json();
            if (json.success && json.data) {
                showCausanteError(
                    'Esta cédula ya existe en la base de datos. Use la barra de búsqueda para cargar esta persona.',
                    ['[data-bind="causante.cedula"]']
                );
                inputCedulaCausante.value = '';
                caseData.causante.cedula = '';
                inputCedulaCausante.focus();
            }
        } catch (e) { /* ignore */ }
    };

    if (inputCedulaCausante) {
        inputCedulaCausante.addEventListener('blur', checkCedulaUniqueness);
    }
    if (selectTipoCedulaCausante) {
        selectTipoCedulaCausante.addEventListener('change', checkCedulaUniqueness);
    }
}

function initRepresentanteAutocomplete() {
    const inputCedulaRep = $('[data-bind="representante.cedula"]');
    const selectLetraRep = $('[data-bind="representante.letra_cedula"]');
    const inputRifRep = $('[data-bind="representante.rif_personal"]');
    const selectLetraRif = $('[data-bind="representante.letra_rif"]');
    const inputBuscarRep = document.getElementById('inputBuscarRepresentante');

    // Track which field was used to search ('cedula' | 'rif' | 'search' | null)
    let searchOrigin = null;

    const baseUrl = (window.BASE_URL || '/tesis_francisco/public').replace(/\/+$/, '');
    const lockStyle = 'var(--cc-slate-50, #f8fafc)';

    // Lock a set of elements (input + select)
    const lockEls = (...els) => {
        els.forEach(el => {
            if (el) { el.disabled = true; el.style.backgroundColor = lockStyle; }
        });
    };
    const unlockEls = (...els) => {
        els.forEach(el => {
            if (el) { el.disabled = false; el.style.backgroundColor = ''; }
        });
    };

    const allPersonalFields = ['nombres', 'apellidos', 'fecha_nacimiento', 'sexo', 'estado_civil', 'nacionalidad'];

    const clearAndEnableAll = () => {
        const prevOrigin = searchOrigin;
        searchOrigin = null;

        // Clear ALL personal data fields (locked + manually filled)
        allPersonalFields.forEach(f => {
            caseData.representante[f] = '';
            const el = $(`[data-bind="representante.${f}"]`);
            if (el) { el.value = ''; el.disabled = false; el.style.backgroundColor = ''; }
        });
        caseData.representante.persona_id = '';
        caseData.representante._locked_fields = [];

        // Clear the cross-filled document field
        if (prevOrigin === 'search') {
            // From search bar: clear both
            caseData.representante.cedula = '';
            caseData.representante.letra_cedula = 'V';
            caseData.representante.rif_personal = '';
            caseData.representante.letra_rif = 'V';
            if (inputCedulaRep) inputCedulaRep.value = '';
            if (selectLetraRep) selectLetraRep.value = 'V';
            if (inputRifRep) inputRifRep.value = '';
            if (selectLetraRif) selectLetraRif.value = 'V';
        } else if (prevOrigin === 'cedula') {
            caseData.representante.rif_personal = '';
            caseData.representante.letra_rif = 'V';
            if (inputRifRep) inputRifRep.value = '';
            if (selectLetraRif) selectLetraRif.value = 'V';
        } else if (prevOrigin === 'rif') {
            caseData.representante.cedula = '';
            caseData.representante.letra_cedula = 'V';
            if (inputCedulaRep) inputCedulaRep.value = '';
            if (selectLetraRep) selectLetraRep.value = 'V';
        }

        // Unlock both document fields
        unlockEls(inputCedulaRep, selectLetraRep, inputRifRep, selectLetraRif);

        // Clear search bar
        if (inputBuscarRep) inputBuscarRep.value = '';

        // Clear inline errors
        clearRepErrors();
    };

    const fillAndDisable = (selector, value, forceEnable = false) => {
        const el = $(selector);
        if (!el) return;
        if (forceEnable) {
            el.value = ''; el.disabled = false; el.style.backgroundColor = '';
        } else if (value !== undefined && value !== null) {
            el.value = value; el.disabled = true; el.style.backgroundColor = lockStyle;
        }
    };

    // ── Inline error helpers for representante card ──
    const showRepError = (msgs, fieldSelectors = []) => {
        const container = document.getElementById('representanteErrors');
        const list = document.getElementById('representanteErrorsList');
        if (!container || !list) return;
        const arr = Array.isArray(msgs) ? msgs : [msgs];
        list.innerHTML = arr.map(m => `<li>${m}</li>`).join('');
        container.classList.add('is-visible');
        container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

        const selArr = Array.isArray(fieldSelectors) ? fieldSelectors : [fieldSelectors].filter(Boolean);
        selArr.forEach(sel => {
            const el = document.querySelector(sel);
            if (el) {
                const field = el.closest('.cc-field');
                if (field) field.classList.add('cc-field--error');
            }
        });
        // Auto-clear on input within representante card only
        const card = container.closest('.cc-card');
        if (card) {
            const clearHandler = () => {
                clearRepErrors();
                card.removeEventListener('input', clearHandler);
                card.removeEventListener('change', clearHandler);
            };
            card.addEventListener('input', clearHandler, { once: true });
            card.addEventListener('change', clearHandler, { once: true });
        }
    };

    const clearRepErrors = () => {
        const container = document.getElementById('representanteErrors');
        if (container) container.classList.remove('is-visible');
        const card = document.querySelector('[data-bind="representante.nombres"]')?.closest('.cc-card');
        if (card) card.querySelectorAll('.cc-field--error').forEach(el => el.classList.remove('cc-field--error'));
    };

    // Shared handler for API response
    const handlePersonaData = (data, origin) => {
        // Validar que no sea el mismo causante
        let isSamePerson = false;
        if (caseData.causante) {
            // Compare by cédula compuesta
            if (caseData.causante.cedula && data.cedula && data.cedula === caseData.causante.cedula && data.tipo_cedula === caseData.causante.tipo_cedula) {
                isSamePerson = true;
            }
            // Compare by RIF
            if (data.rif_personal && caseData.causante.rif_personal && data.rif_personal === caseData.causante.rif_personal) {
                isSamePerson = true;
            }
            // Compare by persona_id
            if (data.persona_id && caseData.causante.persona_id && String(data.persona_id) === String(caseData.causante.persona_id)) {
                isSamePerson = true;
            }
        }

        if (isSamePerson) {
            // Clear fields first, THEN show the error (clearAndEnableAll calls clearRepErrors)
            clearAndEnableAll();
            showRepError('El representante no puede ser el mismo causante.');
            return;
        }

        searchOrigin = origin;

        // Clear previously locked fields before filling new person's data
        (caseData.representante._locked_fields || []).forEach(f => {
            const el = $(`[data-bind="representante.${f}"]`);
            if (el) { el.value = ''; el.disabled = false; el.style.backgroundColor = ''; }
        });
        caseData.representante._locked_fields = [];

        // Populate personal data — only fill and lock if DB has a value
        caseData.representante.persona_id = data.persona_id;

        // Helper: fill & lock only if value exists, otherwise leave editable
        const fillIfPresent = (field, value) => {
            const sel = `[data-bind="representante.${field}"]`;
            if (value !== null && value !== undefined && value !== '') {
                caseData.representante[field] = value;
                fillAndDisable(sel, value);
                return true; // was locked
            } else {
                caseData.representante[field] = '';
                fillAndDisable(sel, null, true); // forceEnable
                return false; // left editable
            }
        };

        const locked = [];
        if (fillIfPresent('nombres', data.nombres)) locked.push('nombres');
        if (fillIfPresent('apellidos', data.apellidos)) locked.push('apellidos');
        if (fillIfPresent('fecha_nacimiento', data.fecha_nacimiento)) locked.push('fecha_nacimiento');
        if (fillIfPresent('sexo', data.sexo)) locked.push('sexo');
        if (fillIfPresent('estado_civil', data.estado_civil)) locked.push('estado_civil');
        if (fillIfPresent('nacionalidad', data.nacionalidad)) locked.push('nacionalidad');

        // Cross-fill: populate the OTHER document field and lock it ONLY if data exists
        if (origin === 'search') {
            // From search bar: fill and lock BOTH cédula and RIF
            if (data.cedula && data.tipo_cedula) {
                caseData.representante.letra_cedula = data.tipo_cedula;
                caseData.representante.cedula = data.cedula;
                if (selectLetraRep) selectLetraRep.value = data.tipo_cedula;
                if (inputCedulaRep) inputCedulaRep.value = data.cedula;
                lockEls(inputCedulaRep, selectLetraRep);
            }
            if (data.rif_personal) {
                const rifLetra = data.rif_personal.charAt(0);
                const rifNumero = data.rif_personal.substring(1);
                caseData.representante.letra_rif = rifLetra;
                caseData.representante.rif_personal = rifNumero;
                if (selectLetraRif) selectLetraRif.value = rifLetra;
                if (inputRifRep) inputRifRep.value = rifNumero;
                lockEls(inputRifRep, selectLetraRif);
            }
        } else if (origin === 'cedula') {
            if (data.rif_personal) {
                const rifLetra = data.rif_personal.charAt(0);
                const rifNumero = data.rif_personal.substring(1);
                caseData.representante.letra_rif = rifLetra;
                caseData.representante.rif_personal = rifNumero;
                if (selectLetraRif) selectLetraRif.value = rifLetra;
                if (inputRifRep) inputRifRep.value = rifNumero;
                lockEls(inputRifRep, selectLetraRif);
            }
            // Keep cédula fields editable (origin)
            unlockEls(inputCedulaRep, selectLetraRep);
        } else if (origin === 'rif') {
            if (data.cedula && data.tipo_cedula) {
                caseData.representante.letra_cedula = data.tipo_cedula;
                caseData.representante.cedula = data.cedula;
                if (selectLetraRep) selectLetraRep.value = data.tipo_cedula;
                if (inputCedulaRep) inputCedulaRep.value = data.cedula;
                lockEls(inputCedulaRep, selectLetraRep);
            }
            // Keep RIF fields editable (origin)
            unlockEls(inputRifRep, selectLetraRif);
        }

        // Update search bar display
        if (inputBuscarRep && origin === 'search') {
            inputBuscarRep.value = `${data.nombres || ''} ${data.apellidos || ''} — ${data.cedula || 'S/C'}`.trim();
        }

        showToast('Datos del representante autocompletados', 'success');
        caseData.representante._locked_fields = locked;
    };

    // Search by Cédula
    const fetchByCedula = async () => {
        // If loaded via RIF or search bar, ignore cédula input
        if (searchOrigin === 'rif' || searchOrigin === 'search') return;

        const cedula = inputCedulaRep ? inputCedulaRep.value.trim() : '';
        const tipo = selectLetraRep ? selectLetraRep.value : '';

        if (!cedula || cedula.length < 6) {
            if (cedula.length === 0 && searchOrigin === 'cedula') clearAndEnableAll();
            return;
        }
        if (!tipo) return;

        try {
            const resp = await fetch(`${baseUrl}/api/buscar-persona?tipo=${tipo}&cedula=${cedula}`);
            const json = await resp.json();
            if (json.success && json.data) {
                handlePersonaData(json.data, 'cedula');
            } else if (searchOrigin === 'cedula') {
                clearAndEnableAll();
            }
        } catch (err) {
            console.error("Error buscando persona por cédula", err);
        }
    };

    // Search by RIF
    const fetchByRif = async () => {
        // If loaded via cédula or search bar, ignore RIF input
        if (searchOrigin === 'cedula' || searchOrigin === 'search') return;

        const rifNumero = inputRifRep ? inputRifRep.value.trim() : '';
        const rifLetra = selectLetraRif ? selectLetraRif.value : '';

        if (!rifNumero || rifNumero.length < 5) {
            if (rifNumero.length === 0 && searchOrigin === 'rif') clearAndEnableAll();
            return;
        }

        const rif = `${rifLetra}${rifNumero}`;

        try {
            const resp = await fetch(`${baseUrl}/api/buscar-persona?rif=${rif}`);
            const json = await resp.json();
            if (json.success && json.data) {
                handlePersonaData(json.data, 'rif');
            } else if (searchOrigin === 'rif') {
                clearAndEnableAll();
            }
        } catch (err) {
            console.error("Error buscando persona por RIF", err);
        }
    };

    // Cédula listeners (exact-match fallback)
    if (inputCedulaRep) {
        inputCedulaRep.addEventListener('input', fetchByCedula);
    }
    if (selectLetraRep) {
        selectLetraRep.addEventListener('change', fetchByCedula);
    }

    // RIF listeners (exact-match fallback)
    if (inputRifRep) {
        inputRifRep.addEventListener('input', fetchByRif);
    }
    if (selectLetraRif) {
        selectLetraRif.addEventListener('change', fetchByRif);
    }

    // ── Uniqueness validation on blur ──
    // When a persona is loaded and the OTHER document field is manually filled,
    // check that the value doesn't belong to a different persona in the DB.
    const validateDocUniqueness = async (field, value, currentPersonaId) => {
        if (!value || !currentPersonaId) return true;
        try {
            let url;
            if (field === 'rif') {
                url = `${baseUrl}/api/buscar-persona?rif=${value}`;
            } else {
                const tipo = selectLetraRep ? selectLetraRep.value : 'V';
                url = `${baseUrl}/api/buscar-persona?tipo=${tipo}&cedula=${value}`;
            }
            const resp = await fetch(url);
            const json = await resp.json();
            if (json.success && json.data && String(json.data.persona_id) !== String(currentPersonaId)) {
                return false; // belongs to someone else
            }
        } catch (e) { /* ignore */ }
        return true;
    };

    if (inputRifRep) {
        inputRifRep.addEventListener('blur', async () => {
            if (searchOrigin !== 'cedula') return;
            const rifNumero = inputRifRep.value.trim();
            if (!rifNumero || rifNumero.length < 5) return;
            const letra = selectLetraRif ? selectLetraRif.value : 'V';
            const rif = `${letra}${rifNumero}`;
            const ok = await validateDocUniqueness('rif', rif, caseData.representante.persona_id);
            if (!ok) {
                showRepError('Este RIF ya pertenece a otra persona en la base de datos.', '#inp-rep-rif');
                inputRifRep.value = '';
                caseData.representante.rif_personal = '';
                inputRifRep.focus();
            }
        });
    }

    if (inputCedulaRep) {
        inputCedulaRep.addEventListener('blur', async () => {
            if (searchOrigin !== 'rif') return;
            const cedula = inputCedulaRep.value.trim();
            if (!cedula || cedula.length < 6) return;
            const ok = await validateDocUniqueness('cedula', cedula, caseData.representante.persona_id);
            if (!ok) {
                showRepError('Esta cédula ya pertenece a otra persona en la base de datos.', '#inp-rep-cedula');
                inputCedulaRep.value = '';
                caseData.representante.cedula = '';
                inputCedulaRep.focus();
            }
        });
    }

    // ── AutocompleteDropdown integration ──
    if (typeof AutocompleteDropdown !== 'undefined') {
        // Dropdown for Cédula
        let repCedulaDropdown = null;
        if (inputCedulaRep) {
            repCedulaDropdown = new AutocompleteDropdown({
                input: inputCedulaRep,
                debounceMs: 300,

                fetchFn: async (query, signal) => {
                    // Suppress if persona is loaded via RIF (cédula is being filled manually)
                    if (searchOrigin === 'rif') return null;

                    const tipo = selectLetraRep ? selectLetraRep.value : '';
                    const params = new URLSearchParams({ campo: 'cedula' });
                    if (query) params.set('q', query);
                    if (tipo) params.set('tipo', tipo);

                    const resp = await fetch(`${baseUrl}/api/buscar-personas?${params}`, { signal });
                    const json = await resp.json();
                    return json.success ? json.data : [];
                },

                onSelect: async (item) => {
                    if (item.cedula) {
                        if (item.tipo_cedula && item.tipo_cedula !== 'No_Aplica') {
                            if (selectLetraRep) selectLetraRep.value = item.tipo_cedula;
                            caseData.representante.letra_cedula = item.tipo_cedula;
                        }
                        inputCedulaRep.value = item.cedula;
                        caseData.representante.cedula = item.cedula;
                        fetchByCedula();
                    } else {
                        // S/C persona → fetch by persona_id
                        try {
                            const resp = await fetch(`${baseUrl}/api/buscar-persona?persona_id=${item.persona_id}`);
                            const json = await resp.json();
                            if (json.success && json.data) {
                                handlePersonaData(json.data, 'cedula');
                            }
                        } catch (err) {
                            console.error('Error fetching persona by ID:', err);
                        }
                    }
                }
            });

            if (selectLetraRep) {
                selectLetraRep.addEventListener('change', () => {
                    if (repCedulaDropdown) repCedulaDropdown._cache.clear();
                });
            }
        }

        // Dropdown for RIF
        let repRifDropdown = null;
        if (inputRifRep) {
            repRifDropdown = new AutocompleteDropdown({
                input: inputRifRep,
                debounceMs: 300,

                fetchFn: async (query, signal) => {
                    // Suppress if persona is loaded via cédula (RIF is being filled manually)
                    if (searchOrigin === 'cedula') return null;

                    const letra = selectLetraRif ? selectLetraRif.value : '';
                    const params = new URLSearchParams({ campo: 'rif' });
                    if (query) params.set('q', letra + query);

                    const resp = await fetch(`${baseUrl}/api/buscar-personas?${params}`, { signal });
                    const json = await resp.json();
                    return json.success ? json.data : [];
                },

                onSelect: async (item) => {
                    if (item.rif_personal) {
                        const rifLetra = item.rif_personal.charAt(0);
                        const rifNumero = item.rif_personal.substring(1);
                        if (selectLetraRif) selectLetraRif.value = rifLetra;
                        caseData.representante.letra_rif = rifLetra;
                        inputRifRep.value = rifNumero;
                        caseData.representante.rif_personal = rifNumero;
                        fetchByRif();
                    } else if (item.persona_id) {
                        // Fetch by persona_id
                        try {
                            const resp = await fetch(`${baseUrl}/api/buscar-persona?persona_id=${item.persona_id}`);
                            const json = await resp.json();
                            if (json.success && json.data) {
                                handlePersonaData(json.data, 'rif');
                            }
                        } catch (err) {
                            console.error('Error fetching persona by ID:', err);
                        }
                    }
                }
            });

            if (selectLetraRif) {
                selectLetraRif.addEventListener('change', () => {
                    if (repRifDropdown) repRifDropdown._cache.clear();
                });
            }
        }

        // Dropdown for Search Bar
        if (inputBuscarRep) {
            new AutocompleteDropdown({
                input: inputBuscarRep,
                debounceMs: 300,
                minLength: 0,

                fetchFn: async (query, signal) => {
                    const params = new URLSearchParams({ campo: 'cedula', con_documentos: '1' });
                    if (query) params.set('q', query);
                    const resp = await fetch(`${baseUrl}/api/buscar-personas?${params}`, { signal });
                    const json = await resp.json();
                    return json.success ? json.data : [];
                },

                onSelect: async (item) => {
                    try {
                        const resp = await fetch(`${baseUrl}/api/buscar-persona?persona_id=${item.persona_id}`);
                        const json = await resp.json();
                        if (json.success && json.data) {
                            handlePersonaData(json.data, 'search');
                        }
                    } catch (err) {
                        console.error('Error fetching representante by ID:', err);
                    }
                }
            });
        }
    }

    // Expose clear function for the Limpiar Campos button
    window.CC.clearRepresentante = clearAndEnableAll;
    window.CC.showRepError = showRepError;
    window.CC.clearRepErrors = clearRepErrors;
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
                    // Caso publicado o no encontrado: redirigir a la lista
                    const msg = json.message || 'No se puede acceder a este caso.';
                    const baseUrl = (window.BASE_URL || '/tesis_francisco/public').replace(/\/+$/, '');
                    sessionStorage.setItem('cc_redirect_msg', msg);
                    window.location.replace(baseUrl + '/casos-sucesorales');
                    return;
                }
            } catch (err) {
                const baseUrl = (window.BASE_URL || '/tesis_francisco/public').replace(/\/+$/, '');
                sessionStorage.setItem('cc_redirect_msg', 'Error de conexión al cargar el caso.');
                window.location.replace(baseUrl + '/casos-sucesorales');
                return;
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
    initChecklist();

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

    const btnTour = $('#btnTourTutorial');
    if (btnTour) {
        btnTour.addEventListener('click', async () => {
            const { launchTour } = await import('./tour_loader.js');
            launchTour();
        });
    }
}

document.addEventListener('DOMContentLoaded', init);
