<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Combo extends Model
{
  use HasFactory;

    protected $table = 'combos'; // O 'tipo_pago' si tienes una tabla específica para pagos
    protected $primaryKey = 'id';
    public $timestamps = false;

    // Campos permitidos para asignación masiva
    protected $fillable = [
        'combo', // Nombre del método de pago o combo (ej. "Efectivo", "Tarjeta")
        'precio'
    ];
}
