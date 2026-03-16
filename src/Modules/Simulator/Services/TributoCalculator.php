<?php
declare(strict_types=1);

namespace App\Modules\Simulator\Services;

use PDO;
use App\Core\DB;

/**
 * Calcula la Determinación de Tributo para la declaración sucesoral.
 *
 * Implementa el flujo verificado contra SENIAT real:
 *  1. Cuota parte = patrimonio_neto / total_herederos (en UT)
 *  2. Por heredero: lookup de tramo tarifario en sim_cat_tarifas_sucesion
 *  3. Exención Art. 9: grupo 1 + cuota ≤ 75 UT → impuesto = 0
 *  4. Fórmula: impuesto_ut = (cuota_ut × % / 100) − sustraendo_ut
 *  5. Totales: líneas 12–15 del resumen
 *
 * @see flujo_calculo_determinacion_tributo_seniat_comprobado.md
 */
class TributoCalculator
{
    /**
     * Ejecuta el cálculo completo de determinación de tributo.
     *
     * @param  float $patrimonioNeto  Línea 11 del reverso (Bs)
     * @param  int   $totalHerederos  Cantidad total de herederos
     * @param  float $valorUT         Valor de la Unidad Tributaria vigente (Bs)
     * @param  array $herederos       Lista con 'parentesco_id' (int) por heredero
     * @param  PDO   $db              Conexión a la base de datos
     * @return array{
     *     herederos: array<int, array{
     *         cuota_parte_ut: float,
     *         porcentaje: float,
     *         sustraendo_ut: float,
     *         impuesto_determinado: float,
     *         reduccion: float,
     *         impuesto_a_pagar: float
     *     }>,
     *     linea_12: float,
     *     linea_13: float,
     *     linea_14: float,
     *     total_impuesto_a_pagar: float
     * }
     */
    public static function calcular(
        float $patrimonioNeto,
        int $totalHerederos,
        float $valorUT,
        array $herederos,
        PDO $db
    ): array {
        $empty = self::resultadoVacio(count($herederos));

        // ── Guardas: evitar división por cero y casos sin patrimonio ──
        if ($totalHerederos <= 0 || $patrimonioNeto <= 0 || $valorUT <= 0) {
            return $empty;
        }

        try {
            // ── Paso 1: Cuota parte (igual para todos) ──
            $cuotaParteBs = $patrimonioNeto / $totalHerederos;
            $cuotaParteUT = $cuotaParteBs / $valorUT;

            // ── Cargar catálogos ──
            $gruposPorParentesco = self::cargarGruposTarifa($db);
            $tramosPorGrupo = self::cargarTramosTarifa($db);

            // ── Paso 2: Cálculo por heredero ──
            $resultados = [];
            $linea12 = 0.0;  // sum of impuesto_a_pagar (after reductions)
            $linea13 = 0.0;  // sum of reducciones (informativo)

            foreach ($herederos as $h) {
                $parentescoId = (int) ($h['parentesco_id'] ?? 0);

                // Grupo tarifario: default 4 (extraños) si no se encuentra
                $grupoId = $gruposPorParentesco[$parentescoId] ?? 4;

                // Buscar tramo aplicable
                $tramo = self::buscarTramo($tramosPorGrupo, $grupoId, $cuotaParteUT);

                $porcentaje = $tramo ? (float) $tramo['porcentaje'] : 0.0;
                $sustraendoUT = $tramo ? (float) $tramo['sustraendo_ut'] : 0.0;

                // Exención Art. 9: grupo 1 y cuota ≤ 75 UT → impuesto = 0
                if ($grupoId === 1 && $cuotaParteUT <= 75.0) {
                    $impuestoDeterminado = 0.0;
                } else {
                    $impuestoUT = ($cuotaParteUT * $porcentaje / 100) - $sustraendoUT;
                    $impuestoDeterminado = round($impuestoUT * $valorUT, 2);
                }

                // Reducción: viene del cálculo manual, por ahora 0
                $reduccion = (float) ($h['reduccion_bs'] ?? 0.0);
                $impuestoAPagar = max(0.0, round($impuestoDeterminado - $reduccion, 2));

                $linea12 += $impuestoAPagar;
                $linea13 += $reduccion;

                $resultados[] = [
                    'cuota_parte_ut' => round($cuotaParteUT, 2),
                    'porcentaje' => $porcentaje,
                    'sustraendo_ut' => $sustraendoUT,
                    'impuesto_determinado' => $impuestoDeterminado,
                    'reduccion' => $reduccion,
                    'impuesto_a_pagar' => $impuestoAPagar,
                ];
            }

            // ── Paso 3: Totales ──
            // linea_14 = declaración sustituida (siempre 0 en originaria)
            return [
                'herederos' => $resultados,
                'linea_12' => round($linea12, 2),
                'linea_13' => round($linea13, 2),
                'linea_14' => 0.0,
                'total_impuesto_a_pagar' => round($linea12, 2),
            ];

        } catch (\Throwable $e) {
            error_log('[TributoCalculator] Error: ' . $e->getMessage());
            return $empty;
        }
    }

