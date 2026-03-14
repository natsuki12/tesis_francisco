<?php
declare(strict_types=1);

$pageTitle = 'Servicios de Declaración — Simulador';
$activePage = 'simulador';

// Carga Bootstrap + estilos SENIAT en <head> con @layer para no contaminar layout
$extraCss = '
<style>
    @import url("' . asset('css/simulator/seniat_actual/servicios_declaracion/bootstrap-icons.css') . '") layer(seniat-bootstrap);
    @import url("' . asset('css/simulator/seniat_actual/servicios_declaracion/bootstrap.min.css') . '") layer(seniat-bootstrap);
    @import url("' . asset('css/simulator/seniat_actual/servicios_declaracion/servicios_declaracion.css') . '") layer(seniat-bootstrap);
</style>';

ob_start();
?>

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
        font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        font-size: 1rem;
        line-height: 1.5;
        color: #212529;
        -webkit-text-size-adjust: 100%;
    }

    .seniat-wrapper .seniat-scope-bs *,
    .seniat-wrapper .seniat-scope-bs *::before,
    .seniat-wrapper .seniat-scope-bs *::after {
        box-sizing: border-box;
    }

    /* Re-declarar estilos Bootstrap críticos fuera de @layer
       para que tengan prioridad correcta dentro del wrapper */
    .seniat-wrapper .form-floating {
        position: relative;
    }

    /* Ocultar placeholder cuando el label está encima (efecto floating) */
    .seniat-wrapper .form-floating>.form-control::placeholder,
    .seniat-wrapper .form-floating>input::placeholder {
        color: transparent !important;
    }

    .seniat-wrapper .form-floating>.form-control:focus::placeholder,
    .seniat-wrapper .form-floating>input:focus::placeholder {
        color: #6c757d !important;
    }

    .seniat-wrapper .form-floating>.form-control {
        height: calc(3.5rem + 2px);
        padding: 1rem .75rem;
    }

    .seniat-wrapper .form-floating>label {
        position: absolute;
        top: 0;
        left: 0;
        height: 100%;
        padding: 1rem .75rem;
        pointer-events: none;
        border: 1px solid transparent;
        transform-origin: 0 0;
        transition: opacity .1s ease-in-out, transform .1s ease-in-out;
        color: #6c757d !important;
        font-size: 1rem !important;
        font-weight: 400 !important;
        font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif !important;
    }

    .seniat-wrapper .form-floating>.form-control:focus~label,
    .seniat-wrapper .form-floating>.form-control:not(:placeholder-shown)~label {
        opacity: .65;
        transform: scale(.85) translateY(-.5rem) translateX(.15rem);
    }

    /* Mover el texto del input hacia abajo cuando el label está flotando */
    .seniat-wrapper .form-floating>.form-control:not(:placeholder-shown) {
        padding-top: 1.625rem;
        padding-bottom: .625rem;
    }
    .seniat-wrapper .form-floating>.form-control:focus {
        padding-top: 1.625rem;
        padding-bottom: .625rem;
    }

    .seniat-wrapper .form-control {
        display: block;
        width: 100%;
        padding: .375rem .75rem;
        font-size: 1rem;
        font-weight: 400;
        line-height: 1.5;
        color: #212529;
        background-color: #fff;
        border: 1px solid #ced4da;
        border-radius: .375rem;
        transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out;
        appearance: none;
    }

    .seniat-wrapper .form-control:focus {
        color: #212529;
        background-color: #fff;
        border-color: #86b7fe;
        outline: 0;
        box-shadow: 0 0 0 .25rem rgba(13, 110, 253, .25);
    }

    .seniat-wrapper .input-group {
        position: relative;
        display: flex;
        flex-wrap: wrap;
        align-items: stretch;
        width: 100%;
    }

    .seniat-wrapper .input-group>.form-control,
    .seniat-wrapper .input-group>.form-floating {
        position: relative;
        flex: 1 1 auto;
        width: 1%;
        min-width: 0;
    }

    .seniat-wrapper .input-group-text {
        display: flex;
        align-items: center;
        padding: .375rem .75rem;
        font-size: 1rem;
        font-weight: 400;
        line-height: 1.5;
        color: #212529;
        text-align: center;
        white-space: nowrap;
        background-color: #e9ecef;
        border: 1px solid #ced4da;
        border-radius: 0 .375rem .375rem 0;
    }

    .seniat-wrapper .input-group>.form-floating:not(:last-child)>.form-control {
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }

    .seniat-wrapper .card {
        position: relative;
        display: flex;
        flex-direction: column;
        min-width: 0;
        word-wrap: break-word;
        background-color: #fff;
        background-clip: border-box;
        border: none;
        border-radius: 1rem;
    }

    .seniat-wrapper .card-header:first-child {
        border-radius: calc(1rem - 1px) calc(1rem - 1px) 0 0;
    }

    .seniat-wrapper .card-body {
        flex: 1 1 auto;
        padding: 1rem;
    }

    .seniat-wrapper .card-header {
        padding: .5rem 1rem;
        margin-bottom: 0;
        background-color: rgba(0, 0, 0, .03);
    }

    .seniat-wrapper .card-footer {
        padding: .5rem 1rem;
        background-color: rgba(0, 0, 0, .03);
    }

    /* Bootstrap grid basics */
    .seniat-wrapper .container {
        width: 100%;
        padding-right: .75rem;
        padding-left: .75rem;
        margin-right: auto;
        margin-left: auto;
    }

    .seniat-wrapper .row {
        display: flex;
        flex-wrap: wrap;
        margin-top: 0;
        margin-right: -.75rem;
        margin-left: -.75rem;
    }

    .seniat-wrapper .row>* {
        flex-shrink: 0;
        width: 100%;
        max-width: 100%;
        padding-right: .75rem;
        padding-left: .75rem;
    }

    .seniat-wrapper .col {
        flex: 1 0 0%;
    }

    .seniat-wrapper .col-md {
        flex: 1 0 0%;
    }

    .seniat-wrapper .col-md-3 {
        flex: 0 0 auto;
        width: 25%;
    }

    @media (min-width: 576px) {
        .seniat-wrapper .col-sm-6 {
            flex: 0 0 auto;
            width: 50%;
        }

        .seniat-wrapper .px-sm-4 {
            padding-right: 1.5rem;
            padding-left: 1.5rem;
        }
    }

    /* Bootstrap utilities */
    .seniat-wrapper .d-flex {
        display: flex;
    }

    .seniat-wrapper .align-items-center {
        align-items: center;
    }

    .seniat-wrapper .justify-content {
        justify-content: flex-start;
    }

    .seniat-wrapper .mb-3 {
        margin-bottom: 1rem;
    }

    .seniat-wrapper .mt-3 {
        margin-top: 1rem;
    }

    .seniat-wrapper .g-2 {
        --bs-gutter-x: .5rem;
        --bs-gutter-y: .5rem;
    }

    .seniat-wrapper .g-2>* {
        padding-right: calc(var(--bs-gutter-x)*.5);
        padding-left: calc(var(--bs-gutter-x)*.5);
        margin-top: var(--bs-gutter-y);
    }

    .seniat-wrapper .mx-5 {
        margin-right: 3rem;
        margin-left: 3rem;
    }

    .seniat-wrapper .py-4 {
        padding-top: 1.5rem;
        padding-bottom: 1.5rem;
    }

    .seniat-wrapper .p-sm-3 {
        padding: 1rem;
    }

    .seniat-wrapper .px-lg-3 {
        padding-right: 1rem;
        padding-left: 1rem;
    }

    .seniat-wrapper .py-lg-3 {
        padding-top: 1rem;
        padding-bottom: 1rem;
    }

    .seniat-wrapper .h-100 {
        height: 100%;
    }

    .seniat-wrapper .text-muted {
        color: #6c757d;
    }

    .seniat-wrapper .text-sm {
        font-size: .875rem;
    }

    .seniat-wrapper .text-danger {
        color: #dc3545;
    }

    .seniat-wrapper .text-color {
        color: #212529;
    }

    /* Button overrides */
    .seniat-wrapper .btn {
        display: inline-block;
        font-weight: 400;
        line-height: 1.5;
        text-align: center;
        text-decoration: none;
        vertical-align: middle;
        cursor: pointer;
        user-select: none;
        border: 1px solid transparent;
        padding: .375rem .75rem;
        font-size: 1rem;
        border-radius: .375rem;
        transition: color .15s ease-in-out, background-color .15s ease-in-out;
    }

    .seniat-wrapper .btn-light {
        color: #000;
        background-color: #f8f9fa;
        border-color: #f8f9fa;
    }

    .seniat-wrapper .btn-light:hover {
        color: #000;
        background-color: #e2e6ea;
        border-color: #dae0e5;
    }
