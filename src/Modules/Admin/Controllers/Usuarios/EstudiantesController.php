<?php
declare(strict_types=1);

namespace App\Modules\Admin\Controllers\Usuarios;

use App\Modules\Admin\Models\EstudiantesModel;
use App\Core\BitacoraModel;
use App\Core\MailQueueService;
use App\Core\Csrf;

class EstudiantesController
{
    /**
     * Muestra la vista de gestión administrativa de estudiantes.
     * Los datos se cargan server-side via apiList().
     */
    public function index()
    {
        try {
            $model = new EstudiantesModel();
            $conteo = $model->getConteo();
            $secciones = $model->getSecciones();
        } catch (\Throwable $e) {
            error_log('[EstudiantesController::index] ' . $e->getMessage());
            $conteo = ['total' => 0, 'activos' => 0, 'inactivos' => 0];
            $secciones = [];
        }

        require_once __DIR__ . '/../../../../../resources/views/admin/usuarios/gestionar_estudiantes.php';
    }

    /**
     * API paginada para la DataTable de estudiantes (AJAX GET).
     */
    public function apiList(): void
    {
        header('Content-Type: application/json');

        try {
            $page    = max(1, (int) ($_GET['page'] ?? 1));
            $limit   = min(50, max(10, (int) ($_GET['limit'] ?? 15)));
            $search  = trim($_GET['search'] ?? '');
            $sortCol = $_GET['sort'] ?? 'created_at';
            $sortDir = strtoupper($_GET['order'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';
            $status  = trim($_GET['status'] ?? '');

            $model = new EstudiantesModel();
            $result = $model->getPaginated($page, $limit, $search, $sortCol, $sortDir, $status);

            echo json_encode($result);
        } catch (\Throwable $e) {
            error_log('[EstudiantesController::apiList] ' . $e->getMessage());
            echo json_encode(['rows' => [], 'total' => 0, 'page' => 1, 'pages' => 1, 'conteo' => ['total' => 0, 'activos' => 0, 'inactivos' => 0]]);
        }
    }

    /**
     * Registra un nuevo estudiante (AJAX, POST).
     * Flujo: CSRF → validar → duplicados → transacción → bitácora → email → JSON
     */
    public function guardar(): void
    {
        header('Content-Type: application/json');

        try {
            // ── 1. CSRF ──
            if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
                echo json_encode(['success' => false, 'message' => 'Token de seguridad inválido. Recargue la página.']);
                exit;
            }

            // ── 2. Sanitizar ──
            $nacionalidad = trim(strtoupper($_POST['nacionalidad'] ?? ''));
            $cedula       = trim($_POST['cedula'] ?? '');
            $nombres      = trim($_POST['nombres'] ?? '');
            $apellidos    = trim($_POST['apellidos'] ?? '');
            $email        = mb_strtolower(trim($_POST['email'] ?? ''), 'UTF-8'); // Normalizamos a minúsculas

            // ── 3. Validar ──
            $errors = [];

            if (!in_array($nacionalidad, ['V', 'E'], true)) {
                $errors[] = 'Nacionalidad inválida.';
            }
            if (!preg_match('/^\d{6,10}$/', $cedula)) {
                $errors[] = 'La cédula debe contener entre 6 y 10 dígitos.';
            }
            if (empty($nombres) || mb_strlen($nombres) < 2) {
                $errors[] = 'Nombre obligatorio (mín. 2 caracteres).';
            }
            if (empty($apellidos) || mb_strlen($apellidos) < 2) {
                $errors[] = 'Apellido obligatorio (mín. 2 caracteres).';
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Email inválido.';
            }

            if (!empty($errors)) {
                echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
                exit;
            }

            // ── 4. Verificar duplicados (usa trait ValidatesUsuarios) ──
            $model = new EstudiantesModel();

            if ($model->cedulaExists($nacionalidad, $cedula)) {
                echo json_encode(['success' => false, 'message' => 'Ya existe una persona registrada con esa cédula.']);
                exit;
            }
            if ($model->emailExists($email)) {
                echo json_encode(['success' => false, 'message' => 'Ya existe un usuario registrado con ese email.']);
                exit;
            }

            // ── 5. Crear estudiante (transacción) ──
            $seccionId = (int) ($_POST['seccion_id'] ?? 0);

            $result = $model->createEstudiante([
                'nacionalidad' => $nacionalidad,
                'cedula'       => $cedula,
                'nombres'      => $nombres,
                'apellidos'    => $apellidos,
                'email'        => $email,
                'seccion_id'   => $seccionId,
            ]);

            // ── 6. Registrar en bitácora ──
            BitacoraModel::registrar(
                BitacoraModel::STUDENT_CREATED,
                'usuarios',
                null,
                $email,
                'users',
                $result['user_id'],
                "Estudiante creado: $nombres $apellidos ($nacionalidad-$cedula)"
            );

            // ── 7. Enviar correo de bienvenida ──
            $emailSent = false;
            try {
                $baseUrl = rtrim($_ENV['APP_BASE'] ?? 'http://localhost/tesis_francisco', '/');
                $emailBody = $this->buildWelcomeEmail($nombres, $apellidos, $email, $cedula, $baseUrl);
                $emailSent = MailQueueService::send(
                    $email,
                    'Bienvenido al SUCELAB — Su cuenta ha sido creada',
                    $emailBody,
                    'bienvenida',
                    (int) $result['user_id']
                );
            } catch (\Throwable $m) {
                error_log('[EstudiantesController::guardar] Advertencia: Fallo al enviar email asíncrono: ' . $m->getMessage());
            }

            // ── 8. Respuesta ──
            $message = $emailSent
                ? 'Estudiante registrado exitosamente. Se ha enviado un correo de bienvenida.'
                : 'Estudiante registrado exitosamente. El correo de bienvenida será entregado en breve (fallo temporal al despachar).';

            echo json_encode(['success' => true, 'message' => $message]);
            exit;

        } catch (\Throwable $e) {
            error_log('[EstudiantesController::guardar] ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno al crear el estudiante.']);
            exit;
        }
    }

