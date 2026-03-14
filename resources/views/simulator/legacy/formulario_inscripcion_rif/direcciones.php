<?php
declare(strict_types=1);

$pageTitle = 'Direcciones — Simulador';
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

<link rel="stylesheet" href="<?= asset('css/simulator/legacy/formulario_inscripcion_rif/direcciones.css') ?>">

<div class="seniat-wrapper">
    <div class="seniat-scope">
        <div class="Bodyid1siteid0">
<table width=100% cellpadding=0 cellspacing=0 border=0 bgcolor=#FFFFFF>
<colgroup><col style="width:265px;"><col><col style="width:640px;"></colgroup>
<tbody><tr><td align=left rowspan=2><img src="<?= asset('img/simulator/formularios_rif_sucesoral/logo_inscripcion_rif.jpg') ?>" border=0 width=208 height=71><td align=left valign=middle colspan=2 bgcolor=#FFFFFF><span class=letrasFecha>Venezuela, <font size=1 face=Arial narrow>lunes 9 de marzo de 2026</font></span><tr height=68><td style="background-IMAGE:url(<?= asset('img/simulator/formularios_rif_sucesoral/inscripcion_rif_gradient.jpg') ?>)" height=68>&nbsp;<td height=68 valign=baseline align=right class=fondoPrincipal><img src="<?= asset('img/simulator/formularios_rif_sucesoral/inscripcion_rif_header.jpg') ?>" width="640" height="68" alt="Aqui estan tus Tributos" border="0"></table>
<table id=tblBarra width=100% cellpadding=0 cellspacing=5 class=barraPpal border=0 align=center>
 <tbody><tr>
 
 </tr>
</table>
 
 
 




<table>
<tbody><tr><td valign=top>
 
<div class=glossymenu>
 <span class=menuitem>Menú</span>
 
 
 <a class="menuitem" href="<?= base_url('/simulador/inscripcion-rif/datos-basicos') ?>">Datos Básicos</a>
 
 
 <a class="menuitem" href="<?= base_url('/simulador/inscripcion-rif/direcciones') ?>">Direcciones</a>
 
 
 <a class="menuitem" href="<?= base_url('/simulador/inscripcion-rif/relaciones') ?>">Relaciones</a>
 
 
 <a class=menuitem href=javascript:void(0)>Ver Planilla</a>
 
 
 <a class="menuitem" href="<?= base_url('/simulador/inscripcion-rif/validar-inscripcion') ?>">Validar Inscripción</a>
 
 
