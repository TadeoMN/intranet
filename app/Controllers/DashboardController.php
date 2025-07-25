<?php
namespace App\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\UserSession;
use function view, redirect;

class DashboardController {
    public function index() {
        if (!\Core\Auth::check()) {
            flash('info','Autenticación requerida','Por favor inicia sesión');
            return redirect('/');
        }

        $users       = User::all();              // solo activos
        $roles       = Role::all();              // siempre visibles
        $permissions = Permission::all();        // idem

        $session     = UserSession::activeUser($_SESSION['uid'] ?? 0);
        $active   = UserSession::activeForUser();
        $history  = UserSession::historyForUser();

        return view('dashboard/dashboard', compact('users', 'roles', 'permissions', 'active', 'history', 'session'));
    }
}