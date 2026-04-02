<?php
/**
 * Declaración Reverso — Secciones A–D (header) + J-Anexos con datos dinámicos.
 *
 * Variables recibidas desde SucesionController::declaracionReverso():
 *   $datos['nombre_sucesion']      — "SUCESION APELLIDOS, NOMBRES"
 *   $datos['rif']                  — RIF sucesoral
 *   $datos[A-D headers]            — Same as anverso
 *   $datos['inmuebles']            — Array de bienes inmuebles items
 *   $datos['muebles']              — Array de bienes muebles items (merged)
 *   $datos['pasivos']              — Array de pasivos items (merged)
 *   $datos['desgravamenes']        — Array de desgravámenes items
 *   $datos['exenciones']           — Array de exenciones items
 *   $datos['exoneraciones']        — Array de exoneraciones items
 *   $datos['total_*']              — Totales formateados para cada sección
 *   $datos['fmt']                  — Función formatDecimal($float)
 */
ob_start();
$activeMenu = 'verDeclaracion';
$activeItem = 'Ver Declaración';
$breadcrumbs = [
    ['label' => 'Inicio', 'url' => '/simulador/servicios_declaracion/dashboard'],
    ['label' => 'Ver Declaración'],
];

// ── Safe extraction ──
$d = $datos ?? [];
$fmt = $d['fmt'] ?? function (float $v) {
    return number_format($v, 2, ',', '.'); };

$inmuebles = $d['inmuebles'] ?? [];
$muebles = $d['muebles'] ?? [];
$pasivos = $d['pasivos'] ?? [];
$desgravamenes = $d['desgravamenes'] ?? [];
$exenciones = $d['exenciones'] ?? [];
$exoneraciones = $d['exoneraciones'] ?? [];

/**
 * Helper: parse decimal for display (Venezuelan format "3.700.000,00" → 3700000.00)
 */
$parseDec = function (string $val) {
    $val = trim($val);
    if ($val === '' || $val === '0')
        return 0.0;
    if (str_contains($val, ',')) {
        $val = str_replace('.', '', $val);
        $val = str_replace(',', '.', $val);
    }
    return (float) $val;
};

/**
 * Helper: Build formatted "Descripción" for inmueble
 * Formato SENIAT: porcentaje + descripción + linderos + superficies + dirección
 */
/**
 * Helper: Format surface value — if > 0 appends "metros cuadrados", if 0 shows "no aplica"
 */
function formatSuperficie(string $val): string
{
    $val = trim($val);
    if ($val === '' || $val === '0' || $val === '0,00') {
        return 'no aplica';
    }
    return $val . ' metros cuadrados';
}

function buildDescripcionInmueble(array $inm): string
{
    $parts = [];

    $porc = $inm['porcentaje'] ?? '';
    if (!empty($porc) && $porc !== '0,00') {
        $parts[] = $porc . '% de ' . ($inm['descripcion'] ?? '');
    } else {
        if (!empty($inm['descripcion'])) {
            $parts[] = $inm['descripcion'];
        }
    }

    if (!empty($inm['tipo_bien_nombres'])) {
        $parts[] = 'Tipo de Bien Inmueble: ' . $inm['tipo_bien_nombres'];
    }

    if (!empty($inm['linderos'])) {
        $parts[] = 'Linderos: ' . $inm['linderos'];
    }

    $superficies = [];
    $superficies[] = 'Superficie Construida: ' . formatSuperficie($inm['superficie_construida'] ?? '');
    $superficies[] = 'Superficie Sin Construir: ' . formatSuperficie($inm['superficie_no_construida'] ?? '');
    $superficies[] = 'Área o Superficie: ' . formatSuperficie($inm['area_superficie'] ?? '');
    $parts[] = implode(', ', $superficies);

    if (!empty($inm['direccion'])) {
        $parts[] = 'Dirección: ' . $inm['direccion'];
    }

    return implode('. ', array_filter($parts));
}

/**
 * Helper: Build formatted "Registro" for inmueble
 * Formato SENIAT: Oficina Subalterna + Nro Registro + Libro + Protocolo + Fecha + Trimestre
 *                 + Asiento Registral + Matrícula + Libro de Folio Real
 */
function buildRegistroInmueble(array $inm): string
{
    $parts = [];

    if (!empty($inm['oficina_registro'])) {
        $parts[] = 'Oficina Subalterna/Juzgado/Notaría/Misión Vivienda: ' . $inm['oficina_registro'];
    }
    if (!empty($inm['nro_registro'])) {
        $parts[] = 'Número de Registro: ' . $inm['nro_registro'];
    }
    if (!empty($inm['libro'])) {
        $parts[] = 'Libro: ' . $inm['libro'];
    }
    if (!empty($inm['protocolo'])) {
        $parts[] = 'Protocolo: ' . $inm['protocolo'];
    }
    if (!empty($inm['fecha_registro'])) {
        $parts[] = 'Fecha: ' . $inm['fecha_registro'];
    }
    if (!empty($inm['trimestre'])) {
        $parts[] = 'Trimestre: ' . $inm['trimestre'];
    }
    if (!empty($inm['asiento_registral'])) {
        $parts[] = 'Asiento Registral: ' . $inm['asiento_registral'];
    }
    if (!empty($inm['matricula'])) {
        $parts[] = 'Matrícula: ' . $inm['matricula'];
    }
    if (!empty($inm['folio_real_anio'])) {
        $parts[] = 'Libro de Folio Real del Año: ' . $inm['folio_real_anio'];
    }

    return implode(', ', array_filter($parts));
}

