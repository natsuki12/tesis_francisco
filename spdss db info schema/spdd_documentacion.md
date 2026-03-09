# Documentación de la Base de Datos — Simulador SENIAT (SPDSS)

## 1. Descripción General

La base de datos **spdss** soporta un simulador educativo del sistema REDESU del SENIAT (Servicio Nacional Integrado de Administración Aduanera y Tributaria) de Venezuela. Su propósito es permitir a estudiantes de Derecho practicar el proceso de declaración sucesoral en un entorno controlado, mientras los profesores crean y evalúan casos de estudio.

El esquema contiene **84 tablas** organizadas en los siguientes módulos:

- **Sistema base**: Autenticación, roles, bitácora, gestión académica (usuarios, estudiantes, profesores, carreras, secciones)
- **Geografía venezolana**: Estados, municipios, parroquias, ciudades, códigos postales, países
- **Simulador — Capa Maestro (sim_caso_*)**: Datos correctos definidos por el profesor
- **Simulador — Capa Intento (sim_intento_*)**: Datos ingresados por el estudiante durante su intento
- **Simulador — Catálogos (sim_cat_*)**: Datos de referencia compartidos por ambas capas
- **Simulador — Entidades globales (sim_personas, sim_empresas)**: Datos reutilizables entre casos

---

## 2. Arquitectura de Dos Capas

El diseño central del simulador se basa en una arquitectura de **dos capas paralelas** que separa la "respuesta correcta" del "intento del estudiante":

### 2.1 Capa Maestro (sim_caso_*)

El profesor crea un caso de estudio con todos los datos precargados: datos del causante, herederos, bienes inmuebles, bienes muebles, pasivos, exenciones, exoneraciones. Estos datos representan la **respuesta correcta** contra la cual se evaluará al estudiante.

**Tablas principales:**
- `sim_casos_estudios` — Tabla central del caso
- `sim_caso_participantes` — Herederos y legatarios del caso
- `sim_caso_bienes_inmuebles` — Bienes inmuebles del caso
- `sim_caso_bienes_muebles` — Bienes muebles del caso (tabla base)
- `sim_caso_bm_*` — 10 tablas hijas de bienes muebles
- `sim_caso_bienes_litigiosos` — Bienes en litigio
- `sim_caso_pasivos_deuda` — Deudas del causante
- `sim_caso_pasivos_gastos` — Gastos del proceso sucesoral
- `sim_caso_exenciones` — Exenciones fiscales
- `sim_caso_exoneraciones` — Exoneraciones fiscales
- `sim_caso_prorrogas` — Prórrogas de la declaración
- `sim_caso_tipoherencia_rel` — Tipos de herencia del caso (M:N)
- `sim_caso_asignaciones` — Asignación del caso a estudiantes

### 2.2 Capa Intento (sim_intento_*)

Cuando un estudiante inicia un caso asignado, el sistema crea un **intento** donde el estudiante llena todos los datos desde cero, simulando el proceso real del SENIAT. Al finalizar, el sistema compara su intento contra la capa maestro.

**Tablas principales:**
- `sim_intentos` — Tabla central del intento (FK a caso y estudiante)
- `sim_intento_relaciones` — Herederos ingresados por el estudiante
- `sim_intento_bienes_inmuebles` — Bienes inmuebles del intento
- `sim_intento_bienes_muebles` — Bienes muebles del intento (tabla base)
- `sim_intento_bm_*` — 10 tablas hijas de bienes muebles
- `sim_intento_bienes_litigiosos` — Bienes en litigio del intento
- `sim_intento_pasivos_deuda` — Deudas ingresadas por el estudiante
- `sim_intento_pasivos_gastos` — Gastos ingresados por el estudiante
- `sim_intento_exenciones` — Exenciones del intento
- `sim_intento_exoneraciones` — Exoneraciones del intento
- `sim_intento_prorrogas` — Prórrogas del intento
- `sim_intento_tipoherencias` — Tipos de herencia del intento
- `sim_intento_datos_basicos` — Datos del causante ingresados por el estudiante
- `sim_intento_direcciones` — Direcciones ingresadas por el estudiante
- `sim_intento_estados` — Historial de cambios de estado del intento
- `sim_intento_observaciones` — Observaciones del profesor sobre el intento

### 2.3 Flujo de Datos

