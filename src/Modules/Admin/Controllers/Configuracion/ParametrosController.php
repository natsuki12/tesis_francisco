<?php
declare(strict_types=1);

namespace App\Modules\Admin\Controllers\Configuracion;

use App\Core\Csrf;
use App\Core\BitacoraModel;
use App\Modules\Admin\Models\ConfigGlobalModel;

class ParametrosController
{
    /**
     * Muestra la vista de parámetros globales del sistema.
     * GET /admin/configuracion/parametros
     */
    public function index()
    {
        // Cargar configuración de respaldo desde DB
        try {
            $configModel = new ConfigGlobalModel();
            $config = $configModel->getByCategoria('respaldo');
        } catch (\Throwable $e) {
            error_log('[ParametrosController::index] ' . $e->getMessage());
            $config = [];
        }

        // Cargar historial de backups desde filesystem
        $backupDir = __DIR__ . '/../../../../../database_backup';
        $backups = [];

        if (is_dir($backupDir)) {
            $files = glob($backupDir . '/*.sql');
            if ($files) {
                usort($files, fn($a, $b) => filemtime($b) - filemtime($a));
                foreach (array_slice($files, 0, 10) as $file) {
                    $filename = basename($file);
                    $tipo = str_starts_with($filename, 'auto_') ? 'Automático' : 'Manual';
                    $size = filesize($file);
                    $backups[] = [
                        'filename' => $filename,
                        'size'     => $size !== false ? $size : 0,
                        'date'     => filemtime($file) ?: time(),
                        'tipo'     => $tipo,
                    ];
                }
            }
        }

        require_once __DIR__ . '/../../../../../resources/views/admin/configuracion/parametros.php';
    }

