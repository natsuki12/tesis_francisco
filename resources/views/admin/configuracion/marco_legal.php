<?php
declare(strict_types=1);

// ARCHIVO: resources/views/admin/configuracion/marco_legal.php

$pageTitle = 'Marco Legal';
$activePage = 'marco-legal';
$breadcrumbs = [
    'Inicio' => base_url('/admin'),
    'Configuración' => '#',
    'Marco Legal' => '#'
];

$extraCss = '<link rel="stylesheet" href="' . asset('css/professor/casos_sucesorales.css') . '">';

ob_start();
?>

<div class="page-header">
    <div class="page-header-left">
        <h1>Marco Legal Vigente</h1>
        <p>Actualice los artículos y estatutos técnicos aplicables para el simulador interactivo.</p>
    </div>
    <button class="btn btn-primary" onclick="alert('Funcionalidad de Modal diferida.')">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
            stroke-linecap="round">
            <line x1="12" y1="5" x2="12" y2="19" />
            <line x1="5" y1="12" x2="19" y2="12" />
        </svg>
        Registrar Artículo
    </button>
</div>

<!-- Tabla Base -->
<div class="table-container">
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 15%">Código / Art.</th>
                <th style="width: 60%">Extracto</th>
                <th style="width: 15%">Afectación</th>
                <th style="width: 10%">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>L.I.S.S.D Art. 52</strong></td>
                <td>
                    <p style="margin:0; font-size: 13px; color: var(--color-text-light); line-height:1.4;">"Cuando la
                        cuota de un heredero o legatario exceda del equivalente a cincuenta (50) UT..."</p>
                </td>
                <td>Cálculo Impuesto</td>
                <td>
                    <div class="row-actions">
                        <button class="row-action-btn" title="Editar"><svg viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                            </svg></button>
                    </div>
                </td>
            </tr>
            <tr>
                <td><strong>C.C.V Art. 822</strong></td>
                <td>
                    <p style="margin:0; font-size: 13px; color: var(--color-text-light); line-height:1.4;">"Al padre, a
                        la madre y a todo ascendiente suceden sus hijos o descendientes..."</p>
                </td>
                <td>Orden Sucesoral</td>
                <td>
                    <div class="row-actions">
                        <button class="row-action-btn" title="Editar"><svg viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                            </svg></button>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<?php
$extraJs = '<script src="' . asset('js/modal.js') . '"></script>';
$content = ob_get_clean();
require __DIR__ . '/../../layouts/logged_layout.php';
?>