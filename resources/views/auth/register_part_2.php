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
$extraCss = '
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="'.asset('css/auth/progress-bar-register.css?v='.time()).'">
<link rel="stylesheet" href="'.asset('css/auth/register.css?v='.time()).'">';

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

                <?php
                    $flashError = $_SESSION['flash_error'] ?? null;
                    $flashSuccessResend = $_SESSION['flash_success_resend'] ?? null;
                    $flashSeg = $_SESSION['flash_seg'] ?? null;
                    unset($_SESSION['flash_error'], $_SESSION['flash_vista'], $_SESSION['flash_success_resend'], $_SESSION['flash_seg']);
                ?>

                <?php if($flashSuccessResend): ?>
                    <div style="background-color: #d1e7dd; color: #0f5132; padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center; border: 1px solid #badbcc;">
                        <strong>¡Éxito!</strong> <?= $flashSuccessResend ?>
                    </div>
                <?php endif; ?>

                <?php if($flashError === 'espere_tiempo'): ?>
                    <div style="background-color: #fff3cd; color: #856404; padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center; border: 1px solid #ffeeba;">
                        <strong>Por favor espere:</strong> Debe esperar <?= $flashSeg ?> segundos para solicitar otro código.
                    </div>
                <?php endif; ?>
                <?php if($flashError === 'codigo_invalido'): ?>
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

                <div class="alert-info-spa" style="display:flex; justify-content:space-between; align-items:center; gap: 10px;">
                    <span><strong>¿No recibió el código?</strong> Revise su spam.</span>
                    <button type="button" onclick="document.getElementById('form-resend-code').submit();" style="background:none; border:none; color:#0d6efd; text-decoration:underline; cursor:pointer; padding:0; font-size:inherit; font-family:inherit;">Solicitar nuevo</button>
                </div>
            </div>

            <div class="spa-footer space-between">
                <a href="<?= base_url('/registro/atras') ?>" class="btn-spa btn-secondary" style="text-decoration:none; display:flex; align-items:center; justify-content:center;">Regresar</a>
                
                <button type="submit" id="btn-verificar" class="btn-spa btn-primary">Verificar código</button>
            </div>
        </form>

        <!-- Formulario oculto para reenvío (fuera del form principal para evitar anidación) -->
        <form id="form-resend-code" action="<?= base_url('/registro') ?>" method="POST" class="d-none">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="resend_code">
        </form>
    </div>

</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/guest.php';
?>