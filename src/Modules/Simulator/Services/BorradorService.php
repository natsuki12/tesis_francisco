<?php
declare(strict_types=1);

namespace App\Modules\Simulator\Services;

/**
 * BorradorService — Extracts and formats data from borrador_json.
 *
 * Reusable across all sucesion pages (principal, herederos, bienes, etc.).
 * Supports both JSON formats:
 *   - New format: datos_basicos, representante, herederos.items, direcciones.items
 *   - Legacy format: inscripcion, relaciones, direcciones (flat array)
 */
class BorradorService
{
    private array $borrador;
    private array $intento;

    public function __construct(array $intento)
    {
        $this->intento = $intento;
        $raw = $intento['borrador_json'] ?? '{}';
        $this->borrador = json_decode($raw, true) ?: [];
    }

    // ─── Raw accessors ───

    public function getBorrador(): array
    {
        return $this->borrador;
    }

    /**
     * Returns datos_basicos (new format) or inscripcion (legacy).
     */
    public function getDatosBasicos(): array
    {
        return $this->borrador['datos_basicos']
            ?? $this->borrador['inscripcion']
            ?? [];
    }

    /**
     * Returns relaciones array (used by both formats for herederos page).
     */
    public function getRelaciones(): array
    {
        return $this->borrador['relaciones'] ?? [];
    }

    /**
     * Returns direcciones as a flat array of items.
     * Handles both: direcciones.items[] (new) and direcciones[] (legacy flat).
     */
    public function getDirecciones(): array
    {
        $dirs = $this->borrador['direcciones'] ?? [];
        // New format: { items: [...] }
        if (isset($dirs['items'])) {
            return $dirs['items'];
        }
        // Legacy format: flat array [...] or already array of objects
        return is_array($dirs) && !empty($dirs) && isset($dirs[0]) ? $dirs : [];
    }

    // ─── Datos de la Sucesión ───

    public function getRif(): string
    {
        // Primary: from DB column sim_intentos.rif_sucesoral
        if (!empty($this->intento['rif_sucesoral'])) {
            return str_replace('-', '', $this->intento['rif_sucesoral']);
        }
        // Fallback: from borrador_json
        $db = $this->getDatosBasicos();
        $rif = $db['rif'] ?? $db['rif_personal'] ?? '';
        return str_replace('-', '', $rif);
    }

    public function getNombreSucesion(): string
    {
        $db = $this->getDatosBasicos();
        $apellidos = $db['apellidos'] ?? trim(($db['primer_apellido'] ?? '') . ' ' . ($db['segundo_apellido'] ?? ''));
        $nombres   = $db['nombres']   ?? trim(($db['primer_nombre'] ?? '')   . ' ' . ($db['segundo_nombre'] ?? ''));
        return 'SUCESION ' . strtoupper(trim($apellidos . ', ' . $nombres));
    }

    public function getEmail(): string
    {
        $db = $this->getDatosBasicos();
        return strtoupper($db['email_sucesion'] ?? $db['correo'] ?? '');
    }

    public function getFechaFallecimiento(): string
    {
        $db = $this->getDatosBasicos();
        $fecha = $db['fecha_fallecimiento'] ?? '';
        return $this->isoToDisplay($fecha);
    }

    public function getFechaVencimiento(): string
    {
        $db = $this->getDatosBasicos();
        $fecha = $db['fecha_fallecimiento'] ?? '';
        if (empty($fecha)) return '';

        try {
            $dt = new \DateTime($fecha);
            $dt->modify('+180 days');
            return $dt->format('d/m/Y');
        } catch (\Exception $e) {
            return '';
        }
    }

    public function getUtAplicable(): string
    {
        $db = $this->getDatosBasicos();
        $ut = $db['ut_aplicable'] ?? '0,4000000000';
        if (is_numeric($ut)) {
            return number_format((float) $ut, 10, ',', '');
        }
        return (string) $ut;
    }

    // ─── Datos del Causante ───

    public function getCedulaCausante(): string
    {
        $db = $this->getDatosBasicos();
        return $db['cedula'] ?? '';
    }

