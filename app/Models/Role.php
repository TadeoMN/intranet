<?php
namespace App\Models;

class Role extends Model {
    protected static string $table='roles';
    protected static string $primary='id_role';
    protected static array  $fillable=['name_role'];
}