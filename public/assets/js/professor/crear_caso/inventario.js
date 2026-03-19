import { $, show, hide, formatBs } from '../../global/utils.js';
import { caseData, UIState } from './state.js';
import { openModal, removeItem, removeMueble, closeModal } from './modal.js';
import { getCatalogs } from '../../global/catalogos.js';

// Capture local ref to parseDecimal at module-load time (tamper-proof)
const _parseDecimal = typeof parseDecimal === 'function'
    ? parseDecimal
    : (v) => { const n = parseFloat(String(v).replace(/\./g, '').replace(',', '.')); return isNaN(n) ? 0 : n; };

/** Reusable total bar for inventory sections */
function totalBar(label, items) {
  const total = items.reduce((sum, it) => sum + _parseDecimal(it.valor_declarado), 0);
  return `<div style="display:flex;justify-content:flex-end;align-items:center;padding:12px 16px;margin-top:8px;background:var(--gray-50);border-radius:10px;border:1px solid var(--gray-200);">
    <span style="font-size:13px;color:var(--gray-500);margin-right:8px;">${label}:</span>
    <span style="font-size:15px;font-weight:700;color:var(--gray-800);">${formatBs(total)}</span>
  </div>`;
}

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

  // Litigiosos count
  const litInm = caseData.bienes_inmuebles.filter(b => b.bien_litigioso === 'Si').length;
  const litMue = Object.values(caseData.bienes_muebles).reduce((s, arr) =>
    s + (Array.isArray(arr) ? arr.filter(b => b.bien_litigioso === 'Si').length : 0), 0);
  el('countLitigiosos', litInm + litMue);

  // Desgravámenes count (auto-derived)
  el('countDesgravamenes', collectDesgravamenes().length);
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
      totalBar('Total Inmuebles', caseData.bienes_inmuebles) +
      `<button class="btn btn-secondary btn-sm cc-mt" onclick="CC.openModal('inmueble')"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Agregar Inmueble</button>`;
  }

  // Muebles
  renderMueblesList();

  // Deudas
  renderListSection('pasivos_deuda', 'deudasEmpty', 'deudasList', 'pasivo_deuda', 'Agregar Deuda', item => {
    if (item.tipo_pasivo_deuda_id) {
      const tipo = (getCatalogs().tiposPasivoDeuda || []).find(t => t.tipo_pasivo_deuda_id == item.tipo_pasivo_deuda_id);
      if (tipo) return tipo.nombre;
    }
    return item.subtipo || item.tipo_deuda || 'Deuda';
  }, 'Total Deudas');
  // Gastos
  renderListSection('pasivos_gastos', 'gastosEmpty', 'gastosList', 'pasivo_gasto', 'Agregar Gasto', item => {
    if (item.tipo_pasivo_gasto_id) {
      const tipo = (getCatalogs().tiposPasivoGasto || []).find(t => t.tipo_pasivo_gasto_id == item.tipo_pasivo_gasto_id);
      if (tipo) return tipo.nombre;
    }
    return item.tipo_gasto || 'Gasto';
  }, 'Total Gastos');
  // Exenciones
  renderListSection('exenciones', 'exencionesEmpty', 'exencionesList', 'exencion', 'Agregar Exención', item => item.tipo_exencion || 'Exención', 'Total Exenciones');
  // Exoneraciones
  renderListSection('exoneraciones', 'exoneracionesEmpty', 'exoneracionesList', 'exoneracion', 'Agregar Exoneración', item => item.tipo_exoneracion || 'Exoneración', 'Total Exoneraciones');
  // Litigiosos (read-only)
  renderLitigiosos();
  // Desgravámenes (read-only, auto-derived)
  renderDesgravamenes();
}

function renderListSection(dataKey, emptyId, listId, modalType, btnText, nameGetter, totalLabel) {
  const empty = document.getElementById(emptyId);
  const list = document.getElementById(listId);
  const items = caseData[dataKey] || [];
  if (items.length === 0) {
    show(empty); hide(list);
  } else {
    hide(empty); show(list);
    list.innerHTML = items.map((item, i) => renderItemCard(item, dataKey, i, '💼', nameGetter(item))).join('') +
      (totalLabel ? totalBar(totalLabel, items) : '') +
      `<button class="btn btn-secondary btn-sm cc-mt" onclick="CC.openModal('${modalType}')"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> ${btnText}</button>`;
  }
}

