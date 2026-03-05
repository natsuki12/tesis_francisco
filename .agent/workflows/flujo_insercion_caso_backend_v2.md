# Flujo de Inserción Backend — Crear Caso de Estudio

> **Versión:** 2.0  
> **Fecha:** 2026-03-04  
> **Aplica a:** `POST /api/casos/guardar-borrador` y `POST /api/casos/publicar`  
> **JSON de referencia:** `ejemplo_payload_crear_caso_v3.json`  
> **Cambios v2:** tipo_sucesion condiciona acta de defunción (Paso 4b), inmuebles M:N con tabla pivote (Paso 7), UT resuelta dinámicamente (Paso 2)

---

## Reglas Generales

- **Todo dentro de una transacción.** Si cualquier paso falla, se hace ROLLBACK completo.
- **profesor_id** se obtiene de `$_SESSION['profesor_id']`, nunca del JSON.
- **Campos de auditoría** (`created_at`, `updated_at`, `deleted_at`) los maneja MariaDB automáticamente.
- **Campos con prefijo `_`** en el JSON (`_tabla`, `_nota`, `_ref_index`, `_categoria`) son metadatos de documentación. El backend los ignora.
- **Orden de pasos es estricto** — cada paso depende de IDs generados en pasos anteriores.

---

## Flujo A — Guardar Borrador

Cuando el profesor hace clic en "Guardar Borrador", el backend ejecuta **un solo paso**:

```
SI el JSON trae caso.id (borrador existente):
    UPDATE sim_casos_estudios
    SET titulo = :titulo,
        descripcion = :descripcion,
        borrador_json = :json_completo,
        updated_at = NOW()
    WHERE id = :caso_id
      AND profesor_id = :profesor_id_sesion
      AND estado = 'Borrador'

SI NO trae caso.id (borrador nuevo):
    INSERT INTO sim_casos_estudios (
        profesor_id, titulo, descripcion, tipo_sucesion, estado, borrador_json
    ) VALUES (
        :profesor_id_sesion, :titulo, :descripcion, :tipo_sucesion, 'Borrador', :json_completo
    )
    → Retorna $casoId al frontend para futuros guardados
```

No se toca ninguna otra tabla. El JSON completo se almacena tal cual en `borrador_json`.

---

## Flujo B — Publicar Caso

Cuando el profesor hace clic en "Publicar", el backend:

1. Carga el `borrador_json` de `sim_casos_estudios` donde `estado = 'Borrador'`
2. Valida que el JSON esté completo (todos los campos NOT NULL presentes)
3. Ejecuta los pasos 1–13 dentro de una transacción
4. Limpia `borrador_json = NULL` y cambia `estado = 'Publicado'`

---

### Paso 1 — Insertar Personas (sim_personas)

**Dependencias:** Ninguna.  
**Genera:** `$causanteId`, `$representanteId`, `$herederoPersonaIds[]`

```
1a. INSERT causante → sim_personas
    Campos: tipo_cedula, nacionalidad, cedula, pasaporte, rif_personal,
            nombres, apellidos, fecha_nacimiento, estado_civil, sexo,
            created_by = :profesor_id_sesion

    SI caso.tipo_sucesion = 'Sin_Cedula':
        tipo_cedula = 'No_Aplica', cedula = NULL

    → $causanteId = LAST_INSERT_ID()

1b. INSERT representante → sim_personas
    Mismos campos que 1a.
    → $representanteId = LAST_INSERT_ID()

1c. PARA CADA heredero en herederos.items[]:
    INSERT heredero.persona → sim_personas
    → $herederoPersonaIds[_ref_index] = LAST_INSERT_ID()
```

**Nota:** Aunque el representante y un heredero compartan cédula (ej: María Elena, C.I. 15789432), se crean como registros **separados** en `sim_personas`. Esto simplifica la lógica y evita conflictos de actualización.

---

### Paso 2 — Insertar Caso Principal (sim_casos_estudios)

