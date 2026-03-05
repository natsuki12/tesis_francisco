<?php
declare(strict_types=1);

namespace App\Modules\Professor\Models\Casos;

use App\Core\DB;
use PDO;

/**
 * Modelo para INSERTAR un caso sucesoral completo en la BD.
 * Todas las inserciones ocurren dentro de una transacción.
 */
class StoreCasoModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = DB::connect();
    }

    /**
     * Inserta un caso completo.
     * @return int caso_id generado
     */
    public function store(array $data, int $profesorId): int
    {
        $this->db->beginTransaction();

        try {
            // 1. Insertar causante (sim_personas)
            $causanteId = $this->insertPersona($data['causante'] ?? [], $profesorId);

            // 2. Insertar datos fiscales del causante
            $this->insertDatosFiscales($data['datos_fiscales_causante'] ?? [], $causanteId);

            // 3. Insertar acta de defunción
            $fechaFallecimiento = $data['causante']['fecha_fallecimiento'] ?? null;
            $this->insertActaDefuncion($data['acta_defuncion'] ?? [], $causanteId, $fechaFallecimiento);

            // 4. Insertar domicilio fiscal del causante
            $domicilio = $data['domicilio_causante'] ?? [];
            if (!empty($domicilio['estado'])) {
                $this->insertDireccion($domicilio, $causanteId);
            }

            // 4b. Insertar direcciones adicionales del causante
            foreach (($data['direcciones_causante'] ?? []) as $dir) {
                if (!empty($dir['estado'])) {
                    $this->insertDireccion($dir, $causanteId);
                }
            }

            // 5. Insertar representante (sim_personas)
            $representanteId = null;
            $rep = $data['representante'] ?? [];
            if (!empty($rep['nombres'])) {
                // Mapear el tipo_cedula del representante:
                // Frontend envía tipo_cedula='Cédula'|'Rif' (UI) y letra_cedula='V'|'E'
                // BD espera sim_personas.tipo_cedula = 'V'|'E'|'No_Aplica'
                if (isset($rep['letra_cedula'])) {
                    $rep['tipo_cedula'] = $rep['letra_cedula']; // 'V' o 'E'
                } elseif (($rep['tipo_cedula'] ?? '') === 'Cédula') {
                    $rep['tipo_cedula'] = 'V';
                } elseif (($rep['tipo_cedula'] ?? '') === 'Rif') {
                    $rep['tipo_cedula'] = 'No_Aplica';
                }
                $representanteId = $this->insertPersona($rep, $profesorId);
            }

            // 6. Insertar caso (sim_casos_estudios)
            $caso = $data['caso'] ?? [];
            $casoId = $this->insertCaso($caso, $profesorId, $causanteId, $representanteId, $fechaFallecimiento);

            // 7. Insertar config (sim_caso_configs)
            $config = $data['config'] ?? [];
            $configId = $this->insertConfig($config, $casoId, $profesorId);

            // 8. Insertar tipos de herencia
            $herencia = $data['herencia'] ?? [];
            $this->insertTiposHerencia($herencia['tipos'] ?? [], $casoId);

            // 9. Insertar herederos
            $herederoIdMap = []; // _ref_index → sim_caso_participantes.id
            foreach (($data['herederos'] ?? []) as $index => $h) {
                $herederoIdMap[$index] = $this->insertHeredero($h, $casoId, $profesorId, null);
            }

            // 10. Insertar herederos premuertos (con padre referenciado)
            foreach (($data['herederos_premuertos'] ?? []) as $hp) {
                $padreRefIndex = $hp['premuerto_padre_id'] ?? null;
                $padreParticipanteId = null;
                if ($padreRefIndex !== null && isset($herederoIdMap[(int) $padreRefIndex])) {
                    $padreParticipanteId = $herederoIdMap[(int) $padreRefIndex];
                }
                $this->insertHeredero($hp, $casoId, $profesorId, $padreParticipanteId);
            }

            // 11. Insertar bienes inmuebles (array)
            foreach (($data['bienes_inmuebles'] ?? []) as $bi) {
                $this->insertBienInmueble($bi, $casoId);
            }

            // 12. Insertar bienes muebles (object con categorías como keys)
            $muebles = $data['bienes_muebles'] ?? [];
            if (is_array($muebles) || is_object($muebles)) {
                foreach ($muebles as $catId => $items) {
                    if (!is_array($items))
                        continue;
                    foreach ($items as $bm) {
                        $bm['categoria_bien_mueble_id'] = (int) $catId;
                        $this->insertBienMueble($bm, $casoId);
                    }
                }
            }

            // 13. Insertar pasivos deuda (array)
            foreach (($data['pasivos_deuda'] ?? []) as $pd) {
                $this->insertPasivoDeuda($pd, $casoId);
            }

            // 14. Insertar pasivos gastos (array)
            foreach (($data['pasivos_gastos'] ?? []) as $pg) {
                $this->insertPasivoGasto($pg, $casoId);
            }

            // 15. Insertar exenciones (array)
            foreach (($data['exenciones'] ?? []) as $ex) {
                $this->insertExencion($ex, $casoId);
            }

            // 16. Insertar exoneraciones (array)
            foreach (($data['exoneraciones'] ?? []) as $exo) {
                $this->insertExoneracion($exo, $casoId);
            }

            // 17. Insertar prórrogas (array)
            foreach (($data['prorrogas'] ?? []) as $pr) {
                $this->insertProrroga($pr, $casoId);
            }

            // 18. Insertar asignaciones de estudiantes
            $this->insertAsignaciones($data['estudiantes_asignados'] ?? [], $configId);

            $this->db->commit();
            return $casoId;

        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // ====================================================================
    // Personas
    // ====================================================================
    private function insertPersona(array $p, int $createdBy): int
    {
        $sql = "INSERT INTO sim_personas 
                (tipo_cedula, cedula, pasaporte, rif_personal, nombres, apellidos, 
                 sexo, estado_civil, fecha_nacimiento, nacionalidad, created_by)
                VALUES (:tipo_cedula, :cedula, :pasaporte, :rif_personal, :nombres, :apellidos,
                        :sexo, :estado_civil, :fecha_nacimiento, :nacionalidad, :created_by)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'tipo_cedula' => $p['tipo_cedula'] ?? null,
            'cedula' => $p['cedula'] ?: null,
            'pasaporte' => $p['pasaporte'] ?: null,
            'rif_personal' => $p['rif_personal'] ?: null,
            'nombres' => $p['nombres'] ?? '',
            'apellidos' => $p['apellidos'] ?? '',
            'sexo' => $p['sexo'] ?? null,
            'estado_civil' => $p['estado_civil'] ?? null,
            'fecha_nacimiento' => $p['fecha_nacimiento'] ?: null,
            'nacionalidad' => !empty($p['nacionalidad']) ? (int) $p['nacionalidad'] : null,
            'created_by' => $createdBy,
        ]);

        return (int) $this->db->lastInsertId();
    }

    // ====================================================================
    // Datos fiscales
    // ====================================================================
    private function insertDatosFiscales(array $df, int $personaId): void
    {
        if (empty($df))
            return;

        $sql = "INSERT INTO sim_causante_datos_fiscales 
                (sim_persona_id, domiciliado_pais, fecha_cierre_fiscal)
                VALUES (:persona_id, :domiciliado_pais, :fecha_cierre_fiscal)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'persona_id' => $personaId,
            'domiciliado_pais' => (int) ($df['domiciliado_pais'] ?? 1),
            'fecha_cierre_fiscal' => $df['fecha_cierre_fiscal'] ?: null,
        ]);
    }

    // ====================================================================
    // Acta de defunción
    // ====================================================================
    private function insertActaDefuncion(array $acta, int $personaId, ?string $fechaFallecimiento): void
    {
        $sql = "INSERT INTO sim_actas_defunciones 
                (sim_persona_id, fecha_fallecimiento, numero_acta, year_acta, parroquia_registro_id)
                VALUES (:persona_id, :fecha_fallecimiento, :numero_acta, :year_acta, :parroquia_registro_id)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'persona_id' => $personaId,
            'fecha_fallecimiento' => $fechaFallecimiento ?: ($acta['fecha_fallecimiento'] ?? null),
            'numero_acta' => $acta['numero_acta'] ?: null,
            'year_acta' => !empty($acta['year_acta']) ? (int) $acta['year_acta'] : null,
            'parroquia_registro_id' => !empty($acta['parroquia_registro_id']) ? (int) $acta['parroquia_registro_id'] : null,
        ]);
    }

    // ====================================================================
    // Direcciones
    // ====================================================================
    private function insertDireccion(array $d, int $personaId): void
    {
        $sql = "INSERT INTO sim_persona_direcciones 
                (sim_persona_id, tipo_direccion, tipo_vialidad, nombre_vialidad, tipo_inmueble,
                 nro_inmueble, tipo_nivel, nro_nivel, tipo_sector, nombre_sector,
                 estado_id, municipio_id, parroquia_id, ciudad_id, codigo_postal_id,
                 telefono_fijo, telefono_celular, fax, punto_referencia)
                VALUES (:persona_id, :tipo_direccion, :tipo_vialidad, :nombre_vialidad, :tipo_inmueble,
                        :nro_inmueble, :tipo_nivel, :nro_nivel, :tipo_sector, :nombre_sector,
                        :estado_id, :municipio_id, :parroquia_id, :ciudad_id, :codigo_postal_id,
                        :telefono_fijo, :telefono_celular, :fax, :punto_referencia)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'persona_id' => $personaId,
            'tipo_direccion' => $d['tipo_direccion'] ?? 'Casa_Matriz_Establecimiento_Principal',
            'tipo_vialidad' => $d['tipo_vialidad'] ?? null,
            'nombre_vialidad' => $d['nombre_vialidad'] ?? null,
            'tipo_inmueble' => $d['tipo_inmueble'] ?? null,
            'nro_inmueble' => $d['nro_inmueble'] ?? null,
            'tipo_nivel' => $d['tipo_nivel'] ?? null,
            'nro_nivel' => $d['nro_nivel'] ?? null,
            'tipo_sector' => $d['tipo_sector'] ?? null,
            'nombre_sector' => $d['nombre_sector'] ?? null,
            'estado_id' => !empty($d['estado']) ? (int) $d['estado'] : null,
            'municipio_id' => !empty($d['municipio']) ? (int) $d['municipio'] : null,
            'parroquia_id' => !empty($d['parroquia']) ? (int) $d['parroquia'] : null,
            'ciudad_id' => !empty($d['ciudad']) ? (int) $d['ciudad'] : null,
            'codigo_postal_id' => !empty($d['codigo_postal_id']) ? (int) $d['codigo_postal_id'] : null,
            'telefono_fijo' => $d['telefono_fijo'] ?: null,
            'telefono_celular' => $d['telefono_celular'] ?: null,
            'fax' => $d['fax'] ?: null,
            'punto_referencia' => $d['punto_referencia'] ?: null,
        ]);
    }

    // ====================================================================
    // Caso principal
    // ====================================================================
    private function insertCaso(array $caso, int $profesorId, int $causanteId, ?int $representanteId, ?string $fechaFallecimiento): int
    {
        // Resolver unidad tributaria automáticamente desde fecha de fallecimiento
        $utId = null;
        if ($fechaFallecimiento) {
            $year = (int) date('Y', strtotime($fechaFallecimiento));
            $stmt = $this->db->prepare("SELECT id FROM sim_cat_unidades_tributarias WHERE anio = :anio LIMIT 1");
            $stmt->execute(['anio' => $year]);
            $utId = $stmt->fetchColumn() ?: null;
        }

        // Normalizar tipo_sucesion: frontend envía 'Con Cédula', BD espera 'Con_Cedula'
        $tipoSucesion = $caso['tipo_sucesion'] ?? 'Con_Cedula';
        $tipoSucesion = str_replace(' ', '_', $tipoSucesion);
        $tipoSucesion = str_replace(['é', 'É'], 'e', $tipoSucesion);

        $sql = "INSERT INTO sim_casos_estudios 
                (titulo, descripcion, tipo_sucesion, estado, profesor_id, causante_id, 
                 representante_id, unidad_tributaria_id)
                VALUES (:titulo, :descripcion, :tipo_sucesion, :estado, :profesor_id, :causante_id,
                        :representante_id, :unidad_tributaria_id)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'titulo' => $caso['titulo'] ?? '',
            'descripcion' => $caso['descripcion'] ?? null,
            'tipo_sucesion' => $tipoSucesion,
            'estado' => $caso['estado'] ?? 'Borrador',
            'profesor_id' => $profesorId,
            'causante_id' => $causanteId,
            'representante_id' => $representanteId,
            'unidad_tributaria_id' => $utId,
        ]);

        return (int) $this->db->lastInsertId();
    }

    // ====================================================================
    // Config
    // ====================================================================
    private function insertConfig(array $config, int $casoId, int $profesorId): int
    {
        // Normalizar modalidad: frontend puede enviar 'Practica', BD espera 'Practica_Libre'
        $modalidad = $config['modalidad'] ?? null;
        if ($modalidad === 'Practica')
            $modalidad = 'Practica_Libre';

        $sql = "INSERT INTO sim_caso_configs 
                (caso_id, profesor_id, modalidad, max_intentos, fecha_limite)
                VALUES (:caso_id, :profesor_id, :modalidad, :max_intentos, :fecha_limite)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'caso_id' => $casoId,
            'profesor_id' => $profesorId,
            'modalidad' => $modalidad,
            'max_intentos' => (int) ($config['max_intentos'] ?? 0),
            'fecha_limite' => $config['fecha_limite'] ?: null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    // ====================================================================
    // Tipos de herencia
    // ====================================================================
    private function insertTiposHerencia(array $tipos, int $casoId): void
    {
        if (empty($tipos))
            return;

        $sql = "INSERT INTO sim_caso_tipoherencia_rel 
                (caso_estudio_id, tipo_herencia_id, subtipo_testamento, fecha_testamento, fecha_conclusion_inventario)
                VALUES (:caso_id, :tipo_id, :subtipo, :fecha_test, :fecha_inv)";
        $stmt = $this->db->prepare($sql);

        foreach ($tipos as $tipo) {
            // Soporta tanto array de objetos [{tipo_herencia_id:1, ...}] como array simple [1,2,3]
            $tipoId = is_array($tipo) ? ($tipo['tipo_herencia_id'] ?? null) : $tipo;
            if (!empty($tipoId)) {
                $stmt->execute([
                    'caso_id' => $casoId,
                    'tipo_id' => (int) $tipoId,
                    'subtipo' => is_array($tipo) ? ($tipo['subtipo_testamento'] ?? null) : null,
                    'fecha_test' => is_array($tipo) && !empty($tipo['fecha_testamento']) ? $tipo['fecha_testamento'] : null,
                    'fecha_inv' => is_array($tipo) && !empty($tipo['fecha_conclusion_inventario']) ? $tipo['fecha_conclusion_inventario'] : null,
                ]);
            }
        }
    }

    // ====================================================================
    // Herederos
    // ====================================================================
    private function insertHeredero(array $h, int $casoId, int $profesorId, ?int $padreParticipanteId): int
    {
        // Derivar tipo_cedula para sim_personas desde letra_cedula del modal
        // El modal colecta letra_cedula='V'|'E'|'J'|'G' y tipo_documento='Cédula'|'RIF'
        // pero sim_personas espera tipo_cedula='V'|'E'|'No_Aplica'
        $tipoCedula = $h['tipo_cedula'] ?? null;
        if (isset($h['letra_cedula'])) {
            $tipoCedula = in_array($h['letra_cedula'], ['V', 'E']) ? $h['letra_cedula'] : 'No_Aplica';
        }

        // Insertar persona del heredero
        $personaData = [
            'tipo_cedula' => $tipoCedula,
            'cedula' => $h['cedula'] ?? null,
            'pasaporte' => $h['pasaporte'] ?? null,
            'rif_personal' => $h['rif_personal'] ?? null,
            'nombres' => $h['nombres'] ?? '',
            'apellidos' => $h['apellidos'] ?? '',
            'sexo' => $h['sexo'] ?? null,
            'estado_civil' => $h['estado_civil'] ?? null,
            'fecha_nacimiento' => $h['fecha_nacimiento'] ?? null,
            'nacionalidad' => $h['nacionalidad'] ?? null,
        ];
        $personaId = $this->insertPersona($personaData, $profesorId);

        // Insertar participante
        $esPremuerto = ($h['premuerto'] ?? 'NO') === 'SI';
        $sql = "INSERT INTO sim_caso_participantes 
                (caso_estudio_id, persona_id, rol_en_caso, parentesco_id, 
                 es_premuerto, premuerto_padre_id)
                VALUES (:caso_id, :persona_id, :rol, :parentesco_id, :es_premuerto, :padre_id)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'caso_id' => $casoId,
            'persona_id' => $personaId,
            'rol' => $h['caracter'] ?? 'Heredero',
            'parentesco_id' => (int) ($h['parentesco_id'] ?? 0),
            'es_premuerto' => $esPremuerto ? 1 : 0,
            'padre_id' => $padreParticipanteId,
        ]);

        $participanteId = (int) $this->db->lastInsertId();

        // Si es premuerto, insertar acta de defunción
        if ($esPremuerto && !empty($h['fecha_fallecimiento'])) {
            $this->insertActaDefuncion(
                ['numero_acta' => null, 'year_acta' => null, 'parroquia_registro_id' => null],
                $personaId,
                $h['fecha_fallecimiento']
            );
        }

        return $participanteId;
    }

    // ====================================================================
    // Bienes Inmuebles
    // ====================================================================
    private function insertBienInmueble(array $b, int $casoId): void
    {
        $sql = "INSERT INTO sim_caso_bienes_inmuebles 
                (caso_estudio_id, es_vivienda_principal, es_bien_litigioso, porcentaje,
                 descripcion, linderos, superficie_construida, superficie_no_construida,
                 area_superficie, direccion, oficina_registro, nro_registro, libro,
                 protocolo, fecha_registro, trimestre, asiento_registral, matricula,
                 folio_real_anio, valor_original, valor_declarado)
                VALUES (:caso_id, :vivienda, :litigioso, :porcentaje,
                        :descripcion, :linderos, :sup_const, :sup_no_const,
                        :area, :direccion, :oficina, :nro_registro, :libro,
                        :protocolo, :fecha_reg, :trimestre, :asiento, :matricula,
                        :folio_anio, :val_original, :val_declarado)";

        $esVivienda = in_array($b['vivienda_principal'] ?? '', ['Si', '1', 'true'], true);
        $esLitigioso = in_array($b['bien_litigioso'] ?? '', ['Si', '1', 'true'], true);

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'caso_id' => $casoId,
            'vivienda' => $esVivienda ? 1 : 0,
            'litigioso' => $esLitigioso ? 1 : 0,
            'porcentaje' => (float) ($b['porcentaje'] ?? 100),
            'descripcion' => $b['descripcion'] ?? null,
            'linderos' => $b['linderos'] ?? null,
            'sup_const' => (float) ($b['superficie_construida'] ?? 0),
            'sup_no_const' => (float) ($b['superficie_no_construida'] ?? 0),
            'area' => (float) ($b['area_superficie'] ?? 0),
            'direccion' => $b['direccion'] ?? null,
            'oficina' => $b['oficina_registro'] ?? null,
            'nro_registro' => $b['nro_registro'] ?? null,
            'libro' => $b['libro'] ?? null,
            'protocolo' => $b['protocolo'] ?? null,
            'fecha_reg' => $b['fecha_registro'] ?: null,
            'trimestre' => $b['trimestre'] ?? null,
            'asiento' => $b['asiento_registral'] ?? null,
            'matricula' => $b['matricula'] ?? null,
            'folio_anio' => $b['folio_real_anio'] ?? null,
            'val_original' => (float) ($b['valor_original'] ?? 0),
            'val_declarado' => (float) ($b['valor_declarado'] ?? 0),
        ]);

        $bienId = (int) $this->db->lastInsertId();

        // Insertar tipos de bien inmueble (relación M:N)
        $tipos = $b['tipo_bien_inmueble_id'] ?? [];
        if (!is_array($tipos))
            $tipos = [$tipos];
        $tipoStmt = $this->db->prepare(
            "INSERT INTO sim_caso_bien_inmueble_tipo_rel (bien_inmueble_id, tipo_bien_inmueble_id) VALUES (:bien_id, :tipo_id)"
        );
        foreach ($tipos as $tipoId) {
            if (!empty($tipoId)) {
                $tipoStmt->execute(['bien_id' => $bienId, 'tipo_id' => (int) $tipoId]);
            }
        }

        // Insertar datos de bien litigioso
        if ($esLitigioso) {
            $this->insertBienLitigioso($b, $bienId, 'Inmueble', $casoId);
        }
    }

    // ====================================================================
    // Bienes Muebles
    // ====================================================================
    private function insertBienMueble(array $b, int $casoId): void
    {
        $esLitigioso = in_array($b['bien_litigioso'] ?? '', ['Si', '1', 'true'], true);

        $sql = "INSERT INTO sim_caso_bienes_muebles 
                (caso_estudio_id, categoria_bien_mueble_id, tipo_bien_mueble_id,
                 es_bien_litigioso, porcentaje, descripcion, valor_declarado)
                VALUES (:caso_id, :cat_id, :tipo_id, :litigioso, :porcentaje, :descripcion, :valor)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'caso_id' => $casoId,
            'cat_id' => (int) ($b['categoria_bien_mueble_id'] ?? 0),
            'tipo_id' => !empty($b['tipo_bien_mueble_id']) ? (int) $b['tipo_bien_mueble_id'] : null,
            'litigioso' => $esLitigioso ? 1 : 0,
            'porcentaje' => (float) ($b['porcentaje'] ?? 100),
            'descripcion' => $b['descripcion'] ?? null,
            'valor' => (float) ($b['valor_declarado'] ?? 0),
        ]);

        $bienMuebleId = (int) $this->db->lastInsertId();

        // Insertar detalle por categoría
        $catId = (int) ($b['categoria_bien_mueble_id'] ?? 0);
        $this->insertDetalleMueble($catId, $b, $bienMuebleId);

        if ($esLitigioso) {
            $this->insertBienLitigioso($b, $bienMuebleId, 'Mueble', $casoId);
        }
    }

    private function insertDetalleMueble(int $catId, array $b, int $bienMuebleId): void
    {
        switch ($catId) {
            case 1: // Banco
                $this->insertSimple('sim_caso_bm_banco', $bienMuebleId, [
                    'banco_id' => !empty($b['banco_id']) ? (int) $b['banco_id'] : null,
                    'numero_cuenta' => $b['numero_cuenta'] ?? null,
                ]);
                break;
            case 2: // Seguros
                $empresaId = $this->resolveEmpresa($b['rif_empresa'] ?? null, $b['razon_social'] ?? null);
                $this->insertSimple('sim_caso_bm_seguro', $bienMuebleId, [
                    'empresa_id' => $empresaId,
                    'numero_prima' => $b['numero_prima'] ?? null,
                ]);
                break;
            case 3: // Transporte
                $this->insertSimple('sim_caso_bm_transporte', $bienMuebleId, [
                    'anio' => $b['anio'] ?? null,
                    'marca' => $b['marca'] ?? null,
                    'modelo' => $b['modelo'] ?? null,
                    'serial_placa' => $b['serial_placa'] ?? null,
                ]);
                break;
            case 4: // Opciones de compra
                $this->insertSimple('sim_caso_bm_opciones_compra', $bienMuebleId, [
                    'nombre_oferente' => $b['nombre_oferente'] ?? null,
                ]);
                break;
            case 5: // Cuentas por cobrar
                $this->insertSimple('sim_caso_bm_cuentas_cobrar', $bienMuebleId, [
                    'rif_cedula' => $b['rif_cedula'] ?? null,
                    'apellidos_nombres' => $b['apellidos_nombres'] ?? null,
                ]);
                break;
            case 6: // Semovientes
                $this->insertSimple('sim_caso_bm_semovientes', $bienMuebleId, [
                    'tipo_semoviente_id' => !empty($b['tipo_semoviente_id']) ? (int) $b['tipo_semoviente_id'] : null,
                    'cantidad' => (int) ($b['cantidad'] ?? 0),
                ]);
                break;
            case 7: // Bonos
                $this->insertSimple('sim_caso_bm_bonos', $bienMuebleId, [
                    'tipo_bonos' => $b['tipo_bonos'] ?? null,
                    'numero_bonos' => $b['numero_bonos'] ?? null,
                    'numero_serie' => $b['numero_serie'] ?? null,
                ]);
                break;
            case 8: // Acciones
                $empresaId = $this->resolveEmpresa($b['rif_empresa'] ?? null, $b['razon_social'] ?? null);
                $this->insertSimple('sim_caso_bm_acciones', $bienMuebleId, [
                    'empresa_id' => $empresaId,
                ]);
                break;
            case 9: // Prestaciones
                $empresaId = $this->resolveEmpresa($b['rif_empresa'] ?? null, $b['razon_social'] ?? null);
                $poseeBanco = in_array($b['posee_banco'] ?? '', ['SI', '1', 'true'], true);
                $this->insertSimple('sim_caso_bm_prestaciones', $bienMuebleId, [
                    'posee_banco' => $poseeBanco ? 1 : 0,
                    'banco_id' => $poseeBanco && !empty($b['banco_id']) ? (int) $b['banco_id'] : null,
                    'numero_cuenta' => $poseeBanco ? ($b['numero_cuenta'] ?? null) : null,
                    'empresa_id' => $empresaId,
                ]);
                break;
            case 10: // Caja de ahorro
                $empresaId = $this->resolveEmpresa($b['rif_empresa'] ?? null, $b['razon_social'] ?? null);
                $this->insertSimple('sim_caso_bm_caja_ahorro', $bienMuebleId, [
                    'empresa_id' => $empresaId,
                ]);
                break;
            // case 11: Plantaciones — no tiene tabla detalle
            // case 12: Otros — no tiene tabla detalle
        }
    }

    /**
     * Helper genérico para insertar en tablas de detalle de bien mueble.
     */
    private function insertSimple(string $table, int $bienMuebleId, array $fields): void
    {
        $fields['bien_mueble_id'] = $bienMuebleId;
        $columns = implode(', ', array_keys($fields));
        $placeholders = ':' . implode(', :', array_keys($fields));
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($fields);
    }

    // ====================================================================
    // Bien Litigioso
    // ====================================================================
    private function insertBienLitigioso(array $b, int $bienId, string $bienTipo, int $casoId = 0): void
    {
        $sql = "INSERT INTO sim_caso_bienes_litigiosos 
                (caso_estudio_id, bien_id, bien_tipo, numero_expediente, tribunal_causa, partes_juicio, estado_juicio)
                VALUES (:caso_id, :bien_id, :bien_tipo, :expediente, :tribunal, :partes, :estado)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'caso_id' => $casoId,
            'bien_id' => $bienId,
            'bien_tipo' => $bienTipo,
            'expediente' => $b['numero_expediente'] ?? null,
            'tribunal' => $b['tribunal_causa'] ?? null,
            'partes' => $b['partes_juicio'] ?? null,
            'estado' => $b['estado_juicio'] ?? null,
        ]);
    }

    // ====================================================================
    // Pasivos
    // ====================================================================
    private function insertPasivoDeuda(array $pd, int $casoId): void
    {
        $sql = "INSERT INTO sim_caso_pasivos_deuda 
                (caso_estudio_id, tipo_pasivo_deuda_id, banco_id, numero_tdc, 
                 porcentaje, descripcion, valor_declarado)
                VALUES (:caso_id, :tipo_id, :banco_id, :tdc, :pct, :desc, :valor)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'caso_id' => $casoId,
            'tipo_id' => (int) ($pd['tipo_pasivo_deuda_id'] ?? 0),
            'banco_id' => !empty($pd['banco_id']) ? (int) $pd['banco_id'] : null,
            'tdc' => $pd['numero_tdc'] ?: null,
            'pct' => (float) ($pd['porcentaje'] ?? 100),
            'desc' => $pd['descripcion'] ?? null,
            'valor' => (float) ($pd['valor_declarado'] ?? 0),
        ]);
    }

    private function insertPasivoGasto(array $pg, int $casoId): void
    {
        $sql = "INSERT INTO sim_caso_pasivos_gastos 
                (caso_estudio_id, tipo_pasivo_gasto_id, porcentaje, descripcion, valor_declarado)
                VALUES (:caso_id, :tipo_id, :pct, :desc, :valor)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'caso_id' => $casoId,
            'tipo_id' => (int) ($pg['tipo_pasivo_gasto_id'] ?? 0),
            'pct' => (float) ($pg['porcentaje'] ?? 100),
            'desc' => $pg['descripcion'] ?? null,
            'valor' => (float) ($pg['valor_declarado'] ?? 0),
        ]);
    }

    // ====================================================================
    // Exenciones y Exoneraciones
    // ====================================================================
    private function insertExencion(array $ex, int $casoId): void
    {
        $sql = "INSERT INTO sim_caso_exenciones (caso_estudio_id, tipo, descripcion, valor_declarado)
                VALUES (:caso_id, :tipo, :desc, :valor)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'caso_id' => $casoId,
            'tipo' => $ex['tipo_exencion'] ?? null,
            'desc' => $ex['descripcion'] ?? null,
            'valor' => (float) ($ex['valor_declarado'] ?? 0),
        ]);
    }

    private function insertExoneracion(array $ex, int $casoId): void
    {
        $sql = "INSERT INTO sim_caso_exoneraciones (caso_estudio_id, tipo, descripcion, valor_declarado)
                VALUES (:caso_id, :tipo, :desc, :valor)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'caso_id' => $casoId,
            'tipo' => $ex['tipo_exoneracion'] ?? null,
            'desc' => $ex['descripcion'] ?? null,
            'valor' => (float) ($ex['valor_declarado'] ?? 0),
        ]);
    }

    // ====================================================================
    // Prórrogas
    // ====================================================================
    private function insertProrroga(array $pr, int $casoId): void
    {
        $sql = "INSERT INTO sim_caso_prorrogas 
                (caso_estudio_id, fecha_solicitud, nro_resolucion, fecha_resolucion, 
                 plazo_otorgado_dias, fecha_vencimiento)
                VALUES (:caso_id, :solicitud, :resolucion, :fecha_res, :plazo, :vencimiento)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'caso_id' => $casoId,
            'solicitud' => $pr['fecha_solicitud'] ?: null,
            'resolucion' => $pr['nro_resolucion'] ?? null,
            'fecha_res' => $pr['fecha_resolucion'] ?: null,
            'plazo' => (int) ($pr['plazo_dias'] ?? 0),
            'vencimiento' => $pr['fecha_vencimiento'] ?: null,
        ]);
    }

    // ====================================================================
    // Asignaciones de estudiantes
    // ====================================================================
    private function insertAsignaciones(array $estudianteIds, int $configId): void
    {
        if (empty($estudianteIds))
            return;

        $sql = "INSERT INTO sim_caso_asignaciones (config_id, estudiante_id) VALUES (:config_id, :est_id)";
        $stmt = $this->db->prepare($sql);

        foreach ($estudianteIds as $estId) {
            if (!empty($estId)) {
                $stmt->execute(['config_id' => $configId, 'est_id' => (int) $estId]);
            }
        }
    }

    // ====================================================================
    // Helpers
    // ====================================================================

    /**
     * Busca o crea una empresa por RIF.
     */
    private function resolveEmpresa(?string $rif, ?string $razonSocial): ?int
    {
        if (empty($rif))
            return null;

        // Normalizar RIF (quitar guiones para búsqueda)
        $rifClean = strtoupper(str_replace('-', '', $rif));

        $stmt = $this->db->prepare("SELECT id FROM sim_empresas WHERE REPLACE(rif, '-', '') = :rif LIMIT 1");
        $stmt->execute(['rif' => $rifClean]);
        $id = $stmt->fetchColumn();

        if ($id)
            return (int) $id;

        // Crear empresa
        $stmt = $this->db->prepare("INSERT INTO sim_empresas (rif, razon_social, activo) VALUES (:rif, :rs, 1)");
        $stmt->execute(['rif' => $rif, 'rs' => $razonSocial ?? '']);

        return (int) $this->db->lastInsertId();
    }
}
