/**
 * CREAR CASO SUCESORAL — Vanilla JS
 * Architecture:
 *   - Proxy-based reactive state (caseData)
 *   - display:none step toggling (no DOM destruction)
 *   - Single generic modal with swappable innerHTML
 */
'use strict';

/* ===================================================================
   DATA CONSTANTS
   =================================================================== */
const PARENTESCOS = [
    "Cónyuge", "Concubina/Concubino", "Padre", "Madre", "Hijo/a",
    "Hermano/a Simple Conjunción", "Hermano/a Doble Conjunción",
    "Sobrino/a", "Tío/a", "Primo/a", "Abuelo/a",
    "Otro Ascendiente", "Otro Pariente"
];

const TIPOS_HERENCIA = [
    "Testamento", "Ab-Intestato", "Pura y Simple",
    "Presunción de Ausencia", "Presunción de Muerte por Accidente", "Beneficio de Inventario"
];

const TIPOS_BIEN_INMUEBLE = [
    "Anexo", "Apartamento", "Bienhechurías", "Casa", "Construcción destinada a Explotación",
    "Consultorio", "Edificio", "Galpón", "Hotel o Similar", "Inmueble en Construcción",
    "Local", "Maletero", "Mixto (Residencia/Apartamento/Comercial)", "Oficina",
    "Otros Específique", "Parcela", "Puesto de Estacionamiento", "Quinta",
    "Resort", "Terreno", "Townhouse"
];

const CATEGORIAS_MUEBLE = [
    { key: "banco", label: "Banco", icon: "🏦" },
    { key: "seguro", label: "Seguro", icon: "🛡️" },
    { key: "transporte", label: "Transporte", icon: "🚗" },
    { key: "opciones_compra", label: "Opciones de Compra", icon: "📋" },
    { key: "cuentas_cobrar", label: "Cuentas y Efectos por Cobrar", icon: "📄" },
    { key: "semovientes", label: "Semovientes", icon: "🐄" },
    { key: "bonos", label: "Bonos", icon: "📊" },
    { key: "acciones", label: "Acciones", icon: "📈" },
    { key: "prestaciones", label: "Prestaciones Sociales", icon: "👷" },
    { key: "caja_ahorro", label: "Caja de Ahorro", icon: "💰" },
    { key: "plantaciones", label: "Plantaciones", icon: "🌿" },
    { key: "otros", label: "Otros", icon: "📦" },
];

const TIPOS_PASIVO_DEUDA = [
    { key: "tdc", label: "Tarjetas de Crédito" },
    { key: "hipotecario", label: "Crédito Hipotecario" },
    { key: "prestamos", label: "Préstamos, Cuentas y Efectos por Pagar" },
    { key: "otros", label: "Otros" },
];

const TIPOS_PASIVO_GASTO = [
    "Exequias", "Apertura de Testamento", "Avalúo", "Declaración de Herencia",
    "Honorarios", "Servicios Funerarios", "Otros (especifique)"
];

const MOCK_STUDENTS = [
    { id: 1, nombre: "María García", cedula: "V-28.456.789" },
    { id: 2, nombre: "Carlos Rodríguez", cedula: "V-27.123.456" },
    { id: 3, nombre: "Ana Martínez", cedula: "V-29.876.543" },
    { id: 4, nombre: "José López", cedula: "V-26.543.210" },
    { id: 5, nombre: "Laura Pérez", cedula: "V-30.111.222" },
    { id: 6, nombre: "Diego Hernández", cedula: "V-28.333.444" },
    { id: 7, nombre: "Valentina Díaz", cedula: "V-27.555.666" },
    { id: 8, nombre: "Andrés Morales", cedula: "V-29.777.888" },
];

/* ===================================================================
   PROXY-BASED REACTIVE STATE
   =================================================================== */
function createReactiveState(initial, onChange) {
    const handler = {
        set(target, prop, value) {
            target[prop] = value;
            onChange(prop);
            return true;
        }
    };
    // Deep proxy for nested objects/arrays
    for (const key of Object.keys(initial)) {
        if (typeof initial[key] === 'object' && initial[key] !== null) {
            initial[key] = new Proxy(initial[key], handler);
        }
    }
    return new Proxy(initial, handler);
}

/* ===================================================================
   MAIN MODULE (CC namespace)
   =================================================================== */
