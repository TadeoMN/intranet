<?php ob_start(); ?>

<?php include __DIR__.'/../includes/header.php'; ?>

  <div class="container my-3">
    <h1 class="text-center">Empleados</h1>
    <p class="text-center">Lista de empleados registrados en el sistema.</p>
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

<?php include __DIR__.'/../includes/footer.php'; ?>

<?php $content = ob_get_clean(); include __DIR__.'/../layouts/layout-main.php'; ?>