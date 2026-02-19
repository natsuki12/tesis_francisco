<?php
namespace App\Modules\Auth\Services;

use App\Core\Mailer;
use App\Modules\Auth\Models\RegisterModel;

class RegisterService
{
    private RegisterModel $model;

    public function __construct(RegisterModel $model)
    {
        $this->model = $model;
    }

    /**
     * Guarda datos flash en sesión (se consumen una sola vez en la vista).
     */
    private function flash(string $key, string $value): void
    {
        $_SESSION['flash_' . $key] = $value;
    }

    public function getCurrentStep(): int
    {
        return (int) ($_SESSION['register_step'] ?? 1);
    }

    public function getSeccionesActivas(): array
    {
        return $this->model->getSeccionesActivas();
    }

    // =========================================================
    // PASO 1
    // =========================================================
    public function handleRegisterData(string $nacionalidad, string $cedula, string $email): string
    {
        // 🧹 LIMPIEZA PREVENTIVA
        unset($_SESSION['code_verified'], $_SESSION['verified_email']);

        // 📝 Guardar datos enviados para repoblar el formulario si hay error
        $_SESSION['flash_old'] = [
            'nacionalidad' => $nacionalidad,
            'cedula'       => $cedula,
            'email'        => $email,
        ];

        // ⏱️ RATE LIMITING
        $lastSent = (int) ($_SESSION['last_email_sent_at'] ?? 0);
        if (time() - $lastSent < 60) {
            $segundos = max(0, (int) (60 - (time() - $lastSent)));
            $this->flash('vista', 'datos');
            $this->flash('error', 'espere_tiempo');
            $this->flash('seg', (string) $segundos);
            return "/registro";
        }

        // VALIDACIÓN DE FORMATO
        if (!in_array($nacionalidad, ['V', 'E'], true)) {
            $this->flash('vista', 'datos');
            $this->flash('error', 'nacionalidad_invalida');
            return "/registro";
        }
        if (!preg_match('/^\d{6,10}$/', $cedula)) {
            $this->flash('vista', 'datos');
            $this->flash('error', 'cedula_invalida');
            return "/registro";
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->flash('vista', 'datos');
            $this->flash('error', 'email_invalido');
            return "/registro";
        }

        // Validar duplicados (misma lógica)
        if ($this->model->personaExistsByCedula($nacionalidad, $cedula)) {
            $this->flash('vista', 'datos');
            $this->flash('error', 'cedula_existe');
            return "/registro";
        }
        if ($this->model->userExistsByEmail($email)) {
            $this->flash('vista', 'datos');
            $this->flash('error', 'email_existe');
            return "/registro";
        }

        // Guardar temporalmente
        $_SESSION['temp_user'] = [
            'nacionalidad' => $nacionalidad,
            'cedula'       => $cedula,
            'email'        => $email,
            'rol'          => 'Estudiante'
        ];

        // Generar código seguro
        try {
            $codigo = (string) random_int(100000, 999999);
        } catch (\Exception $e) {
            error_log("CSPRNG Error: " . $e->getMessage());
            $this->flash('vista', 'datos');
            $this->flash('error', 'error_interno');
            return "/registro";
        }

        // Configurar sesión
        $_SESSION['verification_code'] = $codigo;
        $_SESSION['verification_expires_at'] = time() + (10 * 60); // 10 min
        $_SESSION['verification_attempts'] = 0;

        // Enviar correo (mismo HTML)
        $asunto = "Código de Verificación - Simulador SENIAT";
        $mensajeHTML = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 10px;'>
                <h2 style='color: #004085; text-align: center;'>Verificación de Cuenta</h2>
                <p>Su código de seguridad es:</p>
                <div style='background-color: #f8f9fa; padding: 15px; text-align: center; margin: 20px 0;'>
                    <strong style='font-size: 32px; letter-spacing: 5px; color: #0d6efd;'>{$codigo}</strong>
                </div>
                <p style='color: #777; font-size: 12px;'>Válido por 10 minutos.</p>
            </div>
        ";

        if (Mailer::send($email, $asunto, $mensajeHTML)) {
            $_SESSION['last_email_sent_at'] = time();
            $_SESSION['register_step'] = 2;
            unset($_SESSION['flash_old']); // Ya no necesitamos repoblar
            return "/registro";
        }

        // Falló envío
        $this->clearRegistrationSessionKeepRateLimit();
        $this->flash('vista', 'datos');
        $this->flash('error', 'fallo_envio');
        return "/registro";
    }

