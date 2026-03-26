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
        $subject = 'Código de Recuperación de Contraseña - Simulador SENIAT';
        $body = "
            <div style='font-family: \"Plus Jakarta Sans\", sans-serif; color: #333;'>
                <h2>Recuperación de Contraseña</h2>
                <p>Hola, hemos recibido una solicitud para restablecer tu contraseña en el Simulador SENIAT.</p>
                <p>Tu código de verificación es:</p>
                <div style='background: #f4f6f8; padding: 15px; border-radius: 8px; display: inline-block;'>
                    <h1 style='color: #0052a3; margin: 0; letter-spacing: 5px;'>{$code}</h1>
                </div>
                <p>Este código expira en 15 minutos.</p>
                <hr style='border: 0; border-top: 1px solid #eee; margin: 20px 0;'>
                <p style='font-size: 12px; color: #888;'>Si no solicitaste este cambio, por favor ignora este correo.</p>
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
