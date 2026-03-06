/**
 * public/assets/js/professor/gestionar_caso/gestionar_caso.js
 * Script para la vista de gestión de un caso sucesoral específico.
 */

document.addEventListener('DOMContentLoaded', () => {
    // Inicializar Tabs
    const tabs = document.querySelectorAll('.gc-tab');
    const panels = document.querySelectorAll('.gc-panel');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            // Remover 'is-active' de todos
            tabs.forEach(t => t.classList.remove('is-active'));
            panels.forEach(p => p.classList.remove('is-active'));

            // Agregar al seleccionado
            tab.classList.add('is-active');

            // Mostrar panel correspondiente
            const targetId = `tab-${tab.dataset.tab}`;
            const targetPanel = document.getElementById(targetId);
            if (targetPanel) {
                targetPanel.classList.add('is-active');
            }
        });
    });
});
