<?php
declare(strict_types=1);

namespace App\Modules\Admin\Models;

use App\Core\DB;

/**
 * Model CRUD para el marco legal del sistema.
 * Anti-crash: cada método tiene try/catch con retorno seguro.
 */
class MarcoLegalModel
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = DB::connect();
    }

    /**
     * Obtiene todos los registros del marco legal ordenados por orden.
     */
    public function getAll(): array
    {
        try {
            $stmt = $this->db->query("
                SELECT id, titulo, tipo, descripcion, url, estado,
                       orden, fecha_publicacion, numero_gaceta,
                       created_at, updated_at
                FROM sim_marco_legals
                ORDER BY orden ASC, id ASC
            ");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log('[MarcoLegalModel::getAll] ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Crea un nuevo artículo del marco legal.
     * Si orden es 0 o null, auto-asigna MAX(orden)+1.
     */
    public function create(array $data): int
    {
        try {
            $orden = (int) ($data['orden'] ?? 0);
            if ($orden <= 0) {
                $max = $this->db->query("SELECT COALESCE(MAX(orden), 0) FROM sim_marco_legals")->fetchColumn();
                $orden = (int) $max + 1;
            }

            $stmt = $this->db->prepare("
                INSERT INTO sim_marco_legals (titulo, tipo, descripcion, url, estado, orden, fecha_publicacion, numero_gaceta)
                VALUES (:titulo, :tipo, :descripcion, :url, :estado, :orden, :fecha_publicacion, :numero_gaceta)
            ");
            $stmt->execute([
                'titulo'            => $data['titulo'],
                'tipo'              => $data['tipo'],
                'descripcion'       => $data['descripcion'],
                'url'               => $data['url'] ?: null,
                'estado'            => $data['estado'] ?? 'Vigente',
                'orden'             => $orden,
                'fecha_publicacion' => $data['fecha_publicacion'] ?: null,
                'numero_gaceta'     => $data['numero_gaceta'] ?: null,
            ]);
            return (int) $this->db->lastInsertId();
        } catch (\Throwable $e) {
            error_log('[MarcoLegalModel::create] ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Actualiza un artículo existente por ID.
     */
    public function update(int $id, array $data): bool
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE sim_marco_legals
                SET titulo = :titulo,
                    tipo = :tipo,
                    descripcion = :descripcion,
                    url = :url,
                    estado = :estado,
                    orden = :orden,
                    fecha_publicacion = :fecha_publicacion,
                    numero_gaceta = :numero_gaceta
                WHERE id = :id
            ");
            return $stmt->execute([
                'id'                => $id,
                'titulo'            => $data['titulo'],
                'tipo'              => $data['tipo'],
                'descripcion'       => $data['descripcion'],
                'url'               => $data['url'] ?: null,
                'estado'            => $data['estado'] ?? 'Vigente',
                'orden'             => (int) ($data['orden'] ?? 0),
                'fecha_publicacion' => $data['fecha_publicacion'] ?: null,
                'numero_gaceta'     => $data['numero_gaceta'] ?: null,
            ]);
        } catch (\Throwable $e) {
            error_log('[MarcoLegalModel::update] ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Elimina un artículo por ID.
     */
    public function delete(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM sim_marco_legals WHERE id = :id");
            return $stmt->execute(['id' => $id]);
        } catch (\Throwable $e) {
            error_log('[MarcoLegalModel::delete] ' . $e->getMessage());
            return false;
        }
    }
}
