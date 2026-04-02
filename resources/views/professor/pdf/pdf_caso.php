<?php
/**
 * PDF Case Template — Rendered by mPDF (Complete Version)
 *
 * All variables are extracted in CasosController::descargarPdf()
 */

// Local helpers — prefixed to avoid collision with global helpers
if (!function_exists('pdfFormatBs')) {
    function pdfFormatBs(float $val): string {
        return 'Bs. ' . number_format($val, 2, ',', '.');
    }
}
if (!function_exists('pdfShowVal')) {
    function pdfShowVal($val, string $default = '—'): string {
        return !empty($val) ? htmlspecialchars((string)$val) : $default;
    }
}
if (!function_exists('pdfDate')) {
    function pdfDate($val): string {
        if (empty($val) || !is_string($val)) return '—';
        $ts = strtotime($val);
        return $ts ? date('d/m/Y', $ts) : '—';
    }
}
?>
<style>
    body { font-family: 'dejavusans', sans-serif; font-size: 9pt; color: #1a1a1a; line-height: 1.4; }

    .encabezado { text-align: center; margin-bottom: 16px; padding-bottom: 10px; border-bottom: 2px solid #1a365d; }
    .encabezado h1 { font-size: 14pt; color: #1a365d; margin: 0 0 4px 0; }
    .encabezado p { font-size: 8pt; color: #555; margin: 0 0 4px 0; }
    .encabezado .estado { display: inline-block; padding: 3px 12px; border-radius: 4px; font-size: 8pt; font-weight: bold; margin-top: 6px; }
    .estado-publicado { background: #c6f6d5; color: #276749; }
    .estado-borrador { background: #fefcbf; color: #975a16; }
    .estado-inactivo { background: #e2e8f0; color: #4a5568; }

    .meta-table { width: 100%; border-collapse: collapse; margin: 10px 0 16px 0; }
    .meta-table td { border: none; padding: 4px 10px; font-size: 9pt; }
    .meta-label { font-weight: bold; color: #4a5568; width: 170px; }

    .stats-grid { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
    .stats-grid td { width: 25%; text-align: center; padding: 16px 10px; border: 1px solid #cbd5e0; background: #ffffff; }
    .stat-label { font-size: 7.5pt; color: #4a5568; text-transform: uppercase; display: block; margin-bottom: 6px; letter-spacing: 0.5px; }
    .stat-value { font-size: 13pt; font-weight: bold; color: #1a365d; display: block; }

    .seccion-header { background-color: #1a365d; color: #ffffff; padding: 6px 12px; margin: 20px 0 6px 0; font-size: 10pt; font-weight: bold; }

    .tabla { width: 100%; border-collapse: collapse; margin-bottom: 14px; font-size: 8.5pt; }
    .tabla th { background-color: #e2e8f0; padding: 5px 8px; text-align: left; font-size: 7.5pt; text-transform: uppercase; color: #4a5568; border-bottom: 2px solid #cbd5e0; }
    .tabla td { padding: 5px 8px; border-bottom: 1px solid #edf2f7; vertical-align: top; }
    .tabla .money { text-align: right; }

    .info-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
    .info-table td { padding: 4px 10px; font-size: 9pt; border-bottom: 1px solid #f0f0f0; }
    .info-label { font-weight: bold; color: #4a5568; width: 190px; }

    .badge-small { display: inline-block; padding: 2px 6px; border-radius: 3px; font-size: 7pt; font-weight: bold; }
    .badge-green { background: #c6f6d5; color: #276749; }
    .badge-red { background: #fed7d7; color: #9b2c2c; }
    .badge-gray { background: #e2e8f0; color: #4a5568; }

    .subtotal-row td { font-weight: 600; background: #f7fafc; }
    .total-row td { font-weight: 700; background: #ebf8ff; border-top: 2px solid #1a365d; }

    .tag { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 8pt; background: #edf2f7; color: #2d3748; margin: 2px 4px 2px 0; }

    .bien-block { border: 1px solid #e2e8f0; margin-bottom: 8px; page-break-inside: avoid; }
    .bien-header { background: #edf2f7; padding: 4px 10px; font-weight: 600; font-size: 9pt; color: #2d3748; border-bottom: 1px solid #cbd5e0; }
    .bien-body { padding: 4px 0; }
    .bien-body .info-table td { border-bottom: none; padding: 1px 8px; font-size: 8.5pt; }

    .lit-box { margin: 4px 10px 6px 10px; padding: 6px 10px; background: #fff5f5; border-left: 3px solid #f87171; border-radius: 4px; font-size: 8pt; }
    .lit-box strong { color: #dc2626; }

    .pie-reporte { text-align: center; font-size: 7pt; color: #a0aec0; margin-top: 15px; border-top: 1px solid #e2e8f0; padding-top: 4px; }
</style>

<!-- ═══ MEMBRETE INSTITUCIONAL ═══ -->
<?php include __DIR__ . '/../../partials/pdf/pdf_membrete.php'; ?>

<!-- ═══ TÍTULO DEL CASO ═══ -->
<div style="text-align: center; margin-bottom: 10px;">
    <h1 style="font-size: 14pt; color: #1a365d; margin: 0; text-transform: uppercase;"><?= htmlspecialchars($titulo) ?></h1>
</div>

<!-- ═══ META ═══ -->
<table class="meta-table">
    <tr>
        <td class="meta-label">Causante:</td>
        <td><?= htmlspecialchars($causanteStr) ?></td>
        <td class="meta-label">Representante:</td>
        <td><?= htmlspecialchars($repStr) ?></td>
    </tr>
    <tr>
        <td class="meta-label">Tipo Sucesión:</td>
        <td><?= pdfShowVal($tipoSuc) ?></td>
        <td class="meta-label">Fecha Publicación:</td>
        <td><?= $fechaPublicacion ?></td>
    </tr>

</table>

<!-- ═══ STATS ═══ -->
<table class="stats-grid">
    <tr>
        <td><span class="stat-label">Herederos</span><br><span class="stat-value"><?= $totalHerederos ?></span></td>
        <td><span class="stat-label">Patrimonio Neto</span><br><span class="stat-value"><?= pdfFormatBs($patrimonioNeto) ?></span></td>
        <td><span class="stat-label">Total Activos</span><br><span class="stat-value"><?= pdfFormatBs($totalActivos) ?></span></td>
        <td><span class="stat-label">Total Pasivos</span><br><span class="stat-value"><?= pdfFormatBs($totalPasivos) ?></span></td>
    </tr>
</table>

<!-- ═══ DATOS DEL CAUSANTE ═══ -->
<div class="seccion-header">Datos del Causante</div>
<table class="info-table">
    <tr>
        <td class="info-label">Nombres</td><td><?= pdfShowVal($causante['nombres'] ?? null) ?></td>
        <td class="info-label">Apellidos</td><td><?= pdfShowVal($causante['apellidos'] ?? null) ?></td>
    </tr>
    <tr>
        <td class="info-label">Cédula</td>
        <td><?= !empty($causante['cedula']) ? htmlspecialchars(($causante['tipo_cedula'] ?? 'V') . '-' . $causante['cedula']) : '—' ?></td>
        <td class="info-label">Sexo</td><td><?= pdfShowVal($causante['sexo'] ?? null) ?></td>
    </tr>
    <tr>
        <td class="info-label">Estado Civil</td><td><?= pdfShowVal($causante['estado_civil'] ?? null) ?></td>
        <td class="info-label">Fecha Nacimiento</td>
        <td><?= pdfDate($causante['fecha_nacimiento'] ?? null) ?></td>
    </tr>
    <?php
    // Fecha de fallecimiento
    $fechaFallPdf = '';
    if ($source === 'borrador' && !empty($causante['fecha_fallecimiento'])) {
        $fechaFallPdf = $causante['fecha_fallecimiento'];
    } elseif ($source === 'publicado') {
        $fechaFallPdf = $acta['fecha_fallecimiento'] ?? $caso['causante_fecha_fallecimiento'] ?? $caso['fecha_fallecimiento'] ?? '';
    }
    ?>
    <?php if (!empty($fechaFallPdf)): ?>
    <tr>
        <td class="info-label">Fecha Fallecimiento</td><td><?= pdfDate($fechaFallPdf) ?></td>
        <td class="info-label">Tipo de Sucesión</td><td><?= pdfShowVal($tipoSuc) ?></td>
    </tr>
    <?php endif; ?>
    <?php if (!empty($caso['causante_nacionalidad_nombre'] ?? null)): ?>
    <tr>
        <td class="info-label">Nacionalidad</td><td colspan="3"><?= htmlspecialchars($caso['causante_nacionalidad_nombre']) ?></td>
    </tr>
    <?php endif; ?>
</table>

<!-- ═══ ACTA DE DEFUNCIÓN (publicado + sin cédula) ═══ -->
<?php if (!$esConCedula && !empty($acta)): ?>
<div class="seccion-header">Acta de Defunción</div>
<table class="info-table">
    <tr>
        <td class="info-label">Fecha Fallecimiento</td><td><?= pdfDate($acta['fecha_fallecimiento'] ?? null) ?></td>
        <td class="info-label">Número de Acta</td><td><?= pdfShowVal($acta['numero_acta'] ?? null) ?></td>
    </tr>
    <tr>
        <td class="info-label">Año del Acta</td><td><?= pdfShowVal($acta['year_acta'] ?? null) ?></td>
        <td class="info-label">Parroquia de Registro</td><td><?= pdfShowVal($acta['parroquia_registro'] ?? null) ?></td>
    </tr>
</table>
<?php endif; ?>

<!-- ═══ DATOS FISCALES ═══ -->
<?php if (!empty($datosFiscales)): ?>
<div class="seccion-header">Datos Fiscales del Causante</div>
<table class="info-table">
    <tr>
        <td class="info-label">Domiciliado en el País</td>
        <td><?= ($datosFiscales['domiciliado_pais'] ?? 0) ? 'Sí' : 'No' ?></td>
        <td class="info-label">Fecha Cierre Fiscal</td>
        <td><?= pdfDate($datosFiscales['fecha_cierre_fiscal'] ?? null) ?></td>
    </tr>
</table>
<?php endif; ?>

<!-- ═══ REPRESENTANTE LEGAL ═══ -->
<div class="seccion-header">Representante Legal</div>
<table class="info-table">
    <tr>
        <td class="info-label">Nombres</td><td><?= pdfShowVal($rep['nombres'] ?? $caso['rep_nombres'] ?? null) ?></td>
        <td class="info-label">Apellidos</td><td><?= pdfShowVal($rep['apellidos'] ?? $caso['rep_apellidos'] ?? null) ?></td>
    </tr>
    <?php
    $repCedulaPdf = $rep['cedula'] ?? $caso['rep_cedula'] ?? '';
    $repLetraPdf = $rep['letra_cedula'] ?? $rep['tipo_cedula'] ?? $caso['rep_tipo_cedula'] ?? 'V';
    $repPasaportePdf = $rep['pasaporte'] ?? $caso['rep_pasaporte'] ?? '';
    $repRifPdf = $rep['rif_personal'] ?? $rep['rif'] ?? $caso['rep_rif_personal'] ?? '';
    $repFnPdf = $rep['fecha_nacimiento'] ?? $caso['rep_fecha_nacimiento'] ?? '';
    ?>
    <tr>
        <?php if (!empty($repCedulaPdf)): ?>
            <td class="info-label">Cédula</td><td><?= htmlspecialchars($repLetraPdf . '-' . $repCedulaPdf) ?></td>
        <?php elseif (!empty($repPasaportePdf)): ?>
            <td class="info-label">Pasaporte</td><td><?= htmlspecialchars($repPasaportePdf) ?></td>
        <?php else: ?>
            <td class="info-label">Identificación</td><td>—</td>
        <?php endif; ?>
        <td class="info-label">Sexo</td><td><?= pdfShowVal($rep['sexo'] ?? $caso['rep_sexo'] ?? null) ?></td>
    </tr>
    <?php if (!empty($repRifPdf) || !empty($repFnPdf)): ?>
    <tr>
        <?php if (!empty($repRifPdf)): ?>
            <td class="info-label">RIF</td><td><?= htmlspecialchars($repRifPdf) ?></td>
        <?php else: ?>
            <td></td><td></td>
        <?php endif; ?>
        <?php if (!empty($repFnPdf)): ?>
            <td class="info-label">Fecha Nacimiento</td><td><?= pdfDate($repFnPdf) ?></td>
        <?php else: ?>
            <td></td><td></td>
        <?php endif; ?>
    </tr>
    <?php endif; ?>
</table>

<!-- ═══ TIPOS DE HERENCIA ═══ -->
<?php if (!empty($herenciaTipos)): ?>
<div class="seccion-header">Tipos de Herencia</div>
<table class="tabla">
    <thead>
        <tr>
            <th>Tipo</th>
            <th>Subtipo Testamento</th>
            <th>Fecha Testamento</th>
            <th>Fecha Conclusión Inventario</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($herenciaTipos as $ht): ?>
        <tr>
            <td><?= htmlspecialchars($ht['tipo_nombre'] ?? $ht['nombre'] ?? '—') ?></td>
            <td><?= pdfShowVal($ht['subtipo_testamento'] ?? null) ?></td>
            <td><?= pdfDate($ht['fecha_testamento'] ?? null) ?></td>
            <td><?= pdfDate($ht['fecha_conclusion_inventario'] ?? null) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<!-- ═══ HEREDEROS ═══ -->
<?php if (!empty($herederos)): ?>
<div class="seccion-header">Herederos (<?= count($herederos) ?>)</div>
<table class="tabla">
    <thead><tr><th>Nombre</th><th>Identificación</th><th>Parentesco</th><th>Fecha Nac.</th><th style="text-align:center;">Premuerto</th></tr></thead>
    <tbody>
        <?php foreach ($herederos as $h):
            $hNombre = trim(($h['nombres'] ?? '') . ' ' . ($h['apellidos'] ?? ''));
            $hIdParts = [];
            if (!empty($h['cedula'])) $hIdParts[] = ($h['tipo_cedula'] ?? 'V') . '-' . $h['cedula'];
            if (!empty($h['pasaporte'] ?? '')) $hIdParts[] = 'Pasaporte: ' . $h['pasaporte'];
            if (!empty($h['rif_personal'] ?? '')) $hIdParts[] = 'RIF: ' . $h['rif_personal'];
            $hIdStr = !empty($hIdParts) ? implode(' · ', $hIdParts) : '—';
            $isP = ($h['es_premuerto'] ?? $h['premuerto'] ?? 'NO');
            $isP = ($isP === 1 || $isP === '1' || $isP === 'SI');
        ?>
            <tr>
                <td><?= htmlspecialchars($hNombre) ?></td>
                <td><?= htmlspecialchars($hIdStr) ?></td>
                <td><?= htmlspecialchars($h['parentesco_nombre'] ?? $h['parentesco_id'] ?? '—') ?></td>
                <td><?= pdfDate($h['fecha_nacimiento'] ?? null) ?></td>
                <td style="text-align:center;"><span class="badge-small <?= $isP ? 'badge-red' : 'badge-gray' ?>"><?= $isP ? 'Sí' : 'No' ?></span></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<!-- ═══ HEREDEROS DE PREMUERTOS ═══ -->
<?php if (!empty($herederos_premuertos)): ?>
<div class="seccion-header">Herederos de Premuertos (<?= count($herederos_premuertos) ?>)</div>
<table class="tabla">
    <thead><tr><th>Nombre</th><th>Identificación</th><th>Parentesco</th><th>Representa a</th></tr></thead>
    <tbody>
        <?php foreach ($herederos_premuertos as $hp):
            $hpNombre = trim(($hp['nombres'] ?? '') . ' ' . ($hp['apellidos'] ?? ''));
            $hpIdParts = [];
            if (!empty($hp['cedula'])) $hpIdParts[] = ($hp['tipo_cedula'] ?? 'V') . '-' . $hp['cedula'];
            if (!empty($hp['pasaporte'] ?? '')) $hpIdParts[] = 'Pasaporte: ' . $hp['pasaporte'];
            $hpIdStr = !empty($hpIdParts) ? implode(' · ', $hpIdParts) : '—';
        ?>
            <tr>
                <td><?= htmlspecialchars($hpNombre) ?></td>
                <td><?= htmlspecialchars($hpIdStr) ?></td>
                <td><?= htmlspecialchars($hp['parentesco_nombre'] ?? $hp['parentesco_id'] ?? '—') ?></td>
                <td><?= pdfShowVal($hp['premuerto_padre_nombre'] ?? $hp['premuerto_padre_id'] ?? null) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<!-- ═══ DIRECCIONES DEL CAUSANTE ═══ -->
<?php if (!empty($direcciones)): ?>
<div class="seccion-header">Domicilio Fiscal del Causante</div>
<?php foreach ($direcciones as $dir):
    $tipoDir = str_replace('_', ' ', $dir['tipo_direccion'] ?? 'Domicilio Fiscal');

    // Build address components matching the frontend format
    $tipoVialidad = $dir['tipo_vialidad'] ?? '';
    $nombreVialidad = $dir['nombre_vialidad'] ?? '';
    $tipoInmueble = str_replace('_', ' ', $dir['tipo_inmueble'] ?? '');
    $nombreInmueble = $dir['nombre_inmueble'] ?? '';
    $nroInmueble = $dir['nro_inmueble'] ?? '';
    $tipoNivel = $dir['tipo_nivel'] ?? '';
    $nroNivel = $dir['nro_nivel'] ?? '';
    $tipoSector = $dir['tipo_sector'] ?? '';
    $nombreSector = $dir['nombre_sector'] ?? '';

    $estadoNom = $dir['estado_nombre'] ?? $dir['estado'] ?? '';
    $municipioNom = $dir['municipio_nombre'] ?? $dir['municipio'] ?? '';
    $parroquiaNom = $dir['parroquia_nombre'] ?? $dir['parroquia'] ?? '';
    $ciudadNom = $dir['ciudad_nombre'] ?? $dir['ciudad'] ?? '';
    $codPostal = $dir['codigo_postal_codigo'] ?? $dir['codigo_postal_id'] ?? '';

    $telFijo = $dir['telefono_fijo'] ?? '';
    $telCel = $dir['telefono_celular'] ?? '';
    $fax = $dir['fax'] ?? '';
?>
<div class="bien-block">
    <div class="bien-header"><?= htmlspecialchars($tipoDir) ?></div>
    <div class="bien-body" style="padding: 6px 10px;">
        <!-- Vialidad -->
        <?php if (!empty($tipoVialidad) || !empty($nombreVialidad)): ?>
        <div style="margin-bottom: 3px;">
            <strong style="color:#4a5568;"><?= htmlspecialchars($tipoVialidad) ?>:</strong>
            <?= htmlspecialchars($nombreVialidad) ?>
        </div>
        <?php endif; ?>

        <!-- Inmueble + Nivel -->
        <?php if (!empty($tipoInmueble)): ?>
        <div style="margin-bottom: 3px;">
            <strong style="color:#4a5568;"><?= htmlspecialchars($tipoInmueble) ?>:</strong>
            <?= htmlspecialchars($nombreInmueble) ?>
            <?= !empty($nroInmueble) ? ' - ' . htmlspecialchars($nroInmueble) : '' ?>
            <?php if (!empty($tipoNivel) || !empty($nroNivel)): ?>
                (<?= htmlspecialchars($tipoNivel) ?> <?= htmlspecialchars($nroNivel) ?>)
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Sector -->
        <?php if (!empty($tipoSector) || !empty($nombreSector)): ?>
        <div style="margin-bottom: 3px;">
            <strong style="color:#4a5568;"><?= htmlspecialchars($tipoSector) ?>:</strong>
            <?= htmlspecialchars($nombreSector) ?>
        </div>
        <?php endif; ?>

        <!-- Ubicación geográfica -->
        <div style="margin-bottom: 3px; color: #2d3748;">
            <?php
            $ubParts = [];
            if (!empty($estadoNom)) $ubParts[] = 'Edo. ' . $estadoNom;
            if (!empty($municipioNom)) $ubParts[] = 'Mun. ' . $municipioNom;
            if (!empty($parroquiaNom)) $ubParts[] = 'Pq. ' . $parroquiaNom;
            if (!empty($ciudadNom)) $ubParts[] = $ciudadNom;
            ?>
            <?= htmlspecialchars(implode(', ', $ubParts)) ?>
            <?= !empty($codPostal) ? ' · <strong style="color:#4a5568;">C.P.</strong> ' . htmlspecialchars((string)$codPostal) : '' ?>
        </div>

        <!-- Teléfonos -->
        <?php if (!empty($telFijo) || !empty($telCel) || !empty($fax)): ?>
        <div style="margin-bottom: 3px; font-size: 8.5pt; color: #4a5568;">
            <?php if (!empty($telFijo)): ?>Fijo: <?= htmlspecialchars($telFijo) ?> &nbsp; <?php endif; ?>
            <?php if (!empty($telCel)): ?>Cel: <?= htmlspecialchars($telCel) ?> &nbsp; <?php endif; ?>
            <?php if (!empty($fax)): ?>Fax: <?= htmlspecialchars($fax) ?><?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Punto de referencia -->
        <?php if (!empty($dir['punto_referencia'])): ?>
        <div style="font-size: 8pt; color: #718096; font-style: italic;">
            Ref: <?= htmlspecialchars($dir['punto_referencia']) ?>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endforeach; ?>
<?php endif; ?>

<!-- ═══ PRÓRROGAS ═══ -->
<?php if (!empty($prorrogas)): ?>
<div class="seccion-header">Prórrogas (<?= count($prorrogas) ?>)</div>
<table class="tabla">
    <thead><tr><th>Fecha Solicitud</th><th>Nro. Resolución</th><th>Fecha Resolución</th><th style="text-align:center;">Plazo (días)</th><th>Vencimiento</th></tr></thead>
    <tbody>
        <?php foreach ($prorrogas as $pr): ?>
        <tr>
            <td><?= pdfDate($pr['fecha_solicitud'] ?? null) ?></td>
            <td><?= pdfShowVal($pr['nro_resolucion'] ?? null) ?></td>
            <td><?= pdfDate($pr['fecha_resolucion'] ?? null) ?></td>
            <td style="text-align:center;"><?= pdfShowVal($pr['plazo_otorgado_dias'] ?? $pr['plazo_dias'] ?? null) ?></td>
            <td><?= pdfDate($pr['fecha_vencimiento'] ?? null) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<pagebreak />

<!-- ═══════════════════════════════════════ -->
<!-- INVENTARIO PATRIMONIAL                 -->
<!-- ═══════════════════════════════════════ -->

<!-- ═══ BIENES INMUEBLES (DETALLADO) ═══ -->
<div class="seccion-header">Bienes Inmuebles (<?= count($bienesInmuebles) ?>)</div>
<?php if (empty($bienesInmuebles)): ?>
    <p style="padding:8px;color:#718096;font-style:italic;">No hay bienes inmuebles registrados.</p>
<?php else: ?>
    <?php foreach ($bienesInmuebles as $i => $bi):
        $tipoBien = $bi['tipo_bien_nombres'] ?? $bi['tipo_bien_nombre'] ?? '';
        $desc = $bi['descripcion'] ?? 'Inmueble #' . ($i + 1);
        $headerLabel = !empty($tipoBien) ? $tipoBien : $desc;
        $esVivienda = ($bi['vivienda_principal'] ?? $bi['es_vivienda_principal'] ?? 0);
        $esVivienda = ($esVivienda === 'true' || $esVivienda == 1);
        $esLitigioso = ($bi['bien_litigioso'] ?? $bi['es_bien_litigioso'] ?? 0);
        $esLitigioso = ($esLitigioso === 'true' || $esLitigioso == 1);
    ?>
    <div class="bien-block">
        <div class="bien-header">
            #<?= $i + 1 ?> — <?= htmlspecialchars($headerLabel) ?>
            <?php if ($esVivienda): ?> · <span style="color:#276749;">Vivienda Principal</span><?php endif; ?>
            <?php if ($esLitigioso): ?> · <span style="color:#dc2626;">Litigioso</span><?php endif; ?>
            — <?= pdfFormatBs((float)($bi['valor_declarado'] ?? 0)) ?>
        </div>
        <div class="bien-body">
            <table class="info-table">
                <tr>
                    <td class="info-label">Porcentaje</td><td><?= number_format((float)($bi['porcentaje'] ?? 0), 2) ?>%</td>
                    <td class="info-label">Valor Original</td><td><?= pdfFormatBs((float)($bi['valor_original'] ?? 0)) ?></td>
                </tr>
                <tr>
                    <td class="info-label">Sup. Construida</td><td><?= pdfShowVal($bi['superficie_construida'] ?? null) ?> m²</td>
                    <td class="info-label">Sup. No Construida</td><td><?= pdfShowVal($bi['superficie_no_construida'] ?? null) ?> m²</td>
                </tr>
                <tr>
                    <td class="info-label">Área Superficie</td><td><?= pdfShowVal($bi['area_superficie'] ?? null) ?> m²</td>
                    <td class="info-label">Oficina Registro</td><td><?= pdfShowVal($bi['oficina_registro'] ?? $bi['oficina_subalterna'] ?? null) ?></td>
                </tr>
                <tr>
                    <td class="info-label">Nro. Registro</td><td><?= pdfShowVal($bi['nro_registro'] ?? $bi['numero_registro'] ?? null) ?></td>
                    <td class="info-label">Libro</td><td><?= pdfShowVal($bi['libro'] ?? null) ?></td>
                </tr>
                <tr>
                    <td class="info-label">Protocolo</td><td><?= pdfShowVal($bi['protocolo'] ?? null) ?></td>
                    <td class="info-label">Fecha Registro</td><td><?= pdfDate($bi['fecha_registro'] ?? null) ?></td>
                </tr>
                <tr>
                    <td class="info-label">Trimestre</td><td><?= pdfShowVal($bi['trimestre'] ?? null) ?></td>
                    <td class="info-label">Asiento Registral</td><td><?= pdfShowVal($bi['asiento_registral'] ?? null) ?></td>
                </tr>
                <tr>
                    <td class="info-label">Matrícula</td><td><?= pdfShowVal($bi['matricula'] ?? null) ?></td>
                    <td class="info-label">Libro Folio Real</td><td><?= pdfShowVal($bi['folio_real_anio'] ?? $bi['libro_folio_real'] ?? null) ?></td>
                </tr>
                <?php if (!empty($bi['descripcion'])): ?>
                <tr><td class="info-label">Descripción</td><td colspan="3"><?= htmlspecialchars($bi['descripcion']) ?></td></tr>
                <?php endif; ?>
                <?php if (!empty($bi['linderos'])): ?>
                <tr><td class="info-label">Linderos</td><td colspan="3"><?= htmlspecialchars($bi['linderos']) ?></td></tr>
                <?php endif; ?>
                <?php if (!empty($bi['direccion'])): ?>
                <tr><td class="info-label">Dirección</td><td colspan="3"><?= htmlspecialchars($bi['direccion']) ?></td></tr>
                <?php endif; ?>
            </table>
            <?php
            $litData = $bi['litigioso_data'] ?? null;
            if (!$litData && $esLitigioso) {
                $litData = ['tribunal_causa' => $bi['tribunal_causa'] ?? null, 'numero_expediente' => $bi['numero_expediente'] ?? null, 'partes_juicio' => $bi['partes_juicio'] ?? null, 'estado_juicio' => $bi['estado_juicio'] ?? null];
            }
            if ($esLitigioso && $litData): ?>
            <div class="lit-box">
                <strong>⚠ Datos Litigiosos</strong><br>
                Tribunal: <?= pdfShowVal($litData['tribunal_causa'] ?? null) ?> · 
                Expediente: <?= pdfShowVal($litData['numero_expediente'] ?? null) ?> · 
                Partes: <?= pdfShowVal($litData['partes_juicio'] ?? null) ?> · 
                Estado: <?= pdfShowVal($litData['estado_juicio'] ?? null) ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
<?php endif; ?>

<!-- ═══ BIENES MUEBLES (DETALLADO) ═══ -->
<div class="seccion-header">Bienes Muebles (<?= count($bienesMuebles) ?>)</div>
<?php if (empty($bienesMuebles)): ?>
    <p style="padding:8px;color:#718096;font-style:italic;">No hay bienes muebles registrados.</p>
<?php else: ?>
    <?php foreach ($bienesMuebles as $i => $bm):
        $cat = $bm['categoria_nombre'] ?? $bm['categoria'] ?? 'Sin categoría';
        $tipo = $bm['tipo_nombre'] ?? '';
        $descBm = $bm['descripcion'] ?? 'Bien mueble #' . ($i + 1);
        $esLitigiosoBm = ($bm['es_bien_litigioso'] ?? 0) == 1;
    ?>
    <div class="bien-block">
        <div class="bien-header">
            #<?= $i + 1 ?> — <?= htmlspecialchars($cat) ?><?= !empty($tipo) ? ' — ' . htmlspecialchars($tipo) : '' ?>
            <?php if ($esLitigiosoBm): ?> · <span style="color:#dc2626;">Litigioso</span><?php endif; ?>
            — <?= pdfFormatBs((float)($bm['valor_declarado'] ?? 0)) ?>
        </div>
        <div class="bien-body">
            <table class="info-table">
                <tr>
                    <td class="info-label">Valor Declarado</td><td><?= pdfFormatBs((float)($bm['valor_declarado'] ?? 0)) ?></td>
                    <td class="info-label">Porcentaje</td><td><?= number_format((float)($bm['porcentaje'] ?? 0), 2) ?>%</td>
                </tr>
                <?php if (!empty($bm['descripcion'])): ?>
                <tr><td class="info-label">Descripción</td><td colspan="3"><?= htmlspecialchars($bm['descripcion']) ?></td></tr>
                <?php endif; ?>
                <?php
                // Show category-specific fields if available
                $extraFields = [];
                if (!empty($bm['nombre_banco'] ?? '')) $extraFields['Banco'] = $bm['nombre_banco'];
                if (!empty($bm['numero_cuenta'] ?? '')) $extraFields['Nro. Cuenta'] = $bm['numero_cuenta'];
                if (!empty($bm['marca'] ?? '')) $extraFields['Marca'] = $bm['marca'];
                if (!empty($bm['modelo'] ?? '')) $extraFields['Modelo'] = $bm['modelo'];
                if (!empty($bm['year'] ?? '')) $extraFields['Año'] = $bm['year'];
                if (!empty($bm['placa'] ?? '')) $extraFields['Placa'] = $bm['placa'];
                if (!empty($bm['serial_motor'] ?? '')) $extraFields['Serial Motor'] = $bm['serial_motor'];
                if (!empty($bm['serial_carroceria'] ?? '')) $extraFields['Serial Carrocería'] = $bm['serial_carroceria'];
                if (!empty($bm['nombre_empresa'] ?? '')) $extraFields['Empresa'] = $bm['nombre_empresa'];
                if (!empty($bm['nro_titulo'] ?? '')) $extraFields['Nro. Título'] = $bm['nro_titulo'];
                if (!empty($bm['numero_poliza'] ?? '')) $extraFields['Nro. Póliza'] = $bm['numero_poliza'];
                if (!empty($bm['compania_seguro'] ?? '')) $extraFields['Compañía'] = $bm['compania_seguro'];
                if (!empty($bm['denominacion'] ?? '')) $extraFields['Denominación'] = $bm['denominacion'];
                if (!empty($bm['cantidad'] ?? '')) $extraFields['Cantidad'] = $bm['cantidad'];
                $chunks = array_chunk($extraFields, 2, true);
                foreach ($chunks as $pair):
                    $keys = array_keys($pair);
                    $vals = array_values($pair);
                ?>
                <tr>
                    <td class="info-label"><?= $keys[0] ?></td><td><?= htmlspecialchars($vals[0]) ?></td>
                    <?php if (isset($keys[1])): ?>
                    <td class="info-label"><?= $keys[1] ?></td><td><?= htmlspecialchars($vals[1]) ?></td>
                    <?php else: ?>
                    <td></td><td></td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
    <?php endforeach; ?>
<?php endif; ?>

<!-- ═══ PASIVOS — DEUDAS ═══ -->
<?php if (!empty($pasivosDeuda)): ?>
<div class="seccion-header">Pasivos — Deudas (<?= count($pasivosDeuda) ?>)</div>
<table class="tabla">
    <thead><tr><th style="width:5%">#</th><th style="width:25%">Tipo</th><th>Descripción</th><th style="width:10%;text-align:right;">%</th><th style="width:18%;text-align:right;">Valor</th></tr></thead>
    <tbody>
        <?php foreach ($pasivosDeuda as $i => $pd): ?>
        <tr>
            <td style="text-align:center;"><?= $i + 1 ?></td>
            <td><?= pdfShowVal($pd['tipo_nombre'] ?? $pd['tipo'] ?? null) ?></td>
            <td><?= pdfShowVal($pd['descripcion'] ?? null) ?></td>
            <td style="text-align:right;"><?= number_format((float)($pd['porcentaje'] ?? 0), 2) ?>%</td>
            <td class="money"><?= pdfFormatBs((float)($pd['valor_declarado'] ?? 0)) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<!-- ═══ PASIVOS — GASTOS ═══ -->
<?php if (!empty($pasivosGastos)): ?>
<div class="seccion-header">Pasivos — Gastos (<?= count($pasivosGastos) ?>)</div>
<table class="tabla">
    <thead><tr><th style="width:5%">#</th><th style="width:25%">Tipo</th><th>Descripción</th><th style="width:10%;text-align:right;">%</th><th style="width:18%;text-align:right;">Valor</th></tr></thead>
    <tbody>
        <?php foreach ($pasivosGastos as $i => $pg): ?>
        <tr>
            <td style="text-align:center;"><?= $i + 1 ?></td>
            <td><?= pdfShowVal($pg['tipo_nombre'] ?? $pg['tipo'] ?? null) ?></td>
            <td><?= pdfShowVal($pg['descripcion'] ?? null) ?></td>
            <td style="text-align:right;"><?= number_format((float)($pg['porcentaje'] ?? 0), 2) ?>%</td>
            <td class="money"><?= pdfFormatBs((float)($pg['valor_declarado'] ?? 0)) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<!-- ═══ EXENCIONES ═══ -->
<?php if (!empty($exenciones)): ?>
<div class="seccion-header">Exenciones (<?= count($exenciones) ?>)</div>
<table class="tabla">
    <thead><tr><th>Tipo</th><th>Descripción</th><th style="text-align:right;">Valor</th></tr></thead>
    <tbody>
        <?php foreach ($exenciones as $ex): ?>
        <tr>
            <td><?= pdfShowVal($ex['tipo'] ?? null) ?></td>
            <td><?= pdfShowVal($ex['descripcion'] ?? null) ?></td>
            <td class="money"><?= pdfFormatBs((float)($ex['valor_declarado'] ?? 0)) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<!-- ═══ EXONERACIONES ═══ -->
<?php if (!empty($exoneraciones)): ?>
<div class="seccion-header">Exoneraciones (<?= count($exoneraciones) ?>)</div>
<table class="tabla">
    <thead><tr><th>Tipo</th><th>Descripción</th><th style="text-align:right;">Valor</th></tr></thead>
    <tbody>
        <?php foreach ($exoneraciones as $exo): ?>
        <tr>
            <td><?= pdfShowVal($exo['tipo'] ?? null) ?></td>
            <td><?= pdfShowVal($exo['descripcion'] ?? null) ?></td>
            <td class="money"><?= pdfFormatBs((float)($exo['valor_declarado'] ?? 0)) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<!-- ═══ DESGRAVÁMENES ═══ -->
<?php
$desgravamenesItems = [];
foreach ($bienesInmuebles as $bi) {
    $esVivP = ($bi['es_vivienda_principal'] ?? $bi['vivienda_principal'] ?? 0);
    if ($esVivP == 1 || $esVivP === 'Si' || $esVivP === 'true') {
        $desgravamenesItems[] = [
            'concepto' => 'Vivienda Principal',
            'detalle' => $bi['tipo_bien_nombres'] ?? $bi['tipo_bien_nombre'] ?? ($bi['descripcion'] ?? 'Inmueble'),
            'valor' => (float)($bi['valor_declarado'] ?? 0),
        ];
    }
}
foreach ($bienesMuebles as $bm) {
    $catN = strtolower($bm['categoria_nombre'] ?? $bm['categoria'] ?? '');
    $tipoN = strtolower($bm['tipo_nombre'] ?? '');
    if (strpos($catN, 'seguro') !== false &&
        (strpos($tipoN, 'montep') !== false || strpos($tipoN, 'seguro de vida') !== false)) {
        $desgravamenesItems[] = [
            'concepto' => 'Seguro (' . ($bm['tipo_nombre'] ?? 'Seguro') . ')',
            'detalle' => $bm['descripcion'] ?? '—',
            'valor' => (float)($bm['valor_declarado'] ?? 0),
        ];
    }
    if (strpos($catN, 'prestacion') !== false && empty($bm['banco_id'])) {
        $desgravamenesItems[] = [
            'concepto' => 'Prestaciones Sociales',
            'detalle' => $bm['descripcion'] ?? '—',
            'valor' => (float)($bm['valor_declarado'] ?? 0),
        ];
    }
}
$totalDesgPdf = 0;
foreach ($desgravamenesItems as $dg) $totalDesgPdf += $dg['valor'];
?>
<?php if (!empty($desgravamenesItems)): ?>
<div class="seccion-header">Desgravámenes</div>
<table class="tabla">
    <thead><tr><th>Concepto</th><th>Detalle</th><th style="text-align:right;">Valor</th></tr></thead>
    <tbody>
        <?php foreach ($desgravamenesItems as $dg): ?>
        <tr>
            <td><?= htmlspecialchars($dg['concepto']) ?></td>
            <td><?= htmlspecialchars($dg['detalle']) ?></td>
            <td class="money"><?= pdfFormatBs($dg['valor']) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr class="subtotal-row">
            <td colspan="2" style="text-align:right;font-weight:700;">Total Desgravámenes</td>
            <td class="money" style="font-weight:700;color:#276749;"><?= pdfFormatBs($totalDesgPdf) ?></td>
        </tr>
    </tfoot>
</table>
<?php endif; ?>

<!-- ═══════════════════════════════════════ -->
<!-- RESUMEN DEL TRIBUTO (solo publicado)   -->
<!-- ═══════════════════════════════════════ -->
<?php if ($source === 'publicado' && isset($tInmuebles)): ?>
<pagebreak />

<div class="seccion-header">Resumen Patrimonial y Tributo</div>
<?php if ($utValor > 0): ?>
<table class="info-table" style="margin-bottom:10px;">
    <tr>
        <td class="info-label">Unidad Tributaria (UT)</td>
        <td style="font-weight:700;color:#2563eb;"><?= pdfFormatBs($utValor) ?></td>
        <td class="info-label">Año UT</td>
        <td><?= htmlspecialchars((string)$utAnio) ?></td>
    </tr>
</table>
<?php endif; ?>

<table class="tabla">
    <thead><tr><th style="width:5%;text-align:center;">#</th><th style="width:65%">Concepto</th><th style="text-align:right;">Gravamen</th></tr></thead>
    <tbody>
        <tr><td style="text-align:center;">1</td><td>Total Bienes Inmuebles</td><td class="money"><?= pdfFormatBs($tInmuebles) ?></td></tr>
        <tr><td style="text-align:center;">2</td><td>Total Bienes Muebles</td><td class="money"><?= pdfFormatBs($tMuebles) ?></td></tr>
        <tr class="subtotal-row"><td style="text-align:center;">3</td><td><strong>Patrimonio Hereditario Bruto (1 + 2)</strong></td><td class="money"><?= pdfFormatBs($patrimonioBruto) ?></td></tr>
        <tr class="subtotal-row"><td style="text-align:center;">4</td><td><strong>Activo Hereditario Bruto</strong></td><td class="money"><?= pdfFormatBs($activoHerBruto) ?></td></tr>
        <tr><td style="text-align:center;">5</td><td>Desgravámenes</td><td class="money"><?= pdfFormatBs($totalDesgravamenes) ?></td></tr>
        <tr><td style="text-align:center;">6</td><td>Exenciones</td><td class="money"><?= pdfFormatBs($totalExenciones) ?></td></tr>
        <tr><td style="text-align:center;">7</td><td>Exoneraciones</td><td class="money"><?= pdfFormatBs($totalExoneraciones) ?></td></tr>
        <tr class="subtotal-row"><td style="text-align:center;">8</td><td><strong>Total de Exclusiones</strong></td><td class="money"><?= pdfFormatBs($tExclusiones) ?></td></tr>
        <tr><td style="text-align:center;">9</td><td>Activo Hereditario Neto</td><td class="money"><?= pdfFormatBs($activoHereditarioNeto) ?></td></tr>
        <tr><td style="text-align:center;">10</td><td>Total Pasivo</td><td class="money"><?= pdfFormatBs($totalPasivos) ?></td></tr>
        <tr class="subtotal-row"><td style="text-align:center;">11</td><td><strong>Patrimonio Neto Hereditario</strong></td><td class="money" style="color:<?= $tPatrimonioNeto >= 0 ? '#059669' : '#dc2626' ?>;"><?= pdfFormatBs($tPatrimonioNeto) ?></td></tr>
        <tr><td style="text-align:center;">12</td><td>Impuesto Determinado según Tarifa</td><td class="money"><?= pdfFormatBs($totalImpuesto) ?></td></tr>
        <tr><td style="text-align:center;">13</td><td>Reducciones</td><td class="money"><?= pdfFormatBs($totalReducciones) ?></td></tr>
        <tr class="total-row"><td style="text-align:center;">14</td><td><strong>Total Impuesto a Pagar</strong></td><td class="money" style="color:#2563eb;"><?= pdfFormatBs($totalImpuesto) ?></td></tr>
    </tbody>
</table>

<!-- ═══ CUOTA PARTE HEREDITARIA ═══ -->
<?php if (!empty($herederosCalc)): ?>
<div class="seccion-header">Cuota Parte Hereditaria</div>
<table class="tabla" style="font-size:8pt;">
    <thead><tr>
        <th>Heredero</th><th style="text-align:center;">Cédula</th><th style="text-align:center;">Parentesco</th><th style="text-align:center;">Grado</th>
        <th style="text-align:right;">Cuota (UT)</th><th style="text-align:right;">%</th><th style="text-align:right;">Sustr. (UT)</th>
        <th style="text-align:right;">Imp. Det.</th><th style="text-align:right;">Reduc.</th><th style="text-align:right;">Imp. a Pagar</th>
    </tr></thead>
    <tbody>
        <?php foreach ($herederosCalc as $hc):
            $hh = $hc['h'];
            $nombre = trim(($hh['apellidos'] ?? '') . ' ' . ($hh['nombres'] ?? ''));
            $cedula = !empty($hh['cedula']) ? ($hh['tipo_cedula'] ?? 'V') . '-' . $hh['cedula'] : ($hh['pasaporte'] ?? '—');
            $parentesco = $hh['parentesco_nombre'] ?? $hh['parentesco_id'] ?? '—';
        ?>
        <tr>
            <td><?= htmlspecialchars($nombre ?: '—') ?></td>
            <td style="text-align:center;"><?= htmlspecialchars($cedula) ?></td>
            <td style="text-align:center;"><?= htmlspecialchars((string)$parentesco) ?></td>
            <td style="text-align:center;"><?= $hc['grupo_id'] ?></td>
            <td style="text-align:right;"><?= number_format($hc['cuota_parte_ut'], 2, ',', '.') ?></td>
            <td style="text-align:right;"><?= number_format($hc['porcentaje'], 2, ',', '.') ?></td>
            <td style="text-align:right;"><?= number_format($hc['sustraendo_ut'], 2, ',', '.') ?></td>
            <td style="text-align:right;"><?= number_format($hc['impuesto_determinado'], 2, ',', '.') ?></td>
            <td style="text-align:right;"><?= number_format($hc['reduccion'], 2, ',', '.') ?></td>
            <td style="text-align:right;font-weight:600;"><?= number_format($hc['impuesto_a_pagar'], 2, ',', '.') ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>
<?php endif; ?>

<div class="pie-reporte">
    Generado por SUCELAB — <?= date('d/m/Y H:i:s') ?>
</div>