/**
 * Helper: Build formatted "Descripción" for bienes muebles.
 * Replica el formato usado en cada tabla de guardado por sección.
 *
 * @param array $mue  Item de bienes muebles (con 'categoria' inyectado por BorradorService)
 * @return string     Descripción formateada
 */
function buildDescripcionMueble(array $mue): string
{
    $pct  = $mue['porcentaje'] ?? '';
    $desc = $mue['descripcion'] ?? '';
    $cat  = $mue['categoria'] ?? '';

    // Base: "X% de DESCRIPCION"
    $base = '';
    if (!empty($pct) && $pct !== '0,00') {
        $base = $pct . '% de ' . $desc;
    } else {
        $base = $desc;
    }

    // Agregar campos específicos según categoría
    $extra = '';
    switch ($cat) {
        case 'Banco':
            $parts = [];
            if (!empty($mue['banco_nombre'])) $parts[] = 'Banco: ' . $mue['banco_nombre'];
            if (!empty($mue['numero_cuenta'])) $parts[] = 'Número de Cuenta: ' . $mue['numero_cuenta'];
            $extra = implode(', ', $parts);
            break;

        case 'Seguro':
            $parts = [];
            if (!empty($mue['rif_empresa'])) $parts[] = 'RIF Aseguradora: ' . $mue['rif_empresa'] . ' ' . ($mue['razon_social'] ?? '');
            if (!empty($mue['numero_prima'])) $parts[] = 'Número Prima: ' . $mue['numero_prima'];
            $extra = implode(', ', $parts);
            break;

        case 'Transporte':
            $parts = [];
            if (!empty($mue['marca'])) $parts[] = 'Marca: ' . $mue['marca'];
            if (!empty($mue['modelo'])) $parts[] = 'Modelo: ' . $mue['modelo'];
            if (!empty($mue['anio'])) $parts[] = 'Año: ' . $mue['anio'];
            if (!empty($mue['color'])) $parts[] = 'Color: ' . $mue['color'];
            if (!empty($mue['placa'])) $parts[] = 'Placa: ' . $mue['placa'];
            if (!empty($mue['serial_carroceria'])) $parts[] = 'Serial Carrocería: ' . $mue['serial_carroceria'];
            if (!empty($mue['serial_motor'])) $parts[] = 'Serial Motor: ' . $mue['serial_motor'];
            $extra = implode(', ', $parts);
            break;

        case 'Acciones':
        case 'Prestaciones Sociales':
        case 'Caja de Ahorro':
            $parts = [];
            if (!empty($mue['razon_social'])) $parts[] = 'Nombre de la Empresa: ' . $mue['razon_social'];
            if (!empty($mue['rif_empresa'])) $parts[] = 'RIF Empresa: ' . $mue['rif_empresa'];
            $extra = implode(', ', $parts);
            break;

        case 'Semovientes':
            $parts = [];
            if (!empty($mue['tipo_semoviente_nombre'])) $parts[] = 'Tipo: ' . $mue['tipo_semoviente_nombre'];
            if (!empty($mue['cantidad'])) $parts[] = 'Cantidad de Semovientes: ' . $mue['cantidad'];
            $extra = implode(', ', $parts);
            break;

        case 'Bonos':
            $parts = [];
            if (!empty($mue['tipo_bonos'])) $parts[] = 'Tipo de Bono: ' . $mue['tipo_bonos'];
            if (!empty($mue['numero_bonos'])) $parts[] = 'Número de Bonos: ' . $mue['numero_bonos'];
            if (!empty($mue['numero_serie'])) $parts[] = 'Número de Serie: ' . $mue['numero_serie'];
            $extra = implode(', ', $parts);
            break;

        case 'Cuentas/Efectos':
            $parts = [];
            if (!empty($mue['nombre_apellido'])) $parts[] = 'Nombre del Deudor: ' . $mue['nombre_apellido'];
            if (!empty($mue['rif_cedula'])) $parts[] = 'RIF Deudor: ' . $mue['rif_cedula'];
            $extra = implode(', ', $parts);
            break;

        case 'Opciones de Compra':
            if (!empty($mue['nombre_oferente'])) $extra = 'Oferente: ' . $mue['nombre_oferente'];
            break;

        // Plantaciones, Otros → solo porcentaje + descripcion
        default:
            break;
    }

    if (!empty($extra)) {
        return $base . '. ' . $extra . '.';
    }
    return $base . '.';
}

