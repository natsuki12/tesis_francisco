# Flujo de Cálculo: Determinación de Tributo — SENIAT Real

> **Proyecto:** SPDSS — Sistema de Práctica de Declaración Sucesoral Simulada
> **Fecha:** 16 de marzo de 2026
> **Base legal:** Decreto 360 de 1999, G.O. 5.391 Extraordinario (Ley de Sucesiones vigente)
> **Fuente:** Pruebas directas en el portal SENIAT de Declaración Sucesoral en línea

---

## 1. Estructura General

La pantalla de Determinación de Tributo en SENIAT tiene dos secciones:

1. **Reverso** (líneas 1–11): Resumen del patrimonio hereditario. Calcula el patrimonio neto gravable a partir de bienes, exclusiones y pasivos.
2. **Determinación de Tributo** (líneas 12–15): Cálculo del impuesto a pagar basado en la cuota parte de cada heredero, su grupo tarifario y las reducciones aplicadas.

Ambas secciones son visibles en la misma pantalla pero se calculan de forma independiente.

---

## 2. Reverso — Patrimonio Hereditario (Líneas 1–11)

### 2.1 Líneas y fórmulas

| Línea | Concepto | Cálculo |
|---|---|---|
| 1 | Total Bienes Inmuebles | Σ valor de bienes inmuebles declarados |
| 2 | Total Bienes Muebles | Σ valor de bienes muebles declarados |
| 3 | **Patrimonio Hereditario Bruto** | Línea 1 + Línea 2 |
| 4 | **Activo Hereditario Bruto** | Igual a Línea 3 |
| 5 | Desgravámenes | Σ desgravámenes declarados |
| 6 | Exenciones | Σ exenciones declaradas |
| 7 | Exoneraciones | Σ exoneraciones declaradas |
| 8 | **Total de Exclusiones** | Línea 5 + Línea 6 + Línea 7 |
| 9 | **Activo Hereditario Neto** | Línea 4 − Línea 8 (mínimo 0) |
| 10 | Total Pasivo | Σ pasivos declarados |
| 11 | **Patrimonio Neto Hereditario o Líquido Hereditario Gravable** | Línea 9 − Línea 10 |

### 2.2 Porcentaje de propiedad del bien

Cada bien tiene un campo de porcentaje (%) que indica la proporción que pertenecía al causante. Este porcentaje **no afecta** el cálculo en el flujo real observado — el valor declarado del bien se suma tal cual al patrimonio bruto.

---

## 3. Determinación de Tributo (Líneas 12–15)

### 3.1 Datos de entrada

| Dato | Fuente |
|---|---|
| Patrimonio Neto (Bs) | Línea 11 del reverso |
| Total de herederos | Cantidad de herederos registrados |
| Valor de la UT (Bs) | UT vigente a la fecha de fallecimiento del causante |
| Grupo tarifario | Determinado por el parentesco de cada heredero |
| Reducción (Bs) | Ingresada manualmente por el declarante |

### 3.2 Unidad Tributaria aplicable

Se usa la UT vigente **al momento del fallecimiento**, no al momento de declarar.

```sql
SELECT valor
FROM sim_cat_unidades_tributarias
WHERE fecha_gaceta <= :fecha_fallecimiento
  AND activo = 1
ORDER BY fecha_gaceta DESC
LIMIT 1
```

### 3.3 Cuota parte hereditaria

La cuota parte se calcula como **división igualitaria** entre todos los herederos:

```
cuota_parte_bs = patrimonio_neto / total_herederos
cuota_parte_ut = cuota_parte_bs / valor_ut
```

La cuota parte es editable manualmente mediante el botón "Modificar Cálculo" (para distribuciones desiguales por testamento u otras razones).

### 3.4 Grupos tarifarios (Art. 7)

| Grupo | Nombre en SENIAT | Parentescos |
|---|---|---|
| 1 | Cónyuges Hijos y Ascendientes | Cónyuge, Hija/Hijo, Padre, Madre |
| 2 | Hermanos Sobrinos y Abuelos | Hermana(o) Simple/Doble Conjunción, Sobrino(a), Abuelo(a) |
| 3 | Los de 3° y los de 4° Grado | Tía/Tío, Prima/Primo Segundo, Bisabuelo(a), Bisnieto(a) |
| 4 | Extraños | Extraño |

Parentescos del mismo grupo producen exactamente el mismo cálculo. El sistema usa el grupo, no el parentesco específico.

### 3.5 Tramos de tarifa (Art. 7)

Cada grupo tiene 8 tramos progresivos. El tramo se selecciona según la cuota parte en UT:

