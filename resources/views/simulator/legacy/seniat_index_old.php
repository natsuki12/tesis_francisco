<?php
declare(strict_types=1);

$pageTitle = 'Portal SENIAT — Simulador';
$activePage = 'simulador';

ob_start();
?>

<!-- ============================================================
     CSS SENIAT encapsulado bajo .seniat-scope
     Evita que los estilos del layout afecten al contenido SENIAT
     y viceversa.
     ============================================================ -->
<link rel="stylesheet" href="<?= asset('css/simulator/legacy/seniat_index_old.css') ?>">

<!-- ============================================================
     Contenido SENIAT encapsulado
     ============================================================ -->
<div class="seniat-wrapper">
  <div class="seniat-scope">

    <noscript>
      <div>This page requires JavaScript</div>
    </noscript>
    <!--  <iframe id="portalIFrame" title="" frameborder="0" width="0" height="0" src="../../../images/pobtrans.gif"></iframe>
  <script async="" src="https://www.google-analytics.com/analytics.js"></script>
  <script type="text/javascript" src="../../pls/portal/PORTAL.wwsbr_javascriptc2e1.js?p_language=us&amp;p_version=10.1.4.1.0.113">
  </script>
-->
    <table border="0" cellpadding="0" cellspacing="0" summary="" width="100%">
      <tbody>
        <tr>
          <td style="width:25%" valign="top">
            <table border="0" cellpadding="0" cellspacing="0" id="rg2040" style="height:629px" summary="" width="100%">
            </table>
          </td>
          <td style="width:1030px" valign="top">
            <table border="0" cellpadding="0" cellspacing="0" id="rg1549" style="height:58px" summary="" width="100%">
              <tbody>
                <tr>
                  <td style="padding:0px 0px 0px 0px;width:100%;" valign="top">
                    <div id="p0_17344_78_1_1">
                      <table border="0" cellpadding="0" cellspacing="0" class="RegionNoBorder" width="100%">
                        <tbody>
                          <tr>
                            <td class="RegionHeaderColor" style="width:100%">
                              <div id="pcnt0_17344_78_1_1">
                                <!-- <script language="JavaScript1.2" src="./PORTAL_SENIAT_files/funciones.js.descarga"></script> -->
                                <!-- <center> -->
                                <table width="100%">
                                  <tbody>
                                    <tr>
                                      <td>
                                        <img height="55" src="<?= asset('img/seniat-index-viejo/36f8a1929520.jpg') ?>"
                                          width="100%" />
                                      </td>
                                      <!--
                                    <td>
                                      <img src="Cintillo_MPPS-BATALLA-NAVAL-2.png" width="100%" height="55">
                                    </td> -->
                                      <!--                                     <td >
                                          <img src="batalla_de_carabobo200-03.png" width="100%" height="55">
                                    </td> -->
                                    </tr>
                                  </tbody>
                                </table>
                                <!-- </center> -->
                              </div>
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
            <table border="0" cellpadding="0" cellspacing="0" summary="" width="100%">
              <tbody>
                <tr>
                  <td style="width:250px" valign="top">
                    <table border="0" cellpadding="0" cellspacing="0" id="rg2059" style="height:189px" summary=""
                      width="100%">
                      <tbody>
                        <tr align="LEFT">
                          <td style="padding:0px 0px 0px 0px;width:100%;" valign="top">
                            <div id="p78_3595354_78_1_1">
                              <table border="0" cellpadding="0" cellspacing="0" class="RegionNoBorder" width="100%">
                                <tbody>
                                  <tr>
                                    <td class="RegionHeaderColor" style="width:100%">
                                      <div id="pcnt78_3595354_78_1_1">
                                        <script>
                                          function login() {
                                            window.open("http://contribuyente.seniat.gob.ve/iseniatlogin/contribuyente.do", "loginWindow", "width=380,height=500,scrollbars=yes,statusbar=no");
                                          }

                                          function loginvista() {
                                            (function (i, s, o, g, r, a, m) {
                                              i['GoogleAnalyticsObject'] = r; i[r] = i[r] || function () {
                                                (i[r].q = i[r].q || []).push(arguments)
                                              }, i[r].l = 1 * new Date(); a = s.createElement(o),
                                                m = s.getElementsByTagName(o)[0]; a.async = 1; a.src = g; m.parentNode.insertBefore(a, m)
                                            })(window, document, 'script', 'https://www.google-analytics.com/analytics.js', 'ga');

                                            ga('create', 'UA-89890525-1', 'auto');
                                            ga('send', 'pageview');

                                          }

                                          function loginJur() {
                                            window.open("http://contribuyente.seniat.gob.ve/iseniatlogin/juridico.do", "loginWindow", "width=380,height=500,scrollbars=yes,statusbar=no");
                                          }

                                          function loginJurvista() {
                                            (function (i, s, o, g, r, a, m) {
                                              i['GoogleAnalyticsObject'] = r; i[r] = i[r] || function () {
                                                (i[r].q = i[r].q || []).push(arguments)
                                              }, i[r].l = 1 * new Date(); a = s.createElement(o),
                                                m = s.getElementsByTagName(o)[0]; a.async = 1; a.src = g; m.parentNode.insertBefore(a, m)
                                            })(window, document, 'script', 'https://www.google-analytics.com/analytics.js', 'ga');

                                            ga('create', 'UA-89924722-1', 'auto');
                                            ga('send', 'pageview');

                                          }
                                        </script>
                                        <table align="center" cellpadding="0" cellspacing="0" width="203">
                                          <tbody>
                                            <tr valign="top">
                                              <td align="center" colspan="2">
                                                <a href="#">
                                                  <!-- <img src="../../../seniat/images/logocr.gif" border="0"> -->
                                                  <!-- <img src="logocr.jpg" border="0" width="207" height="73"> -->
                                                  <img border="0" height="73"
                                                    src="<?= asset('img/seniat-index-viejo/d2c6fda393db.png') ?>"
                                                    width="207" />
                                                </a>
                                              </td>
                                            </tr>
                                            <tr>
                                              <td height="5">
                                              </td>
                                            </tr>
                                            <tr>
                                              <td align="center" colspan="2">
                                                <img border="0"
                                                  src="<?= asset('img/seniat-index-viejo/457c8006115e.gif') ?>" />
                                              </td>
                                            </tr>
                                            <tr>
                                              <td height="3">
                                              </td>
                                            </tr>
                                            <tr>
                                              <td align="center" colspan="2">
                                                <a href="#">
                                                  <img border="0"
                                                    src="<?= asset('img/seniat-index-viejo/8f33ca7879fb.gif') ?>" />
                                                </a>
                                              </td>
                                            </tr>
                                            <tr>
                                              <td height="3">
                                              </td>
                                            </tr>
                                            <tr>
                                              <td align="center" colspan="2">
                                                <a href="#">
                                                  <img border="0"
                                                    src="<?= asset('img/seniat-index-viejo/6b5aeaee6757.gif') ?>" />
                                                </a>
                                              </td>
                                            </tr>
                                            <tr>
                                              <td align="center" colspan="2" height="5">
                                              </td>
                                            </tr>
                                          </tbody>
                                        </table>
                                      </div>
                                    </td>
                                  </tr>
                                </tbody>
                              </table>
                            </div>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </td>
                  <td style="width:780px" valign="top">
                    <table border="0" cellpadding="0" cellspacing="0" id="rg1564" style="height:189px" summary=""
                      width="100%">
                      <tbody>
                        <tr align="LEFT">
                          <td style="padding:0px 0px 0px 0px;width:100%;" valign="top">
                            <div id="p78_9721162_78_1_1">
                              <table border="0" cellpadding="0" cellspacing="0" class="RegionNoBorder" width="100%">
                                <tbody>
                                  <tr>
                                    <td class="RegionHeaderColor" style="width:100%">
                                      <div id="pcnt78_9721162_78_1_1">
                                        <meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
                                        <title>
                                        </title>
                                        <table border="0" cellpadding="0" cellspacing="0" width="725">
                                          <tbody>
                                            <tr>
                                              <td height="190" width="725">
                                                <!--
                                            <SCRIPT LANGUAGE="JavaScript">
                                              var banner = 1;
                                              var now = new Date()
                                              var sec = now.getSeconds()
                                              var ad = sec % banner;
                                              ad +=1;
                                              if (ad==1) {
                                                url="http://www.fitven.gob.ve";
                                                alt="";
                                                banner="MANEJADOR_CONTENIDO_SENIAT/03TRIBUTOS/3.0NOTICIAS_TRIBUTOS/Banner-07-03-2019.jpg";
                                                name="banner"
                                                width="725";
                                                height="190";
                                              }
                                              document.write('<center>');
                                              document.write('<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,0,0" width="729" height="190" id=\"' + name + '\" align="middle">');
                                              document.write('<param name="allowScriptAccess" value="sameDomain" />');
                                              document.write('<param name="movie" value=\"' + banner + '\" />');
                                              document.write('<param name="quality" value="high" />');
                                              document.write('<embed src=\"' + banner + '\" quality="high" bgcolor="#ffffff" width="729" height="190" name=\"' + name + '\" align="middle" allowscriptaccess="sameDomain" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />');
                                              document.write('</object>');
                                              document.write('</center>');
                                            </SCRIPT>
                                          -->
                                                <!-- <img src="banner.jpg" width="725" height="190"> -->
                                                <img height="190"
                                                  src="<?= asset('img/seniat-index-viejo/21a64b195cd9.png') ?>"
                                                  width="725" />
                                              </td>
                                            </tr>
                                          </tbody>
                                        </table>
                                      </div>
                                    </td>
                                  </tr>
                                </tbody>
                              </table>
                            </div>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </td>
                </tr>
              </tbody>
            </table>
            <table border="0" cellpadding="0" cellspacing="0" id="rg1563" style="height:21px" summary="" width="100%">
              <tbody>
                <tr align="LEFT">
                  <td style="padding:0px 0px 0px 0px;width:100%;" valign="top">
                    <div id="p78_21645_78_1_1">
                      <table border="0" cellpadding="0" cellspacing="0" class="RegionNoBorder" width="100%">
                        <tbody>
                          <tr>
                            <td class="RegionHeaderColor" style="width:100%">
                              <div id="pcnt78_21645_78_1_1">
                                <style type="text/css">
                                  #dropmenudiv {
                                    position: absolute;
                                    border: 1px solid #EE3822;
                                    border-bottom-width: 0;
                                    font: normal 9px Verdana;
                                    font-color: red;
                                    color: red;
                                    line-height: 18px;
                                    z-index: 100;
                                  }

                                  #dropmenudiv a {
                                    width: 100%;
                                    display: block;
                                    text-indent: 3px;
                                    border-bottom: 1px solid red;
                                    padding: 1px 0;
                                    text-decoration: none;
                                    font-weight: bold;
                                    color: #EE3822;
                                  }

                                  #dropmenudiv a:hover {
                                    /*hover background color*/
                                    background-color: #CCCCCC;
                                    color: white;
                                  }
                                </style>
                                <script>
                                  function abrirRif(ventana) {
                                    window.open(ventana,
                                      'NuevaVentana',
                                      'menubar=no,scrollbars=yes,' + 'resizable=no,' +
                                      'width=350,height=395');
                                  }
                                </script>
                                <script>
                                  function abrirCertificado(ventana) {
                                    window.open(ventana,
                                      'NuevaVentana',
                                      'menubar=no,scrollbars=yes,' + 'resizable=no,width=300,' +
                                      'height=130');
                                  }

                                  function abrirRifvista() {

                                    (function (i, s, o, g, r, a, m) {
                                      i['GoogleAnalyticsObject'] = r; i[r] = i[r] || function () {
                                        (i[r].q = i[r].q || []).push(arguments)
                                      }, i[r].l = 1 * new Date(); a = s.createElement(o),
                                        m = s.getElementsByTagName(o)[0]; a.async = 1; a.src = g; m.parentNode.insertBefore(a, m)
                                    })(window, document, 'script', 'https://www.google-analytics.com/analytics.js', 'ga');

                                    ga('create', 'UA-90117244-1', 'auto');
                                    ga('send', 'pageview');


                                  }

                                  function abrirCertificadovista() {

                                    (function (i, s, o, g, r, a, m) {
                                      i['GoogleAnalyticsObject'] = r; i[r] = i[r] || function () {
                                        (i[r].q = i[r].q || []).push(arguments)
                                      }, i[r].l = 1 * new Date(); a = s.createElement(o),
                                        m = s.getElementsByTagName(o)[0]; a.async = 1; a.src = g; m.parentNode.insertBefore(a, m)
                                    })(window, document, 'script', 'https://www.google-analytics.com/analytics.js', 'ga');

                                    ga('create', 'UA-90097838-1', 'auto');
                                    ga('send', 'pageview');



                                  }

                                  function consultarCertificadovista() {

                                    (function (i, s, o, g, r, a, m) {
                                      i['GoogleAnalyticsObject'] = r; i[r] = i[r] || function () {
                                        (i[r].q = i[r].q || []).push(arguments)
                                      }, i[r].l = 1 * new Date(); a = s.createElement(o),
                                        m = s.getElementsByTagName(o)[0]; a.async = 1; a.src = g; m.parentNode.insertBefore(a, m)
                                    })(window, document, 'script', 'https://www.google-analytics.com/analytics.js', 'ga');

                                    ga('create', 'UA-90126240-1', 'auto');
                                    ga('send', 'pageview');



                                  }
                                </script>
                                <script type="text/javascript">
                                  var menu1 = new Array()
                                  menu1[0] = '<a href="#">Orientación Tributaria y Asistencia Técnica</a>'
                                  menu1[1] = '<a href="#">Orientación General sobre Trámites</a>'
                                  menu1[2] = '<a href="#">Denuncias</a>'
                                  menu1[3] = '<a href="#">Información de interés</a>'
                                  menu1[4] = '<a href="#">Defensoría del Contribuyente</a>'

                                  var menu2 = new Array()
                                  menu2[0] = '<a href="<?= base_url('/simulador/inscripcion-rif') ?>">Inscripción de RIF</a>'
                                  menu2[1] = '<a href="<?= base_url('/simulador/consulta-rif') ?>">Consulta de RIF</a>'
                                  menu2[2] = '<a href="#">Consulta Comprobante Digital de RIF</a>'
                                  menu2[3] = '<a href="#">Consulta Certificados</a>'
                                  menu2[4] = '<a href="#" target="_blank">Retención IVA<br> (Prueba Carga de archivo)</a>'
                                  menu2[5] = '<a href="#" target="_blank">Retención IVA Proveedor<br> (Prueba Carga de archivo)</a>'


                                  var menu3 = new Array()
                                  menu3[0] = '<a href="#">Constitución Nacional</a>'
                                  menu3[1] = '<a href="#">Codigo Orgánico Tributario</a>'
                                  menu3[2] = '<a href="#">Convenios</a>'
                                  menu3[3] = '<a href="#">Tributos Internos</a>'
                                  menu3[4] = '<a href="#">Aduanas</a>'
                                  menu3[5] = '<a href="#">Criterios Jurídicos</a>'
                                  menu3[6] = '<a href="#">Otras Normas de Interés</a>'

                                  var menu5 = new Array()
                                  menu5[0] = '<a href="#">Carteles de Remate</a>'
                                  menu5[1] = '<a href="#">Notificaciones</a>'

                                  var menu8 = new Array()
                                  menu8[0] = '<a href="<?= base_url('/simulador/inscripcion-rif') ?>">Inscripcion de RIF.</a>'
                                  menu8[1] = '<a href="<?= base_url('/simulador/consulta-rif') ?>">Consulta de RIF.</a>'
                                  var menuwidth = '300px'
                                  var menubgcolor = 'white'
                                  var disappeardelay = 250
                                  var hidemenu_onclick = "yes"

                                  var ie4 = document.all
                                  var ns6 = document.getElementById && !document.all
                                  if (ie4 || ns6)
                                    document.write('<div id="dropmenudiv" style="visibility:hidden;width:' + menuwidth + ';background-color:' + menubgcolor + '" onMouseover="clearhidemenu()" onMouseout="dynamichide(event)"></div>')
                                  function getposOffset(what, offsettype) {
                                    var totaloffset = (offsettype == "left") ? what.offsetLeft : what.offsetTop;
                                    var parentEl = what.offsetParent;
                                    while (parentEl != null) {
                                      totaloffset = (offsettype == "left") ? totaloffset + parentEl.offsetLeft : totaloffset + parentEl.offsetTop;
                                      parentEl = parentEl.offsetParent;
                                    }
                                    return totaloffset;
                                  }
                                  function showhide(obj, e, visible, hidden, menuwidth) {
                                    if (ie4 || ns6)
                                      dropmenuobj.style.left = dropmenuobj.style.top = "-500px"
                                    if (menuwidth != "") {
                                      dropmenuobj.widthobj = dropmenuobj.style
                                      dropmenuobj.widthobj.width = menuwidth
                                    }
                                    if (e.type == "click" && obj.visibility == hidden || e.type == "mouseover")
                                      obj.visibility = visible
                                    else if (e.type == "click")
                                      obj.visibility = hidden
                                  }
                                  function iecompattest() {
                                    return (document.compatMode && document.compatMode != "BackCompat") ? document.documentElement : document.body
                                  }
                                  function clearbrowseredge(obj, whichedge) {
                                    var edgeoffset = 0
                                    if (whichedge == "rightedge") {
                                      var windowedge = ie4 && !window.opera ? iecompattest().scrollLeft + iecompattest().clientWidth - 15 : window.pageXOffset + window.innerWidth - 15
                                      dropmenuobj.contentmeasure = dropmenuobj.offsetWidth
                                      if (windowedge - dropmenuobj.x < dropmenuobj.contentmeasure)
                                        edgeoffset = dropmenuobj.contentmeasure - obj.offsetWidth
                                    }
                                    else {
                                      var topedge = ie4 && !window.opera ? iecompattest().scrollTop : window.pageYOffset
                                      var windowedge = ie4 && !window.opera ? iecompattest().scrollTop + iecompattest().clientHeight - 15 : window.pageYOffset + window.innerHeight - 18
                                      dropmenuobj.contentmeasure = dropmenuobj.offsetHeight
                                      if (windowedge - dropmenuobj.y < dropmenuobj.contentmeasure) {
                                        edgeoffset = dropmenuobj.contentmeasure + obj.offsetHeight
                                        if ((dropmenuobj.y - topedge) < dropmenuobj.contentmeasure)
                                          edgeoffset = dropmenuobj.y + obj.offsetHeight - topedge
                                      }
                                    }
                                    return edgeoffset
                                  }
                                  function populatemenu(what) {
                                    if (ie4 || ns6)
                                      dropmenuobj.innerHTML = what.join("")
                                  }
                                  function dropdownmenu(obj, e, menucontents, menuwidth) {
                                    if (window.event) event.cancelBubble = true
                                    else if (e.stopPropagation) e.stopPropagation()
                                    clearhidemenu()
                                    dropmenuobj = document.getElementById ? document.getElementById("dropmenudiv") : dropmenudiv
                                    populatemenu(menucontents)
                                    if (ie4 || ns6) {
                                      showhide(dropmenuobj.style, e, "visible", "hidden", menuwidth)
                                      dropmenuobj.x = getposOffset(obj, "left")
                                      dropmenuobj.y = getposOffset(obj, "top")
                                      dropmenuobj.style.left = dropmenuobj.x - clearbrowseredge(obj, "rightedge") + "px"
                                      dropmenuobj.style.top = dropmenuobj.y - clearbrowseredge(obj, "bottomedge") + obj.offsetHeight + "px"
                                    }
                                    return clickreturnvalue()
                                  }
                                  function clickreturnvalue() {
                                    if (ie4 || ns6) return false
                                    else return true
                                  }
                                  function contains_ns6(a, b) {
                                    while (b.parentNode)
                                      if ((b = b.parentNode) == a)
                                        return true;
                                    return false;
                                  }
                                  function dynamichide(e) {
                                    if (ie4 && !dropmenuobj.contains(e.toElement))
                                      delayhidemenu()
                                    else if (ns6 && e.currentTarget != e.relatedTarget && !contains_ns6(e.currentTarget, e.relatedTarget))
                                      delayhidemenu()
                                  }
                                  function hidemenu(e) {
                                    if (typeof dropmenuobj != "undefined") {
                                      if (ie4 || ns6)
                                        dropmenuobj.style.visibility = "hidden"
                                    }
                                  }
                                  function delayhidemenu() {
                                    if (ie4 || ns6)
                                      delayhide = setTimeout("hidemenu()", disappeardelay)
                                  }
                                  function clearhidemenu() {
                                    if (typeof delayhide != "undefined")
                                      clearTimeout(delayhide)
                                  }
                                  if (hidemenu_onclick == "yes")
                                    document.onclick = hidemenu
                                </script>
                                <div id="dropmenudiv" onmouseout="dynamichide(event)" onmouseover="clearhidemenu()"
                                  style="visibility: hidden; width: 210px; background-color: white; top: -500px; left: -500px;">
                                  <a href="<?= base_url('/simulador/inscripcion-rif') ?>">Inscripción de RIF
                                  </a>
                                  <a href="<?= base_url('/simulador/consulta-rif') ?>">Consulta de RIF
                                  </a>
                                  <a href="#">Consulta Comprobante Digital de RIF
                                  </a>
                                  <a href="#">Consulta Certificados</a><a href="#" rel="noopener"
                                    target="_blank">Retención IVA<br /> (Prueba Carga de archivo)
                                  </a>
                                  <a href="#" rel="noopener" target="_blank">Retención IVA Proveedor
                                    <br /> (Prueba Carga de archivo)
                                  </a>
                                </div>
                                <center>
                                  <table border="0" height="21" width="942">
                                    <tbody>
                                      <tr>
                                        <td width="111">
                                          <a href="#">
                                            <img border="0"
                                              src="<?= asset('img/seniat-index-viejo/c42251336969.gif') ?>"
                                              usemap="#aistencia" />
                                          </a>
                                        </td>
                                        <td width="110">
                                          <a href="#" onclick="return clickreturnvalue()" onmouseout="delayhidemenu()"
                                            onmouseover="dropdownmenu(this, event, menu2, '210px')">
                                            <img border="0"
                                              src="<?= asset('img/seniat-index-viejo/28dec1f51bd1.gif') ?>"
                                              usemap="#sistemaslinea" />
                                          </a>
                                        </td>
                                        <td width="113">
                                          <a href="#">
                                            <img border="0"
                                              src="<?= asset('img/seniat-index-viejo/b7657e917e99.gif') ?>"
                                              usemap="#normativa" />
                                          </a>
                                        </td>
                                        <td width="112">
                                          <a href="#">
                                            <img border="0"
                                              src="<?= asset('img/seniat-index-viejo/61934b6fc513.gif') ?>"
                                              usemap="#educacion" />
                                          </a>
                                        </td>
                                        <td width="109">
                                          <a href="#">
                                            <img border="0"
                                              src="<?= asset('img/seniat-index-viejo/ac79e2a2f6d7.gif') ?>"
                                              usemap="carteles" />
                                          </a>
                                        </td>
                                        <td width="110">
                                          <a href="#">
                                            <img border="0"
                                              src="<?= asset('img/seniat-index-viejo/05fcf2a6f27d.gif') ?>"
                                              usemap="#estadisticas" />
                                          </a>
                                        </td>
                                        <td width="115">
                                          <a href="#">
                                            <img border="0"
                                              src="<?= asset('img/seniat-index-viejo/3287b995b426.gif') ?>"
                                              usemap="#enlaces" />
                                          </a>
                                        </td>
                                        <td width="110">
                                          <a href="#">
                                            <img border="0"
                                              src="<?= asset('img/seniat-index-viejo/ab61986c9318.gif') ?>"
                                              usemap="#ayuda" />
                                          </a>
                                        </td>
                                      </tr>
                                    </tbody>
                                  </table>
                                </center>
                              </div>
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
            <!--TABlA INFERIOR-->
            <table border="0" border-color="red" cellpadding="0" cellspacing="0" summary="" width="100%">
              <tbody>
                <tr>
                  <!--ADUANAS-->
                  <td style="width:350px" valign="top">
                    <table border="0" cellpadding="0" cellspacing="0" id="rg1565" summary="" width="100%">
                      <tbody>
                        <tr align="LEFT">
                          <td valign="top">
                            <div id="p78_17357_78_1_1">
                              <table border="0" cellpadding="0" cellspacing="0" class="RegionNoBorder">
                                <tbody>
                                  <tr>
                                    <td class="RegionHeaderColor">
                                      <div id="pcnt78_17357_78_1_1">
                                        <table align="center" cellpadding="0" cellspacing="0">
                                          <tbody>
                                            <!--ADUANAS IMAGEN-->
                                            <tr valign="top">
                                              <td align="center">
                                                <a href="#">
                                                  <img src="<?= asset('img/seniat-index-viejo/fac281837eef.gif') ?>" />
                                                </a>
                                              </td>
                                            </tr>
                                            <!--ADUANAS CONTENIDOS-->
                                            <tr bordercolor="#CC666666">
                                              <td align="center" bordercolor="#FF0000">
                                                <table border="1" bordercolor="#FF0000"
                                                  style="border-collapse: collapse">
                                                  <tbody>
                                                    <tr>
                                                      <td bordercolor="#FFFFFF" valign="top">
                                                        <table border="0" height="507">
                                                          <tbody>
                                                            <!-- 13-10-2023 -->
                                                            <!--<tr>
                                                            <td>
                                                              <font color="#0000" face="Arial, Helvetica, sans-serif" size="1" style="text-decoration: none">
                                                                <img border="0" src="https://declaraciones.seniat.gob.ve/portal/page/portal/MANEJADOR_CONTENIDO_SENIAT/01NOTICIAS/00IMAGENES/nuevo.gif">
                                                                <a href="#" target="_blank" style="text-decoration:none">
                                                                  <font color="#000000" face="Arial, Helvetica, sans-serif" size="1">
                                                                    <span style="text-decoration: none; font-weight: 400">AVISO AUXILIARES ADUANA SANTA ELENA DE UAIREN
                                                                    </span>
                                                                  </font>
                                                                </a>
                                                              </font>
                                                            </td>
                                                          </tr>
                                                          <tr>-->
                                                            <!-- MOD. HOY -->
                                                            <tr>
                                                              <td>
                                                                <font color="#0000" face="Arial, Helvetica, sans-serif"
                                                                  size="1" style="text-decoration: none">
                                                                  <strong>
                                                                    <img border="0"
                                                                      src="<?= asset('img/seniat-index-viejo/13c6fce9f33f.gif') ?>" />
                                                                    <a href="#" style="text-decoration:none"
                                                                      target="_blank">
                                                                      <font color="#000000"
                                                                        face="Arial, Helvetica, sans-serif" size="1">
                                                                        <span
                                                                          style="text-decoration: none; font-weight: 400">Instrucciones
                                                                          beneficios en el Arancel de Aduanas Decreto
                                                                          4.944
                                                                        </span>
                                                                      </font>
                                                                    </a>
                                                                  </strong>
                                                                </font>
                                                              </td>
                                                            </tr>
                                                            <tr>
                                                              <td>
                                                                <font color="#0000" face="Arial, Helvetica, sans-serif"
                                                                  size="1" style="text-decoration: none">
                                                                  <strong>
                                                                    <img border="0"
                                                                      src="<?= asset('img/seniat-index-viejo/13c6fce9f33f.gif') ?>" />
                                                                    <a href="#" style="text-decoration:none"
                                                                      target="_blank">
                                                                      <font color="#000000"
                                                                        face="Arial, Helvetica, sans-serif" size="1">
                                                                        <span
                                                                          style="text-decoration: none; font-weight: 400">Instrucciones
                                                                          Decretos de exoneración 5.196 y 5.197
                                                                        </span>
                                                                      </font>
                                                                    </a>
                                                                  </strong>
                                                                </font>
                                                              </td>
                                                            </tr>
                                                            <tr>
                                                              <td>
                                                                <font color="#0000" face="Arial, Helvetica, sans-serif"
                                                                  size="1" style="text-decoration: none">
                                                                  <strong>
                                                                    <img border="0"
                                                                      src="<?= asset('img/seniat-index-viejo/13c6fce9f33f.gif') ?>" />
                                                                    <a href="#" style="text-decoration:none"
                                                                      target="_blank">
                                                                      <font color="#000000"
                                                                        face="Arial, Helvetica, sans-serif" size="1">
                                                                        <span
                                                                          style="text-decoration: none; font-weight: 400">Decreto
                                                                          de exoneración GORBV 6952
                                                                        </span>
                                                                      </font>
                                                                    </a>
                                                                  </strong>
                                                                </font>
                                                              </td>
                                                            </tr>
                                                            <tr>
                                                              <td>
                                                                <font color="#0000" face="Arial, Helvetica, sans-serif"
                                                                  size="1" style="text-decoration: none">
                                                                  <strong>
                                                                    <img border="0"
                                                                      src="<?= asset('img/seniat-index-viejo/13c6fce9f33f.gif') ?>" />
                                                                    <a href="#" style="text-decoration:none"
                                                                      target="_blank">
                                                                      <font color="#000000"
                                                                        face="Arial, Helvetica, sans-serif" size="1">
                                                                        <span
                                                                          style="text-decoration: none; font-weight: 400">AVISO
                                                                          SIDUNEA WORLD ADUANAS PRINCIPALES GUAMACHE Y
                                                                          SUS
                                                                          SUBALTERNAS , PUERTO SUCRE, GUIRIA, CARUPANO Y
                                                                          CIUDAD GUAYANA.
                                                                        </span>
                                                                      </font>
                                                                    </a>
                                                                  </strong>
                                                                </font>
                                                              </td>
                                                            </tr>
                                                            <!--                                                           
                                                        <tr>
                                                            <td>
                                                              <font color="#0000" face="Arial, Helvetica, sans-serif" size="1" style="text-decoration: none">
                                                                
                                                                <strong>
                                                                  <img border="0" src="https://declaraciones.seniat.gob.ve/portal/page/portal/MANEJADOR_CONTENIDO_SENIAT/01NOTICIAS/00IMAGENES/nuevo.gif"> 
                                                                  <a href="#" target="_blank" style="text-decoration:none">
                                                                    <font color="#000000" face="Arial, Helvetica, sans-serif" size="1">
                                                                      <span style="text-decoration: none; font-weight: 400">Instrucciones Decreto exoneración 5.104
                                                                      </span>
                                                                    </font>
                                                                  </a>
                                                                </strong>
                                                              </font>
                                                            </td>
                                                          </tr>

                                                        <tr>
                                                            <td>
                                                              <font color="#0000" face="Arial, Helvetica, sans-serif" size="1" style="text-decoration: none">
                                                                
                                                                <strong>
                                                                  <img border="0" src="https://declaraciones.seniat.gob.ve/portal/page/portal/MANEJADOR_CONTENIDO_SENIAT/01NOTICIAS/00IMAGENES/nuevo.gif"> 
                                                                  <a href="#" target="_blank" style="text-decoration:none">
                                                                    <font color="#000000" face="Arial, Helvetica, sans-serif" size="1">
                                                                      <span style="text-decoration: none; font-weight: 400">Decreto de exoneración 5.104
                                                                      </span>
                                                                    </font>
                                                                  </a>
                                                                </strong>
                                                              </font>
                                                            </td>
                                                          </tr>





                                                        <tr>
                                                            <td>
                                                              <font color="#0000" face="Arial, Helvetica, sans-serif" size="1" style="text-decoration: none">
                                                                
                                                                <strong>
                                                                   <img border="0" src="https://declaraciones.seniat.gob.ve/portal/page/portal/MANEJADOR_CONTENIDO_SENIAT/01NOTICIAS/00IMAGENES/nuevo.gif">
                                                                  <a href="#" target="_blank" style="text-decoration:none">
                                                                    <font color="#000000" face="Arial, Helvetica, sans-serif" size="1">
                                                                      <span style="text-decoration: none; font-weight: 400">Gaceta Oficial N° 43.044, Decreto de exóneración N° 5.079
                                                                      </span>
                                                                    </font>
                                                                  </a>
                                                                </strong>
                                                              </font>
                                                            </td>
                                                          </tr>

                                                        <tr>
                                                            <td>
                                                              <font color="#0000" face="Arial, Helvetica, sans-serif" size="1" style="text-decoration: none">
                                                                
                                                                <strong>
                                                                   
                                                                  <a href="#" target="_blank" style="text-decoration:none">
                                                                    <font color="#000000" face="Arial, Helvetica, sans-serif" size="1">
                                                                      <span style="text-decoration: none; font-weight: 400">Aviso web Decreto 5071
                                                                      </span>
                                                                    </font>
                                                                  </a>
                                                                </strong>
                                                              </font>
                                                            </td>
                                                          </tr>

                                                        <tr>
                                                            <td>
                                                              <font color="#0000" face="Arial, Helvetica, sans-serif" size="1" style="text-decoration: none">
                                                                
                                                                <strong>
                                                                   
                                                                  <a href="#" target="_blank" style="text-decoration:none">
                                                                    <font color="#000000" face="Arial, Helvetica, sans-serif" size="1">
                                                                      <span style="text-decoration: none; font-weight: 400">Decreto de exoneración 5071
                                                                      </span>
                                                                    </font>
                                                                  </a>
                                                                </strong>
                                                              </font>
                                                            </td>
                                                          </tr>
