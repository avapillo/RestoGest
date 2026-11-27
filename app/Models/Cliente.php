<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cliente extends Model
{
    use HasFactory;

    protected $table = 'clientes';

    protected $fillable = [
        'nombre',
        'dni_ultimos_4',
    ];

    public function pendientes(): HasMany
    {
        return $this->hasMany(Pendiente::class, 'cliente_id');
    }
}
