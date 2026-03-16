# Documentación de la Base de Datos — Simulador SENIAT (SPDSS)

## 1. Descripción General

La base de datos **spdss** soporta un simulador educativo del sistema REDESU del SENIAT (Servicio Nacional Integrado de Administración Aduanera y Tributaria) de Venezuela. Su propósito es permitir a estudiantes de Derecho practicar el proceso de declaración sucesoral en un entorno controlado, mientras los profesores crean y evalúan casos de estudio.

El esquema contiene **90 tablas** organizadas en los siguientes módulos:

- **Sistema base**: Autenticación, roles, bitácora, gestión académica (usuarios, estudiantes, profesores, carreras, secciones, sesiones)
- **Geografía venezolana**: Estados, municipios, parroquias, ciudades, códigos postales, países
- **Simulador — Capa Maestro (sim_caso_*)**: Datos correctos definidos por el profesor
- **Simulador — Capa Intento (sim_intento_*)**: Datos ingresados por el estudiante durante su intento
- **Simulador — Catálogos (sim_cat_*)**: Datos de referencia compartidos por ambas capas
- **Simulador — Entidades globales (sim_personas, sim_empresas, etc.)**: Datos reutilizables entre casos

---

## 2. Arquitectura de Tres Capas

El diseño central del simulador se basa en una arquitectura de **tres capas** que separa el contenido del caso, su configuración de asignación, y el intento del estudiante:

### 2.1 Capa Maestro (sim_caso_*)

El profesor crea un caso de estudio con todos los datos precargados: datos del causante, herederos, bienes inmuebles, bienes muebles, pasivos, exenciones, exoneraciones. Estos datos representan la **respuesta correcta** contra la cual se evaluará al estudiante.

**Tablas principales:**
- `sim_casos_estudios` — Tabla central del caso (incluye `tipo_sucesion` y `borrador_json`)
- `sim_caso_configs` — Configuraciones de asignación del caso (modalidad, intentos, fechas)
- `sim_caso_asignaciones` — Asignación del caso a estudiantes (FK a config, no directa a caso)
- `sim_caso_participantes` — Herederos y legatarios del caso
- `sim_caso_direcciones` — Direcciones del causante en el contexto de cada caso
- `sim_caso_bienes_inmuebles` — Bienes inmuebles del caso
- `sim_caso_bien_inmueble_tipo_rel` — Tabla pivote M:N entre bien inmueble y tipos de bien inmueble
- `sim_caso_bienes_muebles` — Bienes muebles del caso (tabla base)
- `sim_caso_bm_*` — 10 tablas hijas de bienes muebles
- `sim_caso_bienes_litigiosos` — Bienes en litigio
- `sim_caso_pasivos_deuda` — Deudas del causante
- `sim_caso_pasivos_gastos` — Gastos del proceso sucesoral
- `sim_caso_exenciones` — Exenciones fiscales
- `sim_caso_exoneraciones` — Exoneraciones fiscales
- `sim_caso_prorrogas` — Prórrogas de la declaración
- `sim_caso_tipoherencia_rel` — Tipos de herencia del caso (M:N)

### 2.2 Capa Configuración e Intermedia

La capa de configuración conecta el caso con los estudiantes a través de un flujo de tres niveles:

```
sim_casos_estudios → sim_caso_configs → sim_caso_asignaciones → sim_intentos
```

**sim_caso_configs** define cómo se asigna un caso: modalidad (`Practica_Libre`, `Evaluacion`, `Practica_guiada`), número máximo de intentos, fechas de apertura/límite, instrucciones y estado (`Activo`/`Inactivo`).

**sim_caso_asignaciones** vincula una configuración con un estudiante específico, con estado propio (`Pendiente`, `En_Progreso`, `Completado`, `Vencido`, `Inactivo`).

### 2.3 Capa Intento (sim_intento_*)

Cuando un estudiante inicia un caso asignado, el sistema crea un **intento** donde el estudiante llena todos los datos desde cero, simulando el proceso real del SENIAT. Al finalizar, el sistema compara su intento contra la capa maestro.

**Tablas principales:**
- `sim_intentos` — Tabla central del intento (FK a asignación; incluye `borrador_json`, `paso_actual`, `pasos_completados`, `numero_control`, campos de RIF)
- `sim_intento_relaciones` — Herederos ingresados por el estudiante (incluye campo `orden`)
- `sim_intento_datos_basicos` — Datos del causante ingresados por el estudiante
- `sim_intento_direcciones` — Direcciones ingresadas por el estudiante
- `sim_intento_bienes_inmuebles` — Bienes inmuebles del intento
- `sim_intento_bien_inmueble_tipo_rel` — Tabla pivote M:N intento bien inmueble ↔ tipos
- `sim_intento_bienes_muebles` — Bienes muebles del intento (tabla base)
- `sim_intento_bm_*` — 10 tablas hijas de bienes muebles
- `sim_intento_bienes_litigiosos` — Bienes en litigio del intento
- `sim_intento_pasivos_deuda` — Deudas ingresadas por el estudiante
- `sim_intento_pasivos_gastos` — Gastos ingresados por el estudiante
- `sim_intento_exenciones` — Exenciones del intento
- `sim_intento_exoneraciones` — Exoneraciones del intento
- `sim_intento_prorrogas` — Prórrogas del intento
- `sim_intento_tipoherencias` — Tipos de herencia del intento
- `sim_intento_estados` — Historial de cambios de estado del intento
- `sim_intento_observaciones` — Observaciones del profesor sobre el intento

### 2.4 Flujo de Datos

