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
  $router->post('/login', 'AuthController@login');
  $router->get('/logout', 'AuthController@logout');
  $router->get('/dashboard', 'DashboardController@index');
  $router->post('/sessions/close', 'SessionController@close');

  $router->get('/employees/list', 'EmployeeController@listEmployees');
  $router->post('/employee/delete/{id}', 'EmployeeController@deleteEmployee');
  $router->post('/employee/store', 'EmployeeController@storeEmployee');
  $router->post('/employee/update/{id}', 'EmployeeController@updateEmployee');
  $router->get('/api/employee/{id}', 'EmployeeController@showEmployee');
  
  // Employee Profile Routes (3-mode system)
  $router->get('/employee/profile/{id}', 'EmployeeController@profile');
  $router->get('/employee/profile/create/{id}', 'EmployeeController@profileCreate');
  $router->post('/employee/profile/store/{id}', 'EmployeeController@profileStore');
  $router->post('/employee/profile/update/{id}', 'EmployeeController@profileUpdate');
  $router->get('/api/employees/search', 'EmployeeController@search');

  $router->get('/employees/edit/{id}', 'EmployeeController@editEmployee');


  $router->get('/employee/create', 'EmployeeController@createEmployee');
  $router->post('/employees/update/{id}', 'EmployeeController@updateEmployee');
  
  $output = $router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);

  echo $output;