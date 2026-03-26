<?php
declare(strict_types=1);

// Script CLI para procesar la cola de correos.
// Diseñado para ejecutarse via CRON cada 2 minutos.
//
// Windows (Programador de Tareas):
//   Programa: C:\xampp\php\php.exe
//   Argumentos: C:\xampp\htdocs\tesis_francisco\scripts\process_mail_queue.php
//
// Linux:
//   */2 * * * * php /path/to/scripts/process_mail_queue.php >> /path/to/storage/logs/cron.log 2>&1

// Bloquear ejecución desde navegador
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('Acceso denegado.');
}

$basePath = dirname(__DIR__);

// 1. Autoloader de Composer
require $basePath . '/vendor/autoload.php';

// 2. Cargar .env → $_ENV (reutiliza App::loadEnv)
\App\Core\App::loadEnv($basePath . '/.env');

// 3. Verificar conexión a DB antes de procesar
$ts = date('Y-m-d H:i:s');
try {
    \App\Core\DB::connect();
} catch (\Throwable $e) {
    echo "[{$ts}] ERROR: No se pudo conectar a la base de datos — {$e->getMessage()}\n";
    exit(1);
}

// 4. Procesar cola en loop hasta vaciarla (máx 10 rondas = 200 correos)
$maxRounds = 10;
$totalProcessed = 0;
$totalSuccess = 0;
$totalFailed = 0;

for ($round = 1; $round <= $maxRounds; $round++) {
    $result = \App\Core\MailQueueService::processPending();

    if ($result['skipped']) {
        echo "[{$ts}] SKIP: Otro proceso activo.\n";
        exit(0);
    }

    $totalProcessed += $result['processed'];
    $totalSuccess   += $result['success'];
    $totalFailed    += $result['failed'];

    // Si procesó menos de 20, ya no hay más pendientes
    if ($result['processed'] < 20) {
        break;
    }
}

// 5. Output para log del cron
echo "[{$ts}] OK: procesados={$totalProcessed} exitosos={$totalSuccess} fallidos={$totalFailed} rondas={$round}\n";
