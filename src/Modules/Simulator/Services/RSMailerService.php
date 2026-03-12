<?php

declare(strict_types=1);

namespace App\Modules\Simulator\Services;

use App\Core\Mailer;

/**
 * RSMailerService — Envío de correos de validación R.S.
 *
 * Construye y envía correos HTML con los resultados de la validación
 * realizada por RSValidator: discrepancias (error) o éxito (RIF generado).
 *
 * El destinatario es el correo del estudiante (pasado dinámicamente).
 */
class RSMailerService
{
    /**
     * Envía un correo con las discrepancias encontradas por el validador.
     *
     * @param string $destinatario      Correo del estudiante
     * @param array  $errores           Array de errores agrupados por sección (salida de RSValidator)
     * @param string $nombreEstudiante  Nombre del estudiante que realizó el intento
     * @param int    $intentoId         ID del intento validado
     * @param string $casoTitulo        Título del caso asignado
     * @return bool  true si el correo se envió correctamente
     */
    public function enviarDiscrepancias(
        string $destinatario,
        array  $errores,
        string $nombreEstudiante,
        int    $intentoId,
        string $casoTitulo = ''
    ): bool {
        if (empty($errores)) {
            return false;
        }

        $subject = "Discrepancias en R.S. — Intento #{$intentoId}";
        $body = $this->buildHtmlDiscrepancias($errores, $nombreEstudiante, $intentoId, $casoTitulo);

        return Mailer::send($destinatario, $subject, $body);
    }

    /**
     * Envía un correo de éxito con el RIF Sucesoral generado.
     *
     * @param string $destinatario      Correo del estudiante
     * @param string $nombreEstudiante  Nombre del estudiante
     * @param int    $intentoId         ID del intento validado
     * @param string $rifSucesoral      RIF Sucesoral generado (ej: J-12345678-0)
     * @param string $casoTitulo        Título del caso asignado
     * @return bool  true si el correo se envió correctamente
     */
    public function enviarExito(
        string $destinatario,
        string $nombreEstudiante,
        int    $intentoId,
        string $rifSucesoral,
        string $casoTitulo = ''
    ): bool {
        $subject = "✅ RIF Sucesoral Generado — Caso: {$casoTitulo}, Intento: #{$intentoId}";
        $body = $this->buildHtmlExito($nombreEstudiante, $intentoId, $rifSucesoral, $casoTitulo);

        return Mailer::send($destinatario, $subject, $body);
    }

    // ════════════════════════════════════════════════════════
    //  HTML: DISCREPANCIAS (ERROR)
    // ════════════════════════════════════════════════════════

    private function buildHtmlDiscrepancias(
        array  $errores,
        string $nombreEstudiante,
        int    $intentoId,
        string $casoTitulo
    ): string {
        $fecha = date('d/m/Y H:i');

        $html = '
        <div style="font-family: Verdana, Arial, sans-serif; max-width: 640px; margin: 0 auto; color: #333;">
            <div style="background: #003366; color: #fff; padding: 16px 24px; border-radius: 6px 6px 0 0;">
                <h2 style="margin: 0; font-size: 18px;">⚠ Discrepancias detectadas en validación R.S.</h2>
            </div>

            <div style="padding: 20px 24px; background: #f9f9f9; border: 1px solid #ddd; border-top: none; border-radius: 0 0 6px 6px;">
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 16px; font-size: 13px;">
                    <tr>
                        <td style="padding: 6px 0; font-weight: bold; width: 140px;">Estudiante:</td>
                        <td style="padding: 6px 0;">' . htmlspecialchars($nombreEstudiante, ENT_QUOTES, 'UTF-8') . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; font-weight: bold;">Intento:</td>
                        <td style="padding: 6px 0;">#' . $intentoId . '</td>
                    </tr>';

        if ($casoTitulo) {
            $html .= '
                    <tr>
                        <td style="padding: 6px 0; font-weight: bold;">Caso:</td>
                        <td style="padding: 6px 0;">' . htmlspecialchars($casoTitulo, ENT_QUOTES, 'UTF-8') . '</td>
                    </tr>';
        }

        $html .= '
                    <tr>
                        <td style="padding: 6px 0; font-weight: bold;">Fecha:</td>
                        <td style="padding: 6px 0;">' . $fecha . '</td>
                    </tr>
                </table>';

        // Secciones de errores
        $secciones = [
            'causante'   => ['titulo' => 'Datos del Causante',   'icono' => '👤'],
            'relaciones' => ['titulo' => 'Relaciones',           'icono' => '👥'],
            'direcciones' => ['titulo' => 'Direcciones',         'icono' => '📍'],
            'general'    => ['titulo' => 'General',              'icono' => '🔍'],
        ];

        foreach ($secciones as $clave => $seccion) {
            if (empty($errores[$clave])) continue;

            $html .= '
                <div style="margin-bottom: 16px;">
                    <h3 style="font-size: 14px; color: #003366; margin: 0 0 8px 0; border-bottom: 2px solid #003366; padding-bottom: 4px;">
                        ' . $seccion['icono'] . ' ' . $seccion['titulo'] . '
                    </h3>
                    <ul style="margin: 0; padding-left: 20px; font-size: 12px; line-height: 1.8;">';

            foreach ($errores[$clave] as $error) {
                $html .= '
                        <li style="color: #cc0000;">' . htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . '</li>';
            }

            $html .= '
                    </ul>
                </div>';
        }

        $totalErrores = 0;
        foreach ($errores as $arr) {
            $totalErrores += count($arr);
        }

        $html .= '
                <div style="background: #fff3cd; border: 1px solid #ffc107; padding: 10px 14px; border-radius: 4px; font-size: 12px; margin-top: 12px;">
                    <strong>Total de discrepancias:</strong> ' . $totalErrores . '
                </div>

                <p style="font-size: 11px; color: #888; margin-top: 16px; text-align: center;">
                    Este correo fue generado automáticamente por el Simulador SENIAT.
                </p>
            </div>
        </div>';

        return $html;
    }

