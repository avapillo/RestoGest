<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EstadoPedido extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (['Recibido', 'Preparando', 'Listo'] as $estado) {
            DB::table('estados')->updateOrInsert(
                ['estado' => $estado],
                ['estado' => $estado]
            );
        }
    }
}
