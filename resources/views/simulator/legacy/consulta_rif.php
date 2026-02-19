
<html>
<head><meta charset="utf-8"/><meta content="http://contribuyente.seniat.gob.ve/BuscaRif/BuscaRif.jsp" name="cloned-from"/>

<meta content="text/css" http-equiv="Content-Style-Type"/>
<link href="<?= asset('css/simulator/legacy/consulta_rif.css') ?>" rel="stylesheet"/>
<script charset="utf-8" src="<?= asset('js/simulator/legacy/consulta_rif.js') ?>" type="text/javascript"></script>
<title>Consulta de RIF </title>
<script language="JavaScript">
            function doSubmit(){
                document.consulta.busca.disabled = 'false';
                validaForma();
            }
            function validaForma() {        
                if(document.getElementById('p_rif').value != '' || document.getElementById('p_cedula').value != '') {
                    document.forms[0].submit();
                }else {
                    alert('Debe introducir un rif o una cédula!!');
                    document.consulta.busca.disabled = '';
                    window.event.returnValue = false;
                }
            }
        </script>
</head>
<body background="http://contribuyente.seniat.gob.ve/imagenes/fondoseniat.gif" bgcolor="#FFFFFF" text="#000000">
<form action="BuscaRif.jsp" id="consulta" method="post" name="consulta" onsubmit="doSubmit()">
<br/>
<table width="100%">
<tr>
<td align="left" class="letrasLista" width="50%">     <b>RIF</b></td>
<td align="center" width="50%">
<input alt="Rif del Contribuyente" id="p_rif" maxlength="10" name="p_rif" onblur="javascript:this.value=this.value.toUpperCase();" onchange="javascript:this.value=this.value.toUpperCase();" style="font-family: Verdana; font-size: 10;" title="Ingrese su número de Rif, según Ejm " type="text"/>
</td>
</tr>
<tr>
<td align="left" class="letrasLista" width="50%">     <b>CÉDULA O PASAPORTE</b></td>
<td align="center" width="50%">
<input alt="Cédula/Pasaporte del Contribuyente" id="p_cedula" maxlength="12" name="p_cedula" onblur="javascript:this.value=this.value.toUpperCase();" onchange="javascript:this.value=this.value.toUpperCase();" style="font-family: Verdana; font-size: 10;" title="Ingrese su número de Cédula o Pasaporte, según Ejm " type="text"/>
</td>
</tr>
<tr>
<td colspan="2">
                             Ejemplo del RIF: V123456789 <br/>
                             <font face="Verdana" size="1"> Si el RIF es menor a nueve (9) dígitos
                        <br/>       complete  con ceros (0) a la izquierda </font><br/><br/>
</td>
</tr>
<tr>
<td colspan="2">     Ejemplo de la Cédula: 12345678 <br/><br/></td>
</tr>
<tr>
<td colspan="2">     Ejemplo de Pasaporte: D1234567<br/><br/></td>
</tr>
<tr>
<td colspan="2">     Escriba las letras y/o números que observa en el recuadro:</td>
</tr>
<tr>
<td align="center" width="50%"><br/><img border="0" src="<?= asset('img/simulator/consulta_rif/53aaaf7342a8.jpg') ?>"/></td>
<td align="center" width="50%"><br/><input id="codigo" maxlength="10" name="codigo" style="font-family: Verdana; font-size: 10;" type="text"/></td>
</tr>
<tr>
<td align="center" width="50%"><br/><input class="boton" name="busca" type="submit" value=" Buscar "/></td>
<td align="center" width="50%"><br/><input class="boton" onclick="window.close();" type="button" value="Cancelar"/></td>
</tr>
</table>
</form>
<!-- VISUALIZAR RIF -->
<table align="center">
<tr align="center">
<td align="center">
<br/><b></b><br/><br/>
<b><font face="Verdana" size="2"> </font></b>
</td>
</tr>
</table>

<table>
<tr>
<td align="center" colspan="2">
<br/><font face="Verdana" size="1">
<br/><br/>
<br/>
<br/>
<br/>
<br/>
<br/>
</font></td>
</tr>
</table>
</body>
</html>
