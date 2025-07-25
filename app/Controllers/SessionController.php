<?php
namespace App\Controllers;

use App\Models\UserSession;
use function view, redirect, flash;

class SessionController {
    public function close() {
        header('Content-Type: application/json');
        if (!\Core\Auth::check()) {
            echo json_encode(['ok'=>false,'msg'=>'auth']);
            return;
        }

        $id_session = $_POST['id_session'] ?? 0;
        $ok = UserSession::forceClose($id_session);
        echo json_encode(['ok'=>$ok]);
    }
}