/**
 * AutocompleteDropdown — reusable dropdown component
 *
 * Usage:
 *   new AutocompleteDropdown({
 *     input:      HTMLInputElement,
 *     fetchFn:    async (query) => [{ persona_id, tipo_cedula, cedula, nombres, apellidos, ... }],
 *     renderItem: (item) => HTMLString,
 *     onSelect:   (item) => void,
 *     debounceMs: 300
 *   });
 */
class AutocompleteDropdown {
    /**
     * @param {Object} opts
     * @param {HTMLInputElement} opts.input       The text input to attach to
     * @param {Function}         opts.fetchFn     async (query:string) => Array<Object>
     * @param {Function}         [opts.renderItem] (item:Object) => HTMLString
     * @param {Function}         opts.onSelect    (item:Object) => void
     * @param {number}           [opts.debounceMs=300]
     */
    constructor(opts) {
        const { input, fetchFn, renderItem, onSelect, debounceMs = 300 } = opts;
        this.input = input;
        this.fetchFn = fetchFn;
        this.renderItem = renderItem || AutocompleteDropdown.defaultRenderItem;
        this.onSelect = onSelect;
        this.debounceMs = debounceMs;
        this.minLength = opts.minLength || 0;

        this._timer = null;
        this._items = [];
        this._highlightIdx = -1;
        this._isOpen = false;
        this._cache = new Map(); // simple query→results cache
        this._abortController = null;

        this._buildDOM();
        this._bindEvents();
    }

    /* ── DOM setup ── */

    _buildDOM() {
        // Wrap the input if not already wrapped
        const parent = this.input.parentElement;
        if (!parent.classList.contains('ac-wrapper')) {
            const wrapper = document.createElement('div');
            wrapper.className = 'ac-wrapper';
            parent.insertBefore(wrapper, this.input);
            wrapper.appendChild(this.input);
        }

        // Create dropdown on body (fixed positioning to escape overflow:hidden)
        this.dropdown = document.createElement('div');
        this.dropdown.className = 'ac-dropdown';
        this.dropdown.setAttribute('role', 'listbox');
        document.body.appendChild(this.dropdown);
    }

    _updatePosition() {
        const rect = this.input.getBoundingClientRect();
        this.dropdown.style.top = (rect.bottom + 4) + 'px';
        this.dropdown.style.left = rect.left + 'px';
        this.dropdown.style.width = rect.width + 'px';
    }

    /* ── Event binding ── */

    _bindEvents() {
        // Focus → open with all results
        this.input.addEventListener('focus', () => {
            this._search(this.input.value.trim());
        });

        // Input → filter with debounce
        this.input.addEventListener('input', () => {
            clearTimeout(this._timer);
            this._timer = setTimeout(() => {
                this._search(this.input.value.trim());
            }, this.debounceMs);
        });

        // Keyboard navigation
        this.input.addEventListener('keydown', (e) => {
            if (!this._isOpen) return;

            switch (e.key) {
                case 'ArrowDown':
                    e.preventDefault();
                    this._moveHighlight(1);
                    break;
                case 'ArrowUp':
                    e.preventDefault();
                    this._moveHighlight(-1);
                    break;
                case 'Enter':
                    e.preventDefault();
                    if (this._highlightIdx >= 0 && this._items[this._highlightIdx]) {
                        this._selectItem(this._items[this._highlightIdx]);
                    }
                    break;
                case 'Escape':
                    e.preventDefault();
                    this.close();
                    break;
            }
        });

        // Click outside → close
        document.addEventListener('mousedown', (e) => {
            if (!this.input.contains(e.target) && !this.dropdown.contains(e.target)) {
                this.close();
            }
        });

        // Reposition on scroll/resize
        const reposition = () => { if (this._isOpen) this._updatePosition(); };
        window.addEventListener('scroll', reposition, true);
        window.addEventListener('resize', reposition);
    }

    /* ── Search & render ── */

