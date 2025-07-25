<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= $title ?? 'Intranet' ?></title>

  <link rel="stylesheet" href="/assets/vendor/bootstrap/css/bootstrap.min.css?v=5.3.7">
  <link rel="stylesheet" href="/assets/vendor/datatables/css/datatables.min.css?v=2.3.2">
  <link rel="stylesheet" href="/assets/vendor/sweetalert2/sweetalert2.min.css?v=11.22.2">
  <link rel="stylesheet" href="/assets/css/app.css">
</head>

<body class="background-login">

  <?= $content ?>

  <script src="/assets/vendor/jquery/jquery-3.7.1.min.js?v=3.7.1"></script>
  <script src="/assets/vendor/bootstrap/js/bootstrap.bundle.min.js?v=5.3.7"></script>
  <script src="/assets/vendor/datatables/js/datatables.min.js?v=2.3.2"></script>
  <script src="/assets/vendor/sweetalert2/sweetalert2.all.min.js?v=11.22.2"></script>
  <script src="/assets/js/app.js"></script>

  <?= flash_alert() ?>

</body>
</html>