<?php
$pageTitle = 'Nuevo Caso Sucesoral';
$activePage = 'casos-sucesorales';
$userName = htmlspecialchars($_SESSION['user_name'] ?? 'Profesor', ENT_QUOTES, 'UTF-8');

$extraCss = '
<link rel="stylesheet" href="' . asset('css/global/autocomplete_dropdown.css') . '">
<link rel="stylesheet" href="' . asset('css/professor/crear_caso.css') . '">
';

$extraJs = '
<script>
  window.BASE_URL = "' . base_url() . '";
</script>
<script src="' . asset('js/global/number_utils.js') . '"></script>
<script src="' . asset('js/global/decimal_input.js') . '"></script>
<script src="' . asset('js/global/autocomplete_dropdown.js') . '"></script>
<script type="module" src="' . asset('js/professor/crear_caso/main.js') . '"></script>
';

ob_start();
?>

<!-- ===== BREADCRUMB TOP BAR ===== -->
<div class="cc-topbar">
  <div class="cc-topbar__left">
    <a href="<?= base_url('/casos-sucesorales') ?>" class="cc-topbar__back">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
        stroke-linecap="round">
        <polyline points="15 18 9 12 15 6" />
      </svg>
      Casos Sucesorales
    </a>
    <span class="cc-topbar__sep">/</span>
    <span class="cc-topbar__title">Nuevo Caso</span>
    <span class="status-badge status-draft">Borrador</span>
  </div>
  <div class="cc-topbar__right">
    <button class="btn btn-outline" id="btnTourTutorial" style="margin-right: 8px; border-color: var(--gray-300); background: #fff;" title="Ver tutorial guiado">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="10"></circle>
        <polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"></polygon>
      </svg>
      Tutorial
    </button>
    <button class="btn btn-outline" id="btnChecklistToggle" style="margin-right: 8px; border-color: var(--gray-300); background: #fff;">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M9 11l3 3L22 4" />
        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11" />
      </svg>
      Estado del Caso
      <span id="checklistPercentage" style="background:#e2e8f0; color:#475569; border-radius:10px; padding:2px 8px; font-size:11px; margin-left:6px; font-weight:700;">0%</span>
    </button>
    <button class="btn btn-outline" id="btnSaveDraft" style="border-color: var(--gray-300); background: #fff;">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
        stroke-linecap="round">
        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" />
        <polyline points="17 21 17 13 7 13 7 21" />
        <polyline points="7 3 7 8 15 8" />
      </svg>
      Guardar Borrador
    </button>
  </div>
</div>

<!-- ===== STEPPER ===== -->
<div class="cc-stepper" id="stepper">
  <div class="cc-stepper__step is-active" data-step="0">
    <div class="cc-stepper__icon">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
        stroke-linecap="round">
        <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z" />
        <polyline points="14 2 14 8 20 8" />
        <line x1="16" y1="13" x2="8" y2="13" />
        <line x1="16" y1="17" x2="8" y2="17" />
      </svg>
    </div>
    <div class="cc-stepper__label">
      <span class="cc-stepper__name">Datos del Caso</span>
      <span class="cc-stepper__sub">Caso, herencia y causante</span>
    </div>
  </div>
  <div class="cc-stepper__connector"></div>
  <div class="cc-stepper__step" data-step="1">
    <div class="cc-stepper__icon">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
        stroke-linecap="round">
        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
        <circle cx="9" cy="7" r="4" />
        <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
        <path d="M16 3.13a4 4 0 0 1 0 7.75" />
      </svg>
    </div>
    <div class="cc-stepper__label">
      <span class="cc-stepper__name">Relaciones de la Sucesión</span>
      <span class="cc-stepper__sub">Herederos y representantes</span>
    </div>
  </div>
  <div class="cc-stepper__connector"></div>
  <div class="cc-stepper__step" data-step="2">
    <div class="cc-stepper__icon">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
        stroke-linecap="round">
        <rect x="2" y="7" width="20" height="14" rx="2" ry="2" />
        <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16" />
      </svg>
    </div>
    <div class="cc-stepper__label">
      <span class="cc-stepper__name">Inventario</span>
      <span class="cc-stepper__sub">Bienes, pasivos y exenciones</span>
    </div>
  </div>
  <div class="cc-stepper__connector"></div>
  <div class="cc-stepper__step" data-step="3">
    <div class="cc-stepper__icon">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
        stroke-linecap="round">
        <polyline points="20 6 9 17 4 12" />
      </svg>
    </div>
    <div class="cc-stepper__label">
      <span class="cc-stepper__name">Resumen</span>
      <span class="cc-stepper__sub">Verificación final</span>
    </div>
  </div>
</div>

