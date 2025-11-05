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
        ['key' => '__sequence', 'label' => 'Núm. consecutivo'],
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
            foreach ($incidents as $index => $incident) {
                $cells = [];
                foreach (self::EXPORT_COLUMNS as $column) {
                    $cells[] = '<td>' . htmlspecialchars(
                        self::columnValue($incident, $column, $index),
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

    public static function buildPdf(array $incidents, array $filters = []): string
    {
        $now = new DateTimeImmutable('now', new DateTimeZone('America/Mexico_City'));
        $summary = self::filtersSummary($filters);
        $html = self::renderPdfHtml($incidents, $now, $summary);

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
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

        //format dateFrom and dateTo as d/m/Y
        $dateFrom = trim((string)($filters['dateFrom'] ?? ''));
        $dateTo = trim((string)($filters['dateTo'] ?? ''));
        if ($dateFrom !== '' && $dateTo !== '') {
            $parts[] = 'Periodo de ' . date('d/m/Y', strtotime($dateFrom)) . ' a ' . date('d/m/Y', strtotime($dateTo));
        } elseif ($dateFrom !== '') {
            $parts[] = 'Desde ' . date('d/m/Y', strtotime($dateFrom));
        } elseif ($dateTo !== '') {
            $parts[] = 'Hasta ' . date('d/m/Y', strtotime($dateTo));
        }

        // $dateFrom = trim((string)($filters['dateFrom'] ?? ''));
        // $dateTo = trim((string)($filters['dateTo'] ?? ''));
        // if ($dateFrom !== '' && $dateTo !== '') {
        //     $parts[] = 'Periodo ' . formatter_date($dateFrom) . ' - ' . formatter_date($dateTo);
        // } elseif ($dateFrom !== '') {
        //     $parts[] = 'Desde ' . formatter_date($dateFrom);
        // } elseif ($dateTo !== '') {
        //     $parts[] = 'Hasta ' . formatter_date($dateTo);
        // }

        $status = trim((string)($filters['status'] ?? ''));
        if ($status !== '') {
            $parts[] = 'Estatus=' . $status;
        }

        return empty($parts) ? null : implode(' | ', $parts);
    }

    private static function columnValue(array $incident, array $column, int $index): string
    {
        $key = $column['key'];
        if ($key === '__sequence') {
            return (string) ($index + 1);
        }
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
        $generated = $generatedAt->format('d/m/Y H:i:s') . ' hrs';
        $filtersText = trim((string)($summary ?? '')) === '' ? 'N/A' : htmlspecialchars($summary, ENT_QUOTES, 'UTF-8');

        $headerImage = self::headerImageDataUri();
        $headerImageHtml = $headerImage ? '<img src="' . $headerImage . '" alt="Logo">' : '';

        $incidentsCount = count($incidents);
        $pagesData = $incidentsCount === 0 ? [[['__empty' => true]]] : array_chunk($incidents, 2);
        $totalPages = count($pagesData);

        $sequence = 0;
        $body = '';

        foreach ($pagesData as $pageIndex => $chunk) {
            $pageNumber = $pageIndex + 1;
            
            $body .= '<div class="page">';
            $body .= self::renderPageHeader($headerImageHtml, $generated, $filtersText);
            $body .= '<div class="total-incidencias">Total de incidencias: ' . $incidentsCount . '</div>';

            if (isset($chunk[0]['__empty'])) {
                $body .= '<div class="empty">Sin incidencias registradas</div>';
            } else {
                foreach ($chunk as $incident) {
                    $body .= self::renderIncidentSheet($incident, $sequence++);
                }
            }

            $body .= self::renderPageFooter($pageNumber, $totalPages);
            $body .= '</div>';
        }

        return <<<HTML
<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <style>
      @page { margin: 44px 36px; }
      * { box-sizing: border-box; }
      body {
        font-family: 'DejaVu Sans', Arial, Helvetica, sans-serif;
        color: #111;
        font-size: 12px;
        margin: 0;
      }
      .page { page-break-after: always; }
      .page:last-child { page-break-after: auto; }
      .header-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        border: .6px solid #2c2c2cff;
      }
      .header-table th {
        border: .6px solid #2c2c2cff;
        vertical-align: middle;
        padding: 6px 8px;
      }
      .logo img { max-width: 90%; object-fit: contain; object-position: center; }
      .title { font-weight: 900; font-size: 20px; letter-spacing: .4px; text-align: center; }
      .meta { font-size: 11px; text-align: right; line-height: 1; }
      .meta p { margin: 0; }
      .sheet { margin-top: 10px; }
      .table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        border: .6px solid #2c2c2cff;
        margin-bottom: 10px;
      }
      .table td,
      .table th {
        border: .6px solid #2c2c2cff;
        vertical-align: middle;
        padding: 4px 8px;
        background-color: #eeeff1ff;
      }
      .table .label { font-weight: 100; display: block; text-transform: uppercase; font-size: 11px; }
      .table .value { margin-top: 1.5px; white-space: pre-line; font-weight: 100; font-size: 10px; }
      .col-3 th, .col-3 td { width: 33.33%; }
      .col-4 th, .col-4 td { width: 25%; }
      .col-2 th, .col-2 td { width: 50%; }
      .h-32 { height: 32px; }
      .h-60 { height: 60px; }
      .h-90 { height: 90px; }
      .footer {
        margin-top: 10px;
        text-align: right;
        font-size: 10px;
        font-weight: 600;
      }
      .total-incidencias {
        font-weight: 600;
        margin-top: 4px;
        font-size: 10px;
      }
      .empty {
        padding: 32px;
        text-align: center;
        font-style: italic;
        color: #6b7280;
        border: 1px dashed #cbd5f5;
        border-radius: 6px;
        margin-top: 24px;
      }
      .text-justify { text-align: justify; }
    </style>
  </head>
  <body>
    {$body}
  </body>
</html>
HTML;
    }

    private static function renderPageHeader(string $logoHtml, string $generated, string $filtersText): string
    {
        return '<table class="header-table col-3">'
            . '<tr class="h-32">'
            . '<th class="logo">' . $logoHtml . '</th>'
            . '<th class="title">REPORTE DE INCIDENCIAS</th>'
            . '<th class="meta"><p>Generado: ' . htmlspecialchars($generated, ENT_QUOTES, 'UTF-8') . '</p><p>Filtros aplicados:</p><p>' . $filtersText . '</p></th>'
            . '</tr>'
            . '</table>';
    }

    private static function renderPageFooter(int $pageNumber, int $totalPages): string
    {
        return '<div class="footer">Página ' . $pageNumber . ' de ' . $totalPages . '</div>';
    }

    private static function renderIncidentSheet(array $incident, int $index): string
    {
        $sequence = $index + 1;
        $code = self::value($incident, 'code_incident_type');
        $name = self::value($incident, 'name_incident_type');

        $reportedBy = self::value($incident, 'reporter_name');

        $rows = [];
        $rows[] = '<tr class="h-32">'
            . self::tableCell('Núm.:', (string) $sequence)
            . self::tableCell('ID Incidencia:', self::value($incident, 'id_incident'))
            . self::tableCell('Cod. Incidencia:', $code)
            . self::tableCell('Nombre Incidencia:', $name)
            . '</tr>';
        $rows[] = '<tr class="h-60 col-2">'
            . self::tableCell('Cod. Empleado:', self::value($incident, 'code_employee'), 2)
            . self::tableCell('Nombre Empleado:', self::value($incident, 'name_employee'), 2)
            . '</tr>';
        $rows[] = self::tableRowFull('Descripción Incidencia:', self::value($incident, 'description_incident_type'), 'h-90');
        $rows[] = self::tableRowFull('Acciones:', self::value($incident, 'action_incident_type'), 'h-90');
        $rows[] = self::tableRowFull('Observaciones:', self::value($incident, 'observation_incident'), 'h-90');
        $rows[] = '<tr class="h-60">'
            . self::tableCell('Gravedad:', self::value($incident, 'severity_incident_type'))
            . self::tableCell('OT:', self::value($incident, 'ot_incident'))
            . self::tableCell('Desperdicio:', self::value($incident, 'waste_incident'))
            . self::tableCell('Identificación:', self::value($incident, 'identification_incident'))
            . '</tr>';
        $rows[] = self::tableRowFull('Apelación:', self::value($incident, 'appeal_incident'), 'h-60');
        $rows[] = '<tr class="h-60 col-2">'
            . self::tableCell('Reportado por:', $reportedBy, 2)
            . self::tableCell('Fecha y hora de reporte:', self::valueDate($incident, 'reported_at'), 2)
            . '</tr>';

        return '<div class="sheet"><table class="table col-4">' . implode('', $rows) . '</table></div>';
    }

    private static function tableCell(string $label, string $value, int $colspan = 1): string
    {
        return '<th colspan="' . $colspan . '"><span class="label">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</span><div class="value">' . self::htmlValue($value) . '</div></th>';
    }

    private static function tableRowFull(string $label, string $value, string $rowClass): string
    {
        return '<tr class="' . $rowClass . '"><th colspan="4"><span class="label text-justify">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</span><div class="value text-justify">' . self::htmlValue($value) . '</div></th></tr>';
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

        return $dt->format('d/m/Y H:i:s') . ' hrs';
    }

    private static ?string $headerImageDataUri = null;

    private static function headerImageDataUri(): ?string
    {
        if (self::$headerImageDataUri !== null) {
            return self::$headerImageDataUri;
        }

        $path = realpath(__DIR__ . '/../../public/assets/images/TOPLABEL.png');
        if ($path && is_readable($path)) {
            $data = @file_get_contents($path);
            if ($data !== false) {
                self::$headerImageDataUri = 'data:image/png;base64,' . base64_encode($data);
                return self::$headerImageDataUri;
            }
        }

        self::$headerImageDataUri = null;
        return null;
    }
}
