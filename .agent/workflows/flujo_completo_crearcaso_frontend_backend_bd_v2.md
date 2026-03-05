# Flujo Completo — Frontend → Backend → BD → Respuesta

> **Versión:** 2.0  
> **Fecha:** 2026-03-04  
> **Complemento de:** `flujo_insercion_caso_backend_v2.md` (los 13 pasos de INSERT)  
> **JSON de referencia:** `ejemplo_payload_crear_caso_v3.json`  
> **Cambios v2:** tipo_sucesion condiciona campos de acta/causante en frontend y backend, inmuebles M:N con tipos múltiples, UT resuelta dinámicamente

---

## 1. Arquitectura General

```
┌─────────────────────────────────────────────────────────────────┐
│                        FRONTEND (Navegador)                     │
│                                                                 │
│  crear_caso.php + JS (state.js, modal.js, navigation.js, etc.) │
│                                                                 │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐       │
│  │  Paso 1  │→ │  Paso 2  │→ │  Paso 3  │→ │  Paso N  │       │
│  │ Caso &   │  │ Causante │  │Herederos │  │  Bienes  │       │
│  │  Config  │  │ & Fiscal │  │          │  │ Pasivos  │       │
│  └──────────┘  └──────────┘  └──────────┘  └──────────┘       │
│         │                                        │              │
│         ▼                                        ▼              │
│   caseData (state.js) ◄──── estado global ────► collect()      │
│         │                                        │              │
│         └──────────── JSON.stringify() ──────────┘              │
│                           │                                     │
└───────────────────────────┼─────────────────────────────────────┘
                            │
                   POST /api/casos/...
                            │
                            ▼
┌─────────────────────────────────────────────────────────────────┐
│                     BACKEND (PHP / Apache)                       │
│                                                                 │
│  ┌─────────────┐    ┌──────────────┐    ┌────────────────┐     │
│  │  Router     │ →  │  Controller  │ →  │  Service Layer │     │
│  │  /api/casos │    │  CasoCtrl    │    │  CasoService   │     │
│  └─────────────┘    └──────────────┘    └────────────────┘     │
│                                                │                │
│                          ┌─────────────────────┤                │
│                          │                     │                │
│                          ▼                     ▼                │
│                   ┌────────────┐      ┌──────────────┐         │
│                   │  Validador │      │  EmpresaSvc  │         │
│                   │  de Payload│      │  resolver()  │         │
│                   └────────────┘      └──────────────┘         │
│                                                                 │
└─────────────────────────────────────┼───────────────────────────┘
                                      │
                              Transacción SQL
                                      │
                                      ▼
┌─────────────────────────────────────────────────────────────────┐
│                      BASE DE DATOS (MariaDB)                    │
│                                                                 │
│  sim_personas ← sim_casos_estudios → sim_caso_configs           │
│       │                 │                    │                   │
│       ├── sim_actas_defunciones       sim_caso_asignaciones     │
│       ├── sim_causante_datos_fiscales                           │
│       └── sim_persona_direcciones                               │
│                         │                                       │
│       sim_caso_participantes ──► sim_caso_tipoherencia_rel      │
│                         │                                       │
│       sim_caso_bienes_inmuebles ──► sim_caso_bien_inmueble_tipo_rel
│                │                                                │
│                └──────────────► sim_caso_bienes_litigiosos      │
│       sim_caso_bienes_muebles  ──► sim_caso_bm_*               │
│                         │                                       │
│       sim_caso_pasivos_deuda    sim_caso_exenciones             │
│       sim_caso_pasivos_gastos   sim_caso_exoneraciones          │
│       sim_caso_prorrogas        sim_empresas                    │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

## 2. Endpoints API

| Método | Endpoint | Acción | Respuesta |
|--------|----------|--------|-----------|
| POST | `/api/casos/borrador` | Crear o actualizar borrador | `{ caso_id, estado: "Borrador" }` |
| POST | `/api/casos/{id}/publicar` | Publicar caso (13 pasos) | `{ caso_id, estado: "Publicado" }` |
| GET | `/api/casos/{id}/borrador` | Cargar borrador para edición | `{ borrador_json }` |
| GET | `/api/empresas/buscar?rif=J-00006860-9` | Buscar empresa por RIF (autocompletar) | `{ id, rif, razon_social }` o `null` |
| GET | `/api/catalogos/tipos-herencia` | Catálogo de tipos de herencia | `[{ id, nombre }, ...]` |
| GET | `/api/catalogos/parentescos` | Catálogo de parentescos | `[{ id, nombre }, ...]` |
| GET | `/api/catalogos/tipos-bien-inmueble` | Catálogo de 21 tipos (multi-select, M:N) | `[{ id, nombre }, ...]` |
| GET | `/api/catalogos/categorias-bien-mueble` | Catálogo de 12 categorías | `[{ id, nombre }, ...]` |
| GET | `/api/catalogos/tipos-bien-mueble?categoria={id}` | Subtipos por categoría | `[{ id, nombre }, ...]` |
| GET | `/api/catalogos/bancos` | Catálogo de 31 bancos | `[{ id, nombre }, ...]` |
| GET | `/api/catalogos/tipos-semoviente` | Catálogo de 11 tipos | `[{ id, nombre }, ...]` |
| GET | `/api/catalogos/tipos-pasivo-deuda` | Catálogo de 4 tipos | `[{ id, nombre }, ...]` |
| GET | `/api/catalogos/tipos-pasivo-gasto` | Catálogo de 7 tipos | `[{ id, nombre }, ...]` |
| GET | `/api/catalogos/unidades-tributarias` | Catálogo de UT por año | `[{ id, anio, valor }, ...]` |
| GET | `/api/geo/estados` | Estados de Venezuela | `[{ id, nombre }, ...]` |
| GET | `/api/geo/municipios?estado_id={id}` | Municipios por estado | `[{ id, nombre }, ...]` |
| GET | `/api/geo/parroquias?municipio_id={id}` | Parroquias por municipio | `[{ id, nombre }, ...]` |
| GET | `/api/geo/ciudades?estado_id={id}` | Ciudades por estado | `[{ id, nombre }, ...]` |
| GET | `/api/geo/codigos-postales?parroquia_id={id}` | Códigos postales | `[{ id, codigo }, ...]` |
| GET | `/api/estudiantes?seccion={id}` | Estudiantes para asignar | `[{ id, nombre }, ...]` |

---

## 3. Diagramas de Secuencia

### 3.1 Flujo: Guardar Borrador

```
  Profesor              Frontend (JS)           Backend (PHP)           MariaDB
     │                      │                       │                      │
     │  Llena datos en      │                       │                      │
     │  el stepper          │                       │                      │
     │  ─────────────────►  │                       │                      │
     │                      │                       │                      │
     │  Clic "Guardar       │                       │                      │
     │  Borrador"           │                       │                      │
     │  ─────────────────►  │                       │                      │
     │                      │  collect() recopila   │                      │
     │                      │  datos de todos los   │                      │
     │                      │  pasos del stepper    │                      │
     │                      │  ──────────────────►  │                      │
     │                      │  JSON.stringify()     │                      │
     │                      │                       │                      │
     │                      │  POST /api/casos/     │                      │
     │                      │  borrador             │                      │
     │                      │  Body: { json }       │                      │
     │                      │  ─────────────────────►                      │
     │                      │                       │                      │
     │                      │                       │  Validar sesión      │
     │                      │                       │  profesor_id         │
     │                      │                       │                      │
     │                      │                       │  ¿Tiene caso_id?     │
     │                      │                       │                      │
     │                      │                       │  [SI: UPDATE]        │
     │                      │                       │  UPDATE sim_casos_   │
     │                      │                       │  estudios SET        │
     │                      │                       │  borrador_json=:json │
     │                      │                       │  ─────────────────────►
     │                      │                       │                      │
     │                      │                       │  [NO: INSERT]        │
     │                      │                       │  INSERT sim_casos_   │
     │                      │                       │  estudios            │
     │                      │                       │  ─────────────────────►
     │                      │                       │                      │
     │                      │                       │  ◄─ $casoId ─────────┤
     │                      │                       │                      │
     │                      │  ◄─── 200 OK ─────────┤                      │
     │                      │  { caso_id, estado:   │                      │
     │                      │    "Borrador" }       │                      │
     │                      │                       │                      │
     │                      │  Guardar caso_id en   │                      │
     │                      │  caseData.caso.id     │                      │
     │                      │  para futuros saves   │                      │
     │                      │                       │                      │
     │  ◄── Toast: "Borrador│                       │                      │
     │  guardado"           │                       │                      │
     │                      │                       │                      │
