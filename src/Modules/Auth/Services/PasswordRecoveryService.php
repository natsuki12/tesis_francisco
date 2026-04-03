<?php
namespace App\Modules\Auth\Services;

use App\Modules\Auth\Models\PasswordRecoveryModel;
use App\Core\MailQueueService;
use App\Core\BitacoraModel;

class PasswordRecoveryService
{
    private PasswordRecoveryModel $model;
    private const RATE_LIMIT_SECONDS = 60;
    private const MAX_ATTEMPTS = 5;

    public function __construct(PasswordRecoveryModel $model)
    {
        $this->model = $model;
    }

    public function sendRecoveryCode(string $email): array
    {
        // 0. RATE LIMITING
        if (session_status() === PHP_SESSION_NONE)
            session_start();

        $lastSent = $_SESSION['recovery_last_sent_at'] ?? 0;
        if (time() - $lastSent < self::RATE_LIMIT_SECONDS) {
            $remaining = self::RATE_LIMIT_SECONDS - (time() - $lastSent);
            return ['success' => false, 'message' => "Por favor espera {$remaining} segundos antes de solicitar otro código."];
        }

        // 1. Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'El correo electrónico no es válido.'];
        }

        // 2. Check user
        $user = $this->model->getUserByEmail($email);
        if (!$user) {
            // Returning error message for better UX in internal tool
            return ['success' => false, 'message' => 'No encontramos un usuario con ese correo electrónico.'];
        }

        if ($user['status'] !== 'active') {
            return ['success' => false, 'message' => 'Tu cuenta no está activa. Contacta al administrador.'];
        }

        // 3. Generate 6-digit code
        // Cryptographically secure random number
        try {
            $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        } catch (\Exception $e) {
            $code = str_pad((string) mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
        }

        // 4. Hash code for storage
        $tokenHash = password_hash($code, PASSWORD_DEFAULT);

        // 5. Store in DB
        if (!$this->model->storeRecoveryToken((int) $user['id'], $tokenHash)) {
            return ['success' => false, 'message' => 'Error interno al generar el código.'];
        }

        // 6. Send Email
        $baseUrl = rtrim($_ENV['APP_BASE'] ?? 'http://localhost/tesis_francisco', '/');

        $subject = 'Código de Recuperación de Contraseña — SUCELAB';
        $body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 0;'>
            <div style='background: linear-gradient(135deg, #1a237e, #283593); color: white; padding: 30px; border-radius: 10px 10px 0 0; text-align: center;'>
                <img src='{$baseUrl}/assets/img/logos/sucelab/logo_Mesa%20de%20trabajo%201-04.png' alt='SUCELAB Logo' style='display: block; margin: 0 auto 15px auto; max-width: 150px; height: auto;'>
                <h1 style='margin: 0; font-size: 24px;'>Recuperación de Contraseña</h1>
                <p style='margin: 10px 0 0; opacity: 0.9;'>Sistema Universitario de Capacitación y Evaluación en Legislación y Administración de Bienes Sucesorales</p>
            </div>
            <div style='background: #ffffff; padding: 30px; border: 1px solid #e0e0e0; border-top: none; border-radius: 0 0 10px 10px;'>
                <p style='font-size: 16px;'>Estimado/a usuario,</p>
                <p>Hemos recibido una solicitud para restablecer su contraseña en el SUCELAB. Utilice el siguiente código de verificación para completar el proceso:</p>

                <div style='background: #f5f5f5; border: 2px solid #1a237e; border-radius: 12px; padding: 24px; margin: 24px 0; text-align: center;'>
                    <p style='margin: 0 0 8px; font-size: 13px; color: #555;'>Su código de verificación es:</p>
                    <strong style='font-size: 32px; letter-spacing: 8px; color: #1a237e;'>{$code}</strong>
                </div>

                <div style='background: #fff3e0; border-left: 4px solid #ff9800; padding: 15px; margin: 20px 0; border-radius: 0 5px 5px 0;'>
                    <p style='margin: 0; font-weight: bold; color: #e65100;'>Importante</p>
                    <p style='margin: 10px 0 0;'>Este código expira en <strong>15 minutos</strong>. Si no solicitó este cambio, puede ignorar este correo de forma segura.</p>
                </div>

                <p style='color: #999; font-size: 12px; margin-top: 30px; border-top: 1px solid #eee; padding-top: 15px; text-align: center;'>
                    Este correo fue generado automáticamente por el SUCELAB — Simulador SENIAT.
                </p>
            </div>
        </div>
        ";

        // Intentar envío directo (sin cola de reintentos)
        MailQueueService::sendDirect($email, $subject, $body, 'reset_password', (int) $user['id']);

        // Siempre retornar éxito: el código ya está en BD y es válido.
        // No podemos garantizar si PHPMailer entregó o no el correo,
        // así que evitamos falsos negativos que confundan al usuario.
        $_SESSION['recovery_last_sent_at'] = time();
        $_SESSION['recovery_attempts'] = 0;

        BitacoraModel::registrar(
            BitacoraModel::PASSWORD_RESET_REQ,
            'autenticacion',
            (int) $user['id'],
            $email
        );

        return ['success' => true, 'message' => 'Código enviado. Revisa tu bandeja de entrada o carpeta de spam.'];
    }

