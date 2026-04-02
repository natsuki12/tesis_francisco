<?php
declare(strict_types=1);

namespace App\Modules\Professor\Models\Casos;

use App\Core\DB;
use PDO;

/**
 * Modelo para obtener toda la información de un caso publicado
 * desde las tablas normalizadas de la BD.
 * Usado por la vista gestionar_caso.php.
 */
class GestionarCasoModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = DB::connect();
    }

    /**
     * Retorna toda la información del caso en un array asociativo.
     * Si el caso es borrador, parsea el borrador_json.
     * Si es publicado, consulta las tablas normalizadas.
     */
    public function getFullCaseById(int $casoId, int $profesorId): ?array
    {
        // 1. Obtener caso base
        $sql = "SELECT c.*, 
                    p_caus.nombres AS causante_nombres, p_caus.apellidos AS causante_apellidos,
                    p_caus.tipo_cedula AS causante_tipo_cedula, p_caus.cedula AS causante_cedula,
                    p_caus.sexo AS causante_sexo, p_caus.estado_civil AS causante_estado_civil,
                    p_caus.fecha_nacimiento AS causante_fecha_nacimiento,
                    p_caus.pasaporte AS causante_pasaporte,
                    p_caus.rif_personal AS causante_rif_personal,
                    p_caus.nacionalidad AS causante_nacionalidad_id,
                    pais_caus.nombre AS causante_nacionalidad_nombre,
                    p_rep.nombres AS rep_nombres, p_rep.apellidos AS rep_apellidos,
                    p_rep.tipo_cedula AS rep_tipo_cedula, p_rep.cedula AS rep_cedula,
                    p_rep.sexo AS rep_sexo, p_rep.fecha_nacimiento AS rep_fecha_nacimiento,
                    p_rep.estado_civil AS rep_estado_civil,
                    p_rep.pasaporte AS rep_pasaporte,
                    p_rep.rif_personal AS rep_rif_personal,
                    ut.valor AS ut_valor, ut.anio AS ut_anio
                FROM sim_casos_estudios c
                LEFT JOIN sim_personas p_caus ON c.causante_id = p_caus.id
                LEFT JOIN paises pais_caus ON p_caus.nacionalidad = pais_caus.id
                LEFT JOIN sim_personas p_rep ON c.representante_id = p_rep.id
                LEFT JOIN sim_cat_unidades_tributarias ut ON c.unidad_tributaria_id = ut.id
                WHERE c.id = :id AND c.profesor_id = :prof AND c.estado != 'Eliminado'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $casoId, 'prof' => $profesorId]);
        $caso = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$caso)
            return null;

        // Si es borrador, devolver datos del JSON
        if ($caso['estado'] === 'Borrador' && !empty($caso['borrador_json'])) {
            $json = json_decode($caso['borrador_json'], true);
            return [
                'caso' => $caso,
                'source' => 'borrador',
                'borrador' => $json
            ];
        }

        // 2. Acta de defunción del causante
        $acta = null;
        if ($caso['causante_id']) {
            $stmt = $this->db->prepare("SELECT ad.*, ad.parroquia_registro AS parroquia_nombre
                FROM sim_actas_defunciones ad
                WHERE ad.sim_persona_id = :pid");
            $stmt->execute(['pid' => $caso['causante_id']]);
            $acta = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        }

        // 3. Datos fiscales
        $datosFiscales = null;
        if ($caso['causante_id']) {
            $stmt = $this->db->prepare("SELECT * FROM sim_causante_datos_fiscales WHERE sim_persona_id = :pid");
            $stmt->execute(['pid' => $caso['causante_id']]);
            $datosFiscales = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        }

        // 4. Direcciones
        $stmt = $this->db->prepare("SELECT d.*, 
                e.nombre AS estado_nombre, m.nombre AS municipio_nombre, 
                p.nombre AS parroquia_nombre, ci.nombre AS ciudad_nombre,
                cp.codigo AS codigo_postal_codigo
            FROM sim_caso_direcciones d
            LEFT JOIN estados e ON d.estado_id = e.id
            LEFT JOIN municipios m ON d.municipio_id = m.id
            LEFT JOIN parroquias p ON d.parroquia_id = p.id
            LEFT JOIN ciudades ci ON d.ciudad_id = ci.id
            LEFT JOIN codigos_postales cp ON d.codigo_postal_id = cp.id
            WHERE d.sim_caso_estudio_id = :cid");
        $stmt->execute(['cid' => $casoId]);
        $direcciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 5. Tipos de herencia
        $stmt = $this->db->prepare("SELECT r.*, th.nombre AS tipo_nombre
            FROM sim_caso_tipoherencia_rel r
            LEFT JOIN sim_cat_tipoherencias th ON r.tipo_herencia_id = th.id
            WHERE r.caso_estudio_id = :cid");
        $stmt->execute(['cid' => $casoId]);
        $herencia = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 6. Herederos (participantes)
        $stmt = $this->db->prepare("SELECT cp.*, 
                per.nombres, per.apellidos, per.tipo_cedula, per.cedula, per.sexo,
                per.estado_civil, per.fecha_nacimiento, per.pasaporte, per.rif_personal,
                par.etiqueta AS parentesco_nombre, par.grupo_tarifa_id,
                ad.fecha_fallecimiento,
                CONCAT(p_padre.nombres, ' ', p_padre.apellidos) AS premuerto_padre_nombre
            FROM sim_caso_participantes cp
            JOIN sim_personas per ON cp.persona_id = per.id
            LEFT JOIN sim_cat_parentescos par ON cp.parentesco_id = par.id
            LEFT JOIN sim_actas_defunciones ad ON ad.sim_persona_id = per.id
            LEFT JOIN sim_caso_participantes cp_padre ON cp.premuerto_padre_id = cp_padre.id
            LEFT JOIN sim_personas p_padre ON cp_padre.persona_id = p_padre.id
            WHERE cp.caso_estudio_id = :cid
            ORDER BY cp.es_premuerto ASC, cp.id ASC");
        $stmt->execute(['cid' => $casoId]);
        $herederos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 7. Bienes inmuebles (con tipos de bien)
        $stmt = $this->db->prepare("SELECT bi.*, 
                GROUP_CONCAT(tbi.nombre ORDER BY tbi.nombre SEPARATOR ', ') AS tipo_bien_nombre
            FROM sim_caso_bienes_inmuebles bi
            LEFT JOIN sim_caso_bien_inmueble_tipo_rel rel ON bi.id = rel.bien_inmueble_id
            LEFT JOIN sim_cat_tipos_bien_inmueble tbi ON rel.tipo_bien_inmueble_id = tbi.id
            WHERE bi.caso_estudio_id = :cid AND bi.deleted_at IS NULL
            GROUP BY bi.id");
        $stmt->execute(['cid' => $casoId]);
        $bienesInmuebles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 8. Bienes muebles (con detalle banco para categoría 1)
        $stmt = $this->db->prepare("SELECT bm.*, 
                cat.nombre AS categoria_nombre, tbm.nombre AS tipo_nombre,
                bmb.numero_cuenta, b_cat.nombre AS banco_nombre
            FROM sim_caso_bienes_muebles bm
            LEFT JOIN sim_cat_categorias_bien_mueble cat ON bm.categoria_bien_mueble_id = cat.id
            LEFT JOIN sim_cat_tipos_bien_mueble tbm ON bm.tipo_bien_mueble_id = tbm.id
            LEFT JOIN sim_caso_bm_banco bmb ON bmb.bien_mueble_id = bm.id
            LEFT JOIN sim_cat_bancos b_cat ON bmb.banco_id = b_cat.id
            WHERE bm.caso_estudio_id = :cid AND bm.deleted_at IS NULL");
        $stmt->execute(['cid' => $casoId]);
        $bienesMuebles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 8c. Cargar detalles específicos por categoría de bienes muebles
        $bmIds = array_column($bienesMuebles, 'id');
        if (!empty($bmIds)) {
            $placeholders = implode(',', array_fill(0, count($bmIds), '?'));

            // Seguro
            $stmt = $this->db->prepare("SELECT s.bien_mueble_id, s.numero_prima, e.razon_social AS empresa_nombre
                FROM sim_caso_bm_seguro s LEFT JOIN sim_empresas e ON s.empresa_id = e.id
                WHERE s.bien_mueble_id IN ($placeholders)");
            $stmt->execute($bmIds);
            $seguroMap = [];
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) $seguroMap[$r['bien_mueble_id']] = $r;

            // Transporte
            $stmt = $this->db->prepare("SELECT * FROM sim_caso_bm_transporte WHERE bien_mueble_id IN ($placeholders)");
            $stmt->execute($bmIds);
            $transporteMap = [];
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) $transporteMap[$r['bien_mueble_id']] = $r;

            // Acciones
            $stmt = $this->db->prepare("SELECT a.bien_mueble_id, e.razon_social AS empresa_nombre
                FROM sim_caso_bm_acciones a LEFT JOIN sim_empresas e ON a.empresa_id = e.id
                WHERE a.bien_mueble_id IN ($placeholders)");
            $stmt->execute($bmIds);
            $accionesMap = [];
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) $accionesMap[$r['bien_mueble_id']] = $r;

            // Bonos
            $stmt = $this->db->prepare("SELECT * FROM sim_caso_bm_bonos WHERE bien_mueble_id IN ($placeholders)");
            $stmt->execute($bmIds);
            $bonosMap = [];
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) $bonosMap[$r['bien_mueble_id']] = $r;

            // Cuentas por Cobrar
            $stmt = $this->db->prepare("SELECT * FROM sim_caso_bm_cuentas_cobrar WHERE bien_mueble_id IN ($placeholders)");
            $stmt->execute($bmIds);
            $cuentasCobrarMap = [];
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) $cuentasCobrarMap[$r['bien_mueble_id']] = $r;

            // Opciones de Compra
            $stmt = $this->db->prepare("SELECT * FROM sim_caso_bm_opciones_compra WHERE bien_mueble_id IN ($placeholders)");
            $stmt->execute($bmIds);
            $opcionesMap = [];
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) $opcionesMap[$r['bien_mueble_id']] = $r;

            // Prestaciones Sociales
            $stmt = $this->db->prepare("SELECT p.bien_mueble_id, p.posee_banco, p.numero_cuenta,
                    b.nombre AS banco_prestaciones_nombre, e.razon_social AS empresa_nombre
                FROM sim_caso_bm_prestaciones p
                LEFT JOIN sim_cat_bancos b ON p.banco_id = b.id
                LEFT JOIN sim_empresas e ON p.empresa_id = e.id
                WHERE p.bien_mueble_id IN ($placeholders)");
            $stmt->execute($bmIds);
            $prestacionesMap = [];
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) $prestacionesMap[$r['bien_mueble_id']] = $r;

            // Semovientes
            $stmt = $this->db->prepare("SELECT s.bien_mueble_id, s.cantidad, ts.nombre AS tipo_semoviente_nombre
                FROM sim_caso_bm_semovientes s
                LEFT JOIN sim_cat_tipos_semoviente ts ON s.tipo_semoviente_id = ts.id
                WHERE s.bien_mueble_id IN ($placeholders)");
            $stmt->execute($bmIds);
            $semovientesMap = [];
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) $semovientesMap[$r['bien_mueble_id']] = $r;

            // Caja de Ahorro
            $stmt = $this->db->prepare("SELECT c.bien_mueble_id, e.razon_social AS empresa_nombre
                FROM sim_caso_bm_caja_ahorro c LEFT JOIN sim_empresas e ON c.empresa_id = e.id
                WHERE c.bien_mueble_id IN ($placeholders)");
            $stmt->execute($bmIds);
            $cajaAhorroMap = [];
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) $cajaAhorroMap[$r['bien_mueble_id']] = $r;

            // Adjuntar detalles a cada bien mueble
            foreach ($bienesMuebles as &$bm) {
                $id = $bm['id'];
                $bm['detalle_seguro'] = $seguroMap[$id] ?? null;
                $bm['detalle_transporte'] = $transporteMap[$id] ?? null;
                $bm['detalle_acciones'] = $accionesMap[$id] ?? null;
                $bm['detalle_bonos'] = $bonosMap[$id] ?? null;
                $bm['detalle_cuentas_cobrar'] = $cuentasCobrarMap[$id] ?? null;
                $bm['detalle_opciones_compra'] = $opcionesMap[$id] ?? null;
                $bm['detalle_prestaciones'] = $prestacionesMap[$id] ?? null;
                $bm['detalle_semovientes'] = $semovientesMap[$id] ?? null;
                $bm['detalle_caja_ahorro'] = $cajaAhorroMap[$id] ?? null;
            }
            unset($bm);
        }

        // 8b. Datos litigiosos (para inmuebles y muebles)
        $stmt = $this->db->prepare("SELECT * FROM sim_caso_bienes_litigiosos WHERE caso_estudio_id = :cid");
        $stmt->execute(['cid' => $casoId]);
        $litigiosos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // Indexar por tipo+id para acceso rápido
        $litigiosoMap = [];
        foreach ($litigiosos as $lit) {
            $key = ($lit['bien_tipo'] ?? '') . '_' . ($lit['bien_id'] ?? 0);
            $litigiosoMap[$key] = $lit;
        }
        // Adjuntar a bienes inmuebles
        foreach ($bienesInmuebles as &$bi) {
            $key = 'Inmueble_' . $bi['id'];
            $bi['litigioso_data'] = $litigiosoMap[$key] ?? null;
        }
        unset($bi);
        // Adjuntar a bienes muebles
        foreach ($bienesMuebles as &$bm) {
            $key = 'Mueble_' . $bm['id'];
            $bm['litigioso_data'] = $litigiosoMap[$key] ?? null;
        }
        unset($bm);

        $stmt = $this->db->prepare("SELECT pd.*, 
                tpd.nombre AS tipo_nombre, b.nombre AS banco_nombre
            FROM sim_caso_pasivos_deuda pd
            LEFT JOIN sim_cat_tipos_pasivo_deuda tpd ON pd.tipo_pasivo_deuda_id = tpd.id
            LEFT JOIN sim_cat_bancos b ON pd.banco_id = b.id
            WHERE pd.caso_estudio_id = :cid AND pd.deleted_at IS NULL");
        $stmt->execute(['cid' => $casoId]);
        $pasivosDeuda = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 10. Pasivos gastos
        $stmt = $this->db->prepare("SELECT pg.*, tpg.nombre AS tipo_nombre
            FROM sim_caso_pasivos_gastos pg
            LEFT JOIN sim_cat_tipos_pasivo_gasto tpg ON pg.tipo_pasivo_gasto_id = tpg.id
            WHERE pg.caso_estudio_id = :cid AND pg.deleted_at IS NULL");
        $stmt->execute(['cid' => $casoId]);
        $pasivosGastos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 11. Exenciones
        $stmt = $this->db->prepare("SELECT * FROM sim_caso_exenciones 
            WHERE caso_estudio_id = :cid AND deleted_at IS NULL");
        $stmt->execute(['cid' => $casoId]);
        $exenciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 12. Exoneraciones
        $stmt = $this->db->prepare("SELECT * FROM sim_caso_exoneraciones 
            WHERE caso_estudio_id = :cid AND deleted_at IS NULL");
        $stmt->execute(['cid' => $casoId]);
        $exoneraciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 13. Prórrogas
        $stmt = $this->db->prepare("SELECT * FROM sim_caso_prorrogas WHERE caso_estudio_id = :cid");
        $stmt->execute(['cid' => $casoId]);
        $prorrogas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 14. Configuraciones (múltiples por caso) con estudiantes anidados
        $stmt = $this->db->prepare("SELECT * FROM sim_caso_configs WHERE caso_id = :cid ORDER BY id ASC");
        $stmt->execute(['cid' => $casoId]);
        $configs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 15. Para cada config, traer sus estudiantes asignados
        foreach ($configs as &$cfg) {
            try {
                $stmt = $this->db->prepare("SELECT a.*, 
                        per.nombres, per.apellidos, per.cedula
                    FROM sim_caso_asignaciones a
                    JOIN estudiantes est ON a.estudiante_id = est.id
                    JOIN personas per ON est.persona_id = per.id
                    WHERE a.config_id = :cid
                    ORDER BY per.apellidos ASC, per.nombres ASC");
                $stmt->execute(['cid' => $cfg['id']]);
                $cfg['estudiantes'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (\Throwable $e) {
                $cfg['estudiantes'] = [];
            }
        }
        unset($cfg); // romper referencia

        // Mantener compatibilidad: config plano y asignaciones planas para borrador
        $config = $configs[0] ?? null;
        $asignaciones = [];
        foreach ($configs as $c) {
            foreach ($c['estudiantes'] as $est) {
                $asignaciones[] = $est;
            }
        }

        // Cálculos de resumen
        $totalActivos = 0;
        foreach ($bienesInmuebles as $bi)
            $totalActivos += (float) ($bi['valor_declarado'] ?? 0);
        foreach ($bienesMuebles as $bm)
            $totalActivos += (float) ($bm['valor_declarado'] ?? 0);
        $totalPasivos = 0;
        foreach ($pasivosDeuda as $pd)
            $totalPasivos += (float) ($pd['valor_declarado'] ?? 0);
        foreach ($pasivosGastos as $pg)
            $totalPasivos += (float) ($pg['valor_declarado'] ?? 0);

        // 12. Tarifas de sucesión
        $stmt = $this->db->prepare("SELECT * FROM sim_cat_tarifas_sucesion WHERE activo = 1 ORDER BY grupo_tarifa_id, rango_desde_ut");
        $stmt->execute();
        $tarifas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 13. Grupos de tarifa
        $stmt = $this->db->prepare("SELECT * FROM sim_cat_grupos_tarifa WHERE activo = 1 ORDER BY id");
        $stmt->execute();
        $gruposTarifa = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 14. Cálculo manual (si existe)
        $stmt = $this->db->prepare("SELECT cm.*, CONCAT(p.nombres, ' ', p.apellidos) AS heredero_nombre
            FROM sim_caso_calculo_manual cm
            LEFT JOIN sim_caso_participantes cp ON cm.participante_id = cp.id
            LEFT JOIN sim_personas p ON cp.persona_id = p.id
            WHERE cm.caso_estudio_id = :cid");
        $stmt->execute(['cid' => $casoId]);
        $calculoManual = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Totals for resumen
        $totalExenciones = 0;
        foreach ($exenciones as $ex) $totalExenciones += (float)($ex['valor_declarado'] ?? 0);
        $totalExoneraciones = 0;
        foreach ($exoneraciones as $eo) $totalExoneraciones += (float)($eo['valor_declarado'] ?? 0);

        return [
            'caso' => $caso,
            'source' => 'publicado',
            'acta_defuncion' => $acta,
            'datos_fiscales' => $datosFiscales,
            'direcciones' => $direcciones,
            'herencia' => $herencia,
            'herederos' => $herederos,
            'bienes_inmuebles' => $bienesInmuebles,
            'bienes_muebles' => $bienesMuebles,
            'pasivos_deuda' => $pasivosDeuda,
            'pasivos_gastos' => $pasivosGastos,
            'exenciones' => $exenciones,
            'exoneraciones' => $exoneraciones,
            'prorrogas' => $prorrogas,
            'config' => $config,
            'configs' => $configs,
            'asignaciones' => $asignaciones,
            'tarifas' => $tarifas,
            'grupos_tarifa' => $gruposTarifa,
            'calculo_manual' => $calculoManual,
            'resumen' => [
                'total_herederos' => count($herederos),
                'total_activos' => $totalActivos,
                'total_pasivos' => $totalPasivos,
                'total_exenciones' => $totalExenciones,
                'total_exoneraciones' => $totalExoneraciones,
                'patrimonio_neto' => $totalActivos - $totalPasivos - $totalExenciones - $totalExoneraciones,
                'total_bienes' => count($bienesInmuebles) + count($bienesMuebles),
                'total_items' => count($bienesInmuebles) + count($bienesMuebles) + count($pasivosDeuda) + count($pasivosGastos),
            ]
        ];
    }
}
