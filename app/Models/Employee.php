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

    public static function allEmployeeUser(int $limit = 100, int $offset = 0): array {
        $pdo = \Core\Database::pdo();
        
        // Optimized query with specific columns and pagination
        // Consulta optimizada con columnas específicas y paginación
        $sql = 
            '   SELECT 
                    e.id_employee,
                    e.name_employee,
                    e.date_hired,
                    e.status_employee,
                    e.type_employee,
                    e.seniority_employee,
                    u.name_user,
                    u.email_user,
                    p.name_position,
                    d.name_department
                FROM employee e
                INNER JOIN users u ON u.id_user = e.id_user_fk
                INNER JOIN positions p ON p.id_position = e.id_position_fk
                INNER JOIN department d ON d.id_department = p.id_departament_fk
                WHERE e.status_employee = 1
                ORDER BY e.name_employee
                LIMIT :limit OFFSET :offset';
                
        $st = $pdo->prepare($sql);
        $st->bindParam(':limit', $limit, \PDO::PARAM_INT);
        $st->bindParam(':offset', $offset, \PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll();
    }
    
    /**
     * Get total count of active employees for pagination
     * Obtener el total de empleados activos para paginación
     */
    public static function countActiveEmployees(): int {
        $pdo = \Core\Database::pdo();
        $sql = 'SELECT COUNT(*) as total FROM employee WHERE status_employee = 1';
        $st = $pdo->prepare($sql);
        $st->execute();
        $result = $st->fetch();
        return (int) $result['total'];
    }
}