| Tramo | Rango (UT) |
|---|---|
| 1 | 1 — 15 |
| 2 | 15,01 — 50 |
| 3 | 50,01 — 100 |
| 4 | 100,01 — 250 |
| 5 | 250,01 — 500 |
| 6 | 500,01 — 1.000 |
| 7 | 1.000,01 — 4.000 |
| 8 | 4.000,01 — en adelante |

Los porcentajes y sustraendos por grupo y tramo están almacenados en `sim_cat_tramos_tarifa` (32 registros = 4 grupos × 8 tramos).

### 3.6 Exención (Art. 9)

```
Si cuota_parte_ut ≤ 75 UT  Y  grupo = 1:
    impuesto_determinado = 0,00
```

Texto mostrado por SENIAT al pie de pantalla: *"Impuesto Determinado = 0 (Si la Cuota Parte es menor o igual a 75 UT y el parentesco es de 1er grado)"*

### 3.7 Fórmula de cálculo por heredero

```
impuesto_ut            = (cuota_parte_ut × porcentaje / 100) − sustraendo_ut
impuesto_determinado   = impuesto_ut × valor_ut
impuesto_a_pagar       = impuesto_determinado − reduccion
```

Si la exención aplica (Grupo 1 y cuota ≤ 75 UT), el impuesto_determinado es 0.

### 3.8 Reducciones (Art. 11)

Las reducciones son **siempre manuales**. El declarante ingresa el monto en bolívares. SENIAT no verifica automáticamente edad, condición de incapacidad ni parentesco.

| # | Concepto | % de reducción sobre el impuesto determinado |
|---|---|---|
| 1 | Cónyuge sobreviviente | 40% |
| 2 | Incapacitados totales para trabajar | 30% |
| 3 | Incapacitados parciales para trabajar | 25% |
| 4 | Hijos menores de 21 años | 40% |
| 5 | Mayores de 60 años que vivan con el causante | 30% |
| 6 | Por cada persona a cargo del heredero | 5% |
| 7 | Gratificación por servicios prestados al causante | 30% |

### 3.9 Líneas de resumen

| Línea | Nombre en SENIAT | Cálculo real |
|---|---|---|
| 12 | Impuesto Determinado por Según Tarifa | **Σ impuesto_a_pagar** de cada heredero (neto de reducción) |
| 13 | Reducciones | Σ reducciones (informativo) |
| 14 | Total Impuesto Pagado en Declaración Sustituida | Monto de declaración anterior (0,00 si original) |
| 15 | **Total Impuesto a Pagar** | **Línea 12 − Línea 14** |

> **Importante:** El nombre de la línea 12 es engañoso. No es la suma de impuestos brutos — es la suma de impuestos **ya netos de reducciones**. La línea 13 es solo un campo informativo que muestra el total de reducciones aplicadas.

### 3.10 Campo Premuerto

El campo "Premuerto" (SÍ/NO) de cada heredero es **estrictamente informativo**. No afecta la cuota parte, no redistribuye montos y no modifica el cálculo del impuesto. La representación de herederos premuertos se maneja agregando herederos representantes en la pestaña de herederos.

---

## 4. Flujo Completo Paso a Paso

```
ENTRADA:
  patrimonio_neto  ← Línea 11 del reverso (Bs)
  total_herederos  ← Cantidad de herederos registrados
  valor_ut         ← UT vigente a la fecha de fallecimiento (Bs)
  herederos[]      ← Lista con: parentesco, grupo_id, reduccion_bs

PASO 1 — Cuota parte:
  cuota_parte_bs = patrimonio_neto / total_herederos
  cuota_parte_ut = cuota_parte_bs / valor_ut

PASO 2 — Por cada heredero:
  2a. Obtener grupo tarifario del parentesco
  2b. Buscar tramo según cuota_parte_ut
  2c. Si grupo = 1 Y cuota_parte_ut ≤ 75:
        impuesto_determinado = 0,00
      Si no:
        impuesto_ut = (cuota_parte_ut × porcentaje / 100) − sustraendo_ut
        impuesto_determinado = round(impuesto_ut × valor_ut, 2)
  2d. impuesto_a_pagar = max(0, impuesto_determinado − reduccion_bs)

PASO 3 — Totales:
  linea_12 = Σ impuesto_a_pagar
  linea_13 = Σ reduccion_bs
  linea_14 = impuesto_declaracion_sustituida (0,00 si es original)
  linea_15 = linea_12 − linea_14
```

---

## 5. Ejemplo Verificado Contra SENIAT

### Datos del caso

