<?php 
namespace App\Models;

use Core\Database;

class Contracts extends Model {
    protected static string $table = 'contracts';
    protected static string $primary = 'id_contract';
    protected static array  $fillable = [
      'id_contract',
      'id_employee_fk',
      'number_payroll_contract',
      'code_employee_snapshot',
      'id_contract_type_fk',
      'id_payroll_scheme_fk',
      'start_date_contract',
      'trial_period_contract',
      'end_date_contract',
      'salary_contract',
      'termination_reason_contract',
      'is_active',
    ];

    /**
     * Get the contract by employee ID.
     * @param int $id_employee Employee ID.
     * @return array|null Contract data or null if not found.
     */
    public static function getByEmployeeId(int $id_employee): ?array {
        $pdo = Database::pdo();
        $sql =
            "   SELECT *
                FROM " . static::$table . "
                INNER JOIN contract_type ON contracts.id_contract_type_fk = contract_type.id_contract_type
                INNER JOIN payroll_scheme ON contracts.id_payroll_scheme_fk = payroll_scheme.id_payroll_scheme
                WHERE id_employee_fk = :id_employee";
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
    public static function getContractsEnums($column): array {
        $pdo = \Core\Database::pdo();
        $sql = "SHOW COLUMNS FROM " . static::$table . " LIKE '{$column}'";
        $st = $pdo->prepare($sql);
        $st->execute();
        $result = $st->fetchAll(\PDO::FETCH_ASSOC);
        if ($result) {
            $enums = [];
            foreach ($result as $row) {
                preg_match('/^enum\((.*)\)$/', $row['Type'], $matches);
                if (isset($matches[1])) {
                    $enums[$row['Field']] = array_map(function($value) {
                        return trim($value, "'");
                    }, explode(',', $matches[1]));
                }
            }
            return $enums[$column] ?? [];
        }
        return [];
    }
}