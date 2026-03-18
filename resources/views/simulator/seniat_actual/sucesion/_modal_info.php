<!-- ═══ Modal Información — Componente global para Sucesiones ═══ -->
<!-- Replica el modal del SENIAT: header "Información" + alerta con icono -->
<div class="modal fade" id="modalInfoSucesiones" tabindex="-1" role="dialog" aria-labelledby="modalInfoTitle" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalInfoTitle">Información</h5>
                <button type="button" class="btn btn-light btn-sm" id="modalInfoClose" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12">
                        <div role="alert" class="alert" id="modalInfoAlert">
                            <i class="bi" id="modalInfoIcon"></i>
                            <span id="modalInfoMessage"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" id="modalInfoFooter" style="display:none">
                <button type="button" class="btn btn-sm" id="modalInfoCloseFooter"
                        style="background-color:#1a2a6c;color:#fff;border-color:#1a2a6c;"
                        onclick="if(typeof hideModalInfo==='function')hideModalInfo()">
                    <i class="bi bi-x-circle-fill"></i> Cerrar
                </button>&nbsp;
            </div>
        </div>
    </div>
</div>
<!-- Backdrop overlay -->
<div class="modal-backdrop fade" id="modalInfoBackdrop" style="display:none"></div>
