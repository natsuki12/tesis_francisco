import { showToast } from '../../global/utils.js';

/**
 * Carga de forma asíncrona y defensiva la librería Driver.js solo cuando el 
 * usuario hace clic en el botón de Ayuda/Tour, ahorrando peso en el inicio.
 */

let isLoading = false;
const CSS_URL = window.BASE_URL + '/assets/css/lib/driver.css';
const JS_URL = window.BASE_URL + '/assets/js/lib/driver.js.iife.js';

export async function launchTour() {
    if (isLoading) return;

    if (!window.driver || !window.driver.js || typeof window.driver.js.driver !== 'function') {
        isLoading = true;
        // Mostrar estado de carga en el botón si es necesario (opcional)
        const btn = document.getElementById('btnTourTutorial');
        const originalHTML = btn ? btn.innerHTML : '';
        if (btn) btn.innerHTML = '<span style="font-size:12px;">...</span>';

        try {

            // 1. Cargar CSS
            if (!document.querySelector(`link[href="${CSS_URL}"]`)) {
                const link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = CSS_URL;
                document.head.appendChild(link);
            }

            // 2. Cargar Script
            await new Promise((resolve, reject) => {
                const script = document.createElement('script');
                script.src = JS_URL;
                script.onload = resolve;
                script.onerror = reject;
                document.body.appendChild(script);
            });

            if (btn) btn.innerHTML = originalHTML;
        } catch (err) {
            console.error("Error cargando Driver.js:", err);
            if (btn) btn.innerHTML = originalHTML; // restaurar UI
            showToast('No se pudo cargar el tutorial comunicándose con el servidor.', 'error');
            isLoading = false;
            return;
        }
        isLoading = false;
    }

    // 3. Importar dinámicamente el configurador del tour y ejecutarlo
    try {
        const { startTour } = await import('./tour.js');
        startTour();
    } catch (err) {
        console.error("Error inicializando Tour:", err);
    }
}
