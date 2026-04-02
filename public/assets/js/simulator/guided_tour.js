/**
 * ═══════════════════════════════════════════════════════════════════════════
 * guided_tour.js — Motor de tours guiados para el simulador SENIAT (SUCELAB)
 * ═══════════════════════════════════════════════════════════════════════════
 *
 * Archivo único que gestiona todos los tours de Driver.js del simulador.
 * Se activa SOLO cuando la modalidad es "Práctica Guiada".
 *
 * ARQUITECTURA:
 *   1. GUARDS         — Verifica modalidad y dependencias
 *   2. CONFIG         — Configuración base de Driver.js (idioma, estilos)
 *   3. UTILIDADES     — Detección de página, localStorage, scroll-lock
 *   4. TOURS          — Definiciones de tours principales (auto-start por página)
 *   5. SUB-TOURS      — Tours contextuales disparados por interacción del usuario
 *   6. LISTENERS      — Event listeners que disparan sub-tours
 *   7. API PÚBLICA    — window.startGuidedTour(), window.initGuidedTourForPage()
 *   8. BOOTSTRAP      — Auto-start y setup de listeners al cargar
 *
 * DEPENDENCIAS:
 *   - driver.js IIFE (cargado en public/assets/js/lib/)
 *   - window.SIM_TOUR_STATE (inyectado desde logged_layout.php)
 *     Contiene: { modalidad, intentoId, tieneRif, estaRegistrado, estaLoggeado }
 *
 * CÓMO AGREGAR UN TOUR NUEVO:
 *   1. Agregar la detección de URL en detectPageKey()
 *   2. Crear TOURS.mi_pagina = function(s) { ... } con los steps
 *   3. (Opcional) Si necesita sub-tours, crear en SUBTOURS y su listener
 *   4. Registrar el listener en autoStart() si aplica
 *
 * NOTA SOBRE SCROLL: El layout usa .sim-main con overflow-y:auto como scroll
 * container (body es overflow:hidden). Driver.js renderiza su overlay en body,
 * por lo que el scroll se bloquea durante el tour para evitar desalineamiento
 * del overlay SVG con los elementos resaltados.
 * ═══════════════════════════════════════════════════════════════════════════
 */
