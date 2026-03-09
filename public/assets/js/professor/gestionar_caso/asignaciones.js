/**
 * asignaciones.js
 * CRUD frontend para configuraciones de asignaciones en Gestionar Caso.
 * Usa window.__casoId y window.__baseUrl inyectados desde PHP.
 */
(function () {
    'use strict';

    const BASE = window.__baseUrl || '';
    const CASO_ID = window.__casoId || 0;

    /* ========== STYLED CONFIRM (global style) ========== */
    function showConfirm(message, title = 'Confirmación', { confirmText = 'Aceptar', cancelText = 'Cancelar', icon = '⚠️' } = {}) {
        return new Promise(resolve => {
            const overlay = document.createElement('div');
            overlay.style.cssText = `position:fixed;inset:0;background:rgba(10,30,61,0.5);backdrop-filter:blur(4px);
                -webkit-backdrop-filter:blur(4px);display:flex;align-items:center;justify-content:center;z-index:99999;
                opacity:0;transition:opacity 0.2s ease;`;
            overlay.innerHTML = `
                <div style="background:#fff;border-radius:16px;width:440px;max-width:90vw;
                    box-shadow:0 24px 80px rgba(0,0,0,0.25);transform:translateY(10px) scale(0.98);
                    transition:transform 0.2s ease;overflow:hidden;">
                    <div style="padding:16px 24px;border-bottom:1px solid #f1f5f9;
                        display:flex;align-items:center;gap:10px;">
                        <span style="font-size:20px;">${icon}</span>
                        <h3 style="margin:0;font-size:15px;font-weight:700;color:#1e293b;">${title}</h3>
                    </div>
                    <div style="padding:20px 24px;font-size:14px;color:#475569;line-height:1.6;">
                        ${message}
                    </div>
                    <div style="padding:16px 24px;border-top:1px solid #f1f5f9;
                        display:flex;justify-content:flex-end;gap:10px;">
                        <button id="gcConfirmNo" style="padding:8px 20px;border-radius:8px;border:1px solid #e2e8f0;
                            background:#fff;color:#475569;font-size:13px;font-weight:600;
                            cursor:pointer;transition:all 0.15s ease;">${cancelText}</button>
                        <button id="gcConfirmYes" style="padding:8px 20px;border-radius:8px;border:none;
                            background:#2563eb;color:#fff;font-size:13px;font-weight:600;
                            cursor:pointer;transition:all 0.15s ease;">${confirmText}</button>
                    </div>
                </div>`;
            document.body.appendChild(overlay);
            requestAnimationFrame(() => {
                overlay.style.opacity = '1';
                overlay.querySelector('div').style.transform = 'translateY(0) scale(1)';
            });
            const close = (val) => {
                overlay.style.opacity = '0';
                setTimeout(() => { overlay.remove(); resolve(val); }, 200);
            };
            overlay.querySelector('#gcConfirmNo').addEventListener('click', () => close(false));
            overlay.querySelector('#gcConfirmYes').addEventListener('click', () => close(true));
        });
    }

    /* ========== DOM refs ========== */
    const modal = document.getElementById('asignacionModal');
    const modalTitle = document.getElementById('asignacionModalTitle');
    const form = document.getElementById('formAsignacion');
    const hiddenId = document.getElementById('editConfigId');
    const elModalidad = document.getElementById('cfgModalidad');
    const elIntentos = document.getElementById('cfgMaxIntentos');
    const elApertura = document.getElementById('cfgFechaApertura');
    const elLimite = document.getElementById('cfgFechaLimite');
    const searchInput = document.getElementById('studentSearch');
    const resultsDiv = document.getElementById('studentResults');
    const selectedDiv = document.getElementById('selectedStudents');

    let selectedStudents = [];
    let editRules = null;
    let availableStudents = [];

    if (!modal) return;

    /* ========== MODAL open/close ========== */
    function openModal() { modal.style.display = 'flex'; }
    function closeModal() {
        modal.style.display = 'none';
        resetForm();
    }

    function resetForm() {
        form.reset();
        hiddenId.value = '';
        selectedStudents = [];
        editRules = null;
        renderSelected();
        resultsDiv.style.display = 'none';
        elModalidad.disabled = false;
        elIntentos.min = 0;
        elModalidad.title = '';
        elIntentos.title = '';
    }

    document.getElementById('btnCloseModal')?.addEventListener('click', closeModal);
    document.getElementById('btnCancelModal')?.addEventListener('click', closeModal);
    modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });

    /* ========== NUEVA ASIGNACIÓN ========== */
    document.getElementById('btnNuevaAsignacion')?.addEventListener('click', async () => {
        resetForm();
        modalTitle.textContent = 'Nueva Asignación';
        await loadAvailableStudents();
        openModal();
    });

    /* ========== EDITAR ========== */
    document.querySelectorAll('.btnEditConfig').forEach(btn => {
        btn.addEventListener('click', async () => {
            const configId = btn.dataset.configId;
            resetForm();
            modalTitle.textContent = 'Editar Asignación';
            hiddenId.value = configId;

            try {
                const res = await fetch(`${BASE}/api/casos/${CASO_ID}/configs`);
                const data = await res.json();
                if (!data.ok) return;

                const cfg = data.configs.find(c => String(c.id) === String(configId));
                if (!cfg) return;

                elModalidad.value = cfg.modalidad || 'Practica_Libre';
                elIntentos.value = cfg.max_intentos || 0;
                if (cfg.fecha_apertura) {
                    elApertura.value = cfg.fecha_apertura.replace(' ', 'T').substring(0, 16);
                }
                if (cfg.fecha_limite) {
                    elLimite.value = cfg.fecha_limite.replace(' ', 'T').substring(0, 16);
                }

                editRules = cfg.rules || null;
                if (editRules) {
                    if (!editRules.modalidad_editable) {
                        elModalidad.disabled = true;
                        elModalidad.title = 'No editable — ya existen intentos registrados bajo esta modalidad.';
                    }
                    if (editRules.min_intentos_permitido > 0) {
                        elIntentos.min = editRules.min_intentos_permitido;
                        elIntentos.title = `Mínimo ${editRules.min_intentos_permitido} (ya hay intentos usados)`;
                    }
                }

                selectedStudents = (cfg.estudiantes || []).map(e => ({
                    id: e.estudiante_id,
                    nombres: e.nombres,
                    apellidos: e.apellidos,
                    cedula: e.cedula,
                    asignacion_id: e.asignacion_id,
                    existing: true
                }));
                renderSelected();

                await loadAvailableStudents();
                openModal();
            } catch (err) {
                console.error('Error loading config:', err);
            }
        });
    });

    /* ========== ELIMINAR / DESACTIVAR ========== */
    document.querySelectorAll('.btnDeleteConfig').forEach(btn => {
        btn.addEventListener('click', async () => {
            const configId = btn.dataset.configId;

            let rules = null;
            let studentCount = 0;
            try {
                const res = await fetch(`${BASE}/api/casos/${CASO_ID}/configs`);
                const data = await res.json();
                const cfg = data.configs?.find(c => String(c.id) === String(configId));
                rules = cfg?.rules;
                studentCount = cfg?.estudiantes?.length || 0;
            } catch (e) { /* proceed with generic */ }

            const canDelete = rules ? rules.puede_eliminar : true;

            let msg, title, confirmText, icon;
            if (canDelete) {
                title = '¿Eliminar asignación?';
                icon = '🗑️';
                confirmText = 'Eliminar';
                msg = `Al eliminar esta asignación, se borrarán <strong>permanentemente</strong> todos los datos asociados`
                    + (studentCount > 0 ? `, incluyendo los <strong>${studentCount} estudiante(s)</strong> vinculados` : '')
                    + `.<br><br><span style="color:#94a3b8;font-size:12px;">Esta acción no se puede deshacer.</span>`;
            } else {
                title = '¿Desactivar asignación?';
                icon = '⏸️';
                confirmText = 'Desactivar';
                msg = `Esta asignación <strong>no se puede eliminar</strong> porque ya tiene intentos registrados por estudiantes.`
                    + `<br><br>Al desactivarla:`
                    + `<br>• Los estudiantes <strong>no podrán iniciar</strong> nuevos intentos.`
                    + `<br>• Los intentos ya completados se <strong>conservarán</strong> en el historial.`
                    + `<br><br><span style="color:#94a3b8;font-size:12px;">Puede reactivar la asignación posteriormente si lo necesita.</span>`;
            }

            const confirmed = await showConfirm(msg, title, { confirmText, icon });
            if (!confirmed) return;

            try {
                const res = await fetch(`${BASE}/api/configs/${configId}`, { method: 'DELETE' });
                const data = await res.json();
                if (data.ok) {
                    location.reload();
                } else {
                    alert(data.error || 'Error al procesar.');
                }
            } catch (err) {
                alert('Error de conexión.');
            }
        });
    });

    /* ========== GUARDAR ========== */
    document.getElementById('btnSaveAsignacion')?.addEventListener('click', async () => {
        const configId = hiddenId.value;
        const isEdit = !!configId;

        const payload = {
            modalidad: elModalidad.value,
            max_intentos: parseInt(elIntentos.value) || 0,
            fecha_apertura: elApertura.value || null,
            fecha_limite: elLimite.value || null,
        };

        if (!isEdit) {
            payload.estudiante_ids = selectedStudents.map(s => s.id);
            try {
                const res = await fetch(`${BASE}/api/casos/${CASO_ID}/configs`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.ok) {
                    closeModal();
                    location.reload();
                } else {
                    alert(data.error || 'Error al crear.');
                }
            } catch (err) {
                alert('Error de conexión.');
            }
        } else {
            try {
                const res = await fetch(`${BASE}/api/configs/${configId}`, {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (!data.ok) {
                    alert(data.error || 'Error al actualizar.');
                    return;
                }

                const newStudents = selectedStudents.filter(s => !s.existing);
                if (newStudents.length > 0) {
                    const addRes = await fetch(`${BASE}/api/configs/${configId}/estudiantes`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ estudiante_ids: newStudents.map(s => s.id) })
                    });
                    const addData = await addRes.json();
                    if (addData.duplicados?.length > 0) {
                        alert(`${addData.duplicados.length} estudiante(s) ya estaban asignados en otra configuración.`);
                    }
                }

                closeModal();
                location.reload();
            } catch (err) {
                alert('Error de conexión.');
            }
        }
    });

    /* ========== STUDENT SEARCH ========== */
    async function loadAvailableStudents() {
        try {
            const res = await fetch(`${BASE}/api/casos/${CASO_ID}/estudiantes-disponibles`);
            const data = await res.json();
            availableStudents = data.estudiantes || [];
        } catch (e) {
            availableStudents = [];
        }
    }

    searchInput?.addEventListener('input', () => {
        const q = searchInput.value.trim().toLowerCase();
        if (q.length < 2) {
            resultsDiv.style.display = 'none';
            return;
        }

        const selectedIds = new Set(selectedStudents.map(s => String(s.id)));
        const matches = availableStudents.filter(s =>
            !selectedIds.has(String(s.estudiante_id)) &&
            (`${s.nombres} ${s.apellidos}`.toLowerCase().includes(q) ||
                (s.cedula || '').includes(q))
        ).slice(0, 10);

        if (matches.length === 0) {
            resultsDiv.innerHTML = '<div class="gc-sr-empty">Sin resultados</div>';
        } else {
            resultsDiv.innerHTML = matches.map(s =>
                `<div class="gc-sr-item" data-id="${s.estudiante_id}" data-nombres="${esc(s.nombres)}" data-apellidos="${esc(s.apellidos)}" data-cedula="${esc(s.cedula || '')}">
                    <span class="gc-sr-name">${esc(s.nombres)} ${esc(s.apellidos)}</span>
                    <span class="gc-sr-info">CI: ${esc(s.cedula || '—')} · ${esc(s.seccion_nombre || '')}</span>
                </div>`
            ).join('');
        }
        resultsDiv.style.display = 'block';
    });

    resultsDiv?.addEventListener('click', e => {
        const item = e.target.closest('.gc-sr-item');
        if (!item) return;
        selectedStudents.push({
            id: item.dataset.id,
            nombres: item.dataset.nombres,
            apellidos: item.dataset.apellidos,
            cedula: item.dataset.cedula,
            existing: false
        });
        renderSelected();
        searchInput.value = '';
        resultsDiv.style.display = 'none';
    });

    document.addEventListener('click', e => {
        if (!e.target.closest('.gc-student-search')) {
            resultsDiv.style.display = 'none';
        }
    });

    function renderSelected() {
        if (!selectedDiv) return;
        if (selectedStudents.length === 0) {
            selectedDiv.innerHTML = '<p class="gc-empty-text">No hay estudiantes seleccionados.</p>';
            return;
        }
        selectedDiv.innerHTML = selectedStudents.map((s, i) =>
            `<div class="gc-student-chip${s.existing ? ' existing' : ''}">
                <span>${esc(s.nombres)} ${esc(s.apellidos)} <small>(${esc(s.cedula || '—')})</small></span>
                <button type="button" class="gc-chip-remove" data-idx="${i}" title="${s.existing ? 'Quitar de la asignación' : 'Quitar'}">&times;</button>
            </div>`
        ).join('');
    }

    selectedDiv?.addEventListener('click', async e => {
        const btn = e.target.closest('.gc-chip-remove');
        if (!btn) return;
        const idx = parseInt(btn.dataset.idx);
        const student = selectedStudents[idx];

        if (student.existing && student.asignacion_id && hiddenId.value) {
            const confirmed = await showConfirm(
                `Se quitará a <strong>${esc(student.nombres)} ${esc(student.apellidos)}</strong> (CI: ${esc(student.cedula || '—')}) de esta asignación.<br><br>Si el estudiante ya tiene intentos registrados, será <strong>desactivado</strong> en lugar de eliminado y sus intentos se conservarán.`,
                '¿Quitar estudiante?',
                { confirmText: 'Quitar', icon: '👤' }
            );
            if (!confirmed) return;
            try {
                const res = await fetch(`${BASE}/api/configs/${hiddenId.value}/estudiantes/${student.asignacion_id}`, {
                    method: 'DELETE'
                });
                const data = await res.json();
                if (data.ok) {
                    selectedStudents.splice(idx, 1);
                    renderSelected();
                } else {
                    alert(data.error || 'Error.');
                }
            } catch (err) {
                alert('Error de conexión.');
            }
        } else {
            selectedStudents.splice(idx, 1);
            renderSelected();
        }
    });

    /* ========== helpers ========== */
    function esc(str) {
        const div = document.createElement('div');
        div.textContent = str || '';
        return div.innerHTML;
    }
})();
