<?php
namespace App\Models;

class RolePermission extends Model {
    protected static string $table='role_permissions';
    protected static string $primary='role_id_fk';
    protected static array  $fillable=['role_id_fk','permission_id_fk'];
}