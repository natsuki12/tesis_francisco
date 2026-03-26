<?php
/**
 * sim_header.php — Shared SENIAT header partial
 * Renders: banner image + grey navbar (name + hamburger) + blue navbar
 * 
 * Required: nothing (uses defaults)
 * Optional: $blueNavText (string, text for the blue bar — empty = blank)
 *           $extraCss (array of CSS paths)
 *           $headerUserName (string, name shown in the grey bar)
 */
$blueNavText = $blueNavText ?? '';
$headerUserName = $headerUserName ?? '';
?>
<!DOCTYPE html>
<html lang=es translate=no>
<head>
<meta charset=utf-8>
<title>iSeniatV2</title>
<meta http-equiv=Cache-Control content="no-cache, no-store, must-revalidate">
<meta http-equiv=Pragma content=no-cache>
<meta http-equiv=Expires content=0>
<meta http-equiv=Last-Modified content=0>
<meta name=viewport content="width=device-width, initial-scale=1">
<meta name=referrer content=no-referrer>
<link rel=icon type=image/x-icon href="data:image/x-icon;base64,AAABAAEAEBAAAAEAIABoBAAAFgAAACgAAAAQAAAAIAAAAAEAIAAAAAAAAAQAAAAAAAAAAAAAAAAAAAAAAAD////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////+///////////////////////////////////c4uj/6+vr/+3u7v/t7e3/7O3t/+7u7v/4+Pj///79/+jk5P/L2Oj/8fX5///////+/v////////n5/P/p5un/AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA==">
<link rel="stylesheet" href="<?= base_url('/assets/vendor/bootstrap-icons/bootstrap-icons.min.css') ?>">
<link rel="stylesheet" href="<?= base_url('/assets/css/simulator/seniat_actual/sucesion/bienes_muebles/banco_legacy.css') ?>">
<?php if (!empty($extraCss)): foreach ($extraCss as $css): ?>
<link rel="stylesheet" href="<?= base_url($css) ?>">
<?php endforeach; endif; ?>
<style>
/* Active sidebar link — keep grey like the rest */
.active-link { color: #6c757d !important; font-weight: 600; }
/* Hamburger dropdown */
#hamburgerMenu { display:none; position:absolute; top:100%; right:0; left:auto; z-index:9999; }
#hamburgerMenu.show { display:block; }
</style>
</head>
<body>
 <app-root _nghost-pgi-c36 ng-version=12.2.17><router-outlet _ngcontent-pgi-c36></router-outlet><app-inicio _nghost-pgi-c62><div _ngcontent-pgi-c62 class=container><div _ngcontent-pgi-c62 class="row align-items-center"><app-headersuc _ngcontent-pgi-c62 style=padding:0 _nghost-pgi-c59><img _ngcontent-pgi-c59 id=banner src="<?= base_url('/assets/img/simulator/seniat_actual/sucesion/banco/logo_banco.png') ?>" width=100%></app-headersuc></div><div _ngcontent-pgi-c62 class="row align-items-center" style=color:#fff;background-color:#d7d7d7><div _ngcontent-pgi-c62 class="bg-light clearfix"><div _ngcontent-pgi-c62 class=float-start><span _ngcontent-pgi-c62 style=color:black><?= htmlspecialchars($headerUserName) ?></span></div><div _ngcontent-pgi-c62 class=float-end><div style="position:relative;display:inline-block" id="hamburgerWrap"><a href="#" role="button" aria-expanded="false" class="nav-link dropdown-toggle link-secondary" id="hamburgerBtn" onclick="event.preventDefault();var m=document.getElementById('hamburgerMenu');m.classList.toggle('show')"><i class="bi bi-list"></i></a><ul class="dropdown-menu" id="hamburgerMenu" style="right:0;left:auto;min-width:180px"><li style="text-align:center"><a class="dropdown-item" href="<?= base_url('/simulador/servicios_declaracion/logout') ?>" style="color:#212529;text-decoration:none;font:13px Arial,Helvetica,sans-serif;padding:8px 16px">Cerrar sesion</a></li></ul></div><ul _ngcontent-pgi-c62 class="dropdown-menu sf-hidden"></ul></div></div></div><div _ngcontent-pgi-c62 class="row bg-color"><div _ngcontent-pgi-c62 class=col-sm-12 style=text-align:center;color:white><span _ngcontent-pgi-c62 style=width:100vh><?= $blueNavText ?: '&nbsp;' ?></span></div></div>