-->
                                                            <tr>
                                                              <td>
                                                                <font color="#0000" face="Arial, Helvetica, sans-serif"
                                                                  size="1" style="text-decoration: none">
                                                                  <strong>
                                                                    <a href="#" style="text-decoration:none"
                                                                      target="_blank">
                                                                      <font color="#000000"
                                                                        face="Arial, Helvetica, sans-serif" size="1">
                                                                        <span
                                                                          style="text-decoration: none; font-weight: 400">Informativo
                                                                          SIDUNEA WORLD Versión 4.2.2
                                                                        </span>
                                                                      </font>
                                                                    </a>
                                                                  </strong>
                                                                </font>
                                                              </td>
                                                            </tr>
                                                            <tr>
                                                              <td>
                                                                <font color="#0000" face="Arial, Helvetica, sans-serif"
                                                                  size="1" style="text-decoration: none">
                                                                  <a href="#" style="text-decoration:none"
                                                                    target="_blank">
                                                                    <font color="#000000"
                                                                      face="Arial, Helvetica, sans-serif" size="1">
                                                                      <span
                                                                        style="text-decoration: none; font-weight: 400">Arancel
                                                                        de Aduanas Decreto 4.944
                                                                      </span>
                                                                    </font>
                                                                  </a>
                                                                </font>
                                                              </td>
                                                            </tr>
                                                            <!--<tr>
                                                            <td>
                                                              <font color="#0000" face="Arial, Helvetica, sans-serif" size="1" style="text-decoration: none">
                                                                
                                                                  
                                                                <a href="#" target="_blank" style="text-decoration:none">
                                                                  <font color="#000000" face="Arial, Helvetica, sans-serif" size="1">
                                                                    <span style="text-decoration: none; font-weight: 400">Decreto de exoneración 4907 
                                                                    </span>
                                                                  </font>
                                                                </a>
                                                              </font>
                                                            </td>
                                                          </tr>



                                                          <tr>
                                                            <td>
                                                              <font color="#0000" face="Arial, Helvetica, sans-serif" size="1" style="text-decoration: none">
                                                                
                                                                  
                                                                <a href="#" target="_blank" style="text-decoration:none">
                                                                  <font color="#000000" face="Arial, Helvetica, sans-serif" size="1">
                                                                    <span style="text-decoration: none; font-weight: 400">Aviso Decreto de exoneración 4907 
                                                                    </span>
                                                                  </font>
                                                                </a>
                                                              </font>
                                                            </td>
                                                          </tr>-->
                                                            <tr>
                                                              <td>
                                                                <font color="#0000" face="Arial, Helvetica, sans-serif"
                                                                  size="1" style="text-decoration: none">
                                                                  <a href="#" style="text-decoration:none"
                                                                    target="_blank">
                                                                    <font color="#000000"
                                                                      face="Arial, Helvetica, sans-serif" size="1">
                                                                      <span
                                                                        style="text-decoration: none; font-weight: 400">Declaración
                                                                        del Valor de Aduana (Gaceta)
                                                                      </span>
                                                                    </font>
                                                                  </a>
                                                                </font>
                                                              </td>
                                                            </tr>
                                                            <tr>
                                                            <tr>
                                                              <td>
                                                                <font color="#0000" face="Arial, Helvetica, sans-serif"
                                                                  size="1" style="text-decoration: none">
                                                                  <a href="#" style="text-decoration:none"
                                                                    target="_blank">
                                                                    <font color="#000000"
                                                                      face="Arial, Helvetica, sans-serif" size="1">
                                                                      <span
                                                                        style="text-decoration: none; font-weight: 400">Planilla
                                                                        de Declaración del Valor
                                                                      </span>
                                                                    </font>
                                                                  </a>
                                                                </font>
                                                              </td>
                                                            </tr>
                                                            <tr>
                                                            <tr>
                                                              <td>
                                                                <font color="#0000" face="Arial, Helvetica, sans-serif"
                                                                  size="1" style="text-decoration: none">
                                                                  <a href="#" style="text-decoration:none"
                                                                    target="_blank">
                                                                    <font color="#000000"
                                                                      face="Arial, Helvetica, sans-serif" size="1">
                                                                      <span
                                                                        style="text-decoration: none; font-weight: 400">Hoja
                                                                        adicional de la Declaración del Valor
                                                                      </span>
                                                                    </font>
                                                                  </a>
                                                                </font>
                                                              </td>
                                                            </tr>
                                                            <tr>
                                                              <!--<tr>
                                                            <td>
                                                              <font color="#0000" face="Arial, Helvetica, sans-serif" size="1" style="text-decoration: none">
                                                                
                                                                <a href="#" target="_blank" style="text-decoration:none">
                                                                  <font color="#000000" face="Arial, Helvetica, sans-serif" size="1">
                                                                    <span style="text-decoration: none; font-weight: 400">AVISO AUXILIARES ADUANA LAS PIEDRAS Y SUS SUBALTERNAS
                                                                    </span>
                                                                  </font>
                                                                </a>
                                                              </font>
                                                            </td>
                                                          </tr>
                                                          <tr>-->
                                                              <!-- <tr>
                                                            <td>
                                                              <font color="#0000" face="Arial, Helvetica, sans-serif" size="1" style="text-decoration: none">
                                                                
                                                                <a href="#" target="_blank" style="text-decoration:none">
                                                                  <font color="#000000" face="Arial, Helvetica, sans-serif" size="1">
                                                                    <span style="text-decoration: none; font-weight: 400">Reforma Decreto 4822
                                                                    </span>
                                                                  </font>
                                                                </a>
                                                              </font>
                                                            </td>
                                                          </tr>
                                                          <tr>-->
                                                              <!--<tr>
                                                            <td>
                                                              <font color="#0000" face="Arial, Helvetica, sans-serif" size="1" style="text-decoration: none">
                                                                
                                                                <a href="#" target="_blank" style="text-decoration:none">
                                                                  <font color="#000000" face="Arial, Helvetica, sans-serif" size="1">
                                                                    <span style="text-decoration: none; font-weight: 400">Decreto 4821 GOE 6750 Exoneración
                                                                    </span>
                                                                  </font>
                                                                </a>
                                                              </font>
                                                            </td>
                                                          </tr>
                                                          <tr>-->
                                                              <!--<tr>
                                                            <td>
                                                              <font color="#0000" face="Arial, Helvetica, sans-serif" size="1" style="text-decoration: none">
                                                                
                                                                <a href="#" target="_blank" style="text-decoration:none">
                                                                  <font color="#000000" face="Arial, Helvetica, sans-serif" size="1">
                                                                    <span style="text-decoration: none; font-weight: 400">Aviso Web Decreto Exoneración 4821
                                                                    </span>
                                                                  </font>
                                                                </a>
                                                              </font>
                                                            </td>
                                                          </tr>
                                                          <tr>-->
                                                            <tr>
                                                              <td>
                                                                <font color="#0000" face="Arial, Helvetica, sans-serif"
                                                                  size="1" style="text-decoration: none">
                                                                  <a href="#" style="text-decoration:none"
                                                                    target="_blank">
                                                                    <font color="#000000"
                                                                      face="Arial, Helvetica, sans-serif" size="1">
                                                                      <span
                                                                        style="text-decoration: none; font-weight: 400">ACUERDO
                                                                        DE ALCANCE PARCIAL DE COMPLEMENTACIÓN ECONÓMICA
                                                                        (AAP.CE) N° 23 CHILE-VENEZUELA
                                                                      </span>
                                                                    </font>
                                                                  </a>
                                                                </font>
                                                              </td>
                                                            </tr>
                                                            <tr>
                                                            <tr>
                                                              <td>
                                                                <font color="#0000" face="Arial, Helvetica, sans-serif"
                                                                  size="1" style="text-decoration: none">
                                                                  <a href="#" style="text-decoration:none"
                                                                    target="_blank">
                                                                    <font color="#000000"
                                                                      face="Arial, Helvetica, sans-serif" size="1">
                                                                      <span
                                                                        style="text-decoration: none; font-weight: 400">AVISO
                                                                        SIDUNEA WORLD ADUANA PRINCIPAL EL AMPARO DE
                                                                        APURE
                                                                        Y SUS SUBALTERNAS
                                                                      </span>
                                                                    </font>
                                                                  </a>
                                                                </font>
                                                              </td>
                                                            </tr>
                                                            <tr>
                                                            <tr>
                                                              <td>
                                                                <font color="#0000" face="Arial, Helvetica, sans-serif"
                                                                  size="1" style="text-decoration: none">
                                                                  <a href="#" style="text-decoration:none"
                                                                    target="_blank">
                                                                    <font color="#000000"
                                                                      face="Arial, Helvetica, sans-serif" size="1">
                                                                      <span
                                                                        style="text-decoration: none; font-weight: 400">LISTADO
                                                                        AGENTES DE ADUANA BAJO RELACIÓN DE DEPENDENCIA
                                                                        POR
                                                                        NOTIFICAR 2016-2023
                                                                      </span>
                                                                    </font>
                                                                  </a>
                                                                </font>
                                                              </td>
                                                            </tr>
                                                            <tr>
                                                              <!-- <tr>
                                                            <td>
                                                              <font color="#0000" face="Arial, Helvetica, sans-serif" size="1" style="text-decoration: none">
                                                                <img border="0" src="https://declaraciones.seniat.gob.ve/portal/page/portal/MANEJADOR_CONTENIDO_SENIAT/01NOTICIAS/00IMAGENES/nuevo.gif">
                                                                <a href="#" target="_blank" style="text-decoration:none">
                                                                  <font color="#000000" face="Arial, Helvetica, sans-serif" size="1">
                                                                    <span style="text-decoration: none; font-weight: 400">Decreto 4757 GOE 6.727 Exoneración
                                                                    </span>
                                                                  </font>
                                                                </a>
                                                              </font>
                                                            </td>
                                                          </tr> -->
                                                              <!--                                                           <tr>
                                                            <td>
                                                              <font color="#0000" face="Arial, Helvetica, sans-serif" size="1" style="text-decoration: none">
                                                                <img border="0" src="https://declaraciones.seniat.gob.ve/portal/page/portal/MANEJADOR_CONTENIDO_SENIAT/01NOTICIAS/00IMAGENES/nuevo.gif">
                                                                <a href="#" target="_blank" style="text-decoration:none">
                                                                  <font color="#000000" face="Arial, Helvetica, sans-serif" size="1">
                                                                    <span style="text-decoration: none; font-weight: 400">Aviso Web Decreto Exoneración 4757
                                                                    </span>
                                                                  </font>
                                                                </a>
                                                              </font>
                                                            </td>
                                                          </tr> -->
                                                              <!--                                                           <tr>
                                                            <td>
                                                              <font color="#0000" face="Arial, Helvetica, sans-serif" size="1" style="text-decoration: none">
                                                                <img border="0" src="https://declaraciones.seniat.gob.ve/portal/page/portal/MANEJADOR_CONTENIDO_SENIAT/01NOTICIAS/00IMAGENES/nuevo.gif">
                                                                <a href="#" target="_blank" style="text-decoration:none">
                                                                  <font color="#000000" face="Arial, Helvetica, sans-serif" size="1">
                                                                    <span style="text-decoration: none; font-weight: 400">Decreto 4.727 Reforma del Arancel de Aduanas 23-08-2022
                                                                    </span>
                                                                  </font>
                                                                </a>
                                                              </font>
                                                            </td>
                                                          </tr> -->
                                                              <!-- 

                                                          <tr>
                                                            <td>
                                                              <font color="#0000" face="Arial, Helvetica, sans-serif" size="1" style="text-decoration: none">
                                                                <img border="0" src="https://declaraciones.seniat.gob.ve/portal/page/portal/MANEJADOR_CONTENIDO_SENIAT/01NOTICIAS/00IMAGENES/nuevo.gif">
                                                                <a href="#" target="_blank" style="text-decoration:none">
                                                                  <font color="#000000" face="Arial, Helvetica, sans-serif" size="1">
                                                                    <span style="text-decoration: none; font-weight: 400">Anuncio Web Implementación del Código Adicional 579
                                                                    </span>
                                                                  </font>
                                                                </a>
                                                              </font>
                                                            </td>
                                                          </tr> -->
                                                              <!--                                                           <tr>
                                                            <td>
                                                              <font color="#0000" face="Arial, Helvetica, sans-serif" size="1" style="text-decoration: none">
                                                                <img border="0" src="https://declaraciones.seniat.gob.ve/portal/page/portal/MANEJADOR_CONTENIDO_SENIAT/01NOTICIAS/00IMAGENES/nuevo.gif">
                                                                <a href="#" target="_blank" style="text-decoration:none">
                                                                  <font color="#000000" face="Arial, Helvetica, sans-serif" size="1">
                                                                    <span style="text-decoration: none; font-weight: 400">NOTIFICACION DE APROBADOS AGENTES DE ADUANAS
                                                                    </span>
                                                                  </font>
                                                                </a>
                                                              </font>
                                                            </td>
                                                          </tr> -->
                                                              <!--                                                           <tr>
                                                            <td>
                                                              <font color="#0000" face="Arial, Helvetica, sans-serif" size="1" style="text-decoration: none">
                                                                <img border="0" src="https://declaraciones.seniat.gob.ve/portal/page/portal/MANEJADOR_CONTENIDO_SENIAT/01NOTICIAS/00IMAGENES/nuevo.gif">
                                                                <a href="#" target="_blank" style="text-decoration:none">
                                                                  <font color="#000000" face="Arial, Helvetica, sans-serif" size="1">
                                                                    <span style="text-decoration: none; font-weight: 400">NOTIFICACION DE EXCLUSION, IMPROCEDENTES E INCLUSIONES
                                                                    </span>
                                                                  </font>
                                                                </a>
                                                              </font>
                                                            </td>
                                                          </tr> -->
                                                            <tr>
                                                              <td>
                                                                <font color="#0000" face="Arial, Helvetica, sans-serif"
                                                                  size="1" style="text-decoration: none">
                                                                  <!-- <img border="0" src="https://declaraciones.seniat.gob.ve/portal/page/portal/MANEJADOR_CONTENIDO_SENIAT/01NOTICIAS/00IMAGENES/nuevo.gif"> -->
                                                                  <a href="#" style="text-decoration:none"
                                                                    target="_blank">
                                                                    <font color="#000000"
                                                                      face="Arial, Helvetica, sans-serif" size="1">
                                                                      <span
                                                                        style="text-decoration: none; font-weight: 400">AVISO
                                                                        SIDUNEA WORLD
                                                                      </span>
                                                                    </font>
                                                                  </a>
                                                                </font>
                                                              </td>
                                                            </tr>
                                                            <!--                                                           <tr>
                                                            <td>
                                                              <font color="#0000" face="Arial, Helvetica, sans-serif" size="1" style="text-decoration: none">
                                                                <img border="0" src="https://declaraciones.seniat.gob.ve/portal/page/portal/MANEJADOR_CONTENIDO_SENIAT/01NOTICIAS/00IMAGENES/nuevo.gif">
                                                                <a href="#" target="_blank" style="text-decoration:none">
                                                                  <font color="#000000" face="Arial, Helvetica, sans-serif" size="1">
                                                                    <span style="text-decoration: none; font-weight: 400">Respuesta a solicitudes de Agentes de Aduanas Bajo Relación de Dependencia.
                                                                    </span>
                                                                  </font>
                                                                </a>
                                                              </font>
                                                            </td>
                                                          </tr> -->
                                                            <!-- <tr>
                                                            <td>
                                                              <font color="#0000" face="Arial, Helvetica, sans-serif" size="1" style="text-decoration: none">
                                                                <strong>
                                                                  <!--<img border="0" src="https://declaraciones.seniat.gob.ve/portal/page/portal/MANEJADOR_CONTENIDO_SENIAT/01NOTICIAS/00IMAGENES/nuevo.gif"> -->
                                                            <!--</strong>
                                                                <a href="#" style="text-decoration:none">
                                                                  <strong style="font-weight: 400">
                                                                    <font color="#0000" face="Arial, Helvetica, sans-serif" size="1" style="text-decoration: none">Correlación entre Decreto 2.647 y Capitulo 98 del Decreto de Reforma parcial del Arancel de Aduanas  4.684</font>
                                                                  </strong>
                                                                </a>
                                                              </font>
                                                            </td>
                                                          </tr>-->
                                                            <!--                                                           <tr>
                                                            <td>
                                                              <font color="#0000" face="Arial, Helvetica, sans-serif" size="1" style="text-decoration: none">
                                                                <strong>
                                                                  <img border="0" src="https://declaraciones.seniat.gob.ve/portal/page/portal/MANEJADOR_CONTENIDO_SENIAT/01NOTICIAS/00IMAGENES/nuevo.gif">
                                                                </strong>
                                                                <a href="#" style="text-decoration:none">
                                                                  <strong style="font-weight: 400">
                                                                    <font color="#0000" face="Arial, Helvetica, sans-serif" size="1" style="text-decoration: none">Decreto de Reforma parcial del Arancel de Aduanas  4.684</font>
                                                                  </strong>
                                                                </a>
                                                              </font>
                                                            </td>
                                                          </tr> -->
                                                            <!--                                                           <tr>
                                                            <td>
                                                              <font color="#0000" face="Arial, Helvetica, sans-serif" size="1" style="text-decoration: none">
                                                                <strong>
                                                                  <img border="0" src="https://declaraciones.seniat.gob.ve/portal/page/portal/MANEJADOR_CONTENIDO_SENIAT/01NOTICIAS/00IMAGENES/nuevo.gif">
                                                                </strong>
                                                                <a href="#" style="text-decoration:none">
                                                                  <strong style="font-weight: 400">
                                                                    <font color="#0000" face="Arial, Helvetica, sans-serif" size="1" style="text-decoration: none">Agentes de Aduanas al Servicio de una Persona Jurídica bajo Relación de Dependencia.</font>
                                                                  </strong>
                                                                </a>
                                                              </font>
                                                            </td>
                                                          </tr> -->
                                                            <!--                                                           <tr>
                                                            <td>
                                                              <font color="#0000" face="Arial, Helvetica, sans-serif" size="1" style="text-decoration: none">
                                                                <strong>
                                                                  <img border="0" src="https://declaraciones.seniat.gob.ve/portal/page/portal/MANEJADOR_CONTENIDO_SENIAT/01NOTICIAS/00IMAGENES/nuevo.gif">
                                                                </strong>
                                                                <a href="#" style="text-decoration:none">
                                                                  <strong style="font-weight: 400">
                                                                    <font color="#0000" face="Arial, Helvetica, sans-serif" size="1" style="text-decoration: none">Respuestas a solicitudes Agente de Aduanas al Servicio de una Persona Jurídica bajo Relación de Dependencia. </font>
                                                                  </strong>
                                                                </a>
                                                              </font>
                                                            </td>
                                                          </tr> -->
                                                            <!--                                                           <tr>
                                                            <td>
                                                              <font color="#0000" face="Arial, Helvetica, sans-serif" size="1" style="text-decoration: none">
                                                                <img border="0" src="https://declaraciones.seniat.gob.ve/portal/page/portal/MANEJADOR_CONTENIDO_SENIAT/01NOTICIAS/00IMAGENES/nuevo.gif">
                                                                <a href="#" target="_blank" style="text-decoration:none">
                                                                  <font color="#000000" face="Arial, Helvetica, sans-serif" size="1">
                                                                    <span style="text-decoration: none; font-weight: 400">Notificación INCLUSION EXCLUSION Agente de Aduanas al Servicio de una Persona Jurídica bajo Relación de Dependencia.
                                                                    </span>
                                                                  </font>
                                                                </a>
                                                              </font>
                                                            </td>
                                                          </tr> -->
                                                            <!-- 
                                                          <tr>
                                                            <td>
                                                              <font color="#0000" face="Arial, Helvetica, sans-serif" size="1" style="text-decoration: none">
                                                                <img border="0" src="https://declaraciones.seniat.gob.ve/portal/page/portal/MANEJADOR_CONTENIDO_SENIAT/01NOTICIAS/00IMAGENES/nuevo.gif">
                                                                <a href="#" target="_blank" style="text-decoration:none">
                                                                  <font color="#000000" face="Arial, Helvetica, sans-serif" size="1">
                                                                    <span style="text-decoration: none; font-weight: 400">Aviso Web Decreto de exoneración 4683
                                                                    </span>
                                                                  </font>
                                                                </a>
                                                              </font>
                                                            </td>
                                                          </tr> -->
                                                            <!--     <tr>
                                                            <td>
                                                              <font color="#0000" face="Arial, Helvetica, sans-serif" size="1" style="text-decoration: none">
                                                                <img border="0" src="https://declaraciones.seniat.gob.ve/portal/page/portal/MANEJADOR_CONTENIDO_SENIAT/01NOTICIAS/00IMAGENES/nuevo.gif">
                                                                <a href="#" target="_blank" style="text-decoration:none">
                                                                  <font color="#000000" face="Arial, Helvetica, sans-serif" size="1">
                                                                    <span style="text-decoration: none; font-weight: 400">Decreto de exoneración 4683
                                                                    </span>
                                                                  </font>
                                                                </a>
                                                              </font>
                                                            </td>
                                                          </tr> -->
                                                            <!--     <tr>
                                                            <td>
                                                              <font color="#0000" face="Arial, Helvetica, sans-serif" size="1" style="text-decoration: none">
                                                                <img border="0" src="https://declaraciones.seniat.gob.ve/portal/page/portal/MANEJADOR_CONTENIDO_SENIAT/01NOTICIAS/00IMAGENES/nuevo.gif">
                                                                <a href="#" target="_blank" style="text-decoration:none">
                                                                  <font color="#000000" face="Arial, Helvetica, sans-serif" size="1">
                                                                    <span style="text-decoration: none; font-weight: 400">Acuerdo de Desarrollo Comercial Venezuela - Turquía
                                                                    </span>
                                                                  </font>
                                                                </a>
                                                              </font>
                                                            </td>
                                                          </tr> -->
                                                            <!--                                                           <tr>
                                                            <td>
                                                              <font color="#0000" face="Arial, Helvetica, sans-serif" size="1" style="text-decoration: none">
                                                                <img border="0" src="https://declaraciones.seniat.gob.ve/portal/page/portal/MANEJADOR_CONTENIDO_SENIAT/01NOTICIAS/00IMAGENES/nuevo.gif">
                                                                <a href="#" target="_blank" style="text-decoration:none">
                                                                  <font color="#000000" face="Arial, Helvetica, sans-serif" size="1">
                                                                    <span style="text-decoration: none; font-weight: 400">Respuestas a Solicitudes Agente Aduanas (Persona Natural)
                                                                    </span>
                                                                  </font>
                                                                </a>
                                                              </font>
                                                            </td>
                                                          </tr> -->
                                                            <!--                                                           <tr>
                                                            <td>
                                                              <font color="#0000" face="Arial, Helvetica, sans-serif" size="1" style="text-decoration: none">
                                                                <img border="0" src="https://declaraciones.seniat.gob.ve/portal/page/portal/MANEJADOR_CONTENIDO_SENIAT/01NOTICIAS/00IMAGENES/nuevo.gif">
                                                                <a href="#" target="_blank" style="text-decoration:none">
                                                                  <font color="#000000" face="Arial, Helvetica, sans-serif" size="1">
                                                                    <span style="text-decoration: none; font-weight: 400">Recaudos Autorizaciones Agente Aduanas (Persona Natural)
                                                                    </span>
                                                                  </font>
                                                                </a>
                                                              </font>
                                                            </td>
                                                          </tr> -->
                                                            <!--                                                           <tr>
                                                            <td>
                                                              <font color="#0000" face="Arial, Helvetica, sans-serif" size="1" style="text-decoration: none">
                                                                <img border="0" src="https://declaraciones.seniat.gob.ve/portal/page/portal/MANEJADOR_CONTENIDO_SENIAT/01NOTICIAS/00IMAGENES/nuevo.gif">
                                                                <a href="#" target="_blank" style="text-decoration:none">
                                                                  <font color="#000000" face="Arial, Helvetica, sans-serif" size="1">
                                                                    <span style="text-decoration: none; font-weight: 400">Autorización Agente Aduanas (Persona Natural) Aprobadas
                                                                    </span>
                                                                  </font>
                                                                </a>
                                                              </font>
                                                            </td>
                                                          </tr> -->
                                                            <!--<tr>
                                                            <td>
                                                              <font color="#0000" face="Arial, Helvetica, sans-serif" size="1" style="text-decoration: none">
                                                                
                                                                <!--<img border="0" src="https://declaraciones.seniat.gob.ve/portal/page/portal/MANEJADOR_CONTENIDO_SENIAT/01NOTICIAS/00IMAGENES/nuevo.gif"> -->
                                                            <!--<a href="#" target="_blank" style="text-decoration:none">
                                                                  <font color="#000000" face="Arial, Helvetica, sans-serif" size="1">
                                                                    <span style="text-decoration: none; font-weight: 400">Arancel de Aduanas - Decreto 2.647 y sus Reformas
                                                                    </span>
                                                                  </font>
                                                                </a>
                                                              </font>
                                                            </td>
                                                          </tr>-->
                                                            <!--<tr>
                                                            <td>
                                                              <font color="#0000" face="Arial, Helvetica, sans-serif" size="1" style="text-decoration: none">
                                                                
                                                                <!-- <img border="0" src="https://declaraciones.seniat.gob.ve/portal/page/portal/MANEJADOR_CONTENIDO_SENIAT/01NOTICIAS/00IMAGENES/nuevo.gif"> -->
                                                            <!--<a href="#" target="_blank" style="text-decoration:none">
                                                                  <font color="#000000" face="Arial, Helvetica, sans-serif" size="1">
                                                                    <span style="text-decoration: none; font-weight: 400">Instrucciones Reforma del Arancel de Aduanas
                                                                    </span>
                                                                  </font>
                                                                </a>
                                                              </font>
                                                            </td>
                                                          </tr>-->
                                                            <!--                                                           <tr>
                                                            <td>
                                                              <font color="#0000" face="Arial, Helvetica, sans-serif" size="1" style="text-decoration: none">
                                                                
                                                                <img border="0" src="https://declaraciones.seniat.gob.ve/portal/page/portal/MANEJADOR_CONTENIDO_SENIAT/01NOTICIAS/00IMAGENES/nuevo.gif">
                                                                <a href="#" target="_blank" style="text-decoration:none">
                                                                  <font color="#000000" face="Arial, Helvetica, sans-serif" size="1">
                                                                    <span style="text-decoration: none; font-weight: 400">Nuevo • Arancel de Aduanas-Decreto 4.111
                                                                    </span>
                                                                  </font>
                                                                </a>
                                                              </font>
                                                            </td>
                                                          </tr> -->
                                                            <tr>
                                                              <td>
                                                                <font color="#0000" face="Arial, Helvetica, sans-serif"
                                                                  size="1" style="text-decoration: none">
                                                                  <!-- <img border="0" src="https://declaraciones.seniat.gob.ve/portal/page/portal/MANEJADOR_CONTENIDO_SENIAT/01NOTICIAS/00IMAGENES/nuevo.gif"> -->
                                                                  <a href="#" style="text-decoration:none"
                                                                    target="_blank">
                                                                    <font color="#000000"
                                                                      face="Arial, Helvetica, sans-serif" size="1">
                                                                      <span
                                                                        style="text-decoration: none; font-weight: 400">Liquidación
                                                                        y Autoliquidación de Tasas
                                                                      </span>
                                                                    </font>
                                                                  </a>
                                                                </font>
                                                              </td>
                                                            </tr>
                                                            <tr>
                                                              <td>
                                                                <font color="#0000" face="Arial, Helvetica, sans-serif"
                                                                  size="1" style="text-decoration: none">
                                                                  <strong>
                                                                    <!-- <img border="0" src="https://declaraciones.seniat.gob.ve/portal/page/portal/MANEJADOR_CONTENIDO_SENIAT/01NOTICIAS/00IMAGENES/nuevo.gif"> -->
                                                                  </strong>
                                                                  <a href="#" style="text-decoration:none">
                                                                    <strong style="font-weight: 400">
                                                                      <font color="#0000"
                                                                        face="Arial, Helvetica, sans-serif" size="1"
                                                                        style="text-decoration: none">Descarga de
                                                                        aplicaciones SIDUNEA
                                                                      </font>
                                                                    </strong>
                                                                  </a>
                                                                </font>
                                                              </td>
                                                            </tr>
                                                            <tr>
                                                              <td>
                                                                <font color="#0000" face="Arial, Helvetica, sans-serif"
                                                                  size="1" style="text-decoration: none">
                                                                  <a href="#" style="text-decoration:none">
                                                                    <strong style="font-weight: 400">
                                                                      <font color="#0000"
                                                                        face="Arial, Helvetica, sans-serif" size="1"
                                                                        style="text-decoration: none">SIDUNEA World
                                                                        Venezuela</font>
                                                                    </strong>
                                                                  </a>
                                                                </font>
                                                              </td>
                                                            </tr>
                                                    </tr>
                                            </tr>
                                  </tr>
                        </tr>
                </tr>
        </tr>
      </tbody>
    </table>
    </td>
    </tr>
    </tbody>
    </table>
    </td>
    </tr>
    </tbody>
    </table>
  </div>
  </td>
  </tr>
  </tbody>
  </table>
