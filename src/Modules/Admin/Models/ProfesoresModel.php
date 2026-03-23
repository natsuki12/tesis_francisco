<?php
declare(strict_types=1);

namespace App\Modules\Admin\Models;

use App\Core\DB;

/**
 * Model para CRUD de profesores del sistema.
 */
class ProfesoresModel
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = DB::connect();
    }

    // =========================================================
    // LECTURA
    // =========================================================

    /**
     * Obtiene todos los profesores con datos personales, usuario y conteo de secciones.
     */
    public function getAll(): array
    {
        try {
            $stmt = $this->db->query("
                SELECT pr.id,
                       pr.titulo,
                       pe.id          AS persona_id,
                       pe.nacionalidad,
                       pe.cedula,
                       pe.nombres,
                       pe.apellidos,
                       pe.genero,
                       u.id           AS user_id,
                       u.email,
                       u.status,
                       u.force_password_change,
                       u.created_at   AS fecha_registro,
                       (SELECT COUNT(*) FROM secciones s WHERE s.profesor_id = pr.id) AS total_secciones
                FROM profesores pr
                INNER JOIN personas pe ON pe.id = pr.persona_id
                INNER JOIN users u     ON u.persona_id = pe.id AND u.role_id = 2
                ORDER BY u.status ASC, pe.apellidos ASC, pe.nombres ASC
            ");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log('[ProfesoresModel::getAll] ' . $e->getMessage());
            return [];
        }
    }

    // =========================================================
    // VALIDACIONES
    // =========================================================

    /**
     * Verifica si ya existe una persona con esa cédula y nacionalidad.
     */
    public function cedulaExists(string $nacionalidad, string $cedula): bool
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT COUNT(*) FROM personas WHERE nacionalidad = :nac AND cedula = :ced"
            );
            $stmt->execute([':nac' => $nacionalidad, ':ced' => $cedula]);
            return (int) $stmt->fetchColumn() > 0;
        } catch (\Throwable $e) {
            error_log('[ProfesoresModel::cedulaExists] ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Verifica si ya existe un usuario con ese email.
     * @param int|null $excludeUserId  Para edición: excluir al propio usuario.
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
            error_log('[ProfesoresModel::emailExists] ' . $e->getMessage());
            return false;
        }
    }

    // =========================================================
    // CREACIÓN
    // =========================================================

    /**
     * Crea un profesor completo: persona + profesores + users (transacción).
     *
     * @param array $data {
     *   nacionalidad, cedula, nombres, apellidos, email, titulo
     * }
     * @return array { user_id, persona_id, profesor_id }
     * @throws \RuntimeException si falla la transacción
     */
    public function createProfesor(array $data): array
    {
        $this->db->beginTransaction();

        try {
            // 1. INSERT personas (fecha_nacimiento placeholder, genero null)
            $stmt = $this->db->prepare("
                INSERT INTO personas (nacionalidad, cedula, nombres, apellidos, fecha_nacimiento, genero)
                VALUES (:nac, :ced, :nom, :ape, '2000-01-01', NULL)
            ");
            $stmt->execute([
                ':nac' => $data['nacionalidad'],
                ':ced' => $data['cedula'],
                ':nom' => $data['nombres'],
                ':ape' => $data['apellidos'],
            ]);
            $personaId = (int) $this->db->lastInsertId();

            // 2. INSERT profesores
            $stmt = $this->db->prepare("
                INSERT INTO profesores (persona_id, titulo)
                VALUES (:pid, :titulo)
            ");
            $stmt->execute([
                ':pid'    => $personaId,
                ':titulo' => $data['titulo'],
            ]);
            $profesorId = (int) $this->db->lastInsertId();

            // 3. INSERT users (password = hash de cédula, force_password_change = 1)
            $passwordHash = password_hash($data['cedula'], PASSWORD_DEFAULT);

            $stmt = $this->db->prepare("
                INSERT INTO users (persona_id, role_id, email, password, status, force_password_change)
                VALUES (:pid, 2, :email, :pass, 'active', 1)
            ");
            $stmt->execute([
                ':pid'   => $personaId,
                ':email' => $data['email'],
                ':pass'  => $passwordHash,
            ]);
            $userId = (int) $this->db->lastInsertId();

            $this->db->commit();

            return [
                'user_id'     => $userId,
                'persona_id'  => $personaId,
                'profesor_id' => $profesorId,
            ];

        } catch (\Throwable $e) {
            $this->db->rollBack();
            error_log('[ProfesoresModel::createProfesor] ' . $e->getMessage());
            throw new \RuntimeException('Error al crear el profesor: ' . $e->getMessage(), 0, $e);
        }
    }

    // =========================================================
    // ESTADO
    // =========================================================

    /**
     * Cambia el status de un usuario-profesor (active ↔ inactive).
     * Solo afecta usuarios con role_id = 2 (profesor) por seguridad.
     *
     * @return bool true si se actualizó al menos 1 fila
     */
    public function toggleStatus(int $userId, string $newStatus): bool
    {
        if (!in_array($newStatus, ['active', 'inactive'], true)) {
            return false;
        }

        try {
            $stmt = $this->db->prepare("
                UPDATE users SET status = :status, updated_at = NOW()
                WHERE id = :uid AND role_id = 2
            ");
            $stmt->execute([':status' => $newStatus, ':uid' => $userId]);
            return $stmt->rowCount() > 0;
        } catch (\Throwable $e) {
            error_log('[ProfesoresModel::toggleStatus] ' . $e->getMessage());
            return false;
        }
    }
}
