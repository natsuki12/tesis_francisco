/**
 * tour.js — Tutorial Contextual Completo (Driver.js v2)
 * 
 * Detecta en qué paso del formulario está el profesor y lanza
 * un tour específico y exhaustivo para esa sección.
 * 
 * Características:
 * - DOM Guardian: omite elementos inexistentes o invisibles
 * - Auto-Expand: abre tarjetas colapsadas antes de iluminar su contenido
 * - Auto-Collapse: cierra las tarjetas previas para mantener la página compacta
 * - Tab-Aware: cambia sub-pestañas de inventario programáticamente
 * - Header-Target: apunta a headers de tarjetas altas para no recortar popovers
 */

// ── Helpers ──

/**
 * Detecta el step visible actualmente leyendo el DOM.
 */
function getCurrentStep() {
    for (var i = 0; i <= 3; i++) {
        var el = document.getElementById('step-' + i);
        if (el && el.style.display !== 'none') return i;
    }
    return 0;
}

/**
 * Verifica si un elemento es visible (tiene dimensiones reales).
 */
function isVisible(el) {
    if (!el) return false;
    var rect = el.getBoundingClientRect();
    return rect.width > 0 && rect.height > 0;
}

/**
 * Resuelve un step.element a un nodo DOM (acepta string o HTMLElement).
 */
function resolveElement(ref) {
    if (!ref) return null;
    if (typeof ref === 'string') return document.querySelector(ref);
    return ref;
}

/**
 * Expande una tarjeta colapsable si está cerrada.
 * Busca el .cc-card--collapsible más cercano al elemento dado.
 */
function ensureExpanded(el) {
    if (!el) return;
    var card = (el.classList && el.classList.contains('cc-card--collapsible'))
        ? el
        : el.closest('.cc-card--collapsible');
    if (!card) return;

    var body = card.querySelector('.cc-card__collapse');
    if (!body) return;

    if (body.style.display === 'none' || body.offsetHeight === 0) {
        var toggle = card.querySelector('.cc-card__toggle');
        if (toggle) toggle.click();
        void card.offsetHeight; // Forzar reflow síncrono
    }
}

/**
 * Colapsa todas las tarjetas colapsables del Step dado,
 * excepto la que contiene el elemento actual.
 */
function collapseOtherCards(currentEl, stepId) {
    var step = document.getElementById(stepId);
    if (!step) return;

    var currentCard = null;
    if (currentEl) {
        currentCard = (currentEl.classList && currentEl.classList.contains('cc-card--collapsible'))
            ? currentEl
            : currentEl.closest('.cc-card--collapsible');
    }

    step.querySelectorAll('.cc-card--collapsible').forEach(function(card) {
        if (card === currentCard) return;
        var body = card.querySelector('.cc-card__collapse');
        if (body && body.style.display !== 'none' && body.offsetHeight > 0) {
            var toggle = card.querySelector('.cc-card__toggle');
            if (toggle) toggle.click();
        }
    });
}


/**
 * Hace clic programático en una pestaña de inventario.
 */
function clickTab(tabName) {
    var tab = document.querySelector('[data-tab="' + tabName + '"]');
    if (tab) tab.click();
}

/**
 * Filtra pasos cuyo elemento no existe o no es visible,
 * y ejecuta acciones previas (expand/tab/collapse) antes de iluminar.
 */
function purifySteps(rawSteps) {
    return rawSteps
        .filter(function(step) {
            if (!step.element) return true;
            var el = resolveElement(step.element);
            if (!el) return false;
            // Si el paso tiene _expand, no verificar visibilidad (se abrirá en onHighlightStarted)
            if (step._expand) return true;
            return isVisible(el);
        })
        .map(function(step) {
            var clean = {};
            for (var k in step) {
                if (k !== '_expand' && k !== '_clickTab' && k !== '_collapseStep') {
                    clean[k] = step[k];
                }
            }

            var needsExpand = step._expand;
            var needsTab = step._clickTab;
            var collapseStep = step._collapseStep;
            var originalOnHighlight = step.onHighlightStarted;

            if (needsExpand || needsTab || collapseStep || originalOnHighlight) {
                clean.onHighlightStarted = function(element, stepObj, opts) {
                    if (collapseStep) collapseOtherCards(element, collapseStep);
                    if (needsTab) clickTab(needsTab);
                    if (needsExpand) ensureExpanded(element);
                    if (originalOnHighlight) originalOnHighlight(element, stepObj, opts);
                };
            }

            return clean;
        });
}

