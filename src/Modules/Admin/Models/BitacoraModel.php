<?php
declare(strict_types=1);

namespace App\Modules\Admin\Models;

use App\Core\DB;

class BitacoraModel
{
    /**
     * Obtiene todos los eventos de la bitácora, ordenados por fecha descendente.
     * Incluye JOIN con tipos_eventos para datos legibles.
     *
     * @return array
     */
    public static function getAll(): array
    {
        try {
            $db = DB::connect();

            $sql = "SELECT 
                        ba.id,
                        COALESCE(ba.attempted_email, 'Sistema') AS email,
                        te.descripcion AS evento,
                        te.nivel_riesgo,
                        ba.modulo,
                        ba.entidad_tipo,
                        ba.entidad_id,
                        ba.detalle,
                        ba.ip_address,
                        ba.created_at
                    FROM bitacora_eventos ba
                    LEFT JOIN tipos_eventos te ON ba.tipo_evento_id = te.id
                    ORDER BY ba.created_at DESC";

            $stmt = $db->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log('[BitacoraModel::getAll] ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene la lista de emails únicos que tienen registros en la bitácora.
     *
     * @return string[]
     */
    public static function getUniqueEmails(): array
    {
        try {
            $db = DB::connect();

            $sql = "SELECT DISTINCT COALESCE(ba.attempted_email, 'Sistema') AS email
                    FROM bitacora_eventos ba
                    ORDER BY email ASC";

            $stmt = $db->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_COLUMN);
        } catch (\Throwable $e) {
            error_log('[BitacoraModel::getUniqueEmails] ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene los tipos de eventos disponibles.
     *
     * @return array
     */
    public static function getTiposEventos(): array
    {
        try {
            $db = DB::connect();

            $sql = "SELECT id, descripcion, nivel_riesgo FROM tipos_eventos ORDER BY id ASC";

            $stmt = $db->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log('[BitacoraModel::getTiposEventos] ' . $e->getMessage());
            return [];
        }
    }
}