    /**
     * RIF personal del causante (V/E type), distinto al rif_sucesoral (J type).
     */
    public function getRifCausante(): string
    {
        $db = $this->getDatosBasicos();
        return $db['rif_personal'] ?? $db['rif'] ?? '';
    }

    public function getNombreCausante(): string
    {
        $db = $this->getDatosBasicos();
        $apellidos = $db['apellidos'] ?? trim(($db['primer_apellido'] ?? '') . ' ' . ($db['segundo_apellido'] ?? ''));
        $nombres   = $db['nombres']   ?? trim(($db['primer_nombre'] ?? '')   . ' ' . ($db['segundo_nombre'] ?? ''));
        return strtoupper(trim($apellidos . ', ' . $nombres));
    }

    /**
     * Display name: "NOMBRES APELLIDOS" (for grey bar header).
     */
    public function getNombreCausanteDisplay(): string
    {
        $db = $this->getDatosBasicos();
        $apellidos = $db['apellidos'] ?? trim(($db['primer_apellido'] ?? '') . ' ' . ($db['segundo_apellido'] ?? ''));
        $nombres   = $db['nombres']   ?? trim(($db['primer_nombre'] ?? '')   . ' ' . ($db['segundo_nombre'] ?? ''));
        return strtoupper(trim($nombres . ' ' . $apellidos));
    }

    // ─── Representante Legal ───

    public function getRepresentanteLegal(): array
    {
        // New format: representante is a top-level object
        if (isset($this->borrador['representante'])) {
            $rep = $this->borrador['representante'];
            $tipoCed = $rep['tipo_cedula'] ?? 'V';
            $cedula  = $rep['cedula'] ?? '';
            return [
                'rif'    => $tipoCed . $cedula,
                'nombre' => strtoupper(trim(($rep['apellidos'] ?? '') . ', ' . ($rep['nombres'] ?? ''))),
            ];
        }

        // Legacy format: find in relaciones by parentescoText
        foreach ($this->getRelaciones() as $rel) {
            if (isset($rel['parentescoText']) && strtoupper($rel['parentescoText']) === 'REPRESENTANTE DE LA SUCESION') {
                return [
                    'rif'    => $rel['idDocumento'] ?? (($rel['tipodocumento'] ?? '') . ($rel['cedula'] ?? '')),
                    'nombre' => strtoupper(trim(($rel['apellido'] ?? '') . ', ' . ($rel['nombre'] ?? ''))),
                ];
            }
        }
        return ['rif' => '', 'nombre' => ''];
    }

    // ─── Domicilio Fiscal ───

    public function getDomicilioFiscal(): string
    {
        $dirs = $this->getDirecciones();
        if (empty($dirs)) return '';

        // Use the first "DOMICILIO FISCAL" or the first entry
        $d = $dirs[0];
        foreach ($dirs as $dir) {
            if (strtoupper($dir['tipoDireccionText'] ?? '') === 'DOMICILIO FISCAL') {
                $d = $dir;
                break;
            }
        }

        // SENIAT format: the JSON already has *Text fields for geo names
        if (isset($d['vialidad']) || isset($d['tipoVialidadLabel'])) {
            // Resolve parroquia name (no parroquiaText in JSON → use GeoModel)
            $parroquiaNombre = '';
            if (!empty($d['parroquiaText'])) {
                $parroquiaNombre = $d['parroquiaText'];
            } elseif (!empty($d['parroquia'])) {
                $geo = new \App\Modules\Simulator\Models\GeoModel();
                $parroquiaNombre = $geo->getParroquiaNombre((int) $d['parroquia']);
            }

            return strtoupper(implode(' ', array_filter([
                $d['tipoVialidadLabel'] ?? '',
                $d['vialidad'] ?? '',
                $d['tipoEdificacionLabel'] ?? '',
                $d['edificacion'] ?? '',
                !empty($d['piso']) ? $d['piso'] : '',
                $d['tipoLocalLabel'] ?? '',
                $d['local'] ?? '',
                $d['tipoSectorLabel'] ?? '',
                $d['sector'] ?? '',
                !empty($d['ciudadText'])    ? 'CIUDAD ' . $d['ciudadText']          : '',
                $parroquiaNombre            ? 'PARROQUIA: ' . $parroquiaNombre       : '',
                !empty($d['municipioText']) ? 'MUNICIPIO: ' . $d['municipioText']    : '',
                !empty($d['estadoText'])   ? 'ESTADO: ' . $d['estadoText']          : '',
            ])));
        }

        // Legacy format — text fields already present
        return strtoupper(implode(' ', array_filter([
            $d['calle'] ?? '', $d['casa'] ?? '', $d['conjunto'] ?? '',
            $d['nro'] ?? '', $d['local'] ?? '',
            !empty($d['urbanizacion']) ? 'URBANIZACION ' . $d['urbanizacion'] : '',
            !empty($d['ciudad']) ? 'CIUDAD ' . $d['ciudad'] : '',
            !empty($d['parroquia']) ? 'PARROQUIA: ' . $d['parroquia'] : '',
            !empty($d['municipio']) ? 'MUNICIPIO: ' . $d['municipio'] : '',
            !empty($d['estado']) ? 'ESTADO: ' . $d['estado'] : '',
        ])));
    }