```

### 3.2 Flujo: Cargar Borrador Existente

```
  Profesor              Frontend (JS)           Backend (PHP)           MariaDB
     │                      │                       │                      │
     │  Abre caso desde     │                       │                      │
     │  dashboard "Mis      │                       │                      │
     │  Casos → Borrador"   │                       │                      │
     │  ─────────────────►  │                       │                      │
     │                      │  GET /api/casos/      │                      │
     │                      │  {id}/borrador        │                      │
     │                      │  ─────────────────────►                      │
     │                      │                       │                      │
     │                      │                       │  SELECT borrador_json│
     │                      │                       │  FROM sim_casos_     │
     │                      │                       │  estudios            │
     │                      │                       │  WHERE id=:id       │
     │                      │                       │  AND profesor_id=    │
     │                      │                       │  :sesion             │
     │                      │                       │  AND estado=         │
     │                      │                       │  'Borrador'          │
     │                      │                       │  ─────────────────────►
     │                      │                       │                      │
     │                      │                       │  ◄─ borrador_json ───┤
     │                      │                       │                      │
     │                      │  ◄─── 200 OK ─────────┤                      │
     │                      │  { borrador_json }    │                      │
     │                      │                       │                      │
     │                      │  JSON.parse()         │                      │
     │                      │  Cargar en caseData   │                      │
     │                      │  (state.js)           │                      │
     │                      │                       │                      │
     │                      │  Renderizar stepper   │                      │
     │                      │  con datos cargados   │                      │
     │                      │                       │                      │
     │  ◄── Stepper con     │                       │                      │
     │  datos prellenados   │                       │                      │
     │                      │                       │                      │
