<?php

declare(strict_types=1);

namespace App\Modules\Simulator\Validators;

use App\Core\DB;
use App\Modules\Simulator\Services\BorradorService;
use PDO;

/**
 * DeclaracionValidator — Validador de Declaración Sucesoral
 *
 * Compara los datos de bienes, pasivos, exclusiones, tipo de herencia
 * y cálculos del borrador JSON del estudiante contra los datos
 * del caso asignado en la base de datos.
 *
 * Complementa al RSValidator (que valida causante, relaciones, direcciones).
 *
 * Retorna un array de errores por sección.
 */
class DeclaracionValidator
{
    private PDO $db;

    public function __construct()
    {
        $this->db = DB::connect();
    }

    // ════════════════════════════════════════════════════════
    //  MÉTODO PRINCIPAL
    // ════════════════════════════════════════════════════════

    /**
     * Valida el borrador de un intento contra los datos del caso en la DB.
     *
     * @param int $intentoId  ID del intento activo
     * @param int $estudianteId  ID del estudiante (para ownership)
     * @return array{ok: bool, errores: array<string, string[]>}
     */
    public function validar(int $intentoId, int $estudianteId): array
    {
        try {
            // 1. Cargar intento + caso
            $intento = $this->getIntentoConCaso($intentoId, $estudianteId);
            if (!$intento) {
                return ['ok' => false, 'errores' => ['general' => ['Intento no encontrado o no pertenece al estudiante.']]];
            }

            $borrador = json_decode($intento['borrador_json'] ?: '{}', true);
            if (!is_array($borrador)) $borrador = [];
            $casoId = (int) $intento['caso_id'];

            $errores = [];

            // 2. Validar tipo de herencia
            $e = $this->validarTipoHerencia($borrador, $casoId);
            if (!empty($e)) $errores['tipo_herencia'] = $e;

            // 3. Validar bienes inmuebles
            $e = $this->validarBienesInmuebles($borrador, $casoId);
            if (!empty($e)) $errores['bienes_inmuebles'] = $e;

            // 4. Validar bienes muebles (12 categorías)
            $e = $this->validarBienesMuebles($borrador, $casoId);
            if (!empty($e)) $errores['bienes_muebles'] = $e;

            // 5. Validar pasivos deuda
            $e = $this->validarPasivosDeuda($borrador, $casoId);
            if (!empty($e)) $errores['pasivos_deuda'] = $e;

            // 6. Validar pasivos gastos
            $e = $this->validarPasivosGastos($borrador, $casoId);
            if (!empty($e)) $errores['pasivos_gastos'] = $e;

            // 7. Validar exenciones
            $e = $this->validarExenciones($borrador, $casoId);
            if (!empty($e)) $errores['exenciones'] = $e;

            // 8. Validar exoneraciones
            $e = $this->validarExoneraciones($borrador, $casoId);
            if (!empty($e)) $errores['exoneraciones'] = $e;

            // 9. Validar desgravámenes (calculados)
            $e = $this->validarDesgravamenes($borrador, $casoId);
            if (!empty($e)) $errores['desgravamenes'] = $e;

            // 10. Validar totales
            $e = $this->validarTotales($borrador, $casoId, $intento);
            if (!empty($e)) $errores['totales'] = $e;

            return [
                'ok' => empty($errores),
                'errores' => $errores,
            ];
        } catch (\Throwable $e) {
            error_log('DeclaracionValidator::validar() error: ' . $e->getMessage());
            return [
                'ok' => false,
                'errores' => ['general' => ['Error interno durante la validación. Contacte al administrador.']],
            ];
        }
    }

    // ════════════════════════════════════════════════════════
    //  CARGA DEL INTENTO Y CASO
    // ════════════════════════════════════════════════════════

