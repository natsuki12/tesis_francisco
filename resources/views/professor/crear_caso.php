<?php
$pageTitle = 'Nuevo Caso Sucesoral';
$activePage = 'casos-sucesorales';
$userName = htmlspecialchars($_SESSION['user_name'] ?? 'Profesor', ENT_QUOTES, 'UTF-8');

$extraCss = '
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="' . asset('css/professor/crear_caso.css') . '">
';

$extraJs = '
<script>
  window.BASE_URL = "' . base_url() . '";
</script>
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
    <span class="cc-badge cc-badge--slate">Borrador</span>
  </div>
  <div class="cc-topbar__right">
    <button class="cc-btn cc-btn--outline" id="btnSaveDraft">
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
      <span class="cc-stepper__name">Herederos</span>
      <span class="cc-stepper__sub">Identificación de herederos</span>
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
        <circle cx="12" cy="12" r="3" />
        <path
          d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42" />
      </svg>
    </div>
    <div class="cc-stepper__label">
      <span class="cc-stepper__name">Configuración</span>
      <span class="cc-stepper__sub">Resumen y asignación</span>
    </div>
  </div>
</div>

<!-- =============================================================== -->
<!-- STEP 0: Datos del Caso y Causante                               -->
<!-- =============================================================== -->
<div class="cc-step" id="step-0">

  <!-- Datos del Caso -->
  <div class="cc-card">
    <div class="cc-card__header">
      <div>
        <h3>Datos del Caso</h3>
        <p>Información general del caso práctico</p>
      </div>
    </div>
    <div class="cc-card__body">
      <div class="cc-grid cc-grid--3">
        <div class="cc-field cc-span-2">
          <label>Título del caso <span class="req">*</span></label>
          <input type="text" data-bind="caso.titulo" placeholder="Ej: Sucesión García - Caso práctico bienes mixtos">
        </div>
        <div class="cc-field">
          <label>Modalidad <span class="req">*</span></label>
          <select data-bind="caso.modalidad">
            <option value="">Seleccione...</option>
            <option value="Practica_Libre">Práctica Libre</option>
            <option value="Evaluacion">Evaluación</option>
          </select>
        </div>
        <div class="cc-field cc-span-2">
          <label>Descripción / Narrativa del escenario</label>
          <textarea data-bind="caso.descripcion" rows="3"
            placeholder="Describa el contexto del caso para orientar al estudiante..."></textarea>
        </div>
        <div>
          <div class="cc-field">
            <label>Máx. intentos</label>
            <input type="number" data-bind="caso.max_intentos" min="0" max="99" value="0">
            <span class="cc-hint">0 = ilimitados</span>
          </div>
          <div class="cc-field cc-mt" id="fieldFechaLimite" style="display:none;">
            <label>Fecha límite</label>
            <input type="datetime-local" data-bind="caso.fecha_limite">
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Tipo de Herencia -->
  <div class="cc-card">
    <div class="cc-card__header">
      <div>
        <h3>Tipo de Herencia</h3>
        <p>Seleccione el tipo de herencia que aplicará en este caso</p>
      </div>
    </div>
    <div class="cc-card__body">
      <div class="cc-grid cc-grid--3" id="herenciaCheckboxes">
        <!-- Generated by JS -->
      </div>
    </div>
  </div>

  <!-- Datos del Causante -->
  <div class="cc-card">
    <div class="cc-card__header">
      <div>
        <h3>Datos del Causante</h3>
        <p>Persona cuya sucesión se analiza en el caso</p>
      </div>
    </div>
    <div class="cc-card__body">
      <div class="cc-grid cc-grid--4">
        <div class="cc-field">
          <label>Tipo Cédula <span class="req">*</span></label>
          <select data-bind="causante.tipo_cedula">
            <option value="">Seleccione...</option>
            <option value="V">V - Venezolano</option>
            <option value="E">E - Extranjero</option>
          </select>
        </div>
        <div class="cc-field">
          <label>Cédula <span class="req">*</span></label>
          <input type="text" data-bind="causante.cedula" placeholder="12.345.678">
        </div>
        <div class="cc-field">
          <label>Pasaporte</label>
          <input type="text" data-bind="causante.pasaporte" placeholder="Opcional">
        </div>
        <div class="cc-field">
          <label>RIF Personal</label>
          <input type="text" data-bind="causante.rif_personal" placeholder="V-12345678-9">
        </div>
        <div class="cc-field">
          <label>Nombres <span class="req">*</span></label>
          <input type="text" data-bind="causante.nombres" placeholder="Nombres del causante">
        </div>
        <div class="cc-field">
          <label>Apellidos <span class="req">*</span></label>
          <input type="text" data-bind="causante.apellidos" placeholder="Apellidos del causante">
        </div>
        <div class="cc-field">
          <label>Sexo <span class="req">*</span></label>
          <select data-bind="causante.sexo">
            <option value="">Seleccione...</option>
            <option value="M">Masculino</option>
            <option value="F">Femenino</option>
          </select>
        </div>
        <div class="cc-field">
          <label>Estado Civil <span class="req">*</span></label>
          <select data-bind="causante.estado_civil">
            <option value="">Seleccione...</option>
            <option value="Soltero">Soltero</option>
            <option value="Casado">Casado</option>
            <option value="Viudo">Viudo</option>
            <option value="Divorciado">Divorciado</option>
          </select>
        </div>
        <div class="cc-field">
          <label>Fecha de Nacimiento <span class="req">*</span></label>
          <input type="date" data-bind="causante.fecha_nacimiento">
        </div>
        <div class="cc-field">
          <label>Fecha de Fallecimiento <span class="req">*</span></label>
          <input type="date" data-bind="causante.fecha_fallecimiento">
        </div>
        <div class="cc-field">
          <label>Nacionalidad</label>
          <select data-bind="causante.nacionalidad">
            <option value="">Seleccione...</option>
            <option value="1">Venezuela</option>
            <option value="2">Colombia</option>
            <option value="3">Otro</option>
          </select>
        </div>
        <div class="cc-field">
          <label>Valor U.T. al fallecimiento</label>
          <input type="text" data-bind="causante.valor_ut" placeholder="9,00">
          <span class="cc-hint">Referencia visual</span>
        </div>
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
    <div class="cc-card__body p-0">

      <!-- Tipo de dirección -->
      <div class="cc-p-4 cc-border-b">
        <div class="cc-grid cc-grid--1">
          <div class="cc-field">
            <select data-bind="domicilio_causante.tipo_direccion" class="cc-select-lg">
              <option value="">SELECCIONAR</option>
              <option value="BODEGA, ALMACENAMIENTO, DEPÓSITO">BODEGA, ALMACENAMIENTO, DEPÓSITO</option>
              <option value="CASA MATRIZ O ESTABLECIMIENTO PRINCIPAL">CASA MATRIZ O ESTABLECIMIENTO PRINCIPAL</option>
              <option value="DIRECCIÓN DE NOTIFICACIÓN FÍSICA">DIRECCIÓN DE NOTIFICACIÓN FÍSICA</option>
              <option value="DOMICILIO FISCAL" selected>DOMICILIO FISCAL</option>
              <option value="NEGOCIO INDEPENDIENTE">NEGOCIO INDEPENDIENTE</option>
              <option value="PLANTA INDUSTRIAL O FABRICA">PLANTA INDUSTRIAL O FABRICA</option>
              <option value="SUCURSAL COMERCIAL">SUCURSAL COMERCIAL</option>
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
              <label class="cc-radio-pill"><input type="radio" name="vialidad" value="calle"
                  data-bind="domicilio_causante.vialidad"> calle</label>
              <label class="cc-radio-pill"><input type="radio" name="vialidad" value="avenida"
                  data-bind="domicilio_causante.vialidad"> avenida</label>
              <label class="cc-radio-pill"><input type="radio" name="vialidad" value="vereda"
                  data-bind="domicilio_causante.vialidad"> vereda</label>
              <label class="cc-radio-pill"><input type="radio" name="vialidad" value="carretera"
                  data-bind="domicilio_causante.vialidad"> carretera</label>
              <label class="cc-radio-pill"><input type="radio" name="vialidad" value="esquina"
                  data-bind="domicilio_causante.vialidad"> esquina</label>
              <label class="cc-radio-pill"><input type="radio" name="vialidad" value="carrera"
                  data-bind="domicilio_causante.vialidad"> carrera</label>
            </div>
          </div>
          <!-- Right: Vivienda -->
          <div class="cc-addr-th">
            <div class="cc-addr-radios cc-addr-radios--inline">
              <label class="cc-radio-pill"><input type="radio" name="tipo_vivienda" value="edificio"
                  data-bind="domicilio_causante.tipo_vivienda"> edificio</label>
              <label class="cc-radio-pill"><input type="radio" name="tipo_vivienda" value="centro_comercial"
                  data-bind="domicilio_causante.tipo_vivienda"> centro comercial</label>
              <label class="cc-radio-pill"><input type="radio" name="tipo_vivienda" value="quinta"
                  data-bind="domicilio_causante.tipo_vivienda"> quinta</label>
              <label class="cc-radio-pill"><input type="radio" name="tipo_vivienda" value="casa"
                  data-bind="domicilio_causante.tipo_vivienda"> casa</label>
              <label class="cc-radio-pill"><input type="radio" name="tipo_vivienda" value="local"
                  data-bind="domicilio_causante.tipo_vivienda"> local</label>
            </div>
          </div>
        </div>

        <!-- ROW 2: Inputs -->
        <div class="cc-addr-tr">
          <div class="cc-addr-td">
            <div class="cc-field"><input type="text" data-bind="domicilio_causante.nombre_vialidad" placeholder="">
            </div>
          </div>
          <div class="cc-addr-td">
            <div class="cc-field"><input type="text" data-bind="domicilio_causante.nombre_vivienda" placeholder="">
            </div>
          </div>
        </div>

        <!-- ROW 3: Headers with Radios -->
        <div class="cc-addr-tr">
          <!-- Left: Nro/Piso (Sub-vivienda) -->
          <div class="cc-addr-th">
            <div class="cc-addr-radios cc-addr-radios--inline">
              <label class="cc-radio-pill"><input type="radio" name="sub_vivienda" value="apartamento"
                  data-bind="domicilio_causante.sub_vivienda"> apartamento</label>
              <label class="cc-radio-pill"><input type="radio" name="sub_vivienda" value="local"
                  data-bind="domicilio_causante.sub_vivienda"> local</label>
              <label class="cc-radio-pill"><input type="radio" name="sub_vivienda" value="oficina"
                  data-bind="domicilio_causante.sub_vivienda"> oficina</label>
            </div>
          </div>
          <!-- Right: Sector -->
          <div class="cc-addr-th">
            <div class="cc-addr-radios cc-addr-radios--inline">
              <label class="cc-radio-pill"><input type="radio" name="tipo_sector" value="urbanizacion"
                  data-bind="domicilio_causante.tipo_sector"> urbanización</label>
              <label class="cc-radio-pill"><input type="radio" name="tipo_sector" value="zona"
                  data-bind="domicilio_causante.tipo_sector"> zona</label>
              <label class="cc-radio-pill"><input type="radio" name="tipo_sector" value="sector"
                  data-bind="domicilio_causante.tipo_sector"> sector</label>
              <label class="cc-radio-pill"><input type="radio" name="tipo_sector" value="conjunto_residencial"
                  data-bind="domicilio_causante.tipo_sector"> conjunto res.</label>
              <label class="cc-radio-pill"><input type="radio" name="tipo_sector" value="barrio"
                  data-bind="domicilio_causante.tipo_sector"> barrio</label>
              <label class="cc-radio-pill"><input type="radio" name="tipo_sector" value="caserio"
                  data-bind="domicilio_causante.tipo_sector"> caserío</label>
            </div>
          </div>
        </div>

        <!-- ROW 4: Inputs -->
        <div class="cc-addr-tr">
          <div class="cc-addr-td">
            <div class="cc-field"><input type="text" data-bind="domicilio_causante.nro_piso" placeholder=""></div>
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
              <select data-bind="domicilio_causante.zona_postal">
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

    </div>
  </div>

  <!-- Representante de la Sucesión (collapsible) -->
  <div class="cc-card cc-card--collapsible">
    <div class="cc-card__header cc-card__toggle">
      <div>
        <h3>Representante de la Sucesión</h3>
        <p>Persona designada como representante ante el SENIAT</p>
      </div>
      <svg class="cc-card__chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
        stroke-width="2">
        <polyline points="6 9 12 15 18 9" />
      </svg>
    </div>
    <div class="cc-card__body cc-card__collapse" style="display:none;">
      <div class="cc-grid cc-grid--4">
        <div class="cc-field">
          <label>Tipo Cédula</label>
          <select data-bind="representante.tipo_cedula">
            <option value="">Seleccione...</option>
            <option value="V">V</option>
            <option value="E">E</option>
          </select>
        </div>
        <div class="cc-field">
          <label>Cédula</label>
          <input type="text" data-bind="representante.cedula" placeholder="12.345.678">
        </div>
        <div class="cc-field">
          <label>Nombres</label>
          <input type="text" data-bind="representante.nombres" placeholder="Nombres">
        </div>
        <div class="cc-field">
          <label>Apellidos</label>
          <input type="text" data-bind="representante.apellidos" placeholder="Apellidos">
        </div>
      </div>
    </div>
  </div>

