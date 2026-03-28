<?php
declare(strict_types=1);

$pageTitle = 'Validar Inscripción — Simulador';
$activePage = 'simulador';

// ── Decodificar borrador ANTES del HTML para usarlo en condicionales ──
$intentoId = $intento['id'] ?? null;
$modalidad = $_SESSION['sim_modalidad'] ?? 'Practica_Libre';
$borrador = null;
if ($intento && !empty($intento['borrador_json'])) {
    $borrador = json_decode($intento['borrador_json'], true);
}

// ── Validar datos del borrador ──
$errores = [];

// 1. Verificar Domicilio Fiscal en direcciones
$direcciones = $borrador['direcciones'] ?? [];
$tieneDomicilioFiscal = false;
foreach ($direcciones as $dir) {
    if (($dir['tipoDireccion'] ?? '') === '06') {
        $tieneDomicilioFiscal = true;
        break;
    }
}
if (!$tieneDomicilioFiscal) {
    $errores[] = 'Se debe registrar obligatoriamente la dirección del Domicilio Fiscal.';
}

// 2. Verificar Representante de la Sucesión en relaciones
$relaciones = $borrador['relaciones'] ?? [];
$tieneRepresentante = false;
foreach ($relaciones as $rel) {
    if (($rel['parentesco'] ?? ($rel['parentesco.codigo'] ?? '')) === '50') {
        $tieneRepresentante = true;
        break;
    }
}
if (!$tieneRepresentante) {
    $errores[] = 'Se debe registrar obligatoriamente el Representante de la Sucesión.';
}

$datosCompletos = empty($errores);

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
    .seniat-scope select {
        box-sizing: border-box;
        font-family: inherit;
        font-size: inherit;
    }

    /* Prevent grid conflicts inside the scope */
    .seniat-scope .row {
        display: block;
        margin: 0;
    }
</style>

