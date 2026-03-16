# Instrucciones de Implementación — Mejoras al Reporte de Comparación SPDSS

## Contexto

El reporte se genera con mPDF (PHP → HTML → PDF). Las mejoras se dividen en 3 áreas:
1. **Lógica de matching** (PHP backend — comparador)
2. **Estructura del reporte** (HTML template para mPDF)
3. **Diferenciación visual** (CSS inline para mPDF)

El reporte es para **modo Práctica** — se muestran todos los campos con sus valores correctos.

---

## ÁREA 1: LÓGICA DE MATCHING (PHP)

### 1.1 — Matching de bienes muebles por descripción normalizada

**Problema actual:** Muchas categorías de bienes muebles muestran "Ingresado → No encontrado" porque el matching falla. Las categorías donde funciona (Banco, Cuentas y Efectos parcialmente) sugieren que ya existe algo de lógica, pero las demás no emparejan nada.

**Solución:** Normalizar las descripciones antes de comparar y usar un algoritmo de emparejamiento en dos pasadas.

```php
/**
 * Normaliza una descripción para comparación.
 * Convierte a mayúsculas, elimina acentos, trim, y colapsa espacios múltiples.
 */
function normalizarDescripcion(string $desc): string {
    $desc = mb_strtoupper(trim($desc), 'UTF-8');
    // Eliminar acentos
    $desc = strtr($desc, [
        'Á'=>'A','É'=>'E','Í'=>'I','Ó'=>'O','Ú'=>'U',
        'Ñ'=>'N','Ü'=>'U'
    ]);
    // Colapsar espacios múltiples
    $desc = preg_replace('/\s+/', ' ', $desc);
    return $desc;
}

/**
 * Empareja bienes del estudiante con bienes del caso dentro de una misma categoría.
 *
 * Estrategia de dos pasadas:
 *   Pasada 1 — Match exacto por descripción normalizada.
 *   Pasada 2 — Los sobrantes se emparejan por posición ordinal (1° sin match del
 *              estudiante con 1° sin match del caso).
 *
 * Retorna un array con tres grupos:
 *   'emparejados'  => [ ['estudiante' => [...], 'caso' => [...]], ... ]
 *   'sobrantes'    => [ ['estudiante' => [...]], ... ]  // ingresó pero no existe en caso
 *   'faltantes'    => [ ['caso' => [...]], ... ]         // existe en caso pero no ingresó
 */
function emparejarBienes(array $bienesEstudiante, array $bienesCaso): array {
    $resultado = ['emparejados' => [], 'sobrantes' => [], 'faltantes' => []];

    // Indexar caso por descripción normalizada
    $casoIndexado = [];
    foreach ($bienesCaso as $i => $bien) {
        $key = normalizarDescripcion($bien['descripcion'] ?? '');
        $casoIndexado[$key] = ['index' => $i, 'data' => $bien, 'usado' => false];
    }

    $estudianteSinMatch = [];

    // PASADA 1: match exacto por descripción normalizada
    foreach ($bienesEstudiante as $bienEst) {
        $keyEst = normalizarDescripcion($bienEst['descripcion'] ?? '');
        if (isset($casoIndexado[$keyEst]) && !$casoIndexado[$keyEst]['usado']) {
            $resultado['emparejados'][] = [
                'estudiante' => $bienEst,
                'caso'       => $casoIndexado[$keyEst]['data']
            ];
            $casoIndexado[$keyEst]['usado'] = true;
        } else {
            $estudianteSinMatch[] = $bienEst;
        }
    }

    // Recoger bienes del caso que no fueron emparejados
    $casoSinMatch = [];
    foreach ($casoIndexado as $entry) {
        if (!$entry['usado']) {
            $casoSinMatch[] = $entry['data'];
        }
    }

    // PASADA 2: emparejar sobrantes por posición ordinal
    $minCount = min(count($estudianteSinMatch), count($casoSinMatch));
    for ($i = 0; $i < $minCount; $i++) {
        $resultado['emparejados'][] = [
            'estudiante' => $estudianteSinMatch[$i],
            'caso'       => $casoSinMatch[$i]
        ];
    }

    // Lo que queda del estudiante sin emparejar → sobrantes
    for ($i = $minCount; $i < count($estudianteSinMatch); $i++) {
        $resultado['sobrantes'][] = ['estudiante' => $estudianteSinMatch[$i]];
    }

    // Lo que queda del caso sin emparejar → faltantes
    for ($i = $minCount; $i < count($casoSinMatch); $i++) {
        $resultado['faltantes'][] = ['caso' => $casoSinMatch[$i]];
    }

    return $resultado;
}
```

