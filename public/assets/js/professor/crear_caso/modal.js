import { $, $$, showToast } from '../../global/utils.js';
import { caseData, UIState } from './state.js';
import { renderHerederos, renderHerederosPremuertos } from './herederos.js';
import { renderInventario } from './inventario.js';
import { getCatalogs } from '../../global/catalogos.js';

// Capture local ref to parseDecimal at module-load time (tamper-proof)
const _parseDecimal = typeof parseDecimal === 'function'
    ? parseDecimal
    : (v) => { const n = parseFloat(String(v).replace(/\./g, '').replace(',', '.')); return isNaN(n) ? 0 : n; };

/**
 * Muestra un diálogo de confirmación estilizado.
 * Retorna una Promise que resuelve a true (Aceptar) o false (Cancelar).
 */
function showConfirm(message, title = 'Confirmación') {
  return new Promise(resolve => {
    const overlay = document.createElement('div');
    overlay.style.cssText = `position:fixed;inset:0;background:rgba(10,30,61,0.5);backdrop-filter:blur(4px);
      display:flex;align-items:center;justify-content:center;z-index:300;
      opacity:0;transition:opacity 0.2s ease;`;
    overlay.innerHTML = `
      <div style="background:var(--cc-white,#fff);border-radius:16px;width:440px;max-width:90vw;
        box-shadow:0 24px 80px rgba(0,0,0,0.25);transform:translateY(10px) scale(0.98);
        transition:transform 0.2s ease;overflow:hidden;">
        <div style="padding:16px 24px;border-bottom:1px solid var(--cc-slate-100,#f1f5f9);
          display:flex;align-items:center;gap:10px;">
          <span style="font-size:20px;">⚠️</span>
          <h3 style="margin:0;font-size:15px;font-weight:700;color:var(--cc-slate-800,#1e293b);">${title}</h3>
        </div>
        <div style="padding:20px 24px;font-size:14px;color:var(--cc-slate-600,#475569);line-height:1.6;">
          ${message}
        </div>
        <div style="padding:16px 24px;border-top:1px solid var(--cc-slate-100,#f1f5f9);
          display:flex;justify-content:flex-end;gap:10px;">
          <button id="ccConfirmNo" style="padding:8px 20px;border-radius:8px;border:1px solid var(--cc-slate-200,#e2e8f0);
            background:var(--cc-white,#fff);color:var(--cc-slate-600,#475569);font-size:13px;font-weight:600;
            cursor:pointer;transition:all 0.15s ease;">Cancelar</button>
          <button id="ccConfirmYes" style="padding:8px 20px;border-radius:8px;border:none;
            background:var(--cc-blue-600,#2563eb);color:#fff;font-size:13px;font-weight:600;
            cursor:pointer;transition:all 0.15s ease;">Aceptar</button>
        </div>
      </div>`;
    document.body.appendChild(overlay);
    requestAnimationFrame(() => {
      overlay.style.opacity = '1';
      overlay.querySelector('div').style.transform = 'translateY(0) scale(1)';
    });
    const close = (val) => {
      overlay.style.opacity = '0';
      setTimeout(() => { overlay.remove(); resolve(val); }, 200);
    };
    overlay.querySelector('#ccConfirmNo').addEventListener('click', () => close(false));
    overlay.querySelector('#ccConfirmYes').addEventListener('click', () => close(true));
  });
}

