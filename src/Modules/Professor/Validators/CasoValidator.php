<?php
declare(strict_types=1);

namespace App\Modules\Professor\Validators;

/**
 * Validador server-side para el payload de Crear/Editar Caso Sucesoral.
 * Espeja las reglas del frontend (modal.js, prorroga.js, direccion.js).
 *
 * Modos:
 *   - 'Borrador': solo requiere titulo
 *   - 'Publicar': validación completa de todas las secciones
 */
class CasoValidator
{
    private array $errors = [];

    /**
     * Valida el payload completo.
     * @return string[] Array de mensajes de error (vacío = válido)
     */
    public function validate(array $data, string $modo = 'Borrador'): array
    {
        $this->errors = [];

        // ── Siempre requerido ──
        $this->validateCaso($data['caso'] ?? []);

        if ($modo === 'Publicar') {
            $this->validateCausante($data['causante'] ?? [], $data['caso']['tipo_sucesion'] ?? '');
            $this->validateDatosFiscales($data['datos_fiscales_causante'] ?? []);
            $this->validateActaDefuncion($data['acta_defuncion'] ?? [], $data['caso']['tipo_sucesion'] ?? '');
            $this->validateDomicilio($data['domicilio_causante'] ?? []);
            $this->validateDirecciones($data['direcciones_causante'] ?? []);
            $this->validateRepresentante($data['representante'] ?? []);
            $this->validateHerencia($data['herencia'] ?? []);
            $this->validateHerederos($data['herederos'] ?? []);
            $this->validateHerederosPremuertos($data['herederos_premuertos'] ?? []);
            $this->validateBienesInmuebles($data['bienes_inmuebles'] ?? []);
            $this->validateBienesMuebles($data['bienes_muebles'] ?? []);
            $this->validatePasivosDeuda($data['pasivos_deuda'] ?? []);
            $this->validatePasivosGastos($data['pasivos_gastos'] ?? []);
            $this->validateExenciones($data['exenciones'] ?? []);
            $this->validateExoneraciones($data['exoneraciones'] ?? []);
            $this->validateProrrogas($data['prorrogas'] ?? []);
            $this->validateConfig($data['config'] ?? []);
        }

        return $this->errors;
    }

    // ====================================================================
    // Sección: Caso
    // ====================================================================
    private function validateCaso(array $caso): void
    {
        if (empty(trim($caso['titulo'] ?? ''))) {
            $this->errors[] = 'El Título del Caso es obligatorio.';
        }
        $validEstados = ['Borrador', 'Publicado'];
        if (!empty($caso['estado']) && !in_array($caso['estado'], $validEstados)) {
            $this->errors[] = 'Estado del caso inválido.';
        }
        $validSucesion = ['Con Cédula', 'Sin Cédula', 'Con_Cedula', 'Sin_Cedula'];
        if (!empty($caso['tipo_sucesion']) && !in_array($caso['tipo_sucesion'], $validSucesion)) {
            $this->errors[] = 'Tipo de sucesión inválido.';
        }
    }

    // ====================================================================
    // Sección: Causante
    // ====================================================================
    private function validateCausante(array $c, string $tipoSucesion): void
    {
        if (empty($c['nombres']))
            $this->errors[] = 'Causante: Nombres es obligatorio.';
        if (empty($c['apellidos']))
            $this->errors[] = 'Causante: Apellidos es obligatorio.';
        if (empty($c['sexo']) || !in_array($c['sexo'], ['M', 'F'])) {
            $this->errors[] = 'Causante: Sexo debe ser M o F.';
        }
        $validEC = ['Soltero', 'Casado', 'Divorciado', 'Viudo', 'Union_Estable'];
        if (empty($c['estado_civil']) || !in_array($c['estado_civil'], $validEC)) {
            $this->errors[] = 'Causante: Estado civil inválido.';
        }
        if (empty($c['fecha_nacimiento']) || !$this->isValidDate($c['fecha_nacimiento'])) {
            $this->errors[] = 'Causante: Fecha de nacimiento inválida.';
        }

        $esCedula = in_array($tipoSucesion, ['Con Cédula', 'Con_Cedula']);
        if ($esCedula) {
            if (empty($c['cedula'])) {
                $this->errors[] = 'Causante: Cédula es obligatoria cuando la sucesión es Con Cédula.';
            } elseif (!preg_match('/^\d{6,10}$/', $c['cedula'])) {
                $this->errors[] = 'Causante: La cédula debe contener solo números (6-10 dígitos).';
            }
        }
    }

