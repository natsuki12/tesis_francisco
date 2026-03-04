import { $, $$ } from './utils.js';
import { caseData, UIState } from './state.js';
import { renderHerederos, renderHerederosPremuertos } from './herederos.js';
import { renderInventario } from './inventario.js';
import { getCatalogs } from './catalogos.js';

const MODAL_CONFIGS = {
  heredero: {
    title: (edit) => edit !== null ? 'Editar Heredero' : 'Agregar Heredero',
    saveLabel: (edit) => edit !== null ? 'Guardar Cambios' : 'Agregar',
    wide: false,
    build: (form) => `
      <div class="cc-grid cc-grid--2">
        <div class="cc-field"><label>Nombres <span class="req">*</span></label>
          <input type="text" data-modal="nombres" value="${form.nombres || ''}" placeholder="Nombres"></div>
        <div class="cc-field"><label>Apellidos <span class="req">*</span></label>
          <input type="text" data-modal="apellidos" value="${form.apellidos || ''}" placeholder="Apellidos"></div>
        <div class="cc-field" style="grid-column: 1 / -1;"><label>TIPO DE DOCUMENTO <span class="req">*</span></label>
          <div class="cc-radio-group cc-radio-group--inline" style="display:flex;gap:1.5rem; margin-top:0.25rem;">
            <label class="cc-radio"><input type="radio" name="doc_heredero" value="Cédula" data-modal="tipo_documento" ${form.tipo_documento === 'Cédula' || !form.tipo_documento ? 'checked' : ''}> CÉDULA</label>
            <label class="cc-radio"><input type="radio" name="doc_heredero" value="RIF" data-modal="tipo_documento" ${form.tipo_documento === 'RIF' ? 'checked' : ''}> RIF</label>
          </div>
        </div>
        <div class="cc-field cc-field--doc"><label id="lblDocHer1">CÉDULA <span class="req">*</span></label>
          <div class="cc-doc-wrapper" style="display:flex; gap:0.5rem">
            <select data-modal="letra_cedula" style="width:70px;">
              <option value="V" ${form.letra_cedula === 'V' || !form.letra_cedula ? 'selected' : ''}>V</option>
              <option value="E" ${form.letra_cedula === 'E' ? 'selected' : ''}>E</option>
              <option value="J" ${form.letra_cedula === 'J' ? 'selected' : ''}>J</option>
              <option value="G" ${form.letra_cedula === 'G' ? 'selected' : ''}>G</option>
            </select>
            <input type="text" data-modal="cedula" id="inputCedHer1" value="${form.cedula || ''}" placeholder="Ej: 12345678" style="flex:1;">
          </div>
        </div>
        <div class="cc-field"><label>PASAPORTE (SÓLO EXTRANJEROS SIN CÉDULA/RIF)</label>
          <input type="text" data-modal="pasaporte" id="inputPasaHer1" value="${form.pasaporte || ''}" placeholder="Opcional"></div>
        <div class="cc-field"><label>Fecha de Nacimiento</label>
          <input type="date" data-modal="fecha_nacimiento" value="${form.fecha_nacimiento || ''}"></div>
        <div class="cc-field"><label>Sexo <span class="req">*</span></label>
          <select data-modal="sexo">
            <option value="">Seleccione...</option>
            <option value="M" ${form.sexo === 'M' ? 'selected' : ''}>Masculino</option>
            <option value="F" ${form.sexo === 'F' ? 'selected' : ''}>Femenino</option>
          </select></div>
        <div class="cc-field"><label>Estado Civil <span class="req">*</span></label>
          <select data-modal="estado_civil">
            <option value="">Seleccione...</option>
            <option value="Soltero" ${form.estado_civil === 'Soltero' ? 'selected' : ''}>Soltero/a</option>
            <option value="Casado" ${form.estado_civil === 'Casado' ? 'selected' : ''}>Casado/a</option>
            <option value="Viudo" ${form.estado_civil === 'Viudo' ? 'selected' : ''}>Viudo/a</option>
            <option value="Divorciado" ${form.estado_civil === 'Divorciado' ? 'selected' : ''}>Divorciado/a</option>
          </select></div>
        <div class="cc-field"><label>Carácter <span class="req">*</span></label>
          <select data-modal="caracter">
            <option value="HEREDERO" ${form.caracter === 'HEREDERO' ? 'selected' : ''}>Heredero</option>
            <option value="LEGATARIO" ${form.caracter === 'LEGATARIO' ? 'selected' : ''}>Legatario</option>
          </select></div>
        <div class="cc-field"><label>Parentesco <span class="req">*</span></label>
          <select data-modal="parentesco_id">
            <option value="">Seleccione...</option>
            ${getCatalogs().parentescos.map(p => `<option value="${p.parentesco_id}" ${form.parentesco_id == p.parentesco_id ? 'selected' : ''}>${p.nombre}</option>`).join('')}
          </select></div>
        <div class="cc-field"><label>Premuerto</label>
          <select data-modal="premuerto" id="modalHerederoPremuerto">
            <option value="NO" ${form.premuerto !== 'SI' ? 'selected' : ''}>No</option>
            <option value="SI" ${form.premuerto === 'SI' ? 'selected' : ''}>Sí</option>
          </select></div>
        <div class="cc-field" id="bloqueFallecimiento" style="display: ${form.premuerto === 'SI' ? 'block' : 'none'}"><label>Fecha de Fallecimiento <span class="req">*</span></label>
          <input type="date" data-modal="fecha_fallecimiento" value="${form.fecha_fallecimiento || ''}"></div>
      </div>`,
    collect: () => collectModalFields(),
    validate: (form) => {
      // Si tienen pasaporte desactiva validacion cedula
      if (form.pasaporte && !form.cedula) {
        // OK
      } else if (!form.cedula && !form.pasaporte) {
        return "Debe ingresar Cédula/RIF o Pasaporte.";
      }
      if (!form.nombres || !form.apellidos || !form.fecha_nacimiento || !form.sexo || !form.estado_civil || !form.caracter || !form.parentesco_id || !form.premuerto) {
        return "Por favor, complete todos los campos obligatorios del heredero.";
      }
      if (form.premuerto === 'SI' && !form.fecha_fallecimiento) {
        return "Debe ingresar la fecha de fallecimiento del premuerto.";
      }
      return null;
    },
    save: (form) => {
      if (UIState.editIndex !== null) { caseData.herederos[UIState.editIndex] = form; }
      else { caseData.herederos.push(form); }
      renderHerederos();
    }
  },

  heredero_premuerto: {
    title: (edit) => edit !== null ? 'Editar Heredero del Premuerto' : 'Agregar Heredero del Premuerto',
    saveLabel: (edit) => edit !== null ? 'Guardar Cambios' : 'Agregar',
    wide: false,
    build: (form) => `
      <div class="cc-grid cc-grid--2">
        <div class="cc-field"><label>Nombres <span class="req">*</span></label>
          <input type="text" data-modal="nombres" value="${form.nombres || ''}" placeholder="Nombres"></div>
        <div class="cc-field"><label>Apellidos <span class="req">*</span></label>
          <input type="text" data-modal="apellidos" value="${form.apellidos || ''}" placeholder="Apellidos"></div>
        <div class="cc-field" style="grid-column: 1 / -1;"><label>TIPO DE DOCUMENTO <span class="req">*</span></label>
          <div class="cc-radio-group cc-radio-group--inline" style="display:flex;gap:1.5rem; margin-top:0.25rem;">
            <label class="cc-radio"><input type="radio" name="doc_heredero2" value="Cédula" data-modal="tipo_documento" ${form.tipo_documento === 'Cédula' || !form.tipo_documento ? 'checked' : ''}> CÉDULA</label>
            <label class="cc-radio"><input type="radio" name="doc_heredero2" value="RIF" data-modal="tipo_documento" ${form.tipo_documento === 'RIF' ? 'checked' : ''}> RIF</label>
          </div>
        </div>
        <div class="cc-field cc-field--doc"><label id="lblDocHer2">CÉDULA <span class="req">*</span></label>
          <div class="cc-doc-wrapper" style="display:flex; gap:0.5rem">
            <select data-modal="letra_cedula" style="width:70px;">
              <option value="V" ${form.letra_cedula === 'V' || !form.letra_cedula ? 'selected' : ''}>V</option>
              <option value="E" ${form.letra_cedula === 'E' ? 'selected' : ''}>E</option>
              <option value="J" ${form.letra_cedula === 'J' ? 'selected' : ''}>J</option>
              <option value="G" ${form.letra_cedula === 'G' ? 'selected' : ''}>G</option>
            </select>
            <input type="text" data-modal="cedula" id="inputCedHer2" value="${form.cedula || ''}" placeholder="Ej: 12345678" style="flex:1;">
          </div>
        </div>
        <div class="cc-field"><label>PASAPORTE (SÓLO EXTRANJEROS SIN CÉDULA/RIF)</label>
          <input type="text" data-modal="pasaporte" id="inputPasaHer2" value="${form.pasaporte || ''}" placeholder="Opcional"></div>
        <div class="cc-field"><label>Fecha de Nacimiento</label>
          <input type="date" data-modal="fecha_nacimiento" value="${form.fecha_nacimiento || ''}"></div>
        <div class="cc-field"><label>Sexo <span class="req">*</span></label>
          <select data-modal="sexo">
            <option value="">Seleccione...</option>
            <option value="M" ${form.sexo === 'M' ? 'selected' : ''}>Masculino</option>
            <option value="F" ${form.sexo === 'F' ? 'selected' : ''}>Femenino</option>
          </select></div>
        <div class="cc-field"><label>Estado Civil <span class="req">*</span></label>
          <select data-modal="estado_civil">
            <option value="">Seleccione...</option>
            <option value="Soltero" ${form.estado_civil === 'Soltero' ? 'selected' : ''}>Soltero/a</option>
            <option value="Casado" ${form.estado_civil === 'Casado' ? 'selected' : ''}>Casado/a</option>
            <option value="Viudo" ${form.estado_civil === 'Viudo' ? 'selected' : ''}>Viudo/a</option>
            <option value="Divorciado" ${form.estado_civil === 'Divorciado' ? 'selected' : ''}>Divorciado/a</option>
          </select></div>
        <div class="cc-field"><label>Carácter <span class="req">*</span></label>
          <select data-modal="caracter">
            <option value="HEREDERO" ${form.caracter === 'HEREDERO' ? 'selected' : ''}>Heredero</option>
            <option value="LEGATARIO" ${form.caracter === 'LEGATARIO' ? 'selected' : ''}>Legatario</option>
          </select></div>
        <div class="cc-field"><label>Parentesco <span class="req">*</span></label>
          <select data-modal="parentesco_id">
            <option value="">Seleccione...</option>
            ${getCatalogs().parentescos.map(p => `<option value="${p.parentesco_id}" ${form.parentesco_id == p.parentesco_id ? 'selected' : ''}>${p.nombre}</option>`).join('')}
          </select></div>
        <div class="cc-field"><label>Premuerto</label>
          <select data-modal="premuerto" id="modalHerederoPremuerto2">
            <option value="NO" ${form.premuerto !== 'SI' ? 'selected' : ''}>No</option>
            <option value="SI" ${form.premuerto === 'SI' ? 'selected' : ''}>Sí</option>
          </select></div>
        <div class="cc-field" id="bloqueFallecimiento2" style="display: ${form.premuerto === 'SI' ? 'block' : 'none'}"><label>Fecha de Fallecimiento <span class="req">*</span></label>
          <input type="date" data-modal="fecha_fallecimiento" value="${form.fecha_fallecimiento || ''}"></div>
        <div class="cc-field"><label>Representa a: <span class="req">*</span></label>
          <select data-modal="premuerto_padre_id">
            <option value="">No aplica...</option>
            ${caseData.herederos.map((h, i) => {
      if (h.premuerto === 'SI' && i !== UIState.editIndex) {
        return `<option value="${h.cedula}" ${form.premuerto_padre_id == h.cedula ? 'selected' : ''}>${h.nombres} ${h.apellidos}</option>`;
      }
      return '';
    }).join('')}
          </select></div>
      </div>`,
    collect: () => collectModalFields(),
    validate: (form) => {
      if (form.pasaporte && !form.cedula) {
        // OK
      } else if (!form.cedula && !form.pasaporte) {
        return "Debe ingresar Cédula/RIF o Pasaporte.";
      }
      if (!form.nombres || !form.apellidos || !form.fecha_nacimiento || !form.sexo || !form.estado_civil || !form.caracter || !form.parentesco_id || !form.premuerto || !form.premuerto_padre_id) {
        return "Por favor, complete todos los campos (incluyendo a quién representa).";
      }
      if (form.premuerto === 'SI' && !form.fecha_fallecimiento) {
        return "Debe ingresar la fecha de fallecimiento del premuerto.";
      }
      return null;
    },
    save: (form) => {
      if (UIState.editIndex !== null) { caseData.herederos_premuertos[UIState.editIndex] = form; }
      else { caseData.herederos_premuertos.push(form); }
      renderHerederosPremuertos();
    }
  },

  inmueble: {
    title: () => 'Agregar Bien Inmueble',
    saveLabel: () => 'Agregar Inmueble',
    wide: true,
    build: (form) => `
      <div class="cc-field" style="margin-bottom:16px">
        <label style="margin-bottom:8px;display:block">Tipo de Bien</label>
        <div class="cc-type-grid">${getCatalogs().tiposBienInmueble.map(t =>
      `<button type="button" class="cc-type-option${form.tipo_bien_inmueble_id == t.tipo_bien_inmueble_id ? ' is-selected' : ''}" data-tipo="${t.tipo_bien_inmueble_id}">${t.nombre}</button>`
    ).join('')}</div>
      </div>
      <div class="cc-grid cc-grid--3">
        <div class="cc-field"><label>Vivienda Principal</label>
          <select data-modal="vivienda_principal">
            <option value="No" ${form.vivienda_principal !== 'Si' ? 'selected' : ''}>No</option>
            <option value="Si" ${form.vivienda_principal === 'Si' ? 'selected' : ''}>Sí</option>
          </select></div>
        <div class="cc-field"><label>Bien Litigioso</label>
          <select data-modal="bien_litigioso" id="modalBienLitigioso">
            <option value="No" ${form.bien_litigioso !== 'Si' ? 'selected' : ''}>No</option>
            <option value="Si" ${form.bien_litigioso === 'Si' ? 'selected' : ''}>Sí</option>
          </select></div>
        <div class="cc-field"><label>Porcentaje %</label>
          <input type="number" data-modal="porcentaje" min="0" max="100" value="${form.porcentaje || 100}"></div>
      </div>

      <!-- Sección 9: Bloque litigioso condicional -->
      <div id="bloquelitigioso" class="cc-conditional-block cc-mt" style="display:${form.bien_litigioso === 'Si' ? 'block' : 'none'}">
        <h4 class="cc-section-subtitle">📋 Detalle de Bien Litigioso</h4>
        <div class="cc-grid cc-grid--2">
          <div class="cc-field"><label>N° Expediente</label>
            <input type="text" data-modal="numero_expediente" value="${form.numero_expediente || ''}" placeholder="Número de expediente"></div>
          <div class="cc-field"><label>Tribunal de la Causa</label>
            <input type="text" data-modal="tribunal_causa" value="${form.tribunal_causa || ''}" placeholder="Nombre del tribunal"></div>
          <div class="cc-field"><label>Partes en Juicio</label>
            <input type="text" data-modal="partes_juicio" value="${form.partes_juicio || ''}" placeholder="Partes involucradas" maxlength="255"></div>
          <div class="cc-field"><label>Estado del Juicio</label>
            <input type="text" data-modal="estado_juicio" value="${form.estado_juicio || ''}" placeholder="Estado actual"></div>
        </div>
      </div>

      <div class="cc-field cc-mt"><label>Descripción</label>
        <textarea data-modal="descripcion" rows="2" placeholder="Características del bien...">${form.descripcion || ''}</textarea></div>
      <div class="cc-grid cc-grid--3 cc-mt">
        <div class="cc-field"><label>Superficie Construida (m²)</label>
          <input type="text" data-modal="superficie_construida" value="${form.superficie_construida || ''}"></div>
        <div class="cc-field"><label>Superficie No Construida (m²)</label>
          <input type="text" data-modal="superficie_no_construida" value="${form.superficie_no_construida || ''}"></div>
        <div class="cc-field"><label>Área Superficie (m²)</label>
          <input type="text" data-modal="area_superficie" value="${form.area_superficie || ''}"></div>
      </div>
      <div class="cc-field cc-mt"><label>Dirección del inmueble</label>
        <textarea data-modal="direccion" rows="2" placeholder="Dirección completa...">${form.direccion || ''}</textarea></div>

      <!-- Sección 9: Datos Registrales -->
      <h4 class="cc-section-subtitle cc-mt">📑 Datos Registrales</h4>
      <div class="cc-field"><label>Linderos</label>
        <textarea data-modal="linderos" rows="2" placeholder="Descripción de los linderos...">${form.linderos || ''}</textarea></div>
      <div class="cc-grid cc-grid--3 cc-mt">
        <div class="cc-field"><label>Oficina de Registro</label>
          <input type="text" data-modal="oficina_registro" value="${form.oficina_registro || ''}"></div>
        <div class="cc-field"><label>N° Registro</label>
          <input type="text" data-modal="nro_registro" value="${form.nro_registro || ''}"></div>
        <div class="cc-field"><label>Libro</label>
          <input type="text" data-modal="libro" value="${form.libro || ''}"></div>
        <div class="cc-field"><label>Protocolo</label>
          <input type="text" data-modal="protocolo" value="${form.protocolo || ''}"></div>
        <div class="cc-field"><label>Fecha de Registro</label>
          <input type="date" data-modal="fecha_registro" value="${form.fecha_registro || ''}"></div>
        <div class="cc-field"><label>Trimestre</label>
          <input type="text" data-modal="trimestre" value="${form.trimestre || ''}"></div>
        <div class="cc-field"><label>Asiento Registral</label>
          <input type="text" data-modal="asiento_registral" value="${form.asiento_registral || ''}"></div>
        <div class="cc-field"><label>Matrícula</label>
          <input type="text" data-modal="matricula" value="${form.matricula || ''}"></div>
        <div class="cc-field"><label>Folio Real / Año</label>
          <input type="text" data-modal="folio_real_anio" value="${form.folio_real_anio || ''}"></div>
      </div>

      <div class="cc-grid cc-grid--3 cc-mt">
        <div class="cc-field"><label>Valor Original (Bs.)</label>
          <input type="number" step="0.01" data-modal="valor_original" value="${form.valor_original || ''}"></div>
        <div class="cc-field"><label>Valor Declarado (Bs.) <span class="req">*</span></label>
          <input type="number" step="0.01" data-modal="valor_declarado" value="${form.valor_declarado || ''}"></div>
      </div>`,
    collect: () => {
      const f = collectModalFields();
      // Get selected tipo from type grid
      const sel = $('#modalBody .cc-type-option.is-selected');
      if (sel) f.tipo_bien_inmueble_id = sel.dataset.tipo;
      // Si no es litigioso, limpiar campos litigiosos
      if (f.bien_litigioso !== 'Si') {
        delete f.numero_expediente;
        delete f.tribunal_causa;
        delete f.partes_juicio;
        delete f.estado_juicio;
      }
      return f;
    },
    save: (form) => {
      caseData.bienes_inmuebles.push(form);
      renderInventario();
    }
  },

  mueble: {
    title: () => `Agregar — ${getCatalogs().categoriasBienMueble.find(c => c.categoria_bien_mueble_id == UIState.currentSubTab)?.nombre || ''}`,
    saveLabel: () => 'Agregar',
    wide: false,
    build: (form) => {
      const cat = getCatalogs().categoriasBienMueble.find(c => c.categoria_bien_mueble_id == UIState.currentSubTab);
      const nameKey = cat ? cat.nombre.toLowerCase() : '';
      const tipos = getCatalogs().tiposBienMueble[UIState.currentSubTab] || [];

      let extraFields = '';

      if (nameKey.includes('banco')) {
        extraFields = `
              <div class="cc-field"><label>Banco <span class="req">*</span></label>
                <select data-modal="banco_id">
                  <option value="">Seleccione...</option>
                  ${getCatalogs().bancos.map(b => `<option value="${b.banco_id}" ${form.banco_id == b.banco_id ? 'selected' : ''}>${b.nombre}</option>`).join('')}
                </select></div>
              <div class="cc-field"><label>N° Cuenta <span class="req">*</span></label>
                <input type="text" data-modal="numero_cuenta" value="${form.numero_cuenta || ''}"></div>
            `;
      } else if (nameKey.includes('seguro')) {
        extraFields = `
              <div class="cc-field"><label>Aseguradora (Empresa) <span class="req">*</span></label>
                <select data-modal="empresa_id">
                  <option value="">Seleccione...</option>
                  ${getCatalogs().empresas.map(e => `<option value="${e.empresa_id}" ${form.empresa_id == e.empresa_id ? 'selected' : ''}>${e.nombre}</option>`).join('')}
                </select></div>
              <div class="cc-field"><label>N° Póliza/Prima <span class="req">*</span></label>
                <input type="text" data-modal="numero_prima" value="${form.numero_prima || ''}"></div>
            `;
      } else if (nameKey.includes('transporte')) {
        extraFields = `
              <div class="cc-field"><label>Marca <span class="req">*</span></label>
                <input type="text" data-modal="marca" value="${form.marca || ''}"></div>
              <div class="cc-field"><label>Modelo <span class="req">*</span></label>
                <input type="text" data-modal="modelo" value="${form.modelo || ''}"></div>
              <div class="cc-field"><label>Año <span class="req">*</span></label>
                <input type="number" data-modal="anio" value="${form.anio || ''}" min="1900"></div>
              <div class="cc-field"><label>Serial / Placa <span class="req">*</span></label>
                <input type="text" data-modal="serial_placa" value="${form.serial_placa || ''}"></div>
            `;
      } else if (nameKey.includes('acciones') || nameKey.includes('caja de ahorro')) {
        extraFields = `
              <div class="cc-field cc-span-2"><label>Empresa <span class="req">*</span></label>
                <select data-modal="empresa_id">
                  <option value="">Seleccione...</option>
                  ${getCatalogs().empresas.map(e => `<option value="${e.empresa_id}" ${form.empresa_id == e.empresa_id ? 'selected' : ''}>${e.nombre}</option>`).join('')}
                </select></div>
            `;
      } else if (nameKey.includes('bonos')) {
        extraFields = `
              <div class="cc-field cc-span-2"><label>Tipo de Bonos <span class="req">*</span></label>
                <input type="text" data-modal="tipo_bonos" value="${form.tipo_bonos || ''}"></div>
              <div class="cc-field"><label>N° Bonos <span class="req">*</span></label>
                <input type="number" data-modal="numero_bonos" value="${form.numero_bonos || ''}"></div>
              <div class="cc-field"><label>N° Serie <span class="req">*</span></label>
                <input type="text" data-modal="numero_serie" value="${form.numero_serie || ''}"></div>
            `;
      } else if (nameKey.includes('cobrar')) {
        extraFields = `
              <div class="cc-field"><label>RIF/Cédula del Deudor <span class="req">*</span></label>
                <input type="text" data-modal="rif_cedula" value="${form.rif_cedula || ''}"></div>
              <div class="cc-field"><label>Nombres / Apellidos del Deudor <span class="req">*</span></label>
                <input type="text" data-modal="apellidos_nombres" value="${form.apellidos_nombres || ''}"></div>
            `;
      } else if (nameKey.includes('compra')) {
        extraFields = `
              <div class="cc-field cc-span-2"><label>Nombre del Oferente <span class="req">*</span></label>
                <input type="text" data-modal="nombre_oferente" value="${form.nombre_oferente || ''}"></div>
            `;
      } else if (nameKey.includes('prestaciones')) {
        extraFields = `
              <div class="cc-field cc-span-2"><label>Empresa Empleadora <span class="req">*</span></label>
                <select data-modal="empresa_id">
                  <option value="">Seleccione...</option>
                  ${getCatalogs().empresas.map(e => `<option value="${e.empresa_id}" ${form.empresa_id == e.empresa_id ? 'selected' : ''}>${e.nombre}</option>`).join('')}
                </select></div>
              <div class="cc-field"><label>¿Posee Banco? <span class="req">*</span></label>
                <select data-modal="posee_banco" id="modalPoseeBanco">
                  <option value="NO" ${form.posee_banco !== 'SI' ? 'selected' : ''}>No</option>
                  <option value="SI" ${form.posee_banco === 'SI' ? 'selected' : ''}>Sí</option>
                </select></div>
              <div class="cc-field"></div>
              <div id="prestacionesBancoBlock" class="cc-grid cc-grid--2 cc-span-2" style="display:${form.posee_banco === 'SI' ? 'grid' : 'none'}; width:100%">
                  <div class="cc-field"><label>Banco</label>
                    <select data-modal="banco_id">
                      <option value="">Seleccione banco...</option>
                      ${getCatalogs().bancos.map(b => `<option value="${b.banco_id}" ${form.banco_id == b.banco_id ? 'selected' : ''}>${b.nombre}</option>`).join('')}
                    </select></div>
                  <div class="cc-field"><label>N° Cuenta</label>
                    <input type="text" data-modal="numero_cuenta" value="${form.numero_cuenta || ''}"></div>
              </div>
            `;
      } else if (nameKey.includes('semovientes')) {
        extraFields = `
              <div class="cc-field"><label>Tipo Semoviente <span class="req">*</span></label>
                <select data-modal="tipo_semoviente_id">
                  <option value="">Seleccione...</option>
                  ${getCatalogs().tiposSemoviente.map(s => `<option value="${s.tipo_semoviente_id}" ${form.tipo_semoviente_id == s.tipo_semoviente_id ? 'selected' : ''}>${s.nombre}</option>`).join('')}
                </select></div>
              <div class="cc-field"><label>Cantidad <span class="req">*</span></label>
                <input type="number" data-modal="cantidad" value="${form.cantidad || ''}"></div>
            `;
      }

      const selectsTipo = tipos.length > 0 ? `
            <div class="cc-field cc-span-2"><label>Tipo Específico de Bien <span class="req">*</span></label>
            <select data-modal="tipo_bien_mueble_id">
                <option value="">Seleccione...</option>
                ${tipos.map(t => `<option value="${t.tipo_bien_mueble_id}" ${form.tipo_bien_mueble_id == t.tipo_bien_mueble_id ? 'selected' : ''}>${t.nombre}</option>`).join('')}
            </select></div>` : ``;

      // Bloque litigioso
      const bloqueLitigiosoHTML = `
            <div id="bloquelitigiosoMueble" class="cc-conditional-block cc-span-2 cc-mt" style="display:${form.bien_litigioso === 'Si' ? 'block' : 'none'}">
                <h4 class="cc-section-subtitle">📋 Detalle de Bien Litigioso</h4>
                <div class="cc-grid cc-grid--2">
                <div class="cc-field"><label>N° Expediente</label>
                    <input type="text" data-modal="numero_expediente" value="${form.numero_expediente || ''}" placeholder="Número de expediente"></div>
                <div class="cc-field"><label>Tribunal de la Causa</label>
                    <input type="text" data-modal="tribunal_causa" value="${form.tribunal_causa || ''}" placeholder="Nombre del tribunal"></div>
                <div class="cc-field"><label>Partes en Juicio</label>
                    <input type="text" data-modal="partes_juicio" value="${form.partes_juicio || ''}" placeholder="Partes involucradas" maxlength="255"></div>
                <div class="cc-field"><label>Estado del Juicio</label>
                    <input type="text" data-modal="estado_juicio" value="${form.estado_juicio || ''}" placeholder="Estado actual"></div>
                </div>
            </div>`;

      return `
      <div class="cc-grid cc-grid--2">
        <div class="cc-field"><label>Bien Litigioso</label>
          <select data-modal="bien_litigioso" id="modalBienLitigiosoMueble">
            <option value="No" ${form.bien_litigioso !== 'Si' ? 'selected' : ''}>No</option>
            <option value="Si" ${form.bien_litigioso === 'Si' ? 'selected' : ''}>Sí</option>
          </select></div>
        <div class="cc-field"><label>Porcentaje %</label>
          <input type="number" data-modal="porcentaje" min="0" max="100" value="${form.porcentaje || 100}"></div>
        
        ${selectsTipo}
        ${extraFields}

        ${bloqueLitigiosoHTML}

        <div class="cc-field cc-span-2"><label>Descripción</label>
          <textarea data-modal="descripcion" placeholder="Descripción del bien mueble...">${form.descripcion || ''}</textarea></div>
        <div class="cc-field cc-span-2"><label>Valor Declarado (Bs.) <span class="req">*</span></label>
          <input type="number" step="0.01" data-modal="valor_declarado" placeholder="0.00" value="${form.valor_declarado || ''}"></div>
      </div>`;
    },
    collect: () => {
      const form = collectModalFields();
      if (form.bien_litigioso !== 'Si') {
        delete form.numero_expediente;
        delete form.tribunal_causa;
        delete form.partes_juicio;
        delete form.estado_juicio;
      }
      return form;
    },
    save: (form) => {
      if (!caseData.bienes_muebles[UIState.currentSubTab]) caseData.bienes_muebles[UIState.currentSubTab] = [];
      caseData.bienes_muebles[UIState.currentSubTab].push(form);
      renderInventario();
    }
  },

  pasivo_deuda: {
    title: () => 'Agregar Deuda',
    saveLabel: () => 'Agregar',
    wide: false,
    build: (form) => {
      const catalogs = getCatalogs();
      const showBanco = catalogs.tiposPasivoDeuda.find(t => t.tipo_pasivo_deuda_id == form.tipo_pasivo_deuda_id)?.nombre.toLowerCase().includes('tarjeta');
      return `
      <div class="cc-grid cc-grid--2">
        <div class="cc-field cc-span-2"><label>Tipo de Deuda <span class="req">*</span></label>
          <select data-modal="tipo_pasivo_deuda_id" id="selectPasivoDeuda">
            <option value="">Seleccione...</option>
            ${catalogs.tiposPasivoDeuda.map(t => `<option value="${t.tipo_pasivo_deuda_id}" ${form.tipo_pasivo_deuda_id == t.tipo_pasivo_deuda_id ? 'selected' : ''}>${t.nombre}</option>`).join('')}
          </select></div>
        
        <div id="deudaBancoBlock" class="cc-grid cc-grid--2 cc-span-2 cc-mt" style="display:${showBanco ? 'grid' : 'none'}; width:100%">
            <div class="cc-field"><label>Banco</label>
              <select data-modal="banco_id">
                <option value="">Seleccione banco...</option>
                ${catalogs.bancos.map(b => `<option value="${b.banco_id}" ${form.banco_id == b.banco_id ? 'selected' : ''}>${b.nombre}</option>`).join('')}
              </select></div>
            <div class="cc-field"><label>N° TDC</label>
              <input type="text" data-modal="numero_tdc" value="${form.numero_tdc || ''}"></div>
        </div>

        <div class="cc-field"><label>Porcentaje %</label>
          <input type="number" data-modal="porcentaje" min="0" max="100" value="${form.porcentaje || 100}"></div>
        <div class="cc-field cc-span-2"><label>Descripción</label>
          <textarea data-modal="descripcion" placeholder="Descripción de la deuda...">${form.descripcion || ''}</textarea></div>
        <div class="cc-field cc-span-2"><label>Valor Declarado (Bs.) <span class="req">*</span></label>
          <input type="number" step="0.01" data-modal="valor_declarado" value="${form.valor_declarado || ''}"></div>
      </div>`;
    },
    collect: () => collectModalFields(),
    save: (form) => { caseData.pasivos_deuda.push(form); renderInventario(); }
  },

  pasivo_gasto: {
    title: () => 'Agregar Gasto',
    saveLabel: () => 'Agregar',
    wide: false,
    build: (form) => `
      <div class="cc-grid cc-grid--2">
        <div class="cc-field"><label>Tipo de Gasto <span class="req">*</span></label>
          <select data-modal="tipo_pasivo_gasto_id">
            <option value="">Seleccione...</option>
            ${getCatalogs().tiposPasivoGasto.map(t => `<option value="${t.tipo_pasivo_gasto_id}" ${form.tipo_pasivo_gasto_id == t.tipo_pasivo_gasto_id ? 'selected' : ''}>${t.nombre}</option>`).join('')}
          </select></div>
        <div class="cc-field"><label>Porcentaje %</label>
          <input type="number" data-modal="porcentaje" min="0" max="100" value="${form.porcentaje || 100}"></div>
        <div class="cc-field cc-span-2"><label>Descripción</label>
          <textarea data-modal="descripcion" placeholder="Motivo del gasto...">${form.descripcion || ''}</textarea></div>
        <div class="cc-field cc-span-2"><label>Valor Declarado (Bs.) <span class="req">*</span></label>
          <input type="number" step="0.01" data-modal="valor_declarado" value="${form.valor_declarado || ''}"></div>
      </div>`,
    collect: () => collectModalFields(),
    save: (form) => { caseData.pasivos_gastos.push(form); renderInventario(); }
  },

  exencion: {
    title: () => 'Agregar Exención',
    saveLabel: () => 'Agregar',
    wide: false,
    build: (form) => `
      <div class="cc-field"><label>Tipo de Exención <span class="req">*</span></label>
        <input type="text" data-modal="tipo_exencion" placeholder="Tipo de exención" value="${form.tipo_exencion || ''}"></div>
      <div class="cc-field cc-mt"><label>Descripción</label>
        <textarea data-modal="descripcion" placeholder="Descripción...">${form.descripcion || ''}</textarea></div>
      <div class="cc-field cc-mt"><label>Valor Declarado (Bs.) <span class="req">*</span></label>
        <input type="number" step="0.01" data-modal="valor_declarado" value="${form.valor_declarado || ''}"></div>`,
    collect: () => collectModalFields(),
    save: (form) => { caseData.exenciones.push(form); renderInventario(); }
  },

  exoneracion: {
    title: () => 'Agregar Exoneración',
    saveLabel: () => 'Agregar',
    wide: false,
    build: (form) => `
      <div class="cc-field"><label>Tipo de Exoneración <span class="req">*</span></label>
        <input type="text" data-modal="tipo_exoneracion" placeholder="Tipo de exoneración" value="${form.tipo_exoneracion || ''}"></div>
      <div class="cc-field cc-mt"><label>Descripción</label>
        <textarea data-modal="descripcion" placeholder="Descripción...">${form.descripcion || ''}</textarea></div>
      <div class="cc-field cc-mt"><label>Valor Declarado (Bs.) <span class="req">*</span></label>
        <input type="number" step="0.01" data-modal="valor_declarado" value="${form.valor_declarado || ''}"></div>`,
    collect: () => collectModalFields(),
    save: (form) => { caseData.exoneraciones.push(form); renderInventario(); }
  },

  // ── Sección 13: Modal de Prórrogas ──
  prorroga: {
    title: () => 'Agregar Prórroga',
    saveLabel: () => 'Agregar',
    wide: false,
    build: (form) => `
      <div class="cc-grid cc-grid--2">
        <div class="cc-field"><label>Fecha de Solicitud <span class="req">*</span></label>
          <input type="date" data-modal="fecha_solicitud" value="${form.fecha_solicitud || ''}"></div>
        <div class="cc-field"><label>N° Resolución</label>
          <input type="text" data-modal="nro_resolucion" value="${form.nro_resolucion || ''}" placeholder="Número de resolución" maxlength="50"></div>
        <div class="cc-field"><label>Fecha Resolución</label>
          <input type="date" data-modal="fecha_resolucion" value="${form.fecha_resolucion || ''}"></div>
        <div class="cc-field"><label>Plazo Otorgado (días)</label>
          <input type="number" data-modal="plazo_otorgado_dias" min="0" value="${form.plazo_otorgado_dias || ''}"></div>
        <div class="cc-field"><label>Fecha de Vencimiento</label>
          <input type="date" data-modal="fecha_vencimiento" value="${form.fecha_vencimiento || ''}"></div>
      </div>`,
    collect: () => collectModalFields(),
    save: (form) => { caseData.prorrogas.push(form); renderInventario(); }
  }
};

