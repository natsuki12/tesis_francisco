<?php
declare(strict_types=1);

namespace App\Modules\Professor\Validators;

use App\Core\DB;
use PDO;

/**
 * Validador server-side para el payload de Crear/Editar Caso Sucesoral.
 * Espeja las reglas del frontend (modal.js, prorroga.js, direccion.js).
 *
 * Modos:
 *   - 'Borrador': solo requiere titulo
 *   - 'Publicar': validación completa de todas las secciones
 */
class CasoValidator
{
    private array $errors = [];

    /**
     * Campos que deben ser numéricos (int o float).
     * Formato: 'ruta.al.campo' para campos simples,
     * o se manejan en lógica especial para arrays.
     */
    private const NUMERIC_FIELDS = [
        'datos_fiscales_causante.domiciliado_pais',
    ];

    /**
     * Campos numéricos dentro de arrays (herederos, bienes, etc.)
     */
    private const NUMERIC_ARRAY_FIELDS = [
        'bienes_inmuebles' => ['valor_declarado', 'valor_original', 'porcentaje', 'superficie_construida', 'superficie_no_construida', 'area_superficie'],
        'pasivos_deuda' => ['valor_declarado', 'porcentaje'],
        'pasivos_gastos' => ['valor_declarado'],
        'exenciones' => ['valor_declarado'],
        'exoneraciones' => ['valor_declarado'],
    ];

    // ====================================================================
    // Sanitización del payload
    // ====================================================================

    /**
     * Sanitiza todo el payload: escapa strings contra XSS y coerce tipos.
     * Debe llamarse ANTES de validate() y store().
     * @return array Copia sanitizada del payload
     */
    public static function sanitize(array $data): array
    {
        // 1. Escapar todos los strings recursivamente
        $data = self::escapeStrings($data);

        // 2. Coercer campos numéricos simples
        foreach (self::NUMERIC_FIELDS as $path) {
            $keys = explode('.', $path);
            $data = self::coerceNumericField($data, $keys);
        }

        // 3. Coercer campos numéricos en arrays
        foreach (self::NUMERIC_ARRAY_FIELDS as $section => $fields) {
            if (!isset($data[$section]) || !is_array($data[$section]))
                continue;
            foreach ($data[$section] as $i => $item) {
                if (!is_array($item))
                    continue;
                foreach ($fields as $f) {
                    if (isset($data[$section][$i][$f])) {
                        $data[$section][$i][$f] = self::toNumeric($data[$section][$i][$f]);
                    }
                }
            }
        }

        // 4. Coercer campos numéricos en bienes_muebles (keyed by catId)
        if (isset($data['bienes_muebles']) && is_array($data['bienes_muebles'])) {
            foreach ($data['bienes_muebles'] as $catId => $items) {
                if (!is_array($items))
                    continue;
                foreach ($items as $i => $b) {
                    if (isset($b['valor_declarado'])) {
                        $data['bienes_muebles'][$catId][$i]['valor_declarado'] = self::toNumeric($b['valor_declarado']);
                    }
                }
            }
        }

        return $data;
    }