    async _search(query) {
        // Respect minLength
        if (query.length < this.minLength) {
            this.close();
            return;
        }

        // Show loading
        this.dropdown.innerHTML = `
            <div class="ac-dropdown__loading">
                <div class="ac-dropdown__spinner"></div>
                Buscando…
            </div>`;
        this._open();

        // Check cache
        if (this._cache.has(query)) {
            this._renderResults(this._cache.get(query));
            return;
        }

        try {
            // Cancel previous fetch
            if (this._abortController) this._abortController.abort();
            this._abortController = new AbortController();

            const results = await this.fetchFn(query, this._abortController.signal);

            // null/false = suppress dropdown (e.g. field is in manual-entry mode)
            if (results === null || results === false) {
                this.close();
                return;
            }

            this._cache.set(query, results);
            this._renderResults(results);
        } catch (err) {
            if (err.name === 'AbortError') return; // Silently ignore aborted fetches
            console.error('[AutocompleteDropdown] fetch error:', err);
            this.dropdown.innerHTML = `<div class="ac-dropdown__empty">Error al buscar</div>`;
        }
    }

    _renderResults(results) {
        this._items = results;
        this._highlightIdx = -1;

        if (!results || results.length === 0) {
            this.dropdown.innerHTML = `<div class="ac-dropdown__empty">Sin resultados</div>`;
            this._open();
            return;
        }

        this.dropdown.innerHTML = results.map((item, idx) =>
            `<div class="ac-dropdown__item" data-idx="${idx}" role="option">
                ${this.renderItem(item)}
            </div>`
        ).join('');

        // Click on items
        this.dropdown.querySelectorAll('.ac-dropdown__item').forEach(el => {
            el.addEventListener('mousedown', (e) => {
                e.preventDefault(); // Prevent input blur
                const idx = parseInt(el.dataset.idx, 10);
                if (this._items[idx]) {
                    this._selectItem(this._items[idx]);
                }
            });

            // Hover highlight
            el.addEventListener('mouseenter', () => {
                this._setHighlight(parseInt(el.dataset.idx, 10));
            });
        });

        this._open();
    }

    /* ── Selection ── */

    _selectItem(item) {
        this.close();
        this.onSelect(item);
    }

    /* ── Highlight / navigation ── */

    _moveHighlight(delta) {
        if (!this._items.length) return;
        let newIdx = this._highlightIdx + delta;
        if (newIdx < 0) newIdx = this._items.length - 1;
        if (newIdx >= this._items.length) newIdx = 0;
        this._setHighlight(newIdx);
    }

    _setHighlight(idx) {
        // Remove previous
        const prev = this.dropdown.querySelector('.is-highlighted');
        if (prev) prev.classList.remove('is-highlighted');

        this._highlightIdx = idx;
        const items = this.dropdown.querySelectorAll('.ac-dropdown__item');
        if (items[idx]) {
            items[idx].classList.add('is-highlighted');
            items[idx].scrollIntoView({ block: 'nearest' });
        }
    }

    /* ── Open / close ── */

    _open() {
        this._isOpen = true;
        this._updatePosition();
        this.dropdown.classList.add('is-open');
    }

    close() {
        this._isOpen = false;
        this.dropdown.classList.remove('is-open');
        this._highlightIdx = -1;
        clearTimeout(this._timer);
    }

    /* ── Default renderer ── */

    static defaultRenderItem(item) {
        const tipo = item.tipo_cedula || '';
        const badgeText = (!tipo || tipo === 'No_Aplica') ? 'S/C' : tipo;
        const badgeClass = (!tipo || tipo === 'No_Aplica') ? 'ac-dropdown__badge ac-dropdown__badge--muted' : 'ac-dropdown__badge';
        const identifier = item.cedula || (item.rif_personal ? `RIF: ${item.rif_personal}` : '—');
        const nombres = item.nombres || '';
        const apellidos = item.apellidos || '';
        return `
            <span class="${badgeClass}">${badgeText}</span>
            <span class="ac-dropdown__cedula">${identifier}</span>
            <span class="ac-dropdown__sep">—</span>
            <span class="ac-dropdown__name">${nombres} ${apellidos}</span>
        `;
    }
}

// Export for both module and non-module usage
if (typeof window !== 'undefined') {
    window.AutocompleteDropdown = AutocompleteDropdown;
}
