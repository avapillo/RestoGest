<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetallePedido extends Model
{
    use HasFactory;

    protected $table = 'detalle_pedido';
    protected $primaryKey = 'id_detalle';
    public $timestamps = false;

    protected $fillable = ['fk_id_pedido', 'fk_id_producto', 'fk_id_combo', 'cantidad'];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'fk_id_producto', 'id');
    }

    public function combo()
    {
        return $this->belongsTo(Combo::class, 'fk_id_combo', 'id');
    }
}