<div class="seniat-wrapper">
    <div class="seniat-scope">
        <div class="Bodyid1siteid0">
        <style>
            .seniat-scope .fondoPrincipal {
                background-color: #E52129
            }

            .seniat-scope .letras {
                FONT-SIZE: 11px;
                COLOR: black;
                FONT-FAMILY: Verdana, Arial
            }

            .seniat-scope .letrasLista {
                FONT-SIZE: 10px;
                COLOR: black;
                FONT-FAMILY: Verdana, Arial
            }

            .seniat-scope .boton {
                font-size: 8pt;
                font-family: Verdana;
                font-weight: bold;
                color: #000000;
                background-color: #ebebeb;
                width: 120px;
                height: 20px
            }

            .seniat-scope .barraPpal {
                BORDER-RIGHT: 0px;
                PADDING-RIGHT: 0px;
                BORDER-TOP: 0px;
                PADDING-LEFT: 0px;
                FONT-WEIGHT: bold;
                FONT-SIZE: 12px;
                PADDING-BOTTOM: 0px;
                BORDER-LEFT: 0px;
                COLOR: white;
                PADDING-TOP: 0px;
                BORDER-BOTTOM: 0px;
                FONT-FAMILY: Verdana, Arial;
                HEIGHT: 20px;
                BACKGROUND-COLOR: #CCCCCC;
                TEXT-ALIGN: center
            }

            .seniat-scope .tablaTitulo {
                BORDER-RIGHT: 0px;
                PADDING-RIGHT: 0px;
                BORDER-TOP: 0px;
                PADDING-LEFT: 0px;
                FONT-WEIGHT: bold;
                FONT-SIZE: 12px;
                PADDING-BOTTOM: 0px;
                BORDER-LEFT: 0px;
                COLOR: white;
                PADDING-TOP: 0px;
                BORDER-BOTTOM: 0px;
                FONT-FAMILY: Verdana, Arial;
                HEIGHT: 25px;
                BACKGROUND-COLOR: #E32227;
                TEXT-ALIGN: center
            }

            .seniat-scope .tablaSubTitulo {
                BORDER-RIGHT: 0px;
                PADDING-RIGHT: 0px;
                BORDER-TOP: 0px;
                PADDING-LEFT: 0px;
                FONT-WEIGHT: bold;
                PADDING-BOTTOM: 0px;
                BORDER-LEFT: 0px;
                COLOR: white;
                PADDING-TOP: 0px;
                BORDER-BOTTOM: 0px;
                FONT-FAMILY: Verdana, Arial;
                BACKGROUND-COLOR: #969696;
                TEXT-ALIGN: center
            }

            .seniat-scope .letrasSmall {
                FONT-SIZE: 9pt;
                COLOR: black;
                FONT-FAMILY: Verdana, Arial
            }

            .seniat-scope .letrasFecha {
                font-family: Verdana, Arial, Helvetica, sans-serif;
                color: #666666;
                font-size: 9px
            }

            .seniat-scope td {
                font-family: Verdana, Arial;
                font-size: 8pt
            }

            .seniat-scope .menuItem {
                background-color: #dedede;
                font-family: Verdana;
                font-weight: bold
            }
        </style>

            <table width=100% cellpadding=0 cellspacing=0 border=0 bgcolor=#FFFFFF>
                <colgroup>
                    <col style="width:265px;">
                    <col>
                    <col style="width:640px;">
                </colgroup>
                <tbody>
                    <tr>
                        <td align=left rowspan=2><img src="<?= asset('img/simulator/formularios_rif_sucesoral/logo_inscripcion_rif.jpg') ?>" border=0 width=208 height=71>
                        <td align=left valign=middle colspan=2 bgcolor=#FFFFFF><span
                                class=letrasFecha>Venezuela,
                                <font size=1 face=Arial narrow>lunes 9 de marzo de 2026</font>
                            </span>
                    <tr height=68>
                        <td style="background-IMAGE:url(<?= asset('img/simulator/formularios_rif_sucesoral/inscripcion_rif_gradient.jpg') ?>)"
                            height=68>&nbsp;
                        <td height=68 valign=baseline align=right class=fondoPrincipal><img src="<?= asset('img/simulator/formularios_rif_sucesoral/inscripcion_rif_header.jpg') ?>" width="640" height="68" alt="Aqui estan tus Tributos" border="0">
            </table>
            <table id=tblBarra width=100% cellpadding=0 cellspacing=5 class=barraPpal border=0 align=center>
                <tbody>
                    <tr>

                    </tr>
            </table>




            <style>
                .glossymenu {
                    padding: 0;
                    width: 205px;
                    border: 1px solid #E32227
                }

                .glossymenu a.menuitem {
                    background: #A7A7A7;
                    font: 11px Arial, Verdana;
                    color: black;
                    display: block;
                    position: relative;
                    width: auto;
                    padding: 4px 0;
                    padding-left: 10px;
                    text-decoration: none;
                    border-bottom: 1px solid #FFFFFF
                }

                .glossymenu span.menuitem {
                    background: #E32227;
                    font: bold 11px Verdana, Arial;
                    text-align: center;
                    color: white;
                    display: block;
                    position: relative;
                    width: auto;
                    padding: 4px 0;
                    padding-left: 10px;
                    text-decoration: none;
                    border-bottom: 2px solid #E32227
                }

                .glossymenu a.menuitem:visited {
                    color: black
                }

                .glossymenu a.menuitem:hover {
                    color: #E32227
                }

                .glossymenu div.submenu ul li a:hover {
                    color: #E32227
                }

                .glossymenu div.submenu1 ul li a:hover {
                    color: #E32227
                }

                .glossymenu div.submenu2 ul li a:hover {
                    color: #E32227
                }
            </style>
            <table>
                <tbody>
                    <tr>
                        <td valign=top>

                            <div class=glossymenu>
                                <span class=menuitem>Menú</span>


                                <a class="menuitem"
                                    href="<?= base_url('/simulador/inscripcion-rif/datos-basicos') ?>">Datos Básicos</a>


                                <a class="menuitem"
                                    href="<?= base_url('/simulador/inscripcion-rif/direcciones') ?>">Direcciones</a>


                                <a class="menuitem"
                                    href="<?= base_url('/simulador/inscripcion-rif/relaciones') ?>">Relaciones</a>


                                <a class=menuitem href=javascript:void(0)>Ver Planilla</a>


                                <a class="menuitem"
                                    href="<?= base_url('/simulador/inscripcion-rif/validar-inscripcion') ?>">Validar
                                    Inscripción</a>


                            </div>
                            &nbsp;
                        </td>
                        <td valign=top align=left width=100%>
                            <table width=100% class=tablaTitulo>
                                <tbody>
                                    <tr>
                                        <td width=100% valign=top align=center>Registro Único de Información Fiscal -
                                            Inscripción</td>
                                    </tr>
                            </table>
                            <br>
                            <center><span style=color:#FF0000;font-size:11px;font-family:Verdana,Arial><b></b></span>
                            </center>

                            <table align=center>
                                <tbody>
                                    <tr>
                                        <td class=letras align=center width=100%>
                                            <?php if ($datosCompletos): ?>
                                                &nbsp;<b>DATOS COMPLETOS</b>&nbsp;
                                            <?php else: ?>
                                                &nbsp;<b>FALTAN DATOS POR REGISTRAR</b>&nbsp;
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <br><br>
                            <?php if ($datosCompletos): ?>
                                <center><span style=font-size:10px;font-family:Verdana,Arial>Estimado Usuario, a partir de esta fecha Usted dispone de treinta (30) días continuos para entregar los requisitos respectivos y formalizar el trámite ante la Unidad/Sector/Gerencia Regional de Tributos Internos que de acuerdo a su domicilio fiscal le corresponda, de lo contrario la información será suprimida de nuestro sistema en el lapso indicado.</span></center>
                            <?php else: ?>
                                <?php foreach ($errores as $error): ?>
                                    <center><span style=color:#FF0000;font-size:10px><?= $error ?></span></center>
                                <?php endforeach; ?>
                            <?php endif; ?>

                        </td>
                    </tr>
                </tbody>
            </table>
            <br><br><br>
        </div>
    </div>
