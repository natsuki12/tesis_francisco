<?php
declare(strict_types=1);

namespace App\Modules\Simulator\Controllers;

/**
 * Shared controller for RIF lookup across all Bienes Muebles modules.
 * Single endpoint: POST /api/buscar-rif
 */
class RifController
{
    /** POST /api/buscar-rif */
    public function buscarRif(): void
    {
        header('Content-Type: application/json');

        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $rif = trim($input['rif'] ?? '');

            if ($rif === '') {
                http_response_code(400);
                echo json_encode(['ok' => false, 'error' => 'RIF requerido']);
                return;
            }

            $db = \App\Core\DB::connect();

            // 1. Search in sim_empresas
            $stmt = $db->prepare("SELECT id, razon_social, rif FROM sim_empresas WHERE rif = :rif LIMIT 1");
            $stmt->execute(['rif' => $rif]);
            $empresa = $stmt->fetch();

            if ($empresa) {
                echo json_encode([
                    'ok'           => true,
                    'found'        => true,
                    'razon_social' => $empresa['razon_social'] ?? '',
                    'fuente'       => 'empresa',
                ]);
                return;
            }

            // 2. Search in sim_personas
            $stmt2 = $db->prepare("SELECT id, nombres, apellidos, rif_personal FROM sim_personas WHERE rif_personal = :rif LIMIT 1");
            $stmt2->execute(['rif' => $rif]);
            $persona = $stmt2->fetch();

            if ($persona) {
                $nombreCompleto = trim(($persona['nombres'] ?? '') . ' ' . ($persona['apellidos'] ?? ''));
                echo json_encode([
                    'ok'           => true,
                    'found'        => true,
                    'razon_social' => $nombreCompleto,
                    'fuente'       => 'persona',
                ]);
                return;
            }

            echo json_encode(['ok' => true, 'found' => false]);
        } catch (\Throwable $e) {
            error_log('[RifController::buscarRif] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'Error interno del servidor']);
        }
    }
}
