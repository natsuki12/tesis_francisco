import { $, $$, show, hide, formatBs } from './utils.js';
import { caseData } from './state.js';
import { openModal, removeItem } from './modal.js';
import { getCatalogs } from './catalogos.js';

// Asignamos callbacks globales para el HTML onClick
window.CC = window.CC || {};

export function renderHerenciaCheckboxes() {
  const container = $('#herenciaCheckboxes');
  if (!container) return;
  const tiposHerencia = getCatalogs().tiposHerencia || [];

  const extrasContainer = $('#herenciaExtras');
  if (!extrasContainer) return;

  let htmlCheckboxes = '';
  let htmlExtras = '';

  tiposHerencia.forEach(tipo => {
    const id = tipo.id;
    const exists = caseData.herencia.tipos.find(t => t.tipo_herencia_id == id);
    const checked = !!exists;

    htmlCheckboxes += `<label class="cc-check-card${checked ? ' is-selected' : ''}">
      <input type="checkbox" ${checked ? 'checked' : ''} data-herencia-id="${id}">
      <span>${tipo.nombre || 'Desconocido'}</span>
    </label>`;

    if (checked && tipo.nombre && tipo.nombre.toLowerCase().includes('testamento')) {
      htmlExtras += `<div class="cc-herencia-extra cc-mt">
                <div class="cc-field">
                    <label>Subtipo de Testamento</label>
                    <select data-herencia-extra="subtipo_testamento" data-herencia-ref="${id}">
                        <option value="">Seleccione</option>
                        <option value="Abierto" ${exists.subtipo_testamento === 'Abierto' ? 'selected' : ''}>Abierto</option>
                        <option value="Cerrado" ${exists.subtipo_testamento === 'Cerrado' ? 'selected' : ''}>Cerrado</option>
                    </select>
                </div>
                <div class="cc-field cc-mt">
                    <label>Fecha de Testamento</label>
                    <input type="date" data-herencia-extra="fecha_testamento" data-herencia-ref="${id}" value="${exists.fecha_testamento || ''}">
                </div>
            </div>`;
    }

    if (checked && tipo.nombre && tipo.nombre.toLowerCase().includes('inventario')) {
      htmlExtras += `<div class="cc-herencia-extra cc-mt">
                <div class="cc-field">
                    <label>Fecha Conclusión Inventario</label>
                    <input type="date" data-herencia-extra="fecha_conclusion_inventario" data-herencia-ref="${id}" value="${exists.fecha_conclusion_inventario || ''}">
                </div>
            </div>`;
    }
  });

  container.innerHTML = htmlCheckboxes;
  extrasContainer.innerHTML = htmlExtras;

  container.querySelectorAll('input[data-herencia-id]').forEach(cb => {
    cb.addEventListener('change', () => {
      const id = cb.dataset.herenciaId;
      if (cb.checked) {
        if (!caseData.herencia.tipos.find(t => t.tipo_herencia_id == id)) {
          caseData.herencia.tipos.push({ tipo_herencia_id: id });
        }
      } else {
        caseData.herencia.tipos = caseData.herencia.tipos.filter(t => t.tipo_herencia_id != id);
      }
      renderHerenciaCheckboxes(); // Re-render to show/hide extra fields
    });
  });

  extrasContainer.querySelectorAll('[data-herencia-extra]').forEach(el => {
    el.addEventListener('change', () => {
      const id = el.dataset.herenciaRef;
      const field = el.dataset.herenciaExtra;
      const item = caseData.herencia.tipos.find(t => t.tipo_herencia_id == id);
      if (item) {
        item[field] = el.value;
      }
    });
  });
}

