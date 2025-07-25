<?php

  ini_set('display_errors', 1);
  error_reporting(E_ALL);
  
  date_default_timezone_set('America/Mexico_City');

  // Start performance monitoring / Iniciar monitoreo de rendimiento
  $startTime = microtime(true);

  require_once dirname(__DIR__).'/vendor/autoload.php';
  require_once dirname(__DIR__).'/core/Helpers.php';

  // Initialize performance monitoring / Inicializar monitoreo de rendimiento
  use Core\Performance;
  Performance::startTimer('app_bootstrap');

  // --- Sesión segura ---------------------------------
  $c = config('session');
  session_name($c['name']);
  session_set_cookie_params([
    'lifetime' => $c['ttl'],
    'path'     => '/',
    'domain'   => '',
    'secure'   => $c['secure'],
    'httponly' => true,
    'samesite' => 'Strict'
  ]);

  session_start();
  Performance::endTimer('app_bootstrap');

  // --- Router + rutas --------------------------------
  use Core\Router; 
  Performance::startTimer('routing');

  $router = new Router;
  $router->get('/',          'AuthController@showLogin');
  $router->post('/login',    'AuthController@login');
  $router->get('/logout',    'AuthController@logout');
  $router->get('/dashboard', 'DashboardController@index');
  $router->post('/sessions/close', 'SessionController@close');
  $router->get('/employees/list', 'EmployeeController@listEmployees');
  $router->get('/employees/view/{id}', 'EmployeeController@viewEmployee');
  $router->get('/employees/edit/{id}', 'EmployeeController@editEmployee');
  $router->post('/employees/update/{id}', 'EmployeeController@updateEmployee');
  $router->post('/employees/delete/{id}', 'EmployeeController@deleteEmployee');
  
  Performance::endTimer('routing');
  Performance::startTimer('controller_execution');
  
  $output = $router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
  
  Performance::endTimer('controller_execution');
  
  // Add performance headers and output / Agregar headers de rendimiento y salida
  Performance::addPerformanceHeaders();
  echo $output;