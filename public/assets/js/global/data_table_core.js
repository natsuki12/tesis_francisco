/**
 * data_table_core.js
 * Universal Client-Side DataTable Engine with SSR DOM Morphing
 * Automatically paginates, filters, and sorts any <table class="data-table">
 */

window.DataTableManager = (function() {
    const tableInstances = {}; // Store states for multiple tables per page if needed
    const _reloadLocks = {};   // Mutex: evita recargas simultáneas por tabla

    function initTable(tableEl) {
        const tableId = tableEl.id;
        if (!tableId) return;

        const tbody = tableEl.querySelector('tbody');
        // Accept empty tables gracefully
        if (!tbody) return;

        let rows = Array.from(tbody.querySelectorAll('tr[data-search]'));
        
        const searchInput = document.querySelector(`[data-search-for="${tableId}"]`);
        const perPageSel = document.querySelector(`[data-perpage-for="${tableId}"]`);
        const footer = document.querySelector(`[data-footer-for="${tableId}"]`);
        const footerInfo = footer ? footer.querySelector('.table-footer-info') : null;
        const paginationEl = footer ? footer.querySelector('.pagination') : null;

        let state = {
            searchTerm: '',
            currentPage: 1,
            sortCol: null,
            sortDir: 1,
            rows: rows,
            tbody: tbody
        };

        tableInstances[tableId] = state;

        function getPerPage() { return parseInt(perPageSel?.value || tableEl.dataset.perPage || '10', 10); }

        function getVisible() {
            return state.rows.filter(r => !state.searchTerm || (r.dataset.search || '').includes(state.searchTerm));
        }

        function sortRows(arr) {
            if (state.sortCol === null) return arr;
            return arr.slice().sort((a, b) => {
                const va = (a.children[state.sortCol]?.textContent || '').trim().toLowerCase();
                const vb = (b.children[state.sortCol]?.textContent || '').trim().toLowerCase();
                const na = parseFloat(va.replace(/[^\d.-]/g, ''));
                const nb = parseFloat(vb.replace(/[^\d.-]/g, ''));
                if (!isNaN(na) && !isNaN(nb)) return state.sortDir * (na - nb);
                return state.sortDir * va.localeCompare(vb);
            });
        }

        function render() {
            // Eliminar estado vacío previo si existe
            const oldEmpty = state.tbody.querySelector('.empty-state-row');
            if (oldEmpty) oldEmpty.remove();

            const visible = sortRows(getVisible());

            if (visible.length === 0) {
                if (footerInfo) footerInfo.innerHTML = 'Mostrando <strong>0</strong> registros';
                if (paginationEl) paginationEl.innerHTML = '';
                state.rows.forEach(r => r.style.display = 'none');
                
                const emptyRow = document.createElement('tr');
                emptyRow.className = 'empty-state-row';
                // Mensaje diferente si la tabla está totalmente vacía desde la BD o si fue un filtro de búsqueda
                const isEmptyDB = state.rows.length === 0;
                emptyRow.innerHTML = `<td colspan="100" style="text-align:center; padding: 60px 20px; color: var(--sim-text-light, #6b7280); background: transparent;">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="opacity:0.4; margin-bottom:12px;">
                        ${isEmptyDB 
                            ? `<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="12" y1="18" x2="12" y2="12"></line><line x1="9" y1="15" x2="15" y2="15"></line>`
                            : `<circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line>`
                        }
                    </svg>
                    <div style="font-size:16px; font-weight:600; color:var(--text-dark, #374151);">${isEmptyDB ? 'Aún no hay registros' : 'No se encontraron resultados'}</div>
                    <div style="font-size:14px; opacity:0.8; margin-top:4px;">${isEmptyDB ? 'Agrega un nuevo registro para comenzar' : 'Intenta ajustando los términos de tu búsqueda'}</div>
                </td>`;
                state.tbody.appendChild(emptyRow);
                return;
            }
            
            const PER_PAGE = getPerPage();
            const totalPages = Math.max(1, Math.ceil(visible.length / PER_PAGE));
            if (state.currentPage > totalPages) state.currentPage = totalPages;
            const start = (state.currentPage - 1) * PER_PAGE;
            const pageRows = visible.slice(start, start + PER_PAGE);

            // Reorder DOM to match sort order
            visible.forEach(r => state.tbody.appendChild(r));

            state.rows.forEach(r => r.style.display = 'none');
            pageRows.forEach(r => r.style.display = '');

            if (footerInfo) {
                const from = visible.length > 0 ? start + 1 : 0;
                const to = Math.min(start + PER_PAGE, visible.length);
                footerInfo.innerHTML = `Mostrando <strong>${from}</strong> a <strong>${to}</strong> de <strong>${visible.length}</strong> registros`;
            }

            if (paginationEl) {
                paginationEl.innerHTML = '';
                if (totalPages > 1) {
                    const prev = document.createElement('button');
                    prev.innerHTML = '‹'; prev.disabled = state.currentPage === 1;
                    prev.addEventListener('click', () => { state.currentPage--; render(); });
                    paginationEl.appendChild(prev);
                    for (let p = 1; p <= totalPages; p++) {
                        const b = document.createElement('button');
                        b.textContent = p;
                        if (p === state.currentPage) b.classList.add('active');
                        b.addEventListener('click', () => { state.currentPage = p; render(); });
                        paginationEl.appendChild(b);
                    }
                    const next = document.createElement('button');
                    next.innerHTML = '›'; next.disabled = state.currentPage === totalPages;
                    next.addEventListener('click', () => { state.currentPage++; render(); });
                    paginationEl.appendChild(next);
                }
            }
        }

        if (searchInput && !searchInput.dataset.bound) {
            searchInput.dataset.bound = 'true';
            let debounceTimer;
            searchInput.addEventListener('input', e => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    state.searchTerm = e.target.value.toLowerCase().trim();
                    state.currentPage = 1;
                    render();
                }, 250);
            });
        }

        if (perPageSel && !perPageSel.dataset.bound) {
            perPageSel.dataset.bound = 'true';
            perPageSel.addEventListener('change', () => { state.currentPage = 1; render(); });
        }

        tableEl.querySelectorAll('th.sortable[data-col]').forEach(th => {
            if (th.dataset.bound) return;
            th.dataset.bound = 'true';
            th.style.cursor = 'pointer';
            th.addEventListener('click', () => {
                const col = parseInt(th.dataset.col, 10);
                if (state.sortCol === col) state.sortDir *= -1;
                else { state.sortCol = col; state.sortDir = 1; }
                tableEl.querySelectorAll('th.sortable').forEach(h => h.classList.remove('sort-asc', 'sort-desc'));
                th.classList.add(state.sortDir === 1 ? 'sort-asc' : 'sort-desc');
                render();
            });
        });

        // Store render function for manual morphing updates
        state.render = render;
        render();
    }

    // ═══════════════════════════════════════════════════════════
    //  SERVER-SIDE MODE — Activated by data-server-url attribute
    // ═══════════════════════════════════════════════════════════

    function initServerTable(tableEl) {
        const tableId = tableEl.id;
        if (!tableId) return;

        const serverUrl = tableEl.dataset.serverUrl;
        const tbody = tableEl.querySelector('tbody');
        if (!tbody || !serverUrl) return;

        // Columns from data-columns attribute: JSON array of field names
        const columns = JSON.parse(tableEl.dataset.columns || '[]');
        // Optional custom row renderer (global function name)
        const customRenderer = tableEl.dataset.render ? window[tableEl.dataset.render] : null;

        const searchInput = document.querySelector(`[data-search-for="${tableId}"]`);
        const perPageSel = document.querySelector(`[data-perpage-for="${tableId}"]`);
        const footer = document.querySelector(`[data-footer-for="${tableId}"]`);
        const footerInfo = footer ? footer.querySelector('.table-footer-info') : null;
        const paginationEl = footer ? footer.querySelector('.pagination') : null;

        const state = {
            currentPage: 1,
            searchTerm: '',
            sortCol: null,
            sortDir: 'DESC',
            totalRows: 0,
            totalPages: 1,
            loading: false,
            lastData: null,
            filters: {}   // Custom filters (e.g. {status: 'active'})
        };

        tableInstances[tableId] = state;

        function getPerPage() { return parseInt(perPageSel?.value || '10', 10); }

        function showSpinner() {
            const colCount = tableEl.querySelectorAll('thead th').length || columns.length + 1;
            tbody.innerHTML = `<tr><td colspan="${colCount}" style="text-align:center; padding:40px; color:var(--sim-text-light, #6b7280);">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" style="animation: button-spin 1.5s linear infinite; transform-origin:center;">
                    <polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline>
                    <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
                </svg>
                <div style="margin-top:8px; font-size:14px;">Cargando...</div>
            </td></tr>`;
        }

        function showEmpty(isSearch) {
            const colCount = tableEl.querySelectorAll('thead th').length || columns.length + 1;
            tbody.innerHTML = `<tr><td colspan="${colCount}" style="text-align:center; padding:60px 20px; color:var(--sim-text-light, #6b7280); background:transparent;">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="opacity:0.4; margin-bottom:12px;">
                    ${isSearch
                        ? '<circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line>'
                        : '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="12" y1="18" x2="12" y2="12"></line><line x1="9" y1="15" x2="15" y2="15"></line>'}
                </svg>
                <div style="font-size:16px; font-weight:600; color:var(--text-dark, #374151);">${isSearch ? 'No se encontraron resultados' : 'Aún no hay registros'}</div>
                <div style="font-size:14px; opacity:0.8; margin-top:4px;">${isSearch ? 'Intenta ajustando los términos de tu búsqueda' : 'Los registros aparecerán aquí automáticamente'}</div>
            </td></tr>`;
        }

        function renderRows(data) {
            state.lastData = data;
            const rows = data.rows || [];
            state.totalRows = data.total || 0;
            state.totalPages = data.pages || 1;
            state.currentPage = data.page || 1;

            if (rows.length === 0) {
                showEmpty(state.searchTerm !== '');
                if (footerInfo) footerInfo.innerHTML = 'Mostrando <strong>0</strong> registros';
                if (paginationEl) paginationEl.innerHTML = '';
                return;
            }

            let html = '';
            const perPage = getPerPage();
            const from = (state.currentPage - 1) * perPage + 1;
            const to = Math.min(from + rows.length - 1, state.totalRows);

            rows.forEach((row, i) => {
                if (customRenderer) {
                    html += customRenderer(row, from + i, data);
                } else {
                    // Default: render each column as a plain td
                    html += `<tr>`;
                    html += `<td style="color:var(--gray-400); font-size:13px;">${from + i}</td>`;
                    columns.forEach(col => {
                        html += `<td style="font-size:13px;">${escapeHtml(row[col] ?? '')}</td>`;
                    });
                    html += `</tr>`;
                }
            });

            tbody.innerHTML = html;

            // Footer info
            if (footerInfo) {
                footerInfo.innerHTML = `Mostrando <strong>${from}</strong> a <strong>${to}</strong> de <strong>${state.totalRows}</strong> registros`;
            }

            // Pagination
            if (paginationEl) {
                paginationEl.innerHTML = '';
                if (state.totalPages > 1) {
                    const prev = document.createElement('button');
                    prev.innerHTML = '‹'; prev.disabled = state.currentPage === 1;
                    prev.addEventListener('click', () => { state.currentPage--; fetchData(); });
                    paginationEl.appendChild(prev);

                    // Smart pagination: show max 7 buttons
                    const pages = buildPageNumbers(state.currentPage, state.totalPages);
                    pages.forEach(p => {
                        if (p === '...') {
                            const dots = document.createElement('span');
                            dots.textContent = '…';
                            dots.style.cssText = 'padding:4px 6px; color:var(--gray-400);';
                            paginationEl.appendChild(dots);
                        } else {
                            const b = document.createElement('button');
                            b.textContent = p;
                            if (p === state.currentPage) b.classList.add('active');
                            b.addEventListener('click', () => { state.currentPage = p; fetchData(); });
                            paginationEl.appendChild(b);
                        }
                    });

                    const next = document.createElement('button');
                    next.innerHTML = '›'; next.disabled = state.currentPage === state.totalPages;
                    next.addEventListener('click', () => { state.currentPage++; fetchData(); });
                    paginationEl.appendChild(next);
                }
            }

            // Entrada animación
            const visibleRows = Array.from(tbody.querySelectorAll('tr'));
            visibleRows.forEach((row, i) => {
                row.style.opacity = '0';
                row.style.transform = 'translateY(6px)';
                row.style.transition = `opacity 0.25s ease ${i * 30}ms, transform 0.25s ease ${i * 30}ms`;
                requestAnimationFrame(() => {
                    requestAnimationFrame(() => {
                        row.style.opacity = '1';
                        row.style.transform = 'translateY(0)';
                    });
                });
            });
            setTimeout(() => {
                visibleRows.forEach(row => {
                    row.style.removeProperty('opacity');
                    row.style.removeProperty('transform');
                    row.style.removeProperty('transition');
                });
            }, visibleRows.length * 30 + 400);
        }

        async function fetchData() {
            if (state.loading) return;
            state.loading = true;

            showSpinner();

            const params = new URLSearchParams({
                page: state.currentPage,
                limit: getPerPage(),
                search: state.searchTerm,
                ...(state.sortCol ? { sort: state.sortCol, order: state.sortDir } : {}),
                ...state.filters
            });

            try {
                const [res] = await Promise.all([
                    fetch(`${serverUrl}?${params}`),
                    new Promise(resolve => setTimeout(resolve, 600))
                ]);
                if (res.redirected || !res.ok) { location.reload(); return; }
                const data = await res.json();
                renderRows(data);
                // Dispatch event so views can update dynamic counters
                tableEl.dispatchEvent(new CustomEvent('datatable:loaded', { detail: data }));
            } catch (e) {
                console.error('[DataTable Server]', e);
                tbody.innerHTML = `<tr><td colspan="100" style="text-align:center; padding:40px; color:#ef4444;">Error al cargar datos</td></tr>`;
            } finally {
                state.loading = false;
            }
        }

        // Search (debounced)
        if (searchInput && !searchInput.dataset.bound) {
            searchInput.dataset.bound = 'true';
            let timer;
            searchInput.addEventListener('input', e => {
                clearTimeout(timer);
                timer = setTimeout(() => {
                    state.searchTerm = e.target.value.trim();
                    state.currentPage = 1;
                    fetchData();
                }, 350);
            });
        }

        // Per page
        if (perPageSel && !perPageSel.dataset.bound) {
            perPageSel.dataset.bound = 'true';
            perPageSel.addEventListener('change', () => { state.currentPage = 1; fetchData(); });
        }

        // Sort headers
        tableEl.querySelectorAll('th.sortable[data-sort-key]').forEach(th => {
            if (th.dataset.bound) return;
            th.dataset.bound = 'true';
            th.style.cursor = 'pointer';
            th.addEventListener('click', () => {
                const key = th.dataset.sortKey;
                if (state.sortCol === key) {
                    state.sortDir = state.sortDir === 'ASC' ? 'DESC' : 'ASC';
                } else {
                    state.sortCol = key;
                    state.sortDir = 'ASC';
                }
                tableEl.querySelectorAll('th.sortable').forEach(h => h.classList.remove('sort-asc', 'sort-desc'));
                th.classList.add(state.sortDir === 'ASC' ? 'sort-asc' : 'sort-desc');
                state.currentPage = 1;
                fetchData();
            });
        });

        // Store fetch for manual reload
        state.fetchData = fetchData;
        state.render = fetchData;

        // Initial load
        fetchData();
    }

    // ═══ Helpers ═══

    function escapeHtml(str) {
        const d = document.createElement('div');
        d.textContent = String(str);
        return d.innerHTML;
    }

    function buildPageNumbers(current, total) {
        if (total <= 7) return Array.from({length: total}, (_, i) => i + 1);
        const pages = [];
        pages.push(1);
        if (current > 3) pages.push('...');
        for (let i = Math.max(2, current - 1); i <= Math.min(total - 1, current + 1); i++) {
            pages.push(i);
        }
        if (current < total - 2) pages.push('...');
        pages.push(total);
        return pages;
    }

    // Initialize all tables on load globally
    function autoInit() {
        document.querySelectorAll('table.data-table').forEach(t => {
            if (t.dataset.serverUrl) {
                initServerTable(t);
            } else {
                initTable(t);
            }
        });
    }

    // Handle case where script loads after DOMContentLoaded already fired
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', autoInit);
    } else {
        autoInit();
    }

    return {
        /**
         * Reinyecta el HTML en el tbody y re-pinta la tabla sin recargar.
         * @param {string} tableId - El ID de la tabla HTML (ej: 'tbl-profesores')
         * @param {string} newTbodyHtml - El string HTML del nuevo contenido 
         */
        updateData: function(tableId, newTbodyHtml) {
            const state = tableInstances[tableId];
            if (!state || !state.tbody) return;
            state.tbody.innerHTML = newTbodyHtml;
            state.rows = Array.from(state.tbody.querySelectorAll('tr[data-search]'));
            state.render();
        },

        /**
         * Recarga la tabla: server-side usa fetchData(), client-side usa DOM morphing.
         */
        reloadTableData: async function(tableId) {
            const state = tableInstances[tableId];
            if (state && state.fetchData) {
                // Server-side mode: just re-fetch
                return state.fetchData();
            }

            // Client-side mode: original DOM morphing logic
            if (_reloadLocks[tableId]) return;
            _reloadLocks[tableId] = true;
            let focusedElementId = null;
            let hadFocusInTable = false;
            
            if (document.activeElement && document.activeElement.closest(`#${tableId}`)) {
                hadFocusInTable = true;
                focusedElementId = document.activeElement.id || document.activeElement.getAttribute('data-user-id') || null;
            }

            const btn = document.querySelector(`[data-reload-for="${tableId}"]`);
            
            let svg = null;
            if (btn) {
                svg = btn.querySelector('svg');
                if (svg) svg.style.animation = 'button-spin 1s linear infinite';
                btn.disabled = true;
            }

            if (!state || !state.tbody) {
                if (btn) { btn.disabled = false; if (svg) svg.style.animation = ''; }
                _reloadLocks[tableId] = false;
                return;
            }
            let backupInnerHtml = '';

            backupInnerHtml = state.tbody.innerHTML;
            
            state.tbody.style.opacity = '1';
            state.tbody.style.pointerEvents = 'none';
            
            const currentHeight = state.tbody.getBoundingClientRect().height;
            const minH = currentHeight > 150 ? currentHeight : 150;
            
            state.tbody.setAttribute('aria-busy', 'true');
            
            state.tbody.innerHTML = `
            <tr>
                <td colspan="100" style="text-align:center; vertical-align:middle; height: ${minH}px; padding: 0; background: var(--sim-white);">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="var(--sim-blue, #0056AC)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="animation: button-spin 1.5s linear infinite; transform-origin: center;">
                        <polyline points="23 4 23 10 17 10"></polyline>
                        <polyline points="1 20 1 14 7 14"></polyline>
                        <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
                    </svg>
                    <div style="margin-top:12px; font-weight:600; font-size: 15px; color:var(--sim-text-light, #6b7280);">Sincronizando tabla...</div>
                </td>
            </tr>`;

            try {
                const [res] = await Promise.all([
                    fetch(window.location.href),
                    new Promise(resolve => setTimeout(resolve, 600))
                ]);
                
                const html = await res.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                
                const newTbody = doc.querySelector(`#${tableId} tbody`);
                if (newTbody) {
                    const savedSearch = state.searchTerm;
                    const savedPage = state.currentPage;
                    
                    state.tbody.innerHTML = newTbody.innerHTML;
                    state.rows = Array.from(state.tbody.querySelectorAll('tr[data-search]'));
                    
                    state.searchTerm = savedSearch;
                    const totalAfter = Math.max(1, Math.ceil(state.rows.length / (parseInt(document.querySelector(`[data-perpage-for="${tableId}"]`)?.value || '10', 10))));
                    state.currentPage = Math.min(savedPage, totalAfter);
                    state.render();
                    
                    const visibleRows = Array.from(state.tbody.querySelectorAll('tr[data-search]')).filter(r => r.style.display !== 'none');
                    const maxDelay = visibleRows.length * 40 + 300;
                    visibleRows.forEach((row, i) => {
                        row.style.opacity = '0';
                        row.style.transform = 'translateY(8px)';
                        row.style.transition = `opacity 0.3s ease ${i * 40}ms, transform 0.3s ease ${i * 40}ms`;
                        requestAnimationFrame(() => {
                            requestAnimationFrame(() => {
                                row.style.opacity = '1';
                                row.style.transform = 'translateY(0)';
                            });
                        });
                    });
                    setTimeout(() => {
                        visibleRows.forEach(row => {
                            row.style.removeProperty('opacity');
                            row.style.removeProperty('transform');
                            row.style.removeProperty('transition');
                        });
                    }, maxDelay);
                    
                    if (window.showToast) window.showToast('Actualizado', 'success');
                    
                    if (hadFocusInTable && focusedElementId) {
                        const elemToFocus = document.querySelector(`#${tableId} [id="${focusedElementId}"], #${tableId} [data-user-id="${focusedElementId}"]`);
                        if (elemToFocus) elemToFocus.focus();
                    }
                }
            } catch (e) {
                console.error('[DOM Morphing] Error:', e);
                if (state && state.tbody && backupInnerHtml) {
                    state.tbody.innerHTML = backupInnerHtml;
                    state.rows = Array.from(state.tbody.querySelectorAll('tr[data-search]'));
                    state.render();
                }
                if (window.showToast) window.showToast('Error de conexión al cargar tabla', 'error');
            } finally {
                if (state && state.tbody) {
                    state.tbody.style.pointerEvents = 'auto';
                    state.tbody.removeAttribute('aria-busy');
                }
                
                if (btn) {
                    if (svg) svg.style.animation = '';
                    btn.disabled = false;
                }
                _reloadLocks[tableId] = false;
            }
        },

        /**
         * Set a custom filter for a server-side table and reload.
         * @param {string} tableId
         * @param {string} key - Filter key (sent as query param)
         * @param {string} value - Filter value (empty string removes the filter)
         */
        setFilter: function(tableId, key, value) {
            const state = tableInstances[tableId];
            if (!state) return;
            if (value === '' || value == null) {
                delete state.filters[key];
            } else {
                state.filters[key] = value;
            }
            state.currentPage = 1;
            if (state.fetchData) state.fetchData();
        }
    };
})();

