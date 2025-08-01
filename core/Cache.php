<?php
namespace Core;

/**
 * Simple file-based cache for static data
 * Caché simple basado en archivos para datos estáticos
 */
final class Cache {
    private static string $cacheDir = '';

    public static function init(): void {
        if (!self::$cacheDir) {
            self::$cacheDir = dirname(__DIR__) . '/storage/cache';
            if (!is_dir(self::$cacheDir)) {
                mkdir(self::$cacheDir, 0755, true);
            }
        }
    }

    public static function get(string $key, callable $callback = null, int $ttl = 3600): mixed {
        self::init();
        $file = self::$cacheDir . '/' . md5($key) . '.cache';

        if (file_exists($file) && (time() - filemtime($file)) < $ttl) {
            return unserialize(file_get_contents($file));
        }

        if ($callback) {
            $data = $callback();
            file_put_contents($file, serialize($data), LOCK_EX);
            return $data;
        }

        return null;
    }

    public static function forget(string $key): void {
        self::init();
        $file = self::$cacheDir . '/' . md5($key) . '.cache';
        if (file_exists($file)) {
            unlink($file);
        }
    }

    public static function clear(): void {
        self::init();
        $files = glob(self::$cacheDir . '/*.cache');
        foreach ($files as $file) {
            unlink($file);
        }
    }
}