<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $table = "productos";

    protected $fillable = ["nombre", "precio", "imagen", "fk_id_categoria"];

    public $timestamps = false;

    public function categoria()
    {
        // Relación: fk_id_categoria de la tabla productos apunta a 'id' de la tabla categoria
        return $this->belongsTo(Categoria::class, 'fk_id_categoria', 'id');
    }

    public function detalles()
    {
        return $this->hasMany(DetallePedido::class, 'fk_id_producto', 'id');
    }
}