    // ====================================================================
    // Sección: Datos Fiscales del Causante
    // ====================================================================
    private function validateDatosFiscales(array $df): void
    {
        if (!isset($df['domiciliado_pais']) || !in_array((string) $df['domiciliado_pais'], ['0', '1'])) {
            $this->errors[] = 'Datos fiscales: Domiciliado en el país es obligatorio.';
        }
        if (empty($df['fecha_cierre_fiscal']) || !$this->isValidDate($df['fecha_cierre_fiscal'])) {
            $this->errors[] = 'Datos fiscales: Fecha de cierre fiscal inválida.';
        }
    }

    // ====================================================================
    // Sección: Acta de Defunción
    // ====================================================================
    private function validateActaDefuncion(array $acta, string $tipoSucesion): void
    {
        $sinCedula = in_array($tipoSucesion, ['Sin Cédula', 'Sin_Cedula']);

        if ($sinCedula) {
            if (empty($acta['numero_acta']))
                $this->errors[] = 'Acta de defunción: Número de acta es obligatorio.';
            if (empty($acta['year_acta']) || (int) $acta['year_acta'] < 1900) {
                $this->errors[] = 'Acta de defunción: Año del acta inválido.';
            }
            if (empty($acta['parroquia']))
                $this->errors[] = 'Acta de defunción: Parroquia es obligatoria.';
        }
    }

    // ====================================================================
    // Sección: Domicilio del Causante
    // ====================================================================
    private function validateDomicilio(array $d): void
    {
        $required = [
            'tipo_vialidad',
            'tipo_inmueble',
            'nombre_vialidad',
            'nro_inmueble',
            'tipo_nivel',
            'tipo_sector',
            'nro_nivel',
            'nombre_sector',
            'estado',
            'municipio',
            'parroquia',
            'ciudad'
        ];
        foreach ($required as $field) {
            if (empty($d[$field])) {
                $this->errors[] = "Domicilio causante: El campo «{$field}» es obligatorio.";
                break; // Un solo error genérico para no saturar
            }
        }
    }

    // ====================================================================
    // Sección: Direcciones adicionales del Causante (array)
    // ====================================================================
    private function validateDirecciones(array $direcciones): void
    {
        foreach ($direcciones as $i => $dir) {
            $n = $i + 1;
            $required = [
                'tipo_direccion',
                'tipo_vialidad',
                'tipo_inmueble',
                'nombre_vialidad',
                'nro_inmueble',
                'tipo_nivel',
                'tipo_sector',
                'nro_nivel',
                'nombre_sector',
                'estado',
                'municipio',
                'parroquia',
                'ciudad'
            ];
            foreach ($required as $field) {
                if (empty($dir[$field])) {
                    $this->errors[] = "Dirección #{$n}: Complete todos los campos de ubicación.";
                    break;
                }
            }
        }
    }

    // ====================================================================
    // Sección: Representante
    // ====================================================================
    private function validateRepresentante(array $r): void
    {
        if (empty($r['nombres']))
            $this->errors[] = 'Representante: Nombres es obligatorio.';
        if (empty($r['apellidos']))
            $this->errors[] = 'Representante: Apellidos es obligatorio.';
        if (empty($r['cedula']) && empty($r['pasaporte'])) {
            $this->errors[] = 'Representante: Debe ingresar Cédula o Pasaporte.';
        }
        if (!empty($r['cedula']) && !preg_match('/^\d{6,10}$/', $r['cedula'])) {
            $this->errors[] = 'Representante: La cédula debe contener solo números (6-10 dígitos).';
        }
    }

    // ====================================================================
    // Sección: Herencia
    // ====================================================================
    private function validateHerencia(array $h): void
    {
        if (empty($h['tipos']) || !is_array($h['tipos']) || count($h['tipos']) === 0) {
            $this->errors[] = 'Debe seleccionar al menos un tipo de herencia.';
        }
    }