</div>
</td>
</tr>
</tbody>
</table>
</td>
<!-- TRIBUTOS -->
<td style="width:350px" valign="top">
  <table border="0" cellpadding="0" cellspacing="0" id="rg2063" summary="" width="100%">
    <tbody>
      <tr align="LEFT">
        <td valign="top">
          <div id="p78_17359_78_1_1">
            <table border="0" cellpadding="0" cellspacing="0" class="RegionNoBorder" width="100%">
              <tbody>
                <tr>
                  <td class="RegionHeaderColor" style="width:100%">
                    <div id="pcnt78_17359_78_1_1">
                      <table align="center" cellpadding="0" cellspacing="0">
                        <tbody>
                          <!-- TRIBUTOS IMAGEN-->
                          <tr valign="top">
                            <td align="center">
                              <a href="#">
                                <img src="<?= asset('img/seniat-index-viejo/268459722f7b.gif') ?>" />
                              </a>
                            </td>
                          </tr>
                          <tr bordercolor="#CC0000">
                            <td align="center" bordercolor="#FF0000">
                              <table border="1" bordercolor="#FF0000" style="border-collapse: collapse">
                                <tbody>
                                  <tr>
                                    <td bordercolor="#FFFFFF" valign="top">
                                      <table border="0" height="507" width="270">
                                        <tbody>
                                          <!-- TRIBUTOS CONTENIDO-->
                                          <tr>
                                            <td>
                                              <font color="#0000" face="Arial, Helvetica, sans-serif" size="1"
                                                style="text-decoration: none">
                                                <img border="0"
                                                  src="<?= asset('img/seniat-index-viejo/13c6fce9f33f.gif') ?>" />
                                              </font>
                                              <span style="text-decoration: none">
                                                <a href="#" style="text-decoration: none" target="_blank">
                                                  <font color="#000000" face="Arial, Helvetica, sans-serif" size="1">
                                                    Guía Fácil de Atención Electrónica al Contribuyente
                                                  </font>
                                                </a>
                                              </span>
                                            </td>
                                          </tr>
                                          <tr>
                                            <td>
                                              <font color="#0000" face="Arial, Helvetica, sans-serif" size="1"
                                                style="text-decoration: none">
                                                <img border="0"
                                                  src="<?= asset('img/seniat-index-viejo/13c6fce9f33f.gif') ?>" />
                                              </font>
                                              <span style="text-decoration: none">
                                                <a href="#" style="text-decoration: none" target="_blank">
                                                  <font color="#000000" face="Arial, Helvetica, sans-serif" size="1">
                                                    Instructivo de Autoliquidación de Impuesto de Sucesiones
                                                  </font>
                                                </a>
                                              </span>
                                            </td>
                                          </tr>
                                          <tr>
                                            <td>
                                              <font color="#0000" face="Arial, Helvetica, sans-serif" size="1"
                                                style="text-decoration: none">
                                                <img border="0"
                                                  src="<?= asset('img/seniat-index-viejo/13c6fce9f33f.gif') ?>" />
                                              </font>
                                              <span style="text-decoration: none">
                                                <a href="#" style="text-decoration: none" target="_blank">
                                                  <font color="#000000" face="Arial, Helvetica, sans-serif" size="1">
                                                    Guía Fácil Acceso al Portal Seniat
                                                  </font>
                                                </a>
                                              </span>
                                            </td>
                                          </tr>
                                          <tr>
                                            <td>
                                              <font color="#0000" face="Arial, Helvetica, sans-serif" size="1"
                                                style="text-decoration: none">
                                                <img border="0"
                                                  src="<?= asset('img/seniat-index-viejo/13c6fce9f33f.gif') ?>" />
                                              </font>
                                              <span style="text-decoration: none">
                                                <a href="#" style="text-decoration: none" target="_blank">
                                                  <font color="#000000" face="Arial, Helvetica, sans-serif" size="1">
                                                    Guía Fácil Regístrese / Contribuyentes
                                                  </font>
                                                </a>
                                              </span>
                                            </td>
                                          </tr>
                                          <tr>
                                            <td>
                                              <font color="#0000" face="Arial, Helvetica, sans-serif" size="1"
                                                style="text-decoration: none">
                                                <img border="0"
                                                  src="<?= asset('img/seniat-index-viejo/13c6fce9f33f.gif') ?>" />
                                              </font>
                                              <span style="text-decoration: none">
                                                <a href="#" style="text-decoration: none" target="_blank">
                                                  <font color="#000000" face="Arial, Helvetica, sans-serif" size="1">
                                                    Guía Fácil ¿Olvido su Información?
                                                  </font>
                                                </a>
                                              </span>
                                            </td>
                                          </tr>
                                          <tr>
                                            <td>
                                              <font color="#0000" face="Arial, Helvetica, sans-serif" size="1"
                                                style="text-decoration: none">
                                                <img border="0"
                                                  src="<?= asset('img/seniat-index-viejo/13c6fce9f33f.gif') ?>" />
                                              </font>
                                              <span style="text-decoration: none">
                                                <a href="#" style="text-decoration: none" target="_blank">
                                                  <font color="#000000" face="Arial, Helvetica, sans-serif" size="1">
                                                    Guía Fácil Registrar Preguntas
                                                  </font>
                                                </a>
                                              </span>
                                            </td>
                                          </tr>
                                          <tr>
                                            <td>
                                              <font color="#0000" face="Arial, Helvetica, sans-serif" size="1"
                                                style="text-decoration: none">
                                                <img border="0"
                                                  src="<?= asset('img/seniat-index-viejo/13c6fce9f33f.gif') ?>" />
                                              </font>
                                              <span style="text-decoration: none">
                                                <a href="#" style="text-decoration: none" target="_blank">
                                                  <font color="#000000" face="Arial, Helvetica, sans-serif" size="1">
                                                    Instructivo Administración de Perfil
                                                  </font>
                                                </a>
                                              </span>
                                            </td>
                                          </tr>
                                          <tr>
                                            <td>
                                              <font color="#0000" face="Arial, Helvetica, sans-serif" size="1"
                                                style="text-decoration: none">
                                                <img border="0"
                                                  src="<?= asset('img/seniat-index-viejo/13c6fce9f33f.gif') ?>" />
                                              </font>
                                              <span style="text-decoration: none">
                                                <a href="#" style="text-decoration: none" target="_blank">
                                                  <font color="#000000" face="Arial, Helvetica, sans-serif" size="1">
                                                    Providencia SNAT/2025 /000048 mediante la cual se reajusta el
                                                    valor de la Unidad Tributaria de nueve bolívares (Bs 9,00) a
                                                    cuarenta y tres bolívares (Bs43,00).
                                                  </font>
                                                </a>
                                              </span>
                                            </td>
                                          </tr>
                                          <tr>
                                            <td>
                                              <font color="#0000" face="Arial, Helvetica, sans-serif" size="1"
                                                style="text-decoration: none">
                                                <img border="0"
                                                  src="<?= asset('img/seniat-index-viejo/13c6fce9f33f.gif') ?>" />
                                              </font>
                                              <span style="text-decoration: none">
                                                <a href="#" style="text-decoration: none">
                                                  <font color="#000000" face="Arial, Helvetica, sans-serif" size="1">
                                                    Software y Versiones Autorizadas para la Emisión de Facturas y
                                                    Otros Documentos Fiscales.
                                                  </font>
                                                </a>
                                              </span>
                                            </td>
                                          </tr>
                                          <tr>
                                            <td>
                                              <span style="text-decoration: none">
                                                <a href="#" style="text-decoration: none" target="_blank">
                                                  <font color="#000000" face="Arial, Helvetica, sans-serif" size="1">
                                                    Instructivo Declaración para la Protección de las Pensiones.
                                                  </font>
                                                </a>
                                              </span>
                                            </td>
                                          </tr>
                                          <tr>
                                            <td>
                                              <span style="text-decoration: none">
                                                <a href="#" style="text-decoration: none" target="_blank">
                                                  <font color="#000000" face="Arial, Helvetica, sans-serif" size="1">
                                                    Providencia Administrativa que establece las normas para la
                                                    Declaración y Pago de la contribución especial para la Protección
                                                    a las Pensiones de Seguridad Social frente al Bloqueo
                                                    Imperialista.
                                                  </font>
                                                </a>
                                              </span>
                                            </td>
                                          </tr>
                                          <tr>
                                            <td>
                                              <span style="text-decoration: none">
                                                <a href="#" style="text-decoration: none" target="_blank">
                                                  <font color="#000000" face="Arial, Helvetica, sans-serif" size="1">
                                                    Decreto N° 4.952, mediante el cual se establece como monto de la
                                                    contribución especial prevista en la Ley de Protección de las
                                                    Pensiones de Seguridad Social frente al Bloqueo Imperialista el
                                                    nueve por ciento (9%).
                                                  </font>
                                                </a>
                                              </span>
                                            </td>
                                          </tr>
                                          <tr>
                                            <td>
                                              <span style="text-decoration: none">
                                                <a href="#" style="text-decoration: none" target="_blank">
                                                  <font color="#000000" face="Arial, Helvetica, sans-serif" size="1">
                                                    Ley de Protección de las Pensiones de Seguridad Social Frente al
                                                    Bloqueo Imperialista.
                                                  </font>
                                                </a>
                                              </span>
                                            </td>
                                          </tr>
                                          <tr>
                                            <td>
                                              <span style="text-decoration: none">
                                                <a href="#" style="text-decoration: none" target="_blank">
                                                  <font color="#000000" face="Arial, Helvetica, sans-serif" size="1">
                                                    Imprentas Digitales Autorizadas para los Prestadores de Servicio
                                                    Masivo
                                                  </font>
                                                </a>
                                              </span>
                                            </td>
                                          </tr>
                                          <tr>
                                            <td>
                                              <!-- <font color="#0000" face="Arial, Helvetica, sans-serif" size="1" style="text-decoration: none">
                                                                <img border="0" src="https://declaraciones.seniat.gob.ve/portal/page/portal/MANEJADOR_CONTENIDO_SENIAT/01NOTICIAS/00IMAGENES/nuevo.gif">
                                                              </font> -->
                                              <span style="text-decoration: none">
                                                <a href="#" style="text-decoration: none" target="_blank">
                                                  <font color="#000000" face="Arial, Helvetica, sans-serif" size="1">
                                                    Instructivo de Usuario Declaración y Pago de Enajenación de
                                                    Inmuebles (Forma 33), Versión 1.0.
                                                  </font>
                                                </a>
                                              </span>
                                            </td>
                                          </tr>
                                          <tr>
                                            <td>
                                              <!-- <font color="#0000" face="Arial, Helvetica, sans-serif" size="1" style="text-decoration: none">
                                                                <img border="0" src="https://declaraciones.seniat.gob.ve/portal/page/portal/MANEJADOR_CONTENIDO_SENIAT/01NOTICIAS/00IMAGENES/nuevo.gif"> -->

                                              <span style="text-decoration: none">
                                                <a href="#" style="text-decoration: none" target="_blank">
                                                  <font color="#000000" face="Arial, Helvetica, sans-serif" size="1">
                                                    Instructivo de Usuario Declaración y Pago de Tasas e Impuestos
                                                    (Forma 16), Versión 1.0.
                                                  </font>
                                                </a>
                                              </span>
                                            </td>
                                          </tr>
                                          <tr>
                                            <td>
                                              <!-- <font color="#0000" face="Arial, Helvetica, sans-serif" size="1" style="text-decoration: none">
                                                                <img border="0" src="https://declaraciones.seniat.gob.ve/portal/page/portal/MANEJADOR_CONTENIDO_SENIAT/01NOTICIAS/00IMAGENES/nuevo.gif">
                                                              </font> 
                                                              <span style="text-decoration: none">
                                                                <a target="_blank" href="#" style="text-decoration: none">
                                                                  <font color="#000000" face="Arial, Helvetica, sans-serif" size="1">Providencia mediante la cual se reajusta el valor de la Unidad Tributaria de cero coma cuarenta Bolívares (Bs. 0,40) a nueve Bolívares (Bs. 9,00).
                                                                  </font>
                                                                </a>
                                                              </span>
                                                            </td>
                                                          </tr>-->
                                          <tr>
                                            <td>
                                              <span style="text-decoration: none">
                                                <a href="#" style="text-decoration: none" target="_blank">
                                                  <font color="#000000" face="Arial, Helvetica, sans-serif" size="1">
                                                    Instructivo de Usuario Declaración del Impuesto a las Grandes
                                                    Transacciones Financieras.
                                                  </font>
                                                </a>
                                              </span>
                                            </td>
                                          </tr>
                                          <!-- <tr>
                                                            <td>
                                                              <span style="text-decoration: none">
                                                                <a target="_blank" href="#" style="text-decoration: none">
                                                                  <font color="#000000" face="Arial, Helvetica, sans-serif" size="1">Providencia mediante la cual se reajusta la Unidad Tributaria de  Mil Quinientos Bolívares (BS. 1.500,00) a Veinte Mil Bolívares (BS. 20.000,00).
                                                                  </font>
                                                                </a>
                                                              </span>
                                                            </td>
                                                          </tr> -->
                                          <tr>
                                            <td>
                                              <span style="text-decoration: none">
                                                <a href="#" style="text-decoration: none" target="_blank">
                                                  <font color="#000000" face="Arial, Helvetica, sans-serif" size="1">
                                                    Guía Fácil Declaración ISLR/Exoneración Ejercicio Fiscal 2020.
                                                  </font>
                                                </a>
                                              </span>
                                            </td>
                                          </tr>
                                          <tr>
                                            <td>
                                              <span style="text-decoration: none">
                                                <a href="#" style="text-decoration: none" target="_blank">
                                                  <font color="#000000" face="Arial, Helvetica, sans-serif" size="1">
                                                    Requisitos y Formalidades para la Declaración del Impuesto a los
                                                    Grandes Patrimonios. Gaceta Oficial N° 41.696 de fecha 16 de
                                                    agosto del 2019.
                                                  </font>
                                                </a>
                                              </span>
                                            </td>
                                          </tr>
                                          <tr>
                                            <td>
                                              <span style="text-decoration: none">
                                                <a href="#" rel="noopener" style="text-decoration: none"
                                                  target="_blank">
                                                  <font color="#000000" face="Arial, Helvetica, sans-serif" size="1">
                                                    Servicios de Declaración.
                                                  </font>
                                                </a>
                                              </span>
                                            </td>
                                          </tr>
                                          <tr>
                                            <td>
                                              <span style="text-decoration: none">
                                                <a href="#" style="text-decoration: none" target="_blank">
                                                  <font color="#000000" face="Arial, Helvetica, sans-serif" size="1">
                                                    Instructivo Declaración Enteramiento de Recursos Versión 1_0_0.
                                                  </font>
                                                </a>
                                              </span>
                                            </td>
                                          </tr>
                                          <tr>
                                            <td>
                                              <span style="text-decoration: none">
                                                <a href="#" style="text-decoration: none" target="_blank">
                                                  <font color="#000000" face="Arial, Helvetica, sans-serif" size="1">
                                                    Instructivo Declaración Impuesto a los Grandes Patrimonios,
                                                    Versión 4_0_1.
                                                  </font>
                                                </a>
                                              </span>
                                            </td>
                                          </tr>
                                          <tr>
                                            <td>
                                              <font color="#0000" face="Arial, Helvetica, sans-serif" size="1"
                                                style="text-decoration: none">
                                              </font>
                                              <span style="text-decoration: none">
                                                <a href="#" style="text-decoration: none" target="_blank">
                                                  <font color="#000000" face="Arial, Helvetica, sans-serif" size="1">
                                                    Calendario Vigente.
                                                  </font>
                                                </a>
                                              </span>
                                            </td>
                                          </tr>
                                          <tr>
                                            <td>
                                              <font color="#0000" face="Arial, Helvetica, sans-serif" size="1"
                                                style="text-decoration: none">
                                              </font>
                                              <span style="text-decoration: none">
                                                <a href="#" style="text-decoration: none" target="_blank">
                                                  <font color="#000000" face="Arial, Helvetica, sans-serif" size="1">
                                                    Instructivo Declaración IVA
                                                  </font>
                                                </a>
                                              </span>
                                            </td>
                                          </tr>
                                          <tr>
                                            <td>
                                              <font color="#0000" face="Arial, Helvetica, sans-serif" size="1"
                                                style="text-decoration: none">
                                              </font>
                                              <span style="text-decoration: none">
                                                <a href="#" style="text-decoration: none" target="_blank">
                                                  <font color="#000000" face="Arial, Helvetica, sans-serif" size="1">
                                                    Instructivo Declaración Retenciones IVA.
                                                  </font>
                                                </a>
                                              </span>
                                            </td>
                                          </tr>
                                          <!-- <tr>
                                                            <td>
                                                              <font color="#0000" face="Arial, Helvetica, sans-serif" size="1" style="text-decoration: none">
                                                              </font>
                                                              <span style="text-decoration: none">
                                                                <a target="_blank" href="#" style="text-decoration: none">
                                                                  <font color="#000000" face="Arial, Helvetica, sans-serif" size="1">Guía Fácil Clave y Usuario PJ. 
                                                                  </font>
                                                                </a>
                                                              </span>
                                                            </td>
                                                          </tr>
                                                          
                                                          <tr>
                                                            <td>
                                                              <font color="#0000" face="Arial, Helvetica, sans-serif" size="1" style="text-decoration: none">
                                                              </font>
                                                              <span style="text-decoration: none">
                                                                <a target="_blank" href="#" style="text-decoration: none">
                                                                  <font color="#000000" face="Arial, Helvetica, sans-serif" size="1">Guía Fácil Clave y Usuario PN.
                                                                  </font>
                                                                </a>
                                                              </span>
                                                            </td>
                                                          

                                                          <tr>
                                                            <td>
                                                              <font color="#0000" face="Arial, Helvetica, sans-serif" size="1" style="text-decoration: none">
                                                              </font>
                                                              <span style="text-decoration: none">
                                                                <a target="_blank" href="#" style="text-decoration: none">
                                                                  <font color="#000000" face="Arial, Helvetica, sans-serif" size="1">Manual de Declaración Quincenal del Impuesto a las Grandes Transacciones Financieras.
                                                                  </font>
                                                                </a>
                                                              </span>
                                                            </td>
                                                          </tr>
                                                          </tr> -->
                                    </td>
                                  </tr>
                                </tbody>
                              </table>
                            </td>
                          </tr>
                        </tbody>
                      </table>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </td>
      </tr>
    </tbody>
  </table>
  </div>
