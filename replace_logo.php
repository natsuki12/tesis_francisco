<?php
$files = [
    'resources/views/simulator/legacy/formulario_inscripcion_rif/datos_causante.php',
    'resources/views/simulator/legacy/formulario_inscripcion_rif/direcciones.php',
    'resources/views/simulator/legacy/formulario_inscripcion_rif/relaciones.php',
    'resources/views/simulator/legacy/formulario_inscripcion_rif/validar_inscripcion.php',
];

$assetTag = '<img src="<?= asset(\'img/simulator/formularios_rif_sucesoral/logo_inscripcion_rif.jpg\') ?>" border=0 width=207 height=73>';

foreach ($files as $file) {
    $content = file_get_contents($file);
    if ($content === false) {
        echo "ERROR: No se pudo leer $file\n";
        continue;
    }
    
    // Match the base64 GIF logo img tag
    $pattern = '/<img\s+src="data:image\/gif;base64,[^"]*"\s+border=0\s+width=207\s+height=73>/s';
    
    $newContent = preg_replace($pattern, $assetTag, $content);
    
    if ($newContent !== $content) {
        file_put_contents($file, $newContent);
        echo "OK: Reemplazado logo en $file\n";
    } else {
        echo "SIN CAMBIOS: $file (no se encontro el patron)\n";
    }
}

echo "\nListo!\n";