**Dependencias:** `$causanteId`, `$representanteId` (Paso 1)  
**Genera:** `$casoId`

```
// Resolver unidad_tributaria_id dinámicamente
$utId = SELECT id FROM sim_cat_unidades_tributarias
        WHERE anio = YEAR(:acta_defuncion.fecha_fallecimiento)
        LIMIT 1
// Si el JSON trae unidad_tributaria_id explícito (override del profesor), usar ese en vez del dinámico

UPDATE sim_casos_estudios
SET causante_id = :$causanteId,
    representante_id = :$representanteId,
    unidad_tributaria_id = :$utId,
    tipo_sucesion = :caso.tipo_sucesion,
    titulo = :caso.titulo,
    descripcion = :caso.descripcion,
    estado = 'Publicado',
    borrador_json = NULL
WHERE id = :$casoId
  AND profesor_id = :profesor_id_sesion

→ $casoId ya existe (fue creado en el borrador)
```

**Nota:** Como el caso ya existe como borrador, este paso es un UPDATE, no un INSERT. Si por alguna razón no existe (publicación directa sin borrador previo), se hace INSERT completo.

**Nota (UT dinámica):** `unidad_tributaria_id` se resuelve cruzando el año de `fecha_fallecimiento` con `sim_cat_unidades_tributarias`. Si el profesor envía un valor explícito en el JSON, ese tiene prioridad (permite escenarios ficticios con UT diferente).

---

### Paso 3 — Insertar Configuración (sim_caso_configs)

**Dependencias:** `$casoId` (Paso 2)  
**Genera:** `$configId`

```
INSERT INTO sim_caso_configs (
    caso_id, profesor_id, modalidad, max_intentos, fecha_limite
) VALUES (
    :$casoId, :profesor_id_sesion, :config.modalidad,
    :config.max_intentos, :config.fecha_limite
)
→ $configId = LAST_INSERT_ID()
```

---

### Paso 4 — Datos Satélite del Causante

**Dependencias:** `$causanteId` (Paso 1)  
**Genera:** Nada (datos terminales)

Estos 3 INSERTs son independientes entre sí, solo dependen de `$causanteId`:

```
4a. INSERT → sim_causante_datos_fiscales
    Campos: sim_persona_id = :$causanteId,
            domiciliado_pais, fecha_cierre_fiscal

4b. INSERT → sim_actas_defunciones
    Campos: sim_persona_id = :$causanteId,
            fecha_fallecimiento, numero_acta, year_acta,
            parroquia_registro_id

    VALIDACIÓN CONDICIONAL según caso.tipo_sucesion:

    SI tipo_sucesion = 'Con_Cedula':
        OBLIGATORIO: fecha_fallecimiento
        OPCIONALES:  numero_acta, year_acta, parroquia_registro_id (pueden ser NULL)

    SI tipo_sucesion = 'Sin_Cedula':
        OBLIGATORIOS: fecha_fallecimiento, numero_acta, year_acta, parroquia_registro_id
        (El estudiante necesitará estos datos para inscribir al causante
         ante el SENIAT mediante acta de defunción)

4c. INSERT → sim_persona_direcciones
    Campos: sim_persona_id = :$causanteId,
            tipo_direccion, tipo_vialidad, nombre_vialidad,
            tipo_inmueble, nro_inmueble, tipo_nivel, nro_nivel,
            tipo_sector, nombre_sector, estado_id, municipio_id,
            parroquia_id, ciudad_id, codigo_postal_id,
            telefono_fijo, telefono_celular, fax, punto_referencia
```

**Importante (4c):** Los valores ENUM deben llegar del frontend ya en formato BD:
- `tipo_vialidad`: Calle, Avenida, Vereda, Carretera, Esquina (no "calle", "avenida")
- `tipo_inmueble`: Edificio, Centro_Comercial, Quinta, Casa, Local (no "edificio")
- `tipo_nivel`: Apartamento, Local, Oficina
- `tipo_sector`: Urbanizacion, Zona, Sector, Conjunto_Residencial
- `tipo_direccion`: Domicilio_Fiscal, Bodega_Almacenamiento_Deposito, etc.

