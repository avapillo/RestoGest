<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
   use HasFactory;

    protected $table = 'pedidos';
    protected $fillable = [
        'hora_pedido',
        'es_para_llevar',
        'numero_mesa',
        'item',
        'cantidad',
        'precio_unitario',
        'total_pedido'
    ];
    protected $primaryKey = 'id_pedido';
    public $timestamps = false;

    public function mesa()
    {
        return $this->belongsTo(Mesa::class, 'fk_id_mesa', 'id_mesa');
    }

      public function detalles()
    {
        return $this->hasMany(DetallePedido::class, 'fk_id_pedido', 'id_pedido');
    }

}
