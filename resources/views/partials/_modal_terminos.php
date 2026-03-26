<?php
/**
 * PARTIAL: Modal de Términos y Condiciones
 * -----------------------------------------
 * Modal reutilizable que muestra los términos de uso del simulador educativo.
 * 
 * Uso: <?php include __DIR__ . '/../partials/_modal_terminos.php'; ?>
 * 
 * JS API:
 *   - Abrir:  document.getElementById('modalTerminos').classList.add('is-open')
 *   - Cerrar: se cierra automáticamente con los botones Declinar/Aceptar
 *   - Evento: dispara 'terminos:aceptados' en document al hacer click en Aceptar
 */
?>

<div id="modalTerminos" class="modal-terminos-overlay">
    <div class="modal-terminos">
        <h2 class="modal-terminos__title">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
                <polyline points="10 9 9 9 8 9"></polyline>
            </svg>
            Términos y condiciones
        </h2>

        <div class="modal-terminos__content">
            <p><strong>SIMULADOR EDUCATIVO – TÉRMINOS DE USO</strong></p>
            <p>Este sistema es una herramienta de simulación desarrollada exclusivamente con <strong>fines educativos y
                    didácticos</strong>.</p>
            <p>1. <strong>NO ES UN SITIO OFICIAL:</strong> Este software no representa al portal oficial del SENIAT ni a
                ninguna entidad gubernamental.</p>
            <p>2. <strong>DATOS FICTICIOS:</strong> La información ingresada en este simulador es tratada localmente
                para efectos de práctica académica.</p>
            <p>Al continuar, el usuario reconoce y acepta que está interactuando con un entorno de pruebas.</p>
        </div>

        <div class="modal-terminos__footer">
            <button type="button" id="btnTerminosAceptar" class="modal-terminos__btn modal-terminos__btn--primary">Aceptar</button>
        </div>
    </div>
</div>

<style>
.modal-terminos-overlay {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 9999;
    background: rgba(15, 23, 42, 0.5);
    backdrop-filter: blur(4px);
    align-items: center;
    justify-content: center;
    padding: 1rem;
}
.modal-terminos-overlay.is-open {
    display: flex;
}
.modal-terminos {
    background: #fff;
    border-radius: 16px;
    width: 100%;
    max-width: 520px;
    box-shadow: 0 25px 50px -12px rgba(0,0,0,.25);
    animation: modalTerminosIn .25s ease-out;
}
@keyframes modalTerminosIn {
    from { opacity: 0; transform: scale(.95) translateY(10px); }
    to   { opacity: 1; transform: scale(1) translateY(0); }
}
.modal-terminos__title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 1.15rem;
    font-weight: 700;
    color: #0f172a;
    padding: 24px 28px 16px;
    margin: 0;
}
.modal-terminos__content {
    margin: 0 28px;
    padding: 20px 24px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 0.9rem;
    line-height: 1.6;
    color: #334155;
}
.modal-terminos__content p { margin: 0 0 12px; }
.modal-terminos__content p:last-child { margin-bottom: 0; }
.modal-terminos__footer {
    display: flex;
    justify-content: center;
    gap: 12px;
    padding: 24px 28px;
}
.modal-terminos__btn {
    padding: 10px 28px;
    border-radius: 8px;
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
    border: none;
    transition: all .15s ease;
}
.modal-terminos__btn--secondary {
    background: #fff;
    color: #475569;
    border: 1px solid #cbd5e1;
}
.modal-terminos__btn--secondary:hover { background: #f1f5f9; }
.modal-terminos__btn--primary {
    background: #1e3a5f;
    color: #fff;
}
.modal-terminos__btn--primary:hover { background: #152d4a; }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const overlay = document.getElementById('modalTerminos');
    if (!overlay) return;

    document.getElementById('btnTerminosAceptar')?.addEventListener('click', () => {
        overlay.classList.remove('is-open');
        document.dispatchEvent(new CustomEvent('terminos:aceptados'));
    });
});
</script>