</style>

<div class="seniat-wrapper">
    <div class="seniat-scope-bs">

        <app-root>
            <router-outlet>
            </router-outlet>
            <app-login>
                <div class="imgLogin">
                    <div class="page-holder align-items-center py-4 h-100" style="opacity:.9;padding-top:10%">
                        <div class="container mx-5">
                            <div class="row align-items-center">
                                <div class="col-sm-6 px-sm-4">
                                    <div class="card sombra">
                                        <div class="card-header">
                                            <div class="card-heading text-color">
                                                Inicio de Sesión
                                            </div>
                                        </div>
                                        <div class="card-body p-sm-3">
                                            <p class="text-muted text-sm mb-3">
                                                Coloque el usuario y la clave registrada para ingresar como
                                                contribuyente a
                                                través del Portal Fiscal del SENIAT
                                            <form id="loginForm">
                                                <div class="form-floating mb-3">
                                                    <input class="form-control form-control-sm" id="floatingInput"
                                                        maxlength="30" placeholder=" " required="" type="text"
                                                        value="" />
                                                    <label for="floatingInput">
                                                        Usuario
                                                    </label>
                                                </div>
                                                <div class="input-group mb-3">
                                                    <div class="form-floating">
                                                        <input class="form-control form-control-sm"
                                                            id="floatingPassword" maxlength="30" placeholder=" "
                                                            required="" type="password" />
                                                        <label for="floatingPassword">
                                                            Clave
                                                        </label>
                                                    </div>
                                                    <span class="input-group-text">
                                                        <i class="bi bi-eye">
                                                        </i>
                                                    </span>
                                                </div>
                                                <div class="row g-2">
                                                    <div class="col-md-3">
                                                        <label class="imageninput"
                                                            style="font-size:25px; width:120px; height:60px; line-height:60px;">
                                                            ojht6y
                                                        </label>
                                                    </div>
                                                    <div class="col-md">
                                                        <div class="form-floating">
                                                            <input class="form-control form-control-sm"
                                                                id="floatingInputCap" maxlength="6" placeholder=" "
                                                                required="" type="text" value="" />
                                                            <label for="floatingInputCap">
                                                                Ingrese el código mostrado en la imagen
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <br />
                                                <div class="form-floating mb-3">
                                                    <div class="row">
                                                        <div class="col align-items-center">
                                                            <button class="btn btn-seniat" id="btnAceptar"
                                                                type="button">
                                                                Aceptar
                                                            </button>
                                                            <a href="<?= base_url('/simulador') ?>"
                                                                class="btn btn-seniat" type="button">
                                                                Salir
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col align-items-center d-flex justify-content mt-3">
                                                            <a class="btn btn-light"
                                                                href="<?= base_url('/simulador/registro/contribuyente') ?>">
                                                                Regístrese
                                                            </a>
                                                            <a class="btn btn-light" id="btnOlvido" href="#" style="cursor:pointer;">
                                                                ¿Olvidó su Información?
                                                            </a>
                                                            <a class="btn btn-light">
                                                                Registrar Preguntas
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                            </p>
                                        </div>
                                        <div class="card-footer px-lg-3 py-lg-3">
                                            <div class="text-sm text-muted">
                                                <span class="text-danger">
                                                    *
                                                </span>
                                                Este sistema está restringido a personas autorizadas. El acceso o uso no
                                                autorizado se considera un acto criminal.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </app-login>
        </app-root>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const btnAceptar = document.getElementById('btnAceptar');
                const inputUsuario = document.getElementById('floatingInput');
                const inputClave = document.getElementById('floatingPassword');
                const inputCaptcha = document.getElementById('floatingInputCap');

                if (btnAceptar) {
                    btnAceptar.removeAttribute('disabled');

                    btnAceptar.addEventListener('click', (e) => {
                        e.preventDefault();

                        const usuario = inputUsuario ? inputUsuario.value : '';
                        const clave = inputClave ? inputClave.value : '';
                        const captcha = inputCaptcha ? inputCaptcha.value : '';

                        if (!usuario || !clave || !captcha) {
                            alert('Por favor, complete todos los campos (Usuario, Clave y Captcha).');
                            return;
                        }

                        console.log('Login attempt:', { usuario, clave, captcha });
                        alert(`¡Datos Enviados!\n\nUsuario: ${usuario}\nClave: ${clave}\nCaptcha: ${captcha}\n\n(Esta es una simulación visual como solicitaste)`);
                    });
                }

                // ¿Olvidó su Información?
                const btnOlvido = document.getElementById('btnOlvido');
                const olvidoBody = document.getElementById('olvidoBody');

                const usuarioSeniat = <?= json_encode($usuarioSeniat ?? null) ?>;
                const passwordRif   = <?= json_encode($passwordRif ?? null) ?>;

                if (btnOlvido) {
                    btnOlvido.addEventListener('click', (e) => {
                        e.preventDefault();

                        if (!usuarioSeniat || !passwordRif) {
                            olvidoBody.innerHTML = `
                                <div class="olvido-note olvido-note--info">
                                    <strong>ℹ️ Nota educativa:</strong> En el portal SENIAT real, la recuperación de credenciales requiere un proceso de cambio de contraseña mediante correo electrónico y preguntas de seguridad. Para fines prácticos del simulador educativo, se le mostrarían sus datos directamente.
                                </div>
                                <div class="olvido-note olvido-note--warn">
                                    <strong>⚠️</strong> Usted todavía no se ha registrado en el sistema simulado del SENIAT. Utilice el botón "Regístrese" para crear sus credenciales de acceso.
                                </div>
                            `;
                        } else {
                            olvidoBody.innerHTML = `
                                <div class="olvido-note olvido-note--info">
                                    <strong>ℹ️ Nota educativa:</strong> En el portal SENIAT real, la recuperación de credenciales requiere un proceso de cambio de contraseña mediante correo electrónico y preguntas de seguridad. Para fines prácticos del simulador educativo, se le muestran sus datos directamente.
                                </div>
                                <div class="olvido-cred">
                                    <div style="margin-bottom: 8px;">
                                        <strong>Usuario:</strong> <span class="olvido-val">${usuarioSeniat}</span>
                                    </div>
                                    <div>
                                        <strong>Clave:</strong> <span class="olvido-val">${passwordRif}</span>
                                    </div>
                                </div>
                            `;
                        }

                        window.modalManager.open('olvidoDialog');
                    });
                }
            });
        </script>

    </div><!-- /.seniat-scope-bs -->