**Dónde aplicar:** En la función que compara cada categoría de bien mueble. Actualmente probablemente tienes un loop simple — reemplázalo con `emparejarBienes()`. Aplica la misma función para:
- Cada una de las 12 categorías de bienes muebles
- Pasivos Deuda (por tipo de pasivo)
- Pasivos Gastos (por tipo de gasto)
- Exenciones
- Exoneraciones

**Importante para herederos:** Los herederos ya se emparejan bien por documento de identidad (cédula/RIF). No cambies eso — documento es un identificador único natural. El matching por descripción solo aplica a bienes/pasivos/exenciones donde no hay un ID único natural.

### 1.2 — Matching de pasivos, exenciones y exoneraciones

**Problema actual:** Los pasivos muestran solo un monto como "Estado" sin desglose de campos. Las exenciones/exoneraciones muestran "0,00 → No encontrado" para todas las filas.

**Solución:** Aplicar el mismo `emparejarBienes()` usando la descripción o el tipo como key de matching. Para pasivos deuda, agrupar primero por `tipo_pasivo_deuda_id` y dentro de cada tipo emparejar por descripción. Para exenciones/exoneraciones, emparejar por descripción normalizada.

```php
// Pasivos Deuda — agrupar por tipo antes de emparejar
function emparejarPasivosPorTipo(array $pasivosEstudiante, array $pasivosCaso): array {
    // 1. Agrupar ambos arrays por tipo_pasivo_deuda_id
    $estPorTipo = agruparPorCampo($pasivosEstudiante, 'tipo_pasivo_deuda_id');
    $casoPorTipo = agruparPorCampo($pasivosCaso, 'tipo_pasivo_deuda_id');

    // 2. Obtener todos los tipos presentes en ambos
    $todosLosTipos = array_unique(array_merge(
        array_keys($estPorTipo),
        array_keys($casoPorTipo)
    ));

    $resultadoPorTipo = [];
    foreach ($todosLosTipos as $tipoId) {
        $estDeTipo  = $estPorTipo[$tipoId] ?? [];
        $casoDeTipo = $casoPorTipo[$tipoId] ?? [];
        $resultadoPorTipo[$tipoId] = emparejarBienes($estDeTipo, $casoDeTipo);
    }

    return $resultadoPorTipo;
}

function agruparPorCampo(array $items, string $campo): array {
    $grupos = [];
    foreach ($items as $item) {
        $key = $item[$campo] ?? 'sin_tipo';
        $grupos[$key][] = $item;
    }
    return $grupos;
}
```

### 1.3 — Estructura del resultado de comparación

**Cambio necesario:** La función central `CompararIntento::evaluar($intento_id)` debe retornar un array estructurado que el template HTML pueda consumir fácilmente. Cada sección debe incluir su conteo propio.

```php
// Estructura del resultado que retorna el comparador
$resultado = [
    'meta' => [
        'caso_nombre'         => 'caso 1',
        'rif'                 => 'V700178940',
        'causante_nombre'     => 'SARMIENTO, MARIA',
        'fecha_fallecimiento' => '28/03/2024',
        'fecha_reporte'       => date('d/m/Y'),
        'ut_aplicable'        => 9.00,
        'total_campos'        => 208,
        'total_correctos'     => 53,
    ],

    // Resumen por sección (para la página 1 del reporte)
    'resumen_secciones' => [
        ['nombre' => 'Herederos',              'correctos' => 8,  'total' => 18],
        ['nombre' => 'Herederos del Premuerto', 'correctos' => 3,  'total' => 8],
        ['nombre' => 'Tipo de Herencia',        'correctos' => 0,  'total' => 2],
        ['nombre' => 'Bienes Inmuebles',        'correctos' => 22, 'total' => 30],
        ['nombre' => 'Banco',                   'correctos' => 5,  'total' => 12],
        // ... etc para cada sección
        ['nombre' => 'Autoliquidación',         'correctos' => 2,  'total' => 14],
        ['nombre' => 'Impuesto por Heredero',   'correctos' => 2,  'total' => 12],
    ],

    // Detalle por sección
    'secciones' => [
        'herederos' => [
            'titulo'    => 'Herederos',
            'correctos' => 8,
            'total'     => 18,
            'campos'    => [
                // Cada campo tiene: nombre, valor_estudiante, valor_correcto,
                // resultado ('correcto'|'incorrecto'|'faltante'|'sobrante'),
                // y opcionalmente 'contexto' (nombre del heredero, nombre del bien, etc.)
                [
                    'contexto'          => 'Heredero #1: RODRIGUEZ JANETSY',
                    'campo'             => 'Parentesco',
                    'valor_estudiante'  => 'OTRO PARIENTE',
                    'valor_correcto'    => 'OTRO',
                    'resultado'         => 'incorrecto'
                ],
                // ...
            ]
        ],
        'bienes_muebles_banco' => [
            'titulo'    => 'Bienes Muebles — Banco',
            'correctos' => 5,
            'total'     => 12,
            'emparejados' => [ /* resultado de emparejarBienes() */ ],
            'sobrantes'   => [ /* bienes que el estudiante ingresó de más */ ],
            'faltantes'   => [ /* bienes que faltaron */ ],
        ],
        // ... etc
    ]
];
```

