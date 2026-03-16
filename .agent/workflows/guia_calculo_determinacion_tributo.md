# Guía de Cálculo: Determinación de Tributo Sucesoral

## Resumen Visual del Flujo

```
DATOS DE ENTRADA
├── Patrimonio Neto Hereditario (Bs.)     ← Línea 11 del reverso
├── Total de Herederos                     ← Cantidad de herederos declarados
├── Valor de la UT (Bs.)                   ← Vigente a la fecha de fallecimiento
├── Parentesco de cada heredero            ← Determina el Grupo de Tarifa (1-4)
└── Condición personal de cada heredero    ← Para reducciones (Art. 11)

                    ↓

CÁLCULO POR CADA HEREDERO (se repite para cada uno)
├── Paso 1: Cuota Parte en Bs.
├── Paso 2: Cuota Parte en UT
├── Paso 3: Verificar exención (≤ 75 UT + Grupo 1)
├── Paso 4: Buscar tramo en tarifa
├── Paso 5: Calcular impuesto en UT
├── Paso 6: Convertir impuesto a Bs.
└── Paso 7: Calcular reducción (si aplica)

                    ↓

RESULTADO FINAL
├── Línea 12: Suma de impuestos de todos los herederos
├── Línea 13: Suma de reducciones de todos los herederos
└── Línea 14: Total Impuesto a Pagar = Línea 12 - Línea 13
```

---

## Paso a Paso Detallado

### PASO 1 — Calcular la Cuota Parte en Bolívares

Dividir el patrimonio neto hereditario entre el total de herederos.

```
cuota_parte_bs = patrimonio_neto_hereditario / total_herederos
```

**Ejemplo:** Patrimonio = 160 Bs, 3 herederos
```
cuota_parte_bs = 160 / 3 = 53,333 Bs
```

> **Nota:** En el modo automático de SENIAT, todos los herederos reciben la misma
> cuota parte. Si la distribución es desigual (ej: cónyuge recibe 50%, hijos se
> reparten el otro 50%), el declarante debe usar "Modificar Cálculo" para ajustar
> manualmente. En SPDSS, el modo automático usa esta división igualitaria.

---

### PASO 2 — Convertir la Cuota Parte a Unidades Tributarias

Dividir la cuota parte en bolívares entre el valor de la UT vigente al momento
del fallecimiento del causante.

```
cuota_parte_ut = cuota_parte_bs / valor_ut
```

**Ejemplo:** Cuota = 53,333 Bs, UT = 0,40 Bs
```
cuota_parte_ut = 53,333 / 0,40 = 133,333 UT
```

> **¿Qué UT se usa?** Siempre la que estaba vigente en la fecha de fallecimiento
> del causante, NO la fecha en que se hace la declaración. Se busca en la tabla
> de UT el registro cuya fecha_gaceta sea la más reciente pero anterior o igual
> a la fecha de fallecimiento.

---

### PASO 3 — Verificar si aplica la Exención del Art. 9

Antes de calcular el impuesto, verificar si el heredero está exento:

```
SI cuota_parte_ut ≤ 75 Y grupo_tarifa_id = 1
    ENTONCES impuesto = 0 (exento)
    → Saltar directamente al Paso 7
```

**¿Qué significa?**
- Solo aplica al Grupo 1 (Ascendientes, Descendientes, Cónyuges e Hijos Adoptivos)
- Si la cuota parte del heredero es 75 UT o menos, NO paga impuesto
- Si el heredero es de Grupo 2, 3 o 4, esta exención NO aplica sin importar el monto
- Si el heredero es de Grupo 1 pero su cuota supera 75 UT, SÍ paga impuesto

**Ejemplo:** Heredero es HIJO (Grupo 1) con cuota de 50 UT → Exento, impuesto = 0
**Ejemplo:** Heredero es HIJO (Grupo 1) con cuota de 133,333 UT → NO exento, seguir al Paso 4
**Ejemplo:** Heredero es TÍO (Grupo 3) con cuota de 50 UT → NO exento (no es Grupo 1), seguir al Paso 4

