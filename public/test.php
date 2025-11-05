<?php
use Dompdf\Dompdf;
use Dompdf\Options;

require __DIR__ . '/../vendor/autoload.php';

$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('isHtml5ParserEnabled', true);
$options->set('defaultFont', 'DejaVu Sans');

$dompdf = new Dompdf($options);
$dompdf->setPaper('A4', 'portrait');

/* ======== HTML + CSS ======== */
$html = <<<HTML
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<style>
@page{ margin: 40px 32px 40px 32px; }
*{ box-sizing:border-box; }
body{ font-family: DejaVu Sans, Arial, Helvetica, sans-serif; color:#111; font-size:12px; }

.logo img{
  max-width:100%; object-fit:contain; object-position:center;
}

.title{
  font-weight:900; font-size:24px; letter-spacing:.4px; text-align:center;vertical-align:middle;
}
.meta{
  font-size:12px; text-align:right;
}
.meta p{ display:block; margin:0; line-height:1.2; }

.sheet { margin-top:18px; }

/* Tabla del formato */
.table{
  width:100%; border-collapse:collapse; table-layout:fixed; border: .6px solid #000; margin-bottom:18px;
}
.table td, .table th{ border:.6px solid #000; vertical-align:middle; padding:6px 8px; }
.table .label{ font-weight:300; }
.table .value{ margin-top:4px; white-space:pre-line; }

/* Grid de 3 columnas */
.col-3 th, .col-3 td { width:33.33%; }

/* Grid de 4 columnas */
.col-4 th, .col-4 td { width:25%; }

/* Grid de 2 columnas */
.col-2 th, .col-2 td { width:50%; }

/* Alturas aproximadas por fila */
.h-32{ height:32px; }
.h-60{ height:60px; }
.h-90{ height:90px; }

/* Paginación: dos incidencias por página o las que entren en una página */
</style>
</head>
<body>

<div>
  <!-- Encabezado -->
  <div>
    <table class="table col-3">
      <tr class="h-32">
        <th class="logo">
          <div><img src="/assets/images/TOPLABEL.png" alt="Logo"></div>
        </th>
        <th class="title">
          <div>REPORTE DE INCIDENCIAS</div>
        </th>
        <th class="meta">
          <div>
            <p>Generado:</p> <p> 16/10/2025 09:24:04 hrs</p>
            <p>Filtros aplicados:</p> <p>N/A</p>
          </div>
        </th>
      </tr>
    </table>
  </div>

  <!-- Hoja de incidencia -->
  <div class="sheet">
    <table class="table col-4">
      <tr class="h-32">
        <th><span class="label">Núm. Consecutivo Incidencia:</span><div class="value">{{CONSECUTIVO}}</div></th>
        <th><span class="label">ID Nueva Incidencia:</span><div class="value">{{ID_INCIDENTE}}</div></th>
        <th><span class="label">Cod. Tipo Incidencia:</span><div class="value">{{COD_TIPO}}</div></th>
        <th><span class="label">Nombre Tipo Incidencia:</span><div class="value">{{NOMBRE_TIPO}}</div></th>
      </tr>

      <tr class="h-90">
        <th colspan="4">
          <span class="label">Descripción Tipo Incidencia:</span>
          <div class="value">{{DESCRIPCION_TIPO}}</div>
        </th>
      </tr>

      <tr class="h-90">
        <th colspan="4">
          <span class="label">Acciones:</span>
          <div class="value">{{ACCIONES}}</div>
        </th>
      </tr>

      <tr class="h-60">
        <th><span class="label">Severidad:</span><div class="value">{{SEVERIDAD}}</div></th>
        <th><span class="label">OT:</span><div class="value">{{OT}}</div></th>
        <th><span class="label">Desperdicio:</span><div class="value">{{DESPERDICIO}}</div></th>
        <th><span class="label">Identificación:</span><div class="value">{{IDENTIFICACION}}</div></th>
      </tr>

      <tr class="h-90">
        <th colspan="4">
          <span class="label">Observaciones:</span>
          <div class="value">{{OBSERVACIONES}}</div>
        </th>
      </tr>

      <tr class="h-60">
        <th colspan="4">
          <span class="label">Apelación:</span>
          <div class="value">{{APELACION}}</div>
        </th>
      </tr>

      <tr class="h-60 col-2">
        <th colspan="2"><span class="label">Reportado por:</span><div class="value">{{REPORTADO_POR}}</div></th>
        <th colspan="2"><span class="label">Fecha y hora de reporte:</span><div class="value">{{FECHA_REPORTE}}</div></th>
      </tr>
    </table>
  </div>

</div>
</body>
</html>
HTML;

$dompdf->loadHtml($html,'UTF-8');
$dompdf->render();

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="formato_incidencia_demo.pdf"');
echo $dompdf->output();