<?php
declare(strict_types=1);

namespace App\Modules\Simulator\Models;

use App\Core\BitacoraModel;
use App\Core\DB;
use PDO;

/**
 * Modelo para normalizar los datos del intento del estudiante
 * desde borrador_json hacia tablas de la BD.
 * 
 * Análogo a StoreCasoModel (profesor), pero para el flujo del estudiante.
 * 
 * ANTI-CRASH: Todo ocurre dentro de una única transacción.
 * Si cualquier INSERT falla → rollBack() completo, cero datos huérfanos.
 */
class StoreIntentoModel
{
    private PDO $db;

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

    /**
     * Mapping: borrador key → tipo_pasivo_deuda_id
     */
    private const DEUDA_MAP = [
        'pasivos_deuda_tdc'   => 1,
        'pasivos_deuda_ch'    => 2,
        'pasivos_deuda_pce'   => 3,
        'pasivos_deuda_otros' => 4,
    ];

    public function __construct()
    {
        $this->db = DB::connect();
    }

    // ════════════════════════════════════════════════════════
    //  MÉTODO PRINCIPAL
    // ════════════════════════════════════════════════════════

    /**
     * Normaliza y guarda los datos del intento del estudiante
     * desde el borrador_json a las tablas correspondientes.
     * 
     * @param int   $intentoId  ID del intento
     * @param array $borrador   Datos del borrador JSON decodificado
     * @return bool true si todo se guardó correctamente
     * 
     * @throws \Throwable  Re-lanza cualquier error después del rollback
     */
    public function store(int $intentoId, array $borrador): bool
    {
        $this->db->beginTransaction();

        try {
            // 0. Limpiar datos previos (idempotente — seguridad contra double-submit)
            $this->clearExistingData($intentoId);

            // 1. Datos básicos del causante (persona + fiscal + acta)
            $this->insertDatosBasicos($intentoId, $borrador);

            // 2. Direcciones
            $this->insertDirecciones($intentoId, $borrador);

            // 3. Tipos de herencia
            $this->insertTiposHerencia($intentoId, $borrador);

            // 4. Relaciones (herederos + representante + premuertos)
            $indexMap = $this->insertRelaciones($intentoId, $borrador);

            // 5. Bienes inmuebles (+ tipo_rel + litigiosos)
            $this->insertBienesInmuebles($intentoId, $borrador);

            // 6. Bienes muebles (12 categorías + sub-tablas + litigiosos)
            $this->insertBienesMuebles($intentoId, $borrador);

            // 7. Pasivos deuda
            $this->insertPasivosDeuda($intentoId, $borrador);

            // 8. Pasivos gastos
            $this->insertPasivosGastos($intentoId, $borrador);

            // 9. Exenciones
            $this->insertExenciones($intentoId, $borrador);

            // 10. Exoneraciones
            $this->insertExoneraciones($intentoId, $borrador);

            // 11. Prórrogas
            $this->insertProrrogas($intentoId, $borrador);

            // 12. Cálculo manual
            $this->insertCalculoManual($intentoId, $borrador, $indexMap);

            // 13. Registrar estado Pendiente_Revision
            $this->insertEstado($intentoId, 'Pendiente_Revision');

            // 14. Actualizar estado del intento en tabla principal
            //     sim_intentos.estado enum = En_Progreso|Enviado|Aprobado|Rechazado|Cancelado
            //     sim_intento_estados.estado enum = En_Proceso|Pendiente_Revision|Aprobado|Cancelado
            $this->db->prepare(
                "UPDATE sim_intentos SET estado = 'Enviado', submitted_at = NOW(), updated_at = NOW() WHERE id = :id"
            )->execute(['id' => $intentoId]);

            // 15. Registrar en bitácora (dentro de la transacción — atómico)
            $this->registrarBitacora($intentoId);

            $this->db->commit();

            // Habilitar acceso a los PDFs de la declaración
            $_SESSION['sim_declarado'] = true;

            return true;

        } catch (\Throwable $e) {
            $this->db->rollBack();
            error_log('[StoreIntentoModel] Error en store(): ' . $e->getMessage());
            throw $e;
        }
    }

    // ════════════════════════════════════════════════════════
    //  CLEAR (idempotente)
    // ════════════════════════════════════════════════════════

