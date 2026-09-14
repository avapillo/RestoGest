<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    use HasFactory;

    protected $table = 'pedidos';
    protected $primaryKey = 'id_pedido';

    protected $fillable = [
        'monto_total',
        'fecha_pedido',
        'fk_id_mesa',
        'fk_id_tipo_pago',
        'es_para_llevar',
    ];

    // Relación: Un Pedido tiene muchos detalles
    public function detalles()
    {
        return $this->hasMany(DetallePedido::class, 'fk_id_pedido', 'id_pedido');
    }

    // Relación: Un Pedido pertenece a una Mesa
    public function mesa()
    {
        return $this->belongsTo(Mesa::class, 'fk_id_mesa', 'id_mesa');
    }
}
