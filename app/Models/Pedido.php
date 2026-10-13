<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    protected $table = 'pedidos';
    protected $primaryKey = 'id_pedido';
    public $timestamps = false;

    protected $fillable = [
        'monto_total',
        'hora_pedido',
        'fk_id_mesa',
        'fk_id_tipo_pago',
        'es_para_llevar'
    ];

    public function mesa()
    {
        return $this->belongsTo(Mesas::class, 'fk_id_mesa', 'id_mesa');
    }

    public function detalles()
    {
        return $this->hasMany(DetallePedido::class, 'fk_id_pedido', 'id_pedido');
    }

    public function scopeSinCobrar($query)
    {
        return $query->whereNotExists(function ($subquery) {
            $subquery->selectRaw('1')
                ->from('registro_pago')
                ->whereColumn('registro_pago.fk_id_pedido', 'pedidos.id_pedido');
        });
    }
}