<!-- =============================================================== -->
<!-- STEP 0: Datos del Caso y Causante                               -->
<!-- =============================================================== -->
<div class="cc-step" id="step-0">

  <!-- Sección 1: Datos del Caso (→ sim_casos_estudios) -->
  <div class="cc-card cc-card--collapsible">
    <div class="cc-card__header cc-card__toggle">
      <div>
        <h3>Datos del Caso</h3>
        <p>Información general del caso práctico</p>
      </div>
      <svg class="cc-card__chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
        stroke-width="2">
        <polyline points="6 9 12 15 18 9" />
      </svg>
    </div>
    <div class="cc-card__body cc-card__collapse">
      <div class="cc-grid cc-grid--2">
        <div class="cc-field cc-span-2">
          <label>Título del caso <span class="cc-required">*</span></label>
          <input type="text" data-bind="caso.titulo" placeholder="Ej: Sucesión García - Caso práctico bienes mixtos">
        </div>
        <div class="cc-field cc-span-2">
          <label>Descripción / Narrativa del escenario <span class="cc-required">*</span></label>
          <textarea data-bind="caso.descripcion" rows="3"
            placeholder="Describa el contexto del caso para orientar al estudiante..."></textarea>
        </div>
      </div>
    </div>
  </div>
  <!-- Tipo de Sucesión -->
  <div class="cc-card cc-card--collapsible">
    <div class="cc-card__header cc-card__toggle">
      <div>
        <h3>Tipo de Sucesión</h3>
        <p>Determine el tipo documental de la sucesión para este caso</p>
      </div>
      <svg class="cc-card__chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
        stroke-width="2">
        <polyline points="6 9 12 15 18 9" />
      </svg>
    </div>
    <div class="cc-card__body cc-card__collapse">
      <div class="cc-radio-pills">
        <label class="cc-radio-pill">
          <input type="radio" name="tipo_sucesion" value="Con Cédula" data-bind="caso.tipo_sucesion" checked>
          <span class="cc-radio-pill__content">
            <span class="cc-radio-pill__title">Con Cédula</span>
          </span>
        </label>
        <label class="cc-radio-pill">
          <input type="radio" name="tipo_sucesion" value="Sin Cédula" data-bind="caso.tipo_sucesion">
          <span class="cc-radio-pill__content">
            <span class="cc-radio-pill__title">Sin Cédula</span>
          </span>
        </label>
      </div>
    </div>
  </div>

  <!-- Tipo de Herencia -->
  <div class="cc-card cc-card--collapsible">
    <div class="cc-card__header cc-card__toggle">
      <div>
        <h3>Tipo de Herencia</h3>
        <p>Seleccione el tipo de herencia que aplicará en este caso</p>
      </div>
      <svg class="cc-card__chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
        stroke-width="2">
        <polyline points="6 9 12 15 18 9" />
      </svg>
    </div>
    <div class="cc-card__body cc-card__collapse">
      <div class="cc-grid cc-grid--3" id="herenciaCheckboxes">
        <!-- Generated by JS -->
      </div>
      <div id="herenciaExtras">
        <!-- Extra fields for Testamento or Inventario appended by JS -->
      </div>
    </div>
  </div>

  <!-- Datos del Causante -->
  <div class="cc-card cc-card--collapsible">
    <div class="cc-card__header cc-card__toggle">
      <div>
        <h3>Datos del Causante</h3>
        <p>Persona cuya sucesión se analiza en el caso</p>
      </div>
      <svg class="cc-card__chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
        stroke-width="2">
        <polyline points="6 9 12 15 18 9" />
      </svg>
    </div>
    <div class="cc-card__body cc-card__collapse">

      <!-- Buscar persona existente -->
      <div class="cc-search-persona" id="causanteSearchContainer">
        <label class="cc-search-persona__label">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          Buscar persona existente <span class="cc-search-persona__optional">(opcional)</span>
        </label>
        <input type="text" id="inputBuscarCausante" placeholder="Escriba cédula, RIF o nombre para buscar..." autocomplete="off">
      </div>

      <!-- Inline validation errors -->
      <div class="cc-inline-errors" id="causanteErrors">
        <p class="cc-inline-errors__title">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          <span id="causanteErrorsTitle">Error de validación:</span>
        </p>
        <ul class="cc-inline-errors__list" id="causanteErrorsList"></ul>
      </div>

      <div class="cc-grid cc-grid--4">
        <div class="cc-field" id="campo_tipo_cedula_causante">
          <label>Tipo Cédula <span class="cc-required">*</span></label>
          <select data-bind="causante.tipo_cedula">
            <option value="">Seleccione...</option>
            <option value="V">V - Venezolano</option>
            <option value="E">E - Extranjero</option>
          </select>
        </div>
        <div class="cc-field" id="campo_cedula_causante">
          <label>Cédula <span class="cc-required">*</span></label>
          <input type="text" data-bind="causante.cedula" placeholder="12.345.678" autocomplete="off">
        </div>
        <div class="cc-field">
          <label>Nombres <span class="cc-required">*</span></label>
          <input type="text" data-bind="causante.nombres" placeholder="Nombres del causante">
        </div>
        <div class="cc-field">
          <label>Apellidos <span class="cc-required">*</span></label>
          <input type="text" data-bind="causante.apellidos" placeholder="Apellidos del causante">
        </div>
        <div class="cc-field">
          <label>Sexo <span class="cc-required">*</span></label>
          <select data-bind="causante.sexo">
            <option value="">Seleccione...</option>
            <option value="M">Masculino</option>
            <option value="F">Femenino</option>
          </select>
        </div>
        <div class="cc-field">
          <label>Estado Civil <span class="cc-required">*</span></label>
          <select data-bind="causante.estado_civil">
            <option value="">Seleccione...</option>
            <option value="Soltero">Soltero</option>
            <option value="Casado">Casado</option>
            <option value="Viudo">Viudo</option>
            <option value="Divorciado">Divorciado</option>
            <option value="Concubinato">Concubinato</option>
          </select>
        </div>
        <div class="cc-field">
          <label>Fecha de Nacimiento <span class="cc-required">*</span></label>
          <input type="date" data-bind="causante.fecha_nacimiento">
        </div>
        <div class="cc-field">
          <label>Fecha de Fallecimiento <span class="cc-required">*</span></label>
          <input type="date" data-bind="causante.fecha_fallecimiento">
        </div>
        <div class="cc-field">
          <label>Nacionalidad <span class="cc-required">*</span></label>
          <select data-bind="causante.nacionalidad">
            <option value="">Seleccione...</option>
            <!-- Se llenará dinámicamente con todos los países en Fase 2 -->
            <option value="1">Venezuela</option>
            <option value="2">Colombia</option>
            <option value="3">Otro</option>
          </select>
        </div>

        <!-- Datos Fiscales del Causante -->
        <h4 class="cc-section-subtitle cc-mt" style="grid-column: 1 / -1;">Datos Fiscales</h4>
        <div class="cc-field">
          <label>Domiciliado en el país <span class="cc-required">*</span></label>
          <select data-bind="datos_fiscales_causante.domiciliado_pais" disabled
            style="background-color: var(--cc-slate-50, #f8fafc);">
            <option value="1" selected>Sí</option>
            <option value="0">No</option>
          </select>
        </div>
        <div class="cc-field">
          <label>Fecha de Cierre Fiscal <span class="cc-required">*</span></label>
          <input type="date" data-bind="datos_fiscales_causante.fecha_cierre_fiscal" id="input_fecha_cierre_fiscal">
        </div>

        <!-- Block Acta de defunción condicional -->
        <div id="bloque_acta_defuncion" style="display:none; grid-column: 1 / -1;">
          <h4 class="cc-section-subtitle cc-mt">Acta de Defunción</h4>
          <div class="cc-grid cc-grid--3 cc-mt">
            <div class="cc-field">
              <label>Número de Acta <span class="cc-required">*</span></label>
              <input type="text" data-bind="acta_defuncion.numero_acta" placeholder="Número del acta">
            </div>
            <div class="cc-field">
              <label>Año del Acta <span class="cc-required">*</span></label>
              <input type="text" data-bind="acta_defuncion.year_acta" placeholder="2024" maxlength="4">
            </div>
            <div class="cc-field">
              <label>Parroquia de Emisión <span class="cc-required">*</span></label>
              <input type="text" data-bind="acta_defuncion.parroquia_registro_id" placeholder="Ej: Catedral"
                maxlength="98">
            </div>
          </div>
        </div>

      </div>

      <!-- Footer Buttons -->
      <div class="cc-mt cc-text-right">
        <button type="button" class="btn btn-secondary btn--sm"
          onclick="CC.clearCausante()">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round">
            <path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
          </svg>
          Limpiar Campos
        </button>
      </div>

    </div>
  </div>

  <!-- Domicilio Fiscal y otras Direcciones (collapsible) -->
  <div class="cc-card cc-card--collapsible">
    <div class="cc-card__header cc-card__toggle">
      <div>
        <h3>Domicilio Fiscal y otras Direcciones</h3>
        <p>Dirección fiscal y direcciones adicionales del causante</p>
      </div>
      <svg class="cc-card__chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
        stroke-width="2">
        <polyline points="6 9 12 15 18 9" />
      </svg>
    </div>
    <div class="cc-card__body cc-card__collapse p-0" style="display:none;">

      <!-- Inline validation errors -->
      <div class="cc-inline-errors" id="direccionErrors">
        <p class="cc-inline-errors__title">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          Campos requeridos faltantes:
        </p>
        <ul class="cc-inline-errors__list" id="direccionErrorsList"></ul>
      </div>

      <!-- Tipo de dirección -->
      <div class="cc-p-4 cc-border-b">
        <div id="direccionesTableContainer" style="display:none; margin-bottom: 20px;">
          <div class="table-container">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Tipo Dirección</th>
                  <th>Detalle (Vialidad/Inmueble)</th>
                  <th>Ubicación</th>
                  <th>Contactos</th>
                  <th class="cc-th-action">Acción</th>
                </tr>
              </thead>
              <tbody id="direccionesTableBody">
                <!-- Rendered by JS -->
              </tbody>
            </table>
          </div>
        </div>

        <div class="cc-grid cc-grid--1">
          <div class="cc-field">
            <select data-bind="domicilio_causante.tipo_direccion" class="cc-select-lg">
              <option value="">SELECCIONAR TIPO DE DIRECCIÓN</option>
              <option value="Bodega_Almacenamiento_Deposito">BODEGA, ALMACENAMIENTO, DEPÓSITO</option>
              <option value="Casa_Matriz_Establecimiento_Principal">CASA MATRIZ O ESTABLECIMIENTO PRINCIPAL</option>
              <option value="Direccion_Notificacion_Fisica">DIRECCIÓN DE NOTIFICACIÓN FÍSICA</option>
              <option value="Domicilio_Fiscal" selected>DOMICILIO FISCAL</option>
              <option value="Negocio_Independiente">NEGOCIO INDEPENDIENTE</option>
              <option value="Planta_Industrial_Fabrica">PLANTA INDUSTRIAL O FABRICA</option>
              <option value="Sucursal_Comercial">SUCURSAL COMERCIAL</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Main Grid Sections -->
      <div class="cc-addr-table">

        <!-- ROW 1: Headers with Radios -->
        <div class="cc-addr-tr">
          <!-- Left: Vialidad -->
          <div class="cc-addr-th">
            <div class="cc-addr-radios cc-addr-radios--inline">
              <label class="cc-radio-pill"><input type="radio" name="tipo_vialidad" value="Calle"
                  data-bind="domicilio_causante.tipo_vialidad"> calle</label>
              <label class="cc-radio-pill"><input type="radio" name="tipo_vialidad" value="Avenida"
                  data-bind="domicilio_causante.tipo_vialidad"> avenida</label>
              <label class="cc-radio-pill"><input type="radio" name="tipo_vialidad" value="Vereda"
                  data-bind="domicilio_causante.tipo_vialidad"> vereda</label>
              <label class="cc-radio-pill"><input type="radio" name="tipo_vialidad" value="Carretera"
                  data-bind="domicilio_causante.tipo_vialidad"> carretera</label>
              <label class="cc-radio-pill"><input type="radio" name="tipo_vialidad" value="Esquina"
                  data-bind="domicilio_causante.tipo_vialidad"> esquina</label>
              <label class="cc-radio-pill"><input type="radio" name="tipo_vialidad" value="Carrera"
                  data-bind="domicilio_causante.tipo_vialidad"> carrera</label>
            </div>
          </div>
          <!-- Right: Vivienda -->
          <div class="cc-addr-th">
            <div class="cc-addr-radios cc-addr-radios--inline">
              <label class="cc-radio-pill"><input type="radio" name="tipo_inmueble" value="Edificio"
                  data-bind="domicilio_causante.tipo_inmueble"> edificio</label>
              <label class="cc-radio-pill"><input type="radio" name="tipo_inmueble" value="Centro_Comercial"
                  data-bind="domicilio_causante.tipo_inmueble"> centro comercial</label>
              <label class="cc-radio-pill"><input type="radio" name="tipo_inmueble" value="Quinta"
                  data-bind="domicilio_causante.tipo_inmueble"> quinta</label>
              <label class="cc-radio-pill"><input type="radio" name="tipo_inmueble" value="Casa"
                  data-bind="domicilio_causante.tipo_inmueble"> casa</label>
              <label class="cc-radio-pill"><input type="radio" name="tipo_inmueble" value="Local"
                  data-bind="domicilio_causante.tipo_inmueble"> local</label>
            </div>
          </div>
        </div>

        <!-- ROW 2: Inputs -->
        <div class="cc-addr-tr">
          <div class="cc-addr-td">
            <div class="cc-field">
              <label style="font-size:10px; color:#64748b; margin-bottom:2px; font-weight:600;">NOMBRE DE
                VIALIDAD</label>
              <input type="text" data-bind="domicilio_causante.nombre_vialidad" placeholder="">
            </div>
          </div>
          <div class="cc-addr-td" style="display: flex; gap: 8px;">
            <div class="cc-field" style="flex: 2;">
              <label style="font-size:10px; color:#64748b; margin-bottom:2px; font-weight:600;">NOMBRE /
                DESCRIPCIÓN</label>
              <input type="text" id="input_desc_inmueble" data-bind="domicilio_causante.desc_inmueble"
                placeholder="Ej: Torre A">
            </div>
            <div class="cc-field" style="flex: 1;">
              <label id="lbl_piso_nivel"
                style="font-size:10px; color:#64748b; margin-bottom:2px; font-weight:600;">PISO/NRO</label>
              <input type="text" id="input_piso_nivel" data-bind="domicilio_causante.piso_nivel" placeholder="">
            </div>
          </div>
        </div>

        <!-- ROW 3: Headers with Radios -->
        <div class="cc-addr-tr">
          <!-- Left: Nro/Piso (Sub-vivienda) -->
          <div class="cc-addr-th">
            <div class="cc-addr-radios cc-addr-radios--inline">
              <label class="cc-radio-pill"><input type="radio" name="tipo_nivel" value="Apartamento"
                  data-bind="domicilio_causante.tipo_nivel"> apartamento</label>
              <label class="cc-radio-pill"><input type="radio" name="tipo_nivel" value="Local"
                  data-bind="domicilio_causante.tipo_nivel"> local</label>
              <label class="cc-radio-pill"><input type="radio" name="tipo_nivel" value="Oficina"
                  data-bind="domicilio_causante.tipo_nivel"> oficina</label>
            </div>
          </div>
          <!-- Right: Sector -->
          <div class="cc-addr-th">
            <div class="cc-addr-radios cc-addr-radios--inline">
              <label class="cc-radio-pill"><input type="radio" name="tipo_sector" value="Urbanizacion"
                  data-bind="domicilio_causante.tipo_sector"> urbanización</label>
              <label class="cc-radio-pill"><input type="radio" name="tipo_sector" value="Zona"
                  data-bind="domicilio_causante.tipo_sector"> zona</label>
              <label class="cc-radio-pill"><input type="radio" name="tipo_sector" value="Sector"
                  data-bind="domicilio_causante.tipo_sector"> sector</label>
              <label class="cc-radio-pill"><input type="radio" name="tipo_sector" value="Conjunto_Residencial"
                  data-bind="domicilio_causante.tipo_sector"> conjunto res.</label>
              <label class="cc-radio-pill"><input type="radio" name="tipo_sector" value="Barrio"
                  data-bind="domicilio_causante.tipo_sector"> barrio</label>
              <label class="cc-radio-pill"><input type="radio" name="tipo_sector" value="Caserio"
                  data-bind="domicilio_causante.tipo_sector"> caserío</label>
            </div>
          </div>
        </div>

        <!-- ROW 4: Inputs -->
        <div class="cc-addr-tr">
          <div class="cc-addr-td">
            <div class="cc-field"><input type="text" data-bind="domicilio_causante.nro_nivel" placeholder=""></div>
          </div>
          <div class="cc-addr-td">
            <div class="cc-field"><input type="text" data-bind="domicilio_causante.nombre_sector" placeholder=""></div>
          </div>
        </div>

        <!-- ROW 5: Estado / Municipio Headers -->
        <div class="cc-addr-tr">
          <div class="cc-addr-th"><span>Estado</span></div>
          <div class="cc-addr-th"><span>Municipio</span></div>
        </div>

        <!-- ROW 6: Estado / Municipio Inputs -->
        <div class="cc-addr-tr">
          <div class="cc-addr-td">
            <div class="cc-field">
              <select data-bind="domicilio_causante.estado">
                <option value="">SELECCIONAR</option>
              </select>
            </div>
          </div>
          <div class="cc-addr-td">
            <div class="cc-field">
              <select data-bind="domicilio_causante.municipio">
                <option value="">SELECCIONAR</option>
              </select>
            </div>
          </div>
        </div>

        <!-- ROW 7: Parroquia / Ciudad Headers -->
        <div class="cc-addr-tr">
          <div class="cc-addr-th"><span>Parroquia</span></div>
          <div class="cc-addr-th"><span>Ciudad</span></div>
        </div>

        <!-- ROW 8: Parroquia / Ciudad Inputs -->
        <div class="cc-addr-tr">
          <div class="cc-addr-td">
            <div class="cc-field">
              <select data-bind="domicilio_causante.parroquia">
                <option value="">SELECCIONAR</option>
              </select>
            </div>
          </div>
          <div class="cc-addr-td">
            <div class="cc-field">
              <select data-bind="domicilio_causante.ciudad">
                <option value="">SELECCIONAR</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <!-- Contact Info Section -->
      <div class="cc-addr-table cc-mt-0">
        <!-- ROW 9: Contact Headers (4 cols) -->
        <div class="cc-addr-tr cc-addr-tr--4">
          <div class="cc-addr-th"><span>Teléfono Fijo Ej: 0212-1234567</span></div>
          <div class="cc-addr-th"><span>Teléfono Celular Ej: 0416-1234567</span></div>
          <div class="cc-addr-th"><span>Fax Ej: 0212-1234567</span></div>
          <div class="cc-addr-th"><span>Zona Postal</span></div>
        </div>

        <!-- ROW 10: Contact Inputs -->
        <div class="cc-addr-tr cc-addr-tr--4">
          <div class="cc-addr-td">
            <div class="cc-field"><input type="text" data-bind="domicilio_causante.telefono_fijo" placeholder=""></div>
          </div>
          <div class="cc-addr-td">
            <div class="cc-field"><input type="text" data-bind="domicilio_causante.telefono_celular" placeholder="">
            </div>
          </div>
          <div class="cc-addr-td">
            <div class="cc-field"><input type="text" data-bind="domicilio_causante.fax" placeholder=""></div>
          </div>
          <div class="cc-addr-td">
            <div class="cc-field">
              <select data-bind="domicilio_causante.codigo_postal_id">
                <option value="">SELECCIONAR</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <!-- Punto de Referencia -->
      <div class="cc-addr-table cc-mt-0">
        <div class="cc-addr-tr">
          <div class="cc-addr-th"><span>Punto de Referencia</span></div>
        </div>
        <div class="cc-addr-tr">
          <div class="cc-addr-td">
            <div class="cc-field">
              <input type="text" data-bind="domicilio_causante.punto_referencia" placeholder="">
            </div>
          </div>
        </div>
      </div>

      <!-- Footer de agregar direcciones de la tarjeta -->
      <div class="cc-p-4 cc-text-right">
        <button type="button" class="btn btn-secondary" onclick="CC.saveDireccion()" id="btnSaveDireccion">+
          Agregar Dirección</button>
      </div>

    </div>
  </div>

  <!-- Prórrogas -->
  <div class="cc-card cc-card--collapsible cc-mt" id="card_prorrogas">
    <div class="cc-card__header cc-card__toggle">
      <div>
        <h3>Prórrogas</h3>
        <p>Registrar prórrogas concedidas para este caso</p>
      </div>
      <svg class="cc-card__chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
        stroke-width="2">
        <polyline points="6 9 12 15 18 9" />
      </svg>
    </div>
    <div class="cc-card__body cc-card__collapse">

      <!-- Inline validation errors -->
      <div class="cc-inline-errors" id="prorrogaErrors">
        <p class="cc-inline-errors__title">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          Campos requeridos faltantes:
        </p>
        <ul class="cc-inline-errors__list" id="prorrogaErrorsList"></ul>
      </div>

      <!-- Listado de Prórrogas -->
      <div id="prorrogasTableContainer" style="display:none; margin-bottom: 20px;">
        <div class="table-container">
          <table class="data-table">
            <thead>
              <tr>
                <th>F. Solicitud</th>
                <th>Nro Resolución</th>
                <th>F. Resolución</th>
                <th>Plazo (días)</th>
                <th>F. Vencimiento</th>
                <th class="cc-th-action">Acción</th>
              </tr>
            </thead>
            <tbody id="prorrogasTableBody">
              <!-- Rendered by JS -->
            </tbody>
          </table>
        </div>
      </div>

      <div class="cc-grid cc-grid--2">
        <!-- Fila 1 -->
        <div class="cc-field">
          <label>Fecha de Solicitud <span class="cc-required">*</span></label>
          <input type="date" data-bind="prorroga.fecha_solicitud">
        </div>
        <div class="cc-field">
          <label>Nro de Resolución <span class="cc-required">*</span></label>
          <input type="text" data-bind="prorroga.nro_resolucion" placeholder="Nro de Resolución">
        </div>

        <!-- Fila 2 -->
        <div class="cc-field">
          <label>Fecha de Resolución <span class="cc-required">*</span></label>
          <input type="date" data-bind="prorroga.fecha_resolucion">
        </div>
        <div class="cc-field">
          <label>Plazo Otorgado (días) <span class="cc-required">*</span></label>
          <input type="number" data-bind="prorroga.plazo_dias" placeholder="Ej: 30" min="1">
        </div>

        <!-- Fila 3 -->
        <div class="cc-field">
          <label>Fecha de Vencimiento <span class="cc-required">*</span></label>
          <input type="date" data-bind="prorroga.fecha_vencimiento">
        </div>
        <div></div>

        <!-- Fila 4 -->
        <div class="cc-field cc-span-2 cc-text-right" style="margin-top: 10px;">
          <button type="button" class="btn btn-secondary btn--sm" style="width: max-content; margin-left: auto;"
            onclick="CC.saveProrroga()" id="btnSaveProrroga">+ Agregar Prórroga</button>
        </div>
      </div>
    </div>
  </div>

