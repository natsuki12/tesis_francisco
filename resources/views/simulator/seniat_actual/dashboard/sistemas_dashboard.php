<?php
/**
 * Sistemas Dashboard — Main dashboard after selecting a system
 * Uses: sim_dashboard_layout.php
 */
$activeMenu = '';
$activeItem = '';
$extraCss = ['/assets/css/simulator/seniat_actual/dashboard/sistemas_dashboard.css'];

ob_start();
?>

<!-- Empty content area for now -->
<div class="p-3">
    <p class="text-muted">Seleccione una opción del menú lateral.</p>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../../layouts/sim_dashboard_layout.php';
?>