    // ====================================================================
    // Sección: Herederos (array)
    // ====================================================================
    private function validateHerederos(array $herederos): void
    {
        foreach ($herederos as $i => $h) {
            $n = $i + 1;
            if (empty($h['cedula']) && empty($h['pasaporte'])) {
                $this->errors[] = "Heredero #{$n}: Debe ingresar Cédula o Pasaporte.";
            }
            if (!empty($h['cedula']) && !preg_match('/^\d{6,10}$/', $h['cedula'])) {
                $this->errors[] = "Heredero #{$n}: Cédula inválida (6-10 dígitos numéricos).";
            }
            $reqFields = ['nombres', 'apellidos', 'fecha_nacimiento', 'sexo', 'estado_civil', 'parentesco_id'];
            foreach ($reqFields as $f) {
                if (empty($h[$f])) {
                    $this->errors[] = "Heredero #{$n}: Complete todos los campos obligatorios.";
                    break;
                }
            }
            if (($h['premuerto'] ?? '') === 'SI' && empty($h['fecha_fallecimiento'])) {
                $this->errors[] = "Heredero #{$n}: Fecha de fallecimiento es obligatoria para premuertos.";
            }
        }
    }

    // ====================================================================
    // Sección: Herederos Premuertos (array)
    // ====================================================================
    private function validateHerederosPremuertos(array $premuertos): void
    {
        foreach ($premuertos as $i => $h) {
            $n = $i + 1;
            if (empty($h['cedula']) && empty($h['pasaporte'])) {
                $this->errors[] = "Heredero premuerto #{$n}: Debe ingresar Cédula o Pasaporte.";
            }
            $reqFields = ['nombres', 'apellidos', 'fecha_nacimiento', 'sexo', 'estado_civil', 'parentesco_id', 'premuerto_padre_id'];
            foreach ($reqFields as $f) {
                if (empty($h[$f])) {
                    $this->errors[] = "Heredero premuerto #{$n}: Complete todos los campos (incluyendo a quién representa).";
                    break;
                }
            }
        }
    }

    // ====================================================================
    // Sección: Bienes Inmuebles (array)
    // ====================================================================
    private function validateBienesInmuebles(array $inmuebles): void
    {
        foreach ($inmuebles as $i => $b) {
            $n = $i + 1;
            if (empty($b['tipo_bien_inmueble_id']) || (is_array($b['tipo_bien_inmueble_id']) && count($b['tipo_bien_inmueble_id']) === 0)) {
                $this->errors[] = "Inmueble #{$n}: Debe seleccionar al menos un Tipo de Bien.";
            }
            $pct = (float) ($b['porcentaje'] ?? 0);
            if ($pct <= 0 || $pct > 100) {
                $this->errors[] = "Inmueble #{$n}: Porcentaje inválido.";
            }
            $reqFields = [
                'descripcion',
                'linderos',
                'superficie_construida',
                'superficie_no_construida',
                'area_superficie',
                'direccion',
                'oficina_registro',
                'nro_registro',
                'libro',
                'protocolo',
                'fecha_registro',
                'trimestre',
                'asiento_registral',
                'matricula',
                'folio_real_anio',
                'valor_original',
                'valor_declarado'
            ];
            foreach ($reqFields as $f) {
                if (empty($b[$f]) && $b[$f] !== '0' && $b[$f] !== 0) {
                    $this->errors[] = "Inmueble #{$n}: Complete todos los campos obligatorios (falta «{$f}»).";
                    break;
                }
            }
            if ((float) ($b['valor_declarado'] ?? 0) <= 0) {
                $this->errors[] = "Inmueble #{$n}: El Valor Declarado debe ser mayor a 0.";
            }
            if (($b['bien_litigioso'] ?? '') === 'Si') {
                $litFields = ['numero_expediente', 'tribunal_causa', 'partes_juicio', 'estado_juicio'];
                foreach ($litFields as $lf) {
                    if (empty($b[$lf])) {
                        $this->errors[] = "Inmueble #{$n}: Debe completar todos los detalles del Bien Litigioso.";
                        break;
                    }
                }
            }
        }
    }

    // ====================================================================
    // Sección: Bienes Muebles (object con keys de categoría, cada una array)
    // ====================================================================
    private function validateBienesMuebles($muebles): void
    {
        if (!is_array($muebles) && !is_object($muebles))
            return;

        foreach ($muebles as $catId => $items) {
            if (!is_array($items))
                continue;
            foreach ($items as $i => $b) {
                $n = $i + 1;
                $label = "Mueble (cat:{$catId}) #{$n}";

                if ((float) ($b['valor_declarado'] ?? 0) <= 0) {
                    $this->errors[] = "{$label}: El Valor Declarado debe ser mayor a 0.";
                }
                if (($b['bien_litigioso'] ?? '') === 'Si') {
                    $litFields = ['numero_expediente', 'tribunal_causa', 'partes_juicio', 'estado_juicio'];
                    foreach ($litFields as $lf) {
                        if (empty($b[$lf])) {
                            $this->errors[] = "{$label}: Debe completar los detalles del Bien Litigioso.";
                            break;
                        }
                    }
                }
            }
        }
    }

