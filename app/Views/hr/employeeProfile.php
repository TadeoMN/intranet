
<?php ob_start(); ?>

<?php
// Determine form attributes based on mode
$formAction = '';
$formMethod = 'GET';
$readonly = ($mode === 'view') ? 'readonly' : '';
$disabled = ($mode === 'view') ? 'disabled' : '';

switch ($mode) {
    case 'create':
        $formAction = "/employee/profile/store/{$employee['id_employee']}";
        $formMethod = 'POST';
        break;
    case 'edit':
        $formAction = "/employee/profile/update/{$employee['id_employee']}";
        $formMethod = 'POST';
        break;
    default: // view
        $formAction = "#";
        break;
}

// Check if profile/contract exist for messaging
$hasProfile = $profile !== null;
$hasContract = $contract !== null;
?>

<div class="container my-4">
    <div class="card">
        <div class="card-header bg-dark text-white">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <img src="/assets/images/profile-image.jpg"
                         alt="Foto de <?= htmlspecialchars($employee['name_employee']) ?>"
                         class="img-fluid mx-3 rounded-2" style="max-width: 120px; max-height: 120px;">
                    <div>
                        <h2 class="card-title my-auto">
                            <strong><?= htmlspecialchars($employee['name_employee']) ?></strong>
                        </h2>
                        <p class="mb-0">
                            <?= $mode === 'view' ? 'Ver Perfil' : ($mode === 'create' ? 'Crear Perfil' : 'Editar Perfil') ?>
                        </p>
                    </div>
                </div>
                
                <!-- Search Button (always visible) -->
                <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#mdlEmployeeSearch">
                    <i class="fa-solid fa-search"></i> Buscar empleado
                </button>
            </div>
        </div>

        <form method="<?= $formMethod ?>" action="<?= $formAction ?>">
            <div class="card-body">
                
                <!-- Alert if no profile/contract in view mode -->
                <?php if ($mode === 'view' && (!$hasProfile || !$hasContract)): ?>
                <div class="alert alert-warning" role="alert">
                    <i class="fa-solid fa-exclamation-triangle"></i>
                    Este empleado no tiene <?= !$hasProfile ? 'perfil' : '' ?><?= !$hasProfile && !$hasContract ? ' ni ' : '' ?><?= !$hasContract ? 'contrato' : '' ?> completo.
                    <a href="/employee/profile/create/<?= $employee['id_employee'] ?>" class="btn btn-sm btn-primary ms-2">
                        <i class="fa-solid fa-plus"></i> Crear perfil
                    </a>
                </div>
                <?php endif; ?>

                <div class="row">
                    <!-- Employee Basic Info (always readonly) -->
                    <div class="col-md-6">
                        <h5>Información de Empleado</h5>
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="code_employee" 
                                   value="<?= htmlspecialchars($employee['code_employee'] ?? '') ?>" readonly>
                            <label for="code_employee">Código del Empleado</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="date" class="form-control" id="date_hired" 
                                   value="<?= htmlspecialchars($employee['date_hired'] ?? '') ?>" readonly>
                            <label for="date_hired">Fecha de Contratación</label>
                        </div>
                        <div class="form-floating mb-3">
                            <select class="form-select" id="status_employee" disabled>
                                <option value="ACTIVO" <?= ($employee['status_employee'] ?? '') === 'ACTIVO' ? 'selected' : '' ?>>ACTIVO</option>
                                <option value="INACTIVO" <?= ($employee['status_employee'] ?? '') === 'INACTIVO' ? 'selected' : '' ?>>INACTIVO</option>
                                <option value="SUSPENDIDO" <?= ($employee['status_employee'] ?? '') === 'SUSPENDIDO' ? 'selected' : '' ?>>SUSPENDIDO</option>
                            </select>
                            <label for="status_employee">Estado del Empleado</label>
                        </div>
                        <div class="form-floating mb-3">
                            <select class="form-select" id="type_employee" disabled>
                                <option value="OPERATIVO" <?= ($employee['type_employee'] ?? '') === 'OPERATIVO' ? 'selected' : '' ?>>OPERATIVO</option>
                                <option value="ADMINISTRATIVO" <?= ($employee['type_employee'] ?? '') === 'ADMINISTRATIVO' ? 'selected' : '' ?>>ADMINISTRATIVO</option>
                            </select>
                            <label for="type_employee">Tipo de Empleado</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="seniority_employee" 
                                   value="<?= htmlspecialchars(($employee['seniority_employee'] ?? '0') . ' años') ?>" readonly>
                            <label for="seniority_employee">Antigüedad</label>
                        </div>

                        <!-- Department and Position (with cascade) -->
                        <div class="form-floating mb-3">
                            <select class="form-select" name="id_department_fk" id="id_department_fk" <?= $disabled ?>>
                                <option value="">Seleccione un departamento</option>
                                <?php foreach ($departments as $dept): ?>
                                <option value="<?= $dept['id_department'] ?>" 
                                        <?= $currentDept == $dept['id_department'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($dept['name_department']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <label for="id_department_fk">Departamento</label>
                        </div>
                        <div class="form-floating mb-3">
                            <select class="form-select" name="id_position_fk" id="id_position_fk" <?= $disabled ?>>
                                <option value="">Seleccione un puesto</option>
                            </select>
                            <label for="id_position_fk">Puesto</label>
                        </div>
                    </div>

                    <!-- Personal Information -->
                    <div class="col-md-6">
                        <h5>Información Personal</h5>
                        <div class="form-floating mb-3">
                            <input type="date" class="form-control" name="birthdate_employee_profile" id="birthdate_employee_profile" 
                                   value="<?= htmlspecialchars($profile['birthdate_employee_profile'] ?? '') ?>" <?= $readonly ?>>
                            <label for="birthdate_employee_profile">Fecha de Nacimiento</label>
                        </div>
                        <div class="form-floating mb-3">
                            <select class="form-select" name="gender_employee_profile" id="gender_employee_profile" <?= $disabled ?>>
                                <option value="">Seleccione género</option>
                                <option value="HOMBRE" <?= ($profile['gender_employee_profile'] ?? '') === 'HOMBRE' ? 'selected' : '' ?>>HOMBRE</option>
                                <option value="MUJER" <?= ($profile['gender_employee_profile'] ?? '') === 'MUJER' ? 'selected' : '' ?>>MUJER</option>
                            </select>
                            <label for="gender_employee_profile">Género</label>
                        </div>
                        <div class="form-floating mb-3">
                            <select class="form-select" name="marital_status_employee_profile" id="marital_status_employee_profile" <?= $disabled ?>>
                                <option value="SOLTERO" <?= ($profile['marital_status_employee_profile'] ?? 'SOLTERO') === 'SOLTERO' ? 'selected' : '' ?>>SOLTERO</option>
                                <option value="CASADO" <?= ($profile['marital_status_employee_profile'] ?? '') === 'CASADO' ? 'selected' : '' ?>>CASADO</option>
                                <option value="DIVORCIADO" <?= ($profile['marital_status_employee_profile'] ?? '') === 'DIVORCIADO' ? 'selected' : '' ?>>DIVORCIADO</option>
                                <option value="VIUDO" <?= ($profile['marital_status_employee_profile'] ?? '') === 'VIUDO' ? 'selected' : '' ?>>VIUDO</option>
                            </select>
                            <label for="marital_status_employee_profile">Estado Civil</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" name="curp_employee_profile" id="curp_employee_profile" 
                                   value="<?= htmlspecialchars($profile['curp_employee_profile'] ?? '') ?>" <?= $readonly ?>>
                            <label for="curp_employee_profile">CURP</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" name="ssn_employee_profile" id="ssn_employee_profile" 
                                   value="<?= htmlspecialchars($profile['ssn_employee_profile'] ?? '') ?>" <?= $readonly ?>>
                            <label for="ssn_employee_profile">NSS</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" name="account_number_employee_profile" id="account_number_employee_profile" 
                                   value="<?= htmlspecialchars($profile['account_number_employee_profile'] ?? '') ?>" <?= $readonly ?>>
                            <label for="account_number_employee_profile">Cuenta Bancaria</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" name="bank_employee_profile" id="bank_employee_profile" 
                                   value="<?= htmlspecialchars($profile['bank_employee_profile'] ?? '') ?>" <?= $readonly ?>>
                            <label for="bank_employee_profile">Banco</label>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <!-- Contact Information -->
                    <div class="col-md-6">
                        <h5>Información de Contacto</h5>
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" name="phone_employee_profile" id="phone_employee_profile" 
                                   value="<?= htmlspecialchars($profile['phone_employee_profile'] ?? '') ?>" <?= $readonly ?>>
                            <label for="phone_employee_profile">Teléfono</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" name="mobile_employee_profile" id="mobile_employee_profile" 
                                   value="<?= htmlspecialchars($profile['mobile_employee_profile'] ?? '') ?>" <?= $readonly ?>>
                            <label for="mobile_employee_profile">Celular</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="email" class="form-control" name="email_employee_profile" id="email_employee_profile" 
                                   value="<?= htmlspecialchars($profile['email_employee_profile'] ?? '') ?>" <?= $readonly ?>>
                            <label for="email_employee_profile">Email</label>
                        </div>
                        <div class="form-floating mb-3">
                            <textarea class="form-control" name="address_employee_profile" id="address_employee_profile" 
                                      style="height: 100px" <?= $readonly ?>><?= htmlspecialchars($profile['address_employee_profile'] ?? '') ?></textarea>
                            <label for="address_employee_profile">Dirección</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" name="emergency_contact_employee_profile" id="emergency_contact_employee_profile" 
                                   value="<?= htmlspecialchars($profile['emergency_contact_employee_profile'] ?? '') ?>" <?= $readonly ?>>
                            <label for="emergency_contact_employee_profile">Contacto de Emergencia</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" name="emergency_phone_employee_profile" id="emergency_phone_employee_profile" 
                                   value="<?= htmlspecialchars($profile['emergency_phone_employee_profile'] ?? '') ?>" <?= $readonly ?>>
                            <label for="emergency_phone_employee_profile">Teléfono de Emergencia</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" name="emergency_relationship_employee_profile" id="emergency_relationship_employee_profile" 
                                   value="<?= htmlspecialchars($profile['emergency_relationship_employee_profile'] ?? '') ?>" <?= $readonly ?>>
                            <label for="emergency_relationship_employee_profile">Relación con el Contacto</label>
                        </div>
                    </div>

                    <!-- Contract Information -->
                    <div class="col-md-6">
                        <h5>Información de Contrato</h5>
                        <div class="form-floating mb-3">
                            <input type="number" class="form-control" name="number_payrroll_contract" id="number_payrroll_contract" 
                                   value="<?= htmlspecialchars($contract['number_payrroll_contract'] ?? '') ?>" <?= $readonly ?>>
                            <label for="number_payrroll_contract">Número de Nómina</label>
                        </div>
                        <div class="form-floating mb-3">
                            <select class="form-select" name="id_contract_type_fk" id="id_contract_type_fk" <?= $disabled ?>>
                                <option value="">Seleccione tipo de contrato</option>
                                <option value="1" <?= ($contract['id_contract_type_fk'] ?? '') == '1' ? 'selected' : '' ?>>INDEFINIDO</option>
                                <option value="2" <?= ($contract['id_contract_type_fk'] ?? '') == '2' ? 'selected' : '' ?>>TEMPORAL</option>
                                <option value="3" <?= ($contract['id_contract_type_fk'] ?? '') == '3' ? 'selected' : '' ?>>POR_PROYECTO</option>
                                <option value="4" <?= ($contract['id_contract_type_fk'] ?? '') == '4' ? 'selected' : '' ?>>PRACTICAS</option>
                            </select>
                            <label for="id_contract_type_fk">Tipo de Contrato</label>
                        </div>
                        <div class="form-floating mb-3">
                            <select class="form-select" name="id_payroll_scheme_fk" id="id_payroll_scheme_fk" <?= $disabled ?>>
                                <option value="">Seleccione esquema de nómina</option>
                                <option value="1" <?= ($contract['id_payroll_scheme_fk'] ?? '') == '1' ? 'selected' : '' ?>>NOMINA_EMPLEADOS (SEMANAL)</option>
                                <option value="2" <?= ($contract['id_payroll_scheme_fk'] ?? '') == '2' ? 'selected' : '' ?>>NOMINA_EMPLEADOS (QUINCENAL)</option>
                                <option value="3" <?= ($contract['id_payroll_scheme_fk'] ?? '') == '3' ? 'selected' : '' ?>>HONORARIOS_ASIMILADOS (MENSUAL)</option>
                                <option value="4" <?= ($contract['id_payroll_scheme_fk'] ?? '') == '4' ? 'selected' : '' ?>>SALARIO_EJECUTIVOS (MENSUAL)</option>
                            </select>
                            <label for="id_payroll_scheme_fk">Esquema de Nómina</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="date" class="form-control" name="start_date_contract" id="start_date_contract" 
                                   value="<?= htmlspecialchars($contract['start_date_contract'] ?? '') ?>" <?= $readonly ?>>
                            <label for="start_date_contract">Fecha de Inicio</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="date" class="form-control" name="trial_period_contract" id="trial_period_contract" 
                                   value="<?= htmlspecialchars($contract['trial_period_contract'] ?? '') ?>" <?= $readonly ?>>
                            <label for="trial_period_contract">Fin de Periodo de Prueba</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="date" class="form-control" name="end_date_contract" id="end_date_contract" 
                                   value="<?= htmlspecialchars($contract['end_date_contract'] ?? '') ?>" <?= $readonly ?>>
                            <label for="end_date_contract">Fecha de Finalización</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="number" step="0.01" class="form-control" name="salary_contract" id="salary_contract" 
                                   value="<?= htmlspecialchars($contract['salary_contract'] ?? '') ?>" <?= $readonly ?>>
                            <label for="salary_contract">Salario</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="card-footer text-center">
                <?php if ($mode === 'view'): ?>
                    <?php if ($hasProfile): ?>
                    <button type="button" class="btn btn-primary" onclick="enableEditMode()">
                        <i class="fa-solid fa-pen-to-square"></i> Editar
                    </button>
                    <?php endif; ?>
                    <a href="/employees/list" class="btn btn-secondary">
                        <i class="fa-solid fa-arrow-left"></i> Volver
                    </a>
                <?php else: ?>
                    <button type="submit" class="btn btn-success">
                        <i class="fa-solid fa-save"></i> Guardar
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="cancelEdit()">
                        <i class="fa-solid fa-times"></i> Cancelar
                    </button>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Employee Search Modal -->
<div class="modal fade" id="mdlEmployeeSearch" tabindex="-1" aria-labelledby="mdlEmployeeSearchLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mdlEmployeeSearchLabel">Buscar Empleado</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <input type="text" class="form-control" id="searchEmployeeInput" placeholder="Buscar por nombre o código...">
                </div>
                <div id="searchResults">
                    <!-- Results will be populated here -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for mode switching and search -->
<script>
// Mode switching functionality
function enableEditMode() {
    // Change form action and method
    const form = document.querySelector('form');
    form.action = '/employee/profile/update/<?= $employee['id_employee'] ?>';
    form.method = 'POST';
    
    // Enable all profile and contract fields
    const inputs = form.querySelectorAll('input[name*="employee_profile"], input[name*="contract"], select[name*="employee_profile"], select[name*="contract"], textarea[name*="employee_profile"]');
    inputs.forEach(input => {
        input.removeAttribute('readonly');
        input.removeAttribute('disabled');
    });
    
    // Enable department/position selects but keep cascade
    document.getElementById('id_department_fk').removeAttribute('disabled');
    document.getElementById('id_position_fk').removeAttribute('disabled');
    
    // Update buttons
    const footer = document.querySelector('.card-footer');
    footer.innerHTML = `
        <button type="submit" class="btn btn-success">
            <i class="fa-solid fa-save"></i> Guardar
        </button>
        <button type="button" class="btn btn-secondary" onclick="cancelEdit()">
            <i class="fa-solid fa-times"></i> Cancelar
        </button>
    `;
}

function cancelEdit() {
    // Redirect back to view mode
    window.location.href = '/employee/profile/<?= $employee['id_employee'] ?>';
}

// Employee search functionality
let searchTimeout;
document.getElementById('searchEmployeeInput').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    const query = this.value.trim();
    
    if (query.length < 2) {
        document.getElementById('searchResults').innerHTML = '';
        return;
    }
    
    searchTimeout = setTimeout(() => {
        fetch(`/api/employees/search?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(employees => {
                const resultsDiv = document.getElementById('searchResults');
                if (employees.length === 0) {
                    resultsDiv.innerHTML = '<p class="text-muted">No se encontraron empleados.</p>';
                    return;
                }
                
                let html = '<div class="list-group">';
                employees.forEach(emp => {
                    html += `
                        <a href="#" class="list-group-item list-group-item-action employee-result" 
                           data-id="${emp.id_employee}" data-has-profile="${emp.has_profile}">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">${emp.name_employee}</h6>
                                <small class="text-muted">${emp.code_employee}</small>
                            </div>
                            <small class="${emp.status_employee === 'ACTIVO' ? 'text-success' : 'text-warning'}">${emp.status_employee}</small>
                            ${emp.has_profile ? '<span class="badge bg-success">Tiene perfil</span>' : '<span class="badge bg-warning">Sin perfil</span>'}
                        </a>
                    `;
                });
                html += '</div>';
                resultsDiv.innerHTML = html;
                
                // Add click handlers
                document.querySelectorAll('.employee-result').forEach(item => {
                    item.addEventListener('click', function(e) {
                        e.preventDefault();
                        const empId = this.dataset.id;
                        const hasProfile = this.dataset.hasProfile === 'true';
                        
                        if (hasProfile) {
                            // Navigate to profile view
                            window.location.href = `/employee/profile/${empId}`;
                        } else {
                            // Ask if want to create profile
                            Swal.fire({
                                title: 'Empleado sin perfil',
                                text: '¿Desea crear el perfil para este empleado?',
                                icon: 'question',
                                showCancelButton: true,
                                confirmButtonText: 'Crear perfil',
                                cancelButtonText: 'Cancelar',
                                heightAuto: false,
                                scrollbarPadding: false
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = `/employee/profile/create/${empId}`;
                                }
                            });
                        }
                    });
                });
            })
            .catch(error => {
                console.error('Search error:', error);
                document.getElementById('searchResults').innerHTML = '<p class="text-danger">Error en la búsqueda.</p>';
            });
    }, 300);
});
</script>

<!-- Department/Position cascade script -->
<?= cascadePositionsForProfile($positionsByDept, $mode, $currentDept, $currentPos) ?>

<!-- Flash alert -->
<?= flash_alert() ?>

<!-- Auto-prompt for profile creation if coming from list and no profile exists -->
<?php if ($mode === 'view' && !$hasProfile && !isset($_GET['no_prompt'])): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
        title: 'Perfil incompleto',
        text: 'Este empleado no tiene perfil registrado. ¿Desea crear el perfil ahora?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Crear perfil',
        cancelButtonText: 'Cancelar',
        heightAuto: false,
        scrollbarPadding: false
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '/employee/profile/create/<?= $employee['id_employee'] ?>';
        } else {
            window.location.href = '/employees/list';
        }
    });
});
</script>
<?php endif; ?>

<?php $content = ob_get_clean(); include __DIR__.'/../layouts/layout-main.php'; ?>