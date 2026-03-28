<?php
/**
 * PDF Planilla Declaración Sucesoral — FORMA DS-99032
 * Rendered by mPDF — Pixel-perfect replica of declaracion_planilla.html
 * mPDF-safe: no rowspan, no full HTML wrapper, uses <pagebreak />
 */

$fmtBs = fn(float $v) => number_format($v, 2, ',', '.');
$rawImg = dirname(__DIR__, 4) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'logos' . DIRECTORY_SEPARATOR . 'Logo-SENIAT-principal.png';
$logoData = 'data:image/png;base64,' . base64_encode(file_get_contents($rawImg));
$esc = fn(?string $s) => htmlspecialchars((string)($s ?? ''), ENT_QUOTES, 'UTF-8');
?>
<style>
    /* ============================================================
       ESTILOS GENERALES — Idénticos a declaracion_planilla.html
       ============================================================ */
    body {
        font-family: Arial, sans-serif;
        font-size: 9pt;
        color: #000000;
        margin: 0;
        padding: 0;
    }

    table {
        border-collapse: collapse;
        width: 100%;
    }

    .bordered td,
    .bordered th {
        border: 1px solid #000000;
    }

    .no-border {
        border: none !important;
    }

    /* ============================================================
       ENCABEZADO SUPERIOR (Logo + Forma + Sello + Nro)
       ============================================================ */
    .header-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 15px;
    }

    .header-table td {
        vertical-align: middle;
        padding: 5px;
        border: none;
    }

    .header-logo {
        width: 120px;
        text-align: center;
    }

    .header-logo img {
        width: 100px;
        height: auto;
    }

    .header-forma {
        text-align: center;
        font-weight: bold;
        padding: 5px 10px;
    }

    .header-sello {
        text-align: center;
        font-size: 12pt;
        width: 80px;
        padding: 5px;
        border: 1px solid #000000 !important;
        height: 60px;
        line-height: 60px;
    }

    .header-nro-box {
        width: 200px;
        border: 1px solid #000000;
        padding: 8px 10px;
        vertical-align: top;
    }

    .nro-numero {
        font-size: 13pt;
        font-weight: bold;
    }

    .nro-label {
        font-size: 9pt;
    }

    .nro-line {
        border-bottom: 1px solid #000000;
        display: inline-block;
        width: 120px;
        margin-left: 5px;
    }

    /* ============================================================
       SECCIONES A, B, C, D (Datos principales)
       ============================================================ */
    .seccion-datos {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 0;
    }

    .seccion-datos td {
        border: 1px solid #000000;
        padding: 3px 5px;
        vertical-align: top;
    }

    .seccion-titulo {
        font-weight: bold;
        font-size: 9pt;
    }

    .seccion-label-derecha {
        font-weight: bold;
        font-size: 8pt;
        text-align: center;
        width: 160px;
    }

    .seccion-valor-derecha {
        text-align: center;
        font-size: 9pt;
        width: 160px;
    }

    /* ============================================================
       SECCIONES F, G, H, I, J (Barras de título con fondo gris)
       ============================================================ */
    .barra-titulo {
        background-color: #e0e0e0;
        font-weight: bold;
        text-align: center;
        font-size: 9pt;
        padding: 3px 5px;
    }

    .sin-info {
        text-align: center;
        padding: 5px;
        font-size: 9pt;
    }

    /* ============================================================
       TABLA G - HEREDEROS
       ============================================================ */
    .herederos-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 8pt;
    }

    .herederos-table td,
    .herederos-table th {
        border: 1px solid #000000;
        padding: 2px 4px;
    }

    .herederos-table th {
        font-weight: normal;
        font-size: 7pt;
        text-align: center;
        background-color: #f0f0f0;
    }

    /* ============================================================
       TABLA I - AUTOLIQUIDACIÓN DEL IMPUESTO
       ============================================================ */
    .autoliq-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 8pt;
    }

    .autoliq-table td {
        border: 1px solid #000000;
        padding: 2px 4px;
    }

    .autoliq-nro {
        width: 20px;
        text-align: center;
    }

    .autoliq-concepto {
        text-align: left;
    }

    .autoliq-monto {
        width: 90px;
        text-align: right;
    }

    .autoliq-subtitulo {
        font-weight: bold;
        text-align: center;
        font-size: 9pt;
        padding: 4px;
    }

    /* ============================================================
       SECCIÓN J - ANEXOS (Bienes, Pasivos, etc.)
       ============================================================ */
    .anexo-subtitulo {
        font-weight: bold;
        font-size: 9pt;
        padding: 3px 5px;
        border: 1px solid #000000;
    }

    .anexo-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 7pt;
    }

    .anexo-table td,
    .anexo-table th {
        border: 1px solid #000000;
        padding: 2px 3px;
        vertical-align: top;
    }

    .anexo-table th {
        font-weight: normal;
        font-size: 7pt;
        background-color: #f0f0f0;
    }

    /* ============================================================
       PIE DE PÁGINA
       ============================================================ */
    .footer {
        margin-top: 30px;
        font-size: 9pt;
    }

    .footer-texto {
        font-weight: bold;
        font-style: italic;
    }

    .footer-pagina {
        text-align: right;
    }

    /* ============================================================
       LÍNEA SUCESIÓN (solo página 2)
       ============================================================ */
    .sucesion-line {
        text-align: right;
        font-size: 8pt;
        margin-bottom: 5px;
    }