    private function clearExistingData(int $intentoId): void
    {
        // Tablas sin FK hijas primero
        $simpleTables = [
            'sim_intento_calculo_manual',
            'sim_intento_bienes_litigiosos',
            'sim_intento_exenciones',
            'sim_intento_exoneraciones',
            'sim_intento_pasivos_deuda',
            'sim_intento_pasivos_gastos',
            'sim_intento_prorrogas',
            'sim_intento_tipoherencias',
            'sim_intento_direcciones',
            'sim_intento_datos_basicos',
            'sim_intento_estados',
        ];

        foreach ($simpleTables as $table) {
            $this->db->prepare("DELETE FROM {$table} WHERE intento_id = :id")->execute(['id' => $intentoId]);
        }

        // sim_intento_relaciones tiene FK auto-referencial (premuerto_padre_id → id)
        // primero anular la FK, luego borrar
        $this->db->prepare("UPDATE sim_intento_relaciones SET premuerto_padre_id = NULL WHERE intento_id = :id")->execute(['id' => $intentoId]);
        $this->db->prepare("DELETE FROM sim_intento_relaciones WHERE intento_id = :id")->execute(['id' => $intentoId]);

        // BM detail tables (FK → sim_intento_bienes_muebles.id)
        $bmIds = $this->db->prepare("SELECT id FROM sim_intento_bienes_muebles WHERE intento_id = :id");
        $bmIds->execute(['id' => $intentoId]);
        $ids = $bmIds->fetchAll(PDO::FETCH_COLUMN);
        if (!empty($ids)) {
            $in = implode(',', array_map('intval', $ids));
            $detailTables = [
                'sim_intento_bm_banco', 'sim_intento_bm_seguro', 'sim_intento_bm_transporte',
                'sim_intento_bm_opciones_compra', 'sim_intento_bm_cuentas_cobrar',
                'sim_intento_bm_semovientes', 'sim_intento_bm_bonos', 'sim_intento_bm_acciones',
                'sim_intento_bm_prestaciones', 'sim_intento_bm_caja_ahorro',
            ];
            foreach ($detailTables as $dt) {
                $this->db->exec("DELETE FROM {$dt} WHERE bien_mueble_id IN ({$in})");
            }
        }
        $this->db->prepare("DELETE FROM sim_intento_bienes_muebles WHERE intento_id = :id")->execute(['id' => $intentoId]);

        // BI tipo rel (FK → sim_intento_bienes_inmuebles.id)
        $biIds = $this->db->prepare("SELECT id FROM sim_intento_bienes_inmuebles WHERE intento_id = :id");
        $biIds->execute(['id' => $intentoId]);
        $ids = $biIds->fetchAll(PDO::FETCH_COLUMN);
        if (!empty($ids)) {
            $in = implode(',', array_map('intval', $ids));
            $this->db->exec("DELETE FROM sim_intento_bien_inmueble_tipo_rel WHERE bien_inmueble_id IN ({$in})");
        }
        $this->db->prepare("DELETE FROM sim_intento_bienes_inmuebles WHERE intento_id = :id")->execute(['id' => $intentoId]);
    }

    // ════════════════════════════════════════════════════════
    //  1. DATOS BÁSICOS
    // ════════════════════════════════════════════════════════

    private function insertDatosBasicos(int $intentoId, array $borrador): void
    {
        $db = $borrador['datos_basicos'] ?? $borrador['inscripcion'] ?? [];
        if (empty($db)) return;

        $sql = "INSERT INTO sim_intento_datos_basicos 
                (intento_id, tipo_cedula, cedula, rif_personal, pasaporte,
                 apellidos, nombres, fecha_nacimiento, fecha_fallecimiento,
                 sexo, estado_civil, domiciliado_pais, nacionalidad,
                 fecha_cierre_fiscal, email_sucesion,
                 numero_acta, year_acta, parroquia_acta)
                VALUES 
                (:intento_id, :tipo_cedula, :cedula, :rif_personal, :pasaporte,
                 :apellidos, :nombres, :fecha_nacimiento, :fecha_fallecimiento,
                 :sexo, :estado_civil, :domiciliado_pais, :nacionalidad,
                 :fecha_cierre_fiscal, :email_sucesion,
                 :numero_acta, :year_acta, :parroquia_acta)";

        $tipoCedula = $db['tipo_cedula'] ?? 'V';
        if (!in_array($tipoCedula, ['V', 'E', 'No_Aplica'])) $tipoCedula = 'V';

        $sexo = strtoupper(substr($db['sexo'] ?? 'M', 0, 1));
        if (!in_array($sexo, ['M', 'F'])) $sexo = 'M';

        $estadoCivil = $db['estado_civil'] ?? 'Soltero';
        $validEstados = ['Soltero', 'Casado', 'Viudo', 'Divorciado', 'Concubinato', 'No_aplica'];
        if (!in_array($estadoCivil, $validEstados)) $estadoCivil = 'Soltero';

        $apellidos = $db['apellidos'] ?? trim(($db['primer_apellido'] ?? '') . ' ' . ($db['segundo_apellido'] ?? ''));
        $nombres   = $db['nombres']   ?? trim(($db['primer_nombre'] ?? '') . ' ' . ($db['segundo_nombre'] ?? ''));

        $this->db->prepare($sql)->execute([
            'intento_id'          => $intentoId,
            'tipo_cedula'         => $tipoCedula,
            'cedula'              => ($db['cedula'] ?? null) ?: null,
            'rif_personal'        => ($db['rif_personal'] ?? $db['rif'] ?? null) ?: null,
            'pasaporte'           => ($db['pasaporte'] ?? null) ?: null,
            'apellidos'           => $apellidos ?: '',
            'nombres'             => $nombres ?: '',
            'fecha_nacimiento'    => ($db['fecha_nacimiento'] ?? null) ?: null,
            'fecha_fallecimiento' => $db['fecha_fallecimiento'] ?? date('Y-m-d'),
            'sexo'                => $sexo,
            'estado_civil'        => $estadoCivil,
            'domiciliado_pais'    => (int) ($db['domiciliado_pais'] ?? 1),
            'nacionalidad'        => (int) ($db['nacionalidad'] ?? 1),
            'fecha_cierre_fiscal' => $db['fecha_cierre_fiscal'] ?? date('Y-m-d'),
            'email_sucesion'      => $db['email_sucesion'] ?? $db['correo'] ?? '',
            'numero_acta'         => ($db['numero_acta'] ?? null) ?: null,
            'year_acta'           => !empty($db['year_acta']) ? (int) $db['year_acta'] : null,
            'parroquia_acta'      => ($db['parroquia_acta'] ?? $db['parroquia_registro'] ?? null) ?: null,
        ]);
    }

    // ════════════════════════════════════════════════════════
    //  2. DIRECCIONES
    // ════════════════════════════════════════════════════════

