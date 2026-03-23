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
        <p>Tablas maestras del proceso sucesoral SENIAT que alimentan los formularios del simulador (solo lectura).</p>
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
        </div>
        <div class="toolbar-right">
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
                        <tr data-search="<?= e(mb_strtolower($ut['anio'] . ' ' . $ut['valor'] . ' ' . $fechaFmt)) ?>">
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
        <div class="table-container">
            <table class="data-table">
                <thead><tr><th style="width:50px">ID</th><th>Nombre</th><th style="width:90px">Estado</th></tr></thead>
                <tbody>
                    <?php if (empty($tiposBienInmueble)): ?>
                        <tr class="empty-row"><td colspan="3" style="text-align:center; padding:20px; color:var(--gray-400);">Sin registros.</td></tr>
                    <?php else: ?>
                        <?php foreach ($tiposBienInmueble as $item): ?>
                            <tr>
                                <td style="color:var(--gray-400); font-size:12px;"><?= (int)$item['id'] ?></td>
                                <td><?= e($item['nombre'] ?? '') ?></td>
                                <td><?= (int)($item['activo'] ?? 1) ? '<span class="status-badge status-published">Activo</span>' : '<span class="status-badge status-draft">Inactivo</span>' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Sub: Categorías Mueble -->
    <div id="sub-cat-mueble" class="config-subpane">
        <div class="table-container">
            <table class="data-table">
                <thead><tr><th style="width:50px">ID</th><th>Nombre</th><th style="width:90px">Estado</th></tr></thead>
                <tbody>
                    <?php if (empty($categoriasBienMueble)): ?>
                        <tr class="empty-row"><td colspan="3" style="text-align:center; padding:20px; color:var(--gray-400);">Sin registros.</td></tr>
                    <?php else: ?>
                        <?php foreach ($categoriasBienMueble as $item): ?>
                            <tr>
                                <td style="color:var(--gray-400); font-size:12px;"><?= (int)$item['id'] ?></td>
                                <td><?= e($item['nombre'] ?? '') ?></td>
                                <td><?= (int)($item['activo'] ?? 1) ? '<span class="status-badge status-published">Activo</span>' : '<span class="status-badge status-draft">Inactivo</span>' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Sub: Tipos Mueble -->
    <div id="sub-tipos-mueble" class="config-subpane">
        <div class="table-container">
            <table class="data-table">
                <thead><tr><th style="width:50px">ID</th><th>Nombre</th><th>Categoría</th><th style="width:90px">Estado</th></tr></thead>
                <tbody>
                    <?php if (empty($tiposBienMueble)): ?>
                        <tr class="empty-row"><td colspan="4" style="text-align:center; padding:20px; color:var(--gray-400);">Sin registros.</td></tr>
                    <?php else: ?>
                        <?php foreach ($tiposBienMueble as $tm): ?>
                            <tr>
                                <td style="color:var(--gray-400); font-size:12px;"><?= (int)$tm['id'] ?></td>
                                <td><?= e($tm['nombre'] ?? '') ?></td>
                                <td><span style="font-size:12px; color:var(--gray-500);"><?= e($tm['categoria'] ?? '—') ?></span></td>
                                <td><?= (int)($tm['activo'] ?? 1) ? '<span class="status-badge status-published">Activo</span>' : '<span class="status-badge status-draft">Inactivo</span>' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Sub: Semovientes -->
    <div id="sub-semovientes" class="config-subpane">
        <div class="table-container">
            <table class="data-table">
                <thead><tr><th style="width:50px">ID</th><th>Nombre</th><th style="width:90px">Estado</th></tr></thead>
                <tbody>
                    <?php if (empty($tiposSemoviente)): ?>
                        <tr class="empty-row"><td colspan="3" style="text-align:center; padding:20px; color:var(--gray-400);">Sin registros.</td></tr>
                    <?php else: ?>
                        <?php foreach ($tiposSemoviente as $item): ?>
                            <tr>
                                <td style="color:var(--gray-400); font-size:12px;"><?= (int)$item['id'] ?></td>
                                <td><?= e($item['nombre'] ?? '') ?></td>
                                <td><?= (int)($item['activo'] ?? 1) ? '<span class="status-badge status-published">Activo</span>' : '<span class="status-badge status-draft">Inactivo</span>' ?></td>
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
        </div>
        <div class="toolbar-right">
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
                </tr>
            </thead>
            <tbody>
                <?php if (empty($parentescos)): ?>
                    <tr class="empty-row"><td colspan="5" style="text-align:center; padding:30px; color:var(--gray-400);">Sin registros.</td></tr>
                <?php else: ?>
                    <?php foreach ($parentescos as $p): ?>
                        <tr data-search="<?= e(mb_strtolower(($p['clave'] ?? '') . ' ' . ($p['etiqueta'] ?? '') . ' ' . ($p['grupo_tarifa'] ?? ''))) ?>">
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
        <div class="table-container">
            <table class="data-table">
                <thead><tr><th style="width:50px">ID</th><th>Nombre</th><th style="width:90px">Estado</th></tr></thead>
                <tbody>
                    <?php if (empty($tiposPasivoDeuda)): ?>
                        <tr class="empty-row"><td colspan="3" style="text-align:center; padding:20px; color:var(--gray-400);">Sin registros.</td></tr>
                    <?php else: ?>
                        <?php foreach ($tiposPasivoDeuda as $item): ?>
                            <tr>
                                <td style="color:var(--gray-400); font-size:12px;"><?= (int)$item['id'] ?></td>
                                <td><?= e($item['nombre'] ?? '') ?></td>
                                <td><?= (int)($item['activo'] ?? 1) ? '<span class="status-badge status-published">Activo</span>' : '<span class="status-badge status-draft">Inactivo</span>' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Sub: Gastos Funerales -->
    <div id="sub-gasto" class="config-subpane">
        <div class="table-container">
            <table class="data-table">
                <thead><tr><th style="width:50px">ID</th><th>Nombre</th><th style="width:90px">Estado</th></tr></thead>
                <tbody>
                    <?php if (empty($tiposPasivoGasto)): ?>
                        <tr class="empty-row"><td colspan="3" style="text-align:center; padding:20px; color:var(--gray-400);">Sin registros.</td></tr>
                    <?php else: ?>
                        <?php foreach ($tiposPasivoGasto as $item): ?>
                            <tr>
                                <td style="color:var(--gray-400); font-size:12px;"><?= (int)$item['id'] ?></td>
                                <td><?= e($item['nombre'] ?? '') ?></td>
                                <td><?= (int)($item['activo'] ?? 1) ? '<span class="status-badge status-published">Activo</span>' : '<span class="status-badge status-draft">Inactivo</span>' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Sub: Herencias -->
    <div id="sub-herencias" class="config-subpane">
        <div class="table-container">
            <table class="data-table">
                <thead><tr><th style="width:50px">ID</th><th>Nombre</th><th>Descripción</th><th style="width:90px">Estado</th></tr></thead>
                <tbody>
                    <?php if (empty($tipoHerencias)): ?>
                        <tr class="empty-row"><td colspan="4" style="text-align:center; padding:20px; color:var(--gray-400);">Sin registros.</td></tr>
                    <?php else: ?>
                        <?php foreach ($tipoHerencias as $th): ?>
                            <tr>
                                <td style="color:var(--gray-400); font-size:12px;"><?= (int)$th['id'] ?></td>
                                <td><strong><?= e($th['nombre'] ?? '') ?></strong></td>
                                <td style="font-size:13px; color:var(--gray-500); max-width:400px;"><?= e($th['descripcion'] ?? '—') ?></td>
                                <td><?= (int)($th['activo'] ?? 1) ? '<span class="status-badge status-published">Activo</span>' : '<span class="status-badge status-draft">Inactivo</span>' ?></td>
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
}

// ── Sub-Tab Switching (scoped to parent tab) ──
function switchSubTab(paneId, btn, parentId) {
    const parent = document.getElementById(parentId);
    if (!parent) return;
    parent.querySelectorAll('.config-subtab-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    parent.querySelectorAll('.config-subpane').forEach(p => p.classList.remove('active'));
    document.getElementById(paneId).classList.add('active');
}

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
        const footer = document.querySelector(`[data-footer-for="${tblId}"]`);
        const footerInfo = footer?.querySelector('.table-footer-info');
        const paginationEl = footer?.querySelector('.pagination');

        let searchTerm = '';
        let currentPage = 1;
        let sortCol = null, sortDir = 1;

        function getPerPage() { return parseInt(perPageSel?.value || '15', 10); }

        function getVisible() {
            return rows.filter(r => !searchTerm || (r.dataset.search || '').includes(searchTerm));
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
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/logged_layout.php';
?>