<?php

namespace App\Models;

use Core\Database;

class Positions extends Model {
    protected static string $table = 'positions';
    protected static string $primary = 'id_position';
    protected static array  $fillable = [ 'name_position', 'id_department_fk' ];

    public static function all(): array {
        $pdo = Database::pdo();
        $sql = 'SELECT * FROM positions ORDER BY id_position';
        $st = $pdo->prepare($sql);
        $st->execute();
        return $st->fetchAll();
    }

    public static function findByIdDepartment(int $id_department): ?array {
        $pdo = Database::pdo();
        $sql = 'SELECT * FROM positions WHERE id_department_fk = :id_department LIMIT 1';
        $st = $pdo->prepare($sql);
        $st->bindParam(':id_department', $id_department, \PDO::PARAM_INT);
        $st->execute();
        return $st->fetch() ?: null;
    }
}