```

### 3.3 Flujo: Publicar Caso (completo)

```
  Profesor              Frontend (JS)           Backend (PHP)           MariaDB
     │                      │                       │                      │
     │  Revisa resumen      │                       │                      │
     │  en último paso      │                       │                      │
     │  del stepper         │                       │                      │
     │                      │                       │                      │
     │  Clic "Publicar"     │                       │                      │
     │  ─────────────────►  │                       │                      │
     │                      │                       │                      │
     │                      │  Modal confirmación:  │                      │
     │                      │  "¿Publicar caso?     │                      │
     │                      │  Los estudiantes      │                      │
     │                      │  podrán verlo"        │                      │
     │                      │                       │                      │
     │  Confirma "Sí,       │                       │                      │
     │  Publicar"           │                       │                      │
     │  ─────────────────►  │                       │                      │
     │                      │                       │                      │
     │                      │  1. collect() final   │                      │
     │                      │  2. Validación client │                      │
     │                      │     (campos required) │                      │
     │                      │  3. JSON.stringify()  │                      │
     │                      │                       │                      │
     │                      │  POST /api/casos/     │                      │
     │                      │  {id}/publicar        │                      │
     │                      │  Body: { json }       │                      │
     │                      │  ─────────────────────►                      │
     │                      │                       │                      │
     │                      │                       │ ┌─────────────────┐  │
     │                      │                       │ │ VALIDACIÓN      │  │
     │                      │                       │ │ SERVIDOR        │  │
     │                      │                       │ │                 │  │
     │                      │                       │ │ 1. Sesión activa│  │
     │                      │                       │ │ 2. Caso existe  │  │
     │                      │                       │ │ 3. Estado=      │  │
     │                      │                       │ │    Borrador     │  │
     │                      │                       │ │ 4. Profesor es  │  │
     │                      │                       │ │    dueño        │  │
     │                      │                       │ │ 5. Campos NOT   │  │
     │                      │                       │ │    NULL present │  │
     │                      │                       │ │ 6. FKs válidas  │  │
     │                      │                       │ │ 7. ENUMs válidos│  │
     │                      │                       │ └────────┬────────┘  │
     │                      │                       │          │           │
     │                      │                       │  [FALLA VALIDACIÓN]  │
     │                      │  ◄── 422 Unprocessable┤          │           │
     │                      │  { errors: [...] }    │          │           │
     │  ◄── Mostrar errores │                       │          │           │
     │                      │                       │          │           │
     │                      │                       │  [PASA VALIDACIÓN]   │
     │                      │                       │          │           │
     │                      │                       │          ▼           │
     │                      │                       │  BEGIN TRANSACTION   │
     │                      │                       │  ─────────────────────►
     │                      │                       │                      │
     │                      │                       │  ┌───────────────┐   │
     │                      │                       │  │ PASO 1        │   │
     │                      │                       │  │ Personas      │   │
     │                      │                       │  │ (causante,    │   │
     │                      │                       │  │ representante,│   │
     │                      │                       │  │ 4 herederos)  │   │
     │                      │                       │  └───────┬───────┘   │
     │                      │                       │          │           │
     │                      │                       │  6x INSERT sim_     │
     │                      │                       │  personas            │
     │                      │                       │  ─────────────────────►
     │                      │                       │  ◄─ $causanteId      │
     │                      │                       │  ◄─ $representanteId │
     │                      │                       │  ◄─ $herederoIds[]   │
     │                      │                       │                      │
     │                      │                       │  ┌───────────────┐   │
     │                      │                       │  │ PASO 2        │   │
     │                      │                       │  │ Resolver UT + │   │
     │                      │                       │  │ UPDATE caso   │   │
     │                      │                       │  │ (FKs + estado │   │
     │                      │                       │  │ + tipo_suces.)│   │
     │                      │                       │  └───────┬───────┘   │
     │                      │                       │          │           │
     │                      │                       │  SELECT id FROM     │
     │                      │                       │  sim_cat_unidades_  │
     │                      │                       │  tributarias WHERE  │
     │                      │                       │  anio=YEAR(fecha_   │
     │                      │                       │  fallecimiento)     │
     │                      │                       │  ─────────────────────►
     │                      │                       │  ◄─ $utId            │
     │                      │                       │                      │
     │                      │                       │  UPDATE sim_casos_   │
     │                      │                       │  estudios SET        │
     │                      │                       │  causante_id,        │
     │                      │                       │  representante_id,   │
     │                      │                       │  unidad_tributaria_  │
     │                      │                       │  id, tipo_sucesion,  │
     │                      │                       │  estado='Publicado', │
     │                      │                       │  borrador_json=NULL  │
     │                      │                       │  ─────────────────────►
     │                      │                       │                      │
     │                      │                       │  ┌───────────────┐   │
     │                      │                       │  │ PASO 3        │   │
     │                      │                       │  │ Config +      │   │
     │                      │                       │  └───────┬───────┘   │
     │                      │                       │          │           │
     │                      │                       │  INSERT sim_caso_    │
     │                      │                       │  configs             │
     │                      │                       │  ─────────────────────►
     │                      │                       │  ◄─ $configId        │
     │                      │                       │                      │
     │                      │                       │  ┌───────────────┐   │
     │                      │                       │  │ PASOS 4-12    │   │
     │                      │                       │  │ Satélite,     │   │
     │                      │                       │  │ herederos,    │   │
     │                      │                       │  │ bienes,       │   │
     │                      │                       │  │ pasivos,      │   │
     │                      │                       │  │ exenciones,   │   │
     │                      │                       │  │ prórrogas     │   │
     │                      │                       │  └───────┬───────┘   │
     │                      │                       │          │           │
     │                      │                       │  ~55 INSERTs         │
     │                      │                       │  distribuidos en     │
     │                      │                       │  tablas según JSON   │
     │                      │                       │  (incluye pivote     │
     │                      │                       │  inmueble_tipo_rel)  │
     │                      │                       │  ─────────────────────►
     │                      │                       │                      │
     │                      │                       │  ┌───────────────┐   │
     │                      │                       │  │ PASO 8 DETALLE│   │
     │                      │                       │  │ resolverEmpr()│   │
     │                      │                       │  └───────┬───────┘   │
     │                      │                       │          │           │
     │                      │                       │  SELECT sim_empresas │
     │                      │                       │  WHERE rif=:rif      │
     │                      │                       │  ─────────────────────►
     │                      │                       │  ◄─ encontrada / null│
     │                      │                       │                      │
     │                      │                       │  [SI null: INSERT    │
     │                      │                       │   sim_empresas]      │
     │                      │                       │  ─────────────────────►
     │                      │                       │  ◄─ $empresaId       │
     │                      │                       │                      │
     │                      │                       │  ┌───────────────┐   │
     │                      │                       │  │ PASO 13       │   │
     │                      │                       │  │ Asignaciones  │   │
     │                      │                       │  └───────┬───────┘   │
     │                      │                       │          │           │
     │                      │                       │  5x INSERT sim_caso_ │
     │                      │                       │  asignaciones        │
     │                      │                       │  ─────────────────────►
     │                      │                       │                      │
     │                      │                       │  COMMIT              │
     │                      │                       │  ─────────────────────►
     │                      │                       │                      │
     │                      │  ◄─── 200 OK ─────────┤                      │
     │                      │  { caso_id,           │                      │
     │                      │    estado: "Publicado",│                      │
     │                      │    mensaje: "Caso     │                      │
     │                      │    publicado con éxito"│                      │
     │                      │  }                    │                      │
     │                      │                       │                      │
     │  ◄── Redirigir a     │                       │                      │
     │  dashboard "Mis      │                       │                      │
     │  Casos" con toast    │                       │                      │
     │  de éxito            │                       │                      │
     │                      │                       │                      │