    /**
     * Importa estudiantes desde archivo CSV (AJAX, POST).
     */
    public function importarCSV(): void
    {
        header('Content-Type: application/json');

        try {
            // ── 1. CSRF ──
            if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
                echo json_encode(['success' => false, 'message' => 'Token de seguridad inválido.']);
                exit;
            }

            // ── 2. Validar archivo ──
            $file = $_FILES['csv'] ?? null;
            if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
                echo json_encode(['success' => false, 'message' => 'No se recibió un archivo válido.']);
                exit;
            }

            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if ($ext !== 'csv') {
                echo json_encode(['success' => false, 'message' => 'Solo se permiten archivos .csv']);
                exit;
            }

            // ── 3. Procesar CSV ──
            $handle = fopen($file['tmp_name'], 'r');
            if (!$handle) {
                echo json_encode(['success' => false, 'message' => 'No se pudo leer el archivo.']);
                exit;
            }

            $model = new EstudiantesModel();
            $seccionId = (int) ($_POST['seccion_id'] ?? 0);
            $created  = 0;
            $skipped  = 0;
            $errors   = [];
            $lineNum  = 0;

            $seenEmails  = [];
            $seenCedulas = [];

            // Auto-detectar delimitador
            $delimiter = ',';
            $firstLine = fgets($handle);
            if ($firstLine !== false) {
                if (substr_count($firstLine, ';') > substr_count($firstLine, ',')) {
                    $delimiter = ';';
                }
                rewind($handle);
            }

