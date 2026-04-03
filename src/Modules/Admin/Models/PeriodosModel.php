<?php
declare(strict_types=1);

namespace App\Modules\Admin\Models;

use App\Core\DB;

/**
 * Modelo CRUD para períodos académicos.
 */
class PeriodosModel
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = DB::connect();
    }

    /**
     * Obtiene todos los períodos con conteo de estudiantes inscritos y secciones.
     */
    public function getAll(): array
    {
        try {
            $stmt = $this->db->query("
                SELECT p.id, p.nombre, p.fecha_inicio, p.fecha_fin, p.activo,
                       p.created_at, p.updated_at,
                       (SELECT COUNT(*) FROM secciones s WHERE s.periodo_id = p.id AND s.deleted_at IS NULL) AS total_secciones,
                       (SELECT COUNT(*) FROM inscripciones i
                        INNER JOIN secciones s2 ON s2.id = i.seccion_id
                        WHERE s2.periodo_id = p.id AND s2.deleted_at IS NULL) AS total_inscritos
                FROM periodos p
                ORDER BY p.activo DESC, p.fecha_inicio DESC
            ");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log('[PeriodosModel::getAll] ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Verifica si existe un período activo (opcionalmente excluyendo un ID).
     */
    public function existeActivo(?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM periodos WHERE activo = 1";
        $params = [];

        if ($excludeId !== null) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Crea un nuevo período académico.
     * Solo permite crear si no hay otro período activo.
     */
    public function create(string $nombre, string $fechaInicio, string $fechaFin): array
    {
        try {
            if ($this->existeActivo()) {
                return [
                    'success' => false,
                    'message' => 'Ya existe un período activo. Ciérrelo antes de crear uno nuevo.'
                ];
            }

            // Verificar nombre duplicado
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM periodos WHERE nombre = ?");
            $stmt->execute([$nombre]);
            if ((int) $stmt->fetchColumn() > 0) {
                return ['success' => false, 'message' => 'Ya existe un período con ese código.'];
            }

            $stmt = $this->db->prepare(
                "INSERT INTO periodos (nombre, fecha_inicio, fecha_fin, activo) VALUES (?, ?, ?, 1)"
            );
            $stmt->execute([$nombre, $fechaInicio, $fechaFin]);

            return [
                'success' => true,
                'message' => 'Período creado exitosamente.',
                'id' => (int) $this->db->lastInsertId()
            ];
        } catch (\Throwable $e) {
            error_log('[PeriodosModel::create] ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error al crear el período.'];
        }
    }

    /**
     * Actualiza nombre y fechas de un período.
     * El admin tiene control total: puede editar cualquier período sin restricciones de fecha.
     */
    public function update(int $id, string $nombre, string $fechaInicio, string $fechaFin): array
    {
        try {
            // Verificar nombre duplicado (excluyendo el propio)
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM periodos WHERE nombre = ? AND id != ?");
            $stmt->execute([$nombre, $id]);
            if ((int) $stmt->fetchColumn() > 0) {
                return ['success' => false, 'message' => 'Ya existe otro período con ese código.'];
            }

            $stmt = $this->db->prepare(
                "UPDATE periodos SET nombre = ?, fecha_inicio = ?, fecha_fin = ? WHERE id = ?"
            );
            $stmt->execute([$nombre, $fechaInicio, $fechaFin, $id]);

            return ['success' => true, 'message' => 'Período actualizado exitosamente.'];
        } catch (\Throwable $e) {
            error_log('[PeriodosModel::update] ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error al actualizar el período.'];
        }
    }

    /**
     * Activa o desactiva un período.
     * Al activar: desactiva cualquier otro período activo primero (transacción).
     * El admin puede reactivar cualquier período sin restricciones de fecha.
     */
    public function toggleActivo(int $id, bool $activar): array
    {
        $this->db->beginTransaction();
        try {
            if ($activar) {
                // Desactivar cualquier otro período activo
                $this->db->prepare("UPDATE periodos SET activo = 0 WHERE activo = 1 AND id != ?")
                    ->execute([$id]);

                // Activar el período solicitado
                $this->db->prepare("UPDATE periodos SET activo = 1 WHERE id = ?")
                    ->execute([$id]);
            } else {
                // Cerrar el período
                $this->db->prepare("UPDATE periodos SET activo = 0 WHERE id = ?")
                    ->execute([$id]);
            }

            $this->db->commit();

            $accion = $activar ? 'activado' : 'cerrado';
            return ['success' => true, 'message' => "Período {$accion} exitosamente."];
        } catch (\Throwable $e) {
            $this->db->rollBack();
            error_log('[PeriodosModel::toggleActivo] ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error al cambiar el estado del período.'];
        }
    }
}
