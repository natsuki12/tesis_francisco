<?php
declare(strict_types=1);

namespace App\Modules\Simulator\Controllers;

use App\Core\App;
use App\Modules\Student\Models\StudentAssignmentModel;
use App\Modules\Student\Models\StudentAttemptModel;
use App\Modules\Simulator\Services\BorradorService;

/**
 * SucesionController — Handles sucesion simulator page rendering.
 *
 * Each method loads the active intento, builds view data via BorradorService,
 * and passes clean variables to the view (no logic in views).
 */
class SucesionController
{
    private App $app;

    public function __construct()
    {
        global $app;
        $this->app = $app;
    }

    /**
     * GET /simulador/sucesion/principal
     * Shows succession overview: fiscal data, causante, representante, herederos.
     */
    public function principal()
    {
        $intento = $this->getIntentoActivo();

        if (!$intento) {
            return $this->app->view('simulator/seniat_actual/sucesion/sucesion_principal', [
                'datos' => null,
            ]);
        }

        $borrador = new BorradorService($intento);
        $rep = $borrador->getRepresentanteLegal();

        $datos = [
            // Sucesión
            'rif'                  => $borrador->getRif(),
            'nombre_sucesion'      => $borrador->getNombreSucesion(),
            'email'                => $borrador->getEmail(),
            'fecha_fallecimiento'  => $borrador->getFechaFallecimiento(),
            'fecha_vencimiento'    => $borrador->getFechaVencimiento(),
            'ut_aplicable'         => number_format(\App\Core\UnidadTributariaService::obtenerValor($borrador->getDatosBasicos()['fecha_fallecimiento'] ?? ''), 2, ',', '.'),
            // Causante
            'rif_causante'         => $borrador->getRif(),
            'cedula_causante'      => $borrador->getCedulaCausante(),
            'nombre_causante'      => $borrador->getNombreCausante(),
            // Representante
            'rif_representante'    => $rep['rif'],
            'nombre_representante' => $rep['nombre'],
            // Domicilio
            'domicilio'            => $borrador->getDomicilioFiscal(),
            // Herederos
            'herederos'            => $borrador->getHerederos(),
        ];

        return $this->app->view('simulator/seniat_actual/sucesion/sucesion_principal', [
            'datos' => $datos,
        ]);
    }

    /**
     * GET /simulador/sucesion/herederos_premuerto
     * Shows premuerto heirs management.
     */
    public function herederos_premuerto()
    {
        $intento = $this->getIntentoActivo();

        return $this->app->view('simulator/seniat_actual/sucesion/identificacion_herederos/herederos_premuerto', [
            'intento' => $intento,
        ]);
    }