---

### Paso 5 — Tipos de Herencia (sim_caso_tipoherencia_rel)

**Dependencias:** `$casoId` (Paso 2)  
**Genera:** Nada

```
PARA CADA tipo en tipos_herencia.items[]:
    INSERT INTO sim_caso_tipoherencia_rel (
        caso_estudio_id, tipo_herencia_id,
        subtipo_testamento, fecha_testamento,
        fecha_conclusion_inventario
    ) VALUES (
        :$casoId, :tipo.tipo_herencia_id,
        :tipo.subtipo_testamento,     -- NULL si no es Testamento
        :tipo.fecha_testamento,        -- NULL si no es Testamento
        :tipo.fecha_conclusion_inventario  -- NULL si no es Beneficio de Inventario
    )
```

---

### Paso 6 — Herederos (sim_caso_participantes + actas de premuertos)

**Dependencias:** `$casoId` (Paso 2), `$herederoPersonaIds[]` (Paso 1c)  
**Genera:** `$participanteIds[]`, mapa `_ref_index → participante_id`

Este es el paso más complejo por la autoreferencia de `premuerto_padre_id`. Se ejecuta en **dos pasadas**:

```
PASADA A — Insertar todos los participantes SIN resolver premuerto_padre_id:

    $participanteIds = []

    PARA CADA heredero en herederos.items[]:
        INSERT INTO sim_caso_participantes (
            caso_estudio_id, persona_id, rol_en_caso,
            parentesco_id, es_premuerto, premuerto_padre_id
        ) VALUES (
            :$casoId,
            :$herederoPersonaIds[heredero._ref_index],
            :heredero.rol_en_caso,    -- 'Heredero'
            :heredero.parentesco_id,
            :heredero.es_premuerto,
            NULL                       -- Se resuelve en Pasada B
        )
        → $participanteIds[heredero._ref_index] = LAST_INSERT_ID()

        SI heredero.es_premuerto = true Y heredero.fecha_fallecimiento != null:
            INSERT INTO sim_actas_defunciones (
                sim_persona_id, fecha_fallecimiento,
                numero_acta, year_acta, parroquia_registro_id
            ) VALUES (
                :$herederoPersonaIds[heredero._ref_index],
                :heredero.fecha_fallecimiento,
                NULL, NULL, NULL   -- Solo fecha para premuertos
            )


PASADA B — Resolver premuerto_padre_id:

    PARA CADA heredero en herederos.items[]:
        SI heredero.premuerto_padre_id != null:
            $padreRefIndex = heredero.premuerto_padre_id
            $padreParticipanteId = $participanteIds[$padreRefIndex]

            UPDATE sim_caso_participantes
            SET premuerto_padre_id = :$padreParticipanteId
            WHERE id = :$participanteIds[heredero._ref_index]
```

**Ejemplo con el JSON:**
- José Carlos (_ref_index=2) → se inserta con premuerto_padre_id=NULL, es_premuerto=true
- Daniela Sofía (_ref_index=3) → tiene premuerto_padre_id=2
- En Pasada B: premuerto_padre_id de Daniela se resuelve a `$participanteIds[2]` (el ID real de José Carlos)

---

### Paso 7 — Bienes Inmuebles (sim_caso_bienes_inmuebles + tipos M:N + litigiosos)

**Dependencias:** `$casoId` (Paso 2)  
**Genera:** `$inmuebleIds[]` (para litigiosos y tipos)

