<?php
declare(strict_types=1);

$pageTitle = 'Gestión de Estudiantes';
$activePage = 'estudiantes';
$breadcrumbs = [
    'Inicio' => base_url('/admin'),
    'Gestión de Usuarios' => '#',
    'Estudiantes' => '#'
];

$extraCss = '<link rel="stylesheet" href="' . asset('css/shared/data-table.css') . '">';

// Datos inyectados por el controlador
$estudiantes = $estudiantes ?? [];
$conteo      = $conteo ?? ['total' => 0, 'activos' => 0, 'inactivos' => 0];

ob_start();
?>
<div class="page-header">
    <div class="page-header-left">
        <h1>Estudiantes Registrados</h1>
        <p>Visualice y gestione las cuentas de los estudiantes inscritos en el Simulador SENIAT.</p>
    </div>
</div>

<!-- Toolbar -->
<div class="toolbar">
    <div class="toolbar-left">
        <div class="search-box">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                <circle cx="11" cy="11" r="8" />
                <path d="m21 21-4.35-4.35" />
            </svg>
            <input type="text" id="searchInput" placeholder="Buscar por nombre, cédula o correo...">
        </div>

        <button class="filter-chip active" data-filter="Todos">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3" />
            </svg>
            Todos <span class="filter-count"><?= $conteo['total'] ?></span>
        </button>
        <button class="filter-chip" data-filter="Activo">Activos <span class="filter-count"><?= $conteo['activos'] ?></span></button>
        <button class="filter-chip" data-filter="Inactivo">Inactivos <span class="filter-count"><?= $conteo['inactivos'] ?></span></button>
    </div>

    <div class="toolbar-right">
        <label style="font-size:var(--text-xs, 13px); color:var(--gray-500, #64748b); display:flex; align-items:center; gap:6px;">
            Mostrar
            <select id="per-page" class="per-page-select">
                <option value="10">10</option>
                <option value="15" selected>15</option>
                <option value="25">25</option>
                <option value="50">50</option>
            </select>
            filas
        </label>
    </div>
</div>

<!-- Data Table -->
<div class="table-container">
    <table class="data-table">
        <thead>
            <tr>
                <th class="sortable" data-sort="nombre">Nombre</th>
                <th class="sortable" data-sort="apellido">Apellido</th>
                <th>Correo</th>
                <th class="sortable" data-sort="cedula">Cédula</th>
                <th>Carrera</th>
                <th>Período</th>
                <th class="sortable" data-sort="fecha">Fecha de Registro</th>
                <th class="sortable" data-sort="estado">Estado</th>
                <th></th>
            </tr>
        </thead>
        <tbody id="estudiantes-tbody">
            <?php if (empty($estudiantes)): ?>
                <tr>
                    <td colspan="9" style="text-align:center; padding:40px; color:var(--gray-400);">
                        No se encontraron estudiantes registrados.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($estudiantes as $est):
                    $nombres  = e($est['nombres'] ?? '');
                    $apellidos = e($est['apellidos'] ?? '');
                    $email    = e($est['email'] ?? '');
                    $cedula   = e(($est['nacionalidad'] ?? 'V') . '-' . ($est['cedula'] ?? ''));
                    $carrera  = e($est['carrera'] ?? '—');
                    $periodo  = e($est['periodo'] ?? '—');
                    $status   = $est['status'] ?? 'active';
                    $userId   = (int) ($est['user_id'] ?? 0);
                    $genero   = strtolower($est['genero'] ?? '');

                    // Iniciales para avatar
                    $iniciales = '';
                    if (!empty($est['nombres']) && !empty($est['apellidos'])) {
                        $iniciales = mb_strtoupper(mb_substr($est['nombres'], 0, 1) . mb_substr($est['apellidos'], 0, 1));
                    }

                    // Clase de género para avatar
                    $avatarClass = ($genero === 'f') ? 'f' : 'm';

                    // Estado
                    $esActivo = ($status === 'active');
                    $estadoLabel = $esActivo ? 'Activo' : 'Inactivo';
                    $estadoBadge = $esActivo ? 'status-published' : 'status-draft';

                    // Fecha formateada
                    $fechaFormatted = '';
                    $fechaRelativa  = '';
                    if (!empty($est['created_at'])) {
                        try {
                            $date = new \DateTime($est['created_at']);
                            $fechaFormatted = $date->format('d M Y');
                            $diff = (new \DateTime())->diff($date);
                            if ($diff->days === 0) {
                                $fechaRelativa = 'Hoy';
                            } elseif ($diff->days === 1) {
                                $fechaRelativa = 'Ayer';
                            } elseif ($diff->days < 7) {
                                $fechaRelativa = 'Hace ' . $diff->days . ' días';
                            } elseif ($diff->days < 30) {
                                $semanas = (int) floor($diff->days / 7);
                                $fechaRelativa = 'Hace ' . $semanas . ' semana' . ($semanas > 1 ? 's' : '');
                            } else {
                                $meses = (int) floor($diff->days / 30);
                                $fechaRelativa = 'Hace ' . $meses . ' mes' . ($meses > 1 ? 'es' : '');
                            }
                        } catch (\Throwable $e) {
                            $fechaFormatted = e($est['created_at']);
                        }
                    }
                ?>
                    <tr data-estado="<?= $estadoLabel ?>"
                        data-search="<?= e(mb_strtolower($nombres . ' ' . $apellidos . ' ' . $cedula . ' ' . $email)) ?>">
                        <td><?= $nombres ?></td>
                        <td><?= $apellidos ?></td>
                        <td style="font-size: 13px; color: var(--gray-500);"><?= $email ?></td>
                        <td class="case-id" style="font-size: 14px;"><?= $cedula ?></td>
                        <td><?= $carrera ?></td>
                        <td><?= $periodo ?></td>
                        <td class="date-cell"><?= $fechaFormatted ?><?php if ($fechaRelativa): ?><br><span class="date-relative"><?= $fechaRelativa ?></span><?php endif; ?></td>
                        <td><span class="status-badge <?= $estadoBadge ?>"><?= $estadoLabel ?></span></td>
                        <td>
                            <div class="row-actions">
                                <!-- Reset Password Button -->
                                <button class="row-action-btn" title="Restablecer Contraseña"
                                    onclick="openResetPassword(<?= $userId ?>, '<?= e(addslashes($nombres . ' ' . $apellidos)) ?>')">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round">
                                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                                        <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                                    </svg>
                                </button>
                                <?php if ($esActivo): ?>
                                    <button class="row-action-btn btn-inactivar-caso" title="Desactivar"
                                        onclick="openDesactivarEstudiante(<?= $userId ?>)">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round">
                                            <path d="M18.36 6.64a9 9 0 1 1-12.73 0" />
                                            <line x1="12" y1="2" x2="12" y2="12" />
                                        </svg>
                                    </button>
                                <?php else: ?>
                                    <button class="row-action-btn btn-reactivar-caso" title="Reactivar"
                                        onclick="openReactivarEstudiante(<?= $userId ?>)">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round">
                                            <polyline points="23 4 23 10 17 10" />
                                            <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10" />
                                        </svg>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Table Footer -->
    <div class="table-footer">
        <div class="table-footer-info">
            Mostrando <strong>0</strong> de <strong><?= count($estudiantes) ?></strong> estudiantes
        </div>
        <div class="pagination"></div>
    </div>