```
Profesor crea caso → sim_casos_estudios
    ├── Define causante, herederos, bienes, pasivos, etc. (capa maestro)
    └── Asigna caso a estudiantes → sim_caso_asignaciones

Estudiante inicia caso → sim_intentos
    ├── Llena datos desde cero (capa intento)
    ├── Envía intento → cambio de estado en sim_intento_estados
    └── Profesor compara intento vs maestro → evaluación
```

---

## 3. Entidades Globales del Simulador

### 3.1 sim_personas

Personas simuladas creadas por los profesores. Son globales — una misma persona puede usarse como causante en un caso y como heredero en otro.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | BIGINT UNSIGNED | PK |
| tipo_cedula | ENUM('V','E','No_Aplica') | Tipo de documento |
| cedula | VARCHAR(15) | Número de cédula |
| primer_nombre, segundo_nombre | VARCHAR(50) | Nombres |
| primer_apellido, segundo_apellido | VARCHAR(50) | Apellidos |
| fecha_nacimiento | DATE | Fecha de nacimiento |
| sexo | ENUM('M','F') | Sexo |
| nacionalidad | INT UNSIGNED | FK a paises |
| estado_civil | ENUM(...) | Estado civil |
| created_by | BIGINT UNSIGNED | FK a profesores (quién la creó) |

**Tablas relacionadas:**
- `sim_persona_direcciones` — Dirección de la persona (FK a estados, municipios, parroquias, ciudades, códigos postales)
- `sim_actas_defunciones` — Acta de defunción (para causantes)
- `sim_causante_datos_fiscales` — RIF y datos fiscales del causante

### 3.2 sim_empresas

Empresas simuladas creadas por los profesores. Globales y reutilizables entre casos.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | BIGINT UNSIGNED | PK |
| rif | VARCHAR(12) | RIF completo con prefijo (ej: J-12345678-9). UNIQUE |
| razon_social | VARCHAR(255) | Nombre de la empresa |
| activo | TINYINT(1) | Estado activo/inactivo |

Se referencia desde: `sim_caso_bm_acciones`, `sim_caso_bm_seguro`, `sim_caso_bm_prestaciones`, `sim_caso_bm_caja_ahorro` y sus equivalentes en la capa intento.

---

## 4. Bienes Inmuebles

Estructura simple: una sola tabla por capa con FK a un catálogo de 21 tipos.

### 4.1 Tablas

| Tabla | Capa | Descripción |
|-------|------|-------------|
| sim_caso_bienes_inmuebles | Maestro | Bienes inmuebles del caso |
| sim_intento_bienes_inmuebles | Intento | Bienes inmuebles del intento |
| sim_cat_tipos_bien_inmueble | Catálogo | 21 tipos de bien inmueble |

### 4.2 Campos principales

| Campo | Tipo | Descripción |
|-------|------|-------------|
| caso_estudio_id / intento_id | BIGINT UNSIGNED | FK al caso o intento |
| tipo_bien_inmueble_id | TINYINT UNSIGNED | FK al catálogo de tipos |
| es_vivienda_principal | TINYINT(1) | ¿Es vivienda principal? (afecta desgravámenes) |
| es_bien_litigioso | TINYINT(1) | ¿Está en litigio? |
| porcentaje | DECIMAL(5,2) | Porcentaje de propiedad (default 0.01) |
| descripcion | TEXT | Descripción libre |
| valor_declarado | DECIMAL(15,2) | Valor en Bs. (default 0.00) |
| deleted_at | TIMESTAMP | Soft delete |

### 4.3 Catálogo: sim_cat_tipos_bien_inmueble (21 registros)

Apartamentos, Casas, Edificios, Fincas, Fondos de Comercio, Galpones, Haciendas, Locales, Lotes de Terreno, Oficinas, Parcelas, Quincallas, Solares, Terrenos, Terrenos con Construcción, Terrenos Ejidos, Terrenos Rurales, Terrenos Urbanos, Town Houses, y otros.

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
| valor_declarado | DECIMAL(15,2) | Default 0.00 |
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
| numero_expediente | VARCHAR(50) | Número de expediente del juicio |
| tribunal_causa | VARCHAR(255) | Tribunal de la causa |
| partes_juicio | VARCHAR(255) | Partes del juicio |
| estado_juicio | VARCHAR(255) | Estado actual del juicio |
| deleted_at | TIMESTAMP | Soft delete |