```
Profesor crea caso → sim_casos_estudios (borrador_json mientras estado=Borrador)
    ├── Define causante, herederos, bienes, pasivos, etc. (capa maestro)
    └── Configura asignación → sim_caso_configs
            └── Asigna a estudiantes → sim_caso_asignaciones

Estudiante inicia caso → sim_intentos (FK a asignación)
    ├── Llena datos desde cero (capa intento)
    ├── Auto-save en borrador_json con paso_actual/pasos_completados
    ├── Envía intento → numero_control generado, cambio de estado
    ├── Profesor revisa → sim_intento_observaciones
    └── Aprobación → rif_sucesoral generado
```

---

## 3. Entidades Globales del Simulador

### 3.1 sim_personas

Personas simuladas creadas por los profesores. Son globales — una misma persona puede usarse como causante en un caso y como heredero en otro.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | BIGINT UNSIGNED | PK |
| tipo_cedula | ENUM('V','E','No_Aplica') | Tipo de documento |
| cedula | VARCHAR(20) | Número de cédula. NULL para causantes sin cédula |
| pasaporte | VARCHAR(20) | Número de pasaporte, si aplica |
| rif_personal | VARCHAR(12) | RIF personal. Formato Ej: V-12345678-9 |
| nombres | VARCHAR(100) | Nombres (campo unificado) |
| apellidos | VARCHAR(100) | Apellidos (campo unificado) |
| fecha_nacimiento | DATE | Fecha de nacimiento |
| sexo | ENUM('M','F') | Sexo |
| nacionalidad | SMALLINT UNSIGNED | FK a paises |
| estado_civil | ENUM('Soltero','Casado','Viudo','Divorciado','Concubinato','No_aplica') | Estado civil |
| created_by | BIGINT UNSIGNED | FK a profesores (quién la creó) |

**Tablas relacionadas:**
- `sim_caso_direcciones` — Direcciones del causante en el contexto de cada caso (FK a sim_caso_estudio_id, estados, municipios, parroquias, ciudades, códigos postales). Incluye campos detallados de vialidad, inmueble, nivel, sector, teléfonos y fax.
- `sim_actas_defunciones` — Acta de defunción (para causantes). Incluye fecha_fallecimiento, numero_acta, year_acta y parroquia_registro (nullable para premuertos).
- `sim_causante_datos_fiscales` — Datos fiscales del causante: domiciliado_pais (TINYINT 1) y fecha_cierre_fiscal (DATE). FK directa a sim_personas vía sim_persona_id.

### 3.2 sim_empresas

Empresas simuladas creadas por los profesores. Globales y reutilizables entre casos.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | BIGINT UNSIGNED | PK |
| rif | VARCHAR(12) | RIF completo con prefijo (ej: J-12345678-9). UNIQUE |
| razon_social | VARCHAR(255) | Nombre de la empresa |
| activo | TINYINT(1) | Estado activo/inactivo |

Se referencia desde: `sim_caso_bm_acciones`, `sim_caso_bm_seguro`, `sim_caso_bm_prestaciones`, `sim_caso_bm_caja_ahorro` y sus equivalentes en la capa intento.

### 3.3 sim_marco_legals

Tabla de referencia para el marco legal del simulador. Almacena leyes, códigos, providencias, gacetas oficiales y reglamentos relevantes al proceso sucesoral.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | SMALLINT UNSIGNED | PK |
| titulo | VARCHAR(255) | Título del instrumento legal |
| tipo | ENUM('Ley','Codigo','Providencia','Gaceta_Oficial','Reglamento') | Tipo de instrumento |
| descripcion | TEXT | Descripción del instrumento |
| url | VARCHAR(500) | URL de referencia |
| estado | ENUM('Vigente','Derogado') | Estado actual |
| orden | SMALLINT UNSIGNED | Orden de visualización |
| fecha_publicacion | DATE | Fecha de publicación |
| numero_gaceta | VARCHAR(100) | Número de Gaceta Oficial |

---

## 4. Bienes Inmuebles

Estructura con relación **muchos a muchos** entre bienes inmuebles y tipos, gestionada mediante tabla pivote.

### 4.1 Tablas

| Tabla | Capa | Descripción |
|-------|------|-------------|
| sim_caso_bienes_inmuebles | Maestro | Bienes inmuebles del caso |
| sim_intento_bienes_inmuebles | Intento | Bienes inmuebles del intento |
| sim_caso_bien_inmueble_tipo_rel | Maestro | Pivote M:N bien inmueble ↔ tipos |
| sim_intento_bien_inmueble_tipo_rel | Intento | Pivote M:N bien inmueble ↔ tipos |
| sim_cat_tipos_bien_inmueble | Catálogo | 21 tipos de bien inmueble |

### 4.2 Campos principales — sim_caso_bienes_inmuebles / sim_intento_bienes_inmuebles

| Campo | Tipo | Descripción |
|-------|------|-------------|
| caso_estudio_id / intento_id | BIGINT UNSIGNED | FK al caso o intento |
| es_vivienda_principal | TINYINT(1) | ¿Es vivienda principal? (afecta desgravámenes) |
| es_bien_litigioso | TINYINT(1) | ¿Está en litigio? |
| porcentaje | DECIMAL(5,2) | Porcentaje de propiedad (default 0.01) |
| descripcion | TEXT | Descripción libre |
| linderos | TEXT | Linderos del inmueble |
| superficie_construida | DECIMAL(12,2) | Superficie construida |
| superficie_no_construida | DECIMAL(12,2) | Superficie no construida |
| area_superficie | DECIMAL(12,2) | Área total |
| direccion | TEXT | Dirección del inmueble |
| oficina_registro | VARCHAR(255) | Oficina de registro |
| nro_registro | VARCHAR(50) | Número de registro |
| libro | VARCHAR(50) | Libro registral |
| protocolo | VARCHAR(50) | Protocolo registral |
| fecha_registro | DATE | Fecha de registro |
| trimestre | VARCHAR(20) | Trimestre del registro |
| asiento_registral | VARCHAR(50) | Asiento registral |
| matricula | VARCHAR(50) | Matrícula del inmueble |
| folio_real_anio | VARCHAR(20) | Folio real / año |
| valor_original | DECIMAL(15,2) | Valor original del inmueble (default 0.00) |
| valor_declarado | DECIMAL(18,2) | Valor declarado en Bs. (default 0.00) |
| deleted_at | TIMESTAMP | Soft delete |