    private function insertDirecciones(int $intentoId, array $borrador): void
    {
        // Soporta ambos formatos: direcciones.items[] y direcciones[]
        $dirs = $borrador['direcciones'] ?? [];
        if (isset($dirs['items'])) {
            $dirs = $dirs['items'];
        }
        if (!is_array($dirs) || empty($dirs)) return;

        $sql = "INSERT INTO sim_intento_direcciones 
                (intento_id, tipo_direccion, tipo_vialidad, nombre_vialidad, tipo_inmueble,
                 nro_inmueble, tipo_nivel, nro_nivel, tipo_sector, nombre_sector,
                 estado_id, municipio_id, parroquia_id, ciudad_id, codigo_postal_id,
                 telefono_fijo, telefono_celular, fax, punto_referencia)
                VALUES 
                (:intento_id, :tipo_direccion, :tipo_vialidad, :nombre_vialidad, :tipo_inmueble,
                 :nro_inmueble, :tipo_nivel, :nro_nivel, :tipo_sector, :nombre_sector,
                 :estado_id, :municipio_id, :parroquia_id, :ciudad_id, :codigo_postal_id,
                 :telefono_fijo, :telefono_celular, :fax, :punto_referencia)";
        $stmt = $this->db->prepare($sql);

        foreach ($dirs as $d) {
            if (empty($d['estado']) && empty($d['estado_id'])) continue;

            $tipoDireccion = $d['tipo_direccion'] ?? $d['tipoDireccion'] ?? 'Domicilio_Fiscal';
            $tipoDireccion = str_replace(' ', '_', $tipoDireccion);

            $stmt->execute([
                'intento_id'       => $intentoId,
                'tipo_direccion'   => $tipoDireccion,
                'tipo_vialidad'    => ($d['tipo_vialidad'] ?? $d['tipoVialidad'] ?? null) ?: null,
                'nombre_vialidad'  => ($d['nombre_vialidad'] ?? $d['vialidad'] ?? null) ?: null,
                'tipo_inmueble'    => ($d['tipo_inmueble'] ?? $d['tipoEdificacion'] ?? null) ?: null,
                'nro_inmueble'     => ($d['nro_inmueble'] ?? $d['edificacion'] ?? null) ?: null,
                'tipo_nivel'       => ($d['tipo_nivel'] ?? $d['tipoLocal'] ?? null) ?: null,
                'nro_nivel'        => ($d['nro_nivel'] ?? $d['local'] ?? null) ?: null,
                'tipo_sector'      => ($d['tipo_sector'] ?? $d['tipoSector'] ?? null) ?: null,
                'nombre_sector'    => ($d['nombre_sector'] ?? $d['sector'] ?? null) ?: null,
                'estado_id'        => (int) ($d['estado_id'] ?? $d['estado'] ?? 0),
                'municipio_id'     => (int) ($d['municipio_id'] ?? $d['municipio'] ?? 0),
                'parroquia_id'     => (int) ($d['parroquia_id'] ?? $d['parroquia'] ?? 0),
                'ciudad_id'        => !empty($d['ciudad_id'] ?? $d['ciudad'] ?? null) ? (int) ($d['ciudad_id'] ?? $d['ciudad']) : null,
                'codigo_postal_id' => !empty($d['codigo_postal_id'] ?? null) ? (int) $d['codigo_postal_id'] : null,
                'telefono_fijo'    => ($d['telefono_fijo'] ?? $d['telefonoFijo'] ?? null) ?: null,
                'telefono_celular' => ($d['telefono_celular'] ?? $d['telefonoCelular'] ?? null) ?: null,
                'fax'              => ($d['fax'] ?? null) ?: null,
                'punto_referencia' => ($d['punto_referencia'] ?? $d['referencia'] ?? null) ?: null,
            ]);
        }
    }

    // ════════════════════════════════════════════════════════
    //  3. TIPOS DE HERENCIA
    // ════════════════════════════════════════════════════════

    private function insertTiposHerencia(int $intentoId, array $borrador): void
    {
        $tipos = $borrador['tipo_herencia'] ?? [];
        if (empty($tipos)) return;

        $sql = "INSERT INTO sim_intento_tipoherencias 
                (intento_id, tipo_herencia_id, subtipo_testamento, fecha_testamento, fecha_conclusion_inventario)
                VALUES (:intento_id, :tipo_id, :subtipo, :fecha_test, :fecha_inv)";
        $stmt = $this->db->prepare($sql);

        foreach ($tipos as $t) {
            $tipoId = (int) ($t['tipo_herencia_id'] ?? 0);
            if ($tipoId <= 0) continue;

            $stmt->execute([
                'intento_id'  => $intentoId,
                'tipo_id'     => $tipoId,
                'subtipo'     => ($t['subtipo_testamento'] ?? null) ?: null,
                'fecha_test'  => ($t['fecha_testamento'] ?? null) ?: null,
                'fecha_inv'   => ($t['fecha_conclusion_inventario'] ?? null) ?: null,
            ]);
        }
    }

    // ════════════════════════════════════════════════════════
    //  4. RELACIONES (Herederos + Representante + Premuertos)
    // ════════════════════════════════════════════════════════

