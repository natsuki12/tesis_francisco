<?php
declare(strict_types=1);

$pageTitle = 'Catálogos del Sistema';
$activePage = 'catalogos';
$breadcrumbs = [
    'Inicio' => base_url('/admin'),
    'Configuración' => '#',
    'Catálogos' => '#'
];

$extraCss = '
    <link rel="stylesheet" href="' . asset('css/shared/data-table.css') . '">
    <link rel="stylesheet" href="' . asset('css/admin/dashboard.css') . '">
    <link rel="stylesheet" href="' . asset('css/admin/configuracion.css') . '">
';

// Datos inyectados por el controlador
$unidadesTributarias   = $unidadesTributarias   ?? [];
$gruposTarifa          = $gruposTarifa          ?? [];
$tramosTarifa          = $tramosTarifa          ?? [];
$reducciones           = $reducciones           ?? [];
$tiposBienInmueble    = $tiposBienInmueble    ?? [];
$categoriasBienMueble = $categoriasBienMueble ?? [];
$tiposBienMueble      = $tiposBienMueble      ?? [];
$tiposSemoviente      = $tiposSemoviente      ?? [];
$parentescos           = $parentescos           ?? [];
$tiposPasivoDeuda     = $tiposPasivoDeuda     ?? [];
$tiposPasivoGasto     = $tiposPasivoGasto     ?? [];
$tipoHerencias        = $tipoHerencias        ?? [];

// UT vigente (primera fila = más reciente por año)
$utVigente = $unidadesTributarias[0] ?? null;

ob_start();
?>

<div class="page-header">
    <div class="page-header-left">
        <h1>Catálogos Maestros</h1>
        <p>Tablas maestras del proceso sucesoral SENIAT que alimentan los formularios del simulador.</p>
    </div>
</div>

<!-- Tabs Navigation -->
<div class="config-tabs-nav">
    <button class="config-tab-btn active" onclick="switchTab('tab-ut', this)">
        Unidad Tributaria
        <span class="filter-count"><?= count($unidadesTributarias) ?></span>
    </button>
    <button class="config-tab-btn" onclick="switchTab('tab-fiscal', this)">
        Fiscal
        <span class="filter-count"><?= count($gruposTarifa) + count($tramosTarifa) + count($reducciones) ?></span>
    </button>
    <button class="config-tab-btn" onclick="switchTab('tab-bienes', this)">
        Bienes
        <span class="filter-count"><?= count($tiposBienInmueble) + count($categoriasBienMueble) + count($tiposBienMueble) + count($tiposSemoviente) ?></span>
    </button>
    <button class="config-tab-btn" onclick="switchTab('tab-parentescos', this)">
        Parentescos
        <span class="filter-count"><?= count($parentescos) ?></span>
    </button>
    <button class="config-tab-btn" onclick="switchTab('tab-pasivos', this)">
        Pasivos y Herencias
        <span class="filter-count"><?= count($tiposPasivoDeuda) + count($tiposPasivoGasto) + count($tipoHerencias) ?></span>
    </button>
</div>

<!-- ═══════════════════════════════════════════════════════════
     TAB 1: UNIDAD TRIBUTARIA
     ═══════════════════════════════════════════════════════════ -->
<div id="tab-ut" class="config-tab-pane active">

    <!-- Stat Cards - Valor UT Vigente -->
    <?php if ($utVigente): ?>
    <div class="admin-stats-row" style="grid-template-columns:repeat(3,1fr); margin-bottom:20px;">
        <div class="admin-stat-card stat-card">
            <div class="admin-stat-card__info">
                <span class="admin-stat-card__label">Valor Vigente</span>
                <span class="admin-stat-card__value text-blue">Bs. <?= e(number_format((float)($utVigente['valor'] ?? 0), 2, ',', '.')) ?></span>
            </div>
        </div>
        <div class="admin-stat-card stat-card">
            <div class="admin-stat-card__info">
                <span class="admin-stat-card__label">Año</span>
                <span class="admin-stat-card__value"><?= (int)($utVigente['anio'] ?? 0) ?></span>
            </div>
        </div>
        <div class="admin-stat-card stat-card">
            <div class="admin-stat-card__info">
                <span class="admin-stat-card__label">Fecha Gaceta</span>
                <span class="admin-stat-card__value" style="font-size:20px;">
                <?php
                try {
                    echo (new \DateTime($utVigente['fecha_gaceta']))->format('d/m/Y');
                } catch (\Throwable $e) {
                    echo e($utVigente['fecha_gaceta'] ?? '—');
                }
                ?>
                </span>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Toolbar -->
    <div class="toolbar">
        <div class="toolbar-left">
            <div class="search-box">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                <input type="text" data-search-for="tbl-ut" placeholder="Buscar por año o valor...">
            </div>
            <button class="btn btn-primary" onclick="abrirCrearUT()" style="white-space:nowrap;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Registrar UT</button>
        </div>
        <div class="toolbar-right">
            <select class="per-page-select" data-status-for="tbl-ut" onchange="filterEstadoDataTable(this)" style="min-width:100px;"><option value="">Todos</option><option value="1">Activos</option><option value="0">Inactivos</option></select>
            <label style="font-size:var(--text-xs); color:var(--gray-500); display:flex; align-items:center; gap:6px;">
                Mostrar <select data-perpage-for="tbl-ut" class="per-page-select"><option value="10">10</option><option value="15" selected>15</option><option value="25">25</option></select> filas
            </label>
        </div>
    </div>

    <div class="table-container">
        <table class="data-table" id="tbl-ut">
            <thead>
                <tr>
                    <th class="sortable" data-col="0" style="width:80px">Año</th>
                    <th class="sortable" data-col="1">Valor (Bs.)</th>
                    <th class="sortable" data-col="2">Fecha Gaceta</th>
                    <th style="width:90px">Estado</th>
                    <th style="width:90px">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($unidadesTributarias)): ?>
                    <tr class="empty-row"><td colspan="4" style="text-align:center; padding:30px; color:var(--gray-400);">Sin registros.</td></tr>
                <?php else: ?>
                    <?php foreach ($unidadesTributarias as $ut):
                        $activo = (int)($ut['activo'] ?? 0);
                        $fechaFmt = '';
                        try { $fechaFmt = (new \DateTime($ut['fecha_gaceta']))->format('d/m/Y'); } catch (\Throwable $e) { $fechaFmt = e($ut['fecha_gaceta'] ?? '—'); }
                    ?>
                        <tr data-search="<?= e(mb_strtolower($ut['anio'] . ' ' . $ut['valor'] . ' ' . $fechaFmt)) ?>" data-activo="<?= $activo ?>">
                            <td><strong><?= (int)$ut['anio'] ?></strong></td>
                            <td>Bs. <?= e(number_format((float)($ut['valor'] ?? 0), 2, ',', '.')) ?></td>
                            <td><?= $fechaFmt ?></td>
                            <td>
                                <?php if ($ut === $utVigente): ?>
                                    <span class="status-badge status-published">Vigente</span>
                                <?php else: ?>
                                    <span class="status-badge status-draft">Histórico</span>
                                <?php endif; ?>
                            </td>
                            <td><div class="row-actions">
                                <button class="row-action-btn" title="Editar" onclick="abrirEditarUT(<?= (int)$ut['id'] ?>, <?= (int)$ut['anio'] ?>, '<?= e($ut['valor']) ?>', '<?= e($ut['fecha_gaceta'] ?? '') ?>')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>
                                <button class="row-action-btn" title="<?= $activo ? 'Desactivar' : 'Activar' ?>" onclick="toggleActivo('sim_cat_unidades_tributarias', <?= (int)$ut['id'] ?>)" style="color:<?= $activo ? 'var(--amber-500)' : 'var(--green-500)' ?>;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18.36 6.64A9 9 0 1 1 5.64 6.64"/><line x1="12" y1="2" x2="12" y2="12"/></svg></button>
                            </div></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        <div class="table-footer" data-footer-for="tbl-ut">
            <div class="table-footer-info"></div>
            <div class="pagination"></div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════
     TAB 2: FISCAL
     ═══════════════════════════════════════════════════════════ -->
