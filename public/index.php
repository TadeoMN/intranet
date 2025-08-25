<?php

  ini_set('display_errors', 1);
  error_reporting(E_ALL);
  
  date_default_timezone_set('America/Mexico_City');

  require_once dirname(__DIR__).'/vendor/autoload.php';
  require_once dirname(__DIR__).'/core/Helpers.php';

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

  // --- Router + rutas --------------------------------
  use Core\Router; 

  $router = new Router;
  $router->get('/', 'AuthController@showLogin');
  $router->get('/logout', 'AuthController@logout');
  $router->get('/dashboard', 'DashboardController@index');
  $router->post('/login', 'AuthController@login');
  $router->post('/sessions/close', 'SessionController@close');

  $router->get('/employees/list', 'EmployeeController@listEmployees');
  $router->get('/employees/profile/{id}', 'EmployeeController@showProfileEmployee');
  $router->get('/api/employee/{id}', 'EmployeeController@showEmployee');
  $router->post('/employee/delete/{id}', 'EmployeeController@deleteEmployee');
  $router->post('/employee/store', 'EmployeeController@storeEmployee');
  $router->post('/employee/update/{id}', 'EmployeeController@updateEmployee');
  $router->get('/employees/profile/create/{id}', 'EmployeeController@createProfileEmployee');

  $output = $router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);

  echo $output;