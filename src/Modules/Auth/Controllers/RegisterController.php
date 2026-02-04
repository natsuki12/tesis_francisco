<?php
namespace App\Modules\Auth\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\DB;
use App\Core\Mailer;

class RegisterController extends Controller {

    // GET: /registro
    public function index() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        // Si se borró la sesión (reset), esto devuelve 1 automáticamente.
        $step = $_SESSION['register_step'] ?? 1;

        if ($step === 1) {
            return $this->view('auth/register', ['currentStep' => 1]); 
        } elseif ($step === 2) {
            return $this->view('auth/register_part_2', ['currentStep' => 3]);
        } else {
            // Cargar secciones disponibles
            $db = DB::connect();
            $stmt = $db->query("
                SELECT s.id, s.nombre as seccion, m.nombre as materia 
                FROM secciones s 
                INNER JOIN materias m ON s.materia_id = m.id 
                INNER JOIN periodos p ON s.periodo_id = p.id
                WHERE p.activo = 1
                ORDER BY m.nombre ASC, s.nombre ASC
            ");
            $secciones = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return $this->view('auth/register_part_3', [
                'currentStep' => 4,
                'secciones' => $secciones
            ]);
        }
    }

    // POST: /registro
    public function process() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        // 🛡️ SEGURIDAD CSRF GLOBAL
        $token = $this->input('csrf_token');
        if (!Csrf::verify($token)) {
            http_response_code(419);
            die("Error de seguridad: Token inválido o expirado.");
        }

        $action = $this->input('action');

        // =========================================================
        // PASO 1: DATOS BÁSICOS -> ENVÍO DE CÓDIGO
        // =========================================================
        if ($action === 'register_data') {
            
            // 🧹 LIMPIEZA PREVENTIVA
            unset($_SESSION['code_verified'], $_SESSION['verified_email']);

            // ⏱️ RATE LIMITING
            $lastSent = $_SESSION['last_email_sent_at'] ?? 0;
            if (time() - $lastSent < 60) {
                // (Mejora 1) max() para evitar números negativos por micro-desfases
                $segundos = max(0, (int) (60 - (time() - $lastSent)));
                $this->redirect("/registro?vista=datos&error=espere_tiempo&seg=$segundos");
            }
            
            $nacionalidad = $this->inputString('nacionalidad');
            $cedula       = $this->inputString('cedula');
            $email        = $this->inputString('email');

            // VALIDACIÓN DE FORMATO
            if (!in_array($nacionalidad, ['V', 'E'], true)) {
                $this->redirect('/registro?vista=datos&error=nacionalidad_invalida');
            }
            if (!preg_match('/^\d{6,10}$/', $cedula)) {
                $this->redirect('/registro?vista=datos&error=cedula_invalida');
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->redirect('/registro?vista=datos&error=email_invalido');
            }
            
            $db = DB::connect();

            // Validar duplicados
            $stmt = $db->prepare("SELECT id FROM personas WHERE nacionalidad = ? AND cedula = ? LIMIT 1");
            $stmt->execute([$nacionalidad, $cedula]);
            if ($stmt->fetch()) $this->redirect('/registro?vista=datos&error=cedula_existe');

            $stmt = $db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            if ($stmt->fetch()) $this->redirect('/registro?vista=datos&error=email_existe');

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
                $this->redirect('/registro?vista=datos&error=error_interno');
            }

            // Configurar sesión
            $_SESSION['verification_code'] = $codigo;
            $_SESSION['verification_expires_at'] = time() + (10 * 60); // 10 min
            $_SESSION['verification_attempts'] = 0; 