    /**
     * @return array<int, int>  Mapa: índice del heredero en borrador → sim_intento_relaciones.id
     */
    private function insertRelaciones(int $intentoId, array $borrador): array
    {
        $indexMap = []; // índice → relacion_id (para calculo_manual)

        $sql = "INSERT INTO sim_intento_relaciones 
                (intento_id, rol, tipo_cedula, cedula, pasaporte, nombres, apellidos,
                 fecha_nacimiento, parentesco_id, es_premuerto, fecha_fallecimiento,
                 premuerto_padre_id, orden)
                VALUES 
                (:intento_id, :rol, :tipo_cedula, :cedula, :pasaporte, :nombres, :apellidos,
                 :fecha_nacimiento, :parentesco_id, :es_premuerto, :fecha_fallecimiento,
                 :premuerto_padre_id, :orden)";
        $stmt = $this->db->prepare($sql);

        $orden = 0;

        // 4a. Representante legal
        $rep = $borrador['representante'] ?? null;
        if ($rep && !empty($rep['nombres'])) {
            $tipoCed = $rep['tipo_cedula'] ?? 'V';
            if (!in_array($tipoCed, ['V', 'E', 'No_Indica'])) $tipoCed = 'V';

            $stmt->execute([
                'intento_id'         => $intentoId,
                'rol'                => 'Representante',
                'tipo_cedula'        => $tipoCed,
                'cedula'             => ($rep['cedula'] ?? null) ?: null,
                'pasaporte'          => ($rep['pasaporte'] ?? null) ?: null,
                'nombres'            => $rep['nombres'] ?? '',
                'apellidos'          => $rep['apellidos'] ?? '',
                'fecha_nacimiento'   => ($rep['fecha_nacimiento'] ?? null) ?: null,
                'parentesco_id'      => null,
                'es_premuerto'       => 0,
                'fecha_fallecimiento'=> null,
                'premuerto_padre_id' => null,
                'orden'              => $orden++,
            ]);
        }

        // 4b. Herederos (nuevo formato: herederos.items[])
        $herederos = $borrador['herederos']['items'] ?? [];

        // Legacy: relaciones[] (filtrar solo herederos/legatarios)
        if (empty($herederos) && !empty($borrador['relaciones'])) {
            foreach ($borrador['relaciones'] as $rel) {
                $parentText = strtoupper($rel['parentescoText'] ?? '');
                if ($parentText === 'REPRESENTANTE DE LA SUCESION') continue;
                $herederos[] = $rel;
            }
        }

        foreach ($herederos as $i => $h) {
            $tipoCed = $h['tipo_cedula'] ?? $h['tipodocumento'] ?? 'V';
            if (!in_array($tipoCed, ['V', 'E', 'No_Indica'])) $tipoCed = 'V';

            $esPremuerto = in_array(strtolower($h['premuerto'] ?? ''), ['true', 'si', '1'], true);

            $nombres   = $h['nombres'] ?? $h['nombre'] ?? '';
            $apellidos = $h['apellidos'] ?? $h['apellido'] ?? '';

            $rol = ucfirst(strtolower($h['caracter'] ?? 'Heredero'));
            if (!in_array($rol, ['Heredero', 'Legatario'])) $rol = 'Heredero';

            $stmt->execute([
                'intento_id'         => $intentoId,
                'rol'                => $rol,
                'tipo_cedula'        => $tipoCed,
                'cedula'             => ($h['cedula'] ?? null) ?: null,
                'pasaporte'          => ($h['pasaporte'] ?? null) ?: null,
                'nombres'            => $nombres,
                'apellidos'          => $apellidos,
                'fecha_nacimiento'   => ($h['fecha_nacimiento'] ?? null) ?: null,
                'parentesco_id'      => !empty($h['parentesco_id']) ? (int) $h['parentesco_id'] : null,
                'es_premuerto'       => $esPremuerto ? 1 : 0,
                'fecha_fallecimiento'=> $esPremuerto ? (($h['fecha_fallecimiento'] ?? null) ?: null) : null,
                'premuerto_padre_id' => null,
                'orden'              => $orden++,
            ]);

            $relacionId = (int) $this->db->lastInsertId();
            $indexMap[$i] = $relacionId;
        }

        // 4c. Herederos del premuerto
        $premuertos = $borrador['herederos_premuertos'] ?? [];
        foreach ($premuertos as $hp) {
            $tipoCed = $hp['tipo_cedula'] ?? $hp['tipodocumento'] ?? 'V';
            if (!in_array($tipoCed, ['V', 'E', 'No_Indica'])) $tipoCed = 'V';

            $nombres   = $hp['nombres'] ?? $hp['nombre'] ?? '';
            $apellidos = $hp['apellidos'] ?? $hp['apellido'] ?? '';

            // Resolver padre: el borrador guarda premuerto_padre_index como índice
            $padreIndex = $hp['premuerto_padre_index'] ?? $hp['premuerto_padre_id'] ?? null;
            $padreRelId = null;
            if ($padreIndex !== null && $padreIndex !== '' && isset($indexMap[(int) $padreIndex])) {
                $padreRelId = $indexMap[(int) $padreIndex];
            }

            $stmt->execute([
                'intento_id'         => $intentoId,
                'rol'                => 'Heredero',
                'tipo_cedula'        => $tipoCed,
                'cedula'             => ($hp['cedula'] ?? null) ?: null,
                'pasaporte'          => ($hp['pasaporte'] ?? null) ?: null,
                'nombres'            => $nombres,
                'apellidos'          => $apellidos,
                'fecha_nacimiento'   => ($hp['fecha_nacimiento'] ?? null) ?: null,
                'parentesco_id'      => !empty($hp['parentesco_id']) ? (int) $hp['parentesco_id'] : null,
                'es_premuerto'       => 0,
                'fecha_fallecimiento'=> null,
                'premuerto_padre_id' => $padreRelId,
                'orden'              => $orden++,
            ]);
        }

        return $indexMap;
    }

    // ════════════════════════════════════════════════════════
    //  5. BIENES INMUEBLES
    // ════════════════════════════════════════════════════════