</td>
</tr>
</tbody>
</table>
</td>
<td style="width:350px" valign="top">
  <table border="0" cellpadding="0" cellspacing="0" id="rg2063" summary="" width="100%">
    <tbody>
      <tr align="LEFT">
        <td valign="top">
          <div id="p78_17359_78_1_1">
            <table border="0" cellpadding="0" cellspacing="0" class="RegionNoBorder" width="100%">
              <tbody>
                <tr>
                  <td class="RegionHeaderColor">
                    <div id="pcnt78_17359_78_1_1">
                      <table align="center" cellpadding="0" cellspacing="0" width="195">
                        <tbody>
                          <tr valign="top">
                            <td align="center" height="53">
                              <a href="#" rel="noopener" target="_blank">
                                <img src="<?= asset('img/seniat-index-viejo/d33db9d26edc.png') ?>" />
                              </a>
                            </td>
                          </tr>
                          <!-- NOTICIAS -->
                          <tr border="1" bordercolor="#CC0000">
                            <td align="center" bordercolor="#FF0000">
                              <table border="1" bordercolor="#FF0000" height="507" style="border-collapse: collapse">
                                <tbody>
                                  <tr>
                                    <td bordercolor="#FFFFFF" valign="top" width="261">
                                      <table border="0" width="195">
                                        <tbody>
                                          <tr>
                                            <td>
                                              <span style="text-decoration: none">
                                                <a href="#" rel="noopener" style="text-decoration: none"
                                                  target="_blank">
                                                  <font color="#000000" face="Arial, Helvetica, sans-serif" size="2">
                                                    Encuentra aquí todas las noticias oficiales del Servicio Nacional
                                                    Integrado de Administración Aduanera y Tributaria (SENIAT).
                                                  </font>
                                                </a>
                                              </span>
                                            </td>
                                          </tr>
                                        </tbody>
                                      </table>
                                    </td>
                                  </tr>
                                </tbody>
                              </table>
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </td>
      </tr>
    </tbody>
  </table>
