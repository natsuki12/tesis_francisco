<?php
declare(strict_types=1);

namespace App\Modules\Student\Controllers;

use App\Modules\Student\Models\StudentAssignmentModel;

/**
 * Controller para las asignaciones del estudiante.
 * Extraído de web.php para respetar MVC.
 */
class AsignacionesEstudianteController
{
    private StudentAssignmentModel $model;

    public function __construct()
    {
        $this->model = new StudentAssignmentModel();
    }

    /**
     * GET /mis-asignaciones
     * Lista las asignaciones del estudiante.
     */
    public function index(\App\Core\App $app): string
    {
        try {
            $estudianteId = $this->model->getEstudianteId((int) $_SESSION['user_id']);
            $asignaciones = [];
            if ($estudianteId) {
                $asignaciones = $this->model->getAsignaciones($estudianteId);
            }

            return $app->view('student/mis_asignaciones', [
                'asignaciones' => $asignaciones,
            ]);
        } catch (\Throwable $e) {
            error_log('[AsignacionesEstudianteController::index] ' . $e->getMessage());
            return $app->view('student/mis_asignaciones', [
                'asignaciones' => [],
            ]);
        }
    }

    /**
     * GET /mis-asignaciones/{id}
     * Muestra el detalle de una asignación con sus intentos.
     */
    public function show(int $id, \App\Core\App $app): string
    {
        try {
            $estudianteId = $this->model->getEstudianteId((int) $_SESSION['user_id']);

            if (!$estudianteId) {
                http_response_code(404);
                return $app->view('errors/404');
            }

            $asignacion = $this->model->getDetalleAsignacion($id, $estudianteId);
            if (!$asignacion) {
                http_response_code(404);
                return $app->view('errors/404');
            }

            $intentos = $this->model->getIntentos($id);

            return $app->view('student/detalle_asignacion', [
                'asignacion' => $asignacion,
                'intentos' => $intentos,
            ]);
        } catch (\Throwable $e) {
            error_log('[AsignacionesEstudianteController::show] ' . $e->getMessage());
            http_response_code(500);
            return $app->view('errors/404');
        }
    }

