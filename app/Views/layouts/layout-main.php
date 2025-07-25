<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= $title ?? 'Intranet' ?></title>

  <!-- Performance optimizations / Optimizaciones de rendimiento -->
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="description" content="Sistema de Intranet Corporativa">
  
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

  <?= $content ?>

  <!-- Critical JavaScript loaded first / JavaScript crítico cargado primero -->
  <script src="/assets/vendor/jquery/jquery-3.7.1.min.js?v=3.7.1"></script>
  <script src="/assets/vendor/bootstrap/js/bootstrap.bundle.min.js?v=5.3.7"></script>
  
  <!-- Non-critical JavaScript loaded asynchronously / JavaScript no crítico cargado asincrónicamente -->
  <script defer src="/assets/vendor/datatables/js/datatables.min.js?v=2.3.2"></script>
  <script defer src="/assets/vendor/sweetalert2/sweetalert2.all.min.js?v=11.22.2"></script>
  <script async src="https://kit.fontawesome.com/ad0553164e.js" crossorigin="anonymous"></script>
  
  <!-- Optimized inline JavaScript / JavaScript inline optimizado -->
  <script>
    // Initialize tooltips when DOM is ready / Inicializar tooltips cuando el DOM esté listo
    document.addEventListener('DOMContentLoaded', function() {
      const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
      const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
    });
  </script>
  
  <!-- Application JavaScript / JavaScript de la aplicación -->
  <script defer src="/assets/js/app.js"></script>

  <!-- Dynamic content / Contenido dinámico -->
  <?= flash_alert() ?>
  <?= flash_logout() ?>
  
  <!-- Load DataTables and session scripts only when needed / Cargar DataTables y scripts de sesión solo cuando se necesiten -->
  <script>
    // Lazy load session-specific scripts / Carga perezosa de scripts específicos de sesión
    document.addEventListener('DOMContentLoaded', function() {
      <?php if (strpos($_SERVER['REQUEST_URI'] ?? '', '/dashboard') !== false): ?>
        // Only load session scripts on dashboard / Solo cargar scripts de sesión en dashboard
        <?= js_session_tables() ?>
        <?= js_session_close() ?>
      <?php endif; ?>
    });
  </script>
  
</body>
</html>