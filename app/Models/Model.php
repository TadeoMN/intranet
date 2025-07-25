<?php

namespace App\Models;

use Core\Database;
use PDO;

/**
 * Mini-ORM ActiveRecord style.
 * Deriva cada modelo e indica $table y $fillable.
 */
abstract class Model
{
  protected static string $table     = '';
  protected static string $primary   = 'id';
  protected static array  $fillable  = [];

  /* ====== CRUD estático ====== */
  public static function all(): array {
    $sql = 'SELECT * FROM '.static::$table;
    return Database::pdo()->query($sql)->fetchAll();
  }

  public static function find($id): ?array {
    $sql = 'SELECT * FROM '.static::$table.' WHERE '.static::$primary.' = ? LIMIT 1';
    $stmt = Database::pdo()->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
  }

  public static function create(array $data): int {
    $cols = array_intersect(static::$fillable, array_keys($data));
    $placeholders = implode(',', array_fill(0, count($cols), '?'));
    $sql = 'INSERT INTO '.static::$table.' ('.implode(',',$cols).') VALUES ('.$placeholders.')';
    $stmt = Database::pdo()->prepare($sql);
    $stmt->execute(array_values(array_intersect_key($data, array_flip($cols))));
    return (int) Database::pdo()->lastInsertId();
  }

  public static function update($id, array $data): bool {
    $cols = array_intersect(static::$fillable, array_keys($data));
    $set  = implode(', ', array_map(fn($c)=>"$c = ?", $cols));
    $sql  = 'UPDATE '.static::$table.' SET '.$set.' WHERE '.static::$primary.' = ?';
    $stmt = Database::pdo()->prepare($sql);
    return $stmt->execute([...array_values(array_intersect_key($data, array_flip($cols))), $id]);
  }

  public static function delete($id): bool {
    $sql = 'UPDATE '.static::$table.' SET is_active = 0 WHERE '.static::$primary.' = ? LIMIT 1';
    $stmt = Database::pdo()->prepare($sql);
    return $stmt->execute([$id]);
  }
}