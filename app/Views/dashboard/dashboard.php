<?php ob_start(); ?>

<?php include __DIR__.'/../includes/header.php'; ?>

  <div class="container my-3">
    <h1 class="text-center">Dashboard  <?= $session['id_session'] ?></h1>
      <p class="text-center">Bienvenido al panel de control <?= $_SESSION['name'] ?? 'invitado' ?>.</p>
  </div>

  <div class="text-center container mt-4 mb-3">
    <h4>Sesiones activas</h4>
  </div>
  <div class="table-responsive container">
    <table id="tblActivas" class="table table-hover align-middle">
      <thead>
        <tr>
          <th class="text-center">ID</th>
          <th class="text-center">Usuario</th>
          <th class="text-center">IP</th>
          <th class="text-center">Inicio</th>
          <th class="text-center">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($active as $s): ?>
        <tr>
          <td class="text-center"><?= $s['id_session'] ?></td>
          <td class="text-center"><?= $s['name_user'] ?></td>
          <td class="text-center"><?= $s['ip_addr_session'] ?></td>
          <td class="text-center"><?= date('d-m-Y H:i:s', strtotime($s['login_at'])) ?></td>
          <td class="text-center">
            <button class="btn btn-sm btn-danger close-sess" data-id="<?= $s['id_session'] ?>">
              Cerrar
            </button>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>


  <div class="text-center container mt-4 mb-3">
    <h4 class="mt-4">Historial de sesiones</h4>
  </div>

  <div class="table-responsive container">
    <table id="tblHist" class="table table-hover align-middle">
      <thead>
        <tr>
          <th class="text-center">ID</th>
          <th class="text-center">Usuario</th>
          <th class="text-center">IP</th>
          <th class="text-center">Inicio</th>
          <th class="text-center">Fin</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($history as $s): ?>
        <tr>
          <td class="text-center"><?= $s['id_session'] ?></td>
          <td class="text-center"><?= $s['name_user'] ?></td>
          <td class="text-center"><?= $s['ip_addr_session'] ?></td>
          <td class="text-center"><?= date('d-m-Y H:i:s', strtotime($s['login_at'])) ?></td>
          <td class="text-center"><?= date('d-m-Y H:i-s', strtotime($s['logout_at'])) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

<?php include __DIR__.'/../includes/footer.php'; ?>

<?php $content = ob_get_clean(); include __DIR__.'/../layouts/layout-main.php'; ?>