// ── Tour Step 0: Datos del Caso y Causante ──

function getTourStep0() {
    var cards = document.querySelectorAll('#step-0 > .cc-card--collapsible');

    var steps = [
        {
            element: '#stepper',
            popover: {
                title: 'Navegación por Pasos',
                description: 'El formulario se divide en 4 etapas. Puedes hacer clic en cualquiera de ellas para saltar directamente, o usar los botones de abajo.',
                side: 'bottom', align: 'center'
            }
        },
        {
            element: '#btnChecklistToggle',
            popover: {
                title: 'Checklist de Progreso',
                description: 'Abre este panel lateral para ver un resumen visual de qué requisitos te faltan antes de poder publicar el caso.',
                side: 'bottom', align: 'center'
            }
        },
        {
            element: '#btnSaveDraft',
            popover: {
                title: 'Guardar Borrador',
                description: 'Tu progreso se guarda localmente mientras escribes, pero usa este botón para enviarlo al servidor y poder continuar otro día.',
                side: 'bottom', align: 'end'
            }
        }
    ];

    // Card 0: Datos del Caso
    if (cards[0]) {
        steps.push({
            element: cards[0],
            _expand: true,
            _collapseStep: 'step-0',
            popover: {
                title: 'Datos del Caso',
                description: 'Define el título y la narrativa del escenario que verán tus estudiantes. Las tarjetas se pueden colapsar haciendo clic en su cabecera.',
                side: 'bottom', align: 'center'
            }
        });
    }

    // Card 1: Tipo de Sucesión
    if (cards[1]) {
        steps.push({
            element: cards[1],
            _expand: true,
            _collapseStep: 'step-0',
            popover: {
                title: 'Tipo de Sucesión',
                description: 'Indica si el causante tiene "Con Cédula" o "Sin Cédula". Al elegir "Sin Cédula" aparecerán campos adicionales para el Acta de Defunción.',
                side: 'bottom', align: 'center'
            }
        });
    }

    // Card 2: Tipo de Herencia
    if (cards[2]) {
        steps.push({
            element: cards[2],
            _expand: true,
            _collapseStep: 'step-0',
            popover: {
                title: 'Tipo de Herencia',
                description: 'Selecciona una o varias opciones: Testada, Intestada, Contractual o Mixta. Algunos tipos activan campos extra como número de testamento.',
                side: 'bottom', align: 'center'
            }
        });
    }

    // Card 3: Datos del Causante (card completo)
    if (cards[3]) {
        steps.push({
            element: cards[3],
            _expand: true,
            _collapseStep: 'step-0',
            popover: {
                title: 'Datos del Causante',
                description: 'Aquí registras los datos de la persona fallecida cuya sucesión se analiza. Puedes buscar una persona existente o llenar los campos manualmente.',
                side: 'bottom', align: 'center'
            }
        });
    }

    // Card 3: Buscador de persona
    steps.push({
        element: '#causanteSearchContainer',
        _expand: true,
        popover: {
            title: 'Buscar Persona Existente',
            description: 'Escribe una cédula, RIF o nombre para buscar un causante ya registrado en el sistema. Si lo encuentras, los datos se llenan y bloquean automáticamente.',
            side: 'bottom', align: 'center'
        }
    });

    // Card 3: Campos de filiación
    steps.push({
        element: '[data-bind="causante.cedula"]',
        _expand: true,
        popover: {
            title: 'Filiación del Causante',
            description: 'Completa cédula, nombres, apellidos, sexo, estado civil y las fechas de nacimiento y fallecimiento. Todos estos campos son obligatorios.',
            side: 'top', align: 'center'
        }
    });

    // Card 4: Domicilio Fiscal (tarjeta alta, scroll libre)
    if (cards[4]) {
        steps.push({
            element: cards[4],
            _expand: true,
            _collapseStep: 'step-0',
            popover: {
                title: 'Domicilio Fiscal y Direcciones',
                description: 'Registra la dirección fiscal del causante. Puedes hacer scroll para ver todo el formulario de dirección. Se pueden agregar múltiples direcciones.',
                side: 'bottom', align: 'center'
            }
        });
    }

    // Card 5: Prórrogas
    steps.push({
        element: '#card_prorrogas',
        _expand: true,
        _collapseStep: 'step-0',
        popover: {
            title: 'Prórrogas',
            description: 'Opcional. Si la declaración sucesoral tuvo prórrogas concedidas, regístralas aquí con su fecha de solicitud, resolución y plazo otorgado.',
            side: 'bottom', align: 'center'
        }
    });

    // Navegación
    steps.push({
        element: '.cc-bottomnav__right',
        popover: {
            title: '¡Siguiente Paso!',
            description: 'Cuando termines esta sección, presiona "Siguiente" para pasar a declarar el representante y los herederos.',
            side: 'top', align: 'end'
        }
    });

    return steps;
}

