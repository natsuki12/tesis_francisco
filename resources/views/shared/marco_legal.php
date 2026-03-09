<?php
declare(strict_types=1);

// ARCHIVO: resources/views/shared/marco_legal.php
// Vista compartida de solo lectura para Profesor (role=2) y Estudiante (role=3).

// 1. Configuración de la Vista
$pageTitle = 'Marco Legal — Simulador SENIAT';
$activePage = 'marco-legal';

// 2. CSS específico
$extraCss = '<link rel="stylesheet" href="' . asset('css/shared/marco_legal.css') . '">';

// 3. Datos Placeholder (futuro: vendrán del controlador/modelo con base en sim_marco_legals)
$normas = [
    [
        'id' => 1,
        'titulo' => 'Ley de Impuesto sobre Sucesiones, Donaciones y demás Ramos Conexos',
        'tipo' => 'Ley',
        'descripcion' => 'Establece el régimen jurídico aplicable al impuesto sobre sucesiones y donaciones en Venezuela. Define los sujetos pasivos, la base imponible, las exenciones, exoneraciones y las tarifas progresivas que se aplican al patrimonio transmitido por causa de muerte o por actos entre vivos a título gratuito. Es la norma fundamental que regula la obligación tributaria sucesoral.',
        'url' => 'https://www.seniat.gob.ve/ley-sucesiones',
        'estado' => 'Vigente',
        'orden' => 1,
        'fecha_publicacion' => '1999-09-22',
        'numero_gaceta' => '5.391 Extraordinario',
    ],
    [
        'id' => 2,
        'titulo' => 'Código Orgánico Tributario (COT)',
        'tipo' => 'Codigo',
        'descripcion' => 'Marco jurídico general que regula las relaciones entre el Estado y los contribuyentes en materia tributaria. Establece los principios, procedimientos y sanciones aplicables. Es de aplicación supletoria a la Ley de Sucesiones en todo lo no previsto expresamente por esta.',
        'url' => 'https://www.seniat.gob.ve/codigo-organico-tributario',
        'estado' => 'Vigente',
        'orden' => 2,
        'fecha_publicacion' => '2020-01-29',
        'numero_gaceta' => '6.507 Extraordinario',
    ],
    [
        'id' => 3,
        'titulo' => 'Providencia Administrativa SNAT/2023/000067',
        'tipo' => 'Providencia',
        'descripcion' => 'Establece las normas relativas a los deberes formales de los contribuyentes del impuesto sobre sucesiones, incluyendo plazos de presentación de la declaración sucesoral (Forma 32), documentación requerida y procedimiento para solicitar prórrogas ante la Administración Tributaria.',
        'url' => null,
        'estado' => 'Vigente',
        'orden' => 3,
        'fecha_publicacion' => '2023-06-15',
        'numero_gaceta' => '42.660',
    ],
    [
        'id' => 4,
        'titulo' => 'Gaceta Oficial N° 6.287 — Reajuste de la Unidad Tributaria',
        'tipo' => 'Gaceta_Oficial',
        'descripcion' => 'Publicación oficial que establece el valor actualizado de la Unidad Tributaria (U.T.), base de cálculo para determinar el monto del impuesto sucesoral, multas, sanciones y otros conceptos tributarios expresados en unidades tributarias.',
        'url' => 'https://www.seniat.gob.ve/gaceta-ut',
        'estado' => 'Vigente',
        'orden' => 4,
        'fecha_publicacion' => '2024-03-01',
        'numero_gaceta' => '6.287 Extraordinario',
    ],
    [
        'id' => 5,
        'titulo' => 'Ley Orgánica de la Administración Financiera del Sector Público',
        'tipo' => 'Ley',
        'descripcion' => 'Regula la administración financiera, el sistema de control interno del sector público y los mecanismos de recaudación de ingresos no tributarios. Define el marco bajo el cual operan los órganos del Poder Público Nacional en materia presupuestaria, contable y de tesorería.',
        'url' => null,
        'estado' => 'Vigente',
        'orden' => 5,
        'fecha_publicacion' => '2015-11-30',
        'numero_gaceta' => '6.210 Extraordinario',
    ],
    [
        'id' => 6,
        'titulo' => 'Providencia SNAT/2009/0117 — Declaración Electrónica de Sucesiones',
        'tipo' => 'Providencia',
        'descripcion' => 'Regulaba el procedimiento para la presentación electrónica de la Declaración Sucesoral (Forma 32) a través del portal del SENIAT. Fue sustituida por la Providencia SNAT/2023/000067 que moderniza el proceso.',
        'url' => null,
        'estado' => 'Derogado',
        'orden' => 6,
        'fecha_publicacion' => '2009-08-20',
        'numero_gaceta' => '39.247',
    ],
    [
        'id' => 7,
        'titulo' => 'Código Civil de Venezuela',
        'tipo' => 'Codigo',
        'descripcion' => 'Contiene las disposiciones sobre el derecho de sucesiones en el ámbito civil: orden de suceder, legítima, testamentos, aceptación y repudiación de herencia, partición de bienes y comunidad hereditaria. Es la base sustantiva sobre la cual se aplica la normativa tributaria sucesoral.',
        'url' => 'https://www.tsj.gob.ve/codigo-civil',
        'estado' => 'Vigente',
        'orden' => 7,
        'fecha_publicacion' => '1982-07-26',
        'numero_gaceta' => '2.990 Extraordinario',
    ],
    [
        'id' => 8,
        'titulo' => 'Gaceta Oficial N° 38.628 — Tarifas Sucesorales Anteriores',
        'tipo' => 'Gaceta_Oficial',
        'descripcion' => 'Publicación que contenía las tarifas de impuesto sucesoral aplicables antes de la reforma. Las alícuotas y tramos han sido actualizados por legislación posterior.',
        'url' => null,
        'estado' => 'Derogado',
        'orden' => 8,
        'fecha_publicacion' => '2007-02-06',
        'numero_gaceta' => '38.628',
    ],
];