    public function verifyCode(string $email, string $code): array
    {
        if (session_status() === PHP_SESSION_NONE)
            session_start();

        // 0. CHECK MAX ATTEMPTS
        $attempts = $_SESSION['recovery_attempts'] ?? 0;
        if ($attempts >= self::MAX_ATTEMPTS) {
            return ['success' => false, 'message' => 'Has excedido el número máximo de intentos. Solicita un nuevo código.'];
        }

        $user = $this->model->getUserByEmail($email);
        if (!$user) {
            return ['success' => false, 'message' => 'Usuario no válido.'];
        }

        $tokenData = $this->model->getActiveToken((int) $user['id']);
        if (!$tokenData) {
            return ['success' => false, 'message' => 'El código ha expirado o no es válido. Solicita uno nuevo.'];
        }

        if (password_verify($code, $tokenData['token_hash'])) {
            // SUCCESS - Reset attempts? Or keep them? 
            // Better to keep session clean for next steps, maybe reset attempts here.
            $_SESSION['recovery_attempts'] = 0;
            return ['success' => true, 'message' => 'Código verificado.'];
        }

        // FAILURE - Increment attempts
        $_SESSION['recovery_attempts'] = $attempts + 1;
        $remaining = self::MAX_ATTEMPTS - $_SESSION['recovery_attempts'];

        return ['success' => false, 'message' => "Código incorrecto. Te quedan {$remaining} intentos."];
    }

    public function resetPassword(string $email, string $code, string $password, string $confirmPassword): array
    {
        if ($password !== $confirmPassword) {
            return ['success' => false, 'message' => 'Las contraseñas no coinciden.'];
        }

        if (strlen($password) < 8) {
            return ['success' => false, 'message' => 'La contraseña debe tener al menos 8 caracteres.'];
        }

        // Verify code again to be sure (stateless check)
        $verification = $this->verifyCode($email, $code);
        if (!$verification['success']) {
            return $verification;
        }

        $user = $this->model->getUserByEmail($email); // Should exist if verifyCode passed

        // Hash new password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        if ($this->model->updateUserPassword((int) $user['id'], $hashedPassword)) {
            // Invalidate token
            $this->model->markTokenAsUsed((int) $user['id']);

            // Registrar en bitácora
            BitacoraModel::registrar(
                BitacoraModel::PASSWORD_RESET_OK,
                'autenticacion',
                (int) $user['id'],
                $email
            );

            // Clean up session
            unset($_SESSION['recovery_last_sent_at']);
            unset($_SESSION['recovery_attempts']);

            return ['success' => true, 'message' => 'Contraseña actualizada correctamente.'];
        }

        return ['success' => false, 'message' => 'Error al actualizar la contraseña.'];
    }
}
