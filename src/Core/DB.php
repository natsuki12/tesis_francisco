<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use Exception;

/**
 * Clase DB (Database Wrapper)
 * * Implementa el patrón de diseño Singleton para gestionar una única
 * conexión a la base de datos MySQL a través de PDO.
 * * Características de seguridad:
 * - Charset utf8mb4 (Prevención de ataques de codificación).
 * - Desactivación de emulación de preparadas (Seguridad nativa de MySQL).
 * - Manejo de excepciones estricto.
 */
class DB
{
    /**
     * @var PDO|null Instancia única de la conexión
     */
    private static ?PDO $instance = null;

    /**
     * Constructor privado para evitar instanciación directa (new DB()).
     * Parte esencial del patrón Singleton.
     */
    private function __construct() {}

    /**
     * Obtiene la instancia de la conexión a la base de datos.
     * Si no existe, la crea. Si ya existe, devuelve la que hay.
     * * @return PDO Objeto de conexión PDO
     */
    public static function connect(): PDO
    {
        // Si ya estamos conectados, retornamos la conexión existente (Ahorro de recursos)
        if (self::$instance !== null) {
            return self::$instance;
        }

        // --- 1. CONFIGURACIÓN ---
        // Obtenemos variables cargadas previamente por App.php
        $host    = getenv('DB_HOST') ?: '127.0.0.1';
        $db      = getenv('DB_NAME') ?: 'sistema_seniat';
        $user    = getenv('DB_USER') ?: 'root';
        $pass    = getenv('DB_PASS') ?: '';
        $charset = getenv('DB_CHARSET') ?: 'utf8mb4'; // utf8mb4 es más seguro que utf8
        $debug   = getenv('APP_DEBUG'); // 'true' o 'false'

        // Data Source Name (Cadena de conexión)
        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";

        // Opciones de Hardening (Blindaje)
        $options = [
            // Lanzar excepciones reales en lugar de errores silenciosos
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            // Usar arrays asociativos (más limpio para trabajar)
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            // 🚫 CRÍTICO: Desactivar emulación. 
            // Esto obliga a que la separación de datos/consulta la haga MySQL y no PHP.
            // Es la barrera real contra Inyección SQL.
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        // --- 2. INTENTO DE CONEXIÓN ---
        try {
            self::$instance = new PDO($dsn, $user, $pass, $options);
            return self::$instance;

        } catch (PDOException $e) {
            
            // --- 3. MANEJO DE ERRORES INTELIGENTE ---
            
            // A) Logueamos el error técnico siempre (Privado para el admin)
            // Usamos error_log estándar, que App.php ya redirigió a storage/logs/app_errors.log
            error_log("[CRITICAL DB ERROR] " . $e->getMessage());

            // B) Respuesta al Usuario
            // Si App.php ya cargó, preferimos lanzar la excepción y que el Handler global se encargue.
            // Pero si la DB falla al inicio, hacemos un fallback manual.
            
            http_response_code(500);

            if ($debug === 'true' || $debug === '1') {
                // MODO DESARROLLO: Mostrar detalles
                echo "<div style='background:#ffcccc; padding:20px; border:1px solid red; font-family:monospace;'>";
                echo "<h3>☠️ Error de Conexión a Base de Datos</h3>";
                echo "<p><strong>Mensaje:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
                echo "<p><strong>Host:</strong> $host | <strong>DB:</strong> $db | <strong>User:</strong> $user</p>";
                echo "</div>";
                exit; // Detener ejecución
            } else {
                // MODO PRODUCCIÓN: Mensaje genérico seguro
                // No damos pistas de qué motor usamos ni usuarios.
                echo "<div style='text-align:center; padding:50px; font-family:sans-serif;'>";
                echo "<h1 style='color:#555'>Servicio no disponible</h1>";
                echo "<p style='color:#777'>Estamos experimentando problemas técnicos. Por favor intente más tarde.</p>";
                echo "</div>";
                exit; // Detener ejecución
            }
        }
    }
    
    /**
     * Evita que clonen el objeto (Singleton)
     */
    private function __clone() {}
}