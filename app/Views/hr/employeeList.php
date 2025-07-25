<?php ob_start(); ?>

<?php include __DIR__.'/../includes/header.php'; ?>

  <div class="container my-3">
    <h1 class="text-center">Empleados</h1>
    <p class="text-center">Lista de empleados registrados en el sistema.</p>
    
    <!-- Pagination info / Información de paginación -->
    <?php if (isset($pagination)): ?>
    <div class="row">
      <div class="col-12">
        <p class="text-muted text-center">
          Mostrando <?= count($employees) ?> de <?= $pagination['total_items'] ?> empleados 
          (Página <?= $pagination['current_page'] ?> de <?= $pagination['total_pages'] ?>)
        </p>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <div class="table-responsive container mt-5 mb-auto">
    <table id="tblEmployees" class="table align-middle table-hover">
      <thead class="sticky-top">
        <tr>
          <th class="text-center">ID Empleado</th>
          <th class="text-center">Nombre</th>
          <th class="text-center">Fecha de Ingreso</th>
          <th class="text-center">Estado</th>
          <th class="text-center">Tipo</th>
          <th class="text-center">Antigüedad</th>
          <th class="text-center">Usuario Asociado</th>
          <th class="text-center">Posición</th>
          <th class="text-center">Área</th>
          <th class="text-center">Jefe Directo</th>
          <th class="text-center" colspan="3">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($employees as $employee): ?>
        <tr>
          <td class="text-center"><?= $employee['code_employee'] ?></td>
          <td class="text-center"><?= $employee['name_employee'] ?></td>
          <td class="text-center"><?= date('d-m-Y', strtotime($employee['date_hired'])) ?></td>
          <td class="text-center"><?= $employee['status_employee'] ?></td>
          <td class="text-center"><?= $employee['type_employee'] ?></td>
          <td class="text-center"><?= $employee['seniority_employee'] ?> años</td>
          <td class="text-center"><?= $employee['name_user'] ?></td>
          <td class="text-center"><?= $employee['name_position'] ?></td>
          <td class="text-center"><?= $employee['name_department'] ?></td>
          <td class="text-center"><?= $employee['id_manager_employee_fk'] ?></td>
          <td class="text-center">
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
      </tbody>
    </table>
  </div>

  <!-- Pagination controls / Controles de paginación -->
  <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
  <div class="container mt-4">
    <nav aria-label="Employee pagination">
      <ul class="pagination pagination-sm justify-content-center">
        
        <!-- Previous page / Página anterior -->
        <?php if ($pagination['has_prev']): ?>
          <li class="page-item">
            <a class="page-link" href="?page=<?= $pagination['prev_page'] ?>" aria-label="Anterior">
              <span aria-hidden="true">&laquo;</span>
            </a>
          </li>
        <?php else: ?>
          <li class="page-item disabled">
            <span class="page-link" aria-label="Anterior">
              <span aria-hidden="true">&laquo;</span>
            </span>
          </li>
        <?php endif; ?>

        <!-- Page numbers / Números de página -->
        <?php
        $start_page = max(1, $pagination['current_page'] - 2);
        $end_page = min($pagination['total_pages'], $pagination['current_page'] + 2);
        
        for ($i = $start_page; $i <= $end_page; $i++): ?>
          <li class="page-item <?= $i == $pagination['current_page'] ? 'active' : '' ?>">
            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
          </li>
        <?php endfor; ?>

        <!-- Next page / Página siguiente -->
        <?php if ($pagination['has_next']): ?>
          <li class="page-item">
            <a class="page-link" href="?page=<?= $pagination['next_page'] ?>" aria-label="Siguiente">
              <span aria-hidden="true">&raquo;</span>
            </a>
          </li>
        <?php else: ?>
          <li class="page-item disabled">
            <span class="page-link" aria-label="Siguiente">
              <span aria-hidden="true">&raquo;</span>
            </span>
          </li>
        <?php endif; ?>
        
      </ul>
    </nav>
  </div>
  <?php endif; ?>

<?php include __DIR__.'/../includes/footer.php'; ?>

<?php $content = ob_get_clean(); include __DIR__.'/../layouts/layout-main.php'; ?>