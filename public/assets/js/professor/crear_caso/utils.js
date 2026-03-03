/**
 * utils.js
 * Funcionalidades de ayuda compartidas.
 */

export const $ = (sel) => document.querySelector(sel);
export const $$ = (sel) => document.querySelectorAll(sel);
export const show = (el) => { if (el) el.style.display = ''; };
export const hide = (el) => { if (el) el.style.display = 'none'; };

export const formatBs = (v) => {
    const n = parseFloat(v) || 0;
    return 'Bs. ' + n.toLocaleString('es-VE', { minimumFractionDigits: 2 });
};
