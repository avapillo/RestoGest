<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsuarioInicial extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('rol_usuario')->updateOrInsert(
            ['id_rol' => 1],
            ['rol_nombre' => 'Admin']
        );

        DB::table('usuarios')->updateOrInsert(
            ['gmail' => 'admin@gmail.com'],
            [
                'nombre' => 'Admin',
                'contrasenia' => Hash::make('12345678'),
                'id_rol_usuario' => 1,
            ]
        );
    }
}
