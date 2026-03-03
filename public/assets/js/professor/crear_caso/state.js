/**
 * state.js
 * Estado reactivo y constantes globales para la creación de casos.
 */

export const PARENTESCOS = [
    "Cónyuge", "Concubina/Concubino", "Padre", "Madre", "Hijo/a",
    "Hermano/a Simple Conjunción", "Hermano/a Doble Conjunción",
    "Sobrino/a", "Tío/a", "Primo/a", "Abuelo/a",
    "Otro Ascendiente", "Otro Pariente"
];

export const TIPOS_HERENCIA = [
    "Testamento", "Ab-Intestato", "Pura y Simple",
    "Presunción de Ausencia", "Presunción de Muerte por Accidente", "Beneficio de Inventario"
];

export const TIPOS_BIEN_INMUEBLE = [
    "Anexo", "Apartamento", "Bienhechurías", "Casa", "Construcción destinada a Explotación",
    "Consultorio", "Edificio", "Galpón", "Hotel o Similar", "Inmueble en Construcción",
    "Local", "Maletero", "Mixto (Residencia/Apartamento/Comercial)", "Oficina",
    "Otros Específique", "Parcela", "Puesto de Estacionamiento", "Quinta",
    "Resort", "Terreno", "Townhouse"
];

export const CATEGORIAS_MUEBLE = [
    { key: "banco", label: "Banco", icon: "🏦" },
    { key: "seguro", label: "Seguro", icon: "🛡️" },
    { key: "transporte", label: "Transporte", icon: "🚗" },
    { key: "opciones_compra", label: "Opciones de Compra", icon: "📋" },
    { key: "cuentas_cobrar", label: "Cuentas y Efectos por Cobrar", icon: "📄" },
    { key: "semovientes", label: "Semovientes", icon: "🐄" },
    { key: "bonos", label: "Bonos", icon: "📊" },
    { key: "acciones", label: "Acciones", icon: "📈" },
    { key: "prestaciones", label: "Prestaciones Sociales", icon: "👷" },
    { key: "caja_ahorro", label: "Caja de Ahorro", icon: "💰" },
    { key: "plantaciones", label: "Plantaciones", icon: "🌿" },
    { key: "otros", label: "Otros", icon: "📦" },
];

export const TIPOS_PASIVO_DEUDA = [
    { key: "tdc", label: "Tarjetas de Crédito" },
    { key: "hipotecario", label: "Crédito Hipotecario" },
    { key: "prestamos", label: "Préstamos, Cuentas y Efectos por Pagar" },
    { key: "otros", label: "Otros" },
];

export const TIPOS_PASIVO_GASTO = [
    "Exequias", "Apertura de Testamento", "Avalúo", "Declaración de Herencia",
    "Honorarios", "Servicios Funerarios", "Otros (especifique)"
];

export const MOCK_STUDENTS = [
    { id: 1, nombre: "María García", cedula: "V-28.456.789" },
    { id: 2, nombre: "Carlos Rodríguez", cedula: "V-27.123.456" },
    { id: 3, nombre: "Ana Martínez", cedula: "V-29.876.543" },
    { id: 4, nombre: "José López", cedula: "V-26.543.210" },
    { id: 5, nombre: "Laura Pérez", cedula: "V-30.111.222" },
    { id: 6, nombre: "Diego Hernández", cedula: "V-28.333.444" },
    { id: 7, nombre: "Valentina Díaz", cedula: "V-27.555.666" },
    { id: 8, nombre: "Andrés Morales", cedula: "V-29.777.888" },
];

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
        caseData.caso.modalidad === 'Evaluacion' ? show(flField) : hide(flField);
    }
};

export const caseData = createReactiveState({
    caso: { titulo: '', descripcion: '', modalidad: '', max_intentos: '0', fecha_limite: '', estado: 'Borrador' },
    herencia: { tipos: [] },
    causante: { tipo_cedula: '', sexo: '', estado_civil: '', nacionalidad: '', cedula: '', pasaporte: '', rif_personal: '', nombres: '', apellidos: '', fecha_nacimiento: '', fecha_fallecimiento: '', valor_ut: '' },
    domicilio_causante: { tipo_direccion: 'DOMICILIO FISCAL', vialidad: '', tipo_vivienda: '', nombre_vialidad: '', nombre_vivienda: '', sub_vivienda: '', tipo_sector: '', nro_piso: '', nombre_sector: '', estado: '', municipio: '', parroquia: '', ciudad: '', telefono_fijo: '', telefono_celular: '', fax: '', zona_postal: '', punto_referencia: '' },
    representante: { tipo_cedula: '', cedula: '', nombres: '', apellidos: '' },
    herederos: [],
    bienes_inmuebles: [],
    bienes_muebles: {},
    pasivos_deuda: [],
    pasivos_gastos: [],
    exenciones: [],
    exoneraciones: [],
    estudiantes_asignados: [],
}, onStateChange);

// Variables that need to be globally mutated
export const UIState = {
    currentStep: 0,
    currentSubTab: 'banco',
    currentModalType: null,
    editIndex: null,
};
