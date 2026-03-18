/**
 * crud_manager.js — Generic CRUD boilerplate for sucesión pages.
 *
 * Eliminates the duplicated delete, submit, and button management
 * code across ~20 CRUD pages. Page-specific functions (getFormData,
 * resetForm, renderTable, validateForm, editarXxx) remain inline.
 *
 * Usage:
 *   var crud = initCrudManager({
 *       intentoId:   INTENTO_ID,
 *       baseUrl:     BASE,
 *       apiSlug:     'banco',                // → /api/banco/{id}/agregar|editar|eliminar
 *       items:       bancos,                 // reference to the items array
 *       formSel:     'form',                 // CSS selector for the form
 *       btnSel:      'button[type=submit]',  // CSS selector for the submit button
 *       getFormData: function() { ... },     // page-specific
 *       resetForm:   function() { ... },     // page-specific
 *       renderTable: function() { ... },     // page-specific
 *       editName:    'editarBanco',          // global name for edit function
 *       deleteName:  'eliminarBanco',        // global name for delete function
 *       fillForm:    function(item) { ... }, // page-specific: fill form from item
 *       validateForm: function() { ... }     // page-specific: call after fill
 *   });
 *
 *   // The manager exposes:
 *   //   crud.editIndex       — current edit index (null = new)
 *   //   crud.setEditIndex(n) — set edit index
 *   //   crud.setBtnSave()    — set button text to "Guardar"
 *   //   crud.setBtnUpdate()  — set button text to "Actualizar"
 *
 * Anti-crash: all operations wrapped in try/catch.
 */
(function () {
    'use strict';

    function initCrudManager(opts) {
        try {
            if (!opts) {
                console.error('[CRUD Manager] initCrudManager called without options');
                return null;
            }

            var intentoId   = opts.intentoId;
            var baseUrl     = (opts.baseUrl || '').replace(/\/+$/, '');
            var apiSlug     = opts.apiSlug;
            var items       = opts.items;
            var getFormData = opts.getFormData;
            var resetFormFn = opts.resetForm;
            var renderTable = opts.renderTable;
            var fillForm    = opts.fillForm;
            var validateFn  = opts.validateForm;

            var form = document.querySelector(opts.formSel || 'form');
            var btn  = form ? form.querySelector(opts.btnSel || 'button[type=submit]') : null;

            if (!form || !btn) {
                console.error('[CRUD Manager] Form or button not found');
                return null;
            }

            // State
            var state = {
                editIndex: null
            };

            // ─── Button helpers ───────────────────────────────────────────
            function setBtnText(text) {
                try {
                    btn.textContent = text + ' ';
                    var icon = document.createElement('i');
                    icon.className = 'bi-save';
                    btn.appendChild(icon);
                } catch (err) {
                    console.error('[CRUD Manager] setBtnText error:', err);
                }
            }

            function setBtnSave() {
                setBtnText('Guardar');
            }

            function setBtnUpdate() {
                setBtnText('Actualizar');
            }

            // ─── Delete handler ───────────────────────────────────────────
            if (opts.deleteName) {
                window[opts.deleteName] = function (idx) {
                    try {
                        if (!confirm('¿Está seguro de eliminar este registro?')) return;
                        if (!intentoId) { alert('No hay intento activo'); return; }

                        fetch(baseUrl + '/api/' + apiSlug + '/' + intentoId + '/eliminar', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ index: idx })
                        })
                            .then(function (r) { return r.json(); })
                            .then(function (data) {
                                try {
                                    if (data.ok) {
                                        items.splice(idx, 1);
                                        if (typeof renderTable === 'function') renderTable();
                                    } else {
                                        alert(data.error || 'Error al eliminar');
                                    }
                                } catch (err) {
                                    console.error('[CRUD Manager] delete response error:', err);
                                }
                            })
                            .catch(function () { alert('Error de conexión'); });
                    } catch (err) {
                        console.error('[CRUD Manager] delete error:', err);
                    }
                };
            }

            // ─── Edit handler ─────────────────────────────────────────────
            if (opts.editName && typeof fillForm === 'function') {
                window[opts.editName] = function (idx) {
                    try {
                        var item = items[idx];
                        if (!item) return;
                        state.editIndex = idx;

                        fillForm(item);
                        setBtnUpdate();

                        if (typeof validateFn === 'function') validateFn();
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    } catch (err) {
                        console.error('[CRUD Manager] edit error:', err);
                    }
                };
            }

            // ─── Submit handler ───────────────────────────────────────────
            form.addEventListener('submit', function (e) {
                try {
                    e.preventDefault();
                    if (!intentoId) { alert('No hay intento activo'); return; }

                    var formData = typeof getFormData === 'function' ? getFormData() : {};
                    var isEdit = state.editIndex !== null;
                    var url = isEdit
                        ? baseUrl + '/api/' + apiSlug + '/' + intentoId + '/editar'
                        : baseUrl + '/api/' + apiSlug + '/' + intentoId + '/agregar';

                    if (isEdit) formData.index = state.editIndex;

                    fetch(url, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(formData)
                    })
                        .then(function (r) { return r.json(); })
                        .then(function (data) {
                            try {
                                if (data.ok) {
                                    if (isEdit) {
                                        items[state.editIndex] = formData;
                                    } else {
                                        items.push(formData);
                                    }
                                    if (typeof renderTable === 'function') renderTable();
                                    if (typeof resetFormFn === 'function') resetFormFn();
                                    state.editIndex = null;
                                    setBtnSave();
                                    btn.disabled = true;
                                } else {
                                    alert(data.error || 'Error al guardar');
                                }
                            } catch (err) {
                                console.error('[CRUD Manager] submit response error:', err);
                            }
                        })
                        .catch(function () { alert('Error de conexión'); });
                } catch (err) {
                    console.error('[CRUD Manager] submit error:', err);
                }
            });

            // ─── Public API ───────────────────────────────────────────────
            return {
                get editIndex() { return state.editIndex; },
                setEditIndex: function (n) { state.editIndex = n; },
                setBtnSave: setBtnSave,
                setBtnUpdate: setBtnUpdate,
                form: form,
                btn: btn
            };

        } catch (err) {
            console.error('[CRUD Manager] init error:', err);
            return null;
        }
    }

    // Expose globally
    window.initCrudManager = initCrudManager;
})();