---

## ÁREA 2: ESTRUCTURA DEL TEMPLATE HTML

### 2.1 — Página 1: Encabezado + Resumen por sección

Después del encabezado actual (caso, RIF, causante, fecha, UT), agregar una tabla-resumen
que muestre el desglose por sección. Esto le da al estudiante un mapa de su desempeño
antes de entrar al detalle.

```html
<!-- Encabezado existente (mantener igual) -->
<div class="encabezado">
    <h1>Reporte de Comparación de Declaración</h1>
    <p>Sistema de Práctica de Declaración Sucesoral SENIAT (SPDSS)</p>
    <!-- ... datos del caso ... -->
</div>

<!-- NUEVO: Score global destacado -->
<div class="score-global">
    <div class="score-numero">53 / 208</div>
    <div class="score-label">Campos correctos (25.5%)</div>
    <!-- Barra visual -->
    <div class="barra-contenedor">
        <div class="barra-relleno" style="width: 25.5%;"></div>
    </div>
</div>

<!-- NUEVO: Tabla resumen por sección -->
<table class="tabla-resumen">
    <thead>
        <tr>
            <th style="text-align: left;">Sección</th>
            <th style="text-align: center;">Correctos</th>
            <th style="text-align: center;">Total</th>
            <th style="text-align: center;">%</th>
            <th style="text-align: left; width: 40%;">Progreso</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($resultado['resumen_secciones'] as $sec): ?>
        <?php $pct = $sec['total'] > 0 ? round($sec['correctos'] / $sec['total'] * 100) : 0; ?>
        <tr>
            <td><?= $sec['nombre'] ?></td>
            <td style="text-align: center;"><?= $sec['correctos'] ?></td>
            <td style="text-align: center;"><?= $sec['total'] ?></td>
            <td style="text-align: center;"><?= $pct ?>%</td>
            <td>
                <div class="barra-mini-contenedor">
                    <div class="barra-mini-relleno
                        <?php if ($pct >= 70): ?> barra-verde
                        <?php elseif ($pct >= 40): ?> barra-amarilla
                        <?php else: ?> barra-roja
                        <?php endif; ?>"
                        style="width: <?= $pct ?>%;">
                    </div>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- Salto de página después del resumen -->
<pagebreak />
```

### 2.2 — Secciones de detalle: Mostrar todo pero diferenciar tipos de resultado

Para cada sección, mostrar un mini-header con el conteo local, luego la tabla completa
de campos. Cada fila tiene una clase CSS según su tipo de resultado.

