<?php
declare(strict_types=1);

namespace App\Modules\Admin\Controllers\Usuarios;

use App\Core\App;

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
        return $this->app->view('admin/usuarios/gestionar_profesores');
    }

    /**
     * Guarda o actualiza un Profesor (AJAX)
     */
    public function guardar()
    {
        // TODO: Validate CSRF and inputs
        // TODO: Database insertion/update logic

        $id = $_POST['id'] ?? null;
        $isEdit = !empty($id);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => $isEdit ? 'Profesor actualizado correctamente.' : 'Profesor registrado exitosamente. Se ha enviado un correo con las credenciales.'
        ]);
        exit;
    }

    /**
     * Cambia el estado del profesor a inactivo (AJAX)
     */
    public function eliminar()
    {
        // TODO: Validate CSRF and inputs
        // TODO: Database update logic

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'El profesor ha sido desactivado del sistema.'
        ]);
        exit;
    }
}
