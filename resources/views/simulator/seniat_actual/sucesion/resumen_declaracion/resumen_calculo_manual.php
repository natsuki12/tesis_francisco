<?php
/**
 * Cálculo Manual Cuota Parte Hereditaria
 *
 * Réplica del flujo SENIAT:
 *  - Tabla 1 (input):  7 columnas, Cuota y Reducción editables
 *  - Tabla 2 (result): 11 columnas, todos readonly (aparece tras "Calcular")
 *
 * Variables recibidas desde SucesionController::calculoManual():
 *   $datos['ut']             — Valor UT aplicada
 *   $datos['total_impuesto'] — Total Impuesto a Pagar formateado
 *   $datos['herederos']      — Array con datos + cálculos por heredero
 *   $datos['fmt']            — Función formatDecimal($float)
 */
ob_start();
$activeMenu = 'resumen';
$activeItem = 'Ver Resumen';
$breadcrumbs = [
    ['label' => 'Inicio', 'url' => '/simulador/servicios_declaracion/dashboard'],
    ['label' => 'Resumen Declaración'],
];

// ── Helpers de formato ──
$herederos = $datos['herederos'] ?? [];
$fmt       = $datos['fmt'] ?? function (float $v) { return number_format($v, 2, ',', '.'); };
$ut        = $datos['ut'] ?? '0,00';
$totalImp  = $datos['total_impuesto'] ?? '0,00';
?>

