<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= $title ?? 'Intranet' ?></title>
  <!-- Performance optimizations / Optimizaciones de rendimiento -->
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="description" content="Sistema de Intranet Corporativa Top Label">
  <!-- Preload critical resources / Precargar recursos críticos -->
  <link rel="preload" href="/assets/vendor/bootstrap/css/bootstrap.min.css?v=5.3.7" as="style">
  <link rel="preload" href="/assets/vendor/jquery/jquery-3.7.1.min.js?v=3.7.1" as="script">
  <!-- DNS prefetch for external resources / DNS prefetch para recursos externos -->
  <link rel="dns-prefetch" href="//kit.fontawesome.com">
  <!-- Critical CSS inlined or high priority / CSS crítico inline o alta prioridad -->
  <link rel="stylesheet" href="/assets/vendor/bootstrap/css/bootstrap.min.css?v=5.3.7">
  <link rel="stylesheet" href="/assets/css/app.css">
  <!-- Non-critical CSS loaded asynchronously / CSS no crítico cargado asincrónicamente -->
  <link rel="preload" href="/assets/vendor/datatables/css/datatables.min.css?v=2.3.2" as="style" onload="this.onload=null;this.rel='stylesheet'">
  <link rel="preload" href="/assets/vendor/sweetalert2/sweetalert2.min.css?v=11.22.2" as="style" onload="this.onload=null;this.rel='stylesheet'">
  <!-- Fallback for browsers without JS / Fallback para navegadores sin JS -->
  <noscript>
    <link rel="stylesheet" href="/assets/vendor/datatables/css/datatables.min.css?v=2.3.2">
    <link rel="stylesheet" href="/assets/vendor/sweetalert2/sweetalert2.min.css?v=11.22.2">
  </noscript>
  <!-- Cache control headers for static assets / Headers de control de caché para recursos estáticos -->
  <meta http-equiv="Cache-Control" content="public, max-age=3600">
</head>