</div>

<!-- =============================================================== -->
<!-- STEP 1: Relaciones de la Sucesión                               -->
<!-- =============================================================== -->
<div class="cc-step" id="step-1" style="display:none;">

  <!-- Tarjeta para el Representante (Movida y Rediseñada) -->
  <div class="cc-card cc-card--collapsible" style="margin-bottom: 24px;">
    <div class="cc-card__header cc-card__toggle">
      <div>
        <h3>Representante / Apoderado</h3>
        <p>Datos de la persona designada ante el SENIAT</p>
      </div>
      <svg class="cc-card__chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
        stroke-width="2">
        <polyline points="6 9 12 15 18 9" />
      </svg>
    </div>
    <div class="cc-card__body cc-card__collapse">

      <!-- Inline validation errors -->
      <div class="cc-inline-errors" id="representanteErrors">
        <p class="cc-inline-errors__title">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          <span id="representanteErrorsTitle">Error de validación:</span>
        </p>
        <ul class="cc-inline-errors__list" id="representanteErrorsList"></ul>
      </div>

      <!-- Search bar for existing persona -->
      <div class="cc-search-persona">
        <label class="cc-search-persona__label">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
          </svg>
          Buscar Persona Existente
        </label>
        <input type="text" id="inputBuscarRepresentante" placeholder="Escriba cédula, RIF o nombre para buscar..." autocomplete="off">
      </div>

      <div class="cc-grid cc-grid--3">
        <!-- Nombres y Apellidos -->
        <div class="cc-field cc-span-1">
          <label>Nombres <span class="cc-required">*</span></label>
          <input type="text" data-bind="representante.nombres" placeholder="Ej: Juan Carlos">
        </div>
        <div class="cc-field cc-span-1">
          <label>Apellidos <span class="cc-required">*</span></label>
          <input type="text" data-bind="representante.apellidos" placeholder="Ej: Pérez Gómez">
        </div>

        <!-- Cédula -->
        <div class="cc-field cc-span-1" id="wrap-rep-cedula">
          <label>Cédula <span class="cc-required">*</span></label>
          <div style="display: flex; gap: 8px;">
            <select data-bind="representante.letra_cedula" id="sel-rep-letra" style="width: 70px;">
              <option value="V">V</option>
              <option value="E">E</option>
            </select>
            <input type="text" data-bind="representante.cedula" id="inp-rep-cedula" placeholder="Ej: 12345678"
              style="flex: 1;" autocomplete="off">
          </div>
        </div>

        <!-- Sexo -->
        <div class="cc-field cc-span-1">
          <label>Sexo <span class="cc-required">*</span></label>
          <select data-bind="representante.sexo">
            <option value="">Seleccione...</option>
            <option value="M">Masculino</option>
            <option value="F">Femenino</option>
          </select>
        </div>

        <!-- Fecha de Nacimiento -->
        <div class="cc-field cc-span-1">
          <label>Fecha de Nacimiento <span class="cc-required">*</span></label>
          <input type="date" data-bind="representante.fecha_nacimiento">
        </div>

        <!-- RIF -->
        <div class="cc-field cc-span-1" id="wrap-rep-rif">
          <label>RIF <span class="cc-required">*</span></label>
          <div style="display: flex; gap: 8px;">
            <select data-bind="representante.letra_rif" id="sel-rep-letra-rif" style="width: 70px;">
              <option value="V">V</option>
              <option value="J">J</option>
            </select>
            <input type="text" data-bind="representante.rif_personal" id="inp-rep-rif" placeholder="Ej: 123456789"
              style="flex: 1;" autocomplete="off">
          </div>
        </div>
      </div>

      <!-- Footer Buttons -->
      <div class="cc-mt cc-text-right">
        <button type="button" class="btn btn-secondary btn--sm"
          onclick="CC.clearRepresentante()">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round">
            <path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
          </svg>
          Limpiar Campos
        </button>
      </div>

    </div>
  </div>

  <div class="cc-card cc-card--collapsible cc-mt-6">
    <div class="cc-card__header cc-card__toggle"
      style="display: flex; justify-content: space-between; align-items: center;">
      <div>
        <h3>Herederos</h3>
        <p>Añada las personas con derecho sobre el patrimonio</p>
      </div>
      <svg class="cc-card__chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
        stroke-width="2">
        <polyline points="6 9 12 15 18 9" />
      </svg>
    </div>
    <div class="cc-card__body cc-card__collapse">
      <!-- Empty state (shown when no herederos) -->
      <div class="cc-empty" id="herederosEmpty">
        <div class="cc-empty__icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round">
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
            <circle cx="9" cy="7" r="4" />
            <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
            <path d="M16 3.13a4 4 0 0 1 0 7.75" />
          </svg>
        </div>
        <p>No hay herederos registrados</p>
        <button class="btn btn-secondary btn-sm" onclick="CC.openModal('heredero')">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
            stroke-linecap="round">
            <line x1="12" y1="5" x2="12" y2="19" />
            <line x1="5" y1="12" x2="19" y2="12" />
          </svg>
          Agregar Heredero
        </button>
      </div>

      <!-- Table (shown when there are herederos) -->
      <div id="herederosContent" style="display:none;">
        <div class="table-container">
          <table class="data-table">
            <thead>
              <tr>
                <th>Nombres y Apellidos</th>
                <th>Cédula</th>
                <th>Carácter</th>
                <th>Parentesco</th>
                <th>Premuerto</th>
                <th class="cc-th-action">Acción</th>
              </tr>
            </thead>
            <tbody id="herederosTableBody">
              <!-- Rendered by JS -->
            </tbody>
          </table>
        </div>
        <button class="btn btn-secondary btn-sm cc-mt" onclick="CC.openModal('heredero')">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
            stroke-linecap="round">
            <line x1="12" y1="5" x2="12" y2="19" />
            <line x1="5" y1="12" x2="19" y2="12" />
          </svg>
          Agregar Heredero
        </button>
      </div>
    </div>
  </div>

  <!-- Herederos del Premuerto -->
  <div class="cc-card cc-mt-6 cc-card--collapsible" id="card_premuertos"
    style="display:none; border-color: var(--cc-indigo-200);">
    <div class="cc-card__header cc-card__toggle"
      style="background: var(--cc-indigo-50); display: flex; justify-content: space-between; align-items: center;">
      <div>
        <h3>Herederos del Premuerto</h3>
        <p>Añada los representantes de herederos fallecidos antes que el causante</p>
      </div>
      <svg class="cc-card__chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
        stroke-width="2">
        <polyline points="6 9 12 15 18 9" />
      </svg>
    </div>
    <div class="cc-card__body cc-card__collapse">
      <!-- Empty state (shown when no herederos premuertos) -->
      <div class="cc-empty" id="herederosPremuertosEmpty">
        <div class="cc-empty__icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round">
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
            <circle cx="9" cy="7" r="4" />
            <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
            <path d="M16 3.13a4 4 0 0 1 0 7.75" />
          </svg>
        </div>
        <p>No hay herederos del premuerto registrados</p>
        <button class="btn btn-secondary btn-sm" onclick="CC.openModal('heredero_premuerto')">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
            stroke-linecap="round">
            <line x1="12" y1="5" x2="12" y2="19" />
            <line x1="5" y1="12" x2="19" y2="12" />
          </svg>
          Agregar Heredero del Premuerto
        </button>
      </div>

      <!-- Table (shown when there are herederos premuertos) -->
      <div id="herederosPremuertosContent" style="display:none;">
        <div class="table-container">
          <table class="data-table">
            <thead>
              <tr>
                <th>Nombres y Apellidos</th>
                <th>Cédula</th>
                <th>Carácter</th>
                <th>Parentesco</th>
                <th>Premuerto Relacionado</th>
                <th class="cc-th-action">Acción</th>
              </tr>
            </thead>
            <tbody id="herederosPremuertosTableBody">
              <!-- Rendered by JS -->
            </tbody>
          </table>
        </div>
        <button class="btn btn-secondary btn-sm cc-mt" onclick="CC.openModal('heredero_premuerto')">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
            stroke-linecap="round">
            <line x1="12" y1="5" x2="12" y2="19" />
            <line x1="5" y1="12" x2="19" y2="12" />
          </svg>
          Agregar Heredero del Premuerto
        </button>
      </div>
    </div>
  </div>

