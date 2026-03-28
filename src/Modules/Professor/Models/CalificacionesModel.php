<?php
declare(strict_types=1);

namespace App\Modules\Professor\Models;

use App\Core\DB;
use PDO;

/**
 * Modelo de Calificaciones — sábana de notas.
 * Construye la tabla cruzada Estudiantes × Casos con sus notas.
 */
class CalificacionesModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = DB::connect();
    }

    /**
     * Secciones del profesor (para el filtro).
     */
    public function getSecciones(int $profesorId): array
    {
        $stmt = $this->db->prepare("
            SELECT s.id, s.nombre
            FROM secciones s
            WHERE s.profesor_id = :prof_id
            ORDER BY s.nombre ASC
        ");
        $stmt->execute(['prof_id' => $profesorId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Sábana de notas para una sección.
     * Retorna {estudiantes, casos, stats}.
     *
     * Cada estudiante tiene un array de notas indexado por config_id.
     * Cada caso incluye su tipo_calificacion para saber cómo mostrar la nota.
     */
    public function getSabana(int $profesorId, int $seccionId): array
    {
        // 1) Casos (configs) asignados a estudiantes de esta sección
        $casosSql = "
            SELECT DISTINCT
                cfg.id AS config_id,
                ce.id AS caso_id,
                ce.titulo,
                cfg.tipo_calificacion,
                cfg.max_intentos
            FROM sim_caso_configs cfg
            INNER JOIN sim_casos_estudios ce ON ce.id = cfg.caso_id
            INNER JOIN sim_caso_asignaciones a ON a.config_id = cfg.id
            INNER JOIN inscripciones ins ON ins.estudiante_id = a.estudiante_id
            WHERE cfg.profesor_id = :prof_id
              AND ins.seccion_id  = :sec_id
              AND cfg.status != 'Inactivo'
            ORDER BY ce.titulo ASC
        ";
        $stmt = $this->db->prepare($casosSql);
        $stmt->execute(['prof_id' => $profesorId, 'sec_id' => $seccionId]);
        $casos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($casos)) {
            return ['estudiantes' => [], 'casos' => [], 'stats' => ['total' => 0, 'calificados' => 0, 'pendientes' => 0]];
        }

        $configIds = array_column($casos, 'config_id');
        $placeholders = implode(',', array_fill(0, count($configIds), '?'));

        // 2) Estudiantes de esta sección que tienen asignaciones en estos configs
        $estSql = "
            SELECT DISTINCT
                est.id AS estudiante_id,
                pe.nombres,
                pe.apellidos,
                pe.cedula,
                pe.nacionalidad
            FROM estudiantes est
            INNER JOIN personas pe ON pe.id = est.persona_id
            INNER JOIN inscripciones ins ON ins.estudiante_id = est.id
            INNER JOIN sim_caso_asignaciones a ON a.estudiante_id = est.id
            WHERE ins.seccion_id = ?
              AND a.config_id IN ({$placeholders})
            ORDER BY pe.apellidos ASC, pe.nombres ASC
        ";
        $params = array_merge([$seccionId], $configIds);
        $stmt = $this->db->prepare($estSql);
        $stmt->execute($params);
        $estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($estudiantes)) {
            return ['estudiantes' => [], 'casos' => $casos, 'stats' => ['total' => 0, 'calificados' => 0, 'pendientes' => 0]];
        }

        // 3) Notas: mejor intento calificado por estudiante × config
        //    Para numérica: la nota más alta. Para cualitativa: Aprobado > Reprobado.
        $notasSql = "
            SELECT
                a.estudiante_id,
                a.config_id,
                i.nota_numerica,
                i.nota_cualitativa,
                i.estado AS intento_estado,
                i.id AS intento_id,
                i.numero_intento
            FROM sim_intentos i
            INNER JOIN sim_caso_asignaciones a ON a.id = i.asignacion_id
            WHERE a.config_id IN ({$placeholders})
              AND i.estado != 'En_Progreso'
              AND i.estado != 'Cancelado'
            ORDER BY
                a.estudiante_id,
                a.config_id,
                CASE i.estado
                    WHEN 'Aprobado' THEN 1
                    WHEN 'Rechazado' THEN 2
                    WHEN 'Enviado' THEN 3
                    ELSE 4
                END,
                i.nota_numerica DESC
        ";
        $stmt = $this->db->prepare($notasSql);
        $stmt->execute($configIds);
        $allNotas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Agrupar: mejor intento por (estudiante, config)
        $notasMap = []; // [est_id_config_id] => {nota_numerica, nota_cualitativa, estado, intento_id}
        foreach ($allNotas as $row) {
            $key = $row['estudiante_id'] . '_' . $row['config_id'];
            if (!isset($notasMap[$key])) {
                $notasMap[$key] = $row;
            }
        }

        // 3b) Mapa de asignaciones: saber quién está asignado a cada config
        $asigSql = "
            SELECT estudiante_id, config_id
            FROM sim_caso_asignaciones
            WHERE config_id IN ({$placeholders})
        ";
        $stmt = $this->db->prepare($asigSql);
        $stmt->execute($configIds);
        $asigRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $asigMap = [];
        foreach ($asigRows as $ar) {
            $asigMap[$ar['estudiante_id'] . '_' . $ar['config_id']] = true;
        }

        // 4) Ensamblar: para cada estudiante, agregar sus notas
        $calificados = 0;
        $pendientes = 0;

        foreach ($estudiantes as &$est) {
            $est['notas'] = [];
            $notasNum = [];
            foreach ($casos as $caso) {
                $key = $est['estudiante_id'] . '_' . $caso['config_id'];
                $isAsignado = isset($asigMap[$key]);
                $info = $notasMap[$key] ?? null;

                if ($info && ($info['intento_estado'] === 'Aprobado' || $info['intento_estado'] === 'Rechazado')) {
                    $calificados++;
                    $est['notas'][$caso['config_id']] = [
                        'tipo'            => $caso['tipo_calificacion'],
                        'nota_numerica'   => $info['nota_numerica'],
                        'nota_cualitativa' => $info['nota_cualitativa'],
                        'estado'          => $info['intento_estado'],
                        'intento_id'      => $info['intento_id'],
                    ];
                    if ($info['nota_numerica'] !== null) {
                        $notasNum[] = (float) $info['nota_numerica'];
                    }
                } elseif ($info && $info['intento_estado'] === 'Enviado') {
                    $pendientes++;
                    $est['notas'][$caso['config_id']] = [
                        'tipo'            => $caso['tipo_calificacion'],
                        'nota_numerica'   => null,
                        'nota_cualitativa' => null,
                        'estado'          => 'Enviado',
                        'intento_id'      => $info['intento_id'],
                    ];
                } elseif ($isAsignado) {
                    // Asignado pero sin intento enviado
                    $est['notas'][$caso['config_id']] = [
                        'tipo'   => $caso['tipo_calificacion'],
                        'estado' => 'sin_intento',
                    ];
                } else {
                    // No asignado a este caso
                    $est['notas'][$caso['config_id']] = null;
                }
            }
            $est['promedio'] = count($notasNum) > 0 ? round(array_sum($notasNum) / count($notasNum), 1) : null;
        }
        unset($est);

        return [
            'estudiantes' => $estudiantes,
            'casos'       => $casos,
            'stats'       => [
                'total'       => count($estudiantes),
                'calificados' => $calificados,
                'pendientes'  => $pendientes,
            ],
        ];
    }
}
