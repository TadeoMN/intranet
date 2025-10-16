<?php ob_start(); ?>

<!-- PAGE HEADER / FILTERS AND ACTIONS -->
<div class="container-fluid">
  <div class="row g-2 my-3 align-items-center">
    <!-- ADD INCIDENT BUTTON -->
    <div class="col-4 col-xxl-1 order-0 text-center">
      <div class="form-floating">
        <button type="button" class="btn btn-success w-100 tl-btn-new-employee" data-bs-toggle="tooltip" data-bs-title="Agregar Empleado" style="min-height: calc(3.8rem + calc(1px * 2)); max-height: calc(3.8rem + calc(1px * 2));">
          <i class="fa-solid fa-square-plus tl-icon-xl"></i>
        </button>
      </div>
    </div>
    <!-- SEARCH INPUT -->
    <div class="col-8 col-xl-6 col-xxl-4 order-2 order-xl-3 order-xxl-1">
      <form method="GET" action="/employees/list">
      <div class="form-floating">
        <input type="text" class="form-control" aria-label="Nombre o ID de empleado" name="search" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" aria-describedby="searchButton" placeholder="Buscar empleado:">
        <label for="search">Buscar empleado: </label>
      </div>
    </div>
    <!-- DATE INPUTS -->
    <div class="col-8 col-xl-4 col-xxl-3 order-4 order-xl-4 order-xxl-2 d-flex flex-row gap-2">
      <div class="form-floating w-100">
        <input type="date" name="dateFrom" id="dateFrom" class="form-control" value="<?= htmlspecialchars($_GET['dateFrom'] ?? '') ?>">
        <label for="dateFrom">Desde:</label>
      </div>
      <div class="form-floating w-100">
        <input type="date" name="dateTo" id="dateTo" class="form-control" value="<?= htmlspecialchars($_GET['dateTo'] ?? '') ?>">
        <label for="dateTo">Hasta:</label>
      </div>
    </div>
    <!-- STATUS SELECTOR -->
    <div class="col-4 col-xxl-1 order-3 order-xxl-3">
      <div class="form-floating">
        <select class="form-select text-nowrap" name="status" aria-label="Estado del empleado">
          <option value="" disabled selected>Estado del empleado</option>
          <option value="ACTIVO" <?= (isset($_GET['status']) && $_GET['status'] === 'ACTIVO') ? 'selected' : '' ?>>ACTIVO</option>
          <option value="INACTIVO" <?= (isset($_GET['status']) && $_GET['status'] === 'INACTIVO') ? 'selected' : '' ?>>INACTIVO</option>
          <option value="SUSPENDIDO" <?= (isset($_GET['status']) && $_GET['status'] === 'SUSPENDIDO') ? 'selected' : '' ?>>SUSPENDIDO</option>
          <option value="TODOS" <?= (isset($_GET['status']) && $_GET['status'] === 'TODOS') ? 'selected' : '' ?>>TODOS</option>
        </select>
        <label for="status">Estado del empleado: </label>
      </div>
    </div>
    <!-- FILTERS BUTTONS -->
    <div class="col-4 col-xl-2 col-xxl-1 order-5 order-xxl-4 d-flex flex-row gap-2 justify-content-center">
      <button type="submit" class="btn btn-sm btn-primary w-100" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Aplicar filtros" style="height: calc(3.8rem + calc(1px * 2));">
        <i class="fa-solid fa-magnifying-glass tl-icon-xl"></i>
      </button>
      <button type="button" class="btn btn-sm btn-secondary w-100" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Limpiar filtros" onclick="location.href = '/employees/list'" style="height: calc(3.8rem + calc(1px * 2));">
        <i class="fa-solid fa-broom-wide tl-icon-xl"></i>
      </button>
      </form>
    </div>
    <!-- RECORDS PER PAGE SELECTOR -->
    <div class="col-8 col-xxl-2 order-1 order-xl-2 order-xxl-5">
      <div class="form-floating">
        <select class="form-select text-nowrap" id="perPage" name="limit" onchange="location.href = '<?= buildUrl() ?>&limit=' + this.value" aria-label="Empleados por página">
          <?php
          $allowedPages = [5, 10, 20, 50, 100];
          $currentPerPage = $_GET['limit'] ?? 10;
          foreach ($allowedPages as $page) {
            $selected = ($page == $currentPerPage) ? 'selected' : '';
            echo "<option value=\"$page\" $selected>$page</option>";
          }
          ?>
        </select>
        <label for="perPage">Incidencias por página: </label>
      </div>

      <input type="hidden" name="page" value="<?= htmlspecialchars($_GET['page'] ?? 1) ?>">
      <input type="hidden" name="search" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
      <input type="hidden" name="dateFrom" value="<?= htmlspecialchars($_GET['dateFrom'] ?? '') ?>">
      <input type="hidden" name="dateTo" value="<?= htmlspecialchars($_GET['dateTo'] ?? '') ?>">
    </div>
  </div>
