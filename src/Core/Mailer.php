<?php
declare(strict_types=1);

namespace App\Core;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer
{
    /**
     * Envía un correo electrónico con validaciones de seguridad y configuración robusta.
     */
    public static function send(string $to, string $subject, string $body): bool
    {
        // 1. MEJORA DE SEGURIDAD: Validación y saneamiento del email
        // filter_var: Verifica formato correcto.
        // preg_match: Evita "Header Injection" (que metan saltos de línea para spammear).
        if (!filter_var($to, FILTER_VALIDATE_EMAIL) || preg_match("/[\r\n]/", $to)) {
            error_log("Mailer Security Warning: Intento de envío a email inválido o malicioso: " . $to);
            return false;
        }

        // 2. CARGA DE VARIABLES DE ENTORNO
        $host = getenv('SMTP_HOST');
        $user = getenv('SMTP_USER');
        $pass = getenv('SMTP_PASS');
        $port = getenv('SMTP_PORT');
        $fromName = getenv('SMTP_FROM_NAME') ?: 'Sistema';
        $env  = getenv('APP_ENV') ?: 'production'; // Detectar si estamos en local

        // 3. VALIDACIÓN DE CONFIGURACIÓN
        // Si falta algo en el .env, fallamos rápido para no colgar el sistema
        if (!$host || !$user || !$pass || !$port) {
            error_log("Mailer Critical Error: Faltan variables SMTP en el archivo .env");
            return false;
        }

        $mail = new PHPMailer(true);

        try {
            // --- CONFIGURACIÓN DEL SERVIDOR ---
            $mail->isSMTP();
            $mail->Host       = $host;
            $mail->SMTPAuth   = true;
            $mail->Username   = $user;
            $mail->Password   = $pass;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = (int) $port;
            
            // Configuración Global
            $mail->CharSet    = 'UTF-8';
            $mail->Timeout    = 10; // Evita que la web se quede cargando infinito si Gmail no responde
            $mail->isHTML(true);

            // 4. FIX PARA XAMPP / REDES UNIVERSITARIAS (Solo en local)
            // Esto permite certificados auto-firmados, vital para desarrollo local.
            if ($env === 'local') {
                $mail->SMTPOptions = [
                    'ssl' => [
                        'verify_peer'       => false,
                        'verify_peer_name'  => false,
                        'allow_self_signed' => true,
                    ],
                ];
                // Debug level 2 muestra todo el diálogo cliente-servidor (útil si falla)
                // OJO: Esto imprime en pantalla, úsalo solo si tienes errores.
                // $mail->SMTPDebug = 2; 
            }

            // --- REMITENTE Y DESTINATARIO ---
            $mail->setFrom($user, $fromName);
            $mail->addAddress($to);

            // --- CONTENIDO ---
            $mail->Subject = $subject;
            $mail->Body    = $body;
            
            // 5. MEJORA EN TEXTO PLANO
            // Convierte HTML a texto plano limpio para clientes antiguos
            $mail->AltBody = trim(html_entity_decode(strip_tags($body)));

            $mail->send();
            return true;

        } catch (Exception $e) {
            // Logueamos el error técnico exacto de PHPMailer
            error_log("Mailer Exception: " . $mail->ErrorInfo);
            return false;
        }
    }
}