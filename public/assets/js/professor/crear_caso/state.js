/**
 * state.js
 * Estado reactivo y constantes globales para la creación de casos.
 * 
 * NOTA: Las constantes hardcoded (PARENTESCOS, TIPOS_HERENCIA, etc.) se eliminan
 * progresivamente y se reemplazan por catálogos dinámicos desde la BD (Fase 2).
 * Por ahora se mantienen temporalmente las que aún no tienen endpoint.
 */

const STORAGE_KEY = 'crearCaso_caseData';
const STORAGE_KEY_STEP = 'crearCaso_currentStep';

let _saveTimer = null;
export function saveCaseData() {
    clearTimeout(_saveTimer);
    _saveTimer = setTimeout(() => {
        try {
            const plain = JSON.parse(JSON.stringify(caseData));
            sessionStorage.setItem(STORAGE_KEY, JSON.stringify(plain));
            sessionStorage.setItem(STORAGE_KEY_STEP, UIState.currentStep.toString());
        } catch (e) { /* silently fail */ }
    }, 300);
}

export function loadCaseData() {
    try {
        const saved = sessionStorage.getItem(STORAGE_KEY);
        if (!saved) return false;
        const parsed = JSON.parse(saved);
        // Restore each top-level key into the reactive proxy
        for (const key of Object.keys(parsed)) {
            if (key in caseData) {
                if (Array.isArray(parsed[key])) {
                    caseData[key].length = 0;
                    parsed[key].forEach(item => caseData[key].push(item));
                } else if (typeof parsed[key] === 'object' && parsed[key] !== null) {
                    Object.assign(caseData[key], parsed[key]);
                } else {
                    caseData[key] = parsed[key];
                }
            }
        }
        // Restore step
        const savedStep = sessionStorage.getItem(STORAGE_KEY_STEP);
        if (savedStep !== null) UIState.currentStep = parseInt(savedStep, 10) || 0;
        return true;
    } catch (e) { return false; }
}

export function clearSavedCaseData() {
    sessionStorage.removeItem(STORAGE_KEY);
    sessionStorage.removeItem(STORAGE_KEY_STEP);
}

// ── State reactivo ──

const ARRAY_MUTATORS = ['push', 'pop', 'shift', 'unshift', 'splice', 'sort', 'reverse'];

function makeReactive(obj, onChange) {
    if (obj === null || typeof obj !== 'object') return obj;

    // Para arrays: interceptar métodos mutadores
    if (Array.isArray(obj)) {
        const handler = {
            get(target, prop, receiver) {
                const value = Reflect.get(target, prop, receiver);
                if (ARRAY_MUTATORS.includes(prop)) {
                    return function (...args) {
                        const result = Array.prototype[prop].apply(target, args);
                        onChange(prop);
                        return result;
                    };
                }
                return value;
            },
            set(target, prop, value) {
                target[prop] = (typeof value === 'object' && value !== null && !Array.isArray(value))
                    ? makeReactive(value, onChange)
                    : value;
                onChange(prop);
                return true;
            }
        };
        return new Proxy(obj, handler);
    }

    // Para objetos planos: interceptar set
    const handler = {
        set(target, prop, value) {
            target[prop] = value;
            onChange(prop);
            return true;
        }
    };

    for (const key of Object.keys(obj)) {
        if (Array.isArray(obj[key])) {
            obj[key] = makeReactive(obj[key], onChange);
        } else if (typeof obj[key] === 'object' && obj[key] !== null) {
            obj[key] = new Proxy(obj[key], handler);
        }
    }
    return new Proxy(obj, handler);
}

function createReactiveState(initial, onChange) {
    return makeReactive(initial, onChange);
}

const onStateChange = (prop) => {
    saveCaseData();
    const { $, show, hide } = document.ccHelpers; // Inject helpers for binding
    if (!$) return;
    const flField = $('#fieldFechaLimite');
    if (flField) {
        // Sección 1: modalidad ahora vive en config, no en caso
        caseData.config.modalidad === 'Evaluacion' ? show(flField) : hide(flField);
    }

    const bSeccion = $('#bloqueSeccion');
    const bEstudiantes = $('#bloqueEstudiantes');
    if (bSeccion && bEstudiantes) {
        if (caseData.config.tipo_asignacion === 'Seccion') {
            show(bSeccion);
            hide(bEstudiantes);
        } else if (caseData.config.tipo_asignacion === 'Estudiantes') {
            hide(bSeccion);
            show(bEstudiantes);
        }
    }

    const bTipoCedula = $('#campo_tipo_cedula_causante');
    const bCedula = $('#campo_cedula_causante');
    const bActa = $('#bloque_acta_defuncion');

    if (bTipoCedula && bCedula && bActa) {
        if (caseData.caso.tipo_sucesion === 'Con Cédula') {
            show(bTipoCedula);
            show(bCedula);
            hide(bActa);
        } else if (caseData.caso.tipo_sucesion === 'Sin Cédula') {
            hide(bTipoCedula);
            hide(bCedula);
            show(bActa);
        }
    }
};

