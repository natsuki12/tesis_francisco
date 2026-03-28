<?php
declare(strict_types=1);

$pageTitle = 'Registro Contribuyente — Simulador';
$activePage = 'simulador';

// Carga Bootstrap + estilos propios en <head> con @layer para no contaminar layout
$extraCss = '
<style>
    @import url("' . asset('css/simulator/seniat_actual/servicios_declaracion/bootstrap-icons.css') . '") layer(seniat-bootstrap);
    @import url("' . asset('css/simulator/seniat_actual/servicios_declaracion/bootstrap.min.css') . '") layer(seniat-bootstrap);
    @import url("' . asset('css/simulator/seniat_actual/registro/registro_contribuyente.css') . '") layer(seniat-bootstrap);
</style>';

ob_start();
?>

<!--
  Registro Contribuyente - Vista refactorizada
  Original: SingleFile dump de 419KB con fuente base64 inline
  Refactorizado: recursos externos
  Fuente original: https://dgpatrimonios.seniat.gob.ve/registro/contribuyente
-->

<style>
    /* --- Contenedor externo --- */
    .seniat-wrapper {
        background: var(--sim-white, #ffffff);
        border-radius: 12px;
        box-shadow: var(--sim-shadow-lg, 0 4px 6px rgba(0, 0, 0, 0.07));
        overflow: hidden;
        border: 1px solid var(--sim-border, #dfe5ee);
        position: relative;
        min-height: 80vh;
    }

    /* --- Barrera de aislamiento (Bootstrap-compatible) ---
         Estos estilos NO están en @layer, así que ganan sobre
         el layout Y sobre Bootstrap (layered). ---- */
    .seniat-wrapper .seniat-scope-bs {
        font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", "Noto Sans", "Liberation Sans", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
        font-size: 16px;
        font-weight: 400;
        line-height: 1.5;
        color: rgb(33, 37, 41);
        -webkit-text-size-adjust: 100%;
    }

    /* Forzar que el color del texto escrito/seleccionado dentro de los inputs/selects sea negro/oscuro (#212529) */
    .seniat-wrapper .form-control {
        color: #212529 !important;
    }

    .seniat-wrapper .seniat-scope-bs *,
    .seniat-wrapper .seniat-scope-bs *::before,
    .seniat-wrapper .seniat-scope-bs *::after {
        box-sizing: border-box;
    }

    /* 
       NOTA: Todos los estilos de grid, form-floating y form-control 
       son manejados nativamente por bootstrap.min.css cargado en <head> 
    */

    .seniat-wrapper .card {
        position: relative;
        display: flex;
        flex-direction: column;
        min-width: 0;
        word-wrap: break-word;
        background-color: #fff;
        background-clip: border-box;
        border: 1px solid rgba(0, 0, 0, 0.176);
        border-radius: 6px;
    }

    .seniat-wrapper .card-header:first-child {
        border-radius: 5px 5px 0 0;
    }

    .seniat-wrapper .card-body {
        flex: 1 1 auto;
        padding: 1rem;
    }

    .seniat-wrapper .card-header {
        padding: .5rem 1rem;
        margin-bottom: 0;
        background-color: rgba(33, 37, 41, 0.03);
        color: rgb(33, 37, 41);
        border-bottom: 1px solid rgba(0, 0, 0, .125);
    }

    .seniat-wrapper .card-heading strong {
        font-weight: 700;
        font-size: 16px;
    }

    /* 
       Button overrides para colores específicos del SENIAT 
    */

    .seniat-wrapper .btn-danger {
        color: #fff;
        background-color: #245b98;
        border-color: #245b98;
        font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", "Noto Sans", "Liberation Sans", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
        font-size: 14px;
        font-weight: 400; /* Igual a Regresar */
        padding: 4px 8px;
    }

    .seniat-wrapper .btn-danger:hover {
        background-color: #164193;
        border-color: #164193;
    }

    .seniat-wrapper .btn-light {
        color: #000;
        background-color: #f8f9fa;
        border-color: #f8f9fa;
        font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", "Noto Sans", "Liberation Sans", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
        font-size: 14px;
        font-weight: 400;
        padding: 4px 8px;
    }

    .seniat-wrapper .btn-light:hover {
        color: #000;
        background-color: #e2e6ea;
        border-color: #dae0e5;
    }

    /* Label styles para los form-floating */
    .seniat-wrapper .form-floating>label {
        color: rgba(33, 37, 41, 0.65) !important;
        font-size: 16px;
        font-weight: 400;
        transition: opacity 0.1s ease-in-out, transform 0.1s ease-in-out;
        transform-origin: 0 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 100%;
    }

    /* Estado activo/focus del label (azul oscuro SENIAT) */
    .seniat-wrapper .form-floating>.form-control:focus~label,
    .seniat-wrapper .form-floating>select.form-control:focus~label,
    .seniat-wrapper .form-floating>select.form-control:not(:placeholder-shown)~label {
        color: #245b98 !important;
        /* Azul SENIAT en vez del azul claro de Bootstrap */
        opacity: 1 !important;
        transform: matrix(0.85, 0, 0, 0.85, 2.04, -6.8) !important;
    }

    /* Input con texto pero sin foco (label debe quedar flotado pero en gris claro) */
    .seniat-wrapper .form-floating>.form-control:not(:focus):not(:placeholder-shown)~label {
        color: rgba(33, 37, 41, 0.65) !important;
        opacity: 0.65 !important;
        transform: matrix(0.85, 0, 0, 0.85, 2.04, -6.8) !important;
    }

    /* Forzar que el label del RIF siempre esté flotado arriba (sin animación) */
    .seniat-wrapper .form-floating>#rif~label {
        color: rgba(33, 37, 41, 0.65) !important;
        opacity: 0.65 !important;
        transform: matrix(0.85, 0, 0, 0.85, 2.04, -6.8) !important;
    }

    .seniat-wrapper .form-floating>#rif:focus~label {
        color: #245b98 !important;
        opacity: 1 !important;
    }

    .seniat-wrapper #banner {
        width: 100%;
        display: block;
    }

    /* Captcha label - estilo del original */
    .seniat-wrapper .imageninput {
        font-size: 25px;
        display: inline-block;
        width: 120px;
        height: 60px;
        text-align: center;
        background-color: #eee;
        border: none;
        font-weight: 400;
        text-decoration: line-through;
        transform: translateY(-5%);
        margin: auto;
        border-radius: 10px;
    }

    /* =========================================
       MODAL SENIAT NUEVO (No-Legacy)
    ========================================= */
    .seniat-wrapper .seniat-modal-box .modal-content {
        border-radius: 8px;
        border: none;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    .seniat-wrapper .seniat-modal-box .modal-header {
        border-bottom: 1px solid #dee2e6;
        padding: 1rem 1.5rem;
        align-items: center;
    }

    .seniat-wrapper .seniat-modal-box .modal-title {
        color: #212529;
        font-weight: 500;
        font-size: 1.25rem;
        margin: 0;
    }

    /* Botón de cerrar personalizado (Fondo gris con X delgada) */
    .seniat-wrapper .seniat-modal-box .btn-close-custom {
        background-color: #f8f9fa;
        border: none;
        border-radius: 6px;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6c757d;
        cursor: pointer;
        font-size: 1.2rem;
        line-height: 1;
        padding: 0;
        transition: background-color 0.2s;
        margin-left: auto;
    }

    .seniat-wrapper .seniat-modal-box .btn-close-custom:hover {
        background-color: #e2e6ea;
    }

    /* Alerta interna (Error de coincidencia) */
    .seniat-wrapper .seniat-modal-box .alert-seniat-danger {
        background-color: #f8d7da;
        border: 1px solid #f5c6cb;
        color: #842029;
        border-radius: 6px;
        padding: 1rem;
        display: flex;
        align-items: flex-start;
        gap: 0.5rem;
        font-size: 0.95rem;
        margin-bottom: 0;
    }

    .seniat-wrapper .seniat-modal-box .alert-seniat-danger i {
        font-size: 1.1rem;
        line-height: 1.25;
        color: #842029;
    }
</style>

<div class="seniat-wrapper">
    <div class="seniat-scope-bs">

        <!-- Banner de la aplicación -->
        <div class="container">
            <div class="row align-items-center">
                <img id="banner" src="<?= asset('img/simulator/seniat_actual/registro/banner_registro.png') ?>"
                    alt="Banner SENIAT - Registro de Contribuyentes">
            </div>

            <!-- Contenido principal -->
            <div class="page-holder align-items-center py-2 vh-100">
                <div class="card">
                    <div class="card-header">
                        <div class="card-heading">
                            <strong>Registro Usuario</strong>
                        </div>
                    </div>
                    <div class="card-body">

                        <?php if (!empty($rifAsignado)): ?>
                        <div style="display:flex; gap:24px; align-items:flex-start;">
                            <!-- Formulario (izquierda) -->
                            <div style="flex:1; min-width:0;">
                        <?php endif; ?>
                        <form id="formBusquedaContribuyente" novalidate>
                            <div>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div style="color: rgb(33, 37, 41); font-size: 16px; margin-bottom: 8px;">
                                            Indique la Cedula de Identidad o Registro de Información de Fiscal (RIF):
                                        </div>
                                    </div>
                                </div>
                                <br>

                                <!-- Tipo de Búsqueda y Campo RIF/CI -->
                                <div class="row row-cols-auto">
                                    <div class="col-sm-3">
                                        <div class="form-floating">
                                            <select id="tipobusqueda" name="tipobusqueda"
                                                class="form-control form-control-sm">
                                                <option value="" selected>Seleccione</option>
                                                <option value="R">RIF</option>
                                                <option value="C">C.I</option>
                                            </select>
                                            <label for="tipobusqueda">Tipo Búsqueda</label>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-floating">
                                            <input id="rif" name="rif" maxlength="10"
                                                class="form-control form-control-sm" placeholder=" " value="">
                                            <label for="rif">C.I / R.I.F</label>
                                        </div>
                                    </div>
                                    <!-- Mensaje de error de validación -->
                                    <div class="col-sm-5 d-flex align-items-center">
                                        <div id="rifErrorMsg" class="text-danger" style="display: none; padding: 0 12px; font-size: 16px;">
                                            Favor ingrese el formato válido Ej: V1234567 o E12345678
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <br>

                            <!-- Captcha -->
                            <div class="row row-cols-auto g-1">
                                <div class="col-sm-3">
                                    <label class="imageninput" id="captchaText"
                                        style="font-size:25px; width:120px; height:60px; line-height:60px;">ojht6y</label>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-floating">
                                        <input id="floatingInputCap" name="captcha" type="text" placeholder=" "
                                            maxlength="6" required class="form-control" value="">
                                        <label for="floatingInputCap">Ingrese el código mostrado en la imagen</label>
                                    </div>
                                </div>
                            </div>
                            <br>

                            <!-- Botones -->
                            <div class="row">
                                <div class="col-md gy-2 gx-3 align-items-center">
                                    <div class="col">
                                        <div class="form-floating">
                                            <input type="button" id="btnBuscar" value="Buscar"
                                                class="btn btn-sm btn-danger" disabled>
                                            &nbsp;
                                            <a id="btnRegresar" class="btn btn-sm btn-light"
                                                href="<?= base_url('/simulador/servicios_declaracion') ?>">Regresar</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <!-- Fin del formulario -->

                        <?php if (!empty($rifAsignado)): ?>
                            </div><!-- cierre flex-izquierda -->

                            <!-- Panel RIF (derecha) -->
                            <div style="width:320px; flex-shrink:0;">
                                <div style="background:#eef3fa; border:1px solid #b8cce4; border-radius:8px; padding:20px; text-align:center;">
                                    <div style="font-size:14px; color:#245b98; margin-bottom:6px; font-weight:500;">✅ Su RIF Sucesoral</div>
                                    <div style="font-size:26px; font-weight:700; letter-spacing:3px; color:#1a3d6e; margin-bottom:14px;">
                                        <?= htmlspecialchars($rifAsignado) ?>
                                    </div>
                                    <div style="background:#f5f6f8; border:1px solid #d0d5dd; border-radius:6px; padding:10px 12px; text-align:left; font-size:12.5px; color:#495057; line-height:1.6;">
                                        <strong>📌 Nota educativa:</strong> En el proceso real, el RIF se envía únicamente al correo utilizado durante el proceso de registro. Este simulador también se lo ha enviado a su correo, pero lo muestra aquí con fines educativos.
                                    </div>
                                </div>
                            </div>

                        </div><!-- cierre flex container -->
                        <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- =========================================
             Modal de Información (Estilo SENIAT Nuevo — Vanilla JS) 
             ========================================= -->
        <style>
            .seniat-wrapper .seniat-modal-overlay {
                display: none;
                position: fixed;
                top: 0; left: 0; right: 0; bottom: 0;
                background: rgba(0,0,0,0.5);
                z-index: 9999;
                align-items: center;
                justify-content: center;
            }
            .seniat-wrapper .seniat-modal-overlay.show {
                display: flex;
            }
            .seniat-wrapper .seniat-modal-box {
                background: #fff;
                border-radius: 8px;
                box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
                max-width: 500px;
                width: 90%;
                animation: seniatModalFadeIn 0.2s ease;
            }
            @keyframes seniatModalFadeIn {
                from { opacity: 0; transform: scale(0.95); }
                to   { opacity: 1; transform: scale(1); }
            }
            .seniat-wrapper .seniat-modal-box .modal-header {
                border-bottom: 1px solid #dee2e6;
                padding: 1rem 1.5rem;
                display: flex;
                align-items: center;
            }
            .seniat-wrapper .seniat-modal-box .modal-title {
                color: #212529;
                font-weight: 500;
                font-size: 1.25rem;
                margin: 0;
            }
            .seniat-wrapper .seniat-modal-box .modal-body {
                padding: 1.5rem;
            }
        </style>

        <div class="seniat-modal-overlay" id="modalOverlay">
            <div class="seniat-modal-box">
                <div class="modal-header">
                    <h5 class="modal-title">Información</h5>
                    <button type="button" class="btn-close-custom" id="modalCloseBtn" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="modalAlertContent">
                        <!-- Contenido dinámico del modal -->
                    </div>
                </div>
            </div>
        </div>
        <!-- Fin Modal -->

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const btnBuscar = document.getElementById('btnBuscar');
                const rifInput = document.getElementById('rif');
                const tipoBusqueda = document.getElementById('tipobusqueda');
                const rifErrorMsg = document.getElementById('rifErrorMsg');
                const modalOverlay = document.getElementById('modalOverlay');

                // RIF asignado al estudiante (desde el backend)
                const rifAsignado = <?= json_encode($rifAsignado ?? null) ?>;

                // ── Captcha dinámico (igual que login SENIAT) ──
                const captchaLabel = document.getElementById('captchaText');
                const inputCaptcha = document.getElementById('floatingInputCap');

                function generarCaptcha() {
                    const chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
                    let code = '';
                    for (let i = 0; i < 6; i++) code += chars[Math.floor(Math.random() * chars.length)];
                    return code;
                }

                let captchaCode = generarCaptcha();
                if (captchaLabel) {
                    captchaLabel.textContent = captchaCode;
                    captchaLabel.style.cursor = 'pointer';
                    captchaLabel.title = 'Clic para generar un nuevo código';
                    captchaLabel.addEventListener('click', () => {
                        captchaCode = generarCaptcha();
                        captchaLabel.textContent = captchaCode;
                        if (inputCaptcha) inputCaptcha.value = '';
                    });
                }

                // Cerrar modal
                document.getElementById('modalCloseBtn').addEventListener('click', () => {
                    modalOverlay.classList.remove('show');
                });
                modalOverlay.addEventListener('click', (e) => {
                    if (e.target === modalOverlay) modalOverlay.classList.remove('show');
                });

                // Helper para mostrar modal
                const showModal = (message, type = 'danger') => {
                    const content = document.getElementById('modalAlertContent');
                    const alertClass = type === 'success' ? 'alert-seniat-success' : 'alert-seniat-danger';
                    const iconColor = type === 'success' ? '#0f5132' : '#842029';
                    const iconSvg = type === 'success'
                        ? `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="${iconColor}" viewBox="0 0 16 16" style="flex-shrink:0;margin-top:2px"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg>`
                        : `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="${iconColor}" viewBox="0 0 16 16" style="flex-shrink:0;margin-top:2px"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293 5.354 4.646z"/></svg>`;

                    content.innerHTML = `
                        <div class="${alertClass}">
                            ${iconSvg}
                            <div>${message}</div>
                        </div>
                    `;
                    modalOverlay.classList.add('show');
                };

                // Validación en tiempo real del RIF
                const validateRif = () => {
                    let val = rifInput.value;
                    
                    if (val.length > 0) {
                        val = val.charAt(0).toUpperCase() + val.slice(1);
                        rifInput.value = val;
                        
                        const isValid = /^[VE][0-9]{0,9}$/.test(val);
                        
                        if (!isValid) {
                            rifErrorMsg.style.display = 'block';
                            btnBuscar.setAttribute('disabled', 'true');
                        } else {
                            rifErrorMsg.style.display = 'none';
                            btnBuscar.removeAttribute('disabled');
                        }
                    } else {
                        rifErrorMsg.style.display = 'none';
                        btnBuscar.setAttribute('disabled', 'true');
                    }
                };

                if (rifInput) {
                    rifInput.addEventListener('keyup', validateRif);
                    rifInput.addEventListener('blur', validateRif);
                }

                // Botón Buscar — validación del RIF + captcha
                if (btnBuscar) {
                    btnBuscar.addEventListener('click', () => {
                        const rifIngresado = rifInput.value.trim();
                        const captchaIngresado = (inputCaptcha ? inputCaptcha.value : '').trim();

                        // 1. Validar captcha (case-insensitive)
                        if (captchaIngresado.toLowerCase() !== captchaCode.toLowerCase()) {
                            showModal('El código no coincide con la imagen. Por favor verifique e intente de nuevo.', 'danger');
                            captchaCode = generarCaptcha();
                            if (captchaLabel) captchaLabel.textContent = captchaCode;
                            if (inputCaptcha) inputCaptcha.value = '';
                            return;
                        }

                        // 2. Validar que tenga RIF asignado
                        if (!rifAsignado) {
                            showModal('No tiene un RIF asignado aún. Complete los pasos previos del simulador.', 'danger');
                            return;
                        }

                        // 3. Validar que el RIF coincida con el asignado (normalizar quitando guiones)
                        const normalizar = (rif) => rif.replace(/-/g, '');
                        if (normalizar(rifIngresado) !== normalizar(rifAsignado)) {
                            showModal('No hay ningún contribuyente registrado con esos datos.', 'danger');
                            return;
                        }

                        // 4. Todo válido → llamar al backend para marcar sesión y redirigir al paso 2
                        btnBuscar.setAttribute('disabled', 'true');
                        const formData = new FormData();
                        formData.append('rif', rifIngresado);

                        fetch('<?= base_url('/simulador/registro/contribuyente/validar') ?>', {
                            method: 'POST',
                            body: formData
                        })
                        .then(r => r.json())
                        .then(data => {
                            if (data.ok) {
                                window.location.href = '<?= base_url('/simulador/registro/contribuyente/paso-2') ?>';
                            } else {
                                showModal(data.msg || 'Error inesperado.', 'danger');
                                btnBuscar.removeAttribute('disabled');
                            }
                        })
                        .catch(() => {
                            showModal('Error de conexión. Intente de nuevo.', 'danger');
                            btnBuscar.removeAttribute('disabled');
                        });
                    });
                }
            });
        </script>

    </div><!-- /.seniat-scope-bs -->
</div><!-- /.seniat-wrapper -->

<?php
$content = ob_get_clean();
include __DIR__ . '/../../../layouts/logged_layout.php';
?>