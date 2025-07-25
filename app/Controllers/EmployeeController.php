<?php
namespace App\Controllers;

use App\Models\Employee;
use function view, redirect, flash;

class EmployeeController {
    public function listEmployees() {
        $employees = Employee::allEmployeeUser();
        return view('hr/employeeList', compact('employees'));
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