
<div class="modal fade" id="incidentsModal" tabindex="-1" aria-labelledby="incidentsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="/incidents/store" id="incidentsForm">
        <div class="modal-header bg-dark text-white">
          <h3 class="modal-title" id="modalTitle">AGREGAR INCIDENCIA</h3>
        </div>
        <div class="modal-body">
          <input type="hidden" id="id_incident" name="id_incident" value="">

          <div class="input-group">
            <div class="form-floating">
              <input type="text" class="form-control" id="searchEmployeeInput" placeholder="Buscar Empleado">
              <label for="searchEmployeeInput">Buscar Empleado</label>
            </div>
            <button class="btn btn-primary" id="searchEmployeeButton">
              <i class="fa-solid fa-magnifying-glass tl-icon-xl"></i>
            </button>
          </div>

          <div class="form-floating mb-3" id="card-searching">
            <div class="tl-search-result-container" id="result_search">

            </div>
          </div>


          <div class="form-floating mb-3">
            <input type="text" class="form-control" id="title_incident" name="title_incident" required placeholder="">
            <label for="title_incident" class="form-label">Título de la Incidencia</label>
          </div>

          <div class="form-floating mb-3">
            <textarea class="form-control" id="description_incident" name="description_incident" required placeholder=""></textarea>
            <label for="description_incident" class="form-label">Descripción de la Incidencia</label>
          </div>

          <div class="form-floating mb-3">
            <select class="form-select tl-edit-only" id="status_incident" name="status_incident" required>
              <option value="PENDIENTE">PENDIENTE</option>
              <option value="EN PROCESO">EN PROCESO</option>
              <option value="RESUELTA">RESUELTA</option>
            </select>
            <label for="status_incident" class="form-label tl-edit-only">Estado de la Incidencia</label>
          </div>
          <div class="form-floating mb-3">
            <select class="form-select" id="id_employee_fk" name="id_employee_fk" required>
              <option value="">Seleccione un empleado</option>
              <?php foreach ($employees as $employee): ?>
                <option value="<?= $employee['id_employee'] ?>"><?= htmlspecialchars($employee['name_employee']) ?></option>
              <?php endforeach; ?>
            </select>
            <label for="id_employee_fk" class="form-label">Empleado Asociado</label>
          </div>
          <div class="form-floating mb-3">
            <select class="form-select" id="id_department_fk" name="id_department_fk" required>
              <option value="">Seleccione un departamento</option>
              <?php foreach ($departments as $department): ?>
                <option value="<?= $department['id_department'] ?>"><?= htmlspecialchars($department['name_department']) ?></option>
              <?php endforeach; ?>
            </select>
            <label for="id_department_fk" class="form-label">Departamento Asociado</label>
          </div>
          <div class="form-floating mb-3">
            <select class="form-select" id="id_position_fk" name="id_position_fk" required>
              <option value="">Seleccione un puesto</option>
            </select>
            <label for="id_position_fk" class="form-label">Puesto Asociado</label>
          </div>
          <?= cascadePosition($positionsByDepartment) ?>
        </div>
        <div class="modal-footer bg-dark text-white">
          <button type="submit" class="btn btn-success" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Agregar Incidencia" id="submitButton">
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