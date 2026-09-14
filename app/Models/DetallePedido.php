<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetallePedido extends Model
{
    use HasFactory;

    protected $table = 'detalle_pedido';
    protected $primaryKey = 'id_detalle';

    protected $fillable = [
        'fk_id_pedido',
        'fk_id_producto',
        'fk_id_combo',
        'cantidad',
        'precio_unitario' // Asegúrate de incluirlo si guardas el precio al momento de la venta
    ];

    // Relación: El detalle pertenece a un Pedido
    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'fk_id_pedido', 'id_pedido');
    }

    // Relación: El detalle pertenece a un Producto
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'fk_id_producto', 'id');
    }

    // Relación: El detalle pertenece a un Combo
    public function combo()
    {
        return $this->belongsTo(Combo::class, 'fk_id_combo', 'id');
    }
}