<div class="shadow-lg p-3 mb-5 bg-body rounded">
    <div class="card">
        <!-- ═══ Card Header ═══ -->
        <div class="card-header">
            <div class="bg-light clearfix">
                <div class="float-start">
                    <span><strong>Cálculo Manual Cuota Parte Hereditaria</strong></span>
                </div>
                <div class="float-end">
                    <a href="<?= base_url('/simulador/sucesion/resumen_declaracion') ?>"
                       class="btn btn-light" title="Regresar">
                        <i class="bi bi-arrow-clockwise"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- ═══ Card Body ═══ -->
        <div class="card-body">
            <div>
                <!-- ════════════════════════════════════════════
                     FORMULARIO: Inputs + Tabla 1 (Input)
                     ════════════════════════════════════════════ -->
                <form id="formCalculoManual" novalidate>
                    <!-- Floating inputs row: UT + Total Impuesto -->
                    <div class="row py-3">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <div class="form-floating">
                                    <input id="ut" placeholder="#" type="text" readonly
                                           class="form-control form-control-sm"
                                           value="<?= htmlspecialchars($ut) ?>">
                                    <label for="ut">Unidad Tributaria Aplicada para cálculo</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <div class="form-floating">
                                    <input id="ip" type="text" placeholder="#" readonly
                                           class="form-control form-control-sm"
                                           style="text-align:right"
                                           value="<?= htmlspecialchars($totalImp) ?>">
                                    <label for="ip">Total Impuesto a Pagar</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ── TABLA 1: Entrada (7 columnas) ── -->
                    <table class="table table-bordered table-sm lenletratablaResumen">
                        <thead class="table-light">
                            <tr>
                                <th>Apellido(s) y Nombre(s)</th>
                                <th>C.I./Pasaporte</th>
                                <th>Parentesco</th>
                                <th>Grado</th>
                                <th>Premuerto</th>
                                <th>Cuota Parte Hereditaria(UT)</th>
                                <th>Reducción (Bs.)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($herederos as $i => $h): ?>
                            <tr>
                                <td style="text-align:left!important">
                                    <?= htmlspecialchars(strtoupper($h['nombre'] ?? '')) ?>
                                </td>
                                <td style="text-align:center">
                                    <?= htmlspecialchars($h['cedula'] ?? '') ?>
                                </td>
                                <td style="text-align:center">
                                    <?= htmlspecialchars($h['parentesco'] ?? '') ?>
                                </td>
                                <td style="text-align:center">
                                    <?= htmlspecialchars($h['grado'] ?? '1') ?>
                                </td>
                                <td style="text-align:center">
                                    <?= ($h['premuerto'] ?? false) ? 'SI' : 'NO' ?>
                                </td>
                                <td>
                                    <input type="text"
                                           class="decimal-input form-group form-control form-control-sm text-end"
                                           id="itemv-<?= $i ?>"
                                           style="text-align:right"
                                           value="<?= $fmt((float)($h['cuota_parte_ut'] ?? 0)) ?>">
                                </td>
                                <td>
                                    <input type="text"
                                           class="decimal-input form-group form-control form-control-sm text-end"
                                           id="itemd-<?= $i ?>"
                                           style="text-align:right"
                                           value="<?= $fmt((float)($h['reduccion'] ?? 0)) ?>">
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="7" class="text-center">
                                    <button type="button" id="btnCalcular"
                                            class="btn btn-sm btn-danger">Calcular</button>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </form>

                <!-- ════════════════════════════════════════════
                     TABLA 2: Resultados (11 columnas, readonly)
                     Oculta hasta que se haga clic en "Calcular"
                     ════════════════════════════════════════════ -->
                <div id="divResultados" style="display:none;">
                    <div class="row">
                        <div class="col-sm-12">
                            <table class="table table-bordered table-sm lenletratablaResumen">
                                <thead class="table-light">
                                    <tr>
                                        <th>Apellido(s) y Nombre(s)</th>
                                        <th>C.I./Pasaporte</th>
                                        <th>Parentesco</th>
                                        <th>Grado</th>
                                        <th>Premuerto</th>
                                        <th>Cuota Parte Hereditaria(UT)</th>
                                        <th>Porcentaje o Tarifa (%)</th>
                                        <th>Sustraendo (UT)</th>
                                        <th>Impuesto Determinado (Bs.)</th>
                                        <th>Reduccion (Bs.)</th>
                                        <th>Impuesto a Pagar (Impuesto Determinado - Reduccion)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($herederos as $i => $h): ?>
                                    <tr id="result-row-<?= $i ?>">
                                        <td style="text-align:left!important">
                                            <?= htmlspecialchars(strtoupper($h['nombre'] ?? '')) ?>
                                        </td>
                                        <td style="text-align:center">
                                            <?= htmlspecialchars($h['cedula'] ?? '') ?>
                                        </td>
                                        <td style="text-align:center">
                                            <?= htmlspecialchars($h['parentesco'] ?? '') ?>
                                        </td>
                                        <td style="text-align:center">
                                            <?= htmlspecialchars($h['grado'] ?? '1') ?>
                                        </td>
                                        <td style="text-align:center">
                                            <?= ($h['premuerto'] ?? false) ? 'SI' : 'NO' ?>
                                        </td>
                                        <td>
                                            <input readonly
                                                   class="res-cuota form-group form-control form-control-sm text-end"
                                                   value="<?= $fmt((float)($h['cuota_parte_ut'] ?? 0)) ?>">
                                        </td>
                                        <td>
                                            <input readonly
                                                   class="res-porcentaje form-group form-control form-control-sm text-end"
                                                   value="<?= $fmt((float)($h['porcentaje'] ?? 0)) ?>">
                                        </td>
                                        <td>
                                            <input readonly
                                                   class="res-sustraendo form-group form-control form-control-sm text-end"
                                                   value="<?= $fmt((float)($h['sustraendo_ut'] ?? 0)) ?>">
                                        </td>
                                        <td>
                                            <input readonly
                                                   class="res-impuesto form-group form-control form-control-sm text-end"
                                                   value="<?= $fmt((float)($h['impuesto_determinado'] ?? 0)) ?>">
                                        </td>
                                        <td>
                                            <input readonly
                                                   class="res-reduccion form-group form-control form-control-sm text-end"
                                                   value="<?= $fmt((float)($h['reduccion'] ?? 0)) ?>">
                                        </td>
                                        <td>
                                            <input readonly
                                                   class="res-pagar form-group form-control form-control-sm text-end"
                                                   value="<?= $fmt((float)($h['impuesto_a_pagar'] ?? 0)) ?>">
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- Botones Aceptar / Cancelar -->
                    <div class="row">
                        <div class="col-sm-12 text-center">
                            <button type="button" id="btnAceptar"
                               class="btn btn-sm btn-danger">Aceptar</button>&nbsp;
                            <a href="<?= base_url('/simulador/sucesion/resumen_calculo_manual') ?>"
                               class="btn btn-sm btn-danger">Cancelar</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ═══ Script: Recalcular + Guardar ═══ -->
