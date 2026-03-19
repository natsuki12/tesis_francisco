import { formatBs } from '../../global/utils.js';
import { caseData } from './state.js';
import { getCatalogs } from '../../global/catalogos.js';
import { collectDesgravamenes } from './inventario.js';

// Capture a local ref to parseDecimal at module-load time so it survives
// console tampering (e.g. window.parseDecimal = null).
const _parseDecimal = typeof parseDecimal === 'function'
    ? parseDecimal
    : (v) => { const n = parseFloat(String(v).replace(/\./g, '').replace(',', '.')); return isNaN(n) ? 0 : n; };

/**
 * Formats a number as Venezuelan format (dot=thousands, comma=decimal)
 * without the "Bs. " prefix. E.g. 1234.56 → "1.234,56"
 */
function fmtNum(v) {
    const n = parseFloat(v) || 0;
    const parts = n.toFixed(2).split('.');
    const sign = parts[0].startsWith('-') ? '-' : '';
    const intPart = parts[0].replace('-', '').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    return sign + intPart + ',' + parts[1];
}

/**
 * Renders the full resumen (Step 4) with all 4 sections:
 * 1. Info básica
 * 2. Resumen Patrimonial y Tributo (14 rows)
 * 3. Cuota Parte Hereditaria (per-heredero table)
 * 4. Tarifa de Referencia (static, already in HTML)
 */