```html
<!-- Template para una sección genérica (herederos, inmuebles, etc.) -->
<div class="seccion">
    <div class="seccion-header">
        <span class="seccion-titulo">Herederos</span>
        <span class="seccion-conteo">8 / 18 correctos</span>
    </div>

    <!-- Sub-header por item (Heredero #1, Inmueble #2, etc.) -->
    <div class="item-header">Heredero #1: RODRIGUEZ JANETSY</div>

    <table class="tabla-campos">
        <thead>
            <tr>
                <th style="width: 5%;"><!-- icono --></th>
                <th style="width: 25%;">Campo</th>
                <th style="width: 30%;">Su Valor</th>
                <th style="width: 30%;">Valor Correcto</th>
                <th style="width: 10%;">Resultado</th>
            </tr>
        </thead>
        <tbody>
            <!-- Fila CORRECTA -->
            <tr class="fila-correcta">
                <td class="icono-resultado">✓</td>
                <td>Nombre</td>
                <td>RODRIGUEZ JANETSY</td>
                <td>RODRIGUEZ JANETSY</td>
                <td><span class="badge badge-correcto">Correcto</span></td>
            </tr>

            <!-- Fila INCORRECTA (valor diferente) -->
            <tr class="fila-incorrecta">
                <td class="icono-resultado">✗</td>
                <td>Parentesco</td>
                <td>OTRO PARIENTE</td>
                <td>OTRO</td>
                <td><span class="badge badge-incorrecto">Incorrecto</span></td>
            </tr>

            <!-- Fila FALTANTE (no ingresó algo que debía) -->
            <tr class="fila-faltante">
                <td class="icono-resultado">✗</td>
                <td>Fecha Fallecimiento</td>
                <td class="valor-vacio">— No ingresado</td>
                <td>2017-03-02</td>
                <td><span class="badge badge-faltante">Faltante</span></td>
            </tr>

            <!-- Fila SOBRANTE (ingresó algo que no debía) -->
            <tr class="fila-sobrante">
                <td class="icono-resultado">✗</td>
                <td colspan="2">Ingresó: "juju" — Este bien no corresponde al caso</td>
                <td>—</td>
                <td><span class="badge badge-sobrante">Sobrante</span></td>
            </tr>
        </tbody>
    </table>
</div>
```

### 2.3 — Template para bienes emparejados vs sobrantes vs faltantes

Después de aplicar `emparejarBienes()`, el template para bienes muebles cambia:

```html
<div class="seccion">
    <div class="seccion-header">
        <span class="seccion-titulo">Bienes Muebles — Banco</span>
        <span class="seccion-conteo">5 / 12 correctos</span>
    </div>

    <p class="seccion-cantidad">
        Cantidad ingresada: 2 | Cantidad correcta: 2
        <span class="badge badge-correcto">✓</span>
    </p>

    <!-- EMPAREJADOS: comparación campo a campo -->
    <?php foreach ($seccion['emparejados'] as $i => $par): ?>
    <div class="item-header">
        #<?= $i + 1 ?>: <?= $par['caso']['descripcion'] ?>
    </div>
    <table class="tabla-campos">
        <thead>
            <tr>
                <th style="width: 5%;"></th>
                <th style="width: 25%;">Campo</th>
                <th style="width: 30%;">Su Valor</th>
                <th style="width: 30%;">Valor Correcto</th>
                <th style="width: 10%;">Resultado</th>
            </tr>
        </thead>
        <tbody>
            <!-- Comparar cada campo del bien -->
            <?php foreach (compararCamposBien($par['estudiante'], $par['caso']) as $campo): ?>
            <tr class="fila-<?= $campo['resultado'] ?>">
                <td class="icono-resultado"><?= $campo['resultado'] === 'correcto' ? '✓' : '✗' ?></td>
                <td><?= $campo['nombre'] ?></td>
                <td><?= $campo['valor_estudiante'] ?? '—' ?></td>
                <td><?= $campo['valor_correcto'] ?></td>
                <td><span class="badge badge-<?= $campo['resultado'] ?>"><?= ucfirst($campo['resultado']) ?></span></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endforeach; ?>

    <!-- SOBRANTES: bienes que el estudiante ingresó pero no existen en el caso -->
    <?php if (!empty($seccion['sobrantes'])): ?>
    <div class="subseccion-titulo sobrante-titulo">
        Bienes no correspondientes al caso (<?= count($seccion['sobrantes']) ?>)
    </div>
    <?php foreach ($seccion['sobrantes'] as $sob): ?>
    <div class="item-sobrante">
        <span class="badge badge-sobrante">Sobrante</span>
        "<?= $sob['estudiante']['descripcion'] ?>" —
        Este bien fue ingresado pero no existe en el caso de estudio.
        Valor declarado: <?= number_format($sob['estudiante']['valor_declarado'], 2, ',', '.') ?> Bs.
    </div>
    <?php endforeach; ?>
    <?php endif; ?>

    <!-- FALTANTES: bienes que debían estar pero el estudiante no ingresó -->
    <?php if (!empty($seccion['faltantes'])): ?>
    <div class="subseccion-titulo faltante-titulo">
        Bienes faltantes (<?= count($seccion['faltantes']) ?>)
    </div>
    <?php foreach ($seccion['faltantes'] as $fal): ?>
    <div class="item-faltante">
        <span class="badge badge-faltante">Faltante</span>
        "<?= $fal['caso']['descripcion'] ?>" —
        Debía ser ingresado.
        Valor correcto: <?= number_format($fal['caso']['valor_declarado'], 2, ',', '.') ?> Bs.
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>
```

