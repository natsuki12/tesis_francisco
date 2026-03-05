import { $, $$, showToast } from './utils.js';
import { caseData, UIState, loadCaseData, saveCaseData, clearSavedCaseData } from './state.js';

/**
 * Envía el caseData completo al backend vía POST /api/casos.
 * @param {'Borrador'|'Publicado'} modo
 */
async function submitCase(modo) {
    // Fijar el estado antes de enviar
    caseData.caso.estado = modo;

    try {
        const res = await fetch('/api/casos', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(caseData),
        });
        const json = await res.json();

        if (!res.ok || !json.success) {
            // Mostrar cada error del servidor como toast
            (json.errors || ['Error desconocido al guardar.']).forEach(err => showToast(err));
            return;
        }

        // Éxito
        showToast(json.message || 'Guardado exitosamente.', 'success');
        clearSavedCaseData();
        setTimeout(() => { window.location.href = '/casos-sucesorales'; }, 1500);
    } catch (err) {
        showToast('Error de red al guardar el caso: ' + err.message);
    }
}
import { renderHerenciaCheckboxes, initRepresentanteLogic } from './herederos.js';
import { fetchEstados, initAddressListeners, saveDireccion, renderDirecciones, editDireccion, deleteDireccion } from './direccion.js';
import { initStepperClicks, setStep, nextStep, prevStep } from './navigation.js';
import { initStudentSearch } from './summary.js';
import { initCatalogos, getCatalogs, loadSeccionesSelect } from './catalogos.js';
import { openModal, closeModal, saveModal, removeItem, removeMueble } from './modal.js';
import { saveProrroga, renderProrrogas, deleteProrroga, editProrroga } from './prorroga.js'; // Multiples prorrogas

// Asignamos callbacks globales para el HTML onClick
window.CC = {
    nextStep, prevStep, setStep,
    openModal, closeModal, saveModal,
    removeItem, removeMueble,
    saveDireccion, editDireccion, deleteDireccion,
    saveProrroga, deleteProrroga, editProrroga,
    publish: () => submitCase('Publicado')
};

// Global helper access for state changes
document.ccHelpers = { $, show: (el) => { if (el) el.style.display = ''; }, hide: (el) => { if (el) el.style.display = 'none'; } };

function bindInputs() {
    $$('[data-bind]').forEach(el => {
        const [section, key] = el.dataset.bind.split('.');

        // Set initial value
        if (caseData[section] && caseData[section][key] !== undefined) {
            if (el.type === 'radio') {
                el.checked = (el.value === String(caseData[section][key]));
            } else if (el.type === 'checkbox') {
                el.checked = Boolean(caseData[section][key]);
            } else {
                el.value = caseData[section][key];
            }
        }

        // Listen for changes
        const handler = () => {
            if (caseData[section]) {
                if (el.type === 'radio') {
                    if (el.checked) caseData[section][key] = el.value;
                } else if (el.type === 'checkbox') {
                    caseData[section][key] = el.checked;
                } else {
                    caseData[section][key] = el.value;
                }
            }
        };

        el.addEventListener('input', handler);
        el.addEventListener('change', handler);
    });
}

function initCollapsibles() {
    $$('.cc-card--collapsible').forEach(card => {
        const header = card.querySelector('.cc-card__toggle');
        const body = card.querySelector('.cc-card__collapse');

        if (!header) return;

        // Sync initial state (if body is visible, it should have 'is-open' class)
        if (body) {
            const isVisible = getComputedStyle(body).display !== 'none' && body.style.display !== 'none';
            if (isVisible) {
                card.classList.add('is-open');
            } else {
                card.classList.remove('is-open');
            }
        }

        // Clone node to drop previously attached listeners (prevent multiple triggers)
        const newHeader = header.cloneNode(true);
        header.parentNode.replaceChild(newHeader, header);

        newHeader.addEventListener('click', () => {
            card.classList.toggle('is-open');
            if (body) {
                body.style.display = card.classList.contains('is-open') ? '' : 'none';
            }
        });
    });
}

function initTabs() {
    $$('.cc-tab').forEach(tab => {
        tab.addEventListener('click', () => {
            $$('.cc-tab').forEach(t => t.classList.remove('is-active'));
            $$('.cc-tab-panel').forEach(p => { if (p) p.style.display = 'none'; });
            tab.classList.add('is-active');
            const panel = $(`#panel-${tab.dataset.tab}`);
            if (panel) panel.style.display = '';
        });
    });
}

function renderSelects() {
    const cats = getCatalogs();

    // Nacionalidad
    const nacCausante = document.querySelector('select[data-bind="causante.nacionalidad"]');
    const nacRep = document.querySelector('select[data-bind="representante.nacionalidad"]');
    const paisesHtml = '<option value="">Seleccione...</option>' +
        cats.paises.map(p => `<option value="${p.id}">${p.nombre}</option>`).join('');

    if (nacCausante) nacCausante.innerHTML = paisesHtml;
    if (nacRep) nacRep.innerHTML = paisesHtml;
}

async function init() {
    // Restore saved data before binding inputs
    const hadSavedData = loadCaseData();

    bindInputs();
    initCollapsibles();
    initTabs();
    initStepperClicks();
    initStudentSearch();
    await initCatalogos();
    renderSelects();
    loadSeccionesSelect();
    renderHerenciaCheckboxes();
    initRepresentanteLogic();
    initAddressListeners();
    fetchEstados();
    renderDirecciones();
    renderProrrogas();

    // Restore step or start at 0
    setStep(hadSavedData ? UIState.currentStep : 0);

    // Auto-save before page unload
    window.addEventListener('beforeunload', () => saveCaseData());

    const btnSaveDraft = $('#btnSaveDraft');
    if (btnSaveDraft) {
        btnSaveDraft.addEventListener('click', () => {
            if (!caseData.caso || !caseData.caso.titulo || caseData.caso.titulo.trim() === '') {
                showToast('Ingrese el Título del Caso antes de guardar el borrador.');
                return;
            }
            submitCase('Borrador');
        });
    }
}

document.addEventListener('DOMContentLoaded', init);
