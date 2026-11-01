<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\RolUsuario;

class Usuarios extends Model
{
    use SoftDeletes;

    protected $table = 'usuarios';

    protected $fillable = ["nombre", "gmail", "contrasenia"];

    protected $hidden = ["contrasenia"];

    public $timestamps = false;

    public function roles()
    {
        // Relación: id_rol_usuario de la tabla usuarios apunta a 'id' de la tabla rol
        return $this->belongsToMany(RolUsuario::class, 'usuario_roles', 'fk_id_usuario', 'fk_id_rol');

    }
}
