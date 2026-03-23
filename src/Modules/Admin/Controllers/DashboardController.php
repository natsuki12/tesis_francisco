<?php
declare(strict_types=1);

namespace App\Modules\Admin\Controllers;

use App\Modules\Admin\Models\DashboardModel;

/**
 * Controlador del Dashboard Administrativo.
 *
 * Recoge las estadísticas del sistema desde el DashboardModel
 * y las pasa a la vista home_admin.php.
 *
 * ANTI-CRASH: todo el bloque de datos está envuelto en try-catch.
 * Si cualquier cosa falla, la página se renderiza con valores vacíos/cero
 * en vez de mostrar un error fatal al administrador.
 */
class DashboardController
{
    /**
     * Muestra el dashboard de inicio del administrador.
     * Mapeado a las rutas: GET /admin  y  GET /home (role_id = 1)
     */
    public function index(): void
    {
        // Valores seguros por defecto (anti-crash)
        $stats = [
            'totalUsuarios'        => 0,
            'profesoresActivos'    => 0,
            'estudiantesInscritos' => 0,
            'seccionesAbiertas'    => 0,
        ];
        $actividad      = [];
        $periodoActivo  = null;
        $dbStatus       = null;
        $tiposEventos   = 0;

        try {
            $model = new DashboardModel();

            // ── Tarjetas de resumen ──
            $stats = [
                'totalUsuarios'        => $model->countUsuarios(),
                'profesoresActivos'    => $model->countProfesoresActivos(),
                'estudiantesInscritos' => $model->countEstudiantesInscritos(),
                'seccionesAbiertas'    => $model->countSeccionesAbiertas(),
            ];

            // ── Feed de actividad reciente (últimas 5) ──
            $actividad = $model->getActividadReciente(5);

            // ── Estado del sistema ──
            $periodoActivo = $model->getPeriodoActivo();
            $dbStatus      = $model->getDbStatus();
            $tiposEventos  = $model->countTiposEventos();

        } catch (\Throwable $e) {
            // Si algo no capturado en el modelo explota aquí,
            // el dashboard se muestra con los valores por defecto.
            error_log('[DASHBOARD CONTROLLER] ' . $e->getMessage());
        }

        // Renderizar vista (las variables quedan disponibles vía scope)
        require __DIR__ . '/../../../../resources/views/admin/dashboard/home_admin.php';
    }
}
