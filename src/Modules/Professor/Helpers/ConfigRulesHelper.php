<?php
declare(strict_types=1);

namespace App\Modules\Professor\Helpers;

use App\Core\DB;
use PDO;

/**
 * Motor de reglas de edición para configuraciones de asignaciones.
 * Determina qué campos son editables basándose en la existencia de intentos.
 */
class ConfigRulesHelper
{
    private PDO $db;

    public function __construct()
    {
        $this->db = DB::connect();
    }

    /**
     * Devuelve las reglas de edición para una configuración específica.
     */
    public function getEditRules(int $configId): array
    {
        // Obtener info de cada asignación con conteo de intentos
        $sql = "SELECT a.id AS asignacion_id, a.estudiante_id, a.estado,
                       COUNT(i.id) AS num_intentos,
                       COALESCE(MAX(i.numero_intento), 0) AS max_intento
                FROM sim_caso_asignaciones a
                LEFT JOIN sim_intentos i ON i.asignacion_id = a.id
                WHERE a.config_id = :config_id
                GROUP BY a.id, a.estudiante_id, a.estado";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['config_id' => $configId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $totalIntentos = 0;
        $maxIntentosUsados = 0;
        $conIntentos = [];
        $sinIntentos = [];

        foreach ($rows as $r) {
            $numInt = (int) $r['num_intentos'];
            $totalIntentos += $numInt;
            if ((int) $r['max_intento'] > $maxIntentosUsados) {
                $maxIntentosUsados = (int) $r['max_intento'];
            }
            if ($numInt > 0) {
                $conIntentos[] = (int) $r['asignacion_id'];
            } else {
                $sinIntentos[] = (int) $r['asignacion_id'];
            }
        }

        $tieneIntentos = $totalIntentos > 0;

        return [
            'total_intentos' => $totalIntentos,
            'max_intentos_usados' => $maxIntentosUsados,
            'modalidad_editable' => !$tieneIntentos,
            'intentos_editable' => true, // siempre, pero con min
            'min_intentos_permitido' => $maxIntentosUsados,
            'fechas_editables' => true, // siempre
            'puede_eliminar' => !$tieneIntentos,
            'puede_inactivar' => true, // siempre
            'estudiantes_con_intentos' => $conIntentos,
            'estudiantes_sin_intentos' => $sinIntentos,
        ];
    }

    /**
     * Verifica si un estudiante ya está asignado activamente a otra config del mismo caso.
     * Retorna el config_id donde está asignado, o null si no lo está.
     */
    public function estudianteYaAsignadoEnCaso(int $casoId, int $estudianteId, ?int $excludeConfigId = null): ?int
    {
        $sql = "SELECT c.id AS config_id
                FROM sim_caso_asignaciones a
                JOIN sim_caso_configs c ON a.config_id = c.id
                WHERE c.caso_id = :caso_id
                  AND a.estudiante_id = :est_id
                  AND a.estado != 'Inactivo'
                  AND c.status = 'Activo'";
        $params = ['caso_id' => $casoId, 'est_id' => $estudianteId];

        if ($excludeConfigId !== null) {
            $sql .= " AND c.id != :exclude_id";
            $params['exclude_id'] = $excludeConfigId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int) $row['config_id'] : null;
    }
}
