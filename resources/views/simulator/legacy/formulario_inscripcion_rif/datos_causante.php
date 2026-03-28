<?php
declare(strict_types=1);

$pageTitle = 'Datos Causante — Simulador';
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

<link rel="stylesheet" href="<?= asset('css/simulator/legacy/formulario_inscripcion_rif/datos_causante_sc.css') ?>">
<link rel="stylesheet" href="<?= asset('css/simulator/legacy/inscripcion_rif.css') ?>">
<script charset="utf-8" src="<?= asset('js/simulator/legacy/inscripcion_rif.js') ?>" type="text/javascript"></script>

<div class="seniat-wrapper">
    <div class="seniat-scope">
        <div class="Bodyid1siteid0" style="margin:0; padding:0;">
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






            <table>
                <tbody>
                    <tr>
                        <td valign=top>

                            <div class="glossymenu">
                                <span class="menuitem">Menú</span>


                                <a class="menuitem"
                                    href="<?= base_url('/simulador/inscripcion-rif/datos-basicos') ?>">Datos
                                    Básicos</a>


                                <a class="menuitem"
                                    href="<?= base_url('simulador/inscripcion-rif/direcciones') ?>">Direcciones</a>


                                <a class="menuitem"
                                    href="<?= base_url('simulador/inscripcion-rif/relaciones') ?>">Relaciones</a>


                                <a class="menuitem" href="javascript:void(0)">Ver Planilla</a>


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

                            <form name=DatosBasicosNaturalForm method=post
                                action=/rifcontribuyente/datosbasicosnatural.do>

                                <table id=tblPrincipal width=95% align=center cellpadding=2 cellspacing=0 border=0>
                                    <tbody>
                                        <tr>
                                            <td class=letrasSmall width=100% colspan=1 align=center><b>Datos
                                                    Básicos</b><br>
                                                <hr>
                                            </td>
                                        </tr>
                                </table>
                                <br>
                                <?php
                                $cedulaValue = '';
                                $tipoPersona = 'SUCESION';
                                $fechaFallecimiento = '';
                                $apellidosValue = '';
                                $nombresValue = '';
                                $sexoValue = '';
                                $estadoCivilValue = '';
                                $nacionalidadValue = '';
                                $fechaCierreValue = '31/12/' . date('Y');
                                $correoValue = '';

                                if (isset($intento) && !empty($intento['borrador_json'])) {
                                    $payload = json_decode($intento['borrador_json'], true);
                                    $db = $payload['datos_basicos'] ?? [];

                                    if (isset($payload['tipo_sucesion']) && $payload['tipo_sucesion'] === 'Con_Cedula') {
                                        if (!empty($db['cedula'])) {
                                            $cedulaValue = htmlspecialchars($db['cedula']);
                                        }
                                    }

                                    if (!empty($db['fecha_fallecimiento'])) {
                                        $rawFecha = $db['fecha_fallecimiento'];
                                        $dt = DateTime::createFromFormat('d/m/Y', $rawFecha);
                                        if (!$dt) $dt = DateTime::createFromFormat('Y-m-d', $rawFecha);
                                        $fechaFallecimiento = $dt ? $dt->format('d/m/Y') : htmlspecialchars($rawFecha);
                                    }

                                    if (!empty($db['apellidos'])) $apellidosValue = htmlspecialchars($db['apellidos']);
                                    if (!empty($db['nombres']))   $nombresValue   = htmlspecialchars($db['nombres']);
                                    if (!empty($db['sexo']))      $sexoValue      = $db['sexo'];
                                    if (!empty($db['estado_civil'])) $estadoCivilValue = $db['estado_civil'];
                                    if (!empty($db['nacionalidad'])) $nacionalidadValue = $db['nacionalidad'];
                                    if (!empty($db['email_sucesion'])) $correoValue = htmlspecialchars($db['email_sucesion']);

                                    if (!empty($db['fecha_cierre_fiscal'])) {
                                        $rawFC = $db['fecha_cierre_fiscal'];
                                        $dtFC = DateTime::createFromFormat('d/m/Y', $rawFC);
                                        if (!$dtFC) $dtFC = DateTime::createFromFormat('Y-m-d', $rawFC);
                                        $fechaCierreValue = $dtFC ? $dtFC->format('d/m/Y') : htmlspecialchars($rawFC);
                                    }
                                }
                                ?>
                                <table cellspacing=2 cellpadding=1 border=0 width=95% align=center>
                                    <tbody>
                                        <tr>
                                            <td class=tablaSubTitulo colspan=3 width=50%
                                                style=FONT-SIZE:7pt;HEIGHT:20px>
                                                Apellidos </td>
                                            <td class=tablaSubTitulo colspan=3 width=50%
                                                style=FONT-SIZE:7pt;HEIGHT:20px>Nombres
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class=letrasSmall colspan=3 width=50%><input type=text name=apellido
                                                    maxlength=50 size=70 value="<?= $apellidosValue ?>"
                                                    style=font-family:Verdana;font-size:10;width:100%;height:18
                                                    id=apellido>
                                            </td>
                                            <td class=letrasSmall colspan=3 width=50%><input type=text name=nombre
                                                    maxlength=50 size=70 value="<?= $nombresValue ?>"
                                                    style=font-family:Verdana;font-size:10;width:100%;height:18
                                                    id=nombre></td>
                                        </tr>
                                        <tr>



                                            <td class=tablaSubTitulo colspan=3 width=50%
                                                style=FONT-SIZE:7pt;HEIGHT:20px>Cédula
                                            </td>


                                            <td class=tablaSubTitulo colspan=3 width=50%
                                                style=FONT-SIZE:7pt;HEIGHT:20px>Tipo de
                                                Persona </td>
                                        </tr>
                                        <tr>
                                            <td class=letrasSmall colspan=3 width=50%><input type=text name=cedula
                                                    size=50 value="<?= $cedulaValue ?>" readonly
                                                    style=border-style:none;font-family:Verdana;font-size:10;width:100%;height:18
                                                    id=cedula></td>
                                            <td class=letrasSmall colspan=3 width=50%><input type=text
                                                    name=personalidadDescripcion size=50 value="<?= $tipoPersona ?>"
                                                    readonly
                                                    style=border-style:none;font-family:Verdana;font-size:10;width:100%;height:18
                                                    id=personalidadDescripcion></td>
                                        </tr>
                                        <tr>



                                            <td class=tablaSubTitulo colspan=2 width=33%
                                                style=FONT-SIZE:7pt;HEIGHT:20px>Fecha
                                                de Fallecimiento </td>


                                            <td class=tablaSubTitulo colspan=2 width=33%
                                                style=FONT-SIZE:7pt;HEIGHT:20px>Sexo
                                            </td>
                                            <td class=tablaSubTitulo colspan=2 width=34%
                                                style=FONT-SIZE:7pt;HEIGHT:20px>Estado
                                                Civil </td>
                                        </tr>
                                        <tr>
                                            <td class=letrasSmall colspan=2 width=33%>
                                                <input type=text name=fechaNacimiento maxlength=10 value="<?= $fechaFallecimiento ?>"
                                                    style=font-family:Verdana;font-size:10;width:50%;height:18
                                                    id=fechaNacimiento onblur="ValidDate(this)"
                                                    onchange="ValidDate(this)" onkeypress="FormatoFecha(this);">
                                                <a href=# id=bFechaNacimiento><img id=p_calend name=p_calend
                                                        src='data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="24" height="20"><rect fill-opacity="0"/></svg>'
                                                        width=24 height=20 border=0 alt=Calendario align=absmiddle
                                                        style="background-blend-mode:normal!important;background-clip:content-box!important;background-position:50% 50%!important;background-color:rgba(0,0,0,0)!important;background-image:var(--sf-img-4)!important;background-size:100% 100%!important;background-origin:content-box!important;background-repeat:no-repeat!important"></a>
                                            </td>
                                            <td class=letrasSmall colspan=2 width=33%>

                                                <input type=radio name=radiosexo
                                                    style=font-family:Verdana;font-size:8;height:15 value=F
                                                    <?= ($sexoValue === 'F') ? 'checked' : '' ?>><span
                                                    class=letras>FEMENINO</span>
                                                <input type=radio name=radiosexo
                                                    style=font-family:Verdana;font-size:8;height:15 value=M
                                                    <?= ($sexoValue === 'M') ? 'checked' : '' ?>><span class=letras>MASCULINO</span>
                                            </td>
                                            <td class=letrasSmall colspan=2 width=34%>
                                                <select name=estadoCivil.codigo
                                                    style=font-family:Verdana;font-size:10;width:100%;height:18
                                                    id=estadoCivil.codigo>
                                                    <option value>SELECCIONAR</option>
                                                    <option value=2 <?= ($estadoCivilValue === 'CASADO') ? 'selected' : '' ?>>CASADO</option>
                                                    <option value=5 <?= ($estadoCivilValue === 'CONCUBINATO') ? 'selected' : '' ?>>CONCUBINATO</option>
                                                    <option value=4 <?= ($estadoCivilValue === 'DIVORCIADO') ? 'selected' : '' ?>>DIVORCIADO</option>
                                                    <option value=1 <?= ($estadoCivilValue === 'SOLTERO') ? 'selected' : '' ?>>SOLTERO</option>
                                                    <option value=3 <?= ($estadoCivilValue === 'VIUDO') ? 'selected' : '' ?>>VIUDO
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class=tablaSubTitulo colspan=2 width=33%
                                                style=FONT-SIZE:7pt;HEIGHT:20px>
                                                Domiciliado en el País? </td>
                                            <td class=tablaSubTitulo colspan=4 width=67%
                                                style=FONT-SIZE:7pt;HEIGHT:20px>
                                                Nacionalidad </td>
                                        </tr>
                                        <tr>
                                            <td class=letrasSmall colspan=2 width=33%><input type=text
                                                    name=domiciliadoDescripcion value=SI readonly
                                                    style=border-style:none;font-family:Verdana;font-size:10;width:100%;height:18
                                                    id=domiciliadoDescripcion></td>



                                            <td class=letrasSmall colspan=4 width=67%>
                                                <select name=nacionalidad.codigo
                                                    style=font-family:Verdana;font-size:10;width:100%;height:18
                                                    id="nacionalidad.codigo">
                                                    <option value>SELECCIONAR</option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class=tablaSubTitulo colspan=2 width=33%
                                                style=FONT-SIZE:7pt;HEIGHT:20px>Fecha
                                                Cierre Fiscal </td>
                                            <td class=tablaSubTitulo colspan=4 width=67%
                                                style=FONT-SIZE:7pt;HEIGHT:20px>Correo
                                                Electrónico </td>
                                        </tr>
                                        <tr>
                                            <td class=letrasSmall colspan=2 width=33%>



                                                <input type=text name=fechaCierreFiscal maxlength=10 value="<?= $fechaCierreValue ?>"
                                                    style=font-family:Verdana;font-size:10;width:80%;height:18
                                                    id=fechaCierreFiscal onblur="ValidDateCierre(this)"
                                                    onchange="ValidDateCierre(this)" onkeypress="FormatoFecha(this);">


                                                <a href=# id=bFechaCF><img id=p_calendFC name=p_calendFC
                                                        src='data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="24" height="20"><rect fill-opacity="0"/></svg>'
                                                        width=24 height=20 border=0 alt=Calendario align=absmiddle
                                                        style="background-blend-mode:normal!important;background-clip:content-box!important;background-position:50% 50%!important;background-color:rgba(0,0,0,0)!important;background-image:var(--sf-img-4)!important;background-size:100% 100%!important;background-origin:content-box!important;background-repeat:no-repeat!important"></a>
                                            </td>
                                            <td class=letrasSmall colspan=4 width=67%><input type=text name=correo
                                                    maxlength=50 size=60 value="<?= $correoValue ?>"
                                                    style=font-family:Verdana;font-size:10;width:100%;height:18
                                                    id=correo></td>
                                        </tr>
                                </table>
                                <br>
                                <br>
                                <br>
                                <table id=tblPrincipal width=95% align=center cellpadding=2 cellspacing=0 border=0>
                                    <tbody>
                                        <tr>
                                            <td align=center>
                                                <input type=submit name=guardar value=Guardar class=boton id=guardar>
                                            </td>
                                        </tr>
                                </table>
                            </form>

                        </td>
                    </tr>
            </table>
            <br><br><br>

            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    var savedNacionalidad = <?= json_encode($nacionalidadValue) ?>;

                    fetch('<?= base_url('api/paises') ?>')
                        .then(response => response.json())
                        .then(data => {
                            if (data.success && data.data) {
                                const select = document.getElementById('nacionalidad.codigo');

                                data.data.forEach(pais => {
                                    const option = document.createElement('option');
                                    option.value = pais.id;
                                    option.textContent = pais.nombre_pais || pais.pais || pais.nombre || pais.descripcion;

                                    // Preselect: saved value from borrador, or VENEZUELA (id 190) by default
                                    if (savedNacionalidad) {
                                        if (String(pais.id) === String(savedNacionalidad)) {
                                            option.selected = true;
                                        }
                                    } else {
                                        if (String(pais.id) === '190' || (option.textContent || '').toUpperCase() === 'VENEZUELA') {
                                            option.selected = true;
                                        }
                                    }
                                    select.appendChild(option);
                                });
                            } else {
                                console.error('Error fetching countries:', data.message);
                            }
                        })
                        .catch(error => console.error('Error fetching countries:', error));
                });
            </script>
            <script>
                Calendar.setup({
                    inputField: "fechaNacimiento",
                    ifFormat: "%d/%m/%Y",
                    button: "bFechaNacimiento",
                    align: "br",
                    singleClick: true,
                    weekNumbers: false,
                    firstDay: 0,
                    daFormat: "%d/%m/%Y"
                });

                Calendar.setup({
                    inputField: "fechaCierreFiscal",
                    ifFormat: "%d/%m/%Y",
                    button: "bFechaCF",
                    align: "br",
                    singleClick: true,
                    weekNumbers: false,
                    firstDay: 0,
                    daFormat: "%d/%m/%Y"
                });
            </script>

            <?php
            // Expose intento data to JS for save functionality
            $intentoId = $intento['id'] ?? null;
            $borrador = null;
            if ($intento && !empty($intento['borrador_json'])) {
                $borrador = json_decode($intento['borrador_json'], true);
            }
            ?>
            <script>
                window.simIntentoId = <?= json_encode($intentoId) ?>;
                window.simBorrador = <?= json_encode($borrador) ?>;
                window.simBaseUrl = <?= json_encode(rtrim(base_url(''), '/')) ?>;
            </script>

            <script>
            document.addEventListener('DOMContentLoaded', function () {
                var form = document.querySelector('form[name="DatosBasicosNaturalForm"]');
                if (!form) return;

                form.addEventListener('submit', function (e) {
                    e.preventDefault();

                    // Prevent double-click silently — must be first check
                    if (window._savingDatosBasicos) return;

                    // --- Validación ---
                    var errors = [];

                    var apellido = (document.getElementById('apellido').value || '').trim();
                    var nombre = (document.getElementById('nombre').value || '').trim();
                    var sexoRadios = document.getElementsByName('radiosexo');
                    var sexoSelected = false;
                    var sexoValue = '';
                    for (var i = 0; i < sexoRadios.length; i++) {
                        if (sexoRadios[i].checked) {
                            sexoSelected = true;
                            sexoValue = sexoRadios[i].value;
                            break;
                        }
                    }
                    var estadoCivil = document.getElementById('estadoCivil.codigo');
                    var estadoCivilValue = estadoCivil ? estadoCivil.value : '';
                    var estadoCivilText = estadoCivil ? estadoCivil.options[estadoCivil.selectedIndex].text : '';
                    var correo = (document.getElementById('correo').value || '').trim();
                    var fechaFallecimiento = (document.getElementById('fechaNacimiento').value || '').trim();
                    var nacionalidad = document.getElementById('nacionalidad.codigo');
                    var nacionalidadValue = nacionalidad ? nacionalidad.value : '';
                    var fechaCierre = (document.getElementById('fechaCierreFiscal').value || '').trim();

                    if (!apellido) errors.push('Debe ingresar información en el campo Apellidos.');
                    if (!nombre) errors.push('Debe ingresar información en el campo Nombres.');
                    if (!fechaFallecimiento) errors.push('Debe ingresar información en el campo Fecha de Fallecimiento.');
                    if (!sexoSelected) errors.push('Debe ingresar información en el campo Sexo.');
                    if (!estadoCivilValue) errors.push('Debe ingresar información en el campo Estado Civil.');
                    if (!nacionalidadValue) errors.push('Debe ingresar información en el campo Nacionalidad.');
                    if (!fechaCierre) errors.push('Debe ingresar información en el campo Fecha Cierre Fiscal.');
                    if (!correo) errors.push('Debe ingresar información en el campo Correo Electrónico.');

                    if (errors.length > 0) {
                        alert(errors.join('\n'));
                        return;
                    }

                    // --- Convertir fechas dd/mm/yyyy → yyyy-mm-dd ---
                    function toIso(ddmmyyyy) {
                        var parts = ddmmyyyy.split('/');
                        if (parts.length === 3) return parts[2] + '-' + parts[1] + '-' + parts[0];
                        return ddmmyyyy;
                    }

                    // --- Construir datos_basicos para el borrador ---
                    var cedula = (document.getElementById('cedula').value || '').trim();
                    var domiciliado = (document.getElementById('domiciliadoDescripcion').value || '').trim();

                    var borrador = window.simBorrador || {};
                    borrador.datos_basicos = borrador.datos_basicos || {};

                    // Merge form data into existing datos_basicos
                    var db = borrador.datos_basicos;
                    db.apellidos = apellido.toUpperCase();
                    db.nombres = nombre.toUpperCase();
                    if (cedula) db.cedula = cedula;
                    db.fecha_fallecimiento = toIso(fechaFallecimiento);
                    db.sexo = sexoValue;
                    db.estado_civil = estadoCivilText;
                    db.domiciliado_pais = (domiciliado === 'SI') ? 1 : 0;
                    db.nacionalidad = nacionalidadValue;
                    db.fecha_cierre_fiscal = toIso(fechaCierre);
                    db.email_sucesion = correo;

                    // --- Guardar via API ---
                    if (!window.simIntentoId) {
                        alert('Error: No se encontró un intento activo.');
                        return;
                    }

                    window._savingDatosBasicos = true;

                    var apiUrl = (window.simBaseUrl || '') + '/api/intentos/' + window.simIntentoId + '/guardar';

                    fetch(apiUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            borrador: borrador,
                            paso_actual: 1
                        })
                    })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (data.ok) {
                            window.simBorrador = borrador;
                        } else {
                            console.warn('Guardar retornó ok=false:', data);
                        }
                    })
                    .catch(function (error) {
                        console.error('Error:', error);
                    })
                    .finally(function () {
                        window._savingDatosBasicos = false;
                    });
                });
            });
            </script>
        </div><!-- /.Bodyid1siteid0 -->
    </div><!-- /.seniat-scope -->
</div><!-- /.seniat-wrapper -->

<?php
$content = ob_get_clean();
include __DIR__ . '/../../../layouts/logged_layout.php';
?>
