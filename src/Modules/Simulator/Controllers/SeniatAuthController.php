<?php
declare(strict_types=1);

namespace App\Modules\Simulator\Controllers;

use App\Modules\Student\Models\StudentAttemptModel;

/**
 * Controller para la autenticación simulada del portal SENIAT.
 * Extraído de web.php para respetar MVC.
 */
class SeniatAuthController
{
    private StudentAttemptModel $attemptModel;

    public function __construct()
    {
        $this->attemptModel = new StudentAttemptModel();
    }

    /**
     * GET /simulador/servicios_declaracion
     * Muestra la página de login SENIAT con credenciales de "¿Olvidó su información?"
     */
    public function serviciosDeclaracion(\App\Core\App $app): string
    {
        $usuarioSeniat = null;
        $passwordRif = null;

        try {
            if (!empty($_SESSION['sim_asignacion_id'])) {
                $intento = $this->attemptModel->getIntentoActivo((int) $_SESSION['sim_asignacion_id']);
                if ($intento) {
                    $usuarioSeniat = $intento['usuario_seniat'] ?? null;
                    $passwordRif = $intento['password_rif'] ?? null;
                }
            }
        } catch (\Throwable $e) {
            error_log('[SeniatAuthController::serviciosDeclaracion] ' . $e->getMessage());
        }

        return $app->view('simulator/seniat_actual/servicios_declaracion', [
            'usuarioSeniat' => $usuarioSeniat,
            'passwordRif' => $passwordRif,
        ]);
    }

    /**
     * POST /simulador/servicios_declaracion/login
     * Valida usuario/clave contra intento activo.
     */
    public function login(): void
    {
        header('Content-Type: application/json');

        try {
            $usuario = trim($_POST['usuario'] ?? '');
            $clave = trim($_POST['clave'] ?? '');

            if (empty($usuario) || empty($clave)) {
                echo json_encode(['ok' => false, 'msg' => 'Debe ingresar usuario y clave.']);
                return;
            }

            if (empty($_SESSION['sim_asignacion_id'])) {
                echo json_encode(['ok' => false, 'msg' => 'No se encontró una asignación activa.']);
                return;
            }

            $intento = $this->attemptModel->getIntentoActivo((int) $_SESSION['sim_asignacion_id']);

            if (!$intento) {
                echo json_encode(['ok' => false, 'msg' => 'No se encontró un intento en progreso.']);
                return;
            }

            $usuarioDb = $intento['usuario_seniat'] ?? null;
            $claveDb = $intento['password_rif'] ?? null;

            if (empty($usuarioDb) || empty($claveDb)) {
                echo json_encode(['ok' => false, 'msg' => 'Usted no posee credenciales registradas. Utilice el botón "Regístrese" para crear su usuario.']);
                return;
            }

            if ($usuario !== $usuarioDb || $clave !== $claveDb) {
                echo json_encode(['ok' => false, 'msg' => 'Usuario o clave incorrectos.']);
                return;
            }

            // Credenciales válidas — marcar sesión
            $_SESSION['sim_seniat_logged_in'] = true;

            echo json_encode([
                'ok' => true,
                'redirect' => base_url('/simulador/servicios_declaracion/sistemas'),
            ]);

        } catch (\Throwable $e) {
            error_log('[SeniatAuthController::login] ' . $e->getMessage());
            echo json_encode(['ok' => false, 'msg' => 'Error interno del servidor. Intente de nuevo.']);
        }
    }

    /**
     * GET /simulador/servicios_declaracion/logout
     * Cierra sesión SENIAT simulada (no la de la app).
     */
    public function logout(): void
    {
        try {
            unset($_SESSION['sim_seniat_logged_in']);
        } catch (\Throwable $e) {
            error_log('[SeniatAuthController::logout] ' . $e->getMessage());
        }
        header('Location: ' . base_url('/simulador/servicios_declaracion?sesion_cerrada=1'));
        exit;
    }
}
