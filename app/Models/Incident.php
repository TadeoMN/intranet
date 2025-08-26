<?php
namespace App\Models;

class Incident extends Model {
  protected static string $table = 'incident';
  protected static string $primaryKey = 'id_incident';
  protected static array $fillable = [
    'id_employee_fk', 'id_incident_type_fk', 'observation_incident',
    'appeal_incident', 'ot_incident', 'waste_incident',
    'identification_incident', 'reported_by', 'reported_at'
  ];

  public static function storeIncident(array $data): bool {
    $filteredData = array_intersect_key($data, array_flip(self::$fillable));
    return self::create($filteredData) > 0;
  }
}