(function () {
    'use strict';

    // ╔═══════════════════════════════════════════════════════════════════╗
    // ║  1. GUARDS — Verificación de modalidad y dependencias           ║
    // ╚═══════════════════════════════════════════════════════════════════╝

    var state = window.SIM_TOUR_STATE;
    if (!state || state.modalidad !== 'Practica_guiada') return;

    var driverFn = window.driver && window.driver.js && window.driver.js.driver;
    if (!driverFn) {
        console.warn('[GuidedTour] Driver.js not loaded');
        return;
    }

    // ╔═══════════════════════════════════════════════════════════════════╗
    // ║  2. CONFIG — Configuración base de Driver.js                    ║
    // ╚═══════════════════════════════════════════════════════════════════╝

    var baseConfig = {
        showProgress: true,
        animate: true,
        overlayColor: 'rgba(0, 0, 0, 0.65)',
        stagePadding: 10,
        stageRadius: 8,
        popoverClass: 'sucelab-tour-popover',   // CSS: guided_tour.css
        nextBtnText: 'Siguiente →',
        prevBtnText: '← Anterior',
        doneBtnText: '¡Entendido!',
        progressText: '{{current}} de {{total}}',
        allowClose: true,
        smoothScroll: true
    };

    // ╔═══════════════════════════════════════════════════════════════════╗
    // ║  3. UTILIDADES — Detección de página, localStorage, scroll      ║
    // ╚═══════════════════════════════════════════════════════════════════╝

    /**
     * Detecta en qué página del simulador estamos.
     * Retorna un string clave (ej: 'seniat_index', 'inscripcion_rif')
     * o null si no estamos en una página con tour definido.
     *
     * IMPORTANTE: Las rutas exactas (===) evitan que sub-páginas del wizard
     * (ej: /inscripcion-rif/datos-basicos) disparen el tour equivocado.
     */
    function detectPageKey() {
        var path = window.location.pathname;
        var idx = path.indexOf('/simulador');
        if (idx === -1) return null;
        var simPath = path.substring(idx);

        if (simPath === '/simulador' || simPath === '/simulador/') return 'seniat_index';
        if (simPath === '/simulador/inscripcion-rif' || simPath === '/simulador/inscripcion-rif/') return 'inscripcion_rif';
        if (simPath === '/simulador/inscripcion-rif/datos-basicos' || simPath === '/simulador/inscripcion-rif/datos-basicos/') return 'datos_basicos_rif';
        if (simPath === '/simulador/inscripcion-rif/direcciones' || simPath === '/simulador/inscripcion-rif/direcciones/') return 'direcciones_rif';
        if (simPath === '/simulador/inscripcion-rif/relaciones' || simPath === '/simulador/inscripcion-rif/relaciones/') return 'relaciones_rif';
        if (simPath === '/simulador/inscripcion-rif/validar-inscripcion' || simPath === '/simulador/inscripcion-rif/validar-inscripcion/') return 'validar_inscripcion_rif';
        if (simPath === '/simulador/servicios_declaracion') return 'servicios_declaracion';
        if (simPath === '/simulador/servicios_declaracion/sistemas' || simPath === '/simulador/servicios_declaracion/sistemas/') return 'acceder_sistemas';
        if (simPath === '/simulador/servicios_declaracion/dashboard' || simPath === '/simulador/servicios_declaracion/dashboard/') return 'sistemas_dashboard';
        if (simPath === '/simulador/registro/contribuyente/paso-2' || simPath === '/simulador/registro/contribuyente/paso-2/') return 'registro_contribuyente_paso2';
        if (simPath === '/simulador/registro/contribuyente' || simPath === '/simulador/registro/contribuyente/') return 'registro_contribuyente';
        if (simPath.indexOf('/simulador/portal') === 0) return 'seniat_portal_nuevo';
        if (simPath === '/simulador/sucesion/principal' || simPath === '/simulador/sucesion/principal/') return 'sucesion_principal';
        if (simPath === '/simulador/sucesion/herencia' || simPath === '/simulador/sucesion/herencia/') return 'sucesiones_herencia';
        if (simPath === '/simulador/sucesion/prorrogas' || simPath === '/simulador/sucesion/prorrogas/') return 'sucesiones_prorrogas';
        if (simPath === '/simulador/sucesion/herederos' || simPath === '/simulador/sucesion/herederos/') return 'sucesiones_herederos';
        if (simPath === '/simulador/sucesion/herederos_premuerto' || simPath === '/simulador/sucesion/herederos_premuerto/') return 'sucesiones_herederos_premuerto';
        if (simPath === '/simulador/sucesion/bienes_inmuebles' || simPath === '/simulador/sucesion/bienes_inmuebles/') return 'sucesiones_bienes_inmuebles';
        if (simPath === '/simulador/sucesion/bienes_muebles/banco' || simPath === '/simulador/sucesion/bienes_muebles/banco/') return 'sucesiones_muebles_banco';
        if (simPath === '/simulador/sucesion/bienes_muebles/seguro' || simPath === '/simulador/sucesion/bienes_muebles/seguro/') return 'sucesiones_muebles_seguro';
        if (simPath === '/simulador/sucesion/bienes_muebles/transporte' || simPath === '/simulador/sucesion/bienes_muebles/transporte/') return 'sucesiones_muebles_transporte';
        if (simPath === '/simulador/sucesion/bienes_muebles/opciones_compra' || simPath === '/simulador/sucesion/bienes_muebles/opciones_compra/') return 'sucesiones_muebles_opciones_compra';
        if (simPath === '/simulador/sucesion/bienes_muebles/cuentas_efectos' || simPath === '/simulador/sucesion/bienes_muebles/cuentas_efectos/') return 'sucesiones_muebles_cuentas_efectos';
        if (simPath === '/simulador/sucesion/bienes_muebles/semovientes' || simPath === '/simulador/sucesion/bienes_muebles/semovientes/') return 'sucesiones_muebles_semovientes';
        if (simPath === '/simulador/sucesion/bienes_muebles/bonos' || simPath === '/simulador/sucesion/bienes_muebles/bonos/') return 'sucesiones_muebles_bonos';
        if (simPath === '/simulador/sucesion/bienes_muebles/acciones' || simPath === '/simulador/sucesion/bienes_muebles/acciones/') return 'sucesiones_muebles_acciones';
        if (simPath === '/simulador/sucesion/bienes_muebles/prestaciones_sociales' || simPath === '/simulador/sucesion/bienes_muebles/prestaciones_sociales/') return 'sucesiones_muebles_prestaciones';
        if (simPath === '/simulador/sucesion/bienes_muebles/caja_ahorro' || simPath === '/simulador/sucesion/bienes_muebles/caja_ahorro/') return 'sucesiones_muebles_caja_ahorro';
        if (simPath === '/simulador/sucesion/bienes_muebles/plantaciones' || simPath === '/simulador/sucesion/bienes_muebles/plantaciones/') return 'sucesiones_muebles_plantaciones';
        if (simPath === '/simulador/sucesion/bienes_muebles/otros' || simPath === '/simulador/sucesion/bienes_muebles/otros/') return 'sucesiones_muebles_otros';
        if (simPath === '/simulador/sucesion/pasivos_deuda/tarjetas_credito' || simPath === '/simulador/sucesion/pasivos_deuda/tarjetas_credito/') return 'sucesiones_pasivos_tdc';
        if (simPath === '/simulador/sucesion/pasivos_deuda/credito_hipotecario' || simPath === '/simulador/sucesion/pasivos_deuda/credito_hipotecario/') return 'sucesiones_pasivos_ch';
        if (simPath === '/simulador/sucesion/pasivos_deuda/prestamos' || simPath === '/simulador/sucesion/pasivos_deuda/prestamos/') return 'sucesiones_pasivos_prestamos';
        if (simPath === '/simulador/sucesion/pasivos_deuda/otros' || simPath === '/simulador/sucesion/pasivos_deuda/otros/') return 'sucesiones_pasivos_otros';
        if (simPath === '/simulador/sucesion/pasivos_gastos' || simPath === '/simulador/sucesion/pasivos_gastos/') return 'sucesiones_pasivos_gastos';
        if (simPath === '/simulador/sucesion/desgravamenes' || simPath === '/simulador/sucesion/desgravamenes/') return 'sucesiones_desgravamenes';
        if (simPath === '/simulador/sucesion/exenciones' || simPath === '/simulador/sucesion/exenciones/') return 'sucesiones_exenciones';
        if (simPath === '/simulador/sucesion/exoneraciones' || simPath === '/simulador/sucesion/exoneraciones/') return 'sucesiones_exoneraciones';
        if (simPath === '/simulador/sucesion/bienes_litigiosos' || simPath === '/simulador/sucesion/bienes_litigiosos/') return 'sucesiones_bienes_litigiosos';
        if (simPath === '/simulador/sucesion/declaracion_anverso' || simPath === '/simulador/sucesion/declaracion_anverso/') return 'sucesiones_declaracion_anverso';
        if (simPath === '/simulador/sucesion/declaracion_reverso' || simPath === '/simulador/sucesion/declaracion_reverso/') return 'sucesiones_declaracion_reverso';
        if (simPath === '/simulador/sucesion/resumen_declaracion' || simPath === '/simulador/sucesion/resumen_declaracion/') return 'sucesiones_resumen_declaracion';
        if (simPath === '/simulador/sucesion/resumen_calculo_manual' || simPath === '/simulador/sucesion/resumen_calculo_manual/') return 'sucesiones_calculo_manual';
        if (simPath.indexOf('/simulador/sucesion/') !== -1) return 'sucesion_spa';
        return null;
    }

    var currentPageKey = detectPageKey();

    /**
     * localStorage helpers — Persistencia de "ya visto" por tour+intento.
     * Clave: tour_seen_{pageKey}_{intentoId}
     * Esto asegura que al cambiar de intento, los tours se reinician.
     */
    function storageKey(pageKey) {
        return 'tour_seen_' + pageKey + '_' + state.intentoId;
    }

    function hasSeenTour(pageKey) {
        try {
            return localStorage.getItem(storageKey(pageKey)) === '1';
        } catch (e) {
            return false;
        }
    }

    function markTourSeen(pageKey) {
        try {
            localStorage.setItem(storageKey(pageKey), '1');
        } catch (e) { /* ignore */ }
    }


    // ╔═══════════════════════════════════════════════════════════════════╗
    // ║  4. TOURS — Definiciones de tours principales (1 por página)     ║
    // ║                                                                  ║
    // ║  Cada tour es una función(state) => Array<step>.                 ║
    // ║  Se auto-ejecutan al cargar la página (si no se han visto).      ║
    // ║                                                                  ║
    // ║  Formato de step:                                                ║
    // ║  { element: '#selector',  // omitir = popover centrado           ║
    // ║    popover: { title, description, side, align } }                ║
    // ╚═══════════════════════════════════════════════════════════════════╝

    var TOURS = {};

    // ─────────────────────────────────────────────────────────────────────
    // TOUR: Portal SENIAT Viejo (/simulador)
    //
    // Steps adaptativos según progreso del estudiante:
    //   - Sin RIF    → señala Sistemas en Línea + Servicios de Declaración
    //   - Con RIF    → señala Servicios de Declaración (registrarse)
    //   - Registrado → señala Servicios de Declaración (iniciar sesión)
    //   - Completo   → popover de felicitación
    //
    // Selectores inyectados en seniat_index_old.php:
    //   #tourSistemasEnLinea      — <td> del botón de menú
    //   #tourServiciosDeclaracion — <td> del card de Servicios
    // ─────────────────────────────────────────────────────────────────────
    TOURS.seniat_index = function (s) {
        var steps = [];

        // Bienvenida (centrado, sin elemento)
        steps.push({
            popover: {
                title: '🎓 Bienvenido al Simulador SENIAT',
                description:
                    'Esta es una <strong>réplica educativa</strong> del portal oficial del SENIAT. ' +
                    '<strong>No es la página real.</strong><br><br>' +
                    'Solo están habilitadas las opciones necesarias para completar el proceso de ' +
                    '<strong>Inscripción de RIF Sucesoral</strong> y la <strong>Declaración Sucesoral</strong>. ' +
                    'Los demás enlaces no tienen funcionalidad.',
                side: 'over',
                align: 'center'
            }
        });

        // Botón "Ver Guía" en el sidebar
        var btnGuia = document.getElementById('btnGuidedTour');
        if (btnGuia) {
            steps.push({
                element: '#btnGuidedTour',
                popover: {
                    title: '📖 Botón de Guía',
                    description:
                        'Si en cualquier momento necesitas <strong>volver a ver esta guía</strong>, ' +
                        'presiona este botón.',
                    side: 'right',
                    align: 'center'
                }
            });
        }

        // Steps adaptativos según progreso
        if (!s.tieneRif) {
            // ── Sin RIF: primer paso es inscribirlo ──
            var sistemasBtn = document.getElementById('tourSistemasEnLinea');
            if (sistemasBtn) {
                steps.push({
                    element: '#tourSistemasEnLinea',
                    popover: {
                        title: '1️⃣ Inscripción de RIF Sucesoral',
                        description:
                            'Tu primer paso es inscribir el <strong>RIF Sucesoral</strong> del causante.<br><br>' +
                            'Pasa el cursor sobre <strong>"Sistemas en Línea"</strong> y selecciona ' +
                            '<strong>"Inscripción de RIF"</strong>.',
                        side: 'left',
                        align: 'center'
                    }
                });
            }

            // Anticipo: Servicios de Declaración como paso futuro
            var serviciosCard = document.getElementById('tourServiciosDeclaracion');
            if (serviciosCard) {
                steps.push({
                    element: '#tourServiciosDeclaracion',
                    popover: {
                        title: '2️⃣ Servicios de Declaración (después)',
                        description:
                            'Después de obtener tu RIF Sucesoral, vendrás aquí para ' +
                            '<strong>registrarte como contribuyente</strong>, iniciar sesión y ' +
                            'realizar la <strong>Declaración Sucesoral</strong>.',
                        side: 'left',
                        align: 'center'
                    }
                });
            }

        } else if (!s.estaRegistrado) {
            // ── Con RIF, sin registro ──
            var serviciosCard2 = document.getElementById('tourServiciosDeclaracion');
            if (serviciosCard2) {
                steps.push({
                    element: '#tourServiciosDeclaracion',
                    popover: {
                        title: '✅ RIF inscrito — Siguiente: Registrarse',
                        description:
                            'Ya inscribiste tu RIF Sucesoral. Ahora haz clic aquí para ir a ' +
                            '<strong>Servicios de Declaración</strong>, donde debes ' +
                            '<strong>registrarte como contribuyente</strong> con el RIF obtenido.',
                        side: 'left',
                        align: 'center'
                    }
                });
            }

        } else if (!s.estaLoggeado) {
            // ── Registrado, sin login ──
            var serviciosCard3 = document.getElementById('tourServiciosDeclaracion');
            if (serviciosCard3) {
                steps.push({
                    element: '#tourServiciosDeclaracion',
                    popover: {
                        title: '✅ Registrado — Siguiente: Iniciar Sesión',
                        description:
                            'Ya estás registrado como contribuyente. Haz clic aquí para ' +
                            '<strong>iniciar sesión</strong> con el usuario y clave que creaste.',
                        side: 'left',
                        align: 'center'
                    }
                });
            }

        } else {
            // ── Todo completado ──
            steps.push({
                popover: {
                    title: '✅ Portal completado',
                    description:
                        'Ya completaste todos los pasos del portal SENIAT. ' +
                        'Continúa con la declaración desde el panel de <strong>Servicios de Declaración</strong>.',
                    side: 'over',
                    align: 'center'
                }
            });
        }

        return steps;
    };

    // ─────────────────────────────────────────────────────────────────────
    // TOUR: Portal SENIAT Nuevo (/simulador/portal)
    //
    // Misma lógica adaptativa que seniat_index pero con selectores del
    // layout nuevo (navbar horizontal con mega-dropdowns).
    //
    // Selectores inyectados en seniat_index_new.php:
    //   #tourSistemasEnLinea — <li> del mega-dropdown "Sistemas en Línea"
    //   #tourIngresarBtn     — <div> del dropdown "Ingresar"
    //
    // No incluye estado "todo completo" (estaLoggeado) porque el
    // estudiante no vuelve al portal después de iniciar sesión.
    // ─────────────────────────────────────────────────────────────────────
    TOURS.seniat_portal_nuevo = function (s) {
        var steps = [];

        // Bienvenida (centrado, sin elemento)
        steps.push({
            popover: {
                title: '🎓 Bienvenido al Portal SENIAT',
                description:
                    'Esta es una <strong>réplica educativa</strong> del portal oficial del SENIAT. ' +
                    '<strong>No es la página real.</strong><br><br>' +
                    'Solo están habilitadas las opciones necesarias para completar el proceso de ' +
                    '<strong>Inscripción de RIF Sucesoral</strong> y la <strong>Declaración Sucesoral</strong>. ' +
                    'Los demás enlaces no tienen funcionalidad.',
                side: 'over',
                align: 'center'
            }
        });

        // Botón "Ver Guía" en el sidebar
        var btnGuia = document.getElementById('btnGuidedTour');
        if (btnGuia) {
            steps.push({
                element: '#btnGuidedTour',
                popover: {
                    title: '📖 Botón de Guía',
                    description:
                        'Si en cualquier momento necesitas <strong>volver a ver esta guía</strong>, ' +
                        'presiona este botón.',
                    side: 'right',
                    align: 'center'
                }
            });
        }

        // Steps adaptativos según progreso
        if (!s.tieneRif) {
            // ── Sin RIF: primer paso es inscribirlo ──
            var sistemasBtn = document.getElementById('tourSistemasEnLinea');
            if (sistemasBtn) {
                steps.push({
                    element: '#tourSistemasEnLinea',
                    popover: {
                        title: '1️⃣ Inscripción de RIF Sucesoral',
                        description:
                            'Tu primer paso es inscribir el <strong>RIF Sucesoral</strong> del causante.<br><br>' +
                            'Haz clic en <strong>"Sistemas en Línea"</strong> y selecciona ' +
                            '<strong>"Inscripción de RIF"</strong>.',
                        side: 'bottom',
                        align: 'center'
                    }
                });
            }

            // Anticipo: Servicios de Declaración como paso futuro
            var ingresarBtn = document.getElementById('tourIngresarBtn');
            if (ingresarBtn) {
                steps.push({
                    element: '#tourIngresarBtn',
                    popover: {
                        title: '2️⃣ Servicios de Declaración (después)',
                        description:
                            'Después de obtener tu RIF Sucesoral, vendrás aquí para ' +
                            '<strong>registrarte como contribuyente</strong>, iniciar sesión y ' +
                            'realizar la <strong>Declaración Sucesoral</strong>.<br><br>' +
                            'Haz clic en <strong>"Ingresar"</strong> y selecciona <strong>"Servicios de Declaración"</strong>.',
                        side: 'bottom',
                        align: 'end'
                    }
                });
            }

        } else if (!s.estaRegistrado) {
            // ── Con RIF, sin registro ──
            var ingresarBtn2 = document.getElementById('tourIngresarBtn');
            if (ingresarBtn2) {
                steps.push({
                    element: '#tourIngresarBtn',
                    popover: {
                        title: '✅ RIF inscrito — Siguiente: Registrarse',
                        description:
                            'Ya inscribiste tu RIF Sucesoral. Ahora haz clic en <strong>"Ingresar"</strong> ' +
                            'y selecciona <strong>"Servicios de Declaración"</strong>, donde debes ' +
                            '<strong>registrarte como contribuyente</strong> con el RIF obtenido.',
                        side: 'bottom',
                        align: 'end'
                    }
                });
            }

        } else {
            // ── Registrado, sin login ──
            var ingresarBtn3 = document.getElementById('tourIngresarBtn');
            if (ingresarBtn3) {
                steps.push({
                    element: '#tourIngresarBtn',
                    popover: {
                        title: '✅ Registrado — Siguiente: Iniciar Sesión',
                        description:
                            'Ya estás registrado como contribuyente. Haz clic en <strong>"Ingresar"</strong> ' +
                            'y selecciona <strong>"Servicios de Declaración"</strong> para ' +
                            '<strong>iniciar sesión</strong> con el usuario y clave que creaste.',
                        side: 'bottom',
                        align: 'end'
                    }
                });
            }
        }

        return steps;
    };

    // ─────────────────────────────────────────────────────────────────────
    // TOUR: Inscripción de RIF (/simulador/inscripcion-rif)
    //
    // Tour principal: Contexto del formulario + señalar select de tipo.
    // Los sub-tours contextuales se disparan al seleccionar tipo (ver SUBTOURS).
    // ─────────────────────────────────────────────────────────────────────
    TOURS.inscripcion_rif = function () {
        var steps = [];
        var select = document.getElementById('personalidad');
        var selectedText = '';
        if (select && select.selectedIndex > 0) {
            selectedText = select.options[select.selectedIndex].text.toUpperCase();
        }
        var isSucesion = selectedText.indexOf('SUCESI') !== -1;
        var isSinCedula = selectedText.indexOf('SIN') !== -1;

        if (!isSucesion) {
            // Sin selección → tour introductorio
            steps.push({
                popover: {
                    title: '📋 Formulario de Inscripción de RIF',
                    description:
                        'Aquí inscribirás el <strong>RIF Sucesoral</strong> del causante.<br><br>' +
                        'Solo están habilitadas las opciones de <strong>Sucesión</strong> ' +
                        '(con o sin cédula). Las demás opciones mostrarán un aviso informativo.',
                    side: 'over',
                    align: 'center'
                }
            });
            if (select) {
                steps.push({
                    element: '#personalidad',
                    popover: {
                        title: '1️⃣ Tipo de Persona',
                        description:
                            'Selecciona <strong>"SUCESIÓN CON CÉDULA"</strong> o ' +
                            '<strong>"SUCESIÓN SIN CÉDULA"</strong> según los datos de tu caso.<br><br>' +
                            '📄 Los datos que necesitas están en el <strong>PDF del caso</strong> ' +
                            'que puedes descargar desde <strong>Mis Asignaciones</strong>.',
                        side: 'bottom',
                        align: 'center'
                    }
                });
            }
        } else {
            // Sucesión seleccionada → steps del sub-tour correspondiente
            var subFn = isSinCedula ? SUBTOURS.sucesion_sin_cedula : SUBTOURS.sucesion_con_cedula;
            steps = subFn();
        }

        return steps;
    };


    // ─────────────────────────────────────────────────────────────────────
    // TOUR: Datos Básicos del Causante (/simulador/inscripcion-rif/datos-basicos)
    //
    // Primer paso del wizard de inscripción de RIF.
    // Campos requeridos: apellidos, nombres, fecha fallecimiento,
    // sexo, estado civil, nacionalidad, fecha cierre, correo.
    // Algunos vienen precargados (cédula, fecha, nacionalidad, cierre fiscal).
    // ─────────────────────────────────────────────────────────────────────
    TOURS.datos_basicos_rif = function () {
        var steps = [];

        // Detectar si ya hay datos básicos guardados
        var borrador = window.simBorrador || {};
        var db = borrador.datos_basicos || {};
        var yaTieneDatos = !!db.apellidos;

        if (yaTieneDatos) {
            // ── Tour: datos ya guardados → guiar hacia Direcciones ──
            steps.push({
                popover: {
                    title: '✅ Datos Básicos Guardados',
                    description:
                        'Ya tienes los <strong>datos del causante guardados</strong>.<br><br>' +
                        'Si necesitas corregir algo, puedes <strong>modificar los campos</strong> ' +
                        'y hacer clic en <strong>Guardar</strong> nuevamente.',
                    side: 'over',
                    align: 'center'
                }
            });

            // Señalar link de Direcciones en el sidebar
            var linkDir = document.getElementById('tourLinkDirecciones');
            if (linkDir) {
                steps.push({
                    element: '#tourLinkDirecciones',
                    popover: {
                        title: '➡️ Siguiente Paso: Direcciones',
                        description:
                            'Continúa con el siguiente paso haciendo clic en ' +
                            '<strong>Direcciones</strong> en el menú lateral.',
                        side: 'right',
                        align: 'start'
                    }
                });
            }

            return steps;
        }

        // ── Tour: primera vez, sin datos → guía campo por campo ──

        // Contexto general
        steps.push({
            popover: {
                title: '📋 Datos del Causante',
                description:
                    'Completa los <strong>datos personales del causante</strong>.<br><br>' +
                    'Algunos campos ya están precargados del paso anterior. ' +
                    'Los datos que necesitas llenar están en el <strong>PDF del caso</strong> ' +
                    '(descargable desde <strong>Mis Asignaciones</strong>).',
                side: 'over',
                align: 'center'
            }
        });

        // Apellidos
        steps.push({
            element: '#apellido',
            popover: {
                title: '✏️ Apellidos',
                description:
                    'Ingresa los <strong>apellidos del causante</strong> tal como aparecen en el PDF del caso.',
                side: 'bottom',
                align: 'center'
            }
        });

        // Nombres
        steps.push({
            element: '#nombre',
            popover: {
                title: '✏️ Nombres',
                description:
                    'Ingresa los <strong>nombres del causante</strong> según el PDF del caso.',
                side: 'bottom',
                align: 'center'
            }
        });

        // Fecha de Fallecimiento (precargada)
        steps.push({
            element: '#fechaNacimiento',
            popover: {
                title: '📅 Fecha de Fallecimiento',
                description:
                    'Esta fecha fue <strong>precargada</strong> del formulario anterior.<br><br>' +
                    'Verifica que sea correcta antes de continuar, es posible modificarla de ser necesario.',
                side: 'bottom',
                align: 'center'
            }
        });

        // Sexo (radios — apuntar al parent <td>)
        var radioSexo = document.querySelector('input[name="radiosexo"]');
        if (radioSexo) {
            var sexoContainer = radioSexo.closest('td');
            if (sexoContainer) sexoContainer.id = 'tourSexoContainer';
            steps.push({
                element: '#tourSexoContainer',
                popover: {
                    title: '👤 Sexo',
                    description:
                        'Selecciona <strong>FEMENINO</strong> o <strong>MASCULINO</strong> ' +
                        'según los datos del causante en el PDF.',
                    side: 'bottom',
                    align: 'center'
                }
            });
        }

        // Estado Civil
        var estadoCivil = document.getElementById('estadoCivil.codigo');
        if (estadoCivil) {
            steps.push({
                element: '#estadoCivil\\.codigo',
                popover: {
                    title: '💍 Estado Civil',
                    description:
                        'Selecciona el <strong>estado civil</strong> del causante según el PDF.',
                    side: 'bottom',
                    align: 'center'
                }
            });
        }

        // Fecha Cierre Fiscal (precargada)
        steps.push({
            element: '#fechaCierreFiscal',
            popover: {
                title: '📅 Fecha Cierre Fiscal',
                description:
                    'Este campo se genera <strong>automáticamente</strong> (31/12 del año actual).<br><br>' +
                    'No es necesario modificarlo salvo que se indique lo contrario.',
                side: 'bottom',
                align: 'center'
            }
        });

        // Correo
        steps.push({
            element: '#correo',
            popover: {
                title: '📧 Correo Electrónico',
                description:
                    'Ingresa un <strong>correo electrónico válido</strong>. ' +
                    'Es un campo obligatorio para completar la inscripción.',
                side: 'bottom',
                align: 'center'
            }
        });

        // Guardar
        steps.push({
            element: '#guardar',
            popover: {
                title: '💾 Guardar Datos',
                description:
                    'Una vez completados todos los campos, haz clic en <strong>Guardar</strong>.<br><br>' +
                    'Al guardar, se habilitarán los demás pasos en el <strong>menú lateral</strong>: ' +
                    'Direcciones, Relaciones y Validar Inscripción.',
                side: 'top',
                align: 'center'
            }
        });

        return steps;
    };


    // ─────────────────────────────────────────────────────────────────────
    // TOUR: Direcciones (/simulador/inscripcion-rif/direcciones)
    //
    // Segundo paso del wizard. Formulario complejo con radios, cascadas
    // y tabla de direcciones. Tour por secciones, no campo a campo.
    //
    // Campos obligatorios: tipoDireccion, vialidad (radio+text),
    //   edificacion (radio+text), sector (radio+text),
    //   estado→municipio→parroquia→ciudad (cascada), zonaPostal,
    //   al menos 1 teléfono (fijo o celular).
    // Condicionales: piso + tipoLocal+text (solo para edificio/c.comercial)
    // Opcionales: fax, referencia
    // ─────────────────────────────────────────────────────────────────────
    TOURS.direcciones_rif = function () {
        var steps = [];

        // Detectar estado de las direcciones guardadas
        var borrador = window.simBorrador || {};
        var dirs = borrador.direcciones || [];
        var tieneDomicilioFiscal = dirs.some(function (d) { return d.tipoDireccion === '06'; });

        // Helper: asignar ID al link de Relaciones en el sidebar
        function prepararLinkRelaciones() {
            var linkRel = document.querySelector('a.menuitem[href*="relaciones"]');
            if (linkRel) linkRel.id = 'tourLinkRelaciones';
            return linkRel;
        }

        if (dirs.length > 0 && tieneDomicilioFiscal) {
            // ── Estado 3: tiene direcciones + domicilio fiscal → puede avanzar ──
            steps.push({
                popover: {
                    title: '✅ Direcciones Completas',
                    description:
                        'Ya tienes <strong>' + dirs.length + ' dirección(es)</strong> cargada(s), ' +
                        'incluyendo el <strong>Domicilio Fiscal</strong>.<br><br>' +
                        'Puedes seguir agregando más direcciones si lo necesitas, ' +
                        'o avanzar al siguiente paso.',
                    side: 'over',
                    align: 'center'
                }
            });

            if (prepararLinkRelaciones()) {
                steps.push({
                    element: '#tourLinkRelaciones',
                    popover: {
                        title: '➡️ Siguiente Paso: Relaciones',
                        description:
                            'Continúa con <strong>Relaciones</strong> para ingresar el ' +
                            '<strong>representante de la sucesión</strong> y los <strong>herederos</strong>.',
                        side: 'right',
                        align: 'start'
                    }
                });
            }

            return steps;
        }

        if (dirs.length > 0 && !tieneDomicilioFiscal) {
            // ── Estado 2: tiene direcciones PERO falta domicilio fiscal ──
            steps.push({
                popover: {
                    title: '⚠️ Falta el Domicilio Fiscal',
                    description:
                        'Ya tienes <strong>' + dirs.length + ' dirección(es)</strong> cargada(s), ' +
                        'pero ninguna es un <strong>DOMICILIO FISCAL</strong>.<br><br>' +
                        'Debes agregar al menos una dirección con tipo ' +
                        '<strong>DOMICILIO FISCAL</strong> antes de continuar a Relaciones.',
                    side: 'over',
                    align: 'center'
                }
            });

            var selTipoDirCtx = document.querySelector('[name="tipoDireccion.codigo"]');
            if (selTipoDirCtx) {
                steps.push({
                    element: '[name="tipoDireccion.codigo"]',
                    popover: {
                        title: '📋 Selecciona DOMICILIO FISCAL',
                        description:
                            'Elige <strong>DOMICILIO FISCAL</strong> en este campo y completa ' +
                            'los datos de la dirección.',
                        side: 'bottom',
                        align: 'center'
                    }
                });
            }

            return steps;
        }

        // ── Estado 1: sin direcciones → guía completa por secciones ──

        // Step 0: Contexto
        steps.push({
            popover: {
                title: '📍 Direcciones del Causante',
                description:
                    'Aquí registras las <strong>direcciones del causante</strong>.<br><br>' +
                    'Debes agregar al menos un <strong>DOMICILIO FISCAL</strong>. ' +
                    'Puedes agregar varias direcciones (una a la vez).<br><br>' +
                    'Los datos están en el <strong>PDF del caso</strong>.',
                side: 'over',
                align: 'center'
            }
        });

        // Step 1: Tipo de Dirección
        var selTipoDir = document.querySelector('[name="tipoDireccion.codigo"]');
        if (selTipoDir) {
            steps.push({
                element: '[name="tipoDireccion.codigo"]',
                popover: {
                    title: '📋 Tipo de Dirección',
                    description:
                        'Selecciona el tipo de dirección.<br><br>' +
                        'Es obligatorio que al menos una dirección sea <strong>DOMICILIO FISCAL</strong>.',
                    side: 'bottom',
                    align: 'center'
                }
            });
        }

        // Step 2: Vialidad (radios + text)
        var radioVialidad = document.querySelector('input[name="radiovialidad"]');
        if (radioVialidad) {
            var vialidadTd = radioVialidad.closest('td');
            if (vialidadTd) vialidadTd.id = 'tourVialidadSection';
            steps.push({
                element: '#tourVialidadSection',
                popover: {
                    title: '🛤️ Tipo de Vialidad',
                    description:
                        'Selecciona el tipo de vialidad (<strong>calle, avenida, carrera</strong>, etc.) ' +
                        'y escribe el nombre en el campo de texto de abajo.<br><br>' +
                        '<em>Ambos son obligatorios.</em>',
                    side: 'bottom',
                    align: 'center'
                }
            });
        }

        // Step 3: Edificación (radios + text)
        var radioEdif = document.querySelector('input[name="radioedificacion"]');
        if (radioEdif) {
            var edifTd = radioEdif.closest('td');
            if (edifTd) edifTd.id = 'tourEdificacionSection';
            steps.push({
                element: '#tourEdificacionSection',
                popover: {
                    title: '🏢 Tipo de Edificación',
                    description:
                        'Selecciona el tipo (<strong>edificio, quinta, casa</strong>, etc.) ' +
                        'y escribe la descripción abajo.<br><br>' +
                        'Si eliges <em>Edificio</em> o <em>Centro Comercial</em>, se activarán los campos de ' +
                        '<strong>Piso/Nivel</strong> y <strong>Tipo de Local</strong>.',
                    side: 'bottom',
                    align: 'center'
                }
            });
        }

        // Step 4: Sector (radios + text)
        var radioSector = document.querySelector('input[name="radiosector"]');
        if (radioSector) {
            var sectorTd = radioSector.closest('td');
            if (sectorTd) sectorTd.id = 'tourSectorSection';
            steps.push({
                element: '#tourSectorSection',
                popover: {
                    title: '🏘️ Tipo de Sector',
                    description:
                        'Selecciona el tipo de sector (<strong>urbanización, zona, barrio</strong>, etc.) ' +
                        'y escribe el nombre abajo.<br><br>' +
                        '<em>Ambos son obligatorios.</em>',
                    side: 'bottom',
                    align: 'center'
                }
            });
        }

        // Step 5: Ubicación geográfica (cascada)
        var selEstado = document.querySelector('[name="estado.codigo"]');
        if (selEstado) {
            steps.push({
                element: '[name="estado.codigo"]',
                popover: {
                    title: '📍 Ubicación Geográfica',
                    description:
                        'Selecciona el <strong>Estado</strong>. Al hacerlo se cargarán automáticamente: ' +
                        '<strong>Municipio, Parroquia, Ciudad</strong> y <strong>Zona Postal</strong>.<br><br>' +
                        '<em>Todos son obligatorios.</em>',
                    side: 'bottom',
                    align: 'center'
                }
            });
        }

        // Step 6: Teléfonos
        var inputTel = document.querySelector('[name="telefono"]');
        if (inputTel) {
            var telTd = inputTel.closest('td');
            if (telTd) telTd.id = 'tourTelefonoSection';
            steps.push({
                element: '#tourTelefonoSection',
                popover: {
                    title: '📞 Teléfonos de Contacto',
                    description:
                        'Ingresa al menos <strong>un teléfono</strong> (fijo o celular).<br>' +
                        'Formato: <strong>0212-1234567</strong><br><br>' +
                        'El <em>Fax</em> es opcional.',
                    side: 'bottom',
                    align: 'center'
                }
            });
        }

        // Step 7: Punto de Referencia
        steps.push({
            element: '#referencia',
            popover: {
                title: '📌 Punto de Referencia',
                description:
                    'Ingresa un punto de referencia para la dirección.<br><br>' +
                    '<em>Este campo es opcional.</em>',
                side: 'bottom',
                align: 'center'
            }
        });

        // Step 8: Guardar
        steps.push({
            element: '#guardar',
            popover: {
                title: '💾 Guardar Dirección',
                description:
                    'Al hacer clic en <strong>Guardar</strong>, la dirección se agregará a la tabla de arriba.<br><br>' +
                    'Puedes agregar <strong>más de una dirección</strong> repitiendo el proceso. ' +
                    'Recuerda que al menos el <strong>Domicilio Fiscal</strong> es obligatorio.',
                side: 'top',
                align: 'center'
            }
        });

        return steps;
    };


    // ─────────────────────────────────────────────────────────────────────
    // TOUR: Relaciones (/simulador/inscripcion-rif/relaciones)
    //
    // Tercer paso del wizard. Registra representante y herederos.
    // Campos: apellidos, nombres, tipo doc (Cédula/RIF), cédula,
    //   parentesco (REPRESENTANTE=50, HEREDERO=51), pasaporte.
    // Reglas: 1 representante (debe tener RIF), N herederos,
    //   misma persona puede ser ambos, búsqueda en DB al guardar.
    // ─────────────────────────────────────────────────────────────────────
    TOURS.relaciones_rif = function () {
        var steps = [];

        // Detectar estado de las relaciones
        var borrador = window.simBorrador || {};
        var rels = borrador.relaciones || [];
        var tieneRepresentante = rels.some(function (r) { return r.parentesco === '50'; });
        var tieneHeredero = rels.some(function (r) { return r.parentesco === '51'; });

        // Helper: asignar ID al link de Validar Inscripción
        function prepararLinkValidar() {
            var link = document.querySelector('a.menuitem[href*="validar-inscripcion"]');
            if (link) link.id = 'tourLinkValidar';
            return link;
        }

        if (rels.length > 0 && tieneRepresentante && tieneHeredero) {
            // ── Estado 3: tiene representante + heredero(s) → completo ──
            steps.push({
                popover: {
                    title: '✅ Relaciones Completas',
                    description:
                        'Ya tienes <strong>' + rels.length + ' relación(es)</strong> registrada(s), ' +
                        'incluyendo el <strong>Representante de la Sucesión</strong> y al menos un <strong>Heredero</strong>.<br><br>' +
                        'Puedes seguir agregando más herederos si lo necesitas, o avanzar al siguiente paso.',
                    side: 'over',
                    align: 'center'
                }
            });

            if (prepararLinkValidar()) {
                steps.push({
                    element: '#tourLinkValidar',
                    popover: {
                        title: '➡️ Siguiente: Validar Inscripción',
                        description:
                            'Continúa con <strong>Validar Inscripción</strong> para revisar ' +
                            'y confirmar todos los datos ingresados.',
                        side: 'right',
                        align: 'start'
                    }
                });
            }

            return steps;
        }

        if (rels.length > 0 && (!tieneRepresentante || !tieneHeredero)) {
            // ── Estado 2: tiene relaciones pero falta representante o heredero ──
            var faltante = '';
            if (!tieneRepresentante && !tieneHeredero) {
                faltante = 'un <strong>REPRESENTANTE DE LA SUCESIÓN</strong> y al menos un <strong>HEREDERO</strong>';
            } else if (!tieneRepresentante) {
                faltante = 'un <strong>REPRESENTANTE DE LA SUCESIÓN</strong>';
            } else {
                faltante = 'al menos un <strong>HEREDERO</strong>';
            }

            steps.push({
                popover: {
                    title: '⚠️ Relaciones Incompletas',
                    description:
                        'Ya tienes <strong>' + rels.length + ' relación(es)</strong>, ' +
                        'pero aún falta agregar ' + faltante + '.<br><br>' +
                        'Recuerda que una misma persona puede ser <strong>Representante y Heredero</strong> a la vez.',
                    side: 'over',
                    align: 'center'
                }
            });

            var selParentescoCtx = document.querySelector('[name="parentesco.codigo"]');
            if (selParentescoCtx) {
                steps.push({
                    element: '[name="parentesco.codigo"]',
                    popover: {
                        title: '👪 Selecciona el Parentesco',
                        description:
                            'Elige <strong>' + (!tieneRepresentante ? 'REPRESENTANTE DE LA SUCESIÓN' : 'HEREDERO') + '</strong> ' +
                            'y completa los datos de la persona.',
                        side: 'bottom',
                        align: 'center'
                    }
                });
            }

            return steps;
        }

        // ── Estado 1: sin relaciones → guía completa ──

        // Step 0: Contexto
        steps.push({
            popover: {
                title: '👥 Relaciones del Causante',
                description:
                    'Aquí registras las <strong>relaciones del causante</strong>.<br><br>' +
                    'Necesitas agregar al menos:<br>' +
                    '• <strong>1 Representante de la Sucesión</strong> (debe tener RIF)<br>' +
                    '• <strong>1 o más Herederos</strong><br><br>' +
                    'Una misma persona puede ser <strong>Representante y Heredero</strong> a la vez.',
                side: 'over',
                align: 'center'
            }
        });

        // Step 1: Apellidos y Nombres
        steps.push({
            element: '#apellido',
            popover: {
                title: '✏️ Apellidos y Nombres',
                description:
                    'Ingresa los <strong>apellidos y nombres</strong> de la persona.<br><br>' +
                    'Al guardar, el sistema buscará en la <strong>base de datos</strong> ' +
                    'y podrá autocompletar los datos reales.',
                side: 'bottom',
                align: 'center'
            }
        });

        // Step 2: Tipo de Documento (radios)
        var radioTipoDoc = document.querySelector('input[name="tipodocumento"]');
        if (radioTipoDoc) {
            var tipoDocTd = radioTipoDoc.closest('td');
            if (tipoDocTd) tipoDocTd.id = 'tourTipoDocSection';
            steps.push({
                element: '#tourTipoDocSection',
                popover: {
                    title: '📄 Tipo de Documento',
                    description:
                        '<strong>Cédula:</strong> V o E seguido de hasta 8 dígitos (ej: V12345678)<br>' +
                        '<strong>RIF:</strong> J, V, G, E, P o C seguido de 9 dígitos (ej: V123456789)<br><br>' +
                        '⚠️ El <strong>Representante</strong> debe usar <strong>RIF</strong>.',
                    side: 'bottom',
                    align: 'center'
                }
            });
        }

        // Step 3: Cédula/RIF
        steps.push({
            element: '#cedula',
            popover: {
                title: '🔢 Cédula o RIF',
                description:
                    'Ingresa el número de documento.<br><br>' +
                    'Al guardar, el sistema <strong>buscará en la base de datos</strong> ' +
                    'para verificar y completar los datos de la persona.',
                side: 'bottom',
                align: 'center'
            }
        });

        // Step 4: Parentesco
        steps.push({
            element: '[name="parentesco.codigo"]',
            popover: {
                title: '👪 Parentesco',
                description:
                    '<strong>REPRESENTANTE DE LA SUCESIÓN:</strong> solo puede haber 1. Debe tener RIF.<br>' +
                    '<strong>HEREDERO:</strong> puedes agregar varios.<br><br>' +
                    'Una misma persona puede ser <strong>ambos</strong> (se agrega dos veces con parentesco diferente).',
                side: 'bottom',
                align: 'center'
            }
        });

        // Step 5: Pasaporte
        steps.push({
            element: '#pasaporte',
            popover: {
                title: '🛂 Pasaporte',
                description:
                    'Solo para <strong>extranjeros sin cédula</strong>.<br><br>' +
                    'Si ya ingresaste cédula o RIF, este campo no es necesario.',
                side: 'bottom',
                align: 'center'
            }
        });

        // Step 6: Guardar
        steps.push({
            element: '#guardar',
            popover: {
                title: '💾 Guardar Relación',
                description:
                    'Al guardar, la persona se <strong>busca en la base de datos</strong> ' +
                    'y se agrega a la tabla de arriba.<br><br>' +
                    'Repite el proceso para agregar más herederos.',
                side: 'top',
                align: 'center'
            }
        });

        return steps;
    };


    // ─────────────────────────────────────────────────────────────────────
    // TOUR: Validar Inscripción (/simulador/inscripcion-rif/validar-inscripcion)
    //
    // Último paso del wizard. No auto-start cuando datos completos
    // (el modal de confirmación se abre solo). Solo se activa con "Ver Guía".
    // ─────────────────────────────────────────────────────────────────────
    TOURS.validar_inscripcion_rif = function () {
        var steps = [];
        var borrador = window.simBorrador || {};

        // Verificar completitud (misma lógica que el PHP)
        var dirs = borrador.direcciones || [];
        var rels = borrador.relaciones || [];
        var tieneDomFiscal = dirs.some(function (d) { return d.tipoDireccion === '06'; });
        var tieneRepresentante = rels.some(function (r) { return r.parentesco === '50'; });
        var datosCompletos = tieneDomFiscal && tieneRepresentante;

        if (!datosCompletos) {
            // ── Estado 1: datos incompletos ──
            var faltantes = [];
            if (!tieneDomFiscal) faltantes.push('<strong>Domicilio Fiscal</strong> en Direcciones');
            if (!tieneRepresentante) faltantes.push('<strong>Representante de la Sucesión</strong> en Relaciones');

            steps.push({
                popover: {
                    title: '⚠️ Datos Incompletos',
                    description:
                        'No puedes validar la inscripción aún.<br><br>' +
                        'Falta registrar:<br>• ' + faltantes.join('<br>• ') + '<br><br>' +
                        'Vuelve a las secciones correspondientes en el <strong>menú lateral</strong> para completar los datos.',
                    side: 'over',
                    align: 'center'
                }
            });

            // Señalar el primer link relevante
            if (!tieneDomFiscal) {
                var linkDir = document.querySelector('a.menuitem[href*="direcciones"]');
                if (linkDir) {
                    linkDir.id = 'tourLinkDirVal';
                    steps.push({
                        element: '#tourLinkDirVal',
                        popover: {
                            title: '📍 Ir a Direcciones',
                            description: 'Agrega una dirección con tipo <strong>DOMICILIO FISCAL</strong>.',
                            side: 'right',
                            align: 'start'
                        }
                    });
                }
            } else if (!tieneRepresentante) {
                var linkRel = document.querySelector('a.menuitem[href*="relaciones"]');
                if (linkRel) {
                    linkRel.id = 'tourLinkRelVal';
                    steps.push({
                        element: '#tourLinkRelVal',
                        popover: {
                            title: '👥 Ir a Relaciones',
                            description: 'Agrega el <strong>Representante de la Sucesión</strong>.',
                            side: 'right',
                            align: 'start'
                        }
                    });
                }
            }

            return steps;
        }

        // ── Estado 2: datos completos ──
        steps.push({
            popover: {
                title: '✅ Último Paso: Validación',
                description:
                    'Tus datos están completos.<br><br>' +
                    'Al hacer clic en <strong>Confirmar y validar</strong>, el sistema comparará ' +
                    '<strong>todos</strong> tus datos (causante, direcciones y relaciones) con el caso asignado.<br><br>' +
                    'Si hay discrepancias, se te mostrarán para que puedas corregirlas e intentar nuevamente.',
                side: 'over',
                align: 'center'
            }
        });

        return steps;
    };


    // ─────────────────────────────────────────────────────────────────────
    // TOUR: Servicios de Declaración (/simulador/servicios_declaracion)
    //
    // Steps adaptativos según progreso del estudiante:
    //   - Sin RIF         → le indica que debe inscribir el RIF primero
    //   - Con RIF, sin registro → señala botón "Regístrese"
    //   - Con RIF, registrado   → guía el login completo
    //
    // Selectores inyectados en servicios_declaracion.php:
    //   #tourUsuario, #tourClave, #tourCaptcha,
    //   #btnAceptar, #tourSalir, #tourRegistrarse, #btnOlvido
    // ─────────────────────────────────────────────────────────────────────
    TOURS.servicios_declaracion = function (s) {
        var steps = [];

        if (!s.tieneRif) {
            // ── Estado 1: No tiene RIF Sucesoral ──
            steps.push({
                popover: {
                    title: '📋 Portal de Servicios de Declaración',
                    description:
                        'Este es el portal de <strong>Servicios de Declaración</strong> del SENIAT. ' +
                        'Aquí se accede a los aplicativos tributarios para realizar la ' +
                        '<strong>Declaración Sucesoral</strong>.',
                    side: 'over',
                    align: 'center'
                }
            });
            steps.push({
                popover: {
                    title: '⚠️ Requisito Previo',
                    description:
                        'Para poder acceder, primero debe completar la <strong>Inscripción del RIF Sucesoral</strong> ' +
                        'desde el portal principal.<br><br>' +
                        'Una vez obtenido el RIF, podrá <strong>registrarse como contribuyente</strong> y luego ' +
                        'iniciar sesión aquí.',
                    side: 'over',
                    align: 'center'
                }
            });
            var btnSalir = document.getElementById('tourSalir');
            if (btnSalir) {
                steps.push({
                    element: '#tourSalir',
                    popover: {
                        title: '↩️ Regresar al Portal',
                        description:
                            'Presione <strong>Salir</strong> para regresar al portal principal y completar la inscripción del RIF Sucesoral.',
                        side: 'top',
                        align: 'center'
                    }
                });
            }

        } else if (!s.estaRegistrado) {
            // ── Estado 2: Tiene RIF pero no se ha registrado ──
            steps.push({
                popover: {
                    title: '📋 Portal de Servicios de Declaración',
                    description:
                        'Bienvenido al portal de <strong>Servicios de Declaración</strong> del SENIAT. ' +
                        'Aquí se ingresa con las credenciales del contribuyente para acceder a los ' +
                        'aplicativos tributarios.',
                    side: 'over',
                    align: 'center'
                }
            });
            var btnRegistrarse = document.getElementById('tourRegistrarse');
            if (btnRegistrarse) {
                steps.push({
                    element: '#tourRegistrarse',
                    popover: {
                        title: '📝 Regístrese',
                        description:
                            'Ya tiene su RIF Sucesoral. Ahora debe <strong>registrarse como contribuyente</strong> ' +
                            'para crear sus credenciales de acceso al sistema.<br><br>' +
                            'Haga clic en <strong>"Regístrese"</strong> para continuar.',
                        side: 'top',
                        align: 'center'
                    }
                });
            }
            var btnOlvido = document.getElementById('btnOlvido');
            if (btnOlvido) {
                steps.push({
                    element: '#btnOlvido',
                    popover: {
                        title: '🔑 ¿Olvidó su Información?',
                        description:
                            'Si ya se registró y olvidó sus datos, este botón le mostrará sus credenciales.<br><br>' +
                            '<em>Nota: Esta es una facilidad del simulador educativo. En el SENIAT real, el proceso ' +
                            'de recuperación es diferente.</em>',
                        side: 'top',
                        align: 'center'
                    }
                });
            }

        } else {
            // ── Estado 3: Tiene RIF y ya está registrado ──
            steps.push({
                popover: {
                    title: '🔐 Iniciar Sesión',
                    description:
                        'Ingrese con las <strong>credenciales que creó</strong> durante el registro del contribuyente.',
                    side: 'over',
                    align: 'center'
                }
            });
            var elUsuario = document.getElementById('tourUsuario');
            if (elUsuario) {
                steps.push({
                    element: '#tourUsuario',
                    popover: {
                        title: '👤 Usuario',
                        description: 'Escriba su <strong>nombre de usuario</strong> registrado.',
                        side: 'bottom',
                        align: 'center'
                    }
                });
            }
            var elClave = document.getElementById('tourClave');
            if (elClave) {
                steps.push({
                    element: '#tourClave',
                    popover: {
                        title: '🔒 Clave',
                        description:
                            'Ingrese su <strong>clave</strong> registrada.<br><br>' +
                            'Puede hacer clic en el icono del ojo para ver u ocultar la contraseña.',
                        side: 'bottom',
                        align: 'center'
                    }
                });
            }
            var elCaptcha = document.getElementById('tourCaptcha');
            if (elCaptcha) {
                steps.push({
                    element: '#tourCaptcha',
                    popover: {
                        title: '🖼️ Código de Verificación',
                        description:
                            'Copie el <strong>código mostrado</strong> en la imagen de la izquierda.<br><br>' +
                            'Puede hacer <strong>clic en la imagen</strong> para generar un nuevo código si no se lee bien.',
                        side: 'bottom',
                        align: 'center'
                    }
                });
            }
            var elAceptar = document.getElementById('btnAceptar');
            if (elAceptar) {
                steps.push({
                    element: '#btnAceptar',
                    popover: {
                        title: '✅ Aceptar',
                        description: 'Presione <strong>Aceptar</strong> para ingresar al sistema de declaraciones.',
                        side: 'top',
                        align: 'center'
                    }
                });
            }
            var elOlvido = document.getElementById('btnOlvido');
            if (elOlvido) {
                steps.push({
                    element: '#btnOlvido',
                    popover: {
                        title: '🔑 ¿Olvidó su Información?',
                        description:
                            'Si no recuerda sus datos, este botón le mostrará sus credenciales registradas.',
                        side: 'top',
                        align: 'center'
                    }
                });
            }
        }

        return steps;
    };


    // ─────────────────────────────────────────────────────────────────────
    // TOUR: Acceder Sistemas (/simulador/servicios_declaracion/sistemas)
    //
    // Sin context-awareness: requiere login SENIAT para acceder.
    // Muestra la lista de aplicativos y señala el botón "Ir al Sistema".
    //
    // Selectores inyectados en acceder_sistemas.php:
    //   #tourAplicativosList, #tourIrAlSistema
    // ─────────────────────────────────────────────────────────────────────
    TOURS.acceder_sistemas = function () {
        var steps = [];

        steps.push({
            popover: {
                title: '📋 Aplicativos SENIAT',
                description:
                    'Esta es la pantalla de selección de <strong>aplicativos</strong> del SENIAT. ' +
                    'Aquí se listan los distintos sistemas tributarios disponibles.',
                side: 'over',
                align: 'center'
            }
        });

        var elList = document.getElementById('tourAplicativosList');
        if (elList) {
            steps.push({
                element: '#tourAplicativosList',
                popover: {
                    title: '📑 Sistemas Disponibles',
                    description:
                        'Se muestran los aplicativos tributarios. Para el proceso de <strong>Declaración Sucesoral</strong>, ' +
                        'el sistema correspondiente es <strong>SUCESIONES</strong>.<br><br>' +
                        'Los demás aplicativos no tienen funcionalidad en el simulador.',
                    side: 'bottom',
                    align: 'center'
                }
            });
        }

        var elBtn = document.getElementById('tourIrAlSistema');
        if (elBtn) {
            steps.push({
                element: '#tourIrAlSistema',
                popover: {
                    title: '➡️ Ir al Sistema',
                    description:
                        'Presione <strong>Ir al Sistema</strong> para acceder al módulo de Sucesiones y comenzar su declaración.',
                    side: 'top',
                    align: 'center'
                }
            });
        }

        return steps;
    };


    // ─────────────────────────────────────────────────────────────────────
    // TOUR: Dashboard Sistemas (/simulador/servicios_declaracion/dashboard)
    //
    // Sin context-awareness: requiere login SENIAT para acceder.
    // Guía al usuario al menú lateral → Declaraciones → Sucesiones.
    //
    // Selectores inyectados en sim_dashboard_layout.php:
    //   #tourMenuDeclaraciones, #tourSucesionesLink
    // ─────────────────────────────────────────────────────────────────────
    TOURS.sistemas_dashboard = function () {
        var steps = [];

        steps.push({
            popover: {
                title: '🏠 Dashboard del Sistema',
                description:
                    'Este es el panel principal del sistema de declaraciones. ' +
                    'Desde el <strong>menú lateral izquierdo</strong> puede acceder a los distintos módulos.',
                side: 'over',
                align: 'center'
            }
        });

        var elMenu = document.getElementById('tourMenuDeclaraciones');
        if (elMenu) {
            steps.push({
                element: '#tourMenuDeclaraciones',
                popover: {
                    title: '📂 Declaraciones',
                    description:
                        'Abra la sección <strong>Declaraciones</strong> para ver los tipos de declaración disponibles.',
                    side: 'right',
                    align: 'center'
                },
                onHighlightStarted: function () {
                    // Auto-expandir el accordion de Declaraciones para que Sucesiones sea visible
                    var panel = document.querySelector('[data-panel="declaraciones"]');
                    var btn = document.querySelector('[data-section="declaraciones"]');
                    if (panel && !panel.classList.contains('show')) {
                        var accordion = document.getElementById('accordionFlushExample');
                        if (accordion) {
                            accordion.querySelectorAll('.accordion-collapse').forEach(function (p) { p.classList.remove('show'); });
                            accordion.querySelectorAll('.accordion-button').forEach(function (b) { b.classList.add('collapsed'); });
                        }
                        panel.classList.add('show');
                        if (btn) btn.classList.remove('collapsed');
                    }
                }
            });
        }

        var elSucesiones = document.getElementById('tourSucesionesLink');
        if (elSucesiones) {
            steps.push({
                element: '#tourSucesionesLink',
                popover: {
                    title: '📜 Sucesiones',
                    description:
                        'Haga clic en <strong>Sucesiones</strong> para acceder al módulo de Declaración Sucesoral ' +
                        'y comenzar a llenar los datos de la herencia.',
                    side: 'right',
                    align: 'center'
                }
            });
        }

        return steps;
    };


    // ─────────────────────────────────────────────────────────────────────
    // TOUR: Sucesión Principal (/simulador/sucesion/principal)
    //
    // Sin context-awareness: requiere login SENIAT + haber pasado
    // por la pantalla de sistemas.
    //
    // Selectores inyectados en sucesion_principal.php:
    //   #tourInfoFiscal, #tourHerederosTable, #tourBotonesAccion
    // ─────────────────────────────────────────────────────────────────────
    TOURS.sucesion_principal = function () {
        var steps = [];

        steps.push({
            popover: {
                title: '📋 Autoliquidación de Sucesiones',
                description:
                    'Esta es la página principal del módulo de <strong>Sucesiones</strong>. ' +
                    'Aquí se muestra un resumen de la información fiscal del caso antes de iniciar la declaración.',
                side: 'over',
                align: 'center'
            }
        });

        var elInfo = document.getElementById('tourInfoFiscal');
        if (elInfo) {
            steps.push({
                element: '#tourInfoFiscal',
                popover: {
                    title: '📊 Información Fiscal',
                    description:
                        'Se muestran los <strong>datos de la sucesión</strong> (RIF, nombre, fechas) y los ' +
                        '<strong>datos del causante</strong> (cédula, representante legal, domicilio fiscal).<br><br>' +
                        'Verifique que la información sea correcta antes de continuar.',
                    side: 'bottom',
                    align: 'center'
                }
            });
        }

        var elHerederos = document.getElementById('tourHerederosTable');
        if (elHerederos) {
            steps.push({
                element: '#tourHerederosTable',
                popover: {
                    title: '👥 Herederos o Legatarios',
                    description:
                        'Aquí se listan los <strong>herederos</strong> registrados en la inscripción del RIF. ' +
                        'Estos datos provienen del proceso de inscripción realizado anteriormente.',
                    side: 'top',
                    align: 'center'
                }
            });
        }

        var elBotones = document.getElementById('tourBotonesAccion');
        if (elBotones) {
            steps.push({
                element: '#tourBotonesAccion',
                popover: {
                    title: '✅ Continuar con la Declaración',
                    description:
                        'Presione <strong>Sí</strong> para aceptar la información y comenzar el proceso de declaración.<br><br>' +
                        'Presione <strong>No</strong> si desea regresar al dashboard.',
                    side: 'top',
                    align: 'center'
                }
            });
        }

        return steps;
    };


    // ─────────────────────────────────────────────────────────────────────
    // TOUR: Sucesión - Tipo Herencia e Inicio del Inventario (/simulador/sucesion/herencia)
    //
    // Context-Aware: Verifica si la tabla de herencias está vacía.
    // Si está vacía (Estado 1): Tour de Onboarding completo por todo el sidebar.
    // Si tiene datos (Estado 2): Tour corto que enfoca la tabla y el siguiente paso.
    // ─────────────────────────────────────────────────────────────────────
    // =========================================================================
    // HELPER: ONBOARDING GLOBAL DEL MENÚ LATERAL (SUCESIONES)
    // =========================================================================
    function getSucesionesOnboardingSteps() {
        var steps = [];

        steps.push({
            element: '#btnSucesionesMenuTour',
            popover: {
                title: '🧭 Guía del Menú',
                description: 'Este recorrido automático le explicará cómo funciona y para qué sirve cada sección del menú de navegación del inventario sucesoral. Siéntase libre de volver a iniciarlo con este botón.',
                side: 'right',
                align: 'center'
            }
        });

        // Paso 2: Explicar el botón Ver Guía
        var btnGuiaLocal = document.getElementById('btnGuidedTour');
        if (btnGuiaLocal) {
            steps.push({
                element: '#btnGuidedTour',
                popover: {
                    title: '💡 Tutoriales Locales',
                    description: 'Recuerde: si en algún momento no sabe cómo llenar la pantalla en la que se encuentra actualmente, presione este botón para recibir un tutorial específico de esa página.',
                    side: 'right',
                    align: 'center'
                }
            });
        }

        var expandNav = function (section) {
            var btn = document.querySelector('button[data-section="' + section + '"]');
            if (!btn) return;
            var accordion = document.getElementById('accordionFlushExample');
            var panel = accordion ? accordion.querySelector('[data-panel="' + section + '"]') : null;
            if (panel && !panel.classList.contains('show')) {
                if (accordion) {
                    accordion.querySelectorAll('.accordion-collapse').forEach(function (p) { p.classList.remove('show'); });
                    accordion.querySelectorAll('.accordion-button').forEach(function (b) { b.classList.add('collapsed'); });
                }
                panel.classList.add('show');
                btn.classList.remove('collapsed');
            }
        };

        var addSidebarStep = function (section, title, description) {
            var selector = 'button[data-section="' + section + '"]';
            if (document.querySelector(selector)) {
                steps.push({
                    element: selector,
                    popover: {
                        title: title,
                        description: description,
                        side: 'right',
                        align: 'center'
                    },
                    onHighlightStarted: function () {
                        expandNav(section);
                    }
                });
            }
        };

        addSidebarStep('herencia', '🔖 Tipo Herencia', 'Comenzará definiendo las bases legales del caso: si hubo testamento o fue ab-intestato.');
        addSidebarStep('prorrogas', '⏳ Prórrogas', 'Si el plazo de declaración de 180 días se venció y solicitó una prórroga, la registrará aquí.');
        addSidebarStep('herederos', '👥 Herederos', 'Deberá identificar a cada heredero registrado y establecer su grado de parentesco con el difunto.');
        addSidebarStep('inmuebles', '🏠 Bienes Inmuebles', 'Sección para declarar casas, apartamentos, terrenos u otros bienes raíces.');
        addSidebarStep('muebles', '🚗 Bienes Muebles', 'Incluye todo el dinero en bancos, vehículos, acciones, seguros y demás pertenencias.');
        addSidebarStep('pasivosDeuda', '💳 Pasivos (Deuda)', 'Aquí registrará las deudas formales que dejó el causante (tarjetas de crédito, préstamos).');
        addSidebarStep('pasivosGastos', '🏥 Pasivos (Gastos)', 'Registro de los gastos comprobados de última enfermedad y los gastos funerarios.');
        addSidebarStep('desgravamenes', '🛡️ Desgravámenes', 'Deducciones permitidas por la ley, como la vivienda principal y honorarios profesionales.');
        addSidebarStep('exenciones', '📜 Exenciones', 'Bienes o montos exentos del pago del impuesto según la legislación vigente.');
        addSidebarStep('exoneraciones', '✅ Exoneraciones', 'Casos en que el Ejecutivo Nacional dispensa legalmente el pago del impuesto.');
        addSidebarStep('litigiosos', '⚖️ Bienes Litigiosos', 'Bienes cuyo derecho de propiedad se encuentra bajo disputa legal o embargo.');
        addSidebarStep('resumen', '📋 Resumen Declaración', 'Al terminar todo el llenado, aquí podrá revisar cómo el sistema calculó automáticamente el impuesto.');
        addSidebarStep('verDeclaracion', '🖨️ Ver Declaración', 'El objetivo final: en esta sección accederá e imprimirá la Planilla Sucesoral oficial del SENIAT.');

        return steps;
    }

    // Método global para llamar este tour manualmente
    window.playSucesionesOnboardingTour = function () {
        var steps = getSucesionesOnboardingSteps();

        // El tercer parámetro ('sucesiones_onboarding_global') forza a marcarlo como visto
        window.startGuidedTour(null, steps, 'sucesiones_onboarding_global');

        // Al terminar, nos aseguramos que cualquier panel quede cerrado por orden
        setTimeout(function () {
            var accordion = document.getElementById('accordionFlushExample');
            if (accordion) {
                accordion.querySelectorAll('.accordion-collapse').forEach(function (p) { p.classList.remove('show'); });
                accordion.querySelectorAll('.accordion-button').forEach(function (b) { b.classList.add('collapsed'); });
            }
        }, 1000); // Pequeño debounce para no interferir con la limpieza propia del driver
    };

    // ─────────────────────────────────────────────────────────────────────
    // TOUR: Sucesión - Tipo Herencia e Inicio del Inventario (/simulador/sucesion/herencia)
    // ─────────────────────────────────────────────────────────────────────
    TOURS.sucesiones_herencia = function () {
        var steps = [];
        var tbody = document.getElementById('tbodyTipos');
        var isEmpty = tbody && tbody.innerText.indexOf('No hay tipos de herencia') !== -1;

        if (isEmpty) {
            if (!hasSeenTour('sucesiones_onboarding_global')) {
                steps = steps.concat(getSucesionesOnboardingSteps());
                markTourSeen('sucesiones_onboarding_global');
            }

            // Regreso al trabajo actual (asegurando expandir herencia de nuevo)
            var formInfo = document.getElementById('formTipoHerencia');
            if (formInfo) {
                steps.push({
                    element: formInfo,
                    popover: {
                        title: '👉 Comencemos a Trabajar',
                        description:
                            'Regresando al paso actual: Analice los datos que se le ha asignado y seleccione ' +
                            'el tipo o los tipos de herencia que correspondan al caso.',
                        side: 'top',
                        align: 'center'
                    },
                    onHighlightStarted: function () {
                        // Expande el panel de herencia al regresar para que se vea iluminado en el menú
                        var btn = document.querySelector('button[data-section="herencia"]');
                        if (btn) {
                            var accordion = document.getElementById('accordionFlushExample');
                            var panel = accordion ? accordion.querySelector('[data-panel="herencia"]') : null;
                            if (panel && !panel.classList.contains('show')) {
                                if (accordion) {
                                    accordion.querySelectorAll('.accordion-collapse').forEach(function (p) { p.classList.remove('show'); });
                                    accordion.querySelectorAll('.accordion-button').forEach(function (b) { b.classList.add('collapsed'); });
                                }
                                panel.classList.add('show');
                                btn.classList.remove('collapsed');
                            }
                        }

                    }
                });
            }

            var chkTestamento = document.getElementById('chkTestamento');
            if (chkTestamento) {
                steps.push({
                    element: chkTestamento,
                    popover: {
                        title: '⚠️ Opciones Especiales',
                        description:
                            'Tome en cuenta que opciones condicionales como <strong>Testamento</strong> o <strong>Beneficio de Inventario</strong> ' +
                            'desplegarán automáticamente campos adicionales que debe llenar para poder avanzar.',
                        side: 'bottom',
                        align: 'center'
                    }
                });
            }

            var btnGuardar = document.getElementById('tourBtnGuardarHerencia');
            if (btnGuardar) {
                steps.push({
                    element: btnGuardar,
                    popover: {
                        title: '💾 ¡Regla de Oro!',
                        description:
                            'Una vez que seleccione la opción correcta, <strong>SIEMPRE debe presionar Guardar</strong> ' +
                            'antes de cambiar de sección en el menú lateral. De lo contrario, perderá los datos que introdujo.',
                        side: 'top',
                        align: 'center'
                    }
                });
            }

            var tabla = document.getElementById('tablaTiposHerencia');
            if (tabla) {
                steps.push({
                    element: tabla,
                    popover: {
                        title: '📊 Sus Registros Guardados',
                        description:
                            'Al guardar con éxito, sus registros aparecerán listados aquí. ' +
                            'Esta tabla inferior es un estándar en todas las páginas para que usted verifique ' +
                            'lo que ha cargado.',
                        side: 'top',
                        align: 'center'
                    }
                });
            }

        } else {
            // ESTADO 2: Ya tiene datos guardados
            steps.push({
                popover: {
                    title: '✅ Registro Exitoso',
                    description:
                        'Estupendo, usted ya ha completado la selección del Tipo de Herencia.',
                    side: 'over',
                    align: 'center'
                }
            });

            var tablaGuardada = document.getElementById('tablaTiposHerencia');
            if (tablaGuardada) {
                steps.push({
                    element: tablaGuardada,
                    popover: {
                        title: '📝 Edición y Control',
                        description:
                            'Observe que la tabla ya refleja sus opciones guardadas. Recuerde que puede <strong>Editar</strong> o ' +
                            '<strong>Eliminar</strong> los registros usando los iconos de la columna de Acción.',
                        side: 'top',
                        align: 'center'
                    }
                });
            }

            var selectorProrrogas = 'button[data-section="prorrogas"]';
            if (document.querySelector(selectorProrrogas)) {
                steps.push({
                    element: selectorProrrogas,
                    popover: {
                        title: '⏭️ Próximo Paso',
                        description:
                            'Su siguiente tarea es registrar las <strong>Prórrogas</strong> si el caso lo amerita, o continuar ' +
                            'por el menú bajando pacientemente. <strong>Haga clic en este menú</strong> para avanzar.',
                        side: 'right',
                        align: 'center'
                    }
                });
            }
        }

        return steps;
    };


    // ─────────────────────────────────────────────────────────────────────
    // TOUR: Sucesión - Prórrogas (/simulador/sucesion/prorrogas)
    // ─────────────────────────────────────────────────────────────────────
    TOURS.sucesiones_prorrogas = function () {
        var steps = [];
        var container = document.getElementById('tablaContainerProrrogas');
        // Detectar si hay registros usando el display del container
        var isEmpty = container && container.style.display === 'none';

        if (isEmpty) {
            // ESTADO 1: Tabla Vacía -> Onboarding
            steps.push({
                popover: {
                    title: '⏳ Solicitud de Prórrogas',
                    description:
                        'Esta sección permite registrar las prórrogas. ' +
                        'Una prórroga se solicita típicamente cuando no se puede declarar en el ' +
                        '<strong>plazo original de 180 días</strong> posteriores al fallecimiento del causante.',
                    side: 'over',
                    align: 'center'
                }
            });

            // Se apunta al contenedor padre para iluminar tanto la etiqueta como el campo de entrada
            var getFormGroup = function (id) {
                var el = document.getElementById(id);
                return el ? el.closest('.form-group') : null;
            };

            var btnSubmit = document.querySelector('form button[type="submit"]');

            var fSolicitud = getFormGroup('fechaSolicitud');
            if (fSolicitud) {
                steps.push({
                    element: fSolicitud,
                    popover: {
                        title: '📅 Fecha de Solicitud',
                        description: 'Indique la <strong>Fecha de Solicitud</strong> formal de la prórroga.',
                        side: 'bottom',
                        align: 'center'
                    }
                });
            }

            var fResolucion = getFormGroup('fechaResolucion');
            if (fResolucion) {
                steps.push({
                    element: fResolucion,
                    popover: {
                        title: '📆 Fecha de Resolución',
                        description: 'Señale la <strong>Fecha de Resolución</strong> emitida por el SENIAT.',
                        side: 'bottom',
                        align: 'center'
                    }
                });
            }

            var fVencimiento = getFormGroup('fechaVencimiento');
            if (fVencimiento) {
                steps.push({
                    element: fVencimiento,
                    popover: {
                        title: '⏳ Fecha de Vencimiento',
                        description: 'Indique la <strong>Fecha de Vencimiento</strong> de la prórroga.',
                        side: 'bottom',
                        align: 'center'
                    }
                });
            }

            var nroRes = getFormGroup('nroResolucion');
            if (nroRes) {
                steps.push({
                    element: nroRes,
                    popover: {
                        title: '📑 N° de Resolución',
                        description: 'Ingrese el <strong>Número de Resolución</strong> de la providencia.',
                        side: 'top',
                        align: 'center'
                    }
                });
            }

            var plazo = getFormGroup('plazoDias');
            if (plazo) {
                steps.push({
                    element: plazo,
                    popover: {
                        title: '⏱️ Plazo Otorgado',
                        description: 'Escriba el <strong>Plazo Otorgado</strong> en cantidad de días.',
                        side: 'top',
                        align: 'center'
                    }
                });
            }

            if (btnSubmit) {
                steps.push({
                    element: btnSubmit,
                    popover: {
                        title: '💾 Guardar Prórroga',
                        description:
                            'Una vez llenados los campos, asegúrese de presionar <strong>Guardar</strong> para registrar ' +
                            'la prórroga en el sistema.',
                        side: 'top',
                        align: 'center'
                    }
                });
            }
        } else {
            // ESTADO 2: Con datos guardados
            steps.push({
                popover: {
                    title: '✅ Registro Exitoso',
                    description:
                        'Excelente, la prórroga ha sido registrada correctamente.',
                    side: 'over',
                    align: 'center'
                }
            });

            if (container) {
                steps.push({
                    element: container,
                    popover: {
                        title: '📝 Lista de Prórrogas',
                        description:
                            'La prórroga que acaba de registrar aparecerá listada en esta sección. ' +
                            '<br><br>💡 <strong>Nota:</strong> De ser necesario, puede <strong>agregar más prórrogas</strong> ' +
                            'al sistema llenando nuevamente el formulario superior y guardando.',
                        side: 'top',
                        align: 'center'
                    }
                });
            }

            var iconosAccion = document.querySelector('#tbodyProrrogas .accionesicono');
            if (iconosAccion) {
                steps.push({
                    element: iconosAccion,
                    popover: {
                        title: '✏️ Editar y Eliminar',
                        description:
                            'Si cometió un error, puede emplear estos íconos de control para cargar nuevamente los datos en el formulario y ' +
                            '<strong>Modificar</strong> la prórroga, o en su defecto <strong>Eliminarla</strong> por completo del sistema.',
                        side: 'left',
                        align: 'center'
                    }
                });
            }

            var selectorHerederos = 'button[data-section="herederos"]';
            if (document.querySelector(selectorHerederos)) {
                steps.push({
                    element: selectorHerederos,
                    popover: {
                        title: '⏭️ Próximo Paso',
                        description:
                            'Si ya finalizó con las prórrogas pertinentes, su siguiente tarea es registrar a los <strong>Herederos</strong>. ' +
                            '<strong>Haga clic en este menú</strong> para avanzar a la siguiente sección.',
                        side: 'right',
                        align: 'center'
                    }
                });
            }
        }

        return steps;
    };


    // ─────────────────────────────────────────────────────────────────────
    // TOUR: Sucesión - Identificación de Herederos (/simulador/sucesion/herederos)
    //
    // Context-Aware: Verifica si los herederos ya fueron actualizados.
    // Estado 1 (sin actualizar): Onboarding — explica tabla, iconos, modal.
    // Estado 2 (actualizados): Resumen + siguiente paso condicional:
    //   - Si hay premuertos → Herederos Premuerto
    //   - Si no → Bienes Inmuebles
    // ─────────────────────────────────────────────────────────────────────
    TOURS.sucesiones_herederos = function () {
        var steps = [];

        // ── Detección de estado (basada en DOM renderizado) ──
        // La tabla es renderizada por JS inline con iconos de estado:
        //   ✅ .text-success = heredero actualizado (tiene parentesco + fecha)
        //   ❌ .text-danger  = heredero sin actualizar
        // La columna "Premuerto" muestra "SI" o "NO" en texto.
        var tbody = document.getElementById('tbodyHerederos');
        var filas = tbody ? tbody.querySelectorAll('tr') : [];
        var totalHerederos = 0;
        var herederosNoActualizados = 0;
        var hayPremuertos = false;

        filas.forEach(function (fila) {
            // Ignorar fila de "No hay herederos"
            if (fila.querySelector('td[colspan]')) return;
            totalHerederos++;
            if (fila.querySelector('.text-danger')) herederosNoActualizados++;
            // Columna Premuerto es la 6ta celda (índice 5)
            var celdas = fila.querySelectorAll('td');
            if (celdas.length >= 6 && celdas[5].textContent.trim() === 'SI') {
                hayPremuertos = true;
            }
        });

        var todosActualizados = totalHerederos > 0 && herederosNoActualizados === 0;

        if (!todosActualizados) {
            // ── ESTADO 1: Herederos sin actualizar → Onboarding completo ──

            steps.push({
                popover: {
                    title: '👥 Identificación de Herederos',
                    description:
                        'En esta sección debe <strong>actualizar los datos</strong> de cada heredero ' +
                        'que fue registrado durante la inscripción del RIF.<br><br>' +
                        'A cada uno deberá asignarle su <strong>Fecha de Nacimiento</strong>, ' +
                        '<strong>Parentesco</strong> con el causante, y si aplica, marcarlo como <strong>Premuerto</strong>.',
                    side: 'over',
                    align: 'center'
                }
            });

            // Tabla de herederos (reusar tbody de detección de estado)
            if (tbody) {
                var tabla = tbody.closest('table');
                if (tabla) tabla.id = 'tourTablaHerederos';
                steps.push({
                    element: tabla ? '#tourTablaHerederos' : '#tbodyHerederos',
                    popover: {
                        title: '📋 Tabla de Herederos',
                        description:
                            'Aquí se listan todos los herederos del caso.<br><br>' +
                            '<i class="bi bi-check-circle-fill text-success"></i> <strong>Verde:</strong> El heredero ya tiene sus datos completos.<br>' +
                            '<i class="bi bi-x-circle-fill text-danger"></i> <strong>Rojo:</strong> Faltan datos por completar.<br><br>' +
                            'Deberá actualizar <strong>todos</strong> los herederos antes de avanzar.',
                        side: 'bottom',
                        align: 'center'
                    }
                });
            }

            // Botón de edición
            var primerEditar = document.querySelector('[data-edit-idx]');
            if (primerEditar) {
                steps.push({
                    element: '[data-edit-idx]',
                    popover: {
                        title: '✏️ Editar Heredero',
                        description:
                            'Haga clic en el <strong>icono de lápiz</strong> para abrir el formulario de edición ' +
                            'de cada heredero.',
                        side: 'left',
                        align: 'center'
                    }
                });
            }

            // Explicación del modal (sin elemento — centrado)
            steps.push({
                popover: {
                    title: '📝 Datos Requeridos en el Modal',
                    description:
                        'Al abrir el modal de edición, deberá completar:<br><br>' +
                        '• <strong>Premuerto:</strong> "Sí" si el heredero falleció antes que el causante.<br>' +
                        '• <strong>Fecha de Nacimiento:</strong> Dato obligatorio.<br>' +
                        '• <strong>Parentesco:</strong> Relación con el causante (Hijo/a, Cónyuge, etc.).<br><br>' +
                        'Si marca <strong>Premuerto = Sí</strong>, deberá también ingresar la Fecha de Fallecimiento.',
                    side: 'over',
                    align: 'center'
                }
            });

            // Nota sobre premuertos
            steps.push({
                popover: {
                    title: '💡 Dato Importante: Premuerto',
                    description:
                        'Si algún heredero es marcado como <strong>Premuerto</strong>, después de completar ' +
                        'esta sección deberá ir a <strong>"Herederos Premuerto"</strong> (siguiente página en el menú) ' +
                        'para registrar a las personas que <strong>representan</strong> al heredero fallecido.',
                    side: 'over',
                    align: 'center'
                }
            });

        } else {
            // ── ESTADO 2: Todos los herederos actualizados ──

            steps.push({
                popover: {
                    title: '✅ Herederos Actualizados',
                    description:
                        'Todos los herederos tienen sus datos completos: ' +
                        '<strong>Fecha de Nacimiento</strong> y <strong>Parentesco</strong> asignados.<br><br>' +
                        'Si necesita corregir algún dato, puede editar cualquier heredero con el icono del lápiz.',
                    side: 'over',
                    align: 'center'
                }
            });

            // Siguiente paso condicional
            if (hayPremuertos) {
                // Hay premuertos → señalar Herederos Premuerto en el sidebar
                var linkPremuerto = document.querySelector('[data-panel="herederos"] a[href*="herederos_premuerto"]');
                if (linkPremuerto) {
                    linkPremuerto.id = 'tourLinkHerederosPremuerto';
                    steps.push({
                        element: '#tourLinkHerederosPremuerto',
                        popover: {
                            title: '⏭️ Próximo Paso: Herederos Premuerto',
                            description:
                                'Tiene herederos marcados como <strong>Premuerto</strong>. Debe ir a ' +
                                '<strong>Herederos Premuerto</strong> para registrar las personas que los representan ' +
                                'en la sucesión.<br><br>' +
                                '<strong>Haga clic aquí</strong> para continuar.',
                            side: 'right',
                            align: 'center'
                        },
                        onHighlightStarted: function () {
                            // Asegurar que el panel herederos esté expandido
                            var btn = document.querySelector('button[data-section="herederos"]');
                            if (btn) {
                                var accordion = document.getElementById('accordionFlushExample');
                                var panel = accordion ? accordion.querySelector('[data-panel="herederos"]') : null;
                                if (panel && !panel.classList.contains('show')) {
                                    if (accordion) {
                                        accordion.querySelectorAll('.accordion-collapse').forEach(function (p) { p.classList.remove('show'); });
                                        accordion.querySelectorAll('.accordion-button').forEach(function (b) { b.classList.add('collapsed'); });
                                    }
                                    panel.classList.add('show');
                                    btn.classList.remove('collapsed');
                                }
                            }
                        }
                    });
                }
            } else {
                // No hay premuertos → señalar Bienes Inmuebles
                var selectorInmuebles = 'button[data-section="inmuebles"]';
                if (document.querySelector(selectorInmuebles)) {
                    steps.push({
                        element: selectorInmuebles,
                        popover: {
                            title: '⏭️ Próximo Paso: Bienes Inmuebles',
                            description:
                                'No hay herederos premuertos en su caso. Puede avanzar directamente a ' +
                                '<strong>Bienes Inmuebles</strong> para comenzar a declarar los activos del causante.<br><br>' +
                                '<strong>Haga clic en este menú</strong> para continuar.',
                            side: 'right',
                            align: 'center'
                        }
                    });
                }
            }
        }

        return steps;
    };


    // ─────────────────────────────────────────────────────────────────────
    // TOUR: Sucesión - Herederos Premuerto (/simulador/sucesion/herederos_premuerto)
    //
    // Context-Aware con 3 estados:
    //   Estado 1 (sin premuertos): No hay premuertos marcados → informativo + skip.
    //   Estado 2 (premuertos sin sub-herederos): Guía de tabla + lupa + agregar.
    //   Estado 3 (con sub-herederos): Resumen + siguiente paso.
    // ─────────────────────────────────────────────────────────────────────
    TOURS.sucesiones_herederos_premuerto = function () {
        var steps = [];

        // ── Detección de estado ──
        var tbodyPremuertos = document.getElementById('tbodyPremuertos');
        var noHayPremuertos = tbodyPremuertos &&
            tbodyPremuertos.innerText.indexOf('No hay herederos premuertos') !== -1;

        // Contar sub-herederos existentes.
        // La variable herederosPremuertos vive en un IIFE local de la vista,
        // así que leemos el dato desde un data-attribute inyectado en el DOM.
        var wrapperEl = document.querySelector('[data-sub-herederos-count]');
        var subHerederosCount = wrapperEl ? parseInt(wrapperEl.getAttribute('data-sub-herederos-count')) || 0 : 0;
        var tieneSubHerederos = subHerederosCount > 0;

        if (noHayPremuertos) {
            // ── ESTADO 1: Sin premuertos → Informativo ──

            steps.push({
                popover: {
                    title: 'ℹ️ Herederos Premuerto en Representación',
                    description:
                        'Esta sección se utiliza <strong>solo cuando un heredero fue marcado como "Premuerto"</strong> ' +
                        'en la sección anterior (Herederos).<br><br>' +
                        'Un heredero premuerto es aquel que <strong>falleció antes que el causante</strong>, ' +
                        'y sus propios herederos lo representan en la sucesión.',
                    side: 'over',
                    align: 'center'
                }
            });

            steps.push({
                popover: {
                    title: '✅ No Aplica para su Caso',
                    description:
                        'No hay herederos marcados como premuertos en su declaración. ' +
                        'Puede <strong>avanzar al siguiente paso</strong> del proceso.',
                    side: 'over',
                    align: 'center'
                }
            });

            var selectorInmuebles1 = 'button[data-section="inmuebles"]';
            if (document.querySelector(selectorInmuebles1)) {
                steps.push({
                    element: selectorInmuebles1,
                    popover: {
                        title: '⏭️ Próximo Paso: Bienes Inmuebles',
                        description:
                            'Continúe con <strong>Bienes Inmuebles</strong> para comenzar a declarar ' +
                            'los activos del causante.<br><br>' +
                            '<strong>Haga clic en este menú</strong> para avanzar.',
                        side: 'right',
                        align: 'center'
                    }
                });
            }

        } else if (!tieneSubHerederos) {
            // ── ESTADO 2: Premuertos SIN sub-herederos → Guía completa ──

            steps.push({
                popover: {
                    title: '👥 Herederos del Premuerto',
                    description:
                        'Aquí debe registrar a las personas que <strong>representan</strong> a cada heredero premuerto ' +
                        'en la sucesión.<br><br>' +
                        'Por cada premuerto, deberá agregar al menos un heredero con sus datos completos.',
                    side: 'over',
                    align: 'center'
                }
            });

            // Tabla de premuertos
            if (tbodyPremuertos) {
                var tablaPremuertos = tbodyPremuertos.closest('table');
                if (tablaPremuertos) tablaPremuertos.id = 'tourTablaPremuertos';
                steps.push({
                    element: tablaPremuertos ? '#tourTablaPremuertos' : '#tbodyPremuertos',
                    popover: {
                        title: '📋 Lista de Herederos Premuertos',
                        description:
                            'Esta tabla muestra los herederos que fueron marcados como <strong>Premuerto</strong> ' +
                            'en la sección anterior.<br><br>' +
                            'Haga clic en el icono de <strong>lupa</strong> para ver y agregar los herederos de cada uno.',
                        side: 'bottom',
                        align: 'center'
                    }
                });
            }

            // Icono de lupa
            var primerLupa = document.querySelector('[data-padre-cedula]');
            if (primerLupa) {
                steps.push({
                    element: '[data-padre-cedula]',
                    popover: {
                        title: '🔍 Ver Herederos del Premuerto',
                        description:
                            'Haga clic en la <strong>lupa</strong> para expandir la sección de detalle ' +
                            'y ver el botón <strong>"Agregar Heredero"</strong>.',
                        side: 'left',
                        align: 'center'
                    }
                });
            }

            // Explicación del proceso de agregar
            steps.push({
                popover: {
                    title: '➕ Agregar Heredero del Premuerto',
                    description:
                        'Al expandir el detalle, verá un botón <strong>"Agregar Heredero"</strong> ' +
                        'que abrirá un modal donde deberá llenar:<br><br>' +
                        '• <strong>Nombre</strong> y <strong>Apellido</strong><br>' +
                        '• <strong>Cédula</strong> (coloque 0 si no tiene documento)<br>' +
                        '• <strong>Fecha de Nacimiento</strong><br>' +
                        '• <strong>Parentesco</strong> con el premuerto<br><br>' +
                        'Puede agregar <strong>varios herederos</strong> por cada premuerto.',
                    side: 'over',
                    align: 'center'
                }
            });

        } else {
            // ── ESTADO 3: Con sub-herederos → Resumen ──

            steps.push({
                popover: {
                    title: '✅ Herederos Premuerto Registrados',
                    description:
                        'Ya tiene <strong>' + subHerederosCount + ' heredero(s)</strong> de premuerto registrado(s) ' +
                        'en el sistema.<br><br>' +
                        'Si necesita agregar más, haga clic en la lupa del premuerto correspondiente ' +
                        'y use el botón "Agregar Heredero".',
                    side: 'over',
                    align: 'center'
                }
            });

            var selectorInmuebles3 = 'button[data-section="inmuebles"]';
            if (document.querySelector(selectorInmuebles3)) {
                steps.push({
                    element: selectorInmuebles3,
                    popover: {
                        title: '⏭️ Próximo Paso: Bienes Inmuebles',
                        description:
                            'Excelente. Ya puede continuar con <strong>Bienes Inmuebles</strong> para declarar ' +
                            'los activos del causante.<br><br>' +
                            '<strong>Haga clic en este menú</strong> para avanzar.',
                        side: 'right',
                        align: 'center'
                    }
                });
            }
        }

        return steps;
    };


    // ─────────────────────────────────────────────────────────────────────
    // TOUR: Sucesión - Bienes Inmuebles (/simulador/sucesion/bienes_inmuebles)
    //
    // Context-Aware con 2 estados:
    //   Estado 1 (sin inmuebles): Onboarding — 8 steps agrupados por secciones
    //     lógicas del formulario CRUD (tipo de bien, opciones, descripción,
    //     datos registrales, valores, guardar).
    //   Estado 2 (con inmuebles): Resumen + siguiente paso (Bienes Muebles).
    // ─────────────────────────────────────────────────────────────────────
    TOURS.sucesiones_bienes_inmuebles = function () {
        var steps = [];

        // ── Detección de estado (DOM-based) ──
        // La tabla de inmuebles usa style="display:none" cuando no hay datos.
        var tablaContainer = document.getElementById('tablaContainer');
        var tieneInmuebles = tablaContainer && tablaContainer.style.display !== 'none';

        // Contar inmuebles para el mensaje de resumen
        var cantidadInmuebles = 0;
        if (tieneInmuebles) {
            var tbodyInm = document.getElementById('tbodyInmuebles');
            if (tbodyInm) {
                // Restar 1 por la fila de totales
                var filasInm = tbodyInm.querySelectorAll('tr');
                cantidadInmuebles = filasInm.length > 1 ? filasInm.length - 1 : 0;
            }
        }

        if (!tieneInmuebles) {
            // ── ESTADO 1: Sin inmuebles → Onboarding completo (8 steps agrupados) ──

            // Step 1: Introducción general
            steps.push({
                popover: {
                    title: '🏠 Bienes Inmuebles',
                    description:
                        'En esta sección debe registrar <strong>todos los inmuebles</strong> que pertenecían al ' +
                        'causante al momento de su fallecimiento.<br><br>' +
                        'Por cada inmueble, deberá llenar el formulario completo y presionar ' +
                        '<strong>Guardar</strong>. Puede registrar tantos inmuebles como sean necesarios.<br><br>' +
                        '<em>Si algún campo no aplica para su caso, escriba "NO APLICA".</em>',
                    side: 'over',
                    align: 'center'
                }
            });

            // Step 2: Tipo de Bien (checkboxes)
            var checkboxGroup = document.querySelector('.checkbox-group');
            if (checkboxGroup) {
                steps.push({
                    element: '.checkbox-group',
                    popover: {
                        title: '📋 Tipo de Bien',
                        description:
                            'Comience seleccionando el <strong>tipo de inmueble</strong>: Casa, Apartamento, ' +
                            'Terreno, Local, Oficina, etc.<br><br>' +
                            'Puede marcar <strong>más de uno</strong> si aplica (ej: Casa + Terreno).<br><br>' +
                            '<strong>Dato importante:</strong> Si selecciona Casa, Apartamento, Townhouse o Quinta, ' +
                            'se habilitará la opción de <em>Vivienda Principal</em>.',
                        side: 'bottom',
                        align: 'center'
                    }
                });
            }

            // Step 3: Vivienda Principal
            var vpSelect = document.getElementById('vp');
            if (vpSelect) {
                steps.push({
                    element: '#vp',
                    popover: {
                        title: '🏡 Vivienda Principal',
                        description:
                            'Indique si este inmueble era la <strong>vivienda principal</strong> del causante.<br><br>' +
                            'Este campo solo se habilita para tipos de bien residencial ' +
                            '(Casa, Apartamento, Townhouse, Quinta).<br><br>' +
                            '⚠️ Solo puede existir <strong>una vivienda principal</strong> en toda la declaración. ' +
                            'Si ya registró una, el campo se deshabilitará automáticamente.',
                        side: 'bottom',
                        align: 'center'
                    }
                });
            }

            // Step 4: Bien Litigioso
            var blSelect = document.getElementById('bl');
            if (blSelect) {
                steps.push({
                    element: '#bl',
                    popover: {
                        title: '⚖️ Bien Litigioso',
                        description:
                            'Si el inmueble está en <strong>disputa legal</strong>, seleccione "Sí".<br><br>' +
                            'Aparecerán campos adicionales donde deberá indicar:<br>' +
                            '• Número de Expediente<br>' +
                            '• Tribunal de la Causa<br>' +
                            '• Partes en el Juicio<br>' +
                            '• Estado del Juicio<br><br>' +
                            'Si no está en litigio, déjelo en "No".',
                        side: 'bottom',
                        align: 'center'
                    }
                });
            }

            // Step 5: Descripción y características físicas
            var descInp = document.getElementById('desc_inp');
            if (descInp) {
                steps.push({
                    element: '#desc_inp',
                    popover: {
                        title: '📝 Descripción y Características',
                        description:
                            'Describa el inmueble con detalle. Además de este campo, deberá completar:<br><br>' +
                            '• <strong>Linderos:</strong> Límites geográficos del inmueble (Norte, Sur, Este, Oeste).<br>' +
                            '• <strong>Superficies:</strong> Construida, Sin Construir y Área Total.<br>' +
                            '• <strong>Dirección:</strong> Ubicación completa del inmueble.<br>' +
                            '• <strong>Oficina Subalterna:</strong> Registro donde está inscrito el inmueble.',
                        side: 'top',
                        align: 'center'
                    }
                });
            }

            // Step 6: Datos del Registro
            var nrInput = document.getElementById('nr');
            if (nrInput) {
                steps.push({
                    element: '#nr',
                    popover: {
                        title: '📑 Datos del Registro',
                        description:
                            'Complete los datos del <strong>documento de propiedad</strong>:<br><br>' +
                            '• <strong>Nro de Registro</strong> y <strong>Libro</strong><br>' +
                            '• <strong>Protocolo</strong> y <strong>Fecha</strong> del registro<br>' +
                            '• <strong>Trimestre</strong> y <strong>Asiento Registral</strong><br>' +
                            '• <strong>Matrícula</strong> y <strong>Libro de Folio Real</strong><br><br>' +
                            'Si algún dato no está disponible, escriba <strong>"NO APLICA"</strong>.',
                        side: 'top',
                        align: 'center'
                    }
                });
            }

            // Step 7: Valores
            var vdecInput = document.getElementById('vdec');
            if (vdecInput) {
                steps.push({
                    element: '#vdec',
                    popover: {
                        title: '💰 Valores del Inmueble',
                        description:
                            'Ingrese los valores económicos del inmueble:<br><br>' +
                            '• <strong>Valor Original (Bs.):</strong> Precio según el documento de compra-venta.<br>' +
                            '• <strong>Valor Declarado (Bs.):</strong> Valor actual del inmueble.<br><br>' +
                            '⚠️ El <strong>Valor Declarado</strong> es el monto que se usará para el ' +
                            '<strong>cálculo del impuesto</strong> de sucesiones.',
                        side: 'top',
                        align: 'center'
                    }
                });
            }

            // Step 8: Botón Guardar
            var btnGuardar = document.getElementById('btnGuardar');
            if (btnGuardar) {
                steps.push({
                    element: '#btnGuardar',
                    popover: {
                        title: '💾 Guardar Inmueble',
                        description:
                            'Una vez completados <strong>todos los campos obligatorios</strong>, el botón se ' +
                            'habilitará y podrá guardar el inmueble.<br><br>' +
                            'El inmueble aparecerá en una <strong>tabla debajo</strong> del formulario, ' +
                            'donde podrá editarlo o eliminarlo.<br><br>' +
                            'Si el causante tenía <strong>varios inmuebles</strong>, repita el proceso ' +
                            'para cada uno.',
                        side: 'top',
                        align: 'center'
                    }
                });
            }

        } else {
            // ── ESTADO 2: Con inmuebles registrados → Resumen ──

            steps.push({
                element: '#tableim',
                popover: {
                    title: '✅ Inmuebles Registrados',
                    description:
                        'Tiene <strong>' + cantidadInmuebles + ' inmueble(s)</strong> registrado(s) en la declaración.<br><br>' +
                        'Puede <strong>editar</strong> cualquier registro con el icono ✏️ o ' +
                        '<strong>eliminar</strong> con 🗑️.<br><br>' +
                        'Para agregar otro inmueble, complete el formulario de arriba y presione Guardar.',
                    side: 'top',
                    align: 'center'
                }
            });

            // Siguiente paso: Bienes Muebles
            var selectorMuebles = 'button[data-section="muebles"]';
            if (document.querySelector(selectorMuebles)) {
                steps.push({
                    element: selectorMuebles,
                    popover: {
                        title: '⏭️ Próximo Paso: Bienes Muebles',
                        description:
                            'Si ya registró todos los inmuebles del caso, continúe con ' +
                            '<strong>Bienes Muebles</strong> para declarar cuentas bancarias, vehículos, ' +
                            'seguros y demás activos del causante.<br><br>' +
                            '<strong>Haga clic en este menú</strong> para avanzar.',
                        side: 'right',
                        align: 'center'
                    }
                });
            }
        }

        return steps;
    };


    // ─────────────────────────────────────────────────────────────────────
    // TOURS: Sucesión - Bienes Muebles (12 módulos)
    //
    // buildBienMuebleTour(config) genera steps context-aware para cada
    // sub-página. Cada módulo provee stepsEspecificos[] con element+popover
    // que apuntan a campos reales del DOM. El builder agrega los steps
    // comunes: Bien Litigioso, Porcentaje+Descripción, Valor, Guardar.
    // ─────────────────────────────────────────────────────────────────────

    function tieneRegistrosMueble(containerId, tbodyId) {
        var container = document.getElementById(containerId);
        if (!container) return false;
        if (container.style.display === 'none') return false;
        var tbody = document.getElementById(tbodyId);
        if (!tbody) return false;
        var filas = tbody.querySelectorAll('tr');
        return filas.length > 1;
    }

    function contarRegistrosMueble(tbodyId) {
        var tbody = document.getElementById(tbodyId);
        if (!tbody) return 0;
        var filas = tbody.querySelectorAll('tr');
        return filas.length > 1 ? filas.length - 1 : 0;
    }

    /**
     * @param {Object}   config
     * @param {string}   config.titulo            — Título con emoji
     * @param {string}   config.nombreModulo       — Nombre legible
     * @param {string}   config.descripcionIntro   — HTML de intro
     * @param {Array}    config.stepsEspecificos    — [{element, popover}] con selectores reales
     * @param {string}   config.containerId        — ID tabla container
     * @param {string}   config.tbodyId            — ID tbody
     * @param {Object}   config.siguientePaso       — { label, selector }
     */
    function buildBienMuebleTour(config) {
        var steps = [];
        var hayDatos = tieneRegistrosMueble(config.containerId, config.tbodyId);

        // ── Si hay registros, empezar con resumen de la tabla ──
        if (hayDatos) {
            var cantidad = contarRegistrosMueble(config.tbodyId);
            steps.push({
                element: '#' + config.containerId,
                popover: {
                    title: '✅ ' + config.nombreModulo + ' Registrado(s)',
                    description:
                        'Tiene <strong>' + cantidad + ' registro(s)</strong> de ' +
                        config.nombreModulo + '.<br><br>' +
                        'Puede <strong>editar</strong> con ✏️ o <strong>eliminar</strong> con 🗑️.<br>' +
                        'A continuación se explica cada campo del formulario.',
                    side: 'top',
                    align: 'center'
                }
            });
        } else {
            // Introducción general (solo sin datos)
            steps.push({
                popover: {
                    title: config.titulo,
                    description:
                        config.descripcionIntro + '<br><br>' +
                        'Por cada bien, complete el formulario y presione ' +
                        '<strong>Guardar</strong>. Puede registrar varios.',
                    side: 'over',
                    align: 'center'
                }
            });
        }

        // ── SIEMPRE: Steps específicos del módulo ──
        config.stepsEspecificos.forEach(function (step) {
            if (step.element && document.querySelector(step.element)) {
                steps.push(step);
            } else if (!step.element) {
                steps.push(step);
            }
        });

        // ── SIEMPRE: Bien Litigioso ──
        if (document.getElementById('bl')) {
            steps.push({
                element: '#bl',
                popover: {
                    title: '⚖️ Bien Litigioso',
                    description:
                        'Si este bien está en <strong>disputa legal</strong>, seleccione "Sí".<br><br>' +
                        'Aparecerán campos adicionales donde deberá indicar:<br>' +
                        '• Número de Expediente<br>' +
                        '• Tribunal de la Causa<br>' +
                        '• Partes en el Juicio<br>' +
                        '• Estado del Juicio<br><br>' +
                        'Si no aplica, déjelo en "No".',
                    side: 'bottom',
                    align: 'center'
                }
            });
        }

        // ── SIEMPRE: Porcentaje + Descripción ──
        if (document.getElementById('sporcentaje')) {
            steps.push({
                element: '#sporcentaje',
                popover: {
                    title: '📝 Porcentaje y Descripción',
                    description:
                        '<strong>Porcentaje %:</strong> Proporción de propiedad del causante sobre este bien ' +
                        '(por defecto 0,01).<br><br>' +
                        '<strong>Descripción:</strong> En el campo de al lado, detalle las características ' +
                        'del bien. Sea lo más específico posible.',
                    side: 'bottom',
                    align: 'center'
                }
            });
        }

        // ── SIEMPRE: Valor Declarado ──
        if (document.getElementById('ssc')) {
            steps.push({
                element: '#ssc',
                popover: {
                    title: '💰 Valor Declarado (Bs.)',
                    description:
                        'Ingrese el <strong>valor actual en bolívares</strong> de este bien.<br><br>' +
                        '⚠️ Este es el monto que se usará para el <strong>cálculo del impuesto</strong> ' +
                        'de sucesiones.',
                    side: 'top',
                    align: 'center'
                }
            });
        }

        // ── SIEMPRE: Botón Guardar ──
        var btnSubmit = document.querySelector('button[type=submit]');
        if (btnSubmit) {
            steps.push({
                element: 'button[type=submit]',
                popover: {
                    title: '💾 Guardar',
                    description:
                        'Complete todos los campos obligatorios y el botón se habilitará.<br><br>' +
                        'El registro aparecerá en una <strong>tabla debajo</strong> del formulario ' +
                        'donde podrá editarlo o eliminarlo.',
                    side: 'top',
                    align: 'center'
                }
            });
        }

        // ── Si hay registros, cerrar con enlace al siguiente módulo ──
        if (hayDatos && config.siguientePaso && config.siguientePaso.selector) {
            var nextEl = document.querySelector(config.siguientePaso.selector);
            if (nextEl) {
                steps.push({
                    element: config.siguientePaso.selector,
                    popover: {
                        title: '⏭️ Próximo: ' + config.siguientePaso.label,
                        description:
                            'Si ya registró todos los bienes de este tipo, continúe con ' +
                            '<strong>' + config.siguientePaso.label + '</strong>.<br><br>' +
                            '<strong>Haga clic aquí</strong> para avanzar.',
                        side: 'right',
                        align: 'center'
                    }
                });
            }
        }

        return steps;
    }

    // ── Banco ──
    TOURS.sucesiones_muebles_banco = function () {
        return buildBienMuebleTour({
            titulo: '🏦 Banco',
            nombreModulo: 'Banco',
            descripcionIntro: 'Registre las <strong>cuentas bancarias</strong> del causante: cuentas de ahorro, corriente, fideicomisos, certificados de depósito, etc.',
            stepsEspecificos: [
                { element: '[formcontrolname=codTipoBien]', popover: { title: '📋 Tipo de Bien', description: 'Seleccione el <strong>tipo de producto bancario</strong>: cuenta de ahorro, corriente, certificado de depósito, fideicomiso, etc.', side: 'bottom', align: 'center' } },
                { element: '#vp', popover: { title: '🏦 Nombre del Banco', description: 'Seleccione la <strong>entidad bancaria</strong> donde el causante tenía la cuenta.', side: 'bottom', align: 'center' } },
                { element: '#lind', popover: { title: '🔢 Número de Cuenta', description: 'Ingrese los <strong>20 dígitos</strong> del número de cuenta bancaria.<br><br>⚠️ Debe ser exactamente 20 dígitos numéricos, sin guiones ni espacios.', side: 'bottom', align: 'center' } }
            ],
            containerId: 'tablaContainerBanco',
            tbodyId: 'tbodyBanco',
            siguientePaso: { label: 'Seguro', selector: '[data-panel="muebles"] a[href*="seguro"]' }
        });
    };

    // ── Seguro ──
    TOURS.sucesiones_muebles_seguro = function () {
        return buildBienMuebleTour({
            titulo: '🛡️ Seguro',
            nombreModulo: 'Seguro',
            descripcionIntro: 'Registre las <strong>pólizas de seguro</strong> vigentes del causante al momento del fallecimiento.',
            stepsEspecificos: [
                { element: '[formcontrolname=codTipoBien]', popover: { title: '📋 Tipo de Bien', description: 'Seleccione el <strong>tipo de póliza de seguro</strong>: vida, salud, vehículo, etc.', side: 'bottom', align: 'center' } },
                { element: '#rifEmpresa', popover: { title: '🏢 RIF de la Aseguradora', description: 'Ingrese el <strong>RIF de la empresa aseguradora</strong> (ej: J012345678).<br><br>La <strong>Razón Social</strong> se autocompletará al ingresar un RIF válido.', side: 'bottom', align: 'center' } },
                { element: '#numeroPrima', popover: { title: '📄 Número de Prima', description: 'Ingrese el <strong>número de prima</strong> de la póliza de seguro. Este dato aparece en el contrato de la póliza.', side: 'bottom', align: 'center' } }
            ],
            containerId: 'tablaContainerSeguro',
            tbodyId: 'tbodySeguro',
            siguientePaso: { label: 'Transporte', selector: '[data-panel="muebles"] a[href*="transporte"]' }
        });
    };

    // ── Transporte ──
    TOURS.sucesiones_muebles_transporte = function () {
        return buildBienMuebleTour({
            titulo: '🚗 Transporte',
            nombreModulo: 'Transporte',
            descripcionIntro: 'Registre los <strong>vehículos, embarcaciones o aeronaves</strong> que pertenecían al causante.',
            stepsEspecificos: [
                { element: '#tipoBienTransporte', popover: { title: '📋 Tipo de Transporte', description: 'Seleccione el tipo: <strong>vehículo terrestre, embarcación, aeronave</strong>, etc.', side: 'bottom', align: 'center' } },
                { element: '#anio', popover: { title: '📅 Año, Marca y Modelo', description: 'Complete los datos de identificación del transporte:<br><br>• <strong>Año:</strong> Año de fabricación (4 dígitos).<br>• <strong>Marca:</strong> Fabricante (ej: Toyota, Ford).<br>• <strong>Modelo:</strong> Modelo específico (ej: Corolla, F-150).', side: 'bottom', align: 'center' } },
                { element: '#serial', popover: { title: '🔢 Serial / Placas', description: 'Ingrese el <strong>número de serial, identificador o placas</strong> del transporte.<br><br>Para vehículos terrestres, coloque el número de placa. Para embarcaciones o aeronaves, use el número de matrícula.', side: 'bottom', align: 'center' } }
            ],
            containerId: 'tablaContainerTransporte',
            tbodyId: 'tbodyTransporte',
            siguientePaso: { label: 'Opciones Compra', selector: '[data-panel="muebles"] a[href*="opciones_compra"]' }
        });
    };

    // ── Opciones Compra ──
    TOURS.sucesiones_muebles_opciones_compra = function () {
        return buildBienMuebleTour({
            titulo: '📄 Opciones Compra',
            nombreModulo: 'Opciones Compra',
            descripcionIntro: 'Registre las <strong>opciones de compra-venta</strong> pendientes de ejecución que tenía el causante al momento del fallecimiento.',
            stepsEspecificos: [
                { element: '#lind', popover: { title: '👤 Nombre del Oferente', description: 'Ingrese el <strong>nombre de la persona o empresa</strong> que otorgó la opción de compra al causante.', side: 'bottom', align: 'center' } }
            ],
            containerId: 'tablaContainerOpcionesCompra',
            tbodyId: 'tbodyOpcionesCompra',
            siguientePaso: { label: 'Cuentas y Efectos por Cobrar', selector: '[data-panel="muebles"] a[href*="cuentas_efectos"]' }
        });
    };

    // ── Cuentas y Efectos por Cobrar ──
    TOURS.sucesiones_muebles_cuentas_efectos = function () {
        return buildBienMuebleTour({
            titulo: '📋 Cuentas y Efectos por Cobrar',
            nombreModulo: 'Cuentas y Efectos',
            descripcionIntro: 'Registre las <strong>cuentas y documentos por cobrar</strong> a favor del causante: deudas de terceros, pagarés, letras de cambio, etc.',
            stepsEspecificos: [
                { element: '[formcontrolname=codTipoBien]', popover: { title: '📋 Tipo de Bien', description: 'Seleccione el <strong>tipo de cuenta o efecto</strong> por cobrar.', side: 'bottom', align: 'center' } },
                { element: '#lind', popover: { title: '📝 Número de Documento', description: 'Ingrese el <strong>número de documento</strong> del efecto por cobrar (pagaré, letra, etc.).', side: 'bottom', align: 'center' } },
                { element: '#nombreApellido', popover: { title: '👤 Nombre del Deudor', description: 'Ingrese el <strong>nombre y apellido</strong> de la persona o empresa que le debía al causante.', side: 'bottom', align: 'center' } }
            ],
            containerId: 'tablaContainerCuentasEfectos',
            tbodyId: 'tbodyCuentasEfectos',
            siguientePaso: { label: 'Semovientes', selector: '[data-panel="muebles"] a[href*="semovientes"]' }
        });
    };

    // ── Semovientes ──
    TOURS.sucesiones_muebles_semovientes = function () {
        return buildBienMuebleTour({
            titulo: '🐄 Semovientes',
            nombreModulo: 'Semovientes',
            descripcionIntro: 'Registre los <strong>animales de cría, ganado y semovientes</strong> que pertenecían al causante.',
            stepsEspecificos: [
                { element: '#tipoSemoviente', popover: { title: '📋 Tipo de Semoviente', description: 'Seleccione el <strong>tipo de animal</strong>: bovino, porcino, caprino, equino, aviar, etc.', side: 'bottom', align: 'center' } },
                { element: '#lind', popover: { title: '🔢 Cantidad', description: 'Ingrese la <strong>cantidad de animales</strong> de este tipo que poseía el causante.<br><br>⚠️ Solo se aceptan números enteros.', side: 'bottom', align: 'center' } }
            ],
            containerId: 'tablaContainerSemovientes',
            tbodyId: 'tbodySemovientes',
            siguientePaso: { label: 'Bonos', selector: '[data-panel="muebles"] a[href*="bonos"]' }
        });
    };

    // ── Bonos ──
    TOURS.sucesiones_muebles_bonos = function () {
        return buildBienMuebleTour({
            titulo: '📊 Bonos',
            nombreModulo: 'Bonos',
            descripcionIntro: 'Registre los <strong>bonos, títulos valores y letras del tesoro</strong> del causante.',
            stepsEspecificos: [
                { element: '#tipoBonos', popover: { title: '📋 Tipo de Bono', description: 'Ingrese el <strong>tipo de bono o título valor</strong>: bono de deuda pública, letra del tesoro, certificado, etc.', side: 'bottom', align: 'center' } },
                { element: '#numeroBonos', popover: { title: '🔢 Número de Bonos', description: 'Ingrese la <strong>cantidad de bonos</strong> que poseía el causante de este tipo.', side: 'bottom', align: 'center' } },
                { element: '#numeroSerie', popover: { title: '🏷️ Número de Serie', description: 'Ingrese el <strong>número de serie</strong> del bono o título valor. Este dato identifica de forma única la emisión.', side: 'bottom', align: 'center' } }
            ],
            containerId: 'tablaContainerBonos',
            tbodyId: 'tbodyBonos',
            siguientePaso: { label: 'Acciones', selector: '[data-panel="muebles"] a[href*="acciones"]' }
        });
    };

    // ── Acciones ──
    TOURS.sucesiones_muebles_acciones = function () {
        return buildBienMuebleTour({
            titulo: '📈 Acciones',
            nombreModulo: 'Acciones',
            descripcionIntro: 'Registre las <strong>acciones y participaciones societarias</strong> del causante en empresas.',
            stepsEspecificos: [
                { element: '#rifEmpresa', popover: { title: '🏢 RIF de la Empresa', description: 'Ingrese el <strong>RIF de la empresa</strong> donde el causante tenía acciones (ej: J012345678).<br><br>La <strong>Razón Social</strong> se autocompletará al ingresar un RIF válido.', side: 'bottom', align: 'center' } }
            ],
            containerId: 'tablaContainerAcciones',
            tbodyId: 'tbodyAcciones',
            siguientePaso: { label: 'Prestaciones Sociales', selector: '[data-panel="muebles"] a[href*="prestaciones_sociales"]' }
        });
    };

    // ── Prestaciones Sociales ──
    TOURS.sucesiones_muebles_prestaciones = function () {
        return buildBienMuebleTour({
            titulo: '💼 Prestaciones Sociales',
            nombreModulo: 'Prestaciones Sociales',
            descripcionIntro: 'Registre las <strong>prestaciones sociales acumuladas</strong> del causante en las empresas donde trabajó.',
            stepsEspecificos: [
                { element: '#poseeBanco', popover: { title: '🏦 ¿Posee Banco?', description: 'Si las prestaciones están en un <strong>fideicomiso bancario</strong>, seleccione "Sí".<br><br>Se habilitarán los campos de <strong>Nombre Banco</strong> y <strong>Número de Cuenta</strong>. Si no aplica, déjelo en "No".', side: 'bottom', align: 'center' } },
                { element: '#rifEmpresa', popover: { title: '🏢 RIF del Empleador', description: 'Ingrese el <strong>RIF de la empresa</strong> donde el causante acumuló prestaciones (ej: J012345678).<br><br>La <strong>Razón Social</strong> se autocompletará al ingresar un RIF válido.', side: 'bottom', align: 'center' } }
            ],
            containerId: 'tablaContainerPrestaciones',
            tbodyId: 'tbodyPrestaciones',
            siguientePaso: { label: 'Caja de Ahorro', selector: '[data-panel="muebles"] a[href*="caja_ahorro"]' }
        });
    };

    // ── Caja de Ahorro ──
    TOURS.sucesiones_muebles_caja_ahorro = function () {
        return buildBienMuebleTour({
            titulo: '🏧 Caja de Ahorro',
            nombreModulo: 'Caja de Ahorro',
            descripcionIntro: 'Registre los <strong>fondos en cajas de ahorro empresariales</strong> del causante.',
            stepsEspecificos: [
                { element: '#rifEmpresa', popover: { title: '🏢 RIF de la Empresa', description: 'Ingrese el <strong>RIF de la empresa</strong> donde el causante tenía caja de ahorro (ej: J012345678).<br><br>La <strong>Razón Social</strong> se autocompletará al ingresar un RIF válido.', side: 'bottom', align: 'center' } }
            ],
            containerId: 'tablaContainerCajaAhorro',
            tbodyId: 'tbodyCajaAhorro',
            siguientePaso: { label: 'Plantaciones', selector: '[data-panel="muebles"] a[href*="plantaciones"]' }
        });
    };

    // ── Plantaciones ──
    TOURS.sucesiones_muebles_plantaciones = function () {
        return buildBienMuebleTour({
            titulo: '🌿 Plantaciones',
            nombreModulo: 'Plantaciones',
            descripcionIntro: 'Registre las <strong>plantaciones agrícolas y forestales</strong> del causante.',
            stepsEspecificos: [],
            containerId: 'tablaContainerPlantaciones',
            tbodyId: 'tbodyPlantaciones',
            siguientePaso: { label: 'Otros', selector: '[data-panel="muebles"] a[href*="/otros"]' }
        });
    };

    // ── Otros (último módulo → apunta a Pasivos Deuda) ──
    TOURS.sucesiones_muebles_otros = function () {
        return buildBienMuebleTour({
            titulo: '📦 Otros Bienes Muebles',
            nombreModulo: 'Otros',
            descripcionIntro: 'Registre los <strong>bienes muebles que no encajen</strong> en las categorías anteriores (joyas, obras de arte, mobiliario, etc.).',
            stepsEspecificos: [
                { element: '#codTipoBien', popover: { title: '📋 Tipo de Bien', description: 'Seleccione la <strong>categoría más cercana</strong> al tipo de bien mueble que desea registrar.', side: 'bottom', align: 'center' } }
            ],
            containerId: 'tablaContainerOtros',
            tbodyId: 'tbodyOtros',
            siguientePaso: { label: 'Pasivos Deuda', selector: 'button[data-section="pasivosDeuda"]' }
        });
    };


    // ─────────────────────────────────────────────────────────────────────
    // TOURS: Sucesión - Pasivos Deuda (4 módulos)
    //
    // buildPasivoDeudaTour(config) genera steps context-aware para cada
    // sub-página de Pasivos Deuda. Similar a muebles pero sin Bien Litigioso.
    // Campos comunes: Porcentaje, Descripción, Valor Declarado, Guardar.
    // ─────────────────────────────────────────────────────────────────────

    function buildPasivoDeudaTour(config) {
        var steps = [];
        var hayDatos = tieneRegistrosMueble(config.containerId, config.tbodyId);

        // ── Si hay registros, empezar con resumen de la tabla ──
        if (hayDatos) {
            var cantidad = contarRegistrosMueble(config.tbodyId);
            steps.push({
                element: '#' + config.containerId,
                popover: {
                    title: '✅ ' + config.nombreModulo + ' Registrado(s)',
                    description:
                        'Tiene <strong>' + cantidad + ' registro(s)</strong> de ' +
                        config.nombreModulo + '.<br><br>' +
                        'Puede <strong>editar</strong> con ✏️ o <strong>eliminar</strong> con 🗑️.<br>' +
                        'A continuación se explica cada campo del formulario.',
                    side: 'top',
                    align: 'center'
                }
            });
        } else {
            steps.push({
                popover: {
                    title: config.titulo,
                    description:
                        config.descripcionIntro + '<br><br>' +
                        'Por cada pasivo, complete el formulario y presione ' +
                        '<strong>Guardar</strong>. Puede registrar varios.',
                    side: 'over',
                    align: 'center'
                }
            });
        }

        // ── SIEMPRE: Steps específicos del módulo ──
        config.stepsEspecificos.forEach(function (step) {
            if (step.element && document.querySelector(step.element)) {
                steps.push(step);
            } else if (!step.element) {
                steps.push(step);
            }
        });

        // ── SIEMPRE: Porcentaje + Descripción ──
        if (document.getElementById('sporcentaje')) {
            steps.push({
                element: '#sporcentaje',
                popover: {
                    title: '📝 Porcentaje y Descripción',
                    description:
                        '<strong>Porcentaje %:</strong> Proporción de la deuda que corresponde al causante ' +
                        '(por defecto 0,01).<br><br>' +
                        '<strong>Descripción:</strong> En el campo de al lado, detalle el pasivo ' +
                        '(concepto de la deuda, condiciones, etc.).',
                    side: 'bottom',
                    align: 'center'
                }
            });
        }

        // ── SIEMPRE: Valor Declarado ──
        if (document.getElementById('ssc')) {
            steps.push({
                element: '#ssc',
                popover: {
                    title: '💰 Valor Declarado (Bs.)',
                    description:
                        'Ingrese el <strong>monto adeudado en bolívares</strong> al momento del fallecimiento.<br><br>' +
                        '⚠️ Los pasivos <strong>se restan</strong> del activo hereditario bruto para calcular el patrimonio neto.',
                    side: 'top',
                    align: 'center'
                }
            });
        }

        // ── SIEMPRE: Botón Guardar ──
        var btnSubmit = document.querySelector('button[type=submit]');
        if (btnSubmit) {
            steps.push({
                element: 'button[type=submit]',
                popover: {
                    title: '💾 Guardar',
                    description:
                        'Complete todos los campos obligatorios y el botón se habilitará.<br><br>' +
                        'El registro aparecerá en una <strong>tabla debajo</strong> del formulario ' +
                        'donde podrá editarlo o eliminarlo.',
                    side: 'top',
                    align: 'center'
                }
            });
        }

        // ── Si hay registros, cerrar con enlace al siguiente módulo ──
        if (hayDatos && config.siguientePaso && config.siguientePaso.selector) {
            var nextEl = document.querySelector(config.siguientePaso.selector);
            if (nextEl) {
                steps.push({
                    element: config.siguientePaso.selector,
                    popover: {
                        title: '⏭️ Próximo: ' + config.siguientePaso.label,
                        description:
                            'Si ya registró todos los pasivos de este tipo, continúe con ' +
                            '<strong>' + config.siguientePaso.label + '</strong>.<br><br>' +
                            '<strong>Haga clic aquí</strong> para avanzar.',
                        side: 'right',
                        align: 'center'
                    }
                });
            }
        }

        return steps;
    }

    // ── Tarjetas de Crédito ──
    TOURS.sucesiones_pasivos_tdc = function () {
        return buildPasivoDeudaTour({
            titulo: '💳 Tarjetas de Crédito',
            nombreModulo: 'Tarjetas de Crédito',
            descripcionIntro: 'Registre las <strong>deudas por tarjetas de crédito</strong> que tenía el causante al momento del fallecimiento.',
            stepsEspecificos: [
                { element: '#codTipoPasivo', popover: { title: '📋 Tipo de Pasivo', description: 'Indica la categoría del pasivo. En este módulo siempre será <strong>"Deudas"</strong>.', side: 'bottom', align: 'center' } },
                { element: '#codTipoDeuda', popover: { title: '📋 Tipo de Deuda', description: 'Indica el tipo específico de deuda. En este módulo siempre será <strong>"Tarjeta de Crédito"</strong>.', side: 'bottom', align: 'center' } },
                { element: '#vp', popover: { title: '🏦 Banco Emisor', description: 'Seleccione el <strong>banco emisor</strong> de la tarjeta de crédito.', side: 'bottom', align: 'center' } },
                { element: '#lind', popover: { title: '🔢 Número de TDC', description: 'Ingrese el <strong>número de la tarjeta de crédito</strong> (últimos dígitos o número completo según el documento).', side: 'bottom', align: 'center' } }
            ],
            containerId: 'tablaContainerTdc',
            tbodyId: 'tbodyTdc',
            siguientePaso: { label: 'Crédito Hipotecario', selector: '[data-panel="pasivosDeuda"] a[href*="credito_hipotecario"]' }
        });
    };

    // ── Crédito Hipotecario ──
    TOURS.sucesiones_pasivos_ch = function () {
        return buildPasivoDeudaTour({
            titulo: '🏠 Crédito Hipotecario',
            nombreModulo: 'Crédito Hipotecario',
            descripcionIntro: 'Registre los <strong>créditos hipotecarios</strong> que el causante mantenía pendientes al momento del fallecimiento.',
            stepsEspecificos: [
                { element: '#codTipoPasivo', popover: { title: '📋 Tipo de Pasivo', description: 'Indica la categoría del pasivo. En este módulo siempre será <strong>"Deudas"</strong>.', side: 'bottom', align: 'center' } },
                { element: '#codTipoDeuda', popover: { title: '📋 Tipo de Deuda', description: 'Indica el tipo específico de deuda. En este módulo siempre será <strong>"Crédito Hipotecario"</strong>.', side: 'bottom', align: 'center' } },
                { element: '#vp', popover: { title: '🏦 Banco Acreedor', description: 'Seleccione el <strong>banco</strong> que otorgó el crédito hipotecario al causante.', side: 'bottom', align: 'center' } }
            ],
            containerId: 'tablaContainerCh',
            tbodyId: 'tbodyCh',
            siguientePaso: { label: 'Préstamos', selector: '[data-panel="pasivosDeuda"] a[href*="prestamos"]' }
        });
    };

    // ── Préstamos, Cuentas y Efectos por Pagar ──
    TOURS.sucesiones_pasivos_prestamos = function () {
        return buildPasivoDeudaTour({
            titulo: '📋 Préstamos y Efectos por Pagar',
            nombreModulo: 'Préstamos',
            descripcionIntro: 'Registre los <strong>préstamos bancarios, cuentas y efectos por pagar</strong> del causante al momento del fallecimiento.',
            stepsEspecificos: [
                { element: '#codTipoPasivo', popover: { title: '📋 Tipo de Pasivo', description: 'Indica la categoría del pasivo. En este módulo siempre será <strong>"Deudas"</strong>.', side: 'bottom', align: 'center' } },
                { element: '#codTipoDeuda', popover: { title: '📋 Tipo de Deuda', description: 'Indica el tipo específico: <strong>préstamo personal, pagaré, letra de cambio</strong>, etc.', side: 'bottom', align: 'center' } },
                { element: '#vp', popover: { title: '🏦 Banco Acreedor', description: 'Seleccione el <strong>banco o entidad financiera</strong> que otorgó el préstamo.', side: 'bottom', align: 'center' } }
            ],
            containerId: 'tablaContainerPce',
            tbodyId: 'tbodyPce',
            siguientePaso: { label: 'Otros Pasivos', selector: '[data-panel="pasivosDeuda"] a[href*="/otros"]' }
        });
    };

    // ── Otros Pasivos (último → apunta a Pasivos Gastos) ──
    TOURS.sucesiones_pasivos_otros = function () {
        return buildPasivoDeudaTour({
            titulo: '📦 Otros Pasivos',
            nombreModulo: 'Otros Pasivos',
            descripcionIntro: 'Registre <strong>cualquier otra deuda</strong> del causante que no entre en las categorías anteriores (deudas personales, obligaciones pendientes, etc.).',
            stepsEspecificos: [
                { element: '#codTipoPasivo', popover: { title: '📋 Tipo de Pasivo', description: 'Seleccione la <strong>categoría</strong> del pasivo.', side: 'bottom', align: 'center' } },
                { element: '#codTipoDeuda', popover: { title: '📋 Tipo de Deuda', description: 'Seleccione el <strong>tipo específico de deuda</strong> que corresponda.', side: 'bottom', align: 'center' } }
            ],
            containerId: 'tablaContainerOtros',
            tbodyId: 'tbodyOtros',
            siguientePaso: { label: 'Pasivos Gastos', selector: 'button[data-section="pasivosGastos"]' }
        });
    };


    // ─────────────────────────────────────────────────────────────────────
    // TOUR: Sucesión - Pasivos Gastos (1 módulo)
    // ─────────────────────────────────────────────────────────────────────

    TOURS.sucesiones_pasivos_gastos = function () {
        var steps = [];
        var hayDatos = tieneRegistrosMueble('tablaContainerGastos', 'tbodyGastos');

        // ── Si hay registros, empezar con resumen ──
        if (hayDatos) {
            var cantidad = contarRegistrosMueble('tbodyGastos');
            steps.push({
                element: '#tablaContainerGastos',
                popover: {
                    title: '✅ Gastos Registrado(s)',
                    description:
                        'Tiene <strong>' + cantidad + ' gasto(s)</strong> registrado(s).<br><br>' +
                        'Puede <strong>editar</strong> con ✏️ o <strong>eliminar</strong> con 🗑️.<br>' +
                        'A continuación se explica cada campo del formulario.',
                    side: 'top',
                    align: 'center'
                }
            });
        } else {
            steps.push({
                popover: {
                    title: '💸 Pasivos — Gastos',
                    description:
                        'Registre los <strong>gastos relacionados con la sucesión</strong>: ' +
                        'apertura de testamento, última enfermedad, funerarios, etc.<br><br>' +
                        'Por cada gasto, complete el formulario y presione <strong>Guardar</strong>.',
                    side: 'over',
                    align: 'center'
                }
            });
        }

        // ── SIEMPRE: Steps de campos ──
        if (document.getElementById('codTipoPasivo')) {
            steps.push({
                element: '#codTipoPasivo',
                popover: {
                    title: '📋 Tipo de Pasivo',
                    description: 'Indica la categoría del pasivo. En este módulo siempre será <strong>"Gastos"</strong>.',
                    side: 'bottom',
                    align: 'center'
                }
            });
        }

        if (document.getElementById('codTipoGasto')) {
            steps.push({
                element: '#codTipoGasto',
                popover: {
                    title: '📋 Tipo de Gasto',
                    description:
                        'Seleccione el <strong>tipo de gasto</strong> que desea registrar:<br><br>' +
                        '• Apertura de Testamento<br>' +
                        '• Última Enfermedad<br>' +
                        '• Funerarios<br>' +
                        '• Otros gastos sucesorales',
                    side: 'bottom',
                    align: 'center'
                }
            });
        }

        if (document.getElementById('sporcentaje')) {
            steps.push({
                element: '#sporcentaje',
                popover: {
                    title: '📝 Porcentaje y Descripción',
                    description:
                        '<strong>Porcentaje %:</strong> Proporción del gasto que corresponde a esta declaración ' +
                        '(por defecto 0,01).<br><br>' +
                        '<strong>Descripción:</strong> Detalle en el campo de al lado el concepto del gasto y las circunstancias.',
                    side: 'bottom',
                    align: 'center'
                }
            });
        }

        if (document.getElementById('ssc')) {
            steps.push({
                element: '#ssc',
                popover: {
                    title: '💰 Valor Declarado (Bs.)',
                    description:
                        'Ingrese el <strong>monto del gasto en bolívares</strong>.<br><br>' +
                        '⚠️ Los gastos sucesorales <strong>se restan</strong> del activo hereditario bruto.',
                    side: 'top',
                    align: 'center'
                }
            });
        }

        var btnSubmit = document.querySelector('button[type=submit]');
        if (btnSubmit) {
            steps.push({
                element: 'button[type=submit]',
                popover: {
                    title: '💾 Guardar',
                    description:
                        'Complete todos los campos obligatorios y el botón se habilitará.<br><br>' +
                        'El registro aparecerá en una <strong>tabla debajo</strong> del formulario.',
                    side: 'top',
                    align: 'center'
                }
            });
        }

        // ── Si hay registros, cerrar con enlace al siguiente ──
        if (hayDatos) {
            var nextEl = document.querySelector('button[data-section="desgravamenes"]');
            if (nextEl) {
                steps.push({
                    element: 'button[data-section="desgravamenes"]',
                    popover: {
                        title: '⏭️ Próximo: Desgravámenes',
                        description:
                            'Si ya registró todos los gastos sucesorales, continúe con ' +
                            '<strong>Desgravámenes</strong>.<br><br>' +
                            '<strong>Haga clic aquí</strong> para avanzar.',
                        side: 'right',
                        align: 'center'
                    }
                });
            }
        }

        return steps;
    };


    // ─────────────────────────────────────────────────────────────────────
    // TOUR: Sucesión - Desgravámenes (vista informativa, sin CRUD)
    //
    // Esta página NO tiene formulario. Los datos se auto-calculan de:
    // 1. Bienes Inmuebles marcados como Vivienda Principal
    // 2. Seguros tipo Montepío (09) y Seguro de Vida (08)
    // 3. Prestaciones Sociales sin cuenta bancaria
    // ─────────────────────────────────────────────────────────────────────

    TOURS.sucesiones_desgravamenes = function () {
        var steps = [];

        // Detectar si hay tabla con datos (server-rendered)
        var tabla = document.querySelector('.card-body table');
        var hayDatos = tabla && tabla.querySelector('tbody tr');

        if (hayDatos) {
            // ── Con datos: explicar la tabla ──
            steps.push({
                element: '.card-body table',
                popover: {
                    title: '📊 Desgravámenes Calculados',
                    description:
                        'Esta tabla muestra los <strong>desgravámenes</strong> que se calcularon ' +
                        'automáticamente a partir de los bienes que usted ya registró.<br><br>' +
                        '⚠️ <strong>No puede editar esta tabla directamente.</strong> ' +
                        'Los datos provienen de otras secciones de la declaración.',
                    side: 'top',
                    align: 'center'
                }
            });

            steps.push({
                element: '.card-body table thead',
                popover: {
                    title: '📋 ¿De dónde salen estos datos?',
                    description:
                        'Los desgravámenes se generan automáticamente de <strong>3 fuentes</strong>:<br><br>' +
                        '1️⃣ <strong>Vivienda Principal:</strong> Inmuebles marcados como vivienda principal en Bienes Inmuebles.<br><br>' +
                        '2️⃣ <strong>Montepío / Seguro de Vida:</strong> Pólizas tipo Montepío o Seguro de Vida registradas en Bienes Muebles → Seguro.<br><br>' +
                        '3️⃣ <strong>Prestaciones Sociales:</strong> Prestaciones sin cuenta bancaria registradas en Bienes Muebles → Prestaciones.',
                    side: 'bottom',
                    align: 'center'
                }
            });

            steps.push({
                popover: {
                    title: '💡 ¿Cómo modificar los desgravámenes?',
                    description:
                        'Para <strong>agregar o quitar</strong> un desgravamen, debe modificar el bien original:<br><br>' +
                        '• Para agregar vivienda principal → edite el inmueble y marque "Vivienda Principal: Sí".<br>' +
                        '• Para agregar seguro de vida → registre un seguro tipo "Seguro de Vida" o "Montepío".<br>' +
                        '• Para agregar prestaciones → registre prestaciones sociales sin cuenta bancaria.<br><br>' +
                        'Al volver a esta página, los desgravámenes se actualizarán automáticamente.',
                    side: 'over',
                    align: 'center'
                }
            });
        } else {
            // ── Sin datos: explicar qué son y cómo aparecerán ──
            steps.push({
                popover: {
                    title: '📖 Desgravámenes',
                    description:
                        'Los <strong>desgravámenes</strong> son deducciones legales que se restan ' +
                        'del activo hereditario neto para reducir la base imponible.<br><br>' +
                        'Esta sección es <strong>informativa</strong> — los datos se calculan ' +
                        'automáticamente, no requiere llenar ningún formulario.',
                    side: 'over',
                    align: 'center'
                }
            });

            steps.push({
                popover: {
                    title: '📋 ¿Cómo aparecen los desgravámenes?',
                    description:
                        'Se generan automáticamente de <strong>3 fuentes</strong>:<br><br>' +
                        '1️⃣ <strong>Vivienda Principal:</strong> Si marcó un inmueble como vivienda principal en Bienes Inmuebles.<br><br>' +
                        '2️⃣ <strong>Montepío / Seguro de Vida:</strong> Si registró pólizas tipo Montepío o Seguro de Vida en Bienes Muebles → Seguro.<br><br>' +
                        '3️⃣ <strong>Prestaciones Sociales:</strong> Si registró prestaciones sin cuenta bancaria en Bienes Muebles → Prestaciones.<br><br>' +
                        'Actualmente no tiene ningún desgravamen calculado.',
                    side: 'over',
                    align: 'center'
                }
            });
        }

        // ── Siempre: enlace al siguiente paso ──
        var nextEl = document.querySelector('button[data-section="exenciones"]');
        if (nextEl) {
            steps.push({
                element: 'button[data-section="exenciones"]',
                popover: {
                    title: '⏭️ Próximo: Exenciones',
                    description:
                        'Continúe con <strong>Exenciones</strong> para registrar los ' +
                        'beneficios fiscales que apliquen a esta declaración.<br><br>' +
                        '<strong>Haga clic aquí</strong> para avanzar.',
                    side: 'right',
                    align: 'center'
                }
            });
        }

        return steps;
    };


    // ─────────────────────────────────────────────────────────────────────
    // TOURS: Sucesión - Exenciones y Exoneraciones
    // ─────────────────────────────────────────────────────────────────────

    TOURS.sucesiones_exenciones = function () {
        var steps = [];
        var hayDatos = tieneRegistrosMueble('tablaContainerExenciones', 'tbodyExenciones');

        // ── Si hay registros, empezar con resumen ──
        if (hayDatos) {
            var cantidad = contarRegistrosMueble('tbodyExenciones');
            steps.push({
                element: '#tablaContainerExenciones',
                popover: {
                    title: '✅ Exenciones Registrada(s)',
                    description:
                        'Tiene <strong>' + cantidad + ' exención(es)</strong> registrada(s).<br><br>' +
                        'Puede <strong>editar</strong> con ✏️ o <strong>eliminar</strong> con 🗑️.<br>' +
                        'A continuación se explica cada campo del formulario.',
                    side: 'top',
                    align: 'center'
                }
            });
        } else {
            steps.push({
                popover: {
                    title: '🛡️ Exenciones',
                    description:
                        'Las <strong>exenciones</strong> son beneficios fiscales establecidos por ley que ' +
                        'excluyen ciertos bienes del cálculo del impuesto sucesoral.<br><br>' +
                        'Ejemplo: vivienda principal, bienes de uso personal, etc.<br><br>' +
                        'Por cada exención, complete el formulario y presione <strong>Guardar</strong>.',
                    side: 'over',
                    align: 'center'
                }
            });
        }

        // ── SIEMPRE: Steps de campos ──
        if (document.getElementById('exTipo')) {
            steps.push({
                element: '#exTipo',
                popover: {
                    title: '📋 Tipo de Exención',
                    description:
                        'Indique el <strong>tipo o categoría</strong> de la exención.<br><br>' +
                        'Ejemplo: "Vivienda Principal", "Bienes de Uso Personal", "Ajuar del Hogar".',
                    side: 'bottom',
                    align: 'center'
                }
            });
        }

        if (document.getElementById('exDescripcion')) {
            steps.push({
                element: '#exDescripcion',
                popover: {
                    title: '📝 Descripción',
                    description:
                        'Detalle la <strong>exención</strong>: qué bien se exenta, ' +
                        'fundamento legal y cualquier información relevante.',
                    side: 'bottom',
                    align: 'center'
                }
            });
        }

        if (document.getElementById('exValorDeclarado')) {
            steps.push({
                element: '#exValorDeclarado',
                popover: {
                    title: '💰 Valor Declarado (Bs.)',
                    description:
                        'Ingrese el <strong>monto en bolívares</strong> del bien exento.<br><br>' +
                        '⚠️ Las exenciones <strong>se restan</strong> del activo hereditario bruto.',
                    side: 'top',
                    align: 'center'
                }
            });
        }

        var btnSubmit = document.querySelector('button[type=submit]');
        if (btnSubmit) {
            steps.push({
                element: 'button[type=submit]',
                popover: {
                    title: '💾 Guardar',
                    description:
                        'Complete todos los campos obligatorios y el botón se habilitará.<br><br>' +
                        'El registro aparecerá en una <strong>tabla debajo</strong> del formulario.',
                    side: 'top',
                    align: 'center'
                }
            });
        }

        // ── Si hay registros, cerrar con enlace al siguiente ──
        if (hayDatos) {
            var nextEl = document.querySelector('button[data-section="exoneraciones"]');
            if (nextEl) {
                steps.push({
                    element: 'button[data-section="exoneraciones"]',
                    popover: {
                        title: '⏭️ Próximo: Exoneraciones',
                        description:
                            'Si ya registró todas las exenciones, continúe con ' +
                            '<strong>Exoneraciones</strong>.<br><br>' +
                            '<strong>Haga clic aquí</strong> para avanzar.',
                        side: 'right',
                        align: 'center'
                    }
                });
            }
        }

        return steps;
    };

    TOURS.sucesiones_exoneraciones = function () {
        var steps = [];
        var hayDatos = tieneRegistrosMueble('tablaContainerExoneraciones', 'tbodyExoneraciones');

        // ── Si hay registros, empezar con resumen ──
        if (hayDatos) {
            var cantidad = contarRegistrosMueble('tbodyExoneraciones');
            steps.push({
                element: '#tablaContainerExoneraciones',
                popover: {
                    title: '✅ Exoneraciones Registrada(s)',
                    description:
                        'Tiene <strong>' + cantidad + ' exoneración(es)</strong> registrada(s).<br><br>' +
                        'Puede <strong>editar</strong> con ✏️ o <strong>eliminar</strong> con 🗑️.<br>' +
                        'A continuación se explica cada campo del formulario.',
                    side: 'top',
                    align: 'center'
                }
            });
        } else {
            steps.push({
                popover: {
                    title: '📜 Exoneraciones',
                    description:
                        'Las <strong>exoneraciones</strong> son beneficios otorgados por el Ejecutivo Nacional ' +
                        'mediante decreto, que liberan temporalmente del pago del impuesto.<br><br>' +
                        'A diferencia de las exenciones (por ley), las exoneraciones se conceden ' +
                        'por <strong>decreto presidencial</strong>.<br><br>' +
                        'Por cada exoneración, complete el formulario y presione <strong>Guardar</strong>.',
                    side: 'over',
                    align: 'center'
                }
            });
        }

        // ── SIEMPRE: Steps de campos ──
        if (document.getElementById('exoTipo')) {
            steps.push({
                element: '#exoTipo',
                popover: {
                    title: '📋 Tipo de Exoneración',
                    description:
                        'Indique el <strong>tipo o categoría</strong> de la exoneración.<br><br>' +
                        'Ejemplo: decreto gubernamental específico, programa social, etc.',
                    side: 'bottom',
                    align: 'center'
                }
            });
        }

        if (document.getElementById('exoDescripcion')) {
            steps.push({
                element: '#exoDescripcion',
                popover: {
                    title: '📝 Descripción',
                    description:
                        'Detalle la <strong>exoneración</strong>: número de decreto, ' +
                        'Gaceta Oficial, bienes amparados y cualquier información relevante.',
                    side: 'bottom',
                    align: 'center'
                }
            });
        }

        if (document.getElementById('exoValorDeclarado')) {
            steps.push({
                element: '#exoValorDeclarado',
                popover: {
                    title: '💰 Valor Declarado (Bs.)',
                    description:
                        'Ingrese el <strong>monto en bolívares</strong> del bien exonerado.<br><br>' +
                        '⚠️ Las exoneraciones <strong>se restan</strong> del activo hereditario bruto.',
                    side: 'top',
                    align: 'center'
                }
            });
        }

        var btnSubmit = document.querySelector('button[type=submit]');
        if (btnSubmit) {
            steps.push({
                element: 'button[type=submit]',
                popover: {
                    title: '💾 Guardar',
                    description:
                        'Complete todos los campos obligatorios y el botón se habilitará.<br><br>' +
                        'El registro aparecerá en una <strong>tabla debajo</strong> del formulario.',
                    side: 'top',
                    align: 'center'
                }
            });
        }

        // ── Si hay registros, cerrar con enlace al siguiente ──
        if (hayDatos) {
            var nextEl = document.querySelector('button[data-section="litigiosos"]');
            if (nextEl) {
                steps.push({
                    element: 'button[data-section="litigiosos"]',
                    popover: {
                        title: '⏭️ Próximo: Bienes Litigiosos',
                        description:
                            'Si ya registró todas las exoneraciones, continúe con ' +
                            '<strong>Bienes Litigiosos</strong>.<br><br>' +
                            '<strong>Haga clic aquí</strong> para avanzar.',
                        side: 'right',
                        align: 'center'
                    }
                });
            }
        }

        return steps;
    };


    // ─────────────────────────────────────────────────────────────────────
    // TOUR: Sucesión - Bienes Litigiosos (vista informativa, sin CRUD)
    //
    // Escanea TODAS las secciones del borrador buscando items con
    // bien_litigioso === 'true'. Muestra tabla + modal con datos del tribunal.
    // ─────────────────────────────────────────────────────────────────────

    TOURS.sucesiones_bienes_litigiosos = function () {
        var steps = [];

        // Detectar si hay datos (JS-rendered tbody)
        var tbody = document.getElementById('tbodyLitigiosos');
        var hayDatos = tbody && tbody.querySelectorAll('tr').length > 1; // >1 porque siempre hay fila total

        if (hayDatos) {
            // ── Con datos ──
            steps.push({
                element: '.alert-info',
                popover: {
                    title: '⚖️ Bienes Litigiosos',
                    description:
                        'Esta sección reúne <strong>todos los bienes que usted marcó como litigiosos</strong> ' +
                        'en las diferentes secciones de la declaración (Inmuebles, Muebles, etc.).<br><br>' +
                        '⚠️ <strong>No puede editar esta tabla directamente.</strong> ' +
                        'Para agregar o quitar un bien litigioso, vaya a la sección original y cambie la opción "Bien Litigioso".',
                    side: 'bottom',
                    align: 'center'
                }
            });

            steps.push({
                element: '.card-body table',
                popover: {
                    title: '📋 Tabla de Bienes en Disputa',
                    description:
                        'Aquí se listan todos los bienes en <strong>disputa legal</strong>:<br><br>' +
                        '• <strong>Tipo de Bien:</strong> Categoría del bien (Banco, Seguro, Inmueble, etc.)<br>' +
                        '• <strong>Descripción:</strong> Detalle del bien + datos del tribunal<br>' +
                        '• <strong>Valor Declarado:</strong> Monto en bolívares<br>' +
                        '• <strong>Acción:</strong> Botón 📋 para ver datos del tribunal',
                    side: 'top',
                    align: 'center'
                }
            });

            steps.push({
                element: '.accionesicono',
                popover: {
                    title: '📋 Ver Datos del Tribunal',
                    description:
                        'Haga clic en este ícono para abrir un <strong>modal</strong> con los datos del tribunal:<br><br>' +
                        '• Número de Expediente<br>' +
                        '• Tribunal de la Causa<br>' +
                        '• Partes en el Juicio<br>' +
                        '• Estado del Juicio<br><br>' +
                        'Estos datos fueron registrados cuando marcó el bien como litigioso.',
                    side: 'left',
                    align: 'center'
                }
            });
        } else {
            // ── Sin datos ──
            steps.push({
                popover: {
                    title: '⚖️ Bienes Litigiosos',
                    description:
                        'Esta sección muestra los <strong>bienes en disputa legal</strong> del causante.<br><br>' +
                        'Actualmente <strong>no tiene bienes marcados como litigiosos</strong>.<br><br>' +
                        'Los bienes litigiosos se agregan desde las secciones de Bienes Inmuebles y Bienes Muebles, ' +
                        'seleccionando <strong>"Bien Litigioso: Sí"</strong> en el formulario del bien correspondiente.',
                    side: 'over',
                    align: 'center'
                }
            });

            steps.push({
                popover: {
                    title: '📋 ¿Qué información se requiere?',
                    description:
                        'Al marcar un bien como litigioso, se solicitan datos adicionales:<br><br>' +
                        '• <strong>Número de Expediente:</strong> Identificador del caso<br>' +
                        '• <strong>Tribunal de la Causa:</strong> Tribunal que lleva el caso<br>' +
                        '• <strong>Partes en el Juicio:</strong> Involucrados en la disputa<br>' +
                        '• <strong>Estado del Juicio:</strong> En curso, sentencia, etc.<br><br>' +
                        'Esos datos se pueden consultar aquí mediante el ícono 📋.',
                    side: 'over',
                    align: 'center'
                }
            });
        }

        // ── Siempre: enlace al siguiente paso ──
        var nextEl = document.querySelector('button[data-section="resumen"]');
        if (nextEl) {
            steps.push({
                element: 'button[data-section="resumen"]',
                popover: {
                    title: '⏭️ Próximo: Resumen Declaración',
                    description:
                        'Continúe con <strong>Resumen Declaración</strong> para revisar ' +
                        'el consolidado de toda la declaración sucesoral.<br><br>' +
                        '<strong>Haga clic aquí</strong> para avanzar.',
                    side: 'right',
                    align: 'center'
                }
            });
        }

        return steps;
    };


    // ─────────────────────────────────────────────────────────────────────
    // TOUR: Sucesión - Declaración Anverso (FORMA DS-99032)
    //
    // Planilla final de la declaración sucesoral. Vista de solo lectura
    // con secciones A-I + botón Declarar que dispara 3 modales.
    // IDs inyectados: #navAnverso, #seccionAE, #seccionF, #seccionG,
    //                 #seccionH, #seccionI, #btnDeclararAnverso
    // ─────────────────────────────────────────────────────────────────────

    TOURS.sucesiones_declaracion_anverso = function () {
        var steps = [];

        // ── Step 1: Botón Declarar (arriba) ──
        if (document.getElementById('btnDeclararAnverso')) {
            steps.push({
                element: '#btnDeclararAnverso',
                popover: {
                    title: '✅ Botón Declarar',
                    description:
                        'Este botón inicia el <strong>proceso de declaración</strong>.<br><br>' +
                        '<strong>En el SENIAT real:</strong><br>' +
                        '• La declaración es <strong>irreversible</strong> — una vez enviada no puede modificarse.<br>' +
                        '• Se genera la planilla oficial con número de trámite.<br>' +
                        '• El contribuyente debe pagar el impuesto según el monto calculado.<br><br>' +
                        '<strong>En esta simulación:</strong><br>' +
                        '• Al presionar "Declarar" se abrirá una ventana mostrando el monto a pagar.<br>' +
                        '• Luego se le pedirá confirmación definitiva.<br>' +
                        '• La simulación se finalizará y se generarán los documentos PDF.<br>' +
                        '• Los resultados serán enviados a su profesor para revisión.<br><br>' +
                        '⚠️ <strong>Asegúrese de haber revisado toda la información antes de declarar.</strong>',
                    side: 'bottom',
                    align: 'center'
                }
            });
        }

        // ── Step 2: Navegación ──
        if (document.getElementById('navAnverso')) {
            steps.push({
                element: '#navAnverso',
                popover: {
                    title: '🧭 Navegación de la Planilla',
                    description:
                        'Esta es la <strong>FORMA DS-99032</strong> del SENIAT — la planilla oficial de la Declaración Sucesoral.<br><br>' +
                        'La planilla tiene dos caras:<br>' +
                        '• <strong>Anverso</strong> (esta página): Secciones A–I con datos generales, herederos y autoliquidación.<br>' +
                        '• <strong>Reverso</strong>: Anexos detallados de todos los bienes, pasivos y deducciones.<br><br>' +
                        'A continuación recorreremos cada sección.',
                    side: 'bottom',
                    align: 'center'
                }
            });
        }

        // ── Step 3: Secciones A–E ──
        if (document.getElementById('seccionAE')) {
            steps.push({
                element: '#seccionAE',
                popover: {
                    title: '📋 Secciones A–E: Datos Generales',
                    description:
                        '<strong>A — Datos del Contribuyente:</strong> Nombre de la sucesión y RIF sucesoral.<br><br>' +
                        '<strong>Fechas:</strong> Fecha de declaración y vencimiento (180 días hábiles + prórrogas).<br><br>' +
                        '<strong>B — Causante:</strong> Nombre, RIF y cédula del fallecido.<br><br>' +
                        '<strong>C — Dirección:</strong> Domicilio fiscal y fecha de fallecimiento.<br><br>' +
                        '<strong>D — Representante Legal:</strong> Quien presenta la declaración.<br><br>' +
                        '<strong>E — Tipo de Herencia:</strong> Ab-intestato, testamentaria o ambas.',
                    side: 'right',
                    align: 'start'
                }
            });
        }

        // ── Step 4: Sección F ──
        if (document.getElementById('seccionF')) {
            steps.push({
                element: '#seccionF',
                popover: {
                    title: '📂 F — Datos de la Prórroga',
                    description:
                        'Si se solicitó <strong>prórroga</strong>, aquí aparecen:<br><br>' +
                        '• <strong>Fecha Solicitud</strong><br>' +
                        '• <strong>Nro. Resolución</strong><br>' +
                        '• <strong>Fecha Resolución</strong><br>' +
                        '• <strong>Plazo Otorgado</strong> (días)<br>' +
                        '• <strong>Fecha Vencimiento</strong><br><br>' +
                        'Si no se solicitó, indica "Sin prórrogas registradas".',
                    side: 'top',
                    align: 'center'
                }
            });
        }

        // ── Step 5: Sección G ──
        if (document.getElementById('seccionG')) {
            steps.push({
                element: '#seccionG',
                popover: {
                    title: '👨‍👩‍👧‍👦 G — Herederos',
                    description:
                        'Para <strong>cada heredero</strong> se calcula:<br><br>' +
                        '• <strong>Cuota Parte Hereditaria</strong><br>' +
                        '• <strong>Porcentaje/Tarifa</strong> (Art. 7)<br>' +
                        '• <strong>Sustraendo</strong><br>' +
                        '• <strong>Impuesto Determinado</strong> = Cuota × Tarifa − Sustraendo<br>' +
                        '• <strong>Reducción</strong> por parentesco<br>' +
                        '• <strong>Impuesto a Pagar</strong> = Impuesto − Reducción',
                    side: 'top',
                    align: 'center'
                }
            });
        }

        // ── Step 6: Sección H ──
        if (document.getElementById('seccionH')) {
            steps.push({
                element: '#seccionH',
                popover: {
                    title: '⚰️ H — Herederos Premuertos',
                    description:
                        'Si algún heredero <strong>falleció antes que el causante</strong>, su porción pasa a sus propios herederos (derecho de representación).<br><br>' +
                        'Si no hay premuertos, aparece "No hay herederos premuertos".',
                    side: 'top',
                    align: 'center'
                }
            });
        }

        // ── Step 7: Sección I - Intro ──
        if (document.getElementById('seccionI')) {
            steps.push({
                element: '#seccionI thead',
                popover: {
                    title: '📊 I — Autoliquidación del Impuesto',
                    description:
                        'Sección más importante: el <strong>cálculo del impuesto sucesoral</strong>.<br><br>' +
                        '14 líneas en 4 bloques:<br>' +
                        '1️⃣ <strong>Patrimonio Bruto</strong> (líneas 1–4)<br>' +
                        '2️⃣ <strong>Exclusiones</strong> (líneas 5–8)<br>' +
                        '3️⃣ <strong>Patrimonio Neto</strong> (líneas 9–11)<br>' +
                        '4️⃣ <strong>Determinación del Tributo</strong> (líneas 12–14)',
                    side: 'bottom',
                    align: 'center'
                }
            });
        }

        // ── Step 8: Líneas 1-4 ──
        if (document.getElementById('grupoPatrimonioBruto')) {
            steps.push({
                element: '#grupoPatrimonioBruto',
                popover: {
                    title: '🏠 Líneas 1–4: Patrimonio Bruto',
                    description:
                        '<strong>Línea 1:</strong> Total Bienes Inmuebles<br>' +
                        '<strong>Línea 2:</strong> Total Bienes Muebles<br>' +
                        '<strong>Línea 3:</strong> Patrimonio Hereditario Bruto = Línea 1 + 2<br>' +
                        '<strong>Línea 4:</strong> Activo Hereditario Bruto<br><br>' +
                        '⚠️ Bienes litigiosos y vivienda principal se detallan en el <strong>Reverso</strong>.',
                    side: 'left',
                    align: 'center'
                }
            });
        }

        // ── Step 9: Líneas 5-8 ──
        if (document.getElementById('grupoExclusiones')) {
            steps.push({
                element: '#grupoExclusiones',
                popover: {
                    title: '🛡️ Líneas 5–8: Exclusiones',
                    description:
                        '<strong>Línea 5:</strong> Desgravámenes<br>' +
                        '<strong>Línea 6:</strong> Exenciones<br>' +
                        '<strong>Línea 7:</strong> Exoneraciones<br>' +
                        '<strong>Línea 8:</strong> Total Exclusiones = 5 + 6 + 7<br><br>' +
                        'Se <strong>restan</strong> del activo bruto.',
                    side: 'left',
                    align: 'center'
                }
            });
        }

        // ── Step 10: Líneas 9-11 ──
        if (document.getElementById('grupoPatrimonioNeto')) {
            steps.push({
                element: '#grupoPatrimonioNeto',
                popover: {
                    title: '📉 Líneas 9–11: Patrimonio Neto',
                    description:
                        '<strong>Línea 9:</strong> Activo Neto = Bruto − Exclusiones<br>' +
                        '<strong>Línea 10:</strong> Total Pasivo = deudas + gastos<br>' +
                        '<strong>Línea 11:</strong> <strong>Líquido Gravable</strong> = Neto − Pasivo<br><br>' +
                        'Línea 11 es la <strong>base imponible</strong>.',
                    side: 'left',
                    align: 'center'
                }
            });
        }

        // ── Step 11: Líneas 12-14 (ÚLTIMO PASO) ──
        if (document.getElementById('grupoTributo')) {
            steps.push({
                element: '#grupoTributo',
                popover: {
                    title: '💰 Líneas 12–14: Determinación del Tributo',
                    description:
                        '<strong>Línea 12:</strong> Impuesto Determinado — según tarifa Art. 7<br>' +
                        '<strong>Línea 13:</strong> Reducciones — beneficios por parentesco<br>' +
                        '<strong>Línea 14:</strong> <strong>TOTAL IMPUESTO A PAGAR</strong> = 12 − 13<br><br>' +
                        '⚠️ Monto final que el contribuyente debe cancelar al SENIAT.',
                    side: 'left',
                    align: 'center'
                }
            });
        }

        return steps;
    };


    // ─────────────────────────────────────────────────────────────────────
    // TOUR: Sucesión - Resumen de la Declaración
    //
    // 4 tablas: Datos Generales, Patrimonio/Tributo (14 líneas),
    //           Cuota Parte Hereditaria, Tarifa Art. 7
    // IDs: #resumenDatosGenerales, #resumenPatrimonioTributo,
    //      #resumenGrupoBruto, #resumenGrupoExclusiones,
    //      #resumenGrupoNeto, #resumenGrupoTributo,
    //      #resumenCalculoManual, #resumenTablaHerederos,
    //      #resumenTarifaReferencia
    // ─────────────────────────────────────────────────────────────────────

    TOURS.sucesiones_resumen_declaracion = function () {
        var steps = [];

        // ── Step 1: Datos Generales (UT + Herederos) ──
        if (document.getElementById('resumenDatosGenerales')) {
            steps.push({
                element: '#resumenDatosGenerales',
                popover: {
                    title: '📊 Datos Generales',
                    description:
                        'Información base para todos los cálculos:<br><br>' +
                        '<strong>Unidad Tributaria (UT):</strong> Valor de referencia publicado por el SENIAT. ' +
                        'Se usa para convertir montos de bolívares a UT y viceversa, determinar tramos de tarifa ' +
                        'y calcular sustraendos.<br><br>' +
                        '<strong>Total de Herederos:</strong> Cantidad de herederos registrados. El patrimonio ' +
                        'se divide en partes iguales entre ellos para calcular la <em>Cuota Parte Hereditaria</em> de cada uno.',
                    side: 'bottom',
                    align: 'center'
                }
            });
        }

        // ── Step 2: Visión general tabla Patrimonio/Tributo ──
        if (document.getElementById('resumenPatrimonioTributo')) {
            steps.push({
                element: '#resumenPatrimonioTributo thead, #resumenPatrimonioTributo > tbody:first-of-type',
                popover: {
                    title: '📋 Resumen del Cálculo',
                    description:
                        'Esta tabla resume el cálculo completo del impuesto sucesoral en <strong>14 líneas</strong>:<br><br>' +
                        '1️⃣ <strong>Líneas 1–4:</strong> Patrimonio Bruto (inmuebles + muebles)<br>' +
                        '2️⃣ <strong>Líneas 5–8:</strong> Exclusiones (desgravámenes, exenciones, exoneraciones)<br>' +
                        '3️⃣ <strong>Líneas 9–11:</strong> Patrimonio Neto (bruto − exclusiones − pasivos)<br>' +
                        '4️⃣ <strong>Líneas 12–14:</strong> Determinación del Tributo<br><br>' +
                        'A continuación se explica cada bloque.',
                    side: 'bottom',
                    align: 'center'
                }
            });
        }

        // ── Step 3: Patrimonio Bruto (líneas 1-4) ──
        if (document.getElementById('resumenGrupoBruto')) {
            steps.push({
                element: '#resumenGrupoBruto',
                popover: {
                    title: '🏠 Líneas 1–4: Patrimonio Bruto',
                    description:
                        '<strong>Línea 1 — Total Bienes Inmuebles:</strong> Suma de todos los inmuebles registrados (casas, terrenos, apartamentos).<br><br>' +
                        '<strong>Línea 2 — Total Bienes Muebles:</strong> Suma de cuentas bancarias, seguros, vehículos, acciones, semovientes, bonos, etc.<br><br>' +
                        '<strong>Línea 3 — Patrimonio Hereditario Bruto:</strong> Línea 1 + Línea 2. Es la suma total de todos los bienes del causante.<br><br>' +
                        '<strong>Línea 4 — Activo Hereditario Bruto:</strong> Igual al Patrimonio Bruto. Punto de partida para las deducciones.',
                    side: 'left',
                    align: 'center'
                }
            });
        }

        // ── Step 4: Exclusiones (líneas 5-8) ──
        if (document.getElementById('resumenGrupoExclusiones')) {
            steps.push({
                element: '#resumenGrupoExclusiones',
                popover: {
                    title: '🛡️ Líneas 5–8: Exclusiones',
                    description:
                        '<strong>Línea 5 — Desgravámenes:</strong> Deducciones por vivienda principal, seguros de montepío y prestaciones sociales.<br><br>' +
                        '<strong>Línea 6 — Exenciones:</strong> Beneficios fiscales establecidos directamente por la ley.<br><br>' +
                        '<strong>Línea 7 — Exoneraciones:</strong> Beneficios otorgados por decreto presidencial.<br><br>' +
                        '<strong>Línea 8 — Total Exclusiones:</strong> Suma de líneas 5 + 6 + 7. Este monto se <strong>resta</strong> del activo bruto.',
                    side: 'left',
                    align: 'center'
                }
            });
        }

        // ── Step 5: Patrimonio Neto (líneas 9-11) ──
        if (document.getElementById('resumenGrupoNeto')) {
            steps.push({
                element: '#resumenGrupoNeto',
                popover: {
                    title: '📉 Líneas 9–11: Patrimonio Neto',
                    description:
                        '<strong>Línea 9 — Activo Hereditario Neto:</strong> Activo Bruto − Total Exclusiones. Lo que queda después de aplicar desgravámenes, exenciones y exoneraciones.<br><br>' +
                        '<strong>Línea 10 — Total Pasivo:</strong> Suma de todas las deudas y gastos sucesorales (tarjetas de crédito, hipotecas, préstamos, gastos funerarios).<br><br>' +
                        '<strong>Línea 11 — Líquido Hereditario Gravable:</strong> Activo Neto − Total Pasivo. Esta es la <strong>base imponible</strong> sobre la cual se calcula el impuesto.',
                    side: 'left',
                    align: 'center'
                }
            });
        }

        // ── Step 6: Determinación del Tributo (líneas 12-14) ──
        if (document.getElementById('resumenGrupoTributo')) {
            steps.push({
                element: '#resumenGrupoTributo',
                popover: {
                    title: '💰 Líneas 12–14: Determinación del Tributo',
                    description:
                        '<strong>Línea 12 — Impuesto Determinado:</strong> Calculado aplicando la tarifa progresiva del Art. 7 de la Ley de Sucesiones a la cuota parte de cada heredero y sumando todos los importes.<br><br>' +
                        '<strong>Línea 13 — Reducciones:</strong> Beneficios por parentesco cercano con el causante. Los herederos de 1er grado con cuota ≤ 75 UT están exentos.<br><br>' +
                        '<strong>Línea 14 — Total Impuesto a Pagar:</strong> Línea 12 − Línea 13. <strong>Este es el monto final</strong> que el contribuyente debe cancelar al SENIAT.',
                    side: 'left',
                    align: 'center'
                }
            });
        }

        // ── Step 7: Botones Cálculo Manual ──
        if (document.getElementById('resumenCalculoManual')) {
            steps.push({
                element: '#resumenCalculoManual',
                popover: {
                    title: '🔧 Cálculo Manual vs. Automático',
                    description:
                        '<strong>Modificar Cálculo:</strong> Permite al estudiante ajustar manualmente los valores de cuota parte, tarifa, sustraendo e impuesto por heredero, para practicar los cálculos a mano.<br><br>' +
                        '<strong>Restaurar Cálculo Automático:</strong> Revierte al cálculo automático del sistema.<br><br>' +
                        '⚠️ <strong>Diferencia con el SENIAT real:</strong><br>' +
                        'En el portal oficial del SENIAT, si usted activa el cálculo manual, el automático queda <strong>desactivado permanentemente</strong> para esa declaración — <strong>no existe forma de revertirlo</strong>.<br><br>' +
                        'El botón "Restaurar" es una <strong>facilidad exclusiva de este simulador</strong> para que pueda practicar sin consecuencias irreversibles.',
                    side: 'top',
                    align: 'center'
                }
            });
        }

        // ── Step 8: Tabla de Herederos ──
        if (document.getElementById('resumenTablaHerederos')) {
            steps.push({
                element: '#resumenTablaHerederos',
                popover: {
                    title: '👨‍👩‍👧‍👦 Cuota Parte por Heredero',
                    description:
                        'Detalle del cálculo <strong>por cada heredero</strong>:<br><br>' +
                        '• <strong>Cuota Parte (UT):</strong> Patrimonio Neto ÷ Total Herederos, expresado en Unidades Tributarias.<br>' +
                        '• <strong>Porcentaje/Tarifa:</strong> Según el tramo de la tabla del Art. 7 donde cae la cuota parte.<br>' +
                        '• <strong>Sustraendo (UT):</strong> Monto a restar según la tarifa progresiva.<br>' +
                        '• <strong>Impuesto Determinado:</strong> (Cuota Parte × Porcentaje) − Sustraendo.<br>' +
                        '• <strong>Reducción:</strong> Beneficio por parentesco cercano.<br>' +
                        '• <strong>Impuesto a Pagar:</strong> Impuesto Determinado − Reducción.<br><br>' +
                        '📌 Si la cuota parte es <strong>≤ 75 UT</strong> y el heredero es de <strong>1er grado</strong>, el impuesto es <strong>cero</strong>.',
                    side: 'top',
                    align: 'center'
                }
            });
        }

        // ── Step 9: Tarifa de Referencia (Art. 7) ──
        if (document.getElementById('resumenTarifaReferencia')) {
            steps.push({
                element: '#resumenTarifaReferencia',
                popover: {
                    title: '📐 Tarifa del Art. 7 — Ley de Sucesiones',
                    description:
                        'Tabla oficial de tarifas progresivas del impuesto sucesoral:<br><br>' +
                        '<strong>4 grados de parentesco:</strong><br>' +
                        '1° Ascendientes, descendientes, cónyuge e hijos adoptivos<br>' +
                        '2° Hermanos, sobrinos por representación<br>' +
                        '3° Otros colaterales de 3° y 4° grado<br>' +
                        '4° Afines, otros parientes y extraños<br><br>' +
                        '<strong>8 tramos</strong> de cuota parte (desde 0 hasta más de 4.000 UT), cada uno con su <strong>porcentaje</strong> y <strong>sustraendo</strong>.<br><br>' +
                        'A mayor parentesco (grado más lejano) y mayor cuota, <strong>mayor porcentaje</strong> y <strong>mayor impuesto</strong>.',
                    side: 'top',
                    align: 'center'
                }
            });
        }

        return steps;
    };


    // ─────────────────────────────────────────────────────────────────────
    // TOUR: Sucesión — Cálculo Manual Cuota Parte
    //
    // IDs: #cmInputsGenerales, #cmTablaEntrada,
    //      #btnCalcular, #divResultados, #btnAceptar
    // ─────────────────────────────────────────────────────────────────────

    TOURS.sucesiones_calculo_manual = function () {
        var steps = [];

        // ── Step 1: UT + Total Impuesto ──
        if (document.getElementById('cmInputsGenerales')) {
            steps.push({
                element: '#cmInputsGenerales',
                popover: {
                    title: '📊 Datos de Referencia',
                    description:
                        '<strong>Unidad Tributaria Aplicada:</strong> Valor de la UT vigente usado para convertir entre bolívares y UT.<br><br>' +
                        '<strong>Total Impuesto a Pagar:</strong> Monto calculado automáticamente por el sistema. Sus cálculos manuales deben producir un impuesto <strong>igual o mayor</strong> a este valor para ser válidos.<br><br>' +
                        'Estos campos son <strong>de solo lectura</strong>.',
                    side: 'bottom',
                    align: 'center'
                }
            });
        }

        // ── Step 2: Tabla de Entrada ──
        if (document.getElementById('cmTablaEntrada')) {
            steps.push({
                element: '#cmTablaEntrada',
                popover: {
                    title: '✏️ Tabla de Entrada',
                    description:
                        'Para cada heredero puede modificar <strong>dos valores</strong>:<br><br>' +
                        '• <strong>Cuota Parte Hereditaria (UT):</strong> La porción del patrimonio que le corresponde, expresada en Unidades Tributarias. El sistema la calcula como Patrimonio Neto ÷ Total Herederos.<br><br>' +
                        '• <strong>Reducción (Bs.):</strong> Beneficio fiscal por parentesco cercano. Puede ajustar este valor para practicar distintos escenarios.<br><br>' +
                        'Las demás columnas (nombre, cédula, parentesco, grado, premuerto) son informativas.',
                    side: 'top',
                    align: 'center'
                }
            });
        }

        // ── Step 3: Botón Calcular ──
        if (document.getElementById('btnCalcular')) {
            steps.push({
                element: '#btnCalcular',
                popover: {
                    title: '🖩️ Botón Calcular',
                    description:
                        'Al presionar <strong>«Calcular»</strong> el sistema:<br><br>' +
                        '1. Toma la Cuota Parte que usted ingresó<br>' +
                        '2. Busca el tramo de tarifa del Art. 7 según el grado de parentesco<br>' +
                        '3. Aplica: <em>(Cuota × Porcentaje) − Sustraendo</em><br>' +
                        '4. Resta la Reducción que usted ingresó<br>' +
                        '5. Muestra los resultados en una tabla inferior<br><br>' +
                        '⚠️ Si el total calculado es <strong>menor al impuesto automático</strong>, se mostrará un error.',
                    side: 'top',
                    align: 'center'
                }
            });
        }

        // ── Step 4: Sección de Resultados ──
        if (document.getElementById('divResultados')) {
            steps.push({
                element: '#divResultados',
                popover: {
                    title: '📋 Resultados y Confirmación',
                    description:
                        'Después de calcular se muestra la tabla completa con <strong>11 columnas</strong> (todos readonly):<br><br>' +
                        'Cuota Parte → Porcentaje → Sustraendo → Impuesto Determinado → Reducción → Impuesto a Pagar<br><br>' +
                        '<strong>«Aceptar»:</strong> Guarda los valores manuales en el borrador y regresa al Resumen. Los cálculos se reflejarán en la declaración.<br><br>' +
                        '<strong>«Cancelar»:</strong> Descarta los cambios y recarga la página.',
                    side: 'top',
                    align: 'center'
                }
            });
        }

        return steps;
    };

    // ─────────────────────────────────────────────────────────────────────
    // TOUR: Sucesión - Declaración Reverso (FORMA DS-99032 — cara posterior)
    //
    // Anexos J: Bienes Inmuebles, Bienes Muebles, Pasivos,
    //           Desgravámenes, Exenciones, Exoneraciones, Bienes Litigiosos
    // IDs inyectados: #navReverso, #headerReverso,
    //   #anexoInmuebles, #anexoMuebles, #anexoPasivos,
    //   #anexoDesgravamenes, #anexoExenciones, #anexoExoneraciones,
    //   #anexoLitigiosos
    // ─────────────────────────────────────────────────────────────────────

    TOURS.sucesiones_declaracion_reverso = function () {
        var steps = [];
        // ── Step 1: Botón Declarar (arriba) ──
        if (document.getElementById('btnDeclararReverso')) {
            steps.push({
                element: '#btnDeclararReverso',
                popover: {
                    title: '✅ Botón Declarar',
                    description:
                        'También disponible aquí en el Reverso.<br><br>' +
                        '<strong>En el SENIAT real:</strong><br>' +
                        '• La declaración es <strong>irreversible</strong> — una vez enviada no puede modificarse.<br>' +
                        '• Se genera la planilla oficial con número de trámite.<br><br>' +
                        '<strong>En esta simulación:</strong><br>' +
                        '• Se mostrará el monto a pagar y se pedirá confirmación.<br>' +
                        '• Se generarán los documentos PDF y se enviarán al profesor.<br><br>' +
                        '⚠️ <strong>Revise los anexos antes de declarar.</strong>',
                    side: 'bottom',
                    align: 'center'
                }
            });
        }

        // ── Step 2: Navegación ──
        if (document.getElementById('navReverso')) {
            steps.push({
                element: '#navReverso',
                popover: {
                    title: '🧭 Reverso de la Planilla',
                    description:
                        'Esta es la <strong>cara posterior</strong> de la FORMA DS-99032.<br><br>' +
                        'Contiene la <strong>Sección J — Anexos</strong>: el detalle de cada bien, pasivo ' +
                        'y deducción que compone los totales del Anverso.<br><br>' +
                        'Puede volver al <strong>Anverso</strong> con el botón de la izquierda.',
                    side: 'bottom',
                    align: 'center'
                }
            });
        }

        // ── Step 2: Header A-D ──
        if (document.getElementById('headerReverso')) {
            steps.push({
                element: '#headerReverso',
                popover: {
                    title: '📋 Secciones A–D (Encabezado)',
                    description:
                        'Replica los datos generales del Anverso para identificación:<br><br>' +
                        '• <strong>A:</strong> Contribuyente y RIF<br>' +
                        '• <strong>B:</strong> Causante<br>' +
                        '• <strong>C:</strong> Dirección y fecha de fallecimiento<br>' +
                        '• <strong>D:</strong> Representante Legal',
                    side: 'bottom',
                    align: 'center'
                }
            });
        }

        // ── Step 3: Bienes Inmuebles ──
        if (document.getElementById('anexoInmuebles')) {
            steps.push({
                element: '#anexoInmuebles',
                popover: {
                    title: '🏠 Anexo: Bienes Inmuebles',
                    description:
                        'Detalle de cada inmueble declarado:<br><br>' +
                        '• <strong>Tipo:</strong> Casa, terreno, apartamento, etc.<br>' +
                        '• <strong>Descripción:</strong> Porcentaje, linderos, superficies, dirección<br>' +
                        '• <strong>Registro:</strong> Oficina, número, protocolo, fecha<br>' +
                        '• <strong>Vivienda Principal / Litigioso:</strong> Indicadores SI/NO<br>' +
                        '• <strong>Monto Declarado</strong><br><br>' +
                        'El total alimenta la <strong>Línea 1</strong> del Anverso.',
                    side: 'top',
                    align: 'center'
                }
            });
        }

        // ── Step 4: Bienes Muebles ──
        if (document.getElementById('anexoMuebles')) {
            steps.push({
                element: '#anexoMuebles',
                popover: {
                    title: '🚗 Anexo: Bienes Muebles',
                    description:
                        'Todas las categorías: Bancos, Seguros, Transporte, Acciones, Semovientes, Bonos, etc.<br><br>' +
                        '• <strong>Categoría:</strong> Tipo de bien mueble<br>' +
                        '• <strong>Descripción:</strong> Datos específicos por categoría<br>' +
                        '• <strong>Litigioso:</strong> SI/NO<br>' +
                        '• <strong>Monto Declarado</strong><br><br>' +
                        'El total alimenta la <strong>Línea 2</strong> del Anverso.',
                    side: 'top',
                    align: 'center'
                }
            });
        }

        // ── Step 5: Pasivos ──
        if (document.getElementById('anexoPasivos')) {
            steps.push({
                element: '#anexoPasivos',
                popover: {
                    title: '💳 Anexo: Pasivos',
                    description:
                        'Deudas y gastos: tarjetas de crédito, hipotecarios, préstamos, gastos funerarios, etc.<br><br>' +
                        '• <strong>Categoría:</strong> Tipo de pasivo<br>' +
                        '• <strong>Descripción:</strong> Detalle + datos bancarios<br>' +
                        '• <strong>Monto Declarado</strong><br><br>' +
                        'El total alimenta la <strong>Línea 10</strong> del Anverso.',
                    side: 'top',
                    align: 'center'
                }
            });
        }

        // ── Step 6: Desgravámenes ──
        if (document.getElementById('anexoDesgravamenes')) {
            steps.push({
                element: '#anexoDesgravamenes',
                popover: {
                    title: '🏡 Anexo: Desgravámenes',
                    description:
                        'Bienes que se deducen: vivienda principal, seguros de montepío, prestaciones.<br><br>' +
                        '• <strong>Tipo:</strong> Categoría del desgravamen<br>' +
                        '• <strong>Descripción:</strong> Detalle del bien<br>' +
                        '• <strong>Monto Declarado</strong><br><br>' +
                        'El total alimenta la <strong>Línea 5</strong> del Anverso.',
                    side: 'top',
                    align: 'center'
                }
            });
        }

        // ── Step 7: Exenciones ──
        if (document.getElementById('anexoExenciones')) {
            steps.push({
                element: '#anexoExenciones',
                popover: {
                    title: '🛡️ Anexo: Exenciones',
                    description:
                        'Beneficios fiscales establecidos por <strong>ley</strong>.<br><br>' +
                        '• <strong>Tipo:</strong> Categoría de la exención<br>' +
                        '• <strong>Descripción:</strong> Detalle del beneficio<br>' +
                        '• <strong>Monto Declarado</strong><br><br>' +
                        'El total alimenta la <strong>Línea 6</strong> del Anverso.',
                    side: 'top',
                    align: 'center'
                }
            });
        }

        // ── Step 8: Exoneraciones ──
        if (document.getElementById('anexoExoneraciones')) {
            steps.push({
                element: '#anexoExoneraciones',
                popover: {
                    title: '📜 Anexo: Exoneraciones',
                    description:
                        'Beneficios otorgados por <strong>decreto presidencial</strong>.<br><br>' +
                        '• <strong>Tipo:</strong> Tipo de exoneración<br>' +
                        '• <strong>Descripción:</strong> Decreto, Gaceta Oficial<br>' +
                        '• <strong>Monto Declarado</strong><br><br>' +
                        'El total alimenta la <strong>Línea 7</strong> del Anverso.',
                    side: 'top',
                    align: 'center'
                }
            });
        }

        // ── Step 9: Bienes Litigiosos ──
        if (document.getElementById('anexoLitigiosos')) {
            steps.push({
                element: '#anexoLitigiosos',
                popover: {
                    title: '⚖️ Anexo: Bienes Litigiosos',
                    description:
                        'Bienes en <strong>disputa legal</strong> — extraídos automáticamente de inmuebles y muebles marcados como litigiosos.<br><br>' +
                        '• <strong>Tipo:</strong> Categoría original del bien<br>' +
                        '• <strong>Descripción:</strong> Detalle completo<br>' +
                        '• <strong>Monto Declarado</strong><br><br>' +
                        'Esta sección es <strong>informativa</strong> — los montos ya están incluidos en los totales de inmuebles/muebles.',
                    side: 'top',
                    align: 'center'
                }
            });
        }

        return steps;
    };


    // ─────────────────────────────────────────────────────────────────────
    // TOUR: Registro Contribuyente — Paso 1 (/simulador/registro/contribuyente)
    //
    // Steps adaptativos según progreso del estudiante:
    //   - Sin RIF         → indica que debe inscribir RIF primero
    //   - Ya registrado   → indica que ya tiene cuenta, vuelva al login
    //   - Flujo normal    → guía búsqueda de RIF + captcha
    //
    // Selectores inyectados en registro_contribuyente.php:
    //   #tourTipoBusqueda, #tourRifInput, #tourCaptchaRegistro,
    //   #btnBuscar, #btnRegresar
    // ─────────────────────────────────────────────────────────────────────
    TOURS.registro_contribuyente = function (s) {
        var steps = [];

        if (!s.tieneRif) {
            // ── Estado 1: No tiene RIF Sucesoral ──
            steps.push({
                popover: {
                    title: '📝 Registro de Contribuyente',
                    description:
                        'Esta es la página de <strong>registro de contribuyente</strong> del SENIAT, donde se crean ' +
                        'las credenciales de acceso al sistema de declaraciones.',
                    side: 'over',
                    align: 'center'
                }
            });
            steps.push({
                popover: {
                    title: '⚠️ Requisito Previo',
                    description:
                        'Para registrarse, primero necesita obtener su <strong>RIF Sucesoral</strong> ' +
                        'mediante la inscripción en el portal principal.<br><br>' +
                        'Regrese al portal para completar ese paso.',
                    side: 'over',
                    align: 'center'
                }
            });
            var btnReg = document.getElementById('btnRegresar');
            if (btnReg) {
                steps.push({
                    element: '#btnRegresar',
                    popover: {
                        title: '↩️ Regresar',
                        description: 'Presione <strong>Regresar</strong> para volver al portal de Servicios de Declaración.',
                        side: 'top',
                        align: 'center'
                    }
                });
            }

        } else if (s.estaRegistrado) {
            // ── Estado 2: Ya tiene cuenta registrada ──
            steps.push({
                popover: {
                    title: '✅ Ya Está Registrado',
                    description:
                        'Usted ya posee un <strong>usuario registrado</strong> en el sistema. ' +
                        'No necesita registrarse nuevamente.<br><br>' +
                        'Regrese al portal de Servicios de Declaración e <strong>inicie sesión</strong> con sus credenciales.',
                    side: 'over',
                    align: 'center'
                }
            });
            var btnReg2 = document.getElementById('btnRegresar');
            if (btnReg2) {
                steps.push({
                    element: '#btnRegresar',
                    popover: {
                        title: '↩️ Ir al Login',
                        description: 'Presione <strong>Regresar</strong> para ir a la página de inicio de sesión.',
                        side: 'top',
                        align: 'center'
                    }
                });
            }

        } else {
            // ── Estado 3: Tiene RIF, no registrado (flujo normal) ──
            steps.push({
                popover: {
                    title: '📝 Registro de Contribuyente',
                    description:
                        'Aquí debe registrarse como contribuyente para crear sus <strong>credenciales de acceso</strong> ' +
                        'al sistema de declaraciones.',
                    side: 'over',
                    align: 'center'
                }
            });
            var elTipo = document.getElementById('tourTipoBusqueda');
            if (elTipo) {
                steps.push({
                    element: '#tourTipoBusqueda',
                    popover: {
                        title: '🔍 Tipo de Búsqueda',
                        description: 'Seleccione el tipo de búsqueda: <strong>RIF</strong> o <strong>C.I</strong> (Cédula de Identidad).',
                        side: 'bottom',
                        align: 'center'
                    }
                });
            }
            var elRif = document.getElementById('tourRifInput');
            if (elRif) {
                steps.push({
                    element: '#tourRifInput',
                    popover: {
                        title: '🆔 RIF / Cédula',
                        description:
                            'Ingrese su <strong>RIF Sucesoral</strong> (ej: J30061516).<br><br>' +
                            'Si tiene el panel de referencia a la derecha, puede copiarlo desde allí.',
                        side: 'bottom',
                        align: 'center'
                    }
                });
            }
            var elCaptcha = document.getElementById('tourCaptchaRegistro');
            if (elCaptcha) {
                steps.push({
                    element: '#tourCaptchaRegistro',
                    popover: {
                        title: '🖼️ Código de Verificación',
                        description:
                            'Copie el <strong>código</strong> mostrado en la imagen. ' +
                            'Puede hacer clic en la imagen para generar uno nuevo.',
                        side: 'bottom',
                        align: 'center'
                    }
                });
            }
            var elBuscar = document.getElementById('btnBuscar');
            if (elBuscar) {
                steps.push({
                    element: '#btnBuscar',
                    popover: {
                        title: '✅ Buscar',
                        description: 'Presione <strong>Buscar</strong> para validar su RIF y continuar al paso de creación de credenciales.',
                        side: 'top',
                        align: 'center'
                    }
                });
            }
        }

        return steps;
    };


    // ─────────────────────────────────────────────────────────────────────
    // TOUR: Registro Contribuyente — Paso 2 (/simulador/registro/contribuyente/paso-2)
    //
    // No requiere context-awareness: solo es accesible si el paso 1
    // fue completado exitosamente.
    //
    // Selectores inyectados en registro_contribuyente_2.php:
    //   #tourUsuarioRegistro, #tourClaveRegistro,
    //   #tourSeccionIlustrativa, #btnAceptar
    // ─────────────────────────────────────────────────────────────────────
    TOURS.registro_contribuyente_paso2 = function () {
        var steps = [];

        steps.push({
            popover: {
                title: '🔐 Crear Credenciales',
                description:
                    'Cree sus <strong>credenciales de acceso</strong> al portal SENIAT. ' +
                    'Estas serán las que utilice para iniciar sesión en el sistema de declaraciones.',
                side: 'over',
                align: 'center'
            }
        });

        var elUsuario = document.getElementById('tourUsuarioRegistro');
        if (elUsuario) {
            steps.push({
                element: '#tourUsuarioRegistro',
                popover: {
                    title: '👤 Usuario',
                    description: 'Escriba un <strong>nombre de usuario</strong> para su cuenta.',
                    side: 'bottom',
                    align: 'center'
                }
            });
        }

        var elClave = document.getElementById('tourClaveRegistro');
        if (elClave) {
            steps.push({
                element: '#tourClaveRegistro',
                popover: {
                    title: '🔒 Clave',
                    description:
                        'Cree una <strong>contraseña segura</strong> que cumpla con los requisitos mostrados debajo del campo:<br>' +
                        '• Mínimo 8 caracteres<br>• Mayúscula, minúscula, número y carácter especial.',
                    side: 'bottom',
                    align: 'center'
                }
            });
        }

        var elIlustrativa = document.getElementById('tourSeccionIlustrativa');
        if (elIlustrativa) {
            steps.push({
                element: '#tourSeccionIlustrativa',
                popover: {
                    title: 'ℹ️ Campos Ilustrativos',
                    description:
                        'Estos campos (celular, preguntas de seguridad, captcha) se muestran con <strong>fines ilustrativos</strong>.<br><br>' +
                        'En el simulador educativo, solo se requieren <strong>usuario y contraseña</strong>.',
                    side: 'top',
                    align: 'center'
                }
            });
        }

        var elAceptar = document.getElementById('btnAceptar');
        if (elAceptar) {
            steps.push({
                element: '#btnAceptar',
                popover: {
                    title: '✅ Aceptar',
                    description:
                        'Presione <strong>Aceptar</strong> para crear su cuenta. ' +
                        'Será redirigido al portal para iniciar sesión.',
                    side: 'top',
                    align: 'center'
                }
            });
        }

        return steps;
    };


    // ╔═══════════════════════════════════════════════════════════════════╗
    // ║  5. SUB-TOURS — Tours contextuales disparados por interacción   ║
    // ║                                                                  ║
    // ║  Se disparan al seleccionar una opción en un select, clic, etc.  ║
    // ║  No son auto-start; los lanzan los listeners de la sección 6.    ║
    // ║  Cada sub-tour se guarda como "sub_{key}" en localStorage.       ║
    // ╚═══════════════════════════════════════════════════════════════════╝

    var SUBTOURS = {};

    // ─────────────────────────────────────────────────────────────────────
    // SUB-TOUR: Sucesión CON cédula
    // Trigger: Seleccionar "SUCESIÓN CON CÉDULA" en #personalidad
    // Guía: cédula del causante → fecha nacimiento → buscar
    // ─────────────────────────────────────────────────────────────────────
    SUBTOURS.sucesion_con_cedula = function () {
        var steps = [];

        steps.push({
            element: '#cedulaPasaporte',
            popover: {
                title: '🪪 Cédula del Causante',
                description:
                    'Ingresa la cédula del causante con el formato <strong>V12345678</strong> ' +
                    '(sin guiones ni puntos).<br><br>' +
                    '📄 Este dato está en el <strong>PDF del caso</strong>.',
                side: 'bottom',
                align: 'center'
            }
        });

        steps.push({
            element: '#fecha',
            popover: {
                title: '📅 Fecha de Fallecimiento',
                description:
                    'Ingresa la <strong>fecha de fallecimiento</strong> del causante con formato ' +
                    '<strong>dd/mm/aaaa</strong> (ejemplo: 25/10/1995).<br><br>' +
                    '📄 Este dato está en el <strong>PDF del caso</strong>.',
                side: 'bottom',
                align: 'center'
            }
        });

        steps.push({
            element: '#buscar',
            popover: {
                title: '🔍 Buscar',
                description:
                    'Una vez que llenes todos los campos, haz clic en <strong>Buscar</strong>.<br><br>' +
                    'El sistema te indicará que no hay datos registrados y te ofrecerá un enlace ' +
                    'para <strong>registrar los datos</strong> del causante.',
                side: 'top',
                align: 'center'
            }
        });

        return steps;
    };

    // ─────────────────────────────────────────────────────────────────────
    // SUB-TOUR: Sucesión SIN cédula
    // Trigger: Seleccionar "SUCESIÓN SIN CÉDULA" en #personalidad
    // Campos activos: fecha, razonSocial (parroquia), registroProvidencia (nro acta), tomoGaceta (año)
    // ─────────────────────────────────────────────────────────────────────
    SUBTOURS.sucesion_sin_cedula = function () {
        var steps = [];

        steps.push({
            element: '#fecha',
            popover: {
                title: '📅 Fecha de Fallecimiento',
                description:
                    'Ingresa la <strong>fecha de fallecimiento</strong> del causante ' +
                    'con formato <strong>dd/mm/aaaa</strong> (ejemplo: 15/03/2024).<br><br>' +
                    '📄 Este dato está en el <strong>PDF del caso</strong>.',
                side: 'bottom',
                align: 'center'
            }
        });

        steps.push({
            element: '#razonSocial',
            popover: {
                title: '📝 Parroquia',
                description:
                    'Ingresa el <strong>nombre de la parroquia</strong> del acta de defunción.<br><br>' +
                    '📄 Este dato está en el <strong>PDF del caso</strong>.',
                side: 'bottom',
                align: 'center'
            }
        });

        steps.push({
            element: '#registroProvidencia',
            popover: {
                title: '📋 Nro. de Acta',
                description:
                    'Ingresa el <strong>número del acta</strong> de defunción.<br><br>' +
                    '📄 Este dato está en el <strong>PDF del caso</strong>.',
                side: 'bottom',
                align: 'center'
            }
        });

        steps.push({
            element: '#tomoGaceta',
            popover: {
                title: '📆 Año',
                description:
                    'Ingresa el <strong>año del acta</strong> de defunción ' +
                    '(4 dígitos, ejemplo: 2024).',
                side: 'bottom',
                align: 'center'
            }
        });

        steps.push({
            element: '#buscar',
            popover: {
                title: '🔍 Buscar',
                description:
                    'Una vez que llenes todos los campos, haz clic en <strong>Buscar</strong>.<br><br>' +
                    'El sistema te indicará que no hay datos registrados y te ofrecerá un enlace ' +
                    'para <strong>registrar los datos</strong> del causante.',
                side: 'top',
                align: 'center'
            }
        });

        return steps;
    };


    // ╔═══════════════════════════════════════════════════════════════════╗
    // ║  6. LISTENERS — Event listeners para disparar sub-tours         ║
    // ║                                                                  ║
    // ║  Se registran en autoStart() solo para la página correspondiente.║
    // ╚═══════════════════════════════════════════════════════════════════╝

    /**
     * Listener para /simulador/inscripcion-rif
     *
     * Escucha cambios en el select #personalidad. Si el usuario elige una
     * opción de sucesión, dispara el sub-tour correspondiente (con/sin cédula).
     *
     * Diferenciación: ambas opciones tienen value="6", así que se distinguen
     * por el texto de la opción (indexOf('SIN')).
     */
    function setupInscripcionRifListener() {
        var select = document.getElementById('personalidad');
        if (!select) return;

        select.addEventListener('change', function () {
            var selectedText = select.options[select.selectedIndex].text.toUpperCase();

            // Ignorar opciones que no son sucesión
            if (selectedText.indexOf('SUCESI') === -1) return;

            var subKey = selectedText.indexOf('SIN') !== -1
                ? 'sucesion_sin_cedula'
                : 'sucesion_con_cedula';

            // Cada sub-tour se muestra solo una vez por tipo por intento
            if (hasSeenTour('sub_' + subKey)) return;

            var subTourFn = SUBTOURS[subKey];
            if (!subTourFn) return;

            // Delay para que ActivarBusqPreinscritos() habilite los campos primero
            setTimeout(function () {
                window.startGuidedTour(null, subTourFn(), 'sub_' + subKey);
            }, 500);
        });
    }


    // ╔═══════════════════════════════════════════════════════════════════╗
    // ║  7. API PÚBLICA                                                  ║
    // ║                                                                  ║
    // ║  window.startGuidedTour() — Inicia un tour (principal o sub)     ║
    // ║  window.initGuidedTourForPage() — Re-inicializa tras nav SPA    ║
    // ╚═══════════════════════════════════════════════════════════════════╝

    /**
     * Inicia o reinicia un tour guiado.
     *
     * Uso principal (tour de página):
     *   window.startGuidedTour()                     — auto-detecta página
     *   window.startGuidedTour('seniat_index')       — fuerza página
     *
     * Uso sub-tour (steps pre-construidos):
     *   window.startGuidedTour(null, stepsArray, 'sub_mi_tour')
     *
     * @param {string|null} forcePageKey - Override de la clave de página
     * @param {Array|null}  customSteps  - Steps pre-construidos (para sub-tours)
     * @param {string|null} tourId       - ID para localStorage (para sub-tours)
     */
    window.startGuidedTour = function (forcePageKey, customSteps, tourId) {
        // ── Debounce de 800ms ────────────────────────────────
        // Previene doble ejecución rápida (ej: doble click).
        // A diferencia de una bandera estricta (isTourActive), el debounce
        // se auto-libera por tiempo, sin depender de que onDestroyed se ejecute.
        if (window._tourDebounce) return;
        window._tourDebounce = true;
        setTimeout(function () { window._tourDebounce = false; }, 800);

        var pageKey = forcePageKey || currentPageKey;
        var steps;

        if (customSteps && customSteps.length > 0) {
            steps = customSteps;
        } else {
            if (!pageKey) return;
            var tourFn = TOURS[pageKey];
            if (!tourFn) {
                console.log('[GuidedTour] No tour defined for page:', pageKey);
                return;
            }
            steps = tourFn(state);
        }

        if (!steps || steps.length === 0) {
            console.log('[GuidedTour] No steps');
            return;
        }

        var seenKey = tourId || pageKey;

        // ── Scroll-lock ─────────────────────────────────────
        // Bloquea .sim-main durante el tour para evitar desalineamiento
        // del overlay SVG (que vive en body) con los elementos resaltados.
        var simMain = document.querySelector('.sim-main');
        var originalOverflow = simMain ? simMain.style.overflow : '';

        var scrollTimeout = null;

        // lockScroll — Bloqueo directo (usado en llamadas síncronas confiables)
        function lockScroll() {
            if (simMain) simMain.style.overflow = 'hidden';
        }

        // safeLockScroll — Bloqueo con cortafuegos (usado SOLO en setTimeout)
        // Verifica que el popover de Driver.js aún exista en el DOM.
        // Si el usuario cerró el tour abruptamente y Driver.js hizo "Silent Failure"
        // (onDestroyed nunca se ejecutó), el popover ya no existirá y esta función
        // se abstiene de bloquear, evitando trancar la página permanentemente.
        function safeLockScroll() {
            var driverSigueVivo = document.querySelector('.driver-popover') !== null;
            if (simMain && driverSigueVivo) {
                simMain.style.overflow = 'hidden';
            }
        }

        function unlockScroll() {
            if (simMain) {
                simMain.style.overflow = originalOverflow || 'auto';
            }
        }

        function scrollToStep(element) {
            if (!element || !simMain) return;
            // Desbloquear → scroll → re-bloquear tras animación
            unlockScroll();
            element.scrollIntoView({ behavior: 'smooth', block: 'center' });

            if (scrollTimeout) clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(safeLockScroll, 350); // safeLockScroll, NO lockScroll
        }

        // ── Construir config con hooks ──────────────────────
        var config = {};
        for (var k in baseConfig) {
            if (baseConfig.hasOwnProperty(k)) config[k] = baseConfig[k];
        }
        config.steps = steps;

        // Antes de cada step: scroll al elemento y bloquear
        config.onHighlightStarted = function (el, step) {
            var domEl = el instanceof HTMLElement ? el : null;
            if (!domEl && step && step.element) {
                domEl = document.querySelector(step.element);
            }
            if (domEl) {
                scrollToStep(domEl);
            } else {
                if (scrollTimeout) clearTimeout(scrollTimeout);
                lockScroll();
            }
        };

        // Al cerrar/terminar: restaurar scroll + marcar como visto
        config.onDestroyed = function () {
            if (scrollTimeout) clearTimeout(scrollTimeout);
            if (safetyInterval) clearInterval(safetyInterval);
            unlockScroll();
            if (seenKey) markTourSeen(seenKey);
        };

        lockScroll();

        var d = driverFn(config);
        d.drive();

        // ── Monitor de seguridad (Safety Net) ────────────────
        // Cada 500ms verifica si el popover de Driver.js sigue en el DOM.
        // Si desapareció (Silent Failure: onDestroyed nunca se ejecutó),
        // fuerza el desbloqueo del scroll para que la página no quede trancada.
        var safetyInterval = setInterval(function () {
            if (!document.querySelector('.driver-popover')) {
                clearInterval(safetyInterval);
                unlockScroll();
            }
        }, 500);
    };

    /**
     * Re-inicializa el tour para la página actual.
     * Llamado por spa_loader.js después de una navegación SPA.
     */
    window.initGuidedTourForPage = function () {
        currentPageKey = detectPageKey();
        if (currentPageKey && !hasSeenTour(currentPageKey)) {
            setTimeout(function () {
                window.startGuidedTour();
            }, 400);
        }
    };


    // ╔═══════════════════════════════════════════════════════════════════╗
    // ║  8. BOOTSTRAP — Auto-start y registro de listeners              ║
    // ╚═══════════════════════════════════════════════════════════════════╝

    function autoStart() {
        // Auto-ejecutar tour principal si no se ha visto
        // EXCEPCIÓN: validar_inscripcion_rif con datos completos → el modal ya se abre solo
        if (currentPageKey && !hasSeenTour(currentPageKey)) {
            if (currentPageKey === 'validar_inscripcion_rif') {
                var borr = window.simBorrador || {};
                var ds = borr.direcciones || [];
                var rs = borr.relaciones || [];
                var completo = ds.some(function (d) { return d.tipoDireccion === '06'; }) && rs.some(function (r) { return r.parentesco === '50'; });
                if (completo) {
                    // No auto-start, el modal de confirmación ya se abre
                    markTourSeen(currentPageKey);
                    return;
                }
            }
            setTimeout(function () {
                window.startGuidedTour();
            }, 600);
        }

        // Registrar listeners específicos por página
        if (currentPageKey === 'inscripcion_rif') {
            setupInscripcionRifListener();
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', autoStart);
    } else {
        autoStart();
    }

})();
