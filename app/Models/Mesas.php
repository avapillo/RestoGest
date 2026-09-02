<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mesas extends Model
{
    protected $mesas = 'mesas';

    protected $fillable = ["id_mesa", "numero_mesa", "fk_id_seccion"];

    public $timestamps = false;

    public function seccion(){
        return $this->belongsTo(Seccion::class, 'fk_id_seccion', 'id_seccion');
    }

}