    /**
     * Recalcula el tributo usando overrides manuales por heredero.
     *
     * @param  float $valorUT         Valor de la UT vigente (Bs)
     * @param  array $herederos       Lista con 'parentesco_id' por heredero
     * @param  array $overrides       Array indexado: [{cuota_parte_ut, reduccion_bs}, ...]
     * @param  PDO   $db              Conexión a la base de datos
     * @return array  Misma estructura que calcular()
     */
    public static function calcularConOverrides(
        float $valorUT,
        array $herederos,
        array $overrides,
        PDO $db
    ): array {
        $empty = self::resultadoVacio(count($herederos));

        if ($valorUT <= 0) {
            return $empty;
        }

        try {
            $gruposPorParentesco = self::cargarGruposTarifa($db);
            $tramosPorGrupo      = self::cargarTramosTarifa($db);

            $resultados = [];
            $linea12 = 0.0;
            $linea13 = 0.0;

            foreach ($herederos as $i => $h) {
                $parentescoId = (int) ($h['parentesco_id'] ?? 0);
                $grupoId = $gruposPorParentesco[$parentescoId] ?? 4;

                // Usar cuota parte del override
                $cuotaParteUT = (float) ($overrides[$i]['cuota_parte_ut'] ?? 0.0);

                // Buscar tramo con la cuota parte manual
                $tramo = self::buscarTramo($tramosPorGrupo, $grupoId, $cuotaParteUT);

                $porcentaje   = $tramo ? (float) $tramo['porcentaje']    : 0.0;
                $sustraendoUT = $tramo ? (float) $tramo['sustraendo_ut'] : 0.0;

                // Exención Art. 9
                if ($grupoId === 1 && $cuotaParteUT <= 75.0) {
                    $impuestoDeterminado = 0.0;
                } else {
                    $impuestoUT          = ($cuotaParteUT * $porcentaje / 100) - $sustraendoUT;
                    $impuestoDeterminado = round($impuestoUT * $valorUT, 2);
                }

                $reduccion      = (float) ($overrides[$i]['reduccion_bs'] ?? 0.0);
                $impuestoAPagar = max(0.0, round($impuestoDeterminado - $reduccion, 2));

                $linea12 += $impuestoAPagar;
                $linea13 += $reduccion;

                $resultados[] = [
                    'cuota_parte_ut'       => round($cuotaParteUT, 2),
                    'porcentaje'           => $porcentaje,
                    'sustraendo_ut'        => $sustraendoUT,
                    'impuesto_determinado' => $impuestoDeterminado,
                    'reduccion'            => $reduccion,
                    'impuesto_a_pagar'     => $impuestoAPagar,
                ];
            }

            return [
                'herederos'              => $resultados,
                'linea_12'               => round($linea12, 2),
                'linea_13'               => round($linea13, 2),
                'linea_14'               => 0.0,
                'total_impuesto_a_pagar' => round($linea12, 2),
            ];

        } catch (\Throwable $e) {
            error_log('[TributoCalculator::calcularConOverrides] Error: ' . $e->getMessage());
            return $empty;
        }
    }

    // ─────────────────────── Helpers ───────────────────────

    /**
     * Carga el mapeo parentesco_id → grupo_tarifa_id desde la DB.
     *
     * @return array<int, int>  [parentesco_id => grupo_tarifa_id]
     */
    private static function cargarGruposTarifa(PDO $db): array
    {
        $stmt = $db->query(
            'SELECT id, grupo_tarifa_id FROM sim_cat_parentescos WHERE activo = 1'
        );
        $map = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $map[(int) $row['id']] = $row['grupo_tarifa_id'] !== null
                ? (int) $row['grupo_tarifa_id']
                : 4; // Sin definir → extraños
        }
        return $map;
    }

    /**
     * Carga todos los tramos de tarifa agrupados por grupo_tarifa_id.
     *
     * @return array<int, array<int, array>>  [grupo_id => [ tramo, ... ]]
     */
    private static function cargarTramosTarifa(PDO $db): array
    {
        $stmt = $db->query(
            'SELECT grupo_tarifa_id, rango_desde_ut, rango_hasta_ut,
                    porcentaje, sustraendo_ut
               FROM sim_cat_tarifas_sucesion
              WHERE activo = 1
              ORDER BY grupo_tarifa_id, rango_desde_ut'
        );
        $tramos = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $g = (int) $row['grupo_tarifa_id'];
            $tramos[$g][] = $row;
        }
        return $tramos;
    }

    /**
     * Busca el tramo aplicable para un grupo y cuota parte dada.
     *
     * @param  array<int, array> $tramosPorGrupo
     * @param  int               $grupoId
     * @param  float             $cuotaParteUT
     * @return array|null        Tramo encontrado o null si no hay
     */
    private static function buscarTramo(array $tramosPorGrupo, int $grupoId, float $cuotaParteUT): ?array
    {
        $tramos = $tramosPorGrupo[$grupoId] ?? [];
        foreach ($tramos as $t) {
            $desde = (float) $t['rango_desde_ut'];
            $hasta = $t['rango_hasta_ut'];

            if ($cuotaParteUT >= $desde && ($hasta === null || $cuotaParteUT <= (float) $hasta)) {
                return $t;
            }
        }
        // Fallback: último tramo del grupo (sin límite superior)
        return !empty($tramos) ? end($tramos) : null;
    }

    /**
     * Genera un resultado vacío con todo en 0,00.
     *
     * @param  int $count  Cantidad de herederos
     * @return array
     */
    private static function resultadoVacio(int $count): array
    {
        $hVacio = [
            'cuota_parte_ut' => 0.0,
            'porcentaje' => 0.0,
            'sustraendo_ut' => 0.0,
            'impuesto_determinado' => 0.0,
            'reduccion' => 0.0,
            'impuesto_a_pagar' => 0.0,
        ];
        return [
            'herederos' => array_fill(0, max(0, $count), $hVacio),
            'linea_12' => 0.0,
            'linea_13' => 0.0,
            'linea_14' => 0.0,
            'total_impuesto_a_pagar' => 0.0,
        ];
    }
}
