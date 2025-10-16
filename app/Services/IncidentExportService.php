<?php

namespace App\Services;

use DateTimeImmutable;
use DateTimeZone;
use Dompdf\Dompdf;
use Dompdf\Options;

class IncidentExportService
{
    private const PDF_PAGE_FORMAT = 'letter';

    private const EXPORT_COLUMNS = [
        ['key' => 'id_incident', 'label' => 'ID Incidencia'],
        ['key' => 'id_employee_fk', 'label' => 'ID Empleado'],
        ['key' => 'name_employee', 'label' => 'Empleado'],
        ['key' => 'id_incident_type_fk', 'label' => 'ID Tipo'],
        ['key' => 'code_incident_type', 'label' => 'Código Tipo'],
        ['key' => 'name_incident_type', 'label' => 'Nombre Tipo'],
        ['key' => 'description_incident_type', 'label' => 'Descripción Tipo'],
        ['key' => 'severity_incident_type', 'label' => 'Severidad'],
        ['key' => 'action_incident_type', 'label' => 'Acción Tipo'],
        ['key' => 'ot_incident', 'label' => 'OT Incidente'],
        ['key' => 'waste_incident', 'label' => 'Desperdicio'],
        ['key' => 'observation_incident', 'label' => 'Observación'],
        ['key' => 'appeal_incident', 'label' => 'Apelación'],
        ['key' => 'identification_incident', 'label' => 'Identificación'],
        ['key' => 'reported_by', 'label' => 'ID Reporta'],
        ['key' => 'reporter_name', 'label' => 'Reportado por'],
        ['key' => 'reported_at', 'label' => 'Fecha reporte', 'formatter' => 'dateTime'],
    ];

    /**
     * Genera una tabla HTML que Excel puede interpretar conservando los filtros aplicados.
     */
    public static function buildExcel(array $incidents, array $filters = []): string
    {
        $now = new DateTimeImmutable('now', new DateTimeZone('America/Mexico_City'));
        $columnCount = count(self::EXPORT_COLUMNS);

        $lines = [];
        $lines[] = '<table border="1" cellspacing="0" cellpadding="4">';
        $lines[] = '<thead>';
        $lines[] = '<tr><th colspan="' . $columnCount . '">Reporte de incidencias</th></tr>';
        $lines[] = '<tr><th colspan="' . $columnCount . '">Generado: ' . htmlspecialchars($now->format('d/m/Y H:i:s')) . ' hrs</th></tr>';

        if ($summary = self::filtersSummary($filters)) {
            $lines[] = '<tr><th colspan="' . $columnCount . '">Filtros aplicados: ' . htmlspecialchars($summary) . '</th></tr>';
        }

        $headerCells = array_map(
            fn(array $column) => '<th>' . htmlspecialchars($column['label']) . '</th>',
            self::EXPORT_COLUMNS
        );
        $lines[] = '<tr>' . implode('', $headerCells) . '</tr>';
        $lines[] = '</thead>';
        $lines[] = '<tbody>';

        if (empty($incidents)) {
            $lines[] = '<tr><td colspan="' . $columnCount . '" style="text-align:center;">Sin incidencias registradas</td></tr>';
        } else {
            foreach ($incidents as $incident) {
                $cells = [];
                foreach (self::EXPORT_COLUMNS as $column) {
                    $cells[] = '<td>' . htmlspecialchars(
                        self::columnValue($incident, $column),
                        ENT_QUOTES,
                        'UTF-8'
                    ) . '</td>';
                }
                $lines[] = '<tr>' . implode('', $cells) . '</tr>';
            }
        }

        $lines[] = '</tbody>';
        $lines[] = '<tfoot>';
        $lines[] = '<tr><td colspan="' . $columnCount . '">Total de incidencias: ' . count($incidents) . '</td></tr>';
        $lines[] = '</tfoot>';
        $lines[] = '</table>';

        return implode("\n", $lines);
    }