function renderItemCard(item, collection, index, emoji, name) {
  let displayName = name || item.descripcion || 'Sin tipo';

  // Handle array of IDs for inmuebles
  if (collection === 'bienes_inmuebles' && Array.isArray(item.tipo_bien_inmueble_id)) {
    const catalogs = getCatalogs().tiposBienInmueble || [];
    const names = item.tipo_bien_inmueble_id.map(id => {
      const found = catalogs.find(t => t.tipo_bien_inmueble_id == id);
      return found ? found.nombre : '';
    }).filter(n => n);

    if (names.length > 0) {
      displayName = names.join(', ');
    }
  } else if (item.tipo) {
    displayName = item.tipo;
  }
  const inmuebleDetails = collection === 'bienes_inmuebles' ? `
        <div class="cc-item-card__meta" style="margin-top: 4px; font-size: 11px;">
          ${item.direccion ? `<span style="max-width:200px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="${item.direccion}">📍 ${item.direccion}</span>` : ''}
          ${item.nro_registro ? `<span>📑 Reg: ${item.nro_registro} ${item.fecha_registro ? `(${item.fecha_registro})` : ''}</span>` : ''}
        </div>` : '';

  const deudaDetails = collection === 'pasivos_deuda' ? `
        <div class="cc-item-card__meta" style="margin-top: 4px; font-size: 11px;">
          ${item.banco_id ? `<span style="max-width:200px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="Institución Bancaria">🏦 ${(getCatalogs().bancos.find(b => b.banco_id == item.banco_id) || {}).nombre || ''}</span>` : ''}
          ${item.numero_tdc ? `<span style="max-width:200px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">💳 TDC: ${item.numero_tdc}</span>` : ''}
        </div>` : '';

  const editButton = collection === 'bienes_inmuebles'
    ? `<button class="btn-icon" onclick="CC.openModal('inmueble', ${index})"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>`
    : collection === 'pasivos_deuda'
      ? `<button class="btn-icon" onclick="CC.openModal('pasivo_deuda', ${index})"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>`
      : collection === 'pasivos_gastos'
        ? `<button class="btn-icon" onclick="CC.openModal('pasivo_gasto', ${index})"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>`
        : collection === 'exenciones'
          ? `<button class="btn-icon" onclick="CC.openModal('exencion', ${index})"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>`
          : collection === 'exoneraciones'
            ? `<button class="btn-icon" onclick="CC.openModal('exoneracion', ${index})"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>`
            : '';

  return `<div class="cc-item-card">
    <div class="cc-item-card__left">
      <div class="cc-item-card__icon">${emoji}</div>
      <div>
        <div class="cc-item-card__name">${displayName}</div>
        <div class="cc-item-card__meta">
          <span>${item.porcentaje || 100}%</span>
          ${item.vivienda_principal === 'Si' ? '<span class="status-badge status-completed">Viv. Principal</span>' : ''}
          ${item.bien_litigioso === 'Si' ? '<span class="status-badge status-danger">Litigioso</span>' : ''}
        </div>
        ${inmuebleDetails}
        ${deudaDetails}
      </div>
    </div>
    <div class="cc-item-card__right">
      <span class="cc-item-card__value">${formatBs(item.valor_declarado)}</span>
      ${editButton}
      <button class="btn-danger-ghost" onclick="CC.removeItem('${collection}', ${index})"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></button>
    </div>
  </div>`;
}

function renderMuebleSubtabs() {
  const container = $('#muebleSubtabs');
  if (!container) return;
  const cats = getCatalogs().categoriasBienMueble || [];

  if (!UIState.currentSubTab && cats.length > 0) {
    UIState.currentSubTab = cats[0].categoria_bien_mueble_id;
  }

  container.innerHTML = cats.map(c => {
    const id = c.categoria_bien_mueble_id;
    const count = (caseData.bienes_muebles[id] || []).length;
    const isActive = (UIState.currentSubTab == id);
    return `<button class="cc-subtab${isActive ? ' is-active' : ''}" data-subtab="${id}">
      <span class="cc-subtab__label">${c.nombre}</span>
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
  const cats = getCatalogs().categoriasBienMueble || [];
  const cat = cats.find(c => c.categoria_bien_mueble_id == UIState.currentSubTab);
  const title = $('#muebleSubtitle');
  const desc = $('#muebleSubdesc');
  const emptyEl = $('#mueblesEmpty');
  const emptyText = $('#mueblesEmptyText');
  const list = $('#mueblesList');

  if (title) title.textContent = cat ? cat.nombre : '';
  if (desc) desc.textContent = cat ? `Bienes muebles de tipo "${cat.nombre}"` : '';
  if (emptyText) emptyText.textContent = cat ? `No hay registros de ${cat.nombre} ` : '';

  const items = caseData.bienes_muebles[UIState.currentSubTab] || [];
  if (items.length === 0) {
    show(emptyEl); hide(list);
  } else {
    hide(emptyEl); show(list);
    list.innerHTML = items.map((b, i) => {
      // Resolve tipo name from catalog
      const tipos = getCatalogs().tiposBienMueble[UIState.currentSubTab] || [];
      const tipoObj = tipos.find(t => t.tipo_bien_mueble_id == b.tipo_bien_mueble_id);
      const tipoName = tipoObj ? tipoObj.nombre : '';
      const displayName = tipoName || b.descripcion || 'Sin descripción';

      // Category-specific meta
      const catObj = cats.find(c => c.categoria_bien_mueble_id == UIState.currentSubTab);
      const catName = catObj ? catObj.nombre.toLowerCase() : '';
      let extraMeta = '';

      if (catName.includes('banco') && b.banco_id) {
        const banco = (getCatalogs().bancos || []).find(x => x.banco_id == b.banco_id);
        extraMeta += banco ? `<span>🏦 ${banco.nombre}</span>` : '';
        if (b.numero_cuenta) extraMeta += `<span>N° ${b.numero_cuenta}</span>`;
      } else if (catName.includes('transporte')) {
        if (b.marca) extraMeta += `<span>${b.marca} ${b.modelo || ''} ${b.anio || ''}</span>`;
        if (b.serial_placa) extraMeta += `<span>🔖 ${b.serial_placa}</span>`;
      } else if (catName.includes('seguro')) {
        if (b.razon_social) extraMeta += `<span>📋 ${b.razon_social}</span>`;
        if (b.numero_prima) extraMeta += `<span>Prima: ${b.numero_prima}</span>`;
      } else if (catName.includes('acciones') || catName.includes('caja de ahorro')) {
        if (b.razon_social) extraMeta += `<span>🏢 ${b.razon_social}</span>`;
        if (b.rif_empresa) extraMeta += `<span>RIF: ${b.rif_empresa}</span>`;
      } else if (catName.includes('prestaciones')) {
        if (b.razon_social) extraMeta += `<span>🏢 ${b.razon_social}</span>`;
        if (b.posee_banco === 'SI') {
          const banco = (getCatalogs().bancos || []).find(x => x.banco_id == b.banco_id);
          if (banco) extraMeta += `<span>🏦 ${banco.nombre}</span>`;
        }
      } else if (catName.includes('cobrar')) {
        if (b.apellidos_nombres) extraMeta += `<span>👤 ${b.apellidos_nombres}</span>`;
      } else if (catName.includes('bonos')) {
        if (b.tipo_bonos) extraMeta += `<span>${b.tipo_bonos}</span>`;
      } else if (catName.includes('semovientes')) {
        const tipo = (getCatalogs().tiposSemoviente || []).find(x => x.tipo_semoviente_id == b.tipo_semoviente_id);
        if (tipo) extraMeta += `<span>${tipo.nombre}</span>`;
        if (b.cantidad) extraMeta += `<span>Cant: ${b.cantidad}</span>`;
      } else if (catName.includes('compra')) {
        if (b.nombre_oferente) extraMeta += `<span>👤 ${b.nombre_oferente}</span>`;
      }

      return `<div class="cc-item-card">
        <div class="cc-item-card__left">
          <div>
            <div class="cc-item-card__name">${displayName}</div>
            <div class="cc-item-card__meta">
              <span>${b.porcentaje || 100}%</span>
              ${b.bien_litigioso === 'Si' ? '<span class="status-badge status-danger">Litigioso</span>' : ''}
            </div>
            ${extraMeta ? `<div class="cc-item-card__meta" style="margin-top:4px;font-size:11px;">${extraMeta}</div>` : ''}
          </div>
        </div>
        <div class="cc-item-card__right">
          <span class="cc-item-card__value">${formatBs(b.valor_declarado)}</span>
          <button class="btn-icon" onclick="CC.openModal('mueble', ${i})"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>
          <button class="btn-danger-ghost" onclick="CC.removeMueble('${UIState.currentSubTab}', ${i})"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></button>
        </div>
      </div>`;
    }).join('') +
      totalBar('Total ' + (cats.find(c => c.categoria_bien_mueble_id == UIState.currentSubTab)?.nombre || 'Muebles'), items) +
      `<button class="btn btn-secondary btn-sm cc-mt" onclick="CC.openModal('mueble')"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19" /><line x1="5" y1="12" x2="19" y2="12" /></svg> Agregar</button>`;
  }
}