</div>
&nbsp;
</td> 
 
 <td valign=top align=left width=100%>
 <table width=100% class=tablaTitulo>
 <tbody><tr>
 <td width=100% valign=top align=center>Registro Único de Información Fiscal - Inscripción</td>
 </tr> 
 </table>
 <br>
 <center><span style=color:#FF0000;font-size:11px;font-family:Verdana,Arial><b></b></span></center>
 
 <form name=DireccionForm method=post action=/rifcontribuyente/datosdirecciones.do>
 
 <table id=tblPrincipal width=95% align=center cellpadding=2 cellspacing=0 border=0> 
 <tbody><tr>
 
 
 
 <td class=letrasSmall width=100% colspan=1 align=center><b>Direcciones</b><br><hr></td>
 
 
 </tr>
 </table>
 <br>
 <table cellspacing=2 cellpadding=1 border=0 width=95% align=center>
 <tbody><tr>
 <td class=tablaSubTitulo width=05% colspan=1 style=FONT-SIZE:7pt;HEIGHT:20px>Remover </td>
 <td class=tablaSubTitulo width=25% colspan=1 style=FONT-SIZE:7pt;HEIGHT:20px>Tipo de Dirección </td>
 <td class=tablaSubTitulo width=20% colspan=1 style=FONT-SIZE:7pt;HEIGHT:20px>Tipo Vialidad </td> 
 <td class=tablaSubTitulo width=20% colspan=1 style=FONT-SIZE:7pt;HEIGHT:20px>Tipo Sector </td> 
 <td class=tablaSubTitulo width=20% colspan=1 style=FONT-SIZE:7pt;HEIGHT:20px>Ciudad </td> 
 <td class=tablaSubTitulo width=10% colspan=1 style=FONT-SIZE:7pt;HEIGHT:20px>Estado </td> 
 </tr> 
 <tr class="dir-empty">
 <td colspan="6" style="FONT-SIZE:7pt;HEIGHT:20px;BACKGROUND-COLOR:#D7D7D7;text-align:center;font-family:Verdana,Arial;">No existen direcciones cargadas</td>
 </tr>
 
 </table> 
 <br>
 <table id=tblPrincipal width=95% align=center cellpadding=2 cellspacing=0 border=0> 
 <tbody><tr>
 <td align=center>
 <input type=submit name=remover value=Remover class=boton id=remover>
 </td> 
 </tr> 
 </table> 
 
 
 <br><br> 
 <table cellspacing=2 cellpadding=1 border=0 width=95% align=center>
 
 
 
 <tbody><tr>
 <td class=tablaSubTitulo colspan=4 width=100% style=FONT-SIZE:7pt;HEIGHT:20px>Domicilio Fiscal y otras Direcciones </td>
 </tr>
 <tr>
 <td class=letrasSmall colspan=4 width=100%>
 <select name=tipoDireccion.codigo style=font-family:Verdana;font-size:10;width:50%;height:18 id=tipoDireccion.codigo><option value selected>SELECCIONAR</option>
                      <option value=03>BODEGA, ALMACENAMIENTO, DEPÓSITO</option>
