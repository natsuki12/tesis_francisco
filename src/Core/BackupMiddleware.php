<?php
declare(strict_types=1);

namespace App\Core;

use App\Modules\Admin\Models\ConfigGlobalModel;

/**
 * Pseudo-cron: verifica en cada request admin si toca ejecutar un respaldo automático.
 * Diseñado para ser ultra-ligero: una sola consulta SELECT si el backup está desactivado.
 */
class BackupMiddleware
{
    /**
     * Verifica si corresponde ejecutar un respaldo automático.
     * Llamar desde logged_layout.php o bootstrap.
     */
    public static function check(): void
    {
        try {
            $model = new ConfigGlobalModel();

            // 1. Lectura rápida — si está desactivado, salir inmediatamente
            $enabled = $model->get('backup_auto_enabled');
            if ($enabled !== '1') {
                return;
            }

            // 2. Leer configuración completa de respaldo
            $config = $model->getByCategoria('respaldo');

            $frecuencia = $config['backup_frecuencia'] ?? 'diario';
            $hora        = $config['backup_hora'] ?? '03:00';
            $diaSemana   = (int)($config['backup_dia'] ?? 0);
            $retencion   = max(1, (int)($config['backup_retencion'] ?? 5));
            $ultimoTs    = $config['backup_ultimo_timestamp'] ?? null;

            // 3. ¿Toca hacer backup?
            $now = new \DateTime();
            $horaActual = $now->format('H:i');

            // ¿Ya pasó la hora programada hoy?
            if ($horaActual < $hora) {
                return;
            }

            // ¿El último backup ya es de hoy (misma fecha)?
            if ($ultimoTs) {
                try {
                    $ultimoDate = (new \DateTime($ultimoTs))->format('Y-m-d');
                    if ($ultimoDate === $now->format('Y-m-d')) {
                        return; // Ya se hizo hoy
                    }
                } catch (\Throwable $e) {
                    // Timestamp corrupto — ignorar y continuar
                    error_log('[BackupMiddleware] backup_ultimo_timestamp inválido: ' . $ultimoTs);
                }
            }

            // Semanal: verificar si hoy es el día configurado (0=Domingo ... 6=Sábado)
            if ($frecuencia === 'semanal') {
                $diaHoy = (int)$now->format('w'); // 0=Domingo
                if ($diaHoy !== $diaSemana) {
                    return;
                }
            }

            // 4. Ejecutar mysqldump
            $backupDir = __DIR__ . '/../../database_backup';
            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0755, true);
            }

            $timestamp = $now->format('Y-m-d_H-i-s');
            $filename  = "auto_{$timestamp}.sql";
            $filepath  = $backupDir . '/' . $filename;

            $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
            $db   = $_ENV['DB_NAME'] ?? '';
            $user = $_ENV['DB_USER'] ?? 'root';
            $pass = $_ENV['DB_PASS'] ?? '';

            if (empty($db)) {
                return;
            }

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
                    unlink($filepath);
                }
                error_log('[BackupMiddleware] mysqldump falló (code ' . $returnCode . '): ' . implode("\n", $output));
                return;
            }

            // 5. Actualizar timestamp
            $model->set('backup_ultimo_timestamp', $now->format('Y-m-d H:i:s'));

            // 6. Registrar en bitácora
            BitacoraModel::registrar(BitacoraModel::SYSTEM_BACKUP, 'sistema', null, null, null, null, 'Respaldo automático: ' . $filename);

            // 7. Aplicar retención — solo archivos auto_
            self::aplicarRetencion($backupDir, $retencion);

            error_log('[BackupMiddleware] Respaldo automático generado: ' . $filename);

        } catch (\Throwable $e) {
            error_log('[BackupMiddleware] Error: ' . $e->getMessage());
        }
    }

    /**
     * Elimina respaldos automáticos antiguos que excedan la retención.
     * Solo afecta archivos con prefijo auto_ — los manuales no se tocan.
     */
    public static function aplicarRetencion(string $backupDir, int $maxBackups): void
    {
        $autoFiles = glob($backupDir . '/auto_*.sql');
        if (!$autoFiles || count($autoFiles) <= $maxBackups) {
            return;
        }

        // Ordenar por fecha de modificación (más reciente primero)
        usort($autoFiles, fn($a, $b) => filemtime($b) - filemtime($a));

        // Eliminar los que sobran
        $sobrantes = array_slice($autoFiles, $maxBackups);
        foreach ($sobrantes as $file) {
            unlink($file);
            error_log('[BackupMiddleware] Retención: eliminado ' . basename($file));
        }
    }
}
