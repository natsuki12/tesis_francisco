<?php
$activeMenu = 'litigiosos';
$activeItem = 'Bienes Litigiosos';
$breadcrumbs = [
    ['label' => 'Inicio', 'url' => '/simulador/servicios_declaracion/dashboard'],
    ['label' => 'Bienes Litigiosos'],
];

/* ── Data from controller ── */
$intento = $intento ?? null;
$borrador = [];
if ($intento && !empty($intento['borrador_json'])) {
    $borrador = json_decode($intento['borrador_json'], true) ?: [];
}

/*
 * Scan ALL borrador sections for items with bien_litigioso === 'true'.
 * Each section stores items in a different key. We map
 *   borrador key  →  human-readable "Tipo de Bien" label.
 */
$secciones = [
    'bienes_muebles_banco'                => 'Banco',
    'bienes_muebles_seguro'               => 'Seguro',
    'bienes_muebles_transporte'           => 'Transporte',
    'bienes_muebles_acciones'             => 'Acciones',
    'bienes_muebles_bonos'                => 'Bonos',
    'bienes_muebles_caja_ahorro'          => 'Caja de Ahorro',
    'bienes_muebles_cuentas_efectos'      => 'Cuentas y Efectos por Cobrar',
    'bienes_muebles_opciones_compra'      => 'Opciones de Compra',
    'bienes_muebles_plantaciones'         => 'Plantaciones',
    'bienes_muebles_otros'                => 'Otros',
    'bienes_muebles_semovientes'          => 'Semovientes',
    'bienes_muebles_prestaciones_sociales'=> 'Prestaciones Sociales',
    'bienes_inmuebles'                    => 'Bienes Inmuebles',
];

$litigiosos = [];

foreach ($secciones as $key => $label) {
    $items = $borrador[$key] ?? [];
    foreach ($items as $item) {
        if (($item['bien_litigioso'] ?? 'false') === 'true') {
            // Build description the same way each module does
            $desc = '';
            $pct = $item['porcentaje'] ?? '0,01';

            if ($key === 'bienes_inmuebles') {
                $desc = $pct . '% de ' . ($item['descripcion'] ?? '');
            } elseif ($key === 'bienes_muebles_transporte') {
                $desc = $pct . '% de ' . ($item['descripcion'] ?? '')
                    . '. Año: '  . ($item['anio'] ?? '')
                    . ', Marca: ' . ($item['marca'] ?? '')
                    . ', Modelo: ' . ($item['modelo'] ?? '')
                    . ', Serial/Número Identificador/Placas: ' . ($item['serial'] ?? '');
            } elseif ($key === 'bienes_muebles_semovientes') {
                $desc = $pct . '% de ' . ($item['descripcion'] ?? '')
                    . '. Tipo: ' . ($item['tipo_semoviente_nombre'] ?? '')
                    . ', Cantidad de Semovientes: ' . ($item['cantidad'] ?? '');
            } elseif ($key === 'bienes_muebles_prestaciones_sociales') {
                $desc = $pct . '% de ' . ($item['descripcion'] ?? '')
                    . '. Nombre de la Empresa: ' . ($item['razon_social'] ?? '')
                    . ', RIF Empresa: ' . ($item['rif_empresa'] ?? '');
            } else {
                $desc = $pct . '% de ' . ($item['descripcion'] ?? '');
            }

            // Tribunal data
            $tribunal = [
                'num_expediente'  => $item['num_expediente'] ?? '',
                'tribunal_causa'  => $item['tribunal_causa'] ?? '',
                'partes_juicio'   => $item['partes_juicio'] ?? '',
                'estado_juicio'   => $item['estado_juicio'] ?? '',
            ];

            // Append tribunal info to description
            $tribunalDesc = '';
            if (!empty($tribunal['num_expediente'])) {
                $tribunalDesc .= '. Numero de Expediente: ' . $tribunal['num_expediente']
                    . ', Tribunal de la Causa: ' . $tribunal['tribunal_causa']
                    . ', Partes en el Juicio: ' . $tribunal['partes_juicio']
                    . ', Estado del Juicio: ' . $tribunal['estado_juicio'];
            }

            $tipoBien = $item['nombre_tipo_bien'] ?? $item['tipo_bien_nombres'] ?? $label;

            $litigiosos[] = [
                'tipo_bien'       => $tipoBien,
                'descripcion'     => $desc . $tribunalDesc,
                'valor_declarado' => $item['valor_declarado'] ?? '0,00',
                'tribunal'        => $tribunal,
            ];
        }
    }
}

ob_start();
?>

