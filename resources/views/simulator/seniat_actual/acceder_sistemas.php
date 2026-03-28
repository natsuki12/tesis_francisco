<?php
/**
 * Acceder Sistemas — Servicios de Declaración > Sistemas
 * Standalone page: SENIAT header + full-width APLICATIVOS content (no sidebar)
 * Uses: logged_layout.php as outer shell + seniat-wrapper card isolation
 */
$pageTitle = 'Sistemas — Simulador';
$activePage = 'simulador';

// ─── Collect CSS for logged_layout.php ─────────────────────────────
$cssHtml  = '<link rel="stylesheet" href="' . base_url('/assets/css/simulator/seniat_actual/sucesion/bienes_muebles/banco_legacy.css') . '">' . "\n";
$cssHtml .= '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">' . "\n";
$cssHtml .= '<link rel="stylesheet" href="' . base_url('/assets/css/simulator/seniat_actual/acceder_sistemas.css') . '">' . "\n";
$cssHtml .= '<style>
.seniat-wrapper{background:var(--sim-white,#fff);border-radius:12px;box-shadow:var(--sim-shadow-lg,0 4px 6px rgba(0,0,0,.07));overflow:hidden;border:1px solid var(--sim-border,#dfe5ee);font-family:system-ui,-apple-system,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif;font-size:1rem;color:#212529;line-height:1.5}
.seniat-wrapper label,.seniat-wrapper h1,.seniat-wrapper h2,.seniat-wrapper h3,.seniat-wrapper h4,.seniat-wrapper h5,.seniat-wrapper h6{font-family:inherit;color:inherit}
#hamburgerMenu{display:none;position:absolute;top:100%;right:0;left:auto;z-index:9999}
#hamburgerMenu.show{display:block}
</style>';
$extraCss = $cssHtml;

$blueNavText = '';

ob_start();
?>
<div class="seniat-wrapper">

<!-- ═══ SENIAT Header: banner + grey bar + blue bar ═══ -->
<app-root _nghost-pgi-c36 ng-version=12.2.17><router-outlet _ngcontent-pgi-c36></router-outlet><app-inicio _nghost-pgi-c62><div _ngcontent-pgi-c62 class=container><div _ngcontent-pgi-c62 class="row align-items-center"><app-headersuc _ngcontent-pgi-c62 style=padding:0 _nghost-pgi-c59><img _ngcontent-pgi-c59 id=banner src="<?= base_url('/assets/img/simulator/seniat_actual/sucesion/banco/logo_banco.png') ?>" width=100%></app-headersuc></div><div _ngcontent-pgi-c62 class="row align-items-center" style=color:#fff;background-color:#d7d7d7><div _ngcontent-pgi-c62 class="bg-light clearfix"><div _ngcontent-pgi-c62 class=float-start><span _ngcontent-pgi-c62 style=color:black><span _ngcontent-pgi-c62 style=color:black><?= htmlspecialchars($nombreCausante ?? '') ?></span></span></div><div _ngcontent-pgi-c62 class=float-end><div style="position:relative;display:inline-block" id="hamburgerWrap"><a href="#" role="button" aria-expanded="false" class="nav-link dropdown-toggle link-secondary" id="hamburgerBtn" onclick="event.preventDefault();var m=document.getElementById('hamburgerMenu');m.classList.toggle('show')"><i class="bi bi-list"></i></a><ul class="dropdown-menu" id="hamburgerMenu" style="right:0;left:auto;min-width:180px"><li style="text-align:center"><a class="dropdown-item" href="<?= base_url('/simulador/servicios_declaracion/logout') ?>" style="color:#212529;text-decoration:none;font:13px Arial,Helvetica,sans-serif;padding:8px 16px">Cerrar sesion</a></li></ul></div><ul _ngcontent-pgi-c62 class="dropdown-menu sf-hidden"></ul></div></div></div><div _ngcontent-pgi-c62 class="row bg-color"><div _ngcontent-pgi-c62 class=col-sm-12 style=text-align:center;color:white><span _ngcontent-pgi-c62 style=width:100vh>&nbsp;</span></div></div>

<!-- ═══ Main content (full width, no sidebar) ═══ -->
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
                <a href="<?= base_url('/simulador/servicios_declaracion/dashboard') ?>" class="btn-ir-sistema">Ir al Sistema <i class="bi bi-hand-index-thumb"></i></a>
            </div>
        </div>
    </div>
</div>

</div>
</div></div></app-inicio></app-root>

<script>document.addEventListener("click",function(e){var w=document.getElementById("hamburgerWrap");if(w&&!w.contains(e.target)){document.getElementById("hamburgerMenu").classList.remove("show")}})</script>

</div><!-- /.seniat-wrapper -->

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/logged_layout.php';
?>
