<?php
$activeMenu = 'desgravamenes';
$activeItem = 'Desgravámenes';
$breadcrumbs = [
    ['label' => 'Inicio', 'url' => '/simulador/servicios_declaracion/dashboard'],
    ['label' => 'Desgravámenes'],
];

/* ── Data from controller ── */
$intento = $intento ?? null;
$borrador = [];
if ($intento && !empty($intento['borrador_json'])) {
    $borrador = json_decode($intento['borrador_json'], true) ?: [];
}

/* ═══ Filtrar desgravámenes de 3 fuentes ═══ */
$desgravamenes = [];

// 1. Bienes Inmuebles con vivienda_principal = 'true'
$inmuebles = $borrador['bienes_inmuebles'] ?? [];
foreach ($inmuebles as $inm) {
    if (($inm['vivienda_principal'] ?? 'false') === 'true') {
        // Construir descripción como en el SENIAT original
        $pct = $inm['porcentaje'] ?? '0,01';
        $desc = $inm['descripcion'] ?? '';
        $tipoNombre = $inm['tipo_bien_nombres'] ?? '';
        $linderos = $inm['linderos'] ?? '';
        $supConst = $inm['superficie_construida'] ?? '';
        $supNoConst = $inm['superficie_no_construida'] ?? '';
        $area = $inm['area_superficie'] ?? '';

        $descripcionFull = "{$pct}% de {$desc}";
        if ($tipoNombre) {
            $descripcionFull .= " Tipo de Bien Inmueble: {$tipoNombre}.";
        }
        if ($linderos) {
            $descripcionFull .= " Linderos: {$linderos}";
        }
        if ($supConst) {
            $descripcionFull .= ", Superficie Construida: {$supConst}";
        }
        if ($supNoConst) {
            $descripcionFull .= ", Superficie Sin Construir: {$supNoConst}";
        }
        if ($area) {
            $descripcionFull .= ", Área o Superficie: {$area}";
        }
        $descripcionFull .= '.';

        $desgravamenes[] = [
            'tipo_bien' => $tipoNombre ?: 'Inmueble',
            'vivienda_principal' => 'Si',
            'bien_litigioso' => ($inm['bien_litigioso'] ?? 'false') === 'true' ? 'Si' : 'No',
            'descripcion' => $descripcionFull,
            'valor_documento' => $inm['valor_declarado'] ?? '0,00',
            'valor_declarado' => $inm['valor_declarado'] ?? '0,00',
        ];
    }
}

// 2. Seguros: Montepío (tipo_bien=09) y Seguro de Vida (tipo_bien=08)
$seguros = $borrador['bienes_muebles_seguro'] ?? [];
foreach ($seguros as $seg) {
    $tipoBien = $seg['tipo_bien'] ?? '';
    if ($tipoBien === '09' || $tipoBien === '08') {
        $pct = $seg['porcentaje'] ?? '0,01';
        $desc = $seg['descripcion'] ?? '';
        $rif = $seg['rif_empresa'] ?? '';
        $razon = $seg['razon_social'] ?? '';
        $prima = $seg['numero_prima'] ?? '';

        $descripcionFull = " {$pct}% de {$desc}. RIF Aseguradora: {$rif} {$razon}, Número Prima: {$prima}. ";

        $desgravamenes[] = [
            'tipo_bien' => $seg['tipo_bien_nombre'] ?? ($tipoBien === '09' ? 'Montepío' : 'Seguro de Vida'),
            'vivienda_principal' => 'No',
            'bien_litigioso' => ($seg['bien_litigioso'] ?? 'false') === 'true' ? 'Si' : 'No',
            'descripcion' => $descripcionFull,
            'valor_documento' => $seg['valor_declarado'] ?? '0,00',
            'valor_declarado' => $seg['valor_declarado'] ?? '0,00',
        ];
    }
}

