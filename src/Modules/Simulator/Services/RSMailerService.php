<?php

declare(strict_types=1);

namespace App\Modules\Simulator\Services;

use App\Core\MailQueueService;

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
        // Desactivado en Fase 2: este método dejará de enviar correos.
        // Se conserva la firma para no romper código que lo invoca.
        return true;
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

        return MailQueueService::send($destinatario, $subject, $body, 'rif_sucesoral', null, $intentoId);
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

        $html = "
        <div style='font-family: \"Plus Jakarta Sans\", sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 10px;'>
            <h2 style='color: #004085; text-align: center;'>⚠ Discrepancias Detectadas</h2>
            <p style='text-align: center; color: #555; margin-bottom: 20px;'>Se encontraron diferencias en su validación de RIF Sucesoral.</p>

            <div style='background-color: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-size: 14px;'>
                <strong>Estudiante:</strong> " . htmlspecialchars($nombreEstudiante, ENT_QUOTES, 'UTF-8') . "<br>
                <strong>Intento:</strong> #{$intentoId}<br>";

        if ($casoTitulo) {
            $html .= "                <strong>Caso:</strong> " . htmlspecialchars($casoTitulo, ENT_QUOTES, 'UTF-8') . "<br>";
        }

        $html .= "                <strong>Fecha:</strong> {$fecha}
            </div>";

        // Secciones de errores
        $secciones = [
            'causante'    => ['titulo' => 'Datos del Causante',   'icono' => '👤'],
            'relaciones'  => ['titulo' => 'Relaciones',           'icono' => '👥'],
            'direcciones' => ['titulo' => 'Direcciones',          'icono' => '📍'],
            'general'     => ['titulo' => 'General',              'icono' => '🔍'],
        ];

        foreach ($secciones as $clave => $seccion) {
            if (empty($errores[$clave])) continue;

            $html .= "
            <div style='margin-bottom: 16px;'>
                <h3 style='font-size: 14px; color: #004085; margin: 0 0 8px 0; border-bottom: 1px solid #e0e0e0; padding-bottom: 6px;'>
                    {$seccion['icono']} {$seccion['titulo']}
                </h3>
                <ul style='margin: 0; padding-left: 20px; font-size: 13px; line-height: 1.8;'>";

            foreach ($errores[$clave] as $error) {
                $html .= "
                    <li style='color: #dc3545;'>" . htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . "</li>";
            }

            $html .= "
                </ul>
            </div>";
        }

        $totalErrores = 0;
        foreach ($errores as $arr) {
            $totalErrores += count($arr);
        }

        $html .= "
            <div style='background-color: #fff3cd; padding: 12px 16px; border-radius: 8px; font-size: 13px; margin-bottom: 16px;'>
                <strong>Total de discrepancias:</strong> {$totalErrores}
            </div>

            <p style='color: #777; font-size: 12px; text-align: center;'>Este correo fue generado automáticamente por el Simulador SENIAT.</p>
        </div>";

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

        $html = "
        <div style='font-family: \"Plus Jakarta Sans\", sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 10px;'>
            <h2 style='color: #004085; text-align: center;'>✅ RIF Sucesoral Generado</h2>
            <p style='text-align: center; color: #555; margin-bottom: 20px;'>Su validación fue exitosa y se ha generado el RIF Sucesoral.</p>

            <div style='background-color: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-size: 14px;'>
                <strong>Estudiante:</strong> " . htmlspecialchars($nombreEstudiante, ENT_QUOTES, 'UTF-8') . "<br>
                <strong>Intento:</strong> #{$intentoId}<br>";

        if ($casoTitulo) {
            $html .= "                <strong>Caso:</strong> " . htmlspecialchars($casoTitulo, ENT_QUOTES, 'UTF-8') . "<br>";
        }

        $html .= "                <strong>Fecha:</strong> {$fecha}
            </div>

            <div style='background-color: #f8f9fa; padding: 15px; text-align: center; margin: 20px 0;'>
                <p style='margin: 0 0 8px; font-size: 13px; color: #555;'>Su RIF Sucesoral es:</p>
                <strong style='font-size: 32px; letter-spacing: 5px; color: #0d6efd;'>" . htmlspecialchars($rifSucesoral, ENT_QUOTES, 'UTF-8') . "</strong>
            </div>

            <div style='margin-bottom: 16px; font-size: 13px; line-height: 1.7;'>
                <h3 style='font-size: 14px; color: #004085; margin: 0 0 8px 0; border-bottom: 1px solid #e0e0e0; padding-bottom: 6px;'>
                    📋 Instrucciones
                </h3>
                <ol style='margin: 0; padding-left: 20px; color: #555;'>
                    <li style='margin-bottom: 6px;'>Guarde este RIF Sucesoral para sus registros.</li>
                    <li style='margin-bottom: 6px;'>En el proceso real, debe presentar la planilla impresa ante la unidad del SENIAT correspondiente a su domicilio fiscal.</li>
                    <li style='margin-bottom: 6px;'>Para fines educativos, este RIF ha sido asignado automáticamente a su caso en el simulador.</li>
                </ol>
            </div>

            <div style='background-color: #f8f9fa; padding: 12px 16px; border-radius: 8px; font-size: 13px; margin-bottom: 16px; color: #555;'>
                <strong>📌 Recuerde:</strong> En el proceso real ante el SENIAT, se requiere un correo electrónico exclusivo para el RIF Sucesoral.
                Se recomienda crear uno para uso exclusivo de la sucesión.
            </div>

            <p style='color: #777; font-size: 12px; text-align: center;'>Este correo fue generado automáticamente por el Simulador SENIAT.</p>
        </div>";

        return $html;
    }

    // ════════════════════════════════════════════════════════
    //  APROBACIÓN RIF (Evaluación — aprobado por profesor)
    // ════════════════════════════════════════════════════════

    /**
     * Envía correo al estudiante notificando que su RIF fue aprobado.
     *
     * @param string $destinatario      Correo del estudiante
     * @param string $nombreEstudiante  Nombre completo del estudiante
     * @param int    $intentoId         ID del intento
     * @param string $rifSucesoral      RIF Sucesoral generado
     * @param string $casoTitulo        Título del caso asignado
     * @return bool  true si el correo se envió/encoló correctamente
     */
    public function enviarAprobacionRif(
        string $destinatario,
        string $nombreEstudiante,
        int    $intentoId,
        string $rifSucesoral,
        string $casoTitulo = ''
    ): bool {
        $subject = "✅ RIF Sucesoral Aprobado — {$casoTitulo}";
        $body    = $this->buildHtmlAprobacionRif($nombreEstudiante, $intentoId, $rifSucesoral, $casoTitulo);

        return MailQueueService::send($destinatario, $subject, $body, 'rif_sucesoral', null, $intentoId);
    }

    /**
     * Construye HTML del correo de aprobación de RIF.
     * Usa encabezado gradient institucional como el correo de bienvenida.
     */
    private function buildHtmlAprobacionRif(
        string $nombreEstudiante,
        int    $intentoId,
        string $rifSucesoral,
        string $casoTitulo
    ): string {
        $n     = htmlspecialchars($nombreEstudiante, ENT_QUOTES, 'UTF-8');
        $rif   = htmlspecialchars($rifSucesoral, ENT_QUOTES, 'UTF-8');
        $caso  = htmlspecialchars($casoTitulo, ENT_QUOTES, 'UTF-8');
        $fecha = date('d/m/Y H:i');

        return "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 0;'>
            <div style='background: linear-gradient(135deg, #1a237e, #283593); color: white; padding: 30px; border-radius: 10px 10px 0 0; text-align: center;'>
                <h1 style='margin: 0; font-size: 24px;'>✅ RIF Sucesoral Aprobado</h1>
                <p style='margin: 10px 0 0; opacity: 0.9;'>Sistema Pedagógico de Declaración Sucesoral Simulada</p>
            </div>
            <div style='background: #ffffff; padding: 30px; border: 1px solid #e0e0e0; border-top: none; border-radius: 0 0 10px 10px;'>
                <p style='font-size: 16px;'>Estimado/a <strong>{$n}</strong>,</p>
                <p>Le informamos que su inscripción de RIF Sucesoral ha sido <strong style='color: #059669;'>aprobada</strong> por su profesor. A continuación encontrará los datos generados:</p>

                <div style='background: #f0fdf4; border: 2px solid #059669; border-radius: 12px; padding: 24px; margin: 24px 0; text-align: center;'>
                    <p style='margin: 0 0 8px; font-size: 13px; color: #555;'>Su RIF Sucesoral es:</p>
                    <strong style='font-size: 32px; letter-spacing: 5px; color: #059669;'>{$rif}</strong>
                </div>

                <div style='background: #f5f5f5; border-left: 4px solid #065f46; padding: 15px; margin: 20px 0; border-radius: 0 5px 5px 0;'>
                    <p style='margin: 5px 0;'><strong>📋 Caso:</strong> {$caso}</p>
                    <p style='margin: 5px 0;'><strong>🔢 Intento:</strong> #{$intentoId}</p>
                    <p style='margin: 5px 0;'><strong>📅 Fecha de aprobación:</strong> {$fecha}</p>
                </div>

                <div style='margin: 24px 0; font-size: 14px; line-height: 1.7;'>
                    <h3 style='font-size: 15px; color: #065f46; margin: 0 0 12px 0; border-bottom: 1px solid #e0e0e0; padding-bottom: 8px;'>
                        📋 Próximos pasos
                    </h3>
                    <ol style='margin: 0; padding-left: 20px; color: #444;'>
                        <li style='margin-bottom: 8px;'>Guarde este RIF Sucesoral en un lugar seguro.</li>
                        <li style='margin-bottom: 8px;'>Ingrese al simulador y diríjase a la sección de <strong>Registro de Contribuyente</strong> para inscribirse en los servicios de declaración sucesoral.</li>
                        <li style='margin-bottom: 8px;'>Una vez registrado, complete la <strong>Declaración Sucesoral</strong> (Forma DS-99032).</li>
                    </ol>
                </div>

                <div style='background: #fffbeb; border-left: 4px solid #f59e0b; padding: 15px; margin: 20px 0; border-radius: 0 5px 5px 0;'>
                    <p style='margin: 0; font-weight: bold; color: #92400e;'>📌 Nota sobre el proceso real</p>
                    <p style='margin: 10px 0 0; font-size: 13px; color: #555;'>En el proceso real ante el SENIAT, el contribuyente debe presentar la planilla impresa ante la Unidad/Sector/Gerencia Regional de Tributos Internos correspondiente a su domicilio fiscal para formalizar el trámite.</p>
                </div>

                <p style='color: #999; font-size: 12px; margin-top: 30px; border-top: 1px solid #eee; padding-top: 15px; text-align: center;'>
                    Este correo fue generado automáticamente por el SPDSS — Simulador SENIAT.
                </p>
            </div>
        </div>";
    }

    // ════════════════════════════════════════════════════════
    //  RECHAZO RIF (Evaluación — rechazado por profesor)
    // ════════════════════════════════════════════════════════

    /**
     * Envía correo al estudiante notificando que su RIF fue rechazado.
     *
     * @param string      $destinatario      Correo del estudiante
     * @param string      $nombreEstudiante  Nombre completo
     * @param int         $intentoId         ID del intento
     * @param string      $casoTitulo        Título del caso
     * @param string      $observacion       Motivo del rechazo (profesor)
     * @param string|null $nota              Nota asignada (ej: "Reprobado" o "5")
     * @return bool       true si el correo se envió/encoló
     */
    public function enviarRechazoRif(
        string  $destinatario,
        string  $nombreEstudiante,
        int     $intentoId,
        string  $casoTitulo,
        string  $observacion,
        ?string $nota = null
    ): bool {
        $subject = "❌ RIF Sucesoral No Aprobado — {$casoTitulo}";
        $body    = $this->buildHtmlRechazoRif($nombreEstudiante, $intentoId, $casoTitulo, $observacion, $nota);

        return MailQueueService::send($destinatario, $subject, $body, 'rif_sucesoral', null, $intentoId);
    }

    /**
     * Construye HTML del correo de rechazo de RIF.
     * Usa encabezado gradient rojo + información clara del motivo.
     */
    private function buildHtmlRechazoRif(
        string  $nombreEstudiante,
        int     $intentoId,
        string  $casoTitulo,
        string  $observacion,
        ?string $nota
    ): string {
        $n     = htmlspecialchars($nombreEstudiante, ENT_QUOTES, 'UTF-8');
        $caso  = htmlspecialchars($casoTitulo, ENT_QUOTES, 'UTF-8');
        $obs   = htmlspecialchars($observacion, ENT_QUOTES, 'UTF-8');
        $fecha = date('d/m/Y H:i');

        $notaHtml = '';
        if ($nota !== null && $nota !== '') {
            $notaEsc = htmlspecialchars($nota, ENT_QUOTES, 'UTF-8');
            $notaHtml = "<p style='margin: 5px 0;'><strong>📊 Nota asignada:</strong> <span style='color: #dc2626; font-weight: bold;'>{$notaEsc}</span></p>";
        }

        return "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 0;'>
            <div style='background: linear-gradient(135deg, #1a237e, #283593); color: white; padding: 30px; border-radius: 10px 10px 0 0; text-align: center;'>
                <h1 style='margin: 0; font-size: 24px;'>❌ RIF Sucesoral No Aprobado</h1>
                <p style='margin: 10px 0 0; opacity: 0.9;'>Sistema Pedagógico de Declaración Sucesoral Simulada</p>
            </div>
            <div style='background: #ffffff; padding: 30px; border: 1px solid #e0e0e0; border-top: none; border-radius: 0 0 10px 10px;'>
                <p style='font-size: 16px;'>Estimado/a <strong>{$n}</strong>,</p>
                <p>Le informamos que su inscripción de RIF Sucesoral <strong>no ha sido aprobada</strong> por su profesor. A continuación encontrará los detalles de la revisión:</p>

                <div style='background: #f5f5f5; border-left: 4px solid #283593; padding: 15px; margin: 20px 0; border-radius: 0 5px 5px 0;'>
                    <p style='margin: 5px 0;'><strong>📋 Caso:</strong> {$caso}</p>
                    <p style='margin: 5px 0;'><strong>🔢 Intento:</strong> #{$intentoId}</p>
                    <p style='margin: 5px 0;'><strong>📅 Fecha de revisión:</strong> {$fecha}</p>
                    {$notaHtml}
                </div>

                <div style='background: #f0f1f8; border: 1px solid #c5cae9; border-radius: 8px; padding: 16px; margin: 24px 0;'>
                    <h3 style='font-size: 15px; color: #1a237e; margin: 0 0 8px 0;'>📝 Observación del profesor</h3>
                    <p style='margin: 0; font-size: 14px; color: #444; line-height: 1.7;'>{$obs}</p>
                </div>

                <div style='background: #f5f5f5; border-left: 4px solid #283593; padding: 15px; margin: 20px 0; border-radius: 0 5px 5px 0;'>
                    <p style='margin: 0; font-weight: bold; color: #1a237e;'>💡 ¿Qué puede hacer?</p>
                    <p style='margin: 10px 0 0; font-size: 13px; color: #555;'>Si la configuración de su caso permite múltiples intentos, puede iniciar un nuevo intento desde la vista de <strong>Mis Asignaciones</strong>, revisando cuidadosamente los datos antes de enviarlo nuevamente. Si tiene dudas, comuníquese directamente con su profesor.</p>
                </div>

                <p style='color: #999; font-size: 12px; margin-top: 30px; border-top: 1px solid #eee; padding-top: 15px; text-align: center;'>
                    Este correo fue generado automáticamente por el SPDSS — Simulador SENIAT.
                </p>
            </div>
        </div>";
    }
}
