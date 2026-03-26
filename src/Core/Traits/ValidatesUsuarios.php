<?php
declare(strict_types=1);

namespace App\Core\Traits;

/**
 * Trait con validaciones reutilizables para modelos de usuarios.
 * Requiere que la clase que lo use tenga la propiedad $db (PDO).
 */
trait ValidatesUsuarios
{
    /**
     * Verifica si ya existe una persona con esa cédula y nacionalidad.
     * @param int|null $excludePersonaId Para edición: excluir a la propia persona.
     */
    public function cedulaExists(string $nacionalidad, string $cedula, ?int $excludePersonaId = null): bool
    {
        try {
            $sql = "SELECT COUNT(*) FROM personas WHERE nacionalidad = :nac AND cedula = :ced";
            $params = [':nac' => $nacionalidad, ':ced' => $cedula];

            if ($excludePersonaId !== null) {
                $sql .= " AND id != :pid";
                $params[':pid'] = $excludePersonaId;
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return (int) $stmt->fetchColumn() > 0;
        } catch (\Throwable $e) {
            error_log('[ValidatesUsuarios::cedulaExists] ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Verifica si ya existe un usuario con ese email.
     * @param int|null $excludeUserId Para edición: excluir al propio usuario.
     */
    public function emailExists(string $email, ?int $excludeUserId = null): bool
    {
        try {
            $sql = "SELECT COUNT(*) FROM users WHERE email = :email";
            $params = [':email' => $email];

            if ($excludeUserId !== null) {
                $sql .= " AND id != :uid";
                $params[':uid'] = $excludeUserId;
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return (int) $stmt->fetchColumn() > 0;
        } catch (\Throwable $e) {
            error_log('[ValidatesUsuarios::emailExists] ' . $e->getMessage());
            return false;
        }
    }
}
