<?php
namespace App\Services;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\UserSession;
use App\Models\Department;
use App\Models\Positions;
use Core\Cache;
use Core\Database;

/**
 * Dashboard data service with optimized queries and caching
 * Servicio de datos del dashboard con consultas optimizadas y caché
 */
class DashboardService {

    /**
     * Get all dashboard data with optimized queries and caching
     * Obtener todos los datos del dashboard con consultas optimizadas y caché
     */
    public static function getDashboardData(int $userId): array {
        // Cache static data (roles and permissions) for 1 hour
        // Cachear datos estáticos (roles y permisos) por 1 hora
        $staticData = Cache::get('dashboard_static_data', function() {
            return [
                'roles' => Role::all(),
                'permissions' => Permission::all()
            ];
        }, 3600);

        // Get dynamic user and session data with optimized queries
        // Obtener datos dinámicos de usuario y sesión con consultas optimizadas
        $dynamicData = self::getDynamicDashboardData($userId);

        return array_merge($staticData, $dynamicData);
    }

    /**
     * Get dynamic data that shouldn't be cached
     * Obtener datos dinámicos que no deben ser cacheados
     */
    private static function getDynamicDashboardData(int $userId): array {
      
        $session = UserSession::sessionActivate($userId);
        $active = UserSession::allSessionActivate();
        $history = UserSession::historySession();
        $users = User::findAllActive();

        $departments = Department::all();
        $positions = Positions::all();
        $positionsByDepartment = [];

        foreach ($positions as $position) {
            $positionsByDepartment[$position['id_department_fk']][] = [
                'id_position' => $position['id_position'],
                'name_position' => $position['name_position']
            ];
        }

        return [
            'users' => $users,
            'session' => $session,
            'active' => $active,
            'history' => $history,
            'departments' => $departments,
            'positionsByDepartment' => $positionsByDepartment
        ];
    }

    /**
     * Clear dashboard cache when data changes
     * Limpiar caché del dashboard cuando cambien los datos
     */
    public static function clearCache(): void {
        Cache::forget('dashboard_static_data');
    }
}