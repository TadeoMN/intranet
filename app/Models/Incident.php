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

  private const BASE_FIELDS = 'incident.*, employee.*, incident_type.*, users.*, reporter.name_employee AS "reporter_name"';
  private const ALLOWED_SORTS = [
    'id_incident',
    'employee.name_employee',
    'reported_at',
    'code_incident_type',
    'name_incident_type',
    'reporter_name'
  ];

  public static function storeIncident(array $data): bool {
    $pdo = \Core\Database::pdo();
    $sql =
        '   INSERT INTO ' . self::$table . ' (' . implode(', ', array_keys($data)) . ')
            VALUES (' . implode(', ', array_fill(0, count($data), '?')) . ')';
    $stmt = $pdo->prepare($sql);
    return $stmt->execute(array_values($data));
  }

  public static function allIncidents(): array {
    $pdo = \Core\Database::pdo();

    $sql = 
        '   SELECT incident.*, employee.*, incident_type.*, users.*, reporter.name_employee AS "reporter_name"
            FROM incident
            INNER JOIN employee ON incident.id_employee_fk = employee.id_employee
            INNER JOIN incident_type ON incident.id_incident_type_fk = incident_type.id_incident_type
            INNER JOIN users ON incident.reported_by = users.id_user
            INNER JOIN employee AS reporter ON users.id_employee_fk = reporter.id_employee
            ORDER BY incident.reported_at DESC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
  }

  public static function filterPaginated(?string $search, ?string $dateFrom, ?string $dateTo, int $limit, int $offset, string $sort, string $order, ?string $status): array {
    $pdo = \Core\Database::pdo();

    $filters = self::buildFilters($search, $dateFrom, $dateTo, $status);
    $orderBy = self::resolveOrder($sort, $order);

    $sqlBase = self::baseFromClause() . ' WHERE ' . $filters['where'];

    $countStmt = $pdo->prepare('SELECT COUNT(*) AS total' . $sqlBase);
    $countStmt->execute($filters['bindings']);
    $total = (int)$countStmt->fetchColumn();

    $sql = 'SELECT ' . self::BASE_FIELDS . $sqlBase . ' ORDER BY ' . $orderBy . ' LIMIT :limit OFFSET :offset';

    $stmt = $pdo->prepare($sql);

    foreach ($filters['bindings'] as $key => $value) {
      $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);

    $stmt->execute();
    $incidents = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    return ['total' => $total, 'incidents' => $incidents];
  }

  public static function filterForExport(?string $search, ?string $dateFrom, ?string $dateTo, string $sort, string $order, ?string $status): array {
    $pdo = \Core\Database::pdo();
    $filters = self::buildFilters($search, $dateFrom, $dateTo, $status);
    $orderBy = self::resolveOrder($sort, $order);

    $sql = 'SELECT ' . self::BASE_FIELDS . self::baseFromClause() . ' WHERE ' . $filters['where'] . ' ORDER BY ' . $orderBy;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($filters['bindings']);

    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
  }

  public static function findById(int $id_incident): ?array {
    $pdo = \Core\Database::pdo();

    $sql = 
        '   SELECT incident.*, employee.*, incident_type.*, users.*, reporter.name_employee AS "reporter_name"
            FROM incident
            INNER JOIN employee ON incident.id_employee_fk = employee.id_employee
            INNER JOIN incident_type ON incident.id_incident_type_fk = incident_type.id_incident_type
            INNER JOIN users ON incident.reported_by = users.id_user
            INNER JOIN employee AS reporter ON users.id_employee_fk = reporter.id_employee
            WHERE incident.id_incident = :id_incident';

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id_incident' => $id_incident]);
    $incident = $stmt->fetch(\PDO::FETCH_ASSOC);
    return $incident ?: null;
  }

  private static function baseFromClause(): string {
    return ' FROM incident
                 INNER JOIN employee ON incident.id_employee_fk = employee.id_employee
                 INNER JOIN incident_type ON incident.id_incident_type_fk = incident_type.id_incident_type
                 INNER JOIN users ON incident.reported_by = users.id_user
                 INNER JOIN employee AS reporter ON users.id_employee_fk = reporter.id_employee';
  }

  private static function buildFilters(?string $search, ?string $dateFrom, ?string $dateTo, ?string $status): array {
    $where = [];
    $bindings = [];

    $search = trim((string)$search);
    if ($search !== '') {
      $likeSearch = '%' . mb_strtoupper($search, 'UTF-8') . '%';
      $where[] = '(
        UPPER(employee.name_employee) LIKE :search1
        OR UPPER(incident_type.code_incident_type) LIKE :search2
        OR UPPER(incident_type.name_incident_type) LIKE :search3
        OR UPPER(reporter.name_employee) LIKE :search4
      )';
      $bindings += [
        ':search1' => $likeSearch,
        ':search2' => $likeSearch,
        ':search3' => $likeSearch,
        ':search4' => $likeSearch,
      ];
    }

    $dateFrom = $dateFrom === '' ? null : $dateFrom;
    $dateTo = $dateTo === '' ? null : $dateTo;

    if ($dateFrom) {
      $bindings[':dateFrom'] = $dateFrom . ' 00:00:00';
      $where[] = 'incident.reported_at >= :dateFrom';
    }
    if ($dateTo) {
      $bindings[':dateTo'] = $dateTo . ' 23:59:59';
      $where[] = 'incident.reported_at <= :dateTo';
    }

    return [
      'where' => count($where) ? implode(' AND ', $where) : '1=1',
      'bindings' => $bindings
    ];
  }

  private static function resolveOrder(string $sort, string $order): string {
    $orderBy = in_array($sort, self::ALLOWED_SORTS, true) ? $sort : 'id_incident';
    $orderDirection = strtolower($order) === 'asc' ? 'ASC' : 'DESC';
    return $orderBy . ' ' . $orderDirection;
  }
}
