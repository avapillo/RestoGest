<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RolUsuario extends Model
{
    protected $table = 'rol_usuario';

    protected $primaryKey = 'id_rol';

    public $timestamps = false;
}
