<?php
/**
 * SENIAT Sucesiones Layout — Shared wrapper for all sucesiones pages
 * Uses: logged_layout.php as outer shell + seniat-wrapper card isolation
 *
 * Required variables: $content, $activeMenu, $activeItem, $breadcrumbs
 * Optional: $extraCss (array of CSS paths), $extraJs (array of JS paths)
 *           $intento (array — active intento, used to extract causante name)
 */

// ─── Resolve causante display name for the grey header bar ─────────
$headerUserName = '';
if (empty($intento)) {
    // Load intento from session if not passed by the route
    try {
        $assignModel = new \App\Modules\Student\Models\StudentAssignmentModel();
        $attemptModel = new \App\Modules\Student\Models\StudentAttemptModel();
        $estudianteId = $assignModel->getEstudianteId((int) ($_SESSION['user_id'] ?? 0));
        if ($estudianteId && !empty($_SESSION['sim_asignacion_id'])) {
            $intento = $attemptModel->getIntentoActivo((int) $_SESSION['sim_asignacion_id']);
        }
    } catch (\Throwable $e) {
        // Silently fail — name will just be empty
    }
}
if (!empty($intento)) {
    $borrador = new \App\Modules\Simulator\Services\BorradorService($intento);
    $headerUserName = $borrador->getNombreCausanteDisplay();
}

