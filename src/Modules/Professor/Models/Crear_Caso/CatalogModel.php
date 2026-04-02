<?php
namespace App\Modules\Professor\Models\Crear_Caso;

use App\Core\DB;

class CatalogModel
{
    public function getUnidadesTributarias()
    {
        $db = DB::connect();
        $sql = "SELECT id as unidad_tributaria_id, valor, fecha_gaceta as fecha_entrada_vigencia, anio as gaceta FROM sim_cat_unidades_tributarias ORDER BY fecha_gaceta DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getTiposHerencia()
    {
        $db = DB::connect();
        $sql = "SELECT id, nombre FROM sim_cat_tipoherencias ORDER BY id ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getPaises()
    {
        $db = DB::connect();
        $sql = "SELECT id, nombre FROM paises ORDER BY nombre ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getParentescos()
    {
        $db = DB::connect();
        $sql = "SELECT id as parentesco_id, etiqueta as nombre, grupo_tarifa_id FROM sim_cat_parentescos WHERE activo = 1 ORDER BY id ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getTarifasSucesion()
    {
        $db = DB::connect();
        $sql = "SELECT grupo_tarifa_id, rango_desde_ut, rango_hasta_ut, porcentaje, sustraendo_ut
                  FROM sim_cat_tarifas_sucesion WHERE activo = 1
                  ORDER BY grupo_tarifa_id, rango_desde_ut";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getTiposBienInmueble()
    {
        $db = DB::connect();
        $sql = "SELECT id as tipo_bien_inmueble_id, nombre FROM sim_cat_tipos_bien_inmueble ORDER BY nombre ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getCategoriasBienMueble()
    {
        $db = DB::connect();
        $sql = "SELECT id as categoria_bien_mueble_id, nombre FROM sim_cat_categorias_bien_mueble ORDER BY id ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getTiposBienMueble($categoria_id = null)
    {
        $db = DB::connect();
        $sql = "SELECT id as tipo_bien_mueble_id, categoria_bien_mueble_id, nombre FROM sim_cat_tipos_bien_mueble";
        $params = [];
        if ($categoria_id !== null) {
            $sql .= " WHERE categoria_bien_mueble_id = ?";
            $params[] = $categoria_id;
        }
        $sql .= " ORDER BY nombre ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getBancos()
    {
        $db = DB::connect();
        $sql = "SELECT id as banco_id, nombre FROM sim_cat_bancos ORDER BY nombre ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getEmpresas()
    {
        $db = DB::connect();
        $sql = "SELECT id as empresa_id, razon_social as nombre, rif as rif_empresa FROM sim_empresas ORDER BY razon_social ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getTiposSemoviente()
    {
        $db = DB::connect();
        $sql = "SELECT id as tipo_semoviente_id, nombre FROM sim_cat_tipos_semoviente ORDER BY nombre ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getTiposPasivoDeuda()
    {
        $db = DB::connect();
        $sql = "SELECT id as tipo_pasivo_deuda_id, nombre FROM sim_cat_tipos_pasivo_deuda ORDER BY nombre ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getTiposPasivoGasto()
    {
        $db = DB::connect();
        $sql = "SELECT id as tipo_pasivo_gasto_id, nombre FROM sim_cat_tipos_pasivo_gasto ORDER BY nombre ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getEmpresaByRif(string $rif)
    {
        $db = DB::connect();
        $sql = "SELECT id as empresa_id, razon_social as nombre, rif as rif_empresa FROM sim_empresas WHERE rif = :rif LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->execute(['rif' => $rif]);
        return $stmt->fetch() ?: null;
    }

    public function searchEmpresas(string $query)
    {
        $db = DB::connect();
        $db->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false); // Permitir LIMIT seguro
        $sql = "SELECT id as empresa_id, razon_social, rif as rif_empresa 
                FROM sim_empresas 
                WHERE (rif LIKE :q1 OR razon_social LIKE :q2) 
                  AND activo = 1 
                ORDER BY razon_social ASC
                LIMIT 15";
        $stmt = $db->prepare($sql);
        $likeStr = '%' . trim($query) . '%';
        $stmt->execute(['q1' => $likeStr, 'q2' => $likeStr]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getSeccionesByProfesor(int $userId)
    {
        $db = DB::connect();
        $sql = "SELECT 
                    s.id,
                    s.nombre AS seccion,
                    p.nombre AS periodo
                FROM secciones s
                INNER JOIN profesores pr ON pr.id = s.profesor_id
                INNER JOIN users u ON u.persona_id = pr.persona_id
                INNER JOIN periodos p ON p.id = s.periodo_id
                WHERE u.id = :user_id
                ORDER BY p.fecha_inicio DESC, s.nombre";
        $stmt = $db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function getEstudiantesByProfesor(int $userId)
    {
        $db = DB::connect();
        $sql = "SELECT DISTINCT
                    e.id AS estudiante_id,
                    pe.cedula,
                    pe.nombres,
                    pe.apellidos,
                    s.nombre AS seccion,
                    p.nombre AS periodo
                FROM estudiantes e
                INNER JOIN personas pe ON pe.id = e.persona_id
                INNER JOIN inscripciones i ON i.estudiante_id = e.id
                INNER JOIN secciones s ON s.id = i.seccion_id
                INNER JOIN profesores pr ON pr.id = s.profesor_id
                INNER JOIN users u ON u.persona_id = pr.persona_id
                INNER JOIN periodos p ON p.id = s.periodo_id
                WHERE u.id = :user_id
                  AND p.activo = 1
                ORDER BY s.nombre, pe.apellidos, pe.nombres";
        $stmt = $db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function getPersonaByDocumento(string $tipoCedula, string $cedula, string $pasaporte, string $rif, int $personaId = 0)
    {
        $db = DB::connect();

        $sql = "SELECT p.id as persona_id, p.tipo_cedula, p.nacionalidad, p.cedula, p.pasaporte, p.rif_personal, 
                       p.nombres, p.apellidos, p.fecha_nacimiento, p.estado_civil, p.sexo, 
                       a.fecha_fallecimiento, a.numero_acta, a.year_acta, a.parroquia_registro AS parroquia_registro_id,
                       df.fecha_cierre_fiscal, df.domiciliado_pais
                FROM sim_personas p
                LEFT JOIN sim_actas_defunciones a ON a.sim_persona_id = p.id
                LEFT JOIN sim_causante_datos_fiscales df ON df.sim_persona_id = p.id
                WHERE 1=1 AND (";

        $params = [];

        if ($personaId > 0) {
            $sql .= " p.id = :persona_id";
            $params['persona_id'] = $personaId;
        } elseif ($cedula !== '') {
            if ($tipoCedula !== '') {
                $sql .= " p.tipo_cedula = :tipo_cedula AND p.cedula = :cedula";
                $params['tipo_cedula'] = $tipoCedula;
                $params['cedula'] = $cedula;
            } else {
                $sql .= " p.cedula = :cedula";
                $params['cedula'] = $cedula;
            }
        } elseif ($rif !== '') {
            $sql .= " p.rif_personal = :rif";
            $params['rif'] = $rif;
        } elseif ($pasaporte !== '') {
            $sql .= " p.pasaporte = :pasaporte";
            $params['pasaporte'] = $pasaporte;
        } else {
            $sql .= " 1=0"; // Fallback if no params actually sent
        }

        $sql .= ") LIMIT 1";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetch() ?: null;
    }

    /**
     * Búsqueda parcial de personas para dropdown autocomplete.
     * Si $query está vacío retorna todas; si tiene contenido, filtra con LIKE.
     */
    public function searchPersonas(string $query, string $campo = 'cedula', string $tipo = '', bool $sinCedula = false, bool $conDocumentos = false): array
    {
        $db = DB::connect();

        $select = "SELECT p.id AS persona_id, p.tipo_cedula, p.cedula, p.rif_personal,
                          p.nombres, p.apellidos";
        $from   = " FROM sim_personas p";
        $where  = " WHERE 1=1";
        $params = [];

        // Filtrar por tipo de cédula si se proporcionó (match estricto)
        if ($tipo !== '') {
            $where .= " AND p.tipo_cedula = :tipo";
            $params['tipo'] = $tipo;
        }

        // Only personas WITHOUT cédula (for Sin Cédula cases)
        if ($sinCedula) {
            $where .= " AND (p.cedula IS NULL OR p.cedula = '' OR p.tipo_cedula = 'No_Aplica')";
        }

        // Only personas WITH both cédula and RIF (for representante)
        if ($conDocumentos) {
            $where .= " AND p.cedula IS NOT NULL AND p.cedula != '' AND p.rif_personal IS NOT NULL AND p.rif_personal != ''";
        }

        // Filtrar con LIKE si hay query — buscar en cedula, rif, nombres y apellidos
        if ($query !== '') {
            $where .= " AND (p.cedula LIKE :q OR p.rif_personal LIKE :q2 OR p.nombres LIKE :q3 OR p.apellidos LIKE :q4)";
            $likeVal = '%' . $query . '%';
            $params['q']  = $likeVal;
            $params['q2'] = $likeVal;
            $params['q3'] = $likeVal;
            $params['q4'] = $likeVal;
        }

        $sql = $select . $from . $where . " ORDER BY p.apellidos ASC, p.nombres ASC, p.cedula ASC LIMIT 50";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
