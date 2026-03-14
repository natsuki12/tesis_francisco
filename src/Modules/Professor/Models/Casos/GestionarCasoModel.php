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
                    p_rep.nombres AS rep_nombres, p_rep.apellidos AS rep_apellidos,
                    p_rep.tipo_cedula AS rep_tipo_cedula, p_rep.cedula AS rep_cedula,
                    p_rep.sexo AS rep_sexo, p_rep.fecha_nacimiento AS rep_fecha_nacimiento,
                    p_rep.estado_civil AS rep_estado_civil,
                    ut.valor AS ut_valor, ut.anio AS ut_anio
                FROM sim_casos_estudios c
                LEFT JOIN sim_personas p_caus ON c.causante_id = p_caus.id
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
                per.estado_civil, per.fecha_nacimiento,
                par.etiqueta AS parentesco_nombre,
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

        // 7. Bienes inmuebles
        $stmt = $this->db->prepare("SELECT * FROM sim_caso_bienes_inmuebles 
            WHERE caso_estudio_id = :cid AND deleted_at IS NULL");
        $stmt->execute(['cid' => $casoId]);
        $bienesInmuebles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 8. Bienes muebles
        $stmt = $this->db->prepare("SELECT bm.*, 
                cat.nombre AS categoria_nombre, tbm.nombre AS tipo_nombre
            FROM sim_caso_bienes_muebles bm
            LEFT JOIN sim_cat_categorias_bien_mueble cat ON bm.categoria_bien_mueble_id = cat.id
            LEFT JOIN sim_cat_tipos_bien_mueble tbm ON bm.tipo_bien_mueble_id = tbm.id
            WHERE bm.caso_estudio_id = :cid AND bm.deleted_at IS NULL");
        $stmt->execute(['cid' => $casoId]);
        $bienesMuebles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 9. Pasivos deuda
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
            'resumen' => [
                'total_herederos' => count($herederos),
                'total_activos' => $totalActivos,
                'total_pasivos' => $totalPasivos,
                'patrimonio_neto' => $totalActivos - $totalPasivos,
                'total_bienes' => count($bienesInmuebles) + count($bienesMuebles),
                'total_items' => count($bienesInmuebles) + count($bienesMuebles) + count($pasivosDeuda) + count($pasivosGastos),
            ]
        ];
    }
}
