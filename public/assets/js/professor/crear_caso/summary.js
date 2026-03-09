import { formatBs } from '../../global/utils.js';
import { caseData } from './state.js';
import { getCatalogs } from '../../global/catalogos.js';

export function renderSummary() {
    const s = (id, val) => { const e = document.getElementById(id); if (e) e.textContent = val; };

    s('sumTitulo', caseData.caso.titulo || 'Sin título');
    const causante = caseData.causante.nombres && caseData.causante.apellidos
        ? `${caseData.causante.nombres} ${caseData.causante.apellidos}`
        : 'Sin definir';
    s('sumCausante', causante);
    s('sumHerederos', caseData.herederos.length);
    const cats = getCatalogs();
    const herenciaNombres = caseData.herencia.tipos.map(t => {
        const found = (cats.tiposHerencia || []).find(c => c.id == t.tipo_herencia_id);
        return found ? found.nombre : `ID ${t.tipo_herencia_id}`;
    });
    s('sumHerencia', herenciaNombres.length > 0 ? herenciaNombres.join(', ') : 'Sin definir');
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
