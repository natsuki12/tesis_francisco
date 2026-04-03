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
                   AND a.estado != 'Inactivo'
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
                "INSERT INTO sim_caso_configs (caso_id, profesor_id, nombre, modalidad, max_intentos, tipo_calificacion, fecha_apertura, fecha_limite, status)
                 VALUES (:caso_id, :prof_id, :nombre, :modalidad, :max, :tipo_calif, :apertura, :limite, 'Activo')"
            );
            $tipoCalif = in_array($data['tipo_calificacion'] ?? '', ['numerica', 'aprobado_reprobado'])
                ? $data['tipo_calificacion']
                : 'aprobado_reprobado';
            $maxIntentos = max(0, min(100, (int) ($data['max_intentos'] ?? 0)));
            $nombre = !empty(trim($data['nombre'] ?? '')) ? trim($data['nombre']) : null;
            $stmt->execute([
                'caso_id' => $casoId,
                'prof_id' => $profesorId,
                'nombre' => $nombre,
                'modalidad' => $data['modalidad'],
                'max' => $maxIntentos,
                'tipo_calif' => $tipoCalif,
                'apertura' => !empty($data['fecha_apertura']) ? $data['fecha_apertura'] : null,
                'limite' => !empty($data['fecha_limite']) ? $data['fecha_limite'] : null,
            ]);
            $configId = (int) $this->db->lastInsertId();

            // Insertar estudiantes (la config es nueva, no puede haber duplicados)
            $estudianteIds = $data['estudiante_ids'] ?? [];
            foreach ($estudianteIds as $estId) {
                $estId = (int) $estId;
                try {
                    $ins = $this->db->prepare(
                        "INSERT INTO sim_caso_asignaciones (config_id, estudiante_id, estado)
                         VALUES (:cid, :eid, 'Pendiente')"
                    );
                    $ins->execute(['cid' => $configId, 'eid' => $estId]);
                } catch (\Throwable $e) {
                    // Silently skip if duplicate (e.g. same student sent twice in array)
                    error_log('[AsignacionesModel::createConfig] Skip estudiante ' . $estId . ': ' . $e->getMessage());
                }
            }

            $this->db->commit();
            return [
                'ok' => true,
                'config_id' => $configId,
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
        try {
            $rules = $this->rules->getEditRules($configId);

            // Obtener estado actual de DB
            $stmt = $this->db->prepare("SELECT modalidad, max_intentos, tipo_calificacion FROM sim_caso_configs WHERE id = :id");
            $stmt->execute(['id' => $configId]);
            $currentConfig = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$currentConfig) {
                return ['ok' => false, 'error' => 'Configuración no encontrada.'];
            }

            // Validar modalidad (solo falla si muta el valor y no es editable)
            if (isset($data['modalidad']) && $data['modalidad'] !== $currentConfig['modalidad']) {
                if (!$rules['modalidad_editable']) {
                    return ['ok' => false, 'error' => 'No se puede cambiar la modalidad — ya existen intentos registrados.'];
                }
            }

            // Validar tipo_calificacion (solo falla si muta y no es editable)
            if (isset($data['tipo_calificacion']) && $data['tipo_calificacion'] !== $currentConfig['tipo_calificacion']) {
                if (!$rules['tipo_calif_editable']) {
                    return ['ok' => false, 'error' => 'No se puede cambiar el tipo de calificación — ya existen intentos registrados.'];
                }
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

            if (array_key_exists('nombre', $data)) {
                $sets[] = 'nombre = :nombre';
                $trimmed = trim($data['nombre'] ?? '');
                $params['nombre'] = $trimmed !== '' ? $trimmed : null;
            }
            if (isset($data['modalidad'])) {
                $sets[] = 'modalidad = :modalidad';
                $params['modalidad'] = $data['modalidad'];
            }
            if (isset($data['max_intentos'])) {
                $sets[] = 'max_intentos = :max';
                $params['max'] = max(0, min(100, (int) $data['max_intentos']));
            }
            if (array_key_exists('fecha_apertura', $data)) {
                $sets[] = 'fecha_apertura = :apertura';
                $params['apertura'] = !empty($data['fecha_apertura']) ? $data['fecha_apertura'] : null;
            }
            if (array_key_exists('fecha_limite', $data)) {
                $sets[] = 'fecha_limite = :limite';
                $params['limite'] = !empty($data['fecha_limite']) ? $data['fecha_limite'] : null;
            }
            if (isset($data['status']) && in_array($data['status'], ['Activo', 'Inactivo'])) {
                $sets[] = 'status = :status';
                $params['status'] = $data['status'];
            }
            if (isset($data['tipo_calificacion']) && in_array($data['tipo_calificacion'], ['numerica', 'aprobado_reprobado'])) {
                $sets[] = 'tipo_calificacion = :tipo_calif';
                $params['tipo_calif'] = $data['tipo_calificacion'];
            }

            if (empty($sets)) {
                return ['ok' => true, 'message' => 'Nada que actualizar.'];
            }

            $sql = "UPDATE sim_caso_configs SET " . implode(', ', $sets) . " WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return ['ok' => true, 'rules' => $rules];

        } catch (\Throwable $e) {
            error_log('[AsignacionesModel::updateConfig] Error DB: ' . $e->getMessage());
            return ['ok' => false, 'error' => 'Ocurrió un error interno al actualizar la configuración de asignación.'];
        }
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

        $agregados = 0;
        $duplicados = [];

        foreach ($estudianteIds as $estId) {
            $estId = (int) $estId;
            // Verificar duplicado dentro de ESTA config (no a nivel de caso)
            if ($this->estudianteYaEnConfig($configId, $estId)) {
                $duplicados[] = $estId;
                continue;
            }
            try {
                $ins = $this->db->prepare(
                    "INSERT INTO sim_caso_asignaciones (config_id, estudiante_id, estado)
                     VALUES (:cid, :eid, 'Pendiente')"
                );
                $ins->execute(['cid' => $configId, 'eid' => $estId]);
                $agregados++;
            } catch (\Throwable $e) {
                error_log('[AsignacionesModel::addEstudiantes] Error: ' . $e->getMessage());
                $duplicados[] = $estId;
            }
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
     * Devuelve todos los estudiantes de secciones del profesor (período activo).
     * Usa GROUP BY para evitar duplicados si un estudiante está en múltiples secciones.
     * El frontend se encarga de excluir los ya seleccionados en el modal.
     */
    public function getEstudiantesDisponibles(int $profesorId): array
    {
        try {
            $sql = "SELECT est.id AS estudiante_id, per.nombres, per.apellidos, per.cedula,
                           GROUP_CONCAT(DISTINCT sec.nombre ORDER BY sec.nombre SEPARATOR ', ') AS seccion_nombre,
                           u.email
                    FROM estudiantes est
                    JOIN personas per ON est.persona_id = per.id
                    JOIN users u ON u.persona_id = est.persona_id
                    JOIN inscripciones ins ON ins.estudiante_id = est.id
                    JOIN secciones sec ON ins.seccion_id = sec.id
                    JOIN periodos p ON sec.periodo_id = p.id AND p.activo = 1
                    WHERE sec.profesor_id = :prof_id
                      AND sec.deleted_at IS NULL
                    GROUP BY est.id, per.nombres, per.apellidos, per.cedula, u.email
                    ORDER BY per.apellidos, per.nombres";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['prof_id' => $profesorId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log('[AsignacionesModel::getEstudiantesDisponibles] Error: ' . $e->getMessage());
            return [];
        }
    }

    /* =========================================================
     *  Verificar propiedad del caso
     * ========================================================= */

    public function casoPertenece(int $casoId, int $profesorId): bool
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT 1 FROM sim_casos WHERE id = :cid AND profesor_id = :pid LIMIT 1"
            );
            $stmt->execute(['cid' => $casoId, 'pid' => $profesorId]);
            return (bool) $stmt->fetch();
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Verifica que un configId pertenece al profesor dado.
     */
    public function configPertenece(int $configId, int $profesorId): bool
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT 1 FROM sim_caso_configs WHERE id = :cid AND profesor_id = :pid LIMIT 1"
            );
            $stmt->execute(['cid' => $configId, 'pid' => $profesorId]);
            return (bool) $stmt->fetch();
        } catch (\Throwable $e) {
            return false;
        }
    }

    /* =========================================================
     *  Helper: ¿Estudiante ya activo en esta config?
     * ========================================================= */

    private function estudianteYaEnConfig(int $configId, int $estudianteId): bool
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT 1 FROM sim_caso_asignaciones
                 WHERE config_id = :cid AND estudiante_id = :eid AND estado != 'Inactivo'
                 LIMIT 1"
            );
            $stmt->execute(['cid' => $configId, 'eid' => $estudianteId]);
            return (bool) $stmt->fetch();
        } catch (\Throwable $e) {
            error_log('[AsignacionesModel::estudianteYaEnConfig] Error: ' . $e->getMessage());
            return false;
        }
    }
}
