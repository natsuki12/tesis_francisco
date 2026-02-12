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
            
            <!-- Sección Superior: Logo y Enlaces -->
            <div class="footer-top">
                <div class="footer-logo">
                    <img src="<?= asset('img/logos/Logo-SENIAT-principal.png') ?>" alt="SENIAT">
                </div>
                <div class="footer-links">
                    <a href="#">Conócenos</a>
                    <a href="#">Ubícanos</a>
                    <a href="#">Ayuda</a>
                </div>
            </div>

            <div class="footer-bottom">
                <!-- Iconos de Redes Sociales (SVG) -->
                <div class="social-icons">
                    <!-- Facebook -->
                    <a href="#" class="social-icon" title="Facebook">
                        <svg viewBox="0 0 24 24" width="24" height="24"><path fill="currentColor" d="M12 2.04C6.5 2.04 2 6.53 2 12.06C2 17.06 5.66 21.21 10.44 21.96V14.96H7.9V12.06H10.44V9.85C10.44 7.34 11.93 5.96 14.15 5.96C15.21 5.96 16.12 6.04 16.12 6.04V8.51H15.02C13.78 8.51 13.39 9.28 13.39 10.07V12.06H16.18L15.74 14.96H13.39V21.96C18.16 21.21 21.82 17.06 21.82 12.06C21.82 6.53 17.32 2.04 12 2.04Z"/></svg>
                    </a>
                    <!-- Instagram -->
                    <a href="#" class="social-icon" title="Instagram">
                        <svg viewBox="0 0 24 24" width="24" height="24"><path fill="currentColor" d="M7.8,2H16.2C19.4,2 22,4.6 22,7.8V16.2A5.8,5.8 0 0,1 16.2,22H7.8C4.6,22 2,19.4 2,16.2V7.8A5.8,5.8 0 0,1 7.8,2M7.6,4A3.6,3.6 0 0,0 4,7.6V16.4C4,18.39 5.61,20 7.6,20H16.4A3.6,3.6 0 0,0 20,16.4V7.6C20,5.61 18.39,4 16.4,4H7.6M12,7A5,5 0 0,1 17,12A5,5 0 0,1 12,17A5,5 0 0,1 7,12A5,5 0 0,1 12,7M12,9A3,3 0 0,0 9,12A3,3 0 0,0 12,15A3,3 0 0,0 15,12A3,3 0 0,0 12,9M18,5A1,1 0 0,1 19,6A1,1 0 0,1 18,7A1,1 0 0,1 17,6A1,1 0 0,1 18,5Z"/></svg>
                    </a>
                    <!-- YouTube -->
                    <a href="#" class="social-icon" title="YouTube">
                        <svg viewBox="0 0 24 24" width="24" height="24"><path fill="currentColor" d="M10,15L15.19,12L10,9V15M21.56,7.17C21.69,7.64 21.78,8.27 21.84,9.07C21.91,9.87 21.94,10.66 21.94,11.5C21.94,12.33 21.91,13.12 21.84,13.92C21.78,14.72 21.69,15.35 21.56,15.82C21.31,16.96 20.42,17.85 19.28,18.1C16.89,18.63 12,18.5 12,18.5C12,18.5 7.11,18.63 4.72,18.1C3.58,17.85 2.69,16.96 2.44,15.82C2.31,15.35 2.22,14.72 2.16,13.92C2.09,13.12 2.06,12.33 2.06,11.5C2.06,10.66 2.09,9.87 2.16,9.07C2.22,8.27 2.31,7.64 2.44,7.17C2.69,6.03 3.58,5.14 4.72,4.89C7.11,4.36 12,4.5 12,4.5C12,4.5 16.89,4.36 19.28,4.89C20.42,5.14 21.31,6.03 21.56,7.17Z"/></svg>
                    </a>
                    <!-- TikTok -->
                    <a href="#" class="social-icon" title="TikTok">
                        <svg viewBox="0 0 24 24" width="24" height="24"><path fill="currentColor" d="M16.6 5.82C15.93 5.82 15.29 5.61 14.75 5.25C14.21 4.88 13.82 4.36 13.61 3.73C13.56 3.6 13.54 3.46 13.53 3.32H9.8V15C9.8 15.86 9.46 16.68 8.85 17.29C8.24 17.89 7.42 18.24 6.55 18.24C5.69 18.24 4.87 17.89 4.26 17.29C3.65 16.68 3.31 15.86 3.31 15C3.31 14.13 3.65 13.31 4.26 12.7C4.87 12.09 5.69 11.75 6.55 11.75C6.88 11.74 7.21 11.8 7.52 11.91V8.21C7.2 8.13 6.87 8.09 6.54 8.09C5.64 8.09 4.75 8.27 3.92 8.61C3.1 8.95 2.35 9.45 1.71 10.08C1.08 10.72 0.58 11.47 0.24 12.29C-0.1 13.12 -0.28 14.01 -0.28 14.91C-0.28 15.8 0.08 16.69 0.42 17.52C0.77 18.34 1.27 19.09 1.9 19.73C2.54 20.37 3.29 20.87 4.11 21.21C4.94 21.55 5.83 21.73 6.72 21.73C7.62 21.73 8.51 21.55 9.33 21.21C10.15 20.87 10.9 20.37 11.54 19.73C12.17 19.09 12.67 18.34 13.01 17.52C13.35 16.69 13.53 15.8 13.53 14.91V9.5C14.74 10.38 16.22 10.86 17.72 10.86V7.17C16.6 7.17 15.53 6.73 14.73 5.94C15.53 5.94 16.32 5.94 16.6 5.82Z" transform="translate(1 1)"/></svg>
                    </a>
                     <!-- Threads -->
                     <a href="#" class="social-icon" title="Threads">
                        <svg viewBox="0 0 24 24" width="24" height="24"><path fill="currentColor" d="M12.63 15.842c-1.467 0-2.455-.865-2.455-2.228 0-1.745 1.455-2.657 3.39-2.657 1.055 0 1.967.243 2.455.518v.578c0 1.986-1.556 3.789-3.39 3.789zm4.277-5.91c-.089-1.39-1.022-2.122-2.31-2.122-1.39 0-2.522.845-3.089 2.19.778-.29 1.778-.456 2.767-.456.966 0 1.933.155 2.633.388zM24 12c0 6.627-5.373 12-12 12S0 18.627 0 12 5.373 0 12 0s12 5.373 12 12zm-3.867 0c0-4.044-3.522-7.555-8.2-7.555-4.478 0-8.067 3.3-8.067 8.077 0 4.178 3.233 7.856 7.822 7.856 2.767 0 4.889-1.222 5.956-3.233h-2.167c-.822 1.188-2.344 1.79-3.8 1.79-3.289 0-5.633-2.611-5.633-6.4 0-3.9 2.6-6.633 6.044-6.633 3.633 0 6.033 2.5 6.033 6.066 0 1.89-.955 3.123-2.355 3.123-1.045 0-1.633-.611-1.633-1.6v-3.79c-1.31-1.632-3.888-1.577-4.8 1.178-.29.833-.367 1.833-.367 2.655 0 2.689 1.633 4.411 3.966 4.411 1.6 0 2.823-.744 3.634-1.922v1.544c0 1.622 1.088 2.378 2.61 2.378 2.645 0 4.167-2.633 4.167-6.078z"/></svg>
                     </a>
                     <!-- Telegram -->
                     <a href="#" class="social-icon" title="Telegram">
                        <svg viewBox="0 0 24 24" width="24" height="24"><path fill="currentColor" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 0 0-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.4-1.08.39-.35-.01-1.03-.2-1.54-.35-.62-.19-1.12-.29-1.08-.61.02-.16.24-.32.65-.49 2.55-1.08 4.25-1.79 5.09-2.12a20.07 20.07 0 0 1 5.08-1.28z"/></svg>
                    </a>
                    <!-- X (Twitter) -->
                    <a href="#" class="social-icon" title="X">
                        <svg viewBox="0 0 24 24" width="24" height="24"><path fill="currentColor" d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                    </a>
                </div>

                <!-- Texto de Copyright -->
                <div class="copyright-text">
                    2024 © Copyright - SENIAT. Servicio Nacional Integrado de Administración Aduanera y Tributaria - Todos los derechos reservados.
                </div>
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
