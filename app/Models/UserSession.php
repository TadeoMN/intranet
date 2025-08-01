<?php
namespace App\Models;

class UserSession extends Model {
    protected static string $table='user_sessions';
    protected static string $primary='id_session';
    protected static array  $fillable=[
        'id_user_session_fk','token_session','ip_addr_session',
        'login_at','logout_at','is_active'
    ];

    public static function sessionActivate(int $uid): ?array {
        $pdo = \Core\Database::pdo();
        $sql = 
            ('  SELECT *
                FROM user_sessions
                INNER JOIN users ON users.id_user = user_sessions.id_user_session_fk
                WHERE id_user_session_fk = :id_user_session_fk
                    AND is_active = 1
                LIMIT 1');
        $st = $pdo->prepare($sql);
        $st->bindParam(':id_user_session_fk', $uid, \PDO::PARAM_INT);
        $st->execute();
        return $st->fetch() ?: null;
    }

    public static function deactivate(string $token_session): void {
        $pdo = \Core\Database::pdo();
        $sql =
            ('  SELECT id_session, id_user_session_fk
                FROM user_sessions
                WHERE token_session = :token_session
                LIMIT 1');
        $st = $pdo->prepare($sql);
        $st->bindParam(':token_session', $token_session, \PDO::PARAM_STR);
        $st->execute();

        $row  = $st->fetch();
        if (!$row) return;

        $uid  = $row['id_user_session_fk'];

        $sql = 
            ('  DELETE
                FROM user_sessions
                WHERE id_user_session_fk = :id_user_session_fk
                    AND is_active = 0');
        $del = $pdo->prepare($sql);
        $del->bindParam(':id_user_session_fk', $uid, \PDO::PARAM_INT);
        $del->execute();

        $sql = 
            ('  UPDATE user_sessions
                SET is_active = 0
                WHERE id_session = :id_session');
        $upd = $pdo->prepare($sql);
        $upd->bindParam(':id_session', $row['id_session'], \PDO::PARAM_INT);
        $upd->execute();
    }

    public static function forceClose(int $id_session): bool {
        $pdo = \Core\Database::pdo();

        $pdo->beginTransaction();

        $sql = 
            ('  DELETE
                FROM user_sessions
                WHERE id_session = :id_session
                    AND is_active = 0');
        $del = $pdo->prepare($sql);
        $del->bindParam(':id_session', $id_session, \PDO::PARAM_INT);
        $del->execute();

        $sql = 
            ('  UPDATE user_sessions
                SET is_active = 0
                WHERE id_session = :id_session
                    AND is_active = 1');
        $upd = $pdo->prepare($sql);
        $upd->bindParam(':id_session', $id_session, \PDO::PARAM_INT);
        $upd->execute();

        $pdo->commit();
        return true;
    }

    public static function allSessionActivate(): array {
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

    public static function historySession(): array {
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