<?php
namespace Core;
final class Router {
    private array $routes = [];
    private array $paramsRoutes = [];

    public function get($url, $action) {
        $this->addRoute('GET', $url, $action);
    }

    public function post($url, $action) {
        $this->addRoute('POST', $url, $action);
    }

    private function addRoute($method, $url, $action) {
        if (strpos($url, '{') !== false) {
            $pattern = preg_replace('/\{[^}]+\}/', '([^/]+)', $url);
            $this->paramsRoutes[$method][] = [
                'pattern' => '#^' . $pattern . '$#',
                'action' => $action,
                'original' => $url
            ];
        } else {
            $this->routes[$method][$url] = $action;
        }

    }

    public function dispatch($method, $uri) {
        $uri = rtrim(explode('?', $uri)[0], '/') ?: '/';

        if (isset($this->routes[$method][$uri])) {
            $action = $this->routes[$method][$uri];
            return $this->executeAction($action);
        }

        if (isset($this->paramsRoutes[$method])) {
            foreach ($this->paramsRoutes[$method] as $route) {
                if (preg_match($route['pattern'], $uri, $matches)) {
                    array_shift($matches); // Remove full match
                    $action = $route['action'];
                    return $this->executeAction($action, $matches);
                }
            }
        }

        http_response_code(404);
        return '404 Route Not Found';
    }

    private function executeAction($action, $params = []) {
        [$controller, $method] = explode('@', $action);
        $controllerClass = "App\\Controllers\\$controller";

        if (!class_exists($controllerClass)) {
            http_response_code(500);
            exit("Controller $controllerClass not found");
        }

        $instance = new $controllerClass();

        if (!method_exists($instance, $method)) {
            http_response_code(500);
            exit("Method $method not found in controller $controllerClass");
        }

        return call_user_func_array([$instance, $method], $params);
    }
}