</div>

<!-- =============================================================== -->
<!-- STEP 2: Inventario Patrimonial                                  -->
<!-- =============================================================== -->
<div class="cc-step" id="step-2" style="display:none;">

  <!-- Tab Nav -->
  <div class="cc-tabs" id="inventarioTabs">
    <button class="cc-tab is-active" data-tab="inmuebles">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
        stroke-linecap="round">
        <rect x="4" y="2" width="16" height="20" rx="2" />
        <path d="M9 22v-4h6v4M8 6h.01M16 6h.01M12 6h.01M12 10h.01M12 14h.01M16 10h.01M16 14h.01M8 10h.01M8 14h.01" />
      </svg>
      <span>Bienes Inmuebles</span>
      <span class="cc-tab__count" id="countInmuebles">0</span>
    </button>
    <button class="cc-tab" data-tab="muebles">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
        stroke-linecap="round">
        <path
          d="M14 16H9m10 0h3v-3.15a1 1 0 0 0-.84-.99L16 11l-2.7-3.6a1 1 0 0 0-.8-.4H5.24a2 2 0 0 0-1.8 1.1l-.8 1.63A6 6 0 0 0 2 12.42V16h2" />
        <circle cx="6.5" cy="16.5" r="2.5" />
        <circle cx="16.5" cy="16.5" r="2.5" />
      </svg>
      <span>Bienes Muebles</span>
      <span class="cc-tab__count" id="countMuebles">0</span>
    </button>
    <button class="cc-tab" data-tab="pasivos">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
        stroke-linecap="round">
        <line x1="12" y1="1" x2="12" y2="23" />
        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
      </svg>
      <span>Pasivos</span>
      <span class="cc-tab__count" id="countPasivos">0</span>
    </button>
    <button class="cc-tab" data-tab="exenciones">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
        stroke-linecap="round">
        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
      </svg>
      <span>Exenciones / Exoneraciones</span>
      <span class="cc-tab__count" id="countExenciones">0</span>
    </button>
    <button class="cc-tab" data-tab="desgravamenes">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
        stroke-linecap="round">
        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
      </svg>
      <span>Desgravámenes</span>
      <span class="cc-tab__count" id="countDesgravamenes">0</span>
    </button>
    <button class="cc-tab" data-tab="litigiosos">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
        stroke-linecap="round">
        <path d="M12 2L2 7l10 5 10-5-10-5z" />
        <path d="M2 17l10 5 10-5" />
        <path d="M2 12l10 5 10-5" />
      </svg>
      <span>Bienes Litigiosos</span>
      <span class="cc-tab__count" id="countLitigiosos">0</span>
    </button>
  </div>

  <!-- TAB: Inmuebles -->
  <div class="cc-tab-panel is-active" id="panel-inmuebles">
    <div class="cc-card">
      <div class="cc-card__header">
        <div>
          <h3>Bienes Inmuebles</h3>
          <p>Propiedades inmobiliarias vinculadas a la sucesión</p>
        </div>
      </div>
      <div class="cc-card__body">
        <div class="cc-empty" id="inmueblesEmpty">
          <div class="cc-empty__icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              stroke-linecap="round">
              <rect x="4" y="2" width="16" height="20" rx="2" />
              <path d="M9 22v-4h6v4" />
            </svg>
          </div>
          <p>No hay bienes inmuebles registrados</p>
          <button class="btn btn-secondary btn-sm" onclick="CC.openModal('inmueble')">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
              stroke-linecap="round">
              <line x1="12" y1="5" x2="12" y2="19" />
              <line x1="5" y1="12" x2="19" y2="12" />
            </svg>
            Agregar Bien Inmueble
          </button>
        </div>
        <div id="inmueblesList" style="display:none;">
          <!-- Rendered by JS -->
        </div>
      </div>
    </div>
  </div>

  <!-- TAB: Muebles -->
  <div class="cc-tab-panel" id="panel-muebles" style="display:none;">
    <div class="cc-subtabs" id="muebleSubtabs">
      <!-- Generated by JS -->
    </div>
    <div class="cc-card">
      <div class="cc-card__header">
        <div>
          <h3 id="muebleSubtitle">Banco</h3>
          <p id="muebleSubdesc">Bienes muebles de tipo "Banco"</p>
        </div>
      </div>
      <div class="cc-card__body">
        <div class="cc-empty" id="mueblesEmpty">
          <div class="cc-empty__icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              stroke-linecap="round">
              <rect x="2" y="7" width="20" height="14" rx="2" ry="2" />
              <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16" />
            </svg>
          </div>
          <p id="mueblesEmptyText">No hay registros de Banco</p>
          <button class="btn btn-secondary btn-sm" onclick="CC.openModal('mueble')">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
              stroke-linecap="round">
              <line x1="12" y1="5" x2="12" y2="19" />
              <line x1="5" y1="12" x2="19" y2="12" />
            </svg>
            Agregar Bien
          </button>
        </div>
        <div id="mueblesList" style="display:none;">
          <!-- Rendered by JS -->
        </div>
      </div>
    </div>
  </div>

  <!-- TAB: Pasivos -->
  <div class="cc-tab-panel" id="panel-pasivos" style="display:none;">
    <div class="cc-card">
      <div class="cc-card__header">
        <div>
          <h3>Pasivos — Deudas</h3>
          <p>Deudas del causante deducibles del impuesto</p>
        </div>
      </div>
      <div class="cc-card__body">
        <div class="cc-empty" id="deudasEmpty">
          <div class="cc-empty__icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              stroke-linecap="round">
              <line x1="12" y1="1" x2="12" y2="23" />
              <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
            </svg>
          </div>
          <p>No hay deudas registradas</p>
          <button class="btn btn-secondary btn-sm" onclick="CC.openModal('pasivo_deuda')">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
              stroke-linecap="round">
              <line x1="12" y1="5" x2="12" y2="19" />
              <line x1="5" y1="12" x2="19" y2="12" />
            </svg>
            Agregar Deuda
          </button>
        </div>
        <div id="deudasList" style="display:none;">
          <!-- Rendered by JS -->
        </div>
      </div>
    </div>

    <div class="cc-card">
      <div class="cc-card__header">
        <div>
          <h3>Pasivos — Gastos</h3>
          <p>Gastos asociados al proceso sucesoral</p>
        </div>
      </div>
      <div class="cc-card__body">
        <div class="cc-empty" id="gastosEmpty">
          <div class="cc-empty__icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              stroke-linecap="round">
              <line x1="12" y1="1" x2="12" y2="23" />
              <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
            </svg>
          </div>
          <p>No hay gastos registrados</p>
          <button class="btn btn-secondary btn-sm" onclick="CC.openModal('pasivo_gasto')">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
              stroke-linecap="round">
              <line x1="12" y1="5" x2="12" y2="19" />
              <line x1="5" y1="12" x2="19" y2="12" />
            </svg>
            Agregar Gasto
          </button>
        </div>
        <div id="gastosList" style="display:none;">
          <!-- Rendered by JS -->
        </div>
      </div>
    </div>
  </div>

  <!-- TAB: Exenciones / Exoneraciones -->
  <div class="cc-tab-panel" id="panel-exenciones" style="display:none;">
    <div class="cc-card">
      <div class="cc-card__header">
        <div>
          <h3>Exenciones</h3>
          <p>Reducciones legales del impuesto</p>
        </div>
      </div>
      <div class="cc-card__body">
        <div class="cc-empty" id="exencionesEmpty">
          <div class="cc-empty__icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              stroke-linecap="round">
              <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
            </svg>
          </div>
          <p>No hay exenciones registradas</p>
          <button class="btn btn-secondary btn-sm" onclick="CC.openModal('exencion')">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
              stroke-linecap="round">
              <line x1="12" y1="5" x2="12" y2="19" />
              <line x1="5" y1="12" x2="19" y2="12" />
            </svg>
            Agregar Exención
          </button>
        </div>
        <div id="exencionesList" style="display:none;">
          <!-- Rendered by JS -->
        </div>
      </div>
    </div>

    <div class="cc-card">
      <div class="cc-card__header">
        <div>
          <h3>Exoneraciones</h3>
          <p>Dispensas del tributo otorgadas por decreto</p>
        </div>
      </div>
      <div class="cc-card__body">
        <div class="cc-empty" id="exoneracionesEmpty">
          <div class="cc-empty__icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              stroke-linecap="round">
              <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
            </svg>
          </div>
          <p>No hay exoneraciones registradas</p>
          <button class="btn btn-secondary btn-sm" onclick="CC.openModal('exoneracion')">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
              stroke-linecap="round">
              <line x1="12" y1="5" x2="12" y2="19" />
              <line x1="5" y1="12" x2="19" y2="12" />
            </svg>
            Agregar Exoneración
          </button>
        </div>
        <div id="exoneracionesList" style="display:none;">
          <!-- Rendered by JS -->
        </div>
      </div>
    </div>
  </div>

  <!-- TAB: Desgravámenes -->
  <div class="cc-tab-panel" id="panel-desgravamenes" style="display:none;">
    <div class="cc-card">
      <div class="cc-card__header">
        <div>
          <h3>Desgravámenes</h3>
          <p>Deducciones permitidas por Ley que se aplican al patrimonio</p>
        </div>
      </div>
      <div class="cc-card__body">
        <div class="cc-empty" id="desgravamenesEmpty">
          <div class="cc-empty__icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              stroke-linecap="round">
              <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
            </svg>
          </div>
          <p>No hay desgravámenes registrados</p>
          <small style="color: var(--cc-text-muted); display:block; margin-top:0.25rem;">Los desgravámenes aparecerán aquí automáticamente cuando el estudiante los registre en la simulación.</small>
        </div>
        <div id="desgravamenesList" style="display:none;">
          <!-- Rendered by JS -->
        </div>
      </div>
    </div>
  </div>

  <!-- TAB: Bienes Litigiosos (solo lectura) -->
  <div class="cc-tab-panel" id="panel-litigiosos" style="display:none;">
    <div class="cc-card">
      <div class="cc-card__header">
        <div>
          <h3>Bienes Litigiosos</h3>
          <p>Vista consolidada de bienes marcados como litigiosos y sus datos de tribunal</p>
        </div>
      </div>
      <div class="cc-card__body">
        <div class="cc-empty" id="litigiososEmpty">
          <div class="cc-empty__icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              stroke-linecap="round">
              <path d="M12 2L2 7l10 5 10-5-10-5z" />
              <path d="M2 17l10 5 10-5" />
              <path d="M2 12l10 5 10-5" />
            </svg>
          </div>
          <p>No hay bienes litigiosos registrados</p>
          <small style="color: var(--cc-text-muted); display:block; margin-top:0.25rem;">Los bienes litigiosos aparecerán aquí automáticamente cuando marque un bien inmueble o mueble como litigioso.</small>
        </div>
        <div id="litigiososList" style="display:none;">
          <!-- Rendered by JS -->
        </div>
      </div>
    </div>
  </div>