</td>
<td bgcolor="#FFFFFF" style="width:350px" valign="top">
  <table border="0" cellpadding="0" cellspacing="0" id="rg2061" summary="" width="100%">
    <tbody>
      <tr align="LEFT">
        <td valign="top">
          <div id="p78_29329_78_1_1">
            <table border="0" cellpadding="0" cellspacing="0" class="RegionNoBorder" width="100%">
              <tbody>
                <tr>
                  <td border="0" class="RegionHeaderColor" style="width:100%">
                    <div id="pcnt78_29329_78_1_1">
              <tbody>
                <tr valign="top">
                  <td align="center" colspan="3" height="18">
                    <a href="<?= base_url('/simulador/servicios_declaracion') ?>">
                      <img border="0" height="140" src="<?= asset('img/seniat-index-viejo/cd18ca8322c9.jpg') ?>"
                        width="150" />
                    </a>
                  </td>
                </tr>
                <br />
                <tr>
                  <td align="center" colspan="3">
                    <a href="#" rel="noopener" target="_blank">
                      <img border="0" src="<?= asset('img/seniat-index-viejo/d3386fccda34.gif') ?>" />
                    </a>
                    <br />
                    <br />
                  </td>
                </tr>
                <tr>
                  <td align="center" colspan="3">
                    <a href="#"><img border="0" src="<?= asset('img/seniat-index-viejo/ce41a4235d69.jpg') ?>" /></a>
                  </td>
                </tr>
                <tr>
                  <td align="center" colspan="3" height="18">
                    <a href="<?= base_url('/simulador/portal') ?>" style="padding: 0 20px;">
                      <img border="0" height="120" src="<?= asset('img/seniat-index-viejo/ed8aa2dc7132.jpeg') ?>"
                        style="margin: 20px 0px;" width="200" />
                    </a>
                  </td>
                </tr>
              </tbody>
          </div>
        </td>
      </tr>
    </tbody>
  </table>
  </div>
