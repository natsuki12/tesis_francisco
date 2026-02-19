<!DOCTYPE html>
<html lang="es" translate="no">

<head>

    <meta charset="utf-8" />
    <title>iSeniatV2</title>
    <meta content="no-cache, no-store, must-revalidate" http-equiv="Cache-Control" />
    <meta content="no-cache" http-equiv="Pragma" />
    <meta content="0" http-equiv="Expires" />
    <meta content="0" http-equiv="Last-Modified" />
    <meta content="width=device-width, initial-scale=1" name="viewport" />
    <meta content="no-referrer" name="referrer" />
    <link
        href="data:image/x-icon;base64,AAABAAEAEBAAAAEAIABoBAAAFgAAACgAAAAQAAAAIAAAAAEAIAAAAAAAAAQAAAAAAAAAAAAAAAAAAAAAAAD////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////+///////////////////////////////////c4uj/6+vr/+3u7v/t7e3/7O3t/+7u7v/4+Pj///79/+jk5P/L2Oj/8fX5///////+/v////////n5/P/p5un/AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=="
        rel="icon" type="image/x-icon" />
    <link href="https://dgpatrimonios.seniat.gob.ve/auth" rel="canonical" />
    <link href="<?= asset('css/simulator/steps/servicios_declaracion/bootstrap-icons.css') ?>" rel="stylesheet" />
    <link href="<?= asset('css/simulator/steps/servicios_declaracion/bootstrap.min.css') ?>" rel="stylesheet" />
    <link href="<?= asset('css/simulator/steps/servicios_declaracion/servicios_declaracion.css') ?>" rel="stylesheet" />
</head>

<body>

    <app-root>
        <router-outlet>
        </router-outlet>
        <app-login>
            <div class="imgLogin">
                <div class="page-holder align-items-center py-4 vh-100"
                    style="opacity:.9;margin-top:16vh">
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
                                            Coloque el usuario y la clave registrada para ingresar como contribuyente a
                                            través del Portal Fiscal del SENIAT
                                        <form id="loginForm">
                                            <div class="form-floating mb-3">
                                                <input
                                                    class="form-control form-control-sm"
                                                    id="floatingInput" maxlength="30"
                                                    placeholder="Usuario*" required="" type="text" value="" />
                                                <label for="floatingInput">
                                                    Usuario
                                                </label>
                                            </div>
                                            <div class="input-group mb-3">
                                                <div class="form-floating">
                                                    <input
                                                        class="form-control form-control-sm"
                                                        id="floatingPassword" maxlength="30"
                                                        placeholder="Password" required="" type="password" />
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
                                                    <label class="imageninput">
                                                        ojhtxf
                                                    </label>
                                                </div>
                                                <div class="col-md">
                                                    <div class="form-floating">
                                                        <input
                                                            class="form-control form-control-sm"
                                                            id="floatingInputCap"
                                                            maxlength="6"
                                                            placeholder="Ingrese el código mostrado en la imagen"
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
                                                        <button class="btn btn-seniat"
                                                            id="btnAceptar" type="button">
                                                            Aceptar
                                                        </button>
                                                        <a href="<?= base_url('/step_01_seniat_index') ?>" class="btn btn-seniat" type="button">
                                                            Salir
                                                        </a>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col align-items-center d-flex justify-content mt-3">
                                                        <a class="btn btn-light">
                                                            Regístrese
                                                        </a>
                                                        <a class="btn btn-light">
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
                // Ensure enabled
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

                    // Simulation of action
                    console.log('Login attempt:', { usuario, clave, captcha });
                    alert(`¡Datos Enviados!\n\nUsuario: ${usuario}\nClave: ${clave}\nCaptcha: ${captcha}\n\n(Esta es una simulación visual como solicitaste)`);
                });
            }
        });
    </script>
</body>

</html>