export function renderSummary() {
    const s = (id, val) => { const e = document.getElementById(id); if (e) e.textContent = val; };
    const cats = getCatalogs();

    // ═══ Section 1: Info Básica (enriched) ═══
    s('sumTitulo', caseData.caso.titulo || 'Sin título');

    // Causante — nombre + cédula
    const cNombres = caseData.causante.nombres && caseData.causante.apellidos
        ? `${caseData.causante.nombres} ${caseData.causante.apellidos}`
        : '';
    const cCedula = caseData.causante.cedula
        ? `${caseData.causante.tipo_cedula || 'V'}-${caseData.causante.cedula}`
        : '';
    const causanteDisplay = cCedula && cNombres
        ? `${cCedula} — ${cNombres}`
        : cNombres || cCedula || 'Sin definir';
    s('sumCausante', causanteDisplay);

    // Herencia + Tipo Sucesión combinados
    const herenciaNombres = caseData.herencia.tipos.map(t => {
        const found = (cats.tiposHerencia || []).find(c => c.id == t.tipo_herencia_id);
        return found ? found.nombre : `ID ${t.tipo_herencia_id}`;
    });
    const herenciaStr = herenciaNombres.length > 0 ? herenciaNombres.join(', ') : '';
    const sucesionStr = caseData.caso.tipo_sucesion || '';
    const herenciaDisplay = [herenciaStr, sucesionStr].filter(Boolean).join(' · ') || 'Sin definir';
    s('sumHerencia', herenciaDisplay);

    // Estado civil + Fecha de fallecimiento
    const estadoCivil = caseData.causante.estado_civil || '';
    const fechaFall = caseData.causante.fecha_fallecimiento || '';
    const fechaFmt = fechaFall ? fechaFall.split('-').reverse().join('/') : '';
    const ecDisplay = [estadoCivil, fechaFmt].filter(Boolean).join(' · ') || 'Sin definir';
    s('sumEstadoCivil', ecDisplay);

    // Unidad Tributaria — replicates UnidadTributariaService::obtenerPorFecha
    try {
        const fechaFallUT = caseData.causante.fecha_fallecimiento;
        const uts = cats.unidadesTributarias || [];
        if (fechaFallUT && uts.length > 0) {
            const yearFall = parseInt(fechaFallUT.split('-')[0], 10);
            let utVigente = null;

            // Rule 1: before 2021-04-01 → always use UT id = 21
            if (fechaFallUT < '2021-04-01') {
                utVigente = uts.find(u => parseInt(u.unidad_tributaria_id) === 21);
            } else {
                // Rule 2: UT with fecha_gaceta <= fecha, highest anio first
                // (uts already sorted by fecha_gaceta DESC from backend)
                utVigente = uts.find(u =>
                    !u.fecha_entrada_vigencia || u.fecha_entrada_vigencia <= fechaFallUT
                );
            }

            // Rule 3: if none found (e.g. future date), use latest UT overall
            if (!utVigente) {
                utVigente = uts[0]; // already sorted DESC, first = latest
            }

            if (utVigente) {
                s('sumUT', `Bs. ${fmtNum(utVigente.valor)} (Gaceta ${utVigente.gaceta})`);
            } else {
                s('sumUT', 'No se encontró UT vigente');
            }
        } else {
            s('sumUT', fechaFallUT ? 'Sin datos de UT' : 'Ingrese fecha de fallecimiento');
        }
    } catch (e) {
        console.error('Error determinando UT vigente:', e);
        s('sumUT', 'Error al determinar UT');
    }
    const nHer = caseData.herederos.length;
    const nPre = caseData.herederos_premuertos.length;
    let herDisplay = `${nHer} heredero${nHer !== 1 ? 's' : ''}`;
    if (nPre > 0) herDisplay += ` · ${nPre} representante${nPre > 1 ? 's' : ''} de premuerto`;
    s('sumHerederos', herDisplay);

    // Representante + Prórrogas
    const rep = caseData.representante || {};
    const repNombre = rep.nombres && rep.apellidos
        ? `${rep.nombres} ${rep.apellidos}`
        : 'Sin representante';
    const nProrrogas = (caseData.prorrogas || []).length;
    const prorrogaStr = nProrrogas > 0 ? `${nProrrogas} prórroga${nProrrogas > 1 ? 's' : ''}` : 'Sin prórrogas';
    s('sumRepresentante', `${repNombre} · ${prorrogaStr}`);

    // ═══ Section 2: Resumen Patrimonial y Tributo ═══

    // Row 1: Total Inmuebles
    const totalInm = caseData.bienes_inmuebles.reduce((sum, b) => sum + (_parseDecimal(b.valor_declarado) || 0), 0);

    // Row 2: Total Muebles
    const totalMue = Object.values(caseData.bienes_muebles).reduce((sum, arr) => {
        return sum + (Array.isArray(arr) ? arr.reduce((ss, b) => ss + (_parseDecimal(b.valor_declarado) || 0), 0) : 0);
    }, 0);

    // Row 3 & 4: Patrimonio/Activo Bruto
    const patrimonioBruto = totalInm + totalMue;

    // Row 5: Desgravámenes (excluding Prestaciones Sociales — per real SENIAT, they only count as bien mueble)
    const desgItems = collectDesgravamenes();
    const totalDesgravamenes = desgItems
        .filter(it => it._origin !== 'Prestaciones Sociales')
        .reduce((sum, it) => sum + (_parseDecimal(it.valor_declarado) || 0), 0);

    // Row 6: Exenciones
    const totalExenciones = (caseData.exenciones || []).reduce((sum, e) => sum + (_parseDecimal(e.valor_declarado) || 0), 0);

    // Row 7: Exoneraciones
    const totalExoneraciones = (caseData.exoneraciones || []).reduce((sum, e) => sum + (_parseDecimal(e.valor_declarado) || 0), 0);

    // Row 8: Total Exclusiones
    const totalExclusiones = totalDesgravamenes + totalExenciones + totalExoneraciones;

    // Row 9: Activo Neto
    const activoNeto = patrimonioBruto - totalExclusiones;

    // Row 10: Total Pasivos
    const totalPasivos = [...(caseData.pasivos_deuda || []), ...(caseData.pasivos_gastos || [])]
        .reduce((sum, p) => sum + (_parseDecimal(p.valor_declarado) || 0), 0);

    // Row 11: Patrimonio Neto
    const patrimonioNeto = activoNeto - totalPasivos;

    // Populate rows 1-11 with Bs. prefix
    const bs = v => 'Bs. ' + fmtNum(v);
    s('resInmuebles', bs(totalInm));
    s('resMuebles', bs(totalMue));
    s('resBruto', bs(patrimonioBruto));
    s('resActivoBruto', bs(patrimonioBruto));
    s('resDesgravamenes', bs(totalDesgravamenes));
    s('resExenciones', bs(totalExenciones));
    s('resExoneraciones', bs(totalExoneraciones));
    s('resTotalExclusiones', bs(totalExclusiones));
    s('resActivoNeto', bs(activoNeto));
    s('resPasivos', bs(totalPasivos));
    s('resPatrimonioNeto', bs(patrimonioNeto));

    // ───── Determinación de Tributo (needs UT + herederos) ─────

    // Obtain UT vigente value (float) — reuse the same lookup already done for sumUT
    let valorUT = 0;
    try {
        const fechaFallUT = caseData.causante.fecha_fallecimiento;
        const uts = cats.unidadesTributarias || [];
        if (fechaFallUT && uts.length > 0) {
            let utVigente = null;
            if (fechaFallUT < '2021-04-01') {
                utVigente = uts.find(u => parseInt(u.unidad_tributaria_id) === 21);
            } else {
                utVigente = uts.find(u =>
                    !u.fecha_entrada_vigencia || u.fecha_entrada_vigencia <= fechaFallUT
                );
            }
            if (!utVigente) utVigente = uts[0];
            if (utVigente) valorUT = parseFloat(utVigente.valor) || 0;
        }
    } catch (e) { console.error('Error obteniendo UT para tributo:', e); }

    const allHerederos = [...caseData.herederos];  // Only main herederos count for cuota parte
    const totalHerederos = allHerederos.length;

    // ── Build tarifa lookup from catalogs ──
    const tarifas = cats.tarifasSucesion || [];
    // Group tramos by grupo_tarifa_id
    const tramosPorGrupo = {};
    for (const t of tarifas) {
        const g = parseInt(t.grupo_tarifa_id) || 4;
        if (!tramosPorGrupo[g]) tramosPorGrupo[g] = [];
        tramosPorGrupo[g].push({
            desde: parseFloat(t.rango_desde_ut) || 0,
            hasta: t.rango_hasta_ut !== null && t.rango_hasta_ut !== undefined
                   ? parseFloat(t.rango_hasta_ut) : null,
            porcentaje: parseFloat(t.porcentaje) || 0,
            sustraendoUT: parseFloat(t.sustraendo_ut) || 0,
        });
    }

    // Build parentesco_id → grupo_tarifa_id lookup
    const parentescoGrupo = {};
    for (const p of (cats.parentescos || [])) {
        parentescoGrupo[p.parentesco_id] = p.grupo_tarifa_id !== null && p.grupo_tarifa_id !== undefined
            ? parseInt(p.grupo_tarifa_id) : 4;
    }

    // buscarTramo — find applicable tarifa range for a cuota in UT
    function buscarTramo(grupoId, cuotaParteUT) {
        const tramos = tramosPorGrupo[grupoId] || [];
        for (const t of tramos) {
            if (cuotaParteUT >= t.desde && (t.hasta === null || cuotaParteUT <= t.hasta)) {
                return t;
            }
        }
        // Fallback: last tramo (unlimited upper bound)
        return tramos.length > 0 ? tramos[tramos.length - 1] : null;
    }

    // ── Calculate tributo per heredero ──
    let linea12 = 0; // sum of impuesto_a_pagar
    let linea13 = 0; // sum of reducciones

    const herederosCalc = allHerederos.map(h => {
        const parentescoId = parseInt(h.parentesco_id) || 0;
        const grupoId = parentescoGrupo[parentescoId] ?? 4;

        let cuotaParteUT = 0;
        let porcentaje = 0;
        let sustraendoUT = 0;
        let impuestoDeterminado = 0;
        let reduccion = 0;
        let impuestoAPagar = 0;

        if (patrimonioNeto > 0 && totalHerederos > 0 && valorUT > 0) {
            const cuotaParteBs = patrimonioNeto / totalHerederos;
            cuotaParteUT = cuotaParteBs / valorUT;

            const tramo = buscarTramo(grupoId, cuotaParteUT);
            if (tramo) {
                porcentaje = tramo.porcentaje;
                sustraendoUT = tramo.sustraendoUT;
            }

            // Art. 9: grupo 1 (1° grado) + cuota ≤ 75 UT → impuesto = 0
            if (grupoId === 1 && cuotaParteUT <= 75.0) {
                impuestoDeterminado = 0;
            } else {
                const impuestoUT = (cuotaParteUT * porcentaje / 100) - sustraendoUT;
                impuestoDeterminado = Math.round(impuestoUT * valorUT * 100) / 100;
            }

            impuestoAPagar = Math.max(0, Math.round((impuestoDeterminado - reduccion) * 100) / 100);
        }

        linea12 += impuestoAPagar;
        linea13 += reduccion;

        return {
            h,
            cuotaParteUT: Math.round(cuotaParteUT * 100) / 100,
            porcentaje,
            sustraendoUT,
            impuestoDeterminado,
            reduccion,
            impuestoAPagar,
        };
    });

    const totalImpuesto = Math.round(linea12 * 100) / 100;
    const totalReducciones = Math.round(linea13 * 100) / 100;

    // Rows 12-14: Tributo
    s('resImpuestoTarifa', bs(totalImpuesto));
    s('resReducciones', bs(totalReducciones));
    s('resTotalImpuesto', bs(totalImpuesto));

    // Color-code patrimonio neto based on positive/negative
    const netoEl = document.getElementById('resPatrimonioNeto');
    if (netoEl) {
        netoEl.style.color = patrimonioNeto >= 0 ? 'var(--emerald-600)' : 'var(--red-600)';
    }

    // ═══ Section 3: Cuota Parte Hereditaria ═══
    const tbody = document.getElementById('resHerederosBody');
    if (!tbody) return;

    if (herederosCalc.length === 0) {
        tbody.innerHTML = `<tr><td colspan="11" style="text-align:center;padding:32px;">
            <div style="display:flex;flex-direction:column;align-items:center;gap:8px;">
                <div style="width:40px;height:40px;border-radius:10px;background:var(--gray-100);display:flex;align-items:center;justify-content:center;font-size:18px;">👥</div>
                <span style="color:var(--gray-400);font-size:13px;">No hay herederos registrados</span>
            </div>
        </td></tr>`;
        return;
    }

    tbody.innerHTML = herederosCalc.map(({ h, cuotaParteUT, porcentaje, sustraendoUT, impuestoDeterminado, reduccion, impuestoAPagar }) => {
        const nombre = `${h.apellidos || ''} ${h.nombres || ''}`.trim() || '—';
        const cedula = h.cedula ? `${h.letra_cedula || 'V'}-${h.cedula}` : (h.pasaporte || '—');
        const parentescoId = h.parentesco_id;
        const parentescoObj = (cats.parentescos || []).find(p => p.parentesco_id == parentescoId);
        const parentescoNombre = parentescoObj ? parentescoObj.nombre : '—';
        const grado = parentescoObj ? (parentescoObj.grupo_tarifa_id || '—') : '—';
        const premuerto = h.premuerto === 'SI' ? 'Sí' : 'No';

        return `<tr>
            <td>${nombre}</td>
            <td class="text-center">${cedula}</td>
            <td class="text-center">${parentescoNombre}</td>
            <td class="text-center">${grado}</td>
            <td class="text-center">${premuerto}</td>
            <td class="text-end">${fmtNum(cuotaParteUT)}</td>
            <td class="text-end">${fmtNum(porcentaje)}</td>
            <td class="text-end">${fmtNum(sustraendoUT)}</td>
            <td class="text-end">${fmtNum(impuestoDeterminado)}</td>
            <td class="text-end">${fmtNum(reduccion)}</td>
            <td class="text-end">${fmtNum(impuestoAPagar)}</td>
        </tr>`;
    }).join('');

    // Store auto-calc data for cálculo manual modal
    storeAutoCalc({
        herederosCalc,
        totalImpuesto,
        valorUT,
        tramosPorGrupo,
        parentescoGrupo,
    });

    // ── Check for persisted calculo_manual overrides (from borrador_json) ──
    if (Array.isArray(caseData.calculo_manual) && caseData.calculo_manual.length > 0 && !_manualOverrides) {
        try {
            // Build a map of _uid → override
            const overrideMap = {};
            for (const ov of caseData.calculo_manual) {
                if (ov._uid) overrideMap[ov._uid] = ov;
            }

            // Re-calculate with the stored overrides
            let oLinea12 = 0;
            let oLinea13 = 0;
            const overrideResults = herederosCalc.map(hc => {
                const ov = overrideMap[hc.h._uid];
                if (!ov) return hc; // no override for this heredero, keep auto

                const cuotaUT = parseFloat(ov.cuota_parte_ut) || 0;
                const red = parseFloat(ov.reduccion_bs) || 0;
                const pId = parseInt(hc.h.parentesco_id) || 0;
                const gId = parentescoGrupo[pId] ?? 4;
                const tramo = buscarTramo(gId, cuotaUT);
                const pct = tramo ? tramo.porcentaje : 0;
                const sus = tramo ? tramo.sustraendoUT : 0;

                let impDet = 0;
                if (gId === 1 && cuotaUT <= 75.0) {
                    impDet = 0;
                } else {
                    impDet = Math.round(((cuotaUT * pct / 100) - sus) * valorUT * 100) / 100;
                }
                const impPagar = Math.max(0, Math.round((impDet - red) * 100) / 100);

                oLinea12 += impPagar;
                oLinea13 += red;

                return {
                    h: hc.h,
                    cuotaParteUT: Math.round(cuotaUT * 100) / 100,
                    porcentaje: pct,
                    sustraendoUT: sus,
                    impuestoDeterminado: impDet,
                    reduccion: red,
                    impuestoAPagar: impPagar,
                };
            });

            // Apply to DOM
            const bs = v => 'Bs. ' + fmtNum(v);
            const setEl = (id, val) => { const e = document.getElementById(id); if (e) e.textContent = val; };
            oLinea12 = Math.round(oLinea12 * 100) / 100;
            oLinea13 = Math.round(oLinea13 * 100) / 100;
            setEl('resImpuestoTarifa', bs(oLinea12));
            setEl('resReducciones', bs(oLinea13));
            setEl('resTotalImpuesto', bs(oLinea12));

            if (tbody) {
                const cats2 = getCatalogs();
                tbody.innerHTML = overrideResults.map(({ h: oh, cuotaParteUT: cUT, porcentaje: pct2, sustraendoUT: sUT, impuestoDeterminado: iD, reduccion: red2, impuestoAPagar: iAP }) => {
                    const nombre = `${oh.apellidos || ''} ${oh.nombres || ''}`.trim() || '—';
                    const cedula = oh.cedula ? `${oh.letra_cedula || 'V'}-${oh.cedula}` : (oh.pasaporte || '—');
                    const pObj = (cats2.parentescos || []).find(p => p.parentesco_id == oh.parentesco_id);
                    const pNombre = pObj ? pObj.nombre : '—';
                    const grado = pObj ? (pObj.grupo_tarifa_id || '—') : '—';
                    const premuerto = oh.premuerto === 'SI' ? 'Sí' : 'No';

                    return `<tr>
                        <td>${nombre}</td>
                        <td class="text-center">${cedula}</td>
                        <td class="text-center">${pNombre}</td>
                        <td class="text-center">${grado}</td>
                        <td class="text-center">${premuerto}</td>
                        <td class="text-end">${fmtNum(cUT)}</td>
                        <td class="text-end">${fmtNum(pct2)}</td>
                        <td class="text-end">${fmtNum(sUT)}</td>
                        <td class="text-end">${fmtNum(iD)}</td>
                        <td class="text-end">${fmtNum(red2)}</td>
                        <td class="text-end">${fmtNum(iAP)}</td>
                    </tr>`;
                }).join('');
            }

            // Enable Restablecer
            const btnR = document.getElementById('btnRestablecerCalculo');
            if (btnR) btnR.disabled = false;

        } catch (e) {
            console.error('[renderSummary] Error applying persisted calculo_manual:', e);
        }
    }
}

