<?php
declare(strict_types=1);

/**
 * VISTA: Registro de Usuario - Paso 2 (Verificación)
 * --------------------------------------------------
 * Solicita el código de 6 dígitos que fue generado en el controlador
 * y enviado (simuladamente) al usuario.
 */

$pageTitle = 'Verificación de Código';

// Carga de estilos
$extraCss = '<link rel="stylesheet" href="'.asset('css/auth/progress-bar-register.css').'">
             <link rel="stylesheet" href="'.asset('css/auth/register.css').'">';

// Variable para el partial de progreso (Paso 3 activo: Verificación)
$currentStep = 3;

ob_start();
?>

<?php include __DIR__ . '/_progress-bar-register.php'; ?>

<div id="registro-spa-container">

    <div id="vista-verificacion" class="spa-view">
        <h2 class="data-title">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
            </svg>
            Verificación de código
        </h2>

        <form action="<?= base_url('/registro') ?>" method="POST">
            
            <?= csrf_field() ?>
            
            <input type="hidden" name="action" value="verify_code">

            <div class="data-form-container">
                <p class="verification-instruction">
                    Ingrese el código de verificación enviado a su correo electrónico.
                </p>

                <?php if(isset($_GET['error']) && $_GET['error'] === 'codigo_invalido'): ?>
                    <div style="background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center; border: 1px solid #f5c6cb;">
                        <strong>⚠ Error:</strong> El código ingresado es incorrecto.
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label>Código de verificación</label>
                    <div class="custom-input-group">
                        <div class="input-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                        </div>
                        <input type="text" name="codigo_verificacion" class="form-control-spa" placeholder="Ingrese el código" required autocomplete="off">
                    </div>
                </div>

                <div class="alert-info-spa">
                    <strong>¿No recibió el código?</strong> Revise su carpeta de spam o solicite un nuevo código.
                </div>
            </div>

            <div class="spa-footer space-between">
                <a href="<?= base_url('/registro/atras') ?>" class="btn-spa btn-secondary" style="text-decoration:none; display:flex; align-items:center; justify-content:center;">Regresar</a>
                
                <div style="display: flex; gap: 10px;">
                    <button type="submit" id="btn-verificar" class="btn-spa btn-primary">Verificar código</button>
                    <a href="<?= base_url('/registro/parte3') ?>" class="btn-spa btn-primary" style="text-decoration:none; display:flex; align-items:center; justify-content:center; background: #6c757d;">Continuar (Test)</a>
                </div>
            </div>
        </form>
    </div>

</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/guest.php';
?>