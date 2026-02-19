<?php
namespace App\Modules\Auth\Controllers;

use App\Core\Controller;
use App\Core\App;
use App\Core\Csrf;
use App\Modules\Auth\services\RegisterService;
use App\Modules\Auth\models\RegisterModel;

class RegisterController extends Controller
{
    private RegisterService $service;

    public function __construct(App $app)
    {
        parent::__construct($app);
        // Service con su Model
        $this->service = new RegisterService(new RegisterModel());
    }

    // GET: /registro
    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $step = $this->service->getCurrentStep();

        if ($step === 1) {
            return $this->view('auth/register', ['currentStep' => 1]);
        }

        if ($step === 2) {
            return $this->view('auth/register_part_2', ['currentStep' => 3]);
        }

        // step >= 3 -> cargar secciones activas
        $secciones = $this->service->getSeccionesActivas();

        return $this->view('auth/register_part_3', [
            'currentStep' => 4,
            'secciones'   => $secciones
        ]);
    }

    // POST: /registro
    public function process()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        // 🛡️ CSRF GLOBAL
        $token = $this->input('csrf_token');
        if (!Csrf::verify($token)) {
            http_response_code(419);
            die("Error de seguridad: Token inválido o expirado.");
        }

        $action = $this->input('action');

        switch ($action) {

            // =========================================================
            // PASO 1: DATOS BÁSICOS -> ENVÍO DE CÓDIGO
            // =========================================================
            case 'register_data': {
                $nacionalidad = $this->inputString('nacionalidad');
                $cedula       = $this->inputString('cedula');
                $email        = $this->inputString('email');

                $redirectUrl = $this->service->handleRegisterData($nacionalidad, $cedula, $email);
                $this->redirect($redirectUrl);
                break;
            }

            // =========================================================
            // PASO 2: VERIFICACIÓN DEL CÓDIGO
            // =========================================================
            case 'verify_code': {
                $inputCode = $this->inputString('codigo_verificacion');

                $redirectUrl = $this->service->handleVerifyCode($inputCode);
                $this->redirect($redirectUrl);
                break;
            }

            // =========================================================
            // REENVIAR CÓDIGO
            // =========================================================
            case 'resend_code': {
                $redirectUrl = $this->service->handleResendCode();
                $this->redirect($redirectUrl);
                break;
            }

            // =========================================================
            // PASO 3: GUARDADO FINAL
            // =========================================================
            case 'personal_data': {
                $payload = [
                    'nombres'          => $this->inputString('nombres'),
                    'apellidos'        => $this->inputString('apellidos'),
                    'fecha_nacimiento' => $this->inputString('fecha_nacimiento'),
                    'genero'           => $this->inputString('genero'),
                    'seccion'          => (int) $this->input('seccion'),
                    'password'         => $this->inputString('password'),
                    'password_confirm' => $this->inputString('password_confirm'),
                ];

                $redirectUrl = $this->service->handlePersonalData($payload);
                $this->redirect($redirectUrl);
                break;
            }

            default:
                http_response_code(400);
                die("Error 400: Acción no válida.");
        }
    }

    public function back()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $redirectUrl = $this->service->handleBack();
        $this->redirect($redirectUrl);
    }
}