/**
 * Helper: Build formatted "Descripción" for pasivos.
 * Replica el formato usado en cada tabla de guardado por sección.
 *
 * @param array $pas  Item de pasivos (con 'categoria' inyectado por BorradorService)
 * @return string     Descripción formateada
 */
function buildDescripcionPasivo(array $pas): string
{
    $pct  = $pas['porcentaje'] ?? '';
    $desc = $pas['descripcion'] ?? '';
    $cat  = $pas['categoria'] ?? '';

    // Base: "X% de DESCRIPCION"
    $base = '';
    if (!empty($pct) && $pct !== '0,00') {
        $base = $pct . '% de ' . $desc;
    } else {
        $base = $desc;
    }

    $extra = '';
    switch ($cat) {
        case 'Tarjetas de Crédito':
            $parts = [];
            if (!empty($pas['nombre_banco'])) $parts[] = 'Banco: ' . $pas['nombre_banco'];
            if (!empty($pas['numero_tdc'])) $parts[] = 'Número de Cuenta/Tarjeta: ' . $pas['numero_tdc'];
            $extra = implode(', ', $parts);
            break;

        case 'Créditos Hipotecarios':
        case 'Préstamos/Créditos':
            if (!empty($pas['nombre_banco'])) $extra = 'Banco: ' . $pas['nombre_banco'];
            break;

        // Gastos, Otros → solo porcentaje + descripcion
        default:
            break;
    }

    if (!empty($extra)) {
        return $base . '. ' . $extra . '.';
    }
    return $base . '.';
}

/**
 * Helper: Build formatted "Descripción" for desgravámenes.
 * Vivienda Principal: porcentaje + descripción + tipo de bien + linderos + superficies
 * Seguro: porcentaje + descripción + RIF Aseguradora + Número Prima
 *
 * @param array $des  Item de desgravamen (inmueble o seguro con 'categoria')
 * @return string     Descripción formateada
 */
function buildDescripcionDesgravamen(array $des): string
{
    $cat = $des['categoria'] ?? '';

    if ($cat === 'Vivienda Principal') {
        $pct  = $des['porcentaje'] ?? '';
        $desc = $des['descripcion'] ?? '';
        $tipo = $des['tipo_bien_nombres'] ?? '';

        $base = '';
        if (!empty($pct) && $pct !== '0,00') {
            $base = $pct . '% de ' . $desc;
        } else {
            $base = $desc;
        }

        $parts = [];
        if (!empty($tipo)) {
            $parts[] = 'Tipo de Bien Inmueble: ' . $tipo;
        }
        if (!empty($des['linderos'])) {
            $parts[] = 'Linderos: ' . $des['linderos'];
        }
        $parts[] = 'Superficie Construida: ' . formatSuperficie($des['superficie_construida'] ?? '');
        $parts[] = 'Superficie Sin Construir: ' . formatSuperficie($des['superficie_no_construida'] ?? '');
        $parts[] = 'Área o Superficie: ' . formatSuperficie($des['area_superficie'] ?? '');

        if (!empty($parts)) {
            return $base . '. ' . implode(', ', $parts) . '.';
        }
        return $base . '.';
    }

    if ($cat === 'Seguro') {
        $pct  = $des['porcentaje'] ?? '';
        $desc = $des['descripcion'] ?? '';

        $base = '';
        if (!empty($pct) && $pct !== '0,00') {
            $base = $pct . '% de ' . $desc;
        } else {
            $base = $desc;
        }

        $parts = [];
        if (!empty($des['rif_empresa'])) {
            $parts[] = 'RIF Aseguradora: ' . $des['rif_empresa'] . ' ' . ($des['razon_social'] ?? '');
        }
        if (!empty($des['numero_prima'])) {
            $parts[] = 'Número Prima: ' . $des['numero_prima'];
        }

        if (!empty($parts)) {
            return $base . '. ' . implode(', ', $parts) . '.';
        }
        return $base . '.';
    }

    // Fallback
    return $des['descripcion'] ?? '';
}
?>

<!-- Estilos para textareas auto-ajustables -->
<style>
.autosize-ta {
    width: 100%;
    min-height: 28px;
    overflow: hidden;
    resize: none;
    border: 1px solid #dee2e6;
    background: #fff;
    padding: 4px 6px;
    font-size: inherit;
    font-family: inherit;
    line-height: 1.4;
    box-sizing: border-box;
}
</style>




