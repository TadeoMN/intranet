<?php
namespace Core;
use PDO, PDOException;

final class Database {
    private static ?PDO $pdo=null;
    public static function pdo(): PDO {
        if(!self::$pdo){
            $d=config('db');
            $opt=[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
                  PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
                  PDO::MYSQL_ATTR_INIT_COMMAND=>"SET sql_mode='STRICT_TRANS_TABLES'"];
            self::$pdo = new PDO($d['dsn'],$d['user'],$d['pass'],$opt);
        }
        return self::$pdo;
    }
}