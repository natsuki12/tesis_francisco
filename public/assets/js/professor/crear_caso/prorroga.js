import { caseData, UIState } from './state.js';
import { $, $$, showToast } from '../../global/utils.js';

/**
 * Renderiza el listado de prórrogas en la tabla y maneja su estado vacío
 */
export function renderProrrogas() {
    const tableContainer = $('#prorrogasTableContainer');
    const tableBody = $('#prorrogasTableBody');

    if (!tableContainer || !tableBody) return;

    if (!caseData.prorrogas || caseData.prorrogas.length === 0) {
        tableContainer.style.display = 'none';
        tableBody.innerHTML = '';
        return;
    }

    tableContainer.style.display = '';

    tableBody.innerHTML = caseData.prorrogas.map((p, index) => {
        // Formatear fechas para no verse en formato YYYY-MM-DD directamente si es posible, 
        // pero mantenemos simpleza en la vista.
        const dSolicitud = p.fecha_solicitud.split('-').reverse().join('/');
        const dRes = p.fecha_resolucion.split('-').reverse().join('/');
        const dVence = p.fecha_vencimiento.split('-').reverse().join('/');

        return `
            <tr>
                <td>${dSolicitud}</td>
                <td>${p.nro_resolucion}</td>
                <td>${dRes}</td>
                <td style="text-align:center;">${p.plazo_dias}</td>
                <td>${dVence}</td>
                <td class="cc-th-action">
                    <div class="cc-td-actions">
                        <button type="button" class="btn-icon" onclick="CC.editProrroga(${index})" title="Editar Prórroga">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                            </svg>
                        </button>
                        <button type="button" class="btn-danger-ghost" onclick="CC.deleteProrroga(${index})" title="Eliminar Prórroga">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                                <path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                            </svg>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

/**
 * Valida y guarda una prórroga desde los inputs al arreglo
 */
export function saveProrroga() {
    const { fecha_solicitud, nro_resolucion, fecha_resolucion, plazo_dias, fecha_vencimiento } = caseData.prorroga;

    // Validación: Todos los campos requeridos
    if (!fecha_solicitud || !nro_resolucion.trim() || !fecha_resolucion || !plazo_dias || !fecha_vencimiento) {
        showToast('Complete todos los campos de la prórroga antes de agregarla.');
        return;
    }

    if (parseInt(plazo_dias) < 1) {
        showToast('El plazo otorgado debe ser al menos 1 día.');
        return;
    }

    const payload = {
        fecha_solicitud,
        nro_resolucion: nro_resolucion.trim(),
        fecha_resolucion,
        plazo_dias,
        fecha_vencimiento
    };

    if (UIState.editProrrogaIndex !== null && UIState.editProrrogaIndex !== undefined) {
        // Actualizar existente
        caseData.prorrogas[UIState.editProrrogaIndex] = payload;
        UIState.editProrrogaIndex = null;
        const btnSave = $('#btnSaveProrroga');
        if (btnSave) {
            btnSave.innerHTML = '+ Agregar Prórroga';
            btnSave.classList.remove('btn-primary');
            btnSave.classList.add('btn-secondary');
        }
    } else {
        // Guardar nueva
        caseData.prorrogas.push(payload);
    }

    // Limpiar form
    caseData.prorroga.fecha_solicitud = '';
    caseData.prorroga.nro_resolucion = '';
    caseData.prorroga.fecha_resolucion = '';
    caseData.prorroga.plazo_dias = '';
    caseData.prorroga.fecha_vencimiento = '';

    // Actualizar ui
    renderProrrogas();

    // Enfocar inputs bound al primer form visualmente
    const firstInput = document.querySelector('[data-bind="prorroga.fecha_solicitud"]');
    if (firstInput) {
        // Necesitamos despachar el evento change en los elementos del form para que UI se actualice con lo borrado del state
        document.querySelectorAll('[data-bind^="prorroga."]').forEach(el => {
            el.value = '';
        });
        firstInput.focus();
    }
}

/**
 * Cargar una prórroga para su edición
 */
export function editProrroga(index) {
    const prorroga = caseData.prorrogas[index];
    if (!prorroga) return;

    // Cargar al form
    caseData.prorroga.fecha_solicitud = prorroga.fecha_solicitud;
    caseData.prorroga.nro_resolucion = prorroga.nro_resolucion;
    caseData.prorroga.fecha_resolucion = prorroga.fecha_resolucion;
    caseData.prorroga.plazo_dias = prorroga.plazo_dias;
    caseData.prorroga.fecha_vencimiento = prorroga.fecha_vencimiento;

    // Disparar binding events
    document.querySelectorAll('[data-bind^="prorroga."]').forEach(el => {
        const key = el.dataset.bind.split('.')[1];
        el.value = prorroga[key];
    });

    UIState.editProrrogaIndex = index;

    // Actualizar botón
    const btnSave = $('#btnSaveProrroga');
    if (btnSave) {
        btnSave.innerHTML = `
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" style="margin-right: 4px;">
              <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
              <polyline points="17 21 17 13 7 13 7 21"></polyline>
              <polyline points="7 3 7 8 15 8"></polyline>
            </svg>
            Guardar Cambios
        `;
        btnSave.classList.remove('btn-secondary');
        btnSave.classList.add('btn-primary');
    }

    // Scroll a la seccion
    const card = $('#card_prorrogas');
    if (card) {
        card.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}

/**
 * Eliminar una prórroga del listado
 */
export function deleteProrroga(index) {
    if (confirm("¿Está seguro de eliminar esta prórroga?")) {
        caseData.prorrogas.splice(index, 1);
        renderProrrogas();
    }
}
