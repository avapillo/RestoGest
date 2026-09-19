<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mesas extends Model
{
    protected $table = 'mesas';
    protected $primaryKey = 'id_mesa';
    public $timestamps = false;
    protected $fillable = ['numero_mesa', 'fk_id_seccion'];

    public function seccion()
    {
        return $this->belongsTo(Seccion::class, 'fk_id_seccion', 'id_seccion');
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'fk_id_mesa', 'id_mesa');
    }

}
