<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistorialCaja extends Model
{
    protected $table = 'historial_caja';
    protected $primaryKey = 'id_historial';
    public $timestamps = false;

    protected $fillable = [
        'monto_total',
        'monto_inicial',
        'monto_cierre',
        'momento_cierre',
        'id_usuario'
    ];

    protected $casts = [
        'monto_inicial' => 'decimal:2',
        'monto_cierre' => 'decimal:2',
    ];

    // Relación para traer la información del usuario
    public function usuario()
    {
        return $this->belongsTo(Login::class, 'id_usuario', 'id');
    }
}
