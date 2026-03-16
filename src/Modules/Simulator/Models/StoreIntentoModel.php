<?php
declare(strict_types=1);

namespace App\Modules\Simulator\Models;

use App\Core\DB;
use PDO;

/**
 * Modelo para normalizar los datos del intento del estudiante
 * desde borrador_json hacia tablas de la BD.
 * 
 * Análogo a StoreCasoModel (profesor), pero para el flujo del estudiante.
 * TODO: Implementar al final del proyecto.
 */
class StoreIntentoModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = DB::connect();
    }

    /**
     * Normaliza y guarda los datos del intento del estudiante
     * desde el borrador_json a las tablas correspondientes.
     * 
     * @param int $intentoId ID del intento
     * @param array $borrador Datos del borrador JSON decodificado
     * @return bool
     * 
     * TODO: Implementar — leerá bienes_muebles_banco, bienes_inmuebles, etc.
     */
    public function store(int $intentoId, array $borrador): bool
    {
        // TODO: Implementar normalización de datos del estudiante
        return false;
    }
}
