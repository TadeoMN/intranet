<?php ob_start(); ?>
<?php
  $exportFilters = [
    'search' => $_GET['search'] ?? null,
    'dateFrom' => $_GET['dateFrom'] ?? null,
    'dateTo' => $_GET['dateTo'] ?? null,
    'sort' => $_GET['sort'] ?? null,
    'order' => $_GET['order'] ?? null,
    'status' => $_GET['status'] ?? null,
  ];

  $exportFilters = array_filter(
    $exportFilters,
    fn($value) => $value !== null && $value !== ''
  );

  $exportQuery = http_build_query($exportFilters);
  $exportExcelUrl = '/incidents/export/excel' . ($exportQuery ? '?' . $exportQuery : '');
  $exportPdfUrl = '/incidents/export/pdf' . ($exportQuery ? '?' . $exportQuery : '');
?>

<!-- PAGE HEADER / FILTERS AND ACTIONS -->
<div class="container-fluid">
  <div class="row g-2 my-3 align-items-center">
    <!-- ADD INCIDENT BUTTON -->
    <div class="col-2 col-xxl-1 order-0 text-center">
      <div class="form-floating">
        <button class="btn btn-success w-100 tl-btn-incident tl-btn-export" data-bs-toggle="tooltip" data-bs-title="Agregar Incidencia">
          <i class="fa-solid fa-square-plus tl-icon-xl"></i>
        </button>
      </div>
    </div>
    <!-- SEARCH INPUT -->
    <div class="col-8 col-xl-6 col-xxl-4 order-4 order-xl-3 order-xxl-1">
      <form method="GET" action="/incidents/list">
      <div class="form-floating">
        <input type="text" class="form-control" aria-label="Nombre o ID de empleado" name="search" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" aria-describedby="searchButton" placeholder="Buscar empleado/incidencia:">
        <label for="search">Buscar empleado/incidencia: </label>
      </div>
    </div>
    <!-- DATE INPUTS -->
    <div class="col-12 col-xl-4 col-xxl-3 order-3 order-xl-4 order-xxl-2 d-flex flex-row gap-2">
      <div class="form-floating w-100">
        <input type="date" name="dateFrom" id="dateFrom" class="form-control" value="<?= htmlspecialchars($_GET['dateFrom'] ?? '') ?>">
        <label for="dateFrom">Desde:</label>
      </div>
      <div class="form-floating w-100">
        <input type="date" name="dateTo" id="dateTo" class="form-control" value="<?= htmlspecialchars($_GET['dateTo'] ?? '') ?>">
        <label for="dateTo">Hasta:</label>
      </div>
    </div>
    <!-- FILTERS BUTTONS -->
    <div class="col-4 col-xl-2 col-xxl-1 order-5 order-xxl-3 d-flex flex-row gap-2 justify-content-center">
      <button type="submit" class="btn btn-sm btn-primary w-100 tl-btn-export" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Aplicar filtros">
        <i class="fa-solid fa-magnifying-glass tl-icon-xl"></i>
      </button>
      <button type="button" class="btn btn-sm btn-secondary w-100 tl-btn-export" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Limpiar filtros" onclick="location.href = '/incidents/list'">
        <i class="fa-solid fa-broom-wide tl-icon-xl"></i>
      </button>
    </div>
    <!-- RECORDS PER PAGE SELECTOR -->
    <div class="col-6 col-xxl-2 order-1 order-xl-2 order-xxl-4">
      <div class="form-floating">
        <select class="form-select text-nowrap" id="perPage" name="limit" onchange="const form = this.form; if (form.page) { form.page.value = 1; } form.submit();" aria-label="Empleados por página">
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
    </div>
    </form>
    <!-- EXPORT BUTTONS -->
    <div class="col-4 col-xxl-1 order-2 order-xxl-5 d-flex flex-row gap-2 justify-content-center align-content-center">
      <a href="<?= htmlspecialchars($exportExcelUrl, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-sm btn-success w-100 tl-btn-export d-flex align-items-center justify-content-center" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Descargar Excel">
        <i class="fa-solid fa-file-excel tl-icon-xl"></i>
      </a>
      <a href="<?= htmlspecialchars($exportPdfUrl, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-sm btn-danger w-100 tl-btn-export d-flex align-items-center justify-content-center" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Descargar PDF">
        <i class="fa-solid fa-file-pdf tl-icon-xl"></i>
      </a>
    </div>
  </div>
</div>
<!-- END PAGE HEADER / FILTERS AND ACTIONS -->
 
