<?php
/**
 * Membrete Institucional del SUCELAB — Partial reutilizable para PDFs
 *
 * Variables esperadas (definidas antes del include):
 *   $pdfTipoDocumento  — string: "Caso Sucesoral", "Reporte de Comparación", "Planilla DS-99032"
 *   $pdfReferencia      — string: "#CASO-1", "#INT-5", etc.
 *   $pdfEstado          — string: "Publicado", "85%", "J-12345678-9", etc.
 *   $pdfEstadoLabel     — string (opcional): "Estado", "Score", "RIF" — label de la columna derecha
 */

// ── Logo como base64 (ruta absoluta al filesystem) ──
$_membrete_logoPath = dirname(__DIR__, 4) . DIRECTORY_SEPARATOR
    . 'public' . DIRECTORY_SEPARATOR
    . 'assets' . DIRECTORY_SEPARATOR
    . 'img' . DIRECTORY_SEPARATOR
    . 'logos' . DIRECTORY_SEPARATOR
    . 'sucelab' . DIRECTORY_SEPARATOR
    . 'sucelab logo_Mesa de trabajo 1-01.png';

$_membrete_logoData = '';
if (file_exists($_membrete_logoPath)) {
    $_membrete_logoData = 'data:image/png;base64,' . base64_encode(file_get_contents($_membrete_logoPath));
}

$_membrete_fecha = date('d/m/Y H:i');
$_membrete_estadoLabel = $pdfEstadoLabel ?? 'Estado';
?>

<style>
    .membrete-wrapper {
        margin-bottom: 14px;
    }
    .membrete-logo-block {
        text-align: center;
        margin-bottom: 4px;
    }
    .membrete-logo-block img {
        width: 320px;
        height: auto;
    }
    .membrete-subtitulo {
        text-align: center;
        font-size: 7.5pt;
        color: #4a5568;
        margin: 0;
        line-height: 1.3;
    }
    .membrete-linea {
        border: none;
        border-top: 2px solid #1a237e;
        margin: 8px 0 6px 0;
    }
    .membrete-meta {
        width: 100%;
        border-collapse: collapse;
    }
    .membrete-meta td {
        border: none;
        padding: 1px 6px;
        font-size: 8pt;
        color: #4a5568;
    }
    .membrete-meta-label {
        font-weight: bold;
        color: #2d3748;
        width: 110px;
    }
</style>

<div class="membrete-wrapper">
    <!-- ═══ LOGO CENTRADO ═══ -->
    <?php if (!empty($_membrete_logoData)): ?>
    <div class="membrete-logo-block">
        <img src="<?= $_membrete_logoData ?>" alt="SUCELAB">
    </div>
    <?php endif; ?>

    <!-- ═══ SUBTÍTULO ═══ -->
    <div class="membrete-subtitulo">
        Sistema Universitario de Capacitación y Evaluación en
        Legislación y Administración de Bienes Sucesorales
    </div>

    <!-- ═══ LÍNEA SEPARADORA ═══ -->
    <hr class="membrete-linea">

    <!-- ═══ FILA DE METADATOS ═══ -->
    <table class="membrete-meta">
        <tr>
            <td class="membrete-meta-label">Generado:</td>
            <td><?= $_membrete_fecha ?></td>
            <td class="membrete-meta-label">Documento:</td>
            <td><?= htmlspecialchars($pdfTipoDocumento ?? 'Documento') ?></td>
        </tr>
        <tr>
            <td class="membrete-meta-label">Ref:</td>
            <td><?= htmlspecialchars($pdfReferencia ?? '') ?></td>
            <td class="membrete-meta-label"><?= htmlspecialchars($_membrete_estadoLabel) ?>:</td>
            <td><?= htmlspecialchars($pdfEstado ?? '') ?></td>
        </tr>
    </table>
</div>