    /**
     * GET /mis-asignaciones/{id}/caso-pdf
     * Genera el PDF del caso asociado a la asignación del estudiante.
     * Reutiliza el mismo template y lógica que el profesor.
     */
    public function descargarCasoPdf(int $asignacionId): void
    {
        try {
            $estudianteId = $this->model->getEstudianteId((int) $_SESSION['user_id']);

            if (!$estudianteId) {
                http_response_code(403);
                echo 'No autorizado.';
                return;
            }

            // Verificar que la asignación pertenece al estudiante y obtener caso_id + profesor_id
            $asignacion = $this->model->getDetalleAsignacion($asignacionId, $estudianteId);
            if (!$asignacion) {
                http_response_code(404);
                echo 'Asignación no encontrada.';
                return;
            }

            $casoId = (int) $asignacion['caso_id'];

            // Obtener profesor_id desde la config
            $db = \App\Core\DB::connect();
            $stmt = $db->prepare("SELECT profesor_id FROM sim_caso_configs WHERE id = :cfg_id LIMIT 1");
            $stmt->execute(['cfg_id' => $asignacion['config_id']]);
            $profesorId = (int) $stmt->fetchColumn();

            if (!$profesorId) {
                http_response_code(404);
                echo 'Configuración no encontrada.';
                return;
            }

            // Reutilizar el modelo del profesor para obtener todos los datos del caso
            $model = new \App\Modules\Professor\Models\Casos\GestionarCasoModel();
            $casoData = $model->getFullCaseById($casoId, $profesorId);

            if (!$casoData) {
                http_response_code(404);
                echo 'Caso no encontrado.';
                return;
            }

            // ── Misma lógica de extracción que CasosController::descargarPdf ──
            $caso = $casoData['caso'];
            $source = $casoData['source'];
            $titulo = $caso['titulo'] ?? 'Sin título';
            $estado = $caso['estado'] ?? 'Borrador';
            $casoIdVal = (int) $caso['id'];
            $tipoSuc = $caso['tipo_sucesion'] ?? '';
            $esConCedula = stripos($tipoSuc, 'Con') !== false;
            $fechaPublicacion = ($estado === 'Publicado' && !empty($caso['updated_at']))
                ? date('d/m/Y', strtotime($caso['updated_at']))
                : (isset($caso['created_at']) ? date('d/m/Y', strtotime($caso['created_at'])) : '—');

            if ($source === 'borrador') {
                $b = $casoData['borrador'];
                $causante = $b['causante'] ?? [];
                $rep = $b['representante'] ?? [];
                $herederos = $b['herederos'] ?? [];
                $herederos_premuertos = $b['herederos_premuertos'] ?? [];
                $allHerederos = array_merge($herederos, $herederos_premuertos);
                $bienesInmuebles = $b['bienes_inmuebles'] ?? [];

                $catNames = [
                    '1' => 'Banco', '2' => 'Seguro', '3' => 'Transporte',
                    '4' => 'Opciones de Compra', '5' => 'Cuentas y Efectos por Cobrar',
                    '6' => 'Semovientes', '7' => 'Bonos', '8' => 'Acciones',
                    '9' => 'Prestaciones Sociales', '10' => 'Caja de Ahorro',
                    '11' => 'Plantaciones', '12' => 'Otros'
                ];
                $bienesMuebles = [];
                foreach (($b['bienes_muebles'] ?? []) as $catId => $items) {
                    if ($catId === 'length' || !is_array($items)) continue;
                    foreach ($items as $item) {
                        $item['categoria'] = $catNames[$catId] ?? "Cat. $catId";
                        $bienesMuebles[] = $item;
                    }
                }
                foreach (($b['bienes_muebles_banco'] ?? []) as $bancoItem) {
                    $bancoItem['categoria'] = 'Banco';
                    $bancoItem['tipo_nombre'] = $bancoItem['tipo_bien_nombre'] ?? '';
                    $bancoItem['es_bien_litigioso'] = ($bancoItem['bien_litigioso'] ?? 'false') === 'true' ? 1 : 0;
                    $bienesMuebles[] = $bancoItem;
                }

                $pasivosDeuda = $b['pasivos_deuda'] ?? [];
                $pasivosGastos = $b['pasivos_gastos'] ?? [];
                $exenciones = $b['exenciones'] ?? [];
                $exoneraciones = $b['exoneraciones'] ?? [];
                $prorrogas = $b['prorrogas'] ?? [];
                $herenciaTipos = $b['herencia']['tipos'] ?? [];
                $direcciones = $b['direcciones_causante'] ?? [];
                $acta = $b['acta_defuncion'] ?? [];
                $datosFiscales = $b['datos_fiscales_causante'] ?? $b['datos_fiscales'] ?? [];

                $causanteNombre = trim(($causante['nombres'] ?? '') . ' ' . ($causante['apellidos'] ?? ''));
                $causanteCedula = $causante['cedula'] ?? '';
                $causanteStr = !empty($causanteNombre)
                    ? $causanteNombre . (!empty($causanteCedula) ? " ({$causante['tipo_cedula']}-{$causanteCedula})" : '')
                    : 'No definido';
                $repNombre = trim(($rep['nombres'] ?? '') . ' ' . ($rep['apellidos'] ?? ''));
                $repStr = !empty($repNombre) ? $repNombre . (!empty($rep['cedula']) ? " ({$rep['letra_cedula']}-{$rep['cedula']})" : '') : 'No definido';

                $totalActivos = 0;
                foreach ($bienesInmuebles as $bi) $totalActivos += (float)($bi['valor_declarado'] ?? 0);
                foreach ($bienesMuebles as $bm) $totalActivos += (float)($bm['valor_declarado'] ?? 0);
                $totalPasivos = 0;
                foreach ($pasivosDeuda as $pd) $totalPasivos += (float)($pd['valor_declarado'] ?? 0);
                foreach ($pasivosGastos as $pg) $totalPasivos += (float)($pg['valor_declarado'] ?? 0);
                $patrimonioNeto = $totalActivos - $totalPasivos;
                $totalHerederos = count($allHerederos);
                $totalItems = count($bienesInmuebles) + count($bienesMuebles) + count($pasivosDeuda) + count($pasivosGastos);
                $totalExenciones = 0;
                foreach ($exenciones as $ex) $totalExenciones += (float)($ex['valor_declarado'] ?? 0);
                $totalExoneraciones = 0;
                foreach ($exoneraciones as $eo) $totalExoneraciones += (float)($eo['valor_declarado'] ?? 0);

                $herederosCalc = [];
            } else {
                // ── Published case ──
                $causante = [
                    'nombres' => $caso['causante_nombres'] ?? '',
                    'apellidos' => $caso['causante_apellidos'] ?? '',
                    'cedula' => $caso['causante_cedula'] ?? '',
                    'tipo_cedula' => $caso['causante_tipo_cedula'] ?? '',
                    'sexo' => $caso['causante_sexo'] ?? '',
                    'estado_civil' => $caso['causante_estado_civil'] ?? '',
                    'fecha_nacimiento' => $caso['causante_fecha_nacimiento'] ?? '',
                ];
                $causanteNombre = trim(($caso['causante_nombres'] ?? '') . ' ' . ($caso['causante_apellidos'] ?? ''));
                $causanteCedula = $caso['causante_cedula'] ?? '';
                $causanteStr = !empty($causanteNombre)
                    ? $causanteNombre . (!empty($causanteCedula) ? " ({$caso['causante_tipo_cedula']}-{$causanteCedula})" : '')
                    : 'No definido';

                $rep = $caso;
                $repNombre = trim(($caso['rep_nombres'] ?? '') . ' ' . ($caso['rep_apellidos'] ?? ''));
                $repStr = !empty($repNombre) ? $repNombre . (!empty($caso['rep_cedula']) ? " ({$caso['rep_tipo_cedula']}-{$caso['rep_cedula']})" : '') : 'No definido';

                $acta = $casoData['acta_defuncion'] ?? [];
                $allHerederos = $casoData['herederos'] ?? [];
                $herederos = [];
                $herederos_premuertos = [];
                foreach ($allHerederos as $h) {
                    if (isset($h['premuerto_padre_id']) && !empty($h['premuerto_padre_id'])) {
                        $herederos_premuertos[] = $h;
                    } else {
                        $herederos[] = $h;
                    }
                }
                $bienesInmuebles = $casoData['bienes_inmuebles'] ?? [];
                $bienesMuebles = $casoData['bienes_muebles'] ?? [];
                $pasivosDeuda = $casoData['pasivos_deuda'] ?? [];
                $pasivosGastos = $casoData['pasivos_gastos'] ?? [];
                $exenciones = $casoData['exenciones'] ?? [];
                $exoneraciones = $casoData['exoneraciones'] ?? [];
                $prorrogas = $casoData['prorrogas'] ?? [];
                $herenciaTipos = $casoData['herencia'] ?? [];
                $direcciones = $casoData['direcciones'] ?? [];
                $datosFiscales = $casoData['datos_fiscales'] ?? [];
                $tarifas = $casoData['tarifas'] ?? [];
                $gruposTarifa = $casoData['grupos_tarifa'] ?? [];

                $resumen = $casoData['resumen'];
                $totalHerederos = $resumen['total_herederos'];
                $totalActivos = $resumen['total_activos'];
                $totalPasivos = $resumen['total_pasivos'];
                $totalExenciones = $resumen['total_exenciones'] ?? 0;
                $totalExoneraciones = $resumen['total_exoneraciones'] ?? 0;
                $patrimonioNeto = $resumen['patrimonio_neto'];
                $totalItems = $resumen['total_items'];

                // ── Tribute calculations ──
                $fechaFall = $acta['fecha_fallecimiento']
                    ?? $caso['causante_fecha_fallecimiento']
                    ?? $caso['fecha_fallecimiento']
                    ?? null;
                $utData = null;
                if ($fechaFall) {
                    $utData = \App\Core\UnidadTributariaService::obtenerPorFecha($fechaFall);
                }
                $utValor = $utData ? (float)$utData['valor'] : 0;
                $utAnio = $utData ? $utData['anio'] : '—';

                $tInmuebles = 0;
                foreach ($bienesInmuebles as $bi) $tInmuebles += (float)($bi['valor_declarado'] ?? 0);
                $tMuebles = 0;
                foreach ($bienesMuebles as $bm) $tMuebles += (float)($bm['valor_declarado'] ?? 0);
                $patrimonioBruto = $tInmuebles + $tMuebles;
                $activoHerBruto = $patrimonioBruto;

                $totalDesgravamenes = 0;
                foreach ($bienesInmuebles as $bi) {
                    if (($bi['es_vivienda_principal'] ?? 0) == 1 || ($bi['vivienda_principal'] ?? '') === 'Si') {
                        $totalDesgravamenes += (float)($bi['valor_declarado'] ?? 0);
                    }
                }
                foreach ($bienesMuebles as $bm) {
                    $catNombre = strtolower($bm['categoria_nombre'] ?? '');
                    $tipoNombre = strtolower($bm['tipo_nombre'] ?? '');
                    if (strpos($catNombre, 'seguro') !== false &&
                        (strpos($tipoNombre, 'montep') !== false || strpos($tipoNombre, 'seguro de vida') !== false)) {
                        $totalDesgravamenes += (float)($bm['valor_declarado'] ?? 0);
                    }
                    if (strpos($catNombre, 'prestacion') !== false && empty($bm['banco_id'])) {
                        $totalDesgravamenes += (float)($bm['valor_declarado'] ?? 0);
                    }
                }

                $tExclusiones = $totalDesgravamenes + $totalExenciones + $totalExoneraciones;
                $activoHereditarioNeto = max(0, $activoHerBruto - $tExclusiones);
                $tPatrimonioNeto = max(0, $activoHereditarioNeto - $totalPasivos);

                $tramosPorGrupo = [];
                foreach ($tarifas as $t) {
                    $gid = (int)$t['grupo_tarifa_id'];
                    $tramosPorGrupo[$gid][] = [
                        'desde' => (float)$t['rango_desde_ut'],
                        'hasta' => $t['rango_hasta_ut'] !== null ? (float)$t['rango_hasta_ut'] : null,
                        'porcentaje' => (float)$t['porcentaje'],
                        'sustraendo' => (float)$t['sustraendo_ut'],
                    ];
                }

                $mainHerederos = $herederos;
                $nHerederos = count($mainHerederos);
                $herederosCalc = [];
                $totalImpuesto = 0;
                $totalReducciones = 0;

                foreach ($mainHerederos as $h) {
                    $grupoId = isset($h['grupo_tarifa_id']) && $h['grupo_tarifa_id'] !== null
                        ? (int)$h['grupo_tarifa_id'] : 4;

                    $cuotaParteUT = 0; $porcentaje = 0; $sustraendoUT = 0;
                    $impuestoDet = 0; $reduccion = 0; $impuestoPagar = 0;

                    if ($tPatrimonioNeto > 0 && $nHerederos > 0 && $utValor > 0) {
                        $cuotaParteBs = $tPatrimonioNeto / $nHerederos;
                        $cuotaParteUT = $cuotaParteBs / $utValor;

                        $tramos = $tramosPorGrupo[$grupoId] ?? [];
                        $tramo = null;
                        foreach ($tramos as $tr) {
                            if ($cuotaParteUT >= $tr['desde'] && ($tr['hasta'] === null || $cuotaParteUT <= $tr['hasta'])) {
                                $tramo = $tr; break;
                            }
                        }
                        if (!$tramo && !empty($tramos)) $tramo = end($tramos);
                        if ($tramo) { $porcentaje = $tramo['porcentaje']; $sustraendoUT = $tramo['sustraendo']; }

                        if ($grupoId === 1 && $cuotaParteUT <= 75.0) {
                            $impuestoDet = 0;
                        } else {
                            $impuestoUT = ($cuotaParteUT * $porcentaje / 100) - $sustraendoUT;
                            $impuestoDet = round($impuestoUT * $utValor, 2);
                        }
                        $impuestoPagar = max(0, round($impuestoDet - $reduccion, 2));
                    }

                    $totalImpuesto += $impuestoPagar;
                    $totalReducciones += $reduccion;

                    $herederosCalc[] = [
                        'h' => $h,
                        'grupo_id' => $grupoId,
                        'cuota_parte_ut' => round($cuotaParteUT, 2),
                        'porcentaje' => $porcentaje,
                        'sustraendo_ut' => $sustraendoUT,
                        'impuesto_determinado' => $impuestoDet,
                        'reduccion' => $reduccion,
                        'impuesto_a_pagar' => $impuestoPagar,
                    ];
                }
                $totalImpuesto = round($totalImpuesto, 2);
                $totalReducciones = round($totalReducciones, 2);
            }

            // ── Variables del membrete ──
            $pdfTipoDocumento = 'Caso Sucesoral';
            $pdfReferencia = '#CASO-' . $casoIdVal;
            $pdfEstado = $estado;
            $pdfEstadoLabel = 'Estado';

            // ── Render PDF template into HTML ──
            ob_start();
            include __DIR__ . '/../../../../resources/views/professor/pdf/pdf_caso.php';
            $html = ob_get_clean();

            // ── Generate PDF with mPDF ──
            $mpdf = new \Mpdf\Mpdf([
                'mode'          => 'utf-8',
                'format'        => 'Letter',
                'margin_left'   => 15,
                'margin_right'  => 15,
                'margin_top'    => 20,
                'margin_bottom' => 20,
                'margin_header' => 5,
                'margin_footer' => 5,
                'default_font'  => 'dejavusans',
            ]);
            $mpdf->setAutoTopMargin = 'stretch';
            $mpdf->setAutoBottomMargin = 'stretch';

            $safeTitle = preg_replace('/[^a-zA-Z0-9_\- áéíóúñÁÉÍÓÚÑ]/', '', $titulo);
            $mpdf->SetTitle($safeTitle . ' — SUCELAB');
            $mpdf->SetAuthor('SUCELAB');
            $mpdf->WriteHTML($html);
            $mpdf->Output('caso_' . $casoIdVal . '.pdf', 'I');

        } catch (\Throwable $e) {
            error_log('[AsignacionesEstudianteController::descargarCasoPdf] ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            http_response_code(500);
            echo 'Ocurrió un error inesperado al generar el documento. Por favor, contacte al administrador.';
        }
    }
}
