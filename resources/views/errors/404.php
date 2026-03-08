<?php
$pageTitle = 'No encontrado — Simulador SENIAT';
$activePage = '';
ob_start();
?>
<div style="text-align:center; padding:4rem 2rem;">
    <h1 style="font-size:4rem; color:#d1d5db; margin:0;">404</h1>
    <h2 style="color:#374151; margin:1rem 0 .5rem;">Página no encontrada</h2>
    <p style="color:#6b7280;">El recurso que buscas no existe o no tienes permiso para verlo.</p>
    <a href="<?= base_url('/casos-sucesorales') ?>"
        style="display:inline-block;margin-top:1.5rem;padding:.625rem 1.25rem;background:#2563eb;color:#fff;border-radius:8px;text-decoration:none;font-weight:600;">Volver
        a Casos</a>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/logged_layout.php';
?>