</style>

<?php
// ─── Bloque reutilizable: ENCABEZADO ───
$printHeader = function() use ($logoData, $nroPlanilla, $esc) {
?>
<div style="text-align: center; font-size: 8pt; color: #cc0000; font-weight: bold; margin-bottom: 5px; padding: 3px; border: 1px solid #cc0000;">
    ESTE NO ES UN DOCUMENTO OFICIAL, ES UNA RÉPLICA CON FINES EDUCATIVOS
</div>
<table class="header-table">
    <tr>
        <td class="header-logo">
            <img src="<?= $logoData ?>" alt="SENIAT" width="120">
        </td>
        <td class="header-forma">
            <div style="font-size: 10pt; margin-bottom: 3px;">FORMA DS-99032</div>
            <div style="font-size: 10pt; margin-bottom: 3px;">DECLARACIÓN DEFINITIVA</div>
            <div style="font-size: 9pt;">IMPUESTO SOBRE SUCESIONES</div>
        </td>
        <td class="header-sello" style="border: 1px solid #000000; height: 60px; line-height: 60px;">
            SELLO
        </td>
        <td class="header-nro-box">
            <div style="margin-bottom: 6px;">
                <span class="nro-label">Nro.</span>&nbsp;&nbsp;
                <span class="nro-numero"><?= $esc($nroPlanilla) ?></span>
            </div>
            <div style="margin-bottom: 6px;">
                <span class="nro-label">Fecha de</span>
                <span class="nro-line">&nbsp;</span>
            </div>
            <div>
                <span class="nro-label">Nro. de</span>
                <span class="nro-line">&nbsp;</span>
            </div>
        </td>
    </tr>
</table>
<?php }; ?>

<?php
// ─── Bloque reutilizable: SECCIONES A-D ───
$printDatos = function() use ($datos, $esc) {
?>
<table class="seccion-datos">
    <!-- Sección A -->
    <tr>
        <td class="seccion-titulo" colspan="2">A- DATOS DEL CONTRIBUYENTE</td>
        <td class="seccion-label-derecha">N° RIF</td>
    </tr>
    <tr>
        <td colspan="2"><?= $esc($datos['nombre_sucesion'] ?? '') ?></td>
        <td class="seccion-valor-derecha" style="font-weight: bold; font-size: 10pt;"><?= $esc($datos['rif_sucesoral'] ?? '') ?></td>
    </tr>
    <tr>
        <td><strong>FECHA DECLARACIÓN</strong>&nbsp;&nbsp;&nbsp;&nbsp;<?= $esc($datos['fecha_declaracion'] ?? '') ?></td>
        <td><strong>FECHA VENCIMIENTO</strong> <?= $esc($datos['fecha_vencimiento'] ?? '') ?></td>
        <td class="seccion-valor-derecha">&nbsp;</td>
    </tr>

    <!-- Sección B -->
    <tr>
        <td class="seccion-titulo" colspan="2">B- DATOS DEL CAUSANTE O DONANTE</td>
        <td class="seccion-label-derecha">RIF Ó CEDULA</td>
    </tr>
    <tr>
        <td colspan="2"><?= $esc($datos['nombre_causante'] ?? '') ?></td>
        <td class="seccion-valor-derecha"><?= $esc(($datos['rif_causante'] ?? '') . ' / ' . ($datos['cedula_causante'] ?? '')) ?></td>
    </tr>

    <!-- Sección C -->
    <tr>
        <td class="seccion-titulo" colspan="2">C- DIRECCIÓN DEL CAUSANTE O DONANTE</td>
        <td class="seccion-label-derecha">FECHA DE FALLECIMIENTO</td>
    </tr>
    <tr>
        <td colspan="2"><?= $esc($datos['direccion_causante'] ?? '') ?></td>
        <td class="seccion-valor-derecha"><?= $esc($datos['fecha_fallecimiento'] ?? '') ?></td>
    </tr>

    <!-- Sección D -->
    <tr>
        <td class="seccion-titulo" colspan="2">D- DATOS DEL REPRESENTANTE LEGAL O RESPONSABLE</td>
        <td class="seccion-label-derecha">N° RIF</td>
    </tr>
    <tr>
        <td colspan="2"><?= $esc($datos['representante_nombre'] ?? 'SIN INFORMACIÓN') ?></td>
        <td class="seccion-valor-derecha"><?= $esc($datos['representante_rif'] ?? '') ?></td>
    </tr>
</table>
<?php }; ?>


