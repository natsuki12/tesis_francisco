<?php
/**
 * Sucesión Principal — Autoliquidación de Impuesto sobre Sucesiones
 * Standalone page: SENIAT header + full-width content (no sidebar)
 * Uses: logged_layout.php as outer shell + seniat-wrapper card isolation
 *
 * Receives from controller: $datos (array with all formatted fields, or null)
 */
$pageTitle = 'Sucesión Principal — Simulador';
$activePage = 'simulador';

// ─── Collect CSS for logged_layout.php ─────────────────────────────
$cssHtml = '<link rel="stylesheet" href="' . base_url('/assets/css/simulator/seniat_actual/sucesion/bienes_muebles/banco_legacy.css') . '">' . "\n";
$cssHtml .= '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">' . "\n";
$cssHtml .= '<link rel="stylesheet" href="' . base_url('/assets/css/simulator/seniat_actual/servicios_declaracion/bootstrap.min.css') . '">' . "\n";
$cssHtml .= '<link rel="stylesheet" href="' . base_url('/assets/css/simulator/seniat_actual/servicios_declaracion/bootstrap-icons.css') . '">' . "\n";
$cssHtml .= '<link rel="stylesheet" href="' . base_url('/assets/css/simulator/seniat_actual/sucesion/sucesion_principal.css') . '">' . "\n";
$cssHtml .= '<style>
.seniat-wrapper{background:var(--sim-white,#fff);border-radius:12px;box-shadow:var(--sim-shadow-lg,0 4px 6px rgba(0,0,0,.07));overflow:hidden;border:1px solid var(--sim-border,#dfe5ee);font-family:system-ui,-apple-system,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif;font-size:1rem;color:#212529;line-height:1.5}
.seniat-wrapper label,.seniat-wrapper h1,.seniat-wrapper h2,.seniat-wrapper h3,.seniat-wrapper h4,.seniat-wrapper h5,.seniat-wrapper h6{font-family:inherit;color:inherit}
#hamburgerMenu{display:none;position:absolute;top:100%;right:0;left:auto;z-index:9999}
#hamburgerMenu.show{display:block}
</style>';
$extraCss = $cssHtml;

$blueNavText = 'Autoliquidación de Impuesto sobre Sucesiones';

// Shorthand — all fields come pre-formatted from the controller
$d = $datos ?? [];

// ─── Resolve causante display name for the grey header bar ─────────
$headerUserName = '';
if (!empty($intento)) {
    $headerUserName = (new \App\Modules\Simulator\Services\BorradorService($intento))->getNombreCausanteDisplay();
} else {
    try {
        $assignModel  = new \App\Modules\Student\Models\StudentAssignmentModel();
        $attemptModel = new \App\Modules\Student\Models\StudentAttemptModel();
        $estudianteId = $assignModel->getEstudianteId((int) ($_SESSION['user_id'] ?? 0));
        if ($estudianteId && !empty($_SESSION['sim_asignacion_id'])) {
            $int = $attemptModel->getIntentoActivo((int) $_SESSION['sim_asignacion_id']);
            if ($int) $headerUserName = (new \App\Modules\Simulator\Services\BorradorService($int))->getNombreCausanteDisplay();
        }
    } catch (\Throwable $e) {}
}

ob_start();
?>
<div class="seniat-wrapper">

<!-- ═══ SENIAT Header: banner + grey bar + blue bar ═══ -->
<app-root _nghost-pgi-c36 ng-version=12.2.17><router-outlet _ngcontent-pgi-c36></router-outlet><app-inicio _nghost-pgi-c62><div _ngcontent-pgi-c62 class=container><div _ngcontent-pgi-c62 class="row align-items-center"><app-headersuc _ngcontent-pgi-c62 style=padding:0 _nghost-pgi-c59><img _ngcontent-pgi-c59 id=banner src="<?= base_url('/assets/img/simulator/seniat_actual/sucesion/banco/logo_banco.png') ?>" width=100%></app-headersuc></div><div _ngcontent-pgi-c62 class="row align-items-center" style=color:#fff;background-color:#d7d7d7><div _ngcontent-pgi-c62 class="bg-light clearfix"><div _ngcontent-pgi-c62 class=float-start><span _ngcontent-pgi-c62 style=color:black><?= htmlspecialchars($headerUserName) ?></span></div><div _ngcontent-pgi-c62 class=float-end><div style="position:relative;display:inline-block" id="hamburgerWrap"><a href="#" role="button" aria-expanded="false" class="nav-link dropdown-toggle link-secondary" id="hamburgerBtn" onclick="event.preventDefault();var m=document.getElementById('hamburgerMenu');m.classList.toggle('show')"><i class="bi bi-list"></i></a><ul class="dropdown-menu" id="hamburgerMenu" style="right:0;left:auto;min-width:180px"><li style="text-align:center"><a class="dropdown-item" href="<?= base_url('/simulador/servicios_declaracion/logout') ?>" style="color:#212529;text-decoration:none;font:13px Arial,Helvetica,sans-serif;padding:8px 16px">Cerrar sesion</a></li></ul></div><ul _ngcontent-pgi-c62 class="dropdown-menu sf-hidden"></ul></div></div></div><div _ngcontent-pgi-c62 class="row bg-color"><div _ngcontent-pgi-c62 class=col-sm-12 style=text-align:center;color:white><span _ngcontent-pgi-c62 style=width:100vh><?= $blueNavText ?></span></div></div>

  <!-- ═══ Main content (full width, no sidebar) ═══ -->
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
                    <tr align="center">
                      <td colspan="2"><strong>Datos de la Sucesion</strong></td>
                    </tr>
                    <tr>
                      <td align="right"><strong>RIF</strong></td>
                      <td><?= htmlspecialchars($d['rif'] ?? '') ?></td>
                    </tr>
                    <tr>
                      <td align="right"><strong>Nombre(s)</strong></td>
                      <td><?= htmlspecialchars($d['nombre_sucesion'] ?? '') ?></td>
                    </tr>
                    <tr>
                      <td align="right"><strong>E-mail</strong></td>
                      <td><?= htmlspecialchars($d['email'] ?? '') ?></td>
                    </tr>
                    <tr>
                      <td align="right"><strong>Fecha de Fallecimiento</strong></td>
                      <td><?= htmlspecialchars($d['fecha_fallecimiento'] ?? '') ?></td>
                    </tr>
                    <tr>
                      <td align="right"><strong>Fecha de Vencimiento</strong></td>
                      <td><?= htmlspecialchars($d['fecha_vencimiento'] ?? '') ?></td>
                    </tr>
                    <tr>
                      <td align="right"><strong>U.T. Aplicable</strong></td>
                      <td><?= htmlspecialchars($d['ut_aplicable'] ?? '') ?></td>
                    </tr>
                  </tbody>
                  <tbody>
                    <tr align="center">
                      <td colspan="2"><strong>Datos del Causante</strong></td>
                    </tr>
                    <tr>
                      <td align="right"><strong>Rif/C.I.</strong></td>
                      <td><?php
                        $rifC = $d['rif_causante'] ?? '';
                        $cedC = $d['cedula_causante'] ?? '';
                        echo htmlspecialchars($rifC && $cedC ? "$rifC / $cedC" : ($rifC ?: $cedC));
                      ?></td>
                    </tr>
                    <tr>
                      <td align="right"><strong>Nombre(s)</strong></td>
                      <td><?= htmlspecialchars($d['nombre_causante'] ?? '') ?></td>
                    </tr>
                    <tr>
                      <td align="right"><strong>Rif del Representante Legal</strong></td>
                      <td><?= htmlspecialchars($d['rif_representante'] ?? '') ?></td>
                    </tr>
                    <tr>
                      <td align="right"><strong>Nombre del Representante Legal</strong></td>
                      <td><?= htmlspecialchars($d['nombre_representante'] ?? '') ?></td>
                    </tr>
                    <tr>
                      <td align="right"><strong>Domicilio Fiscal</strong></td>
                      <td><?= htmlspecialchars($d['domicilio'] ?? '') ?></td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <br>

              <!-- Tabla de Herederos -->
              <table class="table table-bordered table-striped table-sm lenletra">
                <thead>
                  <tr align="center">
                    <td colspan="4"><strong>Herederos o Legatarios</strong></td>
                  </tr>
                  <tr>
                    <th scope="col">Caracter</th>
                    <th scope="col">Apellido(s)</th>
                    <th scope="col">Nombre(s)</th>
                    <th scope="col">RIF/C.I./Pasaporte</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($d['herederos'])): ?>
                    <tr><td colspan="4" class="text-center">No hay herederos registrados</td></tr>
                  <?php else: ?>
                    <?php foreach ($d['herederos'] as $h): ?>
                    <tr>
                      <td>HEREDERO</td>
                      <td><?= htmlspecialchars($h['apellido'] ?? '') ?></td>
                      <td><?= htmlspecialchars($h['nombre'] ?? '') ?></td>
                      <td><?= htmlspecialchars($h['idDocumento'] ?? '') ?></td>
                    </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>

              <br>

              <!-- Botones de acción -->
              <div class="border-top">
                <div class="row">
                  <div class="col-md-6 col-sm-6 col-lg-6">
                    <?php if (!empty($mostrarAcciones)): ?>
                      <strong class="py-2">
                        <h6>El RIF ya posee una declaración ¿Desea sustituir esta declaración?</h6>
                      </strong>
                    <?php else: ?>
                      <strong class="py-2">
                        <h6>¿Desea continuar con el proceso de Declaración?</h6>
                      </strong>
                    <?php endif; ?>
                    <br>
                    <button class="btn btn-sm btn-danger"
                      onclick="window.location.href='<?= base_url('/simulador/servicios_declaracion/dashboard') ?>'">No&nbsp;<i
                        class="bi-x-circle"></i></button>&nbsp;
                    <button class="btn btn-sm btn-danger"
                      onclick="document.getElementById('avisoModal').style.display='flex'">Si&nbsp;<i
                        class="bi-check-circle"></i></button>
                  </div>
                  <?php if (!empty($mostrarAcciones)): ?>
                    <div class="col-md-6 col-sm-6 col-lg-6 text-center">
                      <strong class="py-2">
                        <h6>Para ver su declaración e imprimir, por favor presione el siguiente botón</h6>
                      </strong>
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
      <div id="avisoModal"
        style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:1050;align-items:center;justify-content:center">
        <div style="background:#fff;border-radius:4px;width:100%;max-width:500px;box-shadow:0 5px 15px rgba(0,0,0,.5)">
          <div
            style="display:flex;justify-content:space-between;align-items:center;padding:1rem;border-bottom:1px solid #dee2e6">
            <h5 style="margin:0;font-weight:500">Aviso</h5>
            <button onclick="document.getElementById('avisoModal').style.display='none'"
              style="background:none;border:none;font-size:1.5rem;cursor:pointer;padding:0;line-height:1">&times;</button>
          </div>
          <div style="padding:1rem">
            <p>Si la información de los Herederos o Legatarios no es correcta o no corresponde, por favor diríjase a su
              Gerencia de Adscripción para actualizar su RIF.</p>
            <p>Si está de acuerdo, presione Aceptar. Si desea salir, presione Cancelar.</p>
          </div>
          <div style="display:flex;justify-content:flex-end;gap:0.5rem;padding:1rem;border-top:1px solid #dee2e6">
            <button onclick="document.getElementById('avisoModal').style.display='none'"
              class="btn btn-sm btn-outline-secondary">Cancelar</button>
            <button onclick="window.location.href='<?= base_url('/simulador/sucesion/herencia') ?>'"
              class="btn btn-sm btn-danger">Aceptar</button>
          </div>
        </div>
      </div>

    </div>
  </div>
</div></app-inicio></app-root>

<script>document.addEventListener("click", function (e) { var w = document.getElementById("hamburgerWrap"); if (w && !w.contains(e.target)) { document.getElementById("hamburgerMenu").classList.remove("show") } })</script>

</div><!-- /.seniat-wrapper -->

<?php
$content = ob_get_clean();
include __DIR__ . '/../../../layouts/logged_layout.php';
?>