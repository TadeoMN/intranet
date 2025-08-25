<?php ob_start(); ?>

  <div class="container my-4">
    <h1 class="text-center">Empleados</h1>
    <p class="text-center">Lista de empleados registrados en el sistema.</p>
  </div>

  <div class="container-fluid">
    <div class="row mx-0 mb-3 d-flex align-items-center p-0"> <!-- Search and Filter Controls -->

      <div class="py-1 col-sm-12 col-md-4 col-lg-2"> <!-- New Employee Button -->
        <a href="/employee/create" class="btn btn-success tl-btn-new-employee" data-bs-toggle="modal" data-bs-target="#createEmployeeModal">
          <i class="fa-solid fa-plus"></i> Agregar empleado
        </a>
      </div>

      <div class="py-1 col-sm-12 col-md-4 col-lg-8"> <!-- Search and Filter Form -->
        <form method="GET" action="/employees/list" class="row g-2 align-items-center justify-content-center">
          <div class="form-floating col-auto">
            <input type="text" class="form-control" aria-label="Nombre o ID de empleado" name="search"
            value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" aria-describedby="searchButton" placeholder="Buscar empleado: ">
            <label for="search">Buscar empleado: </label>
          </div>

          <div class="form-floating col-auto">
            <select class="form-select text-nowrap" name="status" aria-label="Estado del empleado">
              <option value="" disabled selected>Estado del empleado</option>
              <option value="ACTIVO" <?= (isset($_GET['status']) && $_GET['status'] === 'ACTIVO') ? 'selected' : '' ?>>ACTIVO</option>
              <option value="INACTIVO" <?= (isset($_GET['status']) && $_GET['status'] === 'INACTIVO') ? 'selected' : '' ?>>INACTIVO</option>
              <option value="SUSPENDIDO" <?= (isset($_GET['status']) && $_GET['status'] === 'SUSPENDIDO') ? 'selected' : '' ?>>SUSPENDIDO</option>
              <option value="TODOS" <?= (isset($_GET['status']) && $_GET['status'] === 'TODOS') ? 'selected' : '' ?>>TODOS</option>
            </select>
            <label for="status">Estado del empleado: </label>
          </div>

          <div class="form-floating col-auto">
            <input type="date" name="dateFrom" id="dateFrom" class="form-control" value="<?= htmlspecialchars($_GET['dateFrom'] ?? '') ?>">
            <label for="dateFrom">Desde:</label>
          </div>

          <div class="form-floating col-auto">
            <input type="date" name="dateTo" id="dateTo" class="form-control" value="<?= htmlspecialchars($_GET['dateTo'] ?? '') ?>">
            <label for="dateTo">Hasta:</label>
          </div>

          <div class="col-auto">
            <button type="submit" class="btn btn-sm btn-primary mx-1" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Aplicar filtros">
              <i class="fa-solid fa-magnifying-glass tl-icon-xl"></i>
            </button>

            <a href="/employees/list" class="btn btn-sm btn-secondary mx-1" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Limpiar filtros">
              <i class="fa-solid fa-rotate tl-icon-xl"></i>
            </a>
          </div>

          <input type="hidden" name="page" value="<?= htmlspecialchars($_GET['page'] ?? 1) ?>">
          <input type="hidden" name="limit" value="<?= htmlspecialchars($_GET['limit'] ?? 10) ?>">
        </form>
      </div>

      <div class="py-1 col-sm-12 col-md-4 col-lg-2"> <!-- Pagination and Per Page Selector -->
        <form method="GET" action="/employees/list">
          <div class="form-floating">
            <select class="form-select text-nowrap" id="perPage" name="limit" onchange="location.href = '<?= buildUrl()?>&limit=' + this.value" aria-label="Empleados por página" style="min-width: 190px;"> 
              <?php
                $allowedPages = [5,10,20,50,100];
                $currentPerPage = $_GET['limit'] ?? 10;
                foreach ($allowedPages as $page) {
                  $selected = ($page == $currentPerPage) ? 'selected' : '';
                  echo "<option value=\"$page\" $selected>$page</option>";
                }
              ?>
            </select>
            <label for="perPage">Empleados por página: </label>
          </div>
          <input type="hidden" name="page" value="<?= htmlspecialchars($_GET['page'] ?? 1) ?>">
          <input type="hidden" name="search" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
          <input type="hidden" name="dateFrom" value="<?= htmlspecialchars($_GET['dateFrom'] ?? '') ?>">
          <input type="hidden" name="dateTo" value="<?= htmlspecialchars($_GET['dateTo'] ?? '') ?>">
        </form>
      </div>
    </div>
  </div>

  <div class="container-fluid table-responsive">
    <table id="" class="table table-hover table-bordered">
      <thead class="table-group-divider align-middle text-center table-dark">
        <tr> <!-- Table headers / Encabezados de la tabla -->
          <th> <!-- No. de Empleado -->
            <a href="<?= sortLink('code_employee', $pagination['sort'], $pagination['order']) ?>"
              class="text-white d-flex flex-row align-items-center justify-content-center" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Ordenar por No. de Empleado"
              style="text-decoration: none;">
              <p class="my-0 mx-3">No. de Empleado</p>
              <i class="fa-solid <?= sortIcon('code_employee', $pagination['sort'], $pagination['order']) ?>"></i>
            </a>
          </th>
          <th> <!-- No. de Nómina -->
            <a href="<?= sortLink('number_payroll_contract', $pagination['sort'], $pagination['order']) ?>"
              class="text-white d-flex flex-row align-items-center justify-content-center" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Ordenar por No. de Nómina"
              style="text-decoration: none;">
              <p class="my-0 mx-3">No. de Nómina</p>
              <i class="fa-solid <?= sortIcon('number_payroll_contract', $pagination['sort'], $pagination['order']) ?>"></i>
            </a>
          </th>
          <th> <!-- Nombre del Empleado -->
            <a href="<?= sortLink('name_employee', $pagination['sort'], $pagination['order']) ?>"
              class="text-white d-flex flex-row align-items-center justify-content-center" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Ordenar por Nombre del Empleado"
              style="text-decoration: none;">
              <p class="my-0 mx-3">Nombre del Empleado</p>
              <i class="fa-solid <?= sortIcon('name_employee', $pagination['sort'], $pagination['order']) ?>"></i>
            </a>
          </th>
          <th> <!-- Fecha de Ingreso -->
            <a href="<?= sortLink('date_hired', $pagination['sort'], $pagination['order']) ?>"
              class="text-white d-flex flex-row align-items-center justify-content-center" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Ordenar por Fecha de Ingreso"
              style="text-decoration: none;">
              <p class="my-0 mx-3">Fecha de Ingreso</p>
              <i class="fa-solid <?= sortIcon('date_hired', $pagination['sort'], $pagination['order']) ?>"></i>
            </a>
          </th>
          <th> <!-- Estado del Empleado -->
            <a href="<?= sortLink('status_employee', $pagination['sort'], $pagination['order']) ?>"
              class="text-white d-flex flex-row align-items-center justify-content-center" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Ordenar por Estado del Empleado"
              style="text-decoration: none;">
              <p class="my-0 mx-3">Estado</p>
              <i class="fa-solid <?= sortIcon('status_employee', $pagination['sort'], $pagination['order']) ?>"></i>
            </a>
          </th>
          <th> <!-- Tipo de Empleado -->
            <a href="<?= sortLink('type_employee', $pagination['sort'], $pagination['order']) ?>"
              class="text-white d-flex flex-row align-items-center justify-content-center" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Ordenar por Tipo de Empleado"
              style="text-decoration: none;">
              <p class="my-0 mx-3">Tipo</p>
              <i class="fa-solid <?= sortIcon('type_employee', $pagination['sort'], $pagination['order']) ?>"></i>
            </a>
          </th>
          <th> <!-- Antigüedad -->
            <a href="<?= sortLink('seniority_employee', $pagination['sort'], $pagination['order']) ?>"
              class="text-white d-flex flex-row align-items-center justify-content-center" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Ordenar por Antigüedad"
              style="text-decoration: none;">
              <p class="my-0 mx-3">Antigüedad</p>
              <i class="fa-solid <?= sortIcon('seniority_employee', $pagination['sort'], $pagination['order']) ?>"></i>
            </a>
          </th>

          <th> <!-- Posición -->
            <a href="<?= sortLink('name_position', $pagination['sort'], $pagination['order']) ?>"
              class="text-white d-flex flex-row align-items-center justify-content-center" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Ordenar por Posición"
              style="text-decoration: none;">
              <p class="my-0 mx-3">Posición</p>
              <i class="fa-solid <?= sortIcon('name_position', $pagination['sort'], $pagination['order']) ?>"></i>
            </a>
          </th>
          <th> <!-- Área -->
            <a href="<?= sortLink('name_department', $pagination['sort'], $pagination['order']) ?>"
              class="text-white d-flex flex-row align-items-center justify-content-center" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Ordenar por Área"
              style="text-decoration: none;">
              <p class="my-0 mx-3">Área</p>
              <i class="fa-solid <?= sortIcon('name_department', $pagination['sort'], $pagination['order']) ?>"></i>
            </a>
          </th>
          <th> <!-- Jefe Directo -->
            <a href="<?= sortLink('name_manager', $pagination['sort'], $pagination['order']) ?>"
              class="text-white d-flex flex-row align-items-center justify-content-center" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Ordenar por Jefe Directo"
              style="text-decoration: none;">
              <p class="my-0 mx-3">Jefe Directo</p>
              <i class="fa-solid <?= sortIcon('name_manager', $pagination['sort'], $pagination['order']) ?>"></i>
            </a>
          </th>
          <th>Acciones</th> <!-- Actions -->
        </tr>
      </thead>
      <tbody class="align-middle text-center">
        <?php foreach($employees as $employee): ?>
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
            <?= date('d-m-Y', strtotime($employee['date_hired'])) ?>
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

              <button class="btn btn-sm btn-warning tl-btn-incident" data-bs-toggle="tooltip" data-bs-title="Levantar Incidencia">
                <i class="fa-solid fa-bell tl-icon-xl"></i>
              </button>

              <a href="/employees/profile/<?= $employee['id_employee'] ?>"
                  class="btn btn-sm btn-warning" data-bs-toggle="tooltip" data-bs-title="Calificar Empleado">
                  <i class="fa-solid fa-star tl-icon-xl"></i>
              </a>

              <a href="/employees/profile/<?= $employee['id_employee'] ?>"
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

  <!-- Show number of records / Mostrar número de registros -->
  <?php if (!isset($pagination) || $pagination['total_pages'] <= 1): ?>
  <div class="my-4" style="max-width: 80%; margin: auto;">
    <p class="text-muted text-center m-0">
      Mostrando <?= count($employees) ?> de <?= count($employees) ?> empleados
    </p>
  </div>
  <?php endif; ?>
  <!-- Pagination controls / Controles de paginación -->
  <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
  <div class="my-4 d-flex justify-content-between align-items-center" style="max-width: 80%; margin: auto;">
    <!-- Pagination info / Información de paginación -->
    <p class="text-muted text-center m-0">
      Mostrando <?= count($employees) ?> de <?= $pagination['total_items'] ?> empleados 
      (Página <?= $pagination['current_page'] ?> de <?= $pagination['total_pages'] ?>)
    </p>
    <!-- Pagination links / Enlaces de paginación -->
    <nav aria-label="Employee pagination">
      <ul class="pagination tl-pagination-md justify-content-end m-0">
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
  <?php endif; ?>

<?php $content = ob_get_clean(); include __DIR__.'/../layouts/layout-main.php'; ?>