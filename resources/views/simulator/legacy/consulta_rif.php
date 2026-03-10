<?php
declare(strict_types=1);

$pageTitle = 'Consulta de RIF — Simulador';
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
    }

    .seniat-scope *,
    .seniat-scope *::before,
    .seniat-scope *::after {
        box-sizing: content-box;
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

<link href="<?= asset('css/simulator/legacy/consulta_rif.css') ?>" rel="stylesheet" />
<script charset="utf-8" src="<?= asset('js/simulator/legacy/consulta_rif.js') ?>" type="text/javascript"></script>

<div class="seniat-wrapper">
    <div class="seniat-scope">

        <script language="JavaScript">
            function doSubmit() {
                document.consulta.busca.disabled = 'false';
                validaForma();
            }
            function validaForma() {
                if (document.getElementById('p_rif').value != '' || document.getElementById('p_cedula').value != '') {
                    document.forms[0].submit();
                } else {
                    alert('Debe introducir un rif o una cédula!!');
                    document.consulta.busca.disabled = '';
                    window.event.returnValue = false;
                }
            }
        </script>

        <div
            style="background: url('http://contribuyente.seniat.gob.ve/imagenes/fondoseniat.gif') #FFFFFF; color: #000000;">
            <form action="BuscaRif.jsp" id="consulta" method="post" name="consulta" onsubmit="doSubmit()">
                <br />
                <table width="100%">
                    <tr>
                        <td align="left" class="letrasLista" width="50%"> <b>RIF</b></td>
                        <td align="center" width="50%">
                            <input alt="Rif del Contribuyente" id="p_rif" maxlength="10" name="p_rif"
                                onblur="javascript:this.value=this.value.toUpperCase();"
                                onchange="javascript:this.value=this.value.toUpperCase();"
                                style="font-family: Verdana; font-size: 10;"
                                title="Ingrese su número de Rif, según Ejm " type="text" />
                        </td>
                    </tr>
                    <tr>
                        <td align="left" class="letrasLista" width="50%"> <b>CÉDULA O PASAPORTE</b></td>
                        <td align="center" width="50%">
                            <input alt="Cédula/Pasaporte del Contribuyente" id="p_cedula" maxlength="12" name="p_cedula"
                                onblur="javascript:this.value=this.value.toUpperCase();"
                                onchange="javascript:this.value=this.value.toUpperCase();"
                                style="font-family: Verdana; font-size: 10;"
                                title="Ingrese su número de Cédula o Pasaporte, según Ejm " type="text" />
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            Ejemplo del RIF: V123456789 <br />
                            <font face="Verdana" size="1"> Si el RIF es menor a nueve (9) dígitos
                                <br /> complete con ceros (0) a la izquierda
                            </font><br /><br />
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2"> Ejemplo de la Cédula: 12345678 <br /><br /></td>
                    </tr>
                    <tr>
                        <td colspan="2"> Ejemplo de Pasaporte: D1234567<br /><br /></td>
                    </tr>
                    <tr>
                        <td colspan="2"> Escriba las letras y/o números que observa en el recuadro:</td>
                    </tr>
                    <tr>
                        <td align="center" width="50%"><br /><img border="0"
                                src="<?= asset('img/simulator/consulta_rif/53aaaf7342a8.jpg') ?>" /></td>
                        <td align="center" width="50%"><br /><input id="codigo" maxlength="10" name="codigo"
                                style="font-family: Verdana; font-size: 10;" type="text" /></td>
                    </tr>
                    <tr>
                        <td align="center" width="50%"><br /><input class="boton" name="busca" type="submit"
                                value=" Buscar " /></td>
                        <td align="center" width="50%"><br /><input class="boton" onclick="window.close();"
                                type="button" value="Cancelar" /></td>
                    </tr>
                </table>
            </form>
            <!-- VISUALIZAR RIF -->
            <table align="center">
                <tr align="center">
                    <td align="center">
                        <br /><b></b><br /><br />
                        <b>
                            <font face="Verdana" size="2"> </font>
                        </b>
                    </td>
                </tr>
            </table>

            <table>
                <tr>
                    <td align="center" colspan="2">
                        <br />
                        <font face="Verdana" size="1">
                            <br /><br />
                            <br />
                            <br />
                            <br />
                            <br />
                            <br />
                        </font>
                    </td>
                </tr>
            </table>
        </div>

    </div><!-- /.seniat-scope -->
</div><!-- /.seniat-wrapper -->

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/logged_layout.php';
?>