    /**
     * GET /simulador/sucesion/resumen_declaracion
     * Shows resumen with computed totals from borrador_json.
     */
    public function resumen()
    {
        $intento = $this->getIntentoActivo();

        if (!$intento) {
            return $this->app->view('simulator/seniat_actual/sucesion/resumen_declaracion/resumen_declaracion', [
                'datos' => null,
            ]);
        }

        if ($this->redirectIfHerederosIncompletos($intento)) return;

        $borrador = new BorradorService($intento);

        // ── Filas 1-2: Bienes ──
        $totalInmuebles = $borrador->getTotalBienesInmuebles();
        $totalMuebles   = $borrador->getTotalBienesMuebles();

        // ── Fila 3-4: Patrimonio Hereditario Bruto ──
        $patrimonioBruto = $totalInmuebles + $totalMuebles;

        // ── Filas 5-7: Exclusiones ──
        $desgravamenes = $borrador->getTotalDesgravamenes();
        $exenciones    = $borrador->getTotalExenciones();
        $exoneraciones = $borrador->getTotalExoneraciones();

        // ── Fila 8: Total Exclusiones ──
        $totalExclusiones = $desgravamenes + $exenciones + $exoneraciones;

        // ── Fila 9: Activo Hereditario Neto ──
        $activoNeto = $patrimonioBruto - $totalExclusiones;
        if ($activoNeto < 0) $activoNeto = 0;

        // ── Fila 10: Total Pasivo ──
        $totalPasivos = $borrador->getTotalPasivos();

        // ── Fila 11: Patrimonio Neto Hereditario ──
        $patrimonioNeto = $activoNeto - $totalPasivos;
        if ($patrimonioNeto < 0) $patrimonioNeto = 0;

        // ── Herederos ──
        $herederos = $borrador->getHerederosDetalle();
        $totalHerederos = count($herederos);

        // ── UT (from DB via fecha_fallecimiento) ──
        $datosBasicos = $borrador->getDatosBasicos();
        $fechaFallecimiento = $datosBasicos['fecha_fallecimiento'] ?? '';
        $utData = \App\Core\UnidadTributariaService::obtenerPorFecha($fechaFallecimiento);
        $ut     = $utData ? $utData['valor'] : '0,00';
        $utFloat = $utData ? (float) $utData['valor'] : 0.0;

        // ── Determinación de Tributo (filas 12-15) ──
            $calculoManual = $borrador->getBorrador()['calculo_manual'] ?? null;

            if ($calculoManual && !empty($calculoManual['herederos'])) {
                $tributo = \App\Modules\Simulator\Services\TributoCalculator::calcularConOverrides(
                    $utFloat,
                    $herederos,
                    $calculoManual['herederos'],
                    \App\Core\DB::connect()
                );
            } else {
                $tributo = \App\Modules\Simulator\Services\TributoCalculator::calcular(
                    $patrimonioNeto,
                    $totalHerederos,
                    $utFloat,
                    $herederos,
                    \App\Core\DB::connect()
                );
            }

        // Cargar catálogo de parentescos: id → [etiqueta, grupo_tarifa_id]
        $stmtPar = \App\Core\DB::connect()->query(
            'SELECT id, etiqueta, grupo_tarifa_id FROM sim_cat_parentescos WHERE activo = 1'
        );
        $parentescoCat = [];
        foreach ($stmtPar->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $parentescoCat[(int)$row['id']] = $row;
        }

        // Mezclar datos calculados por heredero con datos de display
        foreach ($herederos as $i => &$h) {
            $calc = $tributo['herederos'][$i] ?? [];
            $h['cuota_parte_ut']       = $calc['cuota_parte_ut']       ?? 0.0;
            $h['porcentaje']           = $calc['porcentaje']           ?? 0.0;
            $h['sustraendo_ut']        = $calc['sustraendo_ut']        ?? 0.0;
            $h['impuesto_determinado'] = $calc['impuesto_determinado'] ?? 0.0;
            $h['reduccion']            = $calc['reduccion']            ?? 0.0;
            $h['impuesto_a_pagar']     = $calc['impuesto_a_pagar']     ?? 0.0;

            // Resolver parentesco real y grado desde catálogo
            $pid = (int) ($h['parentesco_id'] ?? 0);
            $cat = $parentescoCat[$pid] ?? null;
            if ($cat) {
                $h['parentesco'] = strtoupper($cat['etiqueta']);
                $h['grado']      = (string) ($cat['grupo_tarifa_id'] ?? '1');
            }
        }
        unset($h);

        $fmt = function (float $v) use ($borrador) {
            return $borrador->formatDecimal($v);
        };

        $datos = [
            'ut'                  => $ut,
            'total_herederos'     => $totalHerederos,
            'total_inmuebles'     => $fmt($totalInmuebles),
            'total_muebles'       => $fmt($totalMuebles),
            'patrimonio_bruto'    => $fmt($patrimonioBruto),
            'activo_bruto'        => $fmt($patrimonioBruto), // fila 4 = fila 3
            'desgravamenes'       => $fmt($desgravamenes),
            'exenciones'          => $fmt($exenciones),
            'exoneraciones'       => $fmt($exoneraciones),
            'total_exclusiones'   => $fmt($totalExclusiones),
            'activo_neto'         => $fmt($activoNeto),
            'total_pasivos'       => $fmt($totalPasivos),
            'patrimonio_neto'     => $fmt($patrimonioNeto),
            'impuesto_tarifa'     => $fmt($tributo['linea_12']),
            'reducciones'         => $fmt($tributo['linea_13']),
            'impuesto_sustituida' => $fmt($tributo['linea_14']),
            'total_impuesto'      => $fmt($tributo['total_impuesto_a_pagar']),
            'herederos'           => $herederos,
            'intento_id'          => $intento['id'] ?? null,
            'borrador_raw'        => $borrador->getBorrador(),
        ];

        return $this->app->view('simulator/seniat_actual/sucesion/resumen_declaracion/resumen_declaracion', [
            'datos' => $datos,
        ]);
    }

