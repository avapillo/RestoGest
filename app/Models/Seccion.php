<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seccion extends Model
{
    protected $table = "seccion";
    protected $primaryKey = "id_seccion";
    // Nombre de la tabla en la BD
    protected $table = 'seccion';

    protected $fillable = ['seccion'];

    // Desactivar timestamps automáticos (created_at / updated_at)
    public $timestamps = false;
    protected $fillable = ['seccion'];


    public function mesas()
    {
        return $this->hasMany(Mesas::class, 'fk_id_seccion', 'id_seccion');
    }

}