</div>

<!-- ==============================================
     MODALES 
     ============================================== -->

<!-- Modal: Confirmar Desactivación -->
<dialog class="modal-base" id="modal-eliminar">
    <div class="modal-base__container" style="max-width: 480px;">
        <div class="modal-base__header">
            <h2 class="modal-base__title">¿Desactivar estudiante?</h2>
            <button class="modal-base__close" onclick="window.modalManager.close('modal-eliminar')"
                aria-label="Cerrar modal">&times;</button>
        </div>
        <div class="modal-base__body">
            <p style="font-size: 15px; color: var(--text-body); line-height: 1.5; margin-bottom: 0;">
                Si desactiva a este estudiante, <strong>no podrá iniciar sesión en el simulador</strong> ni continuar
                sus resoluciones de casos hasta que sea reactivado. Sus casos en curso no serán eliminados.
            </p>
            <form id="formDesactivarEstudiante" action="<?= base_url('/admin/estudiantes/desactivar') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="desactivar_estudiante_id" value="">
            </form>
        </div>
        <div class="modal-base__footer" style="padding-top: 24px;">
            <button class="modal-btn modal-btn-cancel" style="min-width: 120px;"
                onclick="window.modalManager.close('modal-eliminar')">Cancelar</button>
            <button type="submit" form="formDesactivarEstudiante" class="modal-btn modal-btn-danger"
                style="min-width: 120px;">
                Sí, desactivar
            </button>
        </div>
    </div>
</dialog>

<!-- Modal: Restablecer Contraseña -->
<dialog class="modal-base" id="modal-reset-password">
    <div class="modal-base__container" style="max-width: 460px;">
        <div class="modal-base__header">
            <h2 class="modal-base__title">¿Restablecer contraseña?</h2>
            <button class="modal-base__close" onclick="window.modalManager.close('modal-reset-password')"
                aria-label="Cerrar modal">&times;</button>
        </div>
        <div class="modal-base__body">
            <p style="font-size: 15px; color: var(--text-body); line-height: 1.5; margin-bottom: 0;">
                Se enviará un correo electrónico al estudiante <strong id="reset-student-name"></strong> con un
                enlace para restablecer su contraseña. El enlace expirará en 24 horas.
            </p>
            <form id="formResetPassword" action="<?= base_url('/admin/estudiantes/reset-password') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="reset_estudiante_id" value="">
            </form>
        </div>
        <div class="modal-base__footer">
            <button class="modal-btn modal-btn-cancel"
                onclick="window.modalManager.close('modal-reset-password')">Cancelar</button>
            <button type="submit" form="formResetPassword" class="modal-btn modal-btn-primary">
                Enviar correo
            </button>
        </div>
    </div>
