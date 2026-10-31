<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UsuarioIncial extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        # Inserta un rol dentro de la tablas roles
        DB::table("roles")->insert([
            'id_rol' => 1,
            'nombre' => 'Admin',
        ]);

        # Registra un usuario por defecto
        DB::table('usuarios')->insert([
            'nombre' => 'Admin',
            'contrasenia' => 12345678,
            'id_rol_usuario' => 1,
            'email' => 'admin@gmail.com'
        ]);
    }
}
