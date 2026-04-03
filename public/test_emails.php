<?php
/**
 * ═══════════════════════════════════════════════════════════
 *  TEST EMAIL PREVIEW — Envía TODOS los templates de correo
 *  sin tocar la base de datos (usa Mailer::send directamente).
 * ═══════════════════════════════════════════════════════════
 *
 *  Uso: http://localhost/tesis_francisco/test_emails.php
 *
 *  SEGURIDAD: Este script NO debe existir en producción.
 *  Solo envía correos, NO inserta en mail_queue ni en ninguna tabla.
 */

// ── Bootstrap mínimo (autoload + .env) ──
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\App;
use App\Core\Mailer;

App::loadEnv(__DIR__ . '/../.env');

// ── Config ──
$destinatario = 'fadr2001@gmail.com';
$baseUrl      = rtrim($_ENV['APP_BASE'] ?? 'http://localhost/tesis_francisco', '/');
$fecha        = date('d/m/Y H:i');

// ── Resultados ──
$resultados = [];

// ════════════════════════════════════════════════════════════
//  1. BIENVENIDA ESTUDIANTE
// ════════════════════════════════════════════════════════════
$body1 = "
<div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
    <div style='background: linear-gradient(135deg, #1a237e, #283593); color: white; padding: 30px; border-radius: 10px 10px 0 0; text-align: center;'>
        <img src='{$baseUrl}/assets/img/logos/sucelab/logo_Mesa%20de%20trabajo%201-04.png' alt='SUCELAB Logo' style='display: block; margin: 0 auto 15px auto; max-width: 150px; height: auto;'>
        <h1 style='margin: 0; font-size: 24px;'>Bienvenido al SUCELAB</h1>
        <p style='margin: 10px 0 0; opacity: 0.9;'>Sistema Universitario de Capacitación y Evaluación en Legislación y Administración de Bienes Sucesorales</p>
    </div>
    <div style='background: #ffffff; padding: 30px; border: 1px solid #e0e0e0; border-top: none;'>
        <p style='font-size: 16px;'>Estimado/a <strong>María Pérez García</strong>,</p>
        <p>Su cuenta de estudiante ha sido creada exitosamente en el SUCELAB. A continuación encontrará sus datos de acceso:</p>

        <div style='background: #f5f5f5; border-left: 4px solid #1a237e; padding: 15px; margin: 20px 0; border-radius: 0 5px 5px 0;'>
            <p style='margin: 5px 0;'><strong>🔗 URL del sistema:</strong> <a href='{$baseUrl}/login'>{$baseUrl}/login</a></p>
            <p style='margin: 5px 0;'><strong>📧 Email:</strong> maria.perez@ejemplo.com</p>
            <p style='margin: 5px 0;'><strong>🔑 Contraseña temporal:</strong> Su número de cédula (12345678)</p>
        </div>

        <div style='background: #fff3e0; border-left: 4px solid #ff9800; padding: 15px; margin: 20px 0; border-radius: 0 5px 5px 0;'>
            <p style='margin: 0; font-weight: bold; color: #e65100;'>⚠️ Importante</p>
            <p style='margin: 10px 0 0;'>Al iniciar sesión por primera vez, el sistema le solicitará establecer una nueva contraseña segura.</p>
        </div>

        <p style='color: #666; font-size: 13px; margin-top: 30px; border-top: 1px solid #eee; padding-top: 15px;'>
            Este es un correo automático del SUCELAB. Si no solicitó esta cuenta, puede ignorar este mensaje.
        </p>
    </div>
</div>";

$resultados[] = [
    'nombre'  => '1. Bienvenida Estudiante',
    'subject' => 'Bienvenido al SUCELAB — Su cuenta ha sido creada',
    'ok'      => Mailer::send($destinatario, '[TEST] Bienvenido al SUCELAB — Su cuenta ha sido creada (Estudiante)', $body1),
];

