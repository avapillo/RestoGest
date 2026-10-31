<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Estado extends Model
{
    protected $table = 'estados';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'nombre'
    ];

    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'fk_id_estado', 'id');
    }
}
