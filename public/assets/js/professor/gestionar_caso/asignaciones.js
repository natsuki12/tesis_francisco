/**
 * asignaciones.js
 * CRUD frontend para configuraciones de asignaciones en Gestionar Caso.
 * Usa window.__casoId y window.__baseUrl inyectados desde PHP.
 */
(function () {
    'use strict';

    const BASE = window.__baseUrl || '';
    const CASO_ID = window.__casoId || 0;


    /* ========== DOM refs ========== */
    const modal = document.getElementById('asignacionModal');
    const modalTitle = document.getElementById('asignacionModalTitle');
    const form = document.getElementById('formAsignacion');
    const hiddenId = document.getElementById('editConfigId');
    const elModalidad = document.getElementById('cfgModalidad');
    const elNombre = document.getElementById('cfgNombre');
    const elIntentos = document.getElementById('cfgMaxIntentos');
    const elApertura = document.getElementById('cfgFechaApertura');
    const elLimite = document.getElementById('cfgFechaLimite');
    const elTipoCalif = document.getElementById('cfgTipoCalificacion');
    const elTipoCalifRow = document.getElementById('tipoCalifRow');
    const searchInput = document.getElementById('studentSearch');
    const selectedDiv = document.getElementById('selectedStudents');
    const counterEl = document.getElementById('studentCounter');
    const seccionSelect = document.getElementById('cfgSeccionFilter');
    const bulkActions = document.getElementById('bulkStudentActions');
    const btnSelectAll = document.getElementById('btnSelectAllSection');
    const btnRemoveAll = document.getElementById('btnRemoveAll');
    const errContainer = document.getElementById('asigErrors');
    const errList = document.getElementById('asigErrorsList');

    let selectedStudents = [];
    let editRules = null;
    let availableStudents = [];
    let isEditMode = false;
    let currentSeccion = '';
    let autocompleteEst = null;

    if (!modal) return;

    /* ========== INLINE ERRORS (uses global modal-error-box) ========== */
    function showErrors(messages) {
        if (!errContainer || !errList) return;
        if (!Array.isArray(messages)) messages = [messages];
        errList.innerHTML = messages.map(m => `<li>${m}</li>`).join('');
        errContainer.classList.add('show');
        // Shake animation for attention
        errContainer.classList.remove('shake');
        void errContainer.offsetWidth; // force reflow
        errContainer.classList.add('shake');
        // Scroll modal body to make errors visible
        const modalBody = errContainer.closest('.modal-base__body');
        if (modalBody) {
            modalBody.scrollTop = errContainer.offsetTop - modalBody.offsetTop - 10;
        }
    }

    function clearErrors() {
        if (errContainer) errContainer.classList.remove('show', 'shake');
        if (errList) errList.innerHTML = '';
    }

    /* ========== BUTTONS STATE ========== */
    function updateBulkButtonsState() {
        if (!bulkActions || bulkActions.style.display === 'none') return; // no visible

        if (btnRemoveAll) {
            btnRemoveAll.disabled = selectedStudents.length === 0;
        }

        if (btnSelectAll) {
            const seccion = seccionSelect ? seccionSelect.value : '';
            const selectedIds = new Set(selectedStudents.map(s => String(s.id)));
            const toAdd = availableStudents.filter(s =>
                !selectedIds.has(String(s.estudiante_id)) &&
                (!seccion || (s.seccion_nombre || '').split(', ').includes(seccion))
            );
            btnSelectAll.disabled = toAdd.length === 0;
        }
    }

    /* ========== COUNTER ========== */
    function updateCounter() {
        if (counterEl) counterEl.textContent = selectedStudents.length;
    }

    /* ========== SECTION FILTER ========== */
    function populateSecciones() {
        if (!seccionSelect) return;
        const secciones = [...new Set(availableStudents.flatMap(s => (s.seccion_nombre || '').split(', ')).filter(Boolean))].sort();
        // Keep the "Todas" option, remove dynamic ones
        seccionSelect.innerHTML = '<option value="">— Todas —</option>';
        secciones.forEach(sec => {
            const opt = document.createElement('option');
            opt.value = sec;
            opt.textContent = sec;
            seccionSelect.appendChild(opt);
        });
        currentSeccion = '';
    }

    if (seccionSelect) {
        seccionSelect.addEventListener('change', () => {
            currentSeccion = seccionSelect.value;
            if (autocompleteEst) { autocompleteEst._cache.clear(); autocompleteEst.close(); }
            if (searchInput) { searchInput.value = ''; searchInput.focus(); }
            updateBulkButtonsState(); // Update btn state on filter change
        });
    }

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
        isEditMode = false;
        currentSeccion = '';
        if (autocompleteEst) { autocompleteEst._cache.clear(); autocompleteEst.close(); }
        clearErrors();
        updateCounter();
        renderSelected();
        if (searchInput) searchInput.value = '';
        elModalidad.disabled = false;
        elIntentos.min = 0;
        elModalidad.title = '';
        elIntentos.title = '';
        if (elTipoCalif) elTipoCalif.value = 'aprobado_reprobado';
        toggleTipoCalif();
        if (seccionSelect) seccionSelect.value = '';
        // Show bulk actions (create mode)
        if (bulkActions) bulkActions.style.display = '';
    }

    document.getElementById('btnCloseModal')?.addEventListener('click', closeModal);
    document.getElementById('btnCancelModal')?.addEventListener('click', closeModal);
    modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });

    /* ========== NUEVA ASIGNACIÓN ========== */
    document.getElementById('btnNuevaAsignacion')?.addEventListener('click', async () => {
        resetForm();
        modalTitle.textContent = 'Nueva Asignación';
        isEditMode = false;
        if (bulkActions) bulkActions.style.display = '';
        if (searchInput) { searchInput.disabled = true; searchInput.placeholder = 'Cargando estudiantes...'; }
        await loadAvailableStudents();
        if (searchInput) { searchInput.disabled = false; searchInput.placeholder = 'Nombre, cédula o correo...'; }
        populateSecciones();
        openModal();
    });

    /* ========== EDITAR ========== */
    document.querySelectorAll('.btnEditConfig').forEach(btn => {
        btn.addEventListener('click', async () => {
            const configId = btn.dataset.configId;
            resetForm();
            modalTitle.textContent = 'Editar Asignación';
            hiddenId.value = configId;
            isEditMode = true;
            // Hide bulk actions in edit mode
            if (bulkActions) bulkActions.style.display = 'none';

            try {
                const res = await fetch(`${BASE}/api/casos/${CASO_ID}/configs`);
                const data = await res.json();
                if (!data.ok) return;

                const cfg = data.configs.find(c => String(c.id) === String(configId));
                if (!cfg) return;

                elModalidad.value = cfg.modalidad || 'Practica_Libre';
                if (elNombre) elNombre.value = cfg.nombre || '';
                elIntentos.value = cfg.max_intentos || 0;
                if (elTipoCalif) elTipoCalif.value = cfg.tipo_calificacion || 'aprobado_reprobado';
                toggleTipoCalif();
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
                    seccion: '',
                    asignacion_id: e.asignacion_id,
                    existing: true
                }));
                updateCounter();
                renderSelected();

                if (searchInput) { searchInput.disabled = true; searchInput.placeholder = 'Cargando estudiantes...'; }
                await loadAvailableStudents();
                if (searchInput) { searchInput.disabled = false; searchInput.placeholder = 'Nombre, cédula o correo...'; }
                populateSecciones();
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

            const confirmed = await window.showConfirm({
                title,
                message: msg,
                icon: canDelete ? 'danger' : 'warning',
                confirmText,
                confirmStyle: canDelete ? 'danger' : 'primary'
            });
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

    /* ========== REACTIVAR CONFIG ========== */
    document.querySelectorAll('.btnReactivarConfig').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            e.stopPropagation();
            const configId = btn.dataset.configId;

            const confirmed = await window.showConfirm({
                title: '¿Reactivar asignación?',
                message: 'Esta asignación volverá a estar activa y los estudiantes podrán iniciar nuevos intentos.',
                icon: 'info',
                confirmText: 'Reactivar',
                confirmStyle: 'primary'
            });
            if (!confirmed) return;

            try {
                const res = await fetch(`${BASE}/api/configs/${configId}`, {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ status: 'Activo' })
                });
                const data = await res.json();
                if (data.ok) {
                    location.reload();
                } else {
                    alert(data.error || 'Error al reactivar.');
                }
            } catch (err) {
                alert('Error de conexión.');
            }
        });
    });

    /* ========== VALIDATION ========== */
    function validateForm() {
        const errors = [];
        const now = new Date();

        const apertura = elApertura.value ? new Date(elApertura.value) : null;
        const cierre = elLimite.value ? new Date(elLimite.value) : null;

        // Only validate dates-in-past for NEW assignments (edit may have dates already passed)
        if (!isEditMode) {
            if (apertura && apertura < now) {
                errors.push('La fecha de apertura no puede ser anterior al momento actual.');
            }
            if (cierre && cierre < now) {
                errors.push('La fecha de cierre no puede ser anterior al momento actual.');
            }
        }
        if (apertura && cierre && cierre <= apertura) {
            errors.push('La fecha de cierre debe ser posterior a la fecha de apertura.');
        }
        if (!isEditMode && selectedStudents.length === 0) {
            errors.push('Debe seleccionar al menos un estudiante.');
        }

        const intentos = parseInt(elIntentos.value) || 0;
        if (intentos < 0) {
            errors.push('El máximo de intentos no puede ser negativo.');
        }
        if (intentos > 100) {
            errors.push('El máximo de intentos no puede superar 100.');
        }

        return errors;
    }

    /* ========== GUARDAR ========== */
    const btnSave = document.getElementById('btnSaveAsignacion');
    btnSave?.addEventListener('click', async () => {
        clearErrors();
        const validationErrors = validateForm();
        if (validationErrors.length > 0) {
            showErrors(validationErrors);
            return;
        }

        // Prevent double-submit
        btnSave.disabled = true;
        btnSave.textContent = 'Guardando...';

        const configId = hiddenId.value;
        const isEdit = !!configId;

        const payload = {
            nombre: elNombre ? elNombre.value.trim() : '',
            modalidad: elModalidad.value,
            max_intentos: Math.max(0, Math.min(100, parseInt(elIntentos.value) || 0)),
            tipo_calificacion: elTipoCalif ? elTipoCalif.value : 'aprobado_reprobado',
            fecha_apertura: elApertura.value || null,
            fecha_limite: elLimite.value || null,
        };

        try {
            if (!isEdit) {
                payload.estudiante_ids = selectedStudents.map(s => s.id);
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
                    showErrors(data.error || 'Error al crear la asignación.');
                }
            } else {
                const res = await fetch(`${BASE}/api/configs/${configId}`, {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (!data.ok) {
                    showErrors(data.error || 'Error al actualizar.');
                    return;
                }

                let hasDuplicates = false;
                const newStudents = selectedStudents.filter(s => !s.existing);
                if (newStudents.length > 0) {
                    const addRes = await fetch(`${BASE}/api/configs/${configId}/estudiantes`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ estudiante_ids: newStudents.map(s => s.id) })
                    });
                    const addData = await addRes.json();
                    if (addData.duplicados?.length > 0) {
                        showErrors(`${addData.duplicados.length} estudiante(s) ya estaban asignados en esta configuración.`);
                        hasDuplicates = true;
                    }
                }

                if (!hasDuplicates) {
                    closeModal();
                    location.reload();
                }
            }
        } catch (err) {
            showErrors('Error de conexión. Intente de nuevo.');
        } finally {
            btnSave.disabled = false;
            btnSave.textContent = 'Guardar';
        }
    });

    /* ========== BULK ACTIONS ========== */
    // Select all from current section
    if (btnSelectAll) {
        btnSelectAll.addEventListener('click', () => {
            clearErrors();
            const seccion = seccionSelect ? seccionSelect.value : '';
            const selectedIds = new Set(selectedStudents.map(s => String(s.id)));
            const toAdd = availableStudents.filter(s =>
                !selectedIds.has(String(s.estudiante_id)) &&
                (!seccion || (s.seccion_nombre || '').split(', ').includes(seccion))
            );
            if (toAdd.length === 0) {
                showErrors('No hay estudiantes disponibles, o ya están todos seleccionados.');
                return;
            }
            toAdd.forEach(s => selectedStudents.push({
                id: s.estudiante_id,
                nombres: s.nombres,
                apellidos: s.apellidos,
                cedula: s.cedula,
                seccion: s.seccion_nombre || '',
                existing: false
            }));
            updateCounter();
            renderSelected();
        });
    }

    // Remove all
    if (btnRemoveAll) {
        btnRemoveAll.addEventListener('click', () => {
            clearErrors();
            if (selectedStudents.length === 0) return;
            if (isEditMode) {
                // In edit mode, only remove non-existing (newly added)
                selectedStudents = selectedStudents.filter(s => s.existing);
            } else {
                selectedStudents.length = 0;
            }
            updateCounter();
            renderSelected();
        });
    }

    /* ========== STUDENT SEARCH (AutocompleteDropdown) ========== */
    async function loadAvailableStudents() {
        try {
            const res = await fetch(`${BASE}/api/casos/${CASO_ID}/estudiantes-disponibles`);
            const data = await res.json();
            availableStudents = data.estudiantes || [];
        } catch (e) {
            availableStudents = [];
        }
        updateBulkButtonsState(); // Update after fetching
    }

    if (searchInput && window.AutocompleteDropdown) {
        autocompleteEst = new AutocompleteDropdown({
            input: searchInput,
            minLength: 0,
            debounceMs: 150,
            fetchFn: async (query) => {
                const q = query.toLowerCase();
                const selectedIds = new Set(selectedStudents.map(s => String(s.id)));
                return availableStudents.filter(s =>
                    !selectedIds.has(String(s.estudiante_id)) &&
                    (!currentSeccion || (s.seccion_nombre || '').split(', ').includes(currentSeccion)) &&
                    (q.length === 0 ||
                     `${s.nombres} ${s.apellidos}`.toLowerCase().includes(q) ||
                     (s.cedula || '').includes(q) ||
                     (s.email || '').toLowerCase().includes(q))
                ).slice(0, 10);
            },
            renderItem: (item) => {
                let ci = item.cedula || '—';
                if (ci !== '—' && !ci.includes('-')) {
                    ci = (item.nacionalidad || 'V') + '-' + ci;
                }
                const nombre = `${esc(item.nombres)} ${esc(item.apellidos)}`;
                const email = item.email ? esc(item.email) : '';
                const seccion = item.seccion_nombre ? esc(item.seccion_nombre) : '';
                return `
                    <div style="line-height:1.4;">
                        <span class="ac-dropdown__cedula">${esc(ci)}</span>
                        <span class="ac-dropdown__sep">—</span>
                        <span class="ac-dropdown__name">${nombre}</span>
                        <br>
                        <small style="color:#64748b;">${email}${email && seccion ? ' · ' : ''}${seccion}</small>
                    </div>`;
            },
            onSelect: (item) => {
                clearErrors();
                selectedStudents.push({
                    id: item.estudiante_id,
                    nombres: item.nombres,
                    apellidos: item.apellidos,
                    cedula: item.cedula,
                    seccion: item.seccion_nombre || '',
                    existing: false
                });
                updateCounter();
                renderSelected();
                searchInput.value = '';
                setTimeout(() => {
                    if (autocompleteEst) autocompleteEst.close();
                    searchInput.blur();
                }, 80);
            }
        });
    }

    function renderSelected() {
        if (!selectedDiv) return;
        if (selectedStudents.length === 0) {
            selectedDiv.innerHTML = '<div class="gc-empty-students"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="32" height="32"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><line x1="19" y1="11" x2="23" y2="11"></line></svg><span>No hay estudiantes seleccionados</span></div>';
            updateBulkButtonsState(); // Ensure empty means disabled Remove All
            return;
        }
        selectedDiv.innerHTML = selectedStudents.map((s, i) => {
            const secLabel = s.seccion ? ` <span class="gc-chip-section">· ${esc(s.seccion)}</span>` : '';
            return `<div class="gc-student-chip${s.existing ? ' existing' : ''}">
                <span>${esc(s.nombres)} ${esc(s.apellidos)} <small>(${esc(s.cedula || '—')})</small>${secLabel}</span>
                <button type="button" class="gc-chip-remove" data-idx="${i}" title="${s.existing ? 'Quitar de la asignación' : 'Quitar'}">&times;</button>
            </div>`;
        }).join('');
        updateBulkButtonsState(); // Check button states whenever UI updates
    }

    selectedDiv?.addEventListener('click', async e => {
        const btn = e.target.closest('.gc-chip-remove');
        if (!btn) return;
        const idx = parseInt(btn.dataset.idx);
        const student = selectedStudents[idx];

        if (student.existing && student.asignacion_id && hiddenId.value) {
            const confirmed = await window.showConfirm({
                title: '¿Quitar estudiante?',
                message: `Se quitará a <strong>${esc(student.nombres)} ${esc(student.apellidos)}</strong> (CI: ${esc(student.cedula || '—')}) de esta asignación.<br><br>Si el estudiante ya tiene intentos registrados, será <strong>desactivado</strong> en lugar de eliminado y sus intentos se conservarán.`,
                icon: 'warning',
                confirmText: 'Quitar',
                confirmStyle: 'warning'
            });
            if (!confirmed) return;
            try {
                const res = await fetch(`${BASE}/api/configs/${hiddenId.value}/estudiantes/${student.asignacion_id}`, {
                    method: 'DELETE'
                });
                const data = await res.json();
                if (data.ok) {
                    selectedStudents.splice(idx, 1);
                    updateCounter();
                    renderSelected();
                } else {
                    showErrors(data.error || 'Error al quitar estudiante.');
                }
            } catch (err) {
                showErrors('Error de conexión.');
            }
        } else {
            selectedStudents.splice(idx, 1);
            updateCounter();
            renderSelected();
        }
    });

    // Clear errors when user interacts with form fields
    /* ========== TOGGLE TIPO CALIFICACION ========== */
    function toggleTipoCalif() {
        // Tipo de calificación is now always visible for all modalities
    }

    [elApertura, elLimite, elModalidad, elIntentos].forEach(el => {
        if (el) el.addEventListener('change', clearErrors);
    });

    /* ========== helpers ========== */
    function esc(str) {
        const div = document.createElement('div');
        div.textContent = str || '';
        return div.innerHTML;
    }

    /* ========== DETAIL MODAL (click row → show students) ========== */
    const detailModal = document.getElementById('modalDetalleAsignacion');
    const detailTitle = document.getElementById('detalleAsigTitulo');
    const detailBody = document.getElementById('detalleAsigBody');
    const detailTable = document.getElementById('detalleAsigTabla');
    const detailEmpty = document.getElementById('detalleAsigEmpty');
    const detailSummary = document.getElementById('detalleAsigSummary');

    function openDetailModal(row) {
        const nombre = row.dataset.nombre || 'Asignación';
        const modalidad = row.dataset.modalidad || '';
        const modClass = row.dataset.modClass || 'mode-libre';
        const intentos = row.dataset.intentos || '∞';
        const periodo = row.dataset.periodo || '';
        const estado = row.dataset.estado || 'Activa';
        let estudiantes = [];
        try { estudiantes = JSON.parse(row.dataset.estudiantes || '[]'); } catch(e) {}

        // Title
        detailTitle.textContent = nombre;

        // Summary badges
        detailSummary.innerHTML = `
            <span class="mode-badge ${modClass}">${esc(modalidad)}</span>
            <span class="status-badge status-draft has-dot" style="font-size:12px;">${esc(intentos === '∞' ? 'Ilimitados' : intentos + ' intentos')}</span>
            <span style="font-size:12px;color:var(--gray-500);">📅 ${esc(periodo)}</span>
            <span class="status-badge ${estado === 'Activa' ? 'status-active' : 'status-draft'} has-dot" style="margin-left:auto;font-size:12px;">${esc(estado)}</span>
        `;

        // Students table
        detailBody.innerHTML = '';

        if (estudiantes.length === 0) {
            detailTable.style.display = 'none';
            detailEmpty.style.display = '';
        } else {
            detailTable.style.display = '';
            detailEmpty.style.display = 'none';
            estudiantes.forEach(est => {
                const fullName = esc((est.nombres || '') + ' ' + (est.apellidos || ''));
                const cedula = esc(est.cedula || '—');
                const estado = est.estado || 'Pendiente';
                const badgeClass = estado === 'Completado' ? 'status-completed' : 'status-review';
                detailBody.innerHTML += `
                    <tr>
                        <td>${fullName}</td>
                        <td>${cedula}</td>
                        <td><span class="status-badge ${badgeClass} has-dot">${esc(estado)}</span></td>
                    </tr>`;
            });
        }

        detailModal.style.display = '';
    }

    function closeDetailModal() {
        detailModal.style.display = 'none';
    }

    // Row click → open detail
    document.querySelectorAll('.asig-row').forEach(row => {
        row.style.cursor = 'pointer';
        row.addEventListener('click', (e) => {
            if (e.target.closest('.row-actions')) return;
            openDetailModal(row);
        });
    });

    // Close detail modal
    document.getElementById('btnCloseDetalle')?.addEventListener('click', closeDetailModal);
    document.getElementById('btnCloseDetalle2')?.addEventListener('click', closeDetailModal);
    detailModal?.addEventListener('click', e => { if (e.target === detailModal) closeDetailModal(); });
})();
