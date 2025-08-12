<?php
namespace App\Models;

/**
 * Employee Model
 * This class represents the employee entity and provides methods to interact with the employee database table.
 * It includes methods for finding, filtering, creating, updating, and deleting employees.
 */
class Employee extends Model {
    protected static string $table = 'employee';
    protected static string $primary = 'id_employee';
    protected static array  $fillable = [
      'name_employee', 'date_hired', 'status_employee',
      'type_employee', 'seniority_employee', 'id_position_fk'
    ];

    /**
     * Find an employee by ID.
     * @param int $id_employee Employee ID.
     * @return array|null Employee data or null if not found.
     * @throws \Exception If the employee is not found.
     * @description This function retrieves an employee's details by their ID, including related user and
     * position information. It returns an associative array with employee details or null if the employee
     * does not exist.
     */
    public static function findById(int $id_employee): ?array {
        $pdo = \Core\Database::pdo();
        $sql = 
            '   SELECT *
                FROM employee
                INNER JOIN users ON users.id_employee_fk = employee.id_employee
                INNER JOIN positions ON positions.id_position = employee.id_position_fk
                INNER JOIN department ON department.id_department = positions.id_department_fk
                INNER JOIN employee_profile ON employee_profile.id_employee_fk = employee.id_employee
                INNER JOIN contracts ON contracts.id_employee_fk = employee.id_employee
                WHERE id_employee = :id_employee
                LIMIT 1';
        $st = $pdo->prepare($sql);
        $st->bindParam(':id_employee', $id_employee, \PDO::PARAM_INT);
        $st->execute();
        return $st->fetch() ?: null;
    }

    /**
     * Find an employee by ID (simple version without joins).
     * @param int $id_employee Employee ID.
     * @return array|null Employee data or null if not found.
     */
    public static function findByIdSimple(int $id_employee): ?array {
        $pdo = \Core\Database::pdo();
        $sql = 'SELECT * FROM employee WHERE id_employee = :id_employee LIMIT 1';
        $st = $pdo->prepare($sql);
        $st->bindParam(':id_employee', $id_employee, \PDO::PARAM_INT);
        $st->execute();
        return $st->fetch() ?: null;
    }
    /**
     * Filter and paginate employees based on various criteria.
     * @param ?string $search Search term for employee name, code, status, type, etc.
     * @param ?string $dateFrom Start date for filtering by hiring date.
     * @param ?string $dateTo End date for filtering by hiring date.
     * @param int $limit Number of employees per page.
     * @param int $offset Offset for pagination.
     * @param string $sort Column to sort by.
     * @param string $order Sort order (asc or desc).
     * @param ?string $status Employee status to filter by (e.g., 'ACTIVO', 'INACTIVO').
     * @return array Array containing filtered employees and total count.
     * @throws \Exception If there is an error during the database query.
     * @description This function retrieves a paginated list of employees based on the provided search criteria
     * and filters. It supports searching by employee attributes, filtering by hiring dates, and sorting
     * by various columns. The results include employee details along with their associated user, position,
     * and department information. The function returns an array with the filtered employees and the total count
     * of employees matching the criteria.
     */
    public static function filterPaginated(?string $search, ?string $dateFrom, ?string $dateTo, int $limit, int $offset, string $sort, string $order, ?string $status): array {
        $pdo = \Core\Database::pdo();

        if ($status === null) { $status = 'ACTIVO'; }

        $where = [];
        $bindings = [];


        if ($status !== 'TODOS') {
            $where[] = 'employee.status_employee = :status';
            $bindings[':status'] = $status;
        }

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
                OR LOWER(contracts.number_payroll_contract) LIKE :search10
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
                ':search9' => $likeSearch,
                ':search10' => $likeSearch
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
            'type_employee', 'seniority_employee', 'name_user', 'name_position',
            'name_department', 'name_manager', 'number_payroll_contract'
        ];
        $orderBy = in_array($sort, $allowedSorts, true) ? $sort : 'code_employee';
        $orderDirection = $order === 'desc' ? 'DESC' : 'ASC';
        $orderBy = $orderBy . ' ' . $orderDirection;