// ═══ Module-level state for sharing between renderSummary and cálculo manual ═══
let _lastAutoCalc = null; // { herederosCalc, totalImpuesto, valorUT, tramosPorGrupo, parentescoGrupo }
let _manualOverrides = null; // { herederos: [{ cuotaParteUT, reduccion, ... }] } or null

/**
 * Stores the last automatic calculation results so cálculo manual can reference them.
 * Called at the end of renderSummary.
 */
function storeAutoCalc(data) {
    _lastAutoCalc = data;
}

/**
 * Initializes the Cálculo Manual modal:
 * - Populates Table 1 with editable heredero rows
 * - Wires Calcular, Aceptar, Cancelar, Restablecer buttons
 */
export function initCalculoManualModal() {
    const btn = document.getElementById('btnAbrirCalculoManual');
    const modal = document.getElementById('modalCalculoManual');
    const btnRestablecer = document.getElementById('btnRestablecerCalculo');
    if (!btn || !modal) return;

    // Guard against duplicate listeners
    if (btn._cmBound) return;
    btn._cmBound = true;

    btn.addEventListener('click', (e) => {
        e.stopPropagation();
        e.preventDefault();
        populateModalEntrada();
        setTimeout(() => { modal.style.display = 'flex'; }, 0);
    });

    // Close on overlay click
    modal.addEventListener('mousedown', (e) => {
        if (e.target === modal) closeModal();
    });

    // Calcular button
    const btnCalc = document.getElementById('btnCMCalcular');
    if (btnCalc) {
        btnCalc.addEventListener('click', doCalculoManual);
    }

    // Aceptar button
    const btnAceptar = document.getElementById('btnCMAceptar');
    if (btnAceptar) {
        btnAceptar.addEventListener('click', applyManualCalc);
    }

    // Restablecer button
    if (btnRestablecer) {
        btnRestablecer.addEventListener('click', () => {
            _manualOverrides = null;
            // Clear persisted overrides
            caseData.calculo_manual.length = 0;
            renderSummary(); // re-runs automatic calculation
            btnRestablecer.disabled = true;
        });
    }
}