/**
 * Collects items that qualify as desgravámenes (auto-derived):
 * 1. Inmuebles with vivienda_principal = 'Si'
 * 2. Seguros tipo Montepío or Seguro de Vida
 * 3. Prestaciones Sociales without bank account
 */
export function collectDesgravamenes() {
  const items = [];
  const cats = getCatalogs().categoriasBienMueble || [];

  // 1. Inmuebles con vivienda principal
  caseData.bienes_inmuebles.forEach(b => {
    if (b.vivienda_principal === 'Si') {
      const tiposInm = getCatalogs().tiposBienInmueble || [];
      let tipeName = 'Inmueble';
      if (Array.isArray(b.tipo_bien_inmueble_id)) {
        const names = b.tipo_bien_inmueble_id.map(id => {
          const found = tiposInm.find(t => t.tipo_bien_inmueble_id == id);
          return found ? found.nombre : '';
        }).filter(n => n);
        if (names.length > 0) tipeName = names.join(', ');
      }
      const desc = `${b.porcentaje || 100}% de ${b.descripcion || tipeName}`;
      items.push({
        _displayName: tipeName,
        _origin: 'Bien Inmueble — Vivienda Principal',
        _emoji: '🏠',
        _desc: desc,
        valor_declarado: b.valor_declarado,
        vivienda_principal: 'Si',
        bien_litigioso: b.bien_litigioso || 'No'
      });
    }
  });

  // 2 & 3. Muebles: Seguros (Montepío/Seguro de Vida) y Prestaciones Sociales
  Object.entries(caseData.bienes_muebles).forEach(([catId, arr]) => {
    if (!Array.isArray(arr)) return;
    const cat = cats.find(c => c.categoria_bien_mueble_id == catId);
    if (!cat) return;
    const catName = cat.nombre.toLowerCase();

    arr.forEach(b => {
      const tipos = getCatalogs().tiposBienMueble[catId] || [];
      const tipoObj = tipos.find(t => t.tipo_bien_mueble_id == b.tipo_bien_mueble_id);
      const tipoName = tipoObj ? tipoObj.nombre : '';
      const tipoLower = tipoName.toLowerCase();

      // Seguros: Montepío o Seguro de Vida
      if (catName.includes('seguro') && (tipoLower.includes('montepí') || tipoLower.includes('seguro de vida'))) {
        const desc = `${b.porcentaje || 100}% de ${b.descripcion || tipoName}. ${b.razon_social ? `Aseguradora: ${b.razon_social}` : ''}`;
        items.push({
          _displayName: tipoName || 'Seguro',
          _origin: `Seguro — ${tipoName}`,
          _emoji: '🛡️',
          _desc: desc,
          valor_declarado: b.valor_declarado,
          vivienda_principal: 'No',
          bien_litigioso: b.bien_litigioso || 'No'
        });
      }

      // Prestaciones Sociales sin cuenta bancaria
      if (catName.includes('prestaciones') && b.posee_banco !== 'SI') {
        const desc = `${b.porcentaje || 100}% de ${b.descripcion || 'Prestaciones Sociales'}. ${b.razon_social ? `Empresa: ${b.razon_social}` : ''}`;
        items.push({
          _displayName: tipoName || 'Prestaciones Sociales',
          _origin: 'Prestaciones Sociales',
          _emoji: '💼',
          _desc: desc,
          valor_declarado: b.valor_declarado,
          vivienda_principal: 'No',
          bien_litigioso: b.bien_litigioso || 'No'
        });
      }
    });
  });

  return items;
}

