<?php
declare(strict_types=1);

namespace App\Modules\Admin\Controllers\Usuarios;

use App\Core\App;
use App\Core\Csrf;
use App\Core\MailQueueService;
use App\Core\BitacoraModel;
use App\Modules\Admin\Models\ProfesoresModel;

class ProfesoresController
{
    private App $app;

    public function __construct()
    {
        global $app;
        $this->app = $app;
    }

    /**
     * Muestra la vista principal de Gestión de Profesores
     */
    public function index()
    {
        try {
            $model = new ProfesoresModel();
            $profesores = $model->getAll();
        } catch (\Throwable $e) {
            error_log('[ProfesoresController::index] ' . $e->getMessage());
            $profesores = [];
        }

        require_once __DIR__ . '/../../../../../resources/views/admin/usuarios/gestionar_profesores.php';
    }

    /**
     * Crea un Profesor nuevo (AJAX, POST).
     * Valida inputs, verifica duplicados, crea en BD, envía correo.
     */
    public function guardar()
    {
        header('Content-Type: application/json');

        try {
            // ── 1. CSRF ──
            if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
                echo json_encode(['success' => false, 'message' => 'Token de seguridad inválido. Recargue la página.']);
                exit;
            }

            // ── 2. Sanitizar inputs ──
            $nacionalidad = trim($_POST['nacionalidad'] ?? '');
            $cedula       = trim($_POST['cedula'] ?? '');
            $nombres      = trim($_POST['nombres'] ?? '');
            $apellidos    = trim($_POST['apellidos'] ?? '');
            $email        = trim($_POST['email'] ?? '');
            $titulo       = trim($_POST['titulo'] ?? '');

            // ── 3. Validaciones ──
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

            $titulosValidos = ['profesor', 'licenciado', 'ingeniero', 'magíster', 'doctor', 'abogado', 'especialista'];
            if (!in_array(mb_strtolower($titulo), $titulosValidos, true)) {
                $errors[] = 'Título académico inválido.';
            }

            if (!empty($errors)) {
                echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
                exit;
            }

            // ── 4. Verificar duplicados ──
            $model = new ProfesoresModel();

            if ($model->cedulaExists($nacionalidad, $cedula)) {
                echo json_encode(['success' => false, 'message' => 'Ya existe una persona registrada con esa cédula.']);
                exit;
            }
            if ($model->emailExists($email)) {
                echo json_encode(['success' => false, 'message' => 'Ya existe un usuario registrado con ese email.']);
                exit;
            }

            // ── 5. Crear profesor (transacción) ──
            $result = $model->createProfesor([
                'nacionalidad' => $nacionalidad,
                'cedula'       => $cedula,
                'nombres'      => $nombres,
                'apellidos'    => $apellidos,
                'email'        => $email,
                'titulo'       => mb_strtolower($titulo),
            ]);

            // ── 6. Registrar en bitácora ──
            BitacoraModel::registrar(
                BitacoraModel::PROFESSOR_CREATED,
                'usuarios',
                null, // tomará user_id de sesión (admin)
                $email,
                'users',
                $result['user_id'],
                "Profesor creado: $nombres $apellidos ($nacionalidad-$cedula)"
            );

            // ── 7. Enviar correo de bienvenida (no bloquea) ──
            $baseUrl = rtrim($_ENV['APP_BASE'] ?? 'http://localhost/tesis_francisco', '/');
            $emailBody = $this->buildWelcomeEmail($nombres, $apellidos, $email, $cedula, $baseUrl);
            $emailSent = MailQueueService::send(
                $email,
                'Bienvenido al SPDSS — Su cuenta ha sido creada',
                $emailBody,
                'bienvenida',
                (int) $result['user_id']
            );

            // ── 8. Respuesta ──
            $message = $emailSent
                ? 'Profesor registrado exitosamente. Se ha enviado un correo de bienvenida.'
                : 'Profesor registrado exitosamente. El correo de bienvenida será entregado en breve.';

            echo json_encode(['success' => true, 'message' => $message]);
            exit;

        } catch (\Throwable $e) {
            error_log('[ProfesoresController::guardar] ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno al crear el profesor.']);
            exit;
        }
    }

    /**
     * Actualiza un profesor existente (AJAX, POST)
     */
    public function actualizar()
    {
        header('Content-Type: application/json');

        try {
            // ── 1. CSRF ──
            if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
                echo json_encode(['success' => false, 'message' => 'Token de seguridad inválido.']);
                exit;
            }

            // ── 2. Recibir y sanitizar datos ──
            $userId       = (int) ($_POST['user_id'] ?? 0);
            $nombres      = trim(htmlspecialchars($_POST['nombres'] ?? ''));
            $apellidos    = trim(htmlspecialchars($_POST['apellidos'] ?? ''));
            $nacionalidad = trim(strtoupper($_POST['nacionalidad'] ?? 'V'));
            $cedula       = trim($_POST['cedula'] ?? '');
            $email        = trim(filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL));
            $titulo       = trim(htmlspecialchars($_POST['titulo'] ?? ''));

            if ($userId <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID de profesor no válido.']);
                exit;
            }

            if (empty($nombres) || empty($apellidos) || empty($email) || empty($cedula)) {
                echo json_encode(['success' => false, 'message' => 'Todos los campos marcados con * son obligatorios.']);
                exit;
            }

            if (mb_strlen($nombres) < 2 || mb_strlen($apellidos) < 2) {
                echo json_encode(['success' => false, 'message' => 'Nombres y apellidos deben tener al menos 2 caracteres.']);
                exit;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo json_encode(['success' => false, 'message' => 'El formato del correo electrónico es inválido.']);
                exit;
            }

            if (!preg_match('/^\d{6,10}$/', $cedula)) {
                echo json_encode(['success' => false, 'message' => 'La cédula debe contener entre 6 y 10 dígitos.']);
                exit;
            }

            if (!in_array($nacionalidad, ['V', 'E'], true)) {
                echo json_encode(['success' => false, 'message' => 'Nacionalidad inválida. Debe ser V o E.']);
                exit;
            }

            $titulosValidos = ['profesor', 'licenciado', 'ingeniero', 'magíster', 'doctor', 'abogado', 'especialista'];
            if (!in_array(mb_strtolower($titulo), $titulosValidos, true)) {
                echo json_encode(['success' => false, 'message' => 'Título académico inválido.']);
                exit;
            }

            $model = new ProfesoresModel();

            // ── 3. Obtener el profesor actual para conocer su persona_id ──
            $profesorActual = $model->getProfesorById($userId);
            if (!$profesorActual) {
                echo json_encode(['success' => false, 'message' => 'El profesor no existe o fue eliminado.']);
                exit;
            }
            $personaId = (int) $profesorActual['persona_id'];

            // ── 4. Validar duplicados (con Anti-Crash) ──
            if ($model->emailExists($email, $userId)) {
                echo json_encode(['success' => false, 'message' => 'Ese correo electrónico ya pertenece a otro usuario registrado.']);
                exit;
            }

            if ($model->cedulaExists($nacionalidad, $cedula, $personaId)) {
                echo json_encode(['success' => false, 'message' => "La cédula $nacionalidad-$cedula ya está registrada a nombre de otra persona."]);
                exit;
            }

            // ── 5. Actualizar profesor (transacción) ──
            $model->updateProfesor($userId, $personaId, [
                'nacionalidad' => $nacionalidad,
                'cedula'       => $cedula,
                'nombres'      => $nombres,
                'apellidos'    => $apellidos,
                'email'        => $email,
                'titulo'       => mb_strtolower($titulo),
            ]);

            // ── 6. Registrar en bitácora ──
            BitacoraModel::registrar(
                BitacoraModel::PROFESSOR_UPDATED,
                'usuarios',
                null,
                null,
                'users',
                $userId,
                "Se editaron los datos del Profesor: $nombres $apellidos ($nacionalidad-$cedula)"
            );

            echo json_encode(['success' => true, 'message' => 'Profesor actualizado exitosamente.']);
            exit;

        } catch (\Throwable $e) {
            error_log('[ProfesoresController::actualizar] ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno al actualizar el profesor.']);
            exit;
        }
    }

    /**
     * Cambia el estado del profesor: active ↔ inactive (AJAX, POST)
     */
    public function eliminar()
    {
        header('Content-Type: application/json');

        try {
            // ── CSRF ──
            if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
                echo json_encode(['success' => false, 'message' => 'Token de seguridad inválido.']);
                exit;
            }

            $userId = (int) ($_POST['user_id'] ?? 0);
            $action = $_POST['action'] ?? ''; // 'activate' | 'deactivate'

            if ($userId <= 0 || !in_array($action, ['activate', 'deactivate'], true)) {
                echo json_encode(['success' => false, 'message' => 'Parámetros inválidos.']);
                exit;
            }

            $newStatus = $action === 'activate' ? 'active' : 'inactive';

            $model = new ProfesoresModel();
            $updated = $model->toggleStatus($userId, $newStatus);

            if (!$updated) {
                echo json_encode(['success' => false, 'message' => 'No se pudo actualizar el estado del profesor.']);
                exit;
            }

            // Bitácora
            BitacoraModel::registrar(
                BitacoraModel::USER_STATUS_CHANGED,
                'usuarios',
                null,
                null,
                'users',
                $userId,
                "Estado cambiado a: $newStatus"
            );

            $label = $action === 'activate' ? 'activado' : 'desactivado';
            echo json_encode(['success' => true, 'message' => "El profesor ha sido $label correctamente."]);
            exit;

        } catch (\Throwable $e) {
            error_log('[ProfesoresController::eliminar] ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno al cambiar el estado.']);
            exit;
        }
    }

    /**
     * Importa profesores desde un archivo CSV (AJAX, POST).
     * Formato por fila (sin cabecera): email,cedula,nombres,apellidos
     * La cédula debe iniciar con V o E seguido de 6-10 dígitos. Ej: V12345678
     */
    public function importarCSV()
    {
        header('Content-Type: application/json');

        try {
            // ── 1. CSRF ──
            if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
                echo json_encode(['success' => false, 'message' => 'Token de seguridad inválido. Recargue la página.']);
                exit;
            }

            // ── 2. Validar archivo ──
            if (empty($_FILES['csv']) || $_FILES['csv']['error'] !== UPLOAD_ERR_OK) {
                echo json_encode(['success' => false, 'message' => 'No se recibió un archivo válido.']);
                exit;
            }

            $file = $_FILES['csv'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if ($ext !== 'csv') {
                echo json_encode(['success' => false, 'message' => 'El archivo debe ser de tipo .csv']);
                exit;
            }

            if ($file['size'] > 2 * 1024 * 1024) { // 2MB max
                echo json_encode(['success' => false, 'message' => 'El archivo excede el tamaño máximo de 2MB.']);
                exit;
            }

            // ── 3. Abrir y parsear CSV ──
            $handle = fopen($file['tmp_name'], 'r');
            if (!$handle) {
                echo json_encode(['success' => false, 'message' => 'No se pudo leer el archivo.']);
                exit;
            }

            $model = new ProfesoresModel();
            $created  = 0;
            $skipped  = 0;
            $errors   = [];
            $lineNum  = 0;

            // Sets para detectar duplicados dentro del propio CSV
            $seenEmails  = [];
            $seenCedulas = [];

            // Auto-detectar delimitador (coma o punto y coma)
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

                // Saltar filas vacías
                if (empty($row) || (count($row) === 1 && trim($row[0]) === '')) {
                    continue;
                }

                // Validar que tenga exactamente 4 columnas
                if (count($row) !== 4) {
                    $errors[] = "Fila $lineNum: debe tener exactamente 4 columnas (email,cédula,nombres,apellidos).";
                    $skipped++;
                    continue;
                }

                $email    = trim($row[0]);
                $cedulaRaw = strtoupper(trim($row[1]));
                $nombres  = trim($row[2]);
                $apellidos = trim($row[3]);

                $rowErrors = [];

                // ── Validar email ──
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $rowErrors[] = 'email inválido';
                }

                // ── Validar cédula (V o E + 6-10 dígitos) ──
                if (!preg_match('/^[VE]\d{6,10}$/', $cedulaRaw)) {
                    $rowErrors[] = 'cédula inválida (debe ser V o E seguido de 6-10 dígitos)';
                }

                // ── Validar nombres/apellidos ──
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

                // Extraer nacionalidad y número de cédula
                $nacionalidad = $cedulaRaw[0]; // V o E
                $cedula = substr($cedulaRaw, 1); // solo números

                // ── Duplicados dentro del CSV ──
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

                // ── Duplicados contra BD ──
                if ($model->cedulaExists($nacionalidad, $cedula)) {
                    $errors[] = "Fila $lineNum: cédula $cedulaRaw ya está registrada en el sistema.";
                    $skipped++;
                    continue;
                }
                if ($model->emailExists($email)) {
                    $errors[] = "Fila $lineNum: email '$email' ya está registrado.";
                    $skipped++;
                    continue;
                }

                // ── Crear profesor ──
                try {
                    $result = $model->createProfesorBasic([
                        'nacionalidad' => $nacionalidad,
                        'cedula'       => $cedula,
                        'nombres'      => $nombres,
                        'apellidos'    => $apellidos,
                        'email'        => $email,
                    ]);

                    $seenEmails[$emailLower] = true;
                    $seenCedulas[$cedulaKey] = true;
                    $created++;

                    // Bitácora
                    BitacoraModel::registrar(
                        BitacoraModel::PROFESSOR_CREATED,
                        'usuarios',
                        null,
                        $email,
                        'users',
                        null,
                        "Profesor importado (CSV): $nombres $apellidos ($cedulaRaw)"
                    );

                    // Encolar correo de bienvenida (CRON lo envía, no bloquea)
                    $baseUrl = rtrim($_ENV['APP_BASE'] ?? 'http://localhost/tesis_francisco', '/');
                    $emailBody = $this->buildWelcomeEmail($nombres, $apellidos, $email, $cedula, $baseUrl);
                    MailQueueService::queue(
                        $email,
                        'Bienvenido al SPDSS — Su cuenta ha sido creada',
                        $emailBody,
                        'bienvenida',
                        (int) $result['user_id']
                    );

                } catch (\Throwable $e) {
                    error_log("[ProfesoresController::importarCSV] Fila $lineNum: " . $e->getMessage());
                    $errors[] = "Fila $lineNum: error interno al crear el profesor.";
                    $skipped++;
                }
            }

            fclose($handle);

            // ── 4. Respuesta ──
            $message = "Importación completada: $created profesor(es) creado(s).";
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
            error_log('[ProfesoresController::importarCSV] ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno al procesar el archivo.']);
            exit;
        }
    }

    // =========================================================
    // HELPERS PRIVADOS
    // =========================================================

    /**
     * Construye el cuerpo HTML del correo de bienvenida.
     */
    private function buildWelcomeEmail(
        string $nombres,
        string $apellidos,
        string $email,
        string $cedula,
        string $baseUrl
    ): string {
        // Escapar variables para prevenir XSS en clientes de correo
        $n = htmlspecialchars($nombres, ENT_QUOTES, 'UTF-8');
        $a = htmlspecialchars($apellidos, ENT_QUOTES, 'UTF-8');
        $e = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
        $c = htmlspecialchars($cedula, ENT_QUOTES, 'UTF-8');
        $u = htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8');

        return "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <div style='background: linear-gradient(135deg, #1a237e, #283593); color: white; padding: 30px; border-radius: 10px 10px 0 0; text-align: center;'>
                <h1 style='margin: 0; font-size: 24px;'>🎓 Bienvenido al SPDSS</h1>
                <p style='margin: 10px 0 0; opacity: 0.9;'>Sistema Pedagógico de Declaración Sucesoral Simulada</p>
            </div>
            <div style='background: #ffffff; padding: 30px; border: 1px solid #e0e0e0; border-top: none;'>
                <p style='font-size: 16px;'>Estimado/a <strong>{$n} {$a}</strong>,</p>
                <p>Su cuenta de profesor ha sido creada exitosamente en el SPDSS. A continuación encontrará sus datos de acceso:</p>
                
                <div style='background: #f5f5f5; border-left: 4px solid #1a237e; padding: 15px; margin: 20px 0; border-radius: 0 5px 5px 0;'>
                    <p style='margin: 5px 0;'><strong>🔗 URL del sistema:</strong> <a href='{$u}/login'>{$u}/login</a></p>
                    <p style='margin: 5px 0;'><strong>📧 Email:</strong> {$e}</p>
                    <p style='margin: 5px 0;'><strong>🔑 Contraseña temporal:</strong> Su número de cédula ({$c})</p>
                </div>

                <div style='background: #fff3e0; border-left: 4px solid #ff9800; padding: 15px; margin: 20px 0; border-radius: 0 5px 5px 0;'>
                    <p style='margin: 0; font-weight: bold; color: #e65100;'>⚠️ Importante</p>
                    <p style='margin: 10px 0 0;'>Al iniciar sesión por primera vez, el sistema le solicitará establecer una nueva contraseña segura.</p>
                </div>

                <p style='color: #666; font-size: 13px; margin-top: 30px; border-top: 1px solid #eee; padding-top: 15px;'>
                    Este es un correo automático del SPDSS. Si no solicitó esta cuenta, puede ignorar este mensaje.
                </p>
            </div>
        </div>";
    }
}

