function getBaseUrl() {
    let url = window.BASE_URL || '/tesis_francisco/public';
    return url.replace(/\/+$/, '');
}

const catalogsCache = {
    unidadesTributarias: [],
    tiposHerencia: [],
    paises: [],
    parentescos: [],
    tiposBienInmueble: [],
    categoriasBienMueble: [],
    tiposBienMueble: {}, // por id de categoria
    bancos: [],
    empresas: [],
    tiposSemoviente: [],
    tiposPasivoDeuda: [],
    tiposPasivoGasto: []
};

async function fetchCatalog(endpoint, errorMessage) {
    try {
        const baseUrl = getBaseUrl();
        const response = await fetch(`${baseUrl}/api/${endpoint}`);
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        const data = await response.json();
        if (data.success) {
            return data.data;
        } else {
            console.error(errorMessage, data.message);
            return [];
        }
    } catch (error) {
        console.error(errorMessage, error);
        return [];
    }
}

export async function initCatalogos() {
    console.log('Inicializando catálogos...');

    const [
        ut,
        herencia,
        paises,
        parentescos,
        inmuebles,
        muebleCategorias,
        bancos,
        empresas,
        semovientes,
        deudas,
        gastos
    ] = await Promise.all([
        fetchCatalog('unidades-tributarias', 'Error cargando Unidades Tributarias'),
        fetchCatalog('tipos-herencia', 'Error cargando Tipos Herencia'),
        fetchCatalog('paises', 'Error cargando Paises'),
        fetchCatalog('parentescos', 'Error cargando Parentescos'),
        fetchCatalog('tipos-bien-inmueble', 'Error cargando Tipos Bien Inmueble'),
        fetchCatalog('categorias-bien-mueble', 'Error cargando Categorías Mueble'),
        fetchCatalog('bancos', 'Error cargando Bancos'),
        fetchCatalog('empresas', 'Error cargando Empresas'),
        fetchCatalog('tipos-semoviente', 'Error cargando Tipos Semoviente'),
        fetchCatalog('tipos-pasivo-deuda', 'Error cargando Tipos Pasivo Deuda'),
        fetchCatalog('tipos-pasivo-gasto', 'Error cargando Tipos Pasivo Gasto')
    ]);

    catalogsCache.unidadesTributarias = ut;
    catalogsCache.tiposHerencia = herencia;
    catalogsCache.paises = paises;
    catalogsCache.parentescos = parentescos;
    catalogsCache.tiposBienInmueble = inmuebles;
    catalogsCache.categoriasBienMueble = muebleCategorias;
    catalogsCache.bancos = bancos;
    catalogsCache.empresas = empresas;
    catalogsCache.tiposSemoviente = semovientes;
    catalogsCache.tiposPasivoDeuda = deudas;
    catalogsCache.tiposPasivoGasto = gastos;

    if (muebleCategorias) {
        const promises = muebleCategorias.map(async cat => {
            const id = cat.categoria_bien_mueble_id;
            const tipos = await fetchCatalog(`tipos-bien-mueble?categoria_id=${id}`, 'Error cargando Tipos Bien Mueble');
            catalogsCache.tiposBienMueble[id] = tipos;
        });
        await Promise.all(promises);
    }

    console.log('Catálogos cargados:', catalogsCache);
}

export function getCatalogs() {
    return catalogsCache;
}
