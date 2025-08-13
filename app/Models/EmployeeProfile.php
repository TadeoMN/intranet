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
      'birthdate_employee_profile',
      'gender_employee_profile',
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
      'emergency_relationship_employee_profile'
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
     * Find employee profile by employee ID.
     * @param int $id_employee Employee ID.
     * @return array|null Employee profile data or null if not found.
     */
    public static function findByEmployee(int $id_employee): ?array {
        return static::getByEmployeeId($id_employee);
    }

    /**
     * Check if employee profile exists for employee ID.
     * @param int $id_employee Employee ID.
     * @return bool True if profile exists, false otherwise.
     */
    public static function existsForEmployee(int $id_employee): bool {
        $pdo = \Core\Database::pdo();
        $sql = "SELECT 1 FROM " . static::$table . " WHERE id_employee_fk = :id_employee LIMIT 1";
        $st = $pdo->prepare($sql);
        $st->bindParam(':id_employee', $id_employee, \PDO::PARAM_INT);
        $st->execute();
        return (bool)$st->fetch();
    }

    /**
     * Upsert employee profile (insert if not exists, update if exists).
     * @param int $id_employee Employee ID.
     * @param array $data Employee profile data.
     * @return bool True if operation was successful, false otherwise.
     */
    public static function upsertForEmployee(int $id_employee, array $data): bool {
        // Add employee FK to data
        $data['id_employee_fk'] = $id_employee;
        
        if (static::existsForEmployee($id_employee)) {
            // Update existing profile
            unset($data['id_employee_fk']); // Don't update FK
            return static::updateByEmployeeId($id_employee, $data);
        } else {
            // Insert new profile
            $profileId = static::create($data);
            return $profileId > 0;
        }
    }
}