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

    public function showProfileEmployee(int $id_employee) {
        $profile = EmployeeProfile::getByEmployeeId($id_employee);
        $contract = Contracts::getByEmployeeId($id_employee);
        $employee = Employee::findById($id_employee);

        // mandar error si perfil y contrato no existen
        if (!$contract || !$profile) {
            flash('error', 'Perfil no encontrado', 'El perfil del empleado solicitado no existe.');
            return redirect('/employees/list');
        }
        return view('hr/employeeProfile', compact('profile', 'contract', 'employee'));
    }

    public function createEmployee() {
        $departments = Department::all();
        $positions = Positions::all();
        $positionsByDepartment = [];
        foreach ($positions as $position) {
            $positionsByDepartment[$position['id_department_fk']][] = [
                'id_position' => $position['id_position'],
                'name_position' => $position['name_position']
            ];
        }
        return view('hr/employeeCreate', compact('departments', 'positionsByDepartment'));
    }

    public function editEmployee(int $id_employee) {
        $employee = Employee::findById($id_employee);
        if (!$employee) {
            flash('error', 'Empleado no encontrado', 'El empleado solicitado no existe.');
            return redirect('/employees/list');
        }
        $departments = Department::all();
        $positions = Positions::all();
        $positionsByDepartment = [];
        foreach ($positions as $position) {
            $positionsByDepartment[$position['id_department_fk']][] = [
                'id_position' => $position['id_position'],
                'name_position' => $position['name_position']
            ];
        }
        return view('hr/employeeEdit', compact('employee', 'departments', 'positionsByDepartment'));
    }

    public function updateProfileEmployee(int $id_employee) {
        try {
            // Update employee data
            $employeeData = [
                'status_employee' => $_POST['status_employee'],
                'type_employee' => $_POST['type_employee']
            ];
            Employee::updateById($id_employee, $employeeData);

            // Update employee profile data
            $profileData = [
                'birthdate_employee_profile' => $_POST['birthdate_employee'],
                'curp_employee_profile' => $_POST['curp_employee'],
                'ssn_employee_profile' => $_POST['nss_employee'],
                'gender_employee_profile' => $_POST['gender_employee'],
                'marital_status_employee_profile' => $_POST['marital_status_employee'],
                'account_number_employee_profile' => $_POST['account_number_employee'],
                'bank_employee_profile' => $_POST['bank_employee'],
                'phone_employee_profile' => $_POST['phone_employee_profile'],
                'mobile_employee_profile' => $_POST['mobile_employee_profile'],
                'email_employee_profile' => $_POST['email_employee_profile'],
                'address_employee_profile' => $_POST['address_employee_profile'],
                'emergency_contact_employee_profile' => $_POST['emergency_contact_employee_profile'],
                'emergency_phone_employee_profile' => $_POST['emergency_phone_employee_profile'],
                'emergency_relationship_employee_profile' => $_POST['emergency_relationship_employee_profile']
            ];
            EmployeeProfile::updateByEmployeeId($id_employee, $profileData);

            // Update contract data if exists
            $contractData = [
                'number_payroll_contract' => $_POST['number_payroll_contract'],
                'code_employee_snapshot' => $_POST['code_employee_snapshot'],
                'id_contract_type_fk' => $_POST['id_contract_type_fk'],
                'id_payroll_scheme_fk' => $_POST['id_payroll_scheme_fk'],
                'start_date_contract' => $_POST['start_date_contract'],
                'trial_period_contract' => $_POST['trial_period_contract'],
                'end_date_contract' => $_POST['end_date_contract'],
                'salary_contract' => $_POST['salary_contract'],
                'termination_reason_contract' => $_POST['termination_reason_contract'],
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ];
            Contracts::updateByEmployeeId($id_employee, $contractData);

            flash('success', 'Perfil actualizado', 'El perfil del empleado ha sido actualizado exitosamente.');
        } catch (\Exception $e) {
            flash('error', 'Error al actualizar', 'No se pudo actualizar el perfil: ' . $e->getMessage());
        }
        
        return redirect('/employees/profile/' . $id_employee);
    }

    public function searchEmployees() {
        $query = trim($_GET['q'] ?? '');
        
        if (strlen($query) < 2) {
            header('Content-Type: application/json');
            echo json_encode([]);
            return;
        }

        $pdo = \Core\Database::pdo();
        $likeQuery = '%' . $query . '%';
        
        $sql = '
            SELECT 
                e.id_employee,
                e.code_employee,
                e.name_employee,
                e.status_employee,
                d.name_department,
                CASE 
                    WHEN ep.id_employee_fk IS NOT NULL AND c.id_employee_fk IS NOT NULL THEN 1 
                    ELSE 0 
                END as has_profile
            FROM employee e
            INNER JOIN positions p ON p.id_position = e.id_position_fk
            INNER JOIN department d ON d.id_department = p.id_department_fk
            LEFT JOIN employee_profile ep ON ep.id_employee_fk = e.id_employee
            LEFT JOIN contracts c ON c.id_employee_fk = e.id_employee
            WHERE (
                LOWER(e.name_employee) LIKE :query1 OR
                LOWER(e.code_employee) LIKE :query2
            ) AND e.status_employee = "ACTIVO"
            ORDER BY e.name_employee
            LIMIT 20
        ';
        
        $st = $pdo->prepare($sql);
        $st->bindParam(':query1', $likeQuery);
        $st->bindParam(':query2', $likeQuery);
        $st->execute();
        $results = $st->fetchAll(\PDO::FETCH_ASSOC);
        
        header('Content-Type: application/json');
        echo json_encode($results);
    }

    public function createProfileEmployee(int $id_employee) {
        $employee = Employee::findByIdSimple($id_employee);
        if (!$employee) {
            flash('error', 'Empleado no encontrado', 'El empleado solicitado no existe.');
            return redirect('/employees/list');
        }

        // Check if profile already exists
        $profile = EmployeeProfile::getByEmployeeId($id_employee);
        $contract = Contracts::getByEmployeeId($id_employee);
        
        if ($profile && $contract) {
            // If profile exists, redirect to view it
            return redirect('/employees/profile/' . $id_employee);
        }

        // Initialize empty profile and contract for creation
        $profile = [
            'birthdate_employee_profile' => '',
            'curp_employee_profile' => '',
            'ssn_employee_profile' => '',
            'gender_employee_profile' => '',
            'marital_status_employee_profile' => '',
            'account_number_employee_profile' => '',
            'bank_employee_profile' => '',
            'phone_employee_profile' => '',
            'mobile_employee_profile' => '',
            'email_employee_profile' => '',
            'address_employee_profile' => '',
            'emergency_contact_employee_profile' => '',
            'emergency_phone_employee_profile' => '',
            'emergency_relationship_employee_profile' => ''
        ];
        
        $contract = [
            'number_payroll_contract' => '',
            'code_employee_snapshot' => $employee['code_employee'],
            'id_contract_type_fk' => '',
            'id_payroll_scheme_fk' => '',
            'start_date_contract' => '',
            'trial_period_contract' => '',
            'end_date_contract' => '',
            'salary_contract' => '',
            'termination_reason_contract' => '',
            'is_active' => 1
        ];

        return view('hr/employeeProfileCreate', compact('profile', 'contract', 'employee'));
    }

    public function storeProfileEmployee(int $id_employee) {
        try {
            // Create employee profile
            $profileData = [
                'id_employee_fk' => $id_employee,
                'birthdate_employee_profile' => $_POST['birthdate_employee'],
                'curp_employee_profile' => $_POST['curp_employee'],
                'ssn_employee_profile' => $_POST['nss_employee'],
                'gender_employee_profile' => $_POST['gender_employee'],
                'marital_status_employee_profile' => $_POST['marital_status_employee'],
                'account_number_employee_profile' => $_POST['account_number_employee'],
                'bank_employee_profile' => $_POST['bank_employee'],
                'phone_employee_profile' => $_POST['phone_employee_profile'],
                'mobile_employee_profile' => $_POST['mobile_employee_profile'],
                'email_employee_profile' => $_POST['email_employee_profile'],
                'address_employee_profile' => $_POST['address_employee_profile'],
                'emergency_contact_employee_profile' => $_POST['emergency_contact_employee_profile'],
                'emergency_phone_employee_profile' => $_POST['emergency_phone_employee_profile'],
                'emergency_relationship_employee_profile' => $_POST['emergency_relationship_employee_profile']
            ];
            EmployeeProfile::create($profileData);

            // Create contract
            $contractData = [
                'id_employee_fk' => $id_employee,
                'number_payroll_contract' => $_POST['number_payroll_contract'],
                'code_employee_snapshot' => $_POST['code_employee_snapshot'],
                'id_contract_type_fk' => $_POST['id_contract_type_fk'],
                'id_payroll_scheme_fk' => $_POST['id_payroll_scheme_fk'],
                'start_date_contract' => $_POST['start_date_contract'],
                'trial_period_contract' => $_POST['trial_period_contract'],
                'end_date_contract' => $_POST['end_date_contract'],
                'salary_contract' => $_POST['salary_contract'],
                'termination_reason_contract' => $_POST['termination_reason_contract'],
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ];
            Contracts::create($contractData);

            // Update employee data
            $employeeData = [
                'status_employee' => $_POST['status_employee'],
                'type_employee' => $_POST['type_employee']
            ];
            Employee::updateById($id_employee, $employeeData);

            flash('success', 'Perfil creado', 'El perfil del empleado ha sido creado exitosamente.');
        } catch (\Exception $e) {
            flash('error', 'Error al crear perfil', 'No se pudo crear el perfil: ' . $e->getMessage());
        }
        
        return redirect('/employees/profile/' . $id_employee);
    }
}