    // ─── Herederos ───

    public function getHerederos(): array
    {
        $herederos = [];

        // New format: herederos.items[]
        if (isset($this->borrador['herederos']['items'])) {
            foreach ($this->borrador['herederos']['items'] as $h) {
                $tipoCed = $h['tipo_cedula'] ?? 'V';
                $cedula  = $h['cedula'] ?? '';
                $herederos[] = [
                    'apellido'    => strtoupper($h['apellidos'] ?? ''),
                    'nombre'      => strtoupper($h['nombres'] ?? ''),
                    'idDocumento' => $tipoCed . $cedula,
                ];
            }
            return $herederos;
        }

        // Legacy format: filter relaciones by parentescoText
        foreach ($this->getRelaciones() as $rel) {
            if (isset($rel['parentescoText']) && strtoupper($rel['parentescoText']) === 'HEREDERO') {
                $herederos[] = [
                    'apellido'    => strtoupper($rel['apellido'] ?? ''),
                    'nombre'      => strtoupper($rel['nombre'] ?? ''),
                    'idDocumento' => $rel['idDocumento'] ?? (($rel['tipodocumento'] ?? '') . ($rel['cedula'] ?? '')),
                ];
            }
        }
        return $herederos;
    }

    // ─── Helpers ───

    /**
     * Parse decimal value supporting both formats:
     *  - Venezuelan: "3.700.000,00" → 3700000.00
     *  - Plain:      "555.5", "12", "1000" → 555.5, 12.0, 1000.0
     */
    public function parseDecimal(string $value): float
    {
        $value = trim($value);
        if ($value === '' || $value === '0') return 0.0;

        // If it has a comma → Venezuelan format (dots are thousands, comma is decimal)
        if (str_contains($value, ',')) {
            $value = str_replace('.', '', $value);   // remove thousands
            $value = str_replace(',', '.', $value);  // comma → dot
        }
        // Otherwise dots are decimal separators (plain format)

        return (float) $value;
    }

    /**
     * Format float → "3.700.000,00"
     */
    public function formatDecimal(float $value): string
    {
        return number_format($value, 2, ',', '.');
    }

    /**
     * Sum valor_declarado across an array of items.
     */
    private function sumValorDeclarado(array $items, string $field = 'valor_declarado'): float
    {
        $total = 0.0;
        foreach ($items as $item) {
            $total += $this->parseDecimal($item[$field] ?? '0,00');
        }
        return $total;
    }

    // ─── Totales para Resumen ───

    public function getTotalBienesInmuebles(): float
    {
        return $this->sumValorDeclarado($this->borrador['bienes_inmuebles'] ?? []);
    }

    public function getTotalBienesMuebles(): float
    {
        $keys = [
            'bienes_muebles_banco',
            'bienes_muebles_seguro',
            'bienes_muebles_transporte',
            'bienes_muebles_acciones',
            'bienes_muebles_bonos',
            'bienes_muebles_caja_ahorro',
            'bienes_muebles_cuentas_efectos',
            'bienes_muebles_opciones_compra',
            'bienes_muebles_plantaciones',
            'bienes_muebles_prestaciones_sociales',
            'bienes_muebles_semovientes',
            'bienes_muebles_otros',
        ];
        $total = 0.0;
        foreach ($keys as $key) {
            $total += $this->sumValorDeclarado($this->borrador[$key] ?? []);
        }
        return $total;
    }

