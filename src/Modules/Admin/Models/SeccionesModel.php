<?php
declare(strict_types=1);

namespace App\Modules\Admin\Models;

use App\Core\DB;

/**
 * Model para la gestión CRUD de secciones académicas.
 */
class SeccionesModel
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = DB::connect();
    }

    /**
     * Obtiene todas las secciones con datos del período, profesor, materia y conteo de inscritos.
     */
    public function getAll(): array
    {
        try {
            $stmt = $this->db->query("
                SELECT s.id,
                       s.nombre,
                       s.cupo_maximo,
                       s.materia_id,
                       s.created_at,
                       m.nombre     AS materia,
                       p.nombre     AS periodo,
                       p.activo     AS periodo_activo,
                       p.id         AS periodo_id,
                       pr.id        AS profesor_id,
                       CONCAT(pe.nombres, ' ', pe.apellidos) AS profesor_nombre,
                       (SELECT COUNT(*) FROM inscripciones i WHERE i.seccion_id = s.id) AS inscritos
                FROM secciones s
                INNER JOIN materias   m  ON m.id  = s.materia_id
                INNER JOIN periodos   p  ON p.id  = s.periodo_id
                LEFT JOIN profesores pr ON pr.id = s.profesor_id
                LEFT JOIN personas   pe ON pe.id = pr.persona_id
                WHERE s.deleted_at IS NULL
                ORDER BY p.activo DESC, p.nombre DESC, s.nombre ASC
            ");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log('[SeccionesModel::getAll] ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene todos los períodos para el selector del modal.
     */
    public function getPeriodos(): array
    {
        try {
            $stmt = $this->db->query("SELECT id, nombre, activo FROM periodos ORDER BY activo DESC, nombre DESC");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log('[SeccionesModel::getPeriodos] ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene todos los profesores para el selector del modal.
     */
    public function getProfesores(): array
    {
        try {
            $stmt = $this->db->query("
                SELECT pr.id,
                       CONCAT(pe.nombres, ' ', pe.apellidos) AS nombre_completo,
                       pr.titulo
                FROM profesores pr
                INNER JOIN personas pe ON pe.id = pr.persona_id
                INNER JOIN users u ON u.persona_id = pe.id AND u.status = 'active'
                ORDER BY pe.apellidos ASC
            ");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log('[SeccionesModel::getProfesores] ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene todas las materias para el selector del modal.
     */
    public function getMaterias(): array
    {
        try {
            $stmt = $this->db->query("SELECT id, nombre, codigo FROM materias ORDER BY nombre ASC");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log('[SeccionesModel::getMaterias] ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene el período activo actual.
     */
    public function getPeriodoActivo(): ?array
    {
        try {
            $stmt = $this->db->query("SELECT id, nombre FROM periodos WHERE activo = 1 LIMIT 1");
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (\Throwable $e) {
            error_log('[SeccionesModel::getPeriodoActivo] ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Crea una nueva sección.
     * @return array{success: bool, message: string}
     */
    public function create(array $data): array
    {
        $nombre     = trim($data['nombre'] ?? '');
        $cupo       = (int)($data['cupo_maximo'] ?? 40);
        $profesorId = !empty($data['profesor_id']) ? (int)$data['profesor_id'] : null;
        $materiaId  = (int)($data['materia_id'] ?? 0);
        $periodoId  = (int)($data['periodo_id'] ?? 0);

        // Validaciones
        if ($nombre === '') {
            return ['success' => false, 'message' => 'El nombre de la sección es obligatorio.'];
        }
        if (mb_strlen($nombre) > 20) {
            return ['success' => false, 'message' => 'El nombre no puede exceder 20 caracteres.'];
        }
        if ($cupo < 1 || $cupo > 999) {
            return ['success' => false, 'message' => 'El cupo máximo debe estar entre 1 y 999.'];
        }
        if ($materiaId <= 0) {
            return ['success' => false, 'message' => 'Debe seleccionar una materia.'];
        }
        if ($periodoId <= 0) {
            return ['success' => false, 'message' => 'No se detectó un período académico activo.'];
        }

        // Verificar duplicado: mismo nombre + periodo + materia
        $check = $this->db->prepare("
            SELECT id FROM secciones 
            WHERE nombre = ? AND periodo_id = ? AND materia_id = ? AND deleted_at IS NULL
        ");
        $check->execute([$nombre, $periodoId, $materiaId]);
        if ($check->fetch()) {
            return ['success' => false, 'message' => 'Ya existe una sección con ese nombre en este período y materia.'];
        }

        // Verificar que profesor exista (si se proporcionó)
        if ($profesorId !== null) {
            $profCheck = $this->db->prepare("SELECT id FROM profesores WHERE id = ?");
            $profCheck->execute([$profesorId]);
            if (!$profCheck->fetch()) {
                return ['success' => false, 'message' => 'El profesor seleccionado no existe.'];
            }
        }

        $matCheck = $this->db->prepare("SELECT id FROM materias WHERE id = ?");
        $matCheck->execute([$materiaId]);
        if (!$matCheck->fetch()) {
            return ['success' => false, 'message' => 'La materia seleccionada no existe.'];
        }

        // Insertar
        $stmt = $this->db->prepare("
            INSERT INTO secciones (materia_id, profesor_id, periodo_id, nombre, cupo_maximo)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$materiaId, $profesorId, $periodoId, $nombre, $cupo]);

        return ['success' => true, 'message' => "Sección «{$nombre}» creada exitosamente."];
    }

    /**
     * Actualiza una sección existente.
     * @return array{success: bool, message: string}
     */
    public function update(int $id, array $data): array
    {
        $nombre     = trim($data['nombre'] ?? '');
        $cupo       = (int)($data['cupo_maximo'] ?? 40);
        $profesorId = !empty($data['profesor_id']) ? (int)$data['profesor_id'] : null;
        $materiaId  = (int)($data['materia_id'] ?? 0);

        // Validaciones
        if ($nombre === '') {
            return ['success' => false, 'message' => 'El nombre de la sección es obligatorio.'];
        }
        if (mb_strlen($nombre) > 20) {
            return ['success' => false, 'message' => 'El nombre no puede exceder 20 caracteres.'];
        }
        if ($cupo < 1 || $cupo > 999) {
            return ['success' => false, 'message' => 'El cupo máximo debe estar entre 1 y 999.'];
        }
        if ($materiaId <= 0) {
            return ['success' => false, 'message' => 'Debe seleccionar una materia.'];
        }

        // Verificar que la sección exista
        $existing = $this->db->prepare("SELECT id, periodo_id FROM secciones WHERE id = ? AND deleted_at IS NULL");
        $existing->execute([$id]);
        $row = $existing->fetch(\PDO::FETCH_ASSOC);
        if (!$row) {
            return ['success' => false, 'message' => 'La sección no existe o fue eliminada.'];
        }

        // Verificar duplicado: mismo nombre + periodo + materia (excluir el registro actual)
        $check = $this->db->prepare("
            SELECT id FROM secciones 
            WHERE nombre = ? AND periodo_id = ? AND materia_id = ? AND id != ? AND deleted_at IS NULL
        ");
        $check->execute([$nombre, $row['periodo_id'], $materiaId, $id]);
        if ($check->fetch()) {
            return ['success' => false, 'message' => 'Ya existe otra sección con ese nombre en este período y materia.'];
        }

        // Verificar que profesor exista (si se proporcionó)
        if ($profesorId !== null) {
            $profCheck = $this->db->prepare("SELECT id FROM profesores WHERE id = ?");
            $profCheck->execute([$profesorId]);
            if (!$profCheck->fetch()) {
                return ['success' => false, 'message' => 'El profesor seleccionado no existe.'];
            }
        }

        // Verificar que materia exista
        $matCheck = $this->db->prepare("SELECT id FROM materias WHERE id = ?");
        $matCheck->execute([$materiaId]);
        if (!$matCheck->fetch()) {
            return ['success' => false, 'message' => 'La materia seleccionada no existe.'];
        }

        // Actualizar
        $stmt = $this->db->prepare("
            UPDATE secciones SET nombre = ?, materia_id = ?, profesor_id = ?, cupo_maximo = ?
            WHERE id = ?
        ");
        $stmt->execute([$nombre, $materiaId, $profesorId, $cupo, $id]);

        return ['success' => true, 'message' => "Sección «{$nombre}» actualizada exitosamente."];
    }

    // ════════════════════════════════════════════════════════════
    //  GESTIÓN DE ESTUDIANTES POR SECCIÓN
    // ════════════════════════════════════════════════════════════

    /**
     * Obtiene los estudiantes inscritos en una sección.
     */
    public function getEstudiantesSeccion(int $seccionId): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT e.id,
                       pe.nacionalidad,
                       pe.cedula,
                       CONCAT(pe.nombres, ' ', pe.apellidos) AS nombre_completo,
                       u.email,
                       i.created_at AS fecha_inscripcion
                FROM inscripciones i
                INNER JOIN estudiantes e ON e.id = i.estudiante_id
                INNER JOIN personas pe   ON pe.id = e.persona_id
                LEFT  JOIN users u       ON u.persona_id = pe.id
                WHERE i.seccion_id = :seccion_id
                ORDER BY pe.apellidos ASC, pe.nombres ASC
            ");
            $stmt->execute(['seccion_id' => $seccionId]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log('[SeccionesModel::getEstudiantesSeccion] ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca estudiantes NO inscritos en una sección (para el autocomplete).
     */
    public function buscarEstudiantesDisponibles(int $seccionId, string $query): array
    {
        try {
            $like = '%' . mb_strtolower(trim($query)) . '%';
            $stmt = $this->db->prepare("
                SELECT e.id,
                       pe.nacionalidad,
                       pe.cedula,
                       CONCAT(pe.nombres, ' ', pe.apellidos) AS nombre_completo,
                       u.email
                FROM estudiantes e
                INNER JOIN personas pe ON pe.id = e.persona_id
                LEFT  JOIN users u     ON u.persona_id = pe.id
                WHERE e.id NOT IN (
                    SELECT i.estudiante_id FROM inscripciones i WHERE i.seccion_id = :seccion_id
                )
                AND (
                    LOWER(CONCAT(pe.nombres, ' ', pe.apellidos)) LIKE :q
                    OR pe.cedula LIKE :q2
                    OR LOWER(COALESCE(u.email, '')) LIKE :q3
                )
                ORDER BY pe.apellidos ASC
                LIMIT 25
            ");
            $stmt->execute([
                'seccion_id' => $seccionId,
                'q'          => $like,
                'q2'         => $like,
                'q3'         => $like,
            ]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log('[SeccionesModel::buscarEstudiantesDisponibles] ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Inscribe un estudiante en una sección.
     */
    public function inscribirEstudiante(int $seccionId, int $estudianteId): array
    {
        try {
            // Verificar que la sección exista
            $sec = $this->db->prepare("SELECT id, cupo_maximo FROM secciones WHERE id = ? AND deleted_at IS NULL");
            $sec->execute([$seccionId]);
            $seccion = $sec->fetch(\PDO::FETCH_ASSOC);
            if (!$seccion) {
                return ['success' => false, 'message' => 'La sección no existe.'];
            }

            // Verificar que el estudiante exista
            $est = $this->db->prepare("SELECT id FROM estudiantes WHERE id = ?");
            $est->execute([$estudianteId]);
            if (!$est->fetch()) {
                return ['success' => false, 'message' => 'El estudiante no existe.'];
            }

            // Verificar duplicado
            $dup = $this->db->prepare("SELECT id FROM inscripciones WHERE seccion_id = ? AND estudiante_id = ?");
            $dup->execute([$seccionId, $estudianteId]);
            if ($dup->fetch()) {
                return ['success' => false, 'message' => 'El estudiante ya está inscrito en esta sección.'];
            }

            // Verificar cupo
            $count = $this->db->prepare("SELECT COUNT(*) FROM inscripciones WHERE seccion_id = ?");
            $count->execute([$seccionId]);
            $inscritos = (int) $count->fetchColumn();
            if ($inscritos >= (int) $seccion['cupo_maximo']) {
                return ['success' => false, 'message' => 'La sección ha alcanzado su cupo máximo.'];
            }

            // Inscribir
            $stmt = $this->db->prepare("INSERT INTO inscripciones (estudiante_id, seccion_id) VALUES (?, ?)");
            $stmt->execute([$estudianteId, $seccionId]);

            return ['success' => true, 'message' => 'Estudiante inscrito exitosamente.', 'inscritos' => $inscritos + 1];
        } catch (\Throwable $e) {
            error_log('[SeccionesModel::inscribirEstudiante] ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno al inscribir.'];
        }
    }

    /**
     * Desinscribe un estudiante de una sección.
     */
    public function desinscribirEstudiante(int $seccionId, int $estudianteId): array
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM inscripciones WHERE seccion_id = ? AND estudiante_id = ?");
            $stmt->execute([$seccionId, $estudianteId]);

            if ($stmt->rowCount() === 0) {
                return ['success' => false, 'message' => 'No se encontró la inscripción.'];
            }

            // Retornar nuevo conteo
            $count = $this->db->prepare("SELECT COUNT(*) FROM inscripciones WHERE seccion_id = ?");
            $count->execute([$seccionId]);

            return ['success' => true, 'message' => 'Estudiante removido de la sección.', 'inscritos' => (int) $count->fetchColumn()];
        } catch (\Throwable $e) {
            error_log('[SeccionesModel::desinscribirEstudiante] ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno al desinscribir.'];
        }
    }

    /**
     * Sincroniza los estudiantes de una sección.
     * Recibe la lista final de IDs → inserta nuevos, elimina los que ya no están.
     * @param int   $seccionId
     * @param int[] $estudianteIds  Lista final deseada de IDs de estudiantes
     * @return array{success: bool, message: string, inscritos?: int}
     */
    public function syncEstudiantes(int $seccionId, array $estudianteIds): array
    {
        try {
            // Verificar que la sección exista
            $sec = $this->db->prepare("SELECT id, cupo_maximo FROM secciones WHERE id = ? AND deleted_at IS NULL");
            $sec->execute([$seccionId]);
            $seccion = $sec->fetch(\PDO::FETCH_ASSOC);
            if (!$seccion) {
                return ['success' => false, 'message' => 'La sección no existe.'];
            }

            // Verificar cupo
            if (count($estudianteIds) > (int) $seccion['cupo_maximo']) {
                return ['success' => false, 'message' => 'La cantidad de estudiantes excede el cupo máximo de la sección (' . $seccion['cupo_maximo'] . ').'];
            }

            // Obtener IDs actualmente inscritos
            $stmt = $this->db->prepare("SELECT estudiante_id FROM inscripciones WHERE seccion_id = ?");
            $stmt->execute([$seccionId]);
            $actuales = array_column($stmt->fetchAll(\PDO::FETCH_ASSOC), 'estudiante_id');
            $actuales = array_map('intval', $actuales);

            $nuevosIds = array_map('intval', $estudianteIds);

            $paraInscribir   = array_diff($nuevosIds, $actuales);
            $paraDesinscribir = array_diff($actuales, $nuevosIds);

            // Nada que cambiar
            if (empty($paraInscribir) && empty($paraDesinscribir)) {
                return ['success' => true, 'message' => 'Sin cambios.', 'inscritos' => count($actuales)];
            }

            $this->db->beginTransaction();

            // Eliminar los removidos
            if (!empty($paraDesinscribir)) {
                $placeholders = implode(',', array_fill(0, count($paraDesinscribir), '?'));
                $del = $this->db->prepare("DELETE FROM inscripciones WHERE seccion_id = ? AND estudiante_id IN ($placeholders)");
                $del->execute(array_merge([$seccionId], array_values($paraDesinscribir)));
            }

            // Insertar los nuevos
            if (!empty($paraInscribir)) {
                $ins = $this->db->prepare("INSERT INTO inscripciones (estudiante_id, seccion_id) VALUES (?, ?)");
                foreach ($paraInscribir as $estId) {
                    $ins->execute([$estId, $seccionId]);
                }
            }

            $this->db->commit();

            $totalInscritos = count($nuevosIds);
            $added = count($paraInscribir);
            $removed = count($paraDesinscribir);
            $parts = [];
            if ($added > 0) $parts[] = "$added inscrito(s)";
            if ($removed > 0) $parts[] = "$removed removido(s)";

            return [
                'success'   => true,
                'message'   => 'Estudiantes actualizados: ' . implode(', ', $parts) . '.',
                'inscritos' => $totalInscritos,
            ];
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            error_log('[SeccionesModel::syncEstudiantes] ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno al sincronizar estudiantes.'];
        }
    }
}