    // =========================================================
    // PASO 2
    // =========================================================
    public function handleVerifyCode(string $inputCode): string
    {
        if (($this->getCurrentStep()) !== 2) {
            $this->flash('vista', 'datos');
            $this->flash('error', 'flujo_invalido');
            return "/registro";
        }

        $realCode = $_SESSION['verification_code'] ?? null;
        $expires  = (int) ($_SESSION['verification_expires_at'] ?? 0);

        // Validación formato
        if (!preg_match('/^\d{6}$/', $inputCode)) {
            $this->flash('error', 'codigo_formato_invalido');
            return "/registro";
        }

        // Sesión expirada
        if (!$realCode) {
            $this->clearRegistrationSessionKeepRateLimit();
            $this->flash('vista', 'datos');
            $this->flash('error', 'sesion_expirada');
            return "/registro";
        }

        // Tiempo expirado
        if (time() > $expires) {
            $this->clearRegistrationSessionKeepRateLimit();
            $this->flash('vista', 'datos');
            $this->flash('error', 'codigo_expirado');
            return "/registro";
        }

        // Intentos
        $_SESSION['verification_attempts'] = (int) ($_SESSION['verification_attempts'] ?? 0) + 1;
        if ($_SESSION['verification_attempts'] > 5) {
            $this->clearRegistrationSessionKeepRateLimit();
            $this->flash('vista', 'datos');
            $this->flash('error', 'demasiados_intentos');
            return "/registro";
        }

        // Comparación segura
        if (hash_equals((string) $realCode, (string) $inputCode)) {

            if (empty($_SESSION['temp_user']['email'])) {
                $this->clearRegistrationSessionKeepRateLimit();
                $this->flash('vista', 'datos');
                $this->flash('error', 'sesion_expirada');
                return "/registro";
            }

            // Regenerar ID de sesión
            session_regenerate_id(true);

            // Binding y avance
            $_SESSION['verified_email'] = $_SESSION['temp_user']['email'];
            $_SESSION['code_verified']  = true;
            $_SESSION['register_step']  = 3;

            // Limpiamos SOLO verificación
            unset($_SESSION['verification_code'], $_SESSION['verification_expires_at'], $_SESSION['verification_attempts']);

            return "/registro";
        }

        $this->flash('error', 'codigo_invalido');
        return "/registro";
    }

    // =========================================================
    // PASO 3
    // =========================================================
    public function handlePersonalData(array $p): string
    {
        if (($this->getCurrentStep()) !== 3) {
            $this->flash('vista', 'datos');
            $this->flash('error', 'flujo_invalido');
            return "/registro";
        }

        // Anti-Bypass
        if (
            empty($_SESSION['code_verified']) ||
            empty($_SESSION['temp_user']) ||
            (($_SESSION['verified_email'] ?? '') !== ($_SESSION['temp_user']['email'] ?? ''))
        ) {
            $this->clearRegistrationSessionKeepRateLimit();
            $this->flash('vista', 'datos');
            $this->flash('error', 'acceso_ilegal');
            return "/registro";
        }

        $tempUser  = $_SESSION['temp_user'];

        $nombres   = (string) ($p['nombres'] ?? '');
        $apellidos = (string) ($p['apellidos'] ?? '');
        $fechaNac  = (string) ($p['fecha_nacimiento'] ?? '');
        $genero    = (string) ($p['genero'] ?? '');
        $seccionId = (int)    ($p['seccion'] ?? 0);
        $pass      = (string) ($p['password'] ?? '');
        $passConf  = (string) ($p['password_confirm'] ?? '');

        // 📝 Guardar datos enviados para repoblar el formulario si hay error (sin contraseñas)
        $_SESSION['flash_old'] = [
            'nombres'          => $nombres,
            'apellidos'        => $apellidos,
            'fecha_nacimiento' => $fechaNac,
            'genero'           => $genero,
            'seccion'          => $seccionId,
        ];

        if (strlen($pass) < 8 || !preg_match('/\d/', $pass)) {
            $this->flash('error', 'pass_debil');
            return "/registro";
        }
        if ($pass !== $passConf) {
            $this->flash('error', 'pass_mismatch');
            return "/registro";
        }
        if ($seccionId <= 0) {
            $this->flash('error', 'seccion_invalida');
            return "/registro";
        }

        $dt = \DateTime::createFromFormat('Y-m-d', $fechaNac);
        if (!$dt || $dt->format('Y-m-d') !== $fechaNac) {
            $this->flash('error', 'fecha_invalida');
            return "/registro";
        }

        try {
            $this->model->createFullStudentRegistration([
                'temp_user'  => $tempUser,
                'nombres'    => $nombres,
                'apellidos'  => $apellidos,
                'fechaNac'   => $fechaNac,
                'genero'     => $genero,
                'seccionId'  => $seccionId,
                'password'   => $pass,
            ]);

            // Regenerar sesión final
            session_regenerate_id(true);

            // Limpieza final exitosa + borrar rate limit (misma mejora 2)
            $this->clearRegistrationSessionALL();
            unset($_SESSION['last_email_sent_at']);

            // ⚡ FLASH MESSAGE
            $_SESSION['flash_message'] = [
                'type' => 'success',
                'message' => '¡Registro exitoso! Por favor inicia sesión.'
            ];

            return "/login";

        } catch (\PDOException $e) {
            error_log("DB Error: " . $e->getMessage());

            if ($e->getCode() === '23000') {
                $this->clearRegistrationSessionKeepRateLimit();
                $this->flash('vista', 'datos');
                $this->flash('error', 'usuario_duplicado');
                return "/registro";
            }

            $this->flash('error', 'error_db');
            return "/registro";

        } catch (\Exception $e) {
            error_log("General Error: " . $e->getMessage());
            $msg = ($e->getMessage() === "Sección no existe") ? "seccion_invalida" : "error_general";
            $this->flash('error', $msg);
            return "/registro";
        }
    }

