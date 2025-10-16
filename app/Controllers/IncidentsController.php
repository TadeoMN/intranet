<?php

namespace App\Controllers;

use App\Models\Incident;
use App\Models\IncidentType;
use App\Models\Department;
use App\Models\Positions;
use App\Services\IncidentExportService;

use function view, redirect, flash;

class IncidentsController {

  private const ALLOWED_PAGE_SIZES = [5, 10, 20, 50, 100];
  private const ALLOWED_SORTS = [
    'id_incident',
    'employee.name_employee',
    'reported_at',
    'code_incident_type',
    'name_incident_type',
    'reporter_name'
  ];

  public function searchIncidents(): void {
    header('Content-Type: application/json; charset=utf-8');

    $q = isset($_GET['q']) ? trim((string)$_GET['q']) : (isset($_GET['term']) ? trim((string)$_GET['term']) : '');
    if (mb_strlen($q) < 2) {
      echo json_encode(['ok' => true, 'items' => []], JSON_UNESCAPED_UNICODE);
      return;
    }

    try {
      $results = IncidentType::searchIncidents($q);
      echo json_encode(['ok' => true, 'items' => $results], JSON_UNESCAPED_UNICODE);
    } catch (\PDOException $e) {
      if ($e->getCode() === '42S22') { // Column not found
        echo json_encode(['ok' => false, 'items' => []], JSON_UNESCAPED_UNICODE);
        flash('error', 'Búsqueda de tipos de incidente', 'Ocurrió un error al realizar la búsqueda. Intenta de nuevo.');
      } else {
        echo json_encode(['ok' => false, 'items' => []], JSON_UNESCAPED_UNICODE);
        flash('error', 'Búsqueda de tipos de incidente', 'Ocurrió un error inesperado: ' . $e->getMessage());
      }
    }
  }

  public function findIncidentById(int $id_incident): void {
    header('Content-Type: application/json; charset=utf-8');

    try {
      $incident = Incident::findById($id_incident);
      if ($incident) {
        echo json_encode(['ok' => true, 'item' => $incident], JSON_UNESCAPED_UNICODE);
      } else {
        echo json_encode(['ok' => false, 'item' => null], JSON_UNESCAPED_UNICODE);
        flash('error', 'Tipo de incidencia no encontrado', 'No se encontró el tipo de incidencia solicitado.');
      }
    } catch (\PDOException $e) {
      if ($e->getCode() === '42S22') { // Column not found
        echo json_encode(['ok' => false, 'item' => null], JSON_UNESCAPED_UNICODE);
        flash('error', 'Búsqueda de tipo de incidencia', 'Ocurrió un error al buscar el tipo de incidencia. Intenta de nuevo.');
      } else {
        echo json_encode(['ok' => false, 'item' => null], JSON_UNESCAPED_UNICODE);
        flash('error', 'Búsqueda de tipo de incidencia', 'Ocurrió un error inesperado: ' . $e->getMessage());
      }
    }
  }

