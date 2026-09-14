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
        'momento_cierre',
        'id_usuario'
    ];

    // Relación para traer la información del usuario
    public function usuario()
    {
        return $this->belongsTo(Login::class, 'id_usuario', 'id');
    }
}
