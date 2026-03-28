<?php
declare(strict_types=1);

// ARCHIVO: resources/views/professor/calificaciones.php
// Sábana de notas — tabla cruzada Estudiantes × Casos, AJAX por sección.

$pageTitle = 'Calificaciones — Simulador SENIAT';
$activePage = 'calificaciones';
$extraCss  = '<link rel="stylesheet" href="' . asset('css/shared/data-table.css') . '">';
$extraCss .= '<link rel="stylesheet" href="' . asset('css/professor/calificaciones.css') . '">';

$secciones = $secciones ?? [];

ob_start();
?>

<!-- Page Header -->
<div class="page-header">
    <div class="page-header-left">
        <h1>Calificaciones</h1>
        <p>Sábana de notas por sección y caso</p>
    </div>
</div>

<!-- Toolbar -->
<div class="toolbar">
    <div class="toolbar-left">
        <select class="filter-select" id="select-seccion" style="min-width:200px;">
            <option value="">— Seleccionar sección —</option>
            <?php foreach ($secciones as $sec): ?>
                <option value="<?= (int)$sec['id'] ?>">
                    <?= htmlspecialchars($sec['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<!-- Stats Row (hidden until loaded) -->
<div class="stats-row" id="cal-stats" style="display:none;">
    <div class="stat-card stat-card--vertical animate-in">
        <div class="stat-card-top">
            <span class="stat-label">Estudiantes</span>
            <div class="stat-icon blue">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                    <circle cx="9" cy="7" r="4" />
                </svg>
            </div>
        </div>
        <div class="stat-value" id="stat-total">0</div>
    </div>
    <div class="stat-card stat-card--vertical animate-in">
        <div class="stat-card-top">
            <span class="stat-label">Calificados</span>
            <div class="stat-icon green">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <polyline points="20 6 9 17 4 12" />
                </svg>
            </div>
        </div>
        <div class="stat-value" id="stat-calificados">0</div>
    </div>
    <div class="stat-card stat-card--vertical animate-in">
        <div class="stat-card-top">
            <span class="stat-label">Pendientes</span>
            <div class="stat-icon amber">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <circle cx="12" cy="12" r="10" />
                    <polyline points="12 6 12 12 16 14" />
                </svg>
            </div>
        </div>
        <div class="stat-value" id="stat-pendientes">0</div>
    </div>
</div>

<!-- Grades Container -->
<div class="grades-wrapper animate-in" id="grades-container">
    <div class="grades-initial-state" id="cal-placeholder">
        <div class="empty-state-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                <polyline points="22 4 12 14.01 9 11.01" />
            </svg>
        </div>
        <h3>Selecciona una sección</h3>
        <p>Elige una sección del selector para ver las calificaciones de tus estudiantes.</p>
    </div>
    <div id="cal-table-wrap" style="display:none; overflow-x:auto;"></div>
</div>

<script>
(function() {
    var API = '<?= base_url("/calificaciones/api") ?>';
    var select = document.getElementById('select-seccion');
    var placeholder = document.getElementById('cal-placeholder');
    var tableWrap = document.getElementById('cal-table-wrap');
    var statsRow = document.getElementById('cal-stats');

    var STORAGE_KEY = 'cal_seccion';

    // Restore saved section
    var saved = localStorage.getItem(STORAGE_KEY);
    if (saved && select.querySelector('option[value="' + saved + '"]')) {
        select.value = saved;
    }

    select.addEventListener('change', function() {
        var secId = this.value;
        localStorage.setItem(STORAGE_KEY, secId);
        if (!secId) {
            placeholder.style.display = '';
            tableWrap.style.display = 'none';
            statsRow.style.display = 'none';
            return;
        }
        loadSeccion(secId);
    });

    function loadSeccion(secId) {
        placeholder.style.display = 'none';
        tableWrap.style.display = '';
        tableWrap.innerHTML = '<p style="text-align:center; padding:40px; color:var(--gray-400)">Cargando…</p>';

        fetch(API + '?seccion_id=' + secId)
            .then(function(r) { return r.json(); })
            .then(function(data) { renderSabana(data); })
            .catch(function() {
                tableWrap.innerHTML = '<p style="text-align:center; padding:40px; color:var(--red-500)">Error al cargar datos</p>';
            });
    }

    var avatarColors = ['avatar--blue', 'avatar--green', 'avatar--amber', 'avatar--purple', 'avatar--red'];

    // Auto-load saved section on page load
    if (select.value) {
        loadSeccion(select.value);
    }

    function renderSabana(data) {
        var estudiantes = data.estudiantes || [];
        var casos = data.casos || [];
        var stats = data.stats || {};

        // Stats
        document.getElementById('stat-total').textContent = stats.total || 0;
        document.getElementById('stat-calificados').textContent = stats.calificados || 0;
        document.getElementById('stat-pendientes').textContent = stats.pendientes || 0;
        statsRow.style.display = '';

        if (!estudiantes.length) {
            tableWrap.innerHTML = '<div class="grades-initial-state"><h3>Sin estudiantes</h3><p>No hay estudiantes asignados a casos en esta sección.</p></div>';
            return;
        }

        var html = '<table class="grades-table">';
        // Header
        html += '<thead><tr><th class="col-estudiante">Estudiante</th>';
        casos.forEach(function(c) {
            var tipoLabel = c.tipo_calificacion === 'numerica' ? '(Numérica)' : '(A/R)';
            html += '<th class="col-caso">' + esc(c.titulo) + '<br><small style="color:var(--gray-400);font-weight:400;">' + tipoLabel + '</small></th>';
        });
        html += '<th class="col-promedio">Promedio</th></tr></thead>';

        // Body
        html += '<tbody>';
        estudiantes.forEach(function(est, i) {
            var fullName = (est.nombres || '') + ' ' + (est.apellidos || '');
            var initials = getInitials(fullName);
            var avatarCls = avatarColors[Math.abs(hashCode(initials)) % avatarColors.length];
            var cedula = (est.nacionalidad || 'V') + '-' + formatNumber(est.cedula);

            html += '<tr>';
            // Estudiante cell
            html += '<td class="col-estudiante">';
            html += '<div class="estudiante-cell">';
            html += '<div class="estudiante-avatar ' + avatarCls + '">' + esc(initials) + '</div>';
            html += '<div class="estudiante-info">';
            html += '<div class="estudiante-name">' + esc(fullName.trim()) + '</div>';
            html += '<div class="estudiante-ci">' + esc(cedula) + '</div>';
            html += '</div></div></td>';

            // Notas per caso
            casos.forEach(function(caso) {
                var nota = est.notas[caso.config_id] || null;
                html += '<td class="col-caso">' + renderNota(nota, caso) + '</td>';
            });

            // Promedio
            html += '<td class="col-promedio">';
            if (est.promedio !== null) {
                var promCls = est.promedio >= 10 ? 'grade-pass' : 'grade-fail';
                html += '<span class="promedio-final ' + promCls + '">' + est.promedio.toFixed(1) + '</span>';
            } else {
                html += '<span class="promedio-final grade-na">—</span>';
            }
            html += '</td>';

            html += '</tr>';
        });
        html += '</tbody></table>';

        tableWrap.innerHTML = html;
    }

    function renderNota(nota, caso) {
        if (!nota) {
            // No asignado a este caso
            return '<span class="grade-cell grade-no-asignado" title="No asignado">No asignado</span>';
        }
        if (nota.estado === 'sin_intento') {
            // Asignado pero no ha iniciado
            return '<span class="grade-cell grade-sin-intento" title="Asignado, sin intento">Sin intento</span>';
        }
        if (nota.estado === 'Enviado') {
            return '<a href="<?= base_url("/entregas/") ?>' + nota.intento_id + '" class="grade-cell grade-pending" title="Enviado, pendiente de revisión">Pendiente</a>';
        }
        // Calificado
        if (caso.tipo_calificacion === 'numerica' && nota.nota_numerica !== null) {
            var n = parseFloat(nota.nota_numerica);
            var cls = n >= 10 ? 'grade-pass' : 'grade-fail';
            return '<a href="<?= base_url("/entregas/") ?>' + nota.intento_id + '" class="grade-cell ' + cls + '">' + n.toFixed(1) + '</a>';
        }
        // Cualitativa
        if (nota.nota_cualitativa) {
            var cls2 = nota.nota_cualitativa === 'Aprobado' ? 'grade-pass' : 'grade-fail';
            var label = nota.nota_cualitativa === 'Aprobado' ? 'A' : 'R';
            return '<a href="<?= base_url("/entregas/") ?>' + nota.intento_id + '" class="grade-cell ' + cls2 + '" title="' + nota.nota_cualitativa + '">' + label + '</a>';
        }
        return '<span class="grade-cell grade-na">—</span>';
    }

    function esc(s) {
        var el = document.createElement('span');
        el.textContent = s || '';
        return el.innerHTML;
    }

    function getInitials(name) {
        var parts = (name || '').trim().split(/\s+/);
        return (parts[0] ? parts[0][0] : '') + (parts[1] ? parts[1][0] : '');
    }

    function hashCode(s) {
        var h = 0;
        for (var i = 0; i < s.length; i++) {
            h = ((h << 5) - h) + s.charCodeAt(i);
            h |= 0;
        }
        return h;
    }

    function formatNumber(n) {
        if (!n) return '0';
        return parseInt(n).toLocaleString('es-VE');
    }
})();
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/logged_layout.php';
?>