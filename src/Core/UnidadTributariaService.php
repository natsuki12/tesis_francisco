<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

/**
 * Servicio compartido para resolver la Unidad Tributaria aplicable
 * a partir de una fecha (típicamente fecha_fallecimiento del causante).
 *
 * Reglas:
 *  - Compara por fecha_gaceta en sim_cat_unidades_tributarias.
 *  - Si la fecha es anterior a 2021, devuelve la UT con id = 21.
 *  - Si la fecha es posterior a todas las UTs registradas, devuelve la última (fecha más reciente).
 */
class UnidadTributariaService
{
    /**
     * Obtener la UT aplicable para una fecha dada.
     *
     * @param  string $fecha  Fecha en cualquier formato parseable (YYYY-MM-DD, DD/MM/YYYY, etc.)
     * @return array{id: int, anio: int, valor: string, fecha_gaceta: ?string}|null
     */
    public static function obtenerPorFecha(string $fecha): ?array
    {
        try {
            // Normalizar la fecha a YYYY-MM-DD
            $timestamp = strtotime(str_replace('/', '-', $fecha));
            if ($timestamp === false) {
                error_log("[UnidadTributariaService] Fecha inválida: {$fecha}");
                return null;
            }
            $fechaNorm = date('Y-m-d', $timestamp);
            $anio = (int) date('Y', $timestamp);

            $pdo = DB::connect();

            // Regla 1: Si el año es anterior a 2021, usar UT con id = 21
            if ($anio < 2021) {
                $stmt = $pdo->prepare(
                    'SELECT id, anio, valor, fecha_gaceta
                       FROM sim_cat_unidades_tributarias
                      WHERE id = 21 AND activo = 1
                      LIMIT 1'
                );
                $stmt->execute();
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                return $row ?: null;
            }

            // Regla 2: Buscar la UT cuya fecha_gaceta sea la más alta
            //          pero que no supere la fecha dada.
            //          Es decir: la UT vigente a esa fecha.
            $stmt = $pdo->prepare(
                'SELECT id, anio, valor, fecha_gaceta
                   FROM sim_cat_unidades_tributarias
                  WHERE activo = 1
                    AND (fecha_gaceta IS NULL OR fecha_gaceta <= :fecha)
                  ORDER BY anio DESC
                  LIMIT 1'
            );
            $stmt->execute([':fecha' => $fechaNorm]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            // Regla 3: Si la fecha es posterior a todas las UTs registradas
            //          (o no se encontró, ej. fecha futura), devolver la última.
            if (!$row) {
                $stmt = $pdo->prepare(
                    'SELECT id, anio, valor, fecha_gaceta
                       FROM sim_cat_unidades_tributarias
                      WHERE activo = 1
                      ORDER BY anio DESC
                      LIMIT 1'
                );
                $stmt->execute();
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
            }

            return $row ?: null;

        } catch (\Throwable $e) {
            error_log("[UnidadTributariaService] Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Shortcut: obtener solo el valor numérico de la UT.
     *
     * @param  string $fecha
     * @return float  Valor de la UT, o 0.0 si no se encuentra.
     */
    public static function obtenerValor(string $fecha): float
    {
        $ut = self::obtenerPorFecha($fecha);
        return $ut ? (float) $ut['valor'] : 0.0;
    }
}