</div>
<!-- END PAGE HEADER / FILTERS AND ACTIONS -->

<div class="container-fluid table-responsive">
  <table id="" class="table table-hover table-bordered">
    <thead class="table-group-divider align-middle text-center table-dark" style="height: 60px;">
      <tr> <!-- Table headers / Encabezados de la tabla -->
        <th> <!-- No. de Empleado -->
          <a href="<?= sortLink('code_employee', $pagination['sort'], $pagination['order']) ?>"
            class="text-white d-flex flex-row align-items-center justify-content-center text-decoration-none" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Ordenar por No. de Empleado">
            <p class="my-0 mx-3">No. de Empleado</p>
            <i class="fa-solid <?= sortIcon('code_employee', $pagination['sort'], $pagination['order']) ?>"></i>
          </a>
        </th>
        <th> <!-- No. de Nómina -->
          <a href="<?= sortLink('number_payroll_contract', $pagination['sort'], $pagination['order']) ?>"
            class="text-white d-flex flex-row align-items-center justify-content-center text-decoration-none" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Ordenar por No. de Nómina">
            <p class="my-0 mx-3">No. de Nómina</p>
            <i class="fa-solid <?= sortIcon('number_payroll_contract', $pagination['sort'], $pagination['order']) ?>"></i>
          </a>
        </th>
        <th> <!-- Nombre del Empleado -->
          <a href="<?= sortLink('name_employee', $pagination['sort'], $pagination['order']) ?>"
            class="text-white d-flex flex-row align-items-center justify-content-center text-decoration-none" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Ordenar por Nombre del Empleado">
            <p class="my-0 mx-3">Nombre del Empleado</p>
            <i class="fa-solid <?= sortIcon('name_employee', $pagination['sort'], $pagination['order']) ?>"></i>
          </a>
        </th>
        <th> <!-- Fecha de Ingreso -->
          <a href="<?= sortLink('date_hired', $pagination['sort'], $pagination['order']) ?>"
            class="text-white d-flex flex-row align-items-center justify-content-center text-decoration-none" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Ordenar por Fecha de Ingreso">
            <p class="my-0 mx-3">Fecha de Ingreso</p>
            <i class="fa-solid <?= sortIcon('date_hired', $pagination['sort'], $pagination['order']) ?>"></i>
          </a>
        </th>
        <th> <!-- Estado del Empleado -->
          <a href="<?= sortLink('status_employee', $pagination['sort'], $pagination['order']) ?>"
            class="text-white d-flex flex-row align-items-center justify-content-center text-decoration-none" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Ordenar por Estado del Empleado">
            <p class="my-0 mx-3">Estado</p>
            <i class="fa-solid <?= sortIcon('status_employee', $pagination['sort'], $pagination['order']) ?>"></i>
          </a>
        </th>
        <th> <!-- Tipo de Empleado -->
          <a href="<?= sortLink('type_employee', $pagination['sort'], $pagination['order']) ?>"
            class="text-white d-flex flex-row align-items-center justify-content-center text-decoration-none" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Ordenar por Tipo de Empleado">
            <p class="my-0 mx-3">Tipo</p>
            <i class="fa-solid <?= sortIcon('type_employee', $pagination['sort'], $pagination['order']) ?>"></i>
          </a>
        </th>
        <th> <!-- Antigüedad -->
          <a href="<?= sortLink('seniority_employee', $pagination['sort'], $pagination['order']) ?>"
            class="text-white d-flex flex-row align-items-center justify-content-center text-decoration-none" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Ordenar por Antigüedad">
            <p class="my-0 mx-3">Antigüedad</p>
            <i class="fa-solid <?= sortIcon('seniority_employee', $pagination['sort'], $pagination['order']) ?>"></i>
          </a>
        </th>
        <th> <!-- Posición -->
          <a href="<?= sortLink('name_position', $pagination['sort'], $pagination['order']) ?>"
            class="text-white d-flex flex-row align-items-center justify-content-center text-decoration-none" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Ordenar por Posición">
            <p class="my-0 mx-3">Posición</p>
            <i class="fa-solid <?= sortIcon('name_position', $pagination['sort'], $pagination['order']) ?>"></i>
          </a>
        </th>
        <th> <!-- Área -->
          <a href="<?= sortLink('name_department', $pagination['sort'], $pagination['order']) ?>"
            class="text-white d-flex flex-row align-items-center justify-content-center text-decoration-none" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Ordenar por Área">
            <p class="my-0 mx-3">Área</p>
            <i class="fa-solid <?= sortIcon('name_department', $pagination['sort'], $pagination['order']) ?>"></i>
          </a>
        </th>
        <th> <!-- Jefe Directo -->
          <a href="<?= sortLink('name_manager', $pagination['sort'], $pagination['order']) ?>"
            class="text-white d-flex flex-row align-items-center justify-content-center text-decoration-none" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Ordenar por Jefe Directo">
            <p class="my-0 mx-3">Jefe Directo</p>
            <i class="fa-solid <?= sortIcon('name_manager', $pagination['sort'], $pagination['order']) ?>"></i>
          </a>
        </th>
        <th>Acciones</th> <!-- Actions -->
      </tr>
    </thead>
    <tbody class="align-middle text-center">
      <?php foreach ($employees as $employee): ?>
        <tr> <!-- Table row for each employee / Fila de la tabla para cada empleado -->
          <td> <!-- No. de Empleado -->
            <?= $employee['code_employee'] ?>
          </td>
          <?php if (empty($employee['number_payroll_contract'])): ?>
            <td class="text-muted">
              SIN CONTRATO
            </td>
          <?php else: ?>
            <td> <!-- No. de Nómina -->
              <?= $employee['number_payroll_contract'] ?>
            </td>
          <?php endif; ?>
          <td> <!-- Nombre del Empleado -->
            <?= $employee['name_employee'] ?>
          </td>
          <td> <!-- Fecha de Ingreso -->
            <?= date_lenguage_spanish($employee['date_hired']) ?>
          </td>
          <td> <!-- Estado del Empleado -->
            <?= $employee['status_employee'] ?>
          </td>
          <td> <!-- Tipo de Empleado -->
            <?= $employee['type_employee'] ?>
          </td>
          <td> <!-- Antigüedad -->
            <?= $employee['seniority_employee'] ?> años
          </td>

          <td> <!-- Posición -->
            <?= $employee['name_position'] ?>
          </td>
          <td> <!-- Área -->
            <?= $employee['name_department'] ?>
          </td>
          <?php if ($employee['name_manager'] === $employee['name_employee'] && $employee['name_manager'] !== 'TAMEZ ALCARAZ EDGAR LEONARDO'): ?>
            <td class="text-muted">
              TAMEZ ALCARAZ EDGAR LEONARDO
            </td>
          <?php elseif ($employee['name_manager'] === 'TAMEZ ALCARAZ EDGAR LEONARDO'): ?>
            <td class="text-muted">
              N/A
            </td>
          <?php else: ?>
            <td> <!-- Jefe Directo -->
              <?= $employee['name_manager'] ?>
            </td>
          <?php endif; ?>
          <td class="text-center"> <!-- Editar Empleado -->
            <div class="tl-grid-template-buttons-actions">
              <button class="btn btn-sm btn-primary tl-btn-edit-employee" data-id="<?= $employee['id_employee'] ?>"
                data-bs-toggle="tooltip" data-bs-title="Editar">
                <i class="fa-solid fa-pen-to-square tl-icon-xl"></i>
              </button>

              <?php if ($employee['status_employee'] !== 'INACTIVO'): ?>
                <form method="POST" action="/employee/delete/<?= $employee['id_employee'] ?>" class="d-inline form-delete-employee">
                  <button type="submit" class="btn btn-sm btn-danger" data-name="<?= htmlspecialchars($employee['name_employee']) ?>"
                    data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Eliminar">
                    <i class="fa-solid fa-trash tl-icon-xl"></i>
                  </button>
                </form>
              <?php else: ?>
                <div class="inline-block" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Empleado inactivo, no se puede eliminar">
                  <button class="btn btn-sm btn-danger disabled">
                    <i class="fa-solid fa-trash tl-icon-xl"></i>
                  </button>
                </div>
              <?php endif; ?>

              <a href="/employees/profile/<?= $employee['id_employee'] ?>"
                class="btn btn-sm btn-success" data-bs-toggle="tooltip" data-bs-title="Ver Perfil/Contrato">
                <i class="fa-solid fa-user tl-icon-xl"></i>
              </a>

              <a class="btn btn-sm btn-warning tl-btn-incident" data-bs-toggle="tooltip" data-bs-title="Levantar Incidencia" data-id="<?= $employee['id_employee'] ?>">
                <i class="fa-solid fa-bell tl-icon-xl"></i>
              </a>

              <a href="#"
                class="btn btn-sm btn-warning" data-bs-toggle="tooltip" data-bs-title="Calificar Empleado">
                <i class="fa-solid fa-star tl-icon-xl"></i>
              </a>

              <a href="#"
                class="btn btn-sm btn-warning" data-bs-toggle="tooltip" data-bs-title="Ver Expediente">
                <i class="fa-solid fa-eye tl-icon-xl"></i>
              </a>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($employees)): ?>
        <tr class="text-center"> <!-- No hay empleados -->
          <td colspan="11">
            <i class="fa-solid fa-triangle-exclamation text-warning tl-icon-xl mx-3"></i>
            <strong>No se encontraron empleados.</strong>
          </td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<div class="container-fluid">
  <!-- Show number of records / Mostrar número de registros -->
  <?php if (!isset($pagination) || $pagination['total_pages'] <= 1): ?>
    <div class="row my-4">
      <div class="col-12">
        <p class="text-muted text-center m-0">
          Mostrando <?= count($employees) ?> de <?= count($employees) ?> empleados
        </p>
      </div>
    </div>
  <?php endif; ?>
  <!-- Pagination controls / Controles de paginación -->
  <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
    <div class="my-4 d-flex justify-content-between align-items-center row">
      <!-- Pagination info / Información de paginación -->
      <div class="col-12 col-md-6">
        <p class="text-muted text-center text-md-start m-0 mb-3 mb-md-0">
          Mostrando <?= count($employees) ?> de <?= $pagination['total_items'] ?> empleados
          (Página <?= $pagination['current_page'] ?> de <?= $pagination['total_pages'] ?>)
        </p>
      </div>
      <!-- Pagination links / Enlaces de paginación -->
      <div class="col-12 col-md-6">
        <nav aria-label="Employee pagination">
          <ul class="pagination tl-pagination-md justify-content-center justify-content-md-end m-0">
            <!-- First page / Página inicial -->
            <?php if ($pagination['current_page'] > 1): ?>
              <li class="page-item">
                <a class="page-link" href="<?= buildUrl(['page' => 1]) ?>" aria-label="Primera" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Primera">
                  <span aria-hidden="false">&laquo;&laquo;</span>
                </a>
              </li>
            <?php else: ?>
              <li class="page-item disabled">
                <span class="page-link" aria-label="Primera">
                  <span aria-hidden="false">&laquo;&laquo;</span>
                </span>
              </li>
            <?php endif; ?>

            <!-- Previous page / Página anterior -->
            <?php if ($pagination['has_prev']): ?>
              <li class="page-item">
                <a class="page-link" href="<?= buildUrl(['page' => $pagination['prev_page']]) ?>" aria-label="Anterior" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Anterior">
                  <span aria-hidden="false">&laquo;</span>
                </a>
              </li>
            <?php else: ?>
              <li class="page-item disabled">
                <span class="page-link" aria-label="Anterior">
                  <span aria-hidden="false">&laquo;</span>
                </span>
              </li>
            <?php endif; ?>

            <!-- Page numbers / Números de página -->
            <?php
            $start_page = max(1, $pagination['current_page'] - 2);
            $end_page = min($pagination['total_pages'], $pagination['current_page'] + 2);
            for ($i = $start_page; $i <= $end_page; $i++): ?>
              <li class="page-item <?= $i == $pagination['current_page'] ? 'active' : '' ?>">
                <a class="page-link" href="<?= buildUrl(['page' => $i]) ?>" aria-label="Página <?= $i ?>" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Página <?= $i ?>">
                  <?= $i ?>
                </a>
              </li>
            <?php endfor; ?>

            <!-- Next page / Página siguiente -->
            <?php if ($pagination['has_next']): ?>
              <li class="page-item">
                <a class="page-link" href="<?= buildUrl(['page' => $pagination['next_page']]) ?>" aria-label="Siguiente" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Siguiente">
                  <span aria-hidden="false">&raquo;</span>
                </a>
              </li>
            <?php else: ?>
              <li class="page-item disabled">
                <span class="page-link" aria-label="Siguiente">
                  <span aria-hidden="false">&raquo;</span>
                </span>
              </li>
            <?php endif; ?>

            <!-- Last page / Última página -->
            <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
              <li class="page-item">
                <a class="page-link" href="<?= buildUrl(['page' => $pagination['total_pages']]) ?>" aria-label="Última" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Última">
                  <span aria-hidden="false">&raquo;&raquo;</span>
                </a>
              </li>
            <?php else: ?>
              <li class="page-item disabled">
                <span class="page-link" aria-label="Última">
                  <span aria-hidden="false">&raquo;&raquo;</span>
                </span>
              </li>
            <?php endif; ?>
          </ul>
        </nav>
      </div>
    </div>
  <?php endif; ?>
</div>

<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/layout-main.php'; ?>