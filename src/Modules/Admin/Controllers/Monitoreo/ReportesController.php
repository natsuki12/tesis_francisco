<?php
declare(strict_types=1);

namespace App\Modules\Admin\Controllers\Monitoreo;

use App\Modules\Admin\Models\Monitoreo\AdminReportesModel;

class ReportesController
{
    /**
     * Muestra la vista de reportes estadísticos.
     * Mapeado a la ruta: GET /admin/monitoreo/reportes
     */
    public function index()
    {
        try {
            $model = new AdminReportesModel();
            $kpi            = $model->getKPI();
            $estados        = $model->getDistribucionEstados();
            $tasaExito      = $model->getTasaExito();
            $rendimiento    = $model->getRendimientoPorSeccion();
            $notas          = $model->getDistribucionNotas();
            $topPromedio    = $model->getTopEstudiantes('promedio');
            $topActivos     = $model->getTopEstudiantes('activos');
            $promSeccion    = $model->getPromedioPorSeccion();
        } catch (\Throwable $e) {
            error_log('[ReportesController] ' . $e->getMessage());
            $kpi         = ['estudiantes' => 0, 'profesores' => 0, 'secciones' => 0, 'casos' => 0, 'intentos' => 0, 'asignaciones' => 0, 'asignaciones_con_intento' => 0];
            $estados     = [];
            $tasaExito   = 0;
            $rendimiento = [];
            $notas       = ['numericas' => ['0 – 5' => 0, '6 – 10' => 0, '11 – 15' => 0, '16 – 20' => 0, 'total' => 0], 'cualitativas' => [], 'promedio' => null];
            $topPromedio = [];
            $topActivos  = [];
            $promSeccion = [];
        }

        require_once __DIR__ . '/../../../../../resources/views/admin/monitoreo/reportes.php';
    }
}
