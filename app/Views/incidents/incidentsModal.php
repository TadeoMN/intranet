<div class="modal fade" id="incidentsModal" tabindex="-1" aria-labelledby="incidentsModalTitle" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="/incidents/store" id="incidentsForm">
        <div class="modal-header bg-dark text-white">
          <h3 class="modal-title" id="incidentsTitleModal">AGREGAR INCIDENCIA</h3>
        </div>
        <div class="modal-body row g-2">
          <div class="input-group col-12" id="incidentSearchGroup">
            <div class="form-floating">
              <input
                type="text"
                class="form-control"
                id="searchIncidentInput"
                placeholder="Buscar Incidencia"
                autocomplete="off"
                aria-autocomplete="list"
                aria-controls="result_incident_search"
                aria-expanded="false"
                data-endpoint="/api/incidents/search"
              >
              <label for="searchIncidentInput">Buscar Incidencia</label>
            </div>
            <button class="btn btn-primary" id="searchIncidentButton" type="button" aria-label="Buscar">
              <i class="fa-solid fa-magnifying-glass tl-icon-xl"></i>
            </button>
          </div>

          <div class="col-12">
            <div class="form-floating" id="card-incident-searching" style="opacity:0; transition:opacity .2s ease;">
              <div class="tl-search-result-container" id="result_incident_search" role="listbox" aria-label="Resultados de búsqueda"></div>
            </div>
          </div>

          <div class="col-12 d-none">
            <div class="form-floating">
              <input type="text" class="form-control" id="id_incident_modal" name="id_incident_modal" placeholder="ID Incidencia" hidden readonly>
              <label for="id_incident_modal" class="form-label">ID Incidencia</label>
            </div>
          </div>

          <div class="col-4">
            <div class="form-floating">
              <input type="text" class="form-control" id="code_incident_modal" name="code_incident_modal" placeholder="Codigo de incidencia" disabled>
              <label for="code_incident_modal" class="form-label">No. de incidencia</label>
            </div>
          </div>

          <div class="col-8">
            <div class="form-floating">
              <input type="text" class="form-control" id="name_incident_type_modal" name="name_incident_type_modal" placeholder="Tipo de incidencia" disabled>
              <label for="name_incident_type_modal" class="form-label">Incidencia</label>
            </div>
          </div>

          <div class="col-12">
            <div class="form-floating">
              <textarea class="form-control" id="description_incident_type_modal" name="description_incident_type_modal" placeholder="Descripción de la incidencia" rows="3" disabled></textarea>
              <label for="description_incident_type_modal" class="form-label">Descripción de la incidencia</label>
            </div>
          </div>

          <div class="col-12">
            <div class="form-floating">
              <textarea class="form-control" id="actions_incident_type_modal" name="actions_incident_type_modal" placeholder="Acciones a tomar" rows="3" disabled></textarea>
              <label for="actions_incident_type_modal" class="form-label">Acciones a tomar</label>
            </div>
          </div>

          <div class="input-group col-12" id="employeeSearchGroup">
            <div class="form-floating">
              <input
                type="text"
                class="form-control"
                id="searchEmployeeInput"
                placeholder="Buscar Empleado"
                autocomplete="off"
                aria-autocomplete="list"
                aria-controls="result_employee_search"
                aria-expanded="false"
                data-endpoint="/api/employees/search"
              >
              <label for="searchEmployeeInput">Buscar Empleado</label>
            </div>
            <button class="btn btn-primary" id="searchEmployeeButton" type="button" aria-label="Buscar">
              <i class="fa-solid fa-magnifying-glass tl-icon-xl"></i>
            </button>
          </div>

          <div class="col-12">
            <div class="form-floating" id="card-employee-searching" style="opacity:0; transition:opacity .2s ease;">
              <div class="tl-search-result-container" id="result_employee_search" role="listbox" aria-label="Resultados de búsqueda"></div>
            </div>
          </div>

          <div class="col-12 d-none">
            <div class="form-floating">
              <input type="text" class="form-control" id="reported_by_modal" name="reported_by_modal" placeholder="ID de usuario" value="<?= $_SESSION['uid'] ?>" hidden readonly>
              <label for="reported_by_modal" class="form-label">ID usuario</label>
            </div>
          </div>

          <div class="col-12 d-none">
            <div class="form-floating">
              <input type="text" class="form-control" id="id_employee_modal" name="id_employee_modal" placeholder="ID de empleado" hidden readonly>
              <label for="id_employee_modal" class="form-label">ID Empleado</label>
            </div>
          </div>
      
          <div class="col-4">
            <div class="form-floating">
              <input type="text" class="form-control" id="code_employee_modal" name="code_employee_modal" placeholder="Codigo de empleado" disabled>
              <label for="code_employee_modal" class="form-label">No. de empleado</label>
            </div>
          </div>

          <div class="col-8">
            <div class="form-floating">
              <input type="text" class="form-control" id="name_employee_modal" name="name_employee_modal" placeholder="Nombre de empleado" disabled>
              <label for="name_employee_modal" class="form-label">Nombre de empleado</label>
            </div>
          </div>

          <div class="col-6 col-md-4">
            <div class="form-floating">
              <input type="text" class="form-control" id="ot_incident_modal" name="ot_incident_modal" placeholder="Ot de incidencia">
              <label for="ot_incident_modal" class="form-label">Orden de trabajo</label>
            </div>
          </div>

          <div class="col-6 col-md-4">
            <div class="form-floating">
              <input type="text" class="form-control" id="waste_incident_modal" name="waste_incident_modal" placeholder="Tipo de incidencia">
              <label for="waste_incident_modal" class="form-label">Desperdicio</label>
            </div>
          </div>

          <div class="col-12 col-md-4">
            <div class="form-floating">
              <select class="form-select" id="identification_incident_modal" name="identification_incident_modal" aria-label="Detección de incidencia" required>
                <option value="INTERNA" selected>Interna</option>
                <option value="EXTERNA">Externa</option>
              </select>
              <label for="identification_incident_modal" class="form-label">Detección de incidencia</label>
            </div>
          </div>

          <div class="col-12">
            <div class="form-floating">
              <textarea class="form-control" id="observation_incident_modal" name="observation_incident_modal" placeholder="Observaciones de la incidencia" rows="6"></textarea>
              <label for="observation_incident_modal" class="form-label">Observaciones de la incidencia</label>
            </div>
          </div>
        </div>

        <div class="modal-footer bg-dark text-white">
          <button type="submit" class="btn btn-success" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Agregar Incidencia" id="saveIncidentButtonModal">
            <i class="fa-solid fa-plus tl-icon-xl"></i>
          </button>
          <button type="button" class="btn btn-primary" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Limpiar formulario" id="clearIncidentButtonModal">
            <i class="fa-solid fa-eraser tl-icon-xl"></i>
          </button>
          <button type="button" class="btn btn-danger" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Cancelar" data-bs-dismiss="modal">
            <i class="fa-solid fa-xmark tl-icon-xl"></i>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>