function closeModal() {
    const modal = document.getElementById('modalCalculoManual');
    if (modal) modal.style.display = 'none';
    // Reset results section
    const resultCard = document.getElementById('cmResultadosCard');
    if (resultCard) resultCard.style.display = 'none';
    const btnAceptar = document.getElementById('btnCMAceptar');
    if (btnAceptar) btnAceptar.disabled = true;
    clearModalAlert();
}

function showModalAlert(msg, type = 'danger') {
    clearModalAlert();
    const body = document.querySelector('#modalCalculoManual .cc-modal__body');
    if (!body) return;
    const colors = {
        danger: { bg: '#fef2f2', border: '#fecaca', text: '#991b1b' },
        warning: { bg: '#fffbeb', border: '#fed7aa', text: '#92400e' },
        info: { bg: '#eff6ff', border: '#bfdbfe', text: '#1e40af' },
    };
    const c = colors[type] || colors.danger;
    const alert = document.createElement('div');
    alert.id = 'cmAlert';
    alert.style.cssText = `background:${c.bg};border:1px solid ${c.border};color:${c.text};padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:12px;display:flex;align-items:center;gap:8px;`;
    alert.innerHTML = `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg><span>${msg}</span>`;
    body.insertBefore(alert, body.firstChild);
}

function clearModalAlert() {
    const existing = document.getElementById('cmAlert');
    if (existing) existing.remove();
}