    /**
     * GET /simulador/sucesion/resumen_calculo_manual
     * Shows Cálculo Manual Cuota Parte Hereditaria:
     *  - Table 1: editable Cuota Parte + Reducción per heir
     *  - Table 2: readonly results with Porcentaje, Sustraendo, Impuesto (after Calcular)
     */
    public function calculoManual()
    {
        try {
            $intento = $this->getIntentoActivo();

            if (!$intento) {
                return $this->app->view('simulator/seniat_actual/sucesion/resumen_declaracion/resumen_calculo_manual', [
                    'datos' => null,
                ]);
            }

            if ($this->redirectIfHerederosIncompletos($intento)) return;

            $borrador = new BorradorService($intento);

            // ── Mismos cálculos que resumen() ──
            $totalInmuebles   = $borrador->getTotalBienesInmuebles();
            $totalMuebles     = $borrador->getTotalBienesMuebles();
            $patrimonioBruto  = $totalInmuebles + $totalMuebles;

            $desgravamenes    = $borrador->getTotalDesgravamenes();
            $exenciones       = $borrador->getTotalExenciones();
            $exoneraciones    = $borrador->getTotalExoneraciones();
            $totalExclusiones = $desgravamenes + $exenciones + $exoneraciones;

            $activoNeto = $patrimonioBruto - $totalExclusiones;
            if ($activoNeto < 0) $activoNeto = 0;

            $totalPasivos   = $borrador->getTotalPasivos();
            $patrimonioNeto = $activoNeto - $totalPasivos;
            if ($patrimonioNeto < 0) $patrimonioNeto = 0;

            $herederos      = $borrador->getHerederosDetalle();
            $totalHerederos = count($herederos);

            // ── UT ──
            $datosBasicos       = $borrador->getDatosBasicos();
            $fechaFallecimiento = $datosBasicos['fecha_fallecimiento'] ?? '';
            $utData  = \App\Core\UnidadTributariaService::obtenerPorFecha($fechaFallecimiento);
            $ut      = $utData ? $utData['valor'] : '0,00';
            $utFloat = $utData ? (float) $utData['valor'] : 0.0;

            // ── Determinación de Tributo ──
            $calculoManual = $borrador->getBorrador()['calculo_manual'] ?? null;

            if ($calculoManual && !empty($calculoManual['herederos'])) {
                $tributo = \App\Modules\Simulator\Services\TributoCalculator::calcularConOverrides(
                    $utFloat,
                    $herederos,
                    $calculoManual['herederos'],
                    \App\Core\DB::connect()
                );
            } else {
                $tributo = \App\Modules\Simulator\Services\TributoCalculator::calcular(
                    $patrimonioNeto,
                    $totalHerederos,
                    $utFloat,
                    $herederos,
                    \App\Core\DB::connect()
                );
            }

            // ── Catálogo parentescos ──
            $stmtPar = \App\Core\DB::connect()->query(
                'SELECT id, etiqueta, grupo_tarifa_id FROM sim_cat_parentescos WHERE activo = 1'
            );
            $parentescoCat = [];
            foreach ($stmtPar->fetchAll(\PDO::FETCH_ASSOC) as $row) {
                $parentescoCat[(int)$row['id']] = $row;
            }

            // ── Mezclar datos calculados con herederos ──
            foreach ($herederos as $i => &$h) {
                $calc = $tributo['herederos'][$i] ?? [];
                $h['cuota_parte_ut']       = $calc['cuota_parte_ut']       ?? 0.0;
                $h['porcentaje']           = $calc['porcentaje']           ?? 0.0;
                $h['sustraendo_ut']        = $calc['sustraendo_ut']        ?? 0.0;
                $h['impuesto_determinado'] = $calc['impuesto_determinado'] ?? 0.0;
                $h['reduccion']            = $calc['reduccion']            ?? 0.0;
                $h['impuesto_a_pagar']     = $calc['impuesto_a_pagar']     ?? 0.0;

                // Parentesco real desde catálogo
                $pid = (int) ($h['parentesco_id'] ?? 0);
                $cat = $parentescoCat[$pid] ?? null;
                if ($cat) {
                    $h['parentesco'] = strtoupper($cat['etiqueta']);
                    $h['grado']      = (string) ($cat['grupo_tarifa_id'] ?? '1');
                }
            }
            unset($h);

            $fmt = function (float $v) use ($borrador) {
                return $borrador->formatDecimal($v);
            };

            // Cargar tarifas para JS client-side recalculation
            $stmtTarifas = \App\Core\DB::connect()->query(
                'SELECT grupo_tarifa_id, rango_desde_ut, rango_hasta_ut, porcentaje, sustraendo_ut
                   FROM sim_cat_tarifas_sucesion WHERE activo = 1
                   ORDER BY grupo_tarifa_id, rango_desde_ut'
            );
            $tarifas = $stmtTarifas->fetchAll(\PDO::FETCH_ASSOC);

            $datos = [
                'ut'              => $ut,
                'ut_float'        => $utFloat,
                'total_impuesto'  => $fmt($tributo['linea_12']),
                'herederos'       => $herederos,
                'fmt'             => $fmt,
                'intento_id'      => $intento['id'] ?? null,
                'tarifas'         => $tarifas,
                'parentesco_cat'  => $parentescoCat,
                'borrador_raw'    => $borrador->getBorrador(),
            ];

            return $this->app->view('simulator/seniat_actual/sucesion/resumen_declaracion/resumen_calculo_manual', [
                'datos' => $datos,
            ]);

        } catch (\Throwable $e) {
            error_log('[SucesionController::calculoManual] Error: ' . $e->getMessage());
            return $this->app->view('simulator/seniat_actual/sucesion/resumen_declaracion/resumen_calculo_manual', [
                'datos' => null,
            ]);
        }
    }