<div id="tab-fiscal" class="config-tab-pane">

    <div class="config-subtabs-nav">
        <button class="config-subtab-btn active" onclick="switchSubTab('sub-grupos', this, 'tab-fiscal')">
            Grupos de Tarifa <span class="filter-count"><?= count($gruposTarifa) ?></span>
        </button>
        <button class="config-subtab-btn" onclick="switchSubTab('sub-tramos', this, 'tab-fiscal')">
            Tramos Progresivos <span class="filter-count"><?= count($tramosTarifa) ?></span>
        </button>
        <button class="config-subtab-btn" onclick="switchSubTab('sub-reducciones', this, 'tab-fiscal')">
            Reducciones Art. 11 <span class="filter-count"><?= count($reducciones) ?></span>
        </button>
    </div>

    <!-- Sub: Grupos de Tarifa -->
    <div id="sub-grupos" class="config-subpane active">
        <div class="table-container">
            <table class="data-table">
                <thead><tr><th style="width:50px">ID</th><th>Nombre del Grupo</th><th style="width:90px">Estado</th></tr></thead>
                <tbody>
                    <?php if (empty($gruposTarifa)): ?>
                        <tr class="empty-row"><td colspan="3" style="text-align:center; padding:20px; color:var(--gray-400);">Sin registros.</td></tr>
                    <?php else: ?>
                        <?php foreach ($gruposTarifa as $g): ?>
                            <tr>
                                <td style="color:var(--gray-400); font-size:12px;"><?= (int)$g['id'] ?></td>
                                <td><?= e($g['nombre'] ?? '') ?></td>
                                <td><?= (int)($g['activo'] ?? 1) ? '<span class="status-badge status-published">Activo</span>' : '<span class="status-badge status-draft">Inactivo</span>' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>


    <!-- Sub: Tramos de Tarifa Progresiva -->
    <div id="sub-tramos" class="config-subpane">
        <div class="toolbar">
            <div class="toolbar-left">
                <div class="search-box">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                    <input type="text" data-search-for="tbl-tramos" placeholder="Buscar tramo o grupo...">
                </div>
            </div>
            <div class="toolbar-right">
                <label style="font-size:var(--text-xs); color:var(--gray-500); display:flex; align-items:center; gap:6px;">
                    Mostrar <select data-perpage-for="tbl-tramos" class="per-page-select"><option value="10">10</option><option value="15" selected>15</option><option value="32">32</option></select> filas
                </label>
            </div>
        </div>
        <div class="table-container">
            <table class="data-table" id="tbl-tramos">
                <thead>
                    <tr>
                        <th class="sortable" data-col="0" style="width:40px">Nº</th>
                        <th class="sortable" data-col="1">Grupo</th>
                        <th class="sortable" data-col="2">Desde (UT)</th>
                        <th class="sortable" data-col="3">Hasta (UT)</th>
                        <th class="sortable" data-col="4">%</th>
                        <th class="sortable" data-col="5">Sustraendo (UT)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($tramosTarifa)): ?>
                        <tr class="empty-row"><td colspan="6" style="text-align:center; padding:20px; color:var(--gray-400);">Sin registros.</td></tr>
                    <?php else: ?>
                        <?php foreach ($tramosTarifa as $t): ?>
                            <tr data-search="<?= e(mb_strtolower(($t['grupo'] ?? '') . ' ' . $t['tramo'] . ' ' . $t['porcentaje'])) ?>">
                                <td style="font-weight:600; color:var(--gray-500);"><?= (int)$t['tramo'] ?></td>
                                <td><span style="font-size:12px;"><?= e($t['grupo'] ?? '—') ?></span></td>
                                <td><?= e(number_format((float)($t['limite_inferior_ut'] ?? 0), 2)) ?></td>
                                <td><?= $t['limite_superior_ut'] !== null ? e(number_format((float)$t['limite_superior_ut'], 2)) : '<span style="color:var(--gray-400);">∞</span>' ?></td>
                                <td><strong><?= e(number_format((float)($t['porcentaje'] ?? 0), 2)) ?>%</strong></td>
                                <td><?= e(number_format((float)($t['sustraendo_ut'] ?? 0), 2)) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            <div class="table-footer" data-footer-for="tbl-tramos">
                <div class="table-footer-info"></div>
                <div class="pagination"></div>
            </div>
        </div>
    </div>

    <!-- Sub: Reducciones Art. 11 -->
    <div id="sub-reducciones" class="config-subpane">
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:40px">Ord.</th>
                        <th>Descripción</th>
                        <th style="width:80px">% Reduc.</th>
                        <th style="width:100px">Condición</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($reducciones)): ?>
                        <tr class="empty-row"><td colspan="4" style="text-align:center; padding:20px; color:var(--gray-400);">Sin registros.</td></tr>
                    <?php else: ?>
                        <?php foreach ($reducciones as $r): ?>
                            <tr>
                                <td style="font-weight:600; color:var(--gray-500);"><?= (int)$r['ordinal'] ?></td>
                                <td><?= e($r['etiqueta'] ?? '') ?></td>
                                <td><strong><?= e(number_format((float)($r['porcentaje_reduccion'] ?? 0), 0)) ?>%</strong></td>
                                <td style="font-size:12px; color:var(--gray-500);">
                                    <?php if ((int)($r['es_por_dependiente'] ?? 0)): ?>
                                        Por dependiente
                                    <?php elseif ($r['cuota_max_beneficiario_ut'] !== null): ?>
                                        Cuota ≤ <?= e(number_format((float)$r['cuota_max_beneficiario_ut'], 0)) ?> UT
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- ═══════════════════════════════════════════════════════════
     TAB 3: BIENES
     ═══════════════════════════════════════════════════════════ -->
