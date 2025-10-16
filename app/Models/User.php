<?php
namespace App\Models;

class User extends Model {
    protected static string $table='users';
    protected static string $primary='id_user';
    protected static array  $fillable=['name_user','password_hash_user','status_user', 'id_employee_fk'];

    public static function findByEmail(string $email): ?array {
        $pdo = \Core\Database::pdo();
        $sql = 
            '   SELECT *
                FROM users
                WHERE email_user = :email_user
                LIMIT 1';
        $st=$pdo->prepare($sql);
        $st->bindParam(':email_user', $email, \PDO::PARAM_STR);
        $st->execute();
        return $st->fetch() ?: null;
    }

    public static function findByName(string $name): ?array {
        $pdo = \Core\Database::pdo();
        $sql = 
            '   SELECT *
                FROM users
                WHERE name_user = :name_user
                LIMIT 1';
        $st=$pdo->prepare($sql);
        $st->bindParam(':name_user', $name, \PDO::PARAM_STR);
        $st->execute();
        return $st->fetch() ?: null;
    }

    public static function findById(int $id): ?array {
        $pdo = \Core\Database::pdo();
        $sql = 
            '   SELECT *
                FROM users
                WHERE id_user = :id_user
                LIMIT 1';
        $st=$pdo->prepare($sql);
        $st->bindParam(':id_user', $id, \PDO::PARAM_INT);
        $st->execute();
        return $st->fetch() ?: null;
    }

    public static function findAllActive(): array {
        $pdo = \Core\Database::pdo();
        $sql = 
            '   SELECT *
                FROM users
                WHERE status_user = 1
                ORDER BY name_user';
        $st = $pdo->query($sql);
        return $st->fetchAll() ?: [];
    }

    public static function findUserWithEmployee(int $id): ?array {
        $pdo = \Core\Database::pdo();
        $sql = 
            '   SELECT users.*, employee.*
                FROM users
                INNER JOIN employee ON users.id_employee_fk = employee.id_employee
                WHERE users.id_user = :id_user
                LIMIT 1';
        $st=$pdo->prepare($sql);
        $st->bindParam(':id_user', $id, \PDO::PARAM_INT);
        $st->execute();
        return $st->fetch() ?: null;
    }
}