**Nota:** `bien_id` no tiene FK porque puede apuntar a sim_caso_bienes_inmuebles o sim_caso_bienes_muebles dependiendo de `bien_tipo`. La integridad se maneja desde la aplicación.

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
| valor_declarado | DECIMAL(15,2) | Default 0.00 |
| deleted_at | TIMESTAMP | Soft delete |

**sim_cat_tipos_pasivo_deuda** (4 registros): Tarjetas de Crédito, Crédito Hipotecario, Préstamos Cuentas y Efectos por Pagar, Otros.

### 7.2 Pasivos Gastos — sim_caso_pasivos_gastos / sim_intento_pasivos_gastos

Son gastos generados por el proceso sucesoral.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| tipo_pasivo_gasto_id | TINYINT UNSIGNED | FK a sim_cat_tipos_pasivo_gasto |
| porcentaje | DECIMAL(5,2) | Default 0.01 |
| descripcion | TEXT | Nullable |
| valor_declarado | DECIMAL(15,2) | Default 0.00 |
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
| valor_declarado | DECIMAL(15,2) | Valor en Bs. |
| deleted_at | TIMESTAMP | Soft delete |

### 8.2 sim_caso_exoneraciones / sim_intento_exoneraciones

Misma estructura que exenciones.

---

## 9. Sistema de Cálculo Tributario

### 9.1 Unidad Tributaria — sim_cat_unidades_tributarias

Catálogo histórico de valores de la UT por año (25 registros, de 2001 a 2025).

| Campo | Tipo | Descripción |
|-------|------|-------------|
| anio | SMALLINT UNSIGNED | Año. UNIQUE |
| valor | DECIMAL(15,2) | Valor en Bs. de la UT para ese año |
| fecha_gaceta | DATE | Fecha de publicación en Gaceta Oficial |

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

Cada uno de los 17 parentescos tiene FK a su grupo de tarifa:

| Grupo | Parentescos |
|-------|-------------|
| 1 | Hijo, Nieto, Bisnieto, Madre, Padre, Abuelo, Hijo Adoptivo, Cónyuge, Concubino |
| 2 | Hermano Simple, Hermano Doble, Sobrino por Representación |
| 3 | Tío, Sobrino, Primo Segundo |
| 4 | Otro Pariente, Extraño |

### 9.4 Tarifas de Sucesión — sim_cat_tarifas_sucesion

32 registros (4 grupos × 8 rangos). Implementa el Artículo 7 de la Ley de Impuesto sobre Sucesiones.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| grupo_tarifa_id | TINYINT UNSIGNED | FK a sim_cat_grupos_tarifa |
| rango_desde_ut | DECIMAL(10,2) | Inicio del rango en UT |
| rango_hasta_ut | DECIMAL(10,2) | Fin del rango (NULL para el último: "a partir de 4000 UT") |
| porcentaje | DECIMAL(5,2) | Tarifa aplicable al rango |
| sustraendo_ut | DECIMAL(10,2) | Sustraendo en UT |

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
| parentesco_id | INT UNSIGNED | FK a sim_cat_parentescos |
| apellidos | VARCHAR(120) | Apellidos del heredero |
| fecha_nacimiento | DATE | Fecha de nacimiento |
| es_premuerto | TINYINT(1) | ¿El heredero prefalleció al causante? |
| fecha_fallecimiento | DATE | Solo si es_premuerto = 1 |
| premuerto_padre_id | BIGINT UNSIGNED | FK autoreferencial a sim_caso_participantes. Permite modelar herencia por representación |
| deleted_at | TIMESTAMP | Soft delete |

### 10.2 Capa Intento — sim_intento_relaciones

Misma estructura pero con FK a sim_intentos y autoreferencia a sí misma.

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

---

## 12. Prórrogas

Las prórrogas permiten extender el plazo de declaración.

| Tabla | Capa |
|-------|------|
| sim_caso_prorrogas | Maestro |
| sim_intento_prorrogas | Intento |

---

## 13. Secciones Pendientes

Las siguientes secciones están pendientes de diseño, ya que requieren acceso a las vistas correspondientes del sistema SENIAT real:

- **Desgravámenes** — Se generan automáticamente a partir de los bienes registrados
- **Resumen de la Declaración** — Totaliza activos, pasivos, deducciones y base imponible
- **Cálculo por Heredero** — Aplica la tarifa según cuota parte y parentesco de cada heredero