// ════════════════════════════════════════════════════════════
//  2. BIENVENIDA PROFESOR
// ════════════════════════════════════════════════════════════
$body2 = "
<div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
    <div style='background: linear-gradient(135deg, #1a237e, #283593); color: white; padding: 30px; border-radius: 10px 10px 0 0; text-align: center;'>
        <img src='{$baseUrl}/assets/img/logos/sucelab/logo_Mesa%20de%20trabajo%201-04.png' alt='SUCELAB Logo' style='display: block; margin: 0 auto 15px auto; max-width: 150px; height: auto;'>
        <h1 style='margin: 0; font-size: 24px;'>Bienvenido al SUCELAB</h1>
        <p style='margin: 10px 0 0; opacity: 0.9;'>Sistema Universitario de Capacitación y Evaluación en Legislación y Administración de Bienes Sucesorales</p>
    </div>
    <div style='background: #ffffff; padding: 30px; border: 1px solid #e0e0e0; border-top: none;'>
        <p style='font-size: 16px;'>Estimado/a <strong>Prof. Carlos Rodríguez</strong>,</p>
        <p>Su cuenta de profesor ha sido creada exitosamente en el SUCELAB. A continuación encontrará sus datos de acceso:</p>
        
        <div style='background: #f5f5f5; border-left: 4px solid #1a237e; padding: 15px; margin: 20px 0; border-radius: 0 5px 5px 0;'>
            <p style='margin: 5px 0;'><strong>🔗 URL del sistema:</strong> <a href='{$baseUrl}/login'>{$baseUrl}/login</a></p>
            <p style='margin: 5px 0;'><strong>📧 Email:</strong> carlos.rodriguez@ejemplo.com</p>
            <p style='margin: 5px 0;'><strong>🔑 Contraseña temporal:</strong> Su número de cédula (87654321)</p>
        </div>

        <div style='background: #fff3e0; border-left: 4px solid #ff9800; padding: 15px; margin: 20px 0; border-radius: 0 5px 5px 0;'>
            <p style='margin: 0; font-weight: bold; color: #e65100;'>⚠️ Importante</p>
            <p style='margin: 10px 0 0;'>Al iniciar sesión por primera vez, el sistema le solicitará establecer una nueva contraseña segura.</p>
        </div>

        <p style='color: #666; font-size: 13px; margin-top: 30px; border-top: 1px solid #eee; padding-top: 15px;'>
            Este es un correo automático del SUCELAB. Si no solicitó esta cuenta, puede ignorar este mensaje.
        </p>
    </div>
</div>";

$resultados[] = [
    'nombre'  => '2. Bienvenida Profesor',
    'subject' => 'Bienvenido al SUCELAB — Su cuenta ha sido creada',
    'ok'      => Mailer::send($destinatario, '[TEST] Bienvenido al SUCELAB — Su cuenta ha sido creada (Profesor)', $body2),
];

// ════════════════════════════════════════════════════════════
//  3. RECUPERACIÓN DE CONTRASEÑA
// ════════════════════════════════════════════════════════════
$body3 = "
<div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 0;'>
    <div style='background: linear-gradient(135deg, #1a237e, #283593); color: white; padding: 30px; border-radius: 10px 10px 0 0; text-align: center;'>
        <img src='{$baseUrl}/assets/img/logos/sucelab/logo_Mesa%20de%20trabajo%201-04.png' alt='SUCELAB Logo' style='display: block; margin: 0 auto 15px auto; max-width: 150px; height: auto;'>
        <h1 style='margin: 0; font-size: 24px;'>Recuperación de Contraseña</h1>
        <p style='margin: 10px 0 0; opacity: 0.9;'>Sistema Universitario de Capacitación y Evaluación en Legislación y Administración de Bienes Sucesorales</p>
    </div>
    <div style='background: #ffffff; padding: 30px; border: 1px solid #e0e0e0; border-top: none; border-radius: 0 0 10px 10px;'>
        <p style='font-size: 16px;'>Estimado/a usuario,</p>
        <p>Hemos recibido una solicitud para restablecer su contraseña en el SUCELAB. Utilice el siguiente código de verificación para completar el proceso:</p>

        <div style='background: #f5f5f5; border: 2px solid #1a237e; border-radius: 12px; padding: 24px; margin: 24px 0; text-align: center;'>
            <p style='margin: 0 0 8px; font-size: 13px; color: #555;'>Su código de verificación es:</p>
            <strong style='font-size: 32px; letter-spacing: 8px; color: #1a237e;'>483721</strong>
        </div>

        <div style='background: #fff3e0; border-left: 4px solid #ff9800; padding: 15px; margin: 20px 0; border-radius: 0 5px 5px 0;'>
            <p style='margin: 0; font-weight: bold; color: #e65100;'>Importante</p>
            <p style='margin: 10px 0 0;'>Este código expira en <strong>15 minutos</strong>. Si no solicitó este cambio, puede ignorar este correo de forma segura.</p>
        </div>

        <p style='color: #999; font-size: 12px; margin-top: 30px; border-top: 1px solid #eee; padding-top: 15px; text-align: center;'>
            Este correo fue generado automáticamente por el SUCELAB — Simulador SENIAT.
        </p>
    </div>
