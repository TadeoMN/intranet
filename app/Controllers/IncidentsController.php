<?php

namespace App\Controllers;

use App\Models\Incident;
use App\Models\IncidentType;
use App\Models\Department;
use App\Models\Positions;

use function view, redirect, flash;

class IncidentsController {

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
    $allowedPages = [5,10,20,50,100]; // Example allowed pages
    $limit = (int)($_GET['limit'] ?? 10);
    $limit = in_array($limit, $allowedPages) ? $limit : 10;

    $page = max(1, (int) ($_GET['page'] ?? 1));
    $offset = ($page - 1) * $limit;

    $search = trim($_GET['search'] ?? '');
    $dateFrom = $_GET['dateFrom'] ?? '';
    $dateTo = $_GET['dateTo'] ?? '';

    $allowedSorts = [
        'id_incident', 'employee.name_employee', 'reported_at', 'code_incident_type', 'name_incident_type', 'reporter_name'
    ];

    $sort = $_GET['sort'] ?? 'id_incident';

    $sort = in_array($sort, $allowedSorts, true) ? $sort : 'id_incident';
    $order = (isset($_GET['order']) && strtolower($_GET['order']) === 'asc') ? 'asc' : 'desc';

    $status = $_GET['status'] ?? null;

    $incidentData = Incident::filterPaginated($search, $dateFrom, $dateTo, $limit, $offset, $sort, $order, $status);
    $incidents = $incidentData['incidents'];
    $total = $incidentData['total'];
    $totalPages = ceil($total / $limit);

    $pagination = [
        'current_page' => $page,
        'total_pages' => $totalPages,
        'total_items' => $total,
        'limit' => $limit,
        'has_prev' => $page > 1,
        'has_next' => $page < $totalPages,
        'prev_page' => $page > 1 ? $page - 1 : null,
        'next_page' => $page < $totalPages ? $page + 1 : null,
        'search' => $search,
        'dateFrom' => $dateFrom,
        'dateTo' => $dateTo,
        'sort' => $sort,
        'order' => strtolower($order),
        'status' => $status
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
}