---

### PASO 4 — Buscar el Tramo en la Tarifa Progresiva

Usando el grupo_tarifa_id del parentesco del heredero y su cuota_parte_ut,
buscar en la tabla de tramos el registro donde la cuota caiga dentro del rango.

```
BUSCAR EN sim_cat_tramos_tarifa DONDE:
    grupo_tarifa_id = [grupo del heredero]
    Y cuota_parte_ut >= limite_inferior_ut
    Y (cuota_parte_ut <= limite_superior_ut O limite_superior_ut ES NULO)
```

**Ejemplo:** Heredero HIJO (Grupo 1) con 133,333 UT

| Tramo | Desde    | Hasta   | ¿Cae aquí? |
|-------|----------|---------|------------|
| 1     | 0.00     | 15.00   | NO         |
| 2     | 15.01    | 50.00   | NO         |
| 3     | 50.01    | 100.00  | NO         |
| 4     | 100.01   | 250.00  | ✅ SÍ      |
| ...   | ...      | ...     | ...        |

→ Tramo 4: porcentaje = 7.50%, sustraendo = 3.98 UT

**Ejemplo:** Heredero TÍO (Grupo 3) con 133,333 UT

| Tramo | Desde    | Hasta   | ¿Cae aquí? |
|-------|----------|---------|------------|
| 1     | 0.00     | 15.00   | NO         |
| 2     | 15.01    | 50.00   | NO         |
| 3     | 50.01    | 100.00  | NO         |
| 4     | 100.01   | 250.00  | ✅ SÍ      |
| ...   | ...      | ...     | ...        |

→ Tramo 4 del Grupo 3: porcentaje = 25.00%, sustraendo = 9.73 UT

> **Importante:** Mismo monto en UT pero distinto grupo = distinto porcentaje y sustraendo.
> El Grupo 3 paga mucho más que el Grupo 1 por la misma cuota parte.

---

### PASO 5 — Calcular el Impuesto en Unidades Tributarias

Aplicar la fórmula con el porcentaje y sustraendo del tramo encontrado:

```
impuesto_ut = (cuota_parte_ut × porcentaje / 100) - sustraendo_ut
```

**Ejemplo (HIJO, Grupo 1):**
```
impuesto_ut = (133,333 × 7.50 / 100) - 3.98
impuesto_ut = 10.00 - 3.98
impuesto_ut = 6.02 UT
```

**Ejemplo (TÍO, Grupo 3):**
```
impuesto_ut = (133,333 × 25.00 / 100) - 9.73
impuesto_ut = 33.333 - 9.73
impuesto_ut = 23.603 UT
```

> **Si el resultado es negativo** (puede pasar en tramos bajos), el impuesto es 0.
> Nunca puede ser un número negativo.

---

### PASO 6 — Convertir el Impuesto a Bolívares

Multiplicar el impuesto en UT por el valor de la UT:

```
impuesto_bs = impuesto_ut × valor_ut
```

**Ejemplo (HIJO):**
```
impuesto_bs = 6.02 × 0.40 = 2.408 Bs → redondeado a 2.41 Bs
```

**Ejemplo (TÍO):**
```
impuesto_bs = 23.603 × 0.40 = 9.441 Bs → redondeado a 9.44 Bs
```

---

### PASO 7 — Calcular la Reducción (Art. 11)

Las reducciones son descuentos sobre el impuesto determinado según la condición
personal del heredero. Este paso se aplica DESPUÉS de calcular el impuesto.

#### 7.1 — ¿Califica para alguna reducción?