</div>

<!-- =============================================================== -->
<!-- STEP 1: Herederos                                               -->
<!-- =============================================================== -->
<div class="cc-step" id="step-1" style="display:none;">

  <div class="cc-card">
    <div class="cc-card__header">
      <div>
        <h3>Herederos y Legatarios</h3>
        <p>Personas que recibirán la herencia o legado</p>
      </div>
    </div>
    <div class="cc-card__body">
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
        <button class="cc-btn cc-btn--soft" onclick="CC.openModal('heredero')">
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
        <div class="cc-table-wrap">
          <table class="cc-table">
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
        <button class="cc-btn cc-btn--soft cc-mt" onclick="CC.openModal('heredero')">
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
          <button class="cc-btn cc-btn--soft" onclick="CC.openModal('inmueble')">
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
          <button class="cc-btn cc-btn--soft" onclick="CC.openModal('mueble')">
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
          <button class="cc-btn cc-btn--soft" onclick="CC.openModal('pasivo_deuda')">
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
          <button class="cc-btn cc-btn--soft" onclick="CC.openModal('pasivo_gasto')">
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
          <button class="cc-btn cc-btn--soft" onclick="CC.openModal('exencion')">
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
          <button class="cc-btn cc-btn--soft" onclick="CC.openModal('exoneracion')">
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