<div class="shadow-lg p-3 mb-5 bg-body rounded lenletratablaResumen">
    <div>
        <div class="row">
            <!-- Botones de navegación -->
            <div class="col-sm-12" style="text-align:center" id="navReverso">
                <a href="<?= base_url('/simulador/sucesion/declaracion_anverso') ?>" class="btn btn-sm btn-danger">
                    <i class="bi bi-arrow-bar-left"></i> Anverso
                </a>
                &nbsp;
                <button type="button" class="btn btn-sm btn-danger" id="btnDeclararReverso"
                    onclick="window.modalManager.open('modal-aviso-seniat')">
                    <i class="bi-check-circle"></i> Declarar
                </button>
                &nbsp;&nbsp;
                <button type="button" class="btn btn-sm btn-danger" disabled>
                    Reverso <i class="bi bi-arrow-bar-right"></i>
                </button>
            </div>

            <div style="height:30px"></div>

            <div class="row">
                <div class="col-sm-12">
                    <div>
                        <!-- ═══ Tabla A–D (misma que Anverso) ═══ -->
                        <table class="table table-bordered table-sm lenletratablaResumen" id="headerReverso">
                            <!-- A - DATOS DEL CONTRIBUYENTE -->
                            <tbody>
                                <tr>
                                    <th class="table-light">A - DATOS DEL CONTRIBUYENTE</th>
                                    <th class="table-light">Nº RIF</th>
                                </tr>
                            </tbody>
                            <tbody>
                                <tr>
                                    <td class="bordeIzq bordeAbajo bordeDer">
                                        <?= htmlspecialchars($d['nombre_sucesion'] ?? '') ?></td>
                                    <td class="bordeAbajo bordeDer text-end"><?= htmlspecialchars($d['rif'] ?? '') ?>
                                    </td>
                                </tr>
                            </tbody>

                            <!-- FECHA DE DECLARACIÓN / FECHA DE VENCIMIENTO -->
                            <tbody>
                                <tr>
                                    <th class="table-light">FECHA DE DECLARACIÓN</th>
                                    <th class="table-light">FECHA DE VENCIMIENTO</th>
                                </tr>
                            </tbody>
                            <tbody>
                                <tr>
                                    <td class="bordeIzq bordeAbajo bordeDer">
                                        <?= htmlspecialchars($d['fecha_declaracion'] ?? '') ?></td>
                                    <td class="bordeAbajo bordeDer text-end">
                                        <?= htmlspecialchars($d['fecha_vencimiento'] ?? '') ?></td>
                                </tr>
                            </tbody>

                            <!-- B - DATOS DEL CAUSANTE O DONANTE -->
                            <tbody>
                                <tr>
                                    <th class="table-light">B - DATOS DEL CAUSANTE O DONANTE</th>
                                    <th class="table-light">RIF Ó CEDULA DE IDENTIDAD</th>
                                </tr>
                            </tbody>
                            <tbody>
                                <tr>
                                    <td class="bordeIzq bordeAbajo bordeDer">
                                        <?= htmlspecialchars($d['nombre_causante'] ?? '') ?></td>
                                    <td class="bordeAbajo bordeDer text-end">
                                        <?= htmlspecialchars(($d['rif_causante'] ?? '') . ' / ' . ($d['cedula_causante'] ?? '')) ?>
                                    </td>
                                </tr>
                            </tbody>

                            <!-- C - DIRECCIÓN DEL CAUSANTE O DONANTE -->
                            <tbody>
                                <tr>
                                    <th class="table-light">C - DIRECCIÓN DEL CAUSANTE O DONANTE</th>
                                    <th class="table-light">FECHA DE FALLECIMIENTO</th>
                                </tr>
                            </tbody>
                            <tbody>
                                <tr>
                                    <td class="bordeIzq bordeAbajo bordeDer">
                                        <?= htmlspecialchars($d['domicilio_fiscal'] ?? '') ?></td>
                                    <td class="bordeAbajo bordeDer text-end">
                                        <?= htmlspecialchars($d['fecha_fallecimiento'] ?? '') ?></td>
                                </tr>
                            </tbody>

                            <!-- D - DATOS DEL REPRESENTANTE LEGAL O RESPONSABLE -->
                            <tbody>
                                <tr>
                                    <th class="table-light">D - DATOS DEL REPRESENTANTE LEGAL O RESPONSABLE</th>
                                    <th class="bordeAbajo bordeDer">N°- RIF</th>
                                </tr>
                            </tbody>
                            <tbody>
                                <tr>
                                    <td><?= htmlspecialchars($d['representante_nombre'] ?? '') ?></td>
                                    <td class="text-end"><?= htmlspecialchars($d['representante_rif'] ?? '') ?></td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- ═══ J - ANEXOS ═══ -->
                        <table class="table table-bordered table-sm lenletratablaResumen">
                            <tbody>
                                <tr>
                                    <td colspan="10" class="table-light"><strong>J- ANEXOS</strong></td>
                                </tr>
                            </tbody>

                            <!-- ══════════════════════════════════════ -->
                            <!-- ── Bienes Inmuebles ───────────────── -->
                            <!-- ══════════════════════════════════════ -->
                            <tbody id="anexoInmuebles">
                                <tr>
                                    <td colspan="10" class="table-light"><strong>Bienes Inmuebles</strong></td>
                                </tr>
                            </tbody>
                            <tbody>
                                <tr>
                                    <td colspan="10">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-sm lenletratablaResumen">
                                                <thead>
                                                    <tr>
                                                        <td class="table-light">Tipo</td>
                                                        <td class="table-light">Descripción</td>
                                                        <td class="table-light">Registro</td>
                                                        <td class="table-light text-center">Vivienda Principal</td>
                                                        <td class="table-light text-center">Bien Litigioso</td>
                                                        <td class="table-light text-end">Monto Declarado</td>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($inmuebles as $inm): ?>
                                                        <tr>
                                                            <td><?= htmlspecialchars($inm['tipo_bien_nombres'] ?? '') ?>
                                                            </td>
                                                            <td>
                                                                <div>
                                                                    <textarea class="autosize-ta"
                                                                        readonly><?= htmlspecialchars(buildDescripcionInmueble($inm)) ?></textarea>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div>
                                                                    <textarea class="autosize-ta"
                                                                        readonly><?= htmlspecialchars(buildRegistroInmueble($inm)) ?></textarea>
                                                                </div>
                                                            </td>
                                                            <td class="text-center">
                                                                <?php if (($inm['vivienda_principal'] ?? 'false') === 'true'): ?>
                                                                    <span
                                                                        class="badge rounded-pill bg-success">&nbsp;SI&nbsp;</span>
                                                                <?php else: ?>
                                                                    <span
                                                                        class="badge rounded-pill bg-danger">&nbsp;NO&nbsp;</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td class="text-center">
                                                                <?php if (($inm['bien_litigioso'] ?? 'false') === 'true'): ?>
                                                                    <span
                                                                        class="badge rounded-pill bg-success">&nbsp;SI&nbsp;</span>
                                                                <?php else: ?>
                                                                    <span
                                                                        class="badge rounded-pill bg-danger">&nbsp;NO&nbsp;</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td class="text-end">
                                                                <?= htmlspecialchars($inm['valor_declarado'] ?? '0,00') ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <td colspan="5" class="text-end"><strong>Monto Total</strong>
                                                        </td>
                                                        <td class="text-end">
                                                            <strong><?= $d['total_inmuebles'] ?? '0,00' ?></strong></td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>

                            <!-- ══════════════════════════════════════ -->
                            <!-- ── Bienes Muebles ─────────────────── -->
                            <!-- ══════════════════════════════════════ -->
                            <tbody id="anexoMuebles">
                                <tr>
                                    <td colspan="10" class="table-light"><strong>Bienes Muebles</strong></td>
                                </tr>
                            </tbody>
                            <tbody>
                                <tr>
                                    <td colspan="10">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-sm lenletratablaResumen">
                                                <thead>
                                                    <tr>
                                                        <td class="table-light">Categoría</td>
                                                        <td class="table-light">Descripción</td>
                                                        <td class="table-light text-center">Bien Litigioso</td>
                                                        <td class="table-light text-end">Monto Declarado</td>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($muebles as $mue): ?>
                                                        <tr>
                                                            <td><?= htmlspecialchars($mue['categoria'] ?? '') ?></td>
                                                            <td>
                                                                <div>
                                                                    <textarea class="autosize-ta"
                                                                        readonly><?= htmlspecialchars(buildDescripcionMueble($mue)) ?></textarea>
                                                                </div>
                                                            </td>
                                                            <td class="text-center">
                                                                <?php if (($mue['bien_litigioso'] ?? 'false') === 'true'): ?>
                                                                    <span
                                                                        class="badge rounded-pill bg-success">&nbsp;SI&nbsp;</span>
                                                                <?php else: ?>
                                                                    <span
                                                                        class="badge rounded-pill bg-danger">&nbsp;NO&nbsp;</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td class="text-end">
                                                                <?= htmlspecialchars($mue['valor_declarado'] ?? '0,00') ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <td colspan="3" class="text-end"><strong>Monto Total</strong>
                                                        </td>
                                                        <td class="text-end">
                                                            <strong><?= $d['total_muebles'] ?? '0,00' ?></strong></td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>

                            <!-- ══════════════════════════════════════ -->
                            <!-- ── Pasivos ────────────────────────── -->
                            <!-- ══════════════════════════════════════ -->
                            <tbody id="anexoPasivos">
                                <tr>
                                    <td colspan="10" class="table-light"><strong>Pasivos</strong></td>
                                </tr>
                            </tbody>
                            <tbody>
                                <tr>
                                    <td colspan="10">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-sm lenletratablaResumen">
                                                <thead>
                                                    <tr>
                                                        <td class="table-light">Categoría</td>
                                                        <td class="table-light">Descripción</td>
                                                        <td class="table-light text-end">Monto Declarado</td>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($pasivos as $pas): ?>
                                                        <tr>
                                                            <td><?= htmlspecialchars($pas['categoria'] ?? '') ?></td>
                                                            <td>
                                                                <div>
                                                                    <textarea class="autosize-ta"
                                                                        readonly><?= htmlspecialchars(buildDescripcionPasivo($pas)) ?></textarea>
                                                                </div>
                                                            </td>
                                                            <td class="text-end">
                                                                <?= htmlspecialchars($pas['valor_declarado'] ?? '0,00') ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <td colspan="2" class="text-end"><strong>Monto Total</strong>
                                                        </td>
                                                        <td class="text-end">
                                                            <strong><?= $d['total_pasivos'] ?? '0,00' ?></strong></td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>

                            <!-- ══════════════════════════════════════ -->
                            <!-- ── Desgravamenes ──────────────────── -->
                            <!-- ══════════════════════════════════════ -->
                            <tbody id="anexoDesgravamenes">
                                <tr>
                                    <td colspan="10" class="table-light"><strong>Desgravamenes</strong></td>
                                </tr>
                            </tbody>
                            <tbody>
                                <tr>
                                    <td colspan="10">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-sm lenletratablaResumen">
                                                <thead>
                                                    <tr>
                                                        <td class="table-light">Tipo</td>
                                                        <td class="table-light">Descripción</td>
                                                        <td class="table-light text-end">Monto Declarado</td>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($desgravamenes as $des): ?>
                                                        <tr>
                                                            <td><?= htmlspecialchars($des['categoria'] ?? '') ?></td>
                                                            <td>
                                                                <div>
                                                                    <textarea class="autosize-ta"
                                                                        readonly><?= htmlspecialchars(buildDescripcionDesgravamen($des)) ?></textarea>
                                                                </div>
                                                            </td>
                                                            <td class="text-end">
                                                                <?= htmlspecialchars($des['valor_declarado'] ?? '0,00') ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <td colspan="2" class="text-end"><strong>Monto Total</strong>
                                                        </td>
                                                        <td class="text-end">
                                                            <strong><?= $d['total_desgravamenes'] ?? '0,00' ?></strong>
                                                        </td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>

                            <!-- ══════════════════════════════════════ -->
                            <!-- ── Exenciones ─────────────────────── -->
                            <!-- ══════════════════════════════════════ -->
                            <tbody id="anexoExenciones">
                                <tr>
                                    <td colspan="10" class="table-light"><strong>Exenciones</strong></td>
                                </tr>
                            </tbody>
                            <tbody>
                                <tr>
                                    <td colspan="10">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-sm lenletratablaResumen">
                                                <thead>
                                                    <tr>
                                                        <td class="table-light">Tipo</td>
                                                        <td class="table-light">Descripción</td>
                                                        <td class="table-light text-end">Monto Declarado</td>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($exenciones as $exc): ?>
                                                        <tr>
                                                            <td><?= htmlspecialchars($exc['tipo'] ?? '') ?>
                                                            </td>
                                                            <td>
                                                                <div>
                                                                    <textarea class="autosize-ta"
                                                                        readonly><?= htmlspecialchars($exc['descripcion'] ?? '') ?></textarea>
                                                                </div>
                                                            </td>
                                                            <td class="text-end">
                                                                <?= htmlspecialchars($exc['valor_declarado'] ?? '0,00') ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <td colspan="2" class="text-end"><strong>Monto Total</strong>
                                                        </td>
                                                        <td class="text-end">
                                                            <strong><?= $d['total_exenciones'] ?? '0,00' ?></strong>
                                                        </td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>

                            <!-- ══════════════════════════════════════ -->
                            <!-- ── Exoneraciones ──────────────────── -->
                            <!-- ══════════════════════════════════════ -->
                            <tbody id="anexoExoneraciones">
                                <tr>
                                    <td colspan="10" class="table-light"><strong>Exoneraciones</strong></td>
                                </tr>
                            </tbody>
                            <tbody>
                                <tr>
                                    <td colspan="10">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-sm lenletratablaResumen">
                                                <thead>
                                                    <tr>
                                                        <td class="table-light">Tipo</td>
                                                        <td class="table-light">Descripción</td>
                                                        <td class="table-light text-end">Monto Declarado</td>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($exoneraciones as $exo): ?>
                                                        <tr>
                                                            <td><?= htmlspecialchars($exo['tipo'] ?? '') ?>
                                                            </td>
                                                            <td>
                                                                <div>
                                                                    <textarea class="autosize-ta"
                                                                        readonly><?= htmlspecialchars($exo['descripcion'] ?? '') ?></textarea>
                                                                </div>
                                                            </td>
                                                            <td class="text-end">
                                                                <?= htmlspecialchars($exo['valor_declarado'] ?? '0,00') ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <td colspan="2" class="text-end"><strong>Monto Total</strong>
                                                        </td>
                                                        <td class="text-end">
                                                            <strong><?= $d['total_exoneraciones'] ?? '0,00' ?></strong>
                                                        </td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>

                            <!-- ══════════════════════════════════════ -->
                            <!-- ── Bienes Litigiosos ──────────────── -->
                            <!-- ══════════════════════════════════════ -->
                            <tbody id="anexoLitigiosos">
                                <tr>
                                    <td colspan="10" class="table-light"><strong>Bienes Litigiosos</strong></td>
                                </tr>
                            </tbody>
                            <tbody>
                                <tr>
                                    <td colspan="10">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-sm lenletratablaResumen">
                                                <thead>
                                                    <tr>
                                                        <td class="table-light">Tipo</td>
                                                        <td class="table-light">Descripción</td>
                                                        <td class="table-light text-end">Monto Declarado</td>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    // Filter bienes litigiosos from inmuebles + muebles
                                                    $litigiosos = [];
                                                    foreach ($inmuebles as $inm) {
                                                        if (($inm['bien_litigioso'] ?? 'false') === 'true') {
                                                            $litigiosos[] = [
                                                                'tipo' => $inm['tipo_bien_nombres'] ?? 'Inmueble',
                                                                'descripcion' => buildDescripcionInmueble($inm),
                                                                'valor' => $inm['valor_declarado'] ?? '0,00',
                                                            ];
                                                        }
                                                    }
                                                    foreach ($muebles as $mue) {
                                                        if (($mue['bien_litigioso'] ?? 'false') === 'true') {
                                                            $litigiosos[] = [
                                                                'tipo' => $mue['categoria'] ?? 'Mueble',
                                                                'descripcion' => buildDescripcionMueble($mue),
                                                                'valor' => $mue['valor_declarado'] ?? '0,00',
                                                            ];
                                                        }
                                                    }
                                                    $totalLitigiosos = 0.0;
                                                    foreach ($litigiosos as $lit):
                                                        $totalLitigiosos += $parseDec($lit['valor'] ?? '0,00');
                                                        ?>
                                                        <tr>
                                                            <td><?= htmlspecialchars($lit['tipo']) ?></td>
                                                            <td>
                                                                <div>
                                                                    <textarea class="autosize-ta"
                                                                        readonly><?= htmlspecialchars($lit['descripcion']) ?></textarea>
                                                                </div>
                                                            </td>
                                                            <td class="text-end"><?= htmlspecialchars($lit['valor']) ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <td colspan="2" class="text-end"><strong>Monto Total</strong>
                                                        </td>
                                                        <td class="text-end">
                                                            <strong><?= $fmt($totalLitigiosos) ?></strong></td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ═══ Modal Aviso SENIAT (paso 1) ═══ -->