function renderDesgravamenes() {
  const empty = document.getElementById('desgravamenesEmpty');
  const list = document.getElementById('desgravamenesList');
  if (!empty || !list) return;

  const items = collectDesgravamenes();

  if (items.length === 0) {
    show(empty); hide(list);
  } else {
    hide(empty); show(list);
    list.innerHTML = items.map(item => `<div class="cc-item-card">
      <div class="cc-item-card__left">
        <div class="cc-item-card__icon">${item._emoji}</div>
        <div>
          <div class="cc-item-card__name">${item._displayName}</div>
          <div class="cc-item-card__meta">
            <span style="color:var(--gray-500);font-size:11px;">${item._origin}</span>
            ${item.vivienda_principal === 'Si' ? '<span class="status-badge status-completed">Viv. Principal</span>' : ''}
            ${item.bien_litigioso === 'Si' ? '<span class="status-badge status-danger">Litigioso</span>' : ''}
          </div>
          <div class="cc-item-card__meta" style="margin-top:4px;font-size:11px;">
            <span style="max-width:400px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="${item._desc}">${item._desc}</span>
          </div>
        </div>
      </div>
      <div class="cc-item-card__right">
        <span class="cc-item-card__value">${formatBs(item.valor_declarado)}</span>
      </div>
    </div>`).join('') +
      totalBar('Total Desgravámenes', items);
  }
}