    /**
     * GET /simulador/sucesion/declaracion_anverso
     * Displays the Declaration Front Page (Secciones A–I) with JSON data.
     */
    public function declaracionAnverso()
    {
        try {
            $intento = $this->getIntentoActivo();
            if (!$intento) {
                return $this->app->view('simulator/seniat_actual/sucesion/resumen_declaracion/declaracion_anverso', [
                    'datos' => null,
                ]);
            }

            if ($this->redirectIfHerederosIncompletos($intento)) return;

            $borrador = new BorradorService($intento);

            // ── Cálculos para autoliquidación (líneas 1–14) ──
            $totalInmuebles   = $borrador->getTotalBienesInmuebles();
            $totalMuebles     = $borrador->getTotalBienesMuebles();
            $patrimonioBruto  = $totalInmuebles + $totalMuebles;

            $desgravamenes    = $borrador->getTotalDesgravamenes();
            $exenciones       = $borrador->getTotalExenciones();
            $exoneraciones    = $borrador->getTotalExoneraciones();
            $totalExclusiones = $desgravamenes + $exenciones + $exoneraciones;

            $activoNeto = $patrimonioBruto - $totalExclusiones;
            if ($activoNeto < 0) $activoNeto = 0;

            $totalPasivos   = $borrador->getTotalPasivos();
            $patrimonioNeto = $activoNeto - $totalPasivos;
            if ($patrimonioNeto < 0) $patrimonioNeto = 0;

            // ── Herederos + tributo ──
            $herederos      = $borrador->getHerederosDetalle();
            $totalHerederos = count($herederos);

            $datosBasicos       = $borrador->getDatosBasicos();
            $fechaFallecimiento = $datosBasicos['fecha_fallecimiento'] ?? '';
            $utData  = \App\Core\UnidadTributariaService::obtenerPorFecha($fechaFallecimiento);
            $utFloat = $utData ? (float) $utData['valor'] : 0.0;

            $calculoManual = $borrador->getBorrador()['calculo_manual'] ?? null;

            if ($calculoManual && !empty($calculoManual['herederos'])) {
                $tributo = \App\Modules\Simulator\Services\TributoCalculator::calcularConOverrides(
                    $utFloat,
                    $herederos,
                    $calculoManual['herederos'],
                    \App\Core\DB::connect()
                );
            } else {
                $tributo = \App\Modules\Simulator\Services\TributoCalculator::calcular(
                    $patrimonioNeto,
                    $totalHerederos,
                    $utFloat,
                    $herederos,
                    \App\Core\DB::connect()
                );
            }

            // Catálogo parentescos
            $stmtPar = \App\Core\DB::connect()->query(
                'SELECT id, etiqueta, grupo_tarifa_id FROM sim_cat_parentescos WHERE activo = 1'
            );
            $parentescoCat = [];
            foreach ($stmtPar->fetchAll(\PDO::FETCH_ASSOC) as $row) {
                $parentescoCat[(int)$row['id']] = $row;
            }

            // Merge tributo → herederos
            foreach ($herederos as $i => &$h) {
                $calc = $tributo['herederos'][$i] ?? [];
                $h['cuota_parte_bs']       = round(($calc['cuota_parte_ut'] ?? 0.0) * $utFloat, 2);
                $h['porcentaje']           = $calc['porcentaje']           ?? 0.0;
                $h['sustraendo_bs']        = round(($calc['sustraendo_ut'] ?? 0.0) * $utFloat, 2);
                $h['impuesto_determinado'] = $calc['impuesto_determinado'] ?? 0.0;
                $h['reduccion']            = $calc['reduccion']            ?? 0.0;
                $h['impuesto_a_pagar']     = $calc['impuesto_a_pagar']     ?? 0.0;

                $pid = (int) ($h['parentesco_id'] ?? 0);
                $cat = $parentescoCat[$pid] ?? null;
                if ($cat) {
                    $h['parentesco'] = strtoupper($cat['etiqueta']);
                    $h['grado']      = (string) ($cat['grupo_tarifa_id'] ?? '1');
                }
            }
            unset($h);

            $rep = $borrador->getRepresentanteLegal();

            $fmt = function (float $v) use ($borrador) {
                return $borrador->formatDecimal($v);
            };

            $datos = [
                // Sección A
                'nombre_sucesion'   => $borrador->getNombreSucesion(),
                'rif'               => $borrador->getRif(),
                // Fechas
                'fecha_declaracion' => $borrador->getFechaDeclaracion(),
                'fecha_vencimiento' => $borrador->getFechaVencimiento(),
                // Sección B
                'nombre_causante'   => $borrador->getNombreCausante(),
                'rif_causante'      => $borrador->getRifCausante(),
                'cedula_causante'   => $borrador->getCedulaCausante(),
                // Sección C
                'domicilio_fiscal'      => $borrador->getDomicilioFiscal(),
                'fecha_fallecimiento'   => $borrador->getFechaFallecimiento(),
                // Sección D
                'representante_nombre' => $rep['nombre'],
                'representante_rif'    => $rep['rif'],
                // Sección E - Tipo de Herencia
                'tipos_herencia' => $this->buildTiposHerenciaTexto($borrador),
                // Sección F - Prórrogas
                'prorrogas' => $borrador->getBorrador()['prorrogas'] ?? [],
                // Sección G
                'herederos' => $herederos,
                // Sección H - Herederos Premuerto
                'herederos_premuertos' => $borrador->getBorrador()['herederos_premuertos'] ?? [],
                'relaciones'           => $borrador->getBorrador()['relaciones'] ?? [],
                'parentesco_cat'       => $parentescoCat,
                // Sección I – Autoliquidación (líneas 1–14)
                'linea_1'  => $fmt($totalInmuebles),
                'linea_2'  => $fmt($totalMuebles),
                'linea_3'  => $fmt($patrimonioBruto),
                'linea_4'  => $fmt($patrimonioBruto),
                'linea_5'  => $fmt($desgravamenes),
                'linea_6'  => $fmt($exenciones),
                'linea_7'  => $fmt($exoneraciones),
                'linea_8'  => $fmt($totalExclusiones),
                'linea_9'  => $fmt($activoNeto),
                'linea_10' => $fmt($totalPasivos),
                'linea_11' => $fmt($patrimonioNeto),
                'linea_12' => $fmt($tributo['linea_12']),
                'linea_13' => $fmt($tributo['linea_13']),
                'linea_14' => $fmt($tributo['total_impuesto_a_pagar']),  // Total Impuesto a Pagar
                'fmt'      => $fmt,
            ];

            return $this->app->view('simulator/seniat_actual/sucesion/resumen_declaracion/declaracion_anverso', [
                'datos' => $datos,
            ]);

        } catch (\Throwable $e) {
            error_log('[SucesionController::declaracionAnverso] Error: ' . $e->getMessage());
            return $this->app->view('simulator/seniat_actual/sucesion/resumen_declaracion/declaracion_anverso', [
                'datos' => null,
            ]);
        }
    }

