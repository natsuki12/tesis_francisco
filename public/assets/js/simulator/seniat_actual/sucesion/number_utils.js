/**
 * number_utils.js — Global number formatting/parsing for sucesión pages.
 *
 * Usage:
 *   parseDecimal('1.234,56')   → 1234.56
 *   fmtBs(1234.56)            → '1.234,56'
 *
 * Anti-crash: all functions wrapped in try/catch with safe fallbacks.
 */
(function () {
    'use strict';

    /**
     * Parse a Venezuelan-format number string to a float.
     * E.g. '1.234,56' → 1234.56
     *      '0,00'     → 0
     *      null       → 0
     *
     * @param {string|number} str - The value to parse
     * @returns {number}
     */
    function parseDecimal(str) {
        try {
            if (str == null) return 0;
            str = String(str).trim();
            if (str === '') return 0;
            str = str.replace(/\./g, '').replace(',', '.');
            var result = parseFloat(str);
            return isNaN(result) ? 0 : result;
        } catch (err) {
            console.error('[number_utils] parseDecimal error:', err);
            return 0;
        }
    }

    /**
     * Format a float into Venezuelan Bolívar format.
     * E.g. 1234.56 → '1.234,56'
     *      0       → '0,00'
     *
     * @param {number} v - The value to format
     * @returns {string}
     */
    function fmtBs(v) {
        try {
            if (v == null || isNaN(v)) v = 0;
            var parts = v.toFixed(2).split('.');
            var intPart = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            return intPart + ',' + parts[1];
        } catch (err) {
            console.error('[number_utils] fmtBs error:', err);
            return '0,00';
        }
    }

    // Expose globally
    window.parseDecimal = parseDecimal;
    window.fmtBs = fmtBs;
})();
