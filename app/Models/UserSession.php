<?php
namespace App\Models;

class UserSession extends Model {
    protected static string $table='user_sessions';
    protected static string $primary='id_session';
    protected static array  $fillable=[
        'id_user_session_fk','token_session','ip_addr_session',
        'login_at','logout_at','is_active'
    ];

    public static function activeUser(int $uid): ?array {
        $pdo = \Core\Database::pdo();
        $sql =
            ('  SELECT *
                FROM user_sessions
                WHERE id_user_session_fk = :id_user_session_fk
                    AND is_active = 1
                LIMIT 1');
        $st = $pdo->prepare($sql);
        $st->bindParam(':id_user_session_fk', $uid, \PDO::PARAM_INT);
        $st->execute();
        return $st->fetch() ? : null;
    }

    public static function deactivate(string $token_session): void {
        $pdo = \Core\Database::pdo();
        
        // Single optimized query with transaction
        // Consulta única optimizada con transacción
        $pdo->beginTransaction();
        
        try {
            // Get session info and update in one query
            // Obtener información de sesión y actualizar en una consulta
            $sql = "
                UPDATE user_sessions us1
                SET is_active = 0,
                    logout_at = NOW()
                WHERE token_session = :token_session 
                AND is_active = 1
            ";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':token_session', $token_session, \PDO::PARAM_STR);
            $stmt->execute();
            
            // Clean up old inactive sessions for the same user in batch
            // Limpiar sesiones inactivas antiguas para el mismo usuario en lote
            $cleanupSql = "
                DELETE us2 FROM user_sessions us2
                INNER JOIN user_sessions us1 ON us1.id_user_session_fk = us2.id_user_session_fk
                WHERE us1.token_session = :token_session 
                AND us2.is_active = 0 
                AND us2.logout_at < DATE_SUB(NOW(), INTERVAL 30 DAY)
            ";
            $cleanupStmt = $pdo->prepare($cleanupSql);
            $cleanupStmt->bindParam(':token_session', $token_session, \PDO::PARAM_STR);
            $cleanupStmt->execute();
            
            $pdo->commit();
        } catch (\Exception $e) {
            $pdo->rollback();
            throw $e;
        }
    }

    public static function forceClose(int $id_session): bool {
        $pdo = \Core\Database::pdo();

        $pdo->beginTransaction();
        
        try {
            // Single optimized query to close session and clean up
            // Consulta única optimizada para cerrar sesión y limpiar
            $sql = "
                UPDATE user_sessions 
                SET is_active = 0, 
                    logout_at = NOW()
                WHERE id_session = :id_session 
                AND is_active = 1
            ";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id_session', $id_session, \PDO::PARAM_INT);
            $updated = $stmt->execute();
            
            // Optional: Clean up old inactive sessions for the same user
            // Opcional: Limpiar sesiones inactivas antiguas para el mismo usuario
            $cleanupSql = "
                DELETE FROM user_sessions 
                WHERE id_user_session_fk = (
                    SELECT id_user_session_fk 
                    FROM (SELECT id_user_session_fk FROM user_sessions WHERE id_session = :id_session) AS temp
                )
                AND is_active = 0 
                AND logout_at < DATE_SUB(NOW(), INTERVAL 30 DAY)
            ";
            $cleanupStmt = $pdo->prepare($cleanupSql);
            $cleanupStmt->bindParam(':id_session', $id_session, \PDO::PARAM_INT);
            $cleanupStmt->execute();

            $pdo->commit();
            return $updated;
        } catch (\Exception $e) {
            $pdo->rollback();
            return false;
        }
    }

    public static function activeForUser(): array {
        $pdo = \Core\Database::pdo();
        $sql = 
            ('  SELECT *
                FROM user_sessions
                INNER JOIN users
                    ON users.id_user = user_sessions.id_user_session_fk
                WHERE is_active =1
                ORDER BY login_at DESC');
        $st = $pdo->prepare($sql);
        $st->execute();
        return $st->fetchAll() ?: [];
    }

    public static function historyForUser(): array {
        $pdo = \Core\Database::pdo();
        $sql = 
            ('  SELECT *
                FROM session_history 
                INNER JOIN users
                    ON users.id_user = session_history.id_user_session_fk
                ORDER BY login_at DESC');
        $st = $pdo->prepare($sql);
        $st->execute();
        return $st->fetchAll() ?: [];
    }
}