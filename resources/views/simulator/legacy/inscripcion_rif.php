
<html lang="es">
<head><meta charset="utf-8"/><meta content="http://contribuyente.seniat.gob.ve/rifcontribuyente/logincontribuyente.do" name="cloned-from"/>

<meta content="text/css" http-equiv="Content-Style-Type"/>
<link href="<?= asset('css/simulator/legacy/inscripcion_rif.css') ?>" rel="stylesheet"/>
<script charset="utf-8" src="<?= asset('js/simulator/legacy/inscripcion_rif.js') ?>" type="text/javascript"></script>
</head>
<body class="Bodyid1siteid0" leftmargin="0" marginheight="0" marginwidth="0" rightmargin="0" topmargin="0">
<table bgcolor="#FFFFFF" border="0" cellpadding="0" cellspacing="0" width="100%"><tr><td align="left" rowspan="2" width="265"><img border="0" height="73" src="<?= asset('img/simulator/inscripcion_rif/79a6e64baf42.gif') ?>" width="207"/></td><td align="left" bgcolor="#FFFFFF" colspan="2" valign="middle" width="322"><span class="letrasFecha">Venezuela, <script language="javascript">dows = new Array("domingo","lunes","martes","mi&eacute;rcoles","jueves","viernes","sabado");months = new    Array("enero","febrero","marzo","abril","mayo","junio","julio","agosto","septiembre","octubre","noviembre","diciembre");now = new Date();dow = now.getDay();d = now.getDate();m = now.getMonth();h = now.getTime();y = now.getFullYear();document.write("<font size=1 face=Arial Narrow>" + dows[dow]+" "+d+" de "+months[m]+" de "+y + "</font>");</script></span></td></tr><tr height="68"><td height="68" style="background-IMAGE: url('<?= asset('img/simulator/inscripcion_rif/4c907c96f891.jpg') ?>');"> </td><td align="right" class="fondoPrincipal" height="68" valign="baseline" width="640"><img alt="Aqui estan tus Tributos" border="0" height="68" src="<?= asset('img/simulator/inscripcion_rif/5e3bb17364a1.jpg') ?>" width="640"/></td></tr></table>
<table align="center" border="0" cellpadding="0" cellspacing="5" class="barraPpal" id="tblBarra" width="100%">
<tr>
</tr>
</table>
<script>
var reincorporar = '';
function EsCedula(cedula){
  if (obtenerObjeto('personalidad','value') == "1" || obtenerObjeto('personalidad','value') == "2" || obtenerObjeto('personalidad','value') == "6"){
      ValidarCedula(cedula);
  }
}
</script>
<style>
    .visibles { visibility:visible }
    .novisibles { visibility:hidden }
	  .inactivo {background-color: #dcdcdc;}
    .activo{background-color: White;}
</style>
<table align="center" border="0" cellpadding="0" cellspacing="0" width="100%">
<tr>
<!--------------------->
<td align="left" valign="top" width="80%">
<table class="tablaTitulo" valign="top" width="100%">
<tr>
<td align="center" valign="top" width="100%">Registro Único de Información Fiscal - Inscripción</td>
</tr>
</table>
<br/>
<center><span style="color:#FF0000; font-size:11px; font-family: Verdana, Arial"><b></b></span></center>
<br/>
<form action="/rifcontribuyente/buscarpreinscrito.do;jsessionid=864fc90b43e0f7fca7046c62306a277c31d6e834fc2d936b7b0a4de2b2c9257a.e34PbhuMb3ePaO0La3ePaxqRb34Pe0" method="post" name="BuscarInscritoRifForm" onsubmit="return ValidarBusquedaPreinscritos(reincorporar);">
<input id="noresidenciado" name="noresidenciado" type="hidden" value=""/>
<input id="consejocomunal" name="consejocomunal" type="hidden" value=""/>
<input id="comuna" name="comuna" type="hidden" value=""/>
<input id="nodomiciliadocondireccion" name="nodomiciliadocondireccion" type="hidden" value=""/>
<input id="consejoeducativo" name="consejoeducativo" type="hidden" value=""/>
<table align="right" border="0" cellpadding="5" cellspacing="2" class="letras" width="80%">
<tr>
<td class="fondoGrisClaro" width="20%">Tipo de Persona </td>
<td class="fondoGrisOscuro">
<select class="letras" id="personalidad" name="personalidad" onchange="ActivarBusqPreinscritos();"><option value="">SELECCIONAR</option>
<option value="1">PERSONA NATURAL VENEZOLANA</option>
<option value="2">PERSONA NATURAL EXTRANJERA CON CEDULA</option>
<option value="4">PERSONA NATURAL EXTRANJERA CON PASAPORTE</option>
<option value="1">PERSONA NATURAL NO RESIDENTE CON BASE FIJA CON CÉDULA VENEZOLANA</option>
<option value="2">PERSONA NATURAL NO RESIDENTE CON BASE FIJA CON CÉDULA EXTRANJERA</option>
<option value="4">PERSONA NATURAL NO RESIDENTE CON BASE FIJA CON PASAPORTE</option>
<option value="3">PERSONA JURIDÍCA</option>
<option value="3">PERSONA JURIDICA NO DOMICILIADA CON ESTABLECIMIENTO PERMANENTE</option>
<option value="5">ORGANISMO GUBERNAMENTAL</option>
<option value="6">SUCESION CON CÉDULA</option>
<option value="6">SUCESION SIN CÉDULA</option>
<option value="3">CONSEJO COMUNAL</option>
<option value="3">COMUNA</option>
<option value="3">CONSEJO EDUCATIVO</option></select>
<br/><span>Seleccione el tipo de persona a inscribir.</span>
</td>
<td width="25%"> </td>
</tr>
<tr>
<td class="fondoGrisClaro" width="20%"><input class="fondoGrisClaro" id="labelcedula" name="labelcedula" onfocus="this.form.cedulaPasaporte.focus()" readonly="" style="border-style: none; " type="text" value="Cédula / Pasaporte"/> </td>
<td class="fondoGrisOscuro">
<input class="inactivo" disabled="disabled" id="cedulaPasaporte" maxlength="15" name="cedulaPasaporte" onblur="javascript:this.value=this.value.toUpperCase();EsCedula(this);" onchange="javascript:this.value=this.value.toUpperCase();EsCedula(this);" tabindex="1" type="text" value=""/>
<br/><span>Indique el número de CI o Pasaporte. En caso de CI debe indicar el caracter de nacionalidad 'V' o 'E', sin guiones, ni puntos. Ejemplo de la Cédula: V12345678. </span>
</td>
<td width="25%"> </td>
</tr>
<tr>
<td class="fondoGrisClaro" width="20%"><input class="fondoGrisClaro" id="labelfechanac" name="labelfechanac" onfocus="this.form.fecha.focus()" readonly="" size="30" style="border-style: none; " type="text" value="Fecha Nacimiento / Constitución"/> </td>
<td class="fondoGrisOscuro">
<input class="inactivo" disabled="disabled" id="fecha" maxlength="10" name="fecha" onblur="ValidDate(this)" onchange="ValidDate(this)" onkeypress="FormatoFecha(this);" tabindex="2" type="text" value=""/>
<a href="#" id="bFecha" onclick=""><img align="absmiddle" alt="Calendario" border="0" class="novisibles" height="20" id="p_calend" name="p_calend" src="<?= asset('img/simulator/inscripcion_rif/9bdef02d3863.gif') ?>" width="24"/></a>
<br/><span>Indique la fecha solicitada. Ejemplo de fecha: '25/10/1995'.</span>
</td>
<td width="25%"> </td>
</tr>
<tr>
<td class="fondoGrisClaro" width="20%"><input class="fondoGrisClaro" id="labelnombre" name="labelnombre" onfocus="this.form.razonSocial.focus()" readonly="" style="border-style: none; " type="text" value="Razón Social"/></td>
<td class="fondoGrisOscuro">
<input class="inactivo" disabled="disabled" id="razonSocial" maxlength="60" name="razonSocial" onblur="javascript:this.value=this.value.toUpperCase();" onchange="javascript:this.value=this.value.toUpperCase();" style="font-family: Verdana; font-size:10; width:100% ;height:18;" tabindex="3" type="text" value=""/>
<span>Indique la razón social del contribuyente. Indique el nombre de la parroquia del acta de defunción en caso de 'Sucesión sin cédula'.</span>
</td>
<td width="25%"> </td>
</tr>
<tr>
<td class="fondoGrisClaro" width="20%"><input class="fondoGrisClaro" id="labelregistro" name="labelregistro" onfocus="this.form.registroProvidencia.focus()" readonly="" size="30" style="border-style: none; " type="text" value="Nro Registro / Providencia"/></td>
<td class="fondoGrisOscuro">
<input class="inactivo" disabled="disabled" id="registroProvidencia" maxlength="15" name="registroProvidencia" onblur="javascript:this.value=this.value.toUpperCase();EsNumeroCC(this);" onchange="javascript:this.value=this.value.toUpperCase();EsNumeroCC(this);" style="font-family: Verdana; font-size:10; width:50% ;height:18;" tabindex="4" type="text" value=""/>
<br/><span>Indique el número de registro del acta constitutiva de la empresa en caso de 'Persona Juridica'. Indique el número de providencia del documento de creación en caso de 'Organismo Gubernamental'. Indique el número de MPPCPS en caso de 'Consejo Comunal' ó 'Comuna'.</span>
</td>
<td width="25%"> </td>
</tr>
<tr>
<td class="fondoGrisClaro" width="20%"><input class="fondoGrisClaro" id="labeltomo" name="labeltomo" onfocus="this.form.tomoGaceta.focus()" readonly="" style="border-style: none;" type="text" value="Nro Tomo / Gaceta"/></td>
<td class="fondoGrisOscuro">
<input class="inactivo" disabled="disabled" id="tomoGaceta" maxlength="15" name="tomoGaceta" onblur="javascript:this.value=this.value.toUpperCase();EsAlfaNumCC(this);" onchange="javascript:this.value=this.value.toUpperCase();EsAlfaNumCC(this);" style="font-family: Verdana; font-size:10; width:50% ;height:18;" tabindex="5" type="text" value=""/>
<br/><span>Indique el número de tomo del acta constitutiva de la empresa en caso de 'Persona Juridica'. Indique el número de gaceta del documento de creación en caso de 'Organismo Gubernamental'. Indique el número de registro en caso de 'Consejo Comunal' ó 'Comuna'.</span>
</td>
<td width="25%"> </td>
</tr>
<tr>
<td class="fondoGrisClaro" width="20%"><input class="fondoGrisClaro" id="labelcorreo" name="labelcorreo" onfocus="this.form.correo.focus()" readonly="" style="border-style: none;" type="text" value="Correo"/></td>
<td class="fondoGrisOscuro">
<input class="inactivo" disabled="disabled" id="correo" maxlength="160" name="correo" onblur="javascript:this.value=this.value.toUpperCase();ValidarEmail(this);" onchange="javascript:this.value=this.value.toUpperCase();ValidarEmail(this);" style="font-family: Verdana; font-size:10; width:100% ;height:18;" tabindex="5" type="text" value=""/>
<span>Indique el correo electrónico.</span>
</td>
<td width="25%"> </td>
</tr>
</table>
<br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/>
<table align="center" border="0" cellpadding="1" cellspacing="2" width="60%">
<tr>
<td align="center" class="letras" style="FONT-SIZE: 8pt; HEIGHT:20px" width="100%">Si los datos ya están preinscritos puede agilizar la búsqueda por el <b> Número de Control</b> impreso en la planilla: </td>
</tr>
<tr>
<td align="center" class="letrasSmall" width="100%"><input id="numeroControl" maxlength="20" name="numeroControl" style="font-family: Verdana; font-size:10; width:50% ;height:18;" type="text"/></td>
</tr>
</table>
<br/>
<table align="center">
<tr>
<td align="right" width="50%"><input class="boton" id="id" name="reestablecer" type="reset" value="Reestablecer"/></td>
<td width="50%"><input class="boton" id="buscar" name="buscar" tabindex="6" type="submit" value="Buscar"/></td>
</tr>
</table>
</form>
<form action="/rifcontribuyente/buscarexpediente.do;jsessionid=864fc90b43e0f7fca7046c62306a277c31d6e834fc2d936b7b0a4de2b2c9257a.e34PbhuMb3ePaO0La3ePaxqRb34Pe0" method="post" name="BuscarInscritoRifForm">
<input id="exppersonalidad" name="exppersonalidad" type="hidden" value=""/>
<input id="expcedulaPasaporte" name="expcedulaPasaporte" type="hidden" value=""/>
<input id="exprazonSocial" name="exprazonSocial" type="hidden" value=""/>
<input id="expcorreo" name="expcorreo" type="hidden" value=""/>
<input id="expfecha" name="expfecha" type="hidden" value=""/>
<input id="expregistroProvidencia" name="expregistroProvidencia" type="hidden" value=""/>
<input id="exptomoGaceta" name="exptomoGaceta" type="hidden" value=""/>
<input id="exprif" name="exprif" type="hidden" value=""/>
<input id="expidcontribuyente" name="expidcontribuyente" type="hidden" value=""/>
<input id="expnoresidenciado" name="expnoresidenciado" type="hidden" value=""/>
<input id="expconsejocomunal" name="expconsejocomunal" type="hidden" value=""/>
<input id="expcomuna" name="expcomuna" type="hidden" value=""/>
<input id="expnodomiciliadocondireccion" name="expnodomiciliadocondireccion" type="hidden" value=""/>
<input id="expconsejoeducativo" name="expconsejoeducativo" type="hidden" value=""/>
</form>
</td>
</tr>
</table>
<br/><br/><br/>
</body>
</html>
<script>
    Calendar.setup({
        inputField     :    "fecha",     // id of the input field
        ifFormat       :    "%d/%m/%Y",     // format of the input field
        button         :    "bFecha",     // trigger for the calendar (button ID)
        align          :    "br",           // alignment (defaults to "Bl")
        singleClick    :    true,
        weekNumbers    :    false,
        firstDay       :    0,
        daFormat       :    "%d/%m/%Y"
    });   
</script>
