<?php
/**
 * Acceder Sistemas — Servicios de Declaración > Sistemas
 * Standalone page: header partial + full-width APLICATIVOS content (no sidebar)
 */
$blueNavText = '';
$extraCss = ['/assets/css/simulator/seniat_actual/acceder_sistemas.css'];
include __DIR__ . '/../../layouts/partials/sim_header.php';
?>

<!-- Main content row (full width, no sidebar) -->
<div _ngcontent-pgi-c62 class=row>
<div _ngcontent-pgi-c62 id=divHijo class=col-sm-12>

<div class="p-4">
    <div class="card">
        <div class="card-header fw-bold">APLICATIVOS</div>
        <div class="card-body">
            <div class="aplicativos-wrapper">
                <div class="aplicativos-list">
                    <div class="aplicativo-item">IMPUESTO A LAS GRANDES TRANSACCIONES FINANCIERAS</div>
                    <div class="aplicativo-item">ENAJENACIÓN DE INMUEBLES</div>
                    <div class="aplicativo-item">TASAS E IMPUESTOS</div>
                    <div class="aplicativo-item">PROTECCIÓN A LAS PENSIONES</div>
                    <div class="aplicativo-item">SUCESIONES</div>
                </div>
                <a href="<?= base_url('/simulador/servicios_declaracion/dashboard') ?>" class="btn-ir-sistema">Ir al Sistema <i class="bi bi-cursor-fill"></i></a>
            </div>
        </div>
    </div>
</div>

</div>
</div></div></app-inicio></app-root>

<script>document.addEventListener("click",function(e){var w=document.getElementById("hamburgerWrap");if(w&&!w.contains(e.target)){document.getElementById("hamburgerMenu").style.display="none"}})</script>
</body>
</html>