            while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) {
                $lineNum++;

                if (empty($row) || (count($row) === 1 && trim($row[0]) === '')) {
                    continue;
                }

                if (count($row) !== 4) {
                    $errors[] = "Fila $lineNum: debe tener exactamente 4 columnas (email,cédula,nombres,apellidos).";
                    $skipped++;
                    continue;
                }

                $email     = trim($row[0]);
                // Eliminación del "Byte Order Mark" (BOM) invisible que añade Excel (UTF-8) a la celda A1 (\xEF\xBB\xBF)
                $email     = preg_replace('/^\xEF\xBB\xBF/', '', $email);
                // Normalización obligatoria a minúsculas para evitar colisiones "Case-Sensitive"
                $email     = mb_strtolower($email, 'UTF-8');
                
                // Saneamiento de la cédula: remueve espacios, guiones, puntos y comas.
                $cedulaRaw = strtoupper(trim($row[1]));
                $cedulaRaw = preg_replace('/[\s\-\.,]/', '', $cedulaRaw);
                
                $nombres   = trim($row[2]);
                $apellidos = trim($row[3]);

                // ── Detección e Ignorado Silencioso de Cabeceras (Header Row) ──
                if ($lineNum === 1) {
                    if (stripos($email, 'email') !== false || stripos($email, 'correo') !== false || stripos($cedulaRaw, 'cedula') !== false) {
                        // Saltamos la fila de títulos sin contarla como $skipped ni como error
                        continue;
                    }
                }

                $rowErrors = [];

                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $rowErrors[] = 'email inválido';
                }
                if (!preg_match('/^[VE]\d{6,10}$/', $cedulaRaw)) {
                    $rowErrors[] = 'cédula inválida (V o E + 6-10 dígitos)';
                }
                if (empty($nombres) || mb_strlen($nombres) < 2) {
                    $rowErrors[] = 'nombre inválido (mín. 2 caracteres)';
                }
                if (empty($apellidos) || mb_strlen($apellidos) < 2) {
                    $rowErrors[] = 'apellido inválido (mín. 2 caracteres)';
                }

                if (!empty($rowErrors)) {
                    $errors[] = "Fila $lineNum ($cedulaRaw): " . implode(', ', $rowErrors) . '.';
                    $skipped++;
                    continue;
                }

                $nacionalidad = $cedulaRaw[0];
                $cedula = substr($cedulaRaw, 1);

                // Duplicados dentro del CSV
                $emailLower = mb_strtolower($email);
                if (isset($seenEmails[$emailLower])) {
                    $errors[] = "Fila $lineNum: email '$email' duplicado en el CSV.";
                    $skipped++;
                    continue;
                }
                $cedulaKey = $nacionalidad . '-' . $cedula;
                if (isset($seenCedulas[$cedulaKey])) {
                    $errors[] = "Fila $lineNum: cédula '$cedulaRaw' duplicada en el CSV.";
                    $skipped++;
                    continue;
                }

                // Duplicados contra BD
                if ($model->cedulaExists($nacionalidad, $cedula)) {
                    $errors[] = "Fila $lineNum: cédula $cedulaRaw ya está registrada.";
                    $skipped++;
                    continue;
                }
                if ($model->emailExists($email)) {
                    $errors[] = "Fila $lineNum: email '$email' ya está registrado.";
                    $skipped++;
                    continue;
                }

                // Crear estudiante
                try {
                    $result = $model->createEstudiante([
                        'nacionalidad' => $nacionalidad,
                        'cedula'       => $cedula,
                        'nombres'      => $nombres,
                        'apellidos'    => $apellidos,
                        'email'        => $email,
                        'seccion_id'   => $seccionId,
                    ]);

                    $seenEmails[$emailLower] = true;
                    $seenCedulas[$cedulaKey] = true;
                    $created++;

                    BitacoraModel::registrar(
                        BitacoraModel::STUDENT_CREATED,
                        'usuarios',
                        null,
                        $email,
                        'users',
                        null,
                        "Estudiante importado (CSV): $nombres $apellidos ($cedulaRaw)"
                    );

                    // Encolar correo de bienvenida (Proteger del rollback en caso de fallo externo)
                    try {
                        $baseUrl = rtrim($_ENV['APP_BASE'] ?? 'http://localhost/tesis_francisco', '/');
                        $emailBody = $this->buildWelcomeEmail($nombres, $apellidos, $email, $cedula, $baseUrl);
                        MailQueueService::queue(
                            $email,
                            'Bienvenido al SUCELAB — Su cuenta ha sido creada',
                            $emailBody,
                            'bienvenida',
                            (int) $result['user_id']
                        );
                    } catch (\Throwable $m) {
                        error_log("[EstudiantesController::importarCSV] Error encolando email fila $lineNum: " . $m->getMessage());
                        // Se notifica la advertencia de correo sin invalidar ni saltar la inserción
                        $errors[] = "Fila $lineNum: El estudiante fue creado existosamente, pero falló el encolamiento de su correo de bienvenida.";
                    }

                } catch (\Throwable $e) {
                    error_log("[EstudiantesController::importarCSV] Fila $lineNum: " . $e->getMessage());
                    $errors[] = "Fila $lineNum: error interno al crear el estudiante.";
                    $skipped++;
                }
            }

            fclose($handle);

            $message = "Importación completada: $created estudiante(s) creado(s).";
            if ($skipped > 0) {
                $message .= " $skipped fila(s) omitida(s).";
            }

            echo json_encode([
                'success' => true,
                'message' => $message,
                'created' => $created,
                'skipped' => $skipped,
                'errors'  => $errors,
            ]);
            exit;

        } catch (\Throwable $e) {
            error_log('[EstudiantesController::importarCSV] ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno al procesar el archivo.']);
            exit;
        }
    }

    /**
     * Construye el correo HTML de bienvenida para estudiantes.
     */
    private function buildWelcomeEmail(
        string $nombres,
        string $apellidos,
        string $email,
        string $cedula,
        string $baseUrl
    ): string {
        $n = htmlspecialchars($nombres, ENT_QUOTES, 'UTF-8');
        $a = htmlspecialchars($apellidos, ENT_QUOTES, 'UTF-8');
        $e = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
        $c = htmlspecialchars($cedula, ENT_QUOTES, 'UTF-8');
        $u = htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8');

        return "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <div style='background: linear-gradient(135deg, #1a237e, #283593); color: white; padding: 24px 20px; border-radius: 10px 10px 0 0; text-align: center;'>
                <img src='https://raw.githubusercontent.com/natsuki12/tesis_francisco/refs/heads/main/public/assets/img/logos/sucelab/logo_Mesa%20de%20trabajo%201-04.png' alt='SUCELAB Logo' style='display: block; margin: 0 auto 12px auto; max-width: 120px; height: auto;'>
                <h1 style='margin: 0; font-size: 24px;'>Bienvenido al SUCELAB</h1>
                <p style='margin: 10px 0 0; opacity: 0.9;'>Sistema Universitario de Capacitación y Evaluación en Legislación y Administración de Bienes Sucesorales</p>
            </div>
            <div style='background: #ffffff; padding: 30px; border: 1px solid #e0e0e0; border-top: none;'>
                <p style='font-size: 16px;'>Estimado/a <strong>{$n} {$a}</strong>,</p>
                <p>Su cuenta de estudiante ha sido creada exitosamente en el SUCELAB. A continuación encontrará sus datos de acceso:</p>

                <div style='background: #f5f5f5; border-left: 4px solid #1a237e; padding: 15px; margin: 20px 0; border-radius: 0 5px 5px 0;'>
                    <p style='margin: 5px 0;'><strong>👤 Usuario:</strong> {$e}</p>
                    <p style='margin: 5px 0;'><strong>🔑 Contraseña temporal:</strong> Su número de cédula ({$c})</p>
                </div>

                <div style='background: #fff3e0; border-left: 4px solid #ff9800; padding: 15px; margin: 20px 0; border-radius: 0 5px 5px 0;'>
                    <p style='margin: 0; font-weight: bold; color: #e65100;'>⚠️ Importante</p>
                    <p style='margin: 10px 0 0;'>Al iniciar sesión por primera vez, el sistema le solicitará establecer una nueva contraseña segura.</p>
                </div>

                <div style='text-align: center; margin: 35px 0 15px 0;'>
                    <a href='{$u}/home' style='background-color: #283593; color: #ffffff; padding: 12px 28px; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 16px; display: inline-block; font-family: Arial, sans-serif;'>Ir al sistema</a>
                </div>

                <p style='color: #666; font-size: 13px; margin-top: 30px; border-top: 1px solid #eee; padding-top: 15px;'>
                    Este es un correo automático del SUCELAB. Si no solicitó esta cuenta, puede ignorar este mensaje.
                </p>
            </div>
        </div>";
    }
}
