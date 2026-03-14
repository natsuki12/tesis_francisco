<!--
 SENIAT Sucesiones Layout — Shared wrapper for all sucesiones pages
 Required variables: $content, $activeMenu, $activeItem, $breadcrumbs
 Optional: $extraCss (array of CSS paths), $extraJs (array of JS paths)
-->
<?php
$blueNavText = 'Autoliquidación de Impuesto sobre Sucesiones';
include __DIR__ . '/partials/sim_header.php';
?>
<div _ngcontent-pgi-c62 class=row><div _ngcontent-pgi-c62 class="col-sm-2 px-sm-2" style=background-color:#c1bdbb><app-menusuc _ngcontent-pgi-c62 _nghost-pgi-c61><div _ngcontent-pgi-c61 id=wrapper class=d-flex><div _ngcontent-pgi-c61 id=sidebar-wrapper class="bg-light border-right show"><div _ngcontent-pgi-c61 class=sidebar-heading><div _ngcontent-pgi-c61 style=text-align:center><span _ngcontent-pgi-c61 style=font-size:1em;align-items:center><a _ngcontent-pgi-c61 href="<?= base_url('/simulador/servicios_declaracion/sistemas') ?>" style=cursor:pointer;text-decoration:none;color:inherit><i _ngcontent-pgi-c61 class="bi bi-arrow-left"></i>&nbsp; Inicio</a></span></div></div><div _ngcontent-pgi-c61>

<?php
// ======================== SIDEBAR ACCORDION ========================
$menuItems = [
    'herencia'       => ['label' => 'Herencia',                 'items' => [
        ['label' => 'Tipo Herencia', 'url' => '/simulador/sucesion/herencia'],
    ]],
    'prorrogas'      => ['label' => 'Prórrogas',                'items' => [['label' => 'Prórroga']]],
    'herederos'      => ['label' => 'Identificación Herederos', 'items' => [['label' => 'Herederos'], ['label' => 'Herederos Premuerto']]],
    'inmuebles'      => ['label' => 'Bienes Inmuebles',         'items' => [['label' => 'Bienes Inmuebles']]],
    'muebles'        => ['label' => 'Bienes Muebles',           'items' => [
        ['label' => 'Banco', 'url' => '/simulador/sucesion/bienes_muebles/banco'],
        ['label' => 'Seguro'], ['label' => 'Transporte'], ['label' => 'Opciones Compra'],
        ['label' => 'Cuenta y Efectos por cobrar'], ['label' => 'Semovientes'], ['label' => 'Bonos'],
        ['label' => 'Acciones'], ['label' => 'Prestaciones Sociales'], ['label' => 'Caja de Ahorro'],
        ['label' => 'Plantaciones'], ['label' => 'Otros'],
    ]],
    'pasivosDeuda'   => ['label' => 'Pasivos Deuda',            'items' => [['label' => 'Tarjetas de Crédito'], ['label' => 'Crédito Hipotecario'], ['label' => 'Préstamos, Cuentas y Efectos por Pagar']]],
    'pasivosGastos'  => ['label' => 'Pasivos Gastos',           'items' => [['label' => 'Pasivos Gastos']]],
    'desgravamenes'  => ['label' => 'Desgravámenes',            'items' => [['label' => 'Desgravámenes']]],
    'exenciones'     => ['label' => 'Exenciones',               'items' => [['label' => 'Exenciones']]],
    'exoneraciones'  => ['label' => 'Exoneraciones',            'items' => [['label' => 'Exoneraciones']]],
    'litigiosos'     => ['label' => 'Bienes Litigiosos',        'items' => [['label' => 'Bienes Litigiosos']]],
    'resumen'        => ['label' => 'Resumen Declaración',      'items' => [['label' => 'Ver Resumen']]],
    'verDeclaracion' => ['label' => 'Ver Declaración',          'items' => [['label' => 'Ver Declaración']]],
];

$activeMenu = $activeMenu ?? '';
$activeItem = $activeItem ?? '';
$isFirst = true;
?>
<div _ngcontent-pgi-c61 id=accordionFlushExample class="accordion accordion-flush">
<?php foreach ($menuItems as $key => $menu): 
    $isActive = ($key === $activeMenu);
    $btnClass = $isActive ? 'accordion-button' : 'accordion-button collapsed';
    $panelClass = $isActive ? 'accordion-collapse collapse show' : 'accordion-collapse collapse';
    $itemClass = 'accordion-item';
    $isFirst = false;
?>
    <div class="<?= $itemClass ?>">
        <h2 class="accordion-header"><button class="<?= $btnClass ?>" type="button" data-section="<?= $key ?>"> <?= $menu['label'] ?> </button></h2>
        <div class="<?= $panelClass ?>" data-panel="<?= $key ?>">
            <ul class="list-group">
            <?php foreach ($menu['items'] as $item):
                $itemLabel = is_array($item) ? $item['label'] : $item;
                $itemUrl = (is_array($item) && !empty($item['url'])) ? base_url($item['url']) : '#';
                $isItemActive = ($isActive && $itemLabel === $activeItem);
            ?>
                <li class="list-group-item"><a href="<?= $itemUrl ?>" class="link-secondary<?= $isItemActive ? ' active-link' : '' ?>" style="cursor:pointer"><?= $itemLabel ?></a></li>
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

<div _ngcontent-pgi-c60 class=row><div _ngcontent-pgi-c60 class=col-sm-12><router-outlet _ngcontent-pgi-c60></router-outlet><app-bancos _nghost-pgi-c72><div _ngcontent-pgi-c72 class=lenletrabreadcrumb><app-tipodeclaracion _ngcontent-pgi-c72 _nghost-pgi-c65><div _ngcontent-pgi-c65 class=row><div _ngcontent-pgi-c65 class=col-sm-12><div _ngcontent-pgi-c65 role=alert class="row alert alert-sm alert-info"><div _ngcontent-pgi-c65 class="text-center fw-bold"> SU DECLARACIÓN ES TIPO ORIGINARIA</div></div></div></div></app-tipodeclaracion><nav _ngcontent-pgi-c72 aria-label=breadcrumb><ol _ngcontent-pgi-c72 class=breadcrumb>
<?php
$breadcrumbs = $breadcrumbs ?? [];
foreach ($breadcrumbs as $i => $crumb):
    $isLast = ($i === count($breadcrumbs) - 1);
    if ($isLast): ?>
        <li _ngcontent-pgi-c72 aria-current=page class="breadcrumb-item active"><strong _ngcontent-pgi-c72><?= $crumb['label'] ?></strong>
    <?php elseif (!empty($crumb['url'])): ?>
        <li _ngcontent-pgi-c72 class=breadcrumb-item><a _ngcontent-pgi-c72 href="<?= base_url($crumb['url']) ?>"><?= $crumb['label'] ?></a>
    <?php else: ?>
        <li _ngcontent-pgi-c72 aria-current=page class="breadcrumb-item active"><?= $crumb['label'] ?>
    <?php endif;
endforeach; ?>
</ol></nav></div>

<?= $content ?? '' ?>

</app-bancos></div></div>

</app-contentsuc></div></div></div></app-inicio></app-root>

<script>document.addEventListener("click",function(e){var w=document.getElementById("hamburgerWrap");if(w&&!w.contains(e.target)){document.getElementById("hamburgerMenu").style.display="none"}})</script>

<?php if (!empty($extraJs)):
    if (is_array($extraJs)):
        foreach ($extraJs as $js): ?>
<script src="<?= base_url($js) ?>"></script>
<?php   endforeach;
    else:
        echo $extraJs;
    endif;
endif; ?>
</body>
</html>