<dialog class="modal-base" id="modal-aviso-seniat">
    <div class="modal-base__container" style="max-width: 460px;">
        <div class="modal-base__header" style="background: #f8f9fa; border-bottom: 1px solid #dee2e6; padding: 12px 16px;">
            <h2 class="modal-base__title" style="font-size: 16px; font-weight: 600; color: #333;">Aviso</h2>
            <button class="modal-base__close" onclick="window.modalManager.close('modal-aviso-seniat')" style="font-size: 18px; color: #666;">&times;</button>
        </div>
        <div class="modal-base__body" style="padding: 20px;">
            <p style="font-size: 14px; color: #333; margin: 0 0 16px;">
                Su monto a pagar es <strong><?= $d['linea_14'] ?? '0,00' ?></strong>
            </p>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 20px;">
                <label style="font-size: 13px; color: #555; white-space: nowrap;">Seleccione la cantidad de porciones</label>
                <select id="selectPorciones" style="padding: 6px 10px; border: 1px solid #ced4da; border-radius: 4px; font-size: 13px; min-width: 80px; background: #fff;">
                    <option value="1">1</option>
                </select>
            </div>
            <p style="font-size: 14px; color: #333; margin: 0;">
                Si está seguro presione Declarar?
            </p>
        </div>
        <div class="modal-base__footer" style="padding: 12px 16px; border-top: 1px solid #dee2e6; text-align: right;">
            <button class="modal-btn modal-btn-cancel" onclick="window.modalManager.close('modal-aviso-seniat')" style="margin-right: 8px;">Cancelar</button>
            <button class="modal-btn modal-btn-primary" id="btnAvisoDeclararReverso" style="background-color: #2c3e6b; border-color: #2c3e6b;">Declarar</button>
        </div>
    </div>