    // ====================================================================
    // Sección: Pasivos Deuda (array)
    // ====================================================================
    private function validatePasivosDeuda(array $deudas): void
    {
        foreach ($deudas as $i => $d) {
            $n = $i + 1;
            if (empty($d['tipo_pasivo_deuda_id'])) {
                $this->errors[] = "Deuda #{$n}: Debe seleccionar un Tipo de Deuda.";
            }
            if ((float) ($d['valor_declarado'] ?? 0) <= 0) {
                $this->errors[] = "Deuda #{$n}: El Valor Declarado debe ser mayor a 0.";
            }
            $pct = (float) ($d['porcentaje'] ?? 100);
            if ($pct <= 0 || $pct > 100) {
                $this->errors[] = "Deuda #{$n}: Porcentaje inválido.";
            }
        }
    }

    // ====================================================================
    // Sección: Pasivos Gastos (array)
    // ====================================================================
    private function validatePasivosGastos(array $gastos): void
    {
        foreach ($gastos as $i => $g) {
            $n = $i + 1;
            if (empty($g['tipo_pasivo_gasto_id'])) {
                $this->errors[] = "Gasto #{$n}: Debe seleccionar un Tipo de Gasto.";
            }
            if ((float) ($g['valor_declarado'] ?? 0) <= 0) {
                $this->errors[] = "Gasto #{$n}: El Valor Declarado debe ser mayor a 0.";
            }
        }
    }

    // ====================================================================
    // Sección: Exenciones (array)
    // ====================================================================
    private function validateExenciones(array $exenciones): void
    {
        foreach ($exenciones as $i => $e) {
            $n = $i + 1;
            if (empty($e['tipo_exencion'])) {
                $this->errors[] = "Exención #{$n}: Tipo de exención es obligatorio.";
            }
            if ((float) ($e['valor_declarado'] ?? 0) <= 0) {
                $this->errors[] = "Exención #{$n}: El Valor Declarado debe ser mayor a 0.";
            }
        }
    }

    // ====================================================================
    // Sección: Exoneraciones (array)
    // ====================================================================
    private function validateExoneraciones(array $exoneraciones): void
    {
        foreach ($exoneraciones as $i => $e) {
            $n = $i + 1;
            if (empty($e['tipo_exoneracion'])) {
                $this->errors[] = "Exoneración #{$n}: Tipo de exoneración es obligatorio.";
            }
            if ((float) ($e['valor_declarado'] ?? 0) <= 0) {
                $this->errors[] = "Exoneración #{$n}: El Valor Declarado debe ser mayor a 0.";
            }
        }
    }

    // ====================================================================
    // Sección: Prórrogas (array)
    // ====================================================================
    private function validateProrrogas(array $prorrogas): void
    {
        foreach ($prorrogas as $i => $p) {
            $n = $i + 1;
            $reqFields = ['fecha_solicitud', 'nro_resolucion', 'fecha_resolucion', 'plazo_dias', 'fecha_vencimiento'];
            foreach ($reqFields as $f) {
                if (empty($p[$f])) {
                    $this->errors[] = "Prórroga #{$n}: Complete todos los campos.";
                    break;
                }
            }
            if (!empty($p['plazo_dias']) && (int) $p['plazo_dias'] < 1) {
                $this->errors[] = "Prórroga #{$n}: El plazo debe ser al menos 1 día.";
            }
        }
    }

    // ====================================================================
    // Sección: Config
    // ====================================================================
    private function validateConfig(array $c): void
    {
        $validModalidad = ['Practica', 'Evaluacion'];
        if (empty($c['modalidad']) || !in_array($c['modalidad'], $validModalidad)) {
            $this->errors[] = 'Configuración: Debe seleccionar una modalidad.';
        }
        if (($c['modalidad'] ?? '') === 'Evaluacion') {
            if (empty($c['fecha_limite'])) {
                $this->errors[] = 'Configuración: Fecha límite es obligatoria para evaluaciones.';
            }
        }
        $validAsignacion = ['Seccion', 'Estudiantes'];
        if (!empty($c['tipo_asignacion']) && !in_array($c['tipo_asignacion'], $validAsignacion)) {
            $this->errors[] = 'Configuración: Tipo de asignación inválido.';
        }
    }

    // ====================================================================
    // Helpers
    // ====================================================================
    private function isValidDate(string $date): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
}