<!-- ================================================================
     PÁGINA 1
     ================================================================ -->

<?php $printHeader(); ?>
<?php $printDatos(); ?>

<br>

<!-- SECCIÓN F - DATOS DE LA PRORROGA -->
<table class="herederos-table">
    <tr><td colspan="5" class="barra-titulo">F- DATOS DE LA PRORROGA</td></tr>
    <?php if (empty($prorrogas)): ?>
    <tr><td colspan="5" class="sin-info">SIN INFORMACIÓN</td></tr>
    <?php else: ?>
    <tr>
        <th>Fecha Solicitud</th>
        <th>Nro. Resolución</th>
        <th>Fecha Resolución</th>
        <th>Plazo Otorgado (días)</th>
        <th>Fecha Vencimiento</th>
    </tr>
    <?php foreach ($prorrogas as $pro): ?>
    <tr>
        <td><?= $esc($pro['fecha_solicitud'] ?? '') ?></td>
        <td><?= $esc($pro['nro_resolucion'] ?? '') ?></td>
        <td><?= $esc($pro['fecha_resolucion'] ?? '') ?></td>
        <td style="text-align: center;"><?= $esc($pro['plazo_dias'] ?? '') ?></td>
        <td><?= $esc($pro['fecha_vencimiento'] ?? '') ?></td>
    </tr>
    <?php endforeach; ?>
    <?php endif; ?>
</table>

<br>

<!-- SECCIÓN G - HEREDEROS -->
<table class="herederos-table">
    <tr>
        <td colspan="9" class="barra-titulo">G- HEREDEROS</td>
    </tr>
    <tr>
        <th style="width: 14%;">Apellido(s)</th>
        <th style="width: 14%;">Nombre(s)</th>
        <th style="width: 11%;">Cedula/Pasaporte</th>
        <th style="width: 9%;">Parentesco</th>
        <th style="width: 6%;">Grado</th>
        <th style="width: 8%;">Premuerto</th>
        <th style="width: 10%;">Cuota parte</th>
        <th style="width: 12%;">Reducción</th>
        <th style="width: 10%;">Impuesto</th>
    </tr>
    <?php if (empty($herederos)): ?>
    <tr><td colspan="9" class="sin-info">SIN INFORMACIÓN</td></tr>
    <?php else: ?>
    <?php foreach ($herederos as $h): ?>
    <tr>
        <td><?= $esc($h['apellidos'] ?? '') ?></td>
        <td><?= $esc($h['nombres'] ?? '') ?></td>
        <td><?= $esc($h['cedula'] ?? '') ?></td>
        <td><?= $esc($h['parentesco'] ?? '') ?></td>
        <td style="text-align: center;"><?= $esc((string)($h['grado'] ?? '1')) ?></td>
        <td style="text-align: center;"><?= $esc($h['premuerto'] ?? 'NO') ?></td>
        <td style="text-align: right;"><?= $fmtBs((float)($h['cuota_parte'] ?? 0)) ?></td>
        <td style="text-align: right;"><?= $fmtBs((float)($h['reduccion'] ?? 0)) ?></td>
        <td style="text-align: right;"><?= $fmtBs((float)($h['impuesto'] ?? 0)) ?></td>
    </tr>
    <?php endforeach; ?>
    <?php endif; ?>
