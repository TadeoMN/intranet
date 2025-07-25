<?php
namespace App\Services;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\UserSession;
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
        $pdo = Database::pdo();
        
        // Single optimized query to get user session data
        // Consulta única optimizada para obtener datos de sesión del usuario
        $sessionSql = "
            SELECT 
                us1.*,
                u.name_user,
                u.email_user,
                'current' as session_type
            FROM user_sessions us1
            INNER JOIN users u ON u.id_user = us1.id_user_session_fk
            WHERE us1.id_user_session_fk = :user_id AND us1.is_active = 1
            LIMIT 1
        ";
        
        $sessionStmt = $pdo->prepare($sessionSql);
        $sessionStmt->execute(['user_id' => $userId]);
        $session = $sessionStmt->fetch() ?: null;
        
        // Optimized query for active sessions with user info
        // Consulta optimizada para sesiones activas con información de usuario
        $activeSql = "
            SELECT 
                us.id_session,
                us.login_at,
                us.ip_addr_session,
                u.name_user,
                u.email_user
            FROM user_sessions us
            INNER JOIN users u ON u.id_user = us.id_user_session_fk
            WHERE us.is_active = 1
            ORDER BY us.login_at DESC
            LIMIT 50
        ";
        
        $activeStmt = $pdo->prepare($activeSql);
        $activeStmt->execute();
        $active = $activeStmt->fetchAll();
        
        // Optimized query for session history
        // Consulta optimizada para historial de sesiones
        $historySql = "
            SELECT 
                sh.id_user_session_fk,
                sh.login_at,
                sh.logout_at,
                u.name_user,
                u.email_user
            FROM session_history sh
            INNER JOIN users u ON u.id_user = sh.id_user_session_fk
            ORDER BY sh.login_at DESC
            LIMIT 100
        ";
        
        $historyStmt = $pdo->prepare($historySql);
        $historyStmt->execute();
        $history = $historyStmt->fetchAll();
        
        // Get active users with minimal data
        // Obtener usuarios activos con datos mínimos
        $usersSql = "
            SELECT id_user, name_user, email_user, status_user
            FROM users 
            WHERE status_user = 1
            ORDER BY name_user
        ";
        
        $usersStmt = $pdo->prepare($usersSql);
        $usersStmt->execute();
        $users = $usersStmt->fetchAll();
        
        return [
            'users' => $users,
            'session' => $session,
            'active' => $active,
            'history' => $history
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