</div>

<!-- ── Modal: Información del proceso de validación ── -->
<?php if ($datosCompletos): ?>
<?php if ($modalidad === 'Evaluacion'): ?>
<dialog id="validarInscripcionModal" class="modal-base" data-no-backdrop-close>
<?php else: ?>
<dialog id="validarInscripcionModal" class="modal-base">
<?php endif; ?>
    <div class="modal-base__container" style="max-width:560px;">
        <div class="modal-base__header">
            <h3 class="modal-base__title">
                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle; margin-right:6px; color:var(--blue-600);">
                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>
                </svg>
                <?= ($modalidad === 'Evaluacion') ? 'Confirmación de envío' : 'Información sobre el proceso' ?>
            </h3>
            <?php if ($modalidad !== 'Evaluacion'): ?>
                <button class="modal-base__close" onclick="window.modalManager.close('validarInscripcionModal')">✕</button>
            <?php endif; ?>
        </div>
        <div class="modal-base__body" style="line-height:1.7; font-size:.9375rem; color:var(--gray-600);">
            <p style="margin:0 0 16px;">
                Estimado/a <strong><?= htmlspecialchars($user['name'] ?? 'Estudiante') ?></strong>,
            </p>
            <p style="margin:0 0 16px;">
                En el proceso real ante el <strong>SENIAT</strong>, una vez completados los datos de inscripción, el contribuyente debe imprimir la planilla generada y presentarla en la <strong>Unidad/Sector/Gerencia Regional de Tributos Internos</strong> correspondiente a su domicilio fiscal para la validación y creación formal del RIF Sucesoral.
            </p>
            <?php if ($modalidad === 'Evaluacion'): ?>
                <p style="margin:0 0 16px;">
                    Al confirmar, su inscripción será <strong>enviada al profesor para revisión</strong>. El profesor comparará los datos ingresados con los del caso asignado y decidirá si aprueba la generación del RIF Sucesoral.
                </p>
                <div style="background:var(--blue-50); border:1px solid var(--blue-200); border-radius:8px; padding:12px 16px; margin-top:4px;">
                    <p style="margin:0; font-size:.875rem; color:var(--blue-700);">
                        <strong>📋 Modo Evaluación:</strong> El RIF Sucesoral no se genera automáticamente. Su profesor revisará los datos y aprobará o rechazará la inscripción.
                    </p>
                </div>
            <?php else: ?>
                <p style="margin:0 0 16px;">
                    Para fines educativos, este sistema <strong>generará el RIF de manera automática</strong> al validar que los datos ingresados coincidan con los de su asignación. Recibirá en su correo electrónico registrado los resultados:
                </p>
                <ul style="margin:0 0 16px; padding-left:20px; color:var(--gray-600);">
                    <li style="margin-bottom:6px;">En caso de ser <strong style="color:var(--green-600);">correctos</strong>: el RIF Sucesoral generado junto con las instrucciones correspondientes.</li>
                    <li>En caso de presentar <strong style="color:var(--red-500);">inconsistencias</strong>: se le notificarán las observaciones para que pueda corregir los datos.</li>
                </ul>
                <div style="background:var(--amber-50); border:1px solid var(--amber-200); border-radius:8px; padding:12px 16px; margin-top:4px;">
                    <p style="margin:0; font-size:.875rem; color:var(--amber-700);">
                        <strong>📌 Nota sobre el proceso real:</strong> El SENIAT requiere el uso de un correo electrónico exclusivo para el RIF Sucesoral, se recomienda la creación de uno para uso exclusivo de la sucesión, ya que el sistema no admite direcciones de correo previamente registradas. Para efectos prácticos de esta simulación, se utilizará su correo institucional registrado.
                    </p>
                </div>
            <?php endif; ?>
        </div>
        <div class="modal-base__footer">
            <?php if ($modalidad === 'Evaluacion'): ?>
                <button class="modal-btn modal-btn-cancel" onclick="window.location.href='<?= base_url('/simulador/inscripcion-rif/datos-basicos') ?>'">
                    Cancelar y revisar datos
                </button>
            <?php else: ?>
                <button class="modal-btn modal-btn-cancel" onclick="window.modalManager.close('validarInscripcionModal')">
                    Cancelar
                </button>
            <?php endif; ?>
            <button class="modal-btn modal-btn-primary" id="btnConfirmarValidacion">
                <?= ($modalidad === 'Evaluacion') ? 'Confirmar y enviar al profesor' : 'Confirmar y validar' ?>
            </button>
        </div>
    </div>
