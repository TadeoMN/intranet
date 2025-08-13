<?php 
namespace App\Models;

use Core\Database;

class Contracts extends Model {
    protected static string $table = 'contracts';
    protected static string $primary = 'id_contract';
    protected static array  $fillable = [
      'id_contract',
      'id_employee_fk',
      'number_payrroll_contract',
      'code_employee_snapshot',
      'id_contract_type_fk',
      'id_payroll_scheme_fk',
      'start_date_contract',
      'trial_period_contract',
      'end_date_contract',
      'salary_contract',
      'termination_clause_contract',
      'is_active',
    ];

    /**
     * Get the contract by employee ID.
     * @param int $id_employee Employee ID.
     * @return array|null Contract data or null if not found.
     */
    public static function getByEmployeeId(int $id_employee): ?array {
        $pdo = Database::pdo();
        $sql = "SELECT * FROM " . static::$table . " WHERE id_employee_fk = :id_employee";
        $st = $pdo->prepare($sql);
        $st->bindParam(':id_employee', $id_employee, \PDO::PARAM_INT);
        $st->execute();
        return $st->fetch(\PDO::FETCH_ASSOC) ?: null;

    }
    /**
     * Create a new contract.
     * @param array $data Contract data.
     * @return int The ID of the newly created contract.
     */
    public static function create(array $data): int {
        $pdo = \Core\Database::pdo();
        $sql = "INSERT INTO " . static::$table . " (" . implode(',', array_keys($data)) . ") VALUES (:" . implode(',:', array_keys($data)) . ")";
        $st = $pdo->prepare($sql);
        foreach ($data as $key => $value) {
            $st->bindValue(':' . $key, $value);
        }
        $st->execute();
        return (int)$pdo->lastInsertId();
    }

    /**
     * Find contract by employee ID.
     * @param int $id_employee Employee ID.
     * @return array|null Contract data or null if not found.
     */
    public static function findByEmployee(int $id_employee): ?array {
        return static::getByEmployeeId($id_employee);
    }

    /**
     * Check if contract exists for employee ID.
     * @param int $id_employee Employee ID.
     * @return bool True if contract exists, false otherwise.
     */
    public static function existsForEmployee(int $id_employee): bool {
        $pdo = Database::pdo();
        $sql = "SELECT 1 FROM " . static::$table . " WHERE id_employee_fk = :id_employee LIMIT 1";
        $st = $pdo->prepare($sql);
        $st->bindParam(':id_employee', $id_employee, \PDO::PARAM_INT);
        $st->execute();
        return (bool)$st->fetch();
    }

    /**
     * Update contract by employee ID.
     * @param int $id_employee Employee ID.
     * @param array $data Contract data to update.
     * @return bool True if the contract was updated, false otherwise.
     */
    public static function updateByEmployeeId(int $id_employee, array $data): bool {
        $pdo = Database::pdo();
        $setClause = implode(', ', array_map(fn($key) => "$key = :$key", array_keys($data)));
        $sql = "UPDATE " . static::$table . " SET $setClause WHERE id_employee_fk = :id_employee";
        $st = $pdo->prepare($sql);
        foreach ($data as $key => $value) {
            $st->bindValue(':' . $key, $value);
        }
        $st->bindValue(':id_employee', $id_employee, \PDO::PARAM_INT);
        return $st->execute();
    }

    /**
     * Upsert contract (insert if not exists, update if exists).
     * @param int $id_employee Employee ID.
     * @param array $data Contract data.
     * @return bool True if operation was successful, false otherwise.
     */
    public static function upsertForEmployee(int $id_employee, array $data): bool {
        // Add employee FK to data
        $data['id_employee_fk'] = $id_employee;
        
        if (static::existsForEmployee($id_employee)) {
            // Update existing contract
            unset($data['id_employee_fk']); // Don't update FK
            return static::updateByEmployeeId($id_employee, $data);
        } else {
            // Insert new contract
            $contractId = static::create($data);
            return $contractId > 0;
        }
    }
}