  // Initialize tooltips when DOM is ready / Inicializar tooltips cuando el DOM esté listo
  document.addEventListener('DOMContentLoaded', function() {
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
  });

  document.querySelectorAll('.dropdown-menu [data-bs-toggle="dropdown"]').forEach(dropdown => {
    dropdown.addEventListener('click', function(event) {
      event.preventDefault();
      event.stopPropagation();
    });
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

    if (!modalElement || !formElement || !modalTitle || !hiddenIdInput || !btnNewEmployee || !btnEditEmployee.length || !edit_only.length || !submitButton) return console.error('One or more required elements at the modal not found.');

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
    const btnNewIncident = document.querySelectorAll('.tl-btn-incident');
    const modalIncident = new bootstrap.Modal(document.getElementById('incidentsModal'));

    if (!btnNewIncident || !modalIncident) return console.error('New incident button or modal not found.');

    btnNewIncident.forEach(button => {
      button.addEventListener('click', function() {
        const modalTitle = document.getElementById('modalTitle');
        const submitButton = document.getElementById('submitButton');
        const formElement = document.getElementById('incidentsForm');

        if (!modalTitle || !submitButton || !formElement ) {
          return console.error('One or more required elements in the incident modal not found.');
        }
        modalTitle.textContent = 'NUEVA INCIDENCIA';
        submitButton.setAttribute('data-bs-original-title', 'Registrar Incidencia');
        submitButton.setAttribute('class', 'btn btn-success');
        submitButton.innerHTML = '<i class="fa-solid fa-plus tl-icon-xl"></i>';
        formElement.action = '/incident/store';
        modalIncident.show();
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

  document.addEventListener('DOMContentLoaded', function() {
    const backToTopButton = document.getElementById('backToTopButton');
    const scrollableContainer = document.getElementById('scrollableContainer');

    if (!backToTopButton) return console.error('Back to top button not found.');
    if (!scrollableContainer) return console.error('Scrollable container not found.');

    backToTopButton.addEventListener('click', function() {
      scrollableContainer.scrollTo({
        top: 0,
        behavior: 'smooth'
      });
    });
  });

  // Live employee search for incidents modal (AJAX with debounce + local fallback)
  // Búsqueda en vivo de empleados para el modal de incidencias (AJAX con debounce + fallback local)
  document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('searchEmployeeInput');
    const btn = document.getElementById('searchEmployeeButton');
    const card = document.getElementById('card-searching');
    const result = document.getElementById('result_search');

    const selectEmployee = document.getElementById('id_employee_fk');

    if (!input || !btn || !card || !result || !selectEmployee || !selectDept || !selectPos) {
      return; // Not present in every view
    }

    const endpoint = input.dataset.endpoint || '/api/employee/search';
    const MIN_LENGTH = 2;
    let lastQuery = '';
    let controller = null;

    function debounce(fn, delay) {
      let t;
      return (...args) => {
        clearTimeout(t);
        t = setTimeout(() => fn(...args), delay);
      };
    }

    function escapeHTML(str) {
      return str.replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
    }

    function showCard(show) {
      card.style.opacity = show ? '1' : '0';
      input.setAttribute('aria-expanded', show ? 'true' : 'false');
      if (!show) result.innerHTML = '';
    }

    function renderResults(items) {
      if (!items || !items.length) {
        result.innerHTML = '<div class="text-muted p-2">No se encontraron resultados</div>';
        return;
      }
      const html = items.map((it, idx) => {
        const name = escapeHTML(it.name || it.name_employee || '');
        const id = it.id || it.id_employee;
        return `
          <button type="button"
            role="option"
            class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
            data-id="${id}"
            data-name="${name}"
            aria-selected="${idx === 0 ? 'true' : 'false'}">
            <span>${name}</span>
          </button>
        `;
      }).join('');
      result.innerHTML = `<div class="list-group list-group-flush">${html}</div>`;
    }

    function pickItem(el) {
      if (!el || !el.dataset) return;
      const id = el.dataset.id;
      const name = el.dataset.name || '';

      // Seleccionar empleado en el select principal
      const opt = selectEmployee.querySelector(`option[value="${id}"]`);
      if (!opt) {
        // si no existe la opción, créala efímeramente para permitir selección
        const tmp = document.createElement('option');
        tmp.value = id;
        tmp.textContent = name;
        selectEmployee.appendChild(tmp);
      }
      selectEmployee.value = id;

      input.value = name;
      showCard(false);
    }

    async function remoteSearch(q) {
      try {
        if (controller) controller.abort();
        controller = new AbortController();
        const res = await fetch(`${endpoint}?q=${encodeURIComponent(q)}`, {
          signal: controller.signal,
          headers: { 'Accept': 'application/json' }
        });
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const data = await res.json();
        // Normalizar campos esperados
        const items = (Array.isArray(data) ? data : (data.items || []))
          .map(e => ({
            id: e.id ?? e.id_employee,
            name: e.name ?? e.name_employee,
          }));
        renderResults(items);
      } catch (err) {
        // Fallback: filtrado local en el select de empleados
        const qLower = q.toLowerCase();
        const options = Array.from(selectEmployee.options)
          .filter(o => o.value && o.text.toLowerCase().includes(qLower))
          .slice(0, 10)
          .map(o => ({ id: o.value, name: o.text }));
        renderResults(options);
      }
    }

    const onType = debounce(() => {
      const q = input.value.trim();
      if (q.length < MIN_LENGTH) {
        showCard(false);
        lastQuery = '';
        return;
      }
      if (q === lastQuery) return;
      lastQuery = q;
      showCard(true);
      remoteSearch(q);
    }, 250);

    input.addEventListener('input', onType);
    btn.addEventListener('click', () => onType());

    // Delegación: click sobre un resultado
    result.addEventListener('click', (e) => {
      const btn = e.target.closest('button[role="option"]');
      if (btn) pickItem(btn);
    });

    // Teclas: Enter selecciona el primero; Escape cierra
    input.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') {
        showCard(false);
      } else if (e.key === 'Enter') {
        e.preventDefault();
        const first = result.querySelector('button[role="option"]');
        if (first) pickItem(first);
      }
    });

    // Cerrar al hacer click fuera
    document.addEventListener('click', (e) => {
      if (!card.contains(e.target) && e.target !== input) {
        showCard(false);
      }
    });
  });