    private function insertBienesInmuebles(int $intentoId, array $borrador): void
    {
        $items = $borrador['bienes_inmuebles'] ?? [];
        if (empty($items)) return;

        $sql = "INSERT INTO sim_intento_bienes_inmuebles 
                (intento_id, es_vivienda_principal, es_bien_litigioso, porcentaje,
                 descripcion, linderos, superficie_construida, superficie_no_construida,
                 area_superficie, direccion, oficina_registro, nro_registro, libro,
                 protocolo, fecha_registro, trimestre, asiento_registral, matricula,
                 folio_real_anio, valor_original, valor_declarado)
                VALUES 
                (:intento_id, :vivienda, :litigioso, :porcentaje,
                 :descripcion, :linderos, :sup_const, :sup_no_const,
                 :area, :direccion, :oficina, :nro_registro, :libro,
                 :protocolo, :fecha_reg, :trimestre, :asiento, :matricula,
                 :folio_anio, :val_original, :val_declarado)";
        $stmt = $this->db->prepare($sql);

        $tipoRelStmt = $this->db->prepare(
            "INSERT INTO sim_intento_bien_inmueble_tipo_rel (bien_inmueble_id, tipo_bien_inmueble_id) 
             VALUES (:bien_id, :tipo_id)"
        );

        foreach ($items as $b) {
            $esVivienda = ($b['vivienda_principal'] ?? 'false') === 'true';
            $esLitigioso = ($b['bien_litigioso'] ?? 'false') === 'true';

            $stmt->execute([
                'intento_id'   => $intentoId,
                'vivienda'     => $esVivienda ? 1 : 0,
                'litigioso'    => $esLitigioso ? 1 : 0,
                'porcentaje'   => self::toFloat($b['porcentaje'] ?? '100', 100),
                'descripcion'  => $b['descripcion'] ?? null,
                'linderos'     => $b['linderos'] ?? null,
                'sup_const'    => self::toFloat($b['superficie_construida'] ?? '0'),
                'sup_no_const' => self::toFloat($b['superficie_no_construida'] ?? '0'),
                'area'         => self::toFloat($b['area_superficie'] ?? '0'),
                'direccion'    => $b['direccion'] ?? null,
                'oficina'      => $b['oficina_registro'] ?? null,
                'nro_registro' => $b['nro_registro'] ?? null,
                'libro'        => $b['libro'] ?? null,
                'protocolo'    => $b['protocolo'] ?? null,
                'fecha_reg'    => ($b['fecha_registro'] ?? null) ?: null,
                'trimestre'    => $b['trimestre'] ?? null,
                'asiento'      => $b['asiento_registral'] ?? null,
                'matricula'    => $b['matricula'] ?? null,
                'folio_anio'   => $b['folio_real_anio'] ?? null,
                'val_original' => self::toFloat($b['valor_original'] ?? '0'),
                'val_declarado'=> self::toFloat($b['valor_declarado'] ?? '0'),
            ]);

            $bienId = (int) $this->db->lastInsertId();

            // Tipos de bien inmueble (M:N)
            $tipos = $b['tipo_bien_inmueble_id'] ?? [];
            if (!is_array($tipos)) $tipos = [$tipos];
            foreach ($tipos as $tipoId) {
                if (!empty($tipoId)) {
                    $tipoRelStmt->execute(['bien_id' => $bienId, 'tipo_id' => (int) $tipoId]);
                }
            }

            // Litigioso
            if ($esLitigioso) {
                $this->insertBienLitigioso($intentoId, $bienId, 'Inmueble', $b);
            }
        }
    }

    // ════════════════════════════════════════════════════════
    //  6. BIENES MUEBLES (12 categorías)
    // ════════════════════════════════════════════════════════

    private function insertBienesMuebles(int $intentoId, array $borrador): void
    {
        $sqlBase = "INSERT INTO sim_intento_bienes_muebles 
                    (intento_id, categoria_bien_mueble_id, tipo_bien_mueble_id,
                     es_bien_litigioso, porcentaje, descripcion, valor_declarado)
                    VALUES 
                    (:intento_id, :cat_id, :tipo_id, :litigioso, :porcentaje, :descripcion, :valor)";
        $stmtBase = $this->db->prepare($sqlBase);

        foreach (self::MUEBLE_MAP as $borradorKey => $catId) {
            $items = $borrador[$borradorKey] ?? [];
            if (empty($items)) continue;

            foreach ($items as $b) {
                $esLitigioso = ($b['bien_litigioso'] ?? 'false') === 'true';

                $stmtBase->execute([
                    'intento_id'  => $intentoId,
                    'cat_id'      => $catId,
                    'tipo_id'     => !empty($b['tipo_bien'] ?? $b['tipo_bien_mueble_id'] ?? null)
                                     ? (int) ($b['tipo_bien'] ?? $b['tipo_bien_mueble_id']) : null,
                    'litigioso'   => $esLitigioso ? 1 : 0,
                    'porcentaje'  => self::toFloat($b['porcentaje'] ?? '100', 100),
                    'descripcion' => $b['descripcion'] ?? null,
                    'valor'       => self::toFloat($b['valor_declarado'] ?? '0'),
                ]);

                $bienMuebleId = (int) $this->db->lastInsertId();

                // Sub-tabla de detalle por categoría
                $this->insertDetalleMueble($catId, $b, $bienMuebleId);

                // Litigioso
                if ($esLitigioso) {
                    $this->insertBienLitigioso($intentoId, $bienMuebleId, 'Mueble', $b);
                }
            }
        }
    }

