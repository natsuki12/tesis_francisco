<?php

namespace App\Modules\Admin\Controllers\Usuarios;

class EstudiantesController
{
    /**
     * Muestra la vista de gestión administrativa de estudiantes.
     * Mapeado a la ruta: GET /admin/estudiantes
     */
    public function index()
    {
        // En una implementación real, aquí se obtendrían de la BD,
        // ej: $estudiantes = EstudianteModel::where('rol', 3)->paginate(15);
        $estudiantes = [];

        require_once __DIR__ . '/../../../../../resources/views/admin/usuarios/gestionar_estudiantes.php';
    }
}
