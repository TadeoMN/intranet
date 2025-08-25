<div class="modal fade" id="incidentsModal" tabindex="-1" aria-labelledby="incidentsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="/incidents/store" id="incidentsForm">
        <div class="modal-header bg-dark text-white">
          <h3 class="modal-title" id="incidentsModalTitle">AGREGAR INCIDENCIA</h3>
        </div>
        <div class="modal-body row g-2">
          <div class="input-group col-12">
            <div class="form-floating">
              <input type="text" class="form-control" id="searchIncidentInput"
                placeholder="Buscar Incidencia" autocomplete="off" aria-autocomplete="list"
                aria-controls="result_search" aria-expanded="false" data-endpoint="/api/incident/search" >
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

          <div class="col-4">
            <div class="form-floating">
              <input type="text" class="form-control" id="code_incident" name="code_incident" placeholder="Codigo de incidencia" readonly>
              <label for="code_incident" class="form-label">No. de incidencia</label>
            </div>
          </div>

          <div class="col-8">
            <div class="form-floating">
              <input type="text" class="form-control" id="name_incident_type" name="name_incident_type" placeholder="Tipo de incidencia" readonly>
              <label for="name_incident_type" class="form-label">Incidencia</label>
            </div>
          </div>

          <div class="col-12">
            <div class="form-floating">
              <textarea class="form-control" id="description_incident_type" name="description_incident_type" placeholder="Descripción de la incidencia" rows="3" readonly></textarea>
              <label for="description_incident_type" class="form-label">Descripción de la incidencia</label>
            </div>
          </div>

          <div class="col-12">
            <div class="form-floating">
              <textarea class="form-control" id="actions_incident_type" name="actions_incident_type" placeholder="Acciones a tomar" rows="3" readonly></textarea>
              <label for="actions_incident_type" class="form-label">Acciones a tomar</label>
            </div>
          </div>

          <div class="input-group col-12">
            <div class="form-floating">
              <input type="text" class="form-control" id="searchEmployeeInput"
                placeholder="Buscar Empleado" autocomplete="off" aria-autocomplete="list"
                aria-controls="result_search" aria-expanded="false" data-endpoint="/api/employee/search" >
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
      
          <div class="col-4">
            <div class="form-floating">
              <input type="text" class="form-control" id="code_employee" name="code_employee" placeholder="Codigo de empleado" readonly>
              <label for="code_employee" class="form-label">No. de empleado</label>
            </div>
          </div>

          <div class="col-8">
            <div class="form-floating">
              <input type="text" class="form-control" id="employee_name" name="employee_name" placeholder="Nombre de empleado" readonly>
              <label for="employee_name" class="form-label">Nombre de empleado</label>
            </div>
          </div>

          <div class="col-6 col-md-4">
            <div class="form-floating">
              <input type="text" class="form-control" id="ot_indicent" name="ot_indicent" placeholder="OT de incidencia">
              <label for="ot_indicent" class="form-label">Orden de trabajo</label>
            </div>
          </div>

          <div class="col-6 col-md-4">
            <div class="form-floating">
              <input type="text" class="form-control" id="waste_incident" name="waste_incident" placeholder="Tipo de incidencia">
              <label for="waste_incident" class="form-label">Desperdicio</label>
            </div>
          </div>

          <div class="col-12 col-md-4">
            <div class="form-floating">
              <select class="form-select" id="detection_incident" name="detection_incident" aria-label="Detección de incidencia">
                <option value="Interna" selected>Interna</option>
                <option value="Externa">Externa</option>
              </select>
              <label for="detection_incident" class="form-label">Detección de incidencia</label>
            </div>
          </div>

          <div class="col-12">
            <div class="form-floating">
              <textarea class="form-control" id="remark_incident" name="remark_incident" placeholder="Observaciones de la incidencia" rows="6"></textarea>
              <label for="remark_incident" class="form-label">Observaciones de la incidencia</label>
            </div>
          </div>
        </div>

        <div class="modal-footer bg-dark text-white">
          <button type="submit" class="btn btn-success" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Agregar Incidencia" id="incidentsSubmitButton">
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