<?php
declare(strict_types=1);

$pageTitle = 'Relaciones — Simulador';
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
                                class=letrasFecha>Venezuela, <font size=1 face=Arial narrow>lunes 9 de marzo de 2026
                                </font></span>
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

                            <form name=CargaFamiliarForm method=post
                                action=javascript:void(0)>





                                <table id=tblPrincipal width=95% align=center cellpadding=2 cellspacing=0 border=0>
                                    <tbody>
                                        <tr>
                                            <td class=letrasSmall width=100% colspan=1 align=center>
                                                <b>Relaciones</b><br>
                                                <hr>
                                            </td>
                                        </tr>
                                </table>
                                <br>
                                <table cellspacing=2 cellpadding=1 border=0 width=95% align=center>
                                    <tbody>
                                        <tr>
                                            <td class=tablaSubTitulo width=05% style=FONT-SIZE:7pt;HEIGHT:20px>Remover
                                            </td>
                                            <td class=tablaSubTitulo width=25% style=FONT-SIZE:7pt;HEIGHT:20px>
                                                Parentesco </td>
                                            <td class=tablaSubTitulo width=25% style=FONT-SIZE:7pt;HEIGHT:20px>Nombres
                                            </td>
                                            <td class=tablaSubTitulo width=25% style=FONT-SIZE:7pt;HEIGHT:20px>Apellidos
                                            </td>
                                            <td class=tablaSubTitulo width=10% style=FONT-SIZE:7pt;HEIGHT:20px>
                                                Rif/Cédula </td>
                                            <td class=tablaSubTitulo width=10% style=FONT-SIZE:7pt;HEIGHT:20px>Pasaporte
                                            </td>
                                        </tr>

                                </table>
                                <br>
                                <table id=tblPrincipal width=95% align=center cellpadding=2 cellspacing=0 border=0>
                                    <tbody>
                                        <tr>
                                            <td align=center>
                                                <input type=button name=remover value=Remover class=boton id=remover>
                                            </td>
                                        </tr>
                                </table>


                                <br><br>
                                <table cellspacing=2 cellpadding=1 border=0 width=95% align=center>
                                    <tbody>
                                        <tr>
                                            <td class=tablaSubTitulo width=50% style=FONT-SIZE:7pt;HEIGHT:20px>Apellidos
                                            </td>
                                            <td class=tablaSubTitulo width=50% style=FONT-SIZE:7pt;HEIGHT:20px>Nombres
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class=letrasSmall width=50%><input type=text name=apellido maxlength=60
                                                    size=60 value
                                                    style=font-family:Verdana;font-size:10;width:100%;height:18
                                                    id=apellido></td>
                                            <td class=letrasSmall width=50%><input type=text name=nombre maxlength=60
                                                    size=60 value
                                                    style=font-family:Verdana;font-size:10;width:100%;height:18
                                                    id=nombre></td>
                                        </tr>
                                </table>
                                <table cellspacing=2 cellpadding=1 border=0 width=95% align=center>
                                    <tbody>
                                        <tr>
                                            <td class=tablaSubTitulo width=20% style=FONT-SIZE:7pt;HEIGHT:20px>Tipo de
                                                Documento </td>
                                            <td class=tablaSubTitulo width=30% style=FONT-SIZE:7pt;HEIGHT:20px>Cédula
                                            </td>
                                            <td class=tablaSubTitulo width=50% style=FONT-SIZE:7pt;HEIGHT:20px>
                                                Parentesco </td>
                                        </tr>
                                        <tr>
                                            <td class=letrasSmall width=20%>
                                                <input type=radio name=tipodocumento
                                                    style=font-family:Verdana;font-size:8;height:15 value=C
                                                    checked><span class=letras>Cédula</span>
                                                <input type=radio name=tipodocumento
                                                    style=font-family:Verdana;font-size:8;height:15 value=R><span
                                                    class=letras>Rif</span>
                                            </td>
                                            <td class=letrasSmall width=30%><input type=text name=cedula maxlength=10
                                                    size=10 value
                                                    style=font-family:Verdana;font-size:10;width:100%;height:18
                                                    id=cedula></td>
                                            <td class=letrasSmall width=50%>
                                                <select name=parentesco.codigo
                                                    style=font-family:Verdana;font-size:10;width:100%;height:18
                                                    id=parentesco.codigo>
                                                    <option value selected>SELECCIONAR</option>
                                                    <option value=51>HEREDERO</option>
                                                    <option value=50>REPRESENTANTE DE LA SUCESION
                                                </select>
                                            </td>
                                        </tr>
                                </table>
                                <table cellspacing=2 cellpadding=1 border=0 width=95% align=center>
                                    <tbody>
                                        <tr>
                                            <td class=tablaSubTitulo colspan=2 width=100%
                                                style=FONT-SIZE:7pt;HEIGHT:20px>En caso de ser extranjero sin cédula
                                                ingrese el número de pasaporte: </td>
                                        </tr>
                                        <tr>
                                            <td class=letrasSmall colspan=2 width=100%><input type=text name=pasaporte
                                                    maxlength=30 size=30 value
                                                    style=font-family:Verdana;font-size:10;width:100%;height:18
                                                    id=pasaporte></td>
                                        </tr>
                                </table>
                                <br>
                                <table id=tblPrincipal width=95% align=center cellpadding=2 cellspacing=0 border=0>
                                    <tbody>
                                        <tr>
                                            <td width=50% align=right><input type=reset name=reestablecer
                                                    value=Reestablecer class=boton id=reestablecer></td>
                                            <td width=50%><input type=button name=guardar value=Guardar class=boton
                                                    id=guardar></td>
                                        </tr>
                                </table>
                            </form>
                        </td>
                    </tr>
            </table>
            <br><br><br>
        </div>
    </div>
</div>

<?php
$intentoId = $intento['id'] ?? null;
$borrador = null;
if ($intento && !empty($intento['borrador_json'])) {
    $borrador = json_decode($intento['borrador_json'], true);
}
?>
<script>
    window.BASE_URL = "<?= base_url() ?>";
    window.simIntentoId = <?= json_encode($intentoId) ?>;
    window.simBorrador = <?= json_encode($borrador) ?>;
    window.simBaseUrl = <?= json_encode(rtrim(base_url(''), '/')) ?>;
</script>
<script src="<?= asset('js/simulator/legacy/relaciones_sim.js') ?>"></script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../../layouts/logged_layout.php';
?>