### 2.4 — Sección de Autoliquidación mejorada

La autoliquidación necesita una nota cuando el Patrimonio Neto Hereditario es ≤ 0.

```html
<div class="seccion">
    <div class="seccion-header">
        <span class="seccion-titulo">Autoliquidación del Impuesto</span>
        <span class="seccion-conteo"><?= $autoConteo ?> / <?= $autoTotal ?> correctos</span>
    </div>

    <table class="tabla-autoliquidacion">
        <!-- ... filas de la 1 a la 14 como ya las tienes ... -->
    </table>

    <?php if ($valorCorrecto['patrimonio_neto'] <= 0): ?>
    <div class="nota-informativa">
        <strong>Nota:</strong> El Patrimonio Neto Hereditario correcto es ≤ 0 (los pasivos
        superan los activos), por lo tanto no hay impuesto a determinar. Todos los valores
        de impuesto correctos son 0,00 Bs.
    </div>
    <?php endif; ?>
</div>
```

---

## ÁREA 3: ESTILOS CSS (INLINE PARA mPDF)

mPDF soporta un subconjunto de CSS. Todo debe ir en un `<style>` dentro del HTML
que le pasas a `$mpdf->WriteHTML()`. NO uses CSS externo ni flexbox/grid
(mPDF no los soporta bien). Usa tablas y propiedades básicas.

