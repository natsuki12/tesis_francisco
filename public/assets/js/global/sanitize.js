/**
 * sanitize.js
 * Global input sanitization — removes dangerous characters from all text inputs.
 * Loaded in logged_layout.php so it applies across every page in the app.
 *
 * Characters removed:  ' " ` ; \ < > { } | ~ ^ & # $ %
 *
 * To skip sanitization on a specific input, add the attribute:
 *   data-no-sanitize
 */

document.addEventListener('input', (e) => {
    const el = e.target;

    // Only sanitize text inputs and textareas
    if (!el.matches('input[type="text"], input:not([type]), textarea')) return;

    // Skip fields explicitly marked as no-sanitize
    if (el.hasAttribute('data-no-sanitize')) return;

    // Remove dangerous characters for SQL/HTML injection
    const original = el.value;
    const sanitized = original.replace(/['"`;\\\<\>{}|~^&#$%]/g, '');
    if (sanitized !== original) {
        el.value = sanitized;
    }
});
