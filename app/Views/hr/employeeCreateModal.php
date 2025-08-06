<div class="modal fade" id="createEmployeeModal" tabindex="-1" aria-labelledby="createEmployeeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="/employee/store" id="createEmployeeForm">
        <div class="modal-header bg-dark text-white">
          <h3 class="modal-title" id="modalTitle">AGREGAR EMPLEADO</h3>
        </div>
        <div class="modal-body">
          <input type="hidden" id="id_employee" name="id_employee" value="">
          <div class="form-floating mb-3">
            <input type="text" class="form-control" id="name_employee" name="name_employee" required placeholder="">
            <label for="name_employee" class="form-label">Nombre del Empleado</label>
          </div>
          <div class="form-floating mb-3">
            <input type="date" class="form-control" id="date_hired" name="date_hired" required>
            <label for="date_hired" class="form-label">Fecha de Contratación</label>
          </div>

         <div class="form-floating mb-3">
            <select class="form-select tl-edit-only" id="status_employee" name="status_employee" required>
              <option value="ACTIVO">ACTIVO</option>
              <option value="INACTIVO">INACTIVO</option>
              <option value="SUSPENDIDO">SUSPENDIDO</option>
            </select>
            <label for="status_employee" class="form-label tl-edit-only">Estado del Empleado</label>
          </div>

          <div class="form-floating mb-3">
            <select class="form-select" id="type_employee" name="type_employee" required>
              <option value="OPERATIVO">OPERATIVO</option>
              <option value="ADMINISTRATIVO">ADMINISTRATIVO</option>
            </select>
            <label for="type_employee" class="form-label">Tipo de Empleado</label>
          </div>

          <div class="form-floating mb-3">
            <select class="form-select" id="id_department_fk" name="id_department_fk" required>
              <option value="">Seleccione un departamento</option>
              <?php foreach ($departments as $department): ?>
                <option value="<?= $department['id_department'] ?>"><?= htmlspecialchars($department['name_department']) ?></option>
              <?php endforeach; ?>
            </select>
            <label for="id_department_fk" class="form-label">Departamento</label>
          </div>

          <div class="form-floating mb-3">
            <select class="form-select" id="id_position_fk" name="id_position_fk" required>
              <option value="">Seleccione un puesto</option>
            </select>
            <label for="id_position_fk" class="form-label">Puesto</label>
          </div>

          <?= cascadePosition($positionsByDepartment) ?>

        </div>
        <div class="modal-footer bg-dark text-white">
          <button type="submit" class="btn btn-success" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Agregar Empleado" id="submitButton">
            <i class="fa-solid fa-plus tl-icon-xl"></i>
          </button>
          <button type="button" class="btn btn-danger" data-bs-dismiss="modal" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Cancelar">
            <i class="fa-solid fa-xmark tl-icon-xl"></i>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>