</div>

<!-- =============================================================== -->
<!-- STEP 3: Configuración y Asignación                              -->
<!-- =============================================================== -->
<div class="cc-step" id="step-3" style="display:none;">

  <!-- Resumen -->
  <div class="cc-card">
    <div class="cc-card__header">
      <div>
        <h3>Resumen del Caso</h3>
        <p>Verificación final antes de guardar</p>
      </div>
    </div>
    <div class="cc-card__body">
      <div class="cc-grid cc-grid--2">
        <div class="cc-summary-list">
          <div class="cc-summary-row"><span>Título</span><strong id="sumTitulo">Sin título</strong></div>
          <div class="cc-summary-row"><span>Modalidad</span><span id="sumModalidad">—</span></div>
          <div class="cc-summary-row"><span>Causante</span><strong id="sumCausante">Sin definir</strong></div>
          <div class="cc-summary-row"><span>Herederos</span><strong id="sumHerederos">0</strong></div>
          <div class="cc-summary-row"><span>Tipo de Herencia</span><span id="sumHerencia">Sin definir</span></div>
        </div>
        <div>
          <div class="cc-patrimonio-box">
            <p class="cc-patrimonio-title">Resumen Patrimonial</p>
            <div class="cc-patrimonio-row"><span>Bienes Inmuebles</span><strong id="sumInmuebles">Bs. 0,00</strong>
            </div>
            <div class="cc-patrimonio-row"><span>Bienes Muebles</span><strong id="sumMuebles">Bs. 0,00</strong></div>
            <div class="cc-patrimonio-row cc-patrimonio-row--border"><span class="cc-fw600">Total Activos</span><strong
                class="cc-text-blue" id="sumActivos">Bs. 0,00</strong></div>
            <div class="cc-patrimonio-row"><span>Total Pasivos</span><strong class="cc-text-red" id="sumPasivos">- Bs.
                0,00</strong></div>
            <div class="cc-patrimonio-row cc-patrimonio-row--border"><span class="cc-fw700">Patrimonio
                Neto</span><strong class="cc-text-green" id="sumNeto">Bs. 0,00</strong></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Asignar Estudiantes -->
  <div class="cc-card">
    <div class="cc-card__header">
      <div>
        <h3>Asignar Estudiantes</h3>
        <p>Seleccione los estudiantes que tendrán acceso a este caso (opcional)</p>
      </div>
    </div>
    <div class="cc-card__body">
      <div class="cc-search-box">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          stroke-linecap="round">
          <circle cx="11" cy="11" r="8" />
          <line x1="21" y1="21" x2="16.65" y2="16.65" />
        </svg>
        <input type="text" id="studentSearch" placeholder="Buscar por nombre o cédula...">
      </div>
      <div class="cc-selected-count" id="selectedCount" style="display:none;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          stroke-linecap="round">
          <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
          <circle cx="9" cy="7" r="4" />
        </svg>
        <span id="selectedCountText">0 estudiante(s) seleccionado(s)</span>
      </div>
      <div class="cc-students-grid" id="studentsGrid">
        <!-- Rendered by JS -->
      </div>
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
      <button class="cc-btn cc-btn--ghost" onclick="CC.closeModal()">Cancelar</button>
      <button class="cc-btn cc-btn--primary" id="modalSaveBtn" onclick="CC.saveModal()">Agregar</button>
    </div>
  </div>
</div>

<!-- ===== BOTTOM NAV ===== -->
<div class="cc-bottomnav">
  <button class="cc-btn cc-btn--ghost" id="btnPrev" disabled onclick="CC.prevStep()">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
      stroke-linecap="round">
      <polyline points="15 18 9 12 15 6" />
    </svg>
    Anterior
  </button>
  <div class="cc-bottomnav__right">
    <button class="cc-btn cc-btn--primary" id="btnNext" onclick="CC.nextStep()">
      Siguiente
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
        stroke-linecap="round">
        <polyline points="9 18 15 12 9 6" />
      </svg>
    </button>
    <button class="cc-btn cc-btn--success" id="btnPublish" onclick="CC.publish()" style="display:none;">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
        stroke-linecap="round">
        <polyline points="20 6 9 17 4 12" />
      </svg>
      Publicar Caso
    </button>
  </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/logged_layout.php';
?>