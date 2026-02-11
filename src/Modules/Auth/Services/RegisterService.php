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

        // ⏱️ RATE LIMITING
        $lastSent = (int) ($_SESSION['last_email_sent_at'] ?? 0);
        if (time() - $lastSent < 60) {
            $segundos = max(0, (int) (60 - (time() - $lastSent)));
            return "/registro?vista=datos&error=espere_tiempo&seg=$segundos";
        }

        // VALIDACIÓN DE FORMATO
        if (!in_array($nacionalidad, ['V', 'E'], true)) {
            return "/registro?vista=datos&error=nacionalidad_invalida";
        }
        if (!preg_match('/^\d{6,10}$/', $cedula)) {
            return "/registro?vista=datos&error=cedula_invalida";
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return "/registro?vista=datos&error=email_invalido";
        }

        // Validar duplicados (misma lógica)
        if ($this->model->personaExistsByCedula($nacionalidad, $cedula)) {
            return "/registro?vista=datos&error=cedula_existe";
        }
        if ($this->model->userExistsByEmail($email)) {
            return "/registro?vista=datos&error=email_existe";
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
            return "/registro?vista=datos&error=error_interno";
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
            return "/registro";
        }

        // Falló envío
        $this->clearRegistrationSessionKeepRateLimit();
        return "/registro?vista=datos&error=fallo_envio";
    }

    // =========================================================
    // PASO 2
    // =========================================================
    public function handleVerifyCode(string $inputCode): string
    {
        if (($this->getCurrentStep()) !== 2) {
            return "/registro?vista=datos&error=flujo_invalido";
        }

        $realCode = $_SESSION['verification_code'] ?? null;
        $expires  = (int) ($_SESSION['verification_expires_at'] ?? 0);

        // Validación formato
        if (!preg_match('/^\d{6}$/', $inputCode)) {
            return "/registro?error=codigo_formato_invalido";
        }

        // Sesión expirada
        if (!$realCode) {
            $this->clearRegistrationSessionKeepRateLimit();
            return "/registro?vista=datos&error=sesion_expirada";
        }

        // Tiempo expirado
        if (time() > $expires) {
            $this->clearRegistrationSessionKeepRateLimit();
            return "/registro?vista=datos&error=codigo_expirado";
        }

        // Intentos
        $_SESSION['verification_attempts'] = (int) ($_SESSION['verification_attempts'] ?? 0) + 1;
        if ($_SESSION['verification_attempts'] > 5) {
            $this->clearRegistrationSessionKeepRateLimit();
            return "/registro?vista=datos&error=demasiados_intentos";
        }

        // Comparación segura
        if (hash_equals((string) $realCode, (string) $inputCode)) {

            if (empty($_SESSION['temp_user']['email'])) {
                $this->clearRegistrationSessionKeepRateLimit();
                return "/registro?vista=datos&error=sesion_expirada";
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

        return "/registro?error=codigo_invalido";
    }

    // =========================================================
    // PASO 3
    // =========================================================
    public function handlePersonalData(array $p): string
    {
        if (($this->getCurrentStep()) !== 3) {
            return "/registro?vista=datos&error=flujo_invalido";
        }

        // Anti-Bypass
        if (
            empty($_SESSION['code_verified']) ||
            empty($_SESSION['temp_user']) ||
            (($_SESSION['verified_email'] ?? '') !== ($_SESSION['temp_user']['email'] ?? ''))
        ) {
            $this->clearRegistrationSessionKeepRateLimit();
            return "/registro?vista=datos&error=acceso_ilegal";
        }

        $tempUser  = $_SESSION['temp_user'];

        $nombres   = (string) ($p['nombres'] ?? '');
        $apellidos = (string) ($p['apellidos'] ?? '');
        $fechaNac  = (string) ($p['fecha_nacimiento'] ?? '');
        $genero    = (string) ($p['genero'] ?? '');
        $seccionId = (int)    ($p['seccion'] ?? 0);
        $pass      = (string) ($p['password'] ?? '');
        $passConf  = (string) ($p['password_confirm'] ?? '');

        if (strlen($pass) < 8 || !preg_match('/\d/', $pass)) {
            return "/registro?error=pass_debil";
        }
        if ($pass !== $passConf) {
            return "/registro?error=pass_mismatch";
        }
        if ($seccionId <= 0) {
            return "/registro?error=seccion_invalida";
        }

        $dt = \DateTime::createFromFormat('Y-m-d', $fechaNac);
        if (!$dt || $dt->format('Y-m-d') !== $fechaNac) {
            return "/registro?error=fecha_invalida";
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

            return "/login?registro=exito";

        } catch (\PDOException $e) {
            error_log("DB Error: " . $e->getMessage());

            if ($e->getCode() === '23000') {
                $this->clearRegistrationSessionKeepRateLimit();
                return "/registro?vista=datos&error=usuario_duplicado";
            }

            return "/registro?error=error_db";

        } catch (\Exception $e) {
            error_log("General Error: " . $e->getMessage());
            $msg = ($e->getMessage() === "Sección no existe") ? "seccion_invalida" : "error_general";
            return "/registro?error=$msg";
        }
    }

    // =========================================================
    // BACK
    // =========================================================
    public function handleBack(): string
    {
        $currentStep = $this->getCurrentStep();
        $_SESSION['register_step'] = max(1, $currentStep - 1);

        if ($currentStep === 2) {
            return "/registro?vista=datos";
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
