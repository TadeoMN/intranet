<?php ob_start(); ?>

<?php include __DIR__.'/../includes/header.php'; ?>

  <div class="container my-3">
    <h1 class="text-center">Empleados</h1>
    <p class="text-center">Lista de empleados registrados en el sistema.</p>
  </div>

  <div style="max-width: 80%; max-height: 60vh; overflow-y: auto; margin: 35px auto auto;">
    <div class="row mx-0 mb-3 d-flex align-items-center p-0">

      <div class="py-1 col-sm-12 col-md-4 col-lg-2">
        <a href="/employees/create" class="btn btn-success">
          <i class="fa-solid fa-plus"></i> Agregar empleado
        </a>
      </div>

      <div class="py-1 col-sm-12 col-md-4 col-lg-8">
        <form method="GET" action="/employees/list" class="row g-2 align-items-center justify-content-center">

          <div class="form-floating col-auto">
            <input type="text" class="form-control" aria-label="Nombre o ID de empleado" name="search"
            value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" aria-describedby="searchButton">
            <label for="search">Buscar Empleado:</label>
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

      <div class="py-1 col-sm-12 col-md-4 col-lg-2">
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

  <?php
    $currentSort = $_GET['sort'] ?? 'code_employee';
    $currentOrder = (isset($_GET['order']) && strtolower($_GET['order']) === 'desc') ? 'desc' : 'asc'; 
    
    function sortLink(string $field): string {
        global $currentSort, $currentOrder;
        $newOrder = ($currentSort === $field && $currentOrder === 'asc') ? 'desc' : 'asc';
        return buildUrl(['sort' => $field, 'order' => $newOrder, 'page' => 1]);
    }

    function sortIcon(string $field): string {
        global $currentSort, $currentOrder;
        if ($currentSort !== $field) { return 'fa-house-chimney'; }
      return $currentOrder === 'asc' ? 'fa-sort-up' : 'fa-sort-down';
    }

    var_dump($_GET['sort'] ?? null);
    var_dump($_GET['order'] ?? null);
  ?>

  <div class="table-responsive" style="max-width: 80%; max-height: 60vh; overflow-y: auto; margin: auto;">
    <table id="" class="table table-hover table-bordered">
      <thead class="table-group-divider align-middle text-center table-dark">
        <tr>
          <th style="width: 5%;">
            <a href="<?= sortLink('code_employee') ?>" class="text-white" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Ordenar por No. de Empleado">
              No. de Empleado
              <i class="fa-solid <?= sortIcon('code_employee') ?>"></i>
            </a>
          </th>
          <th style="width: 20%;">
            Nombre Completo
          </th>
          <th style="width: 10%;">
            Fecha de Ingreso
          </th>
          <th>
            Estado
          </th>
          <th>
            Tipo
          </th>
          <th>
            Antigüedad
          </th>
          <th>
            Usuario Asociado
          </th>
          <th>
            Posición
          </th>
          <th>
            Área
          </th>
          <th>
              Jefe Directo
          </th>
          <th colspan="3">Acciones</th>
        </tr>
      </thead>
      <tbody class="align-middle text-center">
        <?php foreach($employees as $employee): ?>
        <tr>
          <td><?= $employee['code_employee'] ?></td>
          <td><?= $employee['name_employee'] ?></td>
          <td><?= date('d-m-Y', strtotime($employee['date_hired'])) ?></td>
          <td><?= $employee['status_employee'] ?></td>
          <td><?= $employee['type_employee'] ?></td>
          <td><?= $employee['seniority_employee'] ?> años</td>
          <td><?= $employee['name_user'] ?></td>
          <td><?= $employee['name_position'] ?></td>
          <td><?= $employee['name_department'] ?></td>
          <td><?= $employee['name_manager'] ? $employee['name_manager'] : 'N/A' ?></td>
          <td>
            <a href="/employees/edit/<?= $employee['id_employee'] ?>"
                class="btn btn-sm btn-primary" data-bs-toggle="tooltip" data-bs-title="Editar">
              <i class="fa-solid fa-pen-to-square tl-icon-xl"></i>
            </a>
          </td>
          <td class="text-center">
            <form action="/employees/delete/<?= $employee['id_employee'] ?>" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar este empleado?');">
              <button type="submit" class="btn btn-sm btn-danger" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Eliminar">
                <i class="fa-solid fa-trash tl-icon-xl"></i>
              </button>
            </form>
          </td>
          <td class="text-center">
            <a href="/employees/view/<?= $employee['id_employee'] ?>"
                class="btn btn-sm btn-warning" data-bs-toggle="tooltip" data-bs-title="Ver Expediente Completo">
                <i class="fa-solid fa-eye tl-icon-xl"></i>
            </a>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($employees)): ?>
        <tr>
          <td colspan="11" class="text-center">No se encontraron empleados.</td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Pagination controls / Controles de paginación -->
  <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
  <div class="mt-4 d-flex justify-content-between align-items-center" style="max-width: 80%; margin: auto;">
    <!-- Pagination info / Información de paginación -->
    <p class="text-muted text-center m-0">
      Mostrando <?= count($employees) ?> de <?= $pagination['total_items'] ?> empleados 
      (Página <?= $pagination['current_page'] ?> de <?= $pagination['total_pages'] ?>)
    </p>

    <nav aria-label="Employee pagination">
      <ul class="pagination tl-pagination-md justify-content-end m-0">
        <!-- First page / Página inicial -->
        <?php if ($pagination['current_page'] > 1): ?>
          <li class="page-item">
            <a class="page-link" href="<?= pageUrl(1) ?>" aria-label="Primera" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Primera">
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
            <a class="page-link" href="<?= pageUrl($pagination['prev_page']) ?>" aria-label="Anterior" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Anterior">
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
            <a class="page-link" href="<?= pageUrl($i) ?>" aria-label="Página <?= $i ?>" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Página <?= $i ?>">
              <?= $i ?>
            </a>
          </li>
        <?php endfor; ?>

        <!-- Next page / Página siguiente -->
        <?php if ($pagination['has_next']): ?>
          <li class="page-item">
            <a class="page-link" href="<?= pageUrl($pagination['next_page']) ?>" aria-label="Siguiente" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Siguiente">
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
            <a class="page-link" href="<?= pageUrl($pagination['total_pages']) ?>" aria-label="Última" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Última">
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

<?php include __DIR__.'/../includes/footer.php'; ?>

<?php $content = ob_get_clean(); include __DIR__.'/../layouts/layout-main.php'; 