<?php
namespace Core;
use App\Models\User, App\Models\UserSession;

class Auth {
    public static function attempt(string $name, string $password, ?string &$reason = null): bool {
        $user = User::findByName($name);

        if (!$user || $user['status_user'] !== 'ACTIVO') {
            $reason = 'blocked';
            return false;
        }

        if (!password_verify($password, $user['password_hash_user'])) {
            $reason = 'invalid';
            return false;
        }

        if (UserSession::activeUser($user['id_user'])) {
            $reason = 'active';
            return false;
        }

        $token_session = bin2hex(random_bytes(32));

        UserSession::create([
            'id_user_session_fk'  => $user['id_user'],
            'token_session'       => $token_session,
            'ip_addr_session'     => $_SERVER['REMOTE_ADDR'],
            'login_at'            => date('Y-m-d H:i:s'),
            'is_active'           => 1
        ]);

        $_SESSION['uid']   = $user['id_user'];
        $_SESSION['email'] = $user['email_user'];
        $_SESSION['name']  = $user['name_user'];
        $_SESSION['token_session'] = $token_session;
        $_SESSION['status_user'] = $user['status_user'];

        session_regenerate_id(true);
        $reason = 'ok';
        return true;
    }

    public static function logout(): void {
        if(isset($_SESSION['token_session'])) {
            UserSession::deactivate($_SESSION['token_session']);
        }
        session_destroy();  // destruye sesión   
    }

    public static function check(): bool {
        return isset($_SESSION['uid']);
    }
}