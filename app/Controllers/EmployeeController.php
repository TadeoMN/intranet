<?php
namespace App\Controllers;

use App\Models\Employee;
use function view, redirect, flash;

class EmployeeController {
    public function listEmployees() {

        $allowedPages = [5,10,20,50,100]; // Example allowed pages
        $limit = (int)($_GET['limit'] ?? 10);
        $limit = in_array($limit, $allowedPages) ? $limit : 10;

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $offset = ($page - 1) * $limit;

        $search = trim($_GET['search'] ?? '');
        $dateFrom = $_GET['dateFrom'] ?? '';
        $dateTo = $_GET['dateTo'] ?? '';

        $allowedSorts = [
            'code_employee', 'name_employee', 'date_hired', 'status_employee',
            'type_employee', 'seniority_employee', 'name_user', 'name_position', 'name_department', 'name_manager'
        ];

        $sort = $_GET['sort'] ?? 'code_employee';

        $sort = in_array($sort, $allowedSorts, true) ? $sort : 'code_employee';
        $order = (isset($_GET['order']) && strtolower($_GET['order']) === 'desc') ? 'desc' : 'asc';

        $employeesData = Employee::filterPaginated($search, $dateFrom, $dateTo, $limit, $offset, $sort, $order);
        $employees = $employeesData['employees'];
        $total = $employeesData['total'];
        $totalPages = ceil($total / $limit);

        $pagination = [
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_items' => $total,
            'limit' => $limit,
            'has_prev' => $page > 1,
            'has_next' => $page < $totalPages,
            'prev_page' => $page > 1 ? $page - 1 : null,
            'next_page' => $page < $totalPages ? $page + 1 : null,
            'search' => $search,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'sort' => $sort,
            'order' => strtolower($order)
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