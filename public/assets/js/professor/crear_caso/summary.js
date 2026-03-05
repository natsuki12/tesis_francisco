import { $, show, hide, formatBs } from './utils.js';
import { caseData } from './state.js';
import { getCatalogs } from './catalogos.js';

export function renderSummary() {
    const s = (id, val) => { const e = document.getElementById(id); if (e) e.textContent = val; };

    s('sumTitulo', caseData.caso.titulo || 'Sin título');
    const modalBadge = document.getElementById('sumModalidad');
    if (modalBadge) {
        modalBadge.innerHTML = caseData.config.modalidad === 'Evaluacion'
            ? '<span class="cc-badge cc-badge--amber">Evaluación</span>'
            : '<span class="cc-badge cc-badge--blue">Práctica Libre</span>';
    }
    const causante = caseData.causante.nombres && caseData.causante.apellidos
        ? `${caseData.causante.nombres} ${caseData.causante.apellidos}`
        : 'Sin definir';
    s('sumCausante', causante);
    s('sumHerederos', caseData.herederos.length);
    s('sumHerencia', caseData.herencia.tipos.length > 0 ? caseData.herencia.tipos.join(', ') : 'Sin definir');
    s('sumUT', 'Sin definir');

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

export function renderStudents(filter = '') {
    const grid = $('#studentsGrid');
    if (!grid) return;

    const allStudents = getCatalogs().estudiantes || [];

    const filtered = allStudents.filter(s =>
        `${s.nombres} ${s.apellidos}`.toLowerCase().includes(filter.toLowerCase()) ||
        (s.cedula || '').includes(filter)
    );

    grid.innerHTML = filtered.map(s => {
        const selected = caseData.estudiantes_asignados.includes(s.estudiante_id);
        const nombre = `${s.nombres} ${s.apellidos}`;
        const cedula = `V-${s.cedula}`;
        return `<button class="cc-student-card${selected ? ' is-selected' : ''}" data-student-id="${s.estudiante_id}">
      <div class="cc-student-avatar">${selected ? '✓' : nombre.charAt(0)}</div>
      <div>
        <div class="cc-student-name">${nombre}</div>
        <div class="cc-student-ci">${cedula} · ${s.seccion}</div>
      </div>
    </button>`;
    }).join('');

    if (filtered.length === 0) {
        grid.innerHTML = '<p style="text-align:center;color:var(--cc-slate-400);padding:20px;">No se encontraron estudiantes</p>';
    }

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

export function initStudentSearch() {
    const input = $('#studentSearch');
    if (!input) return;
    input.addEventListener('input', () => {
        renderStudents(input.value);
    });
}