**Nota:** El campo `tipo_bien_inmueble_id` fue migrado de FK directa a relación M:N via las tablas pivote `sim_caso_bien_inmueble_tipo_rel` y `sim_intento_bien_inmueble_tipo_rel`. Cada pivote contiene: `bien_inmueble_id` (FK) y `tipo_bien_inmueble_id` (FK a sim_cat_tipos_bien_inmueble).

### 4.3 Catálogo: sim_cat_tipos_bien_inmueble (21 registros)

Anexo, Apartamento, Bienhechurías, Casa, Construcción destinado a Explotación, Consultorio, Edificio, Galpón, Hotel o Similar, Inmueble en Construcción, Local, Maletero, Mixto (Residencia/Apartamento/Comercial), Oficina, Otros Especifique, Parcela, Puesto de Estacionamiento, Quinta, Resort, Terreno, Townhouse.

---

## 5. Bienes Muebles

Estructura más compleja: usa **herencia de tablas** (tabla base + tablas hijas). El SENIAT maneja 12 categorías de bienes muebles, cada una con un formulario diferente.

### 5.1 Diseño Jerárquico

```
sim_cat_categorias_bien_mueble (12 categorías)
    └── sim_cat_tipos_bien_mueble (22 subtipos, FK a categoría)

sim_caso_bienes_muebles (tabla base, campos comunes)
    ├── sim_caso_bm_banco (campos específicos de Banco)
    ├── sim_caso_bm_seguro (campos específicos de Seguro)
    ├── sim_caso_bm_transporte (campos específicos de Transporte)
    ├── sim_caso_bm_opciones_compra (campos específicos de Opciones de Compra)
    ├── sim_caso_bm_cuentas_cobrar (campos específicos de Cuentas por Cobrar)
    ├── sim_caso_bm_semovientes (campos específicos de Semovientes)
    ├── sim_caso_bm_bonos (campos específicos de Bonos)
    ├── sim_caso_bm_acciones (campos específicos de Acciones)
    ├── sim_caso_bm_prestaciones (campos específicos de Prestaciones Sociales)
    └── sim_caso_bm_caja_ahorro (campos específicos de Caja de Ahorro)
    (Plantaciones y Otros solo usan campos base, sin tabla hija)
```

La misma estructura se replica en la capa intento (sim_intento_bienes_muebles + sim_intento_bm_*).

### 5.2 Tabla Base — sim_caso_bienes_muebles / sim_intento_bienes_muebles

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | BIGINT UNSIGNED | PK |
| caso_estudio_id / intento_id | BIGINT UNSIGNED | FK al caso o intento |
| categoria_bien_mueble_id | TINYINT UNSIGNED | FK a sim_cat_categorias_bien_mueble. NOT NULL |
| tipo_bien_mueble_id | SMALLINT UNSIGNED | FK a sim_cat_tipos_bien_mueble. NULL si la categoría no tiene subtipos |
| es_bien_litigioso | TINYINT(1) | Default 0 |
| porcentaje | DECIMAL(5,2) | Default 0.01 |
| descripcion | TEXT | Nullable |
| valor_declarado | DECIMAL(18,2) | Default 0.00 |
| deleted_at | TIMESTAMP | Soft delete |

La `categoria_bien_mueble_id` determina qué formulario se renderiza en el frontend y a qué tabla hija ir a buscar los campos específicos. El `tipo_bien_mueble_id` es el subtipo dentro de esa categoría (nullable para categorías sin subtipos).

### 5.3 Tablas Hijas (relación 1:1 con tabla base via UNIQUE en bien_mueble_id)

**Banco** — sim_caso_bm_banco:
- banco_id (FK a sim_cat_bancos)
- numero_cuenta (VARCHAR 20)

**Seguro** — sim_caso_bm_seguro:
- empresa_id (FK a sim_empresas)
- numero_prima (VARCHAR 15)

**Transporte** — sim_caso_bm_transporte:
- anio (VARCHAR 4)
- marca (VARCHAR 15)
- modelo (VARCHAR 15)
- serial_placa (VARCHAR 30)

**Opciones de Compra** — sim_caso_bm_opciones_compra:
- nombre_oferente (VARCHAR 40)

**Cuentas y Efectos por Cobrar** — sim_caso_bm_cuentas_cobrar:
- rif_cedula (VARCHAR 12) — texto libre, puede ser persona o empresa
- apellidos_nombres (VARCHAR 100)

**Semovientes** — sim_caso_bm_semovientes:
- tipo_semoviente_id (FK a sim_cat_tipos_semoviente)
- cantidad (INT UNSIGNED)

**Bonos** — sim_caso_bm_bonos:
- tipo_bonos (VARCHAR 60) — texto libre
- numero_bonos (VARCHAR 30)
- numero_serie (VARCHAR 30)

**Acciones** — sim_caso_bm_acciones:
- empresa_id (FK a sim_empresas)

