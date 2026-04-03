<?php
declare(strict_types=1);

namespace App\Modules\Shared\Models;

use App\Core\DB;

/**
 * Modelo para la página de perfil.
 * Consulta datos personales, académicos y de usuario para cualquier rol.
 */
class PerfilModel
{
    /**
     * Obtiene los datos personales desde la tabla `personas`.
     *
     * @return array{nacionalidad: string, cedula: string, nombres: string, apellidos: string}|array{}
     */
    public function getPersona(int $personaId): array
    {
        $db = DB::connect();
        $stmt = $db->prepare(
            "SELECT nacionalidad, cedula, nombres, apellidos
             FROM personas
             WHERE id = ?
             LIMIT 1"
        );
        $stmt->execute([$personaId]);

        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Obtiene la fecha de registro del usuario (created_at de la tabla `users`).
     */
    public function getUserCreatedAt(int $personaId): ?string
    {
        $db = DB::connect();
        $stmt = $db->prepare(
            "SELECT created_at FROM users WHERE persona_id = ? LIMIT 1"
        );
        $stmt->execute([$personaId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $row ? $row['created_at'] : null;
    }

    /**
     * Obtiene datos académicos del estudiante: carrera y sección activa.
     *
     * @return array{estudiante_id: int, carrera: string, seccion: ?string}|array{}
     */
    public function getEstudianteData(int $personaId): array
    {
        $db = DB::connect();
        $stmt = $db->prepare(
            "SELECT e.id AS estudiante_id, c.nombre AS carrera,
                    (SELECT s.nombre
                     FROM inscripciones i
                     JOIN secciones s ON i.seccion_id = s.id
                     JOIN periodos p  ON s.periodo_id = p.id AND p.activo = 1
                     WHERE i.estudiante_id = e.id
                     ORDER BY i.created_at DESC
                     LIMIT 1) AS seccion
             FROM estudiantes e
             JOIN carreras c ON e.carrera_id = c.id
             WHERE e.persona_id = ?
             LIMIT 1"
        );
        $stmt->execute([$personaId]);

        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Obtiene datos del profesor: título y firma digital.
     *
     * @return array{titulo: string, firma_digital: ?string}|array{}
     */
    public function getProfesorData(int $personaId): array
    {
        $db = DB::connect();
        $stmt = $db->prepare(
            "SELECT titulo, firma_digital FROM profesores WHERE persona_id = ? LIMIT 1"
        );
        $stmt->execute([$personaId]);

        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: [];
    }
}
