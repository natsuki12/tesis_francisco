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
     * Resuelve el intento para generar PDFs.
     * Intenta: 1) query param ?intento_id, 2) sesión activa del simulador,
     * 3) sim_last_intento_id (post-declaración).
     */
    private function resolveIntento(): ?array
    {
        $assignModel  = new StudentAssignmentModel();
        $attemptModel = new StudentAttemptModel();
        $estudianteId = $assignModel->getEstudianteId((int) $_SESSION['user_id']);
        if (!$estudianteId) return null;

        // 1) Query param (desde vista de asignaciones)
        $qpId = (int) ($_GET['intento_id'] ?? 0);
        if ($qpId > 0) {
            $intento = $attemptModel->getIntento($qpId, $estudianteId);
            if ($intento) return $intento;
        }

        // 2) Sesión activa del simulador
        if (!empty($_SESSION['sim_asignacion_id'])) {
            $intento = $attemptModel->getIntentoActivo((int) $_SESSION['sim_asignacion_id']);
            if ($intento) return $intento;
        }

        // 3) Post-declaración: intento ya enviado
        if (!empty($_SESSION['sim_last_intento_id'])) {
            $intento = $attemptModel->getIntento((int) $_SESSION['sim_last_intento_id'], $estudianteId);
            if ($intento) return $intento;
        }

        return null;
    }

    /**
     * GET /simulador/sucesion/declaracion_pdf
     * Generates and streams the PDF directly to the browser.
     */
    public function generar(): void
    {
        try {
            $intento = $this->resolveIntento();
            if (!$intento) {
                http_response_code(404);
                echo 'No se encontró un intento para generar el PDF.';
                return;
            }

            $assignModel  = new StudentAssignmentModel();
            $estudianteId = $assignModel->getEstudianteId((int) $_SESSION['user_id']);

            // Validate herederos have parentesco defined (moved from web.php)
            try {
                $borradorCheck = new \App\Modules\Simulator\Services\BorradorService($intento);
                foreach ($borradorCheck->getHerederosDetalle() as $h) {
                    $pid = (int) ($h['parentesco_id'] ?? 0);
                    if ($pid === 0 || $pid === 19) {
                        header('Location: ' . base_url('/simulador/sucesion/herederos?datos_incompletos=1'));
                        exit;
                    }
                }
            } catch (\Throwable $e) {
                error_log('[PdfReportController::generar] herederos check: ' . $e->getMessage());
            }
            // 2. Run comparison
            $comparador = new DeclaracionComparador();
            $resultado = $comparador->comparar((int) $intento['id'], $estudianteId);

            // 3. Membrete variables
            $pdfTipoDocumento = 'Reporte de Comparación';
            $pdfReferencia = '#INT-' . $intento['id'];
            $pdfEstado = $resultado['score']['porcentaje'] . '% acierto';
            $pdfEstadoLabel = 'Score';

            // 4. Render HTML template
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

            $mpdf->SetTitle('Reporte de Comparación — SUCELAB');
            $mpdf->SetAuthor('SUCELAB');
            $mpdf->WriteHTML($html);
            $mpdf->Output('reporte_declaracion.pdf', 'I'); // 'I' = inline (browser)

        } catch (\Throwable $e) {
            error_log('[PdfReportController::generar] ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            http_response_code(500);
            echo 'Ocurrió un error inesperado al generar el documento. Por favor, contacte al administrador.';
        }
    }

    /**
     * GET /simulador/sucesion/planilla_pdf
     * Generates and streams the SENIAT FORMA DS-99032 planilla PDF.
     */
    public function generarPlanilla(): void
    {
        try {
            $intento = $this->resolveIntento();
            if (!$intento) {
                http_response_code(404);
                echo 'No se encontró un intento para generar la planilla.';
                return;
            }

            $service = new \App\Modules\Simulator\Services\PlanillaDeclaracionService();
            
            // Supresión temporal de Advertencias en mPDF para evitar el "Headers ya enviados"
            $oldReporting = error_reporting();
            error_reporting($oldReporting & ~E_WARNING & ~E_NOTICE);
            
            $service->generar($intento);
            
            // Restaurar configuración original
            error_reporting($oldReporting);

        } catch (\Throwable $e) {
            error_log('[PdfReportController::generarPlanilla] ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            http_response_code(500);
            echo 'Ocurrió un error inesperado al generar el documento. Por favor, contacte al administrador.';
        }
    }
}