  public function storeIncident() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $dataIncident = [
        'id_employee_fk' => $_POST['id_employee_modal'],
        'id_incident_type_fk' => $_POST['id_incident_modal'],
        'ot_incident' => trim(strtoupper($_POST['ot_incident_modal'])) === "" ? "N/A" : trim(strtoupper($_POST['ot_incident_modal'])),
        'waste_incident' => trim(strtoupper($_POST['waste_incident_modal'])) === "" ? "N/A" : trim(strtoupper($_POST['waste_incident_modal'])),
        'observation_incident' => trim(strtoupper($_POST['observation_incident_modal'])) === "" ? "SIN OBSERVACIONES" : trim(strtoupper($_POST['observation_incident_modal'])),
        'identification_incident' => $_POST['identification_incident_modal'],
        'reported_by' => $_POST['reported_by_modal']
      ];
    } else {
      flash('error', 'Error al crear incidencia', 'No se pudo crear la incidencia. Inténtalo de nuevo más tarde.');
      header('Location: ' . $_SERVER['HTTP_REFERER']);
      exit;
    }

    try {
      $idIncident = Incident::storeIncident($dataIncident);
      if ($idIncident) {
        flash('success', 'Incidencia creada', 'La incidencia ha sido creada exitosamente.');
      } else {
        flash('error', 'Error al crear incidencia', 'No se pudo crear la incidencia. Inténtalo de nuevo más tarde.');
      }
    } catch (\PDOException $e) {
      if ($e->getCode() === 'HY000') {
        flash('error', 'Error al crear incidencia', 'Debes iniciar sesión para crear una incidencia.');
      } else {
        flash('error', 'Error al crear incidencia', 'Ocurrió un error inesperado: ' . $e->getMessage());
      }
    }
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
  }

  public function listIncidents() {
    $filters = $this->extractFilters();

    $incidentData = Incident::filterPaginated(
      $filters['search'],
      $filters['dateFrom'],
      $filters['dateTo'],
      $filters['limit'],
      $filters['offset'],
      $filters['sort'],
      $filters['order'],
      $filters['status']
    );
    $incidents = $incidentData['incidents'];
    $total = $incidentData['total'];
    $totalPages = (int)ceil($total / $filters['limit']);

    $pagination = [
        'current_page' => $filters['page'],
        'total_pages' => $totalPages,
        'total_items' => $total,
        'limit' => $filters['limit'],
        'has_prev' => $filters['page'] > 1,
        'has_next' => $filters['page'] < $totalPages,
        'prev_page' => $filters['page'] > 1 ? $filters['page'] - 1 : null,
        'next_page' => $filters['page'] < $totalPages ? $filters['page'] + 1 : null,
        'search' => $filters['search'],
        'dateFrom' => $filters['dateFrom'],
        'dateTo' => $filters['dateTo'],
        'sort' => $filters['sort'],
        'order' => $filters['order'],
        'status' => $filters['status']
    ];

    $departments = Department::all();
    $positions = Positions::all();
    $positionsByDepartment = [];
    
    foreach ($positions as $position) {
        $positionsByDepartment[$position['id_department_fk']][] = [
            'id_position' => $position['id_position'],
            'name_position' => $position['name_position']
        ];
    }
    return view('incidents/incidentsList', compact('incidents', 'pagination', 'departments', 'positionsByDepartment'));
  }

  public function exportExcel(): void {
    $filters = $this->extractFilters();
    $incidents = Incident::filterForExport(
      $filters['search'],
      $filters['dateFrom'],
      $filters['dateTo'],
      $filters['sort'],
      $filters['order'],
      $filters['status']
    );

    $content = IncidentExportService::buildExcel($incidents, $filters);
    $filename = 'incidencias_' . date('Ymd_His') . '.xls';

    while (ob_get_level() > 0) {
      ob_end_clean();
    }

    header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-store, no-cache, must-revalidate');

    echo "\xEF\xBB\xBF"; // UTF-8 BOM so Excel recognises encoding
    echo $content;
    exit;
  }

  public function exportPdf(): void {
    $filters = $this->extractFilters();
    $incidents = Incident::filterForExport(
      $filters['search'],
      $filters['dateFrom'],
      $filters['dateTo'],
      $filters['sort'],
      $filters['order'],
      $filters['status']
    );

    $pdf = IncidentExportService::buildPdf($incidents, $filters);
    $filename = 'incidencias_' . date('Ymd_His') . '.pdf';

    while (ob_get_level() > 0) {
      ob_end_clean();
    }

    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-store, no-cache, must-revalidate');

    echo $pdf;
    exit;
  }

  private function extractFilters(): array {
    $limit = (int)($_GET['limit'] ?? 10);
    if (!in_array($limit, self::ALLOWED_PAGE_SIZES, true)) {
      $limit = 10;
    }

    $page = max(1, (int)($_GET['page'] ?? 1));
    $offset = ($page - 1) * $limit;

    $search = trim((string)($_GET['search'] ?? ''));
    $dateFrom = $_GET['dateFrom'] ?? '';
    $dateTo = $_GET['dateTo'] ?? '';

    $sort = $_GET['sort'] ?? 'id_incident';
    if (!in_array($sort, self::ALLOWED_SORTS, true)) {
      $sort = 'id_incident';
    }

    $order = strtolower((string)($_GET['order'] ?? 'desc')) === 'asc' ? 'asc' : 'desc';
    $status = $_GET['status'] ?? null;

    return [
      'limit' => $limit,
      'page' => $page,
      'offset' => $offset,
      'search' => $search,
      'dateFrom' => $dateFrom,
      'dateTo' => $dateTo,
      'sort' => $sort,
      'order' => $order,
      'status' => $status
    ];
  }
}
