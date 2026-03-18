/**
 * rif_lookup.js — Global RIF lookup with debounce for sucesión pages.
 *
 * Usage (from inline script):
 *   initRifLookup({
 *       rifInputId:   'rifEmpresa',   // id of <input> for RIF
 *       razonInputId: 'razonSocial',  // id of <input> for Razón Social
 *       baseUrl:      BASE,           // base URL for the API
 *       onResult:     validateForm    // callback after lookup (optional)
 *   });
 *
 * Anti-crash: every step is wrapped in try/catch.
 */
(function () {
    'use strict';

    /**
     * Initialise a RIF lookup on a pair of inputs.
     *
     * @param {Object} opts
     * @param {string} opts.rifInputId   - DOM id of the RIF input
     * @param {string} opts.razonInputId - DOM id of the Razón Social input
     * @param {string} opts.baseUrl      - API base URL (no trailing slash)
     * @param {Function} [opts.onResult] - callback invoked after every lookup attempt
     */
    function initRifLookup(opts) {
        try {
            if (!opts) {
                console.error('[RIF lookup] initRifLookup called without options');
                return;
            }

            var rifInput   = document.getElementById(opts.rifInputId   || 'rifEmpresa');
            var razonInput = document.getElementById(opts.razonInputId || 'razonSocial');

            if (!rifInput || !razonInput) {
                console.error('[RIF lookup] Input elements not found:',
                    opts.rifInputId, opts.razonInputId);
                return;
            }

            var baseUrl    = (opts.baseUrl || '').replace(/\/+$/, '');
            var onResult   = typeof opts.onResult === 'function' ? opts.onResult : null;
            var debounceTimer = null;

            rifInput.addEventListener('input', function () {
                try {
                    clearTimeout(debounceTimer);
                    var rif = rifInput.value.trim().toUpperCase();
                    rifInput.value = rif;

                    // Clear razón social while typing
                    razonInput.value = '';

                    // Only search when RIF is complete (letter + 9 digits = 10 chars)
                    if (!/^[JGVEP]\d{9}$/i.test(rif)) return;

                    debounceTimer = setTimeout(function () {
                        try {
                            fetch(baseUrl + '/api/buscar-rif', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ rif: rif })
                            })
                                .then(function (r) { return r.json(); })
                                .then(function (data) {
                                    try {
                                        if (data.ok && data.found) {
                                            razonInput.value = data.razon_social || '';
                                        } else {
                                            razonInput.value = '';
                                        }
                                        if (onResult) onResult();
                                    } catch (err) {
                                        console.error('[RIF lookup response]', err);
                                        razonInput.value = '';
                                    }
                                })
                                .catch(function (err) {
                                    console.error('[RIF lookup fetch]', err);
                                    razonInput.value = '';
                                });
                        } catch (err) {
                            console.error('[RIF lookup debounce]', err);
                        }
                    }, 300);
                } catch (err) {
                    console.error('[RIF lookup input]', err);
                }
            });
        } catch (err) {
            console.error('[RIF lookup init]', err);
        }
    }

    // Expose globally
    window.initRifLookup = initRifLookup;
})();
