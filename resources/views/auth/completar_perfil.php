<?php
declare(strict_types=1);

$pageTitle = 'Completar Perfil — SPDSS';
$extraCss = '
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Outfit:wght@700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="' . asset('css/auth/login.css?v=' . time()) . '">';

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
                    <rect width="40" height="40" rx="10" fill="rgba(255,255,255,0.12)" />
                    <path d="M12 28V16l8-6 8 6v12H12z" stroke="#fff" stroke-width="1.8" fill="none" />
                    <rect x="17" y="22" width="6" height="6" rx="1" stroke="#fff" stroke-width="1.5" fill="none" />
                </svg>
                <span class="brand-logo-text">Simulador SENIAT</span>
            </a>

            <div class="brand-message">
                <h2 class="brand-title">Bienvenido al<br>sistema</h2>
                <p class="brand-desc">
                    Complete su perfil y establezca una contraseña segura
                    para comenzar a utilizar la plataforma.
                </p>
            </div>

            <div class="brand-features">
                <div class="brand-feature">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                        <polyline points="22 4 12 14.01 9 11.01" />
                    </svg>
                    <span>Datos personales</span>
                </div>
                <div class="brand-feature">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                        <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                    </svg>
                    <span>Nueva contraseña segura</span>
                </div>
                <div class="brand-feature">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="m9 12 2 2 4-4"/>
                    </svg>
                    <span>Un solo paso</span>
                </div>
            </div>

            <p class="brand-badge">Primer inicio de sesión</p>
        </div>
    </aside>

    <!-- ====== PANEL DERECHO (Formulario) ====== -->
    <section class="auth-panel">
        <div class="auth-panel-inner">

            <!-- Header -->
            <div class="auth-header">
                <h1 class="auth-title">Complete su perfil</h1>
                <p class="auth-subtitle">Actualice su información personal y establezca una contraseña segura para continuar.</p>
            </div>

            <!-- Info box -->
            <div style="background:#e8f5e9; border-left:4px solid #4caf50; padding:12px 16px; border-radius:0 6px 6px 0; margin-bottom:20px; font-size:13px; color:#2e7d32;">
                <strong>ℹ️ Primer inicio de sesión.</strong> Esta información solo se solicita una vez.
            </div>

            <!-- Formulario -->
            <form class="auth-form" id="form-completar" onsubmit="return false;" novalidate>

                <div class="field">
                    <label class="field-label" for="fecha_nacimiento">Fecha de Nacimiento <span style="color:#e53e3e">*</span></label>
                    <div class="field-input-wrap">
                        <svg class="field-icon" viewBox="0 0 24 24" width="18" height="18" fill="none"
                            stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                            <line x1="16" y1="2" x2="16" y2="6" />
                            <line x1="8" y1="2" x2="8" y2="6" />
                            <line x1="3" y1="10" x2="21" y2="10" />
                        </svg>
                        <input class="field-input" type="date" id="fecha_nacimiento" name="fecha_nacimiento"
                            max="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>

                <div class="field">
                    <label class="field-label" for="genero">Género <span style="color:var(--gray-400); font-weight:400;">(opcional)</span></label>
                    <div class="field-input-wrap">
                        <svg class="field-icon" viewBox="0 0 24 24" width="18" height="18" fill="none"
                            stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                            <circle cx="12" cy="7" r="4" />
                        </svg>
                        <select class="field-input" id="genero" name="genero" style="appearance:auto; cursor:pointer;">
                            <option value="">— No especificar —</option>
                            <option value="M">Masculino</option>
                            <option value="F">Femenino</option>
                            <option value="Otro">Otro</option>
                            <option value="Prefiero no decir">Prefiero no decir</option>
                        </select>
                    </div>
                </div>

                <div class="field">
                    <label class="field-label" for="password">Nueva Contraseña <span style="color:#e53e3e">*</span></label>
                    <div class="field-input-wrap">
                        <svg class="field-icon" viewBox="0 0 24 24" width="18" height="18" fill="none"
                            stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                        </svg>
                        <input class="field-input" type="password" id="password" name="password"
                            placeholder="Mínimo 8 caracteres" minlength="8" required autocomplete="new-password">
                    </div>
                    <!-- Requisitos de contraseña (idéntico a register_part_3.php) -->
                    <ul style="margin: 5px 0 0 0; padding-left: 0; list-style: none; font-size: 0.8rem; color: var(--gray-400); line-height: 1.6;">
                        <li id="req-length" style="display: flex; align-items: center; gap: 6px;">
                            <span class="req-icon" style="color: #adb5bd;">○</span> Mínimo 8 caracteres
                        </li>
                        <li id="req-number" style="display: flex; align-items: center; gap: 6px;">
                            <span class="req-icon" style="color: #adb5bd;">○</span> Al menos un número
                        </li>
                    </ul>
                </div>

                <div class="field">
                    <label class="field-label" for="password_confirm">Confirmar Contraseña <span style="color:#e53e3e">*</span></label>
                    <div class="field-input-wrap">
                        <svg class="field-icon" viewBox="0 0 24 24" width="18" height="18" fill="none"
                            stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                        </svg>
                        <input class="field-input" type="password" id="password_confirm" name="password_confirm"
                            placeholder="Confirme su contraseña" minlength="8" required autocomplete="new-password">
                    </div>
                    <div id="password-match-error" style="color: #dc3545; font-size: 0.8rem; margin-top: 5px; display: none; padding-left: 20px;">
                        Las contraseñas no coinciden
                    </div>
                </div>

                <button class="auth-submit" type="button" id="btn-completar" onclick="completarPerfil()">
                    <span>Completar y Continuar</span>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12h14" />
                        <path d="m12 5 7 7-7 7" />
                    </svg>
                </button>
            </form>

        </div>
    </section>

