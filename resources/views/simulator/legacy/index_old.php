<?php
// Configuración básica
$pageTitle = 'Inicio - Simulación SENIAT';
$activePage = 'inicio';
$extraCss = '<link rel="stylesheet" href="' . asset('css/simulator/legacy/index_old.css') . '">';

ob_start(); 
?>

<div class="simulation-wrapper" style="background: white; padding: 10px; border-radius: 4px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">

    <header class="top-header" style="margin-bottom: 10px; border: none;">
        <img src="<?= asset('img/seniat-portal/seniat-portal-banner-header.jpg') ?>" alt="Gobierno Bolivariano">
    </header>

    <div class="banner-section" style="margin-bottom: 10px;">
        <div class="seniat-online-box">
            <img src="<?= asset('img/seniat-portal/seniat-portal-logo-seniat.png') ?>" alt="SENIAT" style="width: 100%; margin-bottom: 5px;">
            
            <div class="online-header">
                <img src="<?= asset('img/seniat-portal/seniat-portal-en-linea.gif') ?>" alt="SENIAT en Línea">
            </div>
            
            <a href="#" class="btn-online">
                <img src="<?= asset('img/seniat-portal/seniat-portal-link-persona-natural.gif') ?>" alt="Persona Natural">
            </a>
            <a href="#" class="btn-online">
                <img src="<?= asset('img/seniat-portal/seniat-portal-link-persona-juridica.gif') ?>" alt="Persona Jurídica">
            </a>
        </div>

        <div class="main-banner-box">
            <img src="<?= asset('img/seniat-portal/seniat-portal-banner-islr.png') ?>" alt="ISLR">
        </div>
    </div>

    <nav class="nav-bar">
        <a href="#" class="nav-item">asistencia al contribuyente</a>
        <div class="nav-item has-dropdown">
            sistemas en linea
            <ul class="seniat-dropdown">
                <li><a href="#">Inscripción de RIF</a></li>
                <li><a href="#">Consulta de RIF</a></li>
                <li><a href="#">Consulta Comprobante Digital de RIF</a></li>
                <li><a href="#">Consulta Certificados</a></li>
                <li><a href="#">Retención IVA <span class="dropdown-subtext">(Prueba Carga de archivo)</span></a></li>
                <li><a href="#">Retención IVA Proveedor <span class="dropdown-subtext">(Prueba Carga de archivo)</span></a></li>
            </ul>
        </div>
        <a href="#" class="nav-item">normativa legal</a>
        <a href="#" class="nav-item">educacion aduanera y tributaria</a>
        <a href="#" class="nav-item">carteles</a>
        <a href="#" class="nav-item">estadísticas</a>
        <a href="#" class="nav-item">enlaces</a>
        <a href="#" class="nav-item">ayuda</a>
    </nav>

    <div class="content-grid">
        
        <section class="col-aduanas">
            <div class="col-header">
                <img src="<?= asset('img/seniat-portal/seniat-portal-btn-aduanas.gif') ?>" alt="ADUANAS">
            </div>
            <div class="seniat-col-box">
                <ul class="link-list">
                    <li><a href="#">Instrucciones beneficios en el Arancel de Aduanas Decreto 4.944</a></li>
                    <li><a href="#">Instrucciones Decretos de exoneración 5.196 y 5.197</a></li>
                    <li><a href="#">Decreto de exoneración GORBV 6952</a></li>
                    <li><a href="#">AVISO SIDUNEA WORLD ADUANAS PRINCIPALES GUAMACHE Y SUS SUBALTERNAS</a></li>
                    <li><a href="#">Informativo SIDUNEA WORLD Versión 4.2.2</a></li>
                    <li><a href="#">Arancel de Aduanas Decreto 4.944</a></li>
                    <li><a href="#">Declaración del Valor de Aduana (Gaceta)</a></li>
                    <li><a href="#">Planilla de Declaración del Valor</a></li>
                    <li><a href="#">AVISO SIDUNEA WORLD ADUANA PRINCIPAL EL AMPARO DE APURE</a></li>
                </ul>
            </div>
        </section>

        <section class="col-tributos">
            <div class="col-header">
                <img src="<?= asset('img/seniat-portal/seniat-portal-btn-tributos.gif') ?>" alt="TRIBUTOS">
            </div>
            <div class="seniat-col-box">
                <ul class="link-list">
                    <li><a href="#">Instructivo de Autoliquidación de Impuesto de Sucesiones</a></li>
                    <li><a href="#">Guía Fácil Acceso al Portal Seniat</a></li>
                    <li><a href="#">Guía Fácil Regístrese / Contribuyentes</a></li>
                    <li><a href="#">Guía Fácil ¿Olvido su Información?</a></li>
                    <li><a href="#">Instructivo Administración de Perfil</a></li>
                    <li><a href="#">Providencia SNAT/2025/000048 (Unidad Tributaria)</a></li>
                    <li><a href="#">Instructivo Declaración para la Protección de las Pensiones</a></li>
                    <li><a href="#">Decreto N° 4.952 Contribución Especial Pensiones</a></li>
                </ul>
            </div>
        </section>

        <section class="col-noticias">
            <div class="col-header">
                <img src="<?= asset('img/seniat-portal/seniat-portal-title-noticias.png') ?>" alt="NOTICIAS">
            </div>
            
            <div class="col-noticias-text-box">
                Encuentra aquí todas las noticias oficiales del Servicio Nacional Integrado de Administración Aduanera y Tributaria (SENIAT).
            </div>
        </section>

        <aside class="col-banners">
            <a href="#" class="sidebar-banner">
                <img src="<?= asset('img/seniat-portal/seniat-portal-banner-servicios-declaracion.jpg') ?>" alt="Servicios">
            </a>
            <a href="#" class="sidebar-banner">
                <img src="<?= asset('img/seniat-portal/seniat-portal-banner-somos-seniat.gif') ?>" alt="Somos SENIAT">
            </a>
            <a href="#" class="sidebar-banner">
                <img src="<?= asset('img/seniat-portal/seniat-portal-banner-proveedores-autorizados.jpg') ?>" alt="Proveedores">
            </a>
            
            <a href="#" class="sidebar-banner">
                <img src="<?= asset('img/logos/logo-unimar.png') ?>" alt="UNIMAR" style="opacity: 0.9; width: 70px; margin-top: 15px; border:none;">
            </a>
        </aside>

    </div>

    <footer class="portal-footer">
        RIF: G-20000303-0 - Servicio Nacional Integrado de Administración Aduanera y Tributaria
    </footer>
    
    <div class="copyright-footer">
        &copy; Copyright, --SENIAT--, Servicio Nacional Integrado de Administración Aduanera y Tributaria, todos los derechos reservados.
    </div>

</div>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../../layouts/student_layout.php'; 
?>