**Prestaciones Sociales** — sim_caso_bm_prestaciones:
- posee_banco (TINYINT 1, default 0)
- banco_id (FK a sim_cat_bancos, nullable — solo si posee_banco = 1)
- numero_cuenta (VARCHAR 20, nullable — solo si posee_banco = 1)
- empresa_id (FK a sim_empresas)

**Caja de Ahorro** — sim_caso_bm_caja_ahorro:
- empresa_id (FK a sim_empresas)

### 5.4 Catálogos de Bienes Muebles

**sim_cat_categorias_bien_mueble** (12 registros):
Banco, Seguro, Transporte, Opciones de Compra, Cuentas y Efectos por Cobrar, Semovientes, Bonos, Acciones, Prestaciones Sociales, Caja de Ahorro, Plantaciones, Otros.

**sim_cat_tipos_bien_mueble** (22 registros, FK a categoría):
- Banco → Acciones, Cajas de Ahorro, Cuentas Bancarias, Fideicomiso, Inventario Caja de Seguridad, Prestaciones Sociales
- Seguro → Caja de Ahorro, Montepío, Seguro de Vida
- Transporte → Aéreos, Maquinaria, Moto, Motonave, Naves, Vehículos
- Cuentas y Efectos por Cobrar → Asociación Civil, Clubes, Cuentas y Efectos por Cobrar
- Acciones → Cotizadas en la Bolsa de Valores, En Clubes, No Cotizadas en la Bolsa de Valores, Sociedad Mercantiles
- Categorías sin subtipos: Opciones de Compra, Semovientes, Bonos, Prestaciones Sociales, Caja de Ahorro, Plantaciones, Otros

**Nota de diseño:** Algunos subtipos comparten nombre con categorías independientes (ej: "Acciones" es subtipo de Banco y también es categoría propia). Esto es correcto — en el contexto de Banco, "Acciones" se refiere a acciones custodiadas en un banco (con campos de banco y número de cuenta), mientras que la categoría "Acciones" se refiere a participaciones societarias (con campos de empresa).

**sim_cat_bancos** (31 registros):
Todos los bancos del sistema financiero venezolano, incluyendo "NO APLICA" y "OTROS BANCOS" como opciones especiales.

**sim_cat_tipos_semoviente** (11 registros):
Abejas, Aves, Caracoles, Conejos o Liebres, Ganado Vacuno o Bovino, Ganado Caprino, Ganado Equino o Caballar, Ganado Ovino, Ganado Porcino, Peces, Otros (Especifique).

---

## 6. Bienes Litigiosos

Tabla polimórfica que almacena información de litigio para cualquier tipo de bien (inmueble o mueble).

### 6.1 Tablas

| Tabla | Capa |
|-------|------|
| sim_caso_bienes_litigiosos | Maestro |
| sim_intento_bienes_litigiosos | Intento |

### 6.2 Campos

| Campo | Tipo | Descripción |
|-------|------|-------------|
| caso_estudio_id / intento_id | BIGINT UNSIGNED | FK |
| bien_tipo | ENUM('Inmueble','Mueble') | Tipo de bien al que pertenece |
| bien_id | BIGINT UNSIGNED | ID del bien (sin FK, referencia polimórfica) |
| numero_expediente | VARCHAR(100) | Número de expediente del juicio |
| tribunal_causa | VARCHAR(255) | Tribunal de la causa |
| partes_juicio | VARCHAR(255) | Partes del juicio |
| estado_juicio | VARCHAR(100) | Estado actual del juicio |

**Nota:** `bien_id` no tiene FK porque puede apuntar a sim_caso_bienes_inmuebles o sim_caso_bienes_muebles dependiendo de `bien_tipo`. La integridad se maneja desde la aplicación. Estas tablas usan `created_at` / `updated_at` en lugar de `deleted_at`.

---

## 7. Pasivos

El SENIAT separa los pasivos en dos secciones con formularios distintos.

### 7.1 Pasivos Deuda — sim_caso_pasivos_deuda / sim_intento_pasivos_deuda

Son deudas que tenía el causante al momento de fallecer.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| tipo_pasivo_deuda_id | TINYINT UNSIGNED | FK a sim_cat_tipos_pasivo_deuda |
| banco_id | SMALLINT UNSIGNED | FK a sim_cat_bancos. NULL para tipo "Otros" |
| numero_tdc | VARCHAR(20) | Solo para Tarjetas de Crédito |
| porcentaje | DECIMAL(5,2) | Default 0.01 |
| descripcion | TEXT | Nullable |
| valor_declarado | DECIMAL(18,2) | Default 0.00 |
| deleted_at | TIMESTAMP | Soft delete |

**sim_cat_tipos_pasivo_deuda** (4 registros): Tarjetas de Crédito, Crédito Hipotecario, Préstamos Cuentas y Efectos por Pagar, Otros.

### 7.2 Pasivos Gastos — sim_caso_pasivos_gastos / sim_intento_pasivos_gastos

Son gastos generados por el proceso sucesoral.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| tipo_pasivo_gasto_id | TINYINT UNSIGNED | FK a sim_cat_tipos_pasivo_gasto |
| porcentaje | DECIMAL(5,2) | Default 0.01 |
| descripcion | TEXT | Nullable |
| valor_declarado | DECIMAL(18,2) | Default 0.00 |
| deleted_at | TIMESTAMP | Soft delete |

**sim_cat_tipos_pasivo_gasto** (7 registros): Exequias, Servicios Funerarios, Apertura de Testamento, Avalúo, Declaración de Herencia, Honorarios, Otros (Especifique).

---

## 8. Exenciones y Exoneraciones

Ambas secciones son formularios con entrada libre del usuario (no generadas automáticamente).

### 8.1 sim_caso_exenciones / sim_intento_exenciones

