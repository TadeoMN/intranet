<?php

namespace App\Controllers;

use App\Models\Incident;
use App\Models\IncidentType;

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

  public function storeIncident() {
    $data = [
      'id_employee_fk' => $_POST['id_employee_modal'],
      'id_incident_type_fk' => $_POST['id_incident_modal'],
      'reported_by' => $_POST['reported_by_modal'] ?? 1,
      'ot_incident' => $_POST['ot_incident_modal'],
      'waste_incident' => $_POST['waste_incident_modal'],
      'identification_incident' => $_POST['identification_incident_modal'],
      'observation_incident' => $_POST['observation_incident_modal']
    ];

    try {
      $idIncident = Incident::create($data);
      if ($idIncident) {
        flash('success', 'Incidencia creada', 'La incidencia ha sido creada exitosamente.');
      } else {
        flash('error', 'Error al crear incidencia', 'No se pudo crear la incidencia. Inténtalo de nuevo más tarde.');
      }
    } catch (\PDOException $e) {
      if ($e->errorInfo[1] === 1062) { // Duplicate entry
        flash('error', 'Error al crear incidencia', 'Ya existe una incidencia con los mismos datos.');
      } elseif ($e->errorInfo[1] === 1452) { // Foreign key constraint fails
        flash('error', 'Error al crear incidencia', 'El tipo de incidencia seleccionado no es válido o no existe.');
      } elseif ($e->errorInfo[1] === 1364) { // Incorrect datetime value
        flash('error', 'Error al crear incidencia', 'La fecha de la incidencia es incorrecta o está vacía.');
      } else {
        flash('error', 'Error al crear incidencia', 'Ocurrió un error inesperado: ' . $e->getMessage());
      }
    }

    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
  }
}