<script>
(function() {
    'use strict';

    var BASE_URL   = '<?= rtrim(base_url(), "/") ?>';
    var INTENTO_ID = <?= json_encode($datos ? ($datos['intento_id'] ?? null) : null) ?>;
    var UT_FLOAT   = <?= json_encode($datos ? ($datos['ut_float'] ?? 0) : 0) ?>;
    var HEREDEROS  = <?= json_encode($datos ? array_values($datos['herederos'] ?? []) : [], JSON_UNESCAPED_UNICODE) ?>;
    var TARIFAS    = <?= json_encode($datos ? ($datos['tarifas'] ?? []) : [], JSON_UNESCAPED_UNICODE) ?>;
    var PARENTESCO_CAT = <?= json_encode($datos ? ($datos['parentesco_cat'] ?? []) : [], JSON_UNESCAPED_UNICODE) ?>;
    var BORRADOR   = <?= json_encode($datos ? ($datos['borrador_raw'] ?? []) : [], JSON_UNESCAPED_UNICODE) ?>;
    var IMPUESTO_AUTO = parseDecimal('<?= htmlspecialchars($totalImp) ?>');

    // Agrupar tarifas por grupo_tarifa_id
    var tarifasPorGrupo = {};
    TARIFAS.forEach(function(t) {
        var g = parseInt(t.grupo_tarifa_id);
        if (!tarifasPorGrupo[g]) tarifasPorGrupo[g] = [];
        tarifasPorGrupo[g].push(t);
    });

    // Obtener grupo_tarifa_id para un parentesco_id
    function getGrupoId(parentescoId) {
        var cat = PARENTESCO_CAT[parentescoId];
        return cat ? parseInt(cat.grupo_tarifa_id || 4) : 4;
    }

    // Buscar tramo aplicable
    function buscarTramo(grupoId, cuotaUT) {
        var tramos = tarifasPorGrupo[grupoId] || [];
        for (var i = 0; i < tramos.length; i++) {
            var desde = parseFloat(tramos[i].rango_desde_ut);
            var hasta = tramos[i].rango_hasta_ut;
            if (cuotaUT >= desde && (hasta === null || cuotaUT <= parseFloat(hasta))) {
                return tramos[i];
            }
        }
        return tramos.length ? tramos[tramos.length - 1] : null;
    }

    // Parse "1.234,56" → 1234.56
    function parseDecimal(str) {
        if (!str) return 0;
        str = String(str).trim();
        str = str.replace(/\./g, '').replace(',', '.');
        return parseFloat(str) || 0;
    }

    // Format float → "1.234,56"
    function fmtBs(v) {
        var parts = v.toFixed(2).split('.');
        var intPart = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        return intPart + ',' + parts[1];
    }

    // Helper: mostrar toast SPDSS (solo para errores del sistema, no validaciones SENIAT)
    function showToast(type, msg) {
        var container = document.getElementById('cc-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'cc-toast-container';
            document.body.appendChild(container);
        }
        var icons = {
            error: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>',
            warning: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>'
        };
        var toast = document.createElement('div');
        toast.className = 'cc-toast cc-toast--' + type;
        toast.innerHTML = '<span class="cc-toast__icon">' + (icons[type] || icons.error) + '</span>'
            + '<span class="cc-toast__msg">' + msg + '</span>'
            + '<button class="cc-toast__close" onclick="this.parentElement.classList.add(\'cc-toast--exit\');var t=this.parentElement;setTimeout(function(){t.remove()},300)">✕</button>';
        container.appendChild(toast);
        setTimeout(function(){ toast.classList.add('cc-toast--exit'); setTimeout(function(){ toast.remove(); },300); }, 5000);
    }

    var btnCalcular   = document.getElementById('btnCalcular');
    var divResultados = document.getElementById('divResultados');

    if (btnCalcular) {
        btnCalcular.addEventListener('click', function() {
            try {
            var totalImpuesto     = 0;
            var totalDeterminado  = 0;
            var hayError          = false;

            // Helper: truncar a N decimales hacia abajo (sin redondear)
            function truncDecimal(v, decimals) {
                var factor = Math.pow(10, decimals);
                return Math.floor(v * factor) / factor;
            }

            // Límite 3 fijo: parte proporcional del impuesto automático
            var maxLimite3 = Math.floor(IMPUESTO_AUTO / HEREDEROS.length);

            for (var i = 0; i < HEREDEROS.length; i++) {
                var h = HEREDEROS[i];
                var cuotaUT   = parseDecimal(document.getElementById('itemv-' + i).value);
                var reduccion = parseDecimal(document.getElementById('itemd-' + i).value);

                var parentescoId = parseInt(h.parentesco_id || 0);
                var grupoId = getGrupoId(parentescoId);
                var tramo = buscarTramo(grupoId, cuotaUT);

                var porcentaje   = tramo ? parseFloat(tramo.porcentaje)    : 0;
                var sustraendoUT = tramo ? parseFloat(tramo.sustraendo_ut) : 0;

                var impuestoDeterminado = 0;
                if (grupoId === 1 && cuotaUT <= 75.0) {
                    impuestoDeterminado = 0;
                } else {
                    var impuestoUT = (cuotaUT * porcentaje / 100) - sustraendoUT;
                    impuestoDeterminado = Math.round(impuestoUT * UT_FLOAT * 100) / 100;
                }

                // ── Validación de Reducción (3 límites) ──

                // Límite 1: reducción no puede igualar ni superar el impuesto determinado
                if (reduccion >= impuestoDeterminado && impuestoDeterminado > 0) {
                    alert('El monto de Reducción no puede ser mayor al impuesto determinado');
                    hayError = true; break;
                }

                // Límite 2: reducción no puede superar la mitad del impuesto (truncado a 2 dec)
                var maxLimite2 = truncDecimal(impuestoDeterminado / 2, 2);

                // Aplicar el más restrictivo entre Límite 2 y Límite 3
                if (reduccion > Math.min(maxLimite2, maxLimite3) + 0.001) {
                    alert('Diferencia en el monto a pagar de la declaración');
                    hayError = true; break;
                }

                var impuestoAPagar = Math.max(0, Math.round((impuestoDeterminado - reduccion) * 100) / 100);
                totalDeterminado += impuestoDeterminado;
                totalImpuesto    += impuestoAPagar;

                // Actualizar Tabla 2 (resultados)
                var row = document.getElementById('result-row-' + i);
                if (row) {
                    row.querySelector('.res-cuota').value      = fmtBs(cuotaUT);
                    row.querySelector('.res-porcentaje').value  = fmtBs(porcentaje);
                    row.querySelector('.res-sustraendo').value  = fmtBs(sustraendoUT);
                    row.querySelector('.res-impuesto').value    = fmtBs(impuestoDeterminado);
                    row.querySelector('.res-reduccion').value   = fmtBs(reduccion);
                    row.querySelector('.res-pagar').value       = fmtBs(impuestoAPagar);
                }
            }

            if (hayError) {
                divResultados.style.display = 'none';
                return;
            }

            // ── Validación de Cuota: suma determinados >= impuesto automático ──
            if (totalDeterminado < IMPUESTO_AUTO - 0.001) {
                document.getElementById('ip').value = fmtBs(totalImpuesto);
                divResultados.style.display = 'none';
                alert('Diferencia en el monto a pagar de la declaración');
                return;
            }

            // Todo OK → mostrar resultados
            document.getElementById('ip').value = fmtBs(totalImpuesto);
            divResultados.style.display = 'block';
            divResultados.scrollIntoView({ behavior: 'smooth', block: 'start' });

            } catch (e) {
                console.error('[Cálculo Manual] Error:', e);
                divResultados.style.display = 'none';
                showToast('error', 'Error inesperado al calcular. Verifique los datos ingresados.');
            }
        });
    }

    var btnAceptar = document.getElementById('btnAceptar');
    if (btnAceptar) {
        btnAceptar.addEventListener('click', function(e) {
            e.preventDefault();
            if (!INTENTO_ID) { alert('No hay intento activo'); return; }

            btnAceptar.disabled = true;
            btnAceptar.textContent = 'Guardando...';

            try {
                // Construir overrides
                var overrides = [];
                HEREDEROS.forEach(function(h, i) {
                    overrides.push({
                        cuota_parte_ut: parseDecimal(document.getElementById('itemv-' + i).value),
                        reduccion_bs:   parseDecimal(document.getElementById('itemd-' + i).value)
                    });
                });

                // Agregar overrides al borrador completo y guardar
                BORRADOR.calculo_manual = { herederos: overrides };

                fetch(BASE_URL + '/api/intentos/' + INTENTO_ID + '/guardar', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ borrador: BORRADOR })
                })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.ok) {
                        window.location.href = BASE_URL + '/simulador/sucesion/resumen_declaracion';
                    } else {
                        showToast('error', 'Error al guardar: ' + (data.error || 'Error desconocido'));
                        btnAceptar.disabled = false;
                        btnAceptar.textContent = 'Aceptar';
                    }
                })
                .catch(function(err) {
                    showToast('error', 'Error de conexión: ' + err.message);
                    btnAceptar.disabled = false;
                    btnAceptar.textContent = 'Aceptar';
                });
            } catch (e) {
                console.error('[Cálculo Manual] Error al guardar:', e);
                showToast('error', 'Error inesperado al guardar. Intente de nuevo.');
                btnAceptar.disabled = false;
                btnAceptar.textContent = 'Aceptar';
            }
        });
    }
})();
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../../../layouts/sim_sucesiones_layout.php';
?>