```
PARA CADA inmueble en bienes_inmuebles.items[]:

    7a. INSERT → sim_caso_bienes_inmuebles
        Campos: caso_estudio_id = :$casoId,
                es_vivienda_principal,
                es_bien_litigioso, porcentaje, descripcion,
                linderos, superficie_construida, superficie_no_construida,
                area_superficie, direccion, oficina_registro,
                nro_registro, libro, protocolo, fecha_registro,
                trimestre, asiento_registral, matricula,
                folio_real_anio, valor_original, valor_declarado
        → $inmuebleId = LAST_INSERT_ID()

    7b. PARA CADA tipoId en inmueble.tipos_bien_inmueble_ids[]:
        INSERT → sim_caso_bien_inmueble_tipo_rel
            Campos: bien_inmueble_id = :$inmuebleId,
                    tipo_bien_inmueble_id = :tipoId

    7c. SI inmueble.litigioso != null:
        INSERT → sim_caso_bienes_litigiosos
            Campos: caso_estudio_id = :$casoId,
                    bien_tipo = 'Inmueble',
                    bien_id = :$inmuebleId,
                    numero_expediente, tribunal_causa,
                    partes_juicio, estado_juicio
```

**Nota (v2):** `tipo_bien_inmueble_id` singular fue reemplazado por `tipos_bien_inmueble_ids` (array). Un inmueble puede tener 1 o más tipos (ej: Apartamento + Local Comercial). El backend inserta N registros en la tabla pivote `sim_caso_bien_inmueble_tipo_rel`, uno por cada ID del array. El UNIQUE compuesto `(bien_inmueble_id, tipo_bien_inmueble_id)` previene duplicados.

---

### Paso 8 — Bienes Muebles (sim_caso_bienes_muebles + tablas hija + litigiosos)

**Dependencias:** `$casoId` (Paso 2)  
**Genera:** `$muebleIds[]` (para litigiosos)

```
PARA CADA mueble en bienes_muebles.items[]:

    8a. INSERT → sim_caso_bienes_muebles (tabla base)
        Campos: caso_estudio_id = :$casoId,
                categoria_bien_mueble_id, tipo_bien_mueble_id,
                es_bien_litigioso, porcentaje,
                descripcion, valor_declarado
        → $muebleId = LAST_INSERT_ID()

    8b. INSERT en tabla hija según categoria_bien_mueble_id:

        SWITCH (mueble.categoria_bien_mueble_id):

            CASO 1 (banco):
                INSERT → sim_caso_bm_banco
                    bien_mueble_id = :$muebleId,
                    banco_id, numero_cuenta

            CASO 2 (seguro):
                $empresaId = resolverEmpresa(mueble.detalle_seguro)
                INSERT → sim_caso_bm_seguro
                    bien_mueble_id = :$muebleId,
                    empresa_id = :$empresaId,
                    numero_prima

            CASO 3 (transporte):
                INSERT → sim_caso_bm_transporte
                    bien_mueble_id = :$muebleId,
                    anio, marca, modelo, serial_placa

            CASO 4 (opciones_compra):
                INSERT → sim_caso_bm_opciones_compra
                    bien_mueble_id = :$muebleId,
                    nombre_oferente

            CASO 5 (cuentas_cobrar):
                INSERT → sim_caso_bm_cuentas_cobrar
                    bien_mueble_id = :$muebleId,
                    rif_cedula, apellidos_nombres

            CASO 6 (semovientes):
                INSERT → sim_caso_bm_semovientes
                    bien_mueble_id = :$muebleId,
                    tipo_semoviente_id, cantidad

            CASO 7 (bonos):
                INSERT → sim_caso_bm_bonos
                    bien_mueble_id = :$muebleId,
                    tipo_bonos, numero_bonos, numero_serie

            CASO 8 (acciones):
                $empresaId = resolverEmpresa(mueble.detalle_acciones)
                INSERT → sim_caso_bm_acciones
                    bien_mueble_id = :$muebleId,
                    empresa_id = :$empresaId

            CASO 9 (prestaciones):
                $empresaId = resolverEmpresa(mueble.detalle_prestaciones)
                INSERT → sim_caso_bm_prestaciones
                    bien_mueble_id = :$muebleId,
                    posee_banco, banco_id, numero_cuenta,
                    empresa_id = :$empresaId

            CASO 10 (caja_ahorro):
                $empresaId = resolverEmpresa(mueble.detalle_caja_ahorro)
                INSERT → sim_caso_bm_caja_ahorro
                    bien_mueble_id = :$muebleId,
                    empresa_id = :$empresaId

            CASO 11 (plantaciones):
                -- Sin tabla hija, solo campos base

            CASO 12 (otros):
                -- Sin tabla hija, solo campos base

    8c. SI mueble.litigioso != null:
        INSERT → sim_caso_bienes_litigiosos
            caso_estudio_id = :$casoId,
            bien_tipo = 'Mueble',
            bien_id = :$muebleId,
            numero_expediente, tribunal_causa,
            partes_juicio, estado_juicio
```

