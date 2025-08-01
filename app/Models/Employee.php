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

    public static function allEmployeeUser (int $limit, int $offset): array {
        $pdo = \Core\Database::pdo();
        $sql = 
            '   SELECT
                    employee.id_employee AS id_employee, employee.code_employee AS code_employee,
                    employee.name_employee AS name_employee, employee.date_hired AS date_hired,
                    employee.status_employee AS status_employee, employee.type_employee AS type_employee,
                    employee.seniority_employee AS seniority_employee, employee.id_user_fk AS id_user_fk,
                    users.id_user, users.name_user, positions.id_position, positions.name_position,
                    department.id_department, department.name_department, department.id_manager_employee_fk,
                    manager.id_employee AS id_manager, manager.name_employee AS name_manager
                FROM employee
                INNER JOIN users ON users.id_user = employee.id_user_fk
                INNER JOIN positions ON positions.id_position = employee.id_position_fk
                INNER JOIN department ON department.id_department = positions.id_departament_fk
                LEFT JOIN employee AS manager ON manager.id_employee = department.id_manager_employee_fk
                WHERE employee.status_employee = "ACTIVO"
                ORDER BY employee.id_user_fk ASC
                LIMIT :limit OFFSET :offset';
        $st = $pdo->prepare($sql);
        $st->bindParam(':limit', $limit, \PDO::PARAM_INT);
        $st->bindParam(':offset', $offset, \PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll();
    }

    public static function countActiveEmployees(): int {
        $pdo = \Core\Database::pdo();
        $sql = 
            '   SELECT COUNT(*) as TOTAL
                FROM employee
                WHERE status_employee = 1';
        $st = $pdo->prepare($sql);
        $st->execute();
        $result = $st->fetch();
        return (int) $result['TOTAL'] ?? 0;
    }

    public static function filterPaginated(?string $search, ?string $dateFrom, ?string $dateTo, int $limit, int $offset, string $sort, string $order): array {
        $pdo = \Core\Database::pdo();
        $where = ['employee.status_employee = "ACTIVO"'];
        $bindings = [];

        $search = trim((string)$search);
        if ($search !== '') {
            $likeSearch = '%' . mb_strtolower($search, 'UTF-8') . '%';
            $where[] = '(
                LOWER(employee.code_employee) LIKE :search1
                OR LOWER(employee.name_employee) LIKE :search2
                OR LOWER(employee.status_employee) LIKE :search3
                OR LOWER(employee.type_employee) LIKE :search4
                OR LOWER(employee.seniority_employee) LIKE :search5
                OR LOWER(users.name_user) LIKE :search6
                OR LOWER(positions.name_position) LIKE :search7
                OR LOWER(department.name_department) LIKE :search8
                OR LOWER(manager.name_employee) LIKE :search9
            )';
            $bindings += [
                ':search1' => $likeSearch,
                ':search2' => $likeSearch,
                ':search3' => $likeSearch,
                ':search4' => $likeSearch,
                ':search5' => $likeSearch,
                ':search6' => $likeSearch,
                ':search7' => $likeSearch,
                ':search8' => $likeSearch,
                ':search9' => $likeSearch
            ];
        }

        $dateFrom = $dateFrom === '' ? null : $dateFrom;
        $dateTo = $dateTo === '' ? null : $dateTo;

        if ($dateFrom) {
            $where[] = 'employee.date_hired >= :dateFrom';
            $bindings[':dateFrom'] = $dateFrom;
        }

        if ($dateTo) {
            $where[] = 'employee.date_hired <= :dateTo';
            $bindings[':dateTo'] = $dateTo;
        }

        $allowedSorts = [
            'code_employee', 'name_employee', 'date_hired', 'status_employee',
            'type_employee', 'seniority_employee', 'name_user', 'name_position', 'name_department', 'name_manager'
        ];
        $orderBy = in_array($sort, $allowedSorts, true) ? $sort : 'code_employee';
        $orderDirection = $order === 'desc' ? 'DESC' : 'ASC';
        $orderBy = $orderBy . ' ' . $orderDirection;

        $sqlBase = 
            '   FROM employee
                INNER JOIN users ON users.id_user = employee.id_user_fk
                INNER JOIN positions ON positions.id_position = employee.id_position_fk
                INNER JOIN department ON department.id_department = positions.id_departament_fk
                LEFT JOIN employee AS manager ON manager.id_employee = department.id_manager_employee_fk
                WHERE ' . implode(' AND ', $where);

        $st = $pdo->prepare('SELECT COUNT(*) '. $sqlBase);
        $st->execute($bindings);
        $total = (int)$st->fetchColumn();

        $sql = 
            '   SELECT
                    employee.id_employee AS id_employee,
                    employee.code_employee AS code_employee,
                    employee.name_employee AS name_employee,
                    employee.date_hired AS date_hired,
                    employee.status_employee AS status_employee,
                    employee.type_employee AS type_employee,
                    employee.seniority_employee AS seniority_employee,
                    employee.id_user_fk AS id_user_fk,
                    users.id_user, users.name_user, positions.id_position, positions.name_position,
                    department.id_department, department.name_department, department.id_manager_employee_fk,
                    manager.id_employee AS id_manager,
                    manager.name_employee AS name_manager
                ' . $sqlBase . '
                ORDER BY ' . $orderBy . '
                LIMIT :limit OFFSET :offset';
        $st = $pdo->prepare($sql);
        $bindings[':limit'] = $limit;
        $bindings[':offset'] = $offset;
        $st->execute($bindings);
        $employees = $st->fetchAll(\PDO::FETCH_ASSOC);
        return ['employees' => $employees, 'total' => $total];
    }
}