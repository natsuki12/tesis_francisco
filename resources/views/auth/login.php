<?php
declare(strict_types=1);

// Rutas de imágenes
// Nota: Eliminé las variables del footer ($footerLogos, etc) porque ya borraste esa sección.
$phMain   = 'data:image/svg+xml;base64,...'; // (Tu código base64 largo...)

// Captura de errores y mensajes de la URL
$error = $_GET['error'] ?? null;


$pageTitle = 'Autenticación de Usuario';

// Solo cargamos el CSS externo. ¡Sin estilos inline! 🧹
$extraCss = '<link rel="stylesheet" href="'.asset('css/auth/login.css').'">';

ob_start();
?>

  <main class="auth-main">
    <section class="auth-shell">

      <div class="auth-shell__logo">
        <img
          class="logo-card__img"
          src="<?= asset('img/seniat-portal/seniat-portal-logo-seniat.png') ?>"
          alt="Logo SENIAT"
          onerror="this.onerror=null;this.src='<?= $phMain ?>';" 
        >
      </div>

      <div class="auth-shell__panel">
        <h1 class="auth-title">AUTENTICACIÓN DE USUARIO</h1>

        <?php if (!empty($flashMessage)) : ?>
          <div class="auth-alert <?= $flashMessage['type'] === 'success' ? 'success' : 'error' ?>" role="alert">
            <?= htmlspecialchars($flashMessage['message']) ?>
          </div>
        <?php endif; ?>

        <?php if (!empty($error)) : ?>
          <div class="auth-alert" role="alert">
            <?php 
                if ($error === 'credenciales') echo "Credenciales incorrectas.";
                elseif ($error === 'inactivo') echo "Su cuenta está inactiva.";
                elseif ($error === 'campos_vacios') echo "Por favor complete todos los campos.";
                else echo "Ocurrió un error. Intente de nuevo.";
            ?>
          </div>
        <?php endif; ?>

        <form class="auth-form" method="POST" action="<?= base_url('/login') ?>">
          
          <?= csrf_field() ?>

          <div class="auth-fields-container">

            <div class="auth-fields">
              <div class="field">
                <label class="field__label" for="email">Correo electrónico</label>

                <div class="input-group">
                  <span class="input-group__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="16" height="16">
                      <path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z" fill="currentColor"/>
                    </svg>
                  </span>

                  <input
                    class="input-group__input"
                    type="email"
                    id="email"
                    name="email"
                    placeholder="ejemplo@correo.com"
                    autocomplete="email"
                    required
                  >
                </div>
              </div>

              <div class="field">
                <label class="field__label" for="password">Contraseña</label>

                <div class="input-group">
                  <span class="input-group__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="16" height="16">
                      <path d="M14 10a4 4 0 1 0-1.2 2.86l1.4 1.4H16v2h2v2h2v-3.17l-4.03-4.03A3.98 3.98 0 0 0 14 10Zm-6 0a2 2 0 1 1 2 2 2 2 0 0 1-2-2Z" fill="currentColor"/>
                    </svg>
                  </span>

                  <input
                    class="input-group__input"
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Contraseña"
                    autocomplete="current-password"
                    required
                  >
                </div>
              </div>

              <div class="auth-links">
                <a href="#" class="auth-link">¿Olvidó su contraseña?</a>
                <a href="<?= base_url('/registro') ?>" class="auth-link">¿Sin usuario? Registrar cuenta</a>
              </div>

              <button class="auth-submit" type="submit">Ingresar</button>
            </div>
          </div>
        </form>
      </div>
    </section>
  </main>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/guest.php';
?>