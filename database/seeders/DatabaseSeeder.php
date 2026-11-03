<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Para ejecutar el seeder presonalizado
     */
    public function run(): void
    {
    $this->call([
    UsuarioInicial::class,
    TiposPagos::class,
    EstadoPedido::class,
]);
    }
}
