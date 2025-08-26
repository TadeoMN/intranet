<?php
namespace App\Models;

class IncidentType extends Model {
    protected static string $table = 'incident_type';
    protected static string $primaryKey = 'id_incident_type';
    protected static array $fillable = [
      'code_incident_type', 'name_incident_type',
      'description_incident_type', 'severity_incident_type',
      'action_incident_type'
    ];

    public static function searchIncidents(string $query): array {
        $pdo = \Core\Database::pdo();

        $query = trim((string)$query);
        if ($query === '') return [];
        $search = '%' . str_replace(['%', '_'], ['\\%', '\\_'], mb_strtolower($query, 'UTF-8')) . '%';
        $bindings = [];

        $sql =
            '   SELECT *
                FROM incident_type
                WHERE LOWER(name_incident_type) LIKE :search
                    OR code_incident_type LIKE :search2
                ORDER BY code_incident_type
                LIMIT 10';
        $bindings = [
            ':search' => $search,
            ':search2' => $search
        ];
        $st = $pdo->prepare($sql);
        $st->execute($bindings);
        return $st->fetchAll(\PDO::FETCH_ASSOC);
    }
}