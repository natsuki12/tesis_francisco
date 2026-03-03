import { $, $$, show, hide, formatBs } from './utils.js';
import { caseData, UIState, MOCK_STUDENTS } from './state.js';
import { renderHerederos } from './herederos.js';
import { renderInventario } from './inventario.js';
import { renderSummary, renderStudents } from './summary.js';

export function setStep(n) {
    n = Math.max(0, Math.min(3, n));
    UIState.currentStep = n;

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

export function nextStep() { setStep(UIState.currentStep + 1); }
export function prevStep() { setStep(UIState.currentStep - 1); }

export function initStepperClicks() {
    $$('.cc-stepper__step').forEach(step => {
        step.addEventListener('click', () => {
            const n = parseInt(step.dataset.step);
            setStep(n);
        });
    });
}
