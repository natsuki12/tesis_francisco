import { $, $$, show, hide, formatBs, showToast } from '../../global/utils.js';
import { caseData } from './state.js';
import { openModal, removeItem } from './modal.js';
import { getCatalogs } from '../../global/catalogos.js';

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
      <input type="checkbox" ${checked ? 'checked' : ''} data-herencia-id="${id}" data-herencia-nombre="${tipo.nombre || ''}">
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
          caseData.herencia.tipos.push({ tipo_herencia_id: id, nombre: cb.dataset.herenciaNombre || '' });
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

      // Validación en tiempo real: fecha_testamento no puede superar fecha de fallecimiento
      if (field === 'fecha_testamento') {
        const fechaFall = caseData.causante?.fecha_fallecimiento;
        if (fechaFall && el.value && el.value > fechaFall) {
          showToast('La fecha del testamento no puede ser posterior a la fecha de fallecimiento del causante.');
          el.value = '';
          if (item) item[field] = '';
          return;
        }
      }

      if (item) {
        item[field] = el.value;
      }
    });
  });

  // Aplicar max en el date picker: fecha_fallecimiento o fecha actual como límite
  const actualizarMaxTestamento = () => {
    const fechaFall = caseData.causante?.fecha_fallecimiento;
    // Si no hay fecha de fallecimiento, usar la fecha actual como límite
    const maxDate = fechaFall || new Date().toISOString().split('T')[0];
    extrasContainer.querySelectorAll('[data-herencia-extra="fecha_testamento"]').forEach(el => {
      el.setAttribute('max', maxDate);
      // Si la fecha actual excede el nuevo max, limpiarla
      if (el.value && el.value > maxDate) {
        el.value = '';
        const id = el.dataset.herenciaRef;
        const item = caseData.herencia.tipos.find(t => t.tipo_herencia_id == id);
        if (item) {
          item.fecha_testamento = '';
          showToast('La fecha del testamento fue limpiada porque excede la fecha de fallecimiento.');
        }
      }
    });
  };
  actualizarMaxTestamento();

  // Engancharse al input de fecha_fallecimiento para actualizar max dinámicamente
  const inputFechaFall = document.querySelector('[data-bind="causante.fecha_fallecimiento"]');
  if (inputFechaFall && !inputFechaFall._herenciaMaxLinked) {
    inputFechaFall._herenciaMaxLinked = true;
    inputFechaFall.addEventListener('change', actualizarMaxTestamento);
  }
}

export function renderHerederos() {
  const empty = $('#herederosEmpty');
  const content = $('#herederosContent');
  const tbody = $('#herederosTableBody');
  const cardPremuertos = $('#card_premuertos');

  if (caseData.herederos.length === 0) {
    show(empty); hide(content);
    if (cardPremuertos) hide(cardPremuertos);
    return;
  }
  hide(empty); show(content);

  if (cardPremuertos) {
    const hasPremuerto = caseData.herederos.some(h => h.premuerto === 'SI');
    if (hasPremuerto) show(cardPremuertos);
    else hide(cardPremuertos);
  }

  tbody.innerHTML = caseData.herederos.map((h, i) => {
    const pName = getCatalogs().parentescos.find(p => p.parentesco_id == h.parentesco_id)?.nombre || '<em style="color:var(--cc-slate-300)">Sin definir</em>';
    return `
    <tr>
      <td>${h.nombres || ''} ${h.apellidos || ''}</td>
      <td>${h.letra_cedula || 'V'}-${h.cedula || ''}</td>
      <td><span class="status-badge ${h.caracter === 'HEREDERO' ? 'status-active' : 'status-review'}">${h.caracter || ''}</span></td>
      <td>${pName}</td>
      <td><span class="status-badge ${h.premuerto === 'SI' ? 'status-danger' : 'status-draft'}">${h.premuerto || 'NO'}</span></td>
      <td>
        <div class="cc-td-actions">
          <button class="btn-icon" onclick="CC.openModal('heredero', ${i})"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>
          <button class="btn-danger-ghost" onclick="CC.removeItem('herederos', ${i})"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></button>
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
      const padre = caseData.herederos.find(her => her._uid === h.premuerto_padre_id);
      const padreLabel = padre ? `${padre.nombres} ${padre.apellidos}` : '<em style="color:var(--cc-slate-300)">Ninguno</em>';
      return `
    <tr>
      <td>${h.nombres || ''} ${h.apellidos || ''}</td>
      <td>${h.letra_cedula || 'V'}-${h.cedula || ''}</td>
      <td><span class="status-badge ${h.caracter === 'HEREDERO' ? 'status-active' : 'status-review'}">${h.caracter || ''}</span></td>
      <td>${pName}</td>
      <td><span class="status-badge status-draft">${padreLabel}</span></td>
      <td>
        <div class="cc-td-actions">
          <button class="btn-icon" onclick="CC.openModal('heredero_premuerto', ${i})"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>
          <button class="btn-danger-ghost" onclick="CC.removeItem('herederos_premuertos', ${i})"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></button>
        </div>
      </td>
    </tr>
  `;
  }).join('');
}

export function initRepresentanteLogic() {
  // No radio buttons to handle anymore — Cédula and RIF fields are always visible.
  // Nothing to toggle.
}