</table>

<!-- SECCIÓN H - IDENTIFICACIÓN DE HEREDEROS PREMUERTOS -->
<table class="herederos-table" style="margin-top: 5px;">
    <tr><td colspan="4" class="barra-titulo">H - IDENTIFICACIÓN DE HEREDEROS PREMUERTOS</td></tr>
    <?php if (empty($premuertos)): ?>
    <tr><td colspan="4" class="sin-info">SIN INFORMACIÓN</td></tr>
    <?php else: ?>
    <tr>
        <th style="width: 25%;">Nombre del Premuerto</th>
        <th style="width: 30%;">Heredero(s) del Premuerto</th>
        <th style="width: 20%;">C.I./Pasaporte</th>
        <th style="width: 25%;">Parentesco</th>
    </tr>
    <?php foreach ($premuertos as $pm): ?>
    <tr>
        <td><?= $esc($pm['representa_a'] ?? '') ?></td>
        <td><?= $esc(($pm['apellidos'] ?? '') . ', ' . ($pm['nombres'] ?? '')) ?></td>
        <td><?= $esc($pm['cedula'] ?? '') ?></td>
        <td><?= $esc($pm['parentesco'] ?? '') ?></td>
    </tr>
    <?php endforeach; ?>
    <?php endif; ?>
</table>

<br>

<!-- SECCIÓN I - AUTOLIQUIDACIÓN DEL IMPUESTO -->
<table class="autoliq-table">
    <tr>
        <td colspan="4" class="barra-titulo">I - AUTOLIQUIDACIÓN DEL IMPUESTO</td>
    </tr>
    <tr>
        <td colspan="2" style="font-weight: bold; font-size: 9pt;"><strong>Conceptos</strong></td>
        <td colspan="2" style="font-weight: bold; font-size: 9pt; text-align: right;"><strong>Gravamen</strong></td>
    </tr>
    <?php foreach ($autoItems as $item): ?>
        <?php if (($item['tipo'] ?? '') === 'separador'): ?>
    <tr>
        <td colspan="4" class="autoliq-subtitulo"><?= $esc($item['concepto']) ?></td>
    </tr>
        <?php else: ?>
    <tr>
        <td class="autoliq-nro"><?= $item['num'] ?? '' ?></td>
        <td class="autoliq-concepto"><?= $esc($item['concepto']) ?></td>
        <td class="autoliq-monto"><?= in_array($item['num'], [1,2,5,6,7]) ? $fmtBs((float)$item['valor']) : '&nbsp;' ?></td>
        <td class="autoliq-monto"><?= in_array($item['num'], [3,4,8,9,10,11,12,13,14,15]) ? $fmtBs((float)$item['valor']) : '&nbsp;' ?></td>
    </tr>
        <?php endif; ?>
    <?php endforeach; ?>
</table>

<!-- PIE DE PÁGINA 1 -->
<table class="footer" style="margin-top: 60px;">
    <tr>
        <td class="footer-texto">Este documento debe ser presentado en las oficina del SENIAT</td>
        <td class="footer-pagina">Pág 1 &nbsp;/ 2</td>
    </tr>
</table>


<!-- ================================================================
     PÁGINA 2
     ================================================================ -->
<pagebreak />

<!-- ENCABEZADO PÁGINA 2 -->
<?php $printHeader(); ?>

<!-- Línea de Sucesión -->
<div class="sucesion-line">
    Sucesión de <?= $esc($datos['nombre_causante'] ?? '') ?> Nro.<?= $esc($nroPlanilla) ?>
</div>

<!-- SECCIONES A-D REPETIDAS EN PÁGINA 2 -->
<?php $printDatos(); ?>

<br>

<!-- SECCIÓN J - ANEXOS -->
<table class="bordered" style="margin-bottom: 5px;">
    <tr>
        <td class="barra-titulo">J - ANEXOS</td>
    </tr>
</table>