<option value=01>CASA MATRIZ O ESTABLECIMIENTO PRINCIPAL</option>
<option value=91>DIRECCIÓN DE NOTIFICACIÓN FÍSICA</option>
<option value=06>DOMICILIO FISCAL</option>
<option value=04>NEGOCIO INDEPENDIENTE</option>
<option value=05>PLANTA INDUSTRIAL O FABRICA</option>
<option value=02>SUCURSAL COMERCIAL</select> 
 </td>
 </tr> 
 
 
 
 
 
 
 <tr>
 <td class=tablaSubTitulo colspan=2 width=50% style=FONT-SIZE:7pt;HEIGHT:20px>
 
 
 <input type=radio name=radiovialidad id=radiovialidad style=font-family:Verdana;font-size:8;height:15 value=01>calle
 
 <input type=radio name=radiovialidad id=radiovialidad style=font-family:Verdana;font-size:8;height:15 value=02>avenida
 
 <input type=radio name=radiovialidad id=radiovialidad style=font-family:Verdana;font-size:8;height:15 value=03>vereda
 
 <input type=radio name=radiovialidad id=radiovialidad style=font-family:Verdana;font-size:8;height:15 value=04>carretera
 
 <input type=radio name=radiovialidad id=radiovialidad style=font-family:Verdana;font-size:8;height:15 value=05>esquina
 
 <input type=radio name=radiovialidad id=radiovialidad style=font-family:Verdana;font-size:8;height:15 value=06>carrera
 
 </td>
 <td class=tablaSubTitulo colspan=2 width=50% style=FONT-SIZE:7pt;HEIGHT:20px>
 
 
 <input type=radio name=radioedificacion id=radioedificacion style=font-family:Verdana;font-size:8;height:15 value=01>edificio
 
 <input type=radio name=radioedificacion id=radioedificacion style=font-family:Verdana;font-size:8;height:15 value=02>centro comercial
 
 <input type=radio name=radioedificacion id=radioedificacion style=font-family:Verdana;font-size:8;height:15 value=03>quinta
 
 <input type=radio name=radioedificacion id=radioedificacion style=font-family:Verdana;font-size:8;height:15 value=04>casa
 
 <input type=radio name=radioedificacion id=radioedificacion style=font-family:Verdana;font-size:8;height:15 value=05>local
 
 </td> 
 </tr> 
 <tr>
 <td class=letrasSmall colspan=2 width=50%><input type=text name=vialidad.descripcion maxlength=120 size=60 value style=font-family:Verdana;font-size:10;width:100%;height:18 id=vialidad.descripcion></td> 
 <td class=letrasSmall colspan=2 width=50%><input type=text name=edificacion.descripcion maxlength=120 size=60 value style=font-family:Verdana;font-size:10;width:65%;height:18 id=edificacion.descripcion>
 <input type=text name=label style=border-style:none;background-color:WHITE;width:10% class=letras value readonly>
 <input type=text name=piso value style=font-family:Verdana;font-size:10;width:20%;height:18> </td> 
 </tr> 
 <tr>
 <td class=tablaSubTitulo colspan=2 width=50% style=FONT-SIZE:7pt;HEIGHT:20px>
 
 
 <input type=radio name=radiolocal id=radiolocal style=font-family:Verdana;font-size:8;height:15 value=01>apartamento
 
 <input type=radio name=radiolocal id=radiolocal style=font-family:Verdana;font-size:8;height:15 value=02>local
 
 <input type=radio name=radiolocal id=radiolocal style=font-family:Verdana;font-size:8;height:15 value=03>oficina
 
 </td>
 <td class=tablaSubTitulo colspan=2 width=50% style=FONT-SIZE:7pt;HEIGHT:20px>
 
 
 <input type=radio name=radiosector id=radiosector style=font-family:Verdana;font-size:8;height:15 value=01>urbanizacion
 
 <input type=radio name=radiosector id=radiosector style=font-family:Verdana;font-size:8;height:15 value=02>zona
 
 <input type=radio name=radiosector id=radiosector style=font-family:Verdana;font-size:8;height:15 value=03>sector
 
 <input type=radio name=radiosector id=radiosector style=font-family:Verdana;font-size:8;height:15 value=04>conjunto residencial
 
 <input type=radio name=radiosector id=radiosector style=font-family:Verdana;font-size:8;height:15 value=05>barrio
 
 <input type=radio name=radiosector id=radiosector style=font-family:Verdana;font-size:8;height:15 value=06>caserio
 
 </td> 
 </tr> 
 <tr> 
 <td class=letrasSmall colspan=2 width=50%><input type=text name=local.descripcion maxlength=100 size=60 value style=font-family:Verdana;font-size:10;width:100%;height:18 id=local.descripcion> </td> 
 <td class=letrasSmall colspan=2 width=50%><input type=text name=sector.descripcion maxlength=100 size=60 value style=font-family:Verdana;font-size:10;width:100%;height:18 id=sector.descripcion></td> 
 </tr> 
 
 
 <tr>
 <td class=tablaSubTitulo colspan=2 width=50% style=FONT-SIZE:7pt;HEIGHT:20px>Estado </td>
 <td class=tablaSubTitulo colspan=2 width=50% style=FONT-SIZE:7pt;HEIGHT:20px>Municipio </td>
 </tr>
 <tr> 
 <td class=letrasSmall colspan=2 width=50%>
 <select name=estado.codigo style=font-family:Verdana;font-size:10;width:100%;height:18 id=estado.codigo><option value selected>SELECCIONAR</option>
                  <option value=02>AMAZONAS</option>