    /**
     * Escapa recursivamente todos los valores string del array.
     */
    private static function escapeStrings(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $data[$key] = htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            } elseif (is_array($value)) {
                $data[$key] = self::escapeStrings($value);
            }
            // int, float, bool, null → se dejan intactos
        }
        return $data;
    }

    /**
     * Coerce un valor a numérico (int si es entero, float si tiene decimales).
     * @return int|float|string Retorna el valor original si no es numérico.
     */
    private static function toNumeric(mixed $value): int|float|string
    {
        if (is_int($value) || is_float($value))
            return $value;
        if (!is_string($value) || $value === '')
            return $value;

        // Already a standard numeric string (e.g. "123.45", "-0.5")
        if (is_numeric($value)) {
            return str_contains($value, '.') ? (float) $value : (int) $value;
        }

        // Venezuelan format: dots as thousands, comma as decimal (e.g. "-1.234.567,89")
        // Detect: contains comma, possibly dots before it
        if (str_contains($value, ',')) {
            $clean = str_replace('.', '', $value); // Remove thousands dots
            $clean = str_replace(',', '.', $clean); // Comma → dot for decimal
            if (is_numeric($clean)) {
                return (float) $clean;
            }
        }

        return $value; // Leave as-is if not convertible (the validator will catch it)
    }

    /**
     * Coerce un campo nested a numérico dado un path de keys.
     */
    private static function coerceNumericField(array $data, array $keys): array
    {
        $ref = &$data;
        $lastKey = array_pop($keys);
        foreach ($keys as $k) {
            if (!isset($ref[$k]) || !is_array($ref[$k]))
                return $data;
            $ref = &$ref[$k];
        }
        if (isset($ref[$lastKey])) {
            $ref[$lastKey] = self::toNumeric($ref[$lastKey]);
        }
        return $data;
    }

    /**
     * Valida el payload completo.
     * @return string[] Array de mensajes de error (vacío = válido)
     */
    public function validate(array $data, string $modo = 'Borrador', int $profesorId = 0, ?int $casoId = null): array
    {
        $this->errors = [];

        // ── Siempre requerido ──
        $this->validateCaso($data['caso'] ?? [], $modo, $profesorId, $casoId);

        if ($modo === 'Publicar') {
            $this->validateCausante($data['causante'] ?? [], $data['caso']['tipo_sucesion'] ?? '');
            $this->validateDatosFiscales(
                $data['datos_fiscales_causante'] ?? [],
                $data['causante']['fecha_fallecimiento'] ?? ''
            );
            $this->validateActaDefuncion($data['acta_defuncion'] ?? [], $data['caso']['tipo_sucesion'] ?? '', $data['causante']['fecha_fallecimiento'] ?? '');
            $this->validateDirecciones($data['direcciones_causante'] ?? []);
            $this->validateRepresentante($data['representante'] ?? []);
            $this->validateHerencia($data['herencia'] ?? [], $data['causante']['fecha_fallecimiento'] ?? '');
            $this->validateHerederos($data['herederos'] ?? []);
            $this->validateHerederosPremuertos($data['herederos_premuertos'] ?? []);
            $this->validateBienesInmuebles($data['bienes_inmuebles'] ?? []);
            $this->validateBienesMuebles($data['bienes_muebles'] ?? []);
            $this->validatePasivosDeuda($data['pasivos_deuda'] ?? []);
            $this->validatePasivosGastos($data['pasivos_gastos'] ?? []);
            $this->validateExenciones($data['exenciones'] ?? []);
            $this->validateExoneraciones($data['exoneraciones'] ?? []);
            $this->validateProrrogas($data['prorrogas'] ?? []);


            // ── Checks de existencia mínima ──
            if (empty($data['herederos'])) {
                $this->errors[] = 'Debe agregar al menos un heredero.';
            }

            $tieneInmuebles = !empty($data['bienes_inmuebles']);
            $tieneMuebles = false;
            foreach (($data['bienes_muebles'] ?? []) as $items) {
                if (is_array($items) && count($items) > 0) {
                    $tieneMuebles = true;
                    break;
                }
            }
            if (!$tieneInmuebles && !$tieneMuebles) {
                $this->errors[] = 'Debe agregar al menos un bien (inmueble o mueble).';
            }

            // ── #18/#19/#34 Cédulas cruzadas ──
            $cedulaCausante = trim($data['causante']['cedula'] ?? '');
            $cedulaRep = trim($data['representante']['cedula'] ?? '');
            if ($cedulaCausante && $cedulaRep && $cedulaCausante === $cedulaRep) {
                $this->errors[] = 'La cédula del representante no puede ser igual a la del causante.';
            }
            // Recopilar todas las cédulas de herederos + premuertos para cruce
            $todasCedulasHerederos = [];
            foreach (($data['herederos'] ?? []) as $i => $h) {
                $cedulaH = trim($h['cedula'] ?? '');
                if ($cedulaCausante && $cedulaH && $cedulaH === $cedulaCausante) {
                    $this->errors[] = 'Heredero #' . ($i + 1) . ': La cédula no puede ser igual a la del causante.';
                }
                if ($cedulaH)
                    $todasCedulasHerederos[] = $cedulaH;
            }
            // Verificar premuertos contra herederos y causante
            foreach (($data['herederos_premuertos'] ?? []) as $i => $hp) {
                $cedulaHP = trim($hp['cedula'] ?? '');
                if ($cedulaCausante && $cedulaHP && $cedulaHP === $cedulaCausante) {
                    $this->errors[] = 'Heredero premuerto #' . ($i + 1) . ': La cédula no puede ser igual a la del causante.';
                }
                if ($cedulaHP && in_array($cedulaHP, $todasCedulasHerederos)) {
                    $this->errors[] = 'Heredero premuerto #' . ($i + 1) . ': La cédula está duplicada con un heredero.';
                }
                if ($cedulaHP)
                    $todasCedulasHerederos[] = $cedulaHP;
            }
        }

        return $this->errors;
    }

    // ====================================================================
    // Sección: Caso
    // ====================================================================
    private function validateCaso(array $caso, string $modo = 'Borrador', int $profesorId = 0, ?int $casoId = null): void
    {
        $titulo = trim($caso['titulo'] ?? '');
        if (empty($titulo)) {
            $this->errors[] = 'El Título del Caso es obligatorio.';
        }
        // #02 — Longitud máxima
        if (strlen($titulo) > 255) {
            $this->errors[] = 'El Título no puede exceder 255 caracteres.';
        }
        // #03 — Título no duplicado para este profesor
        if ($titulo && $profesorId > 0) {
            try {
                $db = DB::connect();
                $sql = "SELECT COUNT(*) FROM sim_casos_estudios WHERE titulo = :t AND profesor_id = :p AND estado != 'Eliminado'";
                $params = ['t' => $titulo, 'p' => $profesorId];
                if ($casoId) {
                    $sql .= " AND id != :id";
                    $params['id'] = $casoId;
                }
                $stmt = $db->prepare($sql);
                $stmt->execute($params);
                if ((int) $stmt->fetchColumn() > 0) {
                    $this->errors[] = 'Ya existe un caso con este título. Elige otro.';
                }
            } catch (\Throwable $e) {
                // Si falla la BD, no bloquear — la unicidad es best-effort
            }
        }

        $descripcion = trim($caso['descripcion'] ?? '');
        if ($modo === 'Publicar' && empty($descripcion)) {
            $this->errors[] = 'La Descripción del Caso es obligatoria.';
        }
        // #05 — Longitud máxima
        if (strlen($descripcion) > 1000) {
            $this->errors[] = 'La Descripción no puede exceder 1000 caracteres.';
        }

        $validEstados = ['Borrador', 'Publicado'];
        if (!empty($caso['estado']) && !in_array($caso['estado'], $validEstados)) {
            $this->errors[] = 'Estado del caso inválido.';
        }
        $validSucesion = ['Con Cédula', 'Sin Cédula', 'Con_Cedula', 'Sin_Cedula'];
        if ($modo === 'Publicar' && empty($caso['tipo_sucesion'])) {
            $this->errors[] = 'El Tipo de Sucesión es obligatorio.';
        } elseif (!empty($caso['tipo_sucesion']) && !in_array($caso['tipo_sucesion'], $validSucesion)) {
            $this->errors[] = 'Tipo de sucesión inválido.';
        }
    }

    // ====================================================================
    // Sección: Causante
    // ====================================================================
    private function validateCausante(array $c, string $tipoSucesion): void
    {
        if (empty($c['nombres'])) {
            $this->errors[] = 'Causante: Nombres es obligatorio.';
        } elseif (!$this->isValidName($c['nombres'])) {
            $this->errors[] = 'Causante: Nombres no puede contener solo espacios o números.';
        }
        if (empty($c['apellidos'])) {
            $this->errors[] = 'Causante: Apellidos es obligatorio.';
        } elseif (!$this->isValidName($c['apellidos'])) {
            $this->errors[] = 'Causante: Apellidos no puede contener solo espacios o números.';
        }
        if (empty($c['sexo']) || !in_array($c['sexo'], ['M', 'F'])) {
            $this->errors[] = 'Causante: Sexo debe ser M o F.';
        }
        $validEC = ['Soltero', 'Casado', 'Divorciado', 'Viudo', 'Union_Estable', 'Concubinato'];
        if (empty($c['estado_civil']) || !in_array($c['estado_civil'], $validEC)) {
            $this->errors[] = 'Causante: Estado civil inválido.';
        }
        if (empty($c['fecha_nacimiento']) || !$this->isValidDate($c['fecha_nacimiento'])) {
            $this->errors[] = 'Causante: Fecha de nacimiento inválida.';
        }
        if (empty($c['fecha_fallecimiento']) || !$this->isValidDate($c['fecha_fallecimiento'])) {
            $this->errors[] = 'Causante: Fecha de fallecimiento es obligatoria.';
        }

        // #13 — Fallecimiento posterior a nacimiento
        if (
            !empty($c['fecha_nacimiento']) && !empty($c['fecha_fallecimiento'])
            && $this->isValidDate($c['fecha_nacimiento']) && $this->isValidDate($c['fecha_fallecimiento'])
        ) {
            if ($c['fecha_fallecimiento'] <= $c['fecha_nacimiento']) {
                $this->errors[] = 'Causante: La fecha de fallecimiento debe ser posterior a la de nacimiento.';
            }
            // #14 — Edad razonable (0–130)
            $edad = $this->calcAge($c['fecha_nacimiento'], $c['fecha_fallecimiento']);
            if ($edad < 0 || $edad > 130) {
                $this->errors[] = 'Causante: La edad resultante no es razonable (0–130 años).';
            }
        }

        $esCedula = in_array($tipoSucesion, ['Con Cédula', 'Con_Cedula']);
        $sinCedula = in_array($tipoSucesion, ['Sin Cédula', 'Sin_Cedula']);
        if ($esCedula) {
            if (empty($c['cedula'])) {
                $this->errors[] = 'Causante: Cédula es obligatoria cuando la sucesión es Con Cédula.';
            } elseif (!preg_match('/^\d{6,10}$/', $c['cedula'])) {
                $this->errors[] = 'Causante: La cédula debe contener solo números (6-10 dígitos).';
            }
        }
        if ($sinCedula && !empty(trim($c['cedula'] ?? ''))) {
            $this->errors[] = 'Causante: En sucesión Sin Cédula, el campo de cédula debe estar vacío.';
        }
    }

    // ====================================================================
    // Sección: Datos Fiscales del Causante
    // ====================================================================
    private function validateDatosFiscales(array $df, string $fechaFallecimiento = ''): void
    {
        if (!isset($df['domiciliado_pais']) || !in_array((string) $df['domiciliado_pais'], ['0', '1'])) {
            $this->errors[] = 'Datos fiscales: Domiciliado en el país es obligatorio.';
        }
        if (empty($df['fecha_cierre_fiscal']) || !$this->isValidDate($df['fecha_cierre_fiscal'])) {
            $this->errors[] = 'Datos fiscales: Fecha de cierre fiscal inválida.';
        }
        // #21 — Cierre fiscal ≥ fallecimiento
        if (
            !empty($df['fecha_cierre_fiscal']) && !empty($fechaFallecimiento)
            && $this->isValidDate($df['fecha_cierre_fiscal']) && $this->isValidDate($fechaFallecimiento)
        ) {
            if ($df['fecha_cierre_fiscal'] < $fechaFallecimiento) {
                $this->errors[] = 'Datos fiscales: La fecha de cierre fiscal debe ser posterior o igual a la de fallecimiento.';
            }
        }
    }

    // ====================================================================
    // Sección: Acta de Defunción
    // ====================================================================
    private function validateActaDefuncion(array $acta, string $tipoSucesion, string $fechaFallecimiento = ''): void
    {
        $sinCedula = in_array($tipoSucesion, ['Sin Cédula', 'Sin_Cedula']);

        if ($sinCedula) {
            if (empty($acta['numero_acta']))
                $this->errors[] = 'Acta de defunción: Número de acta es obligatorio.';

            $yearActa = (int) ($acta['year_acta'] ?? 0);
            $yearActual = (int) date('Y');
            if (empty($acta['year_acta']) || $yearActa < 1900) {
                $this->errors[] = 'Acta de defunción: Año del acta inválido (mínimo 1900).';
            } elseif ($yearActa > $yearActual) {
                $this->errors[] = 'Acta de defunción: El año del acta no puede ser futuro.';
            }
            // Coherencia: año del acta >= año de fallecimiento
            if (
                $yearActa >= 1900 && $yearActa <= $yearActual
                && !empty($fechaFallecimiento) && $this->isValidDate($fechaFallecimiento)
            ) {
                $yearFallecimiento = (int) date('Y', strtotime($fechaFallecimiento));
                if ($yearActa < $yearFallecimiento) {
                    $this->errors[] = 'Acta de defunción: El año del acta debe ser mayor o igual al año de fallecimiento del causante.';
                }
            }

            $parroquia = $acta['parroquia_registro_id'] ?? $acta['parroquia_registro'] ?? $acta['parroquia'] ?? '';
            if (empty($parroquia))
                $this->errors[] = 'Acta de defunción: Parroquia es obligatoria.';
        }
    }

    // ====================================================================
    // Sección: Domicilio del Causante
    // ====================================================================
    private function validateDomicilio(array $d): void
    {
        $required = [
            'tipo_vialidad',
            'tipo_inmueble',
            'nombre_vialidad',
            'nro_inmueble',
            'tipo_nivel',
            'tipo_sector',
            'nro_nivel',
            'nombre_sector',
            'estado',
            'municipio',
            'parroquia',
            'ciudad'
        ];
        foreach ($required as $field) {
            if (empty($d[$field])) {
                $this->errors[] = "Domicilio causante: El campo «{$field}» es obligatorio.";
                break; // Un solo error genérico para no saturar
            }
        }
    }

    // ====================================================================
    // Sección: Direcciones adicionales del Causante (array)
    // ====================================================================
    private function validateDirecciones(array $direcciones): void
    {
        foreach ($direcciones as $i => $dir) {
            $n = $i + 1;
            $required = [
                'tipo_vialidad',
                'tipo_inmueble',
                'nombre_vialidad',
                'nro_inmueble',
                'tipo_sector',
                'nombre_sector',
                'estado',
                'municipio',
                'parroquia',
                'ciudad'
            ];
            foreach ($required as $field) {
                if (empty($dir[$field])) {
                    $this->errors[] = "Dirección #{$n}: Complete todos los campos de ubicación.";
                    break;
                }
            }
        }
    }

    // ====================================================================
    // Sección: Representante
    // ====================================================================
    private function validateRepresentante(array $r): void
    {
        if (empty($r['nombres'])) {
            $this->errors[] = 'Representante: Nombres es obligatorio.';
        } elseif (!$this->isValidName($r['nombres'])) {
            $this->errors[] = 'Representante: Nombres no puede contener solo espacios o números.';
        }
        if (empty($r['apellidos'])) {
            $this->errors[] = 'Representante: Apellidos es obligatorio.';
        } elseif (!$this->isValidName($r['apellidos'])) {
            $this->errors[] = 'Representante: Apellidos no puede contener solo espacios o números.';
        }

        // Cédula obligatoria
        if (empty($r['cedula'])) {
            $this->errors[] = 'Representante: Cédula es obligatoria.';
        } elseif (!preg_match('/^\d{6,10}$/', $r['cedula'])) {
            $this->errors[] = 'Representante: La cédula debe contener solo números (6-10 dígitos).';
        }

        // RIF obligatorio
        if (empty($r['rif_personal'])) {
            $this->errors[] = 'Representante: RIF es obligatorio.';
        } elseif (!preg_match('/^\d{5,10}$/', $r['rif_personal'])) {
            $this->errors[] = 'Representante: El RIF debe contener solo números (5-10 dígitos).';
        }

        if (empty($r['sexo']) || !in_array($r['sexo'], ['M', 'F'])) {
            $this->errors[] = 'Representante: Sexo es obligatorio.';
        }

        if (empty($r['fecha_nacimiento']) || !$this->isValidDate($r['fecha_nacimiento'])) {
            $this->errors[] = 'Representante: Fecha de nacimiento es obligatoria.';
        }
        // #33 — Representante ≥ 18 años
        if (!empty($r['fecha_nacimiento']) && $this->isValidDate($r['fecha_nacimiento'])) {
            $edad = $this->calcAge($r['fecha_nacimiento']);
            if ($edad < 18) {
                $this->errors[] = 'Representante: Debe ser mayor de 18 años.';
            }
        }

        // ── DB cross-validation: si la cédula o RIF ya existe, los datos deben coincidir ──
        $this->validateRepresentanteAgainstDB($r);
    }

    /**
     * Verifica que si la cédula o RIF del representante ya existen en sim_personas,
     * los datos enviados coincidan con los de la BD (previene condiciones de carrera).
     */
    private function validateRepresentanteAgainstDB(array $r): void
    {
        $cedula = trim($r['cedula'] ?? '');
        $letraCedula = trim($r['letra_cedula'] ?? '');
        $rifNumero = trim($r['rif_personal'] ?? '');
        $letraRif = trim($r['letra_rif'] ?? '');

        if (empty($cedula) && empty($rifNumero)) return;

        try {
            $db = DB::connect();

            // Buscar por cédula
            if ($cedula && $letraCedula) {
                $stmt = $db->prepare(
                    "SELECT id, nombres, apellidos, rif_personal
                     FROM sim_personas
                     WHERE tipo_cedula = :tipo AND cedula = :cedula
                     LIMIT 1"
                );
                $stmt->execute(['tipo' => $letraCedula, 'cedula' => $cedula]);
                $personaByCedula = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($personaByCedula) {
                    // Verificar que los nombres coinciden
                    if (
                        strtolower(trim($personaByCedula['nombres'])) !== strtolower(trim($r['nombres'] ?? ''))
                        || strtolower(trim($personaByCedula['apellidos'])) !== strtolower(trim($r['apellidos'] ?? ''))
                    ) {
                        $this->errors[] = 'Representante: La cédula ' . $letraCedula . '-' . $cedula
                            . ' ya está registrada con nombres diferentes ('
                            . $personaByCedula['nombres'] . ' ' . $personaByCedula['apellidos']
                            . '). Verifique los datos.';
                    }

                    // Verificar que el RIF coincide si ambos existen
                    if ($rifNumero && $letraRif && !empty($personaByCedula['rif_personal'])) {
                        $rifEnviado = $letraRif . $rifNumero;
                        if ($personaByCedula['rif_personal'] !== $rifEnviado) {
                            $this->errors[] = 'Representante: La cédula ' . $letraCedula . '-' . $cedula
                                . ' tiene un RIF diferente en la base de datos ('
                                . $personaByCedula['rif_personal']
                                . '). Verifique los datos.';
                        }
                    }
                }
            }

            // Buscar por RIF
            if ($rifNumero && $letraRif) {
                $rifCompleto = $letraRif . $rifNumero;
                $stmt = $db->prepare(
                    "SELECT id, nombres, apellidos, tipo_cedula, cedula
                     FROM sim_personas
                     WHERE rif_personal = :rif
                     LIMIT 1"
                );
                $stmt->execute(['rif' => $rifCompleto]);
                $personaByRif = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($personaByRif) {
                    // Verificar que los nombres coinciden
                    if (
                        strtolower(trim($personaByRif['nombres'])) !== strtolower(trim($r['nombres'] ?? ''))
                        || strtolower(trim($personaByRif['apellidos'])) !== strtolower(trim($r['apellidos'] ?? ''))
                    ) {
                        $this->errors[] = 'Representante: El RIF ' . $rifCompleto
                            . ' ya está registrado con nombres diferentes ('
                            . $personaByRif['nombres'] . ' ' . $personaByRif['apellidos']
                            . '). Verifique los datos.';
                    }

                    // Verificar que la cédula coincide si ambos existen
                    if ($cedula && $letraCedula && !empty($personaByRif['cedula'])) {
                        if (
                            $personaByRif['cedula'] !== $cedula
                            || $personaByRif['tipo_cedula'] !== $letraCedula
                        ) {
                            $this->errors[] = 'Representante: El RIF ' . $rifCompleto
                                . ' tiene una cédula diferente en la base de datos ('
                                . $personaByRif['tipo_cedula'] . '-' . $personaByRif['cedula']
                                . '). Verifique los datos.';
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            // Si falla la BD, no bloquear — la validación es best-effort
        }
    }

    // ====================================================================
    // Sección: Herencia
    // ====================================================================
    private function validateHerencia(array $h, string $fechaFallecimiento = ''): void
    {
        if (empty($h['tipos']) || !is_array($h['tipos']) || count($h['tipos']) === 0) {
            $this->errors[] = 'Debe seleccionar al menos un tipo de herencia.';
            return;
        }

        // Prefetch nombres de tipos si no vienen en el payload (borradores viejos)
        $nombresById = [];
        try {
            $db = DB::connect();
            $rows = $db->query("SELECT id, nombre FROM sim_tipos_herencia")->fetchAll(\PDO::FETCH_KEY_PAIR);
            $nombresById = $rows ?: [];
        } catch (\Throwable) {
            // Si falla la BD, continuamos sin los nombres (las validaciones opcionales serán omitidas)
        }

        foreach ($h['tipos'] as $tipo) {
            $id = (int) ($tipo['tipo_herencia_id'] ?? 0);
            $nombre = strtolower($tipo['nombre'] ?? $nombresById[$id] ?? '');

            // Testamentaria → requiere subtipo + fecha
            if (str_contains($nombre, 'testament')) {
                if (empty($tipo['subtipo_testamento'])) {
                    $this->errors[] = 'Herencia Testamentaria: Debe seleccionar el subtipo de testamento.';
                }
                if (empty($tipo['fecha_testamento'])) {
                    $this->errors[] = 'Herencia Testamentaria: La fecha del testamento es obligatoria.';
                }
                // Fecha testamento no puede ser posterior al fallecimiento del causante
                if (
                    !empty($tipo['fecha_testamento']) && !empty($fechaFallecimiento)
                    && $this->isValidDate($tipo['fecha_testamento']) && $this->isValidDate($fechaFallecimiento)
                    && $tipo['fecha_testamento'] > $fechaFallecimiento
                ) {
                    $this->errors[] = 'Herencia Testamentaria: La fecha del testamento no puede ser posterior a la fecha de fallecimiento del causante.';
                }
            }

            // Beneficio de Inventario → requiere fecha
            if (str_contains($nombre, 'inventario') || str_contains($nombre, 'beneficio')) {
                if (empty($tipo['fecha_conclusion_inventario'])) {
                    $this->errors[] = 'Beneficio de Inventario: La fecha de conclusión es obligatoria.';
                }
            }
        }
    }

    // ====================================================================
    // Sección: Herederos (array)
    // ====================================================================
    private function validateHerederos(array $herederos): void
    {
        // #39 — Cédulas no duplicadas entre herederos
        $cedulasVistas = [];
        foreach ($herederos as $i => $h) {
            $n = $i + 1;
            if (empty($h['cedula']) && empty($h['pasaporte'])) {
                $this->errors[] = "Heredero #{$n}: Debe ingresar Cédula o Pasaporte.";
            }
            if (!empty($h['cedula']) && !preg_match('/^\d{6,10}$/', $h['cedula'])) {
                $this->errors[] = "Heredero #{$n}: Cédula inválida (6-10 dígitos numéricos).";
            }
            // #39 — Duplicados
            $ced = trim($h['cedula'] ?? '');
            if ($ced && in_array($ced, $cedulasVistas)) {
                $this->errors[] = "Heredero #{$n}: Cédula duplicada con otro heredero.";
            }
            if ($ced)
                $cedulasVistas[] = $ced;

            $reqFields = ['nombres', 'apellidos', 'fecha_nacimiento', 'sexo', 'estado_civil', 'parentesco_id'];
            foreach ($reqFields as $f) {
                if (empty($h[$f])) {
                    $this->errors[] = "Heredero #{$n}: Complete todos los campos obligatorios.";
                    break;
                }
            }
            // #41 — Edad razonable del heredero
            if (!empty($h['fecha_nacimiento']) && $this->isValidDate($h['fecha_nacimiento'])) {
                $edad = $this->calcAge($h['fecha_nacimiento']);
                if ($edad < 0 || $edad > 150) {
                    $this->errors[] = "Heredero #{$n}: La fecha de nacimiento no resulta en una edad razonable.";
                }
            }
            if (($h['premuerto'] ?? '') === 'SI' && empty($h['fecha_fallecimiento'])) {
                $this->errors[] = "Heredero #{$n}: Fecha de fallecimiento es obligatoria para premuertos.";
            }
        }
    }

    // ====================================================================
    // Sección: Herederos Premuertos (array)
    // ====================================================================
    private function validateHerederosPremuertos(array $premuertos): void
    {
        foreach ($premuertos as $i => $h) {
            $n = $i + 1;
            if (empty($h['cedula']) && empty($h['pasaporte'])) {
                $this->errors[] = "Heredero premuerto #{$n}: Debe ingresar Cédula o Pasaporte.";
            }
            $reqFields = ['nombres', 'apellidos', 'fecha_nacimiento', 'sexo', 'estado_civil', 'parentesco_id', 'premuerto_padre_id'];
            foreach ($reqFields as $f) {
                if (empty($h[$f])) {
                    $this->errors[] = "Heredero premuerto #{$n}: Complete todos los campos (incluyendo a quién representa).";
                    break;
                }
            }
        }
    }

    // ====================================================================
    // Sección: Bienes Inmuebles (array)
    // ====================================================================
    private function validateBienesInmuebles(array $inmuebles): void
    {
        foreach ($inmuebles as $i => $b) {
            $n = $i + 1;
            if (empty($b['tipo_bien_inmueble_id']) || (is_array($b['tipo_bien_inmueble_id']) && count($b['tipo_bien_inmueble_id']) === 0)) {
                $this->errors[] = "Inmueble #{$n}: Debe seleccionar al menos un Tipo de Bien.";
            }
            $pct = (float) ($b['porcentaje'] ?? 0);
            if ($pct <= 0 || $pct > 100) {
                $this->errors[] = "Inmueble #{$n}: Porcentaje inválido.";
            }
            $reqFields = [
                'descripcion',
                'linderos',
                'superficie_construida',
                'superficie_no_construida',
                'area_superficie',
                'direccion',
                'oficina_registro',
                'nro_registro',
                'libro',
                'protocolo',
                'fecha_registro',
                'trimestre',
                'asiento_registral',
                'matricula',
                'folio_real_anio',
                'valor_original',
                'valor_declarado'
            ];
            foreach ($reqFields as $f) {
                if (empty($b[$f]) && $b[$f] !== '0' && $b[$f] !== 0) {
                    $this->errors[] = "Inmueble #{$n}: Complete todos los campos obligatorios (falta «{$f}»).";
                    break;
                }
            }

            if (($b['bien_litigioso'] ?? '') === 'Si') {
                $litFields = ['numero_expediente', 'tribunal_causa', 'partes_juicio', 'estado_juicio'];
                foreach ($litFields as $lf) {
                    if (empty($b[$lf])) {
                        $this->errors[] = "Inmueble #{$n}: Debe completar todos los detalles del Bien Litigioso.";
                        break;
                    }
                }
            }
        }
    }

    // ====================================================================
    // Sección: Bienes Muebles (object con keys de categoría, cada una array)
    // ====================================================================
    private function validateBienesMuebles($muebles): void
    {
        if (!is_array($muebles) && !is_object($muebles))
            return;

        foreach ($muebles as $catId => $items) {
            if (!is_array($items))
                continue;
            foreach ($items as $i => $b) {
                $n = $i + 1;
                $label = "Mueble (cat:{$catId}) #{$n}";

                if (empty(trim($b['descripcion'] ?? ''))) {
                    $this->errors[] = "{$label}: La Descripción es obligatoria.";
                }

                if (($b['bien_litigioso'] ?? '') === 'Si') {
                    $litFields = ['numero_expediente', 'tribunal_causa', 'partes_juicio', 'estado_juicio'];
                    foreach ($litFields as $lf) {
                        if (empty($b[$lf])) {
                            $this->errors[] = "{$label}: Debe completar los detalles del Bien Litigioso.";
                            break;
                        }
                    }
                }
            }
        }
    }

    // ====================================================================
    // Sección: Pasivos Deuda (array)
    // ====================================================================
    private function validatePasivosDeuda(array $deudas): void
    {
        foreach ($deudas as $i => $d) {
            $n = $i + 1;
            if (empty($d['tipo_pasivo_deuda_id'])) {
                $this->errors[] = "Deuda #{$n}: Debe seleccionar un Tipo de Deuda.";
            }
            if (empty(trim($d['descripcion'] ?? ''))) {
                $this->errors[] = "Deuda #{$n}: La Descripción es obligatoria.";
            }

            $pct = (float) ($d['porcentaje'] ?? 100);
            if ($pct <= 0 || $pct > 100) {
                $this->errors[] = "Deuda #{$n}: Porcentaje inválido.";
            }
        }
    }

    // ====================================================================
    // Sección: Pasivos Gastos (array)
    // ====================================================================
    private function validatePasivosGastos(array $gastos): void
    {
        foreach ($gastos as $i => $g) {
            $n = $i + 1;
            if (empty($g['tipo_pasivo_gasto_id'])) {
                $this->errors[] = "Gasto #{$n}: Debe seleccionar un Tipo de Gasto.";
            }
            if (empty(trim($g['descripcion'] ?? ''))) {
                $this->errors[] = "Gasto #{$n}: La Descripción es obligatoria.";
            }

        }
    }

    // ====================================================================
    // Sección: Exenciones (array)
    // ====================================================================
    private function validateExenciones(array $exenciones): void
    {
        foreach ($exenciones as $i => $e) {
            $n = $i + 1;
            if (empty($e['tipo_exencion'])) {
                $this->errors[] = "Exención #{$n}: Tipo de exención es obligatorio.";
            }
            if (empty(trim($e['descripcion'] ?? ''))) {
                $this->errors[] = "Exención #{$n}: La Descripción es obligatoria.";
            }

        }
    }

    // ====================================================================
    // Sección: Exoneraciones (array)
    // ====================================================================
    private function validateExoneraciones(array $exoneraciones): void
    {
        foreach ($exoneraciones as $i => $e) {
            $n = $i + 1;
            if (empty($e['tipo_exoneracion'])) {
                $this->errors[] = "Exoneración #{$n}: Tipo de exoneración es obligatorio.";
            }
            if (empty(trim($e['descripcion'] ?? ''))) {
                $this->errors[] = "Exoneración #{$n}: La Descripción es obligatoria.";
            }

        }
    }

    // ====================================================================
    // Sección: Prórrogas (array)
    // ====================================================================
    private function validateProrrogas(array $prorrogas): void
    {
        foreach ($prorrogas as $i => $p) {
            $n = $i + 1;
            $reqFields = ['fecha_solicitud', 'nro_resolucion', 'fecha_resolucion', 'plazo_dias', 'fecha_vencimiento'];
            foreach ($reqFields as $f) {
                if (empty($p[$f])) {
                    $this->errors[] = "Prórroga #{$n}: Complete todos los campos.";
                    break;
                }
            }
            if (!empty($p['plazo_dias']) && (int) $p['plazo_dias'] < 1) {
                $this->errors[] = "Prórroga #{$n}: El plazo debe ser al menos 1 día.";
            }
        }
    }



    // ====================================================================
    // Helpers
    // ====================================================================
    private function isValidDate(string $date): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    /**
     * Calcula la edad en años entre una fecha de nacimiento y una fecha de referencia.
     * Si no se proporciona fecha de referencia, usa la fecha actual.
     */
    private function calcAge(string $birthDate, ?string $refDate = null): int
    {
        $birth = new \DateTime($birthDate);
        $ref = $refDate ? new \DateTime($refDate) : new \DateTime();
        return (int) $birth->diff($ref)->y;
    }

    /**
     * Valida que un nombre/apellido contenga al menos una letra.
     * Rechaza valores que sean solo espacios, solo números, o vacíos.
     */
    private function isValidName(string $name): bool
    {
        return (bool) preg_match('/[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ]/u', $name);
    }
}
