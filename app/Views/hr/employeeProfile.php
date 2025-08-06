
<?php ob_start(); ?>

  <div class="container my-4">
    <div class="card">
      <div class="card-header bg-dark text-white">
        <div class="d-flex align-items-center justify-content-start">
          <img src="/assets/images/profile-image.jpg"
          alt="Foto de <?= $employee['name_employee'] ? $employee['name_employee'] : 'Empleado' ?>"
          class="img-fluid mx-3 rounded-2" style="max-width: 150px; max-height: 150px;">
          <h2 class="card-title text-center my-auto">
            <strong><?= htmlspecialchars($employee['name_employee'])?></strong>
          </h2>
        </div>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6">
            <h5>Información de Empleado</h5>
            <div class="form-floating mb-3">
              <input type="text" class="form-control" name="code_employee" id="code_employee" value="<?= htmlspecialchars($employee['code_employee']) ?>" readonly>
              <label for="code_employee" class="form-label">Código del Empleado</label>
            </div>
            <div class="form-floating mb-3">
              <input type="date" class="form-control" name="date_hired" id="date_hired" value="<?= htmlspecialchars($employee['date_hired']) ?>" readonly>
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
              <input type="text" class="form-control" name="seniority_employee" id="seniority_employee" value="<?= htmlspecialchars($employee['seniority_employee']) ?> años" readonly>
              <label for="seniority_employee" class="form-label">Antigüedad</label>
            </div>
          </div>

          <div class="col-md-6">
            <h5>Información Personal</h5>
            
            <div class="form-floating mb-3">
              <input type="date" class="form-control" name="birthdate_employee" id="birthdate_employee" value="<?= htmlspecialchars($profile['birthdate_employee_profile']) ?>" readonly>
              <label for="birthdate_employee" class="form-label">Fecha de Nacimiento</label>
            </div>
            <div class="form-floating mb-3">
              <input type="text" class="form-control" name="curp_employee" id="curp_employee" value="<?= htmlspecialchars($profile['curp_employee_profile']) ?>" readonly>
              <label for="curp_employee" class="form-label">CURP</label>
            </div>
            <div class="form-floating mb-3">
              <input type="text" class="form-control" name="nss_employee" id="nss_employee" value="<?= htmlspecialchars($profile['ssn_employee_profile']) ?>" readonly>
              <label for="nss_employee" class="form-label">NSS</label>
            </div>
            <div class="form-floating mb-3">
              <input type="text" class="form-control" name="gender_employee" id="gender_employee" value="<?= htmlspecialchars($profile['gender_employee_profile']) ?>" readonly>
              <label for="gender_employee" class="form-label">Género</label>
            </div>
            <div class="form-floating mb-3">
              <input type="text" class="form-control" name="marital_status_employee" id="marital_status_employee" value="<?= htmlspecialchars($profile['marital_status_employee_profile']) ?>" readonly>
              <label for="marital_status_employee" class="form-label">Estado Civil</label>
            </div>
            <div class="form-floating mb-3">
              <input type="text" class="form-control" name="account_number_employee" id="account_number_employee" value="<?= htmlspecialchars($profile['account_number_employee_profile']) ?>" readonly>
              <label for="account_number_employee" class="form-label">Cuenta Bancaria</label>
            </div>
            <div class="form-floating mb-3">
              <input type="text" class="form-control" name="bank_employee" id="bank_employee" value="<?= htmlspecialchars($profile['bank_employee_profile']) ?>" readonly>
              <label for="bank_employee" class="form-label">Banco</label>
            </div>
          </div>
        </div>
  
        <div class="row mt-4">
          <div class="col-md-6">
            <h5>Información de Contrato</h5>

            <div class="form-floating mb-3">
              <input type="text" class="form-control" name="number_payroll_contract" id="number_payroll_contract" value="<?= htmlspecialchars($contract['number_payroll_contract']) ?>" readonly>
              <label for="number_payroll_contract" class="form-label">Número de Nómina</label>
            </div>
            <div class="form-floating mb-3">
              <input type="text" class="form-control" name="code_employee_snapshot" id="code_employee_snapshot" value="<?= htmlspecialchars($contract['code_employee_snapshot']) ?>" readonly>
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
              <input type="date" class="form-control" name="start_date_contract" id="start_date_contract" value="<?= htmlspecialchars($contract['start_date_contract'] ?? '') ?>" readonly>
              <label for="start_date_contract" class="form-label">Fecha de Inicio de Contrato</label>
            </div>
            <div class="form-floating mb-3">
              <input type="date" class="form-control" name="trial_period_contract" id="trial_period_contract" value="<?= htmlspecialchars($contract['trial_period_contract'] ?? '') ?>" readonly>
              <label for="trial_period_contract" class="form-label">Finalización de Periodo de Prueba</label>
            </div>
            <div class="form-floating mb-3">
              <input type="date" class="form-control" name="end_date_contract" id="end_date_contract" value="<?= htmlspecialchars($contract['end_date_contract'] ?? '') ?>" readonly>
              <label for="end_date_contract" class="form-label">Fecha de Finalización de Contrato</label>
            </div>
            <div class="form-floating mb-3">
              <input type="number" class="form-control" name="salary_contract" id="salary_contract" value="<?= htmlspecialchars($contract['salary_contract']) ?>" readonly>
              <label for="salary_contract" class="form-label">Salario</label>
            </div>
            <div class="form-floating mb-3">
              <textarea class="form-control" name="termination_reason_contract" id="termination_reason_contract" readonly><?= htmlspecialchars($contract['termination_reason_contract'] ? $contract['termination_reason_contract'] : 'No Aplica') ?></textarea>
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
              <input type="text" class="form-control" name="phone_employee_profile" id="phone_employee_profile" value="<?= htmlspecialchars($profile['phone_employee_profile']) ?>" readonly>
              <label for="phone_employee_profile" class="form-label">Teléfono</label>
            </div>
            <div class="form-floating mb-3">
              <input type="text" class="form-control" name="mobile_employee_profile" id="mobile_employee_profile" value="<?= htmlspecialchars($profile['mobile_employee_profile']) ?>" readonly>
              <label for="mobile_employee_profile" class="form-label">Celular</label>
            </div>
            <div class="form-floating mb-3">
              <input type="email" class="form-control" name="email_employee_profile" id="email_employee_profile" value="<?= htmlspecialchars($profile['email_employee_profile']) ?>" readonly>
              <label for="email_employee_profile" class="form-label">Email</label>
            </div>
            <div class="form-floating mb-3">
              <input type="text" class="form-control" name="address_employee_profile" id="address_employee_profile" value="<?= htmlspecialchars($profile['address_employee_profile']) ?>" readonly>
              <label for="address_employee_profile" class="form-label">Dirección</label>
            </div>
            <div class="form-floating mb-3">
              <input type="text" class="form-control" name="emergency_contact_employee_profile" id="emergency_contact_employee_profile" value="<?= htmlspecialchars($profile['emergency_contact_employee_profile']) ?>" readonly>
              <label for="emergency_contact_employee_profile" class="form-label">Contacto de Emergencia</label>
            </div>
            <div class="form-floating mb-3">
              <input type="text" class="form-control" name="emergency_phone_employee_profile" id="emergency_phone_employee_profile" value="<?= htmlspecialchars($profile['emergency_phone_employee_profile']) ?>" readonly>
              <label for="emergency_phone_employee_profile" class="form-label">Teléfono de Emergencia</label>
            </div>
            <div class="form-floating mb-3">
              <input type="text" class="form-control" name="emergency_relationship_employee_profile" id="emergency_relationship_employee_profile" value="<?= htmlspecialchars($profile['emergency_relationship_employee_profile']) ?>" readonly>
              <label for="emergency_relationship_employee_profile" class="form-label">Relación con el Contacto de Emergencia</label>
            </div>
          </div>
        </div>
      </div>
      <div class="card-footer text-center">
        <a href="/employees/edit/<?= $employee['id_employee'] ?>" class="btn btn-primary">
          <i class="fa-solid fa-pen-to-square"></i> Editar
        </a>
        <a href="/employees/list" class="btn btn-secondary">
          <i class="fa-solid fa-arrow-left"></i> Volver a la Lista
        </a>
      </div>
    </div>
  </div>

<?php $content = ob_get_clean(); include __DIR__.'/../layouts/layout-main.php'; ?>