// ── Tour Step 1: Relaciones de la Sucesión ──

function getTourStep1() {
    var cards = document.querySelectorAll('#step-1 > .cc-card--collapsible');

    var steps = [];

    // Card 0: Representante
    if (cards[0]) {
        steps.push({
            element: cards[0],
            _expand: true,
            _collapseStep: 'step-1',
            popover: {
                title: 'Representante / Apoderado',
                description: 'Es opcional. Si el caso lo requiere, busca o registra a la persona designada ante el SENIAT. Puedes dejar esta sección completamente vacía.',
                side: 'bottom', align: 'center'
            }
        });
    }

    // Buscador de representante
    steps.push({
        element: '#inputBuscarRepresentante',
        _expand: true,
        popover: {
            title: 'Búsqueda Inteligente',
            description: 'Escribe cédula, RIF o nombre. Si la persona ya existe en el sistema, sus datos se llenarán y bloquearán automáticamente para evitar errores.',
            side: 'bottom', align: 'center'
        }
    });

    // Card 1: Herederos
    if (cards[1]) {
        steps.push({
            element: cards[1],
            _expand: true,
            _collapseStep: 'step-1',
            popover: {
                title: 'Declarar Herederos',
                description: 'Presiona "Agregar Heredero" para abrir un formulario emergente. Necesitas registrar al menos un heredero para poder publicar el caso.',
                side: 'bottom', align: 'center'
            }
        });
    }

    // Card: Premuertos — siempre explicar, con o sin tarjeta visible
    var premuertosEl = document.getElementById('card_premuertos');
    var premuertosVisible = premuertosEl && isVisible(premuertosEl);

    if (premuertosVisible) {
        steps.push({
            element: '#card_premuertos',
            _expand: true,
            popover: {
                title: 'Herederos del Premuerto',
                description: 'Esta tarjeta apareció porque un heredero fue marcado como premuerto. Aquí registras a sus descendientes que lo representan en la herencia.',
                side: 'top', align: 'center'
            }
        });
    } else {
        steps.push({
            popover: {
                title: 'Herederos del Premuerto',
                description: 'Si marcas a un heredero como "premuerto" en la tabla, aparecerá una tarjeta adicional para registrar a sus descendientes representantes. Actualmente no hay premuertos declarados.',
                align: 'center'
            }
        });
    }

    // Navegación
    steps.push({
        element: '.cc-bottomnav__right',
        popover: {
            title: 'Siguiente: Inventario',
            description: 'Con al menos un heredero registrado, avanza para declarar los bienes que conforman el patrimonio del causante.',
            side: 'top', align: 'end'
        }
    });

    return steps;
}

// ── Tour Step 2: Inventario Patrimonial ──

function getTourStep2() {
    return [
        {
            element: '#inventarioTabs',
            popover: {
                title: 'Categorías del Patrimonio',
                description: 'El inventario se organiza en 6 pestañas. Cada una representa un tipo de activo, pasivo o exclusión. Navega entre ellas según lo que necesites declarar.',
                side: 'bottom', align: 'center'
            }
        },
        {
            element: '[data-tab="inmuebles"]',
            _clickTab: 'inmuebles',
            popover: {
                title: 'Bienes Inmuebles',
                description: 'Registra propiedades como casas, terrenos o locales comerciales. Necesitas declarar al menos 1 bien (inmueble o mueble) para publicar el caso.',
                side: 'bottom', align: 'start'
            }
        },
        {
            element: '[data-tab="muebles"]',
            popover: {
                title: 'Bienes Muebles',
                description: 'Incluye cuentas bancarias, vehículos, acciones y otros activos del causante. Se subdividen por tipo en sub-pestañas internas.',
                side: 'bottom', align: 'center'
            }
        },
        {
            element: '[data-tab="pasivos"]',
            popover: {
                title: 'Pasivos (Deudas)',
                description: 'Las deudas pendientes del causante se descuentan del patrimonio bruto para calcular el monto neto sobre el cual se aplica el impuesto.',
                side: 'bottom', align: 'center'
            }
        },
        {
            element: '[data-tab="exenciones"]',
            popover: {
                title: 'Exenciones / Exoneraciones',
                description: 'Bienes o montos que están excluidos del gravamen por disposición legal. Se registran tanto exenciones como exoneraciones por separado.',
                side: 'bottom', align: 'center'
            }
        },
        {
            element: '[data-tab="desgravamenes"]',
            popover: {
                title: 'Desgravámenes',
                description: 'Deducciones permitidas por ley. Aparecerán para que el estudiante las registre durante la simulación en el portal SENIAT.',
                side: 'bottom', align: 'center'
            }
        },
        {
            element: '[data-tab="litigiosos"]',
            popover: {
                title: 'Bienes Litigiosos',
                description: 'Vista consolidada de bienes en disputa legal. Se activan automáticamente al marcar cualquier bien inmueble o mueble como litigioso.',
                side: 'bottom', align: 'center'
            }
        },
        {
            element: '.cc-bottomnav__right',
            popover: {
                title: 'Siguiente: Resumen Final',
                description: 'Con el inventario completo, avanza al Resumen donde verás el cálculo automático del impuesto y podrás publicar el caso.',
                side: 'top', align: 'end'
            }
        }
    ];
}

