<?php 
namespace App\Models;

/**
 * EmployeeProfile Model
 * This class represents the employee profile entity and provides methods to interact with the employee profile database table.
 * It includes methods for retrieving employee profiles by employee ID.
 */
class EmployeeProfile extends Model {
    protected static string $table = 'employee_profile';
    protected static string $primary = 'id_profile';
    protected static array  $fillable = [
      'id_employee_profile',
      'id_employee_fk',
      'image_employee_profile',
      'birthdate_employee_profile',
      'gender_employee_profile',
      'blood_type_employee_profile',
      'marital_status_employee_profile',
      'curp_employee_profile',
      'rfc_employee_profile',
      'nss_employee_profile',
      'account_number_employee_profile',
      'bank_employee_profile',
      'phone_employee_profile',
      'mobile_employee_profile',
      'email_employee_profile',
      'address_employee_profile',
      'emergency_contact_employee_profile',
      'emergency_phone_employee_profile',
      'emergency_relationship_employee_profile',
      'digital_file_employee_profile',
    ];

    /**
     * Get the employee profile by employee ID.
     * @param int $id_employee Employee ID.
     * @return array|null Employee profile data or null if not found.
     */
    public static function getByEmployeeId(int $id_employee): ?array {
        $pdo = \Core\Database::pdo();
        $sql = "SELECT * FROM " . static::$table . " WHERE id_employee_fk = :id_employee";
        $st = $pdo->prepare($sql);
        $st->bindParam(':id_employee', $id_employee, \PDO::PARAM_INT);
        $st->execute();
        return $st->fetch(\PDO::FETCH_ASSOC) ?: null;
    }
    /**
     * Create a new employee profile.
     * @param array $data Employee profile data.
     * @return int The ID of the newly created employee profile.
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
     * Update an employee profile by employee ID.
     * @param int $id_employee Employee ID.
     * @param array $data Employee profile data to update.
     * @return bool True if the profile was updated, false otherwise.
     */
    public static function updateByEmployeeId(int $id_employee, array $data): bool {
        $pdo = \Core\Database::pdo();
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
     * Get the employee profile enums for a specific column.
     * @param string $column The column name to get enums for.
     * @return array An array of enum values for the specified column.
     * @throws \Exception If there is an error during the query.
     * @description This function retrieves the enum values for a specific column in the employee profile table.
     * It returns an array of enum values or an empty array if the column does not exist or has no enum values.
     */
    public static function getEmployeeProfileEnums($column): array {
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