    // ════════════════════════════════════════════════════════
    //  HTML: ÉXITO (RIF GENERADO)
    // ════════════════════════════════════════════════════════

    private function buildHtmlExito(
        string $nombreEstudiante,
        int    $intentoId,
        string $rifSucesoral,
        string $casoTitulo
    ): string {
        $fecha = date('d/m/Y H:i');

        $html = '
        <div style="font-family: Verdana, Arial, sans-serif; max-width: 640px; margin: 0 auto; color: #333;">
            <div style="background: #166534; color: #fff; padding: 16px 24px; border-radius: 6px 6px 0 0;">
                <h2 style="margin: 0; font-size: 18px;">✅ RIF Sucesoral Generado Exitosamente</h2>
            </div>

            <div style="padding: 20px 24px; background: #f9f9f9; border: 1px solid #ddd; border-top: none; border-radius: 0 0 6px 6px;">
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 16px; font-size: 13px;">
                    <tr>
                        <td style="padding: 6px 0; font-weight: bold; width: 140px;">Estudiante:</td>
                        <td style="padding: 6px 0;">' . htmlspecialchars($nombreEstudiante, ENT_QUOTES, 'UTF-8') . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; font-weight: bold;">Intento:</td>
                        <td style="padding: 6px 0;">#' . $intentoId . '</td>
                    </tr>';

        if ($casoTitulo) {
            $html .= '
                    <tr>
                        <td style="padding: 6px 0; font-weight: bold;">Caso:</td>
                        <td style="padding: 6px 0;">' . htmlspecialchars($casoTitulo, ENT_QUOTES, 'UTF-8') . '</td>
                    </tr>';
        }

        $html .= '
                    <tr>
                        <td style="padding: 6px 0; font-weight: bold;">Fecha:</td>
                        <td style="padding: 6px 0;">' . $fecha . '</td>
                    </tr>
                </table>

                <div style="background: #dcfce7; border: 1px solid #86efac; padding: 16px 20px; border-radius: 8px; text-align: center; margin-bottom: 16px;">
                    <p style="margin: 0 0 8px; font-size: 13px; color: #166534;">Su RIF Sucesoral ha sido generado:</p>
                    <p style="margin: 0; font-size: 28px; font-weight: bold; color: #166534; letter-spacing: 2px;">'
                        . htmlspecialchars($rifSucesoral, ENT_QUOTES, 'UTF-8') .
                    '</p>
                </div>

                <div style="margin-bottom: 16px; font-size: 13px; line-height: 1.7;">
                    <h3 style="font-size: 14px; color: #003366; margin: 0 0 8px 0; border-bottom: 2px solid #003366; padding-bottom: 4px;">
                        📋 Instrucciones
                    </h3>
                    <ol style="margin: 0; padding-left: 20px;">
                        <li style="margin-bottom: 6px;">Guarde este RIF Sucesoral para sus registros.</li>
                        <li style="margin-bottom: 6px;">En el proceso real, debe presentar la planilla impresa ante la unidad del SENIAT correspondiente a su domicilio fiscal.</li>
                        <li style="margin-bottom: 6px;">Para fines educativos, este RIF ha sido asignado automáticamente a su caso en el simulador.</li>
                    </ol>
                </div>

                <div style="background: #eff6ff; border: 1px solid #93c5fd; padding: 10px 14px; border-radius: 4px; font-size: 12px;">
                    <strong>📌 Recuerde:</strong> En el proceso real ante el SENIAT, se requiere un correo electrónico exclusivo para el RIF Sucesoral.
                    Se recomienda crear uno para uso exclusivo de la sucesión.
                </div>

                <p style="font-size: 11px; color: #888; margin-top: 16px; text-align: center;">
                    Este correo fue generado automáticamente por el Simulador SENIAT.
                </p>
            </div>
        </div>';

        return $html;
    }
}
