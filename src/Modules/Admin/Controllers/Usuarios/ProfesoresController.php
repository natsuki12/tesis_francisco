<?php
declare(strict_types=1);

namespace App\Modules\Admin\Controllers\Usuarios;

use App\Core\App;
use App\Core\Csrf;
use App\Core\Mailer;
use App\Core\BitacoraModel;
use App\Modules\Admin\Models\ProfesoresModel;

class ProfesoresController
{
    private App $app;

    public function __construct()
    {
        global $app;
        $this->app = $app;
    }

    /**
     * Muestra la vista principal de Gestión de Profesores
     */
    public function index()
    {
        try {
            $model = new ProfesoresModel();
            $profesores = $model->getAll();
        } catch (\Throwable $e) {
            error_log('[ProfesoresController::index] ' . $e->getMessage());
            $profesores = [];
        }

        require_once __DIR__ . '/../../../../../resources/views/admin/usuarios/gestionar_profesores.php';
    }

    /**
     * Crea un Profesor nuevo (AJAX, POST).
     * Valida inputs, verifica duplicados, crea en BD, envía correo.
     */
    public function guardar()
    {
        header('Content-Type: application/json');

        try {
            // ── 1. CSRF ──
            if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
                echo json_encode(['success' => false, 'message' => 'Token de seguridad inválido. Recargue la página.']);
                exit;
            }

            // ── 2. Sanitizar inputs ──
            $nacionalidad = trim($_POST['nacionalidad'] ?? '');
            $cedula       = trim($_POST['cedula'] ?? '');
            $nombres      = trim($_POST['nombres'] ?? '');
            $apellidos    = trim($_POST['apellidos'] ?? '');
            $email        = trim($_POST['email'] ?? '');
            $titulo       = trim($_POST['titulo'] ?? '');

            // ── 3. Validaciones ──
            $errors = [];

            if (!in_array($nacionalidad, ['V', 'E'], true)) {
                $errors[] = 'Nacionalidad inválida.';
            }
            if (!preg_match('/^\d{6,10}$/', $cedula)) {
                $errors[] = 'La cédula debe contener entre 6 y 10 dígitos.';
            }
            if (empty($nombres) || mb_strlen($nombres) < 2) {
                $errors[] = 'Nombre obligatorio (mín. 2 caracteres).';
            }
            if (empty($apellidos) || mb_strlen($apellidos) < 2) {
                $errors[] = 'Apellido obligatorio (mín. 2 caracteres).';
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Email inválido.';
            }

            $titulosValidos = ['profesor', 'licenciado', 'ingeniero', 'magíster', 'doctor', 'abogado', 'especialista'];
            if (!in_array(mb_strtolower($titulo), $titulosValidos, true)) {
                $errors[] = 'Título académico inválido.';
            }

            if (!empty($errors)) {
                echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
                exit;
            }

            // ── 4. Verificar duplicados ──
            $model = new ProfesoresModel();

            if ($model->cedulaExists($nacionalidad, $cedula)) {
                echo json_encode(['success' => false, 'message' => 'Ya existe una persona registrada con esa cédula.']);
                exit;
            }
            if ($model->emailExists($email)) {
                echo json_encode(['success' => false, 'message' => 'Ya existe un usuario registrado con ese email.']);
                exit;
            }

            // ── 5. Crear profesor (transacción) ──
            $result = $model->createProfesor([
                'nacionalidad' => $nacionalidad,
                'cedula'       => $cedula,
                'nombres'      => $nombres,
                'apellidos'    => $apellidos,
                'email'        => $email,
                'titulo'       => mb_strtolower($titulo),
            ]);

            // ── 6. Registrar en bitácora ──
            BitacoraModel::registrar(
                BitacoraModel::PROFESSOR_CREATED,
                'usuarios',
                null, // tomará user_id de sesión (admin)
                $email,
                'users',
                $result['user_id'],
                "Profesor creado: $nombres $apellidos ($nacionalidad-$cedula)"
            );

            // ── 7. Enviar correo de bienvenida (no bloquea) ──
            $emailSent = false;
            try {
                $baseUrl = rtrim($_ENV['APP_BASE'] ?? 'http://localhost/tesis_francisco', '/');
                $emailBody = $this->buildWelcomeEmail($nombres, $apellidos, $email, $cedula, $baseUrl);
                $emailSent = Mailer::send($email, 'Bienvenido al SPDSS — Su cuenta ha sido creada', $emailBody);
            } catch (\Throwable $mailError) {
                error_log('[ProfesoresController::guardar] Error al enviar correo: ' . $mailError->getMessage());
            }

            // ── 8. Respuesta ──
            $message = 'Profesor registrado exitosamente.';
            if (!$emailSent) {
                $message .= ' (El correo de bienvenida no pudo ser enviado)';
            }

            echo json_encode(['success' => true, 'message' => $message]);
            exit;

        } catch (\Throwable $e) {
            error_log('[ProfesoresController::guardar] ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno al crear el profesor.']);
            exit;
        }
    }

    /**
     * Cambia el estado del profesor: active ↔ inactive (AJAX, POST)
     */
    public function eliminar()
    {
        header('Content-Type: application/json');

        try {
            // ── CSRF ──
            if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
                echo json_encode(['success' => false, 'message' => 'Token de seguridad inválido.']);
                exit;
            }

            $userId = (int) ($_POST['user_id'] ?? 0);
            $action = $_POST['action'] ?? ''; // 'activate' | 'deactivate'

            if ($userId <= 0 || !in_array($action, ['activate', 'deactivate'], true)) {
                echo json_encode(['success' => false, 'message' => 'Parámetros inválidos.']);
                exit;
            }

            $newStatus = $action === 'activate' ? 'active' : 'inactive';

            $model = new ProfesoresModel();
            $updated = $model->toggleStatus($userId, $newStatus);

            if (!$updated) {
                echo json_encode(['success' => false, 'message' => 'No se pudo actualizar el estado del profesor.']);
                exit;
            }

            // Bitácora
            BitacoraModel::registrar(
                BitacoraModel::USER_STATUS_CHANGED,
                'usuarios',
                null,
                null,
                'users',
                $userId,
                "Estado cambiado a: $newStatus"
            );

            $label = $action === 'activate' ? 'activado' : 'desactivado';
            echo json_encode(['success' => true, 'message' => "El profesor ha sido $label correctamente."]);
            exit;

        } catch (\Throwable $e) {
            error_log('[ProfesoresController::eliminar] ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno al cambiar el estado.']);
            exit;
        }
    }

    // =========================================================
    // HELPERS PRIVADOS
    // =========================================================

    /**
     * Construye el cuerpo HTML del correo de bienvenida.
     */
    private function buildWelcomeEmail(
        string $nombres,
        string $apellidos,
        string $email,
        string $cedula,
        string $baseUrl
    ): string {
        return "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <div style='background: linear-gradient(135deg, #1a237e, #283593); color: white; padding: 30px; border-radius: 10px 10px 0 0; text-align: center;'>
                <h1 style='margin: 0; font-size: 24px;'>🎓 Bienvenido al SPDSS</h1>
                <p style='margin: 10px 0 0; opacity: 0.9;'>Sistema Pedagógico de Declaración Sucesoral Simulada</p>
            </div>
            <div style='background: #ffffff; padding: 30px; border: 1px solid #e0e0e0; border-top: none;'>
                <p style='font-size: 16px;'>Estimado/a <strong>$nombres $apellidos</strong>,</p>
                <p>Su cuenta de profesor ha sido creada exitosamente en el SPDSS. A continuación encontrará sus datos de acceso:</p>
                
                <div style='background: #f5f5f5; border-left: 4px solid #1a237e; padding: 15px; margin: 20px 0; border-radius: 0 5px 5px 0;'>
                    <p style='margin: 5px 0;'><strong>🔗 URL del sistema:</strong> <a href='$baseUrl/login'>$baseUrl/login</a></p>
                    <p style='margin: 5px 0;'><strong>📧 Email:</strong> $email</p>
                    <p style='margin: 5px 0;'><strong>🔑 Contraseña temporal:</strong> Su número de cédula ($cedula)</p>
                </div>

                <div style='background: #fff3e0; border-left: 4px solid #ff9800; padding: 15px; margin: 20px 0; border-radius: 0 5px 5px 0;'>
                    <p style='margin: 0; font-weight: bold; color: #e65100;'>⚠️ Importante</p>
                    <p style='margin: 10px 0 0;'>Al iniciar sesión por primera vez, el sistema le solicitará:</p>
                    <ul style='margin: 10px 0;'>
                        <li>Completar su fecha de nacimiento</li>
                        <li>Establecer una nueva contraseña segura</li>
                    </ul>
                </div>

                <p style='color: #666; font-size: 13px; margin-top: 30px; border-top: 1px solid #eee; padding-top: 15px;'>
                    Este es un correo automático del SPDSS. Si no solicitó esta cuenta, puede ignorar este mensaje.
                </p>
            </div>
        </div>";
    }
}

