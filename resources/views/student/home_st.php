<?php
declare(strict_types=1);

// ARCHIVO: resources/views/dashboard/home_st.php

// 1. Configuración de la Vista
$pageTitle = 'Inicio Estudiante - Simulador SENIAT';

// 2. Cargamos el CSS específico (home_st.css)
$extraCss = '<link rel="stylesheet" href="'.asset('css/student/home_st.css').'">';

// 3. Datos (Placeholder: Luego vendrán del Controlador)
$userName = $_SESSION['user_name'] ?? 'Estudiante'; 

ob_start();
?>

<main class="dashboard-main">
    <div class="dashboard-container">
        
        <section class="dash-hero">
            <h1 class="dash-hero__title">
                Bienvenido, <span class="dash-hero__name"><?= htmlspecialchars($userName) ?></span>
            </h1>
            <p class="dash-hero__subtitle">
                Panel de control del estudiante. Seleccione una acción para continuar.
            </p>
        </section>

        <section class="dash-grid">

            <article class="dash-card">
                <div class="dash-card__icon-box">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                        <polyline points="10 9 9 9 8 9"></polyline>
                    </svg>
                </div>
                <h2 class="dash-card__title">Nueva Declaración</h2>
                <p class="dash-card__desc">
                    Acceda al simulador interactivo para realizar el proceso de declaración sucesoral (Forma 32), cálculos y desglose de herederos.
                </p>
                <a href="<?= base_url('/simulador') ?>" class="dash-btn dash-btn--primary">
                    Iniciar Simulación
                </a>
            </article>

            <article class="dash-card">
                <div class="dash-card__icon-box">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </div>
                <h2 class="dash-card__title">Mi Perfil</h2>
                <p class="dash-card__desc">
                    Revise su información personal registrada, datos académicos y gestione la seguridad de su cuenta.
                </p>
                <a href="<?= base_url('/perfil') ?>" class="dash-btn dash-btn--outline">
                    Ver Datos Personales
                </a>
            </article>

            <article class="dash-card dash-card--disabled">
                <div class="dash-card__icon-box">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                </div>
                <h2 class="dash-card__title">Historial</h2>
                <p class="dash-card__desc">
                    Consulte sus declaraciones anteriores y reportes de evaluación generados por el sistema.<br>
                    <em>(Módulo en construcción)</em>
                </p>
                <button class="dash-btn" disabled>No disponible</button>
            </article>

        </section>
    </div>
</main>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layouts/student_layout.php'; 
?>