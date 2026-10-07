<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CajaEstado extends Model
{
    protected $table = 'caja_estado';

    protected $fillable = [
        'abierta',
        'monto_actual',
        'momento_apertura',
        'id_usuario',
    ];

    protected $casts = [
        'abierta' => 'boolean',
        'monto_actual' => 'decimal:2',
        'momento_apertura' => 'datetime',
    ];
}