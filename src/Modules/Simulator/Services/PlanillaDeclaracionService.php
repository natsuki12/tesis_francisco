<?php

declare(strict_types=1);

namespace App\Modules\Simulator\Services;

use App\Core\DB;
use App\Core\UnidadTributariaService;
use Mpdf\Mpdf;
use PDO;

/**
 * PlanillaDeclaracionService — Generates a SENIAT FORMA DS-99032 PDF
 * from the student's borrador JSON data.
 *
 * All data comes from BorradorService (borrador_json) +
 * TributoCalculator for the autoliquidación section.
 */
class PlanillaDeclaracionService
{
    private PDO $db;

    public function __construct()
    {
        $this->db = DB::connect();
    }

    /**
     * Generate the planilla PDF and stream it to the browser.
     *
     * @param array $intento   The intento row (must include borrador_json)
     * @param int   $estudianteId
     */
    public function generar(array $intento): void
    {
        $bs = new BorradorService($intento);
        $borrador = $bs->getBorrador();

        // ── Catálogo de parentescos ──
        $parentescoCat = $this->getParentescoCatalog();
        $gruposTarifa  = $this->getGruposTarifa();

        // ── Datos generales ──
        $rep = $bs->getRepresentanteLegal();
        $datos = [
            'nombre_sucesion'       => $bs->getNombreSucesion(),
            'rif_sucesoral'         => $bs->getRif(),
            'nombre_causante'       => $bs->getNombreCausante(),
            'cedula_causante'       => $bs->getCedulaCausante(),
            'rif_causante'          => $bs->getRifCausante(),
            'direccion_causante'    => $bs->getDomicilioFiscal(),
            'fecha_fallecimiento'   => $bs->getFechaFallecimiento(),
            'fecha_declaracion'     => $bs->getFechaDeclaracion(),
            'fecha_vencimiento'     => $bs->getFechaVencimiento(),
            'representante_nombre'  => $rep['nombre'],
            'representante_rif'     => $rep['rif'],
        ];

        // ── Prórrogas ──
        $prorrogas = $borrador['prorrogas'] ?? [];

        // ── Herederos + cálculo de impuesto ──
        $herederosDetalle = $bs->getHerederosDetalle();
        $numH = count($herederosDetalle);

        // UT
        $datosBasicos = $bs->getDatosBasicos();
        $fechaFall = $datosBasicos['fecha_fallecimiento'] ?? '';
        $utData = UnidadTributariaService::obtenerPorFecha($fechaFall);
        $ut = $utData ? (float) $utData['valor'] : 0.4;

        // Autoliquidación totals
        $tIB = $bs->getTotalBienesInmuebles();
        $tMB = $bs->getTotalBienesMuebles();
        $pBB = $tIB + $tMB;
        $dB  = $bs->getTotalDesgravamenes();
        $exB = $bs->getTotalExenciones();
        $eoB = $bs->getTotalExoneraciones();
        $teB = $dB + $exB + $eoB;
        $anB = max(0, $pBB - $teB);
        $tPB = $bs->getTotalPasivos();
        $pNB = max(0, $anB - $tPB);

        // TributoCalculator
        $calculoManual = $borrador['calculo_manual'] ?? null;
        if ($calculoManual && !empty($calculoManual['herederos'])) {
            $tributo = TributoCalculator::calcularConOverrides($ut, $herederosDetalle, $calculoManual['herederos'], $this->db);
        } else {
            $tributo = TributoCalculator::calcular($pNB, $numH, $ut, $herederosDetalle, $this->db);
        }

        // Build herederos array for template (with impuesto data)
        $herederos = [];
        foreach ($herederosDetalle as $i => $h) {
            $calc = $tributo['herederos'][$i] ?? [];
            $pid = (int) ($h['parentesco_id'] ?? 0);
            $grupoId = $gruposTarifa[$pid] ?? 4;

            // Split nombre into apellidos + nombres
            $parts = explode(' ', $h['nombre'] ?? '', 2);
            $herederos[] = [
                'apellidos'   => $this->getApellidosFromBorrador($borrador, $i),
                'nombres'     => $this->getNombresFromBorrador($borrador, $i),
                'cedula'      => $h['cedula'] ?? '',
                'parentesco'  => $parentescoCat[$pid] ?? '',
                'grado'       => $grupoId,
                'premuerto'   => $h['premuerto'] ?? 'NO',
                'cuota_parte' => $calc['cuota_parte_ut'] ?? 0,
                'reduccion'   => $calc['reduccion'] ?? 0,
                'impuesto'    => $calc['impuesto_a_pagar'] ?? 0,
            ];
        }

        // Herederos del premuerto
        $premuertos = [];
        foreach (($borrador['herederos_premuertos'] ?? []) as $hp) {
            $pid = (int) ($hp['parentesco_id'] ?? 0);
            $premuertos[] = [
                'apellidos'        => $hp['apellido'] ?? $hp['apellidos'] ?? '',
                'nombres'          => $hp['nombre'] ?? $hp['nombres'] ?? '',
                'cedula'           => $hp['idDocumento'] ?? $hp['cedula'] ?? '',
                'parentesco'       => $parentescoCat[$pid] ?? '',
                'representa_a'     => $hp['premuerto_padre_nombre'] ?? '',
                'fecha_nacimiento' => $this->isoToDisplay($hp['fecha_nacimiento'] ?? ''),
            ];
        }

        // ── Autoliquidación rows (matching SENIAT DS-99032 exactly) ──
        $autoItems = [
            ['num' => '1',  'concepto' => 'Total de Bienes Inmuebles',              'valor' => $tIB],
            ['num' => '2',  'concepto' => 'Total de Bienes Muebles',                'valor' => $tMB],
            ['num' => '3',  'concepto' => 'Patrimonio Hereditario Bruto (1+2)',     'valor' => $pBB],
            ['num' => '4',  'concepto' => 'Activo Hereditario Bruto (Patrimonio)',  'valor' => $pBB],
            ['num' => '5',  'concepto' => 'Desgravámenes',                          'valor' => $dB],
            ['num' => '6',  'concepto' => 'Exenciones',                             'valor' => $exB],
            ['num' => '7',  'concepto' => 'Exoneraciones',                          'valor' => $eoB],
            ['num' => '8',  'concepto' => 'Total de Exclusiones (Desgravámenes-Exenciones-Exoneraciones)', 'valor' => $teB],
            ['tipo' => 'separador', 'concepto' => 'Patrimonio Neto Hereditario', 'valor' => 0],
            ['num' => '9',  'concepto' => 'Activo Hereditario Neto: (Activo Hereditario Bruto-Total de Exclusiones)', 'valor' => $anB],
            ['num' => '10', 'concepto' => 'Total Pasivos',                          'valor' => $tPB],
            ['num' => '11', 'concepto' => 'Patrimonio Neto Hereditario o Líquido Hereditario Gravable (Activo Hereditario Neto-Total Pasivos)', 'valor' => $pNB],
            ['tipo' => 'separador', 'concepto' => 'Determinación del Tributo', 'valor' => 0],
            ['num' => '12', 'concepto' => 'Impuesto Determinado según Tarifa',      'valor' => $tributo['linea_12']],
            ['num' => '13', 'concepto' => 'Reducciones',                            'valor' => $tributo['linea_13']],
            ['num' => '14', 'concepto' => 'Total Impuesto Pagado según Declaracion Sustitutiva', 'valor' => $tributo['linea_14']],
            ['num' => '15', 'concepto' => 'Total Impuesto a Pagar',                'valor' => $tributo['total_impuesto_a_pagar']],
        ];

        // ── Inventario items ──
        $inmuebles = [];
        foreach ($bs->getBienesInmueblesItems() as $bi) {
            // Build registro string from borrador fields
            $regParts = array_filter([
                !empty($bi['oficina_registro']) ? 'Oficina: ' . $bi['oficina_registro'] : '',
                !empty($bi['protocolo']) ? 'Protocolo: ' . $bi['protocolo'] : '',
                !empty($bi['tomo']) ? 'Tomo: ' . $bi['tomo'] : '',
                !empty($bi['folio']) ? 'Folio: ' . $bi['folio'] : '',
                !empty($bi['trimestre']) ? 'Trimestre: ' . $bi['trimestre'] : '',
                !empty($bi['fecha_registro']) ? 'Fecha: ' . $bi['fecha_registro'] : '',
            ]);
            $inmuebles[] = [
                'tipo'             => $bi['tipo_bien_nombres'] ?? '',
                'descripcion'      => $bi['descripcion'] ?? '',
                'registro'         => implode(', ', $regParts),
                'porcentaje'       => $bs->parseDecimal($bi['porcentaje'] ?? '0'),
                'valor_declarado'  => $bs->parseDecimal($bi['valor_declarado'] ?? '0'),
                'valor_original'   => $bs->parseDecimal($bi['valor_original'] ?? '0'),
                'bien_litigioso'   => ($bi['bien_litigioso'] ?? 'false') === 'true',
                'num_expediente'   => $bi['num_expediente'] ?? '',
                'tribunal_causa'   => $bi['tribunal_causa'] ?? '',
            ];
        }

        $muebles = [];
        foreach ($bs->getBienesMueblesItems() as $bm) {
            $muebles[] = [
                'categoria'       => $bm['categoria'] ?? '',
                'tipo'            => $bm['tipo_bien_nombre'] ?? '',
                'descripcion'     => $bm['descripcion'] ?? '',
                'porcentaje'      => $bs->parseDecimal($bm['porcentaje'] ?? '0'),
                'valor_declarado' => $bs->parseDecimal($bm['valor_declarado'] ?? '0'),
                'litigioso'       => ($bm['bien_litigioso'] ?? 'false') === 'true' ? 'SI' : 'NO',
                'num_expediente'  => $bm['num_expediente'] ?? '',
                'tribunal_causa'  => $bm['tribunal_causa'] ?? '',
            ];
        }

        $pasivos = [];
        foreach ($bs->getPasivosItems() as $p) {
            $pasivos[] = [
                'tipo'            => $p['categoria'] ?? '',
                'descripcion'     => $p['descripcion'] ?? '',
                'porcentaje'      => $bs->parseDecimal($p['porcentaje'] ?? '0'),
                'valor_declarado' => $bs->parseDecimal($p['valor_declarado'] ?? '0'),
            ];
        }

        $desgravamenes = [];
        foreach ($bs->getDesgravamenesItems() as $d) {
            $desgravamenes[] = [
                'descripcion' => $d['descripcion'] ?? '',
                'tipo'        => $d['categoria'] ?? '',
                'valor'       => $bs->parseDecimal($d['valor_declarado'] ?? '0'),
            ];
        }

        $exenciones = $bs->getExencionesItems();
        $exoneraciones = $bs->getExoneracionesItems();

        // ── Bienes Litigiosos (filtrados de inmuebles + muebles) ──
        $litigiosos = [];
        foreach ($inmuebles as $bi) {
            if ($bi['bien_litigioso'] === true) {
                $litigiosos[] = [
                    'tipo'        => $bi['tipo'],
                    'descripcion' => $bi['descripcion'],
                    'valor'       => $bi['valor_declarado'],
                ];
            }
        }
        foreach ($muebles as $bm) {
            if (($bm['litigioso'] ?? 'NO') === 'SI') {
                $litigiosos[] = [
                    'tipo'        => $bm['categoria'],
                    'descripcion' => $bm['descripcion'],
                    'valor'       => $bm['valor_declarado'],
                ];
            }
        }

        // ── Nro Planilla (estático para simulador) ──
        $nroPlanilla = '0000000000';

        // ── Variables del membrete ──
        $pdfTipoDocumento = 'Planilla DS-99032';
        $pdfReferencia = '#INT-' . ($intento['id'] ?? $intento['intento_id'] ?? '0');
        $pdfEstado = $datos['rif_sucesoral'] ?? '';
        $pdfEstadoLabel = 'RIF';

        // ── Render HTML ──
        ob_start();
        include __DIR__ . '/../../../../resources/views/simulator/pdf/pdf_planilla_declaracion.php';
        $html = ob_get_clean();

        // ── mPDF ──
        $mpdf = new Mpdf([
            'mode'          => 'utf-8',
            'format'        => 'Letter',
            'margin_left'   => 12,
            'margin_right'  => 12,
            'margin_top'    => 10,
            'margin_bottom' => 12,
            'default_font'  => 'dejavusans',
        ]);

        $mpdf->SetTitle('Declaración Sucesoral — FORMA DS-99032');
        $mpdf->SetAuthor('SUCELAB');
        $mpdf->WriteHTML($html);
        $mpdf->Output('planilla_declaracion.pdf', 'I');
    }

