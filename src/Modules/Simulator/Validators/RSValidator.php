<?php

declare(strict_types=1);

namespace App\Modules\Simulator\Validators;

use App\Core\DB;
use PDO;

/**
 * RSValidator — Validador de RIF Sucesoral
 *
 * Compara los datos del borrador JSON del estudiante
 * (causante, relaciones, direcciones) contra los datos
 * del caso asignado en la base de datos.
 *
 * Retorna un array de errores por sección.
 * Si no hay errores, el estudiante puede generar el R.S.
 */
class RSValidator
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
            $casoId = (int) $intento['caso_id'];

            $errores = [];

            // 2. Validar datos del causante
            $erroresCausante = $this->validarCausante($borrador, $casoId);
            if (!empty($erroresCausante)) {
                $errores['causante'] = $erroresCausante;
            }

            // 3. Validar relaciones (representante + herederos)
            $erroresRelaciones = $this->validarRelaciones($borrador, $casoId);
            if (!empty($erroresRelaciones)) {
                $errores['relaciones'] = $erroresRelaciones;
            }

            // 4. Validar direcciones
            $erroresDirecciones = $this->validarDirecciones($borrador, $casoId);
            if (!empty($erroresDirecciones)) {
                $errores['direcciones'] = $erroresDirecciones;
            }

            return [
                'ok' => empty($errores),
                'errores' => $errores,
            ];
        } catch (\Throwable $e) {
            error_log('RSValidator::validar() error: ' . $e->getMessage());
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
                ce.id AS caso_id, ce.causante_id, ce.representante_id, ce.tipo_sucesion
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
    //  VALIDACIÓN: CAUSANTE
    // ════════════════════════════════════════════════════════

    private function validarCausante(array $borrador, int $casoId): array
    {
        $errores = [];

        $datosBasicos = $borrador['datos_basicos'] ?? null;
        if (!$datosBasicos) {
            return ['No se encontraron datos básicos del causante en el borrador.'];
        }

        // Obtener causante de la DB
        $causanteDb = $this->getCausanteDelCaso($casoId);
        if (!$causanteDb) {
            return ['No se encontró el causante del caso en la base de datos.'];
        }

        // Comparar cédula (DB guarda tipo_cedula y cedula por separado, borrador los une como "V4079920")
        $cedulaBorrador = mb_strtoupper(trim($datosBasicos['cedula'] ?? ''));
        $tipoCedulaDb = mb_strtoupper(trim($causanteDb['tipo_cedula'] ?? ''));
        $cedulaDb = trim($causanteDb['cedula'] ?? '');
        $cedulaCompletaDb = $tipoCedulaDb . $cedulaDb; // Ej: "V" + "4079920" = "V4079920"
        if ($cedulaBorrador !== $cedulaCompletaDb) {
            $errores[] = "La cédula del causante no coincide. Esperado: {$cedulaCompletaDb}, ingresado: {$cedulaBorrador}.";
        }

        // Comparar nombres
        $nombresB = mb_strtoupper(trim($datosBasicos['nombres'] ?? ''));
        $nombresDb = mb_strtoupper(trim($causanteDb['nombres'] ?? ''));
        if ($nombresB !== $nombresDb) {
            $errores[] = "Los nombres del causante no coinciden. Esperado: {$nombresDb}, ingresado: {$nombresB}.";
        }

        // Comparar apellidos
        $apellidosB = mb_strtoupper(trim($datosBasicos['apellidos'] ?? ''));
        $apellidosDb = mb_strtoupper(trim($causanteDb['apellidos'] ?? ''));
        if ($apellidosB !== $apellidosDb) {
            $errores[] = "Los apellidos del causante no coinciden. Esperado: {$apellidosDb}, ingresado: {$apellidosB}.";
        }

        // Comparar fecha de fallecimiento
        $fechaB = $this->normalizarFecha($datosBasicos['fecha_fallecimiento'] ?? '');
        $fechaDb = $this->normalizarFecha($causanteDb['fecha_fallecimiento'] ?? '');
        if ($fechaB !== $fechaDb) {
            $errores[] = "La fecha de fallecimiento no coincide. Esperado: {$fechaDb}, ingresado: {$fechaB}.";
        }

        // Comparar sexo
        $sexoB = mb_strtoupper(trim($datosBasicos['sexo'] ?? ''));
        $sexoDb = mb_strtoupper(trim($causanteDb['sexo'] ?? ''));
        if ($sexoB && $sexoDb && $sexoB !== $sexoDb) {
            $errores[] = "El sexo del causante no coincide. Esperado: {$sexoDb}, ingresado: {$sexoB}.";
        }

        // Comparar estado civil
        $ecB = mb_strtoupper(trim($datosBasicos['estado_civil'] ?? ''));
        $ecDb = mb_strtoupper(trim($causanteDb['estado_civil'] ?? ''));
        if ($ecB && $ecDb && $ecB !== $ecDb) {
            $errores[] = "El estado civil del causante no coincide. Esperado: {$ecDb}, ingresado: {$ecB}.";
        }

        // Comparar nacionalidad
        $nacB = trim((string)($datosBasicos['nacionalidad'] ?? ''));
        $nacDb = trim((string)($causanteDb['nacionalidad'] ?? ''));
        if ($nacB && $nacDb && $nacB !== $nacDb) {
            $errores[] = "La nacionalidad del causante no coincide. Esperado: {$nacDb}, ingresado: {$nacB}.";
        }

        // Comparar domiciliado en el país
        $domB = $datosBasicos['domiciliado_pais'] ?? null;
        $domDb = $causanteDb['domiciliado_pais'] ?? null;
        if ($domB !== null && $domDb !== null && (int)$domB !== (int)$domDb) {
            $esperado = (int)$domDb ? 'Sí' : 'No';
            $ingresado = (int)$domB ? 'Sí' : 'No';
            $errores[] = "Domiciliado en el país no coincide. Esperado: {$esperado}, ingresado: {$ingresado}.";
        }

        // Comparar fecha de cierre fiscal
        $fcB = $this->normalizarFecha($datosBasicos['fecha_cierre_fiscal'] ?? '');
        $fcDb = $this->normalizarFecha($causanteDb['fecha_cierre_fiscal'] ?? '');
        if ($fcB && $fcDb && $fcB !== $fcDb) {
            $errores[] = "La fecha de cierre fiscal no coincide. Esperado: {$fcDb}, ingresado: {$fcB}.";
        }

        // Comparar RIF solo si ambos tienen valor (el estudiante no siempre ingresa RIF)
        $rifDb = trim($causanteDb['rif_personal'] ?? '');
        $rifB = trim($datosBasicos['rif_personal'] ?? '');
        if ($rifDb && $rifB && $rifB !== $rifDb) {
            $errores[] = "El RIF del causante no coincide. Esperado: {$rifDb}, ingresado: {$rifB}.";
        }

        return $errores;
    }

    private function getCausanteDelCaso(int $casoId): ?array
    {
        $sql = "
            SELECT
                p.tipo_cedula, p.cedula, p.pasaporte, p.rif_personal,
                p.nombres, p.apellidos, p.sexo, p.estado_civil,
                p.fecha_nacimiento, p.nacionalidad,
                a.fecha_fallecimiento,
                df.domiciliado_pais, df.fecha_cierre_fiscal
            FROM sim_casos_estudios c
            INNER JOIN sim_personas p ON p.id = c.causante_id
            LEFT JOIN sim_actas_defunciones a ON a.sim_persona_id = p.id
            LEFT JOIN sim_causante_datos_fiscales df ON df.sim_persona_id = p.id
            WHERE c.id = :caso_id
            LIMIT 1
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':caso_id', $casoId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    // ════════════════════════════════════════════════════════
    //  VALIDACIÓN: RELACIONES (representante + herederos)
    // ════════════════════════════════════════════════════════

    private function validarRelaciones(array $borrador, int $casoId): array
    {
        $errores = [];

        $relaciones = $borrador['relaciones'] ?? [];
        if (empty($relaciones)) {
            return ['No se encontraron relaciones en el borrador.'];
        }

        // Obtener participantes de la DB
        $participantesDb = $this->getParticipantesDelCaso($casoId);
        $representanteDb = $this->getRepresentanteDelCaso($casoId);

        // Separar relaciones del borrador por tipo
        $representanteB = null;
        $herederosB = [];
        foreach ($relaciones as $rel) {
            if (($rel['parentesco'] ?? '') === '50') {
                $representanteB = $rel;
            } else {
                $herederosB[] = $rel;
            }
        }

        // ── Validar representante ──
        if ($representanteDb && !$representanteB) {
            $errores[] = 'Falta el Representante de la Sucesión en las relaciones.';
        } elseif (!$representanteDb && $representanteB) {
            $errores[] = 'Se ingresó un Representante de la Sucesión pero el caso no tiene uno.';
        } elseif ($representanteDb && $representanteB) {
            // Verificar que el documento del representante coincida
            $docRepB = trim($representanteB['idDocumento'] ?? $representanteB['cedula'] ?? '');
            if ($docRepB && !$this->matchDocumento($docRepB, $representanteDb)) {
                $esperado = $representanteDb['rif_personal'] ?: ($representanteDb['tipo_cedula'] . $representanteDb['cedula']);
                $errores[] = "Representante: documento no coincide. Esperado: {$esperado}, ingresado: {$docRepB}.";
            }
            // Comparar nombres
            $repErrors = $this->compararPersona($representanteB, $representanteDb, 'Representante');
            $errores = array_merge($errores, $repErrors);
        }

        // ── Validar herederos ──
        // Contar herederos esperados vs ingresados
        $herederoCount = count($herederosB);
        $expectedCount = count($participantesDb);
        if ($herederoCount !== $expectedCount) {
            $errores[] = "Se esperan {$expectedCount} heredero(s) pero se ingresaron {$herederoCount}.";
        }

        // Verificar que cada heredero del borrador tenga match en la DB
        $dbUsados = [];
        foreach ($herederosB as $i => $hB) {
            $pos = $i + 1;
            $docB = trim($hB['idDocumento'] ?? $hB['cedula'] ?? '');
            $matched = false;

            foreach ($participantesDb as $j => $hDb) {
                if (in_array($j, $dbUsados)) continue;

                if ($this->matchDocumento($docB, $hDb)) {
                    $matched = true;
                    $dbUsados[] = $j;

                    // Comparar nombres
                    $nombresErrors = $this->compararPersona($hB, $hDb, "Heredero #{$pos}");
                    $errores = array_merge($errores, $nombresErrors);
                    break;
                }
            }

            if (!$matched) {
                $errores[] = "Heredero #{$pos} (documento: {$docB}) no se encontró en el caso asignado.";
            }
        }

        // Verificar herederos de la DB que no fueron ingresados
        foreach ($participantesDb as $j => $hDb) {
            if (!in_array($j, $dbUsados)) {
                $nombre = trim(($hDb['nombres'] ?? '') . ' ' . ($hDb['apellidos'] ?? ''));
                $doc = $hDb['rif_personal'] ?: ($hDb['tipo_cedula'] . $hDb['cedula']);
                $errores[] = "Falta el heredero: {$nombre} (documento: {$doc}).";
            }
        }

        return $errores;
    }

    private function getParticipantesDelCaso(int $casoId): array
    {
        $sql = "
            SELECT
                p.tipo_cedula, p.cedula, p.pasaporte, p.rif_personal,
                p.nombres, p.apellidos,
                cp.rol_en_caso, cp.parentesco_id
            FROM sim_caso_participantes cp
            INNER JOIN sim_personas p ON p.id = cp.persona_id
            WHERE cp.caso_estudio_id = :caso_id
              AND cp.rol_en_caso = 'Heredero'
              AND (cp.premuerto_padre_id IS NULL OR cp.premuerto_padre_id = 0)
            ORDER BY cp.id ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':caso_id', $casoId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getRepresentanteDelCaso(int $casoId): ?array
    {
        $sql = "
            SELECT
                p.tipo_cedula, p.cedula, p.pasaporte, p.rif_personal,
                p.nombres, p.apellidos
            FROM sim_casos_estudios c
            INNER JOIN sim_personas p ON p.id = c.representante_id
            WHERE c.id = :caso_id
              AND c.representante_id IS NOT NULL
            LIMIT 1
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':caso_id', $casoId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    // ════════════════════════════════════════════════════════
    //  VALIDACIÓN: DIRECCIONES
    // ════════════════════════════════════════════════════════

    private function validarDirecciones(array $borrador, int $casoId): array
    {
        $errores = [];

        $direcciones = $borrador['direcciones'] ?? [];
        if (empty($direcciones)) {
            return ['No se encontraron direcciones en el borrador.'];
        }

        // Obtener direcciones de la DB
        $direccionesDb = $this->getDireccionesDelCausante($casoId);

        $countB = count($direcciones);
        $countDb = count($direccionesDb);
        if ($countB !== $countDb) {
            $errores[] = "Se esperan {$countDb} dirección(es) pero se ingresaron {$countB}.";
        }

        // ── Mapas de traducción: código del simulador → enum de la DB ──
        $mapTipoDireccion = [
            '01' => 'Casa_Matriz_Establecimiento_Principal',
            '02' => 'Sucursal_Comercial',
            '03' => 'Bodega_Almacenamiento_Deposito',
            '04' => 'Negocio_Independiente',
            '05' => 'Planta_Industrial_Fabrica',
            '06' => 'Domicilio_Fiscal',
            '91' => 'Direccion_Notificacion_Fisica',
        ];
        $mapTipoVialidad = [
            '01' => 'Calle', '02' => 'Avenida', '03' => 'Vereda',
            '04' => 'Carretera', '05' => 'Esquina', '06' => 'Carrera',
        ];
        $mapTipoInmueble = [
            '01' => 'Edificio', '02' => 'Centro_Comercial', '03' => 'Quinta',
            '04' => 'Casa', '05' => 'Local',
        ];
        $mapTipoSector = [
            '01' => 'Urbanizacion', '02' => 'Zona', '03' => 'Sector',
            '04' => 'Conjunto_Residencial', '05' => 'Barrio', '06' => 'Caserio',
        ];
        $mapTipoLocal = [
            '01' => 'Apartamento', '02' => 'Local', '03' => 'Oficina',
        ];

        // Comparar cada dirección del borrador contra la DB
        $dbUsados = [];
        foreach ($direcciones as $i => $dirB) {
            $pos = $i + 1;
            $matched = false;

            foreach ($direccionesDb as $j => $dirDb) {
                if (in_array($j, $dbUsados)) continue;

                // Match por estado + municipio + parroquia (usando IDs)
                $estadoMatch = (int)($dirB['estado'] ?? 0) === (int)($dirDb['estado_id'] ?? 0);
                $municipioMatch = (int)($dirB['municipio'] ?? 0) === (int)($dirDb['municipio_id'] ?? 0);
                $parroquiaMatch = (int)($dirB['parroquia'] ?? 0) === (int)($dirDb['parroquia_id'] ?? 0);

                if ($estadoMatch && $municipioMatch && $parroquiaMatch) {
                    $matched = true;
                    $dbUsados[] = $j;

                    // ── Tipo de dirección (código → enum) ──
                    $tipoDirB = $mapTipoDireccion[$dirB['tipoDireccion'] ?? ''] ?? ($dirB['tipoDireccion'] ?? '');
                    $tipoDirDb = trim((string)($dirDb['tipo_direccion'] ?? ''));
                    if ($tipoDirB !== '' && $tipoDirDb !== '' && mb_strtoupper($tipoDirB) !== mb_strtoupper($tipoDirDb)) {
                        $errores[] = "Dirección #{$pos}: Tipo de dirección no coincide. Esperado: '{$tipoDirDb}', ingresado: '{$tipoDirB}'.";
                    }

                    // ── Tipo de vialidad (código → enum) ──
                    $tipoVialB = $mapTipoVialidad[$dirB['tipoVialidad'] ?? ''] ?? ($dirB['tipoVialidad'] ?? '');
                    $tipoVialDb = trim((string)($dirDb['tipo_vialidad'] ?? ''));
                    if ($tipoVialB !== '' && $tipoVialDb !== '' && mb_strtoupper($tipoVialB) !== mb_strtoupper($tipoVialDb)) {
                        $errores[] = "Dirección #{$pos}: Tipo de vialidad no coincide. Esperado: '{$tipoVialDb}', ingresado: '{$tipoVialB}'.";
                    }

                    // ── Nombre de vialidad (borrador puede incluir número extra) ──
                    $vialidadB = mb_strtoupper(trim((string)($dirB['vialidad'] ?? '')));
                    $vialidadDb = mb_strtoupper(trim((string)($dirDb['nombre_vialidad'] ?? '')));
                    if ($vialidadB !== '' && $vialidadDb !== '' && mb_strpos($vialidadB, $vialidadDb) === false && $vialidadB !== $vialidadDb) {
                        $errores[] = "Dirección #{$pos}: Nombre de vialidad no coincide. Esperado: '{$vialidadDb}', ingresado: '{$vialidadB}'.";
                    }

                    // ── Tipo de inmueble (código → enum) ──
                    $tipoInmB = $mapTipoInmueble[$dirB['tipoEdificacion'] ?? ''] ?? ($dirB['tipoEdificacion'] ?? '');
                    $tipoInmDb = trim((string)($dirDb['tipo_inmueble'] ?? ''));
                    if ($tipoInmB !== '' && $tipoInmDb !== '' && mb_strtoupper($tipoInmB) !== mb_strtoupper($tipoInmDb)) {
                        $errores[] = "Dirección #{$pos}: Tipo de inmueble no coincide. Esperado: '{$tipoInmDb}', ingresado: '{$tipoInmB}'.";
                    }

                    // ── Nombre/Nro de inmueble ──
                    // La DB concatena: "NOMBRE - PISO X" o "NOMBRE - NIVEL X" o "NOMBRE - NRO X"
                    // El borrador guarda por separado: edificacion (nombre) y piso (nro)
                    $nroInmDb = trim((string)($dirDb['nro_inmueble'] ?? ''));
                    $edificacionB = mb_strtoupper(trim((string)($dirB['edificacion'] ?? '')));
                    $pisoB = mb_strtoupper(trim((string)($dirB['piso'] ?? '')));

                    // Descomponer nro_inmueble de la DB: "NOMBRE - PISO/NIVEL/NRO X"
                    $dbNombreInmueble = mb_strtoupper($nroInmDb);
                    $dbPisoNivel = '';
                    if (preg_match('/^(.+?)\s*-\s*(PISO|NIVEL|NRO)\s+(.+)$/i', $nroInmDb, $m)) {
                        $dbNombreInmueble = mb_strtoupper(trim($m[1]));
                        $dbPisoNivel = mb_strtoupper(trim($m[3]));
                    }

                    if ($edificacionB !== '' && $dbNombreInmueble !== '' && $edificacionB !== $dbNombreInmueble) {
                        $errores[] = "Dirección #{$pos}: Nombre de inmueble no coincide. Esperado: '{$dbNombreInmueble}', ingresado: '{$edificacionB}'.";
                    }
                    if ($pisoB !== '' && $dbPisoNivel !== '' && $pisoB !== $dbPisoNivel) {
                        $errores[] = "Dirección #{$pos}: Piso/Nivel no coincide. Esperado: '{$dbPisoNivel}', ingresado: '{$pisoB}'.";
                    }

                    // ── Tipo de local/nivel (código → enum) ──
                    $tipoLocalB = $mapTipoLocal[$dirB['tipoLocal'] ?? ''] ?? ($dirB['tipoLocal'] ?? '');
                    $tipoNivelDb = trim((string)($dirDb['tipo_nivel'] ?? ''));
                    if ($tipoLocalB !== '' && $tipoNivelDb !== '' && mb_strtoupper($tipoLocalB) !== mb_strtoupper($tipoNivelDb)) {
                        $errores[] = "Dirección #{$pos}: Tipo de local no coincide. Esperado: '{$tipoNivelDb}', ingresado: '{$tipoLocalB}'.";
                    }

                    // ── Nro de local/apto (borrador: local → DB: nro_nivel) ──
                    $localB = mb_strtoupper(trim((string)($dirB['local'] ?? '')));
                    $nroNivelDb = mb_strtoupper(trim((string)($dirDb['nro_nivel'] ?? '')));
                    if ($localB !== '' && $nroNivelDb !== '' && $localB !== $nroNivelDb) {
                        $errores[] = "Dirección #{$pos}: Nro. de local no coincide. Esperado: '{$nroNivelDb}', ingresado: '{$localB}'.";
                    }

                    // ── Tipo de sector (código → enum) ──
                    $tipoSecB = $mapTipoSector[$dirB['tipoSector'] ?? ''] ?? ($dirB['tipoSector'] ?? '');
                    $tipoSecDb = trim((string)($dirDb['tipo_sector'] ?? ''));
                    if ($tipoSecB !== '' && $tipoSecDb !== '' && mb_strtoupper($tipoSecB) !== mb_strtoupper($tipoSecDb)) {
                        $errores[] = "Dirección #{$pos}: Tipo de sector no coincide. Esperado: '{$tipoSecDb}', ingresado: '{$tipoSecB}'.";
                    }

                    // ── Nombre de sector ──
                    $sectorB = mb_strtoupper(trim((string)($dirB['sector'] ?? '')));
                    $sectorDb = mb_strtoupper(trim((string)($dirDb['nombre_sector'] ?? '')));
                    if ($sectorB !== '' && $sectorDb !== '' && $sectorB !== $sectorDb) {
                        $errores[] = "Dirección #{$pos}: Nombre de sector no coincide. Esperado: '{$sectorDb}', ingresado: '{$sectorB}'.";
                    }

                    // ── Ciudad (mostrar nombre, no ID) ──
                    $ciudadB = (int)($dirB['ciudad'] ?? 0);
                    $ciudadDb = (int)($dirDb['ciudad_id'] ?? 0);
                    if ($ciudadB > 0 && $ciudadDb > 0 && $ciudadB !== $ciudadDb) {
                        $ciudadNombreDb = $dirDb['ciudad_nombre'] ?? $ciudadDb;
                        $ciudadNombreB = $this->resolverNombre('ciudades', $ciudadB) ?? $ciudadB;
                        $errores[] = "Dirección #{$pos}: Ciudad no coincide. Esperado: '{$ciudadNombreDb}', ingresado: '{$ciudadNombreB}'.";
                    }

                    // ── Zona Postal (mostrar código, no ID) ──
                    $zonaB = (int)($dirB['zonaPostal'] ?? 0);
                    $zonaDb = (int)($dirDb['codigo_postal_id'] ?? 0);
                    if ($zonaB > 0 && $zonaDb > 0 && $zonaB !== $zonaDb) {
                        $zonaNombreDb = $dirDb['codigo_postal_codigo'] ?? $zonaDb;
                        $zonaNombreB = $this->resolverNombre('codigos_postales', $zonaB, 'codigo') ?? $zonaB;
                        $errores[] = "Dirección #{$pos}: Zona postal no coincide. Esperado: '{$zonaNombreDb}', ingresado: '{$zonaNombreB}'.";
                    }

                    // ── Teléfono fijo ──
                    $telFijoB = trim((string)($dirB['telefono'] ?? ''));
                    $telFijoDb = trim((string)($dirDb['telefono_fijo'] ?? ''));
                    if ($telFijoDb !== '' && $telFijoB === '') {
                        $errores[] = "Dirección #{$pos}: Falta teléfono fijo. Esperado: '{$telFijoDb}'.";
                    } elseif ($telFijoB !== '' && $telFijoDb === '') {
                        $errores[] = "Dirección #{$pos}: Se ingresó teléfono fijo '{$telFijoB}' pero no se espera uno.";
                    } elseif ($telFijoB !== '' && $telFijoDb !== '' && $telFijoB !== $telFijoDb) {
                        $errores[] = "Dirección #{$pos}: Teléfono fijo no coincide. Esperado: '{$telFijoDb}', ingresado: '{$telFijoB}'.";
                    }

                    // ── Teléfono celular ──
                    $celularB = trim((string)($dirB['celular'] ?? ''));
                    $celularDb = trim((string)($dirDb['telefono_celular'] ?? ''));
                    if ($celularDb !== '' && $celularB === '') {
                        $errores[] = "Dirección #{$pos}: Falta teléfono celular. Esperado: '{$celularDb}'.";
                    } elseif ($celularB !== '' && $celularDb === '') {
                        $errores[] = "Dirección #{$pos}: Se ingresó teléfono celular '{$celularB}' pero no se espera uno.";
                    } elseif ($celularB !== '' && $celularDb !== '' && $celularB !== $celularDb) {
                        $errores[] = "Dirección #{$pos}: Teléfono celular no coincide. Esperado: '{$celularDb}', ingresado: '{$celularB}'.";
                    }

                    // ── Fax ──
                    $faxB = trim((string)($dirB['fax'] ?? ''));
                    $faxDb = trim((string)($dirDb['fax'] ?? ''));
                    if ($faxDb !== '' && $faxB === '') {
                        $errores[] = "Dirección #{$pos}: Falta fax. Esperado: '{$faxDb}'.";
                    } elseif ($faxB !== '' && $faxDb === '') {
                        $errores[] = "Dirección #{$pos}: Se ingresó fax '{$faxB}' pero no se espera uno.";
                    } elseif ($faxB !== '' && $faxDb !== '' && $faxB !== $faxDb) {
                        $errores[] = "Dirección #{$pos}: Fax no coincide. Esperado: '{$faxDb}', ingresado: '{$faxB}'.";
                    }

                    // ── Punto de referencia ──
                    $refB = mb_strtoupper(trim((string)($dirB['referencia'] ?? '')));
                    $refDb = mb_strtoupper(trim((string)($dirDb['punto_referencia'] ?? '')));
                    if ($refB !== '' && $refDb !== '' && $refB !== $refDb) {
                        $errores[] = "Dirección #{$pos}: Punto de referencia no coincide. Esperado: '{$refDb}', ingresado: '{$refB}'.";
                    }

                    break;
                }
            }

            if (!$matched) {
                $errores[] = "Dirección #{$pos} no se encontró en el caso asignado.";
            }
        }

        return $errores;
    }

    private function getDireccionesDelCausante(int $casoId): array
    {
        $sql = "
            SELECT
                d.tipo_direccion, d.tipo_vialidad, d.nombre_vialidad,
                d.tipo_inmueble, d.nro_inmueble, d.tipo_nivel, d.nro_nivel,
                d.tipo_sector, d.nombre_sector,
                d.estado_id, d.municipio_id, d.parroquia_id, d.ciudad_id,
                d.codigo_postal_id, d.telefono_fijo, d.telefono_celular,
                d.fax, d.punto_referencia,
                e.nombre  AS estado_nombre,
                m.nombre  AS municipio_nombre,
                p.nombre  AS parroquia_nombre,
                ci.nombre AS ciudad_nombre,
                cp.codigo AS codigo_postal_codigo
            FROM sim_caso_direcciones d
            LEFT JOIN estados          e  ON d.estado_id         = e.id
            LEFT JOIN municipios       m  ON d.municipio_id      = m.id
            LEFT JOIN parroquias       p  ON d.parroquia_id      = p.id
            LEFT JOIN ciudades         ci ON d.ciudad_id         = ci.id
            LEFT JOIN codigos_postales cp ON d.codigo_postal_id  = cp.id
            WHERE d.sim_caso_estudio_id = :caso_id
            ORDER BY d.id ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':caso_id', $casoId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Resuelve el nombre legible de un ID de catálogo.
     */
    private function resolverNombre(string $tabla, int $id, string $campo = 'nombre'): ?string
    {
        try {
            $tablasPermitidas = ['estados', 'municipios', 'parroquias', 'ciudades', 'codigos_postales'];
            if (!in_array($tabla, $tablasPermitidas)) return null;
            $stmt = $this->db->prepare("SELECT {$campo} FROM {$tabla} WHERE id = :id LIMIT 1");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchColumn();
            return $result !== false ? (string)$result : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    // ════════════════════════════════════════════════════════
    //  UTILIDADES
    // ════════════════════════════════════════════════════════

    /**
     * Compara nombres, apellidos y documento de una relación del borrador
     * contra los datos de la DB.
     */
    private function compararPersona(array $borradorRel, array $dbPersona, string $label): array
    {
        $errores = [];

        $nombresB = mb_strtoupper(trim($borradorRel['nombre'] ?? $borradorRel['nombres'] ?? ''));
        $nombresDb = mb_strtoupper(trim($dbPersona['nombres'] ?? ''));
        if ($nombresB !== $nombresDb) {
            $errores[] = "{$label}: nombres no coinciden. Esperado: {$nombresDb}, ingresado: {$nombresB}.";
        }

        $apellidosB = mb_strtoupper(trim($borradorRel['apellido'] ?? $borradorRel['apellidos'] ?? ''));
        $apellidosDb = mb_strtoupper(trim($dbPersona['apellidos'] ?? ''));
        if ($apellidosB !== $apellidosDb) {
            $errores[] = "{$label}: apellidos no coinciden. Esperado: {$apellidosDb}, ingresado: {$apellidosB}.";
        }

        // Comparar documento (cédula/RIF) usando matchDocumento
        $docB = trim($borradorRel['idDocumento'] ?? $borradorRel['cedula'] ?? '');
        if ($docB && !$this->matchDocumento($docB, $dbPersona)) {
            $rifDb = mb_strtoupper(trim($dbPersona['rif_personal'] ?? ''));
            $tipoCedulaDb = mb_strtoupper(trim($dbPersona['tipo_cedula'] ?? ''));
            $cedulaDb = trim($dbPersona['cedula'] ?? '');
            $esperado = $rifDb ?: ($tipoCedulaDb . $cedulaDb) ?: $cedulaDb;
            $errores[] = "{$label}: documento no coincide. Esperado: {$esperado}, ingresado: {$docB}.";
        }

        return $errores;
    }

    /**
     * Extrae solo la parte numérica de un documento.
     * Ej: "V-42240148-0" → "422401480", "V42240148" → "42240148", "4224014" → "4224014"
     */
    private function limpiarDocumento(string $doc): string
    {
        return preg_replace('/[^0-9]/', '', $doc);
    }

    /**
     * Intenta hacer match de un documento del borrador contra TODOS los
     * identificadores de una persona en la DB (cédula, RIF, pasaporte).
     *
     * Estrategia:
     * 1. Comparación exacta (case-insensitive) contra cédula completa, RIF y pasaporte
     * 2. Comparación numérica pura (sin letras ni guiones) contra cada campo
     */
    private function matchDocumento(string $docBorrador, array $personaDb): bool
    {
        $docB = mb_strtoupper(trim($docBorrador));
        if (!$docB) return false;

        // Construir todos los identificadores de la DB
        $tipoCedula = mb_strtoupper(trim($personaDb['tipo_cedula'] ?? ''));
        $cedula     = trim($personaDb['cedula'] ?? '');
        $rif        = mb_strtoupper(trim($personaDb['rif_personal'] ?? ''));
        $pasaporte  = mb_strtoupper(trim($personaDb['pasaporte'] ?? ''));
        $cedulaCompleta = $tipoCedula . $cedula; // Ej: "V4224014"

        // ── Paso 1: Comparación exacta (case-insensitive) ──
        if ($cedulaCompleta && $docB === $cedulaCompleta) return true;
        if ($rif && $docB === $rif) return true;
        if ($pasaporte && $docB === $pasaporte) return true;
        if ($cedula && $docB === $cedula) return true;

        // ── Paso 2: Comparación por número puro ──
        // Extraer solo dígitos de ambos lados
        $numB = $this->limpiarDocumento($docB);
        if (!$numB) return false;

        // Match contra número de cédula
        if ($cedula && $numB === $this->limpiarDocumento($cedula)) return true;
        // Match contra número de RIF
        if ($rif && $numB === $this->limpiarDocumento($rif)) return true;
        // Match contra pasaporte (puede tener letras, comparar limpio)
        if ($pasaporte && $numB === $this->limpiarDocumento($pasaporte)) return true;

        return false;
    }

    /**
     * Normaliza fechas DD/MM/YYYY o YYYY-MM-DD a formato YYYY-MM-DD.
     */
    private function normalizarFecha(string $fecha): string
    {
        $fecha = trim($fecha);
        if (!$fecha) return '';

        // DD/MM/YYYY → YYYY-MM-DD
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $fecha, $m)) {
            return "{$m[3]}-{$m[2]}-{$m[1]}";
        }

        // Ya es YYYY-MM-DD (tomar solo los primeros 10 chars por si hay hora)
        return substr($fecha, 0, 10);
    }
}
