<?php
declare(strict_types=1);

namespace App\Modules\Simulator\Services;

use App\Core\DB;

/**
 * RifGeneratorService — Genera RIF Sucesoral únicos.
 *
 * Extrae la lógica de generación de RIF de IntentosController
 * para reutilizarla en:
 * - Práctica Libre/Guiada  → IntentosController::validarRs()
 * - Evaluación (profesor)   → GeneracionRsController::aprobar()
 *
 * No depende de $_SESSION: toda la info se obtiene por queries.
 */
class RifGeneratorService
{
    /** Máximo de reintentos ante colisión UNIQUE del RIF. */
    private const MAX_REINTENTOS = 5;

    /**
     * Genera un RIF Sucesoral único y lo persiste en sim_intentos.
     *
     * Flujo:
     *   1. Obtiene tipo_cedula del causante vía JOINs
     *   2. Genera candidato: prefijo (V|E) + 8 dígitos aleatorios
     *   3. Intenta persistir con loop de reintentos por UNIQUE
     *
     * @param  int $intentoId  ID del intento en sim_intentos
     * @return string|null     RIF generado (ej: "V12345678") o null si falla
     */
    public function generar(int $intentoId): ?string
    {
        try {
            $db = DB::connect();

            // ── 1. Obtener prefijo (tipo_cedula del causante) ──
            $prefijo = $this->obtenerPrefijoCausante($db, $intentoId);

            // ── 2. Generar + persistir con reintentos ──
            for ($r = 0; $r < self::MAX_REINTENTOS; $r++) {
                $rifCandidate = $prefijo . str_pad(
                    (string) random_int(10000000, 99999999),
                    8,
                    '0',
                    STR_PAD_LEFT
                );

                try {
                    $stmt = $db->prepare("
                        UPDATE sim_intentos
                        SET rif_sucesoral = :rif,
                            updated_at    = NOW()
                        WHERE id = :id
                    ");
                    $stmt->execute([
                        'rif' => $rifCandidate,
                        'id'  => $intentoId,
                    ]);

                    return $rifCandidate;

                } catch (\PDOException $e) {
                    // Colisión UNIQUE → reintentar con otro candidato
                    if ($e->getCode() == '23000') {
                        continue;
                    }
                    error_log("[RifGeneratorService] PDO error: " . $e->getMessage());
                    return null;
                }
            }

            error_log("[RifGeneratorService] No se pudo generar RIF único tras " . self::MAX_REINTENTOS . " intentos para intento #{$intentoId}");
            return null;

        } catch (\Throwable $e) {
            error_log("[RifGeneratorService] Error crítico: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtiene el prefijo del RIF basado en el tipo_cedula del causante.
     *
     * @return string 'V' o 'E'
     */
    private function obtenerPrefijoCausante(\PDO $db, int $intentoId): string
    {
        try {
            $stmt = $db->prepare("
                SELECT p.tipo_cedula
                FROM sim_intentos i
                INNER JOIN sim_caso_asignaciones a  ON a.id  = i.asignacion_id
                INNER JOIN sim_caso_configs cfg     ON cfg.id = a.config_id
                INNER JOIN sim_casos_estudios ce    ON ce.id  = cfg.caso_id
                INNER JOIN sim_personas p           ON p.id   = ce.causante_id
                WHERE i.id = :intento_id
                LIMIT 1
            ");
            $stmt->execute(['intento_id' => $intentoId]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($row && !empty($row['tipo_cedula']) && $row['tipo_cedula'] === 'E') {
                return 'E';
            }
        } catch (\Throwable $e) {
            error_log("[RifGeneratorService] Error obteniendo prefijo: " . $e->getMessage());
        }

        return 'V'; // Default
    }

    /**
     * Obtiene datos del caso para correos (título, email/nombre estudiante).
     * Método utilitario usado por los controllers.
     *
     * @return array{caso_titulo: string, est_email: string, est_nombre: string}
     */
    public function getDatosParaCorreo(int $intentoId): array
    {
        $default = [
            'caso_titulo' => 'Caso Sucesoral',
            'est_email'   => '',
            'est_nombre'  => 'Estudiante',
        ];

        try {
            $db = DB::connect();
            $stmt = $db->prepare("
                SELECT ce.titulo AS caso_titulo,
                       u.email AS est_email,
                       CONCAT(p.nombres, ' ', p.apellidos) AS est_nombre
                FROM sim_intentos i
                INNER JOIN sim_caso_asignaciones a  ON a.id  = i.asignacion_id
                INNER JOIN sim_caso_configs cfg     ON cfg.id = a.config_id
                INNER JOIN sim_casos_estudios ce    ON ce.id  = cfg.caso_id
                INNER JOIN estudiantes e            ON e.id   = a.estudiante_id
                INNER JOIN personas p               ON p.id   = e.persona_id
                INNER JOIN users u                  ON u.persona_id = p.id
                WHERE i.id = :intento_id
                LIMIT 1
            ");
            $stmt->execute(['intento_id' => $intentoId]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($row) {
                return [
                    'caso_titulo' => $row['caso_titulo'] ?: $default['caso_titulo'],
                    'est_email'   => $row['est_email'] ?: '',
                    'est_nombre'  => $row['est_nombre'] ?: $default['est_nombre'],
                ];
            }
        } catch (\Throwable $e) {
            error_log("[RifGeneratorService] Error obteniendo datos correo: " . $e->getMessage());
        }

        return $default;
    }
}
