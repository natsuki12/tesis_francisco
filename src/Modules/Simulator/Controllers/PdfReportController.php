<?php

declare(strict_types=1);

namespace App\Modules\Simulator\Controllers;

use App\Modules\Student\Models\StudentAssignmentModel;
use App\Modules\Student\Models\StudentAttemptModel;
use App\Modules\Simulator\Services\DeclaracionComparador;
use Mpdf\Mpdf;

/**
 * PdfReportController — Generates a PDF comparison report
 * between the student's borrador and the DB case data.
 */
class PdfReportController
{
    /**
     * GET /api/declaracion/pdf
     * Generates and streams the PDF directly to the browser.
     */
    public function generar(): void
    {
        try {
            // 1. Get active intento
            $assignModel  = new StudentAssignmentModel();
            $attemptModel = new StudentAttemptModel();
            $estudianteId = $assignModel->getEstudianteId((int) $_SESSION['user_id']);

            if (!$estudianteId || empty($_SESSION['sim_asignacion_id'])) {
                http_response_code(403);
                echo 'No hay sesión activa del simulador.';
                return;
            }

            $intento = $attemptModel->getIntentoActivo((int) $_SESSION['sim_asignacion_id']);
            if (!$intento) {
                http_response_code(404);
                echo 'No se encontró un intento activo.';
                return;
            }

            // 2. Run comparison
            $comparador = new DeclaracionComparador();
            $resultado = $comparador->comparar((int) $intento['id'], $estudianteId);

            // 3. Render HTML template
            ob_start();
            $datos              = $resultado['datos_caso'];
            $secciones          = $resultado['secciones'];
            $resumenSecciones   = $resultado['resumen_secciones'];
            $autoItems          = $resultado['autoliquidacion'];
            $herederosCalc      = $resultado['herederos_calculo'];
            $score              = $resultado['score'];
            $patrimonioNeto     = $datos['patrimonio_neto_correcto'] ?? 0;
            include __DIR__ . '/../../../../resources/views/simulator/pdf/pdf_comparacion.php';
            $html = ob_get_clean();

            // 4. Generate PDF with mPDF
            $mpdf = new Mpdf([
                'mode'          => 'utf-8',
                'format'        => 'Letter',
                'margin_left'   => 15,
                'margin_right'  => 15,
                'margin_top'    => 15,
                'margin_bottom' => 15,
                'default_font'  => 'dejavusans',
            ]);

            $mpdf->SetTitle('Reporte de Comparación — SPDSS');
            $mpdf->SetAuthor('SPDSS');
            $mpdf->WriteHTML($html);
            $mpdf->Output('reporte_declaracion.pdf', 'I'); // 'I' = inline (browser)

        } catch (\Throwable $e) {
            error_log('[PdfReportController::generar] ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            http_response_code(500);
            echo 'Error al generar el PDF: ' . $e->getMessage();
        }
    }
}
