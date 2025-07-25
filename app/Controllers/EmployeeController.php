<?php
namespace App\Controllers;

use App\Models\Employee;
use function view, redirect, flash;

class EmployeeController {
    public function listEmployees() {
        // Add pagination support for large employee lists
        // Agregar soporte de paginación para listas grandes de empleados
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 50; // Show 50 employees per page / Mostrar 50 empleados por página
        $offset = ($page - 1) * $perPage;
        
        $employees = Employee::allEmployeeUser($perPage, $offset);
        $totalEmployees = Employee::countActiveEmployees();
        $totalPages = ceil($totalEmployees / $perPage);
        
        $pagination = [
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_items' => $totalEmployees,
            'per_page' => $perPage,
            'has_prev' => $page > 1,
            'has_next' => $page < $totalPages,
            'prev_page' => $page > 1 ? $page - 1 : null,
            'next_page' => $page < $totalPages ? $page + 1 : null
        ];
        
        return view('hr/employeeList', compact('employees', 'pagination'));
    }

    public function show($id) {
        $employee = Employee::findById($id);
        if (!$employee) {
            flash('Employee not found', 'danger');
            return redirect('employee/index');
        }
        return view('employee/show', ['employee' => $employee]);
    }

    public function create() {
        return view('employee/create');
    }
}