    /**
     * Guarda la configuración de respaldo.
     * POST /admin/configuracion/parametros/guardar
     */
    public function guardarConfig()
    {
        header('Content-Type: application/json');

        try {
            // Verificar CSRF
            if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Token de seguridad inválido. Recargue la página.']);
                exit;
            }

            $configModel = new ConfigGlobalModel();

            $datos = [
                'backup_auto_enabled' => ($_POST['backup_auto_enabled'] ?? '0') === '1' ? '1' : '0',
                'backup_frecuencia'   => in_array($_POST['backup_frecuencia'] ?? '', ['diario', 'semanal']) ? $_POST['backup_frecuencia'] : 'diario',
                'backup_hora'         => preg_match('/^\d{2}:\d{2}$/', $_POST['backup_hora'] ?? '') ? $_POST['backup_hora'] : '03:00',
                'backup_dia'          => (string)max(0, min(6, (int)($_POST['backup_dia'] ?? 0))),
                'backup_retencion'    => (string)max(1, min(50, (int)($_POST['backup_retencion'] ?? 5))),
            ];

            $ok = $configModel->setMultiple($datos);

            // Aplicar retención inmediatamente si se cambió el límite
            if ($ok) {
                $backupDir = __DIR__ . '/../../../../../database_backup';
                if (is_dir($backupDir)) {
                    \App\Core\BackupMiddleware::aplicarRetencion($backupDir, (int)$datos['backup_retencion']);
                }

                // Registrar en bitácora
                $detalle = 'Auto: ' . ($datos['backup_auto_enabled'] === '1' ? 'Sí' : 'No')
                    . ', Frecuencia: ' . $datos['backup_frecuencia']
                    . ', Hora: ' . $datos['backup_hora']
                    . ', Retención: ' . $datos['backup_retencion'];
                BitacoraModel::registrar(BitacoraModel::BACKUP_CONFIG_CHANGED, 'sistema', null, null, 'configs_globales', null, $detalle);
            }

            echo json_encode([
                'success' => $ok,
                'message' => $ok ? 'Configuración guardada correctamente.' : 'Error al guardar la configuración.'
            ]);
        } catch (\Throwable $e) {
            error_log('[ParametrosController::guardarConfig] ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno del servidor.']);
        }

        exit;
    }

    /**
     * Genera un respaldo manual de la base de datos (mysqldump).
     * POST /admin/configuracion/backup
     */
    public function backup()
    {
        header('Content-Type: application/json');

        try {
            // Verificar CSRF
            if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Token de seguridad inválido. Recargue la página.']);
                exit;
            }

            $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
            $db   = $_ENV['DB_NAME'] ?? '';
            $user = $_ENV['DB_USER'] ?? 'root';
            $pass = $_ENV['DB_PASS'] ?? '';

            if (empty($db)) {
                echo json_encode(['success' => false, 'message' => 'Variable DB_NAME no configurada.']);
                exit;
            }

            $backupDir = __DIR__ . '/../../../../../database_backup';
            if (!is_dir($backupDir)) {
                if (!mkdir($backupDir, 0755, true)) {
                    echo json_encode(['success' => false, 'message' => 'No se pudo crear el directorio de respaldos.']);
                    exit;
                }
            }

            $timestamp = date('Y-m-d_H-i-s');
            $filename  = "manual_{$timestamp}.sql";
            $filepath  = $backupDir . '/' . $filename;

            $mysqldump = 'C:\\xampp\\mysql\\bin\\mysqldump.exe';
            if (!file_exists($mysqldump)) {
                $mysqldump = 'mysqldump';
            }

            $cmd = sprintf(
                '"%s" --host=%s --user=%s %s --single-transaction --routines --triggers %s > "%s" 2>&1',
                $mysqldump,
                escapeshellarg($host),
                escapeshellarg($user),
                $pass !== '' ? '--password=' . escapeshellarg($pass) : '',
                escapeshellarg($db),
                $filepath
            );

            exec($cmd, $output, $returnCode);

            if ($returnCode !== 0 || !file_exists($filepath) || filesize($filepath) === 0) {
                if (file_exists($filepath)) {
                    @unlink($filepath);
                }
                $errorMsg = implode("\n", $output);
                error_log("[ParametrosController::backup] mysqldump falló (code $returnCode): $errorMsg");
                echo json_encode(['success' => false, 'message' => 'Error al generar respaldo: ' . ($errorMsg ?: 'mysqldump no disponible.')]);
                exit;
            }

            // Actualizar timestamp en DB
            try {
                $configModel = new ConfigGlobalModel();
                $configModel->set('backup_ultimo_timestamp', date('Y-m-d H:i:s'));
            } catch (\Throwable $e) {
                error_log('[ParametrosController::backup] No se pudo actualizar timestamp: ' . $e->getMessage());
            }

            $fileSize = filesize($filepath);
            $sizeMB = $fileSize !== false ? round($fileSize / 1024 / 1024, 1) : 0;

            // Registrar en bitácora
            BitacoraModel::registrar(BitacoraModel::SYSTEM_BACKUP, 'sistema', null, null, null, null, "Respaldo manual: {$filename} ({$sizeMB} MB)");

            echo json_encode([
                'success'  => true,
                'message'  => "Respaldo generado exitosamente: {$filename} ({$sizeMB} MB)",
                'filename' => $filename,
                'size'     => $sizeMB,
            ]);
        } catch (\Throwable $e) {
            error_log('[ParametrosController::backup] ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno del servidor.']);
        }

        exit;
    }

    /**
     * Descarga un archivo de respaldo.
     * GET /admin/configuracion/backup/descargar?file=xxx
     */
    public function descargar()
    {
        try {
            $filename = basename($_GET['file'] ?? '');

            if (empty($filename) || !preg_match('/^(manual|auto)_[\d\-_]+\.sql$/', $filename)) {
                http_response_code(400);
                echo 'Archivo inválido.';
                exit;
            }

            $filepath = __DIR__ . '/../../../../../database_backup/' . $filename;

            if (!file_exists($filepath)) {
                http_response_code(404);
                echo 'Archivo no encontrado.';
                exit;
            }

            $fileSize = filesize($filepath);
            if ($fileSize === false) {
                http_response_code(500);
                echo 'Error al leer el archivo.';
                exit;
            }

            // Registrar en bitácora
            BitacoraModel::registrar(BitacoraModel::BACKUP_DOWNLOADED, 'sistema', null, null, null, null, 'Descargado: ' . $filename);

            header('Content-Type: application/sql');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . $fileSize);
            readfile($filepath);
        } catch (\Throwable $e) {
            error_log('[ParametrosController::descargar] ' . $e->getMessage());
            http_response_code(500);
            echo 'Error interno del servidor.';
        }

        exit;
    }

    /**
     * Elimina un archivo de respaldo.
     * POST /admin/configuracion/backup/eliminar
     */
    public function eliminarBackup()
    {
        header('Content-Type: application/json');

        try {
            // Verificar CSRF
            if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Token de seguridad inválido. Recargue la página.']);
                exit;
            }

            $filename = basename($_POST['file'] ?? '');

            if (empty($filename) || !preg_match('/^(manual|auto)_[\d\-_]+\.sql$/', $filename)) {
                echo json_encode(['success' => false, 'message' => 'Archivo inválido.']);
                exit;
            }

            $filepath = __DIR__ . '/../../../../../database_backup/' . $filename;

            if (!file_exists($filepath)) {
                echo json_encode(['success' => false, 'message' => 'Archivo no encontrado.']);
                exit;
            }

            if (!@unlink($filepath)) {
                echo json_encode(['success' => false, 'message' => 'No se pudo eliminar el archivo.']);
                exit;
            }

            // Registrar en bitácora
            BitacoraModel::registrar(BitacoraModel::BACKUP_DELETED, 'sistema', null, null, null, null, 'Eliminado: ' . $filename);

            echo json_encode(['success' => true, 'message' => 'Respaldo eliminado.']);
        } catch (\Throwable $e) {
            error_log('[ParametrosController::eliminarBackup] ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno del servidor.']);
        }

        exit;
    }
}