    /**
     * Desgravámenes = vivienda principal (inmuebles) + seguros montepío/vida + prestaciones sociales.
     */
    public function getTotalDesgravamenes(): float
    {
        $total = 0.0;
        // 1. Inmuebles con vivienda_principal = 'true'
        foreach (($this->borrador['bienes_inmuebles'] ?? []) as $inm) {
            if (($inm['vivienda_principal'] ?? 'false') === 'true') {
                $total += $this->parseDecimal($inm['valor_declarado'] ?? '0,00');
            }
        }
        // 2. Seguros tipo 08 (Seguro de Vida) y 09 (Montepío)
        foreach (($this->borrador['bienes_muebles_seguro'] ?? []) as $seg) {
            $tipo = $seg['tipo_bien'] ?? '';
            if ($tipo === '08' || $tipo === '09') {
                $total += $this->parseDecimal($seg['valor_declarado'] ?? '0,00');
            }
        }
        return $total;
    }

    public function getTotalExenciones(): float
    {
        return $this->sumValorDeclarado($this->borrador['exenciones'] ?? []);
    }

    public function getTotalExoneraciones(): float
    {
        return $this->sumValorDeclarado($this->borrador['exoneraciones'] ?? []);
    }

    public function getTotalPasivos(): float
    {
        $keys = [
            'pasivos_deuda_ch',
            'pasivos_deuda_pce',
            'pasivos_deuda_tdc',
            'pasivos_deuda_otros',
            'pasivos_gastos',
        ];
        $total = 0.0;
        foreach ($keys as $key) {
            $total += $this->sumValorDeclarado($this->borrador[$key] ?? []);
        }
        return $total;
    }

    // ─── Raw item accessors (for Ver Declaración Reverso) ───

    /**
     * Returns raw bienes inmuebles items from borrador_json.
     * @return array<int, array>
     */
    public function getBienesInmueblesItems(): array
    {
        return $this->borrador['bienes_inmuebles'] ?? [];
    }

    /**
     * Returns all bienes muebles merged from all sub-categories.
     * Each item keeps its original structure + 'categoria' key.
     * @return array<int, array>
     */
    public function getBienesMueblesItems(): array
    {
        $categorias = [
            'bienes_muebles_banco'                  => 'Banco',
            'bienes_muebles_seguro'                 => 'Seguro',
            'bienes_muebles_transporte'             => 'Transporte',
            'bienes_muebles_acciones'               => 'Acciones',
            'bienes_muebles_bonos'                  => 'Bonos',
            'bienes_muebles_caja_ahorro'            => 'Caja de Ahorro',
            'bienes_muebles_cuentas_efectos'        => 'Cuentas/Efectos',
            'bienes_muebles_opciones_compra'        => 'Opciones de Compra',
            'bienes_muebles_plantaciones'           => 'Plantaciones',
            'bienes_muebles_prestaciones_sociales'  => 'Prestaciones Sociales',
            'bienes_muebles_semovientes'            => 'Semovientes',
            'bienes_muebles_otros'                  => 'Otros',
        ];
        $items = [];
        foreach ($categorias as $key => $label) {
            foreach (($this->borrador[$key] ?? []) as $item) {
                $item['categoria'] = $label;
                $items[] = $item;
            }
        }
        return $items;
    }

    /**
     * Returns all pasivos merged from all sub-categories.
     * @return array<int, array>
     */
    public function getPasivosItems(): array
    {
        $categorias = [
            'pasivos_deuda_ch'    => 'Créditos Hipotecarios',
            'pasivos_deuda_pce'   => 'Préstamos/Créditos',
            'pasivos_deuda_tdc'   => 'Tarjetas de Crédito',
            'pasivos_deuda_otros' => 'Otros',
            'pasivos_gastos'      => 'Gastos',
        ];
        $items = [];
        foreach ($categorias as $key => $label) {
            foreach (($this->borrador[$key] ?? []) as $item) {
                $item['categoria'] = $label;
                $items[] = $item;
            }
        }
        return $items;
    }