```css
<style>
    /* ============================================ */
    /* BASE                                         */
    /* ============================================ */
    body {
        font-family: 'DejaVu Sans', sans-serif;
        font-size: 9pt;
        color: #1a1a1a;
        line-height: 1.4;
    }

    /* ============================================ */
    /* ENCABEZADO                                   */
    /* ============================================ */
    .encabezado {
        text-align: center;
        margin-bottom: 15px;
    }
    .encabezado h1 {
        font-size: 14pt;
        color: #1a365d;
        margin-bottom: 2px;
    }
    .encabezado p {
        font-size: 8pt;
        color: #555;
    }

    /* ============================================ */
    /* SCORE GLOBAL                                 */
    /* ============================================ */
    .score-global {
        text-align: center;
        margin: 15px 0;
        padding: 12px;
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 4px;
    }
    .score-numero {
        font-size: 22pt;
        font-weight: bold;
        color: #1a365d;
    }
    .score-label {
        font-size: 10pt;
        color: #666;
        margin-bottom: 8px;
    }
    .barra-contenedor {
        background-color: #e9ecef;
        height: 12px;
        border-radius: 6px;
        margin-top: 6px;
    }
    .barra-relleno {
        background-color: #3182ce;
        height: 12px;
        border-radius: 6px;
    }

    /* ============================================ */
    /* TABLA RESUMEN POR SECCIÓN                    */
    /* ============================================ */
    .tabla-resumen {
        width: 100%;
        border-collapse: collapse;
        margin: 15px 0;
        font-size: 9pt;
    }
    .tabla-resumen th {
        background-color: #1a365d;
        color: #ffffff;
        padding: 6px 8px;
        font-weight: bold;
        font-size: 8pt;
        text-transform: uppercase;
    }
    .tabla-resumen td {
        padding: 5px 8px;
        border-bottom: 1px solid #e2e8f0;
    }
    .tabla-resumen tr:nth-child(even) td {
        background-color: #f7fafc;
    }

    /* Mini barras de progreso dentro de la tabla resumen */
    .barra-mini-contenedor {
        background-color: #e2e8f0;
        height: 8px;
        border-radius: 4px;
    }
    .barra-mini-relleno {
        height: 8px;
        border-radius: 4px;
    }
    .barra-verde  { background-color: #38a169; }
    .barra-amarilla { background-color: #d69e2e; }
    .barra-roja   { background-color: #e53e3e; }

    /* ============================================ */
    /* SECCIONES DE DETALLE                         */
    /* ============================================ */
    .seccion {
        margin-bottom: 15px;
        page-break-inside: avoid; /* mPDF: intentar no partir una sección entre páginas */
    }
    .seccion-header {
        background-color: #1a365d;
        color: #ffffff;
        padding: 6px 10px;
        margin-bottom: 0;
    }
    .seccion-titulo {
        font-size: 11pt;
        font-weight: bold;
    }
    .seccion-conteo {
        float: right;
        font-size: 9pt;
        font-weight: normal;
        opacity: 0.9;
    }
    .seccion-cantidad {
        background-color: #edf2f7;
        padding: 4px 10px;
        font-size: 8pt;
        color: #4a5568;
        margin: 0 0 8px 0;
    }

    .item-header {
        background-color: #edf2f7;
        padding: 4px 10px;
        font-size: 9pt;
        font-weight: bold;
        color: #2d3748;
        border-left: 3px solid #3182ce;
        margin: 8px 0 2px 0;
    }

    /* ============================================ */
    /* TABLA DE CAMPOS                              */
    /* ============================================ */
    .tabla-campos {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 5px;
        font-size: 8pt;
    }
    .tabla-campos th {
        background-color: #e2e8f0;
        padding: 4px 6px;
        text-align: left;
        font-size: 7pt;
        text-transform: uppercase;
        color: #4a5568;
        border-bottom: 2px solid #cbd5e0;
    }
    .tabla-campos td {
        padding: 3px 6px;
        border-bottom: 1px solid #edf2f7;
        vertical-align: top;
    }

    /* ============================================ */
    /* FILAS POR TIPO DE RESULTADO                  */
    /* ============================================ */

    /* Correcta — fondo verde muy suave */
    .fila-correcta td {
        background-color: #f0fff4;
    }
    .fila-correcta .icono-resultado {
        color: #38a169;
        font-weight: bold;
    }

    /* Incorrecta — fondo rosado suave */
    .fila-incorrecta td {
        background-color: #fff5f5;
    }
    .fila-incorrecta .icono-resultado {
        color: #e53e3e;
        font-weight: bold;
    }

    /* Faltante — fondo naranja suave */
    .fila-faltante td {
        background-color: #fffaf0;
    }
    .fila-faltante .icono-resultado {
        color: #dd6b20;
        font-weight: bold;
    }
    .valor-vacio {
        color: #a0aec0;
        font-style: italic;
    }

    /* Sobrante — fondo amarillo suave */
    .fila-sobrante td {
        background-color: #fffff0;
    }
    .fila-sobrante .icono-resultado {
        color: #d69e2e;
        font-weight: bold;
    }

    /* ============================================ */
    /* BADGES                                       */
    /* ============================================ */
    .badge {
        display: inline-block;
        padding: 1px 6px;
        border-radius: 3px;
        font-size: 7pt;
        font-weight: bold;
        text-transform: uppercase;
    }
    .badge-correcto {
        background-color: #c6f6d5;
        color: #276749;
    }
    .badge-incorrecto {
        background-color: #fed7d7;
        color: #9b2c2c;
    }
    .badge-faltante {
        background-color: #feebc8;
        color: #9c4221;
    }
    .badge-sobrante {
        background-color: #fefcbf;
        color: #975a16;
    }

    /* ============================================ */
    /* BIENES SOBRANTES Y FALTANTES                 */
    /* ============================================ */
    .subseccion-titulo {
        font-size: 9pt;
        font-weight: bold;
        padding: 4px 10px;
        margin-top: 8px;
    }
    .sobrante-titulo {
        background-color: #fffff0;
        border-left: 3px solid #d69e2e;
        color: #975a16;
    }
    .faltante-titulo {
        background-color: #fffaf0;
        border-left: 3px solid #dd6b20;
        color: #9c4221;
    }
    .item-sobrante, .item-faltante {
        padding: 4px 10px 4px 20px;
        font-size: 8pt;
        border-bottom: 1px solid #edf2f7;
    }
    .item-sobrante {
        background-color: #fffff0;
        color: #744210;
    }
    .item-faltante {
        background-color: #fffaf0;
        color: #7b341e;
    }

    /* ============================================ */
    /* AUTOLIQUIDACIÓN                              */
    /* ============================================ */
    .tabla-autoliquidacion {
        width: 100%;
        border-collapse: collapse;
        font-size: 9pt;
    }
    .tabla-autoliquidacion th {
        background-color: #1a365d;
        color: #ffffff;
        padding: 5px 8px;
        font-size: 8pt;
    }
    .tabla-autoliquidacion td {
        padding: 4px 8px;
        border-bottom: 1px solid #e2e8f0;
    }
    /* Filas de subtotal o total con fondo más oscuro */
    .fila-subtotal td {
        background-color: #edf2f7;
        font-weight: bold;
    }
    .fila-total td {
        background-color: #1a365d;
        color: #ffffff;
        font-weight: bold;
    }

    .nota-informativa {
        background-color: #ebf8ff;
        border: 1px solid #90cdf4;
        border-left: 3px solid #3182ce;
        padding: 8px 12px;
        font-size: 8pt;
        color: #2c5282;
        margin-top: 8px;
    }

    /* ============================================ */
    /* PIE DE PÁGINA                                */
    /* ============================================ */
    .pie-reporte {
        text-align: center;
        font-size: 7pt;
        color: #a0aec0;
        margin-top: 20px;
        border-top: 1px solid #e2e8f0;
        padding-top: 5px;
    }
</style>
```