</td>
</tr>
</tbody>
</table>
</td>
</tr>
</tbody>
</table>
<table border="0" cellpadding="0" cellspacing="0" id="rg1566" style="height:100px" summary="" width="100%">
  <tbody>
    <tr align="LEFT">
      <td style="padding:0px 0px 0px 0px;width:100%;" valign="top">
        <div id="p78_22223_78_1_1">
          <table border="0" cellpadding="0" cellspacing="0" class="RegionNoBorder" width="100%">
            <tbody>
              <tr>
                <td class="RegionHeaderColor" style="width:100%">
                  <div id="pcnt78_22223_78_1_1">
                    <table align="center" bgcolor="#D4CBC6" bordercolor="#D4CBC6" cellpading="0" cellspacing="0"
                      height="1" valign="top" width="100%">
                      <tbody>
                        <tr>
                          <td>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                    <table align="center" bgcolor="#FFFFFF" border="0" cellpading="0" cellspacing="0" height="8"
                      valign="top" width="100%">
                      <tbody>
                        <tr>
                          <td>
                            <font color="#666666" face="Arial, Helvetica, sans-serif" size="1">  © Copyright,
                              --SENIAT--, Servicio Nacional Integrado de Administración Aduanera y Tributaria, todos
                              los derechos reservados.
                            </font>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </td>
    </tr>
  </tbody>