| Campo | Tipo | Descripción |
|-------|------|-------------|
| tipo | VARCHAR(255) | Tipo de exención (texto libre) |
| descripcion | TEXT | Descripción |
| valor_declarado | DECIMAL(18,2) | Valor en Bs. |
| deleted_at | TIMESTAMP | Soft delete |

### 8.2 sim_caso_exoneraciones / sim_intento_exoneraciones

Misma estructura que exenciones.

---

## 9. Sistema de Cálculo Tributario

### 9.1 Unidad Tributaria — sim_cat_unidades_tributarias

Catálogo histórico de valores de la UT por año (25 registros, de 2001 a 2025).

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | SMALLINT UNSIGNED | PK |
| anio | SMALLINT UNSIGNED | Año. UNIQUE |
| valor | DECIMAL(18,2) | Valor en Bs. de la UT para ese año |
| fecha_gaceta | DATE | Fecha de publicación en Gaceta Oficial |
| activo | TINYINT(1) | Estado activo/inactivo (default 1) |

**Nota:** Los valores reflejan las reconversiones monetarias de 2018 (se eliminaron 5 ceros) y 2021 (se eliminaron 6 ceros).

**Vinculación con sim_casos_estudios:** El campo `unidad_tributaria_id` (FK a este catálogo) determina qué UT aplica al caso. El sistema la selecciona automáticamente según el año de fallecimiento del causante.

### 9.2 Grupos de Tarifa — sim_cat_grupos_tarifa

La ley agrupa los parentescos en 4 grupos para aplicar tarifas diferenciadas (4 registros):

| ID | Nombre |
|----|--------|
| 1 | Ascendientes, Descendientes, Cónyuge e Hijos Adoptivos |
| 2 | Hermanos, Sobrinos por Derecho de Representación |
| 3 | Otros Colaterales de 3er y 4to Grado |
| 4 | Afines, Otros Parientes y Extraños |

### 9.3 Vinculación Parentesco → Grupo — sim_cat_parentescos.grupo_tarifa_id

Cada uno de los 19 parentescos tiene FK a su grupo de tarifa (o NULL para `Sin_Definir`):

| Grupo | Parentescos |
|-------|-------------|
| 1 | Hijo, Nieto, Bisnieto, Madre, Padre, Abuelo, Hijo Adoptivo, Cónyuge, Concubino |
| 2 | Hermano Simple, Hermano Doble |
| 3 | Tío, Sobrino, Primo Segundo, Primo |
| 4 | Otro Pariente, Extraño, Otro |
| NULL | Sin Definir |

**Nota:** La tabla usa `clave` (identificador interno) y `etiqueta` (texto visible al usuario). Collation: `utf8mb4_general_ci`.

### 9.4 Tarifas de Sucesión — sim_cat_tarifas_sucesion

32 registros (4 grupos × 8 rangos). Implementa el Artículo 7 de la Ley de Impuesto sobre Sucesiones.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| grupo_tarifa_id | TINYINT UNSIGNED | FK a sim_cat_grupos_tarifa |
| rango_desde_ut | DECIMAL(10,2) | Inicio del rango en UT |
| rango_hasta_ut | DECIMAL(10,2) | Fin del rango (NULL para el último: "a partir de 4000 UT") |
| porcentaje | DECIMAL(5,2) | Tarifa aplicable al rango |
| sustraendo_ut | DECIMAL(10,2) | Sustraendo en UT |

**Nota:** Esta tabla contiene los datos históricos/de referencia. Para el cálculo activo se usa `sim_cat_tramos_tarifa` (ver 9.5).

### 9.5 Tramos de Tarifa Progresiva — sim_cat_tramos_tarifa (NUEVA)

32 registros (4 grupos × 8 tramos). Implementa el Artículo 7 de la Ley de Impuesto sobre Sucesiones con una estructura normalizada orientada al cálculo programático. FK a `sim_cat_grupos_tarifa`.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT UNSIGNED | PK |
| grupo_tarifa_id | TINYINT UNSIGNED | FK a sim_cat_grupos_tarifa |
| tramo | TINYINT UNSIGNED | Número de tramo (1-8) |
| limite_inferior_ut | DECIMAL(10,2) | Desde (en UT) |
| limite_superior_ut | DECIMAL(10,2) | Hasta (en UT). NULL = sin límite superior |
| porcentaje | DECIMAL(5,2) | Porcentaje aplicable |
| sustraendo_ut | DECIMAL(10,2) | Sustraendo en UT (default 0.00) |
| activo | TINYINT(1) | Estado activo/inactivo (default 1) |

### 9.6 Reducciones al Impuesto Sucesoral — sim_cat_reducciones (NUEVA)

7 registros. Implementa el Artículo 11 de la Ley de Impuesto sobre Sucesiones. Tabla independiente sin FKs a otras tablas.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT UNSIGNED | PK |
| ordinal | TINYINT UNSIGNED | Ordinal del Art. 11 (1-7) |
| clave | VARCHAR(50) | Identificador interno (ej: CONYUGE_SOBREVIVIENTE, HIJO_MENOR_21) |
| etiqueta | VARCHAR(120) | Descripción para mostrar en UI |
| porcentaje_reduccion | DECIMAL(5,2) | Porcentaje de reducción sobre el impuesto |
| es_por_dependiente | TINYINT(1) | 1 = se aplica por cada hijo < 21 a cargo (ordinal 6). Default 0 |
| cuota_max_beneficiario_ut | DECIMAL(10,2) | Tope de cuota del beneficiario para aplicar (ordinal 7 = 20 UT). NULL = sin tope |
| activo | TINYINT(1) | Estado activo/inactivo (default 1) |