/**
 * Populates Table 1 (Entrada) with editable heredero rows.
 */
function populateModalEntrada() {
    const cats = getCatalogs();
    const tbody = document.getElementById('cmEntradaBody');
    const btnCalc = document.getElementById('btnCMCalcular');
    const cmUT = document.getElementById('cmUT');
    const cmTotal = document.getElementById('cmTotalImpuesto');
    const resultCard = document.getElementById('cmResultadosCard');
    const btnAceptar = document.getElementById('btnCMAceptar');

    if (!tbody || !_lastAutoCalc) return;

    // Reset results
    if (resultCard) resultCard.style.display = 'none';
    if (btnAceptar) btnAceptar.disabled = true;
    clearModalAlert();

    const { herederosCalc, totalImpuesto, valorUT } = _lastAutoCalc;

    // Set readonly fields
    if (cmUT) cmUT.value = fmtNum(valorUT);
    if (cmTotal) cmTotal.value = fmtNum(totalImpuesto);

    if (herederosCalc.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:var(--gray-400);padding:20px;">Sin herederos</td></tr>';
        if (btnCalc) btnCalc.disabled = true;
        return;
    }

    // Build editable rows
    tbody.innerHTML = herederosCalc.map(({ h, cuotaParteUT, reduccion }, i) => {
        const nombre = `${h.apellidos || ''} ${h.nombres || ''}`.trim() || '—';
        const cedula = h.cedula ? `${h.letra_cedula || 'V'}-${h.cedula}` : (h.pasaporte || '—');
        const parentescoObj = (cats.parentescos || []).find(p => p.parentesco_id == h.parentesco_id);
        const parentescoNombre = parentescoObj ? parentescoObj.nombre : '—';
        const grado = parentescoObj ? (parentescoObj.grupo_tarifa_id || '—') : '—';
        const premuerto = h.premuerto === 'SI' ? 'Sí' : 'No';

        return `<tr>
            <td>${nombre}</td>
            <td class="text-center">${cedula}</td>
            <td class="text-center">${parentescoNombre}</td>
            <td class="text-center">${grado}</td>
            <td class="text-center">${premuerto}</td>
            <td><input type="text" class="cc-cm-input decimal-input" id="cmCuota-${i}" value="${fmtNum(cuotaParteUT)}" style="text-align:right;width:100%;"></td>
            <td><input type="text" class="cc-cm-input decimal-input" id="cmReduccion-${i}" value="${fmtNum(reduccion)}" style="text-align:right;width:100%;"></td>
        </tr>`;
    }).join('');

    if (btnCalc) btnCalc.disabled = false;
}

