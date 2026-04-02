<?php
declare(strict_types=1);

namespace App\Modules\Professor\Controllers\Casos;

use App\Core\App;
use App\Core\BitacoraModel;
use App\Modules\Professor\Models\Casos\CasosModel;
use App\Modules\Professor\Models\Casos\StoreCasoModel;
use App\Modules\Professor\Models\HomeProfessorModel;
use App\Modules\Professor\Validators\CasoValidator;

class CasosController
{
    private App $app;
    private CasosModel $casosModel;
    private int $profesorId;

    public function __construct()
    {
        global $app;
        $this->app = $app;
        $this->casosModel = new CasosModel();
        $userId = (int) ($_SESSION['user_id'] ?? 0);
        $this->profesorId = (new HomeProfessorModel())->getProfesorId($userId) ?? 0;
    }

    /**
     * Muestra la lista de casos sucesorales del profesor.
     */
    public function index()
    {
        // La autenticación y verificación de rol se hacen en web.php ($requireAuth + $requireRole)
        $profesorId = $this->profesorId;

        // Obtener la información real de la BD
        $casos = $this->casosModel->getCasosByProfesor($profesorId);
        $stats = $this->casosModel->getStatsByProfesor($profesorId);

        return $this->app->view('professor/casos_sucesorales', [
            'casos' => $casos,
            'stats' => $stats
        ]);
    }

    /**
     * Muestra el formulario de crear/editar caso.
     * Valida ?edit=ID en servidor antes de renderizar.
     * GET /crear-caso
     */
    public function crearCaso()
    {
        $editId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;

        if ($editId > 0) {
            $db = \App\Core\DB::connect();
            $stmt = $db->prepare("SELECT estado FROM sim_casos_estudios WHERE id = :id AND profesor_id = :prof");
            $stmt->execute(['id' => $editId, 'prof' => $this->profesorId]);
            $estado = $stmt->fetchColumn();

            if (!$estado || $estado !== 'Borrador') {
                $_SESSION['flash_msg'] = $estado
                    ? 'No se puede editar un caso con estado "' . $estado . '".'
                    : 'Caso no encontrado.';
                header('Location: ' . base_url('/casos-sucesorales'));
                exit;
            }
        }

        return $this->app->view('professor/crear_caso');
    }

    /**
     * Retorna el JSON de un caso específico para edición.
     * GET /api/casos/{id}
     */
    public function show(int $id)
    {
        header('Content-Type: application/json');

        $profesorId = $this->profesorId;
        $result = $this->casosModel->getCasoJsonById($id, $profesorId);

        if (!$result) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Caso no encontrado.']);
            exit;
        }

