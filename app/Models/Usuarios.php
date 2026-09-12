<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\RolUsuario;

class Usuarios extends Model
{
    protected $table = 'usuarios';

    protected $fillable = ["nombre", "contrasenia", "id_rol_usuario"];

    public $timestamps = false;

    public function roles()
    {
        // Relación: id_rol_usuario de la tabla usuarios apunta a 'id' de la tabla rol
        return $this->belongsTo(RolUsuario::class, 'id_rol_usuario', 'id_rol');

    }
}
