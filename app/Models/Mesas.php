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

    public function pedidoActivo()
    {
        return $this->hasOne(Pedido::class, 'fk_id_mesa', 'id_mesa')
                    ->whereNull('fk_id_tipo_pago')
                    ->latest('fecha_pedido');
    }

}
