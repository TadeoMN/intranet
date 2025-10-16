<?php ob_start(); ?>

  <div class="container my-3">
    <h1 class="text-center">Dashboard</h1>
    <p class="text-center">Bienvenido a Top Label Intranet: <?= $_SESSION['name_employee'] ?? 'invitado' ?>.</p>
  </div>

  <div class="container">
    <div class="tl-grid-container">
      <?php foreach ($departments as $department): ?>
        <div class="card shadow-sm">
          <h2 class="card-header bg-dark text-white text-center"><?= htmlspecialchars($department['name_department']) ?></h2>
          <div class="card-body text-center">
            <img src="/assets/images/department.png" class="img-fluid mb-3" alt="<?= htmlspecialchars($department['name_department']) ?>" style="max-height: 220px;">
          </div>
          <div class="card-footer bg-dark text-white">
            <a href="/employees/list?search=<?= $department['name_department'] ?>" class="btn btn-outline-light">
              Ver Empleados
            </a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

<?php $content = ob_get_clean(); include __DIR__.'/../layouts/layout-main.php'; ?>