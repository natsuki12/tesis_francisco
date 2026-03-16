/**
 * datos_tribunal.js — Reusable toggle logic for "Datos del Tribunal"
 *
 * Expects:
 *   - A <select id="bl"> for "Bien Litigioso" (value = 'true' | 'false')
 *   - The partial _datos_tribunal.php included in the page
 *
 * Provides globally:
 *   - initTribunalToggle()  → call once on DOMContentLoaded
 *   - getTribunalData()     → returns object with tribunal field values
 *   - setTribunalData(item) → fills tribunal fields from an item object
 *   - resetTribunal()       → clears tribunal fields and hides section
 */

(function () {
    'use strict';

    var tribunalDiv, blSelect;

    function toggleTribunal() {
        try {
            var show = blSelect.value === 'true';
            tribunalDiv.style.display = show ? '' : 'none';
            if (!show) {
                resetTribunal();
            }
        } catch (err) {
            console.error('[DatosTribunal::toggle]', err);
        }
    }

    window.initTribunalToggle = function () {
        try {
            blSelect = document.getElementById('bl');
            tribunalDiv = document.getElementById('datosTribunal');
            if (!blSelect || !tribunalDiv) return;
            blSelect.addEventListener('change', toggleTribunal);
            // Show if already "Si"
            if (blSelect.value === 'true') {
                tribunalDiv.style.display = '';
            }
        } catch (err) {
            console.error('[DatosTribunal::init]', err);
        }
    };

    window.getTribunalData = function () {
        try {
            return {
                num_expediente: (document.getElementById('litigioNroExpediente') || {}).value || '',
                tribunal_causa: (document.getElementById('litigioTribunalCausa') || {}).value || '',
                partes_juicio:  (document.getElementById('litigioPartesJuicio') || {}).value || '',
                estado_juicio:  (document.getElementById('litigioEstadoJuicio') || {}).value || '',
            };
        } catch (err) {
            console.error('[DatosTribunal::getData]', err);
            return {};
        }
    };

    window.setTribunalData = function (item) {
        try {
            var nroExp = document.getElementById('litigioNroExpediente');
            var tribCausa = document.getElementById('litigioTribunalCausa');
            var partesJ = document.getElementById('litigioPartesJuicio');
            var estadoJ = document.getElementById('litigioEstadoJuicio');
            if (nroExp)   nroExp.value   = item.num_expediente || '';
            if (tribCausa) tribCausa.value = item.tribunal_causa || '';
            if (partesJ)  partesJ.value  = item.partes_juicio || '';
            if (estadoJ)  estadoJ.value  = item.estado_juicio || '';
            // Show/hide based on bien_litigioso value
            if (tribunalDiv) {
                tribunalDiv.style.display = (item.bien_litigioso === 'true') ? '' : 'none';
            }
        } catch (err) {
            console.error('[DatosTribunal::setData]', err);
        }
    };

    window.resetTribunal = function () {
        try {
            var nroExp = document.getElementById('litigioNroExpediente');
            var tribCausa = document.getElementById('litigioTribunalCausa');
            var partesJ = document.getElementById('litigioPartesJuicio');
            var estadoJ = document.getElementById('litigioEstadoJuicio');
            if (nroExp)   nroExp.value   = '';
            if (tribCausa) tribCausa.value = '';
            if (partesJ)  partesJ.value  = '';
            if (estadoJ)  estadoJ.value  = '';
            if (tribunalDiv) tribunalDiv.style.display = 'none';
        } catch (err) {
            console.error('[DatosTribunal::reset]', err);
        }
    };
})();
