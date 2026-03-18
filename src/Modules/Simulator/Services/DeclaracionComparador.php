<?php

declare(strict_types=1);

namespace App\Modules\Simulator\Services;

use App\Core\DB;
use PDO;

/**
 * DeclaracionComparador — Compara campo-por-campo el borrador del estudiante
 * contra los datos del caso en la DB, retornando resultados detallados
 * para generar el PDF de retroalimentación.
 *
 * Secciones: herederos, tipo herencia, bienes inmuebles, bienes muebles (12 cats),
 * pasivos deuda/gastos, exenciones, exoneraciones, autoliquidación, cálculo por heredero.
 */
class DeclaracionComparador
{
    private PDO $db;

    private const MUEBLE_LABELS = [
        1 => 'Banco', 2 => 'Seguro', 3 => 'Transporte', 4 => 'Opciones de Compra',
        5 => 'Cuentas y Efectos', 6 => 'Semovientes', 7 => 'Bonos',
        8 => 'Acciones', 9 => 'Prestaciones Sociales', 10 => 'Caja de Ahorro',
        11 => 'Plantaciones', 12 => 'Otros',
    ];

    private const MUEBLE_MAP = [
        'bienes_muebles_banco' => 1, 'bienes_muebles_seguro' => 2,
        'bienes_muebles_transporte' => 3, 'bienes_muebles_opciones_compra' => 4,
        'bienes_muebles_cuentas_efectos' => 5, 'bienes_muebles_semovientes' => 6,
        'bienes_muebles_bonos' => 7, 'bienes_muebles_acciones' => 8,
        'bienes_muebles_prestaciones_sociales' => 9, 'bienes_muebles_caja_ahorro' => 10,
        'bienes_muebles_plantaciones' => 11, 'bienes_muebles_otros' => 12,
    ];

    /** Categorías de bien mueble que NO tienen subtipos en sim_cat_tipos_bien_mueble */
    private const CATS_SIN_SUBTIPO = [4, 6, 7, 9, 10, 11, 12];

    public function __construct()
    {
        $this->db = DB::connect();
    }

    /**
     * Compara el borrador de un intento contra la DB.
     */
    public function comparar(int $intentoId, int $estudianteId): array
    {
        $intento = $this->getIntentoConCaso($intentoId, $estudianteId);
        if (!$intento) {
            return ['datos_caso' => [], 'secciones' => [], 'autoliquidacion' => [],
                    'herederos_calculo' => [], 'score' => ['correctos' => 0, 'total' => 0, 'porcentaje' => 0]];
        }

        $borrador = json_decode($intento['borrador_json'] ?: '{}', true) ?: [];
        $casoId = (int) $intento['caso_id'];
        $bs = new BorradorService($intento);

        $secciones = [];

        // 1. Herederos (regulares + marcados como premuerto)
        $allDbHerederos = $this->getHerederosDelCaso($casoId);

        // Split DB: herederos regulares/premuertos vs herederos DEL premuerto
        $dbRegulares = []; // es_premuerto in (0,1) AND premuerto_padre_id IS NULL = herederos directos
        $dbDelPremuerto = []; // premuerto_padre_id IS NOT NULL = heredan por representación
        foreach ($allDbHerederos as $h) {
            if (!empty($h['premuerto_padre_id'])) {
                $dbDelPremuerto[] = $h;
            } else {
                $dbRegulares[] = $h;
            }
        }

        $sec = $this->compararHerederosRegulares($borrador, $dbRegulares);
        if (!empty($sec)) $secciones[] = ['titulo' => 'Herederos', 'grupos' => $sec];

        // 2. Herederos del Premuerto (solo si hay en DB o en borrador)
        $sec = $this->compararHerederosDelPremuerto($borrador, $dbDelPremuerto);
        if (!empty($sec)) $secciones[] = ['titulo' => 'Herederos del Premuerto', 'grupos' => $sec];

        // 2b. Prórrogas
        $sec = $this->compararProrrogas($borrador, $casoId);
        if (!empty($sec)) $secciones[] = ['titulo' => 'Prórrogas', 'grupos' => $sec];

        // 3. Tipo de Herencia
        $sec = $this->compararTipoHerencia($borrador, $casoId);
        if (!empty($sec)) $secciones[] = ['titulo' => 'Tipo de Herencia', 'grupos' => $sec];

        // 3. Bienes Inmuebles
        $sec = $this->compararBienesInmuebles($borrador, $casoId);
        if (!empty($sec)) $secciones[] = ['titulo' => 'Bienes Inmuebles', 'grupos' => $sec];

        // 4. Bienes Muebles (12 categorías)
        $secMuebles = $this->compararBienesMuebles($borrador, $casoId);
        foreach ($secMuebles as $s) {
            $secciones[] = $s;
        }

        // 5. Pasivos Deuda (sub-secciones por tipo)
        $secDeuda = $this->compararPasivosDeuda($borrador, $casoId);
        foreach ($secDeuda as $s) {
            $secciones[] = $s;
        }

        // 6. Pasivos Gastos
        $sec = $this->compararPasivosGastos($borrador, $casoId);
        if (!empty($sec)) $secciones[] = ['titulo' => 'Pasivos Gastos', 'grupos' => $sec];

        // 7. Exenciones
        $sec = $this->compararExclusion($borrador, 'exenciones', 'sim_caso_exenciones', $casoId);
        if (!empty($sec)) $secciones[] = ['titulo' => 'Exenciones', 'grupos' => $sec];

        // 8. Exoneraciones
        $sec = $this->compararExclusion($borrador, 'exoneraciones', 'sim_caso_exoneraciones', $casoId);
        if (!empty($sec)) $secciones[] = ['titulo' => 'Exoneraciones', 'grupos' => $sec];

        // ═══ UT ═══
        $datosBasicos = $bs->getDatosBasicos();
        $fechaFall = $datosBasicos['fecha_fallecimiento'] ?? '';
        $utData = \App\Core\UnidadTributariaService::obtenerPorFecha($fechaFall);
        $ut = $utData ? (float) $utData['valor'] : 0.4;

        // ═══ Autoliquidación (filas 1-14) ═══
        $autoItems = $this->compararAutoliquidacion($bs, $casoId, $ut);

        // ═══ Cálculo por heredero ═══
        $herederosCalc = $this->compararCalculoHerederos($bs, $casoId, $ut);

        // ═══ Score global por UNIDADES ═══
        // Cada "grupo" en secciones = 1 unidad (excepto "Cantidad" que es resumen)
        $resumenSecciones = [];
        $globalScore = [
            'correctas' => 0, 'con_errores' => 0,
            'omitidas'  => 0, 'de_mas'      => 0,
            'total_esperado' => 0,
        ];

        foreach ($secciones as $sec) {
            $u = ['correctas' => 0, 'con_errores' => 0, 'omitidas' => 0, 'de_mas' => 0];
            foreach ($sec['grupos'] as $g) {
                // Saltar grupos "Cantidad" — son resumen, no unidades
                if ($g['label'] === 'Cantidad') continue;

                $label = $g['label'];
                if (str_starts_with($label, 'Omitido:')) {
                    $u['omitidas']++;
                } elseif (str_starts_with($label, 'De más:')) {
                    $u['de_mas']++;
                } else {
                    // Unidad emparejada — evaluar si todos los campos son correctos
                    $todosOk = true;
                    foreach ($g['campos'] as $c) {
                        if (!$c['correcto']) { $todosOk = false; break; }
                    }
                    if ($todosOk) { $u['correctas']++; } else { $u['con_errores']++; }
                }
            }
            $esperadas = $u['correctas'] + $u['con_errores'] + $u['omitidas'];
            $resumenSecciones[] = [
                'nombre'         => $sec['titulo'],
                'correctas'      => $u['correctas'],
                'con_errores'    => $u['con_errores'],
                'omitidas'       => $u['omitidas'],
                'de_mas'         => $u['de_mas'],
                'total_esperado' => $esperadas,
            ];
            $globalScore['correctas']      += $u['correctas'];
            $globalScore['con_errores']    += $u['con_errores'];
            $globalScore['omitidas']       += $u['omitidas'];
            $globalScore['de_mas']         += $u['de_mas'];
            $globalScore['total_esperado'] += $esperadas;
        }

        // Autoliquidación — cada línea es 1 unidad independiente
        $uAuto = ['correctas' => 0, 'con_errores' => 0, 'omitidas' => 0, 'de_mas' => 0];
        foreach ($autoItems as $item) {
            if ($item['correcto']) { $uAuto['correctas']++; } else { $uAuto['con_errores']++; }
        }
        $autoEsperadas = $uAuto['correctas'] + $uAuto['con_errores'];
        $resumenSecciones[] = [
            'nombre' => 'Autoliquidación', 'correctas' => $uAuto['correctas'],
            'con_errores' => $uAuto['con_errores'], 'omitidas' => 0, 'de_mas' => 0,
            'total_esperado' => $autoEsperadas,
        ];
        $globalScore['correctas']      += $uAuto['correctas'];
        $globalScore['con_errores']    += $uAuto['con_errores'];
        $globalScore['total_esperado'] += $autoEsperadas;

        // Impuesto por heredero — cada heredero es 1 unidad
        if (!empty($herederosCalc)) {
            $uHc = ['correctas' => 0, 'con_errores' => 0, 'omitidas' => 0, 'de_mas' => 0];
            foreach ($herederosCalc as $h) {
                $todosOk = true;
                foreach ($h['campos'] as $c) {
                    if (!$c['correcto']) { $todosOk = false; break; }
                }
                if ($todosOk) { $uHc['correctas']++; } else { $uHc['con_errores']++; }
            }
            $hcEsperadas = $uHc['correctas'] + $uHc['con_errores'];
            $resumenSecciones[] = [
                'nombre' => 'Impuesto por Heredero', 'correctas' => $uHc['correctas'],
                'con_errores' => $uHc['con_errores'], 'omitidas' => 0, 'de_mas' => 0,
                'total_esperado' => $hcEsperadas,
            ];
            $globalScore['correctas']      += $uHc['correctas'];
            $globalScore['con_errores']    += $uHc['con_errores'];
            $globalScore['total_esperado'] += $hcEsperadas;
        }

        $globalScore['porcentaje'] = $globalScore['total_esperado'] > 0
            ? round(($globalScore['correctas'] / $globalScore['total_esperado']) * 100, 1)
            : 0;

        // Patrimonio neto correcto (for informational note)
        $patrimonioNetoDb = 0;
        foreach ($autoItems as $item) {
            if (strpos($item['campo'], 'Patrimonio Neto') !== false) {
                $patrimonioNetoDb = $this->parseDecimal($item['esperado']);
                break;
            }
        }

        return [
            'datos_caso' => [
                'nombre_caso'         => $intento['caso_titulo'] ?? 'Sin título',
                'rif_sucesion'        => $bs->getRif(),
                'nombre_causante'     => $bs->getNombreCausante(),
                'fecha_fallecimiento' => $bs->getFechaFallecimiento(),
                'fecha_declaracion'   => date('d/m/Y'),
                'ut_aplicable'        => number_format($ut, 2, ',', '.'),
                'patrimonio_neto_correcto' => $patrimonioNetoDb,
            ],
            'secciones'          => $secciones,
            'resumen_secciones'  => $resumenSecciones,
            'autoliquidacion'    => $autoItems,
            'herederos_calculo'  => $herederosCalc,
            'score'              => $globalScore,
        ];
    }

    // ════════════════════════════════════════════════════════
    //  HEREDEROS
    // ════════════════════════════════════════════════════════

