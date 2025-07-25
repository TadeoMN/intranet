<?php
namespace App\Models;

class UserRole extends Model {
    protected static string $table='user_roles';
    protected static string $primary='id_user_fk';
    protected static array  $fillable=['id_user_fk','id_role_fk'];
}