<?php
declare(strict_types=1);

namespace App\Modules\Professor\Models\Asignaciones;

use App\Core\DB;
use App\Modules\Professor\Helpers\ConfigRulesHelper;
use PDO;

/**
 * Modelo CRUD para configuraciones de asignaciones y estudiantes vinculados.
 */
class AsignacionesModel
{
    private PDO $db;
    private ConfigRulesHelper $rules;

    public function __construct()
    {
        $this->db = DB::connect();
        $this->rules = new ConfigRulesHelper();
    }

    /* =========================================================
     *  READ — Configs con estudiantes + reglas
     * ========================================================= */

    /**
     * Devuelve todas las configs del caso con estudiantes y reglas de edición.
     */
    public function getConfigsConReglas(int $casoId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM sim_caso_configs WHERE caso_id = :cid ORDER BY id ASC"
        );
        $stmt->execute(['cid' => $casoId]);
        $configs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($configs as &$cfg) {
            // Estudiantes
            $stmt2 = $this->db->prepare(
                "SELECT a.id AS asignacion_id, a.estudiante_id, a.estado, a.created_at,
                        per.nombres, per.apellidos, per.cedula
                 FROM sim_caso_asignaciones a
                 JOIN estudiantes est ON a.estudiante_id = est.id
                 JOIN personas per ON est.persona_id = per.id
                 WHERE a.config_id = :cid
                 ORDER BY per.apellidos ASC, per.nombres ASC"
            );
            $stmt2->execute(['cid' => $cfg['id']]);
            $cfg['estudiantes'] = $stmt2->fetchAll(PDO::FETCH_ASSOC);

            // Reglas de edición
            $cfg['rules'] = $this->rules->getEditRules((int) $cfg['id']);
        }
        unset($cfg);

