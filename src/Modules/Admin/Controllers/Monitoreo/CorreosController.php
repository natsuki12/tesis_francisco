<?php
declare(strict_types=1);

namespace App\Modules\Admin\Controllers\Monitoreo;

use App\Core\Mailer;
use App\Core\MailQueueService;
use App\Core\Csrf;

/**
 * Controlador de Gestión de Correos.
 * Muestra el panel de cola de correos, estado del SMTP y estadísticas de envío.
 * Mapeado a la ruta: GET /admin/monitoreo/correos
 */
class CorreosController
{
    public function index(): void
    {
        // SMTP Health Check se carga async via AJAX (no bloquea la página)
        $smtpHealth = ['ok' => null, 'host' => 'Verificando...', 'latency_ms' => null, 'error' => null];

        // Estadísticas reales desde mail_queue
        $stats = MailQueueService::getStats();
        $maxRetries = MailQueueService::MAX_RETRIES;

        require __DIR__ . '/../../../../../resources/views/admin/monitoreo/correos.php';
    }

    /**
     * Health check SMTP (AJAX GET).
     * Separado de index() para no bloquear la carga de la página.
     */
    public function smtpHealth(): void
    {
        header('Content-Type: application/json');
        session_write_close(); // SMTP check puede tardar — liberar sesión

        try {
            $result = Mailer::checkHealth();
            echo json_encode($result);
        } catch (\Throwable $e) {
            error_log('[CorreosController] SMTP Health Check failed: ' . $e->getMessage());
            echo json_encode(['ok' => false, 'host' => '', 'latency_ms' => null, 'error' => 'No se pudo verificar el servidor de correo.']);
        }
    }

    /**
     * API paginada para la DataTable de correos (AJAX GET).
     * Soporta search, sort y paginación server-side.
     */
    public function apiList(): void
    {
        header('Content-Type: application/json');
        session_write_close(); // Liberar sesión para no bloquear navegación

        try {
            $page    = max(1, (int) ($_GET['page'] ?? 1));
            $limit   = min(50, max(10, (int) ($_GET['limit'] ?? 10)));
            $search  = trim($_GET['search'] ?? '');
            $sortCol = $_GET['sort'] ?? 'created_at';
            $sortDir = strtoupper($_GET['order'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';

            $result = MailQueueService::getPaginated($page, $limit, $search, $sortCol, $sortDir);
            $result['maxRetries'] = MailQueueService::MAX_RETRIES;

            echo json_encode($result);
        } catch (\Throwable $e) {
            error_log('[CorreosController::apiList] ' . $e->getMessage());
            echo json_encode(['rows' => [], 'total' => 0, 'page' => 1, 'pages' => 1, 'maxRetries' => 4]);
        }
    }

    /**
     * Procesa la cola de correos pendientes (AJAX POST).
     * Protegido con CSRF y file lock (dentro de processPending).
     */
    public function procesarCola(): void
    {
        header('Content-Type: application/json');

        try {
            if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
                echo json_encode(['success' => false, 'message' => 'Token CSRF inválido.']);
                return;
            }

            $result = MailQueueService::processPending();

            if ($result['skipped']) {
                echo json_encode(['success' => false, 'message' => 'Ya hay un proceso activo.']);
                return;
            }

            echo json_encode([
                'success'      => true,
                'message'      => "Procesados: {$result['processed']}, Exitosos: {$result['success']}, Fallidos: {$result['failed']}",
                'processed'    => $result['processed'],
                'successCount' => $result['success'],
                'failedCount'  => $result['failed'],
            ]);
        } catch (\Throwable $e) {
            error_log('[CorreosController::procesarCola] ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno.']);
        }
    }
}
