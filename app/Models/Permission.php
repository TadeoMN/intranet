<?php
namespace App\Models;

class Permission extends Model {
    protected static string $table='permissions';
    protected static string $primary='id_permission';
    protected static array  $fillable=['code_permission','description_permission'];
}