<option value=03>ANZOATEGUI</option>
<option value=04>APURE</option>
<option value=05>ARAGUA</option>
<option value=06>BARINAS</option>
<option value=07>BOLIVAR</option>
<option value=08>CARABOBO</option>
<option value=09>COJEDES</option>
<option value=10>DELTA AMACURO</option>
<option value=25>DEPENDENCIAS FEDERALES</option>
<option value=01>DISTRITO CAPITAL</option>
<option value=11>FALCON</option>
<option value=12>GUARICO</option>
<option value=24>LA GUAIRA</option>
<option value=13>LARA</option>
<option value=15>MIRANDA</option>
<option value=16>MONAGAS</option>
<option value=14>MÉRIDA</option>
<option value=17>NUEVA ESPARTA</option>
<option value=18>PORTUGUESA</option>
<option value=19>SUCRE</option>
<option value=20>TACHIRA</option>
<option value=21>TRUJILLO</option>
<option value=22>YARACUY</option>
<option value=23>ZULIA</select> 
 </td>
 <td class=letrasSmall colspan=2 width=50%>
 <select name=municipio.codigo style=font-family:Verdana;font-size:10;width:100%;height:18 id=municipio.codigo></select> 
 </td>
 </tr>
 <tr>
 <td class=tablaSubTitulo colspan=2 width=50% style=FONT-SIZE:7pt;HEIGHT:20px>Parroquia </td>
 <td class=tablaSubTitulo colspan=2 width=50% style=FONT-SIZE:7pt;HEIGHT:20px>Ciudad </td>
 </tr>
 <tr>
 <td class=letrasSmall colspan=2 width=50%>
 <select name=parroquia.codigo style=font-family:Verdana;font-size:10;width:100%;height:18 id=parroquia.codigo></select> 
 </td>
 <td class=letrasSmall colspan=2 width=50%>
 <select name=ciudad.codigo style=font-family:Verdana;font-size:10;width:100%;height:18 id=ciudad.codigo></select> 
 </td> 
 </tr> 
 <tr> 
 <td class=tablaSubTitulo colspan=1 width=25% style=FONT-SIZE:7pt;HEIGHT:20px>Teléfono Fijo Ej: 0212-1234567 </td> 
 <td class=tablaSubTitulo colspan=1 width=25% style=FONT-SIZE:7pt;HEIGHT:20px>Teléfono Celular Ej: 0416-1234567 </td> 
 <td class=tablaSubTitulo colspan=1 width=25% style=FONT-SIZE:7pt;HEIGHT:20px>Fax Ej: 0212-1234567 </td>
 <td class=tablaSubTitulo colspan=1 width=25% style=FONT-SIZE:7pt;HEIGHT:20px>Zona Postal </td>
 
 </tr> 
 <tr> 
 <td class=letrasSmall colspan=1 width=25%><input type=text name=telefono maxlength=12 size=60 value style=font-family:Verdana;font-size:10;width:100%;height:18></td> 
 <td class=letrasSmall colspan=1 width=25%><input type=text name=telefonoCelular maxlength=12 size=60 value style=font-family:Verdana;font-size:10;width:100%;height:18></td> 
 <td class=letrasSmall colspan=1 width=25%><input type=text name=fax maxlength=12 size=60 value style=font-family:Verdana;font-size:10;width:100%;height:18></td> 
 <td class=letrasSmall colspan=1 width=25%>
 <select name=zonaPostal style=font-family:Verdana;font-size:10;width:100%;height:18 id=zonaPostal></select> 
 </td> 
 </tr>
 <tr>
 <td class=tablaSubTitulo colspan=4 width=100% style=FONT-SIZE:7pt;HEIGHT:20px>Punto de Referencia </td>
 </tr>
 <tr>
 <td class=letrasSmall colspan=4 width=100%><input type=text name=referencia maxlength=50 size=60 value style=font-family:Verdana;font-size:10;width:100%;height:18 id=referencia></td> 
 </tr>
 </table> 
 <br>
 <table id=tblPrincipal width=95% align=center cellpadding=2 cellspacing=0 border=0> 
 <tbody><tr>
 <td width=50% align=right><input type=reset name=reestablecer value=Reestablecer class=boton id=reestablecer></td> 
 <td width=50%><input type=submit name=guardar value=Guardar class=boton id=guardar></td> 
 </tr> 
 </table> 
 </form>
 </td>
 </tr>
</table> 
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
<script src="<?= asset('js/simulator/legacy/direcciones_sim.js') ?>"></script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../../layouts/logged_layout.php';
?>