<div _ngcontent-sdd-c75 class="shadow-lg p-3 mb-5 bg-body rounded lenletra">
    <div _ngcontent-sdd-c75 class=card>
        <div _ngcontent-sdd-c75 class=card-header>Bienes Litigiosos</div>
        <div _ngcontent-sdd-c75 class=card-body>
            <div _ngcontent-sdd-c75 role=alert class="alert alert-info"> Los datos del tribunal se puede observar en la acción <i _ngcontent-sdd-c75 placement=top ngbtooltip="Registrar Tribunal" class="bi bi-clipboard2-fill"></i></div>

            <?php if (count($litigiosos) > 0): ?>
            <table _ngcontent-sdd-c75 class="table table-bordered table-striped table-sm">
                <thead _ngcontent-sdd-c75>
                    <tr _ngcontent-sdd-c75>
                        <th _ngcontent-sdd-c75 scope=col>Tipo de Bien</th>
                        <th _ngcontent-sdd-c75 scope=col>Descripción</th>
                        <th _ngcontent-sdd-c75 scope=col>Valor Declarado (Bs.)</th>
                        <th _ngcontent-sdd-c75 scope=col>Acción</th>
                    </tr>
                </thead>
                <tbody _ngcontent-sdd-c75 id="tbodyLitigiosos">
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div><br _ngcontent-sdd-c75>
</div>

<script>
    const litigiososData = <?= json_encode($litigiosos, JSON_UNESCAPED_UNICODE) ?>;

    document.addEventListener('DOMContentLoaded', function () {
        const tbody = document.getElementById('tbodyLitigiosos');
        if (!tbody || litigiososData.length === 0) return;

        let totalDeclarado = 0;

        litigiososData.forEach(function (item, idx) {
            const vd = parseFloat((item.valor_declarado || '0').replace(/\./g, '').replace(',', '.'));
            totalDeclarado += vd;

            const tr = document.createElement('tr');
            tr.innerHTML =
                '<td>' + (item.tipo_bien || '') + '</td>' +
                '<td>' + (item.descripcion || '') + '</td>' +
                '<td align=right>' + (item.valor_declarado || '0,00') + '</td>' +
                '<td>' +
                    '<div class=accionesicono>' +
                        '<i class="bi bi-clipboard2-fill" style="cursor:pointer" title="Datos Tribunal" onclick="verTribunal(' + idx + ')"></i>' +
                    '</div>' +
                '</td>';
            tbody.appendChild(tr);
        });

        // Total row
        var trTotal = document.createElement('tr');
        trTotal.innerHTML =
            '<td></td>' +
            '<td class=text-end>Total:</td>' +
            '<td align=right> ' + totalDeclarado.toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '</td>' +
            '<td></td>';
        tbody.appendChild(trTotal);
    });

    // Show tribunal details in a Bootstrap modal
    window.verTribunal = function (idx) {
        var item = litigiososData[idx];
        if (!item || !item.tribunal) return;

        var t = item.tribunal;
        document.getElementById('modalExpediente').textContent = t.num_expediente || 'N/A';
        document.getElementById('modalTribunal').textContent = t.tribunal_causa || 'N/A';
        document.getElementById('modalPartes').textContent = t.partes_juicio || 'N/A';
        document.getElementById('modalEstado').textContent = t.estado_juicio || 'N/A';

        var modal = new bootstrap.Modal(document.getElementById('modalTribunalDatos'));
        modal.show();
    };
</script>

<!-- ═══ Modal Datos del Tribunal ═══ -->
<div class="modal fade" id="modalTribunalDatos" tabindex="-1" aria-modal="true" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div _ngcontent-sdd-c75 class="modal-header">
                <h5 _ngcontent-sdd-c75 id="modal-basic-title" class="modal-title">Datos del Tribunal</h5>
                <button _ngcontent-sdd-c75 type="button" aria-label="Close" class="btn btn-light btn-sm close" data-bs-dismiss="modal"><span _ngcontent-sdd-c75 aria-hidden="true">×</span></button>
            </div>
            <div _ngcontent-sdd-c75 class="modal-body">
                <table _ngcontent-sdd-c75 class="table table-bordered table-striped table-sm">
                    <thead _ngcontent-sdd-c75>
                        <tr _ngcontent-sdd-c75>
                            <th _ngcontent-sdd-c75 scope=col>Número de Expediente</th>
                            <th _ngcontent-sdd-c75 scope=col>Tribunal de la Causa</th>
                            <th _ngcontent-sdd-c75 scope=col>Partes en el Juicio</th>
                            <th _ngcontent-sdd-c75 scope=col>Estado del Juicio</th>
                        </tr>
                    </thead>
                    <tbody _ngcontent-sdd-c75>
                        <tr _ngcontent-sdd-c75>
                            <td _ngcontent-sdd-c75 id="modalExpediente"></td>
                            <td _ngcontent-sdd-c75 id="modalTribunal"></td>
                            <td _ngcontent-sdd-c75 id="modalPartes"></td>
                            <td _ngcontent-sdd-c75 id="modalEstado"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div _ngcontent-sdd-c75 class="modal-footer">
                <button _ngcontent-sdd-c75 type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Ok</button>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../../../layouts/sim_sucesiones_layout.php';
?>
