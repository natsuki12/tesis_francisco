<?php
// Read the base64 from datos_causante.php
$content = file_get_contents('resources/views/simulator/legacy/formulario_inscripcion_rif/datos_causante.php');

// Extract the base64 from background-IMAGE:url(data:image/jpeg;base64,...)
if (preg_match('/background-IMAGE:url\(data:image\/jpeg;base64,([A-Za-z0-9+\/=\s]+)\)/', $content, $matches)) {
    $base64 = trim($matches[1]);
    $imageData = base64_decode($base64);
    $outputPath = 'public/assets/img/simulator/formularios_rif_sucesoral/inscripcion_rif_gradient.jpg';
    file_put_contents($outputPath, $imageData);
    $size = getimagesize($outputPath);
    echo "Guardado en: $outputPath\n";
    echo "Dimensiones: {$size[0]}x{$size[1]}\n";
    echo "Tamaño: " . strlen($imageData) . " bytes\n";
} else {
    echo "No se encontro el base64 del degradado\n";
}
