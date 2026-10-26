<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Ventas extends Model
{
    protected $table = 'registro_pago';
    protected $primaryKey = 'id_registro';
    public $timestamps = false;

    protected $fillable = [
        'monto',
        'fk_tipo_pago',
        'fecha_pago',
        'fk_id_historial',
        'fk_id_pedido',
    ];

    protected $casts = [
        'monto' => 'decimal:0',
        'fecha_pago' => 'date',
    ];

    public function historialCaja()
    {
        return $this->belongsTo(HistorialCaja::class, 'fk_id_historial', 'id_historial');
    }

    public function scopeBuscar(Builder $query, array $filtros): Builder
    {
        foreach ([
            'id_registro',
            'monto',
            'fk_tipo_pago',
            'fecha_pago',
            'fk_id_historial',
            'fk_id_pedido',
        ] as $campo) {
            if (isset($filtros[$campo]) && $filtros[$campo] !== '') {
                $query->where($campo, $filtros[$campo]);
            }
        }

        return $query;
    }
}