// Contadores por tipo
$contadores = ['Todos' => count($normas)];
foreach ($normas as $n) {
    $t = $n['tipo'];
    $contadores[$t] = ($contadores[$t] ?? 0) + 1;
}

// Mapeo tipo → clase CSS y label legible
$tipoMap = [
    'Ley' => ['class' => 'tipo-ley', 'label' => 'Ley'],
    'Codigo' => ['class' => 'tipo-codigo', 'label' => 'Código'],
    'Providencia' => ['class' => 'tipo-providencia', 'label' => 'Providencia'],
    'Gaceta_Oficial' => ['class' => 'tipo-gaceta', 'label' => 'Gaceta Oficial'],
];

ob_start();
?>

<!-- Page Header -->
<div class="page-header">
  <div class="page-header-left">
    <h1>Marco Legal</h1>
    <p>Normativa aplicable al proceso de declaración sucesoral ante el SENIAT. Material de referencia estático.</p>
  </div>
</div>

<!-- Toolbar: Search + Filters -->
<div class="ml-toolbar">
  <div class="ml-search-box">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
      <circle cx="11" cy="11" r="8" />
      <path d="m21 21-4.35-4.35" />
    </svg>
    <input type="text" id="ml-search" placeholder="Buscar por título, descripción o gaceta...">
  </div>
  <div class="ml-filters" id="ml-filters">
    <button class="ml-chip active" data-filter="Todos">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
        <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3" />
      </svg>
      Todos
      <span class="ml-chip-count"><?= $contadores['Todos'] ?></span>
    </button>
    <button class="ml-chip" data-filter="Ley">
      Ley
      <span class="ml-chip-count"><?= $contadores['Ley'] ?? 0 ?></span>
    </button>
    <button class="ml-chip" data-filter="Codigo">
      Código
      <span class="ml-chip-count"><?= $contadores['Codigo'] ?? 0 ?></span>
    </button>
    <button class="ml-chip" data-filter="Providencia">
      Providencia
      <span class="ml-chip-count"><?= $contadores['Providencia'] ?? 0 ?></span>
    </button>
    <button class="ml-chip" data-filter="Gaceta_Oficial">
      Gaceta Oficial
      <span class="ml-chip-count"><?= $contadores['Gaceta_Oficial'] ?? 0 ?></span>
    </button>
  </div>
</div>

<!-- Results Info -->
<div class="ml-results-info" id="ml-results-info">
  Mostrando <strong id="ml-showing"><?= count($normas) ?></strong> de <strong><?= count($normas) ?></strong> normas
</div>