    private function compararHerederosRegulares(array $borrador, array $herederosDb): array
    {
        $grupos = [];
        $parentescoCatalog = $this->getParentescoCatalog();

        // Borrador: herederos regulares (incluye los que están marcados premuerto=SI)
        $herederosB = $borrador['herederos']['items'] ?? [];
        // Legacy fallback
        if (empty($herederosB)) {
            foreach (($borrador['relaciones'] ?? []) as $rel) {
                $pt = strtoupper($rel['parentescoText'] ?? '');
                if ($pt !== 'REPRESENTANTE DE LA SUCESION') {
                    $herederosB[] = $rel;
                }
            }
        }

        $countB = count($herederosB); $countDb = count($herederosDb);
        if ($countB === 0 && $countDb === 0) return $grupos;

        $grupos[] = ['label' => 'Cantidad', 'campos' => [
            ['campo' => 'Cantidad de herederos', 'borrador' => (string)$countB, 'esperado' => (string)$countDb, 'correcto' => $countB === $countDb],
        ]];

        // Match by documento (cédula, RIF, pasaporte)
        $dbUsados = [];
        foreach ($herederosB as $i => $hB) {
            $pos = $i + 1;
            $docB = trim($hB['idDocumento'] ?? $hB['cedula'] ?? '');
            $matched = false;

            foreach ($herederosDb as $j => $hDb) {
                if (in_array($j, $dbUsados)) continue;

                if ($this->matchDocumento($docB, $hDb)) {
                    $matched = true;
                    $dbUsados[] = $j;
                    $campos = [];

                    $nB = strtoupper(trim(($hB['apellidos'] ?? $hB['apellido'] ?? '') . ' ' . ($hB['nombres'] ?? $hB['nombre'] ?? '')));
                    $nDb = strtoupper(trim(($hDb['apellidos'] ?? '') . ' ' . ($hDb['nombres'] ?? '')));
                    $campos[] = ['campo' => 'Nombre', 'borrador' => $nB, 'esperado' => $nDb, 'correcto' => $nB === $nDb];

                    $docDbDisplay = $hDb['rif_personal'] ?: ($hDb['tipo_cedula'] . $hDb['cedula']);
                    $campos[] = ['campo' => 'Documento', 'borrador' => strtoupper($docB), 'esperado' => strtoupper($docDbDisplay), 'correcto' => true];

                    // Parentesco — resolver parentesco_id del borrador
                    $parIdB = (int)($hB['parentesco_id'] ?? 0);
                    $parB = strtoupper(trim($parentescoCatalog[$parIdB] ?? ''));
                    $parDb = strtoupper(trim($hDb['parentesco_nombre'] ?? ''));
                    $campos[] = ['campo' => 'Parentesco', 'borrador' => $parB ?: '—', 'esperado' => $parDb ?: '—',
                                 'correcto' => ($parB === $parDb) || ($parB === '' && $parDb === '')];

                    // Premuerto
                    $pmB = in_array(strtolower($hB['premuerto'] ?? ''), ['true', 'si', '1'], true) ? 'SI' : 'NO';
                    $pmDb = ((int)($hDb['es_premuerto'] ?? 0)) === 1 ? 'SI' : 'NO';
                    $campos[] = ['campo' => 'Premuerto', 'borrador' => $pmB, 'esperado' => $pmDb, 'correcto' => $pmB === $pmDb];

                    // Fecha de nacimiento
                    $fnB = $this->normFecha($hB['fecha_nacimiento'] ?? '');
                    $fnDb = $this->normFecha($hDb['fecha_nacimiento'] ?? '');
                    $campos[] = ['campo' => 'Fecha Nacimiento', 'borrador' => $fnB ?: '—', 'esperado' => $fnDb ?: '—',
                                 'correcto' => ($fnB === $fnDb) || ($fnB === '' && $fnDb === '')];

                    // Fecha de fallecimiento (solo si es premuerto)
                    if ($pmDb === 'SI' || $pmB === 'SI') {
                        $ffB = $this->normFecha($hB['fecha_fallecimiento'] ?? '');
                        // Solo mostrar el valor DB si es_premuerto=1 en el caso
                        $ffDb = ($pmDb === 'SI') ? $this->normFecha($hDb['fecha_fallecimiento'] ?? '') : '';
                        $campos[] = ['campo' => 'Fecha Fallecimiento', 'borrador' => $ffB ?: '—', 'esperado' => $ffDb ?: '—',
                                     'correcto' => ($ffB !== '' && $ffDb !== '' && $ffB === $ffDb)
                                                   || ($ffB === '' && $ffDb === '')];
                    }

                    $grupos[] = ['label' => "Heredero #{$pos}: {$nDb}", 'campos' => $campos];
                    break;
                }
            }

            if (!$matched) {
                $nB = strtoupper(trim(($hB['apellidos'] ?? $hB['apellido'] ?? '') . ' ' . ($hB['nombres'] ?? $hB['nombre'] ?? '')));
                $docB = strtoupper(trim($hB['idDocumento'] ?? $hB['cedula'] ?? ''));
                $parIdB = (int)($hB['parentesco_id'] ?? 0);
                $parB = strtoupper(trim($parentescoCatalog[$parIdB] ?? ''));
                $pmB = in_array(strtolower($hB['premuerto'] ?? ''), ['true', 'si', '1'], true) ? 'SI' : 'NO';
                $fnB = $this->normFecha($hB['fecha_nacimiento'] ?? '');
                $campos = [
                    ['campo' => 'Nombre', 'borrador' => $nB ?: '—', 'esperado' => '—', 'correcto' => false, 'tipo' => 'sobrante'],
                    ['campo' => 'Documento', 'borrador' => $docB ?: '—', 'esperado' => '—', 'correcto' => false, 'tipo' => 'sobrante'],
                    ['campo' => 'Parentesco', 'borrador' => $parB ?: '—', 'esperado' => '—', 'correcto' => false, 'tipo' => 'sobrante'],
                    ['campo' => 'Premuerto', 'borrador' => $pmB, 'esperado' => '—', 'correcto' => false, 'tipo' => 'sobrante'],
                    ['campo' => 'Fecha Nacimiento', 'borrador' => $fnB ?: '—', 'esperado' => '—', 'correcto' => false, 'tipo' => 'sobrante'],
                ];
                $grupos[] = ['label' => "De más: {$nB}", 'campos' => $campos];
            }
        }

        // DB herederos not matched — show full expected fields
        foreach ($herederosDb as $j => $hDb) {
            if (!in_array($j, $dbUsados)) {
                $nDb = strtoupper(trim(($hDb['apellidos'] ?? '') . ' ' . ($hDb['nombres'] ?? '')));
                $docDb = $hDb['rif_personal'] ?: ($hDb['tipo_cedula'] . $hDb['cedula']);
                $parDb = strtoupper(trim($hDb['parentesco_nombre'] ?? ''));
                $fnDb = $this->normFecha($hDb['fecha_nacimiento'] ?? '');
                $pmDb = ((int)($hDb['es_premuerto'] ?? 0)) === 1 ? 'SI' : 'NO';
                $campos = [
                    ['campo' => 'Nombre', 'borrador' => '—', 'esperado' => $nDb, 'correcto' => false, 'tipo' => 'omitido'],
                    ['campo' => 'Documento', 'borrador' => '—', 'esperado' => strtoupper($docDb), 'correcto' => false, 'tipo' => 'omitido'],
                    ['campo' => 'Parentesco', 'borrador' => '—', 'esperado' => $parDb ?: '—', 'correcto' => false, 'tipo' => 'omitido'],
                    ['campo' => 'Premuerto', 'borrador' => '—', 'esperado' => $pmDb, 'correcto' => false, 'tipo' => 'omitido'],
                    ['campo' => 'Fecha Nacimiento', 'borrador' => '—', 'esperado' => $fnDb ?: '—', 'correcto' => false, 'tipo' => 'omitido'],
                ];
                if ($pmDb === 'SI') {
                    $ffDb = $this->normFecha($hDb['fecha_fallecimiento'] ?? '');
                    $campos[] = ['campo' => 'Fecha Fallecimiento', 'borrador' => '—', 'esperado' => $ffDb ?: '—', 'correcto' => false, 'tipo' => 'omitido'];
                }
                $grupos[] = ['label' => "Omitido: {$nDb}", 'campos' => $campos];
            }
        }

        return $grupos;
    }

    private function compararHerederosDelPremuerto(array $borrador, array $herederosDb): array
    {
        $grupos = [];
        $parentescoCatalog = $this->getParentescoCatalog();
        $herederosB = $borrador['herederos_premuertos'] ?? [];

        // Build lookup: cédula → nombre from borrador relaciones (para resolver premuerto_padre_id)
        $relaciones = $borrador['relaciones'] ?? [];
        $relNombrePorCedula = [];
        foreach ($relaciones as $rel) {
            $ced = trim($rel['cedula'] ?? '');
            if ($ced !== '') {
                $relNombrePorCedula[$ced] = strtoupper(trim(($rel['apellido'] ?? '') . ' ' . ($rel['nombre'] ?? '')));
            }
        }

        $countB = count($herederosB); $countDb = count($herederosDb);
        if ($countB === 0 && $countDb === 0) return $grupos;

        $grupos[] = ['label' => 'Cantidad', 'campos' => [
            ['campo' => 'Cantidad', 'borrador' => (string)$countB, 'esperado' => (string)$countDb, 'correcto' => $countB === $countDb],
        ]];

        // Match by documento
        $dbUsados = [];
        foreach ($herederosB as $i => $hB) {
            $pos = $i + 1;
            $docB = trim($hB['idDocumento'] ?? $hB['cedula'] ?? '');
            $matched = false;

            foreach ($herederosDb as $j => $hDb) {
                if (in_array($j, $dbUsados)) continue;

                if ($this->matchDocumento($docB, $hDb)) {
                    $matched = true;
                    $dbUsados[] = $j;
                    $campos = [];

                    $nB = strtoupper(trim(($hB['apellido'] ?? $hB['apellidos'] ?? '') . ' ' . ($hB['nombre'] ?? $hB['nombres'] ?? '')));
                    $nDb = strtoupper(trim(($hDb['apellidos'] ?? '') . ' ' . ($hDb['nombres'] ?? '')));
                    $campos[] = ['campo' => 'Nombre', 'borrador' => $nB, 'esperado' => $nDb, 'correcto' => $nB === $nDb];

                    $docDbDisplay = $hDb['rif_personal'] ?: ($hDb['tipo_cedula'] . $hDb['cedula']);
                    $campos[] = ['campo' => 'Documento', 'borrador' => strtoupper($docB), 'esperado' => strtoupper($docDbDisplay), 'correcto' => true];

                    // Parentesco
                    $parIdB = (int)($hB['parentesco_id'] ?? 0);
                    $parB = strtoupper(trim($parentescoCatalog[$parIdB] ?? ''));
                    $parDb = strtoupper(trim($hDb['parentesco_nombre'] ?? ''));
                    $campos[] = ['campo' => 'Parentesco', 'borrador' => $parB ?: '—', 'esperado' => $parDb ?: '—',
                                 'correcto' => ($parB === $parDb) || ($parB === '' && $parDb === '')];

                    // Fecha de nacimiento
                    $fnB = $this->normFecha($hB['fecha_nacimiento'] ?? '');
                    $fnDb = $this->normFecha($hDb['fecha_nacimiento'] ?? '');
                    $campos[] = ['campo' => 'Fecha Nacimiento', 'borrador' => $fnB ?: '—', 'esperado' => $fnDb ?: '—',
                                 'correcto' => ($fnB === $fnDb) || ($fnB === '' && $fnDb === '')];

                    // Representa a (premuerto padre)
                    $padreIdB = trim($hB['premuerto_padre_id'] ?? '');
                    $reprB = $relNombrePorCedula[$padreIdB] ?? ($padreIdB ? "CED: {$padreIdB}" : '');
                    $reprDb = '';
                    if (!empty($hDb['padre_nombres']) || !empty($hDb['padre_apellidos'])) {
                        $reprDb = strtoupper(trim(($hDb['padre_apellidos'] ?? '') . ' ' . ($hDb['padre_nombres'] ?? '')));
                    }
                    $reprB = strtoupper($reprB);
                    $campos[] = ['campo' => 'Representa a', 'borrador' => $reprB ?: '—', 'esperado' => $reprDb ?: '—',
                                 'correcto' => ($reprB === $reprDb) || ($reprB === '' && $reprDb === '')];

                    $grupos[] = ['label' => "Heredero del Premuerto #{$pos}: {$nDb}", 'campos' => $campos];
                    break;
                }
            }

            if (!$matched) {
                $nB = strtoupper(trim(($hB['apellido'] ?? $hB['apellidos'] ?? '') . ' ' . ($hB['nombre'] ?? $hB['nombres'] ?? '')));
                $docB = strtoupper(trim($hB['idDocumento'] ?? $hB['cedula'] ?? ''));
                $parIdB = (int)($hB['parentesco_id'] ?? 0);
                $parB = strtoupper(trim($parentescoCatalog[$parIdB] ?? ''));
                $fnB = $this->normFecha($hB['fecha_nacimiento'] ?? '');
                $padreIdB = trim($hB['premuerto_padre_id'] ?? '');
                $reprB = strtoupper($relNombrePorCedula[$padreIdB] ?? ($padreIdB ? "CED: {$padreIdB}" : ''));
                $campos = [
                    ['campo' => 'Nombre', 'borrador' => $nB ?: '—', 'esperado' => '—', 'correcto' => false, 'tipo' => 'sobrante'],
                    ['campo' => 'Documento', 'borrador' => $docB ?: '—', 'esperado' => '—', 'correcto' => false, 'tipo' => 'sobrante'],
                    ['campo' => 'Parentesco', 'borrador' => $parB ?: '—', 'esperado' => '—', 'correcto' => false, 'tipo' => 'sobrante'],
                    ['campo' => 'Fecha Nacimiento', 'borrador' => $fnB ?: '—', 'esperado' => '—', 'correcto' => false, 'tipo' => 'sobrante'],
                    ['campo' => 'Representa a', 'borrador' => $reprB ?: '—', 'esperado' => '—', 'correcto' => false, 'tipo' => 'sobrante'],
                ];
                $grupos[] = ['label' => "De más: {$nB}", 'campos' => $campos];
            }
        }

        // Faltantes — show full expected fields
        foreach ($herederosDb as $j => $hDb) {
            if (!in_array($j, $dbUsados)) {
                $nDb = strtoupper(trim(($hDb['apellidos'] ?? '') . ' ' . ($hDb['nombres'] ?? '')));
                $docDb = $hDb['rif_personal'] ?: ($hDb['tipo_cedula'] . $hDb['cedula']);
                $parDb = strtoupper(trim($hDb['parentesco_nombre'] ?? ''));
                $fnDb = $this->normFecha($hDb['fecha_nacimiento'] ?? '');
                $reprDb = '';
                if (!empty($hDb['padre_nombres']) || !empty($hDb['padre_apellidos'])) {
                    $reprDb = strtoupper(trim(($hDb['padre_apellidos'] ?? '') . ' ' . ($hDb['padre_nombres'] ?? '')));
                }
                $campos = [
                    ['campo' => 'Nombre', 'borrador' => '—', 'esperado' => $nDb, 'correcto' => false, 'tipo' => 'omitido'],
                    ['campo' => 'Documento', 'borrador' => '—', 'esperado' => strtoupper($docDb), 'correcto' => false, 'tipo' => 'omitido'],
                    ['campo' => 'Parentesco', 'borrador' => '—', 'esperado' => $parDb ?: '—', 'correcto' => false, 'tipo' => 'omitido'],
                    ['campo' => 'Fecha Nacimiento', 'borrador' => '—', 'esperado' => $fnDb ?: '—', 'correcto' => false, 'tipo' => 'omitido'],
                    ['campo' => 'Representa a', 'borrador' => '—', 'esperado' => $reprDb ?: '—', 'correcto' => false, 'tipo' => 'omitido'],
                ];
                $grupos[] = ['label' => "Omitido: {$nDb}", 'campos' => $campos];
            }
        }

        return $grupos;
    }

