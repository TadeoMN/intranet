<?php ob_start(); ?>

<?php include __DIR__.'/../includes/header.php'; ?>

  <div class="container my-3">
    <h1 class="text-center">Empleados</h1>
    <p class="text-center">Formulario de creación de empleado</p>
  </div>

  <div class="container my-3">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/dashboard">Inicio</a></li>
        <li class="breadcrumb-item"><a href="/employees/list">Empleados</a></li>
        <li class="breadcrumb-item active" aria-current="page">Crear Empleado</li>
      </ol>
    </nav>
  </div>

  <div class="container my-3">
    <form method="POST" action="/employee/store">
      <div class="form-floating mb-3">
        <input type="text" class="form-control" id="code_employee" name="code_employee" required placeholder="">
        <label for="code_employee" class="form-label">Número de Empleado</label>
      </div>
      <div class="form-floating mb-3">
        <input type="text" class="form-control" id="name_employee" name="name_employee" required placeholder="">
        <label for="name_employee" class="form-label">Nombre del Empleado</label>
      </div>
      <div class="form-floating mb-3">
        <input type="date" class="form-control" id="date_hired" name="date_hired" required>
        <label for="date_hired" class="form-label">Fecha de Contratación</label>
      </div>

      <div class="form-floating mb-3">
        <select class="form-select" id="type_employee" name="type_employee" required>
          <option value="OPERATIVO">OPERATIVO</option>
          <option value="ADMINISTRATIVO">ADMINISTRATIVO</option>
        </select>
        <label for="type_employee" class="form-label">Tipo de Empleado</label>
      </div>

      <div class="form-floating mb-3">
        <select class="form-select" id="id_department_fk" name="id_department_fk" required>
          <option value="">Seleccione un departamento</option>
          <?php foreach ($departments as $department): ?>
            <option value="<?= $department['id_department'] ?>"><?= htmlspecialchars($department['name_department']) ?></option>
          <?php endforeach; ?>
        </select>
        <label for="id_department_fk" class="form-label">Departamento</label>
      </div>

      <div class="form-floating mb-3">
        <select class="form-select" id="id_position_fk" name="id_position_fk" required>
          <option value="">Seleccione un puesto</option>
        </select>
        <label for="id_position_fk" class="form-label">Puesto</label>
      </div>

      <?= cascadePosition($positionsByDepartment) ?>

      <button type="submit" class="btn btn-primary">Crear Empleado</button>
      <a href="/employees/list" class="btn btn-secondary">Cancelar</a>
    </form>
  </div>

<?php include __DIR__.'/../includes/footer.php'; ?>

<?php $content = ob_get_clean(); include __DIR__.'/../layouts/layout-main.php'; 