        return $configs;
    }

    /* =========================================================
     *  CREATE — Nueva config + estudiantes
     * ========================================================= */

    public function createConfig(int $casoId, int $profesorId, array $data): array
    {
        $this->db->beginTransaction();
        try {
            // Insertar config
            $stmt = $this->db->prepare(
                "INSERT INTO sim_caso_configs (caso_id, profesor_id, modalidad, max_intentos, fecha_apertura, fecha_limite, status)
                 VALUES (:caso_id, :prof_id, :modalidad, :max, :apertura, :limite, 'Activo')"
            );
            $stmt->execute([
                'caso_id' => $casoId,
                'prof_id' => $profesorId,
                'modalidad' => $data['modalidad'],
                'max' => (int) ($data['max_intentos'] ?? 0),
                'apertura' => !empty($data['fecha_apertura']) ? $data['fecha_apertura'] : null,
                'limite' => !empty($data['fecha_limite']) ? $data['fecha_limite'] : null,
            ]);
            $configId = (int) $this->db->lastInsertId();

            // Insertar estudiantes
            $estudianteIds = $data['estudiante_ids'] ?? [];
            $duplicados = [];
            foreach ($estudianteIds as $estId) {
                $estId = (int) $estId;
                // Verificar duplicado en el caso
                $yaEn = $this->rules->estudianteYaAsignadoEnCaso($casoId, $estId);
                if ($yaEn !== null) {
                    $duplicados[] = $estId;
                    continue;
                }
                $ins = $this->db->prepare(
                    "INSERT INTO sim_caso_asignaciones (config_id, estudiante_id, estado)
                     VALUES (:cid, :eid, 'Pendiente')"
                );
                $ins->execute(['cid' => $configId, 'eid' => $estId]);
            }

            $this->db->commit();
            return [
                'ok' => true,
                'config_id' => $configId,
                'duplicados' => $duplicados,
            ];
        } catch (\Throwable $e) {
            $this->db->rollBack();
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    /* =========================================================
     *  UPDATE — Editar config (con reglas)
     * ========================================================= */

    public function updateConfig(int $configId, array $data): array
    {
        $rules = $this->rules->getEditRules($configId);

        // Validar modalidad
        if (isset($data['modalidad']) && !$rules['modalidad_editable']) {
            return ['ok' => false, 'error' => 'No se puede cambiar la modalidad — ya existen intentos registrados.'];
        }

        // Validar max_intentos
        if (isset($data['max_intentos'])) {
            $nuevo = (int) $data['max_intentos'];
            if ($nuevo !== 0 && $nuevo < $rules['min_intentos_permitido']) {
                return [
                    'ok' => false,
                    'error' => "No se puede bajar a {$nuevo} intentos — ya hay estudiantes con {$rules['min_intentos_permitido']} intentos usados."
                ];
            }
        }

        // Construir SET dinámico
        $sets = [];
        $params = ['id' => $configId];

        if (isset($data['modalidad'])) {
            $sets[] = 'modalidad = :modalidad';
            $params['modalidad'] = $data['modalidad'];
        }
        if (isset($data['max_intentos'])) {
            $sets[] = 'max_intentos = :max';
            $params['max'] = (int) $data['max_intentos'];
        }
        if (array_key_exists('fecha_apertura', $data)) {
            $sets[] = 'fecha_apertura = :apertura';
            $params['apertura'] = !empty($data['fecha_apertura']) ? $data['fecha_apertura'] : null;
        }
        if (array_key_exists('fecha_limite', $data)) {
            $sets[] = 'fecha_limite = :limite';
            $params['limite'] = !empty($data['fecha_limite']) ? $data['fecha_limite'] : null;
        }

        if (empty($sets)) {
            return ['ok' => true, 'message' => 'Nada que actualizar.'];
        }

        $sql = "UPDATE sim_caso_configs SET " . implode(', ', $sets) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return ['ok' => true, 'rules' => $rules];
    }

    /* =========================================================
     *  DELETE / DEACTIVATE — Config
     * ========================================================= */

    public function deleteOrDeactivateConfig(int $configId): array
    {
        $rules = $this->rules->getEditRules($configId);

        if ($rules['puede_eliminar']) {
            // Sin intentos: eliminar todo
            $this->db->beginTransaction();
            try {
                $this->db->prepare("DELETE FROM sim_caso_asignaciones WHERE config_id = :cid")
                    ->execute(['cid' => $configId]);
                $this->db->prepare("DELETE FROM sim_caso_configs WHERE id = :id")
                    ->execute(['id' => $configId]);
                $this->db->commit();
                return ['ok' => true, 'action' => 'deleted'];
            } catch (\Throwable $e) {
                $this->db->rollBack();
                return ['ok' => false, 'error' => $e->getMessage()];
            }
        } else {
            // Con intentos: inactivar
            $this->db->prepare("UPDATE sim_caso_configs SET status = 'Inactivo' WHERE id = :id")
                ->execute(['id' => $configId]);
            return ['ok' => true, 'action' => 'deactivated'];
        }
    }

    /* =========================================================
     *  AGREGAR estudiantes a config existente
     * ========================================================= */

    public function addEstudiantes(int $configId, array $estudianteIds): array
    {
        // Obtener caso_id de la config
        $stmt = $this->db->prepare("SELECT caso_id FROM sim_caso_configs WHERE id = :id");
        $stmt->execute(['id' => $configId]);
        $cfg = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$cfg)
            return ['ok' => false, 'error' => 'Config no encontrada.'];

        $casoId = (int) $cfg['caso_id'];
        $agregados = 0;
        $duplicados = [];

        foreach ($estudianteIds as $estId) {
            $estId = (int) $estId;
            $yaEn = $this->rules->estudianteYaAsignadoEnCaso($casoId, $estId);
            if ($yaEn !== null) {
                $duplicados[] = $estId;
                continue;
            }
            $ins = $this->db->prepare(
                "INSERT INTO sim_caso_asignaciones (config_id, estudiante_id, estado)
                 VALUES (:cid, :eid, 'Pendiente')"
            );
            $ins->execute(['cid' => $configId, 'eid' => $estId]);
            $agregados++;
        }

        return ['ok' => true, 'agregados' => $agregados, 'duplicados' => $duplicados];
    }

    /* =========================================================
     *  QUITAR / DESACTIVAR estudiante individual
     * ========================================================= */

    public function removeOrDeactivateEstudiante(int $asignacionId): array
    {
        // Verificar si tiene intentos
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) AS cnt FROM sim_intentos WHERE asignacion_id = :aid"
        );
        $stmt->execute(['aid' => $asignacionId]);
        $cnt = (int) $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];

        if ($cnt === 0) {
            // Sin intentos: eliminar
            $this->db->prepare("DELETE FROM sim_caso_asignaciones WHERE id = :id")
                ->execute(['id' => $asignacionId]);
            return ['ok' => true, 'action' => 'deleted'];
        } else {
            // Con intentos: inactivar
            $this->db->prepare("UPDATE sim_caso_asignaciones SET estado = 'Inactivo' WHERE id = :id")
                ->execute(['id' => $asignacionId]);
            return ['ok' => true, 'action' => 'deactivated'];
        }
    }

    /* =========================================================
     *  Estudiantes disponibles para asignar
     * ========================================================= */

    /**
     * Devuelve estudiantes de secciones del profesor que no están
     * asignados activamente a ninguna config del caso dado.
     */
    public function getEstudiantesDisponibles(int $casoId, int $profesorId): array
    {
        $sql = "SELECT est.id AS estudiante_id, per.nombres, per.apellidos, per.cedula,
                       sec.nombre AS seccion_nombre
                FROM estudiantes est
                JOIN personas per ON est.persona_id = per.id
                JOIN inscripciones ins ON ins.estudiante_id = est.id
                JOIN secciones sec ON ins.seccion_id = sec.id
                WHERE sec.profesor_id = :prof_id
                  AND est.id NOT IN (
                      SELECT a.estudiante_id FROM sim_caso_asignaciones a
                      JOIN sim_caso_configs c ON a.config_id = c.id
                      WHERE c.caso_id = :caso_id AND a.estado != 'Inactivo' AND c.status = 'Activo'
                  )
                ORDER BY sec.nombre, per.apellidos, per.nombres";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['prof_id' => $profesorId, 'caso_id' => $casoId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