</dialog>
<?php endif; ?>

<!-- ── Modal: Resultado de la validación (errores o éxito) ── -->
<?php if ($datosCompletos): ?>
<dialog id="resultadoValidacionModal" class="modal-base">
    <div class="modal-base__container" style="max-width:620px;">
        <div class="modal-base__header" id="resultadoModalHeader">
            <h3 class="modal-base__title" id="resultadoModalTitle"></h3>
            <button class="modal-base__close" onclick="window.modalManager.close('resultadoValidacionModal')">✕</button>
        </div>
        <div class="modal-base__body" id="resultadoModalBody" style="max-height:60vh; overflow-y:auto; line-height:1.7; font-size:.9375rem; color:var(--gray-600);">
        </div>
        <div class="modal-base__footer">
            <button class="modal-btn modal-btn-primary" onclick="window.modalManager.close('resultadoValidacionModal')">
                Entendido
            </button>
        </div>
    </div>
</dialog>
<?php endif; ?>

<?php if ($datosCompletos): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // ── No mostrar modal si ya se validó (redirigido con ?validado=1) ──
        var params = new URLSearchParams(window.location.search);
        var yaValidado = params.get('validado') === '1';

        if (!yaValidado) {
            // Auto-abrir modal informativo al cargar
            var checkModal = setInterval(function() {
                if (window.modalManager) {
                    clearInterval(checkModal);
                    window.modalManager.open('validarInscripcionModal');
                }
            }, 100);
        } else {
            // Limpiar params de la URL sin recargar
            history.replaceState(null, '', window.location.pathname);
        }

        // ── Botón "Confirmar y validar" ──
        var btnConfirmar = document.getElementById('btnConfirmarValidacion');
        if (btnConfirmar) {
            btnConfirmar.addEventListener('click', function() {
                var intentoId = window.simIntentoId;
                if (!intentoId) {
                    alert('No se encontró el intento activo.');
                    return;
                }

                btnConfirmar.disabled = true;
                btnConfirmar.textContent = 'Validando...';

                var apiUrl = (window.simBaseUrl || '') + '/api/intentos/' + intentoId + '/validar-rs';

                fetch(apiUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(function(r) {
                    if (!r.ok && r.status !== 200) {
                        return r.json().catch(function() {
                            throw new Error('El servidor respondió con código ' + r.status);
                        });
                    }
                    return r.json();
                })
                .then(function(data) {
                    // Cerrar modal informativo
                    if (window.modalManager) {
                        window.modalManager.close('validarInscripcionModal');
                    }

                    if (data.ok) {
                        // Evaluación: redirigir a mis-asignaciones directamente
                        if (data.pendiente_rif) {
                            window.location.href = (window.simBaseUrl || '') + '/mis-asignaciones';
                            return;
                        }
                        mostrarModalExito(data);
                    } else {
                        mostrarModalErrores(data);
                    }
                })
                .catch(function(error) {
                    console.error('Error al validar:', error);
                    btnConfirmar.disabled = false;
                    btnConfirmar.textContent = 'Confirmar y validar';
                    alert('Error de red al validar. Intente nuevamente.');
                });
            });
        }
    });

    // ══════════════════════════════════════════════════════
    //  Modal de ÉXITO
    // ══════════════════════════════════════════════════════
    function mostrarModalExito(data) {
        var title = document.getElementById('resultadoModalTitle');
        var body = document.getElementById('resultadoModalBody');

        // Modo evaluación: pendiente de aprobación del profesor
        if (data.pendiente_rif) {
            title.innerHTML = '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle; margin-right:6px; color:var(--blue-600);"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg> Inscripción Enviada';

            body.innerHTML =
                '<div style="text-align:center; padding:16px 0;">' +
                    '<div style="font-size:3rem; margin-bottom:12px;">📋</div>' +
                    '<p style="margin:0 0 8px; font-size:1.1rem; font-weight:600; color:var(--gray-700);">Pendiente de aprobación</p>' +
                    '<p style="margin:0; color:var(--gray-500); font-size:.9375rem;">' +
                        escapeHtml(data.mensaje || 'Su inscripción ha sido enviada al profesor para revisión.') +
                    '</p>' +
                '</div>' +
                '<div style="background:var(--blue-50); border:1px solid var(--blue-200); border-radius:8px; padding:12px 16px; margin-top:16px;">' +
                    '<p style="margin:0; font-size:.875rem; color:var(--blue-700);">' +
                        '<strong>📋 Modo Evaluación:</strong> El profesor revisará y comparará los datos ingresados con los del caso. Una vez aprobado, se generará su RIF Sucesoral.' +
                    '</p>' +
                '</div>';
        } else {
            // Práctica libre/guiada: mostrar RIF generado
            title.innerHTML = '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle; margin-right:6px; color:var(--green-600);"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg> Validación Exitosa';

            var rif = data.rif_sucesoral || 'N/A';
            var emailMsg = data.email_enviado
                ? '<p style="margin:16px 0 0; font-size:.875rem; color:var(--green-700);">✉️ También se ha enviado esta información a su <strong>correo electrónico</strong> registrado.</p>'
                : '';

            body.innerHTML =
                '<div style="text-align:center; padding:16px 0;">' +
                    '<p style="margin:0 0 8px; color:var(--gray-500); font-size:.875rem;">Su RIF Sucesoral generado es:</p>' +
                    '<div style="font-size:2rem; font-weight:700; letter-spacing:4px; color:var(--blue-600); background:var(--blue-50); border-radius:12px; padding:20px; margin:0 auto; display:inline-block;">' +
                        escapeHtml(rif) +
                    '</div>' +
                    emailMsg +
                '</div>' +
                '<div style="background:var(--amber-50); border:1px solid var(--amber-200); border-radius:8px; padding:12px 16px; margin-top:20px;">' +
                    '<p style="margin:0; font-size:.875rem; color:var(--amber-700);">' +
                        '<strong>📌 Nota:</strong> En el proceso real ante el SENIAT, debe presentar la planilla impresa ante la unidad correspondiente a su domicilio fiscal.' +
                    '</p>' +
                '</div>';
        }

        // Abrir modal — botón "Entendido" redirige al portal
        var checkModal = setInterval(function() {
            if (window.modalManager) {
                clearInterval(checkModal);
                var btnEntendido = document.querySelector('#resultadoValidacionModal .modal-btn-primary');
                if (btnEntendido) {
                    btnEntendido.onclick = function() {
                        window.location.href = (window.simBaseUrl || '') + '/simulador?validado=1';
                    };
                }
                window.modalManager.open('resultadoValidacionModal');
            }
        }, 50);
    }

    // ══════════════════════════════════════════════════════
    //  Modal de ERRORES (Discrepancias)
    // ══════════════════════════════════════════════════════
    function mostrarModalErrores(data) {
        var title = document.getElementById('resultadoModalTitle');
        var body = document.getElementById('resultadoModalBody');
        var btnConfirmar = document.getElementById('btnConfirmarValidacion');

        // Re-habilitar botón para que pueda reintentar
        if (btnConfirmar) {
            btnConfirmar.disabled = false;
            btnConfirmar.textContent = 'Confirmar y validar';
        }

        title.innerHTML = '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle; margin-right:6px; color:var(--red-500);"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg> Discrepancias Detectadas';

        var errores = data.errores || {};
        var secciones = [
            { key: 'causante',    titulo: 'Datos del Causante',  icono: '👤' },
            { key: 'relaciones',  titulo: 'Relaciones',          icono: '👥' },
            { key: 'direcciones', titulo: 'Direcciones',         icono: '📍' },
            { key: 'general',     titulo: 'General',             icono: '🔍' }
        ];

        var html = '<p style="margin:0 0 16px; color:var(--gray-500);">Se encontraron diferencias entre los datos ingresados y el caso asignado:</p>';
        var totalErrores = 0;

        secciones.forEach(function(sec) {
            var items = errores[sec.key];
            if (!items || items.length === 0) return;
            totalErrores += items.length;

            html += '<div style="margin-bottom:16px;">';
            html += '<h4 style="font-size:.875rem; color:var(--gray-700); margin:0 0 8px; border-bottom:1px solid var(--gray-200); padding-bottom:6px;">' +
                        sec.icono + ' ' + sec.titulo +
                    '</h4>';
            html += '<ul style="margin:0; padding-left:20px; font-size:.8125rem; line-height:1.8;">';

            items.forEach(function(err) {
                html += '<li style="color:var(--red-600);">' + escapeHtml(err) + '</li>';
            });

            html += '</ul></div>';
        });

        html += '<div style="background:var(--red-50); border:1px solid var(--red-200); border-radius:8px; padding:10px 14px; font-size:.8125rem; color:var(--red-700);">' +
                    '<strong>Total de discrepancias:</strong> ' + totalErrores +
                    ' — Corrija los datos en las secciones correspondientes e intente nuevamente.' +
                '</div>';

        body.innerHTML = html;

        // Abrir modal
        var checkModal = setInterval(function() {
            if (window.modalManager) {
                clearInterval(checkModal);
                window.modalManager.open('resultadoValidacionModal');
            }
        }, 50);
    }

    // ── Utilidad para escapar HTML ──
    function escapeHtml(text) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(text));
        return div.innerHTML;
    }
</script>
<?php endif; ?>

<script>
    window.BASE_URL = "<?= base_url() ?>";
    window.simIntentoId = <?= json_encode($intentoId) ?>;
    window.simBorrador = <?= json_encode($borrador) ?>;
    window.simBaseUrl = <?= json_encode(rtrim(base_url(''), '/')) ?>;
    window.simModalidad = <?= json_encode($modalidad) ?>;
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../../layouts/logged_layout.php';
?>