function renderLitigiosos() {
  const empty = document.getElementById('litigiososEmpty');
  const list = document.getElementById('litigiososList');
  if (!empty || !list) return;

  // Collect all litigious items
  const items = [];

  // From inmuebles
  caseData.bienes_inmuebles.forEach(b => {
    if (b.bien_litigioso === 'Si') {
      const catalogs = getCatalogs().tiposBienInmueble || [];
      let name = 'Inmueble';
      if (Array.isArray(b.tipo_bien_inmueble_id)) {
        const names = b.tipo_bien_inmueble_id.map(id => {
          const found = catalogs.find(t => t.tipo_bien_inmueble_id == id);
          return found ? found.nombre : '';
        }).filter(n => n);
        if (names.length > 0) name = names.join(', ');
      }
      items.push({ ...b, _displayName: name, _origin: 'Bien Inmueble', _emoji: '🏠' });
    }
  });

  // From muebles
  const catsMueble = getCatalogs().categoriasBienMueble || [];
  Object.entries(caseData.bienes_muebles).forEach(([catId, arr]) => {
    if (!Array.isArray(arr)) return;
    const cat = catsMueble.find(c => c.categoria_bien_mueble_id == catId);
    const catName = cat ? cat.nombre : 'Bien Mueble';
    arr.forEach(b => {
      if (b.bien_litigioso === 'Si') {
        const tipos = getCatalogs().tiposBienMueble[catId] || [];
        const tipoObj = tipos.find(t => t.tipo_bien_mueble_id == b.tipo_bien_mueble_id);
        const name = tipoObj ? tipoObj.nombre : (b.descripcion || catName);
        items.push({ ...b, _displayName: name, _origin: catName, _emoji: '🚗' });
      }
    });
  });

  if (items.length === 0) {
    show(empty); hide(list);
  } else {
    hide(empty); show(list);
    list.innerHTML = items.map((item, i) => `<div class="cc-item-card">
      <div class="cc-item-card__left">
        <div class="cc-item-card__icon">${item._emoji}</div>
        <div>
          <div class="cc-item-card__name">${item._displayName}</div>
          <div class="cc-item-card__meta">
            <span class="status-badge status-danger">Litigioso</span>
            <span style="color:var(--gray-500);font-size:11px;">${item._origin}</span>
          </div>
          <div class="cc-item-card__meta" style="margin-top:4px;font-size:11px;">
            <span>📋 Exp: ${item.numero_expediente || 'N/A'}</span>
            <span>⚖️ ${item.tribunal_causa || 'N/A'}</span>
          </div>
        </div>
      </div>
      <div class="cc-item-card__right">
        <button class="btn-icon" title="Ver datos litigiosos" onclick="CC.viewLitigioso(${i})">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
            <circle cx="12" cy="12" r="3"/>
          </svg>
        </button>
      </div>
    </div>`).join('');
  }

  // Store items for viewLitigioso access
  window._litigiososItems = items;
}

export function viewLitigioso(index) {
  const items = window._litigiososItems || [];
  const item = items[index];
  if (!item) return;

  const overlay = document.getElementById('genericModal');
  if (!overlay) return;

  const titleEl = overlay.querySelector('.cc-modal__title');
  const bodyEl = overlay.querySelector('.cc-modal__body');
  const footerEl = overlay.querySelector('.cc-modal__footer');
  const modal = overlay.querySelector('.cc-modal');

  if (titleEl) titleEl.textContent = `Datos Litigiosos — ${item._displayName}`;
  if (modal) modal.style.width = '520px';

  const field = (label, value) => `
    <div style="margin-bottom:12px;">
      <label style="font-weight:600;color:var(--gray-500);font-size:12px;text-transform:uppercase;letter-spacing:0.5px;display:block;margin-bottom:4px;">${label}</label>
      <div style="padding:10px 14px;background:var(--gray-50);border-radius:8px;font-size:14px;color:var(--gray-700);">${value || '—'}</div>
    </div>`;

  if (bodyEl) {
    bodyEl.innerHTML = `
      <div>
        ${field('Origen', item._origin)}
        ${field('Número de Expediente', item.numero_expediente)}
        ${field('Tribunal de la Causa', item.tribunal_causa)}
        ${field('Partes en el Juicio', item.partes_juicio)}
        ${field('Estado del Juicio', item.estado_juicio)}
      </div>`;
  }

  if (footerEl) {
    footerEl.innerHTML = `<button class="btn btn-secondary" onclick="CC.closeModal()">Cerrar</button>`;
  }

  overlay.classList.add('is-open');
}
