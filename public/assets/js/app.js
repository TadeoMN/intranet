  // Initialize tooltips when DOM is ready / Inicializar tooltips cuando el DOM esté listo
  document.addEventListener('DOMContentLoaded', function() {
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
  });

  const modalElement = new bootstrap.Modal(document.getElementById('createEmployeeModal'));
  const formElement = document.getElementById('createEmployeeForm');
  const modalTitle = document.getElementById('modalTitle');
  const hiddenIdInput = document.getElementById('id_employee');
  const btnNewEmployee = document.querySelector('.tl-btn-new-employee');
  const btnEditEmployee = document.querySelectorAll('.tl-btn-edit-employee');
  const edit_only = document.querySelectorAll('.tl-edit-only');
  const submitButton = document.getElementById('submitButton');

  btnNewEmployee.addEventListener('click', function() {
    modalTitle.textContent = 'AGREGAR EMPLEADO';
    edit_only.forEach(element => { element.style.display = 'none'; });
    submitButton.setAttribute('data-bs-original-title', 'Agregar Empleado');
    submitButton.setAttribute('class', 'btn btn-success');
    submitButton.innerHTML = '<i class="fa-solid fa-plus tl-icon-xl"></i>';
    formElement.action = '/employee/store';
    formElement.reset();
    hiddenIdInput.value = '';
    id_department_fk.dispatchEvent(new Event('change'));
  });

  btnEditEmployee.forEach(button => {
    button.addEventListener('click', async function() {
      const id_employee = this.getAttribute('data-id');
      const response = await fetch(`/api/employee/${id_employee}`);
      const data = await response.json();

      const name_employee = document.getElementById('name_employee');
      const date_hired = document.getElementById('date_hired');
      const status_employee = document.getElementById('status_employee');
      const type_employee = document.getElementById('type_employee');
      const id_department_fk = document.getElementById('id_department_fk');
      const id_position_fk = document.getElementById('id_position_fk');

      id_position_fk.innerHTML = '<option value="">Seleccione un puesto</option>';
      id_position_fk.dataset.preselected = '';
      id_position_fk.disabled = true;

      edit_only.forEach(element => { element.style.display = 'block'; });
      submitButton.setAttribute('data-bs-original-title', 'Guardar Cambios');
      submitButton.setAttribute('class', 'btn btn-primary');
      submitButton.innerHTML = '<i class="fa-solid fa-save tl-icon-xl"></i>';
      modalTitle.textContent = 'EDITAR EMPLEADO';
      formElement.action = `/employee/update/${id_employee}`;

      name_employee.value = data.name_employee;
      date_hired.value = data.date_hired;
      status_employee.value = data.status_employee;
      type_employee.value = data.type_employee;
      id_department_fk.value = data.id_department_fk;
      id_position_fk.dataset.preselected = data.id_position_fk;
      id_position_fk.disabled = false;
    
      hiddenIdInput.value = data.id_employee;
      modalElement.show();

      id_department_fk.dispatchEvent(new Event('change'));
    });
  });