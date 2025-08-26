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
        const modalTitle = document.getElementById('incidentsModalTitle');
        const submitButton = document.getElementById('incidentsSubmitButton');
        const formElement = document.getElementById('incidentsForm');

        if (!modalTitle || !submitButton || !formElement ) {
          return console.error('One or more required elements in the incident modal not found.');
        }

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



 function debounce(fn, delay) {
    let t;
    return (...args) => {
      clearTimeout(t);
      t = setTimeout(() => fn(...args), delay);
    };
  }

  function escapeHTML(str = '') {
    return String(str).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
  }

  function setupLiveSearch({ inputId, buttonId, cardId, resultId, endpoint, minLength = 2, mapItems, onPick }) {
    document.addEventListener('DOMContentLoaded', () => {
      const input = document.getElementById(inputId);
      const btn = document.getElementById(buttonId);
      const card = document.getElementById(cardId);
      const result = document.getElementById(resultId);
      if (!input || !btn || !card || !result) return;

      const url = input.dataset.endpoint || endpoint;
      let lastQuery = '';
      let controller = null;

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
          const primary = escapeHTML(it.primary);
          const secondary = escapeHTML(it.secondary || '');
          const dataset = Object.entries(it.dataset || {})
            .map(([k, v]) => `data-${k}="${escapeHTML(String(v))}"`).join(' ');
          return `
            <button type="button"
              role="option"
              class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
              ${dataset}
              aria-selected="${idx === 0 ? 'true' : 'false'}">
              <span>${primary}</span>
              ${secondary ? `<small class="text-muted">${secondary}</small>` : ''}
            </button>
          `;
        }).join('');
        result.innerHTML = `<div class="list-group list-group-flush">${html}</div>`;
      }

      async function remoteSearch(q) {
        try {
          if (controller) controller.abort();
          controller = new AbortController();
          const res = await fetch(`${url}?q=${encodeURIComponent(q)}`, {
            signal: controller.signal,
            headers: { 'Accept': 'application/json' }
          });
          if (!res.ok) throw new Error(`HTTP ${res.status}`);
          const data = await res.json();
          const raw = Array.isArray(data) ? data : (data.items || []);
          const mapped = raw.map(mapItems);
          renderResults(mapped);
        } catch (err) {
          renderResults([]);
        }
      }

      const onType = debounce(() => {
        const q = input.value.trim();
        if (q.length < minLength) {
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

      result.addEventListener('click', (e) => {
        const btn = e.target.closest('button[role="option"]');
        if (!btn) return;
        onPick(btn, { input, showCard });
      });

      input.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
          showCard(false);
        } else if (e.key === 'Enter') {
          e.preventDefault();
          const first = result.querySelector('button[role="option"]');
          if (first) onPick(first, { input, showCard });
        }
      });

      document.addEventListener('click', (e) => {
        if (!card.contains(e.target) && e.target !== input) showCard(false);
      });
    });
  }

  setupLiveSearch({
    inputId: 'searchEmployeeInput',
    buttonId: 'searchEmployeeButton',
    cardId: 'card-employee-searching',
    resultId: 'result_employee_search',
    endpoint: '/api/employees/search',
    mapItems: (e) => {
      const id = e.id_employee ?? e.id ?? '';
      const code = e.code_employee ?? e.code ?? '';
      const name = e.name_employee ?? e.name ?? '';
      return {
        primary: name,
        secondary: code ? `No. ${code}` : '',
        dataset: {
          id: id,
          code: code,
          name: name
        }
      };
    },
    onPick: (el, ctx) => {
      const id = el.dataset.id || '';
      const code = el.dataset.code || '';
      const name = el.dataset.name || '';
      const idInput = document.getElementById('id_employee_modal');
      const codeInput = document.getElementById('code_employee_modal');
      const nameInput = document.getElementById('name_employee_modal');
      if (idInput) idInput.value = id;
      if (codeInput) codeInput.value = code;
      if (nameInput) nameInput.value = name;
      if (ctx?.input) ctx.input.value = name || code;
      if (ctx?.showCard) ctx.showCard(false);
    }
  });

  setupLiveSearch({
    inputId: 'searchIncidentInput',
    buttonId: 'searchIncidentButton',
    cardId: 'card-incident-searching',
    resultId: 'result_incident_search',
    endpoint: '/api/incidents/search',
    mapItems: (e) => {
      const id = e.id_incident_type ?? e.id ?? '';
      const code = e.code_incident_type ?? e.code ?? e.id ?? '';
      const name = e.name_incident_type ?? e.name ?? '';
      const desc = e.description_incident_type ?? e.description ?? '';
      const actions = e.action_incident_type ?? e.actions ?? '';
      return {
        primary: name || code,
        secondary: name && code ? `No. ${code}` : '',
        dataset: {
          id: id,
          code: code,
          name: name,
          description: desc,
          actions: actions
        }
      };
    },
    onPick: (el, ctx) => {
      const id = el.dataset.id || '';
      const code = el.dataset.code || '';
      const name = el.dataset.name || '';
      const desc = el.dataset.description || '';
      const actions = el.dataset.actions || '';
      const idInput = document.getElementById('id_incident_modal');
      const codeInput = document.getElementById('code_incident_modal');
      const nameInput = document.getElementById('name_incident_type_modal');
      const descInput = document.getElementById('description_incident_type_modal');
      const actInput = document.getElementById('actions_incident_type_modal');
      if (idInput) idInput.value = id;
      if (codeInput) codeInput.value = code;
      if (nameInput) nameInput.value = name || code;
      if (descInput) descInput.value = desc;
      if (actInput) actInput.value = actions;
      if (ctx?.input) ctx.input.value = name || code;
      if (ctx?.showCard) ctx.showCard(false);
    }
  });