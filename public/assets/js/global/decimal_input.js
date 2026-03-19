/**
 * decimal_input.js — Formateo automático de inputs decimales venezolanos
 *
 * Uso: agregar la clase CSS "decimal-input" a cualquier <input> de texto.
 *      Para permitir valores negativos, agregar también "decimal-signed".
 *
 * Comportamiento:
 *  - Al escribir un punto (.), se convierte automáticamente en coma (,)
 *  - Solo permite dígitos, una coma, y máximo 2 decimales
 *  - Al perder el foco (blur), formatea con separadores de miles: 1234567,89 → 1.234.567,89
 *  - Al obtener el foco (focus), quita los separadores de miles para facilitar la edición
 *  - [decimal-signed] Tecla "-" pone signo negativo al inicio; "+" lo quita
 */
(function () {
    'use strict';

    /**
     * Formatea un string numérico limpio (sin puntos de miles) al formato venezolano.
     * Entrada: "1234567,89" → Salida: "1.234.567,89"
     * Entrada: "-1234567,89" → Salida: "-1.234.567,89"
     */
    function formatVenezolano(raw) {
        if (!raw) return '0,00';

        // Preservar signo negativo
        var negative = false;
        if (raw.charAt(0) === '-') {
            negative = true;
            raw = raw.substring(1);
        }

        var parts = raw.split(',');
        var intPart = parts[0] || '0';
        var decPart = parts[1] || '';

        // Quitar ceros iniciales innecesarios (pero dejar al menos un "0")
        intPart = intPart.replace(/^0+(?=\d)/, '');
        if (!intPart) intPart = '0';

        // Agregar separadores de miles
        intPart = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

        // Asegurar 2 decimales
        decPart = (decPart + '00').substring(0, 2);

        // No poner negativo si el valor es 0,00
        if (negative && intPart === '0' && decPart === '00') negative = false;

        return (negative ? '-' : '') + intPart + ',' + decPart;
    }

    /**
     * Limpia un valor a solo dígitos y una coma, máximo 2 decimales.
     * Convierte puntos a comas. Quita caracteres no válidos.
     * Si allowSign es true, preserva un "-" al inicio.
     */
    function sanitize(value, allowSign) {
        // Detectar y preservar signo negativo al inicio
        var negative = false;
        if (allowSign && value.charAt(0) === '-') {
            negative = true;
            value = value.substring(1);
        }

        // Reemplazar punto por coma
        value = value.replace(/\./g, ',');

        // Quitar todo lo que no sea dígito o coma
        value = value.replace(/[^\d,]/g, '');

        // Solo permitir una coma
        var firstComma = value.indexOf(',');
        if (firstComma !== -1) {
            value = value.substring(0, firstComma + 1)
                  + value.substring(firstComma + 1).replace(/,/g, '');
        }

        // Máximo 2 decimales
        if (firstComma !== -1 && value.length > firstComma + 3) {
            value = value.substring(0, firstComma + 3);
        }

        return (negative ? '-' : '') + value;
    }

    // Interceptar teclas especiales
    document.addEventListener('keydown', function (e) {
        if (!e.target.classList.contains('decimal-input')) return;
        var el = e.target;

        // ── Manejo de +/- para campos con decimal-signed ──
        if (el.classList.contains('decimal-signed')) {
            if (e.key === '-') {
                e.preventDefault();
                // No poner negativo si el valor es 0 o vacío
                var numVal = el.value.replace(/[^\d]/g, '');
                if (!numVal || parseInt(numVal, 10) === 0) return;
                // Si no tiene signo negativo, agregarlo al inicio
                if (el.value.charAt(0) !== '-') {
                    el.value = '-' + el.value;
                }
                return;
            }
            if (e.key === '+') {
                e.preventDefault();
                // Quitar signo negativo si existe
                if (el.value.charAt(0) === '-') {
                    el.value = el.value.substring(1);
                }
                return;
            }
        }

        // ── Interceptar punto/coma ──
        if (e.key !== '.' && e.key !== ',') return;

        var commaPos = el.value.indexOf(',');

        if (commaPos !== -1) {
            // Ya hay coma → no insertar otra, solo mover cursor justo después de ella
            e.preventDefault();
            var newPos = commaPos + 1;
            el.setSelectionRange(newPos, newPos);
        }
        // Si no hay coma, dejar que se inserte (el handler de input la convertirá si es punto)
    });

    // Delegación de eventos sobre el document para inputs actuales y futuros
    document.addEventListener('input', function (e) {
        if (!e.target.classList.contains('decimal-input')) return;

        var el = e.target;
        var pos = el.selectionStart;
        var before = el.value;
        var allowSign = el.classList.contains('decimal-signed');
        var clean = sanitize(before, allowSign);

        if (clean !== before) {
            var diff = before.length - clean.length;
            el.value = clean;
            // Ajustar posición del cursor
            var newPos = Math.max(0, pos - diff);
            el.setSelectionRange(newPos, newPos);
        }
    });

    document.addEventListener('blur', function (e) {
        if (!e.target.classList.contains('decimal-input')) return;

        var el = e.target;
        // Quitar puntos de miles existentes (por si hay), dejar solo dígitos, coma y signo
        var raw = el.value.replace(/\./g, '');
        el.value = formatVenezolano(raw);
    }, true); // useCapture para blur

    document.addEventListener('focus', function (e) {
        if (!e.target.classList.contains('decimal-input')) return;

        var el = e.target;
        // Quitar separadores de miles para facilitar edición
        el.value = el.value.replace(/\./g, '');
    }, true); // useCapture para focus
})();