<!-- J.1 - BIENES INMUEBLES -->
<table class="anexo-table">
    <tr>
        <td colspan="5" class="anexo-subtitulo" style="background-color: #ffffff;"><strong>Bienes Inmuebles</strong></td>
    </tr>
    <?php if (empty($inmuebles)): ?>
    <tr><td colspan="5" class="sin-info">SIN INFORMACIÓN</td></tr>
    <?php else: ?>
    <tr>
        <th style="width: 8%;">Tipo</th>
        <th style="width: 32%;">Descripción</th>
        <th style="width: 32%;">Registro</th>
        <th style="width: 13%;">Monto Documento</th>
        <th style="width: 13%;">Monto Declarado</th>
    </tr>
    <?php foreach ($inmuebles as $bi): ?>
    <tr>
        <td><?= $esc($bi['tipo'] ?? '') ?></td>
        <td><?= $esc($bi['descripcion'] ?? '') ?></td>
        <td><?= $esc($bi['registro'] ?? '') ?></td>
        <td style="text-align: right;"><?= $fmtBs((float)($bi['valor_original'] ?? 0)) ?></td>
        <td style="text-align: right;"><?= $fmtBs((float)($bi['valor_declarado'] ?? 0)) ?></td>
    </tr>
    <?php endforeach; ?>
    <?php endif; ?>
</table>

<br>

<!-- J.2 - BIENES MUEBLES -->
<?php if (empty($muebles)): ?>
<table class="bordered">
    <tr>
        <td class="anexo-subtitulo" style="text-align: left; background-color: #ffffff;"><strong>Bienes Muebles</strong></td>
    </tr>
</table>
<div class="sin-info" style="margin-top: 5px; margin-bottom: 10px;">SIN INFORMACIÓN</div>
<?php else: ?>
<table class="anexo-table">
    <tr>
        <td colspan="3" class="anexo-subtitulo" style="text-align: left; background-color: #ffffff;"><strong>Bienes Muebles</strong></td>
    </tr>
    <tr>
        <th style="width: 15%;">Tipo</th>
        <th style="width: 65%;">Descripción</th>
        <th style="width: 20%;">Monto Declarado</th>
    </tr>
    <?php foreach ($muebles as $bm): ?>
    <tr>
        <td><?= $esc($bm['categoria'] ?? '') ?></td>
        <td><?= $esc($bm['descripcion'] ?? '') ?></td>
        <td style="text-align: right;"><?= $fmtBs((float)($bm['valor_declarado'] ?? 0)) ?></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>

<!-- J.3 - PASIVOS -->
<?php if (empty($pasivos)): ?>
<table class="bordered">
    <tr>
        <td class="anexo-subtitulo" style="text-align: left; background-color: #ffffff;"><strong>Pasivos</strong></td>
    </tr>
    <tr>
        <td class="sin-info">SIN INFORMACIÓN</td>
    </tr>
</table>
<?php else: ?>
<table class="anexo-table">
    <tr>
        <td colspan="3" class="anexo-subtitulo" style="text-align: left; background-color: #ffffff;"><strong>Pasivos</strong></td>
    </tr>
    <tr>
        <th style="width: 15%;">Tipo</th>
        <th style="width: 65%;">Descripción</th>
        <th style="width: 20%;">Monto Declarado</th>
    </tr>
    <?php foreach ($pasivos as $p): ?>
    <tr>
        <td><?= $esc($p['tipo'] ?? '') ?></td>
        <td><?= $esc($p['descripcion'] ?? '') ?></td>
        <td style="text-align: right;"><?= $fmtBs((float)($p['valor_declarado'] ?? 0)) ?></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>

<br>

<!-- J.4 - DESGRAVAMENES -->
<table class="anexo-table">
    <tr>
        <td colspan="3" class="anexo-subtitulo" style="text-align: left; background-color: #ffffff;"><strong>Desgravamenes</strong></td>
    </tr>
    <?php if (empty($desgravamenes)): ?>
    <tr><td colspan="3" class="sin-info">SIN INFORMACIÓN</td></tr>
    <?php else: ?>
    <tr>
        <th style="width: 10%;">Tipo</th>
        <th>Descripción</th>
        <th style="width: 13%;">Monto Declarado</th>
    </tr>
    <?php foreach ($desgravamenes as $d): ?>
    <tr>
        <td><?= $esc($d['tipo'] ?? '') ?></td>
        <td><?= $esc($d['descripcion'] ?? '') ?></td>
        <td style="text-align: right;"><?= $fmtBs((float)($d['valor'] ?? 0)) ?></td>
    </tr>
    <?php endforeach; ?>
    <?php endif; ?>