</div>";

$resultados[] = [
    'nombre'  => '3. Recuperación de Contraseña',
    'subject' => 'Código de Recuperación de Contraseña — SUCELAB',
    'ok'      => Mailer::send($destinatario, '[TEST] Código de Recuperación de Contraseña — SUCELAB', $body3),
];

// ════════════════════════════════════════════════════════════
//  4. RIF ÉXITO (Validación automática)
// ════════════════════════════════════════════════════════════
$body4 = "
<div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 0;'>
    <div style='background: linear-gradient(135deg, #1a237e, #283593); color: white; padding: 30px; border-radius: 10px 10px 0 0; text-align: center;'>
        <img src='{$baseUrl}/assets/img/logos/sucelab/logo_Mesa%20de%20trabajo%201-04.png' alt='SUCELAB Logo' style='display: block; margin: 0 auto 15px auto; max-width: 150px; height: auto;'>
        <h1 style='margin: 0; font-size: 24px;'>RIF Sucesoral Generado</h1>
        <p style='margin: 10px 0 0; opacity: 0.9;'>Sistema Universitario de Capacitación y Evaluación en Legislación y Administración de Bienes Sucesorales</p>
    </div>
    <div style='background: #ffffff; padding: 30px; border: 1px solid #e0e0e0; border-top: none; border-radius: 0 0 10px 10px;'>
        <p style='font-size: 16px;'>Estimado/a <strong>María Pérez García</strong>,</p>
        <p>Su validación fue exitosa y se ha generado el RIF Sucesoral para su caso. A continuación encontrará los detalles:</p>

        <div style='background: #f5f5f5; border-left: 4px solid #1a237e; padding: 15px; margin: 20px 0; border-radius: 0 5px 5px 0;'>
            <p style='margin: 5px 0;'><strong>Caso:</strong> Caso de Prueba — Sucesión López</p>
            <p style='margin: 5px 0;'><strong>Intento:</strong> #42</p>
            <p style='margin: 5px 0;'><strong>Fecha:</strong> {$fecha}</p>
        </div>

        <div style='background: #f0fdf4; border: 2px solid #059669; border-radius: 12px; padding: 24px; margin: 24px 0; text-align: center;'>
            <p style='margin: 0 0 8px; font-size: 13px; color: #555;'>Su RIF Sucesoral es:</p>
            <strong style='font-size: 32px; letter-spacing: 5px; color: #059669;'>J-50123456-7</strong>
        </div>

        <div style='margin: 24px 0; font-size: 14px; line-height: 1.7;'>
            <h3 style='font-size: 15px; color: #1a237e; margin: 0 0 12px 0; border-bottom: 1px solid #e0e0e0; padding-bottom: 8px;'>
                Instrucciones
            </h3>
            <ol style='margin: 0; padding-left: 20px; color: #444;'>
                <li style='margin-bottom: 8px;'>Guarde este RIF Sucesoral para sus registros.</li>
                <li style='margin-bottom: 8px;'>En el proceso real, debe presentar la planilla impresa ante la unidad del SENIAT correspondiente a su domicilio fiscal.</li>
                <li style='margin-bottom: 8px;'>Para fines educativos, este RIF ha sido asignado automáticamente a su caso en el simulador.</li>
            </ol>
        </div>

        <div style='background: #fffbeb; border-left: 4px solid #f59e0b; padding: 15px; margin: 20px 0; border-radius: 0 5px 5px 0;'>
            <p style='margin: 0; font-weight: bold; color: #92400e;'>Nota sobre el proceso real</p>
            <p style='margin: 10px 0 0; font-size: 13px; color: #555;'>En el proceso real ante el SENIAT, se requiere un correo electrónico exclusivo para el RIF Sucesoral. Se recomienda crear uno para uso exclusivo de la sucesión.</p>
        </div>

        <p style='color: #999; font-size: 12px; margin-top: 30px; border-top: 1px solid #eee; padding-top: 15px; text-align: center;'>
            Este correo fue generado automáticamente por el SUCELAB — Simulador SENIAT.
        </p>
    </div>