| Ord. | Condición del heredero                            | Reducción |
|------|---------------------------------------------------|-----------|
| 1    | Cónyuge sobreviviente                             | 40%       |
| 2    | Incapacitado total y permanente                   | 30%       |
| 3    | Incapacitado parcial y permanente                 | 25%       |
| 4    | Hijo menor de 21 años                             | 40%       |
| 5    | Mayor de 60 años                                  | 30%       |
| 6    | Por cada hijo menor de 21 a cargo del heredero    | 5% c/u   |
| 7    | Gratificación por servicios al causante (≤ 20 UT) | 30%       |

#### 7.2 — Verificar tope de cuota líquida (Parágrafo Segundo)

Para los ordinales 1 al 6:

```
SI cuota_parte_ut ≤ 250 UT:
    → La reducción se aplica COMPLETA (100%)

SI cuota_parte_ut > 250 UT Y ≤ 500 UT:
    → La reducción se aplica A MITAD (50%)

SI cuota_parte_ut > 500 UT:
    → NO aplica reducción (0%)
```

Para el ordinal 7: solo aplica si la cuota del beneficiario ≤ 20 UT.

#### 7.3 — Regla de concurrencia (Parágrafo Primero)

Si un heredero califica para más de una reducción, se aplica SOLO la más
favorable (la de mayor porcentaje).

**Excepción:** El ordinal 6 (por hijos menores a cargo) es acumulativo y se
SUMA a la reducción principal.

#### 7.4 — Calcular el monto de la reducción

```
reduccion_bs = impuesto_bs × (porcentaje_reduccion / 100)
```

**Para el ordinal 6:**
```
reduccion_ord6_bs = impuesto_bs × (5% × cantidad_hijos_menores_21)
```

**Reducción total del heredero:**
```
reduccion_total_heredero = reduccion_principal + reduccion_ord6
```

#### 7.5 — Ejemplo completo de reducción

Heredero: Cónyuge sobreviviente, 62 años, con 2 hijos menores de 21 a cargo.
Impuesto determinado: 100 Bs. Cuota parte: 200 UT (dentro del tope de 250).

Califica para:
- Ordinal 1 (Cónyuge): 40%
- Ordinal 5 (Mayor de 60): 30%
- Ordinal 6 (2 hijos menores): 5% × 2 = 10%

Regla de concurrencia: entre ordinal 1 (40%) y ordinal 5 (30%), se toma el
mayor → 40% (Cónyuge).

```
Reducción principal = 100 × 40% = 40 Bs
Reducción por hijos  = 100 × 10% = 10 Bs
Reducción total      = 40 + 10   = 50 Bs
```

---

### PASO 8 — Calcular el Impuesto a Pagar por Heredero

```
impuesto_a_pagar_heredero = impuesto_bs - reduccion_total_heredero
```

Si el resultado es negativo, se pone 0 (nunca se "devuelve" dinero).

---

## Cálculo del Resumen Final (Determinación de Tributo)

Una vez calculado el impuesto de CADA heredero, se suman para obtener los
totales de la declaración:

```
Línea 12 = Σ impuesto_bs de todos los herederos
           (Impuesto Determinado por Según Tarifa)

Línea 13 = Σ reduccion_total de todos los herederos
           (Reducciones)

Línea 14 = Línea 12 - Línea 13
           (Total Impuesto a Pagar)
```

> **Línea 14 nunca puede ser negativa.** Si las reducciones superan el impuesto,
> el total es 0.

---

## Ejemplo Completo con 3 Herederos

**Datos del caso:**
- Patrimonio Neto Hereditario: 160,00 Bs
- Valor UT: 0,40 Bs (vigente al fallecimiento)
- 3 herederos, todos HIJA/HIJO (Grupo 1), mayores de 21 años

**Paso 1 — Cuota parte:**
```
160 / 3 = 53,333 Bs por heredero
```

**Paso 2 — Convertir a UT:**
```
53,333 / 0,40 = 133,333 UT por heredero
```

**Paso 3 — ¿Exención?**
```
133,333 > 75 UT → NO exento. Seguir calculando.
```

