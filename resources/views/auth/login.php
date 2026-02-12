<?php
declare(strict_types=1);

$error = $_GET['error'] ?? null;

$pageTitle = 'Iniciar sesión - Simulador SENIAT';
$extraCss = '
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Outfit:wght@700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="'.asset('css/auth/login.css?v='.time()).'">';

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
                <h2 class="brand-title">Aprende el proceso<br>sucesoral sin riesgos</h2>
                <p class="brand-desc">
                    Plataforma educativa simulada para practicar 
                    la declaración sucesoral paso a paso.
                </p>
            </div>

            <div class="brand-features">
                <div class="brand-feature">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    <span>Formularios reales</span>
                </div>
                <div class="brand-feature">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    <span>Datos simulados</span>
                </div>
                <div class="brand-feature">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    <span>Normativa vigente</span>
                </div>
            </div>

            <p class="brand-badge">Trabajo de Grado &mdash; UNIMAR</p>
        </div>
    </aside>

    <!-- ====== PANEL DERECHO (Formulario) ====== -->
    <section class="auth-panel">
        <div class="auth-panel-inner">

            <!-- Link volver -->
            <a href="<?= base_url('/') ?>" class="back-link">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                Volver al inicio
            </a>

            <!-- Header -->
            <div class="auth-header">
                <h1 class="auth-title">Bienvenido de vuelta</h1>
                <p class="auth-subtitle">Ingresa tus credenciales para acceder al simulador</p>
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

            <?php if (!empty($error)) : ?>
                <div class="alert alert--error" role="alert">
                    <svg class="alert-icon" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    <span>
                        <?php 
                            if ($error === 'credenciales') echo "Credenciales incorrectas. Verifica tu correo y contraseña.";
                            elseif ($error === 'inactivo') echo "Tu cuenta está inactiva. Contacta al administrador.";
                            elseif ($error === 'campos_vacios') echo "Por favor completa todos los campos.";
                            else echo "Ocurrió un error inesperado. Intenta de nuevo.";
                        ?>
                    </span>
                </div>
            <?php endif; ?>

            <!-- Formulario -->
            <form class="auth-form" method="POST" action="<?= base_url('/login') ?>" novalidate>
                <?= csrf_field() ?>

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

                <div class="field">
                    <div class="field-label-row">
                        <label class="field-label" for="password">Contraseña</label>
                        <a href="#" class="field-link">¿Olvidaste tu contraseña?</a>
                    </div>
                    <div class="field-input-wrap">
                        <svg class="field-icon" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                        <input
                            class="field-input"
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Ingresa tu contraseña"
                            autocomplete="current-password"
                            required
                        >
                        <button type="button" class="toggle-password" aria-label="Mostrar contraseña" onclick="togglePasswordVisibility()">
                            <svg class="eye-open" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                            <svg class="eye-closed" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="display:none">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
                                <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
                                <line x1="1" y1="1" x2="23" y2="23"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <button class="auth-submit" type="submit">
                    <span>Ingresar</span>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                </button>
            </form>

            <!-- Registro -->
            <p class="auth-footer-text">
                ¿No tienes cuenta? 
                <a href="<?= base_url('/registro') ?>" class="auth-footer-link">Crear cuenta</a>
            </p>

        </div>
    </section>

</main>

<script>
function togglePasswordVisibility() {
    const input = document.getElementById('password');
    const btn = input.closest('.field-input-wrap').querySelector('.toggle-password');
    const eyeOpen = btn.querySelector('.eye-open');
    const eyeClosed = btn.querySelector('.eye-closed');
    
    if (input.type === 'password') {
        input.type = 'text';
        eyeOpen.style.display = 'none';
        eyeClosed.style.display = 'block';
        btn.setAttribute('aria-label', 'Ocultar contraseña');
    } else {
        input.type = 'password';
        eyeOpen.style.display = 'block';
        eyeClosed.style.display = 'none';
        btn.setAttribute('aria-label', 'Mostrar contraseña');
    }
}
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/guest.php';
?>
