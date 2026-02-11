<?php
namespace App\Modules\Auth\Models;

use App\Core\DB;

class RegisterModel
{
    public function getSeccionesActivas(): array
    {
        $db = DB::connect();
        $stmt = $db->query("
            SELECT s.id, s.nombre as seccion, m.nombre as materia 
            FROM secciones s 
            INNER JOIN materias m ON s.materia_id = m.id 
            INNER JOIN periodos p ON s.periodo_id = p.id
            WHERE p.activo = 1
            ORDER BY m.nombre ASC, s.nombre ASC
        ");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function personaExistsByCedula(string $nacionalidad, string $cedula): bool
    {
        $db = DB::connect();
        $stmt = $db->prepare("SELECT id FROM personas WHERE nacionalidad = ? AND cedula = ? LIMIT 1");
        $stmt->execute([$nacionalidad, $cedula]);
        return (bool) $stmt->fetch();
    }

    public function userExistsByEmail(string $email): bool
    {
        $db = DB::connect();
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        return (bool) $stmt->fetch();
    }

    /**
     * Inserta TODO el registro final con la misma lógica y transacción:
     * personas -> users -> estudiantes -> inscripciones
     */
    public function createFullStudentRegistration(array $data): void
    {
        $tempUser = $data['temp_user'];

        $nombres   = $data['nombres'];
        $apellidos = $data['apellidos'];
        $fechaNac  = $data['fechaNac'];
        $genero    = $data['genero'];
        $seccionId = (int) $data['seccionId'];
        $password  = $data['password'];

        $db = DB::connect();

        // Validar FK seccion (misma excepción)
        $stmt = $db->prepare("SELECT id FROM secciones WHERE id = ? LIMIT 1");
        $stmt->execute([$seccionId]);
        if (!$stmt->fetch()) {
            throw new \Exception("Sección no existe");
        }

        // Rol "Estudiante"
        $stmt = $db->query("SELECT id FROM roles WHERE nombre = 'Estudiante' LIMIT 1");
        $rol = $stmt->fetch();
        if (!$rol) {
            throw new \RuntimeException("Rol no configurado");
        }
        $rolId = (int) $rol['id'];

        try {
            $db->beginTransaction();

            $sqlPersona = "INSERT INTO personas (nacionalidad, cedula, nombres, apellidos, fecha_nacimiento, genero, created_at)
                           VALUES (?, ?, ?, ?, ?, ?, NOW())";
            $stmt = $db->prepare($sqlPersona);
            $stmt->execute([
                $tempUser['nacionalidad'],
                $tempUser['cedula'],
                $nombres,
                $apellidos,
                $fechaNac,
                $genero
            ]);
            $personaId = $db->lastInsertId();

            $passHash = password_hash($password, PASSWORD_DEFAULT);
            $sqlUser = "INSERT INTO users (persona_id, role_id, email, password, status, created_at)
                        VALUES (?, ?, ?, ?, 'active', NOW())";
            $stmt = $db->prepare($sqlUser);
            $stmt->execute([
                $personaId,
                $rolId,
                $tempUser['email'],
                $passHash
            ]);

            // Misma lógica: carrera fija = 1
            $carreraId = 1;
            $sqlEstudiante = "INSERT INTO estudiantes (persona_id, carrera_id, created_at) VALUES (?, ?, NOW())";
            $stmt = $db->prepare($sqlEstudiante);
            $stmt->execute([$personaId, $carreraId]);
            $estudianteId = $db->lastInsertId();

            $sqlInscripcion = "INSERT INTO inscripciones (estudiante_id, seccion_id, created_at) VALUES (?, ?, NOW())";
            $stmt = $db->prepare($sqlInscripcion);
            $stmt->execute([$estudianteId, $seccionId]);

            $db->commit();

        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }
}