            // Enviar correo
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
                $this->redirect('/registro');
            } else {
                $this->clearRegistrationSession();
                $this->redirect('/registro?vista=datos&error=fallo_envio');
            }
        }

        // =========================================================
        // PASO 2: VERIFICACIÓN DEL CÓDIGO
        // =========================================================
        elseif ($action === 'verify_code') {
            
            if (($_SESSION['register_step'] ?? 1) !== 2) {
                $this->redirect('/registro?vista=datos&error=flujo_invalido');
            }

            $inputCode = $this->inputString('codigo_verificacion');
            $realCode  = $_SESSION['verification_code'] ?? null;
            $expires   = $_SESSION['verification_expires_at'] ?? 0;
            
            // Validación Formato
            if (!preg_match('/^\d{6}$/', $inputCode)) {
                $this->redirect('/registro?error=codigo_formato_invalido');
            }

            // A. Validación de Sesión Expirada
            if (!$realCode) {
                $this->clearRegistrationSession();
                $this->redirect('/registro?vista=datos&error=sesion_expirada');
            }

            // B. Validación de Tiempo Expirado
            if (time() > $expires) {
                $this->clearRegistrationSession();
                $this->redirect('/registro?vista=datos&error=codigo_expirado');
            }

            // C. Límite de Intentos
            $_SESSION['verification_attempts'] = ($_SESSION['verification_attempts'] ?? 0) + 1;
            
            if ($_SESSION['verification_attempts'] > 5) {
                $this->clearRegistrationSession();
                $this->redirect('/registro?vista=datos&error=demasiados_intentos');
            }

            // D. Comparación Segura
            if (hash_equals((string)$realCode, (string)$inputCode)) {
                
                // Verificar temp_user antes de usarlo
                if (empty($_SESSION['temp_user']['email'])) {
                    $this->clearRegistrationSession();
                    $this->redirect('/registro?vista=datos&error=sesion_expirada');
                }

                // Regenerar ID de sesión
                session_regenerate_id(true);

                // Binding y avance
                $_SESSION['verified_email'] = $_SESSION['temp_user']['email'];
                $_SESSION['code_verified'] = true; 
                $_SESSION['register_step'] = 3;
                
                // Limpiamos SOLO lo de verificación
                unset($_SESSION['verification_code'], $_SESSION['verification_expires_at'], $_SESSION['verification_attempts']);
                
                $this->redirect('/registro');
            } else {
                $this->redirect('/registro?error=codigo_invalido');
            }
        }
        
        // =========================================================
        // PASO 3: GUARDADO FINAL
        // =========================================================
        elseif ($action === 'personal_data') {
            
            if (($_SESSION['register_step'] ?? 1) !== 3) {
                $this->redirect('/registro?vista=datos&error=flujo_invalido');
            }

            // Anti-Bypass
            if (empty($_SESSION['code_verified']) || 
                empty($_SESSION['temp_user']) || 
                ($_SESSION['verified_email'] ?? '') !== $_SESSION['temp_user']['email']
            ) {
                $this->clearRegistrationSession();
                $this->redirect('/registro?vista=datos&error=acceso_ilegal');
            }

            $tempUser  = $_SESSION['temp_user'];
            $nombres   = $this->inputString('nombres');
            $apellidos = $this->inputString('apellidos');
            $fechaNac  = $this->inputString('fecha_nacimiento');
            $genero    = $this->inputString('genero');
            $seccionId = (int) $this->input('seccion');
            $pass      = $this->inputString('password');
            $passConf  = $this->inputString('password_confirm');

            if (strlen($pass) < 8 || !preg_match('/\d/', $pass)) {
                $this->redirect('/registro?error=pass_debil');
            }
            if ($pass !== $passConf) $this->redirect('/registro?error=pass_mismatch');
            if ($seccionId <= 0) $this->redirect('/registro?error=seccion_invalida');

            $dt = \DateTime::createFromFormat('Y-m-d', $fechaNac);
            if (!$dt || $dt->format('Y-m-d') !== $fechaNac) {
                $this->redirect('/registro?error=fecha_invalida');
            }

            $db = DB::connect();
            
            try {
                // Validar FK
                $stmt = $db->prepare("SELECT id FROM secciones WHERE id = ? LIMIT 1");
                $stmt->execute([$seccionId]);
                if (!$stmt->fetch()) throw new \Exception("Sección no existe");

                $stmt = $db->query("SELECT id FROM roles WHERE nombre = 'Estudiante' LIMIT 1");
                $rol = $stmt->fetch();
                if (!$rol) throw new \RuntimeException("Rol no configurado");
                $rolId = (int)$rol['id'];

                // 🚀 TRANSACCIÓN
                $db->beginTransaction();

                $sqlPersona = "INSERT INTO personas (nacionalidad, cedula, nombres, apellidos, fecha_nacimiento, genero, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())";
                $stmt = $db->prepare($sqlPersona);
                $stmt->execute([$tempUser['nacionalidad'], $tempUser['cedula'], $nombres, $apellidos, $fechaNac, $genero]);
                $personaId = $db->lastInsertId();

                $passHash = password_hash($pass, PASSWORD_DEFAULT);
                $sqlUser = "INSERT INTO users (persona_id, role_id, email, password, status, created_at) VALUES (?, ?, ?, ?, 'active', NOW())";
                $stmt = $db->prepare($sqlUser);
                $stmt->execute([$personaId, $rolId, $tempUser['email'], $passHash]);

                $carreraId = 1; 
                $sqlEstudiante = "INSERT INTO estudiantes (persona_id, carrera_id, created_at) VALUES (?, ?, NOW())";
                $stmt = $db->prepare($sqlEstudiante);
                $stmt->execute([$personaId, $carreraId]);
                $estudianteId = $db->lastInsertId();

                $sqlInscripcion = "INSERT INTO inscripciones (estudiante_id, seccion_id, created_at) VALUES (?, ?, NOW())";
                $stmt = $db->prepare($sqlInscripcion);
                $stmt->execute([$estudianteId, $seccionId]);

                $db->commit();

                // Regenerar sesión final
                session_regenerate_id(true);

                // Limpieza Final Exitosa
                $this->clearRegistrationSession();
                // (Mejora 2) Borrar Rate Limit tras éxito total
                unset($_SESSION['last_email_sent_at']);
                
                $this->redirect('/login?registro=exito');

            } catch (\PDOException $e) {
                if ($db->inTransaction()) $db->rollBack();
                error_log("DB Error: " . $e->getMessage());
                
                if ($e->getCode() === '23000') {
                    $this->clearRegistrationSession();
                    $this->redirect('/registro?vista=datos&error=usuario_duplicado');
                } else {
                    $this->redirect('/registro?error=error_db');
                }

            } catch (\Exception $e) {
                if ($db->inTransaction()) $db->rollBack();
                error_log("General Error: " . $e->getMessage());
                
                $msg = ($e->getMessage() === "Sección no existe") ? 'seccion_invalida' : 'error_general';
                $this->redirect("/registro?error=$msg");
            }
        }
        
        // =========================================================
        // MANEJO DE ACCIÓN INVÁLIDA (Bad Request)
        // =========================================================
        else {
            http_response_code(400);
            die("Error 400: Acción no válida.");
        }
    }

    public function back() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $currentStep = $_SESSION['register_step'] ?? 1;
        $_SESSION['register_step'] = max(1, $currentStep - 1);
        
        if ($currentStep === 2) {
            $this->redirect('/registro?vista=datos');
        } else {
            $this->redirect('/registro');
        }
    }

    /**
     * Helper privado para limpiar la sesión de registro.
     * ⚠️ IMPORTANTE: No borramos 'last_email_sent_at' para mantener
     * el Rate Limit activo si el usuario falla o reinicia.
     * Solo se borra manualmente si el registro es exitoso.
     */
    private function clearRegistrationSession(): void
    {
        unset(
            $_SESSION['temp_user'],
            $_SESSION['register_step'],
            $_SESSION['code_verified'],
            $_SESSION['verified_email'],
            // $_SESSION['last_email_sent_at'], // PERSISTENTE (Anti-Spam)
            $_SESSION['verification_code'],
            $_SESSION['verification_expires_at'],
            $_SESSION['verification_attempts']
        );
    }
}