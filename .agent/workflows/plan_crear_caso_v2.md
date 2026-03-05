# Plan de Corrección: Formulario `crear_caso.php` vs Base de Datos

> **Proyecto:** Simulador Educativo SENIAT — Declaración Sucesoral  
> **Objetivo:** Alinear todos los campos del formulario del profesor (master layer) con las tablas reales de la base de datos `spdss`.  
> **Referencia:** Análisis realizado sobre `crear_caso.php`, los 9 archivos JS del stepper, el export de `information_schema.COLUMNS`, foreign keys, y el JSON de ejemplo del payload.  
> **Nota general sobre auditoría:** Las tablas de la BD tienen campos automáticos (`id`, `created_at`, `updated_at`, `deleted_at`, `created_by`) que el backend maneja internamente. No se mencionan en cada sección para no ser redundante, pero el backend debe poblarlos al insertar/actualizar.

---

## Índice

1. [Separar Datos del Caso de Configuración del Caso](#1-separar-datos-del-caso-de-configuración-del-caso)
2. [Unidad Tributaria: de texto libre a select de catálogo](#2-unidad-tributaria-de-texto-libre-a-select-de-catálogo)
3. [Tipo de Herencia: agregar campos condicionales](#3-tipo-de-herencia-agregar-campos-condicionales)
4. [Datos del Causante: campos faltantes de datos fiscales y acta de defunción](#4-datos-del-causante-campos-faltantes-de-datos-fiscales-y-acta-de-defunción)
5. [Nacionalidad: de hardcoded a catálogo dinámico](#5-nacionalidad-de-hardcoded-a-catálogo-dinámico)
6. [Domicilio Fiscal: corrección de nombres y valores de campos](#6-domicilio-fiscal-corrección-de-nombres-y-valores-de-campos)
7. [Representante de la Sucesión: campos NOT NULL faltantes](#7-representante-de-la-sucesión-campos-not-null-faltantes)
8. [Herederos: campos faltantes y catálogos dinámicos](#8-herederos-campos-faltantes-y-catálogos-dinámicos)
9. [Bienes Inmuebles: datos registrales, nombres de campos y detalle litigioso](#9-bienes-inmuebles-datos-registrales-nombres-de-campos-y-detalle-litigioso)
10. [Bienes Muebles: modales diferenciados por categoría](#10-bienes-muebles-modales-diferenciados-por-categoría)
11. [Pasivos (Deudas): campos condicionales y FKs a catálogos](#11-pasivos-deudas-campos-condicionales-y-fks-a-catálogos)
12. [Pasivos (Gastos): FK a catálogo en lugar de string](#12-pasivos-gastos-fk-a-catálogo-en-lugar-de-string)
13. [Prórrogas del Caso](#13-prórrogas-del-caso)
14. [Resumen general: estado de cada tabla vs formulario](#14-resumen-general-estado-de-cada-tabla-vs-formulario)

---

## 1. Separar Datos del Caso de Configuración del Caso

### Problema actual

En `state.js`, el objeto `caseData.caso` mezcla campos que pertenecen a dos tablas distintas en la BD:

- `titulo`, `descripcion`, `estado` → van a `sim_casos_estudios`
- `modalidad`, `max_intentos`, `fecha_limite` → van a `sim_caso_configs`

La BD diseñó esta separación para que un mismo caso pueda tener múltiples configuraciones (por ejemplo, asignar el mismo caso como Práctica Libre a una sección y como Evaluación a otra). El formulario actual los trata como si fueran una sola cosa.

Además, `sim_caso_configs` tiene un campo `profesor_id` (FK a `profesores`) que el formulario no contempla — el backend debe llenarlo desde la sesión.

### Qué hacer

**Paso 1 — Separar el state en JS.** En `state.js`, reemplazar el objeto `caso` por dos objetos separados:

- `caso`: que contenga solo `titulo`, `descripcion` y `estado` (estos van a `sim_casos_estudios`).
- `config`: que contenga `modalidad`, `max_intentos` y `fecha_limite` (estos van a `sim_caso_configs`).

**Paso 2 — Separar visualmente en el PHP.** Actualmente todo está en una sola tarjeta "Datos del Caso". Dividir en dos tarjetas:

- **Tarjeta 1: "Datos del Caso"** — título (input text, requerido), descripción (textarea, opcional). El estado no se muestra como campo editable; se mantiene como badge "Borrador" que cambia automáticamente al publicar.
- **Tarjeta 2: "Configuración de Asignación"** — modalidad (select: Practica_Libre / Evaluacion), máximo de intentos (number, default 0 = ilimitados), fecha límite (datetime-local, solo visible si modalidad = Evaluacion).

**Paso 3 — Actualizar data-bind.** Cambiar los `data-bind` de los inputs de configuración de `caso.modalidad` a `config.modalidad`, `caso.max_intentos` a `config.max_intentos`, y `caso.fecha_limite` a `config.fecha_limite`.

**Paso 4 — Actualizar la lógica reactiva.** En `state.js`, la función `onStateChange` que muestra/oculta el campo `fecha_limite` actualmente busca `caseData.caso.modalidad`. Debe cambiarse a `caseData.config.modalidad`.

**Paso 5 — Actualizar el resumen (Step 3).** En `summary.js`, la línea que lee `caseData.caso.modalidad` para el badge del resumen debe leer de `caseData.config.modalidad`.

**Paso 6 — Backend.** Al guardar, el backend debe:
1. Insertar en `sim_casos_estudios` (titulo, descripcion, estado, profesor_id, causante_id, representante_id, unidad_tributaria_id).
2. Insertar en `sim_caso_configs` (caso_id, profesor_id, modalidad, max_intentos, fecha_limite). Notar que `profesor_id` se toma de la sesión del usuario logueado, no del formulario.

### Campos involucrados

| Campo frontend | Tabla BD destino | Columna BD | Acción |
|---|---|---|---|
| `caso.titulo` | `sim_casos_estudios` | `titulo` | Mover a `caseData.caso` (ya está) |
| `caso.descripcion` | `sim_casos_estudios` | `descripcion` | Mover a `caseData.caso` (ya está) |
| `caso.estado` | `sim_casos_estudios` | `estado` | Mover a `caseData.caso` (ya está) |
| `caso.modalidad` | `sim_caso_configs` | `modalidad` | Mover a `caseData.config` |
| `caso.max_intentos` | `sim_caso_configs` | `max_intentos` | Mover a `caseData.config` |
| `caso.fecha_limite` | `sim_caso_configs` | `fecha_limite` | Mover a `caseData.config` |
| (no existe en frontend) | `sim_caso_configs` | `profesor_id` | Backend lo toma de la sesión |

---

## 2. Unidad Tributaria: de texto libre a select de catálogo

### Problema actual

El formulario tiene un campo `valor_ut` como input de texto de "referencia visual" dentro de `caseData.causante`. Sin embargo, en la BD el valor de la unidad tributaria está en `sim_casos_estudios.unidad_tributaria_id`, que es una FK a la tabla `sim_cat_unidades_tributarias` (catálogo de UT por año con su valor numérico). Es decir: el campo está en el objeto equivocado, con el tipo de widget equivocado, y apuntando a la tabla equivocada.

### Qué hacer

**Paso 1 — Mover el campo del causante al caso.** Eliminar `valor_ut` de `caseData.causante` en `state.js`. Agregar `unidad_tributaria_id` dentro de `caseData.caso`.

**Paso 2 — Cambiar el widget en el PHP.** Reemplazar el input de texto `valor_ut` por un `<select>` con `data-bind="caso.unidad_tributaria_id"`. Este select se cargará dinámicamente desde la API.

**Paso 3 — Crear endpoint API.** El backend debe exponer un endpoint (por ejemplo `GET /api/unidades-tributarias`) que devuelva el catálogo `sim_cat_unidades_tributarias` con `id`, `anio` y `valor`. El select mostrará algo como "2024 — Bs. 9,00" y guardará el `id`.

**Paso 4 — Cargar dinámicamente.** En `direccion.js` o en un nuevo archivo `catalogos.js`, crear una función `fetchUnidadesTributarias()` que haga fetch al endpoint y llene el select. Llamarla en `init()` de `main.js`.

**Paso 5 — Mover visualmente.** El select de UT debe estar en la tarjeta "Datos del Caso" (junto a título y descripción), no en "Datos del Causante", porque es un atributo del caso.

### Campos involucrados

| Campo frontend actual | Acción | Campo BD destino |
|---|---|---|
| `causante.valor_ut` (input text) | ELIMINAR | N/A |
| (nuevo) `caso.unidad_tributaria_id` (select) | CREAR | `sim_casos_estudios.unidad_tributaria_id` → FK a `sim_cat_unidades_tributarias.id` |

---

## 3. Tipo de Herencia: agregar campos condicionales

### Problema actual

El formulario muestra checkboxes planos para los tipos de herencia (Testamento, Ab-Intestato, Pura y Simple, etc.) y solo guarda un array de strings. La BD tiene la tabla `sim_caso_tipoherencia_rel` con campos adicionales condicionales:

- `subtipo_testamento` (ENUM: Abierto/Cerrado) — solo si el tipo es "Testamento"
- `fecha_testamento` (date) — solo si el tipo es "Testamento"
- `fecha_conclusion_inventario` (date) — solo si el tipo es "Beneficio de Inventario"

Además, el formulario guarda strings de texto pero la BD espera `tipo_herencia_id` como FK entero a `sim_cat_tipoherencias`.

### Qué hacer

**Paso 1 — Cambiar el state.** En `state.js`, el array `herencia.tipos` actualmente guarda strings simples (ej: `["Testamento", "Ab-Intestato"]`). Debe cambiarse para que cada elemento sea un objeto con la estructura: `{ tipo_herencia_id: int, subtipo_testamento: string|null, fecha_testamento: string|null, fecha_conclusion_inventario: string|null }`.

**Paso 2 — Cargar tipos desde catálogo.** Eliminar el array hardcoded `TIPOS_HERENCIA` de `state.js`. Crear un endpoint `GET /api/tipos-herencia` que devuelva `sim_cat_tipoherencias` con `id` y `nombre`. Cargar dinámicamente en `herederos.js` (función `renderHerenciaCheckboxes`).

**Paso 3 — Agregar campos condicionales en el PHP/JS.** Cuando el usuario marca el checkbox de "Testamento", debe aparecer debajo:

- Un select "Subtipo de Testamento" con opciones: Abierto / Cerrado.
- Un input date "Fecha del Testamento".

Cuando marca "Beneficio de Inventario", debe aparecer:

- Un input date "Fecha de Conclusión del Inventario".

Cuando desmarca cualquiera de estos, los campos condicionales deben ocultarse y sus valores limpiarse.

**Paso 4 — Actualizar `renderHerenciaCheckboxes`.** En `herederos.js`, modificar la función para que cada checkbox card tenga un contenedor colapsable debajo que muestre los campos condicionales según el tipo. Al hacer check/uncheck, mostrar/ocultar ese contenedor y actualizar el objeto correspondiente en el array `caseData.herencia.tipos`.

**Paso 5 — Actualizar el resumen.** En `summary.js`, la línea que muestra `caseData.herencia.tipos.join(', ')` debe adaptarse para mostrar los nombres de los tipos (consultados por ID desde el catálogo cargado) en lugar de strings directos.

### Campos involucrados

| Campo frontend actual | Acción | Campo BD destino |
|---|---|---|
| `herencia.tipos` (array de strings) | CORREGIR → array de objetos con `tipo_herencia_id` | `sim_caso_tipoherencia_rel.tipo_herencia_id` |
| (no existe) | CREAR → select condicional | `sim_caso_tipoherencia_rel.subtipo_testamento` |
| (no existe) | CREAR → input date condicional | `sim_caso_tipoherencia_rel.fecha_testamento` |
| (no existe) | CREAR → input date condicional | `sim_caso_tipoherencia_rel.fecha_conclusion_inventario` |

---

## 4. Datos del Causante: campos faltantes de datos fiscales y acta de defunción

### Problema actual

Los datos del causante alimentan 3 tablas en la BD: `sim_personas` (datos personales), `sim_causante_datos_fiscales` (datos fiscales específicos del causante) y `sim_actas_defunciones` (datos del acta de defunción). El formulario actual solo captura campos de `sim_personas` y omite las otras dos tablas por completo.

### Qué hacer

**Paso 1 — Agregar sección "Datos Fiscales del Causante" en el PHP.** Crear una nueva tarjeta colapsable dentro del Step 0, después de "Datos del Causante", con los siguientes campos:

- `domiciliado_pais` — select con opciones Sí (1) / No (0). Indica si el causante estaba domiciliado en Venezuela al momento del fallecimiento. Este dato es relevante porque determina el alcance territorial de los bienes a declarar. **Campo NOT NULL en la BD (default 1).**
- `fecha_cierre_fiscal` — input date. Fecha del cierre del ejercicio fiscal del causante. **Campo NOT NULL en la BD, sin default — es requerido.**

**Paso 2 — Agregar sección "Acta de Defunción" en el PHP.** Crear otra tarjeta colapsable dentro del Step 0 con:

- `numero_acta` — input text. Número del acta de defunción emitida por el registro civil. (Nullable en BD.)
- `year_acta` — input number (min 1900). Año del acta de defunción. **NOT NULL en BD.** La BD tiene un CHECK constraint `chk_acta_defuncion_year_min_1900`.
- `parroquia_registro_id` — select dinámico dependiente de estado/municipio. Parroquia donde se registró la defunción. Es FK a la tabla de parroquias. **NOT NULL en BD — es requerido.**

**Paso 3 — Nota sobre `fecha_fallecimiento`.** El campo `sim_actas_defunciones.fecha_fallecimiento` (NOT NULL) ya se captura desde `caseData.causante.fecha_fallecimiento` (que tiene `data-bind="causante.fecha_fallecimiento"` en el PHP). El backend debe reutilizar este valor al insertar en `sim_actas_defunciones` — no es necesario crear un campo duplicado.

**Paso 4 — Actualizar el state.** En `state.js`, agregar dos nuevos objetos:

- `datos_fiscales_causante: { domiciliado_pais: '1', fecha_cierre_fiscal: '' }`
- `acta_defuncion: { numero_acta: '', year_acta: '', parroquia_registro_id: '' }`

**Paso 5 — Agregar data-bind.** Cada input debe tener su `data-bind` correspondiente: `datos_fiscales_causante.domiciliado_pais`, `datos_fiscales_causante.fecha_cierre_fiscal`, `acta_defuncion.numero_acta`, etc.

**Paso 6 — Selects dinámicos para parroquia del acta.** La parroquia donde se registró la defunción necesita su propia cadena de selects estado → municipio → parroquia, independiente de la del domicilio fiscal. En `direccion.js`, crear funciones `fetchEstadosActa()`, `fetchMunicipiosActa(estadoId)`, `fetchParroquiasActa(municipioId)` que usen los mismos endpoints pero llenen selects diferentes (con data-bind prefijado `acta_defuncion.*`).

### Campos faltantes

| Campo a crear | Tabla BD destino | Columna BD | Tipo widget | NOT NULL | Condiciones |
|---|---|---|---|---|---|
| `domiciliado_pais` | `sim_causante_datos_fiscales` | `domiciliado_pais` | Select (Sí=1/No=0) | **Sí** (default 1) | Siempre visible |
| `fecha_cierre_fiscal` | `sim_causante_datos_fiscales` | `fecha_cierre_fiscal` | Input date | **Sí** | Siempre visible, requerido |
| `numero_acta` | `sim_actas_defunciones` | `numero_acta` | Input text | No | Siempre visible |
| `year_acta` | `sim_actas_defunciones` | `year_acta` | Input number (min 1900) | **Sí** | Siempre visible, requerido |
| `parroquia_registro_id` | `sim_actas_defunciones` | `parroquia_registro_id` | Select dinámico (cascada estado→municipio→parroquia) | **Sí** | Siempre visible, requerido |
| (ya existe: `causante.fecha_fallecimiento`) | `sim_actas_defunciones` | `fecha_fallecimiento` | — | **Sí** | Backend reutiliza del causante |

---

## 5. Nacionalidad: de hardcoded a catálogo dinámico

### Problema actual

El formulario tiene un select de nacionalidad con solo 3 opciones hardcoded: Venezuela=1, Colombia=2, Otro=3. La BD espera una FK a la tabla `paises` (smallint unsigned) que contiene todos los países. La columna en `sim_personas` se llama `nacionalidad` (no `pais_id`), así que el nombre del campo en el state puede mantenerse.

### Qué hacer

**Paso 1 — Crear endpoint API.** `GET /api/paises` que devuelva `id` y `nombre` de la tabla `paises`, ordenado alfabéticamente.

**Paso 2 — Reemplazar select hardcoded.** En el PHP, reemplazar el `<select>` con las 3 opciones por un select vacío con `data-bind="causante.nacionalidad"` que se llene dinámicamente.

**Paso 3 — Cargar dinámicamente.** En `direccion.js` o `catalogos.js`, crear `fetchPaises()` que llene el select. Llamar en `init()`.

**Paso 4 — El valor guardado debe ser el `id` del país** (entero), no un string. El nombre del campo en el state (`nacionalidad`) ya coincide con la columna de `sim_personas`.

---

## 6. Domicilio Fiscal: corrección de nombres y valores de campos

### Problema actual

Hay dos tipos de discrepancias entre el formulario y la BD:

1. **Nombres de campos:** Los nombres en `state.js` (`caseData.domicilio_causante`) no coinciden con las columnas de `sim_persona_direcciones`.
2. **Valores de ENUM:** Los radio buttons y selects del PHP envían valores en minúscula o con espacios/acentos, pero la BD usa ENUMs con capitalización específica (ej: `Edificio`, no `edificio`; `Urbanizacion`, no `urbanizacion`; `Domicilio_Fiscal`, no `DOMICILIO FISCAL`).

Si el backend guarda los valores tal cual vienen del frontend, los INSERTs fallarán porque MariaDB/MySQL rechaza valores que no coinciden exactamente con los definidos en el ENUM.

### Qué hacer

**Paso 1 — Renombrar campos en `state.js` y en los `data-bind` del PHP.**

| Campo actual en `state.js` / PHP `data-bind` | Columna correcta en `sim_persona_direcciones` | Acción |
|---|---|---|
| `vialidad` | `tipo_vialidad` | Renombrar |
| `tipo_vivienda` | `tipo_inmueble` | Renombrar |
| `nombre_vivienda` | `nro_inmueble` | Renombrar |
| `sub_vivienda` | `tipo_nivel` | Renombrar |
| `nro_piso` | `nro_nivel` | Renombrar |
| `zona_postal` | `codigo_postal_id` | Renombrar (el select dinámico ya guarda el ID del catálogo `codigos_postales`) |

**Paso 2 — Corregir los valores de los radio buttons y selects para que coincidan con los ENUMs de la BD.** A continuación se listan las correcciones valor por valor:

**`tipo_direccion`** (actualmente es `<select>` con strings largos):

| Valor actual en PHP | Valor correcto ENUM en BD |
|---|---|
| `BODEGA, ALMACENAMIENTO, DEPÓSITO` | `Bodega_Almacenamiento_Deposito` |
| `CASA MATRIZ O ESTABLECIMIENTO PRINCIPAL` | `Casa_Matriz_Establecimiento_Principal` |
| `DIRECCIÓN DE NOTIFICACIÓN FÍSICA` | `Direccion_Notificacion_Fisica` |
| `DOMICILIO FISCAL` | `Domicilio_Fiscal` |
| `NEGOCIO INDEPENDIENTE` | `Negocio_Independiente` |
| `PLANTA INDUSTRIAL O FABRICA` | `Planta_Industrial_Fabrica` |
| `SUCURSAL COMERCIAL` | `Sucursal_Comercial` |

**`tipo_vialidad`** (actualmente radio buttons con name `vialidad`):

| Valor actual en PHP | Valor correcto ENUM en BD |
|---|---|
| `calle` | `Calle` |
| `avenida` | `Avenida` |
| `vereda` | `Vereda` |
| `carretera` | `Carretera` |
| `esquina` | `Esquina` |
| `carrera` | `Carrera` |

**`tipo_inmueble`** (actualmente radio buttons con name `tipo_vivienda`):

| Valor actual en PHP | Valor correcto ENUM en BD |
|---|---|
| `edificio` | `Edificio` |
| `centro_comercial` | `Centro_Comercial` |
| `quinta` | `Quinta` |
| `casa` | `Casa` |
| `local` | `Local` |

**`tipo_nivel`** (actualmente radio buttons con name `sub_vivienda`):

| Valor actual en PHP | Valor correcto ENUM en BD |
|---|---|
| `apartamento` | `Apartamento` |
| `local` | `Local` |
| `oficina` | `Oficina` |

**`tipo_sector`** (actualmente radio buttons):

| Valor actual en PHP | Valor correcto ENUM en BD |
|---|---|
| `urbanizacion` | `Urbanizacion` |
| `zona` | `Zona` |
| `sector` | `Sector` |
| `conjunto_residencial` | `Conjunto_Residencial` |
| `barrio` | `Barrio` |
| `caserio` | `Caserio` |

**Paso 3 — Alternativa: mapear en backend.** Si prefieres no tocar todos los radio buttons del PHP, puedes hacer un mapeo en el backend (ej: `ucfirst($value)` o un diccionario de traducción). Pero es más seguro corregir los values en el HTML directamente para evitar errores de conversión.

**Nota:** Los demás campos (`nombre_vialidad`, `nombre_sector`, `telefono_fijo`, `telefono_celular`, `fax`, `punto_referencia`) son varchar y no tienen restricción ENUM, por lo que sus valores no necesitan corrección. Los campos `estado`, `municipio`, `parroquia`, `ciudad` ya guardan el ID numérico del select dinámico y coinciden con `estado_id`, `municipio_id`, `parroquia_id`, `ciudad_id` en la BD.

---

## 7. Representante de la Sucesión: campos NOT NULL faltantes

### Problema actual

El formulario solo pide 4 campos del representante: `tipo_cedula`, `cedula`, `nombres`, `apellidos`. El representante se guarda como un registro en `sim_personas` referenciado desde `sim_casos_estudios.representante_id`. La tabla `sim_personas` tiene como NOT NULL: `tipo_cedula`, `nombres`, `apellidos`, `fecha_nacimiento`, `estado_civil`, `sexo`, y `created_by`. Faltan 3 campos obligatorios que impiden hacer el INSERT.

### Qué hacer

**Paso 1 — Agregar campos al formulario.** En la tarjeta "Representante de la Sucesión" del PHP, agregar:

- `sexo` — select con opciones M / F. Con `data-bind="representante.sexo"`.
- `estado_civil` — select con opciones Soltero / Casado / Viudo / Divorciado. Con `data-bind="representante.estado_civil"`.
- `fecha_nacimiento` — input date. Con `data-bind="representante.fecha_nacimiento"`.

**Paso 2 — Actualizar el state.** En `state.js`, agregar estos campos al objeto `representante`:

```
representante: { tipo_cedula: '', cedula: '', nombres: '', apellidos: '', sexo: '', estado_civil: '', fecha_nacimiento: '', pasaporte: '', rif_personal: '', nacionalidad: '' }
```

**Paso 3 — Campos opcionales recomendados.** El JSON de ejemplo del payload también incluye `pasaporte`, `rif_personal` y `nacionalidad` para el representante. Aunque son nullable en `sim_personas`, es recomendable agregarlos al formulario para completitud. `nacionalidad` usaría el mismo select dinámico de países (Sección 5).

**Paso 4 — (Opcional pero recomendable) Agregar dirección del representante.** Si el formulario real del SENIAT pide dirección propia del representante, agregar un bloque de dirección similar al del causante pero con `data-bind="domicilio_representante.*"`. Esto implicaría otro registro en `sim_persona_direcciones` ligado al `persona_id` del representante.

### Campos faltantes

| Campo a crear | Tabla BD | Columna | Tipo widget | NOT NULL en BD |
|---|---|---|---|---|
| `representante.sexo` | `sim_personas` | `sexo` | Select (M/F) | **Sí** |
| `representante.estado_civil` | `sim_personas` | `estado_civil` | Select (Soltero/Casado/Viudo/Divorciado) | **Sí** |
| `representante.fecha_nacimiento` | `sim_personas` | `fecha_nacimiento` | Input date | **Sí** |
| `representante.pasaporte` | `sim_personas` | `pasaporte` | Input text | No |
| `representante.rif_personal` | `sim_personas` | `rif_personal` | Input text | No |
| `representante.nacionalidad` | `sim_personas` | `nacionalidad` | Select dinámico (países) | No |

---

## 8. Herederos: campos faltantes y catálogos dinámicos

### Problema actual

El modal de heredero (en `modal.js`, config `heredero`) tiene 8 campos: nombres, apellidos, tipo_cedula, cedula, fecha_nacimiento, caracter, parentesco, premuerto. Faltan campos NOT NULL de `sim_personas` y el parentesco usa un array hardcoded en lugar de FK al catálogo.

### Qué hacer

**Paso 1 — Agregar campos NOT NULL faltantes al modal.** Agregar estos campos al HTML generado por `MODAL_CONFIGS.heredero.build()`:

- `sexo` — select M / F, con `data-modal="sexo"`.
- `estado_civil` — select Soltero / Casado / Viudo / Divorciado, con `data-modal="estado_civil"`.

**Paso 2 — Migrar parentesco a catálogo dinámico.** Eliminar el array `PARENTESCOS` hardcoded de `state.js`. Crear un endpoint `GET /api/parentescos` que devuelva `sim_cat_parentescos` con `id`, `clave` y `etiqueta`. En el modal, el select de parentesco debe cargarse dinámicamente y guardar `parentesco_id` (entero) en lugar del string de texto.

Esto implica cambiar `data-modal="parentesco"` a `data-modal="parentesco_id"` y que el select se llene con `<option value="ID">Etiqueta</option>`.

**Paso 3 — Agregar campo `premuerto_padre_id`.** Cuando un heredero hereda "en representación de" un premuerto, necesita indicarse la relación. Agregar un select condicional `data-modal="premuerto_padre_id"` que aparezca solo cuando `premuerto = NO`, y cuyas opciones sean los herederos del caso que están marcados como `premuerto = SI`. El label sería "Hereda en representación de" y sería opcional.

Para poblar este select, al abrir el modal se deben consultar los herederos ya registrados en `caseData.herederos` que tengan `es_premuerto === true`.

**Nota sobre orden de carga:** Si el profesor necesita marcar un heredero como "hereda en representación de" un premuerto, primero debe crear al heredero premuerto y luego crear o editar al representante para asignarle el `premuerto_padre_id`. El formulario debe permitir editar herederos ya creados para asignar esta relación después.

**Paso 4 — Agregar campos opcionales de `sim_personas`.** Los siguientes campos son nullable pero enriquecen el caso:

- `pasaporte` — input text.
- `rif_personal` — input text.
- `nacionalidad` — select dinámico del catálogo `paises` (misma lógica que Sección 5).

**Paso 5 — Actualizar `renderHerederos` en `herederos.js`.** La tabla HTML de herederos debería asegurar que los nuevos campos se capturan en el modal aunque no se muestren todos en la tabla resumen.

**Paso 6 — Actualizar defaults en `openModal`.** En `modal.js`, el bloque `else if (type === 'heredero')` debe agregar los nuevos campos al `formData` default: `sexo: '', estado_civil: '', parentesco_id: '', premuerto_padre_id: null`.

### Campos faltantes en el modal

| Campo a crear | Tabla BD | Columna | Tipo widget | Prioridad |
|---|---|---|---|---|
| `sexo` | `sim_personas` | `sexo` | Select (M/F) | Crítico (NOT NULL) |
| `estado_civil` | `sim_personas` | `estado_civil` | Select | Crítico (NOT NULL) |
| `parentesco_id` (reemplaza `parentesco` string) | `sim_caso_participantes` | `parentesco_id` | Select dinámico del catálogo | Crítico (NOT NULL) |
| `premuerto_padre_id` | `sim_caso_participantes` | `premuerto_padre_id` | Select condicional (herederos premuertos) | Menor |
| `pasaporte` | `sim_personas` | `pasaporte` | Input text | Opcional |
| `rif_personal` | `sim_personas` | `rif_personal` | Input text | Opcional |
| `nacionalidad` | `sim_personas` | `nacionalidad` | Select dinámico catálogo países | Opcional |

---

## 9. Bienes Inmuebles: datos registrales, nombres de campos y detalle litigioso

### Problema actual

El modal de inmueble captura 11 campos (tipo, vivienda_principal, bien_litigioso, porcentaje, descripcion, superficie_construida, superficie_no_construida, area_total, direccion, valor_original, valor_declarado). Pero la tabla `sim_caso_bienes_inmuebles` tiene 10 campos adicionales de datos registrales que el formulario no pide. Además, cuando `es_bien_litigioso = Si`, la BD tiene una tabla separada `sim_caso_bienes_litigiosos` con 4 campos de detalle del litigio que tampoco se capturan. También hay discrepancias de nombres de campos entre el frontend y la BD.

### Qué hacer

**Paso 1 — Corregir nombres de campos que no coinciden con la BD.**

| Campo actual en el modal (JS) | Columna correcta en `sim_caso_bienes_inmuebles` | Acción |
|---|---|---|
| `area_total` | `area_superficie` | Renombrar en `data-modal` y en `state.js` |

Los demás campos del modal (`porcentaje`, `descripcion`, `superficie_construida`, `superficie_no_construida`, `direccion`, `valor_original`, `valor_declarado`) coinciden con los nombres de columna de la BD.

**Paso 2 — Agregar sección "Datos Registrales" al modal de inmueble.** Dentro de `MODAL_CONFIGS.inmueble.build()` en `modal.js`, después de los campos de dirección y antes de los valores, agregar un bloque con estos campos:

- `linderos` — textarea. Descripción de los linderos del inmueble.
- `oficina_registro` — input text. Nombre de la oficina de registro.
- `nro_registro` — input text. Número de registro.
- `libro` — input text.
- `protocolo` — input text.
- `fecha_registro` — input date.
- `trimestre` — input text.
- `asiento_registral` — input text.
- `matricula` — input text.
- `folio_real_anio` — input text.

**Paso 3 — Cambiar `tipo` de string a FK.** El campo `tipo` actualmente usa un grid de botones con strings de texto. La BD espera `tipo_bien_inmueble_id` (FK a `sim_cat_tipos_bien_inmueble`). Crear endpoint `GET /api/tipos-bien-inmueble` y cargar el grid dinámicamente. Cambiar `data-tipo` de string a ID. En `collect()`, guardar el ID numérico, no el string.

**Paso 4 — Agregar campos condicionales para bien litigioso.** Cuando el usuario selecciona `bien_litigioso = Si`, deben aparecer 4 campos adicionales:

- `numero_expediente` — input text.
- `tribunal_causa` — input text.
- `partes_juicio` — textarea (varchar 255 en BD).
- `estado_juicio` — input text.

Estos se guardan en `sim_caso_bienes_litigiosos`. Esta tabla usa un diseño polimórfico: tiene `bien_tipo` (ENUM: 'Inmueble'/'Mueble') y `bien_id` (ID del bien en la tabla correspondiente), más `caso_estudio_id`. Al guardar un inmueble litigioso, el backend inserta en `sim_caso_bienes_litigiosos` con `bien_tipo='Inmueble'` y `bien_id` = el ID del inmueble recién insertado.

**Paso 5 — Implementar lógica condicional.** En `MODAL_CONFIGS.inmueble.build()`, el HTML del bloque litigioso debe tener un contenedor con `id` o clase que se pueda mostrar/ocultar. Después de renderizar el modal, agregar un event listener al select `bien_litigioso` que muestre/oculte ese bloque.

**Paso 6 — Actualizar `collect()`.** La función `collect` del inmueble debe recoger los campos litigiosos solo si `bien_litigioso = Si` y guardarlos como un sub-objeto `litigioso: { numero_expediente, tribunal_causa, partes_juicio, estado_juicio }`. Si `bien_litigioso = No`, guardar `litigioso: null`.

### Campos faltantes — Datos registrales

| Campo a crear | Columna BD | Tipo widget |
|---|---|---|
| `linderos` | `sim_caso_bienes_inmuebles.linderos` | Textarea |
| `oficina_registro` | `sim_caso_bienes_inmuebles.oficina_registro` | Input text |
| `nro_registro` | `sim_caso_bienes_inmuebles.nro_registro` | Input text |
| `libro` | `sim_caso_bienes_inmuebles.libro` | Input text |
| `protocolo` | `sim_caso_bienes_inmuebles.protocolo` | Input text |
| `fecha_registro` | `sim_caso_bienes_inmuebles.fecha_registro` | Input date |
| `trimestre` | `sim_caso_bienes_inmuebles.trimestre` | Input text |
| `asiento_registral` | `sim_caso_bienes_inmuebles.asiento_registral` | Input text |
| `matricula` | `sim_caso_bienes_inmuebles.matricula` | Input text |
| `folio_real_anio` | `sim_caso_bienes_inmuebles.folio_real_anio` | Input text |

### Campos faltantes — Detalle litigioso (condicionales)

| Campo a crear | Columna BD | Tipo widget | Condición |
|---|---|---|---|
| `numero_expediente` | `sim_caso_bienes_litigiosos.numero_expediente` | Input text | Solo si `bien_litigioso = Si` |
| `tribunal_causa` | `sim_caso_bienes_litigiosos.tribunal_causa` | Input text | Solo si `bien_litigioso = Si` |
| `partes_juicio` | `sim_caso_bienes_litigiosos.partes_juicio` | Input text (max 255) | Solo si `bien_litigioso = Si` |
| `estado_juicio` | `sim_caso_bienes_litigiosos.estado_juicio` | Input text | Solo si `bien_litigioso = Si` |

### Campos a corregir

| Campo actual | Acción | Campo BD destino |
|---|---|---|
| `tipo` (string del grid) | Cambiar a `tipo_bien_inmueble_id` (FK entero) | `sim_caso_bienes_inmuebles.tipo_bien_inmueble_id` |
| `area_total` | Renombrar a `area_superficie` | `sim_caso_bienes_inmuebles.area_superficie` |

---

## 10. Bienes Muebles: modales diferenciados por categoría

### Problema actual

Este es el gap más grande del formulario. Las 12 subcategorías de bienes muebles (banco, seguro, transporte, opciones_compra, cuentas_cobrar, semovientes, bonos, acciones, prestaciones, caja_ahorro, plantaciones, otros) usan el mismo modal genérico con solo 4 campos: porcentaje, bien_litigioso, descripcion, valor_declarado. Pero la BD tiene tablas de detalle específicas para 10 de las 12 categorías con campos propios que el formulario ignora por completo.

Además hay dos campos de la tabla base `sim_caso_bienes_muebles` que no se capturan:

- `categoria_bien_mueble_id` (FK a `sim_cat_categorias_bien_mueble`) — se infiere del subtab activo pero no se guarda explícitamente.
- `tipo_bien_mueble_id` (FK a `sim_cat_tipos_bien_mueble` — subtipos dentro de cada categoría) — no se captura en absoluto.

Por último, las categorías están hardcoded en `CATEGORIAS_MUEBLE` de `state.js` con keys de texto (`banco`, `seguro`, etc.), pero la BD usa IDs numéricos en `sim_cat_categorias_bien_mueble`.

### Qué hacer

**Paso 1 — Migrar categorías de hardcoded a catálogo dinámico.** Eliminar el array `CATEGORIAS_MUEBLE` de `state.js`. Crear un endpoint `GET /api/categorias-bien-mueble` que devuelva `sim_cat_categorias_bien_mueble` con `id` y `nombre`. Cargar dinámicamente los subtabs en `inventario.js` (función `renderMuebleSubtabs`). El subtab activo (`UIState.currentSubTab`) debe guardar el `id` numérico de la categoría, no un string como `'banco'`.

**Paso 2 — Agregar `tipo_bien_mueble_id` como campo común.** Todos los bienes muebles (sin importar categoría) deben capturar este campo. Crear endpoint `GET /api/tipos-bien-mueble?categoria_id=X` que filtre los subtipos por categoría. El select mostrará los subtipos disponibles para la categoría activa. Este campo es nullable en la BD (comentario: "NULL para categorías sin subtipos como Plantaciones, Otros"), así que para categorías sin subtipos, el select simplemente no se muestra o se muestra deshabilitado.

**Paso 3 — Mapear `categoria_bien_mueble_id` desde el subtab activo.** Al guardar un bien mueble, el backend debe recibir `categoria_bien_mueble_id` como el ID numérico del subtab activo. El frontend lo agrega automáticamente al payload desde `UIState.currentSubTab` (que ahora es un ID numérico tras el Paso 1).

**Paso 4 — Crear modales diferenciados.** En `modal.js`, reemplazar el único `MODAL_CONFIGS.mueble` por un modal dinámico que genere HTML según la categoría activa, agregando los campos específicos después de los campos comunes.

**Paso 5 — Implementar los campos específicos por categoría:**

#### 10.1 — Banco (`sim_caso_bm_banco`)

- `banco_id` — select dinámico. Cargar desde `sim_cat_bancos` (endpoint `GET /api/bancos`). Muestra nombre del banco, guarda ID.
- `numero_cuenta` — input text (max 20 chars). Número de cuenta bancaria.

#### 10.2 — Seguro (`sim_caso_bm_seguro`)

- `empresa_id` — select dinámico o input text con autocompletado. Cargar desde `sim_empresas`. FK a `sim_empresas`.
- `numero_prima` — input text (max 15 chars). Número de póliza/prima.

#### 10.3 — Transporte (`sim_caso_bm_transporte`)

- `anio` — input text (max 4 chars). Año del vehículo.
- `marca` — input text (max 15 chars). Marca del vehículo.
- `modelo` — input text (max 15 chars). Modelo del vehículo.
- `serial_placa` — input text (max 30 chars). Serial o placa del vehículo.

#### 10.4 — Acciones (`sim_caso_bm_acciones`)

- `empresa_id` — select dinámico o input text con autocompletado. FK a `sim_empresas`.

#### 10.5 — Bonos (`sim_caso_bm_bonos`)

- `tipo_bonos` — input text (max 60 chars). Tipo de bonos.
- `numero_bonos` — input text (max 30 chars). Cantidad o número de bonos.
- `numero_serie` — input text (max 30 chars). Número de serie del bono.

#### 10.6 — Caja de Ahorro (`sim_caso_bm_caja_ahorro`)

- `empresa_id` — select dinámico o input text con autocompletado. FK a `sim_empresas`.

#### 10.7 — Cuentas por Cobrar (`sim_caso_bm_cuentas_cobrar`)

- `rif_cedula` — input text (max 12 chars). RIF o cédula del deudor.
- `apellidos_nombres` — input text (max 100 chars). Nombre completo del deudor.

#### 10.8 — Opciones de Compra (`sim_caso_bm_opciones_compra`)

- `nombre_oferente` — input text (max 40 chars). Nombre del oferente.

#### 10.9 — Prestaciones Sociales (`sim_caso_bm_prestaciones`)

- `posee_banco` — select Sí(1) / No(0). ¿Las prestaciones están depositadas en un banco? NOT NULL, default 0.
- `banco_id` — select dinámico de `sim_cat_bancos`. Solo visible si `posee_banco = 1`.
- `numero_cuenta` — input text (max 20 chars). Solo visible si `posee_banco = 1`.
- `empresa_id` — select dinámico de `sim_empresas`. Empresa donde laboró el causante.

#### 10.10 — Semovientes (`sim_caso_bm_semovientes`)

- `tipo_semoviente_id` — select dinámico. Cargar desde `sim_cat_tipos_semoviente` (endpoint `GET /api/tipos-semoviente`). Tipo de animal.
- `cantidad` — input number. Cantidad de cabezas.

#### 10.11 — Plantaciones (sin tabla de detalle)

El modal genérico actual es suficiente. No requiere campos adicionales.

#### 10.12 — Otros (sin tabla de detalle)

El modal genérico actual es suficiente. No requiere campos adicionales.

**Paso 6 — Agregar bloque litigioso condicional.** Igual que con los inmuebles (Sección 9, Paso 4), si el usuario marca `bien_litigioso = Si`, deben aparecer los 4 campos de `sim_caso_bienes_litigiosos`. El backend inserta con `bien_tipo='Mueble'` y `bien_id` = ID del bien mueble.

**Paso 7 — Actualizar `collect()` del modal mueble.** La función debe recoger: campos comunes (`categoria_bien_mueble_id`, `tipo_bien_mueble_id`, `porcentaje`, `es_bien_litigioso`, `descripcion`, `valor_declarado`), campos específicos como sub-objeto `detalle_*` según la categoría, y campos litigiosos como sub-objeto `litigioso` si aplica.

**Paso 8 — Cargar catálogos necesarios.** Crear los siguientes endpoints y funciones de fetch:

- `GET /api/categorias-bien-mueble` → para los subtabs
- `GET /api/tipos-bien-mueble?categoria_id=X` → para el select de subtipo
- `GET /api/bancos` → para banco, prestaciones
- `GET /api/empresas` → para seguro, acciones, caja_ahorro, prestaciones
- `GET /api/tipos-semoviente` → para semovientes

### Resumen de campos específicos por categoría

| Categoría | Tabla detalle | Campos a agregar | Catálogos necesarios |
|---|---|---|---|
| Banco | `sim_caso_bm_banco` | `banco_id`, `numero_cuenta` | `sim_cat_bancos` |
| Seguro | `sim_caso_bm_seguro` | `empresa_id`, `numero_prima` | `sim_empresas` |
| Transporte | `sim_caso_bm_transporte` | `anio`, `marca`, `modelo`, `serial_placa` | Ninguno |
| Acciones | `sim_caso_bm_acciones` | `empresa_id` | `sim_empresas` |
| Bonos | `sim_caso_bm_bonos` | `tipo_bonos`, `numero_bonos`, `numero_serie` | Ninguno |
| Caja Ahorro | `sim_caso_bm_caja_ahorro` | `empresa_id` | `sim_empresas` |
| Cuentas Cobrar | `sim_caso_bm_cuentas_cobrar` | `rif_cedula`, `apellidos_nombres` | Ninguno |
| Opciones Compra | `sim_caso_bm_opciones_compra` | `nombre_oferente` | Ninguno |
| Prestaciones | `sim_caso_bm_prestaciones` | `posee_banco`, `banco_id`, `numero_cuenta`, `empresa_id` | `sim_cat_bancos`, `sim_empresas` |
| Semovientes | `sim_caso_bm_semovientes` | `tipo_semoviente_id`, `cantidad` | `sim_cat_tipos_semoviente` |
| Plantaciones | (ninguna) | (ninguno extra) | — |
| Otros | (ninguna) | (ninguno extra) | — |

---

## 11. Pasivos (Deudas): campos condicionales y FKs a catálogos

### Problema actual

El modal de deudas usa el string del subtipo directamente, pero la BD espera `tipo_pasivo_deuda_id` (FK a `sim_cat_tipos_pasivo_deuda`). Además, faltan campos condicionales: `banco_id` (aplica para TDC, hipotecario y préstamos) y `numero_tdc` (solo para tarjetas de crédito).

### Qué hacer

**Paso 1 — Migrar subtipo a FK de catálogo.** Eliminar `TIPOS_PASIVO_DEUDA` hardcoded de `state.js`. Crear endpoint `GET /api/tipos-pasivo-deuda`. Cambiar el select del modal para que cargue dinámicamente y guarde `tipo_pasivo_deuda_id` (entero) en lugar del string `subtipo`.

**Paso 2 — Agregar campo condicional `banco_id`.** Para los tipos TDC, Crédito Hipotecario y Préstamos, el formulario debe mostrar un select de banco cargado desde `sim_cat_bancos` (mismo endpoint `GET /api/bancos` usado en muebles-banco). Este campo aparece solo cuando el tipo seleccionado es uno de esos tres.

**Paso 3 — Agregar campo condicional `numero_tdc`.** Solo visible cuando el tipo seleccionado es "Tarjetas de Crédito". Input text (max 20 chars) para el número de tarjeta.

**Paso 4 — Implementar la lógica condicional.** En `MODAL_CONFIGS.pasivo_deuda.build()`, agregar contenedores ocultos para `banco_id` y `numero_tdc`. Después de renderizar, agregar event listener al select de tipo que muestre/oculte según corresponda:

- Si tipo es TDC → mostrar `banco_id` + `numero_tdc`
- Si tipo es Hipotecario o Préstamos → mostrar solo `banco_id`
- Si tipo es Otros → no mostrar ninguno

### Campos involucrados

| Campo actual | Acción | Campo BD destino |
|---|---|---|
| `subtipo` (string) | CORREGIR → `tipo_pasivo_deuda_id` (FK entero) | `sim_caso_pasivos_deuda.tipo_pasivo_deuda_id` |
| (no existe) | CREAR → select condicional | `sim_caso_pasivos_deuda.banco_id` |
| (no existe) | CREAR → input condicional | `sim_caso_pasivos_deuda.numero_tdc` |

---

## 12. Pasivos (Gastos): FK a catálogo en lugar de string

### Problema actual

El modal de gastos usa un select con strings hardcoded en `TIPOS_PASIVO_GASTO` de `state.js`. La BD espera `tipo_pasivo_gasto_id` (FK a `sim_cat_tipos_pasivo_gasto`).

### Qué hacer

**Paso 1 — Migrar a catálogo dinámico.** Eliminar `TIPOS_PASIVO_GASTO` hardcoded de `state.js`. Crear endpoint `GET /api/tipos-pasivo-gasto`. El select del modal carga dinámicamente y guarda `tipo_pasivo_gasto_id` (entero).

**Paso 2 — Renombrar campo.** Cambiar `data-modal="tipo_gasto"` a `data-modal="tipo_pasivo_gasto_id"`.

**Paso 3 — Actualizar `renderCurrentTab`.** En `inventario.js`, la función `renderListSection` para gastos usa `item.tipo_gasto` para el nombre a mostrar. Debe adaptarse para buscar el nombre del tipo por ID en los datos del catálogo cargado.

### Campo a corregir

| Campo actual | Acción | Campo BD destino |
|---|---|---|
| `tipo_gasto` (string) | CORREGIR → `tipo_pasivo_gasto_id` (FK entero) | `sim_caso_pasivos_gastos.tipo_pasivo_gasto_id` |

---

## 13. Prórrogas del Caso

### Problema actual

La BD tiene una tabla `sim_caso_prorrogas` vinculada a `sim_casos_estudios` que permite registrar prórrogas solicitadas para la declaración sucesoral. Esta tabla no está contemplada en ninguna parte del formulario actual ni en el JavaScript. Las prórrogas son parte del proceso real del SENIAT y deben estar disponibles para que el profesor las configure como parte del caso.

### Qué hacer

**Paso 1 — Agregar sección "Prórrogas" en el Step 0 o Step 2.** Crear una nueva tarjeta colapsable (idealmente después del inventario patrimonial en el Step 2 o como parte del Step 0 después del domicilio fiscal) con un listado tipo "agregar/eliminar" similar al de exenciones/exoneraciones. Cada prórroga tiene los siguientes campos:

- `fecha_solicitud` — input date. Fecha en que se solicitó la prórroga. **NOT NULL en BD — es requerido.**
- `nro_resolucion` — input text (max 50 chars). Número de resolución emitida por el SENIAT. Nullable.
- `fecha_resolucion` — input date. Fecha de la resolución. Nullable.
- `plazo_otorgado_dias` — input number. Cantidad de días otorgados. Nullable.
- `fecha_vencimiento` — input date. Fecha en que vence la prórroga. Nullable.

**Paso 2 — Actualizar el state.** En `state.js`, agregar al `caseData`:

```
prorrogas: []
```

Cada prórroga en el array tendrá la estructura: `{ fecha_solicitud, nro_resolucion, fecha_resolucion, plazo_otorgado_dias, fecha_vencimiento }`.

**Paso 3 — Crear modal de prórroga.** En `modal.js`, agregar una nueva entrada `MODAL_CONFIGS.prorroga` con los 5 campos listados arriba. La lógica de agregar/eliminar es idéntica a la de exenciones: un botón "Agregar Prórroga" que abre el modal, y cada prórroga se muestra como card con botón de eliminar.

**Paso 4 — Actualizar `inventario.js` o crear sección propia.** Agregar la lógica de render para la lista de prórrogas, similar a `renderListSection`.

**Paso 5 — Actualizar el PHP.** Agregar el HTML de la sección con el empty state, el contenedor de la lista, y el botón de agregar, siguiendo el mismo patrón de exenciones/exoneraciones.

**Paso 6 — Backend.** Al guardar, insertar cada prórroga en `sim_caso_prorrogas` con el `caso_estudio_id` correspondiente.

### Campos de la tabla

| Campo | Columna BD | Tipo widget | NOT NULL |
|---|---|---|---|
| `fecha_solicitud` | `sim_caso_prorrogas.fecha_solicitud` | Input date | **Sí** |
| `nro_resolucion` | `sim_caso_prorrogas.nro_resolucion` | Input text | No |
| `fecha_resolucion` | `sim_caso_prorrogas.fecha_resolucion` | Input date | No |
| `plazo_otorgado_dias` | `sim_caso_prorrogas.plazo_otorgado_dias` | Input number | No |
| `fecha_vencimiento` | `sim_caso_prorrogas.fecha_vencimiento` | Input date | No |

---

## 14. Resumen general: estado de cada tabla vs formulario

### Tabla: `sim_casos_estudios`

| Columna BD | Estado | Detalle |
|---|---|---|
| `id` | OK | Auto-generado |
| `profesor_id` | OK | Se toma de la sesión del backend |
| `causante_id` | OK | FK generada al insertar el causante en `sim_personas` |
| `representante_id` | OK | FK generada al insertar el representante en `sim_personas` |
| `unidad_tributaria_id` | **FALTA** | Actualmente es texto libre `valor_ut` en causante. Debe ser select FK (Sección 2) |
| `titulo` | OK | `caseData.caso.titulo` |
| `descripcion` | OK | `caseData.caso.descripcion` |
| `estado` | OK | Manejado por lógica (Borrador → Publicado) |
| `created_at` / `updated_at` | OK | Automáticos |

### Tabla: `sim_caso_configs`

| Columna BD | Estado | Detalle |
|---|---|---|
| `id` | OK | Auto-generado |
| `caso_id` | OK | FK al caso recién creado |
| `profesor_id` | **NOTA** | No existe en frontend — el backend lo toma de la sesión (Sección 1) |
| `modalidad` | **CORREGIR** | Actualmente en `caseData.caso.modalidad`, debe moverse a `caseData.config.modalidad` (Sección 1) |
| `max_intentos` | **CORREGIR** | Actualmente en `caseData.caso.max_intentos`, debe moverse a `caseData.config` (Sección 1) |
| `fecha_limite` | **CORREGIR** | Actualmente en `caseData.caso.fecha_limite`, debe moverse a `caseData.config` (Sección 1) |

### Tabla: `sim_caso_tipoherencia_rel`

| Columna BD | Estado | Detalle |
|---|---|---|
| `caso_estudio_id` | OK | FK al caso |
| `tipo_herencia_id` | **CORREGIR** | Actualmente guarda string, debe guardar FK entero (Sección 3) |
| `subtipo_testamento` | **FALTA** | Campo condicional no capturado (Sección 3) |
| `fecha_testamento` | **FALTA** | Campo condicional no capturado (Sección 3) |
| `fecha_conclusion_inventario` | **FALTA** | Campo condicional no capturado (Sección 3) |

### Tabla: `sim_personas` (para causante, representante, herederos)

| Columna BD | Estado causante | Estado representante | Estado herederos |
|---|---|---|---|
| `tipo_cedula` | OK | OK | OK |
| `nacionalidad` | **CORREGIR** (Sección 5) — hardcoded, debe ser dinámico | Opcional (Sección 7) | Opcional (Sección 8) |
| `cedula` | OK | OK | OK |
| `pasaporte` | OK | Opcional (Sección 7) | Opcional |
| `rif_personal` | OK | Opcional (Sección 7) | Opcional |
| `nombres` | OK | OK | OK |
| `apellidos` | OK | OK | OK |
| `fecha_nacimiento` | OK | **FALTA** (Sección 7) | OK |
| `estado_civil` | OK | **FALTA** (Sección 7) | **FALTA** (Sección 8) |
| `sexo` | OK | **FALTA** (Sección 7) | **FALTA** (Sección 8) |
| `created_by` | OK | OK | OK — backend usa sesión del profesor |

### Tabla: `sim_causante_datos_fiscales`

| Columna BD | Estado | Detalle |
|---|---|---|
| `sim_persona_id` | OK | FK al causante |
| `domiciliado_pais` | **FALTA** | NOT NULL, default 1 — no capturado (Sección 4) |
| `fecha_cierre_fiscal` | **FALTA** | NOT NULL — no capturado (Sección 4) |

### Tabla: `sim_actas_defunciones`

| Columna BD | Estado | Detalle |
|---|---|---|
| `sim_persona_id` | OK | FK al causante |
| `fecha_fallecimiento` | OK | Se reutiliza de `causante.fecha_fallecimiento` (Sección 4, Paso 3) |
| `numero_acta` | **FALTA** | No capturado (Sección 4) |
| `year_acta` | **FALTA** | NOT NULL — no capturado (Sección 4) |
| `parroquia_registro_id` | **FALTA** | NOT NULL — no capturado (Sección 4) |

### Tabla: `sim_persona_direcciones` (domicilio causante)

| Columna BD | Estado | Detalle |
|---|---|---|
| `sim_persona_id` | OK | FK al causante |
| `tipo_direccion` | **CORREGIR** | Valores ENUM no coinciden (Sección 6) |
| `tipo_vialidad` | **CORREGIR** | Nombre (`vialidad`) y valores ENUM no coinciden (Sección 6) |
| `nombre_vialidad` | OK | — |
| `tipo_inmueble` | **CORREGIR** | Nombre (`tipo_vivienda`) y valores ENUM no coinciden (Sección 6) |
| `nro_inmueble` | **CORREGIR** | Nombre: frontend dice `nombre_vivienda` (Sección 6) |
| `tipo_nivel` | **CORREGIR** | Nombre (`sub_vivienda`) y valores ENUM no coinciden (Sección 6) |
| `nro_nivel` | **CORREGIR** | Nombre: frontend dice `nro_piso` (Sección 6) |
| `tipo_sector` | **CORREGIR** | Valores ENUM no coinciden — capitalización (Sección 6) |
| `nombre_sector` | OK | — |
| `estado_id` | OK | — |
| `municipio_id` | OK | — |
| `parroquia_id` | OK | — |
| `ciudad_id` | OK | — |
| `codigo_postal_id` | **CORREGIR** | Nombre: frontend dice `zona_postal` (Sección 6) |
| `telefono_fijo` | OK | — |
| `telefono_celular` | OK | — |
| `fax` | OK | — |
| `punto_referencia` | OK | — |

### Tabla: `sim_caso_participantes` (herederos)

| Columna BD | Estado | Detalle |
|---|---|---|
| `caso_estudio_id` | OK | FK al caso |
| `persona_id` | OK | FK generada al insertar heredero en `sim_personas` |
| `rol_en_caso` | OK | Mapeado desde `caracter` (HEREDERO/LEGATARIO → Heredero/Legatario) |
| `parentesco_id` | **CORREGIR** | Actualmente guarda string, debe guardar FK entero (Sección 8). NOT NULL en BD. |
| `es_premuerto` | OK | Mapeado desde `premuerto` (SI/NO → 1/0) |
| `premuerto_padre_id` | **FALTA** | No capturado (Sección 8) |

### Tabla: `sim_caso_bienes_inmuebles`

| Columna BD | Estado | Detalle |
|---|---|---|
| `caso_estudio_id` | OK | FK al caso |
| `tipo_bien_inmueble_id` | **CORREGIR** | Actualmente guarda string, debe guardar FK entero (Sección 9) |
| `es_vivienda_principal` | OK | — |
| `es_bien_litigioso` | OK | — |
| `porcentaje` | OK | — |
| `descripcion` | OK | — |
| `linderos` | **FALTA** | Sección 9 |
| `superficie_construida` | OK | — |
| `superficie_no_construida` | OK | — |
| `area_superficie` | **CORREGIR** | Frontend dice `area_total`, BD dice `area_superficie` (Sección 9) |
| `direccion` | OK | — |
| `oficina_registro` | **FALTA** | Sección 9 |
| `nro_registro` | **FALTA** | Sección 9 |
| `libro` | **FALTA** | Sección 9 |
| `protocolo` | **FALTA** | Sección 9 |
| `fecha_registro` | **FALTA** | Sección 9 |
| `trimestre` | **FALTA** | Sección 9 |
| `asiento_registral` | **FALTA** | Sección 9 |
| `matricula` | **FALTA** | Sección 9 |
| `folio_real_anio` | **FALTA** | Sección 9 |
| `valor_original` | OK | — |
| `valor_declarado` | OK | — |
| `deleted_at` | OK | Backend (soft delete) |

### Tabla: `sim_caso_bienes_litigiosos`

| Columna BD | Estado | Detalle |
|---|---|---|
| `caso_estudio_id` | OK | FK al caso |
| `bien_tipo` | **FALTA** | Backend genera: 'Inmueble' o 'Mueble' (Secciones 9 y 10) |
| `bien_id` | **FALTA** | Backend genera: ID del bien insertado (Secciones 9 y 10) |
| `numero_expediente` | **FALTA** | No capturado (Sección 9, Paso 4) |
| `tribunal_causa` | **FALTA** | No capturado (Sección 9, Paso 4) |
| `partes_juicio` | **FALTA** | No capturado (Sección 9, Paso 4) |
| `estado_juicio` | **FALTA** | No capturado (Sección 9, Paso 4) |

### Tabla: `sim_caso_bienes_muebles` (base, aplica a todas las categorías)

| Columna BD | Estado | Detalle |
|---|---|---|
| `caso_estudio_id` | OK | FK al caso |
| `categoria_bien_mueble_id` | **FALTA** | Debe mapearse desde el subtab activo a ID numérico (Sección 10) |
| `tipo_bien_mueble_id` | **FALTA** | No capturado, debe ser select dinámico por categoría (Sección 10) |
| `es_bien_litigioso` | OK | (pero falta detalle litigioso, ver `sim_caso_bienes_litigiosos`) |
| `porcentaje` | OK | — |
| `descripcion` | OK | — |
| `valor_declarado` | OK | — |
| `deleted_at` | OK | Backend (soft delete) |

### Tablas de detalle mueble (10 tablas)

| Tabla | Estado | Campos que faltan |
|---|---|---|
| `sim_caso_bm_banco` | **FALTA COMPLETA** | `banco_id`, `numero_cuenta` |
| `sim_caso_bm_seguro` | **FALTA COMPLETA** | `empresa_id`, `numero_prima` |
| `sim_caso_bm_transporte` | **FALTA COMPLETA** | `anio`, `marca`, `modelo`, `serial_placa` |
| `sim_caso_bm_acciones` | **FALTA COMPLETA** | `empresa_id` |
| `sim_caso_bm_bonos` | **FALTA COMPLETA** | `tipo_bonos`, `numero_bonos`, `numero_serie` |
| `sim_caso_bm_caja_ahorro` | **FALTA COMPLETA** | `empresa_id` |
| `sim_caso_bm_cuentas_cobrar` | **FALTA COMPLETA** | `rif_cedula`, `apellidos_nombres` |
| `sim_caso_bm_opciones_compra` | **FALTA COMPLETA** | `nombre_oferente` |
| `sim_caso_bm_prestaciones` | **FALTA COMPLETA** | `posee_banco`, `banco_id`, `numero_cuenta`, `empresa_id` |
| `sim_caso_bm_semovientes` | **FALTA COMPLETA** | `tipo_semoviente_id`, `cantidad` |

**Nota:** Cada tabla de detalle tiene un campo `bien_mueble_id` (FK a `sim_caso_bienes_muebles`) que el backend genera al insertar el bien mueble base. No es un campo que el frontend capture.

### Tabla: `sim_caso_pasivos_deuda`

| Columna BD | Estado | Detalle |
|---|---|---|
| `caso_estudio_id` | OK | FK al caso |
| `tipo_pasivo_deuda_id` | **CORREGIR** | Actualmente guarda string `subtipo`, debe guardar FK entero (Sección 11) |
| `banco_id` | **FALTA** | Condicional, no capturado (Sección 11) |
| `numero_tdc` | **FALTA** | Condicional, solo para TDC (Sección 11) |
| `porcentaje` | OK | — |
| `descripcion` | OK | — |
| `valor_declarado` | OK | — |
| `deleted_at` | OK | Backend (soft delete) |

### Tabla: `sim_caso_pasivos_gastos`

| Columna BD | Estado | Detalle |
|---|---|---|
| `caso_estudio_id` | OK | FK al caso |
| `tipo_pasivo_gasto_id` | **CORREGIR** | Actualmente guarda string `tipo_gasto`, debe guardar FK entero (Sección 12) |
| `porcentaje` | OK | — |
| `descripcion` | OK | — |
| `valor_declarado` | OK | — |
| `deleted_at` | OK | Backend (soft delete) |

### Tablas: `sim_caso_exenciones` y `sim_caso_exoneraciones`

| Columna BD | Estado | Detalle |
|---|---|---|
| `caso_estudio_id` | OK | FK al caso |
| `tipo` (varchar 255) | OK | Texto libre, coincide con el formulario |
| `descripcion` | OK | — |
| `valor_declarado` | OK | — |
| `deleted_at` | OK | Backend (soft delete) |

### Tabla: `sim_caso_prorrogas`

| Columna BD | Estado | Detalle |
|---|---|---|
| `caso_estudio_id` | OK | FK al caso |
| `fecha_solicitud` | **FALTA** | NOT NULL — no capturado (Sección 13) |
| `nro_resolucion` | **FALTA** | No capturado (Sección 13) |
| `fecha_resolucion` | **FALTA** | No capturado (Sección 13) |
| `plazo_otorgado_dias` | **FALTA** | No capturado (Sección 13) |
| `fecha_vencimiento` | **FALTA** | No capturado (Sección 13) |

### Tabla: `sim_caso_asignaciones`

| Columna BD | Estado | Detalle |
|---|---|---|
| `config_id` | **NOTA** | La asignación es por config, no por caso directamente. El backend debe usar el `config_id` generado en Sección 1 |
| `estudiante_id` | OK | Del array `caseData.estudiantes_asignados` |
| `estado` | OK | Se establece como "Pendiente" al crear |
| `fecha_completado` | OK | NULL al crear, se actualiza después |

---

## Resumen de endpoints API necesarios

| Endpoint | Tabla fuente | Usado en secciones |
|---|---|---|
| `GET /api/unidades-tributarias` | `sim_cat_unidades_tributarias` | 2 |
| `GET /api/tipos-herencia` | `sim_cat_tipoherencias` | 3 |
| `GET /api/paises` | `paises` | 5, 7, 8 |
| `GET /api/parentescos` | `sim_cat_parentescos` | 8 |
| `GET /api/tipos-bien-inmueble` | `sim_cat_tipos_bien_inmueble` | 9 |
| `GET /api/categorias-bien-mueble` | `sim_cat_categorias_bien_mueble` | 10 |
| `GET /api/tipos-bien-mueble?categoria_id=X` | `sim_cat_tipos_bien_mueble` | 10 |
| `GET /api/bancos` | `sim_cat_bancos` | 10 (banco, prestaciones), 11 |
| `GET /api/empresas` | `sim_empresas` | 10 (seguro, acciones, caja_ahorro, prestaciones) |
| `GET /api/tipos-semoviente` | `sim_cat_tipos_semoviente` | 10 (semovientes) |
| `GET /api/tipos-pasivo-deuda` | `sim_cat_tipos_pasivo_deuda` | 11 |
| `GET /api/tipos-pasivo-gasto` | `sim_cat_tipos_pasivo_gasto` | 12 |

**Nota:** Los endpoints de estados, municipios, parroquias, ciudades y zonas postales ya existen para el domicilio fiscal. Se reutilizan para la parroquia del acta de defunción (Sección 4).

---

## Orden de implementación sugerido

### Fase 1 — Correcciones estructurales (no requieren nuevos endpoints)
1. Sección 1: Separar caso de config en state + PHP
2. Sección 6: Renombrar campos y corregir valores ENUM de domicilio
3. Sección 7: Agregar campos NOT NULL del representante
4. Sección 9: Renombrar `area_total` → `area_superficie`

### Fase 2 — Catálogos dinámicos (requieren endpoints simples)
5. Sección 2: Unidad tributaria como select
6. Sección 5: Nacionalidad dinámica
7. Sección 8: Herederos — sexo, estado_civil, parentesco como FK
8. Sección 12: Gastos — tipo como FK
9. Sección 11: Deudas — tipo como FK + campos condicionales

### Fase 3 — Campos faltantes complejos
10. Sección 3: Tipo herencia — campos condicionales
11. Sección 4: Datos fiscales + acta de defunción
12. Sección 9: Inmuebles — datos registrales + detalle litigioso
13. Sección 13: Prórrogas del caso

### Fase 4 — Modales diferenciados de muebles (el trabajo más pesado)
14. Sección 10: Migrar categorías a catálogo dinámico + crear modales específicos para las 10 categorías con tabla de detalle