<!-- Cards Grid -->
<div class="ml-grid" id="ml-grid">
  <?php foreach ($normas as $i => $norma):
    $tipoInfo = $tipoMap[$norma['tipo']] ?? ['class' => 'tipo-ley', 'label' => $norma['tipo']];
    $isDerogado = $norma['estado'] === 'Derogado';
    $fechaFormatted = $norma['fecha_publicacion']
      ? date('d/m/Y', strtotime($norma['fecha_publicacion']))
      : null;
    // check if description is long enough to truncate (over ~120 chars)
    $needsTruncate = mb_strlen($norma['descripcion']) > 150;
  ?>
    <div class="ml-card animate-in <?= $isDerogado ? 'ml-card--derogado' : '' ?>"
         data-tipo="<?= htmlspecialchars($norma['tipo']) ?>"
         data-estado="<?= htmlspecialchars($norma['estado']) ?>"
         data-search="<?= htmlspecialchars(mb_strtolower($norma['titulo'] . ' ' . $norma['descripcion'] . ' ' . ($norma['numero_gaceta'] ?? ''))) ?>">

      <!-- Header: Badges -->
      <div class="ml-card-header">
        <div class="ml-card-badges">
          <span class="ml-badge-tipo <?= $tipoInfo['class'] ?>"><?= $tipoInfo['label'] ?></span>
          <span class="ml-badge-estado <?= $isDerogado ? 'derogado' : 'vigente' ?>">
            <?= $norma['estado'] ?>
          </span>
        </div>
      </div>

      <!-- Title -->
      <h3 class="ml-card-title"><?= htmlspecialchars($norma['titulo']) ?></h3>

      <!-- Description -->
      <p class="ml-card-desc <?= $needsTruncate ? 'truncated' : '' ?>">
        <?= htmlspecialchars($norma['descripcion']) ?>
      </p>
      <?php if ($needsTruncate): ?>
        <button class="ml-toggle-more" onclick="this.previousElementSibling.classList.toggle('truncated'); this.previousElementSibling.classList.toggle('expanded'); this.textContent = this.textContent.trim() === 'Ver más' ? 'Ver menos' : 'Ver más';">Ver más</button>
      <?php endif; ?>

      <!-- Meta: Fecha + Gaceta -->
      <div class="ml-card-meta">
        <?php if ($fechaFormatted): ?>
          <span class="ml-meta-item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
              <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
              <line x1="16" y1="2" x2="16" y2="6" />
              <line x1="8" y1="2" x2="8" y2="6" />
              <line x1="3" y1="10" x2="21" y2="10" />
            </svg>
            <?= $fechaFormatted ?>
          </span>
        <?php endif; ?>
        <?php if (!empty($norma['numero_gaceta'])): ?>
          <span class="ml-meta-item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
              <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" />
              <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" />
            </svg>
            Gaceta N° <?= htmlspecialchars($norma['numero_gaceta']) ?>
          </span>
        <?php endif; ?>
      </div>

      <!-- Footer: URL -->
      <?php if (!empty($norma['url'])): ?>
        <div class="ml-card-footer">
          <a href="<?= htmlspecialchars($norma['url']) ?>" target="_blank" rel="noopener noreferrer" class="ml-link-btn">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
              <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6" />
              <polyline points="15 3 21 3 21 9" />
              <line x1="10" y1="14" x2="21" y2="3" />
            </svg>
            Ver documento
          </a>
        </div>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>

  <!-- Empty State (hidden by default, shown via JS) -->
  <div class="ml-empty" id="ml-empty" style="display: none;">
    <div class="ml-empty-icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
        <circle cx="11" cy="11" r="8" />
        <path d="m21 21-4.35-4.35" />
      </svg>
    </div>
    <h3>Sin resultados</h3>
    <p>No se encontraron normas con los filtros seleccionados.</p>
  </div>
</div>

<!-- JS: Filtrado y Búsqueda -->
<script>
(function () {
  const chips = document.querySelectorAll('.ml-chip');
  const cards = document.querySelectorAll('.ml-card');
  const searchInput = document.getElementById('ml-search');
  const showingEl = document.getElementById('ml-showing');
  const emptyEl = document.getElementById('ml-empty');

  let activeFilter = 'Todos';
  let searchQuery = '';

  function render() {
    let visible = 0;
    const q = searchQuery.toLowerCase().trim();

    cards.forEach(card => {
      const tipo = card.dataset.tipo;
      const searchText = card.dataset.search || '';

      const matchesFilter = activeFilter === 'Todos' || tipo === activeFilter;
      const matchesSearch = !q || searchText.includes(q);

      if (matchesFilter && matchesSearch) {
        card.style.display = '';
        visible++;
      } else {
        card.style.display = 'none';
      }
    });

    showingEl.textContent = visible;
    emptyEl.style.display = visible === 0 ? '' : 'none';
  }

  // Filter chips
  chips.forEach(chip => {
    chip.addEventListener('click', () => {
      chips.forEach(c => c.classList.remove('active'));
      chip.classList.add('active');
      activeFilter = chip.dataset.filter;
      render();
    });
  });

  // Search
  searchInput.addEventListener('input', () => {
    searchQuery = searchInput.value;
    render();
  });
})();
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/logged_layout.php';
?>