<div id="tab-bienes" class="config-tab-pane">

    <div class="config-subtabs-nav">
        <button class="config-subtab-btn active" onclick="switchSubTab('sub-inmuebles', this, 'tab-bienes')">
            Inmuebles <span class="filter-count"><?= count($tiposBienInmueble) ?></span>
        </button>
        <button class="config-subtab-btn" onclick="switchSubTab('sub-cat-mueble', this, 'tab-bienes')">
            Categorías Mueble <span class="filter-count"><?= count($categoriasBienMueble) ?></span>
        </button>
        <button class="config-subtab-btn" onclick="switchSubTab('sub-tipos-mueble', this, 'tab-bienes')">
            Tipos Mueble <span class="filter-count"><?= count($tiposBienMueble) ?></span>
        </button>
        <button class="config-subtab-btn" onclick="switchSubTab('sub-semovientes', this, 'tab-bienes')">
            Semovientes <span class="filter-count"><?= count($tiposSemoviente) ?></span>
        </button>
    </div>

    <!-- Sub: Inmuebles -->
    <div id="sub-inmuebles" class="config-subpane active">
        <div style="margin-bottom:12px;display:flex;align-items:center;gap:10px;flex-wrap:wrap;"><button class="btn btn-primary" onclick="abrirCrearSimple('sim_cat_tipos_bien_inmueble','Tipo Inmueble')" style="font-size:13px;padding:8px 14px;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Agregar</button><select class="per-page-select" onchange="filterEstadoSimple(this)" style="min-width:100px;"><option value="">Todos</option><option value="1">Activos</option><option value="0">Inactivos</option></select></div>
        <div class="table-container">
            <table class="data-table">
                <thead><tr><th style="width:50px">ID</th><th>Nombre</th><th style="width:90px">Estado</th><th style="width:90px">Acciones</th></tr></thead>
                <tbody>
                    <?php if (empty($tiposBienInmueble)): ?>
                        <tr class="empty-row"><td colspan="4" style="text-align:center; padding:20px; color:var(--gray-400);">Sin registros.</td></tr>
                    <?php else: ?>
                        <?php foreach ($tiposBienInmueble as $item): ?>
                            <tr>
                                <td style="color:var(--gray-400); font-size:12px;"><?= (int)$item['id'] ?></td>
                                <td><?= e($item['nombre'] ?? '') ?></td>
                                <td><?= (int)($item['activo'] ?? 1) ? '<span class="status-badge status-published">Activo</span>' : '<span class="status-badge status-draft">Inactivo</span>' ?></td>
                                <td><div class="row-actions"><button class="row-action-btn" title="Editar" onclick="abrirEditarSimple('sim_cat_tipos_bien_inmueble','Tipo Inmueble',<?= (int)$item['id'] ?>,'<?= e(addslashes($item['nombre'])) ?>')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button><button class="row-action-btn" title="<?= (int)($item['activo'] ?? 1) ? 'Desactivar' : 'Activar' ?>" onclick="toggleActivo('sim_cat_tipos_bien_inmueble',<?= (int)$item['id'] ?>)" style="color:<?= (int)($item['activo'] ?? 1) ? 'var(--amber-500)' : 'var(--green-500)' ?>;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18.36 6.64A9 9 0 1 1 5.64 6.64"/><line x1="12" y1="2" x2="12" y2="12"/></svg></button></div></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Sub: Categorías Mueble -->
    <div id="sub-cat-mueble" class="config-subpane">
        <div style="margin-bottom:12px;display:flex;align-items:center;gap:10px;flex-wrap:wrap;"><button class="btn btn-primary" onclick="abrirCrearSimple('sim_cat_categorias_bien_mueble','Categoría Mueble')" style="font-size:13px;padding:8px 14px;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Agregar</button><select class="per-page-select" onchange="filterEstadoSimple(this)" style="min-width:100px;"><option value="">Todos</option><option value="1">Activos</option><option value="0">Inactivos</option></select></div>
        <div class="table-container">
            <table class="data-table">
                <thead><tr><th style="width:50px">ID</th><th>Nombre</th><th style="width:90px">Estado</th><th style="width:90px">Acciones</th></tr></thead>
                <tbody>
                    <?php if (empty($categoriasBienMueble)): ?>
                        <tr class="empty-row"><td colspan="4" style="text-align:center; padding:20px; color:var(--gray-400);">Sin registros.</td></tr>
                    <?php else: ?>
                        <?php foreach ($categoriasBienMueble as $item): ?>
                            <tr>
                                <td style="color:var(--gray-400); font-size:12px;"><?= (int)$item['id'] ?></td>
                                <td><?= e($item['nombre'] ?? '') ?></td>
                                <td><?= (int)($item['activo'] ?? 1) ? '<span class="status-badge status-published">Activo</span>' : '<span class="status-badge status-draft">Inactivo</span>' ?></td>
                                <td><div class="row-actions"><button class="row-action-btn" title="Editar" onclick="abrirEditarSimple('sim_cat_categorias_bien_mueble','Categoría Mueble',<?= (int)$item['id'] ?>,'<?= e(addslashes($item['nombre'])) ?>')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button><button class="row-action-btn" title="<?= (int)($item['activo'] ?? 1) ? 'Desactivar' : 'Activar' ?>" onclick="toggleActivo('sim_cat_categorias_bien_mueble',<?= (int)$item['id'] ?>)" style="color:<?= (int)($item['activo'] ?? 1) ? 'var(--amber-500)' : 'var(--green-500)' ?>;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18.36 6.64A9 9 0 1 1 5.64 6.64"/><line x1="12" y1="2" x2="12" y2="12"/></svg></button></div></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Sub: Tipos Mueble -->
    <div id="sub-tipos-mueble" class="config-subpane">
        <div style="margin-bottom:12px;display:flex;align-items:center;gap:10px;flex-wrap:wrap;"><button class="btn btn-primary" onclick="abrirCrearTipoMueble()" style="font-size:13px;padding:8px 14px;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Agregar</button><select class="per-page-select" onchange="filterEstadoSimple(this)" style="min-width:100px;"><option value="">Todos</option><option value="1">Activos</option><option value="0">Inactivos</option></select></div>
        <div class="table-container">
            <table class="data-table">
                <thead><tr><th style="width:50px">ID</th><th>Nombre</th><th>Categoría</th><th style="width:90px">Estado</th><th style="width:90px">Acciones</th></tr></thead>
                <tbody>
                    <?php if (empty($tiposBienMueble)): ?>
                        <tr class="empty-row"><td colspan="5" style="text-align:center; padding:20px; color:var(--gray-400);">Sin registros.</td></tr>
                    <?php else: ?>
                        <?php foreach ($tiposBienMueble as $tm): ?>
                            <tr>
                                <td style="color:var(--gray-400); font-size:12px;"><?= (int)$tm['id'] ?></td>
                                <td><?= e($tm['nombre'] ?? '') ?></td>
                                <td><span style="font-size:12px; color:var(--gray-500);"><?= e($tm['categoria'] ?? '—') ?></span></td>
                                <td><?= (int)($tm['activo'] ?? 1) ? '<span class="status-badge status-published">Activo</span>' : '<span class="status-badge status-draft">Inactivo</span>' ?></td>
                                <td><div class="row-actions"><button class="row-action-btn" title="Editar" onclick="abrirEditarTipoMueble(<?= (int)$tm['id'] ?>,'<?= e(addslashes($tm['nombre'])) ?>',<?= (int)($tm['categoria_bien_mueble_id'] ?? 0) ?>)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button><button class="row-action-btn" title="<?= (int)($tm['activo'] ?? 1) ? 'Desactivar' : 'Activar' ?>" onclick="toggleActivo('sim_cat_tipos_bien_mueble',<?= (int)$tm['id'] ?>)" style="color:<?= (int)($tm['activo'] ?? 1) ? 'var(--amber-500)' : 'var(--green-500)' ?>;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18.36 6.64A9 9 0 1 1 5.64 6.64"/><line x1="12" y1="2" x2="12" y2="12"/></svg></button></div></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Sub: Semovientes -->
    <div id="sub-semovientes" class="config-subpane">
        <div style="margin-bottom:12px;display:flex;align-items:center;gap:10px;flex-wrap:wrap;"><button class="btn btn-primary" onclick="abrirCrearSimple('sim_cat_tipos_semoviente','Tipo Semoviente')" style="font-size:13px;padding:8px 14px;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Agregar</button><select class="per-page-select" onchange="filterEstadoSimple(this)" style="min-width:100px;"><option value="">Todos</option><option value="1">Activos</option><option value="0">Inactivos</option></select></div>
        <div class="table-container">
            <table class="data-table">
                <thead><tr><th style="width:50px">ID</th><th>Nombre</th><th style="width:90px">Estado</th><th style="width:90px">Acciones</th></tr></thead>
                <tbody>
                    <?php if (empty($tiposSemoviente)): ?>
                        <tr class="empty-row"><td colspan="4" style="text-align:center; padding:20px; color:var(--gray-400);">Sin registros.</td></tr>
                    <?php else: ?>
                        <?php foreach ($tiposSemoviente as $item): ?>
                            <tr>
                                <td style="color:var(--gray-400); font-size:12px;"><?= (int)$item['id'] ?></td>
                                <td><?= e($item['nombre'] ?? '') ?></td>
                                <td><?= (int)($item['activo'] ?? 1) ? '<span class="status-badge status-published">Activo</span>' : '<span class="status-badge status-draft">Inactivo</span>' ?></td>
                                <td><div class="row-actions"><button class="row-action-btn" title="Editar" onclick="abrirEditarSimple('sim_cat_tipos_semoviente','Tipo Semoviente',<?= (int)$item['id'] ?>,'<?= e(addslashes($item['nombre'])) ?>')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button><button class="row-action-btn" title="<?= (int)($item['activo'] ?? 1) ? 'Desactivar' : 'Activar' ?>" onclick="toggleActivo('sim_cat_tipos_semoviente',<?= (int)$item['id'] ?>)" style="color:<?= (int)($item['activo'] ?? 1) ? 'var(--amber-500)' : 'var(--green-500)' ?>;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18.36 6.64A9 9 0 1 1 5.64 6.64"/><line x1="12" y1="2" x2="12" y2="12"/></svg></button></div></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- ═══════════════════════════════════════════════════════════
     TAB 4: PARENTESCOS
     ═══════════════════════════════════════════════════════════ -->
