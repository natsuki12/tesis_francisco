import { $, $$ } from './utils.js';
import { caseData, TIPOS_BIEN_INMUEBLE, CATEGORIAS_MUEBLE, TIPOS_PASIVO_DEUDA, TIPOS_PASIVO_GASTO, PARENTESCOS, UIState } from './state.js';
import { renderHerederos } from './herederos.js';
import { renderInventario } from './inventario.js';

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
        <div class="cc-field"><label>Tipo Cédula</label>
          <select data-modal="tipo_cedula">
            <option value="V" ${form.tipo_cedula === 'V' ? 'selected' : ''}>V</option>
            <option value="E" ${form.tipo_cedula === 'E' ? 'selected' : ''}>E</option>
          </select></div>
        <div class="cc-field"><label>Cédula</label>
          <input type="text" data-modal="cedula" value="${form.cedula || ''}" placeholder="12.345.678"></div>
        <div class="cc-field"><label>Fecha de Nacimiento</label>
          <input type="date" data-modal="fecha_nacimiento" value="${form.fecha_nacimiento || ''}"></div>
        <div class="cc-field"><label>Carácter <span class="req">*</span></label>
          <select data-modal="caracter">
            <option value="HEREDERO" ${form.caracter === 'HEREDERO' ? 'selected' : ''}>Heredero</option>
            <option value="LEGATARIO" ${form.caracter === 'LEGATARIO' ? 'selected' : ''}>Legatario</option>
          </select></div>
        <div class="cc-field"><label>Parentesco <span class="req">*</span></label>
          <select data-modal="parentesco">
            <option value="">Seleccione...</option>
            ${PARENTESCOS.map(p => `<option value="${p}" ${form.parentesco === p ? 'selected' : ''}>${p}</option>`).join('')}
          </select></div>
        <div class="cc-field"><label>Premuerto</label>
          <select data-modal="premuerto">
            <option value="NO" ${form.premuerto !== 'SI' ? 'selected' : ''}>No</option>
            <option value="SI" ${form.premuerto === 'SI' ? 'selected' : ''}>Sí</option>
          </select></div>
      </div>`,
        collect: () => collectModalFields(),
        save: (form) => {
            if (UIState.editIndex !== null) { caseData.herederos[UIState.editIndex] = form; }
            else { caseData.herederos.push(form); }
            renderHerederos();
        }
    },

    inmueble: {
        title: () => 'Agregar Bien Inmueble',
        saveLabel: () => 'Agregar Inmueble',
        wide: true,
        build: (form) => `
      <div class="cc-field" style="margin-bottom:16px">
        <label style="margin-bottom:8px;display:block">Tipo de Bien</label>
        <div class="cc-type-grid">${TIPOS_BIEN_INMUEBLE.map(t =>
            `<button type="button" class="cc-type-option${form.tipo === t ? ' is-selected' : ''}" data-tipo="${t}">${t}</button>`
        ).join('')}</div>
      </div>
      <div class="cc-grid cc-grid--3">
        <div class="cc-field"><label>Vivienda Principal</label>
          <select data-modal="vivienda_principal">
            <option value="No" ${form.vivienda_principal !== 'Si' ? 'selected' : ''}>No</option>
            <option value="Si" ${form.vivienda_principal === 'Si' ? 'selected' : ''}>Sí</option>
          </select></div>
        <div class="cc-field"><label>Bien Litigioso</label>
          <select data-modal="bien_litigioso">
            <option value="No" ${form.bien_litigioso !== 'Si' ? 'selected' : ''}>No</option>
            <option value="Si" ${form.bien_litigioso === 'Si' ? 'selected' : ''}>Sí</option>
          </select></div>
        <div class="cc-field"><label>Porcentaje %</label>
          <input type="number" data-modal="porcentaje" min="0" max="100" value="${form.porcentaje || 100}"></div>
      </div>
      <div class="cc-field cc-mt"><label>Descripción</label>
        <textarea data-modal="descripcion" rows="2" placeholder="Características del bien...">${form.descripcion || ''}</textarea></div>
      <div class="cc-grid cc-grid--3 cc-mt">
        <div class="cc-field"><label>Superficie Construida (m²)</label>
          <input type="text" data-modal="superficie_construida" value="${form.superficie_construida || ''}"></div>
        <div class="cc-field"><label>Superficie No Construida (m²)</label>
          <input type="text" data-modal="superficie_no_construida" value="${form.superficie_no_construida || ''}"></div>
        <div class="cc-field"><label>Área Total (m²)</label>
          <input type="text" data-modal="area_total" value="${form.area_total || ''}"></div>
      </div>
      <div class="cc-field cc-mt"><label>Dirección del inmueble</label>
        <textarea data-modal="direccion" rows="2" placeholder="Dirección completa...">${form.direccion || ''}</textarea></div>
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
            if (sel) f.tipo = sel.dataset.tipo;
            return f;
        },
        save: (form) => {
            caseData.bienes_inmuebles.push(form);
            renderInventario();
        }
    },

    mueble: {
        title: () => `Agregar — ${CATEGORIAS_MUEBLE.find(c => c.key === UIState.currentSubTab)?.label || ''}`,
        saveLabel: () => 'Agregar',
        wide: false,
        build: (form) => `
      <div class="cc-grid cc-grid--2">
        <div class="cc-field"><label>Porcentaje %</label>
          <input type="number" data-modal="porcentaje" min="0" max="100" value="${form.porcentaje || 100}"></div>
        <div class="cc-field"><label>Bien Litigioso</label>
          <select data-modal="bien_litigioso">
            <option value="No" ${form.bien_litigioso !== 'Si' ? 'selected' : ''}>No</option>
            <option value="Si" ${form.bien_litigioso === 'Si' ? 'selected' : ''}>Sí</option>
          </select></div>
        <div class="cc-field cc-span-2"><label>Descripción</label>
          <textarea data-modal="descripcion" placeholder="Descripción del bien mueble...">${form.descripcion || ''}</textarea></div>
        <div class="cc-field cc-span-2"><label>Valor Declarado (Bs.) <span class="req">*</span></label>
          <input type="number" step="0.01" data-modal="valor_declarado" placeholder="0.00" value="${form.valor_declarado || ''}"></div>
      </div>`,
        collect: () => collectModalFields(),
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
        build: (form) => `
      <div class="cc-grid cc-grid--2">
        <div class="cc-field"><label>Tipo de Deuda <span class="req">*</span></label>
          <select data-modal="subtipo">
            <option value="">Seleccione...</option>
            ${TIPOS_PASIVO_DEUDA.map(t => `<option value="${t.key}" ${form.subtipo === t.key ? 'selected' : ''}>${t.label}</option>`).join('')}
          </select></div>
        <div class="cc-field"><label>Porcentaje %</label>
          <input type="number" data-modal="porcentaje" min="0" max="100" value="${form.porcentaje || 100}"></div>
        <div class="cc-field cc-span-2"><label>Descripción</label>
          <textarea data-modal="descripcion" placeholder="Descripción de la deuda...">${form.descripcion || ''}</textarea></div>
        <div class="cc-field cc-span-2"><label>Valor Declarado (Bs.) <span class="req">*</span></label>
          <input type="number" step="0.01" data-modal="valor_declarado" value="${form.valor_declarado || ''}"></div>
      </div>`,
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
          <select data-modal="tipo_gasto">
            <option value="">Seleccione...</option>
            ${TIPOS_PASIVO_GASTO.map(t => `<option value="${t}" ${form.tipo_gasto === t ? 'selected' : ''}>${t}</option>`).join('')}
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
        formData = { tipo_cedula: 'V', caracter: 'HEREDERO', premuerto: 'NO', parentesco: '' };
    } else if (type === 'inmueble') {
        formData = { tipo: '', vivienda_principal: 'No', bien_litigioso: 'No', porcentaje: '100' };
    } else if (type === 'mueble') {
        formData = { porcentaje: '100', bien_litigioso: 'No' };
    } else if (type === 'pasivo_deuda') {
        formData = { porcentaje: '100' };
    } else if (type === 'pasivo_gasto') {
        formData = { porcentaje: '100' };
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
