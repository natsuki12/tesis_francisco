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
     * Safely parse a value that may be in Venezuelan format (dots=thousands, comma=decimal)
     * e.g. "-1.234.567,89" → -1234567.89
     */
    private static function toFloat(mixed $value, float $default = 0): float
    {
        if (is_int($value) || is_float($value))
            return (float) $value;
        if (!is_string($value) || $value === '')
            return $default;
        // Already standard numeric (e.g. "123.45")
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
     * Guarda (INSERT o UPDATE) un borrador: solo titulo + JSON del stepper.
     * No toca tablas relacionadas (personas, direcciones, herederos, etc.).
     * @return int caso_id
     */
    public function storeDraft(array $data, int $profesorId, ?int $casoId = null): int
    {
        $this->db->beginTransaction();
        try {
            $titulo = $data['caso']['titulo'] ?? 'Sin título';
            $json = json_encode($data, JSON_UNESCAPED_UNICODE);

            if ($casoId) {
                // UPDATE borrador existente
                $sql = "UPDATE sim_casos_estudios 
                        SET titulo = :titulo, borrador_json = :json, updated_at = NOW()
                        WHERE id = :id AND profesor_id = :prof AND estado = 'Borrador'";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    'titulo' => $titulo,
                    'json' => $json,
                    'id' => $casoId,
                    'prof' => $profesorId
                ]);
                $this->db->commit();
                return $casoId;
            }

            // INSERT nuevo borrador
            $sql = "INSERT INTO sim_casos_estudios (profesor_id, titulo, estado, borrador_json)
                    VALUES (:prof, :titulo, 'Borrador', :json)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['prof' => $profesorId, 'titulo' => $titulo, 'json' => $json]);
            $newId = (int) $this->db->lastInsertId();

            $this->db->commit();
            return $newId;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Inserta o actualiza un caso completo.
     * Si $existingCasoId se proporciona, actualiza el caso existente.
     * @return int caso_id
     */
    public function store(array $data, int $profesorId, ?int $existingCasoId = null): int
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


            // 5. Insertar representante (sim_personas)
            $representanteId = null;
            $rep = $data['representante'] ?? [];
            if (!empty($rep['nombres'])) {
                // Mapear el tipo_cedula del representante:
                // Frontend envía tipo_cedula='Cédula'|'Rif' (UI) y letra_cedula='V'|'E'
                // BD espera sim_personas.tipo_cedula = 'V'|'E'|'No_Aplica'
                $tipoStr = $rep['tipo_cedula'] ?? '';
                if ($tipoStr === 'Rif') {
                    $rep['rif_personal'] = ($rep['letra_cedula'] ?? '') . ($rep['cedula'] ?? '');
                    $rep['tipo_cedula'] = 'No_Aplica';
                    $rep['cedula'] = null;
                } elseif ($tipoStr === 'Cédula') {
                    $rep['tipo_cedula'] = $rep['letra_cedula'] ?? 'V';
                    $rep['rif_personal'] = null;
                } else {
                    $rep['tipo_cedula'] = $rep['letra_cedula'] ?? 'No_Aplica';
                }
                $representanteId = $this->insertPersona($rep, $profesorId);
            }

            // 6. Insertar o actualizar caso (sim_casos_estudios)
            $caso = $data['caso'] ?? [];
            if ($existingCasoId) {
                $casoId = $this->updateCaso($existingCasoId, $caso, $causanteId, $representanteId, $fechaFallecimiento);
                // Limpiar datos relacionados antes de re-insertar
                $this->clearCaseRelatedData($casoId);
            } else {
                $casoId = $this->insertCaso($caso, $profesorId, $causanteId, $representanteId, $fechaFallecimiento);
            }

            // 4. Insertar domicilio fiscal del causante vinculándolo al caso
            $domicilio = $data['domicilio_causante'] ?? [];
            if (!empty($domicilio['estado'])) {
                $this->insertDireccion($domicilio, $casoId);
            }

            // 4b. Insertar direcciones adicionales del causante vinculándolas al caso
            foreach (($data['direcciones_causante'] ?? []) as $dir) {
                if (!empty($dir['estado'])) {
                    $this->insertDireccion($dir, $casoId);
                }
            }

            // 7. Insertar tipos de herencia
            $herencia = $data['herencia'] ?? [];
            $this->insertTiposHerencia($herencia['tipos'] ?? [], $casoId);

            // 8. Insertar herederos
            $uidMap = [];        // _uid → sim_caso_participantes.id (for calculo_manual & premuerto linkage)
            foreach (($data['herederos'] ?? []) as $index => $h) {
                $participanteId = $this->insertHeredero($h, $casoId, $profesorId, null);
                // Map _uid → participanteId for calculo_manual and premuerto linkage
                $uid = $h['_uid'] ?? null;
                if ($uid) {
                    $uidMap[$uid] = $participanteId;
                }
            }

            // 9. Insertar herederos premuertos (con padre referenciado)
            foreach (($data['herederos_premuertos'] ?? []) as $hp) {
                $padreRef = $hp['premuerto_padre_id'] ?? null;
                $padreParticipanteId = null;
                // premuerto_padre_id now contains _uid (not cedula), so look up in uidMap
                if ($padreRef !== null && $padreRef !== '' && isset($uidMap[$padreRef])) {
                    $padreParticipanteId = $uidMap[$padreRef];
                }
                $pId = $this->insertHeredero($hp, $casoId, $profesorId, $padreParticipanteId);
                // Map _uid for premuertos too (they can appear in calculo_manual)
                $uid = $hp['_uid'] ?? null;
                if ($uid) {
                    $uidMap[$uid] = $pId;
                }
            }

            // 10. Insertar bienes inmuebles (array)
            foreach (($data['bienes_inmuebles'] ?? []) as $bi) {
                $this->insertBienInmueble($bi, $casoId);
            }

            // 11. Insertar bienes muebles (object con categorías como keys)
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

            // 12. Insertar pasivos deuda (array)
            foreach (($data['pasivos_deuda'] ?? []) as $pd) {
                $this->insertPasivoDeuda($pd, $casoId);
            }

            // 13. Insertar pasivos gastos (array)
            foreach (($data['pasivos_gastos'] ?? []) as $pg) {
                $this->insertPasivoGasto($pg, $casoId);
            }

            // 14. Insertar exenciones (array)
            foreach (($data['exenciones'] ?? []) as $ex) {
                $this->insertExencion($ex, $casoId);
            }

            // 15. Insertar exoneraciones (array)
            foreach (($data['exoneraciones'] ?? []) as $exo) {
                $this->insertExoneracion($exo, $casoId);
            }

            // 16. Insertar prórrogas (array)
            foreach (($data['prorrogas'] ?? []) as $pr) {
                $this->insertProrroga($pr, $casoId);
            }

            // 17. Insertar cálculo manual overrides (array)
            $calculoManual = $data['calculo_manual'] ?? [];
            if (!empty($calculoManual) && !empty($uidMap)) {
                $cmStmt = $this->db->prepare(
                    "INSERT INTO sim_caso_calculo_manual 
                     (caso_estudio_id, participante_id, cuota_parte_ut, reduccion_bs)
                     VALUES (:caso_id, :participante_id, :cuota, :reduccion)"
                );
                foreach ($calculoManual as $cm) {
                    $uid = $cm['_uid'] ?? null;
                    if ($uid && isset($uidMap[$uid])) {
                        $cmStmt->execute([
                            'caso_id' => $casoId,
                            'participante_id' => $uidMap[$uid],
                            'cuota' => (float) ($cm['cuota_parte_ut'] ?? 0),
                            'reduccion' => (float) ($cm['reduccion_bs'] ?? 0),
                        ]);
                    }
                }
            }
            // Las tablas sim_caso_configs y sim_caso_asignaciones quedan vacías al publicar.

            // Limpiar borrador_json al publicar
            $this->db->prepare("UPDATE sim_casos_estudios SET borrador_json = NULL WHERE id = :id")
                ->execute(['id' => $casoId]);

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
        $personaId = !empty($p['persona_id']) ? (int) $p['persona_id'] : null;

        $tipoCedula = $p['tipo_cedula'] ?? null;
        $cedula = $p['cedula'] ?: null;
        $pasaporte = $p['pasaporte'] ?: null;
        $rifPersonal = $p['rif_personal'] ?: null;

        // Si no viene ID, intentar buscar la persona por documento para evitar duplicados
        if (!$personaId) {
            $sqlSearch = "SELECT id, nombres, apellidos, sexo, estado_civil, fecha_nacimiento, nacionalidad 
                          FROM sim_personas 
                          WHERE (tipo_cedula = :tc AND cedula = :ced)
                             OR (pasaporte IS NOT NULL AND pasaporte = :pas)
                             OR (rif_personal IS NOT NULL AND rif_personal = :rif)
                          LIMIT 1";
            $stmtSearch = $this->db->prepare($sqlSearch);
            // PDO requiere bindear null adecuadamente o ignorar la busqueda estricta en DB,
            // pero COALESCE no es necesario ya que se envian param por nombre
            $stmtSearch->execute([
                'tc' => $tipoCedula,
                'ced' => $cedula,
                'pas' => $pasaporte,
                'rif' => $rifPersonal
            ]);
            $existingPersona = $stmtSearch->fetch(PDO::FETCH_ASSOC);

            if ($existingPersona) {
                $personaId = (int) $existingPersona['id'];
                $dbData = $existingPersona;
            }
        } else {
            // Si vino un persona_id del frontend, buscamos sus datos actuales
            $stmtSearch = $this->db->prepare("SELECT nombres, apellidos, sexo, estado_civil, fecha_nacimiento, nacionalidad FROM sim_personas WHERE id = :id LIMIT 1");
            $stmtSearch->execute(['id' => $personaId]);
            $dbData = $stmtSearch->fetch(PDO::FETCH_ASSOC) ?: [];
        }

        if ($personaId) {
            // UPSERT: Actualizar SOLO los campos que estaban vacíos en la BD.
            $nombres = !empty($dbData['nombres']) ? $dbData['nombres'] : ($p['nombres'] ?? '');
            $apellidos = !empty($dbData['apellidos']) ? $dbData['apellidos'] : ($p['apellidos'] ?? '');
            $sexo = !empty($dbData['sexo']) ? $dbData['sexo'] : ($p['sexo'] ?? null);
            $estadoCivil = !empty($dbData['estado_civil']) ? $dbData['estado_civil'] : ($p['estado_civil'] ?? null);
            $fechaNacimiento = !empty($dbData['fecha_nacimiento']) ? $dbData['fecha_nacimiento'] : ($p['fecha_nacimiento'] ?: null);
            $nacionalidad = !empty($dbData['nacionalidad']) ? $dbData['nacionalidad'] : (!empty($p['nacionalidad']) ? (int) $p['nacionalidad'] : null);

            $sqlUpdate = "UPDATE sim_personas 
                          SET nombres = :nombres, apellidos = :apellidos, 
                              sexo = :sexo, estado_civil = :estado_civil, 
                              fecha_nacimiento = :fecha_nacimiento, nacionalidad = :nacionalidad
                          WHERE id = :id";
            $stmtUpdate = $this->db->prepare($sqlUpdate);
            $stmtUpdate->execute([
                'nombres' => $nombres,
                'apellidos' => $apellidos,
                'sexo' => $sexo,
                'estado_civil' => $estadoCivil,
                'fecha_nacimiento' => $fechaNacimiento,
                'nacionalidad' => $nacionalidad,
                'id' => $personaId
            ]);

            return $personaId;
        }

        // Si no existe, hacemos un INSERT normal
        $sql = "INSERT INTO sim_personas 
                (tipo_cedula, cedula, pasaporte, rif_personal, nombres, apellidos, 
                 sexo, estado_civil, fecha_nacimiento, nacionalidad, created_by)
                VALUES (:tipo_cedula, :cedula, :pasaporte, :rif_personal, :nombres, :apellidos,
                        :sexo, :estado_civil, :fecha_nacimiento, :nacionalidad, :created_by)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'tipo_cedula' => $tipoCedula,
            'cedula' => $cedula,
            'pasaporte' => $pasaporte,
            'rif_personal' => $rifPersonal,
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

        // Si ya existen datos fiscales para esta persona, no insertar
        $check = $this->db->prepare("SELECT COUNT(*) FROM sim_causante_datos_fiscales WHERE sim_persona_id = :id");
        $check->execute(['id' => $personaId]);
        if ((int) $check->fetchColumn() > 0) {
            return;
        }

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
        // Si ya existe un acta de defunción para esta persona, no insertar
        $check = $this->db->prepare("SELECT COUNT(*) FROM sim_actas_defunciones WHERE sim_persona_id = :id");
        $check->execute(['id' => $personaId]);
        if ((int) $check->fetchColumn() > 0) {
            return;
        }

        $sql = "INSERT INTO sim_actas_defunciones 
                (sim_persona_id, fecha_fallecimiento, numero_acta, year_acta, parroquia_registro)
                VALUES (:persona_id, :fecha_fallecimiento, :numero_acta, :year_acta, :parroquia_registro)";

        $parroquiaVal = $acta['parroquia_registro'] ?? $acta['parroquia_registro_id'] ?? null;

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'persona_id' => $personaId,
            'fecha_fallecimiento' => $fechaFallecimiento ?: ($acta['fecha_fallecimiento'] ?? null),
            'numero_acta' => $acta['numero_acta'] ?: null,
            'year_acta' => !empty($acta['year_acta']) ? (int) $acta['year_acta'] : null,
            'parroquia_registro' => !empty($parroquiaVal) ? $parroquiaVal : null,
        ]);
    }

    // ====================================================================
    // Direcciones
    // ====================================================================
    private function insertDireccion(array $d, int $casoId): void
    {
        $sql = "INSERT INTO sim_caso_direcciones 
                (sim_caso_estudio_id, tipo_direccion, tipo_vialidad, nombre_vialidad, tipo_inmueble,
                 nro_inmueble, tipo_nivel, nro_nivel, tipo_sector, nombre_sector,
                 estado_id, municipio_id, parroquia_id, ciudad_id, codigo_postal_id,
                 telefono_fijo, telefono_celular, fax, punto_referencia)
                VALUES (:caso_id, :tipo_direccion, :tipo_vialidad, :nombre_vialidad, :tipo_inmueble,
                        :nro_inmueble, :tipo_nivel, :nro_nivel, :tipo_sector, :nombre_sector,
                        :estado_id, :municipio_id, :parroquia_id, :ciudad_id, :codigo_postal_id,
                        :telefono_fijo, :telefono_celular, :fax, :punto_referencia)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'caso_id' => $casoId,
            'tipo_direccion' => $d['tipo_direccion'] ?? null,
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
            $ut = \App\Core\UnidadTributariaService::obtenerPorFecha($fechaFallecimiento);
            $utId = $ut ? (int) $ut['id'] : null;
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

    /**
     * Actualiza un caso existente (cuando se publica un borrador).
     */
    private function updateCaso(int $casoId, array $caso, int $causanteId, ?int $representanteId, ?string $fechaFallecimiento): int
    {
        // Resolver unidad tributaria
        $utId = null;
        if ($fechaFallecimiento) {
            $ut = \App\Core\UnidadTributariaService::obtenerPorFecha($fechaFallecimiento);
            $utId = $ut ? (int) $ut['id'] : null;
        }

        $tipoSucesion = $caso['tipo_sucesion'] ?? 'Con_Cedula';
        $tipoSucesion = str_replace(' ', '_', $tipoSucesion);
        $tipoSucesion = str_replace(['é', 'É'], 'e', $tipoSucesion);

        $sql = "UPDATE sim_casos_estudios SET
                    titulo = :titulo, descripcion = :descripcion,
                    tipo_sucesion = :tipo_sucesion, estado = :estado,
                    causante_id = :causante_id, representante_id = :representante_id,
                    unidad_tributaria_id = :unidad_tributaria_id, borrador_json = NULL
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'titulo' => $caso['titulo'] ?? '',
            'descripcion' => $caso['descripcion'] ?? null,
            'tipo_sucesion' => $tipoSucesion,
            'estado' => $caso['estado'] ?? 'Publicado',
            'causante_id' => $causanteId,
            'representante_id' => $representanteId,
            'unidad_tributaria_id' => $utId,
            'id' => $casoId,
        ]);

        return $casoId;
    }

    /**
     * Elimina datos relacionados de un caso antes de re-insertarlos.
     * Se usa cuando se publica un borrador que ya tenía datos parciales.
     */
    private function clearCaseRelatedData(int $casoId): void
    {
        $tables = [
            'sim_caso_calculo_manual' => 'caso_estudio_id',
            'sim_caso_direcciones' => 'sim_caso_estudio_id',
            'sim_caso_tipoherencia_rel' => 'caso_estudio_id',
            'sim_caso_participantes' => 'caso_estudio_id',
            // Child detail tables BEFORE parent bienes tables (FK order)
            'sim_caso_bienes_litigiosos' => 'caso_estudio_id',
            'sim_caso_pasivos_deuda' => 'caso_estudio_id',
            'sim_caso_pasivos_gastos' => 'caso_estudio_id',
            'sim_caso_exenciones' => 'caso_estudio_id',
            'sim_caso_exoneraciones' => 'caso_estudio_id',
            'sim_caso_prorrogas' => 'caso_estudio_id',
        ];

        foreach ($tables as $table => $col) {
            $this->db->prepare("DELETE FROM {$table} WHERE {$col} = :cid")->execute(['cid' => $casoId]);
        }

        // Delete bien mueble detail tables (FK to sim_caso_bienes_muebles.id)
        $bienMuebleIds = $this->db->prepare("SELECT id FROM sim_caso_bienes_muebles WHERE caso_estudio_id = :cid");
        $bienMuebleIds->execute(['cid' => $casoId]);
        $bmIds = $bienMuebleIds->fetchAll(\PDO::FETCH_COLUMN);
        if (!empty($bmIds)) {
            $inClause = implode(',', array_map('intval', $bmIds));
            $detailTables = [
                'sim_caso_bm_banco', 'sim_caso_bm_seguro', 'sim_caso_bm_transporte',
                'sim_caso_bm_opciones_compra', 'sim_caso_bm_cuentas_cobrar',
                'sim_caso_bm_semovientes', 'sim_caso_bm_bonos', 'sim_caso_bm_acciones',
                'sim_caso_bm_prestaciones', 'sim_caso_bm_caja_ahorro'
            ];
            foreach ($detailTables as $dt) {
                $this->db->exec("DELETE FROM {$dt} WHERE bien_mueble_id IN ({$inClause})");
            }
        }
        $this->db->prepare("DELETE FROM sim_caso_bienes_muebles WHERE caso_estudio_id = :cid")->execute(['cid' => $casoId]);

        // Delete bien inmueble relation table (FK to sim_caso_bienes_inmuebles.id)
        $bienInmIds = $this->db->prepare("SELECT id FROM sim_caso_bienes_inmuebles WHERE caso_estudio_id = :cid");
        $bienInmIds->execute(['cid' => $casoId]);
        $biIds = $bienInmIds->fetchAll(\PDO::FETCH_COLUMN);
        if (!empty($biIds)) {
            $inClause = implode(',', array_map('intval', $biIds));
            $this->db->exec("DELETE FROM sim_caso_bien_inmueble_tipo_rel WHERE bien_inmueble_id IN ({$inClause})");
        }
        $this->db->prepare("DELETE FROM sim_caso_bienes_inmuebles WHERE caso_estudio_id = :cid")->execute(['cid' => $casoId]);

        // Config y asignaciones
        $stmt = $this->db->prepare("SELECT id FROM sim_caso_configs WHERE caso_id = :cid");
        $stmt->execute(['cid' => $casoId]);
        $configId = $stmt->fetchColumn();
        if ($configId) {
            $this->db->prepare("DELETE FROM sim_caso_asignaciones WHERE config_id = :cid")->execute(['cid' => $configId]);
            $this->db->prepare("DELETE FROM sim_caso_configs WHERE id = :id")->execute(['id' => $configId]);
        }
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
            'rol' => ucfirst(strtolower($h['caracter'] ?? 'Heredero')),
            'parentesco_id' => (int) ($h['parentesco_id'] ?? 0),
            'es_premuerto' => $esPremuerto ? 1 : 0,
            'padre_id' => $padreParticipanteId,
        ]);

        $participanteId = (int) $this->db->lastInsertId();

        // Si es premuerto, insertar acta de defunción
        if ($esPremuerto && !empty($h['fecha_fallecimiento'])) {
            $this->insertActaDefuncion(
                ['numero_acta' => null, 'year_acta' => null, 'parroquia_registro' => null],
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
            'porcentaje' => self::toFloat($b['porcentaje'] ?? 100, 100),
            'descripcion' => $b['descripcion'] ?? null,
            'linderos' => $b['linderos'] ?? null,
            'sup_const' => self::toFloat($b['superficie_construida'] ?? 0),
            'sup_no_const' => self::toFloat($b['superficie_no_construida'] ?? 0),
            'area' => self::toFloat($b['area_superficie'] ?? 0),
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
            'val_original' => self::toFloat($b['valor_original'] ?? 0),
            'val_declarado' => self::toFloat($b['valor_declarado'] ?? 0),
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
            'porcentaje' => self::toFloat($b['porcentaje'] ?? 100, 100),
            'descripcion' => $b['descripcion'] ?? null,
            'valor' => self::toFloat($b['valor_declarado'] ?? 0),
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
            'tdc' => ($pd['numero_tdc'] ?? null) ?: null,
            'pct' => self::toFloat($pd['porcentaje'] ?? 100, 100),
            'desc' => $pd['descripcion'] ?? null,
            'valor' => self::toFloat($pd['valor_declarado'] ?? 0),
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
            'pct' => self::toFloat($pg['porcentaje'] ?? 100, 100),
            'desc' => $pg['descripcion'] ?? null,
            'valor' => self::toFloat($pg['valor_declarado'] ?? 0),
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
            'valor' => self::toFloat($ex['valor_declarado'] ?? 0),
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
            'valor' => self::toFloat($ex['valor_declarado'] ?? 0),
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
     * Busca o crea una empresa por RIF. Realiza UPSERT de razon_social si estaba vacía.
     */
    private function resolveEmpresa(?string $rif, ?string $razonSocial): ?int
    {
        if (empty($rif))
            return null;

        // Normalizar RIF (quitar guiones y espacios, dejar solo letra inicial + numeros)
        // Ejemplo: "J - 12345678 - 9" -> "J123456789"
        $rifClean = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $rif));

        // Buscar empresa con el RIF exacto
        $stmt = $this->db->prepare("SELECT id, razon_social FROM sim_empresas WHERE rif = :rif LIMIT 1");
        $stmt->execute(['rif' => $rifClean]);
        $empresa = $stmt->fetch(PDO::FETCH_ASSOC);

        // Si no encontró y el RIF es solo números, buscar con prefijos comunes
        if (!$empresa && ctype_digit($rifClean)) {
            foreach (['J', 'V', 'E', 'G'] as $prefix) {
                $stmt->execute(['rif' => $prefix . $rifClean]);
                $empresa = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($empresa) {
                    break;
                }
            }
        }

        if ($empresa) {
            $id = (int) $empresa['id'];

            // Si tiene el nombre vacío en BD, pero el frontend mandó uno, lo actualizamos (UPSERT parcial)
            if (empty($empresa['razon_social']) && !empty($razonSocial)) {
                $stmtUpd = $this->db->prepare("UPDATE sim_empresas SET razon_social = :rs WHERE id = :id");
                $stmtUpd->execute(['rs' => $razonSocial, 'id' => $id]);
            }
            return $id;
        }

        // Crear empresa con el RIF normalizado estrictamente
        $stmt = $this->db->prepare("INSERT INTO sim_empresas (rif, razon_social, activo) VALUES (:rif, :rs, 1)");
        $stmt->execute(['rif' => $rifClean, 'rs' => $razonSocial ?? '']);

        return (int) $this->db->lastInsertId();
    }
}