    private function getHerederosDelCaso(int $casoId): array
    {
        $sql = "
            SELECT p.tipo_cedula, p.cedula, p.pasaporte, p.rif_personal,
                   p.nombres, p.apellidos, p.fecha_nacimiento,
                   cp.es_premuerto, cp.premuerto_padre_id,
                   par.etiqueta AS parentesco_nombre,
                   ad.fecha_fallecimiento,
                   pp.nombres AS padre_nombres, pp.apellidos AS padre_apellidos,
                   pp.tipo_cedula AS padre_tipo_cedula, pp.cedula AS padre_cedula
            FROM sim_caso_participantes cp
            INNER JOIN sim_personas p ON cp.persona_id = p.id
            LEFT JOIN sim_cat_parentescos par ON cp.parentesco_id = par.id
            LEFT JOIN sim_actas_defunciones ad ON ad.sim_persona_id = p.id
            LEFT JOIN sim_caso_participantes cp_padre ON cp.premuerto_padre_id = cp_padre.id
            LEFT JOIN sim_personas pp ON cp_padre.persona_id = pp.id
            WHERE cp.caso_estudio_id = :caso_id
            ORDER BY cp.es_premuerto DESC, cp.id ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':caso_id', $casoId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getParentescoCatalog(): array
    {
        $stmt = $this->db->query("SELECT id, etiqueta FROM sim_cat_parentescos ORDER BY id ASC");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $map = [];
        foreach ($rows as $r) {
            $map[(int)$r['id']] = $r['etiqueta'];
        }
        return $map;
    }

    // ════════════════════════════════════════════════════════
    //  PRÓRROGAS
    // ════════════════════════════════════════════════════════

    private function compararProrrogas(array $borrador, int $casoId): array
    {
        $itemsB = $borrador['prorrogas'] ?? [];

        $stmt = $this->db->prepare("SELECT * FROM sim_caso_prorrogas WHERE caso_estudio_id = :id ORDER BY id ASC");
        $stmt->bindValue(':id', $casoId, PDO::PARAM_INT);
        $stmt->execute();
        $itemsDb = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($itemsB) && empty($itemsDb)) return [];

        $grupos = [];

        $grupos[] = ['label' => 'Cantidad', 'campos' => [
            ['campo' => 'Cantidad de Prórrogas', 'borrador' => (string)count($itemsB), 'esperado' => (string)count($itemsDb), 'correcto' => count($itemsB) === count($itemsDb)],
        ]];

        // ── PASADA 1: Score-based matching (nro_resolucion +2, fecha_solicitud +1) ──
        $dbUsados = [];
        $bSinMatch = [];
        foreach ($itemsB as $i => $bI) {
            $nroB = strtoupper(trim($bI['nro_resolucion'] ?? ''));
            $fsolB = $this->normFecha($bI['fecha_solicitud'] ?? '');

            $bestJ = -1; $bestScore = 0;
            foreach ($itemsDb as $j => $dbI) {
                if (in_array($j, $dbUsados)) continue;
                $score = 0;
                $nroDb = strtoupper(trim($dbI['nro_resolucion'] ?? ''));
                $fsolDb = $this->normFecha($dbI['fecha_solicitud'] ?? '');
                if ($nroB !== '' && $nroDb !== '' && $nroB === $nroDb) $score += 2;
                if ($fsolB !== '' && $fsolDb !== '' && $fsolB === $fsolDb) $score += 1;
                if ($score > $bestScore) { $bestScore = $score; $bestJ = $j; }
            }

            if ($bestJ >= 0) {
                $dbUsados[] = $bestJ;
                $dbI = $itemsDb[$bestJ];
                $campos = $this->camposProrroga($bI, $dbI);
                $desc = trim($dbI['nro_resolucion'] ?? "Prórroga #" . ($i+1));
                $grupos[] = ['label' => "Prórroga #" . ($i+1) . ": {$desc}", 'campos' => $campos];
            } else {
                $bSinMatch[] = $i;
            }
        }

        // ── PASADA 2: Ordinal fallback ──
        $dbSinMatch = [];
        foreach ($itemsDb as $j => $dbI) {
            if (!in_array($j, $dbUsados)) $dbSinMatch[] = $j;
        }
        $minPar = min(count($bSinMatch), count($dbSinMatch));
        for ($k = 0; $k < $minPar; $k++) {
            $i = $bSinMatch[$k];
            $j = $dbSinMatch[$k];
            $bI = $itemsB[$i]; $dbI = $itemsDb[$j];
            $campos = $this->camposProrroga($bI, $dbI);
            $desc = trim($dbI['nro_resolucion'] ?? "Prórroga #" . ($i+1));
            $grupos[] = ['label' => "Prórroga #" . ($i+1) . ": {$desc}", 'campos' => $campos];
        }

        // ── Sobrantes ──
        for ($k = $minPar; $k < count($bSinMatch); $k++) {
            $i = $bSinMatch[$k];
            $bI = $itemsB[$i];
            $desc = trim($bI['nro_resolucion'] ?? '');
            $campos = [
                ['campo' => 'Fecha Solicitud', 'borrador' => $this->normFecha($bI['fecha_solicitud'] ?? ''), 'esperado' => '—', 'correcto' => false, 'tipo' => 'sobrante'],
                ['campo' => 'Nro. Resolución', 'borrador' => strtoupper(trim($bI['nro_resolucion'] ?? '')), 'esperado' => '—', 'correcto' => false, 'tipo' => 'sobrante'],
                ['campo' => 'Fecha Resolución', 'borrador' => $this->normFecha($bI['fecha_resolucion'] ?? ''), 'esperado' => '—', 'correcto' => false, 'tipo' => 'sobrante'],
                ['campo' => 'Plazo Otorgado (Días)', 'borrador' => trim($bI['plazo_dias'] ?? ''), 'esperado' => '—', 'correcto' => false, 'tipo' => 'sobrante'],
                ['campo' => 'Fecha Vencimiento', 'borrador' => $this->normFecha($bI['fecha_vencimiento'] ?? ''), 'esperado' => '—', 'correcto' => false, 'tipo' => 'sobrante'],
            ];
            $grupos[] = ['label' => "De más: {$desc}", 'campos' => $campos];
        }

        // ── Faltantes ──
        for ($k = $minPar; $k < count($dbSinMatch); $k++) {
            $j = $dbSinMatch[$k];
            $dbI = $itemsDb[$j];
            $desc = trim($dbI['nro_resolucion'] ?? '');
            $campos = [
                ['campo' => 'Fecha Solicitud', 'borrador' => '—', 'esperado' => $this->normFecha($dbI['fecha_solicitud'] ?? ''), 'correcto' => false, 'tipo' => 'omitido'],
                ['campo' => 'Nro. Resolución', 'borrador' => '—', 'esperado' => strtoupper(trim($dbI['nro_resolucion'] ?? '')), 'correcto' => false, 'tipo' => 'omitido'],
                ['campo' => 'Fecha Resolución', 'borrador' => '—', 'esperado' => $this->normFecha($dbI['fecha_resolucion'] ?? ''), 'correcto' => false, 'tipo' => 'omitido'],
                ['campo' => 'Plazo Otorgado (Días)', 'borrador' => '—', 'esperado' => (string)($dbI['plazo_otorgado_dias'] ?? ''), 'correcto' => false, 'tipo' => 'omitido'],
                ['campo' => 'Fecha Vencimiento', 'borrador' => '—', 'esperado' => $this->normFecha($dbI['fecha_vencimiento'] ?? ''), 'correcto' => false, 'tipo' => 'omitido'],
            ];
            $grupos[] = ['label' => "Omitido: {$desc}", 'campos' => $campos];
        }

        return $grupos;
    }

    /**
     * Compare fields of a matched pair of prórrogas.
     */
    private function camposProrroga(array $bI, array $dbI): array
    {
        $campos = [];
        $campos[] = $this->cmpTexto('Fecha Solicitud', $this->normFecha($bI['fecha_solicitud'] ?? ''), $this->normFecha($dbI['fecha_solicitud'] ?? ''));
        $campos[] = $this->cmpTexto('Nro. Resolución', strtoupper(trim($bI['nro_resolucion'] ?? '')), strtoupper(trim($dbI['nro_resolucion'] ?? '')));
        $campos[] = $this->cmpTexto('Fecha Resolución', $this->normFecha($bI['fecha_resolucion'] ?? ''), $this->normFecha($dbI['fecha_resolucion'] ?? ''));
        // Borrador usa 'plazo_dias', DB usa 'plazo_otorgado_dias'
        $campos[] = $this->cmpTexto('Plazo Otorgado (Días)', trim($bI['plazo_dias'] ?? ''), (string)($dbI['plazo_otorgado_dias'] ?? ''));
        $campos[] = $this->cmpTexto('Fecha Vencimiento', $this->normFecha($bI['fecha_vencimiento'] ?? ''), $this->normFecha($dbI['fecha_vencimiento'] ?? ''));
        return $campos;
    }

    // ════════════════════════════════════════════════════════
    //  TIPO DE HERENCIA
    // ════════════════════════════════════════════════════════

    private function compararTipoHerencia(array $borrador, int $casoId): array
    {
        $grupos = [];
        $tiposB = $borrador['tipo_herencia'] ?? [];
        $tiposDb = $this->getTiposHerenciaDelCaso($casoId);

        if (empty($tiposB) && empty($tiposDb)) return $grupos;

        $idsB = array_map(fn($t) => (int)($t['tipo_herencia_id'] ?? 0), $tiposB);
        $idsDb = array_map(fn($t) => (int)$t['tipo_herencia_id'], $tiposDb);

        // Tipos comunes
        foreach (array_intersect($idsB, $idsDb) as $id) {
            $bI = $this->findById($tiposB, 'tipo_herencia_id', $id);
            $dbI = $this->findById($tiposDb, 'tipo_herencia_id', $id);
            if (!$bI || !$dbI) continue;

            $nombre = $dbI['tipo_nombre'] ?? "ID {$id}";
            $campos = [];
            $campos[] = ['campo' => 'Tipo', 'borrador' => $nombre, 'esperado' => $nombre, 'correcto' => true];

            if (!empty($dbI['subtipo_testamento'])) {
                $sB = strtoupper(trim($bI['subtipo_testamento'] ?? ''));
                $sDb = strtoupper(trim($dbI['subtipo_testamento']));
                $campos[] = $this->cmpTexto('Subtipo Testamento', $sB, $sDb);
            }
            if (!empty($dbI['fecha_testamento'])) {
                $fB = $this->normFecha($bI['fecha_testamento'] ?? '');
                $fDb = $this->normFecha($dbI['fecha_testamento']);
                $campos[] = $this->cmpTexto('Fecha Testamento', $fB, $fDb);
            }
            if (!empty($dbI['fecha_conclusion_inventario'])) {
                $fB = $this->normFecha($bI['fecha_conclusion_inventario'] ?? '');
                $fDb = $this->normFecha($dbI['fecha_conclusion_inventario']);
                $campos[] = $this->cmpTexto('Fecha Conclusión Inventario', $fB, $fDb);
            }
            $grupos[] = ['label' => $nombre, 'campos' => $campos];
        }

        // Faltantes
        foreach (array_diff($idsDb, $idsB) as $id) {
            $dbI = $this->findById($tiposDb, 'tipo_herencia_id', $id);
            $nombre = $dbI['tipo_nombre'] ?? "ID {$id}";
            $campos = [
                ['campo' => 'Tipo de Herencia', 'borrador' => '—', 'esperado' => $nombre, 'correcto' => false, 'tipo' => 'omitido'],
            ];
            if (!empty($dbI['subtipo_testamento'])) {
                $campos[] = ['campo' => 'Subtipo Testamento', 'borrador' => '—', 'esperado' => strtoupper(trim($dbI['subtipo_testamento'])), 'correcto' => false, 'tipo' => 'omitido'];
            }
            if (!empty($dbI['fecha_testamento'])) {
                $campos[] = ['campo' => 'Fecha Testamento', 'borrador' => '—', 'esperado' => $this->normFecha($dbI['fecha_testamento']), 'correcto' => false, 'tipo' => 'omitido'];
            }
            $grupos[] = ['label' => "Omitido: {$nombre}", 'campos' => $campos];
        }
        // Sobrantes
        foreach (array_diff($idsB, $idsDb) as $id) {
            $bI = $this->findById($tiposB, 'tipo_herencia_id', $id);
            $nombre = $bI['nombre'] ?? $bI['tipo_nombre'] ?? "ID {$id}";
            $campos = [
                ['campo' => 'Tipo', 'borrador' => $nombre, 'esperado' => '—', 'correcto' => false, 'tipo' => 'sobrante'],
            ];
            if (!empty($bI['subtipo_testamento'])) {
                $campos[] = ['campo' => 'Subtipo Testamento', 'borrador' => strtoupper(trim($bI['subtipo_testamento'])), 'esperado' => '—', 'correcto' => false, 'tipo' => 'sobrante'];
            }
            if (!empty($bI['fecha_testamento'])) {
                $campos[] = ['campo' => 'Fecha Testamento', 'borrador' => $this->normFecha($bI['fecha_testamento']), 'esperado' => '—', 'correcto' => false, 'tipo' => 'sobrante'];
            }
            if (!empty($bI['fecha_conclusion_inventario'])) {
                $campos[] = ['campo' => 'Fecha Conclusión Inventario', 'borrador' => $this->normFecha($bI['fecha_conclusion_inventario']), 'esperado' => '—', 'correcto' => false, 'tipo' => 'sobrante'];
            }
            $grupos[] = ['label' => "De más: {$nombre}", 'campos' => $campos];
        }

        return $grupos;
    }

