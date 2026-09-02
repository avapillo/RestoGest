<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seccion extends Model
{
    // Nombre de la tabla en la BD
    protected $table = 'seccion';

    protected $fillable = ['seccion'];

    // Desactivar timestamps automáticos (created_at / updated_at)
    public $timestamps = false;
}