export function renderHerederos() {
  const empty = $('#herederosEmpty');
  const content = $('#herederosContent');
  const tbody = $('#herederosTableBody');

  if (caseData.herederos.length === 0) {
    show(empty); hide(content);
    return;
  }
  hide(empty); show(content);

  tbody.innerHTML = caseData.herederos.map((h, i) => {
    const pName = getCatalogs().parentescos.find(p => p.parentesco_id == h.parentesco_id)?.nombre || '<em style="color:var(--cc-slate-300)">Sin definir</em>';
    return `
    <tr>
      <td>${h.nombres || ''} ${h.apellidos || ''}</td>
      <td>${h.letra_cedula || 'V'}-${h.cedula || ''}</td>
      <td><span class="cc-badge ${h.caracter === 'HEREDERO' ? 'cc-badge--blue' : 'cc-badge--amber'}">${h.caracter || ''}</span></td>
      <td>${pName}</td>
      <td><span class="cc-badge ${h.premuerto === 'SI' ? 'cc-badge--red' : 'cc-badge--slate'}">${h.premuerto || 'NO'}</span></td>
      <td>
        <div class="cc-td-actions">
          <button class="cc-btn--icon-edit" onclick="CC.openModal('heredero', ${i})"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>
          <button class="cc-btn--icon-danger" onclick="CC.removeItem('herederos', ${i})"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></button>
        </div>
      </td>
    </tr>
  `;
  }).join('');
}

export function renderHerederosPremuertos() {
  const empty = $('#herederosPremuertosEmpty');
  const content = $('#herederosPremuertosContent');
  const tbody = $('#herederosPremuertosTableBody');

  if (!empty || !content || !tbody) return;

  if (caseData.herederos_premuertos.length === 0) {
    show(empty); hide(content);
    return;
  }
  hide(empty); show(content);

  tbody.innerHTML = caseData.herederos_premuertos.map((h, i) => {
    const pName = getCatalogs().parentescos.find(p => p.parentesco_id == h.parentesco_id)?.nombre || '<em style="color:var(--cc-slate-300)">Sin definir</em>';
    return `
    <tr>
      <td>${h.nombres || ''} ${h.apellidos || ''}</td>
      <td>${h.letra_cedula || 'V'}-${h.cedula || ''}</td>
      <td><span class="cc-badge ${h.caracter === 'HEREDERO' ? 'cc-badge--blue' : 'cc-badge--amber'}">${h.caracter || ''}</span></td>
      <td>${pName}</td>
      <td><span class="cc-badge cc-badge--slate">${h.premuerto_padre_id || '<em style="color:var(--cc-slate-300)">Ninguno</em>'}</span></td>
      <td>
        <div class="cc-td-actions">
          <button class="cc-btn--icon-edit" onclick="CC.openModal('heredero_premuerto', ${i})"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>
          <button class="cc-btn--icon-danger" onclick="CC.removeItem('herederos_premuertos', ${i})"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></button>
        </div>
      </td>
    </tr>
  `;
  }).join('');
}

export function initRepresentanteLogic() {
  const radios = document.querySelectorAll('input[name="rep_tipo_doc"]');
  const selLetra = document.getElementById('sel-rep-letra');
  const lblCedula = document.getElementById('lbl-rep-cedula');

  const inpCedula = document.getElementById('inp-rep-cedula');
  const inpPasaporte = document.getElementById('inp-rep-pasaporte');

  if (!inpCedula || !inpPasaporte) return;

  // 1. Variar Cédula/RIF y la visibilidad de la letra
  const updateDocType = () => {
    const selected = document.querySelector('input[name="rep_tipo_doc"]:checked');
    if (selected && selected.value === 'Rif') {
      if (lblCedula) lblCedula.innerHTML = 'RIF <span class="req">*</span>';
      if (selLetra) selLetra.style.display = 'none'; // Ocultar para RIF
    } else {
      if (lblCedula) lblCedula.innerHTML = 'Cédula <span class="req">*</span>';
      if (selLetra) selLetra.style.display = ''; // Mostrar V/E
    }
  };

  radios.forEach(r => r.addEventListener('change', updateDocType));
  updateDocType();

  // 2. Deshabilitación cruzada Cédula vs Pasaporte
  const updateDisabling = () => {
    if (inpCedula.value.trim().length > 0) {
      inpPasaporte.disabled = true;
    } else if (inpPasaporte.value.trim().length > 0) {
      inpCedula.disabled = true;
      if (selLetra) selLetra.disabled = true;
      radios.forEach(r => r.disabled = true);
    } else {
      inpPasaporte.disabled = false;
      inpCedula.disabled = false;
      if (selLetra) selLetra.disabled = false;
      radios.forEach(r => r.disabled = false);
    }
  };

  inpCedula.addEventListener('input', updateDisabling);
  inpPasaporte.addEventListener('input', updateDisabling);
  updateDisabling();
}
