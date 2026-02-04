<?php
namespace App\Modules\Auth\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\DB;

class LoginController extends Controller {

    /**
     * Muestra el formulario de login.
     */
    public function show() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        // Si ya hay sesión, redirigir al dashboard
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/dashboard'); 
        }

        return $this->view('auth/login');
    }

    /**
     * Procesa los datos del formulario POST
     */
    public function process() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        // 1. Verificar Token CSRF
        $token = $this->input('csrf_token');
        if (!Csrf::verify($token)) {
            die("Error de seguridad: Token inválido (CSRF).");
        }

        // 2. Obtener datos
        $email = $this->inputString('email');
        $password = $this->inputString('password');

        if (!$email || !$password) {
            $this->redirect('/login?error=campos_vacios');
        }

        // 3. Buscar usuario en BD
        $db = DB::connect();
        
        // Traemos ID, Password (Hash), Rol, Persona_ID y Status
        $sql = "SELECT u.id, u.password, u.role_id, u.persona_id, u.status, r.nombre as rol_nombre
                FROM users u
                JOIN roles r ON u.role_id = r.id
                WHERE u.email = ? LIMIT 1";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // 4. Verificación
        if ($user && password_verify($password, $user['password'])) {
            
            // Verificar si está activo
            if ($user['status'] !== 'active') {
                $this->redirect('/login?error=inactivo');
            }

            // --- LOGIN EXITOSO ---

            // A. Regenerar ID de sesión (Seguridad)
            session_regenerate_id(true);

            // B. Guardar datos en sesión
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role_id'] = $user['role_id'];
            $_SESSION['role_name'] = $user['rol_nombre'];
            $_SESSION['persona_id'] = $user['persona_id'];
            $_SESSION['email'] = $email;
            $_SESSION['logged_in'] = true;

            // C. Redirigir al sistema
            $this->redirect('/dashboard'); 

        } else {
            // Error de credenciales
            $this->redirect('/login?error=credenciales');
        }
    }

    /**
     * Cierra la sesión
     */
    public function logout() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        $_SESSION = [];
        
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        session_destroy();

        $this->redirect('/login');
    }
}