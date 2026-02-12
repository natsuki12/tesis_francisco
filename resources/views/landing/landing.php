<?php
declare(strict_types=1);

$pageTitle = 'Bienvenido - Simulador SENIAT';
$extraCss = '
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="'.asset('css/landing/landing.css?v='.time()).'">';

ob_start();
?>

<!-- ============ HERO SPLIT ============ -->
<section class="landing-hero">

    <div class="landing-left">
        <div class="content-wrapper">

            <span class="badge-project">
                <span class="badge-dot"></span>
                Trabajo de Grado &mdash; UNIMAR
            </span>

            <h1 class="landing-title">
                <span class="title-line-1">Simulador del Proceso</span>
                <span class="title-line-2">Sucesoral del SENIAT</span>
            </h1>
            <div class="title-accent" aria-hidden="true"></div>

            <p class="landing-description">
                Practica paso a paso cómo completar una declaración sucesoral
                en un entorno simulado. Aprende la normativa vigente, llena formularios
                reales y domina el proceso — sin afectar datos oficiales.
            </p>

            <div class="cta-group">
                <a href="<?= base_url('/login') ?>" class="btn btn-primary">
                    <span class="btn-label">Inicia sesión</span>
                    <svg class="btn-arrow" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                </a>
                <a href="<?= base_url('/registro') ?>" class="btn btn-secondary">
                    <span class="btn-label">Crear cuenta</span>
                </a>
            </div>

            <p class="trust-note">
                <svg class="trust-icon" viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                    <path d="M9 12l2 2 4-4"></path>
                </svg>
                <span>Plataforma simulada con fines educativos. No sustituye el portal oficial del SENIAT.</span>
            </p>

        </div>
    </div>

    <div class="landing-right">
        <div class="blue-backdrop" aria-hidden="true">
            <div class="backdrop-glow backdrop-glow--1"></div>
            <div class="backdrop-glow backdrop-glow--2"></div>
            <div class="backdrop-grid"></div>
        </div>

        <div class="right-content">
            <p class="right-eyebrow">Aprende haciendo</p>
            <h2 class="right-title">Interfaz real,<br>datos simulados</h2>

            <div class="css-laptop">
                <div class="screen">
                    <img src="<?= asset('img/landing/index-simulador.PNG') ?>" alt="Vista previa del Simulador Sucesoral SENIAT mostrando el formulario de declaración" class="screen-content" loading="lazy">
                </div>
                <div class="keyboard">
                    <div class="keyboard-trackpad"></div>
                </div>
            </div>
        </div>
    </div>

</section>

<!-- ============ FEATURES ============ -->
<section class="features-section">
    <div class="features-container">

        <div class="feature-card">
            <div class="feature-icon-wrap">
                <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
            </div>
            <h3 class="feature-title">Aprendizaje guiado</h3>
            <p class="feature-desc">Cada paso del proceso sucesoral explicado con instrucciones claras. Ideal si es tu primera vez.</p>
        </div>

        <div class="feature-card">
            <div class="feature-icon-wrap">
                <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
            </div>
            <h3 class="feature-title">Entorno 100% seguro</h3>
            <p class="feature-desc">Tus datos nunca salen de la plataforma. Practica sin consecuencias legales ni fiscales.</p>
        </div>

        <div class="feature-card">
            <div class="feature-icon-wrap">
                <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                </svg>
            </div>
            <h3 class="feature-title">Normativa vigente</h3>
            <p class="feature-desc">Basado en la legislación venezolana actual. Formularios y cálculos alineados con el SENIAT.</p>
        </div>

        <div class="feature-card">
            <div class="feature-icon-wrap">
                <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                </svg>
            </div>
            <h3 class="feature-title">Para quién es</h3>
            <p class="feature-desc">Estudiantes de contaduría, derecho, administración y cualquier persona que necesite entender el proceso.</p>
        </div>

    </div>
</section>

<!-- ============ HOW IT WORKS ============ -->
<section class="steps-section">
    <div class="steps-container">

        <div class="steps-header">
            <span class="section-eyebrow">¿Cómo funciona?</span>
            <h2 class="section-title">Tres pasos para dominar<br>el proceso sucesoral</h2>
        </div>

        <div class="steps-grid">

            <div class="step-card">
                <div class="step-number">01</div>
                <h3 class="step-title">Crea tu cuenta</h3>
                <p class="step-desc">Regístrate con tus datos básicos y accede al simulador. No se requiere información fiscal real.</p>
            </div>

            <div class="step-connector" aria-hidden="true">
                <svg width="40" height="2" viewBox="0 0 40 2"><line x1="0" y1="1" x2="40" y2="1" stroke="currentColor" stroke-width="2" stroke-dasharray="4 4"/></svg>
            </div>

            <div class="step-card">
                <div class="step-number">02</div>
                <h3 class="step-title">Completa la declaración</h3>
                <p class="step-desc">Sigue el proceso paso a paso: datos del causante, herederos, activos y pasivos del patrimonio.</p>
            </div>

            <div class="step-connector" aria-hidden="true">
                <svg width="40" height="2" viewBox="0 0 40 2"><line x1="0" y1="1" x2="40" y2="1" stroke="currentColor" stroke-width="2" stroke-dasharray="4 4"/></svg>
            </div>

            <div class="step-card">
                <div class="step-number">03</div>
                <h3 class="step-title">Revisa y aprende</h3>
                <p class="step-desc">Genera la planilla, revisa los cálculos del impuesto y entiende cada componente de la declaración.</p>
            </div>

        </div>

    </div>
</section>

<!-- ============ CTA FINAL ============ -->
<section class="cta-section">
    <div class="cta-section-inner">
        <h2 class="cta-section-title">¿Listo para practicar?</h2>
        <p class="cta-section-desc">Crea tu cuenta en menos de un minuto y comienza a explorar el simulador.</p>
        <div class="cta-section-buttons">
            <a href="<?= base_url('/registro') ?>" class="btn btn-primary btn-lg">
                <span class="btn-label">Comenzar ahora</span>
                <svg class="btn-arrow" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
            </a>
            <a href="<?= base_url('/login') ?>" class="btn btn-ghost">Ya tengo cuenta</a>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/guest.php';
?>