</div>";

$resultados[] = [
    'nombre'  => '4. RIF Éxito (Validación automática)',
    'subject' => '✅ RIF Sucesoral Generado — Caso: Sucesión López, Intento: #42',
    'ok'      => Mailer::send($destinatario, '[TEST] ✅ RIF Sucesoral Generado — Caso: Sucesión López, Intento: #42', $body4),
];

// ════════════════════════════════════════════════════════════
//  5. RIF APROBADO (Por profesor)
// ════════════════════════════════════════════════════════════
$body5 = "
<div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 0;'>
    <div style='background: linear-gradient(135deg, #1a237e, #283593); color: white; padding: 30px; border-radius: 10px 10px 0 0; text-align: center;'>
        <img src='{$baseUrl}/assets/img/logos/sucelab/logo_Mesa%20de%20trabajo%201-04.png' alt='SUCELAB Logo' style='display: block; margin: 0 auto 15px auto; max-width: 150px; height: auto;'>
        <h1 style='margin: 0; font-size: 24px;'>RIF Sucesoral Aprobado</h1>
        <p style='margin: 10px 0 0; opacity: 0.9;'>Sistema Universitario de Capacitación y Evaluación en Legislación y Administración de Bienes Sucesorales</p>
    </div>
    <div style='background: #ffffff; padding: 30px; border: 1px solid #e0e0e0; border-top: none; border-radius: 0 0 10px 10px;'>
        <p style='font-size: 16px;'>Estimado/a <strong>María Pérez García</strong>,</p>
        <p>Le informamos que su inscripción de RIF Sucesoral ha sido <strong style='color: #059669;'>aprobada</strong> por su profesor. A continuación encontrará los datos generados:</p>

        <div style='background: #f0fdf4; border: 2px solid #059669; border-radius: 12px; padding: 24px; margin: 24px 0; text-align: center;'>
            <p style='margin: 0 0 8px; font-size: 13px; color: #555;'>Su RIF Sucesoral es:</p>
            <strong style='font-size: 32px; letter-spacing: 5px; color: #059669;'>J-50123456-7</strong>
        </div>

        <div style='background: #f5f5f5; border-left: 4px solid #065f46; padding: 15px; margin: 20px 0; border-radius: 0 5px 5px 0;'>
            <p style='margin: 5px 0;'><strong>📋 Caso:</strong> Sucesión López</p>
            <p style='margin: 5px 0;'><strong>🔢 Intento:</strong> #42</p>
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
            Este correo fue generado automáticamente por el SUCELAB — Simulador SENIAT.
        </p>
    </div>
</div>";

$resultados[] = [
    'nombre'  => '5. RIF Aprobado (Por profesor)',
    'subject' => '✅ RIF Sucesoral Aprobado — Sucesión López',
    'ok'      => Mailer::send($destinatario, '[TEST] ✅ RIF Sucesoral Aprobado — Sucesión López', $body5),
];

