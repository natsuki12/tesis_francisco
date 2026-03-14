<?php
declare(strict_types=1);

$pageTitle = 'Registro Contribuyente — Paso 2 — Simulador';
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
  Registro Contribuyente - Paso 2
  Campos: Usuario, Clave, Nro. Celular, Preguntas de Seguridad, Captcha
  Fuente original: https://dgpatrimonios.seniat.gob.ve/registro/contribuyente
-->

<style>
    /* --- Contenedor externo --- */
    .seniat-wrapper {
        background: var(--sim-white, #ffffff);
        border-radius: 12px;
        box-shadow: var(--sim-shadow-lg, 0 4px 6px rgba(0, 0, 0, 0.07));
        overflow: visible;
        border: 1px solid var(--sim-border, #dfe5ee);
        position: relative;
    }

    /* --- Barrera de aislamiento (Bootstrap-compatible) --- */
    .seniat-wrapper .seniat-scope-bs {
        font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", "Noto Sans", "Liberation Sans", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
        font-size: 16px;
        font-weight: 400;
        line-height: 1.5;
        color: rgb(33, 37, 41);
        -webkit-text-size-adjust: 100%;
    }

    .seniat-wrapper .form-control {
        color: #212529 !important;
    }

    .seniat-wrapper .seniat-scope-bs *,
    .seniat-wrapper .seniat-scope-bs *::before,
    .seniat-wrapper .seniat-scope-bs *::after {
        box-sizing: border-box;
    }

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

    /* Button overrides */
    .seniat-wrapper .btn-danger {
        color: #fff;
        background-color: #245b98;
        border-color: #245b98;
        font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", "Noto Sans", "Liberation Sans", Arial, sans-serif;
        font-size: 14px;
        font-weight: 400;
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
        font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", "Noto Sans", "Liberation Sans", Arial, sans-serif;
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

    .seniat-wrapper .form-floating>.form-control:focus~label,
    .seniat-wrapper .form-floating>.form-control:not(:placeholder-shown)~label {
        color: #245b98 !important;
        opacity: 1 !important;
        transform: matrix(0.85, 0, 0, 0.85, 2.04, -6.8) !important;
    }

    .seniat-wrapper #banner {
        width: 100%;
        display: block;
    }

    /* Captcha label */
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

    /* Separador de secciones */
    .seniat-wrapper .section-separator {
        border: 0;
        border-top: 1px solid rgba(0, 0, 0, 0.1);
        margin: 1rem 0;
    }

    /* Campos deshabilitados del simulador */
    .seniat-wrapper .sim-disabled {
        opacity: 0.5;
        pointer-events: none;
    }

    /* Nota informativa del simulador */
    .seniat-wrapper .sim-note {
        background-color: #e7f1ff;
        border: 1px solid #b6d4fe;
        border-radius: 6px;
        padding: 10px 14px;
        font-size: 13px;
        color: #084298;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .seniat-wrapper .sim-note i {
        font-size: 16px;
    }

    /* Botón toggle de contraseña */
    .seniat-wrapper .btn-toggle-pass {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        cursor: pointer;
        padding: 4px;
        z-index: 5;
        display: flex;
        align-items: center;
    }
    .seniat-wrapper .btn-toggle-pass:hover svg {
        fill: #495057;
    }

    /* Requisitos de contraseña */
    .seniat-wrapper .password-requirements {
        font-size: 13px;
        color: #6c757d;
        padding: 8px 12px;
        padding-left: 12px;
        background-color: #f8f9fa;
        border-radius: 6px;
        margin-top: 4px;
        list-style-position: inside;
    }

    .seniat-wrapper .password-requirements li {
        margin-bottom: 2px;
    }

    .seniat-wrapper .password-requirements li.valid {
        color: #198754;
    }

    .seniat-wrapper .password-requirements li.invalid {
        color: #dc3545;
    }

    /* =========================================
       MODAL SENIAT NUEVO (No-Legacy)
    ========================================= */
    .seniat-wrapper .seniat-modal .modal-content {
        border-radius: 8px;
        border: none;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    .seniat-wrapper .seniat-modal .modal-header {
        border-bottom: 1px solid #dee2e6;
        padding: 1rem 1.5rem;
        align-items: center;
    }

    .seniat-wrapper .seniat-modal .modal-title {
        color: #212529;
        font-weight: 500;
        font-size: 1.25rem;
        margin: 0;
    }

    .seniat-wrapper .seniat-modal .btn-close-custom {
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

    .seniat-wrapper .seniat-modal .btn-close-custom:hover {
        background-color: #e2e6ea;
    }

    .seniat-wrapper .seniat-modal .alert-seniat-danger {
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

    .seniat-wrapper .seniat-modal .alert-seniat-danger i {
        font-size: 1.1rem;
        line-height: 1.25;
        color: #842029;
    }

    .seniat-wrapper .seniat-modal .alert-seniat-success {
        background-color: #d1e7dd;
        border: 1px solid #badbcc;
        color: #0f5132;
        border-radius: 6px;
        padding: 1rem;
        display: flex;
        align-items: flex-start;
        gap: 0.5rem;
        font-size: 0.95rem;
        margin-bottom: 0;
    }

    .seniat-wrapper .seniat-modal .alert-seniat-success i {
        font-size: 1.1rem;
        line-height: 1.25;
        color: #0f5132;
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
            <div class="page-holder align-items-center py-2">
                <div class="card">
                    <div class="card-header">
                        <div class="card-heading">
                            <strong>Registro Usuario</strong>
                        </div>
                    </div>
                    <div class="card-body">

                        <form id="formRegistroPaso2" novalidate>

                            <!-- ============================
                                 Sección 1: Datos de acceso
                            ============================= -->
                            <div style="color: rgb(33, 37, 41); font-size: 16px; margin-bottom: 8px;">
                                Complete los siguientes datos para crear su cuenta de acceso:
                            </div>
                            <br>

                            <!-- Campo: Usuario -->
                            <div class="row mb-3">
                                <div class="col-sm-6">
                                    <div class="form-floating">
                                        <input id="usuario" name="usuario" type="text" maxlength="30"
                                            class="form-control form-control-sm" placeholder=" " required value="">
                                        <label for="usuario">Usuario</label>
                                    </div>
                                </div>
                            </div>

                            <!-- Campo: Clave -->
                            <div class="row mb-3">
                                <div class="col-sm-6">
                                    <div class="form-floating" style="position:relative;">
                                        <input id="clave" name="clave" type="password" maxlength="29"
                                            class="form-control form-control-sm" placeholder=" " required value=""
                                            style="padding-right: 42px;">
                                        <label for="clave">Clave</label>
                                        <button type="button" id="toggleClave" class="btn-toggle-pass" title="Mostrar/ocultar clave">
                                            <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="#6c757d" viewBox="0 0 16 16">
                                                <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/>
                                                <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/>
                                            </svg>
                                        </button>
                                    </div>
                                    <ul class="password-requirements" id="passwordRequirements">
                                        <li id="reqLength" class="invalid">Mínimo 8 caracteres</li>
                                        <li id="reqUpper" class="invalid">Al menos una mayúscula</li>
                                        <li id="reqLower" class="invalid">Al menos una minúscula</li>
                                        <li id="reqSpecial" class="invalid">Un carácter especial (* - +)</li>
                                        <li id="reqNumber" class="invalid">Al menos un número</li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Campo: Nro. Celular (deshabilitado en simulador) -->
                            <div class="row mb-3 sim-disabled">
                                <div class="col-sm-6">
                                    <div class="form-floating">
                                        <input id="nroCelular" name="nroCelular" type="text" maxlength="11"
                                            class="form-control form-control-sm" placeholder=" " value="" disabled>
                                        <label for="nroCelular">Nro. Celular</label>
                                    </div>
                                </div>
                            </div>

                            <hr class="section-separator">

                            <!-- Nota del simulador -->
                            <div class="sim-note">
                                <i class="bi bi-info-circle-fill"></i>
                                <span>En el simulador educativo, solo se requiere usuario y contraseña. Los demás campos se muestran con fines ilustrativos.</span>
                            </div>

                            <!-- ============================
                                 Sección 2: Preguntas de Seguridad (deshabilitada en simulador)
                            ============================= -->
                            <div class="sim-disabled">
                                <div style="color: rgb(33, 37, 41); font-size: 16px; margin-bottom: 8px;">
                                    Registre las preguntas de seguridad para la recuperación de su información:
                                </div>
                                <br>

                                <!-- Pregunta 1 (Selección) -->
                                <div class="row mb-3">
                                    <div class="col-sm-6">
                                        <div class="form-floating">
                                            <select id="pregunta1" name="pregunta1" class="form-control form-control-sm" disabled>
                                                <option value="" selected>Seleccione una pregunta</option>
                                                <option value="1">¿Cuál es el nombre de su primera mascota?</option>
                                                <option value="2">¿Cuál es el nombre de su mejor amigo de la infancia?</option>
                                                <option value="3">¿Cuál es su comida favorita?</option>
                                                <option value="4">¿Cuál es el nombre de su escuela primaria?</option>
                                                <option value="5">¿Cuál es su color favorito?</option>
                                            </select>
                                            <label for="pregunta1">Pregunta de Seguridad 1</label>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-floating">
                                            <input id="respuesta1" name="respuesta1" type="text" maxlength="100"
                                                class="form-control form-control-sm" placeholder=" " value="" disabled>
                                            <label for="respuesta1">Respuesta</label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Pregunta 2 (Selección) -->
                                <div class="row mb-3">
                                    <div class="col-sm-6">
                                        <div class="form-floating">
                                            <select id="pregunta2" name="pregunta2" class="form-control form-control-sm" disabled>
                                                <option value="" selected>Seleccione una pregunta</option>
                                                <option value="6">¿En qué ciudad nació?</option>
                                                <option value="7">¿Cuál es el nombre de su madre?</option>
                                                <option value="8">¿Cuál fue su primer número de teléfono?</option>
                                                <option value="9">¿Cuál es el nombre de su película favorita?</option>
                                                <option value="10">¿Cuál es su deporte favorito?</option>
                                            </select>
                                            <label for="pregunta2">Pregunta de Seguridad 2</label>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-floating">
                                            <input id="respuesta2" name="respuesta2" type="text" maxlength="100"
                                                class="form-control form-control-sm" placeholder=" " value="" disabled>
                                            <label for="respuesta2">Respuesta</label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Pregunta 3 (Personalizada) -->
                                <div class="row mb-3">
                                    <div class="col-sm-6">
                                        <div class="form-floating">
                                            <input id="pregunta3" name="pregunta3" type="text" maxlength="150"
                                                class="form-control form-control-sm" placeholder=" " value="" disabled>
                                            <label for="pregunta3">Pregunta Personalizada</label>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-floating">
                                            <input id="respuesta3" name="respuesta3" type="text" maxlength="100"
                                                class="form-control form-control-sm" placeholder=" " value="" disabled>
                                            <label for="respuesta3">Respuesta</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr class="section-separator">

                            <!-- ============================
                                 Sección 3: Captcha + Botones (deshabilitado en simulador)
                            ============================= -->
                            <div class="row row-cols-auto g-1 sim-disabled">
                                <div class="col-sm-3">
                                    <label class="imageninput" id="captchaText"
                                        style="font-size:25px; width:120px; height:60px; line-height:60px;">ab3k9m</label>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-floating">
                                        <input id="floatingInputCap" name="captcha" type="text" placeholder=" "
                                            maxlength="6" class="form-control" value="" disabled>
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
                                            <input type="button" id="btnAceptar" value="Aceptar"
                                                class="btn btn-sm btn-danger">
                                            &nbsp;
                                            <a id="btnRegresar" class="btn btn-sm btn-light"
                                                href="<?= base_url('/simulador/registro/contribuyente') ?>">Regresar</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <!-- Fin del formulario -->

                    </div>
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
            /* Botón de cerrar */
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
            /* Alertas del modal */
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
            .seniat-wrapper .seniat-modal-box .alert-seniat-success {
                background-color: #d1e7dd;
                border: 1px solid #badbcc;
                color: #0f5132;
                border-radius: 6px;
                padding: 1rem;
                display: flex;
                align-items: flex-start;
                gap: 0.5rem;
                font-size: 0.95rem;
                margin-bottom: 0;
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
                const claveInput = document.getElementById('clave');
                const nroCelularInput = document.getElementById('nroCelular');
                const btnAceptar = document.getElementById('btnAceptar');

                // Toggle mostrar/ocultar contraseña
                const toggleBtn = document.getElementById('toggleClave');
                if (toggleBtn && claveInput) {
                    toggleBtn.addEventListener('click', () => {
                        const isPassword = claveInput.type === 'password';
                        claveInput.type = isPassword ? 'text' : 'password';
                        // Cambiar ícono: ojo abierto ↔ ojo tachado
                        document.getElementById('eyeIcon').innerHTML = isPassword
                            ? '<path d="M13.359 11.238C15.06 9.72 16 8 16 8s-3-5.5-8-5.5a7.028 7.028 0 0 0-2.79.588l.77.771A5.944 5.944 0 0 1 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.134 13.134 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755-.165.165-.337.328-.517.486l.708.709z"/><path d="M11.297 9.176a3.5 3.5 0 0 0-4.474-4.474l.823.823a2.5 2.5 0 0 1 2.829 2.829l.822.822zm-2.943 1.299l.822.822a3.5 3.5 0 0 1-4.474-4.474l.823.823a2.5 2.5 0 0 0 2.829 2.829z"/><path d="M3.35 5.47c-.18.16-.353.322-.518.487A13.134 13.134 0 0 0 1.172 8l.195.288c.335.48.83 1.12 1.465 1.755C4.121 11.332 5.881 12.5 8 12.5c.716 0 1.39-.133 2.02-.36l.77.772A7.029 7.029 0 0 1 8 13.5C3 13.5 0 8 0 8s.939-1.721 2.641-3.238l.708.709zm10.296 8.884l-12-12 .708-.708 12 12-.708.708z"/>'
                            : '<path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/><path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/>';
                    });
                }

                // ============================
                // Validación de Contraseña en tiempo real
                // ============================
                const requirements = {
                    reqLength: (v) => v.length >= 8,
                    reqUpper: (v) => /[A-Z]/.test(v),
                    reqLower: (v) => /[a-z]/.test(v),
                    reqSpecial: (v) => /[_*\-+]/.test(v),
                    reqNumber: (v) => /[0-9]/.test(v),
                };

                if (claveInput) {
                    // Sanitizar contraseña (misma lógica que sanitize.js para text inputs)
                    // Se aplica aquí porque sanitize.js no procesa inputs type="password"
                    // y esta contraseña se almacena como texto plano
                    claveInput.addEventListener('input', () => {
                        const original = claveInput.value;
                        const sanitized = original.replace(/['"`;\\\<\>{}|~^&#$%]/g, '');
                        if (sanitized !== original) {
                            claveInput.value = sanitized;
                        }
                    });

                    claveInput.addEventListener('keyup', () => {
                        const val = claveInput.value;
                        let allValid = true;

                        for (const [id, test] of Object.entries(requirements)) {
                            const li = document.getElementById(id);
                            if (test(val)) {
                                li.classList.remove('invalid');
                                li.classList.add('valid');
                            } else {
                                li.classList.remove('valid');
                                li.classList.add('invalid');
                                allValid = false;
                            }
                        }
                    });
                }




                const modalOverlay = document.getElementById('modalOverlay');

                // Cerrar modal
                document.getElementById('modalCloseBtn').addEventListener('click', () => {
                    modalOverlay.classList.remove('show');
                });
                modalOverlay.addEventListener('click', (e) => {
                    if (e.target === modalOverlay) modalOverlay.classList.remove('show');
                });

                // ============================
                // Helper para mostrar modal
                // ============================
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

                // ============================
                // Botón Aceptar (solo valida usuario y clave en el simulador)
                // ============================
                if (btnAceptar) {
                    btnAceptar.addEventListener('click', () => {
                        const usuario = document.getElementById('usuario').value.trim();
                        const clave = document.getElementById('clave').value;

                        // Validación de campos requeridos
                        if (!usuario || !clave) {
                            showModal('Los campos Usuario y Clave son obligatorios.', 'danger');
                            return;
                        }

                        // Validación de contraseña
                        const passValid = Object.values(requirements).every(test => test(clave));
                        if (!passValid) {
                            showModal('La clave no cumple con los requisitos mínimos de seguridad.', 'danger');
                            return;
                        }

                        // Enviar al backend
                        btnAceptar.setAttribute('disabled', 'true');
                        const formData = new FormData();
                        formData.append('usuario', usuario);
                        formData.append('clave', clave);

                        fetch('<?= base_url('/simulador/registro/contribuyente/paso-2/guardar') ?>', {
                            method: 'POST',
                            body: formData
                        })
                        .then(r => r.json())
                        .then(data => {
                            if (data.ok) {
                                window.location.href = data.redirect;
                            } else {
                                showModal(data.msg || 'Error inesperado.', 'danger');
                                btnAceptar.removeAttribute('disabled');
                            }
                        })
                        .catch(() => {
                            showModal('Error de conexión. Intente de nuevo.', 'danger');
                            btnAceptar.removeAttribute('disabled');
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