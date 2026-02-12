<?php
namespace App\Modules\Auth\Controllers;

use App\Core\Controller;
use App\Core\App;
use App\Core\Csrf;
use App\Modules\Auth\Services\LoginService;
use App\Modules\Auth\Models\LoginModel;

class LoginController extends Controller {


    private LoginService $service;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->service = new LoginService(new LoginModel());
    }

    /**
     * Muestra el formulario de login.
     */
    public function show() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        // Si ya hay sesión, redirigir al dashboard
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/home'); 
        }

        // Recuperar Flash Message
        $flashMessage = $_SESSION['flash_message'] ?? null;
        if ($flashMessage) {
            unset($_SESSION['flash_message']); // Limpiar para que no salga otra vez
        }

        return $this->view('auth/login', [
            'flashMessage' => $flashMessage
        ]);
    }

    /**
     * Procesa los datos del formulario POST
     */
    public function login() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        // 1. Verificar Token CSRF
        $token = $this->input('csrf_token');
        if (!Csrf::verify($token)) {
            die("Error de seguridad: Token inválido (CSRF).");
        }

        // 2. Obtener datos
        $email = $this->inputString('email');
        $password = $this->inputString('password');

        $redirectUrl = $this->service->handleLogin($email, $password);
        $this->redirect($redirectUrl);
    }

    /**
     * Cierra la sesión
     */
    public function logout() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        $redirectUrl = $this->service->handleLogout();
        $this->redirect($redirectUrl);
    }
}