</table>
</td>
<td style="width:25%" valign="top">
  <table border="0" cellpadding="0" cellspacing="0" id="rg1550" style="height:629px" summary="" width="100%">
    <tbody>
      <tr>
        <td colspan="1" style="width:100%;">
          <!-- <img src="../../../images/pobtrans.gif" height="1" width="1" alt="" style="display:block"> -->
        </td>
      </tr>
    </tbody>
  </table>
</td>
</tr>
</tbody>
</table>
<!-- Page Metadata Generated On: 15-JAN-2014:16:46:03  Time Taken: 750 msecs -->

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const scope = document.querySelector('.seniat-scope');
    if (!scope) return;

    const links = scope.querySelectorAll('a');
    const baseUrl = '<?= rtrim(base_url(), '/') ?>';

    links.forEach(link => {
      const href = link.getAttribute('href');

      // Valid links that should be allowed
      const isSimulatorLink = href && (href.startsWith(baseUrl) || href.startsWith('/simulador'));
      
      if (!isSimulatorLink) {
        link.addEventListener('click', function (e) {
          e.preventDefault();
        });
        
        // Remove target="_blank" to prevent opening empty tabs before JS fires (just in case)
        if (link.getAttribute('target') === '_blank') {
          link.removeAttribute('target');
        }
      }
    });

    // Patch dropdown positioning: .seniat-scope now has position:relative,
    // so the dropdown's left/top are relative to it, not the page.
    // We override the legacy function to subtract .seniat-scope's page offset.
    if (typeof window.dropdownmenu === 'function') {
      const _origDropdown = window.dropdownmenu;
      window.dropdownmenu = function (obj, e, menucontents, menuwidth) {
        const result = _origDropdown.call(this, obj, e, menucontents, menuwidth);
        if (typeof dropmenuobj !== 'undefined' && dropmenuobj && scope) {
          const scopeX = getposOffset(scope, 'left');
          const scopeY = getposOffset(scope, 'top');
          const rawX = dropmenuobj.x - clearbrowseredge(obj, 'rightedge');
          // Siempre abrir hacia abajo: .sim-main tiene scroll vertical,
          // así que siempre hay espacio. Sin clearbrowseredge('bottomedge')
          // que erróneamente lo flipea hacia arriba por el header del layout.
          const rawY = dropmenuobj.y + obj.offsetHeight;
          dropmenuobj.style.left = (rawX - scopeX) + 'px';
          dropmenuobj.style.top = (rawY - scopeY) + 'px';
        }
        return result;
      };
    }
  });