    private function getTiposHerenciaDelCaso(int $casoId): array
    {
        $stmt = $this->db->prepare("
            SELECT r.*, th.nombre AS tipo_nombre
            FROM sim_caso_tipoherencia_rel r
            LEFT JOIN sim_cat_tipoherencias th ON r.tipo_herencia_id = th.id
            WHERE r.caso_estudio_id = :id
        ");
        $stmt->bindValue(':id', $casoId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ════════════════════════════════════════════════════════
    //  BIENES INMUEBLES
    // ════════════════════════════════════════════════════════

    private function compararBienesInmuebles(array $borrador, int $casoId): array
    {
        $grupos = [];
        $itemsB = $borrador['bienes_inmuebles'] ?? [];
        $itemsDb = $this->fetchRows('sim_caso_bienes_inmuebles', $casoId);

        if (empty($itemsB) && empty($itemsDb)) return $grupos;

        // Datos de tribunal para inmuebles
        $litDbMap = $this->getLitigiosos($casoId, 'Inmueble');

        $grupos[] = ['label' => 'Cantidad', 'campos' => [
            ['campo' => 'Cantidad de inmuebles', 'borrador' => (string)count($itemsB), 'esperado' => (string)count($itemsDb), 'correcto' => count($itemsB) === count($itemsDb)],
        ]];

        // ── PASADA 1: Score-based matching ──
        $dbUsados = [];
        $bSinMatch = [];
        foreach ($itemsB as $i => $bI) {
            $valorB = $this->parseDecimal($bI['valor_declarado'] ?? '0');
            $descB = $this->normDesc($bI['descripcion'] ?? '');

            $bestJ = -1; $bestScore = 0;
            foreach ($itemsDb as $j => $dbI) {
                if (in_array($j, $dbUsados)) continue;
                $score = 0;
                $descDb = $this->normDesc($dbI['descripcion'] ?? '');
                $valorDb = (float)($dbI['valor_declarado'] ?? 0);
                if ($descB !== '' && $descDb !== '' && $descB === $descDb) $score += 2;
                if (abs($valorB - $valorDb) < 0.01) $score += 1;
                if ($score > $bestScore) { $bestScore = $score; $bestJ = $j; }
            }

            if ($bestJ >= 0) {
                $dbUsados[] = $bestJ;
                $campos = $this->camposBienInmueble($bI, $itemsDb[$bestJ], $litDbMap);
                $label = mb_substr(trim($itemsDb[$bestJ]['descripcion'] ?? "Inmueble #" . ($i+1)), 0, 50);
                $grupos[] = ['label' => "Inmueble #" . ($i+1) . ": {$label}", 'campos' => $campos];
            } else {
                $bSinMatch[] = $i;
            }
        }

        // ── PASADA 2: Ordinal fallback ──
        $dbSinMatch = [];
        foreach ($itemsDb as $j => $dbI) {
            if (!in_array($j, $dbUsados)) $dbSinMatch[] = $j;
        }

        $minPar = min(count($bSinMatch), count($dbSinMatch));
        for ($k = 0; $k < $minPar; $k++) {
            $i = $bSinMatch[$k];
            $j = $dbSinMatch[$k];
            $campos = $this->camposBienInmueble($itemsB[$i], $itemsDb[$j], $litDbMap);
            $label = mb_substr(trim($itemsDb[$j]['descripcion'] ?? "Inmueble #" . ($i+1)), 0, 50);
            $grupos[] = ['label' => "Inmueble #" . ($i+1) . ": {$label}", 'campos' => $campos];
        }

        // ── Sobrantes ──
        for ($k = $minPar; $k < count($bSinMatch); $k++) {
            $i = $bSinMatch[$k];
            $bI = $itemsB[$i];
            $desc = mb_substr(trim($bI['descripcion'] ?? ''), 0, 50);
            $campos = [
                ['campo' => 'Valor Declarado', 'borrador' => $this->fmtBs($this->parseDecimal($bI['valor_declarado'] ?? '0')), 'esperado' => '—', 'correcto' => false, 'tipo' => 'sobrante'],
                ['campo' => 'Porcentaje', 'borrador' => number_format($this->parseDecimal($bI['porcentaje'] ?? '0'), 2, ',', '.'), 'esperado' => '—', 'correcto' => false, 'tipo' => 'sobrante'],
                ['campo' => 'Descripción', 'borrador' => strtoupper(trim($bI['descripcion'] ?? '')), 'esperado' => '—', 'correcto' => false, 'tipo' => 'sobrante'],
            ];
            $vpB = ($bI['vivienda_principal'] ?? 'false') === 'true' ? 'SI' : 'NO';
            $campos[] = ['campo' => 'Vivienda Principal', 'borrador' => $vpB, 'esperado' => '—', 'correcto' => false, 'tipo' => 'sobrante'];
            $litB = ($bI['bien_litigioso'] ?? 'false') === 'true' ? 'SI' : 'NO';
            $campos[] = ['campo' => 'Bien Litigioso', 'borrador' => $litB, 'esperado' => '—', 'correcto' => false, 'tipo' => 'sobrante'];
            $grupos[] = ['label' => "De más: {$desc}", 'campos' => $campos];
        }

        // ── Faltantes ──
        for ($k = $minPar; $k < count($dbSinMatch); $k++) {
            $j = $dbSinMatch[$k];
            $dbI = $itemsDb[$j];
            $desc = mb_substr(trim($dbI['descripcion'] ?? ''), 0, 50);
            $campos = $this->camposBienInmuebleFaltante($dbI, $litDbMap);
            $grupos[] = ['label' => "Omitido: {$desc}", 'campos' => $campos];
        }

        return $grupos;
    }

    /**
     * Build campo-a-campo comparison for a matched pair of bienes inmuebles.
     */
    private function camposBienInmueble(array $bI, array $dbI, array $litDbMap): array
    {
        $campos = [];
        $campos[] = $this->cmpDec('Valor Declarado', $this->parseDecimal($bI['valor_declarado'] ?? '0'), (float)($dbI['valor_declarado'] ?? 0));
        $campos[] = $this->cmpDec('Porcentaje', $this->parseDecimal($bI['porcentaje'] ?? '0'), (float)($dbI['porcentaje'] ?? 0));
        $campos[] = $this->cmpTexto('Descripción', $this->normDesc($bI['descripcion'] ?? ''), $this->normDesc($dbI['descripcion'] ?? ''));

        $vpB = ($bI['vivienda_principal'] ?? 'false') === 'true' ? 'SI' : 'NO';
        $vpDb = ((int)($dbI['es_vivienda_principal'] ?? 0)) === 1 ? 'SI' : 'NO';
        $campos[] = ['campo' => 'Vivienda Principal', 'borrador' => $vpB, 'esperado' => $vpDb, 'correcto' => $vpB === $vpDb];

        $litB = ($bI['bien_litigioso'] ?? 'false') === 'true' ? 'SI' : 'NO';
        $litDb = ((int)($dbI['es_bien_litigioso'] ?? 0)) === 1 ? 'SI' : 'NO';
        $campos[] = ['campo' => 'Bien Litigioso', 'borrador' => $litB, 'esperado' => $litDb, 'correcto' => $litB === $litDb];

        $dbBienId = (int)($dbI['id'] ?? 0);
        $litData = $litDbMap[$dbBienId] ?? null;
        if ($litB === 'SI' || $litDb === 'SI') {
            $campos[] = $this->cmpTexto('Nro. Expediente', trim($bI['num_expediente'] ?? ''), trim($litData['numero_expediente'] ?? ''));
            $campos[] = $this->cmpTexto('Tribunal de la Causa', trim($bI['tribunal_causa'] ?? ''), trim($litData['tribunal_causa'] ?? ''));
            $campos[] = $this->cmpTexto('Partes en Juicio', trim($bI['partes_juicio'] ?? ''), trim($litData['partes_juicio'] ?? ''));
            $campos[] = $this->cmpTexto('Estado del Juicio', trim($bI['estado_juicio'] ?? ''), trim($litData['estado_juicio'] ?? ''));
        }

        $campos[] = $this->cmpTexto('Oficina Registro', trim($bI['oficina_registro'] ?? ''), trim($dbI['oficina_registro'] ?? ''));
        $campos[] = $this->cmpTexto('Nro Registro', trim($bI['nro_registro'] ?? ''), trim($dbI['nro_registro'] ?? ''));
        $campos[] = $this->cmpTexto('Libro', trim($bI['libro'] ?? ''), trim($dbI['libro'] ?? ''));
        $campos[] = $this->cmpTexto('Protocolo', trim($bI['protocolo'] ?? ''), trim($dbI['protocolo'] ?? ''));
        $campos[] = $this->cmpTexto('Trimestre', trim($bI['trimestre'] ?? ''), trim($dbI['trimestre'] ?? ''));
        $campos[] = $this->cmpTexto('Fecha Registro', $this->normFecha($bI['fecha_registro'] ?? ''), $this->normFecha($dbI['fecha_registro'] ?? ''));
        $campos[] = $this->cmpDec('Superficie Construida', $this->parseDecimal($bI['superficie_construida'] ?? '0'), (float)($dbI['superficie_construida'] ?? 0));
        $campos[] = $this->cmpDec('Superficie No Construida', $this->parseDecimal($bI['superficie_no_construida'] ?? '0'), (float)($dbI['superficie_no_construida'] ?? 0));
        $campos[] = $this->cmpDec('Valor Original', $this->parseDecimal($bI['valor_original'] ?? '0'), (float)($dbI['valor_original'] ?? 0));

        return $campos;
    }

    /**
     * Build expected-only fields for a faltante bien inmueble (student didn't enter it).
     */
    private function camposBienInmuebleFaltante(array $dbI, array $litDbMap): array
    {
        $campos = [];
        $campos[] = ['campo' => 'Valor Declarado', 'borrador' => '—', 'esperado' => $this->fmtBs((float)($dbI['valor_declarado'] ?? 0)), 'correcto' => false, 'tipo' => 'omitido'];
        $campos[] = ['campo' => 'Porcentaje', 'borrador' => '—', 'esperado' => number_format((float)($dbI['porcentaje'] ?? 0), 2, ',', '.'), 'correcto' => false, 'tipo' => 'omitido'];
        $campos[] = ['campo' => 'Descripción', 'borrador' => '—', 'esperado' => strtoupper(trim($dbI['descripcion'] ?? '')), 'correcto' => false, 'tipo' => 'omitido'];

        $vpDb = ((int)($dbI['es_vivienda_principal'] ?? 0)) === 1 ? 'SI' : 'NO';
        $campos[] = ['campo' => 'Vivienda Principal', 'borrador' => '—', 'esperado' => $vpDb, 'correcto' => false, 'tipo' => 'omitido'];

        $litDb = ((int)($dbI['es_bien_litigioso'] ?? 0)) === 1 ? 'SI' : 'NO';
        $campos[] = ['campo' => 'Bien Litigioso', 'borrador' => '—', 'esperado' => $litDb, 'correcto' => false, 'tipo' => 'omitido'];

        if ($litDb === 'SI') {
            $dbBienId = (int)($dbI['id'] ?? 0);
            $litData = $litDbMap[$dbBienId] ?? null;
            $campos[] = ['campo' => 'Nro. Expediente', 'borrador' => '—', 'esperado' => trim($litData['numero_expediente'] ?? ''), 'correcto' => false, 'tipo' => 'omitido'];
            $campos[] = ['campo' => 'Tribunal de la Causa', 'borrador' => '—', 'esperado' => trim($litData['tribunal_causa'] ?? ''), 'correcto' => false, 'tipo' => 'omitido'];
            $campos[] = ['campo' => 'Partes en Juicio', 'borrador' => '—', 'esperado' => trim($litData['partes_juicio'] ?? ''), 'correcto' => false, 'tipo' => 'omitido'];
            $campos[] = ['campo' => 'Estado del Juicio', 'borrador' => '—', 'esperado' => trim($litData['estado_juicio'] ?? ''), 'correcto' => false, 'tipo' => 'omitido'];
        }

        $campos[] = ['campo' => 'Oficina Registro', 'borrador' => '—', 'esperado' => trim($dbI['oficina_registro'] ?? ''), 'correcto' => false, 'tipo' => 'omitido'];
        $campos[] = ['campo' => 'Nro Registro', 'borrador' => '—', 'esperado' => trim($dbI['nro_registro'] ?? ''), 'correcto' => false, 'tipo' => 'omitido'];
        $campos[] = ['campo' => 'Fecha Registro', 'borrador' => '—', 'esperado' => $this->normFecha($dbI['fecha_registro'] ?? ''), 'correcto' => false, 'tipo' => 'omitido'];
        $campos[] = ['campo' => 'Valor Original', 'borrador' => '—', 'esperado' => $this->fmtBs((float)($dbI['valor_original'] ?? 0)), 'correcto' => false, 'tipo' => 'omitido'];

        return $campos;
    }

    // ════════════════════════════════════════════════════════
    //  BIENES MUEBLES (12 categorías)
    // ════════════════════════════════════════════════════════

    private function compararBienesMuebles(array $borrador, int $casoId): array
    {
        $secciones = [];

        $stmt = $this->db->prepare("
            SELECT bm.*, cat.nombre AS categoria_nombre, tbm.nombre AS tipo_nombre
            FROM sim_caso_bienes_muebles bm
            LEFT JOIN sim_cat_categorias_bien_mueble cat ON bm.categoria_bien_mueble_id = cat.id
            LEFT JOIN sim_cat_tipos_bien_mueble tbm ON bm.tipo_bien_mueble_id = tbm.id
            WHERE bm.caso_estudio_id = :id AND bm.deleted_at IS NULL
            ORDER BY bm.categoria_bien_mueble_id ASC, bm.id ASC
        ");
        $stmt->bindValue(':id', $casoId, PDO::PARAM_INT);
        $stmt->execute();
        $allDb = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $dbByCat = [];
        foreach ($allDb as $m) { $dbByCat[(int)$m['categoria_bien_mueble_id']][] = $m; }

        // Tribunal data for muebles
        $litDbMap = $this->getLitigiosos($casoId, 'Mueble');

        foreach (self::MUEBLE_MAP as $borradorKey => $catId) {
            $label = self::MUEBLE_LABELS[$catId] ?? "Cat.{$catId}";
            $itemsB = $borrador[$borradorKey] ?? [];
            $itemsDb = $dbByCat[$catId] ?? [];

            if (empty($itemsB) && empty($itemsDb)) continue;

            $grupos = [];

            $grupos[] = ['label' => 'Cantidad', 'campos' => [
                ['campo' => "Cantidad de {$label}", 'borrador' => (string)count($itemsB), 'esperado' => (string)count($itemsDb), 'correcto' => count($itemsB) === count($itemsDb)],
            ]];

            // ── PASADA 1: Score-based matching ──
            $dbUsados = [];
            $bSinMatch = []; // indices of borrador items without match
            foreach ($itemsB as $i => $bI) {
                $valorB = $this->parseDecimal($bI['valor_declarado'] ?? '0');
                $tipoB = (int)($bI['tipo_bien'] ?? 0);
                $descB = $this->normDesc($bI['descripcion'] ?? '');

                $bestJ = -1; $bestScore = 0;
                foreach ($itemsDb as $j => $dbI) {
                    if (in_array($j, $dbUsados)) continue;
                    $score = 0;
                    $tipoDb = (int)($dbI['tipo_bien_mueble_id'] ?? 0);
                    $descDb = $this->normDesc($dbI['descripcion'] ?? '');
                    $valorDb = (float)($dbI['valor_declarado'] ?? 0);
                    if ($tipoB > 0 && $tipoDb > 0 && $tipoB === $tipoDb) $score += 3;
                    if ($descB !== '' && $descDb !== '' && $descB === $descDb) $score += 2;
                    if (abs($valorB - $valorDb) < 0.01) $score += 1;
                    if ($score > $bestScore) { $bestScore = $score; $bestJ = $j; }
                }

                if ($bestJ >= 0) {
                    $dbUsados[] = $bestJ;
                    $campos = $this->camposBienMueble($bI, $itemsDb[$bestJ], $litDbMap, $catId);
                    $desc = mb_substr(trim($itemsDb[$bestJ]['descripcion'] ?? $itemsDb[$bestJ]['tipo_nombre'] ?? "Bien #" . ($i+1)), 0, 40);
                    $grupos[] = ['label' => "#" . ($i+1) . ": {$desc}", 'campos' => $campos];
                } else {
                    $bSinMatch[] = $i;
                }
            }

            // ── PASADA 2: Ordinal fallback — emparejar sobrantes por posición ──
            $dbSinMatch = [];
            foreach ($itemsDb as $j => $dbI) {
                if (!in_array($j, $dbUsados)) $dbSinMatch[] = $j;
            }

            $minPar = min(count($bSinMatch), count($dbSinMatch));
            for ($k = 0; $k < $minPar; $k++) {
                $i = $bSinMatch[$k];
                $j = $dbSinMatch[$k];
                $bI = $itemsB[$i];
                $dbI = $itemsDb[$j];
                $campos = $this->camposBienMueble($bI, $dbI, $litDbMap, $catId);
                $desc = mb_substr(trim($dbI['descripcion'] ?? $dbI['tipo_nombre'] ?? "Bien #" . ($i+1)), 0, 40);
                $grupos[] = ['label' => "#" . ($i+1) . ": {$desc}", 'campos' => $campos];
            }

            // ── Sobrantes: bienes del estudiante que NO existen en el caso ──
            for ($k = $minPar; $k < count($bSinMatch); $k++) {
                $i = $bSinMatch[$k];
                $bI = $itemsB[$i];
                $desc = mb_substr(trim($bI['descripcion'] ?? ''), 0, 40);
                $campos = $this->camposBienMuebleSobrante($bI, $catId);
                $grupos[] = ['label' => "De más: {$desc}", 'campos' => $campos];
            }

            // ── Faltantes: bienes del caso que el estudiante NO ingresó ──
            for ($k = $minPar; $k < count($dbSinMatch); $k++) {
                $j = $dbSinMatch[$k];
                $dbI = $itemsDb[$j];
                $desc = mb_substr(trim($dbI['descripcion'] ?? $dbI['tipo_nombre'] ?? ''), 0, 40);
                $campos = $this->camposBienMuebleFaltante($dbI, $litDbMap, $catId);
                $grupos[] = ['label' => "Omitido: {$desc}", 'campos' => $campos];
            }

            if (!empty($grupos)) {
                $secciones[] = ['titulo' => "Bienes Muebles — {$label}", 'grupos' => $grupos];
            }
        }

        return $secciones;
    }

    /**
     * Build campo-a-campo comparison for a matched pair of bienes muebles.
     */
    private function camposBienMueble(array $bI, array $dbI, array $litDbMap, int $catId = 0): array
    {
        $campos = [];

        // Solo mostrar "Tipo de Bien" si la categoría tiene subtipos
        if (!in_array($catId, self::CATS_SIN_SUBTIPO, true)) {
            $tipoB = (int)($bI['tipo_bien'] ?? 0);
            $tipoDb = (int)($dbI['tipo_bien_mueble_id'] ?? 0);
            $tipoNombreDb = ($tipoDb > 0) ? ($dbI['tipo_nombre'] ?? '') : '—';
            $tipoNombreB = ($tipoB > 0) ? ($bI['tipo_bien_nombre'] ?? '') : '—';
            if ($tipoNombreDb === '' || $tipoNombreDb === '0') $tipoNombreDb = '—';
            if ($tipoNombreB === '' || $tipoNombreB === '0') $tipoNombreB = '—';
            $campos[] = ['campo' => 'Tipo de Bien', 'borrador' => $tipoNombreB, 'esperado' => $tipoNombreDb,
                         'correcto' => ($tipoNombreB === '—' && $tipoNombreDb === '—') || ($tipoB > 0 && $tipoDb > 0 && $tipoB === $tipoDb)];
        }

        $campos[] = $this->cmpDec('Valor Declarado', $this->parseDecimal($bI['valor_declarado'] ?? '0'), (float)($dbI['valor_declarado'] ?? 0));
        $campos[] = $this->cmpDec('Porcentaje', $this->parseDecimal($bI['porcentaje'] ?? '0'), (float)($dbI['porcentaje'] ?? 0));
        $campos[] = $this->cmpTexto('Descripción', $this->normDesc($bI['descripcion'] ?? ''), $this->normDesc($dbI['descripcion'] ?? ''));

        // Bien litigioso
        $litB = ($bI['bien_litigioso'] ?? 'false') === 'true' ? 'SI' : 'NO';
        $litDb = ((int)($dbI['es_bien_litigioso'] ?? 0)) === 1 ? 'SI' : 'NO';
        $campos[] = ['campo' => 'Bien Litigioso', 'borrador' => $litB, 'esperado' => $litDb, 'correcto' => $litB === $litDb];

        // Datos del tribunal
        $dbBienId = (int)($dbI['id'] ?? 0);
        $litData = $litDbMap[$dbBienId] ?? null;
        if ($litB === 'SI' || $litDb === 'SI') {
            $campos[] = $this->cmpTexto('Nro. Expediente', trim($bI['num_expediente'] ?? ''), trim($litData['numero_expediente'] ?? ''));
            $campos[] = $this->cmpTexto('Tribunal de la Causa', trim($bI['tribunal_causa'] ?? ''), trim($litData['tribunal_causa'] ?? ''));
            $campos[] = $this->cmpTexto('Partes en Juicio', trim($bI['partes_juicio'] ?? ''), trim($litData['partes_juicio'] ?? ''));
            $campos[] = $this->cmpTexto('Estado del Juicio', trim($bI['estado_juicio'] ?? ''), trim($litData['estado_juicio'] ?? ''));
        }

        return $campos;
    }

    /**
     * Build a descriptive summary string for a sobrante bien mueble.
     * e.g. "90.50% de juju. Tipo: Cuenta de Ahorros, Valor: 4.545,00 Bs"
     */
    private function resumenBienMueble(array $bI, string $catLabel): string
    {
        $parts = [];
        $pct = trim($bI['porcentaje'] ?? '');
        $desc = trim($bI['descripcion'] ?? '');
        if ($pct && $pct !== '0') {
            $parts[] = "{$pct}% de {$desc}";
        } else {
            $parts[] = $desc ?: $catLabel;
        }
        $tipo = trim($bI['tipo_bien_nombre'] ?? '');
        if ($tipo && $tipo !== '0') $parts[] = "Tipo: {$tipo}";
        $val = $this->fmtBs($this->parseDecimal($bI['valor_declarado'] ?? '0'));
        $parts[] = "Valor: {$val}";
        return implode('. ', array_filter($parts));
    }

    /**
     * Build student-only fields for a sobrante bien mueble (student entered it but it doesn't exist in case).
     * All esperado values are "—", all borrador show the student's value.
     */
    private function camposBienMuebleSobrante(array $bI, int $catId = 0): array
    {
        $campos = [];

        // Solo mostrar "Tipo de Bien" si la categoría tiene subtipos
        if (!in_array($catId, self::CATS_SIN_SUBTIPO, true)) {
            $tipoB = (int)($bI['tipo_bien'] ?? 0);
            $tipoNombreB = ($tipoB > 0) ? ($bI['tipo_bien_nombre'] ?? '') : '—';
            if ($tipoNombreB === '' || $tipoNombreB === '0') $tipoNombreB = '—';
            $campos[] = ['campo' => 'Tipo de Bien', 'borrador' => $tipoNombreB, 'esperado' => '—', 'correcto' => false, 'tipo' => 'sobrante'];
        }

        $campos[] = ['campo' => 'Valor Declarado', 'borrador' => $this->fmtBs($this->parseDecimal($bI['valor_declarado'] ?? '0')), 'esperado' => '—', 'correcto' => false, 'tipo' => 'sobrante'];
        $campos[] = ['campo' => 'Porcentaje', 'borrador' => number_format($this->parseDecimal($bI['porcentaje'] ?? '0'), 2, ',', '.'), 'esperado' => '—', 'correcto' => false, 'tipo' => 'sobrante'];
        $campos[] = ['campo' => 'Descripción', 'borrador' => strtoupper(trim($bI['descripcion'] ?? '')), 'esperado' => '—', 'correcto' => false, 'tipo' => 'sobrante'];

        $litB = in_array(strtolower($bI['bien_litigioso'] ?? ''), ['true', 'si', '1'], true) ? 'SI' : 'NO';
        $campos[] = ['campo' => 'Bien Litigioso', 'borrador' => $litB, 'esperado' => '—', 'correcto' => false, 'tipo' => 'sobrante'];

        if ($litB === 'SI') {
            $campos[] = ['campo' => 'Nro. Expediente', 'borrador' => trim($bI['num_expediente'] ?? ''), 'esperado' => '—', 'correcto' => false, 'tipo' => 'sobrante'];
            $campos[] = ['campo' => 'Tribunal de la Causa', 'borrador' => trim($bI['tribunal_causa'] ?? ''), 'esperado' => '—', 'correcto' => false, 'tipo' => 'sobrante'];
            $campos[] = ['campo' => 'Partes en Juicio', 'borrador' => trim($bI['partes_juicio'] ?? ''), 'esperado' => '—', 'correcto' => false, 'tipo' => 'sobrante'];
            $campos[] = ['campo' => 'Estado del Juicio', 'borrador' => trim($bI['estado_juicio'] ?? ''), 'esperado' => '—', 'correcto' => false, 'tipo' => 'sobrante'];
        }

        return $campos;
    }

    /**
     * Build expected-only fields for a faltante bien mueble (student didn't enter it).
     * All borrador values are "—", all esperado show the DB value.
     */
    private function camposBienMuebleFaltante(array $dbI, array $litDbMap, int $catId = 0): array
    {
        $campos = [];

        // Solo mostrar "Tipo de Bien" si la categoría tiene subtipos
        if (!in_array($catId, self::CATS_SIN_SUBTIPO, true)) {
            $tipoNombre = $dbI['tipo_nombre'] ?? '';
            $campos[] = ['campo' => 'Tipo de Bien', 'borrador' => '—', 'esperado' => $tipoNombre, 'correcto' => false, 'tipo' => 'omitido'];
        }
        $campos[] = ['campo' => 'Valor Declarado', 'borrador' => '—', 'esperado' => $this->fmtBs((float)($dbI['valor_declarado'] ?? 0)), 'correcto' => false, 'tipo' => 'omitido'];
        $campos[] = ['campo' => 'Porcentaje', 'borrador' => '—', 'esperado' => number_format((float)($dbI['porcentaje'] ?? 0), 2, ',', '.'), 'correcto' => false, 'tipo' => 'omitido'];
        $campos[] = ['campo' => 'Descripción', 'borrador' => '—', 'esperado' => strtoupper(trim($dbI['descripcion'] ?? '')), 'correcto' => false, 'tipo' => 'omitido'];

        $litDb = ((int)($dbI['es_bien_litigioso'] ?? 0)) === 1 ? 'SI' : 'NO';
        $campos[] = ['campo' => 'Bien Litigioso', 'borrador' => '—', 'esperado' => $litDb, 'correcto' => false, 'tipo' => 'omitido'];

        if ($litDb === 'SI') {
            $dbBienId = (int)($dbI['id'] ?? 0);
            $litData = $litDbMap[$dbBienId] ?? null;
            $campos[] = ['campo' => 'Nro. Expediente', 'borrador' => '—', 'esperado' => trim($litData['numero_expediente'] ?? ''), 'correcto' => false, 'tipo' => 'omitido'];
            $campos[] = ['campo' => 'Tribunal de la Causa', 'borrador' => '—', 'esperado' => trim($litData['tribunal_causa'] ?? ''), 'correcto' => false, 'tipo' => 'omitido'];
            $campos[] = ['campo' => 'Partes en Juicio', 'borrador' => '—', 'esperado' => trim($litData['partes_juicio'] ?? ''), 'correcto' => false, 'tipo' => 'omitido'];
            $campos[] = ['campo' => 'Estado del Juicio', 'borrador' => '—', 'esperado' => trim($litData['estado_juicio'] ?? ''), 'correcto' => false, 'tipo' => 'omitido'];
        }

        return $campos;
    }

    // ════════════════════════════════════════════════════════
    //  PASIVOS DEUDA
    // ════════════════════════════════════════════════════════

    private function compararPasivosDeuda(array $borrador, int $casoId): array
    {
        $secciones = [];
        $deudaNombreMap = [
            'pasivos_deuda_tdc'   => 'Tarjetas de Crédito',
            'pasivos_deuda_ch'    => 'Crédito Hipotecario',
            'pasivos_deuda_pce'   => 'Préstamos Cuentas y Efectos por Pagar',
            'pasivos_deuda_otros' => 'Otros',
        ];

        $stmt = $this->db->prepare("
            SELECT pd.*, tpd.nombre AS tipo_nombre
            FROM sim_caso_pasivos_deuda pd
            LEFT JOIN sim_cat_tipos_pasivo_deuda tpd ON pd.tipo_pasivo_deuda_id = tpd.id
            WHERE pd.caso_estudio_id = :id AND pd.deleted_at IS NULL
            ORDER BY pd.tipo_pasivo_deuda_id ASC, pd.id ASC
        ");
        $stmt->bindValue(':id', $casoId, PDO::PARAM_INT);
        $stmt->execute();
        $allDb = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Build dynamic type ID map
        $tipoStmt = $this->db->query("SELECT id, nombre FROM sim_cat_tipos_pasivo_deuda ORDER BY id");
        $byNombre = [];
        foreach ($tipoStmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $byNombre[mb_strtoupper(trim($r['nombre']))] = (int)$r['id'];
        }

        $dbByTipo = [];
        foreach ($allDb as $d) { $dbByTipo[(int)$d['tipo_pasivo_deuda_id']][] = $d; }

        foreach ($deudaNombreMap as $borradorKey => $nombre) {
            $tipoId = $byNombre[mb_strtoupper(trim($nombre))] ?? null;
            if (!$tipoId) continue;

            $itemsB = $borrador[$borradorKey] ?? [];
            $itemsDb = $dbByTipo[$tipoId] ?? [];
            if (empty($itemsB) && empty($itemsDb)) continue;

            $grupos = $this->compararItemsGenerico($itemsB, $itemsDb, $nombre);
            if (!empty($grupos)) {
                $secciones[] = ['titulo' => "Pasivos Deuda — {$nombre}", 'grupos' => $grupos];
            }
        }

        return $secciones;
    }

    // ════════════════════════════════════════════════════════
    //  PASIVOS GASTOS
    // ════════════════════════════════════════════════════════

    private function compararPasivosGastos(array $borrador, int $casoId): array
    {
        $itemsB = $borrador['pasivos_gastos'] ?? [];
        $stmt = $this->db->prepare("
            SELECT pg.*, tpg.nombre AS tipo_nombre
            FROM sim_caso_pasivos_gastos pg
            LEFT JOIN sim_cat_tipos_pasivo_gasto tpg ON pg.tipo_pasivo_gasto_id = tpg.id
            WHERE pg.caso_estudio_id = :id AND pg.deleted_at IS NULL ORDER BY pg.id ASC
        ");
        $stmt->bindValue(':id', $casoId, PDO::PARAM_INT);
        $stmt->execute();
        $itemsDb = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($itemsB) && empty($itemsDb)) return [];
        return $this->compararItemsGenerico($itemsB, $itemsDb, 'Gasto');
    }

    // ════════════════════════════════════════════════════════
    //  EXENCIONES / EXONERACIONES (tipo + descripcion + valor)
    // ════════════════════════════════════════════════════════

    private function compararExclusion(array $borrador, string $borradorKey, string $tabla, int $casoId): array
    {
        $allowed = ['sim_caso_exenciones', 'sim_caso_exoneraciones'];
        if (!in_array($tabla, $allowed)) return [];

        $itemsB = $borrador[$borradorKey] ?? [];
        $stmt = $this->db->prepare("SELECT *, tipo AS tipo_nombre FROM {$tabla} WHERE caso_estudio_id = :id AND deleted_at IS NULL ORDER BY id ASC");
        $stmt->bindValue(':id', $casoId, PDO::PARAM_INT);
        $stmt->execute();
        $itemsDb = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($itemsB) && empty($itemsDb)) return [];
        $label = ($borradorKey === 'exenciones') ? 'Exención' : 'Exoneración';

        $grupos = [];

        $grupos[] = ['label' => 'Cantidad', 'campos' => [
            ['campo' => "Cantidad de {$label}", 'borrador' => (string)count($itemsB), 'esperado' => (string)count($itemsDb), 'correcto' => count($itemsB) === count($itemsDb)],
        ]];

        // ── PASADA 1: Score-based matching (tipo +3, descripcion +2, valor +1) ──
        $dbUsados = [];
        $bSinMatch = [];
        foreach ($itemsB as $i => $bI) {
            $tipoB  = strtoupper(trim($bI['tipo'] ?? ''));
            $descB  = $this->normDesc($bI['descripcion'] ?? '');
            $valorB = $this->parseDecimal($bI['valor_declarado'] ?? '0');

            $bestJ = -1; $bestScore = 0;
            foreach ($itemsDb as $j => $dbI) {
                if (in_array($j, $dbUsados)) continue;
                $score = 0;
                $tipoDb  = strtoupper(trim($dbI['tipo'] ?? ''));
                $descDb  = $this->normDesc($dbI['descripcion'] ?? '');
                $valorDb = (float)($dbI['valor_declarado'] ?? 0);
                if ($tipoB !== '' && $tipoDb !== '' && $tipoB === $tipoDb) $score += 3;
                if ($descB !== '' && $descDb !== '' && $descB === $descDb) $score += 2;
                if (abs($valorB - $valorDb) < 0.01) $score += 1;
                if ($score > $bestScore) { $bestScore = $score; $bestJ = $j; }
            }

            if ($bestJ >= 0) {
                $dbUsados[] = $bestJ;
                $dbI = $itemsDb[$bestJ];
                $campos = [];
                $campos[] = $this->cmpTexto('Tipo', $tipoB, strtoupper(trim($dbI['tipo'] ?? '')));
                $campos[] = $this->cmpTexto('Descripción', $this->normDesc($bI['descripcion'] ?? ''), $this->normDesc($dbI['descripcion'] ?? ''));
                $campos[] = $this->cmpDec('Valor Declarado', $valorB, (float)($dbI['valor_declarado'] ?? 0));
                $desc = mb_substr(trim($dbI['descripcion'] ?? $dbI['tipo'] ?? ''), 0, 40);
                $grupos[] = ['label' => "{$label} #" . ($i+1) . ": {$desc}", 'campos' => $campos];
            } else {
                $bSinMatch[] = $i;
            }
        }

        // ── PASADA 2: Ordinal fallback ──
        $dbSinMatch = [];
        foreach ($itemsDb as $j => $dbI) {
            if (!in_array($j, $dbUsados)) $dbSinMatch[] = $j;
        }
        $minPar = min(count($bSinMatch), count($dbSinMatch));
        for ($k = 0; $k < $minPar; $k++) {
            $i = $bSinMatch[$k]; $j = $dbSinMatch[$k];
            $bI = $itemsB[$i]; $dbI = $itemsDb[$j];
            $campos = [];
            $campos[] = $this->cmpTexto('Tipo', strtoupper(trim($bI['tipo'] ?? '')), strtoupper(trim($dbI['tipo'] ?? '')));
            $campos[] = $this->cmpTexto('Descripción', $this->normDesc($bI['descripcion'] ?? ''), $this->normDesc($dbI['descripcion'] ?? ''));
            $campos[] = $this->cmpDec('Valor Declarado', $this->parseDecimal($bI['valor_declarado'] ?? '0'), (float)($dbI['valor_declarado'] ?? 0));
            $desc = mb_substr(trim($dbI['descripcion'] ?? $dbI['tipo'] ?? ''), 0, 40);
            $grupos[] = ['label' => "{$label} #" . ($i+1) . ": {$desc}", 'campos' => $campos];
        }

        // ── Sobrantes ──
        for ($k = $minPar; $k < count($bSinMatch); $k++) {
            $i = $bSinMatch[$k];
            $bI = $itemsB[$i];
            $desc = mb_substr(trim($bI['descripcion'] ?? ''), 0, 40);
            $campos = [
                ['campo' => 'Tipo', 'borrador' => strtoupper(trim($bI['tipo'] ?? '')), 'esperado' => '—', 'correcto' => false, 'tipo' => 'sobrante'],
                ['campo' => 'Descripción', 'borrador' => strtoupper(trim($bI['descripcion'] ?? '')), 'esperado' => '—', 'correcto' => false, 'tipo' => 'sobrante'],
                ['campo' => 'Valor Declarado', 'borrador' => $this->fmtBs($this->parseDecimal($bI['valor_declarado'] ?? '0')), 'esperado' => '—', 'correcto' => false, 'tipo' => 'sobrante'],
            ];
            $grupos[] = ['label' => "De más: {$desc}", 'campos' => $campos];
        }

        // ── Faltantes ──
        for ($k = $minPar; $k < count($dbSinMatch); $k++) {
            $j = $dbSinMatch[$k];
            $dbI = $itemsDb[$j];
            $desc = mb_substr(trim($dbI['descripcion'] ?? $dbI['tipo'] ?? ''), 0, 40);
            $campos = [
                ['campo' => 'Tipo', 'borrador' => '—', 'esperado' => strtoupper(trim($dbI['tipo'] ?? '')), 'correcto' => false, 'tipo' => 'omitido'],
                ['campo' => 'Descripción', 'borrador' => '—', 'esperado' => strtoupper(trim($dbI['descripcion'] ?? '')), 'correcto' => false, 'tipo' => 'omitido'],
                ['campo' => 'Valor Declarado', 'borrador' => '—', 'esperado' => $this->fmtBs((float)($dbI['valor_declarado'] ?? 0)), 'correcto' => false, 'tipo' => 'omitido'],
            ];
            $grupos[] = ['label' => "Omitido: {$desc}", 'campos' => $campos];
        }

        return $grupos;
    }

    // ════════════════════════════════════════════════════════
    //  COMPARADOR GENÉRICO (valor_declarado + descripcion)
    // ════════════════════════════════════════════════════════

    private function compararItemsGenerico(array $itemsB, array $itemsDb, string $label): array
    {
        $grupos = [];

        $grupos[] = ['label' => 'Cantidad', 'campos' => [
            ['campo' => "Cantidad de {$label}", 'borrador' => (string)count($itemsB), 'esperado' => (string)count($itemsDb), 'correcto' => count($itemsB) === count($itemsDb)],
        ]];

        // ── PASADA 1: Score-based matching (descripcion +2, valor +1) ──
        $dbUsados = [];
        $bSinMatch = [];
        foreach ($itemsB as $i => $bI) {
            $valorB = $this->parseDecimal($bI['valor_declarado'] ?? '0');
            $descB = $this->normDesc($bI['descripcion'] ?? '');

            $bestJ = -1; $bestScore = 0;
            foreach ($itemsDb as $j => $dbI) {
                if (in_array($j, $dbUsados)) continue;
                $score = 0;
                $descDb = $this->normDesc($dbI['descripcion'] ?? '');
                $valorDb = (float)($dbI['valor_declarado'] ?? 0);
                if ($descB !== '' && $descDb !== '' && $descB === $descDb) $score += 2;
                if (abs($valorB - $valorDb) < 0.01) $score += 1;
                if ($score > $bestScore) { $bestScore = $score; $bestJ = $j; }
            }

            if ($bestJ >= 0) {
                $dbUsados[] = $bestJ;
                $dbI = $itemsDb[$bestJ];
                $campos = [];
                $campos[] = $this->cmpDec('Valor Declarado', $valorB, (float)($dbI['valor_declarado'] ?? 0));
                $campos[] = $this->cmpDec('Porcentaje', $this->parseDecimal($bI['porcentaje'] ?? '0'), (float)($dbI['porcentaje'] ?? 0));
                $campos[] = $this->cmpTexto('Descripción', $this->normDesc($bI['descripcion'] ?? ''), $this->normDesc($dbI['descripcion'] ?? ''));
                $desc = mb_substr(trim($dbI['descripcion'] ?? $dbI['tipo_nombre'] ?? ''), 0, 40);
                $grupos[] = ['label' => "{$label} #" . ($i+1) . ": {$desc}", 'campos' => $campos];
            } else {
                $bSinMatch[] = $i;
            }
        }

        // ── PASADA 2: Ordinal fallback ──
        $dbSinMatch = [];
        foreach ($itemsDb as $j => $dbI) {
            if (!in_array($j, $dbUsados)) $dbSinMatch[] = $j;
        }
        $minPar = min(count($bSinMatch), count($dbSinMatch));
        for ($k = 0; $k < $minPar; $k++) {
            $i = $bSinMatch[$k];
            $j = $dbSinMatch[$k];
            $bI = $itemsB[$i]; $dbI = $itemsDb[$j];
            $campos = [];
            $campos[] = $this->cmpDec('Valor Declarado', $this->parseDecimal($bI['valor_declarado'] ?? '0'), (float)($dbI['valor_declarado'] ?? 0));
            $campos[] = $this->cmpDec('Porcentaje', $this->parseDecimal($bI['porcentaje'] ?? '0'), (float)($dbI['porcentaje'] ?? 0));
            $campos[] = $this->cmpTexto('Descripción', $this->normDesc($bI['descripcion'] ?? ''), $this->normDesc($dbI['descripcion'] ?? ''));
            $desc = mb_substr(trim($dbI['descripcion'] ?? $dbI['tipo_nombre'] ?? ''), 0, 40);
            $grupos[] = ['label' => "{$label} #" . ($i+1) . ": {$desc}", 'campos' => $campos];
        }

        // ── Sobrantes ──
        for ($k = $minPar; $k < count($bSinMatch); $k++) {
            $i = $bSinMatch[$k];
            $bI = $itemsB[$i];
            $desc = mb_substr(trim($bI['descripcion'] ?? ''), 0, 40);
            $campos = [
                ['campo' => 'Valor Declarado', 'borrador' => $this->fmtBs($this->parseDecimal($bI['valor_declarado'] ?? '0')), 'esperado' => '—', 'correcto' => false, 'tipo' => 'sobrante'],
                ['campo' => 'Porcentaje', 'borrador' => number_format($this->parseDecimal($bI['porcentaje'] ?? '0'), 2, ',', '.'), 'esperado' => '—', 'correcto' => false, 'tipo' => 'sobrante'],
                ['campo' => 'Descripción', 'borrador' => strtoupper(trim($bI['descripcion'] ?? '')), 'esperado' => '—', 'correcto' => false, 'tipo' => 'sobrante'],
            ];
            $grupos[] = ['label' => "De más: {$desc}", 'campos' => $campos];
        }

        // ── Faltantes — show full expected fields ──
        for ($k = $minPar; $k < count($dbSinMatch); $k++) {
            $j = $dbSinMatch[$k];
            $dbI = $itemsDb[$j];
            $desc = mb_substr(trim($dbI['descripcion'] ?? $dbI['tipo_nombre'] ?? ''), 0, 40);
            $campos = [
                ['campo' => 'Valor Declarado', 'borrador' => '—', 'esperado' => $this->fmtBs((float)($dbI['valor_declarado'] ?? 0)), 'correcto' => false, 'tipo' => 'omitido'],
                ['campo' => 'Porcentaje', 'borrador' => '—', 'esperado' => number_format((float)($dbI['porcentaje'] ?? 0), 2, ',', '.'), 'correcto' => false, 'tipo' => 'omitido'],
                ['campo' => 'Descripción', 'borrador' => '—', 'esperado' => strtoupper(trim($dbI['descripcion'] ?? '')), 'correcto' => false, 'tipo' => 'omitido'],
            ];
            $grupos[] = ['label' => "Omitido: {$desc}", 'campos' => $campos];
        }

        return $grupos;
    }

    // ════════════════════════════════════════════════════════
    //  AUTOLIQUIDACIÓN (filas 1-14)
    // ════════════════════════════════════════════════════════

    private function compararAutoliquidacion(BorradorService $bs, int $casoId, float $ut): array
    {
        $tIB = $bs->getTotalBienesInmuebles();
        $tMB = $bs->getTotalBienesMuebles();
        $pBB = $tIB + $tMB;
        $dB  = $bs->getTotalDesgravamenes();
        $exB = $bs->getTotalExenciones();
        $eoB = $bs->getTotalExoneraciones();
        $teB = $dB + $exB + $eoB;
        $anB = max(0, $pBB - $teB);
        $tPB = $bs->getTotalPasivos();
        $pNB = max(0, $anB - $tPB);

        $tIDb = $this->sumFromTable('sim_caso_bienes_inmuebles', $casoId);
        $tMDb = $this->sumFromTable('sim_caso_bienes_muebles', $casoId);
        $pBDb = $tIDb + $tMDb;
        $dDb  = $this->calcDesgravamenesDb($casoId);
        $exDb = $this->sumFromTable('sim_caso_exenciones', $casoId);
        $eoDb = $this->sumFromTable('sim_caso_exoneraciones', $casoId);
        $teDb = $dDb + $exDb + $eoDb;
        $anDb = max(0, $pBDb - $teDb);
        $tPDDb = $this->sumFromTable('sim_caso_pasivos_deuda', $casoId);
        $tPGDb = $this->sumFromTable('sim_caso_pasivos_gastos', $casoId);
        $tPDb  = $tPDDb + $tPGDb;
        $pNDb  = max(0, $anDb - $tPDb);

        // Líneas 12-14: usar TributoCalculator para ambos lados
        $herederosB  = $bs->getHerederosDetalle();
        $numHB       = count($herederosB);
        $herederosDb = $this->getHerederosDbForCalc($casoId);
        $numHDb      = count($herederosDb);

        // Borrador: usar overrides manuales si el estudiante los ingresó
        $calculoManual = $bs->getBorrador()['calculo_manual'] ?? null;
        if ($calculoManual && !empty($calculoManual['herederos'])) {
            $tributoB = TributoCalculator::calcularConOverrides($ut, $herederosB, $calculoManual['herederos'], $this->db);
        } else {
            $tributoB = TributoCalculator::calcular($pNB, $numHB, $ut, $herederosB, $this->db);
        }
        // DB: siempre cálculo automático (ground truth)
        $tributoDb = TributoCalculator::calcular($pNDb, $numHDb, $ut, $herederosDb, $this->db);

        return [
            $this->cmpDec('1. Total Bienes Inmuebles', $tIB, $tIDb),
            $this->cmpDec('2. Total Bienes Muebles', $tMB, $tMDb),
            $this->cmpDec('3. Patrimonio Hereditario Bruto (1+2)', $pBB, $pBDb),
            $this->cmpDec('4. Activo Hereditario Bruto', $pBB, $pBDb),
            $this->cmpDec('5. Desgravámenes', $dB, $dDb),
            $this->cmpDec('6. Exenciones', $exB, $exDb),
            $this->cmpDec('7. Exoneraciones', $eoB, $eoDb),
            $this->cmpDec('8. Total Exclusiones (5+6+7)', $teB, $teDb),
            $this->cmpDec('9. Activo Hereditario Neto (4-8)', $anB, $anDb),
            $this->cmpDec('10. Total Pasivo', $tPB, $tPDb),
            $this->cmpDec('11. Patrimonio Neto Hereditario (9-10)', $pNB, $pNDb),
            $this->cmpDec('12. Impuesto Determinado según Tarifa', $tributoB['linea_12'], $tributoDb['linea_12']),
            $this->cmpDec('13. Reducciones', $tributoB['linea_13'], $tributoDb['linea_13']),
            $this->cmpDec('14. Total Impuesto a Pagar', $tributoB['total_impuesto_a_pagar'], $tributoDb['total_impuesto_a_pagar']),
        ];
    }

    // ════════════════════════════════════════════════════════
    //  CÁLCULO POR HEREDERO (tarifa Art. 7)
    // ════════════════════════════════════════════════════════

    private function compararCalculoHerederos(BorradorService $bs, int $casoId, float $ut): array
    {
        $herederosB  = $bs->getHerederosDetalle();
        $numHB       = count($herederosB);
        if ($numHB === 0) return [];

        // Patrimonio neto borrador
        $tIB = $bs->getTotalBienesInmuebles(); $tMB = $bs->getTotalBienesMuebles();
        $pBB = $tIB + $tMB;
        $teB = $bs->getTotalDesgravamenes() + $bs->getTotalExenciones() + $bs->getTotalExoneraciones();
        $anB = max(0, $pBB - $teB);
        $pNB = max(0, $anB - $bs->getTotalPasivos());

        // Patrimonio neto DB
        $tIDb = $this->sumFromTable('sim_caso_bienes_inmuebles', $casoId);
        $tMDb = $this->sumFromTable('sim_caso_bienes_muebles', $casoId);
        $pBDb = $tIDb + $tMDb;
        $teDb = $this->calcDesgravamenesDb($casoId) + $this->sumFromTable('sim_caso_exenciones', $casoId) + $this->sumFromTable('sim_caso_exoneraciones', $casoId);
        $anDb = max(0, $pBDb - $teDb);
        $tPDb = $this->sumFromTable('sim_caso_pasivos_deuda', $casoId) + $this->sumFromTable('sim_caso_pasivos_gastos', $casoId);
        $pNDb = max(0, $anDb - $tPDb);

        // DB herederos for TributoCalculator
        $herederosDb = $this->getHerederosDbForCalc($casoId);
        $numHDb      = count($herederosDb);

        // Calcular usando TributoCalculator (respeta grupos, Art. 9, etc.)
        // Borrador: usar overrides manuales si el estudiante los ingresó
        $calculoManual = $bs->getBorrador()['calculo_manual'] ?? null;
        if ($calculoManual && !empty($calculoManual['herederos'])) {
            $tributoB = TributoCalculator::calcularConOverrides($ut, $herederosB, $calculoManual['herederos'], $this->db);
        } else {
            $tributoB = TributoCalculator::calcular($pNB, $numHB, $ut, $herederosB, $this->db);
        }
        // DB: siempre cálculo automático (ground truth)
        $tributoDb = TributoCalculator::calcular($pNDb, $numHDb, $ut, $herederosDb, $this->db);

        // Cargar catálogos para display y notas
        $parentescoCat = $this->getParentescoCatalog();
        $gruposTarifa  = $this->getGruposTarifa();

        $result = [];
        foreach ($herederosB as $i => $h) {
            $calcB  = $tributoB['herederos'][$i]  ?? [];
            $calcDb = $tributoDb['herederos'][$i] ?? [];

            $campos = [];
            $campos[] = ['campo' => 'Cuota Parte (UT)',
                         'borrador' => $this->fmtBs($calcB['cuota_parte_ut'] ?? 0),
                         'esperado' => $this->fmtBs($calcDb['cuota_parte_ut'] ?? 0),
                         'correcto' => abs(($calcB['cuota_parte_ut'] ?? 0) - ($calcDb['cuota_parte_ut'] ?? 0)) < 0.01];
            $campos[] = ['campo' => 'Porcentaje (%)',
                         'borrador' => number_format($calcB['porcentaje'] ?? 0, 2, ',', '.') . '%',
                         'esperado' => number_format($calcDb['porcentaje'] ?? 0, 2, ',', '.') . '%',
                         'correcto' => abs(($calcB['porcentaje'] ?? 0) - ($calcDb['porcentaje'] ?? 0)) < 0.01];
            $campos[] = $this->cmpDec('Sustraendo (UT)', $calcB['sustraendo_ut'] ?? 0, $calcDb['sustraendo_ut'] ?? 0);
            $campos[] = $this->cmpDec('Impuesto Determinado (Bs)', $calcB['impuesto_determinado'] ?? 0, $calcDb['impuesto_determinado'] ?? 0);
            $campos[] = $this->cmpDec('Reducción (Bs)', $calcB['reduccion'] ?? 0, $calcDb['reduccion'] ?? 0);
            $campos[] = $this->cmpDec('Impuesto a Pagar (Bs)', $calcB['impuesto_a_pagar'] ?? 0, $calcDb['impuesto_a_pagar'] ?? 0);

            $pid = (int) ($h['parentesco_id'] ?? 0);
            $parNombreB = $parentescoCat[$pid] ?? 'Desconocido';

            // ── Notas diagnósticas por escenario ──
            $notas = [];
            $parIdB  = $pid;
            $parIdDb = (int) ($herederosDb[$i]['parentesco_id'] ?? 0);
            $parNombreDb = $parentescoCat[$parIdDb] ?? 'Desconocido';
            $parentescoOk = ($parIdB === $parIdDb);

            $grupoDb = $gruposTarifa[$parIdDb] ?? 4;

            $cuotaB  = (float) ($calcB['cuota_parte_ut'] ?? 0);
            $cuotaDb = (float) ($calcDb['cuota_parte_ut'] ?? 0);
            $cuotaOk = abs($cuotaB - $cuotaDb) < 0.01;

            $exentoDb = ($grupoDb === 1 && $cuotaDb <= 75.0);
            $impB     = (float) ($calcB['impuesto_a_pagar'] ?? 0);

            // Escenario B: PN ≤ 0 + parentesco incorrecto
            if ($pNDb <= 0 && !$parentescoOk) {
                $notas[] = "El parentesco declarado ({$parNombreB}) no coincide con el correcto ({$parNombreDb}). "
                         . "Además, el Patrimonio Neto Hereditario correcto es ≤ 0, "
                         . "por lo que no hay impuesto a determinar independientemente del parentesco.";
            }
            // Escenario D: PN > 0 + parentesco incorrecto
            elseif ($pNDb > 0 && !$parentescoOk) {
                $notas[] = "El parentesco declarado ({$parNombreB}) no coincide con el correcto ({$parNombreDb}). "
                         . "Esto afecta el grupo de tarifa aplicable y por tanto el porcentaje, sustraendo e impuesto calculado.";
            }

            // Escenario E: PN > 0 + parentesco OK + cuota incorrecta
            if ($pNDb > 0 && $parentescoOk && !$cuotaOk) {
                $notas[] = "La Cuota Parte difiere porque el Patrimonio Neto Hereditario declarado "
                         . "({$this->fmtBs($pNB)} Bs) no coincide con el correcto ({$this->fmtBs($pNDb)} Bs), "
                         . "o porque la cantidad de herederos declarada ({$numHB}) no coincide con la correcta ({$numHDb}).";
            }

            // Escenario F: Heredero exento Art. 9 pero estudiante calculó impuesto
            if ($exentoDb && $impB > 0.01) {
                $notas[] = "Este heredero está exento según el Art. 9 de la Ley "
                         . "(Cuota Parte ≤ 75 UT y parentesco de primer grado). "
                         . "Impuesto correcto: 0,00 Bs.";
            }

            $result[] = [
                'nombre' => $h['nombre'] ?? '', 'cedula' => $h['cedula'] ?? '',
                'parentesco' => $parNombreB, 'campos' => $campos,
                'notas' => $notas,
            ];
        }

        return $result;
    }

    /**
     * Get DB herederos in the format TributoCalculator expects: [{parentesco_id: int}, ...]
     * Only regular herederos (not herederos del premuerto).
     */
    private function getHerederosDbForCalc(int $casoId): array
    {
        $stmt = $this->db->prepare("
            SELECT cp.parentesco_id
            FROM sim_caso_participantes cp
            WHERE cp.caso_estudio_id = :id AND cp.premuerto_padre_id IS NULL
            ORDER BY cp.id ASC
        ");
        $stmt->bindValue(':id', $casoId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Load parentesco_id → grupo_tarifa_id mapping from sim_cat_parentescos.
     * @return array<int, int>
     */
    private function getGruposTarifa(): array
    {
        $stmt = $this->db->query('SELECT id, grupo_tarifa_id FROM sim_cat_parentescos WHERE activo = 1');
        $map = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $map[(int) $row['id']] = $row['grupo_tarifa_id'] !== null
                ? (int) $row['grupo_tarifa_id']
                : 4;
        }
        return $map;
    }

    /**
     * Fetch litigiosos from DB indexed by bien_id for a given tipo (Inmueble/Mueble).
     */
    private function getLitigiosos(int $casoId, string $bienTipo): array
    {
        $stmt = $this->db->prepare("
            SELECT bien_id, numero_expediente, tribunal_causa, partes_juicio, estado_juicio
            FROM sim_caso_bienes_litigiosos
            WHERE caso_estudio_id = :id AND bien_tipo = :tipo
        ");
        $stmt->bindValue(':id', $casoId, PDO::PARAM_INT);
        $stmt->bindValue(':tipo', $bienTipo, PDO::PARAM_STR);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $map = [];
        foreach ($rows as $r) { $map[(int)$r['bien_id']] = $r; }
        return $map;
    }

    /**
     * Normalize a description for comparison: uppercase, strip accents, collapse spaces.
     */
    private function normDesc(string $desc): string
    {
        $desc = mb_strtoupper(trim($desc), 'UTF-8');
        $desc = strtr($desc, [
            'Á'=>'A','É'=>'E','Í'=>'I','Ó'=>'O','Ú'=>'U','Ñ'=>'N','Ü'=>'U',
        ]);
        return preg_replace('/\s+/', ' ', $desc);
    }

    private function cmpDec(string $campo, float $borrador, float $esperado): array
    {
        return ['campo' => $campo, 'borrador' => $this->fmtBs($borrador), 'esperado' => $this->fmtBs($esperado), 'correcto' => abs($borrador - $esperado) < 0.01];
    }

    private function cmpTexto(string $campo, string $borrador, string $esperado): array
    {
        $b = strtoupper(trim($borrador)); $e = strtoupper(trim($esperado));
        return ['campo' => $campo, 'borrador' => $b ?: '—', 'esperado' => $e ?: '—',
                'correcto' => ($b === $e) || ($b === '' && $e === '')];
    }

    private function fmtBs(float $v): string { return number_format($v, 2, ',', '.'); }

    private function parseDecimal(string $v): float
    {
        $v = trim($v);
        if ($v === '' || $v === '0') return 0.0;
        if (str_contains($v, ',')) { $v = str_replace('.', '', $v); $v = str_replace(',', '.', $v); }
        return (float)$v;
    }

    private function normFecha(string $f): string
    {
        $f = trim($f);
        if (!$f) return '';
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $f, $m)) return "{$m[3]}-{$m[2]}-{$m[1]}";
        return substr($f, 0, 10);
    }

    /**
     * Robust document matching — adapted from RSValidator::matchDocumento.
     * Matches borrador document against DB persona's cédula, RIF, pasaporte.
     * Strategy: exact match first, then numeric-only fallback.
     */
    private function matchDocumento(string $docBorrador, array $personaDb): bool
    {
        $docB = mb_strtoupper(trim($docBorrador));
        if (!$docB) return false;

        $tipoCedula = mb_strtoupper(trim($personaDb['tipo_cedula'] ?? ''));
        $cedula     = trim($personaDb['cedula'] ?? '');
        $rif        = mb_strtoupper(trim($personaDb['rif_personal'] ?? ''));
        $pasaporte  = mb_strtoupper(trim($personaDb['pasaporte'] ?? ''));
        $cedulaCompleta = $tipoCedula . $cedula;

        // Paso 1: Comparación exacta
        if ($cedulaCompleta && $docB === $cedulaCompleta) return true;
        if ($rif && $docB === $rif) return true;
        if ($pasaporte && $docB === $pasaporte) return true;
        if ($cedula && $docB === $cedula) return true;

        // Paso 2: Comparación por número puro (solo dígitos)
        $numB = preg_replace('/[^0-9]/', '', $docB);
        if (!$numB) return false;

        if ($cedula && $numB === preg_replace('/[^0-9]/', '', $cedula)) return true;
        if ($rif && $numB === preg_replace('/[^0-9]/', '', $rif)) return true;
        if ($pasaporte && $numB === preg_replace('/[^0-9]/', '', $pasaporte)) return true;

        return false;
    }

    private function findById(array $items, string $key, int $id): ?array
    {
        foreach ($items as $item) { if ((int)($item[$key] ?? 0) === $id) return $item; }
        return null;
    }

    // calcTarifa() removed — now using TributoCalculator::calcular() which reads from DB

    private function getIntentoConCaso(int $intentoId, int $estudianteId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT i.id, i.borrador_json, i.estado, i.rif_sucesoral,
                   ce.id AS caso_id, ce.causante_id, ce.tipo_sucesion,
                   ce.titulo AS caso_titulo
            FROM sim_intentos i
            INNER JOIN sim_caso_asignaciones a ON a.id = i.asignacion_id
            INNER JOIN sim_caso_configs cfg ON cfg.id = a.config_id
            INNER JOIN sim_casos_estudios ce ON ce.id = cfg.caso_id
            WHERE i.id = :int_id AND a.estudiante_id = :est_id LIMIT 1
        ");
        $stmt->bindValue(':int_id', $intentoId, PDO::PARAM_INT);
        $stmt->bindValue(':est_id', $estudianteId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function sumFromTable(string $table, int $casoId): float
    {
        $allowed = ['sim_caso_bienes_inmuebles','sim_caso_bienes_muebles','sim_caso_pasivos_deuda','sim_caso_pasivos_gastos','sim_caso_exenciones','sim_caso_exoneraciones'];
        if (!in_array($table, $allowed)) return 0.0;
        $stmt = $this->db->prepare("SELECT COALESCE(SUM(valor_declarado),0) FROM {$table} WHERE caso_estudio_id = :id AND deleted_at IS NULL");
        $stmt->bindValue(':id', $casoId, PDO::PARAM_INT);
        $stmt->execute();
        return (float)$stmt->fetchColumn();
    }

    private function fetchRows(string $table, int $casoId): array
    {
        $allowed = ['sim_caso_bienes_inmuebles','sim_caso_bienes_muebles'];
        if (!in_array($table, $allowed)) return [];
        $stmt = $this->db->prepare("SELECT * FROM {$table} WHERE caso_estudio_id = :id AND deleted_at IS NULL ORDER BY id ASC");
        $stmt->bindValue(':id', $casoId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function calcDesgravamenesDb(int $casoId): float
    {
        $total = 0.0;
        $stmt = $this->db->prepare("SELECT COALESCE(SUM(valor_declarado),0) FROM sim_caso_bienes_inmuebles WHERE caso_estudio_id = :id AND es_vivienda_principal = 1 AND deleted_at IS NULL");
        $stmt->bindValue(':id', $casoId, PDO::PARAM_INT);
        $stmt->execute();
        $total += (float)$stmt->fetchColumn();

        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(bm.valor_declarado),0)
            FROM sim_caso_bienes_muebles bm
            INNER JOIN sim_cat_tipos_bien_mueble tbm ON bm.tipo_bien_mueble_id = tbm.id
            WHERE bm.caso_estudio_id = :id AND bm.categoria_bien_mueble_id = 2
              AND (tbm.nombre LIKE '%Seguro de Vida%' OR tbm.nombre LIKE '%montepi%')
              AND bm.deleted_at IS NULL
        ");
        $stmt->bindValue(':id', $casoId, PDO::PARAM_INT);
        $stmt->execute();
        $total += (float)$stmt->fetchColumn();

        return $total;
    }
}
