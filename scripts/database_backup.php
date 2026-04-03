<?php
declare(strict_types=1);

// Script CLI para ejecutar respaldos automáticos de la base de datos.
// Diseñado para ejecutarse via CRON una vez al día.
//
// Windows (Programador de Tareas):
//   Programa: C:\xampp\php\php.exe
//   Argumentos: C:\xampp\htdocs\tesis_francisco\scripts\database_backup.php
//
// Linux (Hosting):
//   0 3 * * * php /path/to/scripts/database_backup.php >> /path/to/storage/logs/cron_backup.log 2>&1

// Bloquear ejecución desde navegador
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('Acceso denegado.');
}

$basePath = dirname(__DIR__);

// 1. Autoloader de Composer
require $basePath . '/vendor/autoload.php';

// 2. Cargar .env → $_ENV
\App\Core\App::loadEnv($basePath . '/.env');

// 3. Verificar conexión a DB
$ts = date('Y-m-d H:i:s');
try {
    $db = \App\Core\DB::connect();
} catch (\Throwable $e) {
    echo "[{$ts}] ERROR: No se pudo conectar a la base de datos — {$e->getMessage()}\n";
    exit(1);
}

// 4. Leer configuración de retención
$retencion = 5; // por defecto
$model = null;
try {
    $model = new \App\Modules\Admin\Models\ConfigGlobalModel();
    $config = $model->getByCategoria('respaldo');
    $retencion = max(1, (int)($config['backup_retencion'] ?? 5));
} catch (\Throwable $e) {
    echo "[{$ts}] WARN: No se pudo leer config de retención, usando default ({$retencion})\n";
}

// 5. Preparar directorio y nombre de archivo
$backupDir = $basePath . '/database_backup';
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

$filename = 'auto_' . date('Y-m-d_H-i-s') . '.sql';
$filepath = $backupDir . '/' . $filename;

// 6. Obtener credenciales
$host    = $_ENV['DB_HOST'] ?? '127.0.0.1';
$db_name = $_ENV['DB_NAME'] ?? '';
$user    = $_ENV['DB_USER'] ?? 'root';
$pass    = $_ENV['DB_PASS'] ?? '';

if (empty($db_name)) {
    echo "[{$ts}] ERROR: DB_NAME no configurado en .env\n";
    exit(1);
}

// 7. Detectar mysqldump según SO
$candidates = PHP_OS_FAMILY === 'Windows'
    ? ['C:\\xampp\\mysql\\bin\\mysqldump.exe', 'C:\\wamp64\\bin\\mysql\\mysql8.0.31\\bin\\mysqldump.exe']
    : ['/usr/bin/mysqldump', '/usr/local/bin/mysqldump', '/Applications/MAMP/Library/bin/mysqldump'];

$mysqldump = 'mysqldump'; // fallback: buscar en PATH
foreach ($candidates as $path) {
    if (file_exists($path)) {
        $mysqldump = $path;
        break;
    }
}

// 8. Ejecutar mysqldump
$cmd = sprintf(
    '"%s" --host=%s --user=%s %s --single-transaction --routines --triggers %s > "%s" 2>&1',
    $mysqldump,
    escapeshellarg($host),
    escapeshellarg($user),
    $pass !== '' ? '--password=' . escapeshellarg($pass) : '',
    escapeshellarg($db_name),
    $filepath
);

exec($cmd, $output, $returnCode);

if ($returnCode !== 0 || !file_exists($filepath) || filesize($filepath) === 0) {
    if (file_exists($filepath)) {
        unlink($filepath);
    }
    echo "[{$ts}] ERROR: mysqldump falló (code {$returnCode}): " . implode("\n", $output) . "\n";
    exit(1);
}

$sizeKB = round(filesize($filepath) / 1024, 1);
echo "[{$ts}] OK: Respaldo generado — {$filename} ({$sizeKB} KB)\n";

// 9. Registrar en bitácora
try {
    \App\Core\BitacoraModel::registrar(
        \App\Core\BitacoraModel::SYSTEM_BACKUP,
        'sistema', null, null, null, null,
        'Respaldo automático (CRON): ' . $filename
    );
} catch (\Throwable $e) {
    echo "[{$ts}] WARN: No se pudo registrar en bitácora: {$e->getMessage()}\n";
}

// 10. Actualizar timestamp en config
try {
    if ($model) {
        $model->set('backup_ultimo_timestamp', date('Y-m-d H:i:s'));
    }
} catch (\Throwable $e) {
    // No es crítico
}

// 11. Aplicar retención — eliminar backups auto_ antiguos
\App\Core\BackupMiddleware::aplicarRetencion($backupDir, $retencion);

echo "[{$ts}] Retención aplicada (máx {$retencion} backups automáticos)\n";
