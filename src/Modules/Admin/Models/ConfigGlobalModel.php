<?php
declare(strict_types=1);

namespace App\Modules\Admin\Models;

use App\Core\DB;

/**
 * Modelo genérico para la tabla configs_globales (patrón clave-valor).
 */
class ConfigGlobalModel
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = DB::connect();
    }

    /**
     * Obtiene todas las configuraciones de una categoría como array [clave => valor].
     */
    public function getByCategoria(string $categoria): array
    {
        $stmt = $this->db->prepare("SELECT clave, valor FROM configs_globales WHERE categoria = ?");
        $stmt->execute([$categoria]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $config = [];
        foreach ($rows as $row) {
            $config[$row['clave']] = $row['valor'];
        }
        return $config;
    }

    /**
     * Obtiene el valor de una clave específica.
     */
    public function get(string $clave): ?string
    {
        $stmt = $this->db->prepare("SELECT valor FROM configs_globales WHERE clave = ? LIMIT 1");
        $stmt->execute([$clave]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ? $row['valor'] : null;
    }

    /**
     * Actualiza el valor de una clave.
     */
    public function set(string $clave, ?string $valor): bool
    {
        $stmt = $this->db->prepare("UPDATE configs_globales SET valor = ? WHERE clave = ?");
        return $stmt->execute([$valor, $clave]);
    }

    /**
     * Actualiza múltiples claves en una sola transacción.
     */
    public function setMultiple(array $datos): bool
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("UPDATE configs_globales SET valor = ? WHERE clave = ?");
            foreach ($datos as $clave => $valor) {
                $stmt->execute([$valor, $clave]);
            }
            $this->db->commit();
            return true;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            error_log('[ConfigGlobalModel::setMultiple] ' . $e->getMessage());
            return false;
        }
    }
}
