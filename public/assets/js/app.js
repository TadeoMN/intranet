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

document.addEventListener('DOMContentLoaded', function () {
  const modalIncident = document.getElementById('incidentsModal');
  if (!modalIncident) return flash('error', 'Error', 'No cargó la ventana emergente de incidencias. Refresque la página e inténtelo de nuevo.');

  const modalBootstrap = bootstrap.Modal.getOrCreateInstance(modalIncident);
  const incidentSearchGroup = document.getElementById('incidentSearchGroup');
  const employeeSearchGroup = document.getElementById('employeeSearchGroup');

  const inputElements = {
    id_incident:               document.getElementById('id_incident_modal'),
    code_incident:             document.getElementById('code_incident_modal'),
    name_incident_type:        document.getElementById('name_incident_type_modal'),
    description_incident_type: document.getElementById('description_incident_type_modal'),
    actions_incident_type:     document.getElementById('actions_incident_type_modal'), // tu input (texto/textarea)
    id_employee:               document.getElementById('id_employee_modal'),
    code_employee:             document.getElementById('code_employee_modal'),
    name_employee:             document.getElementById('name_employee_modal'),
    ot_incident:               document.getElementById('ot_incident_modal'),
    waste_incident:            document.getElementById('waste_incident_modal'),
    identification_incident:   document.getElementById('identification_incident_modal'), // SELECT
    observation_incident:      document.getElementById('observation_incident_modal'),
    searchIncidentInput:       document.getElementById('searchIncidentInput'),
    searchEmployeeInput:       document.getElementById('searchEmployeeInput')
  };

  const titleModal = document.getElementById('incidentsTitleModal');
  const saveIncidentButtonModal  = document.getElementById('saveIncidentButtonModal');
  const clearIncidentButtonModal = document.getElementById('clearIncidentButtonModal');
  if (!titleModal || !saveIncidentButtonModal || !clearIncidentButtonModal)
    return flash('error', 'Error', 'No cargó la ventana emergente de incidencias de forma correcta. Refresque la página e inténtelo de nuevo.');

  // --- estado actual del modal ---
  let currentMode = 'create'; // 'create' | 'view'

  // Helpers
  const isInput = el => el && ('value' in el);
  const setDisabled = (el, v) => { if (el) el.disabled = v; };

  function ensureIdentificationOptions(mode, value = null) {
    const sel = inputElements.identification_incident;
    if (!sel) return;

    // Limpia opciones
    while (sel.firstChild) sel.removeChild(sel.firstChild);

    if (mode === 'view') {
      // En vista: muestra sólo el valor actual como opción fija
      const opt = document.createElement('option');
      opt.value = value ?? '';
      opt.textContent = value ?? '';
      sel.appendChild(opt);
      sel.value = value ?? '';
      sel.disabled = true;
      return;
    }

    // En creación/edición: repone catálogo
    const opts = [
      { v: '',          t: 'Seleccione…' },
      { v: 'INTERNA',   t: 'INTERNA' },
      { v: 'EXTERNA',   t: 'EXTERNA' }
    ];
    opts.forEach(o => {
      const opt = document.createElement('option');
      opt.value = o.v; opt.textContent = o.t;
      sel.appendChild(opt);
    });
    sel.value = value ?? '';
    sel.disabled = false;
  }

  function setMode(mode) {
    currentMode = (mode === 'view') ? 'view' : 'create';
    const readOnly = currentMode === 'view';

    // deshabilita todos menos los buscadores
    Object.entries(inputElements).forEach(([key, el]) => {
      if (!isInput(el)) return;
      if (key === 'searchIncidentInput' || key === 'searchEmployeeInput') return;
      setDisabled(el, readOnly);
    });

    // identificación: en view deja sólo el valor; en create repone catálogo
    if (currentMode === 'view') {
      // si aún no hay valor, no toques; fillInputsForm lo fijará
      setDisabled(inputElements.identification_incident, true);
    } else {
      ensureIdentificationOptions('create'); // repone opciones
    }

    titleModal.textContent = readOnly ? 'DETALLE INCIDENCIA' : 'AGREGAR INCIDENCIA';
    saveIncidentButtonModal.classList.toggle('d-none', readOnly);
    clearIncidentButtonModal.classList.toggle('d-none', readOnly);
  }

  function resetIncidentModal() {
    // Mantén identification_incident; limpia el resto
    const keepIdValue = inputElements.identification_incident?.value ?? null;

    Object.entries(inputElements).forEach(([key, el]) => {
      if (!isInput(el)) return;
      if (key === 'identification_incident') return; // NO limpiar
      el.value = '';
    });

    // Reactiva buscadores
    setDisabled(inputElements.searchIncidentInput, false);
    setDisabled(inputElements.searchEmployeeInput, false);

    // Si estamos en create, repón opciones del select (y deja valor en vacío)
    if (currentMode === 'create') {
      ensureIdentificationOptions('create', '');
    } else {
      // En view conserva el valor actual
      ensureIdentificationOptions('view', keepIdValue);
    }
  }

  // Mapea claves del JSON -> inputs
  const KEY_MAP = {
    id_incident:               'id_incident',
    code_incident:             'code_incident_type',
    name_incident_type:        'name_incident_type',
    description_incident_type: 'description_incident_type',
    actions_incident_type:     'action_incident_type',
    id_employee:               'id_employee',
    code_employee:             'code_employee',
    name_employee:             'name_employee',
    ot_incident:               'ot_incident',
    waste_incident:            'waste_incident',
    identification_incident:   'identification_incident',
    observation_incident:      'observation_incident'
  };

  function pick(obj, keyOrKeys) {
    if (Array.isArray(keyOrKeys)) {
      for (const k of keyOrKeys) {
        const v = obj?.[k];
        if (v !== undefined && v !== null) return v;
      }
      return '';
    }
    return obj?.[keyOrKeys] ?? '';
  }

  function fillInputsForm(payload) {
    // Tu API devuelve { ok:true, item:{...} }
    const d = (payload && typeof payload === 'object' && 'item' in payload) ? payload.item : payload;
    console.log('incident item ->', d);

    Object.entries(KEY_MAP).forEach(([keyInput, keyJson]) => {
      const el = inputElements[keyInput];
      if (!isInput(el)) return;
      const val = pick(d, keyJson) ?? '';
      if (keyInput === 'identification_incident') {
        // En view coloca sólo ese valor; en create repone catálogo y selecciona
        if (currentMode === 'view') ensureIdentificationOptions('view', String(val));
        else ensureIdentificationOptions('create', String(val));
      } else {
        el.value = String(val);
      }
    });

    // Ajustes de solo lectura en view:
    if (currentMode === 'view') {
      ['id_employee','code_employee','name_employee','code_incident','name_incident_type','description_incident_type','actions_incident_type','ot_incident','waste_incident','observation_incident']
        .forEach(k => setDisabled(inputElements[k], true));
    }
  }

  // --- Abrir CREATE ---
  document.addEventListener('click', async function (event) {
    const btnIncident = event.target.closest('.tl-btn-incident');
    if (!btnIncident) return;

    setMode('create');
    resetIncidentModal();

    incidentSearchGroup.classList.remove('d-none'); // muestra buscador en create
    employeeSearchGroup.classList.remove('d-none'); // muestra buscador en create

    const id_employee = btnIncident.getAttribute('data-id');
    if (id_employee) {
      try {
        const resp = await fetch(`/api/employee/${id_employee}`, { headers: { 'Accept':'application/json' } });
        if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
        const data = await resp.json();
        if (data) {
          if (inputElements.id_employee)    { inputElements.id_employee.value    = data.id_employee ?? '';    setDisabled(inputElements.id_employee, false); }
          if (inputElements.code_employee)  { inputElements.code_employee.value  = data.code_employee ?? '';  setDisabled(inputElements.code_employee, true); }
          if (inputElements.name_employee)  { inputElements.name_employee.value  = data.name_employee ?? '';  setDisabled(inputElements.name_employee, true); }
          if (inputElements.searchEmployeeInput) { inputElements.searchEmployeeInput.value = (data.name_employee ?? data.code_employee ?? ''); setDisabled(inputElements.searchEmployeeInput, true); }
          if (inputElements.searchIncidentInput) inputElements.searchIncidentInput.focus();

          // Campos dependientes del catálogo de incidentes (si se llenan por búsqueda)
          setDisabled(inputElements.code_incident,             true);
          setDisabled(inputElements.name_incident_type,        true);
          setDisabled(inputElements.description_incident_type, true);
          setDisabled(inputElements.actions_incident_type,     true);
        }
      } catch (error) {
        return flash('error', 'Error', 'No se pudo obtener la información del empleado.');
      }
    } else {
      // Sin empleado preseleccionado
      setDisabled(inputElements.searchIncidentInput,  false);
      setDisabled(inputElements.searchEmployeeInput,  false);
      if (inputElements.id_employee)    { inputElements.id_employee.value = '';    setDisabled(inputElements.id_employee, false); }
      if (inputElements.id_incident)    { inputElements.id_incident.value = '';    setDisabled(inputElements.id_incident, false); }
      if (inputElements.code_incident)  { inputElements.code_incident.value = '';  setDisabled(inputElements.code_incident, true); }
      if (inputElements.name_incident_type)        { inputElements.name_incident_type.value = '';        setDisabled(inputElements.name_incident_type, true); }
      if (inputElements.description_incident_type)  { inputElements.description_incident_type.value = '';  setDisabled(inputElements.description_incident_type, true); }
      if (inputElements.actions_incident_type)      { inputElements.actions_incident_type.value = '';      setDisabled(inputElements.actions_incident_type, true); }
      if (inputElements.code_employee)  { inputElements.code_employee.value = '';  setDisabled(inputElements.code_employee, true); }
      if (inputElements.name_employee)  { inputElements.name_employee.value = '';  setDisabled(inputElements.name_employee, true); }
    }

    // En create: repón opciones del select identificación
    ensureIdentificationOptions('create');
    modalBootstrap.show();
  });

  // --- Abrir VIEW ---
  document.addEventListener('click', async function (event) {
    const btnViewIncident = event.target.closest('.tl-btn-view-incident');
    if (!btnViewIncident) return;

    const id_incident = btnViewIncident.getAttribute('data-id');
    if (!id_incident) return flash('error', 'Error', 'No se proporcionó el ID de la incidencia.');

    setMode('view');
    resetIncidentModal(); // limpia (y mantiene identificación)
    incidentSearchGroup.classList.add('d-none'); // oculta buscador en view
    employeeSearchGroup.classList.add('d-none'); // oculta buscador en view

    try {
      const response = await fetch(`/api/incident/${id_incident}`, { headers: { 'Accept': 'application/json' } });
      if (!response.ok) throw new Error('HTTP '+response.status);
      const data = await response.json();
      fillInputsForm(data);
      modalBootstrap.show();
    } catch (error) {
      return flash('error', 'Error', 'Sistema de incidencias fuera de servicio. Notifique al administrador.');
    }
  });

  clearIncidentButtonModal.addEventListener('click', resetIncidentModal);
  modalIncident.addEventListener('hidden.bs.modal', resetIncidentModal);
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