    /**
     * GET /simulador/sucesion/declaracion_reverso
     * Displays the Declaration Reverse Page (A–D headers + J-Anexos) with JSON data.
     */
    public function declaracionReverso()
    {
        try {
            $intento = $this->getIntentoActivo();
            if (!$intento) {
                return $this->app->view('simulator/seniat_actual/sucesion/resumen_declaracion/declaracion_reverso', [
                    'datos' => null,
                ]);
            }

            if ($this->redirectIfHerederosIncompletos($intento)) return;

            $borrador = new BorradorService($intento);
            $rep = $borrador->getRepresentanteLegal();

            $fmt = function (float $v) use ($borrador) {
                return $borrador->formatDecimal($v);
            };

            // ── Tributo calculation (needed for Aviso modal monto a pagar) ──
            $totalInmuebles   = $borrador->getTotalBienesInmuebles();
            $totalMuebles     = $borrador->getTotalBienesMuebles();
            $patrimonioBruto  = $totalInmuebles + $totalMuebles;
            $desgravamenes    = $borrador->getTotalDesgravamenes();
            $exenciones       = $borrador->getTotalExenciones();
            $exoneraciones    = $borrador->getTotalExoneraciones();
            $totalExclusiones = $desgravamenes + $exenciones + $exoneraciones;
            $activoNeto = max(0, $patrimonioBruto - $totalExclusiones);
            $totalPasivos   = $borrador->getTotalPasivos();
            $patrimonioNeto = max(0, $activoNeto - $totalPasivos);

            $herederos      = $borrador->getHerederosDetalle();
            $totalHerederos = count($herederos);
            $datosBasicos       = $borrador->getDatosBasicos();
            $fechaFallecimiento = $datosBasicos['fecha_fallecimiento'] ?? '';
            $utData  = \App\Core\UnidadTributariaService::obtenerPorFecha($fechaFallecimiento);
            $utFloat = $utData ? (float) $utData['valor'] : 0.0;

            $calculoManual = $borrador->getBorrador()['calculo_manual'] ?? null;
            if ($calculoManual && !empty($calculoManual['herederos'])) {
                $tributo = \App\Modules\Simulator\Services\TributoCalculator::calcularConOverrides(
                    $utFloat, $herederos, $calculoManual['herederos'], \App\Core\DB::connect()
                );
            } else {
                $tributo = \App\Modules\Simulator\Services\TributoCalculator::calcular(
                    $patrimonioNeto, $totalHerederos, $utFloat, $herederos, \App\Core\DB::connect()
                );
            }

            $datos = [
                // Secciones A–D header (same as anverso)
                'nombre_sucesion'      => $borrador->getNombreSucesion(),
                'rif'                  => $borrador->getRif(),
                'fecha_declaracion'    => $borrador->getFechaDeclaracion(),
                'fecha_vencimiento'    => $borrador->getFechaVencimiento(),
                'nombre_causante'      => $borrador->getNombreCausante(),
                'rif_causante'         => $borrador->getRifCausante(),
                'cedula_causante'      => $borrador->getCedulaCausante(),
                'domicilio_fiscal'     => $borrador->getDomicilioFiscal(),
                'fecha_fallecimiento'  => $borrador->getFechaFallecimiento(),
                'representante_nombre' => $rep['nombre'],
                'representante_rif'    => $rep['rif'],
                // J – Anexos (detail items)
                'inmuebles'      => $borrador->getBienesInmueblesItems(),
                'muebles'        => $borrador->getBienesMueblesItems(),
                'pasivos'        => $borrador->getPasivosItems(),
                'desgravamenes'  => $borrador->getDesgravamenesItems(),
                'exenciones'     => $borrador->getExencionesItems(),
                'exoneraciones'  => $borrador->getExoneracionesItems(),
                // Totales for tfoot
                'total_inmuebles'     => $fmt($borrador->getTotalBienesInmuebles()),
                'total_muebles'       => $fmt($borrador->getTotalBienesMuebles()),
                'total_pasivos'       => $fmt($borrador->getTotalPasivos()),
                'total_desgravamenes' => $fmt($borrador->getTotalDesgravamenes()),
                'total_exenciones'    => $fmt($borrador->getTotalExenciones()),
                'total_exoneraciones' => $fmt($borrador->getTotalExoneraciones()),
                // Aviso modal
                'linea_14'            => $fmt($tributo['total_impuesto_a_pagar']),
                'fmt'                 => $fmt,
            ];

            return $this->app->view('simulator/seniat_actual/sucesion/resumen_declaracion/declaracion_reverso', [
                'datos' => $datos,
            ]);

        } catch (\Throwable $e) {
            error_log('[SucesionController::declaracionReverso] Error: ' . $e->getMessage());
            return $this->app->view('simulator/seniat_actual/sucesion/resumen_declaracion/declaracion_reverso', [
                'datos' => null,
            ]);
        }
    }