Estas tablas se agregarán sin afectar la estructura existente.

---

## 14. Resumen de Catálogos

| Catálogo | Registros | Usado en |
|----------|-----------|----------|
| sim_cat_categorias_bien_mueble | 12 | Bienes muebles (tabla base) |
| sim_cat_tipos_bien_mueble | 22 | Bienes muebles (tabla base) |
| sim_cat_tipos_bien_inmueble | 21 | Bienes inmuebles |
| sim_cat_bancos | 31 | Bienes muebles (Banco, Prestaciones), Pasivos Deuda |
| sim_cat_tipos_semoviente | 11 | Bienes muebles (Semovientes) |
| sim_cat_tipos_pasivo_deuda | 4 | Pasivos Deuda |
| sim_cat_tipos_pasivo_gasto | 7 | Pasivos Gastos |
| sim_cat_unidades_tributarias | 25 | Casos de estudio (valor UT) |
| sim_cat_grupos_tarifa | 4 | Parentescos, Tarifas |
| sim_cat_tarifas_sucesion | 32 | Cálculo del impuesto |
| sim_cat_parentescos | 17 | Herederos (ambas capas) |
| sim_cat_tipoherencias | 6 | Tipos de herencia |

---

## 15. Convenciones de Diseño

### 15.1 Nomenclatura

- `sim_caso_*` — Tablas de la capa maestro
- `sim_intento_*` — Tablas de la capa intento
- `sim_cat_*` — Catálogos compartidos
- `sim_*` — Entidades globales del simulador
- Prefijo `fk_` para foreign keys con abreviaturas mnemotécnicas (ej: fk_cbm_caso = FK de caso_bienes_muebles a caso)
- Prefijo `uq_` para constraints UNIQUE

### 15.2 Patrones Recurrentes

- **Soft delete**: Campo `deleted_at` (TIMESTAMP NULL) en tablas transaccionales. NULL = activo, con fecha = eliminado lógicamente.
- **Timestamps**: `created_at` y `updated_at` con CURRENT_TIMESTAMP y ON UPDATE CURRENT_TIMESTAMP.
- **Catálogos**: Campo `activo` (TINYINT 1, default 1) para desactivar registros sin eliminarlos.
- **Herencia de tablas**: Tabla base con campos comunes + tablas hijas con campos específicos vinculadas por FK UNIQUE (1:1). Usado en bienes muebles.
- **Referencias polimórficas**: `bien_tipo` ENUM + `bien_id` sin FK. Usado en bienes litigiosos.
- **Relaciones M:N**: Tabla pivote con FKs compuestas. Usado en tipos de herencia.

### 15.3 Motor y Codificación

- Motor: InnoDB (soporte de FK y transacciones)
- Charset: utf8mb4
- Collation: utf8mb4_unicode_ci (soporte completo de caracteres especiales del español)

---

## 16. Diagrama de Relaciones Principales

```
sim_casos_estudios (centro)
    ├── FK → sim_personas (causante)
    ├── FK → sim_personas (representante)
    ├── FK → profesores (creador)
    ├── FK → sim_cat_unidades_tributarias (UT del caso)
    │
    ├── sim_caso_participantes → sim_personas + sim_cat_parentescos → sim_cat_grupos_tarifa
    ├── sim_caso_tipoherencia_rel → sim_cat_tipoherencias
    ├── sim_caso_prorrogas
    │
    ├── sim_caso_bienes_inmuebles → sim_cat_tipos_bien_inmueble
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
    ├── sim_caso_exoneraciones
    │
    └── sim_caso_asignaciones → estudiantes
            └── sim_intentos (espejo completo de la capa maestro)

sim_cat_tarifas_sucesion → sim_cat_grupos_tarifa ← sim_cat_parentescos
```

---

## 17. Conteo Final

| Categoría | Cantidad |
|-----------|----------|
| Tablas del sistema base | 19 |
| Tablas de geografía | 5 |
| Tablas del simulador — Capa maestro | 24 |
| Tablas del simulador — Capa intento | 24 |
| Tablas del simulador — Catálogos | 12 |
| Tablas del simulador — Entidades globales | 5 |
| **Total** | **84** (+ 5 pendientes de secciones calculadas) |
| **Foreign keys** | **125** |