</div><!-- /.seniat-wrapper -->

<!-- Dialog: ¿Olvidó su Información? (Estilo del sistema, fuera del wrapper SENIAT) -->
<style>
    .olvido-note {
        border-radius: var(--radius-xs);
        padding: 12px 16px;
        font-family: var(--font-ui);
        font-size: var(--text-sm);
        margin-bottom: 16px;
        line-height: 1.6;
    }
    .olvido-note--warn {
        background-color: var(--amber-50);
        border: 1px solid var(--amber-100);
        color: var(--amber-600);
    }
    .olvido-note--info {
        background-color: var(--blue-50);
        border: 1px solid var(--blue-100);
        color: var(--blue-600);
    }
    .olvido-cred {
        background-color: var(--gray-50);
        border: 1px solid var(--gray-200);
        border-radius: var(--radius-xs);
        padding: 14px 18px;
        font-family: var(--font-ui);
        font-size: var(--text-md);
    }
    .olvido-cred strong {
        color: var(--gray-800);
    }
    .olvido-val {
        color: var(--blue-500);
        font-family: monospace;
        font-weight: var(--weight-semibold);
        user-select: all;
    }
</style>

<dialog id="olvidoDialog" class="modal-base">
    <div class="modal-base__container" style="max-width:500px;">
        <div class="modal-base__header">
            <h3 class="modal-base__title" style="margin:0; font-size:1.25rem; font-weight:700; color:var(--gray-800);">
                Recuperación de Información</h3>
            <button class="modal-base__close" onclick="window.modalManager.close('olvidoDialog')">✕</button>
        </div>
        <div class="modal-base__body" id="olvidoBody">
            <!-- Contenido dinámico -->
        </div>
    </div>
</dialog>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/logged_layout.php';
?>