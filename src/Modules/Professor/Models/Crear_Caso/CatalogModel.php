<?php
namespace App\Modules\Professor\Models\Crear_Caso;

use App\Core\DB;

class CatalogModel
{
    public function getUnidadesTributarias()
    {
        $db = DB::connect();
        $sql = "SELECT unidad_tributaria_id, valor, fecha_entrada_vigencia, gaceta FROM sim_cat_unidades_tributarias ORDER BY fecha_entrada_vigencia DESC";
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
        $sql = "SELECT id as parentesco_id, etiqueta as nombre FROM sim_cat_parentescos WHERE activo = 1 ORDER BY id ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getTiposBienInmueble()
    {
        $db = DB::connect();
        $sql = "SELECT tipo_bien_inmueble_id, tipo_bien_inmueble as nombre FROM sim_cat_tipos_bien_inmueble ORDER BY tipo_bien_inmueble ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getCategoriasBienMueble()
    {
        $db = DB::connect();
        $sql = "SELECT categoria_bien_mueble_id, categoria_bien_mueble as nombre FROM sim_cat_categorias_bien_mueble ORDER BY categoria_bien_mueble_id ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getTiposBienMueble($categoria_id = null)
    {
        $db = DB::connect();
        $sql = "SELECT tipo_bien_mueble_id, categoria_bien_mueble_id, tipo_bien_mueble as nombre FROM sim_cat_tipos_bien_mueble";
        $params = [];
        if ($categoria_id !== null) {
            $sql .= " WHERE categoria_bien_mueble_id = ?";
            $params[] = $categoria_id;
        }
        $sql .= " ORDER BY tipo_bien_mueble ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getBancos()
    {
        $db = DB::connect();
        $sql = "SELECT banco_id, banco as nombre FROM sim_cat_bancos ORDER BY banco ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getEmpresas()
    {
        $db = DB::connect();
        $sql = "SELECT empresa_id, razon_social as nombre, rif_empresa FROM sim_empresas ORDER BY razon_social ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getTiposSemoviente()
    {
        $db = DB::connect();
        $sql = "SELECT tipo_semoviente_id, tipo_semoviente as nombre FROM sim_cat_tipos_semoviente ORDER BY tipo_semoviente ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getTiposPasivoDeuda()
    {
        $db = DB::connect();
        $sql = "SELECT tipo_pasivo_deuda_id, tipo_pasivo_deuda as nombre FROM sim_cat_tipos_pasivo_deuda ORDER BY tipo_pasivo_deuda ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getTiposPasivoGasto()
    {
        $db = DB::connect();
        $sql = "SELECT tipo_pasivo_gasto_id, tipo_pasivo_gasto as nombre FROM sim_cat_tipos_pasivo_gasto ORDER BY tipo_pasivo_gasto ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
