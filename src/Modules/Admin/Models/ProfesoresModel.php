<?php
declare(strict_types=1);

namespace App\Modules\Admin\Models;

use App\Core\DB;
use App\Core\Traits\ValidatesUsuarios;

/**
 * Model para CRUD de profesores del sistema.
 */
class ProfesoresModel
{
    use ValidatesUsuarios;

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

    /**
     * Obtiene un profesor específico dado su user_id.
     */
    public function getProfesorById(int $userId): ?array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT pr.id AS profesor_id,
                       pr.titulo,
                       pe.id AS persona_id,
                       pe.nacionalidad,
                       pe.cedula,
                       pe.nombres,
                       pe.apellidos,
                       u.id AS user_id,
                       u.email,
                       u.status
                FROM users u
                INNER JOIN personas pe ON pe.id = u.persona_id
                INNER JOIN profesores pr ON pr.persona_id = pe.id
                WHERE u.id = :uid AND u.role_id = 2
                LIMIT 1
            ");
            $stmt->execute([':uid' => $userId]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (\Throwable $e) {
            error_log('[ProfesoresModel::getProfesorById] ' . $e->getMessage());
            return null;
        }
    }

    // Validaciones cedulaExists() y emailExists() provienen del trait ValidatesUsuarios

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
            // 1. INSERT personas
            $stmt = $this->db->prepare("
                INSERT INTO personas (nacionalidad, cedula, nombres, apellidos)
                VALUES (:nac, :ced, :nom, :ape)
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

    /**
     * Crea un profesor sin título (para importación masiva CSV).
     * Inserta persona + profesores (sin titulo) + users.
     *
     * @param array $data { nacionalidad, cedula, nombres, apellidos, email }
     * @return array { user_id, persona_id, profesor_id }
     * @throws \RuntimeException
     */
    public function createProfesorBasic(array $data): array
    {
        $this->db->beginTransaction();

        try {
            // 1. INSERT personas
            $stmt = $this->db->prepare("
                INSERT INTO personas (nacionalidad, cedula, nombres, apellidos)
                VALUES (:nac, :ced, :nom, :ape)
            ");
            $stmt->execute([
                ':nac' => $data['nacionalidad'],
                ':ced' => $data['cedula'],
                ':nom' => $data['nombres'],
                ':ape' => $data['apellidos'],
            ]);
            $personaId = (int) $this->db->lastInsertId();

            // 2. INSERT profesores (titulo queda NULL por defecto)
            $stmt = $this->db->prepare("
                INSERT INTO profesores (persona_id) VALUES (:pid)
            ");
            $stmt->execute([':pid' => $personaId]);
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
            error_log('[ProfesoresModel::createProfesorBasic] ' . $e->getMessage());
            throw new \RuntimeException('Error al crear el profesor: ' . $e->getMessage(), 0, $e);
        }
    }

    // =========================================================
    // ACTUALIZACIÓN
    // =========================================================

    /**
     * Actualiza los datos de un profesor (transacción).
     *
     * @param int $userId ID de la tabla users
     * @param int $personaId ID de la tabla personas
     * @param array $data { nacionalidad, cedula, nombres, apellidos, email, titulo }
     * @return bool
     * @throws \RuntimeException
     */
    public function updateProfesor(int $userId, int $personaId, array $data): bool
    {
        $this->db->beginTransaction();

        try {
            // 1. UPDATE personas
            $stmt = $this->db->prepare("
                UPDATE personas 
                SET nacionalidad = :nac, cedula = :ced, nombres = :nom, apellidos = :ape 
                WHERE id = :pid
            ");
            $stmt->execute([
                ':nac' => $data['nacionalidad'],
                ':ced' => $data['cedula'],
                ':nom' => $data['nombres'],
                ':ape' => $data['apellidos'],
                ':pid' => $personaId,
            ]);

            // 2. UPDATE profesores
            $stmt = $this->db->prepare("
                UPDATE profesores 
                SET titulo = :titulo 
                WHERE persona_id = :pid
            ");
            $stmt->execute([
                ':titulo' => $data['titulo'],
                ':pid'    => $personaId,
            ]);

            // 3. UPDATE users
            $stmt = $this->db->prepare("
                UPDATE users 
                SET email = :email, updated_at = NOW() 
                WHERE id = :uid
            ");
            $stmt->execute([
                ':email' => $data['email'],
                ':uid'   => $userId,
            ]);

            $this->db->commit();
            return true;

        } catch (\Throwable $e) {
            $this->db->rollBack();
            error_log('[ProfesoresModel::updateProfesor] ' . $e->getMessage());
            throw new \RuntimeException('Error al actualizar el profesor: ' . $e->getMessage(), 0, $e);
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
