<?php
$pageTitle = 'Inicio - Simulación SENIAT';
$activePage = 'inicio';
$extraCss = '<link rel="stylesheet" href="' . asset('css/simulator/steps/step_01_seniat_index.css') . '">';

ob_start(); 
?>

<div class="simulation-wrapper">
    <header class="top-header">
        <img src="<?= asset('img/seniat-portal-nuevo/seniat-banner-header.jpg') ?>" alt="Gobierno Bolivariano" style="width: 100%; display: block;">
    </header>
    
    <!-- Navbar SENIAT -->
    <nav class="seniat-navbar">
        <div class="seniat-navbar__logo">
            <img src="<?= asset('img/seniat-portal-nuevo/logo-seniat-blanco.png') ?>" alt="SENIAT">
        </div>
        <div class="seniat-navbar__right">
            <ul class="seniat-navbar__menu">
                <li class="has-mega-dropdown">
                    <a href="#">Asistencia al contribuyente <span class="dropdown-arrow">▼</span></a>
                    <div class="mega-dropdown">
                        <div class="mega-dropdown__section">
                            <h4 class="mega-dropdown__title">INFORMACIÓN DE INTERES</h4>
                            <ul>
                                <li><a href="#">Valor de la Unidad Tributaria (UT)</a></li>
                                <li><a href="#">Calendario Vigente</a></li>
                                <li><a href="#">Bancos Recaudadores</a></li>
                                <li><a href="#">Documentación Sistemas en Línea</a></li>
                                <li><a href="#">Glosario Aduanero</a></li>
                                <li><a href="#">Glosario Tributario</a></li>
                            </ul>
                        </div>
                        <div class="mega-dropdown__section">
                            <h4 class="mega-dropdown__title">ADUANAS</h4>
                        </div>
                        <div class="mega-dropdown__section">
                            <h4 class="mega-dropdown__title">TRIBUTOS</h4>
                        </div>
                    </div>
                </li>
                <li class="has-mega-dropdown">
                    <a href="#">Conócenos <span class="dropdown-arrow">▼</span></a>
                    <div class="mega-dropdown">
                        <div class="mega-dropdown__section">
                            <ul>
                                <li><a href="#">SENIAT</a></li>
                                <li><a href="#">Organigrama Seniat</a></li>
                                <li><a href="#">Ubícanos</a></li>
                                <li><a href="#">Museo</a></li>
                            </ul>
                        </div>
                    </div>
                </li>
                <li><a href="#">Noticias SENIAT</a></li>
                <li class="has-mega-dropdown dropdown-right">
                    <a href="#">Sistemas en Línea <span class="dropdown-arrow">▼</span></a>
                    <div class="mega-dropdown">
                        <div class="mega-dropdown__section">
                            <ul>
                                <li><a href="#">Inscripción de RIF</a></li>
                                <li><a href="#">Consulta RIF</a></li>
                                <li><a href="#">Consulta comprobante digital de RIF</a></li>
                                <li><a href="#">Consulta de Certificados</a></li>
                                <li><a href="#">Retención IVA (Prueba carga de archivo)</a></li>
                                <li><a href="#">Retención IVA Proveedor (Prueba carga de archivo)</a></li>
                            </ul>
                        </div>
                    </div>
                </li>
            </ul>
            <div class="seniat-navbar__actions">
                <div class="ingresar-wrapper">
                    <a href="#" class="btn-ingresar">Ingresar <span class="dropdown-arrow">▼</span></a>
                    <div class="ingresar-dropdown">
                        <div class="ingresar-header">SENIAT en Línea</div>
                        <a href="#" class="ingresar-item">Persona Natural</a>
                        <a href="#" class="ingresar-item">Persona Jurídica</a>
                        <a href="#" class="ingresar-item">Servicios de Declaración</a>
                    </div>
                </div>
                <button class="btn-search" aria-label="Buscar">
                    <svg viewBox="0 0 24 24" width="18" height="18"><path fill="currentColor" d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
                </button>
            </div>
        </div>
    </nav>
    
    <!-- Slider de imágenes -->
    <div class="slider-container">
        <div class="slider">
            <div class="slide active">
                <img src="<?= asset('img/seniat-portal-nuevo/slider-inicio/slider-SENIAT-1.png') ?>" alt="Slide 1">
            </div>
            <div class="slide">
                <img src="<?= asset('img/seniat-portal-nuevo/slider-inicio/slider-SENIAT-2.png') ?>" alt="Slide 2">
            </div>
            <div class="slide">
                <img src="<?= asset('img/seniat-portal-nuevo/slider-inicio/slider-SENIAT-3.png') ?>" alt="Slide 3">
            </div>
            <div class="slide">
                <img src="<?= asset('img/seniat-portal-nuevo/slider-inicio/slider-SENIAT-4.PNG') ?>" alt="Slide 4">
            </div>
            <div class="slide">
                <img src="<?= asset('img/seniat-portal-nuevo/slider-inicio/slider-SENIAT-5.PNG') ?>" alt="Slide 5">
            </div>
        </div>
        <div class="slider-dots">
            <span class="dot active" onclick="goToSlide(0)"></span>
            <span class="dot" onclick="goToSlide(1)"></span>
            <span class="dot" onclick="goToSlide(2)"></span>
            <span class="dot" onclick="goToSlide(3)"></span>
            <span class="dot" onclick="goToSlide(4)"></span>
        </div>
    </div>
    
    <!-- Quick Links Icons -->
    <div class="quick-links">
        <a href="#" class="quick-link-item">
            <div class="quick-link-icon">
                <svg viewBox="0 0 24 24" width="40" height="40"><path fill="currentColor" d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
            </div>
            <span>Carteles</span>
        </a>
        <a href="#" class="quick-link-item">
            <div class="quick-link-icon">
                <svg viewBox="0 0 24 24" width="40" height="40"><path fill="currentColor" d="M20 6h-8l-2-2H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2zm0 12H4V8h16v10z"/></svg>
            </div>
            <span>Normativa Legal</span>
        </a>
        <a href="#" class="quick-link-item">
            <div class="quick-link-icon">
                <svg viewBox="0 0 24 24" width="40" height="40"><path fill="currentColor" d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
            </div>
            <span>Contrataciones Públicas</span>
        </a>
        <a href="#" class="quick-link-item">
            <div class="quick-link-icon">
                <svg viewBox="0 0 24 24" width="40" height="40"><path fill="currentColor" d="M18 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zM6 4h5v8l-2.5-1.5L6 12V4z"/></svg>
            </div>
            <span>Revista</span>
        </a>
        <a href="#" class="quick-link-item">
            <div class="quick-link-icon">
                <svg viewBox="0 0 24 24" width="40" height="40"><path fill="currentColor" d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
            </div>
            <span>Somos SENIAT</span>
        </a>
    </div>
    
    <!-- Ticker de Unidad Tributaria -->
    <div class="ticker-container">
        <div class="ticker-wrapper">
            <div class="ticker-content">
                <span>📦 EL VALOR DE LA UNIDAD TRIBUTARIA (U.T.) ES DE Bs. 43,00.</span>
                <span>📦 EL VALOR DE LA UNIDAD TRIBUTARIA (U.T.) ES DE Bs. 43,00.</span>
                <span>📦 EL VALOR DE LA UNIDAD TRIBUTARIA (U.T.) ES DE Bs. 43,00.</span>
                <span>📦 EL VALOR DE LA UNIDAD TRIBUTARIA (U.T.) ES DE Bs. 43,00.</span>
                <span>📦 EL VALOR DE LA UNIDAD TRIBUTARIA (U.T.) ES DE Bs. 43,00.</span>
                <span>📦 EL VALOR DE LA UNIDAD TRIBUTARIA (U.T.) ES DE Bs. 43,00.</span>
                <span>📦 EL VALOR DE LA UNIDAD TRIBUTARIA (U.T.) ES DE Bs. 43,00.</span>
                <span>📦 EL VALOR DE LA UNIDAD TRIBUTARIA (U.T.) ES DE Bs. 43,00.</span>
                <span>📦 EL VALOR DE LA UNIDAD TRIBUTARIA (U.T.) ES DE Bs. 43,00.</span>
                <span>📦 EL VALOR DE LA UNIDAD TRIBUTARIA (U.T.) ES DE Bs. 43,00.</span>
                <span>📦 EL VALOR DE LA UNIDAD TRIBUTARIA (U.T.) ES DE Bs. 43,00.</span>
                <span>📦 EL VALOR DE LA UNIDAD TRIBUTARIA (U.T.) ES DE Bs. 43,00.</span>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="site-footer">
        <div class="footer-content">
            <div class="footer-logo">
                <img src="<?= asset('img/logos/Logo-SENIAT-principal.png') ?>" alt="SENIAT">
            </div>
            <div class="footer-links">
                <a href="#">Conócenos</a>
                <a href="#">Ubícanos</a>
                <a href="#">Ayuda</a>
            </div>
        </div>
    </footer>
</div>

<script src="<?= asset('js/simulator/steps/step_01_seniat_index.js') ?>"></script>

<!-- Iconos flotantes fijos en el lado derecho -->
<div class="floating-sidebar">
    <a href="#" class="floating-icon" title="Correo">
        <svg viewBox="0 0 24 24" width="24" height="24"><path fill="currentColor" d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
    </a>
    <a href="#" class="floating-icon" title="Teléfono">
        <svg viewBox="0 0 24 24" width="24" height="24"><path fill="currentColor" d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/></svg>
    </a>
    <a href="#" class="floating-icon" title="Contacto">
        <svg viewBox="0 0 24 24" width="24" height="24"><path fill="currentColor" d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
    </a>
</div>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../../layouts/student_layout.php'; 
?>