**Paso 4 — Buscar tramo (Grupo 1, 133,333 UT):**
```
Tramo 4: 100,01 - 250,00 UT → porcentaje 7,50%, sustraendo 3,98 UT
```

**Paso 5 — Impuesto en UT:**
```
(133,333 × 7,50 / 100) - 3,98 = 10,00 - 3,98 = 6,02 UT
```

**Paso 6 — Impuesto en Bs:**
```
6,02 × 0,40 = 2,408 → redondeado 2,41 Bs por heredero
```

**Paso 7 — Reducciones:**
```
Ninguno califica (todos mayores de 21, no cónyuge, no incapacitados)
Reducción = 0,00 Bs por heredero
```

**Resultado final:**
```
Línea 12 = 2,41 + 2,41 + 2,41 = 7,23 Bs
Línea 13 = 0,00 + 0,00 + 0,00 = 0,00 Bs
Línea 14 = 7,23 - 0,00          = 7,23 Bs
```

---

## Ejemplo con Exención (Todo en 0)

**Datos del caso:**
- Patrimonio Neto Hereditario: 40,00 Bs
- Valor UT: 0,40 Bs
- 2 herederos, ambos HIJA/HIJO (Grupo 1)

**Paso 1:** 40 / 2 = 20,00 Bs
**Paso 2:** 20,00 / 0,40 = 50,00 UT
**Paso 3:** 50,00 ≤ 75 UT Y Grupo 1 → **EXENTO**

```
Línea 12 = 0,00 + 0,00 = 0,00 Bs
Línea 13 = 0,00
Línea 14 = 0,00 Bs
```

---

## Ejemplo con Herederos de Distintos Grupos

**Datos del caso:**
- Patrimonio Neto Hereditario: 800,00 Bs
- Valor UT: 0,40 Bs
- 2 herederos:
  - Heredero A: HIJO (Grupo 1)
  - Heredero B: TÍO (Grupo 3)

**Paso 1:** 800 / 2 = 400,00 Bs cada uno
**Paso 2:** 400,00 / 0,40 = 1.000,00 UT cada uno
**Paso 3:** 1.000 > 75 → NO exento para ambos

**Heredero A (HIJO, Grupo 1, 1.000 UT):**
- Tramo 6: 500,01 - 1000,00 → porcentaje 15%, sustraendo 35,23 UT
- Impuesto UT: (1.000 × 15 / 100) - 35,23 = 150 - 35,23 = 114,77 UT
- Impuesto Bs: 114,77 × 0,40 = 45,91 Bs

**Heredero B (TÍO, Grupo 3, 1.000 UT):**
- Tramo 6: 500,01 - 1000,00 → porcentaje 35%, sustraendo 47,23 UT
- Impuesto UT: (1.000 × 35 / 100) - 47,23 = 350 - 47,23 = 302,77 UT
- Impuesto Bs: 302,77 × 0,40 = 121,11 Bs

**Resultado:**
```
Línea 12 = 45,91 + 121,11 = 167,02 Bs
Línea 13 = 0,00 (ninguno califica para reducción)
Línea 14 = 167,02 Bs
```

> Nota cómo el tío paga casi 3 veces más que el hijo por la misma cuota parte.
> Esto es porque los parentescos más lejanos tienen tarifas progresivas más altas.

---

## Tablas de Referencia Necesarias

Para implementar este cálculo necesitas consultar:

1. **Tabla de UT** → para obtener el valor de la UT según fecha de fallecimiento
2. **Tabla de parentescos** → para obtener el grupo_tarifa_id del heredero
3. **Tabla de tramos de tarifa** → para obtener porcentaje y sustraendo
4. **Tabla de reducciones** → para obtener el porcentaje de reducción según condición

---

## Pseudocódigo del Algoritmo Completo