```

### 3.4 Flujo: Error durante Publicación

```
  Profesor              Frontend (JS)           Backend (PHP)           MariaDB
     │                      │                       │                      │
     │                      │  POST /api/casos/     │                      │
     │                      │  {id}/publicar        │                      │
     │                      │  ─────────────────────►                      │
     │                      │                       │                      │
     │                      │                       │  BEGIN TRANSACTION   │
     │                      │                       │  ─────────────────────►
     │                      │                       │                      │
     │                      │                       │  Pasos 1-7 OK...    │
     │                      │                       │  ─────────────────────►
     │                      │                       │                      │
     │                      │                       │  Paso 8: INSERT      │
     │                      │                       │  sim_caso_bm_banco   │
     │                      │                       │  banco_id=999        │
     │                      │                       │  (no existe)         │
     │                      │                       │  ─────────────────────►
     │                      │                       │                      │
     │                      │                       │  ◄── FK CONSTRAINT   │
     │                      │                       │      VIOLATION       │
     │                      │                       │                      │
     │                      │                       │  CATCH Exception     │
     │                      │                       │                      │
     │                      │                       │  ROLLBACK            │
     │                      │                       │  ─────────────────────►
     │                      │                       │                      │
     │                      │                       │  (Todo deshecho:     │
     │                      │                       │   personas, caso,    │
     │                      │                       │   config, herederos, │
     │                      │                       │   inmuebles — todo   │
     │                      │                       │   revertido)         │
     │                      │                       │                      │
     │                      │  ◄─── 500 Error ──────┤                      │
     │                      │  { error: "Error al   │                      │
     │                      │    publicar. El       │                      │
     │                      │    borrador se        │                      │
     │                      │    conserva." }       │                      │
     │                      │                       │                      │
     │  ◄── Modal error:    │                       │                      │
     │  "No se pudo         │                       │                      │
     │  publicar. Tu        │                       │                      │
     │  borrador está       │                       │                      │
     │  guardado."          │                       │                      │
     │                      │                       │                      │
```

---

## 4. Flujo del Autocompletar Empresa

```
  Profesor              Frontend (JS)           Backend (PHP)           MariaDB
     │                      │                       │                      │
     │  En modal de bien    │                       │                      │
     │  mueble (Acciones,   │                       │                      │
     │  Prestaciones, etc.) │                       │                      │
     │                      │                       │                      │
     │  Escribe RIF:        │                       │                      │
     │  "J-0000686"         │                       │                      │
     │  ─────────────────►  │                       │                      │
     │                      │                       │                      │
     │                      │  debounce(300ms)      │                      │
     │                      │                       │                      │
     │  Termina de escribir:│                       │                      │
     │  "J-00006860-9"      │                       │                      │
     │  ─────────────────►  │                       │                      │
     │                      │                       │                      │
     │                      │  Validar formato RIF  │                      │
     │                      │  (regex: ^[JGVEP]-    │                      │
     │                      │  \d{8}-\d$)           │                      │
     │                      │                       │                      │
     │                      │  [FORMATO VÁLIDO]     │                      │
     │                      │                       │                      │
     │                      │  GET /api/empresas/   │                      │
     │                      │  buscar?rif=          │                      │
     │                      │  J-00006860-9         │                      │
     │                      │  ─────────────────────►                      │
     │                      │                       │                      │
     │                      │                       │  SELECT id,rif,      │
     │                      │                       │  razon_social        │
     │                      │                       │  FROM sim_empresas   │
     │                      │                       │  WHERE rif=:rif      │
     │                      │                       │  AND activo=1        │
     │                      │                       │  ─────────────────────►
     │                      │                       │                      │
     │                      │                       │                      │
     │  ┌─── CASO A: EMPRESA ENCONTRADA ───────────────────────────────┐  │
     │  │                   │                       │                  │  │
     │  │                   │                       │  ◄── { id: 12,   │  │
     │  │                   │                       │    rif, razon_   │  │
     │  │                   │                       │    social }      │  │
     │  │                   │                       │                  │  │
     │  │                   │  ◄── 200 OK ──────────┤                  │  │
     │  │                   │  { encontrada: true,  │                  │  │
     │  │                   │    razon_social:       │                  │  │
     │  │                   │    "Empresas Polar"}  │                  │  │
     │  │                   │                       │                  │  │
     │  │                   │  Autocompletar campo  │                  │  │
     │  │                   │  Razón Social con     │                  │  │
     │  │                   │  valor retornado.     │                  │  │
     │  │                   │  Campo deshabilitado  │                  │  │
     │  │                   │  (readonly) + ícono ✓ │                  │  │
     │  │                   │                       │                  │  │
     │  │  ◄── Campo Razón  │                       │                  │  │
     │  │  Social prellenado│                       │                  │  │
     │  │  y en readonly    │                       │                  │  │
     │  └──────────────────────────────────────────────────────────────┘  │
     │                      │                       │                      │
     │  ┌─── CASO B: EMPRESA NO ENCONTRADA ────────────────────────────┐  │
     │  │                   │                       │                  │  │
     │  │                   │                       │  ◄── NULL        │  │
     │  │                   │                       │                  │  │
     │  │                   │  ◄── 200 OK ──────────┤                  │  │
     │  │                   │  { encontrada: false } │                  │  │
     │  │                   │                       │                  │  │
     │  │                   │  Campo Razón Social   │                  │  │
     │  │                   │  habilitado + vacío   │                  │  │
     │  │                   │  + placeholder "Nueva │                  │  │
     │  │                   │  empresa — escriba    │                  │  │
     │  │                   │  razón social"        │                  │  │
     │  │                   │                       │                  │  │
     │  │  ◄── Profesor     │                       │                  │  │
     │  │  escribe razón    │                       │                  │  │
     │  │  social manualmente│                      │                  │  │
     │  │                   │                       │                  │  │
     │  │  (La empresa se   │                       │                  │  │
     │  │  crea en BD al    │                       │                  │  │
     │  │  publicar, Paso 8 │                       │                  │  │
     │  │  resolverEmpresa) │                       │                  │  │
     │  └──────────────────────────────────────────────────────────────┘  │
     │                      │                       │                      │
```

---

## 5. Flujo de Selects Geográficos en Cascada (Domicilio)

```
  Profesor              Frontend (JS)           Backend (PHP)           MariaDB
     │                      │                       │                      │
     │  Selecciona Estado:  │                       │                      │
     │  "Distrito Capital"  │                       │                      │
     │  ─────────────────►  │                       │                      │
     │                      │  onChange(estado_id=7) │                      │
     │                      │                       │                      │
     │                      │  GET /api/geo/        │                      │
     │                      │  municipios?           │                      │
     │                      │  estado_id=7           │                      │
     │                      │  ─────────────────────►                      │
     │                      │                       │  SELECT...           │
     │                      │                       │  ─────────────────────►
     │                      │                       │  ◄── municipios[]    │
     │                      │  ◄── 200 OK ──────────┤                      │
     │                      │                       │                      │
     │                      │  Poblar select        │                      │
     │                      │  Municipio.           │                      │
     │                      │  Limpiar Parroquia,   │                      │
     │                      │  Ciudad, Cód.Postal   │                      │
     │                      │                       │                      │
     │  Selecciona          │                       │                      │
     │  Municipio:          │                       │                      │
     │  "Libertador"        │                       │                      │
     │  ─────────────────►  │                       │                      │
     │                      │  onChange(municipio_   │                      │
     │                      │  id=45)               │                      │
     │                      │                       │                      │
     │                      │  GET /api/geo/        │                      │
     │                      │  parroquias?           │                      │
     │                      │  municipio_id=45       │                      │
     │                      │  ─────────────────────►                      │
     │                      │                       │  ◄── parroquias[]    │
     │                      │  ◄── 200 OK ──────────┤                      │
     │                      │                       │                      │
     │                      │  Poblar select        │                      │
     │                      │  Parroquia.           │                      │
     │                      │  Limpiar Cód.Postal   │                      │
     │                      │                       │                      │
     │  (Repite para Ciudad │                       │                      │
     │  y Código Postal)    │                       │                      │
     │                      │                       │                      │
```

---

## 6. Ciclo de Vida Completo del Caso

```
  ┌──────────────────────────────────────────────────────────────────┐
  │                                                                  │
  │    ┌──────────┐     ┌──────────┐     ┌──────────┐               │
  │    │ NUEVO    │     │BORRADOR  │     │PUBLICADO │               │
  │    │          │────►│          │────►│          │               │
  │    │ (no existe│    │ Solo JSON │    │ Datos en  │               │
  │    │  en BD)  │     │ en una   │     │ 29 tablas │               │
  │    └──────────┘     │ columna  │     │ relacion. │               │
  │         │           └──────────┘     └──────────┘               │
  │         │                │                │                      │
  │         │           Guardar /         Publicar                   │
  │         │           Editar              (13 pasos)               │
  │         │           (1 UPDATE)          (~61 INSERTs)            │
  │         │                │                │                      │
  │         │                │                ▼                      │
  │         │                │          ┌──────────┐                │
  │         │                │          │ INACTIVO │                │
  │         │                │          │          │                │
  │         │                │          │ No visible│                │
  │         │                │          │ a estud. │                │
  │         │                │          └──────────┘                │
  │         │                │                │                      │
  │         │                │                ▼                      │
  │         │                │          ┌──────────┐                │
  │         │                │          │ELIMINADO │                │
  │         │                │          │          │                │
  │         │                │          │ Soft del.│                │
  │         │                │          │ en BD    │                │
  │         │                │          └──────────┘                │
  │         │                │                                      │
  │         │                ▼                                      │
  │         │          ┌──────────┐                                 │
  │         └─────────►│ELIMINADO │  (borrador descartado)          │
  │                    └──────────┘                                 │
  │                                                                  │
  └──────────────────────────────────────────────────────────────────┘

  Transiciones válidas:
  ─────────────────────
  Nuevo      → Borrador    (primer guardado)
  Borrador   → Borrador    (re-guardado, N veces)
  Borrador   → Publicado   (publicar — 13 pasos transaccionales)
  Borrador   → Eliminado   (descartar borrador)
  Publicado  → Inactivo    (ocultar a estudiantes)
  Inactivo   → Publicado   (reactivar)
  Inactivo   → Eliminado   (eliminar definitivamente)
  Publicado  → Eliminado   (eliminar directamente)
```

---

## 7. Respuestas HTTP del Backend

### Respuestas exitosas

```json
// POST /api/casos/borrador (nuevo)
{
  "success": true,
  "caso_id": 42,
  "estado": "Borrador",
  "mensaje": "Borrador creado correctamente."
}

// POST /api/casos/borrador (existente)
{
  "success": true,
  "caso_id": 42,
  "estado": "Borrador",
  "mensaje": "Borrador actualizado correctamente."
}

// POST /api/casos/42/publicar
{
  "success": true,
  "caso_id": 42,
  "estado": "Publicado",
  "mensaje": "Caso publicado correctamente. 5 estudiantes asignados.",
  "resumen": {
    "personas_creadas": 6,
    "bienes_inmuebles": 2,
    "inmueble_tipos_asignados": 3,
    "bienes_muebles": 12,
    "pasivos": 4,
    "empresas_creadas": 1,
    "empresas_reutilizadas": 2,
    "estudiantes_asignados": 5
  }
}

// GET /api/casos/42/borrador
{
  "success": true,
  "caso_id": 42,
  "estado": "Borrador",
  "borrador_json": { ... }
}
```

### Respuestas de error

```json
// 401 — No autenticado
{
  "success": false,
  "error": "No autenticado.",
  "codigo": "AUTH_REQUIRED"
}

// 403 — No es dueño del caso
{
  "success": false,
  "error": "No tiene permiso para modificar este caso.",
  "codigo": "FORBIDDEN"
}

// 404 — Caso no encontrado o no es borrador
{
  "success": false,
  "error": "Caso no encontrado o ya fue publicado.",
  "codigo": "NOT_FOUND"
}

// 422 — Validación fallida
{
  "success": false,
  "error": "Datos incompletos para publicar.",
  "codigo": "VALIDATION_FAILED",
  "errores": [
    {
      "campo": "causante.cedula",
      "mensaje": "La cédula del causante es obligatoria cuando tipo_sucesion='Con_Cedula'."
    },
    {
      "campo": "acta_defuncion.numero_acta",
      "mensaje": "El número de acta es obligatorio cuando tipo_sucesion='Sin_Cedula'."
    },
    {
      "campo": "herederos[2].fecha_fallecimiento",
      "mensaje": "El heredero premuerto debe tener fecha de fallecimiento."
    },
    {
      "campo": "bienes_inmuebles[0].tipos_bien_inmueble_ids",
      "mensaje": "Debe seleccionar al menos un tipo de bien inmueble."
    }
  ]
}

// 500 — Error en transacción
{
  "success": false,
  "error": "Error interno al publicar el caso. El borrador se conserva.",
  "codigo": "TRANSACTION_FAILED"
}
```

---

## 8. Validaciones

### 8.1 Validaciones del Frontend (antes de enviar)

Estas validaciones se ejecutan en JS al hacer clic en "Publicar", antes del POST. Son validaciones de UX para evitar enviar datos incompletos:

- Caso: titulo no vacío (min 5 caracteres)
- Caso: tipo_sucesion seleccionado ('Con_Cedula' o 'Sin_Cedula')
- Causante (Con_Cedula): cedula, nombres, apellidos, fecha_nacimiento, sexo, estado_civil obligatorios
- Causante (Sin_Cedula): mismos campos EXCEPTO cedula (que queda vacío/null)
- Domicilio: tipo_direccion, estado_id, municipio_id, parroquia_id obligatorios
- Acta defunción (Con_Cedula): solo fecha_fallecimiento obligatoria
- Acta defunción (Sin_Cedula): fecha_fallecimiento, numero_acta, year_acta, parroquia_registro_id TODOS obligatorios
- Datos fiscales: fecha_cierre_fiscal obligatoria
- Herederos: mínimo 1 heredero con persona completa y parentesco_id
- Herederos premuertos: fecha_fallecimiento obligatoria si es_premuerto=true
- Premuerto_padre_id: debe apuntar a un _ref_index válido donde es_premuerto=true
- Bienes inmuebles: tipos_bien_inmueble_ids debe ser array con al menos 1 elemento
- Bienes muebles con empresa: rif_empresa con formato válido y razon_social no vacío
- Config modalidad Evaluacion: fecha_limite obligatoria y futura
- Config modalidad Evaluacion: estudiantes_asignados con mínimo 1 estudiante

**Lógica condicional del frontend por tipo_sucesion:**
- Cuando tipo_sucesion='Con_Cedula': mostrar campo cédula del causante como obligatorio, ocultar o marcar como opcionales los campos numero_acta, year_acta, parroquia_registro_id del acta de defunción.
- Cuando tipo_sucesion='Sin_Cedula': ocultar o deshabilitar campo cédula del causante, mostrar campos del acta de defunción (numero_acta, year_acta, parroquia_registro_id) como obligatorios con indicador visual (*).
- El cambio de tipo_sucesion debe actualizar la UI en tiempo real (toggle de campos visibles/obligatorios).

### 8.2 Validaciones del Backend (antes de la transacción)

Estas se ejecutan en PHP después de recibir el JSON y antes de BEGIN TRANSACTION. Son validaciones de integridad:

- Sesión válida con profesor_id
- Caso existe, estado='Borrador', profesor_id coincide con sesión
- tipo_sucesion es 'Con_Cedula' o 'Sin_Cedula'
- Todos los campos NOT NULL de cada tabla están presentes (considerando condicionales por tipo_sucesion)
- Si tipo_sucesion='Con_Cedula': causante.cedula NOT NULL
- Si tipo_sucesion='Sin_Cedula': causante.cedula puede ser NULL, pero acta_defuncion.numero_acta, year_acta y parroquia_registro_id deben ser NOT NULL
- FKs existen en sus tablas destino (unidad_tributaria_id, parentesco_id, tipo_herencia_id, categoria_bien_mueble_id, tipo_bien_mueble_id, banco_id, tipo_pasivo_deuda_id, tipo_pasivo_gasto_id, tipo_semoviente_id, parroquia_registro_id, estado_id, municipio_id, parroquia_id)
- Cada ID en tipos_bien_inmueble_ids[] existe en sim_cat_tipos_bien_inmueble
- tipos_bien_inmueble_ids es array con al menos 1 elemento, sin duplicados internos
- Valores ENUM son válidos (tipo_cedula incluyendo 'No_Aplica', sexo, estado_civil, tipo_sucesion, tipo_direccion, tipo_vialidad, tipo_inmueble, tipo_nivel, tipo_sector, modalidad, rol_en_caso, bien_tipo, subtipo_testamento)
- estudiante_id existe en tabla de estudiantes y está activo
- Si es_premuerto=true, fecha_fallecimiento es anterior a acta_defuncion.fecha_fallecimiento del causante
- premuerto_padre_id apunta a un índice válido dentro del array y ese heredero tiene es_premuerto=true
- No hay _ref_index duplicados en herederos
- formato RIF válido en empresas (^[JGVEP]-\d{8}-\d$)
