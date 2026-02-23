<?php
namespace App\Modules\Auth\Controllers;

use App\Core\Controller;
use App\Core\App;
use App\Core\Csrf;
use App\Modules\Auth\Services\PasswordRecoveryService;
use App\Modules\Auth\Models\PasswordRecoveryModel;

class PasswordRecoveryController extends Controller
{
    private PasswordRecoveryService $service;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->service = new PasswordRecoveryService(new PasswordRecoveryModel());
    }

    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        // Si ya hay sesión, redirigir al home
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/home');
        }

        return $this->view('auth/password_recovery', [
            'step' => 'email',
            'flashMessage' => $_SESSION['flash_message'] ?? null
        ]);
        // Limpiamos mensaje flash después de usarlo en la vista? 
        // El Controller base o la vista suelen manejarlo, pero aquí lo pasamos explícitamente.
        // Se limpiará en la vista o al recargar.
        if (isset($_SESSION['flash_message'])) unset($_SESSION['flash_message']);
    }

    public function process()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!Csrf::verify($this->input('csrf_token'))) {
            die("Error de seguridad: Token CSRF inválido.");
        }

        $step = $this->input('step');
        $email = $this->input('email');

        if ($step === 'email') {
            $result = $this->service->sendRecoveryCode($email);
            
            if ($result['success']) {
                // Éxito: Avanzar al paso 'code'
                return $this->view('auth/password_recovery', [
                    'step' => 'code',
                    'flashMessage' => ['type' => 'success', 'message' => $result['message']],
                    '_POST' => ['email' => $email] // Para mantener el email en el hidden input
                ]);
            } else {
                // Error: Quedarse en 'email'
                return $this->view('auth/password_recovery', [
                    'step' => 'email',
                    'flashMessage' => ['type' => 'error', 'message' => $result['message']],
                    '_POST' => ['email' => $email]
                ]);
            }
        }

        if ($step === 'resend') {
            $result = $this->service->sendRecoveryCode($email);
            
            // Stay on 'code' step regardless of success/limit, showing the message
            return $this->view('auth/password_recovery', [
                'step' => 'code',
                'flashMessage' => [
                    'type' => $result['success'] ? 'success' : 'error',
                    'message' => $result['message']
                ],
                '_POST' => ['email' => $email]
            ]);
        }

        if ($step === 'code') {
            $code = $this->input('code');
            $result = $this->service->verifyCode($email, $code);

            if ($result['success']) {
                // Éxito: Avanzar al paso 'reset'
                return $this->view('auth/password_recovery', [
                    'step' => 'reset',
                    'flashMessage' => ['type' => 'success', 'message' => $result['message']],
                    '_POST' => ['email' => $email, 'code' => $code]
                ]);
            } else {
                // Error: Quedarse en 'code'
                return $this->view('auth/password_recovery', [
                    'step' => 'code',
                    'flashMessage' => ['type' => 'error', 'message' => $result['message']],
                    '_POST' => ['email' => $email]
                ]);
            }
        }

        if ($step === 'reset') {
            $code = $this->input('code');
            $password = $this->input('password');
            $passwordConfirmation = $this->input('password_confirmation');

            $result = $this->service->resetPassword($email, $code, $password, $passwordConfirmation);

            if ($result['success']) {
                // Éxito: Redirigir al login
                $_SESSION['flash_message'] = ['type' => 'success', 'message' => $result['message']];
                $this->redirect('/login');
            } else {
                // Error: Quedarse en 'reset'
                return $this->view('auth/password_recovery', [
                    'step' => 'reset',
                    'flashMessage' => ['type' => 'error', 'message' => $result['message']],
                    '_POST' => ['email' => $email, 'code' => $code]
                ]);
            }
        }

        // Fallback
        $this->redirect('/password-recovery');
    }
}
