<?php
/**
 * Sucesión Principal — Autoliquidación de Impuesto sobre Sucesiones
 * Standalone page: header partial + full-width content (no sidebar)
 *
 * Step 1: Raw HTML extracted from SENIAT original (no base64 yet)
 *         Once verified, will integrate with sim_header layout.
 */
$blueNavText = 'Autoliquidación de Impuesto sobre Sucesiones';
$extraCss = [
    '/assets/css/simulator/seniat_actual/servicios_declaracion/bootstrap.min.css',
    '/assets/css/simulator/seniat_actual/servicios_declaracion/bootstrap-icons.css',
    '/assets/css/simulator/seniat_actual/sucesion/sucesion_principal.css'
];
include __DIR__ . '/../../../layouts/partials/sim_header.php';
?>

<!-- Main content row (full width, no sidebar) -->
<div _ngcontent-pgi-c62 class=row>
<div _ngcontent-pgi-c62 id=divHijo class=col-sm-12>

<p class="text-center"><strong>La declaración será registrada bajo la siguiente información fiscal:</strong></p>

<div class="shadow-lg p-3 mb-5 bg-body rounded lenletra">
  <div class="card">
    <div class="card-header">Información Fiscal</div>
    <div class="card-body">
      <div>
        <!-- Recuadro interno con borde -->
        <div class="border">
          <table class="table table-borderless table-sm lenletra">
            <tbody>
              <tr align="center"><td colspan="2"><strong>Datos de la Sucesion</strong></td></tr>
              <tr><td align="right"><strong>RIF</strong></td><td>J504705900</td></tr>
              <tr><td align="right"><strong>Nombre(s)</strong></td><td>SUCESION BAUZA MARIN, RAMON ERNESTO</td></tr>
              <tr><td align="right"><strong>E-mail</strong></td><td>RAMONBAUZA12345@GMAIL.COM</td></tr>
              <tr><td align="right"><strong>Fecha de Fallecimiento</strong></td><td>21/04/2022</td></tr>
              <tr><td align="right"><strong>Fecha de Vencimiento</strong></td><td>17/01/2023</td></tr>
              <tr><td align="right"><strong>U.T. Aplicable</strong></td><td>0,4000000000</td></tr>
            </tbody>
            <tbody>
              <tr align="center"><td colspan="2"><strong>Datos del Causante</strong></td></tr>
              <tr><td align="right"><strong>Rif/C.I.</strong></td><td>J504705900 / 6145727</td></tr>
              <tr><td align="right"><strong>Nombre(s)</strong></td><td>BAUZA MARIN, RAMON ERNESTO</td></tr>
              <tr><td align="right"><strong>Rif del Representante Legal</strong></td><td>V069727138</td></tr>
              <tr><td align="right"><strong>Nombre del Representante Legal</strong></td><td>PEDRONI LEPERVANCHE, PAOLA MARIA</td></tr>
              <tr><td align="right"><strong>Domicilio Fiscal</strong></td><td>CALLE OESTE CASA CONJUNTO RESIDENCIAL LAS TUNAS NRO 04-04 NO APLICA URBANIZACION MANEIRO CIUDAD PAMPATAR PARROQUIA: CAPITAL MANEIRO MUNICIPIO: MANEIRO ESTADO: NUEVA ESPARTA</td></tr>
            </tbody>
          </table>
        </div>

        <br>

        <!-- Tabla de Herederos -->
        <table class="table table-bordered table-striped table-sm lenletra">
          <thead>
            <tr align="center"><td colspan="4"><strong>Herederos o Legatarios</strong></td></tr>
            <tr>
              <th scope="col">Caracter</th>
              <th scope="col">Apellido(s)</th>
              <th scope="col">Nombre(s)</th>
              <th scope="col">RIF/C.I./Pasaporte</th>
            </tr>
          </thead>
          <tbody>
            <tr><td>HEREDERO</td><td>BAUZA PEDRONI</td><td>RAMON ERNESTO</td><td>V213264954</td></tr>
            <tr><td>HEREDERO</td><td>BAUZA PEDRONI</td><td>ANDRES ALEJANDRO</td><td>V213264962</td></tr>
            <tr><td>HEREDERO</td><td>PEDRONI LEPERVANCHE</td><td>PAOLA MARIA</td><td>V069727138</td></tr>
          </tbody>
        </table>

        <br>

        <!-- Botones de acción -->
        <div class="border-top">
          <div class="row">
            <div class="col-md-6 col-sm-6 col-lg-6">
              <?php if (!empty($mostrarAcciones)): ?>
              <strong class="py-2"><h6>El RIF ya posee una declaración ¿Desea sustituir esta declaración?</h6></strong>
              <?php else: ?>
              <strong class="py-2"><h6>¿Desea continuar con el proceso de Declaración?</h6></strong>
              <?php endif; ?>
              <br>
              <button class="btn btn-sm btn-danger" onclick="window.location.href='<?= base_url('/simulador/servicios_declaracion/dashboard') ?>'">No&nbsp;<i class="bi-x-circle"></i></button>&nbsp;
              <button class="btn btn-sm btn-danger" onclick="document.getElementById('avisoModal').style.display='flex'">Si&nbsp;<i class="bi-check-circle"></i></button>
            </div>
            <?php if (!empty($mostrarAcciones)): ?>
            <div class="col-md-6 col-sm-6 col-lg-6 text-center">
              <strong class="py-2"><h6>Para ver su declaración e imprimir, por favor presione el siguiente botón</h6></strong>
              <button class="btn btn-sm btn-danger">Ver Declaración&nbsp;<i class="bi-eye"></i></button>&nbsp;
            </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Aviso -->
<div id="avisoModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:1050;align-items:center;justify-content:center">
  <div style="background:#fff;border-radius:4px;width:100%;max-width:500px;box-shadow:0 5px 15px rgba(0,0,0,.5)">
    <div style="display:flex;justify-content:space-between;align-items:center;padding:1rem;border-bottom:1px solid #dee2e6">
      <h5 style="margin:0;font-weight:500">Aviso</h5>
      <button onclick="document.getElementById('avisoModal').style.display='none'" style="background:none;border:none;font-size:1.5rem;cursor:pointer;padding:0;line-height:1">&times;</button>
    </div>
    <div style="padding:1rem">
      <p>Si la información de los Herederos o Legatarios no es correcta o no corresponde, por favor diríjase a su Gerencia de Adscripción para actualizar su RIF.</p>
      <p>Si está de acuerdo, presione Aceptar. Si desea salir, presione Cancelar.</p>
    </div>
    <div style="display:flex;justify-content:flex-end;gap:0.5rem;padding:1rem;border-top:1px solid #dee2e6">
      <button onclick="document.getElementById('avisoModal').style.display='none'" class="btn btn-sm btn-secondary">Cancelar</button>
      <button onclick="window.location.href='<?= base_url('/simulador/sucesion/herencia') ?>'" class="btn btn-sm btn-primary">Aceptar</button>
    </div>
  </div>
</div>

</div>
</div></div></app-inicio></app-root>

<script>document.addEventListener("click",function(e){var w=document.getElementById("hamburgerWrap");if(w&&!w.contains(e.target)){document.getElementById("hamburgerMenu").style.display="none"}})</script>
</body>
</html>