</dialog>

<!-- ═══ Modal Confirmación Declarar (SPDSS) ═══ -->
<dialog class="modal-base" id="modal-declarar" data-no-backdrop-close>
    <div class="modal-base__container" style="max-width: 480px;">
        <div class="modal-base__header">
            <h2 class="modal-base__title">Confirmar Declaración</h2>
            <button class="modal-base__close" onclick="window.modalManager.close('modal-declarar')">✕</button>
        </div>
        <div class="modal-base__body">
            <p style="color: var(--gray-600); font-size: var(--text-md); margin: 0 0 12px;">
                Está a punto de <strong>enviar su declaración sucesoral</strong>. Esta acción es
                <strong>definitiva</strong> y no podrá ser revertida.
            </p>
            <p style="color: var(--gray-600); font-size: var(--text-md); margin: 0 0 8px;">Al confirmar:</p>
            <ul
                style="color: var(--gray-600); font-size: var(--text-md); margin: 0 0 12px; padding-left: 20px; line-height: 1.8;">
                <li>Se finalizará la simulación del proceso</li>
                <li>No podrá modificar los datos ingresados después de declarar</li>
                <li>Recibirá un correo electrónico con el resumen y los resultados obtenidos</li>
            </ul>
            <p style="color: var(--gray-500); font-size: var(--text-xs); margin: 0; font-style: italic;">
                Asegúrese de haber revisado toda la información antes de continuar.
            </p>
        </div>
        <div class="modal-base__footer">
            <button class="modal-btn modal-btn-cancel"
                onclick="window.modalManager.close('modal-declarar')">Cancelar</button>
            <button class="modal-btn modal-btn-primary" id="btnConfirmarDeclaracion">Sí, Declarar</button>
        </div>
    </div>