const CC = (() => {
    // ----- State -----
    let currentStep = 0;
    let currentSubTab = 'banco';
    let currentModalType = null;
    let editIndex = null; // null = adding, number = editing

    const caseData = {
        caso: { titulo: '', descripcion: '', modalidad: '', max_intentos: '0', fecha_limite: '' },
        herencia: { tipos: [] },
        causante: { tipo_cedula: '', sexo: '', estado_civil: '', nacionalidad: '', cedula: '', pasaporte: '', rif_personal: '', nombres: '', apellidos: '', fecha_nacimiento: '', fecha_fallecimiento: '', valor_ut: '' },
        domicilio_causante: { estado: '', municipio: '', parroquia: '', direccion: '', codigo_postal: '' },
        representante: { tipo_cedula: '', cedula: '', nombres: '', apellidos: '' },
        herederos: [],
        bienes_inmuebles: [],
        bienes_muebles: {},
        pasivos_deuda: [],
        pasivos_gastos: [],
        exenciones: [],
        exoneraciones: [],
        estudiantes_asignados: [],
    };

    // ----- Helpers -----
    const $ = (sel) => document.querySelector(sel);
    const $$ = (sel) => document.querySelectorAll(sel);
    const show = (el) => { if (el) el.style.display = ''; };
    const hide = (el) => { if (el) el.style.display = 'none'; };
    const formatBs = (v) => {
        const n = parseFloat(v) || 0;
        return 'Bs. ' + n.toLocaleString('es-VE', { minimumFractionDigits: 2 });
    };

    // ----- Two-way binding -----
    function bindInputs() {
        $$('[data-bind]').forEach(el => {
            const [section, key] = el.dataset.bind.split('.');
            // Set initial value
            if (caseData[section] && caseData[section][key] !== undefined) {
                el.value = caseData[section][key];
            }
            // Listen for changes
            el.addEventListener('input', () => {
                if (caseData[section]) caseData[section][key] = el.value;
                onDataChange();
            });
            el.addEventListener('change', () => {
                if (caseData[section]) caseData[section][key] = el.value;
                onDataChange();
            });
        });
    }

    function onDataChange() {
        // Show/hide fecha_limite when modalidad = Evaluacion
        const flField = $('#fieldFechaLimite');
        if (flField) {
            caseData.caso.modalidad === 'Evaluacion' ? show(flField) : hide(flField);
        }
    }

    // ----- Wizard Navigation -----
    function setStep(n) {
        n = Math.max(0, Math.min(3, n));
        currentStep = n;

        // Toggle step visibility
        $$('.cc-step').forEach((el, i) => {
            i === n ? show(el) : hide(el);
        });

        // Update stepper icons
        $$('.cc-stepper__step').forEach((el, i) => {
            el.classList.remove('is-active', 'is-done');
            if (i < n) el.classList.add('is-done');
            else if (i === n) el.classList.add('is-active');
        });
        $$('.cc-stepper__connector').forEach((el, i) => {
            el.classList.toggle('is-done', i < n);
        });

        // Update buttons
        const btnPrev = $('#btnPrev');
        const btnNext = $('#btnNext');
        const btnPub = $('#btnPublish');
        btnPrev.disabled = n === 0;
        if (n === 3) {
            hide(btnNext);
            show(btnPub);
            renderSummary();
        } else {
            show(btnNext);
            hide(btnPub);
        }

        // Render dynamic content for current step
        if (n === 1) renderHerederos();
        if (n === 2) renderInventario();
        if (n === 3) { renderSummary(); renderStudents(); }
    }

    function nextStep() { setStep(currentStep + 1); }
    function prevStep() { setStep(currentStep - 1); }

    // ----- Collapsible Cards -----
    function initCollapsibles() {
        $$('.cc-card__toggle').forEach(header => {
            header.addEventListener('click', () => {
                const card = header.closest('.cc-card--collapsible');
                const body = card.querySelector('.cc-card__collapse');
                card.classList.toggle('is-open');
                body.style.display = card.classList.contains('is-open') ? '' : 'none';
            });
        });
    }

    // ----- Herencia Checkboxes -----
    function renderHerenciaCheckboxes() {
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

    // ----- Herederos Rendering -----
    function renderHerederos() {
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

    // ----- Inventario Rendering -----
    function renderInventario() {
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

    // ----- Muebles Sub-tabs -----
    function renderMuebleSubtabs() {
        const container = $('#muebleSubtabs');
        if (!container) return;
        container.innerHTML = CATEGORIAS_MUEBLE.map(c => {
            const count = (caseData.bienes_muebles[c.key] || []).length;
            return `<button class="cc-subtab${currentSubTab === c.key ? ' is-active' : ''}" data-subtab="${c.key}">
        <span class="cc-subtab__emoji">${c.icon}</span>
        <span class="cc-subtab__label">${c.label}</span>
        ${count > 0 ? `<span class="cc-subtab__count">${count}</span>` : ''}
      </button>`;
        }).join('');

        container.querySelectorAll('.cc-subtab').forEach(btn => {
            btn.addEventListener('click', () => {
                currentSubTab = btn.dataset.subtab;
                renderMuebleSubtabs();
                renderMueblesList();
            });
        });
    }

    function renderMueblesList() {
        const cat = CATEGORIAS_MUEBLE.find(c => c.key === currentSubTab);
        const title = $('#muebleSubtitle');
        const desc = $('#muebleSubdesc');
        const emptyEl = $('#mueblesEmpty');
        const emptyText = $('#mueblesEmptyText');
        const list = $('#mueblesList');

        if (title) title.textContent = cat ? cat.label : '';
        if (desc) desc.textContent = cat ? `Bienes muebles de tipo "${cat.label}"` : '';
        if (emptyText) emptyText.textContent = cat ? `No hay registros de ${cat.label}` : '';

        const items = caseData.bienes_muebles[currentSubTab] || [];
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
            <button class="cc-btn--icon-danger" onclick="CC.removeMueble('${currentSubTab}', ${i})"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></button>
          </div>
        </div>`;
            }).join('') +
                `<button class="cc-btn cc-btn--soft cc-mt" onclick="CC.openModal('mueble')"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Agregar</button>`;
        }
    }

    // ----- Tabs (Inventario) -----
    function initTabs() {
        $$('.cc-tab').forEach(tab => {
            tab.addEventListener('click', () => {
                $$('.cc-tab').forEach(t => t.classList.remove('is-active'));
                $$('.cc-tab-panel').forEach(p => hide(p));
                tab.classList.add('is-active');
                const panel = $(`#panel-${tab.dataset.tab}`);
                if (panel) show(panel);
            });
        });
    }

    // ----- GENERIC MODAL -----
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
                if (editIndex !== null) { caseData.herederos[editIndex] = form; }
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
            title: () => `Agregar — ${CATEGORIAS_MUEBLE.find(c => c.key === currentSubTab)?.label || ''}`,
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
                if (!caseData.bienes_muebles[currentSubTab]) caseData.bienes_muebles[currentSubTab] = [];
                caseData.bienes_muebles[currentSubTab].push(form);
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

    function openModal(type, editIdx) {
        currentModalType = type;
        editIndex = (editIdx !== undefined && editIdx !== null) ? editIdx : null;

        const config = MODAL_CONFIGS[type];
        if (!config) return;

        const overlay = $('#genericModal');
        const modal = overlay.querySelector('.cc-modal');
        const titleEl = $('#modalTitle');
        const bodyEl = $('#modalBody');
        const saveBtn = $('#modalSaveBtn');

        // Prepare form data
        let formData = {};
        if (type === 'heredero' && editIndex !== null) {
            formData = { ...caseData.herederos[editIndex] };
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

        titleEl.textContent = config.title(editIndex);
        saveBtn.textContent = config.saveLabel(editIndex);
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

    function closeModal() {
        $('#genericModal').classList.remove('is-open');
        currentModalType = null;
        editIndex = null;
    }

    function saveModal() {
        const config = MODAL_CONFIGS[currentModalType];
        if (!config) return;
        const form = config.collect();
        config.save(form);
        closeModal();
    }

    // ----- Remove Items -----
    function removeItem(collection, index) {
        caseData[collection].splice(index, 1);
        if (collection === 'herederos') renderHerederos();
        else renderInventario();
    }

    function removeMueble(category, index) {
        if (caseData.bienes_muebles[category]) {
            caseData.bienes_muebles[category].splice(index, 1);
        }
        renderInventario();
    }

    // ----- Summary (Step 4) -----
    function renderSummary() {
        const s = (id, val) => { const e = document.getElementById(id); if (e) e.textContent = val; };

        s('sumTitulo', caseData.caso.titulo || 'Sin título');
        const modalBadge = document.getElementById('sumModalidad');
        if (modalBadge) {
            modalBadge.innerHTML = caseData.caso.modalidad === 'Evaluacion'
                ? '<span class="cc-badge cc-badge--amber">Evaluación</span>'
                : '<span class="cc-badge cc-badge--blue">Práctica Libre</span>';
        }
        const causante = caseData.causante.nombres && caseData.causante.apellidos
            ? `${caseData.causante.nombres} ${caseData.causante.apellidos}`
            : 'Sin definir';
        s('sumCausante', causante);
        s('sumHerederos', caseData.herederos.length);
        s('sumHerencia', caseData.herencia.tipos.length > 0 ? caseData.herencia.tipos.join(', ') : 'Sin definir');

        const totalInm = caseData.bienes_inmuebles.reduce((s, b) => s + (parseFloat(b.valor_declarado) || 0), 0);
        const totalMue = Object.values(caseData.bienes_muebles).reduce((s, arr) => {
            return s + (Array.isArray(arr) ? arr.reduce((ss, b) => ss + (parseFloat(b.valor_declarado) || 0), 0) : 0);
        }, 0);
        const totalPas = [...caseData.pasivos_deuda, ...caseData.pasivos_gastos].reduce((s, p) => s + (parseFloat(p.valor_declarado) || 0), 0);

        s('sumInmuebles', formatBs(totalInm));
        s('sumMuebles', formatBs(totalMue));
        s('sumActivos', formatBs(totalInm + totalMue));
        s('sumPasivos', `- ${formatBs(totalPas)}`);
        s('sumNeto', formatBs(totalInm + totalMue - totalPas));
    }

    // ----- Students (Step 4) -----
    function renderStudents(filter = '') {
        const grid = $('#studentsGrid');
        if (!grid) return;

        const filtered = MOCK_STUDENTS.filter(s =>
            s.nombre.toLowerCase().includes(filter.toLowerCase()) || s.cedula.includes(filter)
        );

        grid.innerHTML = filtered.map(s => {
            const selected = caseData.estudiantes_asignados.includes(s.id);
            return `<button class="cc-student-card${selected ? ' is-selected' : ''}" data-student-id="${s.id}">
        <div class="cc-student-avatar">${selected ? '✓' : s.nombre.charAt(0)}</div>
        <div>
          <div class="cc-student-name">${s.nombre}</div>
          <div class="cc-student-ci">${s.cedula}</div>
        </div>
      </button>`;
        }).join('');

        grid.querySelectorAll('.cc-student-card').forEach(card => {
            card.addEventListener('click', () => {
                const id = parseInt(card.dataset.studentId);
                const idx = caseData.estudiantes_asignados.indexOf(id);
                if (idx >= 0) caseData.estudiantes_asignados.splice(idx, 1);
                else caseData.estudiantes_asignados.push(id);
                renderStudents(filter);
                updateSelectedCount();
            });
        });

        updateSelectedCount();
    }

    function updateSelectedCount() {
        const countEl = $('#selectedCount');
        const textEl = $('#selectedCountText');
        const n = caseData.estudiantes_asignados.length;
        if (n > 0) {
            show(countEl);
            if (textEl) textEl.textContent = `${n} estudiante(s) seleccionado(s)`;
        } else {
            hide(countEl);
        }
    }

    // ----- Student Search -----
    function initStudentSearch() {
        const input = $('#studentSearch');
        if (!input) return;
        input.addEventListener('input', () => {
            renderStudents(input.value);
        });
    }

    // ----- Stepper clicking -----
    function initStepperClicks() {
        $$('.cc-stepper__step').forEach(step => {
            step.addEventListener('click', () => {
                const n = parseInt(step.dataset.step);
                setStep(n);
            });
        });
    }

    // ----- Publish -----
    function publish() {
        alert('Caso publicado exitosamente (pendiente integración con backend).');
    }

    // ----- Init -----
    function init() {
        bindInputs();
        initCollapsibles();
        initTabs();
        initStepperClicks();
        initStudentSearch();
        renderHerenciaCheckboxes();
        setStep(0);
    }

    document.addEventListener('DOMContentLoaded', init);

    // Public API
    return {
        nextStep, prevStep, setStep,
        openModal, closeModal, saveModal,
        removeItem, removeMueble,
        publish,
    };
})();
