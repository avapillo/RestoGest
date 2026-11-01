<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Seccion extends Model
{
    use SoftDeletes;

    //protected $table = "seccion";
    protected $primaryKey = "id_seccion";
    // Nombre de la tabla en la BD
    protected $table = 'seccion';

    protected $fillable = ['seccion'];

    // Desactivar timestamps automáticos (created_at / updated_at)
    public $timestamps = false;

    public function mesas()
    {
        return $this->hasMany(Mesas::class, 'fk_id_seccion', 'id_seccion');
    }

}
