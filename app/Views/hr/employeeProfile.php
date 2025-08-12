
<?php ob_start(); ?>

  <div class="container my-4">
    <!-- Employee Search Modal -->
    <div class="modal fade" id="employeeSearchModal" tabindex="-1" aria-labelledby="employeeSearchModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="employeeSearchModalLabel">Buscar Empleado</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <input type="text" id="modalSearchInput" class="form-control" placeholder="Buscar por nombre o código de empleado...">
            </div>
            <div id="employeeSearchResults">
              <!-- Results will be loaded here -->
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header bg-dark text-white">
        <div class="d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center">
            <img src="/assets/images/profile-image.jpg"
            alt="Foto de <?= $employee['name_employee'] ? $employee['name_employee'] : 'Empleado' ?>"
            class="img-fluid mx-3 rounded-2" style="max-width: 150px; max-height: 150px;">
            <h2 class="card-title text-center my-auto">
              <strong><?= htmlspecialchars($employee['name_employee'])?></strong>
            </h2>
          </div>
          <div>
            <button type="button" class="btn btn-outline-light" data-bs-toggle="modal" data-bs-target="#employeeSearchModal">
              <i class="fa-solid fa-search"></i> Buscar Empleado
            </button>
          </div>
        </div>
      </div>
      <div class="card-body">
        <form id="employeeProfileForm" method="POST" action="/employees/profile/update/<?= $employee['id_employee'] ?>">
        <div class="row">
          <div class="col-md-6">
            <h5>Información de Empleado</h5>
            <div class="form-floating mb-3">
              <input type="text" class="form-control" name="code_employee" id="code_employee" value="<?= htmlspecialchars($employee['code_employee']) ?>" readonly disabled>
              <label for="code_employee" class="form-label">Código del Empleado</label>
            </div>
            <div class="form-floating mb-3">
              <input type="date" class="form-control" name="date_hired" id="date_hired" value="<?= htmlspecialchars($employee['date_hired']) ?>" readonly disabled>
              <label for="date_hired" class="form-label">Fecha de Contratación</label>
            </div>
            <div class="form-floating mb-3">
              <select class="form-select" name="status_employee" id="status_employee" disabled>
                <option value="ACTIVO" <?= $employee['status_employee'] === 'ACTIVO' ? 'selected' : '' ?>>ACTIVO</option>
                <option value="INACTIVO" <?= $employee['status_employee'] === 'INACTIVO' ? 'selected' : '' ?>>INACTIVO</option>
                <option value="SUSPENDIDO" <?= $employee['status_employee'] === 'SUSPENDIDO' ? 'selected' : '' ?>>SUSPENDIDO</option>
              </select>
              <label for="status_employee" class="form-label">Estado del Empleado</label>
            </div>
            <div class="form-floating mb-3">
              <select class="form-select" name="type_employee" id="type_employee" disabled>
                <option value="OPERATIVO" <?= $employee['type_employee'] === 'OPERATIVO' ? 'selected' : '' ?>>OPERATIVO</option>
                <option value="ADMINISTRATIVO" <?= $employee['type_employee'] === 'ADMINISTRATIVO' ? 'selected' : '' ?>>ADMINISTRATIVO</option>
              </select>
              <label for="type_employee" class="form-label">Tipo de Empleado</label>
            </div>
            <div class="form-floating mb-3">
              <input type="text" class="form-control" name="seniority_employee" id="seniority_employee" value="<?= htmlspecialchars($employee['seniority_employee']) ?> años" readonly disabled>
              <label for="seniority_employee" class="form-label">Antigüedad</label>
            </div>
          </div>

          <div class="col-md-6">
            <h5>Información Personal</h5>
            
            <div class="form-floating mb-3">
              <input type="date" class="form-control" name="birthdate_employee" id="birthdate_employee" value="<?= htmlspecialchars($profile['birthdate_employee_profile']) ?>" readonly disabled>
              <label for="birthdate_employee" class="form-label">Fecha de Nacimiento</label>
            </div>
            <div class="form-floating mb-3">
              <input type="text" class="form-control" name="curp_employee" id="curp_employee" value="<?= htmlspecialchars($profile['curp_employee_profile']) ?>" readonly disabled>
              <label for="curp_employee" class="form-label">CURP</label>
            </div>
            <div class="form-floating mb-3">
              <input type="text" class="form-control" name="nss_employee" id="nss_employee" value="<?= htmlspecialchars($profile['nss_employee_profile']) ?>" readonly disabled>
              <label for="nss_employee" class="form-label">NSS</label>
            </div>
            <div class="form-floating mb-3">
              <select class="form-select" name="gender_employee" id="gender_employee" disabled>
                <option value="MASCULINO" <?= $profile['gender_employee_profile'] === 'MASCULINO' ? 'selected' : '' ?>>MASCULINO</option>
                <option value="FEMENINO" <?= $profile['gender_employee_profile'] === 'FEMENINO' ? 'selected' : '' ?>>FEMENINO</option>
                <option value="OTRO" <?= $profile['gender_employee_profile'] === 'OTRO' ? 'selected' : '' ?>>OTRO</option>
              </select>
              <label for="gender_employee" class="form-label">Género</label>
            </div>
            <div class="form-floating mb-3">
              <select class="form-select" name="marital_status_employee" id="marital_status_employee" disabled>
                <option value="SOLTERO" <?= $profile['marital_status_employee_profile'] === 'SOLTERO' ? 'selected' : '' ?>>SOLTERO</option>
                <option value="CASADO" <?= $profile['marital_status_employee_profile'] === 'CASADO' ? 'selected' : '' ?>>CASADO</option>
                <option value="DIVORCIADO" <?= $profile['marital_status_employee_profile'] === 'DIVORCIADO' ? 'selected' : '' ?>>DIVORCIADO</option>
                <option value="VIUDO" <?= $profile['marital_status_employee_profile'] === 'VIUDO' ? 'selected' : '' ?>>VIUDO</option>
                <option value="UNION_LIBRE" <?= $profile['marital_status_employee_profile'] === 'UNION_LIBRE' ? 'selected' : '' ?>>UNIÓN LIBRE</option>
              </select>
              <label for="marital_status_employee" class="form-label">Estado Civil</label>
            </div>
            <div class="form-floating mb-3">
              <input type="text" class="form-control" name="account_number_employee" id="account_number_employee" value="<?= htmlspecialchars($profile['account_number_employee_profile']) ?>" readonly disabled>
              <label for="account_number_employee" class="form-label">Cuenta Bancaria</label>
            </div>
            <div class="form-floating mb-3">
              <input type="text" class="form-control" name="bank_employee" id="bank_employee" value="<?= htmlspecialchars($profile['bank_employee_profile']) ?>" readonly disabled>
              <label for="bank_employee" class="form-label">Banco</label>
            </div>
          </div>
        </div>
  
        <div class="row mt-4">
          <div class="col-md-6">
            <h5>Información de Contrato</h5>

            <div class="form-floating mb-3">
              <input type="text" class="form-control" name="number_payroll_contract" id="number_payroll_contract" value="<?= htmlspecialchars($contract['number_payroll_contract']) ?>" readonly disabled>
              <label for="number_payroll_contract" class="form-label">Número de Nómina</label>
            </div>
            <div class="form-floating mb-3">
              <input type="text" class="form-control" name="code_employee_snapshot" id="code_employee_snapshot" value="<?= htmlspecialchars($contract['code_employee_snapshot']) ?>" readonly disabled>
              <label for="code_employee_snapshot" class="form-label">Código de Snapshot del Empleado</label>
            </div>
            <div class="form-floating mb-3">
              <select class="form-select" name="id_contract_type_fk" id="id_contract_type_fk" disabled>
                <option value="<?= htmlspecialchars($contract['id_contract_type_fk']) ?>"><?= htmlspecialchars($contract['id_contract_type_fk']) ?></option>
              </select>
              <label for="id_contract_type_fk" class="form-label">Tipo de Contrato</label>
            </div>
            <div class="form-floating mb-3">
              <select class="form-select" name="id_payroll_scheme_fk" id="id_payroll_scheme_fk" disabled>
                <option value="<?= htmlspecialchars($contract['id_payroll_scheme_fk']) ?>"><?= htmlspecialchars($contract['id_payroll_scheme_fk']) ?></option>
              </select>
              <label for="id_payroll_scheme_fk" class="form-label">Esquema de Nómina</label>
            </div>
            <div class="form-floating mb-3">
              <input type="date" class="form-control" name="start_date_contract" id="start_date_contract" value="<?= htmlspecialchars($contract['start_date_contract'] ?? '') ?>" readonly disabled>
              <label for="start_date_contract" class="form-label">Fecha de Inicio de Contrato</label>
            </div>
            <div class="form-floating mb-3">
              <input type="date" class="form-control" name="trial_period_contract" id="trial_period_contract" value="<?= htmlspecialchars($contract['trial_period_contract'] ?? '') ?>" readonly disabled>
              <label for="trial_period_contract" class="form-label">Finalización de Periodo de Prueba</label>
            </div>
            <div class="form-floating mb-3">
              <input type="date" class="form-control" name="end_date_contract" id="end_date_contract" value="<?= htmlspecialchars($contract['end_date_contract'] ?? '') ?>" readonly disabled>
              <label for="end_date_contract" class="form-label">Fecha de Finalización de Contrato</label>
            </div>
            <div class="form-floating mb-3">
              <input type="number" class="form-control" name="salary_contract" id="salary_contract" value="<?= htmlspecialchars($contract['salary_contract']) ?>" readonly disabled>
              <label for="salary_contract" class="form-label">Salario</label>
            </div>
            <div class="form-floating mb-3">
              <textarea class="form-control" name="termination_reason_contract" id="termination_reason_contract" readonly disabled><?= htmlspecialchars($contract['termination_reason_contract'] ? $contract['termination_reason_contract'] : 'No Aplica') ?></textarea>
              <label for="termination_reason_contract" class="form-label">Motivo de Terminación de Contrato</label>
            </div>
            <div class="form-check mb-3">
              <input type="checkbox" class="form-check-input" name="is_active" id="is_active" <?= $contract['is_active'] ? 'checked' : '' ?> disabled>
              <label for="is_active" class="form-check-label">Activo</label>
            </div>
          </div>

          <div class="col-md-6">
            <h5>Información de Contacto</h5>
            <div class="form-floating mb-3">
              <input type="text" class="form-control" name="phone_employee_profile" id="phone_employee_profile" value="<?= htmlspecialchars($profile['phone_employee_profile']) ?>" readonly disabled>
              <label for="phone_employee_profile" class="form-label">Teléfono</label>
            </div>
            <div class="form-floating mb-3">
              <input type="text" class="form-control" name="mobile_employee_profile" id="mobile_employee_profile" value="<?= htmlspecialchars($profile['mobile_employee_profile']) ?>" readonly disabled>
              <label for="mobile_employee_profile" class="form-label">Celular</label>
            </div>
            <div class="form-floating mb-3">
              <input type="email" class="form-control" name="email_employee_profile" id="email_employee_profile" value="<?= htmlspecialchars($profile['email_employee_profile']) ?>" readonly disabled>
              <label for="email_employee_profile" class="form-label">Email</label>
            </div>
            <div class="form-floating mb-3">
              <input type="text" class="form-control" name="address_employee_profile" id="address_employee_profile" value="<?= htmlspecialchars($profile['address_employee_profile']) ?>" readonly disabled>
              <label for="address_employee_profile" class="form-label">Dirección</label>
            </div>
            <div class="form-floating mb-3">
              <input type="text" class="form-control" name="emergency_contact_employee_profile" id="emergency_contact_employee_profile" value="<?= htmlspecialchars($profile['emergency_contact_employee_profile']) ?>" readonly disabled>
              <label for="emergency_contact_employee_profile" class="form-label">Contacto de Emergencia</label>
            </div>
            <div class="form-floating mb-3">
              <input type="text" class="form-control" name="emergency_phone_employee_profile" id="emergency_phone_employee_profile" value="<?= htmlspecialchars($profile['emergency_phone_employee_profile']) ?>" readonly disabled>
              <label for="emergency_phone_employee_profile" class="form-label">Teléfono de Emergencia</label>
            </div>
            <div class="form-floating mb-3">
              <input type="text" class="form-control" name="emergency_relationship_employee_profile" id="emergency_relationship_employee_profile" value="<?= htmlspecialchars($profile['emergency_relationship_employee_profile']) ?>" readonly disabled>
              <label for="emergency_relationship_employee_profile" class="form-label">Relación con el Contacto de Emergencia</label>
            </div>
          </div>
        </div>
        </form>
      </div>
      <div class="card-footer text-center">
        <button type="button" id="editBtn" class="btn btn-primary">
          <i class="fa-solid fa-pen-to-square"></i> Editar Perfil
        </button>
        <button type="submit" form="employeeProfileForm" id="saveBtn" class="btn btn-success d-none">
          <i class="fa-solid fa-save"></i> Guardar Cambios
        </button>
        <button type="button" id="cancelBtn" class="btn btn-secondary d-none">
          <i class="fa-solid fa-times"></i> Cancelar
        </button>
        <a href="/employees/list" class="btn btn-secondary">
          <i class="fa-solid fa-arrow-left"></i> Volver a la Lista
        </a>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const editBtn = document.getElementById('editBtn');
      const saveBtn = document.getElementById('saveBtn');
      const cancelBtn = document.getElementById('cancelBtn');
      const form = document.getElementById('employeeProfileForm');
      
      // Get all form controls that should be editable
      const editableFields = [
        'status_employee', 'type_employee', 'birthdate_employee', 'curp_employee', 'nss_employee',
        'gender_employee', 'marital_status_employee', 'account_number_employee', 'bank_employee',
        'number_payroll_contract', 'code_employee_snapshot', 'id_contract_type_fk', 'id_payroll_scheme_fk',
        'start_date_contract', 'trial_period_contract', 'end_date_contract', 'salary_contract',
        'termination_reason_contract', 'is_active', 'phone_employee_profile', 'mobile_employee_profile',
        'email_employee_profile', 'address_employee_profile', 'emergency_contact_employee_profile',
        'emergency_phone_employee_profile', 'emergency_relationship_employee_profile'
      ];
      
      // Store original values
      let originalValues = {};
      
      function storeOriginalValues() {
        editableFields.forEach(fieldName => {
          const field = document.getElementById(fieldName);
          if (field) {
            if (field.type === 'checkbox') {
              originalValues[fieldName] = field.checked;
            } else {
              originalValues[fieldName] = field.value;
            }
          }
        });
      }
      
      function restoreOriginalValues() {
        editableFields.forEach(fieldName => {
          const field = document.getElementById(fieldName);
          if (field && originalValues.hasOwnProperty(fieldName)) {
            if (field.type === 'checkbox') {
              field.checked = originalValues[fieldName];
            } else {
              field.value = originalValues[fieldName];
            }
          }
        });
      }
      
      function toggleEditMode(isEditing) {
        editableFields.forEach(fieldName => {
          const field = document.getElementById(fieldName);
          if (field) {
            field.disabled = !isEditing;
            if (field.hasAttribute('readonly')) {
              if (isEditing) {
                field.removeAttribute('readonly');
              } else {
                field.setAttribute('readonly', 'readonly');
              }
            }
          }
        });
        
        // Toggle button visibility
        if (isEditing) {
          editBtn.classList.add('d-none');
          saveBtn.classList.remove('d-none');
          cancelBtn.classList.remove('d-none');
        } else {
          editBtn.classList.remove('d-none');
          saveBtn.classList.add('d-none');
          cancelBtn.classList.add('d-none');
        }
      }
      
      // Store original values on page load
      storeOriginalValues();
      
      editBtn.addEventListener('click', function() {
        toggleEditMode(true);
      });
      
      cancelBtn.addEventListener('click', function() {
        restoreOriginalValues();
        toggleEditMode(false);
      });
      
      // Employee search functionality
      const modalSearchInput = document.getElementById('modalSearchInput');
      const employeeSearchResults = document.getElementById('employeeSearchResults');
      let searchTimeout;
      
      modalSearchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();
        
        if (query.length < 2) {
          employeeSearchResults.innerHTML = '<p class="text-muted">Ingrese al menos 2 caracteres para buscar.</p>';
          return;
        }
        
        searchTimeout = setTimeout(() => {
          fetch(`/api/employees/search?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
              if (data.length === 0) {
                employeeSearchResults.innerHTML = '<p class="text-muted">No se encontraron empleados.</p>';
                return;
              }
              
              let html = '<div class="list-group">';
              data.forEach(employee => {
                html += `
                  <button type="button" class="list-group-item list-group-item-action" 
                          onclick="selectEmployee(${employee.id_employee}, '${employee.name_employee}', ${employee.has_profile})">
                    <div class="d-flex w-100 justify-content-between">
                      <h6 class="mb-1">${employee.name_employee}</h6>
                      <small>${employee.code_employee}</small>
                    </div>
                    <p class="mb-1">Departamento: ${employee.department || 'N/A'}</p>
                    <small class="text-muted">Estado: ${employee.status_employee}</small>
                    ${!employee.has_profile ? '<span class="badge bg-warning">Sin perfil</span>' : ''}
                  </button>
                `;
              });
              html += '</div>';
              employeeSearchResults.innerHTML = html;
            })
            .catch(error => {
              console.error('Error:', error);
              employeeSearchResults.innerHTML = '<p class="text-danger">Error al buscar empleados.</p>';
            });
        }, 300);
      });
    });
    
    function selectEmployee(employeeId, employeeName, hasProfile) {
      if (hasProfile) {
        // Redirect to view profile
        window.location.href = `/employees/profile/${employeeId}`;
      } else {
        // Redirect to create profile
        window.location.href = `/employees/profile/create/${employeeId}`;
      }
    }
  </script>

<?php $content = ob_get_clean(); include __DIR__.'/../layouts/layout-main.php'; ?>