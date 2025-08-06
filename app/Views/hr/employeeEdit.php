<?php ob_start(); ?>

<?php include __DIR__.'/../includes/header.php'; ?>

  <div class="container my-4">
    <h1 class="text-center">Empleados</h1>
    <p class="text-center">Formulario de edición de empleado</p>
  </div>

  <div class="container my-4">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/dashboard">Inicio</a></li>
        <li class="breadcrumb-item"><a href="/employees/list">Empleados</a></li>
        <li class="breadcrumb-item active" aria-current="page">Editar Empleado</li>
      </ol>
    </nav>
  </div>

  <div class="container my-4">
    <form action="/employees/edit" method="POST">
      <input type="hidden" name="id" value="<?php echo $employee['id_employee']; ?>">
      <div class="mb-3">
        <label for="name" class="form-label">Nombre</label>
        <input type="text" class="form-control" id="name" name="name" value="<?php echo $employee['name_employee']; ?>" required>
      </div>
      <div class="mb-3">
        <label for="position" class="form-label">Puesto</label>
        <select class="form-select" id="position" name="position" required>
          <?php foreach ($positionsByDepartment as $departmentId => $positions): ?>
            <optgroup label="<?php echo htmlspecialchars($departments[$departmentId]['name_department']); ?>">
              <?php foreach ($positions as $position): ?>
                <option value="<?php echo $position['id_position']; ?>" <?php echo $employee['id_position_fk'] == $position['id_position'] ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($position['name_position']); ?>
                </option>
              <?php endforeach; ?>
            </optgroup>
          <?php endforeach; ?>
        </select>
      </div>
      <button type="submit" class="btn btn-primary">Guardar Cambios</button>
    </form>
  </div>

<?php include __DIR__.'/../includes/footer.php'; ?>

<?php $content = ob_get_clean(); include __DIR__.'/../layouts/layout-main.php'; ?>