</div>

<!-- =============================================================== -->
<!-- STEP 3: Resumen del Caso                                        -->
<!-- =============================================================== -->
<div class="cc-step" id="step-3" style="display:none;">

  <!-- ═══ Sección 1: Información Básica ═══ -->
  <div class="cc-card">
    <div class="cc-card__header">
      <div>
        <h3>Resumen del Caso</h3>
        <p>Verificación final antes de publicar</p>
      </div>
    </div>
    <div class="cc-card__body" style="padding:0;">
      <div class="cc-info-row"><div class="cc-info-row__left"><span class="cc-info-row__icon" style="background:var(--blue-50);">📋</span><span class="cc-info-row__label">Título</span></div><span class="cc-info-row__value" id="sumTitulo">Sin título</span></div>
      <div class="cc-info-row"><div class="cc-info-row__left"><span class="cc-info-row__icon" style="background:var(--gray-100);">👤</span><span class="cc-info-row__label">Causante</span></div><span class="cc-info-row__value" id="sumCausante">Sin definir</span></div>
      <div class="cc-info-row"><div class="cc-info-row__left"><span class="cc-info-row__icon" style="background:rgba(236,253,245,0.6);">📜</span><span class="cc-info-row__label">Herencia / Sucesión</span></div><span class="cc-info-row__value" id="sumHerencia">Sin definir</span></div>
      <div class="cc-info-row"><div class="cc-info-row__left"><span class="cc-info-row__icon" style="background:rgba(254,243,199,0.6);">⚖️</span><span class="cc-info-row__label">Estado Civil / Fallecimiento</span></div><span class="cc-info-row__value" id="sumEstadoCivil">Sin definir</span></div>
      <div class="cc-info-row"><div class="cc-info-row__left"><span class="cc-info-row__icon" style="background:rgba(255,247,237,0.6);">💰</span><span class="cc-info-row__label">Unidad Tributaria</span></div><span class="cc-info-row__value" id="sumUT">Sin definir</span></div>
      <div class="cc-info-row"><div class="cc-info-row__left"><span class="cc-info-row__icon" style="background:rgba(239,246,255,0.6);">👥</span><span class="cc-info-row__label">Total de Herederos</span></div><span class="cc-info-row__value" id="sumHerederos">0</span></div>
      <div class="cc-info-row" style="border-bottom:none;"><div class="cc-info-row__left"><span class="cc-info-row__icon" style="background:rgba(237,233,254,0.6);">📄</span><span class="cc-info-row__label">Representante / Prórrogas</span></div><span class="cc-info-row__value" id="sumRepresentante">Sin representante</span></div>
    </div>
  </div>

  <!-- ═══ Sección 2: Resumen Patrimonio / Tributo (14 filas numeradas) ═══ -->
  <div class="cc-card cc-mt-6">
    <div class="cc-card__header">
      <div>
        <h3>Resumen Patrimonial y Tributo</h3>
        <p>Cálculo automático basado en los datos del caso</p>
      </div>
    </div>
    <div class="cc-card__body" style="padding:0;overflow-x:auto;">
      <table class="cc-resumen-table">
        <thead>
          <tr><th style="width:32px;">#</th><th>Concepto</th><th>Gravamen</th></tr>
        </thead>
        <tbody>
          <tr><td>1</td><td>Total Bienes Inmuebles</td><td id="resInmuebles">0,00</td></tr>
          <tr><td>2</td><td>Total Bienes Muebles</td><td id="resMuebles">0,00</td></tr>
          <tr class="cc-resumen-highlight"><td>3</td><td>Patrimonio Hereditario Bruto (1 + 2)</td><td id="resBruto">0,00</td></tr>
          <tr class="cc-resumen-highlight"><td>4</td><td>Activo Hereditario Bruto (Patrimonio Hereditario Bruto)</td><td id="resActivoBruto">0,00</td></tr>
          <tr><td>5</td><td>Desgravámenes</td><td id="resDesgravamenes">0,00</td></tr>
          <tr><td>6</td><td>Exenciones</td><td id="resExenciones">0,00</td></tr>
          <tr><td>7</td><td>Exoneraciones</td><td id="resExoneraciones">0,00</td></tr>
          <tr class="cc-resumen-highlight"><td>8</td><td>Total de Exclusiones (Desgravámenes + Exenciones + Exoneraciones)</td><td id="resTotalExclusiones">0,00</td></tr>
          <tr class="cc-resumen-separator"><td colspan="3">Patrimonio Neto Hereditario</td></tr>
          <tr><td>9</td><td>Activo Hereditario Neto (Activo Hereditario Bruto − Total de Exclusiones)</td><td id="resActivoNeto">0,00</td></tr>
          <tr><td>10</td><td>Total Pasivo</td><td id="resPasivos">0,00</td></tr>
          <tr class="cc-resumen-highlight--neto"><td>11</td><td>Patrimonio Neto Hereditario o Líquido Hereditario Gravable (Activo Neto − Total Pasivo)</td><td id="resPatrimonioNeto">0,00</td></tr>
          <tr class="cc-resumen-separator"><td colspan="3">Determinación de Tributo</td></tr>
          <tr><td>12</td><td>Impuesto Determinado según Tarifa</td><td id="resImpuestoTarifa">0,00</td></tr>
          <tr><td>13</td><td>Reducciones</td><td id="resReducciones">0,00</td></tr>
          <tr class="cc-resumen-highlight--total"><td>14</td><td>Total Impuesto a Pagar</td><td id="resTotalImpuesto">0,00</td></tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- ═══ Sección 3: Cuota Parte Hereditaria ═══ -->
  <div class="cc-card cc-mt-6">
    <div class="cc-card__header">
      <div style="display:flex;align-items:center;justify-content:space-between;width:100%;">
        <div>
          <h3>Cuota Parte Hereditaria</h3>
          <p>Patrimonio Hereditario / Total de Herederos — Impuesto Determinado = (Cuota Parte × Porcentaje) − Sustraendo</p>
        </div>
        <div style="display:flex;gap:8px;align-items:center;">
          <button type="button" class="cc-btn cc-btn--outline" id="btnAbrirCalculoManual" title="Abrir Cálculo Manual">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16v16H4z"/><path d="M8 8h2v2H8zM14 8h2v2h-2zM8 14h8v2H8z"/></svg>
            Cálculo Manual
          </button>
          <button type="button" class="cc-btn cc-btn--outline" id="btnRestablecerCalculo" title="Restablecer Cálculo Automático" disabled>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 4v6h6"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
            Restablecer Cálculo Automático
          </button>
        </div>
      </div>
    </div>
    <div class="cc-card__body" style="padding:0;overflow-x:auto;">
      <table class="cc-herederos-table">
        <thead>
          <tr>
            <th>Apellido(s) y Nombre(s)</th>
            <th class="text-center">C.I./Pasaporte</th>
            <th class="text-center">Parentesco</th>
            <th class="text-center">Grado</th>
            <th class="text-center">Premuerto</th>
            <th class="text-end">Cuota Parte (UT)</th>
            <th class="text-end">Porcentaje (%)</th>
            <th class="text-end">Sustraendo (UT)</th>
            <th class="text-end">Impuesto Det. (Bs.)</th>
            <th class="text-end">Reducción (Bs.)</th>
            <th class="text-end">Impuesto a Pagar</th>
          </tr>
        </thead>
        <tbody id="resHerederosBody">
          <tr><td colspan="11" style="text-align:center;color:var(--gray-400);padding:24px;">No hay herederos registrados</td></tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- ═══ Modal: Cálculo Manual Cuota Parte Hereditaria ═══ -->
  <div id="modalCalculoManual" style="display:none;">
    <div class="cc-cm-modal">
      <div class="cc-modal__header">
        <h3>Cálculo Manual — Cuota Parte Hereditaria</h3>
        <button type="button" class="cc-modal__close" onclick="document.getElementById('modalCalculoManual').style.display='none'">✕</button>
      </div>
      <div class="cc-modal__body">
        <!-- Floating inputs: UT + Total Impuesto -->
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;">
          <div class="cc-floating-field">
            <input type="text" id="cmUT" readonly placeholder=" ">
            <label>Unidad Tributaria Aplicada</label>
          </div>
          <div class="cc-floating-field">
            <input type="text" id="cmTotalImpuesto" readonly placeholder=" " style="text-align:right;">
            <label>Total Impuesto a Pagar</label>
          </div>
        </div>

        <!-- Tabla 1: Entrada (7 columnas, cuota + reducción editables) -->
        <div class="cc-card" style="margin-bottom:20px;">
          <div class="cc-card__header" style="padding:10px 16px;">
            <h3 style="font-size:13px;">Tabla de Entrada</h3>
          </div>
          <div class="cc-card__body" style="padding:0;overflow:visible;">
            <table class="cc-herederos-table" id="cmTablaEntrada">
              <thead>
                <tr>
                  <th>Apellido(s) y Nombre(s)</th>
                  <th class="text-center">C.I./Pasaporte</th>
                  <th class="text-center">Parentesco</th>
                  <th class="text-center">Grado</th>
                  <th class="text-center">Premuerto</th>
                  <th class="text-end">Cuota Parte (UT) <span class="cc-tooltip" data-tooltip="Patrimonio neto (UT) ÷ herederos. Si el heredero es grado 1 (ascendiente, descendiente, cónyuge o hijo adoptivo) y su cuota es ≤ 75 UT, queda exento de impuesto.">ⓘ</span></th>
                  <th class="text-end">Reducción (Bs.) <span class="cc-tooltip" data-tooltip="No puede igualar o superar el impuesto determinado, ni exceder el 50% de este, ni la proporción equitativa (impuesto total ÷ número de herederos).">ⓘ</span></th>
                </tr>
              </thead>
              <tbody id="cmEntradaBody">
                <tr><td colspan="7" style="text-align:center;color:var(--gray-400);padding:20px;">Sin herederos</td></tr>
              </tbody>
              <tfoot>
                <tr>
                  <td colspan="7" style="text-align:center;padding:12px;">
                    <button type="button" class="cc-btn cc-btn--primary" id="btnCMCalcular" disabled>Calcular</button>
                  </td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>

        <!-- Tabla 2: Resultados (11 columnas, oculta hasta Calcular) -->
        <div class="cc-card" id="cmResultadosCard" style="display:none;">
          <div class="cc-card__header" style="padding:10px 16px;">
            <h3 style="font-size:13px;">Resultado del Cálculo</h3>
          </div>
          <div class="cc-card__body" style="padding:0;overflow-x:auto;">
            <table class="cc-herederos-table" id="cmTablaResultado">
              <thead>
                <tr>
                  <th>Apellido(s) y Nombre(s)</th>
                  <th class="text-center">C.I./Pasaporte</th>
                  <th class="text-center">Parentesco</th>
                  <th class="text-center">Grado</th>
                  <th class="text-center">Premuerto</th>
                  <th class="text-end">Cuota Parte (UT)</th>
                  <th class="text-end">Porcentaje (%)</th>
                  <th class="text-end">Sustraendo (UT)</th>
                  <th class="text-end">Impuesto Det. (Bs.)</th>
                  <th class="text-end">Reducción (Bs.)</th>
                  <th class="text-end">Impuesto a Pagar</th>
                </tr>
              </thead>
              <tbody id="cmResultadoBody">
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <div class="cc-modal__footer">
        <button type="button" class="cc-btn cc-btn--outline" onclick="document.getElementById('modalCalculoManual').style.display='none'">Cancelar</button>
        <button type="button" class="cc-btn cc-btn--primary" id="btnCMAceptar" disabled>Aceptar</button>
      </div>
    </div>
  </div>

  <!-- ═══ Sección 4: Tarifa de Referencia (collapsible) ═══ -->
  <div class="cc-card cc-mt-6 cc-card--tarifa is-collapsed">
    <div class="cc-card__header" onclick="this.closest('.cc-card--tarifa').classList.toggle('is-collapsed')">
      <div style="display:flex;align-items:center;justify-content:space-between;width:100%;">
        <div>
          <h3>Tarifa de Referencia</h3>
          <p>Tabla de porcentajes y sustraendos según grado de parentesco</p>
        </div>
        <svg class="cc-card__chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
      </div>
    </div>
    <div class="cc-card__body" style="padding:0;overflow-x:auto;">
      <table class="cc-tarifa-table">
        <thead>
          <tr>
            <th>Indicación del Parentesco</th>
            <th></th>
            <th>Hasta 15 UT</th>
            <th>15,01–50 UT</th>
            <th>50,01–100 UT</th>
            <th>100,01–250 UT</th>
            <th>250,01–500 UT</th>
            <th>500,01–1000 UT</th>
            <th>1000,01–4000 UT</th>
            <th>A partir de 4000,01 UT</th>
          </tr>
        </thead>
        <tbody>
          <!-- 1° Grado -->
          <tr class="cc-tarifa-grado-1">
            <td rowspan="2">1° Ascendientes, Descendientes, Cónyuges e Hijos Adoptivos</td>
            <td>Porcentaje</td>
            <td>1%</td><td>2,5%</td><td>5%</td><td>7,50%</td><td>10%</td><td>15%</td><td>20%</td><td>25%</td>
          </tr>
          <tr class="cc-tarifa-grado-1">
            <td>Sustraendo</td>
            <td></td><td>0,23 UT</td><td>1,48 UT</td><td>3,98 UT</td><td>10,23 UT</td><td>35,23 UT</td><td>85,23 UT</td><td>285,23 UT</td>
          </tr>
          <!-- 2° Grado -->
          <tr class="cc-tarifa-grado-2">
            <td rowspan="2">2° Hermanos, Sobrinos por Derecho de Representación</td>
            <td>Porcentaje</td>
            <td>2,5%</td><td>5%</td><td>10%</td><td>15%</td><td>20%</td><td>25%</td><td>30%</td><td>40%</td>
          </tr>
          <tr class="cc-tarifa-grado-2">
            <td>Sustraendo</td>
            <td></td><td>0,38 UT</td><td>2,88 UT</td><td>7,88 UT</td><td>20,38 UT</td><td>45,38 UT</td><td>95,38 UT</td><td>495,38 UT</td>
          </tr>
          <!-- 3° Grado -->
          <tr class="cc-tarifa-grado-3">
            <td rowspan="2">3° Otros Colaterales de 3° Grado y los de 4° Grado</td>
            <td>Porcentaje</td>
            <td>6%</td><td>12,5%</td><td>20%</td><td>25%</td><td>30%</td><td>35%</td><td>40%</td><td>50%</td>
          </tr>
          <tr class="cc-tarifa-grado-3">
            <td>Sustraendo</td>
            <td></td><td>0,98 UT</td><td>4,73 UT</td><td>9,73 UT</td><td>22,23 UT</td><td>47,23 UT</td><td>97,23 UT</td><td>497,23 UT</td>
          </tr>
          <!-- 4° Grado -->
          <tr class="cc-tarifa-grado-4">
            <td rowspan="2">4° Afines, Otros Parientes y Extraños</td>
            <td>Porcentaje</td>
            <td>10%</td><td>15%</td><td>25%</td><td>30%</td><td>35%</td><td>40%</td><td>45%</td><td>55%</td>
          </tr>
          <tr class="cc-tarifa-grado-4">
            <td>Sustraendo</td>
            <td></td><td>0,75 UT</td><td>5,75 UT</td><td>10,75 UT</td><td>23,25 UT</td><td>48,25 UT</td><td>98,25 UT</td><td>498,25 UT</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

