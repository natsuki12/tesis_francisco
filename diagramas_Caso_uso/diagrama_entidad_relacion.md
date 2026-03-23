# Diagrama Entidad-Relación — Base de Datos SPDSS

```mermaid
erDiagram
    %% ═══════════════════════════════════
    %% MÓDULO: SISTEMA / AUTENTICACIÓN
    %% ═══════════════════════════════════

    personas {
        bigint id PK
        enum nacionalidad "V, E"
        varchar cedula
        varchar nombres
        varchar apellidos
        date fecha_nacimiento
        enum genero
    }

    roles {
        int id PK
        varchar nombre
        varchar descripcion
    }

    users {
        bigint id PK
        bigint persona_id FK
        int role_id FK
        varchar email
        varchar password
        enum status
    }

    user_sessions {
        bigint id PK
        bigint user_id FK
        varchar session_id
        varchar ip_address
    }

    password_resets {
        int id PK
        bigint user_id FK
        varchar token_hash
        timestamp expires_at
    }

    tipos_eventos {
        int id PK
        varchar codigo
        varchar descripcion
        enum nivel_riesgo
    }

    bitacora_accesos {
        bigint id PK
        bigint user_id FK
        int tipo_evento_id FK
        varchar attempted_email
        varchar ip_address
        timestamp created_at
    }

    %% ═══════════════════════════════════
    %% MÓDULO: ACADÉMICO
    %% ═══════════════════════════════════

    carreras {
        int id PK
        varchar nombre
        varchar codigo
    }

    materias {
        int id PK
        varchar nombre
        varchar codigo
        int carrera_id FK
    }

    periodos {
        int id PK
        varchar nombre
        date fecha_inicio
        date fecha_fin
        tinyint activo
    }

    profesores {
        bigint id PK
        bigint persona_id FK
        varchar titulo
    }

    estudiantes {
        bigint id PK
        bigint persona_id FK
        int carrera_id FK
    }

    secciones {
        int id PK
        int materia_id FK
        bigint profesor_id FK
        int periodo_id FK
        varchar nombre
        int cupo_maximo
    }

    inscripciones {
        bigint id PK
        bigint estudiante_id FK
        int seccion_id FK
    }

    %% ═══════════════════════════════════
    %% MÓDULO: GEOGRAFÍA
    %% ═══════════════════════════════════

    estados {
        bigint id PK
        varchar nombre
    }

    municipios {
        bigint id PK
        bigint estado_id FK
        varchar nombre
    }

    ciudades {
        bigint id PK
        bigint municipio_id FK
        varchar nombre
    }

    parroquias {
        bigint id PK
        bigint municipio_id FK
        varchar nombre
    }

    codigos_postales {
        bigint id PK
        bigint estado_id FK
        varchar codigo
    }

    paises {
        smallint id PK
        varchar nombre
    }

    %% ═══════════════════════════════════
    %% MÓDULO: SIMULADOR - PERSONAS
    %% ═══════════════════════════════════

    sim_personas {
        bigint id PK
        enum tipo_cedula "V, E, No_Aplica"
        smallint nacionalidad FK
        varchar cedula
        varchar nombres
        varchar apellidos
        enum sexo
        enum estado_civil
        bigint created_by FK
    }

    sim_actas_defunciones {
        bigint id PK
        bigint sim_persona_id FK
        date fecha_fallecimiento
        varchar numero_acta
    }

    sim_causante_datos_fiscales {
        bigint sim_persona_id PK
        tinyint domiciliado_pais
        date fecha_cierre_fiscal
    }

    sim_empresas {
        bigint id PK
        varchar rif
        varchar razon_social
    }

    %% ═══════════════════════════════════
    %% MÓDULO: SIMULADOR - CASOS DE ESTUDIO
    %% ═══════════════════════════════════

    sim_casos_estudios {
        bigint id PK
        bigint profesor_id FK
        bigint causante_id FK
        bigint representante_id FK
        smallint unidad_tributaria_id FK
        enum tipo_sucesion
        varchar titulo
        enum estado "Borrador, Publicado, Inactivo, Eliminado"
    }

    sim_caso_configs {
        bigint id PK
        bigint caso_id FK
        bigint profesor_id FK
        enum modalidad
        tinyint max_intentos
        timestamp fecha_limite
    }

    sim_caso_asignaciones {
        bigint id PK
        bigint config_id FK
        bigint estudiante_id FK
        enum estado
    }

    sim_caso_participantes {
        bigint id PK
        bigint caso_estudio_id FK
        bigint persona_id FK
        enum rol_en_caso "Heredero, Legatario"
        int parentesco_id FK
        tinyint es_premuerto
        bigint premuerto_padre_id FK
    }

    sim_caso_calculo_manual {
        bigint id PK
        bigint caso_estudio_id FK
        bigint participante_id FK
        decimal cuota_parte_ut
        decimal reduccion_bs
    }

    sim_caso_direcciones {
        bigint id PK
        bigint sim_caso_estudio_id FK
        enum tipo_direccion
        bigint estado_id FK
        bigint municipio_id FK
        bigint parroquia_id FK
        bigint ciudad_id FK
        bigint codigo_postal_id FK
    }

    sim_caso_tipoherencia_rel {
        bigint id PK
        bigint caso_estudio_id FK
        tinyint tipo_herencia_id FK
        enum subtipo_testamento
    }

    sim_caso_prorrogas {
        bigint id PK
        bigint caso_estudio_id FK
        date fecha_solicitud
        varchar nro_resolucion
    }

    %% ═══════════════════════════════════
    %% MÓDULO: SIMULADOR - BIENES INMUEBLES
    %% ═══════════════════════════════════

    sim_caso_bienes_inmuebles {
        bigint id PK
        bigint caso_estudio_id FK
        tinyint es_vivienda_principal
        decimal porcentaje
        decimal valor_declarado
    }

    sim_caso_bien_inmueble_tipo_rel {
        bigint id PK
        bigint bien_inmueble_id FK
        tinyint tipo_bien_inmueble_id FK
    }

    sim_caso_bienes_litigiosos {
        bigint id PK
        bigint caso_estudio_id FK
        enum bien_tipo "Inmueble, Mueble"
        bigint bien_id
    }

    %% ═══════════════════════════════════
    %% MÓDULO: SIMULADOR - BIENES MUEBLES
    %% ═══════════════════════════════════

    sim_caso_bienes_muebles {
        bigint id PK
        bigint caso_estudio_id FK
        tinyint categoria_bien_mueble_id FK
        smallint tipo_bien_mueble_id FK
        decimal porcentaje
        decimal valor_declarado
    }

    sim_caso_bm_banco {
        bigint id PK
        bigint bien_mueble_id FK
        smallint banco_id FK
        varchar numero_cuenta
    }

    sim_caso_bm_seguro {
        bigint id PK
        bigint bien_mueble_id FK
        bigint empresa_id FK
        varchar numero_prima
    }

    sim_caso_bm_transporte {
        bigint id PK
        bigint bien_mueble_id FK
        varchar marca
        varchar modelo
    }

    sim_caso_bm_acciones {
        bigint id PK
        bigint bien_mueble_id FK
        bigint empresa_id FK
    }

    sim_caso_bm_bonos {
        bigint id PK
        bigint bien_mueble_id FK
        varchar tipo_bonos
    }

    sim_caso_bm_semovientes {
        bigint id PK
        bigint bien_mueble_id FK
        tinyint tipo_semoviente_id FK
    }

    sim_caso_bm_caja_ahorro {
        bigint id PK
        bigint bien_mueble_id FK
        bigint empresa_id FK
    }

    sim_caso_bm_cuentas_cobrar {
        bigint id PK
        bigint bien_mueble_id FK
        varchar rif_cedula
    }

    sim_caso_bm_opciones_compra {
        bigint id PK
        bigint bien_mueble_id FK
        varchar nombre_oferente
    }

    sim_caso_bm_prestaciones {
        bigint id PK
        bigint bien_mueble_id FK
        tinyint posee_banco
        smallint banco_id FK
        bigint empresa_id FK
    }

    %% ═══════════════════════════════════
    %% MÓDULO: SIMULADOR - PASIVOS
    %% ═══════════════════════════════════

    sim_caso_pasivos_deuda {
        bigint id PK
        bigint caso_estudio_id FK
        tinyint tipo_pasivo_deuda_id FK
        smallint banco_id FK
        decimal valor_declarado
    }

    sim_caso_pasivos_gastos {
        bigint id PK
        bigint caso_estudio_id FK
        tinyint tipo_pasivo_gasto_id FK
        decimal valor_declarado
    }

    sim_caso_exenciones {
        bigint id PK
        bigint caso_estudio_id FK
        varchar tipo
        decimal valor_declarado
    }

    sim_caso_exoneraciones {
        bigint id PK
        bigint caso_estudio_id FK
        varchar tipo
        decimal valor_declarado
    }

    %% ═══════════════════════════════════
    %% MÓDULO: CATÁLOGOS DEL SIMULADOR
    %% ═══════════════════════════════════

    sim_cat_bancos {
        smallint id PK
        varchar nombre
    }

    sim_cat_categorias_bien_mueble {
        tinyint id PK
        varchar nombre
    }

    sim_cat_tipos_bien_mueble {
        smallint id PK
        tinyint categoria_bien_mueble_id FK
        varchar nombre
    }

    sim_cat_tipos_bien_inmueble {
        tinyint id PK
        varchar nombre
    }

    sim_cat_tipos_pasivo_deuda {
        tinyint id PK
        varchar nombre
    }

    sim_cat_tipos_pasivo_gasto {
        tinyint id PK
        varchar nombre
    }

    sim_cat_tipos_semoviente {
        tinyint id PK
        varchar nombre
    }

    sim_cat_parentescos {
        int id PK
        varchar clave
        varchar etiqueta
        tinyint grupo_tarifa_id FK
    }

    sim_cat_grupos_tarifa {
        tinyint id PK
        varchar nombre
    }

    sim_cat_tarifas_sucesion {
        smallint id PK
        tinyint grupo_tarifa_id FK
        decimal porcentaje
        decimal sustraendo_ut
    }

    sim_cat_tramos_tarifa {
        int id PK
        tinyint grupo_tarifa_id FK
        decimal porcentaje
        decimal sustraendo_ut
    }

    sim_cat_reducciones {
        int id PK
        tinyint ordinal
        varchar clave
        decimal porcentaje_reduccion
    }

    sim_cat_unidades_tributarias {
        smallint id PK
        smallint anio
        decimal valor
        date fecha_gaceta
    }

    sim_cat_tipoherencias {
        tinyint id PK
        varchar nombre
    }

    sim_marco_legals {
        smallint id PK
        varchar titulo
        enum tipo
        enum estado_doc "Vigente, Derogado"
    }

    %% ═══════════════════════════════════
    %% MÓDULO: INTENTOS DEL ESTUDIANTE
    %% ═══════════════════════════════════

    sim_intentos {
        bigint id PK
        bigint asignacion_id FK
        smallint numero_intento
        enum estado
        tinyint paso_actual
        char numero_control
        varchar rif_sucesoral
    }

    sim_intento_datos_basicos {
        bigint id PK
        bigint intento_id FK
        varchar cedula
        varchar nombres
        date fecha_fallecimiento
        smallint nacionalidad FK
    }

    sim_intento_relaciones {
        bigint id PK
        bigint intento_id FK
        enum rol
        varchar cedula
        varchar nombres
        int parentesco_id FK
    }

    sim_intento_direcciones {
        bigint id PK
        bigint intento_id FK
        enum tipo_direccion
        bigint estado_id FK
        bigint municipio_id FK
        bigint parroquia_id FK
        bigint ciudad_id FK
        bigint codigo_postal_id FK
    }

    sim_intento_tipoherencias {
        bigint id PK
        bigint intento_id FK
        tinyint tipo_herencia_id FK
    }

    sim_intento_bienes_inmuebles {
        bigint id PK
        bigint intento_id FK
        decimal valor_declarado
    }

    sim_intento_bien_inmueble_tipo_rel {
        bigint id PK
        bigint bien_inmueble_id FK
        tinyint tipo_bien_inmueble_id FK
    }

    sim_intento_bienes_muebles {
        bigint id PK
        bigint intento_id FK
        tinyint categoria_bien_mueble_id FK
        smallint tipo_bien_mueble_id FK
        decimal valor_declarado
    }

    sim_intento_bienes_litigiosos {
        bigint id PK
        bigint intento_id FK
        enum bien_tipo
    }

    sim_intento_bm_banco {
        bigint id PK
        bigint bien_mueble_id FK
        smallint banco_id FK
    }

    sim_intento_bm_seguro {
        bigint id PK
        bigint bien_mueble_id FK
        bigint empresa_id FK
    }

    sim_intento_bm_transporte {
        bigint id PK
        bigint bien_mueble_id FK
    }

    sim_intento_bm_acciones {
        bigint id PK
        bigint bien_mueble_id FK
        bigint empresa_id FK
    }

    sim_intento_bm_bonos {
        bigint id PK
        bigint bien_mueble_id FK
    }

    sim_intento_bm_semovientes {
        bigint id PK
        bigint bien_mueble_id FK
        tinyint tipo_semoviente_id FK
    }

    sim_intento_bm_caja_ahorro {
        bigint id PK
        bigint bien_mueble_id FK
        bigint empresa_id FK
    }

    sim_intento_bm_cuentas_cobrar {
        bigint id PK
        bigint bien_mueble_id FK
    }

    sim_intento_bm_opciones_compra {
        bigint id PK
        bigint bien_mueble_id FK
    }

    sim_intento_bm_prestaciones {
        bigint id PK
        bigint bien_mueble_id FK
        smallint banco_id FK
        bigint empresa_id FK
    }

    sim_intento_pasivos_deuda {
        bigint id PK
        bigint intento_id FK
        tinyint tipo_pasivo_deuda_id FK
        smallint banco_id FK
    }

    sim_intento_pasivos_gastos {
        bigint id PK
        bigint intento_id FK
        tinyint tipo_pasivo_gasto_id FK
    }

    sim_intento_exenciones {
        bigint id PK
        bigint intento_id FK
        decimal valor_declarado
    }

    sim_intento_exoneraciones {
        bigint id PK
        bigint intento_id FK
        decimal valor_declarado
    }

    sim_intento_prorrogas {
        bigint id PK
        bigint intento_id FK
        date fecha_solicitud
    }

    sim_intento_calculo_manual {
        bigint id PK
        bigint intento_id FK
        bigint relacion_id FK
        decimal cuota_parte_ut
    }

    sim_intento_estados {
        bigint id PK
        bigint intento_id FK
        enum estado
        varchar comentario
    }

    sim_intento_observaciones {
        bigint id PK
        bigint intento_id FK
        varchar observacion
    }

    %% ═══════════════════════════════════════════════
    %% RELACIONES - SISTEMA / AUTENTICACIÓN
    %% ═══════════════════════════════════════════════

    personas ||--o{ users : "tiene cuenta"
    personas ||--o| profesores : "es profesor"
    personas ||--o| estudiantes : "es estudiante"
    roles ||--o{ users : "define permisos"
    users ||--o{ user_sessions : "inicia sesion"
    users ||--o{ password_resets : "solicita reset"
    users ||--o{ bitacora_accesos : "genera evento"
    tipos_eventos ||--o{ bitacora_accesos : "clasifica"

    %% ═══════════════════════════════════════════════
    %% RELACIONES - ACADÉMICO
    %% ═══════════════════════════════════════════════

    carreras ||--o{ materias : "contiene"
    carreras ||--o{ estudiantes : "cursa"
    materias ||--o{ secciones : "abre seccion"
    profesores ||--o{ secciones : "dicta"
    periodos ||--o{ secciones : "pertenece a"
    estudiantes ||--o{ inscripciones : "se inscribe"
    secciones ||--o{ inscripciones : "agrupa"

    %% ═══════════════════════════════════════════════
    %% RELACIONES - GEOGRAFÍA
    %% ═══════════════════════════════════════════════

    estados ||--o{ municipios : "contiene"
    estados ||--o{ codigos_postales : "asocia"
    municipios ||--o{ ciudades : "contiene"
    municipios ||--o{ parroquias : "contiene"

    %% ═══════════════════════════════════════════════
    %% RELACIONES - SIMULADOR PERSONAS
    %% ═══════════════════════════════════════════════

    sim_personas ||--o| sim_actas_defunciones : "tiene acta"
    sim_personas ||--o| sim_causante_datos_fiscales : "datos fiscales"
    paises ||--o{ sim_personas : "nacionalidad"
    profesores ||--o{ sim_personas : "created_by"

    %% ═══════════════════════════════════════════════
    %% RELACIONES - CASOS DE ESTUDIO
    %% ═══════════════════════════════════════════════

    profesores ||--o{ sim_casos_estudios : "crea caso"
    sim_personas ||--o{ sim_casos_estudios : "es causante"
    sim_personas ||--o{ sim_casos_estudios : "es representante"
    sim_cat_unidades_tributarias ||--o{ sim_casos_estudios : "UT del caso"

    sim_casos_estudios ||--o{ sim_caso_configs : "tiene config"
    profesores ||--o{ sim_caso_configs : "profesor config"
    sim_caso_configs ||--o{ sim_caso_asignaciones : "asigna"
    estudiantes ||--o{ sim_caso_asignaciones : "recibe caso"

    sim_casos_estudios ||--o{ sim_caso_participantes : "tiene herederos"
    sim_personas ||--o{ sim_caso_participantes : "es participante"
    sim_cat_parentescos ||--o{ sim_caso_participantes : "define parentesco"

    sim_casos_estudios ||--o{ sim_caso_calculo_manual : "tiene calculos"
    sim_caso_participantes ||--o{ sim_caso_calculo_manual : "cuota asignada"

    sim_casos_estudios ||--o{ sim_caso_direcciones : "tiene direcciones"
    estados ||--o{ sim_caso_direcciones : "estado"
    municipios ||--o{ sim_caso_direcciones : "municipio"
    parroquias ||--o{ sim_caso_direcciones : "parroquia"
    ciudades ||--o{ sim_caso_direcciones : "ciudad"
    codigos_postales ||--o{ sim_caso_direcciones : "codigo postal"
    sim_casos_estudios ||--o{ sim_caso_tipoherencia_rel : "tipo herencia"
    sim_cat_tipoherencias ||--o{ sim_caso_tipoherencia_rel : "catalogo"
    sim_casos_estudios ||--o{ sim_caso_prorrogas : "tiene prorrogas"

    %% ═══════════════════════════════════════════════
    %% RELACIONES - BIENES INMUEBLES (CASO)
    %% ═══════════════════════════════════════════════

    sim_casos_estudios ||--o{ sim_caso_bienes_inmuebles : "tiene inmuebles"
    sim_caso_bienes_inmuebles ||--o{ sim_caso_bien_inmueble_tipo_rel : "clasifica tipo"
    sim_cat_tipos_bien_inmueble ||--o{ sim_caso_bien_inmueble_tipo_rel : "catalogo"
    sim_casos_estudios ||--o{ sim_caso_bienes_litigiosos : "tiene litigiosos"

    %% ═══════════════════════════════════════════════
    %% RELACIONES - BIENES MUEBLES (CASO)
    %% ═══════════════════════════════════════════════

    sim_casos_estudios ||--o{ sim_caso_bienes_muebles : "tiene muebles"
    sim_cat_categorias_bien_mueble ||--o{ sim_caso_bienes_muebles : "categoria"
    sim_cat_tipos_bien_mueble ||--o{ sim_caso_bienes_muebles : "subtipo"

    sim_caso_bienes_muebles ||--o| sim_caso_bm_banco : "detalle banco"
    sim_caso_bienes_muebles ||--o| sim_caso_bm_seguro : "detalle seguro"
    sim_caso_bienes_muebles ||--o| sim_caso_bm_transporte : "detalle transporte"
    sim_caso_bienes_muebles ||--o| sim_caso_bm_acciones : "detalle acciones"
    sim_caso_bienes_muebles ||--o| sim_caso_bm_bonos : "detalle bonos"
    sim_caso_bienes_muebles ||--o| sim_caso_bm_semovientes : "detalle semovientes"
    sim_caso_bienes_muebles ||--o| sim_caso_bm_caja_ahorro : "detalle caja ahorro"
    sim_caso_bienes_muebles ||--o| sim_caso_bm_cuentas_cobrar : "detalle cuentas"
    sim_caso_bienes_muebles ||--o| sim_caso_bm_opciones_compra : "detalle opciones"
    sim_caso_bienes_muebles ||--o| sim_caso_bm_prestaciones : "detalle prestaciones"

    sim_cat_bancos ||--o{ sim_caso_bm_banco : "banco"
    sim_empresas ||--o{ sim_caso_bm_seguro : "aseguradora"
    sim_empresas ||--o{ sim_caso_bm_acciones : "empresa"
    sim_empresas ||--o{ sim_caso_bm_caja_ahorro : "empresa"
    sim_cat_tipos_semoviente ||--o{ sim_caso_bm_semovientes : "tipo animal"
    sim_cat_bancos ||--o{ sim_caso_bm_prestaciones : "banco"
    sim_empresas ||--o{ sim_caso_bm_prestaciones : "empresa"

    %% ═══════════════════════════════════════════════
    %% RELACIONES - PASIVOS Y DEDUCCIONES (CASO)
    %% ═══════════════════════════════════════════════

    sim_casos_estudios ||--o{ sim_caso_pasivos_deuda : "tiene deudas"
    sim_cat_tipos_pasivo_deuda ||--o{ sim_caso_pasivos_deuda : "tipo deuda"
    sim_cat_bancos ||--o{ sim_caso_pasivos_deuda : "banco"

    sim_casos_estudios ||--o{ sim_caso_pasivos_gastos : "tiene gastos"
    sim_cat_tipos_pasivo_gasto ||--o{ sim_caso_pasivos_gastos : "tipo gasto"

    sim_casos_estudios ||--o{ sim_caso_exenciones : "tiene exenciones"
    sim_casos_estudios ||--o{ sim_caso_exoneraciones : "tiene exoneraciones"

    %% ═══════════════════════════════════════════════
    %% RELACIONES - CATÁLOGOS FISCALES
    %% ═══════════════════════════════════════════════

    sim_cat_grupos_tarifa ||--o{ sim_cat_tarifas_sucesion : "define tarifas"
    sim_cat_grupos_tarifa ||--o{ sim_cat_tramos_tarifa : "define tramos"
    sim_cat_grupos_tarifa ||--o{ sim_cat_parentescos : "agrupa"
    sim_cat_categorias_bien_mueble ||--o{ sim_cat_tipos_bien_mueble : "agrupa subtipos"

    %% ═══════════════════════════════════════════════
    %% RELACIONES - INTENTOS DEL ESTUDIANTE
    %% ═══════════════════════════════════════════════

    sim_caso_asignaciones ||--o{ sim_intentos : "genera intentos"

    sim_intentos ||--o| sim_intento_datos_basicos : "datos basicos"
    sim_intentos ||--o{ sim_intento_relaciones : "herederos"
    sim_intentos ||--o{ sim_intento_direcciones : "direcciones"
    sim_intentos ||--o{ sim_intento_tipoherencias : "tipos herencia"
    sim_intentos ||--o{ sim_intento_bienes_inmuebles : "inmuebles"
    sim_intentos ||--o{ sim_intento_bienes_muebles : "muebles"
    sim_intentos ||--o{ sim_intento_bienes_litigiosos : "litigiosos"
    sim_intentos ||--o{ sim_intento_pasivos_deuda : "deudas"
    sim_intentos ||--o{ sim_intento_pasivos_gastos : "gastos"
    sim_intentos ||--o{ sim_intento_exenciones : "exenciones"
    sim_intentos ||--o{ sim_intento_exoneraciones : "exoneraciones"
    sim_intentos ||--o{ sim_intento_prorrogas : "prorrogas"
    sim_intentos ||--o{ sim_intento_calculo_manual : "calculos"
    sim_intentos ||--o{ sim_intento_estados : "historial estados"
    sim_intentos ||--o{ sim_intento_observaciones : "observaciones"

    sim_intento_bienes_inmuebles ||--o{ sim_intento_bien_inmueble_tipo_rel : "tipo inmueble"
    sim_intento_bienes_muebles ||--o| sim_intento_bm_banco : "detalle banco"
    sim_intento_bienes_muebles ||--o| sim_intento_bm_seguro : "detalle seguro"
    sim_intento_bienes_muebles ||--o| sim_intento_bm_transporte : "detalle transporte"
    sim_intento_bienes_muebles ||--o| sim_intento_bm_acciones : "detalle acciones"
    sim_intento_bienes_muebles ||--o| sim_intento_bm_bonos : "detalle bonos"
    sim_intento_bienes_muebles ||--o| sim_intento_bm_semovientes : "detalle semovientes"
    sim_intento_bienes_muebles ||--o| sim_intento_bm_caja_ahorro : "detalle caja ahorro"
    sim_intento_bienes_muebles ||--o| sim_intento_bm_cuentas_cobrar : "detalle cuentas"
    sim_intento_bienes_muebles ||--o| sim_intento_bm_opciones_compra : "detalle opciones"
    sim_intento_bienes_muebles ||--o| sim_intento_bm_prestaciones : "detalle prestaciones"

    sim_intento_relaciones ||--o{ sim_intento_calculo_manual : "cuota"

    %% ═══════════════════════════════════════════════
    %% RELACIONES - INTENTOS → CATÁLOGOS
    %% ═══════════════════════════════════════════════

    paises ||--o{ sim_intento_datos_basicos : "nacionalidad"
    sim_cat_parentescos ||--o{ sim_intento_relaciones : "parentesco"
    sim_cat_tipoherencias ||--o{ sim_intento_tipoherencias : "tipo herencia"
    sim_cat_tipos_bien_inmueble ||--o{ sim_intento_bien_inmueble_tipo_rel : "tipo inmueble"
    sim_cat_categorias_bien_mueble ||--o{ sim_intento_bienes_muebles : "categoria"
    sim_cat_tipos_bien_mueble ||--o{ sim_intento_bienes_muebles : "subtipo"

    sim_cat_bancos ||--o{ sim_intento_bm_banco : "banco"
    sim_empresas ||--o{ sim_intento_bm_seguro : "aseguradora"
    sim_empresas ||--o{ sim_intento_bm_acciones : "empresa"
    sim_empresas ||--o{ sim_intento_bm_caja_ahorro : "empresa"
    sim_cat_tipos_semoviente ||--o{ sim_intento_bm_semovientes : "tipo animal"
    sim_cat_bancos ||--o{ sim_intento_bm_prestaciones : "banco"
    sim_empresas ||--o{ sim_intento_bm_prestaciones : "empresa"

    sim_cat_tipos_pasivo_deuda ||--o{ sim_intento_pasivos_deuda : "tipo deuda"
    sim_cat_bancos ||--o{ sim_intento_pasivos_deuda : "banco"
    sim_cat_tipos_pasivo_gasto ||--o{ sim_intento_pasivos_gastos : "tipo gasto"

    %% ═══════════════════════════════════════════════
    %% RELACIONES - INTENTO DIRECCIONES → GEOGRAFÍA
    %% ═══════════════════════════════════════════════

    estados ||--o{ sim_intento_direcciones : "estado"
    municipios ||--o{ sim_intento_direcciones : "municipio"
    parroquias ||--o{ sim_intento_direcciones : "parroquia"
    ciudades ||--o{ sim_intento_direcciones : "ciudad"
    codigos_postales ||--o{ sim_intento_direcciones : "codigo postal"
```