</main>

<script>
const CSRF_TOKEN = '<?= \App\Core\Csrf::getToken() ?>';
const BASE_URL   = '<?= base_url() ?>';

// ── Validación de requisitos en tiempo real (idéntico a register_part_3.php) ──
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('password_confirm');
    const errorMsg = document.getElementById('password-match-error');
    const reqLength = document.getElementById('req-length');
    const reqNumber = document.getElementById('req-number');

    function updateRequirements() {
        const pass = passwordInput.value;

        // Mínimo 8 caracteres
        const iconLength = reqLength.querySelector('.req-icon');
        if (pass.length >= 8) {
            reqLength.style.color = '#198754';
            iconLength.style.color = '#198754';
            iconLength.textContent = '✔';
        } else {
            reqLength.style.color = 'var(--gray-400)';
            iconLength.style.color = '#adb5bd';
            iconLength.textContent = '○';
        }

        // Al menos un número
        const iconNumber = reqNumber.querySelector('.req-icon');
        if (/\d/.test(pass)) {
            reqNumber.style.color = '#198754';
            iconNumber.style.color = '#198754';
            iconNumber.textContent = '✔';
        } else {
            reqNumber.style.color = 'var(--gray-400)';
            iconNumber.style.color = '#adb5bd';
            iconNumber.textContent = '○';
        }
    }

    function checkMatch() {
        const pass = passwordInput.value;
        const confirm = confirmInput.value;

        if (pass.length > 0 && confirm.length > 0 && pass !== confirm) {
            errorMsg.style.display = 'block';
        } else {
            errorMsg.style.display = 'none';
        }
    }

    confirmInput.addEventListener('input', checkMatch);
    passwordInput.addEventListener('input', function() {
        updateRequirements();
        checkMatch();
    });

    updateRequirements();
});

async function completarPerfil() {
    const btn = document.getElementById('btn-completar');
    const password = document.getElementById('password').value;
    const confirm  = document.getElementById('password_confirm').value;
    const fecha    = document.getElementById('fecha_nacimiento').value;

    // Client-side quick checks
    if (!fecha) { showAlert('Ingrese su fecha de nacimiento.', 'error'); return; }
    if (password.length < 8) { showAlert('La contraseña debe tener al menos 8 caracteres.', 'error'); return; }
    if (!/\d/.test(password)) { showAlert('La contraseña debe contener al menos 1 dígito.', 'error'); return; }
    if (password !== confirm) { showAlert('Las contraseñas no coinciden.', 'error'); return; }

    btn.disabled = true;
    btn.querySelector('span').textContent = 'Procesando...';

    const body = new URLSearchParams({
        csrf_token:       CSRF_TOKEN,
        fecha_nacimiento: fecha,
        genero:           document.getElementById('genero').value,
        password:         password,
        password_confirm: confirm
    });

    try {
        const res = await fetch(BASE_URL + '/completar-perfil', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body
        });
        const data = await res.json();

        if (data.success) {
            showAlert(data.message, 'success');
            setTimeout(() => {
                window.location.href = data.redirect || BASE_URL + '/home';
            }, 1500);
        } else {
            showAlert(data.message || 'Error al procesar.', 'error');
            btn.disabled = false;
            btn.querySelector('span').textContent = 'Completar y Continuar';
        }
    } catch (err) {
        console.error(err);
        showAlert('Error de conexión.', 'error');
        btn.disabled = false;
        btn.querySelector('span').textContent = 'Completar y Continuar';
    }
}

function showAlert(msg, type) {
    // Remove existing
    document.querySelector('.alert')?.remove();

    const div = document.createElement('div');
    div.className = 'alert alert--' + type;
    div.setAttribute('role', 'alert');

    // SVG icons del sistema (idéntico a login.php)
    const icons = {
        error: '<svg class="alert-icon" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>',
        success: '<svg class="alert-icon" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>'
    };

    div.innerHTML = (icons[type] || icons.error) + '<span>' + msg + '</span>';

    const form = document.getElementById('form-completar');
    form.parentNode.insertBefore(div, form);

    if (type !== 'success') setTimeout(() => div.remove(), 6000);
}
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/guest.php';
?>
