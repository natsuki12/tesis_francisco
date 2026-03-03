import { $, show, hide, formatBs } from './utils.js';
import { caseData, CATEGORIAS_MUEBLE, UIState } from './state.js';
import { openModal, removeItem, removeMueble } from './modal.js';

export function renderInventario() {
    updateTabCounts();
    renderCurrentTab();
    renderMuebleSubtabs();
}

function updateTabCounts() {
    const el = (id, n) => { const e = document.getElementById(id); if (e) e.textContent = n; };
    el('countInmuebles', caseData.bienes_inmuebles.length);
    const mTotal = Object.values(caseData.bienes_muebles).reduce((s, arr) => s + (Array.isArray(arr) ? arr.length : 0), 0);
    el('countMuebles', mTotal);
    el('countPasivos', (caseData.pasivos_deuda.length + caseData.pasivos_gastos.length));
    el('countExenciones', (caseData.exenciones.length + caseData.exoneraciones.length));
}

function renderCurrentTab() {
    // Inmuebles
    const iEmpty = $('#inmueblesEmpty');
    const iList = $('#inmueblesList');
    if (caseData.bienes_inmuebles.length === 0) {
        show(iEmpty); hide(iList);
    } else {
        hide(iEmpty); show(iList);
        iList.innerHTML = caseData.bienes_inmuebles.map((b, i) => renderItemCard(b, 'bienes_inmuebles', i, '🏠')).join('') +
            `<button class="cc-btn cc-btn--soft cc-mt" onclick="CC.openModal('inmueble')"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Agregar Inmueble</button>`;
    }

    // Muebles
    renderMueblesList();

    // Deudas
    renderListSection('pasivos_deuda', 'deudasEmpty', 'deudasList', 'pasivo_deuda', 'Agregar Deuda', item => item.subtipo || item.tipo_deuda || 'Deuda');
    // Gastos
    renderListSection('pasivos_gastos', 'gastosEmpty', 'gastosList', 'pasivo_gasto', 'Agregar Gasto', item => item.tipo_gasto || 'Gasto');
    // Exenciones
    renderListSection('exenciones', 'exencionesEmpty', 'exencionesList', 'exencion', 'Agregar Exención', item => item.tipo_exencion || 'Exención');
    // Exoneraciones
    renderListSection('exoneraciones', 'exoneracionesEmpty', 'exoneracionesList', 'exoneracion', 'Agregar Exoneración', item => item.tipo_exoneracion || 'Exoneración');
}

function renderListSection(dataKey, emptyId, listId, modalType, btnText, nameGetter) {
    const empty = document.getElementById(emptyId);
    const list = document.getElementById(listId);
    const items = caseData[dataKey] || [];
    if (items.length === 0) {
        show(empty); hide(list);
    } else {
        hide(empty); show(list);
        list.innerHTML = items.map((item, i) => renderItemCard(item, dataKey, i, '💼', nameGetter(item))).join('') +
            `<button class="cc-btn cc-btn--soft cc-mt" onclick="CC.openModal('${modalType}')"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> ${btnText}</button>`;
    }
}

function renderItemCard(item, collection, index, emoji, name) {
    const displayName = name || item.tipo || item.descripcion || 'Sin tipo';
    return `<div class="cc-item-card">
    <div class="cc-item-card__left">
      <div class="cc-item-card__icon">${emoji}</div>
      <div>
        <div class="cc-item-card__name">${displayName}</div>
        <div class="cc-item-card__meta">
          <span>${item.porcentaje || 100}%</span>
          ${item.vivienda_principal === 'Si' ? '<span class="cc-badge cc-badge--green">Viv. Principal</span>' : ''}
          ${item.bien_litigioso === 'Si' ? '<span class="cc-badge cc-badge--red">Litigioso</span>' : ''}
        </div>
      </div>
    </div>
    <div class="cc-item-card__right">
      <span class="cc-item-card__value">${formatBs(item.valor_declarado)}</span>
      <button class="cc-btn--icon-danger" onclick="CC.removeItem('${collection}', ${index})"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></button>
    </div>
  </div>`;
}

function renderMuebleSubtabs() {
    const container = $('#muebleSubtabs');
    if (!container) return;
    container.innerHTML = CATEGORIAS_MUEBLE.map(c => {
        const count = (caseData.bienes_muebles[c.key] || []).length;
        return `<button class="cc-subtab${UIState.currentSubTab === c.key ? ' is-active' : ''}" data-subtab="${c.key}">
      <span class="cc-subtab__emoji">${c.icon}</span>
      <span class="cc-subtab__label">${c.label}</span>
      ${count > 0 ? `<span class="cc-subtab__count">${count}</span>` : ''}
    </button>`;
    }).join('');

    container.querySelectorAll('.cc-subtab').forEach(btn => {
        btn.addEventListener('click', () => {
            UIState.currentSubTab = btn.dataset.subtab;
            renderMuebleSubtabs();
            renderMueblesList();
        });
    });
}

function renderMueblesList() {
    const cat = CATEGORIAS_MUEBLE.find(c => c.key === UIState.currentSubTab);
    const title = $('#muebleSubtitle');
    const desc = $('#muebleSubdesc');
    const emptyEl = $('#mueblesEmpty');
    const emptyText = $('#mueblesEmptyText');
    const list = $('#mueblesList');

    if (title) title.textContent = cat ? cat.label : '';
    if (desc) desc.textContent = cat ? `Bienes muebles de tipo "${cat.label}"` : '';
    if (emptyText) emptyText.textContent = cat ? `No hay registros de ${cat.label}` : '';

    const items = caseData.bienes_muebles[UIState.currentSubTab] || [];
    if (items.length === 0) {
        show(emptyEl); hide(list);
    } else {
        hide(emptyEl); show(list);
        list.innerHTML = items.map((b, i) => {
            return `<div class="cc-item-card">
        <div class="cc-item-card__left">
          <div class="cc-item-card__icon">${cat ? cat.icon : '📦'}</div>
          <div>
            <div class="cc-item-card__name">${b.descripcion || 'Sin descripción'}</div>
            <div class="cc-item-card__meta"><span>${b.porcentaje || 100}%</span></div>
          </div>
        </div>
        <div class="cc-item-card__right">
          <span class="cc-item-card__value">${formatBs(b.valor_declarado)}</span>
          <button class="cc-btn--icon-danger" onclick="CC.removeMueble('${UIState.currentSubTab}', ${i})"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></button>
        </div>
      </div>`;
        }).join('') +
            `<button class="cc-btn cc-btn--soft cc-mt" onclick="CC.openModal('mueble')"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Agregar</button>`;
    }
}
