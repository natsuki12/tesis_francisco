import { $, $$ } from './utils.js';
import { caseData, UIState } from './state.js';
import { renderHerenciaCheckboxes } from './herederos.js';
import { fetchEstados, initAddressListeners } from './direccion.js';
import { initStepperClicks, setStep, nextStep, prevStep } from './navigation.js';
import { initStudentSearch } from './summary.js';
import { openModal, closeModal, saveModal, removeItem, removeMueble } from './modal.js';

// Asignamos callbacks globales para el HTML onClick
window.CC = {
    nextStep, prevStep, setStep,
    openModal, closeModal, saveModal,
    removeItem, removeMueble,
    publish: () => alert('Caso publicado exitosamente (pendiente integración con backend).')
};

// Global helper access for state changes
document.ccHelpers = { $, show: (el) => { if (el) el.style.display = ''; }, hide: (el) => { if (el) el.style.display = 'none'; } };

function bindInputs() {
    $$('[data-bind]').forEach(el => {
        const [section, key] = el.dataset.bind.split('.');

        // Set initial value
        if (caseData[section] && caseData[section][key] !== undefined) {
            el.value = caseData[section][key];
        }

        // Listen for changes
        const handler = () => {
            if (caseData[section]) caseData[section][key] = el.value;
        };

        el.addEventListener('input', handler);
        el.addEventListener('change', handler);
    });
}

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

function init() {
    bindInputs();
    initCollapsibles();
    initTabs();
    initStepperClicks();
    initStudentSearch();
    renderHerenciaCheckboxes();
    initAddressListeners();
    fetchEstados();
    setStep(0);
}

document.addEventListener('DOMContentLoaded', init);