</dialog>

<!-- ═══ Modal Finalización ═══ -->
<dialog class="modal-base" id="modal-finalizacion" data-no-backdrop-close>
    <div class="modal-base__container" style="max-width: 500px;">
        <div class="modal-base__header">
            <h2 class="modal-base__title">Simulación Finalizada</h2>
        </div>
        <div class="modal-base__body" style="text-align: center; padding: 24px;">
            <div style="margin-bottom: 16px;">
                <i class="bi bi-check-circle-fill" style="font-size: 48px; color: #28a745;"></i>
            </div>
            <p style="font-size: 16px; color: #333; margin: 0 0 20px; font-weight: 600;">
                Ha finalizado la simulación del proceso de declaración sucesoral.
            </p>
            <p style="font-size: 14px; color: #555; margin: 0 0 16px;">
                A continuación puede descargar los documentos generados:
            </p>
            <div style="display: flex; flex-direction: column; gap: 10px; align-items: center; margin-bottom: 8px;">
                <a href="<?= base_url('/simulador/sucesion/planilla_pdf') ?>" target="_blank"
                   style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 6px; color: #2c3e6b; text-decoration: none; font-size: 14px; font-weight: 500; width: 280px; justify-content: center;"
                   onmouseover="this.style.background='#e9ecef'" onmouseout="this.style.background='#f8f9fa'">
                    <i class="bi bi-file-earmark-pdf-fill" style="font-size: 18px; color: #dc3545;"></i>
                    Planilla FORMA DS-99032
                </a>
                <a href="<?= base_url('/simulador/sucesion/declaracion_pdf') ?>" target="_blank"
                   style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 6px; color: #2c3e6b; text-decoration: none; font-size: 14px; font-weight: 500; width: 280px; justify-content: center;"
                   onmouseover="this.style.background='#e9ecef'" onmouseout="this.style.background='#f8f9fa'">
                    <i class="bi bi-file-earmark-pdf-fill" style="font-size: 18px; color: #dc3545;"></i>
                    Resumen de la Asignación
                </a>
            </div>
        </div>
        <div class="modal-base__footer" style="justify-content: center;">
            <button class="modal-btn modal-btn-primary" onclick="window.modalManager.close('modal-finalizacion')">Continuar</button>
        </div>
    </div>