</dialog>

<script>
    function openDesactivarEstudiante(id) {
        document.getElementById('desactivar_estudiante_id').value = id;
        window.modalManager.open('modal-eliminar');
    }

    function openReactivarEstudiante(id) {
        // Reactivar directamente vía fetch
        if (!confirm('¿Desea reactivar a este estudiante?')) return;
        fetch('<?= base_url('/api/admin/estudiantes/') ?>' + id + '/reactivar', {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) location.reload();
            else alert(data.message || 'Error al reactivar.');
        })
        .catch(() => alert('Error de conexión.'));
    }

    function openResetPassword(id, nombre) {
        document.getElementById('reset_estudiante_id').value = id;
        document.getElementById('reset-student-name').textContent = nombre;
        window.modalManager.open('modal-reset-password');
    }

    // ── Client-side search & filter ──
    (function() {
        const tbody = document.getElementById('estudiantes-tbody');
        const rows = Array.from(tbody.querySelectorAll('tr[data-search]'));
        const searchInput = document.getElementById('searchInput');
        const filterChips = document.querySelectorAll('.filter-chip');
        const perPage = document.getElementById('per-page');
        const footerInfo = document.querySelector('.table-footer-info');
        const pagination = document.querySelector('.pagination');

        let currentFilter = 'Todos';
        let currentSearch = '';
        let currentPage = 1;

        function getPageSize() {
            return parseInt(perPage?.value || '15', 10);
        }

        function applyFilters() {
            const term = currentSearch.toLowerCase();
            let visible = [];

            rows.forEach(row => {
                const matchSearch = !term || (row.dataset.search || '').includes(term);
                const matchFilter = currentFilter === 'Todos' || row.dataset.estado === currentFilter;
                const show = matchSearch && matchFilter;
                row.style.display = show ? '' : 'none';
                if (show) visible.push(row);
            });

            // Pagination
            const size = getPageSize();
            const totalPages = Math.max(1, Math.ceil(visible.length / size));
            if (currentPage > totalPages) currentPage = totalPages;

            visible.forEach((row, i) => {
                const page = Math.floor(i / size) + 1;
                row.style.display = page === currentPage ? '' : 'none';
            });

            // Footer
            const from = visible.length > 0 ? (currentPage - 1) * size + 1 : 0;
            const to = Math.min(currentPage * size, visible.length);
            if (footerInfo) {
                footerInfo.innerHTML = `Mostrando <strong>${from}</strong> a <strong>${to}</strong> de <strong>${visible.length}</strong> estudiantes`;
            }

            // Pagination buttons
            if (pagination) {
                pagination.innerHTML = '';
                if (totalPages > 1) {
                    const prevBtn = document.createElement('button');
                    prevBtn.className = 'btn btn-secondary btn-sm';
                    prevBtn.textContent = 'Anterior';
                    prevBtn.disabled = currentPage === 1;
                    prevBtn.style.cssText = 'padding:4px 10px;font-size:13px;border-radius:var(--radius-sm);border-color:var(--gray-300);';
                    prevBtn.onclick = () => { currentPage--; applyFilters(); };
                    pagination.appendChild(prevBtn);

                    for (let p = 1; p <= totalPages; p++) {
                        const btn = document.createElement('button');
                        btn.className = p === currentPage ? 'btn btn-primary btn-sm' : 'btn btn-secondary btn-sm';
                        btn.textContent = p;
                        btn.style.cssText = 'padding:4px 12px;font-size:13px;border-radius:var(--radius-sm);' + (p !== currentPage ? 'border-color:var(--gray-300);' : '');
                        btn.onclick = () => { currentPage = p; applyFilters(); };
                        pagination.appendChild(btn);
                    }

                    const nextBtn = document.createElement('button');
                    nextBtn.className = 'btn btn-secondary btn-sm';
                    nextBtn.textContent = 'Siguiente';
                    nextBtn.disabled = currentPage === totalPages;
                    nextBtn.style.cssText = 'padding:4px 10px;font-size:13px;border-radius:var(--radius-sm);border-color:var(--gray-300);';
                    nextBtn.onclick = () => { currentPage++; applyFilters(); };
                    pagination.appendChild(nextBtn);
                }
            }
        }

        searchInput?.addEventListener('input', (e) => {
            currentSearch = e.target.value;
            currentPage = 1;
            applyFilters();
        });

        filterChips.forEach(chip => {
            chip.addEventListener('click', () => {
                filterChips.forEach(c => c.classList.remove('active'));
                chip.classList.add('active');
                currentFilter = chip.dataset.filter;
                currentPage = 1;
                applyFilters();
            });
        });

        perPage?.addEventListener('change', () => {
            currentPage = 1;
            applyFilters();
        });

        // Initial render
        applyFilters();
    })();
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/logged_layout.php';
?>