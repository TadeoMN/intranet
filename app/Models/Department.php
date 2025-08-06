<?php

namespace App\Models;

use Core\Database;

class Department extends Model {
    protected static string $table = 'department';
    protected static string $primary = 'id_department';
    protected static array  $fillable = [ 'name_department', 'id_manager_employee_fk' ];

    public static function all(): array {
        $pdo = Database::pdo();
        $sql = 'SELECT * FROM department ORDER BY name_department';
        $st = $pdo->prepare($sql);
        $st->execute();
        return $st->fetchAll();
    }

    public static function findById(int $id): ?array {
        $pdo = Database::pdo();
        $sql = 'SELECT * FROM department WHERE id_department = :id LIMIT 1';
        $st = $pdo->prepare($sql);
        $st->bindParam(':id', $id, \PDO::PARAM_INT);
        $st->execute();
        return $st->fetch() ?: null;
    }
}