**Los 7 tipos de reducción:**
1. Cónyuge sobreviviente (40%)
2. Incapacitado total y permanente (30%)
3. Incapacitado parcial y permanente (25%)
4. Hijo menor de 21 años (40%)
5. Mayor de 60 años (30%)
6. Por cada hijo menor de 21 a cargo del heredero (5% cada uno, `es_por_dependiente = 1`)
7. Gratificación por años de servicio al causante, cuota ≤ 20 UT (30%)

**Ejemplo de cálculo:** Un hijo hereda una cuota parte de 600 UT.
1. Parentesco "Hijo" → grupo_tarifa_id = 1
2. 600 UT cae en el rango 500.01–1000.00 UT del grupo 1 → porcentaje = 15%, sustraendo = 35.23
3. Impuesto = (600 × 15%) - 35.23 = 90.00 - 35.23 = **54.77 UT**
4. Impuesto en Bs. = 54.77 × valor_ut del caso

**Cadena completa de FK para el cálculo:**
```
sim_caso_participantes.parentesco_id
    → sim_cat_parentescos.grupo_tarifa_id
        → sim_cat_grupos_tarifa.id
            → sim_cat_tarifas_sucesion.grupo_tarifa_id (filtrar por rango)
```

---

## 10. Herederos y Participantes

### 10.1 Capa Maestro — sim_caso_participantes

| Campo | Tipo | Descripción |
|-------|------|-------------|
| caso_estudio_id | BIGINT UNSIGNED | FK a sim_casos_estudios |
| persona_id | BIGINT UNSIGNED | FK a sim_personas |
| rol_en_caso | ENUM('Heredero','Legatario') | Rol del participante en el caso |
| parentesco_id | INT UNSIGNED | FK a sim_cat_parentescos |
| es_premuerto | TINYINT(1) | ¿El heredero prefalleció al causante? |
| premuerto_padre_id | BIGINT UNSIGNED | FK autoreferencial a sim_caso_participantes. Permite modelar herencia por representación |

**Nota:** Esta tabla no tiene `deleted_at`, `fecha_nacimiento`, `apellidos` ni `fecha_fallecimiento` como campos propios. Los datos personales se obtienen vía la FK a `sim_personas` y `sim_actas_defunciones`.

### 10.2 Capa Intento — sim_intento_relaciones

A diferencia de la capa maestro, la capa intento almacena todos los datos del heredero directamente (sin FK a sim_personas), ya que el estudiante los ingresa manualmente.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| intento_id | BIGINT UNSIGNED | FK a sim_intentos |
| rol | ENUM('Heredero','Legatario','Representante') | Rol (incluye Representante) |
| tipo_cedula | ENUM('V','E','No_Indica') | Tipo de documento |
| cedula | VARCHAR(20) | Nullable |
| pasaporte | VARCHAR(20) | Nullable |
| nombres | VARCHAR(100) | Nombres |
| apellidos | VARCHAR(100) | Apellidos |
| fecha_nacimiento | DATE | Fecha de nacimiento |
| parentesco_id | INT UNSIGNED | FK a sim_cat_parentescos |
| es_premuerto | TINYINT(1) | Default 0 |
| fecha_fallecimiento | DATE | Solo si es_premuerto = 1 |
| premuerto_padre_id | BIGINT UNSIGNED | FK autoreferencial |
| orden | TINYINT UNSIGNED | 0 = representante, 1+ = herederos. Asignado por el backend |

### 10.3 Herencia por Representación (Premuertos)

La autoreferencia `premuerto_padre_id` modela el caso donde un heredero prefalleció al causante y sus hijos heredan en su lugar. Ejemplo: si el Hijo A murió antes que el causante, los hijos de A (nietos del causante) heredan la porción de A. En la tabla, los nietos tendrán `premuerto_padre_id` apuntando al registro del Hijo A, y el Hijo A tendrá `es_premuerto = 1`.

---

## 11. Tipos de Herencia

Relación muchos a muchos entre casos y tipos de herencia, ya que un caso puede combinar herencia testada e intestada.

| Tabla | Descripción |
|-------|-------------|
| sim_cat_tipoherencias | Catálogo de 6 tipos de herencia |
| sim_caso_tipoherencia_rel | Tabla pivote caso ↔ tipo herencia |
| sim_intento_tipoherencias | Tabla pivote intento ↔ tipo herencia |

**Los 6 tipos:** Testamento, Ab-Intestato, Pura y Simple, Presunción de Ausencia, Presunción de Muerte por Accidente, Beneficio de Inventario.

---

## 12. Prórrogas

Las prórrogas permiten extender el plazo de declaración.

| Tabla | Capa |
|-------|------|
| sim_caso_prorrogas | Maestro |
| sim_intento_prorrogas | Intento |

| Campo | Tipo | Descripción |
|-------|------|-------------|
| fecha_solicitud | DATE | Fecha de solicitud de la prórroga |
| nro_resolucion | VARCHAR(50) | Número de resolución |
| fecha_resolucion | DATE | Fecha de la resolución |
| plazo_otorgado_dias | SMALLINT UNSIGNED | Días otorgados |
| fecha_vencimiento | DATE | Fecha de vencimiento de la prórroga |

---

## 13. Tabla Central del Intento — sim_intentos

La tabla `sim_intentos` es la pieza central de la capa intento y contiene campos clave para el flujo del estudiante:

