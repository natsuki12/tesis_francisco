<?php
$files = [
    'resources/views/simulator/legacy/formulario_inscripcion_rif/datos_causante.php',
    'resources/views/simulator/legacy/formulario_inscripcion_rif/direcciones.php',
    'resources/views/simulator/legacy/formulario_inscripcion_rif/relaciones.php',
    'resources/views/simulator/legacy/formulario_inscripcion_rif/validar_inscripcion.php',
];

$assetUrl = "<?= asset('img/simulator/formularios_rif_sucesoral/inscripcion_rif_gradient.jpg') ?>";

foreach ($files as $file) {
    $content = file_get_contents($file);
    if ($content === false) {
        echo "ERROR: No se pudo leer $file\n";
        continue;
    }
    
    // Replace background-IMAGE:url(data:image/jpeg;base64,...) with asset reference
    $pattern = '/background-IMAGE:url\(data:image\/jpeg;base64,[A-Za-z0-9+\/=\s]+\)/';
    $replacement = "background-IMAGE:url(" . $assetUrl . ")";
    
    $newContent = preg_replace($pattern, $replacement, $content);
    
    if ($newContent !== $content) {
        file_put_contents($file, $newContent);
        echo "OK: Reemplazado degradado en $file\n";
    } else {
        echo "SIN CAMBIOS: $file\n";
    }
}

echo "\nListo!\n";
