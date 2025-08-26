<?php
namespace App\Controllers;

use App\Models\Employee;
use App\Models\Department;
use App\Models\Positions;
use App\Models\EmployeeProfile;
use App\Models\Contracts;

use function view, redirect, flash;
/**
 * EmployeeController handles employee-related operations such as listing, creating, updating, and deleting employees.
 * It provides methods to manage employee data, including pagination, filtering, and sorting.
 * This controller interacts with the Employee model to perform CRUD operations and prepares data for views.
 */
class EmployeeController {
    /**
     * List employees with pagination and filtering.
     * @param int $limit Number of employees per page.
     * @param int $offset Offset for pagination.
     * @return array Rendered view with employee data.
     * @throws \Exception If there is an error retrieving employee data.
     * @route /employees/list
     * @method GET
     * @description This function retrieves a paginated list of employees, allowing for search and filtering
     *              by various criteria such as name, date of hiring, and status. It also
     *              prepares the necessary data for rendering the employee list view.
     *              It handles pagination, sorting, and filtering parameters from the request.
     */
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
            'type_employee', 'seniority_employee', 'name_user', 'name_position',
            'name_department', 'name_manager', 'number_payroll_contract'
        ];

        $sort = $_GET['sort'] ?? 'code_employee';

        $sort = in_array($sort, $allowedSorts, true) ? $sort : 'code_employee';
        $order = (isset($_GET['order']) && strtolower($_GET['order']) === 'desc') ? 'desc' : 'asc';

        $status = $_GET['status'] ?? null;

        $employeesData = Employee::filterPaginated($search, $dateFrom, $dateTo, $limit, $offset, $sort, $order, $status);
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
            'order' => strtolower($order),
            'status' => $status
        ];

        $departments = Department::all();
        $positions = Positions::all();
        $positionsByDepartment = [];
        
        foreach ($positions as $position) {
            $positionsByDepartment[$position['id_department_fk']][] = [
                'id_position' => $position['id_position'],
                'name_position' => $position['name_position']
            ];
        }
        return view('hr/employeeList', compact('employees', 'pagination', 'departments', 'positionsByDepartment'));
    }
    /**
     * Store a new employee.
     * @return array Redirect response.
     * @throws \Exception If there is an error creating the employee.
     * @route /employee/store
     * @method POST
     * @description This function handles the creation of a new employee. It validates the input data
     *              and attempts to create a new employee record in the database. If successful,
     *              it redirects to the employee list with a success message. If there are validation
     *              errors or database errors, it redirects back with an error message.
     */
    public function storeEmployee() {
        $data = [
            'name_employee' => trim(strtoupper($_POST['name_employee'])),
            'date_hired' => $_POST['date_hired'],
            'type_employee' => $_POST['type_employee'],
            'id_position_fk' => (int)$_POST['id_position_fk']
        ];

        try {
            $idEmployee = Employee::create($data);
            if ($idEmployee) {
                flash('success', 'Empleado creado', 'El empleado ha sido creado exitosamente.');
            } else {
                flash('error', 'Error al crear empleado', 'No se pudo crear el empleado. Inténtalo de nuevo más tarde.');
            }
        } catch (\PDOException $e) {
            if ($e->errorInfo[1] === 1062) { // Duplicate entry
                flash('error', 'Error al crear empleado', 'Ya existe un empleado con el mismo nombre.');
            } elseif ($e->errorInfo[1] === 1452) { // Foreign key constraint fails
                flash('error', 'Error al crear empleado', 'El puesto seleccionado no es válido o no existe.');
            } elseif ($e->errorInfo[1] === 1364) { // Incorrect datetime value
                flash('error', 'Error al crear empleado', 'La fecha de contratación es incorrecta o está vacía.');
            } else {
                flash('error', 'Error al crear empleado', 'Ocurrió un error inesperado: ' . $e->getMessage());
            }
        }
        return redirect('/employees/list?search=' . urlencode($_POST['name_employee']));
    }
    /**
     * Show employee details.
     * @param int $id_employee Employee ID.
     * @return void
     * @throws \Exception If the employee is not found.
     * @route /employee/show/{id_employee}
     * @method GET
     * @description This function retrieves the details of a specific employee by their ID.
     */
    public function showEmployee(int $id_employee) {
        header('Content-Type: application/json');
        echo json_encode(Employee::findById($id_employee));
    }
    /**
     * Update an existing employee.
     * @param int $id_employee Employee ID.
     * @return array Redirect response.
     * @throws \Exception If the employee is not found.
     * @route /employee/update/{id_employee}
     * @method POST
     * @description This function handles the update of an existing employee. It validates the input data
     *              and attempts to update the employee record in the database. If successful,
     *              it redirects to the employee list with a success message. If there are validation
     *              errors or database errors, it redirects back with an error message.
     */
    public function updateEmployee(int $id_employee) {
        $data = [
            'name_employee' => trim(strtoupper($_POST['name_employee'])),
            'date_hired' => $_POST['date_hired'],
            'status_employee' => $_POST['status_employee'],
            'type_employee' => $_POST['type_employee'],
            'id_position_fk' => (int)$_POST['id_position_fk']
        ];

        try {
            $ok = Employee::updateById($id_employee, $data);
            if ($ok) {
                flash('success', 'Empleado actualizado', 'El empleado ha sido actualizado exitosamente.');
            } else {
                flash('error', 'Error al actualizar empleado', 'No se pudo actualizar el empleado. Inténtalo de nuevo más tarde.');
            }
        } catch (\PDOException $e) {
            if ($e->errorInfo[1] === 1062) { // Duplicate entry
                flash('error', 'Error al actualizar empleado', 'Ya existe un empleado con el mismo nombre.');
            } elseif ($e->errorInfo[1] === 1452) { // Foreign key constraint fails
                flash('error', 'Error al actualizar empleado', 'El puesto seleccionado no es válido o no existe.');
            } elseif ($e->errorInfo[1] === 1364) { // Incorrect datetime value
                flash('error', 'Error al actualizar empleado', 'La fecha de contratación es incorrecta o está vacía.');
            } else {
                flash('error', 'Error al actualizar empleado', 'Ocurrió un error inesperado: ' . $e->getMessage());
            }
        }
        return redirect('/employees/list?search=' . urlencode($_POST['name_employee']));
    }
    /**
     * Delete an employee by ID.
     * @param int $id_employee Employee ID.
     * @return array Redirect response.
     * @throws \Exception If the employee is not found or cannot be deleted.
     * @route /employee/delete/{id_employee}
     * @method POST
     * @description This function handles the deletion of an employee by their ID. It attempts to
     *              mark the employee as inactive in the database. If successful, it redirects to
     *              the employee list with a success message. If there are errors, it redirects back
     *              with an error message.
     */
    public function deleteEmployee(int $id_employee) {
        $ok = Employee::deleteById($id_employee);
        if ($ok) {
            flash('success', 'Empleado eliminado', 'El empleado ha sido eliminado exitosamente.');
        } else {
            flash('error', 'Error al eliminar', 'No se pudo eliminar el empleado. Inténtalo de nuevo más tarde.');
        }
        return redirect('/employees/list?' . http_build_query($_GET));
    }
    /**
     * Show the profile of an employee.
     * @param int $id_employee Employee ID.
     * @return array Employee profile data.
     * @throws \Exception If the employee is not found.
     * @route /employee/profile/{id_employee}
     * @method GET
     * @description This function retrieves the profile information of a specific employee by their ID.
     *              It returns the employee's profile data, including personal and job-related information.
     */
    public function showProfileEmployee(int $id_employee) {
        $profile = EmployeeProfile::getByEmployeeId($id_employee);
        $contract = Contracts::getByEmployeeId($id_employee);
        $employee = Employee::findById($id_employee);
        $departments = Department::all();
        $positions = Positions::all();
        $positionsByDepartment = [];

        foreach ($positions as $position) {
            $positionsByDepartment[$position['id_department_fk']][] = [
                'id_position' => $position['id_position'],
                'name_position' => $position['name_position']
            ];
        }

        $bloodTypes = EmployeeProfile::getEmployeeProfileEnums('blood_type_employee_profile');
        $genders = EmployeeProfile::getEmployeeProfileEnums('gender_employee_profile');
        $maritalStatuses = EmployeeProfile::getEmployeeProfileEnums('marital_status_employee_profile');
        $contractTypes = Contracts::getContractsEnums('type_contract');
        $payrollSchemes = Contracts::getContractsEnums('payroll_scheme_contract');

        if (!$contract || !$profile) {
            flash_button('warning', 'Perfil no encontrado', 'El empleado aun no cuenta con un perfil. ¿Desea crear el perfil?', 'Aceptar', '/employees/profile/create/' . $id_employee);
            return redirect('/employees/list');
        }
        return view('hr/employeeProfile', compact('profile', 'contract', 'employee', 'departments', 'positionsByDepartment', 'bloodTypes', 'genders', 'maritalStatuses', 'contractTypes', 'payrollSchemes'));
    }
    /**
     * Show the form to create a new employee profile.
     * @param int $id_employee Employee ID.
     * @return array Rendered view for creating an employee profile.
     * @throws \Exception If the employee is not found.
     * @route /employee/profile/create/{id_employee}
     * @method GET
     * @description This function displays the form to create a new employee profile for a specific employee.
     */
    public function createProfileEmployee(int $id_employee) {
        $employee = Employee::findById($id_employee);
        $profile = [];
        $contract = [];
        $departments = Department::all();
        $positions = Positions::all();
        $positionsByDepartment = [];

        foreach ($positions as $position) {
            $positionsByDepartment[$position['id_department_fk']][] = [
                'id_position' => $position['id_position'],
                'name_position' => $position['name_position']
            ];
        }

        if (!$employee) {
            flash('error', 'Empleado no encontrado', 'El empleado especificado no existe.');
            return redirect('/employees/list');
        }
        return view('hr/employeeProfile', compact('employee', 'profile', 'contract', 'departments', 'positionsByDepartment'));
    }
    // GET /api/employees/search?q=algo[&debug=1]
    public function searchEmployee(): void {
        header('Content-Type: application/json; charset=utf-8');
        $q = isset($_GET['q']) ? trim((string)$_GET['q']) : (isset($_GET['term']) ? trim((string)$_GET['term']) : '');

        if (mb_strlen($q) < 2) {
            echo json_encode(['ok' => true, 'items' => []], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $results = Employee::searchEmployee($q);
            echo json_encode(['ok' => true, 'items' => $results], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            echo json_encode([
                'ok' => false,
                'items' => [],
                'flash' => [
                    'type' => 'danger',
                    'title' => 'Búsqueda de empleados',
                    'message' => 'Ocurrió un error al realizar la búsqueda. Intenta de nuevo.'
                ]
            ], JSON_UNESCAPED_UNICODE);
        }
    }
}