| Campo | Tipo | Descripción |
|-------|------|-------------|
| asignacion_id | BIGINT UNSIGNED | FK a sim_caso_asignaciones (contiene caso + config + estudiante) |
| numero_intento | SMALLINT UNSIGNED | Número secuencial del intento (default 1) |
| estado | ENUM('En_Progreso','Enviado','Aprobado','Rechazado','Cancelado') | Estado del intento |
| paso_actual | TINYINT UNSIGNED | Último paso donde quedó el estudiante (1-6, default 1) |
| pasos_completados | SET('1','2','3','4','5','6') | Pasos que el estudiante ya completó y validó |
| borrador_json | LONGTEXT | Snapshot completo del intento. Se sobreescribe en cada auto-save |
| activo | TINYINT(1) VIRTUAL | Columna generada: 1 si estado='En_Progreso', NULL en otro caso. Permite UNIQUE parcial para un solo intento activo por asignación |
| numero_control | CHAR(11) | Número de control numérico generado al enviar la planilla |
| submitted_at | TIMESTAMP | Cuando el estudiante envió el intento |
| reviewed_at | TIMESTAMP | Cuando el profesor revisó |
| approved_at | TIMESTAMP | Cuando se aprobó y generó el RIF |
| rif_sucesoral | VARCHAR(12) | Formato J-XXXXXXXX-X, generado al aprobar |
| password_rif | VARCHAR(30) | Contraseña simulada del RIF. Texto plano, visible para el estudiante en su panel |
| fuera_de_fecha | TINYINT(1) | 1 si el intento se envió después de la fecha de cierre |
| usuario_seniat | VARCHAR(30) | Usuario SENIAT del intento. UNIQUE |

---

## 14. Configuración de Casos — sim_caso_configs

Tabla intermedia que define cómo se asigna un caso a los estudiantes:

| Campo | Tipo | Descripción |
|-------|------|-------------|
| caso_id | BIGINT UNSIGNED | FK a sim_casos_estudios |
| profesor_id | BIGINT UNSIGNED | FK a profesores |
| modalidad | ENUM('Practica_Libre','Evaluacion','Practica_guiada') | Modalidad de la asignación |
| max_intentos | TINYINT UNSIGNED | 0 = ilimitados |
| fecha_apertura | TIMESTAMP | Fecha desde la cual se puede iniciar |
| fecha_limite | TIMESTAMP | Solo aplica si modalidad = Evaluacion |
| instrucciones | TEXT | Instrucciones para los estudiantes |
| status | ENUM('Activo','Inactivo') | Estado de la configuración |

---

## 15. Sistema Base — Tablas Adicionales

### 15.1 user_sessions

Control de sesión única por usuario. Implementa lógica de "nueva sesión gana": al iniciar sesión, se registra la sesión activa y se invalidan las anteriores.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| user_id | BIGINT UNSIGNED | FK a users |
| session_id | VARCHAR(128) | ID de sesión PHP |
| ip_address | VARCHAR(45) | IP del cliente |
| user_agent | VARCHAR(255) | User agent del navegador |
| last_activity | DATETIME | Última actividad |

### 15.2 tipos_eventos

Catálogo de tipos de evento para la bitácora de accesos.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| codigo | VARCHAR(50) | Código del evento (ej: login_failed, password_reset) |
| descripcion | VARCHAR(255) | Texto legible para reportes |
| nivel_riesgo | ENUM('info','warning','critical') | Nivel de riesgo del evento |

---

## 16. Secciones Pendientes

Las siguientes secciones están pendientes o en progreso:

- **Desgravámenes** — Se generan automáticamente a partir de los bienes registrados (pendiente de diseño de tablas)
- **Resumen de la Declaración** — Totaliza activos, pasivos, deducciones y base imponible (pendiente de diseño de tablas)
- **Cálculo por Heredero** — Los catálogos necesarios ya existen: `sim_cat_tramos_tarifa` (tarifa progresiva por tramo/grupo) y `sim_cat_reducciones` (Art. 11). Falta diseñar las tablas de resultado del cálculo por heredero.

Estas tablas se agregarán sin afectar la estructura existente.

---

## 17. Resumen de Catálogos

| Catálogo | Registros | Usado en |
|----------|-----------|----------|
| sim_cat_categorias_bien_mueble | 12 | Bienes muebles (tabla base) |
| sim_cat_tipos_bien_mueble | 22 | Bienes muebles (tabla base) |
| sim_cat_tipos_bien_inmueble | 21 | Bienes inmuebles (via pivote M:N) |
| sim_cat_bancos | 31 | Bienes muebles (Banco, Prestaciones), Pasivos Deuda |
| sim_cat_tipos_semoviente | 11 | Bienes muebles (Semovientes) |
| sim_cat_tipos_pasivo_deuda | 4 | Pasivos Deuda |
| sim_cat_tipos_pasivo_gasto | 7 | Pasivos Gastos |
| sim_cat_unidades_tributarias | 25 | Casos de estudio (valor UT) |
| sim_cat_grupos_tarifa | 4 | Parentescos, Tarifas, Tramos |
| sim_cat_tarifas_sucesion | 32 | Cálculo del impuesto (referencia) |
| sim_cat_tramos_tarifa | 32 | Cálculo del impuesto (tramos progresivos, Art. 7) |
| sim_cat_reducciones | 7 | Reducciones al impuesto (Art. 11) |
| sim_cat_parentescos | 19 | Herederos (ambas capas) |
| sim_cat_tipoherencias | 6 | Tipos de herencia |

---

## 18. Convenciones de Diseño

### 18.1 Nomenclatura

- `sim_caso_*` — Tablas de la capa maestro
- `sim_intento_*` — Tablas de la capa intento
- `sim_cat_*` — Catálogos compartidos
- `sim_*` — Entidades globales del simulador
- Prefijo `fk_` para foreign keys con abreviaturas mnemotécnicas (ej: fk_cbm_caso = FK de caso_bienes_muebles a caso)
- Prefijo `uq_` para constraints UNIQUE

### 18.2 Patrones Recurrentes