    /**
     * Genera un PDF usando Dompdf con un formato tabular más legible.
     */
    public static function buildPdf(array $incidents, array $filters = []): string
    {
        $now = new DateTimeImmutable('now', new DateTimeZone('America/Mexico_City'));
        $summary = self::filtersSummary($filters);

        $html = self::renderPdfHtml($incidents, $now, $summary);

        $options = new Options();
        $options->set('defaultFont', 'Helvetica');
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper(self::PDF_PAGE_FORMAT, 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }

    private static function filtersSummary(array $filters): ?string
    {
        $parts = [];

        $search = trim((string)($filters['search'] ?? ''));
        if ($search !== '') {
            $parts[] = 'Búsqueda="' . $search . '"';
        }

        $dateFrom = trim((string)($filters['dateFrom'] ?? ''));
        $dateTo = trim((string)($filters['dateTo'] ?? ''));
        if ($dateFrom !== '' && $dateTo !== '') {
            $parts[] = 'Periodo ' . $dateFrom . ' a ' . $dateTo;
        } elseif ($dateFrom !== '') {
            $parts[] = 'Desde ' . $dateFrom;
        } elseif ($dateTo !== '') {
            $parts[] = 'Hasta ' . $dateTo;
        }

        $status = trim((string)($filters['status'] ?? ''));
        if ($status !== '') {
            $parts[] = 'Estatus=' . $status;
        }

        return empty($parts) ? null : implode(' | ', $parts);
    }

    private static function columnValue(array $incident, array $column): string
    {
        $key = $column['key'];
        $formatter = $column['formatter'] ?? null;

        if ($formatter === 'dateTime') {
            return self::formatDateTime($incident[$key] ?? null);
        }

        return self::sanitizeValue($incident[$key] ?? null);
    }

    private static function sanitizeValue($value): string
    {
        if ($value === null) {
            return 'N/A';
        }

        if (is_bool($value)) {
            return $value ? 'SI' : 'NO';
        }

        $string = trim((string)$value);
        if ($string === '') {
            return 'N/A';
        }

        return self::singleLine($string);
    }

    private static function value(array $incident, string $key): string
    {
        return self::sanitizeValue($incident[$key] ?? null);
    }

    private static function valueDate(array $incident, string $key): string
    {
        return self::formatDateTime($incident[$key] ?? null);
    }

    private static function renderPdfHtml(array $incidents, DateTimeImmutable $generatedAt, ?string $summary): string
    {
        $title = 'Reporte de incidencias';
        $generated = $generatedAt->format('d/m/Y H:i:s') . ' hrs';
        $total = count($incidents);

        $incidentBlocks = '';

        if (empty($incidents)) {
            $incidentBlocks = '<div class="empty">Sin incidencias registradas</div>';
        } else {
            foreach ($incidents as $incident) {
                $rows = self::incidentRows($incident);

                $rowsHtml = '';
                foreach ($rows as $label => $value) {
                    $rowsHtml .= '<tr>'
                        . '<td class="label">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</td>'
                        . '<td>' . self::htmlValue($value) . '</td>'
                        . '</tr>';
                }

                $incidentBlocks .= '<section class="incident">'
                    . '<h2>Incidencia #' . htmlspecialchars(self::value($incident, 'id_incident'), ENT_QUOTES, 'UTF-8') . '</h2>'
                    . '<table class="grid"><tbody>' . $rowsHtml . '</tbody></table>'
                    . '</section>';
            }
        }

        // Resumen de filtros aplicados. Si no hay, no muestra mensaje de filtros no aplicados.
        if ($summary !== null) {
            $summaryHtml = $summary ? '<p class="meta">Filtros: ' . htmlspecialchars($summary, ENT_QUOTES, 'UTF-8') . '</p>' : '';
        }
        else {
            $summaryHtml = '<p class="meta">Sin filtros aplicados</p>';
        }

        return <<<HTML
<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <style>
      * { box-sizing: border-box; }
      body {
        font-family: 'Helvetica', Arial, sans-serif;
        font-size: 12px;
        color: #1f2933;
        margin: 0;
        padding: 24px;
        line-height: 1.45;
      }
      h1 {
        font-size: 20px;
        margin: 0 0 4px;
        color: #111827;
      }
      h2 {
        font-size: 14px;
        margin: 0 0 8px;
        color: #111827;
      }
      .meta {
        color: #4b5563;
        margin: 2px 0;
      }
      .summary {
        margin-bottom: 18px;
      }
      .incident {
        margin-bottom: 18px;
        page-break-inside: avoid;
      }
      .grid {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid #d1d5db;
      }
      .grid td {
        padding: 6px 8px;
        border: 1px solid #d1d5db;
        vertical-align: top;
      }
      .grid td.label {
        width: 32%;
        background: #f3f4f6;
        font-weight: 600;
        color: #111827;
      }
      .empty {
        padding: 32px;
        text-align: center;
        font-style: italic;
        color: #6b7280;
        border: 1px dashed #cbd5f5;
        border-radius: 6px;
      }
      .totals {
        margin-top: 24px;
        font-weight: 600;
        color: #111827;
      }
    </style>
  </head>
  <body>
    <header class="summary">
      <h1>{$title}</h1>
      <p class="meta">Generado: {$generated}</p>
      {$summaryHtml}
    </header>
    {$incidentBlocks}
    <p class="totals">Total de incidencias: {$total}</p>
  </body>
</html>
HTML;
    }

    private static function incidentRows(array $incident): array
    {
        return [
            'ID incidencia' => self::value($incident, 'id_incident'),
            'ID empleado' => self::value($incident, 'id_employee_fk'),
            'Empleado' => self::value($incident, 'name_employee'),
            'Tipo ID' => self::value($incident, 'id_incident_type_fk'),
            'Código tipo' => self::value($incident, 'code_incident_type'),
            'Nombre tipo' => self::value($incident, 'name_incident_type'),
            'Descripción tipo' => self::value($incident, 'description_incident_type'),
            'Severidad' => self::value($incident, 'severity_incident_type'),
            'Acción tipo' => self::value($incident, 'action_incident_type'),
            'OT' => self::value($incident, 'ot_incident'),
            'Desperdicio' => self::value($incident, 'waste_incident'),
            'Identificación' => self::value($incident, 'identification_incident'),
            'Observación' => self::value($incident, 'observation_incident'),
            'Apelación' => self::value($incident, 'appeal_incident'),
            'Reportado por (ID)' => self::value($incident, 'reported_by'),
            'Reportado por' => self::value($incident, 'reporter_name'),
            'Fecha' => self::valueDate($incident, 'reported_at'),
        ];
    }

    private static function htmlValue(string $value): string
    {
        return nl2br(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
    }

    private static function singleLine(?string $value): string
    {
        $normalized = trim((string)$value);
        return preg_replace('/\s+/', ' ', $normalized) ?? '';
    }

    private static function formatDateTime($value): string
    {
        if ($value === null) {
            return 'N/A';
        }

        $raw = trim((string)$value);
        if ($raw === '' || $raw === '0000-00-00' || $raw === '0000-00-00 00:00:00') {
            return 'N/A';
        }

        try {
            $dt = new DateTimeImmutable($raw);
        } catch (\Exception $e) {
            return self::sanitizeValue($value);
        }

        return $dt->format('d/m/Y H:i');
    }
}