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
        $ut = $utData ? $utData['valor'] : '0,00';

        $fmt = function (float $v) use ($borrador) {
            return $borrador->formatDecimal($v);
        };

        $datos = [
            'ut'                 => $ut,
            'total_herederos'    => $totalHerederos,
            'total_inmuebles'    => $fmt($totalInmuebles),
            'total_muebles'     => $fmt($totalMuebles),
            'patrimonio_bruto'  => $fmt($patrimonioBruto),
            'activo_bruto'      => $fmt($patrimonioBruto), // fila 4 = fila 3
            'desgravamenes'     => $fmt($desgravamenes),
            'exenciones'        => $fmt($exenciones),
            'exoneraciones'     => $fmt($exoneraciones),
            'total_exclusiones' => $fmt($totalExclusiones),
            'activo_neto'       => $fmt($activoNeto),
            'total_pasivos'     => $fmt($totalPasivos),
            'patrimonio_neto'   => $fmt($patrimonioNeto),
            // Filas 12-15: por ahora 0 (determinación de tributo requiere cálculo con tarifa)
            'impuesto_tarifa'   => '0,00',
            'reducciones'       => '0,00',
            'impuesto_sustituida' => '0,00',
            'total_impuesto'    => '0,00',
            'herederos'         => $herederos,
        ];

        return $this->app->view('simulator/seniat_actual/sucesion/resumen_declaracion/resumen_declaracion', [
            'datos' => $datos,
        ]);
    }

    // ─── Helpers ───

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
}