**Función resolverEmpresa():**
```
FUNCTION resolverEmpresa(detalle):
    $empresa = SELECT id FROM sim_empresas
                WHERE rif = :detalle.rif_empresa
                AND activo = 1
                LIMIT 1

    SI $empresa existe:
        RETURN $empresa.id

    SI NO:
        INSERT INTO sim_empresas (rif, razon_social, activo)
        VALUES (:detalle.rif_empresa, :detalle.razon_social, 1)
        RETURN LAST_INSERT_ID()
```

**Nota:** Si el mismo RIF aparece en varios bienes (ej: Acciones y Prestaciones con "J-00006860-9"), la primera llamada crea la empresa y las siguientes reutilizan el mismo `empresa_id`. Esto funciona automáticamente dentro de la misma transacción.

---

### Paso 9 — Pasivos Deuda (sim_caso_pasivos_deuda)

**Dependencias:** `$casoId` (Paso 2)  
**Genera:** Nada

```
PARA CADA deuda en pasivos_deuda.items[]:
    INSERT → sim_caso_pasivos_deuda
        Campos: caso_estudio_id = :$casoId,
                tipo_pasivo_deuda_id, banco_id,
                numero_tdc,  -- NULL si no es TDC
                porcentaje, descripcion, valor_declarado
```

---

### Paso 10 — Pasivos Gastos (sim_caso_pasivos_gastos)

**Dependencias:** `$casoId` (Paso 2)  
**Genera:** Nada

```
PARA CADA gasto en pasivos_gastos.items[]:
    INSERT → sim_caso_pasivos_gastos
        Campos: caso_estudio_id = :$casoId,
                tipo_pasivo_gasto_id, porcentaje,
                descripcion, valor_declarado
```

---

### Paso 11 — Exenciones y Exoneraciones

**Dependencias:** `$casoId` (Paso 2)  
**Genera:** Nada

```
11a. PARA CADA exencion en exenciones.items[]:
    INSERT → sim_caso_exenciones
        Campos: caso_estudio_id = :$casoId,
                tipo, descripcion, valor_declarado

11b. PARA CADA exoneracion en exoneraciones.items[]:
    INSERT → sim_caso_exoneraciones
        Campos: caso_estudio_id = :$casoId,
                tipo, descripcion, valor_declarado
```

---

### Paso 12 — Prórrogas (sim_caso_prorrogas)

**Dependencias:** `$casoId` (Paso 2)  
**Genera:** Nada

```
PARA CADA prorroga en prorrogas.items[]:
    INSERT → sim_caso_prorrogas
        Campos: caso_estudio_id = :$casoId,
                fecha_solicitud, nro_resolucion,
                fecha_resolucion, plazo_otorgado_dias,
                fecha_vencimiento
```

---

### Paso 13 — Asignaciones a Estudiantes (sim_caso_asignaciones)

**Dependencias:** `$configId` (Paso 3)  
**Genera:** Nada