</div>

<!-- ===== GENERIC MODAL ===== -->
<div class="cc-modal-overlay" id="genericModal">
  <div class="cc-modal">
    <div class="cc-modal__header">
      <h3 id="modalTitle">Título</h3>
      <button class="cc-modal__close" onclick="CC.closeModal()">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          stroke-linecap="round">
          <line x1="18" y1="6" x2="6" y2="18" />
          <line x1="6" y1="6" x2="18" y2="18" />
        </svg>
      </button>
    </div>
    <div class="cc-modal__body" id="modalBody">
      <!-- Injected by JS -->
    </div>
    <div class="cc-modal__footer">
      <button class="btn btn-ghost" onclick="CC.closeModal()">Cancelar</button>
      <button class="btn btn-secondary btn--sm" id="modalClearBtn" style="display:none;" onclick="CC.clearModalFields()">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
          <path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
        </svg>
        Limpiar Campos
      </button>
      <button class="btn btn-primary" id="modalSaveBtn" onclick="CC.saveModal()">Agregar</button>
    </div>
  </div>
</div>

<!-- ===== BOTTOM NAV ===== -->
<div class="cc-bottomnav">
  <button class="btn btn-ghost" id="btnPrev" disabled onclick="CC.prevStep()">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
      stroke-linecap="round">
      <polyline points="15 18 9 12 15 6" />
    </svg>
    Anterior
  </button>
  <div class="cc-bottomnav__right">
    <button class="btn btn-primary" id="btnNext" onclick="CC.nextStep()">
      Siguiente
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
        stroke-linecap="round">
        <polyline points="9 18 15 12 9 6" />
      </svg>
    </button>
    <button class="btn btn-primary" id="btnPublish" onclick="CC.publish()" style="display:none;">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
        stroke-linecap="round">
        <polyline points="20 6 9 17 4 12" />
      </svg>
      Publicar Caso
    </button>
  </div>