    private function insertDetalleMueble(int $catId, array $b, int $bienMuebleId): void
    {
        switch ($catId) {
            case 1: // Banco
                $this->insertSimple('sim_intento_bm_banco', $bienMuebleId, [
                    'banco_id'      => !empty($b['banco_id'] ?? $b['banco'] ?? null) ? (int) ($b['banco_id'] ?? $b['banco']) : null,
                    'numero_cuenta' => $b['numero_cuenta'] ?? null,
                ]);
                break;
            case 2: // Seguros
                $empresaId = $this->resolveEmpresa($b['rif_empresa'] ?? null, $b['razon_social'] ?? null);
                $this->insertSimple('sim_intento_bm_seguro', $bienMuebleId, [
                    'empresa_id'   => $empresaId,
                    'numero_prima' => $b['numero_prima'] ?? null,
                ]);
                break;
            case 3: // Transporte
                $this->insertSimple('sim_intento_bm_transporte', $bienMuebleId, [
                    'anio'         => $b['anio'] ?? null,
                    'marca'        => $b['marca'] ?? null,
                    'modelo'       => $b['modelo'] ?? null,
                    'serial_placa' => $b['serial_placa'] ?? $b['serial'] ?? null,
                ]);
                break;
            case 4: // Opciones de compra
                $this->insertSimple('sim_intento_bm_opciones_compra', $bienMuebleId, [
                    'nombre_oferente' => $b['nombre_oferente'] ?? null,
                ]);
                break;
            case 5: // Cuentas por cobrar
                $this->insertSimple('sim_intento_bm_cuentas_cobrar', $bienMuebleId, [
                    'rif_cedula'        => $b['rif_cedula'] ?? null,
                    'apellidos_nombres' => $b['apellidos_nombres'] ?? null,
                ]);
                break;
            case 6: // Semovientes
                $this->insertSimple('sim_intento_bm_semovientes', $bienMuebleId, [
                    'tipo_semoviente_id' => !empty($b['tipo_semoviente_id'] ?? $b['tipo_semoviente'] ?? null) ? (int) ($b['tipo_semoviente_id'] ?? $b['tipo_semoviente']) : null,
                    'cantidad'           => (int) ($b['cantidad'] ?? 0),
                ]);
                break;
            case 7: // Bonos
                $this->insertSimple('sim_intento_bm_bonos', $bienMuebleId, [
                    'tipo_bonos'   => $b['tipo_bonos'] ?? null,
                    'numero_bonos' => $b['numero_bonos'] ?? null,
                    'numero_serie' => $b['numero_serie'] ?? null,
                ]);
                break;
            case 8: // Acciones
                $empresaId = $this->resolveEmpresa($b['rif_empresa'] ?? null, $b['razon_social'] ?? null);
                $this->insertSimple('sim_intento_bm_acciones', $bienMuebleId, [
                    'empresa_id' => $empresaId,
                ]);
                break;
            case 9: // Prestaciones Sociales
                $empresaId = $this->resolveEmpresa($b['rif_empresa'] ?? null, $b['razon_social'] ?? null);
                $poseeBanco = in_array($b['posee_banco'] ?? '', ['SI', '1', 'true'], true);
                $this->insertSimple('sim_intento_bm_prestaciones', $bienMuebleId, [
                    'posee_banco'   => $poseeBanco ? 1 : 0,
                    'banco_id'      => $poseeBanco && !empty($b['banco_id'] ?? $b['banco'] ?? null) ? (int) ($b['banco_id'] ?? $b['banco']) : null,
                    'numero_cuenta' => $poseeBanco ? ($b['numero_cuenta'] ?? null) : null,
                    'empresa_id'    => $empresaId,
                ]);
                break;
            case 10: // Caja de ahorro
                $empresaId = $this->resolveEmpresa($b['rif_empresa'] ?? null, $b['razon_social'] ?? null);
                $this->insertSimple('sim_intento_bm_caja_ahorro', $bienMuebleId, [
                    'empresa_id' => $empresaId,
                ]);
                break;
            // case 11 (Plantaciones), case 12 (Otros): solo campos base, sin sub-tabla
        }
    }

    // ════════════════════════════════════════════════════════
    //  7. PASIVOS DEUDA
    // ════════════════════════════════════════════════════════

    private function insertPasivosDeuda(int $intentoId, array $borrador): void
    {
        $sql = "INSERT INTO sim_intento_pasivos_deuda 
                (intento_id, tipo_pasivo_deuda_id, banco_id, numero_tdc,
                 porcentaje, descripcion, valor_declarado)
                VALUES 
                (:intento_id, :tipo_id, :banco_id, :tdc, :pct, :desc, :valor)";
        $stmt = $this->db->prepare($sql);

        foreach (self::DEUDA_MAP as $borradorKey => $tipoId) {
            $items = $borrador[$borradorKey] ?? [];
            foreach ($items as $pd) {
                $stmt->execute([
                    'intento_id' => $intentoId,
                    'tipo_id'    => $tipoId,
                    'banco_id'   => !empty($pd['banco_id'] ?? $pd['banco'] ?? null) ? (int) ($pd['banco_id'] ?? $pd['banco']) : null,
                    'tdc'        => ($pd['numero_tdc'] ?? null) ?: null,
                    'pct'        => self::toFloat($pd['porcentaje'] ?? '100', 100),
                    'desc'       => $pd['descripcion'] ?? null,
                    'valor'      => self::toFloat($pd['valor_declarado'] ?? '0'),
                ]);
            }
        }
    }

    // ════════════════════════════════════════════════════════
    //  8. PASIVOS GASTOS
    // ════════════════════════════════════════════════════════