<!-- INCIDENTS TABLE -->
<div class="container-fluid table-responsive">
  <table class="table table-hover table-bordered">
    <thead class="table-group-divider align-middle text-center table-dark" style="height: 60px;">
      <tr>
        <th>
          <a href="<?= sortLink('id_incident', $pagination['sort'], $pagination['order']) ?>"
            class="text-white d-flex flex-row align-items-center justify-content-center text-decoration-none" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Ordenar por No. de Incidente">
            <p class="my-0 mx-3">No.</p>
            <i class="fa-solid <?= sortIcon('id_incident', $pagination['sort'], $pagination['order']) ?>"></i>
          </a>
        </th>
        <th>
          <a href="<?= sortLink('employee.name_employee', $pagination['sort'], $pagination['order']) ?>"
            class="text-white d-flex flex-row align-items-center justify-content-center text-decoration-none" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Ordenar por Empleado">
            <p class="my-0 mx-3">Empleado</p>
            <i class="fa-solid <?= sortIcon('employee.name_employee', $pagination['sort'], $pagination['order']) ?>"></i>
          </a>
        </th>
        <th>
          <a href="<?= sortLink('code_incident_type', $pagination['sort'], $pagination['order']) ?>"
            class="text-white d-flex flex-row align-items-center justify-content-center text-decoration-none" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Ordenar por Tipo de Incidencia">
            <p class="my-0 mx-3">Cód. Incidencia</p>
            <i class="fa-solid <?= sortIcon('code_incident_type', $pagination['sort'], $pagination['order']) ?>"></i>
          </a>
        </th>
        <th>
          <a href="<?= sortLink('name_incident_type', $pagination['sort'], $pagination['order']) ?>"
            class="text-white d-flex flex-row align-items-center justify-content-center text-decoration-none" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Ordenar por Nombre de Incidencia">
            <p class="my-0 mx-3">Nombre Incidencia</p>
            <i class="fa-solid <?= sortIcon('name_incident_type', $pagination['sort'], $pagination['order']) ?>"></i>
          </a>
        </th>
        <th>
          <a href="<?= sortLink('reporter_name', $pagination['sort'], $pagination['order']) ?>"
            class="text-white d-flex flex-row align-items-center justify-content-center text-decoration-none" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Ordenar por Reportado por">
            <p class="my-0 mx-3">Reportado por</p>
            <i class="fa-solid <?= sortIcon('reporter_name', $pagination['sort'], $pagination['order']) ?>"></i>
          </a>
        </th>
        <th>
          <a href="<?= sortLink('reported_at', $pagination['sort'], $pagination['order']) ?>"
            class="text-white d-flex flex-row align-items-center justify-content-center text-decoration-none" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Ordenar por Fecha de Reporte">
            <p class="my-0 mx-3">Fecha de reporte</p>
            <i class="fa-solid <?= sortIcon('reported_at', $pagination['sort'], $pagination['order']) ?>"></i>
          </a>
        </th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody class="align-middle text-center">
      <?php foreach ($incidents as $incident): ?>
        <tr>
          <td><?= htmlspecialchars($incident['id_incident']) ?></td>
          <td><?= htmlspecialchars($incident['name_employee']) ?></td>
          <td><?= htmlspecialchars($incident['code_incident_type']) ?></td>
          <td><?= htmlspecialchars($incident['name_incident_type']) ?></td>
          <td><?= htmlspecialchars($incident['reporter_name']) ?></td>
          <td><?= date_lenguage_spanish($incident['reported_at']) ?></td>
          <td>
            <a href="#"
              class="btn btn-sm btn-warning tl-btn-view-incident" data-bs-toggle="tooltip" data-bs-title="Ver detalles" data-id="<?= $incident['id_incident'] ?>">
              <i class="fa-solid fa-eye tl-icon-xl"></i>
            </a>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($incidents)): ?>
        <tr>
          <td colspan="7" class="text-center">
            <i class="fa-solid fa-triangle-exclamation text-warning tl-icon-xl mx-3"></i>
            <strong>No se encontraron incidencias.</strong>
          </td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
<!-- END INCIDENTS TABLE -->

<!-- PAGINATION CONTROLS -->
<div class="container-fluid">
  <!-- Show number of records / Mostrar número de registros -->
  <?php if (!isset($pagination) || $pagination['total_pages'] <= 1): ?>
    <div class="row my-4">
      <div class="col-12">
        <p class="text-muted text-center m-0">
          Mostrando <?= count($incidents) ?> de <?= count($incidents) ?> incidentes
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
          Mostrando <?= count($incidents) ?> de <?= $pagination['total_items'] ?> incidentes
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
<!-- END PAGINATION CONTROLS -->
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/layout-main.php'; ?>