- **Soft delete**: Campo `deleted_at` (TIMESTAMP NULL) en tablas transaccionales. NULL = activo, con fecha = eliminado lógicamente.
- **Timestamps**: `created_at` y `updated_at` con CURRENT_TIMESTAMP y ON UPDATE CURRENT_TIMESTAMP.
- **Catálogos**: Campo `activo` (TINYINT 1, default 1) para desactivar registros sin eliminarlos.
- **Herencia de tablas**: Tabla base con campos comunes + tablas hijas con campos específicos vinculadas por FK UNIQUE (1:1). Usado en bienes muebles.
- **Referencias polimórficas**: `bien_tipo` ENUM + `bien_id` sin FK. Usado en bienes litigiosos.
- **Relaciones M:N**: Tabla pivote con FKs. Usado en tipos de herencia y tipos de bien inmueble.
- **Columna virtual para UNIQUE parcial**: `activo` GENERATED ALWAYS AS en sim_intentos, permite un solo intento En_Progreso por asignación.
- **Valores monetarios**: DECIMAL(18,2) para todos los campos de valor declarado (escala para bolívares venezolanos).

### 18.3 Motor y Codificación

- Motor: InnoDB (soporte de FK y transacciones)
- Charset: utf8mb4
- Collation: utf8mb4_unicode_ci (soporte completo de caracteres especiales del español). Excepción: sim_cat_parentescos usa utf8mb4_general_ci.

---

## 19. Diagrama de Relaciones Principales

```
sim_casos_estudios (centro)
    ├── FK → sim_personas (causante)
    ├── FK → sim_personas (representante)
    ├── FK → profesores (creador)
    ├── FK → sim_cat_unidades_tributarias (UT del caso)
    │
    ├── sim_caso_configs → profesores
    │       └── sim_caso_asignaciones → estudiantes
    │               └── sim_intentos (borrador_json, paso_actual, pasos_completados)
    │
    ├── sim_caso_participantes → sim_personas + sim_cat_parentescos → sim_cat_grupos_tarifa
    ├── sim_caso_tipoherencia_rel → sim_cat_tipoherencias
    ├── sim_caso_direcciones → estados, municipios, parroquias, ciudades, codigos_postales
    ├── sim_caso_prorrogas
    │
    ├── sim_caso_bienes_inmuebles
    │       └── sim_caso_bien_inmueble_tipo_rel → sim_cat_tipos_bien_inmueble
    ├── sim_caso_bienes_muebles → sim_cat_categorias_bien_mueble + sim_cat_tipos_bien_mueble
    │       ├── sim_caso_bm_banco → sim_cat_bancos
    │       ├── sim_caso_bm_seguro → sim_empresas
    │       ├── sim_caso_bm_transporte
    │       ├── sim_caso_bm_opciones_compra
    │       ├── sim_caso_bm_cuentas_cobrar
    │       ├── sim_caso_bm_semovientes → sim_cat_tipos_semoviente
    │       ├── sim_caso_bm_bonos
    │       ├── sim_caso_bm_acciones → sim_empresas
    │       ├── sim_caso_bm_prestaciones → sim_cat_bancos + sim_empresas
    │       └── sim_caso_bm_caja_ahorro → sim_empresas
    │
    ├── sim_caso_bienes_litigiosos (polimórfica → inmuebles o muebles)
    ├── sim_caso_pasivos_deuda → sim_cat_tipos_pasivo_deuda + sim_cat_bancos
    ├── sim_caso_pasivos_gastos → sim_cat_tipos_pasivo_gasto
    ├── sim_caso_exenciones
    └── sim_caso_exoneraciones

sim_intentos (espejo completo de la capa maestro)
    ├── sim_intento_datos_basicos
    ├── sim_intento_direcciones
    ├── sim_intento_relaciones (incluye campo orden)
    ├── sim_intento_bien_inmueble_tipo_rel
    ├── sim_intento_estados
    ├── sim_intento_observaciones
    └── ... (todas las demás tablas intento espejo)

sim_cat_tarifas_sucesion → sim_cat_grupos_tarifa ← sim_cat_parentescos
sim_cat_tramos_tarifa → sim_cat_grupos_tarifa
sim_cat_reducciones (independiente, sin FKs)
```

---

## 20. Conteo Final

| Categoría | Cantidad |
|-----------|----------|
| Tablas del sistema base | 14 |
| Tablas de geografía | 6 |
| Tablas del simulador — Capa maestro | 25 |
| Tablas del simulador — Capa intento | 26 |
| Tablas del simulador — Catálogos | 14 |
| Tablas del simulador — Entidades globales | 5 |
| **Total** | **90** |
| **Foreign keys** | **127** |

**Desglose del sistema base (14):** bitacora_accesos, carreras, estudiantes, inscripciones, materias, password_resets, periodos, personas, profesores, roles, secciones, tipos_eventos, users, user_sessions.

**Desglose de geografía (6):** estados, municipios, parroquias, ciudades, codigos_postales, paises.

**Desglose de entidades globales (5):** sim_personas, sim_empresas, sim_actas_defunciones, sim_causante_datos_fiscales, sim_marco_legals.

**Desglose de catálogos (14):** sim_cat_categorias_bien_mueble, sim_cat_tipos_bien_mueble, sim_cat_tipos_bien_inmueble, sim_cat_bancos, sim_cat_tipos_semoviente, sim_cat_tipos_pasivo_deuda, sim_cat_tipos_pasivo_gasto, sim_cat_unidades_tributarias, sim_cat_grupos_tarifa, sim_cat_tarifas_sucesion, sim_cat_tramos_tarifa, sim_cat_reducciones, sim_cat_parentescos, sim_cat_tipoherencias.