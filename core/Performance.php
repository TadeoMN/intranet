<?php
namespace Core;

/**
 * Simple performance monitoring utility
 * Utilidad simple de monitoreo de rendimiento
 */
final class Performance {
    private static array $timers = [];
    private static array $memoryUsage = [];
    private static int $queryCount = 0;
    
    /**
     * Start a performance timer
     * Iniciar un temporizador de rendimiento
     */
    public static function startTimer(string $name): void {
        self::$timers[$name] = [
            'start' => microtime(true),
            'memory_start' => memory_get_usage(true)
        ];
    }
    
    /**
     * End a performance timer and return elapsed time
     * Finalizar un temporizador de rendimiento y devolver tiempo transcurrido
     */
    public static function endTimer(string $name): ?float {
        if (!isset(self::$timers[$name])) {
            return null;
        }
        
        $elapsed = microtime(true) - self::$timers[$name]['start'];
        $memoryUsed = memory_get_usage(true) - self::$timers[$name]['memory_start'];
        
        self::$memoryUsage[$name] = $memoryUsed;
        
        return $elapsed;
    }
    
    /**
     * Get performance statistics
     * Obtener estadísticas de rendimiento
     */
    public static function getStats(): array {
        $stats = [
            'total_execution_time' => microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'],
            'peak_memory_usage' => memory_get_peak_usage(true),
            'current_memory_usage' => memory_get_usage(true),
            'database_queries' => Database::getQueryCount(),
            'timers' => [],
            'memory_usage' => self::$memoryUsage
        ];
        
        foreach (self::$timers as $name => $timer) {
            if (isset($timer['start'])) {
                $stats['timers'][$name] = microtime(true) - $timer['start'];
            }
        }
        
        return $stats;
    }
    
    /**
     * Log performance statistics for debugging
     * Registrar estadísticas de rendimiento para depuración
     */
    public static function logStats(): void {
        if (config('app.debug') ?? false) {
            $stats = self::getStats();
            error_log("Performance Stats: " . json_encode($stats));
        }
    }
    
    /**
     * Add performance headers to response
     * Agregar headers de rendimiento a la respuesta
     */
    public static function addPerformanceHeaders(): void {
        if (config('app.debug') ?? false) {
            $stats = self::getStats();
            header('X-Execution-Time: ' . round($stats['total_execution_time'] * 1000, 2) . 'ms');
            header('X-Memory-Usage: ' . round($stats['peak_memory_usage'] / 1024 / 1024, 2) . 'MB');
            header('X-Database-Queries: ' . $stats['database_queries']);
        }
    }
    
    /**
     * Format bytes to human readable format
     * Formatear bytes a formato legible
     */
    public static function formatBytes(int $bytes): string {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}