</script>

</div><!-- /.seniat-scope -->
</div><!-- /.seniat-wrapper -->

<script>
document.addEventListener('DOMContentLoaded', function() {
    var params = new URLSearchParams(window.location.search);
    if (params.get('validado') !== '1') return;

    var emailEnviado = params.get('email') === '1';
    var resultado = params.get('resultado');
    var msg = '';

    if (resultado === 'ok') {
        if (emailEnviado) {
            msg = '✅ Validación exitosa. Se ha generado su RIF Sucesoral y se envió a su correo electrónico.';
        } else {
            msg = '✅ Validación exitosa, pero no se pudo enviar el correo. Contacte al administrador.';
        }
    }

    if (!msg) return;

    // Crear toast con estilos inline para evitar conflictos con seniat-scope
    var toast = document.createElement('div');
    toast.style.cssText = 'position:fixed;top:20px;right:20px;z-index:99999;max-width:420px;' +
        'background:#f0fdf4;color:#166534;border:1px solid #bbf7d0;border-radius:8px;' +
        'padding:14px 18px;font:500 14px/1.5 \"Plus Jakarta Sans\",-apple-system,BlinkMacSystemFont,\"Segoe UI\",Roboto,sans-serif;' +
        'box-shadow:0 4px 20px rgba(0,0,0,.12),0 1px 4px rgba(0,0,0,.06);' +
        'display:flex;align-items:center;gap:10px;opacity:0;transform:translateX(40px);' +
        'transition:opacity .3s ease,transform .3s ease;';

    var closeBtn = document.createElement('button');
    closeBtn.innerHTML = '✕';
    closeBtn.style.cssText = 'background:none;border:none;cursor:pointer;color:#166534;opacity:.5;' +
        'padding:2px 4px;border-radius:4px;font-size:14px;margin-left:8px;flex-shrink:0;';
    closeBtn.onmouseover = function() { this.style.opacity = '1'; };
    closeBtn.onmouseout = function() { this.style.opacity = '.5'; };

    var msgSpan = document.createElement('span');
    msgSpan.style.cssText = 'flex:1;';
    msgSpan.textContent = msg;

    toast.appendChild(msgSpan);
    toast.appendChild(closeBtn);
    document.body.appendChild(toast);

    // Animate in
    requestAnimationFrame(function() {
        requestAnimationFrame(function() {
            toast.style.opacity = '1';
            toast.style.transform = 'translateX(0)';
        });
    });

    function dismiss() {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(40px)';
        setTimeout(function() { toast.remove(); }, 300);
    }

    closeBtn.onclick = dismiss;
    setTimeout(dismiss, 6000);

    // Limpiar params de la URL sin recargar
    history.replaceState(null, '', window.location.pathname);
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/logged_layout.php';
?>