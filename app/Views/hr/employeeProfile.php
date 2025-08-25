<?php ob_start(); ?>

  <!-- Scrollable Container -->
  <div class="container-fluid container-md tl-scroll my-2 px-1" id="scrollableContainer">
    <!-- Card Container -->
    <div class="card shadow-sm">

      <!-- Card Header -->
      <div class="card-header bg-dark text-white row m-0 py-1 align-items-center">
        <div class="col-12 py-2 px-1">
          <div class="input-group">
            <button class="btn btn-secondary" id="listEmployeesButton" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Lista de Empleados" onclick="window.location.href='/employees/list'">
              <i class="fa-solid fa-list tl-icon-xl"></i>
            </button>
            <button class="btn btn-primary" id="searchEmployeeButton" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Editar Perfil">
              <i class="fa-solid fa-edit tl-icon-xl"></i>
            </button>
            <div class="form-floating">
              <input type="text" class="form-control" id="searchEmployeeInput" placeholder="Buscar Empleado">
              <label for="searchEmployeeInput">Buscar Empleado</label>
            </div>
            <button class="btn btn-primary" id="searchEmployeeButton">
              <i class="fa-solid fa-magnifying-glass tl-icon-xl"></i>
            </button>
          </div>
        </div>
      </div>
      <!-- End Card Header -->

      <!-- Card Body -->
      <form class="card-body" id="employeeProfileForm">
        <!-- Employee Profile Header -->
        <div class="row py-1 g-2">
          <!-- Employee Name and Department -->
          <div class="col-12 mb-2">
              <h5 class="card-title">
                <strong><?= htmlspecialchars($employee['name_employee']) ?></strong>
              </h5>
              <p class="card-text">
                <strong><?= htmlspecialchars($employee['name_department']) ?></strong> | <span class="text-muted"><?= htmlspecialchars($employee['name_position']) ?></span>
              </p>
          </div>

          <!-- Employee Image -->
          <div class="col-8 col-md-4 col-xl-3 m-auto">
            <img src="/assets/images/employeeProfile/<?= $employee['image_employee_profile'] ? htmlspecialchars($employee['image_employee_profile']) : '/assets/images/default-profile.jpg' ?>"
            alt="Foto de <?= $employee['name_employee'] ? $employee['name_employee'] : 'Empleado' ?>"
            class="img-fluid rounded-2 border">
          </div>

          <!-- Employee Profile Details -->
          <div class="col-12 col-md-8 col-xl-9 d-flex flex-column align-items-center justify-content-center">
            <div class="tl-grid-container w-100 mb-3">
              <div>
                <a href="/incidents/list" class="btn btn-action" id="viewIncidentsButton" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Ver Incidencias">
                  <i class="fa-solid fa-triangle-exclamation tl-icon-xl"></i>
                  <span>Incidencias</span>
                </a>
              </div>

              <div>
                <a href="/employees/edit/<?= $employee['id_employee'] ?>" class="btn btn-action" id="editEmployeeButton" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Editar Perfil">
                  <i class="fa-solid fa-pen-to-square tl-icon-xl"></i>
                  <span>Editar</span>
                </a>
              </div>

              <div>
                <a href="/employees/delete/<?= $employee['id_employee'] ?>" class="btn btn-action" id="deleteEmployeeButton" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Eliminar Empleado">
                  <i class="fa-solid fa-user-slash"></i>
                  <span>Eliminar</span>
                </a>
              </div>
            </div>

            <div class="tl-grid-container w-100">
              <!-- Payroll Number -->
              <div class="form-floating">
                <input type="text" class="form-control" name="number_payroll_contract" id="number_payroll_contract" value="<?= htmlspecialchars($employee['number_payroll_contract']) ?>" readonly>
                <label for="number_payroll_contract" class="form-label">Número de Nómina</label>
              </div>
              
              <!-- Employee Code -->
              <div class="form-floating">
                <input type="text" class="form-control" name="code_employee" id="code_employee" value="<?= htmlspecialchars($employee['code_employee']) ?>" readonly>
                <label for="code_employee" class="form-label">Código de Empleado</label>
              </div>

              <!-- Date Hired -->
              <div class="form-floating">
                <input type="text" class="form-control" name="date_hired" id="date_hired" value="<?= date('d/m/Y', strtotime($employee['date_hired'])) ?>" readonly>
                <label for="date_hired" class="form-label">Fecha de Contratación</label>
              </div>
              
              <!-- Status -->
              <div class="form-floating">
                <select class="form-select" name="status_employee" id="status_employee" disabled>
                  <option value="ACTIVO" <?= $employee['status_employee'] === 'ACTIVO' ? 'selected' : '' ?>>ACTIVO</option>
                  <option value="INACTIVO" <?= $employee['status_employee'] === 'INACTIVO' ? 'selected' : '' ?>>INACTIVO</option>
                  <option value="SUSPENDIDO" <?= $employee['status_employee'] === 'SUSPENDIDO' ? 'selected' : '' ?>>SUSPENDIDO</option>
                </select>
                <label for="status_employee" class="form-label">Estatus</label>
              </div>

              <!-- Employee Type -->
              <div class="form-floating">
                <select class="form-select" name="type_employee" id="type_employee" disabled>
                  <option value="OPERATIVO" <?= $employee['type_employee'] === 'OPERATIVO' ? 'selected' : '' ?>>OPERATIVO</option>
                  <option value="ADMINISTRATIVO" <?= $employee['type_employee'] === 'ADMINISTRATIVO' ? 'selected' : '' ?>>ADMINISTRATIVO</option>
                </select>
                <label for="type_employee" class="form-label">Tipo de Empleado</label>
              </div>

              <!-- Seniority -->
              <div class="form-floating">
                <input type="text" class="form-control" name="seniority_employee" id="seniority_employee" value="<?= htmlspecialchars($employee['seniority_employee']) ?> años" readonly>
                <label for="seniority_employee" class="form-label">Antigüedad</label>
              </div>

              <!-- Department -->
              <div class="form-floating d-none">
                <select name="name_department" id="name_department" class="form-select" disabled>
                  <option value="" disabled selected>Seleccione un departamento</option>
                  <?php foreach ($departments as $department): ?>
                    <option value="<?= $department['id_department'] ?>" <?= $employee['id_department_fk'] === $department['id_department'] ? 'selected' : '' ?>>
                      <?= htmlspecialchars($department['name_department']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
                <label for="name_department" class="form-label">Departamento</label>
              </div>
              
              <!-- Position -->
              <div class="form-floating d-none">
                <select name="name_position" id="name_position" class="form-select" disabled>
                  <option value="<?= htmlspecialchars($employee['id_position_fk']) ?>" selected>
                    <?= htmlspecialchars($employee['name_position']) ?>
                  </option>
                </select>
                <label for="name_position" class="form-label">Puesto</label>
              </div>
            </div>
          </div>
        </div>
        <!-- End Employee Profile Header -->

        <div><hr></div> <!-- Divider -->

        <!-- Personal Information Header -->
        <div class="row py-1 g-2"> 
          <div class="col-12">
            <h5><strong>Información Personal</strong></h5>
          </div>
        </div>
        <!-- End Personal Information Header -->

        <!-- Personal Information Details -->
        <div class="row py-1 g-2">
          <!-- Personal Information -->
          <div class="col-6 col-md-3">
            <div class="form-floating">
              <input type="date" class="form-control" name="birthdate_employee" id="birthdate_employee" value="<?= htmlspecialchars($profile['birthdate_employee_profile']) ?>" readonly>
              <label for="birthdate_employee" class="form-label">Fecha de Nacimiento</label>
            </div>
          </div>
          <div class="col-6 col-md-3">
            <div class="form-floating">
              <select class="form-select" name="gender_employee" id="gender_employee" aria-label="Género" disabled>
                <?php if (!$profile['gender_employee_profile']): ?>
                  <option value="" disabled selected>Seleccione un género</option>
                <?php endif; ?>
                <!-- Optimized Gender Selection -->
                <?php
                  $genders = $genders ?? [];
                  foreach ($genders as $gender):
                ?>
                  <option value="<?= $gender ?>" <?= $profile['gender_employee_profile'] === $gender ? 'selected' : '' ?>><?= $gender ?></option>
                <?php endforeach; ?>
              </select>
              <label for="gender_employee" class="form-label">Género</label>
            </div>
          </div>
          <div class="col-6 col-md-3">
            <div class="form-floating">
              <select class="form-select" name="marital_status_employee" id="marital_status_employee" aria-label="Estado Civil" disabled>
                <?php if (!$profile['marital_status_employee_profile']): ?>
                  <option value="" disabled selected>Seleccione un estado civil</option>
                <?php endif; ?>
                <!-- Optimized Marital Status Selection -->
                <?php
                  $maritalStatuses = $maritalStatuses ?? [];
                  foreach ($maritalStatuses as $marital_status):
                ?>
                  <option value="<?= $marital_status ?>" <?= $profile['marital_status_employee_profile'] === $marital_status ? 'selected' : '' ?>><?= $marital_status ?></option>
                <?php endforeach; ?>
              </select>
              <label for="marital_status_employee" class="form-label">Estado Civil</label>
            </div>
          </div>
          <div class="col-6 col-md-3">
            <div class="form-floating">
              <select class="form-select" name="blood_type_employee" id="blood_type_employee" aria-label="Tipo de Sangre" disabled>
                <?php if (!$profile['blood_type_employee_profile']): ?>
                  <option value="" disabled selected>Seleccione un tipo de sangre</option>
                <?php endif; ?>
                <!-- Optimized Blood Type Selection -->
                <?php
                  $bloodTypes = $bloodTypes ?? [];
                  foreach ($bloodTypes as $bloodType):
                ?>
                  <option value="<?= $bloodType ?>" <?= $profile['blood_type_employee_profile'] === $bloodType ? 'selected' : '' ?>><?= $bloodType ?></option>
                <?php endforeach; ?>
              </select>
              <label for="blood_type_employee" class="form-label">Tipo de Sangre</label>
            </div>
          </div>
      
          <!-- Personal Identification -->
          <div class="col-12 col-md-4">
            <div class="form-floating">
              <input type="text" class="form-control" name="curp_employee" id="curp_employee" value="<?= htmlspecialchars($profile['curp_employee_profile']) ?>" readonly maxlength="18">
              <label for="curp_employee" class="form-label">CURP</label>
            </div>
          </div>
          <div class="col-6 col-md-4">
            <div class="form-floating">
              <input type="text" class="form-control" name="rfc_employee" id="rfc_employee" value="<?= htmlspecialchars($profile['rfc_employee_profile']) ?>" readonly maxlength="13">
              <label for="rfc_employee" class="form-label">RFC</label>
            </div>
          </div>
          <div class="col-6 col-md-4">
            <div class="form-floating">
              <input type="text" class="form-control" name="nss_employee" id="nss_employee" value="<?= htmlspecialchars($profile['ssn_employee_profile']) ?>" readonly maxlength="11">
              <label for="nss_employee" class="form-label">NSS</label>
            </div>
          </div>
          
          <!-- Bank Account Information And Digital File -->
          <?php 
            $fullAccountNumber = htmlspecialchars($profile['account_number_employee_profile']);
            $lastFourDigits = preg_replace('/.(?=.{4})/', '•', $fullAccountNumber);
          ?>
          <div class="col-12 col-md-4">
            <div class="form-floating">
              <input type="text" class="form-control" name="bank_employee" id="bank_employee" value="<?= htmlspecialchars($profile['bank_employee_profile']) ?>" readonly>
              <label for="bank_employee" class="form-label">Banco</label>
            </div>
          </div>
          <div class="col-12 col-md-4">
            <div class="input-group">
              <div class="form-floating">
                <input type="text" class="form-control" name="account_number_employee" id="account_number_employee" value="<?= $lastFourDigits ?>" readonly>
                <label for="account_number_employee" class="form-label">Cuenta Bancaria</label>
              </div>
              <a class="btn btn-warning d-flex justify-content-center align-items-center" id="viewAccountButton" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="Ver Número Completo"
                      data-full="<?= htmlspecialchars($fullAccountNumber) ?>" data-show="0">
                <i class="fa-solid fa-eye tl-icon-xl"></i>
              </a>
            </div>
          </div>
          <div class="col-12 col-md-4">
          <?php if ($profile['digital_file_employee_profile']): ?>
            <div class="input-group">
              <div class="form-floating">
                <input type="text" class="form-control" name="digital_file" id="digital_file" value="<?= htmlspecialchars($profile['digital_file_employee_profile']) ?>" disabled>
                <label for="digital_file" class="form-label">Expediente Digital</label>
              </div>
              <a class="btn btn-warning d-flex justify-content-center align-items-center" id="viewDigitalFileButton" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Ver Expediente Digital"
                  href="/assets/docs/digitalFiles/<?= htmlspecialchars($profile['digital_file_employee_profile']) ?>" target="_blank">
                <i class="fa-solid fa-eye tl-icon-xl"></i>
              </a>
            </div> 
          <?php else: ?>
            <div class="form-floating">
              <input type="text" class="form-control" name="digital_file" id="digital_file" value="No hay expediente digital disponible" readonly>
              <label for="digital_file" class="form-label">Expediente Digital</label>
            </div>
          <?php endif; ?>
          </div>
        </div>
        <!-- End Personal Information Details -->

        <div><hr></div> <!-- Divider -->

        <!-- Contact Information Header -->
        <div class="row py-1"> 
          <div class="col-12">
            <h5><strong>Información de Contacto</strong></h5>
          </div>
        </div>
        <!-- End Contact Information Header -->

        <!-- Contact Information Details -->
        <div class="row py-1 g-2">
          <!-- Contact Information -->
          <div class="col-6 col-md-4">
            <div class="form-floating">
              <input type="text" data-bs-toggle="tooltip" data-bs-placement="top" title="Celular" aria-label="Celular"
              class="form-control" name="mobile_employee_profile" id="mobile_employee_profile" value="<?= htmlspecialchars($profile['mobile_employee_profile']) ?>" readonly>
              <label for="mobile_employee_profile" class="form-label">Celular</label>
            </div>
          </div>
          <div class="col-6 col-md-4">
            <div class="form-floating">
              <input type="text" data-bs-toggle="tooltip" data-bs-placement="top" title="Teléfono Fijo" aria-label="Teléfono Fijo"
              class="form-control" name="phone_employee_profile" id="phone_employee_profile" value="<?= htmlspecialchars($profile['phone_employee_profile']) ?>" readonly>
              <label for="phone_employee_profile" class="form-label">Teléfono Fijo</label>
            </div>
          </div>
          <div class="col-12 col-md-4">
            <div class="form-floating">
              <input type="email" data-bs-toggle="tooltip" data-bs-placement="top" title="Email" aria-label="Email"
              class="form-control" name="email_employee_profile" id="email_employee_profile" value="<?= htmlspecialchars($profile['email_employee_profile']) ?>" readonly>
              <label for="email_employee_profile" class="form-label">Email</label>
            </div>
          </div>
          <div class="col-12">
            <div class="form-floating">
              <input type="text" data-bs-toggle="tooltip" data-bs-placement="top" title="Dirección" aria-label="Dirección"
              class="form-control" name="address_employee_profile" id="address_employee_profile" value="<?= htmlspecialchars($profile['address_employee_profile']) ?>" readonly>
              <label for="address_employee_profile" class="form-label">Dirección</label>
            </div>
          </div>
        
          <!-- Emergency Contact Information -->
          <div class="col-12 col-md-4">
            <div class="form-floating">
              <input type="text" data-bs-toggle="tooltip" data-bs-placement="top" title="Contacto de Emergencia" aria-label="Contacto de Emergencia"
              class="form-control" name="emergency_contact_employee_profile" id="emergency_contact_employee_profile" value="<?= htmlspecialchars($profile['emergency_contact_employee_profile']) ?>" readonly>
              <label for="emergency_contact_employee_profile" class="form-label">Contacto de Emergencia</label>
            </div>
          </div>
          <div class="col-6 col-md-4">
            <div class="form-floating">
              <input type="text" data-bs-toggle="tooltip" data-bs-placement="top" title="Teléfono de Contacto de Emergencia" aria-label="Teléfono de Contacto de Emergencia"
              class="form-control" name="emergency_phone_employee_profile" id="emergency_phone_employee_profile" value="<?= htmlspecialchars($profile['emergency_phone_employee_profile']) ?>" readonly>
              <label for="emergency_phone_employee_profile" class="form-label">Teléfono de Contacto de Emergencia</label>
            </div>
          </div>
          <div class="col-6 col-md-4">
            <div class="form-floating">
              <input type="text" data-bs-toggle="tooltip" data-bs-placement="top" title="Relación con el Contacto de Emergencia" aria-label="Relación con el Contacto de Emergencia"
              class="form-control" name="emergency_relationship_employee_profile" id="emergency_relationship_employee_profile" value="<?= htmlspecialchars($profile['emergency_relationship_employee_profile']) ?>" readonly>
              <label for="emergency_relationship_employee_profile" class="form-label">Relación con el Contacto de Emergencia</label>
            </div>
          </div>
        </div>
        <!-- End Contact Information Details -->

        <div><hr></div> <!-- Divider -->

        <!-- Contract Information Header -->
        <div class="row py-1">
          <div class="col-12">
            <h5><strong>Información de Contrato</strong></h5>
          </div>
        </div>
        <!-- End Contract Information Header -->

        <!-- Contract Information Details -->
        <div class="row py-1 g-2">
          <!-- Contract Information -->
          <div class="col-6 col-md-4">
            <div class="form-floating">
              <input type="text" class="form-control" name="number_payroll_contract" id="number_payroll_contract" value="<?= htmlspecialchars($contract['number_payroll_contract']) ?>" readonly>
              <label for="number_payroll_contract" class="form-label">Número de Nómina</label>
            </div>
          </div>
          <div class="col-6 col-md-4">
            <div class="form-floating">
              <input type="text" class="form-control" name="code_employee_snapshot" id="code_employee_snapshot" value="<?= htmlspecialchars($contract['code_employee_snapshot']) ?>" readonly>
              <label for="code_employee_snapshot" class="form-label">Código de Empleado</label>
            </div>
          </div>
          <div class="col-6 col-md-4">
            <div class="form-floating">
              <select class="form-select" name="id_contract_type_fk" id="id_contract_type_fk" disabled>
                <option value="<?= htmlspecialchars($contract['id_contract_type_fk']) ?>"><?= htmlspecialchars($contract['name_contract_type']) ?></option>
              </select>
              <label for="id_contract_type_fk" class="form-label">Tipo de Contrato</label>
            </div>
          </div>
          <div class="col-6 col-md-6">
            <div class="form-floating">
              <input type="number" class="form-control" name="salary_contract" id="salary_contract" value="<?= htmlspecialchars($contract['salary_contract']) ?>" readonly>
              <label for="salary_contract" class="form-label">Salario</label>
            </div>
          </div>
          <div class="col-12 col-md-6">
            <div class="form-floating">
              <select class="form-select" name="id_payroll_scheme_fk" id="id_payroll_scheme_fk" disabled>
                <option value="<?= htmlspecialchars($contract['id_payroll_scheme_fk']) ?>"><?= htmlspecialchars($contract['name_payroll_scheme']) ." - ". htmlspecialchars($contract['frequency_payroll_scheme']) ?></option>
              </select>
              <label for="id_payroll_scheme_fk" class="form-label">Esquema de Nómina</label>
            </div>
          </div>
        
          <!-- Contract Dates -->
          <div class="col-12 col-md-6">
            <div class="form-floating">
              <input type="date" class="form-control" name="start_date_contract" id="start_date_contract" value="<?= htmlspecialchars($contract['start_date_contract'] ?? '') ?>" readonly>
              <label for="start_date_contract" class="form-label">Fecha de Contratación</label>
            </div>
          </div>
          <div class="col-12 col-md-6">
            <div class="form-floating">
              <input type="date" class="form-control" name="trial_period_contract" id="trial_period_contract" value="<?= htmlspecialchars($contract['trial_period_contract'] ?? '') ?>" readonly>
              <label for="trial_period_contract" class="form-label">Fin de Periodo de Prueba</label>
            </div>
          </div>

          <!-- Contract Termination -->
          <div class="col-12 col-lg-6">
            <div class="form-floating">
              <input type="date" class="form-control" name="end_date_contract" id="end_date_contract" value="<?= htmlspecialchars($contract['end_date_contract'] ?? '') ?>" readonly>
              <label for="end_date_contract" class="form-label">Fecha de Baja</label>
            </div>
          </div>
          <div class="col-12 col-lg-6">
            <div class="form-floating">
              <textarea
                class="form-control" name="termination_reason_contract" id="termination_reason_contract"
                readonly><?= htmlspecialchars($contract['termination_reason_contract'] ? $contract['termination_reason_contract'] : 'No Aplica') ?>
              </textarea>
              <label for="termination_reason_contract" class="form-label">Motivo de Baja</label>
            </div>
          </div>
        </div>
        <!-- End Contract Information Details -->
      </form>
      <!-- End Card Body -->

      <!-- Card Footer -->
      <div class="card-footer text-center bg-dark text-white">
        <div class="row py-1 g-2">
          <div class="col-12">
            <a class="btn btn-secondary" id="backToTopButton" data-bs-toggle="tooltip" data-bs-placement="top" title="Volver Arriba">
              <i class="fa-solid fa-arrow-up tl-icon-xl"></i>
            </a>
          </div>
        </div>
      </div>
      <!-- End Card Footer -->
    </div>
    <!-- End Card Container -->
  </div>
  <!-- End Scrollable Container -->

<?php $content = ob_get_clean(); include __DIR__.'/../layouts/layout-main.php'; ?>