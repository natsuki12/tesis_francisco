<?php
declare(strict_types=1);

$pageTitle = 'Bienvenido - Simulador SENIAT';
// Aseguramos que cargue el CSS actualizado
$extraCss = '<link rel="stylesheet" href="'.asset('css/landing/landing.css?v='.time()).'">';

ob_start();
?>

<section class="landing-hero-split">
    
    <div class="landing-left">
        <div class="content-wrapper">
            
            <span class="badge-project">Trabajo de Grado - Unimar</span>
            
            <h1 class="landing-title">
                Simulador del Proceso <br>
                <span class="text-highlight">Sucesoral del SENIAT</span>
            </h1>

            <p class="landing-description">
                Una herramienta educativa que simplifica el aprendizaje de los trámites sucesorales. 
                Plataforma simulada segura para dominar la normativa legal vigente sin riesgos.
            </p>

            <div class="auth-pill-container">
                <div class="auth-pill-wrapper">
                    <a href="<?= base_url('/login') ?>" class="auth-btn active">
                        ¿Quieres ingresar al sistema?
                        <span>Inicia sesión</span>
                    </a>
                    <a href="<?= base_url('/registro') ?>" class="auth-btn">
                        ¿No tienes cuenta?
                        <span>Crear cuenta</span>
                    </a>
                </div>
            </div>

        </div>
    </div>

    <div class="landing-right">
        <div class="blue-backdrop"></div>

        <h2 class="landing-right-title">Aprende Haciendo</h2>

        <div class="css-laptop">
            <div class="screen">
                <img src="<?= asset('img/landing/index-simulador.PNG') ?>" alt="Interfaz del Simulador" class="screen-content">
            </div>
            <div class="keyboard"></div>
        </div>
    </div>

</section>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/guest.php';
?>