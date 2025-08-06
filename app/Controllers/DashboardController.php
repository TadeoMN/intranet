<?php
namespace App\Controllers;

use App\Services\DashboardService;

use function view, redirect;

class DashboardController {
    public function index() {
        if (!\Core\Auth::check()) {
            flash('info','Autenticación requerida','Por favor inicia sesión');
            return redirect('/');
        }

        $dashboardData = DashboardService::getDashboardData($_SESSION['uid'] ?? 0);

        extract($dashboardData);

        return view('dashboard/dashboard', compact('users', 'roles', 'permissions', 'active', 'history', 'session',
            'departments', 'positionsByDepartment'));
    }
}