    // =========================================================
    // REENVIAR CÓDIGO
    // =========================================================
    public function handleResendCode(): string
    {
        if (($this->getCurrentStep()) !== 2) {
            $this->flash('vista', 'datos');
            $this->flash('error', 'flujo_invalido');
            return "/registro";
        }

        // Recuperar datos de sesión
        $tempUser = $_SESSION['temp_user'] ?? [];
        $email    = $tempUser['email'] ?? null;
        $cedula   = $tempUser['cedula'] ?? null;
        
        if (!$email || !$cedula) {
            $this->clearRegistrationSessionKeepRateLimit();
            $this->flash('vista', 'datos');
            $this->flash('error', 'sesion_expirada');
            return "/registro";
        }

        // ⏱️ RATE LIMITING (Mismo contador que el registro inicial)
        $lastSent = (int) ($_SESSION['last_email_sent_at'] ?? 0);
        if (time() - $lastSent < 60) {
            $segundos = max(0, (int) (60 - (time() - $lastSent)));
            $this->flash('error', 'espere_tiempo');
            $this->flash('seg', (string) $segundos);
            return "/registro";
        }

        // Generar nuevo código
        try {
            $codigo = (string) random_int(100000, 999999);
        } catch (\Exception $e) {
            error_log("CSPRNG Error: " . $e->getMessage());
            $this->flash('vista', 'datos');
            $this->flash('error', 'error_interno');
            return "/registro";
        }

        // Actualizar sesión
        $_SESSION['verification_code'] = $codigo;
        $_SESSION['verification_expires_at'] = time() + (10 * 60);
        $_SESSION['verification_attempts'] = 0; // Reiniciar intentos

        // Enviar correo (Mismo HTML, quizás cambiar título si se desea)
        $asunto = "Nuevo Código de Verificación - Simulador SENIAT";
        $mensajeHTML = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 10px;'>
                <h2 style='color: #004085; text-align: center;'>Solicitud de Nuevo Código</h2>
                <p>Su nuevo código de seguridad es:</p>
                <div style='background-color: #f8f9fa; padding: 15px; text-align: center; margin: 20px 0;'>
                    <strong style='font-size: 32px; letter-spacing: 5px; color: #0d6efd;'>{$codigo}</strong>
                </div>
                <p style='color: #777; font-size: 12px;'>Válido por 10 minutos.</p>
            </div>
        ";

        if (Mailer::send($email, $asunto, $mensajeHTML)) {
            $_SESSION['last_email_sent_at'] = time();
            $this->flash('success_resend', 'Código reenviado exitosamente.');
            return "/registro";
        }

        $this->flash('vista', 'datos');
        $this->flash('error', 'fallo_envio');
        return "/registro";
    }

    // =========================================================
    // BACK
    // =========================================================
    public function handleBack(): string
    {
        $currentStep = $this->getCurrentStep();
        $_SESSION['register_step'] = max(1, $currentStep - 1);

        if ($currentStep === 2) {
            $this->flash('vista', 'datos');
            return "/registro";
        }

        return "/registro";
    }

    /**
     * Limpia sesión de registro PERO mantiene last_email_sent_at (anti-spam).
     * Igual a tu comentario original.
     */
    private function clearRegistrationSessionKeepRateLimit(): void
    {
        unset(
            $_SESSION['temp_user'],
            $_SESSION['register_step'],
            $_SESSION['code_verified'],
            $_SESSION['verified_email'],
            $_SESSION['verification_code'],
            $_SESSION['verification_expires_at'],
            $_SESSION['verification_attempts']
        );
    }

    /**
     * Limpia TODO (incluye el flujo completo). Útil al finalizar OK.
     */
    private function clearRegistrationSessionALL(): void
    {
        unset(
            $_SESSION['temp_user'],
            $_SESSION['register_step'],
            $_SESSION['code_verified'],
            $_SESSION['verified_email'],
            $_SESSION['verification_code'],
            $_SESSION['verification_expires_at'],
            $_SESSION['verification_attempts']
        );
    }
}