    private function insertPasivosGastos(int $intentoId, array $borrador): void
    {
        $items = $borrador['pasivos_gastos'] ?? [];
        if (empty($items)) return;

        $sql = "INSERT INTO sim_intento_pasivos_gastos 
                (intento_id, tipo_pasivo_gasto_id, porcentaje, descripcion, valor_declarado)
                VALUES (:intento_id, :tipo_id, :pct, :desc, :valor)";
        $stmt = $this->db->prepare($sql);

        foreach ($items as $pg) {
            $stmt->execute([
                'intento_id' => $intentoId,
                'tipo_id'    => (int) ($pg['tipo_pasivo_gasto_id'] ?? $pg['tipo_gasto'] ?? 0),
                'pct'        => self::toFloat($pg['porcentaje'] ?? '100', 100),
                'desc'       => $pg['descripcion'] ?? null,
                'valor'      => self::toFloat($pg['valor_declarado'] ?? '0'),
            ]);
        }
    }

    // ════════════════════════════════════════════════════════
    //  9. EXENCIONES
    // ════════════════════════════════════════════════════════

    private function insertExenciones(int $intentoId, array $borrador): void
    {
        $items = $borrador['exenciones'] ?? [];
        if (empty($items)) return;

        $sql = "INSERT INTO sim_intento_exenciones (intento_id, tipo, descripcion, valor_declarado)
                VALUES (:intento_id, :tipo, :desc, :valor)";
        $stmt = $this->db->prepare($sql);

        foreach ($items as $ex) {
            $stmt->execute([
                'intento_id' => $intentoId,
                'tipo'       => $ex['tipo_exencion'] ?? $ex['tipo'] ?? null,
                'desc'       => $ex['descripcion'] ?? null,
                'valor'      => self::toFloat($ex['valor_declarado'] ?? '0'),
            ]);
        }
    }

    // ════════════════════════════════════════════════════════
    //  10. EXONERACIONES
    // ════════════════════════════════════════════════════════

    private function insertExoneraciones(int $intentoId, array $borrador): void
    {
        $items = $borrador['exoneraciones'] ?? [];
        if (empty($items)) return;

        $sql = "INSERT INTO sim_intento_exoneraciones (intento_id, tipo, descripcion, valor_declarado)
                VALUES (:intento_id, :tipo, :desc, :valor)";
        $stmt = $this->db->prepare($sql);

        foreach ($items as $ex) {
            $stmt->execute([
                'intento_id' => $intentoId,
                'tipo'       => $ex['tipo_exoneracion'] ?? $ex['tipo'] ?? null,
                'desc'       => $ex['descripcion'] ?? null,
                'valor'      => self::toFloat($ex['valor_declarado'] ?? '0'),
            ]);
        }
    }

    // ════════════════════════════════════════════════════════
    //  11. PRÓRROGAS
    // ════════════════════════════════════════════════════════

    private function insertProrrogas(int $intentoId, array $borrador): void
    {
        $items = $borrador['prorrogas'] ?? [];
        if (empty($items)) return;

        $sql = "INSERT INTO sim_intento_prorrogas 
                (intento_id, fecha_solicitud, nro_resolucion, fecha_resolucion,
                 plazo_otorgado_dias, fecha_vencimiento)
                VALUES (:intento_id, :solicitud, :resolucion, :fecha_res, :plazo, :vencimiento)";
        $stmt = $this->db->prepare($sql);

        foreach ($items as $pr) {
            $stmt->execute([
                'intento_id'  => $intentoId,
                'solicitud'   => $pr['fecha_solicitud'] ?? date('Y-m-d'),
                'resolucion'  => ($pr['nro_resolucion'] ?? null) ?: null,
                'fecha_res'   => ($pr['fecha_resolucion'] ?? null) ?: null,
                'plazo'       => !empty($pr['plazo_dias'] ?? $pr['plazo_otorgado_dias'] ?? null) 
                                 ? (int) ($pr['plazo_dias'] ?? $pr['plazo_otorgado_dias']) : null,
                'vencimiento' => ($pr['fecha_vencimiento'] ?? null) ?: null,
            ]);
        }
    }

    // ════════════════════════════════════════════════════════
    //  12. CÁLCULO MANUAL
    // ════════════════════════════════════════════════════════

    private function insertCalculoManual(int $intentoId, array $borrador, array $indexMap): void
    {
        $calculoManual = $borrador['calculo_manual'] ?? null;
        if (!$calculoManual || empty($calculoManual['herederos'])) return;

        $sql = "INSERT INTO sim_intento_calculo_manual 
                (intento_id, relacion_id, cuota_parte_ut, reduccion_bs)
                VALUES (:intento_id, :relacion_id, :cuota, :reduccion)";
        $stmt = $this->db->prepare($sql);

        foreach ($calculoManual['herederos'] as $i => $cm) {
            // El índice del heredero en el array de calculo_manual corresponde
            // al índice en herederos.items[], que está mapeado en indexMap
            $relacionId = $indexMap[$i] ?? null;
            if (!$relacionId) continue;

            $stmt->execute([
                'intento_id'  => $intentoId,
                'relacion_id' => $relacionId,
                'cuota'       => self::toFloat($cm['cuota_parte_ut'] ?? '0'),
                'reduccion'   => self::toFloat($cm['reduccion_bs'] ?? $cm['reduccion'] ?? '0'),
            ]);
        }
    }

    // ════════════════════════════════════════════════════════
    //  BITÁCORA
    // ════════════════════════════════════════════════════════

