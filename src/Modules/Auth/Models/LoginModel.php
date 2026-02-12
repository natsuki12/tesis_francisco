<?php
namespace App\Modules\Auth\Models;

use App\Core\DB;

class LoginModel
{
    public function getUserByEmail(string $email): ?array
    {
        $db = DB::connect();
        
        // Traemos ID, Password (Hash), Rol, Persona_ID, Status y Nombres
        $sql = "SELECT u.id, u.password, u.role_id, u.persona_id, u.status, r.nombre as rol_nombre, p.nombres
                FROM users u
                JOIN roles r ON u.role_id = r.id
                JOIN personas p ON u.persona_id = p.id
                WHERE u.email = ? LIMIT 1";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$email]);
        
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }
}
