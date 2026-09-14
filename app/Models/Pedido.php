<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
   use HasFactory;

    protected $table = 'pedidos';
    protected $primaryKey = 'id_pedido';
    public $timestamps = false;

    protected $fillable = [
        'monto_total',
        'fecha_pedido',
        'fk_id_mesa',
        'fk_id_tipo_pago',
        'es_para_llevar',
    ];

    public function detalles()
    {
        return $this->hasMany(DetallePedido::class, 'fk_id_pedido', 'id_pedido');
    }

    public function mesa()
    {
        return $this->belongsTo(Mesas::class, 'fk_id_mesa', 'id_mesa');
    }
}