    // ─── Helpers ───

    private function getApellidosFromBorrador(array $borrador, int $index): string
    {
        // New format
        $items = $borrador['herederos']['items'] ?? [];
        if (isset($items[$index])) {
            return strtoupper(trim($items[$index]['apellidos'] ?? ''));
        }
        // Legacy
        $rels = $borrador['relaciones'] ?? [];
        $herederoIdx = 0;
        foreach ($rels as $rel) {
            if (isset($rel['parentescoText']) && strtoupper($rel['parentescoText']) !== 'REPRESENTANTE DE LA SUCESION') {
                if ($herederoIdx === $index) {
                    return strtoupper(trim($rel['apellido'] ?? $rel['apellidos'] ?? ''));
                }
                $herederoIdx++;
            }
        }
        return '';
    }

    private function getNombresFromBorrador(array $borrador, int $index): string
    {
        $items = $borrador['herederos']['items'] ?? [];
        if (isset($items[$index])) {
            return strtoupper(trim($items[$index]['nombres'] ?? ''));
        }
        $rels = $borrador['relaciones'] ?? [];
        $herederoIdx = 0;
        foreach ($rels as $rel) {
            if (isset($rel['parentescoText']) && strtoupper($rel['parentescoText']) !== 'REPRESENTANTE DE LA SUCESION') {
                if ($herederoIdx === $index) {
                    return strtoupper(trim($rel['nombre'] ?? $rel['nombres'] ?? ''));
                }
                $herederoIdx++;
            }
        }
        return '';
    }

    private function getParentescoCatalog(): array
    {
        $stmt = $this->db->query("SELECT id, etiqueta FROM sim_cat_parentescos ORDER BY id ASC");
        $map = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $map[(int) $r['id']] = $r['etiqueta'];
        }
        return $map;
    }

    private function getGruposTarifa(): array
    {
        $stmt = $this->db->query('SELECT id, grupo_tarifa_id FROM sim_cat_parentescos WHERE activo = 1');
        $map = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $map[(int) $row['id']] = $row['grupo_tarifa_id'] !== null
                ? (int) $row['grupo_tarifa_id']
                : 4;
        }
        return $map;
    }

    private function isoToDisplay(string $isoDate): string
    {
        if (empty($isoDate)) return '';
        $parts = explode('-', $isoDate);
        if (count($parts) !== 3) return $isoDate;
        return $parts[2] . '/' . $parts[1] . '/' . $parts[0];
    }
}
