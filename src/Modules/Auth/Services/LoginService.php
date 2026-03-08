<?php
namespace App\Modules\Auth\Services;

use App\Modules\Auth\Models\LoginModel;
use App\Modules\Auth\Models\UserSessionModel;
use App\Core\BitacoraModel;

class LoginService
{
    private LoginModel $model;

    public function __construct(LoginModel $model)
    {
        $this->model = $model;
    }

    public function handleLogin(string $email, string $password): string
    {
        if (!$email || !$password) {
            return '/login?error=campos_vacios';
        }

        $user = $this->model->getUserByEmail($email);

        if ($user && password_verify($password, $user['password'])) {

            // Verificar si está activo
            if ($user['status'] !== 'active') {
                BitacoraModel::log(
                    BitacoraModel::USER_BLOCKED,
                    (int) $user['id'],
                    $email,
                    "Usuario con status: {$user['status']}"
                );
                return '/login?error=inactivo';
            }

            // --- LOGIN EXITOSO ---

            // A. Regenerar ID de sesión (Seguridad)
            session_regenerate_id(true);

            // B. Registrar sesión activa en user_sessions (desplaza la anterior si existe)
            $ip = $_SERVER['REMOTE_ADDR'] ?? null;
            $ua = $_SERVER['HTTP_USER_AGENT'] ?? null;
            $displaced = UserSessionModel::upsert((int) $user['id'], session_id(), $ip, $ua);

            // Si desplazó una sesión previa, registrar en bitácora
            if ($displaced) {
                BitacoraModel::log(
                    BitacoraModel::LOGIN_SUCCESS,
                    (int) $user['id'],
                    $email,
                    'Sesión anterior desplazada desde otra ubicación'
                );
            }

            // C. Guardar datos en sesión
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role_id'] = $user['role_id'];
            $_SESSION['role_name'] = $user['rol_nombre'];
            $_SESSION['persona_id'] = $user['persona_id'];
            $_SESSION['user_name'] = $user['nombres']; // Para el dashboard
            $_SESSION['email'] = $email;
            $_SESSION['logged_in'] = true;

            // D. Registrar en bitácora
            BitacoraModel::log(
                BitacoraModel::LOGIN_SUCCESS,
                (int) $user['id'],
                $email
            );

            // E. Redirigir al dashboard
            return '/home';
        }

        // Error de credenciales
        BitacoraModel::log(
            BitacoraModel::LOGIN_FAILED,
            null,
            $email,
            'Credenciales inválidas'
        );
        return '/login?error=credenciales';
    }

    public function handleLogout(): string
    {
        // Capturar datos ANTES de destruir la sesión
        $userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
        $email = $_SESSION['email'] ?? null;

        // Registrar en bitácora
        BitacoraModel::log(
            BitacoraModel::LOGOUT,
            $userId,
            $email
        );

        // Eliminar sesión de user_sessions
        if ($userId) {
            UserSessionModel::deleteByUserId($userId);
        }

        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        session_destroy();

        return '/';
    }
}