---

## ORDEN DE IMPLEMENTACIÓN RECOMENDADO

### Paso 1 — Refactorizar la función comparadora (PHP)
1. Crear `normalizarDescripcion()` y `emparejarBienes()` como funciones auxiliares.
2. Modificar la función de comparación para que use `emparejarBienes()` en cada categoría
   de bien mueble, pasivos, exenciones y exoneraciones.
3. Reestructurar el array de resultado para que separe `emparejados`, `sobrantes` y
   `faltantes` por cada sección.
4. Agregar el cálculo de `resumen_secciones` con conteo por sección.
5. **Probar:** Verificar con el mismo caso de prueba que los bienes que antes aparecían
   como "No encontrado" ahora se emparejan correctamente (al menos los que tienen la
   misma descripción).

### Paso 2 — Crear el template HTML nuevo
1. Copiar el template actual como backup.
2. Agregar el bloque de score global + tabla resumen (sección 2.1).
3. Modificar el loop de secciones para usar las clases CSS por tipo de resultado.
4. Implementar el template de bienes emparejados/sobrantes/faltantes (sección 2.3).
5. Agregar la nota informativa en autoliquidación cuando patrimonio neto ≤ 0.

### Paso 3 — Aplicar los estilos CSS
1. Reemplazar el bloque `<style>` del template con el CSS del Área 3.
2. Verificar que mPDF renderiza correctamente los colores de fondo de las filas.
   **Nota mPDF:** Si los colores de fondo no se renderizan en las celdas `<td>`,
   agregar en la configuración de mPDF:
   ```php
   $mpdf = new \Mpdf\Mpdf([
       'format' => 'Letter',
       'default_font' => 'dejavusans',
   ]);
   ```
   Y verificar que `dejavusans` esté disponible (viene por defecto con mPDF).

### Paso 4 — Probar con datos reales
1. Generar el reporte con el mismo caso de prueba (caso 1, RIF V700178940).
2. Verificar que la primera página muestra el resumen con barras de progreso.
3. Verificar que los bienes muebles emparejados muestran comparación campo a campo.
4. Verificar que los sobrantes dicen "Este bien no corresponde al caso".
5. Verificar que los faltantes dicen "Debía ser ingresado".
6. Verificar los colores de fondo por tipo de fila (verde, rosado, naranja, amarillo).

---

## NOTAS TÉCNICAS PARA mPDF

1. **page-break-inside: avoid** — Funciona en mPDF pero solo si el contenido cabe en
   una página. Para secciones muy largas (como 12 categorías de bienes muebles), no
   forzar `avoid` en el contenedor padre; ponerlo en cada sub-item.

2. **float: right** — Funciona en mPDF para el conteo en el header de sección.

3. **border-radius** — Funciona pero con limitaciones. En barras de progreso puede
   verse un poco brusco. Si se ve mal, quítalo y usa bordes rectos.

4. **Fuentes** — `dejavusans` soporta caracteres especiales (ñ, acentos). Si usas
   otra fuente, verificar que los caracteres venezolanos se rendericen bien.

5. **Tablas anidadas** — mPDF las soporta bien. Puedes anidar la tabla de campos
   dentro del div de sección sin problemas.

6. **Colores de fondo en td** — Algunos builds de mPDF requieren que el color de
   fondo se ponga directamente en el `style` del `<td>` en lugar de heredarlo del
   `<tr>`. Si los fondos no aparecen con clases CSS, usa inline styles como fallback:
   ```html
   <td style="background-color: #f0fff4;">...</td>
   ```