</div>

<!-- ===== CHECKLIST OFFCANVAS PANEL ===== -->
<div class="cc-checklist-overlay" id="checklistOverlay"></div>
<div class="cc-checklist-panel" id="checklistPanel">
  <div class="cc-checklist-header">
    <h3>
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M9 11l3 3L22 4" />
        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11" />
      </svg>
      Validación de Publicación
    </h3>
    <button class="cc-checklist-close" id="btnCloseChecklist">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <line x1="18" y1="6" x2="6" y2="18"></line>
        <line x1="6" y1="6" x2="18" y2="18"></line>
      </svg>
    </button>
  </div>
  
  <div class="cc-checklist-footer" style="border-top: none; border-bottom: 1px solid var(--gray-200); padding-bottom: 12px;">
    <div style="display:flex; justify-content:space-between; font-size:12px; font-weight:600; color:var(--gray-600); margin-bottom:6px;">
      <span>Progreso de llenado</span>
      <span id="ccProgressText">0%</span>
    </div>
    <div class="cc-checklist-progress">
      <div class="cc-checklist-bar" id="ccProgressBar" style="width: 0%;"></div>
    </div>
  </div>

  <div class="cc-checklist-body" id="checklistItemsContainer">
    <!-- Renderizado por checklist.js -->
  </div>
</div>

<!-- Tooltip handler (position: fixed, ignora overflow) -->
<script>
(function(){
    var bubble = null;
    document.addEventListener('mouseenter', function(e){
        if (!e.target.classList || !e.target.classList.contains('cc-tooltip')) return;
        var text = e.target.getAttribute('data-tooltip');
        if (!text) return;

        if (!bubble) {
            bubble = document.createElement('div');
            bubble.className = 'cc-tooltip-bubble';
            document.body.appendChild(bubble);
        }
        bubble.textContent = text;

        var rect = e.target.getBoundingClientRect();
        bubble.style.top = (rect.bottom + 8) + 'px';
        bubble.style.right = (window.innerWidth - rect.right) + 'px';
        bubble.style.left = '';

        requestAnimationFrame(function(){
            bubble.classList.add('is-visible');
        });
    }, true);

    document.addEventListener('mouseleave', function(e){
        if (!e.target.classList || !e.target.classList.contains('cc-tooltip')) return;
        if (bubble) bubble.classList.remove('is-visible');
    }, true);
})();
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/logged_layout.php';
?>