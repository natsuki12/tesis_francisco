<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Servicio central de Cola de Correos.
 *
 * Orquesta el envío de emails con dos estrategias:
 * - send():       Envío directo + fallback a cola con reintentos exponenciales.
 * - sendDirect(): Envío directo + solo historial (sin reintentos).
 *
 * Usa Mailer::send() internamente para el envío SMTP real.
 * Sigue el patrón de BitacoraModel: métodos estáticos, DB::connect(), try/catch silencioso.
 */
class MailQueueService
{
    /**
     * Máximo de intentos antes de marcar como 'fallido'.
     * Incluye el envío directo inicial + reintentos.
     */
    public const MAX_RETRIES = 4;

    /**
     * Calcula los minutos de espera para el próximo reintento.
     * Fórmula: 2^n × 2 minutos (backoff exponencial).
     *
     * intentos=1 → 4 min, intentos=2 → 8 min, intentos=3 → 16 min
     */
    private static function calcularNextRetry(int $intentos): int
    {
        return (int) (pow(2, $intentos) * 2);
    }

    // ─── INSERT genérico (DRY) ─────────────────────────────

    /**
     * Inserta un registro en mail_queue.
     * Usado internamente por send() y sendDirect().
     *
     * Todos los timestamps usan NOW() de MySQL para garantizar consistencia
     * incluso si PHP y MySQL están en servidores separados.
     *
     * @param bool $sentNow       true → sent_at = NOW(), false → sent_at = NULL
     * @param ?int $retryMinutes  null → next_retry_at = NULL, int → NOW() + N MINUTE
     */
    private static function insertQueue(
        string  $to,
        string  $subject,
        string  $body,
        string  $tipo,
        string  $estado,
        int     $intentos,
        ?int    $userId,
        ?int    $refId,
        ?string $errorMsg,
        bool    $sentNow,
        ?int    $retryMinutes
    ): void {
        try {
            $db = DB::connect();

            // Expresiones SQL para timestamps (NOW() de MySQL, no date() de PHP)
            $sentExpr  = $sentNow ? 'NOW()' : 'NULL';
            $retryExpr = $retryMinutes !== null
                ? "DATE_ADD(NOW(), INTERVAL {$retryMinutes} MINUTE)"
                : 'NULL';
            // $retryMinutes es un int calculado internamente, no input del usuario → seguro

            $sql = "INSERT INTO mail_queue
                    (usuario_id, tipo, destinatario, asunto, cuerpo, estado, intentos,
                     referencia_id, error_msg, next_retry_at, sent_at)
                    VALUES
                    (:usuario_id, :tipo, :destinatario, :asunto, :cuerpo, :estado, :intentos,
                     :referencia_id, :error_msg, {$retryExpr}, {$sentExpr})";

            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':usuario_id'    => $userId,
                ':tipo'          => $tipo,
                ':destinatario'  => $to,
                ':asunto'        => $subject,
                ':cuerpo'        => $body,
                ':estado'        => $estado,
                ':intentos'      => $intentos,
                ':referencia_id' => $refId,
                ':error_msg'     => $errorMsg,
            ]);
        } catch (\Throwable $e) {
            // Un fallo en la cola NO debe romper el flujo del sistema
            error_log("[MailQueueService] INSERT failed: " . $e->getMessage());
        }
    }

    // ─── MÉTODOS PÚBLICOS ──────────────────────────────────

    /**
     * Envío directo + fallback a cola con reintentos.
     * Para: bienvenida, rif_sucesoral.
     *
     * @return bool true si se envió directo, false si fue a cola
     */
    public static function send(
        string  $to,
        string  $subject,
        string  $body,
        string  $tipo,
        ?int    $userId = null,
        ?int    $refId  = null
    ): bool {
        try {
            $sent = Mailer::send($to, $subject, $body);
        } catch (\Throwable $e) {
            error_log("[MailQueueService] Mailer::send() error: " . $e->getMessage());
            $sent = false;
        }

        if ($sent) {
            // Envío exitoso → registrar como 'enviado'
            self::insertQueue(
                $to, $subject, $body, $tipo,
                'enviado', 1, $userId, $refId,
                null, true, null
            );
            return true;
        }

        // Envío falló → registrar como 'pendiente' con next_retry_at
        $retryMinutes = self::calcularNextRetry(1); // 4 min

        self::insertQueue(
            $to, $subject, $body, $tipo,
            'pendiente', 1, $userId, $refId,
            'Envío directo fallido', false, $retryMinutes
        );
        return false;
    }

    /**
     * Encolar sin intentar envío directo.
     * Para: importaciones CSV masivas donde intentar SMTP por cada fila
     * excedería max_execution_time de PHP.
     *
     * El CRON procesará la cola y enviará los correos.
     */
    public static function queue(
        string  $to,
        string  $subject,
        string  $body,
        string  $tipo,
        ?int    $userId = null,
        ?int    $refId  = null
    ): void {
        $retryMinutes = 0; // Disponible inmediatamente (sin espera)

        self::insertQueue(
            $to, $subject, $body, $tipo,
            'pendiente', 0, $userId, $refId,
            null, false, $retryMinutes
        );
    }

    /**
     * Envío directo + solo historial (sin reintentos).
     * Para: reset_password.
     *
     * @return bool true si se envió, false si falló
     */
    public static function sendDirect(
        string  $to,
        string  $subject,
        string  $body,
        string  $tipo,
        ?int    $userId = null
    ): bool {
        try {
            $sent = Mailer::send($to, $subject, $body);
        } catch (\Throwable $e) {
            error_log("[MailQueueService] Mailer::send() error: " . $e->getMessage());
            $sent = false;
        }

        // Para reset_password: no almacenar el cuerpo (contiene el código en texto plano).
        // Se guarda un placeholder seguro para que el registro exista en el historial.
        $storedBody = ($tipo === 'reset_password')
            ? '[Contenido omitido por seguridad — código de recuperación]'
            : $body;

        if ($sent) {
            self::insertQueue(
                $to, $subject, $storedBody, $tipo,
                'enviado', 1, $userId, null,
                null, true, null
            );
            return true;
        }

        self::insertQueue(
            $to, $subject, $storedBody, $tipo,
            'fallido', 1, $userId, null,
            'Envío directo fallido (sin reintentos)', false, null
        );
        return false;
    }

    /**
     * Procesa correos pendientes en la cola.
     * Protegido con file lock para evitar ejecución concurrente.
     *
     * @return array{processed: int, success: int, failed: int, skipped: bool}
     */
    public static function processPending(): array
    {
        $result = ['processed' => 0, 'success' => 0, 'failed' => 0, 'skipped' => false];

        // ── File lock: evitar ejecución concurrente ──
        $lockFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'spdss_mail.lock';
        $lock = @fopen($lockFile, 'w');

        if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) {
            $result['skipped'] = true;
            if ($lock) fclose($lock);
            return $result;
        }

        try {
            $db = DB::connect();

            // Buscar pendientes cuyo next_retry_at ya pasó
            $sql = "SELECT id, destinatario, asunto, cuerpo, intentos
                    FROM mail_queue
                    WHERE estado = 'pendiente' AND next_retry_at <= NOW()
                    ORDER BY next_retry_at ASC
                    LIMIT 10";

            $pendientes = $db->query($sql)->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($pendientes as $correo) {
                $result['processed']++;

                try {
                    $sent = Mailer::send(
                        $correo['destinatario'],
                        $correo['asunto'],
                        $correo['cuerpo']
                    );

                    if ($sent) {
                        // Reintento exitoso
                        $stmt = $db->prepare(
                            "UPDATE mail_queue
                             SET estado = 'enviado', sent_at = NOW(), error_msg = NULL, next_retry_at = NULL
                             WHERE id = :id"
                        );
                        $stmt->execute([':id' => $correo['id']]);
                        $result['success']++;
                    } else {
                        // Reintento falló
                        $newIntentos = (int) $correo['intentos'] + 1;

                        if ($newIntentos >= self::MAX_RETRIES) {
                            // Máximo alcanzado → marcar como fallido
                            $stmt = $db->prepare(
                                "UPDATE mail_queue
                                 SET estado = 'fallido', intentos = :intentos,
                                     error_msg = 'Máximo de reintentos alcanzado'
                                 WHERE id = :id"
                            );
                            $stmt->execute([
                                ':intentos' => $newIntentos,
                                ':id'       => $correo['id'],
                            ]);
                        } else {
                            // Calcular próximo reintento con NOW() de MySQL
                            $minutes = self::calcularNextRetry($newIntentos);
                            $stmt = $db->prepare(
                                "UPDATE mail_queue
                                 SET intentos = :intentos,
                                     next_retry_at = DATE_ADD(NOW(), INTERVAL {$minutes} MINUTE),
                                     error_msg = :error
                                 WHERE id = :id"
                            );
                            $stmt->execute([
                                ':intentos' => $newIntentos,
                                ':error'    => 'Reintento #' . $newIntentos . ' fallido',
                                ':id'       => $correo['id'],
                            ]);
                        }
                        $result['failed']++;
                    }
                } catch (\Throwable $e) {
                    // Error en un correo individual → no rompe el lote
                    error_log("[MailQueueService] Error procesando correo #{$correo['id']}: " . $e->getMessage());
                    $result['failed']++;
                }
            }
        } catch (\Throwable $e) {
            error_log("[MailQueueService] processPending() error: " . $e->getMessage());
        } finally {
            // Siempre liberar el lock
            flock($lock, LOCK_UN);
            fclose($lock);
        }

        return $result;
    }

    /**
     * Obtiene estadísticas de la cola para las stat cards.
     *
     * @return array{total: int, enviados: int, pendientes: int, fallidos: int}
     */
    public static function getStats(): array
    {
        try {
            $db = DB::connect();
            $sql = "SELECT
                        COUNT(*) as total,
                        SUM(estado = 'enviado') as enviados,
                        SUM(estado = 'pendiente') as pendientes,
                        SUM(estado = 'fallido') as fallidos
                    FROM mail_queue";

            $row = $db->query($sql)->fetch(\PDO::FETCH_ASSOC);

            return [
                'total'      => (int) ($row['total'] ?? 0),
                'enviados'   => (int) ($row['enviados'] ?? 0),
                'pendientes' => (int) ($row['pendientes'] ?? 0),
                'fallidos'   => (int) ($row['fallidos'] ?? 0),
            ];
        } catch (\Throwable $e) {
            error_log("[MailQueueService] getStats() error: " . $e->getMessage());
            return ['total' => 0, 'enviados' => 0, 'pendientes' => 0, 'fallidos' => 0];
        }
    }

    /**
     * Obtiene todos los registros de la cola para la DataTable.
     * No incluye el campo 'cuerpo' (TEXT pesado).
     *
     * @return array<int, array>
     */
    public static function getAll(): array
    {
        try {
            $db = DB::connect();
            $sql = "SELECT id, tipo, destinatario, asunto, estado, intentos,
                           next_retry_at, sent_at, created_at
                    FROM mail_queue
                    ORDER BY created_at DESC
                    LIMIT 500";

            return $db->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log("[MailQueueService] getAll() error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Paginación server-side para la DataTable de correos.
     *
     * @return array{rows: array, total: int, page: int, pages: int}
     */
    public static function getPaginated(
        int $page = 1,
        int $limit = 10,
        string $search = '',
        string $sortCol = 'created_at',
        string $sortDir = 'DESC'
    ): array {
        $default = ['rows' => [], 'total' => 0, 'page' => $page, 'pages' => 1];

        // Whitelist de columnas para ORDER BY (anti SQL injection)
        $allowedCols = ['tipo', 'destinatario', 'asunto', 'estado', 'intentos', 'created_at', 'next_retry_at'];
        if (!in_array($sortCol, $allowedCols, true)) {
            $sortCol = 'created_at';
        }
        $sortDir = ($sortDir === 'ASC') ? 'ASC' : 'DESC';

        try {
            $db = DB::connect();
            $params = [];
            $where = '';

            if ($search !== '') {
                $where = "WHERE tipo LIKE :s1 OR destinatario LIKE :s2 OR asunto LIKE :s3";
                $like = '%' . $search . '%';
                $params = [':s1' => $like, ':s2' => $like, ':s3' => $like];
            }

            // Total de registros (con filtro)
            $countSql = "SELECT COUNT(*) FROM mail_queue {$where}";
            $countStmt = $db->prepare($countSql);
            $countStmt->execute($params);
            $total = (int) $countStmt->fetchColumn();

            $pages = max(1, (int) ceil($total / $limit));
            if ($page > $pages) $page = $pages;
            $offset = ($page - 1) * $limit;

            // Filas paginadas (ORDER BY usa whitelist, safe to interpolate)
            $dataSql = "SELECT id, tipo, destinatario, asunto, estado, intentos,
                               next_retry_at, sent_at, created_at
                        FROM mail_queue {$where}
                        ORDER BY {$sortCol} {$sortDir}
                        LIMIT {$limit} OFFSET {$offset}";

            $dataStmt = $db->prepare($dataSql);
            $dataStmt->execute($params);
            $rows = $dataStmt->fetchAll(\PDO::FETCH_ASSOC);

            return ['rows' => $rows, 'total' => $total, 'page' => $page, 'pages' => $pages];
        } catch (\Throwable $e) {
            error_log("[MailQueueService] getPaginated() error: " . $e->getMessage());
            return $default;
        }
    }
}

