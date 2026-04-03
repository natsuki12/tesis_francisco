<?php
declare(strict_types=1);

namespace App\Modules\Professor\Models;

use App\Core\DB;

/**
 * Modelo de Estadísticas del Profesor.
 * Agrega datos de intentos, notas y asignaciones filtrados por profesor_id.
 */
class EstadisticasModel
{
    /**
     * Tarjetas resumen: estudiantes, casos activos, intentos totales, tasa de éxito.
     */
    public function getResumen(int $profesorId): array
    {
        $db = DB::connect();

        // 1. Estudiantes inscritos en secciones del profesor
        $stmt = $db->prepare("
            SELECT COUNT(DISTINCT ins.estudiante_id)
            FROM inscripciones ins
            INNER JOIN secciones s ON s.id = ins.seccion_id
            WHERE s.profesor_id = :pid
        ");
        $stmt->execute([':pid' => $profesorId]);
        $estudiantes = (int) ($stmt->fetchColumn() ?: 0);

        // 1b. Secciones del profesor
        $stmt = $db->prepare("
            SELECT COUNT(*) FROM secciones WHERE profesor_id = :pid
        ");
        $stmt->execute([':pid' => $profesorId]);
        $secciones = (int) ($stmt->fetchColumn() ?: 0);

        // 2. Casos publicados del profesor
        $stmt = $db->prepare("
            SELECT COUNT(*)
            FROM sim_casos_estudios
            WHERE profesor_id = :pid AND estado = 'Publicado'
        ");
        $stmt->execute([':pid' => $profesorId]);
        $casos = (int) ($stmt->fetchColumn() ?: 0);

        // 3. Intentos totales
        $stmt = $db->prepare("
            SELECT COUNT(*)
            FROM sim_intentos i
            INNER JOIN sim_caso_asignaciones a ON a.id = i.asignacion_id
            INNER JOIN sim_caso_configs cfg    ON cfg.id = a.config_id
            WHERE cfg.profesor_id = :pid
        ");
        $stmt->execute([':pid' => $profesorId]);
        $intentos = (int) ($stmt->fetchColumn() ?: 0);

        // 4. Tasa de éxito: Aprobados / (Aprobados + Rechazados)
        $stmt = $db->prepare("
            SELECT
                SUM(CASE WHEN i.estado = 'Aprobado' THEN 1 ELSE 0 END) AS aprobados,
                SUM(CASE WHEN i.estado IN ('Aprobado', 'Rechazado') THEN 1 ELSE 0 END) AS evaluados
            FROM sim_intentos i
            INNER JOIN sim_caso_asignaciones a ON a.id = i.asignacion_id
            INNER JOIN sim_caso_configs cfg    ON cfg.id = a.config_id
            WHERE cfg.profesor_id = :pid
        ");
        $stmt->execute([':pid' => $profesorId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        $aprobados = (int) ($row['aprobados'] ?? 0);
        $evaluados = (int) ($row['evaluados'] ?? 0);
        $tasaExito = $evaluados > 0 ? round(($aprobados / $evaluados) * 100) : 0;

        // 5. Asignaciones activas
        $stmt = $db->prepare("
            SELECT COUNT(*)
            FROM sim_caso_asignaciones a
            INNER JOIN sim_caso_configs cfg ON cfg.id = a.config_id
            WHERE cfg.profesor_id = :pid
        ");
        $stmt->execute([':pid' => $profesorId]);
        $asignaciones = (int) ($stmt->fetchColumn() ?: 0);

        // 6. Promedio de notas numéricas
        $stmt = $db->prepare("
            SELECT AVG(i.nota_numerica)
            FROM sim_intentos i
            INNER JOIN sim_caso_asignaciones a ON a.id = i.asignacion_id
            INNER JOIN sim_caso_configs cfg    ON cfg.id = a.config_id
            WHERE cfg.profesor_id = :pid
              AND i.nota_numerica IS NOT NULL
        ");
        $stmt->execute([':pid' => $profesorId]);
        $promedio = $stmt->fetchColumn();
        $promedio = $promedio !== false ? round((float) $promedio, 1) : null;

        return [
            'estudiantes'  => $estudiantes,
            'secciones'    => $secciones,
            'casos'        => $casos,
            'intentos'     => $intentos,
            'tasa_exito'   => (int) $tasaExito,
            'asignaciones' => $asignaciones,
            'promedio'     => $promedio,
        ];
    }

    /**
     * Distribución de intentos por estado para gráfico de dona.
     * Retorna [estado => count].
     */
    public function getDistribucionEstados(int $profesorId): array
    {
        $db = DB::connect();
        $stmt = $db->prepare("
            SELECT i.estado, COUNT(*) AS total
            FROM sim_intentos i
            INNER JOIN sim_caso_asignaciones a ON a.id = i.asignacion_id
            INNER JOIN sim_caso_configs cfg    ON cfg.id = a.config_id
            WHERE cfg.profesor_id = :pid
            GROUP BY i.estado
            ORDER BY total DESC
        ");
        $stmt->execute([':pid' => $profesorId]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $result = [];
        foreach ($rows as $r) {
            $result[$r['estado']] = (int) $r['total'];
        }
        return $result;
    }

    /**
     * Rendimiento por caso: tabla con métricas por cada caso del profesor.
     */
    public function getRendimientoPorCaso(int $profesorId): array
    {
        $db = DB::connect();
        $stmt = $db->prepare("
            SELECT
                c.titulo,
                cfg.modalidad,
                cfg.tipo_calificacion,
                COUNT(DISTINCT a.estudiante_id) AS asignados,
                COUNT(i.id) AS intentos,
                SUM(CASE WHEN i.estado = 'Aprobado' THEN 1 ELSE 0 END) AS aprobados,
                SUM(CASE WHEN i.estado = 'Rechazado' THEN 1 ELSE 0 END) AS rechazados,
                SUM(CASE WHEN i.estado = 'En_Progreso' THEN 1 ELSE 0 END) AS en_progreso
            FROM sim_caso_configs cfg
            INNER JOIN sim_casos_estudios c ON c.id = cfg.caso_id
            LEFT JOIN sim_caso_asignaciones a ON a.config_id = cfg.id
            LEFT JOIN sim_intentos i ON i.asignacion_id = a.id
            WHERE cfg.profesor_id = :pid
            GROUP BY c.id, c.titulo, cfg.modalidad, cfg.tipo_calificacion
            ORDER BY intentos DESC
        ");
        $stmt->execute([':pid' => $profesorId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Distribución de notas:
     * - numericas: agrupadas en rangos (0-5, 6-10, 11-15, 16-20)
     * - cualitativas: conteo de Aprobado vs Reprobado
     */
    public function getDistribucionNotas(int $profesorId): array
    {
        $db = DB::connect();

        // Notas numéricas por rango
        $stmt = $db->prepare("
            SELECT
                SUM(CASE WHEN i.nota_numerica BETWEEN 0 AND 5 THEN 1 ELSE 0 END) AS r_0_5,
                SUM(CASE WHEN i.nota_numerica BETWEEN 6 AND 10 THEN 1 ELSE 0 END) AS r_6_10,
                SUM(CASE WHEN i.nota_numerica BETWEEN 11 AND 15 THEN 1 ELSE 0 END) AS r_11_15,
                SUM(CASE WHEN i.nota_numerica BETWEEN 16 AND 20 THEN 1 ELSE 0 END) AS r_16_20,
                COUNT(i.nota_numerica) AS total_numericas
            FROM sim_intentos i
            INNER JOIN sim_caso_asignaciones a ON a.id = i.asignacion_id
            INNER JOIN sim_caso_configs cfg    ON cfg.id = a.config_id
            WHERE cfg.profesor_id = :pid
              AND i.nota_numerica IS NOT NULL
        ");
        $stmt->execute([':pid' => $profesorId]);
        $num = $stmt->fetch(\PDO::FETCH_ASSOC);

        // Notas cualitativas
        $stmt = $db->prepare("
            SELECT
                i.nota_cualitativa,
                COUNT(*) AS total
            FROM sim_intentos i
            INNER JOIN sim_caso_asignaciones a ON a.id = i.asignacion_id
            INNER JOIN sim_caso_configs cfg    ON cfg.id = a.config_id
            WHERE cfg.profesor_id = :pid
              AND i.nota_cualitativa IS NOT NULL
            GROUP BY i.nota_cualitativa
        ");
        $stmt->execute([':pid' => $profesorId]);
        $cualRows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $cualitativas = [
            'Aprobado' => 0,
            'Reprobado' => 0
        ];
        foreach ($cualRows as $r) {
            $cualitativas[$r['nota_cualitativa']] = (int) $r['total'];
        }

        return [
            'numericas' => [
                '0 – 5'   => (int) ($num['r_0_5'] ?? 0),
                '6 – 10'  => (int) ($num['r_6_10'] ?? 0),
                '11 – 15' => (int) ($num['r_11_15'] ?? 0),
                '16 – 20' => (int) ($num['r_16_20'] ?? 0),
                'total'   => (int) ($num['total_numericas'] ?? 0),
            ],
            'cualitativas' => $cualitativas,
        ];
    }

    /**
     * Estudiantes asignados que nunca han iniciado un intento.
     */
    public function getEstudiantesSinActividad(int $profesorId): array
    {
        $db = DB::connect();
        $stmt = $db->prepare("
            SELECT
                p.nombres,
                p.apellidos,
                p.cedula,
                p.nacionalidad,
                u.email,
                c.titulo AS caso,
                cfg.nombre AS asignacion,
                s.nombre AS seccion
            FROM sim_caso_asignaciones a
            INNER JOIN sim_caso_configs cfg ON cfg.id = a.config_id
            INNER JOIN sim_casos_estudios c ON c.id = cfg.caso_id
            INNER JOIN estudiantes est      ON est.id = a.estudiante_id
            INNER JOIN personas p           ON p.id = est.persona_id
            INNER JOIN users u              ON u.persona_id = p.id
            LEFT JOIN inscripciones ins     ON ins.estudiante_id = est.id
            LEFT JOIN secciones s           ON s.id = ins.seccion_id AND s.profesor_id = :pid2
            LEFT JOIN sim_intentos i        ON i.asignacion_id = a.id
            WHERE cfg.profesor_id = :pid
              AND i.id IS NULL
            ORDER BY p.apellidos, p.nombres
        ");
        $stmt->execute([':pid' => $profesorId, ':pid2' => $profesorId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