/**
 * Calcular button handler — validates and fills Table 2 (Resultados).
 * Replicates the exact logic from the simulator's resumen_calculo_manual.php JS.
 */
function doCalculoManual() {
    try {
        clearModalAlert();
        const cats = getCatalogs();
        if (!_lastAutoCalc) return;

        const { herederosCalc, totalImpuesto: autoTotalImpuesto, valorUT, tramosPorGrupo, parentescoGrupo } = _lastAutoCalc;
        const resultCard = document.getElementById('cmResultadosCard');
        const resultBody = document.getElementById('cmResultadoBody');
        const cmTotal = document.getElementById('cmTotalImpuesto');
        const btnAceptar = document.getElementById('btnCMAceptar');

        if (!resultBody) return;

        // buscarTramo — local copy
        function buscarTramo(grupoId, cuotaUT) {
            const tramos = tramosPorGrupo[grupoId] || [];
            for (const t of tramos) {
                if (cuotaUT >= t.desde && (t.hasta === null || cuotaUT <= t.hasta)) return t;
            }
            return tramos.length > 0 ? tramos[tramos.length - 1] : null;
        }

        // truncDecimal — truncate (floor) to N decimals
        function truncDecimal(v, decimals) {
            const factor = Math.pow(10, decimals);
            return Math.floor(v * factor) / factor;
        }

        let totalNewImpuesto = 0;
        let totalNewDeterminado = 0;
        let hayError = false;
        const maxLimite3 = herederosCalc.length > 0
            ? truncDecimal(autoTotalImpuesto / herederosCalc.length, 2)
            : 0;
        const manualResults = [];

        for (let i = 0; i < herederosCalc.length; i++) {
            const { h } = herederosCalc[i];
            const cuotaInput = document.getElementById('cmCuota-' + i);
            const reducInput = document.getElementById('cmReduccion-' + i);
            if (!cuotaInput || !reducInput) continue;

            const cuotaUT = _parseDecimal(cuotaInput.value);
            const reduccion = _parseDecimal(reducInput.value);

            const parentescoId = parseInt(h.parentesco_id) || 0;
            const grupoId = parentescoGrupo[parentescoId] ?? 4;
            const tramo = buscarTramo(grupoId, cuotaUT);

            const porcentaje = tramo ? tramo.porcentaje : 0;
            const sustraendoUT = tramo ? tramo.sustraendoUT : 0;

            let impuestoDeterminado = 0;
            if (grupoId === 1 && cuotaUT <= 75.0) {
                impuestoDeterminado = 0;
            } else {
                const impuestoUT = (cuotaUT * porcentaje / 100) - sustraendoUT;
                impuestoDeterminado = Math.round(impuestoUT * valorUT * 100) / 100;
            }

            // ── Reduction validations (3 limits, same as simulator) ──

            // Limit 1: reducción cannot equal or exceed impuesto determinado
            if (reduccion >= impuestoDeterminado && impuestoDeterminado > 0) {
                showModalAlert('El monto de Reducción no puede ser mayor o igual al impuesto determinado.');
                hayError = true;
                break;
            }

            // Limit 2: reducción cannot exceed half of impuesto (truncated)
            const maxLimite2 = truncDecimal(impuestoDeterminado / 2, 2);

            // Apply the most restrictive between Limit 2 and Limit 3
            if (reduccion > Math.min(maxLimite2, maxLimite3) + 0.001) {
                showModalAlert('El monto de Reducción excede el límite permitido.');
                hayError = true;
                break;
            }

            const impuestoAPagar = Math.max(0, Math.round((impuestoDeterminado - reduccion) * 100) / 100);
            totalNewDeterminado += impuestoDeterminado;
            totalNewImpuesto += impuestoAPagar;

            manualResults.push({
                h,
                cuotaParteUT: cuotaUT,
                porcentaje,
                sustraendoUT,
                impuestoDeterminado,
                reduccion,
                impuestoAPagar,
            });
        }

        if (hayError) {
            if (resultCard) resultCard.style.display = 'none';
            if (btnAceptar) btnAceptar.disabled = true;
            return;
        }

        // Validation: sum of determinados must be >= automatic impuesto (tolerance 0.02)
        if (totalNewDeterminado < autoTotalImpuesto - 0.02) {
            if (cmTotal) cmTotal.value = fmtNum(totalNewImpuesto);
            if (resultCard) resultCard.style.display = 'none';
            if (btnAceptar) btnAceptar.disabled = true;
            showModalAlert('Diferencia en el monto a pagar de la declaración.');
            return;
        }

        // All OK — populate Table 2 and show it
        if (cmTotal) cmTotal.value = fmtNum(totalNewImpuesto);

        resultBody.innerHTML = manualResults.map(({ h, cuotaParteUT, porcentaje, sustraendoUT, impuestoDeterminado, reduccion, impuestoAPagar }) => {
            const nombre = `${h.apellidos || ''} ${h.nombres || ''}`.trim() || '—';
            const cedula = h.cedula ? `${h.letra_cedula || 'V'}-${h.cedula}` : (h.pasaporte || '—');
            const parentescoObj = (cats.parentescos || []).find(p => p.parentesco_id == h.parentesco_id);
            const parentescoNombre = parentescoObj ? parentescoObj.nombre : '—';
            const grado = parentescoObj ? (parentescoObj.grupo_tarifa_id || '—') : '—';
            const premuerto = h.premuerto === 'SI' ? 'Sí' : 'No';

            return `<tr>
                <td>${nombre}</td>
                <td class="text-center">${cedula}</td>
                <td class="text-center">${parentescoNombre}</td>
                <td class="text-center">${grado}</td>
                <td class="text-center">${premuerto}</td>
                <td class="text-end">${fmtNum(cuotaParteUT)}</td>
                <td class="text-end">${fmtNum(porcentaje)}</td>
                <td class="text-end">${fmtNum(sustraendoUT)}</td>
                <td class="text-end">${fmtNum(impuestoDeterminado)}</td>
                <td class="text-end">${fmtNum(reduccion)}</td>
                <td class="text-end">${fmtNum(impuestoAPagar)}</td>
            </tr>`;
        }).join('');

        if (resultCard) {
            resultCard.style.display = '';
            resultCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
        if (btnAceptar) btnAceptar.disabled = false;

        // Store manual results for Aceptar
        _manualOverrides = { results: manualResults, totalImpuesto: Math.round(totalNewImpuesto * 100) / 100 };

    } catch (e) {
        console.error('[Cálculo Manual] Error:', e);
        showModalAlert('Error inesperado al calcular. Verifique los datos ingresados.');
    }
}

/**
 * Aceptar button — applies manual calculation overrides to the main resumen table.
 */
function applyManualCalc() {
    if (!_manualOverrides) return;

    const { results, totalImpuesto } = _manualOverrides;
    const bs = v => 'Bs. ' + fmtNum(v);
    const s = (id, val) => { const e = document.getElementById(id); if (e) e.textContent = val; };

    // Update rows 12-14 in patrimonial summary
    const totalReducciones = results.reduce((sum, r) => sum + r.reduccion, 0);
    s('resImpuestoTarifa', bs(totalImpuesto));
    s('resReducciones', bs(Math.round(totalReducciones * 100) / 100));
    s('resTotalImpuesto', bs(totalImpuesto));

    // Update herederos table
    const tbody = document.getElementById('resHerederosBody');
    if (tbody) {
        const cats = getCatalogs();
        tbody.innerHTML = results.map(({ h, cuotaParteUT, porcentaje, sustraendoUT, impuestoDeterminado, reduccion, impuestoAPagar }) => {
            const nombre = `${h.apellidos || ''} ${h.nombres || ''}`.trim() || '—';
            const cedula = h.cedula ? `${h.letra_cedula || 'V'}-${h.cedula}` : (h.pasaporte || '—');
            const parentescoObj = (cats.parentescos || []).find(p => p.parentesco_id == h.parentesco_id);
            const parentescoNombre = parentescoObj ? parentescoObj.nombre : '—';
            const grado = parentescoObj ? (parentescoObj.grupo_tarifa_id || '—') : '—';
            const premuerto = h.premuerto === 'SI' ? 'Sí' : 'No';

            return `<tr>
                <td>${nombre}</td>
                <td class="text-center">${cedula}</td>
                <td class="text-center">${parentescoNombre}</td>
                <td class="text-center">${grado}</td>
                <td class="text-center">${premuerto}</td>
                <td class="text-end">${fmtNum(cuotaParteUT)}</td>
                <td class="text-end">${fmtNum(porcentaje)}</td>
                <td class="text-end">${fmtNum(sustraendoUT)}</td>
                <td class="text-end">${fmtNum(impuestoDeterminado)}</td>
                <td class="text-end">${fmtNum(reduccion)}</td>
                <td class="text-end">${fmtNum(impuestoAPagar)}</td>
            </tr>`;
        }).join('');
    }

    // Persist to caseData so it survives in borrador_json
    caseData.calculo_manual.length = 0;
    results.forEach(r => {
        if (r.h._uid) {
            caseData.calculo_manual.push({
                _uid: r.h._uid,
                cuota_parte_ut: r.cuotaParteUT,
                reduccion_bs: r.reduccion,
            });
        }
    });

    // Enable Restablecer button
    const btnRestablecer = document.getElementById('btnRestablecerCalculo');
    if (btnRestablecer) btnRestablecer.disabled = false;

    // Close modal
    closeModal();
}