// ── Tour Step 3: Resumen y Publicación ──

function getTourStep3() {
    // Buscar la card que contiene la tabla de cálculo
    var calculoTable = document.querySelector('.cc-resumen-table');
    var calculoCard = calculoTable ? calculoTable.closest('.cc-card') : null;

    return [
        {
            element: '#step-3 > .cc-card:nth-child(1)',
            popover: {
                title: 'Resumen del Caso',
                description: 'Aquí se muestra un consolidado de título, causante, herencia, herederos y representante. Puedes hacer scroll para revisarlo completo.',
                side: 'bottom', align: 'center'
            }
        },
        {
            element: calculoCard || '.cc-resumen-table',
            popover: {
                title: 'Resumen Patrimonial y Tributo',
                description: 'Esta tabla de 14 filas calcula automáticamente: bienes inmuebles y muebles (filas 1-2), patrimonio bruto (3-4), exclusiones como desgravámenes, exenciones y exoneraciones (5-8), patrimonio neto (9-11) y la determinación final del impuesto (12-14). Haz scroll para revisarla.',
                side: 'top', align: 'center'
            }
        },
        {
            element: '#btnAbrirCalculoManual',
            popover: {
                title: 'Cálculo Manual',
                description: 'Si necesitas ajustar las cuotas por heredero, presiona este botón para abrir una ventana donde podrás sobrescribir los valores y simular escenarios personalizados.',
                side: 'bottom', align: 'start'
            }
        },
        {
            element: '#btnPublish',
            popover: {
                title: '¡Publicar el Caso!',
                description: 'Cuando todo esté completo y verificado, presiona aquí para que el caso quede disponible y puedas asignarlo a tus estudiantes.',
                side: 'top', align: 'end'
            }
        }
    ];
}

// ── Función Principal ──

export function startTour() {
    if (!window.driver || !window.driver.js || typeof window.driver.js.driver !== 'function') {
        console.error('Librería Driver.js no encontrada.');
        return;
    }

    var driverFn = window.driver.js.driver;
    var currentStep = getCurrentStep();

    var rawSteps;
    switch (currentStep) {
        case 0:  rawSteps = getTourStep0(); break;
        case 1:  rawSteps = getTourStep1(); break;
        case 2:  rawSteps = getTourStep2(); break;
        case 3:  rawSteps = getTourStep3(); break;
        default: rawSteps = getTourStep0();
    }

    var steps = purifySteps(rawSteps);

    if (steps.length === 0) {
        console.warn('No se encontraron elementos válidos para el tour en este paso.');
        return;
    }

    // Scroll Forwarder: .sim-main scrollea, no window
    var mainEl = document.querySelector('.sim-main');
    var scrollForwarder = function() {
        window.dispatchEvent(new Event('scroll'));
    };
    if (mainEl) {
        mainEl.addEventListener('scroll', scrollForwarder, { passive: true });
    }

    var driverObj = driverFn({
        showProgress: true,
        animate: true,
        nextBtnText: 'Siguiente &rarr;',
        prevBtnText: '&larr; Atrás',
        doneBtnText: 'Entendido',
        progressText: 'Paso {{current}} de {{total}}',
        onDestroyed: function() {
            if (mainEl) mainEl.removeEventListener('scroll', scrollForwarder);
        },
        steps: steps
    });

    driverObj.drive();
}