    /**
     * Returns desgravámenes items (vivienda principal + seguros desgravables).
     * @return array<int, array>
     */
    public function getDesgravamenesItems(): array
    {
        $items = [];
        foreach (($this->borrador['bienes_inmuebles'] ?? []) as $inm) {
            if (($inm['vivienda_principal'] ?? 'false') === 'true') {
                $inm['categoria'] = 'Vivienda Principal';
                $items[] = $inm;
            }
        }
        foreach (($this->borrador['bienes_muebles_seguro'] ?? []) as $seg) {
            $tipo = $seg['tipo_bien'] ?? '';
            if ($tipo === '08' || $tipo === '09') {
                $seg['categoria'] = 'Seguro';
                $items[] = $seg;
            }
        }
        return $items;
    }

    /**
     * Returns exenciones items.
     * @return array<int, array>
     */
    public function getExencionesItems(): array
    {
        return $this->borrador['exenciones'] ?? [];
    }

    /**
     * Returns exoneraciones items.
     * @return array<int, array>
     */
    public function getExoneracionesItems(): array
    {
        return $this->borrador['exoneraciones'] ?? [];
    }

    /**
     * Returns the current date formatted as DD/MM/YYYY (for "Fecha de Declaración").
     */
    public function getFechaDeclaracion(): string
    {
        return date('d/m/Y');
    }

    /**
     * Herederos with full detail for resumen table.
     * Returns: [ [nombre, cedula, parentesco, grado, premuerto], ... ]
     */
    public function getHerederosDetalle(): array
    {
        $herederos = [];

        // New format: herederos.items[]
        if (isset($this->borrador['herederos']['items'])) {
            foreach ($this->borrador['herederos']['items'] as $h) {
                $tipoCed = $h['tipo_cedula'] ?? 'V';
                $cedula  = $h['cedula'] ?? '';
                $herederos[] = [
                    'nombre'        => strtoupper(trim(($h['apellidos'] ?? '') . ' ' . ($h['nombres'] ?? ''))),
                    'cedula'        => $tipoCed . $cedula,
                    'parentesco'    => strtoupper($h['parentescoText'] ?? $h['parentesco_text'] ?? 'HEREDERO'),
                    'parentesco_id' => (int) ($h['parentesco_id'] ?? 0),
                    'grado'         => $h['grado'] ?? '1',
                    'premuerto'     => in_array(strtolower($h['premuerto'] ?? ''), ['true', 'si', '1'], true) ? 'SI' : 'NO',
                ];
            }
            return $herederos;
        }

        // Legacy format: filter relaciones
        foreach ($this->getRelaciones() as $rel) {
            if (isset($rel['parentescoText']) && strtoupper($rel['parentescoText']) !== 'REPRESENTANTE DE LA SUCESION') {
                $herederos[] = [
                    'nombre'        => strtoupper(trim(($rel['apellido'] ?? '') . ' ' . ($rel['nombre'] ?? ''))),
                    'cedula'        => $rel['idDocumento'] ?? (($rel['tipodocumento'] ?? '') . ($rel['cedula'] ?? '')),
                    'parentesco'    => strtoupper($rel['parentescoText'] ?? 'HEREDERO'),
                    'parentesco_id' => (int) ($rel['parentesco_id'] ?? 0),
                    'grado'         => $rel['grado'] ?? '1',
                    'premuerto'     => in_array(strtolower($rel['premuerto'] ?? ''), ['true', 'si', '1'], true) ? 'SI' : 'NO',
                ];
            }
        }
        return $herederos;
    }

    /**
     * Converts ISO date (YYYY-MM-DD) to display format (DD/MM/YYYY).
     */
    private function isoToDisplay(string $isoDate): string
    {
        if (empty($isoDate)) return '';
        $parts = explode('-', $isoDate);
        if (count($parts) !== 3) return $isoDate;
        return $parts[2] . '/' . $parts[1] . '/' . $parts[0];
    }
}