// ─── Collect CSS for logged_layout.php (expects string) ────────────
$pageCss = (isset($extraCss) && is_array($extraCss)) ? $extraCss : [];
$cssHtml = '<link rel="stylesheet" href="' . base_url('/assets/css/simulator/seniat_actual/sucesion/bienes_muebles/banco_legacy.css') . '">' . "\n";
$cssHtml .= '<link rel="stylesheet" href="' . base_url('/assets/css/simulator/seniat_actual/sucesion/tabla_seniat.css') . '">' . "\n";
$cssHtml .= '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">' . "\n";
$cssHtml .= '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">' . "\n";
foreach ($pageCss as $css) {
    $cssHtml .= '<link rel="stylesheet" href="' . base_url($css) . '">' . "\n";
}
$cssHtml .= '<style>
.seniat-wrapper{background:var(--sim-white,#fff);border-radius:12px;box-shadow:var(--sim-shadow-lg,0 4px 6px rgba(0,0,0,.07));overflow:hidden;border:1px solid var(--sim-border,#dfe5ee);font-family:system-ui,-apple-system,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif;font-size:1rem;color:#212529;line-height:1.5}
.seniat-wrapper label,.seniat-wrapper h1,.seniat-wrapper h2,.seniat-wrapper h3,.seniat-wrapper h4,.seniat-wrapper h5,.seniat-wrapper h6{font-family:inherit;color:inherit}

/* Bootstrap 5 input-group (missing from banco_legacy.css) */
.seniat-wrapper .input-group{position:relative;display:flex;flex-wrap:wrap;align-items:stretch;width:100%}
.seniat-wrapper .input-group>.form-control,.seniat-wrapper .input-group>.form-select{position:relative;flex:1 1 auto;width:1%;min-width:0}
.seniat-wrapper .input-group>.form-control:focus,.seniat-wrapper .input-group>.form-select:focus{z-index:5}
.seniat-wrapper .input-group .btn{position:relative;z-index:2}
.seniat-wrapper .input-group :not(:first-child):not(.dropdown-menu):not(.valid-tooltip):not(.valid-feedback):not(.invalid-tooltip):not(.invalid-feedback){margin-left:calc(var(--bs-border-width,1px)*-1);border-top-left-radius:0;border-bottom-left-radius:0}
.seniat-wrapper .input-group:not(.has-validation)>:not(:last-child):not(.dropdown-toggle):not(.dropdown-menu){border-top-right-radius:0;border-bottom-right-radius:0}
.seniat-wrapper .input-group-sm>.form-control,.seniat-wrapper .input-group-sm>.form-select,.seniat-wrapper .input-group-sm>.input-group-text,.seniat-wrapper .input-group-sm>.btn{padding:.25rem .5rem;font-size:.875rem;border-radius:var(--bs-border-radius-sm,.25rem)}

/* Bootstrap 5 grid (missing from banco_legacy.css) */
.seniat-wrapper .row{display:flex;flex-wrap:wrap;margin-right:calc(-.5*var(--bs-gutter-x,1.5rem));margin-left:calc(-.5*var(--bs-gutter-x,1.5rem))}
.seniat-wrapper .row>*{flex-shrink:0;width:100%;max-width:100%;padding-right:calc(var(--bs-gutter-x,1.5rem)*.5);padding-left:calc(var(--bs-gutter-x,1.5rem)*.5)}
@media (min-width:576px){
.seniat-wrapper .col-sm-1{flex:0 0 auto;width:8.33333%}
.seniat-wrapper .col-sm-2{flex:0 0 auto;width:16.66667%}
.seniat-wrapper .col-sm-3{flex:0 0 auto;width:25%}
.seniat-wrapper .col-sm-4{flex:0 0 auto;width:33.33333%}
.seniat-wrapper .col-sm-5{flex:0 0 auto;width:41.66667%}
.seniat-wrapper .col-sm-6{flex:0 0 auto;width:50%}
.seniat-wrapper .col-sm-7{flex:0 0 auto;width:58.33333%}
.seniat-wrapper .col-sm-8{flex:0 0 auto;width:66.66667%}
.seniat-wrapper .col-sm-9{flex:0 0 auto;width:75%}
.seniat-wrapper .col-sm-10{flex:0 0 auto;width:83.33333%}
.seniat-wrapper .col-sm-11{flex:0 0 auto;width:91.66667%}
.seniat-wrapper .col-sm-12{flex:0 0 auto;width:100%}
}
.seniat-wrapper .g-2,.seniat-wrapper .gx-2{--bs-gutter-x:0.5rem}
.seniat-wrapper .g-2,.seniat-wrapper .gy-2{--bs-gutter-y:0.5rem}

/* btn-outline-secondary (missing from banco_legacy.css) */
.seniat-wrapper .btn-outline-secondary{color:#6c757d;border:1px solid #6c757d;background-color:transparent}
.seniat-wrapper .btn-outline-secondary:hover{color:#fff;background-color:#6c757d;border-color:#6c757d}

/* Bootstrap 5 table (missing from banco_legacy.css) */
.seniat-wrapper .table{width:100%;margin-bottom:1rem;color:#212529;vertical-align:top;border-color:#dee2e6}
.seniat-wrapper .table>:not(caption)>*>*{padding:.5rem .5rem;background-color:var(--bs-table-bg,transparent);border-bottom-width:1px;box-shadow:inset 0 0 0 9999px var(--bs-table-accent-bg,transparent)}
.seniat-wrapper .table>tbody{vertical-align:inherit}
.seniat-wrapper .table>thead{vertical-align:bottom}
.seniat-wrapper .table>thead>tr>th{font-weight:bold;border-bottom:2px solid #dee2e6}
.seniat-wrapper .table-bordered>:not(caption)>*{border-width:1px 0}
.seniat-wrapper .table-bordered>:not(caption)>*>*{border-width:0 1px}
.seniat-wrapper .table-bordered{border:1px solid #dee2e6}
.seniat-wrapper .table-striped>tbody>tr:nth-of-type(odd)>*{--bs-table-accent-bg:rgba(0,0,0,.05)}
.seniat-wrapper .table-sm>:not(caption)>*>*{padding:.25rem .25rem}
.seniat-wrapper .table .text-center{text-align:center!important}
.seniat-wrapper .table .text-danger{color:#dc3545!important}
.seniat-wrapper .text-success{color:#198754!important}
.seniat-wrapper .text-danger{color:#dc3545!important}
.seniat-wrapper .text-info{--bs-text-opacity:1;color:rgba(13,202,240,var(--bs-text-opacity))!important}

/* table-light (for resumen highlighted rows) */
.seniat-wrapper .table-light{--bs-table-color:#000;--bs-table-bg:#f8f9fa;--bs-table-border-color:#c6c7c8;--bs-table-striped-bg:#ecedee;--bs-table-striped-color:#000;--bs-table-active-bg:#dfe0e1;--bs-table-active-color:#000;--bs-table-hover-bg:#e5e6e7;--bs-table-hover-color:#000;color:var(--bs-table-color);border-color:var(--bs-table-border-color)}
.seniat-wrapper tr.table-light>td,.seniat-wrapper tr.table-light>th,.seniat-wrapper td.table-light,.seniat-wrapper th.table-light,.seniat-wrapper thead.table-light>tr>td,.seniat-wrapper thead.table-light>tr>th{background-color:#f8f9fa!important;color:#000}

/* border-white (for separator rows in resumen) */
.seniat-wrapper .border-white{--bs-border-opacity:1;border-color:rgba(255,255,255,var(--bs-border-opacity))!important}

/* py-2 spacing utility */
.seniat-wrapper .py-2{padding-top:.5rem!important;padding-bottom:.5rem!important}

/* h6 used in resumen "modificar cálculo" section */
.seniat-wrapper h6{margin-top:0;margin-bottom:.5rem;font-weight:500;line-height:1.2;font-size:1rem}

/* Alert component (for declaración tipo) */
.seniat-wrapper .alert{--bs-alert-bg:transparent;--bs-alert-padding-x:1rem;--bs-alert-padding-y:1rem;--bs-alert-margin-bottom:1rem;--bs-alert-color:inherit;--bs-alert-border-color:transparent;--bs-alert-border:1px solid var(--bs-alert-border-color);--bs-alert-border-radius:.375rem;position:relative;padding:var(--bs-alert-padding-y) var(--bs-alert-padding-x);margin-bottom:var(--bs-alert-margin-bottom);color:var(--bs-alert-color);background-color:var(--bs-alert-bg);border:var(--bs-alert-border);border-radius:var(--bs-alert-border-radius)}
.seniat-wrapper .alert-info{--bs-alert-color:#055160;--bs-alert-bg:#cff4fc;--bs-alert-border-color:#b6effb}

/* fw-bold */
.seniat-wrapper .fw-bold{font-weight:700!important}

/* Border utility classes (used in anverso data cells) */
.seniat-wrapper .bordeIzq{border-left:1px solid #dee2e6}
.seniat-wrapper .bordeAbajo{border-bottom:1px solid #dee2e6}
.seniat-wrapper .bordeDer{border-right:1px solid #dee2e6}

/* Table responsive */
.seniat-wrapper .table-responsive{overflow-x:auto;-webkit-overflow-scrolling:touch}

/* Badge */
.seniat-wrapper .badge{--bs-badge-padding-x:0.65em;--bs-badge-padding-y:0.35em;--bs-badge-font-size:0.75em;--bs-badge-font-weight:700;--bs-badge-color:#fff;--bs-badge-border-radius:var(--bs-border-radius,.375rem);display:inline-block;padding:var(--bs-badge-padding-y) var(--bs-badge-padding-x);font-size:var(--bs-badge-font-size);font-weight:var(--bs-badge-font-weight);line-height:1;color:var(--bs-badge-color);text-align:center;white-space:nowrap;vertical-align:initial}
.seniat-wrapper .rounded-pill{border-radius:50rem!important}
.seniat-wrapper .bg-success{--bs-bg-opacity:1;background-color:rgba(25,135,84,var(--bs-bg-opacity))!important}
.seniat-wrapper .bg-danger{--bs-bg-opacity:1;background-color:rgba(220,53,69,var(--bs-bg-opacity))!important}

/* Textarea classes (reverso anexos) */
.seniat-wrapper .lth{width:2%}
.seniat-wrapper .lthgtextarea22{height:150px;display:block;border:none;width:380px}
.seniat-wrapper .lthgtextarea23{width:750px;display:block;border:none;height:150px;text-align:justify}
.seniat-wrapper textarea{margin:0;font-family:inherit;font-size:inherit;line-height:inherit;resize:vertical}

/* Resumen-specific font sizes */
.seniat-wrapper .lenletratablaResumen{font-size:10px}
.seniat-wrapper .lenletraResumen{font-size:12px}

/* Card component (for cálculo manual) */
.seniat-wrapper .card{position:relative;display:flex;flex-direction:column;min-width:0;word-wrap:break-word;background-color:#fff;background-clip:initial;border:1px solid rgba(0,0,0,.125);border-radius:.375rem}
.seniat-wrapper .card-header{padding:.5rem 1rem;margin-bottom:0;background-color:rgba(0,0,0,.03);border-bottom:1px solid rgba(0,0,0,.125)}
.seniat-wrapper .card-header:first-child{border-radius:calc(.375rem - 1px) calc(.375rem - 1px) 0 0}
.seniat-wrapper .card-body{flex:1 1 auto;padding:1rem 1rem}

/* btn-light */
.seniat-wrapper .btn-light{color:#000;background-color:#f8f9fa;border-color:#f8f9fa}
.seniat-wrapper .btn-light:hover{color:#000;background-color:#d3d4d5;border-color:#c6c7c8}

/* Layout utilities */
.seniat-wrapper .clearfix::after{display:block;clear:both;content:""}
.seniat-wrapper .float-start{float:left!important}
.seniat-wrapper .float-end{float:right!important}
.seniat-wrapper .py-3{padding-top:1rem!important;padding-bottom:1rem!important}
.seniat-wrapper .bg-light{background-color:#f8f9fa!important}
.seniat-wrapper .form-group{margin-bottom:1rem}
.seniat-wrapper .text-muted{color:rgba(33,37,41,.75)!important}

.active-link{color:#6c757d!important;font-weight:600}
#hamburgerMenu{display:none;position:absolute;top:100%;right:0;left:auto;z-index:9999}
#hamburgerMenu.show{display:block}

/* Bootstrap 5 modal (missing from banco_legacy.css) */
.seniat-wrapper .modal{display:none;position:fixed;top:0;left:0;z-index:1055;width:100%;height:100%;overflow-x:hidden;overflow-y:auto;outline:0}
.seniat-wrapper .modal.show{display:block}
.seniat-wrapper .modal-backdrop{position:fixed;top:0;left:0;z-index:1050;width:100vw;height:100vh;background-color:#000}
.seniat-wrapper .modal-backdrop.show{opacity:.5}
.seniat-wrapper .modal-dialog{position:relative;width:auto;margin:.5rem;pointer-events:none}
@media (min-width:576px){.seniat-wrapper .modal-dialog{max-width:500px;margin:1.75rem auto}}
.seniat-wrapper .modal-content{position:relative;display:flex;flex-direction:column;width:100%;pointer-events:auto;background-color:#fff;background-clip:padding-box;border:1px solid rgba(0,0,0,.2);border-radius:.5rem;outline:0}
.seniat-wrapper .modal-header{display:flex;flex-shrink:0;align-items:center;padding:1rem;border-bottom:1px solid #dee2e6;border-top-left-radius:calc(.5rem - 1px);border-top-right-radius:calc(.5rem - 1px)}
.seniat-wrapper .modal-title{margin-bottom:0;line-height:1.5}
.seniat-wrapper .modal-body{position:relative;flex:1 1 auto;padding:1rem}
.seniat-wrapper .modal-footer{display:flex;flex-shrink:0;flex-wrap:wrap;align-items:center;justify-content:flex-end;padding:.75rem;border-top:1px solid #dee2e6}
.seniat-wrapper .modal-footer>*{margin:.25rem}
.seniat-wrapper .modal.fade .modal-dialog{transition:transform .3s ease-out;transform:translateY(-50px)}
.seniat-wrapper .modal.show .modal-dialog{transform:none}
.seniat-wrapper .fade{transition:opacity .15s linear}
.seniat-wrapper .fade:not(.show){opacity:0}

/* Bootstrap 5 form-floating (missing from banco_legacy.css) */
.seniat-wrapper .form-floating{position:relative}
.seniat-wrapper .form-floating>.form-control,.seniat-wrapper .form-floating>.form-select{height:calc(3.5rem + 2px);min-height:calc(3.5rem + 2px);line-height:1.25}
.seniat-wrapper .form-floating>label{position:absolute;top:0;left:0;z-index:2;height:100%;padding:1rem .75rem;overflow:hidden;color:rgba(33,37,41,.65);text-align:start;text-overflow:ellipsis;white-space:nowrap;pointer-events:none;border:1px solid transparent;transform-origin:0 0;transition:opacity .1s ease-in-out,transform .1s ease-in-out}
.seniat-wrapper .form-floating>.form-control{padding:1rem .75rem}
.seniat-wrapper .form-floating>.form-control::placeholder{color:transparent}
.seniat-wrapper .form-floating>.form-control:focus~label,.seniat-wrapper .form-floating>.form-control:not(:placeholder-shown)~label,.seniat-wrapper .form-floating>.form-select~label{transform:scale(.85) translateY(-.5rem) translateX(.15rem)}
.seniat-wrapper .form-floating>.form-select{padding-top:1.625rem;padding-bottom:.625rem;padding-left:.75rem}

/* Bootstrap 5 form-control (missing from banco_legacy.css) */
.seniat-wrapper .form-control{display:block;width:100%;padding:.375rem .75rem;font-size:1rem;font-weight:400;line-height:1.5;color:#212529;appearance:none;background-color:#fff;background-clip:padding-box;border:1px solid #dee2e6;border-radius:.375rem;transition:border-color .15s ease-in-out,box-shadow .15s ease-in-out}
.seniat-wrapper .form-control:focus{color:#212529;background-color:#fff;border-color:#86b7fe;outline:0;box-shadow:0 0 0 .25rem rgba(13,110,253,.25)}
.seniat-wrapper .form-control-sm{min-height:calc(1.5em + .5rem + 2px);padding:.25rem .5rem;font-size:.875rem;border-radius:.25rem}
.seniat-wrapper .form-control:disabled{background-color:#e9ecef;opacity:1}
.seniat-wrapper .form-control::placeholder{color:#6c757d;opacity:1}

/* Bootstrap 5 alert-info (missing from banco_legacy.css) */
.seniat-wrapper .alert{position:relative;padding:1rem;margin-bottom:1rem;border:1px solid transparent;border-radius:.375rem}
.seniat-wrapper .alert-info{color:#055160;background-color:#cff4fc;border-color:#b6effb}

/* Bootstrap 5 form-select (missing from banco_legacy.css) */
.seniat-wrapper .form-select{display:block;width:100%;padding:.375rem 2.25rem .375rem .75rem;font-size:1rem;font-weight:400;color:#212529;appearance:none;background-color:#fff;background-image:url("data:image/svg+xml,%3csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 16 16%27%3e%3cpath fill=%27none%27 stroke=%27%23343a40%27 stroke-linecap=%27round%27 stroke-linejoin=%27round%27 stroke-width=%272%27 d=%27m2 5 6 6 6-6%27/%3e%3c/svg%3e");background-repeat:no-repeat;background-position:right .75rem center;background-size:16px 12px;border:1px solid #dee2e6;border-radius:.375rem;transition:border-color .15s ease-in-out,box-shadow .15s ease-in-out}
.seniat-wrapper .form-select:focus{border-color:#86b7fe;outline:0;box-shadow:0 0 0 .25rem rgba(13,110,253,.25)}
.seniat-wrapper .form-select:disabled{background-color:#e9ecef;opacity:1}

/* Keep disabled btn-primary blue but opaque */
.seniat-wrapper .btn-primary:disabled,.seniat-wrapper .btn-primary.disabled{background:var(--blue-600,#0d6efd)!important;color:var(--white,#fff)!important;border-color:var(--blue-600,#0d6efd)!important;opacity:.65;pointer-events:none}
</style>';
$extraCss = $cssHtml;

// ─── Collect JS for logged_layout.php (expects string) ─────────────
$pageJs = (isset($extraJs) && is_array($extraJs)) ? $extraJs : [];
$jsHtml = '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>' . "\n";
$jsHtml .= '<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>' . "\n";
$jsHtml .= '<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>' . "\n";
foreach ($pageJs as $js) {
    $jsHtml .= '<script src="' . base_url($js) . '"></script>' . "\n";
}

// ─── Save page content before buffering ────────────────────────────
$pageContent = $content ?? '';
$blueNavText = $blueNavText ?? 'Autoliquidación de Impuesto sobre Sucesiones';

// ─── Buffer the entire SENIAT content inside seniat-wrapper ────────
ob_start();
?>
<div class="seniat-wrapper">

    <!-- ═══ SENIAT Header: banner + grey bar + blue bar ═══ -->
    <app-root _nghost-pgi-c36 ng-version=12.2.17><router-outlet _ngcontent-pgi-c36></router-outlet><app-inicio
            _nghost-pgi-c62>
            <div _ngcontent-pgi-c62 class=container>
                <div _ngcontent-pgi-c62 class="row align-items-center"><app-headersuc _ngcontent-pgi-c62 style=padding:0
                        _nghost-pgi-c59><img _ngcontent-pgi-c59 id=banner
                            src="<?= base_url('/assets/img/simulator/seniat_actual/sucesion/banco/logo_banco.png') ?>"
                            width=100%></app-headersuc></div>
                <div _ngcontent-pgi-c62 class="row align-items-center" style=color:#fff;background-color:#d7d7d7>
                    <div _ngcontent-pgi-c62 class="bg-light clearfix">
                        <div _ngcontent-pgi-c62 class=float-start><span _ngcontent-pgi-c62 style=color:black><?= htmlspecialchars($headerUserName) ?></span></div>
                        <div _ngcontent-pgi-c62 class=float-end>
                            <div style="position:relative;display:inline-block" id="hamburgerWrap"><a href="#"
                                    role="button" aria-expanded="false" class="nav-link dropdown-toggle link-secondary"
                                    id="hamburgerBtn"
                                    onclick="event.preventDefault();var m=document.getElementById('hamburgerMenu');m.classList.toggle('show')"><i
                                        class="bi bi-list"></i></a>
                                <ul class="dropdown-menu" id="hamburgerMenu" style="right:0;left:auto;min-width:180px">
                                    <li style="text-align:center"><a class="dropdown-item"
                                            href="<?= base_url('/simulador/servicios_declaracion/logout') ?>"
                                            style="color:#212529;text-decoration:none;font:13px Arial,Helvetica,sans-serif;padding:8px 16px">Cerrar
                                            sesion</a></li>
                                </ul>
                            </div>
                            <ul _ngcontent-pgi-c62 class="dropdown-menu sf-hidden"></ul>
                        </div>
                    </div>
                </div>
                <div _ngcontent-pgi-c62 class="row bg-color">
                    <div _ngcontent-pgi-c62 class=col-sm-12 style=text-align:center;color:white><span _ngcontent-pgi-c62
                            style=width:100vh><?= $blueNavText ?></span></div>
                </div>

                <!-- ═══ Sidebar + Content Row ═══ -->
                <div _ngcontent-pgi-c62 class=row>
                    <div _ngcontent-pgi-c62 class="col-sm-2 px-sm-2" style=background-color:#c1bdbb><app-menusuc
                            _ngcontent-pgi-c62 _nghost-pgi-c61>
                            <div _ngcontent-pgi-c61 id=wrapper class=d-flex>
                                <div _ngcontent-pgi-c61 id=sidebar-wrapper class="bg-light border-right show">
                                    <div _ngcontent-pgi-c61 class=sidebar-heading>
                                        <div _ngcontent-pgi-c61 style=text-align:center><span _ngcontent-pgi-c61
                                                style=font-size:1em;align-items:center><a _ngcontent-pgi-c61
                                                    href="<?= base_url('/simulador/servicios_declaracion/dashboard') ?>"
                                                    style=cursor:pointer;text-decoration:none;color:inherit><i
                                                        _ngcontent-pgi-c61 class="bi bi-arrow-left"></i>&nbsp;
                                                    Inicio</a></span></div>
                                    </div>
                                    <div _ngcontent-pgi-c61>

                                        <?php
                                        // ======================== SIDEBAR ACCORDION ========================
                                        $menuItems = [
                                            'herencia' => [
                                                'label' => 'Herencia',
                                                'items' => [
                                                    ['label' => 'Tipo Herencia', 'url' => '/simulador/sucesion/herencia'],
                                                ]
                                            ],
                                            'prorrogas' => ['label' => 'Prórrogas', 'items' => [['label' => 'Prórroga', 'url' => '/simulador/sucesion/prorrogas']]],
                                            'herederos' => ['label' => 'Identificación Herederos', 'items' => [['label' => 'Herederos', 'url' => '/simulador/sucesion/herederos'], ['label' => 'Herederos Premuerto', 'url' => '/simulador/sucesion/herederos_premuerto']]],
                                            'inmuebles' => ['label' => 'Bienes Inmuebles', 'items' => [['label' => 'Bienes Inmuebles', 'url' => '/simulador/sucesion/bienes_inmuebles']]],
                                            'muebles' => [
                                                'label' => 'Bienes Muebles',
                                                'items' => [
                                                    ['label' => 'Banco', 'url' => '/simulador/sucesion/bienes_muebles/banco'],
                                                    ['label' => 'Seguro', 'url' => '/simulador/sucesion/bienes_muebles/seguro'],
                                                    ['label' => 'Transporte', 'url' => '/simulador/sucesion/bienes_muebles/transporte'],
                                                    ['label' => 'Opciones Compra', 'url' => '/simulador/sucesion/bienes_muebles/opciones_compra'],
                                                    ['label' => 'Cuenta y Efectos por cobrar', 'url' => '/simulador/sucesion/bienes_muebles/cuentas_efectos'],
                                                    ['label' => 'Semovientes', 'url' => '/simulador/sucesion/bienes_muebles/semovientes'],
                                                    ['label' => 'Bonos', 'url' => '/simulador/sucesion/bienes_muebles/bonos'],
                                                    ['label' => 'Acciones', 'url' => '/simulador/sucesion/bienes_muebles/acciones'],
                                                    ['label' => 'Prestaciones Sociales', 'url' => '/simulador/sucesion/bienes_muebles/prestaciones_sociales'],
                                                    ['label' => 'Caja de Ahorro', 'url' => '/simulador/sucesion/bienes_muebles/caja_ahorro'],
                                                    ['label' => 'Plantaciones', 'url' => '/simulador/sucesion/bienes_muebles/plantaciones'],
                                                    ['label' => 'Otros', 'url' => '/simulador/sucesion/bienes_muebles/otros'],
                                                ]
                                            ],
                                            'pasivosDeuda' => ['label' => 'Pasivos Deuda', 'items' => [['label' => 'Tarjetas de Crédito', 'url' => '/simulador/sucesion/pasivos_deuda/tarjetas_credito'], ['label' => 'Crédito Hipotecario', 'url' => '/simulador/sucesion/pasivos_deuda/credito_hipotecario'], ['label' => 'Préstamos, Cuentas y Efectos por Pagar', 'url' => '/simulador/sucesion/pasivos_deuda/prestamos'], ['label' => 'Otros', 'url' => '/simulador/sucesion/pasivos_deuda/otros']]],
                                            'pasivosGastos' => ['label' => 'Pasivos Gastos', 'items' => [['label' => 'Pasivos Gastos', 'url' => '/simulador/sucesion/pasivos_gastos']]],
                                            'desgravamenes' => ['label' => 'Desgravámenes', 'items' => [['label' => 'Desgravámenes', 'url' => '/simulador/sucesion/desgravamenes']]],
                                            'exenciones' => ['label' => 'Exenciones', 'items' => [['label' => 'Exenciones', 'url' => '/simulador/sucesion/exenciones']]],
                                            'exoneraciones' => ['label' => 'Exoneraciones', 'items' => [['label' => 'Exoneraciones', 'url' => '/simulador/sucesion/exoneraciones']]],
                                            'litigiosos' => ['label' => 'Bienes Litigiosos', 'items' => [['label' => 'Bienes Litigiosos', 'url' => '/simulador/sucesion/bienes_litigiosos']]],
                                            'resumen' => ['label' => 'Resumen Declaración', 'items' => [['label' => 'Ver Resumen', 'url' => '/simulador/sucesion/resumen_declaracion']]],
                                            'verDeclaracion' => ['label' => 'Ver Declaración', 'items' => [['label' => 'Ver Declaración', 'url' => '/simulador/sucesion/declaracion_anverso']]],
                                        ];

                                        $activeMenu = $activeMenu ?? '';
                                        $activeItem = $activeItem ?? '';
                                        ?>
                                        <div _ngcontent-pgi-c61 id=accordionFlushExample
                                            class="accordion accordion-flush">
                                            <?php foreach ($menuItems as $key => $menu):
                                                $isActive = ($key === $activeMenu);
                                                $btnClass = $isActive ? 'accordion-button' : 'accordion-button collapsed';
                                                $panelClass = $isActive ? 'accordion-collapse collapse show' : 'accordion-collapse collapse';
                                                ?>
                                                <div class="accordion-item">
                                                    <h2 class="accordion-header"><button class="<?= $btnClass ?>"
                                                            type="button" data-section="<?= $key ?>"> <?= $menu['label'] ?>
                                                        </button></h2>
                                                    <div class="<?= $panelClass ?>" data-panel="<?= $key ?>">
                                                        <ul class="list-group">
                                                            <?php foreach ($menu['items'] as $item):
                                                                $itemLabel = is_array($item) ? $item['label'] : $item;
                                                                $itemUrl = (is_array($item) && !empty($item['url'])) ? base_url($item['url']) : '#';
                                                                $isItemActive = ($isActive && $itemLabel === $activeItem);
                                                                ?>
                                                                <li class="list-group-item"><a href="<?= $itemUrl ?>"
                                                                        class="link-secondary<?= $isItemActive ? ' active-link' : '' ?>"
                                                                        style="cursor:pointer"><?= $itemLabel ?></a></li>
                                                            <?php endforeach; ?>
                                                        </ul>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>

                                        <script>
                                            (function () {
                                                const accordion = document.getElementById('accordionFlushExample');
                                                if (!accordion) return;
                                                accordion.querySelectorAll('.accordion-button').forEach(btn => {
                                                    btn.addEventListener('click', () => {
                                                        const section = btn.getAttribute('data-section');
                                                        const panel = accordion.querySelector('[data-panel="' + section + '"]');
                                                        const isOpen = panel.classList.contains('show');
                                                        accordion.querySelectorAll('.accordion-collapse').forEach(p => p.classList.remove('show'));
                                                        accordion.querySelectorAll('.accordion-button').forEach(b => b.classList.add('collapsed'));
                                                        if (!isOpen) {
                                                            panel.classList.add('show');
                                                            btn.classList.remove('collapsed');
                                                        }
                                                    });
                                                });
                                            })();
                                        </script>
                                    </div>
                                </div>
                            </div>
                        </app-menusuc></div>
                    <div _ngcontent-pgi-c62 id=divHijo class=col-sm-10><app-contentsuc _ngcontent-pgi-c62
                            _nghost-pgi-c60>

                            <!-- ═══ Breadcrumb + Page Content ═══ -->
                            <div _ngcontent-pgi-c60 class=row>
                                <div _ngcontent-pgi-c60 class=col-sm-12><router-outlet
                                        _ngcontent-pgi-c60></router-outlet><app-bancos _nghost-pgi-c72>
                                        <div _ngcontent-pgi-c72 class=lenletrabreadcrumb><app-tipodeclaracion
                                                _ngcontent-pgi-c72 _nghost-pgi-c65>
                                                <div _ngcontent-pgi-c65 class=row>
                                                    <div _ngcontent-pgi-c65 class=col-sm-12>
                                                        <div _ngcontent-pgi-c65 role=alert
                                                            class="row alert alert-sm alert-info">
                                                            <div _ngcontent-pgi-c65 class="text-center fw-bold"> SU
                                                                DECLARACIÓN ES TIPO ORIGINARIA</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </app-tipodeclaracion>
                                            <nav _ngcontent-pgi-c72 aria-label=breadcrumb>
                                                <ol _ngcontent-pgi-c72 class=breadcrumb>
                                                    <?php
                                                    $breadcrumbs = $breadcrumbs ?? [];
                                                    foreach ($breadcrumbs as $i => $crumb):
                                                        $isLast = ($i === count($breadcrumbs) - 1);
                                                        if ($isLast): ?>
                                                            <li _ngcontent-pgi-c72 aria-current=page
                                                                class="breadcrumb-item active"><strong
                                                                    _ngcontent-pgi-c72><?= $crumb['label'] ?></strong>
                                                            <?php elseif (!empty($crumb['url'])): ?>
                                                            <li _ngcontent-pgi-c72 class=breadcrumb-item><a _ngcontent-pgi-c72
                                                                    href="<?= base_url($crumb['url']) ?>"><?= $crumb['label'] ?></a>
                                                            <?php else: ?>
                                                            <li _ngcontent-pgi-c72 aria-current=page
                                                                class="breadcrumb-item active"><?= $crumb['label'] ?>
                                                            <?php endif;
                                                    endforeach; ?>
                                                </ol>
                                            </nav>
                                        </div>

                                        <?= $pageContent ?>

                                    </app-bancos></div>
                            </div>

                        </app-contentsuc></div>
                </div>
            </div>
        </app-inicio></app-root>

    <script>document.addEventListener("click", function (e) { var w = document.getElementById("hamburgerWrap"); if (w && !w.contains(e.target)) { document.getElementById("hamburgerMenu").classList.remove("show") } })</script>

    <script>
        // Flatpickr — init all [ngbdatepicker] inputs (replaces Angular ngb-datepicker)
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('input[ngbdatepicker]').forEach(function (input) {
                var fp = flatpickr(input, {
                    locale: 'es',
                    dateFormat: 'd/m/Y',
                    allowInput: true,
                    disableMobile: true
                });
                // Calendar icon click opens the picker
                var icon = input.parentElement.querySelector('.bi-calendar3');
                if (icon) {
                    icon.style.cursor = 'pointer';
                    icon.addEventListener('click', function () { fp.open(); });
                }
            });
        });
    </script>

    <script src="<?= base_url('/assets/js/simulator/seniat_actual/sucesion/datos_tribunal.js') ?>"></script>

</div><!-- /.seniat-wrapper -->

<?php
$content = ob_get_clean();

// ─── Pass to logged_layout.php ─────────────────────────────────────
if (!empty($jsHtml)) {
    $extraJs = $jsHtml;
} else {
    unset($extraJs);
}
$pageTitle = $pageTitle ?? 'Sucesiones — Simulador';
$activePage = 'simulador';
include __DIR__ . '/logged_layout.php';
?>