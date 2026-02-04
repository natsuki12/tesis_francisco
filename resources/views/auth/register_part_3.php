<?php
declare(strict_types=1);

/**
 * VISTA: Registro de Usuario - Paso 3 (Datos Personales)
 * -------------------------------------------------------
 * Solicita los datos personales del usuario: nombres, apellidos, fecha de nacimiento,
 * género, sección a cursar, contraseña y confirmación de contraseña.
 */

$pageTitle = 'Datos Personales';

// Carga de estilos
$extraCss = '<link rel="stylesheet" href="'.asset('css/auth/progress-bar-register.css').'">
             <link rel="stylesheet" href="'.asset('css/auth/register.css').'">';

// Variable para el partial de progreso (Paso 4 activo: Datos personales)
$currentStep = 4;

ob_start();
?>

<?php include __DIR__ . '/_progress-bar-register.php'; ?>

<div id="registro-spa-container">

    <div id="vista-datos-personales" class="spa-view">
        <h2 class="data-title">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
            </svg>
            Datos personales
        </h2>

        <form action="<?= base_url('/registro') ?>" method="POST">
            
            <?= csrf_field() ?>
            
            <input type="hidden" name="action" value="personal_data">

            <div class="data-form-container">
                <p class="verification-instruction">
                    Complete sus datos personales para finalizar el registro.
                </p>

                <?php if(isset($_GET['error'])): ?>
                    <div style="background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center; border: 1px solid #f5c6cb;">
                        <strong>⚠ Error:</strong> Por favor, complete todos los campos correctamente.
                    </div>
                <?php endif; ?>

                <!-- Campo Nombres -->
                <div class="form-group">
                    <label>Nombres</label>
                    <div class="custom-input-group">
                        <div class="input-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                        </div>
                        <input type="text" name="nombres" class="form-control-spa" placeholder="Ingrese sus nombres" required autocomplete="given-name">
                    </div>
                </div>

                <!-- Campo Apellidos -->
                <div class="form-group">
                    <label>Apellidos</label>
                    <div class="custom-input-group">
                        <div class="input-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                        </div>
                        <input type="text" name="apellidos" class="form-control-spa" placeholder="Ingrese sus apellidos" required autocomplete="family-name">
                    </div>
                </div>

                <!-- Campo Fecha de Nacimiento -->
                <div class="form-group">
                    <label>Fecha de Nacimiento</label>
                    <div class="custom-input-group">
                        <div class="input-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                            </svg>
                        </div>
                        <input type="date" name="fecha_nacimiento" class="form-control-spa" required>
                    </div>
                </div>

                <!-- Campo Género -->
                <div class="form-group">
                    <label>Género</label>
                    <div class="custom-input-group">
                        <div class="input-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <path d="M12 16v-4"></path>
                                <path d="M12 8h.01"></path>
                            </svg>
                        </div>
                        <select name="genero" class="form-control-spa" required>
                            <option value="">Seleccione su género</option>
                            <option value="masculino">Masculino</option>
                            <option value="femenino">Femenino</option>
                            <option value="otro">Otro</option>
                            <option value="prefiero_no_decir">Prefiero no decir</option>
                        </select>
                    </div>
                </div>

                <!-- Campo Sección a Cursar -->
                <div class="form-group">
                    <label>Sección a cursar</label>
                    <div class="custom-input-group">
                        <div class="input-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
                            </svg>
                        </div>
                        <select name="seccion" class="form-control-spa" required>
                            <option value="">Seleccione sección</option>
                            <?php if (!empty($secciones)): ?>
                                <?php foreach ($secciones as $seccion): ?>
                                    <option value="<?= htmlspecialchars((string)$seccion['id']) ?>">
                                        <?= htmlspecialchars($seccion['materia'] . ' - Sec. ' . $seccion['seccion']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="" disabled>No hay secciones disponibles</option>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>

                <!-- Campo Contraseña -->
                <div class="form-group">
                    <label>Contraseña</label>
                    <div class="custom-input-group">
                        <div class="input-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                        </div>
                        <input type="password" name="password" class="form-control-spa" placeholder="Ingrese su contraseña" required autocomplete="new-password">
                    </div>
                </div>

                <!-- Campo Confirmar Contraseña -->
                <div class="form-group">
                    <label>Confirmar Contraseña</label>
                    <div class="custom-input-group">
                        <div class="input-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                        </div>
                        <input type="password" name="password_confirm" class="form-control-spa" placeholder="Confirme su contraseña" required autocomplete="new-password">
                    </div>
                </div>
            </div>

            <div class="spa-footer space-between">
                <a href="<?= base_url('/registro/atras') ?>" class="btn-spa btn-secondary" style="text-decoration:none; display:flex; align-items:center; justify-content:center;">Regresar</a>
                
                <button type="submit" id="btn-finalizar" class="btn-spa btn-primary">Finalizar registro</button>
            </div>
        </form>
    </div>

</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/guest.php';
?>
