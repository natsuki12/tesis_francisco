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

        // Comparar cédula
        $cedulaBorrador = trim($datosBasicos['cedula'] ?? '');
        $cedulaDb = trim($causanteDb['cedula'] ?? '');
        if ($cedulaBorrador !== $cedulaDb) {
            $errores[] = "La cédula del causante no coincide. Esperado: {$cedulaDb}, ingresado: {$cedulaBorrador}.";
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

        // Comparar RIF si existe en la DB
        $rifDb = trim($causanteDb['rif_personal'] ?? '');
        $rifB = trim($datosBasicos['rif_personal'] ?? '');
        if ($rifDb && $rifB !== $rifDb) {
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
                a.fecha_fallecimiento
            FROM sim_casos_estudios c
            INNER JOIN sim_personas p ON p.id = c.causante_id
            LEFT JOIN sim_actas_defunciones a ON a.sim_persona_id = p.id
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

                $cedulaDb = trim($hDb['cedula'] ?? '');
                $rifDb = trim($hDb['rif_personal'] ?? '');
                $pasaporteDb = trim($hDb['pasaporte'] ?? '');

                // Match por cédula, RIF o pasaporte
                if (
                    ($cedulaDb && $this->documentoContiene($docB, $cedulaDb)) ||
                    ($rifDb && $docB === $rifDb) ||
                    ($pasaporteDb && $docB === $pasaporteDb)
                ) {
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
                $errores[] = "Falta el heredero: {$nombre} (cédula: {$hDb['cedula']}).";
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

        // Mapeo: clave del borrador JS (camelCase) → columna de la DB (snake_case)
        // El borrador usa: tipoDireccion, tipoVialidad, vialidad, tipoEdificacion, edificacion,
        //                  estado, municipio, parroquia, ciudad
        // La DB usa:       tipo_direccion, tipo_vialidad, nombre_vialidad, tipo_inmueble, nro_inmueble,
        //                  estado_id, municipio_id, parroquia_id, ciudad_id

        // Comparar cada dirección del borrador contra la DB
        $dbUsados = [];
        foreach ($direcciones as $i => $dirB) {
            $pos = $i + 1;
            $matched = false;

            foreach ($direccionesDb as $j => $dirDb) {
                if (in_array($j, $dbUsados)) continue;

                // Match por estado + municipio + parroquia (usando nombres del borrador)
                $estadoMatch = (int)($dirB['estado'] ?? 0) === (int)($dirDb['estado_id'] ?? 0);
                $municipioMatch = (int)($dirB['municipio'] ?? 0) === (int)($dirDb['municipio_id'] ?? 0);
                $parroquiaMatch = (int)($dirB['parroquia'] ?? 0) === (int)($dirDb['parroquia_id'] ?? 0);

                if ($estadoMatch && $municipioMatch && $parroquiaMatch) {
                    $matched = true;
                    $dbUsados[] = $j;

                    // Comparar campos individualmente (borrador key → DB column → label)
                    $campos = [
                        ['tipoDireccion', 'tipo_direccion', 'Tipo de dirección'],
                        ['tipoVialidad', 'tipo_vialidad', 'Tipo de vialidad'],
                        ['vialidad', 'nombre_vialidad', 'Nombre de vialidad'],
                        ['tipoEdificacion', 'tipo_inmueble', 'Tipo de inmueble'],
                        ['edificacion', 'nro_inmueble', 'Nro. de inmueble'],
                        ['ciudad', 'ciudad_id', 'Ciudad'],
                    ];

                    foreach ($campos as [$keyB, $keyDb, $label]) {
                        $valB = trim((string)($dirB[$keyB] ?? ''));
                        $valDb = trim((string)($dirDb[$keyDb] ?? ''));
                        if ($valB !== $valDb) {
                            $errores[] = "Dirección #{$pos}: {$label} no coincide. Esperado: '{$valDb}', ingresado: '{$valB}'.";
                        }
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
                d.fax, d.punto_referencia
            FROM sim_caso_direcciones d
            WHERE d.sim_caso_estudio_id = :caso_id
              AND d.deleted_at IS NULL
            ORDER BY d.id ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':caso_id', $casoId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ════════════════════════════════════════════════════════
    //  UTILIDADES
    // ════════════════════════════════════════════════════════

    /**
     * Compara nombres y apellidos de una relación del borrador
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

        return $errores;
    }

    /**
     * Verifica si un documento del borrador contiene la cédula de la DB.
     * Ejemplo: "V12345678" contiene "12345678".
     */
    private function documentoContiene(string $documento, string $cedula): bool
    {
        if ($documento === $cedula) return true;
        // Remover prefijo de letra (V, E, J, G)
        $docLimpio = preg_replace('/^[VEJGP]-?/', '', $documento);
        return $docLimpio === $cedula;
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