function collectModalFields() {
  const form = {};
  $$('#modalBody [data-modal]').forEach(el => {
    form[el.dataset.modal] = el.value;
  });
  return form;
}

export function openModal(type, editIdx) {
  UIState.currentModalType = type;
  UIState.editIndex = (editIdx !== undefined && editIdx !== null) ? editIdx : null;

  const config = MODAL_CONFIGS[type];
  if (!config) return;

  const overlay = $('#genericModal');
  const modal = overlay.querySelector('.cc-modal');
  const titleEl = $('#modalTitle');
  const bodyEl = $('#modalBody');
  const saveBtn = $('#modalSaveBtn');

  // Prepare form data
  let formData = {};
  if (type === 'heredero' && UIState.editIndex !== null) {
    formData = { ...caseData.herederos[UIState.editIndex] };
  } else if (type === 'heredero') {
    formData = { tipo_cedula: 'V', caracter: 'HEREDERO', premuerto: 'NO', parentesco_id: '', sexo: '', estado_civil: '' };
  } else if (type === 'inmueble') {
    formData = { tipo_bien_inmueble_id: '', vivienda_principal: 'No', bien_litigioso: 'No', porcentaje: '100' };
  } else if (type === 'mueble') {
    formData = { porcentaje: '100', bien_litigioso: 'No' };
  } else if (type === 'pasivo_deuda') {
    formData = { porcentaje: '100' };
  } else if (type === 'pasivo_gasto') {
    formData = { porcentaje: '100' };
  } else if (type === 'prorroga') {
    formData = {};
  }

  titleEl.textContent = config.title(UIState.editIndex);
  saveBtn.textContent = config.saveLabel(UIState.editIndex);
  bodyEl.innerHTML = config.build(formData);

  // Wide modal
  modal.classList.toggle('cc-modal--wide', !!config.wide);

  // Bind tipo selectors for inmueble
  if (type === 'inmueble') {
    bodyEl.querySelectorAll('.cc-type-option').forEach(btn => {
      btn.addEventListener('click', () => {
        bodyEl.querySelectorAll('.cc-type-option').forEach(b => b.classList.remove('is-selected'));
        btn.classList.add('is-selected');
      });
    });

    // Toggle bloque litigioso condicional
    const litigiosoSelect = bodyEl.querySelector('#modalBienLitigioso');
    const bloqueLitigioso = bodyEl.querySelector('#bloquelitigioso');
    if (litigiosoSelect && bloqueLitigioso) {
      litigiosoSelect.addEventListener('change', () => {
        bloqueLitigioso.style.display = litigiosoSelect.value === 'Si' ? 'block' : 'none';
      });
    }
  }

  if (type === 'heredero' || type === 'heredero_premuerto') {
    const premuertoSelect = bodyEl.querySelector('select[data-modal="premuerto"]');
    const bloqueFallecimiento = bodyEl.querySelector(type === 'heredero' ? '#bloqueFallecimiento' : '#bloqueFallecimiento2');
    if (premuertoSelect && bloqueFallecimiento) {
      premuertoSelect.addEventListener('change', () => {
        bloqueFallecimiento.style.display = premuertoSelect.value === 'SI' ? 'block' : 'none';
      });
    }

    const radiosDoc = bodyEl.querySelectorAll('input[data-modal="tipo_documento"]');
    const lblDoc = bodyEl.querySelector('.cc-field--doc label');
    const inputCed = bodyEl.querySelector('[data-modal="cedula"]');
    const inputPasa = bodyEl.querySelector('[data-modal="pasaporte"]');
    const selectLetra = bodyEl.querySelector('[data-modal="letra_cedula"]');

    if (radiosDoc.length > 0 && inputCed && inputPasa) {
      radiosDoc.forEach(r => r.addEventListener('change', (e) => {
        if (e.target.checked && lblDoc) {
          lblDoc.innerHTML = e.target.value === 'RIF' ? 'RIF <span class="req">*</span>' : 'CÉDULA <span class="req">*</span>';
        }
      }));

      const handleDocInput = () => {
        if (inputPasa.value.trim() !== '') {
          inputCed.disabled = true;
          selectLetra.disabled = true;
          radiosDoc.forEach(r => r.disabled = true);
        } else if (inputCed.value.trim() !== '') {
          inputPasa.disabled = true;
        } else {
          inputCed.disabled = false;
          selectLetra.disabled = false;
          radiosDoc.forEach(r => r.disabled = false);
          inputPasa.disabled = false;
        }
      };

      inputCed.addEventListener('input', handleDocInput);
      inputPasa.addEventListener('input', handleDocInput);
      handleDocInput();

      if (lblDoc) {
        const checkedRadio = Array.from(radiosDoc).find(r => r.checked);
        if (checkedRadio) lblDoc.innerHTML = checkedRadio.value === 'RIF' ? 'RIF <span class="req">*</span>' : 'CÉDULA <span class="req">*</span>';
      }
    }
  }

  if (type === 'pasivo_deuda') {
    const selectPasivoDeuda = bodyEl.querySelector('#selectPasivoDeuda');
    const deudaBancoBlock = bodyEl.querySelector('#deudaBancoBlock');
    if (selectPasivoDeuda && deudaBancoBlock) {
      selectPasivoDeuda.addEventListener('change', (e) => {
        const tipoId = e.target.value;
        const cats = getCatalogs().tiposPasivoDeuda;
        const tipoAct = cats.find(t => t.tipo_pasivo_deuda_id == tipoId);
        if (tipoAct && tipoAct.nombre.toLowerCase().includes('tarjeta')) {
          deudaBancoBlock.style.display = 'grid';
        } else {
          deudaBancoBlock.style.display = 'none';
        }
      });
    }
  }

  if (type === 'mueble') {
    const litigiosoSelect = bodyEl.querySelector('#modalBienLitigiosoMueble');
    const bloqueLitigioso = bodyEl.querySelector('#bloquelitigiosoMueble');
    if (litigiosoSelect && bloqueLitigioso) {
      litigiosoSelect.addEventListener('change', () => {
        bloqueLitigioso.style.display = litigiosoSelect.value === 'Si' ? 'block' : 'none';
      });
    }

    const modalPoseeBanco = bodyEl.querySelector('#modalPoseeBanco');
    const prestacionesBancoBlock = bodyEl.querySelector('#prestacionesBancoBlock');
    if (modalPoseeBanco && prestacionesBancoBlock) {
      modalPoseeBanco.addEventListener('change', () => {
        prestacionesBancoBlock.style.display = modalPoseeBanco.value === 'SI' ? 'grid' : 'none';
      });
    }
  }

  overlay.classList.add('is-open');
}

export function closeModal() {
  $('#genericModal').classList.remove('is-open');
  UIState.currentModalType = null;
  UIState.editIndex = null;
}

export function saveModal() {
  const config = MODAL_CONFIGS[UIState.currentModalType];
  if (!config) return;
  const form = config.collect();

  if (config.validate) {
    const error = config.validate(form);
    if (error) {
      alert(error);
      return;
    }
  }

  config.save(form);
  closeModal();
}

export function removeItem(collection, index) {
  caseData[collection].splice(index, 1);
  if (collection === 'herederos') renderHerederos();
  else renderInventario();
}

export function removeMueble(category, index) {
  if (caseData.bienes_muebles[category]) {
    caseData.bienes_muebles[category].splice(index, 1);
  }
  renderInventario();
}
