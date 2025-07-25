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

        // Use optimized dashboard service instead of multiple separate queries
        // Usar servicio de dashboard optimizado en lugar de múltiples consultas separadas
        $dashboardData = DashboardService::getDashboardData($_SESSION['uid'] ?? 0);
        
        // Extract data for backward compatibility with existing view
        // Extraer datos para compatibilidad con la vista existente
        extract($dashboardData);

        return view('dashboard/dashboard', compact('users', 'roles', 'permissions', 'active', 'history', 'session'));
    }
}