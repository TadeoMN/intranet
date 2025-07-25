<?php
namespace Core;

final class Router {
    private array $routes = [];
    private array $paramRoutes = [];
    
    public function get($url, $action) {
        $this->addRoute('GET', $url, $action);
    }
    
    public function post($url, $action) {
        $this->addRoute('POST', $url, $action);
    }
    
    /**
     * Add route with parameter support optimization
     * Agregar ruta con optimización de soporte de parámetros
     */
    private function addRoute($method, $url, $action) {
        if (strpos($url, '{') !== false) {
            // Store parameterized routes separately for better performance
            // Almacenar rutas parametrizadas por separado para mejor rendimiento
            $pattern = preg_replace('/\{[^}]+\}/', '([^/]+)', $url);
            $this->paramRoutes[$method][] = [
                'pattern' => '#^' . $pattern . '$#',
                'action' => $action,
                'original' => $url
            ];
        } else {
            // Direct lookup for static routes
            // Búsqueda directa para rutas estáticas
            $this->routes[$method][$url] = $action;
        }
    }
    
    public function dispatch($method, $uri) {
        $uri = rtrim(explode('?', $uri)[0], '/') ?: '/';
        
        // Check static routes first (faster lookup)
        // Verificar rutas estáticas primero (búsqueda más rápida)
        if (isset($this->routes[$method][$uri])) {
            $action = $this->routes[$method][$uri];
            return $this->executeAction($action);
        }
        
        // Check parameterized routes
        // Verificar rutas parametrizadas
        if (isset($this->paramRoutes[$method])) {
            foreach ($this->paramRoutes[$method] as $route) {
                if (preg_match($route['pattern'], $uri, $matches)) {
                    array_shift($matches); // Remove full match
                    $action = $route['action'];
                    return $this->executeAction($action, $matches);
                }
            }
        }
        
        // Route not found
        // Ruta no encontrada
        http_response_code(404);
        exit('404 - Route not found');
    }
    
    /**
     * Execute controller action with optional parameters
     * Ejecutar acción del controlador con parámetros opcionales
     */
    private function executeAction($action, $params = []) {
        [$controller, $method] = explode('@', $action);
        $controllerClass = "\\App\\Controllers\\$controller";
        
        if (!class_exists($controllerClass)) {
            http_response_code(500);
            exit('Controller not found');
        }
        
        $instance = new $controllerClass();
        
        if (!method_exists($instance, $method)) {
            http_response_code(500);
            exit('Method not found');
        }
        
        // Call method with parameters
        // Llamar método con parámetros
        return call_user_func_array([$instance, $method], $params);
    }
}