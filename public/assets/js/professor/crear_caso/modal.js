import { $, $$, showToast } from './utils.js';
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
      if (form.cedula && !/^\d{6,10}$/.test(form.cedula)) {
        return "La cédula debe contener solo números (entre 6 y 10 dígitos).";
      }
      // Cédula compuesta: letra + número
      const fullCed = (form.letra_cedula || '') + (form.cedula || '');
      // Cédula compuesta no puede ser igual a la del causante
      if (form.cedula && caseData.causante.cedula) {
        const causanteCed = (caseData.causante.tipo_cedula || '') + caseData.causante.cedula;
        if (fullCed === causanteCed) return "La cédula del heredero no puede ser igual a la del causante.";
      }
      // Cédula compuesta no puede estar repetida entre herederos
      if (form.cedula) {
        const dupH = caseData.herederos.some((h, i) => i !== UIState.editIndex && (h.letra_cedula || '') + (h.cedula || '') === fullCed);
        if (dupH) return "Ya existe un heredero con esa cédula.";
        const dupHP = caseData.herederos_premuertos.some(h => (h.letra_cedula || '') + (h.cedula || '') === fullCed);
        if (dupHP) return "Ya existe un heredero del premuerto con esa cédula.";
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
      if (h.premuerto === 'SI') {
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
      if (form.cedula && !/^\d{6,10}$/.test(form.cedula)) {
        return "La cédula debe contener solo números (entre 6 y 10 dígitos).";
      }
      // Cédula compuesta: letra + número
      const fullCed = (form.letra_cedula || '') + (form.cedula || '');
      // Cédula compuesta no puede ser igual a la del causante
      if (form.cedula && caseData.causante.cedula) {
        const causanteCed = (caseData.causante.tipo_cedula || '') + caseData.causante.cedula;
        if (fullCed === causanteCed) return "La cédula del heredero del premuerto no puede ser igual a la del causante.";
      }
      // Cédula compuesta no puede estar repetida entre herederos/premuertos
      if (form.cedula) {
        const dupH = caseData.herederos.some(h => (h.letra_cedula || '') + (h.cedula || '') === fullCed);
        if (dupH) return "Ya existe un heredero con esa cédula.";
        const dupHP = caseData.herederos_premuertos.some((h, i) => i !== UIState.editIndex && (h.letra_cedula || '') + (h.cedula || '') === fullCed);
        if (dupHP) return "Ya existe un heredero del premuerto con esa cédula.";
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
    title: (edit) => edit !== null ? 'Editar Bien Inmueble' : 'Agregar Bien Inmueble',
    saveLabel: (edit) => edit !== null ? 'Guardar Cambios' : 'Agregar Inmueble',
    wide: true,
    build: (form) => `
      <div class="cc-field" style="margin-bottom:16px">
        <label style="margin-bottom:8px;display:block">Tipo de Bien</label>
        <div class="cc-grid cc-grid--4 cc-grid--compact" id="inmuebleTipoCheckboxes">
          ${(() => { return ''; })()}
          ${getCatalogs().tiposBienInmueble.map(t => {
      const isChecked = form.tipo_bien_inmueble_id && form.tipo_bien_inmueble_id.includes(t.tipo_bien_inmueble_id.toString());
      return `
            <label class="cc-check-card cc-check-card--compact ${isChecked ? 'is-selected' : ''}">
              <input type="checkbox" value="${t.tipo_bien_inmueble_id}" class="inmueble-tipo-check" ${isChecked ? 'checked' : ''}>
              ${t.nombre}
            </label>`;
    }).join('')}
        </div>
      </div>
      <div class="cc-grid cc-grid--3">
        <div class="cc-field"><label>Vivienda Principal <span class="req">*</span></label>
          <select data-modal="vivienda_principal" id="modalViviendaPrincipal" disabled>
            <option value="No" ${form.vivienda_principal !== 'Si' ? 'selected' : ''}>No</option>
            <option value="Si" ${form.vivienda_principal === 'Si' ? 'selected' : ''}>Sí</option>
          </select></div>
        <div class="cc-field"><label>Bien Litigioso <span class="req">*</span></label>
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

      <div class="cc-grid cc-grid--2 cc-mt">
        <div class="cc-field cc-span-2"><label>Descripción</label>
          <textarea data-modal="descripcion" rows="2">${form.descripcion || ''}</textarea></div>
        
        <div class="cc-field cc-span-2"><label>Linderos</label>
          <textarea data-modal="linderos" rows="2">${form.linderos || ''}</textarea></div>
        
        <div class="cc-grid cc-grid--3 cc-span-2">
          <div class="cc-field"><label>Superficie Construida</label>
            <input type="text" data-modal="superficie_construida" value="${form.superficie_construida || ''}"></div>
          <div class="cc-field"><label>Superficie sin Construir</label>
            <input type="text" data-modal="superficie_no_construida" value="${form.superficie_no_construida || ''}"></div>
          <div class="cc-field"><label>Área o Superficie</label>
            <input type="text" data-modal="area_superficie" value="${form.area_superficie || ''}"></div>
        </div>

        <div class="cc-field cc-span-2"><label>Dirección</label>
          <textarea data-modal="direccion" rows="2">${form.direccion || ''}</textarea></div>

        <div class="cc-field cc-span-2"><label>Oficina Subalterna/ Juzgado/ Notaría/ Misión Vivienda</label>
          <textarea data-modal="oficina_registro" rows="2">${form.oficina_registro || ''}</textarea></div>

        <div class="cc-field"><label>Nro de Registro</label>
          <input type="text" data-modal="nro_registro" value="${form.nro_registro || ''}"></div>
        <div class="cc-field"><label>Libro</label>
          <input type="text" data-modal="libro" value="${form.libro || ''}"></div>

        <div class="cc-field"><label>Protocolo</label>
          <input type="text" data-modal="protocolo" value="${form.protocolo || ''}"></div>
        <div class="cc-field"><label>Fecha</label>
          <input type="date" data-modal="fecha_registro" value="${form.fecha_registro || ''}"></div>

        <div class="cc-field"><label>Trimestre</label>
          <input type="text" data-modal="trimestre" value="${form.trimestre || ''}"></div>
        <div class="cc-field"><label>Asiento Registral</label>
          <input type="text" data-modal="asiento_registral" value="${form.asiento_registral || ''}"></div>

        <div class="cc-field"><label>Matricula</label>
          <input type="text" data-modal="matricula" value="${form.matricula || ''}"></div>
        <div class="cc-field"><label>Libro de Folio Real del Año</label>
          <input type="text" data-modal="folio_real_anio" value="${form.folio_real_anio || ''}"></div>

        <div class="cc-field"><label>Valor Original (Bs.)</label>
          <input type="number" step="0.01" data-modal="valor_original" placeholder="0,00" value="${form.valor_original || ''}"></div>
        <div class="cc-field"><label>Valor Declarado (Bs.) <span class="req">*</span></label>
          <input type="number" step="0.01" data-modal="valor_declarado" placeholder="0,00" value="${form.valor_declarado || ''}"></div>
      </div>`,
    collect: () => {
      const f = collectModalFields();

      // Get selected tipos from checkboxes
      const selectedChecks = Array.from($$('#inmuebleTipoCheckboxes input:checked')).map(cb => cb.value);
      f.tipo_bien_inmueble_id = selectedChecks; // Store as array of IDs

      // Si no es litigioso, limpiar campos litigiosos
      if (f.bien_litigioso !== 'Si') {
        delete f.numero_expediente;
        delete f.tribunal_causa;
        delete f.partes_juicio;
        delete f.estado_juicio;
      }
      return f;
    },
    validate: (form) => {
      if (!form.tipo_bien_inmueble_id || form.tipo_bien_inmueble_id.length === 0) return "Debe seleccionar al menos un Tipo de Bien.";
      if (!form.porcentaje || parseFloat(form.porcentaje) <= 0 || parseFloat(form.porcentaje) > 100) return "Porcentaje inválido.";

      if (form.bien_litigioso === 'Si') {
        if (!form.numero_expediente || !form.tribunal_causa || !form.partes_juicio || !form.estado_juicio) {
          return "Debe completar todos los detalles del Bien Litigioso.";
        }
      }

      if (!form.descripcion || !form.linderos || !form.superficie_construida || !form.superficie_no_construida || !form.area_superficie || !form.direccion || !form.oficina_registro || !form.nro_registro || !form.libro || !form.protocolo || !form.fecha_registro || !form.trimestre || !form.asiento_registral || !form.matricula || !form.folio_real_anio || !form.valor_original || !form.valor_declarado) {
        return "Por favor, complete todos los campos obligatorios del bien inmueble, incluyendo los datos registrales.";
      }
      if (parseFloat(form.valor_declarado) <= 0) return "El Valor Declarado debe ser mayor a 0.";

      return null;
    },
    save: (form) => {
      if (UIState.editIndex !== null) { caseData.bienes_inmuebles[UIState.editIndex] = form; }
      else { caseData.bienes_inmuebles.push(form); }
      renderInventario();
    }
  },

  mueble: {
    title: (edit) => {
      const catName = getCatalogs().categoriasBienMueble.find(c => c.categoria_bien_mueble_id == UIState.currentSubTab)?.nombre || '';
      return edit !== null ? `Editar — ${catName}` : `Agregar — ${catName}`;
    },
    saveLabel: (edit) => edit !== null ? 'Guardar Cambios' : 'Agregar',
    wide: false,
    build: (form) => {
      const cat = getCatalogs().categoriasBienMueble.find(c => c.categoria_bien_mueble_id == UIState.currentSubTab);
      const nameKey = cat ? cat.nombre.toLowerCase() : '';
      const tipos = getCatalogs().tiposBienMueble[UIState.currentSubTab] || [];

      // ── Helper: bloque RIF Empresa + Razón Social (readonly) ──
      const rifEmpresaBlock = (labelRif = 'Rif Empresa', labelRS = 'Razón Social') => `
            <div class="cc-field"><label>${labelRif} <span class="req">*</span></label>
              <input type="text" data-modal="rif_empresa" id="modalRifEmpresa" value="${form.rif_empresa || ''}" placeholder="Ej: J012345678">
              <span class="cc-hint cc-rif-hint" id="rifHint" style="color:var(--cc-amber-600)"></span></div>
            <div class="cc-field"><label>${labelRS}</label>
              <input type="text" data-modal="razon_social" id="modalRazonSocial" value="${form.razon_social || ''}" readonly style="background:var(--cc-slate-50)"></div>`;

      let extraFields = '';

      // ── 1. Banco ──
      if (nameKey.includes('banco')) {
        extraFields = `
              <div class="cc-field"><label>Nombre Banco <span class="req">*</span></label>
                <select data-modal="banco_id">
                  <option value="">Seleccione...</option>
                  ${getCatalogs().bancos.map(b => `<option value="${b.banco_id}" ${form.banco_id == b.banco_id ? 'selected' : ''}>${b.nombre}</option>`).join('')}
                </select></div>
              <div class="cc-field"><label>Número de Cuenta <span class="req">*</span></label>
                <input type="text" data-modal="numero_cuenta" value="${form.numero_cuenta || ''}"></div>`;

        // ── 2. Transporte ──
      } else if (nameKey.includes('transporte')) {
        extraFields = `
              <div class="cc-field"><label>Año <span class="req">*</span></label>
                <input type="number" data-modal="anio" value="${form.anio || ''}" min="1900"></div>
              <div class="cc-field"><label>Marca <span class="req">*</span></label>
                <input type="text" data-modal="marca" value="${form.marca || ''}"></div>
              <div class="cc-field"><label>Modelo <span class="req">*</span></label>
                <input type="text" data-modal="modelo" value="${form.modelo || ''}"></div>
              <div class="cc-field"><label>Serial/Número Identificador/Placas <span class="req">*</span></label>
                <input type="text" data-modal="serial_placa" value="${form.serial_placa || ''}"></div>`;

        // ── 3. Seguro ──
      } else if (nameKey.includes('seguro')) {
        extraFields = `
              ${rifEmpresaBlock()}
              <div class="cc-field"><label>Número de Prima <span class="req">*</span></label>
                <input type="text" data-modal="numero_prima" value="${form.numero_prima || ''}"></div>`;

        // ── 4. Acciones ──
      } else if (nameKey.includes('acciones')) {
        extraFields = rifEmpresaBlock();

        // ── 5. Bonos ──
      } else if (nameKey.includes('bonos')) {
        extraFields = `
              <div class="cc-field"><label>Tipo de Bonos <span class="req">*</span></label>
                <input type="text" data-modal="tipo_bonos" value="${form.tipo_bonos || ''}"></div>
              <div class="cc-field"><label>Número de Bonos <span class="req">*</span></label>
                <input type="number" data-modal="numero_bonos" value="${form.numero_bonos || ''}"></div>
              <div class="cc-field"><label>Número de Serie <span class="req">*</span></label>
                <input type="text" data-modal="numero_serie" value="${form.numero_serie || ''}"></div>`;

        // ── 6. Caja de Ahorro ──  (NO tipo de bien select)
      } else if (nameKey.includes('caja de ahorro')) {
        extraFields = rifEmpresaBlock();

        // ── 7. Cuentas y Efectos por Cobrar ──
      } else if (nameKey.includes('cobrar')) {
        extraFields = `
              <div class="cc-field"><label>Rif o Cédula <span class="req">*</span></label>
                <input type="text" data-modal="rif_cedula" value="${form.rif_cedula || ''}"></div>
              <div class="cc-field"><label>Apellidos y Nombres <span class="req">*</span></label>
                <input type="text" data-modal="apellidos_nombres" value="${form.apellidos_nombres || ''}"></div>`;

        // ── 8. Opciones de Compra ──
      } else if (nameKey.includes('compra')) {
        extraFields = `
              <div class="cc-field cc-span-2"><label>Nombre del Oferente <span class="req">*</span></label>
                <input type="text" data-modal="nombre_oferente" value="${form.nombre_oferente || ''}"></div>`;

        // ── 9. Otros ──  (solo Tipo de Bien + campos comunes)
      } else if (nameKey.includes('otros')) {
        extraFields = ''; // solo tipo + campos comunes

        // ── 10. Plantaciones ──  (NO tipo de bien, solo campos comunes)
      } else if (nameKey.includes('plantaciones')) {
        extraFields = ''; // sin campos extra

        // ── 11. Prestaciones Sociales ──
      } else if (nameKey.includes('prestaciones')) {
        extraFields = `
              <div class="cc-field"><label>¿Posee Banco?</label>
                <select data-modal="posee_banco" id="modalPoseeBanco">
                  <option value="NO" ${form.posee_banco !== 'SI' ? 'selected' : ''}>No</option>
                  <option value="SI" ${form.posee_banco === 'SI' ? 'selected' : ''}>Sí</option>
                </select></div>
              <div class="cc-field"><label>Nombre Banco</label>
                <select data-modal="banco_id" id="modalPrestBanco" ${form.posee_banco !== 'SI' ? 'disabled' : ''}>
                  <option value="">Seleccione banco...</option>
                  ${getCatalogs().bancos.map(b => `<option value="${b.banco_id}" ${form.banco_id == b.banco_id ? 'selected' : ''}>${b.nombre}</option>`).join('')}
                </select></div>
              <div class="cc-field"><label>Número de Cuenta</label>
                <input type="text" data-modal="numero_cuenta" id="modalPrestCuenta" value="${form.numero_cuenta || ''}" ${form.posee_banco !== 'SI' ? 'disabled' : ''}></div>
              ${rifEmpresaBlock()}`;

        // ── 12. Semovientes ──
      } else if (nameKey.includes('semovientes')) {
        extraFields = `
              <div class="cc-field"><label>Tipo de Semoviente <span class="req">*</span></label>
                <select data-modal="tipo_semoviente_id">
                  <option value="">Seleccione...</option>
                  ${getCatalogs().tiposSemoviente.map(s => `<option value="${s.tipo_semoviente_id}" ${form.tipo_semoviente_id == s.tipo_semoviente_id ? 'selected' : ''}>${s.nombre}</option>`).join('')}
                </select></div>
              <div class="cc-field"><label>Cantidad <span class="req">*</span></label>
                <input type="number" data-modal="cantidad" value="${form.cantidad || ''}"></div>`;
      }

      // Tipo de Bien select (some categories like Plantaciones and Caja de Ahorro don't have it)
      const skipTipoSelect = nameKey.includes('plantaciones') || nameKey.includes('caja de ahorro');
      const selectsTipo = (!skipTipoSelect && tipos.length > 0) ? `
            <div class="cc-field"><label>Tipo de Bien <span class="req">*</span></label>
            <select data-modal="tipo_bien_mueble_id">
                <option value="">Seleccione...</option>
                ${tipos.map(t => `<option value="${t.tipo_bien_mueble_id}" ${form.tipo_bien_mueble_id == t.tipo_bien_mueble_id ? 'selected' : ''}>${t.nombre}</option>`).join('')}
            </select></div>` : ``;

      // Bloque litigioso
      const bloqueLitigiosoHTML = `
            <div id="bloquelitigiosoMueble" class="cc-conditional-block cc-span-2 cc-mt" style="display:${form.bien_litigioso === 'Si' ? 'block' : 'none'}">
                <h4 class="cc-section-subtitle">Información del Litigio</h4>
                <div class="cc-grid cc-grid--2">
                <div class="cc-field"><label>Número Expediente</label>
                    <input type="text" data-modal="numero_expediente" value="${form.numero_expediente || ''}"></div>
                <div class="cc-field"><label>Tribunal de la causa</label>
                    <input type="text" data-modal="tribunal_causa" value="${form.tribunal_causa || ''}"></div>
                <div class="cc-field"><label>Partes en el Juicio</label>
                    <input type="text" data-modal="partes_juicio" value="${form.partes_juicio || ''}" maxlength="255"></div>
                <div class="cc-field"><label>Estado del Juicio</label>
                    <input type="text" data-modal="estado_juicio" value="${form.estado_juicio || ''}"></div>
                </div>
            </div>`;

      return `
      <div class="cc-grid cc-grid--2">
        ${selectsTipo}
        ${extraFields}

        <div class="cc-field"><label>Bien Litigioso</label>
          <select data-modal="bien_litigioso" id="modalBienLitigiosoMueble">
            <option value="No" ${form.bien_litigioso !== 'Si' ? 'selected' : ''}>No</option>
            <option value="Si" ${form.bien_litigioso === 'Si' ? 'selected' : ''}>Sí</option>
          </select></div>

        ${bloqueLitigiosoHTML}

        <div class="cc-field"><label>Porcentaje %</label>
          <input type="number" data-modal="porcentaje" min="0" max="100" step="0.01" value="${form.porcentaje || '0.01'}"></div>
        <div class="cc-field"><label>Descripción</label>
          <textarea data-modal="descripcion" placeholder="Descripción del bien mueble...">${form.descripcion || ''}</textarea></div>
        <div class="cc-field cc-span-2" style="display:flex; justify-content:flex-end;">
          <div class="cc-field" style="max-width:300px; width:100%;"><label>Valor Declarado (Bs.) <span class="req">*</span></label>
            <input type="number" step="0.01" data-modal="valor_declarado" placeholder="0.00" value="${form.valor_declarado || ''}"></div>
        </div>
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
    validate: (form) => {
      if (!form.valor_declarado) return "Debe ingresar el Valor Declarado.";
      if (parseFloat(form.valor_declarado) <= 0) return "El Valor Declarado debe ser mayor a 0.";

      const cat = getCatalogs().categoriasBienMueble.find(c => c.categoria_bien_mueble_id == UIState.currentSubTab);
      const nameKey = cat ? cat.nombre.toLowerCase() : '';
      const tipos = getCatalogs().tiposBienMueble[UIState.currentSubTab] || [];

      const skipTipoSelect = nameKey.includes('plantaciones') || nameKey.includes('caja de ahorro');
      if (!skipTipoSelect && tipos.length > 0 && !form.tipo_bien_mueble_id) return "Debe seleccionar el Tipo de Bien.";

      // Validar formato RIF en categorías que lo usan
      const usaRif = nameKey.includes('seguro') || nameKey.includes('acciones') || nameKey.includes('caja de ahorro') || nameKey.includes('prestaciones');
      if (usaRif) {
        if (!form.rif_empresa) return "Debe ingresar el Rif Empresa.";
        if (!/^[Jj]\d{9}$/.test(form.rif_empresa)) return "El Rif Empresa debe tener formato J seguido de 9 dígitos. Ej: J012345678";
        if (!form.razon_social) return "Debe ingresar la Razón Social.";
      }

      if (nameKey.includes('banco')) {
        if (!form.banco_id) return "Debe seleccionar el Banco.";
        if (!form.numero_cuenta) return "Debe ingresar el Número de Cuenta.";
      } else if (nameKey.includes('seguro')) {
        if (!form.numero_prima) return "Debe ingresar el Número de Prima.";
      } else if (nameKey.includes('transporte')) {
        if (!form.anio) return "Debe ingresar el Año.";
        if (!form.marca) return "Debe ingresar la Marca.";
        if (!form.modelo) return "Debe ingresar el Modelo.";
        if (!form.serial_placa) return "Debe ingresar Serial/Número Identificador/Placas.";
      } else if (nameKey.includes('bonos')) {
        if (!form.tipo_bonos) return "Debe ingresar el Tipo de Bonos.";
        if (!form.numero_bonos) return "Debe ingresar el Número de Bonos.";
        if (!form.numero_serie) return "Debe ingresar el Número de Serie.";
      } else if (nameKey.includes('cobrar')) {
        if (!form.rif_cedula) return "Debe ingresar el Rif o Cédula.";
        if (!form.apellidos_nombres) return "Debe ingresar Apellidos y Nombres.";
      } else if (nameKey.includes('compra')) {
        if (!form.nombre_oferente) return "Debe ingresar el Nombre del Oferente.";
      } else if (nameKey.includes('prestaciones')) {
        if (form.posee_banco === 'SI' && !form.banco_id) return "Debe seleccionar el Banco.";
      } else if (nameKey.includes('semovientes')) {
        if (!form.tipo_semoviente_id) return "Debe seleccionar el Tipo de Semoviente.";
        if (!form.cantidad) return "Debe ingresar la Cantidad.";
      }

      if (form.bien_litigioso === 'Si') {
        if (!form.numero_expediente || !form.tribunal_causa || !form.partes_juicio || !form.estado_juicio) {
          return "Debe completar todos los detalles del Bien Litigioso.";
        }
      }

      return null;
    },
    save: (form) => {
      if (!caseData.bienes_muebles[UIState.currentSubTab]) caseData.bienes_muebles[UIState.currentSubTab] = [];
      if (UIState.editIndex !== null) {
        caseData.bienes_muebles[UIState.currentSubTab][UIState.editIndex] = form;
      } else {
        caseData.bienes_muebles[UIState.currentSubTab].push(form);
      }
      renderInventario();
    }
  },

  pasivo_deuda: {
    title: () => 'Agregar Deuda',
    saveLabel: () => 'Agregar',
    wide: false,
    build: (form) => {
      const catalogs = getCatalogs();
      return `
      <div class="cc-grid cc-grid--2 cc-pasivo-deuda-grid" style="display: grid;">
        <div class="cc-field cc-span-2" id="wrapTipoDeuda" style="order:0"><label>Tipo de Deuda <span class="req">*</span></label>
          <select data-modal="tipo_pasivo_deuda_id" id="selectPasivoDeuda">
            <option value="">Seleccione...</option>
            ${catalogs.tiposPasivoDeuda.map(t => `<option value="${t.tipo_pasivo_deuda_id}" ${form.tipo_pasivo_deuda_id == t.tipo_pasivo_deuda_id ? 'selected' : ''}>${t.nombre}</option>`).join('')}
          </select></div>
        
        <div class="cc-field" id="wrapBancoDeuda" style="display:none; order:1"><label>Nombre Banco</label>
          <select data-modal="banco_id">
            <option value="">Seleccione banco...</option>
            ${catalogs.bancos.map(b => `<option value="${b.banco_id}" ${form.banco_id == b.banco_id ? 'selected' : ''}>${b.nombre}</option>`).join('')}
          </select></div>
        
        <div class="cc-field" id="wrapTdcDeuda" style="display:none; order:2"><label>Número de TDC</label>
          <input type="text" data-modal="numero_tdc" value="${form.numero_tdc || ''}"></div>

        <div class="cc-field" id="wrapPorcentajeDeuda" style="order:3; grid-column:1;"><label>Porcentaje %</label>
          <input type="number" data-modal="porcentaje" min="0" max="100" value="${form.porcentaje || '0.01'}"></div>
        
        <div class="cc-field" id="wrapDescDeuda" style="order:4; grid-column:2;"><label>Descripción</label>
          <textarea data-modal="descripcion" placeholder="Descripción de la deuda..." rows="2">${form.descripcion || ''}</textarea></div>
        
        <div class="cc-field" id="wrapValorDeuda" style="order:5; grid-column:2;"><label>Valor Declarado (Bs.) <span class="req">*</span></label>
          <input type="number" step="0.01" data-modal="valor_declarado" placeholder="0,00" value="${form.valor_declarado || ''}"></div>
      </div>`;
    },
    collect: () => {
      const form = collectModalFields();
      const catalogs = getCatalogs();
      const tipo = catalogs.tiposPasivoDeuda.find(t => t.tipo_pasivo_deuda_id == form.tipo_pasivo_deuda_id);
      const name = tipo ? tipo.nombre.toLowerCase() : '';

      if (!name.includes('tarjeta')) {
        delete form.numero_tdc;
      }
      if (!name.includes('tarjeta') && !(name.includes('préstamo') || name.includes('crédito') || name.includes('efecto') || name.includes('hipotecario') || name.includes('cuenta'))) {
        delete form.banco_id;
      }
      return form;
    },
    validate: (form) => {
      if (!form.tipo_pasivo_deuda_id) return "Debe seleccionar un Tipo de Deuda.";
      if (!form.valor_declarado) return "Debe ingresar el Valor Declarado.";
      if (parseFloat(form.valor_declarado) <= 0) return "El Valor Declarado debe ser mayor a 0.";
      if (form.porcentaje && (parseFloat(form.porcentaje) <= 0 || parseFloat(form.porcentaje) > 100)) return "Porcentaje inválido (debe ser entre 0.01 y 100).";

      const catalogs = getCatalogs();
      const tipo = catalogs.tiposPasivoDeuda.find(t => t.tipo_pasivo_deuda_id == form.tipo_pasivo_deuda_id);
      const name = tipo ? tipo.nombre.toLowerCase() : '';

      if (name.includes('tarjeta') && (!form.banco_id || !form.numero_tdc)) {
        return "Debe ingresar el Banco y el N° de TDC.";
      }
      if (!name.includes('tarjeta') && (name.includes('préstamo') || name.includes('crédito') || name.includes('efecto') || name.includes('hipotecario') || name.includes('cuenta')) && !form.banco_id) {
        return "Debe seleccionar el Banco.";
      }
      return null;
    },
    save: (form) => {
      if (UIState.editIndex !== null) {
        caseData.pasivos_deuda[UIState.editIndex] = form;
      } else {
        caseData.pasivos_deuda.push(form);
      }
      renderInventario();
    }
  },

  pasivo_gasto: {
    title: (edit) => edit !== null ? 'Editar Gasto' : 'Agregar Gasto',
    saveLabel: (edit) => edit !== null ? 'Guardar Cambios' : 'Agregar',
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
    validate: (form) => {
      if (!form.tipo_pasivo_gasto_id) return "Debe seleccionar un Tipo de Gasto.";
      if (!form.valor_declarado) return "Debe ingresar el Valor Declarado.";
      if (parseFloat(form.valor_declarado) <= 0) return "El Valor Declarado debe ser mayor a 0.";
      if (form.porcentaje && (parseFloat(form.porcentaje) <= 0 || parseFloat(form.porcentaje) > 100)) return "Porcentaje inválido (debe ser entre 0.01 y 100).";
      return null;
    },
    save: (form) => {
      if (UIState.editIndex !== null) {
        caseData.pasivos_gastos[UIState.editIndex] = form;
      } else {
        caseData.pasivos_gastos.push(form);
      }
      renderInventario();
    }
  },

  exencion: {
    title: (edit) => edit !== null ? 'Editar Exención' : 'Agregar Exención',
    saveLabel: (edit) => edit !== null ? 'Guardar Cambios' : 'Agregar',
    wide: false,
    build: (form) => `
      <div class="cc-field"><label>Tipo de Exención <span class="req">*</span></label>
        <input type="text" data-modal="tipo_exencion" placeholder="Tipo de exención" value="${form.tipo_exencion || ''}"></div>
      <div class="cc-field cc-mt"><label>Descripción</label>
        <textarea data-modal="descripcion" placeholder="Descripción...">${form.descripcion || ''}</textarea></div>
      <div class="cc-field cc-mt"><label>Valor Declarado (Bs.) <span class="req">*</span></label>
        <input type="number" step="0.01" data-modal="valor_declarado" value="${form.valor_declarado || ''}"></div>`,
    collect: () => collectModalFields(),
    validate: (form) => {
      if (!form.tipo_exencion) return "Debe ingresar el Tipo de Exención.";
      if (!form.valor_declarado) return "Debe ingresar el Valor Declarado.";
      if (parseFloat(form.valor_declarado) <= 0) return "El Valor Declarado debe ser mayor a 0.";
      return null;
    },
    save: (form) => {
      if (UIState.editIndex !== null) {
        caseData.exenciones[UIState.editIndex] = form;
      } else {
        caseData.exenciones.push(form);
      }
      renderInventario();
    }
  },

  exoneracion: {
    title: (edit) => edit !== null ? 'Editar Exoneración' : 'Agregar Exoneración',
    saveLabel: (edit) => edit !== null ? 'Guardar Cambios' : 'Agregar',
    wide: false,
    build: (form) => `
      <div class="cc-field"><label>Tipo de Exoneración <span class="req">*</span></label>
        <input type="text" data-modal="tipo_exoneracion" placeholder="Tipo de exoneración" value="${form.tipo_exoneracion || ''}"></div>
      <div class="cc-field cc-mt"><label>Descripción</label>
        <textarea data-modal="descripcion" placeholder="Descripción...">${form.descripcion || ''}</textarea></div>
      <div class="cc-field cc-mt"><label>Valor Declarado (Bs.) <span class="req">*</span></label>
        <input type="number" step="0.01" data-modal="valor_declarado" value="${form.valor_declarado || ''}"></div>`,
    collect: () => collectModalFields(),
    validate: (form) => {
      if (!form.tipo_exoneracion) return "Debe ingresar el Tipo de Exoneración.";
      if (!form.valor_declarado) return "Debe ingresar el Valor Declarado.";
      if (parseFloat(form.valor_declarado) <= 0) return "El Valor Declarado debe ser mayor a 0.";
      return null;
    },
    save: (form) => {
      if (UIState.editIndex !== null) {
        caseData.exoneraciones[UIState.editIndex] = form;
      } else {
        caseData.exoneraciones.push(form);
      }
      renderInventario();
    }
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
    validate: (form) => {
      if (!form.fecha_solicitud) return "Debe ingresar la Fecha de Solicitud.";
      return null;
    },
    save: (form) => { caseData.prorrogas.push(form); renderInventario(); }
  }
};

function collectModalFields() {
  const form = {};
  $$('#modalBody [data-modal]').forEach(el => {
    if (el.type === 'radio') {
      // Solo tomar el valor del radio que esté seleccionado
      if (el.checked) form[el.dataset.modal] = el.value;
    } else {
      form[el.dataset.modal] = el.value;
    }
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
  } else if (type === 'heredero_premuerto' && UIState.editIndex !== null) {
    formData = { ...caseData.herederos_premuertos[UIState.editIndex] };
  } else if (type === 'heredero_premuerto') {
    formData = { tipo_cedula: 'V', caracter: 'HEREDERO', premuerto: 'NO', parentesco_id: '', sexo: '', estado_civil: '', premuerto_padre_id: '' };
  } else if (type === 'inmueble' && UIState.editIndex !== null) {
    formData = { ...caseData.bienes_inmuebles[UIState.editIndex] };
  } else if (type === 'inmueble') {
    formData = { tipo_bien_inmueble_id: [], vivienda_principal: 'No', bien_litigioso: 'No', porcentaje: '100' };
  } else if (type === 'mueble' && UIState.editIndex !== null) {
    formData = { ...caseData.bienes_muebles[UIState.currentSubTab][UIState.editIndex] };
  } else if (type === 'mueble') {
    formData = { porcentaje: '100', bien_litigioso: 'No' };
  } else if (type === 'pasivo_deuda') {
    formData = { porcentaje: '100' };
  } else if (type === 'pasivo_gasto' && UIState.editIndex !== null) {
    formData = { ...caseData.pasivos_gastos[UIState.editIndex] };
  } else if (type === 'pasivo_gasto') {
    formData = { porcentaje: '100' };
  } else if (type === 'exencion' && UIState.editIndex !== null) {
    formData = { ...caseData.exenciones[UIState.editIndex] };
  } else if (type === 'exoneracion' && UIState.editIndex !== null) {
    formData = { ...caseData.exoneraciones[UIState.editIndex] };
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
    const checkViviendaPrincipal = () => {
      const selectedIds = Array.from(bodyEl.querySelectorAll('.inmueble-tipo-check:checked')).map(cb => cb.value);
      const catalogs = getCatalogs().tiposBienInmueble;

      // Look for Apartamento, Casa, Quinta, or Townhouse
      const validTypes = catalogs.filter(t => ['apartamento', 'casa', 'quinta', 'townhouse'].includes(t.nombre.toLowerCase())).map(t => t.tipo_bien_inmueble_id.toString());

      const hasValidType = selectedIds.some(id => validTypes.includes(id));
      const vpSelect = bodyEl.querySelector('#modalViviendaPrincipal');
      if (vpSelect) {
        if (hasValidType) {
          vpSelect.disabled = false;
        } else {
          vpSelect.disabled = true;
          vpSelect.value = 'No';
        }
      }
    };

    // Checkbox styling logic
    bodyEl.querySelectorAll('.inmueble-tipo-check').forEach(cb => {
      cb.addEventListener('change', (e) => {
        const card = e.target.closest('.cc-check-card');
        if (e.target.checked) card.classList.add('is-selected');
        else card.classList.remove('is-selected');
        checkViviendaPrincipal();
      });
    });

    // Run initial check for edit mode
    checkViviendaPrincipal();

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

    // Helper: ajusta opciones del select de letra según tipo de documento
    const syncLetraOptions = (tipoDoc) => {
      if (!selectLetra) return;
      if (tipoDoc === 'RIF') {
        // RIF → solo J, deshabilitado
        selectLetra.innerHTML = '<option value="J" selected>J</option>';
        selectLetra.value = 'J';
        selectLetra.disabled = true;
      } else {
        // Cédula → V / E, habilitado
        const currentVal = selectLetra.value;
        selectLetra.innerHTML = `
          <option value="V" ${currentVal === 'V' || !currentVal ? 'selected' : ''}>V</option>
          <option value="E" ${currentVal === 'E' ? 'selected' : ''}>E</option>
        `;
        selectLetra.disabled = false;
      }
    };

    if (radiosDoc.length > 0 && inputCed && inputPasa) {
      radiosDoc.forEach(r => r.addEventListener('change', (e) => {
        if (e.target.checked) {
          if (lblDoc) lblDoc.innerHTML = e.target.value === 'RIF' ? 'RIF <span class="req">*</span>' : 'CÉDULA <span class="req">*</span>';
          syncLetraOptions(e.target.value);
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
          // Solo rehabilitar selectLetra si no es RIF
          const checkedR = Array.from(radiosDoc).find(r => r.checked);
          if (!checkedR || checkedR.value !== 'RIF') selectLetra.disabled = false;
          radiosDoc.forEach(r => r.disabled = false);
          inputPasa.disabled = false;
        }
      };

      inputCed.addEventListener('input', handleDocInput);
      inputPasa.addEventListener('input', handleDocInput);
      handleDocInput();

      // Inicializar label y opciones de letra según el radio seleccionado
      const checkedRadio = Array.from(radiosDoc).find(r => r.checked);
      if (checkedRadio) {
        if (lblDoc) lblDoc.innerHTML = checkedRadio.value === 'RIF' ? 'RIF <span class="req">*</span>' : 'CÉDULA <span class="req">*</span>';
        syncLetraOptions(checkedRadio.value);
      }
    }
  }

  if (type === 'pasivo_deuda') {
    const selectPasivoDeuda = bodyEl.querySelector('#selectPasivoDeuda');
    const wrapBanco = bodyEl.querySelector('#wrapBancoDeuda');
    const wrapTdc = bodyEl.querySelector('#wrapTdcDeuda');
    const wrapPorcentaje = bodyEl.querySelector('#wrapPorcentajeDeuda');
    const wrapDesc = bodyEl.querySelector('#wrapDescDeuda');
    const wrapValor = bodyEl.querySelector('#wrapValorDeuda');

    const updatePasivoLayout = () => {
      const tipoId = selectPasivoDeuda.value;
      const cats = getCatalogs().tiposPasivoDeuda;
      const tipoAct = cats.find(t => t.tipo_pasivo_deuda_id == tipoId);
      const name = tipoAct ? tipoAct.nombre.toLowerCase() : '';

      // Reset standard grid & visibility values
      wrapBanco.style.display = 'none';
      wrapTdc.style.display = 'none';

      wrapPorcentaje.style.gridColumn = '1';
      wrapPorcentaje.style.order = '3';

      wrapDesc.classList.remove('cc-span-2');
      wrapDesc.style.gridColumn = '2';
      wrapDesc.style.order = '4';

      wrapValor.style.gridColumn = '2';
      wrapValor.style.order = '5';

      if (name.includes('tarjeta')) {
        // Tarjeta
        // Row 1: already taken by Tipo Deuda
        // Row 2: Banco | TDC
        wrapBanco.style.display = 'flex';
        wrapBanco.style.order = '1';

        wrapTdc.style.display = 'flex';
        wrapTdc.style.order = '2';

        // Row 3: Porcentaje | Desc
        wrapPorcentaje.style.order = '3';
        wrapDesc.style.order = '4';

        // Row 4: empty | Valor Declarado
        wrapValor.style.order = '5';
      } else if (name.includes('préstamo') || name.includes('crédito') || name.includes('efecto') || name.includes('hipotecario') || name.includes('cuenta')) {
        // Prestamo, Credito Hipotecario
        // Row 2: Porcentaje | Banco
        wrapBanco.style.display = 'flex';
        wrapBanco.style.order = '2';

        wrapPorcentaje.style.order = '1';

        // Row 3: Descripcion (Full width)
        wrapDesc.classList.add('cc-span-2');
        wrapDesc.style.gridColumn = '1 / span 2';
        wrapDesc.style.order = '3';

        // Row 4: empty | Valor Declarado
        wrapValor.style.order = '4';
      } else {
        // Otros / Impuestos
        // Row 2: Porcentaje | Descripcion
        // Row 3: empty | Valor Declarado
        wrapPorcentaje.style.order = '1';
        wrapDesc.style.order = '2';
        wrapValor.style.order = '3';
      }
    };

    if (selectPasivoDeuda && wrapBanco) {
      selectPasivoDeuda.addEventListener('change', updatePasivoLayout);
      updatePasivoLayout(); // Initial setup
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

    // ── Prestaciones: Posee Banco toggle ──
    const modalPoseeBanco = bodyEl.querySelector('#modalPoseeBanco');
    const prestBanco = bodyEl.querySelector('#modalPrestBanco');
    const prestCuenta = bodyEl.querySelector('#modalPrestCuenta');
    if (modalPoseeBanco && prestBanco && prestCuenta) {
      modalPoseeBanco.addEventListener('change', () => {
        const show = modalPoseeBanco.value === 'SI';
        prestBanco.disabled = !show;
        prestCuenta.disabled = !show;
        if (!show) { prestBanco.value = ''; prestCuenta.value = ''; }
      });
    }

    // ── RIF Empresa: buscar por RIF y auto-rellenar Razón Social ──
    const rifInput = bodyEl.querySelector('#modalRifEmpresa');
    const razonSocialInput = bodyEl.querySelector('#modalRazonSocial');
    const rifHint = bodyEl.querySelector('#rifHint');
    if (rifInput && razonSocialInput) {
      const RIF_REGEX = /^[Jj]\d{9}$/;
      const lockRazonSocial = () => { razonSocialInput.readOnly = true; razonSocialInput.style.background = 'var(--cc-slate-50)'; };
      const unlockRazonSocial = () => { razonSocialInput.readOnly = false; razonSocialInput.style.background = ''; };
      lockRazonSocial(); // default: locked
      const buscarRif = async () => {
        rifInput.value = rifInput.value.trim().toUpperCase();
        const rif = rifInput.value;
        if (!rif) { razonSocialInput.value = ''; lockRazonSocial(); if (rifHint) rifHint.textContent = ''; return; }
        if (!RIF_REGEX.test(rif)) {
          razonSocialInput.value = ''; lockRazonSocial();
          if (rifHint) rifHint.textContent = 'Formato inválido. Debe ser J seguido de 9 dígitos. Ej: J012345678';
          return;
        }
        try {
          const baseUrl = (window.BASE_URL || '/tesis_francisco/public').replace(/\/+$/, '');
          const resp = await fetch(`${baseUrl}/api/buscar-empresa-rif?rif=${encodeURIComponent(rif)}`);
          const data = await resp.json();
          if (data.success && data.data) {
            razonSocialInput.value = data.data.nombre || '';
            lockRazonSocial();
            if (rifHint) rifHint.textContent = '';
          } else {
            razonSocialInput.value = '';
            unlockRazonSocial();
            if (rifHint) rifHint.textContent = 'RIF no encontrado en la base de datos. Ingrese la Razón Social manualmente.';
          }
        } catch (e) {
          razonSocialInput.value = ''; lockRazonSocial();
          if (rifHint) rifHint.textContent = 'Error buscando RIF';
        }
      };
      rifInput.addEventListener('blur', buscarRif);
      // If editing with existing RIF, trigger search
      if (rifInput.value.trim() && !razonSocialInput.value.trim()) buscarRif();
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
      showToast(error);
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
