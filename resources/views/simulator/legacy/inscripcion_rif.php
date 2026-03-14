<?php
declare(strict_types=1);

$pageTitle = 'Inscripción de RIF — Simulador';
$activePage = 'simulador';

ob_start();
?>

<style>
    /* --- Contenedor externo (hereda estilos del layout) --- */
    .seniat-wrapper {
        background: var(--sim-white, #ffffff);
        border-radius: 12px;
        box-shadow: var(--sim-shadow-lg, 0 4px 6px rgba(0, 0, 0, 0.07));
        overflow: hidden;
        border: 1px solid var(--sim-border, #dfe5ee);
    }

    /* --- Barrera de aislamiento --- */
    .seniat-scope {
        all: revert;
        display: block;
        background-color: #ffffff;
        margin: 0;
        padding: 0;
        color: #000000;
        font-family: Arial, Helvetica, sans-serif;
        font-size: 8pt;
        line-height: normal;
        zoom: 1.1;
    }

    .seniat-scope *,
    .seniat-scope *::before,
    .seniat-scope *::after {
        box-sizing: content-box;
    }

    .seniat-scope table {
        table-layout: fixed;
    }

    /* Override base.css globals that leak into the scope */
    .seniat-scope h1,
    .seniat-scope h2,
    .seniat-scope h3 {
        font-family: inherit;
        font-weight: inherit;
        color: inherit;
    }

    .seniat-scope label {
        font-family: inherit;
        font-size: inherit;
        font-weight: inherit;
        color: inherit;
    }

    .seniat-scope input[type="text"],
    .seniat-scope input[type="number"],
    .seniat-scope input[type="email"],
    .seniat-scope input[type="date"],
    .seniat-scope select,
    .seniat-scope textarea {
        font-family: Verdana, Arial;
        font-size: 8pt;
        color: #000;
        box-sizing: border-box;
        max-width: 100%;
    }

    .seniat-scope ::placeholder {
        font-family: Verdana, Arial;
        font-size: 8pt;
        color: #888;
    }

    .seniat-scope a,
    .seniat-scope a:link,
    .seniat-scope a:visited,
    .seniat-scope a:hover {
        font-family: inherit;
        font-size: inherit;
        color: inherit;
    }
</style>

<link href="<?= asset('css/simulator/legacy/inscripcion_rif.css') ?>" rel="stylesheet" />
<script charset="utf-8" src="<?= asset('js/simulator/legacy/inscripcion_rif.js') ?>" type="text/javascript"></script>
<script charset="utf-8" src="<?= asset('js/simulator/legacy/inscripcion_rif_api.js') ?>"
    type="text/javascript"></script>

<div class="seniat-wrapper">
    <div class="seniat-scope">

        <div class="Bodyid1siteid0" style="margin:0; padding:0;">
            <table bgcolor="#FFFFFF" border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td align="left" rowspan="2" width="265"><img border="0" height="73"
                            src="<?= asset('img/simulator/inscripcion_rif/79a6e64baf42.gif') ?>" width="207" /></td>
                    <td align="left" bgcolor="#FFFFFF" colspan="2" valign="middle" width="322"><span
                            class="letrasFecha">Venezuela,
                            <script
                                language="javascript">dows = new Array("domingo", "lunes", "martes", "mi&eacute;rcoles", "jueves", "viernes", "sabado"); months = new Array("enero", "febrero", "marzo", "abril", "mayo", "junio", "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"); now = new Date(); dow = now.getDay(); d = now.getDate(); m = now.getMonth(); h = now.getTime(); y = now.getFullYear(); document.write("<font size=1 face=Arial Narrow>" + dows[dow] + " " + d + " de " + months[m] + " de " + y + "</font>");</script>
                        </span></td>
                </tr>
                <tr height="68">
                    <td height="68"
                        style="background-IMAGE: url('<?= asset('img/simulator/inscripcion_rif/4c907c96f891.jpg') ?>');">
                    </td>
                    <td align="right" class="fondoPrincipal" height="68" valign="baseline" width="640"><img
                            alt="Aqui estan tus Tributos" border="0" height="68"
                            src="<?= asset('img/simulator/inscripcion_rif/5e3bb17364a1.jpg') ?>" width="640" /></td>
                </tr>
            </table>
            <table align="center" border="0" cellpadding="0" cellspacing="5" class="barraPpal" id="tblBarra"
                width="100%">
                <tr>
                </tr>
            </table>
            <script>
                var reincorporar = '';
                function EsCedula(cedula) {
                    if (obtenerObjeto('personalidad', 'value') == "1" || obtenerObjeto('personalidad', 'value') == "2" || obtenerObjeto('personalidad', 'value') == "6") {
                        ValidarCedula(cedula);
                    }
                }
            </script>
            <style>
                .visibles {
                    visibility: visible
                }

                .novisibles {
                    visibility: hidden
                }

                .inactivo {
                    background-color: #dcdcdc;
                }

                .activo {
                    background-color: White;
                }
            </style>
            <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <!--------------------->
                    <td align="left" valign="top" width="80%">
                        <table class="tablaTitulo" valign="top" width="100%">
                            <tr>
                                <td align="center" valign="top" width="100%">Registro Único de Información Fiscal -
                                    Inscripción
                                </td>
                            </tr>
                        </table>
                        <br />
                        <center><span style="color:#FF0000; font-size:11px; font-family: Verdana, Arial"><b></b></span>
                        </center>
                        <br />
                        <form action="#" method="post" name="BuscarInscritoRifForm"
                            onsubmit="event.preventDefault(); mostrarResultadoBusqueda(reincorporar);">
                            <input id="noresidenciado" name="noresidenciado" type="hidden" value="" />
                            <input id="consejocomunal" name="consejocomunal" type="hidden" value="" />
                            <input id="comuna" name="comuna" type="hidden" value="" />
                            <input id="nodomiciliadocondireccion" name="nodomiciliadocondireccion" type="hidden"
                                value="" />
                            <input id="consejoeducativo" name="consejoeducativo" type="hidden" value="" />
                            <!-- Mensaje RIF ya existe (solo para cuando el contribuyente ya tiene RIF) -->
                            <div id="rifExistsMessage"
                                style="display: none; text-align: center; margin-bottom: 12px; font-weight: bold; color: #E32227; font-family: Verdana, Arial; font-size: 11px;">
                            </div>
                            <table align="right" border="0" cellpadding="5" cellspacing="2" class="letras" width="80%">
                                <tr>
                                    <td class="fondoGrisClaro" width="20%">Tipo de Persona </td>
                                    <td class="fondoGrisOscuro">
                                        <select class="letras" id="personalidad" name="personalidad"
                                            onchange="ActivarBusqPreinscritos();">
                                            <option value="">SELECCIONAR</option>
                                            <option value="1">PERSONA NATURAL VENEZOLANA</option>
                                            <option value="2">PERSONA NATURAL EXTRANJERA CON CEDULA</option>
                                            <option value="4">PERSONA NATURAL EXTRANJERA CON PASAPORTE</option>
                                            <option value="1">PERSONA NATURAL NO RESIDENTE CON BASE FIJA CON CÉDULA
                                                VENEZOLANA</option>
                                            <option value="2">PERSONA NATURAL NO RESIDENTE CON BASE FIJA CON CÉDULA
                                                EXTRANJERA</option>
                                            <option value="4">PERSONA NATURAL NO RESIDENTE CON BASE FIJA CON PASAPORTE
                                            </option>
                                            <option value="3">PERSONA JURIDÍCA</option>
                                            <option value="3">PERSONA JURIDICA NO DOMICILIADA CON ESTABLECIMIENTO
                                                PERMANENTE</option>
                                            <option value="5">ORGANISMO GUBERNAMENTAL</option>
                                            <option value="6">SUCESION CON CÉDULA</option>
                                            <option value="6">SUCESION SIN CÉDULA</option>
                                            <option value="3">CONSEJO COMUNAL</option>
                                            <option value="3">COMUNA</option>
                                            <option value="3">CONSEJO EDUCATIVO</option>
                                        </select>
                                        <br /><span>Seleccione el tipo de persona a inscribir.</span>
                                    </td>
                                    <td width="25%"> </td>
                                </tr>
                                <tr>
                                    <td class="fondoGrisClaro" width="20%"><input class="fondoGrisClaro"
                                            id="labelcedula" name="labelcedula"
                                            onfocus="this.form.cedulaPasaporte.focus()" readonly=""
                                            style="border-style: none; " type="text" value="Cédula / Pasaporte" /> </td>
                                    <td class="fondoGrisOscuro">
                                        <input class="inactivo" disabled="disabled" id="cedulaPasaporte" maxlength="15"
                                            name="cedulaPasaporte"
                                            onblur="javascript:this.value=this.value.toUpperCase();EsCedula(this);"
                                            onchange="javascript:this.value=this.value.toUpperCase();EsCedula(this);"
                                            tabindex="1" type="text" value="" />
                                        <br /><span>Indique el número de CI o Pasaporte. En caso de CI debe indicar el
                                            caracter
                                            de nacionalidad 'V' o 'E', sin guiones, ni puntos. Ejemplo de la Cédula:
                                            V12345678.</span>
                                    </td>
                                    <td width="25%"> </td>
                                </tr>
                                <tr>
                                    <td class="fondoGrisClaro" width="20%"><input class="fondoGrisClaro"
                                            id="labelfechanac" name="labelfechanac" onfocus="this.form.fecha.focus()"
                                            readonly="" size="30" style="border-style: none; " type="text"
                                            value="Fecha Nacimiento / Constitución" />
                                    </td>
                                    <td class="fondoGrisOscuro">
                                        <input class="inactivo" disabled="disabled" id="fecha" maxlength="10"
                                            name="fecha" onblur="ValidDate(this)" onchange="ValidDate(this)"
                                            onkeypress="FormatoFecha(this);" tabindex="2" type="text" value="" />
                                        <a href="#" id="bFecha" onclick=""><img align="absmiddle" alt="Calendario"
                                                border="0" class="novisibles" height="20" id="p_calend" name="p_calend"
                                                src="<?= asset('img/simulator/inscripcion_rif/9bdef02d3863.gif') ?>"
                                                width="24" /></a>
                                        <br /><span>Indique la fecha solicitada. Ejemplo de fecha: '25/10/1995'.</span>
                                    </td>
                                    <td width="25%"> </td>
                                </tr>
                                <tr>
                                    <td class="fondoGrisClaro" width="20%"><input class="fondoGrisClaro"
                                            id="labelnombre" name="labelnombre" onfocus="this.form.razonSocial.focus()"
                                            readonly="" style="border-style: none; " type="text" value="Razón Social" />
                                    </td>
                                    <td class="fondoGrisOscuro">
                                        <input class="inactivo" disabled="disabled" id="razonSocial" maxlength="60"
                                            name="razonSocial" onblur="javascript:this.value=this.value.toUpperCase();"
                                            onchange="javascript:this.value=this.value.toUpperCase();"
                                            style="font-family: Verdana; font-size:10; width:100% ;height:18;"
                                            tabindex="3" type="text" value="" />
                                        <span>Indique la razón social del contribuyente. Indique el nombre de la
                                            parroquia del
                                            acta de defunción en caso de 'Sucesión sin cédula'.</span>
                                    </td>
                                    <td width="25%"> </td>
                                </tr>
                                <tr>
                                    <td class="fondoGrisClaro" width="20%"><input class="fondoGrisClaro"
                                            id="labelregistro" name="labelregistro"
                                            onfocus="this.form.registroProvidencia.focus()" readonly="" size="30"
                                            style="border-style: none; " type="text"
                                            value="Nro Registro / Providencia" /></td>
                                    <td class="fondoGrisOscuro">
                                        <input class="inactivo" disabled="disabled" id="registroProvidencia"
                                            maxlength="15" name="registroProvidencia"
                                            onblur="javascript:this.value=this.value.toUpperCase();EsNumeroCC(this);"
                                            onchange="javascript:this.value=this.value.toUpperCase();EsNumeroCC(this);"
                                            style="font-family: Verdana; font-size:10; width:50% ;height:18;"
                                            tabindex="4" type="text" value="" />
                                        <br /><span>Indique el número de registro del acta constitutiva de la empresa en
                                            caso de
                                            'Persona Juridica'. Indique el número de providencia del documento de
                                            creación en
                                            caso de 'Organismo Gubernamental'. Indique el número de MPPCPS en caso de
                                            'Consejo
                                            Comunal' ó 'Comuna'.</span>
                                    </td>
                                    <td width="25%"> </td>
                                </tr>
                                <tr>
                                    <td class="fondoGrisClaro" width="20%"><input class="fondoGrisClaro" id="labeltomo"
                                            name="labeltomo" onfocus="this.form.tomoGaceta.focus()" readonly=""
                                            style="border-style: none;" type="text" value="Nro Tomo / Gaceta" /></td>
                                    <td class="fondoGrisOscuro">
                                        <input class="inactivo" disabled="disabled" id="tomoGaceta" maxlength="15"
                                            name="tomoGaceta"
                                            onblur="javascript:this.value=this.value.toUpperCase();EsAlfaNumCC(this);"
                                            onchange="javascript:this.value=this.value.toUpperCase();EsAlfaNumCC(this);"
                                            style="font-family: Verdana; font-size:10; width:50% ;height:18;"
                                            tabindex="5" type="text" value="" />
                                        <br /><span>Indique el número de tomo del acta constitutiva de la empresa en
                                            caso de
                                            'Persona Juridica'. Indique el número de gaceta del documento de creación en
                                            caso de
                                            'Organismo Gubernamental'. Indique el número de registro en caso de 'Consejo
                                            Comunal' ó 'Comuna'.</span>
                                    </td>
                                    <td width="25%"> </td>
                                </tr>
                                <tr>
                                    <td class="fondoGrisClaro" width="20%"><input class="fondoGrisClaro"
                                            id="labelcorreo" name="labelcorreo" onfocus="this.form.correo.focus()"
                                            readonly="" style="border-style: none;" type="text" value="Correo" /></td>
                                    <td class="fondoGrisOscuro">
                                        <input class="inactivo" disabled="disabled" id="correo" maxlength="160"
                                            name="correo"
                                            onblur="javascript:this.value=this.value.toUpperCase();ValidarEmail(this);"
                                            onchange="javascript:this.value=this.value.toUpperCase();ValidarEmail(this);"
                                            style="font-family: Verdana; font-size:10; width:100% ;height:18;"
                                            tabindex="5" type="text" value="" />
                                        <span>Indique el correo electrónico.</span>
                                    </td>
                                    <td width="25%"> </td>
                                </tr>
                            </table>
                            <br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />
                            <table align="center" border="0" cellpadding="1" cellspacing="2" width="60%">
                                <tr>
                                    <td align="center" class="letras" style="FONT-SIZE: 8pt; HEIGHT:20px" width="100%">
                                        Si los
                                        datos ya están preinscritos puede agilizar la búsqueda por el <b> Número de
                                            Control</b>
                                        impreso en la planilla: </td>
                                </tr>
                                <tr>
                                    <td align="center" class="letrasSmall" width="100%"><input id="numeroControl"
                                            maxlength="20" name="numeroControl"
                                            style="font-family: Verdana; font-size:10; width:50% ;height:18;"
                                            type="text" />
                                    </td>
                                </tr>
                            </table>
                            <br />
                            <table align="center">
                                <tr>
                                    <td align="right" width="50%"><input class="boton" id="id" name="reestablecer"
                                            type="reset" value="Reestablecer" /></td>
                                    <td width="50%"><input class="boton" id="buscar" name="buscar" tabindex="6"
                                            type="submit" value="Buscar" /></td>
                                </tr>
                            </table>
                        </form>

                        <!-- Resultado de la búsqueda normal (oculto por defecto) -->
                        <div id="searchResults"
                            style="display: none; text-align: center; margin-top: 20px; font-weight: bold; color: #E32227; font-family: Verdana, Arial; font-size: 11px;">
                            No hay datos registrados para ese contribuyente, <a href="#" id="linkRegistrarDatos"
                                data-url="#"
                                style="color: navy; text-decoration: underline;">registrar datos aqui</a>.
                        </div>
                        <form
                            action="/rifcontribuyente/buscarexpediente.do;jsessionid=864fc90b43e0f7fca7046c62306a277c31d6e834fc2d936b7b0a4de2b2c9257a.e34PbhuMb3ePaO0La3ePaxqRb34Pe0"
                            method="post" name="BuscarInscritoRifForm">
                            <input id="exppersonalidad" name="exppersonalidad" type="hidden" value="" />
                            <input id="expcedulaPasaporte" name="expcedulaPasaporte" type="hidden" value="" />
                            <input id="exprazonSocial" name="exprazonSocial" type="hidden" value="" />
                            <input id="expcorreo" name="expcorreo" type="hidden" value="" />
                            <input id="expfecha" name="expfecha" type="hidden" value="" />
                            <input id="expregistroProvidencia" name="expregistroProvidencia" type="hidden" value="" />
                            <input id="exptomoGaceta" name="exptomoGaceta" type="hidden" value="" />
                            <input id="exprif" name="exprif" type="hidden" value="" />
                            <input id="expidcontribuyente" name="expidcontribuyente" type="hidden" value="" />
                            <input id="expnoresidenciado" name="expnoresidenciado" type="hidden" value="" />
                            <input id="expconsejocomunal" name="expconsejocomunal" type="hidden" value="" />
                            <input id="expcomuna" name="expcomuna" type="hidden" value="" />
                            <input id="expnodomiciliadocondireccion" name="expnodomiciliadocondireccion" type="hidden"
                                value="" />
                            <input id="expconsejoeducativo" name="expconsejoeducativo" type="hidden" value="" />
                        </form>
                    </td>
                </tr>
            </table>
            <br /><br /><br />
        </div>

        <script>
            Calendar.setup({
                inputField: "fecha",
                ifFormat: "%d/%m/%Y",
                button: "bFecha",
                align: "br",
                singleClick: true,
                weekNumbers: false,
                firstDay: 0,
                daFormat: "%d/%m/%Y"
            });
        </script>

    </div><!-- /.seniat-scope -->
</div><!-- /.seniat-wrapper -->

<!-- Modal: Advertencia de Sucesiones -->
<dialog id="sucesionRestrictDialog" class="modal-base">
    <div class="modal-base__container" style="max-width:420px;">
        <div class="modal-base__header" style="border-bottom:1px solid #e5e7eb;">
            <h3 class="modal-base__title" style="color:#1f2937; margin:0; font-size:1.25rem; font-weight:700;">
                Aviso
            </h3>
            <button type="button" class="modal-base__close"
                onclick="window.modalManager.close('sucesionRestrictDialog')">✕</button>
        </div>
        <div class="modal-base__body" style="text-align:center; padding:28px 24px;">
            <svg viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="#d97706" stroke-width="1.5"
                stroke-linecap="round" stroke-linejoin="round" style="margin-bottom:12px;">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            <p style="margin:0; font-size:.9375rem; color:#4b5563; line-height:1.6;">
                Esta funcionalidad solo está disponible para <b>Sucesiones (con o sin cédula)</b>.
            </p>
        </div>
        <div class="modal-base__footer">
            <button type="button" class="modal-btn modal-btn-primary"
                onclick="window.modalManager.close('sucesionRestrictDialog')"
                style="width: 100%; justify-content: center; background-color: #2563eb; color: white;">
                Aceptar
            </button>
        </div>
    </div>
</dialog>

<!-- Modal: Confirmación Sobrescribir -->
<dialog id="overwriteConfirmDialog" class="modal-base">
    <div class="modal-base__container" style="max-width:420px;">
        <div class="modal-base__header" style="border-bottom:1px solid #e5e7eb;">
            <h3 class="modal-base__title" style="color:#1f2937; margin:0; font-size:1.25rem; font-weight:700;">
                Advertencia
            </h3>
            <button type="button" class="modal-base__close"
                onclick="window.modalManager.close('overwriteConfirmDialog')">✕</button>
        </div>
        <div class="modal-base__body" style="text-align:center; padding:28px 24px;">
            <p style="margin:0; font-size:.9375rem; color:#4b5563; line-height:1.6;">
                Ya tiene datos guardados. Si continúa los reemplazará y perderá su progreso. ¿Desea continuar?
            </p>
        </div>
        <div class="modal-base__footer" style="display:flex; gap:12px; justify-content:center;">
            <button type="button" class="modal-btn modal-btn-secondary"
                onclick="window.modalManager.close('overwriteConfirmDialog')">
                Cancelar
            </button>
            <button type="button" class="modal-btn modal-btn-primary" onclick="window.confirmOverwriteAndProceed()"
                style="background-color: #d97706; color: white; border-color: #d97706;">
                Continuar y Reemplazar
            </button>
        </div>
    </div>
</dialog>

<!-- Modal: RIF Sucesoral ya completado -->
<dialog id="rifCompletadoModal" class="modal-base">
    <div class="modal-base__container" style="max-width:480px;">
        <div class="modal-base__header" style="border-bottom:1px solid #e5e7eb;">
            <h3 class="modal-base__title" style="color:#1f2937; margin:0; font-size:1.25rem; font-weight:700;">
                Inscripción Completada
            </h3>
            <button type="button" class="modal-base__close"
                onclick="window.modalManager.close('rifCompletadoModal')">✕</button>
        </div>
        <div class="modal-base__body" style="text-align:center; padding:28px 24px;">
            <svg viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="#16a34a" stroke-width="1.5"
                stroke-linecap="round" stroke-linejoin="round" style="margin-bottom:12px;">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
            <p style="margin:0 0 12px; font-size:.9375rem; color:#4b5563; line-height:1.6;">
                Estimado estudiante, usted <b>ya completó</b> el paso de inscripción de RIF Sucesoral.
            </p>
            <?php if (isset($intento) && !empty($intento['rif_sucesoral'])): ?>
            <p style="margin:0 0 16px; font-size:1.125rem; color:#1f2937; font-weight:700;">
                RIF asignado: <?= htmlspecialchars($intento['rif_sucesoral']) ?>
            </p>
            <?php endif; ?>
            <p style="margin:0; font-size:.875rem; color:#6b7280; line-height:1.6;">
                Ahora debe dirigirse a inscribirse en los <b>servicios de declaración sucesoral</b> para continuar el proceso. Si ya lo hizo, debe completar la declaración sucesoral.
            </p>
        </div>
        <div class="modal-base__footer">
            <button type="button" class="modal-btn modal-btn-primary"
                onclick="window.modalManager.close('rifCompletadoModal')"
                style="width: 100%; justify-content: center; background-color: #16a34a; color: white; border-color: #16a34a;">
                Entendido
            </button>
        </div>
    </div>
</dialog>

<?php
$borrador = null;
$intentoId = null;
if (isset($intento) && $intento) {
    if (!empty($intento['borrador_json'])) {
        $borrador = json_decode($intento['borrador_json'], true);
    }
    $intentoId = $intento['id'];
}
?>
<script>
    window.simBorrador = <?= json_encode($borrador) ?>;
    window.simIntentoId = <?= json_encode($intentoId) ?>;
    window.simBaseUrl = <?= json_encode(base_url('')) ?>;
    window.simRifGenerado = <?= json_encode(isset($intento) && !empty($intento['rif_sucesoral']) ? $intento['rif_sucesoral'] : null) ?>;
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/logged_layout.php';
?>