- UT: 0,40 Bs
- Herederos: 3
- Cuota parte: 18.641,67 UT (Tramo 8 para todos los grupos)

### Cálculo por heredero

| Heredero | Parentesco | Grupo | % | Sustraendo (UT) | Impuesto (UT) | × UT | Impuesto (Bs) |
|---|---|---|---|---|---|---|---|
| 1 | HERMANA(O) SIMPLE | 2 | 40,00 | 495,38 | 6.961,29 | × 0,40 | 2.784,52 |
| 2 | EXTRAÑO | 4 | 55,00 | 498,25 | 9.754,67 | × 0,40 | 3.901,87 |
| 3 | PADRE | 1 | 25,00 | 285,23 | 4.375,19 | × 0,40 | 1.750,08 |

### Verificación de línea 12

```
2.784,52 + 3.901,87 + 1.750,08 = 8.436,47 Bs  ✓ (coincide con SENIAT)
```

### Ejemplo con reducción (500 Bs al PADRE)

```
PADRE:
  impuesto_determinado = 1.750,08
  reduccion            = 500,00
  impuesto_a_pagar     = 1.250,08

Línea 12 = 2.784,52 + 3.901,87 + 1.250,08 = 7.936,47  ✓
Línea 13 = 500,00  ✓
Línea 15 = 7.936,47 − 0,00 = 7.936,47  ✓
```

---

## 6. Pseudocódigo PHP

```php
function calcularDeterminacionTributo(
    float $patrimonioNeto,
    int   $totalHerederos,
    float $valorUT,
    array $herederos // cada uno: ['grupo_id' => int, 'reduccion_bs' => float]
): array {
    $cuotaParteBs = $patrimonioNeto / $totalHerederos;
    $cuotaParteUT = $cuotaParteBs / $valorUT;

    $linea12 = 0;
    $linea13 = 0;
    $resultados = [];

    foreach ($herederos as $h) {
        $grupo = $h['grupo_id'];

        // Buscar tramo
        $tramo = obtenerTramo($grupo, $cuotaParteUT);
        // SELECT porcentaje, sustraendo
        // FROM sim_cat_tramos_tarifa
        // WHERE grupo_id = :grupo
        //   AND :cuotaParteUT >= limite_inferior
        //   AND (:cuotaParteUT <= limite_superior OR limite_superior IS NULL)

        // Exención Art. 9
        if ($grupo === 1 && $cuotaParteUT <= 75) {
            $impuestoDeterminado = 0.00;
        } else {
            $impuestoUT = ($cuotaParteUT * $tramo['porcentaje'] / 100)
                        - $tramo['sustraendo'];
            $impuestoDeterminado = round($impuestoUT * $valorUT, 2);
        }

        $reduccion      = $h['reduccion_bs'] ?? 0.00;
        $impuestoAPagar = max(0, round($impuestoDeterminado - $reduccion, 2));

        $linea12 += $impuestoAPagar;
        $linea13 += $reduccion;

        $resultados[] = [
            'cuota_parte_ut'       => round($cuotaParteUT, 2),
            'porcentaje'           => $tramo['porcentaje'],
            'sustraendo_ut'        => $tramo['sustraendo'],
            'impuesto_determinado' => $impuestoDeterminado,
            'reduccion'            => $reduccion,
            'impuesto_a_pagar'     => $impuestoAPagar,
        ];
    }

    return [
        'herederos' => $resultados,
        'linea_12'  => round($linea12, 2),
        'linea_13'  => round($linea13, 2),
        'linea_14'  => 0.00,
        'linea_15'  => round($linea12, 2),
    ];
}
```

---

## 7. Tabla de Columnas por Heredero en SENIAT

La tabla de Determinación de Tributo muestra una fila por heredero con las siguientes columnas:

| Columna | Descripción |
|---|---|
| Apellido(s) y Nombre(s) | Nombre completo del heredero |
| C.I./Pasaporte | Documento de identidad |
| Parentesco | Tipo de parentesco con el causante |
| Grado | Grado de parentesco (numérico) |
| Premuerto | SÍ / NO (informativo) |
| Cuota Parte Hereditaria (UT) | Cuota parte en unidades tributarias |
| Porcentaje o Tarifa (%) | Porcentaje del tramo aplicable |
| Sustraendo (UT) | Sustraendo del tramo en UT |
| Impuesto Determinado (Bs.) | Cálculo bruto: (cuota × %) − sustraendo, convertido a Bs |
| Reducción (Bs.) | Monto de reducción ingresado manualmente |
| Impuesto a Pagar (Impuesto Determinado − Reducción) | Impuesto neto final del heredero |