// ════════════════════════════════════════════════════════════
//  6. RIF RECHAZADO (Por profesor)
// ════════════════════════════════════════════════════════════
$body6 = "
<div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 0;'>
    <div style='background: linear-gradient(135deg, #1a237e, #283593); color: white; padding: 30px; border-radius: 10px 10px 0 0; text-align: center;'>
        <img src='{$baseUrl}/assets/img/logos/sucelab/logo_Mesa%20de%20trabajo%201-04.svg' alt='SUCELAB Logo' style='display: block; margin: 0 auto 15px auto; max-width: 150px; height: auto;'>
        <h1 style='margin: 0; font-size: 24px;'>RIF Sucesoral No Aprobado</h1>
        <p style='margin: 10px 0 0; opacity: 0.9;'>Sistema Universitario de Capacitación y Evaluación en Legislación y Administración de Bienes Sucesorales</p>
    </div>
    <div style='background: #ffffff; padding: 30px; border: 1px solid #e0e0e0; border-top: none; border-radius: 0 0 10px 10px;'>
        <p style='font-size: 16px;'>Estimado/a <strong>María Pérez García</strong>,</p>
        <p>Le informamos que su inscripción de RIF Sucesoral <strong>no ha sido aprobada</strong> por su profesor. A continuación encontrará los detalles de la revisión:</p>

        <div style='background: #f5f5f5; border-left: 4px solid #283593; padding: 15px; margin: 20px 0; border-radius: 0 5px 5px 0;'>
            <p style='margin: 5px 0;'><strong>📋 Caso:</strong> Sucesión López</p>
            <p style='margin: 5px 0;'><strong>🔢 Intento:</strong> #42</p>
            <p style='margin: 5px 0;'><strong>📅 Fecha de revisión:</strong> {$fecha}</p>
            <p style='margin: 5px 0;'><strong>📊 Nota asignada:</strong> <span style='color: #dc2626; font-weight: bold;'>Reprobado</span></p>
        </div>

        <div style='background: #f0f1f8; border: 1px solid #c5cae9; border-radius: 8px; padding: 16px; margin: 24px 0;'>
            <h3 style='font-size: 15px; color: #1a237e; margin: 0 0 8px 0;'>📝 Observación del profesor</h3>
            <p style='margin: 0; font-size: 14px; color: #444; line-height: 1.7;'>Los datos del causante no coinciden con el caso asignado. Revise la cédula y la fecha de fallecimiento. Adicionalmente, falta incluir al heredero menor de edad en la relación de sucesores.</p>
        </div>

        <div style='background: #f5f5f5; border-left: 4px solid #283593; padding: 15px; margin: 20px 0; border-radius: 0 5px 5px 0;'>
            <p style='margin: 0; font-weight: bold; color: #1a237e;'>💡 ¿Qué puede hacer?</p>
            <p style='margin: 10px 0 0; font-size: 13px; color: #555;'>Si la configuración de su caso permite múltiples intentos, puede iniciar un nuevo intento desde la vista de <strong>Mis Asignaciones</strong>, revisando cuidadosamente los datos antes de enviarlo nuevamente. Si tiene dudas, comuníquese directamente con su profesor.</p>
        </div>

        <p style='color: #999; font-size: 12px; margin-top: 30px; border-top: 1px solid #eee; padding-top: 15px; text-align: center;'>
            Este correo fue generado automáticamente por el SUCELAB — Simulador SENIAT.
        </p>
    </div>
</div>";

$resultados[] = [
    'nombre'  => '6. RIF Rechazado (Por profesor)',
    'subject' => '❌ RIF Sucesoral No Aprobado — Sucesión López',
    'ok'      => Mailer::send($destinatario, '[TEST] ❌ RIF Sucesoral No Aprobado — Sucesión López', $body6),
];

// ════════════════════════════════════════════════════════════
//  REPORTE FINAL
// ════════════════════════════════════════════════════════════
header('Content-Type: text/html; charset=utf-8');
echo "<!DOCTYPE html><html><head><title>Test Emails</title>
<style>
body { font-family: 'Segoe UI', sans-serif; max-width: 700px; margin: 40px auto; padding: 20px; background: #f5f5f5; }
h1 { color: #1a237e; }
table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.1); }
th, td { padding: 12px 16px; text-align: left; border-bottom: 1px solid #eee; }
th { background: #1a237e; color: white; }
.ok { color: #059669; font-weight: bold; }
.fail { color: #dc2626; font-weight: bold; }
.info { background: #e8eaf6; padding: 12px 16px; border-radius: 8px; margin: 16px 0; font-size: 14px; color: #283593; }
</style>
</head><body>
<h1>📧 Test de Emails del SUCELAB</h1>
<div class='info'>
    <strong>Destinatario:</strong> {$destinatario}<br>
    <strong>Fecha de envío:</strong> {$fecha}<br>
    <strong>⚠️ Ningún cambio fue realizado en la base de datos.</strong>
</div>
<table>
<tr><th>#</th><th>Template</th><th>Estado</th></tr>";

$exitosos = 0;
$fallidos = 0;
foreach ($resultados as $r) {
    $icon = $r['ok'] ? '✅' : '❌';
    $class = $r['ok'] ? 'ok' : 'fail';
    $label = $r['ok'] ? 'Enviado' : 'Falló';
    if ($r['ok']) $exitosos++; else $fallidos++;
    echo "<tr><td>{$icon}</td><td>{$r['nombre']}</td><td class='{$class}'>{$label}</td></tr>";
}

echo "</table>
<div class='info' style='margin-top: 20px;'>
    <strong>Resumen:</strong> {$exitosos} enviados, {$fallidos} fallidos de " . count($resultados) . " total.<br>
    <em>Revisa tu bandeja de entrada (y spam) en {$destinatario}</em>
</div>
</body></html>";
