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

    public function getPersonaByDocumento(string $tipoCedula, string $cedula, string $pasaporte, string $rif)
    {
        $db = DB::connect();

        $sql = "SELECT p.id as persona_id, p.tipo_cedula, p.nacionalidad, p.cedula, p.pasaporte, p.rif_personal, 
                       p.nombres, p.apellidos, p.fecha_nacimiento, p.estado_civil, p.sexo, 
                       a.fecha_fallecimiento,
                       df.fecha_cierre_fiscal, df.domiciliado_pais
                FROM sim_personas p
                LEFT JOIN sim_actas_defunciones a ON a.sim_persona_id = p.id
                LEFT JOIN sim_causante_datos_fiscales df ON df.sim_persona_id = p.id
                WHERE 1=0";

        $params = [];

        if ($cedula !== '') {
            if ($tipoCedula !== '') {
                $sql .= " OR (p.tipo_cedula = :tipo_cedula AND p.cedula = :cedula)";
                $params['tipo_cedula'] = $tipoCedula;
                $params['cedula'] = $cedula;
            } else {
                $sql .= " OR p.cedula = :cedula";
                $params['cedula'] = $cedula;
            }
        }

        if ($pasaporte !== '') {
            $sql .= " OR p.pasaporte = :pasaporte";
            $params['pasaporte'] = $pasaporte;
        }

        if ($rif !== '') {
            $sql .= " OR p.rif_personal = :rif";
            $params['rif'] = $rif;
        }

        $sql .= " LIMIT 1";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetch() ?: null;
    }
}