<body>

  <header class="bg-dark text-white py-3 sticky-top">    
      <nav class="navbar navbar-dark bg-dark sticky-top">
          <div class="container-fluid justify-content-start">
              <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasDarkNavbar" aria-controls="offcanvasDarkNavbar" aria-label="Toggle navigation">
                  <span class="navbar-toggler-icon"></span>
              </button>
              <a class="navbar-brand mx-3" href="/dashboard">Top Label</a>
              <div class="offcanvas offcanvas-start text-bg-dark" tabindex="-1" id="offcanvasDarkNavbar" aria-labelledby="offcanvasDarkNavbarLabel">
                  <div class="offcanvas-header">
                      <h5 class="offcanvas-title" id="offcanvasDarkNavbarLabel">Top Label</h5>
                      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                  </div>
                  <div class="offcanvas-body">
                      <ul class="navbar-nav justify-content-end flex-grow-1 pe-3">
                          <li class="nav-item dropdown">
                              <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink1" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                  Mi perfil
                              </a>
                              <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="navbarDropdownMenuLink1">
                                  <li>
                                      <a class="dropdown-item" href="#">
                                          <i class="fa-solid fa-user me-3"></i>
                                          Ver perfil
                                      </a>
                                  </li>
                                  <li>
                                      <hr class="dropdown-divider">
                                  </li>
                                  <li>
                                      <a class="dropdown-item" href="#">
                                          <i class="fa-solid fa-person-walking-arrow-right me-3"></i>
                                          Solicitar Permiso
                                      </a>
                                  </li>
                                  <li>
                                      <hr class="dropdown-divider">
                                  </li>
                                  <li>
                                      <a class="dropdown-item" href="#">
                                          <i class="fa-solid fa-circle-question me-3"></i>
                                          Ayuda
                                      </a>
                                  </li>
                                  <li>
                                      <a class="dropdown-item logout-link" href="/logout">
                                          <i class="fa-solid fa-right-from-bracket me-3"></i>
                                          Cerrar sesión
                                      </a>
                                  </li>
                              </ul>
                          </li>

                          <li class="nav-item dropdown">
                              <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink2" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                  Panel de administración
                              </a>
                              <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="navbarDropdownMenuLink2">
                                  <li>
                                      <a class="dropdown-item" href="#">
                                          <i class="fa-solid fa-users me-3"></i>
                                          Usuarios
                                      </a>
                                  </li>
                                  <li>
                                      <a class="dropdown-item" href="#">
                                          <i class="fa-solid fa-shield-halved me-3"></i>
                                          Permisos
                                      </a>
                                  </li>
                                  <li>
                                      <a class="dropdown-item" href="#">
                                          <i class="fa-solid fa-dice-three me-3"></i>
                                          Roles
                                      </a>
                                  </li>
                                  <li>
                                      <hr class="dropdown-divider">
                                  </li>
                                  <li>
                                      <a class="dropdown-item" href="#">
                                          <i class="fa-solid fa-user-clock me-3"></i>
                                          Sesiones Activas
                                      </a>
                                  </li>
                                  <li>
                                      <a class="dropdown-item" href="#">
                                          <i class="fa-solid fa-history me-3"></i>
                                          Historial Sesiones
                                      </a>
                                  </li>
                              </ul>
                          </li>

                          <li class="nav-item dropdown">
                              <a data-mdb-dropdown-init class="nav-link dropdown-toggle" href="#"
                                  id="navbarDropdownMenuLink3" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                  Panel de Recursos Humanos
                              </a>
                              <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="navbarDropdownMenuLink3">
                                    <li><h6 class="dropdown-header">Nuevos Registros</h6></li>
                                  <li>
                                      <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#createEmployeeModal">
                                          <i class="fa-solid fa-file-plus me-3 tl-icon"></i>
                                          Alta de Empleado
                                      </a>
                                  </li>
                                  <li>
                                      <a class="dropdown-item" href="#">
                                          <i class="fa-solid fa-file-plus me-3 tl-icon"></i>
                                          Alta de Perfil de Empleado
                                      </a>
                                  </li>
                                  <li>
                                      <a class="dropdown-item" href="#">
                                          <i class="fa-solid fa-file-plus me-3 tl-icon"></i>
                                          Alta de Contrato
                                      </a>
                                  </li>
                                  <li>
                                      <a class="dropdown-item" href="#">
                                          <i class="fa-solid fa-file-plus me-3 tl-icon"></i>
                                          Alta de Puesto
                                      </a>
                                  </li>
                                  <li>
                                      <a class="dropdown-item" href="#">
                                          <i class="fa-solid fa-file-plus me-3 tl-icon"></i>
                                          Alta de Incidencia
                                      </a>
                                  </li>

                                  <li>
                                      <hr class="dropdown-divider">
                                  </li>
                                  <li><h6 class="dropdown-header">Catálogos</h6></li>
                                  <li>
                                      <a class="dropdown-item" href="/employees/list">
                                          <i class="fa-solid fa-list-ul me-3 tl-icon"></i>
                                          Catálogo de Empleados
                                      </a>
                                  </li>
                                  <li>
                                      <a class="dropdown-item" href="#">
                                          <i class="fa-solid fa-list-ul me-3 tl-icon"></i>
                                          Catálogo de Áreas
                                      </a>
                                  </li>
                                  <li>
                                      <a class="dropdown-item" href="#">
                                          <i class="fa-solid fa-list-ul me-3 tl-icon"></i>
                                          Catálogo de Puestos
                                      </a>
                                  </li>

                                  <li>
                                      <hr class="dropdown-divider">
                                  </li>
                                  <li><h6 class="dropdown-header">Reportes</h6></li>
                                  <li>
                                      <a class="dropdown-item" href="#">
                                          <i class="fa-solid fa-file-circle-info me-3 tl-icon"></i>
                                          Reporte de Incidencias
                                      </a>
                                  </li>
                                  <li>
                                      <a class="dropdown-item" href="#">
                                          <i class="fa-solid fa-file-circle-info me-3 tl-icon"></i>
                                          Reporte de Calificaciones
                                      </a>
                                  </li>
                                  <li>
                                      <a class="dropdown-item" href="#">
                                          <i class="fa-solid fa-book-user me-3 tl-icon"></i>
                                          Historial de Puestos
                                      </a>
                                  </li>

                                  <li>
                                      <hr class="dropdown-divider">
                                  </li>
                                  <li><h6 class="dropdown-header">Autorizaciones</h6></li>
                                  <li>
                                      <a class="dropdown-item" href="#">
                                          <i class="fa-solid fa-handshake me-3 tl-icon"></i>
                                          Autorizar Permisos/Ausencias
                                      </a>
                                  </li>

                                  <li>
                                      <hr class="dropdown-divider">
                                  </li>
                                  <li><h6 class="dropdown-header">Configuración</h6></li>
                                  <li>
                                      <a class="dropdown-item" href="#">
                                          <i class="fa-solid fa-file-contract me-3 tl-icon"></i>
                                          Tipos de contratos
                                      </a>
                                  </li>
                                  <li>
                                      <a class="dropdown-item" href="#">
                                          <i class="fa-solid fa-envelope-open-dollar me-3 tl-icon"></i>
                                          Esquemas de pago
                                      </a>
                                  </li>
                              </ul>
                          </li>

                          
                          <li class="nav-item dropdown">
                              <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink1" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                  Panel de Calidad
                              </a>
                              <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="navbarDropdownMenuLink1">
                                  <li>
                                      <a class="dropdown-item" href="#">
                                          <i class="fa-solid fa-file-plus me-3"></i>
                                          Alta Incidencia
                                      </a>
                                  </li>

                                  <li>
                                      <a class="dropdown-item" href="#">
                                          <i class="fa-solid fa-user-graduate me-3"></i>
                                          Calificar
                                      </a>
                                  </li>

                                  <li>
                                      <hr class="dropdown-divider">
                                  </li>
                                  <li>
                                      <a class="dropdown-item" href="#">
                                          <i class="fa-solid fa-eye me-3"></i>
                                          Incidencias
                                      </a>
                                  </li>
                              </ul>
                          </li>
                      </ul>
                  </div>
              </div>
          </div>
      </nav>
  </header>

  <main>
    <?= $content ?? '' ?>
  </main>

  <footer class="bg-black copyright-bar mt-2">
    <div>
      <p class="mb-0"> 
        Copyright ©2025; Designed by <span class="fw-semibold">TM-SAKE</span>
      </p>
    </div>
  </footer>

  <!-- Critical JavaScript loaded first / JavaScript crítico cargado primero -->
  <script src="/assets/vendor/jquery/jquery-3.7.1.min.js?v=3.7.1"></script>
  <script src="/assets/vendor/bootstrap/js/bootstrap.bundle.min.js?v=5.3.7"></script>
  <!-- Non-critical JavaScript loaded asynchronously / JavaScript no crítico cargado asincrónicamente -->
  <script defer src="/assets/vendor/datatables/js/datatables.min.js?v=2.3.2"></script>
  <script src="/assets/vendor/sweetalert2/sweetalert2.all.min.js?v=11.22.2"></script>
  <script async src="https://kit.fontawesome.com/ad0553164e.js" crossorigin="anonymous"></script>

  <!-- Application JavaScript / JavaScript de la aplicación -->
  <script defer src="/assets/js/app.js"></script>

  <!-- Dynamic content / Contenido dinámico -->
  <?= flash_alert() ?>
  <?= flash_logout() ?>
  <?= flash_delete_employee() ?>

  <?php include __DIR__.'/../hr/employeeCreateModal.php'; ?>

  <!-- Load DataTables and session scripts only when needed / Cargar DataTables y scripts de sesión solo cuando se necesiten -->
  <script>
    // Lazy load session-specific scripts / Carga perezosa de scripts específicos de sesión
    document.addEventListener('DOMContentLoaded', function() {
      <?php if (strpos($_SERVER['REQUEST_URI'] ?? '', '/dashboard') !== false): ?>
        // Only load session scripts on dashboard / Solo cargar scripts de sesión en dashboard
        
      <?php endif; ?>
    });
  </script>

</body>
</html>