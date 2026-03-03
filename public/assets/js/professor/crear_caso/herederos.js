import { $, $$, show, hide, formatBs } from './utils.js';
import { caseData, TIPOS_HERENCIA } from './state.js';
import { openModal, removeItem } from './modal.js';

// Asignamos callbacks globales para el HTML onClick
window.CC = window.CC || {};

export function renderHerenciaCheckboxes() {
    const container = $('#herenciaCheckboxes');
    if (!container) return;
    container.innerHTML = TIPOS_HERENCIA.map(tipo => {
        const checked = caseData.herencia.tipos.includes(tipo);
        return `<label class="cc-check-card${checked ? ' is-selected' : ''}">
      <input type="checkbox" ${checked ? 'checked' : ''} data-herencia="${tipo}">
      <span>${tipo}</span>
    </label>`;
    }).join('');

    container.querySelectorAll('input[data-herencia]').forEach(cb => {
        cb.addEventListener('change', () => {
            const tipo = cb.dataset.herencia;
            const card = cb.closest('.cc-check-card');
            if (cb.checked) {
                if (!caseData.herencia.tipos.includes(tipo)) caseData.herencia.tipos.push(tipo);
                card.classList.add('is-selected');
            } else {
                caseData.herencia.tipos = caseData.herencia.tipos.filter(t => t !== tipo);
                card.classList.remove('is-selected');
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

    tbody.innerHTML = caseData.herederos.map((h, i) => `
    <tr>
      <td>${h.nombres || ''} ${h.apellidos || ''}</td>
      <td>${h.tipo_cedula || 'V'}-${h.cedula || ''}</td>
      <td><span class="cc-badge ${h.caracter === 'HEREDERO' ? 'cc-badge--blue' : 'cc-badge--amber'}">${h.caracter || ''}</span></td>
      <td>${h.parentesco || '<em style="color:var(--cc-slate-300)">Sin definir</em>'}</td>
      <td><span class="cc-badge ${h.premuerto === 'SI' ? 'cc-badge--red' : 'cc-badge--slate'}">${h.premuerto || 'NO'}</span></td>
      <td>
        <div class="cc-td-actions">
          <button class="cc-btn--icon-edit" onclick="CC.openModal('heredero', ${i})"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>
          <button class="cc-btn--icon-danger" onclick="CC.removeItem('herederos', ${i})"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></button>
        </div>
      </td>
    </tr>
  `).join('');
}