    private function getIntentoConCaso(int $intentoId, int $estudianteId): ?array
    {
        $sql = "
            SELECT
                i.id, i.borrador_json, i.estado,
                ce.id AS caso_id, ce.causante_id, ce.tipo_sucesion
            FROM sim_intentos i
            INNER JOIN sim_caso_asignaciones a  ON a.id  = i.asignacion_id
            INNER JOIN sim_caso_configs cfg     ON cfg.id = a.config_id
            INNER JOIN sim_casos_estudios ce    ON ce.id  = cfg.caso_id
            WHERE i.id = :int_id
              AND a.estudiante_id = :est_id
            LIMIT 1
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':int_id', $intentoId, PDO::PARAM_INT);
        $stmt->bindValue(':est_id', $estudianteId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    // ════════════════════════════════════════════════════════
    //  VALIDACIÓN: TIPO DE HERENCIA
    // ════════════════════════════════════════════════════════

    private function validarTipoHerencia(array $borrador, int $casoId): array
    {
        $errores = [];

        // Borrador: tipo_herencia puede ser array de objetos con tipo_herencia_id
        $tiposB = $borrador['tipo_herencia'] ?? [];
        if (empty($tiposB)) {
            return ['No se encontraron tipos de herencia en el borrador.'];
        }

        // DB
        $tiposDb = $this->getTiposHerenciaDelCaso($casoId);
        if (empty($tiposDb)) {
            return ['No se encontraron tipos de herencia en el caso.'];
        }

        // Extraer IDs del borrador
        $idsB = [];
        foreach ($tiposB as $t) {
            $id = (int) ($t['tipo_herencia_id'] ?? 0);
            if ($id > 0) $idsB[] = $id;
        }

        // Extraer IDs de la DB
        $idsDb = array_map(fn($t) => (int) $t['tipo_herencia_id'], $tiposDb);

        // Tipos en DB que faltan en borrador
        $faltantes = array_diff($idsDb, $idsB);
        foreach ($faltantes as $id) {
            $nombre = $this->getNombreTipoHerencia($id, $tiposDb);
            $errores[] = "Falta el tipo de herencia: {$nombre}.";
        }

        // Tipos en borrador que sobran
        $sobrantes = array_diff($idsB, $idsDb);
        foreach ($sobrantes as $id) {
            $nombre = '';
            foreach ($tiposB as $t) {
                if ((int)($t['tipo_herencia_id'] ?? 0) === $id) {
                    $nombre = $t['nombre'] ?? $t['tipo_nombre'] ?? "ID {$id}";
                    break;
                }
            }
            $errores[] = "Se ingresó tipo de herencia no esperado: {$nombre}.";
        }

        // Para tipos que coinciden, validar datos asociados
        $comunes = array_intersect($idsB, $idsDb);
        foreach ($comunes as $id) {
            $bItem = $this->findByTipoHerenciaId($tiposB, $id);
            $dbItem = $this->findByTipoHerenciaId($tiposDb, $id);
            if (!$bItem || !$dbItem) continue;

            // Testamento: subtipo + fecha
            if (!empty($dbItem['subtipo_testamento'])) {
                $subB = mb_strtoupper(trim($bItem['subtipo_testamento'] ?? ''));
                $subDb = mb_strtoupper(trim($dbItem['subtipo_testamento'] ?? ''));
                if ($subB && $subDb && $subB !== $subDb) {
                    $errores[] = "Subtipo de testamento no coincide. Esperado: {$subDb}, ingresado: {$subB}.";
                }
            }
            if (!empty($dbItem['fecha_testamento'])) {
                $fechaB = $this->normalizarFecha($bItem['fecha_testamento'] ?? '');
                $fechaDb = $this->normalizarFecha($dbItem['fecha_testamento'] ?? '');
                if ($fechaB && $fechaDb && $fechaB !== $fechaDb) {
                    $errores[] = "Fecha de testamento no coincide. Esperado: {$fechaDb}, ingresado: {$fechaB}.";
                }
            }
            if (!empty($dbItem['fecha_conclusion_inventario'])) {
                $fechaB = $this->normalizarFecha($bItem['fecha_conclusion_inventario'] ?? '');
                $fechaDb = $this->normalizarFecha($dbItem['fecha_conclusion_inventario'] ?? '');
                if ($fechaB && $fechaDb && $fechaB !== $fechaDb) {
                    $errores[] = "Fecha de conclusión de inventario no coincide. Esperado: {$fechaDb}, ingresado: {$fechaB}.";
                }
            }
        }

        return $errores;
    }

    private function getTiposHerenciaDelCaso(int $casoId): array
    {
        $sql = "
            SELECT r.*, th.nombre AS tipo_nombre
            FROM sim_caso_tipoherencia_rel r
            LEFT JOIN sim_cat_tipoherencias th ON r.tipo_herencia_id = th.id
            WHERE r.caso_estudio_id = :caso_id
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':caso_id', $casoId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getNombreTipoHerencia(int $id, array $tipos): string
    {
        foreach ($tipos as $t) {
            if ((int) $t['tipo_herencia_id'] === $id) {
                return $t['tipo_nombre'] ?? "ID {$id}";
            }
        }
        return "ID {$id}";
    }

    private function findByTipoHerenciaId(array $items, int $id): ?array
    {
        foreach ($items as $item) {
            if ((int) ($item['tipo_herencia_id'] ?? 0) === $id) return $item;
        }
        return null;
    }

    // ════════════════════════════════════════════════════════
    //  VALIDACIÓN: BIENES INMUEBLES
    // ════════════════════════════════════════════════════════

    private function validarBienesInmuebles(array $borrador, int $casoId): array
    {
        $errores = [];

        $itemsB = $borrador['bienes_inmuebles'] ?? [];
        $itemsDb = $this->getBienesInmueblesDelCaso($casoId);

        // Contar
        $countB = count($itemsB);
        $countDb = count($itemsDb);
        if ($countB !== $countDb) {
            $errores[] = "Se esperan {$countDb} bien(es) inmueble(s) pero se ingresaron {$countB}.";
        }

        // Match por valor_declarado + descripción
        $dbUsados = [];
        foreach ($itemsB as $i => $bItem) {
            $pos = $i + 1;
            $matched = false;
            $valorB = $this->parseDecimal($bItem['valor_declarado'] ?? '0');

            foreach ($itemsDb as $j => $dbItem) {
                if (in_array($j, $dbUsados)) continue;

                $valorDb = (float) ($dbItem['valor_declarado'] ?? 0);
                $descB = mb_strtoupper(trim($bItem['descripcion'] ?? ''));
                $descDb = mb_strtoupper(trim($dbItem['descripcion'] ?? ''));

                // Match por valor O descripción
                if (abs($valorB - $valorDb) < 0.01 || ($descB !== '' && $descDb !== '' && $descB === $descDb)) {
                    $matched = true;
                    $dbUsados[] = $j;

                    // Comparar campos
                    $this->compararCampoDecimal($bItem, $dbItem, 'valor_declarado', "Inmueble #{$pos}", $errores);
                    $this->compararCampoDecimal($bItem, $dbItem, 'porcentaje', "Inmueble #{$pos}", $errores);
                    $this->compararCampoTexto($bItem, $dbItem, 'descripcion', "Inmueble #{$pos}", $errores);

                    // Vivienda principal
                    $vpB = ($bItem['vivienda_principal'] ?? 'false') === 'true' ? 1 : 0;
                    $vpDb = (int) ($dbItem['es_vivienda_principal'] ?? 0);
                    if ($vpB !== $vpDb) {
                        $esperado = $vpDb ? 'Sí' : 'No';
                        $ingresado = $vpB ? 'Sí' : 'No';
                        $errores[] = "Inmueble #{$pos}: Vivienda principal no coincide. Esperado: {$esperado}, ingresado: {$ingresado}.";
                    }

                    // Datos de registro
                    $this->compararCampoTexto($bItem, $dbItem, 'oficina_registro', "Inmueble #{$pos}", $errores, 'oficina_registro');
                    $this->compararCampoTexto($bItem, $dbItem, 'nro_registro', "Inmueble #{$pos}", $errores, 'nro_registro');
                    $this->compararCampoTexto($bItem, $dbItem, 'libro', "Inmueble #{$pos}", $errores);
                    $this->compararCampoTexto($bItem, $dbItem, 'protocolo', "Inmueble #{$pos}", $errores);
                    $this->compararCampoTexto($bItem, $dbItem, 'trimestre', "Inmueble #{$pos}", $errores);
                    $this->compararCampoTexto($bItem, $dbItem, 'asiento_registral', "Inmueble #{$pos}", $errores, 'asiento_registral');
                    $this->compararCampoTexto($bItem, $dbItem, 'matricula', "Inmueble #{$pos}", $errores);
                    $this->compararCampoTexto($bItem, $dbItem, 'folio_real_anio', "Inmueble #{$pos}", $errores, 'folio_real_anio');

                    // Fecha de registro
                    $fechaRegB = $this->normalizarFecha($bItem['fecha_registro'] ?? '');
                    $fechaRegDb = $this->normalizarFecha($dbItem['fecha_registro'] ?? '');
                    if ($fechaRegB && $fechaRegDb && $fechaRegB !== $fechaRegDb) {
                        $errores[] = "Inmueble #{$pos}: Fecha de registro no coincide. Esperado: {$fechaRegDb}, ingresado: {$fechaRegB}.";
                    }

                    // Superficies
                    $this->compararCampoDecimal($bItem, $dbItem, 'superficie_construida', "Inmueble #{$pos}", $errores);
                    $this->compararCampoDecimal($bItem, $dbItem, 'superficie_no_construida', "Inmueble #{$pos}", $errores);
                    $this->compararCampoDecimal($bItem, $dbItem, 'area_superficie', "Inmueble #{$pos}", $errores);
                    $this->compararCampoDecimal($bItem, $dbItem, 'valor_original', "Inmueble #{$pos}", $errores);

                    // Bien litigioso
                    $litB = ($bItem['bien_litigioso'] ?? 'false') === 'true' ? 1 : 0;
                    $litDb = (int) ($dbItem['es_bien_litigioso'] ?? 0);
                    if ($litB !== $litDb) {
                        $esperado = $litDb ? 'Sí' : 'No';
                        $ingresado = $litB ? 'Sí' : 'No';
                        $errores[] = "Inmueble #{$pos}: Bien litigioso no coincide. Esperado: {$esperado}, ingresado: {$ingresado}.";
                    }

                    break;
                }
            }

            if (!$matched) {
                $desc = mb_substr(trim($bItem['descripcion'] ?? ''), 0, 60);
                $errores[] = "Inmueble #{$pos} ({$desc}...) no se encontró en el caso asignado.";
            }
        }

        // Inmuebles de la DB que no fueron ingresados
        foreach ($itemsDb as $j => $dbItem) {
            if (!in_array($j, $dbUsados)) {
                $desc = mb_substr(trim($dbItem['descripcion'] ?? ''), 0, 60);
                $errores[] = "Falta el inmueble: {$desc}... (valor: {$dbItem['valor_declarado']}).";
            }
        }

        return $errores;
    }

    private function getBienesInmueblesDelCaso(int $casoId): array
    {
        $sql = "
            SELECT bi.*
            FROM sim_caso_bienes_inmuebles bi
            WHERE bi.caso_estudio_id = :caso_id
              AND bi.deleted_at IS NULL
            ORDER BY bi.id ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':caso_id', $casoId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ════════════════════════════════════════════════════════
    //  VALIDACIÓN: BIENES MUEBLES (12 categorías)
    // ════════════════════════════════════════════════════════

    /**
     * Mapping: borrador key → DB categoria_bien_mueble_id
     */
    private const MUEBLE_MAP = [
        'bienes_muebles_banco'                 => 1,
        'bienes_muebles_seguro'                => 2,
        'bienes_muebles_transporte'            => 3,
        'bienes_muebles_opciones_compra'       => 4,
        'bienes_muebles_cuentas_efectos'       => 5,
        'bienes_muebles_semovientes'           => 6,
        'bienes_muebles_bonos'                 => 7,
        'bienes_muebles_acciones'              => 8,
        'bienes_muebles_prestaciones_sociales' => 9,
        'bienes_muebles_caja_ahorro'           => 10,
        'bienes_muebles_plantaciones'          => 11,
        'bienes_muebles_otros'                 => 12,
    ];

    private const MUEBLE_LABELS = [
        1 => 'Banco', 2 => 'Seguro', 3 => 'Transporte', 4 => 'Opciones de Compra',
        5 => 'Cuentas y Efectos por Cobrar', 6 => 'Semovientes', 7 => 'Bonos',
        8 => 'Acciones', 9 => 'Prestaciones Sociales', 10 => 'Caja de Ahorro',
        11 => 'Plantaciones', 12 => 'Otros',
    ];

    private function validarBienesMuebles(array $borrador, int $casoId): array
    {
        $errores = [];

        // Fetch all muebles from DB
        $mueblesSql = "
            SELECT bm.*,
                cat.nombre AS categoria_nombre,
                tbm.nombre AS tipo_nombre
            FROM sim_caso_bienes_muebles bm
            LEFT JOIN sim_cat_categorias_bien_mueble cat ON bm.categoria_bien_mueble_id = cat.id
            LEFT JOIN sim_cat_tipos_bien_mueble tbm ON bm.tipo_bien_mueble_id = tbm.id
            WHERE bm.caso_estudio_id = :caso_id
              AND bm.deleted_at IS NULL
            ORDER BY bm.categoria_bien_mueble_id ASC, bm.id ASC
        ";
        $stmt = $this->db->prepare($mueblesSql);
        $stmt->bindValue(':caso_id', $casoId, PDO::PARAM_INT);
        $stmt->execute();
        $allMueblesDb = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Group DB items by category
        $dbByCat = [];
        foreach ($allMueblesDb as $m) {
            $catId = (int) $m['categoria_bien_mueble_id'];
            $dbByCat[$catId][] = $m;
        }

        // Validate each category
        foreach (self::MUEBLE_MAP as $borradorKey => $catId) {
            $label = self::MUEBLE_LABELS[$catId] ?? "Cat.{$catId}";
            $itemsB = $borrador[$borradorKey] ?? [];
            $itemsDb = $dbByCat[$catId] ?? [];

            $countB = count($itemsB);
            $countDb = count($itemsDb);

            // Skip if both empty
            if ($countB === 0 && $countDb === 0) continue;

            if ($countB !== $countDb) {
                $errores[] = "{$label}: se esperan {$countDb} bien(es) pero se ingresaron {$countB}.";
            }

            // Match items by valor_declarado
            $dbUsados = [];
            foreach ($itemsB as $i => $bItem) {
                $pos = $i + 1;
                $matched = false;
                $valorB = $this->parseDecimal($bItem['valor_declarado'] ?? '0');

                foreach ($itemsDb as $j => $dbItem) {
                    if (in_array($j, $dbUsados)) continue;

                    $valorDb = (float) ($dbItem['valor_declarado'] ?? 0);

                    if (abs($valorB - $valorDb) < 0.01) {
                        $matched = true;
                        $dbUsados[] = $j;

                        // Compare tipo_bien (borrador stores code, DB stores tipo_bien_mueble_id)
                        $tipoB = (int) ($bItem['tipo_bien'] ?? 0);
                        $tipoDb = (int) ($dbItem['tipo_bien_mueble_id'] ?? 0);
                        if ($tipoB > 0 && $tipoDb > 0 && $tipoB !== $tipoDb) {
                            $tipoNombreDb = $dbItem['tipo_nombre'] ?? $tipoDb;
                            $tipoNombreB = $bItem['tipo_bien_nombre'] ?? $tipoB;
                            $errores[] = "{$label} #{$pos}: Tipo de bien no coincide. Esperado: {$tipoNombreDb}, ingresado: {$tipoNombreB}.";
                        }

                        // Compare porcentaje
                        $this->compararCampoDecimal($bItem, $dbItem, 'porcentaje', "{$label} #{$pos}", $errores);

                        // Compare descripcion
                        $this->compararCampoTexto($bItem, $dbItem, 'descripcion', "{$label} #{$pos}", $errores);

                        // Bien litigioso
                        $litB = ($bItem['bien_litigioso'] ?? 'false') === 'true' ? 1 : 0;
                        $litDb = (int) ($dbItem['es_bien_litigioso'] ?? 0);
                        if ($litB !== $litDb) {
                            $esperado = $litDb ? 'Sí' : 'No';
                            $ingresado = $litB ? 'Sí' : 'No';
                            $errores[] = "{$label} #{$pos}: Bien litigioso no coincide. Esperado: {$esperado}, ingresado: {$ingresado}.";
                        }

                        // Category-specific child comparisons
                        $childErrors = $this->validarMuebleHijo($catId, $bItem, $dbItem, "{$label} #{$pos}");
                        $errores = array_merge($errores, $childErrors);

                        break;
                    }
                }

                if (!$matched) {
                    $desc = mb_substr(trim($bItem['descripcion'] ?? ''), 0, 50);
                    $errores[] = "{$label} #{$pos} ({$desc}...) no se encontró en el caso.";
                }
            }

            // DB items not matched
            foreach ($itemsDb as $j => $dbItem) {
                if (!in_array($j, $dbUsados)) {
                    $desc = mb_substr(trim($dbItem['descripcion'] ?? ''), 0, 50);
                    $tipo = $dbItem['tipo_nombre'] ?? '';
                    $errores[] = "{$label}: Falta bien — {$tipo} {$desc}... (valor: {$dbItem['valor_declarado']}).";
                }
            }
        }

        return $errores;
    }

    /**
     * Validates child-table specific fields for a mueble.
     */
    private function validarMuebleHijo(int $catId, array $bItem, array $dbItem, string $label): array
    {
        $errores = [];
        $bienMuebleId = (int) ($dbItem['id'] ?? 0);
        if ($bienMuebleId === 0) return $errores;

        switch ($catId) {
            case 1: // Banco
                $hijo = $this->fetchChildRow('sim_caso_bm_banco', $bienMuebleId);
                if ($hijo) {
                    $this->compararIds($bItem, 'banco', $hijo, 'banco_id', $label, 'Banco', $errores);
                    $this->compararTextoSimple($bItem['numero_cuenta'] ?? '', $hijo['numero_cuenta'] ?? '', $label, 'Número de cuenta', $errores);
                }
                break;

            case 2: // Seguro
                $hijo = $this->fetchChildRow('sim_caso_bm_seguro', $bienMuebleId);
                if ($hijo) {
                    $this->compararIds($bItem, 'empresa', $hijo, 'empresa_id', $label, 'Empresa', $errores);
                    $this->compararTextoSimple($bItem['numero_prima'] ?? '', $hijo['numero_prima'] ?? '', $label, 'Número de prima', $errores);
                }
                break;

            case 3: // Transporte
                $hijo = $this->fetchChildRow('sim_caso_bm_transporte', $bienMuebleId);
                if ($hijo) {
                    $this->compararTextoSimple($bItem['anio'] ?? '', $hijo['anio'] ?? '', $label, 'Año', $errores);
                    $this->compararTextoSimple($bItem['marca'] ?? '', $hijo['marca'] ?? '', $label, 'Marca', $errores);
                    $this->compararTextoSimple($bItem['modelo'] ?? '', $hijo['modelo'] ?? '', $label, 'Modelo', $errores);
                    $this->compararTextoSimple($bItem['serial'] ?? $bItem['serial_placa'] ?? '', $hijo['serial_placa'] ?? '', $label, 'Serial/Placa', $errores);
                }
                break;

            case 4: // Opciones de Compra
                $hijo = $this->fetchChildRow('sim_caso_bm_opciones_compra', $bienMuebleId);
                if ($hijo) {
                    $this->compararTextoSimple($bItem['nombre_oferente'] ?? '', $hijo['nombre_oferente'] ?? '', $label, 'Nombre oferente', $errores);
                }
                break;

            case 5: // Cuentas y Efectos por Cobrar
                $hijo = $this->fetchChildRow('sim_caso_bm_cuentas_cobrar', $bienMuebleId);
                if ($hijo) {
                    $this->compararTextoSimple($bItem['rif_cedula'] ?? '', $hijo['rif_cedula'] ?? '', $label, 'RIF/Cédula', $errores);
                    $this->compararTextoSimple($bItem['apellidos_nombres'] ?? '', $hijo['apellidos_nombres'] ?? '', $label, 'Apellidos/Nombres', $errores);
                }
                break;

            case 6: // Semovientes
                $hijo = $this->fetchChildRow('sim_caso_bm_semovientes', $bienMuebleId);
                if ($hijo) {
                    $this->compararIds($bItem, 'tipo_semoviente', $hijo, 'tipo_semoviente_id', $label, 'Tipo semoviente', $errores);
                    $cantB = (int) ($bItem['cantidad'] ?? 0);
                    $cantDb = (int) ($hijo['cantidad'] ?? 0);
                    if ($cantB > 0 && $cantDb > 0 && $cantB !== $cantDb) {
                        $errores[] = "{$label}: Cantidad no coincide. Esperado: {$cantDb}, ingresado: {$cantB}.";
                    }
                }
                break;

            case 7: // Bonos
                $hijo = $this->fetchChildRow('sim_caso_bm_bonos', $bienMuebleId);
                if ($hijo) {
                    $this->compararTextoSimple($bItem['tipo_bonos'] ?? '', $hijo['tipo_bonos'] ?? '', $label, 'Tipo de bonos', $errores);
                    $this->compararTextoSimple($bItem['numero_bonos'] ?? '', $hijo['numero_bonos'] ?? '', $label, 'Número de bonos', $errores);
                    $this->compararTextoSimple($bItem['numero_serie'] ?? '', $hijo['numero_serie'] ?? '', $label, 'Número de serie', $errores);
                }
                break;

            case 8: // Acciones
                $hijo = $this->fetchChildRow('sim_caso_bm_acciones', $bienMuebleId);
                if ($hijo) {
                    $this->compararIds($bItem, 'empresa', $hijo, 'empresa_id', $label, 'Empresa', $errores);
                }
                break;

            case 9: // Prestaciones Sociales
                $hijo = $this->fetchChildRow('sim_caso_bm_prestaciones', $bienMuebleId);
                if ($hijo) {
                    $this->compararIds($bItem, 'empresa', $hijo, 'empresa_id', $label, 'Empresa', $errores);
                    $poseeB = (int) ($bItem['posee_banco'] ?? 0);
                    $poseeDb = (int) ($hijo['posee_banco'] ?? 0);
                    if ($poseeB !== $poseeDb) {
                        $errores[] = "{$label}: Posee banco no coincide.";
                    }
                    if ($poseeDb === 1) {
                        $this->compararIds($bItem, 'banco', $hijo, 'banco_id', $label, 'Banco', $errores);
                        $this->compararTextoSimple($bItem['numero_cuenta'] ?? '', $hijo['numero_cuenta'] ?? '', $label, 'Número de cuenta', $errores);
                    }
                }
                break;

            case 10: // Caja de Ahorro
                $hijo = $this->fetchChildRow('sim_caso_bm_caja_ahorro', $bienMuebleId);
                if ($hijo) {
                    $this->compararIds($bItem, 'empresa', $hijo, 'empresa_id', $label, 'Empresa', $errores);
                }
                break;

            // 11 (Plantaciones), 12 (Otros): solo campos base, no child table
        }

        return $errores;
    }

    private function fetchChildRow(string $table, int $bienMuebleId): ?array
    {
        $allowedTables = [
            'sim_caso_bm_banco', 'sim_caso_bm_seguro', 'sim_caso_bm_transporte',
            'sim_caso_bm_opciones_compra', 'sim_caso_bm_cuentas_cobrar', 'sim_caso_bm_semovientes',
            'sim_caso_bm_bonos', 'sim_caso_bm_acciones', 'sim_caso_bm_prestaciones',
            'sim_caso_bm_caja_ahorro',
        ];
        if (!in_array($table, $allowedTables)) return null;

        try {
            $stmt = $this->db->prepare("SELECT * FROM {$table} WHERE bien_mueble_id = :id LIMIT 1");
            $stmt->bindValue(':id', $bienMuebleId, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    // ════════════════════════════════════════════════════════
    //  VALIDACIÓN: PASIVOS DEUDA
    // ════════════════════════════════════════════════════════

    /**
     * Mapping: borrador key → nombre en sim_cat_tipos_pasivo_deuda (para lookup dinámico del ID).
     */
    private const DEUDA_NOMBRE_MAP = [
        'pasivos_deuda_tdc'   => 'Tarjetas de Crédito',
        'pasivos_deuda_ch'    => 'Crédito Hipotecario',
        'pasivos_deuda_pce'   => 'Préstamos Cuentas y Efectos por Pagar',
        'pasivos_deuda_otros' => 'Otros',
    ];

    private function validarPasivosDeuda(array $borrador, int $casoId): array
    {
        $errores = [];

        // Build dynamic ID map from DB catalogue
        $deudaMap = $this->buildDeudaMap();

        // Fetch all deudas from DB
        $stmt = $this->db->prepare("
            SELECT pd.*, tpd.nombre AS tipo_nombre
            FROM sim_caso_pasivos_deuda pd
            LEFT JOIN sim_cat_tipos_pasivo_deuda tpd ON pd.tipo_pasivo_deuda_id = tpd.id
            WHERE pd.caso_estudio_id = :caso_id AND pd.deleted_at IS NULL
            ORDER BY pd.tipo_pasivo_deuda_id ASC, pd.id ASC
        ");
        $stmt->bindValue(':caso_id', $casoId, PDO::PARAM_INT);
        $stmt->execute();
        $allDeudasDb = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Group by tipo
        $dbByTipo = [];
        foreach ($allDeudasDb as $d) {
            $tipoId = (int) $d['tipo_pasivo_deuda_id'];
            $dbByTipo[$tipoId][] = $d;
        }

        foreach ($deudaMap as $borradorKey => $tipoId) {
            $label = self::DEUDA_NOMBRE_MAP[$borradorKey] ?? "Tipo {$tipoId}";
            $itemsB = $borrador[$borradorKey] ?? [];
            $itemsDb = $dbByTipo[$tipoId] ?? [];

            if (count($itemsB) === 0 && count($itemsDb) === 0) continue;

            if (count($itemsB) !== count($itemsDb)) {
                $errores[] = "Pasivo Deuda ({$label}): se esperan " . count($itemsDb) . " pero se ingresaron " . count($itemsB) . ".";
            }

            // Match by valor_declarado
            $dbUsados = [];
            foreach ($itemsB as $i => $bItem) {
                $pos = $i + 1;
                $matched = false;
                $valorB = $this->parseDecimal($bItem['valor_declarado'] ?? '0');

                foreach ($itemsDb as $j => $dbItem) {
                    if (in_array($j, $dbUsados)) continue;
                    $valorDb = (float) ($dbItem['valor_declarado'] ?? 0);

                    if (abs($valorB - $valorDb) < 0.01) {
                        $matched = true;
                        $dbUsados[] = $j;
                        $this->compararCampoDecimal($bItem, $dbItem, 'porcentaje', "{$label} #{$pos}", $errores);
                        $this->compararCampoTexto($bItem, $dbItem, 'descripcion', "{$label} #{$pos}", $errores);
                        break;
                    }
                }

                if (!$matched) {
                    $errores[] = "Pasivo Deuda ({$label}) #{$pos}: no se encontró en el caso.";
                }
            }

            foreach ($itemsDb as $j => $dbItem) {
                if (!in_array($j, $dbUsados)) {
                    $errores[] = "Pasivo Deuda ({$label}): Falta — valor: {$dbItem['valor_declarado']}.";
                }
            }
        }

        return $errores;
    }

    /**
     * Builds borrador_key → tipo_pasivo_deuda_id map dynamically from DB catalogue.
     */
    private function buildDeudaMap(): array
    {
        $stmt = $this->db->query("SELECT id, nombre FROM sim_cat_tipos_pasivo_deuda ORDER BY id ASC");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Index by nombre (uppercase) → id
        $byNombre = [];
        foreach ($rows as $r) {
            $byNombre[mb_strtoupper(trim($r['nombre']))] = (int) $r['id'];
        }

        $map = [];
        foreach (self::DEUDA_NOMBRE_MAP as $borradorKey => $nombre) {
            $key = mb_strtoupper(trim($nombre));
            if (isset($byNombre[$key])) {
                $map[$borradorKey] = $byNombre[$key];
            }
        }

        return $map;
    }

    // ════════════════════════════════════════════════════════
    //  VALIDACIÓN: PASIVOS GASTOS
    // ════════════════════════════════════════════════════════

    private function validarPasivosGastos(array $borrador, int $casoId): array
    {
        $errores = [];

        $itemsB = $borrador['pasivos_gastos'] ?? [];

        $stmt = $this->db->prepare("
            SELECT pg.*, tpg.nombre AS tipo_nombre
            FROM sim_caso_pasivos_gastos pg
            LEFT JOIN sim_cat_tipos_pasivo_gasto tpg ON pg.tipo_pasivo_gasto_id = tpg.id
            WHERE pg.caso_estudio_id = :caso_id AND pg.deleted_at IS NULL
            ORDER BY pg.id ASC
        ");
        $stmt->bindValue(':caso_id', $casoId, PDO::PARAM_INT);
        $stmt->execute();
        $itemsDb = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($itemsB) === 0 && count($itemsDb) === 0) return $errores;

        if (count($itemsB) !== count($itemsDb)) {
            $errores[] = "Pasivos Gastos: se esperan " . count($itemsDb) . " pero se ingresaron " . count($itemsB) . ".";
        }

        $dbUsados = [];
        foreach ($itemsB as $i => $bItem) {
            $pos = $i + 1;
            $matched = false;
            $valorB = $this->parseDecimal($bItem['valor_declarado'] ?? '0');

            foreach ($itemsDb as $j => $dbItem) {
                if (in_array($j, $dbUsados)) continue;
                $valorDb = (float) ($dbItem['valor_declarado'] ?? 0);

                if (abs($valorB - $valorDb) < 0.01) {
                    $matched = true;
                    $dbUsados[] = $j;
                    $this->compararCampoDecimal($bItem, $dbItem, 'porcentaje', "Gasto #{$pos}", $errores);
                    $this->compararCampoTexto($bItem, $dbItem, 'descripcion', "Gasto #{$pos}", $errores);
                    break;
                }
            }

            if (!$matched) {
                $errores[] = "Pasivo Gasto #{$pos}: no se encontró en el caso.";
            }
        }

        foreach ($itemsDb as $j => $dbItem) {
            if (!in_array($j, $dbUsados)) {
                $tipo = $dbItem['tipo_nombre'] ?? '';
                $errores[] = "Pasivos Gastos: Falta — {$tipo} (valor: {$dbItem['valor_declarado']}).";
            }
        }

        return $errores;
    }

    // ════════════════════════════════════════════════════════
    //  VALIDACIÓN: EXENCIONES
    // ════════════════════════════════════════════════════════

    private function validarExenciones(array $borrador, int $casoId): array
    {
        return $this->validarExclusion($borrador, 'exenciones', 'sim_caso_exenciones', $casoId, 'Exención');
    }

    // ════════════════════════════════════════════════════════
    //  VALIDACIÓN: EXONERACIONES
    // ════════════════════════════════════════════════════════

    private function validarExoneraciones(array $borrador, int $casoId): array
    {
        return $this->validarExclusion($borrador, 'exoneraciones', 'sim_caso_exoneraciones', $casoId, 'Exoneración');
    }

    /**
     * Generic validator for exenciones/exoneraciones (same structure).
     */
    private function validarExclusion(array $borrador, string $borradorKey, string $tabla, int $casoId, string $labelSingular): array
    {
        $errores = [];

        // Whitelist de tablas permitidas
        $allowedTables = ['sim_caso_exenciones', 'sim_caso_exoneraciones'];
        if (!in_array($tabla, $allowedTables)) return $errores;

        $itemsB = $borrador[$borradorKey] ?? [];

        $stmt = $this->db->prepare("SELECT * FROM {$tabla} WHERE caso_estudio_id = :caso_id AND deleted_at IS NULL ORDER BY id ASC");
        $stmt->bindValue(':caso_id', $casoId, PDO::PARAM_INT);
        $stmt->execute();
        $itemsDb = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($itemsB) === 0 && count($itemsDb) === 0) return $errores;

        if (count($itemsB) !== count($itemsDb)) {
            $errores[] = "{$labelSingular}es: se esperan " . count($itemsDb) . " pero se ingresaron " . count($itemsB) . ".";
        }

        $dbUsados = [];
        foreach ($itemsB as $i => $bItem) {
            $pos = $i + 1;
            $matched = false;
            $valorB = $this->parseDecimal($bItem['valor_declarado'] ?? '0');

            foreach ($itemsDb as $j => $dbItem) {
                if (in_array($j, $dbUsados)) continue;
                $valorDb = (float) ($dbItem['valor_declarado'] ?? 0);

                if (abs($valorB - $valorDb) < 0.01) {
                    $matched = true;
                    $dbUsados[] = $j;
                    $this->compararCampoTexto($bItem, $dbItem, 'tipo', "{$labelSingular} #{$pos}", $errores);
                    $this->compararCampoTexto($bItem, $dbItem, 'descripcion', "{$labelSingular} #{$pos}", $errores);
                    break;
                }
            }

            if (!$matched) {
                $errores[] = "{$labelSingular} #{$pos}: no se encontró en el caso.";
            }
        }

        foreach ($itemsDb as $j => $dbItem) {
            if (!in_array($j, $dbUsados)) {
                $errores[] = "{$labelSingular}es: Falta — {$dbItem['tipo']} (valor: {$dbItem['valor_declarado']}).";
            }
        }

        return $errores;
    }

    // ════════════════════════════════════════════════════════
    //  VALIDACIÓN: DESGRAVÁMENES (calculados)
    // ════════════════════════════════════════════════════════

    private function validarDesgravamenes(array $borrador, int $casoId): array
    {
        $errores = [];

        // Calculate desgravámenes from borrador (same logic as BorradorService)
        $totalBorrador = 0.0;

        // 1. Inmuebles con vivienda_principal = 'true'
        foreach (($borrador['bienes_inmuebles'] ?? []) as $inm) {
            if (($inm['vivienda_principal'] ?? 'false') === 'true') {
                $totalBorrador += $this->parseDecimal($inm['valor_declarado'] ?? '0');
            }
        }

        // 2. Seguros tipo 08 (Seguro de Vida) y 09 (Montepío)
        foreach (($borrador['bienes_muebles_seguro'] ?? []) as $seg) {
            $tipo = $seg['tipo_bien'] ?? '';
            if ($tipo === '08' || $tipo === '09') {
                $totalBorrador += $this->parseDecimal($seg['valor_declarado'] ?? '0');
            }
        }

        // Calculate desgravámenes from DB
        $totalDb = 0.0;

        // 1. Inmuebles con es_vivienda_principal = 1
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(valor_declarado), 0) AS total
            FROM sim_caso_bienes_inmuebles
            WHERE caso_estudio_id = :caso_id
              AND es_vivienda_principal = 1
              AND deleted_at IS NULL
        ");
        $stmt->bindValue(':caso_id', $casoId, PDO::PARAM_INT);
        $stmt->execute();
        $totalDb += (float) $stmt->fetchColumn();

        // 2. Seguros vida/montepío: tipo_bien_mueble_id for these
        // Use LIKE for accent-safe matching (handles Montepío/Montepio/montepío)
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(bm.valor_declarado), 0) AS total
            FROM sim_caso_bienes_muebles bm
            INNER JOIN sim_cat_tipos_bien_mueble tbm ON bm.tipo_bien_mueble_id = tbm.id
            WHERE bm.caso_estudio_id = :caso_id
              AND bm.categoria_bien_mueble_id = 2
              AND (tbm.nombre LIKE '%Seguro de Vida%' OR tbm.nombre LIKE '%montepi%')
              AND bm.deleted_at IS NULL
        ");
        $stmt->bindValue(':caso_id', $casoId, PDO::PARAM_INT);
        $stmt->execute();
        $totalDb += (float) $stmt->fetchColumn();

        // Compare
        if (abs($totalBorrador - $totalDb) > 0.01) {
            $fmtB = number_format($totalBorrador, 2, ',', '.');
            $fmtDb = number_format($totalDb, 2, ',', '.');
            $errores[] = "Total desgravámenes no coincide. Esperado: {$fmtDb}, calculado del borrador: {$fmtB}.";
        }

        return $errores;
    }

    // ════════════════════════════════════════════════════════
    //  VALIDACIÓN: TOTALES Y CÁLCULOS
    // ════════════════════════════════════════════════════════

    private function validarTotales(array $borrador, int $casoId, array $intento): array
    {
        $errores = [];

        // Use BorradorService for borrador totals
        $bs = new BorradorService($intento);

        $totalInmueblesB = $bs->getTotalBienesInmuebles();
        $totalMueblesB = $bs->getTotalBienesMuebles();
        $patrimonioBrutoB = $totalInmueblesB + $totalMueblesB;

        // DB totals
        $totalInmueblesDb = $this->sumFromTable('sim_caso_bienes_inmuebles', $casoId);
        $totalMueblesDb = $this->sumFromTable('sim_caso_bienes_muebles', $casoId);
        $patrimonioBrutoDb = $totalInmueblesDb + $totalMueblesDb;

        if (abs($totalInmueblesB - $totalInmueblesDb) > 0.01) {
            $errores[] = "Total bienes inmuebles no coincide. Esperado: " . $this->fmtBs($totalInmueblesDb) . ", borrador: " . $this->fmtBs($totalInmueblesB) . ".";
        }

        if (abs($totalMueblesB - $totalMueblesDb) > 0.01) {
            $errores[] = "Total bienes muebles no coincide. Esperado: " . $this->fmtBs($totalMueblesDb) . ", borrador: " . $this->fmtBs($totalMueblesB) . ".";
        }

        // Exclusiones
        $desgravamenesB = $bs->getTotalDesgravamenes();
        $exencionesB = $bs->getTotalExenciones();
        $exoneracionesB = $bs->getTotalExoneraciones();

        $exencionesDb = $this->sumFromTable('sim_caso_exenciones', $casoId);
        $exoneracionesDb = $this->sumFromTable('sim_caso_exoneraciones', $casoId);

        if (abs($exencionesB - $exencionesDb) > 0.01) {
            $errores[] = "Total exenciones no coincide. Esperado: " . $this->fmtBs($exencionesDb) . ", borrador: " . $this->fmtBs($exencionesB) . ".";
        }

        if (abs($exoneracionesB - $exoneracionesDb) > 0.01) {
            $errores[] = "Total exoneraciones no coincide. Esperado: " . $this->fmtBs($exoneracionesDb) . ", borrador: " . $this->fmtBs($exoneracionesB) . ".";
        }

        // Pasivos
        $totalPasivosB = $bs->getTotalPasivos();
        $totalPasivosDDb = $this->sumFromTable('sim_caso_pasivos_deuda', $casoId);
        $totalPasivosGDb = $this->sumFromTable('sim_caso_pasivos_gastos', $casoId);
        $totalPasivosDb = $totalPasivosDDb + $totalPasivosGDb;

        if (abs($totalPasivosB - $totalPasivosDb) > 0.01) {
            $errores[] = "Total pasivos no coincide. Esperado: " . $this->fmtBs($totalPasivosDb) . ", borrador: " . $this->fmtBs($totalPasivosB) . ".";
        }

        return $errores;
    }

    private function sumFromTable(string $table, int $casoId): float
    {
        $allowedTables = [
            'sim_caso_bienes_inmuebles', 'sim_caso_bienes_muebles',
            'sim_caso_pasivos_deuda', 'sim_caso_pasivos_gastos',
            'sim_caso_exenciones', 'sim_caso_exoneraciones',
        ];
        if (!in_array($table, $allowedTables)) return 0.0;

        $stmt = $this->db->prepare("SELECT COALESCE(SUM(valor_declarado), 0) FROM {$table} WHERE caso_estudio_id = :id AND deleted_at IS NULL");
        $stmt->bindValue(':id', $casoId, PDO::PARAM_INT);
        $stmt->execute();
        return (float) $stmt->fetchColumn();
    }

    // ════════════════════════════════════════════════════════
    //  UTILIDADES
    // ════════════════════════════════════════════════════════

    /**
     * Parse decimal: "3.700.000,00" → 3700000.00 or "555.5" → 555.5
     */
    private function parseDecimal(string $value): float
    {
        $value = trim($value);
        if ($value === '' || $value === '0') return 0.0;

        if (str_contains($value, ',')) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        }

        return (float) $value;
    }

    private function fmtBs(float $value): string
    {
        return number_format($value, 2, ',', '.');
    }

    /**
     * Compare a decimal field between borrador and DB.
     */
    private function compararCampoDecimal(array $bItem, array $dbItem, string $campo, string $label, array &$errores): void
    {
        $valorB = $this->parseDecimal((string) ($bItem[$campo] ?? '0'));
        $valorDb = (float) ($dbItem[$campo] ?? 0);

        if (abs($valorB - $valorDb) > 0.01) {
            $fmtDb = $this->fmtBs($valorDb);
            $fmtB = $this->fmtBs($valorB);
            $errores[] = "{$label}: {$campo} no coincide. Esperado: {$fmtDb}, ingresado: {$fmtB}.";
        }
    }

    /**
     * Compare a text field between borrador and DB (case-insensitive).
     */
    private function compararCampoTexto(array $bItem, array $dbItem, string $campoB, string $label, array &$errores, ?string $campoDb = null): void
    {
        $campoDb = $campoDb ?? $campoB;
        $valorB = mb_strtoupper(trim((string) ($bItem[$campoB] ?? '')));
        $valorDb = mb_strtoupper(trim((string) ($dbItem[$campoDb] ?? '')));

        if ($valorB !== '' && $valorDb !== '' && $valorB !== $valorDb) {
            $errores[] = "{$label}: {$campoB} no coincide.";
        }
    }

    /**
     * Compare two simple text values.
     */
    private function compararTextoSimple(string $valorB, string $valorDb, string $label, string $campoLabel, array &$errores): void
    {
        $valorB = mb_strtoupper(trim($valorB));
        $valorDb = mb_strtoupper(trim($valorDb));

        if ($valorB !== '' && $valorDb !== '' && $valorB !== $valorDb) {
            $errores[] = "{$label}: {$campoLabel} no coincide. Esperado: {$valorDb}, ingresado: {$valorB}.";
        }
    }

    /**
     * Compare IDs between borrador field and DB field.
     */
    private function compararIds(array $bItem, string $campoB, array $dbItem, string $campoDb, string $label, string $campoLabel, array &$errores): void
    {
        $idB = (int) ($bItem[$campoB] ?? 0);
        $idDb = (int) ($dbItem[$campoDb] ?? 0);

        if ($idB > 0 && $idDb > 0 && $idB !== $idDb) {
            $errores[] = "{$label}: {$campoLabel} no coincide. Esperado ID: {$idDb}, ingresado: {$idB}.";
        }
    }

    /**
     * Normaliza fechas DD/MM/YYYY o YYYY-MM-DD a YYYY-MM-DD.
     */
    private function normalizarFecha(string $fecha): string
    {
        $fecha = trim($fecha);
        if (!$fecha) return '';

        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $fecha, $m)) {
            return "{$m[3]}-{$m[2]}-{$m[1]}";
        }

        return substr($fecha, 0, 10);
    }
}