```
FUNCIÓN calcular_determinacion_tributo(caso):

    patrimonio_neto = caso.patrimonio_neto_hereditario
    valor_ut = obtener_ut_por_fecha(caso.fecha_fallecimiento)
    herederos = obtener_herederos(caso)
    total_herederos = contar(herederos)

    cuota_parte_bs = patrimonio_neto / total_herederos
    cuota_parte_ut = cuota_parte_bs / valor_ut

    total_impuesto = 0
    total_reducciones = 0

    PARA CADA heredero EN herederos:

        grupo = heredero.parentesco.grupo_tarifa_id

        // Paso 3: Exención
        SI cuota_parte_ut <= 75 Y grupo == 1:
            impuesto_bs = 0
        SINO:
            // Paso 4: Buscar tramo
            tramo = BUSCAR EN tramos_tarifa
                    DONDE grupo_tarifa_id = grupo
                    Y cuota_parte_ut >= limite_inferior_ut
                    Y (cuota_parte_ut <= limite_superior_ut O limite_superior_ut ES NULO)

            // Paso 5: Calcular impuesto en UT
            impuesto_ut = (cuota_parte_ut * tramo.porcentaje / 100) - tramo.sustraendo_ut

            SI impuesto_ut < 0:
                impuesto_ut = 0

            // Paso 6: Convertir a Bs
            impuesto_bs = impuesto_ut * valor_ut

        FIN SI

        // Paso 7: Calcular reducción
        reduccion_bs = calcular_reduccion(heredero, impuesto_bs, cuota_parte_ut)

        // Paso 8: Impuesto a pagar del heredero
        impuesto_pagar = impuesto_bs - reduccion_bs
        SI impuesto_pagar < 0:
            impuesto_pagar = 0

        // Acumular
        total_impuesto = total_impuesto + impuesto_bs
        total_reducciones = total_reducciones + reduccion_bs

    FIN PARA

    // Resultado final
    linea_12 = total_impuesto
    linea_13 = total_reducciones
    linea_14 = linea_12 - linea_13
    SI linea_14 < 0:
        linea_14 = 0

    RETORNAR linea_12, linea_13, linea_14

FIN FUNCIÓN


FUNCIÓN calcular_reduccion(heredero, impuesto_bs, cuota_parte_ut):

    // Determinar si aplica reducción según tope de cuota
    SI cuota_parte_ut > 500:
        RETORNAR 0  // No aplica reducción

    factor = 1.0  // 100% de la reducción
    SI cuota_parte_ut > 250:
        factor = 0.5  // 50% de la reducción

    // Buscar la reducción más favorable (mayor porcentaje)
    // entre las que apliquen al heredero (ordinales 1-5, 7)
    reducciones_aplicables = []

    SI heredero.es_conyuge:
        reducciones_aplicables.agregar(40%)  // Ordinal 1
    SI heredero.es_incapacitado_total:
        reducciones_aplicables.agregar(30%)  // Ordinal 2
    SI heredero.es_incapacitado_parcial:
        reducciones_aplicables.agregar(25%)  // Ordinal 3
    SI heredero.es_hijo Y heredero.edad < 21:
        reducciones_aplicables.agregar(40%)  // Ordinal 4
    SI heredero.edad >= 60:
        reducciones_aplicables.agregar(30%)  // Ordinal 5
    SI heredero.es_beneficiario_gratificacion Y cuota_parte_ut <= 20:
        reducciones_aplicables.agregar(30%)  // Ordinal 7

    // Tomar la más favorable
    SI reducciones_aplicables está vacío:
        porcentaje_principal = 0
    SINO:
        porcentaje_principal = MÁXIMO(reducciones_aplicables)

    reduccion_principal = impuesto_bs * (porcentaje_principal / 100) * factor

    // Ordinal 6: acumulativo (hijos menores a cargo)
    hijos_menores = heredero.cantidad_hijos_menores_21_a_cargo
    reduccion_hijos = impuesto_bs * (5 * hijos_menores / 100) * factor

    RETORNAR reduccion_principal + reduccion_hijos

FIN FUNCIÓN
```
