<?php
declare(strict_types=1);

namespace App\Modules\Admin\Models;

use App\Core\DB;

/**
 * Model para la lectura de catálogos maestros SENIAT del sistema.
 */
class CatalogosModel
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = DB::connect();
    }

    // ══════════════════════════════════════════════════════════
    //  TAB 1: UNIDAD TRIBUTARIA
    // ══════════════════════════════════════════════════════════

    public function getUnidadesTributarias(): array
    {
        try {
            $stmt = $this->db->query("
                SELECT id, anio, valor, fecha_gaceta, activo, created_at
                FROM sim_cat_unidades_tributarias
                ORDER BY anio DESC
            ");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log('[CatalogosModel::getUnidadesTributarias] ' . $e->getMessage());
            return [];
        }
    }

    // ══════════════════════════════════════════════════════════
    //  TAB 2: FISCAL (Grupos, Tramos, Reducciones)
    // ══════════════════════════════════════════════════════════

    public function getGruposTarifa(): array
    {
        try {
            $stmt = $this->db->query("
                SELECT id, nombre, activo, created_at
                FROM sim_cat_grupos_tarifa
                ORDER BY id ASC
            ");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log('[CatalogosModel::getGruposTarifa] ' . $e->getMessage());
            return [];
        }
    }

    public function getTramosTarifa(): array
    {
        try {
            $stmt = $this->db->query("
                SELECT t.id, t.tramo, t.limite_inferior_ut, t.limite_superior_ut,
                       t.porcentaje, t.sustraendo_ut, t.activo,
                       g.nombre AS grupo
                FROM sim_cat_tramos_tarifa t
                LEFT JOIN sim_cat_grupos_tarifa g ON g.id = t.grupo_tarifa_id
                ORDER BY t.grupo_tarifa_id ASC, t.tramo ASC
            ");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log('[CatalogosModel::getTramosTarifa] ' . $e->getMessage());
            return [];
        }
    }

    public function getReducciones(): array
    {
        try {
            $stmt = $this->db->query("
                SELECT id, ordinal, clave, etiqueta, porcentaje_reduccion,
                       es_por_dependiente, cuota_max_beneficiario_ut, activo
                FROM sim_cat_reducciones
                ORDER BY ordinal ASC
            ");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log('[CatalogosModel::getReducciones] ' . $e->getMessage());
            return [];
        }
    }

    // ══════════════════════════════════════════════════════════
    //  TAB 3: BIENES (Inmuebles, Categorías, Tipos, Semovientes)
    // ══════════════════════════════════════════════════════════

    public function getTiposBienInmueble(): array
    {
        try {
            $stmt = $this->db->query("
                SELECT id, nombre, activo, created_at
                FROM sim_cat_tipos_bien_inmueble
                ORDER BY nombre ASC
            ");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log('[CatalogosModel::getTiposBienInmueble] ' . $e->getMessage());
            return [];
        }
    }

    public function getCategoriasBienMueble(): array
    {
        try {
            $stmt = $this->db->query("
                SELECT id, nombre, activo, created_at
                FROM sim_cat_categorias_bien_mueble
                ORDER BY nombre ASC
            ");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log('[CatalogosModel::getCategoriasBienMueble] ' . $e->getMessage());
            return [];
        }
    }

    public function getTiposBienMueble(): array
    {
        try {
            $stmt = $this->db->query("
                SELECT t.id, t.nombre, t.activo, t.created_at,
                       c.nombre AS categoria
                FROM sim_cat_tipos_bien_mueble t
                LEFT JOIN sim_cat_categorias_bien_mueble c ON c.id = t.categoria_bien_mueble_id
                ORDER BY c.nombre ASC, t.nombre ASC
            ");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log('[CatalogosModel::getTiposBienMueble] ' . $e->getMessage());
            return [];
        }
    }

    public function getTiposSemoviente(): array
    {
        try {
            $stmt = $this->db->query("
                SELECT id, nombre, activo, created_at
                FROM sim_cat_tipos_semoviente
                ORDER BY nombre ASC
            ");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log('[CatalogosModel::getTiposSemoviente] ' . $e->getMessage());
            return [];
        }
    }

    // ══════════════════════════════════════════════════════════
    //  TAB 4: PARENTESCOS
    // ══════════════════════════════════════════════════════════

    public function getParentescos(): array
    {
        try {
            $stmt = $this->db->query("
                SELECT p.id, p.clave, p.etiqueta, p.activo, p.grupo_tarifa_id,
                       g.nombre AS grupo_tarifa
                FROM sim_cat_parentescos p
                LEFT JOIN sim_cat_grupos_tarifa g ON g.id = p.grupo_tarifa_id
                ORDER BY p.id ASC
            ");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log('[CatalogosModel::getParentescos] ' . $e->getMessage());
            return [];
        }
    }

    // ══════════════════════════════════════════════════════════
    //  TAB 5: PASIVOS Y HERENCIAS
    // ══════════════════════════════════════════════════════════

    public function getTiposPasivoDeuda(): array
    {
        try {
            $stmt = $this->db->query("
                SELECT id, nombre, activo, created_at
                FROM sim_cat_tipos_pasivo_deuda
                ORDER BY id ASC
            ");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log('[CatalogosModel::getTiposPasivoDeuda] ' . $e->getMessage());
            return [];
        }
    }

    public function getTiposPasivoGasto(): array
    {
        try {
            $stmt = $this->db->query("
                SELECT id, nombre, activo, created_at
                FROM sim_cat_tipos_pasivo_gasto
                ORDER BY id ASC
            ");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log('[CatalogosModel::getTiposPasivoGasto] ' . $e->getMessage());
            return [];
        }
    }

    public function getTipoHerencias(): array
    {
        try {
            $stmt = $this->db->query("
                SELECT id, nombre, descripcion, activo, created_at
                FROM sim_cat_tipoherencias
                ORDER BY id ASC
            ");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log('[CatalogosModel::getTipoHerencias] ' . $e->getMessage());
            return [];
        }
    }
}