const MODAL_CONFIGS = {
  heredero: {
    title: (edit) => edit !== null ? 'Editar Heredero' : 'Agregar Heredero',
    saveLabel: (edit) => edit !== null ? 'Guardar Cambios' : 'Agregar',
    wide: false,
    build: (form) => `
      <div class="cc-search-persona" style="margin-bottom:16px;">
        <label class="cc-search-persona__label">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
          </svg>
          Buscar Persona Existente
        </label>
        <input type="text" id="inputBuscarHeredero" placeholder="Escriba cédula, RIF o nombre para buscar..." autocomplete="off">
      </div>
      <div class="cc-inline-errors" id="modalHerederoErrors" style="margin-bottom:12px;">
        <p class="cc-inline-errors__title">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          <span>Error de validación</span>
        </p>
        <ul class="cc-inline-errors__list" id="modalHerederoErrorsList"></ul>
      </div>
      <div class="cc-grid cc-grid--2">
        <input type="hidden" data-modal="persona_id" value="${form.persona_id || ''}">
        <div class="cc-field"><label>Nombres <span class="cc-required">*</span></label>
          <input type="text" data-modal="nombres" value="${form.nombres || ''}" placeholder="Nombres" maxlength="98"></div>
        <div class="cc-field"><label>Apellidos <span class="cc-required">*</span></label>
          <input type="text" data-modal="apellidos" value="${form.apellidos || ''}" placeholder="Apellidos" maxlength="98"></div>
        <div class="cc-field" style="grid-column: 1 / -1;"><label>TIPO DE DOCUMENTO <span class="cc-required">*</span></label>
          <div class="cc-radio-group cc-radio-group--inline" style="display:flex;gap:1.5rem; margin-top:0.25rem;">
            <label class="cc-radio"><input type="radio" name="doc_heredero" value="Cédula" data-modal="tipo_documento" ${form.tipo_documento === 'Cédula' || !form.tipo_documento ? 'checked' : ''}> CÉDULA</label>
            <label class="cc-radio"><input type="radio" name="doc_heredero" value="RIF" data-modal="tipo_documento" ${form.tipo_documento === 'RIF' ? 'checked' : ''}> RIF</label>
            <label class="cc-radio"><input type="radio" name="doc_heredero" value="Pasaporte" data-modal="tipo_documento" ${form.tipo_documento === 'Pasaporte' ? 'checked' : ''}> PASAPORTE</label>
          </div>
        </div>
        <div class="cc-field cc-field--doc" id="wrap-her1-cedula"><label id="lblDocHer1">CÉDULA <span class="cc-required">*</span></label>
          <div class="cc-doc-wrapper" style="display:flex; gap:0.5rem">
            <select data-modal="letra_cedula" id="sel-her1-letra" style="width:70px;">
              <option value="V" ${form.letra_cedula === 'V' || !form.letra_cedula ? 'selected' : ''}>V</option>
              <option value="E" ${form.letra_cedula === 'E' ? 'selected' : ''}>E</option>
              <option value="J" ${form.letra_cedula === 'J' ? 'selected' : ''}>J</option>
              <option value="G" ${form.letra_cedula === 'G' ? 'selected' : ''}>G</option>
            </select>
            <input type="text" data-modal="cedula" id="inputCedHer1" value="${form.cedula || ''}" placeholder="Ej: 12345678" style="flex:1;" maxlength="20" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
          </div>
        </div>
        <div class="cc-field" id="wrap-her1-pasaporte" style="display:none;"><label>PASAPORTE <span class="cc-required">*</span></label>
          <input type="text" data-modal="pasaporte" id="inputPasaHer1" value="${form.pasaporte || ''}" placeholder="" maxlength="20" oninput="this.value = this.value.replace(/[^0-9]/g, '')"></div>
        <div class="cc-field"><label>Fecha de Nacimiento <span class="cc-required">*</span></label>
          <input type="date" data-modal="fecha_nacimiento" value="${form.fecha_nacimiento || ''}"></div>
        <div class="cc-field"><label>Sexo <span class="cc-required">*</span></label>
          <select data-modal="sexo">
            <option value="">Seleccione...</option>
            <option value="M" ${form.sexo === 'M' ? 'selected' : ''}>Masculino</option>
            <option value="F" ${form.sexo === 'F' ? 'selected' : ''}>Femenino</option>
          </select></div>

        <div class="cc-field"><label>Carácter <span class="cc-required">*</span></label>
          <select data-modal="caracter">
            <option value="HEREDERO" selected>Heredero</option>
          </select></div>
        <div class="cc-field"><label>Parentesco <span class="cc-required">*</span></label>
          <select data-modal="parentesco_id">
            <option value="">Seleccione...</option>
            ${getCatalogs().parentescos.filter(p => p.nombre.toLowerCase() !== 'sin definir').map(p => `<option value="${p.parentesco_id}" ${form.parentesco_id == p.parentesco_id ? 'selected' : ''}>${p.nombre}</option>`).join('')}
          </select></div>
        <div class="cc-field"><label>Premuerto <span class="cc-required">*</span></label>
          <select data-modal="premuerto" id="modalHerederoPremuerto">
            <option value="NO" ${form.premuerto !== 'SI' ? 'selected' : ''}>No</option>
            <option value="SI" ${form.premuerto === 'SI' ? 'selected' : ''}>Sí</option>
          </select></div>
        <div class="cc-field" id="bloqueFallecimiento" style="display: ${form.premuerto === 'SI' ? 'block' : 'none'}"><label>Fecha de Fallecimiento</label>
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
      // persona_id no puede ser el causante
      if (form.persona_id && caseData.causante.persona_id && String(form.persona_id) === String(caseData.causante.persona_id)) {
        return "El heredero no puede ser el mismo causante.";
      }
      // Si es el representante, no puede ser premuerto
      if (form.persona_id && caseData.representante.persona_id && String(form.persona_id) === String(caseData.representante.persona_id) && form.premuerto === 'SI') {
        return "El representante no puede ser marcado como premuerto.";
      }
      // Duplicados por persona_id (misma persona desde BD, sin importar si se buscó por cédula o RIF)
      if (form.persona_id) {
        const dupById = caseData.herederos.some((h, i) => i !== UIState.editIndex && h.persona_id && String(h.persona_id) === String(form.persona_id));
        if (dupById) return "Ya existe un heredero con esta persona (misma cédula/RIF en la base de datos).";
        const dupByIdHP = caseData.herederos_premuertos.some(h => h.persona_id && String(h.persona_id) === String(form.persona_id));
        if (dupByIdHP) return "Ya existe un heredero del premuerto que es la misma persona.";
      }
      // Cédula compuesta no puede estar repetida entre herederos
      if (form.cedula) {
        const dupH = caseData.herederos.some((h, i) => i !== UIState.editIndex && (h.letra_cedula || '') + (h.cedula || '') === fullCed);
        if (dupH) return "Ya existe un heredero con esa cédula.";
        const dupHP = caseData.herederos_premuertos.some(h => (h.letra_cedula || '') + (h.cedula || '') === fullCed);
        if (dupHP) return "Ya existe un heredero del premuerto con esa cédula.";
      }
      if (!form.nombres || !form.apellidos || !form.fecha_nacimiento || !form.sexo || !form.caracter || !form.parentesco_id || !form.premuerto) {
        return "Por favor, complete todos los campos obligatorios del heredero.";
      }
      if (form.premuerto === 'SI' && !form.fecha_fallecimiento) {
        return "Debe ingresar la fecha de fallecimiento del premuerto.";
      }
      return null;
    },
    save: (form) => {
      // Parsear _locked_fields de string JSON a array
      if (typeof form._locked_fields === 'string') {
        try { form._locked_fields = JSON.parse(form._locked_fields); } catch { form._locked_fields = []; }
      }
      // Assign stable _uid for cálculo manual linkage
      if (!form._uid) form._uid = crypto.randomUUID();
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
      <div class="cc-search-persona" style="margin-bottom:16px;">
        <label class="cc-search-persona__label">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
          </svg>
          Buscar Persona Existente
        </label>
        <input type="text" id="inputBuscarHeredero" placeholder="Escriba cédula, RIF o nombre para buscar..." autocomplete="off">
      </div>
      <div class="cc-inline-errors" id="modalHerederoErrors" style="margin-bottom:12px;">
        <p class="cc-inline-errors__title">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          <span>Error de validación</span>
        </p>
        <ul class="cc-inline-errors__list" id="modalHerederoErrorsList"></ul>
      </div>
      <div class="cc-grid cc-grid--2">
        <input type="hidden" data-modal="persona_id" value="${form.persona_id || ''}">
        <div class="cc-field"><label>Nombres <span class="cc-required">*</span></label>
          <input type="text" data-modal="nombres" value="${form.nombres || ''}" placeholder="Nombres" maxlength="98"></div>
        <div class="cc-field"><label>Apellidos <span class="cc-required">*</span></label>
          <input type="text" data-modal="apellidos" value="${form.apellidos || ''}" placeholder="Apellidos" maxlength="98"></div>
        <div class="cc-field" style="grid-column: 1 / -1;"><label>TIPO DE DOCUMENTO <span class="cc-required">*</span></label>
          <div class="cc-radio-group cc-radio-group--inline" style="display:flex;gap:1.5rem; margin-top:0.25rem;">
            <label class="cc-radio"><input type="radio" name="doc_heredero2" value="Cédula" data-modal="tipo_documento" ${form.tipo_documento === 'Cédula' || !form.tipo_documento ? 'checked' : ''}> CÉDULA</label>
            <label class="cc-radio"><input type="radio" name="doc_heredero2" value="RIF" data-modal="tipo_documento" ${form.tipo_documento === 'RIF' ? 'checked' : ''}> RIF</label>
            <label class="cc-radio"><input type="radio" name="doc_heredero2" value="Pasaporte" data-modal="tipo_documento" ${form.tipo_documento === 'Pasaporte' ? 'checked' : ''}> PASAPORTE</label>
          </div>
        </div>
        <div class="cc-field cc-field--doc" id="wrap-her2-cedula"><label id="lblDocHer2">CÉDULA <span class="cc-required">*</span></label>
          <div class="cc-doc-wrapper" style="display:flex; gap:0.5rem">
            <select data-modal="letra_cedula" id="sel-her2-letra" style="width:70px;">
              <option value="V" ${form.letra_cedula === 'V' || !form.letra_cedula ? 'selected' : ''}>V</option>
              <option value="E" ${form.letra_cedula === 'E' ? 'selected' : ''}>E</option>
              <option value="J" ${form.letra_cedula === 'J' ? 'selected' : ''}>J</option>
              <option value="G" ${form.letra_cedula === 'G' ? 'selected' : ''}>G</option>
            </select>
            <input type="text" data-modal="cedula" id="inputCedHer2" value="${form.cedula || ''}" placeholder="Ej: 12345678" style="flex:1;" maxlength="20" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
          </div>
        </div>
        <div class="cc-field" id="wrap-her2-pasaporte" style="display:none;"><label>PASAPORTE <span class="cc-required">*</span></label>
          <input type="text" data-modal="pasaporte" id="inputPasaHer2" value="${form.pasaporte || ''}" placeholder="" maxlength="20" oninput="this.value = this.value.replace(/[^0-9]/g, '')"></div>
        <div class="cc-field"><label>Fecha de Nacimiento <span class="cc-required">*</span></label>
          <input type="date" data-modal="fecha_nacimiento" value="${form.fecha_nacimiento || ''}"></div>
        <div class="cc-field"><label>Sexo <span class="cc-required">*</span></label>
          <select data-modal="sexo">
            <option value="">Seleccione...</option>
            <option value="M" ${form.sexo === 'M' ? 'selected' : ''}>Masculino</option>
            <option value="F" ${form.sexo === 'F' ? 'selected' : ''}>Femenino</option>
          </select></div>

        <div class="cc-field"><label>Carácter <span class="cc-required">*</span></label>
          <select data-modal="caracter">
            <option value="HEREDERO" selected>Heredero</option>
          </select></div>
        <div class="cc-field"><label>Parentesco <span class="cc-required">*</span></label>
          <select data-modal="parentesco_id">
            <option value="">Seleccione...</option>
            ${getCatalogs().parentescos.filter(p => p.nombre.toLowerCase() !== 'sin definir').map(p => `<option value="${p.parentesco_id}" ${form.parentesco_id == p.parentesco_id ? 'selected' : ''}>${p.nombre}</option>`).join('')}
          </select></div>

        <div class="cc-field"><label>Representa a: <span class="cc-required">*</span></label>
          <select data-modal="premuerto_padre_id">
            <option value="">No aplica...</option>
            ${caseData.herederos.map((h, i) => {
      if (h.premuerto === 'SI') {
        return `<option value="${h._uid}" ${form.premuerto_padre_id == h._uid ? 'selected' : ''}>${h.nombres} ${h.apellidos}</option>`;
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
      // No puede ser el causante
      if (form.persona_id && caseData.causante.persona_id && String(form.persona_id) === String(caseData.causante.persona_id)) {
        return "El heredero del premuerto no puede ser el mismo causante.";
      }
      // No puede ser el representante
      if (form.persona_id && caseData.representante.persona_id && String(form.persona_id) === String(caseData.representante.persona_id)) {
        return "El heredero del premuerto no puede ser el mismo representante.";
      }
      // Duplicados por persona_id
      if (form.persona_id) {
        const dupById = caseData.herederos.some(h => h.persona_id && String(h.persona_id) === String(form.persona_id));
        if (dupById) return "Ya existe un heredero que es la misma persona.";
        const dupByIdHP = caseData.herederos_premuertos.some((h, i) => i !== UIState.editIndex && h.persona_id && String(h.persona_id) === String(form.persona_id));
        if (dupByIdHP) return "Ya existe un heredero del premuerto que es la misma persona.";
      }
      // Cédula compuesta no puede estar repetida entre herederos/premuertos
      if (form.cedula) {
        const dupH = caseData.herederos.some(h => (h.letra_cedula || '') + (h.cedula || '') === fullCed);
        if (dupH) return "Ya existe un heredero con esa cédula.";
        const dupHP = caseData.herederos_premuertos.some((h, i) => i !== UIState.editIndex && (h.letra_cedula || '') + (h.cedula || '') === fullCed);
        if (dupHP) return "Ya existe un heredero del premuerto con esa cédula.";
      }
      if (!form.nombres || !form.apellidos || !form.fecha_nacimiento || !form.sexo || !form.caracter || !form.parentesco_id || !form.premuerto_padre_id) {
        return "Por favor, complete todos los campos (incluyendo a quién representa).";
      }
      return null;
    },
    save: (form) => {
      if (typeof form._locked_fields === 'string') {
        try { form._locked_fields = JSON.parse(form._locked_fields); } catch { form._locked_fields = []; }
      }
      // Assign stable _uid for cálculo manual linkage
      if (!form._uid) form._uid = crypto.randomUUID();
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
        <div class="cc-field"><label>Vivienda Principal</label>
          <select data-modal="vivienda_principal" id="modalViviendaPrincipal" disabled>
            <option value="No" ${form.vivienda_principal !== 'Si' ? 'selected' : ''}>No</option>
            <option value="Si" ${form.vivienda_principal === 'Si' ? 'selected' : ''}>Sí</option>
          </select></div>
        <div class="cc-field"><label>Bien Litigioso</label>
          <select data-modal="bien_litigioso" id="modalBienLitigioso">
            <option value="No" ${form.bien_litigioso !== 'Si' ? 'selected' : ''}>No</option>
            <option value="Si" ${form.bien_litigioso === 'Si' ? 'selected' : ''}>Sí</option>
          </select></div>
        <div class="cc-field"><label>Porcentaje %</label>
          <input type="text" class="decimal-input" data-modal="porcentaje" placeholder="Ej: 100" value="${form.porcentaje || 100}"></div>
      </div>

      <!-- Sección 9: Bloque litigioso condicional -->
      <div id="bloquelitigioso" class="cc-conditional-block cc-mt" style="display:${form.bien_litigioso === 'Si' ? 'block' : 'none'}">
        <h4 class="cc-section-subtitle">📋 Detalle de Bien Litigioso</h4>
        <div class="cc-grid cc-grid--2">
          <div class="cc-field"><label>N° Expediente</label>
            <input type="text" data-modal="numero_expediente" value="${form.numero_expediente || ''}" placeholder="Número de expediente" maxlength="98"></div>
          <div class="cc-field"><label>Tribunal de la Causa</label>
            <input type="text" data-modal="tribunal_causa" value="${form.tribunal_causa || ''}" placeholder="Nombre del tribunal" maxlength="253"></div>
          <div class="cc-field"><label>Partes en Juicio</label>
            <input type="text" data-modal="partes_juicio" value="${form.partes_juicio || ''}" placeholder="Partes involucradas" maxlength="253"></div>
          <div class="cc-field"><label>Estado del Juicio</label>
            <input type="text" data-modal="estado_juicio" value="${form.estado_juicio || ''}" placeholder="Estado actual" maxlength="98"></div>
        </div>
      </div>

      <div class="cc-grid cc-grid--2 cc-mt">
        <div class="cc-field cc-span-2"><label>Descripción</label>
          <textarea data-modal="descripcion" rows="2">${form.descripcion || ''}</textarea></div>
        
        <div class="cc-field cc-span-2"><label>Linderos</label>
          <textarea data-modal="linderos" rows="2">${form.linderos || ''}</textarea></div>
        
        <div class="cc-grid cc-grid--3 cc-span-2">
          <div class="cc-field"><label>Superficie Construida</label>
            <input type="text" class="decimal-input" data-modal="superficie_construida" value="${form.superficie_construida || ''}"></div>
          <div class="cc-field"><label>Superficie sin Construir</label>
            <input type="text" class="decimal-input" data-modal="superficie_no_construida" value="${form.superficie_no_construida || ''}"></div>
          <div class="cc-field"><label>Área o Superficie</label>
            <input type="text" class="decimal-input" data-modal="area_superficie" value="${form.area_superficie || ''}"></div>
        </div>

        <div class="cc-field cc-span-2"><label>Dirección</label>
          <textarea data-modal="direccion" rows="2">${form.direccion || ''}</textarea></div>

        <div class="cc-field cc-span-2"><label>Oficina Subalterna/ Juzgado/ Notaría/ Misión Vivienda</label>
          <textarea data-modal="oficina_registro" rows="2" maxlength="253">${form.oficina_registro || ''}</textarea></div>

        <div class="cc-field"><label>Nro de Registro</label>
          <input type="text" data-modal="nro_registro" value="${form.nro_registro || ''}" maxlength="48"></div>
        <div class="cc-field"><label>Libro</label>
          <input type="text" data-modal="libro" value="${form.libro || ''}" maxlength="48"></div>

        <div class="cc-field"><label>Protocolo</label>
          <input type="text" data-modal="protocolo" value="${form.protocolo || ''}" maxlength="48"></div>
        <div class="cc-field"><label>Fecha</label>
          <input type="date" data-modal="fecha_registro" value="${form.fecha_registro || ''}"></div>

        <div class="cc-field"><label>Trimestre</label>
          <input type="text" data-modal="trimestre" value="${form.trimestre || ''}" maxlength="18"></div>
        <div class="cc-field"><label>Asiento Registral</label>
          <input type="text" data-modal="asiento_registral" value="${form.asiento_registral || ''}" maxlength="48"></div>

        <div class="cc-field"><label>Matricula</label>
          <input type="text" data-modal="matricula" value="${form.matricula || ''}" maxlength="48"></div>
        <div class="cc-field"><label>Libro de Folio Real del Año</label>
          <input type="text" data-modal="folio_real_anio" value="${form.folio_real_anio || ''}" maxlength="18"></div>

        <div class="cc-field"><label>Valor Original (Bs.)</label>
          <input type="text" class="decimal-input decimal-signed" data-modal="valor_original" placeholder="0,00" value="${form.valor_original || ''}"></div>
        <div class="cc-field"><label>Valor Declarado (Bs.)</label>
          <input type="text" class="decimal-input decimal-signed" data-modal="valor_declarado" placeholder="0,00" value="${form.valor_declarado || ''}"></div>
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
      if (!form.porcentaje || _parseDecimal(form.porcentaje) <= 0 || _parseDecimal(form.porcentaje) > 100) return "Porcentaje inválido.";

      // Solo un bien inmueble puede ser vivienda principal
      if (form.vivienda_principal === 'Si') {
        const existeOtra = caseData.bienes_inmuebles.some((bi, i) =>
          bi.vivienda_principal === 'Si' && i !== UIState.editIndex
        );
        if (existeOtra) return "Ya existe un bien inmueble marcado como Vivienda Principal. Solo se permite uno.";
      }

      if (form.bien_litigioso === 'Si') {
        if (!form.numero_expediente?.trim() || !form.tribunal_causa?.trim() || !form.partes_juicio?.trim() || !form.estado_juicio?.trim()) {
          return "Debe completar todos los detalles del Bien Litigioso.";
        }
      }

      if (!form.descripcion || !form.linderos || !form.superficie_construida || !form.superficie_no_construida || !form.area_superficie || !form.direccion || !form.oficina_registro || !form.nro_registro || !form.libro || !form.protocolo || !form.fecha_registro || !form.trimestre || !form.asiento_registral || !form.matricula || !form.folio_real_anio || !form.valor_original || !form.valor_declarado) {
        return "Por favor, complete todos los campos obligatorios del bien inmueble, incluyendo los datos registrales.";
      }


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
      const rifDigits = (form.rif_empresa || '').replace(/^[A-Za-z]/, ''); // quitar letra J si existe
      const rifEmpresaBlock = (labelRif = 'Rif Empresa', labelRS = 'Razón Social') => `
            <div class="cc-field"><label>${labelRif}</label>
              <div style="display:flex;gap:4px;">
                <select id="modalRifLetra" style="width:60px;flex-shrink:0;" disabled>
                  <option value="J" selected>J</option>
                </select>
                <input type="text" data-modal="rif_empresa" id="modalRifEmpresa" value="${rifDigits}" placeholder="012345678" maxlength="9" style="flex:1;">
              </div>
              <span class="cc-hint cc-rif-hint" id="rifHint" style="color:var(--cc-amber-600)"></span></div>
            <div class="cc-field"><label>${labelRS}</label>
              <input type="text" data-modal="razon_social" id="modalRazonSocial" value="${form.razon_social || ''}" readonly style="background:var(--cc-slate-50)"></div>`;

      let extraFields = '';

      // ── 1. Banco ──
      if (nameKey.includes('banco')) {
        extraFields = `
              <div class="cc-field"><label>Nombre Banco</label>
                <select data-modal="banco_id">
                  <option value="">Seleccione...</option>
                  ${getCatalogs().bancos.map(b => `<option value="${b.banco_id}" ${form.banco_id == b.banco_id ? 'selected' : ''}>${b.nombre}</option>`).join('')}
                </select></div>
              <div class="cc-field"><label>Número de Cuenta</label>
                <input type="text" data-modal="numero_cuenta" value="${form.numero_cuenta || ''}" maxlength="20"></div>`;

        // ── 2. Transporte ──
      } else if (nameKey.includes('transporte')) {
        extraFields = `
              <div class="cc-field"><label>Año</label>
                <input type="number" data-modal="anio" value="${form.anio || ''}" min="1900"></div>
              <div class="cc-field"><label>Marca</label>
                <input type="text" data-modal="marca" value="${form.marca || ''}" maxlength="13"></div>
              <div class="cc-field"><label>Modelo</label>
                <input type="text" data-modal="modelo" value="${form.modelo || ''}" maxlength="13"></div>
              <div class="cc-field"><label>Serial/Número Identificador/Placas</label>
                <input type="text" data-modal="serial_placa" value="${form.serial_placa || ''}" maxlength="28"></div>`;

        // ── 3. Seguro ──
      } else if (nameKey.includes('seguro')) {
        extraFields = `
              ${rifEmpresaBlock()}
              <div class="cc-field"><label>Número de Prima</label>
                <input type="text" data-modal="numero_prima" value="${form.numero_prima || ''}" maxlength="15"></div>`;

        // ── 4. Acciones ──
      } else if (nameKey.includes('acciones')) {
        extraFields = rifEmpresaBlock();

        // ── 5. Bonos ──
      } else if (nameKey.includes('bonos')) {
        extraFields = `
              <div class="cc-field"><label>Tipo de Bonos</label>
                <input type="text" data-modal="tipo_bonos" value="${form.tipo_bonos || ''}" maxlength="58"></div>
              <div class="cc-field"><label>Número de Bonos</label>
                <input type="number" data-modal="numero_bonos" value="${form.numero_bonos || ''}"></div>
              <div class="cc-field"><label>Número de Serie</label>
                <input type="text" data-modal="numero_serie" value="${form.numero_serie || ''}" maxlength="28"></div>`;

        // ── 6. Caja de Ahorro ──  (NO tipo de bien select)
      } else if (nameKey.includes('caja de ahorro')) {
        extraFields = rifEmpresaBlock();

        // ── 7. Cuentas y Efectos por Cobrar ──
      } else if (nameKey.includes('cobrar')) {
        extraFields = `
              <div class="cc-field"><label>Rif o Cédula</label>
                <input type="text" data-modal="rif_cedula" value="${form.rif_cedula || ''}" maxlength="12"></div>
              <div class="cc-field"><label>Apellidos y Nombres</label>
                <input type="text" data-modal="apellidos_nombres" value="${form.apellidos_nombres || ''}" maxlength="98"></div>`;

        // ── 8. Opciones de Compra ──
      } else if (nameKey.includes('compra')) {
        extraFields = `
              <div class="cc-field cc-span-2"><label>Nombre del Oferente</label>
                <input type="text" data-modal="nombre_oferente" value="${form.nombre_oferente || ''}" maxlength="38"></div>`;

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
                <input type="text" data-modal="numero_cuenta" id="modalPrestCuenta" value="${form.numero_cuenta || ''}" maxlength="20" ${form.posee_banco !== 'SI' ? 'disabled' : ''}></div>
              ${rifEmpresaBlock()}`;

        // ── 12. Semovientes ──
      } else if (nameKey.includes('semovientes')) {
        extraFields = `
              <div class="cc-field"><label>Tipo de Semoviente</label>
                <select data-modal="tipo_semoviente_id">
                  <option value="">Seleccione...</option>
                  ${getCatalogs().tiposSemoviente.map(s => `<option value="${s.tipo_semoviente_id}" ${form.tipo_semoviente_id == s.tipo_semoviente_id ? 'selected' : ''}>${s.nombre}</option>`).join('')}
                </select></div>
              <div class="cc-field"><label>Cantidad</label>
                <input type="number" data-modal="cantidad" value="${form.cantidad || ''}"></div>`;
      }

      // Tipo de Bien select (some categories like Plantaciones and Caja de Ahorro don't have it)
      const skipTipoSelect = nameKey.includes('plantaciones') || nameKey.includes('caja de ahorro');
      const selectsTipo = (!skipTipoSelect && tipos.length > 0) ? `
            <div class="cc-field"><label>Tipo de Bien</label>
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
                    <input type="text" data-modal="numero_expediente" value="${form.numero_expediente || ''}" maxlength="98"></div>
                <div class="cc-field"><label>Tribunal de la causa</label>
                    <input type="text" data-modal="tribunal_causa" value="${form.tribunal_causa || ''}" maxlength="253"></div>
                <div class="cc-field"><label>Partes en el Juicio</label>
                    <input type="text" data-modal="partes_juicio" value="${form.partes_juicio || ''}" maxlength="253"></div>
                <div class="cc-field"><label>Estado del Juicio</label>
                    <input type="text" data-modal="estado_juicio" value="${form.estado_juicio || ''}" maxlength="98"></div>
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
          <input type="text" class="decimal-input" data-modal="porcentaje" placeholder="0,01 - 100" value="${form.porcentaje || 100}"></div>
        <div class="cc-field"><label>Descripción</label>
          <textarea data-modal="descripcion" placeholder="Descripción del bien mueble...">${form.descripcion || ''}</textarea></div>
        <div class="cc-field cc-span-2" style="display:flex; justify-content:flex-end;">
          <div class="cc-field" style="max-width:300px; width:100%;"><label>Valor Declarado (Bs.)</label>
            <input type="text" class="decimal-input decimal-signed" data-modal="valor_declarado" placeholder="0,00" value="${form.valor_declarado || ''}"></div>
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
      if (!form.descripcion || !form.descripcion.trim()) return "Debe ingresar la Descripción.";
      if (!form.valor_declarado) return "Debe ingresar el Valor Declarado.";

      if (form.porcentaje && (_parseDecimal(form.porcentaje) <= 0 || _parseDecimal(form.porcentaje) > 100)) return "Porcentaje inválido (debe ser entre 0.01 y 100).";

      const cat = getCatalogs().categoriasBienMueble.find(c => c.categoria_bien_mueble_id == UIState.currentSubTab);
      const nameKey = cat ? cat.nombre.toLowerCase() : '';
      const tipos = getCatalogs().tiposBienMueble[UIState.currentSubTab] || [];

      const skipTipoSelect = nameKey.includes('plantaciones') || nameKey.includes('caja de ahorro');
      if (!skipTipoSelect && tipos.length > 0 && !form.tipo_bien_mueble_id) return "Debe seleccionar el Tipo de Bien.";

      // Validar formato RIF en categorías que lo usan
      const usaRif = nameKey.includes('seguro') || nameKey.includes('acciones') || nameKey.includes('caja de ahorro') || nameKey.includes('prestaciones');
      if (usaRif) {
        if (!form.rif_empresa) return "Debe ingresar el número de Rif Empresa (9 dígitos).";
        if (!/^\d{9}$/.test(form.rif_empresa)) return "El Rif Empresa debe tener exactamente 9 dígitos.";
        if (!form.razon_social) return "Debe ingresar la Razón Social.";
      }

      if (nameKey.includes('banco')) {
        if (!form.banco_id) return "Debe seleccionar el Banco.";
        if (!form.numero_cuenta) return "Debe ingresar el Número de Cuenta.";
        if (!/^\d{20}$/.test(form.numero_cuenta)) return "El Número de Cuenta debe tener exactamente 20 dígitos.";
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
        if (form.posee_banco === 'SI' && form.numero_cuenta && !/^\d{20}$/.test(form.numero_cuenta)) return "El Número de Cuenta debe tener exactamente 20 dígitos.";
      } else if (nameKey.includes('semovientes')) {
        if (!form.tipo_semoviente_id) return "Debe seleccionar el Tipo de Semoviente.";
        if (!form.cantidad) return "Debe ingresar la Cantidad.";
      }

      if (form.bien_litigioso === 'Si') {
        if (!form.numero_expediente?.trim() || !form.tribunal_causa?.trim() || !form.partes_juicio?.trim() || !form.estado_juicio?.trim()) {
          return "Debe completar todos los detalles del Bien Litigioso.";
        }
      }

      return null;
    },
    save: async (form) => {
      // Concatenar letra J con los dígitos del RIF para guardar completo
      if (form.rif_empresa && /^\d{9}$/.test(form.rif_empresa)) {
        form.rif_empresa = 'J' + form.rif_empresa;
      }
      if (!caseData.bienes_muebles[UIState.currentSubTab]) caseData.bienes_muebles[UIState.currentSubTab] = [];
      if (UIState.editIndex !== null) {
        caseData.bienes_muebles[UIState.currentSubTab][UIState.editIndex] = form;
      } else {
        caseData.bienes_muebles[UIState.currentSubTab].push(form);
      }

      // Propagar cambio de razón social a otros bienes con el mismo RIF
      if (form.rif_empresa && form.razon_social) {
        const muebles = caseData.bienes_muebles || {};
        let razonOriginal = null;
        for (const catId of Object.keys(muebles)) {
          if (!Array.isArray(muebles[catId])) continue;
          for (const bien of muebles[catId]) {
            if (bien === form) continue;
            if (bien.rif_empresa === form.rif_empresa && bien.razon_social && bien.razon_social !== form.razon_social) {
              razonOriginal = bien.razon_social;
              break;
            }
          }
          if (razonOriginal) break;
        }
        if (razonOriginal) {
          const confirmar = await showConfirm(
            `La Razón Social de la empresa con RIF <strong>${form.rif_empresa}</strong> ha cambiado a <strong>"${form.razon_social}"</strong>.<br><br>
            ¿Desea actualizar la Razón Social en todos los bienes que usan este mismo RIF?<br><br>
            <span style="color:var(--cc-slate-400);font-size:12px;">Si cancela, se mantendrá la razón social original "${razonOriginal}".</span>`,
            'Cambio de Razón Social'
          );
          if (confirmar) {
            for (const catId of Object.keys(muebles)) {
              if (!Array.isArray(muebles[catId])) continue;
              for (const bien of muebles[catId]) {
                if (bien.rif_empresa === form.rif_empresa) {
                  bien.razon_social = form.razon_social;
                }
              }
            }
          } else {
            form.razon_social = razonOriginal;
          }
        }
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
        <div class="cc-field cc-span-2" id="wrapTipoDeuda" style="order:0"><label>Tipo de Deuda</label>
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
          <input type="text" data-modal="numero_tdc" value="${form.numero_tdc || ''}" maxlength="20"></div>

        <div class="cc-field" id="wrapPorcentajeDeuda" style="order:3; grid-column:1;"><label>Porcentaje %</label>
          <input type="text" class="decimal-input" data-modal="porcentaje" placeholder="0,01 - 100" value="${form.porcentaje || 100}"></div>
        
        <div class="cc-field" id="wrapDescDeuda" style="order:4; grid-column:2;"><label>Descripción</label>
          <textarea data-modal="descripcion" placeholder="Descripción de la deuda..." rows="2">${form.descripcion || ''}</textarea></div>
        
        <div class="cc-field" id="wrapValorDeuda" style="order:5; grid-column:2;"><label>Valor Declarado (Bs.)</label>
          <input type="text" class="decimal-input decimal-signed" data-modal="valor_declarado" placeholder="0,00" value="${form.valor_declarado || ''}"></div>
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
      if (!form.descripcion || !form.descripcion.trim()) return "Debe ingresar la Descripción.";
      if (!form.valor_declarado) return "Debe ingresar el Valor Declarado.";
      if (_parseDecimal(form.valor_declarado) <= 0) return "El Valor Declarado debe ser mayor a 0.";
      if (form.porcentaje && (_parseDecimal(form.porcentaje) <= 0 || _parseDecimal(form.porcentaje) > 100)) return "Porcentaje inválido (debe ser entre 0.01 y 100).";

      const catalogs = getCatalogs();
      const tipo = catalogs.tiposPasivoDeuda.find(t => t.tipo_pasivo_deuda_id == form.tipo_pasivo_deuda_id);
      const name = tipo ? tipo.nombre.toLowerCase() : '';

      if (name.includes('tarjeta') && (!form.banco_id || !form.numero_tdc)) {
        return "Debe ingresar el Banco y el N° de TDC.";
      }
      if (name.includes('tarjeta') && form.numero_tdc && !/^\d{16,}$/.test(form.numero_tdc)) {
        return "El N° de TDC debe tener al menos 16 dígitos numéricos.";
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
        <div class="cc-field"><label>Tipo de Gasto</label>
          <select data-modal="tipo_pasivo_gasto_id">
            <option value="">Seleccione...</option>
            ${getCatalogs().tiposPasivoGasto.map(t => `<option value="${t.tipo_pasivo_gasto_id}" ${form.tipo_pasivo_gasto_id == t.tipo_pasivo_gasto_id ? 'selected' : ''}>${t.nombre}</option>`).join('')}
          </select></div>
        <div class="cc-field"><label>Porcentaje %</label>
          <input type="text" class="decimal-input" data-modal="porcentaje" placeholder="0,01 - 100" value="${form.porcentaje || 100}"></div>
        <div class="cc-field cc-span-2"><label>Descripción</label>
          <textarea data-modal="descripcion" placeholder="Motivo del gasto...">${form.descripcion || ''}</textarea></div>
        <div class="cc-field cc-span-2"><label>Valor Declarado (Bs.)</label>
          <input type="text" class="decimal-input decimal-signed" data-modal="valor_declarado" placeholder="0,00" value="${form.valor_declarado || ''}"></div>
      </div>`,
    collect: () => collectModalFields(),
    validate: (form) => {
      if (!form.tipo_pasivo_gasto_id) return "Debe seleccionar un Tipo de Gasto.";
      if (!form.descripcion || !form.descripcion.trim()) return "Debe ingresar la Descripción.";
      if (!form.valor_declarado) return "Debe ingresar el Valor Declarado.";
      if (_parseDecimal(form.valor_declarado) <= 0) return "El Valor Declarado debe ser mayor a 0.";
      if (form.porcentaje && (_parseDecimal(form.porcentaje) <= 0 || _parseDecimal(form.porcentaje) > 100)) return "Porcentaje inválido (debe ser entre 0.01 y 100).";
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
      <div class="cc-field"><label>Tipo de Exención</label>
        <input type="text" data-modal="tipo_exencion" placeholder="Tipo de exención" value="${form.tipo_exencion || ''}" maxlength="253"></div>
      <div class="cc-field cc-mt"><label>Descripción</label>
        <textarea data-modal="descripcion" placeholder="Descripción...">${form.descripcion || ''}</textarea></div>
      <div class="cc-field cc-mt"><label>Valor Declarado (Bs.)</label>
        <input type="text" class="decimal-input decimal-signed" data-modal="valor_declarado" placeholder="0,00" value="${form.valor_declarado || ''}"></div>`,
    collect: () => collectModalFields(),
    validate: (form) => {
      if (!form.tipo_exencion) return "Debe ingresar el Tipo de Exención.";
      if (!form.descripcion || !form.descripcion.trim()) return "Debe ingresar la Descripción.";
      if (!form.valor_declarado) return "Debe ingresar el Valor Declarado.";
      if (_parseDecimal(form.valor_declarado) <= 0) return "El Valor Declarado debe ser mayor a 0.";
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
      <div class="cc-field"><label>Tipo de Exoneración</label>
        <input type="text" data-modal="tipo_exoneracion" placeholder="Tipo de exoneración" value="${form.tipo_exoneracion || ''}" maxlength="253"></div>
      <div class="cc-field cc-mt"><label>Descripción</label>
        <textarea data-modal="descripcion" placeholder="Descripción...">${form.descripcion || ''}</textarea></div>
      <div class="cc-field cc-mt"><label>Valor Declarado (Bs.)</label>
        <input type="text" class="decimal-input decimal-signed" data-modal="valor_declarado" placeholder="0,00" value="${form.valor_declarado || ''}"></div>`,
    collect: () => collectModalFields(),
    validate: (form) => {
      if (!form.tipo_exoneracion) return "Debe ingresar el Tipo de Exoneración.";
      if (!form.descripcion || !form.descripcion.trim()) return "Debe ingresar la Descripción.";
      if (!form.valor_declarado) return "Debe ingresar el Valor Declarado.";
      if (_parseDecimal(form.valor_declarado) <= 0) return "El Valor Declarado debe ser mayor a 0.";
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
        <div class="cc-field"><label>Fecha de Solicitud</label>
          <input type="date" data-modal="fecha_solicitud" value="${form.fecha_solicitud || ''}"></div>
        <div class="cc-field"><label>N° Resolución</label>
          <input type="text" data-modal="nro_resolucion" value="${form.nro_resolucion || ''}" placeholder="Número de resolución" maxlength="48"></div>
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
    formData = { tipo_cedula: 'V', caracter: 'HEREDERO', premuerto: 'NO', parentesco_id: '', sexo: '' };
  } else if (type === 'heredero_premuerto' && UIState.editIndex !== null) {
    formData = { ...caseData.herederos_premuertos[UIState.editIndex] };
  } else if (type === 'heredero_premuerto') {
    formData = { tipo_cedula: 'V', caracter: 'HEREDERO', premuerto: 'NO', parentesco_id: '', sexo: '', premuerto_padre_id: '' };
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

  // Show/hide Limpiar Campos button (only for heredero modals)
  const clearBtn = document.getElementById('modalClearBtn');
  if (clearBtn) {
    clearBtn.style.display = (type === 'heredero' || type === 'heredero_premuerto') ? '' : 'none';
  }

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
    const inputFechaFall = bloqueFallecimiento ? bloqueFallecimiento.querySelector('[data-modal="fecha_fallecimiento"]') : null;

    // Store DB fecha_fallecimiento so it can be restored on toggle
    let _dbFechaFallecimiento = null;
    if (UIState.editIndex !== null) {
      const col = type === 'heredero' ? caseData.herederos : caseData.herederos_premuertos;
      const itm = col[UIState.editIndex];
      if (itm && itm.fecha_fallecimiento) _dbFechaFallecimiento = itm.fecha_fallecimiento;
    }

    if (premuertoSelect && bloqueFallecimiento) {
      premuertoSelect.addEventListener('change', () => {
        const isSI = premuertoSelect.value === 'SI';
        bloqueFallecimiento.style.display = isSI ? 'block' : 'none';
        // When toggling to SI, load stored DB fecha if field is empty
        if (isSI && inputFechaFall && !inputFechaFall.value && _dbFechaFallecimiento) {
          inputFechaFall.value = _dbFechaFallecimiento;
          // Lock if it came from DB
          const lockedEl = bodyEl.querySelector('[data-modal="_locked_fields"]');
          if (lockedEl) {
            try {
              const arr = JSON.parse(lockedEl.value || '[]');
              if (!arr.includes('fecha_fallecimiento')) {
                arr.push('fecha_fallecimiento');
                lockedEl.value = JSON.stringify(arr);
              }
            } catch(e) {}
          }
          inputFechaFall.disabled = true;
          inputFechaFall.style.backgroundColor = 'var(--cc-slate-50, #f8fafc)';
        }
      });
    }

    // Expose setter so autocomplete can store the DB fecha
    bodyEl._setDbFechaFallecimiento = (val) => { _dbFechaFallecimiento = val; };

    // Si estamos editando un heredero/premuerto cargado de la BD, deshabilitar campos
    if (UIState.editIndex !== null) {
      const collection = type === 'heredero' ? caseData.herederos : caseData.herederos_premuertos;
      const item = collection[UIState.editIndex];
      let lockedFields = item?._locked_fields || [];

      // Fallback: si tiene persona_id pero no _locked_fields, inferir de campos con valor
      if (lockedFields.length === 0 && item?.persona_id) {
        const inferrable = ['nombres', 'apellidos', 'fecha_nacimiento', 'sexo'];
        lockedFields = inferrable.filter(f => item[f] && String(item[f]).trim());
        // fecha_fallecimiento solo si viene explícitamente en _locked_fields (no inferir)
        item._locked_fields = lockedFields;
      }

      if (lockedFields.length > 0) {
        const lockStyle = 'var(--cc-slate-50, #f8fafc)';
        lockedFields.forEach(field => {
          const el = bodyEl.querySelector(`[data-modal="${field}"]`);
          if (el) {
            el.disabled = true;
            el.style.backgroundColor = lockStyle;
          }
        });

        // Also lock fecha_fallecimiento if it came from DB and premuerto is SI
        if (item?.persona_id && item?.fecha_fallecimiento && item?.premuerto === 'SI' && inputFechaFall) {
          inputFechaFall.disabled = true;
          inputFechaFall.style.backgroundColor = lockStyle;
        }
      }
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
        selectLetra.innerHTML = '<option value="V">V</option><option value="J">J</option>';
        selectLetra.value = 'J';
      } else {
        const currentVal = selectLetra.value;
        selectLetra.innerHTML = `
            <option value="V" ${currentVal === 'V' || !currentVal ? 'selected' : ''}>V</option>
            <option value="E" ${currentVal === 'E' ? 'selected' : ''}>E</option>
          `;
      }
    };

    const wrapCedula = bodyEl.querySelector('[id^="wrap-her"][id$="-cedula"]');
    const wrapPasaporte = bodyEl.querySelector('[id^="wrap-her"][id$="-pasaporte"]');

    if (radiosDoc.length > 0 && inputCed && inputPasa) {
      const handleDocInput = () => {
        const hasPasaporte = inputPasa.value.trim() !== '';
        const hasCedula = inputCed.value.trim() !== '';

        if (hasPasaporte) {
          inputCed.disabled = true;
          selectLetra.disabled = true;
        } else if (hasCedula) {
          inputPasa.disabled = true;
        } else {
          inputCed.disabled = false;
          selectLetra.disabled = false;
          inputPasa.disabled = false;
        }
      };

      radiosDoc.forEach(r => r.addEventListener('change', (e) => {
        if (e.target.checked) {
          const val = e.target.value;
          if (val === 'Pasaporte') {
            if (wrapCedula) wrapCedula.style.display = 'none';
            if (wrapPasaporte) wrapPasaporte.style.display = '';
            inputCed.value = '';
            // Clear DB-loaded persona data when switching to pasaporte
            clearPersonaData();
          } else {
            if (wrapCedula) wrapCedula.style.display = '';
            if (wrapPasaporte) wrapPasaporte.style.display = 'none';
            inputPasa.value = '';
            if (lblDoc) lblDoc.innerHTML = val === 'RIF' ? 'RIF' : 'CÉDULA';
            syncLetraOptions(val);
          }
          handleDocInput(); // Recalculate disabled states!
          if (val !== 'Pasaporte') {
            fetchPersona(); // trigger search on doc type switch if there's input
          }
        }
      }));

      if (selectLetra) {
        selectLetra.addEventListener('change', () => fetchPersona());
      }

      const clearPersonaData = () => {
        radiosDoc.forEach(r => r.disabled = false);
        // Only clear fields that were previously auto-filled from DB
        const elLocked = bodyEl.querySelector('[data-modal="_locked_fields"]');
        let lockedFields = [];
        if (elLocked) {
          try { lockedFields = JSON.parse(elLocked.value); } catch { lockedFields = []; }
        }
        lockedFields.forEach(field => {
          const el = bodyEl.querySelector(`[data-modal="${field}"]`);
          if (el) {
            el.value = '';
            el.disabled = false;
            el.style.backgroundColor = '';
          }
        });
        // Also clear persona_id since the person was unlinked
        const elPid = bodyEl.querySelector('[data-modal="persona_id"]');
        if (elPid) elPid.value = '';
        // Reset locked fields tracker
        if (elLocked) elLocked.value = '[]';

        // Clear search bar
        const inputBuscar = bodyEl.querySelector('#inputBuscarHeredero');
        if (inputBuscar) inputBuscar.value = '';
      };

      const fetchPersona = async () => {
        let url = '';
        const baseUrl = (window.BASE_URL || '/tesis_francisco/public').replace(/\/+$/, '');
        const checkedRadio = Array.from(radiosDoc).find(r => r.checked)?.value || 'Cédula';

        if (checkedRadio === 'Pasaporte') {
          const pasaporte = inputPasa ? inputPasa.value.trim() : '';
          if (!pasaporte || pasaporte.length < 5) {
            // Only clear if empty, don't clear while typing early chars
            if (pasaporte.length === 0) clearPersonaData();
            return;
          }
          url = `${baseUrl}/api/buscar-persona?pasaporte=${pasaporte}`;
        } else {
          const cedula = inputCed ? inputCed.value.trim() : '';
          if (!cedula || cedula.length < 6) {
            if (cedula.length === 0) clearPersonaData();
            return;
          }
          const tipo = selectLetra ? selectLetra.value : '';
          if (!tipo) {
            clearPersonaData();
            return;
          }

          if (checkedRadio === 'RIF') {
            url = `${baseUrl}/api/buscar-persona?rif=${tipo}${cedula}`;
          } else {
            url = `${baseUrl}/api/buscar-persona?tipo=${tipo}&cedula=${cedula}`;
          }
        }

        try {
          const resp = await fetch(url);
          const json = await resp.json();

          if (json.success && json.data) {
            const data = json.data;

            // ... (Restante de validación remains unchanged, we will edit the end of the block only)
            let isSamePerson = false;
            if (caseData.causante) {
              // Compare by cédula
              if (checkedRadio === 'RIF') {
                if (data.rif_personal && caseData.causante.rif_personal && data.rif_personal === caseData.causante.rif_personal) isSamePerson = true;
                if (caseData.causante.cedula && data.rif_personal && (caseData.causante.tipo_cedula + caseData.causante.cedula) === data.rif_personal) isSamePerson = true;
              } else if (checkedRadio === 'Pasaporte') {
                if (data.pasaporte && caseData.causante.pasaporte && data.pasaporte === caseData.causante.pasaporte) isSamePerson = true;
              } else {
                if (caseData.causante.cedula && data.cedula && data.cedula === caseData.causante.cedula && data.tipo_cedula === caseData.causante.tipo_cedula) isSamePerson = true;
              }
              if (data.persona_id && caseData.causante.persona_id && String(data.persona_id) === String(caseData.causante.persona_id)) isSamePerson = true;
            }

            // Also check representante (only block for heredero_premuerto, regular heredero can be representante)
            let isSameRepresentante = false;
            if (type === 'heredero_premuerto' && caseData.representante && data.persona_id && caseData.representante.persona_id && String(data.persona_id) === String(caseData.representante.persona_id)) {
              isSameRepresentante = true;
            }

            if (isSamePerson || isSameRepresentante) {
              const errContainer = document.getElementById('modalHerederoErrors');
              const errList = document.getElementById('modalHerederoErrorsList');
              const msg = isSamePerson ? 'El heredero no puede ser el mismo causante.' : 'El heredero del premuerto no puede ser el mismo representante.';
              if (errContainer && errList) {
                errList.innerHTML = `<li>${msg}</li>`;
                errContainer.classList.add('is-visible');
                errContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
              }
              inputCed.value = '';
              inputPasa.value = '';
              handleDocInput();
              return;
            }

            // Clear previously locked fields before filling new person's data
            clearPersonaData();

            // Llenar campos y rastrear cuáles vienen de BD
            const lockedFromDb = [];
            const fillIfExist = (sel, val, fieldName) => {
              const el = bodyEl.querySelector(sel);
              if (el && val) {
                el.value = val;
                el.disabled = true;
                el.style.backgroundColor = 'var(--cc-slate-50, #f8fafc)';
                if (fieldName) lockedFromDb.push(fieldName);
              }
            };

            fillIfExist('[data-modal="nombres"]', data.nombres, 'nombres');
            fillIfExist('[data-modal="apellidos"]', data.apellidos, 'apellidos');
            fillIfExist('[data-modal="fecha_nacimiento"]', data.fecha_nacimiento, 'fecha_nacimiento');

            if (data.sexo) fillIfExist('[data-modal="sexo"]', data.sexo, 'sexo');



            // Fill persona_id if provided by the DB
            if (data.persona_id) {
              const elPersonaId = bodyEl.querySelector('[data-modal="persona_id"]');
              if (elPersonaId) elPersonaId.value = data.persona_id;
            }

            // Store fecha_fallecimiento from DB for later use on premuerto toggle
            if (data.fecha_fallecimiento) {
              if (bodyEl._setDbFechaFallecimiento) bodyEl._setDbFechaFallecimiento(data.fecha_fallecimiento);
              // If premuerto is already SI, fill and lock immediately
              if (premuertoSelect && premuertoSelect.value === 'SI') {
                fillIfExist('[data-modal="fecha_fallecimiento"]', data.fecha_fallecimiento, 'fecha_fallecimiento');
              }
            }

            // Guardar qué campos vienen de la BD en un input oculto
            let elLocked = bodyEl.querySelector('[data-modal="_locked_fields"]');
            if (!elLocked) {
              elLocked = document.createElement('input');
              elLocked.type = 'hidden';
              elLocked.dataset.modal = '_locked_fields';
              bodyEl.appendChild(elLocked);
            }
            elLocked.value = JSON.stringify(lockedFromDb);
          } else {
            // Not found in DB, clear fields
            clearPersonaData();
          }
        } catch (e) {
          console.error('Error auto-rellenando heredero:', e);
          clearPersonaData();
        }
      };

      // premuertoSelect change is handled above for showing/hiding
      // fecha_fallecimiento — do NOT trigger fetchPersona here to
      // avoid wiping manually-entered data.

      inputCed.addEventListener('input', handleDocInput);
      inputPasa.addEventListener('input', handleDocInput);

      inputCed.addEventListener('input', fetchPersona);
      inputPasa.addEventListener('input', fetchPersona);

      handleDocInput();

      // ── Search bar AutocompleteDropdown ──
      const inputBuscarHer = bodyEl.querySelector('#inputBuscarHeredero');
      if (inputBuscarHer && typeof AutocompleteDropdown !== 'undefined') {
        new AutocompleteDropdown({
          input: inputBuscarHer,
          debounceMs: 300,
          minLength: 0,
          fetchFn: async (query, signal) => {
            const baseUrl2 = (window.BASE_URL || '/tesis_francisco/public').replace(/\/+$/, '');
            const params = new URLSearchParams({ campo: 'cedula' });
            if (query) params.set('q', query);
            const resp = await fetch(`${baseUrl2}/api/buscar-personas?${params}`, { signal });
            const json = await resp.json();
            return json.success ? json.data : [];
          },
          onSelect: async (item) => {
            const baseUrl2 = (window.BASE_URL || '/tesis_francisco/public').replace(/\/+$/, '');
            try {
              const resp = await fetch(`${baseUrl2}/api/buscar-persona?persona_id=${item.persona_id}`);
              const json = await resp.json();
              if (!json.success || !json.data) return;
              const data = json.data;

              // Validate not same as causante
              let isSameCausante = false;
              if (caseData.causante) {
                if (data.persona_id && caseData.causante.persona_id && String(data.persona_id) === String(caseData.causante.persona_id)) isSameCausante = true;
                if (data.cedula && caseData.causante.cedula && data.cedula === caseData.causante.cedula && data.tipo_cedula === caseData.causante.tipo_cedula) isSameCausante = true;
                if (data.rif_personal && caseData.causante.rif_personal && data.rif_personal === caseData.causante.rif_personal) isSameCausante = true;
              }

              // Validate not same as representante (only for heredero_premuerto)
              let isSameRep = false;
              if (type === 'heredero_premuerto' && caseData.representante && data.persona_id && caseData.representante.persona_id && String(data.persona_id) === String(caseData.representante.persona_id)) {
                isSameRep = true;
              }

              if (isSameCausante || isSameRep) {
                const errContainer = document.getElementById('modalHerederoErrors');
                const errList = document.getElementById('modalHerederoErrorsList');
                const msg = isSameCausante ? 'El heredero no puede ser el mismo causante.' : 'El heredero del premuerto no puede ser el mismo representante.';
                if (errContainer && errList) {
                  errList.innerHTML = `<li>${msg}</li>`;
                  errContainer.classList.add('is-visible');
                  errContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
                inputBuscarHer.value = '';
                inputBuscarHer.blur();
                return;
              }

              // Validate not duplicate heredero/premuerto (same + cross collection)
              if (data.persona_id) {
                const dupHeredero = caseData.herederos.some((h, i) => {
                  if (type === 'heredero' && i === UIState.editIndex) return false;
                  return h.persona_id && String(h.persona_id) === String(data.persona_id);
                });
                if (dupHeredero) {
                  const errContainer = document.getElementById('modalHerederoErrors');
                  const errList = document.getElementById('modalHerederoErrorsList');
                  if (errContainer && errList) {
                    errList.innerHTML = `<li>Ya existe un heredero con esta persona.</li>`;
                    errContainer.classList.add('is-visible');
                    errContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                  }
                  inputBuscarHer.value = '';
                  inputBuscarHer.blur();
                  return;
                }
                const dupPremuerto = caseData.herederos_premuertos.some((h, i) => {
                  if (type === 'heredero_premuerto' && i === UIState.editIndex) return false;
                  return h.persona_id && String(h.persona_id) === String(data.persona_id);
                });
                if (dupPremuerto) {
                  const errContainer = document.getElementById('modalHerederoErrors');
                  const errList = document.getElementById('modalHerederoErrorsList');
                  if (errContainer && errList) {
                    errList.innerHTML = `<li>Ya existe un heredero del premuerto con esta persona.</li>`;
                    errContainer.classList.add('is-visible');
                    errContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                  }
                  inputBuscarHer.value = '';
                  inputBuscarHer.blur();
                  return;
                }
              }

              // Clear previous data and fill
              clearPersonaData();

              const lockedFromDb = [];
              const fillIfExist2 = (sel, val, fieldName) => {
                const el = bodyEl.querySelector(sel);
                if (el && val) {
                  el.value = val;
                  el.disabled = true;
                  el.style.backgroundColor = 'var(--cc-slate-50, #f8fafc)';
                  if (fieldName) lockedFromDb.push(fieldName);
                }
              };

              fillIfExist2('[data-modal="nombres"]', data.nombres, 'nombres');
              fillIfExist2('[data-modal="apellidos"]', data.apellidos, 'apellidos');
              fillIfExist2('[data-modal="fecha_nacimiento"]', data.fecha_nacimiento, 'fecha_nacimiento');
              if (data.sexo) fillIfExist2('[data-modal="sexo"]', data.sexo, 'sexo');


              if (data.persona_id) {
                const elPid = bodyEl.querySelector('[data-modal="persona_id"]');
                if (elPid) elPid.value = data.persona_id;
              }

              // Fill cédula/RIF from DB
              if (data.cedula && data.tipo_cedula) {
                if (inputCed) inputCed.value = data.cedula;
                if (selectLetra) {
                  syncLetraOptions(data.tipo_cedula === 'J' ? 'RIF' : 'Cédula');
                  selectLetra.value = data.tipo_cedula;
                }
                // Set radio to correct type
                const radioVal = data.tipo_cedula === 'J' ? 'RIF' : 'Cédula';
                radiosDoc.forEach(r => { r.checked = r.value === radioVal; });
                if (lblDoc) lblDoc.innerHTML = radioVal === 'RIF' ? 'RIF' : 'CÉDULA';
                if (wrapCedula) wrapCedula.style.display = '';
                if (wrapPasaporte) wrapPasaporte.style.display = 'none';
                inputCed.disabled = true;
                inputCed.style.backgroundColor = 'var(--cc-slate-50, #f8fafc)';
                if (selectLetra) { selectLetra.disabled = true; selectLetra.style.backgroundColor = 'var(--cc-slate-50, #f8fafc)'; }
                lockedFromDb.push('cedula');
                radiosDoc.forEach(r => r.disabled = true);
              }

              // Store fecha_fallecimiento from DB
              if (data.fecha_fallecimiento && bodyEl._setDbFechaFallecimiento) {
                bodyEl._setDbFechaFallecimiento(data.fecha_fallecimiento);
                if (premuertoSelect && premuertoSelect.value === 'SI') {
                  fillIfExist2('[data-modal="fecha_fallecimiento"]', data.fecha_fallecimiento, 'fecha_fallecimiento');
                }
              }

              // Save locked fields
              let elLocked = bodyEl.querySelector('[data-modal="_locked_fields"]');
              if (!elLocked) {
                elLocked = document.createElement('input');
                elLocked.type = 'hidden';
                elLocked.dataset.modal = '_locked_fields';
                bodyEl.appendChild(elLocked);
              }
              elLocked.value = JSON.stringify(lockedFromDb);

              // Update search bar display
              inputBuscarHer.value = `${data.nombres || ''} ${data.apellidos || ''} — ${data.cedula || 'S/C'}`.trim();

              showToast('Datos del heredero autocompletados', 'success');
            } catch (err) {
              console.error('Error fetching heredero by ID:', err);
            }
          }
        });

        // If editing, show name in search bar
        if (UIState.editIndex !== null) {
          const collection = type === 'heredero' ? caseData.herederos : caseData.herederos_premuertos;
          const item = collection[UIState.editIndex];
          if (item && item.persona_id) {
            inputBuscarHer.value = `${item.nombres || ''} ${item.apellidos || ''} — ${item.cedula || 'S/C'}`.trim();
          }
        }
      }

      // Inicializar UI basada en form data si se edita
      const checkedRadioInit = Array.from(radiosDoc).find(r => r.checked);
      if (checkedRadioInit) {
        const val = checkedRadioInit.value;
        if (val === 'Pasaporte') {
          if (wrapCedula) wrapCedula.style.display = 'none';
          if (wrapPasaporte) wrapPasaporte.style.display = '';
        } else {
          if (wrapCedula) wrapCedula.style.display = '';
          if (wrapPasaporte) wrapPasaporte.style.display = 'none';
          if (lblDoc) lblDoc.innerHTML = val === 'RIF' ? 'RIF' : 'CÉDULA';
          syncLetraOptions(val);
        }
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
    const rifLetra = bodyEl.querySelector('#modalRifLetra');
    const razonSocialInput = bodyEl.querySelector('#modalRazonSocial');
    const rifHint = bodyEl.querySelector('#rifHint');
    if (rifInput && razonSocialInput) {
      const DIGITS_REGEX = /^\d{9}$/;
      const lockRazonSocial = () => { razonSocialInput.readOnly = true; razonSocialInput.style.background = 'var(--cc-slate-50)'; };
      const unlockRazonSocial = () => { razonSocialInput.readOnly = false; razonSocialInput.style.background = ''; };
      lockRazonSocial(); // default: locked

      // Solo permitir dígitos en el input de RIF
      rifInput.addEventListener('input', () => {
        rifInput.value = rifInput.value.replace(/\D/g, '').slice(0, 9);
      });

      const buscarRif = async () => {
        rifInput.value = rifInput.value.trim();
        const digits = rifInput.value;
        if (!digits) { razonSocialInput.value = ''; lockRazonSocial(); if (rifHint) rifHint.textContent = ''; return; }
        if (!DIGITS_REGEX.test(digits)) {
          razonSocialInput.value = ''; lockRazonSocial();
          if (rifHint) rifHint.textContent = 'Debe ingresar exactamente 9 dígitos.';
          return;
        }
        const rif = 'J' + digits; // Concatenar letra + dígitos

        // 1. Buscar primero en la BD (si existe, siempre bloquea razón social)
        try {
          const baseUrl = (window.BASE_URL || '/tesis_francisco/public').replace(/\/+$/, '');
          const resp = await fetch(`${baseUrl}/api/buscar-empresa-rif?rif=${encodeURIComponent(rif)}`);
          const data = await resp.json();
          if (data.success && data.data) {
            razonSocialInput.value = data.data.nombre || '';
            lockRazonSocial();
            if (rifHint) rifHint.textContent = '';
            return; // Empresa existe en DB → locked, no buscar más
          }
        } catch (e) {
          razonSocialInput.value = ''; lockRazonSocial();
          if (rifHint) rifHint.textContent = 'Error buscando RIF';
          return;
        }

        // 2. Si no se encontró en la BD, buscar en los bienes ya guardados en este borrador
        const muebles = caseData.bienes_muebles || {};
        for (const catId of Object.keys(muebles)) {
          if (!Array.isArray(muebles[catId])) continue;
          for (const bien of muebles[catId]) {
            if (bien.rif_empresa === rif && bien.razon_social) {
              razonSocialInput.value = bien.razon_social;
              unlockRazonSocial(); // Editable porque la empresa no existe en DB
              if (rifHint) rifHint.textContent = 'Razón social tomada de otro bien. Si la modifica, se le preguntará si desea actualizarla en todos.';
              return;
            }
          }
        }

        // 3. No encontrado en ningún lado → campo libre
        razonSocialInput.value = '';
        unlockRazonSocial();
        if (rifHint) rifHint.textContent = 'RIF no encontrado en la base de datos. Ingrese la Razón Social manualmente.';
      };
      rifInput.addEventListener('blur', buscarRif);
      // Si hay RIF al abrir (editando), verificar si debe bloquear o desbloquear razón social
      if (rifInput.value.trim()) buscarRif();
    }
  }

  // ── Restricciones adicionales sobre campos decimal-input ──
  // (La sanitización básica la maneja decimal_input.js globalmente.
  //  Aquí solo aplicamos restricciones de longitud máxima y cap de porcentaje.)
  const decimalFields = bodyEl.querySelectorAll(
    '[data-modal="valor_declarado"], [data-modal="valor_original"], [data-modal="porcentaje"], ' +
    '[data-modal="superficie_construida"], [data-modal="superficie_no_construida"], [data-modal="area_superficie"]'
  );
  decimalFields.forEach(el => {
    el.addEventListener('input', () => {
      let val = el.value;
      // Campos de dinero: max 18 dígitos antes de la coma (DECIMAL 20,2)
      const moneyFields = ['valor_declarado', 'valor_original'];
      if (moneyFields.includes(el.dataset.modal)) {
        const p = val.split(',');
        const intPart = p[0].replace(/\D/g, '');
        if (intPart.length > 18) {
          val = intPart.slice(0, 18) + (p[1] !== undefined ? ',' + p[1] : '');
          el.value = val;
        }
      }
      // Campos de superficie: max 10 dígitos antes de la coma (DECIMAL 12,2)
      const surfaceFields = ['superficie_construida', 'superficie_no_construida', 'area_superficie'];
      if (surfaceFields.includes(el.dataset.modal)) {
        const p = val.split(',');
        const intPart = p[0].replace(/\D/g, '');
        if (intPart.length > 10) {
          val = intPart.slice(0, 10) + (p[1] !== undefined ? ',' + p[1] : '');
          el.value = val;
        }
      }
      // Porcentaje no puede ser mayor a 100
      if (el.dataset.modal === 'porcentaje' && _parseDecimal(val) > 100) {
        el.value = '100';
      }
    });
  });

  overlay.classList.add('is-open');
}

export function clearModalFields() {
  const bodyEl = $('#modalBody');
  if (!bodyEl) return;

  // Clear search bar
  const inputBuscar = bodyEl.querySelector('#inputBuscarHeredero');
  if (inputBuscar) inputBuscar.value = '';

  // Clear persona_id and _locked_fields
  const elPid = bodyEl.querySelector('[data-modal="persona_id"]');
  if (elPid) elPid.value = '';
  const elLocked = bodyEl.querySelector('[data-modal="_locked_fields"]');
  if (elLocked) elLocked.value = '[]';

  // Clear and unlock all data-modal fields
  bodyEl.querySelectorAll('[data-modal]').forEach(el => {
    const field = el.dataset.modal;
    if (field === 'persona_id' || field === '_locked_fields') return;

    if (el.type === 'radio') {
      // Reset radios: check first one (Cédula) by default
      el.disabled = false;
      el.checked = el.value === 'Cédula';
    } else if (el.tagName === 'SELECT') {
      // Reset selects to first option, special defaults
      if (field === 'caracter') { el.value = 'HEREDERO'; }
      else if (field === 'premuerto') { el.value = 'NO'; }
      else if (field === 'letra_cedula') { el.value = 'V'; }
      else { el.selectedIndex = 0; }
      el.disabled = false;
      el.style.backgroundColor = '';
    } else {
      el.value = '';
      el.disabled = false;
      el.style.backgroundColor = '';
    }
  });

  // Reset document type UI (show cédula, hide pasaporte)
  const wrapCed = bodyEl.querySelector('[id^="wrap-her"][id$="-cedula"]');
  const wrapPasa = bodyEl.querySelector('[id^="wrap-her"][id$="-pasaporte"]');
  if (wrapCed) wrapCed.style.display = '';
  if (wrapPasa) wrapPasa.style.display = 'none';

  // Hide fecha fallecimiento
  const bloqueFall = bodyEl.querySelector('#bloqueFallecimiento') || bodyEl.querySelector('#bloqueFallecimiento2');
  if (bloqueFall) bloqueFall.style.display = 'none';

  // Reset label
  const lblDoc = bodyEl.querySelector('.cc-field--doc label');
  if (lblDoc) lblDoc.innerHTML = 'CÉDULA';
}

export function closeModal() {
  $('#genericModal').classList.remove('is-open');
  UIState.currentModalType = null;
  UIState.editIndex = null;
}

export async function saveModal() {
  const config = MODAL_CONFIGS[UIState.currentModalType];
  if (!config) return;
  const form = config.collect();

  // Clear previous inline errors
  const errContainer = document.getElementById('modalHerederoErrors');
  const errList = document.getElementById('modalHerederoErrorsList');
  if (errContainer) errContainer.classList.remove('is-visible');

  if (config.validate) {
    const error = config.validate(form);
    if (error) {
      // Show inline for heredero modals
      if ((UIState.currentModalType === 'heredero' || UIState.currentModalType === 'heredero_premuerto') && errContainer && errList) {
        errList.innerHTML = `<li>${error}</li>`;
        errContainer.classList.add('is-visible');
        errContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        // Auto-clear on next input
        const bodyEl = $('#modalBody');
        if (bodyEl) {
          const clearHandler = () => {
            errContainer.classList.remove('is-visible');
            bodyEl.removeEventListener('input', clearHandler);
            bodyEl.removeEventListener('change', clearHandler);
          };
          bodyEl.addEventListener('input', clearHandler, { once: true });
          bodyEl.addEventListener('change', clearHandler, { once: true });
        }
      } else {
        showToast(error);
      }
      return;
    }
  }

  await config.save(form);
  closeModal();
}

export function removeItem(collection, index) {
  // Si es un heredero premuerto, borrar sus herederos del premuerto en cascada
  if (collection === 'herederos') {
    const heredero = caseData.herederos[index];
    if (heredero && heredero.premuerto === 'SI' && heredero._uid) {
      // Eliminar todos los herederos_premuertos que pertenecen a este premuerto (linked by _uid)
      for (let i = caseData.herederos_premuertos.length - 1; i >= 0; i--) {
        const hp = caseData.herederos_premuertos[i];
        if (hp.premuerto_padre_id === heredero._uid) {
          caseData.herederos_premuertos.splice(i, 1);
        }
      }
    }
    // Limpiar calculo_manual del heredero eliminado
    if (heredero && heredero._uid && Array.isArray(caseData.calculo_manual)) {
      const cmIdx = caseData.calculo_manual.findIndex(cm => cm._uid === heredero._uid);
      if (cmIdx !== -1) caseData.calculo_manual.splice(cmIdx, 1);
    }
  }
  caseData[collection].splice(index, 1);
  if (collection === 'herederos') {
    renderHerederos();
    renderHerederosPremuertos();
  }
  else renderInventario();
}

export function removeMueble(category, index) {
  if (caseData.bienes_muebles[category]) {
    caseData.bienes_muebles[category].splice(index, 1);
  }
  renderInventario();
}
