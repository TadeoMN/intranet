  // Initialize tooltips when DOM is ready / Inicializar tooltips cuando el DOM esté listo
  document.addEventListener('DOMContentLoaded', function() {
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
  });

  document.addEventListener('DOMContentLoaded', function() {
    const modalElement = new bootstrap.Modal(document.getElementById('createEmployeeModal'));
    const formElement = document.getElementById('createEmployeeForm');
    const modalTitle = document.getElementById('modalTitle');
    const hiddenIdInput = document.getElementById('id_employee');
    const btnNewEmployee = document.querySelector('.tl-btn-new-employee');
    const btnEditEmployee = document.querySelectorAll('.tl-btn-edit-employee');
    const edit_only = document.querySelectorAll('.tl-edit-only');
    const submitButton = document.getElementById('submitButton');

    if (!modalElement || !formElement || !modalTitle || !hiddenIdInput || !btnNewEmployee || !btnEditEmployee.length || !edit_only.length || !submitButton) return console.error('One or more required elements not found.');

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

        if (!name_employee || !date_hired || !status_employee || !type_employee || !id_department_fk || !id_position_fk) return console.error('One or more form elements not found.');

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
  });

  document.addEventListener('DOMContentLoaded', function() {
    const viewAccountButton = document.getElementById('viewAccountButton');
    const account_number_employee = document.getElementById('account_number_employee');
    if (!viewAccountButton || !account_number_employee) return console.error('View account button or account number input not found.');

    const fullAccountNumber = viewAccountButton.dataset.full;
    if (!fullAccountNumber) return console.error('Full account number not provided in data attribute.');
    const maskedAccountNumber = fullAccountNumber.replace(/.(?=.{4})/g,'•');

    viewAccountButton.addEventListener('click', () => {
      const showingFullNumber = viewAccountButton.dataset.shown === '1';
      if (showingFullNumber) {
        account_number_employee.value = maskedAccountNumber;
        viewAccountButton.innerHTML = '<i class="fa-solid fa-eye tl-icon-xl"></i> ';
        viewAccountButton.setAttribute('data-bs-original-title', 'Ver Número Completo');
        viewAccountButton.dataset.shown = '0';
      } else {
        account_number_employee.value = fullAccountNumber;
        viewAccountButton.innerHTML = '<i class="fa-solid fa-eye-slash tl-icon-xl"></i> ';
        viewAccountButton.setAttribute('data-bs-original-title', 'Ocultar Número Completo');
        viewAccountButton.dataset.shown = '1';
      }
    });
  });

  document.getElementById('backToTopButton').addEventListener('click', function() {
    document.getElementById('scrollableContainer').scrollTo({
      top: 0,
      behavior: 'smooth'
    });
  });