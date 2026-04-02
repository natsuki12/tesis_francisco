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
                       t.categoria_bien_mueble_id,
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

    // ══════════════════════════════════════════════════════════
    //  CRUD GENÉRICO — Tablas simples (id, nombre, activo)
    // ══════════════════════════════════════════════════════════

    /** Whitelist de tablas simples permitidas para CRUD genérico */
    private const TABLAS_SIMPLES = [
        'sim_cat_tipos_bien_inmueble',
        'sim_cat_categorias_bien_mueble',
        'sim_cat_tipos_semoviente',
        'sim_cat_tipos_pasivo_deuda',
        'sim_cat_tipos_pasivo_gasto',
    ];

    /** Whitelist de TODAS las tablas permitidas para toggle activo */
    private const TABLAS_TOGGLE = [
        'sim_cat_tipos_bien_inmueble',
        'sim_cat_categorias_bien_mueble',
        'sim_cat_tipos_semoviente',
        'sim_cat_tipos_pasivo_deuda',
        'sim_cat_tipos_pasivo_gasto',
        'sim_cat_tipos_bien_mueble',
        'sim_cat_parentescos',
        'sim_cat_tipoherencias',
        'sim_cat_unidades_tributarias',
    ];

    /**
     * Crear o actualizar un registro en una tabla simple (nombre + activo).
     * @return array{success: bool, message: string}
     */
    public function upsertSimple(string $tabla, ?int $id, string $nombre): array
    {
        try {
            if (!in_array($tabla, self::TABLAS_SIMPLES, true)) {
                return ['success' => false, 'message' => 'Tabla no permitida.'];
            }

            $nombre = trim($nombre);
            if (empty($nombre) || mb_strlen($nombre) < 2) {
                return ['success' => false, 'message' => 'El nombre debe tener al menos 2 caracteres.'];
            }

            // Verificar duplicado de nombre
            $dupSql = "SELECT id FROM {$tabla} WHERE nombre = ? " . ($id ? "AND id != ?" : "");
            $dupStmt = $this->db->prepare($dupSql);
            $dupStmt->execute($id ? [$nombre, $id] : [$nombre]);
            if ($dupStmt->fetch()) {
                return ['success' => false, 'message' => 'Ya existe un registro con ese nombre.'];
            }

            if ($id) {
                $stmt = $this->db->prepare("UPDATE {$tabla} SET nombre = ? WHERE id = ?");
                $stmt->execute([$nombre, $id]);
                return ['success' => true, 'message' => 'Registro actualizado.'];
            } else {
                $stmt = $this->db->prepare("INSERT INTO {$tabla} (nombre) VALUES (?)");
                $stmt->execute([$nombre]);
                return ['success' => true, 'message' => 'Registro creado.'];
            }
        } catch (\Throwable $e) {
            error_log("[CatalogosModel::upsertSimple:{$tabla}] " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno al guardar.'];
        }
    }

    /**
     * Toggle activo/inactivo en cualquier tabla permitida.
     * @return array{success: bool, message: string, activo?: int}
     */
    public function toggleActivo(string $tabla, int $id): array
    {
        try {
            if (!in_array($tabla, self::TABLAS_TOGGLE, true)) {
                return ['success' => false, 'message' => 'Tabla no permitida.'];
            }

            $stmt = $this->db->prepare("SELECT activo FROM {$tabla} WHERE id = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$row) {
                return ['success' => false, 'message' => 'Registro no encontrado.'];
            }

            $nuevoActivo = (int)$row['activo'] === 1 ? 0 : 1;
            $upd = $this->db->prepare("UPDATE {$tabla} SET activo = ? WHERE id = ?");
            $upd->execute([$nuevoActivo, $id]);

            $label = $nuevoActivo ? 'activado' : 'desactivado';
            return ['success' => true, 'message' => "Registro {$label}.", 'activo' => $nuevoActivo];
        } catch (\Throwable $e) {
            error_log("[CatalogosModel::toggleActivo:{$tabla}] " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno.'];
        }
    }

    // ══════════════════════════════════════════════════════════
    //  CRUD ESPECÍFICO — Parentescos
    // ══════════════════════════════════════════════════════════

    public function upsertParentesco(?int $id, string $clave, string $etiqueta, ?int $grupoTarifaId): array
    {
        try {
            $clave = trim($clave);
            $etiqueta = trim($etiqueta);

            if (empty($clave) || empty($etiqueta)) {
                return ['success' => false, 'message' => 'Clave y etiqueta son obligatorios.'];
            }

            // Verificar duplicado de clave
            $dupSql = "SELECT id FROM sim_cat_parentescos WHERE clave = ?" . ($id ? " AND id != ?" : "");
            $dupStmt = $this->db->prepare($dupSql);
            $dupStmt->execute($id ? [$clave, $id] : [$clave]);
            if ($dupStmt->fetch()) {
                return ['success' => false, 'message' => 'Ya existe un parentesco con esa clave.'];
            }

            if ($id) {
                $stmt = $this->db->prepare("UPDATE sim_cat_parentescos SET clave = ?, etiqueta = ?, grupo_tarifa_id = ? WHERE id = ?");
                $stmt->execute([$clave, $etiqueta, $grupoTarifaId ?: null, $id]);
                return ['success' => true, 'message' => 'Parentesco actualizado.'];
            } else {
                $stmt = $this->db->prepare("INSERT INTO sim_cat_parentescos (clave, etiqueta, grupo_tarifa_id) VALUES (?, ?, ?)");
                $stmt->execute([$clave, $etiqueta, $grupoTarifaId ?: null]);
                return ['success' => true, 'message' => 'Parentesco creado.'];
            }
        } catch (\Throwable $e) {
            error_log('[CatalogosModel::upsertParentesco] ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno al guardar parentesco.'];
        }
    }

    // ══════════════════════════════════════════════════════════
    //  CRUD ESPECÍFICO — Tipos Bien Mueble
    // ══════════════════════════════════════════════════════════

    public function upsertTipoBienMueble(?int $id, string $nombre, int $categoriaId): array
    {
        try {
            $nombre = trim($nombre);
            if (empty($nombre) || mb_strlen($nombre) < 2) {
                return ['success' => false, 'message' => 'El nombre debe tener al menos 2 caracteres.'];
            }
            if ($categoriaId <= 0) {
                return ['success' => false, 'message' => 'Debe seleccionar una categoría.'];
            }

            if ($id) {
                $stmt = $this->db->prepare("UPDATE sim_cat_tipos_bien_mueble SET nombre = ?, categoria_bien_mueble_id = ? WHERE id = ?");
                $stmt->execute([$nombre, $categoriaId, $id]);
                return ['success' => true, 'message' => 'Tipo de bien mueble actualizado.'];
            } else {
                $stmt = $this->db->prepare("INSERT INTO sim_cat_tipos_bien_mueble (nombre, categoria_bien_mueble_id) VALUES (?, ?)");
                $stmt->execute([$nombre, $categoriaId]);
                return ['success' => true, 'message' => 'Tipo de bien mueble creado.'];
            }
        } catch (\Throwable $e) {
            error_log('[CatalogosModel::upsertTipoBienMueble] ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno al guardar tipo de bien mueble.'];
        }
    }

    // ══════════════════════════════════════════════════════════
    //  CRUD ESPECÍFICO — Tipo Herencias
    // ══════════════════════════════════════════════════════════

    public function upsertTipoHerencia(?int $id, string $nombre, string $descripcion): array
    {
        try {
            $nombre = trim($nombre);
            $descripcion = trim($descripcion);

            if (empty($nombre) || mb_strlen($nombre) < 2) {
                return ['success' => false, 'message' => 'El nombre debe tener al menos 2 caracteres.'];
            }

            if ($id) {
                $stmt = $this->db->prepare("UPDATE sim_cat_tipoherencias SET nombre = ?, descripcion = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$nombre, $descripcion ?: null, $id]);
                return ['success' => true, 'message' => 'Tipo de herencia actualizado.'];
            } else {
                $stmt = $this->db->prepare("INSERT INTO sim_cat_tipoherencias (nombre, descripcion) VALUES (?, ?)");
                $stmt->execute([$nombre, $descripcion ?: null]);
                return ['success' => true, 'message' => 'Tipo de herencia creado.'];
            }
        } catch (\Throwable $e) {
            error_log('[CatalogosModel::upsertTipoHerencia] ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno al guardar tipo de herencia.'];
        }
    }

    // ══════════════════════════════════════════════════════════
    //  CRUD ESPECÍFICO — Unidad Tributaria
    // ══════════════════════════════════════════════════════════

    public function upsertUnidadTributaria(?int $id, int $anio, float $valor, string $fechaGaceta): array
    {
        try {
            if ($anio < 1990 || $anio > 2100) {
                return ['success' => false, 'message' => 'Año inválido.'];
            }
            if ($valor <= 0) {
                return ['success' => false, 'message' => 'El valor debe ser mayor a cero.'];
            }
            if (empty($fechaGaceta)) {
                return ['success' => false, 'message' => 'La fecha de gaceta es obligatoria.'];
            }

            // Verificar duplicado de año
            $dupSql = "SELECT id FROM sim_cat_unidades_tributarias WHERE anio = ?" . ($id ? " AND id != ?" : "");
            $dupStmt = $this->db->prepare($dupSql);
            $dupStmt->execute($id ? [$anio, $id] : [$anio]);
            if ($dupStmt->fetch()) {
                return ['success' => false, 'message' => "Ya existe una UT registrada para el año {$anio}."];
            }

            if ($id) {
                $stmt = $this->db->prepare("UPDATE sim_cat_unidades_tributarias SET anio = ?, valor = ?, fecha_gaceta = ? WHERE id = ?");
                $stmt->execute([$anio, $valor, $fechaGaceta, $id]);
                return ['success' => true, 'message' => 'Unidad Tributaria actualizada.'];
            } else {
                $stmt = $this->db->prepare("INSERT INTO sim_cat_unidades_tributarias (anio, valor, fecha_gaceta) VALUES (?, ?, ?)");
                $stmt->execute([$anio, $valor, $fechaGaceta]);
                return ['success' => true, 'message' => 'Unidad Tributaria creada.'];
            }
        } catch (\Throwable $e) {
            error_log('[CatalogosModel::upsertUnidadTributaria] ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno al guardar UT.'];
        }
    }
}

