<?php
declare(strict_types=1);

namespace App\Modules\Simulator\Controllers;

use App\Modules\Student\Models\StudentAttemptModel;

/**
 * Controller para el flujo de registro de contribuyente SENIAT simulado.
 * Extraído de web.php para respetar MVC.
 */
class RegistroContribuyenteController
{
    private StudentAttemptModel $attemptModel;

    public function __construct()
    {
        $this->attemptModel = new StudentAttemptModel();
    }

    /**
     * Helper: obtiene el intento activo desde la sesión.
     */
    private function getIntentoActivo(): ?array
    {
        if (empty($_SESSION['sim_asignacion_id'])) {
            return null;
        }
        return $this->attemptModel->getIntentoActivo((int) $_SESSION['sim_asignacion_id']);
    }

    /**
     * GET /simulador/registro/contribuyente
     * Muestra formulario de registro con RIF asignado.
     */
    public function index(\App\Core\App $app): string
    {
        $rifAsignado = null;
        try {
            $intento = $this->getIntentoActivo();
            if ($intento && !empty($intento['rif_sucesoral'])) {
                $rifAsignado = $intento['rif_sucesoral'];
            }
        } catch (\Throwable $e) {
            error_log('[RegistroContribuyenteController::index] ' . $e->getMessage());
        }

        return $app->view('simulator/seniat_actual/registro/registro_contribuyente', [
            'rifAsignado' => $rifAsignado
        ]);
    }

    /**
     * POST /simulador/registro/contribuyente/validar
     * Valida RIF ingresado vs asignado, marca sesión para paso 2.
     */
    public function validar(): void
    {
        header('Content-Type: application/json');

        try {
            $rifIngresado = trim($_POST['rif'] ?? '');

            if (empty($rifIngresado)) {
                echo json_encode(['ok' => false, 'msg' => 'Debe ingresar un RIF.']);
                return;
            }

            // Obtener RIF asignado
            $rifAsignado = null;
            $intentoActivo = $this->getIntentoActivo();
            if ($intentoActivo && !empty($intentoActivo['rif_sucesoral'])) {
                $rifAsignado = $intentoActivo['rif_sucesoral'];
            }

            if (!$rifAsignado) {
                echo json_encode(['ok' => false, 'msg' => 'No tiene un RIF asignado aún.']);
                return;
            }

            // Normalizar (quitar guiones) para comparar
            $normalizar = fn($rif) => str_replace('-', '', $rif);
            if ($normalizar($rifIngresado) !== $normalizar($rifAsignado)) {
                echo json_encode(['ok' => false, 'msg' => 'No hay ningún contribuyente registrado con esos datos.']);
                return;
            }

            // Verificar si ya posee un usuario registrado
            if (!empty($intentoActivo['usuario_seniat'])) {
                echo json_encode(['ok' => false, 'msg' => 'Ya posee un usuario registrado en el sistema.']);
                return;
            }

            // Marcar sesión como válida para acceder al paso 2
            $_SESSION['registro_paso1_ok'] = true;
            echo json_encode(['ok' => true]);

        } catch (\Throwable $e) {
            error_log('[RegistroContribuyenteController::validar] ' . $e->getMessage());
            echo json_encode(['ok' => false, 'msg' => 'Error interno del servidor. Intente de nuevo.']);
        }
    }

    /**
     * GET /simulador/registro/contribuyente/paso-2
     * Muestra formulario de usuario/clave (solo si pasó paso 1).
     */
    public function paso2(\App\Core\App $app): string
    {
        // Solo accesible si pasó la validación del paso 1
        if (empty($_SESSION['registro_paso1_ok'])) {
            header('Location: ' . base_url('/simulador/registro/contribuyente'));
            exit;
        }

        try {
            // Verificar si ya posee un usuario registrado (guard adicional)
            $intento = $this->getIntentoActivo();
            if ($intento && !empty($intento['usuario_seniat'])) {
                unset($_SESSION['registro_paso1_ok']);
                header('Location: ' . base_url('/simulador/registro/contribuyente'));
                exit;
            }
        } catch (\Throwable $e) {
            error_log('[RegistroContribuyenteController::paso2] ' . $e->getMessage());
        }

        return $app->view('simulator/seniat_actual/registro/registro_contribuyente_2');
    }

    /**
     * POST /simulador/registro/contribuyente/paso-2/guardar
     * Guarda usuario y clave en sim_intentos.
     */
    public function guardarPaso2(): void
    {
        header('Content-Type: application/json');

        // Validar que pasó por el paso 1
        if (empty($_SESSION['registro_paso1_ok'])) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'msg' => 'Debe completar el paso 1 primero.']);
            return;
        }

        $usuario = trim($_POST['usuario'] ?? '');
        $clave = trim($_POST['clave'] ?? '');

        // Validaciones básicas
        if (empty($usuario) || empty($clave)) {
            echo json_encode(['ok' => false, 'msg' => 'Los campos Usuario y Clave son obligatorios.']);
            return;
        }

        if (strlen($clave) < 8) {
            echo json_encode(['ok' => false, 'msg' => 'La clave debe tener mínimo 8 caracteres.']);
            return;
        }

        // Guardar en la DB
        try {
            $intento = $this->getIntentoActivo();

            if (!$intento) {
                echo json_encode(['ok' => false, 'msg' => 'No se encontró un intento activo.']);
                return;
            }

            // Verificar que no tenga ya un usuario registrado
            if (!empty($intento['usuario_seniat'])) {
                echo json_encode(['ok' => false, 'msg' => 'Ya posee un usuario registrado.']);
                return;
            }

            $db = \App\Core\DB::connect();
            $stmt = $db->prepare("
                UPDATE sim_intentos
                SET usuario_seniat = :usuario,
                    password_rif   = :clave,
                    updated_at     = NOW()
                WHERE id = :id
            ");
            $stmt->execute([
                'usuario' => $usuario,
                'clave' => $clave,
                'id' => (int) $intento['id'],
            ]);

            // Limpiar flag de sesión (el registro ya se completó)
            unset($_SESSION['registro_paso1_ok']);

            // Flash toast para mostrar en la siguiente página
            $_SESSION['flash_toast'] = [
                'type' => 'success',
                'msg' => 'Se ha registrado exitosamente su usuario y clave de acceso.'
            ];

            echo json_encode([
                'ok' => true,
                'redirect' => base_url('/simulador/portal')
            ]);

        } catch (\PDOException $e) {
            // Duplicate key en usuario_seniat
            if ($e->getCode() == '23000') {
                echo json_encode(['ok' => false, 'msg' => 'El nombre de usuario ya está en uso. Elija otro.']);
            } else {
                error_log('[RegistroContribuyenteController::guardarPaso2] Error DB: ' . $e->getMessage());
                echo json_encode(['ok' => false, 'msg' => 'Error al guardar. Intente de nuevo.']);
            }
        } catch (\Throwable $e) {
            error_log('[RegistroContribuyenteController::guardarPaso2] Error: ' . $e->getMessage());
            echo json_encode(['ok' => false, 'msg' => 'Error interno del servidor. Intente de nuevo.']);
        }
    }
}