</dialog>

<?php
$content = ob_get_clean();
$content .= '
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Auto-resize all textareas to fit content
    document.querySelectorAll(".autosize-ta").forEach(function(ta) {
        ta.style.height = "auto";
        ta.style.height = ta.scrollHeight + "px";
    });

    // Aviso SENIAT → abre SPDSS modal
    var btnAviso = document.getElementById("btnAvisoDeclararReverso");
    if (btnAviso) {
        btnAviso.addEventListener("click", function() {
            window.modalManager.close("modal-aviso-seniat");
            setTimeout(function() { window.modalManager.open("modal-declarar"); }, 300);
        });
    }

    // SPDSS confirmar → AJAX declarar → abrir modal finalización
    var btn = document.getElementById("btnConfirmarDeclaracion");
    if (btn) {
        btn.addEventListener("click", function() {
            var originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = \'<svg width="18" height="18" viewBox="0 0 24 24" style="animation:button-spin .8s linear infinite"><circle cx="12" cy="12" r="10" stroke="rgba(255,255,255,0.4)" stroke-width="3" fill="none"/><path d="M12 2a10 10 0 0 1 10 10" stroke="#fff" stroke-width="3" fill="none" stroke-linecap="round"/></svg> Procesando...\';

            fetch("' . base_url('/api/intentos/declarar') . '", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: "{}"
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.ok) {
                    window.__asignacionId = data.asignacion_id;
                    window.modalManager.close("modal-declarar");
                    setTimeout(function() { window.modalManager.open("modal-finalizacion"); }, 300);
                } else {
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                    alert(data.error || "Error al procesar la declaración.");
                }
            })
            .catch(function(err) {
                btn.disabled = false;
                btn.innerHTML = originalText;
                console.error(err);
                alert("Error de conexión al procesar la declaración.");
            });
        });
    }

    // Continuar → redirigir al detalle de la asignación
    var btnContinuar = document.querySelector("#modal-finalizacion .modal-btn-primary");
    if (btnContinuar) {
        btnContinuar.addEventListener("click", function(e) {
            e.preventDefault();
            var id = window.__asignacionId || "";
            window.location.href = "' . base_url('/mis-asignaciones/') . '" + id;
        });
    }
});
</script>';
include __DIR__ . '/../../../../layouts/sim_sucesiones_layout.php';
?>