<?php
declare(strict_types=1);

namespace App\Modules\Auth\Controllers;

use App\Core\App;
use App\Core\Csrf;
use App\Core\DB;
use App\Core\BitacoraModel;

/**
 * Controlador para completar perfil en el primer login de un profesor.
 * Muestra formulario para: fecha_nacimiento, género, nueva contraseña.
 */
class CompletarPerfilController
{
    /**
     * Muestra el formulario de completar perfil.
     */
    public function index(): void
    {
        // Solo accesible si hay sesión activa con force_password_change
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (empty($_SESSION['logged_in']) || empty($_SESSION['force_password_change'])) {
            header('Location: ' . base_url('/home'));
            exit;
        }

        $pageTitle = 'Completar Perfil';
        require_once __DIR__ . '/../../../../resources/views/auth/completar_perfil.php';
    }

    /**
     * Procesa el formulario de completar perfil (POST).
     */
    public function guardar(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        header('Content-Type: application/json');

        try {
            // Verificar sesión
            if (empty($_SESSION['logged_in']) || empty($_SESSION['force_password_change'])) {
                echo json_encode(['success' => false, 'message' => 'Sesión inválida.']);
                exit;
            }

            // CSRF
            if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
                echo json_encode(['success' => false, 'message' => 'Token de seguridad inválido.']);
                exit;
            }

            // Datos
            $fechaNacimiento = trim($_POST['fecha_nacimiento'] ?? '');
            $genero          = trim($_POST['genero'] ?? '');
            $password        = $_POST['password'] ?? '';
            $passwordConfirm = $_POST['password_confirm'] ?? '';

            // Validaciones
            $errors = [];

            // Fecha válida
            if (!$fechaNacimiento || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaNacimiento)) {
                $errors[] = 'Fecha de nacimiento inválida.';
            } else {
                $dt = \DateTime::createFromFormat('Y-m-d', $fechaNacimiento);
                if (!$dt || $dt->format('Y-m-d') !== $fechaNacimiento) {
                    $errors[] = 'Fecha de nacimiento no es real.';
                } elseif ($dt > new \DateTime()) {
                    $errors[] = 'La fecha de nacimiento no puede ser futura.';
                }
            }

            // Género (enum: M, F, Otro, Prefiero no decir)
            $generosValidos = ['M', 'F', 'Otro', 'Prefiero no decir'];
            if ($genero !== '' && !in_array($genero, $generosValidos, true)) {
                $errors[] = 'Género inválido.';
            }

            // Contraseña
            if (mb_strlen($password) < 8) {
                $errors[] = 'La contraseña debe tener al menos 8 caracteres.';
            }
            if (!preg_match('/\d/', $password)) {
                $errors[] = 'La contraseña debe contener al menos 1 dígito.';
            }
            if ($password !== $passwordConfirm) {
                $errors[] = 'Las contraseñas no coinciden.';
            }

            if (!empty($errors)) {
                echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
                exit;
            }

            // Actualizar BD
            $db = DB::connect();
            $db->beginTransaction();

            try {
                $personaId = (int) $_SESSION['persona_id'];
                $userId    = (int) $_SESSION['user_id'];

                // 1. UPDATE personas
                $stmt = $db->prepare("
                    UPDATE personas SET fecha_nacimiento = :fn, genero = :gen WHERE id = :pid
                ");
                $stmt->execute([
                    ':fn'  => $fechaNacimiento,
                    ':gen' => $genero ?: null,
                    ':pid' => $personaId,
                ]);

                // 2. UPDATE users
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("
                    UPDATE users SET password = :pass, force_password_change = 0, updated_at = NOW() WHERE id = :uid
                ");
                $stmt->execute([
                    ':pass' => $passwordHash,
                    ':uid'  => $userId,
                ]);

                $db->commit();

                // Limpiar flag de sesión
                unset($_SESSION['force_password_change']);

                // Bitácora
                BitacoraModel::registrar(
                    BitacoraModel::PASSWORD_RESET_OK,
                    'autenticacion',
                    $userId,
                    $_SESSION['email'] ?? null,
                    'users',
                    $userId,
                    'Primer login: perfil completado y contraseña cambiada'
                );

                echo json_encode(['success' => true, 'message' => '¡Perfil completado! Redirigiendo...', 'redirect' => base_url('/home')]);
                exit;

            } catch (\Throwable $e) {
                $db->rollBack();
                throw $e;
            }

        } catch (\Throwable $e) {
            error_log('[CompletarPerfilController::guardar] ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno al procesar.']);
            exit;
        }
    }
}