        // Bloquear edición de casos publicados
        if ($result['estado'] === 'Publicado') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'No se puede editar un caso publicado.']);
            exit;
        }

        if (!$result['data']) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Este caso no tiene datos editables.']);
            exit;
        }

        // Inyectar caso_id en el data para que el frontend lo use en re-saves
        $result['data']['caso_id'] = $result['caso_id'];

        echo json_encode([
            'success' => true,
            'caso_id' => $result['caso_id'],
            'estado' => $result['estado'],
            'data' => $result['data']
        ]);
        exit;
    }

    /**
     * Guarda un caso completo (Borrador o Publicar).
     * POST /api/casos
     */
    public function store()
    {
        header('Content-Type: application/json');

        $profesorId = $this->profesorId;

        // Leer el JSON del body
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);

        if (!$data || !is_array($data)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'errors' => ['El body de la petición no es JSON válido.']]);
            exit;
        }

        // Determinar modo
        $modo = ($data['caso']['estado'] ?? 'Borrador') === 'Publicado' ? 'Publicar' : 'Borrador';

        // Sanitizar: XSS escape + coerción de tipos numéricos
        $data = CasoValidator::sanitize($data);

        // Validar
        // profesorId ya resuelto en constructor
        $casoId = isset($data['caso_id']) ? (int) $data['caso_id'] : null;
        $validator = new CasoValidator();
        $errors = $validator->validate($data, $modo, $profesorId, $casoId);

        if (!empty($errors)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'errors' => $errors]);
            exit;
        }

        // Insertar en la BD
        try {
            $storeModel = new StoreCasoModel();
            $inputCasoId = isset($data['caso_id']) ? (int) $data['caso_id'] : null;

            // Guard: bloquear re-publicación de un caso ya publicado
            if ($inputCasoId && $modo === 'Publicar') {
                $db = \App\Core\DB::connect();
                $chk = $db->prepare("SELECT estado FROM sim_casos_estudios WHERE id = :id AND profesor_id = :prof");
                $chk->execute(['id' => $inputCasoId, 'prof' => $profesorId]);
                $estadoActual = $chk->fetchColumn();
                if ($estadoActual === 'Publicado') {
                    echo json_encode(['success' => true, 'caso_id' => $inputCasoId, 'message' => 'Este caso ya fue publicado.']);
                    exit;
                }
            }

            $tituloCorto = mb_substr($data['caso']['titulo'] ?? 'Sin título', 0, 60);

            if ($modo === 'Borrador') {
                $casoId = $storeModel->storeDraft($data, $profesorId, $inputCasoId);

                // Registrar en bitácora: crear o actualizar borrador
                BitacoraModel::registrar(
                    $inputCasoId ? BitacoraModel::CASE_STATUS_CHANGED : BitacoraModel::CASE_CREATED,
                    'casos',
                    $profesorId,
                    null,
                    'sim_casos_estudios',
                    (int) $casoId,
                    detalle: ($inputCasoId ? 'Borrador actualizado' : 'Borrador creado') . ': ' . $tituloCorto
                );
            } else {
                $casoId = $storeModel->store($data, $profesorId, $inputCasoId);

                // Registrar en bitácora: publicar caso
                BitacoraModel::registrar(
                    BitacoraModel::CASE_PUBLISHED,
                    'casos',
                    $profesorId,
                    null,
                    'sim_casos_estudios',
                    (int) $casoId,
                    detalle: ($inputCasoId ? 'Borrador publicado' : 'Caso creado y publicado') . ': ' . $tituloCorto
                );
            }

            echo json_encode([
                'success' => true,
                'caso_id' => $casoId,
                'message' => $modo === 'Publicar'
                    ? 'Caso publicado exitosamente.'
                    : 'Borrador guardado exitosamente.'
            ]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'errors' => ['Error al guardar el caso: ' . $e->getMessage()]
            ]);
        }

        exit;
    }

    /**
     * Verifica si un título de caso ya existe para este profesor.
     * GET /api/casos/check-titulo?titulo=X
     */
    public function checkTitulo()
    {
        header('Content-Type: application/json');
        $profesorId = $this->profesorId;
        $titulo = trim($_GET['titulo'] ?? '');
        $casoId = isset($_GET['caso_id']) ? (int) $_GET['caso_id'] : null;

        if (empty($titulo)) {
            echo json_encode(['exists' => false]);
            exit;
        }

        try {
            $db = \App\Core\DB::connect();
            $sql = "SELECT COUNT(*) FROM sim_casos_estudios WHERE titulo = :t AND profesor_id = :p AND estado != 'Eliminado'";
            $params = ['t' => $titulo, 'p' => $profesorId];
            if ($casoId) {
                $sql .= " AND id != :id";
                $params['id'] = $casoId;
            }
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $exists = (int) $stmt->fetchColumn() > 0;
            echo json_encode(['exists' => $exists]);
        } catch (\Throwable $e) {
            echo json_encode(['exists' => false]);
        }
        exit;
    }

    /**
     * Elimina un caso (soft delete: estado = 'Eliminado').
     * DELETE /api/casos/{id}
     */
    public function destroy(int $id)
    {
        header('Content-Type: application/json');
        $profesorId = $this->profesorId;

        try {
            $db = \App\Core\DB::connect();

            // Verificar que el caso existe y pertenece al profesor
            $stmt = $db->prepare("SELECT id, titulo FROM sim_casos_estudios WHERE id = :id AND profesor_id = :prof AND estado != 'Eliminado'");
            $stmt->execute(['id' => $id, 'prof' => $profesorId]);
            $caso = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$caso) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Caso no encontrado.']);
                exit;
            }

            // Soft delete: cambiar estado a 'Eliminado'
            $stmt = $db->prepare("UPDATE sim_casos_estudios SET estado = 'Eliminado' WHERE id = :id");
            $stmt->execute(['id' => $id]);

            // Registrar en bitácora
            BitacoraModel::registrar(
                BitacoraModel::CASE_DELETED,
                'casos',
                $profesorId,
                null,
                'sim_casos_estudios',
                $id,
                detalle: 'Caso eliminado: ' . mb_substr($caso['titulo'] ?? '', 0, 60)
            );

            echo json_encode(['success' => true, 'message' => 'Caso eliminado exitosamente.']);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error al eliminar: ' . $e->getMessage()]);
        }
        exit;
    }

    /**
     * Cambia el estado de un caso (ej: Inactivar).
     * PATCH /api/casos/{id}/estado
     */
    public function updateEstado(int $id)
    {
        header('Content-Type: application/json');
        $profesorId = $this->profesorId;

        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        $nuevoEstado = $data['estado'] ?? '';

        // Solo permitir estados válidos
        if (!in_array($nuevoEstado, ['Inactivo', 'Publicado', 'Borrador'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Estado no válido.']);
            exit;
        }

        try {
            $db = \App\Core\DB::connect();

            $stmt = $db->prepare("SELECT id, titulo FROM sim_casos_estudios WHERE id = :id AND profesor_id = :prof AND estado != 'Eliminado'");
            $stmt->execute(['id' => $id, 'prof' => $profesorId]);
            $caso = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$caso) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Caso no encontrado.']);
                exit;
            }

            $stmt = $db->prepare("UPDATE sim_casos_estudios SET estado = :estado WHERE id = :id");
            $stmt->execute(['estado' => $nuevoEstado, 'id' => $id]);

            // Registrar en bitácora
            BitacoraModel::registrar(
                BitacoraModel::CASE_STATUS_CHANGED,
                'casos',
                $profesorId,
                null,
                'sim_casos_estudios',
                $id,
                detalle: 'Estado cambiado a ' . $nuevoEstado . ': ' . mb_substr($caso['titulo'] ?? '', 0, 60)
            );

            echo json_encode(['success' => true, 'message' => 'Estado actualizado a ' . $nuevoEstado . '.']);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit;
    }

    /**
     * GET /casos-sucesorales/{id}
     * Muestra la vista de gestión de un caso específico.
     */
    public function gestionar(int $id): string
    {
        try {
            $profesorId = $this->profesorId;
            $model = new \App\Modules\Professor\Models\Casos\GestionarCasoModel();
            $data = $model->getFullCaseById($id, $profesorId);
            if (!$data) {
                http_response_code(404);
                return $this->app->view('errors/404');
            }
            return $this->app->view('professor/gestionar_caso', ['casoData' => $data]);
        } catch (\Throwable $e) {
            error_log('[CasosController::gestionar] ' . $e->getMessage());
            http_response_code(500);
            return $this->app->view('errors/404');
        }
    }

    /**
     * GET /casos-sucesorales/{id}/pdf
     * Descarga el caso completo como PDF.
     */
    public function descargarPdf(int $id): void
    {
        try {
            $profesorId = $this->profesorId;
            $model = new \App\Modules\Professor\Models\Casos\GestionarCasoModel();
            $casoData = $model->getFullCaseById($id, $profesorId);

            if (!$casoData) {
                http_response_code(404);
                echo 'Caso no encontrado.';
                return;
            }

            // ── Extract data (same logic as gestionar_caso.php view) ──
            $caso = $casoData['caso'];
            $source = $casoData['source'];
            $titulo = $caso['titulo'] ?? 'Sin título';
            $estado = $caso['estado'] ?? 'Borrador';
            $casoId = (int) $caso['id'];
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

                // Tribute vars not available for borrador
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

                // ── Tribute calculations (same as gestionar_caso.php) ──
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

                // Build tarifa lookup
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

            // ── Render PDF template into HTML ──
            ob_start();
            include __DIR__ . '/../../../../../resources/views/professor/pdf/pdf_caso.php';
            $html = ob_get_clean();

            // ── Generate PDF with mPDF ──
            $mpdf = new \Mpdf\Mpdf([
                'mode'          => 'utf-8',
                'format'        => 'Letter',
                'margin_left'   => 15,
                'margin_right'  => 15,
                'margin_top'    => 15,
                'margin_bottom' => 15,
                'default_font'  => 'dejavusans',
            ]);

            $safeTitle = preg_replace('/[^a-zA-Z0-9_\- áéíóúñÁÉÍÓÚÑ]/', '', $titulo);
            $mpdf->SetTitle($safeTitle . ' — SPDSS');
            $mpdf->SetAuthor('SPDSS');
            $mpdf->WriteHTML($html);
            $mpdf->Output('caso_' . $casoId . '.pdf', 'I'); // I = inline (browser preview)

        } catch (\Throwable $e) {
            error_log('[CasosController::descargarPdf] ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            http_response_code(500);
            echo 'Error al generar el PDF: ' . $e->getMessage();
        }
    }
}

