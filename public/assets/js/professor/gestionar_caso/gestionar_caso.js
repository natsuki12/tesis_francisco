/**
 * public/assets/js/professor/gestionar_caso/gestionar_caso.js
 * Script para la vista de gestión de un caso sucesoral específico.
 * Persiste la pestaña activa via URL hash (#asignaciones, #patrimonio, etc.)
 */

document.addEventListener('DOMContentLoaded', () => {
    const tabs = document.querySelectorAll('.gc-tab');
    const panels = document.querySelectorAll('.gc-panel');

    function activateTab(tabId) {
        tabs.forEach(t => t.classList.remove('is-active'));
        panels.forEach(p => p.classList.remove('is-active'));

        const tab = [...tabs].find(t => t.dataset.tab === tabId);
        const panel = document.getElementById(`tab-${tabId}`);

        if (tab && panel) {
            tab.classList.add('is-active');
            panel.classList.add('is-active');
        }
    }

    // Click en tab → activar + actualizar hash
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const tabId = tab.dataset.tab;
            activateTab(tabId);
            history.replaceState(null, '', `#${tabId}`);
        });
    });

    // Al cargar, restaurar pestaña desde hash
    const hash = location.hash.replace('#', '');
    if (hash && [...tabs].some(t => t.dataset.tab === hash)) {
        activateTab(hash);
    }
});
