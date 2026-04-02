<?php
declare(strict_types=1);

namespace App\Modules\Admin\Models\Monitoreo;

use App\Core\DB;

/**
 * Modelo de Reportes y Estadísticas globales del Administrador.
 * Todas las queries son globales (sin filtro por profesor_id).
 * Anti-crash: cada método tiene try/catch con retorno seguro.
 */
class AdminReportesModel
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = DB::connect();
    }

    /**
     * KPIs principales: estudiantes, profesores, secciones, casos, intentos.
     */
    public function getKPI(): array
    {
        $defaults = ['estudiantes' => 0, 'profesores' => 0, 'secciones' => 0, 'casos' => 0, 'intentos' => 0, 'asignaciones' => 0, 'asignaciones_con_intento' => 0];
        try {
            $r = [];
            $r['estudiantes'] = (int) $this->db->query("SELECT COUNT(*) FROM estudiantes")->fetchColumn();
            $r['profesores']  = (int) $this->db->query("SELECT COUNT(*) FROM profesores")->fetchColumn();
            $r['secciones']   = (int) $this->db->query("SELECT COUNT(*) FROM secciones")->fetchColumn();
            $r['casos']       = (int) $this->db->query("SELECT COUNT(*) FROM sim_casos_estudios WHERE estado = 'Publicado'")->fetchColumn();
            $r['intentos']    = (int) $this->db->query("SELECT COUNT(*) FROM sim_intentos")->fetchColumn();
            $r['asignaciones'] = (int) $this->db->query("SELECT COUNT(*) FROM sim_caso_asignaciones")->fetchColumn();
            $r['asignaciones_con_intento'] = (int) $this->db->query("SELECT COUNT(DISTINCT asignacion_id) FROM sim_intentos")->fetchColumn();
            return $r;
        } catch (\Throwable $e) {
            error_log('[AdminReportesModel::getKPI] ' . $e->getMessage());
            return $defaults;
        }
    }

    /**
     * Distribución de intentos por estado para gráfico de dona.
     */
    public function getDistribucionEstados(): array
    {
        try {
            $stmt = $this->db->query("
                SELECT estado, COUNT(*) AS total
                FROM sim_intentos
                GROUP BY estado
                ORDER BY total DESC
            ");
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $result = [];
            foreach ($rows as $r) {
                $result[$r['estado']] = (int) $r['total'];
            }
            return $result;
        } catch (\Throwable $e) {
            error_log('[AdminReportesModel::getDistribucionEstados] ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Tasa de éxito global: Aprobados / (Aprobados + Rechazados) * 100.
     */
    public function getTasaExito(): int
    {
        try {
            $row = $this->db->query("
                SELECT
                    SUM(CASE WHEN estado = 'Aprobado' THEN 1 ELSE 0 END) AS aprobados,
                    SUM(CASE WHEN estado IN ('Aprobado', 'Rechazado') THEN 1 ELSE 0 END) AS evaluados
                FROM sim_intentos
            ")->fetch(\PDO::FETCH_ASSOC);
            $aprobados = (int) ($row['aprobados'] ?? 0);
            $evaluados = (int) ($row['evaluados'] ?? 0);
            return $evaluados > 0 ? (int) round(($aprobados / $evaluados) * 100) : 0;
        } catch (\Throwable $e) {
            error_log('[AdminReportesModel::getTasaExito] ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Rendimiento por sección para la tabla comparativa.
     */
    public function getRendimientoPorSeccion(): array
    {
        try {
            $stmt = $this->db->query("
                SELECT
                    s.id AS seccion_id,
                    s.nombre AS seccion,
                    CONCAT(pe.nombres, ' ', pe.apellidos) AS profesor,
                    COUNT(DISTINCT ins.estudiante_id) AS estudiantes,
                    COUNT(DISTINCT a.id) AS asignaciones,
                    COUNT(DISTINCT i.id) AS intentos,
                    ROUND(AVG(i.nota_numerica), 1) AS promedio,
                    SUM(CASE WHEN i.estado = 'Aprobado' THEN 1 ELSE 0 END) AS aprobados,
                    SUM(CASE WHEN i.estado IN ('Aprobado', 'Rechazado') THEN 1 ELSE 0 END) AS evaluados
                FROM secciones s
                INNER JOIN profesores pr ON pr.id = s.profesor_id
                INNER JOIN personas pe   ON pe.id = pr.persona_id
                LEFT JOIN inscripciones ins ON ins.seccion_id = s.id
                LEFT JOIN sim_caso_asignaciones a ON a.estudiante_id = ins.estudiante_id
                LEFT JOIN sim_caso_configs cfg ON cfg.id = a.config_id AND cfg.profesor_id = pr.id
                LEFT JOIN sim_intentos i ON i.asignacion_id = a.id
                GROUP BY s.id, s.nombre, pe.nombres, pe.apellidos
                ORDER BY s.nombre ASC
            ");
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            // Calcular tasa de aprobación por fila
            foreach ($rows as &$r) {
                $ev = (int) ($r['evaluados'] ?? 0);
                $r['tasa'] = $ev > 0 ? (int) round(((int)$r['aprobados'] / $ev) * 100) : 0;
                $r['promedio'] = $r['promedio'] !== null ? (float) $r['promedio'] : null;
            }
            return $rows;
        } catch (\Throwable $e) {
            error_log('[AdminReportesModel::getRendimientoPorSeccion] ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Distribución de notas numéricas y cualitativas (global).
     */
    public function getDistribucionNotas(): array
    {
        $defaults = [
            'numericas' => ['0 – 5' => 0, '6 – 10' => 0, '11 – 15' => 0, '16 – 20' => 0, 'total' => 0],
            'cualitativas' => [],
        ];
        try {
            $num = $this->db->query("
                SELECT
                    SUM(CASE WHEN nota_numerica BETWEEN 0 AND 5 THEN 1 ELSE 0 END) AS r_0_5,
                    SUM(CASE WHEN nota_numerica BETWEEN 6 AND 10 THEN 1 ELSE 0 END) AS r_6_10,
                    SUM(CASE WHEN nota_numerica BETWEEN 11 AND 15 THEN 1 ELSE 0 END) AS r_11_15,
                    SUM(CASE WHEN nota_numerica BETWEEN 16 AND 20 THEN 1 ELSE 0 END) AS r_16_20,
                    COUNT(nota_numerica) AS total_numericas
                FROM sim_intentos
                WHERE nota_numerica IS NOT NULL
            ")->fetch(\PDO::FETCH_ASSOC);

            $cual = $this->db->query("
                SELECT nota_cualitativa, COUNT(*) AS total
                FROM sim_intentos
                WHERE nota_cualitativa IS NOT NULL
                GROUP BY nota_cualitativa
            ")->fetchAll(\PDO::FETCH_ASSOC);

            $cualitativas = [];
            foreach ($cual as $r) {
                $cualitativas[$r['nota_cualitativa']] = (int) $r['total'];
            }

            // Promedio global
            $promedio = $this->db->query("
                SELECT AVG(nota_numerica) FROM sim_intentos WHERE nota_numerica IS NOT NULL
            ")->fetchColumn();

            return [
                'numericas' => [
                    '0 – 5'   => (int) ($num['r_0_5'] ?? 0),
                    '6 – 10'  => (int) ($num['r_6_10'] ?? 0),
                    '11 – 15' => (int) ($num['r_11_15'] ?? 0),
                    '16 – 20' => (int) ($num['r_16_20'] ?? 0),
                    'total'   => (int) ($num['total_numericas'] ?? 0),
                ],
                'cualitativas' => $cualitativas,
                'promedio'     => $promedio !== false ? round((float) $promedio, 1) : null,
            ];
        } catch (\Throwable $e) {
            error_log('[AdminReportesModel::getDistribucionNotas] ' . $e->getMessage());
            $defaults['promedio'] = null;
            return $defaults;
        }
    }

    /**
     * Top 5 estudiantes por promedio o por cantidad de intentos.
     * @param string $tipo 'promedio' | 'activos'
     */
    public function getTopEstudiantes(string $tipo = 'promedio'): array
    {
        try {
            $orderBy = $tipo === 'activos' ? 'intentos DESC, promedio DESC' : 'promedio DESC, intentos DESC';
            $stmt = $this->db->query("
                SELECT
                    CONCAT(pe.nombres, ' ', pe.apellidos) AS nombre,
                    COUNT(i.id) AS intentos,
                    ROUND(AVG(i.nota_numerica), 1) AS promedio
                FROM estudiantes est
                INNER JOIN personas pe ON pe.id = est.persona_id
                INNER JOIN sim_caso_asignaciones a ON a.estudiante_id = est.id
                INNER JOIN sim_intentos i ON i.asignacion_id = a.id
                WHERE (i.nota_numerica IS NOT NULL OR i.nota_cualitativa IS NOT NULL)
                GROUP BY est.id, pe.nombres, pe.apellidos
                HAVING intentos > 0
                ORDER BY {$orderBy}
                LIMIT 5
            ");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log('[AdminReportesModel::getTopEstudiantes] ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Promedio de notas por sección para gráfico de barras.
     */
    public function getPromedioPorSeccion(): array
    {
        try {
            $stmt = $this->db->query("
                SELECT
                    s.nombre AS seccion,
                    ROUND(AVG(i.nota_numerica), 1) AS promedio
                FROM secciones s
                INNER JOIN inscripciones ins ON ins.seccion_id = s.id
                INNER JOIN sim_caso_asignaciones a ON a.estudiante_id = ins.estudiante_id
                INNER JOIN sim_intentos i ON i.asignacion_id = a.id
                WHERE i.nota_numerica IS NOT NULL
                GROUP BY s.id, s.nombre
                ORDER BY promedio DESC
            ");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log('[AdminReportesModel::getPromedioPorSeccion] ' . $e->getMessage());
            return [];
        }
    }
}