```
PARA CADA estudiante_id en config.estudiantes_asignados[]:
    INSERT → sim_caso_asignaciones
        Campos: config_id = :$configId,
                estudiante_id = :estudiante_id,
                estado = 'Pendiente',
                fecha_completado = NULL
```

---

## Resumen de Dependencias

```
Paso 1  (Personas)          → Sin dependencias
Paso 2  (Caso)              → Depende de Paso 1 ($causanteId, $representanteId)
Paso 3  (Config)            → Depende de Paso 2 ($casoId)
Paso 4  (Satélite causante) → Depende de Paso 1 ($causanteId)
Paso 5  (Tipos herencia)    → Depende de Paso 2 ($casoId)
Paso 6  (Herederos)         → Depende de Paso 1c ($herederoPersonaIds) + Paso 2 ($casoId)
Paso 7  (Inmuebles)         → Depende de Paso 2 ($casoId)
Paso 8  (Muebles)           → Depende de Paso 2 ($casoId)
Paso 9  (Pasivos deuda)     → Depende de Paso 2 ($casoId)
Paso 10 (Pasivos gastos)    → Depende de Paso 2 ($casoId)
Paso 11 (Exenc/Exoner)      → Depende de Paso 2 ($casoId)
Paso 12 (Prórrogas)         → Depende de Paso 2 ($casoId)
Paso 13 (Asignaciones)      → Depende de Paso 3 ($configId)
```

Los pasos 4–12 son independientes entre sí (solo dependen de $casoId o $causanteId), así que el orden entre ellos no importa. Lo que sí es estricto: Paso 1 antes que todo, Paso 2 antes que 3–12, Paso 3 antes que 13.

---

## Pseudocódigo de la Transacción Completa

```
FUNCTION publicarCaso($casoId, $profesorId):

    $json = cargarBorradorJson($casoId, $profesorId)
    validarPayloadCompleto($json)

    BEGIN TRANSACTION

    TRY:
        // Paso 1 — Personas
        $causanteId      = insertarPersona($json.causante, $profesorId)
        $representanteId = insertarPersona($json.representante, $profesorId)
        $herederoPersonaIds = []
        PARA CADA h en $json.herederos.items:
            $herederoPersonaIds[h._ref_index] = insertarPersona(h.persona, $profesorId)

        // Paso 2 — Caso (con UT dinámica)
        $utId = resolverUT($json.acta_defuncion.fecha_fallecimiento)
        SI $json.caso.unidad_tributaria_id != null:
            $utId = $json.caso.unidad_tributaria_id  // override del profesor
        actualizarCasoPublicado($casoId, $causanteId, $representanteId, $utId, $json.caso)

        // Paso 3 — Config
        $configId = insertarConfig($casoId, $profesorId, $json.config)

        // Paso 4 — Satélite causante
        insertarDatosFiscales($causanteId, $json.causante_fiscal)
        insertarActaDefuncion($causanteId, $json.acta_defuncion, $json.caso.tipo_sucesion)
        insertarDireccion($causanteId, $json.domicilio_causante)

        // Paso 5 — Tipos herencia
        PARA CADA tipo en $json.tipos_herencia.items:
            insertarTipoHerencia($casoId, tipo)

        // Paso 6 — Herederos (dos pasadas)
        $participanteIds = []
        PARA CADA h en $json.herederos.items:
            $participanteIds[h._ref_index] = insertarParticipante(
                $casoId, $herederoPersonaIds[h._ref_index], h
            )
            SI h.es_premuerto Y h.fecha_fallecimiento:
                insertarActaPremuerto($herederoPersonaIds[h._ref_index], h.fecha_fallecimiento)

        PARA CADA h en $json.herederos.items:
            SI h.premuerto_padre_id != null:
                $padreId = $participanteIds[h.premuerto_padre_id]
                actualizarPremueroPadre($participanteIds[h._ref_index], $padreId)

        // Paso 7 — Inmuebles (M:N tipos)
        PARA CADA inm en $json.bienes_inmuebles.items:
            $inmId = insertarInmueble($casoId, inm)
            PARA CADA tipoId en inm.tipos_bien_inmueble_ids:
                insertarInmuebleTipoRel($inmId, tipoId)
            SI inm.litigioso:
                insertarLitigioso($casoId, 'Inmueble', $inmId, inm.litigioso)

        // Paso 8 — Muebles
        PARA CADA mue en $json.bienes_muebles.items:
            $mueId = insertarMueble($casoId, mue)
            insertarDetalleMueble($mueId, mue)
            SI mue.litigioso:
                insertarLitigioso($casoId, 'Mueble', $mueId, mue.litigioso)

        // Pasos 9-12 — Pasivos, exenciones, exoneraciones, prórrogas
        PARA CADA d en $json.pasivos_deuda.items:
            insertarPasivoDeuda($casoId, d)
        PARA CADA g en $json.pasivos_gastos.items:
            insertarPasivoGasto($casoId, g)
        PARA CADA e en $json.exenciones.items:
            insertarExencion($casoId, e)
        PARA CADA e en $json.exoneraciones.items:
            insertarExoneracion($casoId, e)
        PARA CADA p en $json.prorrogas.items:
            insertarProrroga($casoId, p)

        // Paso 13 — Asignaciones
        PARA CADA estId en $json.config.estudiantes_asignados:
            insertarAsignacion($configId, estId)

        COMMIT
        RETURN { success: true, caso_id: $casoId }

    CATCH Exception $e:
        ROLLBACK
        THROW $e
```