<div id="tab-parentescos" class="config-tab-pane">
    <div class="toolbar">
        <div class="toolbar-left">
            <div class="search-box">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                <input type="text" data-search-for="tbl-parentescos" placeholder="Buscar parentesco...">
            </div>
            <button class="btn btn-primary" onclick="abrirCrearParentesco()" style="white-space:nowrap;font-size:13px;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Agregar</button>
        </div>
        <div class="toolbar-right">
            <select class="per-page-select" data-status-for="tbl-parentescos" onchange="filterEstadoDataTable(this)" style="min-width:100px;"><option value="">Todos</option><option value="1">Activos</option><option value="0">Inactivos</option></select>
            <label style="font-size:var(--text-xs); color:var(--gray-500); display:flex; align-items:center; gap:6px;">
                Mostrar <select data-perpage-for="tbl-parentescos" class="per-page-select"><option value="10">10</option><option value="19">19</option><option value="25">25</option></select> filas
            </label>
        </div>
    </div>

    <div class="table-container">
        <table class="data-table" id="tbl-parentescos">
            <thead>
                <tr>
                    <th style="width:50px">ID</th>
                    <th class="sortable" data-col="1">Clave</th>
                    <th class="sortable" data-col="2">Etiqueta</th>
                    <th class="sortable" data-col="3">Grupo Tarifa</th>
                    <th style="width:90px">Estado</th>
                    <th style="width:90px">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($parentescos)): ?>
                    <tr class="empty-row"><td colspan="5" style="text-align:center; padding:30px; color:var(--gray-400);">Sin registros.</td></tr>
                <?php else: ?>
                    <?php foreach ($parentescos as $p): ?>
                        <tr data-search="<?= e(mb_strtolower(($p['clave'] ?? '') . ' ' . ($p['etiqueta'] ?? '') . ' ' . ($p['grupo_tarifa'] ?? ''))) ?>" data-activo="<?= (int)($p['activo'] ?? 1) ?>">
                            <td style="color:var(--gray-400); font-size:12px;"><?= (int)$p['id'] ?></td>
                            <td><code style="font-size:12px; background:var(--gray-100); padding:2px 6px; border-radius:4px;"><?= e($p['clave'] ?? '') ?></code></td>
                            <td><?= e($p['etiqueta'] ?? '') ?></td>
                            <td>
                                <?php if (!empty($p['grupo_tarifa'])): ?>
                                    <span style="font-size:12px; color:var(--gray-500);"><?= e($p['grupo_tarifa']) ?></span>
                                <?php else: ?>
                                    <span style="color:var(--gray-300);">—</span>
                                <?php endif; ?>
                            </td>
                            <td><?= (int)($p['activo'] ?? 1) ? '<span class="status-badge status-published">Activo</span>' : '<span class="status-badge status-draft">Inactivo</span>' ?></td>
                            <td><div class="row-actions"><button class="row-action-btn" title="Editar" onclick="abrirEditarParentesco(<?= (int)$p['id'] ?>,'<?= e(addslashes($p['clave'])) ?>','<?= e(addslashes($p['etiqueta'])) ?>',<?= (int)($p['grupo_tarifa_id'] ?? 0) ?>)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button><button class="row-action-btn" title="<?= (int)($p['activo'] ?? 1) ? 'Desactivar' : 'Activar' ?>" onclick="toggleActivo('sim_cat_parentescos',<?= (int)$p['id'] ?>)" style="color:<?= (int)($p['activo'] ?? 1) ? 'var(--amber-500)' : 'var(--green-500)' ?>;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18.36 6.64A9 9 0 1 1 5.64 6.64"/><line x1="12" y1="2" x2="12" y2="12"/></svg></button></div></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        <div class="table-footer" data-footer-for="tbl-parentescos">
            <div class="table-footer-info"></div>
            <div class="pagination"></div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════
     TAB 5: PASIVOS Y HERENCIAS
     ═══════════════════════════════════════════════════════════ -->
