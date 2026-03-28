<?php
declare(strict_types=1);

namespace App\Modules\Professor\Models;

use App\Core\DB;

/**
 * GeneracionRsModel — Consultas para la bandeja de RIF Sucesoral del profesor.
 *
 * Todas las consultas filtran por modalidad = 'Evaluacion' y estados
 * relevantes (Pendiente_RIF, Aprobado, Rechazado).
 *
 * Anti-crash: todos los métodos tienen try/catch con retorno seguro.
 */
class GeneracionRsModel
{
    /**
     * Obtiene el ID del profesor vinculado a un usuario.
     *
     * @return int|null  ID del profesor o null si no existe
     */
    public function getProfesorId(int $userId): ?int
    {
        try {
            $db = DB::connect();
            $stmt = $db->prepare("
                SELECT pr.id
                FROM profesores pr
                INNER JOIN personas p ON p.id = pr.persona_id
                INNER JOIN users u    ON u.persona_id = p.id
                WHERE u.id = :uid
                LIMIT 1
            ");
            $stmt->execute(['uid' => $userId]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);

            return $row ? (int) $row['id'] : null;
        } catch (\Throwable $e) {
            error_log("[GeneracionRsModel::getProfesorId] " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtiene todas las solicitudes de RIF para las configuraciones del profesor.
     * Solo muestra intentos de configuraciones con modalidad = 'Evaluacion'.
     *
     * Incluye: pendientes (cola activa), aprobados y rechazados (historial).
     * Ordenadas: pendientes primero, luego rechazados, luego aprobados.
     *
     * @return array<int, array>
     */
    public function getSolicitudes(int $profesorId): array
    {
        try {
            $db = DB::connect();
            $stmt = $db->prepare("
                SELECT i.id,
                       i.numero_intento,
                       i.estado,
                       i.submitted_at,
                       i.rif_sucesoral,
                       i.nota_numerica,
                       i.nota_cualitativa,
                       i.observacion,
                       i.reviewed_at,
                       i.approved_at,
                       p.nombres AS est_nombres,
                       p.apellidos AS est_apellidos,
                       p.cedula AS est_cedula,
                       p.nacionalidad AS est_nacionalidad,
                       u.email AS est_email,
                       ce.titulo AS caso_titulo,
                       cfg.tipo_calificacion,
                       cfg.max_intentos,
                       sec.nombre AS seccion
                FROM sim_intentos i
                INNER JOIN sim_caso_asignaciones a ON a.id = i.asignacion_id
                INNER JOIN sim_caso_configs cfg    ON cfg.id = a.config_id
                INNER JOIN sim_casos_estudios ce   ON ce.id = cfg.caso_id
                INNER JOIN estudiantes e           ON e.id = a.estudiante_id
                INNER JOIN personas p              ON p.id = e.persona_id
                INNER JOIN users u                 ON u.persona_id = p.id
                LEFT  JOIN inscripciones ins       ON ins.estudiante_id = e.id
                LEFT  JOIN secciones sec           ON sec.id = ins.seccion_id
                WHERE cfg.profesor_id = :profId
                  AND cfg.modalidad = 'Evaluacion'
                  AND i.estado IN ('Pendiente_RIF', 'Aprobado', 'Rechazado')
                ORDER BY FIELD(i.estado, 'Pendiente_RIF', 'Rechazado', 'Aprobado'),
                         i.submitted_at DESC
            ");
            $stmt->execute(['profId' => $profesorId]);

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log("[GeneracionRsModel::getSolicitudes] " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene estadísticas de la bandeja del profesor.
     *
     * @return array{pendientes: int, aprobadas: int, rechazadas: int, total: int}
     */
    public function getStats(int $profesorId): array
    {
        $default = ['pendientes' => 0, 'aprobadas' => 0, 'rechazadas' => 0, 'total' => 0];

        try {
            $db = DB::connect();
            $stmt = $db->prepare("
                SELECT
                    COUNT(*) AS total,
                    SUM(i.estado = 'Pendiente_RIF') AS pendientes,
                    SUM(i.estado = 'Aprobado')      AS aprobadas,
                    SUM(i.estado = 'Rechazado')     AS rechazadas
                FROM sim_intentos i
                INNER JOIN sim_caso_asignaciones a ON a.id = i.asignacion_id
                INNER JOIN sim_caso_configs cfg    ON cfg.id = a.config_id
                WHERE cfg.profesor_id = :profId
                  AND cfg.modalidad = 'Evaluacion'
                  AND i.estado IN ('Pendiente_RIF', 'Aprobado', 'Rechazado')
            ");
            $stmt->execute(['profId' => $profesorId]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($row) {
                return [
                    'pendientes' => (int) ($row['pendientes'] ?? 0),
                    'aprobadas'  => (int) ($row['aprobadas'] ?? 0),
                    'rechazadas' => (int) ($row['rechazadas'] ?? 0),
                    'total'      => (int) ($row['total'] ?? 0),
                ];
            }
        } catch (\Throwable $e) {
            error_log("[GeneracionRsModel::getStats] " . $e->getMessage());
        }

        return $default;
    }

    /**
     * Obtiene un intento por ID, verificando que pertenezca al profesor.
     * Incluye datos del estudiante y tipo de calificación.
     *
     * @return array|null  Datos del intento o null si no existe/no pertenece
     */
    public function getIntentoPorId(int $intentoId, int $profesorId): ?array
    {
        try {
            $db = DB::connect();
            $stmt = $db->prepare("
                SELECT i.id,
                       i.estado,
                       i.numero_intento,
                       i.rif_sucesoral,
                       i.submitted_at,
                       p.nombres AS est_nombres,
                       p.apellidos AS est_apellidos,
                       p.cedula AS est_cedula,
                       u.email AS est_email,
                       ce.titulo AS caso_titulo,
                       cfg.tipo_calificacion,
                       cfg.max_intentos
                FROM sim_intentos i
                INNER JOIN sim_caso_asignaciones a ON a.id = i.asignacion_id
                INNER JOIN sim_caso_configs cfg    ON cfg.id = a.config_id
                INNER JOIN sim_casos_estudios ce   ON ce.id = cfg.caso_id
                INNER JOIN estudiantes e           ON e.id = a.estudiante_id
                INNER JOIN personas p              ON p.id = e.persona_id
                INNER JOIN users u                 ON u.persona_id = p.id
                WHERE i.id = :intentoId
                  AND cfg.profesor_id = :profId
                  AND cfg.modalidad = 'Evaluacion'
                LIMIT 1
            ");
            $stmt->execute([
                'intentoId' => $intentoId,
                'profId'    => $profesorId,
            ]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);

            return $row ?: null;
        } catch (\Throwable $e) {
            error_log("[GeneracionRsModel::getIntentoPorId] " . $e->getMessage());
            return null;
        }
    }

    /**
     * Aprueba un intento: cambia estado a 'Aprobado' y registra el RIF.
     * Solo afecta intentos en estado 'Pendiente_RIF' (idempotente).
     *
     * @param  int    $intentoId    ID del intento
     * @param  string $rifSucesoral RIF generado por RifGeneratorService
     * @return bool   true si afectó exactamente 1 fila
     */
    public function aprobar(int $intentoId, string $rifSucesoral): bool
    {
        try {
            $db = DB::connect();
            $stmt = $db->prepare("
                UPDATE sim_intentos
                SET estado       = 'Aprobado',
                    rif_sucesoral = :rif,
                    approved_at  = NOW(),
                    reviewed_at  = NOW(),
                    updated_at   = NOW()
                WHERE id = :id
                  AND estado = 'Pendiente_RIF'
            ");
            $stmt->execute([
                'rif' => $rifSucesoral,
                'id'  => $intentoId,
            ]);

            return $stmt->rowCount() === 1;
        } catch (\Throwable $e) {
            error_log("[GeneracionRsModel::aprobar] " . $e->getMessage());
            return false;
        }
    }

    /**
     * Rechaza un intento: cambia estado a 'Rechazado' con nota y observación.
     * Solo afecta intentos en estado 'Pendiente_RIF'.
     *
     * La nota se asigna según tipo_calificacion:
     *   - 'aprobado_reprobado' → nota_cualitativa = 'Reprobado', nota_numerica = NULL
     *   - 'numerica'           → nota_numerica = valor 0-9,    nota_cualitativa = NULL
     *
     * @param  int         $intentoId       ID del intento
     * @param  string      $tipoCalificacion 'numerica' o 'aprobado_reprobado'
     * @param  float|null  $notaNumerica    Valor 0-9 (solo si tipo = numerica)
     * @param  string|null $notaCualitativa 'Reprobado' (solo si tipo = aprobado_reprobado)
     * @param  string      $observacion     Motivo del rechazo (obligatorio)
     * @return bool        true si afectó exactamente 1 fila
     */
    public function rechazar(
        int     $intentoId,
        string  $tipoCalificacion,
        ?float  $notaNumerica,
        ?string $notaCualitativa,
        string  $observacion
    ): bool {
        try {
            $db = DB::connect();
            $stmt = $db->prepare("
                UPDATE sim_intentos
                SET estado           = 'Rechazado',
                    nota_numerica    = :nota_num,
                    nota_cualitativa = :nota_cual,
                    observacion      = :obs,
                    reviewed_at      = NOW(),
                    updated_at       = NOW()
                WHERE id = :id
                  AND estado = 'Pendiente_RIF'
            ");
            $stmt->execute([
                'nota_num'  => $notaNumerica,
                'nota_cual' => $notaCualitativa,
                'obs'       => $observacion,
                'id'        => $intentoId,
            ]);

            return $stmt->rowCount() === 1;
        } catch (\Throwable $e) {
            error_log("[GeneracionRsModel::rechazar] " . $e->getMessage());
            return false;
        }
    }
}
