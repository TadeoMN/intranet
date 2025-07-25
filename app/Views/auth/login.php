<?php ob_start(); ?>

  <section class="m-auto w-100 rounded-4" style="max-width: 550px; padding: 4rem 2rem; background-color: rgba(0, 0, 0, .50)">
    <h1 class="py-4 m-auto fw-normal text-white">Iniciar Sesión </h1>
    <form action="/login" method="POST">
      <div class="form-floating py-1">
        <input type="text" class="form-control" id="floatingInput" placeholder="usuario" name="name" required>
        <label for="floatingInput">Usuario</label>
      </div>
      <div class="form-floating py-1">
        <input type="password" class="form-control" id="floatingPassword" placeholder="Contraseña" name="password" required>
        <label for="floatingPassword">Contraseña</label>
      </div>
      <button class="btn btn-success w-100 my-1 mx-0 py-3 fw-bold tlq-btn" type="submit">Entrar</button>
    </form>
  </section>

<?php include __DIR__.'/../includes/footer.php'; ?>

<?php $content = ob_get_clean(); include __DIR__.'/../layouts/layout-login.php'; ?>