// 3. Prestaciones Sociales (todas, informativo)
$prestaciones = $borrador['bienes_muebles_prestaciones_sociales'] ?? [];
foreach ($prestaciones as $prest) {
    // Si posee cuenta bancaria, no aparece en desgravámenes
    if (($prest['posee_banco'] ?? 'false') === 'true') continue;

    $pct = $prest['porcentaje'] ?? '0,01';
    $desc = $prest['descripcion'] ?? '';
    $rif = $prest['rif_empresa'] ?? '';
    $razon = $prest['razon_social'] ?? '';

    $descripcionFull = " {$pct}% de {$desc}. RIF Empresa: {$rif} {$razon}. ";

    $desgravamenes[] = [
        'tipo_bien' => 'Prestaciones Sociales',
        'vivienda_principal' => 'No',
        'bien_litigioso' => ($prest['bien_litigioso'] ?? 'false') === 'true' ? 'Si' : 'No',
        'descripcion' => $descripcionFull,
        'valor_documento' => $prest['valor_declarado'] ?? '0,00',
        'valor_declarado' => $prest['valor_declarado'] ?? '0,00',
    ];
}

/* ═══ Calcular total ═══ */
$totalDeclarado = 0;
foreach ($desgravamenes as $item) {
    $val = str_replace('.', '', $item['valor_declarado']);
    $val = str_replace(',', '.', $val);
    $totalDeclarado += (float) $val;
}

ob_start();
?>

<div _ngcontent-sdd-c78 class="shadow-lg p-3 mb-5 bg-body rounded lenletra">
    <div _ngcontent-sdd-c78 class=card>
        <div _ngcontent-sdd-c78 class=card-header>Desgrávamenes</div>
        <div _ngcontent-sdd-c78 class=card-body>
            <?php if (count($desgravamenes) > 0): ?>
                <table _ngcontent-sdd-c78 class="table table-bordered table-striped table-sm">
                    <thead _ngcontent-sdd-c78>
                        <tr _ngcontent-sdd-c78>
                            <th _ngcontent-sdd-c78 scope=col>Tipo de Bien</th>
                            <th _ngcontent-sdd-c78 scope=col>Vivienda Principal</th>
                            <th _ngcontent-sdd-c78 scope=col>Bien Litigioso</th>
                            <th _ngcontent-sdd-c78 scope=col>Descripción</th>
                            <th _ngcontent-sdd-c78 scope=col>Valor Según Documento (Bs.)</th>
                            <th _ngcontent-sdd-c78 scope=col>Valor Declarado (Bs.)</th>
                        </tr>
                    </thead>
                    <tbody _ngcontent-sdd-c78>
                        <?php foreach ($desgravamenes as $item): ?>
                            <tr _ngcontent-sdd-c78>
                                <td _ngcontent-sdd-c78><?= htmlspecialchars($item['tipo_bien']) ?></td>
                                <td _ngcontent-sdd-c78><?= $item['vivienda_principal'] ?></td>
                                <td _ngcontent-sdd-c78><?= $item['bien_litigioso'] ?></td>
                                <td _ngcontent-sdd-c78><?= htmlspecialchars($item['descripcion']) ?></td>
                                <td _ngcontent-sdd-c78 align=right><?= htmlspecialchars($item['valor_documento']) ?></td>
                                <td _ngcontent-sdd-c78 align=right><?= htmlspecialchars($item['valor_declarado']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr _ngcontent-sdd-c78>
                            <td _ngcontent-sdd-c78></td>
                            <td _ngcontent-sdd-c78></td>
                            <td _ngcontent-sdd-c78></td>
                            <td _ngcontent-sdd-c78></td>
                            <td _ngcontent-sdd-c78 align=right>Total:</td>
                            <td _ngcontent-sdd-c78 align=right> <?= number_format($totalDeclarado, 2, ',', '.') ?></td>
                        </tr>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
    <br _ngcontent-sdd-c78>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../../../layouts/sim_sucesiones_layout.php';
?>
