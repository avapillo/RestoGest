<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TiposPagos extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (['Efectivo', 'Tarjeta de Crédito', 'Tarjeta de Débito', 'Transferencia'] as $tipoPago) {
            DB::table('tipo_pago')->updateOrInsert(
                ['tipo_pago' => $tipoPago],
                ['tipo_pago' => $tipoPago]
            );
        }
    }
}