<div id="tab-pasivos" class="config-tab-pane">

    <div class="config-subtabs-nav">
        <button class="config-subtab-btn active" onclick="switchSubTab('sub-deuda', this, 'tab-pasivos')">
            Tipos de Deuda <span class="filter-count"><?= count($tiposPasivoDeuda) ?></span>
        </button>
        <button class="config-subtab-btn" onclick="switchSubTab('sub-gasto', this, 'tab-pasivos')">
            Gastos Funerales <span class="filter-count"><?= count($tiposPasivoGasto) ?></span>
        </button>
        <button class="config-subtab-btn" onclick="switchSubTab('sub-herencias', this, 'tab-pasivos')">
            Tipos de Herencia <span class="filter-count"><?= count($tipoHerencias) ?></span>
        </button>
    </div>

    <!-- Sub: Deuda -->
    <div id="sub-deuda" class="config-subpane active">
        <div style="margin-bottom:12px;display:flex;align-items:center;gap:10px;flex-wrap:wrap;"><button class="btn btn-primary" onclick="abrirCrearSimple('sim_cat_tipos_pasivo_deuda','Tipo de Deuda')" style="font-size:13px;padding:8px 14px;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Agregar</button><select class="per-page-select" onchange="filterEstadoSimple(this)" style="min-width:100px;"><option value="">Todos</option><option value="1">Activos</option><option value="0">Inactivos</option></select></div>
        <div class="table-container">
            <table class="data-table">
                <thead><tr><th style="width:50px">ID</th><th>Nombre</th><th style="width:90px">Estado</th><th style="width:90px">Acciones</th></tr></thead>
                <tbody>
                    <?php if (empty($tiposPasivoDeuda)): ?>
                        <tr class="empty-row"><td colspan="4" style="text-align:center; padding:20px; color:var(--gray-400);">Sin registros.</td></tr>
                    <?php else: ?>
                        <?php foreach ($tiposPasivoDeuda as $item): ?>
                            <tr>
                                <td style="color:var(--gray-400); font-size:12px;"><?= (int)$item['id'] ?></td>
                                <td><?= e($item['nombre'] ?? '') ?></td>
                                <td><?= (int)($item['activo'] ?? 1) ? '<span class="status-badge status-published">Activo</span>' : '<span class="status-badge status-draft">Inactivo</span>' ?></td>
                                <td><div class="row-actions"><button class="row-action-btn" title="Editar" onclick="abrirEditarSimple('sim_cat_tipos_pasivo_deuda','Tipo de Deuda',<?= (int)$item['id'] ?>,'<?= e(addslashes($item['nombre'])) ?>')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button><button class="row-action-btn" title="<?= (int)($item['activo'] ?? 1) ? 'Desactivar' : 'Activar' ?>" onclick="toggleActivo('sim_cat_tipos_pasivo_deuda',<?= (int)$item['id'] ?>)" style="color:<?= (int)($item['activo'] ?? 1) ? 'var(--amber-500)' : 'var(--green-500)' ?>;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18.36 6.64A9 9 0 1 1 5.64 6.64"/><line x1="12" y1="2" x2="12" y2="12"/></svg></button></div></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Sub: Gastos Funerales -->
    <div id="sub-gasto" class="config-subpane">
        <div style="margin-bottom:12px;display:flex;align-items:center;gap:10px;flex-wrap:wrap;"><button class="btn btn-primary" onclick="abrirCrearSimple('sim_cat_tipos_pasivo_gasto','Gasto Funeral')" style="font-size:13px;padding:8px 14px;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Agregar</button><select class="per-page-select" onchange="filterEstadoSimple(this)" style="min-width:100px;"><option value="">Todos</option><option value="1">Activos</option><option value="0">Inactivos</option></select></div>
        <div class="table-container">
            <table class="data-table">
                <thead><tr><th style="width:50px">ID</th><th>Nombre</th><th style="width:90px">Estado</th><th style="width:90px">Acciones</th></tr></thead>
                <tbody>
                    <?php if (empty($tiposPasivoGasto)): ?>
                        <tr class="empty-row"><td colspan="4" style="text-align:center; padding:20px; color:var(--gray-400);">Sin registros.</td></tr>
                    <?php else: ?>
                        <?php foreach ($tiposPasivoGasto as $item): ?>
                            <tr>
                                <td style="color:var(--gray-400); font-size:12px;"><?= (int)$item['id'] ?></td>
                                <td><?= e($item['nombre'] ?? '') ?></td>
                                <td><?= (int)($item['activo'] ?? 1) ? '<span class="status-badge status-published">Activo</span>' : '<span class="status-badge status-draft">Inactivo</span>' ?></td>
                                <td><div class="row-actions"><button class="row-action-btn" title="Editar" onclick="abrirEditarSimple('sim_cat_tipos_pasivo_gasto','Gasto Funeral',<?= (int)$item['id'] ?>,'<?= e(addslashes($item['nombre'])) ?>')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button><button class="row-action-btn" title="<?= (int)($item['activo'] ?? 1) ? 'Desactivar' : 'Activar' ?>" onclick="toggleActivo('sim_cat_tipos_pasivo_gasto',<?= (int)$item['id'] ?>)" style="color:<?= (int)($item['activo'] ?? 1) ? 'var(--amber-500)' : 'var(--green-500)' ?>;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18.36 6.64A9 9 0 1 1 5.64 6.64"/><line x1="12" y1="2" x2="12" y2="12"/></svg></button></div></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Sub: Herencias -->
    <div id="sub-herencias" class="config-subpane">
        <div style="margin-bottom:12px;display:flex;align-items:center;gap:10px;flex-wrap:wrap;"><button class="btn btn-primary" onclick="abrirCrearHerencia()" style="font-size:13px;padding:8px 14px;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Agregar</button><select class="per-page-select" onchange="filterEstadoSimple(this)" style="min-width:100px;"><option value="">Todos</option><option value="1">Activos</option><option value="0">Inactivos</option></select></div>
        <div class="table-container">
            <table class="data-table">
                <thead><tr><th style="width:50px">ID</th><th>Nombre</th><th>Descripción</th><th style="width:90px">Estado</th><th style="width:90px">Acciones</th></tr></thead>
                <tbody>
                    <?php if (empty($tipoHerencias)): ?>
                        <tr class="empty-row"><td colspan="5" style="text-align:center; padding:20px; color:var(--gray-400);">Sin registros.</td></tr>
                    <?php else: ?>
                        <?php foreach ($tipoHerencias as $th): ?>
                            <tr>
                                <td style="color:var(--gray-400); font-size:12px;"><?= (int)$th['id'] ?></td>
                                <td><strong><?= e($th['nombre'] ?? '') ?></strong></td>
                                <td style="font-size:13px; color:var(--gray-500); max-width:400px;"><?= e($th['descripcion'] ?? '—') ?></td>
                                <td><?= (int)($th['activo'] ?? 1) ? '<span class="status-badge status-published">Activo</span>' : '<span class="status-badge status-draft">Inactivo</span>' ?></td>
                                <td><div class="row-actions"><button class="row-action-btn" title="Editar" onclick="abrirEditarHerencia(<?= (int)$th['id'] ?>,'<?= e(addslashes($th['nombre'])) ?>','<?= e(addslashes($th['descripcion'] ?? '')) ?>')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button><button class="row-action-btn" title="<?= (int)($th['activo'] ?? 1) ? 'Desactivar' : 'Activar' ?>" onclick="toggleActivo('sim_cat_tipoherencias',<?= (int)$th['id'] ?>)" style="color:<?= (int)($th['activo'] ?? 1) ? 'var(--amber-500)' : 'var(--green-500)' ?>;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18.36 6.64A9 9 0 1 1 5.64 6.64"/><line x1="12" y1="2" x2="12" y2="12"/></svg></button></div></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- ═══════════════════════════════════════════════════════════
     JAVASCRIPT
     ═══════════════════════════════════════════════════════════ -->
