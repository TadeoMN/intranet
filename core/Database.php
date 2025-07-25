<?php
namespace Core;
use PDO, PDOException;

final class Database {
    private static ?PDO $pdo = null;
    private static array $queryCache = [];
    private static int $queryCount = 0;
    
    public static function pdo(): PDO {
        if (!self::$pdo) {
            $d = config('db');
            
            // Optimized PDO options for better performance
            // Opciones de PDO optimizadas para mejor rendimiento
            $opt = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET sql_mode='STRICT_TRANS_TABLES'",
                PDO::ATTR_PERSISTENT => true, // Connection pooling / Agrupación de conexiones
                PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_FOUND_ROWS => true
            ];
            
            try {
                self::$pdo = new PDO($d['dsn'], $d['user'], $d['pass'], $opt);
                
                // Set additional MySQL optimizations
                // Establecer optimizaciones adicionales de MySQL
                self::$pdo->exec("SET SESSION query_cache_type = ON");
                self::$pdo->exec("SET SESSION sql_mode = 'STRICT_TRANS_TABLES'");
            } catch (PDOException $e) {
                error_log("Database connection failed: " . $e->getMessage());
                throw $e;
            }
        }
        return self::$pdo;
    }
    
    /**
     * Get query execution statistics for debugging
     * Obtener estadísticas de ejecución de consultas para depuración
     */
    public static function getQueryCount(): int {
        return self::$queryCount;
    }
    
    /**
     * Increment query counter for monitoring
     * Incrementar contador de consultas para monitoreo
     */
    public static function incrementQueryCount(): void {
        self::$queryCount++;
    }
    
    /**
     * Reset connection for testing or maintenance
     * Reiniciar conexión para pruebas o mantenimiento
     */
    public static function resetConnection(): void {
        self::$pdo = null;
        self::$queryCount = 0;
    }
}