<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seccion extends Model
{
    protected $table = "seccion";
    protected $primaryKey = "id_seccion";
    public $timestamps = false;
    protected $fillable = ['seccion'];


    public function mesas()
    {
        return $this->hasMany(Mesas::class, 'fk_id_seccion', 'id_seccion');
    }

}
