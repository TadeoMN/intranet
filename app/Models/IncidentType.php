<?php
namespace App\Models;

class IncidentType extends Model {
    protected $table = 'incident_types';
    protected $primaryKey = 'id_incident_type';

    protected $fillable = [
      'code_incident_type',
      'name_incident_type',
      'description_incident_type',
      'severity_incident_type',
      'action_incident_type'
    ];
}