</table>

<br>

<!-- J.5 - EXENCIONES -->
<?php if (empty($exenciones)): ?>
<table class="bordered">
    <tr>
        <td class="anexo-subtitulo" style="text-align: left; background-color: #ffffff;"><strong>Exenciones</strong></td>
    </tr>
    <tr>
        <td class="sin-info">SIN INFORMACIÓN</td>
    </tr>
</table>
<?php else: ?>
<table class="anexo-table">
    <tr>
        <td colspan="3" class="anexo-subtitulo" style="text-align: left; background-color: #ffffff;"><strong>Exenciones</strong></td>
    </tr>
    <tr>
        <th style="width: 15%;">Tipo</th>
        <th style="width: 65%;">Descripción</th>
        <th style="width: 20%;">Monto Declarado</th>
    </tr>
    <?php foreach ($exenciones as $ex): ?>
    <tr>
        <td><?= $esc($ex['tipo'] ?? '') ?></td>
        <td><?= $esc($ex['descripcion'] ?? '') ?></td>
        <td style="text-align: right;"><?= $fmtBs((float)($ex['valor_declarado'] ?? 0)) ?></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>

<br>

<!-- J.6 - EXONERACIONES -->
<?php if (empty($exoneraciones)): ?>
<table class="bordered">
    <tr>
        <td class="anexo-subtitulo" style="text-align: left; background-color: #ffffff;"><strong>Exoneraciones</strong></td>
    </tr>
    <tr>
        <td class="sin-info">SIN INFORMACIÓN</td>
    </tr>
</table>
<?php else: ?>
<table class="anexo-table">
    <tr>
        <td colspan="3" class="anexo-subtitulo" style="text-align: left; background-color: #ffffff;"><strong>Exoneraciones</strong></td>
    </tr>
    <tr>
        <th style="width: 15%;">Tipo</th>
        <th style="width: 65%;">Descripción</th>
        <th style="width: 20%;">Monto Declarado</th>
    </tr>
    <?php foreach ($exoneraciones as $exo): ?>
    <tr>
        <td><?= $esc($exo['tipo'] ?? '') ?></td>
        <td><?= $esc($exo['descripcion'] ?? '') ?></td>
        <td style="text-align: right;"><?= $fmtBs((float)($exo['valor_declarado'] ?? 0)) ?></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>

<br>

<!-- J.7 - BIENES LITIGIOSOS -->
<?php if (empty($litigiosos)): ?>
<table class="bordered">
    <tr>
        <td class="anexo-subtitulo" style="text-align: left; background-color: #ffffff;"><strong>Bienes Litigiosos</strong></td>
    </tr>
    <tr>
        <td class="sin-info">SIN INFORMACIÓN</td>
    </tr>
</table>
<?php else: ?>
<table class="anexo-table">
    <tr>
        <td colspan="3" class="anexo-subtitulo" style="text-align: left; background-color: #ffffff;"><strong>Bienes Litigiosos</strong></td>
    </tr>
    <tr>
        <th style="width: 15%;">Tipo</th>
        <th style="width: 65%;">Descripción</th>
        <th style="width: 20%;">Monto Declarado</th>
    </tr>
    <?php foreach ($litigiosos as $lit): ?>
    <tr>
        <td><?= $esc($lit['tipo'] ?? '') ?></td>
        <td><?= $esc($lit['descripcion'] ?? '') ?></td>
        <td style="text-align: right;"><?= $fmtBs((float)($lit['valor'] ?? 0)) ?></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>

<!-- PIE DE PÁGINA 2 -->
<table class="footer" style="margin-top: 60px;">
    <tr>
        <td class="footer-texto">Este documento debe ser presentado en las oficina del SENIAT</td>
        <td class="footer-pagina">Pág 2 &nbsp;/ 2</td>
    </tr>
    <tr>
        <td colspan="2" style="text-align: center; font-size: 8pt; padding-top: 3px;">
            Sucesión de <?= $esc($datos['nombre_causante'] ?? '') ?> Nro.<?= $esc($nroPlanilla) ?>
        </td>
    </tr>
</table>
