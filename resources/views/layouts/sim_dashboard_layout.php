<?php
/**
 * SENIAT Dashboard Layout — Wrapper for dashboard/sistema pages
 * Uses: logged_layout.php as outer shell + seniat-wrapper card isolation
 *
 * Required variables: $content, $activeMenu, $activeItem
 * Optional: $extraCss (array of CSS paths), $extraJs (array of JS paths)
 */

// ─── Collect CSS for logged_layout.php (expects string) ────────────
$pageCss = (isset($extraCss) && is_array($extraCss)) ? $extraCss : [];
$cssHtml  = '<link rel="stylesheet" href="' . base_url('/assets/css/simulator/seniat_actual/sucesion/bienes_muebles/banco_legacy.css') . '">' . "\n";
$cssHtml .= '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">' . "\n";
foreach ($pageCss as $css) {
    $cssHtml .= '<link rel="stylesheet" href="' . base_url($css) . '">' . "\n";
}
$cssHtml .= '<style>
.seniat-wrapper{background:var(--sim-white,#fff);border-radius:12px;box-shadow:var(--sim-shadow-lg,0 4px 6px rgba(0,0,0,.07));overflow:hidden;border:1px solid var(--sim-border,#dfe5ee);font-family:system-ui,-apple-system,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif;font-size:1rem;color:#212529;line-height:1.5}
.seniat-wrapper label,.seniat-wrapper h1,.seniat-wrapper h2,.seniat-wrapper h3,.seniat-wrapper h4,.seniat-wrapper h5,.seniat-wrapper h6{font-family:inherit;color:inherit}
.active-link{color:#6c757d!important;font-weight:600}
#hamburgerMenu{display:none;position:absolute;top:100%;right:0;left:auto;z-index:9999}
#hamburgerMenu.show{display:block}
</style>';
$extraCss = $cssHtml;

// ─── Collect JS for logged_layout.php (expects string) ─────────────
$pageJs = (isset($extraJs) && is_array($extraJs)) ? $extraJs : [];
$jsHtml = '';
foreach ($pageJs as $js) {
    $jsHtml .= '<script src="' . base_url($js) . '"></script>' . "\n";
}

// ─── Save page content before buffering ────────────────────────────
$pageContent = $content ?? '';
$blueNavText = $blueNavText ?? '';

// ─── Buffer the entire SENIAT content inside seniat-wrapper ────────
ob_start();
?>
<div class="seniat-wrapper">

<!-- ═══ SENIAT Header: banner + grey bar + blue bar ═══ -->
<app-root _nghost-pgi-c36 ng-version=12.2.17><router-outlet _ngcontent-pgi-c36></router-outlet><app-inicio _nghost-pgi-c62><div _ngcontent-pgi-c62 class=container><div _ngcontent-pgi-c62 class="row align-items-center"><app-headersuc _ngcontent-pgi-c62 style=padding:0 _nghost-pgi-c59><img _ngcontent-pgi-c59 id=banner src="<?= base_url('/assets/img/simulator/seniat_actual/sucesion/banco/logo_banco.png') ?>" width=100%></app-headersuc></div><div _ngcontent-pgi-c62 class="row align-items-center" style=color:#fff;background-color:#d7d7d7><div _ngcontent-pgi-c62 class="bg-light clearfix"><div _ngcontent-pgi-c62 class=float-start><span _ngcontent-pgi-c62 style=color:black>RAMON ERNESTO BAUZA MARIN</span></div><div _ngcontent-pgi-c62 class=float-end><div style="position:relative;display:inline-block" id="hamburgerWrap"><a href="#" role="button" aria-expanded="false" class="nav-link dropdown-toggle link-secondary" id="hamburgerBtn" onclick="event.preventDefault();var m=document.getElementById('hamburgerMenu');m.classList.toggle('show')"><i class="bi bi-list"></i></a><ul class="dropdown-menu" id="hamburgerMenu" style="right:0;left:auto;min-width:180px"><li style="text-align:center"><a class="dropdown-item" href="<?= base_url('/simulador/servicios_declaracion/logout') ?>" style="color:#212529;text-decoration:none;font:13px Arial,Helvetica,sans-serif;padding:8px 16px">Cerrar sesion</a></li></ul></div><ul _ngcontent-pgi-c62 class="dropdown-menu sf-hidden"></ul></div></div></div><div _ngcontent-pgi-c62 class="row bg-color"><div _ngcontent-pgi-c62 class=col-sm-12 style=text-align:center;color:white><span _ngcontent-pgi-c62 style=width:100vh><?= $blueNavText ?: '&nbsp;' ?></span></div></div>

<!-- ═══ Sidebar + Content Row ═══ -->
<div _ngcontent-pgi-c62 class=row><div _ngcontent-pgi-c62 class="col-sm-2 px-sm-2" style=background-color:#c1bdbb><app-menusuc _ngcontent-pgi-c62 _nghost-pgi-c61><div _ngcontent-pgi-c61 id=wrapper class=d-flex><div _ngcontent-pgi-c61 id=sidebar-wrapper class="bg-light border-right show"><div _ngcontent-pgi-c61 class=sidebar-heading><div _ngcontent-pgi-c61 style=text-align:center><span _ngcontent-pgi-c61 style=font-size:1em;align-items:center><a _ngcontent-pgi-c61 href="<?= base_url('/simulador/servicios_declaracion/sistemas') ?>" style=cursor:pointer;text-decoration:none;color:inherit><i _ngcontent-pgi-c61 class="bi bi-arrow-left"></i>&nbsp; Inicio</a></span></div></div><div _ngcontent-pgi-c61>

<?php
// ======================== DASHBOARD SIDEBAR ========================
$menuItems = [
    'declaraciones' => [
        'label' => 'Declaraciones',
        'items' => [
            ['label' => 'Enajenación de Inmuebles (Forma 33)'],
            ['label' => 'Tasas e Impuestos (Forma 16)'],
            ['label' => 'IGTF (Forma 21)'],
            ['label' => 'DPP (Forma 16)'],
            ['label' => 'Sucesiones', 'url' => '/simulador/sucesion/principal'],
        ]
    ],
    'consulta' => [
        'label' => 'Consulta',
        'items' => [
            ['label' => 'General'],
            ['label' => 'Liquidación'],
        ]
    ],
    'servicios' => [
        'label' => 'Servicios al contribuyente',
        'items' => [
            ['label' => 'Solicitar Anulación'],
        ]
    ],
    'perfil' => [
        'label' => 'Administración de Perfil',
        'items' => [
            ['label' => 'Actualizar Información'],
            ['label' => 'Pregunta de Seguridad'],
            ['label' => 'Cambio de Clave'],
        ]
    ],
];

$activeMenu = $activeMenu ?? '';
$activeItem = $activeItem ?? '';
?>
<div _ngcontent-pgi-c61 id=accordionFlushExample class="accordion accordion-flush">
<?php foreach ($menuItems as $key => $menu): 
    $isActive = ($key === $activeMenu);
    $btnClass = $isActive ? 'accordion-button' : 'accordion-button collapsed';
    $panelClass = $isActive ? 'accordion-collapse collapse show' : 'accordion-collapse collapse';
?>
    <div class="accordion-item">
        <h2 class="accordion-header"><button class="<?= $btnClass ?>" type="button" data-section="<?= $key ?>"> <?= $menu['label'] ?> </button></h2>
        <div class="<?= $panelClass ?>" data-panel="<?= $key ?>">
            <ul class="list-group">
            <?php foreach ($menu['items'] as $item):
                $itemLabel = is_array($item) ? $item['label'] : $item;
                $itemUrl = (is_array($item) && !empty($item['url'])) ? base_url($item['url']) : '#';
                $isItemActive = ($isActive && $itemLabel === $activeItem);
            ?>
                <li class="list-group-item"><a href="<?= $itemUrl ?>" class="link-secondary<?= $isItemActive ? ' active-link' : '' ?>" style="cursor:pointer;text-decoration:none"><?= $itemLabel ?></a></li>
            <?php endforeach; ?>
            </ul>
        </div>
    </div>
<?php endforeach; ?>
</div>

<script>
(function() {
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
</div></div></div></app-menusuc></div><div _ngcontent-pgi-c62 id=divHijo class=col-sm-10><app-contentsuc _ngcontent-pgi-c62 _nghost-pgi-c60>

<?= $pageContent ?>

</app-contentsuc></div></div></div></app-inicio></app-root>

<script>document.addEventListener("click",function(e){var w=document.getElementById("hamburgerWrap");if(w&&!w.contains(e.target)){document.getElementById("hamburgerMenu").classList.remove("show")}})</script>

</div><!-- /.seniat-wrapper -->

<?php
$content = ob_get_clean();

// ─── Pass to logged_layout.php ─────────────────────────────────────
if (!empty($jsHtml)) {
    $extraJs = $jsHtml;
} else {
    unset($extraJs);
}
$pageTitle = $pageTitle ?? 'Dashboard — Simulador';
$activePage = 'simulador';
include __DIR__ . '/logged_layout.php';
?>