    // ─── Helpers ───

    /**
     * Checks if any heredero has parentesco_id = 0 (undefined).
     * If so, redirects to herederos page with datos_incompletos flag.
     * Returns true if redirect was issued, false otherwise.
     */
    private function redirectIfHerederosIncompletos(array $intento): bool
    {
        try {
            $borrador = new BorradorService($intento);
            $herederos = $borrador->getHerederosDetalle();

            foreach ($herederos as $h) {
                $pid = (int) ($h['parentesco_id'] ?? 0);
                // 0 = not set, 19 = "Sin Definir" in sim_cat_parentescos
                if ($pid === 0 || $pid === 19) {
                    $url = base_url('/simulador/sucesion/herederos?datos_incompletos=1');
                    header('Location: ' . $url);
                    exit;
                }
            }
        } catch (\Throwable $e) {
            error_log('[SucesionController::redirectIfHerederosIncompletos] ' . $e->getMessage());
        }
        return false;
    }

    /**
     * Loads the active intento for the current student + assignment.
     */
    private function getIntentoActivo(): ?array
    {
        try {
            $assignModel  = new StudentAssignmentModel();
            $attemptModel = new StudentAttemptModel();
            $estudianteId = $assignModel->getEstudianteId((int) $_SESSION['user_id']);

            if ($estudianteId && !empty($_SESSION['sim_asignacion_id'])) {
                return $attemptModel->getIntentoActivo((int) $_SESSION['sim_asignacion_id']);
            }
        } catch (\Throwable $e) {
            error_log('[SucesionController] Error cargando intento: ' . $e->getMessage());
        }
        return null;
    }