    private function registrarBitacora(int $intentoId): void
    {
        try {
            $stmt = $this->db->prepare("
                SELECT i.numero_intento,
                       cfg.max_intentos,
                       ce.titulo AS caso_titulo
                FROM sim_intentos i
                INNER JOIN sim_caso_asignaciones a   ON a.id  = i.asignacion_id
                INNER JOIN sim_caso_configs cfg       ON cfg.id = a.config_id
                INNER JOIN sim_casos_estudios ce      ON ce.id  = cfg.caso_id
                WHERE i.id = :id
                LIMIT 1
            ");
            $stmt->execute(['id' => $intentoId]);
            $ctx = $stmt->fetch(PDO::FETCH_ASSOC);

            $numIntento  = $ctx['numero_intento'] ?? '?';
            $maxIntentos = $ctx['max_intentos'] ?? '?';
            $casoTitulo  = $ctx['caso_titulo'] ?? 'Sin título';

            $detalle = "Envió intento #{$numIntento} de {$maxIntentos} del caso: {$casoTitulo}";

            BitacoraModel::registrar(
                BitacoraModel::ATTEMPT_SUBMITTED,
                'simulador',
                null,           // userId → tomará de $_SESSION
                null,           // email  → tomará de $_SESSION
                'sim_intentos',
                $intentoId,
                $detalle
            );
        } catch (\Throwable $e) {
            // La bitácora NUNCA debe impedir el flujo principal
            error_log('[StoreIntentoModel] Error bitácora: ' . $e->getMessage());
        }
    }

    // ════════════════════════════════════════════════════════
    //  13. ESTADO
    // ════════════════════════════════════════════════════════

    private function insertEstado(int $intentoId, string $estado): void
    {
        $sql = "INSERT INTO sim_intento_estados (intento_id, estado) VALUES (:intento_id, :estado)";
        $this->db->prepare($sql)->execute([
            'intento_id' => $intentoId,
            'estado'     => $estado,
        ]);
    }

    // ════════════════════════════════════════════════════════
    //  BIEN LITIGIOSO (compartido por inmuebles y muebles)
    // ════════════════════════════════════════════════════════

    private function insertBienLitigioso(int $intentoId, int $bienId, string $bienTipo, array $b): void
    {
        $sql = "INSERT INTO sim_intento_bienes_litigiosos 
                (intento_id, bien_tipo, bien_id, numero_expediente, tribunal_causa, partes_juicio, estado_juicio)
                VALUES (:intento_id, :bien_tipo, :bien_id, :expediente, :tribunal, :partes, :estado)";

        $this->db->prepare($sql)->execute([
            'intento_id' => $intentoId,
            'bien_tipo'  => $bienTipo,
            'bien_id'    => $bienId,
            'expediente' => $b['numero_expediente'] ?? null,
            'tribunal'   => $b['tribunal_causa'] ?? null,
            'partes'     => $b['partes_juicio'] ?? null,
            'estado'     => $b['estado_juicio'] ?? null,
        ]);
    }

    // ════════════════════════════════════════════════════════
    //  HELPERS
    // ════════════════════════════════════════════════════════

    /**
     * Helper genérico para insertar en tablas de detalle de bien mueble.
     */
    private function insertSimple(string $table, int $bienMuebleId, array $fields): void
    {
        $fields['bien_mueble_id'] = $bienMuebleId;
        $columns = implode(', ', array_keys($fields));
        $placeholders = ':' . implode(', :', array_keys($fields));
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $this->db->prepare($sql)->execute($fields);
    }

    /**
     * Safely parse a value that may be in Venezuelan format (dots=thousands, comma=decimal)
     * e.g. "-1.234.567,89" → -1234567.89
     */
    private static function toFloat(mixed $value, float $default = 0): float
    {
        if (is_int($value) || is_float($value))
            return (float) $value;
        if (!is_string($value) || $value === '')
            return $default;
        if (is_numeric($value))
            return (float) $value;
        // Venezuelan format: comma present → strip dots, swap comma for dot
        if (str_contains($value, ',')) {
            $clean = str_replace('.', '', $value);
            $clean = str_replace(',', '.', $clean);
            return is_numeric($clean) ? (float) $clean : $default;
        }
        return $default;
    }

    /**
     * Busca o crea una empresa por RIF (reutilizado de StoreCasoModel).
     */
    private function resolveEmpresa(?string $rif, ?string $razonSocial): ?int
    {
        if (empty($rif)) return null;

        $rifClean = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $rif));

        $stmt = $this->db->prepare("SELECT id, razon_social FROM sim_empresas WHERE rif = :rif LIMIT 1");
        $stmt->execute(['rif' => $rifClean]);
        $empresa = $stmt->fetch(PDO::FETCH_ASSOC);

        // Si no encontró con prefijo, intentar sin
        if (!$empresa && ctype_digit($rifClean)) {
            foreach (['J', 'V', 'E', 'G'] as $prefix) {
                $stmt->execute(['rif' => $prefix . $rifClean]);
                $empresa = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($empresa) break;
            }
        }

        if ($empresa) {
            $id = (int) $empresa['id'];
            if (empty($empresa['razon_social']) && !empty($razonSocial)) {
                $this->db->prepare("UPDATE sim_empresas SET razon_social = :rs WHERE id = :id")
                    ->execute(['rs' => $razonSocial, 'id' => $id]);
            }
            return $id;
        }

        // Crear empresa nueva
        $this->db->prepare("INSERT INTO sim_empresas (rif, razon_social, activo) VALUES (:rif, :rs, 1)")
            ->execute(['rif' => $rifClean, 'rs' => $razonSocial ?? '']);

        return (int) $this->db->lastInsertId();
    }
}