export const caseData = createReactiveState({
    // ── Sección 1: Datos del caso (→ sim_casos_estudios) ──
    caso: {
        titulo: '',
        descripcion: '',
        estado: 'Borrador',
        tipo_sucesion: 'Con Cédula'
    },

    // ── Sección 1: Configuración de asignación (→ sim_caso_configs) ──
    config: {
        modalidad: '',
        max_intentos: '0',
        fecha_limite: '',
        tipo_asignacion: 'Seccion',
        seccion_id: ''
    },

    // ── Sección 3: Tipos de herencia (→ sim_caso_tipoherencia_rel) ──
    herencia: { tipos: [] },

    // ── Datos del causante (→ sim_personas) ──
    causante: {
        tipo_cedula: '', sexo: '', estado_civil: '', nacionalidad: '',
        cedula: '', pasaporte: '', rif_personal: '',
        nombres: '', apellidos: '',
        fecha_nacimiento: '', fecha_fallecimiento: ''
        // NOTA: valor_ut eliminado — ahora es caso.unidad_tributaria_id (Sección 2)
    },

    // ── Sección 4: Datos fiscales del causante (→ sim_causante_datos_fiscales) ──
    datos_fiscales_causante: {
        domiciliado_pais: '1',       // NOT NULL, default 1 (Sí)
        fecha_cierre_fiscal: ''      // NOT NULL — requerido
    },

    // ── Sección 4: Acta de defunción (→ sim_actas_defunciones) ──
    acta_defuncion: {
        numero_acta: '',
        year_acta: '',               // NOT NULL (CHECK >= 1900)
        parroquia_registro_id: ''    // FK a parroquias — coincide con columna en sim_actas_defunciones
    },

    // ── Sección 6: Domicilio fiscal del causante (→ sim_persona_direcciones) ──
    // Nombres corregidos para coincidir con columnas de la BD
    domicilio_causante: {
        tipo_direccion: 'Casa_Matriz_Establecimiento_Principal',   // ENUM válido en sim_persona_direcciones
        tipo_vialidad: '',                     // antes: vialidad
        tipo_inmueble: '',                     // antes: tipo_vivienda
        nombre_vialidad: '',
        nro_inmueble: '',                      // antes: nombre_vivienda
        tipo_nivel: '',                        // antes: sub_vivienda
        tipo_sector: '',
        nro_nivel: '',                         // antes: nro_piso
        nombre_sector: '',
        estado: '', municipio: '', parroquia: '', ciudad: '',
        telefono_fijo: '', telefono_celular: '', fax: '',
        codigo_postal_id: '',                  // antes: zona_postal
        punto_referencia: ''
    },
    direcciones_causante: [],

    // ── Sección 7: Representante de la sucesión (→ sim_personas) ──
    // Campos NOT NULL agregados: sexo, estado_civil, fecha_nacimiento
    representante: {
        tipo_cedula: 'Cédula', letra_cedula: 'V', cedula: '', nombres: '', apellidos: '',
        sexo: '', estado_civil: '', fecha_nacimiento: '',
        pasaporte: '', rif_personal: '', nacionalidad: ''
    },

    herederos: [],
    herederos_premuertos: [],
    bienes_inmuebles: [],
    bienes_muebles: {},
    pasivos_deuda: [],
    pasivos_gastos: [],
    exenciones: [],
    exoneraciones: [],

    // ── Sección 13: Prórrogas (→ sim_caso_prorrogas) ──
    prorroga: {
        fecha_solicitud: '', nro_resolucion: '', fecha_resolucion: '',
        plazo_dias: '', fecha_vencimiento: ''
    },
    prorrogas: [],

    estudiantes_asignados: [],
}, onStateChange);

// Variables that need to be globally mutated
export const UIState = {
    currentStep: 0,
    currentSubTab: null,
    currentModalType: null,
    editIndex: null,
    editDireccionIndex: null,
};
