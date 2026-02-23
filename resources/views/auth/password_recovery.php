<?php
declare(strict_types=1);

$error = $_GET['error'] ?? null;
$step = $step ?? 'email';

$pageTitle = 'Recuperar Contraseña - Simulador SENIAT';
$extraCss = '
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Outfit:wght@700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="'.asset('css/auth/password_recovery.css?v='.time()).'">';

ob_start();
?>

<main class="auth-page">

    <!-- ====== PANEL IZQUIERDO (Branding) ====== -->
    <aside class="auth-brand" aria-hidden="true">
        <div class="brand-backdrop">
            <div class="brand-glow brand-glow--1"></div>
            <div class="brand-glow brand-glow--2"></div>
            <div class="brand-grid"></div>
        </div>

        <div class="brand-content">
            <a href="<?= base_url('/') ?>" class="brand-logo" aria-label="Volver al inicio">
                <svg class="brand-logo-icon" viewBox="0 0 40 40" width="40" height="40" fill="none">
                    <rect width="40" height="40" rx="10" fill="rgba(255,255,255,0.12)"/>
                    <path d="M12 28V16l8-6 8 6v12H12z" stroke="#fff" stroke-width="1.8" fill="none"/>
                    <rect x="17" y="22" width="6" height="6" rx="1" stroke="#fff" stroke-width="1.5" fill="none"/>
                </svg>
                <span class="brand-logo-text">Simulador SENIAT</span>
            </a>

            <div class="brand-message">
                <h2 class="brand-title">Recupera tu acceso<br>de manera segura</h2>
                <p class="brand-desc">
                    Sigue los pasos para restablecer tu contraseña y continuar practicando en el simulador.
                </p>
            </div>

            <div class="brand-features">
                <div class="brand-feature">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    <span>Proceso rápido</span>
                </div>
                <div class="brand-feature">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    <span>Seguridad garantizada</span>
                </div>
                <div class="brand-feature">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    <span>Soporte disponible</span>
                </div>
            </div>

            <p class="brand-badge">Trabajo de Grado &mdash; UNIMAR</p>
        </div>
    </aside>

    <!-- ====== PANEL DERECHO (Formulario) ====== -->
    <section class="auth-panel">
        <div class="auth-panel-inner">

            <!-- Link volver -->
            <a href="<?= base_url('/login') ?>" class="back-link">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                Volver al inicio de sesión
            </a>

            <?php if ($step === 'email'): ?>
                <!-- Header -->
                <div class="auth-header">
                    <h1 class="auth-title">Recuperar Contraseña</h1>
                    <p class="auth-subtitle">Ingresa tu correo electrónico para recibir instrucciones de restablecimiento</p>
                </div>

                <!-- Alertas -->
                <?php if (!empty($flashMessage)) : ?>
                    <div class="alert alert--<?= $flashMessage['type'] === 'success' ? 'success' : 'error' ?>" role="alert">
                        <svg class="alert-icon" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <?php if ($flashMessage['type'] === 'success') : ?>
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
                            <?php else : ?>
                                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                            <?php endif; ?>
                        </svg>
                        <span><?= htmlspecialchars($flashMessage['message']) ?></span>
                    </div>
                <?php endif; ?>

                <!-- Formulario -->
                <form class="auth-form" method="POST" action="<?= base_url('/password-recovery') ?>" novalidate>
                    <?= csrf_field() ?>
                    <input type="hidden" name="step" value="email">

                    <div class="field">
                        <label class="field-label" for="email">Correo electrónico</label>
                        <div class="field-input-wrap">
                            <svg class="field-icon" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="4" width="20" height="16" rx="3"/>
                                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                            </svg>
                            <input
                                class="field-input"
                                type="email"
                                id="email"
                                name="email"
                                placeholder="tu@correo.com"
                                autocomplete="email"
                                required
                            >
                        </div>
                    </div>

                    <button class="auth-submit" type="submit">
                        <span>Enviar código</span>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    </button>
                </form>

            <?php elseif ($step === 'code'): ?>
                <!-- Header Code -->
                <div class="auth-header">
                    <h1 class="auth-title">Verificar Código</h1>
                    <p class="auth-subtitle">Ingresa el código que hemos enviado a tu correo electrónico</p>
                </div>

                <!-- Alertas -->
                <?php if (!empty($flashMessage)) : ?>
                    <div class="alert alert--<?= $flashMessage['type'] === 'success' ? 'success' : 'error' ?>" role="alert">
                        <svg class="alert-icon" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <?php if ($flashMessage['type'] === 'success') : ?>
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
                            <?php else : ?>
                                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                            <?php endif; ?>
                        </svg>
                        <span><?= htmlspecialchars($flashMessage['message']) ?></span>
                    </div>
                <?php endif; ?>

                <!-- Formulario Code -->
                <form class="auth-form" method="POST" action="<?= base_url('/password-recovery') ?>" novalidate>
                    <?= csrf_field() ?>
                    <input type="hidden" name="step" value="code">
                    <input type="hidden" name="email" value="<?= $_POST['email'] ?? '' ?>">

                    <div class="field">
                        <label class="field-label" for="code">Código de verificación</label>
                        <div class="field-input-wrap">
                            <svg class="field-icon" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                            <input
                                class="field-input"
                                type="text"
                                id="code"
                                name="code"
                                placeholder="123456"
                                maxlength="6"
                                required
                            >
                        </div>
                    </div>

                    <button class="auth-submit" type="submit">
                        <span>Verificar</span>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    </button>

                    <div style="margin-top: 15px; text-align: center; font-size: 0.9em; display: flex; justify-content: center; gap: 5px;">
                        <span style="color: var(--color-text-secondary);">¿No recibiste el código?</span>
                        <button type="button" onclick="document.getElementById('resend-form').submit();" style="background: none; border: none; padding: 0; color: var(--color-primary); cursor: pointer; text-decoration: underline; font-family: inherit; font-size: inherit;">Solicitar nuevo</button>
                    </div>
                </form>

                <form id="resend-form" method="POST" action="<?= base_url('/password-recovery') ?>" style="display: none;">
                    <?= csrf_field() ?>
                    <input type="hidden" name="step" value="resend">
                    <input type="hidden" name="email" value="<?= $_POST['email'] ?? '' ?>">
                </form>

            <?php elseif ($step === 'reset'): ?>
                <!-- Header Reset -->
                <div class="auth-header">
                    <h1 class="auth-title">Restablecer Contraseña</h1>
                    <p class="auth-subtitle">Crea una nueva contraseña para tu cuenta</p>
                </div>

                <!-- Alertas -->
                <?php if (!empty($flashMessage)) : ?>
                    <div class="alert alert--<?= $flashMessage['type'] === 'success' ? 'success' : 'error' ?>" role="alert">
                        <svg class="alert-icon" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <?php if ($flashMessage['type'] === 'success') : ?>
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
                            <?php else : ?>
                                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                            <?php endif; ?>
                        </svg>
                        <span><?= htmlspecialchars($flashMessage['message']) ?></span>
                    </div>
                <?php endif; ?>

                <!-- Formulario Reset -->
                <form class="auth-form" method="POST" action="<?= base_url('/password-recovery') ?>" novalidate>
                    <?= csrf_field() ?>
                    <input type="hidden" name="step" value="reset">
                    <input type="hidden" name="email" value="<?= $_POST['email'] ?? '' ?>">
                    <input type="hidden" name="code" value="<?= $_POST['code'] ?? '' ?>">

                    <div class="field">
                        <label class="field-label" for="password">Nueva contraseña</label>
                        <div class="field-input-wrap">
                            <svg class="field-icon" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                            <input
                                class="field-input"
                                type="password"
                                id="password"
                                name="password"
                                placeholder="Ingresa tu nueva contraseña"
                                required
                            >
                        </div>
                         <!-- Requisitos de contraseña -->
                        <ul style="margin: 5px 0 0 0; padding-left: 0; list-style: none; font-size: 0.8rem; color: var(--color-text-secondary); line-height: 1.6;">
                            <li id="req-length" style="display: flex; align-items: center; gap: 6px;">
                                <span class="req-icon" style="color: #adb5bd;">○</span> Mínimo 8 caracteres
                            </li>
                            <li id="req-number" style="display: flex; align-items: center; gap: 6px;">
                                <span class="req-icon" style="color: #adb5bd;">○</span> Al menos un número
                            </li>
                        </ul>
                    </div>

                    <div class="field">
                        <label class="field-label" for="password_confirmation">Confirmar contraseña</label>
                        <div class="field-input-wrap">
                            <svg class="field-icon" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                            <input
                                class="field-input"
                                type="password"
                                id="password_confirmation"
                                name="password_confirmation"
                                placeholder="Confirma tu nueva contraseña"
                                required
                            >
                        </div>
                        <div id="password-match-error" style="color: #dc3545; font-size: 0.8rem; margin-top: 5px; display: none;">
                            Las contraseñas no coinciden
                        </div>
                    </div>

                    <button class="auth-submit" type="submit">
                        <span>Cambiar contraseña</span>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    </button>
                </form>

                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const passwordInput = document.getElementById('password');
                        const confirmInput = document.getElementById('password_confirmation');
                        const errorMsg = document.getElementById('password-match-error');
                        const reqLength = document.getElementById('req-length');
                        const reqNumber = document.getElementById('req-number');

                        function updateRequirements() {
                            const pass = passwordInput.value;

                            // Mínimo 8 caracteres
                            const iconLength = reqLength.querySelector('.req-icon');
                            if (pass.length >= 8) {
                                reqLength.style.color = '#166534'; // success green
                                iconLength.style.color = '#166534';
                                iconLength.textContent = '✔';
                            } else {
                                reqLength.style.color = 'var(--color-text-secondary)';
                                iconLength.style.color = '#adb5bd';
                                iconLength.textContent = '○';
                            }

                            // Al menos un número
                            const iconNumber = reqNumber.querySelector('.req-icon');
                            if (/\d/.test(pass)) {
                                reqNumber.style.color = '#166534'; // success green
                                iconNumber.style.color = '#166534';
                                iconNumber.textContent = '✔';
                            } else {
                                reqNumber.style.color = 'var(--color-text-secondary)';
                                iconNumber.style.color = '#adb5bd';
                                iconNumber.textContent = '○';
                            }
                        }

                        function checkMatch() {
                            const pass = passwordInput.value;
                            const confirm = confirmInput.value;

                            // Mostrar error solo si hay contraseña escrita, hay confirmación escrita y NO coinciden
                            if (pass.length > 0 && confirm.length > 0 && pass !== confirm) {
                                errorMsg.style.display = 'block';
                            } else {
                                errorMsg.style.display = 'none';
                            }
                        }

                        if (confirmInput && passwordInput) {
                            confirmInput.addEventListener('input', checkMatch);
                            passwordInput.addEventListener('input', function() {
                                updateRequirements();
                                checkMatch();
                            });
                             // Initial check
                            updateRequirements();
                        }
                    });
                </script>
            <?php endif; ?>

            <!-- Registro -->
            <p class="auth-footer-text">
                ¿Recordaste tu contraseña? 
                <a href="<?= base_url('/login') ?>" class="auth-footer-link">Inicia sesión</a>
            </p>

        </div>
    </section>

</main>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/guest.php';
?>
