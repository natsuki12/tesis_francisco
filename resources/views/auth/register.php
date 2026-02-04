<?php
declare(strict_types=1);

/**
 * VISTA: Registro de Usuario - Paso 1
 * -----------------------------------
 * Muestra los términos y condiciones y el formulario de datos básicos.
 * Si hay errores (duplicados), los muestra aquí.
 */

$pageTitle = 'Registro de Usuario';

// Carga de estilos
$extraCss = '<link rel="stylesheet" href="'.asset('css/auth/progress-bar-register.css').'">
             <link rel="stylesheet" href="'.asset('css/auth/register.css').'">';

// Variable para la barra de progreso (Paso 1 activo)
$currentStep = 1;

// DETECCIÓN INTELIGENTE:
// Si el controlador nos devuelve con ?vista=datos (por un error),
// saltamos los términos y mostramos directo el formulario.
$mostrarDatos = isset($_GET['vista']) && $_GET['vista'] === 'datos';

ob_start();
?>

<?php include __DIR__ . '/_progress-bar-register.php'; ?>

<div id="registro-spa-container">

    <div id="vista-terminos" class="spa-view <?= $mostrarDatos ? 'd-none' : '' ?>">
        <h2 class="terms-title">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
                <polyline points="10 9 9 9 8 9"></polyline>
            </svg>
            Términos y condiciones
        </h2>

        <div class="terms-content">
            <p><strong>SIMULADOR EDUCATIVO - TÉRMINOS DE USO</strong></p>
            <p>Este sistema es una herramienta de simulación desarrollada exclusivamente con <strong>fines educativos y didácticos</strong>.</p>
            <p>1. <strong>NO ES UN SITIO OFICIAL:</strong> Este software no representa al portal oficial del SENIAT ni a ninguna entidad gubernamental.</p>
            <p>2. <strong>DATOS FICTICIOS:</strong> La información ingresada en este simulador es tratada localmente para efectos de práctica académica.</p>
            <p>Al continuar, el usuario reconoce y acepta que está interactuando con un entorno de pruebas.</p>
        </div>

        <div class="spa-footer">
            <a href="<?= base_url('/') ?>" class="btn-spa btn-secondary" style="text-decoration:none; display:flex; align-items:center; justify-content:center;">Declinar</a>
            <button type="button" id="btn-aceptar-terminos" class="btn-spa btn-primary">Aceptar</button>
        </div>
    </div>

    <div id="vista-datos" class="spa-view <?= $mostrarDatos ? '' : 'd-none' ?>">
        <h2 class="data-title">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
            </svg>
            Registro de usuario
        </h2>

        <form id="form-datos-basicos" action="<?= base_url('/registro') ?>" method="POST">
            
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="register_data">

            <?php if (isset($_GET['error'])): ?>
                <div style="background-color: #f8d7da; color: #721c24; padding: 12px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #f5c6cb; font-size: 0.9em; display: flex; align-items: center; gap: 10px;">
                    <svg style="flex-shrink:0" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    <div>
                        <?php if ($_GET['error'] === 'cedula_existe'): ?>
                            <strong>Ya registrado:</strong> Esta cédula ya pertenece a un usuario en el sistema.
                        <?php elseif ($_GET['error'] === 'email_existe'): ?>
                            <strong>Correo en uso:</strong> Este correo ya está registrado. <a href="<?= base_url('/login') ?>" style="color:#721c24; text-decoration:underline;">¿Iniciar sesión?</a>
                        <?php else: ?>
                            Ocurrió un error inesperado al procesar su solicitud.
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
            <div class="data-layout">
                <div class="data-sidebar">
                    <input type="hidden" name="rol" value="Estudiante">
                    <div class="data-option active" onclick="selectOption(this)">Estudiante</div>
                </div>

                <div class="data-form-container">
                    
                    <div class="form-group">
                        <label>Cédula de Identidad</label>
                        <div class="custom-input-group">
                            <div class="input-icon">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                            </div>
                            <select class="input-prefix-select" name="nacionalidad">
                                <option value="V" <?= (isset($_SESSION['temp_user']['nacionalidad']) && $_SESSION['temp_user']['nacionalidad'] == 'V') ? 'selected' : '' ?>>V</option>
                                <option value="E" <?= (isset($_SESSION['temp_user']['nacionalidad']) && $_SESSION['temp_user']['nacionalidad'] == 'E') ? 'selected' : '' ?>>E</option>
                            </select>
                            
                            <input type="number" name="cedula" class="form-control-spa" placeholder="Cédula" required 
                                   value="<?= isset($_SESSION['temp_user']['cedula']) ? e($_SESSION['temp_user']['cedula']) : '' ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Correo Electrónico</label>
                        <div class="custom-input-group">
                            <div class="input-icon">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                            </div>
                            <input type="email" name="email" class="form-control-spa" placeholder="ejemplo@correo.com" required 
                                   value="<?= isset($_SESSION['temp_user']['email']) ? e($_SESSION['temp_user']['email']) : '' ?>">
                        </div>
                    </div>

                    <div class="alert-info-spa">
                        <strong>Importante:</strong> El código de validación será enviado a este correo.
                    </div>

                </div>
            </div>

            <div class="spa-footer space-between">
                <button type="button" id="btn-regresar" class="btn-spa btn-secondary">Regresar</button>
                <button type="submit" id="btn-enviar-codigo" class="btn-spa btn-primary">Enviar código</button>
            </div>
        </form>
    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const btnAceptarTerminos = document.getElementById('btn-aceptar-terminos');
        const btnRegresar = document.getElementById('btn-regresar');
        
        const vistaTerminos = document.getElementById('vista-terminos');
        const vistaDatos = document.getElementById('vista-datos');
        const steps = document.querySelectorAll('.pr-step');

        // Función visual para la barra de progreso
        function actualizarBarraProgreso(paso) {
            if (paso === 2) {
                // Paso 1 Completado, vamos al Form
                if(steps[0]) {
                    steps[0].classList.remove('pr-step--active');
                    steps[0].classList.add('pr-step--completed');
                    const circle = steps[0].querySelector('.pr-circle');
                    if(circle) circle.innerHTML = `<svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="3" fill="none" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>`;
                }
                if(steps[1]) {
                    steps[1].classList.remove('pr-step--pending');
                    steps[1].classList.add('pr-step--active');
                }
            } else {
                // Regresar al inicio
                if(steps[0]) {
                    steps[0].classList.remove('pr-step--completed');
                    steps[0].classList.add('pr-step--active');
                    const circle = steps[0].querySelector('.pr-circle');
                    if(circle) circle.innerHTML = '1';
                }
                if(steps[1]) {
                    steps[1].classList.remove('pr-step--active');
                    steps[1].classList.add('pr-step--pending');
                }
            }
        }

        // Si PHP detectó error y mandó ?vista=datos, actualizamos la barra UI automáticamente
        <?php if ($mostrarDatos): ?>
            actualizarBarraProgreso(2);
        <?php endif; ?>

        // Click en Aceptar Términos
        btnAceptarTerminos.addEventListener('click', () => {
            vistaTerminos.classList.add('d-none');
            vistaDatos.classList.remove('d-none');
            actualizarBarraProgreso(2);
            window.scrollTo(0, 0);
        });

        // Click en Regresar (desde Datos hacia Términos)
        btnRegresar.addEventListener('click', () => {
            vistaDatos.classList.add('d-none');
            vistaTerminos.classList.remove('d-none');
            actualizarBarraProgreso(1);
        });
    });

    function selectOption(element) {
        document.querySelectorAll('.data-option').forEach(el => el.classList.remove('active'));
        element.classList.add('active');
    }
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/guest.php';
?>