        $sqlBase = 
            '   FROM employee
                INNER JOIN users ON users.id_employee_fk = employee.id_employee
                INNER JOIN positions ON positions.id_position = employee.id_position_fk
                INNER JOIN department ON department.id_department = positions.id_department_fk
                LEFT JOIN employee AS manager ON manager.id_employee = department.id_manager_employee_fk
                LEFT JOIN employee_profile ON employee_profile.id_employee_fk = employee.id_employee
                LEFT JOIN contracts ON contracts.id_employee_fk = employee.id_employee
                WHERE ' . (count($where) ? implode(' AND ', $where) : '1=1');

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
                    users.id_employee_fk, users.id_user, users.name_user,
                    positions.id_position, positions.name_position,
                    department.id_department, department.name_department, department.id_manager_employee_fk,
                    manager.id_employee AS id_manager,
                    manager.name_employee AS name_manager,
                    contracts.id_contract, contracts.number_payroll_contract
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
    /**
     * Delete an employee by ID.
     * @param int $id_employee Employee ID.
     * @return bool True if the employee was deleted, false otherwise.
     * @throws \Exception If there is an error during the deletion.
     * @description This function marks an employee as inactive by updating their status and the associated user status.
     * It returns true if the deletion was successful, otherwise false.
     */
    public static function deleteById(int $id_employee): bool {
        $pdo = \Core\Database::pdo();
        $sql = 
            '   UPDATE employee
                INNER JOIN users ON users.id_employee_fk = employee.id_employee
                SET
                    status_employee = "INACTIVO",
                    status_user = "INACTIVO"
                WHERE id_employee = :id_employee';
        $st = $pdo->prepare($sql);
        $st->bindParam(':id_employee', $id_employee, \PDO::PARAM_INT);
        $st->execute();
        if ($st->rowCount() === 0) {
            throw new \Exception('Error deleting employee');
        }
        return $st->rowCount() > 0;
    }
    /**
     * Create a new employee.
     * @param array $data Employee data.
     * @return int The ID of the newly created employee.
     * @throws \Exception If there is an error during the creation.
     * @description This function inserts a new employee into the database and returns the ID of the newly created employee.
     * It requires the employee's name, hiring date, type, and position ID. If the insertion fails,
     * it throws an exception.
     */
    public static function create(array $data): int {
        $pdo = \Core\Database::pdo();
        $sql = 
            '   INSERT INTO
                    employee (name_employee, date_hired, type_employee, id_position_fk)
                VALUES
                    (:name_employee, :date_hired, :type_employee, :id_position_fk)';
        $st = $pdo->prepare($sql);
        $st->bindParam(':name_employee', $data['name_employee'], \PDO::PARAM_STR);
        $st->bindParam(':date_hired', $data['date_hired'], \PDO::PARAM_STR);
        $st->bindParam(':type_employee', $data['type_employee'], \PDO::PARAM_STR);
        $st->bindParam(':id_position_fk', $data['id_position_fk'], \PDO::PARAM_INT);
        $st->execute();
        if ($st->rowCount() === 0) {
            throw new \Exception('Error creating employee');
        }
        return (int)$pdo->lastInsertId();
    }
    /**
     * Update an employee by ID.
     * @param int $id_employee Employee ID.
     * @param array $data Employee data to update.
     * @return bool True if the employee was updated, false otherwise.
     * @throws \Exception If there is an error during the update.
     * @description This function updates an existing employee's details in the database based on their ID.
     * It requires the employee's name, hiring date, status, type, and position ID. If the update fails,
     * it throws an exception.
     */
    public static function updateById(int $id_employee, array $data): bool {
        $pdo = \Core\Database::pdo();
        $sql = 
            '   UPDATE employee
                SET
                    name_employee = :name_employee,
                    date_hired = :date_hired,
                    status_employee = :status_employee,
                    type_employee = :type_employee,
                    id_position_fk = :id_position_fk
                WHERE id_employee = :id_employee';
        $st = $pdo->prepare($sql);
        $st->bindParam(':name_employee', $data['name_employee'], \PDO::PARAM_STR);
        $st->bindParam(':date_hired', $data['date_hired'], \PDO::PARAM_STR);
        $st->bindParam(':status_employee', $data['status_employee'], \PDO::PARAM_STR);
        $st->bindParam(':type_employee', $data['type_employee'], \PDO::PARAM_STR);
        $st->bindParam(':id_position_fk', $data['id_position_fk'], \PDO::PARAM_INT);
        $st->bindParam(':id_employee', $id_employee, \PDO::PARAM_INT);
        $st->execute();
        if ($st->rowCount() === 0) {
            throw new \Exception('Error updating employee');
        }
        return $st->rowCount() > 0;
    }
}