<script>
// ── Tab Switching (main tabs) ──
function switchTab(tabId, btn) {
    document.querySelectorAll('.config-tab-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('.config-tab-pane').forEach(p => p.classList.remove('active'));
    document.getElementById(tabId).classList.add('active');
    // Persist in hash: main tab only (subtab resets to first)
    location.hash = tabId;
}

// ── Sub-Tab Switching (scoped to parent tab) ──
function switchSubTab(paneId, btn, parentId) {
    const parent = document.getElementById(parentId);
    if (!parent) return;
    parent.querySelectorAll('.config-subtab-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    parent.querySelectorAll('.config-subpane').forEach(p => p.classList.remove('active'));
    document.getElementById(paneId).classList.add('active');
    // Persist in hash: main/sub
    location.hash = parentId + '/' + paneId;
}

// ── Restore tab from URL hash on load ──
(function restoreTabFromHash() {
    const hash = location.hash.replace('#', '');
    if (!hash) return;

    const parts = hash.split('/');
    const mainTabId = parts[0];
    const subTabId = parts[1] || null;

    // Activate main tab
    const mainPane = document.getElementById(mainTabId);
    if (!mainPane || !mainPane.classList.contains('config-tab-pane')) return;

    document.querySelectorAll('.config-tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.config-tab-pane').forEach(p => p.classList.remove('active'));
    mainPane.classList.add('active');

    // Find and activate the matching nav button
    document.querySelectorAll('.config-tab-btn').forEach(b => {
        if (b.getAttribute('onclick')?.includes("'" + mainTabId + "'")) b.classList.add('active');
    });

    // Activate subtab if specified
    if (subTabId) {
        const subPane = document.getElementById(subTabId);
        if (subPane && subPane.classList.contains('config-subpane')) {
            mainPane.querySelectorAll('.config-subtab-btn').forEach(b => b.classList.remove('active'));
            mainPane.querySelectorAll('.config-subpane').forEach(p => p.classList.remove('active'));
            subPane.classList.add('active');

            mainPane.querySelectorAll('.config-subtab-btn').forEach(b => {
                if (b.getAttribute('onclick')?.includes("'" + subTabId + "'")) b.classList.add('active');
            });
        }
    }
})();

// ── Reusable DataTable Engine ──
(function() {
    // Tables with pagination: those that have an id and a matching footer
    const tables = document.querySelectorAll('table.data-table[id]');

    tables.forEach(table => {
        const tblId = table.id;
        const tbody = table.querySelector('tbody');
        if (!tbody) return;

        const rows = Array.from(tbody.querySelectorAll('tr[data-search]'));
        if (rows.length === 0) return;

        const searchInput = document.querySelector(`[data-search-for="${tblId}"]`);
        const perPageSel = document.querySelector(`[data-perpage-for="${tblId}"]`);
        const statusSel = document.querySelector(`[data-status-for="${tblId}"]`);
        const footer = document.querySelector(`[data-footer-for="${tblId}"]`);
        const footerInfo = footer?.querySelector('.table-footer-info');
        const paginationEl = footer?.querySelector('.pagination');

        let searchTerm = '';
        let statusFilter = '';
        let currentPage = 1;
        let sortCol = null, sortDir = 1;

        function getPerPage() { return parseInt(perPageSel?.value || '15', 10); }

        function getVisible() {
            return rows.filter(r => {
                if (searchTerm && !(r.dataset.search || '').includes(searchTerm)) return false;
                if (statusFilter && (r.dataset.activo ?? '') !== statusFilter) return false;
                return true;
            });
        }

        function sortRows(arr) {
            if (sortCol === null) return arr;
            return arr.slice().sort((a, b) => {
                const va = (a.children[sortCol]?.textContent || '').trim().toLowerCase();
                const vb = (b.children[sortCol]?.textContent || '').trim().toLowerCase();
                const na = parseFloat(va.replace(/[^\d.-]/g, ''));
                const nb = parseFloat(vb.replace(/[^\d.-]/g, ''));
                if (!isNaN(na) && !isNaN(nb)) return sortDir * (na - nb);
                return sortDir * va.localeCompare(vb);
            });
        }

        function render() {
            const PER_PAGE = getPerPage();
            const visible = sortRows(getVisible());
            const totalPages = Math.max(1, Math.ceil(visible.length / PER_PAGE));
            if (currentPage > totalPages) currentPage = totalPages;
            const start = (currentPage - 1) * PER_PAGE;
            const pageRows = visible.slice(start, start + PER_PAGE);

            rows.forEach(r => r.style.display = 'none');
            pageRows.forEach(r => r.style.display = '');

            if (footerInfo) {
                const from = visible.length > 0 ? start + 1 : 0;
                const to = Math.min(start + PER_PAGE, visible.length);
                footerInfo.innerHTML = `Mostrando <strong>${from}</strong> a <strong>${to}</strong> de <strong>${visible.length}</strong> registros`;
            }

            if (paginationEl) {
                paginationEl.innerHTML = '';
                if (totalPages > 1) {
                    const prev = document.createElement('button');
                    prev.innerHTML = '‹'; prev.disabled = currentPage === 1;
                    prev.addEventListener('click', () => { currentPage--; render(); });
                    paginationEl.appendChild(prev);

                    for (let p = 1; p <= totalPages; p++) {
                        const b = document.createElement('button');
                        b.textContent = p;
                        if (p === currentPage) b.classList.add('active');
                        b.addEventListener('click', () => { currentPage = p; render(); });
                        paginationEl.appendChild(b);
                    }

                    const next = document.createElement('button');
                    next.innerHTML = '›'; next.disabled = currentPage === totalPages;
                    next.addEventListener('click', () => { currentPage++; render(); });
                    paginationEl.appendChild(next);
                }
            }
        }

        // Events
        searchInput?.addEventListener('input', (e) => {
            searchTerm = e.target.value.toLowerCase().trim();
            currentPage = 1;
            render();
        });

        perPageSel?.addEventListener('change', () => { currentPage = 1; render(); });

        statusSel?.addEventListener('change', () => { statusFilter = statusSel.value; currentPage = 1; render(); });

        // Make render accessible for external triggers
        table._dtRender = render;

        // Sortable headers
        table.querySelectorAll('th.sortable[data-col]').forEach(th => {
            th.style.cursor = 'pointer';
            th.addEventListener('click', () => {
                const col = parseInt(th.dataset.col, 10);
                if (sortCol === col) sortDir *= -1;
                else { sortCol = col; sortDir = 1; }
                table.querySelectorAll('th.sortable').forEach(h => h.classList.remove('sort-asc', 'sort-desc'));
                th.classList.add(sortDir === 1 ? 'sort-asc' : 'sort-desc');
                render();
            });
        });

        render();
    });
})();
// ── DataTable status filter (handled by integrated event listener, this is for onchange compat) ──
function filterEstadoDataTable(sel) { /* auto-handled by statusSel event listener */ }

// ── Simple table filter (tables without DataTable engine) ──
function filterEstadoSimple(sel) {
    const container = sel.closest('.config-subpane');
    if (!container) return;
    const val = sel.value; // '', '1', '0'
    container.querySelectorAll('tbody tr:not(.empty-row)').forEach(row => {
        if (!val) { row.style.display = ''; return; }
        const badge = row.querySelector('.status-badge');
        const isActivo = badge?.classList.contains('status-published') ? '1' : '0';
        row.style.display = isActivo === val ? '' : 'none';
    });
}

// ═══════════════════════════════════════════════════════════
//  CRUD Logic
// ═══════════════════════════════════════════════════════════
const CSRF_TOKEN = '<?= \App\Core\Csrf::getToken() ?>';
const BASE_URL   = '<?= base_url() ?>';

// ── Modal Simple (Tier 1: nombre only) ──
function abrirCrearSimple(tabla, titulo) {
    document.getElementById('simple-id').value = '';
    document.getElementById('simple-tabla').value = tabla;
    document.getElementById('simple-nombre').value = '';
    document.getElementById('modal-simple-title').textContent = 'Registrar ' + titulo;
    const btn = document.getElementById('btn-guardar-simple');
    btn.textContent = 'Guardar'; btn.disabled = false;
    window.modalManager.clearError('modal-simple');
    window.modalManager.open('modal-simple');
}
function abrirEditarSimple(tabla, titulo, id, nombre) {
    document.getElementById('simple-id').value = id;
    document.getElementById('simple-tabla').value = tabla;
    document.getElementById('simple-nombre').value = nombre;
    document.getElementById('modal-simple-title').textContent = 'Editar ' + titulo;
    const btn = document.getElementById('btn-guardar-simple');
    btn.textContent = 'Actualizar'; btn.disabled = false;
    window.modalManager.clearError('modal-simple');
    window.modalManager.open('modal-simple');
}
async function guardarSimple() {
    const btn = document.getElementById('btn-guardar-simple');
    window.modalManager.setButtonLoading(btn);
    try {
        const res = await fetch(BASE_URL + '/admin/configuracion/catalogos/guardar-simple', {
            method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'},
            body: new URLSearchParams({ csrf_token:CSRF_TOKEN, tabla:document.getElementById('simple-tabla').value, id:document.getElementById('simple-id').value, nombre:document.getElementById('simple-nombre').value.trim() })
        });
        const data = await res.json();
        if (data.success) { window.modalManager.close('modal-simple'); showToast(data.message,'success'); location.reload(); }
        else window.modalManager.showError('modal-simple', data.message);
    } catch(e) { console.error(e); window.modalManager.showError('modal-simple','Error de conexión.'); }
    finally { window.modalManager.resetButtonLoading(btn); }
}

// ── Toggle Activo ──
async function toggleActivo(tabla, id) {
    try {
        const res = await fetch(BASE_URL + '/admin/configuracion/catalogos/toggle-activo', {
            method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'},
            body: new URLSearchParams({ csrf_token:CSRF_TOKEN, tabla, id })
        });
        const data = await res.json();
        if (data.success) { showToast(data.message,'success'); location.reload(); }
        else showToast(data.message || 'Error','error');
    } catch(e) { console.error(e); showToast('Error de conexión.','error'); }
}

// ── Modal UT ──
function abrirCrearUT() {
    document.getElementById('ut-id').value = '';
    document.getElementById('ut-anio').value = new Date().getFullYear();
    document.getElementById('ut-valor').value = '';
    document.getElementById('ut-fecha').value = '';
    document.getElementById('modal-ut-title').textContent = 'Registrar Unidad Tributaria';
    const btn = document.getElementById('btn-guardar-ut');
    btn.textContent = 'Guardar'; btn.disabled = false;
    window.modalManager.clearError('modal-ut');
    window.modalManager.open('modal-ut');
}
function abrirEditarUT(id, anio, valor, fecha) {
    document.getElementById('ut-id').value = id;
    document.getElementById('ut-anio').value = anio;
    document.getElementById('ut-valor').value = valor;
    document.getElementById('ut-fecha').value = fecha;
    document.getElementById('modal-ut-title').textContent = 'Editar Unidad Tributaria';
    const btn = document.getElementById('btn-guardar-ut');
    btn.textContent = 'Actualizar'; btn.disabled = false;
    window.modalManager.clearError('modal-ut');
    window.modalManager.open('modal-ut');
}
async function guardarUT() {
    const btn = document.getElementById('btn-guardar-ut');
    window.modalManager.setButtonLoading(btn);
    try {
        const res = await fetch(BASE_URL + '/admin/configuracion/catalogos/guardar-ut', {
            method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'},
            body: new URLSearchParams({ csrf_token:CSRF_TOKEN, id:document.getElementById('ut-id').value, anio:document.getElementById('ut-anio').value, valor:document.getElementById('ut-valor').value, fecha_gaceta:document.getElementById('ut-fecha').value })
        });
        const data = await res.json();
        if (data.success) { window.modalManager.close('modal-ut'); showToast(data.message,'success'); location.reload(); }
        else window.modalManager.showError('modal-ut', data.message);
    } catch(e) { console.error(e); window.modalManager.showError('modal-ut','Error de conexión.'); }
    finally { window.modalManager.resetButtonLoading(btn); }
}

// ── Modal Parentesco ──
function abrirCrearParentesco() {
    document.getElementById('par-id').value = '';
    document.getElementById('par-clave').value = '';
    document.getElementById('par-etiqueta').value = '';
    document.getElementById('par-grupo').value = '';
    document.getElementById('modal-par-title').textContent = 'Registrar Parentesco';
    const btn = document.getElementById('btn-guardar-par');
    btn.textContent = 'Guardar'; btn.disabled = false;
    window.modalManager.clearError('modal-parentesco');
    window.modalManager.open('modal-parentesco');
}
function abrirEditarParentesco(id, clave, etiqueta, grupoId) {
    document.getElementById('par-id').value = id;
    document.getElementById('par-clave').value = clave;
    document.getElementById('par-etiqueta').value = etiqueta;
    document.getElementById('par-grupo').value = grupoId || '';
    document.getElementById('modal-par-title').textContent = 'Editar Parentesco';
    const btn = document.getElementById('btn-guardar-par');
    btn.textContent = 'Actualizar'; btn.disabled = false;
    window.modalManager.clearError('modal-parentesco');
    window.modalManager.open('modal-parentesco');
}
async function guardarParentesco() {
    const btn = document.getElementById('btn-guardar-par');
    window.modalManager.setButtonLoading(btn);
    try {
        const res = await fetch(BASE_URL + '/admin/configuracion/catalogos/guardar-parentesco', {
            method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'},
            body: new URLSearchParams({ csrf_token:CSRF_TOKEN, id:document.getElementById('par-id').value, clave:document.getElementById('par-clave').value.trim(), etiqueta:document.getElementById('par-etiqueta').value.trim(), grupo_tarifa_id:document.getElementById('par-grupo').value })
        });
        const data = await res.json();
        if (data.success) { window.modalManager.close('modal-parentesco'); showToast(data.message,'success'); location.reload(); }
        else window.modalManager.showError('modal-parentesco', data.message);
    } catch(e) { console.error(e); window.modalManager.showError('modal-parentesco','Error de conexión.'); }
    finally { window.modalManager.resetButtonLoading(btn); }
}

// ── Modal Tipo Herencia ──
function abrirCrearHerencia() {
    document.getElementById('her-id').value = '';
    document.getElementById('her-nombre').value = '';
    document.getElementById('her-descripcion').value = '';
    document.getElementById('modal-her-title').textContent = 'Registrar Tipo de Herencia';
    const btn = document.getElementById('btn-guardar-her');
    btn.textContent = 'Guardar'; btn.disabled = false;
    window.modalManager.clearError('modal-herencia');
    window.modalManager.open('modal-herencia');
}
function abrirEditarHerencia(id, nombre, descripcion) {
    document.getElementById('her-id').value = id;
    document.getElementById('her-nombre').value = nombre;
    document.getElementById('her-descripcion').value = descripcion;
    document.getElementById('modal-her-title').textContent = 'Editar Tipo de Herencia';
    const btn = document.getElementById('btn-guardar-her');
    btn.textContent = 'Actualizar'; btn.disabled = false;
    window.modalManager.clearError('modal-herencia');
    window.modalManager.open('modal-herencia');
}
async function guardarTipoHerencia() {
    const btn = document.getElementById('btn-guardar-her');
    window.modalManager.setButtonLoading(btn);
    try {
        const res = await fetch(BASE_URL + '/admin/configuracion/catalogos/guardar-herencia', {
            method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'},
            body: new URLSearchParams({ csrf_token:CSRF_TOKEN, id:document.getElementById('her-id').value, nombre:document.getElementById('her-nombre').value.trim(), descripcion:document.getElementById('her-descripcion').value.trim() })
        });
        const data = await res.json();
        if (data.success) { window.modalManager.close('modal-herencia'); showToast(data.message,'success'); location.reload(); }
        else window.modalManager.showError('modal-herencia', data.message);
    } catch(e) { console.error(e); window.modalManager.showError('modal-herencia','Error de conexión.'); }
    finally { window.modalManager.resetButtonLoading(btn); }
}

// ── Modal Tipo Bien Mueble ──
function abrirCrearTipoMueble() {
    document.getElementById('mue-id').value = '';
    document.getElementById('mue-nombre').value = '';
    document.getElementById('mue-categoria').value = '';
    document.getElementById('modal-mue-title').textContent = 'Registrar Tipo de Bien Mueble';
    const btn = document.getElementById('btn-guardar-mue');
    btn.textContent = 'Guardar'; btn.disabled = false;
    window.modalManager.clearError('modal-tipo-mueble');
    window.modalManager.open('modal-tipo-mueble');
}
function abrirEditarTipoMueble(id, nombre, catId) {
    document.getElementById('mue-id').value = id;
    document.getElementById('mue-nombre').value = nombre;
    document.getElementById('mue-categoria').value = catId;
    document.getElementById('modal-mue-title').textContent = 'Editar Tipo de Bien Mueble';
    const btn = document.getElementById('btn-guardar-mue');
    btn.textContent = 'Actualizar'; btn.disabled = false;
    window.modalManager.clearError('modal-tipo-mueble');
    window.modalManager.open('modal-tipo-mueble');
}
async function guardarTipoBienMueble() {
    const btn = document.getElementById('btn-guardar-mue');
    window.modalManager.setButtonLoading(btn);
    try {
        const res = await fetch(BASE_URL + '/admin/configuracion/catalogos/guardar-tipo-mueble', {
            method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'},
            body: new URLSearchParams({ csrf_token:CSRF_TOKEN, id:document.getElementById('mue-id').value, nombre:document.getElementById('mue-nombre').value.trim(), categoria_id:document.getElementById('mue-categoria').value })
        });
        const data = await res.json();
        if (data.success) { window.modalManager.close('modal-tipo-mueble'); showToast(data.message,'success'); location.reload(); }
        else window.modalManager.showError('modal-tipo-mueble', data.message);
    } catch(e) { console.error(e); window.modalManager.showError('modal-tipo-mueble','Error de conexión.'); }
    finally { window.modalManager.resetButtonLoading(btn); }
}
</script>

<!-- ═══════════════════════════════════════════════════════════
     MODALES
     ═══════════════════════════════════════════════════════════ -->

<!-- Modal Simple (Tier 1) -->
<dialog class="modal-base" id="modal-simple" data-no-backdrop-close>
    <div class="modal-base__container" style="max-width:480px;">
        <div class="modal-base__header">
            <h2 class="modal-base__title" id="modal-simple-title">Registrar</h2>
            <button class="modal-base__close" onclick="window.modalManager.close('modal-simple')">&times;</button>
        </div>
        <div class="modal-base__body">
            <input type="hidden" id="simple-id"><input type="hidden" id="simple-tabla">
            <div class="form-group"><label>Nombre <span class="required">*</span></label><input type="text" id="simple-nombre" maxlength="60" placeholder="Nombre del registro"></div>
        </div>
        <div class="modal-base__footer">
            <button class="modal-btn modal-btn-cancel" onclick="window.modalManager.close('modal-simple')">Cancelar</button>
            <button class="modal-btn modal-btn-primary" id="btn-guardar-simple" onclick="guardarSimple()">Guardar</button>
        </div>
    </div>
</dialog>

<!-- Modal UT -->
<dialog class="modal-base" id="modal-ut" data-no-backdrop-close>
    <div class="modal-base__container" style="max-width:520px;">
        <div class="modal-base__header">
            <h2 class="modal-base__title" id="modal-ut-title">Registrar Unidad Tributaria</h2>
            <button class="modal-base__close" onclick="window.modalManager.close('modal-ut')">&times;</button>
        </div>
        <div class="modal-base__body">
            <input type="hidden" id="ut-id">
            <div class="form-grid">
                <div class="form-group"><label>Año <span class="required">*</span></label><input type="number" id="ut-anio" min="1990" max="2100"></div>
                <div class="form-group"><label>Valor (Bs.) <span class="required">*</span></label><input type="number" id="ut-valor" step="0.01" min="0.01" placeholder="Ej: 9.00"></div>
                <div class="form-group form-full"><label>Fecha Gaceta <span class="required">*</span></label><input type="date" id="ut-fecha" max="<?= date('Y-m-d') ?>" onkeydown="return false"></div>
            </div>
        </div>
        <div class="modal-base__footer">
            <button class="modal-btn modal-btn-cancel" onclick="window.modalManager.close('modal-ut')">Cancelar</button>
            <button class="modal-btn modal-btn-primary" id="btn-guardar-ut" onclick="guardarUT()">Guardar</button>
        </div>
    </div>
</dialog>

<!-- Modal Parentesco -->
<dialog class="modal-base" id="modal-parentesco" data-no-backdrop-close>
    <div class="modal-base__container" style="max-width:520px;">
        <div class="modal-base__header">
            <h2 class="modal-base__title" id="modal-par-title">Registrar Parentesco</h2>
            <button class="modal-base__close" onclick="window.modalManager.close('modal-parentesco')">&times;</button>
        </div>
        <div class="modal-base__body">
            <input type="hidden" id="par-id">
            <div class="form-grid">
                <div class="form-group"><label>Clave <span class="required">*</span></label><input type="text" id="par-clave" maxlength="50" placeholder="Ej: hijo_a"></div>
                <div class="form-group"><label>Etiqueta <span class="required">*</span></label><input type="text" id="par-etiqueta" maxlength="60" placeholder="Ej: Hijo(a)"></div>
                <div class="form-group form-full"><label>Grupo de Tarifa</label>
                    <select id="par-grupo"><option value="">— Sin grupo —</option>
                    <?php foreach ($gruposTarifa as $g): ?><option value="<?= (int)$g['id'] ?>"><?= e($g['nombre']) ?></option><?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="modal-base__footer">
            <button class="modal-btn modal-btn-cancel" onclick="window.modalManager.close('modal-parentesco')">Cancelar</button>
            <button class="modal-btn modal-btn-primary" id="btn-guardar-par" onclick="guardarParentesco()">Guardar</button>
        </div>
    </div>
</dialog>

<!-- Modal Tipo Herencia -->
<dialog class="modal-base" id="modal-herencia" data-no-backdrop-close>
    <div class="modal-base__container" style="max-width:520px;">
        <div class="modal-base__header">
            <h2 class="modal-base__title" id="modal-her-title">Registrar Tipo de Herencia</h2>
            <button class="modal-base__close" onclick="window.modalManager.close('modal-herencia')">&times;</button>
        </div>
        <div class="modal-base__body">
            <input type="hidden" id="her-id">
            <div class="form-group"><label>Nombre <span class="required">*</span></label><input type="text" id="her-nombre" maxlength="50" placeholder="Nombre"></div>
            <div class="form-group"><label>Descripción</label><textarea id="her-descripcion" rows="3" placeholder="Descripción opcional..."></textarea></div>
        </div>
        <div class="modal-base__footer">
            <button class="modal-btn modal-btn-cancel" onclick="window.modalManager.close('modal-herencia')">Cancelar</button>
            <button class="modal-btn modal-btn-primary" id="btn-guardar-her" onclick="guardarTipoHerencia()">Guardar</button>
        </div>
    </div>
</dialog>

<!-- Modal Tipo Bien Mueble -->
<dialog class="modal-base" id="modal-tipo-mueble" data-no-backdrop-close>
    <div class="modal-base__container" style="max-width:520px;">
        <div class="modal-base__header">
            <h2 class="modal-base__title" id="modal-mue-title">Registrar Tipo de Bien Mueble</h2>
            <button class="modal-base__close" onclick="window.modalManager.close('modal-tipo-mueble')">&times;</button>
        </div>
        <div class="modal-base__body">
            <input type="hidden" id="mue-id">
            <div class="form-group"><label>Nombre <span class="required">*</span></label><input type="text" id="mue-nombre" maxlength="80" placeholder="Nombre"></div>
            <div class="form-group"><label>Categoría <span class="required">*</span></label>
                <select id="mue-categoria"><option value="">— Seleccione —</option>
                <?php foreach ($categoriasBienMueble as $c): ?><option value="<?= (int)$c['id'] ?>"><?= e($c['nombre']) ?></option><?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="modal-base__footer">
            <button class="modal-btn modal-btn-cancel" onclick="window.modalManager.close('modal-tipo-mueble')">Cancelar</button>
            <button class="modal-btn modal-btn-primary" id="btn-guardar-mue" onclick="guardarTipoBienMueble()">Guardar</button>
        </div>
    </div>
</dialog>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/logged_layout.php';
?>