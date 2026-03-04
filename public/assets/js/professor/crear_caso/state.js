/**
 * state.js
 * Estado reactivo y constantes globales para la creación de casos.
 * 
 * NOTA: Las constantes hardcoded (PARENTESCOS, TIPOS_HERENCIA, etc.) se eliminan
 * progresivamente y se reemplazan por catálogos dinámicos desde la BD (Fase 2).
 * Por ahora se mantienen temporalmente las que aún no tienen endpoint.
 */

// ── Constantes temporales (se eliminarán en Fase 2 al migrar a catálogos dinámicos) ──

export const MOCK_STUDENTS = [
    { id: 1, nombre: "María García", cedula: "V-28.456.789" },
    { id: 2, nombre: "Carlos Rodríguez", cedula: "V-27.123.456" },
    { id: 3, nombre: "Ana Martínez", cedula: "V-29.876.543" },
    { id: 4, nombre: "José López", cedula: "V-26.543.210" },
    { id: 5, nombre: "Laura Pérez", cedula: "V-30.111.222" },
    { id: 6, nombre: "Diego Hernández", cedula: "V-28.333.444" },
    { id: 7, nombre: "Valentina Díaz", cedula: "V-27.555.666" },
    { id: 8, nombre: "Andrés Morales", cedula: "V-29.777.888" },
    { id: 9, nombre: "Sofía Herrera", cedula: "V-28.999.111" },
    { id: 10, nombre: "Miguel Castro", cedula: "V-26.222.333" },
    { id: 11, nombre: "Camila Rojas", cedula: "V-30.444.555" },
    { id: 12, nombre: "Gabriel Silva", cedula: "V-27.666.777" },
    { id: 13, nombre: "Isabella Romero", cedula: "V-29.123.987" },
    { id: 14, nombre: "Lucas Medina", cedula: "V-28.567.432" }
];

// ── State reactivo ──

function createReactiveState(initial, onChange) {
    const handler = {
        set(target, prop, value) {
            target[prop] = value;
            onChange(prop);
            return true;
        }
    };
    for (const key of Object.keys(initial)) {
        if (typeof initial[key] === 'object' && initial[key] !== null) {
            initial[key] = new Proxy(initial[key], handler);
        }
    }
    return new Proxy(initial, handler);
}

const onStateChange = (prop) => {
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
        parroquia: ''                // Texto libre asignado directamente
    },

    // ── Sección 6: Domicilio fiscal del causante (→ sim_persona_direcciones) ──
    // Nombres corregidos para coincidir con columnas de la BD
    domicilio_causante: {
        tipo_direccion: 'Domicilio_Fiscal',   // ENUM corregido
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