    /**
     * Build formatted text for "E - TIPO DE HERENCIA" section.
     * Reads tipos_herencia.items from borrador_json and maps IDs to catalog names.
     *
     * @param BorradorService $borrador
     * @return string  Formatted tipo herencia text (comma-separated)
     */
    private function buildTiposHerenciaTexto(BorradorService $borrador): string
    {
        try {
            $catalogo = [
                1 => 'Testamento',
                2 => 'Ab-Intestato',
                3 => 'Pura y Simple',
                4 => 'Presunción de Ausencia',
                5 => 'Presunción de Muerte por Accidente',
                6 => 'Beneficio de Inventario',
            ];

            $borr = $borrador->getBorrador();
            $items = $borr['tipos_herencia']['items'] ?? [];

            if (empty($items)) {
                return '';
            }

            $textos = [];
            foreach ($items as $item) {
                $id = (int) ($item['tipo_herencia_id'] ?? 0);
                $nombre = $catalogo[$id] ?? 'Desconocido';

                // Add details for Testamento
                if ($id === 1) {
                    $subtipo = $item['subtipo_testamento'] ?? '';
                    $fecha   = $item['fecha_testamento'] ?? '';
                    $detalles = array_filter([$subtipo, $fecha]);
                    if (!empty($detalles)) {
                        $nombre .= ' (' . implode(' - ', $detalles) . ')';
                    }
                }

                // Add details for Beneficio de Inventario
                if ($id === 6) {
                    $fecha = $item['fecha_conclusion_inventario'] ?? '';
                    if (!empty($fecha)) {
                        $nombre .= ' (Conclusión: ' . $fecha . ')';
                    }
                }

                $textos[] = $nombre;
            }

            return implode(', ', $textos);

        } catch (\Throwable $e) {
            error_log('[SucesionController::buildTiposHerenciaTexto] Error: ' . $e->getMessage());
            return '';
        }
    }
}
