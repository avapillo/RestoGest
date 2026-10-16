<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $table = 'productos';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['nombre', 'precio', 'imagen', 'fk_id_categoria'];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'fk_id_categoria', 'id');
    }
}