---

## Conteo de INSERTs por Caso (ejemplo del JSON v3)

| Tabla | Registros | Acumulado |
|-------|-----------|-----------|
| sim_personas | 6 (1 causante + 1 representante + 4 herederos) | 6 |
| sim_casos_estudios | 1 UPDATE (ya existe como borrador) | 6 |
| sim_caso_configs | 1 | 7 |
| sim_causante_datos_fiscales | 1 | 8 |
| sim_actas_defunciones | 2 (1 causante + 1 premuerto) | 10 |
| sim_persona_direcciones | 1 | 11 |
| sim_caso_tipoherencia_rel | 2 | 13 |
| sim_caso_participantes | 4 + 1 UPDATE (premuerto_padre) | 18 |
| sim_caso_bienes_inmuebles | 2 | 20 |
| sim_caso_bien_inmueble_tipo_rel | 3 (1 tipo para inmueble 1 + 2 tipos para inmueble 2) | 23 |
| sim_caso_bienes_litigiosos | 2 (1 inmueble + 1 mueble) | 25 |
| sim_caso_bienes_muebles | 12 | 37 |
| sim_caso_bm_banco | 2 | 39 |
| sim_caso_bm_seguro | 1 | 40 |
| sim_caso_bm_transporte | 1 | 41 |
| sim_caso_bm_opciones_compra | 1 | 42 |
| sim_caso_bm_cuentas_cobrar | 1 | 43 |
| sim_caso_bm_semovientes | 1 | 44 |
| sim_caso_bm_bonos | 1 | 45 |
| sim_caso_bm_acciones | 1 | 46 |
| sim_caso_bm_prestaciones | 1 | 47 |
| sim_caso_bm_caja_ahorro | 1 | 48 |
| sim_empresas | 1–2 (Polar reutilizada, Seguros Caracas nueva) | 50 |
| sim_caso_pasivos_deuda | 2 | 52 |
| sim_caso_pasivos_gastos | 2 | 54 |
| sim_caso_exenciones | 1 | 55 |
| sim_caso_exoneraciones | 0 | 55 |
| sim_caso_prorrogas | 1 | 56 |
| sim_caso_asignaciones | 5 | 61 |

**Total: ~61 operaciones** para un caso completo con todas las categorías (3 más que v2 por la tabla pivote de tipos de inmueble).
