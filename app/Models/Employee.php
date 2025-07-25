<?php
namespace App\Models;

class Employee extends Model {
    protected static string $table = 'employee';
    protected static string $primary = 'id_employee';
    protected static array  $fillable = [
      'name_employee', 'date_hired', 'status_employee',
      'type_employee', 'seniority_employee', 'id_user_fk', 'id_position_fk'
    ];

    public static function findById(int $id_employee): ?array {
        $pdo = \Core\Database::pdo();
        $sql = 
            '   SELECT *
                FROM employee
                INNER JOIN users ON users.id_user = employee.id_user_fk
                WHERE id_employee = :id_employee
                LIMIT 1';
        $st = $pdo->prepare($sql);
        $st->bindParam(':id_employee', $id_employee, \PDO::PARAM_INT);
        $st->execute();
        return $st->fetch() ?: null;
    }

    public static function allEmployeeUser (): array {
        $pdo = \Core\Database::pdo();
        $sql = 
            '   SELECT *
                FROM employee
                INNER JOIN users ON users.id_user = employee.id_user_fk
                INNER JOIN positions ON positions.id_position = employee.id_position_fk
                INNER JOIN department ON department.id_department = positions.id_departament_fk';
        $st = $pdo->prepare($sql);
        $st->execute();
        return $st->fetchAll();
    }
}