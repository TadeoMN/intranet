<?php
namespace Core;
use PDO, PDOException;
use SysvSharedMemory;

final class Database {
    private static ?PDO $pdo = null;
    private static array $queryCache = [];
    private static int $queryCount = 0;

    public static function pdo(): PDO {
        if(!self::$pdo){
            $d=config('db');

            $opt=[
                PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
                PDO::MYSQL_ATTR_INIT_COMMAND=>"SET sql_mode='STRICT_TRANS_TABLES'",
                PDO::ATTR_PERSISTENT => true,
                PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_FOUND_ROWS => true
            ];

            try {
                self::$pdo = new PDO($d['dsn'], $d['user'], $d['pass'], $opt);

                self::$pdo->exec("SET SESSION sql_mode = 'STRICT_TRANS_TABLES'");
            } catch (PDOException $error){
                error_log("Database connection error: " . $error->getMessage());
                throw $error;
            }
        }
        return self::$pdo;
    }

    public static function getQueryCount(): int {
        return self::$queryCount;
    }

    public static function incrementQueryCount(): void {
        self::$queryCount++;
    }

    public static function resetQueryCount(): void {
        self::$pdo = null;
        self::$queryCache = [];
        self::$queryCount = 0;
    }
}