<!--
 SENIAT Dashboard Layout — Wrapper for dashboard/sistema pages
 Required variables: $content, $activeMenu, $activeItem
 Optional: $extraCss (array of CSS paths), $extraJs (array of JS paths)
-->
<?php
$blueNavText = '';
include __DIR__ . '/partials/sim_header.php';

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
$isFirst = true;
?>

<div _ngcontent-pgi-c62 class=row><div _ngcontent-pgi-c62 class="col-sm-2 px-sm-2" style=background-color:#c1bdbb><app-menusuc _ngcontent-pgi-c62 _nghost-pgi-c61><div _ngcontent-pgi-c61 id=wrapper class=d-flex><div _ngcontent-pgi-c61 id=sidebar-wrapper class="bg-light border-right show"><div _ngcontent-pgi-c61 class=sidebar-heading><div _ngcontent-pgi-c61 style=text-align:center><span _ngcontent-pgi-c61 style=font-size:1em;align-items:center><a _ngcontent-pgi-c61 href="<?= base_url('/simulador/servicios_declaracion/sistemas') ?>" style=cursor:pointer;text-decoration:none;color:inherit><i _ngcontent-pgi-c61 class="bi bi-arrow-left"></i>&nbsp; Inicio</a></span></div></div><div _ngcontent-pgi-c61>

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

<?= $content ?? '' ?>

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
