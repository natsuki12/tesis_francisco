<?php

declare(strict_types=1);

namespace App\Modules\Student\Models;

use App\Core\DB;
use PDO;

/**
 * Gestiona la creación, lectura y actualización de intentos
 * del estudiante sobre una asignación.
 */
class StudentAttemptModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = DB::connect();
    }

    // ─── Verificaciones ────────────────────────────────────

    /**
     * Valida que el estudiante puede iniciar un nuevo intento.
     * Retorna ['ok' => true] o ['ok' => false, 'razon' => '...']
     */
    public function verificarPuedeIniciar(int $asignacionId, int $estudianteId): array
    {
        // 1. Verificar ownership + estado asignación
        $sql = "
            SELECT
                a.id, a.estado AS asig_estado,
                cfg.max_intentos, cfg.fecha_limite, cfg.modalidad, cfg.status AS config_status
            FROM sim_caso_asignaciones a
            INNER JOIN sim_caso_configs cfg ON cfg.id = a.config_id
            WHERE a.id = :asig_id AND a.estudiante_id = :est_id
            LIMIT 1
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':asig_id', $asignacionId, PDO::PARAM_INT);
        $stmt->bindValue(':est_id', $estudianteId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return ['ok' => false, 'razon' => 'Asignación no encontrada.'];
        }

        if ($row['config_status'] !== 'Activo') {
            return ['ok' => false, 'razon' => 'Esta asignación no está activa.'];
        }

        if (in_array($row['asig_estado'], ['Vencido', 'Inactivo', 'Completado'], true)) {
            return ['ok' => false, 'razon' => 'Esta asignación ya no acepta nuevos intentos.'];
        }

        // 2. Fecha límite (aplica a todas las modalidades)
        if ($row['fecha_limite'] && strtotime($row['fecha_limite']) < time()) {
            return ['ok' => false, 'razon' => 'La fecha límite ha vencido.'];
        }

        // 3. Verificar intento activo (En_Progreso)
        $activo = $this->getIntentoActivo($asignacionId);
        if ($activo) {
            return ['ok' => false, 'razon' => 'Ya tienes un intento en progreso.', 'intento_activo' => $activo];
        }

        // 3b. Verificar si hay un intento pendiente de aprobación de RIF
        $pendienteRif = $this->getIntentoPendienteRif($asignacionId);
        if ($pendienteRif) {
            return ['ok' => false, 'razon' => 'Tienes un intento pendiente de aprobación del RIF Sucesoral por parte del profesor.'];
        }

        // 4. Contar intentos vs max_intentos
        $maxIntentos = (int) $row['max_intentos'];
        if ($maxIntentos > 0) {
            $count = $this->contarIntentos($asignacionId);
            if ($count >= $maxIntentos) {
                return ['ok' => false, 'razon' => 'Has alcanzado el máximo de intentos permitidos.'];
            }
        }

        return ['ok' => true, 'modalidad' => $row['modalidad']];
    }

    /**
     * Busca un intento con estado = 'En_Progreso' para la asignación.
     */
    public function getIntentoActivo(int $asignacionId): ?array
    {
        $sql = "
            SELECT id, numero_intento, paso_actual, pasos_completados, borrador_json, rif_sucesoral, usuario_seniat, password_rif
            FROM sim_intentos
            WHERE asignacion_id = :asig_id AND estado = 'En_Progreso'
            LIMIT 1
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':asig_id', $asignacionId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Busca un intento con estado = 'Pendiente_RIF' para la asignación.
     * Bloquea al estudiante de crear nuevos intentos mientras el profesor revisa.
     */
    public function getIntentoPendienteRif(int $asignacionId): ?array
    {
        $sql = "
            SELECT id, numero_intento
            FROM sim_intentos
            WHERE asignacion_id = :asig_id AND estado = 'Pendiente_RIF'
            LIMIT 1
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':asig_id', $asignacionId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Cuenta todos los intentos (de cualquier estado) de una asignación.
     */
    private function contarIntentos(int $asignacionId): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM sim_intentos WHERE asignacion_id = :asig_id");
        $stmt->bindValue(':asig_id', $asignacionId, PDO::PARAM_INT);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    // ─── Crear / Cargar ───────────────────────────────────

    /**
     * Obtiene la modalidad de la config asociada a una asignación.
     */
    public function getModalidadByAsignacion(int $asignacionId): ?string
    {
        $sql = "
            SELECT cfg.modalidad
            FROM sim_caso_asignaciones a
            INNER JOIN sim_caso_configs cfg ON cfg.id = a.config_id
            WHERE a.id = :asig_id
            LIMIT 1
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':asig_id', $asignacionId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn() ?: null;
    }

    /**
     * Crea un nuevo intento y lo retorna.
     */
    public function crearIntento(int $asignacionId): array
    {
        $nextNum = $this->contarIntentos($asignacionId) + 1;

        $sql = "
            INSERT INTO sim_intentos (asignacion_id, numero_intento, estado, paso_actual, pasos_completados, borrador_json)
            VALUES (:asig_id, :num, 'En_Progreso', 1, '', '{}')
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':asig_id', $asignacionId, PDO::PARAM_INT);
        $stmt->bindValue(':num', $nextNum, PDO::PARAM_INT);
        $stmt->execute();

        $intentoId = (int) $this->db->lastInsertId();

        // Log en sim_intento_estados
        $logSql = "INSERT INTO sim_intento_estados (intento_id, estado, comentario) VALUES (:id, 'En_Proceso', 'Intento iniciado')";
        $logStmt = $this->db->prepare($logSql);
        $logStmt->bindValue(':id', $intentoId, PDO::PARAM_INT);
        $logStmt->execute();

        return [
            'id' => $intentoId,
            'numero_intento' => $nextNum,
            'paso_actual' => 1,
            'pasos_completados' => '',
            'borrador_json' => '{}',
        ];
    }

    /**
     * Carga un intento con verificación de ownership.
     * Retorna null si no pertenece al estudiante o no existe.
     */
    public function getIntento(int $intentoId, int $estudianteId): ?array
    {
        $sql = "
            SELECT
                i.id,
                i.asignacion_id,
                i.numero_intento,
                i.estado,
                i.paso_actual,
                i.pasos_completados,
                i.borrador_json,
                i.submitted_at,

                ce.titulo AS caso_titulo,
                cfg.modalidad

            FROM sim_intentos i
            INNER JOIN sim_caso_asignaciones a   ON a.id  = i.asignacion_id
            INNER JOIN sim_caso_configs cfg      ON cfg.id = a.config_id
            INNER JOIN sim_casos_estudios ce     ON ce.id  = cfg.caso_id
            WHERE i.id = :int_id
              AND a.estudiante_id = :est_id
            LIMIT 1
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':int_id', $intentoId, PDO::PARAM_INT);
        $stmt->bindValue(':est_id', $estudianteId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    // ─── Guardar / Enviar / Cancelar ──────────────────────

    /**
     * Auto-save del borrador.
     */
    public function guardarBorrador(int $intentoId, string $json, int $pasoActual, string $pasosCompletados): bool
    {
        $sql = "
            UPDATE sim_intentos
            SET borrador_json     = :json,
                paso_actual       = :paso,
                pasos_completados = :pasos_comp,
                updated_at        = NOW()
            WHERE id = :id AND estado = 'En_Progreso'
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':json', $json);
        $stmt->bindValue(':paso', $pasoActual, PDO::PARAM_INT);
        $stmt->bindValue(':pasos_comp', $pasosCompletados);
        $stmt->bindValue(':id', $intentoId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    /**
     * Envía el intento (cambia estado a 'Enviado').
     */
    public function enviarIntento(int $intentoId): bool
    {
        // Verificar que la fecha límite no ha expirado
        $sql = "
            SELECT cfg.fecha_limite, cfg.modalidad
            FROM sim_intentos i
            INNER JOIN sim_caso_asignaciones a ON a.id = i.asignacion_id
            INNER JOIN sim_caso_configs cfg    ON cfg.id = a.config_id
            WHERE i.id = :id
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $intentoId, PDO::PARAM_INT);
        $stmt->execute();
        $config = $stmt->fetch(PDO::FETCH_ASSOC);

        $fueraFecha = 0;
        if ($config && $config['fecha_limite'] && strtotime($config['fecha_limite']) < time()) {
            $fueraFecha = 1;
        }

        $updateSql = "
            UPDATE sim_intentos
            SET estado        = 'Enviado',
                submitted_at  = NOW(),
                fuera_de_fecha = :fuera,
                updated_at    = NOW()
            WHERE id = :id AND estado = 'En_Progreso'
        ";
        $stmt = $this->db->prepare($updateSql);
        $stmt->bindValue(':fuera', $fueraFecha, PDO::PARAM_INT);
        $stmt->bindValue(':id', $intentoId, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            // Log
            $logSql = "INSERT INTO sim_intento_estados (intento_id, estado, comentario) VALUES (:id, 'Pendiente_Revision', 'Intento enviado por el estudiante')";
            $logStmt = $this->db->prepare($logSql);
            $logStmt->bindValue(':id', $intentoId, PDO::PARAM_INT);
            $logStmt->execute();
            return true;
        }

        return false;
    }

    /**
     * Cancela un intento activo.
     */
    public function cancelarIntento(int $intentoId): bool
    {
        $sql = "
            UPDATE sim_intentos
            SET estado = 'Cancelado', updated_at = NOW()
            WHERE id = :id AND estado = 'En_Progreso'
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $intentoId, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $logSql = "INSERT INTO sim_intento_estados (intento_id, estado, comentario) VALUES (:id, 'Cancelado', 'Cancelado por el estudiante')";
            $logStmt = $this->db->prepare($logSql);
            $logStmt->bindValue(':id', $intentoId, PDO::PARAM_INT);
            $logStmt->execute();
            return true;
        }

        return false;
    }
}
